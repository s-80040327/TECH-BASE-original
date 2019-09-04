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
 
   //データベース接続
   require_once("db.php");
   $pdo = db_connect();
 
   //エラーメッセージの初期化
   $errors = array();
 
   if(empty($_POST)) {
      header("Location: mission_6registration_mail_form.php");
	  exit();
   }
 
   $mail_post = $_SESSION['mail'];
   $account = $_SESSION['account'];
 
   //パスワードのハッシュ化(暗号化の不可逆版)
   $password_hash =  password_hash($_SESSION['password'], PASSWORD_DEFAULT);
 
   //ここでデータベースに登録する
   try{
      //例外処理を投げる（スロー）ようにする
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	  //トランザクション(データベースに対する一連の処理(SQL)の整合性を保つための機能。コミット時に初めてデータベースへ反映)開始
	  $pdo->beginTransaction();
	
	  //memberテーブルに本登録する
	  $statement = $pdo->prepare("INSERT INTO membertable (account,mail,password) VALUES (:account,:mail,:password_hash)");
	  //プレースホルダへ実際の値を設定する
      $statement->bindValue(':account', $account, PDO::PARAM_STR);
      $statement->bindValue(':mail', $mail_post, PDO::PARAM_STR);
      $statement->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
      $statement->execute();
		
      //pre_memberのflagを1にする
      $statement = $pdo->prepare("UPDATE pre_member SET flag=1 WHERE mail=(:mail)");
	  //プレースホルダへ実際の値を設定する
	  $statement->bindValue(':mail', $mail_post, PDO::PARAM_STR);
	  $statement->execute();
	
	  // トランザクション完了（コミット）
	  $pdo->commit();
		
	  //データベース接続切断
	  $pdo = null;
	
 	  //本登録完了確認メール
 
	   require 'phpmailer/src/Exception.php';
      require 'phpmailer/src/PHPMailer.php';
      require 'phpmailer/src/SMTP.php';
      require 'phpmailer/setting2.php';

      // PHPMailerのインスタンス生成
      $mail = new PHPMailer\PHPMailer\PHPMailer();

      $mail->isSMTP(); // SMTPを使うようにメーラーを設定する
      $mail->SMTPAuth = true;
      $mail->Host = MAIL_HOST; // メインのSMTPサーバー（メールホスト名）を指定
      $mail->Username = MAIL_USERNAME; // SMTPユーザー名（メールユーザー名）
      $mail->Password = MAIL_PASSWORD; // SMTPパスワード（メールパスワード）
      $mail->SMTPSecure = MAIL_ENCRPT; // TLS暗号化を有効にし、「SSL」も受け入れます
      $mail->Port = SMTP_PORT; // 接続するTCPポート

      // メール内容設定
      $mail->CharSet = "UTF-8";
      $mail->Encoding = "base64";
      $mail->setFrom(MAIL_FROM,MAIL_FROM_NAME);
      $mail->addAddress($mail_post, 'ひとこと日記本登録者様'); //受信者（送信先）を追加する
  //    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
  //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
  //    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
      $mail->Subject = MAIL_SUBJECT; // メールタイトル
      $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
      $body = "本登録が完了いたしました。";

      $mail->Body  = $body; // メール本文
      // メール送信の実行
    if(!$mail->send()) {
    	$errors['sendmail_check'] = 'メールの送信に失敗しました。';
    	$errors['sendmail_errormessage'] = 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
		//セッション変数を全て解除
		$_SESSION = array();
	
		//クッキーの削除(クッキーの有効期限をマイナスにすることで削除)
	   if (isset($_COOKIE["PHPSESSID"])) {
		   setcookie("PHPSESSID", '', time() - 1800, '/');
	   }
  
		//セッションを破棄する
	    session_destroy();
    	$message = '本登録が完了しました。';
    }	
      
   }catch (PDOException $e){
      //トランザクション取り消し（ロールバック）
      $pdo->rollBack();
      $errors['error'] = "もう一度やりなおして下さい。";
      print('Error:'.$e->getMessage());
   } 
   
 ?>
 
<!DOCTYPE html>
<html>
<head>
<title>会員登録完了画面</title>
<meta charset="utf-8">
</head>
<body bgcolor = "#e6efa" text = "#191970">
 
 
<?php if (count($errors) === 0): ?>
<h1>会員登録完了画面</h1>
<p><?=$message?></p>
<p>登録完了いたしました。ログイン画面からどうぞ。</p>
    <a href ="postslist.php" target="_blank">投稿一覧はこちら</a>
<p><a href="signUp.php">ログイン画面</a></p>
 
<?php elseif(count($errors) > 0): ?>
 
<?php
   foreach($errors as $value){
	   echo "<p>".$value."</p>";
   }
?>
<?php endif; ?>
</body>
</html>