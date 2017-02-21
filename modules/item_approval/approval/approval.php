<?php
session_start();
include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");
$action	=	$_GET['action'];
if (empty($_SESSION['username'])) 
{
	echo "<script>
				MessageType.sessexpMsg('wms');
		  </script>";
	$action = "";
	exit();
}
$user	=	$_SESSION['username'];
$today	=	date("Y-m-d");
$TIME	=	date("H:i:s A");

if($action == "SEARCHPO")
{
	$txtitemno		=	$_POST["txtitemno"];
	$txtPONO		=	$_POST["txtPONO"];
	$txtvendorno	=	$_POST["txtvendorno"];
	$txttrxno		=	$_POST["txttrxno"];
	$selstatus		=	$_POST["selstatus"];
	$seldtype		=	$_POST["seldtype"];
	$txtdfrom		=	$_POST["txtdfrom"];
	$txtdto			=	$_POST["txtdto"];
	$USEPREVQRY		=	$_GET["USEPREVQRY"];
	
	if($txtitemno != "")
	{
		$txtitemno_L	=	"LEFT JOIN WMS_NEW.ITEM_APPROVAL_DTL AS D ON D.TRANSNO = H.TRANSNO";
		$txtitemno_W	=	" AND D.`SKUNO` = '$txtitemno'";
	}
	if($txtPONO != "")
	{
		$txtPONO_Q	=	" AND H.`POATP` = '$txtPONO'";
	}
	if($txtvendorno != "")
	{
		$txtvendorno_Q	=	" AND H.VENDORCODE = '$txtvendorno'";
	}
	if($txttrxno != "")
	{
		$txttrxno_Q	=	" AND H.TRANSNO = '$txttrxno'";
	}
	if($selstatus != "")
	{
		$selstatus_Q	=	" AND H.STATUS = '$selstatus'";
	}
	if($seldtype != "")
	{
		$DATE_Q	=	" AND $seldtype BETWEEN '$txtdfrom' AND '$txtdto'";
	}
	if($USEPREVQRY == "YES")
	{
		$GETPO	=	$_SESSION["PREVQRY"];
	}
	else 
	{
		$GETPO	=	"SELECT H.* FROM WMS_NEW.ITEM_APPROVAL_HDR AS H 
					 $txtitemno_L
					 WHERE 1 $txtitemno_W $txtPONO_Q $txtvendorno_Q $txttrxno_Q $selstatus_Q $DATE_Q";
	}
	$_SESSION["PREVQRY"]	=	$GETPO;
	$RSGETPO	=	$conn_255_10->Execute($GETPO);
	if($RSGETPO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETPO,$_SESSION['username'],"ITEM APPROVAL","SEARCHPO");
		$DATASOURCE->displayError();
	}
	else 
	{
		if($RSGETPO->RecordCount()>0)
		{
			$table	=	"<form id='frmtrx'>
						 <table border='1' class='tblresult tablesorter'>
							<thead>
								<tr class='trheader' bgcolor='Teal'>
							 		<th >No.</th>
							 		<th >TRX No.</th>
							 		<th >PO No.</th>
							 		<th >Vendor</th>
							 		<th >Destination</th>
							 		<th >Gatepass No.</th>
							 		<th >Status</th>
							 		<th >Actions</th>
							 	</tr>
							</thead>
							<tbody>";
			$cnt = 1;
			$forrcvng_found	=	false;
			while (!$RSGETPO->EOF) {
				
				$TRANSNO 		= $RSGETPO->fields["TRANSNO"]; 
				$POATP 			= $RSGETPO->fields["POATP"]; 
				$VENDORCODE 	= $RSGETPO->fields["VENDORCODE"]; 
				$SUPPNAME		= $DATASOURCE->selval($conn_255_10,"FDCRMSlive","suppliers","SupplierName","SupplierCode = '$VENDORCODE'");
				$DESTINATION 	= $RSGETPO->fields["DESTINATION"]; 
				$NAME			= $DATASOURCE->selval($conn_255_10,"WMS_USERS","USERS","NAME","USERNAME = '$DESTINATION'");
				$GATEPASSNO 	= $RSGETPO->fields["GATEPASSNO"]; 
				$STATUS 		= $RSGETPO->fields["STATUS"]; 
				if($STATUS == "POSTED")
				{
					$STATUS			=	"FOR RECEIVING";
					$rcvchckbox		=	"<input type='checkbox' name='chktrx[]' id='chktrx$cnt' class='chktrxs tooltips' title='Receive' value='$TRANSNO'>";
					$forrcvng_found	=	true;
				}
				else 
				{
					$rcvchckbox	=	"";
				}
				if($STATUS == "RECEIVED" OR $STATUS == "INPROCESS")
				{
					$btnapprove		=	"<img src='/wms/images/images/action_icon/new/check.png' class='smallbtns tooltips approvebtn' title='Approve Items' data-trxno='$TRANSNO'>";
				}
				else 
				{
					$btnapprove	=	"";
				}
				$checkappdisscomplete	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","ITEM_APPROVAL_DTL","APPROVALSTATUS","TRANSNO = '$TRANSNO' AND APPROVALSTATUS = ''");
				if($checkappdisscomplete != "")
				{
					$btnpost		=	"$checkappdisscomplete<img src='/wms/images/images/action_icon/new/mail.png' class='smallbtns tooltips postbtn' title='Post Trx: $TRANSNO' data-trxno='$TRANSNO'>";
				}
				else 
				{
					$btnpost		=	"";
				}
				$table	.=	"<tr class='trbody trtrxdtls' id='trtrxdtls$cnt'>
								<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' data-status='$STATUS' title='Click to view detials' data-count='$cnt'>$cnt</td>
						 		<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' data-status='$STATUS' title='Click to view detials' data-count='$cnt'>$TRANSNO</td>
						 		<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' data-status='$STATUS' title='Click to view detials' data-count='$cnt'>$POATP</td>
						 		<td class='tooltips tdtrxdtls'data-trxno='$TRANSNO' data-status='$STATUS' title='Click to view detials' data-count='$cnt'>$VENDORCODE - $SUPPNAME</td>
						 		<td class='tooltips tdtrxdtls'data-trxno='$TRANSNO' data-status='$STATUS' title='Click to view detials' data-count='$cnt'>$NAME</td>
						 		<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' data-status='$STATUS' title='Click to view detials' data-count='$cnt'>$GATEPASSNO</td>
						 		<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' data-status='$STATUS' title='Click to view detials' data-count='$cnt'>$STATUS</td>
						 		<td class='cntrd'>$rcvchckbox $btnapprove $btnpost</td>
						   	</tr>";
				$cnt++;
				$RSGETPO->MoveNext();
			}
			if($forrcvng_found)
			{
				$table	.=	"<tr class='trbody trtrxdtls' id='trtrxdtls$cnt'>
						 		<td colspan='7'></td>
						 		<td class='cntrd'><button type='button' id='btnreceivetrx' class='btncheck'>Receive</button></td>
						   	</tr>";
			}
			else 
			{
				
			}
			$table	.=	"</tbody>
					</table>
					</form>";
			echo $table;
		}
		else 
		{
			echo getTBLprev();
		}
	}
	exit();
}
if($action == "RECEIVETRX")
{
	if(!empty($_POST['chktrx']))
	{
		foreach($_POST['chktrx'] as $TRANSNUM)
		{
			$TRXlist	.=	",'$TRANSNUM'";
		}
		$TRXlist		=	substr($TRXlist, 1);
		$RECEIVETRX		=	"UPDATE WMS_NEW.ITEM_APPROVAL_HDR SET STATUS = 'RECEIVED', RECEIVEDBY = '{$_SESSION['username']}',RECEIVEDDATE='$today',RECEIVEDTIME = '$TIME'
						 	 WHERE TRANSNO IN ($TRXlist)";
		$RSRECEIVETRX	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$RECEIVETRX,$_SESSION["username"],"ITEM APPROVAL","RECEIVETRX");
		if($RSRECEIVETRX)
		{
			echo "<script>
					MessageType.infoMsg('Selected transaction/s has/have been successfully received.');
					$('.btnsearch').trigger('click');
				  </script>";
		}
	}
	exit();
}
if($action == "GETDTLS")
{
	$TRANSNO	=	$_GET["TRANSNO"];
	$GETTRXDTLS	=	"SELECT * FROM WMS_NEW.ITEM_APPROVAL_DTL WHERE TRANSNO = '$TRANSNO'";
	$RSGETTRXDTLS	=	$conn_255_10->Execute($GETTRXDTLS);
	if($RSGETTRXDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETTRXDTLS,$_SESSION['username'],"ITEM APPROVAL","GETDTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		$table	=	"<table class='tblresul-tbltdtls tablesorter'>
						<thead>
							<tr class='tbl-scanning-summ-hdr'>
								<th>No.</th>
								<th class='tdaccept'><div class='some-handle'></div>SKU No.</th>
								<th class='tdaccept'><div class='some-handle'></div>Description</th>
								<th class='tdaccept'><div class='some-handle'></div>Approval Status</th>
								<th class=''>Quantity</th>
							</tr>
						</thead>
						<tbody>";
		$cnt	=	1;
		$totqty	=	0;
		while (!$RSGETTRXDTLS->EOF) {
			$APPROVALSTATUS = $RSGETTRXDTLS->fields["APPROVALSTATUS"]; 
			$SKUNO 			= $RSGETTRXDTLS->fields["SKUNO"]; 
			$DESCRIPTION 	= $RSGETTRXDTLS->fields["DESCRIPTION"]; 
			$QTY			= $RSGETTRXDTLS->fields["QTY"]; 
			$table .=	"<tr class='tblresul-tbltdtls-dtls'>
							<td align='center'>$cnt</td>
							<td align='center'>$SKUNO</td>
							<td align='left'>$DESCRIPTION</td>
							<td align='center'>$APPROVALSTATUS</td>
							<td align='center'>$QTY</td>
					   </tr>";
			$cnt++;
			$totqty	+=	$QTY;
			$RSGETTRXDTLS->MoveNext();
		}
		$table .=	"</tbody> 
					 <tfoot>
						<tr class='tblresul-tbltdtls-dtls bld'>
							<td colspan='4' align='center'>TOTAL</td>
							<td align='center'>".number_format($totqty)."</td>
						</tr>
					  </tfoot>	
					</table>";
		echo $table;
	}
	exit();
}
if($action == "GETDTLSTOAPPR")
{
	$TRANSNO	=	$_GET["TRANSNO"];
	
	$GETTRXDTLS	=	"SELECT * FROM WMS_NEW.ITEM_APPROVAL_DTL WHERE TRANSNO = '$TRANSNO'";
	$RSGETTRXDTLS	=	$conn_255_10->Execute($GETTRXDTLS);
	if($RSGETTRXDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETTRXDTLS,$_SESSION['username'],"ITEM APPROVAL","GETDTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		$table	.=	"<table width='100%' class='tblresul-tbltdtls' align='center'><tr class='trheader'><td id='tdtrxnum'>$TRANSNO</td></tr></table>
					 <form id='frmappdis'>
					 <table width='100%' class='tblresul-tbltdtls' align='center'>
						<tr class='tblresul-tbltdtls-hdr'>
							<td>No.</td>
							<td>SKU No.</td>
							<td>Description</td>
							<td>Quantity</td>
							<td>Approve</td>
							<td>Disapprove</td>
						</tr>";
		$cnt	=	1;
		$totqty	=	0;
		while (!$RSGETTRXDTLS->EOF) {
			$APPROVALSTATUS = $RSGETTRXDTLS->fields["APPROVALSTATUS"]; 
			$SKUNO 			= $RSGETTRXDTLS->fields["SKUNO"]; 
			$DESCRIPTION 	= $RSGETTRXDTLS->fields["DESCRIPTION"]; 
			$QTY			= $RSGETTRXDTLS->fields["QTY"]; 
			$APPCHECK	=	"";
			$DISCHECK	=	"";
			if($APPROVALSTATUS == "APPROVED")
			{
				$APPCHECK	=	"checked";
				$DISCHECK	=	"";
			}
			if($APPROVALSTATUS == "DISAPPROVED")
			{
				$APPCHECK	=	"";
				$DISCHECK	=	"checked";
			}
			$table	.=	"<tr class='tblresul-tbltdtls-dtls' id='tritems$cnt'>
							<td class='cntrd'>$cnt</td>
							<td class='cntrd'>$SKUNO</td>
							<td>$DESCRIPTION</td>
							<td class='cntrd' id='tdO_qty$cnt'>$QTY</td>
							<td class='cntrd'>
								<input type='checkbox' name = 'chkapp$cnt' id = 'chkapp$cnt' class='chkappitems' data-cnt='$cnt' value='$SKUNO' $APPCHECK>
							</td>
							<td class='cntrd'>
								<input type='checkbox' name = 'chkdis$cnt' id = 'chkdis$cnt' class='chkdisitems' data-cnt='$cnt' value='$SKUNO' $DISCHECK>
							</td>
						</tr>";
			$cnt++;
			$totqty	+=	$QTY;
			$RSGETTRXDTLS->MoveNext();
		}			
		$table	.=	"</table>
					</form>";
		echo $table;
	}
	exit();
}
if($action == "SAVEAPPDIS")
{
	$TRXNUM	=	$_GET["TRXNUM"];
	$conn_255_10->StartTrans();
	$UPDATEDTLS	=	"UPDATE WMS_NEW.ITEM_APPROVAL_DTL
					 SET `APPROVALSTATUS`= '', `APPROVEDBY`='', `APPROVEDDATE`='', `APPROVEDTIME`='',`DISAPPROVEDBY`='', `DISAPPROVEDDATE`='', `DISAPPROVEDTIME`=''
					 WHERE `TRANSNO` = '$TRXNUM'";
	$RSUPDATEDTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEDTLS,$user,"ITEM APPROVAL","SAVEAPPDIS");
	for ($cnt=1; $cnt <= 10; $cnt++)
	{
		$chkapp		=	$_POST["chkapp$cnt"];
		$chkdis		=	$_POST["chkdis$cnt"];
		if($chkapp != "")
		{
			$UPDATEDTLS	=	"UPDATE WMS_NEW.ITEM_APPROVAL_DTL
							 SET `APPROVALSTATUS`= 'APPROVED', `APPROVEDBY`='$user', `APPROVEDDATE`='$today', `APPROVEDTIME`='$TIME'
							 WHERE `TRANSNO` = '$TRXNUM' AND `SKUNO` = '$chkapp'";
			$RSUPDATEDTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEDTLS,$user,"ITEM APPROVAL","SAVEAPPDIS");
		}
		if($chkdis != "")
		{
			$UPDATEDTLS	=	"UPDATE WMS_NEW.ITEM_APPROVAL_DTL
							 SET `APPROVALSTATUS`= 'DISAPPROVED', `DISAPPROVEDBY`='$user', `DISAPPROVEDDATE`='$today', `DISAPPROVEDTIME`='$TIME'
							 WHERE `TRANSNO` = '$TRXNUM' AND `SKUNO` = '$chkdis'";
			$RSUPDATEDTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEDTLS,$user,"ITEM APPROVAL","SAVEAPPDIS");
		}
	}
	$UPDATEHDR	=	"UPDATE WMS_NEW.ITEM_APPROVAL_HDR SET `STATUS`= 'INPROCESS'
					 WHERE `TRANSNO` = '$TRXNUM'";
	$RSUPDATEHDR	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEHDR,$user,"ITEM APPROVAL","SAVEAPPDIS");
	$conn_255_10->CompleteTrans();
	if($RSUPDATEHDR)
	{
		echo "<script>
				MessageType.successMsg('Transaction has been successfully saved.','');
				$('#divtrxdtls').dialog('close');
				$('.btnsearch').trigger('click');
			</script>";
	}
	exit();
}
if($action == "CLOSETRX")
{
	$TRANSNO	=	$_GET["TRANSNO"];
	$UPDATEHDR	=	"UPDATE WMS_NEW.ITEM_APPROVAL_HDR SET `STATUS`= 'CLOSED'
					 WHERE `TRANSNO` = '$TRANSNO'";
	$RSUPDATEHDR	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEHDR,$user,"ITEM APPROVAL","CLOSETRX");
	$conn_255_10->CompleteTrans();
	if($RSUPDATEHDR)
	{
		echo "<script>
				MessageType.successMsg('Transaction has been successfully posted.','');
				$('.btnsearch').trigger('click');
			</script>";
	}
	exit();
}
if($action == "Q_SEARCHITEM")
{
	$txtitemno 		= $_GET["ITEMNO"];
	$txtitemdesc 	= $_GET["ITEMDESC"];
	
	$sel	 =	"SELECT `SKUNO`,`DESCRIPTION` FROM  WMS_NEW.ITEM_APPROVAL_DTL WHERE 1 $BRAND_Q $CLASS_Q";
	if (!empty($txtitemno)) 
	{
	$sel	.=	" AND SKUNO like '%{$txtitemno}%' ";
	}
	if (!empty($txtitemdesc)) 
	{
	$sel	.=	" AND DESCRIPTION like '%{$txtitemdesc}%' ";
	}
	$sel	.=	" limit 20 ";
//		echo "$sel"; exit();
	$rssel	=	$conn_255_10->Execute($sel);
	if ($rssel == false) 
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"ITEM APPROVAL","Q_SEARCHITEM");
		$DATASOURCE->displayError();
	}
	$cnt	=	$rssel->RecordCount();
	if ($cnt > 0) 
	{
		echo "<select id='selitem' class = 'C_dropdown divsel' style='width:532px;height:auto;' onkeypress='smartselitem(event);' multiple>";
		while (!$rssel->EOF) 
		{
			$q_ITEMNO	=	$rssel->fields['SKUNO'];
			$Q_ITEM_DESC=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['DESCRIPTION']);
			$cValue		=	$q_ITEMNO."|".$Q_ITEM_DESC;
			echo "<option value=\"$cValue\" onclick=\"smartselitem('click');\">$q_ITEMNO-$Q_ITEM_DESC</option>";
			$rssel->MoveNext();
		}
		echo "</select>";
	}
	else
	{
		echo "";
	}
	exit();
}
if ($action=='Q_SEARCHVEND') 
{
	$vendno		=	addslashes($_GET['VENDNO']);
	$vendname	=	addslashes($_GET['VENDNAME']);
	
	$sel	 =	"SELECT SupplierCode,SupplierName FROM  FDCRMSlive.suppliers WHERE 1";
	if (!empty($vendno)) 
	{
	$sel	.=	" AND SupplierCode like '%{$vendno}%' ";
	}
	if (!empty($vendname)) 
	{
	$sel	.=	" AND SupplierName like '%{$vendname}%' ";
	}
	$sel	.=	" limit 20 ";
	$rssel	=	$conn_255_10->Execute($sel);
	if ($rssel == false) 
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"ITEM APPROVAL","Q_SEARCHVEND");
		$DATASOURCE->displayError();
	}
	$cnt	=	$rssel->RecordCount();
	if ($cnt > 0) 
	{
		echo "<select id='selvend' class = 'C_dropdown divsel' style='width:532px;height:auto;' onkeypress='smartsel(event);' multiple>";
		while (!$rssel->EOF) 
		{
			$q_vendno	=	$rssel->fields['SupplierCode'];
			$Q_vendname	=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['SupplierName']);
			$cValue		=	$q_vendno."|".$Q_vendname;
			echo "<option value=\"$cValue\" onclick=\"smartsel('click');\">$q_vendno-$Q_vendname</option>";
			$rssel->MoveNext();
		}
		echo "</select>";
	}
	else
	{
		echo "";
	}
	exit();
}
function getTBLprev()
{
	return "<table border='1' class='tblresult'>
				<tr class='trheader'>
			 		<td >No.</td>
			 		<td >TRX No.</td>
			 		<td >PO No.</td>
			 		<td >Vendor</td>
			 		<td >Destination</td>
			 		<td >Gatepass No.</td>
			 		<td >Status</td>
			 		<td >Actions</td>
			 	</tr>
		 		<tr class='trbody centered fnt-red'>
			 		<td colspan='8'>Nothing to display.</td>
			 	</tr>
			 </table>";
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/item_approval/approval/approval.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/item_approval/approval/approvalUI.php");
?>