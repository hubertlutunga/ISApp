<?php

final class ClientProfileLlmService
{
    public static function generate(string $question, ?string $clientName, array $config): array
    {
        $provider = strtolower(trim((string) ($config['provider'] ?? 'openai')));
        $apiKey = trim((string) ($config['api_key'] ?? ''));
        $model = trim((string) ($config['model'] ?? ''));
        $timeout = max(5, (int) ($config['timeout_seconds'] ?? 20));
        $maxTokens = max(200, (int) ($config['max_tokens'] ?? 500));

        if ($apiKey === '') {
            throw new RuntimeException('Aucune cle API LLM configuree.');
        }

        if ($model === '') {
            throw new RuntimeException('Aucun modele LLM configure.');
        }

        $payload = [
            'question' => trim($question),
            'name' => trim((string) $clientName),
        ];

        if ($provider === 'anthropic') {
            return self::callAnthropic($payload, $apiKey, $model, $timeout, $maxTokens);
        }

        if ($provider !== 'openai') {
            throw new RuntimeException('Provider LLM non supporte: ' . $provider);
        }

        return self::callOpenAi($payload, $apiKey, $model, $timeout, $maxTokens);
    }

    private static function callOpenAi(array $payload, string $apiKey, string $model, int $timeout, int $maxTokens): array
    {
        $request = [
            'model' => $model,
            'temperature' => 0.2,
            'max_tokens' => $maxTokens,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => self::systemPrompt(),
                ],
                [
                    'role' => 'user',
                    'content' => self::userPrompt($payload),
                ],
            ],
        ];
        if (str_starts_with($model, 'gpt-5')) {
            $request['reasoning_effort'] = 'minimal';
        }

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ];

        $response = null;
        $lastException = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                $response = self::httpRequest(
                    'https://api.openai.com/v1/chat/completions',
                    $headers,
                    $request,
                    $timeout
                );
                break;
            } catch (RuntimeException $exception) {
                $lastException = $exception;
                $normalizedError = strtolower($exception->getMessage());

                $retryWithMaxCompletionTokens = str_contains($normalizedError, "unsupported parameter: 'max_tokens'")
                    || str_contains($normalizedError, '"param": "max_tokens"');
                if ($retryWithMaxCompletionTokens && isset($request['max_tokens'])) {
                    unset($request['max_tokens']);
                    $request['max_completion_tokens'] = $maxTokens;
                    continue;
                }

                $retryWithoutTemperature = (
                    str_contains($normalizedError, 'unsupported value')
                    && str_contains($normalizedError, '"param": "temperature"')
                ) || str_contains($normalizedError, "temperature' does not support");
                if ($retryWithoutTemperature && isset($request['temperature'])) {
                    unset($request['temperature']);
                    continue;
                }

                throw $exception;
            }
        }

        if (!is_array($response)) {
            throw $lastException ?? new RuntimeException('Le service LLM n a pas retourne de reponse exploitable.');
        }

        $decoded = json_decode($response['body'], true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Reponse OpenAI invalide (JSON mal forme).');
        }

        $content = self::extractOpenAiMessageContent($decoded['choices'][0]['message']['content'] ?? null);
        if (trim($content) === '') {
            throw new RuntimeException('OpenAI a retourne une reponse vide.');
        }

        return self::normalizeLlmJsonResponse($content);
    }

    private static function callAnthropic(array $payload, string $apiKey, string $model, int $timeout, int $maxTokens): array
    {
        $request = [
            'model' => $model,
            'system' => self::systemPrompt(),
            'max_tokens' => $maxTokens,
            'temperature' => 0.2,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => self::userPrompt($payload),
                ],
            ],
        ];

        $response = self::httpRequest(
            'https://api.anthropic.com/v1/messages',
            [
                'x-api-key: ' . $apiKey,
                'anthropic-version: 2023-06-01',
                'content-type: application/json',
            ],
            $request,
            $timeout
        );

        $decoded = json_decode($response['body'], true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Reponse Anthropic invalide (JSON mal forme).');
        }

        $blocks = $decoded['content'] ?? [];
        if (!is_array($blocks) || $blocks === []) {
            throw new RuntimeException('Anthropic a retourne une reponse vide.');
        }

        $text = '';
        foreach ($blocks as $block) {
            if (is_array($block) && (string) ($block['type'] ?? '') === 'text') {
                $text .= (string) ($block['text'] ?? '');
            }
        }

        if (trim($text) === '') {
            throw new RuntimeException('Anthropic a retourne un contenu inexploitable.');
        }

        return self::normalizeLlmJsonResponse($text);
    }

    private static function normalizeLlmJsonResponse(string $content): array
    {
        $json = trim($content);
        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            $start = strpos($json, '{');
            $end = strrpos($json, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $decoded = json_decode(substr($json, $start, $end - $start + 1), true);
            }
        }

        if (!is_array($decoded)) {
            throw new RuntimeException('La reponse LLM ne respecte pas le format JSON attendu.');
        }

        $answer = trim((string) ($decoded['answer'] ?? ''));
        if ($answer === '') {
            throw new RuntimeException('La reponse LLM ne contient pas de texte exploitable.');
        }

        $suggestions = [];
        $rawSuggestions = $decoded['suggestions'] ?? [];
        if (is_array($rawSuggestions)) {
            foreach ($rawSuggestions as $suggestion) {
                $text = trim((string) $suggestion);
                if ($text !== '') {
                    $suggestions[] = $text;
                }
            }
        }

        return [
            'reply' => $answer,
            'suggestions' => array_slice(array_values(array_unique($suggestions)), 0, 4),
        ];
    }

    private static function extractOpenAiMessageContent(mixed $content): string
    {
        if (is_string($content)) {
            return $content;
        }

        if (!is_array($content)) {
            return '';
        }

        $text = '';
        foreach ($content as $part) {
            if (!is_array($part)) {
                continue;
            }

            if (($part['type'] ?? '') === 'text' && isset($part['text']) && is_string($part['text'])) {
                $text .= $part['text'];
            }
        }

        return $text;
    }

    private static function httpRequest(string $url, array $headers, array $payload, int $timeout): array
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Impossible d initialiser la requete HTTP.');
        }

        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            curl_close($ch);
            throw new RuntimeException('Impossible de serialiser la requete LLM.');
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $rawResponse = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($rawResponse === false) {
            throw new RuntimeException('Echec reseau lors de l appel LLM: ' . $curlError);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $responseSnippet = trim(substr((string) $rawResponse, 0, 500));
            throw new RuntimeException('Le service LLM a retourne une erreur HTTP ' . $httpCode . ($responseSnippet !== '' ? ' - ' . $responseSnippet : ''));
        }

        return [
            'status_code' => $httpCode,
            'body' => (string) $rawResponse,
        ];
    }

    private static function systemPrompt(): string
    {
        return 'Tu es un assistant IA pour la plateforme Invitation Speciale. '
            . 'Tu aides uniquement sur l utilisation de la plateforme: profil client, evenements, invitations, invites, confirmations, messages et configuration de site evenementiel. '
            . 'Donne une reponse pratique, concise, en francais, sous forme d etapes. '
            . 'Si la question est hors sujet, explique poliment que tu traites seulement l utilisation de la plateforme. '
            . 'Tu dois repondre STRICTEMENT en JSON valide avec ce format: {"answer":"...","suggestions":["...","..."]}. '
            . 'Aucun Markdown, aucun texte hors JSON.';
    }

    private static function userPrompt(array $payload): string
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $question = trim((string) ($payload['question'] ?? ''));

        $context = [
            'client_name' => $name,
            'question' => $question,
            'expected_domain' => 'Aide a l utilisation de la plateforme Invitation Speciale',
        ];

        $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = '{"question":"' . str_replace('"', '\"', $question) . '"}';
        }

        return 'Voici le contexte de la question client (JSON): ' . $json;
    }
}
