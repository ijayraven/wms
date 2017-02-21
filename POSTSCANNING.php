<?php
include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");

$conn_255_10->StartTrans();
//
//$GETMPOS	=	"SELECT H.* FROM WMS_NEW.SCANDATA_HDR AS H
//				 LEFT JOIN WMS_NEW.SCANDATA_DTL AS D ON D.MPOSNO = H.MPOSNO
//				 WHERE H.POSTEDBY != '' AND D.ADDTL = 'Y'
//				 GROUP BY H.MPOSNO";
$GETMPOS	=	"SELECT MPOSNO FROM WMS_NEW.SCANDATA_HDR WHERE POSTEDNETAMOUNT = 0";
$RSGETMPOS	=	$conn_255_10->Execute($GETMPOS);
if($RSGETMPOS == false)
{
	echo $errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
	$DATASOURCE->logError("wms",$errmsg,$RSGETMPOS,$_SESSION['username'],"MPOS SCANNING","POSTSCANNING");
	$DATASOURCE->displayError();
}
else 
{
	while (!$RSGETMPOS->EOF) {
		$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];

		$BY 		=	$_SESSION['username'];
		$DATE 		=	date("Y-m-d");
		$TIME		=	date("h:i:s");
		$TOTALQTY	=	0;
		$TOTALAMT	=	0;
		$TOTALNETAMT=	0;
				
		$GETSCANNED	=	"SELECT `MPOSNO`,`CUSTCODE`, `SKUNO`, `SCANNEDQTY`, `UPDATEQTY`,`STATUS` FROM WMS_NEW.SCANDATA_DTL
						 WHERE MPOSNO = '{$MPOSNO}' AND STATUS != 'DELETED' AND DELBY = ''";
		$RSGETSCANNED	=	$conn_255_10->Execute($GETSCANNED);
		if($RSGETSCANNED == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETSCANNED,$_SESSION['username'],"MPOS SCANNING","POSTSCANNING");
			$DATASOURCE->displayError();
		}
		else 
		{
			while (!$RSGETSCANNED->EOF) {
				$MPOSNO		=	$RSGETSCANNED->fields["MPOSNO"];
				$SKUNO		=	$RSGETSCANNED->fields["SKUNO"];
				$SCANNEDQTY	=	$RSGETSCANNED->fields["SCANNEDQTY"];
				$UPDATEQTY	=	$RSGETSCANNED->fields["UPDATEQTY"];
				$CUSTCODE	=	$RSGETSCANNED->fields["CUSTCODE"];
				$STATUS		=	$RSGETSCANNED->fields["STATUS"];
				$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSDTL","UNITPRICE","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$PRICECLASS	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","MPOSDTL","PRICECLASS","SKUNO = '{$SKUNO}' AND MPOSNO = '{$MPOSNO}'");
				$CustPriceBook	=	$DATASOURCE->selval($conn_255_10," FDCRMSlive ","custmast","CustPriceBook","CustNo = '{$CUSTCODE}'");
				$custdiscount	=	$DATASOURCE->selval($conn_255_10," FDCRMSlive ","custdiscount","Discount","PriceBook = '{$CustPriceBook}' AND PriceClass = '{$PRICECLASS}'");
				$F_QTY			=	$SCANNEDQTY; 
				if($UNITPRICE == '')
				{
					$UNITPRICE	=	$DATASOURCE->selval($conn_255_10,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
				}
				$TOTALQTY	+=	$F_QTY;
				$TOTALAMT	+=	($F_QTY * $UNITPRICE);
				$TOTALNETAMT+=	($F_QTY * ($UNITPRICE-($UNITPRICE*($custdiscount/100))));
				
				$RSGETSCANNED->MoveNext();
			}
		}
		$POSTEDGROSSAMOUNT	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW ","SCANDATA_HDR","POSTEDGROSSAMOUNT","MPOSNO = '{$MPOSNO}'");
		if($TOTALAMT != $POSTEDGROSSAMOUNT)
		{
			echo "$MPOSNO--->$TOTALAMT != $POSTEDGROSSAMOUNT<br>";
			$POSTSCANHDR	=	"UPDATE  WMS_NEW.SCANDATA_HDR SET `POSTEDNETAMOUNT`='{$TOTALNETAMT}', `POSTEDGROSSAMOUNT`='{$TOTALAMT}'
								 WHERE MPOSNO = '{$MPOSNO}'";
			$RSPOSTSCANHDR	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$POSTSCANHDR,$user,"MPOS SCANNING","POSTSCANNING");
		}	
		$RSGETMPOS->MoveNext();
	}
	$conn_255_10->CompleteTrans();
}
?>