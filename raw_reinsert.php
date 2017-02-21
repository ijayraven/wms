<?php
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
	$TRXNO	=	"RTN-20161110-003";
	$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_RTNDTL WHERE MTONO = '$TRXNO'";
	$RSGETDTLS	=	$Filstar_conn->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		while (!$RSGETDTLS->EOF) {
			$MPOSNO			= 	$RSGETDTLS->fields["MPOSNO"]; 
			$SKUNO			= 	$RSGETDTLS->fields["SKUNO"]; 
			$DESCRIPTION	= 	addslashes($RSGETDTLS->fields["DESCRIPTION"]); 
			$ITEMSTATUS		= 	$RSGETDTLS->fields["ITEMSTATUS"]; 
			$QTY			= 	$RSGETDTLS->fields["QTY"]; 
			$NO_OF_BOXES	= 	$RSGETDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK		= 	$RSGETDTLS->fields["NO_OF_PACK"]; 
			$BOXLABEL		= 	$RSGETDTLS->fields["BOXLABEL"]; 
			$UNITPRICE		= 	$RSGETDTLS->fields["UNITPRICE"]; 
			$GROSSAMT		= 	$RSGETDTLS->fields["GROSSAMT"]; 
			
			$founditem		= $global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RAWDTL","SKUNO","SKUNO= '$SKUNO' AND MPOSNO = '$MPOSNO'");
			if($founditem == "")
			{
				$X++;
				echo "<br>$X.) $MPOSNO-$SKUNO<BR>";
				echo $insertToRawDtls	=	"INSERT INTO WMS_NEW.MTO_RAWDTL(`MTONO`, `MPOSNO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`, `NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`, `GROSSAMT`)
										 VALUES('$TRXNO','$MPOSNO','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$QTY','$NO_OF_BOXES','$NO_OF_PACK','$BOXLABEL','$UNITPRICE','$GROSSAMT')";
				$RSinsertToRawDtls	=	$Filstar_conn->Execute($insertToRawDtls);
				if($RSinsertToRawDtls == false)
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
				}
			}
		$RSGETDTLS->MoveNext();
		}
	}
?>