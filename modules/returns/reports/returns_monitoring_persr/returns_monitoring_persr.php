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
if($action == "Q_SEARCHSR")
{
	$txtsrno 		= $_GET["SRNO"];
	$txtsrname 		= $_GET["SRNAME"];

	$sel	 =	"SELECT SALESREPCODE ,SRNAME  FROM  FDCFINANCIALS_CMS.SALESREP WHERE 1 ";
	if (!empty($txtsrno)) 
	{
		$sel	.=	" AND SALESREPCODE like '%{$txtsrno}%' ";
	}
	if (!empty($txtsrname)) 
	{
		$sel	.=	" AND SRNAME like '%{$txtsrname}%' ";
	}
	$sel	.=	" limit 20 ";
//		echo "$sel"; exit();
	$rssel	=	$conn_250_172->Execute($sel);
	if ($rssel == false) 
	{
		$errmsg	=	($conn_250_172->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"MPOS MONITORING PER SR","Q_SEARCHSR");
		$DATASOURCE->displayError();
	}
	$cnt	=	$rssel->RecordCount();
	if ($cnt > 0) 
	{
		echo "<select id='selsr' class = 'C_dropdown divsel' style='width:532px;height:auto;' onkeypress='smartselsr(event);' multiple>";
		while (!$rssel->EOF) 
		{
			$q_srNO	=	$rssel->fields['SALESREPCODE'];
			$Q_sr_name=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['SRNAME']);
			$cValue		=	$q_srNO."|".$Q_sr_name;
			echo "<option value=\"$cValue\" onclick=\"smartselsr('click');\">$q_srNO-$Q_sr_name</option>";
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
if ($action=='Q_SEARCHCUST') 
{
	$custno		=	addslashes($_GET['CUSTNO']);
	$custname	=	addslashes($_GET['CUSTNAME']);
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
//		echo "$sel"; exit();
	$rssel	=	$conn_255_10->Execute($sel);
	if ($rssel == false) 
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"MPOS MONITORING PER SR","Q_SEARCHCUST");
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
if ($action == "GETMPOS") 
{
	$txtsrno		=	$_POST["txtsrno"];
	$txtcustno		=	$_POST["txtcustno"];
	$mposdfrom		=	$_POST["mposdfrom"];
	$mposdto		=	$_POST["mposdto"];
	$selreason		=	$_POST["selreason"];
	$rdocusttype	=	$_POST["rdocusttype"];
	if ($txtsrno != "") {
		$txtsrno_Q	=	" AND H.SALESREPNO = '{$txtsrno}'";
	}
	if ($rdocusttype == "NBS") {
		$rdcusttype_Q	=	" AND C.CustomerBranchCode != ''";
	}
	if ($rdocusttype == "TRADE") {
		$rdocusttype_Q	=	" AND C.CustomerBranchCode = ''";
	}
	if ($txtcustno != "") {
		$txtcustno_Q	=	" AND H.CUSTNO = '{$txtcustno}'";
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
	$GETMPOS	=	"SELECT H.SALESREPNO,H.MPOSNO,H.CUSTNO,H.MPOSDATE,H.TOTALQTY,H.GROSSAMOUNT,H.REASON,C.CustName
					 FROM WMS_NEW.`MPOSHDR` AS H 
					 LEFT JOIN WMS_NEW.MPOSDTL AS D ON D.TRANSNO = H.TRANSNO
					 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = H.CUSTNO
					 WHERE 1
					 $txtsrno_Q $rdocusttype_Q $txtcustno_Q $mposdate_Q $scandate_Q $selreason_Q
					 GROUP BY H.TRANSNO
					 ORDER BY H.TRANSNO";
//	echo $GETMPOS; 
//	exit();
	$RSGETMPOS	=	$conn_255_10->Execute($GETMPOS);
	if($RSGETMPOS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOS,$_SESSION['username'],"MPOS MONITORING PER SR","GETMPOS");
		$DATASOURCE->displayError();
	}
	else 
	{
		if($RSGETMPOS->RecordCount() > 0)
		{
			while (!$RSGETMPOS->EOF) {
				$CUSTNO		=	$RSGETMPOS->fields["CUSTNO"];
				$SALESREPNO	=	$RSGETMPOS->fields["SALESREPNO"];
				$SALESREPNAME	=	$DATASOURCE->selval($conn_250_172,"FDCFINANCIALS_CMS","SALESREP","SRNAME","SALESREPCODE = '{$SALESREPNO}'");;
				$CustName	=	$RSGETMPOS->fields["CustName"];
				$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];
				$MPOSDATE	=	$RSGETMPOS->fields["MPOSDATE"];
				$TOTALQTY	=	$RSGETMPOS->fields["TOTALQTY"];
				$REASON		=	$RSGETMPOS->fields["REASON"];
				$GROSSAMOUNT=	$RSGETMPOS->fields["GROSSAMOUNT"];
				
				$arrMpos[$CUSTNO][$MPOSNO]["CustName"]	=	"$CUSTNO-$CustName";
				$arrMpos[$CUSTNO][$MPOSNO]["SALESREP"]	=	"$SALESREPNO-$SALESREPNAME";
				$arrMpos[$CUSTNO][$MPOSNO]["MPOSDATE"]	=	$MPOSDATE;
				$arrMpos[$CUSTNO][$MPOSNO]["REASON"]		=	$REASON;
				$arrMpos[$CUSTNO][$MPOSNO]["TOTALQTY"]	+=	$TOTALQTY;
				$arrMpos[$CUSTNO][$MPOSNO]["GROSSAMOUNT"]	+=	$GROSSAMOUNT;
				$RSGETMPOS->Movenext();
			}
			$_SESSION["arrMPOS"]	=	$arrMpos;
			echo "<table border='1'class='tblresult'>
						<tr class='trheader'>
					 		<td >No.</td>
					 		<td>SR Code-Name</td>
					 		<td>Customer</td>
					 		<td>MPOS No.</td>
					 		<td>MPOS Date</td>	
					 		<td>Reason</td>	
					 		<td>Qty</td>	
					 		<td>Gross Amount</td>
					 	</tr>";
			$cnt=	1;
			$GRANDTOTAL	=	0;
			$GRANDQTY	=	0;
			foreach ($arrMpos as $custno=>$val1)
			{
				foreach ($val1 as $mpos=>$val2)
				{
					$CustName	=	$val2["CustName"];
					$SALESREP	=	$val2["SALESREP"];
					$MPOSDATE	=	$val2["MPOSDATE"];
					$GROSSAMOUNT=	$val2["GROSSAMOUNT"];
					$REASON		=	$val2["REASON"];
					$TOTALQTY	=	$val2["TOTALQTY"];
					
					echo "<tr class='trbody mposdtls pntr' title='Click to view details.' id='mposdtls$cnt' data-mposno='$mpos' data-cnt='$cnt'>
					 		<td align='center'>$cnt</td>
					 		<td >$SALESREP</td>
					 		<td >$CustName</td>
					 		<td align='center'>$mpos</td>
					 		<td align='center'>$MPOSDATE</td>	
					 		<td align='center'>$REASON</td>		
					 		<td align='center'>".number_format($TOTALQTY)."</td>	
					 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
					 	</tr>";
					echo "<tr><td id='tdmposdtls$cnt'class='tdmposdtlsClass trbody' colspan='20' align='center'></td></tr>";
					$GRANDTOTAL	+=	$GROSSAMOUNT;
					$GRANDQTY	+=	$TOTALQTY;
					$cnt++;
				}
			}
			echo "<tr class='trbody bld'>
			 		<td align='center' colspan='6'>GRAND TOTAL</td>
			 		<td align='center'>".number_format($GRANDQTY)."</td>
			 		<td align='right'>".number_format($GRANDTOTAL,2)."</td>
			 	</tr>";
			echo "</table>";
//			echo "<input type='button' name='btnprint' id='btnprint' value='PRINT' class='small_button' onclick='print();'>";
		}
		else 
		{
			echo getTBLprev();
			exit();
		}
	}
	exit();
}
if($action == "VIEWMPOSDTLS")
{
	$MPOSNO	=	$_GET["MPOSNO"];
	$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.MPOSDTL WHERE MPOSNO = '{$MPOSNO}'";
	$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
	if($RSGETMPOSDTLS == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"MPOS MONITORING PER SR","VIEWMPOSDTLS");
		$DATASOURCE->displayError();
	}
	else {
		echo "<br><table border='1' class='tblresul-tbltdtls tablesorter'>
					<thead>
						<tr class='tblresul-tbltdtls-hdr'>
					 		<th >No.</th>
					 		<th >SKU No.</th>
					 		<th >SKU Description</th>
					 		<th >MPOS Qty</th>
					 		<th >MPOS Amount</th>
					 	</tr>
					 </thead>
					 <tbody>";
		$cnt = 1;
		while (!$RSGETMPOSDTLS->EOF) {
				$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
				$QTY		=	$RSGETMPOSDTLS->fields["QTY"];
				$UNITPRICE	=	$RSGETMPOSDTLS->fields["UNITPRICE"];
				$GROSSAMOUNT=	$RSGETMPOSDTLS->fields["GROSSAMOUNT"];
				$ITEMDESC	=	$DATASOURCE->selval($conn_250_171,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = '{$SKUNO}'");
				echo "<tr class='tblresul-tbltdtls-dtls'>
					 		<td align='center'>
					 			$cnt
							</td>
					 		<td align='center'>$SKUNO</td>
					 		<td>$ITEMDESC</td>
					 		<td align='center'>".number_format($QTY)."</td>
					 		<td align='right'>".number_format($GROSSAMOUNT,2)."</td>
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
					<td align='right'>".number_format($totamt,2)."</td>
				  </tr>
				</table>";
			}
	exit();
 }
 function getTBLprev()
{
	return "<table border='1'class='tblresult'>
				<tr class='trheader'>
			 		<td >No.</td>
			 		<td>SR Code-Name</td>
			 		<td>Customer</td>
			 		<td>MPOS No.</td>
			 		<td>MPOS Date</td>	
			 		<td>Reason</td>	
			 		<td>Qty</td>	
			 		<td>Gross Amount</td>
			 	</tr>
		 		<tr class='trbody centered fnt-red'>
			 		<td colspan='11'>Nothing to display.</td>
			 	</tr>
			 </table>";
}

?>
<script>
$("document").ready(function(){
	$(".searchsr").keyup(function(evt){
		var txtsrno	=	$('#txtsrno').val();
		var txtsrname	=	$('#txtsrname').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		if(evt.keyCode == 8)
		{
			if(txtsrno == "" || txtsrname == "")
			{
				$('#txtsrno').val("");
				$('#txtsrname').val("");
				$('#divselsr').html("");
				return;
			}
		}
		if(txtsrno != '' || txtsrname!= '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'returns_monitoring_persr.php?action=Q_SEARCHSR&SRNO='+txtsrno+'&SRNAME='+txtsrname,
						beforeSend	:	function()
									{
	//										$('#divloader').dialog("open");
									},
						success		:	function(response)
									{
	//										$('#divloader').dialog("close");
										if(response == '')
										{
											$('#divselsr').html('');
										}
										else
										{
											$('#divselsr').html(response);
											var position =$("#txtsrno").position();
											var selwidth	=	$("#txtsrno").width() + $("#txtsrname").width()+12;
											$("#divselsr").css({position:'absolute'});
											$('#divselsr').show();
											$('#selsr').css({width:selwidth});
										}
									}
				});
			}
			else if(evthandler == 40 && $('#divselsr').html() != '')
			{
				$('#selsr').focus();
			}
			else
			{
				$('#divselsr').html('');
			}
		}
		else
		{
			$('#divselsr').html('');
			$('#divsr').html('');
		}
	});
	$(".searchcust").keyup(function(evt){
		var txtcustno	=	$('#txtcustno').val();
		var txtcustname	=	$('#txtcustname').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		if(evt.keyCode == 8)
		{
			if(txtcustno == "" || txtcustname == "")
			{
				$('#txtcustno').val("");
				$('#txtcustname').val("");
				$('#divselcust').html("");
				return;
			}
		}
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'returns_monitoring_persr.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname,
						beforeSend	:	function()
									{
	//										$('#divloader').dialog("open");
									},
						success		:	function(response)
									{
	//										$('#divloader').dialog("close");
										if(response == '')
										{
											$('#divselcust').html('');
										}
										else
										{
											$('#divselcust').html(response);
											var position =$("#txtcustno").position();
											var selwidth	=	$("#txtcustno").width() + $("#txtcustname").width()+12;
											$("#divselcust").css({position:'absolute'});
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
		var frmmpos		=	$("#dataform").serialize();
		var txtsrno		=	$("#txtsrno").val();
		var txtcustno	=	$("#txtcustno").val();
		var mposdfrom	=	$("#mposdfrom").val();
		var mposdto		=	$("#mposdto").val();
		var selreason	=	$("#selreason").val();
		var rdotrade	=	$("#rdotrade").val();
		var rdonbs	=	$("#rdonbs").val();
		var valid		=	true;
		var errmsg		=	"";
		if(txtsrno == "" && txtcustno == "" && mposdfrom == "" && mposdto == "" && selreason == "ALL")
		{
			errmsg	=	"Please specify at least one criterion to search.";
		}
		if((mposdfrom == "" && mposdto != "") || (mposdfrom > mposdto))
		{
			errmsg	=	"Invalid date range.";
		}
		if(errmsg == "")
		{
			$.ajax({
				data		:	frmmpos,
				type		:	"POST",
				url			:	'returns_monitoring_persr.php?action=GETMPOS',
				beforeSend	:	function()
							{
								$('#divloader').dialog("open");
							},
				success		:	function(response)
							{
								$('#divMPOS').html(response);
								$('#divloader').dialog("close");
								$(".tdmposdtlsClass").hide();
							}
				});
		}
		else
		{
			MessageType.infoMsg(errmsg);
		}
		
	});
	$("#divMPOS").on("click",".mposdtls",function(){
		var mpos	=	$(this).attr("data-mposno")
		var cnt		=	$(this).attr("data-cnt")
		var tdtext	=	$("#tdmposdtls"+cnt).html();
			tdtext	=	tdtext.trim();
		if(tdtext == "")
		{	
			$.ajax({
					url			:	'returns_monitoring_persr.php?action=VIEWMPOSDTLS&MPOSNO='+mpos,
					beforeSend	:	function()
								{
									$('#divloader').dialog("open");
								},
					success		:	function(response)
								{
									$(".tdmposdtlsClass").html("");
									$('#tdmposdtls'+cnt).html(response);
									$(".tdmposdtlsClass").hide();
									$("#tdmposdtls"+cnt).show();
									$(".mposdtls").removeClass("activetr");
									$("#mposdtls"+cnt).addClass("activetr");
									$("#divloader").dialog("close");
									$(".tablesorter").tablesorter();
								}
				});
		}
		else
		{
			$(".tdmposdtlsClass").hide();
			$("#mposdtls"+cnt).removeClass("activetr");
			$("#tdmposdtls"+cnt).html("");
		}
	});
});
function	smartselsr(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnvalsr').val($('#selsr').val());
		var vx = $('#hdnvalsr').val();
		var x = vx.split('|'); 
		$('#txtsrno').val(x[0]);
		$('#txtsrname').val(x[1]);
		$('#divselsr').html('');
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnvalsr').val($('#selsr').val());
			var vx = $('#hdnvalsr').val();
			var x = vx.split('|'); 
			$('#txtsrno').val(x[0]);
			$('#txtsrname').val(x[1]);
			$('#divselsr').html('');
		}
	}
}
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
</script>
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0"  class="Text_header">
			<tr>
				<td align="center" class="tdoptions">
					<table border="0"class="label_text">
						<tr>
							<td>SR</td>
							<td>:</td>
							<td>
								<input type="text" id="txtsrno" name="txtsrno" size="10" placeholder='SR ID NO.' class="searchsr">
								<input type="text" id="txtsrname" name="txtsrname" size="35" placeholder='SR NAME' class="searchsr">
								<div id="divselsr" class="divsel"></div>
								<input type="hidden" id="hdnvalsr" name="hdnvalsr" value="">
							</td>
						</tr>
						
						<tr>
							<td>CUSTOMER</td>
							<td>:</td>
							<td>
								<input type="text" id="txtcustno" name="txtcustno" size="10" placeholder='CODE' class="searchcust">
								<input type="text" id="txtcustname" name="txtcustname" size="35" placeholder='NAME' class="searchcust">
								<div id="divselcust" class="divsel"></div>
								<input type="hidden" id="hdnval" name="hdnval" value="">
							</td>
						</tr>
						
						<tr>
							<td>MPOS DATE</td>
							<td>:</td>
							<td>
								<input type="text" name="mposdfrom" id="mposdfrom" 	class="dates" 	value="" size="10"  placeholder = "From"> to 
							 	<input type="text" name="mposdto" 	id="mposdto" 	class="dates"	value="" size="10"  placeholder = "To"	>
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
							<td>CUSTOMER RTYPE</td>
							<td>:</td>
							<td>
								<label for="rdotrade"><input type="radio" id="rdotrade" name="rdocusttype" value="TRADE">TRADE</label>
								 <label for="rdonbs"><input type="radio" id="rdonbs" name="rdocusttype" value="NBS">NBS</label>
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