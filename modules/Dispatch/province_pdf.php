<?php
	/**
	 * Author		:	Raymond A. Galaroza
	 * Date Created	:	2013-08-02
	 * Description	:	Print of Dispatch Schedule(PROVINCE)
	 */
	session_start();
	set_time_limit(0);
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
	if (empty($_SESSION['username'])) 
	{
		echo "<script>alert('You dont have a session!');</script>";
		echo "<script>location='../../index.php'</script>";
	}
	
	$SCHEDULE_ID	=	$_GET['schedule_id'];
	$cnt		=	" SELECT COUNT(ID) AS CNT FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_HDR WHERE ID = '{$SCHEDULE_ID}' ";
	$rscnt		=	$Filstar_conn->Execute($cnt);
	if ($rscnt->fields['CNT'] > 0) 
	{
		$aData		=	array();
		$tracking_no	=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","TRANSEQ","ID = '{$SCHEDULE_ID}'");
		$preparedby		=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","PREPAREDBY","ID = '{$SCHEDULE_ID}'");
		$instruction	=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","SPECIAL_INSTRUCTION","ID = '{$SCHEDULE_ID}'");
		
		$sel	 =	" SELECT HDR.ID,HDR.DATE,HDR.ROUTE,HDR.VANNO,HDR.PLATENO,HDR.DRIVER,HDR.HELPER,HDR.FORWARDER, ";
		$sel	.=	" DTL.CUSTCODE,DTL.CUSTNAME,DTL.INVOICENO,DTL.SOFNO,DTL.PRODUCT_LINE,DTL.SIZE_OF_CTN,DTL.QTY_CTN,";
		$sel	.=	" DTL.DR_NO,DTL.WAYBILL_NUMBER,DTL.WEIGHT_BY_KILO ";
		$sel	.=	" FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_HDR HDR ";
		$sel	.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL AS DTL ON DTL.ID = HDR.ID ";
		$sel	.=	" WHERE HDR.ID = '{$SCHEDULE_ID}' ORDER BY DTL.INVOICENO ASC";
		$rssel	=	$Filstar_conn->Execute($sel);
		if ($rssel == false) 
		{
			die(mysql_errno().":".mysql_error());
		}
		else 
		{
			while (!$rssel->EOF) 
			{
				$id				=	$rssel->fields['ID'];
				$date			=	$rssel->fields['DATE'];
				$route			=	$rssel->fields['ROUTE'];
				$vanno			=	$rssel->fields['VANNO'];
				$plateno		=	$rssel->fields['PLATENO'];
				$driver			=	$rssel->fields['DRIVER'];
				$helper			=	$rssel->fields['HELPER'];
				$forward		=	$rssel->fields['FORWARDER'];
				$custno			=	$rssel->fields['CUSTCODE'];
				$custname		=	$rssel->fields['CUSTNAME'];
				$invoiceno		=	$rssel->fields['INVOICENO'];
				$sofno			=	$rssel->fields['SOFNO'];
				$prodline		=	$rssel->fields['PRODUCT_LINE'];
				$size_of_ctn	=	$rssel->fields['SIZE_OF_CTN'];
				$qty_ctn		=	$rssel->fields['QTY_CTN'];
				$dr_no			=	$rssel->fields['DR_NO'];
				$waybill_number	=	$rssel->fields['WAYBILL_NUMBER'];
				$weight_by_kilo	=	$rssel->fields['WEIGHT_BY_KILO'];
				
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['CUSTNAME']		=	$custname;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['INVOICENO']		=	$invoiceno;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['INVOICEDATE']	=	$invoicedate;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['INVOICEAMT']		=	$invoiceamt;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['SOFNO']			=	$sofno;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['PRODLINE']		=	$prodline;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['SIZE_OF_CTN']	=	$size_of_ctn;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['QTY_CTN']		=	$qty_ctn;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['DR_NO']			=	$dr_no;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['WAYBILL_NUMBER']	=	$waybill_number;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['WEIGHT_BY_KILO']	=	$weight_by_kilo;
				$rssel->MoveNext();
			}
			
			$done	=	"UPDATE ".DISPATCH_DB.".DISPATCH_PROVINCE_HDR SET ISPRINT = 'Y' WHERE ID = '{$SCHEDULE_ID}' ";
			$Filstar_conn->Execute($done);
		}
	}
	else 
	{
		$aData	=	array();
	}
	
	class PDF extends FPDF 
	{
		function Header_ko($val_array,$page_cnt,$data_limit,$val_key,$tracking_no)
		{
			$total_cnt[$val_key]	=	1;
			$header_cnt				=	0;
			foreach ($val_array as $key_date=>$val_2)
			{
				$val_date		=	$key_date;		//	DATE
				foreach ($val_2 as $key_route=>$val_3)
				{
					$val_route		=	$key_route;		//ROUTE
					foreach ($val_3 as $key_van=>$val_4)
					{
						$val_van		=	$key_van;		//VAN NO.
						foreach ($val_4 as $key_plateno=>$val_5)
						{
							$val_plateno	=	$key_plateno;	//PLATE NO.
							foreach ($val_5 as $key_driver=>$val_6)
							{
								$val_driver		=	$key_driver;	//DRIVER
								foreach ($val_6 as $key_helper=>$val_7)
								{
									$val_helper		=	$key_helper;	//HELPER	
									foreach ($val_7 as $key_forwarder=>$val8)
									{
										$val_forwarder	=	$key_forwarder;	//FORWARDER
										foreach ($val8 as $key_custno=>$val9)
										{
											$header_cnt++;
											if($header_cnt == $data_limit)
											{
												$total_cnt[$val_key]++;
												$header_cnt	=	0;
											}

										}	
									}
								}
							}
						}
					}
				}
			}

			$this->SetFont('Times','I',12);
			$this->SetX(10);$this->Cell(50,10,'TRACKING NO.'.$tracking_no,0,0,'L');
			$this->SetX(275);$this->Cell(50,10,'PROVINCE',0,0,'R');
		    $this->SetX(295);$this->Cell(50,10,'Page'.$page_cnt.'/'.$total_cnt[$val_key],0,1,'R');
		    
			$this->SetFont('Times','B',11);
			$this->SetX(10);$this->Cell(85,5,FDC_HEADER,"LTR",0,'C');
			$this->SetFont('Times','',11);
			$this->SetX(95);$this->Cell(25,5,"DATE",1,0,'C');
			$this->SetX(120);$this->Cell(30,5,"ROUTE",1,0,'C');
			$this->SetX(150);$this->Cell(22,5,"VAN NO.",1,0,'C');
			$this->SetX(172);$this->Cell(25,5,"PLATE NO.",1,0,'C');
			$this->SetX(197);$this->Cell(155,5,"DRIVER:$val_driver",1,1,'L');
			
			$this->SetFont('Times','B',20);
			$this->SetX(10);$this->Cell(85,15,DISPATCH_HEADER,"LRB",0,'C');
			$this->SetFont('Times','',12);
			$this->SetX(95);$this->Cell(25,15,$val_date,1,0,'C');
			$this->SetX(120);$this->Cell(30,15,$val_route,1,0,'C');
			$this->SetX(150);$this->Cell(22,15,$val_van,1,0,'C');
			$this->SetX(172);$this->Cell(25,15,$val_plateno,1,0,'C');
			$this->SetX(197);$this->Cell(155,7,"HELPER:$val_helper",1,1,'L');
			$this->SetX(197);$this->Cell(155,8,"FORWARDER:",1,1,'L');
			
			$this->SetFont('Times','B',12);
			$this->SetX(10);$this->Cell(85,10,"CUSTOMER NAME & ADDRESS","1",0,'C');
			$this->SetFont('Times','',12);
			$this->SetX(95);$this->Cell(25,5,"INVOICE","LR",0,'C');
			$this->SetX(120);$this->Cell(30,5,"SOF","LR",0,'C');
			$this->SetX(150);$this->Cell(22,5,"PRODUCT","LR",0,'C');
			$this->SetX(172);$this->Cell(25,5,"SIZE","LR",0,'C');
			$this->SetX(197);$this->Cell(25,5,"QTY","LR",0,'C');
			$this->SetX(222);$this->Cell(25,5,"DR NO","LR",0,'C');
			$this->SetX(247);$this->Cell(25,5,"WAYBILL","LR",0,'C');
			$this->SetX(272);$this->Cell(55,5,"RECEIVED",1,0,'C');
			$this->SetX(327);$this->Cell(25,5,"WEIGHT",1,1,'C');
			
			$this->SetFont('Times','',12);
			$this->SetX(95);$this->Cell(25,5,"NUMBER","LBR",0,'C');
			$this->SetX(120);$this->Cell(30,5,"NUMBER","LBR",0,'C');
			$this->SetX(150);$this->Cell(22,5,"LINE","LBR",0,'C');
			$this->SetX(172);$this->Cell(25,5,"OF CTN","LBR",0,'C');
			$this->SetX(197);$this->Cell(25,5,"CTN","LBR",0,'C');
			$this->SetX(222);$this->Cell(25,5,"","LBR",0,'C');
			$this->SetX(247);$this->Cell(25,5,"NUMBER","LBR",0,'C');
			$this->SetX(272);$this->Cell(27,5,"BY:",1,0,'C');
			$this->SetX(299);$this->Cell(28,5,"DATE",1,0,'C');
			$this->SetX(327);$this->Cell(25,5,"(BY KILO)",1,1,'C');
		}
		
		function Footer()
		{
			global $instruction,$preparedby;
			$this->SetFont('Times','I',12);
		    $this->SetXY(10,-35);$this->Cell(85,5,"SPECIAL INSTRUCTION","LTR",0,'L');
		    $this->SetFont('Times','',12);
		    $this->SetXY(95,-35);$this->Cell(75,5,"PREPARED","LTR",0,'L');
		    $this->SetXY(170,-35);$this->Cell(53,5,"CHECKED AND","LTR",0,'L');
		    $this->SetXY(223,-35);$this->Cell(75,5,"APPROVED","LTR",0,'L');
		    $this->SetXY(298,-35);$this->Cell(55,5,"GUARD ON DUTY","LTR",1,'L');
		    
		    $this->SetXY(10,-30);$this->Cell(85,5,"","LR",0,'L');
		    $this->SetXY(95,-30);$this->Cell(75,5,"BY:","LR",0,'L');
		    $this->SetXY(170,-30);$this->Cell(53,5,"DISPATCHED BY:","LR",0,'L');
		    $this->SetXY(223,-30);$this->Cell(75,5,"BY:","LR",0,'L');
		    $this->SetXY(298,-30);$this->Cell(55,5,"","LR",1,'C');
		    
		    $this->SetXY(10,-25);$this->Cell(85,10,$instruction,"LRB",0,'L');
		    $this->SetXY(95,-25);$this->Cell(75,10,$preparedby,1,0,'L');
		    $this->SetXY(170,-25);$this->Cell(53,10,"",1,0,'L');
		    $this->SetXY(223,-25);$this->Cell(75,10,"",1,0,'L');
		    $this->SetXY(298,-25);$this->Cell(55,10,"",1,1,'L');
		}
	}
	
	$pdf = new PDF('L','mm','Legal');
	$pdf->Open();
	$pdf->AliasNbPages();
	$pdf->SetFont('Times','',9);
	
	if (is_array($aData)) 
	{
		if (count($aData) > 0) 
		{
			$data_limit	=	22;
			foreach ($aData as $key_id=>$val_1)
			{
				$cnt		=	0;
				$page_cnt	=	1;
				$pdf->AddPage();
				$pdf->Header_ko($val_1,$page_cnt,$data_limit,$key_id,$tracking_no);
				foreach ($val_1 as $key_date=>$val_2)
				{
					foreach ($val_2 as $key_route=>$val_3)
					{
						foreach ($val_3 as $key_van=>$val_4)
						{
							foreach ($val_4 as $key_plateno=>$val_5)
							{
								foreach ($val_5 as $key_driver=>$val_6)
								{
									foreach ($val_6 as $key_helper=>$val_7)
									{
										foreach ($val_7 as $key_forwarder=>$val8)
										{
											foreach ($val8 as $key_custno=>$val9)
											{
												$cnt++;
												$picklist		=	$global_func->Select_val($Filstar_conn,FDCRMS,"orderheader","PickListNo","CustNo= '".$key_custno."' AND InvoiceNo= '".$val9['INVOICENO']."'");
												$pdf->SetFont('courier','',9);
												$pdf->SetX(10);$pdf->Cell(85,5,$val9['CUSTNAME'],"LR",0,'L');
												$pdf->SetX(95);$pdf->Cell(25,5,"","LR",0,'C');
												$pdf->SetX(120);$pdf->Cell(30,5,"","LR",0,'C');
												$pdf->SetX(150);$pdf->Cell(22,5,"","LR",0,'C');
												$pdf->SetX(172);$pdf->Cell(25,5,"","LR",0,'C');
												$pdf->SetX(197);$pdf->Cell(25,5,"","LR",0,'C');
												$pdf->SetX(222);$pdf->Cell(25,5,"","LR",0,'C');
												$pdf->SetX(247);$pdf->Cell(25,5,"","LR",0,'C');
												$pdf->SetX(272);$pdf->Cell(27,5,"","LR",0,'C');
												$pdf->SetX(299);$pdf->Cell(28,5,"","LR",0,'C');
												$pdf->SetX(327);$pdf->Cell(25,5,"","LR",1,'C');
												
												$street	=	$global_func->Select_val($Filstar_conn,FDCRMS,"customer_address","StreetNumber","CUSTNO= '$key_custno'");
												$town	=	$global_func->Select_val($Filstar_conn,FDCRMS,"customer_address","TownCity","CUSTNO= '$key_custno'");
												$add	=	$street.','.$town;
												
												$a		=	$global_func->str_concat($add,40);
												foreach ($a as $b=>$c)
												{
													$z	=	count($c);
													$x	=	1;
													foreach ($c as $d)
													{
														$cnt++;
														if ($z != $x) 
														{
															$pdf->SetX(10);$pdf->Cell(85,5,$d,"LR",0,'L');
															$pdf->SetX(95);$pdf->Cell(25,5,"","LR",0,'C');
															$pdf->SetX(120);$pdf->Cell(30,5,"","LR",0,'C');
															$pdf->SetX(150);$pdf->Cell(22,5,"","LR",0,'C');
															$pdf->SetX(172);$pdf->Cell(25,5,"","LR",0,'C');
															$pdf->SetX(197);$pdf->Cell(25,5,"","LR",0,'C');
															$pdf->SetX(222);$pdf->Cell(25,5,"","LR",0,'C');
															$pdf->SetX(247);$pdf->Cell(25,5,"","LR",0,'C');
															$pdf->SetX(272);$pdf->Cell(27,5,"","LR",0,'C');
															$pdf->SetX(299);$pdf->Cell(28,5,"","LR",0,'C');
															$pdf->SetX(327);$pdf->Cell(25,5,"","LR",1,'C');
														}
														else 
														{
															$pdf->SetX(10);$pdf->Cell(85,5,$d,"LBR",0,'L');
															$pdf->SetX(95);$pdf->Cell(25,5,$val9['INVOICENO'],"LBR",0,'C');
															$pdf->SetX(120);$pdf->Cell(30,5,$val9['SOFNO'],"LBR",0,'R');
															$pdf->SetX(150);$pdf->Cell(22,5,$val9['PRODLINE'],"LBR",0,'C');
															$pdf->SetX(172);$pdf->Cell(25,5,$val9['SIZE_OF_CTN'],"LBR",0,'R');
															$pdf->SetX(197);$pdf->Cell(25,5,$val9['QTY_CTN'],"LBR",0,'R');
															$pdf->SetX(222);$pdf->Cell(25,5,$val9['DR_NO'],"LBR",0,'R');
															$pdf->SetX(247);$pdf->Cell(25,5,$val9['WAYBILL_NUMBER'],"LBR",0,'R');
															$pdf->SetX(272);$pdf->Cell(27,5,"","LBR",0,'C');
															$pdf->SetX(299);$pdf->Cell(28,5,"","LBR",0,'C');
															$pdf->SetX(327);$pdf->Cell(25,5,$val9['WEIGHT_BY_KILO'],"LBR",1,'R');
															$x	=	1;
														}
														$x++;
													}
												}
												if($cnt > $data_limit)
												{
													$page_cnt++;
													$pdf->AddPage();
													$pdf->Header_ko($val_1,$page_cnt,$data_limit,$key_id);
													$cnt	=	0;
												}
											}
										}
									}
								}
							}
						}
					}
				}
				$pdf->Ln(5);
				$pdf->SetFont('Times','B',12);
			 	$pdf->SetX(10);$pdf->Cell(0,10,"* * * * * NOTHING FOLLOWS * * * * * ",0,0,'C');
			}
		}
		else 
		{
			$pdf->AddPage();
			$pdf->Header_ko($val_1,$page_cnt,$data_limit,$key_id);
			$pdf->Ln(10);
			$pdf->SetFont('Times','B',20);
		 	$pdf->SetX(10);$pdf->Cell(0,10,"* * * * * No Record(s) Found * * * * * ",0,0,'C');
		}
		$pdf->Output();
	}
?>