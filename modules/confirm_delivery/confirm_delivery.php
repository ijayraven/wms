<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../index.php'</script>";
}

	$action				=	$_GET['action'];

	$action				=	$_GET['action'];
	//$_SESSION['DO_IA']	=	"NO";
	
	if ($action=='GETDATA') 
	{
		$OPT		=	$_GET['OPT'];
		$THIS_VAL	=	$_GET['VAL_'];
		$OPT2		=	$_GET['OPT2'];
		
		$today		=	date('Y-m-d',strtotime("-1 day"));
		
		$isconfirm	=	"SELECT SOF from WMS_NEW.CONFIRMDELIVERY_HDR WHERE DOCNO = '{$THIS_VAL}' AND DOCTYPE = '{$OPT}' ";
		$rsisconfirm=	$Filstar_conn->Execute($isconfirm);
		if ($rsisconfirm==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$confirmed	=	$rsisconfirm->RecordCount();
		if ($confirmed == 0) 
		{
			$sel_data	=	"SELECT OrderNo,CustNo,InvoiceNo,OrderAmount,InvoiceAmount from orderheader where InvoiceNo = '{$THIS_VAL}' and OrderCategory = '{$OPT}' ";
			$rssel_data =	$Filstar_conn->Execute($sel_data);
			if ($rssel_data ==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			
			$record		=	$rssel_data->RecordCount();
			if ($record > 0) 
			{
			$OrderNo	=	$rssel_data->fields['OrderNo'];
			$CustNo		=	$rssel_data->fields['CustNo'];
			$InvoiceNo	=	$rssel_data->fields['InvoiceNo'];
			$OrderAmount=	$rssel_data->fields['OrderAmount'];
			if ($OPT == 'STF')
			{
				$sel_gross	=	"SELECT sum(GrossAmount) as gross from orderdetail where OrderNo = '{$OrderNo}' and isDeleted = 'N' ";
				$rssel_gross=	$Filstar_conn->Execute($sel_gross);
				if ($rssel_gross==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				
				$NetAmount	=	$rssel_gross->fields['gross'];
			}
			else 
			{
				$NetAmount	=	$rssel_data->fields['InvoiceAmount'];
  			}
			
			if ($CustNo != 'REVPKG01C') 
			{
				if ($OPT2	== 'MANILA')
				{
					$sel_trackingno		=	"SELECT TRACKINGNO from DISPATCH.DISPATCH_METROMANILA_DTL WHERE SOFNO = '{$OrderNo}' and STATUS = 'OPEN' ";
				}
				else 
				{
					$sel_trackingno		=	"SELECT TRACKINGNO from DISPATCH.DISPATCH_PROVINCE_DTL WHERE SOFNO = '{$OrderNo}' and STATUS = 'OPEN' ";
				}
			
				$rssel_trackingno	=	$Filstar_conn->Execute($sel_trackingno);
				if ($rssel_trackingno==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				$count_dispatch		=	$rssel_trackingno->RecordCount();
			}
			else 
			{
				$count_dispatch		=	1;
			}
			
			if ($count_dispatch > 0)
			{
				$TRACKINGNO	=	$rssel_trackingno->fields['TRACKINGNO'];
				
				if ($CustNo != 'REVPKG01C') 
				{
					if ($OPT2=='MANILA') 
					{
						$sel_dispatch	=	"SELECT STATUS FROM DISPATCH.DISPATCH_METROMANILA_HDR WHERE TRACKINGNO = '{$TRACKINGNO}' ";
					}
					else 
					{
						$sel_dispatch	=	"SELECT STATUS FROM DISPATCH.DISPATCH_PROVINCE_HDR WHERE TRACKINGNO = '{$TRACKINGNO}' ";
					}
					$rssel_dispatch	=	$Filstar_conn->Execute($sel_dispatch);
					if ($rssel_dispatch==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					$STATUS		=	$rssel_dispatch->fields['STATUS'];
				}
				else 
				{
					$STATUS	=	'PRINTED';
				}
				
				if ($STATUS=='PRINTED') 
				{
					
					$CustName	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo='{$CustNo}'");
					
					$sel_D		=	"SELECT sum(ReleaseQty)as RCVD_QTY from orderdetail where OrderNo = '{$OrderNo}' and ReleaseQty != 0 and isDeleted = 'N' ";
					$rssel_D	=	$Filstar_conn->Execute($sel_D);
					if ($rssel_D==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					$RCVD_QTY	=	$rssel_D->fields['RCVD_QTY'];
					
					$show	=	"<table width='100%' border='0'>";
					$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
					$show	.=		"<td width='100%' colspan='4' style='font-size:10px;'>";
					$show	.=			"&nbsp;";
					$show	.=		"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
					$show	.=		"<td width='100%' colspan='4' style='font-size:25px'>";
					$show	.=			$CustNo.'-'.$CustName;
					$show	.=		"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
					$show	.=		"<td width='100%' colspan='4' style='font-size:10px;'>";
					$show	.=			"&nbsp;";
					$show	.=		"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
					$show	.=		"<td width='15%' class='Header_style' style='text-align:left;background-color:#4d61ff;' nowrap>";
					$show	.=			"STF NO";
					$show	.=		"</td>";
					$show	.=				"<td width='35%' align='center' bgcolor='#FFFFF' class='Text_header_value' nowrap>";
					$show	.=					$InvoiceNo;
					$show	.=				"</td>";
					$show	.=						"<td width='20%' class='Header_style' style='text-align:left;background-color:#4d61ff;' nowrap>";
					$show	.=							"TOTAL AMOUNT";
					$show	.=						"</td>";
					$show	.=								"<td width='30%' bgcolor='#FFFFF' class='Text_header_value' style='text-align:center' nowrap>";
					$show	.=									number_format($NetAmount,2);
					$show	.=								"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
					$show	.=		"<td width='15%' class='Header_style' style='text-align:left;background-color:#4d61ff;' nowrap>";
					$show	.=			"SOF NO";
					$show	.=		"</td>";
					$show	.=				"<td width='35%' align='center' bgcolor='#FFFFF' class='Text_header_value' nowrap>";
					$show	.=					$OrderNo;
					$show	.=				"</td>";
					$show	.=						"<td width='20%' class='Header_style' style='text-align:left;background-color:#4d61ff;' nowrap>";
					$show	.=							"TOTAL QUANTITY";
					$show	.=						"</td>";
					$show	.=								"<td width='30%' bgcolor='#FFFFF' class='Text_header_value' style='text-align:center' nowrap>";
					$show	.=									$RCVD_QTY;
					$show	.=								"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
					$show	.=		"<td width='15%' class='Header_style' style='text-align:left;background-color:#4d61ff;' nowrap>";
					$show	.=			"CONFIRMED DEL DATE";
					$show	.=		"</td>";
					$show	.=				"<td width='35%' bgcolor='#FFFFF' class='Text_header_value' align='center' nowrap>";
					$show	.=					"<input type='text' name='dateDEL' id='dateDEL' value='{$today}' style='text-align:center;border:0;font:15px Dejavu Sans, arial, helvetica, sans-serif;font-weight:bold;'>";
					$show	.=				"</td>";
					if ($OPT=='INVOICE')
					{
					$show	.=						"<td width='50%' colspan='2' bgcolor='#FFFFF' class='Text_header_value' nowrap>";
					$show	.=							"&nbsp;";
					$show	.=						"</td>";
					}
					else 
					{
					$show	.=						"<td width='20%' class='Header_style' style='text-align:left;background-color:#4d61ff;' nowrap>";
					$show	.=							"VARIANCE";
					$show	.=						"</td>";
					$show	.=								"<td width='30%' bgcolor='#FFFFF' class='Text_header_value' style='text-align:center' nowrap>";
					$show	.=									"<input type='radio' name='rddiscrepancy' id='discrepancy_N' value='N' checked>NO";
					$show	.=									"<input type='radio' name='rddiscrepancy' id='discrepancy_Y' value='Y' >YES";
					$show	.=								"</td>";
					}
					$show	.=	"</tr>";
					$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
					$show	.=		"<td width='100%' colspan='4' style='font-size:10px'>";
					$show	.=			"<input type='button' name='btnconfirm' id='btnconfirm' value='CONFIRM' class='small_button' style='width:120px;height:35px;' onclick=Confirm_data('{$OPT}','{$THIS_VAL}')>";
					$show	.=			"&nbsp;&nbsp;";
					$show	.=			"<input type='button' name='btncancel' id='btncancel' value='CANCEL' class='small_button' style='width:120px;height:35px;' onclick=Cancel_data('{$OPT}','{$THIS_VAL}')>";
					$show	.=			"&nbsp;&nbsp;";
					$show	.=			"<input type='button' name='btndisplay' id='btndisplay' value='DISPLAY' class='small_button'  style='width:120px;height:35px;' onclick=Display_item('{$OPT}','{$THIS_VAL}');>";
					$show	.=		"</td>";
					$show	.=	"</tr>";
					$show	.=	"</table>";
					
					
					$sel_D		=	"SELECT Item,OrderQty,ReleaseQty,UnitCost,UnitPrice,GrossAmount,NetAmount from orderdetail where OrderNo = '{$OrderNo}' and isDeleted = 'N' ";
					$rssel_D	=	$Filstar_conn->Execute($sel_D);
					if ($rssel_D==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
						$show	.=	"<div id='div_detail' style='display:none;'>";
						$show	.=	"<form id='form_detail' name='form_detail'>";
						$show	.=	"<table width='100%' border='0'>";
						$show	.=	"<tr class='Header_style' style='font-size:11px;'>";
						$show	.=		"<td width='8%' nowrap>";
						$show	.=			"SKUNO";
						$show	.=		"</td>";
						$show	.=				"<td width='32%' align='center' nowrap>";
						$show	.=					"DESCRIPTION";
						$show	.=				"</td>";
						$show	.=						"<td width='10%' align='center' nowrap>";
						$show	.=							"DEL";
						$show	.=						"</td>";
						$show	.=								"<td width='10%' align='center' nowrap>";
						$show	.=									"RCVD QTY";
						$show	.=								"</td>";
						$show	.=										"<td width='10%' align='center' nowrap>";
						$show	.=											"UNIT COST";
						$show	.=										"</td>";
						$show	.=												"<td width='10%' align='center' nowrap>";
						$show	.=													"UNIT PRICE";
						$show	.=												"</td>";
						$show	.=														"<td width='10%' align='center' nowrap>";
						$show	.=															"RCVD NET AMOUNT";
						$show	.=														"</td>";
						$show	.=																"<td width='10%' align='center' nowrap>";
						$show	.=																	"RCVD GROSS AMOUNT";
						$show	.=																"</td>";
						$show	.=																		"<td width='10%' align='center' nowrap>";
						$show	.=																			"REMARKS";
						$show	.=																		"</td>";
						$show	.=	"</tr>";
						while (!$rssel_D->EOF) 
						{
							$CNT++;
							$Item		=	$rssel_D->fields['Item'];
							$ItemDesc	=	substr($global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo='{$Item}'"),0,35);
							
							$OrderQty	=	$rssel_D->fields['OrderQty'];
							$ReleaseQty	=	$rssel_D->fields['ReleaseQty'];
							$UnitCost	=	$rssel_D->fields['UnitCost'];
							$UnitPrice	=	$rssel_D->fields['UnitPrice'];
							$GrossAmount=	number_format($rssel_D->fields['GrossAmount'],2);
							$NetAmount	=	number_format($rssel_D->fields['NetAmount'],2);
							
							$total_order+=	$OrderQty;
							$total_rcvd	+=	$ReleaseQty;
							$total_gross+=	$rssel_D->fields['GrossAmount'];
							$total_net	+=	$rssel_D->fields['NetAmount'];
							
							
							$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
							$show	.=	"<td align='center'>";
							$show	.=	$Item;
							$show	.=	"<input type='hidden' name='hdn_items_$CNT' id='hdn_items_$CNT' value='{$Item}' size='5'>";
							$show	.=	"</td>";
				
							$show	.=	"<td align='center'>";
							$show	.=	"<label id='item_desc_$CNT'>$ItemDesc</label>";
							$show	.=	"</td>";
				
							$show	.=	"<td align='center' nowrap>";
							$show	.=	$ReleaseQty;
							$show	.=	"<input type='hidden' name='txtDelQty_$CNT' id='txtDelQty_$CNT' value='{$ReleaseQty}' size='5'>";
							$show	.=	"</td>";
				
							$show	.=	"<td align='center'>";
							$show	.=	"<input type='text' name='txtRelQty_$CNT' id='txtRelQty_$CNT' value='{$ReleaseQty}' size='5' style='text-align:center' onkeyup=recompute('{$CNT}');isnumeric(this.value,this.id);>";
							$show	.=	"</td>";
							
							
							$show	.=	"<td align='center'>";
							$show	.=	$UnitCost;
							$show	.=	"<input type='hidden' name='txtUnitcost_$CNT' id='txtUnitcost_$CNT' value='{$UnitCost}' size='5'>";
							$show	.=	"<input type='hidden' name='new_net_$CNT' id='new_net_$CNT' value='{$rssel_D->fields['NetAmount']}' size='5'>";
							$show	.=	"<input type='hidden' name='old_net_$CNT' id='old_net_$CNT' value='{$rssel_D->fields['NetAmount']}' size='5'>";
							$show	.=	"</td>";
							
							$show	.=	"<td align='center'>";
							$show	.=	$UnitPrice;
							$show	.=	"<input type='hidden' name='txtUnitprice_$CNT' id='txtUnitprice_$CNT' value='{$UnitPrice}' size='5'>";
							$show	.=	"<input type='hidden' name='new_gross_$CNT' id='new_gross_$CNT' value='{$rssel_D->fields['GrossAmount']}' size='5'>";
							$show	.=	"<input type='hidden' name='old_gross_$CNT' id='old_gross_$CNT' value='{$rssel_D->fields['GrossAmount']}' size='5'>";
							$show	.=	"</td>";
							
							$show	.=	"<td align='center'>";
							$show	.=	"<label id='net_amount_$CNT' name='net_amount_$CNT'>$NetAmount<label>";
							$show	.=	"</td>";
							
							$show	.=	"<td align='center'>";
							$show	.=	"<label id='gross_amount_$CNT' name='gross_amount_$CNT'>$GrossAmount<label>";
							$show	.=	"</td>";
							$show	.=	"<td align='center' id='remard_color_$CNT'>";
							$show	.=	REMARKS_LOOKUP($Filstar_conn,$CNT);
							$show	.=	"<input type='hidden' name='hdn_remarks_$CNT' id='hdn_remarks_$CNT' value='SAME' size='5'>";
							$show	.=	"</td>";
							
							$rssel_D->MoveNext();
						}
						
						$show	.=	"<tr>";
						$show	.=	"<td align='center' colspan='2'>";
						$show	.=	"</td>";
						
						$show	.=	"<td align='center' colspan='1' class='Text_header_value'>";
						$show	.=	$total_rcvd;
						$show	.=	"</td>";
			
						$show	.=	"<td align='center' class='Text_header_value'>";
						$show	.=	"<label id='lbl_rcvd_qrt'>$total_rcvd</label>";
						$show	.=	"</td>";
			
						$show	.=	"<td align='center' colspan='2'>";
						$show	.=	"</td>";
						
						$show	.=	"<td align='center' class='Text_header_value'>";
						$show	.=	"<label id='lbl_net'>".number_format($total_net,2)."</label>";
						$show	.=	"</td>";
						
						$show	.=	"<td align='center' class='Text_header_value'>";
						$show	.=	"<label id='lbl_gross'>".number_format($total_gross,2)."</label>";
						$show	.=	"</td>";
						
						$show	.=	"<td align='center'>";
						$show	.=	"</td>";
						$show	.=	"</tr>";
						
						$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
						$show	.=		"<td width='100%' colspan='9' style='font-size:10px'>";
						$show	.=			"<input type='button' name='btnconfirm' id='btnconfirm' value='CONFIRM' class='small_button' style='width:120px;height:35px;' onclick=Confirm_data('{$OPT}','{$THIS_VAL}')>";
						$show	.=		"</td>";
						$show	.=	"</tr>";
						
						$show	.=	"<input type='hidden' name='split_net' id='split_net' value=''>";
						$show	.=	"<input type='hidden' name='split_gross' id='split_gross' value=''>";
						$show	.=	"<input type='hidden' name='total_cnt' id='total_cnt' value='{$CNT}' size='5'>";
						$show	.=	"</form>";
						$show	.=	"</div>";
					}
					else 
					{
						$show	=	"<table width='100%' border='0'>";
						$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
						$show	.=		"<td width='100%' colspan='4' style='font-size:30px;color:red'>";
						$show	.=			"<blink>NOT YET PRINTED IN DISPATCH</blink>";
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
					$show	.=			"<blink>NOT FOUND IN DISPATCH</blink>";
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
				$show	.=			"<blink>NOT RECORD FOUND</blink>";
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
			$show	.=			"<blink>DOCUMENT IS ALREADY CONFIRMED</blink>";
			$show	.=		"</td>";
			$show	.=	"</tr>";
			$show	.=	"</table>";
		}
		echo $show;
		exit();
	}
	
	if ($action=='SET_ITEMS')
	{
		
		if (empty($_SESSION['DO_IA'])) 
		{
			$_SESSION['DO_IA']	=	"NO";
		}
		
		$ITEM			=	$_GET['ITEM'];
		$DEL_QTY		=	$_GET['DEL_QTY'];
		$REL_QTY		=	$_GET['REL_QTY'];
		$NEW_GROSS		=	$_GET['NEW_GROSS_'];
		$OLD_GROSS_		=	$_GET['OLD_GROSS_'];
		$NEW_NET_		=	$_GET['NEW_NET_'];
		$OLD_NET_		=	$_GET['OLD_NET_'];
		$REMARKS_		=	$_GET['REMARKS_'];
		$HDN_REMARKS_	=	$_GET['HDN_REMARKS_'];
		
		if ($DEL_QTY != $REL_QTY) 
		{
			$_SESSION['DO_IA']	=	"YES";
		}
		
		$_SESSION['ITEM'][$ITEM]['DEL_QTY']		=	$DEL_QTY;
		$_SESSION['ITEM'][$ITEM]['REL_QTY']		=	$REL_QTY;
		$_SESSION['ITEM'][$ITEM]['NEW_GROSS']	=	$NEW_GROSS;
		$_SESSION['ITEM'][$ITEM]['OLD_GROSS_']	=	$OLD_GROSS_;
		$_SESSION['ITEM'][$ITEM]['NEW_NET_']	=	$NEW_NET_;
		$_SESSION['ITEM'][$ITEM]['OLD_NET_']	=	$OLD_NET_;
		$_SESSION['ITEM'][$ITEM]['REMARKS_']	=	$REMARKS_;
		$_SESSION['ITEM'][$ITEM]['HDN_REMARKS_']=	$HDN_REMARKS_;
		
		//print_r($_SESSION);
		exit();
	}
	
	
	if ($action=='CONFIRM__') 
	{
		$VAL_OPT	=	$_GET['VAL_OPT'];
		$VAL__		=	$_GET['VAL__'];
		$AMT_DISCREPANCY	=	$_GET['AMT_DISCREPANCY'];
		$IA			=	"NO";
		$new_net	=	0;
		$new_gross	=	0;
		$new_qty	=	0;
		
		$Filstar_conn->StartTrans();
		
		$sel_data	=	"SELECT OrderNo,CustNo,InvoiceNo,OrderAmount,InvoiceAmount from orderheader where InvoiceNo = '{$VAL__}' and OrderCategory = '{$VAL_OPT}' ";
		$rssel_data	=	$Filstar_conn->Execute($sel_data);
		if ($rssel_data==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$OrderNo		=	$rssel_data->fields['OrderNo'];
		$CustNo			=	$rssel_data->fields['CustNo'];
		$InvoiceNo		=	$rssel_data->fields['InvoiceNo'];
		$OrderAmount	=	$rssel_data->fields['OrderAmount'];
		$InvoiceAmount	=	$rssel_data->fields['InvoiceAmount'];
		
		$SalesRep		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CustNo}' ");
		
		$record	=	$_GET['TOTAL_CNT'];
		
//		for ($x=1;$x<=$record;$x++)
//		{
//			$del_qty		=	$_POST['txtDelQty_'.$x];
//			$rvc_qty		=	$_POST['txtRelQty_'.$x];
//			
//			if ($del_qty != $rvc_qty)
//			{
//				$IA			=	"YES";
//			}
//		}

		if ($_SESSION['DO_IA'] == 'YES')
		{
			$IA			=	"YES";
		}
		
		if ($IA=="YES") 
		{
			$ia_no	=	$global_func->TRANSEQ_IA($Filstar_conn);
		}
		else 
		{
			$ia_no	=	"";
		}

		foreach ($_SESSION['ITEM'] as $key=>$val_item) 
		{
			$item			=	$key;
			$del_qty		=	$val_item['DEL_QTY'];
			$rvc_qty		=	$val_item['REL_QTY'];
			$gross_new		=	$val_item['NEW_GROSS'];
			$gross_old		=	$val_item['OLD_GROSS_'];
			$net_new		=	$val_item['NEW_NET_'];
			$net_old		=	$val_item['OLD_NET_'];
			$remarks_		=	$val_item['REMARKS_'];
			$hdn_remarks_	=	$val_item['HDN_REMARKS_'];
			
			$new_total_net	+=	$net_new;
			$new_total_gross+=	$gross_new;
			$new_qty		+=	$rvc_qty;
			
			if ($del_qty!=$rvc_qty) 
			{
				if (!empty($item)) 
				{
					$insert_dtl	=	"INSERT INTO WMS_NEW.CONFIRMDELIVERY_DTL(`DOCNO`,`SOF`,`IATRANSNO`,`SKUNO`,`QTY`,`RECEIVEDQTY`,`REMARKS`)
									VALUES
									('{$InvoiceNo}','{$OrderNo}','{$ia_no}','{$item}','{$del_qty}','{$rvc_qty}','{$remarks_}')";
					$rsinsert_dtl=	$Filstar_conn->Execute($insert_dtl);
					if ($rsinsert_dtl==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					
					
					$AI_QTY		=	$del_qty-$rvc_qty;
					
					
					$insert_IA	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_DTL(`IATRANSNO`,`SKUNO`,`IAQTY`,`REMARKS`) 
									VALUES
									('{$ia_no}','{$item}','{$AI_QTY}','{$remarks_}')";
					$rsinsert_IA=	$Filstar_conn->Execute($insert_IA);
					if ($rsinsert_IA==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
				}
			}
			else 
			{
				if (!empty($item)) 
				{
					$insert_dtl	=	"INSERT INTO WMS_NEW.CONFIRMDELIVERY_DTL(`DOCNO`,`SOF`,`SKUNO`,`QTY`,`RECEIVEDQTY`,`REMARKS`)
									VALUES
									('{$InvoiceNo}','{$OrderNo}','{$item}','{$del_qty}','{$rvc_qty}','{$remarks_}')";
					$rsinsert_dtl	=	$Filstar_conn->Execute($insert_dtl);
					if ($rsinsert_dtl==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
				}
			}
			
		}
		
		$sel_qty		=	"SELECT sum(`ReleaseQty`)as TOTAL FROM orderdetail where OrderNo = '{$OrderNo}' and isDeleted = 'N' ";
		$rssel_qty		=	$Filstar_conn->Execute($sel_qty);
		if ($rssel_qty==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$TOTAL			=	$rssel_qty->fields['TOTAL'];
		
		$insert_hdr		=	"INSERT INTO WMS_NEW.CONFIRMDELIVERY_HDR(`SOF`,`DOCNO`,`CUSTNO`,`SRNO`,`DOCTYPE`,`NETAMOUNT`,`GROSSAMOUNT`,`TOTALQTY`,`IATRANSNO`,
							`RCVDNETAMOUNT`,`RCVDGROSSAMOUNT`,`RCVDTOTALQTY`,`CONFIRMDELDATE`,`ADDEDBY`,`ADDEDDATE`,`ADDEDTIME`,`VARIANCE`)
							VALUES
							('{$OrderNo}','{$InvoiceNo}','{$CustNo}','{$SalesRep}','{$VAL_OPT}','{$InvoiceAmount}','{$OrderAmount}','{$TOTAL}','{$ia_no}',
							'{$new_total_net}','{$new_total_gross}','{$new_qty}','{$_GET['DATEDEL']}','{$_SESSION['username']}',SYSDATE(),SYSDATE(),'{$AMT_DISCREPANCY}') ";
		$rsinsert_hdr	=	$Filstar_conn->Execute($insert_hdr);
		if ($rsinsert_hdr==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		if ($IA=='YES')
		{
			$insert_ia_hrd	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_HDR(`IATRANSNO`,`CUSTNO`,`REFN0`,`REFTYPE`,`STATUS`,`ADDEDDATE`,`ADDEDTIME`)
								VALUES('{$ia_no}','{$CustNo}','{$InvoiceNo}','{$VAL_OPT}','IN-PROCESS',SYSDATE(),SYSDATE())";
			$rsinsert_ia_hdr=	$Filstar_conn->Execute($insert_ia_hrd);
			if ($rsinsert_ia_hdr==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
		}
		
		unset($_SESSION['ITEM']);
		unset($_SESSION['DO_IA']);
		$Filstar_conn->CompleteTrans();
		echo "done";
		exit();
	}
	
	if ($action=='CANCEL__') 
	{
		$VAL_OPT	=	$_GET['VAL_OPT'];
		$VAL__		=	$_GET['VAL__'];
		$REMARKS_	=	$_GET['REMARKS_'];
		$IA			=	"NO";
		$new_net	=	0;
		$new_gross	=	0;
		$new_qty	=	0;
		
		$Filstar_conn->StartTrans();
		
		$sel_data	=	"SELECT OrderNo,CustNo,InvoiceNo,OrderAmount,InvoiceAmount from orderheader where InvoiceNo = '{$VAL__}' and OrderCategory = '{$VAL_OPT}' ";
		$rssel_data	=	$Filstar_conn->Execute($sel_data);
		if ($rssel_data==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$OrderNo		=	$rssel_data->fields['OrderNo'];
		$CustNo			=	$rssel_data->fields['CustNo'];
		$InvoiceNo		=	$rssel_data->fields['InvoiceNo'];
		$OrderAmount	=	$rssel_data->fields['OrderAmount'];
		$InvoiceAmount	=	$rssel_data->fields['InvoiceAmount'];
		
		$SalesRep		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CustNo}' ");
		
		$record	=	$_GET['TOTAL_CNT'];
		
		$ia_no	=	$global_func->TRANSEQ_IA($Filstar_conn);

		foreach ($_SESSION['ITEM'] as $key=>$val_item) 
		{
			$item			=	$key;
			$del_qty		=	$val_item['DEL_QTY'];
			$rvc_qty		=	$val_item['REL_QTY'];
			$gross_new		=	$val_item['NEW_GROSS'];
			$gross_old		=	$val_item['OLD_GROSS_'];
			$net_new		=	$val_item['NEW_NET_'];
			$net_old		=	$val_item['OLD_NET_'];
			$remarks_		=	$val_item['REMARKS_'];
			$hdn_remarks_	=	$val_item['HDN_REMARKS_'];
			
			$new_total_net	+=	$net_new;
			$new_total_gross+=	$gross_new;
			$new_qty		+=	$rvc_qty;
			
			if (!empty($item)) 
			{
				$insert_dtl	=	"INSERT INTO WMS_NEW.CONFIRMDELIVERY_DTL(`DOCNO`,`SOF`,`IATRANSNO`,`SKUNO`,`QTY`,`RECEIVEDQTY`,`REMARKS`)
								VALUES
								('{$InvoiceNo}','{$OrderNo}','{$ia_no}','{$item}','{$del_qty}',0,'{$REMARKS_}')";
				$rsinsert_dtl=	$Filstar_conn->Execute($insert_dtl);
				if ($rsinsert_dtl==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				
				
				$AI_QTY		=	$del_qty-0;
				
				
				$insert_IA	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_DTL(`IATRANSNO`,`SKUNO`,`IAQTY`,`REMARKS`) 
								VALUES
								('{$ia_no}','{$item}','{$AI_QTY}','{$REMARKS_}')";
				$rsinsert_IA=	$Filstar_conn->Execute($insert_IA);
				if ($rsinsert_IA==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
			}
		}
		
		$sel_qty		=	"SELECT sum(`ReleaseQty`)as TOTAL FROM orderdetail where OrderNo = '{$OrderNo}' and isDeleted = 'N' ";
		$rssel_qty		=	$Filstar_conn->Execute($sel_qty);
		if ($rssel_qty==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$TOTAL			=	$rssel_qty->fields['TOTAL'];
		
		$insert_hdr		=	"INSERT INTO WMS_NEW.CONFIRMDELIVERY_HDR(`SOF`,`DOCNO`,`CUSTNO`,`SRNO`,`DOCTYPE`,`NETAMOUNT`,`GROSSAMOUNT`,`TOTALQTY`,`IATRANSNO`,
							`RCVDNETAMOUNT`,`RCVDGROSSAMOUNT`,`RCVDTOTALQTY`,`CONFIRMDELDATE`,`ADDEDBY`,`ADDEDDATE`,`ADDEDTIME`,`VARIANCE`)
							VALUES
							('{$OrderNo}','{$InvoiceNo}','{$CustNo}','{$SalesRep}','{$VAL_OPT}','{$InvoiceAmount}','{$OrderAmount}','{$TOTAL}','{$ia_no}',
							0,0,0,'{$_GET['DATEDEL']}','{$_SESSION['username']}',SYSDATE(),SYSDATE(),'n) ";
		$rsinsert_hdr	=	$Filstar_conn->Execute($insert_hdr);
		if ($rsinsert_hdr==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		$insert_ia_hrd	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_HDR(`IATRANSNO`,`CUSTNO`,`REFN0`,`REFTYPE`,`STATUS`,`ADDEDDATE`,`ADDEDTIME`)
							VALUES('{$ia_no}','{$CustNo}','{$InvoiceNo}','{$VAL_OPT}','IN-PROCESS',SYSDATE(),SYSDATE())";
		$rsinsert_ia_hdr=	$Filstar_conn->Execute($insert_ia_hrd);
		if ($rsinsert_ia_hdr==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		unset($_SESSION['ITEM']);
		unset($_SESSION['DO_IA']);
		$Filstar_conn->CompleteTrans();
		echo "done";
		exit();
	}
	
	if ($action=='GET_IA') 
	{
		$VAL_OPT	=	$_GET['VAL_OPT'];
		$VAL__		=	strtoupper($_GET['VAL__']);
		
		$sel_ia		=	"SELECT IATRANSNO FROM WMS_NEW.CONFIRMDELIVERY_HDR WHERE DOCNO = '{$VAL__}' AND DOCTYPE = '{$VAL_OPT}' ";
		$rssel_ia	=	$Filstar_conn->Execute($sel_ia);
		if ($rssel_ia==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		if (!empty($rssel_ia->fields['IATRANSNO'])) 
		{
			echo $rssel_ia->fields['IATRANSNO'];
		}
		else 
		{
			echo "done";
		}

//		if (!empty($rssel_ia->fields['IATRANSNO'])) 
//		{
//			echo $rssel_ia->fields['IATRANSNO'];
//		}
//		else 
//		{
//			echo "none";
//		}
		exit();
	}
	
	if ($action=='DOCOMPUTE')
	{
		$RECEIVED	=	$_GET['RECEIVED'];
		$UNITCOST	=	$_GET['UNITCOST'];
		$UNITPRICE	=	$_GET['UNITPRICE'];
		
		$net		=	$RECEIVED * $UNITCOST;
		$gross		=	$RECEIVED * $UNITPRICE;
		
		echo number_format($net,2)."|".$net."|".number_format($gross,2)."|".$gross;
		exit();
	}
	
	function	REMARKS_LOOKUP($conn,$id)
	{
		$sel_	=	"SELECT  CODE,DESCRIPTION FROM WMS_NEW.DELIVERY_REMARKS WHERE 1 ";
		$rssel_	=	$conn->Execute($sel_);
		if ($rssel_==false) 
		{
			echo $conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		$retval	 =	"<select id='remarks_$id' name='remarks_$id' style='font-size:9px;' onclick=put_remarks('{$id}') onkeyup=put_remarks('{$id}')>";
		$retval	.=	"<option value='none'>NONE</option>";
		while (!$rssel_->EOF) 
		{
			$code	=	$rssel_->fields['CODE'];
			$desc	=	$rssel_->fields['DESCRIPTION'];
			
			$retval	.=	"<option value='{$code}'>$desc</option>";
			
			$rssel_->MoveNext();
		}
		$retval	.=	"</option>";
		return $retval;
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
				<td align="right" width="42%">
					<input type="radio" name="rdtype1" id="rdMANILA" value="MANILA" checked>
				</td>
				<td align="center" width="5%" style="font-size:20px">
					MANILA
				</td>
				<td align="center" width="5%">
					<input type="radio" name="rdtype1" id="rdPROVINCIAL" value="PROVINCE">
				</td>
				<td align="left" width="48%"" style="font-size:20px">
					PROVINCIAL
				</td>
			</tr>
			<tr>
				<td align="right" width="42%">
					<input type="radio" name="rdtype" id="rdSTF" value="STF" onclick="SHOW_TXTBOX(this.value);">
				</td>
				<td align="center" width="5%" style="font-size:20px">
					STF
				</td>
				<td align="center" width="5%">
					<input type="radio" name="rdtype" id="rdINVOICE" value="INVOICE" onclick="SHOW_TXTBOX(this.value);">
				</td>
				<td align="left" width="48%"" style="font-size:20px">
					INVOICE
				</td>
			</tr>
		</table>
		<div style="display:none;" id="divSTF">
		<table width="100%" border="0" class="Text_header"  style="border:0px">
			<tr>
				<td align="right" width="43%" style="font-size:20px">
					STF&nbsp;
				</td>
				<td align="left" width="57%" colspan="3" style="font-size:20px">
					:<input type="text" name="txtSTF" id="txtSTF" value="" onkeyup="isnumeric_(this.value,this.id);GET_DATA(event,this.id,this.value);" style="font-size:20px" size="10">
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
		<div id="divdata_detail"></div>
		<div id="divloader1" style="display:none;" align="center" title="LOADING">SAVING PLEASE WAIT<img src="../../images/loading/ajax-loader_fast.gif"></div>
		<div id="div_remarks" style="display:none;" align="center" title="REMARKS">
			<?php echo REMARKS_LOOKUP($Filstar_conn,"CANCEL"); ?>
		</div>
		<div id="divloader" style="display:none;" align="center" title="LOADING"><img src="../../images/loading/ajax-loader_fast.gif"></div>
		<div id="divmsg" style="display:none;" align="center" title="ALERT"></div>
		<div id="divtest" align="center" title="ALERT"></div>
	</form>
</body>
</html>
<script>


function	SHOW_TXTBOX(val_)
{
	if(val_ == 'STF')
	{
		$('#divdata').hide();
		$('#divdata_detail').hide();
		$('#divINVOICE').hide();
		$('#txtSTF').val('');
		$('#divSTF').show();
		$('#txtSTF').focus();
		$('#div_remarks').dialog('close');
	}
	else if(val_ == 'INVOICE')
	{
		$('#divdata').hide();
		$('#divdata_detail').hide();
		$('#divSTF').hide();
		$('#txtINVOICE').val('');
		$('#divINVOICE').show();
		$('#txtINVOICE').focus();
		$('#div_remarks').dialog('close');
	}
}

function	GET_DATA(evt,val_id,val_)
{
	var evtHandler	=	(evt.charCode) ? evt.charCode	: evt.keyCode;
	
	if(evtHandler == 13)
	{
		$('#divdata_detail').hide();
		if(val_ != '')
		{
			if(val_id=='txtSTF')
			{
				var opt	=	'STF';
			}
			else
			{
				var opt	=	'Invoice';
			}
			
			var is_manila	=	$('#rdMANILA').is(":checked");
			
			if(is_manila == true)
			{
				var	opt2	=	'MANILA';
			}
			else
			{
				var	opt2	=	'PROVINCE';
			}
			
			$.ajax({
					url			:	'confirm_delivery.php?action=GETDATA&OPT='+opt+'&VAL_='+val_+'&OPT2='+opt2,
					beforeSend	:	function()
								{
									$('#divloader').dialog('open');
								},
					success		:	function(response)
								{
									$('#divloader').dialog('close');
									$('#divdata').html(response);
									$('#divdata').show();
									$(".dates").datepicker({ 
										dateFormat: 'yy-mm-dd',
										changeMonth: true,
									    changeYear: true 
									});
									
								}
			});
		}
	}
}

function	Confirm_data(val_opt,val__)
{
	var isconfirm	=	confirm('Are you sure you want to confirm this transaction?');
	var	dateDEL		=	$('#dateDEL').val();
	var	total_cnt	=	$('#total_cnt').val();
	var	doprocess	=	"YES";
	if(isconfirm==true)
	{
		for(var x=1;x<=total_cnt;x++)
		{
			var	is_same	=	$('#hdn_remarks_'+x).val();
			if(is_same != 'SAME')
			{
				doprocess	=	'NO';
				$('#remard_color_'+x).css("background-color","red");
			}
		}
		if(doprocess == 'YES')
		{
			var data__	=	$('#form_detail').serialize();
			if(val_opt=='STF')
			{
				var is_N	=	$('#discrepancy_N').is(":checked");
				if(is_N==true)
				{
					var amt_discrepancy	=	'N';
				}
				else
				{
					var amt_discrepancy	=	'Y';
				}
			}
			else
			{
				var amt_discrepancy	=	'N';
			}
			
			$('#divloader1').dialog('open');
			for(var first_cnt=1;first_cnt<=total_cnt;first_cnt++)
			{
				var	item		=	$('#hdn_items_'+first_cnt).val();
				var	del_qty		=	$('#txtDelQty_'+first_cnt).val();
				var	rel_qty		=	$('#txtRelQty_'+first_cnt).val();
				var	new_gross_	=	$('#new_gross_'+first_cnt).val();
				var	old_gross_	=	$('#old_gross_'+first_cnt).val();
				var	new_net_	=	$('#new_net_'+first_cnt).val();
				var	old_net_	=	$('#old_net_'+first_cnt).val();
				var	remarks_	=	$('#remarks_'+first_cnt).val();
				var	hdn_remarks_=	$('#hdn_remarks_'+first_cnt).val();
				
				$.ajax({
						url		:	'confirm_delivery.php?action=SET_ITEMS&ITEM='+item+'&DEL_QTY='+del_qty+'&REL_QTY='+rel_qty+'&NEW_GROSS_='+new_gross_+'&OLD_GROSS_='+old_gross_+'&NEW_NET_='+new_net_+'&OLD_NET_='+old_net_+'&REMARKS_='+remarks_+'&HDN_REMARKS_='+hdn_remarks_,
						async	: 	false,
						success	:	function(response)
								{
//									$('#divtest').html(response);
//									$('#divtest').show();
								}
						
				});
			}
			$('#divloader1').dialog('close');
			$.ajax({
					type		:	'POST',
					url			:	'confirm_delivery.php?action=CONFIRM__&VAL_OPT='+val_opt+'&VAL__='+val__+'&TOTAL_CNT='+total_cnt+'&DATEDEL='+dateDEL+'&AMT_DISCREPANCY='+amt_discrepancy,
					beforeSend	:	function()
								{
									$('#divloader1').dialog('open');
								},
					success		:	function(response)
								{
									$('#divloader1').dialog('close');
//									$('#divtest').html(response);
//									$('#divtest').show();
									if(response=='done')
									{
										Get_IA(val_opt,val__);
									}
									else
									{
										alert(response);
									}
								}
			});
		}
		else
		{
			$('#divmsg').dialog('open');
			$('#divmsg').html('Please select remarks');
		}
	}
}

function	Cancel_data(val_opt,val__)
{
	$('#div_remarks').dialog('open');
}

function	Display_item(val_opt,val__)
{
	$('#div_detail').show();
//	$.ajax({
//			url			:	'confirm_delivery.php?action=GET_ITEM&OPT='+val_opt+'&VAL_='+val__,
//			beforeSend	:	function()
//						{
//							$('#divloader').dialog('open');
//						},
//			success		:	function(response)
//						{
//							$('#divloader').dialog('close');
//							$('#divdata_detail').html(response);
//							$('#divdata_detail').show();
//						}
//			
//	});
}


function	Get_IA(val_opt,val__)
{
	$.ajax({
			url			:	'confirm_delivery.php?action=GET_IA&VAL_OPT='+val_opt+'&VAL__='+val__,
			beforeSend	:	function()
						{
							$('#divloader1').dialog('open');
						},
			success		:	function(response)
						{
							$('#divloader1').dialog('close');
							if(response == 'done')
							{
								alert('Document was successfully confirmed');
							}
							else
							{
								alert('Document was successfully confirmed your I.A No.'+response);	
							}
							
							$('#divdata').hide();
							if(val_opt=='Invoice')
							{
								val_opt	=	'INVOICE';
							}
							SHOW_TXTBOX(val_opt);
						}
	});
}

function	Get_IA_cancel(val_opt,val__)
{
	$.ajax({
			url			:	'confirm_delivery.php?action=GET_IA&VAL_OPT='+val_opt+'&VAL__='+val__,
			beforeSend	:	function()
						{
							$('#divloader1').dialog('open');
						},
			success		:	function(response)
						{
							$('#divloader1').dialog('close');
							alert('Document was successfully confirmed your I.A No.'+response);	
							$('#divdata').hide();
							if(val_opt=='Invoice')
							{
								val_opt	=	'INVOICE';
							}
							SHOW_TXTBOX(val_opt);
						}
	});
}


function	put_remarks(val_item)
{
	var	remarks	=	$('#remarks_'+val_item).val();
	if(remarks != 'none')
	{
		$('#hdn_remarks_'+val_item).val('SAME');
		$('#remard_color_'+val_item).css("background-color","white");
	}
	else
	{
		$('#hdn_remarks_'+val_item).val('CHANGE');
		$('#remard_color_'+val_item).css("background-color","red");
	}
}


$("#divloader").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 100, width: 170, modal:true, autoOpen: false,	draggable: false
});

$("#divloader1").dialog({
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


$("#div_remarks").dialog({
dialogClass: "no-close",
bgiframe:true, resizable:false, height: 100, width: 200, modal:true, autoOpen: false,	draggable: false,
buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		},
		'SUBMIT': function()
		{
			var	dateDEL		=	$('#dateDEL').val();
			var	remarks_	=	$('#remarks_CANCEL').val();
			var	total_cnt	=	$('#total_cnt').val();
			
			var val_id		=	$('#rdSTF').is(":checked");
			
			if(val_id==true)
			{
				var val_opt	=	'STF';
				var	val__	=	$('#txtSTF').val();
			}
			else
			{
				var val_opt	=	'Invoice';
				var	val__	=	$('#txtINVOICE').val();
			}
			
			var is_manila	=	$('#rdMANILA').is(":checked");
			
			if(is_manila == true)
			{
				var	opt2	=	'MANILA';
			}
			else
			{
				var	opt2	=	'PROVINCE';
			}
			
			$('#divloader1').dialog('open');
			for(var first_cnt=1;first_cnt<=total_cnt;first_cnt++)
			{
				var	item		=	$('#hdn_items_'+first_cnt).val();
				var	del_qty		=	$('#txtDelQty_'+first_cnt).val();
				var	rel_qty		=	$('#txtRelQty_'+first_cnt).val();
				var	new_gross_	=	$('#new_gross_'+first_cnt).val();
				var	old_gross_	=	$('#old_gross_'+first_cnt).val();
				var	new_net_	=	$('#new_net_'+first_cnt).val();
				var	old_net_	=	$('#old_net_'+first_cnt).val();
				var	remarks_	=	$('#remarks_'+first_cnt).val();
				var	hdn_remarks_=	$('#hdn_remarks_'+first_cnt).val();
				
				$.ajax({
						url		:	'confirm_delivery.php?action=SET_ITEMS&ITEM='+item+'&DEL_QTY='+del_qty+'&REL_QTY='+rel_qty+'&NEW_GROSS_='+new_gross_+'&OLD_GROSS_='+old_gross_+'&NEW_NET_='+new_net_+'&OLD_NET_='+old_net_+'&REMARKS_='+remarks_+'&HDN_REMARKS_='+hdn_remarks_,
						async	: 	false,
						success	:	function(response)
								{
								}
						
				});
			}
			$('#divloader1').dialog('close');
			$.ajax({
					type		:	'POST',
					url			:	'confirm_delivery.php?action=CANCEL__&VAL_OPT='+val_opt+'&VAL__='+val__+'&REMARKS_='+remarks_+'&DATEDEL='+dateDEL,
					beforeSend	:	function()
								{
									$('#divloader1').dialog('open');
								},
					success		:	function(response)
								{
									$('#divloader1').dialog('close');
									if(response=='done')
									{
										Get_IA_cancel(val_opt,val__);
									}
								}
			});
		}
	}
});

function recompute(val_item)
{
	var Deliver		=	parseFloat($('#txtDelQty_'+val_item).val());
	var	Received	=	parseFloat($('#txtRelQty_'+val_item).val());
	var	Unitcost	=	$('#txtUnitcost_'+val_item).val();
	var	Unitprice	=	$('#txtUnitprice_'+val_item).val();
	var	total_cnt	=	$('#total_cnt').val();
	var	TOTAL_QTY	=	0;
	var TOTAL_NET	=	0;
	var	TOTAL_GROSS	=	0;
	
	if(Deliver >= Received)
	{
		$.ajax({
				url		:	'confirm_delivery.php?action=DOCOMPUTE&RECEIVED='+Received+'&UNITCOST='+Unitcost+'&UNITPRICE='+Unitprice,
				success	:	function(response)
						{
							$('#split_net').val(response);
							var	vx1	=	$('#split_net').val();
							var x1 	= 	vx1.split('|');
							var	net_1	=	x1[0];
							var	net_2	=	x1[1];
							var	gross_1	=	x1[2];
							var	gross_2	=	x1[3];
							$('#net_amount_'+val_item).html(net_1);
							$('#new_net_'+val_item).val(net_2);
							
							$('#gross_amount_'+val_item).html(gross_1);
							$('#new_gross_'+val_item).val(gross_2);
							
							for(var x=1;x<=total_cnt;x++)
							{
								var	rel_qty		=	$('#txtRelQty_'+x).val();
								var	parse_qty	=	parseFloat(rel_qty);
								TOTAL_QTY		=	TOTAL_QTY	+	parse_qty;
								
								var	net			=	$('#new_net_'+x).val();
								var	parse_net	=	parseFloat(net);
								TOTAL_NET		=	TOTAL_NET	+	parse_net;
								
								var	gross		=	($('#new_gross_'+x).val());
								var	parse_gross	=	parseFloat(gross);
								TOTAL_GROSS		=	TOTAL_GROSS	+	parse_gross;
							}
							
							var	FINAL_QTY		=	TOTAL_QTY;
							var	FINAL_NET		=	TOTAL_NET.toFixed(2);
							var	FINAL_GROSS		=	TOTAL_GROSS.toFixed(2);
							
							$('#lbl_rcvd_qrt').html(addCommas(FINAL_QTY));
							$('#lbl_net').html(addCommas(FINAL_NET));
							$('#lbl_gross').html(addCommas(FINAL_GROSS));
						}
		});
		
//		$.ajax({
//				url		:	'confirm_delivery.php?action=DOCOMPUTE2&RECEIVED='+Received+'&UNITPRICE='+Unitprice,
//				success	:	function(response)
//						{
//							$('#split_gross').val(response);
//							var	vx	=	$('#split_gross').val();
//							var x 	= 	vx.split('|');
//							var	gross_1	=	x[0];
//							var	gross_2	=	x[1];
//							$('#gross_amount_'+val_item).html(gross_1);
//							$('#new_gross_'+val_item).val(gross_2);
//						}
//		});
		
		if(Received == Deliver)
		{
			$('#hdn_remarks_'+val_item).val('SAME');
			$('#item_desc_'+val_item).css("color","black");
		}
		else
		{
			$('#hdn_remarks_'+val_item).val('CHANGE');
			$('#item_desc_'+val_item).css("color","red");
		}
		
	}
	else
	{
		alert('Received Quantity is more than Delivered Qunatity!');
		var	old_gross	=	$('#old_gross_'+val_item).val();
		var	old_net		=	$('#old_net_'+val_item).val();
		
		$('#net_amount_'+val_item).html(old_net);
		$('#gross_amount_'+val_item).html(old_gross);
		$('#txtRelQty_'+val_item).val(Deliver);
		
		for(var x=1;x<=total_cnt;x++)
		{
			var	rel_qty		=	$('#txtRelQty_'+x).val();
			var	parse_qty	=	parseFloat(rel_qty);
			TOTAL_QTY		=	TOTAL_QTY	+	parse_qty;
			
			var	net			=	$('#new_net_'+x).val();
			var	parse_net	=	parseFloat(net);
			TOTAL_NET		=	TOTAL_NET	+	parse_net;
			
			var	gross		=	($('#new_gross_'+x).val());
			var	parse_gross	=	parseFloat(gross);
			TOTAL_GROSS		=	TOTAL_GROSS	+	parse_gross;
		}
		
		var	FINAL_QTY		=	TOTAL_QTY;
		var	FINAL_NET		=	TOTAL_NET.toFixed(2);
		var	FINAL_GROSS		=	TOTAL_GROSS.toFixed(2);
		
		$('#lbl_rcvd_qrt').html(addCommas(FINAL_QTY));
		$('#lbl_net').html(addCommas(FINAL_NET));
		$('#lbl_gross').html(addCommas(FINAL_GROSS));
	}
	
	
}

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