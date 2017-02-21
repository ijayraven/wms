<?php
session_start();
//include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");
if (empty($_SESSION['username'])) 
{
	echo "<script>
				MessageType.sessexpMsg('wms');
		  </script>";
}$action = $_GET["action"];
if($action == "Q_SEARCHCUST")
{
	$CUSTNO			=	$_GET["CUSTNO"];
	$CUSTNAME		=	$_GET["CUSTNAME"];
	$CUSTCUSTTYPE	=	$_GET["CUSTCUSTTYPE"];
	
	if($CUSTCUSTTYPE == "NBS")
	{
		$BRANCHCODE_Q	=	" AND CustomerBranchCode != ''";
	}
	if($CUSTCUSTTYPE == "TRADE") 
	{
		$BRANCHCODE_Q	=	" AND CustomerBranchCode = ''";
	}
	if($CUSTNO != "")
	{
		$CUSTNO_Q	=	" AND CustNo like '%{$CUSTNO}%'";
	}
	if($CUSTNAME != "")
	{
		$CUSTNAME_Q	=	" AND CustName like '%{$CUSTNAME}%'";
	}
	$sel	 =	"SELECT CustNo,CustName FROM  FDCRMSlive.custmast WHERE 1 $BRANCHCODE_Q $CUSTNO_Q $CUSTNAME_Q
				 LIMIT 20";
	$rssel	=	$conn_255_10->Execute($sel);
	if ($rssel == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"MPOS RECEIVING","Q_SEARCHCUST");
		$DATASOURCE->displayError();
	}
	$cnt	=	$rssel->RecordCount();
	if ($cnt > 0) 
	{
		echo "<select id='selcust' class = 'C_dropdown divsel' multiple>";
		while (!$rssel->EOF) 
		{
			$q_custno	=	$rssel->fields['CustNo'];
			$Q_custname	=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['CustName']);
			$cValue		=	$q_custno."|".$Q_custname;
			echo "<option value=\"$cValue\">$q_custno-$Q_custname</option>";
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
if($action == "GETMPOSLIST")
{
	$txtmposno		=	$_POST["txtmposno"];
	$rdocusttype	=	$_POST["rdocusttype"];
	$selmpostype	=	$_POST["selmpostype"];
	$txtcustno		=	$_POST["txtcustno"];
	$selDtype		=	$_POST["selDtype"];
	$txtdfrom		=	$_POST["txtdfrom"];
	$txtdto			=	$_POST["txtdto"];
	if($txtmposno != "")
	{
		$TXTMPOSNO_Q	=	" AND MPOSNO = '$txtmposno'";
	}
	if($rdocusttype == "NBS")
	{
		$RDOCUSTTYPE_Q	=	" AND CustomerBranchCode != ''";
	}
	if($rdocusttype == "TRADE")
	{
		$RDOCUSTTYPE_Q	=	" AND CustomerBranchCode = ''";
	}
	if($txtcustno != "")
	{
		$TXTCUSTNO_Q	=	" AND M.CUSTNO = '$txtcustno'";
	}
	if($selmpostype != "")
	{
		$SELMPOSTYPE_Q	=	" AND TYPE = '$selmpostype'";
	}
	if($selmpostype == "WAREHOUSE")
	{
		$IS_TRANSMITTED_Q	=	" AND RTNTRANSMIT = 'Y'";
	}
	if($selDtype == "MPOSDATE")
	{
		$DATE_Q	=	" AND MPOSDATE BETWEEN '$txtdfrom' AND '$txtdto'";
	}
	$GETMPOS	=	"SELECT M.`CUSTNO`, M.`MPOSNO`, M.`TYPE`, M.`SALESREPNO`, M.`TOTALQTY`, M.`GROSSAMOUNT`, M.`STATUS`, C.CustName FROM WMS_NEW.MPOSHDR AS M
					 LEFT JOIN FDCRMSlive.custmast AS C ON C.CustNo = M.CUSTNO
					 WHERE 1 AND RECEIVEDMPOS = 'N' $TXTMPOSNO_Q $RDOCUSTTYPE_Q $SELMPOSTYPE_Q $DATE_Q $TXTCUSTNO_Q $IS_TRANSMITTED_Q";

	$RSGETMPOS	=	$conn_255_10->Execute($GETMPOS);
	if ($RSGETMPOS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOS,$_SESSION['username'],"MPOS RECEIVING","GETMPOSLIST");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<form id='frmchk'>";
		echo "<table class='tblresult tablesorter' border='1'>
					<thead>
						<tr class='trheader'>
							<th>No.</th>
							<th>Custosmer</th>
							<th>MPOS No.</th>
							<th>SR</th>
							<th>MPOS Type</th>
							<th>Quantity</th>
							<th>Gross Amount</th>
							<th>Docs Rec.Date<br>(From Financials)</th>
							<th>Receive</th>
						</tr>
					<thead>
					<tbody>";
		if($RSGETMPOS->RecordCount() > 0)
		{
			$cnt	=	1;
			while (!$RSGETMPOS->EOF) {
				$CUSTNO			=	$RSGETMPOS->fields["CUSTNO"]; 
				$MPOSNO			=	$RSGETMPOS->fields["MPOSNO"]; 
				$TYPE			=	$RSGETMPOS->fields["TYPE"]; 
				$SALESREPNO		=	$RSGETMPOS->fields["SALESREPNO"]; 
				$TOTALQTY		=	$RSGETMPOS->fields["TOTALQTY"]; 
				$GROSSAMOUNT	=	$RSGETMPOS->fields["GROSSAMOUNT"]; 
				$STATUS			=	$RSGETMPOS->fields["STATUS"]; 
				$CustName		=	$RSGETMPOS->fields["CustName"]; 
				$SRNAME			=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$SALESREPNO}'");
				$CMRETURNSEQNO	=	$DATASOURCE->selval($conn_250_172,"FDCFINANCIALS_AR","CMRETURNSHDR","CMRETURNSEQNO","MPOSNO = '$MPOSNO' ");
				$WHSRCVDDATE	=	$DATASOURCE->selval($conn_250_172,"FDCFINANCIALS_AR","MPOSMONITORING","WHSRCVDDATE","CMRETURNSEQNO = '$CMRETURNSEQNO'");
				if($selmpostype == "W/STOCK" or $selmpostype == "FORWARDER")
				{
					if(!empty($WHSRCVDDATE))
					{
						$founddocs	=	true;
					}
					else 
					{
						$founddocs	=	false;
					}
				}
				else 
				{
					$founddocs	=	true;
				}
				if($selDtype == "docsdt")
				{
					if($WHSRCVDDATE >= "$txtdfrom 00:00:00" and $WHSRCVDDATE <= "$txtdto 23:59:59")
					{
						$withinWH_date	=	true;
					}
					else 
					{
						$withinWH_date	=	false;
					}
				}
				else 
				{
					$withinWH_date	=	true;
				}
				if($founddocs and $withinWH_date)
				{
					echo "<tr class='trbody'>
								<td align='center'>$cnt</td>
								<td>$CUSTNO-$CustName</td>
								<td align='center'>$MPOSNO</td>
								<td>$SALESREPNO-$SRNAME</td>
								<td align='center'>$TYPE</td>
								<td align='center'>$TOTALQTY</td>
								<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
								<td align='center'>$WHSRCVDDATE</td>
								<td align='center'>
									<input type	= 'checkbox' id='chkpick$cnt' name='chkpick[]' class='chkmpos' value='$MPOSNO'>
								</td>
						   </tr>";
					$cnt++;
				}
				$RSGETMPOS->MoveNext();
			}
			
		echo " </tbody>
			</table>
			</form>
			<button type='button' class='btntransmit btnprocesses'>RECEIVE</button>";
		}
		else 
		{
			echo "<tr class='trbody'>
						<td align='center' style='color:red;' colspan='9'>Nothing to display.</td>
				  </tr>";
			
		echo " </tbody>
			</table>
			</form>";
		}
	}
	exit();
}
if($action == "RECEIVEMPOS")
{
	if(!empty($_POST['chkpick']))
	{
		foreach($_POST['chkpick'] as $TRANSNUM)
		{
			
			$MPOSlist	.=	",'$TRANSNUM'";
		}
		$MPOSlist	=	substr($MPOSlist, 1);
		$RECEIVEMPOS	=	"UPDATE WMS_NEW.MPOSHDR SET RECEIVEDMPOS = 'Y', RECEIVEDDATE= NOW() WHERE MPOSNO IN ($MPOSlist)";
		$RSRECEIVEMPOS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$RECEIVEMPOS,$_SESSION["username"],"MPOS RECEIVING","RECEIVEMPOS");
		if($RSRECEIVEMPOS)
		{
			echo "<script>
					MessageType.infoMsg('Selected MPOS has been successfully received.');
					$('.btnsearch').trigger('click');
				  </script>";
		}
		
	}
	exit();
}
//include($_SERVER['DOCUMENT_ROOT']."/wms/includes/jsUI.php"); 0011599	 0014662
include($_SERVER['DOCUMENT_ROOT']."/public_js/jsUI.php");
include("receiving.html");
?>