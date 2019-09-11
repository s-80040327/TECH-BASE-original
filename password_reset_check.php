<?php
   session_start();
 
   header("Content-type: text/html; charset=utf-8");
 
   //クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
   if ($_POST['token'] != $_SESSION['token']){
      echo "不正アクセスの可能性あり";
	  exit();
   }
   $token = $_SESSION['token'];
 
 
   //クリックジャッキング対策
   header('X-FRAME-OPTIONS: SAMEORIGIN');
 
   //データベース接続
   require_once("db.php");
   $pdo = db_connect();
 
   //エラーメッセージの初期化
   $errors = array();

   //POSTされてないとき
   if(empty($_POST)) {
     header("Location: password_reset.php");
     exit();
   }

   $mail_post = $_POST['mail'];
   $account = $_POST['account'];

   //DB内でPOSTされたIDを検索
   $sql = 'SELECT * FROM membertable where account=:account ';		
   $stmt = $pdo->prepare($sql);
   $stmt->bindParam(':account', $account, PDO::PARAM_STR);
   $stmt->execute();
   $result= $stmt->fetch();
   if(!isset($result)){
       $errors['account_check']="IDまたはメールアドレスが間違っております";
   }else{
       if($result['mail']!="" && $result['mail']==$mail_post){
           $message="変更後のパスワードを入力してください";
           $_SESSION['account'] = $account; 
       }else{
           $errors['account_check']="IDまたはメールアドレスが間違っております";
       }
   }
?>

<!DOCTYPE html>
<html>
   <head>
   <title>password変更画面</title>
   <meta charset="utf-8">
   <link rel=stylesheet type="text/css" href="fontstyle.css">  
   <link rel=stylesheet type="text/css" href="submit_bottom.css">
   <link rel=stylesheet type="text/css" href="text_box.css">  
   <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
   </head>
   <body bgcolor = "#e6efa" text = "#191970">
   <h1>パスワード再設定</h1>

   <?php if (count($errors) === 0): ?>
   <p><?=$message?></p>
      <form action="password_reset_insert.php" method="post">
      <p><div class="cp_ippass">
         <input type="password" name="password" placeholder="password">
         <i class="fa fa-unlock fa-lg fa-fw" aria-hidden="true"></i>
      </div></p>
      <div style="margin-left:40px">
         <input type="hidden" name="token" value="<?=$token?>">
         <input type="submit" class="btn" id="orange_btn" value="変更する">
      </div>
 
      </form>
 
   <?php elseif(count($errors) > 0): ?>
   <div style="margin-left:40px">
   <?php
      foreach($errors as $value){
	      echo "<p>".$value."</p>"; 
      }
   ?>
   </div>
   <div style="margin-left:40px">
   <input type="button" class="btn" id="dark_btn" value="戻る" onClick="history.back()">
   </div>
   <?php endif; ?>

   </body>
</html>
 
 