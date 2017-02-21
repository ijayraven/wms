<?php
session_start();
//include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");
if (empty($_SESSION['username'])) 
{
	echo "<script>
				MessageType.sessexpMsg();
		  </script>";
	exit();
}
$action	=	$_GET['action'];
if($action == "GETMPOS")
	{
		$txtmposno	=	$_POST["txtmposno"];
		$txtcustno	=	$_POST["txtcustno"];
		$dfrom		=	$_POST["dfrom"];
		$dto		=	$_POST["dto"];
		$seldtype	=	$_POST["seldtype"];
		$selreason	=	$_POST["selreason"];
		$selcusttype=	$_POST["selcusttype"];
		
		$_SESSION["mposdfrom"]	=	$mposdfrom;
		$_SESSION["mposdto"]	=	$mposdto;
		$_SESSION["posteddfrom"]=	$posteddfrom;
		$_SESSION["posteddto"]	=	$posteddto;
		
		if ($txtmposno != "") {
			$txtmposno_Q	=	" AND H.MPOSNO = '{$txtmposno}'";
			$STATUS			=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSHDR","STATUS","MPOSNO= '{$txtmposno}'");
			if($STATUS == "")
			{
				echo "<script>MessageType.infoMsg('MPOS does not exist or item/s is/are not yet scanned.');</script>";
				echo getTBLprev();
				exit();
			}
			elseif ($STATUS == "SCANNED")
			{
				echo "<script>MessageType.infoMsg('Scanned MPOS is not yet posted.');</script>";
				getTBLprev();
				exit();
			}
		}
		if($txtcustno != "")
		{
			$txtcustno_Q	=	" AND H.CUSTNO = '$txtcustno'";
		}
		if ($selreason != "ALL") {
			$selreason_Q	=	" AND H.REASON = '{$selreason}'";
		}
		if ($dfrom != "") {
			if($seldtype == "SCANDATE")
			{
				$seldtype	=	"S.SCANDATE";
			}
			$date_Q		=	" AND $seldtype BETWEEN '{$dfrom}' AND '{$dto}'";
		}
		if($selcusttype != "")
		{
			if($selcusttype == "NBS")
			{
				$selcusttype_Q	=	"  AND C.CustomerBranchCode != ''";
			}
			else 
			{
				$selcusttype_Q	=	"  AND C.CustomerBranchCode = ''";
			}
			$selcusttype_J	=	" LEFT JOIN FDCRMSlive.custmast AS C ON C.CustNo = H.CUSTNO";
		}
		$GETMPOS	=	"SELECT H.TRANSNO,H.MPOSNO,H.CUSTNO,H.MPOSDATE,H.REASON,H.TOTALQTY,H.GROSSAMOUNT,S.POSTEDDATE,S.SCANDATE,H.RECEIVEDDATE
						 FROM WMS_NEW.`MPOSHDR` AS H 
						 LEFT JOIN WMS_NEW.SCANDATA_HDR AS S ON S.MPOSNO = H.MPOSNO
						 $selcusttype_J
						 WHERE S.POSTEDBY!= '' AND (H.STATUS = 'POSTED' OR H.STATUS = 'TRANSMITTED')
						 $txtmposno_Q $txtcustno_Q $date_Q $selreason_Q $selcusttype_Q";
//		echo $GETMPOS; 
//		exit();
		$RSGETMPOS	=	$conn_255_10->Execute($GETMPOS);
		if($RSGETMPOS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMPOS,$_SESSION['username'],"VARIANCE REPORT","GETMPOS");
			$DATASOURCE->displayError();
		}
		else 
		{
			if($RSGETMPOS->RecordCount() == 0)
			{
				echo getTBLprev();exit();
			}
			echo "<table border='1' class='tblresult'>
						<tr class='trheader'>
					 		<td >No.</td>
					 		<td >Customer</td>
					 		<td >MPOS No.</td>
					 		<td >Rec. Date</td>	
					 		<td >MPOS Date</td>	
					 		<td >Scanned Date</td>	
					 		<td >Posted Date</td>	
					 		<td >Reason</td>	
					 		<td >MPOS Qty</td>	
					 		<td >Posted Qty</td>	
					 		<td >MPOS Amount</td>	
					 		<td >Posted Amount</td>	
					 		<td >Posted Net Amount</td>	
					 	</tr>";
			$cnt=	1;
			$arrVAR		=	array()	;
			while (!$RSGETMPOS->EOF) {
				$CUSTNO		=	$RSGETMPOS->fields["CUSTNO"];
				$CUSTNAME	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}'");
				$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];
				$MPOSDATE	=	$RSGETMPOS->fields["MPOSDATE"];
				$POSTEDDATE	=	$RSGETMPOS->fields["POSTEDDATE"];
				$SALESREPNO	=	$RSGETMPOS->fields["SALESREPNO"];
				$REASON		=	$RSGETMPOS->fields["REASON"];
				$TOTALQTY	=	$RSGETMPOS->fields["TOTALQTY"];
				$SCANDATE	=	$RSGETMPOS->fields["SCANDATE"];
				$RECEIVEDDATE	=	$RSGETMPOS->fields["RECEIVEDDATE"];
				$GROSSAMOUNT=	$RSGETMPOS->fields["GROSSAMOUNT"];
				$POSTEDQTY	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW"," SCANDATA_DTL","SUM(POSTEDQTY)","MPOSNO = '{$MPOSNO}'");
				$POSTEDAMT	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW"," SCANDATA_HDR","POSTEDGROSSAMOUNT","MPOSNO = '{$MPOSNO}'");
				$POSTEDNETAMT	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW"," SCANDATA_HDR","POSTEDNETAMOUNT","MPOSNO = '{$MPOSNO}'");
				
				$arrVAR[$MPOSNO]["CUSTNO"]		=	"$CUSTNO-$CUSTNAME";
				$arrVAR[$MPOSNO]["MPOSDATE"]	=	$MPOSDATE;
				$arrVAR[$MPOSNO]["POSTEDDATE"]	=	$POSTEDDATE;
				$arrVAR[$MPOSNO]["SCANDATE"]	=	$SCANDATE;
				$arrVAR[$MPOSNO]["RECEIVEDDATE"]	=	$RECEIVEDDATE;
				$arrVAR[$MPOSNO]["SALESREPNO"]	=	$SALESREPNO;
				$arrVAR[$MPOSNO]["REASON"]		=	$REASON;
				$arrVAR[$MPOSNO]["TOTALQTY"]	=	$TOTALQTY;
				$arrVAR[$MPOSNO]["GROSSAMOUNT"]	=	$GROSSAMOUNT;
				$arrVAR[$MPOSNO]["POSTEDQTY"]	=	$POSTEDQTY;
				$arrVAR[$MPOSNO]["POSTEDAMT"]	=	$POSTEDAMT;
				$arrVAR[$MPOSNO]["POSTEDNETAMT"]	=	$POSTEDNETAMT;
				
				echo "<tr class='trdtls trbody' id='trdtls$cnt' title='Click to view details' data-mposno='$MPOSNO' data-cnt='$cnt'>
					 		<td class='tdmposdtls' align='center'>$cnt</td>
					 		<td class='tdmposdtls'>$CUSTNO-$CUSTNAME</td>
					 		<td class='tdmposdtls' align='center'>$MPOSNO</td>
					 		<td class='tdmposdtls' align='center'>".date("Y-m-d",strtotime($RECEIVEDDATE))."</td>
					 		<td class='tdmposdtls' align='center'>$SCANDATE</td>	
					 		<td class='tdmposdtls' align='center'>$MPOSDATE</td>	
					 		<td class='tdmposdtls' align='center'>$POSTEDDATE</td>	
					 		<td class='tdmposdtls' align='center'>$REASON</td>	
					 		<td class='tdmposdtls' align='center'>".number_format($TOTALQTY)."</td>	
					 		<td class='tdmposdtls' align='center'>".number_format($POSTEDQTY)."</td>	
					 		<td class='tdmposdtls' align='right'>".number_format($GROSSAMOUNT,2)."</td>	
					 		<td class='tdmposdtls' align='right'>".number_format($POSTEDAMT,2)."</td>	
					 		<td class='tdmposdtls' align='right'>".number_format($POSTEDNETAMT,2)."</td>	
					 	</tr>
					 	<tr>
						 		<td id='tdmposdtls$cnt' colspan='13' class='tdmposdtlsClass trbody' align='center'></td>
						</tr>";
				$cnt++;
				$RSGETMPOS->Movenext();
			}
		}
		echo "</table>
			 <br>
			 <button type='button' id='btncsv' class='btncsv'>CSV</button>
			 <button type='button' id='btnpdf' class='btnpdf'>PDF</button>
			 ";
		$_SESSION["arrVAR"]	=	$arrVAR;
		exit();
	}
	if($action == "VIEWMPOSDTLS")
	{
		$MPOSNO	=	$_GET["MPOSNO"];
		$COUNT	=	$_GET["COUNT"];
		$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.MPOSDTL WHERE MPOSNO = '{$MPOSNO}'";
		$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
		if($RSGETMPOSDTLS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"VARIANCE REPORT","VIEWMPOSDTLS");
			$DATASOURCE->displayError();
		}
		else 
		{
			echo "<table border='1' class='tblresul-tbltdtls tablesorter'>
					<thead>
						<tr class='tblresul-tbltdtls-hdr'>
					 		<th >No.</th>
					 		<th >SKU No.</th>
					 		<th >SKU Description</th>
					 		<th >MPOS Qty</th>
					 		<th >Posted Qty</th>
					 		<th >MPOS Amount</th>
					 		<th >Posted Amount</th>
					 	</tr>
					 </thead>
					 <tbody>";
			$cnt	=	1;
			$totqty	=	0;
			$totamt	=	0;
			$totsqty	=	0;
			$totsamt	=	0;
			
			while (!$RSGETMPOSDTLS->EOF) 
			{
				$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
				$SKUNODESC	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
				$QTY		=	$RSGETMPOSDTLS->fields["QTY"];
				$UNITPRICE	=	$RSGETMPOSDTLS->fields["UNITPRICE"];
				if($UNITPRICE == '' OR $UNITPRICE == 0 )
				{
					$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
				}
				$GROSSAMOUNT=	$RSGETMPOSDTLS->fields["GROSSAMOUNT"];
				$F_QTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","SCANDATA_DTL","SCANNEDQTY","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$RECAMT		=	$F_QTY * $UNITPRICE;
				
				echo "<tr class='tblresul-tbltdtls-dtls'>
					 		<td align='center'>
					 			$cnt
							</td>
					 		<td align='center'>$SKUNO</td>
					 		<td>$SKUNODESC</td>
					 		<td align='center'>".number_format($QTY)."</td>
					 		<td align='center'>".number_format($F_QTY)."</td>
					 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
					 		<td align='right'>".number_format($RECAMT,2)."</td>
					 	</tr>";
				$cnt++;
				$totqty	+=	$QTY;
				$totamt	+=	$GROSSAMOUNT;
				$totsqty	+=	$F_QTY;
				$totsamt	+=	$RECAMT;
				
				$RSGETMPOSDTLS->MoveNext();
			}
			$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.SCANDATA_DTL WHERE MPOSNO = '{$MPOSNO}' AND STATUS != 'DELETED' AND DELBY = '' AND ADDTL = 'Y'";
			$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
			if($RSGETMPOSDTLS == false)
			{
				$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
				$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"VARIANCE REPORT","VIEWMPOSDTLS");
				$DATASOURCE->displayError();
			}
			else 
			{
				while (!$RSGETMPOSDTLS->EOF) 
				{
					$SKUNO			=	$RSGETMPOSDTLS->fields["SKUNO"];
					$SKUNODESC		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
					$F_QTY			=	$RSGETMPOSDTLS->fields["SCANNEDQTY"];
					if($UNITPRICE == '')
					{
						$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
					}
					$RECAMT		=	$F_QTY * $UNITPRICE;
					echo "<tr class='tblresul-tbltdtls-dtls'>
					 		<td align='center'>
					 			$cnt
							</td>
					 		<td align='center'>$SKUNO</td>
					 		<td>$SKUNODESC</td>
					 		<td align='center'>0</td>
					 		<td align='center'>".number_format($F_QTY)."</td>
					 		<td align='right'>0.00</td>
					 		<td align='right'>".number_format($RECAMT,2)."</td>
					 	</tr>";
					$cnt++;
					$totsqty	+=	$F_QTY;
					$totsamt	+=	$RECAMT;
				$RSGETMPOSDTLS->MoveNext();
				}
			echo "</tbody>
				<tr class='tblresul-tbltdtls-dtls bld'>
					<td colspan='3' align='center'>Total</td>
					<td align='center'>".number_format($totqty)."</td>
					<td align='center'>".number_format($totsqty)."</td>
					<td align='right'>".number_format($totamt,2)."</td>
					<td align='right'>".number_format($totsamt,2)."</td>
				  </tr>
				</table>";
			}
		}
		exit();
	}
	if ($action=='Q_SEARCHCUST') 
	{
		$custno		=	addslashes($_GET['CUSTNO']);
		$custname	=	addslashes($_GET['CUSTNAME']);
		$sel	 =	"SELECT CustNo,CustName FROM  FDCRMSlive.custmast WHERE 1";
		
		if (!empty($custno)) 
		{
		$sel	.=	" AND CustNo like '%{$custno}%' ";
		}
		if (!empty($custname)) 
		{
		$sel	.=	" AND CustName like '%{$custname}%' ";
		}
		$sel	.=	" limit 20 ";
//		echo "$sel"; exit();
		$rssel	=	$conn_255_10->Execute($sel);
		if ($rssel == false) 
		{
			echo $errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			exit();
		}
		$cnt	=	$rssel->RecordCount();
		if ($cnt > 0) 
		{
			echo "<select id='selcust' class = 'C_dropdown divsel' style='width:532px;height:auto;' onkeypress='smartsel(event);' multiple>";
			while (!$rssel->EOF) 
			{
				$q_custno	=	$rssel->fields['CustNo'];
				$Q_custname	=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['CustName']);
				$cValue		=	$q_custno."|".$Q_custname;
				echo "<option value=\"$cValue\" onclick=\"smartsel('click');\">$q_custno-$Q_custname</option>";
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
	function getTBLprev()
	{
		return "<table border='1'class='tblresult tablesorter'>
					<tr class='trheader'>
				 		<td >No.</td>
				 		<td >Customer</td>
				 		<td >MPOS No.</td>
				 		<td >MPOS Date</td>	
				 		<td >Posted Date</td>	
				 		<td >Reason</td>	
				 		<td >MPOS Qty</td>	
				 		<td >Posted Qty</td>	
				 		<td >MPOS Amount</td>	
				 		<td >Posted Amount</td>	
				 		<td >Posted Net Amount</td>	
				 	</tr>
			 		<tr class='trbody centered fnt-red'>
				 		<td colspan='11'>Nothing to display.</td>
				 	</tr>
				 </table>";
	}
include("variance.html")
?>
