<?php
    session_start();   
    header("Content-type: text/html; charset=utf-8");
    //クリックジャッキング対策
    header('X-FRAME-OPTIONS: SAMEORIGIN');
    //エラーメッセージの初期化
    $errors = array();
    if(!isset($_POST["delete"])){
       //クロスサイトリクエストフォージェリ（CSRF）対策
       $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
       $token = $_SESSION['token'];
       $errors['kuran']="入力してください。";
    }else{
        //クロスサイトリクエストフォージェリ（CSRF）対策のトークン判定
        if ($_POST['token'] != $_SESSION['token']){
           $errors['access_check'] = "不正アクセスの可能性あり";
        }else{
            $token = $_SESSION['token'];
      
            //データベース接続
            require_once("db.php");
            $pdo = db_connect();

            if($_POST["delete"] == "" || $_POST["password"] == ""){
                $errors['kuran'] ="削除する投稿番号とパスワードが入力されていません。";
            }else{
                $ID = $_POST["delete"];
                $password = $_POST["password"];

                $sql = 'SELECT * FROM poststable where id=:id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $ID, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch();
                if(!isset($result)){
                    $errors['id_check']="入力された投稿番号は存在しません";
                }else{
                    if($result['id']==""){
                        $errors['id_check']="入力された投稿番号は存在しません";  
                    }else{
                        $account = $result["account"];
                        $sql2 = 'SELECT * FROM membertable where account=:account';
                        $stmt2 = $pdo->prepare($sql2);
                        $stmt2->bindParam(':account', $account, PDO::PARAM_STR);
                        $stmt2->execute();
                        $result2 = $stmt2->fetch();

                        if(password_verify($password, $result2['password'])){
                            $sql3 = 'delete from poststable where id=:id';
                            $stmt3 = $pdo->prepare($sql3);
                            $stmt3->bindParam(':id', $ID, PDO::PARAM_INT);
                            $stmt3->execute();
                            $message = "投稿番号".$ID."が削除されました。";
                        }else{
                            $errors['pass_check']="パスワードが違います";  
                        }
                    }
                }
            }
        }
    }
?>

<!DOCTYPE HTML>

<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel=stylesheet type="text/css" href="fontstyle.css">
    <link rel=stylesheet type="text/css" href="submit_bottom.css">
    <link rel=stylesheet type="text/css" href="text_box.css">  
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">  
    <title>media</title>
</head>
<body bgcolor = "#e6efa" text = "#191970">
<?php if (count($errors) === 0): ?>
    <h1>投稿削除完了画面</h1>
    <p><?=$message?></p>
     
<?php elseif(count($errors) > 0): ?>
    <h1><font color = "#4b0082">削除</font><br></h1>
<?php
    foreach($errors as $value){
        echo "<p>".$value."</p>";
    }
?>
    <form action="deletepost.php" enctype="multipart/form-data" method="post">
    <p><div class="cp_iptxt">
       <input type="text" name="delete" placeholder="投稿番号">
       <i class="fa fa-sort-numeric-down fa-lg fa-fw" aria-hidden="true"></i>
    </div></p>
    <p><div class="cp_ippass">
	 <input type="password" name="password" placeholder="password">
	 <i class="fa fa-unlock fa-lg fa-fw" aria-hidden="true"></i>
    </div></p>
    <div style="margin-left:40px">
       <input type="hidden" name="token" value="<?=$token?>">
       <input type="submit" class="btn" id="orange_btn" value="削除">
    </div>
    </form>
    <?php endif; ?>
    <div style="margin-left:40px"> 
    <p><i class="fa fa-list-alt"></i> <a href="postslist.php">投稿一覧画面</a></p>
   </div>
    
</body>
</html>