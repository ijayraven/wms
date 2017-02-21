<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}

	$action	=	$_GET['action'];

	$Filstar_171	=	ADONewConnection("mysqlt");
	$dbFilstar_171	=	$Filstar_171->Connect('192.168.250.171','root','');
	if ($dbFilstar_171 == false) 
	{
		echo "<script>alert('Error Occurred no Database Connection!');</script>";
		echo "<script>location = 'index.php'</script>";
	}
	
	$Filstar_loc	=	ADONewConnection("mysqlt");
	
	$dbFilstar_loc	=	$Filstar_loc->Connect('192.168.255.10','root','');
	if ($dbFilstar_loc == false) 
	{
		echo "<script>alert('Error Occurred no Database Connection!');</script>";
		echo "<script>location = 'index.php'</script>";
	}
	
	
	
	if($action=='DISPLAY__')
	{
		$BRAND			=	$_GET['BRAND'];
		$DFROM			=	$_GET['DFROM'];
		$DTO			=	$_GET['DTO'];
		$SOURCE_		=	$_GET['SOURCE_'];
		$DESTINATION_	=	$_GET['DESTINATION_'];
		$SEL_STATUS		=	$_GET['SEL_STATUS'];
		$ITEM_STAT		=	$_GET['ITEM_STAT'];
		
		if ($BRAND != 'ALL')
		{
			$sel_brand	=	"SELECT BRAND_NAME FROM FDC_PMS.BRAND_NEW WHERE BRAND_ID = '{$BRAND}' ";
			$rssel_brand=	$Filstar_171->Execute($sel_brand);
			if ($rssel_brand==false) 
			{
				echo $Filstar_171->ErrorMsg()."::".__LINE__;exit();
			}
			$_SESSION['BRAND_NAME']	=	$rssel_brand->fields['BRAND_NAME'];
		}
		else 
		{
			$_SESSION['BRAND_NAME']	=	'ALL';
		}
		
		if ($DESTINATION_ == 'ALL')
		{
			$_SESSION['D']	=	'CHOPPING AND TIANGGE';
		}
		else if ($DESTINATION_ == '1') 
		{
			$_SESSION['D']	=	'CHOPPING';
		}
		else 
		{
			 $_SESSION['D']	=	'TIANGGE';
		}
		
		
		$sel_transno	 =	"SELECT TRANSNO,TYPE FROM WMS_NEW.TIANGGE_HDR WHERE SCANDATE BETWEEN '{$DFROM}' AND '{$DTO}' ";
		if ($SOURCE_ != 'ALL') 
		{
		$sel_transno	.=	"AND SOURCE = '{$SOURCE_}' ";
		}
		if ($DESTINATION_ != 'ALL')
		{
		$sel_transno	.=	"AND TYPE = '{$DESTINATION_}' ";
		}
		if ($SEL_STATUS != 'ALL') 
		{
		$sel_transno	.=	"AND  STATUS = '{$SEL_STATUS}' ";	
		}
		$rssel_transno	=	$Filstar_loc->Execute($sel_transno);
		if ($rssel_transno==false) 
		{
			echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_transno->EOF) 
		{
			$transno	=	$rssel_transno->fields['TRANSNO'];
			$DESTINATION=	$rssel_transno->fields['TYPE'];
			
			$sel_dtl	=	"SELECT  * FROM WMS_NEW.TIANGGE_DTL WHERE TRANSNO = '{$transno}' ";
			if ($BRAND != 'ALL') 
			{
			$sel_dtl	.=	"AND BRAND = '{$_SESSION['BRAND_NAME']}' ";
			}
			if ($ITEM_STAT != 'ALL') 
			{
				$sel_dtl	.=	"AND ITEMSTATUS = '{$ITEM_STAT}' ";
			}
			$rssel_dtl	=	$Filstar_loc->Execute($sel_dtl);
			if ($rssel_dtl == false) 
			{
				echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
			}
			while (!$rssel_dtl->EOF) 
			{
				$TRANSNO		=	$rssel_dtl->fields['TRANSNO'];
				$SKUNO			=	$rssel_dtl->fields['SKUNO'];
				$ITEMSTATUS		=	$rssel_dtl->fields['ITEMSTATUS'];
				$QTY			=	$rssel_dtl->fields['QTY'];
				$RETAILPRICE	=	$rssel_dtl->fields['RETAILPRICE'];
				$PRODCOST		=	$rssel_dtl->fields['PRODCOST'];
				$GROSSAMOUNT	=	$rssel_dtl->fields['GROSSAMOUNT'];
				$COSTAMOUNT		=	$rssel_dtl->fields['COSTAMOUNT'];
				$BRAND_			=	$rssel_dtl->fields['BRAND'];
				$CATEGORY		=	$rssel_dtl->fields['CATEGORY'];
				$SUBCATEGORY	=	$rssel_dtl->fields['SUBCATEGORY'];
				$CLASS			=	$rssel_dtl->fields['CLASS'];
				
				$aData[$SKUNO]['QTY']			+=	$QTY;
				$aData[$SKUNO]['BRAND']			=	$BRAND_;
				$aData[$SKUNO]['STATUS']		=	$ITEMSTATUS;
				$aData[$SKUNO]['RETAILPRICE']	=	$RETAILPRICE;
				$aData[$SKUNO]['PRODCOST']		=	$PRODCOST;
				$aData[$SKUNO]['GROSSAMOUNT']	+=	$GROSSAMOUNT;
				$aData[$SKUNO]['COSTAMOUNT']	+=	$COSTAMOUNT;
				
				$rssel_dtl->MoveNext();
			}
			$rssel_transno->MoveNext();
		}
		
		$show	=	"<table width='100%' border='0'>";
			$show	.=	"<tr class='Header_style'>";
			$show	.=		"<td width='5%' nowrap>";
			$show	.=			"LINE NO.";
			$show	.=		"</td>";
			$show	.=				"<td width='10%' align='center' nowrap>";
			$show	.=					"BRAND";
			$show	.=				"</td>";
			$show	.=						"<td width='10%' align='center' nowrap>";
			$show	.=							"ITEMNO";
			$show	.=						"</td>";
			$show	.=								"<td width='40%' align='center' nowrap>";
			$show	.=									"DESCRIPTION";
			$show	.=								"</td>";
			$show	.=										"<td width='5%' align='center' nowrap>";
			$show	.=											"QTY";
			$show	.=										"</td>";
			$show	.=												"<td width='10%' align='center' nowrap>";
			$show	.=													"PRODUCT COST";
			$show	.=												"</td>";
			$show	.=														"<td width='10%' align='center' nowrap>";
			$show	.=															"SRP";
			$show	.=														"</td>";
			$show	.=																"<td width='10%' align='center' nowrap>";
			$show	.=																	"COST AMOUNT";
			$show	.=																"</td>";
			$show	.=	"</tr>";
			
			if (count($aData) > 0)
			{
				$CNT		=	1;
				$total_qty	=	0;
				$total_cost	=	0;
				$total_amt	=	0;
				foreach ($aData as $key_sku=>$val__)
				{
					
					$QTY	=	$val__['QTY'];
					$BRAND	=	$val__['BRAND'];
					$STATUS	=	$val__['STATUS'];
					$COST	=	$val__['RETAILPRICE'];
					$SRP	=	$val__['PRODCOST'];
					$GROSS	=	$val__['GROSSAMOUNT'];
					$COST	=	$val__['COSTAMOUNT'];
					
					$item_desc		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$key_sku}' ");
					
					
					$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' >";
					
					$show	.=	"<td align='center'  >";
					$show	.=	$CNT;
					$show	.=	"</td>";
		
					$show	.=	"<td align='center' >";
					$show	.=	$BRAND;
					$show	.=	"</td>";
		
					$show	.=	"<td align='center'  nowrap>";
					$show	.=	$key_sku;
					$show	.=	"</td>";
		
					$show	.=	"<td align='center' >";
					$show	.=	$item_desc;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center' >";
					$show	.=	$QTY;
					$show	.=	"</td>";
					
					$show	.=	"<td align='right'>";
					$show	.=	number_format($COST,2);
					$show	.=	"</td>";
					
					$show	.=	"<td align='right'>";
					$show	.=	number_format($SRP,2);
					$show	.=	"</td>";
					
					$show	.=	"<td align='right'>";
					$show	.=	number_format($GROSS,2);
					$show	.=	"</td>";
					
					$show	.=	"</tr>";
					
					$total_qty	+=	$QTY;
					$total_cost	+=	$COST;
					$total_amt	+=	$GROSS;
					$CNT++;
				}
				
				$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' >";
					
		
					$show	.=	"<td align='right' colspan='4'>";
					$show	.=	"TOTAL";
					$show	.=	"</td>";
					
					$show	.=	"<td align='right'>";
					$show	.=	$total_qty;
					$show	.=	"</td>";
					
					$show	.=	"<td align='right'>";
					$show	.=	number_format($total_cost,2);
					$show	.=	"</td>";
					
					$show	.=	"<td align='right'>";
					$show	.=	"&nbsp";
					$show	.=	"</td>";
					
					$show	.=	"<td align='right'>";
					$show	.=	number_format($total_amt,2);
					$show	.=	"</td>";
					
					$show	.=	"</tr>";
			}
			else 
			{
				$show	.=	"</tr class='Header_style'>";
				$show	.=		"<td colspan='8'>";
				$show	.=			"RECORD FOUND";
				$show	.=		"</td>";
				$show	.=	"<tr>";
			}
		$show	.=	"</table>";
		echo $show;
		exit();
	}
	
	
	
	if ($action=='Q_SEARCHCUST') 
	{
		$custno		=	addslashes($_GET['CUSTNO']);
		$custname	=	addslashes($_GET['CUSTNAME']);
			
		$sel	 =	"SELECT CustNo,CustName FROM  FDCRMSlive.custmast WHERE 1";
		
		if (!empty($custno)) 
		{
		$sel	.=	" AND CustNo like '%{$custno}%' ";
		}
		if (!empty($custname)) 
		{
		$sel	.=	" AND CustName like '%{$custname}%' ";
		}
		$sel	.=	" limit 20 ";
		$rssel	=	$Filstar_conn->Execute($sel);
		if ($rssel == false) 
		{
			echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__); 
			exit();
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
				echo "<option value=\"$cValue\" onclick=\"smartsel('click');\">".$Q_custname."</option>";
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
	
	function GET_BRAND(&$Filstar_171)
	{
		$sel_brand	=	"SELECT BRAND_ID,BRAND_NAME FROM FDC_PMS.BRAND_NEW WHERE 1 ";
		$rssel_brand=	$Filstar_171->Execute($sel_brand);
		if ($rssel_brand==false) 
		{
			echo $Filstar_171->ErrorMsg()."::".__LINE__;exit();
		}
		$aData	=	array();
		while (!$rssel_brand->EOF) 
		{
			$BRAND_ID	=	$rssel_brand->fields['BRAND_ID'];
			$BRAND_NAME	=	$rssel_brand->fields['BRAND_NAME'];
			
			$val		=	$BRAND_ID."|".$BRAND_NAME;
			
			$aData[]	=	$val;
			$rssel_brand->MoveNext();
		}
		return $aData;
	}
	
	
	function GET_SOURCE(&$Filstar_loc)
	{
		$sel_source		=	"SELECT CODE,DESCRIPTION FROM WMS_LOOKUP.SOURCE WHERE 1 ";
		$rssel_source	=	$Filstar_loc->Execute($sel_source);
		if ($rssel_source==false) 
		{
			echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
		}
		$aData	=	array();
		while (!$rssel_source->EOF) 
		{
			$CODE			=	$rssel_source->fields['CODE'];
			$DESCRIPTION	=	$rssel_source->fields['DESCRIPTION'];
			
			$val		=	$CODE."|".$DESCRIPTION;
			
			$aData[]	=	$val;
			$rssel_source->MoveNext();
		}
		return $aData;
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
table {
	font-size: 1em;
}

.no-close .ui-dialog-titlebar-close {
    display: none;
}
</style>
<script>
	function	key_item_sta()
	{
		var item_stat	=	$('#sel_item_status').val();
		if(item_stat!="ALL")
		{
			$('#sel_status').attr('disabled',true);
		}
		else
		{
			$('#sel_status').attr('disabled',false);
		}
	}


	function	DISPLAY_()
	{
		var dfrom			=	$('#dfrom').val();
		var dto				=	$('#dto').val();
		var	brand			=	$('#sel_brand').val();
		var	source_			=	$('#sel_source').val();
		var	destination_	=	$('#sel_destination').val();
		var	sel_status		=	$('#sel_status').val();
		var item_stat		=	$('#sel_item_status').val();
		if(dfrom == '' && dto == '')
		{
			alert('Invalid date range');
			return;
		}
		if(dfrom > dto)
		{
			alert('Invalid date range');
			return;
		}
		
		$.ajax({
				url			:	'tiangge.php?action=DISPLAY__&BRAND='+brand+'&DFROM='+dfrom+'&DTO='+dto+'&SOURCE_='+source_+'&DESTINATION_='+destination_+'&SEL_STATUS='+sel_status+'&ITEM_STAT='+item_stat,
				beforeSend	:	function()
							{
								$('#loading').dialog('open');
							},
				success		:	function(response)
							{
								$('#loading').dialog('close');
								$('#divresponse').dialog('open');
								$('#divresponse').html(response);
							}
		});
	}


	function	DOREPORT()
	{
		var dfrom			=	$('#dfrom').val();
		var dto				=	$('#dto').val();
		var	brand			=	$('#sel_brand').val();
		var	source_			=	$('#sel_source').val();
		var	destination_	=	$('#sel_destination').val();
		var	sel_status		=	$('#sel_status').val();
		var item_stat		=	$('#sel_item_status').val();
		if(dfrom == '' && dto == '')
		{
			alert('Invalid date range');
			return;
		}
		if(dfrom > dto)
		{
			alert('Invalid date range');
			return;
		}
		
		var url	=	'tiangge_PDF.php?action=PDF&BRAND='+brand+'&DFROM='+dfrom+'&DTO='+dto+'&SOURCE_='+source_+'&DESTINATION_='+destination_+'&SEL_STATUS='+sel_status+'&ITEM_STAT='+item_stat;
		window.open(url);
	}
</script>
</head>
<body style="font-size:12px;">
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="1"  class="Text_header">
			<tr>
				<td width="100%" align="center">
					<table border="0"class="label_text">
						<tr>
							<td>BRAND</td>
							<td>
								:<select id="sel_brand" name="sel_brand">
								<option value="ALL">--ALL--</option>
								<?php $brand	=	GET_BRAND($Filstar_171); 
										foreach ($brand as $brand_val)
										{
											$bran_new	=	explode("|",$brand_val);
											$id 		=	$bran_new[0];
											$name		=	$bran_new[1];
								?>
											<option value="<?php echo $id?>"><?php echo $name;?></option>
								<?php
											
										}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td>SOURCE</td>
							<td>
								:<select id="sel_source" name="sel_source">
									<option value="ALL">--ALL--</option>
									<?php $source	=	GET_SOURCE($Filstar_loc); 
										foreach ($source as $source_val)
										{
											$source_new	=	explode("|",$source_val);
											$id 		=	$source_new[0];
											$name		=	$source_new[1];
								?>
											<option value="<?php echo $id?>"><?php echo $name;?></option>
								<?php
											
										}
								?>
								</select>
							</td>
						</tr>
						<tr>
							<td>TYPE</td>
							<td>
								:<select id="sel_destination" name="sel_destination">
									<option value="ALL">--ALL--</option>
									<option value="1">CHOPPING</option>
									<option value="2">TIANGGE</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>ITEM STATUS</td>
							<td>
								:<select id="sel_item_status" name="sel_item_status" onkeyup="key_item_sta();" onclick="key_item_sta();">
									<option value="ALL">--ALL--</option>
									<option value="M">MODELINE</option>
									<option value="P">PRIMESTOCK</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>STATUS</td>
							<td>
								:<select id="sel_status" name="sel_status">
									<option value="ALL">--ALL--</option>
									<option value="SCANNED">SCANNED</option>
									<option value="DELIVERY">DELIVERY</option>
									<option value="DISPOSAL">DISPOSAL</option>
									<option value="DELIVERED">DELIVERED</option>
									<option value="DISPOSED">DISPOSED</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>DATE</td>
							<td>
								:<input type="text" name="dfrom" id="dfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;&nbsp;&nbsp;
							 	<input type="text" name="dto" 	id="dto" 	class="dates"	value="" size="10"  placeholder = "To"	>
							</td>
						</tr>
						<tr>
							<td align="center" colspan="2">
								<!--<input type="button" name="btnreport" id="btnreport" value="Submit" class="small_button" onclick="DOREPORT();">-->
								<input type="button" name="btnreport" id="btnreport" value="DISPLAY" class="small_button" onclick="DISPLAY_();">
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>
	<div id="divresponse"></div>
	<div id="loading" align="center"><br><img src="../../../images/loading/ajax-loader_fast.gif"><br>Loading...</div>
</body>
</html>
<script>
$(".dates").datepicker({ 
	dateFormat: 'yy-mm-dd',
	changeMonth: true,
    changeYear: true 
});

$("#loading").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	title:"Please Wait...",
	modal:true,
	width:200,
	height:100,
	dialogClass:"no-close",
	closeOnEscape:false,
	autoOpen:false,
	resizable:false
});

$("#divresponse").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	title:"SKU SUMMARY",
	modal:true,	width:1100,	height:500,	dialogClass:"no-close",	closeOnEscape:false,	autoOpen:false,	resizable:false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		},
		'PRINT': function()
		{
			var dfrom			=	$('#dfrom').val();
			var dto				=	$('#dto').val();
			var	brand			=	$('#sel_brand').val();
			var	source_			=	$('#sel_source').val();
			var	destination_	=	$('#sel_destination').val();
			var	sel_status		=	$('#sel_status').val();
			var item_stat		=	$('#sel_item_status').val();
			if(dfrom == '' && dto == '')
			{
				alert('Invalid date range');
				return;
			}
			if(dfrom > dto)
			{
				alert('Invalid date range');
				return;
			}
			
			var url	=	'tiangge_PDF.php?action=PDF&BRAND='+brand+'&DFROM='+dfrom+'&DTO='+dto+'&SOURCE_='+source_+'&DESTINATION_='+destination_+'&SEL_STATUS='+sel_status+'&ITEM_STAT='+item_stat;
			window.open(url);
		}
	}
});
</script>