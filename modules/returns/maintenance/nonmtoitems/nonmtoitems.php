<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}
$action	=	$_GET['action'];
if($action == "GETITEMDESC")
{
	$itemno		=	$_GET["ITEMNO"];
	$curcnt		=	$_GET["CURCNT"];
	$itemcnt	=	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","ITEMNO","ITEMNO= '{$itemno}' AND CANCELLED != 'Y'");
	if($itemcnt == "")
	{
		$getdesc	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo= '{$itemno}'");
		$SELLPRICE	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo= '{$itemno}'");
		$ITEMSTATUS	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","DeptNo","ItemNo= '{$itemno}'");
		if($ITEMSTATUS != "P")
		{
			if($getdesc != "")
			{
				echo "<script>
							$('#tditemdesc$curcnt').text('$getdesc');
							$('#tdsrp$curcnt').text('$SELLPRICE');
							$('#tditemdesc$curcnt').removeClass('errnotfound');
					  </script>";
			}
			else 
			{
				echo "<script>
							$('#tditemdesc$curcnt').text('Item not found.');
							$('#tditemdesc$curcnt').addClass('errnotfound');
							$('#tdsrp$curcnt').text('');
							$('#txtitemno$curcnt').val('');
					  </script>";
			}
		}
		else 
		{
			echo "<script>
					$('#tditemdesc$curcnt').text('Item is Prime.');
					$('#tditemdesc$curcnt').addClass('errnotfound');
					$('#tdsrp$curcnt').text('');
					$('#txtitemno$curcnt').val('');
			  </script>";
		}
	}
	else 
	{
		echo "<script>
						$('#tditemdesc$curcnt').text('Item already exists.');
						$('#tditemdesc$curcnt').addClass('errnotfound');
						$('#tdsrp$curcnt').text('');
						$('#txtitemno$curcnt').val('');
				  </script>";
	}
	exit();
}
if($action == "SAVETRX")
{
	$TXNO	=	newTRXno($Filstar_conn);
	$cnt	=	$_POST["hidcnt"];
	$Filstar_conn->StartTrans();
	$SAVEMTOTRX	=	"INSERT INTO WMS_LOOKUP.MTO_EX_ITEMS_HDR(`TRX_NO`, `STATUS`, `CREATEDDT`, `CREATEDBY`)
					 VALUES('{$TXNO}','SAVED',NOW(),'{$_SESSION['username']}')";
	$RSSAVEMTOTRX = $Filstar_conn->Execute($SAVEMTOTRX);
	if($RSSAVEMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		for($a = 1; $a <= $cnt; $a++)
		{
			$itemno	=	$_POST["txtitemno$a"];
			$getdesc	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo= '{$itemno}'");
			$SELLPRICE	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo= '{$itemno}'");
			if($itemno != "" and $itemno != undefined)
			{
				$SAVEMTOTRXDTLS	=	"INSERT INTO WMS_LOOKUP.MTO_EX_ITEMS_DTLS(`TRX_NO`, `ITEMNO`, `ITEM_DESC`, `SRP`)
									 VALUES('{$TXNO}','{$itemno}','{$getdesc}','{$SELLPRICE}')";
				$RSSAVEMTOTRXDTLS	=	$Filstar_conn->Execute($SAVEMTOTRXDTLS);
				if($RSSAVEMTOTRXDTLS == false)
				{
					$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
				}
			}
		}
	}
	$Filstar_conn->CompleteTrans();
	echo "<script>alert('Transaction $TXNO has been successfully saved.');location.reload();</script>";
	exit();
}
if($action == "SEARCHTRX")
{
	$TRXNO	=	$_POST["txttrxno"];
	$itemno	=	$_POST["txtitemno"];
	$Cdfrom	=	$_POST["Cdfrom"];
	$Cdto	=	$_POST["Cdto"];
	$Pdfrom	=	$_POST["Pdfrom"];
	$Pdto	=	$_POST["Pdto"];
	$selstatus	=	$_POST["selstatus"];
	$timef	=	"00:00:00";
	$timet	=	"23:59:59";
	if($TRXNO != "")
	{
		$TRXNO_Q	=	" AND TRX_NO LIKE '%{$TRXNO}%'";
	}
	if($itemno != "")
	{
		echo getItems($Filstar_conn,"","",$itemno,$global_func);
		exit();
	}
	if($Cdfrom != "")
	{
		$Cdfrom_Q	=	" AND CREATEDDT BETWEEN '$Cdfrom $timef' AND '$Cdto $timet'";
	}
	if($Pdfrom != "")
	{
		$Pdfrom_Q	=	" AND CREATEDDT BETWEEN '$Pdfrom $timef' AND '$Pdto $timet'";
	}
	if($selstatus != "")
	{
		$selstatus_Q	=	" AND STATUS = '{$selstatus}'";
	}
	$GETTRX	=	"SELECT * FROM WMS_LOOKUP.MTO_EX_ITEMS_HDR WHERE 1 $TRXNO_Q $TRXNO_D_Q $Cdfrom_Q $Pdfrom_Q $selstatus_Q";
	$RSGETTRX	=	$Filstar_conn->Execute($GETTRX);
	if($RSGETTRX == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		if($RSGETTRX->RecordCount() > 0)
		{
			echo "<table border='1' class='tblresult'>
						<tr class='trheader'>
					 		<td >Line No.</td>
					 		<td >Transaction No.</td>
					 		<td >Status</td>
					 		<td >Date Created</td>
					 		<td >Created By</td>
					 		<td >Updated Date</td>
					 		<td >Updated By</td>
					 		<td >Posted Date</td>
					 		<td >Posted By</td>
					 		<td >Actions</td>
					 	</tr>";
			$cnt = 1;
			while (!$RSGETTRX->EOF) 
			{
				
				$TRX_NO 	= $RSGETTRX->fields["TRX_NO"]; 
				$STATUS 	= $RSGETTRX->fields["STATUS"]; 
				$CREATEDDT 	= $RSGETTRX->fields["CREATEDDT"]; 
				$CREATEDBY 	= $RSGETTRX->fields["CREATEDBY"]; 
				$UPDATEDDT 	= $RSGETTRX->fields["UPDATEDDT"]; 
				$UPDATEDBY 	= $RSGETTRX->fields["UPDATEDBY"]; 
				$POSTEDDT 	= $RSGETTRX->fields["POSTEDDT"]; 
				$POSTEDBY 	= $RSGETTRX->fields["POSTEDBY"]; 
				if($STATUS == "SAVED" OR $STATUS == "UPDATED")
				{
					$btnedit	=	"<img src='/wms/images/images/action_icon/new/compose.png' class='smallbtns editbtn' title='Edit Trx: $TRX_NO' data-trxno='$TRX_NO'>";
					$btnpost	=	"<img src='/wms/images/images/action_icon/new/mail.png' class='smallbtns postbtn' title='Post Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btnedit	=	"";
					$btnpost	=	"";
				}
				echo "<tr class='trbody'  id='trdtls$cnt' title='Click to view details'>
					 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$cnt</td>
					 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$TRX_NO</td>
					 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$STATUS</td>
					 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$CREATEDDT</td>
					 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$CREATEDBY</td>	
					 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$UPDATEDDT</td>
					 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$UPDATEDBY</td>		
					 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$POSTEDDT</td>	
					 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$POSTEDBY</td>	
					 		<td align='center'>$btnedit $btnpost</td>	
					 	</tr> 
					 	<tr>
						 		<td id='tdtrxdtls$cnt' colspan='10' class='tdtrxdtlsClass trbody' align='center'></td>
						</tr>";
				$cnt++;
				$RSGETTRX->MoveNext();
			}
			echo "</table>";
//			echo "<script>$('#hidcnt').val('$cnt');</script>";
		}
		else 
		{
			echo getTBLprev();
		}
	}
	exit();
}
if($action == "VIEWTRXDTLS")
{
	$TRX_NO	=	$_GET["TRXNO"];
	$COUNT	=	$_GET["COUNT"];
	echo getItems($Filstar_conn,$TRX_NO,$COUNT,"",$global_func);
	exit();
}
function getItems($Filstar_conn,$TRX_NO,$COUNT,$itemno,$global_func)
{
	if($itemno != "")
	{
		$ITEMNO_Q	=	" AND ITEMNO = '{$itemno}'";
	}
	if($TRX_NO != "")
	{
		$TRX_NO_Q	=	" AND  TRX_NO = '{$TRX_NO}'";
	}
	$GETDTLS	=	"SELECT * FROM WMS_LOOKUP.MTO_EX_ITEMS_DTLS WHERE 1 $ITEMNO_Q $TRX_NO_Q";
	$RSGETDTLS	=	$Filstar_conn->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		$table = "<br><form id='frmcancel'>
					<table border='1' class='tblresul-tbltdtls'>
					<tr class='tblresul-tbltdtls-hdr'>
				 		<td >Line No.</td>
				 		<td >TRX No.</td>
				 		<td >Item No.</td>
				 		<td >SKU Description</td>
				 		<td >SRP</td>
				 		<td >Cancelled</td>
				 		<td >Cancelled By</td>
				 		<td >Cancelled Date</td>
				 		<td >Action</td>
				 	</tr>";
		$cnt = 0;
		$trxstatus	=	$global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_HDR","STATUS","TRX_NO= '{$TRX_NO}'");
		while (!$RSGETDTLS->EOF) {
			$cnt++;
			$TRX_NO 	= $RSGETDTLS->fields["TRX_NO"]; 
			$ITEMNO 	= $RSGETDTLS->fields["ITEMNO"]; 
			$ITEM_DESC	= $RSGETDTLS->fields["ITEM_DESC"]; 
			$SRP		= $RSGETDTLS->fields["SRP"]; 
			$CANCELLED	= $RSGETDTLS->fields["CANCELLED"]; 
			$CANCELLEDBY= $RSGETDTLS->fields["CANCELLEDBY"]; 
			$CANCELLEDDT= $RSGETDTLS->fields["CANCELLEDDATE"]; 
			if($trxstatus == "POSTED")
			{
				$checkbox	=	"<input type='checkbox' id='chkcancelitem$cnt' name='chkcancelitem$cnt' value='$ITEMNO'>";
			}
			else 
			{
				$checkbox	=	"";
			}
			$table .= "<tr class='tblresul-tbltdtls-dtls'>
				 		<td align='center'>$cnt</td>
				 		<td align='center'>$TRX_NO</td>
				 		<td align='center'>$ITEMNO</td>
				 		<td>$ITEM_DESC</td>
				 		<td align='center'>$SRP</td>
				 		<td align='center'>$CANCELLED</td>
				 		<td align='center'>$CANCELLEDBY</td>
				 		<td align='center'>$CANCELLEDDT</td>
				 		<td align='center'>
				 			$checkbox
				 		</td>
			 	  </tr>";
			$RSGETDTLS->MoveNext();
		}
		if($trxstatus == "POSTED")
		{
			$btncancel	=	"<img src='/wms/images/images/action_icon/new/stop.png' class='smallbtns cancelbtn' title='Cancel selected item/s' data-cnt='$cnt'>";
			$table	 	.=	"<tr class='tblresul-tbltdtls-dtls bld'>
								<td colspan='8'></td>
								<td align='center'>$btncancel</td>
							</tr>";
		}
		else 
		{
			$btncancel 	=	"";
		}
			
		$table .= "</table>
			</form>";
	}
	return $table;
}
if($action == "EDITTRX")
{
	$TRXNO			=	$_GET["TRXNO"];
	$GETTRXDTLS		=	"SELECT * FROM  WMS_LOOKUP.MTO_EX_ITEMS_DTLS WHERE TRX_NO = '{$TRXNO}'";
	$RSGETTRXDTLS	=	$Filstar_conn->Execute($GETTRXDTLS);
	if($RSGETTRXDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		$cnt	=	1;
		while (!$RSGETTRXDTLS->EOF) {
			$ITEMNO 	= $RSGETTRXDTLS->fields["ITEMNO"]; 
			$ITEM_DESC	= $RSGETTRXDTLS->fields["ITEM_DESC"]; 
			$SRP		= $RSGETTRXDTLS->fields["SRP"]; 
			if($cnt > 1)
			{
				echo "<script>
						$('.addbtn').trigger('click');
				 	  </script>";
			}
			echo "<script>
						$('#txtitemno$cnt').val('$ITEMNO');
						$('#tdcurcnt$cnt').text('$cnt');
						$('#tditemdesc$cnt').text('$ITEM_DESC');
						$('#tdsrp$cnt').text('$SRP');
						$('#hidcnt').val('$cnt');
				  </script>";
			
			$cnt++;
			$RSGETTRXDTLS->MoveNext();
		}
	}
	exit();
}
if($action == "UPDATETRX")
{
	$TRX_NO	=	$_GET["TRXNO"];
	$cnt	=	$_POST["hidcnt"];
	$Filstar_conn->StartTrans();
	$DELTRXDTLS	=	"DELETE FROM WMS_LOOKUP.MTO_EX_ITEMS_DTLS WHERE TRX_NO= '{$TRX_NO}'";
	$RSDELTRXDTLS	=	$Filstar_conn->Execute($DELTRXDTLS);
	if($RSDELTRXDTLS == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	$UPDATEMTOTRX	=	"UPDATE WMS_LOOKUP.MTO_EX_ITEMS_HDR SET `STATUS` = 'UPDATED', `UPDATEDDT` = NOW(), `UPDATEDBY` = '{$_SESSION['username']}'
						 WHERE TRX_NO = '{$TRX_NO}'";
	$RSUPDATEMTOTRX	= 	$Filstar_conn->Execute($UPDATEMTOTRX);
	if($RSUPDATEMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		for($a = 1; $a <= $cnt; $a++)
		{
			$itemno	=	$_POST["txtitemno$a"];
			$getdesc	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo= '{$itemno}'");
			$SELLPRICE	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo= '{$itemno}'");
			if($itemno != "" and $itemno != undefined)
			{
				$SAVEMTOTRXDTLS	=	"INSERT INTO WMS_LOOKUP.MTO_EX_ITEMS_DTLS(`TRX_NO`, `ITEMNO`, `ITEM_DESC`, `SRP`)
									 VALUES('{$TRX_NO}','{$itemno}','{$getdesc}','{$SELLPRICE}')";
				$RSSAVEMTOTRXDTLS	=	$Filstar_conn->Execute($SAVEMTOTRXDTLS);
				if($RSSAVEMTOTRXDTLS == false)
				{
					$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
				}
			}
		}
	}
	$Filstar_conn->CompleteTrans();
	echo "<script>alert('Transaction $TRX_NO has been successfully updated.');location.reload();</script>";
	exit();
}
if($action == "POSTTRX")
{
	$TRXNO	=	$_GET["TRXNO"];
	$Filstar_conn->StartTrans();
	$POSTMTOTRX	=		"UPDATE WMS_LOOKUP.MTO_EX_ITEMS_HDR SET `STATUS` = 'POSTED', `POSTEDDT` = NOW(), `POSTEDBY` = '{$_SESSION['username']}'
						 WHERE TRX_NO = '{$TRXNO}'";
	$RSPOSTMTOTRX	= 	$Filstar_conn->Execute($POSTMTOTRX);
	if($RSPOSTMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
//		dms_save($Filstar_conn,$TRXNO);
		echo "<script>alert('Transaction $TRXNO has been successfully posted.');location.reload();</script>";
	}
	$Filstar_conn->CompleteTrans();
	exit();
}
if($action == "CANCELITEM")
{
	$cnt	=	$_GET["CNT"];
	$TODAY	=	date("Y-m-d");
	$time 	=	date("H:i:s");
	$Filstar_conn->StartTrans();
		for ($a = 1; $a <= $cnt; $a++)
		{
			$ITEMNO		=	$_POST["chkcancelitem$a"];
			if($ITEMNO != "")
			{
				echo $CANCELITEM	=	"UPDATE WMS_LOOKUP.MTO_EX_ITEMS_DTLS SET `CANCELLED`='Y', `CANCELLEDBY`='{$_SESSION['username']}', `CANCELLEDDATE`='{$TODAY}', `CANCELLEDTIME`='{$time}'
							 WHERE ITEMNO = '{$ITEMNO}'";
				$RSCANCELITEM	=	$Filstar_conn->Execute($CANCELITEM);
				if($RSCANCELITEM == false)
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
				}
			}
		}
	$Filstar_conn->CompleteTrans();
	echo "<script>alert('Selected item/s has/have been successfully cancelled.');location.reload();</script>";
	exit();
}
function dms_save($Filstar_conn,$TRXNO)
{
	$getmtohdr		=	"SELECT * FROM WMS_NEW.MTO_RTN_EXCHDR WHERE MTONO = '$TRXNO'";
	$RSgetmtohdr	=	$Filstar_conn->Execute($getmtohdr);
	if($RSgetmtohdr == false)
	{
		echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
		exit();
	}
	else
	{
		while (!$RSgetmtohdr->EOF)
		{
			$MTONO 		= $RSgetmtohdr->fields["MTONO"]; 
			$fndmto		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","mtoheader","mhmtnum","mhmtnum = '{$MTONO}'");
			if($fndmto == "")
			{
				$DESTINATION 	= "FDC"; 
				$ADDBY 			= $RSgetmtohdr->fields["ADDBY"]; 
				$ADDDATE 		= $RSgetmtohdr->fields["ADDDATE"]; 
				$SRC			= "RTN";
				
				$INSERTMTO_10	=	"INSERT INTO  FDCRMSlive.mtoheader(`mhmtnum`, `mhfrhse`, `mhtohse`, `mhcrtby`, `mhcrtdt`)
									 VALUES('$MTONO','$SRC','$DESTINATION','$ADDBY','$ADDDATE')";
				$RSINSERTMTO_10	=	$Filstar_conn->Execute($INSERTMTO_10);
				if($RSINSERTMTO_10 == false)
				{
					echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
					exit();
				}
				else
				{
					$getmtodtls		=	"SELECT MTONO,SKUNO,DESCRIPTION,SUM(QTY) AS QTY,UNITPRICE,SUM(GROSSAMT) AS GROSSAMT 
										 FROM  WMS_NEW.MTO_RTN_EXCDTL 
										 WHERE MTONO = '$MTONO'
										 GROUP BY SKUNO";
					$RSgetmtodtls	=	$Filstar_conn->Execute($getmtodtls);
					if($RSgetmtodtls == false)
					{
						echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
						exit();
					}
					else
					{
						while (!$RSgetmtodtls->EOF)
						{
							$MTONO 			= $RSgetmtodtls->fields["MTONO"]; 
	//						$MPOSNO 		= $RSgetmtodtls->fields["MPOSNO"]; 
							$SKUNO 			= $RSgetmtodtls->fields["SKUNO"]; 
							$DESCRIPTION 	= addslashes($RSgetmtodtls->fields["DESCRIPTION"]); 
							$QTY 			= $RSgetmtodtls->fields["QTY"]; 
							$UNITPRICE 		= $RSgetmtodtls->fields["UNITPRICE"]; 
							$GROSSAMT		= $RSgetmtodtls->fields["GROSSAMT"]; 
							
							$INSERTMTOdtls_10	=	"INSERT INTO  FDCRMSlive.mtodetail(`mdmtnum`,`mditmno`, `mditmds`, `mdwhscd`, `mduntpr`, `mdgramt`, `mdrcvqt`, `mdwhsqt`)
													 VALUES('$MTONO','$SKUNO','$DESCRIPTION','$DESTINATION','$UNITPRICE','$GROSSAMT','$QTY','$QTY')";
							$RSINSERTMTOdtls_10	=	$Filstar_conn->Execute($INSERTMTOdtls_10);
							if($RSINSERTMTOdtls_10 == false)
							{
								echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
								exit();
							}
						$RSgetmtodtls->MoveNext();
						}
					}
				}
			}
		$RSgetmtohdr->MoveNext();
		}
	}
}
function  newTRXno($dbconn)
{
	$forTRXno		=	"SELECT	TRX_NO,CREATEDDT FROM  WMS_LOOKUP.MTO_EX_ITEMS_HDR order by LINE_NO";
	$rsforTRXno		=	$dbconn->Execute($forTRXno);
	if ($rsforTRXno == false) 
	{
		echo $errmsg	=	$conn->ErrorMsg()."::".__LINE__; 
		exit();
	}
	while (!$rsforTRXno->EOF) 
	{
		$date1		=	date('Y-m-d', strtotime($rsforTRXno->fields['CREATEDDT']));	
		$lastTRXno 	= 	$rsforTRXno->fields['TRX_NO'];
		$rsforTRXno->MoveNext();
	}
	 $dgt		=	substr($lastTRXno, 14);	
	 $newdgt 	= 	$dgt + 1;
	 $lnt 		= 	strlen($newdgt);
	 $date2		=	date('Y-m-d');
	 if( $date1==$date2 )
	 {
		if($lnt == 1)
		{
			$newTRXno	=	"NMTO-".date('Ymd').'-'."00".$newdgt;
		}
		if($lnt	==	2)
		{
			$newTRXno	=	"NMTO-".date('Ymd').'-'."0".$newdgt;
		}
		if($lnt	==	3)
		{
			$newTRXno	=	"NMTO-".date('Ymd').'-'.$newdgt;
		}
	 }
	 else 
	 {
	 	$newTRXno	=	"NMTO-".date('Ymd').'-'."001";
	 }
	 
	return $newTRXno;
}
function getTBLprev()
{
	return "<table border='1' class='tblresult'>
				<tr class='trheader'>
			 		<td >Line No.</td>
			 		<td >Transaction No.</td>
			 		<td >Status</td>
			 		<td >Date Created</td>
			 		<td >Created By</td>
			 		<td >Updated Date</td>
			 		<td >Updated By</td>
			 		<td >Posted Date</td>
			 		<td >Posted By</td>
			 		<td >Actions</td>
			 	</tr>
		 		<tr class='trbody centered fnt-red'>
			 		<td colspan='11'>Nothing to display.</td>
			 	</tr>
			 </table>";
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/maintenance/nonmtoitems/nonmtoitems.html");
?>