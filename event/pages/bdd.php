<?php 

/*

try
{
  session_start();
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=force1845217;charset=utf8", 'root', '');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch(Exception $e)

{
        die('Erreur : '.$e->getMessage());
}


*/


try
{
  session_start();
    $pdo = new PDO("mysql:host=185.98.131.176;dbname=force1845217;charset=utf8", 'force1845217', 'aetmctm0e6');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e)
{
        die('Erreur : '.$e->getMessage());
}



?>