<?php
   session_start();

   header("Content-type: text/html; charset=utf-8");

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

   //前後にある半角全角スペースを削除する関数
   function spaceTrim ($str) {
      // 行頭
	  $str = preg_replace('/^[ 　]+/u', '', $str);
	  // 末尾
	  $str = preg_replace('/[ 　]+$/u', '', $str);
      //引数
      return $str;
   }

   //エラーメッセージの初期化
   $errors = array();

   if(empty($_POST)) {
	  header("Location: mission_6registration_mail_form.php");
	  exit();
   }else{
	  //POSTされたデータを各変数に入れる($_POST['account']が存在すれば$_POST['account']を代入、しなければnullを代入)
	  $account = isset($_POST['account']) ? $_POST['account'] : NULL;
	  $password = isset($_POST['password']) ? $_POST['password'] : NULL;
   
     //前後にある半角全角スペースを削除
     $account = spaceTrim($account);
     $password = spaceTrim($password);

     //アカウント入力判定
     if ($account == ''){
        $errors['account'] = "アカウントが入力されていません。";
      }elseif(mb_strlen($account)>10){
         $errors['account_length'] = "アカウントは10文字以内で入力して下さい。";
      }else{
         //ここで本登録用のmemberテーブルにすでに登録されているidかどうかをチェックする。
		    $sql = "CREATE TABLE IF NOT EXISTS membertable"
          ."("
          ."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
          ."account VARCHAR(50) NOT NULL,"
          ."mail VARCHAR(50) NOT NULL,"
          ."password VARCHAR(128) NOT NULL,"
          ."flag TINYINT(1) NOT NULL DEFAULT 1"
          .")ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;";
          $stmt = $pdo ->query($sql);

          $sql = 'SELECT * FROM membertable';
          $stmt = $pdo->query($sql);
          $results = $stmt->fetchAll();
          if(count($results) != 0){
             foreach ($results as $word){
               if($account == $word['account']){
                  $errors['account_check'] = "このIDはすでに利用されております。";
               }
             }
          }
      }
	
     //パスワード入力判定
     if ($password == ''):
	     $errors['password'] = "パスワードが入力されていません。";
     elseif(!preg_match('/^[0-9a-zA-Z]{5,30}$/', $_POST["password"])):
        $errors['password_length'] = "パスワードは半角英数字の5文字以上30文字以下で入力して下さい。";
     else:
	     $password_hide = str_repeat('*', strlen($password));
     endif;
	
    }

    //エラーが無ければセッションに登録
    if(count($errors) === 0){
        $_SESSION['account'] = $account;
        $_SESSION['password'] = $password;
    }

?>

<!DOCTYPE html>
<html>
   <head>
   <title>会員登録確認画面</title>
   <meta charset="utf-8">
   </head>
   <body bgcolor = "#e6efa" text = "#191970">
   <h1>会員登録確認画面</h1>

   <?php if (count($errors) === 0): ?>

      <form action="mission_6registration_insert.php" method="post">

        <p>メールアドレス：<?=htmlspecialchars($_SESSION['mail'], ENT_QUOTES)?></p>
        <p>アカウント名：<?=htmlspecialchars($account, ENT_QUOTES)?></p>
        <p>パスワード：<?=$password_hide?></p>

        <input type="button" value="戻る" onClick="history.back()">　　<?php //クリックしたらhistory.back()関数(前のページへ戻る)を呼び出す?>
        <input type="hidden" name="token" value="<?=$_POST['token']?>">
        <input type="submit" value="登録する">

      </form>

   <?php elseif(count($errors) > 0): ?>

   <?php
      foreach($errors as $value){
         echo "<p>".$value."</p>";
      }
   ?>

   <input type="button" value="戻る" onClick="history.back()">

   <?php endif; ?>
 
   </body>
</html>