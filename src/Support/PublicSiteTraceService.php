<?php

final class PublicSiteTraceService
{
    private static bool $booted = false;
    private static bool $enabled = false;
    private static bool $completed = false;
    private static string $traceId = '';
    private static string $lastStage = 'boot';
    private static string $logFile = '';
    private static array $requestContext = [];

    public static function boot(array $context = [], array $config = []): void
    {
        if (self::$booted) {
            self::$requestContext = array_replace(self::$requestContext, self::sanitizeContext($context));
            return;
        }

        self::$booted = true;
        self::$requestContext = self::sanitizeContext($context);
        $normalizedConfig = self::normalizeConfig($config);
        self::$enabled = self::shouldEnable($normalizedConfig);

        if (!self::$enabled) {
            return;
        }

        $directory = self::prepareDirectory($normalizedConfig['directory']);
        if ($directory === '') {
            self::$enabled = false;
            return;
        }

        self::$traceId = self::buildTraceId();
        self::$logFile = rtrim($directory, '/') . '/' . date('Y-m-d') . '.log';

        if (!headers_sent()) {
            header('X-ISAPP-Trace-Id: ' . self::$traceId);
        }

        register_shutdown_function([self::class, 'handleShutdown']);

        self::emit('trace_started', [
            'log_file' => basename(self::$logFile),
            'request_uri' => (string) ($_SERVER['REQUEST_URI'] ?? ''),
        ]);
    }

    public static function enabled(): bool
    {
        return self::$enabled;
    }

    public static function id(): string
    {
        return self::$traceId;
    }

    public static function record(string $stage, array $context = []): void
    {
        if (!self::$enabled) {
            return;
        }

        self::$lastStage = $stage;
        self::emit($stage, $context);
    }

    public static function exception(string $stage, Throwable $exception, array $context = []): void
    {
        if (!self::$enabled) {
            return;
        }

        self::$lastStage = $stage;
        self::emit($stage, array_merge($context, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]));
    }

    public static function finish(string $stage = 'trace_completed', array $context = []): void
    {
        if (!self::$enabled) {
            return;
        }

        self::$completed = true;
        self::record($stage, $context);
    }

    public static function handleShutdown(): void
    {
        if (!self::$enabled) {
            return;
        }

        $error = error_get_last();
        if (is_array($error) && in_array((int) ($error['type'] ?? 0), [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            self::emit('php_fatal', [
                'last_stage' => self::$lastStage,
                'error' => [
                    'type' => (int) ($error['type'] ?? 0),
                    'message' => (string) ($error['message'] ?? ''),
                    'file' => (string) ($error['file'] ?? ''),
                    'line' => (int) ($error['line'] ?? 0),
                ],
            ]);
            return;
        }

        if (!self::$completed) {
            self::emit('trace_shutdown', [
                'last_stage' => self::$lastStage,
            ]);
        }
    }

    private static function emit(string $stage, array $context = []): void
    {
        $entry = [
            'ts' => date('c'),
            'trace_id' => self::$traceId,
            'stage' => $stage,
            'request' => self::$requestContext,
            'context' => self::sanitizeContext($context),
        ];

        $json = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return;
        }

        @file_put_contents(self::$logFile, $json . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private static function normalizeConfig(array $config): array
    {
        return [
            'enabled' => (bool) ($config['enabled'] ?? false),
            'key' => trim((string) ($config['key'] ?? '')),
            'directory' => trim((string) ($config['directory'] ?? dirname(__DIR__, 2) . '/storage/traces/public-site')),
        ];
    }

    private static function shouldEnable(array $config): bool
    {
        $traceRequested = self::isTraceRequested();
        if (!$traceRequested) {
            return false;
        }

        $providedKey = self::providedKey();
        $configuredKey = $config['key'];

        if (self::isProductionLike()) {
            if (!$config['enabled'] || $configuredKey === '') {
                return false;
            }

            return hash_equals($configuredKey, $providedKey);
        }

        if ($configuredKey === '') {
            return true;
        }

        return hash_equals($configuredKey, $providedKey);
    }

    private static function isTraceRequested(): bool
    {
        if (PHP_SAPI === 'cli') {
            return (string) ($_GET['trace'] ?? '') === '1';
        }

        return (string) ($_GET['trace'] ?? '') === '1'
            || (string) ($_SERVER['HTTP_X_ISAPP_TRACE'] ?? '') === '1';
    }

    private static function providedKey(): string
    {
        return trim((string) ($_GET['trace_key'] ?? $_SERVER['HTTP_X_ISAPP_TRACE_KEY'] ?? ''));
    }

    private static function isProductionLike(): bool
    {
        $host = (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
        $homeDirectory = (string) (getenv('HOME') ?: '');

        return str_contains($host, 'invitationspeciale.com')
            || str_contains($homeDirectory, '/home/invizfxg');
    }

    private static function buildTraceId(): string
    {
        try {
            return 'isapp-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
        } catch (Throwable $exception) {
            return 'isapp-' . date('Ymd-His') . '-' . substr(md5((string) mt_rand()), 0, 8);
        }
    }

    private static function prepareDirectory(string $directory): string
    {
        if ($directory === '') {
            return '';
        }

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            return '';
        }

        $htaccessPath = rtrim($directory, '/') . '/.htaccess';
        if (!is_file($htaccessPath)) {
            @file_put_contents($htaccessPath, "Require all denied\nDeny from all\n");
        }

        $indexPath = rtrim($directory, '/') . '/index.html';
        if (!is_file($indexPath)) {
            @file_put_contents($indexPath, '');
        }

        return $directory;
    }

    private static function sanitizeContext(array $context): array
    {
        $sanitized = [];
        $count = 0;

        foreach ($context as $key => $value) {
            $sanitized[(string) $key] = self::sanitizeValue($value, 0);
            $count++;
            if ($count >= 25) {
                break;
            }
        }

        return $sanitized;
    }

    private static function sanitizeValue(mixed $value, int $depth): mixed
    {
        if ($depth >= 3) {
            return '[depth-limit]';
        }

        if (is_null($value) || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return mb_substr($value, 0, 400);
        }

        if (is_array($value)) {
            $result = [];
            $count = 0;
            foreach ($value as $itemKey => $itemValue) {
                $result[(string) $itemKey] = self::sanitizeValue($itemValue, $depth + 1);
                $count++;
                if ($count >= 20) {
                    break;
                }
            }

            return $result;
        }

        if ($value instanceof Throwable) {
            return [
                'exception' => get_class($value),
                'message' => $value->getMessage(),
                'file' => $value->getFile(),
                'line' => $value->getLine(),
            ];
        }

        if (is_object($value)) {
            return '[object ' . get_class($value) . ']';
        }

        return '[unserializable]';
    }
}