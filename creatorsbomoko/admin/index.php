<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/PHPMailer/src/Exception.php';
require_once dirname(__DIR__, 2) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__, 2) . '/PHPMailer/src/SMTP.php';
require_once dirname(__DIR__, 2) . '/qrscan/phpqrcode/qrlib.php';

$eventName = 'Creators Bomoko 2026';
$eventDates = '5–6 juin 2026';
$eventLocation = 'Musée National de la RDC, Kinshasa';
$adminEmail = getenv('CREATORSBOMOKO_ADMIN_EMAIL') ?: 'creatorsbomoko@invitationspeciale.com';
$adminPassword = getenv('CREATORSBOMOKO_ADMIN_PASSWORD') ?: (getenv('CREATORSBOMOKO_SMTP_PASSWORD') ?: 'Huberusbb_01');
$statuses = [
    'nouvelle' => 'Nouvelle',
    'en_etude' => 'En étude',
    'preselectionnee' => 'Présélectionnée',
    'confirmee' => 'Confirmée',
    'rejetee' => 'Rejetée',
];

function cb_admin_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cb_admin_clean(string $value, int $maxLength = 1000): string
{
    $value = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    return substr($value, 0, $maxLength);
}

function cb_admin_ensure_tables(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS participants_cbomoko (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    submission_id VARCHAR(60) NOT NULL,
    submitted_at DATETIME NOT NULL,
    nom_complet VARCHAR(180) NOT NULL,
    email VARCHAR(190) NOT NULL,
    telephone VARCHAR(60) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    profession VARCHAR(160) NOT NULL,
    organisation VARCHAR(180) DEFAULT NULL,
    domaine VARCHAR(220) NOT NULL,
    plateformes TEXT NOT NULL,
    liens_plateformes TEXT NOT NULL,
    experience VARCHAR(80) NOT NULL,
    participation_similaire VARCHAR(10) NOT NULL,
    motivation TEXT NOT NULL,
    thematique VARCHAR(240) NOT NULL,
    attentes TEXT NOT NULL,
    disponible_presentiel VARCHAR(10) NOT NULL,
    infos_futures VARCHAR(10) NOT NULL,
    besoins_specifiques VARCHAR(280) NOT NULL,
    source VARCHAR(220) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'nouvelle',
    notes_admin TEXT DEFAULT NULL,
    acces VARCHAR(10) DEFAULT NULL,
    heure_arrive DATETIME DEFAULT NULL,
    ip VARCHAR(80) DEFAULT NULL,
    user_agent VARCHAR(240) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_participants_cbomoko_submission_id (submission_id),
    KEY idx_participants_cbomoko_email (email),
    KEY idx_participants_cbomoko_status (status),
    KEY idx_participants_cbomoko_acces (acces),
    KEY idx_participants_cbomoko_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

    $stmt = $pdo->prepare('SHOW COLUMNS FROM participants_cbomoko LIKE :column_name');
    $stmt->execute([':column_name' => 'acces']);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec('ALTER TABLE participants_cbomoko ADD COLUMN acces VARCHAR(10) DEFAULT NULL AFTER notes_admin');
    }

    $stmt = $pdo->prepare('SHOW COLUMNS FROM participants_cbomoko LIKE :column_name');
    $stmt->execute([':column_name' => 'heure_arrive']);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec('ALTER TABLE participants_cbomoko ADD COLUMN heure_arrive DATETIME DEFAULT NULL AFTER acces');
    }

    $stmt = $pdo->prepare('SHOW INDEX FROM participants_cbomoko WHERE Key_name = :index_name');
    $stmt->execute([':index_name' => 'idx_participants_cbomoko_acces']);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $pdo->exec('ALTER TABLE participants_cbomoko ADD INDEX idx_participants_cbomoko_acces (acces)');
    }

    $pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users_cbomoko (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_cbomoko_email (email),
    KEY idx_users_cbomoko_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
}

function cb_admin_seed_first_user(PDO $pdo, string $email, string $password): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users_cbomoko')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO users_cbomoko (full_name, email, password_hash, role, is_active) VALUES (:full_name, :email, :password_hash, :role, 1)');
    $stmt->execute([
        ':full_name' => 'Administrateur Creators Bomoko',
        ':email' => $email,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ':role' => 'admin',
    ]);
}

function cb_admin_is_logged_in(): bool
{
    return isset($_SESSION['cbomoko_admin_user_id']);
}

function cb_admin_redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function cb_admin_partner_logos(): array
{
    $directory = dirname(__DIR__) . '/images/parteners';
    if (!is_dir($directory)) {
        return [];
    }

    $logos = [];
    foreach (scandir($directory) ?: [] as $file) {
        if ($file === '.' || $file === '..' || str_starts_with($file, '.')) {
            continue;
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'], true)) {
            continue;
        }

        $logos[] = [
            'src' => '../images/parteners/' . rawurlencode($file),
            'name' => trim(pathinfo($file, PATHINFO_FILENAME)),
        ];
    }

    return $logos;
}

function cb_admin_qr_code_path(int $candidateId, string $accessUrl): string
{
    $directory = dirname(__DIR__, 2) . '/storage/creatorsbomoko/qrcodes';
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $htaccessPath = $directory . '/.htaccess';
    if (!is_file($htaccessPath)) {
        file_put_contents($htaccessPath, "Require all denied\nDeny from all\n");
    }

    $filePath = $directory . '/invitation_cbomoko_' . $candidateId . '.png';
    QRcode::png($accessUrl, $filePath, QR_ECLEVEL_M, 8, 2);

    return $filePath;
}

function cb_admin_send_invitation_email(array $candidate, string $eventName, string $eventDates, string $eventLocation): array
{
    $candidateId = (int) ($candidate['id'] ?? 0);
    $recipientEmail = filter_var((string) ($candidate['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if ($candidateId <= 0 || !$recipientEmail) {
        return ['success' => false, 'message' => 'Adresse e-mail ou participant invalide.'];
    }

    $candidateCode = trim((string) ($candidate['submission_id'] ?? '')) !== '' ? trim((string) $candidate['submission_id']) : (string) $candidateId;
    $accessUrl = 'https://invitationspeciale.com/creatorsbomoko/presence_cible.php?id=' . rawurlencode($candidateCode);
    $qrPath = cb_admin_qr_code_path($candidateId, $accessUrl);
    $smtpPassword = getenv('CREATORSBOMOKO_SMTP_PASSWORD') ?: (getenv('CREATORSBOMOKO_ADMIN_PASSWORD') ?: 'Huberusbb_01');
    $smtpHost = getenv('CREATORSBOMOKO_SMTP_HOST') ?: 'invitationspeciale.com';
    $smtpUser = getenv('CREATORSBOMOKO_SMTP_USER') ?: 'creatorsbomoko@invitationspeciale.com';
    $smtpPort = (int) (getenv('CREATORSBOMOKO_SMTP_PORT') ?: 587);

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtpPort;
        $mail->setFrom($smtpUser, 'Creators Bomoko 2026');
        $mail->addAddress((string) $recipientEmail, (string) ($candidate['nom_complet'] ?? 'Participant'));
        $mail->addReplyTo($smtpUser, 'Creators Bomoko');
        $mail->addEmbeddedImage($qrPath, 'cbomoko_access_qr', 'qrcode-creators-bomoko.png', 'base64', 'image/png');
        $mail->addAttachment($qrPath, 'QR-Code-Creators-Bomoko-2026.png');
        $mail->isHTML(true);

        $safeName = cb_admin_h((string) ($candidate['nom_complet'] ?? 'Participant'));
        $safeEventName = cb_admin_h($eventName);
        $safeEventDates = cb_admin_h($eventDates);
        $safeEventLocation = cb_admin_h($eventLocation);
        $safeReference = cb_admin_h((string) ($candidate['submission_id'] ?? ''));
        $safeDomain = cb_admin_h((string) ($candidate['domaine'] ?? ''));

        $mail->Subject = 'Invitation officielle - ' . $eventName;
        $mail->Body = '
            <div style="margin:0;padding:0;background:#f6efe4;font-family:Inter,Arial,sans-serif;color:#1f2937;">
                <div style="max-width:680px;margin:0 auto;padding:28px 14px;">
                    <div style="background:linear-gradient(135deg,#35180b,#8b4a1f 52%,#0a3a73);border-radius:28px 28px 0 0;padding:34px 28px;color:#fff;text-align:center;">
                        <div style="display:inline-block;padding:9px 14px;border:1px solid rgba(255,255,255,.32);border-radius:999px;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#fff4df;">Invitation officielle</div>
                        <h1 style="margin:18px 0 8px;font-size:34px;line-height:1.05;letter-spacing:-1px;">' . $safeEventName . '</h1>
                        <p style="margin:0;color:#fff4df;font-size:16px;">Votre candidature a été confirmée.</p>
                    </div>
                    <div style="background:#fffaf1;border:1px solid #ead8bd;border-top:0;border-radius:0 0 28px 28px;padding:30px 28px;">
                        <p style="font-size:17px;line-height:1.7;margin:0 0 18px;">Bonjour <strong>' . $safeName . '</strong>,</p>
                        <p style="font-size:16px;line-height:1.7;margin:0 0 22px;">Nous avons le plaisir de vous inviter officiellement à <strong>' . $safeEventName . '</strong>. Merci de présenter le QR Code ci-dessous à l’entrée pour confirmer votre accès.</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;margin:22px 0;background:#ffffff;border:1px solid #ead8bd;border-radius:20px;overflow:hidden;">
                            <tr>
                                <td style="padding:20px;vertical-align:top;">
                                    <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#8b4a1f;font-weight:800;margin-bottom:8px;">Date</div>
                                    <div style="font-size:16px;font-weight:800;color:#111827;">' . $safeEventDates . '</div>
                                </td>
                                <td style="padding:20px;vertical-align:top;">
                                    <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#8b4a1f;font-weight:800;margin-bottom:8px;">Lieu</div>
                                    <div style="font-size:16px;font-weight:800;color:#111827;">' . $safeEventLocation . '</div>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:20px;border-top:1px solid #ead8bd;vertical-align:top;">
                                    <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#8b4a1f;font-weight:800;margin-bottom:8px;">Référence</div>
                                    <div style="font-size:16px;font-weight:800;color:#111827;">' . $safeReference . '</div>
                                </td>
                                <td style="padding:20px;border-top:1px solid #ead8bd;vertical-align:top;">
                                    <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#8b4a1f;font-weight:800;margin-bottom:8px;">Profil</div>
                                    <div style="font-size:16px;font-weight:800;color:#111827;">' . $safeDomain . '</div>
                                </td>
                            </tr>
                        </table>
                        <div style="text-align:center;background:#ffffff;border:1px dashed #8b4a1f;border-radius:24px;padding:24px;margin:24px 0;">
                            <div style="font-size:13px;text-transform:uppercase;letter-spacing:.08em;color:#0a3a73;font-weight:900;margin-bottom:14px;">QR Code d’accès</div>
                            <img src="cid:cbomoko_access_qr" alt="QR Code accès Creators Bomoko" width="220" height="220" style="display:block;margin:0 auto 14px;border:10px solid #fff;border-radius:18px;box-shadow:0 12px 30px rgba(53,24,11,.12);">
                            <div style="color:#8b4a1f;font-weight:900;font-size:15px;line-height:1.55;">Merci de respecter strictement l’heure indiquée afin de faciliter l’accueil et le bon déroulement du programme.</div>
                        </div>
                        <div style="background:#eef7f8;border-left:5px solid #00a6a6;border-radius:16px;padding:16px 18px;margin:22px 0;color:#164e63;line-height:1.65;">
                            <strong>Instructions :</strong><br>
                            Présentez ce QR Code à l’équipe d’accueil. Une pièce d’identité peut être demandée pour vérifier votre nom.
                        </div>
                        <p style="margin:24px 0 0;line-height:1.7;">À très bientôt,<br><strong>L’équipe Creators Bomoko</strong></p>
                    </div>
                    <div style="text-align:center;color:#64748b;font-size:12px;line-height:1.6;margin-top:16px;">Creators Bomoko powered by U.S Embassy Kinshasa · Designed by Hubert Solutions</div>
                </div>
            </div>';
        $mail->AltBody = "Bonjour " . (string) ($candidate['nom_complet'] ?? 'Participant') . ",\n\nVotre candidature à {$eventName} a été confirmée.\n\nDate : {$eventDates}\nLieu : {$eventLocation}\nRéférence : " . (string) ($candidate['submission_id'] ?? '') . "\n\nPrésentez le QR Code joint à l'équipe d'accueil. Merci de respecter strictement l'heure indiquée afin de faciliter l'accueil et le bon déroulement du programme.\n\nL'équipe Creators Bomoko";
        $mail->send();

        return ['success' => true, 'message' => 'Invitation envoyée.'];
    } catch (Throwable $exception) {
        error_log('[Creators Bomoko Invitation] ' . $exception->getMessage());

        return ['success' => false, 'message' => $exception->getMessage()];
    }
}

try {
    cb_admin_ensure_tables($pdo);
    cb_admin_seed_first_user($pdo, $adminEmail, $adminPassword);
    $setupError = '';
} catch (Throwable $exception) {
    error_log('[Creators Bomoko Admin] ' . $exception->getMessage());
    $setupError = 'Impossible de préparer le backoffice. Vérifiez la base de données.';
}

if (empty($_SESSION['cbomoko_admin_csrf'])) {
    $_SESSION['cbomoko_admin_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = (string) $_SESSION['cbomoko_admin_csrf'];

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['cbomoko_admin_user_id'], $_SESSION['cbomoko_admin_name'], $_SESSION['cbomoko_admin_email'], $_SESSION['cbomoko_admin_role']);
    cb_admin_redirect('index.php');
}

$flash = '';
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'login') {
    if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
        $loginError = 'Session expirée. Veuillez réessayer.';
    } else {
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_SANITIZE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        $stmt = $pdo->prepare('SELECT * FROM users_cbomoko WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, (string) $user['password_hash'])) {
            $_SESSION['cbomoko_admin_user_id'] = (int) $user['id'];
            $_SESSION['cbomoko_admin_name'] = (string) $user['full_name'];
            $_SESSION['cbomoko_admin_email'] = (string) $user['email'];
            $_SESSION['cbomoko_admin_role'] = (string) $user['role'];
            $update = $pdo->prepare('UPDATE users_cbomoko SET last_login_at = NOW() WHERE id = :id');
            $update->execute([':id' => (int) $user['id']]);
            cb_admin_redirect('index.php');
        }

        $loginError = 'Identifiants incorrects.';
    }
}

if (cb_admin_is_logged_in() && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'update_candidate') {
    if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
        $flash = 'Session expirée. Mise à jour annulée.';
    } else {
        $candidateId = (int) ($_POST['candidate_id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'nouvelle');
        $notes = cb_admin_clean((string) ($_POST['notes_admin'] ?? ''), 2000);

        if ($candidateId > 0 && isset($statuses[$status])) {
            $candidateStmt = $pdo->prepare('SELECT * FROM participants_cbomoko WHERE id = :id LIMIT 1');
            $candidateStmt->execute([':id' => $candidateId]);
            $candidate = $candidateStmt->fetch(PDO::FETCH_ASSOC);
            $previousStatus = is_array($candidate) ? (string) ($candidate['status'] ?? '') : '';

            $stmt = $pdo->prepare('UPDATE participants_cbomoko SET status = :status, notes_admin = :notes_admin WHERE id = :id');
            $stmt->execute([
                ':status' => $status,
                ':notes_admin' => $notes,
                ':id' => $candidateId,
            ]);
            $flash = 'Candidature mise à jour.';

            if (is_array($candidate) && $status === 'confirmee' && $previousStatus !== 'confirmee') {
                $candidate['status'] = 'confirmee';
                $invitationResult = cb_admin_send_invitation_email($candidate, $eventName, $eventDates, $eventLocation);
                $flash = $invitationResult['success']
                    ? 'Candidature confirmée et invitation envoyée au participant.'
                    : 'Candidature confirmée, mais l’e-mail d’invitation n’a pas pu être envoyé automatiquement.';
            }
        }
    }
}

if (cb_admin_is_logged_in() && isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="participants_cbomoko_' . date('Ymd_His') . '.csv"');
    $output = fopen('php://output', 'wb');
    fputcsv($output, ['Référence', 'Date', 'Nom', 'Email', 'Téléphone', 'Ville', 'Âge', 'Profession', 'Domaine', 'Plateformes', 'Expérience', 'Thématique', 'Source', 'Statut']);
    $stmt = $pdo->query('SELECT * FROM participants_cbomoko ORDER BY submitted_at DESC');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['submission_id'], $row['submitted_at'], $row['nom_complet'], $row['email'], $row['telephone'], $row['ville'], $row['age'],
            $row['profession'], $row['domaine'], $row['plateformes'], $row['experience'], $row['thematique'], $row['source'], $row['status'],
        ]);
    }
    fclose($output);
    exit;
}

$stats = ['total' => 0, 'nouvelle' => 0, 'en_etude' => 0, 'preselectionnee' => 0, 'confirmee' => 0, 'rejetee' => 0];
$candidates = [];
$q = cb_admin_clean((string) ($_GET['q'] ?? ''), 120);
$statusFilter = (string) ($_GET['status'] ?? '');
$partnerLogos = cb_admin_partner_logos();

if (cb_admin_is_logged_in() && $setupError === '') {
    $stats['total'] = (int) $pdo->query('SELECT COUNT(*) FROM participants_cbomoko')->fetchColumn();
    $statusRows = $pdo->query('SELECT status, COUNT(*) AS total FROM participants_cbomoko GROUP BY status')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($statusRows as $row) {
        $key = (string) $row['status'];
        if (array_key_exists($key, $stats)) {
            $stats[$key] = (int) $row['total'];
        }
    }

    $where = [];
    $params = [];
    if ($q !== '') {
        $where[] = '(nom_complet LIKE :q OR email LIKE :q OR telephone LIKE :q OR ville LIKE :q OR domaine LIKE :q OR thematique LIKE :q)';
        $params[':q'] = '%' . $q . '%';
    }
    if ($statusFilter !== '' && isset($statuses[$statusFilter])) {
        $where[] = 'status = :status';
        $params[':status'] = $statusFilter;
    }

    $sql = 'SELECT * FROM participants_cbomoko';
    if ($where !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY submitted_at DESC LIMIT 200';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Backoffice | <?php echo cb_admin_h($eventName); ?></title>
    <link rel="icon" type="image/png" href="../images/favicom.png">
    <style>
        :root{--ink:#25140b;--muted:#725c45;--paper:#fffaf1;--cream:#fff4df;--wood:#8b4a1f;--wood-dark:#35180b;--blue:#0a3a73;--cyan:#16b5a8;--red:#d7354a;--line:#ead7bd;--shadow:0 24px 80px rgba(67,36,15,.16)}
        *{box-sizing:border-box} body{margin:0;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:var(--ink);background:radial-gradient(circle at 10% 0%,rgba(22,181,168,.16),transparent 30rem),radial-gradient(circle at 90% 0%,rgba(215,53,74,.12),transparent 28rem),linear-gradient(180deg,#fff9ee,#f4e6d2)}
        a{color:inherit}.shell{width:min(1240px,100%);margin:0 auto;padding:28px clamp(16px,4vw,46px) 54px}.brand{display:flex;align-items:center;gap:14px}.brand img{width:64px;height:64px;object-fit:contain;background:#fff;border-radius:18px;padding:7px;box-shadow:0 14px 34px rgba(53,24,11,.16)}h1{font-size:clamp(30px,4vw,52px);line-height:1;margin:0;letter-spacing:-.06em}.muted{color:var(--muted);font-weight:700}.card{background:rgba(255,250,241,.94);border:1px solid rgba(139,74,31,.14);border-radius:28px;box-shadow:var(--shadow);padding:24px}.login{min-height:100vh;display:grid;place-items:center;padding:24px}.login .card{width:min(460px,100%)}label{display:block;font-weight:900;margin:0 0 8px}input,select,textarea{width:100%;border:1px solid #d9c5a8;border-radius:14px;background:#fff;padding:12px 13px;font:inherit;color:var(--ink)}textarea{min-height:88px;resize:vertical}.field{margin-bottom:16px}.btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:999px;padding:12px 18px;font-weight:900;cursor:pointer;text-decoration:none}.btn-primary{background:linear-gradient(135deg,var(--red),var(--wood),var(--blue));color:#fff}.btn-soft{background:#f3dfc2;color:var(--wood-dark)}.alert{margin:0 0 18px;padding:13px 15px;border-radius:16px;font-weight:800}.alert-error{background:#fff1f0;color:#b42318;border:1px solid #ffccc7}.alert-ok{background:#ecfdf5;color:#0f766e;border:1px solid #a7f3d0}.stats{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:14px;margin-bottom:22px}.stat{position:relative;overflow:hidden;min-height:126px;border:1px solid rgba(255,255,255,.38);border-radius:26px;padding:18px;background:linear-gradient(145deg,#0f4c81,#0b6b8f 55%,#0f9ca8);box-shadow:0 18px 44px rgba(15,76,129,.16);color:#fff}.stat:nth-child(2){background:linear-gradient(145deg,#008b8b,#00a8a8 55%,#21c4b7);box-shadow:0 18px 44px rgba(0,139,139,.16)}.stat:nth-child(3){background:linear-gradient(145deg,#d97706,#f59e0b 55%,#fbbf24);box-shadow:0 18px 44px rgba(217,119,6,.16)}.stat:nth-child(4){background:linear-gradient(145deg,#4338ca,#2563eb 55%,#38bdf8);box-shadow:0 18px 44px rgba(37,99,235,.16)}.stat:nth-child(5){background:linear-gradient(145deg,#047857,#10b981 55%,#34d399);box-shadow:0 18px 44px rgba(4,120,87,.16)}.stat:nth-child(6){background:linear-gradient(145deg,#b91c1c,#ef4444 55%,#fb7185);box-shadow:0 18px 44px rgba(185,28,28,.16)}.stat:before{content:"";position:absolute;right:-32px;top:-34px;width:92px;height:92px;border-radius:999px;background:rgba(255,255,255,.16)}.stat strong{position:relative;display:block;font-size:clamp(32px,4vw,46px);line-height:1;color:#fff;letter-spacing:-.08em}.stat span{position:relative;display:block;margin-top:12px;font-weight:900;text-transform:uppercase;letter-spacing:.08em;font-size:12px;color:rgba(255,255,255,.86)}.stat-icon{position:relative;width:38px;height:38px;border-radius:14px;display:grid;place-items:center;margin-bottom:12px;background:rgba(255,255,255,.18);font-size:18px}.filters{display:grid;grid-template-columns:1fr 220px auto auto;gap:12px;align-items:end;margin-bottom:18px}.table-wrap{overflow:auto;border-radius:22px;border:1px solid var(--line);background:#fff}table{width:100%;border-collapse:collapse;min-width:980px}th,td{padding:14px;border-bottom:1px solid #f0dfc8;text-align:left;vertical-align:top}th{font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:#745139;background:#fff8ed}td{font-size:14px}.badge{display:inline-flex;padding:6px 10px;border-radius:999px;background:#f3dfc2;color:var(--wood-dark);font-weight:900;font-size:12px}.candidate-name{font-weight:900}.details{max-width:520px}.details summary{cursor:pointer;font-weight:900;color:var(--wood)}.manage-form{display:grid;gap:8px;min-width:220px}
        .admin-site-header{position:sticky;top:0;z-index:10;background:linear-gradient(135deg,#35180b,#8b4a1f 52%,#0a3a73);box-shadow:0 18px 44px rgba(53,24,11,.18)}
        .admin-site-header__inner{width:min(1240px,100%);margin:0 auto;padding:14px clamp(16px,4vw,46px);display:grid;grid-template-columns:220px 1fr 260px;align-items:center;gap:18px}.header-logo-left img{width:92px;max-height:78px;object-fit:contain;display:block}.header-title{text-align:center;color:#fff}.header-title h1{font-size:clamp(26px,3.2vw,46px);color:#fff;text-shadow:0 10px 28px rgba(0,0,0,.22)}.header-title div{margin-top:5px;color:#fff4df;font-weight:900;letter-spacing:.08em;text-transform:uppercase;font-size:12px}.header-actions{display:flex;align-items:center;justify-content:flex-end;gap:12px}.header-is-logo{width:172px;max-height:58px;object-fit:contain}.header-icon{width:42px;height:42px;border-radius:15px;display:grid;place-items:center;border:1px solid rgba(255,244,223,.28);background:rgba(255,244,223,.12);color:#fff;text-decoration:none;font-size:20px;font-weight:900}.header-icon:hover{background:rgba(255,244,223,.2)}
        .partners-section{margin:24px 0 18px;padding:0;border-radius:28px}.partners-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px}.partner-logo-card{min-height:110px;display:grid;place-items:center;padding:16px;border-radius:20px;background:#fff;border:1px solid var(--line);box-shadow:0 14px 34px rgba(67,36,15,.08)}.partner-logo-card img{max-width:100%;max-height:78px;object-fit:contain;filter:saturate(1.04)}
        .login .login-card{width:min(520px,100%);padding:0;overflow:hidden}.login-visual{padding:30px 28px;text-align:center;color:#fff;background:linear-gradient(135deg,#35180b,#8b4a1f 52%,#0a3a73)}.login-visual img{width:108px;height:108px;object-fit:contain;background:rgba(255,255,255,.96);border-radius:28px;padding:12px;box-shadow:0 18px 42px rgba(0,0,0,.18)}.login-visual h1{margin:16px 0 8px;color:#fff}.login-visual p{margin:0;color:#fff4df;font-weight:850}.login-body{padding:28px}.login-helper{margin:18px 0 0;text-align:center;color:var(--muted);font-weight:750;font-size:13px}.admin-footer{margin:28px auto 0;padding:18px 10px;color:var(--muted);display:grid;justify-items:center;gap:12px;text-align:center;background:transparent;box-shadow:none;border-radius:0}.admin-footer__text{font-weight:400;line-height:1.55}.footer-separator{width:100%;border:0;border-top:1px solid var(--line);margin:0 0 4px}.admin-footer__logos{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap}.admin-footer__logos img{width:86px;height:64px;object-fit:contain;background:transparent;border-radius:0;padding:0}.admin-footer__logos img.is-footer-logo{width:150px}
        @media(max-width:1000px){.admin-site-header__inner{grid-template-columns:110px 1fr auto}.header-is-logo{width:136px}.stats{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:900px){.filters{grid-template-columns:1fr;display:grid}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:700px){.admin-site-header__inner{grid-template-columns:1fr;justify-items:center}.header-actions{justify-content:center}.admin-footer__logos img.is-footer-logo{width:130px}}@media(max-width:560px){.stats{grid-template-columns:1fr}}
    </style>
</head>
<body>
<?php if (!cb_admin_is_logged_in()): ?>
    <main class="login">
        <form class="card login-card" method="post" action="index.php">
            <div class="login-visual">
                <img src="../images/Logo_cbomoko.png" alt="Creators Bomoko">
                <h1>Connexion</h1>
                <p>Backoffice sécurisé · Creators Bomoko 2026</p>
            </div>
            <div class="login-body">
                <?php if ($setupError !== ''): ?><div class="alert alert-error"><?php echo cb_admin_h($setupError); ?></div><?php endif; ?>
                <?php if ($loginError !== ''): ?><div class="alert alert-error"><?php echo cb_admin_h($loginError); ?></div><?php endif; ?>
                <input type="hidden" name="form_action" value="login">
                <input type="hidden" name="csrf_token" value="<?php echo cb_admin_h($csrfToken); ?>">
                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" autocomplete="username" placeholder="admin@exemple.com" required>
                </div>
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" placeholder="••••••••" required>
                </div>
                <button class="btn btn-primary" type="submit" style="width:100%">Se connecter</button>
                <div class="login-helper">Accès réservé à l’équipe d’administration.</div>
            </div>
        </form>
        <footer class="admin-footer">
            <hr class="footer-separator">
            <div class="admin-footer__text">©2026 Creators Bomoko powered by U.S Embassy Kinshasa · Designed by Hubert Solutions</div>
        </footer>
    </main>
<?php else: ?>
    <header class="admin-site-header">
        <div class="admin-site-header__inner">
            <a class="header-logo-left" href="index.php" aria-label="Creators Bomoko">
                <img src="../images/Logo_cbomoko_White.png" alt="Creators Bomoko">
            </a>
            <div class="header-title">
                <h1>Candidatures</h1>
                <div><?php echo cb_admin_h($eventName); ?></div>
            </div>
            <div class="header-actions">
                <img class="header-is-logo" src="../../event/images/Logo_invitationSpeciale_4.png" alt="Invitation Spéciale">
                <a class="header-icon" href="?action=logout" aria-label="Déconnexion" title="Déconnexion">⏻</a>
            </div>
        </div>
    </header>

    <main class="shell">
        <?php if ($flash !== ''): ?><div class="alert alert-ok"><?php echo cb_admin_h($flash); ?></div><?php endif; ?>
        <?php if ($setupError !== ''): ?><div class="alert alert-error"><?php echo cb_admin_h($setupError); ?></div><?php endif; ?>

        <section class="stats" aria-label="Statistiques">
            <div class="stat"><div class="stat-icon">Σ</div><strong><?php echo (int) $stats['total']; ?></strong><span>Total</span></div>
            <div class="stat"><div class="stat-icon">✦</div><strong><?php echo (int) $stats['nouvelle']; ?></strong><span>Nouvelles</span></div>
            <div class="stat"><div class="stat-icon">⌕</div><strong><?php echo (int) $stats['en_etude']; ?></strong><span>En étude</span></div>
            <div class="stat"><div class="stat-icon">★</div><strong><?php echo (int) $stats['preselectionnee']; ?></strong><span>Présélection</span></div>
            <div class="stat"><div class="stat-icon">✓</div><strong><?php echo (int) $stats['confirmee']; ?></strong><span>Confirmées</span></div>
            <div class="stat"><div class="stat-icon">×</div><strong><?php echo (int) $stats['rejetee']; ?></strong><span>Rejetées</span></div>
        </section>

        <section class="card">
            <form class="filters" method="get" action="index.php">
                <div>
                    <label for="q">Recherche</label>
                    <input type="search" id="q" name="q" value="<?php echo cb_admin_h($q); ?>" placeholder="Nom, e-mail, téléphone, ville...">
                </div>
                <div>
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="">Tous</option>
                        <?php foreach ($statuses as $value => $label): ?>
                            <option value="<?php echo cb_admin_h($value); ?>" <?php echo $statusFilter === $value ? 'selected' : ''; ?>><?php echo cb_admin_h($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Filtrer</button>
                <a class="btn btn-soft" href="?export=csv">Exporter CSV</a>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Candidat</th>
                        <th>Profil</th>
                        <th>Motivation</th>
                        <th>Suivi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($candidates === []): ?>
                        <tr><td colspan="4">Aucune candidature trouvée.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($candidates as $candidate): ?>
                        <tr>
                            <td>
                                <div class="candidate-name"><?php echo cb_admin_h((string) $candidate['nom_complet']); ?></div>
                                <div><?php echo cb_admin_h((string) $candidate['email']); ?></div>
                                <div><?php echo cb_admin_h((string) $candidate['telephone']); ?></div>
                                <div class="muted"><?php echo cb_admin_h((string) $candidate['ville']); ?> · <?php echo (int) $candidate['age']; ?> ans</div>
                                <div class="badge"><?php echo cb_admin_h($statuses[(string) $candidate['status']] ?? (string) $candidate['status']); ?></div>
                            </td>
                            <td>
                                <strong><?php echo cb_admin_h((string) $candidate['profession']); ?></strong><br>
                                <?php echo cb_admin_h((string) $candidate['organisation']); ?><br>
                                <span class="muted">Domaine :</span> <?php echo cb_admin_h((string) $candidate['domaine']); ?><br>
                                <span class="muted">Plateformes :</span> <?php echo cb_admin_h((string) $candidate['plateformes']); ?><br>
                                <span class="muted">Expérience :</span> <?php echo cb_admin_h((string) $candidate['experience']); ?>
                            </td>
                            <td class="details">
                                <details>
                                    <summary>Voir les réponses</summary>
                                    <p><strong>Liens :</strong><br><?php echo nl2br(cb_admin_h((string) $candidate['liens_plateformes'])); ?></p>
                                    <p><strong>Motivation :</strong><br><?php echo nl2br(cb_admin_h((string) $candidate['motivation'])); ?></p>
                                    <p><strong>Thématique :</strong><br><?php echo cb_admin_h((string) $candidate['thematique']); ?></p>
                                    <p><strong>Attentes :</strong><br><?php echo nl2br(cb_admin_h((string) $candidate['attentes'])); ?></p>
                                    <p><strong>Disponibilité :</strong> <?php echo cb_admin_h((string) $candidate['disponible_presentiel']); ?></p>
                                    <p><strong>Besoins :</strong> <?php echo cb_admin_h((string) $candidate['besoins_specifiques']); ?></p>
                                    <p><strong>Source :</strong> <?php echo cb_admin_h((string) $candidate['source']); ?></p>
                                </details>
                                <div class="muted">Réf. <?php echo cb_admin_h((string) $candidate['submission_id']); ?><br><?php echo cb_admin_h((string) $candidate['submitted_at']); ?></div>
                            </td>
                            <td>
                                <form class="manage-form" method="post" action="index.php?<?php echo http_build_query(['q' => $q, 'status' => $statusFilter]); ?>">
                                    <input type="hidden" name="form_action" value="update_candidate">
                                    <input type="hidden" name="csrf_token" value="<?php echo cb_admin_h($csrfToken); ?>">
                                    <input type="hidden" name="candidate_id" value="<?php echo (int) $candidate['id']; ?>">
                                    <select name="status" aria-label="Statut">
                                        <?php foreach ($statuses as $value => $label): ?>
                                            <option value="<?php echo cb_admin_h($value); ?>" <?php echo (string) $candidate['status'] === $value ? 'selected' : ''; ?>><?php echo cb_admin_h($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <textarea name="notes_admin" placeholder="Notes internes"><?php echo cb_admin_h((string) ($candidate['notes_admin'] ?? '')); ?></textarea>
                                    <button class="btn btn-primary" type="submit">Mettre à jour</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="partners-section" aria-label="Partenaires">
            <div class="partners-grid">
                <?php if ($partnerLogos === []): ?>
                    <div class="partner-logo-card">Aucun logo partenaire disponible.</div>
                <?php endif; ?>
                <?php foreach ($partnerLogos as $partnerLogo): ?>
                    <div class="partner-logo-card">
                        <img src="<?php echo cb_admin_h((string) $partnerLogo['src']); ?>" alt="<?php echo cb_admin_h((string) $partnerLogo['name']); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <footer class="admin-footer">
            <hr class="footer-separator">
            <div class="admin-footer__text">©2026 Creators Bomoko powered by U.S Embassy Kinshasa · Designed by Hubert Solutions</div>
        </footer>
    </main>
<?php endif; ?>
</body>
</html>
