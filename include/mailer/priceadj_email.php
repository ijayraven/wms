<?php	
session_start();

include($_SESSION['ROOT']."/common/session.php");
include($_SESSION['ROOT']."/config/get_sysoption.php");
include($_SESSION['ROOT']."/config/error_handler.php");
include("mailer/class.phpmailer.php");
include($_SESSION['ROOT']."/includes/fpdf/fpdf.php");
include($_SESSION['ROOT']."/{$_SESSION['priceadj_dbaccess']}/priceadjdbaccess.php");	

$pa = new PriceAdjDbaccess();

switch ($_GET['action']):
	case "do_post_pa":
						
				$txtTransNo			= trim($_GET['txtTransNo']);
				$selUser			= trim($_GET['selUser']);
				$txtPassword		= trim($_GET['txtPassword']);
				$txtRemarks			= trim($_GET['txtRemarks']);
				$error 				= 0;
				$datenow 			= date("Y-m-d H:i:s");

						
				$pa->qrySelectWhereArray("TRANSNO,START_DATE","PRICEADJ_HDR","WHERE TRANSNO='{$txtTransNo}'","0","1");
				$arrData = $pa->getArrResult();
					foreach ($arrData as $key => $val)
					{
						$startdate	= $val['START_DATE'];			
					}
					

					$pa->qryDataCount("*","PRICEADJ_HDR","WHERE START_DATE='{$startdate}' and STATUS='Posted'" );
					$cnt = $pa->getDataCount();
													
						if($cnt>0)
						{
								
							$pa->qrySelectWhereArray("*","PRICEADJ_DTL","WHERE TRANSNO='{$txtTransNo}'","0","1");
							$arrData = $pa->getArrResult();
								foreach ($arrData as $key => $val)
								{
									$itemno	= $val['SKUNO'];			
								}
							
								$pa->qryDataCount("*","PRICEADJ_DTL","WHERE SKUNO='{$itemno}' and STARTDATE='{$startdate}'" );
								$cnt = $pa->getDataCount();
																		
									if($cnt>0)
							
									{
										echo "$('#dialog_ok').dialog('open');";
										echo "$('#dialog_ok').html('YOU HAVE SAME ITEM NO for POSTING TODAY');";
										
									}
									else
									{
										$pa->qryUpdate("STATUS='Posted', POSTED_BY='{$_SESSION["login_username"]}', POSTED_DATE='{$datenow}',APPROVED_BY='{$selUser}',REMARKS='{$txtRemarks}'","PRICEADJ_HDR","TRANSNO='{$txtTransNo}'");
										$isSuccess = $pa->getIsSuccess();	
								
											if($isSuccess=='true')
											{
//												echo "$('#dialog_ok').dialog('open');";
//												echo "$('#dialog_ok').html('Trans No. {$txtTransNo} is now posted!');";	
												
												class PDF extends FPDF
												{
													function Header($txtTransNo)
													{
															
														$this->Image($_SESSION['ROOT'].'/images/fdc_ls_col.jpg',30,8,40);
														$this->SetFont('Arial','B',12);
														$this->SetXY(18,5);$this->Cell(180,45,"PRICE ADJUSTMENT",0,0,'C');
														$header .= (!empty($selType)) ? strtoupper($selType)."" : "";
																		
															if((!empty($txtDateFrom) and !empty($txtDateTo)))
															{
																$header = "";
																$header .= (!empty($txtDateFrom) and !empty($txtDateTo)) ? "".date("F j, Y",strtotime($txtDateFrom)). "  to  ".date("F j, Y",strtotime($txtDateTo)) : "";					 
																$this->SetXY(20,10);$this->Cell(180,15,"{$header}",0,0,'C');
															}
																$this->SetFont('Arial','B',9);
																$this->SetXY(46,40);$this->Cell(10,7,"Transaction No.:",0,0,'R');
																$this->SetXY(150,40);$this->Cell(10,7,"Effectivity Date:",0,1,'R');
																$this->SetXY(46,45);$this->Cell(10,7,"Description:",0,0,'R');
																$this->SetXY(150,45);$this->Cell(10,7,"Creation Date:",0,1,'R');
																$this->SetXY(46,50);$this->Cell(10,7,"Type:",0,0,'R');
																$this->SetXY(150,50);$this->Cell(10,7,"Status:",0,1,'R');
																$this->SetXY(46,55);$this->Cell(10,7,"Brand:",0,0,'R');
													}
													function footerBottom()
													{
														$dCurrDate  = 	date('F j, Y');
														$tCurrTime  = 	date('h:i A');
																	
														$this->SetFont('Arial','',8);
														$this->SetXY(10,240);$this->Cell(60,0,"Encoded By : ".$_SESSION["login_username"],0,0,'L');
														$this->SetXY(10,245);$this->Cell(60,0,"Printed By : ".$_SESSION["login_username"],0,0,'L');
														$this->SetXY(10,250);$this->Cell(60,0,"Date Printed : ".$dCurrDate." / ".$tCurrTime,0,0,'L');
														$this->SetXY(140,250);$this->Cell(60,0,"Page ".$this->PageNo()." of {nb}",0,0,'R');
													}	
													function collect($txtTransNo)
													{
														$nYHAxis = 55;
														$pa = new PriceAdjDbaccess();	
														$pa->qrySelectWhereArray("TRANSNO, DESCRIPTION, TYPE, BRAND, STATUS, START_DATE, CREATED_DATE","PRICEADJ_HDR","WHERE TRANSNO IN ({$code})","","");
														$data = $pa->getArrResult();
															if($data!='')
															{
															foreach ($data as $dataKey => $dataVal) 
															{
																$this->SetFont('Arial','',9);
																//$nYAxis += 7;
																$this->SetFont('Arial','',9);
																$this->SetXY(65,40);$this->Cell(10,7,$dataVal['TRANSNO'],0,0,'R');
																$this->SetXY(168,40);$this->Cell(10,7,$dataVal['START_DATE'],0,1,'R');
																$this->SetXY(55,45);$this->Cell(30,7,$dataVal['DESCRIPTION'],0,0,'L');
																$this->SetXY(168,45);$this->Cell(10,7,substr($dataVal['CREATED_DATE'],0,10),0,1,'R');
																$this->SetXY(55,50);$this->Cell(15,7,$dataVal['TYPE'],0,0,'L');
																$this->SetXY(160,50);$this->Cell(10,7,$dataVal['STATUS'],0,1,'L');
																$this->SetXY(55,$nYHAxis);$this->Cell(15,7,$dataVal['BRAND'],0,0,'L');
																					
																$this->SetFont('Arial','B',9);
																$this->SetXY(10,65);$this->Cell(10,7,"Line #",1,0,'C');	
																$this->SetXY(20,65);$this->Cell(15,7,"SKU No.",1,0,'C');		
																$this->SetXY(35,65);$this->Cell(85,7,"Description",1,0,'C');
																$this->SetXY(120,65);$this->Cell(15,7,"SRP",1,0,'C');		
																$this->SetXY(135,65);$this->Cell(15,7,"New SRP",1,0,'C');	
																$this->SetXY(150,65);$this->Cell(20,7,"Category",1,0,'C');	
																$this->SetXY(170,65);$this->Cell(20,7,"Sub Cat",1,0,'C');
																$this->SetXY(190,65);$this->Cell(20,7,"Class",1,0,'C');
																					
																$pa->qrySelectWhereArray("*","PRICEADJ_DTL","where TRANSNO = '{$dataVal['TRANSNO']}'",$offset,$limit);		
																$data = $pa->getArrResult();
																$ctr = 0;
																$counter=1;
																$nYAxis = 65;
																	foreach ($data as $dataKey => $dataVal) 
																	{
																		$pa->qrySelectWhereArray("*","ITEMMASTER","where ITEMNO = '{$dataVal['SKUNO']}'",$offset,$limit);		
																		$data = $pa->getArrResult();
																			foreach ($data as $dataKey => $val) 
																			{
																				$pa->qrySelectWhereArray("*","CATEGORY_NEW","where CATEGORY_ID = '{$val['CATEGORY']}'",$offset,$limit);		
																				$data = $pa->getArrResult();
																				foreach ($data as $dataKey => $cat) 
																			{
																				$category	=	$cat['CATEGORY_NAME'];
																			}
																		$pa->qrySelectWhereArray("*","SUB_CATEGORY_NEW","where SUB_CATEGORY_ID = '{$val['SUB_CATEGORY']}'",$offset,$limit);		
																		$data = $pa->getArrResult();
																			foreach ($data as $dataKey => $sub) 
																			{
																				$subcategory	=	$sub['SUB_CATEGORY_NAME'];
																			}
																				$this->SetFont('Arial','',8);
																				$nYAxis += 7;
																				$this->SetXY(10,$nYAxis);$this->Cell(10,7,$counter,1,0,'C');	
																				$this->SetXY(20,$nYAxis);$this->Cell(15,7,$dataVal['SKUNO'],1,0,'C');
																				$this->SetFont('Arial','',7);		
																				$this->SetXY(35,$nYAxis);$this->Cell(85,7,$dataVal['DESC'],1,0,'C');
																				$this->SetFont('Arial','',7);
																				$this->SetXY(120,$nYAxis);$this->Cell(15,7,$dataVal['OLD_PRICE'],1,0,'C');		
																				$this->SetXY(135,$nYAxis);$this->Cell(15,7,$dataVal['NEW_PRICE'],1,0,'C');	
																				$this->SetXY(150,$nYAxis);$this->Cell(20,7,$category,1,0,'C');	
																				$this->SetXY(170,$nYAxis);$this->Cell(20,7,$subcategory,1,0,'C');	
																				$this->SetXY(190,$nYAxis);$this->Cell(20,7,$val['CLASS'],1,0,'C');	
																				$counter++;	
																			}
																						
																						}
																					
																							if($nYAxis >= 100)
																							{
																								$this->AddPage();
																								$this->footerBottom();
																								$nYAxis = 40;
																	
																								
																							}
																					
																	
																					
																					}
																					
																					}
																					else
																					{
																						$nYAxis += 15;
																						$this->SetXY(20,$nYAxis);$this->Cell(200,15,'NO RECORD FOUND',1,0,'C');
																					}
																		
													}
												}
												
												$txtTransNo			= trim($_GET['txtTransNo']);
												$newprintcode = "";
												
												$arrCode	=	explode(',',$printcode);
												foreach ($arrCode as $arrKey => $arrVal)
												{
													if($newprintcode==""){
														$newprintcode = "'{$arrVal}'";
													}else{
														$newprintcode = $newprintcode.",'{$arrVal}'";
													}
												}
												$selType 	 	= $_GET['selType'];
												$txtDateFrom  	= $_GET['txtDateFrom'];
												$txtDateTo  	= $_GET['txtDateTo'];
												$today			= date("-Ymd");
												$txtEmail		= "uahornachos@filstar.com.ph";
												
												$name  = $_SERVER['DOCUMENT_ROOT'].'FDC_PMS/logs/pdf/priceadjustment'.$txtTransNo.'.pdf';
												$pdf = new PDF ('P','mm','Letter');
												$pdf->Open();
												$pdf->SetAutoPageBreak(true,0);
												$pdf->AddPage();
												$pdf->AliasNbPages();
												//$pdf->Header($txtDateFrom,$txtDateTo);
												$pdf->collect($txtTransNo);
												//$pdf->footerBottom();
												$pdf->Output($name,'F');
																						
												$sDataRS="Please download attached file. <br><br>Thank You!";
												$mail = new PHPMailer();
												$mail-> IsSMTP();
												$mail->Host = "192.168.250.252";
												$mail->SMTPAuth = false;
												$mail->Username = "";
												$mail->Password = "";
												$mail->From = "uahornachos@filstar.com.ph";
												$mail->FromName = "FDC PURCHASING DEPT";
												$mail->AddAddress("$txtEmail");	
											//	$mail->AddAddress("maasuncion@filstar.com.ph");	
												$mail->AddReplyTo("uahornachos@filstar.com.ph");
												$mail->IsHTML(true);
												$mail->AddAttachment($name);
												$mail->Subject = "PRICE ADJUSTMENT".$today."";
												$mail->Body = $sDataRS;
												if(!$mail->Send())
												{
													$Confirm = "Error in sending! Message has not been sent";
													echo "Error in sending! Message has not been sent";
													unlink($name);
												}
												else
												{
													$Confirm = "Message has been sent";
													
													echo "Message has been sent to ".$txtEmail;
													unlink($name);
												}
											}
													
									}
						}
						else
						{
							$pa->qryUpdate("STATUS='Posted', POSTED_BY='{$_SESSION["login_username"]}', POSTED_DATE='{$datenow}',APPROVED_BY='{$selUser}',REMARKS='{$txtRemarks}'","PRICEADJ_HDR","TRANSNO='{$txtTransNo}'");
							$isSuccess = $pa->getIsSuccess();	
								if($isSuccess=='true')
								{
//									echo "$('#dialog_ok').dialog('open');";
//									echo "$('#dialog_ok').html('Trans No. {$txtTransNo} is now posted!');";	

									class PDF extends FPDF
									{
										function Header($txtTransNo)
										{
											$this->Image($_SESSION['ROOT'].'/images/fdc_ls_col.jpg',30,8,40);
											$this->SetFont('Arial','B',12);
											$this->SetXY(18,5);$this->Cell(180,45,"PRICE ADJUSTMENT",0,0,'C');
											$header .= (!empty($selType)) ? strtoupper($selType)."" : "";
						
											if((!empty($txtDateFrom) and !empty($txtDateTo)))
											{
												$header = "";
												$header .= (!empty($txtDateFrom) and !empty($txtDateTo)) ? "".date("F j, Y",strtotime($txtDateFrom)). "  to  ".date("F j, Y",strtotime($txtDateTo)) : "";					 
												$this->SetXY(20,10);$this->Cell(180,15,"{$header}",0,0,'C');
											}
												$this->SetFont('Arial','B',9);
												$this->SetXY(46,40);$this->Cell(10,7,"Transaction No.:",0,0,'R');
												$this->SetXY(150,40);$this->Cell(10,7,"Effectivity Date:",0,1,'R');
												$this->SetXY(46,45);$this->Cell(10,7,"Description:",0,0,'R');
												$this->SetXY(150,45);$this->Cell(10,7,"Creation Date:",0,1,'R');
												$this->SetXY(46,50);$this->Cell(10,7,"Type:",0,0,'R');
												$this->SetXY(150,50);$this->Cell(10,7,"Status:",0,1,'R');
												$this->SetXY(46,55);$this->Cell(10,7,"Brand:",0,0,'R');
										}	
			
										function footerBottom()
										{
											$dCurrDate  = 	date('F j, Y');
											$tCurrTime  = 	date('h:i A');
														
											$this->SetFont('Arial','',8);
											$this->SetXY(10,240);$this->Cell(60,0,"Encoded By : ".$_SESSION["login_username"],0,0,'L');
											$this->SetXY(10,245);$this->Cell(60,0,"Printed By : ".$_SESSION["login_username"],0,0,'L');
											$this->SetXY(10,250);$this->Cell(60,0,"Date Printed : ".$dCurrDate." / ".$tCurrTime,0,0,'L');
											$this->SetXY(140,250);$this->Cell(60,0,"Page ".$this->PageNo()." of {nb}",0,0,'R');
										}
				
										function collect($txtTransNo)
										{
											$nYHAxis = 55;
											$pa = new PriceAdjDbaccess();	
											$pa->qrySelectWhereArray("TRANSNO, DESCRIPTION, TYPE, BRAND, STATUS, START_DATE, CREATED_DATE","PRICEADJ_HDR","WHERE TRANSNO IN ({$txtTransNo})","","");
											$data = $pa->getArrResult();
												if($data!='')
												{
													foreach ($data as $dataKey => $dataVal)
													{
														$this->SetFont('Arial','',9);
														//$nYAxis += 7;
														$this->SetFont('Arial','',9);
														$this->SetXY(65,40);$this->Cell(10,7,$dataVal['TRANSNO'],0,0,'R');
														$this->SetXY(168,40);$this->Cell(10,7,$dataVal['START_DATE'],0,1,'R');
														$this->SetXY(55,45);$this->Cell(30,7,$dataVal['DESCRIPTION'],0,0,'L');
														$this->SetXY(168,45);$this->Cell(10,7,substr($dataVal['CREATED_DATE'],0,10),0,1,'R');
														$this->SetXY(55,50);$this->Cell(15,7,$dataVal['TYPE'],0,0,'L');
														$this->SetXY(160,50);$this->Cell(10,7,$dataVal['STATUS'],0,1,'L');
														$this->SetXY(55,$nYHAxis);$this->Cell(15,7,$dataVal['BRAND'],0,0,'L');
									
														$this->SetFont('Arial','B',9);
														$this->SetXY(10,65);$this->Cell(10,7,"Line #",1,0,'C');	
														$this->SetXY(20,65);$this->Cell(15,7,"SKU No.",1,0,'C');		
														$this->SetXY(35,65);$this->Cell(85,7,"Description",1,0,'C');
														$this->SetXY(120,65);$this->Cell(15,7,"SRP",1,0,'C');		
														$this->SetXY(135,65);$this->Cell(15,7,"New SRP",1,0,'C');	
														$this->SetXY(150,65);$this->Cell(20,7,"Category",1,0,'C');	
														$this->SetXY(170,65);$this->Cell(20,7,"Sub Cat",1,0,'C');
														$this->SetXY(190,65);$this->Cell(20,7,"Class",1,0,'C');
																			
														$pa->qrySelectWhereArray("*","PRICEADJ_DTL","where TRANSNO = '{$dataVal['TRANSNO']}'",$offset,$limit);		
														$data = $pa->getArrResult();
														$ctr = 0;
														$counter=1;
														$nYAxis = 65;
															foreach ($data as $dataKey => $dataVal) 
															{
																$pa->qrySelectWhereArray("*","ITEMMASTER","where ITEMNO = '{$dataVal['SKUNO']}'",$offset,$limit);		
																$data = $pa->getArrResult();
																	foreach ($data as $dataKey => $val) 
																	{
																		$pa->qrySelectWhereArray("*","CATEGORY_NEW","where CATEGORY_ID = '{$val['CATEGORY']}'",$offset,$limit);		
																		$data = $pa->getArrResult();
																			foreach ($data as $dataKey => $cat) 
																			{
																				$category	=	$cat['CATEGORY_NAME'];
																			}
																		$pa->qrySelectWhereArray("*","SUB_CATEGORY_NEW","where SUB_CATEGORY_ID = '{$val['SUB_CATEGORY']}'",$offset,$limit);		
																		$data = $pa->getArrResult();
																			foreach ($data as $dataKey => $sub) 
																			{
																				$subcategory	=	$sub['SUB_CATEGORY_NAME'];
																			}
																				$this->SetFont('Arial','',8);
																				$nYAxis += 7;
																				$this->SetXY(10,$nYAxis);$this->Cell(10,7,$counter,1,0,'C');	
																				$this->SetXY(20,$nYAxis);$this->Cell(15,7,$dataVal['SKUNO'],1,0,'C');
																				$this->SetFont('Arial','',7);		
																				$this->SetXY(35,$nYAxis);$this->Cell(85,7,$dataVal['DESC'],1,0,'C');
																				$this->SetFont('Arial','',7);
																				$this->SetXY(120,$nYAxis);$this->Cell(15,7,$dataVal['OLD_PRICE'],1,0,'C');		
																				$this->SetXY(135,$nYAxis);$this->Cell(15,7,$dataVal['NEW_PRICE'],1,0,'C');	
																				$this->SetXY(150,$nYAxis);$this->Cell(20,7,$category,1,0,'C');	
																				$this->SetXY(170,$nYAxis);$this->Cell(20,7,$subcategory,1,0,'C');	
																				$this->SetXY(190,$nYAxis);$this->Cell(20,7,$val['CLASS'],1,0,'C');	
																				$counter++;	
																	}
										
															}
									
														if($nYAxis >= 100)
														{
															$this->AddPage();
															$this->footerBottom();
															$nYAxis = 40;
														}
													}
									
												}
												else
												{
													$nYAxis += 15;
													$this->SetXY(20,$nYAxis);$this->Cell(200,15,'NO RECORD FOUND',1,0,'C');
												}
										}
									
									}
								
										$txtTransNo			= trim($_GET['txtTransNo']);
										$newprintcode = "";
										
										$arrCode	=	explode(',',$printcode);
										foreach ($arrCode as $arrKey => $arrVal) 
										{
											if($newprintcode==""){
												$newprintcode = "'{$arrVal}'";
											}else{
												$newprintcode = $newprintcode.",'{$arrVal}'";
											}
										}
										$selType 	 	= $_GET['selType'];
										$txtDateFrom  	= $_GET['txtDateFrom'];
										$txtDateTo  	= $_GET['txtDateTo'];
										$today			= date("-Ymd");
										$txtEmail		= "uahornachos@filstar.com.ph";
										
										$name  = $_SERVER['DOCUMENT_ROOT'].'FDC_PMS/logs/pdf/priceadjustment'.$txtTransNo.'.pdf';
										$pdf = new PDF ('P','mm','Letter');
										$pdf->Open();
										$pdf->SetAutoPageBreak(true,0);
										$pdf->AddPage();
										$pdf->AliasNbPages();
										//$pdf->Header($txtDateFrom,$txtDateTo);
										$pdf->collect($txtTransNo);
										//$pdf->footerBottom();
										$pdf->Output($name,'F');
										
										$sDataRS="Please download attached file. <br><br>Thank You!";
										$mail = new PHPMailer();
										$mail-> IsSMTP();
										$mail->Host = "192.168.250.252";
										$mail->SMTPAuth = false;
										$mail->Username = "";
										$mail->Password = "";
										$mail->From = "uahornachos@filstar.com.ph";
										$mail->FromName = "FDC PURCHASING DEPT";
										$mail->AddAddress("$txtEmail");	
									//	$mail->AddAddress("maasuncion@filstar.com.ph");	
										$mail->AddReplyTo("uahornachos@filstar.com.ph");
										$mail->IsHTML(true);
										$mail->AddAttachment($name);
										$mail->Subject = "PRICE ADJUSTMENT".$today."";
										$mail->Body = $sDataRS;
										if(!$mail->Send())
										{
											$Confirm = "Error in sending! Message has not been sent";
											echo "Error in sending! Message has not been sent";
											unlink($name);
										}
										else
										{
											$Confirm = "Message has been sent";
											
											echo "Message has been sent to ".$txtEmail;
											unlink($name);
										}	
								}
//								$where = "WHERE TRANSNO='{$txtTransNo}'";
//								$where .= "order by TRANSNO desc ";
//								viewDisplayDetail($where);
//								
							}
//					$where = "WHERE TRANSNO='{$txtTransNo}'";
//					$where .= "order by TRANSNO desc ";
//					viewDisplayDetail($where);
							
			exit;	

								

############################################ SCHEME TEMPLATE #####################################################
##################################################################################################################
include($_SESSION['DOCUMENT_FOLDER'].'/'.$_SESSION['priceadj_web'].'/priceadj.htm');
##################################################################################################################
##################################################################################################################

?>