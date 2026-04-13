<?php

$codevent = isset($_GET['codevent']) ? (int) $_GET['codevent'] : 0;

if (isset($_POST['submitimgback'])) {
    try {
        $fileName = EventMediaService::storeUploadedImage($_FILES['photo1'] ?? [], '../../couple/images', null, 2097152);
        EventMediaService::updateEventFields($pdo, $codevent, ['photostory' => $fileName]);
    } catch (RuntimeException $e) {
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        return;
    }

              
      ?>
      
               <script>
                  window.location="index.php?page=conf_siteweb&ok=1";
               </script>

      <?php




















}elseif (isset($_POST['submitimgcoeur'])) {
    try {
        $fileName = EventMediaService::storeUploadedImage($_FILES['photo2'] ?? [], '../../couple/images', null, 2097152);
        EventMediaService::updateEventFields($pdo, $codevent, ['photo' => $fileName]);
    } catch (RuntimeException $e) {
        echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        return;
    }

              
      ?>
      
               <script>
                  window.location="index.php?page=conf_siteweb&ok=2";
               </script>

      <?php

















}elseif (isset($_POST['submittextsdd'])) {
    EventMediaService::upsertWebsiteGeneralText($pdo, $codevent, 'text_sdd', $_POST['text_sdd'] ?? null);


              
      ?>
      
               <script>
                  window.location="index.php?page=conf_siteweb&ok=2";
               </script>

      <?php

}

?>