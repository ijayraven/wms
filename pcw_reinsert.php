<?php
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
	
	$MTONO	=	"RTN-20161110-003";
	$GETDTLS	=	"SELECT SKUNO,DESCRIPTION,ITEMSTATUS,SUM(QTY) AS QTY,NO_OF_BOXES,NO_OF_PACK,BOXLABEL,UNITPRICE FROM WMS_NEW.MTO_RAWDTL
					 WHERE MTONO = '$MTONO' GROUP BY SKUNO";
	$RSGETDTLS	=	$Filstar_conn->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		$errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETDTLS,$_SESSION['username'],"RAW ISSUANCE-PIECEWORKER","ISSUEMTO");
		$DATASOURCE->displayError();
	}
	else 
	{
		while (!$RSGETDTLS->EOF) {
			$SKUNO 			= $RSGETDTLS->fields["SKUNO"]; 
			$DESCRIPTION 	= addslashes($RSGETDTLS->fields["DESCRIPTION"]); 
			$ITEMSTATUS 	= $RSGETDTLS->fields["ITEMSTATUS"]; 
			$QTY 			= $RSGETDTLS->fields["QTY"]; 
			$NO_OF_BOXES 	= $RSGETDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK 	= $RSGETDTLS->fields["NO_OF_PACK"]; 
			$BOXLABEL 		= $RSGETDTLS->fields["BOXLABEL"]; 
			$UNITPRICE 		= $RSGETDTLS->fields["UNITPRICE"]; 
			if($QTY != 0)
			{ 
				$X++;
				$founditem		= $global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_PCWDTL","SKUNO","SKUNO= '$SKUNO' AND MTONO = '$MTONO'");
				if($founditem != "")
				{
					echo "<BR>$X.)".$INSERTTOPCWDTLS	=	"UPDATE WMS_NEW.MTO_PCWDTL SET `QTY` = '$QTY' WHERE SKUNO= '$SKUNO' AND MTONO = '$MTONO'";
				}
				else 
				{
					echo "<BR>$X.)".$INSERTTOPCWDTLS	=	"INSERT INTO WMS_NEW.MTO_PCWDTL(`MTONO`,`SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`, `NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`)
											 VALUES('$MTONO','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$QTY','$NO_OF_BOXES','$NO_OF_PACK','$BOXLABEL','$UNITPRICE')";
				}
					
				$RSINSERTTOPCWDTLS	=	$Filstar_conn->Execute($INSERTTOPCWDTLS);
				if($RSINSERTTOPCWDTLS == false)
				{
					$Filstar_conn->ErrorMsg."::".__LINE__; exit();
				}
			}
			$RSGETDTLS->MoveNext();
		}
	}
?>