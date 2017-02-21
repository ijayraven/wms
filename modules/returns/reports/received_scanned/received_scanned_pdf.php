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
		if($_SESSION["rcvddfrom"] != "")
		{
			$this->SetX(10);$this->Cell(0,5,'RECEIVED DATE: '.$_SESSION["rcvddfrom"]." to ".$_SESSION["rcvddto"],0,1,'C');
		}
		if($_SESSION["scandfrom"] != "")
		{
			$this->SetX(10);$this->Cell(0,5,'SCAN DATE: '.$_SESSION["scandfrom"]." to ".$_SESSION["scandto"],0,1,'C');
		}
		$this->image("fdc_ls_col.jpg",10,10,40,20);
		$this->ln(13);
		
		$this->SetFont('Times','B',10);
		$this->SetX(5);$this->Cell(10,8,'No.',1,0,'C');
		$this->SetX(15);$this->Cell(85,8,'CUSTOMER',1,0,'C');
		$this->SetX(100);$this->Cell(15,4,'MPOS',"LTR",0,'C');
		$this->SetX(115);$this->Cell(30,4,'MPOS',"LTR",0,'C');
		$this->SetX(145);$this->Cell(20,4,'MPOS',"LTR",0,'C');
		$this->SetX(165	);$this->Cell(20,4,'SCAN',"LTR",0,'C');
		$this->SetX(185);$this->Cell(42,8,'STATUS',1,0,'C');
		$this->SetX(227);$this->Cell(12,8,'QTY',1,0,'C');
		$this->SetX(239);$this->Cell(19,4,'GROSS',"LTR",0,'C');
		$this->SetX(258);$this->Cell(19,4,'NET',"LTR",1,'C');
		
		$this->SetX(100,44);$this->Cell(15,4,'NO.',"LRB",0,'C');
		$this->SetX(115,44);$this->Cell(30,4,'TYPE',"LRB",0,'C');
		$this->SetX(145,44);$this->Cell(20,4,'DATE',"LRB",0,'C');
		$this->SetX(165,44);$this->Cell(20,4,'DATE',"LRB",0,'C');
		$this->SetX(239,44);$this->Cell(19,4,'AMOUNT',"LRB",0,'C');
		$this->SetX(258,44);$this->Cell(19,4,'AMOUNT',"LRB",1,'C');
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

$GETMPOS	=	$_SESSION["QUERY"];
$RSGETMPOS	=	$Filstar_conn->Execute($GETMPOS);
if($RSGETMPOS == false)
{
	echo $errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__);  exit();
}
else 
{
	$cnt		=	1;
	$GRANDTOTAL	=	0;
	$NETTOTAL	=	0;
	$GRANDQTY	=	0;
	while (!$RSGETMPOS->EOF) {
		$CUSTNO		=	$RSGETMPOS->fields["CUSTNO"];
		$CustName	=	$RSGETMPOS->fields["CustName"];
		$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];
		$MPOSDATE	=	$RSGETMPOS->fields["MPOSDATE"];
		$SCANDATE	=	$RSGETMPOS->fields["SCANDATE"];
		$TOTALQTY	=	$RSGETMPOS->fields["TOTALQTY"];
		$TYPE		=	$RSGETMPOS->fields["TYPE"];
		$STATUS		=	$RSGETMPOS->fields["STATUS"];
		$GROSSAMOUNT=	$RSGETMPOS->fields["GROSSAMOUNT"];
		$NETAMOUNT	=	$RSGETMPOS->fields["NETAMOUNT"];
		if($STATUS != "")
		{
			$STATUS	=	"Received and Scanned";
		}
		else 
		{
			$STATUS	=	"Received and not yet Scanned";
		}		
		$pdf->SetX(5);$pdf->Cell(10,5,"$cnt",1,0,'C');
		$pdf->SetX(15);$pdf->Cell(85,5,"$CUSTNO-$CustName",1,0,'L');
		$pdf->SetX(100);$pdf->Cell(15,5,"$MPOSNO",1,0,'C');
		$pdf->SetX(115);$pdf->Cell(30,5,"$TYPE",1,0,'C');
		$pdf->SetX(145);$pdf->Cell(20,5,"$MPOSDATE",1,0,'C');
		$pdf->SetX(165);$pdf->Cell(20,5,"$SCANDATE",1,0,'C');
		$pdf->SetX(185);$pdf->Cell(42,5,"$STATUS",1,0,'C');
		$pdf->SetX(227);$pdf->Cell(12,5,number_format($TOTALQTY),1,0,'C');
		$pdf->SetX(239);$pdf->Cell(19,5,number_format($GROSSAMOUNT,2),1,0,'R');
		$pdf->SetX(258);$pdf->Cell(19,5,number_format($NETAMOUNT,2),1,1,'R');
			
		$GRANDTOTAL	+=	$GROSSAMOUNT;
		$NETTOTAL	+=	$NETAMOUNT;
		$GRANDQTY	+=	$TOTALQTY;
		$cnt++;
	$RSGETMPOS->Movenext();
	}
					
	$pdf->SetFont('Times','B',9);
	$pdf->SetX(5);$pdf->Cell(222,5,"GRAND TOTAL",1,0,'C');
	$pdf->SetX(227);$pdf->Cell(12,5,number_format($GRANDQTY),1,0,'C');
	$pdf->SetX(239);$pdf->Cell(19,5,number_format($GRANDTOTAL,2),1,0,'R');
	$pdf->SetX(258);$pdf->Cell(19,5,number_format($NETTOTAL,2),1,1,'R');
	echo $pdf->Output();
}
?>