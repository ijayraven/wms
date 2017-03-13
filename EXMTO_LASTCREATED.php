<?php 
include($_SERVER['DOCUMENT_ROOT']."/public_php/adodb/adodb.inc.php");
include($_SERVER['DOCUMENT_ROOT']."/public_php/class_db.php");
include($_SERVER['DOCUMENT_ROOT']."/public_js/jsUI.php");
	$conn_255_10	=	newADOConnection("mysqlt");
	$RSconn_255_10	=	$conn_255_10->Connect("192.168.255.10","root","");
//	$RSconn_255_10	=	$conn_255_10->Connect("localhost","root","");
	if($RSconn_255_10 == false)
	{
		echo "Unable to connect to server."; exit();
	}
	else 
	{
//		$GETMTOITEMS	=	"SELECT `ITEMNO` FROM WMS_LOOKUP.MTO_EX_ITEMS_DTLS WHERE `CANCELLED` != 'Y' ";

		$RSGETMTOITEMS	=	$conn_255_10->Execute($GETMTOITEMS);
		if($RSGETMTOITEMS == false)
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$GETMTOITEMS,$_SESSION['username'],"EXMTO_CHECK","EXMTO_CHECK");
			$DATASOURCE->displayError();
		}
		else 
		{
			 echo "<table border='1'>
						<tr align='center'>
							<TD>NO.</TD>
							<TD>ITEM NO</TD>
							<TD>ITEM MTO STATUS</TD>
							<TD>EXCLUSIVE MIN MTO NO.</TD>
							<TD>EXCLUSIVE MTO MIN ADDED DATE</TD>
							<TD>EXCLUSIVE MAXMTO NO.</TD>
							<TD>EXCLUSIVE MTO MAX ADDED DATE</TD>
							<TD>REGULAR MIN MTO NO.</TD>
							<TD>REGULAR MTO MIN POSTED DATE</TD>
							<TD>REGULAR MAX MTO NO.</TD>
							<TD>REGULAR MTO MAX POSTED DATE</TD>
						</tr>";
			while(!$RSGETMTOITEMS->EOF)
			{
				$ITEMNO	=	$RSGETMTOITEMS->fields["ITEMNO"];
				$MAX_MTONO		=	$DATASOURCE->selvalqry($conn_255_10,"SELECT MAX( D.`MTONO` ) AS MTONO
																			FROM  WMS_NEW.`MTO_RTN_EXCDTL` AS D
																			LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.`MTONO` = D.`MTONO` 
																			WHERE  `SKUNO` =  '$ITEMNO'
																			AND H.STATUS !=  'CANCELLED'", 
																			"MTONO");
				if($MAX_MTONO != "")
				{
					
//					$GETSCANNED		=	"SELECT SCANDATA_DTL.`SKUNO`, CONCAT(SCANDATA_HDR.POSTEDDATE,' ',SCANDATA_HDR.POSTEDTIME) AS SCANDATE
//										 FROM WMS_NEW.SCANDATA_DTL 
//										 LEFT JOIN WMS_NEW.SCANDATA_HDR ON SCANDATA_HDR.MPOSNO = SCANDATA_DTL.MPOSNO
//										 WHERE `SKUNO` = '$ITEMNO' AND ITEMSTATUS != 'P' AND SCANDATA_DTL.STATUS = 'POSTED'
//										 ORDER BY SCANDATA_HDR.POSTEDDATE,SCANDATA_HDR.POSTEDTIME";
//					$RSGETSCANNED	=	$conn_255_10->Execute($GETSCANNED);
//					if($RSGETSCANNED == false)
//					{
//						$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
//						$DATASOURCE->logError("wms",$errmsg,$GETSCANNED,$_SESSION['username'],"EXMTO_CHECK","EXMTO_CHECK");
//						$DATASOURCE->displayError();
//					}
//					else 
//					{
//						while (!$RSGETSCANNED->EOF) {
//							$SKUNO		=	$RSGETSCANNED->fields["SKUNO"];
//							$SCANDATE	=	$RSGETSCANNED->fields["SCANDATE"];	
//							$RSGETSCANNED->MoveNext();
//						}
//					}
					$MAX_ADDDATE	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT CONCAT(H.ADDDATE,' ',H.ADDTIME) AS MAXDATE
																				FROM  WMS_NEW.`MTO_RTN_EXCDTL` AS D
																				LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.`MTONO` = D.`MTONO` 
																				WHERE D.SKUNO =  '$ITEMNO'
																				AND H.STATUS !=  'CANCELLED' AND D.`MTONO` = '$MAX_MTONO'", 
																				"MAXDATE");
					$MIN_MTONO		=	$DATASOURCE->selvalqry($conn_255_10,"SELECT MIN( D.`MTONO` ) AS MTONO
																				FROM  WMS_NEW.`MTO_RTN_EXCDTL` AS D
																				LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.`MTONO` = D.`MTONO` 
																				WHERE  `SKUNO` =  '$ITEMNO'
																				AND H.STATUS !=  'CANCELLED'", 
																				"MTONO");
					$MIN_ADDDATE	=	$DATASOURCE->selvalqry($conn_255_10,"SELECT CONCAT(H.ADDDATE,' ',H.ADDTIME) AS MINDATE
																				FROM  WMS_NEW.`MTO_RTN_EXCDTL` AS D
																				LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.`MTONO` = D.`MTONO` 
																				WHERE D.SKUNO =  '$ITEMNO'
																				AND H.STATUS !=  'CANCELLED' AND D.`MTONO` = '$MIN_MTONO'", 
																				"MINDATE");
																				
					$REGMTO			=	$DATASOURCE->selvalqry($conn_255_10,"SELECT D.`MTONO`
																				FROM  WMS_NEW.`MTO_RTNDTL` AS D
																				LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.`MTONO` = D.`MTONO` 
																				WHERE D.SKUNO =  '$ITEMNO'
																				AND H.STATUS !=  'CANCELLED'", 
																				"MTONO");
					$STATUS			=	"EXCLUSIVE MTO CREATED.";
					$REGMAX_MTONO	=	"";
					$REGMAX_ADDDATE	=	"";
					$REGMIN_MTONO	=	"";
					$REGMIN_ADDDATE	=	"";
					if($REGMTO != "")
					{
//						$STATUS				=	"EXCLUSIVE MTO CREATED with Regular MTO.";
//						$REGMAX_MTONO		=	$DATASOURCE->selvalqry($conn_255_10,"SELECT MAX( D.`MTONO` ) AS MTONO
//																				FROM  WMS_NEW.`MTO_RTNDTL` AS D
//																				LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.`MTONO` = D.`MTONO` 
//																				WHERE  `SKUNO` =  '$ITEMNO'
//																				AND H.STATUS !=  'CANCELLED'", 
//																				"MTONO");
//						$REGMAX_ADDDATE		=	$DATASOURCE->selvalqry($conn_255_10,"SELECT CONCAT(H.ADDDATE,' ',H.ADDTIME) AS MAXDATE
//																				FROM  WMS_NEW.`MTO_RTNDTL` AS D
//																				LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.`MTONO` = D.`MTONO` 
//																				WHERE D.SKUNO =  '$ITEMNO'
//																				AND H.STATUS !=  'CANCELLED' AND D.`MTONO` = '$REGMAX_MTONO'", 
//																				"MAXDATE");
//						$REGMIN_MTONO		=	$DATASOURCE->selvalqry($conn_255_10,"SELECT MIN( D.`MTONO` ) AS MTONO
//																				FROM  WMS_NEW.`MTO_RTNDTL` AS D
//																				LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.`MTONO` = D.`MTONO` 
//																				WHERE  `SKUNO` =  '$ITEMNO'
//																				AND H.STATUS !=  'CANCELLED'", 
//																				"MTONO");
//						$REGMIN_ADDDATE		=	$DATASOURCE->selvalqry($conn_255_10,"SELECT CONCAT(H.ADDDATE,' ',H.ADDTIME) AS MINDATE
//																				FROM  WMS_NEW.`MTO_RTNDTL` AS D
//																				LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.`MTONO` = D.`MTONO` 
//																				WHERE D.SKUNO =  '$ITEMNO'
//																				AND H.STATUS !=  'CANCELLED' AND D.`MTONO` = '$REGMIN_MTONO'", 
//																				"MINDATE");
//						if($REGMAX_ADDDATE < $MIN_ADDDATE)
//						{
//							$cnt++;
//							echo "<tr  align='center'>
//									<td>$cnt</td>
//									<td>$ITEMNO</td>
//									<td>$STATUS</td>
//									<TD>$MIN_MTONO</TD>
//									<TD>$MIN_ADDDATE</TD>
//									<TD>$MAX_MTONO</TD>
//									<TD>$MAX_ADDDATE</TD>
//									<td>$REGMIN_MTONO</td>
//									<td>$REGMIN_ADDDATE</td>
//									<td>$REGMAX_MTONO</td>
//									<td>$REGMAX_ADDDATE</td>
//								</tr>";
							
//						echo "<br>".$UPDATE_SCANDATADTL	=	"UPDATE WMS_NEW.SCANDATA_DTL AS D LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.`MPOSNO` = D.`MPOSNO`
//												 SET D.`MTOEXCREATED` = 'Y'
//												 WHERE `SKUNO` = '$ITEMNO' AND ITEMSTATUS != 'P' AND D.STATUS = 'POSTED' AND
//												 CONCAT(H.POSTEDDATE,' ',H.POSTEDTIME) < '$MAX_ADDDATE'";
//						$RSUPDATE_SCANDATADTL=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATE_SCANDATADTL,$user,"EXMTO_CHECK","EXMTO_CHECK");
//						}
					}
					else 
					{
						$UPDATE_SCANDATADTL	=	"UPDATE WMS_NEW.SCANDATA_DTL AS D LEFT JOIN WMS_NEW.SCANDATA_HDR AS H ON H.`MPOSNO` = D.`MPOSNO`
												 SET D.`MTOEXCREATED` = 'Y'
												 WHERE `SKUNO` = '$ITEMNO' AND ITEMSTATUS != 'P' AND D.STATUS = 'POSTED' AND
												 CONCAT(H.POSTEDDATE,' ',H.POSTEDTIME) < '$MAX_ADDDATE'";
						$RSUPDATE_SCANDATADTL=	$DATASOURCE->execQUERY("wms",$conn_255_10,$UPDATE_SCANDATADTL,$user,"EXMTO_CHECK","EXMTO_CHECK");
					}
				}
				$RSGETMTOITEMS->MoveNext();
			}
		echo "</table><br>";
		}
	}
?>
<style>
* {
	font-size:12px;
}
</style>