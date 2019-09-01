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
 
   //POSTされなかったときは前のページへ
   if(empty($_POST)) {
      header("Location: password_reset_check.php");
	  exit();
   }

   //前後にある半角全角スペースを削除する関数
   function spaceTrim ($str) {
     // 行頭
     $str = preg_replace('/^[ 　]+/u', '', $str);
     // 末尾
     $str = preg_replace('/[ 　]+$/u', '', $str);
     //引数
     return $str;
    }
     $account = $_SESSION['account'];
     //POSTされたデータを各変数に入れる($_POST['password']が存在すれば$_POST['password']を代入、しなければnullを代入)
     $password = isset($_POST['password']) ? $_POST['password'] : NULL;

     //前後にある半角全角スペースを削除
     $password = spaceTrim($password);

     //パスワード入力判定
     if ($password == ''){
       $errors['password'] = "パスワードが入力されていません。";
     }elseif(!preg_match('/^[0-9a-zA-Z]{5,30}$/', $_POST["password"])){
       $errors['password_length'] = "パスワードは半角英数字の5文字以上30文字以下で入力して下さい。";
     }

     //パスワードのハッシュ化(暗号化の不可逆版)
     $password_hash =  password_hash($password, PASSWORD_DEFAULT);

    //エラーが無ければテーブルに登録
    if(count($errors) === 0){
        $sql = 'update membertable set password=:password where account=:account';
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindParam(':account', $account, PDO::PARAM_STR);
        $stmt -> bindParam(':password', $password_hash, PDO::PARAM_STR);
        $stmt -> execute();

        //セッション変数を全て解除
		$_SESSION = array();
	
		//クッキーの削除(クッキーの有効期限をマイナスにすることで削除)
	   if (isset($_COOKIE["PHPSESSID"])) {
		   setcookie("PHPSESSID", '', time() - 1800, '/');
	   }
  
		//セッションを破棄する
	    session_destroy();
    	$message = "編集されました。";
    }
?>
    <!DOCTYPE html>
    <html>
    <head>
    <title>password変更完了画面</title>
    <meta charset="utf-8">
    </head>
    <body bgcolor = "#e6efa" text = "#191970">
     
     
    <?php if (count($errors) === 0): ?>
    <h1>パスワード変更完了完了画面</h1>
    <p><?=$message?></p>
    <p>変更完了いたしました。ログイン画面からどうぞ。</p>
    <p><a href="signUp.php">ログイン画面</a></p>
     
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