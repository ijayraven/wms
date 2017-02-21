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
if($action == "GETMPOS")
	{
		$txtmposno	=	$_POST["txtmposno"];
		$mposdfrom	=	$_POST["mposdfrom"];
		$mposdto	=	$_POST["mposdto"];
		$posteddfrom=	$_POST["posteddfrom"];
		$posteddto	=	$_POST["posteddto"];
		$selreason	=	$_POST["selreason"];
		$selcusttype=	$_POST["selcusttype"];
		
		$_SESSION["mposdfrom"]	=	$mposdfrom;
		$_SESSION["mposdto"]	=	$mposdto;
		$_SESSION["posteddfrom"]=	$posteddfrom;
		$_SESSION["posteddto"]	=	$posteddto;
		
		if ($txtmposno != "") {
			$txtmposno_Q	=	" AND H.MPOSNO = '{$txtmposno}'";
//			$STATUS			=	$DATASOURCE->selval($conn_255_10,"WMS_NEW "," MPOSHDR","STATUS","MPOSNO= '{$txtmposno}'");
//			if($STATUS == "")
//			{
//				echo "<script>alert('MPOS item/s is/are not yet scanned.');</script>";
//				exit();
//			}
//			elseif ($STATUS == "SCANNED")
//			{
//				echo "<script>alert('Scanned MPOS is not yet posted.');</script>";
//				exit();
//			}
		}
		if ($selreason != "ALL") {
			$selreason_Q	=	" AND H.REASON = '{$selreason}'";
		}
		if ($mposdfrom != "") {
			$mposdate_Q		=	" AND H.MPOSDATE BETWEEN '{$mposdfrom}' AND '{$mposdto}'";
		}
		if ($posteddfrom != "") {
			$posteddate_Q		=	" AND S.SCANDATE BETWEEN '{$posteddfrom}' AND '{$posteddto}'";
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
			$GETMPOS	=	"SELECT H.TRANSNO,H.MPOSNO,H.CUSTNO,H.MPOSDATE,H.REASON,H.TOTALQTY,H.GROSSAMOUNT,S.SCANDATE,S.QTY, S.SKUNO
							 FROM WMS_NEW.`MPOSHDR` AS H 
							 LEFT JOIN  FDCRMSlive.SCANDATA AS S ON S.REFNO = H.MPOSNO
							 $selcusttype_J
							 WHERE S.ISDELETED= 'N'
							 $txtmposno_Q $mposdate_Q $selreason_Q $posteddate_Q $selcusttype_Q";
//		echo $GETMPOS; 
//		exit();
		$RSGETMPOS	=	$conn_255_10->Execute($GETMPOS);
		if($RSGETMPOS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMPOS,$_SESSION['username'],"VARIANCE REPORT OLD SCANNING","GETMPOS");
			$DATASOURCE->displayError();
		}
		else 
		{
			if($RSGETMPOS->RecordCount() == 0)
			{
				echo getTBLprev();exit();
			}
			
			$arrVAR		=	array()	;
			while (!$RSGETMPOS->EOF) {
				$CUSTNO		=	$RSGETMPOS->fields["CUSTNO"];
				$CUSTNAME	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}'");
				$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];
				$MPOSDATE	=	$RSGETMPOS->fields["MPOSDATE"];
				$POSTEDDATE	=	$RSGETMPOS->fields["SCANDATE"];
				$SALESREPNO	=	$RSGETMPOS->fields["SALESREPNO"];
				$REASON		=	$RSGETMPOS->fields["REASON"];
				$TOTALQTY	=	$RSGETMPOS->fields["TOTALQTY"];
				$GROSSAMOUNT=	$RSGETMPOS->fields["GROSSAMOUNT"];
				$SKUNO		=	$RSGETMPOS->fields["SKUNO"];
				$POSTEDQTY	=	$RSGETMPOS->fields["QTY"];
				$SRP		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSDTL","UNITPRICE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$POSTEDAMT	=	$POSTEDQTY * $SRP;
				
				$arrVAR[$MPOSNO]["CUSTNO"]		=	"$CUSTNO-$CUSTNAME";
				$arrVAR[$MPOSNO]["MPOSDATE"]	=	$MPOSDATE;
				$arrVAR[$MPOSNO]["POSTEDDATE"]	=	$POSTEDDATE;
				$arrVAR[$MPOSNO]["SALESREPNO"]	=	$SALESREPNO;
				$arrVAR[$MPOSNO]["REASON"]		=	$REASON;
				$arrVAR[$MPOSNO]["TOTALQTY"]	=	$TOTALQTY;
				$arrVAR[$MPOSNO]["GROSSAMOUNT"]	=	$GROSSAMOUNT;
				$arrVAR[$MPOSNO]["POSTEDQTY"]	+=	$POSTEDQTY;
				$arrVAR[$MPOSNO]["POSTEDAMT"]	+=	$POSTEDAMT;
				$RSGETMPOS->Movenext();
			}
			echo "<table border='1' class='tblresult'>
						<tr class='trheader'>
					 		<td >No.</td>
					 		<td >Customer</td>
					 		<td >MPOS No.</td>
					 		<td >MPOS Date</td>	
					 		<td >Scanned Date</td>	
					 		<td >Reason</td>	
					 		<td >MPOS Qty</td>	
					 		<td >Scanned Qty</td>	
					 		<td >MPOS Amount</td>	
					 		<td >Scanned Amount</td>	
					 	</tr>";
			$cnt=	1;
			foreach ($arrVAR as $mpos=>$val1)
			{
				
				$CUSTNO		=	$val1["CUSTNO"];
				$MPOSDATE	=	$val1["MPOSDATE"];
				$POSTEDDATE	=	$val1["POSTEDDATE"];
				$REASON		=	$val1["REASON"];
				$TOTALQTY	=	$val1["TOTALQTY"];
				$POSTEDQTY	=	$val1["POSTEDQTY"];
				$GROSSAMOUNT=	$val1["GROSSAMOUNT"];
				$POSTEDAMT	=	$val1["POSTEDAMT"];
				echo "<tr class='trdtls trbody' id='trdtls$cnt' title='Click to view details' data-mposno='$MPOSNO' data-cnt='$cnt'>
					 		<td class='tdmposdtls' height='30px' align='center'>$cnt</td>
					 		<td class='tdmposdtls'>$CUSTNO</td>
					 		<td class='tdmposdtls' align='center'>$mpos</td>
					 		<td class='tdmposdtls' align='center'>$MPOSDATE</td>	
					 		<td class='tdmposdtls' align='center'>$POSTEDDATE</td>	
					 		<td class='tdmposdtls' align='center'>$REASON</td>	
					 		<td class='tdmposdtls' align='center'>".number_format($TOTALQTY)."</td>	
					 		<td class='tdmposdtls' align='center'>".number_format($POSTEDQTY)."</td>	
					 		<td class='tdmposdtls' align='right'>".number_format($GROSSAMOUNT,2)."</td>	
					 		<td class='tdmposdtls' align='right'>".number_format($POSTEDAMT,2)."</td>	
					 	</tr>
					 	<tr>
						 		<td id='tdmposdtls$cnt' colspan='10' class='tdmposdtlsClass trbody' align='center'></td>
						</tr>";
				$cnt++;
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
			$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"VARIANCE REPORT OLD SCANNING","VIEWMPOSDTLS");
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
					 		<th >Scanned Qty</th>
					 		<th >MPOS Amount</th>
					 		<th >Scanned Amount</th>
					 	</thead>
					 <tbody>";
			$cnt	=	1;
			$totqty	=	0;
			$totamt	=	0;
			$totsqty	=	0;
			$totsamt	=	0;
			
			while (!$RSGETMPOSDTLS->EOF) {
				$MPOSNO		=	$RSGETMPOSDTLS->fields["MPOSNO"];
				$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
				$SKUNODESC	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
				$QTY		=	$RSGETMPOSDTLS->fields["QTY"];
				$UNITPRICE	=	$RSGETMPOSDTLS->fields["UNITPRICE"];
				$GROSSAMOUNT=	$RSGETMPOSDTLS->fields["GROSSAMOUNT"];
				$F_QTY		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive ","SCANDATA","SUM(QTY)","SKUNO = '{$SKUNO}' AND REFNO = '{$MPOSNO}'");
				$RECAMT		=	$F_QTY * $UNITPRICE;
				
					echo "<tr class='tblresul-tbltdtls-dtls'>
					 		<td align='center'>
					 			$cnt
							</td>
					 		<td>$SKUNO</td>
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
				 		<td >Scanned Date</td>	
				 		<td >Reason</td>	
				 		<td >MPOS Qty</td>	
				 		<td >Scanned Qty</td>	
				 		<td >MPOS Amount</td>	
				 		<td >Scanned Amount</td>	
				 	</tr>
			 		<tr class='trbody centered fnt-red'>
				 		<td colspan='11'>Nothing to display.</td>
				 	</tr>
				 </table>";
	}

?>
<script>
$("document").ready(function(){
	$("#btnreport").click(function(event,mainquery){
		var errmsg		=	"";
		var dataform	=	$("#dataform").serialize();
		if($("#txtmposno").val() == "")
		{
			if($("#mposdfrom").val() == "" && $("#mposdto").val() == "" && $("#posteddfrom").val() == "" &&  $("#posteddto").val() == "")
			{
				errmsg	+=	"Please input at least one date range.\n";
			}
			if($("#mposdfrom").val() > $("#mposdto").val())
			{
				errmsg	+=	"Invalid MPOS date range.\n";
			}
			if($("#posteddfrom").val() > $("#posteddto").val())
			{
				errmsg	+=	"Invalid SCANNED date range.\n";
			}
		}
		if(errmsg == "")
		{
			$.ajax({
				type:	"POST",
				data:	dataform,
				url:	"variance.php?action=GETMPOS",
				beforeSend:function(){
					$("#divloader").dialog("open");
				},
				success:function(response){
					$("#divMPOS").html(response);
					$("#divloader").dialog("close");
					$(".tdmposdtlsClass").hide();
					$(".btncsv").button({icons: {primary: "ui-icon ui-icon-note"}});
					$(".btnpdf").button({icons: {primary: "ui-icon ui-icon-document"}});
				}
			});
		}
		else
		{
			MessageType.infoMsg(errmsg);
		}
	});
	$("#divMPOS").on("click",".trdtls",function(){
		var MPOSNO	=	$(this).attr("data-mposno");
		var COUNT	=	$(this).attr("data-cnt");
		var tdtext	=	$("#tdmposdtls"+COUNT).html();
			tdtext	=	tdtext.trim();
		if(tdtext == "")
		{	
		    $.ajax({
				type	:	"GET",
				url		:	"variance.php?action=VIEWMPOSDTLS&MPOSNO="+MPOSNO+"&COUNT="+COUNT,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					
					$(".tdmposdtlsClass").html("");
					$("#tdmposdtls"+COUNT).html(response);
					$(".tditemdtlsClass").hide();
					$("#tdmposdtls"+COUNT).show();
					$(".trdtls").removeClass("activetr");
					$("#trdtls"+COUNT).addClass("activetr");
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter();
				}
			});
		}
		else
		{
			$(".tdmposdtlsClass").hide();
			$("#trdtls"+COUNT).removeClass("activetr");
			$("#tdmposdtls"+COUNT).html("");
		}
	});
	$("#divMPOS").on("click","#btncsv",function(){
		window.open("variance_csv.php");
	});
	$("#divMPOS").on("click","#btnpdf",function(){
		window.open("variance_pdf.php");
	});
});
</script>
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0"  class="Text_header">
			<tr>
				<td align="center" class="tdoptions">
					<table border="0"class="label_text">
						<tr>
							<td>MPOS NO.</td>
							<td>
								:<input type="text" id="txtmposno" name="txtmposno" placeholder="MPOS No." class="centered">
							</td>
						</tr>
						<tr>
							<td>CUSTOMER TYPE</td>
							<td>
								:<select id="selcusttype" name="selcusttype">
									<option value=""><-- Please Select --></option>
									<option value="TRADE">TRADE</option>
									<option value="NBS">NBS</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>REASON</td>
							<td>
								<?php 
								$GETREASON	=	"SELECT DISTINCT(REASON) FROM WMS_NEW.`MPOSHDR` WHERE REASON != ''";
								$RSGETREASON	=	$conn_255_10->Execute($GETREASON);
								if($RSGETREASON== false)
								{
									$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
									$DATASOURCE->logError("wms",$errmsg,$GETREASON,$_SESSION['username'],"VARIANCE REPORT OLD SCANNING","REASON");
									$DATASOURCE->displayError();
								}
								?>
								:<select id="selreason" name="selreason">
									<option value="ALL">All</option>
									<?php 
									while (!$RSGETREASON->EOF) {
										
										$REASON 	=	$RSGETREASON->fields["REASON"];
										echo "<option value='$REASON'>$REASON</option>";
										$RSGETREASON->MoveNext();
									}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td>MPOS DATE</td>
							<td>
								:<input type="text" name="mposdfrom" id="mposdfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;TO&nbsp;&nbsp;
							 	<input type="text" name="mposdto" 	id="mposdto" 	class="dates"	value="" size="10"  placeholder = "To"	>
							</td>
						</tr>
						<tr>
							<td>SCANNED DATE</td>
							<td>
								:<input type="text" name="posteddfrom" id="posteddfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;TO&nbsp;&nbsp;
							 	<input type="text" name="posteddto" 	id="posteddto" 	class="dates"	value="" size="10"  placeholder = "To">
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								&nbsp;<button type="button" id="btnreport" class="btnsearch">Search</button>
							</td>
						</tr>
					</table>
					<br>
				</td>
			</tr>
			<tr>
				<td align="center" class="td-result">
					<div id="divMPOS"><?php echo getTBLprev(); ?></div>
				</td>
			</tr>
		</table>
	</form>