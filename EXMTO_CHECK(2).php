<style>
* {
	font-size:12px;
}
</style>

<?php 
include($_SERVER['DOCUMENT_ROOT']."/public_php/adodb/adodb.inc.php");
include($_SERVER['DOCUMENT_ROOT']."/public_php/class_db.php");
include($_SERVER['DOCUMENT_ROOT']."/public_js/jsUI.php");
	$conn_255_10	=	newADOConnection("mysqlt");
	$RSconn_255_10	=	$conn_255_10->Connect("192.168.255.10","root","");
	if($RSconn_255_10 == false)
	{
		echo "Unable to connect to server."; exit();
	}
	else 
	{
//		$GETEXITEMS		=	"SELECT `ITEMNO` FROM WMS_LOOKUP.MTO_EX_ITEMS_DTLS WHERE `CANCELLED` != 'Y' AND ITEMNO IN ('034105')";
		$GETEXITEMS		=	"SELECT `ITEMNO` FROM WMS_LOOKUP.MTO_EX_ITEMS_DTLS WHERE `CANCELLED` != 'Y' AND ITEMNO IN (
'034105',
'034155',
'056045'
		)";

		$RSGETEXITEMS	=	$conn_255_10->Execute($GETEXITEMS);
		if($RSGETEXITEMS == false)
		{
			echo $conn_255_10->ErrprMsg()."::".__LINE__; exit();
		}
		else 
		{
			echo "<h1>Exclusive Items</h1>";
//			echo "<ol>";
			
			while(!$RSGETEXITEMS->EOF)
			{
				$SUMMEDQTY	=	0;
				echo "<table border='1'>
						<tr align='center'>
							<TD>NO.</TD>
							<TD>MPOS NO</TD>
							<TD>ITEM NO</TD>
							<TD>SCANNED QTY</TD>
							<TD>POSTED DATE</TD>
							<TD>ITEM MTO STATUS</TD>
							<TD>SUMMED QTY</TD>
							<TD>REGULAR MTO NO.</TD>
							<TD>REGULAR MTO POSTED DATE</TD>
							<TD>REGULAR MTO QTY</TD>
							<TD>EXCLUSIVE MTO NO.</TD>
							<TD>EXCLUSIVE MTO POSTED DATE</TD>
							<TD>EXCLUSIVE MTO QTY</TD>
						</tr>";
				$POSTEDRFOM	=	"2016-05-01 00:00:00";
				$POSTEDTO	=	"";
				$item_display = "";
				$ITEMNO			=	$RSGETEXITEMS->fields["ITEMNO"];
				$EXITEMS_WITH_REGMTO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTNDTL AS D LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.MTONO = D.MTONO","SUM(QTY)","SKUNO= '{$ITEMNO}' AND (H.STATUS = 'POSTED' OR H.STATUS = 'TRANSMITTED' OR H.STATUS = 'PRINTED')");
				if($EXITEMS_WITH_REGMTO != 0)
				{
					$EXITEMS_WITH_EXMTO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCDTL AS D LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO","SUM(D.QTY)","D.SKUNO= '{$ITEMNO}' AND (H.STATUS = 'POSTED' OR H.STATUS = 'TRANSMITTED' OR H.STATUS = 'PRINTED')");
					if($EXITEMS_WITH_EXMTO != 0)
					{
						$item_display	=	"Regular MTO created with Exclusive MTO created.";	
					}
					else 
					{
						$item_display	=	"Regular MTO created.";	
					}
				}
				else 
				{
					$EXITEMS_WITH_EXMTO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCDTL AS D LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO","SUM(D.QTY)","D.SKUNO= '{$ITEMNO}' AND (H.STATUS = 'POSTED' OR H.STATUS = 'TRANSMITTED' OR H.STATUS = 'PRINTED')");
					if($EXITEMS_WITH_EXMTO != 0)
					{
						$item_display	=	"Exclusive MTO created.";
					}
				}
				if($item_display != "")
				{
					$GETSCANNED	=	"SELECT SCANDATA_DTL.`MPOSNO`, SCANDATA_DTL.`SKUNO`, SCANDATA_DTL.`SCANNEDQTY`, SCANDATA_DTL.`DEFECTIVEQTY`, SCANDATA_DTL.`IB_QTY` 
									 FROM WMS_NEW.SCANDATA_DTL 
									 LEFT JOIN WMS_NEW.SCANDATA_HDR ON SCANDATA_HDR.MPOSNO = SCANDATA_DTL.MPOSNO
									 WHERE `SKUNO` = '$ITEMNO' AND ITEMSTATUS != 'P'
									 ORDER BY SCANDATA_HDR.POSTEDDATE,SCANDATA_HDR.POSTEDTIME";
					$RSGETSCANNED	=	$conn_255_10->Execute($GETSCANNED);
					if($RSGETSCANNED == false)
					{
						echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
					}
					else 
					{
						$rowcount	=	$RSGETSCANNED->RecordCount();
						$cnt		=	0;	
						while (!$RSGETSCANNED->EOF) {
							$cnt++;
							$MPOSNO			=	$RSGETSCANNED->fields["MPOSNO"];
							$SKUNO			=	$RSGETSCANNED->fields["SKUNO"];
							$DEFECTIVEQTY	=	$RSGETSCANNED->fields["DEFECTIVEQTY"];
							$IB_QTY			=	$RSGETSCANNED->fields["IB_QTY"];
							
							$POSTEDDT	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_HDR","CONCAT(POSTEDDATE,' ',POSTEDTIME)","MPOSNO = '$MPOSNO'");
							$POSTEDTO	=	$POSTEDDT;
							
							$REGMTONO	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT D.MTONO FROM WMS_NEW.MTO_RTNHDR AS H 
																				 LEFT JOIN WMS_NEW.MTO_RTNDTL AS D ON D.MTONO = H.MTONO
																				 WHERE CONCAT(EDITDATE,' ',EDITTIME) BETWEEN '$POSTEDRFOM' AND '$POSTEDTO' AND D.SKUNO = '$SKUNO'
																				 ORDER BY H.EDITDATE DESC,H.EDITTIME DESC",
																				 "MTONO");
							$REGMTOQTY	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT SUM(D.QTY) AS QTY FROM WMS_NEW.MTO_RTNHDR AS H 
																				 LEFT JOIN WMS_NEW.MTO_RTNDTL AS D ON D.MTONO = H.MTONO
																				 WHERE CONCAT(EDITDATE,' ',EDITTIME) BETWEEN '$POSTEDRFOM' AND '$POSTEDTO' AND D.SKUNO = '$SKUNO'
																				 ORDER BY H.EDITDATE DESC,H.EDITTIME DESC",
																				 "QTY");
																				 
							$REGPOSTDATE=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTNHDR","CONCAT(EDITDATE,' ',EDITTIME)","MTONO ='$REGMTONO'");
							
							if($REGPOSTDATE == "0000-00-00 00:00:00")
							{
								$REGMTONO	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT D.MTONO FROM WMS_NEW.MTO_RTNHDR AS H 
																					 LEFT JOIN WMS_NEW.MTO_RTNDTL AS D ON D.MTONO = H.MTONO
																					 WHERE CONCAT(ADDDATE,' ',EDITTIME) BETWEEN '$POSTEDRFOM' AND '$POSTEDTO' AND D.SKUNO = '$SKUNO'
																					 ORDER BY H.ADDDATE DESC,H.ADDTIME DESC",
																					 "MTONO");
																					 
								$REGMTOQTY	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT SUM(D.QTY) AS QTY FROM WMS_NEW.MTO_RTNHDR AS H 
																					 LEFT JOIN WMS_NEW.MTO_RTNDTL AS D ON D.MTONO = H.MTONO
																					 WHERE CONCAT(EDITDATE,' ',EDITTIME) BETWEEN '$POSTEDRFOM' AND '$POSTEDTO' AND D.SKUNO = '$SKUNO'
																					 ORDER BY H.ADDDATE DESC,H.ADDTIME DESC",
																					 "QTY");
								
								$REGPOSTDATE=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTNHDR","CONCAT(ADDDATE,' ',ADDTIME)","MTONO ='$REGMTONO' ORDER ");
								
							}
							
							$EXMTONO	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT D.MTONO FROM WMS_NEW.MTO_RTN_EXCHDR AS H 
																				 LEFT JOIN WMS_NEW.MTO_RTN_EXCDTL AS D ON D.MTONO = H.MTONO
																				 WHERE CONCAT(H.EDITDATE,' ',H.EDITTIME) BETWEEN '$POSTEDRFOM' AND '$POSTEDTO' AND D.SKUNO = '$SKUNO'
																				 ORDER BY H.EDITDATE DESC,H.EDITTIME DESC",
																				 "MTONO");
																				 
							$EXMTOQTY	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT SUM(D.QTY) AS QTY FROM WMS_NEW.MTO_RTN_EXCHDR AS H 
																				 LEFT JOIN WMS_NEW.MTO_RTN_EXCDTL AS D ON D.MTONO = H.MTONO
																				 WHERE CONCAT(H.EDITDATE,' ',H.EDITTIME) BETWEEN '$POSTEDRFOM' AND '$POSTEDTO' AND D.SKUNO = '$SKUNO'
																				 ORDER BY H.EDITDATE DESC,H.EDITTIME DESC",
																				 "QTY");
							
							$EXPOSTDATE	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCHDR","CONCAT(EDITDATE,' ',EDITTIME)","MTONO ='$EXMTONO'");
							
							if($EXPOSTDATE == "0000-00-00 00:00:00")
							{
								$EXMTONO	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT D.MTONO FROM WMS_NEW.MTO_RTN_EXCHDR AS H 
																					 LEFT JOIN WMS_NEW.MTO_RTN_EXCDTL AS D ON D.MTONO = H.MTONO
																					 WHERE CONCAT(H.ADDDATE,' ',H.ADDTIME) BETWEEN '$POSTEDRFOM' AND '$POSTEDTO' AND D.SKUNO = '$SKUNO'
																					 ORDER BY H.ADDDATE DESC,H.ADDTIME DESC",
																					 "MTONO");
																					 
								$EXMTOQTY	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT SUM(D.QTY) AS QTY FROM WMS_NEW.MTO_RTN_EXCHDR AS H 
																					 LEFT JOIN WMS_NEW.MTO_RTN_EXCDTL AS D ON D.MTONO = H.MTONO
																					 WHERE CONCAT(H.ADDDATE,' ',H.ADDTIME) BETWEEN '$POSTEDRFOM' AND '$POSTEDTO' AND D.SKUNO = '$SKUNO'
																					 ORDER BY H.ADDDATE DESC,H.ADDTIME DESC",
																					 "QTY");
								$EXPOSTDATE	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCHDR","CONCAT(ADDDATE,' ',ADDTIME)","MTONO ='$EXMTONO'");
							}
							
							
							$SCANNEDQTY		=	$RSGETSCANNED->fields["SCANNEDQTY"]-$DEFECTIVEQTY-$IB_QTY;
							if(($EXMTONO != "" and $EXPOSTDATE >= $POSTEDRFOM))
							{
								if($REGMTONO != "")
								{
									if($REGPOSTDATE < $EXPOSTDATE)
									{
										$SUMMEDQTY	=	$SUMMEDQTY - $REGMTOQTY;
									}
								}
								echo "<tr  align='center'>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td>$SUMMEDQTY</td>
										<td>$REGMTONO</td>
										<td>$REGPOSTDATE</td>
										<td>$REGMTOQTY</td>
										<td>$EXMTONO</td>
										<td>$EXPOSTDATE</td>
										<td>$EXMTOQTY</td>
									</tr>";
								
								$EXQTY			=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCDTL","QTY","MTONO = '$EXMTONO'  AND `SKUNO` = '$SKUNO'");
								$GROSSAMOUNT	=	$UNITPRICE * $SUMMEDQTY;
								if($EXQTY != $SUMMEDQTY)
								{
//									$UNITPRICE		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCDTL","UNITPRICE","MTONO = '$EXMTONO'  AND `SKUNO` = '$SKUNO'");
//									$GROSSAMOUNT	=	$UNITPRICE * $SUMMEDQTY;
//									
//									$UPDATEXMTOQTY		=	"UPDATE WMS_NEW.MTO_RTN_EXCDTL SET `QTY` = '$SUMMEDQTY', `GROSSAMT` = '$GROSSAMOUNT' WHERE `MTONO` = '$EXMTONO' AND `SKUNO` = '$SKUNO'";
//									$RSUPDATEXMTOQTY	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEXMTOQTY,$user,"EXMTO_CHECK","EXMTO_CHECK");
//									
//									$UPDATEXMTOQTYRAW	=	"UPDATE WMS_NEW.MTO_RAWDTL SET `QTY` = '$SUMMEDQTY', `GROSSAMT` = '$GROSSAMOUNT' WHERE `MTONO` = '$EXMTONO' AND `SKUNO` = '$SKUNO'";
//									$RSUPDATEXMTOQTYRAW	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATEXMTOQTYRAW,$user,"EXMTO_CHECK","EXMTO_CHECK");
//									echo "$UPDATEXMTOQTY <br> $UPDATEXMTOQTYRAW <br><br>";
								
								}
								if($rowcount == $cnt)
								{
									$GETMOREMTO	=	"SELECT D.MTONO,CONCAT(H.ADDDATE,' ',H.ADDTIME) AS MTODATE,QTY FROM WMS_NEW.MTO_RTN_EXCHDR AS H 
													 LEFT JOIN WMS_NEW.MTO_RTN_EXCDTL AS D ON D.MTONO = H.MTONO
													 WHERE CONCAT(H.ADDDATE,' ',H.ADDTIME) BETWEEN '$EXPOSTDATE' AND NOW()  AND D.SKUNO = '$SKUNO'";
									$RSGETMOREMTO	=	$conn_255_10->Execute($GETMOREMTO);
									if($RSGETMOREMTO == false)
									{
										echo $conn_255_10->ErrorMsg()."::".__LINE__; exit();
									}
									else 
									{
										while (!$RSGETMOREMTO->EOF) {
											$MTONO		=	$RSGETMOREMTO->fields["MTONO"];
											$MTODATE	=	$RSGETMOREMTO->fields["MTODATE"];
											$QTY		=	$RSGETMOREMTO->fields["QTY"];
											if($MTODATE > $EXPOSTDATE)
											{
												echo "<tr  align='center'>
														<td>$MTONO</td>
														<td>$MTODATE</td>
														<td>$QTY</td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
														<td></td>
													</tr>";
											}
											$RSGETMOREMTO->MoveNext();
										}
									}
								}
								$SUMMEDQTY = 0;
								$REGMTOQTY = 0;
							}
							$SUMMEDQTY	+=	$SCANNEDQTY-$REGMTOQTY;
								echo "<tr  align='center'>
										<td>$cnt</td>
										<td>$MPOSNO</td>
										<td>$SKUNO</td>
										<td>$SCANNEDQTY</td>
										<td>$POSTEDDT</td>
										<td>$item_display</td>
										<td>$SUMMEDQTY</td>
										<td>$REGMTONO</td>
										<td>$REGPOSTDATE</td>
										<td>$REGMTOQTY</td>
										<td></td>
										<td></td>
										<td></td>
									</tr>";
							$POSTEDRFOM	=	$POSTEDTO;
							$RSGETSCANNED->MoveNext();
						}
					}
				}
				echo "</table><br>";
				$RSGETEXITEMS->MoveNext();
			}
		}
	}
?>