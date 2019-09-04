<?php
    session_start();      
    header("content-type: text/html; charset=utf-8"); //何してるのか微妙

   //クロスサイトリクエストフォージェリ（CSRF）対策
   $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
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
      <title>mission_6-2</title>
   </head>
   <body bgcolor = "#e6efa" text = "#191970">
   <h1>メール登録画面</h1>
 
   <form action="mission_6registration_mail_check.php" method="post">
 
   <p>メールアドレス：<input type="text" name="mail" size="50"></p>
 
   <input type="hidden" name="token" value="<?=$token?>">
   <input type="submit" value="登録する">
 
   </form>
   </body>
</html>