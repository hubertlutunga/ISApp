<?php
session_start();
header('Content-Type: application/json');

include('../../../pages/bdd.php');


	$stmtss = $pdo->prepare("SELECT * FROM is_users WHERE phone = ?");
	$stmtss->execute([$_SESSION['user_phone']]);
	$datasession = $stmtss->fetch();


$codevent = $_POST['codevent'] ?? null;
$besoin = $_POST['besoin'] ?? null;
$client = $datasession['cod_user'] ?? null;
$noms = $datasession['noms'] ?? 'Moi';
$type_user = 2;
$statut = 1;

if (!$codevent || !$besoin || !$client) {
    echo json_encode(['success' => false, 'message' => 'Données incomplètes']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO support (cod_event, cod_cli, type_user, besoin, date_env, statut) VALUES (?, ?, ?, ?, NOW(), ?)");
$stmt->execute([$codevent, $client, $type_user, $besoin, $statut]);

echo json_encode([
    'success' => true,
    'besoin' => htmlspecialchars($besoin),
    'date' => date('d M Y'),
    'noms' => $noms
]);
?>