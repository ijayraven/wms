<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms'</script>";
}
$TRXNO	=	$_GET["TRXNO"];

$TODAY	=	date("Y-m-d");
$time	=	date("H:i:s A");
$MTOdate=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTN_EXCHDR","POSTDATE","MTONO= '{$TRXNO}'");

$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_RTN_EXCDTL WHERE MTONO = '{$TRXNO}'";
$RSGETMTO	=	$Filstar_conn->Execute($GETMTO);
if($RSGETMTO == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__;
	exit();
}
else 
{
	$arrMTO	=	array();
	while (!$RSGETMTO->EOF) {
		
		$SKUNO				= $RSGETMTO->fields["SKUNO"]; 
		$DESCRIPTION		= $RSGETMTO->fields["DESCRIPTION"]; 
		$NO_OF_BOXES		= $RSGETMTO->fields["NO_OF_BOXES"]; 
		$NO_OF_PACK			= $RSGETMTO->fields["NO_OF_PACK"]; 
		$QTY				= $RSGETMTO->fields["QTY"]; 
		$BOXLABEL			= $RSGETMTO->fields["BOXLABEL"]; 
		
		$arrMTO[$SKUNO]["BOXLABEL"]			=	$BOXLABEL;
		$arrMTO[$SKUNO]["DESCRIPTION"]		=	$DESCRIPTION;
		$arrMTO[$SKUNO]["NO_OF_BOXES"]		=	$NO_OF_BOXES;
		$arrMTO[$SKUNO]["NO_OF_PACK"]		=	$NO_OF_PACK;
		$arrMTO[$SKUNO]["SKUQTY"]			+=	$QTY;
		
		$RSGETMTO->MoveNext();
	}
}

		class PDF extends FPDF 
			{
				function Header()
				{
					global $MTOdate,$destination,$global_func,$Filstar_conn;
					$TRXNO	=	$_GET["TRXNO"];
					$boxqty	=	__Global_Func::Select_val($Filstar_conn,"WMS_NEW","MTO_RTN_EXCDTL","NO_OF_BOXES","MTONO= '{$TRXNO}'");
					$pkgqty	=	__Global_Func::Select_val($Filstar_conn,"WMS_NEW","MTO_RTN_EXCDTL","NO_OF_PACK","MTONO= '{$TRXNO}'");
					
					
					$this->SetFont('Times','B',12);
					$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'Material Transfer Order for Return Repacking(Exclusive)',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'Listing Summary',0,1,'C');
					$this->SetFont('Times','',12);
					$this->SetX(10);$this->Cell(0,5,"M.T.O. Number: $TRXNO",0,1,'C');
					$this->SetX(10);$this->Cell(0,5,"M.T.O. Date: $MTOdate",0,1,'C');
					$this->SetX(10);$this->Cell(0,5,"BOX QTY: $boxqty",0,1,'C');
					$this->SetX(10);$this->Cell(0,5,"PACKAGE QTY: $pkgqty",0,1,'C');

					$this->ln(5);
				}
				function Footer()
				{
					$this->SetFont('Times','',12);
					$this->SetY(278);$this->Cell(0,10,'Printed Date : '.date('Y-m-d H:i A'),0,1,'L');
					$this->SetY(282);$this->Cell(0,10,'Printed By	: '.$_SESSION['username'],0,0,'L');
					$this->SetY(278);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
				}
			}
			$pdf= new PDF('P','mm','A4');
			$pdf->Open();
			$pdf->AliasNbPages();
			$pdf->SetAutoPageBreak('auto',15);
			$pdf->AddPage();
			
			$pdf->SetFont('Times','B',12);
			$pdf->SetX(10);$pdf->Cell(7,5,'NO.',1,0,'C');
			$pdf->SetX(17);$pdf->Cell(20,5,'SKU NO.',1,0,'C');
			$pdf->SetX(37);$pdf->Cell(123,5,'SKU DESCRIPTION',1,0,'C');
			$pdf->SetX(160);$pdf->Cell(40,5,'QTY',1,1,'C');
			
			$pdf->SetFont('Times','',12);
			$cnt=1;
			foreach ($arrMTO as $SKUNO=>$val1)
			{
				$DESCRIPTION	=	$val1["DESCRIPTION"];
				$boxes	=	$val1["NO_OF_BOXES"];
				$pack	=	$val1["NO_OF_PACK"];
				$qty	=	$val1["SKUQTY"];
				if($boxes != 0 || $pack != 0)
				{
					$totboxes	+=	$val1["NO_OF_BOXES"];
					$totpack	+=	$val1["NO_OF_PACK"];
					$totqty		+=	$val1["SKUQTY"];
					$pdf->SetX(10);$pdf->Cell(7,5,$cnt,1,0,'C');
					$pdf->SetX(17);$pdf->Cell(20,5,$SKUNO,1,0,'C');
					$pdf->SetX(37);$pdf->Cell(123,5,$DESCRIPTION,1,0,'L');
					$pdf->SetX(160);$pdf->Cell(40,5,number_format($val1["SKUQTY"]),1,1,'C');
					$cnt++;
				}
			}
			$pdf->SetFont('Times','B',12);
			$pdf->SetX(10);$pdf->Cell(150,5,"TOTAL",1,0,'C');
//			$pdf->SetX(120);$pdf->Cell(20,5,number_format($totqty),1,0,'C');
//			$pdf->SetX(140);$pdf->Cell(20,5,number_format($totboxes),1,0,'C');
			$pdf->SetX(160);$pdf->Cell(40,5,number_format($totqty),1,1,'C');
			
//			$pdf->ln(5);
//			$pdf->SetFont('Times','B',12);
//			$pdf->SetX(10);$pdf->Cell(7,5,'NO.',1,0,'C');
//			$pdf->SetX(17);$pdf->Cell(20,5,'SKU NO.',1,0,'C');
//			$pdf->SetX(37);$pdf->Cell(103,5,'DESCRIPTION',1,0,'C');
//			$pdf->SetX(140);$pdf->Cell(20,5,'SKU QTY',1,0,'C');
//			$pdf->SetX(160);$pdf->Cell(40,5,'BOX LABEL',1,1,'C');
//			
//			$pdf->SetFont('Times','',12);
//			asort($arrMTO);
//			$totqty	=	0;
//			$totboxlabel	=	0;
//			foreach ($arrMTO as $SKUNO=>$val1)
//			{
//				$boxlabel	=	$val1["BOXLABEL"];
//				$qty		=	$val1["SKUQTY"];
//				$DESCRIPTION	=	$val1["DESCRIPTION"];
//				if($boxlabel != "")
//				{
//					$totqty	+=	$qty;
//					$totboxlabel	+=	$boxlabel;
//					$pdf->SetX(10);$pdf->Cell(7,5,$cnt,1,0,'C');
//					$pdf->SetX(17);$pdf->Cell(20,5,$SKUNO,1,0,'C');
//					$pdf->SetX(37);$pdf->Cell(103,5,$DESCRIPTION,1,0,'C');
//					$pdf->SetX(140);$pdf->Cell(20,5,number_format($qty),1,0,'C');
//					$pdf->SetX(160);$pdf->Cell(40,5,number_format($boxlabel),1,1,'C');
//					$cnt++;
//				}
//			}
//			$pdf->SetFont('Times','B',12);
//			$pdf->SetX(10);$pdf->Cell(130,5,"TOTAL",1,0,'C');
//			$pdf->SetX(140);$pdf->Cell(20,5,number_format($totqty),1,0,'C');
//			$pdf->SetX(160);$pdf->Cell(40,5,"",1,1,'C');
			echo $pdf->Output();
?>