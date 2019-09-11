<!DOCTYPE HTML>

<html lang="ja">
<html>
<head>
  <meta charset="utf-8">  
  <title>postslist</title>
  <link rel="stylesheet" type="text/css" href="postlist.css">  
  <link rel="stylesheet" type="text/css" href="fontstyle.css">  
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.4/css/all.css">
  <link rel="stylesheet" type="text/css" href="scroll_bottom.css">
  <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script type="text/javascript">
    jQuery(function() {
       var pagetop = $('#page_top');    
       pagetop.hide();
       $(window).scroll(function () {
          if ($(this).scrollTop() > 100) {  //100pxスクロールしたら表示
            pagetop.fadeIn();
          } else {
            pagetop.fadeOut();
          }
       });
       pagetop.click(function () {
          $('body,html').animate({
             scrollTop: 0
          }, 500); //0.5秒かけてトップへ移動
          return false;
       });
    });
    </script>
	</head>
	<body bgcolor = "#e6efa" text = "#191970" >
		<h1><font size = "6" color = "#4b0082" >みんなの投稿</font><br><br></h1>
   <ul>
     <li><i class="fa fa-mail-bulk fa-lg fa-fw"></i><a href = "newpost.php">今日のひとこと</a></li>
     <li><i class="fa fa-trash-alt fa-lg fa-fw"></i><a href = "deletepost.php">間違えたとき</a></li>
     <li><i class="fa fa-sign-out-alt fa-lg fa-fw"></i><a href ="logout.php" >ログアウト</a><li>
   </ul>
   
 <?php
      session_start();
      header("Content-type: text/html; charset=utf-8");
     
      
      //クリックジャッキング対策
      header('X-FRAME-OPTIONS: SAMEORIGIN');

      //データベースへの接続を行う
      require_once("db.php");
      $pdo = db_connect();
 
      $sql = "CREATE TABLE IF NOT EXISTS poststable"
                	."("
	                ."id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
                  ."account VARCHAR(50) NOT NULL," 
                  ."comment TEXT,"
                  ."fname VARCHAR(500),"
                  ."extension VARCHAR(5),"
                  ."raw_data MEDIUMBLOB,"
                  ."datetime DATETIME"
	                .")ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;";
                  $stmt = $pdo->query($sql);
                  //utf 8_general_ciは大文字小文字は区別しないで一致判別。全角半角は区別
                  
                  
      //DBから取得して表示する．
      $sql = "SELECT * FROM poststable ORDER BY id;";
      $stmt = $pdo->query($sql);
      $results = $stmt->fetchall();
      ?>
        <?php if(count($results)!=0):?>
          <?php foreach($results as $row):?>
          
          <div id="postlist">
           <div class="num"><?php echo $row['id'];?></div>
          <div class="fusen" style="padding-left: 60px;">
          <ul>
           <li>
           <?php echo $row["account"]."<br/>";
           $submittime = new DateTime($row['datetime']);
           echo $submittime->format('Y年m月d日 H時i分s秒')."<br/>";
           if($row["comment"]!=""){
             echo $row["comment"]."<br/>";
           }
           if($row["fname"]!=""){
              //動画と画像で場合分け
              $target = $row["fname"];
              if($row["extension"] == "mp4" ||$row["extension"] == "mov"){
                 echo "<video src=\"import_media.php?target=$target\" width=\"426\" height=\"240\" controls></video>";
              }elseif($row["extension"] == "jpeg" || $row["extension"] == "png" || $row["extension"] == "gif"){
                 echo "<img src='import_media.php?target=$target'width='426' controls>";
              }
           }
           echo "<br/><br/>";?>
           </li>
           </ul>
         </div>
         </div>
          <?php endforeach; ?>
                 
          <?php else:?>
          <?php echo "投稿はありません<br/>";?>
          <?php endif; ?> 
     
     
     <div id="page_top"><a href="#"></a></div>
     <!-- トップに戻るボタン -->
<div id="page-top" class="blogicon-chevron-up"></div>

 </body>
</html>

