<?php 

$codevent = $_GET['cod'] ?? '';
$cod = (int) ($_GET['codinv'] ?? 0);
$acces = "oui";
$ha = date('Y-m-d H:i');

$eventStmt = $pdo->prepare("SELECT date_event FROM events WHERE cod_event = :cod_event LIMIT 1");
$eventStmt->execute([':cod_event' => $codevent]);
$dateEvent = $eventStmt->fetchColumn();

$eventDateDay = null;
if (!empty($dateEvent)) {
   try {
      $eventDateDay = (new DateTimeImmutable((string) $dateEvent))->format('Y-m-d');
   } catch (Exception $exception) {
      $eventDateDay = null;
   }
}

$todayDay = date('Y-m-d');
$isBeforeEventDay = $eventDateDay !== null && $todayDay < $eventDateDay;

if (!$isBeforeEventDay && $cod > 0 && $codevent !== '') {
   $sql = "UPDATE invite SET acces = :acces, heure_arrive = :heure_arrive WHERE id_inv = :id_inv";
   $q = $pdo->prepare($sql);
   $q->bindValue(':acces', empty($acces) ? null : $acces, empty($acces) ? PDO::PARAM_NULL : PDO::PARAM_STR);
   $q->bindValue(':heure_arrive', empty($ha) ? null : $ha, empty($ha) ? PDO::PARAM_NULL : PDO::PARAM_STR);
   $q->bindValue(':id_inv', $cod, PDO::PARAM_INT);
   $q->execute();
   $q->closeCursor();
}

?>
      
               <script>
              window.location="index.php?page=access&cod=<?php echo htmlspecialchars((string) $codevent, ENT_QUOTES, 'UTF-8'); ?>";
               </script>
               
               
                