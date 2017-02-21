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
$THIS_TRXNO		=	$_GET['THIS_TRXNO'];



$_SESSION['name']	=	$global_func->Select_val($Filstar_conn,WMS_LOOKUP,"USER","NAME","USERNAME = '{$_SESSION['username']}'");
class PDF extends FPDF 
{
	function HEADER_ko($THIS_TRXNO)
	{

		$this->Image("/var/www/html/wms/images/fdc101.jpg",10,10,50,20);
		$this->SetFont('Courier','B',9);
		$this->SetXY(10,20);$this->Cell(195,7,"FILSTAR DISTRIBUTORS CORPORATION",0,1,'C');
		$this->SetX(10);$this->Cell(195,5,"TRANSACTION : $THIS_TRXNO",0,1,'C');
		$this->SetX(10);$this->Cell(30,5,"",0,1,'L');
		
		$path =  "temp/";
		Generate_Barcode_Image($THIS_TRXNO, $path, "$THIS_TRXNO.png");
		$this->Image("$path$THIS_TRXNO.png",150,15,50,10);
		unlink("$path$THIS_TRXNO.png");
					
		$conn	=	ADONewConnection('mysqlt');
		$dbconn	=	$conn->Connect('192.168.255.10','root','','WMS_NEW');
		
		
		$get_hdr 	= "SELECT * FROM `TIANGGE_HDR` WHERE TRANSNO='{$THIS_TRXNO}'";
		$rsget_hdr	= $conn->Execute($get_hdr);
		
		while (!$rsget_hdr->EOF)
		{
			$type		= $rsget_hdr->fields['TYPE'];
			if($type == "1")
			{
				$type	= "CHOPPING";
			}
			else 
			{
				$type		=	"TIANGGE";
				$_SESSION['TYPE']		=	$type;
				$_SESSION['PRICEPOINT']	=	$rsget_hdr->fields['PRICEPOINT'];
			}

			$source			= $rsget_hdr->fields['SOURCE'];
			$sourcename		= Sel_val($conn,"WMS_LOOKUP","SOURCE","DESCRIPTION"," CODE='{$source}'");
			$status		= $rsget_hdr->fields['STATUS'];
			$scandate	= $rsget_hdr->fields['SCANDATE'];
						
			$this->SetX(20);$this->Cell(20,5,"TYPE",0,0,'L');$this->SetX(35);$this->Cell(20,5,": $type",0,0,'L');
			$this->SetX(130);$this->Cell(35,5,"SOURCE",0,0,'L');$this->SetX(150);$this->Cell(20,5,": $sourcename",0,1,'L');
			$this->SetX(20);$this->Cell(20,5,"STATUS",0,0,'L');$this->SetX(35);$this->Cell(20,5,": $status",0,0,'L');
			$this->SetX(130);$this->Cell(35,5,"SCAN DATE",0,0,'L');$this->SetX(150);$this->Cell(20,5,": $scandate",0,1,'L');
			$this->SetX(20);$this->Cell(35,5,"",0,1,'R');
			$this->SetFont('Courier','B',8);
			
			
			$rsget_hdr->MoveNext();	
		}
		
		$this->SetFont('Courier','B',11);
		
		
	
		$this->SetX(25);$this->Cell(10,5,"#","1",0,'C');
		$this->SetX(35);$this->Cell(30,5,"ITEM NO","1",0,'C');
		$this->SetX(65);$this->Cell(70,5,"DESCRIPTION","1",0,'C');
		$this->SetX(135);$this->Cell(20,5,"QTY","1",0,'C');
		$this->SetX(155);$this->Cell(20,5,"SRP","1",0,'C');
		$this->SetX(175);$this->Cell(20,5,"AMOUNT","1",1,'C');
		$this->SetX(170);$this->Cell(35,5,"",0,1,'C');
			
		
		
	}
	function BODY($THIS_TRXNO)
	{
				
		$conn	=	ADONewConnection('mysqlt');
		$dbconn	=	$conn->Connect('192.168.255.10','root','','WMS_NEW');
		
		
		$get_dtl 	= "SELECT * FROM `TIANGGE_DTL` WHERE TRANSNO='{$THIS_TRXNO}'";
		$rsget_dtl	= $conn->Execute($get_dtl);

		$counter_page	=	0;
		$cnt	=	1;	
		while (!$rsget_dtl->EOF)
		{
			$itemno		= $rsget_dtl->fields['SKUNO'];
			$desc		= Sel_val($conn,"FDCRMSlive","itemmaster","ItemDesc"," ItemNo='{$itemno}'");
			$qty		= $rsget_dtl->fields['QTY'];
			if ($_SESSION['TYPE']=='TIANGGE')
			{
			$srp		= $_SESSION['PRICEPOINT'];
			}
			$srp		= $rsget_dtl->fields['RETAILPRICE'];
			$amount		= $rsget_dtl->fields['GROSSAMOUNT'];
			
			$sumQTY			= $sumQTY + $qty;
			$sumSRP			= $sumSRP + $srp;
			$sumAmount		= $sumAmount + $amount;
			
			
			$this->SetFont('Courier','b',9);
			$this->SetX(25);$this->Cell(10,5,"$cnt",0,0,'C');
			$this->SetX(35);$this->Cell(30,5,"$itemno",0,0,'C');
			$this->SetX(65);$this->Cell(70,5,substr($desc,0,33),0,0,'|');
			$this->SetX(135);$this->Cell(20,5,"$qty",0,0,'C');
			$this->SetX(155);$this->Cell(20,5,"$srp",0,0,'C');
			$this->SetX(175);$this->Cell(20,5,"$amount",0,1,'R');
						
			if ($counter_page==34)
			{
				$counter_page=0;
				$this->AddPage();
				$this->HEADER_ko();
				$this->SetFont('Courier','b',9);
				$this->SetX(25);$this->Cell(10,5,"$cnt",0,0,'C');
				$this->SetX(35);$this->Cell(30,5,"$itemno",0,0,'C');
				$this->SetX(65);$this->Cell(70,5,substr($desc,0,33),0,0,'|');
				$this->SetX(135);$this->Cell(20,5,"$qty",0,0,'C');
				$this->SetX(155);$this->Cell(20,5,"$srp",0,0,'C');
				$this->SetX(175);$this->Cell(20,5,"$amount",0,1,'R');
			}
			$counter_page++;
			$cnt++;
			
			$rsget_dtl->MoveNext();	
		}
			$this->SetFont('Courier','B',12);
			$this->SetX(25);$this->Cell(10,5,"","T",0,'C');
			$this->SetX(35);$this->Cell(30,5,"","T",0,'C');
			$this->SetX(65);$this->Cell(70,5,"TOTAL	:","T",0,'R');
			$this->SetX(135);$this->Cell(20,5,$sumQTY,"T",0,'C');
			$this->SetX(155);$this->Cell(20,5,"","T",0,'C');
			$this->SetX(175);$this->Cell(20,5,number_format($sumAmount,2,".",","),"T",1,'R');
			

		
	}
	function Footer()
			{
//				$this->SetFont('Courier','B',10);
//				$this->SetXY(150,240);$this->Cell(45,3,"STOCK RECEIVED BY","T",0,'C');
//				$this->SetXY(150,150);$this->Cell(45,3,"",0,0,'C');
//				$this->SetXY(150,252);$this->Cell(45,3,"RECEIVED DATE/TIME:","T",0,'C');
				
				$this->SetFont('Courier','',9);
				$this->SetY(240);$this->Cell(0,10,'RePrinted By : '.$_SESSION['name'],0,1,'L');
				$this->SetY(245);$this->Cell(0,10,'Printed Date : '.date('Y-m-d'),0,1,'L');
				$this->SetY(250);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
				$this->SetFont('Courier','I',9);
				$this->SetY(255);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
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
$pdf= new PDF('P','mm','Letter');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak('auto',30);
$pdf->AddPage();
$pdf->HEADER_ko($THIS_TRXNO);
$pdf->BODY($THIS_TRXNO);
$pdf->Output();


?>
