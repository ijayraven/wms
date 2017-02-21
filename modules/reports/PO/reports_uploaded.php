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
	
	$sel	=	" SELECT PONO,SOFNO,SUBSTRING(SOFDATE,1,10) as SOFDATE,PLNO FROM FDC_WMS.UPLOADED WHERE SUBSTRING(SOFDATE,1,10) BETWEEN '{$from}' and '{$to}' ";
	echo $sel;
	die();
	if ($selsearch == 'PO') 
	{
		if (!empty($PO)) 
		{
		$sel	.=	" AND PONO = '{$PO}' ";
		}
	}
	else 
	{
		if (!empty($PO)) 
		{
		$sel	.=	" AND SOFNO = '{$PO}' ";	
		}
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
			$this->SetX(10);$this->Cell(0,5,'PURCHASE ORDER THAT ARE SUCCESSFULLY UPLOADED',0,1,'C');
			$this->Ln(5);
			$this->SetFont('Times','B',12);
			$this->SetX(10);$this->Cell(70,5,'PO#',0,0,'C');
			$this->SetX(78);$this->Cell(30,5,'SOF#',0,0,'C');
			$this->SetX(115);$this->Cell(30,5,'PLNO#',0,0,'C');
			$this->SetX(150);$this->Cell(55,5,'SOF DATE',0,1,'C');
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
			$pdf->SetX(25);$pdf->Cell(50,5,$rssel->fields['PONO'],1,0,'C');
			$pdf->SetX(75);$pdf->Cell(35,5,$rssel->fields['SOFNO'],1,0,'C');
			$pdf->SetX(110);$pdf->Cell(40,5,$rssel->fields['PLNO'],1,0,'C');
			$pdf->SetX(150);$pdf->Cell(55,5,$rssel->fields['SOFDATE'],1,1,'C');
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
