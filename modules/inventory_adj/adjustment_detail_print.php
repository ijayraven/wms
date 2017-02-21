<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}
include("../../adodb/adodb.inc.php");
include("../../fpdf/fpdf.php");
$conn	=	ADONewConnection('mysqlt');
$dbconn	=	$conn->Connect('192.168.255.10','root','','WMS_NEW');

$printcode	= $_GET['printcode'];
$selType	= $_GET['selType'];
$txtDateFrom= $_GET['txtDateFrom'];
$txtDateTo	= $_GET['txtDateTo'];
$reportType	= $_GET['reportType'];
$trans 			= explode(",",$printcode);
$list_trans 	=	implode("','",$trans);
$qry_sel 		= "SELECT * FROM WMS_NEW.INVENTORYADJUSTMENT_HDR WHERE IATRANSNO in ('{$list_trans}')";
$rs_qry_sel		= $conn->Execute($qry_sel);
if ($rs_qry_sel == false)
{
	echo $conn->ErrorMsg()."::".__LINE__;exit();
}
$cnt	=	$rs_qry_sel->RecordCount();
$_SESSION['name']	=	$global_func->Select_val($Filstar_conn,WMS_LOOKUP,"USER","NAME","USERNAME = '{$_SESSION['username']}'");
class INVENTORY extends FPDF 
{
	function HEADER_ko($printcode,$txtDateFrom,$txtDateTo,$IATRANSNO,$custname,$ADDEDDATE,$REFTYPE,$STATUS,$REFN0)
	{
		$this->Image("/var/www/html/wms/images/fdc101.jpg",20,12,50,20);
		$this->SetFont('Courier','B',9);
		$this->SetX(10);$this->Cell(0,7,"FILSTAR DISTRIBUTORS CORPORATION",0,1,'C');
		$this->SetX(10);$this->Cell(0,5,"INVENTORY ADJUSTMENT",0,1,'C');
		$this->SetX(10);$this->Cell(0,5,"CONFIRM DELIVERY",0,1,'C');
		$this->SetX(10);$this->Cell(0,5,"TRANSACTION DETAIL",0,1,'C');
		$this->SetX(10);$this->Cell(30,5,"",0,1,'L');
		$this->Ln(5);

		$this->SetX(20);$this->Cell(20,5,"CUSTOMER",0,0,'L');
		$this->SetX(40);$this->Cell(20,5,": $custname",0,1,'L');
		
		$this->SetX(20);$this->Cell(20,5,"I.A. NO.",0,0,'L');
		$this->SetX(40);$this->Cell(20,5,": $IATRANSNO",0,0,'L');
		$this->SetX(135);$this->Cell(20,5,"REF NO.",0,0,'L');
		$this->SetX(155);$this->Cell(20,5,": $REFN0",0,1,'L');
		
		$this->SetX(20);$this->Cell(20,5,"STATUS.",0,0,'L');
		$this->SetX(40);$this->Cell(20,5,": $STATUS",0,0,'L');
		$this->SetX(135);$this->Cell(20,5,"REF TYPE.",0,0,'L');
		$this->SetX(155);$this->Cell(20,5,": $REFTYPE",0,1,'L');
		$this->SetX(135);$this->Cell(20,5,"I.A DATE.",0,0,'L');
		$this->SetX(155);$this->Cell(20,5,": $ADDEDDATE",0,1,'L');
		
		$this->SetFont('Courier','B',9);
		$this->SetX(20);$this->Cell(20,5,"",0,1,'C');
		$this->SetX(10);$this->Cell(15,5,"LINENO",0,0,'C');
		$this->SetX(25);$this->Cell(10,5,"SKUNO",0,0,'C');
		$this->SetX(30);$this->Cell(55,5,"DESCRIPTION",0,0,'C');
		$this->SetX(88);$this->Cell(15,5,"STATUS",0,0,'C');
		$this->SetX(102);$this->Cell(15,5,"SRP",0,0,'C');
		$this->SetX(115);$this->Cell(25,5,"I.A. QTY",0,0,'C');
		$this->SetX(138);$this->Cell(20,5,"LOCATION",0,0,'C');
		$this->SetX(150);$this->Cell(35,5,"REMARKS",0,0,'C');
		$this->SetX(182);$this->Cell(35,5,"ADJ TYPE",0,1,'C');
		
	}
	function BODY()
	{
				
		$conn	=	ADONewConnection('mysqlt');
		$dbconn	=	$conn->Connect('192.168.255.10','root','','WMS_NEW');
		
		$printcode	= $_GET['printcode'];
		$selType	= $_GET['selType'];
		$txtDateFrom= $_GET['txtDateFrom'];
		$txtDateTo	= $_GET['txtDateTo'];
		$reportType	= $_GET['reportType'];

		$txttransno		=	$_GET['txttransno'];
		$txtrefno		=	$_GET['txtrefno'];
		$customercode	=	$_GET['customercode'];
		$selrefno		=	$_GET['selrefno'];
		$selstatus		=	$_GET['selstatus'];
	}
	
	function footer()
	{
		$this->SetFont('Courier','I',7);
		$this->SetY(250);$this->Cell(0,5,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}

	function Footer_123($STATUS)
	{
		if ($STATUS == "IN-PROCESS")
		{
			$this->SetFont('Courier','B',8);
			$this->SetXY(150,210);$this->Cell(45,3,"STOCK RECEIVED BY","T",0,'C');
	//		$this->SetXY(150,150);$this->Cell(45,3,"",1,0,'C');
			$this->SetXY(150,220);$this->Cell(45,3,"RECEIVED DATE/TIME:","T",0,'C');
		}
		$this->SetFont('Courier','',7);
		$this->SetY(225);$this->Cell(0,10,'Printed By   : '.$_SESSION['name'],0,1,'L');
		$this->SetY(230);$this->Cell(0,10,'Printed Date : '.date('Y-m-d'),0,1,'L');
		$this->SetY(235);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
		
	}
	
}
function Sel_val($conn,$database,$tbl,$fld,$condition)
{
	$sel	=	"SELECT $fld FROM ".$database.".$tbl WHERE $condition";
	$rssel	=	$conn->Execute($sel);
		if($rssel == false)
		{
			echo $conn->ErrorMsg()."::".__LINE__;
			exit();
		}
	$retval	=	$rssel->fields["$fld"];
	return $retval;
}
$pdf= new INVENTORY('P','mm','Letter');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak('auto',30);
if($cnt>0)
{
	$total_qty	=	0;
	
	while (!$rs_qry_sel->EOF)
	{
		$pdf->AddPage();
		$IATRANSNO	=	$rs_qry_sel->fields['IATRANSNO'];
		$CUSTNO		=	$rs_qry_sel->fields['CUSTNO'];
		$REFN0		=	$rs_qry_sel->fields['REFN0'];
		$REFTYPE	=	$rs_qry_sel->fields['REFTYPE'];
		$STATUS		=	$rs_qry_sel->fields['STATUS'];
		$ADDEDDATE	=	$rs_qry_sel->fields['ADDEDDATE'];
		$REFN0		= Sel_val($conn,'WMS_NEW','INVENTORYADJUSTMENT_HDR','REFN0',"IATRANSNO='{$IATRANSNO}'");
		
		$custname		= Sel_val($conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
		
		$pdf->HEADER_ko($printcode,$txtDateFrom,$txtDateTo,$IATRANSNO,$custname,$ADDEDDATE,$REFTYPE,$STATUS,$REFN0);
		
		$IANO	=	"SELECT IATRANSNO,SKUNO,ITEMSTATUS,IAQTY,REMARKS,MOVEMENT FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE  IATRANSNO = '{$IATRANSNO}' ";
		$rsIANO	=	$conn->Execute($IANO);
		if ($rsIANO==false)
		{
			echo $conn->ErrorMsg()."::".__LINE__;exit();
		}
		$pdf->SetFont('Courier','b',7.5);
		$counter_page	=	0;
		$cnt=1;
		
		while (!$rsIANO->EOF) 
		{
			$IATRANSNO	=	$rsIANO->fields['IATRANSNO'];
			$SKUNO		=	$rsIANO->fields['SKUNO'];
			$ITEMSTATUS	=	$rsIANO->fields['ITEMSTATUS'];
			$IAQTY		=	$rsIANO->fields['IAQTY'];
			$REMARKS	=	$rsIANO->fields['REMARKS'];
			$MOVEMENT	=	$rsIANO->fields['MOVEMENT'];
			
			$total_qty	+=	$IAQTY;

			$ItemDesc	=	substr(Sel_val($conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '".$SKUNO."'"),0,40);
			$srp		=	number_format(Sel_val($conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '".$SKUNO."'"),2);
			$whsloc		=	Sel_val($conn,"FDCRMSlive","itembal","whsloc","itmnbr = '".$SKUNO."' and house = 'FDC' ");
			$REMARKS	=	Sel_val($conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE = '".$REMARKS."' ");
		
			if ($counter_page==35)
			{
				$counter_page=0;
				$pdf->AddPage();
				$pdf->HEADER_ko($printcode,$txtDateFrom,$txtDateTo,$IATRANSNO,$custname,$REFN0,$REFTYPE,$STATUS,$REFN0);
				$pdf->SetFont('Courier','b',7.5);
				$pdf->SetX(10);$pdf->Cell(15,5,"$cnt",0,0,'C');
				$pdf->SetX(25);$pdf->Cell(15,5,"$SKUNO",0,0,'C');
				$pdf->SetX(38);$pdf->Cell(68,5,"$ItemDesc",0,0,'L');
				$pdf->SetX(80);$pdf->Cell(30,5,"$ITEMSTATUS",0,0,'C');
				$pdf->SetX(99);$pdf->Cell(15,5,"$srp",0,0,'R');
				$pdf->SetX(105);$pdf->Cell(25,5,"$IAQTY",0,0,'R');
				$pdf->SetX(138);$pdf->Cell(20,5,"$whsloc",0,0,'C');
				$pdf->SetX(155);$pdf->Cell(35,5,"$REMARKS",0,0,'C');
				$pdf->SetX(183);$pdf->Cell(35,5,$MOVEMENT,0,1,'C');
			}
			else 
			{
				$pdf->SetX(10);$pdf->Cell(15,5,"$cnt",0,0,'C');
				$pdf->SetX(25);$pdf->Cell(10,5,"$SKUNO",0,0,'C');
				$pdf->SetX(38);$pdf->Cell(68,5,"$ItemDesc",0,0,'L');
				$pdf->SetX(80);$pdf->Cell(30,5,"$ITEMSTATUS",0,0,'C');
				$pdf->SetX(99);$pdf->Cell(15,5,"$srp",0,0,'R');
				$pdf->SetX(105);$pdf->Cell(25,5,"$IAQTY",0,0,'R');
				$pdf->SetX(138);$pdf->Cell(20,5,"$whsloc",0,0,'C');
				$pdf->SetX(155);$pdf->Cell(35,5,"$REMARKS",0,0,'C');
				$pdf->SetX(183);$pdf->Cell(35,5,$MOVEMENT,0,1,'C');
			}
			$cnt++;
			$counter_page++;
			$rsIANO->MoveNext();
		}
		
		$pdf->SetX(90);$pdf->Cell(15,5,"TOTAL QUANTITY",0,0,'L');
		$pdf->SetX(105);$pdf->Cell(25,5,"$total_qty",0,0,'R');
		
		$pdf->Footer_123($STATUS);
		$rs_qry_sel->MoveNext();
	}
	
}

$pdf->Output();


?>