<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../index.php'</script>";
}

	$action	=	$_GET['action'];

	$action		=	$_GET['action'];
	if ($action=='DISPLAY_') 
	{
		$DFROM		=	$_GET['DFROM'];
		$DTO		=	$_GET['DTO'];
		$date_type	=	$_GET['SEL_DATA_TYPE'];
		$OPT_2		=	$_GET['OPT_2'];
		
		$opt	=	$_GET['OPT__'];
		$doc	=	$_GET['SEL_DOC'];
		
		$sof_list=	array();
		
		
		$sel_val_cust	 =	"SELECT * from WMS_NEW.CONFIRMDELIVERY_HDR WHERE TRANSMIT = 'N' ";
		if ($date_type == '1') 
		{
		 $sel_val_cust	 .=	" AND CONFIRMDELDATE between '{$DFROM}' AND '{$DTO}' ";
		}
		else 
		{
		$sel_val_cust	 .=	" AND ADDEDDATE between '{$DFROM}' AND '{$DTO}' ";	
		}
		if ($OPT_2=='N')
		{
		$sel_val_cust	 .=	" AND VARIANCE = 'N' ";
		}
		else 
		{
		$sel_val_cust	 .=	" AND VARIANCE = 'Y' ";	
		}
		if ($_SESSION['username'] != 'raymond') 
		{
		$sel_val_cust	.=	"AND DOCTYPE = '{$doc}' and ADDEDBY = '{$_SESSION['username']}' ";
		}
		$rssel_val_cust	 =	$Filstar_conn->Execute($sel_val_cust);
		if ($rssel_val_cust==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$cnt	=	$rssel_val_cust->RecordCount();
		if ($cnt > 0) 
		{
			while (!$rssel_val_cust->EOF) 
			{
				$CUSTNO			=	$rssel_val_cust->fields['CUSTNO'];
				$SOF			=	$rssel_val_cust->fields['SOF'];
				
				$branch_code	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustomerBranchCode","CustNo = '{$CUSTNO}' ");
				if ($opt=='NBS' && !empty($branch_code)) 
				{
					$sof_list[]	=	$SOF;
				}
				else if ($opt=='TRADE' && $branch_code == '') 
				{
					$sof_list[]	=	$SOF;
				}
				$rssel_val_cust->MoveNext();
			}
			$count_list	=	count($sof_list);
			if ($count_list > 0) 
			{
				$sof 		=	implode("','",$sof_list);

				$sel_sof	=	"SELECT * FROM WMS_NEW.CONFIRMDELIVERY_HDR WHERE SOF IN ('{$sof}') ";
				$rssel_sof	=	$Filstar_conn->Execute($sel_sof);
				if ($rssel_sof==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				
				$show	.=	"<form id='form_detail' name='form_detail'>";
				$show	.=	"<table width='100%' border='0'>";
				$show	.=	"<tr class='Header_style' style='font-size:12px;'>";
				$show	.=		"<td width='5%' nowrap>";
				$show	.=			"LINENO";
				$show	.=		"</td>";
				$show	.=				"<td width='10%' align='center' nowrap>";
				$show	.=					"SOFNO";
				$show	.=				"</td>";
				$show	.=						"<td width='40%' align='center' nowrap>";
				$show	.=							"CUSTOMER";
				$show	.=						"</td>";
				$show	.=								"<td width='10%' align='center' nowrap>";
				$show	.=									"$doc NO.";
				$show	.=								"</td>";
				$show	.=										"<td width='10%' align='center' nowrap>";
				$show	.=											"$doc AMOUNT";
				$show	.=										"</td>";
				$show	.=												"<td width='10%' align='center' nowrap>";
				$show	.=													"$doc DATE ";
				$show	.=												"</td>";
				$show	.=														"<td width='10%' align='center' nowrap>";
				$show	.=															"GROSS AMOUNT ";
				$show	.=														"</td>";
				$show	.=																"<td width='5%' align='center' nowrap>";
				$show	.=																	"ADDED DATE";
				$show	.=																"</td>";
				$show	.=	"</tr>";
				
				$COUNTER	=	1;
				
				while (!$rssel_sof->EOF) 
				{
					$SOF			=	$rssel_sof->fields['SOF'];
					$CUSTNO			=	$rssel_sof->fields['CUSTNO'];
					$REFN0			=	$rssel_sof->fields['DOCNO'];
					$RCVDNETAMOUNT	=	$rssel_sof->fields['RCVDNETAMOUNT'];
					$RCVDGROSSAMOUNT=	$rssel_sof->fields['RCVDGROSSAMOUNT'];
					$ADDEDDATE		=	$rssel_sof->fields['ADDEDDATE'];
					
					$custname		=	substr($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' "),0,45);
					$InvoiceDate	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","InvoiceDate","OrderNo = '{$SOF}' ");
					
					$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' style='font-size:13px;'>";
					
					$show	.=	"<td align='center'  >";
					$show	.=	$COUNTER;
					$show	.=	"</td>";
		
					$show	.=	"<td align='center'  >";
					$show	.=	$SOF;
					$show	.=	"</td>";
		
					$show	.=	"<td align='center'   nowrap >";
					$show	.=	$CUSTNO.'-'.$custname;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	$REFN0;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	number_format($RCVDNETAMOUNT,2);
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	$InvoiceDate;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	number_format($RCVDGROSSAMOUNT,2);
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	$ADDEDDATE;
					$show	.=	"</td>";
					
					$show	.=	"</tr>";
					
					
					$total_net	+=	$RCVDNETAMOUNT;
					$total_gross+=	$RCVDGROSSAMOUNT;
					
					$COUNTER++;
					
					$rssel_sof->MoveNext();	
				}
				
					$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' style='font-size:13px;'>";
					$show	.=	"<td align='center' colspan='4'>";
					$show	.=	"&nbsp";
					$show	.=	"</td>";
		
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	number_format($total_net,2);
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	"&nbsp";
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	number_format($total_gross,2);
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	"&nbsp";
					$show	.=	"</td>";
					
					$show	.=	"</tr>";
					
					$show	.=	"<tr>";
					$show	.=		"<td colspan='8' align='center'>";
					$show	.=			"<input type='button' name='btnprint' id='btnprint' value='PRINT' class='small_button' onclick='transmit();'>";
					$show	.=		"<td>";
					$show	.=	"</tr>";
				
				$show	.=	"</table>";
			}
			else 
			{
				$show	=	"<table width='100%' border='0'>";
				$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
				$show	.=		"<td width='100%' colspan='4' style='font-size:30px;color:red'>";
				$show	.=			"<blink>NO RECORD FOUND</blink>";
				$show	.=		"</td>";
				$show	.=	"</tr>";
				$show	.=	"</table>";
			}
				
		}
		else 
		{
			$show	=	"<table width='100%' border='0'>";
			$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
			$show	.=		"<td width='100%' colspan='4' style='font-size:30px;color:red'>";
			$show	.=			"<blink>NO RECORD FOUND</blink>";
			$show	.=		"</td>";
			$show	.=	"</tr>";
			$show	.=	"</table>";
		}
		echo $show;
		exit();
	}
	
	if ($action=='ITEM_LIST') 
	{
		$IANO	=	$_GET['VAL_IANO'];
		
		$IANO	=	"SELECT IATRANSN0,SKUNO,IAQTY,REMARKS FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE  IATRANSN0 = '{$IANO}' ";
		$rsIANO	=	$Filstar_conn->Execute($IANO);
		if ($rsIANO==false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		$show	.=	"<table width='100%' border='0'>";
		$show	.=	"<tr class='Header_style' style='font-size:15px;'>";
		$show	.=		"<td width='5%' nowrap>";
		$show	.=			"LINENO";
		$show	.=		"</td>";
		$show	.=				"<td width='15%' align='center' nowrap>";
		$show	.=					"SKUNO";
		$show	.=				"</td>";
		$show	.=						"<td width='50%' align='center' nowrap>";
		$show	.=							"DESCRIPTION";
		$show	.=						"</td>";
		$show	.=								"<td width='10%' align='center' nowrap>";
		$show	.=									"I.A. QTY";
		$show	.=								"</td>";
		$show	.=										"<td width='10%' align='center' nowrap>";
		$show	.=											"REMARKS";
		$show	.=										"</td>";
		$show	.=												"<td width='10%' align='center' nowrap>";
		$show	.=													"LOCATION";
		$show	.=												"</td>";
		$show	.=	"</tr>";
		
		$COUNTER	=	1;
		
		while (!$rsIANO->EOF) 
		{
			$IATRANSN0	=	$rsIANO->fields['IATRANSN0'];
			$SKUNO		=	$rsIANO->fields['SKUNO'];
			$IAQTY		=	$rsIANO->fields['IAQTY'];
			$REMARKS	=	$rsIANO->fields['REMARKS'];
			
			$ItemDesc	=	substr($global_func->Select_val($Filstar_conn,FDCRMS,"itemmaster","ItemDesc","ItemNo = '".$SKUNO."'"),0,40);
			$whsloc		=	$global_func->Select_val($Filstar_conn,FDCRMS,"itembal","whsloc","itmnbr = '".$SKUNO."' and house = 'FDC' ");
			$REMARKS	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE = '".$REMARKS."' ");
			
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#cccccc';\" bgcolor=\"#cccccc\" class='Text_header_hover' style='font-size:13px;' >";
			$show	.=	"<td align='center'  >";
			$show	.=	$COUNTER;
			$show	.=	"</td>";

			$show	.=	"<td align='center'  >";
			$show	.=	$SKUNO;
			$show	.=	"</td>";

			$show	.=	"<td align='center'   nowrap >";
			$show	.=	$SKUNO.'-'.$ItemDesc;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'   nowrap>";
			$show	.=	$IAQTY;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'   nowrap>";
			$show	.=	$REMARKS;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'   nowrap>";
			$show	.=	$whsloc;
			$show	.=	"</td>";
			
			$rsIANO->MoveNext();
		}
		
		$show	.=	"<input type='text' name='hdniano' id='hdniano' value='{$IATRANSN0}'>";
		$show	.=	"</table>";
		
		echo $show;
		exit();
	}
	
	
	if ($action=='POSTING') 
	{
		$THIS_IANO	=	$_GET['THIS_IANO'];
		
		$Filstar_conn->StartTrans();
		$sel_dtl	=	"SELECT  SKUNO,IAQTY FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSN0 = '{$THIS_IANO}' ";
		$rssel_dtl	=	$Filstar_conn->Execute($sel_dtl);
		if ($rssel_dtl==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_dtl->EOF) 
		{
			$SKUNO	=	$rssel_dtl->fields['SKUNO'];
			$IAQTY	=	$rssel_dtl->fields['IAQTY'];
			
			$sel_itembal	=	"SELECT onhqty,whsloc FROM itembal where itmnbr = '{$SKUNO}' and house = 'FDC' ";
			$rssel_itembal	=	$Filstar_conn->Execute($sel_itembal);
			if ($rssel_itembal==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			$onhqty			=	$rssel_itembal->fields['onhqty'];
			$whsloc			=	$rssel_itembal->fields['whsloc'];
			
			$update_item	=	"UPDATE itembal set onhqty = (onhqty + $IAQTY) where itmnbr = '{$SKUNO}' and house = 'FDC' ";
			$rsupdate_item	=	$Filstar_conn->Execute($update_item);
			if ($rsupdate_item==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			
			$newonhand		=	$onhqty+$IAQTY;
			
			$update_ia		=	"UPDATE WMS_NEW.INVENTORYADJUSTMENT_DTL set CURRONHANDQTY = '{$onhqty}' , NEWONHANDQTY =  '{$newonhand}', LOCATION = '{$whsloc}' 
								WHERE IATRANSN0 = '{$THIS_IANO}' AND SKUNO = '{$SKUNO}' AND IAQTY = '{$IAQTY}' ";
			$rsupdate_ia	=	$Filstar_conn->Execute($update_ia);
			if ($rsupdate_ia==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			$rssel_dtl->MoveNext();
		}
		
		$update_hdr			=	"UPDATE WMS_NEW.INVENTORYADJUSTMENT_HDR SET STATUS = 'POSTED', POSTEDBY= '{$_SESSION['username']}',POSTEDDATE=SYSDATE(),POSTEDTIME=SYSDATE()
								WHERE IATRANSNO = '{$THIS_IANO}' ";
		$rsupdate_hdr		=	$Filstar_conn->Execute($update_hdr);
		if ($rsupdate_hdr==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		echo "done";
		$Filstar_conn->CompleteTrans();
		exit();
	}
	
	
	if ($action=='SEARCHCUST')
	{
		$custcode	=	$_GET['CUSTCODE'];
		$custname	=	$_GET['CUSTNAME'];
		$sel	 =	"SELECT CustNo,CustName from custmast where 1 ";
		if (!empty($custcode)) 
		{
		$sel	.=	"AND CustNo like '%{$custcode}%' ";
		}
		if(!empty($custname))
		{
		$sel	.=	"AND CustName like '%{$custname}%' ";
		}
		$sel	.=	"AND CustStatus = 'A' AND CustomerBranchCode != '' ";
		$sel	.=	" limit 20 ";
		$rssel	=	$Filstar_conn->Execute($sel);
		if ($rssel == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;
			exit();
		}
		$cnt	=	$rssel->RecordCount();
		if ($cnt > 0)
		{
			echo "<select id=\"selcust\" onkeypress=\"smartsel(event);\" multiple>";
			while (!$rssel->EOF)
			{
				$custno		=	$rssel->fields['CustNo'];
				$custname	=	$rssel->fields['CustName'];
				$cValue		=	$custno."|".$custname;
				$show		=	$custno."-".$custname;
				echo "<option value=\"$cValue\" onclick=\"smartsel('click');\">".$show."</option>";
				$rssel->MoveNext();
			}
			echo "</select>";
		}
		else
		{
			echo "zero";
		}
		exit();
	}
?>
<html>
<title>SKU SUMMARY</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../../css/style.css);</style>
<style type="text/css">@import url(../../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../../calendar/calendar-setup.js"></script>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<style type="text/css">
body {
	font-size: 62.5%;
	font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
}

table {
	font-size: 1em;
}

.no-close .ui-dialog-titlebar-close {
    display: none;
}

.Text_header_value
{
	font:15px Dejavu Sans, arial, helvetica, sans-serif;
	font-weight:bold;
}

</style>
</head>
<body>
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0" class="Text_header" style="border:0px">
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					CUSTOMER TYPE
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<input type="radio" name="rdtype" id="rdnbs" value="NBS" checked>NBS
					&nbsp;<input type="radio" name="rdtype" id="rdtrade" value="TRADE">TRADE
				</td>
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					DOC TYPE
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<select id="sel_doc" name="sel_doc">
						<option value="STF">STF</option>
						<option value="INVOICE">INVOICE</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					VARIANCE
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<input type="radio" name="rdvariance" id="variance_N" value="N" checked>NO
					&nbsp;<input type="radio" name="rdvariance" id="variance_Y" value="Y">YES
				</td>
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					<select id="sel_data_type" name="sel_data_type">
						<option value="1">CONFIRMED DATE</option>
						<option value="2">ADDED DATE</option>
					</select>
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<input type="text" name="dfrom" id="dfrom" 	class="dates" 	value="" size="10" placeholder="FROM" style="text-align:center;" >&nbsp;
				 	<input type="text" name="dto" 	id="dto" 	class="dates"	value="" size="10" placeholder="TO" style="text-align:center;" >
				</td>
			</tr>
			<tr>
				<td width="100%" colspan="3" align="center">
					<input type="button" name="btnsearch" id="btnsearch" value="SEARCH" class="small_button" onclick="DISPLAY();"  style='width:120px;height:35px;'>
				</td>
			</tr>
		</table>
		</div>
		<div style="display:none;" id="divINVOICE">
		<table width="100%" border="0" class="Text_header" style="border:0px">
			<tr>
				<td align="right" width="45%" style="font-size:20px">
					INVOICE&nbsp;
				</td>
				<td align="left" width="55%" colspan="3" style="font-size:20px">
					:<input type="text" name="txtINVOICE" id="txtINVOICE" value="" onkeyup="isnumeric_(this.value,this.id);GET_DATA(event,this.id,this.value);" style="font-size:20px" size="10">
				</td>
			</tr>
		</table>
		</div>
		<div id="divdata"></div>
		<div id="divdata_detail" title="ITEM LIST"></div>
		<div id="divloader" style="display:none;" align="center" title="LOADING"><img src="../../../images/loading/ajax-loader_fast.gif"></div>
		<div id="divmsg" style="display:none;" align="center" title="ALERT"></div>
	</form>
</body>
</html>
<script>
$(".dates").datepicker({ 
	dateFormat: 'yy-mm-dd',
	changeMonth: true,
    changeYear: true 
});


function DISPLAY()
{
	var	dfrom			=	$('#dfrom').val();
	var	dto				=	$('#dto').val();
	var	sel_doc			=	$('#sel_doc').val();
	var	sel_data_type	=	$('#sel_data_type').val();
	
	if(dfrom == '' || dto == '')
	{
		alert('Invalid transaction date!');
		return;
	}
	if(dfrom > dto)
	{
		alert('Invalid transaction date!');
		return;
	}
	
	var	is_nbs		=	$('#rdnbs').is(":checked");
	if(is_nbs==true)
	{
		var	opt__	=	"NBS";
	}
	else
	{
		var	opt__	=	"TRADE";
	}
	
	var is_variance	=	$('#variance_N').is(":checked");
	if(is_variance == true)
	{
		var	opt_2	=	"N";
	}
	else
	{
		var	opt_2	=	"Y";
	}
	$.ajax({
			url			:	'transmittal.php?action=DISPLAY_&DFROM='+dfrom+'&DTO='+dto+'&OPT__='+opt__+'&SEL_DOC='+sel_doc+'&SEL_DATA_TYPE='+sel_data_type+'&OPT_2='+opt_2,
			beforeSend	:	function()
						{
							$('#divloader').dialog('open');
						},
			success		:	function(response)
						{
							$('#divloader').dialog('close');
							$('#divdata').dialog('open');
							$('#divdata').html(response);
						}
	});
}

 
function searchcust(evt)
{
	var custcode	=	$('#customercode').val();
	var custname	=	$('#customername').val();
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;

	if(custcode != '' || custname != '')
	{
		if(evthandler != 40 && evthandler != 13 && evthandler != 27)
		{
			$.ajax({
				url			:	'adjustment.php?action=SEARCHCUST&CUSTCODE='+custcode+'&CUSTNAME='+custname,
				success		:	function(response)
				{
					if(response == 'zero')
					{
						alert('No record found...');
						$('#divcust').html('');
						$('#customercode').val('');
						$('#customername').val('');
					}
					else
					{
						$('#divcust').html(response);
						$('#divcust').show();
					}
				}
			});
		}
		else if(evthandler == 40 && $('#divcust').html() != '')
		{
			$('#selcust').focus();
		}
		else
		{
			$('#divcust').html('');
		}
	}
	else
	{
		$('#divcust').html('');
		$('#customercode').val('');
		$('#customername').val('');
	}

}

function 	transmit()
{
	var	dfrom	=	$('#dfrom').val();
	var	dto		=	$('#dto').val();
	var	sel_doc	=	$('#sel_doc').val();
	var	sel_data_type	=	$('#sel_data_type').val();
	
	
	if(dfrom == '' || dto == '')
	{
		alert('Invalid transaction date!');
		return;
	}
	if(dfrom > dto)
	{
		alert('Invalid transaction date!');
		return;
	}
	
	var	is_nbs		=	$('#rdnbs').is(":checked");
	if(is_nbs==true)
	{
		var	opt__	=	"NBS";
	}
	else
	{
		var	opt__	=	"TRADE";
	}
	
	var is_variance	=	$('#variance_N').is(":checked");
	if(is_variance == true)
	{
		var	opt_2	=	"N";
	}
	else
	{
		var	opt_2	=	"Y";
	}
	
	var	url	=	'transmittal_pdf.php?action=DISPLAY_&DFROM='+dfrom+'&DTO='+dto+'&OPT__='+opt__+'&SEL_DOC='+sel_doc+'&SEL_DATA_TYPE='+sel_data_type+'&OPT_2='+opt_2;
	window.open(url);
}

function	smartsel(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnval').val($('#selcust').val());
		var vx = $('#hdnval').val();
		var x = vx.split('|');
		$('#customercode').val(x[0]);
		$('#customername').val(x[1]);
		$('#divcust').html('');
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnval').val($('#selcust').val());
			var vx = $('#hdnval').val();
			var x = vx.split('|');
			$('#customercode').val(x[0]);
			$('#customername').val(x[1]);
			$('#divcust').html('');
		}
	}
}


$("#divloader").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 100, width: 170, modal:true, autoOpen: false,	draggable: false
});

$("#divmsg").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 100, width: 200, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		}
	}
});


$("#divdata_detail").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 500, width: 900, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		},
		'SUBMIT': function()
		{
			var	this_iano	=	$('#hdniano').val();
			var isconfirm	=	confirm('Are you sure you want to post this transaction?');
			if(isconfirm==true)
			{
				$.ajax({
						url			:	'adjustment.php?action=POSTING&THIS_IANO='+this_iano,
						beforeSend	:	function()
									{
										$('#divloader').dialog('open');
									},
						success	:	function(response)
									{
										$('#divloader').dialog('close');
										if(response=='done')
										{
											$('#divmsg').dialog('open');
											$('#divmsg').html('Transaction was successfully posted');
											DISPLAY();
										}
										else
										{
											$('#divmsg').dialog('open');
											$('#divmsg').html(response);
										}
									}
				});
			}
		}
	}
});

function isnumeric(num,id)
{
	var ValidChars ="0123456789.";
	var IsNumber = "";
	var Char;
	
	for (var i=0; i < num.length; i++)
	{
		Char = num.charAt(i);
		if(ValidChars.indexOf(Char) != -1)
		{
			IsNumber = IsNumber + Char;
		}
	}
	document.getElementById(id).value = IsNumber;
}

function isnumeric_(num,id)
{
	var ValidChars ="0123456789";
	var IsNumber = "";
	var Char;
	
	for (var i=0; i < num.length; i++)
	{
		Char = num.charAt(i);
		if(ValidChars.indexOf(Char) != -1)
		{
			IsNumber = IsNumber + Char;
		}
	}
	document.getElementById(id).value = IsNumber;
}

function addCommas(nStr) {
    nStr += '';
    var x = nStr.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}
</script>