<?php
/**
	 * Author		:	Raymond A. Galaroza
	 * Date Created	:	2013-08-02
	 * Description	:	Print of Dispatch Schedule(MANILA)
	 */
	session_start();
	set_time_limit(0);
	include('../../../include/config/consolidator.php');
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
	if (empty($_SESSION['username'])) 
	{
		echo "<script>alert('You dont have a session!');</script>";
		echo "<script>location='../../index.php'</script>";
	}
	
	$from		=	$_GET['DFROM'];
	$to			=	$_GET['DTO'];
	$PO			=	$_GET['TXTPO'];
	$selsearch	=	$_GET['SELSEARCH'];
	
	$sel	=	" SELECT PONO,SUBSTRING(UPLOADDATE,1,10) as PODATE FROM FDC_WMS.NOALPHA WHERE SUBSTRING(UPLOADDATE,1,10) BETWEEN '{$from}' and '{$to}' ";
	if (!empty($PO)) 
	{
	$sel	.=	" AND PONO = '{$PO}' ";
	}
	$rssel	=	$db1->Execute($sel);
	if ($rssel == false) 
	{
		echo $db1->ErrorMsg();
		die();
	}
	/*echo $rssel->Recordcount();
	exit();*/
	class PDF extends FPDF 
	{
		function Header()
		{
			global $from,$to;
			$this->SetFont('Times','B',12);
			$this->SetX(10);$this->Cell(0,5,FDC_HEADER,0,1,'C');
			$this->SetFont('Times','I',10);
			$this->SetX(10);$this->Cell(0,5,'From '.date('F j, Y',strtotime($from)).' to '.date('F j, Y',strtotime($to)),0,1,'C');
			$this->SetX(10);$this->Cell(0,5,'PURCHASE ORDER THAT ARE NOT SUCCESSFULLY UPLOADED',0,1,'C');
			$this->Ln(5);
			$this->SetFont('Times','B',12);
			$this->SetX(10);$this->Cell(120,5,'PO#',0,0,'C');
			$this->SetX(130);$this->Cell(70,5,'UPLOAD DATE',0,1,'C');
		}
		
		//Page footer
		 function Footer()
		 {
			//Page No
		    $this->SetY(-15);
			$this->SetFont('Times','I',8);
		    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		 }
	}
	
	$pdf = new PDF('P','mm','letter');
	$pdf->Open();
	$pdf->AddPage();
	$pdf->AliasNbPages();
	$pdf->SetFont('Times','',9);
	if ($rssel->Recordcount() > 0) 
	{
		$cnt=1;
		$pdf->SetFont('courier','',9);
		while (!$rssel->EOF) 
		{
			$pdf->SetX(10);$pdf->Cell(15,5,$cnt,1,0,'C');
			$pdf->SetX(25);$pdf->Cell(95,5,$rssel->fields['PONO'],1,0,'C');
			$pdf->SetX(120);$pdf->Cell(90,5,$rssel->fields['PODATE'],1,1,'C');
			$cnt++;
			$rssel->MoveNext();
		}
		$pdf->SetX(10);$pdf->Cell(0,10,"* * * * * END of report * * * * * ",0,0,'C');
	}
	else 
	{
			$pdf->SetFont('courier','B',12);
		 	$pdf->SetX(10);$pdf->Cell(0,10,"* * * * * No Record(s) Found * * * * * ",0,0,'C');
	}
	$pdf->Output();
?>