<?php
$codevent = isset($codevent) ? (string)$codevent : '';
$getCod   = $_GET['cod'] ?? '';
$getIdInv = $_GET['idinv'] ?? '';

$nomFetard   = htmlspecialchars($dataevent['nomfetard'] ?? '', ENT_QUOTES, 'UTF-8');
$photoStory  = $dataevent['photostory'] ?? '';
$dateEvent   = $dataevent['date_event'] ?? '';
$initialeMar = $dataevent['initiale_mar'] ?? '';
$lieuEvent   = htmlspecialchars($dataevent['lieu'] ?? '', ENT_QUOTES, 'UTF-8');
$adresseEvent = htmlspecialchars($dataevent['adresse'] ?? '', ENT_QUOTES, 'UTF-8');

if ($codevent === '607') {
    $class_sd       = '';
    $class_rsvp     = '';
    $class_rsvpdeux = '';
    $class_hero     = 'hero';
    $class_herodeux = '';

    $color = '#e2af47';
    $customStyle = "
    <style>
        .dynamic-color,
        .dynamic-color * {
            color: #e2af47 !important;
        }

        .dynamic-bg {
            background-color: #e2af47 !important;
        }

        .dynamic-bgfonce {
            background-color: #dd9c28 !important;
        }

        .dynamic-border {
            border-color: #e2af47 !important;
        }

        .btn-primary {
            background-color: #e2af47 !important;
            border-color: #e2af47 !important;
            color: #fff !important;
        }

        .btn-primary:hover {
            background-color: #c9972f !important;
            border-color: #c9972f !important;
        }

        .bg-color-overlay {
            background-color: rgba(226, 175, 71, 0.8) !important;
        }

        .invite_info {
            background-color: #dd9c28 !important;
        }

        #hero::before {
            content: '' !important;
            width: 100% !important;
            height: 100% !important;
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            background-color: #dd9c28 !important;
            z-index: 0 !important;
            -khtml-opacity: 0.8 !important;
            -moz-opacity: 0.8 !important;
            opacity: 0.8 !important;
        }

        #nav-mobile-btn {
            background-color: #dd9c28 !important;
        }
    </style>
    ";
} else {
    $color         = '';
    $customStyle   = '';
    $class_sd      = 'bg-color-overlay';
    $class_rsvp    = 'rsvp-2';
    $class_rsvpdeux = 'section-bg-color';
    $class_hero    = 'hero';
    $class_herodeux = 'section-divider-bg-color';
}

echo $customStyle;

if (empty($photoStory)) {
    $bg = 'bgstand2_1.png';
} else {
    $bg = $photoStory;
}

try {
    $date = new DateTime($dateEvent);
    $formattedDate = $date->format('Y/m/d h:i A');
    $DateWed = $date->format('d/m/Y H:i');
} catch (Exception $e) {
    $formattedDate = date('Y/m/d h:i A');
    $DateWed = date('d/m/Y H:i');
}

if ($getCod === '95') {
    $display = 'display:none;';
} else {
    $display = 'display:block;';
}
?>

<!-- BEGIN PRELOADER -->
<div id="preloader">
    <div class="loading-heart">
        <svg viewBox="0 0 512 512" width="100">
            <path d="M462.3 62.6C407.5 15.9 326 24.3 275.7 76.2L256 96.5l-19.7-20.3C186.1 24.3 104.5 15.9 49.7 62.6c-62.8 53.6-66.1 149.8-9.9 207.9l193.5 199.8c12.5 12.9 32.8 12.9 45.3 0l193.5-199.8c56.3-58.1 53-154.3-9.8-207.9z" />
        </svg>
        <div class="preloader-title dynamic-color">
            <small>Anniversaire</small><br>
            <?php echo $nomFetard; ?>
        </div>
    </div>
</div>
<!-- END PRELOADER -->

<!-- BEGIN WRAPPER -->
<div id="wrapper">

    <!-- BEGIN HEADER -->
    <header id="header">
        <div class="nav-section">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">

                        <a style="font-family: 'Great Vibes', cursive;font-size:40px;margin-top:-2px;"
                           class="nav-logo dynamic-color"
                           href="index.php?page=accueil&cod=<?php echo urlencode($codevent); ?>">
                            <?php
                            $nom = trim($dataevent['nomfetard'] ?? '');
                            $mots = preg_split('/\s+/', $nom);
                            echo htmlspecialchars(implode(' ', array_slice($mots, 0, 2)), ENT_QUOTES, 'UTF-8');
                            ?>
                        </a>

                        <!-- BEGIN MAIN MENU -->
                        <nav class="navbar">
                            <ul class="nav navbar-nav">
                                <li><a href="#hero">accueil</a></li>
                                <?php if ($codevent !== '287') { ?>
                                    <li><a href="#rsvp-2">RSVP</a></li>
                                <?php } ?>
                            </ul>

                            <button id="nav-mobile-btn"><i class="fas fa-bars"></i></button>
                        </nav>
                        <!-- END MAIN MENU -->

                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- END HEADER -->

    <style>
        .bg-slideshowx {
            background-image: url('../../couple/images/<?php echo htmlspecialchars($bg, ENT_QUOTES, 'UTF-8'); ?>') !important;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
        }
    </style>

    <!-- BEGIN HERO SECTION -->
    <div id="<?php echo htmlspecialchars($class_hero, ENT_QUOTES, 'UTF-8'); ?>" class="section-divider-top-1 <?php echo htmlspecialchars($class_herodeux, ENT_QUOTES, 'UTF-8'); ?> off-section dynamic-bg">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="v-center">
                        <div class="hero-divider-top light" data-animation-direction="from-top" data-animation-delay="700"></div>

                        <?php
                        if (!empty($getIdInv)) {
                            include('confscript.php');
                        }
                        ?>

                        <h1 class="hero-title light">
                            <?php if (empty($getIdInv)) { ?>
                                <small data-animation-direction="from-top" data-animation-delay="300">Anniversaire</small><br>
                            <?php } ?>

                            <span data-animation-direction="from-right" data-animation-delay="300"><?php echo $nomFetard; ?></span>
                        </h1>

                        <div class="hero-divider-bottom light" data-animation-direction="from-bottom" data-animation-delay="700"></div>

                        <div class="hero-subtitle light">
                            Qui célèbre son anniversaire dans
                        </div>

                        <div class="countdown" data-date="<?php echo htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8'); ?>"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END HERO SECTION -->

    <!-- BEGIN BRIDE & GROOM SECTION -->
    <section class="section-bg-color overflow-content-over no-padding-top" style="<?php echo $display; ?>">
        <div class="section-bg-color overflow-content no-padding">
            <div class="container">
                <div class="row">
                    <div class="col overflow-image-wrapper">

                        <?php if (empty($photoStory)) { ?>

                            <h2 class="title"><br><br><?php echo $nomFetard; ?></h2>
                            <p class="center">
                                Je célèbre une nouvelle année de ma vie. Je prends un moment pour me réjouir des expériences, des leçons et des belles rencontres de l'année écoulée.
                                En ce jour spécial, je demande à Dieu de me protéger, de me guider et de m'accorder la sagesse pour faire face aux défis à venir.
                                Que cette nouvelle année soit remplie de joie, de santé et d'amour.
                            </p>
                            <p class="center">
                                J'ai le plaisir de vous inviter à célébrer mon anniversaire ! Venez partager un moment festif autour de gâteaux, de rires et de souvenirs.
                            </p>
                            <p class="center"><a class="btn btn-primary" href="#rsvp-2">RSVP</a></p>

                        <?php } else { ?>

                            <div class="overflow extra-padding-top">

                                <?php if ($codevent === '287') { ?>
                                    <p class="center" style="font-style:italic;">
                                        Je vais célébrer une nouvelle année de ma longue vie. C'est un moment de réjouissances, de partage mais aussi d'introspection.
                                    </p>
                                    <p class="center" style="font-style:italic;">
                                        Je suis reconnaissante envers notre Dieu qui, tel un tendre jardinier bienveillant, a cultivé mes 85 printemps sous sa protection attentionnée.
                                    </p>
                                    <p class="center" style="font-style:italic;">
                                        Fêter ses 85 ans, ce n’est pas compter les années, c’est illuminer le chemin parcouru, parsemé de si belles rencontres.
                                        C'est célébrer la vie, l'amitié, la fraternité, la famille et c'est surtout s'aimer vivant !
                                    </p>
                                    <p class="center" style="font-style:italic;">
                                        C'est donc avec un immense plaisir que je vous attends pour partager ce beau moment. Votre présence, avec votre grand cœur, sera mon plus beau cadeau.
                                    </p>
                                    <p class="center" style="font-style:italic;"><b>Maman Laurence "Tate"</b></p>
                                <?php } else { ?>

                                    <div class="overflow-image-text extra-padding-top">
                                        <h2 class="title"><?php echo $nomFetard; ?></h2>
                                        <p class="center">
                                            Je célèbre une nouvelle année de ma vie. Je prends un moment pour me réjouir des expériences, des leçons et des belles rencontres de l'année écoulée.
                                            En ce jour spécial, je demande à Dieu de me protéger, de me guider et de m'accorder la sagesse pour faire face aux défis à venir.
                                            Que cette nouvelle année soit remplie de joie, de santé et d'amour.
                                        </p>
                                        <p class="center">
                                            J'ai le plaisir de vous inviter à célébrer mon anniversaire ! Venez partager un moment festif autour de gâteaux, de rires et de souvenirs.
                                        </p>
                                        <p class="center"><a class="btn btn-primary" href="#rsvp-2">RSVP</a></p>
                                    </div>

                                    <div class="overflow-image flower">
                                        <img src="../../couple/images/<?php echo htmlspecialchars($photoStory, ENT_QUOTES, 'UTF-8'); ?>" alt="Photo">
                                    </div>

                                <?php } ?>
                            </div>

                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END BRIDE & GROOM SECTION -->

    <!-- BEGIN WEDDING INVITE SECTION -->
    <section id="the-wedding" style="background-color: <?php echo htmlspecialchars($color, ENT_QUOTES, 'UTF-8'); ?> !important;" class="parallax-background <?php echo htmlspecialchars($class_sd, ENT_QUOTES, 'UTF-8'); ?> padding-divider-top">
        <div class="section-divider-top-1 section-divider-bg-color off-section"></div>
        <div class="container">
            <div class="row">
                <?php if ($codevent !== '287') { ?>
                    <div class="col-sm-12">
                        <h2 class="section-title light">Birthday party</h2>
                    </div>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-md-12 col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 center">
                    <div class="invite neela-style" data-animation-direction="from-left" data-animation-delay="100">
                        <div class="invite_title">
                            <div class="text">
                                Save<small>the</small>Date
                            </div>
                        </div>

                        <div class="invite_info">
                            <h2><small>Anniversaire de</small><br><?php echo $nomFetard; ?></h2>

                            <?php if ($codevent !== '287') { ?>
                                <div class="uppercase">Demande l'honneur de votre présence le jour de son anniversaire</div>
                            <?php } ?>

                            <?php if ($codevent !== '287') { ?>
                                <div class="date"><?php echo htmlspecialchars($DateWed, ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php } else { ?>
                                <div class="date">
                                    <?php echo date('d/m/Y', strtotime($dateEvent)); ?><br>
                                    <?php echo date('H:i', strtotime($dateEvent)); ?>
                                </div>
                            <?php } ?>

                            <?php if ($codevent !== '287') { ?>
                                <div class="uppercase"><?php echo $lieuEvent; ?></div>
                                <h5>Réception à suivre</h5>
                                <h5><?php echo $adresseEvent; ?></h5>
                            <?php } else { ?>
                                <h5>
                                    Salle de fête de Blocry <br>
                                    N°121 Rue de l'Invasion <br>
                                    1340 Ottignies LLN - Belgique <br>
                                    Information : +32 465 954 685
                                </h5>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END WEDDING INVITE SECTION -->

    <!-- BEGIN CONTACTS SECTION -->
    <?php if (empty($getIdInv)) : ?>
        <section id="<?php echo htmlspecialchars($class_rsvp ?: 'rsvp-2', ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo htmlspecialchars($class_rsvpdeux, ENT_QUOTES, 'UTF-8'); ?> extra-padding-section dynamic-color dynamic-bgfonce">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1 col-xl-8 offset-xl-2 col-xxl-6 offset-xxl-3">
                        <div class="form-wrapper flowers neela-style">
                            <h2 class="section-title">Allez-vous y assister ?</h2>

                            <?php
                            if (isset($_POST['submitrsvp'])) {
                                $nom      = trim($_POST['nom'] ?? '');
                                $phone    = trim($_POST['phone'] ?? '');
                                $presence = trim($_POST['presence'] ?? '');
                                $note     = trim($_POST['note'] ?? '');

                                if ($getCod === '95') {
                                    $repas = null;
                                    $email = 'contact@invitationspeciale.com';
                                } else {
                                    $repas = $_POST['repas'] ?? null;
                                    $email = trim($_POST['email'] ?? '');
                                }

                                if ($nom === '') {
                                    echo '<span style="color:red;">Remplissez le nom</span>';
                                } elseif ($phone === '') {
                                    echo '<span style="color:red;">Remplissez le numéro de téléphone</span>';
                                } elseif ($email === '') {
                                    echo '<span style="color:red;">Remplissez l\'adresse email</span>';
                                } elseif ($presence === '') {
                                    echo '<span style="color:red;">Confirmez votre présence ou absence</span>';
                                } else {
                                    $sql = 'INSERT INTO confirmation (
                                                cod_mar,
                                                noms,
                                                email,
                                                phone,
                                                presence,
                                                typerepas,
                                                note,
                                                date_enreg
                                            ) VALUES (
                                                :cod_mar,
                                                :noms,
                                                :email,
                                                :phone,
                                                :presence,
                                                :typerepas,
                                                :note,
                                                NOW()
                                            )';

                                    $q = $pdo->prepare($sql);
                                    $q->bindValue(':cod_mar', $getCod);
                                    $q->bindValue(':noms', $nom);
                                    $q->bindValue(':email', $email);
                                    $q->bindValue(':phone', $phone);
                                    $q->bindValue(':presence', $presence);
                                    $q->bindValue(':typerepas', $repas);
                                    $q->bindValue(':note', $note);
                                    $q->execute();
                                    $q->closeCursor();

                                    $cod_conf = $pdo->lastInsertId();

                                    foreach ($_POST as $key => $value) {
                                        if (strpos($key, 'preference') === 0) {
                                            $cod_repas = $_POST[$key] ?? null;
                                            if (!empty($cod_repas)) {
                                                $stmt = $pdo->prepare("INSERT INTO menurecolte (cod_event, cod_conf, cod_repas) VALUES (:cod_event, :cod_conf, :cod_repas)");
                                                $stmt->bindValue(':cod_event', $getCod);
                                                $stmt->bindValue(':cod_conf', $cod_conf);
                                                $stmt->bindValue(':cod_repas', $cod_repas);
                                                $stmt->execute();
                                            }
                                        }
                                    }

                                    $speudo  = $nom;
                                    $subject = strtoupper($initialeMar) . " Reservation";
                                    $message = "Bonjour $speudo,\n\nVotre reservation nous est parvenue avec succès.\n\nMerci!";
                                    $headers = "From: contact@invitationspeciale.com\r\n";
                                    $headers .= "MIME-Version: 1.0\r\n";
                                    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
                                    $headers .= "Content-Transfer-Encoding: 8bit\r\n";

                                    if (mail($email, $subject, $message, $headers)) {
                                        echo '<script src="sweet/sweetalert2.all.min.js"></script>';
                                        echo '<script>
                                            Swal.fire({
                                                title: "Reservation !",
                                                text: "Votre réservation est enregistrée avec succès.",
                                                icon: "success",
                                                confirmButtonText: "OK"
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.href = "index.php?page=accueil&cod=' . htmlspecialchars($getCod, ENT_QUOTES, 'UTF-8') . '";
                                                }
                                            });
                                        </script>';
                                    } else {
                                        echo '<span style="color:red;">Erreur lors de l\'envoi de l\'email.</span>';
                                    }
                                }
                            }
                            ?>

                            <form id="contact-form" class="contact-form" action="" method="post">

                                <div class="form-floating">
                                    <input type="text" name="nom" id="name" value="<?php echo htmlspecialchars($_POST['nom'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Votre Noms*" class="form-control required fromName">
                                    <label for="name">Prenom et Nom *</label>
                                </div>

                                <div class="form-floating" style="<?php echo $display; ?>">
                                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="E-mail*" class="form-control fromEmail">
                                    <label for="email">E-mail*</label>
                                </div>

                                <div class="form-floating">
                                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Téléphone*" class="form-control required fromEmail">
                                    <label for="phone">Téléphone*</label>
                                </div>

                                <div class="form-check-wrapper">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input required" type="radio" name="presence" value="oui" id="attend_wedding_yes" <?php echo (($_POST['presence'] ?? '') === 'oui') ? 'checked' : ''; ?>>
                                        <label for="attend_wedding_yes">Oui, je vais y assister.</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input required" type="radio" name="presence" value="non" id="attend_wedding_no" <?php echo (($_POST['presence'] ?? '') === 'non') ? 'checked' : ''; ?>>
                                        <label for="attend_wedding_no">Désolé, je ne peux pas.</label>
                                    </div>
                                </div>

                                <?php
                                if ($codevent === '224') {
                                    $condition = "WHERE type = 'boisson'";
                                    $titrechmp = 'Préférences de boisson';
                                } else {
                                    $condition = "WHERE type = 'repas'";
                                    $titrechmp = 'Préférences des repas';
                                }
                                ?>

                                <fieldset class="form-check-wrapper" id="meal_pref" style="<?php echo $display; ?>">
                                    <label><?php echo htmlspecialchars($titrechmp, ENT_QUOTES, 'UTF-8'); ?></label>

                                    <?php
                                    $reqpre = $pdo->prepare("SELECT * FROM preference_repas $condition ORDER BY cod_pr ASC");
                                    $reqpre->execute();
                                    while ($data_pref = $reqpre->fetch()) {
                                        $prefId = $data_pref['cod_pr'];
                                        $checked = isset($_POST['preference' . $prefId]) ? 'checked' : '';
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" name="<?php echo 'preference' . (int)$prefId; ?>" type="checkbox" value="<?php echo (int)$prefId; ?>" id="pref_<?php echo (int)$prefId; ?>" <?php echo $checked; ?>>
                                            <label for="pref_<?php echo (int)$prefId; ?>">
                                                <?php echo htmlspecialchars($data_pref['nom'], ENT_QUOTES, 'UTF-8'); ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </fieldset>

                                <div class="form-floating">
                                    <textarea id="message" name="note" placeholder="Message" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['note'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <label for="message">Message</label>
                                </div>

                                <div class="center">
                                    <button type="submit" name="submitrsvp" class="btn btn-primary">Envoyer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
    <!-- END CONTACTS SECTION -->

    <!-- BEGIN FOOTER -->
    <footer id="footer">
        <div class="copyright">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        &copy; <?php echo date('Y'); ?> Hubert Solutions All right reserved <br>
                        Plateforme, branche de <a href="https://www.invitationspeciale.com">Invitation Spéciale</a><br>
                        Sous : <a href="https://hubertlutunga.com">Hubert Lutunga</a> <br>
                        <a href="https://wa.me/243810678785" target="_blank">Nous contacter</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- END FOOTER -->

</div>
<!-- END WRAPPER -->

<!-- Google Maps API and Map Richmarker Library -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBHOXsTqoSDPQ5eC5TChvgOf3pAVGapYog"></script>
<script src="js/richmarker.js"></script>

<!-- Libs -->
<script src="js/jquery-3.6.0.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/jquery-migrate-3.3.2.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/jquery.placeholder.min.js"></script>
<script src="js/ismobile.js"></script>
<script src="js/retina.min.js"></script>
<script src="js/waypoints.min.js"></script>
<script src="js/waypoints-sticky.min.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/lightbox.min.js"></script>
<script src="js/jquery.nicescroll.js"></script>
<script src="js/jquery.zoomslider.js"></script>

<!-- Template Scripts -->
<script src="js/variables.js"></script>
<script src="js/scripts.js"></script>