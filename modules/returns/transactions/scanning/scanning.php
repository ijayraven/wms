<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}
$action	=	$_GET['action'];
$SAVEDBY_U		=	"";
$SAVEDDATE_U 		=	"";
$SAVEDTIME_U		=	"";	
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
		$rssel	=	$Filstar_conn->Execute($sel);
		if ($rssel == false) 
		{
			echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__); 
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
//			$ISPICKED	=	$global_func->Select_val($Filstar_conn,"WMS_NEW "," MPOSHDR","PICK","MPOSNO= '{$txtmposno}'");
//			if($ISPICKED == "N")
//			{
//				echo "<script>alert('MPOS is not yet picked.');</script>";
//				exit();
//			}
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
			elseif ($selstatus == "TRANSMITTED")
			{
				$selstatus_Q	=	" AND SH.TRANSMITBY != ''";
			}
			else 
			{
				$selstatus_Q	=	" AND S.STATUS = '{$selstatus}' AND SH.TRANSMITBY = ''";
			}
		}
//		if($MAINQUERY == undefined)
//		{
//			$GETMPOS	=	"SELECT H.TRANSNO,H.MPOSNO,H.CUSTNO,H.MPOSDATE,H.SALESREPNO,H.PICK,H.STATUS,H.RECEIVEDDATE, S.SAVEDDATE,S.STATUS AS SCANSTATUS
//							 FROM WMS_NEW.`MPOSHDR` AS H 
//							 LEFT JOIN WMS_NEW.SCANDATA_DTL AS S ON S.MPOSNO = H.MPOSNO
//							 LEFT JOIN WMS_NEW.SCANDATA_HDR AS SH ON SH.MPOSNO = H.MPOSNO
//							 WHERE 1 AND H.RECEIVEDMPOS = 'Y'
//							 $txtmposno_Q $txtcustno_Q $mposdate_Q $scandate_Q $pickdate_Q $selstatus_Q
//							 GROUP BY H.TRANSNO";
//		}else {
//			$GETMPOS	=	$_SESSION["MAINQUERY"];
//		}
//		$RSGETMPOS	=	$Filstar_conn->Execute($GETMPOS);
//		$totalcnt	=	$RSGETMPOS->RecordCount();
//		$totpagecnt	=	ceil($totalcnt/$limit);
//		$from		=	$limit * $pageno;	
	
		if($MAINQUERY == undefined)
		{
			$GETMPOS	=	"SELECT H.TRANSNO,H.MPOSNO,H.CUSTNO,H.MPOSDATE,H.SALESREPNO,H.PICK,H.STATUS,H.RECEIVEDDATE, SH.SCANDATE,SH.SCANNEDBY,S.STATUS AS SCANSTATUS
							 FROM WMS_NEW.`MPOSHDR` AS H 
							 LEFT JOIN WMS_NEW.SCANDATA_DTL AS S ON S.MPOSNO = H.MPOSNO
							 LEFT JOIN WMS_NEW.SCANDATA_HDR AS SH ON SH.MPOSNO = H.MPOSNO
							 WHERE 1 AND H.RECEIVEDMPOS = 'Y'
							 $txtmposno_Q $txtcustno_Q $mposdate_Q $scandate_Q $pickdate_Q $selstatus_Q
							 GROUP BY H.TRANSNO 
							 ORDER BY S.SAVEDDATE DESC";
		}else {
			$GETMPOS	=	$_SESSION["MAINQUERY"];
		}
		$RSGETMPOS	=	$Filstar_conn->Execute($GETMPOS);
		$_SESSION["MAINQUERY"]	=	$GETMPOS;
		if($RSGETMPOS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
				$CUSTNAME	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}'");
				$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];
				$MPOSDATE	=	$RSGETMPOS->fields["MPOSDATE"];
				$SCANDATE	=	$RSGETMPOS->fields["SCANDATE"];
				$SAVEDBY	=	$RSGETMPOS->fields["SCANNEDBY"];
				$SALESREPNO	=	$RSGETMPOS->fields["SALESREPNO"];
				$SRNAME		=	ucwords(strtolower($global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$SALESREPNO}'")));
				
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
					$delete =	"<img src='/wms/images/images/action_icon/new/stop.png' 	data-mposno='$MPOSNO' class='deletempos' width='20' height='20' style='cursor:pointer;' title='DELETE SCANNED MPOS'>";
				}
				else 
				{
					$post = "";
					$edit = "";
					$delete = "";
				}
				$TRANSMITBY		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_HDR","TRANSMITBY","MPOSNO= '{$MPOSNO}'"); 
				$TRANSMITDT		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_HDR","TRANSMITDATE","MPOSNO= '{$MPOSNO}'"); 
				$POSTEDDATE		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_HDR","POSTEDDATE","MPOSNO= '{$MPOSNO}'"); 
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
//		echo "<tr class='trbody'>
//					<td colspan='11' class='centered'>
//						<input type='button' value='<<'".($currpage == "1" ? "disabled" : "onclick='getmpos(0)'")." class='small_button'>
//						<input type='button' value='<'" .($currpage == "1" ? "disabled" : "onclick='getmpos(".($pageno-1).")'")." class='small_button'>
//							<a><b>$currpage/$totpagecnt</a>
//						<input type='button' value='>'".($currpage == $totpagecnt ? "disabled" : "onclick='getmpos(".($currpage).")'")." class='small_button'>
//						<input type='button' value='>>'" .($currpage == $totpagecnt ? "disabled" : "onclick='getmpos(".($totpagecnt-1).")'")." class='small_button'>
//					</td>
//				</tr>
//			</table>";
		exit();
	}
	if ($action == "SCANNING") {
		$MPOSNO		=	$_GET["MPOSNO"];
		$MPOSDATE	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","MPOSDATE","MPOSNO= '{$MPOSNO}'");
		$GROSSAMOUNT=	number_format($global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","GROSSAMOUNT","MPOSNO= '{$MPOSNO}'"),2);
		$TOTALQTY	=	number_format($global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","TOTALQTY","MPOSNO= '{$MPOSNO}'"));
		$CUSTNO		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
		$CUSTNAME	=	addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}'"));
		$SRNO		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","SALESREPNO","MPOSNO= '{$MPOSNO}'");
		$SRNAME		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$SRNO}'");
		
		echo "<script>
					$('#tdmposno').text(':$MPOSNO');
					$('#tdcustomer').text(':$CUSTNO-$CUSTNAME');
					$('#tdsr').text(':$SRNO-$SRNAME');
					$('#tdmposnodt').text(':$MPOSDATE');
					$('#tdtotqty').text(':$TOTALQTY');
					$('#tdtotamount').text(':$GROSSAMOUNT');
		 	  </script>";
		$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.MPOSDTL WHERE MPOSNO = '{$MPOSNO}'";
		$RSGETMPOSDTLS	=	$Filstar_conn->Execute($GETMPOSDTLS);
		if($RSGETMPOSDTLS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
					 		<td>Defective<br>Qty</td>
					 		<td>Internal<br>Barcode Qty</td>
					 	</tr>";
			$cnt	=	1;
			$totqty	=	0;
			$totamt	=	0;
			while (!$RSGETMPOSDTLS->EOF) {
				$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
				$SKUNODESC	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo= '{$SKUNO}'");
				$SCANNEDS	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$SKUNO}'");
				$QTY		=	$RSGETMPOSDTLS->fields["QTY"];
				$UNITPRICE	=	$RSGETMPOSDTLS->fields["UNITPRICE"];
				$GROSSAMOUNT=	$RSGETMPOSDTLS->fields["GROSSAMOUNT"];
				$MPOSSTATUS	=	$RSGETMPOSDTLS->fields["ITEMTYPE"];
				
				echo "<tr class='trbody' id='trfound$cnt' data-unitprice='$UNITPRICE'>
					 		<td align='center'>
					 			$cnt
								<input type='hidden' id='hiditemno$cnt' name='hiditemno$cnt' value='$SKUNO'>
							</td>
					 		<td  align='center' id='tditemno$cnt'>$SKUNO</td>
					 		<td>$SKUNODESC</td>
					 		<td align='center'>$MPOSSTATUS</td>
					 		<td align='center'>".number_format($QTY)."</td>
					 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
					 		<td align='center' id='tdcurrstatus$cnt' data=status='$SCANSTATUS'>$SCANNEDS</td>
					 		<td align='center'><input type='text' id='txtrecqty$cnt' name='txtrecqty$cnt' size='5' class='txtinputqty' data-curcnt='$cnt'></td>
					 		<td id='tdrecamt$cnt' align='right'></td>
					 		<td align='center'><input type='text' id='txtdefqty$cnt' name='txtdefqty$cnt' size='5' class='txtdefqty' data-curcnt='$cnt'></td>
					 		<td align='center'><input type='text' id='txtibqty$cnt' name='txtibqty$cnt' size='5' class='txtibqty centered' data-curcnt='$cnt'></td>
					 	</tr>";
				$cnt++;
				$totqty	+=	$QTY;
				$totamt	+=	$GROSSAMOUNT;
				$RSGETMPOSDTLS->MoveNext();
			}
			echo "</table><br>
				<input type='button' value='Add Row' id='btnadditem' name='btnadditem' class='small_button' title='Adds row' data-aboverowcnt='$cnt'>
				<input type='button' value='Remove Row' id='btnremoveitem' name='btnremoveitem' class='small_button' title='Removes Last row.'>
				<input type='hidden' id='txtaddeditemscnt' name='txtaddeditemscnt' value='0'><br><br>";
			echo "<table border='1' width='100%' class='tbl-scanning-summ'>
					<tr class='tbl-scanning-summ-hdr'>
						<td>MPOS QUANTITY</td>
						<td>MPOS AMOUNT</td>
						<td>RECEIVED QUANTITY</td>
						<td>RECEIVED AMOUNT</td>
						<td>DEFECTIVE QUANTITY</td>
						<td>INTERNAL BARCODE QUANTITY</td>
					</tr>
					<tr class='tbl-scanning-summ-dtl bld' id='trtotcnt' data-cnt='$cnt'>
						<td align='center'>".number_format($totqty)."</td>
						<td align='right'>".number_format($totamt,2)."</td>
						<td align='center' id='tdrecqty'>0</td>
						<td align='right' id='tdrecamt'>0.00</td>
						<td align='center' id='tdtotdefqty'>0</td>
						<td align='center' id='tdtotibqty'>0</td>
					  </tr>
				</table>";
			echo $FOUNDIBCUST	=	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","IB_CUSTOMERS","COUNT(*)","STATUS = 'ACTIVE' AND CODE = '$CUSTNO'");
			if($FOUNDIBCUST > 0)
			{
				echo "<script>$('.txtibqty').removeAttr('disabled');</script>";
			}
			else 
			{
				echo "<script>$('.txtibqty').attr('disabled','disabled');</script>";
			}
		}
		exit();
	}
	if ($action == "ESCANNING")
	{
		$MPOSNO		=	$_GET["MPOSNO"];
		$MPOSDATE	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","MPOSDATE","MPOSNO= '{$MPOSNO}'");
		$GROSSAMOUNT=	number_format($global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","GROSSAMOUNT","MPOSNO= '{$MPOSNO}'"),2);
		$TOTALQTY	=	number_format($global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","TOTALQTY","MPOSNO= '{$MPOSNO}'"));
		$CUSTNO		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
		$CUSTNAME	=	addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}'"));
		$SRNO		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","SALESREPNO","MPOSNO= '{$MPOSNO}'");
		$SRNAME		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$SRNO}'");
		
		echo "<script>
					$('#tdmposno').text(':$MPOSNO');
					$('#tdcustomer').text(':$CUSTNO-$CUSTNAME');
					$('#tdsr').text(':$SRNO-$SRNAME');
					$('#tdmposnodt').text(':$MPOSDATE');
					$('#tdtotqty').text(':$TOTALQTY');
					$('#tdtotamount').text(':$GROSSAMOUNT');
		 	  </script>";
		$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.SCANDATA_DTL WHERE MPOSNO = '{$MPOSNO}'
							 ORDER BY RECID";
		$RSGETMPOSDTLS	=	$Filstar_conn->Execute($GETMPOSDTLS);
		if($RSGETMPOSDTLS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
					 		<td>Defective<br>Qty</td>
					 		<td>Internal<br>Barcode Qty</td>
					 	</tr>";
			$cnt	=	1;
			$totqty	=	0;
			$totamt	=	0;
			$totscannedqty	=	0;
			$totscannedamt	=	0;
			$totdefectiveamt=	0;
			$totibamt		=	0;
			$addeditemcnt	=	0;
			while (!$RSGETMPOSDTLS->EOF) {
				$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
				$SKUNODESC	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo= '{$SKUNO}'");
				$QTY		=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","QTY","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$UNITPRICE	=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","UNITPRICE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$GROSSAMOUNT=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","GROSSAMOUNT","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$MPOSSTATUS	=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","ITEMTYPE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				
				$SCANNEDQTY	=	$RSGETMPOSDTLS->fields["SCANNEDQTY"];
				$UPDATEQTY	=	$RSGETMPOSDTLS->fields["UPDATEQTY"];
//				$SCANSTATUS	=	$RSGETMPOSDTLS->fields["ITEMSTATUS"];
				$SCANSTATUS	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$SKUNO}'");
				$DEFECTIVEQTY=	$RSGETMPOSDTLS->fields["DEFECTIVEQTY"];
				$IB_QTY		=	$RSGETMPOSDTLS->fields["IB_QTY"];
				$ADDTL		=	$RSGETMPOSDTLS->fields["ADDTL"];
				$STATUS		=	$RSGETMPOSDTLS->fields["STATUS"];
				$F_QTY		=	$SCANNEDQTY;
				
				if($UNITPRICE == '')
				{
					$UNITPRICE	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
				}
				$SCANNEDAMT	=	$F_QTY * $UNITPRICE;
				if($ADDTL == "N")
				{
					echo "<tr class='trbody' id='trfound$cnt' data-unitprice='$UNITPRICE'>
					 		<td align='center'>
					 			$cnt
								<input type='hidden' id='hiditemno$cnt' name='hiditemno$cnt' value='$SKUNO'>
							</td>
					 		<td id='tditemno$cnt' align='center'>$SKUNO</td>
					 		<td>$SKUNODESC</td>
					 		<td align='center'>$MPOSSTATUS</td>
					 		<td align='center'>".number_format($QTY)."</td>
					 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
					 		<td align='center' id='tdcurrstatus$cnt' data=status='$SCANSTATUS'>$SCANSTATUS</td>
					 		<td align='center'><input type='text' id='txtrecqty$cnt' name='txtrecqty$cnt' size='5' class='txtinputqty' value='$F_QTY'></td>
					 		<td id='tdrecamt$cnt' align='right'>".number_format($SCANNEDAMT,2)."</td>
					 		<td align='center'><input type='text' id='txtdefqty$cnt' name='txtdefqty$cnt' size='5' class='txtdefqty' data-curcnt='$cnt' value='$DEFECTIVEQTY'></td>
					 		<td align='center'><input type='text' id='txtibqty$cnt' name='txtibqty$cnt' size='5' class='txtibqty centered' data-curcnt='$cnt' value='$IB_QTY'></td>
					 	</tr>";
					$cnt++;
					$newconuter		=	$cnt;
					$newconuter--;
				}
				else 
				{
					$newconuter++;
					$addeditemcnt++;
					echo "<tr id = 'trfound$newconuter' class='trdtls' style='font-size:12px;'>
								    	<td align='center'>$newconuter</td>
								    	<td align='center'>
		    								<input type='text' id='hiditemno$newconuter' name='hiditemno$newconuter' size='5' class='addeditem' data-curcnt='$newconuter' value='$SKUNO'>
		    								<input type='hidden' id='hidsrp$newconuter'  name='hidsrp$newconuter' value='$UNITPRICE'>
		    							</td>
								    	<td id='tditemdesc$newconuter'>$SKUNODESC</td>
								    	<td id='tditemstatus'></td>
								    	<td id='tditemqty'></td>
								    	<td id='tditemgross'></td>
								    	<td id='tdcurrstatus$newconuter' align='center'>$SCANSTATUS</td>
								    	<td align='center'><input type='text' id='txtrecqty$newconuter' name='txtrecqty$newconuter' size='5' class='txtaddedinputqty txtinputqty' data-curcnt='$newconuter' value='$F_QTY'></td>
								    	<td id='tdrecamt$newconuter' align='right'>".number_format($SCANNEDAMT,2)."</td>
								    	<td align='center'><input type='text' id='txtdefqty$newconuter' name='txtdefqty$newconuter' size='5' class='txtdefqty' data-curcnt='$newconuter' value='$DEFECTIVEQTY'></td>
								    	<td align='center'><input type='text' id='txtibqty$newconuter' name='txtibqty$newconuter' size='5' class='txtibqty centered' data-curcnt='$newconuter' value='$IB_QTY'></td>
			    					</tr>";
				}
				
				
				$totqty			+=	$QTY;
				$totamt			+=	$GROSSAMOUNT;
				$totscannedqty	+=	$F_QTY;
				$totscannedamt	+=	$SCANNEDAMT;
				$totdefectiveamt+=	$DEFECTIVEQTY;
				$totibamt		+=	$IB_QTY;
				$RSGETMPOSDTLS->MoveNext();
			}
			echo "</table><br>
				<input type='button' value='Add Item' id='btnadditem' name='btnadditem' class='small_button' title='Adds row' data-aboverowcnt='$cnt'>
				<input type='button' value='Remove Row' id='btnremoveitem' name='btnremoveitem' class='small_button' title='Removes Last row.'>
				<input type='hidden' id='txtaddeditemscnt' name='txtaddeditemscnt' value='$addeditemcnt'><br><br>";
			echo "<table border='0' width='100%' class='tbl-scanning-summ'>
					<tr class='tbl-scanning-summ-hdr'>
						<td>MPOS QUANTITY</td>
						<td>MPOS AMOUNT</td>
						<td>RECEIVED QUANTITY</td>
						<td>RECEIVED AMOUNT</td>
						<td>DEFECTIVE QUANTITY</td>
						<td>INTERNAL BARCODE QUANTITY</td>
					</tr>
					<tr class='tbl-scanning-summ-dtl bld' id='trtotcnt' data-cnt='$cnt'>
						<td align='center'>".number_format($totqty)."</td>
						<td align='right'>".number_format($totamt,2)."</td>
						<td align='center' id='tdrecqty'>".number_format($totscannedqty)."</td>
						<td align='right' id='tdrecamt'>".number_format($totscannedamt,2)."</td>
						<td align='center' id='tdtotdefqty'>$totdefectiveamt</td>
						<td align='center' id='tdtotibqty'>$totibamt</td>
					  </tr>
				</table>";
			$FOUNDIBCUST	=	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","IB_CUSTOMERS","COUNT(CODE)","STATUS = 'ACTIVE AND CODE = '$CUSTNO'");
			if($FOUNDIBCUST)
			{
				echo "<script>$('.txtibqty').removeAttr('disabsled');</script>";
			}
			else 
			{
				echo "<script>$('.txtibqty').attr('disabsled','disabled');</script>";
			}
		}
		exit();
	}
	if($action == "SAVESCANNINGHDR")
	{
		$MPOSNO			=	substr($_GET["MPOSNO"],1);
		$UPDATEMODE		=	$_GET["UPDATEMODE"];
		$SAVEDBY 		=	$_SESSION['username'];
		$SAVEDDATE 		=	date("Y-m-d");
		$SAVEDTIME		=	date("h:i:s");
		
		$CUSTCODE 		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
		$Filstar_conn->StartTrans();
		if($UPDATEMODE != "Update")
		{
			$INSERTSCANHDR	=	"INSERT INTO WMS_NEW.SCANDATA_HDR(`MPOSNO`, `CUSTNO`,`SCANNEDBY`, `SCANDATE`, `SCANTIME`)
								 VALUES('{$MPOSNO}','{$CUSTCODE}','{$SAVEDBY}','{$SAVEDDATE}','{$SAVEDTIME}')";
			$RSINSERTSCANHDR	=	$Filstar_conn->Execute($INSERTSCANHDR);
			if($RSINSERTSCANHDR == false)
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
			}
			$UPDATEMPOSHDR	=	"UPDATE WMS_NEW.MPOSHDR SET STATUS = 'SCANNED' WHERE MPOSNO = '{$MPOSNO}'";
			$RSUPDATEMPOSHDR=	$Filstar_conn->Execute($UPDATEMPOSHDR);
			if($RSUPDATEMPOSHDR == false)
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
			}
			$SAVEDBY_U 		=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","SCANDATA_DTL","SAVEDBY","MPOSNO= '{$MPOSNO}'");
			$SAVEDDATE_U 	=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","SCANDATA_DTL","SAVEDDATE","MPOSNO= '{$MPOSNO}'");
			$SAVEDTIME_U	=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","SCANDATA_DTL","SAVEDTIME","MPOSNO= '{$MPOSNO}'");
		}
		$delscandtls	=	"DELETE FROM WMS_NEW.SCANDATA_DTL WHERE MPOSNO = '{$MPOSNO}'";
		$RSdelscandtls	=	$Filstar_conn->Execute($delscandtls);
		if($RSdelscandtls == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		else 
		{
			echo "";
		}
		$Filstar_conn->CompleteTrans();
		exit();
	}
	if($action == "SAVESCANNINGDTLS")
	{
		$MPOSNO			=	substr($_GET["MPOSNO"],1);
		$UPDATEMODE		=	$_GET["UPDATEMODE"];
		$SKUNO			=	$_GET["hiditemno"];
		$SCANNEDQTY		=	$_GET["txtrecqty"];
		$DEFQTY			=	$_GET["txtdefqty"];
		$IBQTY			=	$_GET["itemibqty"];
		$COUNT			=	$_GET["count"];
		$SAVEDBY 		=	$_SESSION['username'];
		$SAVEDDATE 		=	date("Y-m-d");
		$SAVEDTIME		=	date("h:i:s");
		if($UPDATEMODE != "Update")
		{
			$SAVEDBY 		=	$SAVEDBY_U;
			$SAVEDDATE 		=	$SAVEDDATE_U;
			$SAVEDTIME		=	$SAVEDTIME_U;
		}
		$CUSTCODE 		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
		$MPOSDTLSCNT	=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","COUNT(MPOSNO)","MPOSNO= '{$MPOSNO}'");
		$ITEMSTATUS 	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$SKUNO}'");
		$BARCODE		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","BarCode","ItemNo= '{$SKUNO}'");
		if($COUNT > ($MPOSDTLSCNT))
		{
			$isAdded	=	"Y";
		}
		else
		{
			$isAdded	=	"N";
		}
		if($SKUNO != undefined and $SKUNO != "")
		{
			if($UPDATEMODE == "Update")
			{
				$STATUS 		=	"UPDATED";
				$ENDMSG			=	"updated";
				$UPDATEDBY 		=	$_SESSION['username'];
				$UPDATEDDATE 	=	date("Y-m-d");
				$UPDATEDTIME	=	date("h:i:s");
				$ITEMFOUND		=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","SCANDATA_DTL","SKUNO","MPOSNO= '{$MPOSNO}' AND SKUNO='$SKUNO'");
				if($ITEMFOUND != "")
				{
					$INSERTMPOSDTLS	=	"UPDATE  WMS_NEW.SCANDATA_DTL SET `STATUS`='$STATUS',`ITEMSTATUS`='$ITEMSTATUS', `BARCODE`='$BARCODE', `SCANNEDQTY`='$SCANNEDQTY',
										`DEFECTIVEQTY`='$DEFQTY',`IB_QTY`='$IBQTY',`UPDATEDBY`='$UPDATEDBY', `UPDATEDDATE`='$UPDATEDDATE', `UPDATEDTIME`='$UPDATEDTIME'
										 WHERE MPOSNO='$MPOSNO' AND SKUNO='$SKUNO'";
				}
				else 
				{
					$INSERTMPOSDTLS	=	"INSERT INTO WMS_NEW.SCANDATA_DTL(`MPOSNO`, `CUSTCODE`, `STATUS`, `SKUNO`, `ITEMSTATUS`, `BARCODE`, `SCANNEDQTY`,`DEFECTIVEQTY`,`IB_QTY`,
										`ADDTL`,`SAVEDBY`, `SAVEDDATE`, `SAVEDTIME`,`UPDATEDBY`, `UPDATEDDATE`, `UPDATEDTIME`)
										 VALUES('{$MPOSNO}','{$CUSTCODE}','{$STATUS}','{$SKUNO}','{$ITEMSTATUS}','{$BARCODE}','{$SCANNEDQTY}','{$DEFQTY}','{$IBQTY}',
										'{$isAdded}', '{$SAVEDBY}','{$SAVEDDATE}','{$SAVEDTIME}','{$UPDATEDBY}','{$UPDATEDDATE}','{$UPDATEDTIME}')";
				}
			}
			else
			{
				$STATUS 		=	"SAVED";
				$ENDMSG			=	"saved";
				$INSERTMPOSDTLS	=	"INSERT INTO WMS_NEW.SCANDATA_DTL(`MPOSNO`, `CUSTCODE`, `STATUS`, `SKUNO`, `ITEMSTATUS`, `BARCODE`, `SCANNEDQTY`,`DEFECTIVEQTY`,`IB_QTY`,
									`ADDTL`,`SAVEDBY`, `SAVEDDATE`, `SAVEDTIME`)
									 VALUES('{$MPOSNO}','{$CUSTCODE}','{$STATUS}','{$SKUNO}','{$ITEMSTATUS}','{$BARCODE}','{$SCANNEDQTY}','{$DEFQTY}','{$IBQTY}',
									'{$isAdded}', '{$SAVEDBY}','{$SAVEDDATE}','{$SAVEDTIME}')";
			}
			$RSINSERTMPOSDTLS	=	$Filstar_conn->Execute($INSERTMPOSDTLS);
			if($RSINSERTMPOSDTLS == false)
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
			}
			
		}
		$Filstar_conn->StartTrans();
		exit();
	}
	if($action == "DELSCANNING")
	{
		$MPOSNO	=	$_GET["MPOSNO"];
		$BY 		=	$_SESSION['username'];
		$DATE 		=	date("Y-m-d");
		$TIME		=	date("h:i:s");
		$Filstar_conn->StartTrans();
		$DELMPOS	=	"UPDATE  WMS_NEW.SCANDATA_DTL SET `STATUS`='DELETED', `DELBY`='{$BY}', `DELDATE`='{$DATE}', `DELTIME`='{$TIME}'
						 WHERE `MPOSNO` = '{$MPOSNO}'";
		$RSDELMPOS	=	$Filstar_conn->Execute($DELMPOS);
		if($RSDELMPOS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		else 
		{
			echo "<script>
					alert('MPOS: $MPOSNO has been successfully deleted.');
					$('#btnreport').trigger('click',['mainquery','']);
				  </script>";
		}
		$Filstar_conn->CompleteTrans();
		exit();
	}
	if($action == "POSTSCANNING")
	{
		$MPOSNO		=	$_GET["MPOSNO"];
		$BY 		=	$_SESSION['username'];
		$DATE 		=	date("Y-m-d");
		$TIME		=	date("h:i:s");
		$Filstar_conn->StartTrans();
		$GETSCANNED	=	"SELECT `MPOSNO`,`CUSTCODE`, `SKUNO`, `SCANNEDQTY`, `UPDATEQTY`,`STATUS` FROM WMS_NEW.SCANDATA_DTL
						 WHERE MPOSNO = '{$MPOSNO}'";
		$RSGETSCANNED	=	$Filstar_conn->Execute($GETSCANNED);
		if($RSGETSCANNED == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
				$UNITPRICE	=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","UNITPRICE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$PRICECLASS	=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","PRICECLASS","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$CustPriceBook	=	$global_func->Select_val($Filstar_conn," FDCRMSlive ","custmast","CustPriceBook","CustNo = '{$CUSTCODE}'");
				$custdiscount	=	$global_func->Select_val($Filstar_conn," FDCRMSlive ","custdiscount","Discount","PriceBook = '{$CustPriceBook}' AND PriceClass = '{$PRICECLASS}'");
				$F_QTY			=	$SCANNEDQTY; 
				
				$TOTALQTY	+=	$F_QTY;
				$TOTALAMT	+=	($F_QTY * $UNITPRICE);
				$TOTALNETAMT+=	($F_QTY * ($UNITPRICE-($UNITPRICE*($custdiscount/100))));
				$POSTMPOS	=	"UPDATE  WMS_NEW.SCANDATA_DTL SET `STATUS`='POSTED', POSTEDQTY = '{$F_QTY}'
								 WHERE `MPOSNO` = '{$MPOSNO}' AND SKUNO = '{$SKUNO}' AND STATUS != 'DELETED'";
				$RSPOSTMPOSMPOS	=	$Filstar_conn->Execute($POSTMPOS);
				if($RSPOSTMPOSMPOS == false)
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
				}
				$RSGETSCANNED->MoveNext();
			}
		}
		$POSTSCANHDR	=	"UPDATE  WMS_NEW.SCANDATA_HDR SET `POSTEDNETAMOUNT`='{$TOTALNETAMT}', `POSTEDGROSSAMOUNT`='{$TOTALAMT}', `POSTEDBY`='{$BY}',
							`POSTEDDATE`='{$DATE}', `POSTEDTIME`='{$TIME}' WHERE MPOSNO = '{$MPOSNO}'";
		$RSPOSTSCANHDR	=	$Filstar_conn->Execute($POSTSCANHDR);
		if($RSPOSTSCANHDR == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		
		$POSTMPOSHDR	=	"UPDATE  WMS_NEW.MPOSHDR SET STATUS='POSTED'
							 WHERE MPOSNO = '{$MPOSNO}'";
		$RSPOSTMPOSHDR	=	$Filstar_conn->Execute($POSTMPOSHDR);
		if($RSPOSTMPOSHDR == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		$Filstar_conn->CompleteTrans();
		echo "<script>
				alert('MPOS: $MPOSNO has been successfully posted.');
				$('#btnreport').trigger('click',['mainquery','']);
			  </script>";
		exit();
	}
	if($action == "VIEWMPOSDTLS")
	{
		$MPOSNO	=	$_GET["MPOSNO"];
		$COUNT	=	$_GET["COUNT"];
		$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.SCANDATA_DTL WHERE MPOSNO = '{$MPOSNO}'";
		$RSGETMPOSDTLS	=	$Filstar_conn->Execute($GETMPOSDTLS);
		if($RSGETMPOSDTLS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		else 
		{
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
					 		<td>Defective<br>Qty</td>
					 		<td>Internal<br>Barcode Qty</td>
					 	</tr>";
			$cnt				=	1;
			$totqty				=	0;
			$totamt				=	0;
			$totsqty			=	0;
			$totsamt			=	0;
			$totdefectiveamt 	= 	0;
			$totibamt 			= 	0;
			while (!$RSGETMPOSDTLS->EOF) {
				$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
				$SKUNODESC	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
				$QTY		=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","QTY","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$UNITPRICE	=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","UNITPRICE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$GROSSAMOUNT=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","GROSSAMOUNT","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$MPOSSTATUS	=	$global_func->Select_val($Filstar_conn,"WMS_NEW ","MPOSDTL","ITEMTYPE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				
				$SCANNEDS		=	$RSGETMPOSDTLS->fields["ITEMSTATUS"];
				$UPDATEQTY		=	$RSGETMPOSDTLS->fields["UPDATEQTY"];
				$DEFECTIVEQTY	=	$RSGETMPOSDTLS->fields["DEFECTIVEQTY"];
				$IB_QTY			=	$RSGETMPOSDTLS->fields["IB_QTY"];
				$SCANNEDQTY		=	$RSGETMPOSDTLS->fields["SCANNEDQTY"];
				$F_QTY			=	$SCANNEDQTY;
				
				if($UNITPRICE == '')
				{
					$UNITPRICE	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
				}
				$RECAMT		=	$F_QTY * $UNITPRICE;
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
					 		<td align='center'>".number_format($F_QTY)."</td>
					 		<td align='right'>".number_format($RECAMT,2)."</td>
					 		<td align='center'>".number_format($DEFECTIVEQTY)."</td>
					 		<td align='center'>".number_format($IB_QTY)."</td>
					 	</tr>";
				$cnt++;
				$totqty				+=	$QTY;
				$totamt				+=	$GROSSAMOUNT;
				$totsqty			+=	$F_QTY;
				$totsamt			+=	$RECAMT;
				$totdefectiveamt 	+= 	$DEFECTIVEQTY;
				$totibamt 			+= 	$IB_QTY;
				$RSGETMPOSDTLS->MoveNext();
			}
			echo "<tr class='tblresul-tbltdtls-dtls bld'>
					<td colspan='4' align='center'>Total</td>
					<td align='center'>".number_format($totqty)."</td>
					<td align='right'>".number_format($totamt,2)."</td>
					<td align='center'>&nbsp;</td>
					<td align='center'>".number_format($totsqty)."</td>
					<td align='right'>".number_format($totsamt,2)."</td>
					<td align='center'>".number_format($totdefectiveamt)."</td>
					<td align='center'>".number_format($totibamt)."</td>
				  </tr>
				</table><br>";
			}
		exit();
	}
	if($action == "GETITEM")
	{
		$BARCODE	=	$_GET["BARCODE"];
		$ITEMNO		=	$_GET["ITEMNO"];
		if($BARCODE != "")
		{
			echo $ITEMNO		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemNo","BarCode = '{$BARCODE}'");
		}
		else 
		{
			echo $ITEMNO;
		}
		
		exit();
	}
	if($action == "GETITEMDTLS")
	{
		$CURCNT 	= 	$_GET["CURCNT"];
		$ITEMNO		=	$_GET["ITEMNO"];
		$ITEMDESC	=	addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$ITEMNO}'"));
		if($ITEMDESC != "")
		{
			$ITEMSTATUS	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","DeptNo","ItemNo = '{$ITEMNO}'");
			$SELLPRICE	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$ITEMNO}'");
			echo "<script>
					$('#tditemdesc$CURCNT').text('$ITEMDESC');
					$('#tdcurrstatus$CURCNT').text('$ITEMSTATUS');
					$('#hidsrp$CURCNT').val('$SELLPRICE');
					if('$ITEMSTATUS' == 'P')
					{
						$('#tdcurrstatus$CURCNT').addClass('primeitem');
					}
					else
					{
						$('#tdcurrstatus$CURCNT').removeClass('primeitem');
					}
				  </script>";
		}
		else 
		{
			echo "<script>
					$('#tditemdesc$CURCNT').text('Item not found');
					$('#tdcurrstatus$CURCNT').text('');
					$('#hiditemno$CURCNT').val('');
					$('#hidsrp$CURCNT').val('');
				  </script>";
		}
		
		exit();
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
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/scanning/scanning.html");

?>