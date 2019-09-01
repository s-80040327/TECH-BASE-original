<?php
   function db_connect(){
      $dsn = '****';
      $user = '****';
      $password = '****';
      $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
      return $pdo; //これないと、＄pdoが渡されない
   }
?>
