<?php

$stmt = $pdo->prepare("SELECT * FROM events ORDER BY cod_event DESC");
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while ($dataevent = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $lien = EventUrlService::publicUrl($dataevent, $isAppConfig);
        $shortCode = ShortUrlService::buildShortCode($dataevent, $isAppConfig);

        // Insérer dans la base de données
        $stmtaddlink = $pdo->prepare("INSERT INTO url_shortener (short_code, long_url, cod_event, date_enreg) VALUES (:short_code, :long_url, :cod_event, NOW())");
        $stmtaddlink->execute(['short_code' => $shortCode, 'long_url' => $lien, 'cod_event' => $dataevent['cod_event']]);
    }
}

?>