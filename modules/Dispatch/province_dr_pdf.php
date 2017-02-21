<?php
	/**
	 * Author		:	Raymond A. Galaroza
	 * Date Created	:	2013-08-02
	 * Description	:	Print of Dispatch Schedule(PROVINCE)
	 */
	session_start();
	set_time_limit(0);
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
	if (empty($_SESSION['username'])) 
	{
		echo "<script>alert('You dont have a session!');</script>";
		echo "<script>location='../../index.php'</script>";
	}
	
	$tracking	=	$_GET['schedule_tracking'];
	$cnt		=	" SELECT COUNT(ID) AS CNT FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_HDR WHERE TRANSEQ = '{$tracking}' ";
	$rscnt		=	$Filstar_conn->Execute($cnt);
	if ($rscnt->fields['CNT'] > 0) 
	{
		$aData		=	array();
		$sel	 =	" SELECT ID_DTL,TRANSEQ,CUSTCODE,INVOICENO,INVOICEAMOUNT,SOFNO,PRODUCT_LINE,SIZE_OF_CTN,QTY_CTN,DR_NO,QTY_PKG,QTY_BDL,DECLARED_2,WEIGHT_BY_KILO ";
		$sel	.=	" FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL WHERE TRANSEQ = '{$tracking}' ";
		$rssel	=	$Filstar_conn->Execute($sel);
		if ($rssel == false) 
		{
			die(mysql_errno().":".mysql_error());
		}
		else 
		{
			while (!$rssel->EOF) 
			{
				$id_dtl			=	$rssel->fields['ID_DTL'];
				$transeq		=	$rssel->fields['TRANSEQ'];
				$custcode		=	$rssel->fields['CUSTCODE'];
				$invoiceno		=	$rssel->fields['INVOICENO'];
				$invoiceamount	=	$rssel->fields['INVOICEAMOUNT'];
				$sofno			=	$rssel->fields['SOFNO'];
				$product_line	=	$rssel->fields['PRODUCT_LINE'];
				$size_of_ctn	=	$rssel->fields['SIZE_OF_CTN'];
				$qty_ctn		=	$rssel->fields['QTY_CTN'];
				$dr_no			=	$rssel->fields['DR_NO'];
				$qty_pkg		=	$rssel->fields['QTY_PKG'];
				$qty_bdl		=	$rssel->fields['QTY_BDL'];
				$declared_2		=	$rssel->fields['DECLARED_2'];
				$weight_by_kilo	=	$rssel->fields['WEIGHT_BY_KILO'];
			
				$aData[$transeq][$custcode][$invoiceno]['ID']			=	$id_dtl;
				$aData[$transeq][$custcode][$invoiceno]['INVOICEAMOUNT']=	$invoiceamount;
				$aData[$transeq][$custcode][$invoiceno]['SOFNO']		=	$sofno;
				$aData[$transeq][$custcode][$invoiceno]['PRODUCT_LINE']	=	$product_line;
				$aData[$transeq][$custcode][$invoiceno]['SIZE_OF_CTN']	=	$size_of_ctn;
				$aData[$transeq][$custcode][$invoiceno]['QTY_CTN']		=	$qty_ctn;
				$aData[$transeq][$custcode][$invoiceno]['DR_NO']		=	$dr_no;
				$aData[$transeq][$custcode][$invoiceno]['QTY_PKG']		=	$qty_pkg;
				$aData[$transeq][$custcode][$invoiceno]['QTY_BDL']		=	$qty_bdl;
				$aData[$transeq][$custcode][$invoiceno]['DECLARED_2']	=	$declared_2;
				$aData[$transeq][$custcode][$invoiceno]['WEIGHT_BY_KILO']=	$weight_by_kilo;
				$rssel->MoveNext();
			}
		}
	}
	else 
	{
		$aData	=	array();
	}
//	print_r($aData);
//	exit();
	class PDF extends FPDF 
	{
		function Header_ko($val_cust,$val_add)
		{
			$this->SetFont('Times','',9);
		    $this->SetX(10);$this->Cell(0,4,FDC_HEADER,0,1,'C');
		    $this->SetX(10);$this->Cell(0,4,FDC_ADDRESS,0,1,'C');
		    $this->SetX(10);$this->Cell(0,4,FDC_TEL,0,1,'C');
		    $this->Ln(5);
		    $this->SetX(15);$this->Cell(0,4,'DELIVERY RECEIPT',0,1,'C');
		    $this->Ln(3);
		    $this->SetX(15);$this->Cell(50,4,'Del. To',0,0,'L');
		    $this->SetX(28);$this->Cell(50,4,':',0,0,'L');
		    $this->SetX(30);$this->Cell(50,4,$val_cust,0,0,'L');
		    
		    $this->SetX(160);$this->Cell(50,4,'DATE :',0,0,'L');
		    $this->SetX(170);$this->Cell(50,4,date('F d, Y'),0,1,'L');
		    
		    $this->SetX(15);$this->Cell(50,4,'Address',0,0,'L');
		    $this->SetX(30);$this->Cell(50,4,$val_add,0,0,'L');
		    $this->SetX(28);$this->Cell(50,4,':',0,1,'L');
		    $this->Ln(5);
		}
		
		function Sub_header()
		{
			$this->SetX(15);$this->Cell(185,14,'',1,0,'C');
			$this->SetX(10);$this->Cell(55,14,'I T E M S',0,0,'C');
			$this->SetX(70);$this->Cell(20,7,'SOF',0,0,'C');
			$this->SetX(95);$this->Cell(20,7,'INV/STF',0,0,'C');
			$this->SetX(120);$this->Cell(30,7,'Q U A N T I T Y',0,0,'C');
			$this->SetX(155);$this->Cell(20,7,'Declared',0,0,'C');
			$this->SetX(180);$this->Cell(18,7,'WEIGHT',0,1,'C');
			$this->SetX(70);$this->Cell(20,7,'No.',0,0,'C');
			$this->SetX(95);$this->Cell(20,7,'DR No.',0,0,'C');
			$this->SetX(120);$this->Cell(7,7,'ctn',0,0,'C');
			$this->SetX(132);$this->Cell(7,7,'pkg',0,0,'C');
			$this->SetX(144);$this->Cell(7,7,'bdl',0,0,'C');
			$this->SetX(155);$this->Cell(20,7,'Value',0,0,'C');
			$this->SetX(180);$this->Cell(18,7,'',0,1,'C');
			$this->Ln(5);
		}
		
		function Footer_ko()
		{
			$this->SetFont('Times','',12);
		    $this->SetXY(15,110);$this->Cell(85,10,'Received by',0,0,'L');
		    $this->SetXY(37,110);$this->Cell(85,10,':',0,0,'L');
		    $this->SetXY(40,117);$this->Cell(60,10,'',"B",1,'L');
		    
		    $this->SetXY(15,120);$this->Cell(85,10,'Date',0,0,'L');
		    $this->SetXY(37,120);$this->Cell(85,10,':',0,0,'L');
		    $this->SetXY(40,108);$this->Cell(60,10,'',"B",1,'L');
		    $this->SetXY(125,110);$this->Cell(60,10,'DR NO.:',0,1,'L');
		}
	}
	
	$y=139.7;
	//$y=150;
	$x=215.9;
	$sizeko	=	array($x,$y);
	$pdf = new PDF('P','mm',$sizeko);
	$pdf->Open();
	$pdf->AliasNbPages();
	$pdf->SetAutoPageBreak(0,300);
	$pdf->SetFont('Times','',9);
	
	if (is_array($aData)) 
	{
		foreach ($aData as $tracking=>$val_cust)
		{
			foreach ($val_cust as $custcode=>$val_invoice)
			{
				$pdf->AddPage();
				$custname	=	$global_func->CustName($Filstar_conn,$custcode);
				$street	=	$global_func->Select_val($Filstar_conn,FDCRMS,"customer_address","StreetNumber","custno= '$custcode'");
				$town	=	$global_func->Select_val($Filstar_conn,FDCRMS,"customer_address","TownCity","custno= '$custcode'");
				$add	=	$street.','.$town;
				$pdf->Header_ko($custname,$add);
				$pdf->Sub_header();
				foreach ($val_invoice as $invoice=>$val_)
				{
					$pdf->SetX(10);$pdf->Cell(55,4,'I T E M S',0,0,'C');
					$pdf->SetX(70);$pdf->Cell(20,4,$val_['SOFNO'],0,0,'C');
					$pdf->SetX(95);$pdf->Cell(20,4,$invoice,0,0,'C');
					$pdf->SetX(97);$pdf->Cell(30,4,$val_['QTY_CTN'],0,0,'R');
					$pdf->SetX(109);$pdf->Cell(30,4,$val_['QTY_PKG'],0,0,'R');
					$pdf->SetX(121);$pdf->Cell(30,4,$val_['QTY_BDL'],0,0,'R');
					$pdf->SetX(155);$pdf->Cell(20,4,$val_['DECLARED_2'],0,0,'R');
					$pdf->SetX(175);$pdf->Cell(18,4,number_format($val_['WEIGHT_BY_KILO'],2),0,1,'R');
				}
				$pdf->Footer_ko();
			}
		}
		$pdf->Output();
	}
?>