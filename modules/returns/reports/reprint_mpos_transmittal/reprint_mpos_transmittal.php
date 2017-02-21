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
if($action == "SEARCHMPOS")
{
	if ($_POST["selcustype"] == "NBS")
	{
		$CUSTYPE_Q	=	" AND C.CustomerBranchCode != ''";
	}
	else if ($_POST["selcustype"] == "TRADE")
	{
		$CUSTYPE_Q	=	" AND C.CustomerBranchCode = ''";
	}
	else 
	{
		$GETNONREV		=	"SELECT `CUSTNO`, CUSTDESC FROM WMS_LOOKUP.NONREVENUE_CUST";
		$RSGETNONREV	=	$conn_255_10->Execute($GETNONREV);
		if($RSGETNONREV == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETNONREV,$_SESSION['username'],"REPRINT MPOS TRANSMITTAL","SEARCHMPOS");
			$DATASOURCE->displayError();
		}
		else 
		{
			while (!$RSGETNONREV->EOF) 
			{
				$nonrevcust	=	$RSGETNONREV->fields["CUSTNO"];
				$listnonrev	.=	",'$nonrevcust'";
				$RSGETNONREV->MoveNext();
			}
		}
		$listnonrev = substr($listnonrev,1);
		$CUSTYPE_Q	=	" AND H.CUSTNO IN ($listnonrev)";
	}
	$GETSCANNEDMPOS	=	"SELECT H.*,C.CustName FROM WMS_NEW. SCANDATA_HDR AS H 
						 LEFT JOIN FDCRMSlive.custmast AS C ON C.CustNo = H.CUSTNO
						 WHERE H.TRANSMITBY != '' AND POSTEDDATE BETWEEN '{$_POST["posteddfrom"]}' AND '{$_POST["posteddto"]}' $CUSTYPE_Q";
	$RSGETSCANNEDMPOS	=	$conn_255_10->Execute($GETSCANNEDMPOS);
	if($RSGETSCANNEDMPOS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETSCANNEDMPOS,$_SESSION['username'],"REPRINT MPOS TRANSMITTAL","SEARCHMPOS");
		$DATASOURCE->displayError();
	}
	else 
	{
		$arrMPOS	=	array();
		while (!$RSGETSCANNEDMPOS->EOF) {
			$MPOSNO 	= $RSGETSCANNEDMPOS->fields["MPOSNO"]; 
			$CUSTCODE 	= $RSGETSCANNEDMPOS->fields["CUSTNO"]; 
			$CustName	= $RSGETSCANNEDMPOS->fields["CustName"]; 
			$SCANNEDAMT	= $RSGETSCANNEDMPOS->fields["POSTEDGROSSAMOUNT"];
			$MPOSAMOUNT	= $DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","GROSSAMOUNT","MPOSNO= '{$MPOSNO}'");
			
			$arrMPOS[$MPOSNO]["CUSTOMER"]	=	"$CUSTCODE($CustName)";
			$arrMPOS[$MPOSNO]["MPOSAMT"]	+=	$MPOSAMOUNT;
			$arrMPOS[$MPOSNO]["SCANNEDAMT"]	+=	$SCANNEDAMT;
			$RSGETSCANNEDMPOS->MoveNext();
		}
		echo "<table border='1' class='tblresult tablesorter'>
				<thead>
					<tr class='trheader'>
				 		<th >No.</th>
				 		<th >MPOS No.</th>
				 		<th >Customer</th>
				 		<th >MPOS Amount</th>
				 		<th >Scanned Amount</th>
				 	</tr>
				</thead>
				<tbody>";
		$cnt	=	1;
		foreach ($arrMPOS as $MPOSNO=>$val1)
		{
			$customer 	= $val1["CUSTOMER"];
			$MPOSAMT	= $val1["MPOSAMT"];
			$SCANNEDAMT	= $val1["SCANNEDAMT"];
			
			echo "<tr class='trbody'>
				 		<td align='center'>$cnt</td>
				 		<td align='center'>$MPOSNO</td>
				 		<td align='left'>$customer</td>
				 		<td align='right'>".number_format($MPOSAMT,2)."</td>
				 		<td align='right'>".number_format($SCANNEDAMT,2)."</td>
				 	</tr>";
			$cnt++;
		}
		echo "</tbody>
			</table>";
	}
	exit();
}
function getTBLprev()
{
	return "<table border='1'class='tblresult tablesorter'>
				<thead>
					<tr class='trheader'>
				 		<th >No.</th>
				 		<th >MPOS No.</th>
				 		<th >Customer</th>
				 		<th >MPOS Amount</th>
				 		<th >Scanned Amount</th>
				 	</tr>
				</thead>
		 		<tr class='trbody centered fnt-red'>
			 		<td colspan='11'>Nothing to display.</td>
			 	</tr>
			 </table>";
}
?>
<script>
$("document").ready(function(){
	$("#btnsearch").click(function(){
		var dataform = $("#dataform").serialize();
		var posteddfrom	=	$("#posteddfrom").val();
		var posteddto	=	$("#posteddto").val();
		var selcustype	=	$("#selcustype").val();
		var errmsg		=	"";
		if(selcustype == "" && posteddfrom == "" && posteddto == "")
		{
			errmsg	=	"Nothing to search.";
		}
		else
		{
			if(selcustype == "")
			{
				errmsg	+=	" - Please select Customer Type.<br>";
			}
			if(posteddfrom == "" && posteddto == "")
			{
				errmsg	+=	" - Specify date range.<br>";
			}
			if(posteddfrom > posteddto || (posteddfrom == "" && posteddto != "") )
			{
				errmsg	+=	" - Invalid date range.<br>";
			}
		}
		if(errmsg == "")
		{
			$.ajax({
				data		:dataform,
				type		:"POST",
				url			:"reprint_mpos_transmittal.php?action=SEARCHMPOS",
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divMPOS").html(response);
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter();
				}
			});
		}
		else
		{
			MessageType.infoMsg(errmsg);
		}
	});
	$("#btngenerate").click(function(){
		var posteddfrom	=	$("#posteddfrom").val();
		var posteddto	=	$("#posteddto").val();
		var selcustype	=	$("#selcustype").val();
		var errmsg		=	"";
		if(selcustype == "" && posteddfrom == "" && posteddto == "")
		{
			errmsg	=	" - Nothing to search.<br>";
		}
		else
		{
			if(selcustype == "")
			{
				errmsg	+=	" - Please select Customer Type.<br>";
			}
			if(posteddfrom == "" && posteddto == "")
			{
				errmsg	+=	" - Specify date range.<br>";
			}
			if(posteddfrom > posteddto || (posteddfrom == "" && posteddto != "") )
			{
				errmsg	+=	" - Invalid date range.";
			}
		}
		if(errmsg == "")
		{
			window.open("reprint_mpos_transmittal_pdf.php?CUSTYPE="+selcustype+"&POSTEDDFROM="+posteddfrom+"&POSTEDDTO="+posteddto);
		}
		else
		{
			MessageType.infoMsg(errmsg);
		}
	});
});
</script>
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0"  class="Text_header">
			<tr>
				<td align="center" class="tdoptions">
					<table border="0"class="label_text">
						<tr>
							<td>CUSTOMER TYPE</td>
							<td>
								:<select id="selcustype" name="selcustype">
									<option value=""><-- Please Select --></option>
									<option value="NBS">NBS</option>
									<option value="TRADE">TRADE</option>
									<option value="NON-REVENUE">NON-REVENUE</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>POSTED DATE</td>
							<td>
								:<input type="text" name="posteddfrom" id="posteddfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;TO&nbsp;&nbsp;
							 	<input type="text" name="posteddto" 	id="posteddto" 	class="dates"	value="" size="10"  placeholder = "To"	>
							</td>
						</tr>
						<tr>	
							<td>&nbsp;</td>
							<td>
								&nbsp;<button type="button" id="btnsearch" class="btnsearch">Search</button>
								&nbsp;<button type="button" id="btngenerate" class="btntransmit">Transmit</button>
							</td>
						</tr>
					</table>
					<br>
				</td>
			</tr>
			<tr>
				<td align="center" class="td-result">
					<div id="divMPOS"><?php echo getTBLprev();?></div>
				</td>
			</tr>
		</table>
	</form>
