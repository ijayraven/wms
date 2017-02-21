<?php
session_start();
include("../common/logs_saver.php");
$datetime  = date("Y-m-d H:i:s");
$ipaddress = $_SERVER['REMOTE_ADDR'];
$file 	   = realpath(dirname(__FILE__));

$errLog = "========================================\n";
$errLog .= "[DATETIME] " 		.$datetime. 			"\n";
$errLog .= "[IPADDR] " 	.$ipaddress. 		"\n";
$errLog .= "[ACCESSED_FILE] " 		.$file.		 "\n";

write("../logs/access",date("Y-m")."_forbiddenaccess","$errLog");

$username = $HTTP_SESSION_VARS['login_username'];
  
if(session_register($username)){
 	$_SESSION['login_username'] = "";
    $_SESSION["login_dept"] = "";
	session_unset();
	session_destroy();
}else{
     session_unset();
	 session_destroy();
}
?>


<html>
<head>
	<title>403 Forbidden Access</title>
</head>
<body>

<p style="color:red;font-weight:bold;">Directory access is forbidden.</p>
<p style="color:red;font-weight:bold;">Your activities are being logged and monitored.</p>
</body>
</html>