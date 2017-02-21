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

if($action == "GETPODTLS")
{
	$PONO	=	$_GET["PONO"];
	$GETPODTLS		=	"SELECT * FROM WMS_NEW.PODETAIL WHERE POATP = '$PONO'";
	$RSGETPODTLS	=	$conn_255_10->Execute($GETPODTLS);
	if($RSGETPODTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETPODTLS,$_SESSION['username'],"ITEM APPROVAL","GETPODTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		if($RSGETPODTLS->RecordCount() == 0)
		{
			echo "<script>MessageType.infoMsg('P.O. not found.');</script>";
		}
		else 
		{
			$suppcode	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","POHEADER","SUPPCODE","POATP = '$PONO'");
			$suppname	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","suppliers","SupplierName","SupplierCode = '$suppcode'");
			$table	.=	"<table class='tblresult'>
							<tr class='customhdr'>
								<td>Supplier Code : $suppcode</td>
								<td>Supplier Description : $suppname</td>
							</tr>
						</table>";
			$table	.=	"<table width='100%' class='tblresul-tbltdtls' align='center'>
							<tr class='tblresul-tbltdtls-hdr'>
								<td>No.</td>
								<td>SKU No.</td>
								<td>Description</td>
								<td>Original Qty</td>
								<td>Qty</td>
								<td>Action</td>
							</tr>";
			$cnt	=	1;
			$recordcnt	=	0;
			while (!$RSGETPODTLS->EOF) {
				$SKUNO		=	$RSGETPODTLS->fields["SKUNO"];
				$APPROVEDQTY=	$RSGETPODTLS->fields["APPROVEDQTY"];
				$skufound	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","ITEM_APPROVAL_DTL AS D LEFT JOIN WMS_NEW.ITEM_APPROVAL_HDR AS H ON H.TRANSNO = D.TRANSNO","SKUNO","H.POATP = '$PONO' AND SKUNO = '$SKUNO' AND H.STATUS != 'CANCELLED'");
				if($skufound == "")
				{	
					$DESCRIPTION	=	$RSGETPODTLS->fields["DESCRIPTION"];
					$table	.=	"<tr class='tblresul-tbltdtls-dtls' id='tritems$cnt'>
									<td class='cntrd'>$cnt</td>
									<td class='cntrd'>$SKUNO</td>
									<td>$DESCRIPTION</td>
									<td class='cntrd' id='tdO_qty$cnt'>$APPROVEDQTY</td>
									<td class='cntrd'>
										<input type='text' size='5' class='txtqtys cntrd numbersonly' name='txtqty$cnt' id='txtqty$cnt' data-cnt='$cnt'>
									</td>
									<td class='cntrd'>
										<input type='checkbox' name = 'chkitems$cnt' class='chkappitem' value='$SKUNO'>
									</td>
								</tr>";
					$cnt++;
					$recordcnt = 1;
				}
				$RSGETPODTLS->MoveNext();
			}
			if($recordcnt == 0)
			{
				$table	.=	"<tr class='tblresul-tbltdtls-dtls'>
								<td class='cntrd' colspan='6'>No records found.</td>
							</tr>";
			}
			$table	.=	"</table>";
			echo $table;
		}
	}
	exit();
}
if($action == "SAVETRX")
{
	$PONO			=	$_POST['txtCpono'];
	$TRANSNO		=	newTRXno($conn_255_10);
	$txtgatepassno	=	$_GET['txtgatepassno'];
	$seluser		=	$_GET['seluser'];
	$suppcode		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","POHEADER","SUPPCODE","POATP = '$PONO'");
	$conn_255_10->StartTrans();
	$cnt			=	1;
	for ($cnt; $cnt <= 10; $cnt++)
	{
		$QTY		=	$_POST["txtqty$cnt"];
		$ITEMNO		=	$_POST["chkitems$cnt"];
		if($ITEMNO != "")
		{
			$itemdesc	=	addslashes($DATASOURCE->selval($conn_255_10,"WMS_NEW","PODETAIL","DESCRIPTION","SKUNO = '$ITEMNO' AND POATP='$PONO'"));
			$INSERTPODTLS	=	"INSERT INTO WMS_NEW.ITEM_APPROVAL_DTL
								(`TRANSNO`, `APPROVALSTATUS`, `SKUNO`, `DESCRIPTION`, `QTY`) VALUES
								('$TRANSNO','','$ITEMNO','$itemdesc','$QTY')";
			$RSINSERTPODTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTPODTLS,$user,"ITEM APPROVAL","SAVETRX");
		}
	}
	$INSERTPOHDR	=	"INSERT INTO WMS_NEW.ITEM_APPROVAL_HDR
						(`TRANSNO`, `POATP`, `VENDORCODE`, `DESTINATION`, `GATEPASSNO`, `STATUS`, `CREATEDBY`, `CREATEDDATE`, `CREATEDTIME`) VALUES
						('$TRANSNO','$PONO','$suppcode','$seluser','$txtgatepassno','SAVED','$user','$today','$TIME')";
	$RSINSERTPOHDR	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTPOHDR,$user,"ITEM APPROVAL","SAVETRX");
	$conn_255_10->CompleteTrans();
	if($RSINSERTPOHDR)
	{
		echo "<script>
				MessageType.successMsg('Transaction has been successfully saved.','');
				$('#divitemappcreate').dialog('close');
				$('#divgatepass').dialog('close');
				ItemApprovalFuncs.cancelPODTLS();
				ItemApprovalFuncs.cancelAP();
				ItemApprovalFuncs.GETTRX('YES');
			</script>";
	}
	exit();
}
if($action == "SEARCHPO")
{
	$txtitemno		=	$_POST["txtitemno"];
	$txtPONO		=	$_POST["txtPONO"];
	$txtvendorno	=	$_POST["txtvendorno"];
	$txttrxno		=	$_POST["txttrxno"];
	$selstatus		=	$_POST["selstatus"];
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
	if($USEPREVQRY == "YES")
	{
		$GETPO	=	$_SESSION["PREVQRY"];
	}
	else 
	{
		$GETPO	=	"SELECT H.* FROM WMS_NEW.ITEM_APPROVAL_HDR AS H 
					 $txtitemno_L
					 WHERE 1 $txtitemno_W $txtPONO_Q $txtvendorno_Q $txttrxno_Q $selstatus_Q";
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
			$table	=	"<table border='1' class='tblresult tablesorter'>
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
			while (!$RSGETPO->EOF) {
				
				$TRANSNO 		= $RSGETPO->fields["TRANSNO"]; 
				$POATP 			= $RSGETPO->fields["POATP"]; 
				$VENDORCODE 	= $RSGETPO->fields["VENDORCODE"]; 
				$SUPPNAME		= $DATASOURCE->selval($conn_255_10,"FDCRMSlive","suppliers","SupplierName","SupplierCode = '$VENDORCODE'");
				$DESTINATION 	= $RSGETPO->fields["DESTINATION"]; 
				$NAME			= $DATASOURCE->selval($conn_255_10,"WMS_USERS","USERS","NAME","USERNAME = '$DESTINATION'");
				$GATEPASSNO 	= $RSGETPO->fields["GATEPASSNO"]; 
				$STATUS 		= $RSGETPO->fields["STATUS"]; 
				if($STATUS == "SAVED")
				{
					$btncancel		=	"<img src='/wms/images/images/action_icon/new/stop.png' class='smallbtns tooltips cancelbtn' title='Cancel Trx: $TRANSNO' data-trxno='$TRANSNO'>";
					$btnpost		=	"<img src='/wms/images/images/action_icon/new/mail.png' class='smallbtns tooltips postbtn' title='Post Trx: $TRANSNO' data-trxno='$TRANSNO'>";
				}
				else 
				{
					$btncancel		=	"";
					$btnpost		=	"";
				}
				$table	.=	"<tr class='trbody trtrxdtls' id='trtrxdtls$cnt'>
								<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' title='Click to view detials' data-count='$cnt'>$cnt</td>
						 		<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' title='Click to view detials' data-count='$cnt'>$TRANSNO</td>
						 		<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' title='Click to view detials' data-count='$cnt'>$POATP</td>
						 		<td class='tooltips tdtrxdtls'data-trxno='$TRANSNO' title='Click to view detials' data-count='$cnt'>$VENDORCODE - $SUPPNAME</td>
						 		<td class='tooltips tdtrxdtls'data-trxno='$TRANSNO' title='Click to view detials' data-count='$cnt'>$NAME</td>
						 		<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' title='Click to view detials' data-count='$cnt'>$GATEPASSNO</td>
						 		<td class='cntrd tooltips tdtrxdtls'data-trxno='$TRANSNO' title='Click to view detials' data-count='$cnt'>$STATUS</td>
						 		<td class='cntrd'>$btncancel $btnpost</td>
						   	</tr>";
				$cnt++;
				$RSGETPO->MoveNext();
			}
			$table	.=	"</tbody>
					</table>";
			echo $table;
		}
		else 
		{
			echo getTBLprev();
		}
	}
	exit();
}
if($action == "CANCELTRX")
{
	$TRANSNO	=	$_GET["TRANSNO"];
	$CANCELTRX	=	"UPDATE WMS_NEW.ITEM_APPROVAL_HDR SET `STATUS` = 'CANCELLED', `CANCELLEDBY` = '$user', `CANCELLEDDATE` = NOW()
					 WHERE `TRANSNO` = '$TRANSNO'";
	$RSCANCELTRX	=	$conn_255_10->Execute($CANCELTRX);
	if($RSCANCELTRX == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$CANCELTRX,$_SESSION['username'],"ITEM APPROVAL","CANCELTRX");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<script>
				MessageType.successMsg('Transaction has been successfully cancelled.','');
				ItemApprovalFuncs.GETTRX('YES');
			</script>";
	}
	exit();
}
if($action == "POSTTRX")
{
	$TRANSNO	=	$_GET["TRANSNO"];
	$POSTTRX	=	"UPDATE WMS_NEW.ITEM_APPROVAL_HDR SET `STATUS` = 'POSTED', `POSTEDBY` = '$user', `POSTEDDATE` = NOW()
					 WHERE `TRANSNO` = '$TRANSNO'";
	$RSPOSTTRX	=	$conn_255_10->Execute($POSTTRX);
	if($RSPOSTTRX == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$POSTTRX,$_SESSION['username'],"ITEM APPROVAL","POSTTRX");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<script>
				MessageType.successMsg('Transaction has been successfully posted.','');
				ItemApprovalFuncs.GETTRX('YES');
			</script>";
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
function  newTRXno($dbconn)
{
	$forTRXno		=	"SELECT	TRANSNO,CREATEDDATE FROM  WMS_NEW.ITEM_APPROVAL_HDR order by LINE_NO";
	$rsforTRXno		=	$dbconn->Execute($forTRXno);
	if ($rsforTRXno == false) 
	{
		echo $errmsg	=	$conn->ErrorMsg()."::".__LINE__; 
		exit();
	}
	while (!$rsforTRXno->EOF) 
	{
		$date1		=	date('Y-m-d', strtotime($rsforTRXno->fields['CREATEDDATE']));	
		$lastTRXno 	= 	$rsforTRXno->fields['TRANSNO'];
		$rsforTRXno->MoveNext();
	}
	 $dgt		=	substr($lastTRXno, 13);	
	 $newdgt 	= 	$dgt + 1;
	 $lnt 		= 	strlen($newdgt);
	 $date2		=	date('Y-m-d');
	 if( $date1==$date2 )
	 {
		if($lnt == 1)
		{
			$newTRXno	=	"IAP-".date('Ymd').'-'."00".$newdgt;
		}
		if($lnt	==	2)
		{
			$newTRXno	=	"IAP-".date('Ymd').'-'."0".$newdgt;
		}
		if($lnt	==	3)
		{
			$newTRXno	=	"IAP-".date('Ymd').'-'.$newdgt;
		}
	 }
	 else 
	 {
	 	$newTRXno	=	"IAP-".date('Ymd').'-'."001";
	 }
	 
	return $newTRXno;
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
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/item_approval/creation/creation.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/item_approval/creation/creationUI.php");
?>