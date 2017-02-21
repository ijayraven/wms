<?php
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
//$GETPCWRKRDTLS	=	"SELECT `MTONO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, SUM(`QTY`) AS QTY, SUM(`RECQTY`) AS RECQTY, SUM(`GOODQTY`) AS GOODQTY, SUM(`DEFQTY`) AS DEFQTY,
//					`NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`, SUM(`GROSSAMT`) AS GROSSAMT, `UPDATED_BY`, `UPDATED_DT` 
//					 FROM WMS_NEW.MTO_PCWDTL2
//					 GROUP BY `MTONO`,`SKUNO`";
//$RSGETPCWRKRDTLS	=	$Filstar_conn->Execute($GETPCWRKRDTLS);
//if($RSGETPCWRKRDTLS == false)
//{
//	echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
//	exit();
//}
//else 
//{
//	while (!$RSGETPCWRKRDTLS->EOF)
//	{
//		$MTONO 			= $RSGETPCWRKRDTLS->fields["MTONO"]; 
//		$SKUNO 			= $RSGETPCWRKRDTLS->fields["SKUNO"]; 
//		$DESCRIPTION 	= addslashes($RSGETPCWRKRDTLS->fields["DESCRIPTION"]); 
//		$ITEMSTATUS 	= $RSGETPCWRKRDTLS->fields["ITEMSTATUS"]; 
//		$QTY 			= $RSGETPCWRKRDTLS->fields["QTY"]; 
//		$RECQTY 		= $RSGETPCWRKRDTLS->fields["RECQTY"]; 
//		$GOODQTY 		= $RSGETPCWRKRDTLS->fields["GOODQTY"]; 
//		$DEFQTY 		= $RSGETPCWRKRDTLS->fields["DEFQTY"]; 
//		$NO_OF_BOXES 	= $RSGETPCWRKRDTLS->fields["NO_OF_BOXES"]; 
//		$NO_OF_PACK 	= $RSGETPCWRKRDTLS->fields["NO_OF_PACK"]; 
//		$BOXLABEL 		= $RSGETPCWRKRDTLS->fields["BOXLABEL"]; 
//		$UNITPRICE 		= $RSGETPCWRKRDTLS->fields["UNITPRICE"]; 
//		$GROSSAMT 		= $RSGETPCWRKRDTLS->fields["GROSSAMT"]; 
//		$UPDATED_BY 	= $RSGETPCWRKRDTLS->fields["UPDATED_BY"]; 
//		$UPDATED_DT		= $RSGETPCWRKRDTLS->fields["UPDATED_DT"]; 
//		
//		$INSERTPCWRKRDTLS	=	"INSERT INTO WMS_NEW.MTO_PCWDTL VALUES('$MTONO','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$QTY','$RECQTY','$GOODQTY','$DEFQTY','$NO_OF_BOXES',
//								 '$NO_OF_PACK','$BOXLABEL','$UNITPRICE','$GROSSAMT','$UPDATED_BY','$UPDATED_DT')";
//		echo "$INSERTPCWRKRDTLS <br>";
//		$RSINSERTPCWRKRDTLS	=	$Filstar_conn->Execute($INSERTPCWRKRDTLS);
//		if($RSINSERTPCWRKRDTLS == false)
//		{
//			echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
//			exit();
//		}
//		$RSGETPCWRKRDTLS->MoveNext();
//	}
//}
?>