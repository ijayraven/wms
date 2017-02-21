<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}
$action	=	$_GET['action'];
if($action == "GETMPOS")
{
	$MPOSNO	=	$_GET["MPOSNO"];
	$curcnt	=	$_GET["CURCNT"];
	$CUSTNO_MTO	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTNDTL","MPOSNO","MPOSNO= '{$MPOSNO}'");
	if($CUSTNO_MTO == "")
	{
		$CUSTNO		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
		if($CUSTNO != "")
		{
			$CUSTDESC	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo= '{$CUSTNO}'");
			echo "<script>
						$('#tdcustomer$curcnt').text('$CUSTDESC-$CUSTNO');
						$('#tdcustomer$curcnt').removeClass('errnotfound');
				  </script>";
		}
		else 
		{
			echo "<script>
						$('#tdcustomer$curcnt').text('MPOS not found');
						$('#tdcustomer$curcnt').addClass('errnotfound');
						$('#txtmposno$curcnt').val('');
				  </script>";
		}
	}
	else 
	{
		echo "<script>
					$('#tdcustomer$curcnt').text('MPOS already exists.');
					$('#tdcustomer$curcnt').addClass('errnotfound');
					$('#txtmposno$curcnt').val('');
			  </script>";
	}
	exit();
}
if($action == "SAVETRX")
{
	$TXNO	=	newTRXno($Filstar_conn);
	$cnt	=	$_POST["hidcnt"];
	$rdodestination_C = $_POST["rdodestination_C"];
	$TODAY	=	date("Y-m-d");
	$time	=	date("H:i:s A");
	$Filstar_conn->StartTrans();
	$SAVEMTOTRX	=	"INSERT INTO WMS_NEW.MTO_RTNHDR(`MTONO`, `STATUS`, `DESTINATION`, `ADDBY`, `ADDDATE`, `ADDTIME`)
					 VALUES('{$TXNO}','SAVED','{$rdodestination_C}','{$_SESSION['username']}','{$TODAY}','{$time}')";
	$RSSAVEMTOTRX = $Filstar_conn->Execute($SAVEMTOTRX);
	if($RSSAVEMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		for($a = 1; $a <= $cnt; $a++)
		{
			$txtmposno		=	$_POST["txtmposno$a"];
			$txtnoboxes		=	$_POST["txtnoboxes$a"];
			$txtnopackages	=	$_POST["txtnopackages$a"];
			
			$GETMPOS	=	"SELECT `SKUNO`, `ITEMTYPE`, `UNITPRICE`, `QTY`, `GROSSAMOUNT` FROM WMS_NEW.MPOSDTL 
							 WHERE MPOSNO = '{$txtmposno}'";
			$RSGETMPOS	=	$Filstar_conn->Execute($GETMPOS);
			if($RSGETMPOS == false)
			{
				$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
			}
			else 
			{
				while (!$RSGETMPOS->EOF) {
					$SKUNO 		= $RSGETMPOS->fields["SKUNO"]; 
					$GETDESC	= addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo= '{$SKUNO}'"));
					$ITEMTYPE 	= $RSGETMPOS->fields["ITEMTYPE"]; 
					$UNITPRICE 	= $RSGETMPOS->fields["UNITPRICE"]; 
					$QTY 		= $RSGETMPOS->fields["QTY"]; 
					$GROSSAMOUNT= $RSGETMPOS->fields["GROSSAMOUNT"];
					$EXMTOITEM	= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","ITEMNO","ITEMNO= '{$SKUNO}'");
					if($EXMTOITEM != "")
					{
						$TRX_NO		= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","TRX_NO","ITEMNO= '{$SKUNO}'");
						$STATUS		= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_HDR","STATUS","TRX_NO= '{$TRX_NO}'");
						if($STATUS == "POSTED")
						{
							$NONMTOITEM = true;
						}
						else 
						{
							$NONMTOITEM = false;
						}
					}
					else 
					{
						$NONMTOITEM = false;
					}
					if($ITEMTYPE != "P" and $NONMTOITEM == false)
					{
						$SAVEMTODTLS	=	"INSERT INTO WMS_NEW.MTO_RTNDTL(`MTONO`, `MPOSNO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`,`NO_OF_BOXES`, `NO_OF_PACK`, `UNITPRICE`, `GROSSAMT`)
											 VALUES('{$TXNO}','{$txtmposno}','{$SKUNO}','{$GETDESC}','{$ITEMTYPE}','{$QTY}','{$txtnoboxes}','{$txtnopackages}','{$UNITPRICE}','{$GROSSAMOUNT}')";
						$RSSAVEMTODTLS	=	$Filstar_conn->Execute($SAVEMTODTLS);
						if($RSSAVEMTODTLS == false)
						{
							echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
						}
					}
				$RSGETMPOS->MoveNext();
				}
			}
		}
	}
	$Filstar_conn->CompleteTrans();
	echo "<script>
			alert('Transaction $TXNO has been successfully saved.');
			location.reload();
		  </script>";
	exit();
}
if($action == "GETMTO")
{
	$txtmtono 			= $_POST["txtmtono"];
	$selstatus 			= $_POST["selstatus"];
	$seldtype 			= $_POST["seldtype"];
	$mtodfrom 			= $_POST["mtodfrom"];
	$mtodto 			= $_POST["mtodto"];
	$rdodestination		= $_POST["rdodestination"];
	$tfrom				= date("00:00:00");
	$tto				= date("23:59:59");
	if($txtmtono != "")
	{
		$txtmtono_Q	=	" AND MTONO = '{$txtmtono}'";
	}
	if($selstatus != "")
	{
		$selstatus_Q	=	" AND STATUS = '{$selstatus}'";
	}
	if($mtodfrom != "")
	{
		$DATE_Q	=	" AND $seldtype BETWEEN '$mtodfrom $tfrom' AND '$mtodto $tto'";
	}
	if($rdodestination != "")
	{
		$rdodestination_Q	=	" AND DESTINATION = '{$rdodestination}'";
	}
	$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_RTNHDR
				 WHERE 1 $txtmtono_Q $selstatus_Q $DATE_Q $rdodestination_Q";
	$RSGETMTO	=	$Filstar_conn->Execute($GETMTO);
	if($RSGETMTO == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		if($RSGETMTO->RecordCount() > 0)
		{
			echo "<table border='0' cellpadding='1' cellspacing='1' width='100%' align='center' height='23px' >
					<tr align='center' background='/wms/images/images/pmscellpic3.gif'>
				 		<td class='text_white10' height='30px'>Line No.</td>
				 		<td class='text_white10' height='30px'>MTO No.</td>
				 		<td class='text_white10' height='30px'>STATUS</td>
				 		<td class='text_white10' height='30px'>DESTINATION</td>
				 		<td class='text_white10' height='30px'>PIECEWORKER</td>
				 		<td class='text_white10'>Date Created</td>
				 		<td class='text_white10'>Created By</td>
				 		<td class='text_white10'>Updated Date</td>
				 		<td class='text_white10'>Updated By</td>
				 		<td class='text_white10'>Posted Date</td>
				 		<td class='text_white10'>Posted By</td>
				 		<td class='text_white10'>Printed Date</td>
				 		<td class='text_white10'>Printed By</td>
				 		<td class='text_white10'>Actions</td>
				 	</tr>";
			$cnt = 1;
			while (!$RSGETMTO->EOF) {
				$TRX_NO			= $RSGETMTO->fields["MTONO"]; 
				$STATUS 		= $RSGETMTO->fields["STATUS"]; 
				$DESTINATION 	= $RSGETMTO->fields["DESTINATION"]; 
				$PCWORKER 		= $RSGETMTO->fields["PCWORKER"]; 
				$ADDBY 			= $RSGETMTO->fields["ADDBY"]; 
				$ADDDATE		= $RSGETMTO->fields["ADDDATE"]; 
				$EDITBY 		= $RSGETMTO->fields["EDITBY"]; 
				$EDITDATE 		= $RSGETMTO->fields["EDITDATE"]; 
				$POSTBY 		= $RSGETMTO->fields["POSTBY"]; 
				$POSTDATE 		= $RSGETMTO->fields["POSTDATE"]; 
				$PRINTBY 		= $RSGETMTO->fields["PRINTBY"]; 
				$PRINTDATE		= $RSGETMTO->fields["PRINTDATE"]; 
				if($STATUS == "SAVED" OR $STATUS == "UPDATED")
				{
					$btnedit	=	"<img src='/wms/images/images/action_icon/edit-icon.gif' class='smallbtns editbtn' title='Edit Trx: $TRX_NO' data-trxno='$TRX_NO'>";
					$btnpost	=	"<img src='/wms/images/images/action_icon/post_mail_blue.png' class='smallbtns postbtn' title='Post Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btnedit	=	"";
					$btnpost	=	"";
				}
				if($STATUS == "POSTED")
				{
					$btnprint	=	"<img src='/wms/images/images/action_icon/print.png' class='smallbtns printbtn' title='Print Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btnprint 	=	"";
				}
				echo "<tr style='font-size:12px;' class='trdtls'  id='trdtls$cnt' title='Click to view details'>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$cnt</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$TRX_NO</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$STATUS</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$DESTINATION</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$PCWORKER</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$ADDDATE</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$ADDBY</td>	
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$EDITDATE</td>
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$EDITBY</td>		
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$POSTDATE</td>	
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$POSTBY</td>		
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$PRINTDATE</td>	
				 		<td align='center' class='tdtrxdtls'data-trxno='$TRX_NO' data-count='$cnt'>$PRINTBY</td>	
				 		<td align='center'>$btnedit $btnpost $btnprint</td>	
				 	</tr> 
				 	<tr>
					 		<td id='tdtrxdtls$cnt' colspan='15' class='tdtrxdtlsClass'></td>
					</tr>";
				$cnt++;
				$RSGETMTO->MoveNext();
			}
			echo "</table>";
			echo "<script>$('#hidcnt').val('$cnt');</script>";
		}
		else 
		{
			echo "<script>alert('No records found.');</script>";
		}
	}
	exit();
}
if($action == "EDITTRX")
{
	$TRXNO			=	$_GET["TRXNO"];
	$GETTRXDTLS		=	"SELECT * FROM  WMS_NEW.MTO_RTNDTL WHERE MTONO = '{$TRXNO}' GROUP BY MPOSNO";
	$RSGETTRXDTLS	=	$Filstar_conn->Execute($GETTRXDTLS);
	if($RSGETTRXDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		$cnt			=	1;
		$DESTINATION	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTNHDR","DESTINATION","MTONO= '{$TRXNO}'");
		echo "<script>
				$('input:radio[value=$DESTINATION]').attr('checked',true);
		 	  </script>";
		while (!$RSGETTRXDTLS->EOF) {
			$MPOSNO 		= 	$RSGETTRXDTLS->fields["MPOSNO"]; 
			$CUSTNO			=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
			$CUSTDESC		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo= '{$CUSTNO}'");
			$NO_OF_BOXES	= 	$RSGETTRXDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK		= 	$RSGETTRXDTLS->fields["NO_OF_PACK"]; 
			if($cnt > 1)
			{
				echo "<script>
						$('.addbtn').trigger('click');
				 	  </script>";
			}
			echo "<script>
						$('#txtmposno$cnt').val('$MPOSNO');
						$('#txtnoboxes$cnt').val('$NO_OF_BOXES');
						$('#txtnopackages$cnt').val('$NO_OF_PACK');
						$('#tdcurcnt$cnt').text('$cnt');
						$('#tdcustomer$cnt').text('$CUSTDESC-$CUSTNO');
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
	$rdodestination_C = $_POST["rdodestination_C"];
	$TODAY	=	date("Y-m-d");
	$time	=	date("H:i:s A");
	$Filstar_conn->StartTrans();
	$DELTRXDTLS	=	"DELETE FROM WMS_NEW.MTO_RTNDTL WHERE MTONO= '{$TRX_NO}'";
	$RSDELTRXDTLS	=	$Filstar_conn->Execute($DELTRXDTLS);
	if($RSDELTRXDTLS == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	$UPDATEMTOTRX	=	"UPDATE  WMS_NEW.MTO_RTNHDR SET `STATUS` = 'UPDATED',DESTINATION='{$rdodestination_C}',EDITBY = '{$_SESSION['username']}', `EDITDATE` ='{$TODAY}',EDITTIME='{$time}'
						 WHERE MTONO = '{$TRX_NO}'";
	$RSUPDATEMTOTRX	= 	$Filstar_conn->Execute($UPDATEMTOTRX);
	if($RSUPDATEMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		for($a = 1; $a <= $cnt; $a++)
		{
			$txtmposno		=	$_POST["txtmposno$a"];
			$txtnoboxes		=	$_POST["txtnoboxes$a"];
			$txtnopackages	=	$_POST["txtnopackages$a"];
			
			$GETMPOS	=	"SELECT `SKUNO`, `ITEMTYPE`, `UNITPRICE`, `QTY`, `GROSSAMOUNT` FROM WMS_NEW.MPOSDTL 
							 WHERE MPOSNO = '{$txtmposno}'";
			$RSGETMPOS	=	$Filstar_conn->Execute($GETMPOS);
			if($RSGETMPOS == false)
			{
				$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
			}
			else 
			{
				while (!$RSGETMPOS->EOF) {
					$SKUNO 		= $RSGETMPOS->fields["SKUNO"]; 
					$GETDESC	= addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo= '{$SKUNO}'"));
					$ITEMTYPE 	= $RSGETMPOS->fields["ITEMTYPE"]; 
					$UNITPRICE 	= $RSGETMPOS->fields["UNITPRICE"]; 
					$QTY 		= $RSGETMPOS->fields["QTY"]; 
					$GROSSAMOUNT= $RSGETMPOS->fields["GROSSAMOUNT"];
					$EXMTOITEM	= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","ITEMNO","ITEMNO= '{$SKUNO}'");
					if($EXMTOITEM != "")
					{
						$TRXNO		= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","TRX_NO","ITEMNO= '{$SKUNO}'");
						$STATUS		= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_HDR","STATUS","TRX_NO= '{$TRXNO}'");
						if($STATUS == "POSTED")
						{
							$NONMTOITEM = true;
						}
						else 
						{
							$NONMTOITEM = false;
						}
					}
					else 
					{
						$NONMTOITEM = false;
					}
					if($ITEMTYPE != "P" and $NONMTOITEM == false)
					{
						$SAVEMTODTLS	=	"INSERT INTO WMS_NEW.MTO_RTNDTL(`MTONO`, `MPOSNO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`,`NO_OF_BOXES`, `NO_OF_PACK`, `UNITPRICE`, `GROSSAMT`)
											 VALUES('{$TRX_NO}','{$txtmposno}','{$SKUNO}','{$GETDESC}','{$ITEMTYPE}','{$QTY}','{$txtnoboxes}','{$txtnopackages}','{$UNITPRICE}','{$GROSSAMOUNT}')";
						$RSSAVEMTODTLS	=	$Filstar_conn->Execute($SAVEMTODTLS);
						if($RSSAVEMTODTLS == false)
						{
							echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
						}
					}
				$RSGETMPOS->MoveNext();
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
	$TODAY	=	date("Y-m-d");
	$time	=	date("H:i:s A");
	
	$Filstar_conn->StartTrans();
	$POSTMTOTRX	=		"UPDATE WMS_NEW.MTO_RTNHDR SET `STATUS` = 'POSTED', `POSTBY` = '{$_SESSION['username']}', `POSTDATE` = '$TODAY',POSTTIME = '{$time}'
						 WHERE MTONO = '{$TRXNO}'";
	$RSPOSTMTOTRX	= 	$Filstar_conn->Execute($POSTMTOTRX);
	if($RSPOSTMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		echo "<script>alert('Transaction $TRXNO has been successfully posted.');location.reload();</script>";
	}
	$Filstar_conn->CompleteTrans();
	exit();
}
if($action == "VIEWTRXDTLS")
{
	$TRX_NO	=	$_GET["TRXNO"];
	$COUNT	=	$_GET["COUNT"];
	$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_RTNDTL WHERE MTONO = '{$TRX_NO}'";
	$RSGETDTLS	=	$Filstar_conn->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		echo "<table border='1' cellpadding='1' cellspacing='1' width='100%' align='center' height='23px' style='background-color:#9fbfdf;border-collapse:collapse;'>
					<tr align='center' background='/wms/images/images/pmscellpic3.gif'>
				 		<td class='text_white10' height='30px'>Line No.</td>
				 		<td class='text_white10'>MPOS NO.</td>
				 		<td class='text_white10'>SKUNO</td>
				 		<td class='text_white10'>DESCRIPTION</td>
				 		<td class='text_white10'>ITEM STATUS</td>
				 		<td class='text_white10'>QTY</td>
				 		<td class='text_white10'>REC. QTY</td>
				 		<td class='text_white10'>DEF. QTY</td>
				 		<td class='text_white10'>NO. OF BOXES</td>
				 		<td class='text_white10'>NO. OF PACK</td>
				 		<td class='text_white10'>UNIT PRICE</td>
				 		<td class='text_white10'>GROSS AMOUNT</td>
				 	</tr>";
		$cnt = 1;
		while (!$RSGETDTLS->EOF) {
			$MPOSNO			= $RSGETDTLS->fields["MPOSNO"]; 
			$SKUNO			= $RSGETDTLS->fields["SKUNO"]; 
			$DESCRIPTION	= $RSGETDTLS->fields["DESCRIPTION"]; 
			$ITEMSTATUS		= $RSGETDTLS->fields["ITEMSTATUS"]; 
			$QTY			= $RSGETDTLS->fields["QTY"]; 
			$RECQTY			= $RSGETDTLS->fields["RECQTY"]; 
			$DEFQTY			= $RSGETDTLS->fields["DEFQTY"]; 
			$NO_OF_BOXES	= $RSGETDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK		= $RSGETDTLS->fields["NO_OF_PACK"]; 
			$UNITPRICE		= $RSGETDTLS->fields["UNITPRICE"]; 
			$GROSSAMT		= $RSGETDTLS->fields["GROSSAMT"]; 
			
			echo "<tr style='font-size:12px;' class='trdtls'>
				 		<td align='center'>$cnt</td>
				 		<td align='center'>$MPOSNO</td>
				 		<td align='center'>$SKUNO</td>
				 		<td>$DESCRIPTION</td>
				 		<td align='center'>$ITEMSTATUS</td>
				 		<td align='center'>$QTY</td>
				 		<td align='center'>$RECQTY</td>
				 		<td align='center'>$DEFQTY</td>
				 		<td align='center'>$NO_OF_BOXES</td>
				 		<td align='center'>$NO_OF_PACK</td>
				 		<td align='right'>$UNITPRICE</td>
				 		<td align='right'>".number_format($GROSSAMT,2)."</td>
			 	  </tr>";
			$cnt++;
			$RSGETDTLS->MoveNext();
		}
		echo "</table>";
	}
	exit();
}
function  newTRXno($dbconn)
{
	$forTRXno		=	"SELECT	MTONO,ADDDATE FROM  WMS_NEW.MTO_RTNHDR order by LINE_NO";
	$rsforTRXno		=	$dbconn->Execute($forTRXno);
	if ($rsforTRXno == false) 
	{
		echo $errmsg	=	$conn->ErrorMsg()."::".__LINE__; 
		exit();
	}
	while (!$rsforTRXno->EOF) 
	{
		$date1		=	date('Y-m-d', strtotime($rsforTRXno->fields['ADDDATE']));	
		$lastTRXno 	= 	$rsforTRXno->fields['MTONO'];
		$rsforTRXno->MoveNext();
	}
	 $dgt		=	substr($lastTRXno, 13);	
	 $newdgt 	= 	$dgt + 1;
	 $lnt 		= 	strlen($newdgt);
	 $date2		=	date('Y-m-d');
	 if( $date1==$date2 )
	 {
		if($lnt == 1)
		{
			$newTRXno	=	"RTN-".date('Ymd').'-'."00".$newdgt;
		}
		if($lnt	==	2)
		{
			$newTRXno	=	"RTN-".date('Ymd').'-'."0".$newdgt;
		}
		if($lnt	==	3)
		{
			$newTRXno	=	"RTN-".date('Ymd').'-'.$newdgt;
		}
	 }
	 else 
	 {
	 	$newTRXno	=	"RTN-".date('Ymd').'-'."001";
	 }
	 
	return $newTRXno;
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/mto/mto.html");
?>