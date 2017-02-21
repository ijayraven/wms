<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}
$selcusttype	=	$_GET["selcusttype"];
$sel_seasons	=	$_GET["sel_seasons"];
$sel_class		=	$_GET["sel_class"];
$txtcustno		=	$_GET["txtcustno"];
$dfrom			=	$_GET["dfrom"];
$dto			=	$_GET["dto"];
$rdoordertype	=	$_GET["rdoordertype"];
if($selcusttype == "NBS")
{
	$selcusttype_Q	=	" AND C.CustomerBranchCode !=''";
}
if($selcusttype == "TRADE")
{
	$selcusttype_Q	=	" AND C.CustomerBranchCode =''";
}
if($sel_class != "")
{
	if($sel_class == "EVERYDAY")
	{
		$SEASON_Q	=	" AND (O.SOF NOT REGEXP 'M' AND O.SOF NOT REGEXP 'ML' AND O.SOF NOT REGEXP 'F' AND O.SOF NOT REGEXP 'FL' AND O.SOF NOT REGEXP 'XN' AND O.SOF NOT REGEXP 'XL' AND O.SOF NOT REGEXP 'X' AND O.SOF NOT REGEXP 'H' AND O.SOF NOT REGEXP 'HL')";
	}
	else 
	{
		if($sel_seasons != "")
		{
			$SEASON_Q	=	" AND O.SOF REGEXP '$sel_seasons'";
		}
		else 
		{
			$SEASON_Q	=	" AND (O.SOF REGEXP 'M' OR O.SOF  REGEXP 'ML' OR O.SOF  REGEXP 'F' OR O.SOF  REGEXP 'FL' OR O.SOF  REGEXP 'XN' OR O.SOF  REGEXP 'XL' OR O.SOF  REGEXP 'X' OR O.SOF  REGEXP 'H' OR O.SOF  REGEXP 'HL')";
		}
	}
}
if ($txtcustno == "")
{
	$CUST_Q		=	"";
}
else 
{
	$CUST_Q		=	" AND O.CUSTNO = '{$txtcustno}'";
}
if($rdoordertype == "")
{
	$TYPE_Q		=	"";
}
else 
{
	$TYPE_Q		=	" AND O.DOCTYPE = '{$rdoordertype}'";
}
$getorder	=	"SELECT O.SOF,O.CUSTNO,O.DOCTYPE, O.GROSSAMOUNT,O.NETAMOUNT,SUM(D.RECEIVEDQTY) as RECEIVEDQTY,C.CustName
				 FROM WMS_NEW.CONFIRMDELIVERY_HDR AS O
				 LEFT JOIN WMS_NEW.CONFIRMDELIVERY_DTL AS D ON D.SOF = O.SOF
				 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = O.CUSTNO
				 WHERE O.CONFIRMDELDATE BETWEEN '{$dfrom}' AND '{$dto}' $CUST_Q $TYPE_Q $selcusttype_Q $SEASON_Q
				 GROUP BY O.SOF
				 ORDER BY C.CustName";
//exit();
$rsgetorder	=	$Filstar_conn->Execute($getorder);
if($rsgetorder == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
}
else 
{
	if($rsgetorder->RecordCount() > 0)
	{
		$arrOrder	=	array();
		while (!$rsgetorder->EOF) {
			
			$SOF			=	$rsgetorder->fields["SOF"];
			$CUSTNO			=	$rsgetorder->fields["CUSTNO"];
			$GROSSAMOUNT	=	$rsgetorder->fields["GROSSAMOUNT"];
			$NETAMOUNT		=	$rsgetorder->fields["NETAMOUNT"];
			$RECEIVEDQTY	=	$rsgetorder->fields["RECEIVEDQTY"];
			$DOCTYPE		=	$rsgetorder->fields["DOCTYPE"];
			$CustName		=	$rsgetorder->fields["CustName"];
			if(strpos($SOF,"M"))
			{
				$SEASON = "MOTHER'S DAY";
			}
			else if(strpos($SOF,"F"))
			{
				$SEASON = "FATHER'S DAY";
			}
			else if(strpos($SOF,"X"))
			{
				$SEASON = "CHRISTMAS";
			}
			else if(strpos($SOF,"H"))
			{
				$SEASON = "VALENTINES";
			}
			else 
			{
				$SEASON = "EVERYDAY";
			}
			$arrOrder[$CUSTNO]["SOF"]			=	$SOF;
			$arrOrder[$CUSTNO]["GROSSAMOUNT"]	+=	$GROSSAMOUNT;
			$arrOrder[$CUSTNO]["NETAMOUNT"]		+=	$NETAMOUNT;
			$arrOrder[$CUSTNO]["RECEIVEDQTY"]	+=	$RECEIVEDQTY;
			$arrOrder[$CUSTNO]["DOCTYPE"]		=	$DOCTYPE;
			$arrOrder[$CUSTNO]["SEASON"]		=	$SEASON;
			$arrOrder[$CUSTNO]["CustName"]		=	$CustName;
			
			$rsgetorder->MoveNext();
		}
//		print_r($arrOrder); exit();
		class PDF extends FPDF 
			{
				function Header()
				{
					$dfrom		=	$_GET["dfrom"];
					$dto		=	$_GET["dto"];
					$rdoordertype	=	$_GET["rdoordertype"];
					$this->SetFont('Courier','B',12);
					$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'CONFIRMED DELIVERIES',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'SKU SUMMARY',0,1,'C');
//					if($rdoordertype !="")
//					{
//						$this->SetX(10);$this->Cell(0,5,'ORDER TYPE: '.$rdoordertype,0,1,'C');
//					}
					$this->SetX(10);$this->Cell(0,5,'PERIOD: '.date("F d, Y",strtotime($dfrom))." to ".date("F d, Y",strtotime($dto)),0,1,'C');
					$this->ln(5);
					$this->SetFont('Courier','B',10);
					$this->SetX(5);$this->Cell(84,6,'CUSTOMER',1,0,'C');
					$this->SetX(89);$this->Cell(11,3,'ORDER',"LTR",0,'C');
					$this->SetX(100);$this->Cell(25,3,'SEASON',"LTR",0,'C');
					$this->SetX(125);$this->Cell(15,3,'TOTAL',"LTR",0,'C');
					$this->SetX(140);$this->Cell(35,3,'TOTAL WHOLESALE',"LTR",0,'C');
					$this->SetX(175);$this->Cell(35,3,'TOTAL GROSS',"LTR",1,'C');
					
					
					$this->SetX(89);$this->Cell(11,3,'TYPE',"LRB",0,'C');
					$this->SetX(125);$this->Cell(15,3,'SKU QTY',"LRB",0,'C');
					$this->SetX(140);$this->Cell(35,3,'AMOUNT',"LRB",0,'C');
					$this->SetX(175);$this->Cell(35,3,'AMOUNT',"LRB",1,'C');
				}
				
				function Footer()
				{
					$this->SetFont('Courier','',9);
					$this->SetY(340);$this->Cell(0,10,'Printed Date  : '.date('Y-m-d'),0,1,'L');
					$this->SetY(343);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
					$this->SetY(343);$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
				}
			}
			
			
			$pdf= new PDF('P','mm','legal');
			$pdf->Open();
			$pdf->AliasNbPages();
			$pdf->SetAutoPageBreak('auto',15);
			$pdf->AddPage();
			
			$pdf->SetFont('Courier','',10);
			
			$totsku			=	0;
			$totwholesale	=	0;
			$totgross		=	0;
			foreach ($arrOrder as $custnum=>$val1)
			{
				if($val1["DOCTYPE"] == "INVOICE")
				{
					$TYPE	=	"INV";
				}
				else 
				{
					$TYPE	=	$val1["DOCTYPE"];
				}
				$pdf->SetX(5);$pdf->Cell(84,5,$custnum."-".substr($val1["CustName"],0,30),1,0,'L');
				$pdf->SetX(89);$pdf->Cell(11,5,$TYPE,1,0,'C');
				$pdf->SetX(100);$pdf->Cell(25,5,$val1["SEASON"],1,0,'C');
				$pdf->SetX(125);$pdf->Cell(15,5,number_format($val1["RECEIVEDQTY"]),1,0,'R');
				$pdf->SetX(140);$pdf->Cell(35,5,number_format($val1["NETAMOUNT"],2),1,0,'R');
				$pdf->SetX(175);$pdf->Cell(35,5,number_format($val1["GROSSAMOUNT"],2),1,1,'R');
				$totsku			+=	$val1["RECEIVEDQTY"];
				$totwholesale	+=	$val1["NETAMOUNT"];
				$totgross		+=	$val1["GROSSAMOUNT"];
			}
			$pdf->ln(5);
			$pdf->SetFont('Courier','B',10);
			$pdf->SetX(5);$pdf->Cell(90,15,"TOTAL",1,0,'C');
			$pdf->SetX(95);$pdf->Cell(45,5,"RELEASE QTY",1,0,'L');
			$pdf->SetX(140);$pdf->Cell(70,5,number_format($totsku),1,1,'R');
			$pdf->SetX(95);$pdf->Cell(45,5,"WHOLESALE AMOUNT",1,0,'L');
			$pdf->SetX(140);$pdf->Cell(70,5,number_format($totwholesale,2),1,1,'R');
			$pdf->SetX(95);$pdf->Cell(45,5,"GROSS AMOUNT",1,0,'L');
			$pdf->SetX(140);$pdf->Cell(70,5,number_format($totgross,2),1,1,'R');
			echo $pdf->Output();
	}
	else 
	{
		echo "No records found.";
	}
}
?>