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
	$TRANS_NO 	=	$_POST["txtmtono"];
	$txtpcwno	=	$_POST["txtpcwno"];
	$selstatus	=	$_POST["selstatus"];
	$selDtype 	=	$_POST["selDtype"];
	$txtfrom 	=	$_POST["txtfrom"];
	$txtto 		=	$_POST["txtto"];
	
	if($TRANS_NO != "")
	{
		$TRANS_NO_Q	=	" AND TRANS_NO = '$TRANS_NO'";
		$default_Q	=	"";
	}
	if($txtpcwno != "")
	{
		$txtpcwno_Q		=	" AND PCWORKER = '$txtpcwno'";
	}
//	if($selstatus != "select")
//	{
		$STATUS_Q	=	" AND STATUS = '$selstatus'";
//	}
	if($txtfrom != "")
	{
		$DATE_Q	=	" AND $selDtype BETWEEN '$txtfrom 00:00:00' AND '$txtto 23:59:59'";
	}
	$GETMTO		=	"SELECT `MTONO`,`DRNO`, `TRANS_NO`, `ARS_NO`, `PIECEWORKER`, `STATUS`, `RECEIVEDBY`, `RECEIVEDDATE`,`POSTEDBY`, `POSTEDDATE`, `PRINTBY`, `PRINTDATE`, `TRANSMITTED_DT`, `TRANSMITTED_BY` 
					 FROM WMS_NEW.MTO_PCWHDR 
					 WHERE 1 $TRANS_NO_Q $txtpcwno_Q $STATUS_Q $DATE_Q";
	$RSGETMTO	=	$conn_255_10->Execute($GETMTO);
	if($RSGETMTO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"PIECEWORKER MTO UPDATE","GETMTO");
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
			echo "
				 
				 <div class='dropdown'>
					  <button class='dropbtn'>Show/hide columns<span>&nbsp;<img src='/wms/images/action_icon/new/arrowdown.png' class='arrowdown'></span></button>
					  <div class='dropdown-content'>
					    <input type='checkbox' id='chk0' class='chkcol' value='1' checked><label for='chk0'>No.</label><br>	
					    <input type='checkbox' id='chk1' class='chkcol' value='2' checked><label for='chk1'>MTO No.</label><br>	
					    <input type='checkbox' id='chk2' class='chkcol' value='3' checked><label for='chk2'>PIF No.</label><br>	
						<input type='checkbox' id='chk3' class='chkcol' value='4' checked><label for='chk3'>ARS No.</label><br>	
						<input type='checkbox' id='chk4' class='chkcol' value='5' checked><label for='chk4'>DR No.</label><br>	
						<input type='checkbox' id='chk5' class='chkcol' value='6' checked><label for='chk5'>Pieceworker</label><br>	
						<input type='checkbox' id='chk6' class='chkcol' value='7' checked><label for='chk6'>Status</label><br>	
						<input type='checkbox' id='chk7' class='chkcol' value='8' checked><label for='chk7'>Received By</label><br>	
						<input type='checkbox' id='chk8' class='chkcol' value='9' checked><label for='chk8'>Received Date</label><br>	
						<input type='checkbox' id='chk9' class='chkcol' value='10'><label for='chk9'>Posted By</label><br>	
						<input type='checkbox' id='chk10' class='chkcol' value='11'><label for='chk10'>Posted Date</label><br>	
						<input type='checkbox' id='chk11' class='chkcol' value='12'><label for='chk11'>Printed By</label><br>	
						<input type='checkbox' id='chk12' class='chkcol' value='13'><label for='chk12'>Printed Date</label><br>	
						<input type='checkbox' id='chk13' class='chkcol' value='14'><label for='chk13'>Transmitted By</label><br>	
						<input type='checkbox' id='chk14' class='chkcol' value='15'><label for='chk14'>Transmitted Date</label><br>	
					  </div>
				 </div>
					<table class='tblresult tablesorter' id='tblmtolist'>
					<thead>
						<tr class='trheader'>
							<th>No.</th>
							<th>MTO No.</th>
							<th>PIF No.</th>
							<th>ARS No.</th>
							<th>DR No.</th>
							<th>Pieceworker</th>
							<th>Status</th>
							<th>Received By</th>
							<th>Received Date</th>
							<th>Posted By</th>
							<th>Posted Date</th>
							<th>Printed By</th>
							<th>Printed Date</th>
							<th>Transmitted By</th>
							<th>Transmitted Date</th>
							<th>Action</th>
						</tr>
					<thead>
					<tbody>";
			$cnt = 1;
			while (!$RSGETMTO->EOF)
			{
				$MTONO				=	$RSGETMTO->fields["MTONO"];
				$DRNO				=	$RSGETMTO->fields["DRNO"];
				$TRANS_NO			=	$RSGETMTO->fields["TRANS_NO"];
				$ARS_NO				=	$RSGETMTO->fields["ARS_NO"];
				$PIECEWORKERID		=	$RSGETMTO->fields["PIECEWORKER"];
				$PIECEWORKER		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","DESCRIPTION","RECID='$PIECEWORKERID'");
				$STATUS				=	$RSGETMTO->fields["STATUS"];
				$RECEIVEDBY			=	$RSGETMTO->fields["RECEIVEDBY"];
				$RECEIVEDDATE		=	$RSGETMTO->fields["RECEIVEDDATE"];
				$POSTEDBY 			= 	$RSGETMTO->fields["POSTEDBY"]; 
				$POSTEDDATE 		= 	$RSGETMTO->fields["POSTEDDATE"]; 
				$PRINTBY 			= 	$RSGETMTO->fields["PRINTBY"]; 
				$PRINTDATE 			= 	$RSGETMTO->fields["PRINTDATE"]; 
				$TRANSMITTED_DT 	= 	$RSGETMTO->fields["TRANSMITTED_DT"]; 
				$TRANSMITTED_BY		= 	$RSGETMTO->fields["TRANSMITTED_BY"]; 
				
				if($STATUS == "")
				{
					$btnrec	=	"<img src='/wms/images/images/action_icon/new/check.png' class='smallbtns recbtn tooltips' title='Receive: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btnrec	=	"";
				}
				if($STATUS == "RECEIVED" OR $STATUS == "UPDATED")
				{
					$btnedit	=	"<img src='/wms/images/images/action_icon/new/compose.png' class='smallbtns editbtn tooltips' title='Edit: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btnedit	=	"";
				}
				if($STATUS == "UPDATED")
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
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$MTONO</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$TRANS_NO</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$ARS_NO</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$DRNO</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$PIECEWORKER</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$STATUS</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$RECEIVEDBY</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$RECEIVEDDATE</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$POSTEDBY</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$POSTEDDATE</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$PRINTBY</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$PRINTDATE</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$TRANSMITTED_BY</td>
								<td align='center' class='tddtls pntr' data-cnt='$cnt' data-mto='$MTONO'>$TRANSMITTED_DT</td>
								<td align='center'>
									 $btnrec $btnedit $btnconfirm $btnpost $btndocument
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
	$GETMTODETAILS	=	"SELECT `SKUNO`, `DESCRIPTION`, `QTY`, `RECQTY`, `DEFQTY`,`GOODQTY`,`UNITPRICE`, `ITEMSTATUS`, `GROSSAMT`,`UPDATED_BY` 
						 FROM WMS_NEW.MTO_PCWDTL
						 WHERE `MTONO` = '$MTONO'";
	$RSGETMTODETAILS	=	$conn_255_10->Execute($GETMTODETAILS);
	if($RSGETMTODETAILS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMTODETAILS,$_SESSION['username'],"PIECEWORKER MTO UPDATE","GETMTODTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<div class='tblresul-tbltdtls-hdr cntrd' id='tdmtono' style='width:100%;padding-top:15px;'>$MTONO</div>
			  <table class='tblresult tablesorter'>
				<thead>
					<tr class='trheader tooltips' title='Click and hold drag icon then drag to move column.'>
						<th>No.</th>
						<th>SKU No.</th>
						<th>Description</th>
						<th>Item Status</th>
						<th>New Item<br>Status</th>
						<th>Unit Price</th>
						<th>New Unit<br>Price</th>
						<th>Quantity</th>
						<th>Rec. Qty.</th>
						<th>Good Qty.</th>
						<th>Def. Qty</th>
						<th>Gross Amt.</th>
						<th>New Gross<br>Amt.</th>
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
			$ITEMSTATUS		= $RSGETMTODETAILS->fields["ITEMSTATUS"]; 
			$UPDATED_BY		= $RSGETMTODETAILS->fields["UPDATED_BY"]; 
			$NEWITEMSTATUS	= $DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo = '$SKUNO'");
			$NEWUNITPRICE	= $DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '$SKUNO'");
			$NEWGROSSAMT	= $NEWUNITPRICE * $RECQTY;
			
			if($NEWITEMSTATUS == "P")
			{
				$prime_color	=	"class='primeitem'";
			}
			else 
			{
				$prime_color	=	"";
			}
			if($UPDATED_BY != "")
			{
				$updatedbg	=	"updated_qty";
			}
			else 
			{
				$updatedbg	=	"";
			}
			echo "<tr class='trbody $updatedbg'  id='tr$cnt'>
						<td align='center'>$cnt</td>
						<td align='center' id='tdskuno$cnt'>$SKUNO</td>
						<td align='left'>$DESCRIPTION</td>
						<td align='center'>$ITEMSTATUS</td>
						<td align='center'$prime_color>$NEWITEMSTATUS</td>
						<td align='center' id='tdunitprice$cnt'>$UNITPRICE</td>
						<td align='center' id='tdnewunitprice$cnt'>$NEWUNITPRICE</td>
						<td align='center' id='tdqty$cnt'>$QTY</td>
						<td align='center' id='tdrecqty$cnt'>$RECQTY</td>
						<td align='center' id='tdgoodqty$cnt'>$GOODQTY</td>
						<td align='center' id='tddefqty$cnt'>$DEFQTY</td>
						<td align='right' id='tdgrossamt$cnt'>".number_format($GROSSAMT,2)."</td>
						<td align='right' id='tdnewgrossamt$cnt'>".number_format($NEWGROSSAMT,2)."</td>
				   </tr>";
			$cnt++;
			$totqty		+=	$QTY;
			$totrecqty	+=	$RECQTY;
			$totgoodqty	+=	$GOODQTY;
			$totdefqty	+=	$DEFQTY;
			$totgrossamt+=	$GROSSAMT;
			$totnewgrossamt+=	$NEWGROSSAMT;
			$RSGETMTODETAILS->MoveNext();
		}
		echo "</tbody> 
			  <tfoot>
				<tr class='trbody bld'>
					<td colspan='7' align='center'>TOTAL</td>
					<td align='center' id='tdtotqty' data-totcnt='$cnt'>".number_format($totqty)."</td>
					<td align='center' id='tdtotrecqty'>".number_format($totrecqty)."</td>
					<td align='center' id='tdtotgoodqty'>".number_format($totgoodqty)."</td>
					<td align='center' id='tdtotdefqty'>$totdefqty</td>
					<td align='right' id='tdtotgrossamt'>".number_format($totgrossamt,2)."</td>
					<td align='right' id='tdtotnewgrossamt'>".number_format($totnewgrossamt,2)."</td>
				</tr>
			  </tfoot>	
			</table>";
	}
	exit();
}
if($action == "RECEIVEMTO")
{
	$MTONO	=	$_POST["MTONO"];
	$DRNO	=	$_POST["DRNO"];
	
	$RECEIVEMTO		=	"UPDATE WMS_NEW.MTO_PCWHDR SET STATUS = 'RECEIVED',DRNO='$DRNO', RECEIVEDBY = '{$_SESSION['username']}',RECEIVEDDATE='$today',RECEIVEDTIME = '$TIME'
					 	 WHERE MTONO ='$MTONO'";
	$RSRECEIVEMTO	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$RECEIVEMTO,$_SESSION["username"],"PIECEWORKER RECEIVING","RECEIVEMTO");
	if($RSRECEIVEMTO)
	{
		echo "<script>
				MessageType.infoMsg('MTO has been successfully received.');
				$('.btnsearch').trigger('click');
				$('#divDR').dialog('close');
			  </script>";
	}
	exit();
}
if($action == "UPDATEMTOHDR")
{
	$MTONO 		= $_GET["MTONO"];
	$UPDATEHDR	=	"UPDATE WMS_NEW.MTO_PCWHDR SET `STATUS` = 'UPDATED', `UPDATED_DT`=NOW(), `UPDATED_BY`='$user'
					 WHERE MTONO = '$MTONO'";
	$RSUPDATEHDR	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEHDR,$user,"PIECEWORKER MTO UPDATE","UPDATEMTO");
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
//	$STATUS		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_PCWHDR","STATUS","MTONO = '$MTONO'");
//	$DATE		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_PCWHDR","UPDATED_DT","MTONO = '$MTONO'");
//	if($STATUS  == "RECEIVED" OR ($STATUS == "UPDATED" and  date("Y-m-d",strtotime($DATE)) != $today))
//	{
//		$UPDATEHDR	=	"UPDATE WMS_NEW.MTO_PCWHDR SET `STATUS` = 'UPDATED', `UPDATED_DT`=NOW(), `UPDATED_BY`='$user'
//						 WHERE MTONO = '$MTONO'";
//		$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEHDR,$user,"PIECEWORKER MTO UPDATE","UPDATEMTO");
//	}
	$UPDATEDTLS	=	"UPDATE WMS_NEW.MTO_PCWDTL SET `RECQTY`='$recqty', `DEFQTY`='$defqty', `GOODQTY`='$goodqty', `GROSSAMT`='$grossamt'
					 WHERE MTONO = '$MTONO' AND SKUNO = '$SKUNO'";
	$RSUPDATEDTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEDTLS,$user,"PIECEWORKER MTO UPDATE","UPDATEMTO");
	if($RSUPDATEDTLS)
	{
		echo "";
	}
	exit();
}
if($action == "POSTMTO")
{
	$MTONO 		= $_GET["MTONO"];
	$POSTMTO	=	"UPDATE WMS_NEW.MTO_PCWHDR SET `STATUS`='POSTED', `POSTEDBY`='$user', `POSTEDDATE`='$today',POSTEDTIME='$TIME'
					 WHERE MTONO = '$MTONO'";
	$RSPOSTMTO	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$POSTMTO,$user,"PIECEWORKER MTO UPDATE","POSTMTO");
	if($RSPOSTMTO)
	{
		echo "<script>MessageType.successMsg('MTO - $MTONO has been successfully posted.');$('#btnsearch').trigger('click');</script>";
	}
	exit();
}
if($action == "GETDTLS")
{
	$MTONO	=	$_GET["MTONO"];
	$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_PCWDTL
					 WHERE `MTONO` = '$MTONO'";
	$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETDTLS,$_SESSION['username'],"PIECEWORKER MTO UPDATE","GETDTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<table class='tblresul-tbltdtls'>
				<tr class='tbl-scanning-summ-hdr'>
					<td>No.</td>
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
		$cnt	=	1;
		while (!$RSGETDTLS->EOF) {
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
					<td align='center'>$cnt</td>
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
			$cnt++;
			$RSGETDTLS->MoveNext();
		}
		echo "</table>";
	}
	
	exit();
}
if($action == "PRINTMTO")
{
	$MTONO 	= 	$_GET["MTONO"];
	$PRRNO	=	getPRR($conn_255_10);
	$PRINT_UPDATE	=	"UPDATE WMS_NEW.MTO_PCWHDR SET PRR_NO='$PRRNO',STATUS = 'PRINTED',`PRINTBY` = '$user', `PRINTDATE` = '$today',PRINTTIME='$TIME'
						 WHERE MTONO = '$MTONO'";
	$RSPRINT_UPDATE	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$PRINT_UPDATE,$user,"PIECEWORKER MTO UPDATE","PRINTMTO");
	if($RSPRINT_UPDATE)
	{
		echo "	<script>
					window.open('mto_update_PDF.php?MTONO=$MTONO');
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
	echo $ITEMNO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_PCWDTL","SKUNO","(SKUNO = '$BARITEMNO' OR SKUNO = '$SCANNEDVAL') AND MTONO = '$MTONO'");
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
	$NEWGROSSAMT	= str_replace(",","",$_POST["NEWGROSSAMT"]);
	$NEWITEMSTATUS	= $DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo = '$ITEMNO'");
	$NEWUNITPRICE	= $DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '$ITEMNO'");
			
	$UPDATEHDR	=	"UPDATE WMS_NEW.MTO_PCWHDR SET `STATUS` = 'UPDATED', `UPDATED_DT`=NOW(), `UPDATED_BY`='$user'
					 WHERE MTONO = '$MTONO'";
	$RSUPDATEHDR=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEHDR,$user,"PIECEWORKER MTO UPDATE","UPDATEITEM");
	
	$UPDATEITEM	=	"UPDATE WMS_NEW.MTO_PCWDTL SET `RECQTY` = '$RECQTY', `GOODQTY`='$GOODQTY', `DEFQTY`='$DEFQTY', `GROSSAMT`='$GROSSAMT', `NEWGROSSAMT`='$NEWGROSSAMT', 
					`UPDATED_BY`='$user', `UPDATED_DT`=NOW(),`NEWITEMSTATUS` = '$NEWITEMSTATUS', `NEWUNITPRICE` = '$NEWUNITPRICE'
					 WHERE MTONO = '$MTONO' AND SKUNO = '$ITEMNO'";
	$RSUPDATEITEM	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEITEM,$user,"PIECEWORKER MTO UPDATE","UPDATEITEM");
	if($RSUPDATEITEM)
	{
		echo "<script>$('#sucmsg').text('Item has been successfully updated.');</script>";
	}
	exit();
}
function getPRR($conn_255_10)
{
	$getMaxID	=	db_funcs::selval($conn_255_10,"WMS_NEW","MTO_PCWHDR","MAX(LINE_NO)","PRR_NO != ''");
	$getMaxPRR	=	db_funcs::selval($conn_255_10,"WMS_NEW","MTO_PCWHDR","PRR_NO","LINE_NO='$getMaxID'");
	$year_month	=	date("Ym");
	if($getMaxPRR != "")
	{
		$ym_arr	=	explode("-",$getMaxPRR);
		$ym		=	$ym_arr[1];
		if($ym == $year_month)
		{
			$oldctr	=	$ym_arr[2];
			$newctr	=	intval($oldctr) + 1;
		}
		else 
		{
			$newctr	=	1;
		}
	}
	else 
	{
		$newctr	=	1;
	}
	$newctr	=	str_pad($newctr,3,"0",STR_PAD_LEFT);
	$PRRNO	=	"PRRRTN-$year_month-$newctr";
	return $PRRNO;
}
function getTblhdr()
{
	return "<table class='tblresult tablesorter' id='tblmtolist'>
				<thead>
					<tr class='trheader'>
						<th>No.</th>
							<th>MTO No.</th>
							<th>PIF No.</th>
							<th>ARS No.</th>
							<th>DR No.</th>
							<th>Pieceworker</th>
							<th>Status</th>
							<th>Received By</th>
							<th>Received Date</th>
							<th>Posted By</th>
							<th>Posted Date</th>
							<th>Printed By</th>
							<th>Printed Date</th>
							<th>Transmitted By</th>
							<th>Transmitted Date</th>
							<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<tr class='trbody fnt-red'>
						<td align='center' colspan='10'>Nothing to display.</td>
				   </tr>
				  </tbody>
			</table>";
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/pcw/mto_update/mto_update.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/pcw/mto_update/mto_updateUI.php");
?>