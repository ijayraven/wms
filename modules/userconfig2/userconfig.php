<?php
session_start();
include("../../common/session.php");
include('../../adodb/adodb.inc.php');
//if (empty($_SESSION['username'])) 
//{
//	echo "<script>alert('You dont have a session!');</script>";
//	echo "<script>location='../../index.php'</script>";
//}
$conn	=	ADONewConnection('mysqlt');
$dbconn	=	$conn->Connect('192.168.255.10','root','','WMS_USER');
if ($dbconn == false) 
{
	echo $conn->ErrorMsg()."::".__LINE__;
	exit();
}

if ($_GET['action'] == "do_gen_transno")
{
	$today 			= date("Y-m-d");
	$datenow 		= date("Y-m-d H:i:s");
	
	# generate transno number
//	echo "HERE";exit();
	$id	= liqui_trxno($today);
	
	echo "$('#txtIdNo').val('$id');";

	exit();	
}
if($_GET['action'] == "do_search")
{
	$pageno		= $_GET['pageno'];
	$limit		= 10;
	$txtSearch	= $_GET['txtSearch'];
	$selField	= $_GET['selField'];
	if ($selField == "STATUS")
	{
		if ($txtSearch == 'Active' || $txtSearch == 'active')
		{
			$txtSearch = 'Y';
		}
		else if ($txtSearch == 'Inactive' || $txtSearch == 'inactive')
		{
			$txtSearch = 'I';
		}
	}
	
//	$txtSearch	= "\"" . strip_tags(preg_replace("/[\n\r ]/","\", \"",$txtSearch)) . "\"";
	$searchcnt		= "SELECT * FROM USER WHERE 1 AND $selField like '%{$txtSearch}%' ORDER by ID asc;";
	$rs_searchcnt	= $conn->Execute($searchcnt);
	
	$totalcnt	=	$rs_searchcnt->RecordCount();
	$totpagecnt	=	ceil($totalcnt/$limit);
	$from		=	$limit * $pageno;
	
	$search		= "SELECT * FROM USER WHERE 1 AND $selField like '%{$txtSearch}%' ORDER by ID asc LIMIT $from, $limit;";
	$rs_search	= $conn->Execute($search);
	if ($rs_search == false)
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}
	else 
	{
		if ($rs_search->RecordCount() > 0)
		{
			$table	=	"<table border=0 class=dtl cellpadding=0 cellspacing=0 width=80% align=center>";
			$counter=0;
			foreach ($rs_search as $key => $value )
			{
				$id_no		= $value['ID'];
				$username	= $value['USERNAME'];
				$name		= $value['NAME'];
				$dept		= $value['DEPT'];
				$user_level	= $value['USERLEVEL'];
				$status		= $value['STATUS'];
				
				if ($status == 'Y')
				{
					$status	= "Active";
				}
				else 
				{
					$status	= "Inactive";
				}
								
				$table	.=	"	<tr class=dtl style='background-color:ffffff; font: 13px 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial; color:#1d311b;' onmouseover=\"$(this).css('background-color', '#f9d9ce');\" onmouseout=\"$(this).css('background-color', '#ffffff');\">
									<td width=12% class=dtl align='center' style='border-color:#007ACC;' id='tr' onclick = 'showDtls(\"".$id_no."\");'><strong>{$id_no}</td>
									<td width=12% class=dtl align='center' style='border-color:#007ACC;' id='tr' onclick = 'showDtls(\"".$id_no."\");'><strong>{$username}</td>
									<td width=28% class=dtl align='center' style='border-color:#007ACC;' id='tr' onclick = 'showDtls(\"".$id_no."\");'>$name</td>
									<td width=12% class=dtl align='center' style='border-color:#007ACC;' id='tr' onclick = 'showDtls(\"".$id_no."\");'>{$dept}</strong></td>
									<td width=12% class=dtl align='center' style='border-color:#007ACC;' id='tr' onclick = 'showDtls(\"".$id_no."\");'>{$user_level}&nbsp;</td>
									<td width=12% class=dtl align='center' style='border-color:#007ACC;' id='tr' onclick = 'showDtls(\"".$id_no."\");'>{$status}&nbsp;</td>
									<td width=12% class=dtl align='center' style='border-color:#007ACC;'>";
									if($status == "Active" )
									{
										$table .=	"<img title=\"UPDATE {$name}\" src=\"../../images/images/action_icon/new/pencil.png\" style=\"height:23px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncEdit('$id_no')\" style=\"vertical-align: top;\">&nbsp;"; 
										$table .=	"<img title=\"CANCEL {$name}\" src=\"../../images/images/action_icon/new/stop.png\" style=\"height:23px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncCancel('$id_no')\" style=\"height:18px;vertical-align: top;\">&nbsp;"; 
									}
									if($status == "Inactive" )
									{
										$table .=	"<img title=\"UPDATE {$name}\" src=\"../../images/images/action_icon/new/pencil.png\" style=\"height:23px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncEdit('$id_no')\" style=\"vertical-align: top;\">&nbsp;"; 
									}
				$table .=			"</td>";
				$table .= "</tr>";
				$counter++;
			}
				$currpage	=	$pageno + 1;
				
				$table	.=	"<tr style='background-color:ffffff; font: 10px Verdana, Arial, Helvetica, sans-serif; color:#007ACC;'>
									<td style='border-color:#007ACC;padding:2px;' align='center' colspan='10'>
										<input type='button' value='<<'".($currpage == "1" ? "disabled" : "onclick='search(0)'")." class='navbutton'>
										<input type='button' value='<'" .($currpage == "1" ? "disabled" : "onclick='search(".($pageno-1).")'")." class='navbutton'>
											<a><b>$currpage/$totpagecnt</a>
										<input type='button' value='>'".($currpage == $totpagecnt ? "disabled" : "onclick='search(".($currpage).")'")." class='navbutton'>
										<input type='button' value='>>'" .($currpage == $totpagecnt ? "disabled" : "onclick='search(".($totpagecnt-1).")'")." class='navbutton'>
									</td>
							</tr>
							
							</table>";

				echo $table;	
		}
		else 
		{
			$table	=	"<table border=0 class=dtl cellpadding=0 cellspacing=0 width=80% align=center>";
			$table	.=		"<tr class=dtl style='background-color:ffffff; font: 13px Verdana, Arial, Helvetica, sans-serif; color:#1d311b;' onmouseover=\"$(this).css('background-color', '#ced9f9');\" onmouseout=\"$(this).css('background-color', '#ffffff');\">
									<td width=10% class=dtl align='center' style='border-color:#007ACC;'>NO RECORDS FOUND</td>";
			$table	.=		"</tr>";
			$table	.=	"</table>";
			echo $table;
		}
	}
	
	exit();
}
if ($_GET['action'] == "do_add_user")
{
	$id_number		= $_GET['txtIdNo'];	
	$username		= $_GET['txtUserName'];	
	$password		= $_GET['txtPass'];	
	$name			= $_GET['txtName'];	
	$dept			= $_GET['selDept'];	
	$user_level		= $_GET['selUserLevel'];	
	$status			= $_GET['selStatus'];	
	$access			= $_POST['access'];
	$arrVal  			= "";
	$datenow 			= date("Y-m-d");	
	
	
	$fields 	= "ID,USERNAME,PASSWORD,NAME,DEPT,USERLEVEL,STATUS,ADDEDDATE,ADDEDBY";
	$value 		= "'','{$username}','{$password}','{$name}','{$dept}','{$user_level}','{$status}','{$datenow}','{$_SESSION["username"]}'";	
	$add_user		= "INSERT INTO WMS_USER.USER ({$fields}) VALUES ({$value})";
	$rs_add_user	= $conn->Execute($add_user);
	if ($rs_add_user == false)
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();	
	}
	
	foreach ($access as $key=>$accessval) 
	{
		$fields_access 	= "ID_NUMBER,MODULEID,ACCESS";
		$value_access 	= "'{$id_number}','{$accessval}','Y'";	
		
		$add_access		= "INSERT INTO WMS_USER.ACCESS_MODULE ({$fields_access}) VALUES ({$value_access})";
		$rs_add_access	= $conn->Execute($add_access);
		if ($rs_add_access == false)
		{
			echo $conn->ErrorMsg()."::".__LINE__;
			exit();	
		}
		else 
		{
			echo "$('#txtIdNo').val('');";
			echo "$('#txtUserName').val('');";
			echo "$('#txtPass').val('');";
			echo "$('#txtName').val('');";
			echo "$('#selDept').val('');";
			echo "$('#selUserLevel').val('');";
			echo "$('#selStatus').val('');";
			echo "$('#txtPass').removeAttr('disabled');";
			echo "$('#chkNBSPL').attr('checked', false);";
			echo "$('#chkIA').attr('checked', false);";
			echo "$('#chkConfDel').attr('checked', false);";
			echo "$('#chkRepII').attr('checked', false);";
			echo "$('#chkRepTGAS').attr('checked', false);";
			echo "$('#chkRepCH').attr('checked', false);";
			echo "$('#chkRepVA').attr('checked', false);";
			echo "$('#chkRepMothers').attr('checked', false);";
			echo "$('#chkRepBTS').attr('checked', false);";
			echo "$('#chkRepSS').attr('checked', false);";
			echo "$('#chkRepSI').attr('checked', false);";
			echo "$('#chkTiaTR').attr('checked', false);";
			echo "$('#chkTiaRE').attr('checked', false);";
			echo "$('#chkRet_Tr_Sca').attr('checked', false);";
			echo "$('#chkRet_Trans_RetMTO').attr('checked', false);";
			echo "$('#chkRet_Rep_Moni').attr('checked', false);";
			echo "$('#chkRet_Rep_PerItem').attr('checked', false);";
			echo "$('#chkDelRet').attr('checked', false);";
			echo "$('#chkOrderStat').attr('checked', false);";
			echo "$('#chkINV_ADJTrans').attr('checked', false);";
			echo "$('#chkINV_ADJRep').attr('checked', false);";
			echo "$('#chkReceivingForm').attr('checked', false);";
			echo "$('#chkMposMoniPerSr').attr('checked', false);";
			echo "$('#chkMposTransmittal').attr('checked', false);";
			echo "$('#chkMposTransmittalReprint').attr('checked', false);";
			echo "$('#chkNonMtoItms').attr('checked', false);";
			
			echo "$('#chkCD_STFINV').attr('checked', false);";
			echo "$('#chkCD_REFDOC').attr('checked', false);";
			echo "$('#chkCD_Transmit').attr('checked', false);";
			echo "$('#chkCD_Summary').attr('checked', false);";
			echo "$('#chkUserConfig').attr('checked', false);";
		}
	}
	exit();
}
if($_GET['action'] == "do_edit")
{
	$id_no		= $_GET['id'];
	
	$search		= "SELECT * FROM USER WHERE ID='{$id_no}'";
	$rs_search	= $conn->Execute($search);
	if ($rs_search == false)
	{
		echo $conn->ErrorMsg().";:".__LINE__;
		exit();	
	}
	else 
	{
		foreach ($rs_search as $key => $value)
		{
			$id_no		= $value['ID'];
			$username	= $value['USERNAME'];
			$password	= $value['PASSWORD'];
			$name		= $value['NAME'];
			$dept		= $value['DEPT'];
			$user_level	= $value['USERLEVEL'];
			$status		= $value['STATUS'];
			echo "$('#txtIdNoEdit').val('$id_no');";
			echo "$('#txtUserNameEdit').val('$username');";
			echo "$('#txtPassEdit').val('$password');";
			echo "$('#txtNameEdit').val('$name');";
			echo "$('#selDeptEdit').val('$dept');";
			echo "$('#selUserLevelEdit').val('$user_level');";
			echo "$('#selStatusEdit').val('$status');";
			echo "$('#txtPassEdit').removeAttr('disabled');";
		}
		
		$search_access		= "SELECT ACCESS,MODULEID FROM ACCESS_MODULE WHERE ID_NUMBER='{$id_no}'";
		$rs_search_access	= $conn->Execute($search_access);
		if ($rs_search_access == false)
		{
			echo $conn->ErrorMsg().";:".__LINE__;
			exit();		
		}
		else 
		{
			foreach ($rs_search_access as $key => $valaccess)
			{
				$access		=	$valaccess['MODULEID'];
				
//				echo "alert('$access');";
				
				if ($access == "1")
				{
					echo "$('#chkNBSPL2').prop('checked',true);";
				}
				if ($access == "11")
				{
					echo "$('#chkIA2').prop('checked',true);";
				}
				if ($access == "12")
				{
					echo "$('#chkConfDel2').prop('checked',true);";
				}
				if ($access == "4")
				{
					echo "$('#chkRepII2').prop('checked',true);";
				}
				if ($access == "5")
				{
					echo "$('#chkRepTGAS2').prop('checked',true);";
				}
				if ($access == "2")
				{
					echo "$('#chkRepCH2').prop('checked',true);";
				}
				if ($access == "3")
				{
					echo "$('#chkRepVA2').prop('checked',true);";
				}
				if ($access == "21")
				{
					echo "$('#chkRepMothers2').prop('checked',true);";
				}
				if ($access == "27")
				{
					echo "$('#chkRepBTS2').prop('checked',true);";
				}
				if ($access == "6")
				{
					echo "$('#chkRepSS2').prop('checked',true);";
				}
				if ($access == "7")
				{
					echo "$('#chkRepSI2').prop('checked',true);";
				}
				if ($access == "10")
				{
					echo "$('#chkTiaTR2').prop('checked',true);";
				}
				if ($access == "9")
				{
					echo "$('#chkTiaRE2').prop('checked',true);";
				}
				if ($access == "13")
				{
					echo "$('#chkRet_Tr_Sca2').prop('checked',true);";
				}
				if ($access == "31")
				{
					echo "$('#chkRet_Trans_RetMTO2').prop('checked',true);";
				}
				if ($access == "14")
				{
					echo "$('#chkRet_Rep_Moni2').prop('checked',true);";
				}
				if ($access == "15")
				{
					echo "$('#chkRet_Rep_PerItem2').prop('checked',true);";
				}
				if ($access == "8")
				{
					echo "$('#chkDelRet2').prop('checked',true);";
				}
				if ($access == "22")
				{
					echo "$('#chkOrderStat2').prop('checked',true);";
				}
				if ($access == "16")
				{
					echo "$('#chkINV_ADJTrans2').prop('checked',true);";
				}
				if ($access == "17")
				{
					echo "$('#chkINV_ADJRep2').prop('checked',true);";
				}
				if ($access == "18")
				{
					echo "$('#chkReceivingForm2').prop('checked',true);";
				}
				if ($access == "19")
				{
					echo "$('#chkMposMoniPerSr2').prop('checked',true);";
				}
				if ($access == "30")
				{
					echo "$('#chkMposTransmittalReprint2').prop('checked',true);";
				}
				if ($access == "29")
				{
					echo "$('#chkMposTransmittal2').prop('checked',true);";
				}
				if ($access == "20")
				{
					echo "$('#chkNonMtoItms2').prop('checked',true);";
				}
				
				if ($access == "23")
				{
					echo "$('#chkCD_STFINV2').prop('checked',true);";
				}
				if ($access == "24")
				{
					echo "$('#chkCD_REFDOC2').prop('checked',true);";
				}
				if ($access == "25")
				{
					echo "$('#chkCD_Transmit2').prop('checked',true);";
				}
				if ($access == "26")
				{
					echo "$('#chkCD_Summary2').prop('checked',true);";
				}
				if ($access == "28")
				{
					echo "$('#chkUserConfig2').prop('checked',true);";
				}
				if ($access == "33")
				{
					echo "$('#chkRet_Tr_ExMto2').prop('checked',true);";
				}
				if ($access == "34")
				{
					echo "$('#chkRet_Tr_Inv2').prop('checked',true);";
				}
			}
		}
	}
	exit();
}
if ($_GET['action'] == "do_update_user")
{	
	$id_number				= $_POST['txtIdNoEdit'];	
	$username				= $_GET['txtUserName'];	
	$password				= $_GET['txtPass'];	
	$name					= $_GET['txtNameEdit'];	
	$dept					= $_GET['selDept'];	
	$user_level				= $_GET['selUserLevel'];	
	$status					= $_GET['selStatus'];	
	
	$chkNBSPL2				= $_GET['chkNBSPL2'];	
	$chkIA2					= $_GET['chkIA2'];	
	$chkConfDel2			= $_GET['chkConfDel2'];
	$chkRepII2				= $_GET['chkRepII2'];
	$chkRepTGAS2			= $_GET['chkRepTGAS2'];
	$chkRepCH2				= $_GET['chkRepCH2'];
	$chkRepVA2				= $_GET['chkRepVA2'];
	$chkRepMothers2			= $_GET['chkRepMothers2'];
	$chkRepBTS2				= $_GET['chkRepBTS2'];
	$chkRepSS2				= $_GET['chkRepSS2'];
	$chkRepSI2				= $_GET['chkRepSI2'];
	$chkTiaTR2				= $_GET['chkTiaTR2'];
	$chkTiaRE2				= $_GET['chkTiaRE2'];
	$chkRet_Tr_Sca2			= $_GET['chkRet_Tr_Sca2'];
	$chkRet_Trans_RetMTO2	= $_GET['chkRet_Trans_RetMTO2'];
	$chkRet_Tr_ExMto2		= $_GET['chkRet_Tr_ExMto2'];
	$chkRet_Tr_Inv2			= $_GET['chkRet_Tr_Inv2'];
	
	$chkRet_Rep_Moni2		= $_GET['chkRet_Rep_Moni2'];
	$chkRet_Rep_PerItem2	= $_GET['chkRet_Rep_PerItem2'];
	$chkDelRet2				= $_GET['chkDelRet2'];
	$chkOrderStat2			= $_GET['chkOrderStat2'];
	$chkINV_ADJTrans2		= $_GET['chkINV_ADJTrans2'];
	$chkINV_ADJRep2			= $_GET['chkINV_ADJRep2'];
	$chkReceivingForm2		= $_GET['chkReceivingForm2'];
	$chkMposMoniPerSr2		= $_GET['chkMposMoniPerSr2'];
	$chkMposTransmittal2	= $_GET['chkMposTransmittal2'];
	$chkMposTransmittalReprint2	= $_GET['chkMposTransmittalReprint2'];
	$chkNonMtoItms2			= $_GET['chkNonMtoItms2'];
	
	$chkCD_STFINV2			= $_GET['chkCD_STFINV2'];
	$chkCD_REFDOC2			= $_GET['chkCD_REFDOC2'];
	$chkCD_Transmit2		= $_GET['chkCD_Transmit2'];
	$chkCD_Summary2			= $_GET['chkCD_Summary2'];
	$chkUserConfig2			= $_GET['chkUserConfig2'];
	
	$datenow 		= date("Y-m-d");
	
	$value 			= "`NAME`='{$name}',`DEPT`='{$dept}',`USERLEVEL`='{$user_level}',`STATUS`='{$status}',`EDITBY`='{$_SESSION["username"]}',`EDITDATE`='{$datenow}'";
	$qryupdate		= "UPDATE WMS_USER.USER SET {$value} WHERE ID='{$id_number}'";
	$rs_qryupdate	= $conn->Execute($qryupdate);
	if($rs_qryupdate == false)
	{
		echo $conn->ErrorMsg().";:".__LINE__;
		exit();	 
	}
	else 
	{
		if ($chkNBSPL2 == 'YES')
		{
			$access[]	= "1";
		}
		if ($chkIA2 == 'YES')
		{
			$access[] = "11";
		}
		if ($chkConfDel2 == 'YES')
		{
			$access[] = "12";
		}
		if ($chkRepII2 == 'YES')
		{
			$access[] = "4";
		}
		if ($chkRepTGAS2 == 'YES')
		{
			$access[] = "5";
		}
		if ($chkRepCH2 == 'YES')
		{
			$access[] = "2";
		}
		if ($chkRepVA2 == 'YES')
		{
			$access[] = "3";
		}
		if ($chkRepMothers2 == 'YES')
		{
			$access[] = "27";
		}
		if ($chkRepBTS2 == 'YES')
		{
			$access[] = "21";
		}
		if ($chkRepSS2 == 'YES')
		{
			$access[] = "6";
		}
		if ($chkRepSI2 == 'YES')
		{
			$access[] = "7";
		}
		if ($chkTiaTR2 == 'YES')
		{
			$access[] = "10";
		}
		if ($chkRet_Tr_ExMto2 == 'YES')
		{
			$access[] = "33";
		}
		if ($chkRet_Tr_Inv2 == 'YES')
		{
			$access[] = "34";
		}
		if ($chkRet_Tr_Inv2 == 'YES')
		{
			$access[] = "9";
		}
		if ($chkRet_Tr_Sca2 == 'YES')
		{
			$access[] = "13";
		}
		if ($chkRet_Trans_RetMTO2 == 'YES')
		{
			$access[] = "31";
		}
		if ($chkRet_Rep_Moni2 == 'YES')
		{
			$access[] = "14";
		}
		if ($chkRet_Rep_PerItem2 == 'YES')
		{
			$access[] = "15";
		}
		if ($chkDelRet2 == 'YES')
		{
			$access[] = "8";
		}
		if ($chkOrderStat2 == 'YES')
		{
			$access[] = "22";
		}
		if ($chkINV_ADJTrans2 == 'YES')
		{
			$access[] = "16";
		}
		if ($chkINV_ADJRep2 == 'YES')
		{
			$access[] = "17";
		}
		if ($chkINV_ADJRep2 == 'YES')
		{
			$access[] = "18";
		}
		if ($chkMposMoniPerSr2 == 'YES')
		{
			$access[] = "19";
		}
		if ($chkMposTransmittalReprint2 == 'YES')
		{
			$access[] = "30";
		}
		if ($chkMposTransmittal2 == 'YES')
		{
			$access[] = "29";
		}
		if ($chkNonMtoItms2 == 'YES')
		{
			$access[] = "20";
		}
		
		
		if ($chkCD_STFINV2 == 'YES')
		{
			$access[] = "23";
		}
		if ($chkCD_REFDOC2 == 'YES')
		{
			$access[] = "24";
		}
		if ($chkCD_Transmit2 == 'YES')
		{
			$access[] = "25";
		}
		if ($chkCD_Summary2 == 'YES')
		{
			$access[] = "26";
		}
		if ($chkUserConfig2 == 'YES')
		{
			$access[] = "28";
		}
		
		
		$delete		=	"DELETE FROM ACCESS_MODULE WHERE ID_NUMBER = '{$id_number}'";
		$rs_delete	=	$conn->Execute($delete);
		if ($rs_delete == false)
		{
			echo $conn->ErrorMsg()."::".__LINE__;
			exit();	
		}
		else 
		{
			foreach ($access as $key => $accessval)
			{
				
				$fields_access 	= "ID_NUMBER,MODULEID,ACCESS";
				$value_access 	= "'{$id_number}','{$accessval}','Y'";	
				
				$add_access		= "INSERT INTO WMS_USER.ACCESS_MODULE ({$fields_access}) VALUES ({$value_access})";
				$rs_add_access	= $conn->Execute($add_access);
				if ($rs_add_access == false)
				{
					echo $conn->ErrorMsg()."::".__LINE__;
					exit();	
				}
				else 
				{
					
				}
			}
		}
	}
	exit();
}
if ($_GET['action'] == "do_cancel")
{
	$id		= $_GET['id'];
	
	$datenow		= date("Y-m-d");
	$timenow		= date("H:i:s");
	
	$value 			= "`STATUS`='Inactive',`EDITBY`='{$_SESSION["username"]}',`EDITDATE`='{$datenow}'";
	$qryupdate		= "UPDATE WMS_USER.USER SET {$value} WHERE ID='{$id}'";
	$rs_qryupdate	= $conn->Execute($qryupdate);
	if ($rs_qryupdate == false)
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();	
	}
	else 
	{
		echo "$('#dialog_ok').dialog('open');";
		echo "$('#dialog_ok').html('Succesfully Cancelled!');";
	}
	exit();
}
function liqui_trxno($val_date)
{
	$conn	=	ADONewConnection('mysqlt');
	$dbconn	=	$conn->Connect('192.168.255.10','root','','WMS_USER');
	if ($dbconn == false) 
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}
	$today 			= date("Ymd");
	$insert_date	=	"INSERT INTO WMS_USER.USERCONFIG_USER(DATE)VALUES(sysdate())";
	$rsinsert_date	=	$conn->Execute($insert_date);

	if ($rsinsert_date==false)
	{
		echo $conn->ErrorMsg()."::".__LINE__;exit();
	}
	$transeq_curr = $conn->Insert_ID();
	$transeq_prev = $transeq_curr-1;

	$sel_date	=	"SELECT * FROM WMS_USER.USERCONFIG_USER WHERE TRANSNO = '{$transeq_prev}'  ";
	$rssel_date	=	$conn->Execute($sel_date);
	if ($rssel_date==false)
	{
		echo $conn->ErrorMsg()."::".__LINE__;exit();
	}
	$date_db	=	substr($rssel_date->fields['DATE'],0,10);
	//echo $date_db."--".$val_date;
	if ($date_db==$val_date)
	{
		$curr_count		=	$rssel_date->fields['COUNT']+1;
		$update_date	=	"UPDATE WMS_USER.USERCONFIG_USER SET COUNT = '{$curr_count}' WHERE TRANSNO = '{$transeq_curr}' ";
		$rsupdate_date	=	$conn->Execute($update_date);
		if ($rsupdate_date==false)
		{
			echo $conn->ErrorMsg()."::".__LINE__;exit();
		}
	}
	else
	{
		$curr_count		=	1;
		$update_date	=	"UPDATE WMS_USER.USERCONFIG_USER SET COUNT = '1' WHERE TRANSNO = '{$transeq_curr}' ";
		$rsupdate_date	=	$conn->Execute($update_date);
		if ($rsupdate_date==false)
		{
			echo $conn->ErrorMsg()."::".__LINE__;exit();
		}
	}

	$retval			=	str_pad($curr_count,2,"0",STR_PAD_LEFT);
	return $retval;
}
##########################################.
############################################
include('../userconfig/userconfig.htm');####
############################################
##########################################
?>