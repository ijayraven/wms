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
	
if ($action=='Q_SEARCHCUST') 
{
	$custno		=	addslashes($_GET['CUSTNO']);
	$custname	=	addslashes($_GET['CUSTNAME']);
	$selcusttype=	$_GET["selcusttype"];
	$custcusttype=	$_GET["custcusttype"];
	
	if($custcusttype == "NBS")
	{
		$BRANCHCODE_Q	=	" AND CustomerBranchCode != ''";
	}
	if($custcusttype == "TRADE") 
	{
		$BRANCHCODE_Q	=	" AND CustomerBranchCode = ''";
	}
	if($selcusttype != "ALL")
	{
		$CUSTNO_Q	=	" AND SUBSTRING(CustNo,-1,1) = '{$selcusttype}'";
	}
	
	$sel	 =	"SELECT CustNo,CustName FROM  FDCRMSlive.custmast WHERE 1 $BRANCHCODE_Q $CUSTNO_Q";
	
	if (!empty($custno)) 
	{
	$sel	.=	" AND CustNo like '%{$custno}%' ";
	}
	if (!empty($custname)) 
	{
	$sel	.=	" AND CustName like '%{$custname}%' ";
	}
	$sel	.=	" limit 20 ";
	$rssel	=	$conn_255_10->Execute($sel);
	if ($rssel == false) 
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"MPOS MONITORING","Q_SEARCHCUST");
		$DATASOURCE->displayError();
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
	$selcusttype=	$_POST["selcusttype"];
	$rdcusttype	=	$_POST["rdcusttype"];
	$txtcustno	=	$_POST["txtcustno"];
	$selmpostype=	$_POST["selmpostype"];
	$mposdfrom	=	$_POST["mposdfrom"];
	$mposdto	=	$_POST["mposdto"];
	$rcvddfrom	=	$_POST["rcvddfrom"];
	$rcvddto	=	$_POST["rcvddto"];
	$scandfrom	=	$_POST["scandfrom"];
	$scandto	=	$_POST["scandto"];
	$selrprttype=	$_POST["selrprttype"];
	
	$_SESSION["mposdfrom"]	=	$mposdfrom;
	$_SESSION["mposdto"]	=	$mposdto;
	$_SESSION["rcvddfrom"]	=	$rcvddfrom;
	$_SESSION["rcvddto"]	=	$rcvddto;
	$_SESSION["scandfrom"]	=	$scandfrom;
	$_SESSION["scandto"]	=	$scandto;
	if ($selcusttype != "ALL") {
		$selcusttype_Q	=	" AND SUBSTRING(H.CUSTNO,-1,1) = '{$selcusttype}'";
	}
	if ($rdcusttype == "NBS") {
		$rdcusttype_Q	=	" AND C.CustomerBranchCode != ''";
	}
	if ($rdcusttype == "TRADE") {
		$rdcusttype_Q	=	" AND C.CustomerBranchCode = ''";
	}
	if ($txtcustno != "") {
		$txtcustno_Q	=	" AND H.CUSTNO = '{$txtcustno}'";
	}
	if ($selmpostype != "") {
		$selmpostype_Q	=	" AND H.TYPE = '{$selmpostype}'";	
	}
	if ($mposdfrom != "") {
		$mposdate_Q		=	" AND H.MPOSDATE BETWEEN '$mposdfrom 00:00:00' AND '$mposdto 23:59:59'";
	}
	if ($rcvddfrom != "") {
		$rcvddate_Q		=	" AND H.RECEIVEDDATE BETWEEN '$rcvddfrom 00:00:00' AND '$rcvddto 23:59:59'";
	}
	if($selrprttype == "rcvd_and_scnd")
	{
		$selrprttype_Q	=	" AND H.STATUS	!=	'' AND RECEIVEDMPOS	=	'Y'";
	}
	if($selrprttype == "rcvd_not_scnd")
	{
		$selrprttype_Q	=	" AND H.STATUS	=	'' AND RECEIVEDMPOS	=	'Y'";
	}
	if($scandfrom != "")
	{
		$SCANNEDWHERE		=	" AND S.SCANDATE BETWEEN '$scandfrom 00:00:00' AND '$scandto 23:59:59'";
	}

	$GETMPOS	=	"SELECT H.MPOSNO,H.CUSTNO,H.MPOSDATE,H.TOTALQTY,H.GROSSAMOUNT,H.NETAMOUNT,H.TYPE,H.STATUS,C.CustName ,S.SCANDATE,H.RECEIVEDDATE
					 FROM WMS_NEW.`MPOSHDR` AS H 
					 LEFT JOIN WMS_NEW.MPOSDTL AS D ON D.TRANSNO = H.TRANSNO
					 LEFT JOIN WMS_NEW.SCANDATA_HDR AS S ON S.MPOSNO = H.MPOSNO
					 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = H.CUSTNO
					 WHERE 1 $SCANNEDWHERE
					 $selcusttype_Q $rdcusttype_Q $txtcustno_Q $selmpostype_Q $mposdate_Q $scandate_Q $rcvddate_Q $selrprttype_Q
					 GROUP BY H.TRANSNO
					 ORDER BY H.TRANSNO";
//	echo $GETMPOS; 
//	exit();
	$_SESSION["QUERY"]	=	$GETMPOS;
	$RSGETMPOS	=	$conn_255_10->Execute($GETMPOS);
	if($RSGETMPOS == false)
	{
		echo $errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOS,$_SESSION['username'],"RECEIVED AND SCANNED","GETMPOS");
		$DATASOURCE->displayError();
	}
	else 
	{
		if($RSGETMPOS->RecordCount() == 0)
		{
			echo getTBLprev();exit();
		}
		else 
		{
			$cnt			=	1;
			$GRANDTOTAL		=	0;
			$GRANDNET		=	0;
			$GRANDQTY		=	0;
			echo "
					<table border='1'class='tblresult tablesorter'>
						<thead>
							<tr class='trheader'>
						 		<th>No.</th>
						 		<th>Customer</th>
						 		<th>MPOS No.</th>
						 		<th>MPOS Type</th>
						 		<th>MPOS Date</th>	
						 		<th>Scan Date</th>	
						 		<th>Received Date</th>	
						 		<th>Status</th>	
						 		<th>Qty</th>	
						 		<th>Gross Amount</th>
						 		<th>Net Amount</th>
						 	</tr>
						 </thead>
						 <tbody>";
			while (!$RSGETMPOS->EOF) {
				$CUSTNO		=	$RSGETMPOS->fields["CUSTNO"];
				$CustName	=	$RSGETMPOS->fields["CustName"];
				$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];
				$MPOSDATE	=	$RSGETMPOS->fields["MPOSDATE"];
				$SCANDATE	=	$RSGETMPOS->fields["SCANDATE"];
				$TOTALQTY	=	$RSGETMPOS->fields["TOTALQTY"];
				$TYPE		=	$RSGETMPOS->fields["TYPE"];
				$STATUS		=	$RSGETMPOS->fields["STATUS"];
				$GROSSAMOUNT=	$RSGETMPOS->fields["GROSSAMOUNT"];
				$NETAMOUNT	=	$RSGETMPOS->fields["NETAMOUNT"];
				$RECEIVEDDATE=	$RSGETMPOS->fields["RECEIVEDDATE"];
				if($STATUS != "")
				{
					$STATUS	=	"Received and Scanned";
				}
				else 
				{
					$STATUS	=	"Received and not yet Scanned";
				}
				echo "<tr class='trbody'>
					 		<td align='center'>$cnt</td>
					 		<td >$CUSTNO-$CustName</td>
					 		<td align='center'>$MPOSNO</td>
					 		<td align='center'>$TYPE</td>
					 		<td align='center'>$MPOSDATE</td>	
					 		<td align='center'>$SCANDATE</td>
					 		<td align='center'>".date("Y-m-d",strtotime($RECEIVEDDATE))."</td>
					 		<td align='center'>$STATUS</td>	
					 		<td align='center'>".number_format($TOTALQTY)."</td>	
					 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
					 		<td align='right'>".number_format($NETAMOUNT,2)."</td>
					 	</tr>";
				$GRANDTOTAL	+=	$GROSSAMOUNT;
				$GRANDNET	+=	$NETAMOUNT;
				$GRANDQTY	+=	$TOTALQTY;
				$cnt++;
			$RSGETMPOS->Movenext();
			}
		}
	}
	echo "</tbody>
		<tr class='trbody bld'>
	 		<td align='center' colspan='8'>GRAND TOTAL</td>
	 		<td align='right'>".number_format($GRANDQTY)."</td>
	 		<td align='right'>".number_format($GRANDTOTAL,2)."</td>
	 		<td align='right'>".number_format($GRANDNET,2)."</td>
	 	</tr>";
	echo "</table>";
	echo "<button type='button' class='btnprint' onclick='print();'>Print PDF</button>";
	echo "<button type='button' class='btnprint' onclick='print_csv();'>Print CSV</button>";
	exit();
}
function getTBLprev()
{
	return "<table border='1'class='tblresult tablesorter'>
				<thead>
					<tr class='trheader'>
				 		<th>No.</th>
				 		<th>Customer</th>
				 		<th>MPOS No.</th>
				 		<th>MPOS Type</th>
				 		<th>MPOS Date</th>	
				 		<th>Scan Date</th>	
				 		<th>Received Date</th>	
				 		<th>Status</th>	
				 		<th>Qty</th>	
				 		<th>Gross Amount</th>
				 		<th>Net Amount</th>
				 	</tr>
				 </thead>
		 		<tr class='trbody centered fnt-red'>
			 		<td colspan='11'>Nothing to display.</td>
			 	</tr>
			 </table>";
}
include("received_scanned.html");
?>
