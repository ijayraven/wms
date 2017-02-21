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
			$table	=	"<table width='100%' class='tblresul-tbltdtls'>
							<tr class='tblresul-tbltdtls-hdr'>
								<td>No.</td>
								<td>SKU No.</td>
								<td>Description</td>
								<td>Approve</td>
								<td>Disapprove</td>
							</tr>";
			$cnt	=	1;
			while (!$RSGETPODTLS->EOF) {
				$SKUNO			=	$RSGETPODTLS->fields["SKUNO"];
				$DESCRIPTION	=	$RSGETPODTLS->fields["DESCRIPTION"];
				$table	.=	"<tr class='tblresul-tbltdtls-dtls'>
								<td class='cntrd'>$cnt</td>
								<td class='cntrd'>$SKUNO</td>
								<td>$DESCRIPTION</td>
								<td class='cntrd'>
									<input type='checkbox' name = 'chkappitemS[]' class='chkappitem' value='$SKUNO'>
								</td>
								<td class='cntrd'>
									<input type='checkbox' name = 'chkdisitemS[]' class='chkdisitem' value='$SKUNO'>
								</td>
							</tr>";
				$cnt++;
				$RSGETPODTLS->MoveNext();
			}
			$table	.=	"</table>";
			echo $table;
		}
	}
	exit();
}
if($action == "APPROVEPO")
{
	$PONO			=	$_POST['Cpono'];
	$TRANSNO		=	newTRXno($conn_255_10);
	$txtgatepassno	=	$_GET['txtgatepassno'];
	$conn_255_10->StartTrans();
	foreach ($_POST["chkappitemS"] as $ITEMNO)
	{
		$itemdesc	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","PODETAIL","DESCRIPTION","SKUNO = '$ITEMNO' AND POATP='$PONO'");
		$QTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","PODETAIL","APPROVEDQTY","SKUNO = '$ITEMNO' AND POATP='$PONO'");
		$INSERTPODTLS	=	"INSERT INTO WMS_NEW.ITEM_APPROVAL_DTL
							(`TRANSNO`, `APPROVALSTATUS`, `SKUNO`, `DESCRIPTION`, `QTY`,`APPROVEDBY`, `APPROVEDDATE`, `APPROVEDTIME`) VALUES
							('$TRANSNO','APPROVED','$ITEMNO','$itemdesc','$QTY','$user','$today','$TIME')";
		$RSINSERTPODTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTPODTLS,$user,"ITEM APPROVAL","APPROVEPO");
	}
	foreach ($_POST["chkdisitemS"] as $ITEMNO)
	{
		$itemdesc	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","PODETAIL","DESCRIPTION","SKUNO = '$ITEMNO' AND POATP='$PONO'");
		$QTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","PODETAIL","APPROVEDQTY","SKUNO = '$ITEMNO' AND POATP='$PONO'");
		$INSERTPODTLS	=	"INSERT INTO WMS_NEW.ITEM_APPROVAL_DTL
							(`TRANSNO`, `APPROVALSTATUS`, `SKUNO`, `DESCRIPTION`, `QTY`,`DISAPPROVEDBY`, `DISAPPROVEDDATE`, `DISAPPROVEDTIME`) VALUES
							('$TRANSNO','DISAPPROVED','$ITEMNO','$itemdesc','$QTY','$user','$today','$TIME')";
		$RSINSERTPODTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTPODTLS,$user,"ITEM APPROVAL","APPROVEPO");
	}
	$INSERTPOHDR	=	"INSERT INTO WMS_NEW.ITEM_APPROVAL_HDR
						(`TRANSNO`, `POATP`, `DESTINATION`, `GATEPASSNO`, `STATUS`, `CREATEDBY`, `CREATEDDATE`, `CREATEDTIME`) VALUES
						('$TRANSNO','$PONO','PP','','','','','','')";
	$conn_255_10->CompleteTrans();
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
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/item_approval/item_approval.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/item_approval/item_approvalUI.php");
?>