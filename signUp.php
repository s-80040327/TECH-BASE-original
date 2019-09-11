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
   <link rel=stylesheet type="text/css" href="fontstyle.css">  
   <link rel=stylesheet type="text/css" href="submit_bottom.css">
   <link rel=stylesheet type="text/css" href="text_box.css">  
   <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
　 </head>
　 <body bgcolor = "#e6efa" text = "#191970">
　 <h1>ログインしませんか・・・</h1>
   <form action = "login.php" method = "post">
      
   <p><div class="cp_iptxt">
	 <input type="text" name="account" placeholder="アカウント名">
	 <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
   </div></p>
   <p><div class="cp_ippass">
	 <input type="password" name="password" placeholder="password">
	 <i class="fa fa-unlock fa-lg fa-fw" aria-hidden="true"></i>
  </div></p>
        
      
   <div style="margin-left:40px">
        <input type="button" class="btn" id="dark_btn" value="戻る" onClick="history.back()">　　<?php //クリックしたらhistory.back()関数(前のページへ戻る)を呼び出す?>
        <input type="hidden" name="token" value="<?=$token?>">
        <input type="submit" class="btn" id="orange_btn" value="ログイン">
   </div>
    </form>
   <div style="margin-left:40px">
      <i class="fa fa-user-plus"></i> 
      <a href ="mission_6registration_mail_form.php">アカウント作成はこちら</a>
   </div>
    </body>
</html>