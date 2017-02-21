<?php
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
$GETITEMS	=	"SELECT  `MPOSNO` ,  `CUSTCODE` ,  `SKUNO` ,  `ITEMSTATUS` , `SCANNEDQTY`, `POSTEDQTY`,  `SAVEDDATE` 
				 FROM  WMS_NEW.`SCANDATA_DTL` 
				 WHERE  `ITEMSTATUS` =  'P'
				 ORDER BY `SAVEDDATE`";
$RSGETITEMS	=	$Filstar_conn->Execute($GETITEMS);
if($RSGETITEMS == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
}
else 
{
	$arrItems	=	array();
	while (!$RSGETITEMS->EOF) {
		
		$MPOSNO		= $RSGETITEMS->fields["MPOSNO"];  
		$SKUNO 		= $RSGETITEMS->fields["SKUNO"];  
		$DESC		= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
		$PRODCOST	= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ProductCost","ItemNo = '{$SKUNO}'");
		$SRP		= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
		$POSTEDQTY 	= $RSGETITEMS->fields["POSTEDQTY"];
		$SAVEDDAT 	= $global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_HDR","SCANDATE","MPOSNO = '{$MPOSNO}'");
		$MONTH		= date("Y-m",strtotime($SAVEDDAT));
		$arrItems[$MONTH][$SAVEDDAT][$SKUNO]["DESC"]	=	$DESC;
		$arrItems[$MONTH][$SAVEDDAT][$SKUNO]["QTY"]		+=	$POSTEDQTY;
		$arrItems[$MONTH][$SAVEDDAT][$SKUNO]["NET"]		+=	$POSTEDQTY * $PRODCOST;
		$arrItems[$MONTH][$SAVEDDAT][$SKUNO]["GROSS"]	+=	$POSTEDQTY * $SRP;
		$RSGETITEMS->MoveNext();
	}
	ksort($arrItems);
	
	$Gtotqty		=	0;
	$Gtotnet		=	0;
	$Gtotgross		=	0;
	foreach ($arrItems as $month=>$val)
	{
		$mtotqty		=	0;
		$mtotnet		=	0;
		$mtotgross		=	0;
		ksort($val);
		
		$MONTHNAME	=	date("F,Y",strtotime($month));
		$output	.=	"MONTH : $MONTHNAME\r";
		foreach ($val as $date=>$val1){
			$totqty		=	0;
			$totnet		=	0;
			$totgross	=	0;
			
			$output	.=	";DATE : $date\r";
			$output	.=	";;ITEM NO.; DESCRIPTION; QTY; NET; GROSS \r";
			foreach ($val1 as $item=>$val2){
				$desc 	=	$val2["DESC"];
				$qty 	=	$val2["QTY"];
				$net 	=	$val2["NET"];
				$gross 	=	$val2["GROSS"];
				$totqty		+=	$qty;
				$totnet		+=	$net;
				$totgross	+=	$gross;
				
				$mtotqty		+=	$qty;
				$mtotnet		+=	$net;
				$mtotgross		+=	$gross;
				
				$Gtotqty		+=	$qty;
				$Gtotnet		+=	$net;
				$Gtotgross		+=	$gross;
				
				$output	.=	";;'$item; $desc; $qty; $net; $gross \r";
			}
			$output	.=	";TOTAL;;; $totqty; $totnet; $totgross \r";
		}
		$MONTHNAME	=	date("F,Y",strtotime($month));
		$output	.=	"MONTHLY TOTAL($MONTHNAME);;;; $mtotqty; $mtotnet; $mtotgross \r";
		$output .=	"\r";
	}
		$output	.=	";;;GRAND TOTAL; $Gtotqty; $Gtotnet; $Gtotgross \r";
	
	header("Content-Disposition: attachment; filename=PrimeItems.csv");
	header("Content-Location: $_SERVER[REQUEST_URI]");
	header("Content-Type: text/plain");
	header("Expires: 0");
	echo $output;
}
?>