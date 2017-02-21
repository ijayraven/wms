<?php
/**
* DYNAMIC MENU MODIFICATION ONLY
* Module Name	:	wms Menu
* Date Modified	:	June 2016
* @author Jay-R A. Magdaluyo <ijayraven@gmail.com>
*/

session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>location='index.php'</script>";
}
$conn			=	ADONewConnection('mysqlt');
$dbconn10		=	$conn->Connect('192.168.255.10','root','');
if ($dbconn10 == false) 
{
	echo $conn172->ErrorMsg()."::".__LINE__;
	exit();
}
function getLink($id,$LINKNAME,$DATASOURCE,$conn)
{
	$getlink	=	"SELECT * FROM WMS_USERS.MODULES WHERE MODULE_GROUP = '{$id}' AND STATUS ='Active' ORDER BY ORDERING";
	$RSgetlink	=	$conn->Execute($getlink);
	if($RSgetlink == false)
	{
		echo $errmsg	=	($conn->ErrorMsg()."::".__LINE__); 
		exit();
	}
	else 
	{
		while (!$RSgetlink->EOF) 
		{
			$ID				=	$RSgetlink->fields["ID"];
			$LINK_NAME		=	$RSgetlink->fields["LINK_NAME"];
			$MODULE_GROUP	=	$RSgetlink->fields["MODULE_GROUP"];
			$IS_GROUP		=	$RSgetlink->fields["IS_GROUP"];
			$LINK			=	$RSgetlink->fields["LINK"];
			$linkCnt		=	selval($conn, "WMS_USERS", "USER_ACCESS", "COUNT(LINK_ID)", "USERID = '{$_SESSION['username_id']}' AND LINK_ID = '{$ID}'");
			if($IS_GROUP == "N")
			{
				$linkhere	=	"$LINK";
				$class		=	"";
				$div_ul		=	"";
				$div_ul_end	=	"";
				$target		=	"target='mainFrame'";
			}
			else 
			{
				$linkhere	=	"#$LINK_NAME";
				$class		=	"class='parent'";
				$div_ul		=	"<div $DISPLAY><ul>";
				$div_ul_end	=	"</ul></div>";
				$target		=	"";
			}
			if ($linkCnt > 0)
			{
				$linklist	.= "<li $DISPLAY> <a href='$linkhere' $class $target> <span>$LINK_NAME</span> </a>";	
								if ($IS_GROUP == "Y")
								{
									$linklist	.=	$div_ul;
									$linklist	.=	getLink($ID,$LINK_NAME,$DATASOURCE,$conn);
									$linklist	.=	$div_ul_end;
								}
								
				$linklist	.= 	 "</li>";
			}
			
			$RSgetlink->MoveNext();
		}
		return $linklist;
	}
}
function selval($conn, $dbname, $tblname, $field, $condition)
{
	$selval		=	"SELECT $field FROM $dbname.$tblname WHERE $condition";
	$RSselval	=	$conn->Execute($selval);
	if($RSselval == false)
	{
		$errmsg	=	($conn->ErrorMsg()."::".__LINE__);
		db_funcs::logError($errmsg,$selval,"");
		exit();
	}
	else 
	{
		$data	=	$RSselval->fields["$field"];
		return $data;
	}
}

?>
<html>
<head>
<title>FDC WMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">
#page-container {
margin:0 0;
width: 170px;
text-align: left;
}

.close {
	display:none;	
}

.open {
	display:compact;
}
li span 
{
	font-weight:bold;
}
</style>
<link rel="stylesheet" type="text/css" media="all" href="style/nav_v.css" />
<!--<script src="script/disabled.js" language="JavaScript" type="text/javascript"></script>-->
<!--[if gte IE 5.5]>
<script language="JavaScript" src="script/nav_v.js" type="text/JavaScript"></script>
<![endif]-->
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="background-color: #82bafc;">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" >
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
					<td align="right" style="font-family: Times New Roman; font-size:15px; font-weight:bold; color:#6CD9C3; text-align:right;">
						Welcome, <?php echo $_SESSION['NAME']; ?>!&nbsp;
					</td>
				</tr>
			</table>
    	</td>
	</tr>
	<tr>
		<td height="100%">
			<table height="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td height="100%" id="menu">
						<table id="menu_table" height="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td bgcolor="#1c54a0" valign="top">
									<div id="page-container">
									  <ul id="navmenu-v">
												<?php
													$getModules		=	"SELECT M.* FROM WMS_USERS.MODULES AS M 
																		 LEFT JOIN WMS_USERS.USER_ACCESS AS A ON A.LINK_ID = M.ID
																		 WHERE M.MODULE_GROUP = ''  AND STATUS ='Active' AND A.USERID ='{$_SESSION['username_id']}'
																		 GROUP BY M.ID
																		 ORDER BY ORDERING";
													$RSgetModules	=	$conn->Execute($getModules);
													if($RSgetModules == false)
													{
														echo $errmsg	=	($conn->ErrorMsg()."::".__LINE__); 
														exit();
													}
														$aMainMenu	=	array();
														while (!$RSgetModules->EOF) 
														{
															$LINK_NAME	=	$RSgetModules->fields["LINK_NAME"];
															$ID			=	$RSgetModules->fields["ID"];
															$IS_GROUP	=	$RSgetModules->fields["IS_GROUP"];
															$LINK		=	$RSgetModules->fields["LINK"];
															
															$linkCnt	=	selval($conn, "WMS_USERS", "USER_ACCESS", "COUNT(LINK_ID)", "USERID = '{$_SESSION['username_id']}' AND LINK_ID = '{$ID}'");
															if($IS_GROUP == "N")
															{
																$linkhere	=	"$LINK";
																$class		=	"";
																$div_ul		=	"";
																$div_ul_end	=	"";
																$target		=	"target='mainFrame'";
															}
															else 
															{
																$linkhere	=	"#$LINK_NAME";
																$class		=	"class='parent'";
																$div_ul		=	"<div $DISPLAY><ul>";
																$div_ul_end	=	"</ul></div>";
																$target		=	"";
															}
															if ($linkCnt > 0)
															{
																echo 	 "<li $DISPLAY> <a href='$linkhere' $class $target> <span>$LINK_NAME</span> </a>";	
																			if ($IS_GROUP == "Y")
																			{
																				echo $div_ul;
																				echo getLink($ID,$LINK_NAME,$DATASOURCE,$conn);
																				echo $div_ul_end;
																			}
																echo 	 "</li>";
															}
															
															$RSgetModules->MoveNext();
														}
														
													?>
										<li><a href="logout.php" target="_self"><strong>Logout</strong></a></li>
									  </ul>
									</div>
								</td>
							</tr>
						</table>
					</td>
					<td align="left" bgcolor="#82bafc"><img src="images/close_vert.gif" style="cursor:pointer" onClick="Close(this);" title="Menu On/Off"></td>
					<td width="1600" id="blank" align="left">
						<iframe height="100%" width="100%" name="mainFrame" frameborder="0" scrolling="auto" allowtransparency="yes" src="table.html"></iframe>
					</td>
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
</body>
</html>
<script>
	function Close(obj)
	{
		if (obj.src.indexOf("images/close_vert.gif") != -1) 
		{
				obj.src = obj.src.replace("images/close_vert.gif","images/open_vert.gif");
				parent.document.getElementById("menu_table").className='close';
				x = parent.document.getElementById("menu").width +10;
				parent.document.getElementById("menu").width=10;
				parent.document.getElementById("blank").width=parseInt(parent.document.getElementById("blank").width+x);
		}
		else
		{
				obj.src = obj.src.replace("images/open_vert.gif","images/close_vert.gif");
				parent.document.getElementById("menu_table").className='open';
				parent.document.getElementById("menu").width=0;
				parent.document.getElementById("blank").width=1600;
		}
	}
	
</script>
