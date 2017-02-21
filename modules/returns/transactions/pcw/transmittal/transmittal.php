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
	$TRANS_NO 	=	$_POST["txtmtono"];
	$txtfrom 	=	$_POST["txtfrom"];
	$txtto 		=	$_POST["txtto"];
	
	if($txtmtono != "")
	{
		$TRANS_NO_Q	=	" AND TRANS_NO = '$TRANS_NO'";
		$default_Q	=	"";
	}
	if($txtfrom != "")
	{
		$DATE_Q	=	" AND PRINTDATE BETWEEN '$txtfrom 00:00:00' AND '$txtto 23:59:59'";
	}
	$GETMTO		=	"SELECT `MTONO`,`DRNO`, `TRANS_NO`, `ARS_NO`, `PIECEWORKER`, `STATUS`, `RECEIVEDBY`, `RECEIVEDDATE`,`POSTEDBY`, `POSTEDDATE`, `PRINTBY`, `PRINTDATE`, `TRANSMITTED_DT`, `TRANSMITTED_BY` 
					 FROM WMS_NEW.MTO_PCWHDR 
					 WHERE STATUS='PRINTED' $TRANS_NO_Q $DATE_Q";
	$RSGETMTO	=	$conn_255_10->Execute($GETMTO);
	if($RSGETMTO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"TRANSMIT MTO TO FB","GETMTO");
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
			echo "
				 <div class='dropdown'>
					  <button class='dropbtn'>Show/hide columns<span>&nbsp;<img src='/wms/images/action_icon/new/arrowdown.png' class='arrowdown'></span></button>
					  <div class='dropdown-content'>
					    <input type='checkbox' id='chk0' class='chkcol' value='1' checked><label for='chk0'>No.</label><br>	
					    <input type='checkbox' id='chk1' class='chkcol' value='2' checked><label for='chk1'>MTO No.</label><br>	
					    <input type='checkbox' id='chk2' class='chkcol' value='3' checked><label for='chk2'>PIF No.</label><br>	
						<input type='checkbox' id='chk3' class='chkcol' value='4' checked><label for='chk3'>ARS No.</label><br>	
						<input type='checkbox' id='chk4' class='chkcol' value='5' checked><label for='chk4'>DR No.</label><br>	
						<input type='checkbox' id='chk5' class='chkcol' value='6' checked><label for='chk5'>Pieceworker</label><br>	
						<input type='checkbox' id='chk6' class='chkcol' value='7' checked><label for='chk6'>Status</label><br>	
						<input type='checkbox' id='chk7' class='chkcol' value='8' checked><label for='chk7'>Received By</label><br>	
						<input type='checkbox' id='chk8' class='chkcol' value='9' checked><label for='chk8'>Received Date</label><br>	
						<input type='checkbox' id='chk9' class='chkcol' value='10'><label for='chk9'>Posted By</label><br>	
						<input type='checkbox' id='chk10' class='chkcol' value='11'><label for='chk10'>Posted Date</label><br>	
						<input type='checkbox' id='chk11' class='chkcol' value='12'><label for='chk11'>Printed By</label><br>	
						<input type='checkbox' id='chk12' class='chkcol' value='13'><label for='chk12'>Printed Date</label><br>	
						<input type='checkbox' id='chk13' class='chkcol' value='14'><label for='chk13'>Transmitted By</label><br>	
						<input type='checkbox' id='chk14' class='chkcol' value='15'><label for='chk14'>Transmitted Date</label><br>	
					  </div>
				 </div>
				 <form id='frmmto'>
					<table class='tblresult tablesorter' id='tblmtolist'>
					<thead>
						<tr class='trheader'>
							<th>No.</th>
							<th>MTO No.</th>
							<th>PIF No.</th>
							<th>ARS No.</th>
							<th>DR No.</th>
							<th>Pieceworker</th>
							<th>Status</th>
							<th>Received By</th>
							<th>Received Date</th>
							<th>Posted By</th>
							<th>Posted Date</th>
							<th>Printed By</th>
							<th>Printed Date</th>
							<th>Transmitted By</th>
							<th>Transmitted Date</th>
							<th>Action</th>
						</tr>
					<thead>
					<tbody>";
			$cnt = 1;
			while (!$RSGETMTO->EOF)
			{
				$MTONO				=	$RSGETMTO->fields["MTONO"];
				$DRNO				=	$RSGETMTO->fields["DRNO"];
				$TRANS_NO			=	$RSGETMTO->fields["TRANS_NO"];
				$ARS_NO				=	$RSGETMTO->fields["ARS_NO"];
				$PIECEWORKERID		=	$RSGETMTO->fields["PIECEWORKER"];
				$PIECEWORKER		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","DESCRIPTION","RECID='$PIECEWORKERID'");
				$STATUS				=	$RSGETMTO->fields["STATUS"];
				$RECEIVEDBY			=	$RSGETMTO->fields["RECEIVEDBY"];
				$RECEIVEDDATE		=	$RSGETMTO->fields["RECEIVEDDATE"];
				$POSTEDBY 			= 	$RSGETMTO->fields["POSTEDBY"]; 
				$POSTEDDATE 		= 	$RSGETMTO->fields["POSTEDDATE"]; 
				$PRINTBY 			= 	$RSGETMTO->fields["PRINTBY"]; 
				$PRINTDATE 			= 	$RSGETMTO->fields["PRINTDATE"]; 
				$TRANSMITTED_DT 	= 	$RSGETMTO->fields["TRANSMITTED_DT"]; 
				$TRANSMITTED_BY		= 	$RSGETMTO->fields["TRANSMITTED_BY"]; 
				
				if($STATUS == "")
				{
					$btnrec	=	"<img src='/wms/images/images/action_icon/new/check.png' class='smallbtns recbtn tooltips' title='Receive: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btnrec	=	"";
				}
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
					$btndocument	=	"<img src='/wms/images/images/action_icon/new/document.png' class='smallbtns documentbtn tooltips' title='Print: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btntdocument	=	"";
				}
				echo "<tr class='trbody'>
								<td align='center'>$cnt</td>
								<td align='center'>$MTONO</td>
								<td align='center'>$TRANS_NO</td>
								<td align='center'>$ARS_NO</td>
								<td align='center'>$DRNO</td>
								<td align='center'>$PIECEWORKER</td>
								<td align='center'>$STATUS</td>
								<td align='center'>$RECEIVEDBY</td>
								<td align='center'>$RECEIVEDDATE</td>
								<td align='center'>$POSTEDBY</td>
								<td align='center'>$POSTEDDATE</td>
								<td align='center'>$PRINTBY</td>
								<td align='center'>$PRINTDATE</td>
								<td align='center'>$TRANSMITTED_BY</td>
								<td align='center'>$TRANSMITTED_DT</td>
								<td align='center'>
									 <input type='checkbox' id='chk$cnt' name='chkmto[]' class='chkmtos' value='$MTONO'>
								</td>
						   </tr>";
					$cnt++;
				$RSGETMTO->MoveNext();
			}
			echo "</tbody>
			</table>
			</form>
			<button type='button' id='btntransmit' class='btntransmit'>TRANSMIT</button>";
		}
	}
	exit();
}
if($action == "TRANSMITMTO")
{
	$conn_255_10->StartTrans();
	if(!empty($_POST['chkmto'])) 
	{
	    foreach($_POST['chkmto'] as $MTONO) {
	        $UPDATEMTO		=	"UPDATE WMS_NEW.MTO_PCWHDR SET STATUS = 'TRANSMITTED', TRANSMITTED_DT = NOW(), TRANSMITTED_BY = '$user'
	        					 WHERE MTONO = '$MTONO'";
	        $RSUPDATEMTO	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEMTO,$user,"TRANSMIT MTO TO FB","TRANSMITMTO");
	        if($RSUPDATEMTO)
	        {
	        	$PCWORKER	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_PCWHDR","PIECEWORKER","MTONO = '$MTONO'");
	        	$TRANS_NO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_PCWHDR","TRANS_NO","MTONO = '$MTONO'");
	        	
	        	$INSERTTOFB		=	"INSERT INTO WMS_NEW.MTO_FILLINGBINHDR(`TRANSNO`,`MTONO`, `STATUS`, `PCWORKER`, `MTO_TRANSMITTED_DT`)
	        						 VALUES('$TRANS_NO','$MTONO','','$PCWORKER',NOW())";
	        	$RSINSERTTOFB	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTTOFB,$user,"TRANSMIT MTO TO FB","TRANSMITMTO");
	        	if($RSINSERTTOFB)
	        	{
	        		$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_PCWDTL WHERE MTONO = '$MTONO'";
	        		$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
	        		if($RSGETDTLS == false)
	        		{
	        			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
						$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"TRANSMIT MTO TO FB","TRANSMITMTO");
						$DATASOURCE->displayError();
	        		}
	        		else 
	        		{
	        			while (!$RSGETDTLS->EOF) {
	        				
	        				$MTONO 			= $RSGETDTLS->fields["MTONO"]; 
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
							$RSINSERTDTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTDTLS,$user,"TRANSMIT MTO TO FB","TRANSMITMTO");
	        				$RSGETDTLS->MoveNext();
	        			}
	        		}
	        	}
	        }
	    }
	}
	$conn_255_10->CompleteTrans();
	echo "<script>MessageType.successMsg('MTO - $MTONO has been successfully transmitted.');location.reload();</script>";
	exit();
}
function getTblhdr()
{
	return "<table class='tblresult tablesorter' id='tblmtolist'>
				<thead>
					<tr class='trheader'>
						<th>No.</th>
							<th>MTO No.</th>
							<th>PIF No.</th>
							<th>ARS No.</th>
							<th>DR No.</th>
							<th>Pieceworker</th>
							<th>Status</th>
							<th>Received By</th>
							<th>Received Date</th>
							<th>Posted By</th>
							<th>Posted Date</th>
							<th>Printed By</th>
							<th>Printed Date</th>
							<th>Transmitted By</th>
							<th>Transmitted Date</th>
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
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/pcw/transmittal/transmittal.html");
?>