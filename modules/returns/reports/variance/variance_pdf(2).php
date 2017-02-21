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
		$this->SetX(4);$this->Cell(7,8,'No.',1,0,'C');
		$this->SetX(11);$this->Cell(70,8,'Customer',1,0,'C');
		$this->SetX(81);$this->Cell(15,4,'MPOS',"LTR",0,'C');
		$this->SetX(96);$this->Cell(18,4,'MPOS',"LTR",0,'C');
		$this->SetX(114);$this->Cell(18,4,'Posted',"LTR",0,'C');
		$this->SetX(132);$this->Cell(25,8,'Reason',1,0,'C');
		$this->SetX(157);$this->Cell(15,4,'MPOS',"LTR",0,'C');
		$this->SetX(172);$this->Cell(15,4,'Posted',"LTR",0,'C');
		$this->SetX(187);$this->Cell(20,4,'MPOS',"LTR",0,'C');
		$this->SetX(207);$this->Cell(20,4,'Posted',"LTR",0,'C');
		$this->SetX(234);$this->Cell(20,4,'Posted',"LTR",0,'C');
		$this->SetX(254);$this->Cell(20,4,'Variance',"LTR",1,'C');
		
		$this->SetX(81);$this->Cell(15,4,'No.',"LRB",0,'C');
		$this->SetX(96);$this->Cell(18,4,'Date',"LRB",0,'C');
		$this->SetX(114);$this->Cell(18,4,'Date',"LRB",0,'C');
		$this->SetX(157);$this->Cell(15,4,'Qty',"LRB",0,'C');
		$this->SetX(172);$this->Cell(15,4,'Qty',"LRB",0,'C');
		$this->SetX(187);$this->Cell(20,4,'Amt',"LRB",0,'C');
		$this->SetX(207);$this->Cell(20,4,'Amt',"LRB",0,'C');
		$this->SetX(234);$this->Cell(20,4,'Net Amt',"LRB",0,'C');
		$this->SetX(254);$this->Cell(20,4,'(Qty)',"LRB",1,'C');
		
		
		
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
$GRANDPSTDNETTOTAL	=	0;
$GRANDMPOSQTY	=	0;
$GRANDPSTDQTY	=	0;
$GRANDVARIANCEQTY	=	0;
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
	$POSTEDNETAMT	= $val1["POSTEDNETAMT"];
	$VARIANCEQTY	=	$POSTEDQTY - $TOTALQTY;
	$pdf->SetX(4);$pdf->Cell(7,5,$cnt,1,0,'C');
	$pdf->SetX(11);$pdf->Cell(70,5,substr($CUSTNO,0,45),1,0,'L');
	$pdf->SetX(81);$pdf->Cell(15,5,$MPOSNO,1,0,'C');
	$pdf->SetX(96);$pdf->Cell(18,5,$MPOSDATE,1,0,'C');
	$pdf->SetX(114);$pdf->Cell(18,5,$POSTEDDATE,1,0,'C');
	$pdf->SetX(132);$pdf->Cell(25,5,$REASON,1,0,'L');
	$pdf->SetX(157);$pdf->Cell(15,5,number_format($TOTALQTY),1,0,'C');
	$pdf->SetX(172);$pdf->Cell(15,5,number_format($POSTEDQTY),1,0,'C');
	$pdf->SetX(187);$pdf->Cell(20,5,number_format($GROSSAMOUNT,2),1,0,'R');
	$pdf->SetX(207);$pdf->Cell(20,5,number_format($POSTEDAMT,2),1,0,'R');
	$pdf->SetX(234);$pdf->Cell(20,5,number_format($POSTEDNETAMT,2),1,0,'R');
	$pdf->SetX(254);$pdf->Cell(20,5,number_format($VARIANCEQTY),1,1,'C');
	
	$GRANDMPOSTOTAL	+=	$GROSSAMOUNT;
	$GRANDPSTDTOTAL	+=	$POSTEDAMT;
	$GRANDPSTDNETTOTAL	+=	$POSTEDNETAMT;
	$GRANDMPOSQTY	+=	$TOTALQTY;
	$GRANDPSTDQTY	+=	$POSTEDQTY;
	$GRANDVARIANCEQTY+=	abs($VARIANCEQTY);
	$cnt++;
}
	$pdf->SetFont('Times','B',9);
	$pdf->SetX(4);$pdf->Cell(153,5,"TOTAL",1,0,'C');
	$pdf->SetX(157);$pdf->Cell(15,5,number_format($GRANDMPOSQTY),1,0,'C');
	$pdf->SetX(172);$pdf->Cell(15,5,number_format($GRANDPSTDQTY),1,0,'C');
	$pdf->SetX(187);$pdf->Cell(20,5,number_format($GRANDMPOSTOTAL,2),1,0,'R');
	$pdf->SetX(207);$pdf->Cell(20,5,number_format($GRANDPSTDTOTAL,2),1,0,'R');	
	$pdf->SetX(234);$pdf->Cell(20,5,number_format($GRANDPSTDNETTOTAL,2),1,0,'R');	
	$pdf->SetX(254);$pdf->Cell(20,5,number_format(abs($GRANDVARIANCEQTY)),1,1,'C');	
	echo $pdf->Output();
?>