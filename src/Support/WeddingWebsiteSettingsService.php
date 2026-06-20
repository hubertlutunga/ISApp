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
                'home_template' => 'home1',
                'hero_title' => $couple,
                'hero_subtitle' => 'Se marient dans',
                'hero_button' => 'RSVP',
                'home2_couple_names' => '',
                'home2_honor_text' => "Les familles Kasenga et Kalala Wa Kalala\nont l'honneur de vous convier au mariage de leurs enfants",
                'home2_hero_meta' => '',
                'home2_button_text' => 'Confirmer ma présence',
                'home2_gala_label' => 'La soirée',
                'home2_gala_title' => 'Soirée de Gala',
                'home2_gala_subtitle' => '',
                'home2_gala_primary_label' => 'Le cadre',
                'home2_gala_primary_value' => '',
                'home2_gala_primary_text' => 'Après les cérémonies du jour, nous aurons le plaisir de vous accueillir pour une soirée de gala conçue comme une célébration intime et mémorable, dans un cadre raffiné au bord du fleuve Congo.',
                'home2_gala_secondary_label' => 'L’esprit de la soirée',
                'home2_gala_secondary_value' => 'Musique · Gastronomie · Festivité',
                'home2_gala_secondary_text' => 'Entourés de nos familles et amis les plus chers, nous célébrerons cette union dans une atmosphère alliant émotion, élégance et joie partagée. Réservez cette date — nous comptons sur votre présence.',
                'home2_program_label' => 'Votre invitation',
                'home2_program_title' => 'Programme',
                'home2_program_subtitle' => "Retrouvez ci-dessous le déroulé de l'événement auquel vous êtes convié.",
                'home2_program_event_eyebrow' => '',
                'home2_program_event_title' => 'Soirée de Gala',
                'home2_program_event_badge' => 'Gala',
                'home2_program_time_label' => "Heure d'accueil",
                'home2_program_time_value' => '',
                'home2_program_location_label' => 'Lieu',
                'home2_program_location_value' => '',
                'home2_program_dress_label' => 'Dress code',
                'home2_program_dress_value' => "Tenue de soirée\nÉlégance requise",
                'home2_program_description' => 'Réception officielle, dîner de gala, animations et grande célébration.',
                'home2_story_label' => 'Notre histoire',
                'home2_story_title' => 'Love Story',
                'home2_story_text_1' => "Chaque étape de notre parcours nous a conduits, avec une certitude tranquille, vers cette célébration. Ce que nous fêtons n'est pas seulement notre union — c'est la joie de réunir autour de nous toutes les personnes qui ont compté dans cette histoire.",
                'home2_story_text_2' => 'Nous vous invitons à partager avec nous une soirée empreinte d’élégance, de chaleur et de moments qui restent.',
                'home2_story_coda' => 'Fin heureuse — nous nous marions.',
                'home2_presence_label' => 'Présences & attentions',
                'home2_presence_title' => 'Votre présence nous honore',
                'home2_presence_subtitle' => 'Votre venue est le plus beau des cadeaux. Pour ceux qui souhaitent nous accompagner davantage dans cette nouvelle étape, une urne sera mise à disposition le soir du gala.',
                'home2_presence_card1_title' => 'Participation à la réception',
                'home2_presence_card1_text' => "Dîner, animations et grande célébration avec l'ensemble des convives de la soirée.",
                'home2_presence_card2_title' => 'Invitation personnelle',
                'home2_presence_card2_text' => 'Cette invitation est strictement personnelle et non transmissible. Merci de confirmer votre présence via le formulaire RSVP.',
                'home2_location_label' => 'Logistique',
                'home2_location_title' => 'Adresse & hébergement',
                'home2_location_subtitle' => 'Informations pratiques pour préparer votre venue à Kinshasa, RDC.',
                'home2_main_place_title' => 'Lieu principal',
                'home2_main_place_text' => '',
                'home2_main_place_note' => 'Dress code : tenue de soirée, élégance requise. Accès et instructions détaillés dans votre carte d’accompagnement.',
                'home2_accommodation_title' => 'Hébergements recommandés',
                'home2_accommodation_text' => "Fleuve Congo Hôtel\nThe First MK\nHôtel du Fleuve",
                'home2_accommodation_note' => 'Pour toute question logistique, contactez l’équipe via le formulaire ci-dessous ou au numéro figurant sur votre invitation.',
                'home2_map_placeholder' => 'Carte Google Maps — à intégrer depuis l’espace d’administration',
                'home2_rsvp_label' => 'Confirmation',
                'home2_rsvp_title' => 'RSVP',
                'home2_rsvp_subtitle' => '',
                'home2_footer_meta' => '',
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
                'wedding_photo_enabled' => '1',
                'wedding_map_enabled' => '1',
                'wedding_map_iframe' => '',
                'wedding_map_iframe_b64' => '',
                'party_enabled' => '1',
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
                'home2_monogram' => '',
            ],
            'media' => [
                'background_music' => '',
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
            self::trace('wedding_settings_defaults_without_event', [
                'event_id' => $eventId,
            ]);
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
                self::trace('wedding_settings_invalid_json', [
                    'event_id' => $eventId,
                    'json_error' => json_last_error_msg(),
                    'json_excerpt' => substr($json, 0, 200),
                ]);
                $stored = [];
            }

            foreach (['sections', 'content', 'images', 'media'] as $key) {
                if (isset($stored[$key]) && !is_array($stored[$key])) {
                    self::trace('wedding_settings_invalid_shape', [
                        'event_id' => $eventId,
                        'key' => $key,
                        'type' => get_debug_type($stored[$key]),
                    ]);
                }
            }

            if (!array_key_exists('visibility_initialized', $stored) && isset($stored['sections']) && is_array($stored['sections'])) {
                foreach (array_keys($defaults['sections']) as $section) {
                    $stored['sections'][$section] = true;
                }
                $stored['visibility_initialized'] = true;
            }

            return self::normalizeStructure(
                self::enforceAlwaysVisibleSections(self::mergeRecursive($defaults, $stored)),
                $defaults
            );
        } catch (Throwable $exception) {
            self::trace('wedding_settings_exception', [
                'event_id' => $eventId,
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
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
        $defaults = self::defaults();
        $settings = self::normalizeStructure(self::mergeRecursive($defaults, $current), $defaults);
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

        if (!in_array((string) ($settings['content']['home_template'] ?? 'home1'), ['home1', 'home2'], true)) {
            $settings['content']['home_template'] = 'home1';
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

    public static function media(array $settings, string $field, string $fallback = ''): string
    {
        $value = trim((string) ($settings['media'][$field] ?? ''));
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

    public static function uploadAudio(array $files, array $settings, string $targetDir): array
    {
        $inputNames = ['wedding_music', 'wedding_music_style1', 'wedding_music_style2'];
        foreach ($inputNames as $inputName) {
            if (isset($files[$inputName]) && ($files[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $uploaded = EventMediaService::storeUploadedAudio($files[$inputName], $targetDir, 'wedmusic_');
                if ($uploaded !== null) {
                    $settings['media']['background_music'] = $uploaded;
                }
            }
        }

        return $settings;
    }

    public static function backgroundMusicMarkup(array $settings, string $urlPrefix = '../couple/audio/'): string
    {
        $musicFile = self::media($settings, 'background_music', '');
        if ($musicFile === '') {
            return '';
        }

        $musicUrl = rtrim($urlPrefix, '/') . '/' . rawurlencode($musicFile);
        $musicUrlHtml = htmlspecialchars($musicUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div class="wedsite-music" aria-live="polite">
    <audio id="wedsiteBackgroundMusic" src="{$musicUrlHtml}" autoplay loop preload="auto" playsinline></audio>
    <button type="button" id="wedsiteMusicButton" class="wedsite-music-button" aria-label="Activer ou arrêter la musique">♫</button>
</div>
<style>
.wedsite-music-button{position:fixed;right:18px;bottom:18px;z-index:2147483000;width:46px;height:46px;border:0;border-radius:50%;background:rgba(15,23,42,.88);color:#fff;box-shadow:0 12px 30px rgba(15,23,42,.25);font-size:20px;font-weight:900;cursor:pointer;display:flex;align-items:center;justify-content:center}
.wedsite-music-button.is-playing{background:#15803d}
.wedsite-music-button.is-waiting{animation:wedsiteMusicPulse 1.2s ease-in-out infinite;background:#b45309}
@keyframes wedsiteMusicPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}
</style>
<script>
(function(){
    var audio = document.getElementById('wedsiteBackgroundMusic');
    var button = document.getElementById('wedsiteMusicButton');
    if (!audio || !button) return;
    audio.volume = 0.55;
    function setState(){
        button.classList.toggle('is-playing', !audio.paused);
        button.classList.toggle('is-waiting', audio.paused);
        button.setAttribute('aria-label', audio.paused ? 'Activer la musique' : 'Arrêter la musique');
    }
    function playMusic(){
        var promise = audio.play();
        if (promise && typeof promise.catch === 'function') {
            promise.catch(function(){ setState(); });
        }
        setTimeout(setState, 120);
    }
    button.addEventListener('click', function(){
        if (audio.paused) { playMusic(); } else { audio.pause(); setState(); }
    });
    ['click','touchstart','keydown'].forEach(function(eventName){
        document.addEventListener(eventName, function once(){
            if (audio.paused) playMusic();
            document.removeEventListener(eventName, once, true);
        }, true);
    });
    audio.addEventListener('play', setState);
    audio.addEventListener('pause', setState);
    playMusic();
    setState();
})();
</script>
HTML;
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

    private static function normalizeStructure(array $settings, array $defaults): array
    {
        foreach (['sections', 'content', 'images', 'media'] as $key) {
            if (!isset($settings[$key]) || !is_array($settings[$key])) {
                $settings[$key] = $defaults[$key];
                continue;
            }

            $settings[$key] = self::mergeRecursive($defaults[$key], $settings[$key]);
        }

        $settings['visibility_initialized'] = (bool) ($settings['visibility_initialized'] ?? $defaults['visibility_initialized']);
        if (!in_array((string) ($settings['content']['home_template'] ?? 'home1'), ['home1', 'home2'], true)) {
            $settings['content']['home_template'] = 'home1';
        }

        return $settings;
    }

    private static function enforceAlwaysVisibleSections(array $settings): array
    {
        foreach (self::ALWAYS_VISIBLE_SECTIONS as $section) {
            $settings['sections'][$section] = true;
        }

        return $settings;
    }

    private static function trace(string $stage, array $context = []): void
    {
        if (class_exists('PublicSiteTraceService')) {
            PublicSiteTraceService::record($stage, $context);
        }
    }
}
