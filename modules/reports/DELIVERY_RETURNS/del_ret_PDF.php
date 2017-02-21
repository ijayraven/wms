<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}
$txtcustno		=	$_GET["txtcustno"];
$txtcustname	=	$_GET["txtcustname"];
$itemno			=	$_GET["txtitemno"];
$deldfrom		=	$_GET["deldfrom"];
$deldto			=	$_GET["deldto"];
$retdfrom		=	$_GET["retdfrom"];
$retdto			=	$_GET["retdto"];
if($itemno != "")
{
	$itemno_Q	=	"AND D.Item = '{$itemno}'";
}
$getorder	=	"SELECT O.CustNumber,O.TransType,O.Date_C,D.NetAmount,D.GrossAmount,C.CustName,D.Item,D.ReleaseQty
				 FROM FDCRMSlive.ordercycle AS O
				 LEFT JOIN FDCRMSlive.orderdetail AS D ON D.OrderNo = O.SOFNumber
				 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = O.CustNumber
				 WHERE O.Date_C BETWEEN '{$deldfrom}' AND '{$deldto}' AND D.isDeleted = 'N' AND O.CustNumber = '{$txtcustno}' $itemno_Q
				 ORDER BY D.Item";
//$getorder	=	"SELECT O.SOFNumber,O.CustNumber,O.TransType,O.InvoiceNumber,O.Season,O.Date_C,D.NetAmount,D.GrossAmount,C.CustName,C.CustomerBranchCode
//				 FROM FDCRMSlive.ordercycle AS O
//				 LEFT JOIN FDCRMSlive.orderdetail AS D ON D.OrderNo = O.SOFNumber
//				 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = O.CustNumber
//				 WHERE O.Date_C BETWEEN '{$deldfrom}' AND '{$deldto}' AND D.isDeleted = 'N' AND O.CustNumber = '{$txtcustno}' AND D.Item = '{$itemno}'
//				 ORDER BY D.Item";

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
			$Date_C			=	$rsgetorder->fields["Date_C"];
			$Item			=	$rsgetorder->fields["Item"];
			$ReleaseQty			=	$rsgetorder->fields["ReleaseQty"];
			
			$arrOrder[$Item]["NetAmount"]		+=	$NetAmount;
			$arrOrder[$Item]["GrossAmount"]		+=	$GrossAmount;
			$arrOrder[$Item]["CustName"]		=	$CustNumber."-".$CustName;
//			$arrOrder[$Item]["Date_C"]			=	$Date_C;
//			$arrOrder[$Item]["TransType"]		=	$TransType;
			$arrOrder[$Item]["ReleaseQty"]		=	$ReleaseQty;
			
			$item_list	.=	",'$Item'";
			
			$rsgetorder->MoveNext();
		}
		$item_list	=	substr($item_list,1); 
		
		$GETRETURNS	=	"SELECT cdtl.ItemNumber as ItemNo, (cdtl.MposQty*cdtl.UnitPrice) as Amount, (cdtl.MposQty*cdtl.UnitPrice)*(1-(cd.Discount/100)) as DiscountedPrice, cdtl.MposQty  
								FROM crsheader AS chdr 
								LEFT JOIN crsdetail AS cdtl ON chdr.RTVNumber = cdtl.RTVNumber 
								LEFT JOIN itemmaster AS itm ON itm.ItemNo = cdtl.ItemNumber 
								LEFT JOIN itemtype AS it on it.ItemType = itm.ItemType 
								
								LEFT JOIN custmast as cm on cm.CustNo = chdr.CustCode
								LEFT JOIN custdiscount AS cd ON cd.PriceBook = cm.CustPriceBook and itm.PriceClass = cd.PriceClass
			                  
			                    WHERE chdr.Createddt BETWEEN '{$retdfrom}' AND '{$retdto}' 
			                    AND chdr.CustCode  LIKE '%{$txtcustno}%' and chdr.RTVNumber != 0 and ItemNo in ($item_list)
			                    GROUP BY ItemNo
			                    
			                    UNION ALL 
			                    
						SELECT  Mcdtl.ItemNumber as ItemNo, (Mcdtl.MposQty*Mcdtl.UnitPrice) as Amount, (Mcdtl.MposQty*Mcdtl.UnitPrice)*(1-(Mcd.Discount/100)) as DiscountedPrice, Mcdtl.MposQty  
								FROM crsheader AS Mchdr 
								LEFT JOIN crsdetail AS Mcdtl ON Mchdr.MposNumber = Mcdtl.MposNumber 
								LEFT JOIN itemmaster AS Mitm ON Mitm.ItemNo = Mcdtl.ItemNumber 
								LEFT JOIN itemtype AS Mit on Mit.ItemType = Mitm.ItemType 
								
								LEFT JOIN custmast as Mcm on Mcm.CustNo = Mchdr.CustCode
								LEFT JOIN custdiscount AS Mcd ON Mcd.PriceBook = Mcm.CustPriceBook and Mitm.PriceClass = Mcd.PriceClass 
			                    WHERE Mchdr.Createddt BETWEEN '{$retdfrom}' AND '{$retdto}' 
			                    AND Mchdr.CustCode like '%{$txtcustno}%' and Mchdr.MposNumber != 0 and ItemNo in ($item_list)
			                    GROUP BY ItemNo";
		$RSGETRETURNS	=	$Filstar_conn->Execute($GETRETURNS);
		if($RSGETRETURNS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		else 
		{
			while (!$RSGETRETURNS->EOF) 
			{
				$ItemNo			=	$RSGETRETURNS->fields["ItemNo"];
				$Amount			=	$RSGETRETURNS->fields["Amount"];
				$DiscountedPrice=	$RSGETRETURNS->fields["DiscountedPrice"];
				$MposQty		=	$RSGETRETURNS->fields["MposQty"];
				
				$arrOrder[$ItemNo]["Amount"]			=	$Amount;
				$arrOrder[$ItemNo]["DiscountedPrice"]	=	$DiscountedPrice;
				$arrOrder[$ItemNo]["MposQty"]			=	$MposQty;
				$RSGETRETURNS->MoveNext();
			}
		}
		
		class PDF extends FPDF 
			{
				function Header()
				{
					$txtcustno		=	$_GET["txtcustno"];
					$txtcustname	=	$_GET["txtcustname"];
					$deldfrom		=	$_GET["deldfrom"];
					$deldto			=	$_GET["deldto"];
					$retdfrom		=	$_GET["retdfrom"];
					$retdto			=	$_GET["retdto"];

					$rdoordertype	=	$_GET["rdoordertype"];
					$this->SetFont('Times','B',12);
					$this->SetX(10);$this->Cell(0,5,'FILSTAR DISTRIBUTORS CORPORATION',0,1,'C');
					$this->SetX(10);$this->Cell(0,5,'DELIVERIES/RETURNS',0,1,'C');
					$this->Ln(2);
					$this->SetFont('Times','B',10);
					$this->SetX(5);$this->Cell(0,5,"CUSTOMER: $txtcustname($txtcustno)",0,1,'L');
					$this->SetX(5);$this->Cell(0,5,'DELIVERY PERIOD: '.date("F d, Y",strtotime($deldfrom))." to ".date("F d, Y",strtotime($deldto)),0,1,'L');
					$this->SetX(5);$this->Cell(0,5,'RETURN PERIOD: '.date("F d, Y",strtotime($retdfrom))." to ".date("F d, Y",strtotime($retdto)),0,1,'L');
					$this->ln(5);
					
					$this->SetX(5);$this->Cell(15,8,"Item No.",1,0,'C');
					$this->SetX(20);$this->Cell(90,8,"Item Description",1,0,'C');
					$this->SetX(110);$this->Cell(50,4,"Delivered","LTR",0,'C');
					$this->SetX(160);$this->Cell(50,4,"Returned","LTR",1,'C');
					
					$this->SetX(110);$this->Cell(20,4,"Quantity",1,0,'C');
					$this->SetX(130);$this->Cell(30,4,"Amount",1,0,'C');
					$this->SetX(160);$this->Cell(20,4,"Quantity",1,0,'C');
					$this->SetX(180);$this->Cell(30,4,"Amount",1,1,'C');
					
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
			$pdf->SetFont('Times','',9);

			$deltotamount		=	0;
			$deltotqty			=	0;
			$rettotamount		=	0;
			$rettotqty			=	0;
			
			foreach ($arrOrder as $skuno=>$val1)
			{	
				$itemdesc			=	$global_func->Select_val($Filstar_pms,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = '{$skuno}'");
				$ReleaseQty			=	$val1["ReleaseQty"];
				$NetAmount			=	$val1["NetAmount"];
				$DiscountedPrice	=	$val1["DiscountedPrice"];
				$MposQty			=	$val1["MposQty"];
				
				$pdf->SetX(5);$pdf->Cell(15,5,"$skuno",1,0,'C');
				$pdf->SetX(20);$pdf->Cell(90,5,substr($itemdesc,0,45),1,0,'L');
				$pdf->SetX(110);$pdf->Cell(20,5,number_format($ReleaseQty),1,0,'C');
				$pdf->SetX(130);$pdf->Cell(30,5,number_format($NetAmount,2),1,0,'R');
				$pdf->SetX(160);$pdf->Cell(20,5,number_format($MposQty),1,0,'C');
				$pdf->SetX(180);$pdf->Cell(30,5,number_format($DiscountedPrice,2),1,1,'R');
				
				$deltotamount		+=	$NetAmount;
				$deltotqty			+=	$ReleaseQty;
				$rettotamount		+=	$DiscountedPrice;
				$rettotqty			+=	$MposQty;
			}
				$pdf->SetFont('Times','B',9);
				$pdf->SetX(5);$pdf->Cell(105,5,"TOTAL",1,0,'C');
				$pdf->SetX(110);$pdf->Cell(20,5,number_format($deltotqty),1,0,'C');
				$pdf->SetX(130);$pdf->Cell(30,5,number_format($deltotamount,2),1,0,'R');
				$pdf->SetX(160);$pdf->Cell(20,5,number_format($rettotqty),1,0,'C');
				$pdf->SetX(180);$pdf->Cell(30,5,number_format($rettotamount,2),1,1,'R');
			echo $pdf->Output();
	}
	else 
	{
		echo "No records found.";
	}
}
?>