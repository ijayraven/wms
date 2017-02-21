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
		$this->SetX(10);$this->Cell(0,5,'RETURNS MONITORING',0,1,'C');
		if($_SESSION["mposdfrom"] != "")
		{
			$this->SetX(10);$this->Cell(0,5,'MPOS DATE: '.$_SESSION["mposdfrom"]." to ".$_SESSION["mposdto"],0,1,'C');
		}
		else 
		{
			$this->SetX(10);$this->Cell(0,5,'',0,1,'C');
		}
		if($_SESSION["scandfrom"] != "")
		{
			$this->SetX(10);$this->Cell(0,5,'SCAN DATE: '.$_SESSION["scandfrom"]." to ".$_SESSION["scandto"],0,1,'C');
		}
		else 
		{
			$this->SetX(10);$this->Cell(0,5,'',0,1,'C');
		}
		$this->image("fdc_ls_col.jpg",10,10,40,20);
		$this->ln(10);
		
		$this->SetFont('Times','B',10);
		$this->SetX(5);$this->Cell(10,8,'LINE',1,0,'C');
		$this->SetX(15);$this->Cell(35,4,'TRANSACTION',"LTR",0,'C');
		$this->SetX(50);$this->Cell(70,8,'CUSTOMER',1,0,'C');
		$this->SetX(120);$this->Cell(20,4,'MPOS',"LTR",0,'C');
		$this->SetX(140);$this->Cell(20,4,'MPOS',"LTR",0,'C');
		$this->SetX(160);$this->Cell(20,4,'SCAN',"LTR",0,'C');
		$this->SetX(180);$this->Cell(36,8,'STATUS',1,0,'C');
		$this->SetX(216);$this->Cell(15,8,'SKU#',1,0,'C');
		$this->SetX(231);$this->Cell(15,8,'QTY',1,0,'C');
		$this->SetX(246);$this->Cell(20,4,'GROSS',"LTR",0,'C');
		$this->SetX(266);$this->Cell(30,8,'BRAND',1,0,'C');
		$this->SetX(296);$this->Cell(53,8,'CLASS',1,0,'C');
		
		
		$this->SetXY(15,44);$this->Cell(35,4,'NO.',"LRB",0,'C');
		$this->SetX(120,44);$this->Cell(20,4,'NO.',"LRB",0,'C');
		$this->SetX(140,44);$this->Cell(20,4,'DATE',"LRB",0,'C');
		$this->SetX(160,44);$this->Cell(20,4,'DATE',"LRB",0,'C');
		$this->SetX(246,44);$this->Cell(20,4,'AMOUNT',"LRB",0,'C');
		$this->Ln(4);
	}
	
	function Footer()
	{
		$this->SetFont('Courier','',9);
		$this->SetFont('Times','',9);
		$this->SetY(200);$this->Cell(0,10,'Printed Date  : '.date('Y-m-d'),0,1,'L');
		$this->SetY(203);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
		$this->SetY(203);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
			
			
$pdf= new PDF('L','mm','Legal');
$pdf->Open();
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak('auto',15);
$pdf->AddPage();
$pdf->SetFont('Times','',9);

$arrMpos	=	$_SESSION["arrMPOS"];
$cnt		=	1;
$GRANDTOTAL	=	0;
$GRANDQTY	=	0;
	foreach ($arrMpos as $custno=>$val1)
	{
		foreach ($val1 as $trxno=>$val2)
		{
			foreach ($val2 as $mpos=>$val3)
			{
				foreach ($val3 as $brand=>$val4)
				{
					foreach ($val4 as $sku=>$val5)
					{
						$CustName	=	$val5["CustName"];
						$MPOSDATE	=	$val5["MPOSDATE"];
						$SCANDATE	=	$val5["SCANDATE"];
						$GROSSAMOUNT=	$val5["GROSSAMOUNT"];
						$BRANDNAME	=	$val5["BRANDNAME"];
						$TOTALQTY	=	$val5["TOTALQTY"];
						$STATUS		=	$val5["STATUS"];
						$CLASS		=	$val5["CLASS"];
						$SKUNO		=	$val5["SKUNO"];
						
						$pdf->SetX(5);$pdf->Cell(10,8,$cnt,1,0,'C');
						$pdf->SetX(15);$pdf->Cell(35,8,$trxno,1,0,'C');
						$pdf->SetX(50);$pdf->Cell(70,8,$CustName,1,0,'L');
						$pdf->SetX(120);$pdf->Cell(20,8,$mpos,1,0,'C');
						$pdf->SetX(140);$pdf->Cell(20,8,$MPOSDATE,1,0,'C');
						$pdf->SetX(160);$pdf->Cell(20,8,$SCANDATE,1,0,'C');
						$pdf->SetX(180);$pdf->Cell(36,8,$STATUS,1,0,'C');
						$pdf->SetX(216);$pdf->Cell(15,8,$SKUNO,1,0,'C');
						$pdf->SetX(231);$pdf->Cell(15,8,number_format($TOTALQTY),1,0,'C');
						$pdf->SetX(246);$pdf->Cell(20,8,number_format($GROSSAMOUNT,2),1,0,'R');
						$pdf->SetX(266);$pdf->Cell(30,8,$BRANDNAME,1,0,'C');
						$pdf->SetX(296);$pdf->Cell(53,8,$CLASS,1,1,'C');
	
						$GRANDTOTAL	+=	$GROSSAMOUNT;
						$GRANDQTY	+=	$TOTALQTY;
						$cnt++;
					}
				}
			}
		}
	}
$pdf->SetFont('Times','B',9);
$pdf->SetX(5);$pdf->Cell(344,5,"GRAND TOTAL",1,0,'C');
$pdf->SetX(231);$pdf->Cell(15,5,number_format($GRANDQTY),1,0,'C');
$pdf->SetX(246);$pdf->Cell(20,5,number_format($GRANDTOTAL,2),1,1,'R');
echo $pdf->Output();
?>