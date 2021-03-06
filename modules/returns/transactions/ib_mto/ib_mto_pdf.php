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

$MTOdate	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_IB_HDR","POSTDATE","MTONO= '{$TRXNO}'");
$destination=	"PIECEWORKER";
$GETMTO		=	"SELECT * FROM WMS_NEW.MTO_IB_DTL WHERE MTONO = '{$TRXNO}'";
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
		
		$SKUNO 			= $RSGETMTO->fields["SKUNO"]; 
		$DESCRIPTION 	= $RSGETMTO->fields["DESCRIPTION"]; 
		$ITEMSTATUS 	= $RSGETMTO->fields["ITEMSTATUS"]; 
		$QTY 			= $RSGETMTO->fields["QTY"]; 
		$GROSSAMT		= $RSGETMTO->fields["GROSSAMT"]; 
		$UNITPRICE		= $RSGETMTO->fields["UNITPRICE"]; 
		
		$Location		= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itembal","whsloc","itmnbr= '{$SKUNO}'  AND house = 'FDC'"); ;
		
		$SUPPLEMENTCODE = $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","SupplementCode","ItemNo= '{$SKUNO}'"); 
		$SUOM 			= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","PackCode","ItemNo= '{$SKUNO}'"); 
		$BUOM 			= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitMeasure","ItemNo= '{$SKUNO}'"); 
		$BRAND 			= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","Brand","ItemNo= '{$SKUNO}'"); 
		$CATEGORY 		= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","Category","ItemNo= '{$SKUNO}'"); 
		$SUB_CATEGORY 	= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","SubCategory","ItemNo= '{$SKUNO}'"); 
		$CLASS 			= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","Class","ItemNo= '{$SKUNO}'");
		
		$arrMTO[$BRAND][$CATEGORY][$SUB_CATEGORY][$CLASS][$SKUNO]["DESCRIPTION"]	=	$DESCRIPTION;
		$arrMTO[$BRAND][$CATEGORY][$SUB_CATEGORY][$CLASS][$SKUNO]["SUPPLEMENTCODE"]	=	$SUPPLEMENTCODE;
		$arrMTO[$BRAND][$CATEGORY][$SUB_CATEGORY][$CLASS][$SKUNO]["DESCRIPTION"]	=	$DESCRIPTION;
		$arrMTO[$BRAND][$CATEGORY][$SUB_CATEGORY][$CLASS][$SKUNO]["ITEMSTATUS"]		=	$ITEMSTATUS;
		$arrMTO[$BRAND][$CATEGORY][$SUB_CATEGORY][$CLASS][$SKUNO]["UNIT"]			=	"$BUOM$SUOM";
		$arrMTO[$BRAND][$CATEGORY][$SUB_CATEGORY][$CLASS][$SKUNO]["LOCATION"]		=	$Location;
		$arrMTO[$BRAND][$CATEGORY][$SUB_CATEGORY][$CLASS][$SKUNO]["GROSSAMT"]		+=	$GROSSAMT;
		$arrMTO[$BRAND][$CATEGORY][$SUB_CATEGORY][$CLASS][$SKUNO]["QTY"]			+=	$QTY;
		$arrMTO[$BRAND][$CATEGORY][$SUB_CATEGORY][$CLASS][$SKUNO]["UNITPRICE"]		=	$UNITPRICE;
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
					$this->SetX(10);$this->Cell(0,5,'Material Transfer Order for Return Repacking(Internal Barcode Items)',0,1,'C');
					
					$path =  "temp/";
					Generate_Barcode_Image($TRXNO, $path, "$TRXNO.png");
					$this->Image("$path$TRXNO.png",220,10,60,10);
					unlink("$path$TRXNO.png");
			
					$this->image("fdc_ls_col.jpg",10,10,40,20);
					
					$this->ln(10);
					$this->SetFont('Times','',12);
					$this->SetX(10);$this->Cell(30,5,'M.T.O. Number: ',0,0,'L');
					$this->SetX(40);$this->Cell(50,5," : $TRXNO",0,0,'L');
					$this->SetX(200);$this->Cell(30,5,'From. ',0,0,'L');
					$this->SetX(230);$this->Cell(50,5," : RTN",0,1,'L');
					
					$this->SetX(10);$this->Cell(30,5,'M.T.O. Date ',0,0,'L');
					$this->SetX(40);$this->Cell(50,5," : $MTOdate",0,0,'L');
					$this->SetX(200);$this->Cell(30,5,'To ',0,0,'L');
					$this->SetX(230);$this->Cell(50,5," : $destination",0,1,'L');
					
					$this->ln(5);
					$this->SetFont('Times','B',12);
					$this->SetX(10);$this->Cell(20,5,'Item No.',0,0,'C');
					$this->SetX(30);$this->Cell(20,5,'SCode',0,0,'C');
					$this->SetX(50);$this->Cell(90,5,'Description',0,0,'C');
					$this->SetX(140);$this->Cell(15,5,'Status',0,0,'C');
					$this->SetX(155);$this->Cell(25,5,'Unit',0,0,'C');
					$this->SetX(180);$this->Cell(30,5,'Location',0,0,'C');
					$this->SetX(210);$this->Cell(30,5,'SRP',0,0,'C');
					$this->SetX(240);$this->Cell(30,5,'MTO Qty',0,1,'C');
//					$this->SetX(230);$this->Cell(20,5,'Good',0,0,'C');
//					$this->SetX(250);$this->Cell(20,5,'Def',0,0,'C');
//					$this->SetX(270);$this->Cell(20,5,'Adj',0,1,'C');
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
			
			$GRANDTOTAL_A = 0;
			$GRANDTOTAL_Q = 0;
			foreach ($arrMTO as $brand_A=>$val1)
			{
				foreach ($val1 as $CATEGORY_A=>$val2)
				{
					foreach ($val2 as $SUB_CATEGORY_A=>$val3)
					{
						$TOTAMOUNT		=	0;
						$TOTQUANTITY	=	0;
						foreach ($val3 as $CLASS_A=>$val4)
						{
							$BRAND 			= $global_func->Select_val($Filstar_pms,"FDC_PMS","BRAND_NEW","BRAND_NAME","BRAND_ID= '{$brand_A}'"); 
							$CATEGORY 		= $global_func->Select_val($Filstar_pms,"FDC_PMS","CATEGORY_NEW","CATEGORY_NAME","CATEGORY_ID= '{$CATEGORY_A}'"); 
							$SUB_CATEGORY 	= $global_func->Select_val($Filstar_pms,"FDC_PMS","SUB_CATEGORY_NEW","SUB_CATEGORY_NAME","SUB_CATEGORY_ID= '{$SUB_CATEGORY_A}'"); 
							$pdf->SetFont('Times','B',12);
							if($pdf->GetY() > 190)
							{
								$pdf->AddPage();
							}
							$pdf->SetX(10);$pdf->Cell(40,5,$BRAND,0,0,'L');
							$pdf->SetX(50);$pdf->Cell(60,5,$CATEGORY,0,0,'L');
							$pdf->SetX(110);$pdf->Cell(110,5,$SUB_CATEGORY,0,0,'L');
							$pdf->SetX(220);$pdf->Cell(40,5,$CLASS_A,0,1,'L');
							ksort($val4);
							foreach ($val4 as $SKUNO_A=>$val5)
							{
								$DESCRIPTION	= $val5["DESCRIPTION"];
								$SUPPLEMENTCODE	= $val5["SUPPLEMENTCODE"];
								$DESCRIPTION	= $val5["DESCRIPTION"];
								$ITEMSTATUS		= $val5["ITEMSTATUS"];
								$UNIT			= $val5["UNIT"];
								$LOCATION		= $val5["LOCATION"];
								$GROSSAMT		= $val5["GROSSAMT"];
								$QTY			= $val5["QTY"];
								$UNITPRICE		= $val5["UNITPRICE"];
								$TOTAMOUNT		+=	$GROSSAMT;
								$TOTQUANTITY	+=	$QTY;
								$GRANDTOTAL_A	+=	$GROSSAMT;
								$GRANDTOTAL_Q	+=	$QTY;
								
								$pdf->SetFont('Times','',12);
								$pdf->SetX(10);$pdf->Cell(20,5,$SKUNO_A,0,0,'C');
								$pdf->SetX(30);$pdf->Cell(20,5,$SUPPLEMENTCODE,0,0,'C');
								$pdf->SetX(50);$pdf->Cell(90,5,substr($DESCRIPTION,0,35),0,0,'L');
								$pdf->SetX(140);$pdf->Cell(15,5,$ITEMSTATUS,0,0,'C');
								$pdf->SetX(155);$pdf->Cell(25,5,$UNIT,0,0,'C');
								$pdf->SetX(180);$pdf->Cell(30,5,$LOCATION,0,0,'C');
								$pdf->SetX(210);$pdf->Cell(30,5,number_format($UNITPRICE,2),0,0,'C');
								$pdf->SetX(250);$pdf->Cell(30,5,number_format($QTY),0,1,'C');
//								$pdf->SetX(230);$pdf->Cell(20,5,"________",0,0,'C');
//								$pdf->SetX(250);$pdf->Cell(20,5,'________',0,0,'C');
//								$pdf->SetX(270);$pdf->Cell(20,5,'________',0,1,'C');
							}
							$pdf->SetFont('Times','B',12);
							$pdf->SetX(210);$pdf->Cell(35,5,"TOTAL",0,0,'C');
//							$pdf->SetX(190);$pdf->Cell(20,5,number_format($TOTAMOUNT,2),0,0,'R');
							$pdf->SetX(250);$pdf->Cell(30,5,number_format($TOTQUANTITY),0,1,'C');
							$pdf->SetX(270);$pdf->Cell(20,5,'',0,1,'C');
						}
					}
				}
			}
			$pdf->SetFont('Times','B',12);
			$pdf->SetX(210);$pdf->Cell(35,5,"GRAND TOTAL",0,0,'C');
//			$pdf->SetX(190);$pdf->Cell(20,5,number_format($GRANDTOTAL_A,2),0,0,'R');
			$pdf->SetX(250);$pdf->Cell(30,5,number_format($GRANDTOTAL_Q),0,1,'C');
			$pdf->SetX(270);$pdf->Cell(20,5,'',0,1,'C');
			
			
			$pdf->ln(10);
			$pdf->SetX(190);$pdf->Cell(50,5,"_________________________________",0,1,'C');
			$pdf->SetX(190);$pdf->Cell(50,5,"Received By",0,1,'C');
			$pdf->ln(5);
			$pdf->SetX(190);$pdf->Cell(50,5,"_________________________________",0,1,'C');
			$pdf->SetX(190);$pdf->Cell(50,5,"Received Date",0,1,'C');
			echo $pdf->Output();
?>