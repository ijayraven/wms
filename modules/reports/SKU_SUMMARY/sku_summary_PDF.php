<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}
$txtcustno		=	$_GET["txtcustno"];
$dfrom			=	$_GET["dfrom"];
$dto			=	$_GET["dto"];
$rdoordertype	=	$_GET["rdoordertype"];
if ($txtcustno == "")
{
	$CUST_Q		=	"";
}
else 
{
	$CUST_Q		=	" AND O.CustNumber = '{$txtcustno}'";
}
if($rdoordertype == "")
{
	$TYPE_Q		=	"";
}
else 
{
	$TYPE_Q		=	" AND O.TransType = '{$rdoordertype}'";
}
$getorder	=	"SELECT O.SOFNumber,O.CustNumber,O.TransType, D.GrossAmount,D.NetAmount,D.ReleaseQty,C.CustName
				 FROM FDCRMSlive.ordercycle AS O
				 LEFT JOIN FDCRMSlive.orderdetail AS D ON D.OrderNo = O.SOFNumber
				 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = O.CustNumber
				 WHERE O.Date_C BETWEEN '{$dfrom}' AND '{$dto}' AND D.isDeleted = 'N' $CUST_Q $TYPE_Q
				 ORDER BY C.CustName";
$rsgetorder	=	$Filstar_conn->Execute($getorder);
if($rsgetorder == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
}
else 
{
	if($rsgetorder->RecordCount() > 0)
	{
		$arrOrder	=	array();
		while (!$rsgetorder->EOF) {
			
			$CustNumber		=	$rsgetorder->fields["CustNumber"];
			$GrossAmount	=	$rsgetorder->fields["GrossAmount"];
			$NetAmount		=	$rsgetorder->fields["NetAmount"];
			$ReleaseQty		=	$rsgetorder->fields["ReleaseQty"];
			$TransType		=	$rsgetorder->fields["TransType"];
			$CustName		=	$rsgetorder->fields["CustName"];
			$arrOrder[$CustNumber]["GrossAmount"]	+=	$GrossAmount;
			$arrOrder[$CustNumber]["NetAmount"]		+=	$NetAmount;
			$arrOrder[$CustNumber]["ReleaseQty"]	+=	$ReleaseQty;
			$arrOrder[$CustNumber]["TransType"]		=	$TransType;
			$arrOrder[$CustNumber]["CustName"]		=	$CustName;
			
			$rsgetorder->MoveNext();
		}
		class PDF extends FPDF 
			{
				function Header()
				{
					$dfrom		=	$_GET["dfrom"];
					$dto		=	$_GET["dto"];
					$rdoordertype	=	$_GET["rdoordertype"];
					$this->SetFont('Courier','B',12);
					$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'CONFIRMED DELIVERIES',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'SKU SUMMARY',0,1,'C');
//					if($rdoordertype !="")
//					{
//						$this->SetX(10);$this->Cell(0,5,'ORDER TYPE: '.$rdoordertype,0,1,'C');
//					}
					$this->SetX(10);$this->Cell(0,5,'PERIOD: '.date("F d, Y",strtotime($dfrom))." to ".date("F d, Y",strtotime($dto)),0,1,'C');
					$this->ln(5);
					$this->SetFont('Courier','B',10);
					$this->SetX(10);$this->Cell(85,6,'CUSTOMER',1,0,'C');
					$this->SetX(95);$this->Cell(20,3,'ORDER',"LTR",0,'C');
					$this->SetX(115);$this->Cell(25,3,'TOTAL',"LTR",0,'C');
					$this->SetX(140);$this->Cell(35,3,'TOTAL WHOLESALE',"LTR",0,'C');
					$this->SetX(175);$this->Cell(30,3,'TOTAL GROSS',"LTR",1,'C');
					
					
					$this->SetX(95);$this->Cell(20,3,'TYPE',"LRB",0,'C');
					$this->SetX(115);$this->Cell(25,3,'SKU QTY',"LRB",0,'C');
					$this->SetX(140);$this->Cell(35,3,'AMOUNT',"LRB",0,'C');
					$this->SetX(175);$this->Cell(30,3,'AMOUNT',"LRB",1,'C');
				}
				
				function Footer()
				{
					$this->SetFont('Courier','',9);
					$this->SetY(340);$this->Cell(0,10,'Printed Date  : '.date('Y-m-d'),0,1,'L');
					$this->SetY(343);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
					$this->SetY(343);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
				}
			}
			
			
			$pdf= new PDF('P','mm','legal');
			$pdf->Open();
			$pdf->AliasNbPages();
			$pdf->SetAutoPageBreak('auto',15);
			$pdf->AddPage();
			
			$pdf->SetFont('Courier','',10);
			
			$totsku			=	0;
			$totwholesale	=	0;
			$totgross		=	0;
			foreach ($arrOrder as $custnum=>$val1)
			{
				
				if($rdoordertype == "")
				{
					$TYPE	=	"STF/INV";
				}
				else 
				{
					$TYPE	=	$val1["TransType"];
				}
				$pdf->SetX(10);$pdf->Cell(105,5,$custnum."-".substr($val1["CustName"],0,30),1,0,'L');
				$pdf->SetX(95);$pdf->Cell(20,5,$TYPE,1,0,'L');
				$pdf->SetX(115);$pdf->Cell(25,5,number_format($val1["ReleaseQty"]),1,0,'R');
				$pdf->SetX(140);$pdf->Cell(35,5,number_format($val1["NetAmount"],2),1,0,'R');
				$pdf->SetX(175);$pdf->Cell(30,5,number_format($val1["GrossAmount"],2),1,1,'R');
				$totsku			+=	$val1["ReleaseQty"];
				$totwholesale	+=	$val1["NetAmount"];
				$totgross		+=	$val1["GrossAmount"];
			}$pdf->ln(5);
			$pdf->SetFont('Courier','B',10);
			$pdf->SetX(10);$pdf->Cell(85,15,"TOTAL",1,0,'C');
			$pdf->SetX(95);$pdf->Cell(45,5,"RELEASE QTY",1,0,'L');
			$pdf->SetX(140);$pdf->Cell(65,5,number_format($totsku),1,1,'R');
			$pdf->SetX(95);$pdf->Cell(45,5,"WHOLESALE AMOUNT",1,0,'L');
			$pdf->SetX(140);$pdf->Cell(65,5,number_format($totwholesale,2),1,1,'R');
			$pdf->SetX(95);$pdf->Cell(45,5,"GROSS AMOUNT",1,0,'L');
			$pdf->SetX(140);$pdf->Cell(65,5,number_format($totgross,2),1,1,'R');
			echo $pdf->Output();
	}
	else 
	{
		echo "No records found.";
	}
}
?>