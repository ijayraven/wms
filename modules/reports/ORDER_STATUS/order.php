<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}
	$action	=	$_GET['action'];
	
	
	if ($action=='CHECK_ORDERS') 
	{
		$sof		=	$_POST['txtsof'];
		$custcode	=	$_POST['customercode'];
		$status		=	$_POST['sel_status'];
		$doctype	=	$_POST['sel_doctype'];
		$from		=	$_POST['dfrom'];
		$to			=	$_POST['dto'];
		$aData		=	array();
		$aData_cust	=	array();
		$opt		=	$_GET['OPT__'];
		
		if (empty($custcode))
		{
			if ($opt == 'NBS')
			{
				if ($doctype=='STF') 
				{
					$sel_cust	=	"SELECT CustNo FROM custmast where SUBSTRING(  `CustNo` , -1, 1 ) =  'C' and CustStatus = 'A' and CustomerBranchCode != '' ";
				}
				else 
				{
					$sel_cust	=	"SELECT CustNo FROM custmast where SUBSTRING(  `CustNo` , -1, 1 ) =  'O' and CustStatus = 'A' and CustomerBranchCode != '' ";
				}
				$rssel_cust	=	$Filstar_conn->Execute($sel_cust);
				if ($rssel_cust==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;
				}
				while (!$rssel_cust->EOF) 
				{
					$CustNo	=	$rssel_cust->fields['CustNo'];
					
					$aData_cust[]	=	$CustNo;
					$rssel_cust->MoveNext();
				}
			}
			elseif ($opt == 'TRADE')
			{
				
				$sel_nrev	=	"SELECT CUSTNO FROM WMS_LOOKUP.NONREVENUE_CUST where 1 ";
				$rssel_nrev	=	$Filstar_conn->Execute($sel_nrev);
				if ($rssel_nrev==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;
				}
				while (!$rssel_nrev->EOF) 
				{
					$CustNo_nrev		=	$rssel_nrev->fields['CUSTNO'];
					$aData_cust_nrev[]	=	$CustNo_nrev;
					
					$rssel_nrev->MoveNext();
				}
				
				$nrev_list	=	implode("','",$aData_cust_nrev);
				
				if ($doctype=='STF') 
				{
					$sel_cust	=	"SELECT CustNo FROM custmast where SUBSTRING(  `CustNo` , -1, 1 ) =  'C' and CustStatus = 'A' and CustomerBranchCode = '' 
									and CustNo NOT IN('{$nrev_list}')";
				}
				else 
				{
					$sel_cust	=	"SELECT CustNo FROM custmast where SUBSTRING(  `CustNo` , -1, 1 ) =  'O' and CustStatus = 'A' and CustomerBranchCode = '' 
									and CustNo NOT IN('{$nrev_list}')";
				}
				$rssel_cust	=	$Filstar_conn->Execute($sel_cust);
				if ($rssel_cust==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;
				}
				while (!$rssel_cust->EOF) 
				{
					$CustNo	=	$rssel_cust->fields['CustNo'];
					
					$aData_cust[]	=	$CustNo;
					$rssel_cust->MoveNext();
				}
			}
			elseif ($opt == 'NREVENUE')
			{
				$sel_cust	=	"SELECT CUSTNO FROM WMS_LOOKUP.NONREVENUE_CUST where 1 ";
				$rssel_cust	=	$Filstar_conn->Execute($sel_cust);
				if ($rssel_cust==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;
				}
				while (!$rssel_cust->EOF) 
				{
					$CustNo	=	$rssel_cust->fields['CUSTNO'];
					
					$aData_cust[]	=	$CustNo;
					$rssel_cust->MoveNext();
				}
			}
			$custlist	=	implode("','",$aData_cust);
		}
		else 
		{
			$custlist	=	$custcode;
		}
		
		
		
		if ($status == 'S001') 
		{
			$sel_data	=	"SELECT OrderNo from orderheader where ";
			$sel_data  .=	"OrderDate between '{$from}' and '{$to}' ";
			if (!empty($sof)) 
			{
			$sel_data  .=	"and OrderNo = '{$sof}' ";	
			}
			$sel_data  .=	"and CustNo in ('{$custlist}') ";
			$sel_data  .=	"and OrderStatus = 'For Picking' ";
			$sel_data  .=	"and InvoiceNo = '' ";
			$sel_data  .=	"Order by OrderDate,PickListNo ASC ";
			
			$rssel_data	=	$Filstar_conn->Execute($sel_data);
			if ($rssel_data==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			while (!$rssel_data->EOF) 
			{
				$OrderNo	=	$rssel_data->fields['OrderNo'];
				$aData[]	=	$OrderNo;
				$rssel_data->MoveNext();
			}
			
		}
		else if ($status == 'S002') 
		{
			$sel_data	=	"SELECT OrderNo from orderheader where ";
			$sel_data  .=	"OrderDate between '{$from}' and '{$to}' ";
			if (!empty($sof)) 
			{
			$sel_data  .=	"and OrderNo = '{$sof}' ";	
			}
			$sel_data  .=	"and OrderCategory = '{$doctype}' ";
			$sel_data  .=	"and CustNo in ('{$custlist}') ";
			$sel_data  .=	"and OrderStatus in ('Confirmed','Invoiced') ";
			$sel_data  .=	"and InvoiceNo != '' ";
			$sel_data  .=	"Order by OrderDate,PickListNo ASC ";
			$rssel_data	=	$Filstar_conn->Execute($sel_data);
			if ($rssel_data==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			while (!$rssel_data->EOF)
			{
				$OrderNo	=	$rssel_data->fields['OrderNo'];
				
				$is_manila	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_METROMANILA_DTL","TRACKINGNO","SOFNO = '{$OrderNo}' and STATUS != 'CANCELLED' ");
				if (empty($is_manila)) 
				{
					$is_province	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_PROVINCE_DTL","TRACKINGNO","SOFNO = '{$OrderNo}' and STATUS != 'CANCELLED' ");
					if (empty($is_province)) 
					{
						$aData[]	=	$OrderNo;
					}
				}
				$rssel_data->MoveNext();
			}
		}
		elseif ($status == 'S003')
		{
			$sel_data	=	"SELECT OrderNo from orderheader where ";
			$sel_data  .=	"OrderDate between '{$from}' and '{$to}' ";
			if (!empty($sof)) 
			{
			$sel_data  .=	"and OrderNo = '{$sof}' ";	
			}
			$sel_data  .=	"and OrderCategory = '{$doctype}' ";
			$sel_data  .=	"and CustNo in ('{$custlist}') ";
			$sel_data  .=	"and OrderStatus in ('Confirmed','Invoiced') ";
			$sel_data  .=	"and InvoiceNo != '' ";
			$sel_data  .=	"Order by OrderDate,PickListNo ASC ";
			$rssel_data	=	$Filstar_conn->Execute($sel_data);
			if ($rssel_data==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			while (!$rssel_data->EOF)
			{
				$OrderNo	=	$rssel_data->fields['OrderNo'];
				
				$is_manila	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_METROMANILA_DTL","TRACKINGNO","SOFNO = '{$OrderNo}' and STATUS != 'CANCELLED' ");
				if (empty($is_manila)) 
				{
					$is_province	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_PROVINCE_DTL","TRACKINGNO","SOFNO = '{$OrderNo}' and STATUS != 'CANCELLED' ");
					if (!empty($is_province)) 
					{
						$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$OrderNo}' ");
						if (empty($is_confirm)) 
						{
							$aData[]	=	$OrderNo;
						}
					}
				}
				else 
				{
					$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$OrderNo}' ");
					if (empty($is_confirm)) 
					{
						$aData[]	=	$OrderNo;
					}
				}
				$rssel_data->MoveNext();
			}
		}
		elseif ($status=='S004')
		{
			$sel_data	=	"SELECT OrderNo from orderheader where ";
			$sel_data  .=	"OrderDate between '{$from}' and '{$to}' ";
			if (!empty($sof)) 
			{
			$sel_data  .=	"and OrderNo = '{$sof}' ";	
			}
			$sel_data  .=	"and OrderCategory = '{$doctype}' ";
			$sel_data  .=	"and CustNo in ('{$custlist}') ";
			$sel_data  .=	"and OrderStatus in ('Confirmed','Invoiced') ";
			$sel_data  .=	"and InvoiceNo != '' ";
			$sel_data  .=	"Order by OrderDate,PickListNo ASC ";
			$rssel_data	=	$Filstar_conn->Execute($sel_data);
			if ($rssel_data==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			while (!$rssel_data->EOF)
			{
				$OrderNo	=	$rssel_data->fields['OrderNo'];
				
				$is_manila	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_METROMANILA_DTL","TRACKINGNO","SOFNO = '{$OrderNo}' and STATUS != 'CANCELLED' ");
				if (empty($is_manila)) 
				{
					$is_province	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_PROVINCE_DTL","TRACKINGNO","SOFNO = '{$OrderNo}' and STATUS != 'CANCELLED' ");
					if (!empty($is_province)) 
					{
						$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$OrderNo}' ");
						if (!empty($is_confirm)) 
						{
							$aData[]	=	$OrderNo;
						}
					}
				}
				else 
				{
					$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$OrderNo}' ");
					if (!empty($is_confirm)) 
					{
						$aData[]	=	$OrderNo;
					}
				}
				$rssel_data->MoveNext();
			}
		}
		
		$record	=	count($aData);
		
		$show	.=	"<table width='100%' border='0'>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='3' class='Text_header_value' style='font-size:13px;color:#258e25'>";
		$show	.=			"S001 - SOF FOR PICKING"."<br>";
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='3' class='Text_header_value' style='font-size:13px;color:#258e25'>";
		$show	.=			"S002 - PLCONF NOT YET DISPATCH"."<br>";
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='3' class='Text_header_value' style='font-size:13px;color:#258e25'>";
		$show	.=			"S003 - DISPATCH NOT YET CONF DELIVERY"."<br>";
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='3' class='Text_header_value' style='font-size:13px;color:#258e25'>";
		$show	.=			"S004 - CONF DELIVERY"."<br>";
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr class='Header_style' style='font-size:11px;'>";
		$show	.=		"<td width='5%' nowrap>";
		$show	.=			"LINENO";
		$show	.=		"</td>";
		$show	.=				"<td width='10%' align='center' nowrap>";
		$show	.=					"SOF NO";
		$show	.=				"</td>";
		$show	.=						"<td width='40%' align='center' nowrap>";
		$show	.=							"CUSTOMER";
		$show	.=						"</td>";
		$show	.=								"<td width='10%' align='center' nowrap>";
		$show	.=									"SOF DATE";
		$show	.=								"</td>";
		$show	.=										"<td width='10%' align='center' nowrap>";
		$show	.=											"PL NO.";
		$show	.=										"</td>";
		$show	.=												"<td width='10%' align='center' nowrap>";
		$show	.=													"GROSS AMT";
		$show	.=												"</td>";
		$show	.=														"<td width='10%' align='center' nowrap>";
		$show	.=															"NET AMT";
		$show	.=														"</td>";
		$show	.=																"<td width='5%' align='center' nowrap>";
		$show	.=																	"STATUS";
		$show	.=																"</td>";
		$show	.=	"</tr>";
		if ($record > 0)
		{
			$cnt	=	1;
			foreach ($aData as $key)
			{
				
				$sel_data_h		=	"SELECT CustNo,OrderDate,OrderStatus,PickListNo,OrderAmount,InvoiceAmount from orderheader where ";
				$sel_data_h	   .=	"OrderNo = '{$key}' ";
				$rsssel_data_h	=	$Filstar_conn->Execute($sel_data_h);
				if ($rsssel_data_h==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				$CustNo			=	$rsssel_data_h->fields['CustNo'];
				$OrderDate		=	$rsssel_data_h->fields['OrderDate'];
				$OrderStatus	=	$rsssel_data_h->fields['OrderStatus'];
				$PickListNo		=	$rsssel_data_h->fields['PickListNo'];
				$gross			=	$rsssel_data_h->fields['OrderAmount'];
				$net			=	$rsssel_data_h->fields['InvoiceAmount'];
				
				$sel_data_d		=	"SELECT sum(OrderQty)as O_QTY,sum(ReleaseQty)as R_QTY,sum(GrossAmount)as AMT,sum(NetAmount)as N_AMT FROM orderdetail ";
				$sel_data_d		.=	"where OrderNo = '{$key}' and isDeleted = 'N' ";
				
				$rssel_data_d	=	$Filstar_conn->Execute($sel_data_d);
				if ($rssel_data_d==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				$O_QTY			=	$rssel_data_d->fields['O_QTY'];
				$R_QTY			=	$rssel_data_d->fields['R_QTY'];
				$gross			=	$rssel_data_d->fields['AMT'];
				$net			=	$rssel_data_d->fields['N_AMT'];
				
				if ($O_QTY == $R_QTY) 
				{
					$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' style='font-size:13px;' onclick=display_item('{$key}')>";
				}
				else 
				{
					$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='yellow';\" bgcolor=\"yellow\" class='Text_header_hover' style='font-size:13px;' onclick=display_item('{$key}')>";
				}
				
				$CustName		=	substr($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CustNo}' "),0,40);
				
				$show	.=	"<td align='center'  >";
				$show	.=	$cnt;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center'  >";
				$show	.=	$key;
				$show	.=	"</td>";
				
				$show	.=	"<td align='left'  >";
				$show	.=	$CustNo.'-'.$CustName;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center'  >";
				$show	.=	$OrderDate;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center'  >";
				$show	.=	$PickListNo;
				$show	.=	"</td>";
				
				$show	.=	"<td align='right'  >";
				$show	.=	number_format($gross,2);
				$show	.=	"</td>";	
				
				$show	.=	"<td align='right' >";
				$show	.=	number_format($net,2);
				$show	.=	"</td>";
				
				$show	.=	"<td align='center'  >";
				$show	.=	$status;
				$show	.=	"</td>";
				$show	.=	"</tr>";
				
				$total_gross	+=	$gross;
				$total_net		+=	$net;
				$cnt++;
			}
			$show	.=	"<tr>";
			$show	.=	"<td align='center' colspan='1'>";
			
			$show	.=	"</td>";
			
			$show	.=	"<td align='center' colspan='4' class='Text_header_value'>";
			$show	.=	"TOTAL AMOUNT";
			$show	.=	"</td>";
			
			$show	.=	"<td align='right' colspan='1' class='Text_header_value'>";
			$show	.=	number_format($total_gross,2);
			$show	.=	"</td>";
			
			$show	.=	"<td align='right' colspan='1' class='Text_header_value'>";
			$show	.=	number_format($total_net,2);
			$show	.=	"</td>";
		}
		else 
		{
			$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
			$show	.=		"<td width='100%' colspan='7' style='font-size:30px;color:red'>";
			$show	.=			"<blink>NO RECORD COUNT</blink>";
			$show	.=		"</td>";
			$show	.=	"</tr>";
		}
		$show	.=	"</table>";
		echo $show;
		exit();
	}
	
	
	if ($action=='DISPLAY_ITEM') 
	{
		$SOFNO	=	$_GET['SOFNO'];
		$status	=	$_GET['STATUS'];
		
		$sel_data_h		=	"SELECT OrderNo,CustNo,PickListNo,PickListDate,InvoiceNo,InvoiceDate,OrderCategory from orderheader where ";
		$sel_data_h	   .=	"OrderNo = '{$SOFNO}' ";
		$rsssel_data_h	=	$Filstar_conn->Execute($sel_data_h);
		if ($rsssel_data_h==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		$OrderNo		=	$rsssel_data_h->fields['OrderNo'];
		$CustNo			=	$rsssel_data_h->fields['CustNo'];
		
		$CustName		=	substr($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CustNo}' "),0,40);
		if ($status == 'S003')
		{
			//$TRACKINGNO		=	"NO AVAILABLE";
			$TRACKINGNO		=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_METROMANILA_DTL","TRACKINGNO","SOFNO = '{$OrderNo}' AND 	STATUS = 'OPEN' ");
			if (empty($TRACKINGNO))
			{
				$TRACKINGNO		=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_PROVINCE_DTL","TRACKINGNO","SOFNO = '{$OrderNo}' AND 	STATUS = 'OPEN' ");
			}
		}
		
		$PickListNo		=	$rsssel_data_h->fields['PickListNo'];
		$PickListDate	=	$rsssel_data_h->fields['PickListDate'];
		$InvoiceNo		=	$rsssel_data_h->fields['InvoiceNo'];
		$InvoiceDate	=	$rsssel_data_h->fields['InvoiceDate'];
		$OrderCategory	=	$rsssel_data_h->fields['OrderCategory'];
		
		$sel_data_d	   .=	"SELECT Item,OrderQty,ReleaseQty,UnitCost,UnitPrice,GrossAmount,NetAmount from orderdetail where ";
		$sel_data_d	   .=	"OrderNo = '{$SOFNO}' and isDeleted = 'N' ";
		$rssel_data_d	=	$Filstar_conn->Execute($sel_data_d);
		if ($rssel_data_d==false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$show	.=	"<table width='100%' border='0'>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='2' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			"TRACKING NO.";
		$show	.=		"</td>";
		$show	.=		"<td colspan='4' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			": ".$TRACKINGNO;
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='2' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			"ORDER NO.";
		$show	.=		"</td>";
		$show	.=		"<td colspan='4' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			": ".$OrderNo;
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='2' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			"CUSTOMER";
		$show	.=		"</td>";
		$show	.=		"<td colspan='4' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			": ".$CustNo.'-'.$CustName;
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='2' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			"PL DATE.";
		$show	.=		"</td>";
		$show	.=		"<td colspan='4' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			": ".$PickListDate;
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='2' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			"PL NO.";
		$show	.=		"</td>";
		$show	.=		"<td colspan='4' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			": ".$PickListNo;
		$show	.=		"</td>";
		$show	.=	"</tr>";
		if (!empty($InvoiceNo)) 
		{
		$show	.=	"<tr>";
		$show	.=		"<td colspan='2' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			"ORDER CATEGORY";
		$show	.=		"</td>";
		$show	.=		"<td colspan='4' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			": ".$OrderCategory;
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr>";
		$show	.=		"<td colspan='2' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			"$OrderCategory Date";
		$show	.=		"</td>";
		$show	.=		"<td colspan='4' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			": ".$InvoiceDate;
		$show	.=		"</td>";
		$show	.=	"</tr>";	
		$show	.=	"<tr>";
		$show	.=		"<td colspan='2' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			"$OrderCategory NO.";
		$show	.=		"</td>";
		$show	.=		"<td colspan='4' class='Text_header_value' style='font-size:13px;' align='left'>";
		$show	.=			": ".$InvoiceNo;
		$show	.=		"</td>";
		$show	.=	"</tr>";
		
		}
		
		$show	.=	"<tr class='Header_style' style='font-size:11px;'>";
		$show	.=		"<td width='5%' nowrap>";
		$show	.=			"LINENO";
		$show	.=		"</td>";
		$show	.=				"<td width='10%' align='center' nowrap>";
		$show	.=					"ITEM NO.";
		$show	.=				"</td>";
		$show	.=						"<td width='35%' align='center' nowrap>";
		$show	.=							"DESCRIPTION";
		$show	.=						"</td>";
		$show	.=								"<td width='5%' align='center' nowrap>";
		$show	.=									"ORDER QTY";
		$show	.=								"</td>";
		$show	.=								"<td width='5%' align='center' nowrap>";
		$show	.=									"RELEASE QTY";
		$show	.=								"</td>";
		$show	.=										"<td width='10%' align='center' nowrap>";
		$show	.=											"UNITCOST";
		$show	.=										"</td>";
		$show	.=												"<td width='10%' align='center' nowrap>";
		$show	.=													"UNITPRICE";
		$show	.=												"</td>";
		$show	.=														"<td width='10%' align='center' nowrap>";
		$show	.=															"GROSS AMT";
		$show	.=														"</td>";
		$show	.=																"<td width='10%' align='center' nowrap>";
		$show	.=																	"NET AMT";
		$show	.=																"</td>";
		$show	.=	"</tr>";
		
		$cnt	=	1;
		
		while (!$rssel_data_d->EOF)
		{
			$Item		=	$rssel_data_d->fields['Item'];
			$OrderQty	=	$rssel_data_d->fields['OrderQty'];
			$ReleaseQty	=	$rssel_data_d->fields['ReleaseQty'];
			$UnitCost	=	$rssel_data_d->fields['UnitCost'];
			$UnitPrice	=	$rssel_data_d->fields['UnitPrice'];
			$GrossAmount=	$rssel_data_d->fields['GrossAmount'];
			$NetAmount	=	$rssel_data_d->fields['NetAmount'];
			
			$ItemDesc	=	substr($global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo='{$Item}'"),0,30);
			
			if ($OrderQty == $ReleaseQty)
			{
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' style='font-size:13px;' >";	
			}
			else 
			{
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='yellow';\" bgcolor=\"yellow\" class='Text_header_hover' style='font-size:13px;' >";	
			}
			
			$show	.=	"<td align='center'  >";
			$show	.=	$cnt;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'  >";
			$show	.=	$Item;
			$show	.=	"</td>";
			
			$show	.=	"<td align='left'  >";
			$show	.=	$ItemDesc;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'  >";
			$show	.=	$OrderQty;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'  >";
			$show	.=	$ReleaseQty;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'  >";
			$show	.=	$UnitCost;
			$show	.=	"</td>";
			
			$show	.=	"<td align='right'  >";
			$show	.=	$UnitPrice;
			$show	.=	"</td>";
			
			$show	.=	"<td align='right'  >";
			$show	.=	$GrossAmount;
			$show	.=	"</td>";
			
			$show	.=	"<td align='right'  >";
			$show	.=	$NetAmount;
			$show	.=	"</td>";
			$show	.=	"</tr>";
				
			$total_gross	+=	$GrossAmount;
			$total_net		+=	$NetAmount;
			$total_qty_1	+=	$OrderQty;
			$total_qty_2	+=	$ReleaseQty;
			$cnt++;
			
			$rssel_data_d->MoveNext();
		}
		
		$show	.=	"<tr>";
		$show	.=	"<td align='center' colspan='1'>";
		$show	.=	"</td>";
		
		$show	.=	"<td align='center' colspan='2' class='Text_header_value'>";
		$show	.=	"TOTAL AMOUNT";
		$show	.=	"</td>";
		
		$show	.=	"<td align='center' colspan='1' class='Text_header_value' style='font-size:13px'>";
		$show	.=	$total_qty_1;
		$show	.=	"</td>";
		
		$show	.=	"<td align='center' colspan='1' class='Text_header_value' style='font-size:13px'>";
		$show	.=	$total_qty_2;
		$show	.=	"</td>";
		
		$show	.=	"<td align='center' colspan='2'>";
		$show	.=	"</td>";
		
		$show	.=	"<td align='right' colspan='1' class='Text_header_value' style='font-size:13px'>";
		$show	.=	number_format($total_gross,2);
		$show	.=	"</td>";
		
		$show	.=	"<td align='right' colspan='1' class='Text_header_value' style='font-size:13px'>";
		$show	.=	number_format($total_net,2);
		$show	.=	"</td>";
		
		$show	.=	"</tablet>";
		echo $show;
		exit();
	}
	
	
	if ($action=='SEARCHCUST')
	{
		$custcode	=	$_GET['CUSTCODE'];
		$custname	=	$_GET['CUSTNAME'];
		$DOCTYPE	=	$_GET['DOCTYPE'];
		$OPT__		=	$_GET['opt__'];
		
		if ($OPT__ == 'NREVENUE') 
		{
			$sel	 =	"SELECT CUSTNO,CUSTDESC from WMS_LOOKUP.NONREVENUE_CUST where 1 ";
			if (!empty($custcode)) 
			{
			$sel	.=	"AND CUSTNO like '%{$custcode}%' ";
			}
			if(!empty($custname))
			{
			$sel	.=	"AND CUSTDESC like '%{$custname}%' ";
			}
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
					$custno		=	$rssel->fields['CUSTNO'];
					$custname	=	$rssel->fields['CUSTDESC'];
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
		}
		else 
		{
			$sel	 =	"SELECT CustNo,CustName from custmast where 1 ";
			if (!empty($custcode)) 
			{
			$sel	.=	"AND CustNo like '%{$custcode}%' ";
			}
			if(!empty($custname))
			{
			$sel	.=	"AND CustName like '%{$custname}%' ";
			}
			if ($_GET['OPT__'] == 'NBS') 
			{
			$sel	.=	"AND CustomerBranchCode != '' ";	
			}
			if ($DOCTYPE == 'STF') 
			{
			$sel	.=	"AND SUBSTRING(CustNo,-1,1) = 'C' ";		
			}
			else 
			{
			$sel	.=	"AND SUBSTRING(CustNo,-1,1) = 'O' ";
			}
			$sel	.=	"AND CustStatus = 'A' ";
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
<script>
function DISPLAY()
{
	var	status	=	$('#sel_status').val();
	var	doctype	=	$('#sel_doctype').val();
	var	dfrom	=	$('#dfrom').val();
	var	dto		=	$('#dto').val();
	var	is_nbs		=	$('#rdNBS').is(":checked");
	var	is_trade	=	$('#rdTRADE').is(":checked");
	var	is_nrevenue	=	$('#rdNON-REVENUE').is(":checked");
	var dataform	=	$('#dataform').serialize();
	
	if(is_nbs==true)
	{
		var	opt__	=	"NBS";
	}
	else if(is_trade==true)
	{
		var	opt__	=	"TRADE";
	}
	else if(is_nrevenue==true)
	{
		var	opt__	=	"NREVENUE";
	}
	
	if(status=='NONE')
	{
		alert('Please select status');
		return;
	}
	if(doctype=='NONE')
	{
		alert('Please select DOCUMENT TPYE');
		return;
	}
	if(dfrom == '' || dto == '')
	{
		alert('Please insert date range');
		return;
	}
	if(dfrom > dto)
	{
		alert('Invalid insert date range');
		return;
	}
	
	$.ajax({
			type		:	'POST',
			data		:	dataform,
			url			:	'order.php?action=CHECK_ORDERS&OPT__='+opt__,
			beforeSend	:	function()
						{
							$('#divloader1').dialog('open');
						},
			success		:	function(response)
						{
							$('#divloader1').dialog('close');
							$('#divdata_detail').html(response);
							$('#divdata_detail').dialog('open');
						}
	});
	
	
}


function	display_item(sofno)
{
	var	status	=	$('#sel_status').val();
	
	$.ajax({
			url			:	'order.php?action=DISPLAY_ITEM&SOFNO='+sofno+'&STATUS='+status,
			beforeSend	:	function()
						{
							$('#divloader1').dialog('open');
						},
			success		:	function(response)
						{
							$('#divloader1').dialog('close');
							$('#divdata_detail_items').html(response);
							$('#divdata_detail_items').dialog('open');
						}			
	});
}


function searchcust(evt)
{
	var	is_nbs		=	$('#rdNBS').is(":checked");
	var	is_trade	=	$('#rdTRADE').is(":checked");
	var	is_nrevenue	=	$('#rdNON-REVENUE').is(":checked");
	var	doctype		=	$('#sel_doctype').val();
	
	if(is_nbs==true)
	{
		var	opt__	=	"NBS";
	}
	else if(is_trade==true)
	{
		var	opt__	=	"TRADE";
	}
	else if(is_nrevenue==true)
	{
		var	opt__	=	"NREVENUE";
	}
	
	if(opt__ != NREVENUE)
	{
		if(doctype=='NONE')
		{
			alert('Please select DOCUMENT TPYE');
			$('#customercode').val('');
			$('#customername').val('');
			return;
		}
	}
	
	var custcode	=	$('#customercode').val();
	var custname	=	$('#customername').val();
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;

	if(custcode != '' || custname != '')
	{
		if(evthandler != 40 && evthandler != 13 && evthandler != 27)
		{
			$.ajax({
				url			:	'order.php?action=SEARCHCUST&CUSTCODE='+custcode+'&CUSTNAME='+custname+'&OPT__='+opt__+'&DOCTYPE='+doctype,
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

function	remove_code()
{
	$('#customercode').val('');
	$('#customername').val('');
	$('#hdnval').val('')
}
</script>
</head>
<body style="font-size:12px;">
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0"  class="Text_header">
			<tr>
				<td width="100%" align="center">
					<table width="70%" border="0"class="label_text">
						<tr>
							<td width="15%">
								&nbsp;
							</td>
							<td width="15%">
								&nbsp;
							</td>
							<td width="70%">
								<input type="radio" name="rdcategory" id="rdNBS" value="NBS" checked onclick="remove_code();">NBS
								<input type="radio" name="rdcategory" id="rdTRADE" value="TRADE" onclick="remove_code();">TRADE
								<input type="radio" name="rdcategory" id="rdNON-REVENUE" value="NREVENUE" onclick="remove_code();">NON-REVENUE
							</td>
						</tr>
						<tr>
							<td width="15%">
								&nbsp;
							</td>
							<td width="15%" align="left">SOF</td>
							<td width="70%" colspan="2">
								:<input type="text" name="txtsof" id="txtsof" value="" size="10">
							</td>
						</tr>
						<tr>
							<td width="15%">
							 	&nbsp;
							</td>
							<td width="15%" align="left">CUSTOMER</td>
							<td width="70%">
								:<input type="text" name="customercode" id="customercode" value="" onkeyup="searchcust(event);" autocomplete="off" size="10" placeholder="CODE">
							 	<input type="text" name="customername" id="customername" value="" onkeyup="searchcust(event);" autocomplete="off" size="40" placeholder="NAME">
							 	<div id="divcust" style="position:absolute;"></div>
								<input type="hidden" id="hdnval" name="hdnval" value="">
							</td>
						</tr>
						<tr>
							<td width="15%">
							 	&nbsp;
							</td>
							<td width="15%" align="left">STATUS</td>
							<td width="70%">
								:<select name="sel_status" id="sel_status">
									<option value="NONE">--SELECT--</option>
									<option value="S001">S001</option>
									<option value="S002">S002</option>
									<option value="S003">S003</option>
									<option value="S004">S004</option>
								</select>
							</td>
						</tr>
						<tr>
							<td width="15%">
							 	&nbsp;
							</td>
							<td width="15%" align="left">DOC TYPE</td>
							<td width="70%">
								:<select name="sel_doctype" id="sel_doctype">
									<option value="NONE">--SELECT--</option>
									<option value="STF">STF</option>
									<option value="Invoice">INV</option>
								</select>
							</td>
						</tr>
						<tr>
							<td width="15%">
							 	&nbsp;
							</td>
							<td width="15%" align="left">ORDER DATE</td>
							<td width="70%">
								:<input type="text" name="dfrom" id="dfrom" 	class="dates" 	value="" size="10"  placeholder = "FROM">
							 	<input type="text" name="dto" 	id="dto" 	class="dates"	value="" size="10"  placeholder = "TO"	>
							</td>
						</tr>
						<tr>
							<td width="100%" colspan="3" align="center">
								<input type="button" name="btnsubmit" id="btnsubmit" value="SUBMIT" class="small_button" onclick="DISPLAY();"  style='width:120px;height:35px;'> 
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<div id="divdata_detail" title="ORDER LIST"></div>
		<div id="divdata_detail_items" title="ORDER DETAIL"></div>
		<div id="divloader1" style="display:none;" align="center" title="LOADING">SEARCHING PLEASE WAIT<img src="../../../images/loading/ajax-loader_fast.gif"></div>
	</form>
</body>
</html>
<script>
$(".dates").datepicker({ 
	dateFormat: 'yy-mm-dd',
	changeMonth: true,
    changeYear: true 
});

$("#divloader1").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	bgiframe:true, resizable:false, height: 100, width: 300, modal:true, autoOpen: false,	draggable: false
});

$("#divdata_detail").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	bgiframe:true, resizable:false, height: 700, width: 1100, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		},
		'PRINT PDF': function()
		{
			var	sofno	=	$('#txtsof').val();
			var custcode=	$('#customercode').val();
			var	status	=	$('#sel_status').val();
			var	doctype	=	$('#sel_doctype').val();
			var	dfrom	=	$('#dfrom').val();
			var	dto		=	$('#dto').val();
			var	is_nbs		=	$('#rdNBS').is(":checked");
			var	is_trade	=	$('#rdTRADE').is(":checked");
			var	is_nrevenue	=	$('#rdNON-REVENUE').is(":checked");
			
			if(is_nbs==true)
			{
				var	opt__	=	"NBS";
			}
			else if(is_trade==true)
			{
				var	opt__	=	"TRADE";
			}
			else if(is_nrevenue==true)
			{
				var	opt__	=	"NREVENUE";
			}
			
			//var url	=	,
			window.open('order_PDF.php?action=PDF&SOFNO='+sofno+'&CUSTCODE='+custcode+'&STATUS='+status+'&DOCTYPE='+doctype+'&DFROM='+dfrom+'&DTO='+dto+'&OPT__='+opt__);
		},
		'DOWNLOAD CSV': function()
		{
			var	sofno	=	$('#txtsof').val();
			var custcode=	$('#customercode').val();
			var	status	=	$('#sel_status').val();
			var	doctype	=	$('#sel_doctype').val();
			var	dfrom	=	$('#dfrom').val();
			var	dto		=	$('#dto').val();
			var	is_nbs		=	$('#rdNBS').is(":checked");
			var	is_trade	=	$('#rdTRADE').is(":checked");
			var	is_nrevenue	=	$('#rdNON-REVENUE').is(":checked");
			
			if(is_nbs==true)
			{
				var	opt__	=	"NBS";
			}
			else if(is_trade==true)
			{
				var	opt__	=	"TRADE";
			}
			else if(is_nrevenue==true)
			{
				var	opt__	=	"NREVENUE";
			}
			
			//var url	=	,
			window.open('order_CSV.php?action=PDF&SOFNO='+sofno+'&CUSTCODE='+custcode+'&STATUS='+status+'&DOCTYPE='+doctype+'&DFROM='+dfrom+'&DTO='+dto+'&OPT__='+opt__);
		}
	}
});

$("#divdata_detail_items").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	bgiframe:true, resizable:false, height: 700, width: 1000, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		}
	}
});
</script>