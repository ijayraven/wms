<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}

class formSTFInvoicePrinting extends FPDF  {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	function formSTFInvoicePrinting() {
		global $deo;
		
		$this->dump('inline');
	}

        function dump($mode) {
                $output = $this->doReport();
                $filename=get_class($this).'-'.date('mdY').'.pdf';
                header("Content-Disposition: $mode; filename=\"$filename\"");
                header("Content-Location: $_SERVER[REQUEST_URI]");
                header("Content-Type: application/pdf");
                header("Expires: 0");
                echo $output;
                exit();
        }


	/**
	 * Create Report
	 * rjam
	 * @return void
	 */ 
	function doReport() {
		global $deo, $dB;

		$this->cryptDiscount = Array(
			'1'	=>	'L',
			'2'	=>	'U',
			'3'	=>	'C',
			'4'	=>	'K',
			'5'	=>	'Y',
			'6'	=>	'S',
			'7'	=>	'T',
			'8'	=>	'O',
			'9'	=>	'R',
			'0'	=>	'E',	
		);

		$this->FPDF('P', 'mm', 'letter');
		$this->AliasNbPages();
		$this->SetLeftMargin(10);

		$this->header = $dB->query_fetch_row("
			SELECT *, RIGHT(OrderNo, 1) as SOFSuffix, IF(MONTH(InvoiceDate)>=2 AND Year(InvoiceDate)>=2006, '12', '10') as Tax
			FROM `orderheader` oh
			LEFT JOIN `custmast` cm ON cm.CustNo = oh.CustNo
			LEFT JOIN `salesreps` sr ON cm.SalesRepCode = sr.SalesRepCode
			LEFT JOIN `customer_address` ca ON cm.CustNo = ca.CustNo
			WHERE oh.OrderNo='".$_SESSION['orderno']."';
		");

		if(!$this->header[OrderCategory]) $this->header[OrderCategory]='Invoice';
		$this->header[CustName] = html_entity_decode($this->header[CustName]);

		$this->SetAutoPageBreak(true, $this->header['OrderCategory']=='Invoice'? 65 : 43 );

		 $this->Tax = Array(
                        'base'  => 12,
                        'div'   => 1.12,
                        'mul'   => 12*0.01,
                );

		$this->CustDiscount = $dB->get_results("SELECT PriceClass, Discount FROM `custdiscount` WHERE PriceBook='".$this->header[CustPriceBook]."';", 'PriceClass');
		
		$detail = $dB->get_results("
			SELECT
				ProdGroupDesc, im.ItemNo, CONCAT(im.ItemNo, '  ', LEFT(im.ItemDesc, 24)) as ItemDesc,
				im.ItemDesc as ItemDescS, im.CategoryClass,im.Brand,
				im.ItemType, it.ItemTypeDesc, PriceClass, CaptionDesc, ReleaseQty as Qty,
				CONCAT(im.UnitMeasure, ' ', im.PackCode) as UnitMeasure,
				IF(PriceClass='A6' OR PriceClass='A7', 'Imp ', '') as Imported,
				od.UnitPrice, od.Discount, GrossAmount, NetAmount, cm.CustomerBranchCode
			FROM orderdetail AS od
			INNER JOIN `itemmaster` AS im ON im.ItemNo = od.Item
			INNER JOIN `productgroup` AS pg ON pg.ProductGroup = im.ProdGroup
			LEFT JOIN `caption` AS c ON im.ItemClass = c.Caption
			LEFT JOIN `itemtype` AS it ON it.ItemType=im.ItemType
			LEFT JOIN custmast AS cm ON cm.CustNo = od.CustNo
			WHERE od.OrderNo='".$_SESSION['orderno']."' AND od.ReleaseQty !=  0 AND od.`isDeleted` = 'N'
			ORDER BY UnitPrice;
		");

		if($detail) {
			if($this->header[OrderCategory]=='Invoice') {
				// Invoice Detail

				$pc = Array('A9', 'A10', 'A11', 'A12','A27','A28');
				$ic = Array('C', 'LS');
				foreach($detail as $k) {
					if(in_array($k[PriceClass], $pc)) {
						$itemized=true;
						break;
					} elseif($k[PriceClass]=='A8') {
						$itemdesc=true;
					} else if(in_array($k['ItemType'], $ic)) {
						$nocaption=true;
					}
				}

				if($itemized) {
					//echo "1";exit();
					$this->process_invoice_specialty($detail);
					$this->render_detail_specialty();
				} else {
					//echo "2";exit();
					$this->process_invoice_normal($detail);
					$this->render_detail_normal($itemdesc, $nocaption);
				}
			} else {
				$cc = Array('G015', 'G019');
 				foreach($detail as $k) if(in_array($k['CategoryClass'], $cc)) $itemized=true;

				// STF Detail
				if($itemized OR $this->header[FormDetail]=='Itemized' OR in_array($this->header[SOFSuffix], Array('V', 'T'))) {
					$this->process_stf_itemized($detail);
					$this->render_detail_itemized();
				} else {
					$this->process_stf_normal($detail);
					$this->render_detail_normal(true);
				}
			}

			// Render Detail tail
			$this->render_detailtail();
		}
		return $this->Output('x', 'S');
	}
	
	/**
	 * ACCOUNTING WAY OF ROUNDING NUMBERS 07/19/12
	 *
	 * @param int $nNum
	 * @param int $nDecCnt
	 * @return int $nRounded
	 */
	function AccntRound_Old($nNum, $nDecCnt)
	{
		$nWholeNum	=	0;		// whole number of a given number
		$nDecimal	=	0;		// decimal of a given number
		$nRounded	=	0;		// rounded number
		
		list($nWholeNum, $nDecimal) = explode(".", $nNum);    // separate whole number from decimal
		
		if ($nDecCnt <= 0)    // WHOLE NUMBER
		{
			$nWholeNum = round($nNum, $nDecCnt);
			$nDecimal = 0;    // make decimal equal to zero
		}
		else    // DECIMAL
		{
			$nRounder	= 	substr($nDecimal, $nDecCnt, 1);
			$nBasis 	= 	substr($nDecimal, ($nDecCnt - 1), 1);
			$nDecimal 	= 	"0.".substr($nDecimal, 0, $nDecCnt);
			
			if ($nRounder >= 5 && $nBasis == 0)
			{
				$nDecimal = ($nDecimal + 0.01);	
			}
			elseif ($nRounder >= 5) 
			{
				$nIncr = 1;     // for incrementing decimal
				for ($n = 0; $n < $nDecCnt; $n++) 
				{ 
					$nIncr = ($nIncr / 10); 
				}
				
				if (($nRounder == 5) && ($nBasis < 5) && (($nBasis % 2) == 0)) 
				{
					$nIncr = 0;
				}
				$nDecimal = ($nDecimal + $nIncr);
			}
		}
		$nRounded = ($nWholeNum + $nDecimal);
		return $nRounded;
	}    // end of AccntRound() function
	
	/**
	 * NBS ACCOUNTING WAY OF ROUNDING NUMBERS 08/27/12
	 *
	 * @param int $num
	 * @param int $round
	 * @return int $newnum
	 */
	function AccntRound_old2($num, $round)
	{
	    $num_parts = explode(".",$num);
	    $plusvals  = "0.".str_pad(1,$round,0,STR_PAD_LEFT);
	
	    $firstdrop   = substr($num_parts[1],$round,1);
	    $lastkept    = substr($num_parts[1],$round-1,1);
	    $succeedvals = substr($num_parts[1],$round+1);
	
	    if ($firstdrop > 5)
	    {
	        $fpart = $num_parts[0];
	        $spart = (substr($num_parts[1],0,$round)) + 1;
	    }
	    elseif ($firstdrop < 5)
	    {
	        $fpart = $num_parts[0];
	        $spart = substr($num_parts[1],0,$round);
	    }
	    elseif ($firstdrop == 5 && $succeedvals == "" || $succeedvals == 0)
	    {
	        if (in_array($lastkept,array(0,2,4,6,8)))
	        {
	            $fpart = $num_parts[0];
	            $spart = substr($num_parts[1],0,$round);
	        }
	        else 
	        {
	            $fpart = $num_parts[0];
	            $spart = (substr($num_parts[1],0,$round)) + 1;
	        }
	    }
	    elseif ($firstdrop == 5 && $succeedvals != "" || $succeedvals > 0)
	    {
	        $fpart = $num_parts[0];
	        $spart = (substr($num_parts[1],0,$round)) + 1;
	    }
	
	    $newnum = str_pad($fpart,1,0,STR_PAD_LEFT).".".str_pad($spart,$round,0,STR_PAD_RIGHT);
	    
	    return $newnum;
	}
	
	function AccntRound($num,$round)
	{
		$num_parts   = explode(".",$num);
	    $dec         = $num_parts[1];
	    $zeroval     = substr($dec,0,1);
	    $plusvals    = "0.".str_pad(1,$round,0,STR_PAD_LEFT);
		$firstdrop   = substr($dec,$round,1);
	    $lastkept    = substr($dec,$round-1,1);
	    $succeedvals = substr($dec,$round+1);
	
	    if ($firstdrop > 5)
	    {
	        $fpart = $num_parts[0];
	        
	        if ($zeroval == 0)
	        {
	        	$spart = $zeroval.((substr($dec,0,$round)) + 1);
	        }
	        else 
	        {
	        	$spart = (substr($dec,0,$round)) + 1;
	        }
	    }
	    elseif ($firstdrop < 5)
	    {
	        $fpart = $num_parts[0];
	        $spart = substr($dec,0,$round);
	    }
	    elseif ($firstdrop == 5 && $succeedvals == "" || $succeedvals == 0)
	    {
	        if (in_array($lastkept,array(0,2,4,6,8)))
	        {
	            $fpart = $num_parts[0];
	            $spart = substr($dec,0,$round);
	        }
	        else 
	        {
	            $fpart = $num_parts[0];
	            if ($zeroval == 0)
	            {
	            	$spart = $zeroval.((substr($dec,0,$round)) + 1);
	            }
	            else 
	            {
	            	$spart = (substr($dec,0,$round)) + 1;
	            }
	        }
	    }
	    elseif ($firstdrop == 5 && $succeedvals != "" || $succeedvals > 0)
	    {
	        $fpart = $num_parts[0];
	        
	        if ($zeroval == 0)
	        {
	        	$spart = $zeroval.((substr($dec,0,$round)) + 1);
	        }
	        else 
	        {
	        	$spart = (substr($dec,0,$round)) + 1;
	        }
	    }
	
	    $newnum = str_pad($fpart,1,0,STR_PAD_LEFT).".".str_pad($spart,$round,0,STR_PAD_RIGHT);
	    
	    return $newnum;
	}
	
	/**
	 * fetch all amounts 07/19/12
	 *
	 * @param int $nDiscount
	 * @param int $nUnitPrice
	 * @param int $nQty
	 * @return array $aData
	 */
	function FetchAmount($nDiscount, $nUnitPrice, $nQty)
	{
		$aData['Disc']        = $this->AccntRound($nDiscount / 100, 2);
		$aData['DiscAmt']     = $this->AccntRound(($nUnitPrice * $aData['Disc']), 2);
		$aData['GrossAmount'] = $this->AccntRound(($nQty * $nUnitPrice), 2);
		$aData['DiscAmount']  = $this->AccntRound(($aData['DiscAmt'] * $nQty), 2);
		$aData['NetAmount']   = $this->AccntRound(($aData['GrossAmount'] - $aData['DiscAmount']), 2);
		
		return $aData;
	}

	/**
	 * Invoice Detail Processing
	 *
	 * @return void
	 */
	function process_invoice_normal($detail) {
		//print_r($detail);exit();
		foreach($detail as $j => $k) {
			switch($k[ItemType]) {
				case 'C': case 'LS':
					$key = $k[ProdGroupDesc].$k[ItemTypeDesc];
					break;
				default:
					$key = $k[ProdGroupDesc].$k[ItemTypeDesc].$k[CaptionDesc].$k[Discount];
			}
			if(!$this->data[$key][$k[UnitPrice]]) $this->data[$key][$k[UnitPrice]] = $k;
			$this->data[$key][$k[UnitPrice]][OrderQty]+=$k[Qty];
			$this->header[cnt]++;
		}
	}

	/**
	 * Invoice Detail Processing (specialty)
	 *
	 * @return void
	 */
	function process_invoice_specialty($detail) {
		$this->data = $detail;
		$this->header[cnt] = count($detail);
	}

	/**
	 * Normal STF Detail Processing (not itemized)
	 *
	 * @return void
	 */
	function process_stf_normal($detail) {
		foreach($detail as $j => $k) {
			switch($k[ItemType]) {
				case 'C': case 'LS':
					$key = $k[Imported].$k[ItemType].$k[ProdGroupDesc];
					break;
				default:
					$key = $k[Imported].$k[ProdGroupDesc].$k[ItemTypeDesc].$k[CaptionDesc];
			}
			if(!$this->data[$key][$k[UnitPrice]]) $this->data[$key][$k[UnitPrice]] = $k;
			$this->data[$key][$k[UnitPrice]][OrderQty]+=$k[Qty];
			$this->header[cnt]++;
		}
	}

	/**
	 * Itemized STF Detail Processing
	 *
	 * @return void
	 */
	function process_stf_itemized($detail) {
		foreach($detail as $j => $k) {
			switch($k[ItemType]) {
				case 'C': case 'LS':
					$key = $k[Imported].$k[ItemType].$k[ProdGroupDesc];
					break;
				default:
					$key = $k[Imported].$k[ProdGroupDesc].$k[ItemTypeDesc].$k[CaptionDesc];
			}
			$this->data[$key][$k[UnitPrice]][$k[ItemNo]] = $k;
			$this->data[$key][$k[UnitPrice]][$k[ItemNo]][OrderQty]=$k[Qty];
			$this->header[cnt]++;
		}
	}

	/**
	 * Render Detail (not itemized)
	 *
	 * @return void
	 */
	function render_detail_normal($itemdesc=false, $nocaption=false) 
	{
		$this->AddPage();
		$this->SetFont('helvetica', '', 11);
		ksort($this->data);

		//print_r($this->data);exit();
		foreach ($this->data as $x => $y) 
		{
			$n = 0;
			foreach ($y as $j => $k) 
			{
				if ($n++==0) 
				{
					if ($this->header[OrderCategory] == 'Invoice') 
					{
						if ($itemdesc) $str = substr($k[CaptionDesc], 0, 3).' '.substr($k[ItemDescS], 0, 38);
						elseif ($nocaption) $str = substr($k[ProdGroupDesc].' '.$k[ItemTypeDesc], 0, 38);
						else $str = substr($k[ProdGroupDesc].' '.$k[ItemTypeDesc].' '.$k[CaptionDesc], 0, 38);
						$this->Cell(90, 4, $k[Imported].$str, 0, 0);
						$this->Cell(20, 4, $k[PriceClass], 0, 1);
					} 
					else 
					{
						$this->Cell(90, 4, substr($k[Imported].$k[ItemType].' '.$k[ProdGroupDesc].' '.$k[CaptionDesc].' '.$k[ItemDescS], 0, 35), 0, 0);
						$this->Cell(30, 4, $k[PriceClass], 0, 1);
					}
				}

				$this->Cell(51, 4, $this->header[OrderCategory]=='Invoice'?nf($j):'', 0, 0, 'R');
				$this->Cell(20, 4);
				$this->Cell(21, 4, $k[OrderQty], 0, 0, 'R');
				$this->Cell(5, 4);
				$this->Cell(15.5, 4, $k[UnitMeasure], 0, 0, 'C');

				/**
				 * @author Jonas Rovera Magboo 
				 * @todo New Computation for Generating STF/Invoice Form Printing Report (Rounding Off)
				 * @since 08/30/12
				 * @final sa wakas nakapag-decision na sila!!!!!! 
				 */
				if ($this->header[OrderCategory]=='Invoice') 
				{
					$nDisc 		= $this->AccntRound($this->CustDiscount[$k['PriceClass']]['Discount']/100, 2);
					$nDiscAmt   = $this->AccntRound($k['UnitPrice'] * $nDisc, 2);
					//$nUnitPrice = $this->AccntRound($k['UnitPrice'] - $nDiscAmt, 2);

						/**
						 * Modified By: Charles
						 * Changes: Remove rounding
						 * Reason of Changes: Not tally amount of doc print vs data hdr and dtl
						 */
						$nUnitPrice = $k['UnitPrice'] - $nDiscAmt;
				}	
				elseif ($this->header[OrderCategory]=='STF') 
				{
					$nUnitPrice = $k['UnitPrice'];
				}
				
				$this->Cell(23, 4, number_format($nUnitPrice, "2", ".", ","), 0, 0, 'R');

				$k['GrossAmount'] = $this->AccntRound($k['OrderQty'] * $nUnitPrice, 2);
				$k['NetAmount']   = $k['GrossAmount'];

				$this->Cell(31, 4, ($this->header['OrderCategory']=='STF' OR $this->header['EnterpriseCode']!='00001')?nf($k['GrossAmount']):'', 0, 0, 'R');
				$this->Cell(32.5, 4, nf($k['NetAmount']), 0, 1, 'R');

				$this->total['OrderQty'][$x]    += $k['OrderQty'];
				$this->total['GrossAmount'][$x] += $k['GrossAmount'];
				$this->total['NetAmount'][$x]   += $k['NetAmount'];
				
				if (count($y) == ($n)) 
				{
					$this->Cell(70, 4, $this->header['OrderCategory']=='Invoice'?strtr($this->CustDiscount[$k['PriceClass']]['Discount'], $this->cryptDiscount):'%', 0, 0, 'R');
					$this->Cell(1);
					$this->Cell(21, 4, $this->total['OrderQty'][$x], 0, 0, 'R');
					$this->Cell(43);
					$this->Cell(31, 4, ($this->header['OrderCategory']=='STF')?nf($this->total['GrossAmount'][$x]):'', 0, 0, 'R');
					$this->Cell(32.5, 4, nf($this->total['NetAmount'][$x]), 0, 1, 'R');
					
					$nTotalAmount+=$this->total['NetAmount'][$x];
				}
				// end
			}
			//$this->Ln(2.5);
			$this->Ln(0);
		}
		$this->SetX(176);
		$this->Cell(32.5, 4, "____________", 0, 1, 'R');
		
		$this->SetX(176);
		$this->Cell(32.5, 4, nf($nTotalAmount), 0, 1, 'R');
		$this->Ln(1);
	}

	/**
	 * Render Detail (specialty)
	 *
	 * @return void
	 */
	function render_detail_specialty() 
	{
		$this->AddPage();
		$this->SetFont('helvetica', '', 11);
		ksort($this->data);

		foreach ($this->data as $x => $k) 
		{
			$k[OrderQty]	= $k[Qty];
			if ($k[CustomerBranchCode] != '') 
			{
				$this->Cell(80, 4, substr($k[Imported].$k[CaptionDesc], 0, 3).' '.substr($k[ItemDescS], 0, 38), 0, 0);
				$this->Cell(20, 4, $k[PriceClass], 0, 1);
	
				$this->Cell(51, 4, nf($k[UnitPrice]), 0, 0, 'R');
				$this->Cell(20, 4);
				$this->Cell(21, 4, $k[OrderQty], 0, 0, 'R');
				$this->Cell(5, 4);
				$this->Cell(15.5, 4, $k[UnitMeasure], 0, 0, 'C');
			}
			else 
			{
				if ($k[Brand] == 'BIC' || $k[Brand] == '03') 
				{
					$this->Cell(80, 4, substr($k[Imported].$k[ItemType].' '.$k[ProdGroupDesc].' '.$k[CaptionDesc], 0, 38), 0, 1);
					$this->Cell(71, 4, ' '.$k['ItemDesc'], 0, 1);
					$this->Cell(51, 4, nf($k[UnitPrice]), 0, 0, 'R');
					$this->Cell(20, 4);
					$this->Cell(21, 4, $k['OrderQty'], 0, 0, 'R');
					$this->Cell(5, 4);
					$this->Cell(15.5, 4, $k['UnitMeasure'], 0, 0, 'C');
				}
				else 
				{
					$this->Cell(80, 4, substr($k[Imported].$k[ItemType].' '.$k[ProdGroupDesc].' '.$k[CaptionDesc], 0, 38), 0, 1);
					$this->Cell(71, 4, ' '.$k['ItemDesc'], 0, 0);
					$this->Cell(21, 4, $k['OrderQty'], 0, 0, 'R');
					$this->Cell(5, 4);
					$this->Cell(15.5, 4, $k['UnitMeasure'], 0, 0, 'C');
				}
				
			}

			/**
			 * @author Jonas Rovera Magboo 
			 * @todo New Computation for Generating STF/Invoice Form Printing Report (Rounding Off)
			 * @since 08/30/12
			 * @final sa wakas nakapag-decision na sila!!!!!! 
			 */
			if ($this->header[OrderCategory]=='Invoice') 
			{
				$nDisc 	= $this->AccntRound($this->CustDiscount[$k['PriceClass']]['Discount']/100, 2);
				$nDiscAmt   = $this->AccntRound($k['UnitPrice'] * $nDisc, 2);
				//$nUnitPrice = $this->AccntRound($k['UnitPrice'] - $nDiscAmt, 2);

				/**
				 * Modified By: Charles
				 * Changes: Remove rounding
				 * Reason of Changes: Not tally amount of doc print vs data hdr and dtl
				 */
				$nUnitPrice = $k['UnitPrice'] - $nDiscAmt;
			}	
			elseif ($this->header[OrderCategory]=='STF') 
			{
				$nUnitPrice = $k['UnitPrice'];
			}
			
			$this->Cell(23, 4, number_format($nUnitPrice, "2", ".", ","), 0, 0, 'R');

			$k['GrossAmount'] = $this->AccntRound($k['OrderQty'] * $nUnitPrice, 2);
			$k['NetAmount']   = $k['GrossAmount'];
				

			$this->Cell(31, 4, nf($k['GrossAmount']), 0, 0, 'R');
			$this->Cell(32.5, 4, nf($k['NetAmount']), 0, 1, 'R');

			$this->total['OrderQty'][$x]    += $k['OrderQty'];
			$this->total['GrossAmount'][$x] += $k['GrossAmount'];
			$this->total['NetAmount'][$x]   += $k['NetAmount'];
  
			$this->Cell(70, 4, strtr($this->CustDiscount[$k['PriceClass']]['Discount'], $this->cryptDiscount), 0, 0, 'R');
			$this->Cell(1);
			$this->Cell(21, 4, $this->total['OrderQty'][$x], 0, 0, 'R');
			$this->Cell(43);
			$this->Cell(31, 4, nf($this->total['GrossAmount'][$x]), 0, 0, 'R');
			$this->Cell(32.5, 4, nf($this->total['NetAmount'][$x]), 0, 1, 'R');
			$this->Ln(0);
			
			$nTotalAmount+=$this->total['NetAmount'][$x];
		}
		$this->SetX(176);
		$this->Cell(32.5, 4, "____________", 0, 1, 'R');
		
		$this->SetX(176);
		$this->Cell(32.5, 4, nf($nTotalAmount), 0, 1, 'R');
		$this->Ln(1);
	}

	/**
	 * Render Detail Itemized
	 *
	 * @return void
	 */
	function render_detail_itemized() 
	{
		$this->AddPage();
		$this->SetFont('helvetica', '', 11);

		ksort($this->data);
		
		foreach ($this->data as $x => $y1) 
		{
			$n = 0;
			foreach ($y1 as $yy => $y) 
			{
				$this->Ln(2.5);
				
				foreach ($y as $j => $k) 
				{
					if ($n++==0) 
					{
						if ($this->header['OrderCategory'] == 'Invoice') 
						{
							$this->Cell(80, 4, substr($k['Imported'].$k['ProdGroupDesc'].' '.$k['ItemTypeDesc'], 0, 38), 0, 1);
						} 
						else 
						{
							$this->Cell(80, 4, substr($k['Imported'].$k['ItemType'].' '.$k['ProdGroupDesc'].' '.$k['CaptionDesc'], 0, 38), 0, 1);
						}
					}
	
					$this->Cell(71, 4, ' '.$k['ItemDesc'], 0, 0);
					$this->Cell(21, 4, $k['OrderQty'], 0, 0, 'R');
					$this->Cell(5, 4);
					$this->Cell(15.5, 4, $k['UnitMeasure'], 0, 0, 'C');
	
					/**
					 * @author Jonas Rovera Magboo 
					 * @todo New Computation for Generating STF/Invoice Form Printing Report (Rounding Off)
					 * @since 08/30/12
					 * @final sa wakas nakapag-decision na sila!!!!!! 
					 */
					if ($this->header[OrderCategory]=='Invoice') 
					{
						$nDisc 		= $this->AccntRound($this->CustDiscount[$k['PriceClass']]['Discount']/100, 2);
						$nDiscAmt   = $this->AccntRound($k['UnitPrice'] * $nDisc, 2);
						//$nUnitPrice = $this->AccntRound($k['UnitPrice'] - $nDiscAmt, 2);

						/**
						 * Modified By: Charles
						 * Changes: Remove rounding
						 * Reason of Changes: Not tally amount of doc print vs data hdr and dtl
						 */
						$nUnitPrice = $k['UnitPrice'] - $nDiscAmt;
					}	
					elseif ($this->header[OrderCategory]=='STF') 
					{
						$nUnitPrice = $k['UnitPrice'];
					
					}
					
					$this->Cell(23, 4, number_format($nUnitPrice, "2", ".", ","), 0, 0, 'R');
		
					$k['GrossAmount'] = $this->AccntRound($k['OrderQty'] * $nUnitPrice, 2);
					$k['NetAmount']   = $k['GrossAmount'];
					
					$this->Cell(31, 4, ($this->header['OrderCategory']=='STF' OR $this->header['EnterpriseCode']!='00001')?nf($k['GrossAmount']):'', 0, 0, 'R');
					$this->Cell(32.5, 4, nf($k['NetAmount']), 0, 1, 'R');

					$this->total['OrderQty'][$x]    += $k['OrderQty'];
					$this->total['GrossAmount'][$x] += $k['GrossAmount'];
					$this->total['NetAmount'][$x]   += $k['NetAmount'];

					$subtotal['OrderQty'][$x][$yy]    += $k['OrderQty'];
					$subtotal['GrossAmount'][$x][$yy] += $k['GrossAmount'];
					$subtotal['NetAmount'][$x][$yy]   += $k['NetAmount'];
				}
				
				$this->Cell(70, 4, $this->header['OrderCategory']=='Invoice'?strtr($this->CustDiscount[$k['PriceClass']]['Discount'], $this->cryptDiscount):'', 0, 0, 'R');
				$this->Cell(1);
				$this->Cell(21, 4, $subtotal['OrderQty'][$x][$yy], 0, 0, 'R');
				$this->Cell(43);
				$this->Cell(31, 4, ($this->header['OrderCategory']=='STF')?nf($subtotal['GrossAmount'][$x][$yy]):'', 0, 0, 'R');
				$this->Cell(32.5, 4, nf($subtotal['NetAmount'][$x][$yy]), 0, 1, 'R');
				// end
			}
			$this->Cell(71, 4);
			$this->Cell(21, 4, $this->total['OrderQty'][$x], 0, 0, 'R');
			$this->Cell(43);
			$this->Cell(31, 4, ($this->header['OrderCategory']=='STF')?nf($this->total['GrossAmount'][$x]):'', 0, 0, 'R');
			$this->Cell(32.5, 4, nf($this->total['NetAmount'][$x]), 0, 1, 'R');
			
			$nTotalAmount+=$this->total['NetAmount'][$x];
		}
		$this->SetX(176);
		$this->Cell(32.5, 4, "____________", 0, 1, 'R');
		
		$this->SetX(176);
		$this->Cell(32.5, 4, nf($nTotalAmount), 0, 1, 'R');
		$this->Ln(1);
	}

	/**
	 * Render Detail Tail
	 *
	 * @return void
	 */
	function render_detailtail() {
		if($this->header[OrderCategory]=='Invoice') {
			if($this->GetY() > 195) $this->AddPage();
			$this->Cell(0, 4, 'SUBJECT TO TERMS AND CONDITIONS STATED AT THE BACK.', 0, 1, 'C');
			$this->Cell(0, 4, 'VAT REGISTERED as of February 27, 1998 R. D. O. No. 43', 0, 1, 'C');
		} else {
			if($this->GetY() > 180) $this->AddPage();
			$this->Cell(180, 4, ' -------------------------TERMS OF STORAGE AGREEMENT--------------------------', 0, 1);
			$this->Cell(180, 4, '       The merchandise represented by this document are being delivered to you for storage/safekeeping. These ', 0, 1);
			$this->Cell(180, 4, '  merchandise shall be and remain the property of Filstar Distributors Corporation. If circumstances require, ', 0, 1);
			$this->Cell(180, 4, '  authorized to withdraw these stocks, but we will bill you accordingly. In the event you are of a sale, the', 0, 1);
			$this->Cell(180, 4, '  proceeds will be collateralized in favor of Filstar Distributors Corporation. Sold items are due and payable', 0, 1);
			$this->Cell(180, 4, '  upon presentation of an invoice. ', 0, 1);
			$this->Cell(180, 4, '       Stocks can be pulled out by Filstar Distributors Corporation if, in its judgement there is a need to recover', 0, 1);
			$this->Cell(180, 4, '  and pull out the items stored. In case of loss of stocks in your hands, Filstar Distributors Corporation has the ', 0, 1);
			$this->Cell(180, 4, '  right to require payment if you cannot show due dilligence in the handling and care of these stocks.', 0, 1);
		}
		$this->LastPage = true;
		$this->Ln(3);
		$this->Cell(100, 4, 'Reference Number:', 0, 1);
		$this->Cell(100, 4, $this->header[RefNo], 0, 0);
		$this->Ln(3);
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
		$this->Ln(7);
		$this->Cell(100, 4, 'REMARKS:'.$instaruction, 0, 1);
		mysql_close($conn_ho);
		
		$this->Ln(7);

	}


	/**
	 * PDF Header
	 *
	 * @return void
	 */
	function Header() {
		
		$code = $this->header["InvoiceNo"]; 
		$path =  "../wReports/temp/";
		
		Generate_Barcode_Image($code, $path, "{$code}.png");
		
		//$this->Image(,80,90,43,30);
		
		$this->Image("$path$code.png",160,12,45,9); //Image(name, x, y, w, h)
		
		$this->SetFont('helvetica', '', 11);

		$CustAddress = explode('||', $this->header["StreetNumber"].'||'.$this->header["TownCity"]);
		
		if($this->header[OrderCategory]=='Invoice') {
			/**
			 * START EDIT
			 */
			$is_mercury	=	substr($this->header["CustName"],0,12);
			$this->SetXY(106, 57); $this->Cell(10, 6, $this->header["CustNo"], 0, 0, 'L');
			if ($is_mercury=='Mercury Drug')
			{
				$this->SetXY(12, 34); $this->Cell(100, 6, "Mercury Drug Corporation ", 0, 0, 'L');
				$this->SetXY(12, 37.5); $this->Cell(100, 6, "No.7 Mercury Ave. cor. C.P. Garcia Ave. Bagumbayan Quezon City - 1110", 0, 0, 'L');
				
				// Customer
				$this->SetXY(12, 42); $this->Cell(100, 6, "DELIVER TO :", 0, 0, 'L');
				$this->SetXY(38, 42); $this->Cell(100, 6, $this->header["CustName"], 0, 0, 'L');
				$this->SetXY(38, 45.5); $this->Cell(100, 6, $CustAddress[0], 0, 0, 'L');
				$this->SetXY(38, 49.5); $this->Cell(100, 6, $CustAddress[1], 0, 0, 'L');
			}
			/**
			 * for 7/11
			 * 2016-03-16
			 */
			elseif ($this->header["CustNo"] == '106297O' || $this->header["CustNo"] == '106313O' || $this->header["CustNo"] == '106329O' || $this->header["CustNo"] == '106229O')
			{
				$this->SetXY(12, 34); $this->Cell(100, 6, "Philippines Seven Corporation ", 0, 0, 'L');
				$this->SetXY(12, 37.5); $this->Cell(100, 6, "7th Floor,The Columbia Tower Ortigas Avenue Mandaluyong City 1550 Philippines", 0, 0, 'L');
				
				// Customer
				$this->SetXY(12, 42); $this->Cell(100, 6, "DELIVER TO :", 0, 0, 'L');
				$this->SetXY(38, 42); $this->Cell(100, 6, $this->header["CustName"], 0, 0, 'L');
				$this->SetXY(38, 45.5); $this->Cell(100, 6, $CustAddress[0], 0, 0, 'L');
				$this->SetXY(38, 49.5); $this->Cell(100, 6, $CustAddress[1], 0, 0, 'L');
			}
			else 
			{
				$this->SetXY(12, 43.5); $this->Cell(100, 6, $CustAddress[0], 0, 0, 'L');
				$this->SetXY(12, 48.5); $this->Cell(100, 6, $CustAddress[1], 0, 0, 'L');
	
				$this->SetXY(106, 57); $this->Cell(10, 6, $this->header["CustNo"], 0, 0, 'L');
				$this->SetXY(12, 34); $this->Cell(100, 6, $this->header["CustName"], 0, 0, 'L');
			}
			
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

			$this->SetY(85);
		} else {
			// Customer
			$this->SetXY(14, 42); $this->Cell(100, 6, $CustAddress[0], 0, 0, 'L');
			$this->SetXY(14, 47); $this->Cell(100, 6, $CustAddress[1], 0, 0, 'L');

			$this->SetXY(108, 59); $this->Cell(10, 6, $this->header["CustNo"], 0, 0, 'L');
			$this->SetXY(14, 36.5); $this->Cell(100, 6, $this->header["CustName"], 0, 0, 'L');

			$this->SetXY(57, 30.5); $this->Cell(50, 5, 'PL CONF: '.$this->header['PickListTime'].' ('.($this->header['Territory']==1?'Manila':'Provl').')', 0);
			$this->SetXY(27, 51.5); $this->Cell(90, 6, $this->header["TownCity"], 0, 0, 'L');

			// Invoice/STF Number
			$this->SetXY(182, 33); $this->Cell(28, 7, $this->header["InvoiceNo"], 0, 0, 'L');
			$this->SetXY(182, 39.5); $this->Cell(28, 7, $this->header["OrderNo"], 0, 0, 'L');
			$this->SetXY(182, 46); $this->Cell(28, 7, $this->header["PickListNo"], 0, 0, 'L');
			$this->SetXY(182, 52); $this->Cell(28, 7, $this->header["InvoiceDate"], 0, 0, 'L');
			$this->SetXY(182, 58); $this->Cell(28, 7, "CHARGE", 0, 0, 'L');
			$this->SetXY(182, 64); $this->Cell(28, 7, "As Stated", 0, 0, 'L');

			// Salesrep
			$this->SetXY(39, 66); $this->Cell(60, 6, $this->header["SalesRepName"], 0, 0, 'L');
			$this->SetXY(138.5, 66); $this->Cell(10, 6, $this->header["SalesRepCode"], 0, 0, 'L');

			$this->SetY(85);
		}
	}

	/**
	 * PDF Footer
	 *
	 * @return void
	 */
	function Footer() 
	{
		global $rnc, $rnt;

		if ($this->LastPage) 
		{
			if ($this->header[OrderCategory] == 'Invoice') 
			{
				if($this->header[TaxSuffix] == 'VAT') 
				{
//					$this->Cell(165, 4, 'TOTAL VATABLE SALES'); 
					
					$nVatableSales = (array_sum($this->total[NetAmount]) / $this->Tax['div']);
					$nVat          = ((array_sum($this->total[NetAmount]) / $this->Tax['div']) * $this->Tax[mul]);
					$nTotalAmount  = nf($nVatableSales+$nVat);
					
					$this->SetXY(175, 215); $this->Cell(32, 4, nf($nVatableSales), 0, 1, 'R');
					
//					$this->Cell(165, 4, $this->Tax[base].'% VAT'); 
					$this->SetXY(25, 225);  $this->Cell(32, 4, $this->Tax[base].'%', 0, 1, 'L');
				
					$this->SetXY(175, 225); $this->Cell(32, 4, nf($nVat), 0, 1, 'R');
//					$this->Cell(165, 4, 'TOTAL AMOUNT PAYABLE'); 
//					$this->SetXY(165, 204); $this->Cell(32, 4, nf(array_sum($this->total[NetAmount])+$tax), 0, 1, 'R');
				} 
				else 
				{
//					$this->Cell(165, 4, 'ZERO-RATED SALE'); 
					$nNonVatableSales = array_sum($this->total[NetAmount]);
					$nTotalAmount     = nf($nNonVatableSales);
					$this->SetXY(175, 215); $this->Cell(32, 4, nf($nNonVatableSales), 0, 1, 'R');
				}
			}
			else 
			{
				$nTotalAmount = nf(array_sum($this->total[NetAmount]));
			}

			$this->SetXY(55, 253); $this->Cell(100, 4, $this->header["CustName"]);

			$this->SetXY(1, 230); $this->Cell(42, 4, 'Records', 0, 0, 'C');
			$this->SetXY(1, 235); $this->Cell(42, 4, $this->header[cnt], 0, 0, 'C');
	
			$this->SetXY(85, 230); $this->Cell(18, 4, date('G:i:s'), 0, 0, 'C');
			$this->SetXY(85, 235); $this->Cell(18, 4, array_sum($this->total[OrderQty]), 0, 0, 'C', 0);
			
			$this->GrossAmount = ($this->header[OrderCategory]=='STF' OR $this->header[EnterpriseCode]!='00001')?nf(array_sum($this->total[GrossAmount])):'';
	
			$this->SetXY(145, 240); $this->Cell(31, 4, $this->GrossAmount, 0, 0, 'R');
	
			if($this->header[OrderCategory]=='Invoice' && $this->header[TaxSuffix]=='VAT') 	
	
			$this->SetXY(174, 235); $this->Cell(32.5, 4, $nTotalAmount, 0, 0, 'R');
			$pagemsg = '*** Last Page';
		} else $pagemsg = 'Next Page Please';
		$this->SetXY(145, 230); $this->Cell(29, 4, 'Page '.$this->PageNo(), 0, 0);

		$this->SetXY(174, 230); $this->Cell(34.5, 4, $pagemsg, 0, 0, 'R');
	}
}
$idx = new formSTFInvoicePrinting();
//"../wReports/InvStfFormPrintingRep.php?data=<?php echo "&act=".addslashes(urlencode('Print'))."&who=".addslashes(urlencode($OrderNo));
?>