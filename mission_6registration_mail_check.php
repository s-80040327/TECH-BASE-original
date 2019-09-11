
   <?php
	  session_start();    //セッションスタート。サーバはクライアント側のクッキーを調べる。もしセッションIDのクッキー（セッションクッキー）が存在しなければ発行
	  header("Content-type: text/html; charset=utf-8");
 
	  //エラーメッセージの初期化
	  $errors = array();

     //クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
      if ($_POST['token'] != $_SESSION['token']){
        $errors['access_check'] = "不正アクセスの可能性あり";
        exit();
      }
 
      //クリックジャッキング対策
      header('X-FRAME-OPTIONS: SAMEORIGIN');
 
      //データベースへの接続を行う
      require_once("db.php");
      $pdo = db_connect();

 
      //エラーメッセージの初期化
      $errors = array();
 
      //postされていない場合は入力フォームのページ
      if(empty($_POST)) {
       	header("Location: mission_6registration_mail_form.php");
        exit();
      }else{
        //POSTされたデータを変数に入れる
	  if(isset($_POST['mail'])){	   
           $mail_post =  $_POST['mail'];
	
   	   //メール入力判定
	   if ($mail_post == ''){
	      $errors['mail'] = "メールが入力されていません。";
	   }else{
	      if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mail_post)){
                 $errors['mail_check'] = "メールアドレスの形式が正しくありません。";
	      }else{
		
		
		    //ここで本登録用のmemberテーブルにすでに登録されているmailかどうかをチェックする。
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
	       	           if($mail_post == $word['mail']){
		                  $errors['member_check'] = "このメールアドレスはすでに利用されております。";
					   }
					}
		    }
          }  
       }
		
      }
	 }
      
 
      if (count($errors) === 0){
	
	  $urltoken = hash('sha256',uniqid(rand(),1));
	  $url = "https://tb-210188.tech-base.net/TECH-BASEhomepage2.php"."?urltoken=".$urltoken; //「?urltoken=」とすることでGETメソッドによりトークンを取得できる。
	
	  //ここでデータベースに登録する
	  try{
        //例外処理を投げる（スロー）ようにする
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		//データベースへトークン、メールアドレス、日時(24時間制限のため)を代入
		$statement = $pdo->prepare("INSERT INTO pre_member (urltoken,mail,date) VALUES (:urltoken,:mail,now() )");
		
		//プレースホルダへ実際の値を設定する
		$statement->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
		$statement->bindValue(':mail', $mail_post, PDO::PARAM_STR);
		$statement->execute();
			
		//データベース接続切断
		$pdo = null;	
		
	  }catch (PDOException $e){
		print('Error:'.$e->getMessage());
		die();
	  }
	
      require 'phpmailer/src/Exception.php';
      require 'phpmailer/src/PHPMailer.php';
      require 'phpmailer/src/SMTP.php';
      require 'phpmailer/setting.php';

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
      $mail->addAddress($mail_post, 'ひとこと日記仮登録者様'); //受信者（送信先）を追加する
  //    $mail->addReplyTo('xxxxxxxxxx@xxxxxxxxxx','返信先');
  //    $mail->addCC('xxxxxxxxxx@xxxxxxxxxx'); // CCで追加
  //    $mail->addBcc('xxxxxxxxxx@xxxxxxxxxx'); // BCCで追加
      $mail->Subject = MAIL_SUBJECT; // メールタイトル
      $mail->isHTML(true);    // HTMLフォーマットの場合はコチラを設定します
      $body = "24時間以内に下記のURLからご登録下さい。".$url;

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
    	$message = 'メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。';
    }	
      }
 
   ?>
   <html>
   <head>
      <meta charset="utf-8">
      <link rel=stylesheet type="text/css" href="fontstyle.css">  
      <link rel=stylesheet type="text/css" href="submit_bottom.css">  
      <title>mission_6registration_mail_check</title>
   </head>
   <body bgcolor = "#e6efa" text = "#191970">

   <h1>メール確認画面</h1>
 
   <?php if (count($errors) === 0): ?>
 
   <p><?=$message?></p>
 
   <?php elseif(count($errors) > 0): ?>
 
   <?php
      foreach($errors as $value){
	    echo "<p>".$value."</p>";
    }
   ?>
 
   <input type="button" class="btn" id="dark_btn" value="戻る" onClick="history.back()">
 
   <?php endif; ?>
 
   </body>
</html>