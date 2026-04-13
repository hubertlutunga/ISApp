<?php

final class PageRouter
{
    public static function sanitizePageName(?string $page): string
    {
        $page = trim((string) $page);
        if ($page === '') {
            return '';
        }

        return preg_match('/^[a-zA-Z0-9_-]+$/', $page) ? $page : '';
    }

    public static function resolve(?string $page, string $pagesDirectory): ?string
    {
        $page = self::sanitizePageName($page);
        if ($page === '') {
            return null;
        }

        $filePath = rtrim($pagesDirectory, '/') . '/' . $page . '.php';

        return is_file($filePath) ? $filePath : null;
    }

    public static function redirect(string $target): void
    {
        header('Location: ' . $target);
        exit();
    }
}