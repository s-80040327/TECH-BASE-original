<?php
   session_start();
   header("Content-type: text/html; charset=uft-8");

   if(isset($_SESSION['mail'])){ //ダブルシングルクオート使い分けわからん
      $message = "ログアウトしました";
   }else{
       $message = "セッションがタイムアウトしました";
   }
   
   //セッション変数のクリア
   $_SESSION = array();

   //セッションクッキーも削除
   if (isset($_COOKIE["PHPSESSID"])) {
    setcookie("PHPSESSID", '', time() - 1800, '/');
   }

   //セッションクリア
       session_destroy();
?>
<html>
    <head>
       <meta charset="utf-8">
       <link rel=stylesheet type="text/css" href="fontstyle.css"> 
       <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet"> 
       <title>logout</title>
    </head>
    <body bgcolor = "#e6efa" text = "#191970">
    <h1>ログアウト完了画面</h1>
    <p><?=$message?></p>
    <i class="fa fa-sign-in-alt"></i><a href ="signUp.php">ログインはこちら</a>
   </body>
</html>
