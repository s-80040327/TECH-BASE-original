<?php
    session_start();      
    header("content-type: text/html; charset=utf-8"); //何してるのか微妙

   //クロスサイトリクエストフォージェリ（CSRF）対策
   $token = $_SESSION['token'];
 
   //クリックジャッキング対策
   header('X-FRAME-OPTIONS: SAMEORIGIN');


   //データベースへの接続を行う
   require_once("db.php");
   $pdo = db_connect();

   //仮登録用のpre_memberデーブル作成
   $sql = "CREATE TABLE IF NOT EXISTS pre_member"
   ."("
   ."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
   ."urltoken VARCHAR(128) NOT NULL,"              //トークン(ランダムの文字列,URLに含める)
   ."mail VARCHAR(50) NOT NULL,"
   ."date DATETIME NOT NULL,"
   ."flag TINYINT(1) NOT NULL DEFAULT 0"           //デフォルトが0、会員登録が完了すると1
   .")ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;";
   $stmt = $pdo ->query($sql);
?>
<html>
   <head>
      <meta charset="utf-8">
      <link rel=stylesheet type="text/css" href="fontstyle.css"> 
      <link rel=stylesheet type="text/css" href="submit_bottom.css">
      <link rel=stylesheet type="text/css" href="text_box.css">  
      <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet"> 
      <title>mission_6-2</title>
   </head>
   <body bgcolor = "#e6efa" text = "#191970">
   <h1>メール登録画面</h1>
 
   <form action="mission_6registration_mail_check.php" method="post">
   
   <p><div class="cp_iptxt">
         <input type="text" name="mail" placeholder="mail">
         <i class="fa fa-envelope fa-lg fa-fw" aria-hidden="true"></i>
      </div></p>

<div style="margin-left:40px">
    <input type="button" class="btn" id="dark_btn" value="戻る" onClick="history.back()">　　
    <input type="hidden" name="token" value="<?=$token?>">
    <input type="submit" class="btn" id="orange_btn" value="登録する">
</div>
   </form>
   </body>
</html>