<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}
if ($_GET["CUSTYPE"] == "NBS")
{
	$CUSTYPE_Q	=	" AND C.CustomerBranchCode != ''";
}
else if ($_GET["CUSTYPE"] == "TRADE")
{
	$CUSTYPE_Q	=	" AND C.CustomerBranchCode = ''";
}
else 
{
	$GETNONREV		=	"SELECT `CUSTNO`, CUSTDESC FROM WMS_LOOKUP.NONREVENUE_CUST";
	$RSGETNONREV	=	$Filstar_conn->Execute($GETNONREV);
	if($RSGETNONREV == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		while (!$RSGETNONREV->EOF) 
		{
			$nonrevcust	=	$RSGETNONREV->fields["CUSTNO"];
			$listnonrev	.=	",'$nonrevcust'";
			$RSGETNONREV->MoveNext();
		}
	}
	$listnonrev = substr($listnonrev,1);
	$CUSTYPE_Q	=	" AND H.CUSTNO IN ($listnonrev)";
}
$GETSCANNEDMPOS	=	"SELECT H.*,C.CustName FROM WMS_NEW. SCANDATA_HDR AS H 
					 LEFT JOIN FDCRMSlive.custmast AS C ON C.CustNo = H.CUSTNO
					 WHERE H.TRANSMITBY = '' AND POSTEDDATE BETWEEN '{$_GET["POSTEDDFROM"]}' AND '{$_GET["POSTEDDTO"]}' $CUSTYPE_Q";
$RSGETSCANNEDMPOS	=	$Filstar_conn->Execute($GETSCANNEDMPOS);
if($RSGETSCANNEDMPOS == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
}
else 
{
	$arrMPOS	=	array();
	while (!$RSGETSCANNEDMPOS->EOF) {
		$MPOSNO 	= $RSGETSCANNEDMPOS->fields["MPOSNO"]; 
		$CUSTCODE 	= $RSGETSCANNEDMPOS->fields["CUSTNO"]; 
		$CustName	= $RSGETSCANNEDMPOS->fields["CustName"]; 
		$SCANNEDAMT	= $RSGETSCANNEDMPOS->fields["POSTEDGROSSAMOUNT"];
		$MPOSAMOUNT	= $global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","GROSSAMOUNT","MPOSNO= '{$MPOSNO}'");
		
		$arrMPOS[$MPOSNO]["CUSTOMER"]	=	"$CUSTCODE($CustName)";
		$arrMPOS[$MPOSNO]["MPOSAMT"]	+=	$MPOSAMOUNT;
		$arrMPOS[$MPOSNO]["SCANNEDAMT"]	+=	$SCANNEDAMT;
		$RSGETSCANNEDMPOS->MoveNext();
	}
}
class PDF extends FPDF 
{
	function Header()
	{
		$this->SetFont('Times','B',12);
		$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'MPOS TRANSMITTAL',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'SCANNING SECTION',0,1,'C');
		$this->image("fdc_ls_col.jpg",10,10,40,20);
		$this->ln(10);
	}
	
	function Footer()
	{
		$this->SetFont('Courier','',9);
		$this->SetFont('Times','',9);
		$this->SetY(274);$this->Cell(0,10,'Printed Date  : '.date('Y-m-d H:i:s'),0,1,'L');
		$this->SetY(279);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
$pdf= new PDF('P','mm','A4');
$pdf->Open();
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak('auto',15);
$pdf->AddPage();
$pdf->SetFont('Times','B',9);

$pdf->SetX(10);$pdf->Cell(30,5,"CUSTOMER TYPE",0,0,'L');
$pdf->SetX(40);$pdf->Cell(50,5,": ".$_GET["CUSTYPE"],0,1,'L');
$pdf->SetX(10);$pdf->Cell(30,5,"POSTED DATE",0,0,'L');
$pdf->SetX(40);$pdf->Cell(50,5,": ".$_GET["POSTEDDFROM"]." to ".$_GET["POSTEDDTO"],0,1,'L');

$pdf->ln(10);
$pdf->SetX(10);$pdf->Cell(20,5,"LINE NO.",1,0,'C');
$pdf->SetX(30);$pdf->Cell(20,5,"MPOS NO.",1,0,'C');
$pdf->SetX(50);$pdf->Cell(80,5,"CUSTOMER",1,0,'C');
$pdf->SetX(130);$pdf->Cell(30,5,"MPOS AMOUNT",1,0,'C');
$pdf->SetX(160);$pdf->Cell(40,5,"SCANNED AMOUNT",1,1,'C');

$cnt			=	1;
$totMPOSAMT		=	0;
$totSCANNEDAMT	=	0;
$DATE			=	date("Y-m-d");
$time			=	date("H:i:s");
$pdf->SetFont('Times','',9);

foreach ($arrMPOS as $MPOSNO=>$val1)
{
	$customer 	= $val1["CUSTOMER"];
	$MPOSAMT	= $val1["MPOSAMT"];
	$SCANNEDAMT	= $val1["SCANNEDAMT"];
	
	$pdf->SetX(10);$pdf->Cell(20,5,"$cnt",1,0,'C');
	$pdf->SetX(30);$pdf->Cell(20,5,"$MPOSNO",1,0,'C');
	$pdf->SetX(50);$pdf->Cell(80,5,"$customer",1,0,'L');
	$pdf->SetX(130);$pdf->Cell(30,5,number_format($MPOSAMT,2),1,0,'R');
	$pdf->SetX(160);$pdf->Cell(40,5,number_format($SCANNEDAMT,2),1,1,'R');
	
	$totMPOSAMT		+=	$MPOSAMT;
	$totSCANNEDAMT	+=	$SCANNEDAMT;
	$cnt++;
	
	$Filstar_conn->StartTrans();
		$TRANS_SCANNING	=	"UPDATE WMS_NEW.SCANDATA_HDR SET `TRANSMITBY` = '{$_SESSION['username']}', `TRANSMITDATE`='{$DATE}', `TRANSMITTIME`='{$time}'
							 WHERE MPOSNO = '{$MPOSNO}'";
		$RSTRANS_SCANNING	=	$Filstar_conn->Execute($TRANS_SCANNING);
		if($RSTRANS_SCANNING == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		$TRANS_MPOSHDR	=	"UPDATE WMS_NEW.MPOSHDR SET STATUS = 'TRANSMITTED'
							 WHERE MPOSNO = '{$MPOSNO}'";
		$RSTRANS_MPOSHDR	=	$Filstar_conn->Execute($TRANS_MPOSHDR);
		if($RSTRANS_MPOSHDR == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
	$Filstar_conn->CompleteTrans();
}
	$pdf->SetFont('Times','B',9);
	$pdf->SetX(10);$pdf->Cell(120,5,"TOTAL",1,0,'C');
	$pdf->SetX(130);$pdf->Cell(30,5,number_format($totMPOSAMT,2),1,0,'R');
	$pdf->SetX(160);$pdf->Cell(40,5,number_format($totSCANNEDAMT,2),1,0,'R');
	
	$pdf->ln(10);
	$user =	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","USER","NAME","USERNAME = '{$_SESSION['username']}'");; 
	$pdf->SetX(10);$pdf->Cell(40,5,"TRANSMITTED BY",0,0,'L');
	$pdf->SetX(50);$pdf->Cell(50,5,": ".$user,0,0,'L');
	
	$pdf->SetX(130);$pdf->Cell(90,5,"_____________________________",0,1,'C');
	$pdf->SetX(130);$pdf->Cell(90,5,"RECEIVED BY",0,1,'C');
	
	$pdf->SetX(10);$pdf->Cell(40,5,"TRANSMITTED DATE",0,0,'L');
	$pdf->SetX(50);$pdf->Cell(50,5,": ".date("Y-m-d H:i:s"),0,1,'L');
	
	$pdf->SetX(130);$pdf->Cell(90,5,"_____________________________",0,1,'C');
	$pdf->SetX(130);$pdf->Cell(90,5,"RECEIVED DATE",0,1,'C');
	
	
echo $pdf->Output();
?>