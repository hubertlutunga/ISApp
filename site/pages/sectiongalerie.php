
<?php
$stmtimg = $pdo->prepare("SELECT nom_photo FROM galeriephotos WHERE cod_event = ? ORDER BY cod_gp DESC LIMIT 5");
$stmtimg->execute([$codevent]);
$photos = $stmtimg->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
$patterns = [
    'col-7 mb-3',
    'col-5 align-self-end mb-3',
    'col-5',
    'col-3',
    'col-3'
];
?>


<!-- ========== SECTION GALERIE PHOTOS ========== -->

<section id="gallery" class="bg-secondary">
    <div class="container spacer-double-lg">
        <div class="col-lg-11 mx-lg-auto">
            <div class="row justify-content-center card-gutters">

                <?php
                $i = 0;
                foreach ($photos as $photo):

                    $class = $patterns[$i % count($patterns)];
                    $imagePath = "../event/users/galeriephoto/" . htmlspecialchars($photo['nom_photo']);
                ?>

                <div class="<?= $class ?>">
                    <a href="<?= $imagePath ?>" class="popup-image hover-effect d-block">
                        <img class="img-fluid" src="<?= $imagePath ?>" alt="Photo de la galerie">
                        <span class="hover-effect-container">
                            <span class="hover-effect-icon has-svg">
                                <!-- Ton SVG reste identique -->
                                <svg viewBox="0 0 73.6404 64.5606">
                                    <g>
                                        <path fill="#E25D5D" d="M36.904,6.66C28.53-1.73,14.94-1.83,6.45,6.47c-8.56,8.38-8.58,22.16-0.20,30.72l24.40,24.90c3.18,3.24,8.38,3.29,11.63,0.11l24.77-24.27c8.56-8.38,8.83-22.38,0.45-30.95C59.15-1.56,45.44-1.71,36.90,6.66z"/>
                                    </g>
                                </svg>
                            </span>
                        </span>
                    </a>
                </div>

                <?php
                $i++;
                endforeach;
                ?>

            </div>
        </div>
    </div>
</section>