<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}
$action	=	$_GET['action'];
	if($action == "Q_SEARCHITEM")
	{
		$txtitemno 		= $_GET["ITEMNO"];
		$txtitemdesc 	= $_GET["ITEMDESC"];
		$sel	 =	"SELECT s.SKUNO,i.ItemDesc  FROM  FDCRMSlive.itemmaster as i
		LEFT JOIN WMS_NEW.SCANDATA_DTL as s on s.SKUNO = i.ItemNo
		WHERE 1";
		
		if (!empty($txtitemno)) 
		{
			$sel	.=	" AND s.SKUNO like '%{$txtitemno}%' ";
		}
		if (!empty($txtitemdesc)) 
		{
		$sel	.=	" AND i.ItemDesc like '%{$txtitemdesc}%' ";
		}
		$sel	.=	" group by s.SKUNO limit 20 ";
//		echo "$sel"; exit();
		$rssel	=	$Filstar_conn->Execute($sel);
		if ($rssel == false) 
		{
			echo $errmsg	=	($Filstar_pms->ErrorMsg()."::".__LINE__); 
			exit();
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
		$RSGETMTOMPOS	=	$Filstar_conn->Execute($GETMTOMPOS);
		if($RSGETMTOMPOS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
		$RSGETSCANNEDMPOS	=	$Filstar_conn->Execute($GETSCANNEDMPOS);
		if($RSGETSCANNEDMPOS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
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
		
		$SCANNEDQTY		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_DTL","SUM(POSTEDQTY)","SKUNO= '{$ITEMNO}'");
		$DEFECTIVEQTY	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_DTL","SUM(DEFECTIVEQTY)","SKUNO= '{$ITEMNO}'");
		$MTOQTY			=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTNDTL AS D LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.MTONO = D.MTONO","SUM(QTY)","SKUNO= '{$ITEMNO}' AND (STATUS = 'POSTED' OR H.STATUS='PRINTED')");
		
		$TDMTOQTY		=	$MTOQTY;
		$GOOD			=	$SCANNEDQTY - $DEFECTIVEQTY;
		$TDSCANNEDQTY	=	$SCANNEDQTY - $MTOQTY;
		$TDGOOD			=	$GOOD - $MTOQTY;
		echo "<script>
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
		$RSGETMPOSDTLS	=	$Filstar_conn->Execute($GETMPOSDTLS);
		if($RSGETMPOSDTLS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		else 
		{
			$table	=	"<table width='90%'>
							<tr class='dtlsheader'>
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
				$table	.=	"<tr class='dtlsdtls'>
								<td align='center'>$cnt</td>
								<td align='center'>$MPOSNO</td>
								<td align='center'>$POSTEDDATE</td>
								<td align='center'>$POSTEDQTY</td>
							 </tr>";
				$cnt++;
				$totqty +=	$POSTEDQTY;
				$RSGETMPOSDTLS->MoveNext();
			}
			
			$table	.=	"<tr class='dtlsheader'>
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
		
		$GETMPOSDTLS	=	"SELECT D.`MPOSNO`, SUM(D.`QTY`) AS `QTY`, H.POSTDATE 
							 FROM WMS_NEW.MTO_RTNDTL AS D 
							 LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.`MTONO` = D.`MTONO` 
							 WHERE D.MPOSNO IN  ($MPOSLIST) AND SKUNO = '$ITEMNO'
							 GROUP BY D.MPOSNO";
		$RSGETMPOSDTLS	=	$Filstar_conn->Execute($GETMPOSDTLS);
		if($RSGETMPOSDTLS == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		else 
		{
			$table	=	"<table width='90%'>
							<tr class='dtlsheader'>
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
				$table	.=	"<tr class='dtlsdtls'>
								<td align='center'>$cnt</td>
								<td align='center'>$MPOSNO</td>
								<td align='center'>$POSTEDDATE</td>
								<td align='center'>$POSTEDQTY</td>
							 </tr>";
				$cnt++;
				$totqty +=	$POSTEDQTY;
				$RSGETMPOSDTLS->MoveNext();
			}
			
			$table	.=	"<tr class='dtlsheader'>
							<td align='center' colspan='3'>TOTAL</td>
							<td align='center'>$totqty</td>
						 </tr>
					</table><br>";
//			$table	.=	"</table>";
			echo $table;
		}
		exit();
	}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/inventory/persku/inventory_sku.html");
?>