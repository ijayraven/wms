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
//print_r($printcode);
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
	function HEADER_ko($printcode,$txtDateFrom,$txtDateTo,$IATRANSNO,$custname,$ADDEDDATE,$REFTYPE,$STATUS)
	{
		$this->Image("/var/www/html/wms/images/fdc101.jpg",20,12,50,20);
		$this->SetFont('Courier','B',9);
		$this->SetX(10);$this->Cell(195,7,"FILSTAR DISTRIBUTORS CORPORATION",0,1,'C');
		$this->SetX(10);$this->Cell(195,5,"INVENTORY ADJUSTMENT",0,1,'C');
		$this->SetX(10);$this->Cell(195,5,"CONFIRM DELIVERY",0,1,'C');
		$this->SetX(10);$this->Cell(195,5,"TRANSACTION DETAIL",0,1,'C');
//		$this->SetX(10);$this->Cell(195,5,"From:  ".date("F j, Y",strtotime($txtDateFrom))." To ".date("F j, Y",strtotime($txtDateTo))."",0,1,'C');
		$this->SetX(10);$this->Cell(30,5,"",0,1,'L');
			

		$this->SetX(20);$this->Cell(20,5,"I.A No",0,0,'L');
		$this->SetX(40);$this->Cell(20,5,": $IATRANSNO",0,0,'L');
		$this->SetX(115);$this->Cell(20,5,"I.A. DATE",0,0,'L');
		$this->SetX(135);$this->Cell(20,5,": $ADDEDDATE",0,1,'L');
		$this->SetX(20);$this->Cell(20,5,"STATUS",0,0,'L');
		$this->SetX(40);$this->Cell(20,5,": $STATUS",0,0,'L');
		$this->SetX(115);$this->Cell(20,5,"SOF",0,0,'L');
		$this->SetX(135);$this->Cell(20,5,": $REFTYPE",0,1,'L');
		$this->SetX(20);$this->Cell(20,5,"CUSTOMER",0,0,'L');
		$this->SetX(40);$this->Cell(20,5,": $custname",0,1,'L');
			
		
		$this->SetFont('Courier','B',9);
		$this->SetX(20);$this->Cell(20,5,"",0,1,'C');
		$this->SetX(20);$this->Cell(15,5,"LINENO","B",0,'C');
		$this->SetX(35);$this->Cell(15,5,"SKUNO","B",0,'C');
		$this->SetX(50);$this->Cell(55,5,"DESCRIPTION","B",0,'C');
		$this->SetX(105);$this->Cell(15,5,"SRP","B",0,'C');
		$this->SetX(120);$this->Cell(25,5,"I.A. QTY","B",0,'C');
		$this->SetX(145);$this->Cell(20,5,"LOCATION","B",0,'C');
		$this->SetX(165);$this->Cell(35,5,"REMARKS","B",1,'C');
		$this->SetX(165);$this->Cell(35,5,"",0,1,'C');
		
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
	function Footer($STATUS)
			{
				
				$this->SetFont('Courier','B',10);
				$this->SetXY(150,240);$this->Cell(45,3,"STOCK RECEIVED BY","T",0,'C');
				$this->SetXY(150,150);$this->Cell(45,3,"",0,0,'C');
				$this->SetXY(150,252);$this->Cell(45,3,"RECEIVED DATE/TIME:","T",0,'C');
				
				$this->SetFont('Courier','',9);
				$this->SetY(255);$this->Cell(0,10,'Printed By   : '.$_SESSION['name'],0,1,'L');
				$this->SetY(258);$this->Cell(0,10,'Printed Date : '.date('Y-m-d'),0,1,'L');
				$this->SetY(261);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
				$this->SetFont('Courier','I',9);
				$this->SetY(261);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
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
	while (!$rs_qry_sel->EOF)
	{
		$pdf->AddPage();

		$IATRANSNO	=	$rs_qry_sel->fields['IATRANSNO'];
		$CUSTNO		=	$rs_qry_sel->fields['CUSTNO'];
		$REFN0		=	$rs_qry_sel->fields['REFN0'];
		$REFTYPE	=	$rs_qry_sel->fields['REFTYPE'];
		$STATUS		=	$rs_qry_sel->fields['STATUS'];
		$ADDEDDATE	=	$rs_qry_sel->fields['ADDEDDATE'];
		
		$custname		= Sel_val($conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
		
		$pdf->HEADER_ko($printcode,$txtDateFrom,$txtDateTo,$IATRANSNO,$custname,$ADDEDDATE,$REFTYPE,$STATUS);
		
		$IANO	=	"SELECT IATRANSNO,SKUNO,IAQTY,REMARKS FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE  IATRANSNO = '{$IATRANSNO}' ";
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
			$IAQTY		=	$rsIANO->fields['IAQTY'];
			$REMARKS	=	$rsIANO->fields['REMARKS'];

			$ItemDesc	=	substr(Sel_val($conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '".$SKUNO."'"),0,40);
			$srp		=	number_format(Sel_val($conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '".$SKUNO."'"),2);
			$whsloc		=	Sel_val($conn,"FDCRMSlive","itembal","whsloc","itmnbr = '".$SKUNO."' and house = 'FDC' ");
			$REMARKS	=	Sel_val($conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE = '".$REMARKS."' ");
		

			
			$pdf->SetX(20);$pdf->Cell(15,5,"$cnt",0,0,'C');
			$pdf->SetX(35);$pdf->Cell(15,5,"$SKUNO",0,0,'C');
			$pdf->SetX(50);$pdf->Cell(55,5,"$ItemDesc",0,0,'C');
			$pdf->SetX(105);$pdf->Cell(15,5,"$srp",0,0,'C');
			$pdf->SetX(120);$pdf->Cell(25,5,"$IAQTY",0,0,'C');
			$pdf->SetX(145);$pdf->Cell(20,5,"$whsloc",0,0,'C');
			$pdf->SetX(165);$pdf->Cell(35,5,"$REMARKS",0,1,'C');
			if ($counter_page==34)
			{
				$counter_page=0;
				//$pdf->AddPage();
				$pdf->HEADER_ko($printcode,$txtDateFrom,$txtDateTo,$IATRANSNO,$custname,$REFN0,$REFTYPE,$STATUS);
				$pdf->SetFont('Courier','b',7.5);
				$pdf->SetX(20);$pdf->Cell(20,5,"$cnt",0,0,'C');
				$pdf->SetX(40);$pdf->Cell(20,5,"$SKUNO",0,0,'C');
				$pdf->SetX(60);$pdf->Cell(60,5,substr($ItemDesc,0,25),1,0,'C');
				$pdf->SetX(120);$pdf->Cell(25,5,"$IAQTY",0,0,'C');
				$pdf->SetX(145);$pdf->Cell(30,5,"$REMARKS",0,0,'C');
				$pdf->SetX(175);$pdf->Cell(25,5,"$whsloc",0,1,'C');
				
			}
			$cnt++;
			$counter_page++;
			$rsIANO->MoveNext();
		}
		
		$rs_qry_sel->MoveNext();
	}
}




$pdf->Output();


?>