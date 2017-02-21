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
$action	=	$_GET['action'];
if($action == "CREATEEXMTO")
{
	$txtsfrom	=	$_GET["txtsfrom"];
	$txtsto		=	$_GET["txtsto"];
	$GETEXITEMS	=	"SELECT D.MPOSNO, D.`SKUNO`, D.`ITEMSTATUS`, SUM(D.`POSTEDQTY`) AS QTY, D.`DEFECTIVEQTY`, D.`DELBY`,D.MTOPRIMECREATED FROM WMS_NEW.SCANDATA_DTL AS D
					 LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO 
					 WHERE H.POSTEDBY != '' AND D.`DELBY` = '' AND D.`ITEMSTATUS` = 'P' AND H.SCANDATE BETWEEN '$txtsfrom' AND '$txtsto'
					 AND D.MTOPRIMECREATED = 'N'
					 GROUP BY D.`SKUNO`";
	$RSGETEXITEMS	=	$conn_255_10->Execute($GETEXITEMS);
	if($RSGETEXITEMS == false)
	{
		echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
	}
	else
	{
		if($RSGETEXITEMS->RecordCount() == 0)
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
										# OF BOXES: 	<input type='text' id='txtboxes' name='txtboxes' size='7' class='numbersonly centered'>&nbsp;&nbsp;&nbsp;
										# OF PACKAGES: 	<input type='text' id='txtpackages' name='txtpackages' size='7' class='numbersonly centered'> 
									</td>
								</tr>
								<tr class='trheader'>
									<td width='4%' align='center'>No.</td>
									<td width='8%' align='center'>Item No.</td>
									<td width='46%' align='center'>Description</td>
									<td width='7%' align='center'>Good Qty</td>
									<td width='7%' align='center'>Status</td>
								</tr>";
			$cnt = 1;
			while (!$RSGETEXITEMS->EOF)
			{
				$MPOSNO			= 	$RSGETEXITEMS->fields["MPOSNO"]; 
				$ITEMNO 		= 	$RSGETEXITEMS->fields["SKUNO"]; 
				$ITEMSTATUS		= 	$RSGETEXITEMS->fields["ITEMSTATUS"];
				$POSTEDQTY		= 	$RSGETEXITEMS->fields["QTY"];
				$ITEMDESC		=	$DATASOURCE->selval($conn_250_171,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = $ITEMNO");
				$DEFECTIVEQTY	=	$RSGETEXITEMS->fields["DEFECTIVEQTY"];;
				$ONHAND			=	$POSTEDQTY - $DEFECTIVEQTY;
				if($ONHAND > 0)
				{
					$table	.=		"<tr id='tr$cnt' class='trbody'>
										<td id='tdcurcnt$cnt' align='center'>$cnt</td>
										<td id='tditemno$cnt' align='center'>$ITEMNO</td>
										<td id='tddescription$cnt'>$ITEMDESC</td>
										<td id='tddonhandqty$cnt' align='center'>
											$ONHAND
											<input type='hidden' id='txtqty$cnt' name='txtqty$cnt' size='5' class='centered' data-curcnt = '$cnt' value='$ONHAND'>
											<input type='hidden' id='txtmpos$cnt' name='txtmpos$cnt' size='5' class='centered' data-curcnt = '$cnt' value='$MPOSNO'>
										</td>
										<td align='center'>
											$ITEMSTATUS
											<input type='hidden' id='txtitemno$cnt' name='txtitemno$cnt' size='10' class='txtitemnos' data-curcnt = '$cnt' value='$ITEMNO'>
										</td>
									</tr>";
				$cnt++;
				}
				$RSGETEXITEMS->MoveNext();
			}
			$table	.=	"</table>
						<input type='hidden' name='hidcnt' id='hidcnt' value='$cnt'>
					</form>";
			echo $table;
			echo "<script>
						$('#divtrxmto').dialog('open'); 
				</script>";
		}
	}
	exit();
}
if($action == "SAVETRXHDR")
{
	$TODAY				=	date("Y-m-d");
	$time				=	date("H:i:s A");
	
	$conn_255_10->StartTrans();
	$TXNO	=	newTRXno($conn_255_10);
	$SAVEMTOTRX	=	"INSERT INTO WMS_NEW.MTO_PRIMESTOCK_HDR(`MTONO`, `STATUS`, `DESTINATION`, `ADDBY`, `ADDDATE`, `ADDTIME`)
					 VALUES('{$TXNO}','SAVED','TIANGGE','{$_SESSION['username']}','{$TODAY}','{$time}')";
	$RSSAVEMTOTRX = $conn_255_10->Execute($SAVEMTOTRX);
	if($RSSAVEMTOTRX == false)
	{
		echo $conn_255_10->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		echo "$TXNO";
	}
	$conn_255_10->CompleteTrans();
	exit();
}
if($action == "SAVETRXDTLS")
{
	$conn_255_10->StartTrans();
		$TRXNO			=	$_GET["TRXNO"];
		$txtitemno		=	$_GET["txtitemno"];
		$txtqty			=	$_GET["txtqty"];
		$txtnoboxes		=	$_GET["txtnoboxes"];
		$txtnopackages	=	$_GET["txtnopackages"];
		$txtboxlabel	=	$_GET["txtboxlabel"];
		$txtmpos		=	$_GET["txtmpos"];
		$txtsfrom		=	$_GET["txtsfrom"];
		$txtsto			=	$_GET["txtsto"];
	
		$GETDESC	= addslashes($DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$txtitemno}'"));
		$ITEMTYPE 	= $DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$txtitemno}'");
		$UNITPRICE 	= $DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
		$GROSSAMOUNT= $txtqty * $UNITPRICE;
		$SAVEMTODTLS	=	"INSERT INTO WMS_NEW.MTO_PRIMESTOCK_DTL(`MTONO`,`SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`,`NO_OF_BOXES`, `NO_OF_PACK`,`BOXLABEL`, `UNITPRICE`, `GROSSAMT`, `RANGESCANFROM`, `RANGESCANTO`)
							 VALUES('{$TRXNO}','{$txtitemno}','{$GETDESC}','{$ITEMTYPE}','{$txtqty}','{$txtnoboxes}','{$txtnopackages}','{$txtboxlabel}','{$UNITPRICE}','{$GROSSAMOUNT}','$txtsfrom','$txtsto')";
		$RSSAVEMTODTLS	=	$conn_255_10->Execute($SAVEMTODTLS);
		if($RSSAVEMTODTLS == false)
		{
			echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
		}
		else 
		{
			echo "";
		}
		$UPDATEDTL 	=	"UPDATE WMS_NEW.SCANDATA_DTL AS D
						 LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO
						 SET D.MTOPRIMECREATED = 'Y' 
						 WHERE H.SCANDATE BETWEEN '$txtsfrom' AND '$txtsto' AND D.SKUNO = '$txtitemno'";
		$RSUPDATEDTL=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEDTL,$_SESSION['username'],"PRIME STOCKS MTO","SAVETRXDTLS");
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
//	$rdodestination		= $_POST["rdodestination"];
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
//	if($rdodestination != "")
//	{
//		$rdodestination_Q	=	" AND DESTINATION = '{$rdodestination}'";
//	}
	
	if($usesessionqry == "YES")
	{
		$GETMTO	=	$_SESSION["MAINQRY"];
	}
	else 
	{
		$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_PRIMESTOCK_HDR
					 WHERE 1 $txtmtono_Q $selstatus_Q $DATE_Q";
	}
	$RSGETMTO	=	$conn_255_10->Execute($GETMTO);
	$_SESSION["MAINQRY"]	=	$GETMTO;
	if($RSGETMTO == false)
	{
		echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
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
			echo "<script>$('#hidcnt').val('$cnt');</script>";
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
	$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_PRIMESTOCK_DTL WHERE MTONO = '{$TRX_NO}' GROUP BY SKUNO";
	$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
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
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$cnt</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$SKUNO</td>
				 		<td align='left'   class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$DESCRIPTION</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$ITEMSTATUS</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$UNITPRICE</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$QTY</td>
				 		<td align='right'  class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$GROSSAMT</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$NO_OF_BOXES</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$NO_OF_PACK</td>
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
	$NO_BOXES		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_PRIMESTOCK_DTL","NO_OF_BOXES","MTONO = '{$TRXNO}'");
	$NO_PACKAGES	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_PRIMESTOCK_DTL","NO_OF_PACK","MTONO = '{$TRXNO}'");
	$GETTRXDTLS		=	"SELECT * FROM  WMS_NEW.MTO_PRIMESTOCK_DTL WHERE MTONO = '{$TRXNO}'";
	$RSGETTRXDTLS	=	$conn_255_10->Execute($GETTRXDTLS);
	if($RSGETTRXDTLS == false)
	{
		echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
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
								<td width='7%' align='center'>Good Qty</td>
							</tr>";
		$cnt = 1;
		while (!$RSGETTRXDTLS->EOF) {
			$SKUNO	 		= 	$RSGETTRXDTLS->fields["SKUNO"]; 
			$DESCRIPTION	= 	$RSGETTRXDTLS->fields["DESCRIPTION"]; 
			$QTY			= 	$RSGETTRXDTLS->fields["QTY"]; 
			$NO_OF_BOXES	= 	$RSGETTRXDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK		= 	$RSGETTRXDTLS->fields["NO_OF_PACK"]; 
			$BOXLABEL		= 	$RSGETTRXDTLS->fields["BOXLABEL"]; 
			
			$table	.=		"<tr id='tr$cnt' class='trbody'>
								<td id='tdcurcnt$cnt' align='center'>$cnt</td>
								<td id='tditemno$cnt' align='center'>$SKUNO</td>
								<td id='tddescription$cnt'>$DESCRIPTION</td>
								<td id='tddonhandqty$cnt' align='center'>
									$QTY
									<input type='hidden' id='txtqty$cnt' name='txtqty$cnt' size='5' class='txtqty centered' data-curcnt = '$cnt' value='$QTY'>
									<input type='hidden' id='txtitemno$cnt' name='txtitemno$cnt' size='10' class='txtitemnos' data-curcnt = '$cnt' value='$SKUNO'>
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
if($action == "UPDATETRX")
{
	$TRX_NO			=	$_GET["TRXNO"];
	$cnt			=	$_POST["hidcnt"];
	$txtnoboxes		=	$_POST["txtboxes"];
	$txtnopackages	=	$_POST["txtpackages"];
	$TODAY			=	date("Y-m-d");
	$time			=	date("H:i:s A");
	$conn_255_10->StartTrans();
	$UPDATEMTOTRX	=	"UPDATE  WMS_NEW.MTO_PRIMESTOCK_HDR SET `STATUS` = 'UPDATED',EDITBY = '{$_SESSION['username']}', `EDITDATE` ='{$TODAY}',EDITTIME='{$time}'
						 WHERE MTONO = '{$TRX_NO}'";
	$RSUPDATEMTOTRX	= 	$conn_255_10->Execute($UPDATEMTOTRX);
	if($RSUPDATEMTOTRX == false)
	{
		echo $conn_255_10->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		$UPDATEDTL	=	"UPDATE WMS_NEW.MTO_PRIMESTOCK_DTL SET NO_OF_BOXES = '$txtnoboxes', NO_OF_PACK = '$txtnopackages'
						 WHERE MTONO = '$TRX_NO'";
		$RSUPDATEDTL=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEDTL,$_SESSION['username'],"PRIME RETURNS MTO","UPDATETRX");
	}
	$conn_255_10->CompleteTrans();
	echo "<script>
			alert('Transaction $TRX_NO has been successfully updated.');
			$('#btnreport').trigger('click',['YES']);
			$('#divtrxmto').dialog('close');
			resettrx();
		</script>";
	exit();
}
if($action == "POSTTRX")
{
	$TRXNO	=	$_GET["TRXNO"];
	$TODAY	=	date("Y-m-d");
	$time	=	date("H:i:s A");
	
	$conn_255_10->StartTrans();
	$POSTMTOTRX	=		"UPDATE WMS_NEW.MTO_PRIMESTOCK_HDR SET `STATUS` = 'POSTED', `POSTBY` = '{$_SESSION['username']}', `POSTDATE` = '$TODAY',POSTTIME = '{$time}'
						 WHERE MTONO = '{$TRXNO}'";
	$RSPOSTMTOTRX	= 	$conn_255_10->Execute($POSTMTOTRX);
	if($RSPOSTMTOTRX == false)
	{
		echo $conn_255_10->Errormsg()."::".__LINE__; exit();
	}
	
	$conn_255_10->CompleteTrans();
	echo "<script>
				alert('Transaction $TRXNO has been successfully posted.');
				$('#btnreport').trigger('click',['YES']);
				$('#divtrxmto').dialog('close');
				resettrx();
		  </script>";
	exit();
}
if($action == "CANCELTRX")
{
	$TRXNO	=	$_GET["TRXNO"];
	$TODAY	=	date("Y-m-d H:i:s");
	
	$conn_255_10->StartTrans();
	$CANCELMTOTRX	=		"UPDATE WMS_NEW.MTO_PRIMESTOCK_HDR SET `STATUS` = 'CANCELLED', `CANCELLEDBY` = '{$_SESSION['username']}', `CANCELLEDDT` = '$TODAY'
							 WHERE MTONO = '{$TRXNO}'";
	$RSCANCELMTOTRX	= 	$conn_255_10->Execute($CANCELMTOTRX);
	if($RSCANCELMTOTRX == false)
	{
		echo $conn_255_10->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		$GETDTLS	=	"SELECT `RANGESCANFROM`, `RANGESCANTO`, SKUNO FROM WMS_NEW.MTO_PRIMESTOCK_DTL WHERE MTONO = '$TRXNO'";
		$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
		if($RSGETDTLS == false)
		{
			echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
		}
		else 
		{
			while (!$RSGETDTLS->EOF) {
				$SKUNO	 			= $RSGETDTLS->fields["SKUNO"];
				$RANGESCANFROM	 	= $RSGETDTLS->fields["RANGESCANFROM"];
				$RANGESCANTO	 	= $RSGETDTLS->fields["RANGESCANTO"];
				$CANCELSCANDATA		=	"UPDATE WMS_NEW.SCANDATA_DTL AS D
										 LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO
										 SET D.MTOPRIMECREATED = 'N' 
										 WHERE H.SCANDATE BETWEEN '$RANGESCANFROM' AND '$RANGESCANTO' AND D.SKUNO = '$SKUNO'";
				$RSCANCELSCANDATA	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$CANCELSCANDATA,$_SESSION['username'],"PRIME STOCKS MTO","CANCELTRX");
				$RSGETDTLS->MoveNext();
			}
		}
	}
	
	$conn_255_10->CompleteTrans();
	echo "<script>
				alert('Transaction $TRXNO has been successfully cancelled.');
				$('#btnreport').trigger('click',['YES']);
				$('#divtrxmto').dialog('close');
				resettrx();
		  </script>";
	exit();
}
if($action == "TRANSMITTRX")
{
	$TRXNO	=	$_GET["TRXNO"];
	$TODAY	=	date("Y-m-d H:i:s A");
	
	$conn_255_10->StartTrans();
	$TRANSMITTRX	=	"UPDATE WMS_NEW.MTO_PRIMESTOCK_HDR SET `STATUS` = 'TRANSMITTED', `TRANSMITTED_BY` = '{$_SESSION['username']}', `TRANSMITTED_DT` = '$TODAY'
						 WHERE MTONO = '{$TRXNO}'";
	$RSTRANSMITTRX	= 	$conn_255_10->Execute($TRANSMITTRX);
	if($RSTRANSMITTRX == false)
	{
		echo $conn_255_10->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		$insertToRaw	=	"INSERT INTO WMS_NEW.MTO_TIANGGE_HDR(`MTONO`,`STATUS`,`DESTINATION`,`MTO_TRANSMITTED_DT`)
							 VALUES('$TRXNO','','FILLING BIN','$TODAY')";
		$RSinsertToRaw	=	$conn_255_10->Execute($insertToRaw);
		if($RSinsertToRaw == false)
		{
			echo $conn_255_10->Errormsg()."::".__LINE__; exit();
		}
		else 
		{
			$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_PRIMESTOCK_DTL WHERE MTONO = '{$TRXNO}'";
			$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
			if($RSGETDTLS == false)
			{
				echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
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
					
					$insertToRawDtls	=	"INSERT INTO WMS_NEW.MTO_TIANGGE_DTL(`MTONO`, `MPOSNO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`, `NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`)
											 VALUES('$TRXNO','','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$QTY','$NO_OF_BOXES','$NO_OF_PACK','$BOXLABEL','$UNITPRICE')";
					$RSinsertToRawDtls	=	$conn_255_10->Execute($insertToRawDtls);
					if($RSinsertToRawDtls == false)
					{
						echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
					}
				$RSGETDTLS->MoveNext();
				}
			}
		}
	}
	$conn_255_10->CompleteTrans();
	echo "<script>
				alert('Transaction $TRXNO has been successfully transmitted.');
				$('#btnreport').trigger('click',['YES']);
				$('#divtrxmto').dialog('close');
				resettrx();
		  </script>";
	exit();
}
function  newTRXno($dbconn)
{
	$forTRXno		=	"SELECT	MTONO,ADDDATE FROM  WMS_NEW.MTO_PRIMESTOCK_HDR order by LINE_NO";
	$rsforTRXno		=	$dbconn->Execute($forTRXno);
	if ($rsforTRXno == false) 
	{
		echo $errmsg	=	$conn->ErrorMsg()."::".__LINE__; 
		exit();
	}
	while (!$rsforTRXno->EOF) 
	{
		$date1		=	date('Y-m-d', strtotime($rsforTRXno->fields['ADDDATE']));	
		$lastTRXno 	= 	$rsforTRXno->fields['MTONO'];
		$rsforTRXno->MoveNext();
	}
	 $dgt		=	substr($lastTRXno, 14);	
	 $newdgt 	= 	$dgt + 1;
	 $lnt 		= 	strlen($newdgt);
	 $date2		=	date('Y-m-d');
	 if( $date1==$date2 )
	 {
		if($lnt == 1)
		{
			$newTRXno	=	"PRTN-".date('Ymd').'-'."00".$newdgt;
		}
		if($lnt	==	2)
		{
			$newTRXno	=	"PRTN-".date('Ymd').'-'."0".$newdgt;
		}
		if($lnt	==	3)
		{
			$newTRXno	=	"PRTN-".date('Ymd').'-'.$newdgt;
		}
	 }
	 else 
	 {
	 	$newTRXno	=	"PRTN-".date('Ymd').'-'."001";
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
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/prime_mto/prime_mto.html");
?>
