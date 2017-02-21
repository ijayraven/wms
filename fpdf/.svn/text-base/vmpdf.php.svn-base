<?php
/*
+--------------------------------------------------------------------------
|   Data Edge One Financials
|   (c) 2005 Data Edge Corporation
|   ========================================
|   Ryan Joel Patawaran
|   Email: zildjian@ispx.com.ph
+---------------------------------------------------------------------------
|
|   > PDF report class
|	>
|   > Module written by Ryan Joel Patawaran
|   > Date started: 1st July 2005
|	> Last changed: $Date: 2006-05-18 10:41:40 +0800 (Thu, 18 May 2006) $
|	> $Id: vmpdf.php 1210 2006-05-18 02:41:40Z ryan1 $

+--------------------------------------------------------------------------
*/

define('FPDF_FONTPATH','Sources/includes/font/');
require "Sources/includes/fpdf.php";

class report2 extends FPDF {
		var $title1 = 'FILSTAR DISTRIBUTOR CORPORATION';
		var $title2	= '';
		var $title3 = '';
		var $r = '';
		var $header = array();
		
		//Page header
		function Header() {
			$this->SetFont('Courier','B',10);
			$this->SetY(11);
			$this->Cell(0, 5, $this->title1, 0, 0, 'C');
			$this->SetFont('Courier','B',9);
			$this->SetY(16);
			$this->Cell(0, 4, $this->title2, 0, 0, 'C');
			$this->SetY(20);
			$this->Cell(0, 4, $this->title3, 0, 0, C);
			$this->SetY(11);
			$this->SetFont('Courier','',8);
			$this->Cell(0, 4, date("l dS of F Y h:i:s A"), 0, 0, R);
//			$this->Ln(20);
			
			$this->SetFillColor(200,220,220);
			$this->setY(25);
			$i = 10;
			$maxwidth = '277.5';			
			foreach (array_keys($this->header) as $x => $y) {
				if ($this->header[$y]['w']{strlen($this->header[$y]['w'])-1} == '%') $this->header[$y]['w'] = $this->header[$y]['w'] * ($maxwidth/100);
//				$this->setXY($i, $j*($f-4)+30);
				$this->setX($i);
				$this->Cell($this->header[$y]['w'],4,ucfirst(strtolower($this->header[$y]['title'])),1,0,C,1);
				$i += $this->header[$y]['w']-1;
				$i++;
			}
			$this->Ln(4.5);
		}
	
		//Page footer
		function Footer() {
			$this->SetY(-15);
			$this->SetFont('Courier','I',8);
			$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		}
		
		function outReport($r, $header) {
			$this->header = $header;
			$this->AliasNbPages();
			$this->AddPage();

			$f = 8;
			$i = 10;

			$this->SetFont('Courier','',$f);
			
			$maxwidth = '277.5';
//			$this->setY(30);
	
			$nx = 34;
			foreach ($r as $x => $y) {
				unset($xNr);
				
				//data
				$this->SetFont('Courier','',$f);
				foreach ($y[result] as $j => $k) {
					$i = 10;
					foreach (array_keys($header) as $y) {
						if ($header[$y]['w']{strlen($header[$y]['w'])-1} == '%') $header[$y]['w'] = $header[$y]['w'] * ($maxwidth/100);
						$this->setX($i);
						$this->Cell($header[$y]['w'], 8, $xNr[$y] ? '' : $k[$y], 0, 0, $header[$y]['align']);
						$i += $header[$y]['w']-1;
						$i++;
						if ($header[$y]['total']) $total[$y] += $k[$y];
						if ($header[$y]['nr']) $xNr[$y] = true; 
					}
					$this->Ln(4.5);
					$end = $j*($f-4)+34;	
				}
//				$nx += 1.2;
				$this->Ln(1.2);
				
				//total?
				$this->SetFont('Courier','B',$f);
				$i = 10;
				foreach (array_keys($header) as $x => $y) {
					if ($header[$y]['total']) {
						$this->setX($i);
						$this->Line($i+1, $this->GetY(), $i+$header[$y]['w']-1, $this->GetY());
//						$this->Line($i+1, $this->GetX()+0.6, $i+$header[$y]['w']-1, $this->GetX()+0.6);
						$this->Cell($header[$y]['w'], 4, ($total[$y]) ? number_format($total[$y], 2, '.', ',') : '', 0, 0, R, 0);
						$grandTotal[$y] += $total[$y];
					}
					$i += $header[$y]['w']-1;
					$i++;
				}
				$this->Ln(3);
			}
			$this->Ln(3);
			
			// grand total
			$i = 10;
			foreach (array_keys($header) as $x => $y) {
				if ($header[$y]['total']) {
					$this->setX($i);
					$this->Line($i+1, $this->GetY()-0.3, $i+$header[$y]['w']-1, $this->GetY()-0.3);
					$this->Line($i+1, $this->GetY()+0.4, $i+$header[$y]['w']-1, $this->GetY()+0.4);
					$this->Cell($header[$y]['w'], 4, ($grandTotal[$y]) ? number_format($grandTotal[$y], 2, '.', ',') : '', 0, 0, R, 0);
				}
				$i += $header[$y]['w']-1;
				$i++;
			}	
		
			
			$this->Ln(5);
			$this->Cell(0, 4, '***** End of Report *****', 0, 0, 'C');
	
	
			return $this->Output();
		}
		
	
	}

	class report extends FPDF {
		var $title1 = 'FILSTAR DISTRIBUTOR CORPORATION';
		var $title2	= '';
		var $title3 = '';
		var $title4;
		var $r = '';
		var $header = array();
		var $bodySuffix;

		function report($orientation='P',$unit='mm',$format='A4') {
			$this->FPDF($orientation, $unit, $format);
		}

		//Page header
		function Header() {
			$this->SetFont('Courier','B',10);
			$this->SetY(11);
			$this->Cell(0, 5, $this->title1, 0, 0, 'C');

			$this->SetFont('Courier','',8);
			$this->Cell(0, 4, date("l dS of F Y h:i:s A"), 0, 1, R);

//			$this->SetY(16);
			if($this->title2) $this->Cell(0, 3, $this->title2, 0, 1, 'C');
			if($this->title3) $this->Cell(0, 3, $this->title3, 0, 1, 'C');
			if($this->title4) $this->Cell(0, 3, $this->title4, 0, 1, 'C');
			$this->Ln(2);
// 			$this->SetFillColor(200,220,220);
			
//			$this->setY(25);
			$i = 10;
			$maxwidth = '277.5';			
			foreach (array_keys($this->header) as $x => $y) {
				if ($this->header[$y]['w']{strlen($this->header[$y]['w'])-1} == '%') $this->header[$y]['w'] = $this->header[$y]['w'] * ($maxwidth/100);
//				$this->setXY($i, $j*($f-4)+30);
				$this->setX($i);
				$this->Cell($this->header[$y]['w'],4,$this->header[$y]['title'],1,0,C,0);
				$i += $this->header[$y]['w']-1;
				$i++;
			}
			$this->Ln(3.5);
		}
	
		//Page footer
		function Footer() {
			$this->SetY(-15);
			$this->SetFont('Courier','I',8);
			$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		}
		
		function outReport($r, $header) {
			$this->header = $header;
			$this->AliasNbPages();
			$this->AddPage();

			$f = 8;
			$i = 10;

			$this->SetFont('Courier','',$f);
			
			$maxwidth = '277.5';

			foreach ($r as $j => $k) {
				$i = 10;
				foreach (array_keys($header) as $y) {
					if ($header[$y]['w']{strlen($header[$y]['w'])-1} == '%') $header[$y]['w'] = $header[$y]['w'] * ($maxwidth/100);
					$this->setX($i);
					$this->Cell($header[$y]['w'], 8, $xNr[$y] ? '' : $header[$y]['nf'] ? nf($k[$y]) : $k[$y], 0, 0, $header[$y]['align']);
					$i += $header[$y]['w']-1;
					$i++;
					if ($header[$y]['total']) $total[$y] += $k[$y];
					if ($header[$y]['nr']) $xNr[$y] = true; 
				}
				$this->Ln(4.5);
			}
			$this->Ln(1);

			//total?
			$this->SetFont('Courier','B',$f);
			$i = 10;
			foreach (array_keys($header) as $x => $y) {
				if ($header[$y]['total']) {
					$this->setX($i);
					$this->Line($i+1, $this->GetY(), $i+$header[$y]['w']-1, $this->GetY());
//						$this->Line($i+1, $this->GetX()+0.6, $i+$header[$y]['w']-1, $this->GetX()+0.6);
					$this->Cell($header[$y]['w'], 4, ($total[$y]) ? nf($total[$y]) : '', 0, 0, R, 0);
					$grandTotal[$y] += $total[$y];
				}
				$i += $header[$y]['w']-1;
				$i++;
			}

			$this->SetFont('Courier','',$f);
			eval($this->bodySuffix);
	
			$this->Ln(5);
			$this->Cell(0, 4, '***** End of Report *****', 0, 0, 'C');
			return $this->Output();
		}
	
	}
?>