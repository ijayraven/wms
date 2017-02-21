<?php
//sample change
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
	$GETEXITEMS	=	"SELECT D.`ITEMNO`, D.`ITEM_DESC`,H.`POSTEDDT` FROM WMS_LOOKUP.MTO_EX_ITEMS_DTLS AS D
					 LEFT JOIN WMS_LOOKUP.MTO_EX_ITEMS_HDR AS H ON H.TRX_NO = D.TRX_NO 
					 WHERE H.POSTEDBY != '' AND D.CANCELLED = 'N'
					 GROUP BY D.ITEMNO";
	$RSGETEXITEMS	=	$Filstar_conn->Execute($GETEXITEMS);
	if($RSGETEXITEMS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else
	{
		$table	=	"<form id='frmdata'>
						<table border='1' class='tblresult' id='tbltrxnonmto'>
							<tr class='tblresul-tbltdtls-hdr'>
								<td align='center' colspan='10' style='padding:10px;'>
									DESTINATION:
									<label for='rdoraw_C'><input type='radio' id='rdoraw_C' name='rdodestination_C' value='RAW' checked>RAW</label>
								</td>
							</tr>
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
								<td width='7%' align='center'>Qty</td>
								<td width='7%' align='center'>Status</td>
								<td width='7%' align='center'>Action</td>
							</tr>";
		$cnt = 1;
		while (!$RSGETEXITEMS->EOF)
		{
			$ITEMNO 		= 	$RSGETEXITEMS->fields["ITEMNO"]; 
			$ITEM_DESC 		= 	$RSGETEXITEMS->fields["ITEM_DESC"];
			$POSTEDDT 		= 	$RSGETEXITEMS->fields["POSTEDDT"];
			$SCANNEDQTY		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_DTL AS D LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO","SUM(POSTEDQTY)","SKUNO= '{$ITEMNO}' AND STATUS = 'POSTED' AND  ITEMSTATUS!='P'");
			$DEFECTIVEQTY	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_DTL AS D LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO","SUM(DEFECTIVEQTY)","SKUNO= '{$ITEMNO}' AND STATUS = 'POSTED' AND  ITEMSTATUS!='P'");
			$IB_QTY			=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_DTL AS D LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO","SUM(IB_QTY)","SKUNO= '{$ITEMNO}' AND STATUS = 'POSTED' AND  ITEMSTATUS!='P'");
			$POSTEDRTNMTOQTY=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTNDTL AS D LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.MTONO = D.MTONO","SUM(QTY)","SKUNO= '{$ITEMNO}' AND H.POSTDATE != '0000-00-00'");
			$POSTEDMTOQTY	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTN_EXCDTL AS D LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO","SUM(D.QTY)","D.SKUNO= '{$ITEMNO}' AND (H.STATUS = 'POSTED' OR H.STATUS = 'TRANSMITTED')");
			$ONHAND			=	$SCANNEDQTY - $DEFECTIVEQTY - $IB_QTY - $POSTEDMTOQTY - $POSTEDRTNMTOQTY;
			
			if($ONHAND > 0)
			{
				$table	.=		"<tr id='tr$cnt' class='trbody'>
									<td id='tdcurcnt$cnt' align='center'>$cnt</td>
									<td id='tditemno$cnt' align='center'>$ITEMNO</td>
									<td id='tddescription$cnt'>$ITEM_DESC</td>
									<td id='tddonhandqty$cnt' align='center'>$ONHAND</td>
									<td align='center'>
										<input type='text' id='txtqty$cnt' name='txtqty$cnt' size='5' class='txtqty centered' data-curcnt = '$cnt' value='$ONHAND'>
									</td>
									<td align='center'>
										$ITEMSTATUS
									</td>
									<td align='center'>
										<input type='checkbox' id='txtitemno$cnt' name='txtitemno$cnt' size='10' class='txtitemnos' data-curcnt = '$cnt' value='$ITEMNO' title='Selects item:$ITEMNO.'>
									</td>
								</tr>";
			$cnt++;
			}
			$RSGETEXITEMS->MoveNext();
		}
	}
	$table	.=	"</table>
				<input type='hidden' name='hidcnt' id='hidcnt' value='$cnt'>
			</form>";
	echo $table;
	exit();
	
}
if($action == "SAVETRXHDR")
{
	$TODAY				=	date("Y-m-d");
	$time				=	date("H:i:s A");
	$rdodestination_C 	= 	$_POST["rdodestination_C"];
	
	$Filstar_conn->StartTrans();
	$TXNO	=	newTRXno($Filstar_conn);
	$SAVEMTOTRX	=	"INSERT INTO WMS_NEW.MTO_RTN_EXCHDR(`MTONO`, `STATUS`, `DESTINATION`, `ADDBY`, `ADDDATE`, `ADDTIME`)
					 VALUES('{$TXNO}','SAVED','{$rdodestination_C}','{$_SESSION['username']}','{$TODAY}','{$time}')";
	$RSSAVEMTOTRX = $Filstar_conn->Execute($SAVEMTOTRX);
	if($RSSAVEMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		echo "$TXNO";
	}
	$Filstar_conn->CompleteTrans();
	exit();
}
if($action == "SAVETRXDTLS")
{
	$Filstar_conn->StartTrans();
		$TRXNO			=	$_GET["TRXNO"];
		$txtitemno		=	$_GET["txtitemno"];
		$txtqty			=	$_GET["txtqty"];
		$txtnoboxes		=	$_GET["txtnoboxes"];
		$txtnopackages	=	$_GET["txtnopackages"];
		$txtboxlabel	=	$_GET["txtboxlabel"];
		
		$GETDESC	= addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$txtitemno}'"));
		$ITEMTYPE 	= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$txtitemno}'");
		$UNITPRICE 	= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
		$GROSSAMOUNT= $txtqty * $UNITPRICE;
		if($txtitemno != "" and $txtitemno != undefined and $txtqty != "" and $txtqty != undefined)
		{
			$SAVEMTODTLS	=	"INSERT INTO WMS_NEW.MTO_RTN_EXCDTL(`MTONO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`,`NO_OF_BOXES`, `NO_OF_PACK`,`BOXLABEL`, `UNITPRICE`, `GROSSAMT`)
								 VALUES('{$TRXNO}','{$txtitemno}','{$GETDESC}','{$ITEMTYPE}','{$txtqty}','{$txtnoboxes}','{$txtnopackages}','{$txtboxlabel}','{$UNITPRICE}','{$GROSSAMOUNT}')";
			$RSSAVEMTODTLS	=	$Filstar_conn->Execute($SAVEMTODTLS);
			if($RSSAVEMTODTLS == false)
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
			}
			else 
			{
				echo "";
			}
		}
	$Filstar_conn->CompleteTrans();
	exit();
}
if($action == "GETMTO")
{
	$txtmtono 			= $_POST["txtmtono"];
	$selstatus 			= $_POST["selstatus"];
	$seldtype 			= $_POST["seldtype"];
	$mtodfrom 			= $_POST["mtodfrom"];
	$mtodto 			= $_POST["mtodto"];
	$rdodestination		= $_POST["rdodestination"];
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
	if($rdodestination != "")
	{
		$rdodestination_Q	=	" AND DESTINATION = '{$rdodestination}'";
	}
	
	if($usesessionqry == "YES")
	{
		$GETMTO	=	$_SESSION["MAINQRY"];
	}
	else 
	{
		$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_RTN_EXCHDR
					 WHERE 1 $txtmtono_Q $selstatus_Q $DATE_Q $rdodestination_Q";
	}
	$RSGETMTO	=	$Filstar_conn->Execute($GETMTO);
	$_SESSION["MAINQRY"]	=	$GETMTO;
	if($RSGETMTO == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$PCWORKER</td>
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
	$TRX_NO	=	$_GET["TRXNO"];
	$COUNT	=	$_GET["COUNT"];
	$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_RTN_EXCDTL WHERE MTONO = '{$TRX_NO}' GROUP BY SKUNO";
	$RSGETDTLS	=	$Filstar_conn->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
				 		<td >Box Label</td>
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
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-count='$cnt'>$BOXLABEL</td>
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
			 		<td >$TOTALBOXES</td>
			 		<td >$TOTALPACK</td>
			 		<td >&nbsp;</td>
		 	 </tr>
		</table><br>";
	}
	exit();
}
if($action == "EDITTRX")
{
	$TRXNO			=	$_GET["TRXNO"];
	$NO_BOXES		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTN_EXCDTL","NO_OF_BOXES","MTONO = '{$TRXNO}'");
	$NO_PACKAGES	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTN_EXCDTL","NO_OF_PACK","MTONO = '{$TRXNO}'");
	
	$GETTRXDTLS		=	"SELECT * FROM  WMS_NEW.MTO_RTN_EXCDTL WHERE MTONO = '{$TRXNO}'";
	$RSGETTRXDTLS	=	$Filstar_conn->Execute($GETTRXDTLS);
	if($RSGETTRXDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
									DESTINATION:
									<label for='rdoraw_C'><input type='radio' id='rdoraw_C' name='rdodestination_C' value='RAW' checked>RAW</label>
								</td>
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
								<td width='7%' align='center'>Qty</td>
								<td width='7%' align='center'>Action</td>
							</tr>";
		$cnt = 1;
		while (!$RSGETTRXDTLS->EOF) {
			$SKUNO	 		= 	$RSGETTRXDTLS->fields["SKUNO"]; 
			$DESCRIPTION	= 	$RSGETTRXDTLS->fields["DESCRIPTION"]; 
			$QTY			= 	$RSGETTRXDTLS->fields["QTY"]; 
			$NO_OF_BOXES	= 	$RSGETTRXDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK		= 	$RSGETTRXDTLS->fields["NO_OF_PACK"]; 
			$BOXLABEL		= 	$RSGETTRXDTLS->fields["BOXLABEL"]; 
			
			$SCANNEDQTY		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_DTL AS D LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO","SUM(POSTEDQTY)","SKUNO= '{$SKUNO}' AND STATUS = 'POSTED' AND  ITEMSTATUS!='P'");
			$DEFECTIVEQTY	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_DTL AS D LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO","SUM(DEFECTIVEQTY)","SKUNO= '{$SKUNO}' AND STATUS = 'POSTED' AND  ITEMSTATUS!='P'");
			$IB_QTY			=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_DTL AS D LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO","SUM(IB_QTY)","SKUNO= '{$SKUNO}' AND STATUS = 'POSTED' AND  ITEMSTATUS!='P'");
			$POSTEDRTNMTOQTY=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTNDTL AS D LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.MTONO = D.MTONO","SUM(QTY)","SKUNO= '{$SKUNO}' AND H.POSTDATE != '0000-00-00'");
			$POSTEDMTOQTY	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTN_EXCDTL AS D LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO","SUM(D.QTY)","D.SKUNO= '{$SKUNO}' AND (H.STATUS = 'POSTED' OR H.STATUS = 'TRANSMITTED')");
			$ONHAND			=	$SCANNEDQTY - $DEFECTIVEQTY - $IB_QTY - $POSTEDMTOQTY - $POSTEDRTNMTOQTY;
			$table	.=		"<tr id='tr$cnt' class='trbody'>
								<td id='tdcurcnt$cnt' align='center'>$cnt</td>
								<td id='tditemno$cnt' align='center'>$SKUNO</td>
								<td id='tddescription$cnt'>$DESCRIPTION</td>
								<td id='tddonhandqty$cnt' align='center'>$ONHAND</td>
								<td align='center'>
									<input type='text' id='txtqty$cnt' name='txtqty$cnt' size='5' class='txtqty centered' data-curcnt = '$cnt' value='$QTY'>
								</td>
								<td align='center'>
									<input type='checkbox' id='txtitemno$cnt' name='txtitemno$cnt' size='10' class='txtitemnos' data-curcnt = '$cnt' value='$SKUNO' title='Selects item:$SKUNO.' checked>
								</td>
							</tr>";
			$cnt++;
			$RSGETTRXDTLS->MoveNext();
		}
		$table	.=	"</table>
					<input type='hidden' name='hidcnt' id='hidcnt' value='$cnt'>
				</form>";
		echo $table;
		$DESTINATION	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTN_EXCHDR","DESTINATION","MTONO= '{$TRXNO}'");
		echo "<script>
				$('input:radio[value=$DESTINATION]').attr('checked',true);
		 	  </script>";
	}
	exit();
}
if($action == "UPDATETRX")
{
	$TRX_NO	=	$_GET["TRXNO"];
	$cnt	=	$_POST["hidcnt"];
	$txtnoboxes		=	$_POST["txtboxes"];
	$txtnopackages	=	$_POST["txtpackages"];
	$rdodestination_C = $_POST["rdodestination_C"];
	$TODAY	=	date("Y-m-d");
	$time	=	date("H:i:s A");
	$Filstar_conn->StartTrans();
	$DELTRXDTLS	=	"DELETE FROM WMS_NEW. MTO_RTN_EXCDTL WHERE MTONO= '{$TRX_NO}'";
	$RSDELTRXDTLS	=	$Filstar_conn->Execute($DELTRXDTLS);
	if($RSDELTRXDTLS == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	$UPDATEMTOTRX	=	"UPDATE  WMS_NEW.MTO_RTN_EXCHDR SET `STATUS` = 'UPDATED',DESTINATION='{$rdodestination_C}',EDITBY = '{$_SESSION['username']}', `EDITDATE` ='{$TODAY}',EDITTIME='{$time}'
						 WHERE MTONO = '{$TRX_NO}'";
	$RSUPDATEMTOTRX	= 	$Filstar_conn->Execute($UPDATEMTOTRX);
	if($RSUPDATEMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		for($a = 1; $a < $cnt; $a++)
		{
			$txtitemno		=	$_POST["txtitemno$a"];
			$txtqty			=	$_POST["txtqty$a"];
			
			$GETDESC	= addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$txtitemno}'"));
			$ITEMTYPE 	= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$txtitemno}'");
			$UNITPRICE 	= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$txtitemno}'");
			$GROSSAMOUNT= $txtqty * $UNITPRICE;
			
			if($txtitemno != "" and $txtitemno != undefined)
			{
				$SAVEMTODTLS	=	"INSERT INTO WMS_NEW.MTO_RTN_EXCDTL(`MTONO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`,`NO_OF_BOXES`, `NO_OF_PACK`,`BOXLABEL`, `UNITPRICE`, `GROSSAMT`)
									 VALUES('{$TRX_NO}','{$txtitemno}','{$GETDESC}','{$ITEMTYPE}','{$txtqty}','{$txtnoboxes}','{$txtnopackages}','{$txtboxlabel}','{$UNITPRICE}','{$GROSSAMOUNT}')";
				$RSSAVEMTODTLS	=	$Filstar_conn->Execute($SAVEMTODTLS);
				if($RSSAVEMTODTLS == false)
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
				}
			}
		}
	}
	$Filstar_conn->CompleteTrans();
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
	
	$Filstar_conn->StartTrans();
	$POSTMTOTRX	=		"UPDATE WMS_NEW.MTO_RTN_EXCHDR SET `STATUS` = 'POSTED', `POSTBY` = '{$_SESSION['username']}', `POSTDATE` = '$TODAY',POSTTIME = '{$time}'
						 WHERE MTONO = '{$TRXNO}'";
	$RSPOSTMTOTRX	= 	$Filstar_conn->Execute($POSTMTOTRX);
	if($RSPOSTMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		mtosave_to_10($Filstar_conn,$TRXNO);
	}
	$Filstar_conn->CompleteTrans();
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
	
	$Filstar_conn->StartTrans();
	$CANCELMTOTRX	=		"UPDATE WMS_NEW.MTO_RTN_EXCHDR SET `STATUS` = 'CANCELLED', `CANCELLEDBY` = '{$_SESSION['username']}', `CANCELLEDDT` = '$TODAY'
							 WHERE MTONO = '{$TRXNO}'";
	$RSCANCELMTOTRX	= 	$Filstar_conn->Execute($CANCELMTOTRX);
	if($RSCANCELMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	$Filstar_conn->CompleteTrans();
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
	
	$Filstar_conn->StartTrans();
	$TRANSMITTRX	=	"UPDATE WMS_NEW.MTO_RTN_EXCHDR SET `STATUS` = 'TRANSMITTED', `TRANSMITTED_BY` = '{$_SESSION['username']}', `TRANSMITTED_DT` = '$TODAY'
						 WHERE MTONO = '{$TRXNO}'";
	$RSTRANSMITTRX	= 	$Filstar_conn->Execute($TRANSMITTRX);
	if($RSTRANSMITTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		$insertToRaw	=	"INSERT INTO WMS_NEW.MTO_RAWHDR(`MTONO`,`STATUS`,`DESTINATION`,`MTO_TRANSMITTED_DT`)
							 VALUES('$TRXNO','','FILLING BIN','$TODAY')";
		$RSinsertToRaw	=	$Filstar_conn->Execute($insertToRaw);
		if($RSinsertToRaw == false)
		{
			echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
		}
		else 
		{
			$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_RTN_EXCDTL WHERE MTONO = '{$TRXNO}'";
			$RSGETDTLS	=	$Filstar_conn->Execute($GETDTLS);
			if($RSGETDTLS == false)
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
					
					$insertToRawDtls	=	"INSERT INTO WMS_NEW.MTO_RAWDTL(`MTONO`, `MPOSNO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`, `NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`)
											 VALUES('$TRXNO','','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$QTY','$NO_OF_BOXES','$NO_OF_PACK','$BOXLABEL','$UNITPRICE')";
					$RSinsertToRawDtls	=	$Filstar_conn->Execute($insertToRawDtls);
					if($RSinsertToRawDtls == false)
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
					}
				$RSGETDTLS->MoveNext();
				}
			}
		}
	}
	$Filstar_conn->CompleteTrans();
	echo "<script>
				alert('Transaction $TRXNO has been successfully transmitted.');
				$('#btnreport').trigger('click',['YES']);
				$('#divtrxmto').dialog('close');
				resettrx();
		  </script>";
	exit();
}
function mtosave_to_10($Filstar_conn,$MTONUM)
{
	$Filstar_conn->StartTrans();
	$getmtohdr		=	"SELECT * FROM WMS_NEW.MTO_RTN_EXCHDR WHERE MTONO = '$MTONUM'";
	$RSgetmtohdr	=	$Filstar_conn->Execute($getmtohdr);
	if($RSgetmtohdr == false)
	{
		echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
		exit();
	}
	else
	{
		while (!$RSgetmtohdr->EOF)
		{
			$MTONO 			= $RSgetmtohdr->fields["MTONO"]; 
			$DESTINATION 	= "FDC"; 
			$ADDBY 			= $RSgetmtohdr->fields["ADDBY"]; 
			$ADDDATE 		= $RSgetmtohdr->fields["ADDDATE"]; 
			$SRC			= "RTN";
			
			$INSERTMTO_10	=	"INSERT INTO  FDCRMSlive.mtoheader(`mhmtnum`, `mhfrhse`, `mhtohse`, `mhcrtby`, `mhcrtdt`)
								 VALUES('$MTONO','$SRC','$DESTINATION','$ADDBY','$ADDDATE')";
			$RSINSERTMTO_10	=	$Filstar_conn->Execute($INSERTMTO_10);
			if($RSINSERTMTO_10 == false)
			{
				echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
				exit();
			}
			else
			{
				
				$getmtodtls		=	"SELECT MTONO,SKUNO,DESCRIPTION,SUM(QTY) AS QTY,UNITPRICE,SUM(GROSSAMT) AS GROSSAMT 
									 FROM  WMS_NEW.MTO_RTN_EXCDTL 
									 WHERE MTONO = '$MTONO'
									 GROUP BY SKUNO";
				$RSgetmtodtls	=	$Filstar_conn->Execute($getmtodtls);
				if($RSgetmtodtls == false)
				{
					echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
					exit();
				}
				else
				{
					while (!$RSgetmtodtls->EOF)
					{
						$MTONO 			= $RSgetmtodtls->fields["MTONO"]; 
						$SKUNO 			= $RSgetmtodtls->fields["SKUNO"]; 
						$DESCRIPTION 	= addslashes($RSgetmtodtls->fields["DESCRIPTION"]); 
						$QTY 			= $RSgetmtodtls->fields["QTY"]; 
						$UNITPRICE 		= $RSgetmtodtls->fields["UNITPRICE"]; 
						$GROSSAMT		= $RSgetmtodtls->fields["GROSSAMT"]; 
						
						$INSERTMTOdtls_10	=	"INSERT INTO  FDCRMSlive.mtodetail(`mdmtnum`, `mditmno`, `mditmds`, `mdwhscd`, `mduntpr`, `mdgramt`, `mdrcvqt`, `mdwhsqt`)
												 VALUES('$MTONO','$SKUNO','$DESCRIPTION','$DESTINATION','$UNITPRICE','$GROSSAMT','$QTY','$QTY')";
						$RSINSERTMTOdtls_10	=	$Filstar_conn->Execute($INSERTMTOdtls_10);
						if($RSINSERTMTOdtls_10 == false)
						{
							echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
							exit();
						}
					$RSgetmtodtls->MoveNext();
					}
				}
			}
		$RSgetmtohdr->MoveNext();
		}
	}
	$Filstar_conn->CompleteTrans();
}
function  newTRXno($dbconn)
{
	$forTRXno		=	"SELECT	MTONO,ADDDATE FROM  WMS_NEW.MTO_RTN_EXCHDR order by LINE_NO";
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
			$newTRXno	=	"XRTN-".date('Ymd').'-'."00".$newdgt;
		}
		if($lnt	==	2)
		{
			$newTRXno	=	"XRTN-".date('Ymd').'-'."0".$newdgt;
		}
		if($lnt	==	3)
		{
			$newTRXno	=	"XRTN-".date('Ymd').'-'.$newdgt;
		}
	 }
	 else 
	 {
	 	$newTRXno	=	"XRTN-".date('Ymd').'-'."001";
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
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/ex_mto/ex_mto.html");
?>
