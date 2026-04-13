<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image on Hover Example</title>
    <style>
        /* Style pour l'image au survol */
        .hover-image {
            display: none; /* Cache l'image par défaut */
            position: absolute;
            z-index: 1000;
            max-width: 200px; /* Ajustez la taille maximale de l'image */
            border: 1px solid #ccc;
            background-color: white;
            padding: 5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .hover-container {
            position: relative; /* Permet à l'image d'être positionnée par rapport à son conteneur */
        }
    </style>
</head>
<body>

<!-- Exemple d'accès à l'image -->
<em>
    <?php 
    echo ' <span class="hover-container"><a href="#" class="model-link">Hubert</a>';
    ?>
    <img class="hover-image" src="Logo_invitationSpeciale_2.png" alt="Image" />
</em>

<script>
    // Sélectionne le lien et l'image
    const modelLink = document.querySelector('.model-link');
    const hoverImage = document.querySelector('.hover-image');

    // Affiche l'image au survol
    modelLink.addEventListener('mouseover', function(event) {
        hoverImage.style.display = 'block'; // Affiche l'image
        hoverImage.style.top = event.target.getBoundingClientRect().bottom + 'px'; // Positionne l'image
        hoverImage.style.left = event.target.getBoundingClientRect().left + 'px'; // Positionne l'image
    });

    // Cache l'image lorsque le curseur quitte le lien
    modelLink.addEventListener('mouseout', function() {
        hoverImage.style.display = 'none'; // Cache l'image
    });
</script>

</body>
</html>