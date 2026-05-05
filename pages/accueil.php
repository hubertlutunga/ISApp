<?php

$homeWhatsappNumber = '243810678785';
$homeWhatsappUrl = 'https://wa.me/' . $homeWhatsappNumber;
$contactDestinationEmail = 'contact@invitationspeciale.com';
$contactFormState = [
  'status' => null,
  'message' => '',
];
$contactFormValues = [
  'name' => trim((string) ($_POST['name'] ?? '')),
  'email' => trim((string) ($_POST['email'] ?? '')),
  'msg' => trim((string) ($_POST['msg'] ?? '')),
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && (string) ($_POST['form_name'] ?? '') === 'home_contact') {
  $contactName = $contactFormValues['name'];
  $contactEmail = $contactFormValues['email'];
  $contactMessage = $contactFormValues['msg'];

  if ($contactName === '' || $contactEmail === '' || $contactMessage === '') {
    $contactFormState = [
      'status' => 'error',
      'message' => 'Veuillez remplir tous les champs du formulaire.',
    ];
  } elseif (filter_var($contactEmail, FILTER_VALIDATE_EMAIL) === false) {
    $contactFormState = [
      'status' => 'error',
      'message' => 'Veuillez saisir une adresse email valide.',
    ];
  } else {
    try {
      require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
      require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
      require_once __DIR__ . '/../PHPMailer/src/Exception.php';

      $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
      $message = MailerService::createMessage($mailer, $isAppConfig);

      if (method_exists($message, 'addAddress')) {
        $message->addAddress($contactDestinationEmail, 'Invitation Speciale');
      }

      if (method_exists($message, 'addReplyTo')) {
        $message->addReplyTo($contactEmail, $contactName !== '' ? $contactName : 'Visiteur');
      }

      if (method_exists($message, 'isHTML')) {
        $message->isHTML(true);
      }

      $safeName = htmlspecialchars($contactName, ENT_QUOTES, 'UTF-8');
      $safeEmail = htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8');
      $safeMessage = nl2br(htmlspecialchars($contactMessage, ENT_QUOTES, 'UTF-8'));

      $message->Subject = 'Nouveau message depuis le site Invitation Speciale';
      $message->Body = '
        <div style="font-family:Arial,sans-serif;font-size:15px;line-height:1.7;color:#22312b;">
          <p><strong>Nom :</strong> ' . $safeName . '</p>
          <p><strong>Email :</strong> ' . $safeEmail . '</p>
          <p><strong>Message :</strong><br>' . $safeMessage . '</p>
        </div>';
      $message->AltBody = "Nom : {$contactName}\nEmail : {$contactEmail}\n\nMessage :\n{$contactMessage}";
      $message->send();

      $contactFormState = [
        'status' => 'success',
        'message' => 'Votre message a bien ete envoye a contact@invitationspeciale.com.',
      ];
      $contactFormValues = [
        'name' => '',
        'email' => '',
        'msg' => '',
      ];
    } catch (Throwable $exception) {
      $contactFormState = [
        'status' => 'error',
        'message' => 'Envoi impossible pour le moment. ' . trim((string) $exception->getMessage()),
      ];
    }
  }
}

$totalEventsStmt = $pdo->query('SELECT COUNT(*) AS total_event FROM events');
$totalEventsData = $totalEventsStmt->fetch(PDO::FETCH_ASSOC);
$totalEvents = $totalEventsData ? (int) $totalEventsData['total_event'] : 0;

$modelsStmt = $pdo->prepare('SELECT image FROM modele_is WHERE siteposition IS NOT NULL ORDER BY siteposition ASC LIMIT 8');
$modelsStmt->execute();
$showcaseModels = $modelsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$weddingsStmt = $pdo->prepare(
    "SELECT date_event, prenom_epoux, prenom_epouse
     FROM events
     WHERE type_event = '1'
       AND date_event IS NOT NULL
       AND prenom_epoux IS NOT NULL
       AND prenom_epouse IS NOT NULL
     ORDER BY date_event ASC"
);
$weddingsStmt->execute();

$calendarEventsByDate = [];
$calendarMonthKeys = [];

while ($wedding = $weddingsStmt->fetch(PDO::FETCH_ASSOC)) {
    $dateValue = trim((string) ($wedding['date_event'] ?? ''));
    $prenomEpoux = trim((string) ($wedding['prenom_epoux'] ?? ''));
    $prenomEpouse = trim((string) ($wedding['prenom_epouse'] ?? ''));

    if ($dateValue === '' || $prenomEpoux === '' || $prenomEpouse === '') {
        continue;
    }

    $timestamp = strtotime($dateValue);
    if ($timestamp === false) {
        continue;
    }

    $dateKey = date('Y-m-d', $timestamp);
    $monthKey = date('Y-m', $timestamp);

    $calendarEventsByDate[$dateKey][] = [
        'epoux' => $prenomEpoux,
        'epouse' => $prenomEpouse,
    ];
    $calendarMonthKeys[$monthKey] = true;
}

$availableMonths = array_keys($calendarMonthKeys);
sort($availableMonths);

$currentMonthKey = date('Y-m');
$initialCalendarMonth = $currentMonthKey;

foreach ($availableMonths as $monthKey) {
    if ($monthKey >= $currentMonthKey) {
        $initialCalendarMonth = $monthKey;
        break;
    }
}

if ($availableMonths !== [] && $initialCalendarMonth === $currentMonthKey && !in_array($currentMonthKey, $availableMonths, true)) {
    $initialCalendarMonth = $availableMonths[0];
}

$weddingDateCount = count($calendarEventsByDate);
$catalogueCount = count($showcaseModels);
?>

<style>
  :root {
    --home-bg: #fcf8f2;
    --home-bg-soft: #f3ece1;
    --home-surface: rgba(255, 255, 255, 0.82);
    --home-surface-strong: #fffdf9;
    --home-text: #22312b;
    --home-muted: #61756d;
    --home-line: rgba(60, 90, 78, 0.14);
    --home-accent: #6f8f7b;
    --home-accent-soft: #dfeadf;
    --home-accent-strong: #506b5b;
    --home-gold: #c8a96b;
    --home-shadow: 0 24px 70px rgba(68, 86, 74, 0.12);
    --home-radius-xl: 34px;
    --home-radius-lg: 24px;
    --home-radius-md: 18px;
  }

  html {
    scroll-behavior: smooth;
  }

  body {
    background:
      radial-gradient(circle at top left, rgba(223, 234, 223, 0.9), transparent 28%),
      radial-gradient(circle at top right, rgba(244, 229, 205, 0.8), transparent 24%),
      linear-gradient(180deg, #fdfaf5 0%, #fcf8f2 50%, #f7f0e4 100%);
    color: var(--home-text);
  }

  .is-homepage {
    color: var(--home-text);
  }

  .is-homepage a {
    color: inherit;
  }

  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }

  .is-home-wrap {
    width: min(1180px, calc(100% - 40px));
    margin: 0 auto;
  }

  .is-home-header {
    position: sticky;
    top: 0;
    z-index: 60;
    background: rgba(252, 248, 242, 0.88);
    backdrop-filter: blur(14px) saturate(160%);
    border-bottom: 1px solid var(--home-line);
  }

  .is-home-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 18px 0;
    min-height: 88px;
  }

  .is-home-brand {
    display: inline-flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
  }

  .is-home-brand img {
    width: 206px;
    max-width: min(52vw, 206px);
    height: auto;
  }

  .is-home-menu {
    display: flex;
    align-items: center;
    gap: 22px;
    font-size: 14px;
    color: var(--home-muted);
  }

  .is-home-menu a {
    position: relative;
    padding-bottom: 4px;
  }

  .is-home-menu a::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 100%;
    height: 1px;
    background: var(--home-accent-strong);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.2s ease;
  }

  .is-home-menu a:hover::after,
  .is-home-menu a:focus-visible::after {
    transform: scaleX(1);
  }

  .is-home-header-cta {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-left: auto;
  }

  .is-home-icon-btn,
  .is-home-icon-btn:visited {
    width: 46px;
    min-width: 46px;
    height: 46px;
    min-height: 46px;
    padding: 0;
    border-radius: 999px;
    border: 1px solid var(--home-line);
    background: rgba(255, 255, 255, 0.82);
    color: var(--home-accent-strong);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 12px 26px rgba(86, 111, 96, 0.09);
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }

  .is-home-icon-btn:hover,
  .is-home-icon-btn:focus-visible {
    transform: translateY(-1px);
    box-shadow: 0 16px 30px rgba(86, 111, 96, 0.14);
  }

  .is-home-icon-btn i {
    font-size: 18px;
  }

  .is-home-btn,
  .is-home-btn:visited {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 46px;
    padding: 0 18px;
    border-radius: 999px;
    border: 1px solid var(--home-line);
    background: rgba(255, 255, 255, 0.74);
    color: var(--home-text);
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  }

  .is-home-btn:hover,
  .is-home-btn:focus-visible {
    transform: translateY(-1px);
    box-shadow: 0 14px 34px rgba(86, 111, 96, 0.12);
  }

  .is-home-btn-primary,
  .is-home-btn-primary:visited {
    background: linear-gradient(180deg, #7e9e88 0%, #5f7c6b 100%);
    border-color: transparent;
    color: #f9f6ef;
  }

  .is-home-btn-soft,
  .is-home-btn-soft:visited {
    background: #eef4ec;
    border-color: rgba(95, 124, 107, 0.18);
    color: var(--home-accent-strong);
  }

  .is-home-menu-toggle {
    display: none;
    width: 46px;
    min-width: 46px;
    height: 46px;
    min-height: 46px;
    padding: 0;
    border: 1px solid var(--home-line);
    background: #fffdf9;
    border-radius: 999px;
    color: var(--home-text);
    box-shadow: 0 12px 26px rgba(86, 111, 96, 0.09);
  }

  .is-home-hero {
    padding: 46px 0 28px;
  }

  .is-home-hero-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(320px, 0.92fr);
    gap: 28px;
    align-items: stretch;
  }

  .is-home-card {
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(255, 253, 249, 0.82));
    border: 1px solid rgba(87, 116, 99, 0.12);
    border-radius: var(--home-radius-xl);
    box-shadow: var(--home-shadow);
  }

  .is-home-hero-copy {
    padding: 42px;
  }

  .is-home-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 14px;
    border-radius: 999px;
    background: var(--home-accent-soft);
    color: var(--home-accent-strong);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
  }

  .is-home-hero-copy h1 {
    margin: 20px 0 18px;
    font: 700 clamp(42px, 6vw, 76px)/0.98 "Playfair Display", serif;
    letter-spacing: -0.03em;
    color: #1f2b26;
  }

  .is-home-hero-copy p {
    margin: 0;
    max-width: 660px;
    font-size: 17px;
    color: var(--home-muted);
  }

  .is-home-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 28px;
  }

  .is-home-stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-top: 32px;
  }

  .is-home-stat {
    padding: 18px;
    border-radius: var(--home-radius-md);
    background: rgba(243, 236, 225, 0.78);
    border: 1px solid rgba(87, 116, 99, 0.1);
  }

  .is-home-stat strong {
    display: block;
    font-size: 30px;
    line-height: 1;
    color: var(--home-accent-strong);
  }

  .is-home-stat span {
    display: block;
    margin-top: 8px;
    color: var(--home-muted);
    font-size: 14px;
  }

  .is-home-hero-visual {
    display: grid;
    gap: 18px;
    padding: 22px;
  }

  .is-home-visual-frame {
    overflow: hidden;
    border-radius: 28px;
    min-height: 100%;
  }

  .is-home-visual-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .is-home-visual-note {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }

  .is-home-note {
    padding: 18px;
    border-radius: 20px;
    background: rgba(245, 240, 231, 0.85);
    border: 1px solid rgba(87, 116, 99, 0.12);
  }

  .is-home-note strong {
    display: block;
    font-size: 14px;
    color: var(--home-text);
  }

  .is-home-note span {
    display: block;
    margin-top: 8px;
    color: var(--home-muted);
    font-size: 13px;
  }

  .is-home-section {
    padding: 34px 0;
  }

  .is-home-section-head {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    align-items: end;
    margin-bottom: 22px;
  }

  .is-home-section-head span {
    display: inline-block;
    color: var(--home-accent-strong);
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.14em;
  }

  .is-home-section-head h2 {
    margin: 8px 0 0;
    font: 700 clamp(30px, 4vw, 48px)/1.02 "Playfair Display", serif;
    color: #1f2b26;
  }

  .is-home-section-head p {
    margin: 10px 0 0;
    max-width: 600px;
    color: var(--home-muted);
  }

  .is-home-about-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(300px, 0.85fr);
    gap: 24px;
  }

  .is-home-about-copy,
  .is-home-about-sidebar,
  .is-home-calendar-shell,
  .is-home-catalogue-card,
  .is-home-contact-shell {
    padding: 30px;
  }

  .is-home-about-copy p,
  .is-home-contact-copy p {
    color: var(--home-muted);
    font-size: 16px;
    margin: 0 0 16px;
  }

  .is-home-about-points {
    display: grid;
    gap: 14px;
    margin-top: 24px;
  }

  .is-home-point {
    display: grid;
    grid-template-columns: 42px 1fr;
    gap: 14px;
    align-items: start;
    padding: 16px 18px;
    border-radius: 18px;
    background: rgba(243, 236, 225, 0.72);
    border: 1px solid rgba(87, 116, 99, 0.1);
  }

  .is-home-point-badge {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: #f0e4cb;
    color: #8f6e33;
    font-weight: 700;
  }

  .is-home-point strong {
    display: block;
    margin-bottom: 4px;
  }

  .is-home-point span,
  .is-home-about-meta li,
  .is-home-contact-meta li {
    color: var(--home-muted);
  }

  .is-home-about-sidebar {
    display: grid;
    gap: 18px;
  }

  .is-home-mini-card {
    padding: 22px;
    border-radius: 22px;
    background: rgba(248, 243, 234, 0.88);
    border: 1px solid rgba(87, 116, 99, 0.12);
  }

  .is-home-mini-card h3 {
    margin: 0 0 10px;
    font-size: 16px;
  }

  .is-home-about-meta,
  .is-home-contact-meta {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 10px;
  }

  .is-home-calendar-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
    gap: 22px;
    align-items: start;
  }

  .is-home-calendar-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
  }

  .is-home-calendar-toolbar h3 {
    margin: 0;
    font: 700 clamp(22px, 2.6vw, 30px)/1.1 "Playfair Display", serif;
  }

  .is-home-calendar-nav {
    display: inline-flex;
    gap: 10px;
  }

  .is-home-calendar-nav button {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 1px solid var(--home-line);
    background: #fffdf9;
    color: var(--home-accent-strong);
    font-size: 18px;
    cursor: pointer;
  }

  .is-home-calendar-nav button:disabled {
    opacity: 0.45;
    cursor: default;
  }

  .is-home-calendar-weekdays,
  .is-home-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 10px;
  }

  .is-home-calendar-weekdays {
    margin-bottom: 10px;
  }

  .is-home-calendar-weekdays span {
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #6e8378;
  }

  .is-home-calendar-day,
  .is-home-calendar-empty {
    aspect-ratio: 1 / 1;
    min-height: 84px;
  }

  .is-home-calendar-empty {
    border-radius: 20px;
    background: rgba(243, 236, 225, 0.35);
    border: 1px dashed rgba(87, 116, 99, 0.08);
  }

  .is-home-calendar-day {
    position: relative;
    padding: 14px 12px;
    border: 1px solid rgba(87, 116, 99, 0.12);
    border-radius: 20px;
    background: #fffdf9;
    text-align: left;
    cursor: pointer;
    color: var(--home-text);
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
  }

  .is-home-calendar-day:hover,
  .is-home-calendar-day:focus-visible,
  .is-home-calendar-day.is-active {
    transform: translateY(-1px);
    border-color: rgba(95, 124, 107, 0.35);
    box-shadow: 0 16px 34px rgba(80, 107, 91, 0.12);
  }

  .is-home-calendar-day.is-has-event {
    background: linear-gradient(180deg, #f7fbf5 0%, #eef5ec 100%);
  }

  .is-home-calendar-day-number {
    display: block;
    font-size: 17px;
    font-weight: 700;
  }

  .is-home-calendar-day-label {
    display: block;
    margin-top: 8px;
    font-size: 12px;
    color: var(--home-muted);
  }

  .is-home-calendar-check {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: #e7f0e3;
    color: #56725f;
    font-size: 13px;
    font-weight: 700;
  }

  .is-home-calendar-details {
    display: grid;
    gap: 16px;
    padding: 28px;
  }

  .is-home-calendar-details h3 {
    margin: 0;
    font: 700 30px/1.02 "Playfair Display", serif;
  }

  .is-home-calendar-details p {
    margin: 0;
    color: var(--home-muted);
  }

  .is-home-calendar-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 12px;
  }

  .is-home-calendar-list li {
    padding: 14px 16px;
    border-radius: 18px;
    background: rgba(243, 236, 225, 0.72);
    border: 1px solid rgba(87, 116, 99, 0.1);
  }

  .is-home-calendar-list strong {
    display: block;
    font-size: 18px;
  }

  .is-home-calendar-list span {
    display: block;
    margin-top: 5px;
    font-size: 13px;
    color: var(--home-muted);
  }

  .is-home-catalogue-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
  }

  .is-home-catalogue-grid a {
    display: block;
    overflow: hidden;
    border-radius: 24px;
    border: 1px solid rgba(87, 116, 99, 0.12);
    aspect-ratio: 0.86 / 1;
    background: #faf6ef;
  }

  .is-home-catalogue-grid img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
  }

  .is-home-catalogue-grid a:hover img,
  .is-home-catalogue-grid a:focus-visible img {
    transform: scale(1.03);
  }

  .is-home-contact-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(320px, 0.9fr);
    gap: 22px;
  }

  .is-home-contact-form {
    display: grid;
    gap: 12px;
    margin-top: 22px;
  }

  .is-home-contact-form input,
  .is-home-contact-form textarea {
    width: 100%;
    border: 1px solid rgba(87, 116, 99, 0.14);
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.88);
    color: var(--home-text);
    font: inherit;
    padding: 14px 16px;
    resize: vertical;
  }

  .is-home-contact-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 6px;
  }

  .is-home-contact-shell {
    display: grid;
    gap: 18px;
  }

  .is-home-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .is-home-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-height: 38px;
    padding: 0 14px;
    border-radius: 999px;
    background: rgba(243, 236, 225, 0.8);
    color: #4f6658;
    border: 1px solid rgba(87, 116, 99, 0.12);
    font-size: 13px;
    font-weight: 600;
  }

  .is-home-footer {
    padding: 34px 0 50px;
  }

  .is-home-footer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    padding-top: 22px;
    border-top: 1px solid var(--home-line);
    color: var(--home-muted);
    font-size: 14px;
  }

  @media (max-width: 1080px) {
    .is-home-hero-grid,
    .is-home-about-grid,
    .is-home-calendar-layout,
    .is-home-contact-grid {
      grid-template-columns: 1fr;
    }

    .is-home-catalogue-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 920px) {
    .is-home-menu {
      position: absolute;
      top: calc(100% + 10px);
      left: 20px;
      right: 20px;
      display: none;
      flex-direction: column;
      align-items: flex-start;
      gap: 14px;
      padding: 18px;
      border-radius: 24px;
      border: 1px solid var(--home-line);
      background: rgba(255, 253, 249, 0.96);
      box-shadow: var(--home-shadow);
    }

    .is-home-menu.is-open {
      display: flex;
    }

    .is-home-menu-toggle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .is-home-header-row {
      position: relative;
      flex-wrap: nowrap;
      gap: 14px;
    }
  }

  @media (max-width: 720px) {
    .is-home-wrap {
      width: min(100%, calc(100% - 24px));
    }

    .is-home-about-copy,
    .is-home-about-sidebar,
    .is-home-calendar-shell,
    .is-home-catalogue-card,
    .is-home-contact-shell,
    .is-home-hero-copy {
      padding: 22px;
    }

    .is-home-stats,
    .is-home-visual-note,
    .is-home-catalogue-grid {
      grid-template-columns: 1fr;
    }

    .is-home-hero-actions,
    .is-home-contact-actions {
      flex-direction: column;
    }

    .is-home-btn,
    .is-home-btn:visited {
      width: 100%;
    }

    .is-home-calendar-weekdays span {
      font-size: 10px;
      letter-spacing: 0.08em;
    }

    .is-home-calendar-grid,
    .is-home-calendar-weekdays {
      gap: 6px;
    }

    .is-home-calendar-day,
    .is-home-calendar-empty {
      min-height: 66px;
      border-radius: 16px;
    }

    .is-home-calendar-day {
      padding: 10px 8px;
    }

    .is-home-calendar-day-label {
      font-size: 11px;
    }

    .is-home-footer-row {
      flex-direction: column;
      align-items: flex-start;
    }

    .is-home-brand img {
      width: 170px;
      max-width: 52vw;
    }

    .is-home-header-row {
      min-height: 78px;
      padding: 14px 0;
    }

    .is-home-menu {
      left: 12px;
      right: 12px;
    }
  }
</style>

<div class="is-homepage">
  <header class="is-home-header">
    <div class="is-home-wrap is-home-header-row">
      <a class="is-home-brand" href="index.php?page=accueil" aria-label="Accueil Invitation Speciale">
        <img src="images/Logo_invitationSpeciale_SF.png" alt="Invitation Speciale" />
      </a>

      <nav class="is-home-menu" id="homePrimaryMenu" aria-label="Navigation principale">
        <a href="index.php?page=accueil">Accueil</a>
        <a href="#a-propos">A propos</a>
        <a href="#catalogue">Catalogue</a>
        <a href="#contact">Contact</a>
        <a href="event/index.php?page=login">Mon compte</a>
      </nav>

      <div class="is-home-header-cta">
        <a class="is-home-icon-btn" href="<?php echo htmlspecialchars($homeWhatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" aria-label="WhatsApp Invitation Speciale">
          <i class="fab fa-whatsapp" aria-hidden="true"></i>
        </a>
        <button class="is-home-menu-toggle" id="homeMenuToggle" type="button" aria-expanded="false" aria-controls="homePrimaryMenu" aria-label="Ouvrir le menu">
          <i class="fas fa-bars" aria-hidden="true"></i>
        </button>
      </div>
    </div>
  </header>

  <section class="is-home-hero" id="accueil">
    <div class="is-home-wrap is-home-hero-grid">
      <div class="is-home-card is-home-hero-copy">
        <span class="is-home-kicker">Invitation haut de gamme · mariage · digital</span>
        <h1>Une nouvelle vitrine plus lumineuse, raffinee et pensee pour les evenements d'exception.</h1>
        <p>
          Invitation Speciale concoit des invitations premium, des sites de mariage elegants et des experiences digitales sur mesure.
          L'approche reste editoriale, delicate et claire, avec une presentation qui inspire confiance autant sur mobile que sur ordinateur.
        </p>

        <div class="is-home-hero-actions">
          <a class="is-home-btn is-home-btn-primary" href="event/index.php?page=commande" target="_blank" rel="noopener">Commander une invitation</a>
          <a class="is-home-btn" href="#calendrier-mariages">Voir le calendrier des mariages</a>
        </div>

        <div class="is-home-stats" aria-label="Chiffres cles">
          <div class="is-home-stat">
            <strong><?php echo $totalEvents; ?></strong>
            <span>evenements accompagnes depuis la plateforme</span>
          </div>
          <div class="is-home-stat">
            <strong><?php echo $weddingDateCount; ?></strong>
            <span>dates de mariage visibles dans le calendrier</span>
          </div>
          <div class="is-home-stat">
            <strong><?php echo $catalogueCount; ?></strong>
            <span>modeles mis en avant dans la selection du moment</span>
          </div>
        </div>
      </div>

      <div class="is-home-card is-home-hero-visual">
        <figure class="is-home-visual-frame" aria-label="Apercu Invitation Speciale">
          <img src="images/IMG_2415.png" alt="Showroom Invitation Speciale" />
        </figure>

        <div class="is-home-visual-note">
          <div class="is-home-note">
            <strong>Direction artistique plus claire</strong>
            <span>Un ton creme, vegetal et editorial, avec un rendu premium et lumineux qui reste sobre, lisible et distinctif.</span>
          </div>
          <div class="is-home-note">
            <strong>Lecture simple des mariages</strong>
            <span>Chaque date de mariage cochee dans le calendrier ouvre instantanement les prenoms des maries au survol ou au clic.</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="is-home-section" id="a-propos">
    <div class="is-home-wrap">
      <div class="is-home-section-head">
        <div>
          <span>A propos</span>
          <h2>Invitation Speciale, une maison dediee aux invitations haut de gamme.</h2>
        </div>
        <p>
          Une entite de Hubert Solutions consacree a la creation, la conception et la mise en scene des invitations premium pour les mariages,
          receptions privees et evenements d'image.
        </p>
      </div>

      <div class="is-home-about-grid">
        <article class="is-home-card is-home-about-copy">
          <p>
            Creee en 2024, Invitation Speciale est une entite de Hubert Solutions dediee a la creation et a la conception d'invitations haut de gamme,
            physiques et digitales, pour une clientele exigeante en Republique Democratique du Congo et a l'international. Notre ambition est simple :
            transformer une invitation en premiere experience de marque pour un couple, une famille ou une organisation.
          </p>
          <p>
            Basee a Kinshasa, dans la commune de la Gombe, sur le Boulevard du 30 Juin, a l'Immeuble Interfina au 2e niveau,
            notre equipe travaille des directions artistiques elegantes, des finitions premium et des dispositifs digitaux utiles : e-invitations,
            confirmations de presence, QR codes et sites d'evenement plus soignes. Nous accompagnons aussi bien les ceremonies locales que les projets destines
            a la diaspora et nous expedions a l'etranger selon les besoins du client et la nature du support commande.
          </p>
          <p>
            Ce positionnement nous permet de reunir le sens du detail, la rigueur technique et une execution adaptee aux attentes d'une clientele qui veut une image nette,
            un rendu noble et une experience harmonieuse avant, pendant et apres l'evenement.
          </p>

          <div class="is-home-about-points">
            <div class="is-home-point">
              <div class="is-home-point-badge">01</div>
              <div>
                <strong>Conception premium</strong>
                <span>Invitations papier, digitales et sites d'evenement avec un langage visuel haut de gamme, coherent et memorisable.</span>
              </div>
            </div>
            <div class="is-home-point">
              <div class="is-home-point-badge">02</div>
              <div>
                <strong>Production locale, rayonnement international</strong>
                <span>Nous accompagnons des clients a Kinshasa, partout en RDC et pour des expeditions ou coordinations vers l'etranger.</span>
              </div>
            </div>
            <div class="is-home-point">
              <div class="is-home-point-badge">03</div>
              <div>
                <strong>Precision et suivi</strong>
                <span>Direction artistique, validation, personnalisation et mise a disposition des elements utiles au client et a ses invites.</span>
              </div>
            </div>
          </div>
        </article>

        <aside class="is-home-card is-home-about-sidebar">
          <div class="is-home-mini-card">
            <h3>Adresse studio</h3>
            <ul class="is-home-about-meta">
              <li>Kinshasa - Gombe</li>
              <li>Boulevard du 30 Juin</li>
              <li>Immeuble Interfina, 2e niveau</li>
            </ul>
          </div>

          <div class="is-home-mini-card">
            <h3>Ce que nous livrons</h3>
            <ul class="is-home-about-meta">
              <li>Invitations physiques premium</li>
              <li>E-invitations et experiences digitales</li>
              <li>Sites de mariage et parcours invite</li>
              <li>Support clients base en RDC et a l'etranger</li>
            </ul>
          </div>

          <div class="is-home-mini-card">
            <h3>Positionnement</h3>
            <p>
              Une signature plus couture que generaliste : chaque detail doit respirer la qualite, la clarte et la justesse.
            </p>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="is-home-section" id="calendrier-mariages">
    <div class="is-home-wrap">
      <div class="is-home-section-head">
        <div>
          <span>Calendrier</span>
          <h2>Les mariages du calendrier apparaissent date par date.</h2>
        </div>
        <p>
          Les dates cochees correspondent aux mariages enregistres dans la table events. Au survol sur ordinateur ou au clic sur mobile,
          le detail affiche uniquement les prenoms des maries prevus ce jour-la.
        </p>
      </div>

      <div class="is-home-card is-home-calendar-shell">
        <div class="is-home-calendar-layout">
          <div>
            <div class="is-home-calendar-toolbar">
              <div>
                <span class="is-home-kicker">Mariages programmes</span>
                <h3 id="calendarMonthLabel">Mois</h3>
              </div>
              <div class="is-home-calendar-nav" aria-label="Navigation calendrier">
                <button type="button" id="calendarPrev" aria-label="Mois precedent">&#8249;</button>
                <button type="button" id="calendarNext" aria-label="Mois suivant">&#8250;</button>
              </div>
            </div>

            <div class="is-home-calendar-weekdays" aria-hidden="true">
              <span>Lun</span>
              <span>Mar</span>
              <span>Mer</span>
              <span>Jeu</span>
              <span>Ven</span>
              <span>Sam</span>
              <span>Dim</span>
            </div>

            <div class="is-home-calendar-grid" id="weddingCalendarGrid"></div>
          </div>

          <aside class="is-home-card is-home-calendar-details" id="calendarDetails">
            <span class="is-home-kicker">Detail du jour</span>
            <h3 id="calendarDetailsTitle">Selectionnez une date</h3>
            <p id="calendarDetailsSubtitle">Le detail des mariages apparait ici pour la date survolee ou choisie.</p>
            <ul class="is-home-calendar-list" id="calendarDetailsList"></ul>
          </aside>
        </div>
      </div>
    </div>
  </section>

  <section class="is-home-section" id="catalogue">
    <div class="is-home-wrap">
      <div class="is-home-section-head">
        <div>
          <span>Catalogue</span>
          <h2>Une selection de modeles pour poser la tonalite de votre evenement.</h2>
        </div>
        <p>
          Cette grille met en avant des modeles visibles en facade pour guider le visiteur sans saturer la lecture.
        </p>
      </div>

      <div class="is-home-card is-home-catalogue-card">
        <div class="is-home-catalogue-grid" aria-label="Selection de modeles Invitation Speciale">
          <?php foreach ($showcaseModels as $model): ?>
            <a href="index.php?page=catalogue" aria-label="Voir le catalogue Invitation Speciale">
              <img src="event/images/modeleis/<?php echo htmlspecialchars((string) $model['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Modele Invitation Speciale" />
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="is-home-section" id="contact">
    <div class="is-home-wrap">
      <div class="is-home-section-head">
        <div>
          <span>Contact</span>
          <h2>Parlons de votre invitation, de votre mariage ou de votre prochaine reception.</h2>
        </div>
        <p>
          Vous pouvez demarrer une commande, demander un devis ou nous presenter un projet a produire localement ou pour l'etranger.
        </p>
      </div>

      <div class="is-home-contact-grid">
        <div class="is-home-card is-home-contact-shell">
          <div class="is-home-contact-copy">
            <p>
              L'equipe vous repond pour les conceptions premium, les e-invitations, les sites de mariage et la coordination de production.
              Pour les commandes internationales, nous adaptons la preparation et l'expedition au support retenu.
            </p>
          </div>

          <form class="is-home-contact-form" id="contactForm" method="post" action="" aria-label="Formulaire de contact Invitation Speciale">
            <input type="hidden" name="form_name" value="home_contact" />

            <label class="sr-only" for="homeContactName">Nom</label>
            <input id="homeContactName" name="name" required placeholder="Votre nom" value="<?php echo htmlspecialchars($contactFormValues['name'], ENT_QUOTES, 'UTF-8'); ?>" />

            <label class="sr-only" for="homeContactEmail">Email</label>
            <input id="homeContactEmail" name="email" type="email" required placeholder="Votre email" value="<?php echo htmlspecialchars($contactFormValues['email'], ENT_QUOTES, 'UTF-8'); ?>" />

            <label class="sr-only" for="homeContactMessage">Message</label>
            <textarea id="homeContactMessage" name="msg" rows="5" required placeholder="Parlez-nous de votre evenement, de votre style souhaite et de votre calendrier."><?php echo htmlspecialchars($contactFormValues['msg'], ENT_QUOTES, 'UTF-8'); ?></textarea>

            <div class="is-home-contact-actions">
              <button class="is-home-btn is-home-btn-primary" type="submit">Envoyer le message</button>
              <a class="is-home-btn" href="event/index.php?page=commande" target="_blank" rel="noopener">Passer une commande</a>
            </div>
          </form>
        </div>

        <aside class="is-home-card is-home-contact-shell">
          <h3 style="margin:0;font:700 30px/1.02 'Playfair Display', serif;">Invitation Speciale</h3>
          <ul class="is-home-contact-meta">
            <li>Hubert Solutions - entite dediee aux invitations haut de gamme</li>
            <li>Kinshasa - Gombe, Boulevard du 30 Juin, Immeuble Interfina, 2e niveau</li>
            <li>Accompagnement local et international</li>
          </ul>

          <div class="is-home-tags">
            <span class="is-home-tag">Direction artistique</span>
            <span class="is-home-tag">Invitations premium</span>
            <span class="is-home-tag">QR & RSVP</span>
            <span class="is-home-tag">Expedition etrangere</span>
          </div>

          <div class="is-home-contact-actions">
            <a class="is-home-btn is-home-btn-soft" href="<?php echo htmlspecialchars($homeWhatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">WhatsApp</a>
            <a class="is-home-btn is-home-btn-soft" href="https://www.instagram.com/invitationspeciale/" target="_blank" rel="noopener">Instagram</a>
            <a class="is-home-btn is-home-btn-soft" href="https://www.tiktok.com/@invitationspeciale" target="_blank" rel="noopener">TikTok</a>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <footer class="is-home-footer">
    <div class="is-home-wrap is-home-footer-row">
      <small>&copy; <span id="homeYear"></span> Invitation Speciale - Hubert Solutions</small>
      <div class="is-home-tags">
        <span class="is-home-tag">RDC · Kinshasa - Gombe</span>
        <span class="is-home-tag">Cree en 2024</span>
      </div>
    </div>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const homeCalendarEvents = <?php echo json_encode($calendarEventsByDate, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  const initialCalendarMonth = <?php echo json_encode($initialCalendarMonth, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  const contactFeedback = <?php echo json_encode($contactFormState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

  document.getElementById('homeYear').textContent = new Date().getFullYear();

  const homeMenuToggle = document.getElementById('homeMenuToggle');
  const homePrimaryMenu = document.getElementById('homePrimaryMenu');

  homeMenuToggle?.addEventListener('click', function () {
    const isExpanded = this.getAttribute('aria-expanded') === 'true';
    this.setAttribute('aria-expanded', String(!isExpanded));
    homePrimaryMenu?.classList.toggle('is-open');
  });

  homePrimaryMenu?.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      homeMenuToggle?.setAttribute('aria-expanded', 'false');
      homePrimaryMenu.classList.remove('is-open');
    });
  });

  (function initWeddingCalendar() {
    const monthLabel = document.getElementById('calendarMonthLabel');
    const grid = document.getElementById('weddingCalendarGrid');
    const prevButton = document.getElementById('calendarPrev');
    const nextButton = document.getElementById('calendarNext');
    const detailsTitle = document.getElementById('calendarDetailsTitle');
    const detailsSubtitle = document.getElementById('calendarDetailsSubtitle');
    const detailsList = document.getElementById('calendarDetailsList');

    if (!monthLabel || !grid || !prevButton || !nextButton || !detailsTitle || !detailsSubtitle || !detailsList) {
      return;
    }

    const eventDates = Object.keys(homeCalendarEvents).sort();
    const monthFormatter = new Intl.DateTimeFormat('fr-FR', { month: 'long', year: 'numeric' });
    const dayFormatter = new Intl.DateTimeFormat('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    const hoverCapable = window.matchMedia('(hover: hover)').matches;

    function monthKeyFromDate(date) {
      return String(date.getFullYear()) + '-' + String(date.getMonth() + 1).padStart(2, '0');
    }

    function parseMonthKey(monthKey) {
      const parts = String(monthKey || '').split('-');
      const year = Number(parts[0]);
      const month = Number(parts[1]);
      return new Date(year, month - 1, 1);
    }

    function shiftMonth(monthKey, delta) {
      const date = parseMonthKey(monthKey);
      date.setMonth(date.getMonth() + delta);
      return monthKeyFromDate(date);
    }

    const availableMonths = Array.from(new Set(eventDates.map(function (dateKey) {
      return dateKey.slice(0, 7);
    }))).sort();

    const fallbackMonth = monthKeyFromDate(new Date());
    const state = {
      currentMonth: initialCalendarMonth || availableMonths[0] || fallbackMonth,
      activeDate: null,
    };

    const minimumMonth = availableMonths[0] || state.currentMonth;
    const maximumMonth = availableMonths[availableMonths.length - 1] || state.currentMonth;

    function updateDetails(dateKey) {
      detailsList.innerHTML = '';

      if (!dateKey || !homeCalendarEvents[dateKey] || homeCalendarEvents[dateKey].length === 0) {
        detailsTitle.textContent = 'Aucun mariage sur cette date';
        detailsSubtitle.textContent = 'Choisissez une date cochee pour afficher les prenoms des maries.';
        return;
      }

      const selectedDate = new Date(dateKey + 'T00:00:00');
      detailsTitle.textContent = dayFormatter.format(selectedDate);
      detailsSubtitle.textContent = String(homeCalendarEvents[dateKey].length) + ' mariage(s) programme(s) ce jour-la.';

      homeCalendarEvents[dateKey].forEach(function (entry, index) {
        const item = document.createElement('li');
        const title = document.createElement('strong');
        title.textContent = entry.epouse + ' & ' + entry.epoux;

        const caption = document.createElement('span');
        caption.textContent = 'Mariage ' + String(index + 1);

        item.appendChild(title);
        item.appendChild(caption);
        detailsList.appendChild(item);
      });
    }

    function setActiveDate(dateKey) {
      state.activeDate = dateKey;
      updateDetails(dateKey);

      grid.querySelectorAll('.is-home-calendar-day').forEach(function (cell) {
        cell.classList.toggle('is-active', cell.dataset.date === dateKey);
      });
    }

    function renderCalendar() {
      const currentDate = parseMonthKey(state.currentMonth);
      const year = currentDate.getFullYear();
      const monthIndex = currentDate.getMonth();
      const firstDay = new Date(year, monthIndex, 1);
      const lastDay = new Date(year, monthIndex + 1, 0);
      const offset = (firstDay.getDay() + 6) % 7;

      monthLabel.textContent = monthFormatter.format(firstDay);
      grid.innerHTML = '';

      for (let blankIndex = 0; blankIndex < offset; blankIndex += 1) {
        const blankCell = document.createElement('div');
        blankCell.className = 'is-home-calendar-empty';
        grid.appendChild(blankCell);
      }

      for (let day = 1; day <= lastDay.getDate(); day += 1) {
        const dateKey = String(year) + '-' + String(monthIndex + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        const hasEvent = Array.isArray(homeCalendarEvents[dateKey]) && homeCalendarEvents[dateKey].length > 0;
        const cell = document.createElement('button');
        cell.type = 'button';
        cell.className = 'is-home-calendar-day' + (hasEvent ? ' is-has-event' : '');
        cell.dataset.date = dateKey;

        const number = document.createElement('span');
        number.className = 'is-home-calendar-day-number';
        number.textContent = String(day);
        cell.appendChild(number);

        const label = document.createElement('span');
        label.className = 'is-home-calendar-day-label';
        label.textContent = hasEvent ? homeCalendarEvents[dateKey].length + ' mariage(s)' : 'Libre';
        cell.appendChild(label);

        if (hasEvent) {
          const check = document.createElement('span');
          check.className = 'is-home-calendar-check';
          check.textContent = '✓';
          cell.appendChild(check);

          cell.addEventListener('click', function () {
            setActiveDate(dateKey);
          });

          if (hoverCapable) {
            cell.addEventListener('mouseenter', function () {
              setActiveDate(dateKey);
            });
          }
        } else {
          cell.addEventListener('click', function () {
            setActiveDate(null);
          });
        }

        grid.appendChild(cell);
      }

      const monthEventDates = eventDates.filter(function (dateKey) {
        return dateKey.startsWith(state.currentMonth + '-');
      });

      if (!state.activeDate || !state.activeDate.startsWith(state.currentMonth + '-')) {
        state.activeDate = monthEventDates[0] || null;
      }

      prevButton.disabled = state.currentMonth <= minimumMonth;
      nextButton.disabled = state.currentMonth >= maximumMonth;
      updateDetails(state.activeDate);

      grid.querySelectorAll('.is-home-calendar-day').forEach(function (cell) {
        cell.classList.toggle('is-active', cell.dataset.date === state.activeDate);
      });
    }

    prevButton.addEventListener('click', function () {
      if (state.currentMonth <= minimumMonth) {
        return;
      }
      state.currentMonth = shiftMonth(state.currentMonth, -1);
      renderCalendar();
    });

    nextButton.addEventListener('click', function () {
      if (state.currentMonth >= maximumMonth) {
        return;
      }
      state.currentMonth = shiftMonth(state.currentMonth, 1);
      renderCalendar();
    });

    renderCalendar();
  }());

  if (contactFeedback && contactFeedback.status) {
    Swal.fire({
      title: contactFeedback.status === 'success' ? 'Message envoye' : 'Envoi impossible',
      text: contactFeedback.message || '',
      icon: contactFeedback.status === 'success' ? 'success' : 'error',
      confirmButtonText: 'Fermer',
      confirmButtonColor: '#5f7c6b',
      background: '#fffdf9',
      color: '#22312b'
    });
  }
</script>
