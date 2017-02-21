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

$_SESSION['THIS_TRXNO']		=	"T_INV0120160414462";

$today		=	date('Y-m-d');
//$sel_date	=	"SELECT * FROM WMS_NEW.DISPOSAL_TRANSNO WHERE DATE = '{$today}' ORDER BY COUNT DESC ";
//$rssel_date	=	$Filstar_loc->Execute($sel_date);
//if ($rssel_date==false) 
//{
//	echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
//}
//if ($rssel_date->RecordCount() > 0) 
//{
//	$COUNT		=	$rssel_date->fields['COUNT']+1;
//	$insert		=	"INSERT INTO WMS_NEW.DISPOSAL_TRANSNO(`DATE`,`COUNT`)VALUES(SYSDATE(),'{$COUNT}') ";
//	$rsinsert	=	$Filstar_loc->Execute($insert);
//	if ($rsinsert==false) 
//	{
//		echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
//	}
//	$transno_d	=	"D".date('Ymd').str_pad($COUNT,3,0,STR_PAD_LEFT);
//}
//else 
//{
//	$insert		=	"INSERT INTO WMS_NEW.DISPOSAL_TRANSNO(`DATE`,`COUNT`)VALUES(SYSDATE(),'1') ";
//	$rsinsert	=	$Filstar_loc->Execute($insert);
//	if ($rsinsert==false) 
//	{
//		echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
//	}
//	$transno_d	=	"D".date('Ymd').str_pad(1,3,0,STR_PAD_LEFT);
//}
//
//$_SESSION['REFNO']		=	$transno_d;

//$update					=	"UPDATE WMS_NEW.TIANGGE_HDR SET 
//							REFN0 = '{$_SESSION['REFNO']}', STATUS = 'DISPOSAL' ,DISPBY = '{$_SESSION['username']}', DISPDATE = SYSDATE(), DISPTIME = SYSDATE() 
//							WHERE TRANSNO = '{$_SESSION['THIS_TRXNO']}' ";
//$rsupdate				=	$Filstar_loc->Execute($update);
//if ($rsupdate==false) 
//{
//	echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
//}

$aData	=	array();
$sel_transno	 =	"SELECT TRANSNO,TYPE,DRNO,PRICEPOINT,CUSTCODE FROM WMS_NEW.TIANGGE_HDR WHERE TRANSNO ='{$_SESSION['THIS_TRXNO']}' ";
$rssel_transno	=	$Filstar_loc->Execute($sel_transno);
if ($rssel_transno==false) 
{
	echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
}
while (!$rssel_transno->EOF) 
{
	$transno				=	$rssel_transno->fields['TRANSNO'];
	$_SESSION['DRNO']		=	$rssel_transno->fields['DRNO'];
	$_SESSION['PRICEPOINT']	=	$rssel_transno->fields['PRICEPOINT'];
	$_SESSION['CUSTCODE']	=	$rssel_transno->fields['CUSTCODE'];
	
	$_SESSION['CUSTNAME']	=	$global_func->Select_val($Filstar_loc,"FDCRMSlive","custmast","CustName","CustNo = '{$_SESSION['CUSTCODE']}' ");
	
	
	$sel_dtl	=	"SELECT  * FROM WMS_NEW.TIANGGE_DTL WHERE TRANSNO = '{$transno}' ";
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
		
		$aData[$SKUNO]['QTY']			+=	$QTY;
		$aData[$SKUNO]['STATUS']		=	$ITEMSTATUS;
		$aData[$SKUNO]['RETAILPRICE']	=	$_SESSION['PRICEPOINT'];
		//$aData[$SKUNO]['GROSSAMOUNT']	+=	$GROSSAMOUNT;
		//$aData[$SKUNO]['COSTAMOUNT']	+=	$COSTAMOUNT;
		
		$rssel_dtl->MoveNext();
	}
	$rssel_transno->MoveNext();
}
		class PDF extends FPDF 
		{
			function Header($header_)
			{
				
				$code = $_SESSION['THIS_TRXNO'];
				$path =  "tmp/";
			
				Generate_Barcode_Image($code, $path, "{$code}.png");
				$this->Image("$path$code.png",11,30,50,9); //Image(name, x, y, w, h)
				
				$this->Image("/var/www/html/wms/images/fdc101.jpg",10,5,35,15);
				
				$this->SetFont('Courier','B',13);
				$this->SetX(10);$this->Cell(0,5,$_SESSION['CUSTCODE']."-".$_SESSION['CUSTNAME'],0,1,'C');
				$this->ln(5);
				$this->SetX(10);$this->Cell(25,5,'TRANSACTION',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(40);$this->Cell(70,5,':'.$_SESSION['THIS_TRXNO'],0,0,'L');
				$this->SetFont('Courier','B',12);
				$this->SetX(130);$this->Cell(20,5,'DR NO.',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(170);$this->Cell(40,5,':'.$_SESSION['DRNO'],0,1,'L');
				
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(25,5,'TYPE',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(40);$this->Cell(70,5,':TIANGGE',0,1,'L');
				
				$this->ln(5);
				$this->SetFont('Courier','B',15);
				$this->SetX(10);$this->Cell(10,5,$header_,0,1,'L');
				$this->SetFont('Courier','B',10);
				$this->SetX(10);$this->Cell(10,5,'LINE#',0,0,'C');
				$this->SetX(20);$this->Cell(20,5,'ITEM #',0,0,'C');
				$this->SetX(40);$this->Cell(95,5,'DESCRIPTION',0,0,'C');
				$this->SetX(135);$this->Cell(20,5,'TOTAL QTY',0,0,'C');
				$this->SetX(165);$this->Cell(20,5,'SRP',0,0,'C');
				$this->SetX(180);$this->Cell(30,5,'AMOUNT',0,1,'R');
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
				$this->SetY(340);$this->Cell(0,10,'Printed Date  : '.date('Y-m-d'),0,1,'L');
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
			
		$record	=	count($aData);
		if ($record > 0) 
		{
			$COUNT		=	1;
			$total_qty	=	0;
			$total_gross=	0;
			
			foreach ($aData as $key_item=>$val__)
			{
				$QTY		=	$val__['QTY'];
				$RETAILPRICE=	$val__['RETAILPRICE'];
				//$GROSSAMOUNT=	$val__['GROSSAMOUNT'];
				$GROSSAMOUNT=	$QTY*$RETAILPRICE;
				
				$total_qty	+=	$QTY;
				$total_gross+=	$GROSSAMOUNT;
				
				$item_desc	=	$pdf->Sel_val($Filstar_171,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = '{$key_item}' ");
				$pdf->SetFont('Courier','B',10);
				$pdf->SetX(10);$pdf->Cell(10,5,$COUNT,0,0,'C');
				$pdf->SetX(20);$pdf->Cell(20,5,$key_item,0,0,'C');
				$pdf->SetFont('Courier','I',10);
				$pdf->SetX(40);$pdf->Cell(95,5,substr($item_desc,0,45),0,0,'L');
				$pdf->SetFont('Courier','B',10);
				$pdf->SetX(135);$pdf->Cell(20,5,$QTY,0,0,'C');
				$pdf->SetX(160);$pdf->Cell(20,5,number_format($RETAILPRICE,2),0,0,'R');
				$pdf->SetX(180);$pdf->Cell(30,5,number_format($GROSSAMOUNT,2),0,1,'R');
				$COUNT++;
			}
			$pdf->SetX(135);$pdf->Cell(20,5,$total_qty,0,0,'C');
			$pdf->SetX(180);$pdf->Cell(30,5,number_format($total_gross,2),0,1,'R');
		}
		else 
		{
			$pdf->SetFont('Courier','B',13);
			$pdf->SetX(10);$pdf->Cell(0,5," * * * NO RECORD FOUND * * * ",0,0,'C');
		}
		echo $pdf->Output();
?>