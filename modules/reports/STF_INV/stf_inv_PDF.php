<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}
$selcusttype	=	$_GET["selcusttype"];
$txtcustno		=	$_GET["txtcustno"];
$dfrom			=	$_GET["dfrom"];
$dto			=	$_GET["dto"];
$tfrom			=	$_GET["tfrom"];
$tto			=	$_GET["tto"];
$rdoordertype	=	$_GET["rdoordertype"];
$selapha		=	$_GET["selalpha"];
if ($selcusttype == "NBS/TRADE")
{
	$CUSTTYPE_Q		=	"";
}
else 
{
	if ($selcusttype == "NBS")
	{
		$CUSTTYPE_Q		=	" AND C.CustomerBranchCode != ''";
	}
	else 
	{
		$CUSTTYPE_Q		=	" AND C.CustomerBranchCode = ''";
	}
}
if ($txtcustno == "")
{
	$CUST_Q		=	"";
}
else 
{
	$CUST_Q		=	" AND O.CustNumber = '{$txtcustno}'";
}
if($rdoordertype == "")
{
	$TYPE_Q		=	"";
}
else 
{
	$TYPE_Q		=	" AND O.TransType = '{$rdoordertype}'";
}
if($tfrom == "")
{
	$TIME_Q		=	"";
}
else 
{
	$TIME_Q		=	" AND O.Time_C BETWEEN '{$tfrom}' AND '{$tto}'";
}
if($selapha != "")
{
	$selapha_Q	=	" AND SUBSTRING(O.SOFNumber,1,3) LIKE '%{$selapha}%'";
}
$getorder	=	"SELECT O.SOFNumber,O.CustNumber,O.TransType,O.InvoiceNumber,O.Season,O.Date_C,D.NetAmount,D.GrossAmount,C.CustName,C.CustomerBranchCode
				 FROM FDCRMSlive.ordercycle AS O
				 LEFT JOIN FDCRMSlive.orderdetail AS D ON D.OrderNo = O.SOFNumber
				 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = O.CustNumber
				 WHERE O.Date_C BETWEEN '{$dfrom}' AND '{$dto}' $TIME_Q AND D.isDeleted = 'N' $CUST_Q $TYPE_Q $CUSTTYPE_Q $selapha_Q
				 ORDER BY C.CustName,O.InvoiceNumber";
//echo $getorder; exit();
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
			
			$CustNumber		=	$rsgetorder->fields["CustNumber"];
			$NetAmount		=	$rsgetorder->fields["NetAmount"];
			$GrossAmount	=	$rsgetorder->fields["GrossAmount"];
			$TransType		=	$rsgetorder->fields["TransType"];
			$CustName		=	$rsgetorder->fields["CustName"];
			$SOFNumber		=	$rsgetorder->fields["SOFNumber"];
			$InvoiceNumber	=	$rsgetorder->fields["InvoiceNumber"];
			$Season			=	$rsgetorder->fields["Season"];
			$Date_C			=	$rsgetorder->fields["Date_C"];
			$CustomerBranchCode			=	$rsgetorder->fields["CustomerBranchCode"];
			if ($CustomerBranchCode == "") {
				$customertype	=	"TRADE";
			}
			else 
			{
				$customertype	=	"NBS";
			}
			
			$arrOrder[$TransType][$customertype][$Season][$SOFNumber]["NetAmount"]		+=	$NetAmount;
			$arrOrder[$TransType][$customertype][$Season][$SOFNumber]["GrossAmount"]	+=	$GrossAmount;
			$arrOrder[$TransType][$customertype][$Season][$SOFNumber]["CustName"]		=	$CustNumber."-".$CustName;
			$arrOrder[$TransType][$customertype][$Season][$SOFNumber]["InvoiceNumber"]	=	$InvoiceNumber;
			$arrOrder[$TransType][$customertype][$Season][$SOFNumber]["Date_C"]			=	$Date_C;
			
			$rsgetorder->MoveNext();
		}
		class PDF extends FPDF 
			{
				function Header()
				{
					$dfrom		=	$_GET["dfrom"];
					$dto		=	$_GET["dto"];
					$rdoordertype	=	$_GET["rdoordertype"];
					$this->SetFont('Courier','B',12);
					$this->SetFont('Times','B',12);
					$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'CONFIRMED DELIVERIES',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'CUSTOMER TYPE: '.$_GET["selcusttype"],0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'PERIOD: '.date("F d, Y",strtotime($dfrom))." to ".date("F d, Y",strtotime($dto)),0,1,'C');
					$this->ln(5);
					
					$this->SetFont('Courier','B',10);
					$this->SetFont('Times','B',10);
					$this->SetX(4);$this->Cell(11,8,'TYPE',1,0,'C');
					$this->SetX(15);$this->Cell(15,8,'SEASON',1,0,'C');
					$this->SetX(30);$this->Cell(20,8,'SOF NO.',1,0,'C');
					$this->SetX(50);$this->Cell(15,8,'INV/STF',1,0,'C');
					$this->SetX(65);$this->Cell(25,4,'WHOESALE',"LTR",0,'C');
					$this->SetX(90);$this->Cell(25,4,'GROSS',"LTR",0,'C');
					$this->SetX(115);$this->Cell(20,4,'DELIVERY',"LTR",0,'C');
					$this->SetX(135);$this->Cell(76,8,'CUSTOMER',1,1,'C');
					
					$this->SetXY(65,39);$this->Cell(25,4,'AMOUNT',"LRB",0,'C');
					$this->SetXY(90,39);$this->Cell(25,4,'AMOUNT',"LRB",0,'C');
					$this->SetXY(115,39);$this->Cell(20,4,'DATE',"LRB",0,'C');
					$this->Ln(4);
				}
				
				function Footer()
				{
					$this->SetFont('Courier','',9);
					$this->SetFont('Times','',9);
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
			$pdf->SetFont('Courier','',9);
			$pdf->SetFont('Times','',9);

			$totamount		=	0;
			foreach ($arrOrder as $transactype=>$val1)
			{
				foreach ($val1 as $customtype=>$val2)
				{
					foreach ($val2 as $seas=>$val3)
					{
						$seasoncount 	= 0;
						$seasontotamt	= 0;
						$seasonGROSStotamt	= 0;
						foreach ($val3 as $SOFnum=>$val4)
						{
							$pdf->SetFont('Times','',9);
							$pdf->SetX(4);$pdf->Cell(11,5,$transactype,1,0,'C');
							$pdf->SetX(15);$pdf->Cell(15,5,$seas,1,0,'C');
							$pdf->SetX(30);$pdf->Cell(20,5,$SOFnum,1,0,'L');
							$pdf->SetX(50);$pdf->Cell(15,5,$val4["InvoiceNumber"],1,0,'C');
							$pdf->SetX(65);$pdf->Cell(25,5,number_format($val4["NetAmount"],2),1,0,'R');
							$pdf->SetX(90);$pdf->Cell(25,5,number_format($val4["GrossAmount"],2),1,0,'R');
							$pdf->SetX(115);$pdf->Cell(20,5,$val4["Date_C"],1,0,'C');
							$pdf->SetX(135);$pdf->Cell(76,5,substr($val4["CustName"],0,50),1,1,'L');
							$seasoncount++;
							$seasontotamt	+=	$val4["NetAmount"];
							$seasonGROSStotamt	+=	$val4["GrossAmount"];
							
							$GrandTotalCnt++;
							$GrandTotalAmt	+=	$val4["NetAmount"];
							$GrandTotalGROSSAmt	+=	$val4["GrossAmount"];
							if($seas == "Everyday")
							{
								$grandeverydaycnt++;
								$grandeverydayamt	+=	$val4["NetAmount"];
								$grandeverydayGROSSamt	+=	$val4["GrossAmount"];
							}
							else 
							{
								$grandseasonalcnt++;
								$grandseasonalamt	+=	$val4["NetAmount"];
								$grandseasonalGROSSamt	+=	$val4["GrossAmount"];
							}
							if($customtype == "NBS")
							{
								$grandNBScnt++;
								$grandNBSamt	+=	$val4["NetAmount"];
								$grandNBSGROSSamt	+=	$val4["GrossAmount"];
							}
							else 
							{
								$grandTRADEcnt++;
								$grandTRADEamt	+=	$val4["NetAmount"];
								$grandTRADEGROSSamt	+=	$val4["GrossAmount"];
							}
						}
						
						$pdf->SetFont('Times','B',9);
						$pdf->SetX(4);$pdf->Cell(207,5,"TOTAL Count: ".number_format($seasoncount),1,0,'L');
						$pdf->SetX(65);$pdf->Cell(25,5,number_format($seasontotamt,2),0,0,'R');
						$pdf->SetX(90);$pdf->Cell(25,5,number_format($seasonGROSStotamt,2),0,1,'R');
					}
				}
			}
			$pdf->Ln(5);
			$pdf->SetFont('Times','B',9);
			$pdf->SetX(4);$pdf->Cell(207,5,"NBS",1,0,'L');
			$pdf->SetX(20);$pdf->Cell(25,5,"Total Count:",0,0,'R');
			$pdf->SetX(45);$pdf->Cell(15,5,number_format($grandNBScnt),0,0,'R');
			$pdf->SetX(70);$pdf->Cell(30,5,"Total Wholesale Amount:",0,0,'R');
			$pdf->SetX(100);$pdf->Cell(25,5,number_format($grandNBSamt,2),0,0,'R');
			$pdf->SetX(130);$pdf->Cell(30,5,"Total Gross Amount:",0,0,'R');
			$pdf->SetX(160);$pdf->Cell(25,5,number_format($grandNBSGROSSamt,2),0,1,'R');
			
			
			$pdf->SetX(4);$pdf->Cell(207,5,"TRADE",1,0,'L');
			$pdf->SetX(20);$pdf->Cell(25,5,"Total Count:",0,0,'R');
			$pdf->SetX(45);$pdf->Cell(15,5,number_format($grandTRADEcnt),0,0,'R');
			$pdf->SetX(70);$pdf->Cell(30,5,"Total Wholesale Amount:",0,0,'R');
			$pdf->SetX(100);$pdf->Cell(25,5,number_format($grandTRADEamt,2),0,0,'R');
			$pdf->SetX(130);$pdf->Cell(30,5,"Total Gross Amount:",0,0,'R');
			$pdf->SetX(160);$pdf->Cell(25,5,number_format($grandTRADEGROSSamt,2),0,1,'R');
			
			$pdf->SetX(4);$pdf->Cell(207,5,"EVERYDAY",1,0,'L');
			$pdf->SetX(20);$pdf->Cell(25,5,"Total Count:",0,0,'R');
			$pdf->SetX(45);$pdf->Cell(15,5,number_format($grandeverydaycnt),0,0,'R');
			$pdf->SetX(70);$pdf->Cell(30,5,"Total Wholesale Amount:",0,0,'R');
			$pdf->SetX(100);$pdf->Cell(25,5,number_format($grandeverydayamt,2),0,0,'R');
			$pdf->SetX(130);$pdf->Cell(30,5,"Total Gross Amount:",0,0,'R');
			$pdf->SetX(160);$pdf->Cell(25,5,number_format($grandeverydayGROSSamt,2),0,1,'R');
			
			$pdf->SetX(4);$pdf->Cell(207,5,"SEASONAL",1,0,'L');
			$pdf->SetX(20);$pdf->Cell(25,5,"Total Count:",0,0,'R');
			$pdf->SetX(45);$pdf->Cell(15,5,number_format($grandseasonalcnt),0,0,'R');
			$pdf->SetX(70);$pdf->Cell(30,5,"Total Wholesale Amount:",0,0,'R');
			$pdf->SetX(100);$pdf->Cell(25,5,number_format($grandseasonalamt,2),0,0,'R');
			$pdf->SetX(130);$pdf->Cell(30,5,"Total Gross Amount:",0,0,'R');
			$pdf->SetX(160);$pdf->Cell(25,5,number_format($grandseasonalGROSSamt,2),0,1,'R');
			
			$pdf->SetX(4);$pdf->Cell(207,5,"GRAND TOTAL",1,0,'L');
			$pdf->SetX(20);$pdf->Cell(25,5,"COUNT:",0,0,'R');
			$pdf->SetX(45);$pdf->Cell(15,5,number_format($GrandTotalCnt),0,0,'R');
			$pdf->SetX(70);$pdf->Cell(30,5,"WHOLESALE AMOUNT:",0,0,'R');
			$pdf->SetX(100);$pdf->Cell(25,5,number_format($GrandTotalAmt,2),0,0,'R');
			$pdf->SetX(130);$pdf->Cell(30,5,"GROSS AMOUNT:",0,0,'R');
			$pdf->SetX(160);$pdf->Cell(25,5,number_format($GrandTotalGROSSAmt,2),0,1,'R');
			echo $pdf->Output();
	}
	else 
	{
		echo "No records found.";
	}
}
?>