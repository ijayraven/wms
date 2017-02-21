<?php
	include('../wms/modules/login.php');
	//sample change
?>
<html>
<head>
<title>FDC WMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!--Designed by Noli R. Gones//-->
<style type="text/css">
#sub {position:relative; margin-top:0px; left:0px; display:block; width:80px;}
#sub a.butlog, #sub a.butlog:visited {display:block; width:80px; height:24px; line-height:22px; background:url(images/butS.gif); background-position:top left; background-color:transparent; text-align:center; color:#2d4865; text-decoration:none; font-family:Arial, Helvetica, sans-serif; font-size:11px; font-weight:bold; margin-top:0px;}
#sub a.butlog:hover {background-position:0 -24px; line-height:24px; overflow:hidden; color:#ffffff;cursor:pointer;}
#sub a.butlog:active {background-position:0 -48px; color:#2d4865;
}

body {	
	margin:0;
	background-color:#82bafc;
}

input {
	width:160px;
	font-family:Arial, Helvetica, sans-serif;
	font-size:12px;
	font-weight:bold;
}
</style>
</head>
<body onload="fOnload();">
<form name = "login" method="POST" action="">
 <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td height="62" valign="top">
			<table width="100%" height="62" border="0" cellpadding="0" cellspacing="0" style="background-image: url(images/topbanbg.gif); background-repeat:repeat-x;">
				<tr>
					<td style="background-image: url(images/toplogo.gif); background-repeat:no-repeat; background-position:left top;"></td>
				</tr>
	  		</table>
    	</td>
	</tr>
	<tr>
		<td valign="top">
			<table width="100%" height="24" border="0" cellpadding="0" cellspacing="0" style="background-image:url(images/menubg.gif); background-repeat: repeat-x;">
				<tr>
					<td></td>
				</tr>
			</table>
    	</td>
	</tr>
	<tr>
		<td height="100%">
			<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>
                        <table align="center" width="300" height="170" border="0" cellpadding="0" cellspacing="0" style="background-image:url(images/logbg.gif); background-repeat:no-repeat;)">
                            <tr>
                                <td colspan="5" height="23" style="font-family: Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold; color:#00ffff; text-align:center;">LOGIN</td>
                            </tr>
                            <tr>
                                <td colspan="5" height="10"></td>
                            </tr>
                            <tr>
                                <td colspan="5" height="12" style="font-family: Arial, Helvetica, sans-serif; font-size:11px; text-align:center; color:#2d4865;">Please enter Username and Password.</td>
                            </tr>
                            <tr>
                                <td colspan="5" height="10"></td>
                            </tr>
                            <tr>
                                <td height="20" width="70"></td>
                                	<td colspan="3" height="20" align="center">
                                		<input type="text" name="user" id="user" maxlength="20" autocomplete="off">
                                	</td>
                                <td height="20" width="70"></td>
                            </tr>
                            <tr>
                              <td colspan="5" height="12" style="font-family: Arial, Helvetica, sans-serif; font-size:11px; text-align:center; color:#2d4865;">Username</td>
                            </tr>
                            <tr>
                             <td height="20" width="70"></td>
                             	<td colspan="3" height="20" align="center">
                             		<input type="password" name="pass" id="pass" maxlength="20"  autocomplete="off">
                             	</td>
                             <td height="20" width="70"></td>
                            </tr>
                            <tr>
                                <td colspan="5" height="12" style="font-family: Arial, Helvetica, sans-serif; font-size:11px; text-align:center; color:#2d4865;">Password</td>
                            </tr>
                            <tr>
                                <td colspan="5" height="10"></td>
                            </tr>
                            <tr>
                                <td colspan="5" height="24">
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td width="125">&nbsp;</td>
                                        <td><div id="sub"><a class="butlog" onClick="fValidate()" style="line-height:24px;">Submit</a></div></td>
                                        <td width="125">&nbsp;</td>
                                    </tr>
                                    </table>
								</td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                            </tr>
                        </table>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
				</tr>
			</table>
    	</td>
	</tr>
	<tr>
		<td valign="bottom">
			<table width="100%" height="16" border="0" cellpadding="0" cellspacing="0" style="background-image:url(images/botbg.gif); background-color:#0a3e89; background-repeat:repeat-y; background-position:left;">
				<tr>
					<td style="font-family:Arial, Helvetica, sans-serif; font-size:9px; color:#81bafd; text-align:right;">Copyright &copy; 2013 Data Edge Corporation. All rights reserved.&nbsp;&nbsp;&nbsp;</td>
				</tr>
			</table>
    	</td>
	</tr>
</table>
</form>
</body>
</html>
<script>
document.onkeydown = keyHit2
function keyHit2(evt)
{
	switch(evt.which)
	{
		case 13:
			fValidate();
		break;
	}
}

function fOnload()
{
	document.login.user.focus();
}

function fValidate() {
	var msg = "";
	var flag = false;
	var user = document.login.user.value;
	var pass = document.login.pass.value;

	if(user == "") {
		msg = msg + " Username\n";
		flag = true;
	}
	if(pass == "") {
		msg = msg + " Password\n";
		flag = true;
	}
	if(flag) {
		alert("Please fill in the required field(s): \n\n" + msg);		 
	} else {
		
		document.login.submit();
	}
}
</script>