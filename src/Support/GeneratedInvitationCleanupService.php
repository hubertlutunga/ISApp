<?php

final class GeneratedInvitationCleanupService
{
    public const DEFAULT_MIN_AGE_SECONDS = 21600;

    public static function summarize(PDO $pdo, string $directory, int $minAgeSeconds = self::DEFAULT_MIN_AGE_SECONDS): array
    {
        $files = self::pdfFiles($directory);
        $referencedNames = self::referencedTemplateNames($pdo);
        $generatedFiles = self::generatedFiles($files, $referencedNames);
        $deletableGeneratedFiles = self::olderThan($generatedFiles, $minAgeSeconds);

        return [
            'directory' => $directory,
            'total_pdf_count' => count($files),
            'total_pdf_bytes' => array_sum(array_map(static fn(array $file): int => (int) $file['size'], $files)),
            'referenced_template_count' => count($referencedNames),
            'generated_pdf_count' => count($generatedFiles),
            'generated_pdf_bytes' => array_sum(array_map(static fn(array $file): int => (int) $file['size'], $generatedFiles)),
            'deletable_generated_pdf_count' => count($deletableGeneratedFiles),
            'deletable_generated_pdf_bytes' => array_sum(array_map(static fn(array $file): int => (int) $file['size'], $deletableGeneratedFiles)),
            'protected_recent_pdf_count' => count($generatedFiles) - count($deletableGeneratedFiles),
            'generated_files' => $generatedFiles,
            'deletable_generated_files' => $deletableGeneratedFiles,
        ];
    }

    public static function cleanup(PDO $pdo, string $directory, int $minAgeSeconds = self::DEFAULT_MIN_AGE_SECONDS): array
    {
        $summary = self::summarize($pdo, $directory, $minAgeSeconds);
        $deletedFiles = 0;
        $deletedBytes = 0;
        $failedFiles = [];

        foreach ($summary['deletable_generated_files'] as $file) {
            $path = (string) ($file['path'] ?? '');
            $name = (string) ($file['name'] ?? '');
            $size = (int) ($file['size'] ?? 0);

            if ($path !== '' && is_file($path) && @unlink($path)) {
                $deletedFiles++;
                $deletedBytes += $size;
                continue;
            }

            $failedFiles[] = $name;
        }

        return [
            'selected_files' => (int) $summary['deletable_generated_pdf_count'],
            'deleted_files' => $deletedFiles,
            'deleted_bytes' => $deletedBytes,
            'failed_files' => $failedFiles,
        ];
    }

    private static function referencedTemplateNames(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT DISTINCT invit_religieux FROM events WHERE invit_religieux IS NOT NULL AND TRIM(invit_religieux) <> \'\'');
        $names = [];

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
            $safeName = self::safeFileName((string) $name);
            if ($safeName !== '') {
                $names[$safeName] = true;
            }
        }

        return $names;
    }

    private static function generatedFiles(array $files, array $referencedNames): array
    {
        $generatedFiles = [];

        foreach ($files as $file) {
            $name = (string) ($file['name'] ?? '');
            if (isset($referencedNames[$name])) {
                continue;
            }

            $generatedFiles[] = $file;
        }

        return $generatedFiles;
    }

    private static function olderThan(array $files, int $minAgeSeconds): array
    {
        if ($minAgeSeconds <= 0) {
            return $files;
        }

        $limit = time() - $minAgeSeconds;
        return array_values(array_filter($files, static fn(array $file): bool => (int) ($file['mtime'] ?? time()) <= $limit));
    }

    private static function pdfFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $baseDir = realpath($directory);
        if ($baseDir === false) {
            return [];
        }

        $files = [];
        foreach (new DirectoryIterator($baseDir) as $fileInfo) {
            if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'pdf') {
                continue;
            }

            $files[] = [
                'name' => $fileInfo->getFilename(),
                'path' => $fileInfo->getPathname(),
                'size' => $fileInfo->getSize(),
                'mtime' => $fileInfo->getMTime(),
            ];
        }

        usort($files, static fn(array $left, array $right): int => ((int) $right['mtime']) <=> ((int) $left['mtime']));
        return $files;
    }

    private static function safeFileName(string $fileName): string
    {
        $fileName = basename(str_replace('\\', '/', trim($fileName)));
        return ($fileName === '.' || $fileName === '..') ? '' : $fileName;
    }
}
