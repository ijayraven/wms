<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}

echo abs("35.000");

	class PDF extends FPDF 
	{
		function Header($header_)
		{
			$code = "123456"; 
			$path =  "tmp/";
			
			Generate_Barcode_Image($code, $path, "{$code}.png");
			$this->Image("$path$code.png",160,12,45,9); //Image(name, x, y, w, h)
			$this->SetFont('helvetica', '', 11);
			
			$this->SetXY(12, 34); $this->Cell(100, 6, "Philippines Seven Corporation ", 0, 0, 'L');
			$this->SetXY(12, 37.5); $this->Cell(100, 6, "7th Floor,The Columbia Tower Ortigas Avenue Mandaluyong City 1550 Philippines", 0, 0, 'L');
			
			// Customer
			$this->SetXY(12, 42); $this->Cell(100, 6, "DELIVER TO :", 0, 0, 'L');
			$this->SetXY(38, 42); $this->Cell(100, 6, $this->header["CustName"], 0, 0, 'L');
			$this->SetXY(38, 45.5); $this->Cell(100, 6, $CustAddress[0], 0, 0, 'L');
			$this->SetXY(38, 49.5); $this->Cell(100, 6, $CustAddress[1], 0, 0, 'L');
			
//			$this->SetXY(12, 43.5); $this->Cell(100, 6, $CustAddress[0], 0, 0, 'L');
//			$this->SetXY(12, 48.5); $this->Cell(100, 6, $CustAddress[1], 0, 0, 'L');
//
//			$this->SetXY(106, 57); $this->Cell(10, 6, $this->header["CustNo"], 0, 0, 'L');
//			$this->SetXY(12, 34); $this->Cell(100, 6, $this->header["CustName"], 0, 0, 'L');

			$this->SetXY(55, 30); $this->Cell(50, 5, 'PL CONF: '.$this->header[PickListTime].' ('.($this->header[Territory]==1?'Manila':'Provl').')', 0);

			$this->SetXY(35, 56); $this->Cell(90, 6, $this->header["TINNo"], 0, 0, 'L');
			$this->SetXY(35, 60); $this->Cell(90, 6, 'Retailer', 0, 0, 'L');

			// Invoice/STF Number
			$this->SetXY(182, 33); $this->Cell(28, 7, $this->header["InvoiceNo"], 0, 0, 'L');
			$this->SetXY(182, 40); $this->Cell(28, 7, $this->header["OrderNo"], 0, 0, 'L');
			$this->SetXY(182, 46); $this->Cell(28, 7, $this->header["PickListNo"], 0, 0, 'L');
			$this->SetXY(182, 52); $this->Cell(28, 7, $this->header["InvoiceDate"], 0, 0, 'L');
			$this->SetXY(182, 58); $this->Cell(28, 7, "CHARGE", 0, 0, 'L');
			$this->SetXY(182, 64); $this->Cell(28, 7, "As Stated", 0, 0, 'L');

			// Salesrep
			$this->SetXY(37, 66); $this->Cell(60, 6, $this->header["SalesRepName"], 0, 0, 'L');
			$this->SetXY(136, 66); $this->Cell(10, 6, $this->header["SalesRepCode"], 0, 0, 'L');
			$this->Ln(5);

		}
		
		function Footer()
		{
			//if ($this->LastPage) 
			//{
				if($this->header[TaxSuffix] == 'VAT') 
				{
					$nVatableSales = (array_sum($this->total[NetAmount]) / $this->Tax['div']);
					$nVat          = ((array_sum($this->total[NetAmount]) / $this->Tax['div']) * $this->Tax[mul]);
					$nTotalAmount  = self::nf($nVatableSales+$nVat);
					
					$this->SetXY(175, 215); $this->Cell(32, 4, self::nf($nVatableSales), 0, 1, 'R');
					$this->SetXY(25, 225);  $this->Cell(32, 4, $this->Tax[base].'%', 0, 1, 'L');
					$this->SetXY(175, 225); $this->Cell(32, 4, self::nf($nVat), 0, 1, 'R');
				} 
				else 
				{
					$nNonVatableSales = array_sum($this->total[NetAmount]);
					$nTotalAmount     = self::nf($nNonVatableSales);
					$this->SetXY(175, 215); $this->Cell(32, 4, self::nf($nNonVatableSales), 0, 1, 'R');
				}
				
				$this->SetXY(55, 253); $this->Cell(100, 4, $this->header["CustName"]);
	
				$this->SetXY(1, 230); $this->Cell(42, 4, 'Records', 0, 0, 'C');
				$this->SetXY(1, 235); $this->Cell(42, 4, $this->header[cnt], 0, 0, 'C');
		
				$this->SetXY(85, 230); $this->Cell(18, 4, date('G:i:s'), 0, 0, 'C');
				$this->SetXY(85, 235); $this->Cell(18, 4, array_sum($this->total[OrderQty]), 0, 0, 'C', 0);
				
				$this->GrossAmount = ($this->header[OrderCategory]=='STF' OR $this->header[EnterpriseCode]!='00001')?self::nf(array_sum($this->total[GrossAmount])):'';
		
				$this->SetXY(145, 240); $this->Cell(31, 4, $this->GrossAmount, 0, 0, 'R');
		
				if($this->header[OrderCategory]=='Invoice' && $this->header[TaxSuffix]=='VAT') 	
		
				$this->SetXY(174, 235); $this->Cell(32.5, 4, $nTotalAmount, 0, 0, 'R');
				$pagemsg = '*** Last Page';
//			} 
//			else 
//			{
//				$pagemsg = 'Next Page Please';
//			}
			//$this->SetXY(145, 230); $this->Cell(29, 4, 'Page '.$this->PageNo(), 0, 0);
	
			//$this->SetXY(174, 230); $this->Cell(34.5, 4, $pagemsg, 0, 0, 'R');
		}
		
		function nf($num)
		{
		 return number_format($num,'2','.',',');
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
	
	$pdf	=	new PDF('P', 'mm', 'letter');
	$pdf->Open();
	$pdf->AliasNbPages();
	$pdf->SetLeftMargin(10);
	$pdf->AddPage();
	$pdf->SetFont('helvetica', '', 11);
	
	if($pdf->GetY() > 195) 
	{
		$pdf->AddPage();
	}
	$pdf->Cell(0, 4, 'SUBJECT TO TERMS AND CONDITIONS STATED AT THE BACK.', 1, 1, 'C');
	$pdf->Cell(0, 4, 'VAT REGISTERED as of February 27, 1998 R. D. O. No. 43', 1, 1, 'C');
	
	$pdf->LastPage = true;
	$pdf->Ln(3);
	$pdf->Cell(100, 4, 'Reference Number:', 0, 1);
	$pdf->Cell(100, 4, $pdf->header[RefNo], 0, 0);
	$pdf->Ln(3);
	$conn_ho	=	mysql_connect('192.168.250.172','root','');
	if ($conn_ho==false) 
	{
		echo mysql_error($conn_ho)."::".__LINE__;exit();
	}
	
	$sel_h		=	mysql_query("SELECT SPECIALINSTRUCTION,REFINSTRUCTION FROM TABLETORDER.ORDERHEADER WHERE ORDERNO = '{$_SESSION['orderno']}'",$conn_ho);
	if ($sel_h==false) 
	{
		echo mysql_error($conn_ho)."::".__LINE__;exit();
	}
	$rssel_h	=	mysql_fetch_array($sel_h);
	
	$a			=	$rssel_h['SPECIALINSTRUCTION'];
	$b			=	$rssel_h['REFINSTRUCTION'];
	$instaruction	=	$a.' '.$b;
	$pdf->Ln(7);
	$pdf->Cell(100, 4, 'REMARKS:'.$instaruction, 0, 1);
	mysql_close($conn_ho);
	
	$pdf->Ln(7);
	echo $pdf->Output();
?>