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
		$dfrom		=	$_POST["dfrom"];
		$dto		=	$_POST["dto"];
//		$selcusttype=	$_POST["selcusttype"];
		$seldtype	=	$_POST["seldtype"];

		if($txtmposno != "")
		{
			$txtmposno_Q	=	" AND H.MPOSNO = '$txtmposno'";
		}
		if($dfrom != "" and $dto != "")
		{
			if($seldtype == "SCANDATE")
			{
				$DATE_Q	=	" AND H.SCANDATE BETWEEN '$dfrom' AND '$dto'";
			}
			else 
			{
				$DATE_Q	=	" AND M.MPOSDATE BETWEEN '$dfrom' AND '$dto'";
			}
				
		}
		$GETMPOS	=	"SELECT D.`SKUNO`, D.`DEFECTIVEQTY`,H.`MPOSNO`, H.`CUSTNO`, H.`SCANDATE`,M.`MPOSDATE` FROM WMS_NEW.SCANDATA_DTL AS D
						 LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO
						 LEFT JOIN WMS_NEW.MPOSHDR AS M ON M.MPOSNO = H.MPOSNO
						 WHERE D.DEFECTIVEQTY != '0' $txtmposno_Q $DATE_Q";
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
					 		<td >MPOS No.</td>
					 		<td >Reason</td>
					 		<td >Customer</td>
					 		<td >SKU No.</td>
					 		<td >SKU Description</td>	
					 		<td >MPOS DATE</td>	
					 		<td >SCANNED DATE</td>
					 		<td >Qty</td>		
					 		<td >Gross Amt.</td>	
					 	</tr>";
			$cnt=	1;
			$arrVAR		=	array()	;
			while (!$RSGETMPOS->EOF) {
				$SKUNO			=	$RSGETMPOS->fields["SKUNO"];
				$DEFECTIVEQTY	=	$RSGETMPOS->fields["DEFECTIVEQTY"];
				$MPOSNO			=	$RSGETMPOS->fields["MPOSNO"];
				$CUSTNO			=	$RSGETMPOS->fields["CUSTNO"];
				$SCANDATE		=	$RSGETMPOS->fields["SCANDATE"];
				$MPOSDATE		=	$RSGETMPOS->fields["MPOSDATE"];
				$SRP			=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSDTL","UNITPRICE","MPOSNO = '{$MPOSNO}' AND SKUNO='$SKUNO'");
				if($SRP == "")
				{
					$SRP			=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
				}
				$REASON			=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","REASON","MPOSNO = '{$MPOSNO}'");
				$GROSSAMOUNT	=	$SRP * $DEFECTIVEQTY;
				$CUSTNAME		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}'");
				$SKUDESC		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
				
				$arrVAR[$CUSTNO][$MPOSNO][$SKUNO]["CUSTOMER"]	=	"$CUSTNO-$CUSTNAME";
				$arrVAR[$CUSTNO][$MPOSNO][$SKUNO]["SKUDESC"]	=	$SKUDESC;
				$arrVAR[$CUSTNO][$MPOSNO][$SKUNO]["DEFECTIVEQTY"]	+=	$DEFECTIVEQTY;
				$arrVAR[$CUSTNO][$MPOSNO][$SKUNO]["SCANDATE"]	=	$SCANDATE;
				$arrVAR[$CUSTNO][$MPOSNO][$SKUNO]["MPOSDATE"]	=	$MPOSDATE;
				$arrVAR[$CUSTNO][$MPOSNO][$SKUNO]["GROSSAMOUNT"]	=	$GROSSAMOUNT;
				$arrVAR[$CUSTNO][$MPOSNO][$SKUNO]["REASON"]	=	$REASON;
				$RSGETMPOS->Movenext();
			}
		}
		foreach ($arrVAR as $CUSTCODE=>$val1)
		{
			$totqty	=	0;
			$totgrs	=	0;
			foreach ($val1 as $MPOSNO=>$val2)
			{
				foreach ($val2 as $ITEMNO=>$val3)
				{
					$CUSTOMER		=	$val3["CUSTOMER"];
					$SKUDESC		=	$val3["SKUDESC"];
					$DEFECTIVEQTY	=	$val3["DEFECTIVEQTY"];
					$SCANDATE		=	$val3["SCANDATE"];
					$MPOSDATE		=	$val3["MPOSDATE"];
					$GROSSAMOUNT	=	$val3["GROSSAMOUNT"];
					$REASON			=	$val3["REASON"];
					echo "<tr class='trdtls trbody' >
					 		<td align='center'>$MPOSNO</td>
					 		<td >$REASON</td>
					 		<td >$CUSTOMER</td>
					 		<td align='center'>$ITEMNO</td>
					 		<td >$SKUDESC</td>	
					 		<td align='center'>$MPOSDATE</td>	
					 		<td align='center'>$SCANDATE</td>	
					 		<td align='center'>$DEFECTIVEQTY</td>	
					 		<td align='right'>$GROSSAMOUNT</td>	
					 	</tr>";
					$totqty	+=	$DEFECTIVEQTY;
					$totgrs	+=	$GROSSAMOUNT;
				}
			}
			echo "		<tr class='trdtls trbody bld' >
					 		<td align='center' colspan='7'>TOTAL</td>
					 		<td align='center'>$totqty</td>	
					 		<td align='right'>$totgrs</td>	
					 	</tr>";
		}
				
		echo "</table>
			 <br>
			 <button type='button' id='btncsv' class='btncsv'>CSV</button>";
		$_SESSION["arrVAR"]	=	$arrVAR;
		exit();
	}
				
	function getTBLprev()
	{
		return "<table border='1'class='tblresult tablesorter'>
					<tr class='trheader'>
				 		<td >MPOS No.</td>
				 		<td >Reason</td>
				 		<td >Customer</td>
				 		<td >SKU No.</td>
				 		<td >SKU Description</td>	
				 		<td >Qty</td>	
				 		<td >Gross Amt.</td>	
				 	</tr>
			 		<tr class='trbody centered fnt-red'>
				 		<td colspan='7'>Nothing to display.</td>
				 	</tr>
				 </table>";
	}
?>
<script>
$("document").ready(function(){
	$("#btnreport").click(function(event,mainquery){
		var errmsg		=	"";
		var dataform	=	$("#dataform").serialize();
		$.ajax({
			type:	"POST",
			data:	dataform,
			url:	"defective.php?action=GETMPOS",
			beforeSend:function(){
				$("#divloader").dialog("open");
			},
			success:function(response){
				$("#divMPOS").html(response);
				$("#divloader").dialog("close");
				$(".btncsv").button({icons: {primary: "ui-icon ui-icon-note"}});
				$(".btnpdf").button({icons: {primary: "ui-icon ui-icon-document"}});
			}
		});
	});
	$("#divMPOS").on("click","#btncsv",function(){
		window.open("defective_csv.php");
	});
//	$("#divMPOS").on("click","#btnpdf",function(){
//		window.open("defective_pdf.php");
//	});
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
<!--						<tr>
							<td>CUSTOMER TYPE</td>
							<td>
								:<select id="selcusttype" name="selcusttype">
									<option value=""><-- Please Select --><!--</option>
									<option value="TRADE">TRADE</option>
									<option value="NBS">NBS</option>
								</select>-->
							<!--</td>
						</tr>-->
						<!--<tr>
							<td>REASON</td>
							<td>
								<?php 
								$GETREASON	=	"SELECT DISTINCT(REASON) FROM WMS_NEW.`MPOSHDR` WHERE REASON != ''";
								$RSGETREASON	=	$conn_255_10->Execute($GETREASON);
								if($RSGETREASON== false)
								{
									$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
									$DATASOURCE->logError("wms",$errmsg,$GETREASON,$_SESSION['username'],"VARIANCE REPORT","REASON");
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
						</tr>-->
						
						<tr>
							<td>DATE TYPE</td>
							<td>
								:<select id="seldtype" name="seldtype">
									<option value="SCANDATE">SCANNED DATE</option>
									<option value="MPOSDATE">MPOS DATE</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>DATE RANGE</td>
							<td>
								:<input type="text" name="dfrom" id="dfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;TO&nbsp;&nbsp;
							 	<input type="text" name="dto" 	id="dto" 	class="dates"	value="" size="10"  placeholder = "To"	>
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