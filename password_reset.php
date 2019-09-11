<?php
   session_start();    //セッションスタート。サーバはクライアント側のクッキーを調べる。もしセッションIDのクッキー（セッションクッキー）が存在しなければ発行
   header("Content-type: text/html; charset=utf-8");

   //クロスサイトリクエストフォージェリ（CSRF）対策
   $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
   $token = $_SESSION['token'];
 
   //クリックジャッキング対策
   header('X-FRAME-OPTIONS: SAMEORIGIN');
?>
<!DOCTYPE html>
<html>
   <head>
   <title>passwordリセット画面</title>
   <meta charset="utf-8">
   <link rel=stylesheet type="text/css" href="fontstyle.css">  
   <link rel=stylesheet type="text/css" href="submit_bottom.css">
   <link rel=stylesheet type="text/css" href="text_box.css">  
   <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
   </head>
   <body bgcolor = "#e6efa" text = "#191970">
   <h1>パスワード再設定</h1>
   <h3>パスワードを変更するアカウント情報を入力してください</h3>
   
   <form action="password_reset_check.php" method="post">

      <p><div class="cp_iptxt">
         <input type="text" name="mail" size="50" placeholder="mail">
         <i class="fa fa-envelope fa-lg fa-fw" aria-hidden="true"></i>
      </div>
      </p>
      <p><div class="cp_iptxt">
         <input type="text" name="account" placeholder="アカウント名">
         <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
      </div>
      </p>

      <input type="hidden" name="token" value="<?=$token?>">
      <input type="submit" class="btn" id="orange_btn" value="このアカウントを変更する">

   </form>
   </body>
</html>