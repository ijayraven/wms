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
		
		
		class PDF extends FPDF 
		{
			function Header()
			{
				$sof		=	$_GET['SOFNO'];
				$custcode	=	$_GET['CUSTCODE'];
				$status		=	$_GET['STATUS'];
				$doctype	=	$_GET['DOCTYPE'];
				$from		=	$_GET['DFROM'];
				$to			=	$_GET['DTO'];
				$aData		=	array();
				$aData_cust	=	array();
				$opt		=	$_GET['OPT__'];
				
				$this->Image("/var/www/html/wms/images/fdc101.jpg",10,5,38,18);

				$this->SetFont('Times','B',12);
				$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
				if ($status == 'S001') 
				{
				$this->SetX(10);$this->Cell(0,5,'SOF FOR PICKING',0,1,'C');
				}
				elseif ($status == 'S002')
				{
				$this->SetX(10);$this->Cell(0,5,'PLCONF NOT YET DISPATCH',0,1,'C');	
				}
				elseif ($status == 'S003')
				{
				$this->SetX(10);$this->Cell(0,5,'DISPATCH NOT YET CONF DELIVERY',0,1,'C');
				}
				elseif ($status == 'S004')
				{
				$this->SetX(10);$this->Cell(0,5,'CONF DELIVERY',0,1,'C');	
				}
				$this->Ln(2);
				$this->SetFont('Times','B',10);
				$this->SetX(10);$this->Cell(0,5,'ORDER DATE from '.date("F d, Y",strtotime($from))." to ".date("F d, Y",strtotime($to)),0,1,'C');
				$this->ln(5);
				
				$this->SetFont('Times','B',10);
				$this->SetX(10);$this->Cell(0,5,'CUSTOMER TYPE : '.$opt,0,0,'L');
				$this->SetX(150);$this->Cell(0,5,'DOC TYPE : '.$doctype,0,1,'L');
				$this->ln(2);
				
				
				$this->SetFont('Times','B',8);
				if ($status != 'S001') 
				{
				$this->SetX(5);$this->Cell(15,5,"LINE NO.",0,0,'C');
				$this->SetX(20);$this->Cell(20,5,"SOF NO.",0,0,'C');
				$this->SetX(40);$this->Cell(75,5,"CUSTOMER",0,0,'C');
				$this->SetX(115);$this->Cell(20,5,"SOF DATE",0,0,'C');
				$this->SetX(135);$this->Cell(18,5,"PL NO.",0,0,'C');
				$this->SetX(153);$this->Cell(18,5,strtoupper($doctype)." NO.",0,0,'C');
				$this->SetX(173);$this->Cell(18,5,"GROSS",0,0,'C');
				$this->SetX(194);$this->Cell(18,5,"NET",0,1,'C');
				}
				else 
				{
				$this->SetX(5);$this->Cell(17,5,"LINE NO.",0,0,'C');
				$this->SetX(22);$this->Cell(20,5,"SOF NO.",0,0,'C');
				$this->SetX(42);$this->Cell(78,5,"CUSTOMER",0,0,'C');
				$this->SetX(120);$this->Cell(25,5,"SOF DATE",0,0,'C');
				$this->SetX(145);$this->Cell(20,5,"PL NO.",0,0,'C');
				$this->SetX(170);$this->Cell(23,5,"GROSS",0,0,'C');
				$this->SetX(193);$this->Cell(20,5,"NET",0,1,'C');	
				}
			}
			
			function Footer()
			{
				$this->SetFont('Courier','',9);
				$this->SetFont('Times','',9);
				$this->SetY(245);$this->Cell(0,10,'Printed By : '.$_SESSION['NAME'],0,1,'L');
				$this->SetY(248);$this->Cell(0,10,'Printed Date  : '.date('Y-m-d'),0,1,'L');
				$this->SetY(251);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
				$this->SetY(255);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
			}
		}
			
			
		$pdf= new PDF('P','mm','letter');
		$pdf->Open();
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak('auto',35);
		$pdf->AddPage();
		
		
		$cnt	=	1;
		
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
			
			$OrderDate		=	$rsssel_data_h->fields['OrderDate'];
			$OrderStatus	=	$rsssel_data_h->fields['OrderStatus'];
			$PickListNo		=	$rsssel_data_h->fields['PickListNo'];
			$gross			=	$rsssel_data_h->fields['OrderAmount'];
			$net			=	$rsssel_data_h->fields['InvoiceAmount'];
			$InvoiceNo		=	$rsssel_data_h->fields['InvoiceNo'];
			
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
			
			if ($status != 'S001') 
			{
				if ($O_QTY != $R_QTY) 
				{
					$pdf->SetFont('Times','BI',8);
				}
				else 
				{
					$pdf->SetFont('Times','',8);
				}
				$pdf->SetX(5);$pdf->Cell(15,5,$cnt,0,0,'C');
				$pdf->SetX(20);$pdf->Cell(20,5,$key,0,0,'C');
				$pdf->SetX(40);$pdf->Cell(75,5,$CustNo.'-'.$CustName,0,0,'L');
				$pdf->SetX(115);$pdf->Cell(20,5,$OrderDate,0,0,'C');
				$pdf->SetX(135);$pdf->Cell(18,5,$PickListNo,0,0,'C');
				$pdf->SetX(153);$pdf->Cell(18,5,$InvoiceNo,0,0,'C');
				$pdf->SetX(170);$pdf->Cell(18,5,number_format($gross,2),0,0,'R');
				$pdf->SetX(193);$pdf->Cell(18,5,number_format($net,2),0,1,'R');	
			}
			else 
			{
				if ($O_QTY != $R_QTY) 
				{
					$pdf->SetFont('Times','BI',8);
				}
				else 
				{
					$pdf->SetFont('Times','',8);
				}
				$pdf->SetX(5);$pdf->Cell(17,5,$cnt,0,0,'C');
				$pdf->SetX(22);$pdf->Cell(20,5,$key,0,0,'C');
				$pdf->SetX(42);$pdf->Cell(78,5,$CustNo.'-'.$CustName,0,0,'L');
				$pdf->SetX(120);$pdf->Cell(25,5,$OrderDate,0,0,'C');
				$pdf->SetX(145);$pdf->Cell(20,5,$PickListNo,0,0,'C');
				$pdf->SetX(165);$pdf->Cell(23,5,number_format($gross,2),0,0,'R');
				$pdf->SetX(190);$pdf->Cell(20,5,number_format($net,2),0,1,'R');
			}
			
			$total_net	+=	$gross;
			$total_gross+=	$net;
			$cnt++;
		}
		
		$pdf->Ln(1);
		$pdf->SetFont('Courier','B',8);
		$pdf->SetX(120);$pdf->Cell(40,5,"TOTAL AMOUNT",0,0,'L');
		$pdf->SetX(165);$pdf->Cell(23,5,number_format($total_net,2),0,0,'R');
		$pdf->SetX(191);$pdf->Cell(20,5,number_format($total_gross,2),0,1,'R');
		$pdf->SetFont('Courier','IB',11);
		$pdf->SetX(10);$pdf->Cell(0,5,"* * * * * * * * * * * END OF RECORD * * * * * * * * * * *",0,0,'C');
		echo $pdf->Output();
?>