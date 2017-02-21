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
$txtSKU		=	$_POST['txtSKU'];
if ($txtSKU!='')
{
	$txtSKU			=	$_POST['txtSKU'];
	$selRemarks		=	$_POST['selRemarks'];
	$customercode	=	$_POST['customercode'];
	$selDateType	=	$_POST['selDateType'];
	$selstatus		=	$_POST['selstatus'];
	$txtDateFrom	= 	$_POST['dfrom'];
	$txtDateTo		= 	$_POST['dto'];
	$reportType		= 	$_POST['reportType'];
	$rdoType		= 	$_POST['rdoType'];
	
	$results = array();
	$results2 = array();
			
	$get_trans		= "SELECT IATRANSNO,SKUNO FROM  WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE SKUNO='{$txtSKU}'";
	$rsget_trans	= $conn->Execute($get_trans);
	
	while (!$rsget_trans->EOF)
	{
		$IATRANSNO_		= $rsget_trans->fields['IATRANSNO'];
		$results2[]		= $IATRANSNO_;
		
		$rsget_trans->MoveNext();	
	}
	
	$_IATRANSNO	= implode("','",$results2);
	
	if ($rdoType == "NBS")
	{
		$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE (CustomerBranchCode!='' or NBSnewBranchCode != '')";
		$rs_cust	= $conn->Execute($qry_cust);
	}
	else 
	{
		$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE CustomerBranchCode=''";
		$rs_cust	= $conn->Execute($qry_cust);
	}
	
	while (!$rs_cust->EOF)
	{
		$cust_no	= $rs_cust->fields['CustNo'];
		$results[] = $cust_no;
		$rs_cust->MoveNext();	
	}
		$_cust	= implode("','",$results);
		$CUST_VALID	= " AND CUSTNO in ('{$_cust}')";

	$get_hdr		= "SELECT `IATRANSNO`, `CUSTNO`, `STATUS`, `ADDEDDATE`, `POSTEDDATE`
					FROM WMS_NEW.INVENTORYADJUSTMENT_HDR where IATRANSNO in ('{$_IATRANSNO}') AND `{$selDateType}` between '{$txtDateFrom}' AND '{$txtDateTo}' $CUST_VALID";
	if (!empty($customercode))
	{
		$get_hdr	.=	" AND `CUSTNO` = '{$customercode}' ";
	}
	if ($selstatus != 'ALL')
	{
		$get_hdr	.=	" AND `STATUS` = '{$selstatus}' ";
	}
	$rsget_hdr		=	$conn->Execute($get_hdr);
	
}
else 
{
	$selRemarks		=	$_POST['selRemarks'];
	$customercode	=	$_POST['customercode'];
	$selDateType	=	$_POST['selDateType'];
	$selstatus		=	$_POST['selstatus'];
	$txtDateFrom	= 	$_POST['dfrom'];
	$txtDateTo		= 	$_POST['dto'];
	$reportType		= 	$_POST['reportType'];
	$rdoType		= 	$_POST['rdoType'];
	
	$qry_sel	=	"SELECT `IATRANSNO`, `CUSTNO`, `STATUS`, `ADDEDDATE`, `POSTEDDATE`
					FROM WMS_NEW.INVENTORYADJUSTMENT_HDR where `{$selDateType}` between '{$txtDateFrom}' AND '{$txtDateTo}'";
	
			if (!empty($customercode))
			{
				$qry_sel	.=	" AND `CUSTNO` = '{$customercode}' ";
			}
			if ($selstatus != 'ALL')
			{
				$qry_sel	.=	" AND `STATUS` = '{$selstatus}' ";
			}
	$rs_qry_sel		= $conn->Execute($qry_sel);
	
	if ($rs_qry_sel == false)
	{
		echo $conn->ErrorMsg()."::".__LINE__;exit();
	}
	$cnt	=	$rs_qry_sel->RecordCount();
}


$_SESSION['name']	=	$global_func->Select_val($Filstar_conn,WMS_LOOKUP,"USER","NAME","USERNAME = '{$_SESSION['username']}'");
class INVENTORY extends FPDF 
{
	function HEADER_ko()
	{
		$selDateType	=	$_POST['selDateType'];
		$selstatus		=	$_POST['selstatus'];
		$txtDateFrom	= 	$_POST['dfrom'];
		$txtDateTo		= 	$_POST['dto'];
		$reportType		= 	$_POST['reportType'];
		$rdoType		= 	$_POST['rdoType'];
		if ($selDateType == "ADDEDDATE")
		{
			$selDateType = "I.A DATE";
		}
		else 
		{
			$selDateType = "POSTED DATE";
		}
		$this->Image("/var/www/html/wms/images/fdc101.jpg",10,12,50,20);
		$this->SetFont('Courier','B',9);
		$this->SetX(10);$this->Cell(195,7,"FILSTAR DISTRIBUTORS CORPORATION",0,1,'C');
		$this->SetX(10);$this->Cell(195,5,"INVENTORY ADJUSTMENT",0,1,'C');
		//$this->SetX(10);$this->Cell(195,5,"CONFIRM DELIVERY",0,1,'C');
		$this->SetX(10);$this->Cell(195,5,"$rdoType TRANSACTION SUMMARY",0,1,'C');
		$this->SetX(10);$this->Cell(195,5,"$selDateType:  ".date("F j, Y",strtotime($txtDateFrom))." To ".date("F j, Y",strtotime($txtDateTo))."",0,1,'C');
		$this->SetX(10);$this->Cell(30,5,"",0,1,'L');
		
		$conn	=	ADONewConnection('mysqlt');
		$dbconn	=	$conn->Connect('192.168.255.10','root','','WMS_NEW');
		
		$txtSKU		=	$_POST['txtSKU'];
		if ($txtSKU!='')
		{
			
			$qry_sku		= "SELECT ItemNo,ItemDesc from  FDCRMSlive.itemmaster WHERE ItemNo={$txtSKU}";
			$rs_qry_sku		= $conn->Execute($qry_sku);
			$desc	= $rs_qry_sku->fields['ItemDesc'];
			
			$this->SetFont('Courier','B',11);
			
			$this->SetX(20);$this->Cell(25,5,"SKU #:",0,0,'R');$this->SetX(55);$this->Cell(35,5,"$txtSKU",0,1,'L');
			$this->SetX(20);$this->Cell(25,5,"DESCRIPTION:",0,0,'R');$this->SetX(55);$this->Cell(35,5,"$desc",0,1,'L');
			$this->SetX(20);$this->Cell(25,5,"",0,1,'R');
			$this->SetFont('Courier','B',8);

			$this->SetX(10);$this->Cell(7,5,"#","1",0,'C');
			$this->SetX(17);$this->Cell(25,5,"I.A. No.","1",0,'C');
			$this->SetX(42);$this->Cell(61,5,"CUSTOMER","1",0,'C');
			$this->SetX(103);$this->Cell(18,5,"STATUS","1",0,'C');
			$this->SetX(121);$this->Cell(15,5,"I.A. QTY","1",0,'C');
			$this->SetX(136);$this->Cell(20,5,"ADJ TYPE","1",0,'C');
			$this->SetX(156);$this->Cell(25,5,"REFNO","1",0,'C');
			$this->SetX(181);$this->Cell(25,5,"REMARKS","1",1,'C');
		}
		else 
		{
			$this->SetFont('Courier','B',8);
			$this->SetX(10);$this->Cell(20,5,"",0,1,'C');
			$this->SetX(10);$this->Cell(10,5,"#","1",0,'C');
			$this->SetX(17);$this->Cell(25,5,"I.A. No.","1",0,'C');
			$this->SetX(42);$this->Cell(61,5,"CUSTOMER","1",0,'C');
			$this->SetX(103);$this->Cell(18,5,"STATUS","1",0,'C');
			$this->SetX(121);$this->Cell(15,5,"I.A. QTY","1",0,'C');
			$this->SetX(136);$this->Cell(20,5,"ADJ TYPE","1",0,'C');
			$this->SetX(156);$this->Cell(25,5,"REFNO","1",0,'C');
			$this->SetX(181);$this->Cell(25,5,"REMARKS","1",1,'C');
			//$this->SetX(156);$this->Cell(41,5,"REMARKS","1",1,'C');
		}
			
		
		
	}
	function BODY()
	{
				
		$conn	=	ADONewConnection('mysqlt');
		$dbconn	=	$conn->Connect('192.168.255.10','root','','WMS_NEW');
		
		$txtSKU			=	$_POST['txtSKU'];
		
		if ($txtSKU!='')
		{
			$txtSKU			=	$_POST['txtSKU'];
			$selRemarks		=	$_POST['selRemarks'];
			$customercode	=	$_POST['customercode'];
			$selDateType	=	$_POST['selDateType'];
			$selstatus		=	$_POST['selstatus'];
			$txtDateFrom	= 	$_POST['dfrom'];
			$txtDateTo		= 	$_POST['dto'];
			$reportType		= 	$_POST['reportType'];
			$rdoType		= 	$_POST['rdoType'];
			$results = array();
			$results2 = array();
					
			$get_trans		= "SELECT IATRANSNO,SKUNO FROM  WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE SKUNO={$txtSKU}";
			$rsget_trans	= $conn->Execute($get_trans);
			
			while (!$rsget_trans->EOF)
			{
				$IATRANSNO_		= $rsget_trans->fields['IATRANSNO'];
				$results2[]		= $IATRANSNO_;
				
				$rsget_trans->MoveNext();	
			}
			
			$_IATRANSNO	= implode("','",$results2);
			
			if ($rdoType == "NBS")
			{
				$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE CustomerBranchCode!=''";
				$rs_cust	= $conn->Execute($qry_cust);
			}
			else 
			{
				$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE CustomerBranchCode=''";
				$rs_cust	= $conn->Execute($qry_cust);
			}
			
			while (!$rs_cust->EOF)
			{
				$cust_no	= $rs_cust->fields['CustNo'];
				$results[] = $cust_no;
				$rs_cust->MoveNext();	
			}
				$_cust	= implode("','",$results);
				$CUST_VALID	= " AND CUSTNO in ('{$_cust}')";
		
			$get_hdr		= "SELECT `IATRANSNO`, `CUSTNO`, `STATUS`, `ADDEDDATE`, `POSTEDDATE`,REFN0,REFTYPE
							FROM WMS_NEW.INVENTORYADJUSTMENT_HDR where IATRANSNO in ('{$_IATRANSNO}') AND `{$selDateType}` between '{$txtDateFrom}' AND '{$txtDateTo}' $CUST_VALID";
			if (!empty($customercode))
			{
				$get_hdr	.=	" AND `CUSTNO` = '{$customercode}' ";
			}
			if ($selstatus != 'ALL')
			{
				$get_hdr	.=	" AND `STATUS` = '{$selstatus}' ";
			}
			
			$rsget_hdr		=	$conn->Execute($get_hdr);
			
			$counter_page	=	0;
			$cnt	=	1;
			while (!$rsget_hdr->EOF)
			{
				$IATRANSNO	=	$rsget_hdr->fields['IATRANSNO'];
				$CUSTNO		=	$rsget_hdr->fields['CUSTNO'];
				$REFN0		=	$rsget_hdr->fields['REFN0'];
				$REFTYPE	=	substr($rsget_hdr->fields['REFTYPE'],0,3);
				$STATUS		=	$rsget_hdr->fields['STATUS'];
				$ADDEDDATE	=	$rsget_hdr->fields['ADDEDDATE'];
				$custname		= Sel_val($conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
				$aRemarkss		=	"";
					
				$get_remarks	= "SELECT REMARKS,MOVEMENT FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSNO='{$IATRANSNO}' GROUP BY REMARKS";
				$rs_remarks		= $conn->Execute($get_remarks);
				if ($rs_remarks==false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;exit();
				}
				while (!$rs_remarks->EOF) 
				{
					if ($aRemarkss=='') 
					{
						$aRemarkss	=	$rs_remarks->fields['REMARKS'];
					}
					else 
					{
						$aRemarkss	=	$aRemarkss.",".$rs_remarks->fields['REMARKS'];
					}
					$MOVEMENT	=	$rs_remarks->fields['MOVEMENT'];
					$rs_remarks->MoveNext();
				}
				$aRemarkss	= Sel_val($conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE='{$aRemarkss}'");
				$IAQTY	= Sel_val($conn,"WMS_NEW","INVENTORYADJUSTMENT_DTL","IAQTY","IATRANSNO='{$IATRANSNO}'");
				
				$total_IA	+=	$IAQTY;
				
				
				$this->SetFont('Courier','b',7.5);
				$this->SetX(10);$this->Cell(10,5,"$cnt",0,0,'C');
				$this->SetX(17);$this->Cell(25,5,"$IATRANSNO",0,0,'C');
				$this->SetX(42);$this->Cell(61,5,"$CUSTNO-".substr($custname,0,29)."",0,0,'|');
				$this->SetX(103);$this->Cell(18,5,$STATUS,0,0,'L');
				$this->SetX(121);$this->Cell(15,5,"$IAQTY",0,0,'C');
				$this->SetX(136);$this->Cell(20,5,$MOVEMENT,"0",0,'C');
				$this->SetX(157);$this->Cell(20,5,$REFTYPE."/".$REFN0,"0",0,'C');
				$this->SetX(181);$this->Cell(30,5,"$aRemarkss",0,1,'L');
							
				if ($counter_page==34)
				{
					$counter_page=0;
					$this->AddPage();
					$this->HEADER_ko();
					$this->SetFont('Courier','b',7.5);
					$this->SetX(10);$this->Cell(10,5,"$cnt",0,0,'C');
					$this->SetX(17);$this->Cell(25,5,"$IATRANSNO",0,0,'C');
					$this->SetX(42);$this->Cell(61,5,"$CUSTNO-".substr($custname,0,29)."",0,0,'|');
					$this->SetX(103);$this->Cell(18,5,$STATUS,0,0,'L');
					$this->SetX(121);$this->Cell(15,5,"$IAQTY",0,0,'C');
					$this->SetX(136);$this->Cell(20,5,$MOVEMENT,"0",0,'C');
					$this->SetX(156);$this->Cell(20,5,$REFTYPE."/".$REFN0,"0",0,'C');
					$this->SetX(181);$this->Cell(30,5,"$aRemarkss",0,1,'L');
				}
				$counter_page++;
				$cnt++;
				$rsget_hdr->MoveNext();
			}
			$this->SetX(103);$this->Cell(18,5,"TOTAL",0,0,'L');
			$this->SetX(121);$this->Cell(15,5,$total_IA,0,0,'C');
			
		}
		else 
		{
			$selRemarks		=	$_POST['selRemarks'];
			$customercode	=	$_POST['customercode'];
			$selDateType	=	$_POST['selDateType'];
			$selstatus		=	$_POST['selstatus'];
			$txtDateFrom	= 	$_POST['dfrom'];
			$txtDateTo		= 	$_POST['dto'];
			$reportType		= 	$_POST['reportType'];
			$rdoType		= 	$_POST['rdoType'];
			$results = array();
			
			if ($rdoType == "NBS")
			{
				$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE CustomerBranchCode!=''";
				$rs_cust	= $conn->Execute($qry_cust);
			}
			else 
			{
				$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE CustomerBranchCode=''";
				$rs_cust	= $conn->Execute($qry_cust);
			}
			
			while (!$rs_cust->EOF)
			{
				$cust_no	= $rs_cust->fields['CustNo'];
				$results[] = $cust_no;
				$rs_cust->MoveNext();	
			}
				$_cust	= implode("','",$results);
				$CUST_VALID	= " AND CUSTNO in ('{$_cust}')";
				
			$qry_sel	=	"SELECT `IATRANSNO`, `CUSTNO`, `STATUS`, `ADDEDDATE`, `POSTEDDATE`,REFN0,REFTYPE
									FROM WMS_NEW.INVENTORYADJUSTMENT_HDR where `{$selDateType}` between '{$txtDateFrom}' AND '{$txtDateTo}' $CUST_VALID";
					if (!empty($customercode))
					{
						$qry_sel	.=	" AND `CUSTNO` = '{$customercode}' ";
					}
					if ($selstatus != 'ALL')
					{
						$qry_sel	.=	" AND `STATUS` = '{$selstatus}' ";
					}
			$rs_qry_sel		= $conn->Execute($qry_sel);
			if ($rs_qry_sel == false)
			{
				echo $conn->ErrorMsg()."::".__LINE__;exit();
			}
			
			
			$counter_page	=	0;
			$cnt	=	1;
			while (!$rs_qry_sel->EOF)
			{
				$IATRANSNO	=	$rs_qry_sel->fields['IATRANSNO'];
				$CUSTNO		=	$rs_qry_sel->fields['CUSTNO'];
				$REFN0		=	$rs_qry_sel->fields['REFN0'];
				$REFTYPE	=	substr($rs_qry_sel->fields['REFTYPE'],0,3);
				$STATUS		=	$rs_qry_sel->fields['STATUS'];
				$ADDEDDATE	=	$rs_qry_sel->fields['ADDEDDATE'];
				$custname		= Sel_val($conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
				$aRemarkss		=	"";
					
				$get_remarks	= "SELECT REMARKS,MOVEMENT FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSNO='{$IATRANSNO}' GROUP BY REMARKS";
				$rs_remarks		= $conn->Execute($get_remarks);
				if ($rs_remarks==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				while (!$rs_remarks->EOF) 
				{
					if ($aRemarkss=='') 
					{
						$aRemarkss	=	$rs_remarks->fields['REMARKS'];
					}
					else 
					{
						$aRemarkss	=	$aRemarkss.",".$rs_remarks->fields['REMARKS'];
					}
					$MOVEMENT	=	$rs_remarks->fields['MOVEMENT'];
					$rs_remarks->MoveNext();
				}
				$aRemarkss	= Sel_val($conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE='{$aRemarkss}'");
				$IAQTY	= Sel_val($conn,"WMS_NEW","INVENTORYADJUSTMENT_DTL","IAQTY","IATRANSNO='{$IATRANSNO}'");
				
				$total_IA	+=	$IAQTY;
				
				$this->SetFont('Courier','b',7.5);
				$this->SetX(10);$this->Cell(10,5,"$cnt",0,0,'C');
				$this->SetX(17);$this->Cell(25,5,"$IATRANSNO",0,0,'C');
				$this->SetX(42);$this->Cell(61,5,"$CUSTNO-".substr($custname,0,29)."",0,0,'|');
				$this->SetX(103);$this->Cell(18,5,$STATUS,0,0,'L');
				$this->SetX(121);$this->Cell(15,5,"$IAQTY",0,0,'C');
				$this->SetX(136);$this->Cell(20,5,$MOVEMENT,"0",0,'C');
				$this->SetX(156);$this->Cell(20,5,$REFTYPE."/".$REFN0,"0",0,'C');
				$this->SetX(181);$this->Cell(30,5,"$aRemarkss",0,1,'L');
				
				if ($counter_page==34)
				{
					$counter_page=0;
					$this->AddPage();
					$this->HEADER_ko();
					$this->SetFont('Courier','b',7.5);
					$this->SetX(10);$this->Cell(10,5,"$cnt",0,0,'C');
					$this->SetX(17);$this->Cell(25,5,"$IATRANSNO",0,0,'C');
					$this->SetX(42);$this->Cell(61,5,"$CUSTNO-".substr($custname,0,29)."",0,0,'|');
					$this->SetX(103);$this->Cell(18,5,$STATUS,0,0,'L');
					$this->SetX(121);$this->Cell(15,5,"$IAQTY",0,0,'C');
					$this->SetX(136);$this->Cell(20,5,$MOVEMENT,"0",0,'C');
					$this->SetX(156);$this->Cell(20,5,$REFTYPE."/".$REFN0,"0",0,'C');
					$this->SetX(181);$this->Cell(30,5,"$aRemarkss",0,1,'L');
				}
				$counter_page++;
				$cnt++;
				$rs_qry_sel->MoveNext();
			}
			
			$this->SetX(103);$this->Cell(18,5,"TOTAL",0,0,'L');
			$this->SetX(121);$this->Cell(15,5,$total_IA,0,0,'C');
		}
		
	}
	function Footer()
			{
//				$this->SetFont('Courier','B',10);
//				$this->SetXY(150,240);$this->Cell(45,3,"STOCK RECEIVED BY","T",0,'C');
//				$this->SetXY(150,150);$this->Cell(45,3,"",0,0,'C');
//				$this->SetXY(150,252);$this->Cell(45,3,"RECEIVED DATE/TIME:","T",0,'C');
				
				$this->SetFont('Courier','',9);
				$this->SetY(240);$this->Cell(0,10,'Printed By   : '.$_SESSION['name'],0,1,'L');
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
$pdf= new INVENTORY('P','mm','Letter');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak('auto',30);
$pdf->AddPage();
$pdf->HEADER_ko();
$pdf->BODY();
$pdf->Output();


?>
