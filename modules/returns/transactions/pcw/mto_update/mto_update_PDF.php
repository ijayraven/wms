<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms'</script>";
}
$MTONO		=	$_GET["MTONO"];
$PRRNO		= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_PCWHDR","PRR_NO","MTONO= '{$MTONO}'");
$PRRDATE	= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_PCWHDR","PRINTDATE","MTONO= '{$MTONO}'");
$PCWID		= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_PCWHDR","PIECEWORKER","MTONO= '{$MTONO}'");
$DRNO		= 	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_PCWHDR","DRNO","MTONO= '{$MTONO}'");
$PCWCODE	= 	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","PIECEWORKER","CODE","RECID= '{$PCWID}'");
$PCWNAME	= 	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","PIECEWORKER","DESCRIPTION","RECID= '{$PCWID}'");
class PDF extends FPDF 
{
	function Header()
	{
		global $MTONO,$PRRNO,$PRRDATE,$PCWCODE,$PCWNAME,$DRNO;
		
		$this->SetFont('Times','B',12);
		$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
		$this->SetX(10);$this->Cell(0,5,'Pieceworker Receiving Report',0,1,'C');
		
//		$path =  "temp/";
//		Generate_Barcode_Image($TRANSNO, $path, "$TRANSNO.png");
//		$this->Image("$path$TRANSNO.png",150,10,50,10);
//		unlink("$path$TRANSNO.png");

		$this->image("fdc_ls_col.jpg",10,10,40,20);
		
		$this->ln(15);
		$this->SetFont('Times','',10);
		$this->SetX(10);$this->Cell(30,5,'P.R.R. No. ',0,0,'L');	
		$this->SetX(40);$this->Cell(40,5," : $PRRNO",0,0,'L');	
		$this->SetX(220);$this->Cell(30,5,"P.R.R Date",0,0,'L');	
		$this->SetX(250);$this->Cell(30,5," : $PRRDATE",0,1,'L');	
		$this->SetX(10);$this->Cell(30,5,'Pieceworker',0,0,'L');	
		$this->SetX(40);$this->Cell(40,5," : $PCWCODE $PCWNAME",0,0,'L');	
		$this->SetX(220);$this->Cell(30,5,"DR No.",0,0,'L');	
		$this->SetX(250);$this->Cell(30,5," : $DRNO",0,1,'L');
		$this->SetX(10);$this->Cell(30,5,'MTO No.',0,0,'L');	
		$this->SetX(40);$this->Cell(40,5," : $MTONO",0,1,'L');
		
		$this->ln(5);
		$this->SetFont('Times','B',10);
		$this->SetX(10);$this->Cell(10,8,'NO.',1,0,'C');	
		$this->SetX(20);$this->Cell(45,8,'BRAND',1,0,'C');	
		$this->SetX(65);$this->Cell(50,8,'CATEGORY',1,0,'C');	
		$this->SetX(115);$this->Cell(70,8,'SUBCATEGORY',1,0,'C');	
		$this->SetX(185);$this->Cell(40,8,'CLASS',1,0,'C');	
		$this->SetX(225);$this->Cell(60,4,'TOTAL QUANTITY',1,1,'C');	
		
		$this->SetX(225);$this->Cell(15,4,'Issued',1,0,'C');	
		$this->SetX(240);$this->Cell(15,4,'Received',1,0,'C');	
		$this->SetX(255);$this->Cell(15,4,'Good',1,0,'C');	
		$this->SetX(270);$this->Cell(15,4,'Defective',1,1,'C');	
		
	}
	function Footer()
	{
		$this->SetFont('Times','',10);
		$this->SetY(195);$this->Cell(0,5,'Printed By	: '.$_SESSION['username'],0,0,'L');
		$this->SetY(200);$this->Cell(0,5,'Printed Date : '.date('Y-m-d H:i A'),0,1,'L');
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

$GETMTODTLS	=	"SELECT * FROM WMS_NEW.MTO_PCWDTL WHERE MTONO = '{$MTONO}'";
$RSGETMTODTLS	=	$Filstar_conn->Execute($GETMTODTLS);
if($RSGETMTODTLS == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__;
	exit();
}
else 
{
	$pdf->SetFont('Times','',10);
	$cnt			=	1;
	$TOTALISSQTY	=	0;
	$TOTALRECQTY	=	0;
	$TOTALGOODQTY	=	0;
	$TOTALDEFQTY	=	0;
	$TOTALAMT		=	0;
	$ARR_MTO		=	array();
	while (!$RSGETMTODTLS->EOF) {
		
		$SKUNO 				= $RSGETMTODTLS->fields["SKUNO"]; 
		$BRANDID			= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","Brand","ItemNo= '{$SKUNO}'");
		$BRANDNAME			= $global_func->Select_val($Filstar_pms,"FDC_PMS","BRAND_NEW","BRAND_NAME","BRAND_ID= '{$BRANDID}'");
		$CATEGORYID			= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","Category","ItemNo= '{$SKUNO}'");
		$CATEGORYNAME		= $global_func->Select_val($Filstar_pms,"FDC_PMS","CATEGORY_NEW","CATEGORY_NAME","CATEGORY_ID= '{$CATEGORYID}'");
		$SUBCATEGORYID		= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","SubCategory","ItemNo= '{$SKUNO}'");
		$SUBCATEGORYNAME	= $global_func->Select_val($Filstar_pms,"FDC_PMS","SUB_CATEGORY_NEW","SUB_CATEGORY_NAME","SUB_CATEGORY_ID= '{$SUBCATEGORYID}'");
		$CLASS				= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","Class","ItemNo= '{$SKUNO}'");
		
		$QTY 				= $RSGETMTODTLS->fields["QTY"]; 
		$RECQTY 			= $RSGETMTODTLS->fields["RECQTY"]; 
		$GOODQTY 			= $RSGETMTODTLS->fields["GOODQTY"]; 
		$DEFQTY				= $RSGETMTODTLS->fields["DEFQTY"]; 
		
		$ARR_MTO[$BRANDNAME][$CATEGORYNAME][$SUBCATEGORYNAME][$CLASS]["QTY"]	+=	$QTY;
		$ARR_MTO[$BRANDNAME][$CATEGORYNAME][$SUBCATEGORYNAME][$CLASS]["RECQTY"]	+=	$RECQTY;
		$ARR_MTO[$BRANDNAME][$CATEGORYNAME][$SUBCATEGORYNAME][$CLASS]["GOODQTY"]+=	$GOODQTY;
		$ARR_MTO[$BRANDNAME][$CATEGORYNAME][$SUBCATEGORYNAME][$CLASS]["DEFQTY"]	+=	$DEFQTY;

		$RSGETMTODTLS->MoveNext();
	}
	foreach ($ARR_MTO AS $BRANDNAME=>$VAL1)
	{
		foreach ($VAL1 AS $CATEGORYNAME=>$VAL2)
		{
			foreach ($VAL2 AS $SUBCATEGORYNAME=>$VAL3)
			{
				foreach ($VAL3 AS $CLASS=>$VAL4)
				{
					
					$pdf->SetX(10);$pdf->Cell(10,5,"$cnt",1,0,"C");	
					$pdf->SetX(20);$pdf->Cell(45,5,"$BRANDNAME",1,0,"L");	
					$pdf->SetX(65);$pdf->Cell(50,5,"$CATEGORYNAME",1,0,"L");	
					$pdf->SetX(115);$pdf->Cell(70,5,substr("$SUBCATEGORYNAME",0,33),1,0,"L");	
					$pdf->SetX(185);$pdf->Cell(40,5,"$CLASS",1,0,"L");		
					$pdf->SetX(225);$pdf->Cell(15,5,number_format($VAL4["QTY"]),1,0,"C");	
					$pdf->SetX(240);$pdf->Cell(15,5,number_format($VAL4["RECQTY"]),1,0,"C");	
					$pdf->SetX(255);$pdf->Cell(15,5,number_format($VAL4["GOODQTY"]),1,0,"C");	
					$pdf->SetX(270);$pdf->Cell(15,5,number_format($VAL4["DEFQTY"]),1,1,"C");	
					
					$TOTALISSQTY	+=	$VAL4["QTY"];
					$TOTALRECQTY	+=	$VAL4["RECQTY"];
					$TOTALGOODQTY	+=	$VAL4["GOODQTY"];
					$TOTALDEFQTY	+=	$VAL4["DEFQTY"];
					$cnt++;
				}
			}
		}
	}
	$pdf->SetFont('Times','B',10);
	$pdf->SetX(10);$pdf->Cell(245,5,"TOTAL",1,0,'C');	
	$pdf->SetX(225);$pdf->Cell(15,5,number_format($TOTALISSQTY),1,0,"C");	
	$pdf->SetX(240);$pdf->Cell(15,5,number_format($TOTALRECQTY),1,0,"C");	
	$pdf->SetX(255);$pdf->Cell(15,5,number_format($TOTALGOODQTY),1,0,"C");	
	$pdf->SetX(270);$pdf->Cell(15,5,number_format($TOTALDEFQTY),1,0,"C");	
	echo $pdf->Output();
}
	
?>