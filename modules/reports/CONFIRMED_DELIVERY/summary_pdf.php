<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}


		$CUSTOMERCODE	=	$_GET['CUSTOMERCODE'];
		$DFROM			=	$_GET['DFROM'];
		$DTO			=	$_GET['DTO'];
		$date_type		=	$_GET['SEL_DATA_TYPE'];
		$OPT_2			=	$_GET['OPT_2'];
		
		$opt	=	$_GET['OPT__'];
		$doc	=	$_GET['SEL_DOC'];
		
		
		$sof_list=	array();
		
		$sel_val_cust	 =	"SELECT * from WMS_NEW.CONFIRMDELIVERY_HDR WHERE 1 ";
		if (!empty($CUSTOMERCODE)) 
		{
		$sel_val_cust	.=	"AND CUSTNO = '{$CUSTOMERCODE}' ";
		}
		$sel_val_cust	.=	"AND DOCTYPE = '{$doc}' ";
		if ($_GET['DATE_TYPE'] != 'ADDED') 
		{
		 $sel_val_cust	 .=	" AND CONFIRMDELDATE between '{$DFROM}' AND '{$DTO}' order by CONFIRMDELDATE asc ";
		}
		else 
		{
		$sel_val_cust	 .=	" AND ADDEDDATE between '{$DFROM}' AND '{$DTO}' order by ADDEDDATE asc ";	
		}
//		if ($_SESSION['username'] != 'raymond') 
//		{
//		$sel_val_cust	.=	"AND DOCTYPE = '{$doc}' and ADDEDBY = '{$_SESSION['username']}' ";
//		}
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
			}
		}
	
		class PDF extends FPDF 
		{
			function Header()
			{
				
				$this->Image("/var/www/html/wms/images/fdc101.jpg",10,5,38,18);
				
				$this->SetFont('Courier','B',15);
				$this->SetX(10);$this->Cell(0,5,'CONFIRMED DELIVERIES',0,1,'C');
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(0,5,$_GET['OPT__'].",".$_GET['SEL_DOC'],0,1,'C');
				$this->SetFont('Courier','B',12);
				
				if ($_GET['DATE_TYPE']=='ADDED') 
				{
				$this->SetX(10);$this->Cell(0,5,"ADDED DATE ".date("F d, Y",strtotime($_GET['DFROM']))." to ".date("F d, Y",strtotime($_GET['DTO'])),0,1,'C');
				$this->SetX(10);$this->Cell(0,5,$_GET['DFROM_TIME']." to ".$_GET['DTO_TIME'],0,1,'C');
				}
				else 
				{
				$this->SetX(10);$this->Cell(0,5,"DELIVERY DATE".date("F d, Y",strtotime($_GET['DFROM']))." to ".date("F d, Y",strtotime($_GET['DTO'])),0,1,'C');	
				}
				
				$this->ln(5);
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(15,5,'LINE#',0,0,'L');
				$this->SetX(25);$this->Cell(25,5,'CUSTOMER',0,0,'C');
				$this->SetX(135);$this->Cell(35,5,'SOF',0,0,'C');
				$this->SetX(158);$this->Cell(35,5,'DOC NO',0,0,'C');
				$this->SetX(178);$this->Cell(45,5,'GROSS AMT',0,0,'C');
				$this->SetX(208);$this->Cell(45,5,'NET AMT',0,0,'C');
				$this->SetX(240);$this->Cell(30,5,'TRANSMITTED',0,1,'C');
			}
			
			function HEADER_TRXNO($TRXNO)
			{
				$this->SetFont('Courier','B',15);
				$this->SetXY(10,35);$this->Cell(10,5,$TRXNO,0,1,'L');
				$this->ln(5);
			}
			
			function Footer()
			{
				$this->SetFont('Courier','',9);
				$this->SetXY(10,185);$this->Cell(0,5,'Printed By  : '.$_SESSION['username'],0,0,'L');
				$this->SetXY(10,190);$this->Cell(0,5,'Printed Date  : '.date('Y-m-d'),0,0,'L');
				$this->SetXY(10,195);$this->Cell(0,5,'Printed Time : '.date('H:i A'),0,0,'L');
				$this->SetXY(10,200);$this->Cell(0,5	,'Page '.$this->PageNo().'/{nb}',0,0,'L');
			}
			
			function Sel_val($conn,$database,$tbl,$fld,$condition)
			{
				$sel	=	"SELECT $fld FROM ".$database.".$tbl WHERE $condition";
				$rssel	=	$conn->Execute($sel);
				if ($rssel == false) 
				{
					die($conn->ErrorMsg());
				}
				$retval	=	$rssel->fields[$fld];
				return $retval;
			}
		}
		
		$pdf= new PDF('L','mm','letter');
		$pdf->Open();
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak('auto',35);
		$pdf->AddPage();
		
		if ($rssel_sof->RecordCount() > 0) 
		{
			$counter=1;
			$pdf->SetFont('Courier','',12);
			while (!$rssel_sof->EOF) 
			{
				
				$SOF			=	$rssel_sof->fields['SOF'];
				$CUSTNO			=	$rssel_sof->fields['CUSTNO'];
				$REFN0			=	$rssel_sof->fields['DOCNO'];
				$RCVDNETAMOUNT	=	$rssel_sof->fields['RCVDNETAMOUNT'];
				$RCVDGROSSAMOUNT=	$rssel_sof->fields['RCVDGROSSAMOUNT'];
				$CONFIRMDELDATE	=	$rssel_sof->fields['CONFIRMDELDATE'];
				$ADDEDDATE		=	$rssel_sof->fields['ADDEDDATE'];
				$TRANSMIT		=	$rssel_sof->fields['TRANSMIT'];
				
				$custname		=	substr($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' "),0,35);
				$InvoiceDate	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","InvoiceDate","OrderNo = '{$SOF}' ");
					
				
//				$pdf->SetX(10);$pdf->Cell(15,5,$counter,0,0,'L');
//				$pdf->SetX(25);$pdf->Cell(25,5,$SOF,0,0,'C');
//				$pdf->SetX(53);$pdf->Cell(100,5,$CUSTNO.'-'.substr($custname,0,35),0,0,'L');
//				$pdf->SetX(162);$pdf->Cell(30,5,$REFN0,0,0,'C');
//				$pdf->SetX(185);$pdf->Cell(30,5,number_format($RCVDNETAMOUNT,2),0,0,'R');
//				$pdf->SetX(222);$pdf->Cell(25,5,$InvoiceDate,0,0,'R');
//				$pdf->SetX(243);$pdf->Cell(35,5,number_format($RCVDGROSSAMOUNT,2),0,0,'R');
//				$pdf->SetX(270);$pdf->Cell(45,5,$CONFIRMDELDATE,0,0,'R');
//				$pdf->SetX(318);$pdf->Cell(30,5,$ADDEDDATE,0,1,'R');

				$pdf->SetX(10);$pdf->Cell(15,5,$counter,0,0,'L');
				$pdf->SetX(25);$pdf->Cell(25,5,$CUSTNO.'-'.$custname,0,0,'L');
				$pdf->SetX(138);$pdf->Cell(35,5,$SOF,0,0,'L');
				$pdf->SetX(158);$pdf->Cell(35,5,$REFN0,0,0,'C');
				$pdf->SetX(167);$pdf->Cell(45,5,$RCVDGROSSAMOUNT,0,0,'R');
				$pdf->SetX(195);$pdf->Cell(45,5,$RCVDNETAMOUNT,0,0,'R');
				$pdf->SetX(250);$pdf->Cell(30,5,$TRANSMIT,0,1,'C');
				
				
				$total_net	+=	$RCVDNETAMOUNT;
				$total_gross+=	$RCVDGROSSAMOUNT;
				
				$counter++;
				
				$rssel_sof->MoveNext();
			}
			$pdf->Ln(1);
			$pdf->SetFont('Courier','B',10);
			$pdf->SetX(53);$pdf->Cell(100,5,"TOTAL",0,0,'C');
			$pdf->SetX(177);$pdf->Cell(35,5,number_format($total_gross,2),0,0,'R');
			$pdf->SetX(205);$pdf->Cell(35,5,number_format($total_net,2),0,1,'R');
			$pdf->SetFont('Courier','IB',11);
			$pdf->SetX(10);$pdf->Cell(0,5,"* * * * * * * * * * * END OF RECORD * * * * * * * * * * *",0,0,'C');
		}
		else 
		{
			$pdf->SetFont('Courier','B',13);
			$pdf->SetX(10);$pdf->Cell(0,5," * * * NO RECORD FOUND * * * ",0,0,'C');
		}
		echo $pdf->Output();
?>