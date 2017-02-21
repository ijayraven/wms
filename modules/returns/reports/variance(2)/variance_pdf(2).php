<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}
class PDF extends FPDF 
{
	function Header()
	{
		$this->SetFont('Times','B',12);
		$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'SCANNED MPOS',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'VARIANCE REPORT',0,1,'C');
		$this->ln(5);
		
		$this->image("fdc_ls_col.jpg",10,10,40,20);
		
		$this->SetFont('Times','B',10);
		$this->SetX(4);$this->Cell(10,5,'Line',1,0,'C');
		$this->SetX(14);$this->Cell(101,5,'CUSTOMER',1,0,'C');
		$this->SetX(115);$this->Cell(20,5,'MPOS No.',1,0,'C');
		$this->SetX(135);$this->Cell(20,5,'MPOS Date',1,0,'C');
		$this->SetX(155);$this->Cell(20,5,'Posted Date',1,0,'C');
		$this->SetX(175);$this->Cell(20,5,'Reason',1,0,'C');
		$this->SetX(195);$this->Cell(20,5,'MPOS Qty',1,0,'C');
		$this->SetX(215);$this->Cell(20,5,'Posted Qty',1,0,'C');
		$this->SetX(235);$this->Cell(20,5,'MPOS Amt',1,0,'C');
		$this->SetX(255);$this->Cell(20,5,'Posted Amt',1,1,'C');
	}
	
	function Footer()
	{
		$this->SetFont('Courier','',9);
		$this->SetFont('Times','',9);
		$this->SetY(200);$this->Cell(0,10,'Printed Date  : '.date('Y-m-d'),0,1,'L');
		$this->SetY(203);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
		$this->SetY(206);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
			
			
$pdf= new PDF('L','mm','Letter');
$pdf->Open();
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak('auto',15);
$pdf->AddPage();
$pdf->SetFont('Times','',9);

$arrMpos	=	$_SESSION["arrVAR"];
$cnt		=	1;
$GRANDMPOSTOTAL	=	0;
$GRANDPSTDTOTAL	=	0;
$GRANDMPOSQTY	=	0;
$GRANDPSTDQTY	=	0;
$pdf->SetFont('Times','',9);
foreach($arrMpos as $MPOSNO=>$val1)
{
	$CUSTNO		= $val1["CUSTNO"];
	$MPOSDATE	= $val1["MPOSDATE"];
	$POSTEDDATE	= $val1["POSTEDDATE"];
	$SALESREPNO	= $val1["SALESREPNO"];
	$REASON		= $val1["REASON"];
	$TOTALQTY	= $val1["TOTALQTY"];
	$GROSSAMOUNT= $val1["GROSSAMOUNT"];
	$POSTEDQTY	= $val1["POSTEDQTY"];
	$POSTEDAMT	= $val1["POSTEDAMT"];

	$pdf->SetX(4);$pdf->Cell(10,5,$cnt,1,0,'C');
	$pdf->SetX(14);$pdf->Cell(101,5,$CUSTNO,1,0,'L');
	$pdf->SetX(115);$pdf->Cell(20,5,$MPOSNO,1,0,'C');
	$pdf->SetX(135);$pdf->Cell(20,5,$MPOSDATE,1,0,'C');
	$pdf->SetX(155);$pdf->Cell(20,5,$POSTEDDATE,1,0,'C');
	$pdf->SetX(175);$pdf->Cell(20,5,$REASON,1,0,'C');
	$pdf->SetX(195);$pdf->Cell(20,5,number_format($TOTALQTY),1,0,'C');
	$pdf->SetX(215);$pdf->Cell(20,5,number_format($POSTEDQTY),1,0,'C');
	$pdf->SetX(235);$pdf->Cell(20,5,number_format($GROSSAMOUNT,2),1,0,'R');
	$pdf->SetX(255);$pdf->Cell(20,5,number_format($POSTEDAMT,2),1,1,'R');
	
	$GRANDMPOSTOTAL	+=	$GROSSAMOUNT;
	$GRANDPSTDTOTAL	+=	$POSTEDAMT;
	$GRANDMPOSQTY	+=	$TOTALQTY;
	$GRANDPSTDQTY	+=	$POSTEDQTY;
	$cnt++;
}
	$pdf->SetFont('Times','B',9);
	$pdf->SetX(4);$pdf->Cell(191,5,"TOTAL",1,0,'C');
	$pdf->SetX(195);$pdf->Cell(20,5,number_format($GRANDMPOSQTY),1,0,'C');
	$pdf->SetX(215);$pdf->Cell(20,5,number_format($GRANDPSTDQTY),1,0,'C');
	$pdf->SetX(235);$pdf->Cell(20,5,number_format($GRANDMPOSTOTAL,2),1,0,'R');
	$pdf->SetX(255);$pdf->Cell(20,5,number_format($GRANDPSTDTOTAL,2),1,1,'R');	
	echo $pdf->Output();
?>