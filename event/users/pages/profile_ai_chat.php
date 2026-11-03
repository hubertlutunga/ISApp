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
    error_log('[profile_ai_chat] LLM indisponible: ' . $exception->getMessage());
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'Le service IA est temporairement indisponible. Verifiez la configuration API puis reessayez.',
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
