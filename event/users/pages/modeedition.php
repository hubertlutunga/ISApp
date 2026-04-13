
<!-- desactiver la mode edition si le projet est déjà encours de conception -->


<?php $isDisabled = !empty($dataevent['crea']); ?>

<?php 
    
    if ($isDisabled){ 

    $displayact = "display:none;";   
    $title = "<span style='color:red;'>Vous ne pouvez plus éditer cet événement car il n'est <br> plus en phase de modification.";
?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("eventForms");
    if (form) {
        const elements = form.querySelectorAll("input, select, textarea, button");
        elements.forEach(el => el.disabled = true);
    }
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("eventForm");
    if (form) {
        const elements = form.querySelectorAll("input, select, textarea, button");
        elements.forEach(el => el.disabled = true);
    }
});
</script>

<?php

}else{

    $displayact = "style='display:block;'";  
    $title = "Modifier l'événement";
} 

?>


<!-- desactiver la mode edition si le projet est déjà encours de conception -->

