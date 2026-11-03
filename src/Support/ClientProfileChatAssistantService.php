<?php

final class ClientProfileChatAssistantService
{
    public static function buildResponse(string $question, ?string $clientName = null): array
    {
        $cleanQuestion = trim($question);
        if ($cleanQuestion === '') {
            return self::fallbackResponse($clientName);
        }

        $normalizedQuestion = self::normalize($cleanQuestion);
        $bestIntent = null;
        $bestScore = 0;

        foreach (self::intents() as $intent) {
            $score = 0;
            foreach ($intent['keywords'] as $keyword) {
                if (strpos($normalizedQuestion, self::normalize((string) $keyword)) !== false) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestIntent = $intent;
                $bestScore = $score;
            }
        }

        if ($bestIntent === null || $bestScore < 2) {
            return self::fallbackResponse($clientName);
        }

        $replyPrefix = 'Voici comment faire :';
        if ($clientName !== null && trim($clientName) !== '') {
            $replyPrefix = trim($clientName) . ', voici comment faire :';
        }

        return [
            'reply' => $replyPrefix . "\n\n" . (string) $bestIntent['answer'],
            'intent' => (string) $bestIntent['id'],
            'suggestions' => (array) $bestIntent['suggestions'],
        ];
    }

    private static function fallbackResponse(?string $clientName = null): array
    {
        $greeting = 'Je peux vous guider pas a pas.';
        if ($clientName !== null && trim($clientName) !== '') {
            $greeting = trim($clientName) . ', je peux vous guider pas a pas.';
        }

        return [
            'reply' => $greeting . "\n\nPosez-moi une question precise, par exemple :\n- Comment ajouter un evenement ?\n- Comment ajouter une invitation ?\n- Comment ajouter un invite ?\n- Comment suivre les confirmations ?",
            'intent' => 'fallback',
            'suggestions' => self::defaultSuggestions(),
        ];
    }

    private static function defaultSuggestions(): array
    {
        return [
            'Comment ajouter un evenement ?',
            'Comment ajouter une invitation ?',
            'Comment ajouter un invite ?',
            'Comment suivre les confirmations ?',
        ];
    }

    private static function intents(): array
    {
        return [
            [
                'id' => 'add_event',
                'keywords' => ['ajouter', 'creer', 'evenement', 'event'],
                'answer' => "- Ouvrez le menu principal puis allez sur la page Evenements.\n- Cliquez sur Ajouter un evenement.\n- Renseignez les informations de base (nom, date, type, lieu).\n- Enregistrez puis revenez sur la liste pour verifier que l evenement apparait.",
                'suggestions' => [
                    'Comment modifier un evenement existant ?',
                    'Comment ajouter des invitations a cet evenement ?',
                ],
            ],
            [
                'id' => 'add_invitation',
                'keywords' => ['ajouter', 'invitation', 'inviter', 'lien'],
                'answer' => "- Ouvrez votre evenement actif.\n- Allez sur la section de gestion des invitations.\n- Ajoutez les informations de l invitation (nom, canal, details utiles).\n- Enregistrez puis testez le lien ou l envoi avant de diffuser a tous.",
                'suggestions' => [
                    'Comment ajouter un invite ?',
                    'Comment envoyer une invitation WhatsApp ?',
                ],
            ],
            [
                'id' => 'add_guest',
                'keywords' => ['ajouter', 'invite', 'liste', 'table'],
                'answer' => "- Ouvrez l evenement puis la page Liste des invites.\n- Cliquez sur Ajouter un invite.\n- Saisissez le nom, le contact et la table si necessaire.\n- Enregistrez puis verifiez sa presence dans la liste.",
                'suggestions' => [
                    'Comment modifier un invite ?',
                    'Comment supprimer un invite ?',
                ],
            ],
            [
                'id' => 'confirmations',
                'keywords' => ['confirmation', 'presence', 'reponse', 'statut'],
                'answer' => "- Ouvrez la page de suivi des confirmations.\n- Filtrez par statut (present, absent, sans reponse).\n- Consultez les details des reponses pour chaque invite.\n- Exportez la liste si vous devez la partager avec votre equipe.",
                'suggestions' => [
                    'Comment relancer les invites sans reponse ?',
                    'Comment voir uniquement les presents ?',
                ],
            ],
            [
                'id' => 'share_invite',
                'keywords' => ['whatsapp', 'envoyer', 'partager', 'sms'],
                'answer' => "- Ouvrez la liste des invites de votre evenement.\n- Selectionnez la personne ou le groupe cible.\n- Utilisez l action d envoi WhatsApp/SMS disponible.\n- Confirmez que le message est parti puis controlez le journal d envoi.",
                'suggestions' => [
                    'Comment verifier si un message WhatsApp a ete envoye ?',
                    'Comment personnaliser le message d invitation ?',
                ],
            ],
            [
                'id' => 'profile_security',
                'keywords' => ['profil', 'compte', 'mot de passe', 'email'],
                'answer' => "- Depuis la page Profil, mettez a jour vos informations personnelles.\n- Enregistrez les modifications du compte.\n- Utilisez le bloc Securite pour changer votre mot de passe.\n- Si vous avez oublie votre mot de passe, utilisez la page de connexion et le lien de reinitialisation.",
                'suggestions' => [
                    'Comment changer mon mot de passe ?',
                    'Comment mettre a jour mon numero de telephone ?',
                ],
            ],
        ];
    }

    private static function normalize(string $value): string
    {
        $normalized = strtolower(trim($value));
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        if ($converted !== false) {
            $normalized = strtolower($converted);
        }

        return preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
    }
}
