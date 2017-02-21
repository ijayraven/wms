<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}


		$DFROM		=	$_GET['DFROM'];
		$DTO		=	$_GET['DTO'];
		$SOFNO		=	$_GET['SOFNO'];
		
		$aSof		=	explode("|",$SOFNO);
		$list_sof 	=	implode("','",$aSof);
		
		$_SESSION['name']	=	$global_func->Select_val($Filstar_conn,WMS_LOOKUP,"USER","NAME","USERNAME = '{$_SESSION['username']}'");
		
		$sel_sof	=	" SELECT A.OrderNo,A.RefNo,A.CustNo,A.OrderDate,A.PickListNo,A.PickListDate,A.OrderAmount from orderheader A ";
		$sel_sof   .=	" LEFT JOIN custmast as B on B.CustNo = A.CustNo ";
		$sel_sof   .=	" WHERE OrderStatus = 'Confirmed' and substring(A.CustNo,-1,1) = 'O' and NBSnewBranchCode != '' ";
	 	$sel_sof   .=	" AND A.OrderNo in ('{$list_sof }') ";
		$rssel_sof  =	$Filstar_conn->Execute($sel_sof);
		if ($rssel_sof==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$cnt	=	$rssel_sof->RecordCount();
	
		class PDF extends FPDF 
		{
			function Header_ko($Filstar_conn,$global_func,$cust,$plno,$pono,$sofdate,$sofno,$RF_no,$SalesRepCode)
			{
				$custname		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$cust}' ");
				$SalesRepName	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$SalesRepCode}' ");
				
				$code = $RF_no;
				$path =  "tmp/";
			
				Generate_Barcode_Image($code, $path, "{$code}.png");
				$this->Image("$path$code.png",160,12,45,9); //Image(name, x, y, w, h)
				
				$this->Image("/var/www/html/wms/images/fdc101.jpg",10,5,38,18);
				
				$this->SetFont('Courier','B',15);
				$this->SetX(10);$this->Cell(0,5,'RECEIVING FORM',0,1,'C');
				$this->ln(10);
				
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(25,5,'CUSTOMER',0,0,'L');
				$this->SetX(35);$this->Cell(110,5,":".$cust.'-'.$custname,0,1,'L');
				
				
				$this->SetX(10);$this->Cell(25,5,'R.F. NO.',0,0,'L');
				$this->SetX(35);$this->Cell(110,5,":".$RF_no,0,0,'L');
				$this->SetX(140);$this->Cell(25,5,'REF DATE',0,0,'L');
				$this->SetX(165);$this->Cell(25,5,":".date('Y-m-d'),0,1,'L');
				
				$this->SetX(10);$this->Cell(25,5,'P.O. NO.',0,0,'L');
				$this->SetX(35);$this->Cell(110,5,":".$pono,0,0,'L');
				$this->SetX(140);$this->Cell(25,5,'SOF DATE',0,0,'L');
				$this->SetX(165);$this->Cell(25,5,":".$sofdate,0,1,'L');
				
				$this->SetX(10);$this->Cell(25,5,'SOF NO.',0,0,'L');
				$this->SetX(35);$this->Cell(110,5,":".$sofno,0,0,'L');
				$this->SetX(140);$this->Cell(25,5,'SSR/SR',0,0,'L');
				$this->SetX(165);$this->Cell(25,5,":".$SalesRepCode,0,1,'L');
				
				$this->SetX(10);$this->Cell(25,5,'PL NO.',0,0,'L');
				$this->SetX(35);$this->Cell(25,5,":".$plno,0,0,'L');
				$this->SetFont('Courier','B',11);
				$this->SetX(165);$this->Cell(25,5,$SalesRepName,0,1,'L');
				
				$this->ln(5);
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(0,5,'',"B",0,1,'C');
				$this->SetX(10);$this->Cell(15,5,'LINE#',0,0,'L');
				$this->SetX(25);$this->Cell(30,5,'ARTICLE',0,0,'C');
				$this->SetX(55);$this->Cell(25,5,'SKU',0,0,'C');
				$this->SetX(80);$this->Cell(50,5,'DESCRIPTION',0,0,'C');
				$this->SetX(140);$this->Cell(25,5,'QTY',0,0,'C');
				$this->SetX(158);$this->Cell(30,5,'UNITPRICE',0,0,'C');
				$this->SetX(185);$this->Cell(30,5,'AMOUNT',0,1,'C');
			}
			
			function HEADER_TRXNO($TRXNO)
			{
				$this->SetFont('Courier','B',15);
				$this->SetXY(10,35);$this->Cell(10,5,$TRXNO,0,1,'L');
				$this->ln(5);
			}
			
			function Footer()
			{
				$this->SetFont('Courier','B',10);
				$this->SetXY(10,245);$this->Cell(0,3,"RECEIVED BY : ",0,0,'L');
				$this->SetXY(38,245);$this->Cell(55,3,"","B",0,'L');
				$this->SetXY(40,248);$this->Cell(47,3,"SIGNED OVER PRINTED NAME",0,0,'L');
				$this->SetXY(10,252);$this->Cell(0,3,"RECEIVED DATE/TIME:",0,0,'L');
				$this->SetXY(50,252);$this->Cell(37,3,"","B",0,'L');
				
				$this->SetFont('Courier','',9);
				$this->SetY(255);$this->Cell(0,10,'Printed By   : '.$_SESSION['name'],0,1,'L');
				$this->SetY(258);$this->Cell(0,10,'Printed Date : '.date('Y-m-d'),0,1,'L');
				$this->SetY(261);$this->Cell(0,10,'Printed Time : '.date('H:i A'),0,0,'L');
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
		
		$pdf= new PDF('P','mm','Letter');
		$pdf->Open();
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak('auto',30);
		
		if ($rssel_sof->RecordCount() > 0) 
		{
			$Filstar_conn->StartTrans();
			while (!$rssel_sof->EOF)
			{
				$pdf->AddPage();
				$OrderNo		=	$rssel_sof->fields['OrderNo'];
				$RefNo			=	$rssel_sof->fields['RefNo'];
				$CustNo			=	$rssel_sof->fields['CustNo'];
				$OrderDate		=	$rssel_sof->fields['OrderDate'];
				$PickListNo		=	$rssel_sof->fields['PickListNo'];
				$PickListDate	=	$rssel_sof->fields['PickListDate'];
				$OrderAmount	=	$rssel_sof->fields['OrderAmount'];
				
				$RF_no			=	$global_func->TRANSEQ_RF($Filstar_conn);
				
				$SalesRepCode	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CustNo}' ");
				
				$pdf->Header_ko($Filstar_conn,$global_func,$CustNo,$PickListNo,$RefNo,$OrderDate,$OrderNo,$RF_no,$SalesRepCode);
				
				$sel_dtl		=	"SELECT Item,ReleaseQty,UnitCost,UnitPrice,Discount,GrossAmount,NetAmount FROM orderdetail where OrderNo = '{$OrderNo}' and isDeleted = 'N' ";
				$rssel_dtl		=	$Filstar_conn->Execute($sel_dtl);
				if ($rssel_dtl==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				
				$pdf->SetFont('Courier','',10);
				
				$counter		=	1;
				$counter_page	=	0;	
				$total_qty		=	0;
				$total_net		=	0;
				while (!$rssel_dtl->EOF) 
				{
					$Item		=	$rssel_dtl->fields['Item'];
					$ReleaseQty	=	$rssel_dtl->fields['ReleaseQty'];
					$UnitCost	=	$rssel_dtl->fields['UnitCost'];
					$UnitPrice	=	$rssel_dtl->fields['UnitPrice'];
					$Discount	=	$rssel_dtl->fields['Discount'];
					$GrossAmount=	$rssel_dtl->fields['GrossAmount'];
					$NetAmount	=	$rssel_dtl->fields['NetAmount'];
					
					$total_qty	+=	$ReleaseQty;
					
					$ARTICLE	=	$global_func->Select_val($Filstar_pms,"FDC_PMS","ITEMMASTER","ARTICLE","ITEMNO = '{$Item}' ");
					$ITEM_DESC	=	substr($global_func->Select_val($Filstar_pms,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO = '{$Item}' "),0,30);
					
					$pdf->SetX(10);$pdf->Cell(15,5,$counter,0,0,'L');
					$pdf->SetX(25);$pdf->Cell(30,5,$ARTICLE,0,0,'C');
					$pdf->SetX(52);$pdf->Cell(25,5,$Item,0,0,'C');
					$pdf->SetX(75);$pdf->Cell(50,5,$ITEM_DESC,0,0,'L');
					$pdf->SetX(138);$pdf->Cell(20,5,$ReleaseQty,0,0,'R');
					$pdf->SetX(165);$pdf->Cell(20,5,$UnitCost,0,0,'R');
					$pdf->SetX(188);$pdf->Cell(20,5,number_format($NetAmount,2),0,1,'R');
					if ($counter_page==34)
					{
						$counter_page=0;
						//$pdf->AddPage();
						$pdf->Header_ko($Filstar_conn,$global_func,$CustNo,$PickListNo,$RefNo,$OrderDate,$OrderNo,$RF_no);
						$pdf->SetFont('Courier','',10);
						$pdf->SetX(10);$pdf->Cell(15,5,$counter,0,0,'L');
						$pdf->SetX(25);$pdf->Cell(30,5,$ARTICLE,0,0,'C');
						$pdf->SetX(52);$pdf->Cell(25,5,$Item,0,0,'C');
						$pdf->SetX(75);$pdf->Cell(50,5,$ITEM_DESC,0,0,'L');
						$pdf->SetX(138);$pdf->Cell(20,5,$ReleaseQty,0,0,'R');
						$pdf->SetX(165);$pdf->Cell(20,5,$UnitCost,0,0,'R');
						$pdf->SetX(188);$pdf->Cell(20,5,number_format($NetAmount,2),0,1,'R');
					}
					
					$insert_dtl		=	"INSERT INTO WMS_NEW.RECEIVINGFORM_DTL(`SOF`,`PONO`,`RFNO`,`SKUNO`,`QTY`,`UNITPRICE`,`DISCOUNT`,`UNITCOST`,`GROSSAMT`,`NETAMT`)";
					$insert_dtl		.=	"VALUES ";
					$insert_dtl		.=	"('{$OrderNo}','{$RefNo}','{$RF_no}','{$Item}','{$ReleaseQty}','{$UnitPrice}','{$Discount}','{$UnitCost}','{$GrossAmount}','{$NetAmount}') ";
					$rsinsert_dtl	 =	$Filstar_conn->Execute($insert_dtl);
					if ($rssel_dtl==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					
					$total_net	+=	$NetAmount;
					
					$counter++;
					$counter_page++;
					$rssel_dtl->MoveNext();
				}
				
				$insert_hrd		 =	"INSERT INTO WMS_NEW.RECEIVINGFORM_HDR(`RFNO`,`CUSTCODE`,`SOF`,`PONO`,`PLNO`,`SRCODE`,`QTY`,`GROSSAMOUNT`,`NETAMOUNT`,`SOFDATE`,`PLDATE`,`ADDEDBY`,`ADDEDDATE`,`ADDEDTIME`) ";
				$insert_hrd		.=	"VALUES ";
				$insert_hrd		.=	"('{$RF_no}','{$CustNo}','{$OrderNo}','{$RefNo}','{$PickListNo}','{$SalesRepCode}','{$total_qty}','{$GrossAmount}','{$NetAmount}','{$OrderDate}','{$PickListDate}','{$_SESSION['username']}',SYSDATE(),SYSDATE())";
				$rsinsert_hdr	 =	$Filstar_conn->Execute($insert_hrd);
				if ($rsinsert_hdr==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
//				for ($x=0;$x<=40;$x++)
//				{
//					$pdf->SetX(10);$pdf->Cell(15,5,$counter,0,0,'L');
//					$pdf->SetX(25);$pdf->Cell(30,5,"SAMPLE",0,0,'C');
//					$pdf->SetX(52);$pdf->Cell(25,5,"SAMPLE",0,0,'C');
//					$pdf->SetX(75);$pdf->Cell(50,5,"SAMPLE",0,0,'L');
//					$pdf->SetX(138);$pdf->Cell(20,5,"SAMPLE",0,0,'R');
//					$pdf->SetX(165);$pdf->Cell(20,5,"SAMPLE",0,0,'R');
//					$pdf->SetX(188);$pdf->Cell(20,5,"SAMPLE",0,1,'R');
//					if ($counter_page==34)
//					{
//						$counter_page=0;
//						$pdf->AddPage();
//						$pdf->Header_ko($Filstar_conn,$global_func,$CustNo,$PickListNo,$RefNo,$OrderDate,$OrderNo,$RF_no);
//						$pdf->SetFont('Courier','',10);
//						$pdf->SetX(10);$pdf->Cell(15,5,$counter,0,0,'L');
//						$pdf->SetX(25);$pdf->Cell(30,5,"SAMPLE",0,0,'C');
//						$pdf->SetX(52);$pdf->Cell(25,5,"SAMPLE",0,0,'C');
//						$pdf->SetX(75);$pdf->Cell(50,5,"SAMPLE",0,0,'L');
//						$pdf->SetX(138);$pdf->Cell(20,5,"SAMPLE",0,0,'R');
//						$pdf->SetX(165);$pdf->Cell(20,5,"SAMPLE",0,0,'R');
//						$pdf->SetX(188);$pdf->Cell(20,5,"SAMPLE",0,1,'R');	
//						$counter_page++;
//					}
//					$counter++;
//					$counter_page++;
//				}
				$pdf->SetFont('Courier','B',10);
				$pdf->SetX(75);$pdf->Cell(50,5,'TOTAL',0,0,'C');
				$pdf->SetX(138);$pdf->Cell(20,5,$total_qty,0,0,'R');
				$pdf->SetX(188);$pdf->Cell(20,5,number_format($total_net,2),0,1,'R');
				$rssel_sof->MoveNext();
				$pdf->SetFont('Courier','IB',10);
				$pdf->SetX(10);$pdf->Cell(0,5,"* * * * * * * * * * * NOTHING FOLLOWS * * * * * * * * * * *",0,0,'C');
			}
			$Filstar_conn->CompleteTrans();
		}
		else 
		{
			$pdf->SetFont('Courier','B',13);
			$pdf->SetX(10);$pdf->Cell(0,5," * * * NO RECORD FOUND * * * ",0,0,'C');
		}
		echo $pdf->Output();
?>
