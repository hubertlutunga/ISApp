<?php
$section = $_GET['section'] ?? '';
$cod     = $_GET['cod'] ?? '';
// vérifs & droits...

switch ($section) {
  case 'bg':
    // formulaire pour changer l’Image Background
    break;
  case 'save_date':
    // formulaire Save the date
    break;
  case 'coeur':
    // formulaire Image Coeur
    break;
  case 'story':
    // formulaire Love Story (texte + images + timeline…)
    break;
  case 'gallery':
    // formulaire Gallery (multi-upload…)
    break;
  default:
    echo "<p>Section invalide.</p>";
}
