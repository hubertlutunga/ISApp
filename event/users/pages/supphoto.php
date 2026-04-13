<?php

/*
require_once '../../pages/bdd.php'; // ou inclure la connexion PDO ici

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['cod'])) {
    echo 'error'; exit;
}

$id = (int) $_GET['cod'];

$stmt = $pdo->prepare("SELECT nom_photo FROM photos_event WHERE cod_photo = ?");
$stmt->execute([$id]);
$photo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$photo) {
    echo 'error'; exit;
}

$filepath = "../photosevent/" . $photo['nom_photo'];
if (file_exists($filepath)) {
    unlink($filepath);
}

$del = $pdo->prepare("DELETE FROM photos_event WHERE cod_photo = ?");
echo $del->execute([$id]) ? 'success' : 'error';


*/



		$cod = $_GET['cod'];


        $stmt = $pdo->prepare("SELECT * FROM photos_event WHERE cod_photo = ?");
        $stmt->execute([$cod]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);

                
        $filepath = "../photosevent/" . $photo['nom_photo'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

		$DeleteT = $pdo->prepare("DELETE from photos_event WHERE cod_photo = :cod_photo");
		$DeleteT->bindParam(':cod_photo', $cod);
		$DeleteT->execute();						 
		
			
		?>
               <script>
                  window.location="index.php?page=modevent&cod=<?php echo $_GET['codevent'];?>&deleted=1";
               </script> 
 