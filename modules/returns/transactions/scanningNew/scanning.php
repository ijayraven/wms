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
$user 		=	$_SESSION['username'];
$day 		=	date("Y-m-d");
$time		=	date("h:i:s");
$action		=	$_GET['action'];
$tmptable	=	"tmpSCANDATA_DTL{$_SESSION['username_id']}";
if ($action=='Q_SEARCHCUST') 
{
	$custno		=	addslashes($_GET['CUSTNO']);
	$custname	=	addslashes($_GET['CUSTNAME']);
	$sel	 =	"SELECT CustNo,CustName FROM  FDCRMSlive.custmast WHERE 1";
	
	if(!empty($custno))
	{
	$sel	.=	" AND CustNo like '%{$custno}%' ";
	}
	if(!empty($custname))
	{
	$sel	.=	" AND CustName like '%{$custname}%' ";
	}
	$sel	.=	" limit 20 ";
	$rssel	=	$conn_255_10->Execute($sel);
	if ($rssel == false) 
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"MPOS SCANNING","Q_SEARCHCUST");
		$DATASOURCE->displayError();
	}
	$cnt	=	$rssel->RecordCount();
	if ($cnt > 0) 
	{
		echo "<select id='selcust' class = 'C_dropdown divsel' style='width:532px;height:auto;' onkeypress='scanningFuns.smartsel(event);' multiple>";
		while (!$rssel->EOF) 
		{
			$q_custno	=	$rssel->fields['CustNo'];
			$Q_custname	=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['CustName']);
			$cValue		=	$q_custno."|".$Q_custname;
			echo "<option value=\"$cValue\" onclick=\"scanningFuns.smartsel('click');\">$q_custno-$Q_custname</option>";
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
if($action == "GETMPOS")
{
	$MAINQUERY	=	$_GET["MAINQUERY"];
	$txtmposno	=	$_POST["txtmposno"];
	$mposdfrom	=	$_POST["mposdfrom"];
	$mposdto	=	$_POST["mposdto"];
	$scandfrom	=	$_POST["scandfrom"];
	$scandto	=	$_POST["scandto"];
	$pickdfrom	=	$_POST["pickdfrom"];
	$pickdto	=	$_POST["pickdto"];
	$selstatus	=	$_POST["selstatus"];
	$txtcustno	=	$_POST["txtcustno"];
	
	$pageno		=	$_GET['pageno'];
	$limit		=	10;
	
	if ($txtmposno != ""){
		$txtmposno_Q	=	" AND H.MPOSNO = '{$txtmposno}'";
	}
	if ($txtcustno != "") {
		$txtcustno_Q	=	" AND H.CUSTNO = '{$txtcustno}'";
	}
	if ($mposdfrom != "") {
		$mposdate_Q		=	" AND H.MPOSDATE BETWEEN '{$mposdfrom}' AND '{$mposdto}'";
	}
	if ($pickdfrom != "") {
		$pickdate_Q		=	" AND H.PICKEDDT BETWEEN '{$pickdfrom}' AND '{$pickdto}'";
	}
	if ($scandfrom != "") {
		$scandate_Q		=	" AND SH.SCANDATE BETWEEN '{$scandfrom}' AND '{$scandto}'";
	}
	if($selstatus != "")
	{
		if($selstatus == "NOT")
		{
			$selstatus_Q	=	" AND H.STATUS = ''";
		}
		else 
		{
			$selstatus_Q	=	" AND SH.STATUS = '{$selstatus}'";
		}
	}
	if($MAINQUERY == undefined)
	{
		$GETMPOS	=	"SELECT H.TRANSNO,H.MPOSNO,H.CUSTNO,H.MPOSDATE,H.SALESREPNO,H.PICK,H.STATUS,H.RECEIVEDDATE, SH.SCANDATE,SH.SCANNEDBY,SH.STATUS AS SCANSTATUS
						 FROM WMS_NEW.`MPOSHDR` AS H 
						 LEFT JOIN WMS_NEW.SCANDATA_HDR AS SH ON SH.MPOSNO = H.MPOSNO
						 WHERE 1 AND H.RECEIVEDMPOS = 'Y'
						 $txtmposno_Q $txtcustno_Q $mposdate_Q $scandate_Q $pickdate_Q $selstatus_Q
						 GROUP BY H.TRANSNO 
						 ORDER BY SH.SCANDATE DESC";
	}else {
		$GETMPOS	=	$_SESSION["MAINQUERY"];
	}
	$RSGETMPOS	=	$conn_255_10->Execute($GETMPOS);
	$_SESSION["MAINQUERY"]	=	$GETMPOS;
	if($RSGETMPOS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOS,$_SESSION['username'],"MPOS SCANNING","GETMPOS");
		$DATASOURCE->displayError();
	}
	else 
	{
		if($RSGETMPOS->RecordCount() == 0)
		{
			echo getTBLprev();exit();
		}
		echo "
			<div class='dropdown'>
				  <button onclick='return false;' class='dropbtn'>Show/hide columns<span>&nbsp;<img src='/wms/images/action_icon/new/arrowdown.png' class='arrowdown'></span></button>
				  <div class='dropdown-content'>
				    <input type='checkbox' id='chk1' class='chkcol' value='2' checked><label for='chk1'>Customer</label><br>	
				    <input type='checkbox' id='chk2' class='chkcol' value='3' checked><label for='chk2'>MPOS No.</label><br>	
					<input type='checkbox' id='chk3' class='chkcol' value='4' checked><label for='chk3'>SR</label><br>	
					<input type='checkbox' id='chk4' class='chkcol' value='5' checked><label for='chk4'>MPOS Date</label><br>	
					<input type='checkbox' id='chk5' class='chkcol' value='6' checked><label for='chk5'>Scanned Date</label><br>	
					<input type='checkbox' id='chk6' class='chkcol' value='7' checked><label for='chk6'>Scanned By</label><br>	
					<input type='checkbox' id='chk7' class='chkcol' value='8' checked><label for='chk7'>Status</label><br>	
					<input type='checkbox' id='chk8' class='chkcol' value='9' checked><label for='chk8'>Posted Date</label><br>	
					<input type='checkbox' id='chk9' class='chkcol' value='10' checked><label for='chk9'>Transmitted Date</label><br>	
					<input type='checkbox' id='chk10' class='chkcol' value='11'><label for='chk10'>Received Date</label><br>	
					<input type='checkbox' id='chk11' class='chkcol' value='12'><label for='chk11'>Age</label><br>	
				  </div>
			 </div>
			<br><br>
			<table class='tblresult' border='1' id='tblmtolist'>
					<tr class='trheader'>
				 		<td>No.</td>
				 		<td width='25%'>Customer</td>
				 		<td>MPOS No.</td>
				 		<td>SR</td>
				 		<td>MPOS Date</td>	
				 		<td>Scanned Date</td>	
				 		<td>Scanned By</td>	
				 		<td>Status</td>	
				 		<td>Posted<br>Date</td>	
				 		<td>Transmitted<br>Date</td>	
				 		<td>Received<br>Date</td>	
				 		<td>Age<br>(Days)</td>	
				 		<td>Action</td>	
				 	</tr>";
		$cnt=	1;
		while (!$RSGETMPOS->EOF) {
			$CUSTNO		=	$RSGETMPOS->fields["CUSTNO"];
			$PICK		=	$RSGETMPOS->fields["PICK"];
			$STATUS		=	$RSGETMPOS->fields["STATUS"];
			$SCANSTATUS	=	$RSGETMPOS->fields["SCANSTATUS"];
			$RECEIVEDDATE=	$RSGETMPOS->fields["RECEIVEDDATE"];
			if($STATUS == "")
			{
				$STATUS	=	"NOT YET SCANNED";
			}
			else 
			{
				$STATUS = $SCANSTATUS;
			}
			$CUSTNAME	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}'");
			$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];
			$MPOSDATE	=	$RSGETMPOS->fields["MPOSDATE"];
			$SCANDATE	=	$RSGETMPOS->fields["SCANDATE"];
			$SAVEDBY	=	$RSGETMPOS->fields["SCANNEDBY"];
			$SALESREPNO	=	$RSGETMPOS->fields["SALESREPNO"];
			$SRNAME		=	ucwords(strtolower($DATASOURCE->selval($conn_255_10,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$SALESREPNO}'")));
			
			if($STATUS == "NOT YET SCANNED")
			{
				$scan = "<img src='/wms/images/action_icon/scan.png' data-mposno='$MPOSNO'class='scanitems' width='20' height='20' style='border: 1px solid blue;cursor:pointer;' title='SCAN ITEMS'>";
			}
			else 
			{
				$scan = "";
			}
			if ($STATUS == "SAVED" OR $STATUS == "UPDATED")
			{
				$edit 	= 	"<img src='/wms/images/images/action_icon/new/compose.png' 	data-mposno='$MPOSNO' class='editdtls' width='20' height='20' style='cursor:pointer;' title='EDIT SCANNED ITEMS'>";
				$post 	=	"<img src='/wms/images/images/action_icon/new/mail.png' 	data-mposno='$MPOSNO' class='postmpos' width='20' height='20' style='cursor:pointer;' title='POST SCANNED MPOS'>";
				$delete =	"<img src='/wms/images/images/action_icon/new/stop.png' 	data-mposno='$MPOSNO' class='deletempos' width='20' height='20' style='cursor:pointer;' title='CANCEL SCANNED MPOS'>";
			}
			else 
			{
				$post = "";
				$edit = "";
				$delete = "";
			}
			$TRANSMITBY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_HDR","TRANSMITBY","MPOSNO= '{$MPOSNO}'"); 
			$TRANSMITDT		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_HDR","TRANSMITDATE","MPOSNO= '{$MPOSNO}'"); 
			$POSTEDDATE		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_HDR","POSTEDDATE","MPOSNO= '{$MPOSNO}'"); 
			if($TRANSMITBY != "")
			{
				$STATUS = "TRANSMITTED";
			}
			if($STATUS == "NOT YET SCANNED")
			{
				$AGE		=	number_format((abs(strtotime(date('Y-m-d H:i:s')) - strtotime($RECEIVEDDATE)))/86400,2);
			}
			else 
			{
				$AGE		=	"";
			}
			echo "<tr class='trdtls trbody' id='trdtls$cnt' title='Click to view details'>
					 		<td align='center' 	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$cnt</td>
					 		<td 				class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$CUSTNO-$CUSTNAME</td>
					 		<td align='center' 	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$MPOSNO</td>
					 		<td					class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$SALESREPNO-$SRNAME</td>	
					 		<td align='center' 	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$MPOSDATE</td>	
					 		<td align='center' 	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$SCANDATE</td>
					 		<td align='center'	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$SAVEDBY</td>
					 		<td align='center' 	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$STATUS</td>
					 		<td align='center' 	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$POSTEDDATE</td>
					 		<td align='center' 	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$TRANSMITDT</td>
					 		<td align='center' 	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$RECEIVEDDATE</td>
					 		<td align='center' 	class='tdmposdtls' data-mposno='$MPOSNO' data-count='$cnt'>$AGE</td>
					 		<td align='center'>
					 			$delete $scan $edit $post 
					 		</td>
					 </tr>
					 <tr>
					 		<td id='tdmposdtls$cnt' colspan='13' class='tdmposdtlsClass trbody' align='center'></td>
					 </tr>";
			$cnt++;
			$RSGETMPOS->Movenext();
		}
	}
	$currpage	=	$pageno + 1;
	exit();
}
if($action == "VIEWMPOSDTLS")
{
	$MPOSNO	=	$_GET["MPOSNO"];
	$COUNT	=	$_GET["COUNT"];
	echo "<br><table border='1' class='tblresul-tbltdtls'>
				<tr class='tblresul-tbltdtls-hdr'>
			 		<td>No.</td>
			 		<td>SKU No.</td>
			 		<td>SKU Description</td>
			 		<td>MPOS<br>Status</td>
			 		<td>MPOS<br>Qty</td>
			 		<td>MPOS<br>Amount</td>
			 		<td>Received<br>Status</td>
			 		<td>Received<br>Qty</td>
			 		<td>Received<br>Amount</td>
			 		<td>Good<br>Qty</td>
			 		<td>Defective<br>Qty</td>
			 		<td>Internal<br>Barcode Qty</td>
			 	</tr>";
	$STATUS	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","STATUS","MPOSNO = '$MPOSNO'");
	if($STATUS == "")
	{
		echo getMPOSDTLStable($MPOSNO,$conn_255_10);
		exit();
	}
	$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.MPOSDTL WHERE MPOSNO = '{$MPOSNO}'";
	$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
	if($RSGETMPOSDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"MPOS SCANNING","VIEWMPOSDTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		$cnt				=	1;
		$totqty				=	0;
		$totamt				=	0;
		$totsqty			=	0;
		$totsamt			=	0;
		$totdefectiveamt 	= 	0;
		$totgoodamt 		= 	0;
		$totibamt 			= 	0;
		while (!$RSGETMPOSDTLS->EOF) {
			$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
			$SKUNODESC	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
			$QTY		=	$RSGETMPOSDTLS->fields["QTY"];
			$UNITPRICE	=	$RSGETMPOSDTLS->fields["UNITPRICE"];
			$GROSSAMOUNT=	$RSGETMPOSDTLS->fields["GROSSAMOUNT"];
			$MPOSSTATUS	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSDTL","ITEMTYPE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
			
			$SCANNEDS		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","SCANDATA_DTL","ITEMSTATUS","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}' AND STATUS != 'DELETED'");
			$DEFECTIVEQTY	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","SCANDATA_DTL","DEFECTIVEQTY","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}' AND STATUS != 'DELETED'");
			$GOODQTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","SCANDATA_DTL","GOODQTY","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}' AND STATUS != 'DELETED'");
			$IB_QTY			=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","SCANDATA_DTL","IB_QTY","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}' AND STATUS != 'DELETED'");
			$SCANNEDQTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","SCANDATA_DTL","SCANNEDQTY","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}' AND STATUS != 'DELETED'");
			
			if($UNITPRICE == '')
			{
				$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
			}
			$RECAMT		=	$SCANNEDQTY * $UNITPRICE;
			echo "<tr class='tblresul-tbltdtls-dtls'>
				 		<td align='center'>
				 			$cnt
						</td>
				 		<td align='center'>$SKUNO</td>
				 		<td>$SKUNODESC</td>
				 		<td align='center'>$MPOSSTATUS</td>
				 		<td align='center'>".number_format($QTY)."</td>
				 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
				 		<td align='center'>$SCANNEDS</td>
				 		<td align='center'>".number_format($SCANNEDQTY)."</td>
				 		<td align='right'>".number_format($RECAMT,2)."</td>
				 		<td align='center'>".number_format($GOODQTY)."</td>
				 		<td align='center'>".number_format($DEFECTIVEQTY)."</td>
				 		<td align='center'>".number_format($IB_QTY)."</td>
				 	</tr>";
			$cnt++;
			$totqty				+=	$QTY;
			$totamt				+=	$GROSSAMOUNT;
			$totsqty			+=	$SCANNEDQTY;
			$totsamt			+=	$RECAMT;
			$totdefectiveamt 	+= 	$DEFECTIVEQTY;
			$totgoodamt		 	+= 	$GOODQTY;
			$totibamt 			+= 	$IB_QTY;
			$RSGETMPOSDTLS->MoveNext();
		}
		$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.SCANDATA_DTL WHERE MPOSNO = '{$MPOSNO}' AND STATUS != 'DELETED' AND DELBY = '' AND ADDTL = 'Y'";
		$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
		if($RSGETMPOSDTLS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"MPOS SCANNING","VIEWMPOSDTLS");
			$DATASOURCE->displayError();
		}
		else 
		{
			while (!$RSGETMPOSDTLS->EOF) {
				$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
				$SKUNODESC	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
				$SCANNEDS		=	$RSGETMPOSDTLS->fields["ITEMSTATUS"];
				$UPDATEQTY		=	$RSGETMPOSDTLS->fields["UPDATEQTY"];
				$DEFECTIVEQTY	=	$RSGETMPOSDTLS->fields["DEFECTIVEQTY"];
				$GOODQTY		=	$RSGETMPOSDTLS->fields["GOODQTY"];
				$IB_QTY			=	$RSGETMPOSDTLS->fields["IB_QTY"];
				$SCANNEDQTY		=	$RSGETMPOSDTLS->fields["SCANNEDQTY"];
				$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
				$RECAMT		=	$SCANNEDQTY * $UNITPRICE;
				echo "<tr class='tblresul-tbltdtls-dtls'>
					 		<td align='center'>
					 			$cnt
							</td>
					 		<td align='center'>$SKUNO</td>
					 		<td>$SKUNODESC</td>
					 		<td align='center'></td>
					 		<td align='center'>0</td>
					 		<td align='right'>0.00</td>
					 		<td align='center'>$SCANNEDS</td>
					 		<td align='center'>".number_format($SCANNEDQTY)."</td>
					 		<td align='right'>".number_format($RECAMT,2)."</td>
					 		<td align='center'>".number_format($GOODQTY)."</td>
					 		<td align='center'>".number_format($DEFECTIVEQTY)."</td>
					 		<td align='center'>".number_format($IB_QTY)."</td>
					 	</tr>";
				$cnt++;
				$totsqty			+=	$SCANNEDQTY;
				$totsamt			+=	$RECAMT;
				$totdefectiveamt 	+= 	$DEFECTIVEQTY;
				$totgoodamt		 	+= 	$GOODQTY;
				$totibamt 			+= 	$IB_QTY;
			$RSGETMPOSDTLS->MoveNext();
			}
		}
		echo "<tr class='tblresul-tbltdtls-dtls bld'>
				<td colspan='4' align='center'>Total</td>
				<td align='center'>".number_format($totqty)."</td>
				<td align='right'>".number_format($totamt,2)."</td>
				<td align='center'>&nbsp;</td>
				<td align='center'>".number_format($totsqty)."</td>
				<td align='right'>".number_format($totsamt,2)."</td>
				<td align='center'>".number_format($totgoodamt)."</td>
				<td align='center'>".number_format($totdefectiveamt)."</td>
				<td align='center'>".number_format($totibamt)."</td>
			  </tr>
			</table><br>";
		}
	exit();
}
if($action == "SCANNING")
{
	$MPOSNO	=	$_GET["MPOSNO"];
	
	$MPOSEXISTS	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_HDR","MPOSNO","MPOSNO = '$MPOSNO'");
	if(!createTmpTale($conn_255_10,$tmptable))
	{
		echo "<script>alert('Please try again.');</script>";
		exit();
	}
	else 
	{
		truncateTmpTbl($conn_255_10,$tmptable);
	}
	if($MPOSEXISTS == "")
	{
		$conn_255_10->StartTrans();
		$CUSTCODE 		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
		$INSERTSCANHDR	=	"INSERT INTO WMS_NEW.SCANDATA_HDR(`MPOSNO`, `CUSTNO`,`SCANNEDBY`, `SCANDATE`, `SCANTIME`)
							 VALUES('{$MPOSNO}','{$CUSTCODE}','{$user}','{$day}','{$time}')";
		$RSINSERTSCANHDR=	execQUERYhere("wms",$conn_255_10,$INSERTSCANHDR,$user,"MPOS SCANNING","SCANNING");
		
		$UPDATEMPOSHDR	=	"UPDATE WMS_NEW.MPOSHDR SET STATUS = 'SCANNED' WHERE MPOSNO = '{$MPOSNO}'";
		$RSUPDATEMPOSHDR=	execQUERYhere("wms",$conn_255_10,$UPDATEMPOSHDR,$user,"MPOS SCANNING","SCANNING");
		$conn_255_10->CompleteTrans();
		if($RSINSERTSCANHDR == false or $RSUPDATEMPOSHDR == false)
		{
			echo "<script>alert('Please try again.');</script>";
			exit();
		}
	}
	
	$MPOSDATE	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","MPOSDATE","MPOSNO= '{$MPOSNO}'");
	$GROSSAMOUNT=	number_format($DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","GROSSAMOUNT","MPOSNO= '{$MPOSNO}'"),2);
	$TOTALQTY	=	number_format($DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","TOTALQTY","MPOSNO= '{$MPOSNO}'"));
	$CUSTNO		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
	$CUSTNAME	=	addslashes($DATASOURCE->selval($conn_255_10,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}'"));
	$SRNO		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","SALESREPNO","MPOSNO= '{$MPOSNO}'");
	$SRNAME		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$SRNO}'");
	
	echo "<script>
				$('#tdmposno').text(':$MPOSNO');
				$('#tdcustomer').text(':$CUSTNO-$CUSTNAME');
				$('#tdsr').text(':$SRNO-$SRNAME');
				$('#tdmposnodt').text(':$MPOSDATE');
				$('#tdtotqty').text(':$TOTALQTY');
				$('#tdtotamount').text(':$GROSSAMOUNT');
	 	  </script>";
	$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.MPOSDTL WHERE MPOSNO = '{$MPOSNO}'";
	$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
	if($RSGETMPOSDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"MPOS SCANNING","SCANNING");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<table border='1' id='tblscanning'>
					<tr class='trheader'>
				 		<td>No.</td>
				 		<td>SKU No.</td>
				 		<td>SKU Description</td>
				 		<td>MPOS<br>Status</td>
				 		<td>MPOS<br>Qty</td>
				 		<td>MPOS<br>Amount</td>
				 		<td>Received<br>Status</td>
				 		<td>Received<br>Qty</td>
				 		<td>Received<br>Amount</td>
				 		<td>Good<br>Qty</td>
				 		<td>Defective<br>Qty</td>
				 		<td>Internal<br>Barcode Qty</td>
				 	</tr>";
		$cnt	=	1;
		$totqty	=	0;
		$totamt	=	0;
		$totscannedqty	=	0;
		$totscannedamt	=	0;
		$totdefectiveamt=	0;
		$totgoodamt		=	0;
		$totibamt		=	0;
		$addeditemcnt	=	0;
		while (!$RSGETMPOSDTLS->EOF) {
			$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
			$SKUNODESC	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo= '{$SKUNO}'");
			$UNITPRICE	=	$RSGETMPOSDTLS->fields["UNITPRICE"];
			$QTY		=	$RSGETMPOSDTLS->fields["QTY"];
			$GROSSAMOUNT=	$QTY*$UNITPRICE;
			$MPOSSTATUS	=	$RSGETMPOSDTLS->fields["ITEMTYPE"];
			$SCANSTATUS	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$SKUNO}'");
			
			$GOODQTY	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","GOODQTY","SKUNO= '{$SKUNO}' AND MPOSNO = '$MPOSNO' AND STATUS != 'DELETED'");
			$DEFECTIVEQTY=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","DEFECTIVEQTY","SKUNO= '{$SKUNO}' AND MPOSNO = '$MPOSNO' AND STATUS != 'DELETED'");
			$IB_QTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","IB_QTY","SKUNO= '{$SKUNO}' AND MPOSNO = '$MPOSNO' AND STATUS != 'DELETED'");
			$ADDTL		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","ADDTL","SKUNO= '{$SKUNO}' AND MPOSNO = '$MPOSNO' AND STATUS != 'DELETED'");
			$STATUS		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","STATUS","SKUNO= '{$SKUNO}' AND MPOSNO = '$MPOSNO' AND STATUS != 'DELETED'");
			$SCANNEDQTY	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","SCANNEDQTY","SKUNO= '{$SKUNO}' AND MPOSNO = '$MPOSNO' AND STATUS != 'DELETED'");
			$SCANNEDS	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","ITEMSTATUS","SKUNO= '{$SKUNO}' AND MPOSNO = '$MPOSNO' AND STATUS != 'DELETED'");
			if($UNITPRICE == '')
			{
				$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
			}
			$SCANNEDAMT	=	$SCANNEDQTY * $UNITPRICE;	
			echo "<tr  id='trfound$cnt' class='trbody' data-unitprice='$UNITPRICE' data-currcnt='$cnt'>
			 		<td align='center'>
			 			$cnt
						<input type='hidden' id='hiditemno$cnt' name='hiditemno$cnt' value='$SKUNO' data-adddtl='N'>
						<input type='hidden' id='txtrecqty$cnt' name='txtrecqty$cnt' size='5' class='txtinputqty numbersonly centered' data-curcnt='$cnt' value='$SCANNEDQTY'>
						<input type='hidden' id='hidsrp$cnt'  name='hidsrp$cnt' value='$UNITPRICE'>
					</td>
			 		<td  align='center' id='tditemno$cnt'>$SKUNO</td>
			 		<td>$SKUNODESC</td>
			 		<td align='center'>$MPOSSTATUS</td>
			 		<td align='center'>".number_format($QTY)."</td>
			 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
			 		<td align='center' id='tdcurrstatus$cnt' data=status='$SCANSTATUS'>$SCANSTATUS</td>
			 		<td align='center'id='tdrecqty$cnt'>$SCANNEDQTY</td>
			 		<td id='tdrecamt$cnt' align='right'>".number_format($SCANNEDAMT,2)."</td>
			 		<td align='center'>
			 			<input type='text' id='txtgoodqty$cnt' name='txtgoodqty$cnt' size='5' class='txtgoodqty numbersonly centered' data-curcnt='$cnt' value='$GOODQTY'>
			 		</td>
			 		<td align='center'>
			 			<input type='text' id='txtdefqty$cnt' name='txtdefqty$cnt' size='5' class='txtdefqty numbersonly centered' data-curcnt='$cnt' value='$DEFECTIVEQTY'>
			 		</td>
			 		<td align='center'>
			 			<input type='text' id='txtibqty$cnt' name='txtibqty$cnt' size='5' class='txtibqty numbersonly centered' data-curcnt='$cnt' value='$IB_QTY'>
			 		</td>
			 	</tr>";
			$cnt++;
			$newconuter		=	$cnt;
			$newconuter--;
				
			$totqty			+=	$QTY;
			$totamt			+=	$GROSSAMOUNT;
			$totscannedqty	+=	$SCANNEDQTY;
			$totscannedamt	+=	$SCANNEDAMT;
			$totdefectiveamt+=	$DEFECTIVEQTY;
			$totgoodamt		+=	$GOODQTY;
			$totibamt		+=	$IB_QTY;
			$RSGETMPOSDTLS->MoveNext();
		}
		$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.SCANDATA_DTL WHERE MPOSNO = '{$MPOSNO}'  AND STATUS != 'DELETED' AND DELBY = '' AND ADDTL = 'Y'";
		$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
		if($RSGETMPOSDTLS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"MPOS SCANNING","SCANNING");
			$DATASOURCE->displayError();
		}
		else 
		{	
			while (!$RSGETMPOSDTLS->EOF) {
				$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
				$SKUNODESC	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo= '{$SKUNO}'");
				$SCANNEDS	=	$RSGETMPOSDTLS->fields["ITEMSTATUS"];
				$QTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSDTL","QTY","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSDTL","UNITPRICE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$GROSSAMOUNT=	$QTY*$UNITPRICE;
				$MPOSSTATUS	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSDTL","ITEMTYPE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$QTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSDTL","QTY","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$SCANNEDQTY	=	$RSGETMPOSDTLS->fields["SCANNEDQTY"];
				$SCANSTATUS	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$SKUNO}'");
				$GOODQTY	=	$RSGETMPOSDTLS->fields["GOODQTY"];
				$DEFECTIVEQTY=	$RSGETMPOSDTLS->fields["DEFECTIVEQTY"];
				$IB_QTY		=	$RSGETMPOSDTLS->fields["IB_QTY"];
				$ADDTL		=	$RSGETMPOSDTLS->fields["ADDTL"];
				$STATUS		=	$RSGETMPOSDTLS->fields["STATUS"];
				
				if($UNITPRICE == '')
				{
					$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
				}	
				$SCANNEDAMT	=	$SCANNEDQTY * $UNITPRICE;	
				$newconuter++;
				$addeditemcnt++;
				echo "<tr id = 'trfound$newconuter' class='trbody trmposdtlsadded' style='font-size:12px;' data-currcnt='$newconuter' title='Double click to delete item from MPOS'>
				    	<td align='center'>
				    		$newconuter
							<input type='hidden' id='txtrecqty$newconuter' name='txtrecqty$newconuter' size='5' class='txtaddedinputqty txtinputqty centered' data-curcnt='$newconuter' value='$SCANNEDQTY'>
							<input type='hidden' id='hiditemno$newconuter' name='hiditemno$newconuter' size='8' class='addeditem centered' data-curcnt='$newconuter' data-adddtl='Y' value='$SKUNO' readonly>
							<input type='hidden' id='hidsrp$newconuter'  name='hidsrp$newconuter' value='$UNITPRICE'>
						</td>
				    	<td align='center'id='tditemno$newconuter'>$SKUNO</td>
				    	<td id='tditemdesc$newconuter'>$SKUNODESC</td>
				    	<td id='tditemstatus'></td>
				    	<td id='tditemqty'></td>
				    	<td id='tditemgross'></td>
				    	<td id='tdcurrstatus$newconuter' align='center'>$SCANSTATUS</td>
				    	<td align='center' id='tdrecqty$newconuter'>$SCANNEDQTY</td>
				    	<td id='tdrecamt$newconuter' align='right'>".number_format($SCANNEDAMT,2)."</td>
				    	<td align='center'>
				    		<input type='text' id='txtgoodqty$newconuter' name='txtgoodqty$newconuter' size='5' class='txtaddedinputqty txtgoodqty centered' data-curcnt='$newconuter' value='$GOODQTY'>
				    	</td>
				    	<td align='center'>
				    		<input type='text' id='txtdefqty$newconuter' name='txtdefqty$newconuter' size='5' class='txtaddedinputqty txtdefqty centered' data-curcnt='$newconuter' value='$DEFECTIVEQTY'>
				    	</td>
				    	<td align='center'>
				    		<input type='text' id='txtibqty$newconuter' name='txtibqty$newconuter' size='5' class='txtaddedinputqty txtibqty centered' data-curcnt='$newconuter' value='$IB_QTY'>
				    	</td>
					</tr>";
			$totqty			+=	$QTY;
			$totamt			+=	$GROSSAMOUNT;
			$totscannedqty	+=	$SCANNEDQTY;
			$totscannedamt	+=	$SCANNEDAMT;
			$totdefectiveamt+=	$DEFECTIVEQTY;
			$totgoodamt		+=	$GOODQTY;
			$totibamt		+=	$IB_QTY;
			$RSGETMPOSDTLS->MoveNext();
			}
		echo "</table>
				<br>
				<input type='hidden' id='txtaboverowcnt' name='txtaboverowcnt' value='$cnt'>
				<input type='hidden' id='txtaddeditemscnt' name='txtaddeditemscnt' value='$addeditemcnt'>
				<br><br>";
		echo "<table border='0' width='100%' class='tbl-scanning-summ'>
				<tr class='tbl-scanning-summ-hdr'>
					<td>MPOS QUANTITY</td>
					<td>MPOS AMOUNT</td>
					<td>RECEIVED QUANTITY</td>
					<td>RECEIVED AMOUNT</td>
					<td>GOOD QUANTITY</td>
					<td>DEFECTIVE QUANTITY</td>
					<td>INTERNAL BARCODE QUANTITY</td>
				</tr>
				<tr class='tbl-scanning-summ-dtl bld' id='trtotcnt' data-cnt='$cnt'>
					<td align='center'>".number_format($totqty)."</td>
					<td align='right'>".number_format($totamt,2)."</td>
					<td align='center' id='tdrecqty'>".number_format($totscannedqty)."</td>
					<td align='right' id='tdrecamt'>".number_format($totscannedamt,2)."</td>
					<td align='center' id='tdtotgoodqty'>$totgoodamt</td>
					<td align='center' id='tdtotdefqty'>$totdefectiveamt</td>
					<td align='center' id='tdtotibqty'>$totibamt</td>
				  </tr>
			</table>";
		}
		$FOUNDIBCUST	=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","IB_CUSTOMERS","COUNT(*)","STATUS = 'ACTIVE' AND CODE = '$CUSTNO'");
		if($FOUNDIBCUST > 0)
		{
			echo "<script>
					$('#txtib').removeAttr('disabled');
					$('#txtibqty').removeAttr('disabled');
					$('.txtibqty').removeAttr('disabled');
				  </script>";
		}
		else 
		{
			echo "<script>
					$('#txtib').attr('disabled','disabled');
					$('#txtibqty').attr('disabled','disabled');
					$('.txtibqty').attr('disabled','disabled');
				  </script>";
		}
	}
	exit();
}
if($action == "GETITEM")
{
	$BARCODE_TXTITEMNO	=	$_GET["BARCODE_TXTITEMNO"];
	$scanmode			=	$_GET["scanmode"];
	if($BARCODE_TXTITEMNO != "")
	{
		$ITEMNO		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemNo","BarCode = '$BARCODE_TXTITEMNO' OR ItemNo = '$BARCODE_TXTITEMNO'");
		if($ITEMNO != "")
		{
			$ITEMDESC	=	addslashes($DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$ITEMNO}'"));
			$ITEMSTATUS	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo = '{$ITEMNO}'");
			$SELLPRICE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$ITEMNO}'");
			echo "$ITEMNO,$ITEMDESC,$ITEMSTATUS,$SELLPRICE";
		}
		else 
		{
			echo "";
		}
	}
	exit();
}
if($action == "SAVEITEM")
{
	$itemno			=	$_GET["itemno"];
	$qty			=	$_GET["qty"];
	$destination_val=	$_GET["destination_val"];
	$MPOSNO			=	substr($_GET["MPOSNO"],1);
	
	$conn_255_10->StartTrans();
	$SCANNEDQTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","$tmptable","SCANNEDQTY","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	if($SCANNEDQTY == "")
	{
		$SCANNEDQTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","SCANNEDQTY","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	}
	$GOODQTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","$tmptable","GOODQTY","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	if($GOODQTY == "")
	{
		$GOODQTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","GOODQTY","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	}
	$DEFECTIVEQTY	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","$tmptable","DEFECTIVEQTY","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	if($DEFECTIVEQTY == "")
	{
		$DEFECTIVEQTY	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","DEFECTIVEQTY","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	}
	$IB_QTY			=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","$tmptable","IB_QTY","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	if($IB_QTY == "")
	{
		$IB_QTY			=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","IB_QTY","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	}
	$ITEMSTATUS		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$itemno}'");
	$ITEMDESC		=	addslashes($DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$itemno}'"));
	$ITEMSTATUS		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo = '{$itemno}'");
	$SELLPRICE		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$itemno}'");
	$CUSTCODE 		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
	$BARCODE		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","BarCode","ItemNo= '{$itemno}'");
	
	
	$MPOSITEM_FOUND_IN_MPOSDTL	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSDTL","SKUNO","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO'");
	if($MPOSITEM_FOUND_IN_MPOSDTL == "")
	{
		$ADDEDITEM="Y";
	}
	else 
	{
		$ADDEDITEM="N";
	}
	if($destination_val == "txtgoodqty")
	{
		$GOODQTY	=	$GOODQTY + $qty;
	}
	if($destination_val == "txtdefqty")
	{
		$DEFECTIVEQTY=	$DEFECTIVEQTY + $qty;
	}
	if($destination_val == "txtibqty")
	{
		$IB_QTY	=	$IB_QTY + $qty;
	}
	$NEW_SCANNEDQTY		=	$GOODQTY + $DEFECTIVEQTY + $IB_QTY;
	$MPOSITEM_FOUND_T	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","$tmptable","SKUNO","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO'");
	$MPOSITEM_FOUND_S	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","SKUNO","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	if($MPOSITEM_FOUND_S == "")
	{
		$save_fields	=	",`SAVEDBY`, `SAVEDDATE`, `SAVEDTIME`";
		$save_values	=	",'$user','$day','$time'";
	}
	else 
	{
		$update_status	=	",`STATUS`";
		$update_statval	=	",'UPDATED'";
		$update_fields	=	",`UPDATEDBY`, `UPDATEDDATE`, `UPDATEDTIME`";
		$update_values	=	",'$user', '$day', '$time'";
	}
	if($MPOSITEM_FOUND_T == "")
	{
		$CUSTCODE 	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
		$BARCODE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","BarCode","ItemNo= '{$itemno}'");
		
		$SAVEITEM	=	"INSERT INTO WMS_NEW.$tmptable(`MPOSNO`,`CUSTCODE`,`SKUNO`$update_status,`ITEMSTATUS`,`BARCODE`, `SCANNEDQTY`,`GOODQTY`, `DEFECTIVEQTY`, `IB_QTY`, `ADDTL`$save_fields $update_fields)
						 VALUES('$MPOSNO','$CUSTCODE','$itemno'$update_statval,'$ITEMSTATUS','$BARCODE','$NEW_SCANNEDQTY','$GOODQTY','$DEFECTIVEQTY','$IB_QTY','$ADDEDITEM' $save_values $update_values)";
	}
	else 
	{
		$SAVEITEM	=	"UPDATE WMS_NEW.$tmptable SET `STATUS`='UPDATED',`SCANNEDQTY`='$NEW_SCANNEDQTY',`GOODQTY`='$GOODQTY', `DEFECTIVEQTY`='$DEFECTIVEQTY', `IB_QTY`='$IB_QTY',
						`ITEMSTATUS`='$ITEMSTATUS',`UPDATEDBY`='$user', `UPDATEDDATE`='$day', `UPDATEDTIME`='$time'
						 WHERE `MPOSNO` = '$MPOSNO' AND `SKUNO` = '$itemno'";
	}
	$RSSAVEITEM		=	execQUERYhere("wms",$conn_255_10,$SAVEITEM,$user,"MPOS SCANNING","SAVEITEM");
	
	
	$conn_255_10->CompleteTrans();
	if($RSSAVEITEM)
	{
		echo "successful,$GOODQTY,$DEFECTIVEQTY,$IB_QTY,$NEW_SCANNEDQTY";
	}
	else 
	{
		echo "<script>alert('Please scan the item again.');</script>";
	}
	exit();
}
if($action == "SAVEITEM_REPLACEQTY")
{
	$MPOSNO		=	substr($_GET["MPOSNO"],1);
	$itemno		=	$_GET["hiditemno"];
	$txtrecqty	=	$_GET["txtrecqty"];
	$txtgoodqty	=	$_GET["txtgoodqty"];
	$txtdefqty	=	$_GET["txtdefqty"];
	$txtibqty	=	$_GET["txtibqty"];
	$adddtl		=	$_GET["adddtl"];
	$conn_255_10->StartTrans();
	$SCANSTATUS	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$itemno}'");

	
	$ITEMSTATUS		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$itemno}'");
	$ITEMDESC		=	addslashes($DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$itemno}'"));
	$ITEMSTATUS		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","DeptNo","ItemNo = '{$itemno}'");
	$SELLPRICE		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$itemno}'");
	$CUSTCODE 		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
	$BARCODE		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","BarCode","ItemNo= '{$itemno}'");
	
	$MPOSITEM_FOUND_T	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","$tmptable","SKUNO","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO'");
	$MPOSITEM_FOUND_S	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","SKUNO","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED'");
	$MPOSITEM_FOUND_IN_MPOSDTL	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSDTL","SKUNO","SKUNO= '{$itemno}' AND `MPOSNO` = '$MPOSNO'");
	if($MPOSITEM_FOUND_IN_MPOSDTL == "")
	{
		$ADDEDITEM="Y";
	}
	else 
	{
		$ADDEDITEM="N";
	}
	if($MPOSITEM_FOUND_S == "")
	{
		$save_fields	=	",`SAVEDBY`, `SAVEDDATE`, `SAVEDTIME`";
		$save_values	=	",'$user','$day','$time'";
	}
	else 
	{
		$update_fields	=	",`UPDATEDBY`, `UPDATEDDATE`, `UPDATEDTIME`";
		$update_values	=	",'$user', '$day', '$time'";
	}
	if($MPOSITEM_FOUND_T == "")
	{
		$CUSTCODE 	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
		$BARCODE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","BarCode","ItemNo= '{$itemno}'");
		
		$SAVEITEM	=	"INSERT INTO WMS_NEW.$tmptable(`MPOSNO`,`CUSTCODE`,`SKUNO`,`ITEMSTATUS`,`BARCODE`, `SCANNEDQTY`,`GOODQTY`, `DEFECTIVEQTY`, `IB_QTY`, `ADDTL`$save_fields $update_fields)
						 VALUES('$MPOSNO','$CUSTCODE','$itemno','$ITEMSTATUS','$BARCODE','$txtrecqty','$txtgoodqty','$txtdefqty','$txtibqty','$ADDEDITEM' $save_values $update_values)";
	}
	else 
	{
		$SAVEITEM	=	"UPDATE WMS_NEW.$tmptable SET `STATUS`='UPDATED',`SCANNEDQTY`='$txtrecqty',`GOODQTY`='$txtgoodqty', `DEFECTIVEQTY`='$txtdefqty', `IB_QTY`='$txtibqty',
						`ITEMSTATUS`='$ITEMSTATUS',`UPDATEDBY`='$user', `UPDATEDDATE`='$day', `UPDATEDTIME`='$time'
						 WHERE `MPOSNO` = '$MPOSNO' AND `SKUNO` = '$itemno'";
	}
	$RSSAVEITEM		=	execQUERYhere("wms",$conn_255_10,$SAVEITEM,$user,"MPOS SCANNING","SAVEITEM");
	
	$conn_255_10->CompleteTrans();
	if($RSSAVEITEM == false)
	{
		echo "<script>alert('Please try again.');</script>";exit();
	}
	exit();
}
if($action == "DELETEITEM")
{
	$MPOSNO		=	substr($_GET["MPOSNO"],1);
	$hiditemno	=	$_GET["hiditemno"];
	$currcnt	=	$_GET["currcnt"];
	$DELETEITEM		=	"UPDATE WMS_NEW.SCANDATA_DTL SET `STATUS`='DELETED',`DELBY`='$user', `DELDATE`='$day', `DELTIME`='$time'
						 WHERE `MPOSNO` = '$MPOSNO' AND SKUNO = '$hiditemno' AND STATUS != 'DELETED'";
	$RSDELETEITEM	=	execQUERYhere("wms",$conn_255_10,$DELETEITEM,$user,"MPOS SCANNING","DELETEITEM");
	$DELTMPITEM		=	"DELETE FROM WMS_NEW.$tmptable WHERE MPOSNO = '$MPOSNO' AND SKUNO = '$hiditemno'";
	$RSDELTMPITEM	=	execQUERYhere("wms",$conn_255_10,$DELTMPITEM,$user,"MPOS SCANNING","DELETEITEM");
	if($RSDELETEITEM and $RSDELTMPITEM)
	{
		echo "<script>
				$('#trfound$currcnt').remove();
				scanningFuns.recompute();
			  </script>";
	}
	else 
	{
		echo "<script>alert('Please try again.');</script>";exit();
	}
	exit();
}
if($action == "POSTSCANNING")
{
	$MPOSNO		=	$_GET["MPOSNO"];
	$BY 		=	$_SESSION['username'];
	$DATE 		=	date("Y-m-d");
	$TIME		=	date("h:i:s");
	$conn_255_10->StartTrans();
	$GETSCANNED	=	"SELECT `MPOSNO`,`CUSTCODE`, `SKUNO`, `SCANNEDQTY`,`POSTEDQTY`, `UPDATEQTY`,`STATUS` FROM WMS_NEW.SCANDATA_DTL
					 WHERE MPOSNO = '{$MPOSNO}' AND STATUS != 'DELETED' AND DELBY = ''";
	$RSGETSCANNED	=	$conn_255_10->Execute($GETSCANNED);
	if($RSGETSCANNED == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETSCANNED,$_SESSION['username'],"MPOS SCANNING","POSTSCANNING");
		$DATASOURCE->displayError();
	}
	else 
	{
		while (!$RSGETSCANNED->EOF) {
			$MPOSNO		=	$RSGETSCANNED->fields["MPOSNO"];
			$SKUNO		=	$RSGETSCANNED->fields["SKUNO"];
			$SCANNEDQTY	=	$RSGETSCANNED->fields["SCANNEDQTY"];
			$UPDATEQTY	=	$RSGETSCANNED->fields["UPDATEQTY"];
			$CUSTCODE	=	$RSGETSCANNED->fields["CUSTCODE"];
			$STATUS		=	$RSGETSCANNED->fields["STATUS"];
			$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSDTL","UNITPRICE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
			$PRICECLASS	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSDTL","PRICECLASS","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
			$F_QTY			=	$SCANNEDQTY; 
			if($UNITPRICE == "")
			{
				$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
			}
			if($PRICECLASS == "")
			{
				$PRICECLASS	=	$DATASOURCE->selval($conn_250_171,"FDC_PMS","ITEMMASTER","PRICECLASS","ITEMNO = '{$SKUNO}'");
			}
			$CustPriceBook	=	$DATASOURCE->selval($conn_255_10," FDCRMSlive ","custmast","CustPriceBook","CustNo = '{$CUSTCODE}'");
			$custdiscount	=	$DATASOURCE->selval($conn_255_10," FDCRMSlive ","custdiscount","Discount","PriceBook = '{$CustPriceBook}' AND PriceClass = '{$PRICECLASS}'");
			$TOTALQTY	+=	$F_QTY;
			$TOTALAMT	+=	($F_QTY * $UNITPRICE);
			$TOTALNETAMT+=	($F_QTY * ($UNITPRICE-($UNITPRICE*($custdiscount/100))));
			$POSTMPOS	=	"UPDATE  WMS_NEW.SCANDATA_DTL SET `STATUS`='POSTED', POSTEDQTY = '{$F_QTY}'
							 WHERE `MPOSNO` = '{$MPOSNO}' AND SKUNO = '{$SKUNO}' AND `STATUS` != 'DELETED' AND DELBY = ''";
			$RSPOSTMPOSMPOS	=	execQUERYhere("wms",$conn_255_10,$POSTMPOS,$user,"MPOS SCANNING","POSTSCANNING");
			$RSGETSCANNED->MoveNext();
		}
	}
	$POSTSCANHDR	=	"UPDATE  WMS_NEW.SCANDATA_HDR SET STATUS='POSTED',`POSTEDNETAMOUNT`='{$TOTALNETAMT}', `POSTEDGROSSAMOUNT`='{$TOTALAMT}', `POSTEDBY`='{$user}',
						`POSTEDDATE`='{$day}', `POSTEDTIME`='{$time}' WHERE MPOSNO = '{$MPOSNO}'";
	$RSPOSTSCANHDR	=	execQUERYhere("wms",$conn_255_10,$POSTSCANHDR,$user,"MPOS SCANNING","POSTSCANNING");
	
	$POSTMPOSHDR	=	"UPDATE  WMS_NEW.MPOSHDR SET STATUS='POSTED' WHERE MPOSNO = '{$MPOSNO}'";
	$RSPOSTMPOSHDR	=	execQUERYhere("wms",$conn_255_10,$POSTMPOSHDR,$user,"MPOS SCANNING","POSTSCANNING");
	$conn_255_10->CompleteTrans();
	if($RSPOSTMPOSMPOS and $RSPOSTSCANHDR and $RSPOSTMPOSHDR)
	{
		echo "<script>
				MessageType.successMsg('MPOS: $MPOSNO has been successfully posted.');
				$('#btnsearch').trigger('click',['mainquery','']);
			  </script>";
	}
	else 
	{
		echo "<script>alert('Please try again.');</script>";exit();
	}
	
	exit();
}
if($action == "DELSCANNING")
{
	$MPOSNO	=	$_GET["MPOSNO"];
	$conn_255_10->StartTrans();
	$DELMPOS	=	"UPDATE  WMS_NEW.SCANDATA_HDR SET `STATUS`='CANCELLED', `CANCELLEDBY`='{$user}', `CANCELLEDDT`= NOW()
					 WHERE `MPOSNO` = '{$MPOSNO}'";
	$RSDELMPOS	=	execQUERYhere("wms",$conn_255_10,$DELMPOS,$user,"MPOS SCANNING","DELSCANNING");
	$conn_255_10->CompleteTrans();
	if($RSDELMPOS) 
	{
		echo "<script>
				MessageType.successMsg('MPOS: $MPOSNO has been successfully cancelled.');
				$('#btnsearch').trigger('click',['mainquery','']);
			  </script>";
	}
	else 
	{
		echo "<script>alert('Please try again.');</script>";exit();
	}
	
	exit();
}
if($action == "TRUNCATE")
{
	truncateTmpTbl($conn_255_10,$tmptable);
	exit();
}
if($action == "SAVESCANNEDITEMS")
{
	$MPOSNO		=	substr($_GET["MPOSNO"],1);
	$mode		=	$_GET["mode"];
	
	$UPDATESCANDATADTL	=	"UPDATE WMS_NEW.SCANDATA_DTL AS S 
							 LEFT JOIN WMS_NEW.$tmptable AS T ON T.MPOSNO = S.MPOSNO AND T.SKUNO = S.SKUNO
							 SET S.`STATUS` = T.`STATUS`,S.`SCANNEDQTY` = T.`SCANNEDQTY`, S.`GOODQTY`= T.`GOODQTY`, S.`DEFECTIVEQTY`=T.`DEFECTIVEQTY`, S.`IB_QTY`=T.`IB_QTY`, 
							 S.`UPDATEDBY`=T.`UPDATEDBY`, S.`UPDATEDDATE`=T.`UPDATEDDATE`, S.`UPDATEDTIME`=T.`UPDATEDTIME`
							 WHERE S.MPOSNO = T.MPOSNO AND S.SKUNO = T.SKUNO AND S.`STATUS` != 'DELETED'";
	$RSUPDATESCANDATADTL	=	execQUERYhere("wms",$conn_255_10,$UPDATESCANDATADTL,$user,"MPOS SCANNING","SAVESCANNEDITEMS");
	if(!$RSUPDATESCANDATADTL)
	{
		echo "<script>alert('Please try again.');</script>";exit();
	}
	else 
	{
		$INSERTSCANDATADTL	=	"INSERT INTO WMS_NEW.SCANDATA_DTL(`MPOSNO`, `CUSTCODE`, `STATUS`, `SKUNO`, `ITEMSTATUS`, `BARCODE`, `SCANNEDQTY`, `GOODQTY`, 
								`DEFECTIVEQTY`, `IB_QTY`, `ADDTL`, `SAVEDBY`, `SAVEDDATE`, `SAVEDTIME`) 
								 	SELECT T.`MPOSNO`, T.`CUSTCODE`, T.`STATUS`, T.`SKUNO`, T.`ITEMSTATUS`, T.`BARCODE`, T.`SCANNEDQTY`, T.`GOODQTY`, 
									T.`DEFECTIVEQTY`, T.`IB_QTY`, T.`ADDTL`, T.`SAVEDBY`, T.`SAVEDDATE`, T.`SAVEDTIME` FROM WMS_NEW.$tmptable AS T
									WHERE T.`SKUNO` NOT IN(SELECT SKUNO FROM WMS_NEW.SCANDATA_DTL WHERE `MPOSNO` = '$MPOSNO' AND STATUS != 'DELETED')
								 ";
		$RSINSERTSCANDATADTL	=	execQUERYhere("wms",$conn_255_10,$INSERTSCANDATADTL,$user,"MPOS SCANNING","SAVESCANNEDITEMS");
		if(!$RSINSERTSCANDATADTL)
		{
			echo "<script>alert('Please try again.');</script>";exit();
		}
		else 
		{
			$UPDATESTATUS	=	"UPDATE WMS_NEW.SCANDATA_HDR SET `STATUS`='UPDATED' WHERE `MPOSNO` = '$MPOSNO'";
			$RSUPDATESTATUS	=	execQUERYhere("wms",$conn_255_10,$UPDATESTATUS,$user,"MPOS SCANNING","SAVEITEM");
			if($RSUPDATESTATUS)
			{
				if($mode == "Save")
				{
					echo "<script>
							MessageType.successMsg('MPOS: $MPOSNO has been successfully saved.');
							$('#btnsearch').trigger('click',['mainquery','']);
						  </script>";
				}
				else 
				{
					
					echo "<script>
							MessageType.successMsg('MPOS: $MPOSNO has been successfully updated.');
						  </script>";
				}
				echo "<script>
						$('#btnsearch').trigger('click',['mainquery','']);
						$('#divscanning').dialog('close');
						$('#divmposdtls').dialog('close');
					  </script>";
				truncateTmpTbl($conn_255_10,$tmptable);
			}
			else 
			{
				echo "<script>alert('Please try again.');</script>";exit();
			}
		}
	}

	exit();
}
function getMPOSDTLStable($MPOSNO,$conn_255_10)
{
	$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.MPOSDTL WHERE MPOSNO = '$MPOSNO'";
	$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
	if($RSGETMPOSDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"MPOS SCANNING","VIEWMPOSDTLS");
		$DATASOURCE->displayError();
	}
	else 
	{
		$cnt				=	1;
		$totqty				=	0;
		$totamt				=	0;
		$totsqty			=	0;
		$totsamt			=	0;
		
		while (!$RSGETMPOSDTLS->EOF) {
			$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
			$SKUNODESC	=	db_funcs::selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
			$QTY		=	$RSGETMPOSDTLS->fields["QTY"];
			$UNITPRICE	=	$RSGETMPOSDTLS->fields["UNITPRICE"];
			$GROSSAMOUNT=	$RSGETMPOSDTLS->fields["GROSSAMOUNT"];
			$MPOSSTATUS	=	$RSGETMPOSDTLS->fields["ITEMTYPE"];
			if($UNITPRICE == '')
			{
				$UNITPRICE	=	db_funcs::selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
			}
			$tbl.= "<tr class='tblresul-tbltdtls-dtls'>
				 		<td align='center'>
				 			$cnt
						</td>
				 		<td align='center'>$SKUNO</td>
				 		<td>$SKUNODESC</td>
				 		<td align='center'>$MPOSSTATUS</td>
				 		<td align='center'>".number_format($QTY)."</td>
				 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
				 		<td align='center'></td>
				 		<td align='center'>".number_format(0)."</td>
				 		<td align='right'>".number_format(0,2)."</td>
				 		<td align='center'>".number_format(0)."</td>
				 		<td align='center'>".number_format(0)."</td>
				 		<td align='center'>".number_format(0)."</td>
				 	</tr>";
			$cnt++;
			$totqty				+=	$QTY;
			$totamt				+=	$GROSSAMOUNT;
			$RSGETMPOSDTLS->MoveNext();
		}
		$tbl.= "<tr class='tblresul-tbltdtls-dtls bld'>
				<td colspan='4' align='center'>Total</td>
				<td align='center'>".number_format($totqty)."</td>
				<td align='right'>".number_format($totamt,2)."</td>
				<td align='center'>&nbsp;</td>
				<td align='center'>".number_format(0)."</td>
				<td align='right'>".number_format(0,2)."</td>
				<td align='center'>".number_format(0)."</td>
				<td align='center'>".number_format(0)."</td>
				<td align='center'>".number_format(0)."</td>
			  </tr>
			</table><br>";
		return $tbl;
	}
}
function getTBLprev()
{
	return "<table class='tblresult' border='1'>
				<tr class='trheader'>
			 		<td>No.</td>
			 		<td width='25%'>Customer</td>
			 		<td>MPOS No.</td>
			 		<td>SR</td>
			 		<td>MPOS Date</td>	
			 		<td>Scanned Date</td>	
			 		<td>Scanned By</td>	
			 		<td>Status</td>	
			 		<td>Posted<br>Date</td>	
			 		<td>Transmitted<br>Date</td>	
			 		<td>Actions</td>	
			 	</tr>
			 	<tr class='trbody centered fnt-red'>
			 		<td colspan='11'>Nothing to display.</td>
			 	</tr>
			 </table>";
}
function execQUERYhere($path,$conn, $query,$user,$module,$action)
{
	$RSEXEC_query	=	$conn->Execute($query);
	if ($RSEXEC_query == false) 
	{
		$errmsg	=	$conn->ErrorMsg()."::".__LINE__; 
		db_funcs::logError($path,$errmsg,$query,$user,$module,$action);
		return false;
		exit();
	}
	else 
	{
		db_funcs::logSuccess($module_folder,$query,$user);
		return $RSEXEC_query;
	}
	
}
function createTmpTale($conn,$tmptable)
{
	$CREATE	=	"CREATE TABLE IF NOT EXISTS WMS_NEW.`$tmptable` (
				  `RECID` int(11) NOT NULL AUTO_INCREMENT,
				  `MPOSNO` varchar(30) NOT NULL,
				  `CUSTCODE` varchar(30) NOT NULL,
				  `STATUS` enum('SAVED','UPDATED','POSTED','DELETED') NOT NULL,
				  `SKUNO` varchar(30) NOT NULL,
				  `ITEMSTATUS` varchar(30) NOT NULL,
				  `BARCODE` varchar(30) NOT NULL,
				  `SCANNEDQTY` int(6) NOT NULL,
				  `GOODQTY` int(11) NOT NULL,
				  `DEFECTIVEQTY` int(6) NOT NULL,
				  `IB_QTY` int(11) NOT NULL,
				  `ADDTL` enum('N','Y') NOT NULL,
				  `UPDATEQTY` int(6) NOT NULL,
				  `POSTEDQTY` int(6) NOT NULL,
				  `SAVEDBY` varchar(30) NOT NULL,
				  `SAVEDDATE` date NOT NULL,
				  `SAVEDTIME` time NOT NULL,
				  `UPDATEDBY` varchar(30) NOT NULL,
				  `UPDATEDDATE` date NOT NULL,
				  `UPDATEDTIME` time NOT NULL,
				  `DELBY` varchar(30) NOT NULL,
				  `DELDATE` date NOT NULL,
				  `DELTIME` time NOT NULL,
				  PRIMARY KEY (`RECID`)
				) ENGINE=InnoDB;";
	$RSCREATE	=	$conn->Execute($CREATE);
	if($RSCREATE == false)
	{
		$errmsg	=	$conn->ErrorMsg()."::".__LINE__; 
		db_funcs::logError($path,$errmsg,$query,$user,$module,$action);
		return false;
		exit();
	}
	else 
	{
		return true;
	}
}
function truncateTmpTbl($conn,$tmptable)
{
	$TRUNCATE	=	"TRUNCATE WMS_NEW.$tmptable";
	$RSTRUNCATE	=	$conn->Execute($TRUNCATE);
	if($RSTRUNCATE == false)
	{
		$conn->ErrorMsg()."::".__LINE__; 
		return false;
		exit();
	}
	else 
	{
		return  true;
	}
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/scanningNew/scanning.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/scanningNew/scanningUI.php");
?>