<?php
	/**
	 * Author		:	Raymond A. Galaroza
	 * Date Created	:	2013-08-02
	 * Description	:	Print of Dispatch Schedule(PANDAYAN)
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
	$cnt		=	" SELECT COUNT(ID) AS CNT FROM ".DISPATCH_DB.".DISPATCH_PANDAYAN_HDR WHERE ID = '{$SCHEDULE_ID}' ";
	$rscnt			=	$Filstar_conn->Execute($cnt);
	if ($rscnt->fields['CNT'] > 0) 
	{
		$aData			=	array();
		$tracking_no	=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","TRANSEQ","ID = '{$SCHEDULE_ID}'");
		$preparedby		=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","PREPAREDBY","ID = '{$SCHEDULE_ID}'");
		$instruction	=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","SPECIAL_INSTRUCTION","ID = '{$SCHEDULE_ID}'");
		
		$sel		 =	" SELECT HDR.ID,HDR.DATE,HDR.ROUTE,HDR.VANNO,HDR.PLATENO,HDR.DRIVER,HDR.HELPER,HDR.FORWARDER, ";
		$sel	.=	" DTL.CUSTCODE,DTL.CUSTNAME,DTL.INVOICENO,DTL.INVOICEDATE,DTL.INVOICEAMOUNT,DTL.SOFNO, ";
		$sel	.=	" DTL.PLNO,DTL.PONO,DTL.PRODUCTLINE,DTL.CARTON,DTL.PACKAGE,DTL.REMARKS ";
		$sel	.=	" FROM ".DISPATCH_DB.".DISPATCH_PANDAYAN_HDR HDR ";
		$sel	.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_PANDAYAN_DTL AS DTL ON DTL.ID = HDR.ID ";
		$sel	.=	" WHERE HDR.ID = '{$SCHEDULE_ID}' ORDER BY DTL.INVOICENO ASC";
		$rssel	=	$Filstar_conn->Execute($sel);
		if ($rssel	== false) 
		{
			die(mysql_errno().":".mysql_error());
		}
		else 
		{
			while (!$rssel->EOF) 
			{
				$id			=	$rssel->fields['ID'];
				$date		=	$rssel->fields['DATE'];
				$route		=	$rssel->fields['ROUTE'];
				$vanno		=	$rssel->fields['VANNO'];
				$plateno	=	$rssel->fields['PLATENO'];
				$driver		=	$rssel->fields['DRIVER'];
				$helper		=	$rssel->fields['HELPER'];
				$forward	=	$rssel->fields['FORWARDER'];
				$custno		=	$rssel->fields['CUSTCODE'];
				$custname	=	$rssel->fields['CUSTNAME'];
				$invoiceno	=	$rssel->fields['INVOICENO'];
				$invoicedate=	$rssel->fields['INVOICEDATE'];
				$invoiceamt	=	$rssel->fields['INVOICEAMOUNT'];
				$sofno		=	$rssel->fields['SOFNO'];
				$plno		=	$rssel->fields['PLNO'];
				$pono		=	$rssel->fields['PONO'];
				$prodline	=	$rssel->fields['PRODUCTLINE'];
				$carton		=	$rssel->fields['CARTON'];
				$package	=	$rssel->fields['PACKAGE'];
				$remarks	=	$rssel->fields['REMARKS'];
				
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['CUSTNAME']	=	$custname;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['INVOICENO']	=	$invoiceno;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['INVOICEDATE']=	$invoicedate;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['INVOICEAMT']	=	$invoiceamt;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['SOFNO']		=	$sofno;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['PLNO']		=	$plno;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['PONO']		=	$pono;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['PRODLINE']	=	$prodline;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['CARTON']		=	$carton;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['PACKAGE']	=	$package;
				$aData[$id][$date][$route][$vanno][$plateno][$driver][$helper][$forward][$custno]['REMARKS']	=	$remarks;
				
				$rssel->MoveNext();
			}
		}
		$done	=	"UPDATE ".DISPATCH_DB.".DISPATCH_PANDAYAN_HDR SET ISPRINT = 'Y' WHERE ID = '{$SCHEDULE_ID}' ";
		$Filstar_conn->Execute($done);
	}
	else 
	{
		$aData = array();
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
			$this->SetX(275);$this->Cell(50,10,'PANDAYAN',0,0,'R');
		    $this->SetX(295);$this->Cell(50,10,'Page'.$page_cnt.'/'.$total_cnt[$val_key],0,1,'R');
		    
			$this->SetFont('Times','B',12);
			$this->SetX(10);$this->Cell(100,5,FDC_HEADER,"LTR",0,'C');
			$this->SetFont('Times','',12);
			$this->SetX(110);$this->Cell(35,5,"DATE",1,0,'C');
			$this->SetX(145);$this->Cell(35,5,"ROUTE",1,0,'C');
			$this->SetX(180);$this->Cell(35,5,"VAN NO.",1,0,'C');
			$this->SetX(215);$this->Cell(35,5,"PLATE NO.",1,0,'C');
			$this->SetX(250);$this->Cell(95,5,"DRIVER:$val_driver",1,1,'L');
			
			$this->SetFont('Times','B',20);
			$this->SetX(10);$this->Cell(100,15,DISPATCH_HEADER,"LRB",0,'C');
			$this->SetFont('Times','',12);
			$this->SetX(110);$this->Cell(35,15,$val_date,1,0,'C');
			$this->SetX(145);$this->Cell(35,15,$val_route,1,0,'C');
			$this->SetX(180);$this->Cell(35,15,$val_van,1,0,'C');
			$this->SetX(215);$this->Cell(35,15,$val_plateno,1,0,'C');
			$this->SetX(250);$this->Cell(95,7,"HELPER:$val_helper",1,1,'L');
			$this->SetX(250);$this->Cell(95,8,"",1,1,'L');
			
			$this->SetFont('Times','B',12);
			$this->SetX(10);$this->Cell(100,10,"CUSTOMER NAME & ADDRESS","1",0,'C');
			$this->SetFont('Times','',12);
			$this->SetX(110);$this->Cell(70,5,"INVOICE",1,0,'C');
			$this->SetX(180);$this->Cell(35,5,"SOF",1,0,'C');
			$this->SetX(215);$this->Cell(35,5,"PICKING",1,0,'C');
			$this->SetX(250);$this->Cell(30,5,"PRODUCT",1,0,'C');
			$this->SetX(280);$this->Cell(30,5,"NUMBER OF",1,0,'C');
			$this->SetX(310);$this->Cell(35,5,"","LTR",1,'C');
			$this->SetFont('Times','',12);
			$this->SetX(110);$this->Cell(35,5,"NUMBER",1,0,'C');
			$this->SetX(145);$this->Cell(35,5,"AMOUNT",1,0,'C');
			$this->SetX(180);$this->Cell(35,5,"NUMBER",1,0,'C');
			$this->SetX(215);$this->Cell(35,5,"LIST NO.",1,0,'C');
			$this->SetX(250);$this->Cell(30,5,"LINE",1,0,'C');
			$this->SetX(280);$this->Cell(30,5,"CARTON",1,0,'C');
			$this->SetX(310);$this->Cell(35,5,"REMARKS","LRB",1,'C');
		}
		
		function Footer()
		{
			global $instruction,$preparedby;
			$this->SetFont('Times','I',12);
		    $this->SetXY(10,-35);$this->Cell(100,5,"SPECIAL INSTRUCTION","LTR",0,'L');
		    $this->SetFont('Times','',12);
		    $this->SetXY(110,-35);$this->Cell(35,5,"PREPARED","LTR",0,'C');
		    $this->SetXY(145,-35);$this->Cell(35,5,"CHECKED &","LTR",0,'C');
		    $this->SetXY(180,-35);$this->Cell(35,5,"APPROVED","LTR",0,'C');
		    $this->SetXY(215,-35);$this->Cell(35,5,"GUARD","LTR",0,'C');
		    $this->SetXY(250,-35);$this->Cell(30,5,"TIME LEFT","LTR",0,'C');
		    $this->SetXY(280,-35);$this->Cell(65,5,"","LTR",1,'C');
		    
		    $this->SetXY(10,-30);$this->Cell(100,5,"","LR",0,'L');
		    $this->SetXY(110,-30);$this->Cell(35,5,"BY:","LR",0,'C');
		    $this->SetXY(145,-30);$this->Cell(35,5,"DISPATCHED BY:","LR",0,'C');
		    $this->SetXY(180,-30);$this->Cell(35,5,"BY","LR",0,'C');
		    $this->SetXY(215,-30);$this->Cell(35,5,"ON DUTY","LR",0,'C');
		    $this->SetXY(250,-30);$this->Cell(30,5,"TO DELIVER","LR",0,'C');
		    $this->SetXY(280,-30);$this->Cell(65,5,"TIME ARRIVE FROM DELIVERY","LR",1,'C');
		    
		    $this->SetXY(10,-25);$this->Cell(100,10,$instruction,"LRB",0,'L');
		    $this->SetXY(110,-25);$this->Cell(35,10,$preparedby,1,0,'C');
		    $this->SetXY(145,-25);$this->Cell(35,10,"",1,0,'C');
		    $this->SetXY(180,-25);$this->Cell(35,10,"",1,0,'C');
		    $this->SetXY(215,-25);$this->Cell(35,10,"",1,0,'C');
		    $this->SetXY(250,-25);$this->Cell(30,10,"",1,0,'C');
		    $this->SetXY(280,-25);$this->Cell(65,10,"",1,1,'C');
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
												$pdf->SetX(10);$pdf->Cell(100,5,$val9['CUSTNAME'],"LR",0,'L');
												$pdf->SetX(110);$pdf->Cell(35,5,"","LR",0,'C');
												$pdf->SetX(145);$pdf->Cell(35,5,"","LR",0,'R');
												$pdf->SetX(180);$pdf->Cell(35,5,"","LR",0,'R');
												$pdf->SetX(215);$pdf->Cell(35,5,"","LR",0,'R');
												$pdf->SetX(250);$pdf->Cell(30,5,"","LR",0,'C');
												$pdf->SetX(280);$pdf->Cell(30,5,"","LR",0,'R');
												$pdf->SetX(310);$pdf->Cell(35,5,"","LR",1,'C');
												
												$street	=	$global_func->Select_val($Filstar_conn,FDCRMS,"customer_address","StreetNumber","CUSTNO= '$key_custno'");
												$town	=	$global_func->Select_val($Filstar_conn,FDCRMS,"customer_address","TownCity","CUSTNO= '$key_custno'");
												$add	=	$street.','.$town;
												
												$a		=	$global_func->str_concat($add,45);
												foreach ($a as $b=>$c)
												{
													$z	=	count($c);
													$x	=	1;
													foreach ($c as $d)
													{
														$cnt++;
														if ($z != $x) 
														{
															$pdf->SetX(10);$pdf->Cell(100,5,$d,"LR",0,'L');
															$pdf->SetX(110);$pdf->Cell(35,5,"","LR",0,'C');
															$pdf->SetX(145);$pdf->Cell(35,5,"","LR",0,'R');
															$pdf->SetX(180);$pdf->Cell(35,5,"","LR",0,'R');
															$pdf->SetX(215);$pdf->Cell(35,5,"","LR",0,'R');
															$pdf->SetX(250);$pdf->Cell(30,5,"","LR",0,'C');
															$pdf->SetX(280);$pdf->Cell(30,5,"","LR",0,'R');
															$pdf->SetX(310);$pdf->Cell(35,5,"","LR",1,'C');
														}
														else 
														{
															$pdf->SetX(10);$pdf->Cell(100,5,$d,"LBR",0,'L');
															$pdf->SetX(110);$pdf->Cell(35,5,$val9['INVOICENO'],"LBR",0,'C');
															$pdf->SetX(145);$pdf->Cell(35,5,number_format($val9['INVOICEAMT'],2),"LBR",0,'R');
															$pdf->SetX(180);$pdf->Cell(35,5,$val9['SOFNO'],"LBR",0,'R');
															$pdf->SetX(215);$pdf->Cell(35,5,$picklist,"LBR",0,'R');
															$pdf->SetX(250);$pdf->Cell(30,5,$val9['PRODLINE'],"LBR",0,'C');
															$pdf->SetX(280);$pdf->Cell(30,5,$val9['CARTON'],"LBR",0,'R');
															$pdf->SetX(310);$pdf->Cell(35,5,$val9['REMARKS'],"LBR",1,'C');
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