<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}

	$sof		=	$_GET['SOFNO'];
	$custcode	=	$_GET['CUSTCODE'];
	$status		=	$_GET['STATUS'];
	$doctype	=	$_GET['DOCTYPE'];
	$from		=	$_GET['DFROM'];
	$to			=	$_GET['DTO'];
	$aData		=	array();
	$aData_cust	=	array();
	$aOrder		=	array();
	$opt		=	$_GET['OPT__'];
	
	$_SESSION['NAME']		=	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","USER","NAME","USERNAME = '{$_SESSION['username']}' ");

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
		
	foreach ($aData as $key)
	{
		$sel_data_h		=	"SELECT CustNo,OrderDate,OrderStatus,PickListNo,OrderAmount,InvoiceAmount,InvoiceNo from orderheader where ";
		$sel_data_h	   .=	"OrderNo = '{$key}' ";
		$rsssel_data_h	=	$Filstar_conn->Execute($sel_data_h);
		if ($rsssel_data_h==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$CustNo			=	$rsssel_data_h->fields['CustNo'];
		
		$CustName		=	substr($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CustNo}' "),0,40);
		$SalesRepCode	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CustNo}' ");
		
		$aOrder[$SalesRepCode][]	=	$key;
	}
	
	$cCSV	 =	"FILSTAR DISTRIBUTORS CORP\n";
	if ($status == 'S001') 
	{
	$cCSV	 .=	"SOF FOR PICKING\n";
	}
	elseif ($status == 'S002')
	{
	$cCSV	 .=	"PLCONF NOT YET DISPATCH\n";
	}
	elseif ($status == 'S003')
	{
	$cCSV	 .=	"DISPATCH NOT YET CONF DELIVERY\n";
	}
	elseif ($status == 'S004')
	{
	$cCSV	 .=	"CONF DELIVERY\n";
	}
	
	$cCSV	 .=	'ORDER DATE from '.date("F d, Y",strtotime($from))." to ".date("F d, Y",strtotime($to));
	$cCSV	 .=	"$opt;$doctype\n";
	$cCSV	 .=	"SALES REP;SOFNO;CUSTOMER;PLNO;INVNO;TRACKING NO;SOFDATE;GROSS AMOUNT;NET AMOUNT;ORDERED QTY;CONFIRMED QTY\n";
	
	
	foreach ($aOrder as $key_sr=>$val__)
	{
		$total_net	=	0;
		$total_gross=	0;
		$total_0_qty=	0;
		$total_R_qty=	0;
		
		foreach ($val__ as $key_val__)
		{
			
			$PickListNo		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","PickListNo","OrderNo	 = '{$key_val__}' ");
			$CustNo		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","CustNo","OrderNo	 = '{$key_val__}' ");
			$InvoiceNo		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","InvoiceNo","OrderNo	 = '{$key_val__}' ");
			$OrderDate		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","OrderDate","OrderNo	 = '{$key_val__}' ");
			$OrderAmount	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","OrderAmount","OrderNo	 = '{$key_val__}' ");
			$ReleasedAmount	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","ReleasedAmount","OrderNo	 = '{$key_val__}' ");
			$InvoiceAmount 	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","InvoiceAmount","OrderNo	 = '{$key_val__}' ");
			$CustName		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CustNo}'");
			if ($status=='S003') 
			{
				$TRACKINGNO		=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_METROMANILA_DTL","TRACKINGNO","SOFNO = '{$key_val__}' AND 	STATUS = 'OPEN' ");
				if (empty($TRACKINGNO))
				{
					$TRACKINGNO		=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_PROVINCE_DTL","TRACKINGNO","SOFNO = '{$key_val__}' AND 	STATUS = 'OPEN' ");
				}
			}						
			
			$salesreps 	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$key_sr}' ");
			
			$sel_data_d		=	"SELECT sum(OrderQty)as O_QTY,sum(ReleaseQty)as R_QTY,sum(GrossAmount)as AMT,sum(NetAmount)as N_AMT FROM orderdetail ";
			$sel_data_d		.=	"where OrderNo = '{$key_val__}' and isDeleted = 'N' ";
			$rssel_data_d	=	$Filstar_conn->Execute($sel_data_d);
			if ($rssel_data_d==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			$O_QTY			=	$rssel_data_d->fields['O_QTY'];
			$R_QTY			=	$rssel_data_d->fields['R_QTY'];
			$gross			=	$rssel_data_d->fields['AMT'];
			$net			=	$rssel_data_d->fields['N_AMT'];
			
			$total_net		+=	$net;
			$total_gross	+=	$gross;
			$total_0_qty	+=	$O_QTY;
			$total_R_qty	+=	$R_QTY;
			
			$cCSV	 .=	"$key_sr-$salesreps;$key_val__;$CustNo-$CustName;$PickListNo;$InvoiceNo;$TRACKINGNO;$OrderDate;$gross;$net;$O_QTY;$R_QTY"."\n";
		}
			$cCSV	 .=	";;;;;;;$total_gross;$total_net;$total_0_qty;$total_R_qty"."\n\n";
	}  

$filename	=	"ORDER_STATUS";

header("Content-type: text/csv");
header("Content-Transfer-Encoding: UTF-8");
header("Content-Disposition: attachment; filename=$filename.csv");
header("Pragma: no-cache");
header("Expires: 0");
echo $cCSV;
?>