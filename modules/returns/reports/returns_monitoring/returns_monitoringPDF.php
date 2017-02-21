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
		$this->SetX(10);$this->Cell(0,5,'RETURNS MONITORING - CREATED MPOS',0,1,'C');
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
		$this->ln(5);
		
		$this->SetFont('Times','B',13);
		$this->SetX(10);$this->Cell(25,5,'REASON ',0,0,'L');
		$this->SetX(35);$this->Cell(50,5,': '.$_SESSION["REASON"],0,0,'L');
		$this->SetX(193);$this->Cell(25,5,'CLASS ',0,0,'L');
		$this->SetX(213);$this->Cell(50,5,': '.$_SESSION["CLASS"],0,1,'L');
		$this->SetX(10);$this->Cell(30,5,'BRAND ',0,0,'L');
		$this->SetX(35);$this->Cell(50,5,': '.$_SESSION["BRAND"],0,1,'L');
		//$this->ln(5);
		$this->SetFont('Times','B',10);
		$this->SetX(5);$this->Cell(10,5,'LINE',1,0,'C');
		$this->SetX(15);$this->Cell(35,5,'TRANSACTION',1,0,'C');
		$this->SetX(50);$this->Cell(70,5,'CUSTOMER',1,0,'C');
		$this->SetX(120);$this->Cell(20,5,'MPOS NO.',1,0,'C');
		$this->SetX(140);$this->Cell(25,5,'MPOS DATE',1,0,'C');
		$this->SetX(165);$this->Cell(28,5,'TABLET DATE',1,0,'C');
		$this->SetX(193);$this->Cell(20,5,'QUANTITY',1,0,'C');
		$this->SetX(213);$this->Cell(30,5,'GROSS AMOUNT',1,0,'C');
		$this->SetX(243);$this->Cell(25,5,'NET AMOUNT',1,0,'C');
		$this->Ln(5);
	}
	
	function Footer()
	{
		$this->SetFont('Courier','',9);
		$this->SetFont('Times','',9);
		$this->SetY(197);$this->Cell(0,10,'Printed BY    : '.$_SESSION['NAME'],0,1,'L');
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

$arrMpos	=	$_SESSION["arrMPOS"];
$cnt		=	1;
$GRANDTOTAL	=	0;
$GRANDNET	=	0;
$GRANDQTY	=	0;
	foreach ($arrMpos as $custno=>$val1)
	{
		foreach ($val1 as $trxno=>$val2)
		{
			foreach ($val2 as $mpos=>$val3)
			{
				foreach ($val3 as $brand=>$val4)
				{
					foreach ($val4 as $class=>$val5)
					{
						$CustName	=	$val5["CustName"];
						$MPOSDATE	=	$val5["MPOSDATE"];
						$SCANDATE	=	$val5["SCANDATE"];
						$GROSSAMOUNT=	$val5["GROSSAMOUNT"];
						$NETAMOUNT	=	$val5["NETAMOUNT"];
						$BRANDNAME	=	$val5["BRANDNAME"];
						$CLASS_		=	$class;
						$STATUS		=	$val5["STATUS"];
						$REASON		=	$val5["REASON"];
						$TOTALQTY	=	$val5["TOTALQTY"];
						
						$tablet_date=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","SCANDATE","TRANSNO = '{$trxno}' and MPOSNO = '{$mpos}' ");
						
						$pdf->SetX(5);$pdf->Cell(10,5,$cnt,1,0,'C');
						$pdf->SetX(15);$pdf->Cell(35,5,$trxno,1,0,'C');
						$pdf->SetX(50);$pdf->Cell(70,5,substr($CustName,0,45),1,0,'L');
						$pdf->SetX(120);$pdf->Cell(20,5,$mpos,1,0,'C');
						$pdf->SetX(140);$pdf->Cell(25,5,$MPOSDATE,1,0,'C');
						$pdf->SetX(165);$pdf->Cell(28,5,$tablet_date,1,0,'C');
						$pdf->SetX(193);$pdf->Cell(20,5,$TOTALQTY,1,0,'C');
						$pdf->SetX(213);$pdf->Cell(30,5,number_format($GROSSAMOUNT,2),1,0,'R');
						$pdf->SetX(243);$pdf->Cell(25,5,number_format($NETAMOUNT,2),1,1,'R');
						
						$GRANDTOTAL	+=	$GROSSAMOUNT;
						$GRANDNET	+=	$NETAMOUNT;
						$GRANDQTY	+=	$TOTALQTY;
						$cnt++;
					}
				}
			}
		}
	}
$pdf->SetFont('Times','B',9);
$pdf->SetX(5);$pdf->Cell(238,5,"GRAND TOTAL",1,0,'C');
$pdf->SetX(193);$pdf->Cell(20,5,$GRANDQTY,1,0,'C');
$pdf->SetX(213);$pdf->Cell(30,5,number_format($GRANDTOTAL,2),1,0,'R');
$pdf->SetX(243);$pdf->Cell(25,5,number_format($GRANDNET,2),1,1,'R');
echo $pdf->Output();
?>