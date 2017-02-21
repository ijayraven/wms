<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms'</script>";
}

class PDF extends FPDF 
{
	function Header()
	{
		global $global_func,$Filstar_conn;
		$TRXNO		=	$_GET["MTONO"];
		$TRANSNO	= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_IB_HDR","TRANSNO","MTONO= '{$TRXNO}'");
		$PCWORKER	= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_IB_HDR","PCWORKER","MTONO= '{$TRXNO}'");
		$PCWORKERD	= 	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","PIECEWORKER","DESCRIPTION","RECID= '{$PCWORKER}'");
		$NOSTREET	= 	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","PIECEWORKER","NOSTREET","RECID= '{$PCWORKER}'");
		$ZIP		= 	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","PIECEWORKER","ZIP","RECID= '{$PCWORKER}'");
		$BRGY		= 	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","PIECEWORKER","BRGY","RECID= '{$PCWORKER}'");
		$CITY		= 	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","PIECEWORKER","CITY","RECID= '{$PCWORKER}'");
		$PROVINCE	= 	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","PIECEWORKER","PROVINCE","RECID= '{$PCWORKER}'");
		$ISSUEDBY	= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_IB_HDR","TRANSMITTED_BY","MTONO= '{$TRXNO}'");
		$ISSUEDBY	= 	$global_func->Select_val($Filstar_conn,"WMS_USERS ","USERS","NAME","USERNAME= '{$ISSUEDBY}'");
		$ISSUEDDATE	= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_IB_HDR","TRANSMITTED_DT","MTONO= '{$TRXNO}'");
		
		$this->SetFont('Times','B',12);
		$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'PIECEWORK ISSUANCE FORM',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'FROM INTERNAL BARCODE',0,1,'C');
		
		$path =  "temp/";
		Generate_Barcode_Image($TRANSNO, $path, "$TRANSNO.png");
		$this->Image("$path$TRANSNO.png",225,10,60,10);
		unlink("$path$TRANSNO.png");

		$this->image("fdc_ls_col.jpg",10,10,40,20);
		
		$this->ln(10);
		$this->SetFont('Times','B',12);
		
		$this->SetX(10);$this->Cell(40,5,'PIF NO.',0,0,'L');		$this->SetX(50);$this->Cell(50,5,":$TRANSNO",0,0,'L');
		$this->SetX(200);$this->Cell(40,5,'ISSUED DATE',0,0,'L');	$this->SetX(230);$this->Cell(40,5,":$ISSUEDDATE",0,1,'L');
		$this->SetX(10);$this->Cell(40,5,'PIECEWORKER',0,0,'L');	$this->SetX(50);$this->Cell(50,5,":$PCWORKERD",0,0,'L');
		$this->SetX(200);$this->Cell(40,5,'ISSUED BY',0,0,'L');		$this->SetX(230);$this->Cell(40,5,":$ISSUEDBY",0,1,'L');
		$this->SetX(10);$this->Cell(40,5,'ADDRESS',0,0,'L');		$this->SetX(50);$this->Cell(50,5,":$NOSTREET, $BRGY, $CITY, $PROVINCE $ZIP",0,0,'L');
		$this->Ln(10);
		
		$this->SetX(10);$this->Cell(68.75,5,'MTO NO.',1,0,'C');	
		$this->SetX(78.75);$this->Cell(68.75,5,'ARS NO.',1,0,'C');	
		$this->SetX(147.5);$this->Cell(68.75,5,'TOTAL QUANTITY',1,0,'C');	
		$this->SetX(216.25);$this->Cell(68.75,5,'GROSS AMOUNT',1,1,'C');	
	}
	function Footer()
	{
		$this->SetFont('Times','',12);
		$this->SetY(194);$this->Cell(0,10,'Printed Date : '.date('Y-m-d H:i A'),0,1,'L');
		$this->SetY(198);$this->Cell(0,10,'Printed By	: '.$_SESSION['username'],0,0,'L');
		$this->SetY(200);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}

$pdf= new PDF('L','mm','A4');
$pdf->Open();
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak('auto',15);
$pdf->AddPage();

$MTONO	=	$_GET["MTONO"];
$TODAY	=	date("Y-m-d");
$time	=	date("H:i:s A");

$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_IB_HDR WHERE MTONO = '{$MTONO}'";
$RSGETMTO	=	$Filstar_conn->Execute($GETMTO);
if($RSGETMTO == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__;
	exit();
}
else 
{
	$pdf->SetFont('Times','',12);
	while (!$RSGETMTO->EOF) {
		
		$MTONO 		= $RSGETMTO->fields["MTONO"]; 
		$ARSNO 		= $RSGETMTO->fields["ARSNO"]; 
		$TOTALQTY	= $global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_IB_DTL","SUM(QTY)","MTONO= '{$MTONO}'");
		$TOTALGROSS	= $global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_IB_DTL","SUM(GROSSAMT)","MTONO= '{$MTONO}'");
		$pdf->SetX(10);$pdf->Cell(68.75,5,$MTONO,1,0,'C');	
		$pdf->SetX(78.75);$pdf->Cell(68.75,5,$ARSNO,1,0,'C');	
		$pdf->SetX(147.5);$pdf->Cell(68.75,5,number_format($TOTALQTY),1,0,'C');	
		$pdf->SetX(216.25);$pdf->Cell(68.75,5,number_format($TOTALGROSS,2),1,0,'C');	
		$RSGETMTO->MoveNext();
	}
	
	$pdf->ln(50);
	$pdf->SetX(10);$pdf->Cell(30,5,"RECEIVED BY : ",0,0,'L');
	$pdf->SetX(40);$pdf->Cell(50,5,"_________________________________",0,1,'L');
	$pdf->SetX(50);$pdf->Cell(50,5,"Signature Over Printed Name",0,1,'L');
	$pdf->ln(5);
	$pdf->SetX(10);$pdf->Cell(30,5,"RECEIVED DATE : ",0,0,'L');
	$pdf->SetX(45);$pdf->Cell(50,5,"_______________________________",0,1,'L');
	echo $pdf->Output();
}
	
?>