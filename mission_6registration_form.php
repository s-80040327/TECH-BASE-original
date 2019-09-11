<?php
session_start();

header("Content-type: text/html; charset=utf-8");

//クロスサイトリクエストフォージェリ（CSRF）対策
if(isset($_SESSION['token'])){
   $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
}
$token = $_SESSION['token'];

//クリックジャッキング対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

//データベース接続
require_once("db.php");
$pdo = db_connect();

//エラーメッセージの初期化
$errors = array();

if(empty($_SESSION['urltoken'])) {
	header("Location: mission_6registration_mail_form.php");
	exit();
}else{
	//GETデータを変数に入れる
	$urltoken = isset($_SESSION['urltoken']) ? $_SESSION['urltoken'] : NULL;
	//メール入力判定
	if ($urltoken == ''){
		$errors['urltoken'] = "もう一度登録をやりなおして下さい。";
	}else{
		try{
			//例外処理を投げる（スロー）ようにする
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			//flagが0の未登録者・仮登録日から24時間以内
			$statement = $pdo->prepare("SELECT mail FROM pre_member WHERE urltoken=(:urltoken) AND flag =0 AND date > now() - interval 24 hour");
			$statement->bindValue(':urltoken', $urltoken, PDO::PARAM_STR);
			$statement->execute();
			
			//レコード件数取得
			$row_count = $statement->rowCount();
			
			//24時間以内に仮登録され、本登録されていないトークンの場合
			if( $row_count ==1){
				$mail_array = $statement->fetch();
				$mail = $mail_array['mail'];
				$_SESSION['mail'] = $mail;
			}else{
				$errors['urltoken_timeover'] = "このURLはご利用できません。有効期限が過ぎた等の問題があります。もう一度登録をやりなおして下さい。";
			}
			
			//データベース接続切断
			$pdo = null;
			
		}catch (PDOException $e){
			print('Error:'.$e->getMessage());
			die();
		}
	}
}

?>

<!DOCTYPE html>
<html>
   <head>
   <title>会員登録画面</title>
   <meta charset="utf-8">
   <link rel=stylesheet type="text/css" href="fontstyle.css">  
   <link rel=stylesheet type="text/css" href="submit_bottom.css">
   <link rel=stylesheet type="text/css" href="text_box.css">  
   <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
   </head>
   <body bgcolor="#e6efa" text="#191970">
   <h1><font size = "6" color = "#4b0082" >会員登録画面</font></h1>

   <?php if (count($errors) === 0): ?>

      <form action="mission_6registration_check.php" method="post">

		 <p><div style="margin-left:45px">
	     <i class="fa fa-envelope fa-lg fa-fw" aria-hidden="true"></i> 
		 <?=htmlspecialchars($mail, ENT_QUOTES, 'UTF-8')?>
		</div></p>
		 <p><div class="cp_iptxt">
		  <input type="text" name="account" placeholder="アカウント名">
	      <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
         </div></p>
         <p><div class="cp_ippass">
         <input type="password" name="password" placeholder="password">
		 <i class="fa fa-unlock fa-lg fa-fw" aria-hidden="true"></i>
         </div></p>
		 <div style="margin-left:45px">
         <input type="hidden" name="token" value="<?=$token?>">
         <input type="submit" class="btn" id="orange_btn" value="確認する">
         </div>
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