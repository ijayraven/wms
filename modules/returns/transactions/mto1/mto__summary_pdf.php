<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}
$TRXNO	=	$_GET["TRXNO"];


$TODAY	=	date("Y-m-d");
$time	=	date("H:i:s A");
$MTOdate=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTNHDR","POSTDATE","MTONO= '{$TRXNO}'");

$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_RTNDTL WHERE MTONO = '{$TRXNO}'";
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
		
		$MPOSNO				= $RSGETMTO->fields["MPOSNO"]; 
		$NO_OF_BOXES		= $RSGETMTO->fields["NO_OF_BOXES"]; 
		$NO_OF_PACK			= $RSGETMTO->fields["NO_OF_PACK"]; 
		$QTY				= $RSGETMTO->fields["QTY"]; 
		$BOXLABEL			= $RSGETMTO->fields["BOXLABEL"]; 
		
		$arrMTO[$MPOSNO]["BOXLABEL"]		=	$BOXLABEL;
		$arrMTO[$MPOSNO]["NO_OF_BOXES"]		=	$NO_OF_BOXES;
		$arrMTO[$MPOSNO]["NO_OF_PACK"]		=	$NO_OF_PACK;
		$arrMTO[$MPOSNO]["MPOSQTY"]			+=	$QTY;
		
		$RSGETMTO->MoveNext();
	}
}

		class PDF extends FPDF 
			{
				function Header()
				{
					$TRXNO	=	$_GET["TRXNO"];
					global $MTOdate,$destination;
					$this->SetFont('Times','B',12);
					$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'Material Transfer Order for Return Repacking',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'Listing Summary',0,1,'C');
					$this->SetFont('Times','',12);
					$this->SetX(10);$this->Cell(0,5,"M.T.O. Number: $TRXNO",0,1,'C');
					$this->SetX(10);$this->Cell(0,5,"M.T.O. Date: $MTOdate",0,1,'C');

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
			$pdf->SetX(17);$pdf->Cell(40.5,5,'MPOS NO.',1,0,'C');
			$pdf->SetX(57.5);$pdf->Cell(47.5,5,'MPOS QTY',1,0,'C');
			$pdf->SetX(105);$pdf->Cell(47.5,5,'BOX QTY',1,0,'C');
			$pdf->SetX(152.5);$pdf->Cell(47.5,5,'PACKAGE QTY',1,1,'C');
			
			$pdf->SetFont('Times','',12);
			$cnt=1;
			foreach ($arrMTO as $MPOSNO=>$val1)
			{
				$boxes	=	$val1["NO_OF_BOXES"];
				$pack	=	$val1["NO_OF_PACK"];
				$qty	=	$val1["MPOSQTY"];
				if($boxes != 0 || $pack != 0)
				{
					$totboxes	+=	$val1["NO_OF_BOXES"];
					$totpack	+=	$val1["NO_OF_PACK"];
					$totqty		+=	$val1["MPOSQTY"];
					$pdf->SetX(10);$pdf->Cell(7,5,$cnt,1,0,'C');
					$pdf->SetX(17);$pdf->Cell(40.5,5,$MPOSNO,1,0,'C');
					$pdf->SetX(57.5);$pdf->Cell(47.5,5,number_format($qty),1,0,'C');
					$pdf->SetX(105);$pdf->Cell(47.5,5,number_format($boxes),1,0,'C');
					$pdf->SetX(152.5);$pdf->Cell(47.5,5,number_format($pack),1,1,'C');
					$cnt++;
				}
			}
			$pdf->SetFont('Times','B',12);
			$pdf->SetX(10);$pdf->Cell(47.5,5,"TOTAL",1,0,'C');
			$pdf->SetX(57.5);$pdf->Cell(47.5,5,number_format($totqty),1,0,'C');
			$pdf->SetX(105);$pdf->Cell(47.5,5,number_format($totboxes),1,0,'C');
			$pdf->SetX(152.5);$pdf->Cell(47.5,5,number_format($totpack),1,1,'C');
			
			$pdf->ln(5);
			$pdf->SetFont('Times','B',12);
			$pdf->SetX(10);$pdf->Cell(7,5,'NO.',1,0,'C');
			$pdf->SetX(17);$pdf->Cell(56.33,5,'MPOS NO.',1,0,'C');
			$pdf->SetX(73.33);$pdf->Cell(63.33,5,'MPOS QTY',1,0,'C');
			$pdf->SetX(136.663);$pdf->Cell(63.33,5,'BOX LABEL',1,1,'C');
			
			$pdf->SetFont('Times','',12);
			asort($arrMTO);
			$totqty	=	0;
			$totboxlabel	=	0;
			foreach ($arrMTO as $MPOSNO=>$val1)
			{
				$boxlabel	=	$val1["BOXLABEL"];
				$qty		=	$val1["MPOSQTY"];
				if($boxlabel != "")
				{
					$totqty	+=	$qty;
					$totboxlabel	+=	$boxlabel;
					$pdf->SetX(10);$pdf->Cell(7,5,$cnt,1,0,'C');
					$pdf->SetX(17);$pdf->Cell(56.33,5,$MPOSNO,1,0,'C');
					$pdf->SetX(73.33);$pdf->Cell(63.33,5,number_format($qty),1,0,'C');
					$pdf->SetX(136.663);$pdf->Cell(63.33,5,number_format($boxlabel),1,1,'C');
					$cnt++;
				}
			}
			$pdf->SetFont('Times','B',12);
			$pdf->SetX(10);$pdf->Cell(63.33,5,"TOTAL",1,0,'C');
			$pdf->SetX(73.33);$pdf->Cell(63.33,5,number_format($totqty),1,0,'C');
			$pdf->SetX(136.663);$pdf->Cell(63.33,5,"",1,1,'C');
			echo $pdf->Output();
?>