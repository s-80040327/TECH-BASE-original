<?php
   try{
      session_start();
 
      header("Content-type: text/html; charset=utf-8");
 
      //クロスサイトリクエストフォージェリ（CSRF）対策
      $_SESSION['token'] = base64_encode(openssl_random_pseudo_bytes(32));
      $token = $_SESSION['token'];
     
      //クリックジャッキング対策
      header('X-FRAME-OPTIONS: SAMEORIGIN');
 
      //データベース接続
      require_once("db.php");
      $pdo = db_connect();
 
      //エラーメッセージの初期化
      $errors = array();

      if((!isset($_POST["comment"]))){          //最初にページを開いたときの指示
         echo "入力してください。<br>";
      }elseif($_POST["comment"]==="" && $_FILES["upfile"]["name"] === ""){
         echo "入力してください。<br>";
      }else{
         $fname="";
         $extension="";
         $raw_data="";
         $comment="";
         $account = $_SESSION['account'];
         $date_show = date("Y/m/d H:i:s"); 
        
         if(isset($_POST["comment"]) && $_POST["comment"] !==""){
            $comment = $_POST["comment"];
         }
         //ファイルアップロードがあったとき
         if (isset($_FILES['upfile']['error']) && is_int($_FILES['upfile']['error']) && $_FILES["upfile"]["name"] !== ""){
            //エラーチェック
            switch ($_FILES['upfile']['error']) {   //アップロードされたファイルの情報が格納されたグローバル変数。$_FILES [ アップロードフォームのinput name値 ] [ アップロードされたファイル情報の項目 ]
               case UPLOAD_ERR_OK: // OK
                  break;
               case UPLOAD_ERR_NO_FILE:   // 未選択
                  throw new RuntimeException('ファイルが選択されていません', 400);
               case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
                  throw new RuntimeException('ファイルサイズが大きすぎます', 400);
               default:
                  throw new RuntimeException('その他のエラーが発生しました', 500);
            }

            //画像・動画をバイナリデータにする．
            $raw_data = file_get_contents($_FILES['upfile']['tmp_name']);

            //拡張子を見る
            $tmp = pathinfo($_FILES["upfile"]["name"]);
            $extension = $tmp["extension"];
            if($extension === "jpg" || $extension === "jpeg" || $extension === "JPG" || $extension === "JPEG"){
               $extension = "jpeg";
            }elseif($extension === "png" || $extension === "PNG"){
               $extension = "png";
            }elseif($extension === "gif" || $extension === "GIF"){
               $extension = "gif";
            }elseif($extension === "mp4" || $extension === "MP4"){
               $extension = "mp4";
            }else{
               echo "非対応ファイルです．<br/>";
               echo ("<a href=\"newpost.php\">戻る</a><br/>");
               exit(1);
            }

            //DBに格納するファイルネーム設定
            //サーバー側の一時的なファイルネームと取得時刻を結合した文字列にsha256をかける．
            $date = getdate();
            $fname = $_FILES["upfile"]["tmp_name"].$date["year"].$date["mon"].$date["mday"].$date["hours"].$date["minutes"].$date["seconds"];
            $fname = hash("sha256", $fname);
          }
          //画像・動画をDBに格納．
          $sql = "INSERT INTO poststable(account, comment, fname, extension, raw_data, datetime) VALUES (:account, :comment, :fname, :extension, :raw_data, :date);";
          $stmt = $pdo->prepare($sql);
          $stmt -> bindValue(':account', $account, PDO::PARAM_STR);
			 $stmt -> bindValue(':comment', $comment, PDO::PARAM_STR);
          $stmt -> bindValue(':fname',$fname, PDO::PARAM_STR);
          $stmt -> bindValue(':extension',$extension, PDO::PARAM_STR);
          $stmt -> bindValue(':raw_data',$raw_data, PDO::PARAM_STR);
          $stmt -> bindParam(':date', $date_show, PDO::PARAM_STR);
          $stmt -> execute();
          header("Location: postslist.php");
          exit();
      }
    }
    catch(RuntimeException $e){
        echo ("<a href=\"newpost.php\">戻る</a><br/>");
        exit($e->getMessage());  
    }
?>

<!DOCTYPE HTML>

<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>media</title>
</head>

<body bgcolor = "#e6efa" text = "#191970">
    <font size = "4" color = "#4b0082">送信用</font><br>
    <form action="newpost.php" enctype="multipart/form-data" method="post">
    コメント　：<textarea name="comment" rows = "3" cols = "19"></textarea><br>
    画像・動画: <input type="file" name="upfile"><br>
        ※画像はjpeg方式，png方式，gif方式に対応しています．動画はmp4方式のみ対応しています．<br>
               <input type="hidden" name="token" value="<?=$token?>">
               <input type="submit" value="アップロード">
    </form>
    <p><a href="postslist.php">投稿一覧画面</a></p>
    
</body>
</html>