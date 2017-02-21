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

$_SESSION['TRXNO']	=	$_GET['VAL_TRX'];

$_SESSION['name']	=	$global_func->Select_val($Filstar_conn,WMS_LOOKUP,"USER","NAME","USERNAME = '{$_SESSION['username']}'");

$aData				=	array();

$sel_transno		=	"SELECT IATRANSNO,CUSTNO,REFN0,REFTYPE,STATUS,ADDEDDATE FROM WMS_NEW.INVENTORYADJUSTMENT_HDR WHERE IATRANSNO = '{$_SESSION['TRXNO']}' ";
$rssel_transno		=	$Filstar_loc->Execute($sel_transno);
if ($rssel_transno==false) 
{
	echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
}
while (!$rssel_transno->EOF) 
{
	$IATRANSNO				=	$rssel_transno->fields['IATRANSNO'];
	$_SESSION['CUSTNO']		=	$rssel_transno->fields['CUSTNO'];
	$_SESSION['REFN0']		=	$rssel_transno->fields['REFN0'];
	$_SESSION['REFTYPE']	=	$rssel_transno->fields['REFTYPE'];
	$_SESSION['STATUS']		=	$rssel_transno->fields['STATUS'];
	$_SESSION['ADDEDDATE']	=	$rssel_transno->fields['ADDEDDATE'];
	
	$sel_dtl	=	"SELECT IATRANSNO,SKUNO,HOUSE,IA_TYPE,MOVEMENT,IAQTY,LOCATION FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE	IATRANSNO = '{$IATRANSNO}' ";
	$rssel_dtl	=	$Filstar_loc->Execute($sel_dtl);
	if ($rssel_dtl == false) 
	{
		echo $Filstar_loc->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_dtl->EOF) 
	{
		$IATRANSNO	=	$rssel_dtl->fields['IATRANSNO'];
		$SKUNO		=	$rssel_dtl->fields['SKUNO'];
		$HOUSE		=	$rssel_dtl->fields['HOUSE'];
		$IA_TYPE	=	$rssel_dtl->fields['IA_TYPE'];
		$MOVEMENT	=	$rssel_dtl->fields['MOVEMENT'];
		$IAQTY		=	$rssel_dtl->fields['IAQTY'];
		$LOCATION	=	$rssel_dtl->fields['LOCATION'];
		
		$_SESSION['CODE']	=	$HOUSE;
		
		$aData[$SKUNO]['HOUSE']		=	$HOUSE;
		$aData[$SKUNO]['IA_TYPE']	=	$IA_TYPE;
		$aData[$SKUNO]['MOVEMENT']	=	$MOVEMENT;
		$aData[$SKUNO]['IAQTY']		+=	$IAQTY;
		$aData[$SKUNO]['LOCATION']	=	$LOCATION;
		$rssel_dtl->MoveNext();
	}
	$rssel_transno->MoveNext();
}
		//print_r($aData);exit();

		class PDF extends FPDF 
		{
			function Header()
			{
				$this->Image("/var/www/html/wms/images/fdc101.jpg",10,5,35,15);
				
				$path =  "temp/";
				$VAL_TRACKING	=	$_SESSION['TRXNO'];
				
				Generate_Barcode_Image($VAL_TRACKING, $path, "{$VAL_TRACKING}.png");
				$this->Image("$path$VAL_TRACKING.png",145,10,60,10);
				unlink("$path$VAL_TRACKING.png");
				
				$this->SetFont('Courier','B',13);
				$this->SetX(10);$this->Cell(0,5,'INVENTORY ADJUSTMENT FORM',0,1,'C');
				$this->SetFont('Courier','B',10);
				//$this->SetX(10);$this->Cell(0,5,'SCAN DATE: '.date("F d, Y",strtotime($_SESSION['DFROM']))." to ".date("F d, Y",strtotime($_SESSION['DTO'])),0,1,'C');
				$this->SetX(10);$this->Cell(0,5,'MANUAL',0,1,'C');
				
				$this->ln(8);
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(25,5,'I.A. NO',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(40);$this->Cell(70,5,':'.$_SESSION['TRXNO'],0,0,'L');
				$this->SetFont('Courier','B',12);
				$this->SetX(140);$this->Cell(20,5,'REFERENCE NO.',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(175);$this->Cell(40,5,':'.$_SESSION['REFN0'],0,1,'L');
				
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(25,5,'STATUS',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(40);$this->Cell(70,5,':'.$_SESSION['STATUS'],0,0,'L');
				$this->SetFont('Courier','B',12);
				$this->SetX(140);$this->Cell(20,5,'REFERENCE TYPE',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(175);$this->Cell(40,5,':'.$_SESSION['REFTYPE'],0,1,'L');
				
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(25,5,'CODE',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(40);$this->Cell(70,5,':'.$_SESSION['CODE'],0,0,'L');
				$this->SetFont('Courier','B',12);
				$this->SetX(140);$this->Cell(20,5,'I.A. DATE',0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(175);$this->Cell(40,5,':'.$_SESSION['ADDEDDATE'],0,1,'L');
				
				$this->ln(5);
				$this->SetFont('Courier','B',10);
				$this->SetX(5);$this->Cell(10,5,'LINE#',0,0,'C');
				$this->SetX(15);$this->Cell(20,5,'ITEM #',0,0,'C');
				$this->SetX(35);$this->Cell(95,5,'DESCRIPTION',0,0,'C');
				$this->SetX(125);$this->Cell(20,5,'SRP',0,0,'C');
				$this->SetX(148);$this->Cell(20,5,'LOCATION',0,0,'C');
				$this->SetX(180);$this->Cell(20,5,'ADJUSTMENT',0,1,'C');
				$this->SetX(170);$this->Cell(20,5,'QUANTITY',1,0,'C');
				$this->SetX(190);$this->Cell(20,5,'TYPE',1,1,'C');
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
				$this->SetY(337);$this->Cell(0,10,'Printed By   : '.$_SESSION['name'],0,1,'L');
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
			
//		$aData[$SKUNO]['HOUSE']		=	$HOUSE;
//		$aData[$SKUNO]['IA_TYPE']	=	$IA_TYPE;
//		$aData[$SKUNO]['MOVEMENT']	=	$MOVEMENT;
//		$aData[$SKUNO]['IAQTY']		=	$IAQTY;
		$record	=	count($aData);
		if ($record > 0) 
		{
			$COUNT=1;
			$total_qty=0;
			foreach ($aData as $key_item=>$val__)
			{
				$HOUSE		=	$val__['HOUSE'];
				$IA_TYPE	=	$val__['IA_TYPE'];
				$MOVEMENT	=	$val__['MOVEMENT'];
				$IAQTY		=	$val__['IAQTY'];
				$LOCATION	=	$val__['LOCATION'];
				
				$total_qty	+=	$IAQTY;
				
				$item_desc	=	substr($pdf->Sel_val($Filstar_171,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = '{$key_item}' "),0,40);
				$srp		=	number_format($pdf->Sel_val($Filstar_loc,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$key_item}' "),2);
				
				$pdf->SetFont('Courier','',11);
				$pdf->SetX(5);$pdf->Cell(10,5,$COUNT,0,0,'C');
				$pdf->SetX(15);$pdf->Cell(20,5,$key_item,0,0,'C');
				$pdf->SetX(35);$pdf->Cell(95,5,$item_desc,0,0,'C');
				$pdf->SetX(125);$pdf->Cell(20,5,$srp,0,0,'R');
				$pdf->SetX(148);$pdf->Cell(20,5,$LOCATION,0,0,'C');
				$pdf->SetX(162);$pdf->Cell(20,5,$IAQTY,0,0,'R');
				$pdf->SetX(190);$pdf->Cell(20,5,$MOVEMENT,0,1,'C');
				$COUNT++;
			}
			$pdf->SetFont('Courier','B',11);
			$pdf->Ln(2);
			$pdf->SetX(148);$pdf->Cell(20,5,"TOTAL",0,0,'C');
			$pdf->SetX(162);$pdf->Cell(20,5,$total_qty,0,1,'R');
			$pdf->Ln(1);
			$pdf->SetX(10);$pdf->Cell(0,5,"* * * * * * * * * * * END OF RECORD * * * * * * * * * * *",0,0,'C');
		}
		else 
		{
			$pdf->SetFont('Courier','B',13);
			$pdf->SetX(10);$pdf->Cell(0,5," * * * NO RECORD FOUND * * * ",0,0,'C');
		}
		echo $pdf->Output();
?>