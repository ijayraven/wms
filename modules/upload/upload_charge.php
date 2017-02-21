<?php
	include('../../include/config/consolidator.php');
	set_time_limit(0);
	if (isset($_POST['btnsubmit'])) 
	{
		$aFile		=	$_FILES['uploadfile'];
		$type		=	$aFile['type'];
		$tmpname	=	$aFile['tmp_name'];
		$name		=	$aFile['name'];
		
		if ($type == 'text/csv') 
		{
			$aData		=	array();
			$n 			= 	'1'; //first n lines that are not wanted 
			$fn 		= 	fopen($tmpname, "r");
			$file 		= 	fread( $fn, filesize($tmpname));
			$aOutput	=	explode("\n",trim($file));
			
			for( $i=0; $i<$n  ; $i++) 
			{
				$HDR		=	trim($aOutput[$i]);
				$out_HDR	=	explode(",",trim($HDR));
				if (trim($out_HDR[$i]) == 'HD') 
				{
					$do_process	=	'YES';
				}
				else 
				{
					$do_process	=	'NO';
				}
			}
			if ($do_process == 'YES') 
			{
				foreach ($aOutput as $akey)
				{
					$explode_val	=	explode(",",$akey);
					$opt			=	trim($explode_val[0]);
					if ($opt == "HD") 
					{
						$BranchCode	=	trim($explode_val[1]);	//BranchCode
						$PO			=	trim($explode_val[2]);	//PO#
						$POdate		=	trim($explode_val[3]);	//PO DATE
						$POdeadline	=	trim($explode_val[4]);	//PO DEADLINE
						$POamount	=	trim($explode_val[5]);	//PO DEADLINE
					}
					else if($opt == "DT") 
					{
						$Sku		=	trim($explode_val[2]);		//SKU
						$Qty		=	trim($explode_val[4]);		//QTY
						$price		=	trim($explode_val[5]);		//UNIT PRICE
						$Grossamount=	trim($explode_val[6]);		//GROSS AMOUNT
						$BUOM		=	trim($explode_val[11]);		//BUOM
						$SRP		=	trim($explode_val[12]);		//SRP
						$DISC		=	trim($explode_val[13]);		//DIS
						
						//->Select customer Code into custmast table
						$Custcode	=	$Global_funcs->Sel_val($db2,SERVER_LOCATION_DATABASE,"custmast","CustNo","CustomerBranchCode = '{$BranchCode}' ");
						//<-END of Select customer Code into custmast table
						
						//->Select Alpha of the item into equi table
						$alpha		=	$Global_funcs->Sel_val($db2,SERVER_LOCATION_DATABASE,"equi","Alpha","Equi = '".substr($Sku,2,3)."' ");
						//<-END of Select Alpha of the item into equi table
						
						//->Select Tag of the item into equi table
						$Tag		=	ucfirst(strtolower($Global_funcs->Sel_val($db2,SERVER_LOCATION_DATABASE,"equi","Tag","Equi = '".substr($Sku,2,3)."' ")));
						//<-END of Select Tag of the item into equi table
						
						
						$subSku		=	substr($Sku,2);
						
						
						//->Insert all information in $aData array format
						$aData[$PO][$Custcode][$POdate][$POdeadline][$POamount][$alpha][$Tag][$subSku]['QTY']			+=	$Qty;
						$aData[$PO][$Custcode][$POdate][$POdeadline][$POamount][$alpha][$Tag][$subSku]['PRICE']		=	$price;
						$aData[$PO][$Custcode][$POdate][$POdeadline][$POamount][$alpha][$Tag][$subSku]['GROSS']		=	$Grossamount;
						$aData[$PO][$Custcode][$POdate][$POdeadline][$POamount][$alpha][$Tag][$subSku]['BUOM']			=	$BUOM;
						$aData[$PO][$Custcode][$POdate][$POdeadline][$POamount][$alpha][$Tag][$subSku]['SRP']			=	$SRP;
						$aData[$PO][$Custcode][$POdate][$POdeadline][$POamount][$alpha][$Tag][$subSku]['DISC']			=	$DISC;
						//<-END of Insert all information in $aData array format
					}
				}
				//print_r($aData);
				//exit();
				try {
					if (TEST_MODE == 'N') 
					{
						$start		=	$db2;
						$Conplete	=	$db2;
					}
					else 
					{
						$start		=	$db1;
						$Conplete	=	$db1;
					}
					$start->StartTrans();
					$errcnt			=	0;
					$pickdate		=	date('Y-m-d');
					$picktime		=	date('H:i:s');
					$msg			=	'';
					$msg_duplicate	=	'';
					$ms_noalpha		=	'';
					foreach ($aData as $key_PO=>$aCustcode)
					{
						if (TEST_MODE == 'N') 
						{
							$sel 		=	"SELECT COUNT(*) AS CNT FROM orderheader where RefNo = '{$key_PO}' ";
							$rssel		=	$db2->Execute($sel);	
						}
						else 
						{
							$sel 		=	"SELECT COUNT(*) AS CNT FROM FDCRMSlive.orderheader where RefNo = '{$key_PO}' ";
							$rssel		=	$db1->Execute($sel);	
						}
						if ($rssel == false) 
						{
							if (TEST_MODE == 'N') 
							{
								throw new Exception($db2->ErrorMsg());
							}
							else 
							{
								throw new Exception($db1->ErrorMsg());
							}
						}
						$isExist	=	$rssel->fields['CNT'];
						if ($isExist > 0) 
						{
							$msg_duplicate .= $key_PO."<br>";
						}
						else 
						{
							foreach ($aCustcode as $key_Custcode=>$aPOdate)
							{
								foreach ($aPOdate as $key_POdate=>$aPODline)
								{
									foreach ($aPODline as $key_PODline=>$aPOamount)
									{
										foreach ($aPOamount as $key_POamount=>$aAlpha)
										{
											foreach ($aAlpha as $key_Alpha=>$aTag)
											{
												foreach ($aTag as $key_Tag=>$aSku)
												{
													if (!empty($key_Alpha)) 
													{
														$sofno	=	$Global_funcs->Create_sofno($db1,$key_Alpha);
														if (TEST_MODE == 'N') 
														{
															/*$PLNO	=	$Global_funcs->Generate_PL_No($db2,TEST_MODE);*/
															$SEL	=	"SELECT trannum FROM transeq WHERE trantyp = 'PICKLIST' ";
															$rsSel	=	$db2->Execute($SEL);
														}
														else 
														{
															/*$PLNO	=	$Global_funcs->Generate_PL_No($db1,TEST_MODE);*/
															$SEL	=	"SELECT trannum FROM FDCRMSlive.transeq WHERE trantyp = 'PICKLIST' ";
															$rsSel	=	$db1->Execute($SEL);
														}
														if ($rsSel == false) 
														{
															if (TEST_MODE == 'N') 
															{
																throw new Exception($db2->ErrorMsg());
															}
															else 
															{
																throw new Exception($db1->ErrorMsg());
															}
														}
														if (TEST_MODE == 'N') 
												    	{
												    		$UPDATE	=	"UPDATE `transeq` SET `trannum` = (`trannum` + 1)   WHERE trantyp = 'PICKLIST'";
												    		$db2->Execute($UPDATE);
												    	}
												    	else 
												    	{
												    		$UPDATE	=	"UPDATE FDCRMSlive.transeq SET `trannum` = (`trannum` + 1)   WHERE trantyp = 'PICKLIST'";
												    		$db1->Execute($UPDATE);
												    	}
														$PLNO		=	$rsSel->fields['trannum'];
														$orderdate	=	date('Y-m-d');
														/*START QUERY FOR ORDERHEADER */
														if (TEST_MODE == 'N') 
														{
															$insert_hdr		 =	" INSERT INTO orderheader ";
															$insert_hdr		.=	" (`OrderNo`,`RefNo`,`RecordType`,`Season`,`OrderStatus`,`PickListNo`,`PickListDate`,`PickListTime`,`CustNo`,`OrderDate`,`OrderType`,`HoldStatus`) ";
															$insert_hdr		.=	" VALUES ";
															$insert_hdr		.=	" ('{$sofno}','{$key_PO}','".Recordtype."','{$key_Tag}','For Picking','{$PLNO}','{$pickdate}','{$picktime}','{$key_Custcode}','{$orderdate}','".Ordertype1."','N')";
															$rsinsert_hdr	 =	$db2->Execute($insert_hdr);
														}
														else 
														{
															$insert_hdr		 =	" INSERT INTO FDCRMSlive.orderheader ";
															$insert_hdr		.=	" (`OrderNo`,`RefNo`,`RecordType`,`Season`,`OrderStatus`,`PickListNo`,`PickListDate`,`PickListTime`,`CustNo`,`OrderDate`,`OrderType`,`HoldStatus`) ";
															$insert_hdr		.=	" VALUES ";
															$insert_hdr		.=	" ('{$sofno}','{$key_PO}','".Recordtype."','{$key_Tag}','For Picking','{$PLNO}','{$pickdate}','{$picktime}','{$key_Custcode}','{$orderdate}','".Ordertype1."','N')";
															$rsinsert_hdr	 =	$db1->Execute($insert_hdr);
														}
														if ($rsinsert_hdr == false) 
														{
															if (TEST_MODE == 'N') 
															{
																throw new Exception($db2->ErrorMsg());
															}
															else 
															{
																throw new Exception($db1->ErrorMsg());
															}
														}
														/*END OF START QUERY FOR ORDER HEADER */
														
														foreach ($aSku as $key_Sku=>$aVal)
														{
															$aAmount		=	$Global_funcs->FetchAmount($aVal['DISC'],$aVal['PRICE'],$aVal['QTY']);
															$nGross			=	$aAmount['GrossAmount'];
															$nDiscAmount	=	$aAmount['DiscAmount'];
															$nNetAmount 	=	$aAmount['NetAmount'];
															
															$nInvoiceAmount[$sofno]+=$nNetAmount; 
															$nOrderAmount[$sofno]+=$nGross;
															
															/*START QUERY FOR ORDERDETAILS*/
															if (TEST_MODE == 'N') 
															{
																$insert_dtl 	 = " INSERT INTO orderdetail ";
																$insert_dtl 	.= " (`OrderNo`,`RecordType`,`Item`,`OrderQty`,`ReleaseQty`,`UnitCost`,`UnitPrice`,`Discount`,`DiscAmount`,`GrossAmount`,`NetAmount`,`CustNo`)";
																$insert_dtl 	.= " VALUES ";
																$insert_dtl 	.= " ('{$sofno}','".Recordtype."','{$key_Sku}','{$aVal['QTY']}','{$aVal['QTY']}','{$aVal['PRICE']}','{$aVal['SRP']}','{$aVal['DISC']}','{$nDiscAmount}','{$nGross}','{$nNetAmount}','{$key_Custcode}')";
																$rsinsert_dtl	 =	$db2->Execute($insert_dtl);
															}
															else 
															{
																$insert_dtl 	 = " INSERT INTO FDCRMSlive.orderdetail ";
																$insert_dtl 	.= " (`OrderNo`,`RecordType`,`Item`,`OrderQty`,`ReleaseQty`,`UnitCost`,`UnitPrice`,`Discount`,`DiscAmount`,`GrossAmount`,`NetAmount`,`CustNo`)";
																$insert_dtl 	.= " VALUES ";
																$insert_dtl 	.= " ('{$sofno}','".Recordtype."','{$key_Sku}','{$aVal['QTY']}','{$aVal['QTY']}','{$aVal['PRICE']}','{$aVal['SRP']}','{$aVal['DISC']}','{$nDiscAmount}','{$nGross}','{$nNetAmount}','{$key_Custcode}')";
																$rsinsert_dtl	 =	$db1->Execute($insert_dtl);
															}
															if ($rsinsert_dtl == false) 
															{
																if (TEST_MODE == 'N') 
																{
																	throw new Exception($db2->ErrorMsg());
																}
																else 
																{
																	throw new Exception($db1->ErrorMsg());
																}
															}
															/*END OF START QUERY FOR ORDERDETAILS*/
															
															/*START QUERY FOR ITEMBAL*/
															if (TEST_MODE == 'N') 
															{
																$update_itembal		=	"UPDATE itembal SET `alcqty` = (`alcqty`+ '{$aVal['QTY']}') where itmnbr = '{$key_Sku}' AND `house` = 'FDC'";
																$rsupdate_itembal	=	$db2->Execute($update_itembal);
															}
															else 
															{
																$update_itembal		=	"UPDATE FDCRMSlive.itembal SET `alcqty` = (`alcqty`+ '{$aVal['QTY']}') where itmnbr = '{$key_Sku}' AND `house` = 'FDC'";
																$rsupdate_itembal	=	$db1->Execute($update_itembal);
															}
															/*START QUERY ITEMBAL*/
															if ($rsupdate_itembal == false) 
															{
																if (TEST_MODE == 'N') 
																{
																	throw new Exception($db2->ErrorMsg());
																}
																else 
																{
																	throw new Exception($db1->ErrorMsg());
																}
															}
														}
														
														if (TEST_MODE == 'N') 
														{
															$update 	=	"UPDATE orderheader SET `InvoiceAmount` = '{$nInvoiceAmount[$sofno]}', `OrderAmount` = '{$nOrderAmount[$sofno]}' WHERE `OrderNo` = '{$sofno}'";
															$rsupdate	=	$db2->Execute($update);
														}
														else 
														{
															$update 	=	"UPDATE FDCRMSlive.orderheader SET `InvoiceAmount` = '{$nInvoiceAmount[$sofno]}', `OrderAmount` = '{$nOrderAmount[$sofno]}' WHERE `OrderNo` = '{$sofno}'";
															$rsupdate	=	$db1->Execute($update);
														}
														if ($rsupdate == false) 
														{
															if (TEST_MODE == 'N') 
															{
																throw new Exception($db2->ErrorMsg());
															}
															else 
															{
																throw new Exception($db1->ErrorMsg());
															}
														}
														
														/*START QUERY FOR CUSTMAST */
														if (TEST_MODE == 'N') 
														{
															$update_bal		=	" UPDATE `custmast` SET `CurrentBalance` = (`CurrentBalance` + '{$nOrderAmount[$sofno]}') WHERE `CustNo` = '{$key_Custcode}'  ";
															$rsupdate_bal	=	$db2->Execute($update_bal);
														}
														else 
														{
															$update_bal	=	" UPDATE FDCRMSlive.custmast SET `CurrentBalance` = (`CurrentBalance` + '{$nOrderAmount[$sofno]}') WHERE `CustNo` = '{$key_Custcode}'  ";
															$rsupdate_bal	=	$db1->Execute($update_bal);
														}
														if ($rsupdate_bal == false) 
														{
															if (TEST_MODE == 'N') 
															{
																throw new Exception($db2->ErrorMsg());
															}
															else 
															{
																throw new Exception($db1->ErrorMsg());
															}
														}
														/*END OF START QUERY FOR CUSTMAST */
														
														
														$success	=	"INSERT INTO FDC_WMS.UPLOADED(`PONO`,`SOFNO`,`PLNO`,`SOFDATE`)VALUES('{$key_PO}','{$sofno}','{$PLNO}',sysdate())";
														$rssuccess	=	$db1->Execute($success);
														if ($rssuccess == false) 
														{
															throw new Exception($db1->ErrorMsg());
														}
													}
													else 
													{
														$insert_alpha	=	"INSERT INTO FDC_WMS.NOALPHA(`PONO`,`UPLOADDATE`)VALUES('{$key_PO}',sysdate())";
														$rsinsert_alpha	=	$db1->Execute($insert_alpha);
														if ($rsinsert_alpha == false) 
														{
															throw new Exception($db1->ErrorMsg());
														}
														$msg_noalpha	.= $key_PO."<br>";
													}
												}
											}
										}
									}
								}
							}
						}
					}
					if(!empty($msg_duplicate) && !empty($msg_noalpha))  
					{
						$msg .= " LIST OF PURCHASE ORDER. WITHOUT ALPHA SUFFIX "."<br>".$msg_noalpha;
						$msg .= " LIST OF P.O. THAT ARE NOT SUCCESSFULLY UPLOADED (DUPLICATE ENTRY) "."<br>".$msg_duplicate."<br>";
					}
					elseif (!empty($msg_duplicate)) 
					{
						$msg .= " LIST OF P.O. THAT ARE NOT SUCCESSFULLY UPLOADED (DUPLICATE ENTRY) "."<br>".$msg_duplicate;
					}
					elseif (!empty($msg_noalpha))
					{
						$msg .= " LIST OF PURCHASE ORDER. WITHOUT ALPHA SUFFIX "."<br>".$msg_noalpha;
					}
					if(empty($msg_duplicate) && empty($msg_noalpha))
					{
						$msg .= " FILE SUCCESSFULLY UPLOADED";
					}
					$Conplete->CompleteTrans();
					echo "<script>location='index.php?msg=$msg'</script>";
				}
				catch (Exception $e)
				{
					echo $err =	$e->__toString();
					$Conplete->CompleteTrans();
				}
			}
		}
		else 
		{
			echo $msg = "INVALID FILE.";
			echo "<script>location='index.php?msg=$msg'</script>";
		}
	}
?>