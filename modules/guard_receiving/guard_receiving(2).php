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

if($action == "GETPODTLS")
{
	$POATP	=	$_GET["PONUM"];
	
	$GETPO	=	"SELECT H.`SUPPLIERCODE`, D.`ITEMNO`, D.`ITEMDESC`, D.`APPROVEDQTY` FROM FDC_PMS.POHEADER AS H
				 LEFT JOIN FDC_PMS.PODETAILS AS D ON D.`PONUMBER` = H.`PONUMBER`
				 WHERE H.`POATP` = '$POATP' AND H.`POSTATUS` = 'POSTED' AND IS_PRINTED = 'Y'";
	$RSGETPO=	$conn_250_171->Execute($GETPO);
	if($RSGETPO == false)
	{
		$errmsg	=	($conn_250_171->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"GUARD's RECEIVING","GETPODTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		if($RSGETPO->RecordCount() > 0)
		{
			$SUPPCODE	=	$RSGETPO->fields["SUPPLIERCODE"];
			$SUPPDESC	=	$DATASOURCE->selval($conn_250_171,"FDC_PMS","SUPPLIERS","SUPPLIERNAME","SUPPLIERCODE = '$SUPPCODE'");
			$table	=	"<table class='tblresult'>
							<tr class='trheader'>
								<td class='tdoptions s20'>PO No. : $POATP</td>
								<td class='tdoptions s20'>Supplier Code : $SUPPCODE</td>
								<td class='tdoptions s20'>Supplier Description : $SUPPDESC</td>
							</tr>
							<tr>
								<td class='cntrd'colspan='3'>
									<button type='button' class='btnactivate btnsubmit'>ACTIVATE</button>
								</td>
							</tr>
							<tr class='tblresul-tbltdtls-hdr'>
								<td>Item No.</td>
								<td>Item Description</td>
								<td>Quantity</td>
							</tr>";
			while (!$RSGETPO->EOF)
			{
				$SUPPLIERCODE	=	$RSGETPO->fields["SUPPLIERCODE"];
				$ITEMNO			=	$RSGETPO->fields["ITEMNO"];
				$ITEMDESC		=	$RSGETPO->fields["ITEMDESC"];
				$APPROVEDQTY	=	$RSGETPO->fields["APPROVEDQTY"];
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
				<button type='button' class='btnactivate btnsubmit'>ACTIVATE</button>";
			echo $table;
		}
		else 
		{
			$POSTATUS	=	$DATASOURCE->selval($conn_250_171,"FDC_PMS","POHEADER","POSTATUS","`POATP` = '$POATP'");
			if($POSTATUS == "")
			{
				$msg	=	"";
			}
			else 
			{
				$msg	=	"";
			}
		}
	}
	exit();
}
if($action == "ACTIVATEPO")
{
	$POATP	=	$_GET["PONUM"];
	
	$GETPO	=	"SELECT H.`SUPPLIERCODE`, D.`ITEMNO`, D.`ITEMDESC`, D.`APPROVEDQTY` FROM FDC_PMS.POHEADER AS H
				 LEFT JOIN FDC_PMS.PODETAILS AS D ON D.`PONUMBER` = H.`PONUMBER`
				 WHERE H.`POATP` = '$POATP' AND H.`POSTATUS` = 'POSTED'";
	exit();
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/guard_receiving/guard_receiving.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/guard_receiving/guard_receivingUI.php");
?>