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
		$txtmposno	=	$_POST["txtmposno"];
		$txttrxno	=	$_POST["txttrxno"];
		$selcusttype=	$_POST["selcusttype"];
		$rdcusttype	=	$_POST["rdcusttype"];
		$txtcustno	=	$_POST["txtcustno"];
		$selbrand	=	$_POST["selbrand"];
		$mposdfrom	=	$_POST["mposdfrom"];
		$mposdto	=	$_POST["mposdto"];
		$scandfrom	=	$_POST["scandfrom"];
		$scandto	=	$_POST["scandto"];
		$pickup		=	$_POST["chkforpickup"];
		$picked		=	$_POST["chkpicked"];
		$scanned	=	$_POST["chkscanned"];
		$created	=	$_POST["chkcreated"];
		$selreason	=	$_POST["selreason"];
		$selclass	=	addslashes($_POST["selclass"]);
		
		$_SESSION["mposdfrom"]	=	$mposdfrom;
		$_SESSION["mposdto"]	=	$mposdto;
		$_SESSION["scandfrom"]	=	$scandfrom;
		$_SESSION["scandto"]	=	$scandto;
		
		if ($txtmposno != "") {
			$txtmposno_Q	=	" AND H.MPOSNO = '{$txtmposno}'";
		}
		if ($txttrxno != "") {
			$txttrxno_Q		=	" AND H.TRANSNO = '{$txttrxno}'";	
		}
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
		if ($selbrand != "ALL") {
			$selbrand_Q		=	" AND D.BRAND = '{$selbrand}'";	
		}
		if ($mposdfrom != "") {
			$mposdate_Q		=	" AND H.MPOSDATE BETWEEN '{$mposdfrom}' AND '{$mposdto}'";
		}
		if ($scandfrom != "") {
			$scandate_Q		=	" AND S.SCANDATE BETWEEN '{$scandfrom}' AND '{$scandto}'";
		}
		if($selreason != "ALL")
		{
			$selreason_Q	=	" AND H.REASON	=	'{$selreason}'";
		}
		if($selclass != "ALL")
		{
			$selclass_Q	=	" AND D.CLASS = '{$selclass}'";
		}
		
		if($scanned == "scanned")
		{
			$SCANNEDLEFTJOIN	=	"LEFT JOIN FDCRMSlive.SCANDATA AS S ON S.REFNO = H.MPOSNO";
			$SCANNEDFIELDS		=	",S.SCANDATE";
			$SCANNEDWHERE		=	" AND S.ISDELETED = 'N' AND S.REFNO != '' ";
			$statuslist_Q		=	"AND H.PICK IN ('Y') ";	
		}
		if($pickup == "N" or $picked == "Y")
		{
			$statuslist_Q		=	"AND H.PICK IN ('$pickup','$picked') ";	
		}
		$GETMPOS	=	"SELECT H.TRANSNO,H.MPOSNO,H.CUSTNO,H.MPOSDATE,SUM(D.QTY) AS TOTALQTY ,SUM(D.GROSSAMOUNT)AS GROSS ,SUM(D.NETAMOUNT) AS NET,H.PICK,H.REASON,D.BRAND,D.CLASS,C.CustName $SCANNEDFIELDS
						 FROM WMS_NEW.`MPOSHDR` AS H 
						 LEFT JOIN WMS_NEW.MPOSDTL AS D ON D.TRANSNO = H.TRANSNO
						 $SCANNEDLEFTJOIN
						 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = H.CUSTNO
						 WHERE 1  $statuslist_Q $SCANNEDWHERE
						 $txtmposno_Q $txttrxno_Q $selcusttype_Q $rdcusttype_Q $txtcustno_Q $selbrand_Q $mposdate_Q $scandate_Q $selreason_Q $selclass_Q
						 GROUP BY H.TRANSNO,H.MPOSNO,H.CUSTNO,D.BRAND,D.CLASS
						 ORDER BY H.TRANSNO,H.MPOSNO";
		
//		$GETMPOS	=	"SELECT H.TRANSNO,H.MPOSNO,H.CUSTNO,H.MPOSDATE,H.TOTALQTY,H.GROSSAMOUNT,H.PICK,H.REASON,D.BRAND,C.CustName $SCANNEDFIELDS
//						 FROM WMS_NEW.`MPOSHDR` AS H 
//						 LEFT JOIN WMS_NEW.MPOSDTL AS D ON D.TRANSNO = H.TRANSNO
//						 $SCANNEDLEFTJOIN
//						 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = H.CUSTNO
//						 WHERE 1  $statuslist_Q $SCANNEDWHERE
//						 $txtmposno_Q $txttrxno_Q $selcusttype_Q $rdcusttype_Q $txtcustno_Q $selbrand_Q $mposdate_Q $scandate_Q $selreason_Q
//						 GROUP BY H.TRANSNO,H.MPOSNO,H.CUSTNO,D.BRAND,
//						 ORDER BY H.TRANSNO,H.MPOSNO";
//		echo $GETMPOS; 
//		exit();
		$RSGETMPOS	=	$conn_255_10->Execute($GETMPOS);
		if($RSGETMPOS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMPOS,$_SESSION['username'],"MPOS MONITORING","GETMPOS");
			$DATASOURCE->displayError();
		}
		else 
		{
			if($RSGETMPOS->RecordCount() == 0)
			{
				echo getTBLprev();exit();
			}
			$arrMpos	=	array();
			while (!$RSGETMPOS->EOF) {
				$TRANSNO	=	$RSGETMPOS->fields["TRANSNO"];
				$CUSTNO		=	$RSGETMPOS->fields["CUSTNO"];
				$CustName	=	$RSGETMPOS->fields["CustName"];
				$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];
				$MPOSDATE	=	$RSGETMPOS->fields["MPOSDATE"];
				$SCANDATE	=	$RSGETMPOS->fields["SCANDATE"];
				$BRAND		=	$RSGETMPOS->fields["BRAND"];
				$CLASS		=	$RSGETMPOS->fields["CLASS"];
				$PICK		=	$RSGETMPOS->fields["PICK"];
				$TOTALQTY	=	$RSGETMPOS->fields["TOTALQTY"];
				$REASON		=	$RSGETMPOS->fields["REASON"];
				if($PICK == "Y"){
					$addstat=	"PICKED";
				}
				else {
					$addstat=	"FOR PICK-UP";
				}
				if(($picked != "Y" or $pickup != "N") and $scanned == "scanned")
				{
					$STATUS =	"SCANNED";
				}
				if(($picked == "Y" or $pickup == "N") and $scanned == "scanned")
				{
					$STATUS =	"$addstat-SCANNED";
				}
				if(($picked == "Y" or $pickup == "N") and $scanned != "scanned")
				{
					$STATUS =	"$addstat";
				}
				$BRAND_NAME	=	$DATASOURCE->selval($conn_250_171,"FDC_PMS","BRAND_NEW","BRAND_NAME","BRAND_ID = '{$BRAND}'");
				$GROSSAMOUNT=	$RSGETMPOS->fields["GROSS"];
				$NETAMOUNT	=	$RSGETMPOS->fields["NET"];
				$OLDMPOS	=	$MPOSNO;
				$OLDTRANSNO	=	$TRANSNO;
				$arrMpos[$CUSTNO][$TRANSNO][$MPOSNO][$BRAND][$CLASS]["CustName"]	=	"$CUSTNO-$CustName";
				$arrMpos[$CUSTNO][$TRANSNO][$MPOSNO][$BRAND][$CLASS]["MPOSDATE"]	=	$MPOSDATE;
				$arrMpos[$CUSTNO][$TRANSNO][$MPOSNO][$BRAND][$CLASS]["SCANDATE"]	=	$SCANDATE;
				$arrMpos[$CUSTNO][$TRANSNO][$MPOSNO][$BRAND][$CLASS]["BRANDNAME"]	=	$BRAND_NAME;
				$arrMpos[$CUSTNO][$TRANSNO][$MPOSNO][$BRAND][$CLASS]["STATUS"]		=	$STATUS;
				$arrMpos[$CUSTNO][$TRANSNO][$MPOSNO][$BRAND][$CLASS]["REASON"]		=	$REASON;
				$arrMpos[$CUSTNO][$TRANSNO][$MPOSNO][$BRAND][$CLASS]["TOTALQTY"]	+=	$TOTALQTY;
				$arrMpos[$CUSTNO][$TRANSNO][$MPOSNO][$BRAND][$CLASS]["GROSSAMOUNT"]	+=	$GROSSAMOUNT;
				$arrMpos[$CUSTNO][$TRANSNO][$MPOSNO][$BRAND][$CLASS]["NETAMOUNT"]	+=	$NETAMOUNT;
				$RSGETMPOS->Movenext();
			}
		}
		$_SESSION["arrMPOS"]	=	$arrMpos;
		if ($selbrand != "ALL") 
		{
		$_SESSION["BRAND"]		=	$DATASOURCE->selval($conn_250_171,"FDC_PMS","BRAND_NEW","BRAND_NAME","BRAND_ID = '{$selbrand}'");
		}
		else 
		{
		$_SESSION["BRAND"]		=	"ALL";	
		}
		$_SESSION["CLASS"]		=	$selclass;
		$_SESSION["REASON"]		=	$selreason;
		echo "
					<table border='1'class='tblresult tablesorter'>
						<thead>
							<tr class='trheader'>
						 		<th>No.</th>
						 		<th>Transaction No.</th>
						 		<th>Customer</th>
						 		<th>MPOS No.</th>
						 		<th>MPOS Date</th>	
						 		<th>Scan Date</th>	
						 		<th>Tablet Date</th>	
						 		<th>REASON</th>	
						 		<th>Brand</th>	
						 		<th>Class</th>	
						 		<th>Qty</th>	
						 		<th>Gross Amount</th>
						 		<th>Net Amount</th>
						 	</tr>
						 </thead>
						 <tbody>";
		$cnt=	1;
		$GRANDTOTAL	=	0;
		$NETTOTAL	=	0;
		$GRANDQTY	=	0;
		foreach ($arrMpos as $custno=>$val1)
		{
			foreach ($val1 as $trxno=>$val2)
			{
				foreach ($val2 as $mpos=>$val3)
				{
					foreach ($val3 as $brand=>$val4)
					{
						foreach ($val4 as $class=>$val5)
						{
							$CustName	=	$val5["CustName"];
							$MPOSDATE	=	$val5["MPOSDATE"];
							$SCANDATE	=	$val5["SCANDATE"];
							$GROSSAMOUNT=	$val5["GROSSAMOUNT"];
							$NETAMOUNT	=	$val5["NETAMOUNT"];
							$BRANDNAME	=	$val5["BRANDNAME"];
							$CLASS_		=	$class;
							$STATUS		=	$val5["STATUS"];
							$REASON		=	$val5["REASON"];
							$TOTALQTY	=	$val5["TOTALQTY"];
							
							$tablet_date=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MPOSHDR","SCANDATE","TRANSNO = '{$trxno}' and MPOSNO = '{$mpos}' ");
							
							echo "<tr class='trbody'>
							 		<td align='center'>$cnt</td>
							 		<td align='center'>$trxno</td>
							 		<td >$CustName</td>
							 		<td align='center'>$mpos</td>
							 		<td align='center' nowrap>$MPOSDATE</td>	
							 		<td align='center' nowrap>$SCANDATE</td>
							 		<td align='center' nowrap>$tablet_date</td>
							 		<td align='center'>$REASON</td>		
							 		<td align='center'>$BRANDNAME</td>	
							 		<td align='center'>$CLASS_</td>	
							 		<td align='center'>".number_format($TOTALQTY)."</td>	
							 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
							 		<td align='right'>".number_format($NETAMOUNT,2)."</td>
							 	</tr>";
							$GRANDTOTAL	+=	$GROSSAMOUNT;
							$NETTOTAL	+=	$NETAMOUNT;
							$GRANDQTY	+=	$TOTALQTY;
							$cnt++;
						}
					}
				}
			}
		}
		echo "</tbody>
			<tr class='trbody bld'>
		 		<td align='center' colspan='10'>GRAND TOTAL</td>
		 		<td align='right'>".number_format($GRANDQTY)."</td>
		 		<td align='right'>".number_format($GRANDTOTAL,2)."</td>
		 		<td align='right'>".number_format($NETTOTAL,2)."</td>
		 	</tr>";
		echo "</table>";
//		echo "<input type='button' name='btnprint' id='btnprint' value='PRINT' class='small_button' onclick='print();'>";
		echo "<button type='button' class='btnprint' onclick='print();'>Print</button>";
		echo "<button type='button' class='btnprint' onclick='dl_cvs();'>CSV</button>";
		exit();
	}
	function getTBLprev()
	{
		return "<table border='1'class='tblresult tablesorter'>
					<thead>
						<tr class='trheader'>
					 		<th>No.</th>
					 		<th>Transaction No.</th>
					 		<th>Customer</th>
					 		<th>MPOS No.</th>
					 		<th>MPOS Date</th>	
					 		<th>Scan Date</th>	
					 		<th>Tablet Date</th>	
					 		<th>REASON</th>	
					 		<th>Brand</th>	
					 		<th>Class</th>	
					 		<th>Qty</th>	
					 		<th>Gross Amount</th>
					 		<th>Net Amount</th>
					 	</tr>
					 </thead>
			 		<tr class='trbody centered fnt-red'>
				 		<td colspan='13'>Nothing to display.</td>
				 	</tr>
				 </table>";
	}
?>
<script>
$("document").ready(function(){
	$(".searchcust").keyup(function(evt){
		var txtcustno	=	$('#txtcustno').val();
		var txtcustname	=	$('#txtcustname').val();
		var custtype	=	$('#selcusttype').val();
		var custcusttype=	$('input[name=rdcusttype]:checked', '#dataform').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'returns_monitoring.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname+'&selcusttype='+custtype+"&custcusttype="+custcusttype,
						beforeSend	:	function()
									{
									},
						success		:	function(response)
									{
										if(response == '')
										{
											MessageType.infoMsg('Customer not found.');
											$('#divselcust').html('');
											$(".searchcust").val("");
										}
										else
										{
											$('#divselcust').html(response);
											var position =$("#txtcustno").position();
											var selwidth	=	$("#txtcustno").width() + $("#txtcustname").width()+12;
											$("#divselcust").css({ position:'absolute'});
											$('#divselcust').show();
											$('#selcust').css({width:selwidth});
										}
									}
				});
			}
			else if(evthandler == 40 && $('#divselcust').html() != '')
			{
				$('#selcust').focus();
			}
			else
			{
				$('#divselcust').html('');
			}
		}
		else
		{
			$('#divselcust').html('');
		}
	});
	$("#btnreport").click(function(){
		var errmsg		=	"";
		var dataform	=	$("#dataform").serialize();
		var errstatus	=	"";
		if($("#txtmposno").val() == "" && $("#txttrxno").val() == "")
		{
			if($("#mposdfrom").val() == "" && $("#mposdto").val() == "" && $("#scandfrom").val() == "" &&  $("#scandto").val() == "")
			{
				errmsg	+=	" - Please input at least one date range.<br>";
			}
			if($("#mposdfrom").val() > $("#mposdto").val())
			{
				errmsg	+=	" - Invalid MPOS date range.<br>";
			}
			if($("#scandfrom").val() > $("#scandto").val())
			{
				errmsg	+=	" - Invalid SCAN date range.<br>";
			}
			errstatus	=	" - Please select status.";
			$('input[type=checkbox]').each(function () {
			     if((this.checked))
			     {
			     	errstatus	=	"";
			     }
			});
		}
		
		if(errmsg == "" && errstatus == "")
		{
			$.ajax({
				type:	"POST",
				data:	dataform,
				url:	"returns_monitoring.php?action=GETMPOS",
				beforeSend:function(){
					$("#divloader").dialog("open");
				},
				success:function(response){
					$("#divMPOS").html(response);
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter();
					$(".btnprint").button({icons: {primary: "ui-icon ui-icon-print"}});
				}
			});
		}
		else
		{
			MessageType.infoMsg(errmsg+errstatus);
		}
	});
	$("#selcusttype").change(function(){
		var custcusttype=	$('input[name=rdcusttype]:checked', '#dataform').val();
		if($(this).val() != "ALL" || custcusttype != undefined)
		{
			$("#txtcustno").removeAttr("disabled"); 
			$("#txtcustname").removeAttr("disabled"); 
		}
		else
		{
			$("#txtcustno").attr("disabled","disabled");
			$("#txtcustname").attr("disabled","disabled");
			$("#txtcustno").val("");
			$("#txtcustname").val("");
		}
	});
	$("#rdonbs, #rdotrade").click(function(){
		$("#txtcustno").removeAttr("disabled"); 
		$("#txtcustname").removeAttr("disabled"); 
	});
	$("#chkscanned").click(function(){
		 if((this.checked))
		 {
		 	$("#scandfrom").removeAttr("disabled"); 
			$("#scandto").removeAttr("disabled"); 
		 }
		 else
		 {
		 	$("#scandfrom").attr("disabled","disabled");
			$("#scandto").attr("disabled","disabled");
		 	$("#scandfrom").val("");
			$("#scandto").val("");
		 }
	});
});
function	smartsel(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnval').val($('#selcust').val());
		var vx = $('#hdnval').val();
		var x = vx.split('|'); 
		$('#txtcustno').val(x[0]);
		$('#txtcustname').val(x[1]);
		$('#divselcust').html('');
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnval').val($('#selcust').val());
			var vx = $('#hdnval').val();
			var x = vx.split('|'); 
			$('#txtcustno').val(x[0]);
			$('#txtcustname').val(x[1]);
			$('#divselcust').html('');
		}
	}
}
function print()
{
	window.open("returns_monitoringPDF.php");
}
function dl_cvs()
{
	window.open("returns_monitoringCSV.php");
}
</script>
<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
	<table width="100%" border="0"  class="Text_header">
		<tr>
			<td align="center" class="tdoptions">
				<table border="0"class="label_text">
					<tr>
						<td>MPOS NO.</td>
						<td>:</td>
						<td>
							<input type="text" id="txtmposno" name="txtmposno" placeholder="MPOS No." class="centered">
						</td>
					</tr>
					<tr>
						<td>TRANCASCTION NO.</td>
						<td>:</td>
						<td>
							<input type="text" id="txttrxno" name="txttrxno" placeholder="TRX No." class="centered">
						</td>
					</tr>
					<tr>
						<td>CUSTOMER TYPE</td>
						<td>:</td>
						<td>
							<select id="selcusttype" name="selcusttype">
								<option value="ALL">All</option>
								<option value="O">OUTRIGHT</option>
								<option value="C">CONCESSIONAIRE</option>
							</select>
							<label for="rdonbs"><input type="radio" id="rdonbs" name="rdcusttype" value="NBS">NBS</label>
							<label for="rdotrade"><input type="radio" id="rdotrade" name="rdcusttype" value="TRADE">TRADE</label>
						</td>
					</tr>
					<tr>
						<td>CUSTOMER</td>
						<td>:</td>
						<td>
							<input type="text" id="txtcustno" name="txtcustno" size="10" placeholder='CODE' class="searchcust centered">
							<input type="text" id="txtcustname" name="txtcustname" size="35" placeholder='NAME' class="searchcust centered">
							<div id="divselcust" class="divsel"></div>
							<input type="hidden" id="hdnval" name="hdnval" value="">
						</td>
					</tr>
					<tr>
						<td>BRAND</td>
						<td>:</td>
						<td>
							<?php 
							$GETBRAND	=	"SELECT * FROM  FDC_PMS.BRAND_NEW";
							$RSGETBRAND	=	$conn_250_171->Execute($GETBRAND);
							if($RSGETBRAND == false)
							{
								$errmsg	=	($conn_250_171->ErrorMsg()."::".__LINE__); 
								$DATASOURCE->logError("wms",$errmsg,$GETBRAND,$_SESSION['username'],"MPOS MONITORING","GETBRAND");
								$DATASOURCE->displayError();
							}
							?>
							<select id="selbrand" name="selbrand">
								<option value="ALL">All</option>
								<?php 
								while (!$RSGETBRAND->EOF) {
									
									$BRAND_ID 	=	$RSGETBRAND->fields["BRAND_ID"];
									$BRAND_NAME	=	$RSGETBRAND->fields["BRAND_NAME"];
									echo "<option value='$BRAND_ID'>$BRAND_NAME</option>";
									$RSGETBRAND->MoveNext();
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>CLASS</td>
						<td>:</td>
						<td>
							<?php 
							$GETCLASS	=	"SELECT * FROM  FDC_PMS.CLASS_NEW";
							$RSGETCLASS	=	$conn_250_171->Execute($GETCLASS);
							if($RSGETCLASS == false)
							{
								$errmsg	=	($conn_250_171->ErrorMsg()."::".__LINE__); 
								$DATASOURCE->logError("wms",$errmsg,$GETCLASS,$_SESSION['username'],"MPOS MONITORING","GETCLASS");
								$DATASOURCE->displayError();
							}
							?>
							<select id="selclass" name="selclass">
								<option value="ALL">All</option>
								<?php 
								while (!$RSGETCLASS->EOF) {
									
									$CLASS_NAME	=	$RSGETCLASS->fields["CLASS_NAME"];
								?>
									<option value="<?php echo $CLASS_NAME;?>"><?php echo $CLASS_NAME;?></option>
								<?php
									$RSGETCLASS->MoveNext();
								}
								?>
							</select>
						</td>
					</tr>
					
					<tr>
						<td>REASON</td>
						<td>:</td>
						<td>
							<?php 
							$GETREASON	=	"SELECT DISTINCT(REASON) FROM WMS_NEW.`MPOSHDR` WHERE REASON != ''";
							$RSGETREASON	=	$conn_255_10->Execute($GETREASON);
							if($RSGETREASON== false)
							{
								$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
								$DATASOURCE->logError("wms",$errmsg,$GETREASON,$_SESSION['username'],"MPOS MONITORING","GETREASON");
								$DATASOURCE->displayError();
							}
							?>
							<select id="selreason" name="selreason">
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
						<td>STATUS<span style="color:red;">*</span></td>
						<td>:</td>
						<td>
							<label><input type="checkbox" name="chkforpickup" 	id="chkforpickup" 	value="N">FOR PICKUP</label>
						 	<label><input type="checkbox" name="chkpicked" 	id="chkpicked" 		value="Y">PICKED</label>
						 	<label><input type="checkbox" name="chkscanned" 	id="chkscanned" 	value="scanned">SCANNED</label>
						 	<label><input type="checkbox" name="chkcreated" 	id="chkcreated" 	value="created">CREATED MPOS</label>
						</td>
					</tr>
					<tr>
						<td>MPOS DATE</td>
						<td>:</td>
						<td>
							<input type="text" name="mposdfrom" id="mposdfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;TO&nbsp;&nbsp;
						 	<input type="text" name="mposdto" 	id="mposdto" 	class="dates"	value="" size="10"  placeholder = "To"	>
						</td>
					</tr>
					<tr>
						<td>SCAN DATE</td>
						<td>:</td>
						<td>
							<input type="text" name="scandfrom" id="scandfrom" 	class="dates" 	value="" size="10"  placeholder = "From" disabled>&nbsp;&nbsp;TO&nbsp;&nbsp;
						 	<input type="text" name="scandto" 	id="scandto" 	class="dates"	value="" size="10"  placeholder = "To" disabled>
						</td>
					</tr>
					<tr>	
						<td>&nbsp;</td>
						<td></td>
						<td>
							<button type="button" id="btnreport" class="btnsearch">Search</button>
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