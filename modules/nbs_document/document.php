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
		$PONO			=	$_GET['PONO'];
		$DFROM			=	$_GET['DFROM'];
		$DTO			=	$_GET['DTO'];
		
		$sof_if_exist	=	array();
		$sof_list		=	array();
		
		
		$sel_if_exist	=	"SELECT SOF,PONO FROM WMS_NEW.RECEIVINGFORM_HDR WHERE PLDATE BETWEEN '{$DFROM}' AND '{$DTO}' ";
		if (!empty($PONO))
		{
		$sel_if_exist	.=	"AND PONO = '{$PONO}' "	;
		}
		$rssel_if_exist	=	$Filstar_conn->Execute($sel_if_exist);
		if ($rssel_if_exist==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		if ($rssel_if_exist->RecordCount() > 0) 
		{
			while (!$rssel_if_exist->EOF) 
			{
				$soflist[]	=	$rssel_if_exist->fields['SOF'];
				$rssel_if_exist->MoveNext();
			}
			$sof_if_exist	=	implode("','",$soflist);
		}
		
		$sel_sof	=	" SELECT A.OrderNo,A.RefNo,A.CustNo,A.OrderDate,A.PickListDate from orderheader A ";
		$sel_sof   .=	" LEFT JOIN custmast as B on B.CustNo = A.CustNo ";
		$sel_sof   .=	" WHERE OrderStatus = 'Confirmed' and substring(A.CustNo,-1,1) = 'O' and NBSnewBranchCode != '' ";
	 	$sel_sof   .=	" AND A.PickListDate between '{$DFROM}' AND '{$DTO}' ";
	 	if (count($sof_if_exist) > 0) 
	 	{
	 	$sel_sof   .=	" AND A.OrderNo NOT IN('{$sof_if_exist}') ";
	 	}
	 	if (!empty($PONO)) 
	 	{
	 	$sel_sof   .=	" AND A.RefNo = '{$PONO}' ";
	 	}
		$rssel_sof  =	$Filstar_conn->Execute($sel_sof);
		if ($rssel_sof==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$cnt	=	$rssel_sof->RecordCount();
		if ($cnt > 0) 
		{
				$show	.=	"<form id='form_detail' name='form_detail'>";
				$show	.=	"<table width='100%' border='0'>";
				$show	.=	"<tr class='Header_style' style='font-size:12px;'>";
				$show	.=		"<td width='5%' nowrap>";
				$show	.=			"LINENO";
				$show	.=		"</td>";
				$show	.=				"<td width='10%' align='center' nowrap>";
				$show	.=					"SOFNO";
				$show	.=				"</td>";
				$show	.=						"<td width='10%' align='center' nowrap>";
				$show	.=							"P.O. NUMBER";
				$show	.=						"</td>";
				$show	.=						"<td width='30%' align='center' nowrap>";
				$show	.=							"CUSTOMER";
				$show	.=						"</td>";
				$show	.=								"<td width='10%' align='center' nowrap>";
				$show	.=									"SOF DATE";
				$show	.=								"</td>";
				$show	.=										"<td width='10%' align='center' nowrap>";
				$show	.=											"PL DATE";
				$show	.=										"</td>";
				$show	.=												"<td width='10%' align='center' nowrap>";
				$show	.=													"QUANTITY";
				$show	.=												"</td>";
				$show	.=														"<td width='10%' align='center' nowrap>";
				$show	.=															"AMOUNT";
				$show	.=														"</td>";
				$show	.=																"<td width='5%' align='center' nowrap>";
				$show	.=																	"ACTION";
				$show	.=																"</td>";
				$show	.=	"</tr>";
				
				$COUNTER	=	1;
				
				while (!$rssel_sof->EOF) 
				{
					$OrderNo		=	$rssel_sof->fields['OrderNo'];
					$RefNo			=	$rssel_sof->fields['RefNo'];
					$CustNo			=	$rssel_sof->fields['CustNo'];
					$OrderDate		=	$rssel_sof->fields['OrderDate'];
					$PickListDate	=	$rssel_sof->fields['PickListDate'];
					
					$custname		=	substr($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CustNo}' "),0,40);
					//$InvoiceDate	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","InvoiceDate","OrderNo = '{$SOF}' ");
					
					$sel_qty		=	"SELECT sum(ReleaseQty) as TOTAL from orderdetail where OrderNo = '{$OrderNo}' and isDeleted = 'N' ";
					$rssel_qty		=	$Filstar_conn->Execute($sel_qty);
					if ($rssel_qty==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					$TOTAL			=	$rssel_qty->fields['TOTAL'];
					
					$sel_gross		=	"SELECT sum(GrossAmount) as gross from orderdetail where OrderNo = '{$OrderNo}' and isDeleted = 'N' ";
					$rssel_gross	=	$Filstar_conn->Execute($sel_gross);
					if ($rssel_gross==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					$gross			=	$rssel_gross->fields['gross'];
					
					$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' style='font-size:13px;' >";
					
					$show	.=	"<td align='center'  onclick=do_check('{$COUNTER}')>";
					$show	.=	$COUNTER;
					$show	.=	"</td>";
		
					$show	.=	"<td align='center'  onclick=do_check('{$COUNTER}')>";
					$show	.=	$OrderNo;
					$show	.=	"<input type='hidden' name='hdnorderno_$COUNTER' id='hdnorderno_$COUNTER' value='{$OrderNo}'>";
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'  onclick=do_check('{$COUNTER}')>";
					$show	.=	$RefNo;
					$show	.=	"</td>";
		
					$show	.=	"<td align='left' nowrap onclick=do_check('{$COUNTER}')>";
					$show	.=	"&nbsp;&nbsp".$CustNo.'-'.$custname;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center' nowrap onclick=do_check('{$COUNTER}')>";
					$show	.=	$OrderDate;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center' nowrap onclick=do_check('{$COUNTER}')>";
					$show	.=	$PickListDate;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center' nowrap onclick=do_check('{$COUNTER}')>";
					$show	.=	$TOTAL;
					$show	.=	"</td>";
					
					$show	.=	"<td align='right' nowrap onclick=do_check('{$COUNTER}')>";
					$show	.=	number_format($gross,2);
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	"<input type='checkbox' name='chk_$COUNTER' id='chk_$COUNTER'>";
					$show	.=	"</td>";
					
					$show	.=	"</tr>";
					
					
					$total_net	+=	$RCVDNETAMOUNT;
					$total_gross+=	$RCVDGROSSAMOUNT;
					
					$COUNTER++;
					
					$rssel_sof->MoveNext();	
				}
				
					$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' style='font-size:13px;'>";
					$show	.=		"<td align='right' colspan='7'>";
					$show	.=		"<input type='button' name='btncheck' id='btncheck' value='CHECK' class='small_button' onclick='Check_($COUNTER);'>";
					$show	.=		"<input type='button' name='btnuncheck' id='btnuncheck' value='UNCHECK' class='small_button' onclick='uncheck($COUNTER);'>";
					$show	.=		"</td>";
					$show	.=		"<td colspan='2' align='center'>";
					$show	.=			"<input type='button' name='btnprint' id='btnprint' value='PRINT' class='small_button' onclick='Create_pdf($COUNTER);'>";
					$show	.=		"<td>";
					$show	.=	"</tr>";
				
				$show	.=	"</table>";
//			}
//			else 
//			{
//				$show	=	"<table width='100%' border='0'>";
//				$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
//				$show	.=		"<td width='100%' colspan='4' style='font-size:30px;color:red'>";
//				$show	.=			"<blink>NO RECORD FOUND</blink>";
//				$show	.=		"</td>";
//				$show	.=	"</tr>";
//				$show	.=	"</table>";
//			}
				
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
<style type="text/css">@import url(../../css/style.css);</style>
<style type="text/css">@import url(../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../calendar/calendar-setup.js"></script>
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
				<td align="left" width="15%" style="font-size:15px">
					P.O NO.
				</td>
				<td align="left" width="60%" style="font-size:15px">
					:<input type="text" name="txtpono" id="txtpono" value="" size="25">
				</td>
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="15%" style="font-size:15px">
					PL DATE
				</td>
				<td align="left" width="60%" style="font-size:15px">
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
		<div id="divloader" style="display:none;" align="center" title="LOADING"><img src="../../images/loading/ajax-loader_fast.gif"></div>
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
	var	pono			=	$('#txtpono').val();
	var	dfrom			=	$('#dfrom').val();
	var	dto				=	$('#dto').val();
	
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
	
	$.ajax({
			url			:	'document.php?action=DISPLAY_&DFROM='+dfrom+'&DTO='+dto+'&PONO='+pono,
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


function	do_check(val_counter)
{
	var ischecked	=	$('#chk_'+val_counter).is(":checked");
	if(ischecked==false)
	{
		$('#chk_'+val_counter).attr('checked', true)
	}
	else
	{
		$('#chk_'+val_counter).attr('checked', false)
	}
}


function	Create_pdf(val_cnt)
{
	var	pono			=	$('#txtpono').val();
	var	dfrom			=	$('#dfrom').val();
	var	dto				=	$('#dto').val();
	var	container		=	'';
	
	for(var x=1;x<=val_cnt;x++)
	{
		var	ischecked	=	$('#chk_'+x).is(":checked");
		if(ischecked==true)
		{
			var sof	=	$('#hdnorderno_'+x).val();
			if(container=='')
			{
				container	=	sof;
			}
			else
			{
				container	=	container+"|"+sof;
			}
		}
	}
	var	url	=	'document_pdf.php?action=PDF&PONO='+pono+'&DFROM='+dfrom+'&DTO='+dto+'&SOFNO='+container;
	window.open(url);
	DISPLAY();
}

function	Check_(val_cnt)
{
	for(var x=1;x<=val_cnt;x++)
	{
		$('#chk_'+x).attr('checked',true)
	}
}

function	uncheck(val_cnt)
{
	for(var x=1;x<=val_cnt;x++)
	{
		$('#chk_'+x).attr('checked',false)
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