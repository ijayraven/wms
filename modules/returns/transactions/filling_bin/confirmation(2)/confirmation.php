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
if($action == "GETMTO")
{
	$txtmtono 	=	$_POST["txtmtono"];
	$txtpcwno	=	$_POST["txtpcwno"];
	$selstatus	=	$_POST["selstatus"];
	$selDtype 	=	$_POST["selDtype"];
	$txtfrom 	=	$_POST["txtfrom"];
	$txtto 		=	$_POST["txtto"];
	
	$default_Q	=	" AND STATUS = 'RECEIVED'";
	if($txtmtono != "")
	{
		$MTONUM_Q	=	" AND MTONO = '$txtmtono'";
		$default_Q	=	"";
	}
	if($txtpcwno != "")
	{
		$txtpcwno_Q		=	" AND PCWORKER = '$txtpcwno'";
	}
	if($selstatus != "")
	{
		$STATUS_Q	=	" AND STATUS = '$selstatus'";
		$default_Q	=	"";
	}
	if($txtfrom != "")
	{
		$DATE_Q	=	" AND $selDtype BETWEEN '$txtfrom 00:00:00' AND '$txtto 23:59:59'";
		$default_Q	=	"";	
	}
	$GETMTO		=	"SELECT `TRANSNO`, `MTONO`,`STATUS`, `PCWORKER`, `RECEIVEDDATE` FROM WMS_NEW.MTO_FILLINGBINHDR 
					 WHERE 1 $default_Q $MTONUM_Q $txtpcwno_Q $STATUS_Q $DATE_Q";
	$RSGETMTO	=	$conn_255_10->Execute($GETMTO);
	if($RSGETMTO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"FILLING BIN CONFIMRATION","GETMTO");
		$DATASOURCE->displayError();
	}
	else 
	{
		if($RSGETMTO->RecordCount() == 0)
		{
			echo getTblhdr(); exit();
		}
		else 
		{
			
			echo "<table class='tblresult tablesorter'>
					<thead>
						<tr class='trheader'>
							<th>No.</th>
							<th>PIF No.</th>
							<th>MTO No.</th>
							<th>Status</th>
							<th>Received Date</th>
							<th>Confirmed Date</th>
							<th>Action</th>
						</tr>
					<thead>
					<tbody>";
			$cnt = 1;
			while (!$RSGETMTO->EOF)
			{
				$TRANSNO			=	$RSGETMTO->fields["TRANSNO"];
				$MTONO				=	$RSGETMTO->fields["MTONO"];
				$STATUS				=	$RSGETMTO->fields["STATUS"];
				$RECEIVEDDATE		=	$RSGETMTO->fields["RECEIVEDDATE"];
				$notconfirmed		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_FILLINGBINDTL","SKUNO","MTONO = '$MTONO' and CONFIRMEDBY = ''");
				if($STATUS == "RECEIVED" OR $STATUS == "UPDATED")
				{
					$btnedit	=	"<img src='/wms/images/images/action_icon/new/compose.png' class='smallbtns editbtn tooltips' title='Edit: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btnedit	=	"";
				}
				if($STATUS == "UPDATED" AND $notconfirmed == "")
				{
					$STATUS		=	"CONFIRMED";
				}
				if($STATUS == "CONFIRMED")
				{
					$btnpost	=	"<img src='/wms/images/images/action_icon/new/mail.png' class='smallbtns postbtn tooltips' title='Post: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btnpost	=	"";
				}
				if($STATUS == "POSTED")
				{
					$btndocument	=	"<img src='/wms/images/images/action_icon/new/document.png' class='smallbtns documentbtn tooltips' title='Print: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btntdocument	=	"";
				}
				
				echo "<tr class='trbody trdtls tooltips' id='trdtl$cnt' title='Click to view details.'>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$cnt</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$TRANSNO</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$MTONO</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$STATUS</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$RECEIVEDDATE</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$CONFIRMEDDATE</td>
								<td align='center'>
									 $btnedit $btnconfirm $btnpost $btndocument
								</td>
						   </tr>";
					$cnt++;
				$RSGETMTO->MoveNext();
			}
			echo "</tbody>
			</table>";
		}
	}
	exit();
}
if($action == "GETMTODTLS")
{
	$MTONO	=	$_GET["MTONO"];
	$GETMTODETAILS	=	"SELECT `SKUNO`, `DESCRIPTION`, `QTY`, `RECQTY`, `DEFQTY`,`GOODQTY`,`UNITPRICE`, `GROSSAMT`,`CONFIRMEDBY` FROM WMS_NEW.MTO_FILLINGBINDTL
						 WHERE `MTONO` = '$MTONO'";
	$RSGETMTODETAILS	=	$conn_255_10->Execute($GETMTODETAILS);
	if($RSGETMTODETAILS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMTODETAILS,$_SESSION['username'],"FILLING BIN CONFIRMATION","GETMTODTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<div class='tblresul-tbltdtls-hdr cntrd' id='tdmtono' style='width:100%;padding-top:15px;'>$MTONO</div>
			  <table class='tblresult tablesorter'>
				<thead>
					<tr class='trheader tooltips' title='Click and hold drag icon then drag to move column.'>
						<th>No.</th>
						<th class='tdaccept'><div class='some-handle'></div>SKU No.</th>
						<th class='tdaccept'><div class='some-handle'></div>Description</th>
						<th class='tdaccept'><div class='some-handle'></div>Unit Price.</th>
						<th class=''>Quantity</th>
						<th class=''>Rec. Qty.</th>
						<th class=''>Good Qty.</th>
						<th class=''>Def. Qty</th>
						<th class=''>Gross Amt.</th>
						<th class=''>
							<label for='chkAllCon'>All</label><br/>
							<input type='checkbox' id='chkAllCon' name='chkAllCon' value='$SKUNO'>
						</th>
					</tr>
				<thead>
				<tbody>";
		$cnt		=	1;
		$totqty		=	0;
		$totrecqty	=	0;
		$totgoodqty	=	0;
		$totdefqty	=	0;
		$totgrossamt=	0;
		while (!$RSGETMTODETAILS->EOF) {
			$SKUNO 			= $RSGETMTODETAILS->fields["SKUNO"]; 
			$DESCRIPTION 	= $RSGETMTODETAILS->fields["DESCRIPTION"]; 
			$QTY 			= $RSGETMTODETAILS->fields["QTY"]; 
			$RECQTY 		= $RSGETMTODETAILS->fields["RECQTY"]; 
			$GOODQTY		= $RSGETMTODETAILS->fields["GOODQTY"]; 
			$DEFQTY 		= $RSGETMTODETAILS->fields["DEFQTY"]; 
			$UNITPRICE		= $RSGETMTODETAILS->fields["UNITPRICE"]; 
			$GROSSAMT		= $RSGETMTODETAILS->fields["GROSSAMT"]; 
			$CONFIRMEDBY	= $RSGETMTODETAILS->fields["CONFIRMEDBY"]; 
			if($CONFIRMEDBY != "")
			{
				$disabled	=	"disabled";
			}
			else 
			{
				$disabled	=	"";
			}
			echo "<tr class='trbody'  id='tr$cnt'>
						<td align='center' C>$cnt</td>
						<td align='center' id='tdskuno$cnt'>$SKUNO</td>
						<td align='left'>$DESCRIPTION</td>
						<td align='center' id='tdunitprice$cnt'>$UNITPRICE</td>
						<td align='center' id='tdqty$cnt'>$QTY</td>
						<td align='center' id='tdrecqty$cnt'>$RECQTY</td>
						<td align='center' id='tdgoodqty$cnt'>
							<input type='text' id='txtgoodqty$cnt' name='txtgoodqty$cnt' value='$GOODQTY' size='5' class='numbersonly txtgoodqties centered'>
						</td>
						<td align='center' id='tddefqty$cnt'>
							<input type='text' id='txtdefqty$cnt' name='txtdefqty$cnt' value='$DEFQTY' size='5' class='numbersonly txtdefqties  centered'>
						</td>
						<td align='right' id='tdgrossamt$cnt'>".number_format($GROSSAMT,2)."</td>
						<td align='center' >
							<input type='checkbox' id='chkcon$cnt' name='chkcon[]' class='chkcons' value='$SKUNO'>
						</td>
				   </tr>";
			$cnt++;
			$totqty		+=	$QTY;
			$totrecqty	+=	$RECQTY;
			$totgoodqty	+=	$GOODQTY;
			$totdefqty	+=	$DEFQTY;
			$totgrossamt+=	$GROSSAMT;
			$RSGETMTODETAILS->MoveNext();
		}
		echo "</tbody> 
			  <tfoot>
				<tr class='trbody bld'>
					<td colspan='4' align='center'>TOTAL</td>
					<td align='center' id='tdtotqty' data-totcnt='$cnt'>".number_format($totqty)."</td>
					<td align='center' id='tdtotrecqty'>".number_format($totrecqty)."</td>
					<td align='center' id='tdtotgoodqty'>".number_format($totgoodqty)."</td>
					<td align='center' id='tdtotdefqty'>$totdefqty</td>
					<td align='right' id='tdtotgrossamt'>".number_format($totgrossamt,2)."</td>
				</tr>
			  </tfoot>	
			</table>";
	}
	exit();
}
if($action == "UPDATEMTOHDR")
{
	$MTONO 		= $_GET["MTONO"];
	$UPDATEHDR	=	"UPDATE WMS_NEW.MTO_FILLINGBINHDR SET `STATUS` = 'UPDATED', `UPDATED_DT`=NOW(), `UPDATED_BY`='$user'
					 WHERE MTONO = '$MTONO'";
	$RSUPDATEHDR	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEHDR,$user,"MTO RAW ISSUANCE","UPDATEMTO");
	if($RSUPDATEHDR)
	{
		echo "";
	}
	exit()	;
}
if($action == "UPDATEMTO")
{
	$MTONO 		= $_GET["MTONO"];
	$SKUNO 		= $_GET["skuno"];
	$recqty 	= $_GET["recqty"];
	$goodqty 	= $_GET["goodqty"];
	$defqty 	= $_GET["defqty"];
	$grossamt 	= str_replace(",","",$_GET["grossamt"]);
//	$STATUS		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_FILLINGBINHDR","STATUS","MTONO = '$MTONO'");
//	$DATE		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_FILLINGBINHDR","UPDATED_DT","MTONO = '$MTONO'");
//	if($STATUS  == "RECEIVED" OR ($STATUS == "UPDATED" and  date("Y-m-d",strtotime($DATE)) != $today))
//	{
//		$UPDATEHDR	=	"UPDATE WMS_NEW.MTO_FILLINGBINHDR SET `STATUS` = 'UPDATED', `UPDATED_DT`=NOW(), `UPDATED_BY`='$user'
//						 WHERE MTONO = '$MTONO'";
//		$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEHDR,$user,"MTO RAW ISSUANCE","UPDATEMTO");
//	}
	$UPDATEDTLS	=	"UPDATE WMS_NEW.MTO_FILLINGBINDTL SET `RECQTY`='$recqty', `DEFQTY`='$defqty', `GOODQTY`='$goodqty', `GROSSAMT`='$grossamt'
					 WHERE MTONO = '$MTONO' AND SKUNO = '$SKUNO'";
	$RSUPDATEDTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEDTLS,$user,"MTO RAW ISSUANCE - FILLING BIN","UPDATEMTO");
	if($RSUPDATEDTLS)
	{
		echo "";
	}
	exit();
}
if($action == "POSTMTO")
{
	$MTONO 		= $_GET["MTONO"];
	$POSTMTO	=	"UPDATE WMS_NEW.MTO_FILLINGBINHDR SET `STATUS`='POSTED', `POSTEDBY`='$user', `POSTEDDATE`='$today',POSTEDTIME='$TIME'
					 WHERE MTONO = '$MTONO'";
	$RSPOSTMTO	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$POSTMTO,$user,"MTO RAW ISSUANCE - FILLING BIN","POSTMTO");
	if($RSPOSTMTO)
	{
		echo "<script>MessageType.successMsg('MTO - $MTONO has been successfully posted.');$('#btnsearch').trigger('click');</script>";
	}
	exit();
}
if($action == "CONFIRMMTO")
{
	$MTONO 		= $_GET["MTONO"];
	$conn_255_10->StartTrans();
	$CONFIRMMTO	=	"UPDATE WMS_NEW.MTO_FILLINGBINHDR SET `STATUS`='CONFIRMED', `CONFIRMEDBY`='$user', `CONFIRMEDDATE`='$today', `CONFIRMEDTIME`='$TIME'
					 WHERE MTONO = '$MTONO'";
	$RSCONFIRMMTO	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$CONFIRMMTO,$user,"FILLING BIN CONFIRMATION","CONFIRMMTO");
	$conn_255_10->CompleteTrans();
	echo "<script>MessageType.successMsg('MTO - $MTONO has been successfully confirmed.');$('#btnsearch').trigger('click');</script>";
	exit();
}
if($action == "GETDTLS")
{
	$MTONO	=	$_GET["MTONO"];
	$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_FILLINGBINDTL
					 WHERE `MTONO` = '$MTONO'";
	$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETDTLS,$_SESSION['username'],"FILLING BIN CONFIRMATION","GETDTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<table class='tblresul-tbltdtls'>
				<tr class='tbl-scanning-summ-hdr'>
					<td>MPOS No.</td>
					<td>SKU No.</td>
					<td>Description</td>
					<td>Status</td>
					<td>Qty.</td>
					<td>Rec. Qty.</td>
					<td>Good Qty.</td>
					<td>Def. Qty.</td>
					<td>Gross Amt.</td>
					<td>Old Onhand</td>
					<td>New Onhand</td>
				</tr>
				";
		while (!$RSGETDTLS->EOF) {
			$MPOSNO 		= $RSGETDTLS->fields["MPOSNO"]; 
			$SKUNO 			= $RSGETDTLS->fields["SKUNO"]; 
			$DESCRIPTION 	= $RSGETDTLS->fields["DESCRIPTION"]; 
			$QTY 			= $RSGETDTLS->fields["QTY"]; 
			$RECQTY 		= $RSGETDTLS->fields["RECQTY"]; 
			$DEFQTY 		= $RSGETDTLS->fields["DEFQTY"]; 
			$GOODQTY 		= $RSGETDTLS->fields["GOODQTY"]; 
			$GROSSAMT		= $RSGETDTLS->fields["GROSSAMT"]; 
			$CONFIRMEDBY	= $RSGETDTLS->fields["CONFIRMEDBY"]; 
			$CURRONHANDQTY	= $RSGETDTLS->fields["CURRONHANDQTY"]; 
			$NEWONHANDQTY	= $RSGETDTLS->fields["NEWONHANDQTY"]; 
			if($CONFIRMEDBY == "")
			{
				$STATUS	=	"Not Yet Confirmed";
			}
			else 
			{
				$STATUS	=	"Confirmed";
			}
			echo "<tr class='tblresul-tbltdtls-dtls'>
					<td align='center'>$MPOSNO</td>
					<td align='center'>$SKUNO</td>
					<td align='left'>$DESCRIPTION</td>
					<td align='left'>$STATUS</td>
					<td align='center'>$QTY</td>
					<td align='center'>$RECQTY</td>
					<td align='center'>$GOODQTY</td>
					<td align='center'>$DEFQTY</td>
					<td align='right'>".number_format($GROSSAMT,2)."</td>
					<td align='center'>".number_format($CURRONHANDQTY)."</td>
					<td align='center'>".number_format($NEWONHANDQTY)."</td>
				</tr>";
			$RSGETDTLS->MoveNext();
		}
		echo "</table>";
	}
	
	exit();
}
if($action == "CONFIRMITEMS")
{
	$MTONO	=	$_GET["MTONO"];
	$conn_255_10->StartTrans();
	if(!empty($_POST['chkcon']))
	{
		foreach($_POST['chkcon'] as $itemno)
		{
			$onhand		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itembal_j","onhqty","itmnbr = '$itemno' AND house = 'FDC'");
			$GOODQTY	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_FILLINGBINDTL","GOODQTY","SKUNO = '$itemno' AND MTONO = '$MTONO'");
			$LOCATION	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itembal_j","whsloc","itmnbr = '$itemno' AND house = 'FDC'");
			$NEWONHAND	=	$onhand + $GOODQTY;
			
			$UPDATE_FILL_DTLS	=	"UPDATE WMS_NEW.MTO_FILLINGBINDTL SET `CURRONHANDQTY`='$onhand', `NEWONHANDQTY`='$NEWONHAND', `CONFIRMEDBY`='$user', 
									`CONFIRMEDDATE`='$today', `CONFIRMEDTIME`='$TIME',`LOCATION`='$LOCATION'
									 WHERE SKUNO = '$itemno' AND MTONO = '$MTONO'";
			$RSUPDATE_FILL_DTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATE_FILL_DTLS,$user,"FILLING BIN CONFIRMATION","CONFIRMITEMS");
			if($RSUPDATE_FILL_DTLS)
			{
				$UPDATE_TEMBAL		=	"UPDATE FDCRMSlive.itembal_j SET onhqty = '$NEWONHAND'
										 WHERE itmnbr = '$itemno' AND house = 'FDC'";
				$RSUPDATE_TEMBAL	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATE_TEMBAL,$user,"FILLING BIN CONFIRMATION","CONFIRMITEMS");
			}
		}
	}
	$conn_255_10->CompleteTrans();
	if($RSUPDATE_TEMBAL)
	{
		echo "<script>MessageType.successMsg('Selected item/s has/have been successfully confirmed.');$('#btnsearch').trigger('click');$('#divconfirm').dialog('close');</script>";
	}
	exit();
}
if($action == "PRINTMTO")
{
	$MTONO = $_GET["MTONO"];
	$PRINT_UPDATE	=	"UPDATE WMS_NEW.MTO_FILLINGBINHDR SET STATUS = 'PRINTED',`PRINTBY` = '$user', `PRINTDATE` = '$today', `PRINTTIME` = '$TIME'
						 WHERE MTONO = '$MTONO'";
	$RSPRINT_UPDATE	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$PRINT_UPDATE,$user,"FILLING BIN CONFIRMATION","PRIMTO");
	if($RSPRINT_UPDATE)
	{
		echo "	<script>
					window.open('confirmation_PDF.php?MTONO=$MTONO');
					$('#btnsearch').trigger('click');
				</script>";
	}
	exit();
}
if($action == "SEARCHITEM")
{
	$SCANNEDVAL		=	$_GET["SCANNEDVAL"];
	$MTONO			=	$_GET["MTONO"];
	$BARITEMNO		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemNo","BarCode = '$SCANNEDVAL'");
	echo $ITEMNO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_FILLINGBINDTL","SKUNO","(SKUNO = '$BARITEMNO' OR SKUNO = '$SCANNEDVAL') AND MTONO = '$MTONO'");
	exit();
}
if($action == "UPDATEITEM")
{
	$ITEMNO		= $_POST["ITEMNO"];
	$MTONO		= $_POST["MTONO"];
	$RECQTY		= $_POST["RECQTY"];
	$GOODQTY	= $_POST["GOODQTY"];
	$DEFQTY		= $_POST["DEFQTY"];
	$GROSSAMT	= str_replace(",","",$_POST["GROSSAMT"]);
	$conn_255_10->StartTrans();
	$UPDATEHDR	=	"UPDATE WMS_NEW.MTO_FILLINGBINHDR SET `STATUS` = 'UPDATED', `UPDATED_DT`=NOW(), `UPDATED_BY`='$user'
					 WHERE MTONO = '$MTONO'";
	$RSUPDATEHDR=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEHDR,$user,"PIECEWORKER MTO UPDATE","UPDATEITEM");
	
	$UPDATEITEM	=	"UPDATE WMS_NEW.MTO_FILLINGBINDTL SET `RECQTY` = '$RECQTY', `GOODQTY`='$GOODQTY', `DEFQTY`='$DEFQTY', `GROSSAMT`='$GROSSAMT', `UPDATED_BY`='$user', `UPDATED_DT`=NOW()
					 WHERE MTONO = '$MTONO' AND SKUNO = '$ITEMNO'";
	$RSUPDATEITEM	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEITEM,$user,"PIECEWORKER MTO UPDATE","UPDATEITEM");
	$conn_255_10->CompleteTrans();
	if($RSUPDATEITEM)
	{
		echo "<script>$('#sucmsg').text('Item has been successfully updated.');</script>";
	}
	exit();
}
function getTblhdr()
{
	return "<table class='tblresult tablesorter'>
				<thead>
					<tr class='trheader'>
						<th>No.</th>
						<th>FIF No.</th>
						<th>MTO No.</th>
						<th>Status</th>
						<th>Received Date</th>
						<th>Confirmed Date</th>
						<th>Action</th>
					</tr>
				<thead>
				<tbody>
					<tr class='trbody fnt-red'>
						<td align='center' colspan='7'>Nothing to display.</td>
				   </tr>
				  </tbody>
			</table>";
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/filling_bin/confirmation/confirmation.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/filling_bin/confirmation/confirmationUI.php");
?>