<html>
<head>
<title>TECH-BASE homepage start</title>
</head>
<?php
session_start();
if(empty($_GET)) {
	header("Location: mission_6registration_mail_form.php");
	exit();
}else{
//GETデータを変数に入れる
	$_SESSION['urltoken'] = isset($_GET['urltoken']) ? $_GET['urltoken'] : NULL;
}
?>
<frameset rows="13%,*">
 <frame src="TECH-BASE title.html" name="title">
 <frame src="mission_6registration_form.php" name="content">
</frameset>
<body>
</body>
</html>