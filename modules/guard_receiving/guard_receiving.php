<?php
session_start();
//include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");
if (empty($_SESSION['username'])) 
{
	echo "<script>
				MessageType.sessexpMsg('wms');
		  </script>";
	exit();
}
$action	=	$_GET['action'];
$user	=	$_SESSION['username'];
$today	=	date("Y-m-d H:i:s A");
$day	=	date("Y-m-d");
$time	=	date("H:i:s A");

if($action == "GETPODTLS")
{
	$POATP	=	$_GET["PONUM"];
	
	$GETPO	=	"SELECT H.`SupplierCode`, D.`ItemNo`, D.`Description`, D.`ApprovedQty` FROM FDCRMSlive.poheader AS H
				 LEFT JOIN FDCRMSlive.podetails AS D ON D.`PONumber` = H.`PONumber`
				 WHERE H.`PONumber` = '$POATP' AND H.`POStatus` = 'Posted'";
	$RSGETPO=	$conn_255_10->Execute($GETPO);
	if($RSGETPO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETPO,$_SESSION['username'],"GUARD's RECEIVING","GETPODTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		if($RSGETPO->RecordCount() > 0)
		{
//			$POStatus	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","poheader","POStatus","`PONumber` = '$POATP'");
			$POStatus	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","POHEADER","POSTATUS","`POATP` = '$POATP'");
			if($POStatus != "")
			{
				echo "<script>MessageType.infoMsg('Status is already $POStatus.');</script>";
				exit();
			}
			$SUPPCODE	=	$RSGETPO->fields["SupplierCode"];
			$SUPPDESC	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","suppliers","SupplierName","SupplierCode = '$SUPPCODE'");
			$table	=	"<table class='tblresult'>
							<tr class='customhdr'>
								<td>PO No. : $POATP</td>
								<td>Supplier Code : $SUPPCODE</td>
								<td>Supplier Description : $SUPPDESC</td>
							</tr>
							<tr>
								<td class='cntrd'colspan='3'>
									<button type='button' class='btnactivate btncheck'>ACTIVATE</button>
								</td>
							</tr>
							<tr class='tblresul-tbltdtls-hdr'>
								<td>Item No.</td>
								<td>Item Description</td>
								<td>Quantity</td>
							</tr>";
			while (!$RSGETPO->EOF)
			{
				$SUPPLIERCODE	=	$RSGETPO->fields["SupplierCode"];
				$ITEMNO			=	$RSGETPO->fields["ItemNo"];
				$ITEMDESC		=	$RSGETPO->fields["Description"];
				$APPROVEDQTY	=	$RSGETPO->fields["ApprovedQty"];
				$totqty			+=	$APPROVEDQTY;
				$table			.=	"<tr class='tblresul-tbltdtls-dtls'>
										<td class='cntrd pdd5px'>$ITEMNO</td>
										<td class='pdd5px'>$ITEMDESC</td>
										<td class='cntrd pdd5px'>$APPROVEDQTY</td>
									</tr>";	
				$RSGETPO->MoveNext();
			}
			$table	.=	"<tr class='bld tblresul-tbltdtls-dtls'>
							<td colspan='2'  class='cntrd pdd5px'>TOTAL</td>
							<td  class=' cntrd pdd5px'>$totqty</td>
						</tr>
				</table>
				<button type='button' class='btnactivate btncheck'>ACTIVATE</button>";
			echo $table;
		}
		else 
		{
//			$POStatus	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","poheader","POStatus","`PONumber` = '$POATP'");
			$POStatus	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","POHEADER","POSTATUS","`POATP` = '$POATP'");
			if($POStatus == "")
			{
				$msg	=	"<script>MessageType.infoMsg('No records found.');</script>";
			}
			else 
			{
				$msg	=	"<script>MessageType.infoMsg('Status is already $POStatus.');</script>";
			}
			echo $msg;
		}
	}
	exit();
}
if($action == "ACTIVATEPO")
{
	$POATP	=	$_GET["PONUM"];
	$conn_255_10->StartTrans();
	$GETPO		=	"SELECT * FROM FDCRMSlive.poheader WHERE `PONumber` = '$POATP'";
	$RSGETPO	=	$conn_255_10->Execute($GETPO);
	if($RSGETPO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETPO,$_SESSION['username'],"GUARD's RECEIVING","ACTIVATEPO");
		$DATASOURCE->displayError();
	}
	else 
	{
		$PONumber 		= $RSGETPO->fields["PONumber"]; 
		$SupplierCode 	= $RSGETPO->fields["SupplierCode"]; 
		$PODate 		= $RSGETPO->fields["PODate"]; 
		$ReqDelDate 	= $RSGETPO->fields["ReqDelDate"]; 
		$PODeadLine 	= $RSGETPO->fields["PODeadLine"]; 
		$CurrencyCode 	= $RSGETPO->fields["CurrencyCode"]; 
		$DeliverTo 		= $RSGETPO->fields["DeliverTo"]; 
		$WarehouseCode 	= $RSGETPO->fields["WarehouseCode"]; 
		$POAmount 		= $RSGETPO->fields["POAmount"]; 
		$ReceivedAmount = $RSGETPO->fields["ReceivedAmount"]; 
		$POType 		= $RSGETPO->fields["POType"]; 
		$Instructions 	= $RSGETPO->fields["Instructions"]; 
		
		$INSERTHDR	=	"INSERT INTO WMS_NEW.POHEADER(`POATP`, `SUPPCODE`, `POAMOUNT`, `RECEIVEDAMOUNT`, `CURRCODE`, `POSTATUS`, `PODATE`, `REQDELDATE`, `PODEADLINE`, `DELIVERTO`,
						 `WAREHOUSECODE`, `POTYPE`, `INSTRUCTIONS`,`ACTIVATEDBY`,`ACTIVEDATE`,`ACTIVETIME`) VALUES('$PONumber','$SupplierCode','$POAmount','$ReceivedAmount','$CurrencyCode','ACTIVE','$PODate','$ReqDelDate',
						 '$PODeadLine','$DeliverTo','$WarehouseCode','$POType','$Instructions','$user','$day','$time')";
		$RSINSERTHDR=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTHDR,$user,"GUARD's RECEIVING","ACTIVATEPO");
		if($RSINSERTHDR)
		{
			$GETPODTLS		=	"SELECT * FROM FDCRMSlive.podetails WHERE PONumber = '$POATP'";
			$RSGETPODTLS	=	$conn_255_10->Execute($GETPODTLS);
			if($RSGETPODTLS == false)
			{
				$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
				$DATASOURCE->logError("wms",$errmsg,$GETPODTLS,$_SESSION['username'],"GUARD's RECEIVING","ACTIVATEPO");
				$DATASOURCE->displayError();
			}
			else 
			{
				while(!$RSGETPODTLS->EOF)
				{
					$PONumber 		= $RSGETPODTLS->fields["PONumber"]; 
					$Description 	= $RSGETPODTLS->fields["Description"]; 
					$ItemNo 		= $RSGETPODTLS->fields["ItemNo"]; 
					$OrderQty 		= $RSGETPODTLS->fields["OrderQty"]; 
					$UnitMeasure 	= $RSGETPODTLS->fields["UnitMeasure"]; 
					$PackCode 		= $RSGETPODTLS->fields["PackCode"]; 
					$ApprovedQty 	= $RSGETPODTLS->fields["ApprovedQty"]; 
					$ItemCost 		= $RSGETPODTLS->fields["ItemCost"]; 
					$GrossAmount 	= $RSGETPODTLS->fields["GrossAmount"];
//					$REMARKS		= $DATASOURCE->selval($conn_250_171,"FDC_PMS","PODETAILS","REMARKS","PONUMBER = '' AND ");
					
					$INSERPODTLS	=	"INSERT INTO WMS_NEW.PODETAIL(`POATP`, `SKUNO`, `DESCRIPTION`, `ORDERQTY`, `UOM`, `PACKCODE`, `APPROVEDQTY`, `ITEMCOST`,
										`GROSSAMOUNT`,`REMARKS`) VALUES('$PONumber','$ItemNo','$Description','$OrderQty','$UnitMeasure','$PackCode','$ApprovedQty','$ItemCost',
										'$GrossAmount','$REMARKS')";
					$RSINSERPODTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERPODTLS,$user,"GUARD's RECEIVING","ACTIVATEPO");
					$RSGETPODTLS->MoveNext();
				}
			}
			echo "<script>MessageType.successMsg('P.O. has been successfully activated.','reload');</script>";
		}
	}
	$conn_255_10->CompleteTrans();
	exit();
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/guard_receiving/guard_receiving.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/guard_receiving/guard_receivingUI.php");
?>