<?php

final class EventPhotoCleanupService
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public static function normalizeYear(mixed $year): int
    {
        $normalizedYear = (int) $year;
        $maxYear = (int) date('Y') + 1;

        if ($normalizedYear < 2000 || $normalizedYear > $maxYear) {
            throw new InvalidArgumentException('Année invalide.');
        }

        return $normalizedYear;
    }

    public static function availableYears(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT DISTINCT photo_year FROM (
                SELECT YEAR(e.date_enreg) AS photo_year
                FROM photos_event p
                LEFT JOIN events e ON e.cod_event = p.cod_event
                WHERE e.date_enreg IS NOT NULL
                UNION
                SELECT YEAR(e.date_event) AS photo_year
                FROM photos_event p
                LEFT JOIN events e ON e.cod_event = p.cod_event
                WHERE e.date_event IS NOT NULL
             ) AS photo_years
             ORDER BY photo_year DESC'
        );

        return array_values(array_filter(array_map(static fn($value): int => (int) $value, $stmt->fetchAll(PDO::FETCH_COLUMN))));
    }

    public static function summarize(PDO $pdo, string $photoDir, int $year): array
    {
        $year = self::normalizeYear($year);
        $rows = self::photoRowsByYear($pdo, $year);
        $referencedNames = self::referencedPhotoNames($pdo);
        $files = self::imageFiles($photoDir);
        $orphanFiles = self::orphanFilesByYear($photoDir, $year, $referencedNames);

        $linkedExistingFiles = 0;
        $missingLinkedFiles = 0;
        $linkedBytes = 0;

        foreach ($rows as $row) {
            $fileName = self::safeFileName((string) ($row['nom_photo'] ?? ''));
            if ($fileName === '') {
                $missingLinkedFiles++;
                continue;
            }

            $filePath = self::resolveFilePath($photoDir, $fileName);
            if ($filePath !== null && is_file($filePath)) {
                $linkedExistingFiles++;
                $linkedBytes += (int) filesize($filePath);
                continue;
            }

            $missingLinkedFiles++;
        }

        return [
            'year' => $year,
            'rows' => $rows,
            'db_photo_count' => self::totalDbPhotos($pdo),
            'server_file_count' => count($files),
            'server_file_bytes' => array_sum(array_map(static fn(array $file): int => (int) $file['size'], $files)),
            'year_db_count' => count($rows),
            'year_linked_existing_count' => $linkedExistingFiles,
            'year_missing_file_count' => $missingLinkedFiles,
            'year_linked_bytes' => $linkedBytes,
            'year_orphan_files' => $orphanFiles,
            'year_orphan_count' => count($orphanFiles),
            'year_orphan_bytes' => array_sum(array_map(static fn(array $file): int => (int) $file['size'], $orphanFiles)),
        ];
    }

    public static function cleanupYear(PDO $pdo, string $photoDir, int $year, bool $deleteOrphans = false): array
    {
        $year = self::normalizeYear($year);
        $rows = self::photoRowsByYear($pdo, $year);
        $dbIdsToDelete = [];
        $deletedServerFiles = 0;
        $deletedServerBytes = 0;
        $missingServerFiles = 0;
        $failedServerFiles = [];

        foreach ($rows as $row) {
            $photoId = (int) ($row['cod_photo'] ?? 0);
            $fileName = self::safeFileName((string) ($row['nom_photo'] ?? ''));

            if ($photoId <= 0) {
                continue;
            }

            if ($fileName === '') {
                $dbIdsToDelete[] = $photoId;
                $missingServerFiles++;
                continue;
            }

            $filePath = self::resolveFilePath($photoDir, $fileName);
            if ($filePath === null || !is_file($filePath)) {
                $dbIdsToDelete[] = $photoId;
                $missingServerFiles++;
                continue;
            }

            $fileSize = (int) filesize($filePath);
            if (@unlink($filePath)) {
                $dbIdsToDelete[] = $photoId;
                $deletedServerFiles++;
                $deletedServerBytes += $fileSize;
                continue;
            }

            $failedServerFiles[] = $fileName;
        }

        $deletedDbRows = self::deletePhotoRows($pdo, $dbIdsToDelete);
        $deletedOrphanFiles = 0;
        $deletedOrphanBytes = 0;
        $failedOrphanFiles = [];

        if ($deleteOrphans) {
            $referencedNames = self::referencedPhotoNames($pdo);
            foreach (self::orphanFilesByYear($photoDir, $year, $referencedNames) as $orphanFile) {
                $filePath = (string) ($orphanFile['path'] ?? '');
                $fileName = (string) ($orphanFile['name'] ?? '');
                $fileSize = (int) ($orphanFile['size'] ?? 0);

                if ($filePath !== '' && is_file($filePath) && @unlink($filePath)) {
                    $deletedOrphanFiles++;
                    $deletedOrphanBytes += $fileSize;
                    continue;
                }

                $failedOrphanFiles[] = $fileName;
            }
        }

        return [
            'year' => $year,
            'selected_db_rows' => count($rows),
            'deleted_db_rows' => $deletedDbRows,
            'deleted_server_files' => $deletedServerFiles,
            'deleted_server_bytes' => $deletedServerBytes,
            'missing_server_files' => $missingServerFiles,
            'failed_server_files' => $failedServerFiles,
            'deleted_orphan_files' => $deletedOrphanFiles,
            'deleted_orphan_bytes' => $deletedOrphanBytes,
            'failed_orphan_files' => $failedOrphanFiles,
        ];
    }

    private static function photoRowsByYear(PDO $pdo, int $year): array
    {
        $stmt = $pdo->prepare(
            'SELECT p.cod_photo, p.cod_event, p.nom_photo, e.date_enreg, e.date_event,
                    COALESCE(e.date_enreg, e.date_event) AS reference_date
             FROM photos_event p
             LEFT JOIN events e ON e.cod_event = p.cod_event
             WHERE (e.date_enreg IS NOT NULL AND YEAR(e.date_enreg) = :year)
            OR (e.date_event IS NOT NULL AND YEAR(e.date_event) = :year)
             ORDER BY p.cod_photo DESC'
        );
        $stmt->bindValue(':year', $year, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function referencedPhotoNames(PDO $pdo): array
    {
        $stmt = $pdo->query('SELECT DISTINCT nom_photo FROM photos_event WHERE nom_photo IS NOT NULL AND TRIM(nom_photo) <> \'\'');
        $names = [];

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
            $safeName = self::safeFileName((string) $name);
            if ($safeName !== '') {
                $names[$safeName] = true;
            }
        }

        return $names;
    }

    private static function totalDbPhotos(PDO $pdo): int
    {
        return (int) $pdo->query('SELECT COUNT(*) FROM photos_event')->fetchColumn();
    }

    private static function deletePhotoRows(PDO $pdo, array $photoIds): int
    {
        $photoIds = array_values(array_unique(array_filter(array_map('intval', $photoIds), static fn(int $id): bool => $id > 0)));
        if ($photoIds === []) {
            return 0;
        }

        $deletedRows = 0;
        foreach (array_chunk($photoIds, 200) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '?'));
            $stmt = $pdo->prepare('DELETE FROM photos_event WHERE cod_photo IN (' . $placeholders . ')');
            $stmt->execute($chunk);
            $deletedRows += $stmt->rowCount();
        }

        return $deletedRows;
    }

    private static function imageFiles(string $photoDir): array
    {
        if (!is_dir($photoDir)) {
            return [];
        }

        $files = [];
        foreach (new DirectoryIterator($photoDir) as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $extension = strtolower($fileInfo->getExtension());
            if (!in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                continue;
            }

            $files[] = [
                'name' => $fileInfo->getFilename(),
                'path' => $fileInfo->getPathname(),
                'mtime' => $fileInfo->getMTime(),
                'size' => $fileInfo->getSize(),
            ];
        }

        return $files;
    }

    private static function orphanFilesByYear(string $photoDir, int $year, array $referencedNames): array
    {
        $orphanFiles = [];

        foreach (self::imageFiles($photoDir) as $file) {
            if ((int) date('Y', (int) $file['mtime']) !== $year) {
                continue;
            }

            if (isset($referencedNames[(string) $file['name']])) {
                continue;
            }

            $orphanFiles[] = $file;
        }

        usort($orphanFiles, static fn(array $left, array $right): int => ((int) $right['mtime']) <=> ((int) $left['mtime']));
        return $orphanFiles;
    }

    private static function safeFileName(string $fileName): string
    {
        $fileName = basename(str_replace('\\', '/', trim($fileName)));

        if ($fileName === '' || $fileName === '.' || $fileName === '..') {
            return '';
        }

        return $fileName;
    }

    private static function resolveFilePath(string $photoDir, string $fileName): ?string
    {
        $safeFileName = self::safeFileName($fileName);
        if ($safeFileName === '') {
            return null;
        }

        $baseDir = realpath($photoDir);
        if ($baseDir === false || !is_dir($baseDir)) {
            return null;
        }

        $filePath = $baseDir . DIRECTORY_SEPARATOR . $safeFileName;
        $realFilePath = realpath($filePath);

        if ($realFilePath === false || !str_starts_with($realFilePath, $baseDir . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $realFilePath;
    }
}
