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
$assistant = null;
$source = 'local';
$chatbotConfig = (array) ($isAppConfig['client_profile_chatbot'] ?? []);

if (!empty($chatbotConfig['enabled'])) {
    try {
        $assistant = ClientProfileLlmService::generate($message, $clientName, $chatbotConfig);
        $source = (string) ($chatbotConfig['provider'] ?? 'llm');
    } catch (RuntimeException $exception) {
        error_log('[profile_ai_chat] LLM indisponible: ' . $exception->getMessage());
    }
}

if (!is_array($assistant)) {
    $assistant = ClientProfileChatAssistantService::buildResponse($message, $clientName);
    $source = 'local_fallback';
}

echo json_encode([
    'success' => true,
    'reply' => (string) ($assistant['reply'] ?? ''),
    'intent' => (string) ($assistant['intent'] ?? 'llm'),
    'source' => $source,
    'suggestions' => array_values((array) ($assistant['suggestions'] ?? [])),
]);
