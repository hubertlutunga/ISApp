<?php

$catalogModels = EventOrderService::listCatalogModels($pdo);
$catalogModels = array_values(array_filter(
    $catalogModels,
    static fn(array $model): bool => ((int) ($model['is_active'] ?? 1)) === 1
));

$catalogOrder = ['invitation', 'chevalet', 'accessoires'];
$catalogLabels = [
    'invitation' => 'Invitations',
    'chevalet' => 'Chevalets de table',
    'accessoires' => 'Accessoires',
];
$catalogIntros = [
    'invitation' => 'Des modèles élégants pensés pour annoncer un moment marquant avec style, clarté et personnalité.',
    'chevalet' => 'Des supports raffinés pour habiller vos tables, guider vos invités et renforcer l’identité visuelle de votre événement.',
    'accessoires' => 'Des compléments utiles et esthétiques pour composer une papeterie cohérente du faire-part jusqu’au jour J.',
];

$groupedCatalog = [];
foreach ($catalogOrder as $catalogType) {
    $groupedCatalog[$catalogType] = [];
}

foreach ($catalogModels as $catalogModel) {
    $catalogType = (string) ($catalogModel['type_mod'] ?? '');
    if (!isset($groupedCatalog[$catalogType])) {
        $groupedCatalog[$catalogType] = [];
    }
    $groupedCatalog[$catalogType][] = $catalogModel;
}

$catalogTotal = count($catalogModels);
$catalogWhatsappNumber = '243810678785';
$catalogImageBasePath = 'event/images/modeleis/';
$catalogWhatsappMessage = rawurlencode("Bonjour Invitation Speciale, je souhaite des informations sur vos modèles du catalogue.");
$catalogWhatsappUrl = 'https://wa.me/' . $catalogWhatsappNumber . '?text=' . $catalogWhatsappMessage;
$catalogDescriptions = [
  'Autocollant Eau / Jus' => 'Une finition élégante pour habiller vos bouteilles et harmoniser votre table jusque dans les petits détails du service.',
  'Autocollant Vin / Champagne' => 'Un habillage raffiné pensé pour personnaliser vos bouteilles de réception et renforcer l’identité visuelle de l’événement.',
  'Carte de présentation' => 'Une carte chic et utile pour accueillir vos invités avec une mise en scène soignée dès leur arrivée à table.',
  'Carte de programme' => 'La solution idéale pour présenter le déroulé de la cérémonie et de la réception avec clarté, style et cohérence.',
  'Carte de remerciement' => 'Une attention délicate pour prolonger l’émotion après l’événement avec un support élégant et mémorable.',
  'Chevalet de table' => 'Un support décoratif premium pour valoriser chaque table et offrir à vos invités une présentation nette et distinguée.',
  'Digital Access' => 'Une option moderne conçue pour fluidifier l’accès à l’événement avec une expérience plus pratique, rapide et haut de gamme.',
  'Emballage Chocolat' => 'Un habillage raffiné pour transformer une petite attention gourmande en détail de réception élégant et personnalisé.',
  'Invitation électronique' => 'Un format digital chic, rapide à diffuser et parfaitement adapté aux mariages modernes, confirmations et relances élégantes.',
  'Invitation imprimée' => 'La signature intemporelle d’un événement haut de gamme, avec un support tangible qui donne immédiatement le ton de la célébration.',
  'Journal Love' => 'Un concept original au rendu éditorial qui raconte votre histoire avec une présence visuelle forte et mémorable.',
  'Medaillon de verre' => 'Une finition décorative délicate pour apporter une touche lumineuse, élégante et précieuse à votre scénographie.',
  'Menu' => 'Un support de table essentiel pour présenter le repas avec un style harmonisé à l’univers graphique de votre réception.',
  'Plan de table' => 'Une présentation claire et esthétique pour orienter vos invités avec élégance tout en valorisant la mise en place de la salle.',
  'Schéma de la salle' => 'Une solution visuelle pratique pour organiser la réception avec précision et rassurer vos invités dès leur arrivée.',
  'Sous verre' => 'Un détail décoratif distinctif qui enrichit votre table et ajoute une finition personnalisée à l’ensemble de la papeterie.',
  'Urne de cadeaux L' => 'Une urne élégante au format généreux, pensée pour s’intégrer harmonieusement à la décoration de votre événement.',
  'Urne de cadeaux XL' => 'Un format plus imposant pour les grandes réceptions, avec une présence visuelle forte et une finition cérémonielle.',
  'Carte' => 'Un chevalet sobre et polyvalent qui convient aux tables élégantes comme aux réceptions à l’esthétique moderne.',
  'Girafe' => 'Un modèle de chevalet élancé et visuellement remarquable, idéal pour apporter du relief et une présence décorative assumée.',
  'Traditionel' => 'Un chevalet au style classique et rassurant, parfait pour les réceptions qui recherchent un rendu intemporel et distingué.',
  'Triangle' => 'Un chevalet graphique et contemporain qui apporte une touche architecturée à la décoration de vos tables.',
  'Accolade' => 'Un modèle romantique à la composition harmonieuse, parfait pour une annonce de mariage douce et raffinée.',
  'Amour scellé' => 'Un design pensé pour exprimer l’engagement et l’émotion avec une présence visuelle noble et cérémonielle.',
  'Au fond du coeur' => 'Une invitation expressive et sentimentale, idéale pour transmettre un message fort avec élégance.',
  'Belle mère' => 'Un modèle singulier à forte personnalité, conçu pour marquer les esprits avec une silhouette originale.',
  'Blouse' => 'Une invitation au style structuré et habillé, avec une allure soignée qui évoque le raffinement textile.',
  'Calebasse d\'amour - gros' => 'Un format généreux et symbolique, parfait pour une présentation chaleureuse à l’inspiration culturelle forte.',
  'Calebasse d\'amour - petit' => 'Une version plus compacte qui conserve tout le charme symbolique du modèle dans un format délicat et élégant.',
  'Calque d\'amour' => 'Un modèle léger et raffiné, apprécié pour son jeu de transparence et son rendu particulièrement délicat.',
  'Carte bancaire' => 'Un design moderne et audacieux qui détourne les codes d’un format familier pour en faire une invitation marquante.',
  'Carte d\'invitation' => 'Une base élégante et efficace, idéale pour les clients qui recherchent un format classique bien présenté.',
  'CD' => 'Un modèle créatif au look original, parfait pour un événement qui veut se démarquer par son concept.',
  'Chemise miroir' => 'Une invitation premium au rendu brillant et sophistiqué, pensée pour un effet visuel fort dès l’ouverture.',
  'Coeur Croisé' => 'Un modèle romantique et travaillé qui évoque l’union, la complicité et l’élégance de la rencontre.',
  'Coeur ouvert' => 'Une invitation douce et expressive qui met en avant la sensibilité du message et la beauté du geste.',
  'Croix' => 'Un modèle particulièrement adapté aux cérémonies religieuses ou aux événements souhaitant une note solennelle.',
  'Dépliant - gros' => 'Un format généreux qui offre plus d’espace pour valoriser le texte, le programme et les éléments visuels.',
  'Dépliant - petit' => 'Une version compacte et élégante, idéale pour transmettre l’essentiel avec sobriété et clarté.',
  'Etiquette Love' => 'Une touche complémentaire raffinée pour embellir vos présents, enveloppes ou éléments de décoration.',
  'Eventail' => 'Un modèle original et très apprécié pour les réceptions estivales, à la fois pratique, chic et mémorable.',
  'Farde d\'amour L' => 'Une invitation à la présentation ample et prestigieuse, conçue pour une ouverture élégante et théâtrale.',
  'Farde d\'amour XXL' => 'La version la plus imposante de la collection, idéale pour un rendu spectaculaire et une présence haut de gamme.',
  'Huberus' => 'Un design distinctif au caractère affirmé, parfait pour les clients qui veulent un modèle exclusif et reconnaissable.',
  'Inauguration d\'amour' => 'Une invitation pensée pour célébrer une nouvelle étape avec une présence noble, festive et symbolique.',
  'La Plume' => 'Un modèle inspiré, délicat et poétique, idéal pour une papeterie qui cherche finesse et élégance littéraire.',
  'Magazine d\'amour' => 'Un concept éditorial fort, parfait pour raconter une histoire de couple avec créativité et impact visuel.',
  'Papillon' => 'Un modèle aérien et romantique qui suggère la légèreté, la transformation et la beauté du moment.',
  'Passeport multi' => 'Une invitation conceptuelle premium, idéale pour une expérience client originale et très mémorable.',
  'Passeport Simple' => 'Un format inspiré du voyage qui donne immédiatement une identité forte, moderne et ludique à votre annonce.',
  'Rail' => 'Un modèle structuré au style net et contemporain, pensé pour une présentation élégante et bien construite.',
  'Smoking Love' => 'Un design chic et habillé, parfait pour une réception formelle ou un mariage à l’esthétique très sophistiquée.',
  'Trapèze' => 'Une silhouette graphique originale qui apporte du rythme, de la modernité et une forte présence visuelle.',
];

$buildDescription = static function (array $catalogModel): string {
    $catalogType = (string) ($catalogModel['type_mod'] ?? '');
    $modelName = trim((string) ($catalogModel['nom'] ?? ''));
    $normalizedName = function_exists('mb_strtolower') ? mb_strtolower($modelName, 'UTF-8') : strtolower($modelName);

  global $catalogDescriptions;

  if (isset($catalogDescriptions[$modelName])) {
    return $catalogDescriptions[$modelName];
  }

    if ($catalogType === 'invitation') {
        if (str_contains($normalizedName, 'elect')) {
            return 'Version digitale prête à partager sur WhatsApp ou par e-mail, avec une finition moderne et rapide à diffuser.';
        }

        return 'Création imprimée au rendu soigné, idéale pour un souvenir tangible et une annonce plus cérémonielle.';
    }

    if ($catalogType === 'chevalet') {
        return 'Format décoratif conçu pour présenter élégamment les tables, les noms ou les indications du jour de réception.';
    }

    return 'Élément complémentaire pensé pour enrichir l’ensemble visuel de la commande et harmoniser la présentation finale.';
};

$formatPrice = static function ($price): string {
    if ($price === null || $price === '') {
        return 'Sur demande';
    }

    $amount = round((float) $price, 2);
    if ($amount <= 0) {
        return 'Sur demande';
    }

    return number_format($amount, 2, '.', ' ') . ' USD';
};

$buildReference = static function (array $catalogModel): string {
  $catalogType = (string) ($catalogModel['type_mod'] ?? 'modele');
  $catalogId = (int) ($catalogModel['cod_mod'] ?? 0);
  $prefixByType = [
    'invitation' => 'INV',
    'chevalet' => 'CHV',
    'accessoires' => 'ACC',
  ];
  $prefix = $prefixByType[$catalogType] ?? 'MOD';

  return $prefix . '-' . str_pad((string) max(1, $catalogId), 3, '0', STR_PAD_LEFT);
};
?>

<style>
  body.catalogue-page{
    background:
      radial-gradient(circle at top left, rgba(176, 122, 60, 0.18), transparent 28%),
      radial-gradient(circle at bottom right, rgba(92, 38, 22, 0.16), transparent 30%),
      linear-gradient(180deg, #f6efe6 0%, #f9f5ef 32%, #fffdf9 100%);
    color:#231815;
  }
  .catalogue-page-shell{
    position:relative;
    overflow:hidden;
  }
  .catalogue-page-shell::before,
  .catalogue-page-shell::after{
    content:'';
    position:absolute;
    inset:auto;
    width:420px;
    height:420px;
    border-radius:999px;
    pointer-events:none;
    filter:blur(10px);
    opacity:.38;
  }
  .catalogue-page-shell::before{
    top:-120px;
    right:-120px;
    background:radial-gradient(circle, rgba(205, 160, 88, 0.42) 0%, rgba(205, 160, 88, 0) 72%);
  }
  .catalogue-page-shell::after{
    left:-140px;
    bottom:240px;
    background:radial-gradient(circle, rgba(122, 58, 39, 0.22) 0%, rgba(122, 58, 39, 0) 74%);
  }
  .catalogue-container{
    width:min(1220px, calc(100% - 32px));
    margin:0 auto;
    position:relative;
    z-index:1;
  }
  .catalogue-hero{
    padding:44px 0 26px;
  }
  .catalogue-hero-card{
    position:relative;
    padding:38px;
    border-radius:34px;
    overflow:hidden;
    background:
      linear-gradient(135deg, rgba(70, 31, 18, 0.96) 0%, rgba(122, 58, 39, 0.94) 44%, rgba(198, 152, 82, 0.92) 100%);
    color:#fffaf2;
    box-shadow:0 34px 70px rgba(89, 43, 26, 0.23);
  }
  .catalogue-hero-card::after{
    content:'';
    position:absolute;
    inset:16px;
    border-radius:26px;
    border:1px solid rgba(255,250,242,0.14);
    pointer-events:none;
  }
  .catalogue-hero-card::before{
    content:'';
    position:absolute;
    top:-90px;
    right:-50px;
    width:280px;
    height:280px;
    background:radial-gradient(circle, rgba(255,255,255,0.26) 0%, rgba(255,255,255,0) 72%);
  }
  .catalogue-hero-grid{
    display:grid;
    grid-template-columns:minmax(0, 1.25fr) minmax(300px, .75fr);
    gap:28px;
    align-items:end;
  }
  .catalogue-kicker{
    display:inline-flex;
    align-items:center;
    gap:10px;
    padding:8px 14px;
    border-radius:999px;
    background:rgba(255,250,242,0.14);
    border:1px solid rgba(255,250,242,0.22);
    font-size:11px;
    font-weight:800;
    letter-spacing:.16em;
    text-transform:uppercase;
  }
  .catalogue-title{
    margin:18px 0 14px;
    font-family:'Playfair Display', serif;
    font-size:clamp(38px, 5vw, 68px);
    line-height:1.02;
    font-weight:700;
  }
  .catalogue-copy{
    max-width:760px;
    margin:0;
    color:rgba(255,250,242,0.86);
    font-size:17px;
    line-height:1.75;
  }
  .catalogue-manifesto{
    margin:18px 0 0;
    max-width:660px;
    color:rgba(255,250,242,0.72);
    font-size:13px;
    line-height:1.9;
    letter-spacing:.08em;
    text-transform:uppercase;
  }
  .catalogue-hero-actions{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    margin-top:26px;
  }
  .catalogue-hero-panel{
    padding:24px;
    border-radius:28px;
    background:rgba(255,250,242,0.12);
    border:1px solid rgba(255,250,242,0.18);
    backdrop-filter:blur(12px);
  }
  .catalogue-stat-grid{
    display:grid;
    gap:14px;
  }
  .catalogue-stat{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:18px;
    padding:16px 0;
    border-top:1px solid rgba(255,250,242,0.16);
  }
  .catalogue-stat:first-child{
    border-top:none;
    padding-top:0;
  }
  .catalogue-stat-label{
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:.12em;
    color:rgba(255,250,242,0.72);
  }
  .catalogue-stat-value{
    font-size:30px;
    font-weight:800;
    line-height:1;
  }
  .catalogue-hero-signature{
    margin-top:18px;
    padding-top:18px;
    border-top:1px solid rgba(255,250,242,0.16);
    color:rgba(255,250,242,0.82);
    font-size:14px;
    line-height:1.7;
  }
  .catalogue-intro-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    padding:18px 4px 30px;
    flex-wrap:wrap;
  }
  .catalogue-chip-row{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
  }
  .catalogue-chip{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:10px 14px;
    border-radius:999px;
    background:#fff7ed;
    border:1px solid rgba(164, 107, 56, 0.18);
    color:#7a3a27;
    font-size:12px;
    font-weight:700;
    letter-spacing:.06em;
    text-transform:uppercase;
    box-shadow:0 10px 24px rgba(145, 91, 45, 0.08);
  }
  .catalogue-note{
    color:#7b6a60;
    font-size:14px;
  }
  .catalogue-section{
    padding:10px 0 44px;
  }
  .catalogue-section + .catalogue-section{
    border-top:1px solid rgba(145, 91, 45, 0.12);
    padding-top:34px;
  }
  .catalogue-section-head{
    display:grid;
    grid-template-columns:minmax(0, 1fr) auto;
    gap:18px;
    align-items:end;
    margin-bottom:22px;
  }
  .catalogue-section-title{
    margin:0;
    font-family:'Playfair Display', serif;
    font-size:34px;
    line-height:1.08;
    color:#301d17;
  }
  .catalogue-section-copy{
    margin:10px 0 0;
    max-width:780px;
    color:#6d5a50;
    font-size:15px;
    line-height:1.8;
  }
  .catalogue-count{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:56px;
    min-height:56px;
    padding:0 18px;
    border-radius:999px;
    background:#fff;
    border:1px solid rgba(145, 91, 45, 0.14);
    color:#7a3a27;
    font-size:14px;
    font-weight:800;
    box-shadow:0 14px 28px rgba(130, 83, 48, 0.08);
  }
  .catalogue-grid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0, 1fr));
    gap:22px;
  }
  .catalogue-card{
    display:flex;
    flex-direction:column;
    min-height:100%;
    border-radius:28px;
    overflow:hidden;
    background:rgba(255,255,255,0.86);
    border:1px solid rgba(145, 91, 45, 0.10);
    box-shadow:0 22px 44px rgba(109, 68, 39, 0.10);
    transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease;
  }
  .catalogue-card:hover{
    transform:translateY(-6px);
    box-shadow:0 28px 52px rgba(109, 68, 39, 0.16);
    border-color:rgba(145, 91, 45, 0.24);
  }
  .catalogue-card-media{
    position:relative;
    aspect-ratio:4 / 4.6;
    overflow:hidden;
    background:linear-gradient(180deg, #f0dfcf 0%, #f7efe7 100%);
  }
  .catalogue-card-media img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition:transform .34s ease;
  }
  .catalogue-card:hover .catalogue-card-media img{
    transform:scale(1.05);
  }
  .catalogue-card-empty{
    width:100%;
    height:100%;
    display:grid;
    place-items:center;
    color:#8b6b54;
    font-weight:700;
    font-size:15px;
    letter-spacing:.08em;
    text-transform:uppercase;
    background:
      linear-gradient(135deg, rgba(255,255,255,0.62) 0%, rgba(255,255,255,0.24) 100%),
      radial-gradient(circle at top, rgba(198, 152, 82, 0.24) 0%, rgba(198, 152, 82, 0) 60%);
  }
  .catalogue-card-badge{
    position:absolute;
    left:16px;
    top:16px;
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 12px;
    border-radius:999px;
    background:rgba(48, 29, 23, 0.76);
    color:#fffaf2;
    font-size:11px;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
    backdrop-filter:blur(10px);
  }
  .catalogue-card-reference{
    position:absolute;
    right:16px;
    top:16px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:34px;
    padding:0 12px;
    border-radius:999px;
    background:rgba(255, 250, 242, 0.92);
    color:#6c402c;
    font-size:11px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
    box-shadow:0 12px 24px rgba(48, 29, 23, 0.12);
  }
  .catalogue-card-body{
    display:flex;
    flex-direction:column;
    gap:16px;
    padding:22px 22px 24px;
    flex:1;
  }
  .catalogue-card-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:14px;
  }
  .catalogue-card-name{
    margin:0;
    font-size:21px;
    line-height:1.3;
    color:#231815;
    font-weight:800;
  }
  .catalogue-card-price{
    flex-shrink:0;
    text-align:right;
    color:#7a3a27;
    font-size:18px;
    font-weight:800;
    white-space:nowrap;
  }
  .catalogue-card-price small{
    display:block;
    margin-top:5px;
    color:#9c7b63;
    font-size:11px;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
  }
  .catalogue-card-desc{
    margin:0;
    color:#6d5a50;
    font-size:14px;
    line-height:1.75;
  }
  .catalogue-card-footer{
    margin-top:auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    padding-top:16px;
    border-top:1px solid rgba(145, 91, 45, 0.10);
  }
  .catalogue-card-meta{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
  }
  .catalogue-meta-pill{
    display:inline-flex;
    align-items:center;
    padding:8px 10px;
    border-radius:999px;
    background:#fff6ea;
    color:#8b633e;
    font-size:11px;
    font-weight:700;
    letter-spacing:.06em;
    text-transform:uppercase;
  }
  .catalogue-card-action{
    display:inline-flex;
    align-items:center;
    gap:8px;
    color:#7a3a27;
    font-size:13px;
    font-weight:800;
    letter-spacing:.05em;
    text-transform:uppercase;
  }
  .catalogue-card-cta{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:100%;
    gap:10px;
    min-height:48px;
    padding:0 18px;
    border-radius:16px;
    background:linear-gradient(135deg, #7a3a27 0%, #c69852 100%);
    color:#fff;
    font-size:12px;
    font-weight:800;
    letter-spacing:.09em;
    text-transform:uppercase;
    box-shadow:0 14px 30px rgba(122, 58, 39, 0.16);
  }
  .catalogue-card-cta:hover{
    transform:translateY(-1px);
  }
  .catalogue-footer-card{
    margin:18px 0 56px;
    padding:26px 28px;
    border-radius:30px;
    background:linear-gradient(135deg, #fffaf4 0%, #f4e8da 100%);
    border:1px solid rgba(145, 91, 45, 0.14);
    box-shadow:0 20px 44px rgba(109, 68, 39, 0.08);
  }
  .catalogue-footer-grid{
    display:grid;
    grid-template-columns:minmax(0, 1fr) auto;
    gap:18px;
    align-items:center;
  }
  .catalogue-footer-title{
    margin:0 0 8px;
    font-family:'Playfair Display', serif;
    font-size:28px;
    color:#301d17;
  }
  .catalogue-footer-copy{
    margin:0;
    color:#6d5a50;
    line-height:1.8;
  }
  .catalogue-footer-actions{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    justify-content:flex-end;
  }
  .catalogue-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    min-height:50px;
    padding:0 20px;
    border-radius:999px;
    font-size:13px;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
    transition:transform .2s ease, box-shadow .2s ease, background .2s ease;
  }
  .catalogue-btn:hover{
    transform:translateY(-2px);
  }
  .catalogue-btn-primary{
    background:linear-gradient(135deg, #7a3a27 0%, #c69852 100%);
    color:#fff;
    box-shadow:0 16px 30px rgba(122, 58, 39, 0.22);
  }
  .catalogue-btn-secondary{
    background:#fff;
    color:#7a3a27;
    border:1px solid rgba(145, 91, 45, 0.16);
  }
  .catalogue-btn-whatsapp{
    background:linear-gradient(135deg, #1b8f5a 0%, #22c55e 100%);
    color:#fff;
    box-shadow:0 18px 34px rgba(34, 197, 94, 0.22);
  }
  .catalogue-whatsapp-float{
    position:fixed;
    right:18px;
    bottom:18px;
    z-index:35;
    display:inline-flex;
    align-items:center;
    gap:10px;
    min-height:58px;
    padding:0 18px;
    border-radius:999px;
    background:linear-gradient(135deg, #1b8f5a 0%, #22c55e 100%);
    color:#fff;
    font-size:13px;
    font-weight:800;
    letter-spacing:.05em;
    text-transform:uppercase;
    box-shadow:0 20px 40px rgba(27, 143, 90, 0.26);
  }
  .catalogue-whatsapp-float svg{
    flex-shrink:0;
  }
  @media (max-width: 1080px){
    .catalogue-grid{
      grid-template-columns:repeat(2, minmax(0, 1fr));
    }
  }
  @media (max-width: 900px){
    .catalogue-hero-grid,
    .catalogue-footer-grid,
    .catalogue-section-head{
      grid-template-columns:1fr;
    }
    .catalogue-hero-card{
      padding:28px;
      border-radius:28px;
    }
    .catalogue-footer-actions{
      justify-content:flex-start;
    }
  }
  @media (max-width: 720px){
    .catalogue-container{
      width:min(100%, calc(100% - 22px));
    }
    .catalogue-hero{
      padding:28px 0 18px;
    }
    .catalogue-hero-card{
      padding:22px;
    }
    .catalogue-copy{
      font-size:15px;
      line-height:1.55;
    }
    .catalogue-manifesto{
      font-size:11px;
      line-height:1.6;
    }
    .catalogue-intro-row{
      padding-bottom:18px;
    }
    .catalogue-grid{
      grid-template-columns:1fr;
      gap:12px;
    }
    .catalogue-section{
      padding:6px 0 28px;
    }
    .catalogue-section + .catalogue-section{
      padding-top:24px;
    }
    .catalogue-section-head{
      gap:12px;
      margin-bottom:14px;
    }
    .catalogue-section-title{
      font-size:26px;
    }
    .catalogue-section-copy{
      font-size:13px;
      line-height:1.6;
    }
    .catalogue-count{
      min-width:48px;
      min-height:48px;
      font-size:12px;
    }
    .catalogue-card{
      flex-direction:row;
      align-items:stretch;
      min-height:0;
      border-radius:22px;
    }
    .catalogue-card-media{
      flex:0 0 96px;
      width:96px;
      min-width:96px;
      aspect-ratio:auto;
      min-height:132px;
    }
    .catalogue-card-badge{
      left:10px;
      top:10px;
      padding:6px 8px;
      font-size:9px;
      letter-spacing:.05em;
    }
    .catalogue-card-reference{
      right:10px;
      top:auto;
      bottom:10px;
      min-height:26px;
      padding:0 8px;
      font-size:9px;
    }
    .catalogue-card-body{
      gap:10px;
      padding:12px 12px 12px 14px;
      min-width:0;
    }
    .catalogue-card-head{
      flex-direction:row;
      gap:8px;
    }
    .catalogue-card-name{
      font-size:15px;
      line-height:1.25;
    }
    .catalogue-card-price{
      text-align:right;
      font-size:14px;
      line-height:1.2;
    }
    .catalogue-card-price small{
      margin-top:3px;
      font-size:9px;
    }
    .catalogue-card-desc{
      font-size:12px;
      line-height:1.45;
      display:-webkit-box;
      -webkit-line-clamp:2;
      -webkit-box-orient:vertical;
      overflow:hidden;
    }
    .catalogue-card-footer{
      gap:8px;
      padding-top:10px;
      align-items:flex-start;
    }
    .catalogue-card-meta{
      gap:6px;
    }
    .catalogue-meta-pill{
      padding:5px 7px;
      font-size:9px;
    }
    .catalogue-card-meta .catalogue-meta-pill:last-child{
      display:none;
    }
    .catalogue-card-action{
      display:none;
    }
    .catalogue-card-cta{
      width:auto;
      min-height:34px;
      padding:0 12px;
      border-radius:12px;
      font-size:10px;
      letter-spacing:.05em;
      align-self:flex-start;
    }
    .catalogue-title{
      font-size:40px;
    }
    .catalogue-footer-card{
      margin:10px 0 56px;
      padding:22px 20px;
    }
    .catalogue-whatsapp-float{
      left:12px;
      right:12px;
      bottom:12px;
      justify-content:center;
    }
  }
</style>

<script>
  document.body.classList.add('catalogue-page');
</script>

<header>
  <div class="container nav">
    <a class="brand" href="index.php?page=accueil" aria-label="Accueil Invitation Spéciale">
      <img src="images/Logo_invitationSpeciale_4.png" width="250px" alt="Invitation Spéciale">
    </a>
    <nav aria-label="Navigation principale" class="menu">
      <a href="index.php?page=accueil#services">Services</a>
      <a href="index.php?page=catalogue">Catalogue</a>
      <a href="index.php?page=accueil#galerie">Galerie</a>
      <a href="index.php?page=accueil#tarifs">Tarifs</a>
      <a href="index.php?page=accueil#contact">Contact</a>
      <a href="event/index.php?page=login">Mon Compte</a>
    </nav>
    <button class="btn hamb" aria-expanded="false" aria-controls="mobileMenu" style="color:#eab308;" id="catalogueHamb">Menu</button>
    <a href="<?php echo htmlspecialchars($catalogWhatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="Discuter sur WhatsApp" style="color:#eab308;"><i class="fab fa-whatsapp icon" style="font-size:40px;margin-right:25px;"></i></a>
  </div>
</header>

<main class="catalogue-page-shell">
  <div class="catalogue-container">
    <section class="catalogue-hero">
      <div class="catalogue-hero-card">
        <div class="catalogue-hero-grid">
          <div>
            <span class="catalogue-kicker">Catalogue Invitation Speciale</span>
            <h1 class="catalogue-title">Une collection pensée pour les mariages et réceptions où chaque détail doit respirer l’élégance.</h1>
            <p class="catalogue-copy">
              Découvrez une sélection de modèles d’invitation, de chevalets et d’accessoires conçus pour sublimer vos événements.
              Chaque pièce est présentée comme un objet de cérémonie: raffinée, lisible, personnalisable et pensée pour donner le ton dès la première impression.
            </p>
            <p class="catalogue-manifesto">Direction artistique soignée, personnalisation sur mesure, rendu premium pour mariage, réception et grandes célébrations.</p>

            <div class="catalogue-hero-actions">
              <a class="catalogue-btn catalogue-btn-primary" href="index.php?page=commande">Commander un événement</a>
              <a class="catalogue-btn catalogue-btn-whatsapp" href="<?php echo htmlspecialchars($catalogWhatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">WhatsApp direct</a>
            </div>
          </div>

          <aside class="catalogue-hero-panel">
            <div class="catalogue-stat-grid">
              <div class="catalogue-stat">
                <span class="catalogue-stat-label">Modèles disponibles</span>
                <strong class="catalogue-stat-value"><?php echo $catalogTotal; ?></strong>
              </div>
              <div class="catalogue-stat">
                <span class="catalogue-stat-label">Catégories</span>
                <strong class="catalogue-stat-value"><?php echo count(array_filter($groupedCatalog)); ?></strong>
              </div>
              <div class="catalogue-stat">
                <span class="catalogue-stat-label">Commande</span>
                <strong class="catalogue-stat-value">Sur mesure</strong>
              </div>
            </div>
            <div class="catalogue-hero-signature">
              Une présentation haut de gamme pour aider votre client à choisir rapidement un style, un modèle et une ambiance visuelle cohérente.
            </div>
          </aside>
        </div>
      </div>
    </section>

    <div class="catalogue-intro-row">
      <div class="catalogue-chip-row">
        <?php foreach ($catalogOrder as $catalogType) {
          if (empty($groupedCatalog[$catalogType])) {
            continue;
          }
          ?>
          <span class="catalogue-chip"><?php echo htmlspecialchars($catalogLabels[$catalogType] ?? ucfirst($catalogType), ENT_QUOTES, 'UTF-8'); ?></span>
        <?php } ?>
      </div>

      <div class="catalogue-note">Tarifs affichés à titre indicatif, selon les données actives du catalogue.</div>
    </div>

    <?php foreach ($catalogOrder as $catalogType) {
      $catalogItems = $groupedCatalog[$catalogType] ?? [];
      if ($catalogItems === []) {
        continue;
      }
      ?>
      <section class="catalogue-section">
        <div class="catalogue-section-head">
          <div>
            <h2 class="catalogue-section-title"><?php echo htmlspecialchars($catalogLabels[$catalogType] ?? ucfirst($catalogType), ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="catalogue-section-copy"><?php echo htmlspecialchars($catalogIntros[$catalogType] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
          </div>
          <div class="catalogue-count"><?php echo count($catalogItems); ?> modèle<?php echo count($catalogItems) > 1 ? 's' : ''; ?></div>
        </div>

        <div class="catalogue-grid">
          <?php foreach ($catalogItems as $catalogItem) {
            $imageName = trim((string) ($catalogItem['image'] ?? ''));
            $imagePath = $imageName !== '' ? $catalogImageBasePath . rawurlencode($imageName) : null;
            $modelName = trim((string) ($catalogItem['nom'] ?? 'Modèle'));
            $priceLabel = $formatPrice($catalogItem['unit_price'] ?? null);
            $modelReference = $buildReference($catalogItem);
            $description = $buildDescription($catalogItem);
            $chooseModelMessage = rawurlencode('Bonjour Invitation Speciale, je choisis le modèle "' . $modelName . '" (' . $modelReference . ') au prix de ' . $priceLabel . '. Merci de me contacter pour la suite.');
            $chooseModelUrl = 'https://wa.me/' . $catalogWhatsappNumber . '?text=' . $chooseModelMessage;
            ?>
            <article class="catalogue-card">
              <div class="catalogue-card-media">
                <span class="catalogue-card-badge"><?php echo htmlspecialchars($catalogLabels[$catalogType] ?? ucfirst($catalogType), ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="catalogue-card-reference"><?php echo htmlspecialchars($modelReference, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php if ($imagePath !== null) { ?>
                  <img src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($modelName, ENT_QUOTES, 'UTF-8'); ?>">
                <?php } else { ?>
                  <div class="catalogue-card-empty">Aperçu du modèle</div>
                <?php } ?>
              </div>

              <div class="catalogue-card-body">
                <div class="catalogue-card-head">
                  <h3 class="catalogue-card-name"><?php echo htmlspecialchars($modelName, ENT_QUOTES, 'UTF-8'); ?></h3>
                  <div class="catalogue-card-price"><?php echo htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8'); ?><small>tarif catalogue</small></div>
                </div>

                <p class="catalogue-card-desc"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></p>

                <div class="catalogue-card-footer">
                  <div class="catalogue-card-meta">
                    <span class="catalogue-meta-pill"><?php echo htmlspecialchars($modelReference, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="catalogue-meta-pill">Personnalisable</span>
                    <span class="catalogue-meta-pill">Commande client</span>
                  </div>
                  <span class="catalogue-card-action">Invitation Spéciale</span>
                </div>

                <a class="catalogue-card-cta" href="<?php echo htmlspecialchars($chooseModelUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Je choisis ce modèle</a>
              </div>
            </article>
          <?php } ?>
        </div>
      </section>
    <?php } ?>

    <section class="catalogue-footer-card">
      <div class="catalogue-footer-grid">
        <div>
          <h2 class="catalogue-footer-title">Un modèle vous intéresse ?</h2>
          <p class="catalogue-footer-copy">
            Envoyez ce catalogue à votre client, puis redirigez-le vers la prise de commande pour sélectionner son modèle,
            son type d’événement et les options souhaitées.
          </p>
        </div>

        <div class="catalogue-footer-actions">
          <a class="catalogue-btn catalogue-btn-secondary" href="index.php?page=accueil">Retour au site</a>
          <a class="catalogue-btn catalogue-btn-whatsapp" href="<?php echo htmlspecialchars($catalogWhatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Parler sur WhatsApp</a>
          <a class="catalogue-btn catalogue-btn-primary" href="index.php?page=commande">Commander un événement</a>
        </div>
      </div>
    </section>
  </div>

  <a class="catalogue-whatsapp-float" href="<?php echo htmlspecialchars($catalogWhatsappUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="Contacter Invitation Speciale sur WhatsApp">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <path d="M20.52 3.48A11.86 11.86 0 0 0 12.07 0C5.5 0 .15 5.34.15 11.92c0 2.1.55 4.16 1.6 5.97L0 24l6.28-1.65a11.88 11.88 0 0 0 5.78 1.48h.01c6.57 0 11.92-5.35 11.92-11.92 0-3.18-1.24-6.16-3.47-8.43ZM12.07 21.8h-.01a9.9 9.9 0 0 1-5.04-1.38l-.36-.21-3.73.98 1-3.64-.23-.37a9.88 9.88 0 0 1-1.52-5.27c0-5.47 4.45-9.92 9.93-9.92a9.86 9.86 0 0 1 7.04 2.91 9.84 9.84 0 0 1 2.9 7.01c0 5.47-4.46 9.92-9.93 9.92Zm5.44-7.4c-.3-.15-1.78-.88-2.05-.98-.28-.1-.48-.15-.69.15-.2.3-.79.98-.96 1.18-.18.2-.35.23-.65.08-.3-.15-1.28-.47-2.43-1.5a9.13 9.13 0 0 1-1.69-2.1c-.18-.3-.02-.45.13-.6.14-.14.3-.35.45-.53.15-.18.2-.3.3-.5.1-.2.05-.38-.03-.53-.07-.15-.69-1.66-.94-2.28-.25-.6-.5-.52-.69-.53h-.58c-.2 0-.53.08-.8.38-.28.3-1.06 1.04-1.06 2.53 0 1.5 1.08 2.94 1.23 3.14.15.2 2.13 3.26 5.15 4.57.72.31 1.29.5 1.73.64.73.23 1.4.2 1.93.12.59-.09 1.78-.73 2.03-1.43.25-.7.25-1.3.17-1.43-.08-.13-.27-.2-.57-.35Z" fill="currentColor"/>
    </svg>
    Contacter sur WhatsApp
  </a>
</main>

<script>
  document.getElementById('catalogueHamb')?.addEventListener('click', function () {
    document.querySelector('.menu')?.classList.toggle('open');
  });
</script>