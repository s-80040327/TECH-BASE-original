<?php
   session_start();    //セッションスタート。サーバはクライアント側のクッキーを調べる。もしセッションIDのクッキー（セッションクッキー）が存在しなければ発行
   header("Content-type: text/html; charset=utf-8");
   
   //クロスサイトリクエストフォージェリ（CSRF）対策
   $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
   $token = $_SESSION['token'];

   //クリックジャッキング対策
   header('X-FRAME-OPTIONS: SAMEORIGIN'); //フレームないのページ表示を同一ドメイン内のみ許可したい場合
 
   //データベースへの接続を行う
   require_once("db.php");
   $pdo = db_connect();
?>

<!DCTYPE html>
<html>
   <head>
   <title>ログイン画面</title>
　 <meta charset="utf-8">
　 </head>
　 <body bgcolor = "#e6efa" text = "#191970">
　 <h1>ようこそ、ログインしてください</h1>
   <form action = "login.php" method = "post">
      
         <p>アカウント名：<input type="text" name="account"></p>
         <p>パスワード：<input type="password" name="password"></p>
 
      <input type="button" value="戻る" onClick="history.back()">　　<?php //クリックしたらhistory.back()関数(前のページへ戻る)を呼び出す?>
      <input type="hidden" name="token" value="<?=$token?>">
      <input type="submit" value="ログイン">

    </form>
    <a href ="mission_6registration_mail_form.php">アカウント作成はこちら</a>
     
    </body>
</html>