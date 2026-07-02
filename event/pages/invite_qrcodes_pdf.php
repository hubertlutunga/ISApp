<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);

include('../../pages/bdd.php');

require_once('pdf/fpdf.php');
require_once __DIR__ . '/../../qrscan/phpqrcode/qrlib.php';

function renderQrPdfErrorPage(string $title, string $message): void
{
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
        <style>
            body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a}
            .card{width:min(100%,560px);padding:30px;border-radius:22px;background:#fff;box-shadow:0 24px 60px rgba(15,23,42,.14);text-align:center}
            h1{margin:0 0 10px;font-size:26px}p{margin:0;color:#475569;line-height:1.7}
        </style>
    </head>
    <body><div class="card"><h1><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1><p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p></div></body>
    </html>
    <?php
    exit();
}

function qrPdfInviteLabel(array $invite): string
{
    $singValue = trim((string) ($invite['sing'] ?? ''));
    if ($singValue === 'C') {
        $prefix = 'Couple';
    } elseif ($singValue === 'Mr') {
        $prefix = 'Monsieur';
    } elseif ($singValue === 'Mme') {
        $prefix = 'Madame';
    } else {
        $prefix = '';
    }

    return trim($prefix . ' ' . ucfirst(trim((string) ($invite['nom'] ?? ''))));
}

$codevent = isset($_GET['event']) && is_scalar($_GET['event']) ? (int) $_GET['event'] : 0;
if ($codevent <= 0) {
    renderQrPdfErrorPage('Evenement introuvable', 'Le code evenement est invalide.');
}

$eventStmt = $pdo->prepare('SELECT cod_event, type_event, nomfetard, prenom_epoux, prenom_epouse, ordrepri FROM events WHERE cod_event = ? LIMIT 1');
$eventStmt->execute([$codevent]);
$dataevent = $eventStmt->fetch(PDO::FETCH_ASSOC) ?: [];
$eventStmt->closeCursor();

if ($dataevent === []) {
    renderQrPdfErrorPage('Evenement introuvable', "L'evenement demande est introuvable ou n'est plus disponible.");
}

$inviteStmt = $pdo->prepare('SELECT id_inv, nom, sing FROM invite WHERE cod_mar = :codevent ORDER BY nom ASC, id_inv ASC');
$inviteStmt->execute([':codevent' => $codevent]);
$invites = $inviteStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
$inviteStmt->closeCursor();

if ($invites === []) {
    renderQrPdfErrorPage('Aucun invite', "Aucun invite n'est disponible pour generer les QR codes.");
}

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetTitle('QR Codes invites', true);
$pdf->SetAuthor('Invitation Speciale', true);

$pageWidth = 210.0;
$pageHeight = 297.0;
$cardSize = 45.0;
$gapX = 5.0;
$gapY = 3.0;
$columns = 4;
$rows = 6;
$cardsPerPage = $columns * $rows;
$startX = ($pageWidth - (($columns * $cardSize) + (($columns - 1) * $gapX))) / 2;
$startY = ($pageHeight - (($rows * $cardSize) + (($rows - 1) * $gapY))) / 2;
$qrSize = 32.0;
$qrOffsetX = ($cardSize - $qrSize) / 2;
$qrOffsetY = 3.6;
$nameY = 36.5;
$nameHeight = 7.2;

$tempDir = __DIR__ . '/temp/';
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0775, true);
}

$tempFiles = [];
$baseQrUrl = 'https://invitationspeciale.com/site/index.php?page=access_cible&cod=' . $codevent . '&codinv=';

foreach ($invites as $index => $invite) {
    if ($index % $cardsPerPage === 0) {
        $pdf->AddPage();
    }

    $position = $index % $cardsPerPage;
    $col = $position % $columns;
    $row = intdiv($position, $columns);
    $x = $startX + ($col * ($cardSize + $gapX));
    $y = $startY + ($row * ($cardSize + $gapY));

    $inviteId = (int) ($invite['id_inv'] ?? 0);
    $codeString = $baseQrUrl . $inviteId;
    $qrFile = $tempDir . 'qr_invite_' . $codevent . '_' . $inviteId . '_' . md5($codeString) . '.png';
    QRcode::png($codeString, $qrFile, QR_ECLEVEL_M, 4, 1);
    $tempFiles[] = $qrFile;

    $pdf->SetDrawColor(221, 221, 221);
    $pdf->SetLineWidth(0.26);
    $pdf->Rect($x, $y, $cardSize, $cardSize);

    $pdf->Image($qrFile, $x + $qrOffsetX, $y + $qrOffsetY, $qrSize, $qrSize);

    $label = qrPdfInviteLabel($invite);
    $label = mb_convert_encoding($label !== '' ? $label : 'Invite', 'ISO-8859-1', 'UTF-8');
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetTextColor(15, 23, 42);
    $pdf->SetXY($x + 2.2, $y + $nameY);
    $pdf->MultiCell($cardSize - 4.4, 3.6, $label, 0, 'C');
}

$eventName = '';
if ((string) ($dataevent['type_event'] ?? '') === '1') {
    $groom = trim((string) ($dataevent['prenom_epoux'] ?? ''));
    $bride = trim((string) ($dataevent['prenom_epouse'] ?? ''));
    $eventName = (($dataevent['ordrepri'] ?? '') === 'm') ? trim($groom . ' ' . $bride) : trim($bride . ' ' . $groom);
} else {
    $eventName = trim((string) ($dataevent['nomfetard'] ?? ''));
}

$fileName = 'QR Codes Invites ' . ($eventName !== '' ? $eventName : 'Evenement ' . $codevent);
$fileName = mb_convert_encoding($fileName, 'UTF-8', 'UTF-8');
$fileName = preg_replace('/[\/:*?"<>|]/', '', $fileName) ?? 'QR Codes Invites';
$fileName = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $fileName) ?: 'QR Codes Invites';
$fileName = strtoupper($fileName);

$pdf->Output($fileName . '.pdf', 'I');

foreach (array_unique($tempFiles) as $tempFile) {
    if (is_file($tempFile)) {
        @unlink($tempFile);
    }
}
