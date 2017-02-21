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
	$selstatus	=	$_POST["selstatus"];
	$selDtype 	=	$_POST["selDtype"];
	$txtfrom 	=	$_POST["txtfrom"];
	$txtto 		=	$_POST["txtto"];
	
	if($txtmtono != "")
	{
		$MTONUM_Q	=	" AND MTONO = '$txtmtono'";
	}
	if($selstatus != "")
	{
		if($selstatus == "RECEIVED")
		{
			$STATUS_Q	=	" AND STATUS = '$selstatus'";
		}
		else 
		{
			$STATUS_Q	=	" AND STATUS = ''";
		}
	}
	if($txtfrom != "")
	{
		if($selDtype == "RECEIVEDDATE")
		{
			$DATE_Q	=	" AND $selDtype BETWEEN '$txtfrom 00:00:00' AND '$txtto 23:59:59'";
		}
		else 
		{
			$DATE_Q	=	" AND MTO_TRANSMITTED_DT BETWEEN '$txtfrom 00:00:00' AND '$txtto 23:59:59'";
		}
	}
	$GETMTO		=	"SELECT `MTONO`, `TRANS_NO`, `ARS_NO`, `PIECEWORKER`, `STATUS`, `RECEIVEDBY`, `RECEIVEDDATE` FROM WMS_NEW.MTO_PCWHDR 
					 WHERE 1 $MTONUM_Q $STATUS_Q $DATE_Q";
	$RSGETMTO	=	$conn_255_10->Execute($GETMTO);
	if($RSGETMTO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"RAW RECEIVING","GETMTO");
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
						<th>MTO No.</th>
						<th>PIF No.</th>
						<th>ARS No.</th>
						<th>Pieceworker</th>
						<th>Status</th>
						<th>MTO Transmitted Date</th>
						<th>Received By</th>
						<th>Received Date</th>
						<th>Action</th>
					</tr>
				<thead>
				<tbody>";
			$cnt = 1;
			while (!$RSGETMTO->EOF)
			{
				$MTONO				=	$RSGETMTO->fields["MTONO"];
				$TRANS_NO			=	$RSGETMTO->fields["TRANS_NO"];
				$ARS_NO				=	$RSGETMTO->fields["ARS_NO"];
				$PIECEWORKERID		=	$RSGETMTO->fields["PIECEWORKER"];
				$PIECEWORKER		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","DESCRIPTION","RECID='$PIECEWORKERID'");
				$STATUS				=	$RSGETMTO->fields["STATUS"];
				$MTO_TRANSMITTED_DT	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RAWHDR","ISSUEDDATE","MTONO='$MTONO'");
				$RECEIVEDBY			=	$RSGETMTO->fields["RECEIVEDBY"];
				$RECEIVEDDATE		=	$RSGETMTO->fields["RECEIVEDDATE"];
				if($STATUS == "")
				{
					$STATUS = "RAW Transmitted";
					$chkbox	=	"<input type	= 'checkbox' id='chkpick$cnt' name='chkpick[]' class='chkmpos' value='$MTONO'>";
				}
				else 
				{
					$chkbox	=	"	";
				}
				echo "<tr class='trbody'>
								<td align='center'>$cnt</td>
								<td align='center'>$MTONO</td>
								<td align='center'>$TRANS_NO</td>
								<td align='center'>$ARS_NO</td>
								<td align='center'>$PIECEWORKER</td>
								<td align='center'>$STATUS</td>
								<td align='center'>$MTO_TRANSMITTED_DT</td>
								<td align='center'>$RECEIVEDBY</td>
								<td align='center'>$RECEIVEDDATE</td>
								<td align='center'>
									$chkbox
								</td>
						   </tr>";
					$cnt++;
				$RSGETMTO->MoveNext();
			}
			echo "</tbody>
			</table>
			<button type='button' class='btntransmit btnprocesses'>RECEIVE</button>
			</form>";
		}
	}
	exit();
}
if($action == "RECEIVEMTO")
{
	if(!empty($_POST['chkpick']))
	{
		foreach($_POST['chkpick'] as $TRANSNUM)
		{
			$MTOlist	.=	",'$TRANSNUM'";
		}
		$MTOlist		=	substr($MTOlist, 1);
		$RECEIVEMTO		=	"UPDATE WMS_NEW.MTO_PCWHDR SET STATUS = 'RECEIVED', RECEIVEDBY = '{$_SESSION['username']}',RECEIVEDDATE='$today',RECEIVEDTIME = '$TIME'
						 	 WHERE MTONO IN ($MTOlist)";
		$RSRECEIVEMTO	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$RECEIVEMTO,$_SESSION["username"],"PIECEWORKER RECEIVING","RECEIVEMTO");
		if($RSRECEIVEMTO)
		{
			echo "<script>
					MessageType.infoMsg('Selected MTO has been successfully received.');
					$('.btnsearch').trigger('click');
				  </script>";
		}
	}
	exit();
}
function getTblhdr()
{
	return "<table class='tblresult tablesorter'>
				<thead>
					<tr class='trheader' bgcolor='Teal'>
						<th>No.</th>
						<th>MTO No.</th>
						<th>PIF No.</th>
						<th>ARS No.</th>
						<th>Pieceworker</th>
						<th>Status</th>
						<th>MTO Transmitted Date</th>
						<th>Received By</th>
						<th>Received Date</th>
						<th>Action</th>
					</tr>
				<thead>
				<tbody>
					<tr class='trbody fnt-red'>
						<td align='center' colspan='10'>Nothing to display.</td>
				   </tr>
				  </tbody>
			</table>";
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/pcw/receiving/receiving.html");
?>