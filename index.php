<?php

require __DIR__ . '/bootstrap/app.php';

 

 
 
     if (isset($_GET['site'])) {
    $shortCode = rtrim((string) $isAppConfig['base_url'], '/') . '/?site=' . $_GET['site'];
    $red_url = ShortUrlService::findLongUrl($pdo, $shortCode);

    if ($red_url) {
        ?>
        
        <script>
            window.location = "<?php echo $red_url; ?>"; // Corrigez ici avec htmlspecialchars
        </script>

        <?php
    } else {
        // Redirection vers la page d'accueil si l'URL n'est pas trouvée
        ?>
        
        <script>
            window.location = "<?php echo htmlspecialchars((string) $isAppConfig['base_url'], ENT_QUOTES, 'UTF-8'); ?>";
        </script>

        <?php
    }
} else {
  
  

$content = PageRouter::resolve($_GET['page'] ?? null, __DIR__ . '/pages');

if ($content === null) {
  PageRouter::redirect('index.php?page=' . ($isAppConfig['default_page'] ?? 'accueil'));
}


}





?>

<!DOCTYPE html>
<html lang="fr">

<head>


  <title>Invitation Spéciale</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Une entreprise spécialisée dans la création d'invitations haut de gamme et sur mesure pour des événements mémorables.">
    <meta name="keywords" content="Invitation spéciale">
    <meta name="author" content="inittheme">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:type" content="Application">
    <meta property="og:title" content="Invitation spéciale">
    <meta property="og:site_name" content="ADN"> 
    <meta property="og:description" content="Invitation spéciale"> 
    <!-- Google site verification -->
    <meta name="google-site-verification" content="...">
    <meta name="facebook-domain-verification" content="...">
    <meta name="csrf-token" content="...">
    <meta name="currency" content="$">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  

<link rel="shortcut icon" href="images/Logo_invitationSpeciale_2.png" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
 






  <style>
    :root{
      --bg:#0b0e16;           /* fond principal (très sombre, élégant) */
      --surface:#0f1424;      /* panneaux */
      --text:#e6e8ee;         /* texte */
      --muted:#a8b0c2;        /* texte secondaire */
      --primary:#eab308;      /* or / accent */
      --primary-2:#ffd54a;    /* or clair */
      --line:#232a3e;         /* séparateurs */
      --ok:#22c55e;           /* vert */
      --err:#ef4444;          /* rouge */
      --radius:18px;
      --shadow: 0 10px 30px rgba(0,0,0,.35);
      --container:1200px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0; background: radial-gradient(1200px 600px at 80% -10%, rgba(234,179,8,.15), transparent 60%),
                radial-gradient(900px 500px at -20% 20%, rgba(234,179,8,.07), transparent 60%),
                var(--bg);
      color:var(--text); font:400 16px/1.6 Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      -webkit-font-smoothing:antialiased; text-rendering:optimizeLegibility
    }
    a{color:inherit; text-decoration:none}
    img{max-width:100%; display:block}
    .container{width:min(100%, var(--container)); margin-inline:auto; padding:0 20px}
    /* Header */
    header{position:sticky; top:0; z-index:40; backdrop-filter:saturate(180%) blur(10px); background:rgba(11,14,22,.6); border-bottom:1px solid rgba(255,255,255,.05)}
    .nav{display:flex; align-items:center; justify-content:space-between; padding:14px 0}
    .brand{display:flex; align-items:center; gap:12px}
    .brand .logo{width:40px; height:40px; border-radius:50%; background:linear-gradient(180deg, var(--primary-2), var(--primary)); display:grid; place-items:center; color:#111; font-weight:800}
    .brand h1{font:700 18px/1 Inter; letter-spacing:.3px}
    .menu{display:flex; gap:20px}
    .menu a{opacity:.9}
    .btn{display:inline-flex; align-items:center; gap:10px; border:1px solid var(--line); padding:10px 14px; border-radius:12px; background:#121832; transition:.2s ease; font-weight:600}
    .btn:hover{transform:translateY(-1px); box-shadow:var(--shadow)}
    .btn.primary{background:linear-gradient(180deg, var(--primary-2), var(--primary)); color:#111; border:none}
    .hamb{display:none}
    @media (max-width:900px){
      .menu{display:none}
      .hamb{display:inline-flex}
    }
    /* Hero */
    .hero{padding:80px 0 40px; position:relative}
    .hero .grid{display:grid; grid-template-columns: 1.1fr .9fr; gap:40px}
    .eyebrow{font:600 12px/1.2 Inter; letter-spacing:.3em; text-transform:uppercase; color:var(--primary)}
    .hero h2{font:700 46px/1.1 Playfair Display, serif; margin:.4em 0 .5em}
    .hero p{color:var(--muted)}
    .hero .cta{margin-top:26px; display:flex; gap:14px; flex-wrap:wrap}
    .hero-card{background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02)); border:1px solid rgba(255,255,255,.06); padding:22px; border-radius:var(--radius); box-shadow:var(--shadow)}
    .stats{display:grid; grid-template-columns:repeat(3,1fr); gap:18px; margin-top:26px}
    .stat{padding:16px; border:1px solid rgba(255,255,255,.06); border-radius:14px; text-align:center; background:#0e142c}
    .stat .n{font-weight:800; font-size:28px; color:var(--primary)}
    .mock{border-radius:20px; overflow:hidden; border:1px solid rgba(255,255,255,.06); box-shadow:var(--shadow)}
    @media (max-width:900px){
      .hero{padding:54px 0 10px}
      .hero .grid{grid-template-columns:1fr}
      .hero h2{font-size:34px}
      .stats{grid-template-columns:repeat(3, minmax(0, 1fr))}
    }
    /* Services */
    section{padding:70px 0; border-top:1px solid rgba(255,255,255,.06)}
    .section-title{display:flex; align-items:end; justify-content:space-between; gap:20px; margin-bottom:26px}
    .section-title h3{font:700 28px/1.2 Playfair Display, serif}
    .grid-3{display:grid; grid-template-columns:repeat(3,1fr); gap:22px}
    @media (max-width:980px){ .grid-3{grid-template-columns:1fr} }
    .card{background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02)); border:1px solid rgba(255,255,255,.06); border-radius:var(--radius); padding:22px; box-shadow:var(--shadow)}
    .card h4{margin:0 0 6px; font-size:18px}
    .pill{font:700 11px/1 Inter; letter-spacing:.2em; text-transform:uppercase; color:#111; background:linear-gradient(180deg, var(--primary-2), var(--primary)); display:inline-block; padding:7px 10px; border-radius:999px}
    .tags{display:flex; gap:8px; flex-wrap:wrap; margin-top:10px}
    .tag{border:1px dashed rgba(255,255,255,.2); padding:6px 10px; border-radius:12px; font-size:12px; color:var(--muted)}
    /* Galerie */
    .masonry{columns: 3 280px; column-gap: 14px}
    .masonry a{display:block; margin:0 0 14px; border-radius:16px; overflow:hidden; border:1px solid rgba(255,255,255,.06)}
    .masonry img{width:100%; height:auto}
    /* Avantages */
    .features{display:grid; grid-template-columns:repeat(4,1fr); gap:16px}
    .feature{padding:16px; border:1px solid rgba(255,255,255,.06); border-radius:14px; background:#0e142c}
    .feature b{color:var(--primary)}
    @media (max-width:980px){ .features{grid-template-columns:1fr 1fr} }
    /* Témoignages */
    .testis{display:grid; grid-template-columns:repeat(3,1fr); gap:16px}
    .testi{padding:18px; border:1px solid rgba(255,255,255,.06); border-radius:14px; background:#0e142c}
    .testi .name{display:flex; align-items:center; gap:10px; margin-top:8px; font-weight:700}
    .avatar{width:36px; height:36px; border-radius:50%; background:#1c2343}
    @media (max-width:980px){ .testis{grid-template-columns:1fr} }
    /* Tarifs */
    .pricing{display:grid; grid-template-columns:repeat(3,1fr); gap:20px}
    .price{padding:20px; border:1px solid rgba(255,255,255,.06); border-radius:16px; background:#0e142c}
    .price.best{outline:2px solid var(--primary); background:linear-gradient(180deg, #141a34, #0d1328)}
    .price .big{font:800 34px/1 Inter; color:var(--primary)}
    .price ul{padding-left:18px; margin:12px 0}
    .price li{margin:6px 0; color:var(--muted)}
    @media (max-width:980px){ .pricing{grid-template-columns:1fr} }
    /* CTA */
    .cta-bar{display:flex; flex-wrap:wrap; gap:14px; padding:18px; border:1px dashed rgba(255,255,255,.2); border-radius:16px; align-items:center; justify-content:space-between}
    /* Footer */
    footer{padding:40px 0; color:var(--muted)}
    .foot{display:flex; gap:26px; flex-wrap:wrap; align-items:center; justify-content:space-between; border-top:1px solid rgba(255,255,255,.06); padding-top:20px}
    .social{display:flex; gap:10px}
    .chip{display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border:1px solid var(--line); border-radius:999px; background:#0f1424}
    /* Utilities */
    .sr-only{position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0}
  </style>
</head>

<body>
   

   
 
      <?php
        include($content);
      ?>
       




</body>
</html>
