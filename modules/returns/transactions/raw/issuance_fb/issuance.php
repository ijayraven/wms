<?php
session_start();
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
	$GETMTO		=	"SELECT `TRANSNO`, `MTONO`, `ARSNO`, `STATUS`, `PCWORKER`, `RECEIVEDDATE`, `ISSUEDDATE` FROM WMS_NEW.MTO_RAWHDR 
					 WHERE SUBSTRING(MTONO,1,1) = 'X' $default_Q $MTONUM_Q $txtpcwno_Q $STATUS_Q $DATE_Q";
	$RSGETMTO	=	$conn_255_10->Execute($GETMTO);
	if($RSGETMTO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"MTO RAW ISSUANCE - FILLING BING","GETMTO");
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
			echo "<form id='frmchk'>";
			echo "<table class='tblresult tablesorter'>
					<thead>
						<tr class='trheader' bgcolor='Teal'>
							<th>No.</th>
							<th>PIF No.</th>
							<th>MTO No.</th>
							<th>Status</th>
							<th>Received Date</th>
							<th>Issued Date</th>
							<th>Action</th>
						</tr>
					<thead>
					<tbody>";
			$cnt = 1;
			while (!$RSGETMTO->EOF)
			{
				$TRANSNO			=	$RSGETMTO->fields["TRANSNO"];
				$MTONO				=	$RSGETMTO->fields["MTONO"];
				$ARSNO				=	$RSGETMTO->fields["ARSNO"];
				$STATUS				=	$RSGETMTO->fields["STATUS"];
				$PCWORKER			=	$RSGETMTO->fields["PCWORKER"];
				$PCWORKER			=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","DESCRIPTION","RECID = '$PCWORKER'");
				$RECEIVEDDATE		=	$RSGETMTO->fields["RECEIVEDDATE"];
				$ISSUEDDATE			=	$RSGETMTO->fields["ISSUEDDATE"];
				
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
					$btntissue	=	"<img src='/wms/images/images/action_icon/new/clipboard.png' class='smallbtns issuebtn tooltips' title='Issue: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btntissue	=	"";
				}
				if($STATUS == "ISSUED")
				{
					$btndocument	=	"<img src='/wms/images/images/action_icon/new/document.png' class='smallbtns documentbtn tooltips' title='Print: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btntdocument	=	"";
				}
				
				echo "<tr class='trbody'>
								<td align='center'>$cnt</td>
								<td align='center'>$TRANSNO</td>
								<td align='center'>$MTONO</td>
								<td align='center'>$STATUS</td>
								<td align='center'>$RECEIVEDDATE</td>
								<td align='center'>$ISSUEDDATE</td>
								<td align='center'>
									$btntissue $btnedit $btnpost $btndocument
								</td>
						   </tr>";
					$cnt++;
				$RSGETMTO->MoveNext();
			}
			echo "</tbody>
			</table>
			</form>";
		}
	}
	exit();
}
if($action == "GETMTODTLS")
{
	$MTONO	=	$_GET["MTONO"];
	$GETMTODETAILS	=	"SELECT `MPOSNO`, `SKUNO`, `DESCRIPTION`, `QTY`, `RECQTY`, `DEFQTY`,`GOODQTY`,`UNITPRICE`, `GROSSAMT` FROM WMS_NEW.MTO_RAWDTL
						 WHERE `MTONO` = '$MTONO'";
	$RSGETMTODETAILS	=	$conn_255_10->Execute($GETMTODETAILS);
	if($RSGETMTODETAILS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMTODETAILS,$_SESSION['username'],"RAW ISSUANCE-FILLING BIN","GETMTODTLS");
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
			echo "<tr class='trbody'  id='tr$cnt'>
						<td align='center'>$cnt</td>
						<td align='center' id='tdskuno$cnt'>$SKUNO</td>
						<td align='left'>$DESCRIPTION</td>
						<td align='center' id='tdunitprice$cnt'>$UNITPRICE</td>
						<td align='center' id='tdqty$cnt'>$QTY</td>
						<td align='center'>
							<input type='text' id='txtrecqty$cnt' name='txtrecqty$cnt' size='5' value='$RECQTY' class='cntrd txtrecqtys numbersonly' data-cnt='$cnt'>
						</td>
						<td align='center'>
							<input type='text' id='txtgoodqty$cnt' name='txtgoodqty$cnt' size='5' value='$GOODQTY' class='cntrd txtgoodqtys numbersonly' data-cnt='$cnt'>
						</td>
						<td align='center'>
							<input type='text' id='txtdefqty$cnt' name='txtdefqty$cnt' size='5' value='$DEFQTY' class='cntrd txtdefqtys numbersonly' data-cnt='$cnt'>
						</td>
						<td align='right' id='tdgrossamt$cnt'>".number_format($GROSSAMT,2)."</td>
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
	$UPDATEHDR	=	"UPDATE WMS_NEW.MTO_RAWHDR SET `STATUS` = 'UPDATED', `UPDATED_DT`=NOW(), `UPDATED_BY`='$user'
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
//	$STATUS		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RAWHDR","STATUS","MTONO = '$MTONO'");
//	$DATE		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RAWHDR","UPDATED_DT","MTONO = '$MTONO'");
//	if($STATUS  == "RECEIVED" OR ($STATUS == "UPDATED" and  date("Y-m-d",strtotime($DATE)) != $today))
//	{
//		$UPDATEHDR	=	"UPDATE WMS_NEW.MTO_RAWHDR SET `STATUS` = 'UPDATED', `UPDATED_DT`=NOW(), `UPDATED_BY`='$user'
//						 WHERE MTONO = '$MTONO'";
//		$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEHDR,$user,"MTO RAW ISSUANCE","UPDATEMTO");
//	}
	$UPDATEDTLS	=	"UPDATE WMS_NEW.MTO_RAWDTL SET `RECQTY`='$recqty', `DEFQTY`='$defqty', `GOODQTY`='$goodqty', `GROSSAMT`='$grossamt'
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
	$POSTMTO	=	"UPDATE WMS_NEW.MTO_RAWHDR SET `STATUS`='POSTED', `POSTEDBY`='$user', `POSTEDDT`=NOW()
					 WHERE MTONO = '$MTONO'";
	$RSPOSTMTO	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$POSTMTO,$user,"MTO RAW ISSUANCE - FILLING BIN","POSTMTO");
	if($RSPOSTMTO)
	{
		echo "<script>MessageType.successMsg('MTO - $MTONO has been successfully posted.');$('#btnsearch').trigger('click');</script>";
	}
	exit();
}
if($action == "ISSUEMTO")
{
	$MTONO 		= $_GET["MTONO"];
	$PIFNO		= getPIF($conn_255_10);
	$conn_255_10->StartTrans();
	$ISSUEMTO	=	"UPDATE WMS_NEW.MTO_RAWHDR SET `TRANSNO`='$PIFNO', `STATUS`='ISSUED', `ISSUEDBY`='$user', `ISSUEDDATE`='$today', `ISSUEDTIME`='$TIME'
					 WHERE MTONO = '$MTONO'";
	$RSISSUEMTO	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$ISSUEMTO,$user,"MTO RAW ISSUANCE - FILLING BIN","ISSUEMTO");
	if($RSISSUEMTO)
	{
		$INSERTHDR	=	"INSERT INTO WMS_NEW.MTO_FILLINGBINHDR(`TRANSNO`, `MTONO`, `STATUS`,`RAW_TRANSMITTED_DT`)
						 VALUES('$PIFNO','$MTONO','',NOW())";
		$RSINSERTHDR	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTHDR,$user,"MTO RAW ISSUANCE - FILLING BIN","ISSUEMTO");
		if($RSINSERTHDR)
		{
			$GETDTLS	=	"SELECT SKUNO,DESCRIPTION,ITEMSTATUS,SUM(QTY) AS QTY,NO_OF_BOXES,NO_OF_PACK,BOXLABEL,UNITPRICE FROM WMS_NEW.MTO_RAWDTL
							 WHERE MTONO = '$MTONO' GROUP BY SKUNO";
			$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
			if($RSGETDTLS == false)
			{
				$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
				$DATASOURCE->logError("wms",$errmsg,$GETDTLS,$_SESSION['username'],"MTO RAW ISSUANCE - FILLING BING","ISSUEMTO");
				$DATASOURCE->displayError();
			}
			else 
			{
				while (!$RSGETDTLS->EOF) {
					$SKUNO 			= $RSGETDTLS->fields["SKUNO"]; 
					$DESCRIPTION 	= addslashes($RSGETDTLS->fields["DESCRIPTION"]); 
					$ITEMSTATUS 	= $RSGETDTLS->fields["ITEMSTATUS"]; 
					$QTY	 		= $RSGETDTLS->fields["QTY"]; 
					$RECQTY 		= $RSGETDTLS->fields["RECQTY"]; 
					$DEFQTY 		= $RSGETDTLS->fields["DEFQTY"]; 
					$GOODQTY 		= $RSGETDTLS->fields["GOODQTY"]; 
					$NO_OF_BOXES 	= $RSGETDTLS->fields["NO_OF_BOXES"]; 
					$NO_OF_PACK 	= $RSGETDTLS->fields["NO_OF_PACK"]; 
					$BOXLABEL 		= $RSGETDTLS->fields["BOXLABEL"]; 
					$UNITPRICE 		= $RSGETDTLS->fields["UNITPRICE"]; 
					$GROSSAMT		= $RSGETDTLS->fields["GROSSAMT"]; 
					
					$INSERTDTLS		=	"INSERT INTO WMS_NEW.MTO_FILLINGBINDTL(`TRANSNO`, `MTONO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`,`RECQTY`, `GOODQTY`, `NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`, `GROSSAMT`)
										 VALUES('$PIFNO','$MTONO','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$GOODQTY','$GOODQTY','$GOODQTY','$NO_OF_BOXES','$NO_OF_PACK','$BOXLABEL','$UNITPRICE','$GROSSAMT')";
					$RSINSERTDTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTDTLS,$user,"MTO RAW ISSUANCE - FILLING BIN","ISSUEMTO");
					$RSGETDTLS->MoveNext();
				}
			}
		}
	}
	$conn_255_10->CompleteTrans();
	echo "<script>MessageType.successMsg('MTO - $MTONO has been successfully issued.');$('#btnsearch').trigger('click');</script>";
	exit();
}

function getPIF($conn_255_10)
{
	$getMaxID	=	db_funcs::selval($conn_255_10,"WMS_NEW","MTO_RAWHDR","MAX(LINE_NO)","TRANSNO != ''");
	$getMaxPIF	=	db_funcs::selval($conn_255_10,"WMS_NEW","MTO_RAWHDR","TRANSNO","LINE_NO='$getMaxID'");
	$year_month	=	date("Ym");
	if($getMaxPIF != "")
	{
		$ym_arr	=	explode("-",$getMaxPIF);
		$ym		=	$ym_arr[0];
		if($ym == $year_month)
		{
			$oldctr	=	$ym_arr[1];
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
	$PIFNO	=	"$year_month-$newctr";
	return $PIFNO;
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
						<th>Issued Date</th>
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
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/raw/issuance_fb/issuance.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/raw/issuance_fb/issuanceUI.php");
?>