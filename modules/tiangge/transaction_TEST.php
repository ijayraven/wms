<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../index.php'</script>";
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
	
	
	$action		=	$_GET['action'];
	
	if ($action=='DISPLAY')
	{

		$page			=	$_GET['PAGE'];
		
		$trxno			=	$_POST['txttransaction'];
		$status			=	$_POST['sel_status'];
		$source			=	$_POST['sel_source'];
		$destination	=	$_POST['sel_destination'];
		$data_from		=	$_POST['dfrom'];
		$data_to		=	$_POST['dto'];
		
		/**
		 * COUNTE HEADER FOR PAGING
		 */
		$sel_cnt	 =	"SELECT TRANSNO,STATUS,SOURCE,TYPE,SCANDATE FROM WMS_NEW.TIANGGE_HDR WHERE 1 ";
		if (!empty($trxno)) 
		{
		$sel_cnt	 .=	" AND TRANSNO = '{$trxno}' ";
		}
		if ($status!='ALL') 
		{
		$sel_cnt	 .=	" AND STATUS = '{$status}' ";
		}
		if ($source!='ALL')
		{
		$sel_cnt	 .=	" AND SOURCE = '{$source}' ";
		}
		if ($destination!='ALL')
		{
		$sel_cnt	 .=	" AND TYPE = '{$destination}' ";	
		}
		if (!empty($data_from) && !empty($data_to)) 
		{
		$sel_cnt	 .=	" AND SCANDATE BETWEEN '{$data_from}' AND '{$data_to}' ";
		}
		$rssel_cnt	  =	$Filstar_loc->Execute($sel_cnt);
		if ($rssel_cnt==false) 
		{
			echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
		}
		$cnt		=	$rssel_cnt->RecordCount();
		if ($cnt > 0)
		{
			$totalcount	=	ceil($cnt / PAGE_LIMIT);
			/**
			 * ENDING COUNTE HEADER FOR PAGING
			 */
			$sel_transno	 =	"SELECT TRANSNO,STATUS,SOURCE,TYPE,SCANDATE FROM WMS_NEW.TIANGGE_HDR WHERE 1 ";
			if (!empty($trxno)) 
			{
			$sel_transno	 .=	" AND TRANSNO = '{$trxno}' ";
			}
			if ($status!='ALL') 
			{
			$sel_transno	 .=	" AND STATUS = '{$status}' ";
			}
			if ($source!='ALL')
			{
			$sel_transno	 .=	" AND SOURCE = '{$source}' ";
			}
			if ($destination!='ALL')
			{
			$sel_transno	 .=	" AND TYPE = '{$destination}' ";	
			}
			if (!empty($data_from) && !empty($data_to)) 
			{
			$sel_transno	 .=	" AND SCANDATE BETWEEN '{$data_from}' AND '{$data_to}' ";
			}
			$sel_transno	.=	" ORDER BY SCANDATE DESC LIMIT ".($page * PAGE_LIMIT).",".PAGE_LIMIT." ";
			
			$rssel_transno	  =	$Filstar_loc->Execute($sel_transno);
			if ($rssel_transno==false) 
			{
				echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
			}
			$show	=	"<table width='100%' border='0'>";
			$show	.=	"<tr class='Header_style'>";
			$show	.=		"<td width='10%' nowrap>";
			$show	.=			"TRANSACTION NO";
			$show	.=		"</td>";
			$show	.=				"<td width='10%' align='center' nowrap>";
			$show	.=					"STATUS";
			$show	.=				"</td>";
			$show	.=						"<td width='10%' align='center' nowrap>";
			$show	.=							"TYPE";
			$show	.=						"</td>";
			$show	.=								"<td width='15%' align='center' nowrap>";
			$show	.=									"SOURCE";
			$show	.=								"</td>";
			$show	.=										"<td width='10%' align='center' nowrap>";
			$show	.=											"SCAN DATE";
			$show	.=										"</td>";
			$show	.=												"<td width='10%' align='center' nowrap>";
			$show	.=													"ACTION";
			$show	.=												"</td>";
			$show	.=	"</tr>";
			
			while (!$rssel_transno->EOF) 
			{
				$TRANSNO	=	$rssel_transno->fields['TRANSNO'];
				$STATUS		=	$rssel_transno->fields['STATUS'];
				$SOURCE		=	$rssel_transno->fields['SOURCE'];
				$TYPE		=	$rssel_transno->fields['TYPE'];
				$SCANDATE	=	$rssel_transno->fields['SCANDATE'];
				
				$this_source=	$global_func->Select_val($Filstar_loc,"WMS_LOOKUP","SOURCE","DESCRIPTION","CODE = '{$SOURCE}' ");
				if ($TYPE == '1') 
				{
					$this_type	=	'CHOPPING';
				}
				else 
				{
					$this_type	=	'TIANGGE';
				}
				//$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" title=\"Click to view SOF No.\" class='Text_header_hover' $do_script>";
				$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' onclick=display_item('{$TRANSNO}')>";
				$show	.=	"<td align='center'>";
				$show	.=	$TRANSNO;
				$show	.=	"</td>";
	
				$show	.=	"<td align='center'>";
				$show	.=	$STATUS;
				$show	.=	"</td>";
	
				$show	.=	"<td align='center' nowrap>";
				$show	.=	$this_type;
				$show	.=	"</td>";
	
				$show	.=	"<td align='center'>";
				$show	.=	$this_source;
				$show	.=	"</td>";
				
				
				$show	.=	"<td align='center'>";
				$show	.=	$SCANDATE;
				$show	.=	"</td>";
	
				$show	.=	"<td align='center'>";
				//for SCANNED AND CHOPPING
				if ($_SESSION['username']!='rmpwh')
				{
					if ($STATUS == 'SCANNED' && $TYPE == '1') 
					{
					$show	.=		"<input type='button' name='btndisposal' id='btndisposal' class='small_button' style='width:90px' value='DISPOSAL' onclick=DOACTION('DISPOSAL','{$TRANSNO}');>";
					
					}
					else if($STATUS=='SCANNED' && $TYPE == '2')
					{
					$show	.=		"<input type='button' name='btndelivery' id='btndelivery' class='small_button' style='width:90px' value='DELIVERY' onclick=DOACTION('DELIVERY','{$TRANSNO}');>";
					$show	.=		"<input type='button' name='btnprint' id='btnprint' class='small_button' style='width:90px' value='PRINT' onclick=DOACTION('PRINT','{$TRANSNO}');>";
					}
					elseif ($STATUS=='DELIVERY')
					{
					$show	.=		"<input type='button' name='btnpost' id='btnpost' class='small_button' style='width:90px' value='POST' onclick=DOACTION('POST','{$TRANSNO}');>";
					}
					elseif ($STATUS=='2')
					{
					$show	.=		"<input type='button' name='btnpost' id='btnpost' class='small_button' style='width:90px' value='POST' onclick=DOACTION('POST','{$TRANSNO}');>";
					}
				}
				else 
				{
					if ($STATUS=='DISPOSAL') 
					{
					$show	.=		"<input type='button' name='btnpost' id='btnpost' class='small_button' style='width:90px' value='POST' onclick=DOACTION('POST','{$TRANSNO}');>";
					}
					else 
					{
					$show	.=		"";
					}
				}
				$show	.=	"</td>";
				$rssel_transno->MoveNext();
			}
			$currentpg	=	$page	+	1;
			$show	.=	"<tr>";
			$show	.=	"<td align='center' colspan='6'>";
			$show	.=	"<input type='button' value='first' ".($page == 0 ? "disabled" : " onclick=\"DISPLAY_(0);\" ").">";
			$show	.=	"<input type='button' value='prev'  ".($page == 0 ? "disabled" : "onclick=\"DISPLAY_('".($page - 1)."');\" ").">";
			$show	.=	"<input type='text' name='txtpage' id='txtpage' value='$currentpg/$totalcount' size='7' style='text-align:center;' readonly>";
			$show	.=	"<input type='button' value='next' ".(($page + 1) == $totalcount ? "disabled" : " onclick=\"DISPLAY_('".($page + 1)."');\" ").">";
			$show	.=	"<input type='button' value='last' ".(($page + 1) == $totalcount ? "disabled" : " onclick=\"DISPLAY_('".($totalcount - 1)."');\" ").">";
			$show	.=	"</td>";
			$show	.=	"</tr>";
			$show	.=	"</table>";
		}
		else 
		{
			$show	=	"<table width='100%' border='0'>";
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
			$show	.=	"<td width='100%' colspan='6' align='center'>";
			$show	.=	"<blink>NOTHING TO DISPLAY</blink>";
			$show	.=	"</td>";
			$show	.=	"</tr>";
			$show	.=	"</table>";
		}
		echo $show;
		exit();
	}
	
	
	if ($action=='ITEM_LIST') 
	{
		$VAL_SOF=	$_GET['VAL_SOF'];
		
		$SOF	=	"SELECT * FROM WMS_NEW.TIANGGE_DTL WHERE TRANSNO = '{$VAL_SOF}' ";
		$rsSOF	=	$Filstar_conn->Execute($SOF);
		if ($rsSOF==false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		$show	.=	"<table width='100%' border='0'>";
		$show	.=	"<tr style='font-size:15px;'>";
		$show	.=		"<td width='5%' nowrap colspan='6'>";
		$show	.=			$VAL_SOF;
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr class='Header_style' style='font-size:15px;'>";
		$show	.=		"<td width='5%' nowrap>";
		$show	.=			"LINENO";
		$show	.=		"</td>";
		$show	.=				"<td width='15%' align='center' nowrap>";
		$show	.=					"SKUNO";
		$show	.=				"</td>";
		$show	.=						"<td width='42%' align='center' nowrap>";
		$show	.=							"DESCRIPTION";
		$show	.=						"</td>";
		$show	.=								"<td width='10%' align='center' nowrap>";
		$show	.=									"QTY";
		$show	.=								"</td>";
		$show	.=										"<td width='7%' align='center' nowrap>";
		$show	.=											"UNITPRICE";
		$show	.=										"</td>";
		$show	.=												"<td width='7%' align='center' nowrap>";
		$show	.=													"UNITCOST";
		$show	.=												"</td>";
		$show	.=														"<td width='7%' align='center' nowrap>";
		$show	.=															"GROSSAMT";
		$show	.=														"</td>";
		$show	.=																"<td width='7%' align='center' nowrap>";
		$show	.=																	"NETAMT";
		$show	.=																"</td>";
		
		 	
		$show	.=	"</tr>";
		
		$COUNTER	=	1;
		$total_gross=	0;
		$total_net	=	0;
		
		while (!$rsSOF->EOF) 
		{
			
			$SOF		=	$rsSOF->fields['SOF'];
			$PONO		=	$rsSOF->fields['PONO'];
			$RFNO		=	$rsSOF->fields['RFNO'];
			$SKUNO		=	$rsSOF->fields['SKUNO'];
			$QTY		=	$rsSOF->fields['QTY'];
			$UNITPRICE	=	$rsSOF->fields['RETAILPRICE'];
			$PRODCOST	=	$rsSOF->fields['PRODCOST'];
			$GROSSAMT	=	$rsSOF->fields['GROSSAMOUNT'];
			$NETAMT		=	$rsSOF->fields['COSTAMOUNT'];
			
			
			$ItemDesc	=	substr($global_func->Select_val($Filstar_conn,FDCRMS,"itemmaster","ItemDesc","ItemNo = '".$SKUNO."'"),0,40);
			
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#cccccc';\" bgcolor=\"#cccccc\" class='Text_header_hover' style='font-size:13px;' >";
			$show	.=	"<td align='center'  >";
			$show	.=	$COUNTER;
			$show	.=	"</td>";

			$show	.=	"<td align='center'  >";
			$show	.=	$SKUNO;
			$show	.=	"</td>";

			$show	.=	"<td align='center'   nowrap >";
			$show	.=	$ItemDesc;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'   nowrap>";
			$show	.=	$QTY;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'   nowrap>";
			$show	.=	$UNITPRICE;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'   nowrap>";
			$show	.=	$PRODCOST;
			$show	.=	"</td>";
			
			$show	.=	"<td align='right'   nowrap>";
			$show	.=	number_format($rsSOF->fields['GROSSAMOUNT'],2);
			$show	.=	"</td>";
			
			$show	.=	"<td align='right'   nowrap>";
			$show	.=	number_format($rsSOF->fields['COSTAMOUNT'],2);
			$show	.=	"</td>";
			
			
			$total_gross	+=	$rsSOF->fields['GROSSAMOUNT'];
			$total_net		+=	$rsSOF->fields['COSTAMOUNT'];
			
			$rsSOF->MoveNext();
		}
		
		$show	.=	"<tr class='Text_header_hover' style='font-size:13px;'>";
		
		$show	.=	"<td align='center' colspan='4'>";
		$show	.=	"&nbsp;";
		$show	.=	"</td>";
		$show	.=	"<td align='center'  nowrap>";
		$show	.=	"TOTAL";
		$show	.=	"</td>";
		$show	.=	"<td align='center'  nowrap>";
		$show	.=	"&nbsp;";
		$show	.=	"</td>";
		
		$show	.=	"<td align='right'  nowrap>";
		$show	.=	number_format($total_gross,2);
		$show	.=	"</td>";
		
		$show	.=	"<td align='right'  nowrap>";
		$show	.=	number_format($total_net,2);
		$show	.=	"</td>";
		
		$show	.=	"</tr>";
		
		$show	.=	"</table>";
		
		echo $show;
		exit();
	}
	
	
	if ($action=='DODELIVERY') 
	{
		
		$CUSTCODE	=	$_GET['CUSTCODE'];
		$TXTDRNO	=	$_GET['TXTDRNO'];
		$TRXNO		=	$_GET['TRXNO'];
		$PRICEPOINT	=	$_GET['PRICEPOINT'];
		
		$Filstar_loc->StartTrans();
		$update		=	"UPDATE WMS_NEW.TIANGGE_HDR SET STATUS = 'DELIVERY', CUSTCODE = '{$CUSTCODE}', DRNO = '{$TXTDRNO}',PRICEPOINT = '{$PRICEPOINT}' ,
						DELIVERYBY = '{$_SESSION['username']}', DELIVERYDATE = SYSDATE(),DELIVERYTIME = SYSDATE()
	 					WHERE TRANSNO = '{$TRXNO}' ";
		$rsupdate	=	$Filstar_loc->Execute($update);
		if ($rsupdate == false) 
		{
			echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
		}
		else 
		{
			$response	=	"done";
		}
		$Filstar_loc->CompleteTrans();
		echo $response;
		exit();
	}
	
	if ($action=='DOPOSTING') 
	{
		$trxno	=	$_GET['THIS_TRXNO'];
		
		$sel_status		=	"select STATUS from WMS_NEW.TIANGGE_HDR WHERE TRANSNO = '{$trxno}' ";
		$rssel_status	=	$Filstar_loc->Execute($sel_status);
		if ($rssel_status==false) 
		{
			echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
		}
		$STATUS			=	$rssel_status->fields['STATUS'];
		if ($STATUS == 'DELIVERY') 
		{
			$UPDATE 	=	"UPDATE WMS_NEW.TIANGGE_HDR SET STATUS = 'DELIVERED',POSTEDBY = '{$_SESSION['username']}',POSTEDDATE=SYSDATE(),POSTEDTIME=SYSDATE() 
							WHERE  TRANSNO = '{$trxno}' ";
			$rsupdate	=	$Filstar_loc->Execute($UPDATE);
			if ($rsupdate==false) 
			{
				echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
			}
		}
		elseif ($STATUS == 'DISPOSAL')
		{
			$UPDATE 	=	"UPDATE WMS_NEW.TIANGGE_HDR SET STATUS = 'DISPOSED',POSTEDBY = '{$_SESSION['username']}',POSTEDDATE=SYSDATE(),POSTEDTIME=SYSDATE() 
							WHERE  TRANSNO = '{$trxno}' ";
			$rsupdate	=	$Filstar_loc->Execute($UPDATE);
			if ($rsupdate==false) 
			{
				echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
			}
		}
		echo "done";
		exit();
	}
	
	
	if ($action=='SEARCHCUST')
	{
		$custcode	=	$_GET['CUSTCODE'];
		$sel	 =	"SELECT CustNo,CustName from custmast where 1 ";
		if (!empty($custcode))
		{
			$sel	.=	"AND CustNo like '%{$custcode}%' AND CustStatus = 'A' ";
		}
		$rssel	=	$Filstar_loc->Execute($sel);
		if ($rssel == false)
		{
			echo $Filstar_loc->ErrorMsg()."::".__LINE__;
			exit();
		}
		$cnt	=	$rssel->RecordCount();
		if ($cnt > 0)
		{
			$custno		=	$rssel->fields['CustNo'];
			$custname	=	$rssel->fields['CustName'];
			$cValue		=	$custno."|".$custname;
			echo $cValue;
			exit();
		}
		else
		{
			echo "zero";
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
</style>
</head>
<body onload="DISPLAY_(0);">
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0" class="Text_header">
			<tr>
				<td width="30%">
					&nbsp;
				</td>
				<td width="10%">TRANSACTION</td>
				<td width="70%">
					:<input type="text" id="txttransaction" name="txttransaction" value="">
				</td>
			</tr>
			<tr>
				<td width="30%">
					&nbsp;
				</td>
				<td width="10%">STATUS</td>
				<td width="70%">
					:<select id="sel_status" name="sel_status">
						<option value="ALL">--ALL--</option>
						<option value="SCANNED">SCANNED</option>
						<option value="DELIVERY">DELIVERY</option>
						<option value="DISPOSAL">DISPOSAL</option>
						<option value="DELIVERED">DELIVERED</option>
						<option value="DISPOSED">DISPOSED</option>
						<option value="CANCELLED">CANCELLED</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="30%">
					&nbsp;
				</td>
				<td width="10%">SOURCE</td>
				<td width="70%">
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
				<td width="30%">
					&nbsp;
				</td>
				<td width="10%">TYPE</td>
				<td width="70%">
					:<select id="sel_destination" name="sel_destination">
						<option value="ALL">--ALL--</option>
						<option value="1">CHOPPING</option>
						<option value="2">TIANGGE</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="30%">
					&nbsp;
				</td>
				<td width="10%">DATE</td>
				<td width="70%">
					:<input type="text" name="dfrom" id="dfrom" 	class="dates" 	value="" size="10" >&nbsp;&nbsp;&nbsp;&nbsp;
				 	<input type="text" name="dto" 	id="dto" 	class="dates"	value="" size="10"  >
				</td>
			</tr>
			<tr>
				<td align="center" colspan="3">
					<input type="button" name="btnreport" id="btnreport" value="SEARCH" class="small_button" onclick="DISPLAY_();">
				</td>
			</tr>
			<tr>
				<td width="100%" colspan="3" align="center">
					<div id="divloader" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
				</td>
			</tr>
		</table>
		<table width="100%" border="0">
			<tr>
				<td colspan="5">
					<div id="divlist"></div>
					<input type="hidden" id="hdntrxno" name="hdntrxno" value="" readonly>
				</td>
			</tr>
		</table>
		<div id="dialog_delivery" title="DELIVERY">
			<table width="100%" border="1"  class="Text_header">
				<tr>
					<td width="20%">
						CUSTOMER
					</td>
					<td width="70%" nowrap>
						<input type="text" name="txtcustcode" id="txtcustcode" value="" size="10" maxlength="8" onkeyup="GET_CUSTCODE(event,value);">
						<input type="text" name="txtcustname" id="txtcustname" value="" size="38" readonly>
						<input type="hidden" name="txtspliter" id="txtspliter" value="" size="" readonly>
					</td>
				</tr>
				<tr>
					<td width="20%" nowrap>
						DELIVERY NO.
					</td>
					<td width="70%">
						<input type="text" name="txtdrno" id="txtdrno" value="" size="30" onkeyup="VALIDATE_(event,this.value);">
					</td>
				</tr>
				<tr>
					<td width="20%" nowrap>
						PRICEPOINT
					</td>
					<td width="70%">
						<input type="text" name="txtpricepoint" id="txtpricepoint" value="" size="30">
					</td>
				</tr>
			</table>
		</div>
		<div id="divdata_detail" title="ITEM LIST"></div>
		<div id="divloader2" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
	</form>
</body>
</html>
<script>
$(".dates").datepicker({ 
	dateFormat: 'yy-mm-dd',
	changeMonth: true,
    changeYear: true 
});


function	DISPLAY_(val_page)
{
	var dataform	=	$('#dataform').serialize();
	var dfrom		=	$('#dfrom').serialize();
	var dto			=	$('#dto').serialize();
	
	$.ajax({
			type		:	'POST',
			data		:	dataform,
			url			:	'transaction.php?action=DISPLAY&PAGE='+val_page,
			beforeSend	:	function()
						{
							$('#divloader').show();
						},
			success		:	function(response)
						{
							$('#divloader').hide();
							$('#divlist').html(response);
							$('#divlist').show();
						}
	});
}

function	display_item(val_sof)
{
	$.ajax({
			url			:	'transaction.php?action=ITEM_LIST&VAL_SOF='+val_sof,
			beforeSend	:	function()
						{
							$('#divloader2').dialog('open');
						},
			success		:	function(response)
						{
							$('#divloader2').dialog('close');
							$('#divdata_detail').dialog('open');
							$('#divdata_detail').html(response);
						}
	});
}

function DOACTION(val_action,val_traxno)
{
	var this_action		=	val_action;
	var this_trxno		=	val_traxno;
	
	if(this_action=='DELIVERY')
	{
		$('#hdntrxno').val();
		$('#dialog_delivery').dialog('open');
		$('#hdntrxno').val(val_traxno);
	}
	else if(this_action == 'DISPOSAL')
	{
		var url	=	'transaction_disposal_pdf.php?action=PDF&THIS_TRXNO='+this_trxno;
		window.open(url);
	}
	else if(this_action == 'PRINT')
	{
//		alert("MAINTENANCE -- "+val_traxno);return;
		var url	=	'transaction_print.php?action=PDF&THIS_TRXNO='+this_trxno;
		window.open(url);
	}
	else
	{
		var isconfirm	=	confirm('Are you sure you want to post this transaction?');
		if(isconfirm==true)
		{
			$.ajax({
					url			:	'transaction.php?action=DOPOSTING&THIS_TRXNO='+this_trxno,
					beforeSend	:	function()
								{
									$('#divloader').show();
								},
					success		:	function(response)
								{
									$('#divloader').hide();
									if(response=='done')	
									{
										alert('Transaction was successfully posted');
										DISPLAY_(0);
									}
									else
									{
										$('#divloader').html(response);
										$('#divloader').show();
									}
								}
					
			});
		}
	}
	DISPLAY_(0);
}

function GET_CUSTCODE(evt,val__)
{
	var evtHandler	=	(evt.charCode) ? evt.charCode	: evt.keyCode;
	if(evtHandler==13 && val__ != '')
	{
		$.ajax({
				url			:	'transaction.php?action=SEARCHCUST&CUSTCODE='+val__,
				success		:	function(response)
				{
					if(response == 'zero')
					{
						alert('No record found...');
						$('#txtspliter').val(response);
						$('#txtcustname').val('');
					}
					else
					{
						$('#txtspliter').val(response);
						var vx = $('#txtspliter').val();
						var x = vx.split('|');
						var custcode	=	x[0];
						var custname	=	x[1];
						$('#txtcustcode').val(custcode);
						$('#txtcustname').val(custname);
						$('#txtdrno').focus();
					}
				}
			});
	}
}


$("#divloader2").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 100, width: 170, modal:true, autoOpen: false,	draggable: false
});

$("#divdata_detail").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 500, width: 1100, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		},
	}
});

$("#dialog_delivery").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 180, width: 450, modal:true, autoOpen: false,	draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		},
		'SUBMIT': function()
		{
			var custcode	=	$('#txtcustcode').val();
			var test		=	$('#txtspliter').val();
			var txtdrno		=	$('#txtdrno').val();
			var trxno		=	$('#hdntrxno').val();
			var pricepoint	=	$('#txtpricepoint').val();
			if(custcode != '' && txtdrno != '')
			{
				$.ajax({
						url			:	'transaction.php?action=DODELIVERY&CUSTCODE='+custcode+'&TXTDRNO='+txtdrno+'&TRXNO='+trxno+'&PRICEPOINT='+pricepoint,
						beforeSend	:	function()
									{
										$('#divloader').show();
									},
						success		:	function(response)
									{
										$('#divloader').hide();
										if(response=='done')
										{
											alert('Transaction was successfully saved!');
											$('#dialog_delivery').dialog('close');
											var url	=	'transaction_delivery_pdf.php?action=PDF&THIS_TRXNO='+trxno;
											window.open(url);
										}
										else
										{
											$('#divloader').html(response);
											$('#divloader').show();
										}
										DISPLAY_(0);
									}
						
				});
			}
			else
			{
				alert('Please insert Customer and DR No.');
			}
			//$(this).dialog('close');
		}
	}
});


function	VALIDATE_(evt,val_)
{
	var evtHandler	=	(evt.charCode) ? evt.charCode	: evt.keyCode;
	var	val__	=	val_;
	if(evtHandler==13 && val__ != '')
	{
		$('#txtpricepoint').focus();
	}
}

</script>