<?php
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
//$getmtohdr		=	"SELECT * FROM WMS_NEW.MTO_RTN_EXCHDR WHERE MTONO IN ('XRTN-20160708-001')";
//$getmtohdr		=	"SELECT * FROM WMS_NEW.MTO_RTN_EXCHDR WHERE MTONO IN ('XRTN-20160711-001')";
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
//			echo "$MTONO<br>";
//			exit();
//			$DESTINATION 	= $RSgetmtohdr->fields["DESTINATION"]; 
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
				echo "$INSERTMTO_10<br>";
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
						echo "$INSERTMTOdtls_10<br>";
					$RSgetmtodtls->MoveNext();
					}
					echo "<br>";
				}
			}
		}
	$RSgetmtohdr->MoveNext();
	}
}
?>