<?php
   session_start();    //セッションスタート。サーバはクライアント側のクッキーを調べる。もしセッションIDのクッキー（セッションクッキー）が存在しなければ発行
   header("Content-type: text/html; charset=utf-8");
 
   //エラーメッセージの初期化
   $errors = array();

   //クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
   if ($_POST['token'] != $_SESSION['token']){
      echo "不正アクセスの可能性あり";
      exit();
   }
 
   //クリックジャッキング対策
   header('X-FRAME-OPTIONS: SAMEORIGIN');
 
   //データベースへの接続を行う
   require_once("db.php");
   $pdo = db_connect();
   
   

   //postされていない場合は入力フォームのページ
   if(empty($_POST['account'])) {
      if($_POST['account']=="" || $_POST['password']==""){
         $errors['kuran_check'] = "IDとパスワードを入力してください";
      }else{
         header("Location: signUp.php");
         exit();
      }
      
   }else{
   //POSTされたデータを変数に入れる
   if(isset($_POST['account'])){	   
      $account =  $_POST['account'];
      $password= $_POST['password'];
      $sql = "CREATE TABLE IF NOT EXISTS membertable"
      ."("
      ."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
      ."account VARCHAR(50) NOT NULL,"
      ."mail VARCHAR(50) NOT NULL,"
      ."password VARCHAR(128) NOT NULL,"
      ."flag TINYINT(1) NOT NULL DEFAULT 1"
      .")ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;";
      $stmt = $pdo ->query($sql);
      
      //DB内でPOSTされたIDを検索
      $sql = 'SELECT * FROM membertable where account=:account ';		
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':account', $account, PDO::PARAM_STR);
      $stmt->execute();
      $result= $stmt->fetch();

      if(!isset($result)){
          $errors['account_check']="IDが間違っております";
      }else{
         if(password_verify($password, $result['password'])) { 
            session_regenerate_id(true); //sessionIDを新しく生成し、置き換える
             $_SESSION['account']=$result['account'];
             $_SESSION['mail'] = $result['mail'];
             $message = "ログインしました";    
         }else{
             $errors['account_check']="IDまたはパスワードが間違っております";
         }
      }
    }
   }
?>
<html>
    <head>
       <meta charset="utf-8">
       <title>login</title>
    </head>
    <body bgcolor = "#e6efa" text = "#191970">
 
    
    <?php if (count($errors) === 0): ?>
    <h1>ログイン完了画面</h1>
    <p><?=$message?></p>
    <a href = "postslist.php">投稿一覧はこちら</a>
 
   <?php elseif(count($errors) > 0): ?>
   <h1>ログイン失敗</h1>
   <?php
      foreach($errors as $value){
	    echo "<p>".$value."</p>";
    }
   ?>
   <input type="button" value="戻る" onClick="history.back()">
   <a href ="password_reset.php">passwordを忘れた場合はこちら</a>  
   <?php endif; ?>

   </body>
</html>
 
  

    
      

