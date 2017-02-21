<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}

$Filstar_loc	=	ADONewConnection("mysqlt");
	
$dbFilstar_loc	=	$Filstar_loc->Connect('192.168.255.10','root','');
if ($dbFilstar_loc == false) 
{
	echo "<script>alert('Error Occurred no Database Connection!');</script>";
	echo "<script>location = 'index.php'</script>";
}


$Filstar_171	=	ADONewConnection("mysqlt");
$dbFilstar_171	=	$Filstar_171->Connect('192.168.250.171','root','');
if ($dbFilstar_171 == false) 
{
	echo "<script>alert('Error Occurred no Database Connection!');</script>";
	echo "<script>location = 'index.php'</script>";
}

$_SESSION['BRAND']			=	$_GET['BRAND'];
$_SESSION['CLASS']			=	addslashes($_GET['CLASS']);
$_SESSION['DFROM']			=	$_GET['DFROM'];
$_SESSION['DTO']			=	$_GET['DTO'];
$_SESSION['SOURCE_']		=	$_GET['SOURCE_'];
$_SESSION['DESTINATION_']	=	$_GET['DESTINATION_'];
$_SESSION['SEL_STATUS']		=	$_GET['SEL_STATUS'];
$_SESSION['ITEM_STAT']		=	$_GET['ITEM_STAT'];

$aData	=	array();

if ($_SESSION['BRAND'] != 'ALL')
{
	$sel_brand	=	"SELECT BRAND_NAME FROM FDC_PMS.BRAND_NEW WHERE BRAND_ID = '{$_SESSION['BRAND']}' ";
	$rssel_brand=	$Filstar_171->Execute($sel_brand);
	if ($rssel_brand==false) 
	{
		echo $Filstar_171->ErrorMsg()."::".__LINE__;exit();
	}
	$_SESSION['BRAND_NAME']	=	$rssel_brand->fields['BRAND_NAME'];
}
else 
{
	$_SESSION['BRAND_NAME']	=	'ALL';
}

if ($_SESSION['DESTINATION_'] == 'ALL')
{
	$_SESSION['D']	=	'CHOPPING AND TIANGGE';
}
else if ($_SESSION['DESTINATION_'] == '1')
{
	$_SESSION['D']	=	'CHOPPING';
}
else 
{
	 $_SESSION['D']	=	'TIANGGE';
}
if($_SESSION['CLASS'] != "ALL")
{
	$CLASS_Q	=	" AND CLASS = '{$_SESSION['CLASS']}'";
}
$sel_transno	 =	"SELECT TRANSNO,TYPE FROM WMS_NEW.TIANGGE_HDR WHERE SCANDATE BETWEEN '{$_SESSION['DFROM']}' AND '{$_SESSION['DTO']}' ";
if ($_SESSION['SOURCE_'] != 'ALL') 
{
$sel_transno	.=	"AND SOURCE = '{$_SESSION['SOURCE_']}' ";
}
if ($_SESSION['DESTINATION_'] != 'ALL')
{
$sel_transno	.=	"AND TYPE = '{$_SESSION['DESTINATION_']}'";
}
if ($_SESSION['SEL_STATUS'] != 'ALL') 
{
$sel_transno	.=	"AND  STATUS = '{$_SESSION['SEL_STATUS']}' ";	
}

$rssel_transno	=	$Filstar_loc->Execute($sel_transno);
if ($rssel_transno==false) 
{
	echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
}
while (!$rssel_transno->EOF) 
{
	$transno	=	$rssel_transno->fields['TRANSNO'];
	$DESTINATION=	$rssel_transno->fields['TYPE'];
	if ($_SESSION['BRAND'] != 'ALL')
	{
		$sel_dtl	=	"SELECT  * FROM WMS_NEW.TIANGGE_DTL WHERE TRANSNO = '{$transno}' AND BRAND = '{$_SESSION['BRAND_NAME']}' $CLASS_Q";
		if ($_SESSION['BRAND'] != 'ALL') 
		{
		$sel_dtl	.=	"AND BRAND = '{$_SESSION['BRAND_NAME']}' ";
		}
		if ($_SESSION['ITEM_STAT']!='ALL') 
		{
			$sel_dtl	.=	"AND ITEMSTATUS = '{$_SESSION['ITEM_STAT']}' ";
		}
		$rssel_dtl	=	$Filstar_loc->Execute($sel_dtl);
		if ($rssel_dtl == false) 
		{
			echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_dtl->EOF) 
		{
			$TRANSNO		=	$rssel_dtl->fields['TRANSNO'];
			$SKUNO			=	$rssel_dtl->fields['SKUNO'];
			$ITEMSTATUS		=	$rssel_dtl->fields['ITEMSTATUS'];
			$QTY			=	$rssel_dtl->fields['QTY'];
			$RETAILPRICE	=	$rssel_dtl->fields['RETAILPRICE'];
			$PRODCOST		=	$rssel_dtl->fields['PRODCOST'];
			$GROSSAMOUNT	=	$rssel_dtl->fields['GROSSAMOUNT'];
			$COSTAMOUNT		=	$rssel_dtl->fields['COSTAMOUNT'];
			$BRAND			=	$rssel_dtl->fields['BRAND'];
			$CATEGORY		=	$rssel_dtl->fields['CATEGORY'];
			$SUBCATEGORY	=	$rssel_dtl->fields['SUBCATEGORY'];
			$CLASS			=	$rssel_dtl->fields['CLASS'];
			
			/*$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['QTY']			+=	$QTY;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['STATUS']			=	$ITEMSTATUS;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['RETAILPRICE']	=	$RETAILPRICE;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['PRODCOST']		=	$PRODCOST;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['GROSSAMOUNT']	=	$GROSSAMOUNT;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['COSTAMOUNT']		=	$COSTAMOUNT;*/
			
			$aData[$SKUNO]['QTY']			+=	$QTY;
			$aData[$SKUNO]['STATUS']		=	$ITEMSTATUS;
			$aData[$SKUNO]['RETAILPRICE']	=	$RETAILPRICE;
			$aData[$SKUNO]['PRODCOST']		=	$PRODCOST;
			$aData[$SKUNO]['GROSSAMOUNT']	=	$GROSSAMOUNT;
			$aData[$SKUNO]['COSTAMOUNT']	+=	$COSTAMOUNT;
			
			$rssel_dtl->MoveNext();
		}
	}
	else 
	{
		$sel_dtl	=	"SELECT  * FROM WMS_NEW.TIANGGE_DTL WHERE TRANSNO = '{$transno}' $CLASS_Q";
		if ($_SESSION['ITEM_STAT']!='ALL') 
		{
			$sel_dtl	.=	"AND ITEMSTATUS = '{$_SESSION['ITEM_STAT']}' ";
		}
		$rssel_dtl	=	$Filstar_loc->Execute($sel_dtl);
		if ($rssel_dtl == false) 
		{
			echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_dtl->EOF) 
		{
			$TRANSNO		=	$rssel_dtl->fields['TRANSNO'];
			$SKUNO			=	$rssel_dtl->fields['SKUNO'];
			$ITEMSTATUS		=	$rssel_dtl->fields['ITEMSTATUS'];
			$QTY			=	$rssel_dtl->fields['QTY'];
			$RETAILPRICE	=	$rssel_dtl->fields['RETAILPRICE'];
			$PRODCOST		=	$rssel_dtl->fields['PRODCOST'];
			$GROSSAMOUNT	=	$rssel_dtl->fields['GROSSAMOUNT'];
			$COSTAMOUNT		=	$rssel_dtl->fields['COSTAMOUNT'];
			$BRAND			=	$rssel_dtl->fields['BRAND'];
			$CATEGORY		=	$rssel_dtl->fields['CATEGORY'];
			$SUBCATEGORY	=	$rssel_dtl->fields['SUBCATEGORY'];
			$CLASS			=	$rssel_dtl->fields['CLASS'];
			
			/*$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['QTY']			+=	$QTY;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['STATUS']			=	$ITEMSTATUS;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['RETAILPRICE']	=	$RETAILPRICE;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['PRODCOST']		=	$PRODCOST;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['GROSSAMOUNT']	+=	$GROSSAMOUNT;
			$aData[$transno][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['COSTAMOUNT']	+=	$COSTAMOUNT;*/
			
			$aData[$SKUNO]['QTY']			+=	$QTY;
			$aData[$SKUNO]['STATUS']		=	$ITEMSTATUS;
			$aData[$SKUNO]['RETAILPRICE']	=	$RETAILPRICE;
			$aData[$SKUNO]['PRODCOST']		=	$PRODCOST;
			$aData[$SKUNO]['GROSSAMOUNT']	+=	$GROSSAMOUNT;
			$aData[$SKUNO]['COSTAMOUNT']	+=	$COSTAMOUNT;
			
			$rssel_dtl->MoveNext();
		}
	}
	$rssel_transno->MoveNext();
}
		class PDF extends FPDF 
		{
			//function Header_ko($header_,$status)
			function Header($header_)
			{
				
				$this->Image("/var/www/html/wms/images/fdc101.jpg",10,5,35,15);
				
				$this->SetFont('Courier','B',13);
				$this->SetX(10);$this->Cell(0,5,'PRIMESTOCK & DEFECTIVE ITEMS',0,1,'C');
				$this->SetFont('Courier','B',10);
				$this->SetX(10);$this->Cell(0,5,'SCAN DATE: '.date("F d, Y",strtotime($_SESSION['DFROM']))." to ".date("F d, Y",strtotime($_SESSION['DTO'])),0,1,'C');
				
				$this->ln(3);
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(25,5,'TYPE',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(40);$this->Cell(70,5,':'.$_SESSION['D'],0,0,'L');
				$this->SetFont('Courier','B',12);
				$this->SetX(150);$this->Cell(20,5,'BRAND',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(170);$this->Cell(40,5,':'.$_SESSION['BRAND_NAME'],0,1,'L');
				
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(25,5,'STATUS',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(40);$this->Cell(70,5,':'.$_SESSION['SEL_STATUS'],0,0,'L');
				$this->SetFont('Courier','B',12);
				$this->SetX(150);$this->Cell(20,5,'SOURCE',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(170);$this->Cell(40,5,':'.$_SESSION['SOURCE_'],0,1,'L');
				
				$this->SetX(150);$this->Cell(20,5,'CLASS',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(170);$this->Cell(40,5,':'.$_SESSION['CLASS'],0,0,'L');
				
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(25,5,'ITEM STATUS',0,0,'L');
				$this->SetFont('Courier','B',11);
				if ($_SESSION['ITEM_STAT'] == 'M') {
				$this->SetX(40);$this->Cell(70,5,':'."MODELINE",0,0,'L');
				}
				else {
				$this->SetX(40);$this->Cell(70,5,':'."PRIMESTOCK",0,0,'L');	
				}
				
				
				$this->ln(5);
				$this->SetFont('Courier','B',15);
				$this->SetX(10);$this->Cell(10,5,$header_,0,1,'L');
				$this->SetFont('Courier','B',10);
				$this->SetX(5);$this->Cell(5,5,'NO.',0,0,'C');
				$this->SetX(10);$this->Cell(17,5,'ITEM #',0,0,'C');
				$this->SetX(27);$this->Cell(85,5,'DESCRIPTION',0,0,'C');
				$this->SetX(112);$this->Cell(15,5,'QTY',0,0,'C');
				$this->SetX(127);$this->Cell(20,5,'PROD COST',0,0,'C');
				$this->SetX(147);$this->Cell(15,5,'SRP',0,0,'C');
				$this->SetX(162);$this->Cell(20,5,'AMOUNT',0,0,'R');
				$this->SetX(182);$this->Cell(30,5,'GROSS AMOUNT',0,1,'R');
			}
			
			function HEADER_TRXNO($TRXNO)
			{
				$this->SetFont('Courier','B',15);
				$this->SetXY(10,35);$this->Cell(10,5,$TRXNO,0,1,'L');
				$this->ln(5);
			}
			
			function Footer()
			{
				$this->SetFont('Courier','',9);
				$this->SetY(340);$this->Cell(0,10,'Printed Date : '.date('Y-m-d'),0,1,'L');
				$this->SetY(343);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
				$this->SetY(343);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
			}
			
			function Sel_val($conn,$database,$tbl,$fld,$condition)
			{
				$sel	=	"SELECT $fld FROM ".$database.".$tbl WHERE $condition";
				$rssel	=	$conn->Execute($sel);
				if ($rssel == false) 
				{
					die($conn->ErrorMsg());
				}
				$retval	=	$rssel->fields[$fld];
				return $retval;
			}
		}
		
		$pdf= new PDF('P','mm','legal');
		$pdf->Open();
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak('auto',15);
		$pdf->AddPage();
			
		//$aData[$DESTINATION][$BRAND][$CATEGORY][$SUBCATEGORY][$CLASS][$TRANSNO][$SKUNO]['QTY']			+=	$QTY;
//		print_r($aData);exit();
		$record	=	count($aData);
		if ($record > 0) 
		{
//			foreach ($aData as $key_trxno=>$val_brand)
//			{
//				$status		=	$pdf->Sel_val($Filstar_loc,"WMS_NEW","TIANGGE_HDR","STATUS","TRANSNO = '{$key_trxno}'");
//				$pdf->Header_ko($key_trxno,$status);
//				//$pdf->HEADER_TRXNO($key_trxno);
//				$COUNT	=	1;
//				foreach ($val_brand as $key_brand=>$val_category)
//				{
//					foreach ($val_category as $key_category=>$val_subcategory)
//					{
//						foreach ($val_subcategory as $key_subcategory=>$val_class)
//						{
//							foreach ($val_class as $key_class=>$val_trans)
//							{
//								foreach ($val_trans as $key_trans=>$val_item)
//								{
//									foreach ($val_item as $key_item=>$val__)
//									{
//										$QTY		=	$val__['QTY'];
//										$RETAILPRICE=	$val__['RETAILPRICE'];
//										$PRODCOST	=	$val__['PRODCOST'];
//										$GROSSAMOUNT=	$val__['GROSSAMOUNT'];
//										$COSTAMOUNT	=	$val__['COSTAMOUNT'];
//										
//										$item_desc	=	$pdf->Sel_val($Filstar_171,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = '{$key_item}' ");
//										$pdf->SetFont('Courier','B',10);
//										$pdf->SetX(10);$pdf->Cell(10,5,$COUNT,0,0,'C');
//										$pdf->SetX(20);$pdf->Cell(20,5,$key_item,0,0,'C');
//										$pdf->SetFont('Courier','I',10);
//										$pdf->SetX(40);$pdf->Cell(95,5,substr($item_desc,0,50),0,0,'L');
//										$pdf->SetFont('Courier','B',10);
//										$pdf->SetX(135);$pdf->Cell(20,5,$QTY,0,0,'C');
//										$pdf->SetX(160);$pdf->Cell(20,5,number_format($RETAILPRICE,2),0,0,'R');
//										$pdf->SetX(180);$pdf->Cell(30,5,number_format($GROSSAMOUNT,2),0,1,'R');
//										$COUNT++;
//									}
//								}
//							}
//						}
//					}
//				}
//				$pdf->AddPage();
//			}
			$COUNT		=	1;
			$total_qty	=	0;
			$total_cost =	0;
			$total_gross=	0;
			
			foreach ($aData as $key_item=>$val__)
			{
				$QTY		=	$val__['QTY'];
				$RETAILPRICE=	$val__['RETAILPRICE'];
				$PRODCOST	=	$val__['PRODCOST'];
				$GROSSAMOUNT=	$val__['GROSSAMOUNT'];
				$COSTAMOUNT	=	$val__['COSTAMOUNT'];
				
				$total_qty		+=	$QTY;
				$total_cost		+=	$PRODCOST;
				$total_gross	+=	$COSTAMOUNT;
				$total_gross_amt+=	$QTY * $RETAILPRICE;
				
				$item_desc	=	$pdf->Sel_val($Filstar_171,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = '{$key_item}' ");
				$pdf->SetFont('Courier','',10);
				$pdf->SetX(5);$pdf->Cell(5,5,$COUNT,0,0,'R');
				$pdf->SetFont('Courier','B',10);
				$pdf->SetX(10);$pdf->Cell(17,5,$key_item,0,0,'C');
				$pdf->SetFont('Courier','I',10);
				$pdf->SetX(27);$pdf->Cell(85,5,substr($item_desc,0,40),0,0,'L');
				$pdf->SetFont('Courier','B',10);
				$pdf->SetX(112);$pdf->Cell(15,5,$QTY,0,0,'C');
				$pdf->SetX(127);$pdf->Cell(20,5,number_format($PRODCOST,2),0,0,'R');
				$pdf->SetX(147);$pdf->Cell(15,5,number_format($RETAILPRICE,2),0,0,'R');
				$pdf->SetX(162);$pdf->Cell(20,5,number_format($COSTAMOUNT,2),0,0,'R');
				$pdf->SetX(182);$pdf->Cell(30,5,number_format($QTY * $RETAILPRICE,2),0,1,'R');
				$COUNT++;
			}
			$pdf->SetX(5);$pdf->Cell(107,5,"TOTAL",0,0,'C');
			$pdf->SetX(112);$pdf->Cell(15,5,number_format($total_qty),0,0,'C');
//			$pdf->SetX(145);$pdf->Cell(20,5,number_format($total_cost,2),0,0,'R');
			$pdf->SetX(162);$pdf->Cell(20,5,number_format($total_gross,2),0,0,'R');
			$pdf->SetX(182);$pdf->Cell(30,5,number_format($total_gross_amt,2),0,1,'R');
			$pdf->Ln(5);
			$pdf->SetX(10);$pdf->Cell(0,5,"* * * * * * * * * * * END OF RECORD * * * * * * * * * * *",0,0,'C');
		}
		else 
		{
			$pdf->SetFont('Courier','B',13);
			$pdf->SetX(10);$pdf->Cell(0,5," * * * NO RECORD FOUND * * * ",0,0,'C');
		}
		echo $pdf->Output();
?>