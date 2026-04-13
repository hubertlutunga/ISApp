<?php
// ajax/search_invites.php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/../../pages/bdd.php'; // ajuste si besoin

$q   = isset($_GET['q'])   ? trim((string)$_GET['q'])   : '';
$cod = isset($_GET['cod']) ? trim((string)$_GET['cod']) : '';

if ($cod === '') {
  http_response_code(400);
  echo json_encode(['html'=>'', 'count'=>0, 'error'=>'Paramètre manquant: cod']);
  exit;
}

$like = '%'.$q.'%';

// On récupère nom_tab via LEFT JOIN; filtre sur nom, TI (sing) et nom_tab
$sql = "
  SELECT 
    i.id_inv,
    i.nom,
    i.sing,
    i.acces,
    i.siege,
    t.nom_tab
  FROM invite i
  LEFT JOIN tableevent t
    ON t.cod_tab = i.siege
   AND t.cod_event = :cod
  WHERE i.cod_mar = :cod
";
$params = [':cod'=>$cod];

if ($q !== '') {
  $sql .= " AND (i.nom LIKE :like OR i.sing LIKE :like OR t.nom_tab LIKE :like) ";
  $params[':like'] = $like;
}
$sql .= " ORDER BY i.nom ASC";

try {
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $html = '';
  $count = 0;

  foreach ($rows as $r) {
    $count++;

    $acces = (string)($r['acces'] ?? '');
    $color = ($acces === 'oui') ? '#3bbc72' : '';

    $singRaw = (string)($r['sing'] ?? '');
    $tiHtml  = ($singRaw === 'C') ? 'Couple' : (!empty($singRaw) ? 'Singleton' : '<em>Non défini</em>');

    $nomtable = $r['nom_tab'] ?? 'Non définie';

    $id_inv   = (int)$r['id_inv'];
    $nom      = htmlspecialchars($r['nom'] ?? '', ENT_QUOTES, 'UTF-8');
    $tabHtml  = htmlspecialchars($nomtable, ENT_QUOTES, 'UTF-8');
    $url      = 'index.php?page=access_cible&codinv=' . $id_inv . '&cod=' . urlencode($cod);
    $colorCss = htmlspecialchars($color, ENT_QUOTES, 'UTF-8');

    $html .= '
      <tr style="margin-bottom:15px;">
        <td align="left" style="border-bottom:1px solid #aaa;padding:7px 0;">
          <a href="'. $url .'" style="color: '. $colorCss .'">'. $nom .'</a>
        </td>
        <td align="left" style="border-bottom:1px solid #aaa;padding:7px 0;">
          <a href="'. $url .'" style="color: '. $colorCss .'">'. $tiHtml .'</a>
        </td>
        <td align="right" style="border-bottom:1px solid #aaa;padding:7px 0;">
          <a href="'. $url .'" style="color: '. $colorCss .'">'. $tabHtml .'</a>
        </td>
      </tr>
    ';
  }

  echo json_encode(['html'=>$html, 'count'=>$count], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['html'=>'', 'count'=>0, 'error'=>'Erreur serveur.']);
}
