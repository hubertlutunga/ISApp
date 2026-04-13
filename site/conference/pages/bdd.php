<?php 


try
{
  session_start();
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=mudikjxm_bd;charset=utf8", 'root', 'Root_2023');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch(Exception $e)

{
        die('Erreur : '.$e->getMessage());
}


/*

try
{
  session_start();
    $pdo = new PDO("mysql:host=localhost;dbname=mudikjxm_bd;charset=utf8", 'mudikjxm_mudikjxm', '@ThW4F0+pY');
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e)
{
        die('Erreur : '.$e->getMessage());
}

*/


error_reporting(E_ALL); ini_set("display_errors", 1);
 

?>