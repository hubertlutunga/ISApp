<?php
$service = trim((string) ($_GET['service'] ?? ''));
$allowedServices = ['billet-special'];
$target = 'event/index.php?page=commande';

if (in_array($service, $allowedServices, true)) {
  $target .= '&service=' . rawurlencode($service);
}
?>

<script>
  window.location.replace('<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>');
</script>

<noscript>
  <meta http-equiv="refresh" content="0;url=<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>">
  <p>Redirection vers la commande en cours...</p>
  <p><a href="<?php echo htmlspecialchars($target, ENT_QUOTES, 'UTF-8'); ?>">Continuer vers la page de commande</a></p>
</noscript>