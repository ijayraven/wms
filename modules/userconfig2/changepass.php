<?php
session_start();
include("../../common/session.php");
include('../../adodb/adodb.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>location='../../index.php'</script>";
}
$conn	=	ADONewConnection('mysqlt');
$dbconn	=	$conn->Connect('192.168.255.10','root','','WMS_USER');
if ($dbconn == false) 
{
	echo $conn->ErrorMsg()."::".__LINE__;
	exit();
}
if ($_GET['action'] == "do_changepass")
{
	
	$txtOldPass		= $_GET['txtOldPass'];
	$txtNewPass		= $_GET['txtNewPass'];
	$datenow		= date("Y-m-d");
	$timenow		= date("H:i:s");

	$check_user		= "SELECT * FROM WMS_USER.USER WHERE USERNAME='{$_SESSION["username"]}' AND PASSWORD='{$txtOldPass}'";
	$rc_check_user	= $conn->Execute($check_user);
	if($rc_check_user == false)
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}
	else 
	{
		$cnt	=	$rc_check_user->RecordCount();
		
		if ($cnt == 0)
		{
			echo "$('#dialog_ok').html('Old Password is Incorrect');";	
			echo "$('#dialog_ok').dialog('open');";	
			echo "$('#dialog_update').dialog('close');";	
			exit();
		}
		else 
		{
			$value 			= "`PASSWORD`='$txtNewPass',`EDITBY`='{$_SESSION["username"]}',`EDITDATE`='{$datenow}'";
			$qryupdate		= "UPDATE WMS_USER.USER SET {$value} WHERE USERNAME='{$_SESSION["username"]}'";
				$rs_qryupdate	= $conn->Execute($qryupdate);
			if ($rs_qryupdate == false)
			{
				echo $conn->ErrorMsg()."::".__LINE__;
				exit();	
			}
			else 
			{
				echo "$('#dialog_ok').html('Password has been successfully reset.');";	
				echo "$('#dialog_ok').dialog('open');";	
				echo "$('#dialog_update').dialog('close');";	
				exit();
			}
		}
	}
	
	exit();
}

?>

<html>
<head>
<meta http-equiv="Content-Language" content="en" />
<meta name="GENERATOR" content="Zend Studio" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link 		href=	"../../includes/JQUERYUI/cupertino/jquery-ui.min.css"	rel	="stylesheet">
<script 	src	=	"../../includes/JQUERYUI/cupertino/external/jquery/jquery.js"></script>
<script 	src	=	"../../includes/JQUERYUI/cupertino/jquery-ui.min.js"></script>
<!--<script 	src	=	"changepass.js"></script>-->
<title>USER CONFIG</title>
<style type="text/css">
body {
	font-size: 80%;
	font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
}
table {
	font-size: 1em;
}
tr.dtl {
	color: #336699;
	font-size: 12px;
	font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
	font-weight: bold;
	padding: 0px;
}

tr.dtl:hover td { 
	background: #ceeef9; 
}
th.dtl, td.dtl { padding: 0px; text-align: center; border-bottom: 1px solid #ddd;
}

.div_text_shadow 
{
color: #f5f8f9;
font-size: 20px;
text-shadow: white 0px 0px 6px;
}
.css_button {
    font-size: 12px bold;
    font-family:"HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
    font-weight: normal;
    text-decoration: inherit;
    -webkit-border-radius: 8px 8px 8px 8px;
    -moz-border-radius: 8px 8px 8px 8px;
    border-radius: 5px 5px 5px 5px;
    border: 1px solid #3866a3;
    padding: 3px 8px;
    text-shadow: 1px 1px 0px #7cacde;
    -webkit-box-shadow: inset 1px 1px 0px 0px #fff;
    -moz-box-shadow: inset 1px 1px 0px 0px #fff;
    box-shadow: inset 1px 1px 0px 0px #fff;
    cursor: pointer;
    color: #fff;
    display: inline-block;
    background: -webkit-linear-gradient(90deg, #468ccf 5%, #63b8ee 100%);
    background: -moz-linear-gradient(90deg, #468ccf 5%, #63b8ee 100%);
    background: -ms-linear-gradient(90deg, #468ccf 5%, #63b8ee 100%);
    background: linear-gradient(180deg, #63b8ee 5%, #468ccf 100%);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#63b8ee",endColorstr="#468ccf");
}
.css_button:hover {
    background: -webkit-linear-gradient(90deg, #63b8ee 5%, #468ccf 100%);
    background: -moz-linear-gradient(90deg, #63b8ee 5%, #468ccf 100%);
    background: -ms-linear-gradient(90deg, #63b8ee 5%, #468ccf 100%);
    background: linear-gradient(180deg, #468ccf 5%, #63b8ee 100%);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr="#468ccf",endColorstr="#63b8ee");
}
.css_button:active {
    position:relative;
    top: 1px;
}
.ui-dialog-titlebar-close {
	display:none;
}
.ui-dialog-title{
    font-size: 80% !important;
}
.ui-dialog .ui-button-text {
    font-size: 10px;
    font-style: bold;
    padding: 8px 8px 8px 8px; /* Or whatever makes it small enough. */
}
input[type=text],input[type=password], textarea {
  -webkit-transition: all 0.30s ease-in-out;
  -moz-transition: all 0.30s ease-in-out;
  -ms-transition: all 0.30s ease-in-out;
  -o-transition: all 0.30s ease-in-out;
  outline: none;
  padding: 3px 0px 3px 3px;
  margin: 5px 1px 3px 0px;
  border: 1px solid #DDDDDD;
}
#txtPass2:focus 
{
	box-shadow: 0 0 5px rgba(81, 203, 238, 1);
	padding: 3px 0px 3px 3px;
	margin: 5px 1px 3px 0px;
	border: 1px solid rgba(81, 203, 238, 1);
}
#txtPass:focus 
{
	box-shadow: 0 0 5px rgba(81, 203, 238, 1);
	padding: 3px 0px 3px 3px;
	margin: 5px 1px 3px 0px;
	border: 1px solid rgba(81, 203, 238, 1);
}
input[type=text]:focus,input[type=password]:focus, textarea:focus {
	box-shadow: 0 0 5px rgba(81, 203, 238, 1);
	padding: 3px 0px 3px 3px;
	margin: 5px 1px 3px 0px;
	border: 1px solid rgba(81, 203, 238, 1);
}
.error{
	background: #f8dbdb;
	border-color: #e77776;
}
.ui-widget button{
	background: #f8dbdb;
	
}
.text_color{
	background:#FDFACD;
}
.label_text{
	font-size: 11px;
	font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
	font-weight: bold;
	color:#1D5987;
	
}

.text_white11 {
    font-size: 11px;
	font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
	font-weight: 800;
	color: #ffffff;
}
#dialog-link {
	padding: 0em 1em 0em 20px;
	text-decoration: none;
	position: relative;
	}
#dialog-link span.ui-icon {
	margin: 0 5px 0 0;
	position: absolute;
	left: .2em;
	top: 50%;
	margin-top: -8px;
}

</style>
</head>
<script>
function save(user)
{
	var hidUser = 	$('#hidUser').val();
	var txtOldPass = 	$('#txtOldPass').val();
	var txtNewPass = 	$('#txtNewPass').val();
	
	if(txtNewPass!='' )
	{
		if(hidUser!='')
		{
			$('#dialog_update').dialog('open');
			$('#dialog_update').html("Are you sure you want to Change Password for User "+hidUser+"?");
			$('#dialog_update').data('txtOldPass',txtOldPass);			
			$('#dialog_update').data('txtNewPass',txtNewPass);			
			$('#dialog_update').data('hidUser',hidUser);			
		}
		else
		{
			$('#dialog_ok').dialog('open');
			$('#dialog_ok').html('SESSION TIME OUT');
		}
	}
	else
	{
		$('#dialog_ok').dialog('open');
		$('#dialog_ok').html('INVALID PASSWORD');
	}
	
}
</script>
<body>
<table class="" align="center" width="90%" height="50%" border="0" cellpadding="0" cellspacing="0">
	<tr valign="top">
		<td>	
			<table align="center" border="0" cellpadding="0" cellspacing="0"  height="" width="100%"  style="background-color:#f5f8f9;">
				<tr>
					<td style="background-color:#f5f8f9; background-repeat:no-repeat;" width="10" height="5"></td>
					<td height="5" style="background-color:#f5f8f9; background-repeat:repeat-x;"></td>
					<td style="background-color:#f5f8f9; background-repeat:no-repeat;" width="10" height="5"></td>
				</tr>
				<tr>					
					<td rowspan="2" width="10" height="100%" style="background-color:#f5f8f9; background-repeat:repeat-y;"></td>					
					<td height="25" style="font-size: 17px; font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; font-weight:bold; color:#000000; text-align:center; background-image: url(../../includes/redmond/images/ui-bg_gloss-wave_55_5c9ccc_500x100.png); -moz-border-radius: 4px; -webkit-border-radius: 4px; background-position:center;"><span class="div_text_shadow">CHANGE PASSWORD</span></td>
					<td rowspan="2" width="10" height="100%" style="background-color:#f5f8f9; background-repeat:repeat-y;"></td>
				</tr>
				<tr>
					<td bgcolor="#f5f8f9" align="center" valign="top">
						<table border="0" cellpadding="10" cellspacing="0" width="100%" align="center">
							<tr>
								<td width="50%" align="right" class="label_text">
									Old Password&nbsp;:
								</td>
								<td width="50%" align="left">
									<input type="password" name="txtOldPass" id="txtOldPass">
								</td>							
							</tr>
							<tr>
								<td width="50%" align="right" class="label_text">
									New Password&nbsp;:
								</td>
								<td width="50%" align="left">
									<input type="password" name="txtNewPass" id="txtNewPass">
								</td>							
							</tr>
							<tr>
								<td colspan="2" align="center">
									<input type="button" name="btnSave" id="btnSave" class="ui-state-default ui-corner-all" value="UPDATE PASSWORD" onclick="save();">
								</td>
							</tr>
							<tr>
								<td>
									<input type="hidden" name="hidUser" id='hidUser' value="<?php echo $_SESSION["username"];?>">
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>		
</table>
<?php// include('userconfig_dialog.htm'); ?>
<div id="dialog_ok" title="Alert" ></div>
<div id="dialog_update" title="Alert" ></div>
</body>
</html>
<script>
$("#dialog_ok").dialog({
	bgiframe:true, resizable:false, modal:true, autoOpen: false, dialogClass:'no-close',closeOnEscape:false,
	overlay: {backgroundColor: '#000', opacity: 0.5},
	buttons: {
		'OK': function()
		{
			$(this).dialog('close');
			
		}
	}
});
$("#dialog_update").dialog({
	bgiframe:true, resizable:false, modal:true, autoOpen: false, dialogClass:'no-close',closeOnEscape:false,
	overlay: {backgroundColor: '#000', opacity: 0.5},
	buttons: {
		'OK': function()
		{
			var	hidUser	= $(this).data('hidUser');
			var	txtOldPass	= $(this).data('txtOldPass');
			var	txtNewPass	= $(this).data('txtNewPass');
			
			$.ajax({
				type : 'POST',
				url: 'changepass.php?action=do_changepass&txtNewPass='+txtNewPass+'&hidUser='+hidUser+'&txtOldPass='+txtOldPass,
				success: function(html)
				{
					eval(html);
//					$('#dialog_update').dialog('close');
//					$('#dialog_ok').dialog('open');
//					$('#dialog_ok').html('Succesfully Changed Your Password!');
					$('#txtNewPass').val('');
					$('#txtOldPass').val('');
				}		
			})
		},
		'CANCEL': function()
		{
			$(this).dialog('close');
			$('#txtNewPass').val('');
		}
	}
});
</script>