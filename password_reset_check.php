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
       $errors['account_check']="IDが間違っております";
   }else{
       if($result['mail']==$mail_post){
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
   </head>
   <body>
   <h1>パスワード再設定</h1>

   <?php if (count($errors) === 0): ?>
   <p><?=$message?></p>
      <form action="password_reset_insert.php" method="post">
         <p>パスワード：<input type="password" name="password"></p>
 
         <input type="hidden" name="token" value="<?=$token?>">
         <input type="submit" value="変更する">
 
      </form>
 
   <?php elseif(count($errors) > 0): ?>

   <?php
      foreach($errors as $value){
	      echo "<p>".$value."</p>"; 
      }
   ?>

   <?php endif; ?>

   </body>
</html>
 
 