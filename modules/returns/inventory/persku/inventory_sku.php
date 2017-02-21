<?php
session_start();
//include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");
if (empty($_SESSION['username'])) 
{
	echo "<script>
				MessageType.sessexpMsg('wms');
		  </script>";
	exit();
}
$action	=	$_GET['action'];
	if($action == "Q_SEARCHITEM")
	{
		$txtitemno 		= $_GET["ITEMNO"];
		$txtitemdesc 	= $_GET["ITEMDESC"];
		$sel =	"SELECT s.SKUNO,i.ItemDesc  FROM  FDCRMSlive.itemmaster as i
				 LEFT JOIN WMS_NEW.SCANDATA_DTL as s on s.SKUNO = i.ItemNo
				 WHERE 1";
		if ($txtitemno != "") 
		{
			$sel	.=	" AND s.SKUNO like '%{$txtitemno}%' ";
		}
		if ($txtitemdesc != "") 
		{
			$sel	.=	" AND i.ItemDesc like '%{$txtitemdesc}%' ";
		}
			$sel	.=	" group by s.SKUNO limit 20 ";
//		echo "$sel"; exit();
		$rssel	=	$conn_255_10->Execute($sel);
		if ($rssel == false) 
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"INVENTORY","Q_SEARCHCUST");
			$DATASOURCE->displayError();
		}
		$cnt	=	$rssel->RecordCount();
		if ($cnt > 0) 
		{
			echo "<select id='selitem' class = 'C_dropdown divsel' style='width:532px;height:auto;' onkeypress='smartselitem(event);' multiple>";
			while (!$rssel->EOF) 
			{
				$q_ITEMNO	=	$rssel->fields['SKUNO'];
				$Q_ITEM_DESC=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['ItemDesc']);
				$cValue		=	$q_ITEMNO."|".$Q_ITEM_DESC;
				echo "<option value=\"$cValue\" onclick=\"smartselitem('click');\">$q_ITEMNO-$Q_ITEM_DESC</option>";
				$rssel->MoveNext();
			}
			echo "</select>";
		}
		else
		{
			echo "";
		}
		exit();
	}
	if($action == "GETITEMDTLS")
	{
		$ITEMNO			=	$_GET["ITEMNO"];
		$GETMTOMPOS		=	"SELECT MPOSNO FROM  WMS_NEW.MTO_RTNDTL AS D
							 LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.MTONO = D.MTONO
							 WHERE SKUNO = '$ITEMNO' AND (H.STATUS='POSTED' OR H.STATUS='PRINTED')
							 GROUP BY MPOSNO";
		$RSGETMTOMPOS	=	$conn_255_10->Execute($GETMTOMPOS);
		if($RSGETMTOMPOS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMTOMPOS,$_SESSION['username'],"INVENTORY","GETITEMDTLS");
			$DATASOURCE->displayError();
		}
		else 
		{
			if($RSGETMTOMPOS->RecordCount() > 0)
			{
				while (!$RSGETMTOMPOS->EOF) {
					$MTOMPOSNOLIST	.=	","."'".$RSGETMTOMPOS->fields["MPOSNO"]."'";
					$RSGETMTOMPOS->MoveNext();
				}
				$MTOMPOSNOLIST		=	substr($MTOMPOSNOLIST,1);
				$MTOMPOSNOLIST_Q	=	" AND MPOSNO NOT IN ($MTOMPOSNOLIST)";
			}
		}
		
		$GETSCANNEDMPOS	=	"SELECT MPOSNO FROM  WMS_NEW.SCANDATA_DTL WHERE SKUNO = '$ITEMNO' $MTOMPOSNOLIST_Q
							 GROUP BY MPOSNO";
		$RSGETSCANNEDMPOS	=	$conn_255_10->Execute($GETSCANNEDMPOS);
		if($RSGETSCANNEDMPOS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETSCANNEDMPOS,$_SESSION['username'],"INVENTORY","GETITEMDTLS");
			$DATASOURCE->displayError();
		}
		else 
		{
			if($RSGETSCANNEDMPOS->RecordCount() > 0)
			{
				while (!$RSGETSCANNEDMPOS->EOF) {
					$SCANNEDMPOSNOLIST	.=	","."'".$RSGETSCANNEDMPOS->fields["MPOSNO"]."'";
					$RSGETSCANNEDMPOS->MoveNext();
				}
				$SCANNEDMPOSNOLIST	=	substr($SCANNEDMPOSNOLIST,1);
			}
		}
		
		$SCANNEDQTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","SUM(POSTEDQTY)","SKUNO= '{$ITEMNO}'");
		$DEFECTIVEQTY	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","SCANDATA_DTL","SUM(DEFECTIVEQTY)","SKUNO= '{$ITEMNO}'");
		$MTOQTY			=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTNDTL AS D LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.MTONO = D.MTONO","SUM(QTY)","SKUNO= '{$ITEMNO}' AND (STATUS = 'POSTED' OR H.STATUS='PRINTED')");
		
		$TDMTOQTY		=	$MTOQTY;
		$GOOD			=	$SCANNEDQTY - $DEFECTIVEQTY;
		$TDSCANNEDQTY	=	$SCANNEDQTY - $MTOQTY;
		$TDGOOD			=	$GOOD - $MTOQTY;
		
		$is_exclusive	=	"NO";
		$comaction		=	"hide";
		$foundexclusive	=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","ITEMNO","ITEMNO= '{$ITEMNO}' AND CANCELLED = 'N'");
		$ITEMDESC		=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$ITEMNO}'");
		echo getitemdtlstbl();
		echo "<script>
					$('.lblitemno').text('$ITEMNO');
					$('.lblitemdesc').text('$ITEMDESC');
			  </script>";
		if($foundexclusive != "")
		{
			$is_exclusive	=	"YES";
			$comaction		=	"show";
			
			$TDMTOQTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCDTL AS D LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO","SUM(QTY)","SKUNO= '{$ITEMNO}' AND (STATUS = 'POSTED' OR H.STATUS='PRINTED')");
			$INP_MTOQTY		=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCDTL AS D LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO","SUM(QTY)","SKUNO= '{$ITEMNO}' AND (STATUS = 'SAVED' OR H.STATUS='UPDATED')");
			$TDSCANNEDQTY	=	$SCANNEDQTY - $TDMTOQTY - $INP_MTOQTY;
			$TDGOOD			=	$GOOD - $TDMTOQTY - $INP_MTOQTY;
			
			
			echo "<script>
						$('#tdIqty').text('".number_format($INP_MTOQTY)."');
						$('#lblitemdesc1').text('$ITEMDESC(Exclusive Item)');
				  </script>";
		}
		echo "<script>
					$('#hdnis_exclusive').val('$is_exclusive');
					$('.tripm').$comaction();
					$('#tdSqty').text('".number_format($TDSCANNEDQTY)."');
					$('#tdSgood').text('".number_format($TDGOOD)."');
					$('#tdSdef').text('".number_format($DEFECTIVEQTY)."');
					$('#tdMqty').text('".number_format($TDMTOQTY)."');
//					$('#tdMgood').text();
//					$('#tdmdef').text();		
//					$('#tdRqty').text(0);
//					$('#tdRgood').text();
//					$('#tdRdef').text();
//					$('#tdPqty').text(0);
//					$('#tdPgood').text();
//					$('#tdPdef').text();
//					$('#tdFqty').text(0);
//					$('#tdFgood').text();
//					$('#tdFdef').text();
					
					$('#hdnmtompos').val(\"$MTOMPOSNOLIST\");
					$('#hdnscannedmpos').val(\"$SCANNEDMPOSNOLIST\");
			 </script>";
		exit();
	}
	if($action == "GETSCANNEDDTLS")
	{
		$ITEMNO			=	$_GET["ITEMNO"];
		$MPOSLIST		=	$_GET["MPOSLIST"];
		
		$GETMPOSDTLS	=	"SELECT D.`MPOSNO`,  SUM(D.`POSTEDQTY`) AS POSTEDQTY, H.POSTEDDATE 
							 FROM WMS_NEW.SCANDATA_DTL AS D
							 LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.MPOSNO = D.MPOSNO
							 WHERE D.MPOSNO IN ($MPOSLIST) AND SKUNO = '$ITEMNO'
							 GROUP BY D.MPOSNO";
		$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
		if($RSGETMPOSDTLS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"INVENTORY","GETSCANNEDDTLS");
			$DATASOURCE->displayError();
		}
		else 
		{
			$table	=	"<br><table class='tblresul-tbltdtls'>
							<tr class='tblresul-tbltdtls-hdr'>
								<td align='center'>No.</td>
								<td align='center'>MPOS No</td>
								<td align='center'>Posted Date</td>
								<td align='center'>Posted Qty</td>
							</tr>";
			$totqty	=	0;
			$cnt	=	1;
			while (!$RSGETMPOSDTLS->EOF) {
				$MPOSNO		=	$RSGETMPOSDTLS->fields["MPOSNO"];
				$POSTEDQTY	=	$RSGETMPOSDTLS->fields["POSTEDQTY"];
				$POSTEDDATE	=	$RSGETMPOSDTLS->fields["POSTEDDATE"];
				$table	.=	"<tr class='tblresul-tbltdtls-dtls'>
								<td align='center'>$cnt</td>
								<td align='center'>$MPOSNO</td>
								<td align='center'>$POSTEDDATE</td>
								<td align='center'>$POSTEDQTY</td>
							 </tr>";
				$cnt++;
				$totqty +=	$POSTEDQTY;
				$RSGETMPOSDTLS->MoveNext();
			}
			
			$table	.=	"<tr class='tblresul-tbltdtls-dtls bld'>
							<td align='center' colspan='3'>TOTAL</td>
							<td align='center'>$totqty</td>
						 </tr>
					</table><br>";
//			$table	.=	"</table>";
			echo $table;
		}
		exit();
	}
	if($action == "GETSMTODTLS")
	{
		$ITEMNO			=	$_GET["ITEMNO"];
		$MPOSLIST		=	$_GET["MPOSLIST"];
		$IS_EXCLUSIVE	=	$_GET["IS_EXCLUSIVE"];
		if($IS_EXCLUSIVE == "YES")
		{
			$GETMTODTLS		=	"SELECT H.MTONO, D.QTY, D.UNITPRICE, D.GROSSAMT  FROM WMS_NEW.MTO_RTN_EXCDTL AS D
								 LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO
								 WHERE SKUNO = '$ITEMNO' AND (H.STATUS = 'POSTED' OR H.STATUS = 'PRINTED')
								 GROUP BY MTONO";	
			$RSGETMTODTLS	=	$conn_255_10->Execute($GETMTODTLS);
			if($RSGETMTODTLS == false)
			{
				$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
				$DATASOURCE->logError("wms",$errmsg,$GETMTODTLS,$_SESSION['username'],"INVENTORY","GETSMTODTLS");
				$DATASOURCE->displayError();
			}
			else 
			{
				$table	=	"<br><table class='tblresul-tbltdtls'>
								<tr class='tblresul-tbltdtls-hdr'>
									<td align='center'>MTO NO.</td>
									<td align='center'>UNIT PRICE</td>
									<td align='center'>QTY</td>
									<td align='center'>GROSS AMOUNT</td>
								</tr>";
				$TOTQTY		=	0;
				$TOTGROSSAMT	=	0;
				while (!$RSGETMTODTLS->EOF)
				{
					$MTONO		=	$RSGETMTODTLS->fields["MTONO"];
					$QTY		=	$RSGETMTODTLS->fields["QTY"];
					$UNITPRICE	=	$RSGETMTODTLS->fields["UNITPRICE"];
					$GROSSAMT	=	$RSGETMTODTLS->fields["GROSSAMT"];
					$table	.=	"<tr class='tblresul-tbltdtls-dtls'>
									<td align='center'>$MTONO</td>
									<td align='center'>$UNITPRICE</td>
									<td align='center'>$QTY</td>
									<td align='center'>".number_format($GROSSAMT,2)."</td>
								 </tr>";
					$TOTQTY			+=	$QTY;
					$TOTGROSSAMT	+=	$GROSSAMT;
					$RSGETMTODTLS->MoveNext();
				}
				$table	.=	"<tr class='tblresul-tbltdtls-dtls bld' >
									<td align='center' colspan='2'>TOTAL</td>
									<td align='center'>$TOTQTY</td>
									<td align='center'>".number_format($TOTGROSSAMT,2)."</td>
								</tr>
							</table><br>";
				echo $table;
			}
		}
		else 
		{
			$GETMPOSDTLS	=	"SELECT D.`MPOSNO`, SUM(D.`QTY`) AS `QTY`, H.POSTDATE 
								 FROM WMS_NEW.MTO_RTNDTL AS D 
								 LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.`MTONO` = D.`MTONO` 
								 WHERE D.MPOSNO IN  ($MPOSLIST) AND SKUNO = '$ITEMNO'
								 GROUP BY D.MPOSNO";
			$RSGETMPOSDTLS	=	$conn_255_10->Execute($GETMPOSDTLS);
			if($RSGETMPOSDTLS == false)
			{
				$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
				$DATASOURCE->logError("wms",$errmsg,$GETMPOSDTLS,$_SESSION['username'],"INVENTORY","GETSMTODTLS");
				$DATASOURCE->displayError();
			}
			else 
			{
				$table	=	"<br><table class='tblresul-tbltdtls'>
								<tr class='tblresul-tbltdtls-hdr'>
									<td align='center'>No.</td>
									<td align='center'>MPOS No.</td>
									<td align='center'>Posted Date</td>
									<td align='center'>Posted Qty</td>
								</tr>";
				$cnt	=	1;
				$totqty	=	0;
				while (!$RSGETMPOSDTLS->EOF) {
					$MPOSNO		=	$RSGETMPOSDTLS->fields["MPOSNO"];
					$POSTEDQTY	=	$RSGETMPOSDTLS->fields["QTY"];
					$POSTEDDATE	=	$RSGETMPOSDTLS->fields["POSTDATE"];
					$table	.=	"<tr class='tblresul-tbltdtls-dtls'>
									<td align='center'>$cnt</td>
									<td align='center'>$MPOSNO</td>
									<td align='center'>$POSTEDDATE</td>
									<td align='center'>$POSTEDQTY</td>
								 </tr>";
					$cnt++;
					$totqty +=	$POSTEDQTY;
					$RSGETMPOSDTLS->MoveNext();
				}
				$table	.=	"<tr class='tblresul-tbltdtls-dtls bld'>
								<td align='center' colspan='3'>TOTAL</td>
								<td align='center'>$totqty</td>
							 </tr>
						</table><br>";
				echo $table;
			}
		}
		exit();
	}
	if($action == "GETIMTODTLS")
	{
		$ITEMNO			=	$_GET["ITEMNO"];
		$GETMTODTLS		=	"SELECT H.MTONO, D.QTY, D.UNITPRICE, D.GROSSAMT  FROM WMS_NEW.MTO_RTN_EXCDTL AS D
							 LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO
							 WHERE SKUNO = '$ITEMNO' AND (H.STATUS = 'SAVED' OR H.STATUS = 'UPDATED')
							 GROUP BY MTONO";	
		$RSGETMTODTLS	=	$conn_255_10->Execute($GETMTODTLS);
		if($RSGETMTODTLS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMTODTLS,$_SESSION['username'],"INVENTORY","GETIMTODTLS");
			$DATASOURCE->displayError();
		}
		else 
		{
			$table	=	"<br><table class='tblresul-tbltdtls'>
								<tr class='tblresul-tbltdtls-hdr'>
									<td align='center'>MTO NO.</td>
									<td align='center'>UNIT PRICE</td>
									<td align='center'>QTY</td>
									<td align='center'>GROSS AMOUNT</td>
								</tr>";
				$TOTQTY		=	0;
				$TOTGROSSAMT	=	0;
				while (!$RSGETMTODTLS->EOF)
				{
					$MTONO		=	$RSGETMTODTLS->fields["MTONO"];
					$QTY		=	$RSGETMTODTLS->fields["QTY"];
					$UNITPRICE	=	$RSGETMTODTLS->fields["UNITPRICE"];
					$GROSSAMT	=	$RSGETMTODTLS->fields["GROSSAMT"];
					$table	.=	"<tr class='tblresul-tbltdtls-dtls'>
									<td align='center'>$MTONO</td>
									<td align='center'>$UNITPRICE</td>
									<td align='center'>$QTY</td>
									<td align='center'>$GROSSAMT</td>
								 </tr>";
					$TOTQTY			+=	$QTY;
					$TOTGROSSAMT	+=	$GROSSAMT;
					$RSGETMTODTLS->MoveNext();
				}
				$table	.=	"<tr class='tblresul-tbltdtls-dtls bld'>
									<td align='center' colspan='2'>TOTAL</td>
									<td align='center'>$TOTQTY</td>
									<td align='center'>$TOTGROSSAMT</td>
								</tr>
							</table><br>";
				echo $table;
		}
		exit();
	}
	function getitemdtlstbl()
	{
		return "<table class='tblresult'>
					<tr class='tblresul-tbltdtls-hdr'>
						<td id='tditem' colspan='4' align='center' class='padding5px colored  radius5pxup'>
							<a href='#lblitemno2'id='lblitemno1' class='coloredfff lblitemno'>&nbsp;</a> <a href='#lblitemdesc2'id='lblitemdesc1' class='coloredfff lblitemdesc'></a>
						</td>
					</tr>
					<tr class='trheader'>
						<td align='center'>LOCATION</td>
						<td align='center'>ONHAND QUANTITY</td>
						<td align='center'>GOOD</td>
						<td align='center'>DEFECTIVE</td>
					</tr>
					
					
					<tr class='trbody trscanning pntr'>
						<td align='center'>
							SCANNING
							<input type='hidden' id='hdnscannedmpos' name='hdnscannedmpos'>
						</td>
						<td align='center' id='tdSqty'>0</td>
						<td align='center' id='tdSgood'>0</td>
						<td align='center' id='tdSdef'>0</td>
					</tr>
					<tr style='display:none;'id='trSdtls'>
						<td id='tdSdtls' class='tdtrxdtlsClass' colspan='4' align='center'></td>
					</tr>
					
					
					<tr class='trbody tripm pntr' style='display:none;'>
						<td align='center'>
							IN-PROCESS MTO
						</td>
						<td align='center' id='tdIqty'>0</td>
						<td align='center' id='tdIgood'></td>
						<td align='center' id='tdIdef'></td>
					</tr>
					<tr style='display:none;'id='trIdtls'>
						<td id='tdIdtls' class='tdtrxdtlsClass' colspan='4' align='center'></td>
					</tr>
					
					
					<tr class='trbody trmto pntr'>
						<td align='center'>
							POSTED MTO
							<input type='hidden' id='hdnmtompos' name='hdnmtompos'>
						</td>
						<td align='center' id='tdMqty'>0</td>
						<td align='center' id='tdMgood'></td>
						<td align='center' id='tdMdef'></td>
					</tr>
					<tr style='display:none;'id='trMdtls'>
						<td id='tdMdtls' class='tdtrxdtlsClass' colspan='4' align='center'></td>
					</tr>
					
					
					<!--<tr class='Text_header_hover trraw'>
						<td class='padding5px colored'>RAW</td>
						<td class='padding5px colored' id='tdRqty'>0</td>
						<td class='padding5px colored' id='tdRgood'></td>
						<td class='padding5px colored' id='tdRdef'></td>
					</tr>
					<tr style='display:none;'id='trRdtls'>
						<td id='tdRdtls' class='tddtls' colspan='4'></td>
					</tr>
					<tr class='Text_header_hover trpiecework'>
						<td class='padding5px colored'>PIECEWORK</td>
						<td class='padding5px colored' id='tdPqty'>0</td>
						<td class='padding5px colored' id='tdPgood'></td>
						<td class='padding5px colored' id='tdPdef'></td>
					</tr>
					<tr style='display:none;'id='trPdtls'>
						<td id='tdPdtls' class='tddtls' colspan='4'></td>
					</tr>
					<tr class='Text_header_hover trfillingbin'>
						<td class='padding5px colored'>FILLING BIN</td>
						<td class='padding5px colored' id='tdFqty'>0</td>
						<td class='padding5px colored' id='tdFgood'></td>
						<td class='padding5px colored' id='tdFdef'></td>
					</tr>
					<tr style='display:none;'id='trFdtls'>
						<td id='tdFdtls' class='tddtls' colspan='4'></td>
					</tr>
					<tr style='background-color:#6a90c8;font-weight:bold;'>
						<td id='tditem' colspan='4' align='center' class='padding5px colored radius5pxdn'>
							<a href='#lblitemno1'id='lblitemno2' class='coloredfff lblitemno'>&nbsp;</a> <a href='#lblitemdesc1' id='lblitemdesc2' class='coloredfff lblitemdesc'></a>
						</td>
					</tr>-->
				</table>";
	}

include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/inventory/persku/inventory_sku.html");
?>