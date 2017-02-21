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
		$TRXNO		=	$_GET["MTONO"];
		
		$this->SetFont('Times','B',12);
		$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'MTO CONFIRMATION RECEIPT',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'FILLING BIN SECTION',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'MTO NO.: '.$TRXNO,0,1,'C');
		
//		$path =  "temp/";
//		Generate_Barcode_Image($TRANSNO, $path, "$TRANSNO.png");
//		$this->Image("$path$TRANSNO.png",150,10,50,10);
//		unlink("$path$TRANSNO.png");

		$this->image("fdc_ls_col.jpg",10,10,40,20);
		
		$this->ln(10);
		$this->SetFont('Times','B',10);
		$this->SetX(10);$this->Cell(10,5,'NO.',1,0,'C');	
		$this->SetX(20);$this->Cell(40,5,'BRAND.',1,0,'C');	
		$this->SetX(60);$this->Cell(20,5,'SKU NO.',1,0,'C');	
		$this->SetX(80);$this->Cell(70,5,'DESCRIPTION',1,0,'C');	
		$this->SetX(150);$this->Cell(15,5,'SRP',1,0,'C');	
		$this->SetX(165);$this->Cell(15,5,'QTY',1,0,'C');	
		$this->SetX(180);$this->Cell(20,5,'AMOUNT',1,0,'C');	
		$this->SetX(200);$this->Cell(20,5,'LOCATION',1,0,'C');	
		$this->SetX(220);$this->Cell(30,5,'CONFIMRED BY',1,0,'C');	
		$this->SetX(250);$this->Cell(35,5,'CONFIMRED DATE',1,1,'C');	
	}
	function Footer()
	{
		$this->SetFont('Times','',10);
		$this->SetY(190);$this->Cell(0,5,'Printed By	: '.$_SESSION['username'],0,0,'L');
		$this->SetY(195);$this->Cell(0,5,'Printed Date : '.date('Y-m-d H:i A'),0,1,'L');
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

$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_FILLINGBINDTL WHERE MTONO = '{$MTONO}'";
$RSGETMTO	=	$Filstar_conn->Execute($GETMTO);
if($RSGETMTO == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__;
	exit();
}
else 
{
	$pdf->SetFont('Times','',10);
	$cnt		=	1;
	$TOTALQTY	=	0;
	$TOTALAMT	=	0;
	while (!$RSGETMTO->EOF) {
		
		$SKUNO		= $RSGETMTO->fields["SKUNO"]; 
		$BRANDID	= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","Brand","ItemNo= '{$SKUNO}'");
		$BRANDDESC	= $global_func->Select_val($Filstar_pms,"FDC_PMS","BRAND_NEW","BRAND_NAME","BRAND_ID= '{$BRANDID}'");
		$DESCRIPTION= $RSGETMTO->fields["DESCRIPTION"]; 
		$GOODQTY	= $RSGETMTO->fields["GOODQTY"]; 
		$UNITPRICE	= $RSGETMTO->fields["UNITPRICE"]; 
		$GROSSAMT	= $RSGETMTO->fields["GROSSAMT"]; 
		$LOCATION	= $RSGETMTO->fields["LOCATION"]; 
		$CONFIRMEDBY= $RSGETMTO->fields["CONFIRMEDBY"]; 
		$CONFIRMEDDATE= $RSGETMTO->fields["CONFIRMEDDATE"]; 
		
		$pdf->SetX(10);$pdf->Cell(10,5,$cnt,1,0,'C');	
		$pdf->SetX(20);$pdf->Cell(40,5,$BRANDDESC,1,0,'C');	
		$pdf->SetX(60);$pdf->Cell(20,5,$SKUNO,1,0,'C');	
		$pdf->SetX(80);$pdf->Cell(70,5,$DESCRIPTION,1,0,'L');	
		$pdf->SetX(150);$pdf->Cell(15,5,$UNITPRICE,1,0,'C');	
		$pdf->SetX(165);$pdf->Cell(15,5,$GOODQTY,1,0,'C');	
		$pdf->SetX(180);$pdf->Cell(20,5,number_format($GROSSAMT,2),1,0,'R');	
		$pdf->SetX(200);$pdf->Cell(20,5,$LOCATION,1,0,'C');	
		$pdf->SetX(220);$pdf->Cell(30,5,$CONFIRMEDBY,1,0,'C');	
		$pdf->SetX(250);$pdf->Cell(35,5,$CONFIRMEDDATE,1,1,'C');	
		$TOTALQTY	+=	$GOODQTY;
		$TOTALAMT	+=	$GROSSAMT;
		$cnt++;
		$RSGETMTO->MoveNext();
	}
	$pdf->SetFont('Times','B',12);
	$pdf->SetX(10);$pdf->Cell(155,5,"TOTAL",1,0,'C');	
	$pdf->SetX(165);$pdf->Cell(15,5,number_format($TOTALQTY),1,0,'C');	
	$pdf->SetX(180);$pdf->Cell(20,5,number_format($TOTALAMT,2),1,0,'R');	
	$pdf->SetX(200);$pdf->Cell(85,5,"",1,1,'R');	
	echo $pdf->Output();
}
	
?>