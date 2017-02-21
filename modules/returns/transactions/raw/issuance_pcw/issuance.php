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
	$txtpcwno	=	$_POST["txtpcwno"];
	$selstatus	=	$_POST["selstatus"];
	$selDtype 	=	$_POST["selDtype"];
	$txtfrom 	=	$_POST["txtfrom"];
	$txtto 		=	$_POST["txtto"];
	
	$default_Q	=	" AND STATUS = 'RECEIVED'";
	if($txtmtono != "")
	{
		$MTONUM_Q	=	" AND MTONO = '$txtmtono'";
		$default_Q	=	"";
	}
	if($txtpcwno != "")
	{
		$txtpcwno_Q		=	" AND PCWORKER = '$txtpcwno'";
	}
	if($selstatus != "")
	{
		$STATUS_Q	=	" AND STATUS = '$selstatus'";
		$default_Q	=	"";
	}
	if($txtfrom != "")
	{
		$DATE_Q	=	" AND $selDtype BETWEEN '$txtfrom 00:00:00' AND '$txtto 23:59:59'";
		$default_Q	=	"";	
	}
	$GETMTO		=	"SELECT `TRANSNO`, `MTONO`, `ARSNO`, `STATUS`, `PCWORKER`, `RECEIVEDDATE`, `ISSUEDDATE` FROM WMS_NEW.MTO_RAWHDR 
					 WHERE (SUBSTRING(MTONO,1,1) = 'R' OR SUBSTRING(MTONO,1,1) = 'I') AND DESTINATION = 'PIECEWORK' $default_Q $MTONUM_Q $txtpcwno_Q $STATUS_Q $DATE_Q";
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
						<th>PIF No.</th>
						<th>MTO No.</th>
						<th>ARS</th>
						<th>Pieceworker</th>
						<th>Status</th>
						<th>Received Date</th>
						<th>Issued Date</th>
						<th>Action</th>
					</tr>
				<thead>
				<tbody>";
			$cnt = 1;
			while (!$RSGETMTO->EOF)
			{
				$TRANSNO			=	$RSGETMTO->fields["TRANSNO"];
				$MTONO				=	$RSGETMTO->fields["MTONO"];
				$ARSNO				=	$RSGETMTO->fields["ARSNO"];
				$STATUS				=	$RSGETMTO->fields["STATUS"];
				$PCWORKER			=	$RSGETMTO->fields["PCWORKER"];
				$PCWORKER			=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","DESCRIPTION","RECID = '$PCWORKER'");
				$RECEIVEDDATE		=	$RSGETMTO->fields["RECEIVEDDATE"];
				$ISSUEDDATE			=	$RSGETMTO->fields["ISSUEDDATE"];
				if($STATUS == "RECEIVED")
				{
					$btntissue	=	"<img src='/wms/images/images/action_icon/new/clipboard.png' class='smallbtns issuebtn tooltips' title='Issue: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btntissue	=	"";
				}
				if($STATUS == "ISSUED" or $STATUS == "PRINTED")
				{
					$btntdocument	=	"<img src='/wms/images/images/action_icon/new/document.png' class='smallbtns documentbtn tooltips' title='Print: $MTONO' data-trxno='$MTONO'>";
				}
				else 
				{
					$btntdocument	=	"";
				}
				
				echo "<tr class='trbody'>
								<td align='center'>$cnt</td>
								<td align='center'>$TRANSNO</td>
								<td align='center'>$MTONO</td>
								<td align='center'>$ARSNO</td>
								<td align='center'>$PCWORKER</td>
								<td align='center'>$STATUS</td>
								<td align='center'>$RECEIVEDDATE</td>
								<td align='center'>$ISSUEDDATE</td>
								<td align='center'>
									$btntdocument $btntissue
								</td>
						   </tr>";
					$cnt++;
				$RSGETMTO->MoveNext();
			}
			echo "</tbody>
			</table>
			</form>";
		}
	}
	exit();
}
if($action == "Q_SEARCHPCW")
{
	$txtpcwno 		= $_GET["PCWNO"];
	$txtpcwdesc 	= $_GET["PCWDESC"];
	$sel =	"SELECT `CODE`, `DESCRIPTION` FROM  WMS_LOOKUP.PIECEWORKER
			 WHERE 1";
	if ($txtpcwno != "") 
	{
		$sel	.=	" AND CODE like '%{$txtpcwno}%' ";
	}
	if ($txtpcwdesc != "") 
	{
		$sel	.=	" AND DESCRIPTION like '%{$txtpcwdesc}%' ";
	}
		$sel	.=	" limit 20 ";
//		echo "$sel";
	$rssel	=	$conn_255_10->Execute($sel);
	if ($rssel == false) 
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"RAW ISSUANCE-PIECEWORKER","Q_SEARCHPCW");
		$DATASOURCE->displayError();
	}
	$cnt	=	$rssel->RecordCount();
	if ($cnt > 0) 
	{
		echo "<select id='selpcw' class = 'C_dropdown divsel' style='width:532px;height:auto;' onkeypress='smartselpcw(event);' multiple>";
		while (!$rssel->EOF) 
		{
			$CODE		=	$rssel->fields['CODE'];
			$DESCRIPTION=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['DESCRIPTION']);
			$cValue		=	$CODE."|".$DESCRIPTION;
			echo "<option value=\"$cValue\" onclick=\"smartselpcw('click');\">$CODE-$DESCRIPTION</option>";
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
if($action == "ISSUEMTO")
{
	$MTONO	=	$_GET["MTONO"];
	$PCW	=	$_GET["PCW"];
	$ARS	=	$_GET["ARS"];
	$PIF	=	getPIF($conn_255_10);
	$conn_255_10->StartTrans();
	$ISSUEMTO	=	"UPDATE WMS_NEW.MTO_RAWHDR SET `TRANSNO`='$PIF', `ARSNO`='$ARS', `STATUS`='ISSUED', `PCWORKER`='$PCW',`ISSUEDBY`='{$_SESSION['username']}',
					`ISSUEDDATE` = '$today', `ISSUEDTIME`='$TIME'
					 WHERE MTONO	=	'$MTONO'";
	$RSISSUEMTO	=	$conn_255_10->Execute($ISSUEMTO);
	if($RSISSUEMTO == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$ISSUEMTO,$_SESSION['username'],"RAW ISSUANCE-PIECEWORKER","ISSUEMTO");
		$DATASOURCE->displayError();
	}
	else 
	{
		$INSERTTOPCW	=	"INSERT INTO WMS_NEW.MTO_PCWHDR(`MTONO`, `TRANS_NO`, `ARS_NO`, `PIECEWORKER`, `STATUS`)
							 VALUES('$MTONO','$PIF','$ARS','$PCW','')";
		$RSINSERTTOPCW	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTTOPCW,$user,"RAW ISSUANCE-PIECEWORKER","ISSUEMTO");
		if($RSINSERTTOPCW)
		{
			$GETDTLS	=	"SELECT SKUNO,DESCRIPTION,ITEMSTATUS,SUM(QTY) AS QTY,NO_OF_BOXES,NO_OF_PACK,BOXLABEL,UNITPRICE FROM WMS_NEW.MTO_RAWDTL
							 WHERE MTONO = '$MTONO' GROUP BY SKUNO";
			$RSGETDTLS	=	$conn_255_10->Execute($GETDTLS);
			if($RSGETDTLS == false)
			{
				$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
				$DATASOURCE->logError("wms",$errmsg,$GETDTLS,$_SESSION['username'],"RAW ISSUANCE-PIECEWORKER","ISSUEMTO");
				$DATASOURCE->displayError();
			}
			else 
			{
				while (!$RSGETDTLS->EOF) {
					$SKUNO 			= $RSGETDTLS->fields["SKUNO"]; 
					$DESCRIPTION 	= addslashes($RSGETDTLS->fields["DESCRIPTION"]); 
					$ITEMSTATUS 	= $RSGETDTLS->fields["ITEMSTATUS"]; 
					$QTY 			= $RSGETDTLS->fields["QTY"]; 
					$NO_OF_BOXES 	= $RSGETDTLS->fields["NO_OF_BOXES"]; 
					$NO_OF_PACK 	= $RSGETDTLS->fields["NO_OF_PACK"]; 
					$BOXLABEL 		= $RSGETDTLS->fields["BOXLABEL"]; 
					$UNITPRICE 		= $RSGETDTLS->fields["UNITPRICE"]; 
					if($QTY != 0)
					{
						$INSERTTOPCWDTLS	=	"INSERT INTO WMS_NEW.MTO_PCWDTL(`MTONO`,`SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`, `NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`)
												 VALUES('$MTONO','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$QTY','$NO_OF_BOXES','$NO_OF_PACK','$BOXLABEL','$UNITPRICE')";
						$RSINSERTTOPCWDTLS	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$INSERTTOPCWDTLS,$user,"RAW ISSUANCE-PIECEWORKER","ISSUEMTO");
					}
					$RSGETDTLS->MoveNext();
				}
			}
		}
		echo "<script>
					MessageType.infoMsg('MTO-$MTONO has been successfully issued to Piecework Section.');
					$('#divpcw_ars').dialog('close');
					$('#btnsearch').trigger('click');
			 </script>";
	}
	$conn_255_10->CompleteTrans();
	exit();
}
function getPIF($conn_255_10)
{
	$getMaxID	=	db_funcs::selval($conn_255_10,"WMS_NEW","MTO_RAWHDR","MAX(LINE_NO)","TRANSNO != ''");
	$getMaxPIF	=	db_funcs::selval($conn_255_10,"WMS_NEW","MTO_RAWHDR","TRANSNO","LINE_NO='$getMaxID'");
	$year_month	=	date("Ym");
	if($getMaxPIF != "")
	{
		$ym_arr	=	explode("-",$getMaxPIF);
		$ym		=	$ym_arr[0];
		if($ym == $year_month)
		{
			$oldctr	=	$ym_arr[1];
			$newctr	=	intval($oldctr) + 1;
		}
		else 
		{
			$newctr	=	1;
		}
	}
	else 
	{
		$newctr	=	1;
	}
	$newctr	=	str_pad($newctr,3,"0",STR_PAD_LEFT);
	$PIFNO	=	"$year_month-$newctr";
	return $PIFNO;
}
function getTblhdr()
{
	return "<table class='tblresult tablesorter'>
				<thead>
					<tr class='trheader' bgcolor='Teal'>
						<th>No.</th>
						<th>PIF No.</th>
						<th>MTO No.</th>
						<th>ARS</th>
						<th>Pieceworker</th>
						<th>Status</th>
						<th>Received Date</th>
						<th>Issued Date</th>
						<th>Action</th>
					</tr>
				<thead>
				<tbody>
					<tr class='trbody fnt-red'>
						<td align='center' colspan='8'>Nothing to display.</td>
				   </tr>
				  </tbody>
			</table>";
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/raw/issuance_pcw/issuance.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/raw/issuance_pcw/issuanceUI.php");
?>