<?php
session_start();
include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");
if (empty($_SESSION['username'])) 
{
	echo "<script>
				MessageType.sessexpMsg('wms');
		  </script>";
	$action = "";
	exit();
}
$action		=	$_GET['action'];
$USER		=	$_SESSION['username'];
$DATE		=	date("Y-m-d");
$DATETIME	=	date("Y-m-d H:i:s");
$TIME		=	date("H:i:s");
if($action == "CREATEIBMTO")
{
	$txtsfrom	=	$_GET["txtsfrom"];
	$txtsto		=	$_GET["txtsto"];
	$GETIBITEMS	=	"SELECT D.MPOSNO, D.`SKUNO`, D.`ITEMSTATUS`, SUM(D.`IB_QTY`) AS QTY, D.`DELBY`,D.MTOIBCREATED FROM WMS_NEW.SCANDATA_DTL AS D
					 LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO 
					 WHERE H.POSTEDBY != '' AND D.`DELBY` = '' AND H.POSTEDDATE BETWEEN '$txtsfrom' AND '$txtsto' AND D.`IB_QTY` != 0 AND D.MTOIBCREATED != 'Y' 
					 GROUP BY D.`SKUNO`";
	$RSGETIBITEMS	=	$conn_255_10->Execute($GETIBITEMS);
	if($RSGETIBITEMS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETIBITEMS,$_SESSION['username'],"INTERNAL BARCODE MTO","CREATEIBMTO");
		$DATASOURCE->displayError();
	}
	else
	{
		if($RSGETIBITEMS->RecordCount() == 0)
		{
			echo "<script>
						MessageType.infoMsg('No recsords found.');
				  </script>";
			exit();
		}
		else 
		{
			$table	=	"<form id='frmdata'>
							<table border='1' class='tblresult' id='tbltrxnonmto'>
								<tr class='tblresul-tbltdtls-hdr'>
									<td align='center' colspan='10' style='padding:10px;'>
										BOX NO.: 		<input type='text' id='txtboxes' name='txtboxes' size='7' class='numbersonly centered'>&nbsp;&nbsp;&nbsp;
										PACKAGE NO.: 	<input type='text' id='txtpackages' name='txtpackages' size='7' class='numbersonly centered'> 
									</td>
								</tr>
								<tr class='trheader'>
									<td width='4%' align='center'>No.</td>
									<td width='8%' align='center'>Item No.</td>
									<td width='46%' align='center'>Description</td>
									<td width='7%' align='center'>Good Qty</td>
									<td width='7%' align='center'>Status</td>
									<td width='7%' align='center'>Select</td>
								</tr>";
			$cnt = 1;
			while (!$RSGETIBITEMS->EOF)
			{
				$MPOSNO			= 	$RSGETIBITEMS->fields["MPOSNO"]; 
				$ITEMNO 		= 	$RSGETIBITEMS->fields["SKUNO"]; 
				$ITEMSTATUS		= 	$RSGETIBITEMS->fields["ITEMSTATUS"];
				$QTY			= 	$RSGETIBITEMS->fields["QTY"];
				$ITEMDESC		=	$DATASOURCE->selval($conn_250_171,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = $ITEMNO");
				$POSTEDMTOQTY	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT SUM(D.`QTY`) AS QTY FROM WMS_NEW.MTO_IB_DTL AS D LEFT JOIN WMS_NEW.MTO_IB_HDR AS H ON H.MTONO = D.MTONO WHERE H.POSTBY != '' AND (D.RANGESCANFROM BETWEEN '$txtsfrom' AND '$txtsto' OR D.RANGESCANTO BETWEEN '$txtsfrom' AND '$txtsto') AND D.`SKUNO` = '$ITEMNO' AND H.STATUS != 'CANCELLED'","QTY");
				$ONHAND			=	$QTY - $POSTEDMTOQTY;
				if($ONHAND > 0)
				{
					$table	.=		"<tr id='tr$cnt' class='trbody'>
										<td id='tdcurcnt$cnt' align='center'>$cnt</td>
										<td id='tditemno$cnt' align='center'>$ITEMNO</td>
										<td id='tddescription$cnt'>$ITEMDESC</td>
										<td id='tddonhandqty$cnt' align='center'>$ONHAND</td>
										<td align='center'>
											$ITEMSTATUS
										</td>
										<td align='center'>
											<input type='checkbox' id='txtitemno$cnt' name='txtitemno$cnt' size='10' class='txtitemnos' data-curcnt = '$cnt' value='$ITEMNO'>
											<input type='hidden' id='txtqty$cnt' name='txtqty$cnt' size='5' class='centered numbersonly txtqty' data-curcnt = '$cnt' value='$ONHAND'>
										</td>
									</tr>";
					$cnt++;
				}
				$RSGETIBITEMS->MoveNext();
			}
			$table	.=	"</table>
						<input type='hidden' name='hidcnt' id='hidcnt' value='$cnt'>
					</form>";
			echo $table;
		}
	}
	exit();
}
if($action == "SAVETRX")
{
	$mode		=	$_GET["mode"];
	
	$conn_255_10->StartTrans();
	
	if($mode == "Save")
	{
		$TRXNO	=	newTRXno($conn_255_10);
		$SAVEMTOTRX	=	"INSERT INTO WMS_NEW.MTO_IB_HDR(`MTONO`, `STATUS`, `DESTINATION`, `ADDBY`, `ADDDATE`, `ADDTIME`)
						 VALUES('$TRXNO','SAVED','PIECEWORK','$USER','{$DATE}','{$TIME}')";
		$endmsg	=	"saved";
	}
	else 
	{
		$TRXNO	=	$_GET["trxno"];
		$SAVEMTOTRX	=	"UPDATE  WMS_NEW.MTO_IB_HDR SET `STATUS` = 'UPDATED',EDITBY = '$USER', `EDITDATE` ='{$DATE}',EDITTIME='{$TIME}'
						 WHERE MTONO = '$TRXNO'";
		$endmsg	=	"updated";
	}
	$RSSAVEMTOTRX	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$SAVEMTOTRX,$USER,"INTERNAL BARCODE MTO","SAVETRX");
	if($RSSAVEMTOTRX)
	{
		echo $TRXNO;
	}
	$conn_255_10->CompleteTrans();
	exit();
}
if($action == "SAVETRXDTLS")
{

	$txtqty		=	$_GET["txtqty"];
	$txtitemno	=	$_GET["txtitemno"];
	$TRXNO		=	$_GET["TRXNO"];
	$mode		=	$_GET["mode"];
	$a			=	$_GET["a"];
	$txtboxes	=	$_GET["txtboxes"];
	$txtpackages=	$_GET["txtpackages"];
	$txtsfrom	=	$_GET["txtsfrom"];
	$txtsto		=	$_GET["txtsto"];

	$ITEMDESC	=	addslashes($DATASOURCE->selval($conn_250_171,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = '$txtitemno'"));
	$ITEMTYPE 	= 	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$txtitemno}'");
	$UNITPRICE 	= 	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$txtitemno}'");
	$GROSSAMOUNT= 	$txtqty * $UNITPRICE;
	
	$conn_255_10->StartTrans();
	if($txtitemno)
	{
		if($mode == "Save")
		{
			$SAVEMTODTLS	=	"INSERT INTO WMS_NEW.MTO_IB_DTL(`MTONO`,`SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`, `RECQTY`,`NO_OF_BOXES`, `NO_OF_PACK`, `UNITPRICE`, `GROSSAMT`, `RANGESCANFROM`, `RANGESCANTO`)
								 VALUES('{$TRXNO}','{$txtitemno}','{$ITEMDESC}','{$ITEMTYPE}','{$txtqty}','{$txtqty}','{$txtboxes}','{$txtpackages}','{$UNITPRICE}','{$GROSSAMOUNT}','$txtsfrom','$txtsto')";
			
			$UPDATEDTL 		=	"UPDATE WMS_NEW.SCANDATA_DTL AS D
								 LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO
								 SET D.MTOIBCREATED = 'Y' 
								 WHERE H.POSTEDDATE BETWEEN '$txtsfrom' AND '$txtsto' AND D.SKUNO = '$txtitemno'";
			$RSUPDATEDTL=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEDTL,$USER,"INTERNAL BARCODE MTO","SAVETRX");
		}
		else 
		{
			$SAVEMTODTLS	=	"UPDATE WMS_NEW.MTO_IB_DTL SET NO_OF_BOXES = '$txtboxes', NO_OF_PACK = '$txtpackages'
								 WHERE MTONO = '$TRXNO' AND SKUNO = '$txtitemno'";
		}
		$RSSAVEMTODTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$SAVEMTODTLS,$USER,"INTERNAL BARCODE MTO","SAVETRX");
		if($RSSAVEMTODTLS)
		{
			echo $a;
		}
	}
	$conn_255_10->CompleteTrans();
	exit();
}
if($action == "GETMTO")
{
	$txtmtono 			= $_POST["txtmtono"];
	$selstatus 			= $_POST["selstatus"];
	$seldtype 			= $_POST["seldtype"];
	$mtodfrom 			= $_POST["mtodfrom"];
	$mtodto 			= $_POST["mtodto"];
	$tfrom				= date("00:00:00");
	$tto				= date("23:59:59");
	$usesessionqry		= $_GET["USESESSIONQUERY"];
	if($txtmtono != "")
	{
		$txtmtono_Q	=	" AND MTONO = '{$txtmtono}'";
	}
	if($selstatus != "")
	{
		$selstatus_Q	=	" AND STATUS = '{$selstatus}'";
	}
	if($mtodfrom != "")
	{
		$DATE_Q	=	" AND $seldtype BETWEEN '$mtodfrom $tfrom' AND '$mtodto $tto'";
	}
	
	if($usesessionqry == "YES")
	{
		$GETMTO	=	$_SESSION["MAINQRY"];
	}
	else 
	{
		$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_IB_HDR
					 WHERE 1 $txtmtono_Q $selstatus_Q $DATE_Q";
	}
	$RSGETMTO	=	$conn_255_10->Execute($GETMTO);
	$_SESSION["MAINQRY"]	=	$GETMTO;
	if($RSGETMTO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMTO,$_SESSION['username'],"INTERNAL BARCODE MTO","GETMTO");
		$DATASOURCE->displayError();
	}
	else 
	{
		if($RSGETMTO->RecordCount() > 0)
		{
			echo "<table border='1' class='tblresult'>
					<tr class='trheader'>
				 		<td >No.</td>
				 		<td >MTO No.</td>
				 		<td >Status</td>
				 		<td >Destination</td>
				 		<td >Date Created</td>
				 		<td >Created By</td>
				 		<td >Updated Date</td>
				 		<td >Updated By</td>
				 		<td >Posted Date</td>
				 		<td >Posted By</td>
				 		<td >Printed Date</td>
				 		<td >Printed By</td>
				 		<td >Actions</td>
				 	</tr>";
			$cnt = 1;
			while (!$RSGETMTO->EOF) {
				$TRX_NO			= $RSGETMTO->fields["MTONO"]; 
				$STATUS 		= $RSGETMTO->fields["STATUS"]; 
				$DESTINATION 	= $RSGETMTO->fields["DESTINATION"]; 
				$PCWORKER 		= $RSGETMTO->fields["PCWORKER"]; 
				$ADDBY 			= $RSGETMTO->fields["ADDBY"]; 
				$ADDDATE		= $RSGETMTO->fields["ADDDATE"]; 
				$EDITBY 		= $RSGETMTO->fields["EDITBY"]; 
				$EDITDATE 		= $RSGETMTO->fields["EDITDATE"]; 
				$POSTBY 		= $RSGETMTO->fields["POSTBY"]; 
				$POSTDATE 		= $RSGETMTO->fields["POSTDATE"]; 
				$PRINTBY 		= $RSGETMTO->fields["PRINTBY"]; 
				$PRINTDATE		= $RSGETMTO->fields["PRINTDATE"]; 
				if($STATUS == "SAVED" OR $STATUS == "UPDATED")
				{
					$btnedit	=	"<img src='/wms/images/images/action_icon/new/compose.png' class='smallbtns editbtn' title='Edit Trx: $TRX_NO' data-trxno='$TRX_NO'>";
					$btnpost	=	"<img src='/wms/images/images/action_icon/new/mail.png' class='smallbtns postbtn' title='Post Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btnedit	=	"";
					$btnpost	=	"";
				}
				if($STATUS == "POSTED" or $STATUS == "PRINTED")
				{
					$btnprint	=	"<img src='/wms/images/images/action_icon/print.png' class='smallbtns printbtn' title='Print Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btnprint 	=	"";
				}
				if($STATUS != "CANCELLED" and $STATUS != "TRANSMITTED" )
				{
					$btncancel		=	"<img src='/wms/images/images/action_icon/new/stop.png' class='smallbtns cancelbtn' title='Cancel Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btncancel 		= 	"";
				}
				if($STATUS == "PRINTED")
				{
					$btntransmit	=	"<img src='/wms/images/images/action_icon/new/briefcase.png' class='smallbtns transmitbtn' title='Transmit Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btntransmit	=	"";
				}
				echo "<tr class='trdtls trbody'  id='trdtls$cnt' title='Click to view details'>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$cnt</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$TRX_NO</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$STATUS</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$DESTINATION</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$ADDDATE</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$ADDBY</td>	
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$EDITDATE</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$EDITBY</td>		
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$POSTDATE</td>	
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$POSTBY</td>		
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$PRINTDATE</td>	
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$PRINTBY</td>	
				 		<td align='center'>$btncancel $btnedit $btnpost $btnprint $btntransmit</td>	
				 	</tr> 
				 	<tr>
					 		<td id='tdtrxdtls$cnt' colspan='15' class='tdtrxdtlsClass trbody' align='center'></td>
					</tr>";
				$cnt++;
				$RSGETMTO->MoveNext();
			}
			echo "</table>";
		}
		else 
		{
			echo getTBLprev();
		}
	}
	exit();
}
if($action == "VIEWTRXDTLS")
{
	$TRX_NO		=	$_GET["TRXNO"];
	$COUNT		=	$_GET["COUNT"];
	$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_IB_DTL WHERE MTONO = '{$TRX_NO}' GROUP BY SKUNO";
	$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETDTLS,$_SESSION['username'],"INTERNAL BARCODE MTO","VIEWTRXDTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<br><table border='1' class='tblresul-tbltdtls'>
					<tr class='tblresul-tbltdtls-hdr'>
				 		<td >No.</td>
				 		<td >Item No.</td>
				 		<td >Description</td>
				 		<td >Status</td>
				 		<td >Unitprice</td>
				 		<td >Qty</td>
				 		<td >Gross Amount</td>
				 		<td >No. of Boxes</td>
				 		<td >No. of Pack</td>
				 	</tr>";
		$cnt = 1;
		$TOTALQTY	=	0;
		$TOTALGROSS	=	0;
		$TOTALBOXES	=	0;
		$TOTALPACK	=	0;
		while (!$RSGETDTLS->EOF) {
			$SKUNO			= 	$RSGETDTLS->fields["SKUNO"]; 
			$DESCRIPTION	= 	$RSGETDTLS->fields["DESCRIPTION"]; 
			$ITEMSTATUS		= 	$RSGETDTLS->fields["ITEMSTATUS"]; 
			$QTY			= 	$RSGETDTLS->fields["QTY"]; 
			$NO_OF_BOXES	= 	$RSGETDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK		= 	$RSGETDTLS->fields["NO_OF_PACK"]; 
			$BOXLABEL		= 	$RSGETDTLS->fields["BOXLABEL"]; 
			$UNITPRICE		= 	$RSGETDTLS->fields["UNITPRICE"]; 
			$GROSSAMT		= 	$RSGETDTLS->fields["GROSSAMT"]; 
			
			echo   "<tr class='trdtlsdtls tblresul-tbltdtls-dtls'  id='trdtlsdtls$cnt'>
				 		<td align='center' class=''data-trxno='$TRX_NO' data-count='$cnt'>$cnt</td>
				 		<td align='center' class=''data-trxno='$TRX_NO' data-count='$cnt'>$SKUNO</td>
				 		<td align='left'   class=''data-trxno='$TRX_NO' data-count='$cnt'>$DESCRIPTION</td>
				 		<td align='center' class=''data-trxno='$TRX_NO' data-count='$cnt'>$ITEMSTATUS</td>
				 		<td align='center' class=''data-trxno='$TRX_NO' data-count='$cnt'>$UNITPRICE</td>
				 		<td align='center' class=''data-trxno='$TRX_NO' data-count='$cnt'>$QTY</td>
				 		<td align='right'  class=''data-trxno='$TRX_NO' data-count='$cnt'>$GROSSAMT</td>
				 		<td align='center' class=''data-trxno='$TRX_NO' data-count='$cnt'>$NO_OF_BOXES</td>
				 		<td align='center' class=''data-trxno='$TRX_NO' data-count='$cnt'>$NO_OF_PACK</td>
				 	</tr>";
			$cnt++;
			$TOTALQTY 	+= 	$QTY;
			$TOTALGROSS	+=	$GROSSAMT;
			$TOTALBOXES	+=	$NO_OF_BOXES;
			$TOTALPACK	+=	$NO_OF_PACK;
			$RSGETDTLS->MoveNext();
		}
		echo "<tr align='center' class='tblresul-tbltdtls-dtls bld'>
			 		<td colspan='5'>TOTAL</td>
			 		<td >$TOTALQTY</td>
			 		<td align='right'>".number_format($TOTALGROSS,2)."</td>
			 		<td ></td>
			 		<td ></td>
		 	 </tr>
		</table><br>";
	}
	exit();
}
if($action == "EDITTRX")
{
	$TRXNO			=	$_GET["TRXNO"];
	$NO_BOXES		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_IB_DTL","NO_OF_BOXES","MTONO = '{$TRXNO}'");
	$NO_PACKAGES	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_IB_DTL","NO_OF_PACK","MTONO = '{$TRXNO}'");
	$RANGESCANFROM	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_IB_DTL","RANGESCANFROM","MTONO = '{$TRXNO}'");
	$RANGESCANTO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_IB_DTL","RANGESCANTO","MTONO = '{$TRXNO}'");
	
	$GETTRXDTLS		=	"SELECT * FROM  WMS_NEW.MTO_IB_DTL WHERE MTONO = '{$TRXNO}'";
	$RSGETTRXDTLS	=	$conn_255_10->Execute($GETTRXDTLS);
	if($RSGETTRXDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETTRXDTLS,$_SESSION['username'],"INTERNAL BARCODE MTO","EDITTRX");
		$DATASOURCE->displayError();
	}
	else 
	{
		$table	=	"<form id='frmdata'>
						<table border='1' class='tblresult' id='tbltrxnonmto'>
							<tr id='trtrxno' class='activetr'>
								<td id='tdtrxno' colspan='10' align='center'style='padding:10px;'>$TRXNO</td>
							</tr>
							<tr class='tblresul-tbltdtls-hdr'>
								<td align='center' colspan='10' style='padding:10px;'>
									# OF BOXES: 	<input type='text' id='txtboxes' name='txtboxes' size='7' class='numbersonly centered' value='$NO_BOXES'>&nbsp;&nbsp;&nbsp;
									# OF PACKAGES: 	<input type='text' id='txtpackages' name='txtpackages' size='7' class='numbersonly centered' value='$NO_PACKAGES'> 
								</td>
							</tr>
							<tr class='trheader'>
								<td width='4%' align='center'>No.</td>
								<td width='8%' align='center'>Item No.</td>
								<td width='46%' align='center'>Description</td>
								<td width='7%' align='center'>Received Qty</td>
								<td width='7%' align='center'>Select</td>
							</tr>";
		$cnt = 1;
		while (!$RSGETTRXDTLS->EOF) {
			$SKUNO	 		= 	$RSGETTRXDTLS->fields["SKUNO"]; 
			$DESCRIPTION	= 	$RSGETTRXDTLS->fields["DESCRIPTION"]; 
			$QTY			= 	$RSGETTRXDTLS->fields["QTY"]; 
			$NO_OF_BOXES	= 	$RSGETTRXDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK		= 	$RSGETTRXDTLS->fields["NO_OF_PACK"]; 
			$BOXLABEL		= 	$RSGETTRXDTLS->fields["BOXLABEL"]; 
			$RANGESCANFROM	= 	$RSGETTRXDTLS->fields["RANGESCANFROM"]; 
			$RANGESCANTO	= 	$RSGETTRXDTLS->fields["RANGESCANTO"]; 
			$ONHAND			=	$SCANNEDQTY - $POSTEDMTOQTY;
			$table	.=		"<tr id='tr$cnt' class='trbody'>
								<td id='tdcurcnt$cnt' align='center'>$cnt</td>
								<td id='tditemno$cnt' align='center'>$SKUNO</td>
								<td id='tddescription$cnt'>$DESCRIPTION</td>
								<td id='tddonhandqty$cnt' align='center'>$QTY</td>
								<td align='center'>
									<input type='checkbox' id='txtitemno$cnt' name='txtitemno$cnt' size='10' class='txtitemnos' data-curcnt = '$cnt' value='$SKUNO' checked disabled>
									<input type='hidden' id='txtqty$cnt' name='txtqty$cnt' size='5' class='txtqty centered' data-curcnt = '$cnt' value='$QTY'>
								</td>
							</tr>";
			
			$cnt++;
			$RSGETTRXDTLS->MoveNext();
		}
		$table	.=	"</table>
					<input type='hidden' name='hidcnt' id='hidcnt' value='$cnt'>
				</form>";
		echo $table;
	}
	exit();
}
if($action == "CANCELTRX")
{
	$TRXNO	=	$_GET["TRXNO"];
	$TODAY	=	date("Y-m-d H:i:s");
	
	$conn_255_10->StartTrans();
	
	$CANCELMTOTRX	=	"UPDATE WMS_NEW.MTO_IB_HDR SET `STATUS` = 'CANCELLED', `CANCELLEDBY` = '{$_SESSION['username']}', `CANCELLEDDT` = '$TODAY'
						 WHERE MTONO = '{$TRXNO}'";
	$RSCANCELMTOTRX	= 	$DATASOURCE->execQUERY("wms",$conn_255_10,$CANCELMTOTRX,$USER,"INTERNAL BARCODE MTO","CANCELTRX");
	if($RSCANCELMTOTRX)
	{
		$GETDTLS	=	"SELECT `RANGESCANFROM`, `RANGESCANTO`, SKUNO FROM WMS_NEW.MTO_IB_DTL WHERE MTONO = '$TRXNO'";
		$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
		if($RSGETDTLS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETDTLS,$_SESSION['username'],"INTERNAL BARCODE MTO","CANCELTRX");
			$DATASOURCE->displayError();
		}
		else 
		{
			while (!$RSGETDTLS->EOF) {
				$SKUNO	 			= $RSGETDTLS->fields["SKUNO"];
				$RANGESCANFROM	 	= $RSGETDTLS->fields["RANGESCANFROM"];
				$RANGESCANTO	 	= $RSGETDTLS->fields["RANGESCANTO"];
				$CANCELSCANDATA		=	"UPDATE WMS_NEW.SCANDATA_DTL AS D
										 LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO
										 SET D.MTOIBCREATED = 'N' 
										 WHERE H.POSTEDDATE BETWEEN '$RANGESCANFROM' AND '$RANGESCANTO' AND D.SKUNO = '$SKUNO'";
				$RSCANCELSCANDATA	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$CANCELSCANDATA,$_SESSION['username'],"DEFECTIVE STOCKS MTO","CANCELTRX");
				$RSGETDTLS->MoveNext();
			}
		}
	}
	$conn_255_10->CompleteTrans();
	echo "<script>
				MessageType.successMsg('Transaction $TRXNO has been successfully cancelled.');
				$('#btnreport').trigger('click',['YES']);
				$('#divtrxmto').dialog('close');
		  </script>";
	exit();
}
if($action == "POSTTRX")
{
	$TRXNO	=	$_GET["TRXNO"];
	$TODAY	=	date("Y-m-d");
	$time	=	date("H:i:s A");
	
	$conn_255_10->StartTrans();
	$POSTMTOTRX	=		"UPDATE WMS_NEW.MTO_IB_HDR SET `STATUS` = 'POSTED', `POSTBY` = '{$_SESSION['username']}', `POSTDATE` = '$TODAY',POSTTIME = '{$time}'
						 WHERE MTONO = '{$TRXNO}'";
	$RSPOSTMTOTRX	= 	$DATASOURCE->execQUERY("wms",$conn_255_10,$POSTMTOTRX,$USER,"INTERNAL BARCODE MTO","POSTTRX");
	$conn_255_10->CompleteTrans();
	echo "<script>
				MessageType.successMsg('Transaction $TRXNO has been successfully posted.');
				$('#btnreport').trigger('click',['YES']);
				$('#divtrxmto').dialog('close');
		  </script>";
	exit();
}
if ($action == "PRINTTRX")
{
	$TRXNO	=	$_GET["TRXNO"];
	
	$PRINTMTO	=	"UPDATE WMS_NEW.MTO_IB_HDR SET `STATUS` = 'PRINTED',`PRINTBY`='{$_SESSION['username']}', `PRINTDATE`='{$TODAY}', `PRINTTIME`='{$time}'
					 WHERE `MTONO` = '{$TRXNO}'";
	$RSPRINTMTO	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$PRINTMTO,$user,"INTERNAL BARCODE MTO","PRINTTRX");
	if($RSPRINTMTO)
	{
		echo "<script>
				window.open('ib_mto_pdf.php?TRXNO='+'$TRXNO');
    			window.open('ib_mto__summary_pdf.php?TRXNO='+'$TRXNO');
				$('#btnreport').trigger('click',['YES']);
		  	</script>";
	}
	exit();
}
if($action == "TRANSMITTRX")
{
	$TRXNO	=	$_GET["TRXNO"];
	$TODAY	=	date("Y-m-d H:i:s A");
	
	$conn_255_10->StartTrans();
	$TRANSMITTRX	=	"UPDATE WMS_NEW.MTO_IB_HDR SET `STATUS` = 'TRANSMITTED', `TRANSMITTED_BY` = '{$_SESSION['username']}', `TRANSMITTED_DT` = '$TODAY'
						 WHERE MTONO = '{$TRXNO}'";
	$RSTRANSMITTRX	= 	$DATASOURCE->execQUERY("wms",$conn_255_10,$TRANSMITTRX,$user,"INTERNAL BARCODE MTO","TRANSMITTRX");
	if($RSTRANSMITTRX)
	{
		$INSERTTORAW	=	"INSERT INTO WMS_NEW.MTO_RAWHDR(`MTONO`,`STATUS`,`DESTINATION`,`MTO_TRANSMITTED_DT`)
							 VALUES('$TRXNO','','PIECEWORK','$TODAY')";
		$RSINSERTTORAW	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTTORAW,$user,"INTERNAL BARCODE MTO","TRANSMITTRX");
		if($RSINSERTTORAW)
		{
			$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_IB_DTL WHERE MTONO = '{$TRXNO}'";
			$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
			if($RSGETDTLS == false)
			{
				$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
				$DATASOURCE->logError("wms",$errmsg,$GETDTLS,$_SESSION['username'],"INTERNAL BARCODE MTO","TRANSMITTRX");
				$DATASOURCE->displayError();
			}
			else 
			{
				while (!$RSGETDTLS->EOF) {
					$SKUNO			= 	$RSGETDTLS->fields["SKUNO"]; 
					$DESCRIPTION	= 	addslashes($RSGETDTLS->fields["DESCRIPTION"]); 
					$ITEMSTATUS		= 	$RSGETDTLS->fields["ITEMSTATUS"]; 
					$QTY			= 	$RSGETDTLS->fields["QTY"]; 
					$NO_OF_BOXES	= 	$RSGETDTLS->fields["NO_OF_BOXES"]; 
					$NO_OF_PACK		= 	$RSGETDTLS->fields["NO_OF_PACK"]; 
					$BOXLABEL		= 	$RSGETDTLS->fields["BOXLABEL"]; 
					$UNITPRICE		= 	$RSGETDTLS->fields["UNITPRICE"]; 
					$GROSSAMT		= 	$RSGETDTLS->fields["GROSSAMT"]; 
					
					$INSERTTORAWDTLS	=	"INSERT INTO WMS_NEW.MTO_RAWDTL(`MTONO`, `MPOSNO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`, `NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`)
											 VALUES('$TRXNO','','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$QTY','$NO_OF_BOXES','$NO_OF_PACK','$BOXLABEL','$UNITPRICE')";
					$RSINSERTTORAWDTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTTORAWDTLS,$user,"INTERNAL BARCODE MTO","TRANSMITTRX");
				$RSGETDTLS->MoveNext();
				}
			}
		}
	}
	$conn_255_10->CompleteTrans();
	echo "<script>
				MessageType.successMsg('Transaction $TRXNO has been successfully transmitted.');
				$('#btnreport').trigger('click',['YES']);
		  </script>";
	exit();
}
function  newTRXno($dbconn)
{
	$forTRXno		=	"SELECT	MTONO,ADDDATE FROM  WMS_NEW.MTO_IB_HDR order by LINE_NO";
	$rsforTRXno		=	$dbconn->Execute($forTRXno);
	if ($rsforTRXno == false) 
	{
		$errmsg	=	($dbconn->ErrorMsg()."::".__LINE__); 
		db_funcs::logError("wms",$errmsg,$forTRXno,$_SESSION['username'],"INTERNAL BARCODE MTO","newTRXno");
		db_funcs::displayError();
	}
	while (!$rsforTRXno->EOF) 
	{
		$date1		=	date('Y-m-d', strtotime($rsforTRXno->fields['ADDDATE']));	
		$lastTRXno 	= 	$rsforTRXno->fields['MTONO'];
		$rsforTRXno->MoveNext();
	}
	$dgt		=	substr($lastTRXno, 15);	
	$newdgt 	= 	$dgt + 1;
	$lnt 		= 	strlen($newdgt);
	$date2		=	date('Y-m-d');
	if( $date1==$date2 )
	{
		if($lnt == 1)
		{
			$newTRXno	=	"IBRTN-".date('Ymd').'-'."00".$newdgt;
		}
		if($lnt	==	2)
		{
			$newTRXno	=	"IBRTN-".date('Ymd').'-'."0".$newdgt;
		}
		if($lnt	==	3)
		{
			$newTRXno	=	"IBTN-".date('Ymd').'-'.$newdgt;
		}
	 }
	 else 
	 {
	 	$newTRXno	=	"IBRTN-".date('Ymd').'-'."001";
	 }
	 
	return $newTRXno;
}
function getTBLprev()
{
	return "<table border='1' class='tblresult'>
				<tr class='trheader'>
			 		<td >No.</td>
			 		<td >MTO No.</td>
			 		<td >Status</td>
			 		<td >Destination</td>
			 		<td >Pieceworker</td>
			 		<td >Date Created</td>
			 		<td >Created By</td>
			 		<td >Updated Date</td>
			 		<td >Updated By</td>
			 		<td >Posted Date</td>
			 		<td >Posted By</td>
			 		<td >Printed Date</td>
			 		<td >Printed By</td>
			 		<td >Actions</td>
			 	</tr>
		 		<tr class='trbody centered fnt-red'>
			 		<td colspan='14'>Nothing to display.</td>
			 	</tr>
			 </table>";
}
include("ib_mto.html");
include("ib_mtoUI.php");
?>