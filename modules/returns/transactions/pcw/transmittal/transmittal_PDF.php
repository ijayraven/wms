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
		$TRANSNO	= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RAWHDR","TRANSNO","MTONO= '{$TRXNO}'");
		$ISSUEDBY	= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RAWHDR","ISSUEDBY","MTONO= '{$TRXNO}'");
		$ISSUEDBY	= 	$global_func->Select_val($Filstar_conn,"WMS_USERS ","USERS","NAME","USERNAME= '{$ISSUEDBY}'");
		$ISSUEDDATE	= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RAWHDR","ISSUEDDATE","MTONO= '{$TRXNO}'");
		
		$this->SetFont('Times','B',12);
		$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'FILLING BIN ISSUANCE FORM',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'FROM RAW',0,1,'C');
		
		$path =  "temp/";
		Generate_Barcode_Image($TRANSNO, $path, "$TRANSNO.png");
		$this->Image("$path$TRANSNO.png",150,10,50,10);
		unlink("$path$TRANSNO.png");

		$this->image("fdc_ls_col.jpg",10,10,40,20);
		
		$this->ln(10);
		$this->SetFont('Times','B',12);
		
		$this->SetX(10);$this->Cell(40,5,'FIF NO.',0,0,'L');		$this->SetX(30);$this->Cell(50,5,":$TRANSNO",0,0,'L');
		$this->SetX(110);$this->Cell(40,5,'ISSUED DATE',0,0,'L');	$this->SetX(140);$this->Cell(40,5,":$ISSUEDDATE",0,1,'L');
		$this->SetX(10);$this->Cell(40,5,'MTO NO.',0,0,'L');		$this->SetX(30);$this->Cell(50,5,":$TRXNO",0,0,'L');
		$this->SetX(110);$this->Cell(40,5,'ISSUED BY',0,0,'L');		$this->SetX(140);$this->Cell(40,5,":$ISSUEDBY",0,1,'L');
		$this->Ln(10);
		
		$this->SetX(10);$this->Cell(20,5,'LINE NO.',1,0,'C');	
		$this->SetX(30);$this->Cell(30,5,'SKU NO.',1,0,'C');	
		$this->SetX(60);$this->Cell(110,5,'DESCRIPTION',1,0,'C');	
		$this->SetX(170);$this->Cell(30,5,'TOTAL QTY',1,1,'C');	
	}
	function Footer()
	{
		$this->SetFont('Times','',12);
		$this->SetY(276);$this->Cell(0,10,'Printed Date : '.date('Y-m-d H:i A'),0,1,'L');
		$this->SetY(280);$this->Cell(0,10,'Printed By	: '.$_SESSION['username'],0,0,'L');
		$this->SetY(285);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}

$pdf= new PDF('P','mm','A4');
$pdf->Open();
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak('auto',15);
$pdf->AddPage();

$MTONO	=	$_GET["MTONO"];
$TODAY	=	date("Y-m-d");
$time	=	date("H:i:s A");

$PRINTMTO	=	"UPDATE WMS_NEW.MTO_RAWHDR SET `STATUS` = 'PRINTED',`PRINTBY`='{$_SESSION['username']}', `PRINTDATE`='{$TODAY}', `PRINTTIME`='{$time}'
				 WHERE `MTONO` = '{$MTONO}'";
$RSPRINTMTO	=	$Filstar_conn->Execute($PRINTMTO);
if($RSPRINTMTO == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
}
$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_RAWDTL WHERE MTONO = '{$MTONO}'";
$RSGETMTO	=	$Filstar_conn->Execute($GETMTO);
if($RSGETMTO == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__;
	exit();
}
else 
{
	$pdf->SetFont('Times','',12);
	$cnt		=	1;
	$TOTALQTY	=	0;
	while (!$RSGETMTO->EOF) {
		
		$SKUNO		= $RSGETMTO->fields["SKUNO"]; 
		$DESCRIPTION= $RSGETMTO->fields["DESCRIPTION"]; 
		$GOODQTY	= $RSGETMTO->fields["GOODQTY"]; 
		
		$pdf->SetX(10);$pdf->Cell(20,5,$cnt,1,0,'C');	
		$pdf->SetX(30);$pdf->Cell(30,5,$SKUNO,1,0,'C');	
		$pdf->SetX(60);$pdf->Cell(110,5,$DESCRIPTION,1,0,'L');	
		$pdf->SetX(170);$pdf->Cell(30,5,$GOODQTY,1,1,'C');	
		$TOTALQTY	+=	$GOODQTY;
		$cnt++;
		$RSGETMTO->MoveNext();
	}
	$pdf->SetFont('Times','B',12);
	$pdf->SetX(10);$pdf->Cell(160,5,"TOTAL",1,0,'C');	
	$pdf->SetX(170);$pdf->Cell(30,5,number_format($TOTALQTY),1,1,'C');	
	
	$pdf->ln(130);
	$pdf->SetX(10);$pdf->Cell(30,5,"RECEIVED BY : ",0,0,'L');
	$pdf->SetX(40);$pdf->Cell(50,5,"_________________________________",0,1,'L');
	$pdf->SetX(50);$pdf->Cell(50,5,"Signature Over Printed Name",0,1,'L');
	$pdf->ln(5);
	$pdf->SetX(10);$pdf->Cell(30,5,"RECEIVED DATE : ",0,0,'L');
	$pdf->SetX(45);$pdf->Cell(50,5,"_______________________________",0,1,'L');
	echo $pdf->Output();
}
	
?>