<?php

final class WeddingWebsiteSettingsService
{
    public const TABLE_NAME = 'wedding_website_settings';
    public const ALWAYS_VISIBLE_SECTIONS = ['hero', 'save_date'];

    public static function defaults(array $event = []): array
    {
        $groom = trim((string) ($event['prenom_epoux'] ?? ''));
        $bride = trim((string) ($event['prenom_epouse'] ?? ''));
        $couple = trim($groom . ' & ' . $bride, ' &');
        if ($couple === '') {
            $couple = 'Les mariés';
        }

        return [
            'visibility_initialized' => true,
            'sections' => [
                'hero' => true,
                'save_date' => true,
                'love_story' => true,
                'wedding_events' => true,
                'gallery' => true,
                'gift' => true,
                'friends' => true,
                'rsvp' => true,
                'location' => true,
            ],
            'content' => [
                'hero_title' => $couple,
                'hero_subtitle' => 'Se marient dans',
                'hero_button' => 'RSVP',
                'save_title' => 'Save the date',
                'save_text' => '',
                'love_title' => 'Love Story',
                'love_subtitle' => 'Notre histoire d’amour et le mariage',
                'love_meeting_title' => 'Quand ' . ($groom !== '' ? $groom : 'il') . ' a rencontré ' . ($bride !== '' ? $bride : 'elle'),
                'love_meeting_subtitle' => 'Ce jour a tout changé',
                'love_end_title' => 'Fin heureuse, nous nous marions',
                'love_end_subtitle' => 'Comptez les jours...',
                'wedding_title' => 'Wedding Events',
                'wedding_subtitle' => 'Cérémonies principales - Fête de mariage',
                'ceremony_title' => 'Bénédiction nuptiale',
                'ceremony_time' => '',
                'ceremony_place' => '',
                'party_title' => 'Wedding Party',
                'party_time' => '',
                'party_place' => '',
                'gift_title' => 'Cadeaux',
                'gift_text' => 'Votre présence est le plus beau des cadeaux. Si vous souhaitez nous témoigner une attention, cette section vous guide.',
                'gift_items' => "Participation à la réception\nDécoration du foyer\nVoyage de noces",
                'friends_title' => 'Nos invités',
                'friends_subtitle' => 'Quelques mots laissés lors des confirmations',
                'friend_1_name' => $bride !== '' ? $bride : 'Demoiselle d’honneur',
                'friend_1_role' => 'Bridesmaid',
                'friend_2_name' => $groom !== '' ? $groom : 'Garçon d’honneur',
                'friend_2_role' => 'Groomsman',
                'friends_quote' => 'Merci d’être à nos côtés pour ce nouveau chapitre.',
                'friends_quote_author' => 'Avec amour, ' . $couple,
                'guest_confirmation_ids' => '',
                'guest_empty_text' => 'Sélectionnez les confirmations à afficher depuis votre espace de personnalisation.',
                'rsvp_title' => 'RSVP',
                'rsvp_subtitle' => 'Allez-vous y assister ?',
                'location_title' => 'Where To Stay',
                'location_subtitle' => 'Adresse, hébergement et informations pratiques',
                'location_1_badge' => 'Lieu',
                'location_1_title' => (string) ($event['lieu'] ?? 'Lieu de cérémonie'),
                'location_1_text' => (string) ($event['adresse'] ?? ''),
                'location_1_link' => '',
                'location_2_badge' => 'Hébergement',
                'location_2_title' => 'Hébergement recommandé',
                'location_2_text' => '',
                'location_2_link' => '',
                'location_accommodations' => '[]',
            ],
            'images' => [
                'hero_bg' => (string) ($event['photostory'] ?? ''),
                'save_heart' => (string) ($event['photo'] ?? ''),
                'wedding_bg' => (string) ($event['photostory'] ?? ''),
                'friend_1_photo' => (string) ($event['photo'] ?? ''),
                'friend_2_photo' => (string) ($event['photostory'] ?? ''),
                'location_1_photo' => (string) ($event['photo'] ?? ''),
                'location_2_photo' => (string) ($event['photostory'] ?? ''),
            ],
        ];
    }

    public static function ensureTable(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE IF NOT EXISTS ' . self::TABLE_NAME . ' (
            cod_event INT NOT NULL PRIMARY KEY,
            settings_json MEDIUMTEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public static function get(PDO $pdo, int $eventId, array $event = []): array
    {
        $defaults = self::defaults($event);
        if ($eventId <= 0) {
            return $defaults;
        }

        try {
            self::ensureTable($pdo);
            $stmt = $pdo->prepare('SELECT settings_json FROM ' . self::TABLE_NAME . ' WHERE cod_event = ? LIMIT 1');
            $stmt->execute([$eventId]);
            $json = (string) ($stmt->fetchColumn() ?: '');
            $stmt->closeCursor();
            $stored = $json !== '' ? json_decode($json, true) : [];
            if (!is_array($stored)) {
                $stored = [];
            }

            if (!array_key_exists('visibility_initialized', $stored) && isset($stored['sections']) && is_array($stored['sections'])) {
                foreach (array_keys($defaults['sections']) as $section) {
                    $stored['sections'][$section] = true;
                }
                $stored['visibility_initialized'] = true;
            }

            return self::enforceAlwaysVisibleSections(self::mergeRecursive($defaults, $stored));
        } catch (Throwable $exception) {
            return $defaults;
        }
    }

    public static function save(PDO $pdo, int $eventId, array $settings): void
    {
        if ($eventId <= 0) {
            return;
        }

        self::ensureTable($pdo);
        $settings = self::enforceAlwaysVisibleSections($settings);
        $json = json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt = $pdo->prepare('INSERT INTO ' . self::TABLE_NAME . ' (cod_event, settings_json, created_at, updated_at)
            VALUES (:cod_event, :settings_json, NOW(), NOW())
            ON DUPLICATE KEY UPDATE settings_json = VALUES(settings_json), updated_at = NOW()');
        $stmt->execute([
            ':cod_event' => $eventId,
            ':settings_json' => $json !== false ? $json : '{}',
        ]);
    }

    public static function fromPost(array $post, array $current): array
    {
        $settings = self::mergeRecursive(self::defaults(), $current);
        $knownSections = array_keys($settings['sections']);
        $postedSections = isset($post['wedding_sections']) && is_array($post['wedding_sections']) ? $post['wedding_sections'] : [];

        foreach ($knownSections as $section) {
            if (in_array($section, self::ALWAYS_VISIBLE_SECTIONS, true)) {
                $settings['sections'][$section] = true;
                continue;
            }

            if (array_key_exists($section, $postedSections)) {
                $settings['sections'][$section] = in_array((string) $postedSections[$section], ['show', 'on', '1', 'true'], true);
            }
        }

        foreach (array_keys($settings['content']) as $field) {
            if ($field === 'guest_confirmation_ids') {
                if (array_key_exists($field, $post) && is_array($post[$field])) {
                    $settings['content'][$field] = implode(',', array_values(array_filter(array_map('intval', $post[$field]))));
                } elseif (!array_key_exists($field, $post)) {
                    $settings['content'][$field] = '';
                }
                continue;
            }

            if (array_key_exists($field, $post)) {
                $settings['content'][$field] = trim((string) $post[$field]);
            }
        }

        return self::enforceAlwaysVisibleSections($settings);
    }

    public static function sectionEnabled(array $settings, string $section): bool
    {
        if ($section === 'footer') {
            return false;
        }

        if (in_array($section, self::ALWAYS_VISIBLE_SECTIONS, true)) {
            return true;
        }

        return (bool) ($settings['sections'][$section] ?? true);
    }

    public static function text(array $settings, string $field, string $fallback = ''): string
    {
        $value = trim((string) ($settings['content'][$field] ?? ''));
        return $value !== '' ? $value : $fallback;
    }

    public static function image(array $settings, string $field, string $fallback = ''): string
    {
        $value = trim((string) ($settings['images'][$field] ?? ''));
        return $value !== '' ? $value : $fallback;
    }

    public static function uploadImages(array $files, array $settings, string $targetDir): array
    {
        foreach (array_keys($settings['images']) as $imageField) {
            $inputName = 'wedding_image_' . $imageField;
            if (isset($files[$inputName]) && ($files[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $uploaded = EventMediaService::storeUploadedImage($files[$inputName], $targetDir, 'wedsite_');
                if ($uploaded !== null) {
                    $settings['images'][$imageField] = $uploaded;
                }
            }
        }

        return $settings;
    }

    private static function mergeRecursive(array $defaults, array $stored): array
    {
        foreach ($stored as $key => $value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key])) {
                $defaults[$key] = self::mergeRecursive($defaults[$key], $value);
            } else {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    private static function enforceAlwaysVisibleSections(array $settings): array
    {
        foreach (self::ALWAYS_VISIBLE_SECTIONS as $section) {
            $settings['sections'][$section] = true;
        }

        return $settings;
    }
}
