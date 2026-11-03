<?php
session_start();
header('Content-Type: application/json');

require __DIR__ . '/../../../bootstrap/app.php';

if (!isset($_SESSION['user_phone'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Session invalide. Veuillez vous reconnecter.',
    ]);
    exit;
}

$message = trim((string) ($_POST['message'] ?? ''));

if ($message === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Veuillez saisir une question.',
    ]);
    exit;
}

if (mb_strlen($message) > 600) {
    echo json_encode([
        'success' => false,
        'message' => 'Votre question est trop longue. Limite: 600 caracteres.',
    ]);
    exit;
}

$user = UserAccountService::currentSessionUser($pdo);
$clientName = is_array($user) ? (string) ($user['noms'] ?? '') : '';
$chatbotConfig = (array) ($isAppConfig['client_profile_chatbot'] ?? []);

if (empty($chatbotConfig['enabled'])) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'Assistant IA indisponible. Activez la configuration LLM pour continuer.',
    ]);
    exit;
}

try {
    $assistant = ClientProfileLlmService::generate($message, $clientName, $chatbotConfig);
    $source = (string) ($chatbotConfig['provider'] ?? 'llm');
} catch (RuntimeException $exception) {
    $errorMessage = (string) $exception->getMessage();
    error_log('[profile_ai_chat] LLM indisponible: ' . $errorMessage);

    $normalizedError = strtolower($errorMessage);
    $clientErrorMessage = 'Le service IA est temporairement indisponible. Verifiez la configuration API puis reessayez.';
    if (
        str_contains($normalizedError, 'insufficient_quota')
        || str_contains($normalizedError, 'credit_balance_exhausted')
        || str_contains($normalizedError, 'no credits remaining')
    ) {
        $clientErrorMessage = 'Le quota API est epuise. Rechargez le solde de votre compte OpenAI puis reessayez.';
    } elseif (
        str_contains($normalizedError, 'unsupported parameter')
        || str_contains($normalizedError, 'max_tokens')
        || str_contains($normalizedError, 'temperature')
    ) {
        $clientErrorMessage = 'Le modele IA configure n accepte pas ce parametre. Mettez a jour la configuration du modele puis reessayez.';
    }

    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => $clientErrorMessage,
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'reply' => (string) ($assistant['reply'] ?? ''),
    'intent' => (string) ($assistant['intent'] ?? 'llm'),
    'source' => $source,
    'suggestions' => array_values((array) ($assistant['suggestions'] ?? [])),
]);
