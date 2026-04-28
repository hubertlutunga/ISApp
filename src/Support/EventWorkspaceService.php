<?php

final class EventWorkspaceService
{
    public static function resolveCurrentEventContext(PDO $pdo, array $session, array $request): array
    {
        $sessionUserId = (string) ($session['cod_user'] ?? '');
        $requestedEventId = isset($request['codevent']) ? (string) $request['codevent'] : '';
        $dataevent = null;

        if ($requestedEventId !== '') {
            self::promoteEventPosition($pdo, $requestedEventId, $sessionUserId);

            $stmt = $pdo->prepare('SELECT * FROM events WHERE cod_event = ? AND (cod_user = ? OR cod_user2 = ?) AND position = ?');
            $stmt->execute([$requestedEventId, $sessionUserId, $sessionUserId, '1']);
            $dataevent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if ($dataevent === null) {
                $stmt = $pdo->prepare('SELECT * FROM events WHERE cod_event = ? AND (cod_user = ? OR cod_user2 = ?)');
                $stmt->execute([$requestedEventId, $sessionUserId, $sessionUserId]);
                $dataevent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
        } else {
            $stmt = $pdo->prepare('SELECT * FROM events WHERE (cod_user = ? OR cod_user2 = ?) AND position = ? ORDER BY cod_event DESC LIMIT 1');
            $stmt->execute([$sessionUserId, $sessionUserId, '1']);
            $dataevent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if ($dataevent === null) {
                $stmt = $pdo->prepare('SELECT * FROM events WHERE (cod_user = ? OR cod_user2 = ?) AND position IS NULL ORDER BY cod_event DESC LIMIT 1');
                $stmt->execute([$sessionUserId, $sessionUserId]);
                $dataevent = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }
        }

        if ($dataevent === null) {
            return [
                'dataevent' => false,
                'date_event' => '',
                'type_event' => '',
                'display' => 'none',
                'codevent' => '',
                'vuoption' => '',
                'photo' => '',
                'photocoeur' => '',
                'data_evenement' => '',
                'text_sdd' => '',
                'text_lovestory' => '',
                'img_debulov' => '',
                'img_finulov' => '',
                'img_event' => '',
            ];
        }

        $codevent = (string) ($dataevent['cod_event'] ?? '');
        $typeEvent = (string) ($dataevent['type_event'] ?? '');
        $photo = $dataevent['photostory'] ?? 'defaulwed_1.png';
        $photocoeur = $dataevent['photo'] ?? 'defaulwed_1.png';
        $dataEvenement = self::findEventTypeName($pdo, $typeEvent);
        $siteContent = self::resolveSiteContent($pdo, $dataevent, $codevent, $typeEvent, $photo, $photocoeur);

        return [
            'dataevent' => $dataevent,
            'date_event' => $dataevent['date_event'] ?? '',
            'type_event' => $typeEvent,
            'display' => 'block',
            'codevent' => $codevent,
            'vuoption' => $codevent,
            'photo' => $photo,
            'photocoeur' => $photocoeur,
            'data_evenement' => $dataEvenement,
            'text_sdd' => $siteContent['text_sdd'],
            'text_lovestory' => $siteContent['text_lovestory'],
            'img_debulov' => $siteContent['img_debulov'],
            'img_finulov' => $siteContent['img_finulov'],
            'img_event' => $siteContent['img_event'],
        ];
    }

    public static function resolveEventIdentity(array $event, string $typeEvent, string $eventTypeName): array
    {
        if ($typeEvent === '1') {
            return [
                'typeevent' => 'Mariage ' . ($event['type_mar'] ?? 'Inconnu'),
                'displayvue' => 'display:block;',
                'fetard' => (($event['prenom_epouse'] ?? '') . ' & ' . ($event['prenom_epoux'] ?? '')) ?: 'Inconnu',
            ];
        }

        return [
            'typeevent' => $eventTypeName,
            'displayvue' => 'display:none;',
            'fetard' => $event['nomfetard'] ?? 'Inconnu',
        ];
    }

    public static function formatEventDate(?string $dateEvent): string
    {
        $date = $dateEvent ? new DateTime($dateEvent) : new DateTime();
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::LONG,
            IntlDateFormatter::NONE,
            null,
            IntlDateFormatter::GREGORIAN,
            'EEEE, dd/MM/yyyy à HH:mm'
        );

        return ucfirst((string) $formatter->format($date));
    }

    public static function audienceLabels(string $typeEvent): array
    {
        if ($typeEvent === '1') {
            return [
                'plural' => 'invites',
                'plural_cap' => 'Invités',
                'singular' => 'invite',
                'singular_cap' => 'Invité',
                'notify' => "Notifier l'invité",
                'edit' => "Modifier l'invité",
                'delete' => "Supprimer l'invité",
                'mine' => 'Mes invités',
                'all' => 'Tous les invités',
                'search' => 'Rechercher un invité ou une table',
                'empty' => 'Aucun invité pour le moment',
                'empty_reaction' => 'Aucun invité trouvé',
                'add_plural' => 'Ajouter les invités',
                'add_plural_indef' => 'Ajout des invités',
                'add_singular' => "Ajouter l'invité",
                'new_singular' => 'Nouvel invité',
                'sheet' => 'Fiche invite',
                'manage_title' => 'Ajoutez et organisez vos invités rapidement',
                'manage_subtitle' => "Consultez, filtrez et gérez rapidement votre liste d'invités.",
                'manage_copy' => 'Centralisez les invitations, rattachez-les à une table et gardez une vue claire sur les confirmations sans quitter cette page.',
                'form_copy' => 'Renseignez le type, le nom et la table associée pour garder une liste propre dès la saisie.',
                'edit_copy' => "Ajustez le profil de l'invite et sa place a table depuis une fiche plus lisible.",
                'name_required' => "Remplissez le nom de l'invité",
                'already_exists' => 'Cet invité existe déjà',
                'pdf_title' => 'Télécharger la liste des invités',
                'pdf_by_name' => 'Classé par nom des invitées',
                'nonreaction_title' => "n'ont pas encore réagi",
                'confirm_title' => 'Suivez les réponses des invités en un coup d\'oeil',
                'confirm_copy' => 'Analysez les présences, identifiez les absences et gardez un historique clair des réponses envoyées pour votre événement.',
                'confirm_subtitle' => 'Retrouvez les messages de confirmation, les repas choisis et les notes laissées par vos invités.',
                'confirm_summary' => 'Invités',
            ];
        }

        return [
            'plural' => 'participants',
            'plural_cap' => 'Participants',
            'singular' => 'participant',
            'singular_cap' => 'Participant',
            'notify' => 'Notifier le participant',
            'edit' => 'Modifier le participant',
            'delete' => 'Supprimer le participant',
            'mine' => 'Mes participants',
            'all' => 'Tous les participants',
            'search' => 'Rechercher un participant ou une table',
            'empty' => 'Aucun participant pour le moment',
            'empty_reaction' => 'Aucun participant trouvé',
            'add_plural' => 'Ajouter des participants',
            'add_plural_indef' => 'Ajout des participants',
            'add_singular' => 'Ajouter le participant',
            'new_singular' => 'Nouveau participant',
            'sheet' => 'Fiche participant',
            'manage_title' => 'Ajoutez et organisez vos participants rapidement',
            'manage_subtitle' => 'Consultez, filtrez et gérez rapidement votre liste de participants.',
            'manage_copy' => 'Centralisez les inscriptions, rattachez-les à une table et gardez une vue claire sur les confirmations sans quitter cette page.',
            'form_copy' => 'Renseignez le type, le nom et la table associée pour garder une liste propre dès la saisie.',
            'edit_copy' => 'Ajustez le profil du participant et sa place a table depuis une fiche plus lisible.',
            'name_required' => 'Remplissez le nom du participant',
            'already_exists' => 'Ce participant existe déjà',
            'pdf_title' => 'Télécharger la liste des participants',
            'pdf_by_name' => 'Classé par nom des participants',
            'nonreaction_title' => "n'ont pas encore répondu",
            'confirm_title' => 'Suivez les réponses des participants en un coup d\'oeil',
            'confirm_copy' => 'Analysez les présences, identifiez les absences et gardez un historique clair des inscriptions envoyées pour votre événement.',
            'confirm_subtitle' => 'Retrouvez les messages de confirmation, les repas choisis et les notes laissées par vos participants.',
            'confirm_summary' => 'Participants',
        ];
    }

    public static function supportsTableAndMenu(string $eventTypeName, string $typeEvent = ''): bool
    {
        if ($typeEvent === '1') {
            return true;
        }

        $normalizedTypeName = self::normalizeEventTypeLabel($eventTypeName);

        foreach (['formation', 'conference', 'concert'] as $hiddenType) {
            if ($normalizedTypeName !== '' && strpos($normalizedTypeName, $hiddenType) !== false) {
                return false;
            }
        }

        return true;
    }

    private static function normalizeEventTypeLabel(string $value): string
    {
        $value = trim(mb_strtolower($value, 'UTF-8'));

        return strtr($value, [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ÿ' => 'y',
            'œ' => 'oe',
            'æ' => 'ae',
        ]);
    }

    public static function getGuestStats(PDO $pdo, string $eventId, ?string $dateEvent): array
    {
        if ($eventId === '') {
            return [
                'total_inv' => 0,
                'total_invconf' => 0,
                'total_invpre' => 0,
                'total_invabs' => 0,
            ];
        }

        $eventIdInt = (int) $eventId;
        $totalInv = InviteStatsService::weightedCount($pdo, $eventIdInt);
        $totalInvConf = ConfirmationService::countByPresence($pdo, $eventIdInt);
        $totalInvPre = InviteStatsService::weightedCount($pdo, $eventIdInt, 'oui');
        $totalInvAbs = 0;

        if ($dateEvent && date('Y-m-d H:i:s') >= $dateEvent) {
            $totalInvAbs = InviteStatsService::weightedCount($pdo, $eventIdInt, null, true);
        }

        return [
            'total_inv' => $totalInv,
            'total_invconf' => $totalInvConf,
            'total_invpre' => $totalInvPre,
            'total_invabs' => $totalInvAbs,
        ];
    }

    public static function getUserEventOptions(PDO $pdo, string $currentEventId, string $userId): array
    {
        if ($userId === '') {
            return [];
        }

        $stmt = $pdo->prepare('SELECT * FROM events WHERE cod_event != ? AND (cod_user = ? OR cod_user2 = ?) ORDER BY date_event DESC');
        $stmt->execute([$currentEventId, $userId, $userId]);

        $options = [];
        while ($event = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $eventTypeName = self::findEventTypeName($pdo, (string) ($event['type_event'] ?? ''));
            $typeSuffix = ($event['type_event'] ?? '') === '1' ? ' ' . ($event['type_mar'] ?? '') : '';
            $options[] = [
                'cod_event' => $event['cod_event'],
                'label' => $eventTypeName . $typeSuffix . ', le ' . date('d/m/Y à H:i', strtotime((string) $event['date_event'])),
            ];
        }

        return $options;
    }

    private static function promoteEventPosition(PDO $pdo, string $eventId, string $userId): void
    {
        $stmt = $pdo->prepare('UPDATE events SET position = :position WHERE cod_event = :cod_event AND cod_user = :cod_user');
        $stmt->bindValue(':position', '1');
        $stmt->bindValue(':cod_event', $eventId);
        $stmt->bindValue(':cod_user', $userId);
        $stmt->execute();

        $stmt = $pdo->prepare('UPDATE events SET position = :position WHERE cod_event != :cod_event AND cod_user = :cod_user');
        $stmt->bindValue(':position', '');
        $stmt->bindValue(':cod_event', $eventId);
        $stmt->bindValue(':cod_user', $userId);
        $stmt->execute();
    }

    private static function findEventTypeName(PDO $pdo, string $typeEvent): string
    {
        static $cache = [];

        if ($typeEvent === '') {
            return '';
        }

        if (!array_key_exists($typeEvent, $cache)) {
            $stmt = $pdo->prepare('SELECT nom FROM evenement WHERE cod_event = ?');
            $stmt->execute([$typeEvent]);
            $cache[$typeEvent] = (string) ($stmt->fetchColumn() ?: '');
        }

        return $cache[$typeEvent];
    }

    private static function resolveSiteContent(PDO $pdo, array $event, string $eventId, string $typeEvent, string $photo, string $heartPhoto): array
    {
        if ($typeEvent === '1') {
            $stmt = $pdo->prepare('SELECT * FROM websitewedgeneral WHERE cod_event = ?');
            $stmt->execute([$eventId]);
            $site = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($site) {
                return [
                    'text_sdd' => $site['text_sdd'],
                    'text_lovestory' => $site['text_lovestory'],
                    'img_debulov' => $site['img_debulov'],
                    'img_finulov' => $site['img_finulov'],
                    'img_event' => $site['img_event'],
                ];
            }

            $groom = $event['prenom_epoux'] ?? '';
            $bride = $event['prenom_epouse'] ?? '';

            return [
                'text_sdd' => 'Le moment tant attendu est enfin arrivé, ' . $groom . ' a pris son courage et a demandé la main de ' . $bride . '. Ce geste, chargé d’émotion et symbolique marque le début d’un nouveau chapitre de leur histoire.',
                'text_lovestory' => 'C’est ici que tout a commencé, Une rencontre de courtoisie qui a donné naissance à une belle histoire d’amour… Deux regards tournés vers l’avenir pour ne plus jamais se quitter …',
                'img_debulov' => $photo,
                'img_finulov' => $heartPhoto,
                'img_event' => $photo,
            ];
        }

        if ($typeEvent === '2') {
            return [
                'text_sdd' => 'Je célèbre une nouvelle année de ma vie. Je prends un moment pour me réjouir des expériences, des leçons et des belles rencontres de l\'année écoulée. En ce jour spécial, je demande à Dieu de me protéger, de me guider et de m\'accorder la sagesse pour faire face aux défis à venir. Que cette nouvelle année soit remplie de joie, de santé et d\'amour.',
                'text_lovestory' => '',
                'img_debulov' => $photo,
                'img_finulov' => $heartPhoto,
                'img_event' => $photo,
            ];
        }

        return [
            'text_sdd' => '',
            'text_lovestory' => '',
            'img_debulov' => $photo,
            'img_finulov' => $heartPhoto,
            'img_event' => $photo,
        ];
    }
}