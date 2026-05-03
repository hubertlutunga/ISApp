<?php

final class EnvLoader
{
    public static function loadProjectEnv(string $projectRoot): void
    {
        static $loadedRoots = [];

        $normalizedRoot = rtrim(str_replace('\\', '/', $projectRoot), '/');
        if ($normalizedRoot === '' || isset($loadedRoots[$normalizedRoot])) {
            return;
        }

        $envPaths = [];

        $parentRoot = dirname($normalizedRoot);
        if ($parentRoot !== '' && $parentRoot !== '.' && $parentRoot !== $normalizedRoot) {
            $envPaths[] = $parentRoot . '/.isapp.env';
            $envPaths[] = $parentRoot . '/.isapp.env.local';
        }

        $envPaths[] = $normalizedRoot . '/.env';
        $envPaths[] = $normalizedRoot . '/.env.local';

        foreach ($envPaths as $envPath) {
            self::loadFile($envPath);
        }

        $loadedRoots[$normalizedRoot] = true;
    }

    private static function loadFile(string $filePath): void
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_starts_with($line, 'export ')) {
                $line = trim(substr($line, 7));
            }

            $separatorPosition = strpos($line, '=');
            if ($separatorPosition === false) {
                continue;
            }

            $name = trim(substr($line, 0, $separatorPosition));
            if ($name === '' || !preg_match('/^[A-Z0-9_]+$/', $name)) {
                continue;
            }

            if (self::hasValue($name)) {
                continue;
            }

            $value = self::normalizeValue(substr($line, $separatorPosition + 1));

            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    private static function normalizeValue(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $firstCharacter = $value[0];
        $lastCharacter = $value[strlen($value) - 1];

        if (($firstCharacter === '"' && $lastCharacter === '"') || ($firstCharacter === "'" && $lastCharacter === "'")) {
            $value = substr($value, 1, -1);
        } else {
            $value = preg_replace('/\s+#.*$/', '', $value) ?? $value;
        }

        return strtr($value, [
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
            '\\"' => '"',
            "\\'" => "'",
            '\\\\' => '\\',
        ]);
    }

    private static function hasValue(string $name): bool
    {
        $value = getenv($name);
        if (is_string($value) && $value !== '') {
            return true;
        }

        return (isset($_ENV[$name]) && $_ENV[$name] !== '')
            || (isset($_SERVER[$name]) && $_SERVER[$name] !== '');
    }
}