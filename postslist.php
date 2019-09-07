<html>
	<head>
  <meta charset="utf-8">
  <link rel=stylesheet type="text/css" href="fontstyle.css">  
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
	<title>postslist</title>
	</head>
	<body bgcolor = "#e6efa" text = "#191970" >
		<h1><font size = "6" color = "#4b0082" >みんなの投稿</font><br><br></h1>

    <div class="scroll_botton">
        <a id="demo_scroll_top" herf="#">ページtopへ戻る</a>
      </div>
     <style type="text/css">
      #pagejump_demo .scroll_button a{
      position: fixed;
      display: block;
      right:50px;
      bottom:50px;
      background: #313131;
      color:#fff;
      padding:20px;
      }
      </style>
      <script type="text/javascript">
      $(function(){
         $("a#demo_scroll_top[href^=#]").click(function(){
           var speed = 1200;
           var href = $(this).attr("href");
           var target = $(href == "#" || href == ""?"html" : href);
           var position = target.offset().top;

           $("body, html").animate({scrollTop:position}, speed, "swing");
           return false;
         });
      });
      </script>
    <div class="scroll_botton_btm">
        <a href="#page-bottom">ページの一番下へ</a>
    </div>
    <style type="text/css">
    #pagejump_demo .scroll_button_btm a{
    position: fixed;
    display: block;
    right:50px;
    top:50px;
    background: #313131;
    color:#fff;
    padding:20px;
    }
    </style>
    <script type="text/javascript">
    $(function(){
      $("a[href^=#page-bottom]").click(function(){
        $('html, body').animate({
          scrollTop: $(document).height()
        },1500);
        return false;
      });
    });
    </script>
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
        if(count($results)!=0){
          foreach($results as $row){
           echo $row['id'].',';
           echo $row["account"].',';
           echo $row["datetime"]."<br/>";
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
           echo "<br/><br/>";
           echo "<hr>";
          }
        }else{
          echo "投稿はありません<br/>";
        }
     
?>
     <a href = "newpost.php">新規投稿</a><br>
     <a href = "deletepost.php">投稿削除</a><br>
     <a href ="logout.php" >ログアウト</a>
      
 </body>
</html>

