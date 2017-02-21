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
	$CUSTNO_MTO	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MTO_RTNDTL AS D LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.MTONO = D.MTONO","MPOSNO","MPOSNO= '{$MPOSNO}' AND H.STATUS != 'CANCELLED'");
	
	if($CUSTNO_MTO == "")
	{
		$CUSTNO		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
		if($CUSTNO != "")
		{
			$IFSCANNED		=	$global_func->Select_val($Filstar_conn,"WMS_NEW","SCANDATA_HDR","MPOSNO","MPOSNO = '$MPOSNO' AND (POSTEDBY != '' OR TRANSMITBY != '')");
			if($IFSCANNED != "")
			{
				$CUSTDESC	=	addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo= '{$CUSTNO}'"));
				echo "<script>
							$('#tdcustomer$curcnt').text('$CUSTDESC-$CUSTNO');
							$('#tdcustomer$curcnt').removeClass('errnotfound');
					  </script>";
			}
			else 
			{
				echo "<script>
						$('#tdcustomer$curcnt').text('Please check MPOS if already SCANNED and POSTED or TRANSMITTED.');
						$('#tdcustomer$curcnt').addClass('errnotfound');
						$('#txtmposno$curcnt').val('');
				  </script>";
			}
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
			$txtboxlabel	=	$_POST["txtboxlabel$a"];
			
			$GETMPOS	=	"SELECT `SKUNO`,`ITEMSTATUS`, `POSTEDQTY`,`DEFECTIVEQTY`,`IB_QTY` FROM WMS_NEW.SCANDATA_DTL 
							 WHERE MPOSNO = '{$txtmposno}' AND STATUS != 'DELETED'";
			$RSGETMPOS	=	$Filstar_conn->Execute($GETMPOS);
			if($RSGETMPOS == false)
			{
				$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
			}
			else 
			{
				while (!$RSGETMPOS->EOF) {
					$SKUNO 			= $RSGETMPOS->fields["SKUNO"]; 
					$POSTEDQTY		= $RSGETMPOS->fields["POSTEDQTY"];
					$DEFECTIVEQTY	= $RSGETMPOS->fields["DEFECTIVEQTY"];
					$IB_QTY			= $RSGETMPOS->fields["IB_QTY"];
					$QTY 			= $POSTEDQTY - $DEFECTIVEQTY - $IB_QTY; 
					$GETDESC		= addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'"));
					$EXMTOITEM		= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","ITEMNO","ITEMNO= '{$SKUNO}' AND CANCELLED = 'N'");
					$ITEMTYPE 		= $RSGETMPOS->fields["ITEMSTATUS"]; 
					$UNITPRICE 		= $global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSDTL","UNITPRICE","MPOSNO = '{$txtmposno}' AND SKUNO = '{$SKUNO}'");
					if($UNITPRICE == "" or $UNITPRICE == 0)
					{
						$UNITPRICE 	= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
					}
					$GROSSAMOUNT= $QTY * $UNITPRICE;
					if($EXMTOITEM != "")
					{
						$TRX_NO		= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","TRX_NO","ITEMNO= '{$SKUNO}'  AND CANCELLED = 'N'");
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
						$SAVEMTODTLS	=	"INSERT INTO WMS_NEW.MTO_RTNDTL(`MTONO`, `MPOSNO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`,`NO_OF_BOXES`, `NO_OF_PACK`,`BOXLABEL`, `UNITPRICE`, `GROSSAMT`)
											 VALUES('{$TXNO}','{$txtmposno}','{$SKUNO}','{$GETDESC}','{$ITEMTYPE}','{$QTY}','{$txtnoboxes}','{$txtnopackages}','{$txtboxlabel}','{$UNITPRICE}','{$GROSSAMOUNT}')";
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
	$useprevqry			= $_GET["USEQUERY"];
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
	
	if($useprevqry == "YES")
	{
		$GETMTO	=	$_SESSION["MAINQRY"];
	}
	else 
	{
		$GETMTO	=	"SELECT * FROM WMS_NEW.MTO_RTNHDR
					 WHERE 1 $txtmtono_Q $selstatus_Q $DATE_Q $rdodestination_Q";
	}
	$RSGETMTO	=	$Filstar_conn->Execute($GETMTO);
	$_SESSION["MAINQRY"]	=	$GETMTO;
	if($RSGETMTO == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		if($RSGETMTO->RecordCount() > 0)
		{
			echo "<table border='1' class='tblresult'>
					<tr class='trheader'>
				 		<td >No.</td>
				 		<td >MTO No.</td>
				 		<td >Status</td>
				 		<td >Destination</td>
				 		<td >Pieceworker</td>
				 		<td >Date Created</td>
				 		<td >Created By</td>
				 		<td >Updated Date</td>
				 		<td >Updated By</td>
				 		<td >Posted Date</td>
				 		<td >Posted By</td>
				 		<td >Printed Date</td>
				 		<td >Printed By</td>
				 		<td >Actions</td>
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
					$btnedit	=	"<img src='/wms/images/images/action_icon/new/compose.png' class='smallbtns editbtn' title='Edit Trx: $TRX_NO' data-trxno='$TRX_NO'>";
					$btnpost	=	"<img src='/wms/images/images/action_icon/new/mail.png' class='smallbtns postbtn' title='Post Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btnedit	=	"";
					$btnpost	=	"";
				}
				if($STATUS == "POSTED" or $STATUS == "PRINTED")
				{
					$btnprint	=	"<img src='/wms/images/images/action_icon/print.png' class='smallbtns printbtn' title='Print Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btnprint 	=	"";
				}
				if($STATUS != "CANCELLED" and $STATUS != "TRANSMITTED")
				{
					$btncancel		=	"<img src='/wms/images/images/action_icon/new/stop.png' class='smallbtns cancelbtn' title='Cancel Trx: $TRX_NO' data-trxno='$TRX_NO'>";
				}
				else 
				{
					$btncancel 		= 	"";
				}
				if($STATUS == "PRINTED")
				{
					$btntransmit	=	"<img src='/wms/images/images/action_icon/new/briefcase.png' class='smallbtns transmitbtn' title='Transmit Trx: $TRX_NO' data-trxno='$TRX_NO' data-destination='$DESTINATION'>";
				}
				else 
				{
					$btntransmit	=	"";
				}
				echo "<tr class='trbody'  id='trdtls$cnt' title='Click to view details'>
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
				 		<td align='center'>$btncancel $btnedit $btnpost $btnprint $btntransmit</td>	
				 	</tr> 
				 	<tr>
					 		<td id='tdtrxdtls$cnt' colspan='15' class='tdtrxdtlsClass trbody' align='center'></td>
					</tr>";
				$cnt++;
				$RSGETMTO->MoveNext();
			}
			echo "</table>";
			echo "<script>$('#hidcnt').val('$cnt');</script>";
		}
		else 
		{
			echo getTBLprev();exit();
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
			$CUSTDESC		=	addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo= '{$CUSTNO}'"));
			$NO_OF_BOXES	= 	$RSGETTRXDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK		= 	$RSGETTRXDTLS->fields["NO_OF_PACK"]; 
			$BOXLABEL		= 	$RSGETTRXDTLS->fields["BOXLABEL"]; 
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
						$('#txtboxlabel$cnt').val('$BOXLABEL');
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
			$txtboxlabel	=	$_POST["txtboxlabel$a"];
			
			$GETMPOS	=	"SELECT `SKUNO`,`ITEMSTATUS`, `POSTEDQTY`,`DEFECTIVEQTY`,`IB_QTY` FROM WMS_NEW.SCANDATA_DTL 
							 WHERE MPOSNO = '{$txtmposno}'  AND STATUS != 'DELETED'";
			$RSGETMPOS	=	$Filstar_conn->Execute($GETMPOS);
			if($RSGETMPOS == false)
			{
				$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
			}
			else 
			{
				while (!$RSGETMPOS->EOF) {
					$SKUNO 			= $RSGETMPOS->fields["SKUNO"]; 
					$POSTEDQTY		= $RSGETMPOS->fields["POSTEDQTY"];
					$DEFECTIVEQTY	= $RSGETMPOS->fields["DEFECTIVEQTY"];
					$IB_QTY			= $RSGETMPOS->fields["IB_QTY"];
					$QTY 			= $POSTEDQTY - $DEFECTIVEQTY - $IB_QTY;
					$GETDESC		= addslashes($global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'"));
					$UNITPRICE 		= $global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSDTL","UNITPRICE","MPOSNO = '{$txtmposno}' AND SKUNO = '{$SKUNO}'");
					$ITEMTYPE 		= $RSGETMPOS->fields["ITEMSTATUS"]; 
					$EXMTOITEM		= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","ITEMNO","ITEMNO= '{$SKUNO}' AND CANCELLED = 'N'");
					if($UNITPRICE == "" or $UNITPRICE == 0)
					{
						$UNITPRICE 	= $global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","UnitPrice","ItemNo = '{$SKUNO}'");
					}
					$GROSSAMOUNT= $QTY * $UNITPRICE;
					if($EXMTOITEM != "")
					{
						$TRXNO		= $global_func->Select_val($Filstar_conn,"WMS_LOOKUP","MTO_EX_ITEMS_DTLS","TRX_NO","ITEMNO= '{$SKUNO}' AND CANCELLED = 'N'");
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
						$SAVEMTODTLS	=	"INSERT INTO WMS_NEW.MTO_RTNDTL(`MTONO`, `MPOSNO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`,`NO_OF_BOXES`, `NO_OF_PACK`,`BOXLABEL`, `UNITPRICE`, `GROSSAMT`)
											 VALUES('{$TRX_NO}','{$txtmposno}','{$SKUNO}','{$GETDESC}','{$ITEMTYPE}','{$QTY}','{$txtnoboxes}','{$txtnopackages}','{$txtboxlabel}','{$UNITPRICE}','{$GROSSAMOUNT}')";
						
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
			alert('Transaction $TRX_NO has been successfully updated.');
			$('#btnreport').trigger('click',['YES']);
			$('#divtrxmto').dialog('close');
			resettrx();
		</script>";
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
		mtosave_to_10($Filstar_conn,$TRXNO);
	}
	$Filstar_conn->CompleteTrans();
	echo "<script>
				alert('Transaction $TRXNO has been successfully posted.');
				$('#btnreport').trigger('click',['YES']);
				$('#divtrxmto').dialog('close');
				resettrx();
		  </script>";
	exit();
}
if($action == "TRANSMITTRX")
{
	$TRXNO			=	$_GET["TRXNO"];
	$DESTINATION	=	$_GET["DESTINATION"];
	$TODAY	=	date("Y-m-d H:i:s A");
	
	$Filstar_conn->StartTrans();
	$TRANSMITTRX	=	"UPDATE WMS_NEW.MTO_RTNHDR SET `STATUS` = 'TRANSMITTED', `TRANSMITTED_BY` = '{$_SESSION['username']}', `TRANSMITTED_DT` = '$TODAY'
						 WHERE MTONO = '{$TRXNO}'";
	$RSTRANSMITTRX	= 	$Filstar_conn->Execute($TRANSMITTRX);
	if($RSTRANSMITTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	else 
	{
		if($DESTINATION == "RAW")
		{
			$insertToRaw	=	"INSERT INTO WMS_NEW.MTO_RAWHDR(`MTONO`,`STATUS`,`DESTINATION`,`MTO_TRANSMITTED_DT`)
								 VALUES('$TRXNO','','PIECEWORK','$TODAY')";
			$RSinsertToRaw	=	$Filstar_conn->Execute($insertToRaw);
			if($RSinsertToRaw == false)
			{
				echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
			}
			else 
			{
				$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_RTNDTL WHERE MTONO = '{$TRXNO}'";
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
						
						$insertToRawDtls	=	"INSERT INTO WMS_NEW.MTO_RAWDTL(`MTONO`, `MPOSNO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`, `NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`, `GROSSAMT`)
												 VALUES('$TRXNO','$MPOSNO','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$QTY','$NO_OF_BOXES','$NO_OF_PACK','$BOXLABEL','$UNITPRICE','$GROSSAMT')";
						$RSinsertToRawDtls	=	$Filstar_conn->Execute($insertToRawDtls);
						if($RSinsertToRawDtls == false)
						{
							echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
						}
					$RSGETDTLS->MoveNext();
					}
				}
			}
		}
		else 
		{
			$insertToBIN	=	"INSERT INTO WMS_NEW.MTO_FILLINGBINHDR(`MTONO`,`STATUS`,`DESTINATION`,`MTO_TRANSMITTED_DT`)
								 VALUES('$TRXNO','','FILLING BIN',NOW())";
			$RSinsertToBIN	=	$Filstar_conn->Execute($insertToBIN);
			if($RSinsertToBIN == false)
			{
				echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
			}
			else 
			{
				$GETDTLS	=	"SELECT MPOSNO,SKUNO,DESCRIPTION,ITEMSTATUS,SUM(QTY) AS QTY,NO_OF_BOXES,NO_OF_PACK,BOXLABEL,UNITPRICE,SUM(GROSSAMT) AS GROSSAMT 
								 FROM WMS_NEW.MTO_RTNDTL WHERE MTONO = '{$TRXNO}'
								 GROUP BY SKUNO";
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
						
						$insertToBINDtls	=	"INSERT INTO WMS_NEW.MTO_FILLINGBINDTL(`MTONO`, `SKUNO`, `DESCRIPTION`, `ITEMSTATUS`, `QTY`,`RECQTY`, `GOODQTY`, `NO_OF_BOXES`, `NO_OF_PACK`, `BOXLABEL`, `UNITPRICE`, `GROSSAMT`)
												 VALUES('$TRXNO','$SKUNO','$DESCRIPTION','$ITEMSTATUS','$QTY','$QTY','$QTY','$NO_OF_BOXES','$NO_OF_PACK','$BOXLABEL','$UNITPRICE','$GROSSAMT')";
						$RSinsertToBINDtls	=	$Filstar_conn->Execute($insertToBINDtls);
						if($RSinsertToBINDtls == false)
						{
							echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
						}
					$RSGETDTLS->MoveNext();
					}
				}
			}
		}
	}
	$Filstar_conn->CompleteTrans();
	echo "<script>
				alert('Transaction $TRXNO has been successfully transmitted.');
				$('#btnreport').trigger('click',['YES']);
				$('#divtrxmto').dialog('close');
				resettrx();
		  </script>";
	exit();
}
if($action == "CANCELTRX")
{
	$TRXNO	=	$_GET["TRXNO"];
	$TODAY	=	date("Y-m-d H:i:s");
	
	$Filstar_conn->StartTrans();
	$CANCELMTOTRX	=		"UPDATE WMS_NEW.MTO_RTNHDR SET `STATUS` = 'CANCELLED', `CANCELLEDBY` = '{$_SESSION['username']}', `CANCELLEDDT` = '$TODAY'
							 WHERE MTONO = '{$TRXNO}'";
	$RSCANCELMTOTRX	= 	$Filstar_conn->Execute($CANCELMTOTRX);
	if($RSCANCELMTOTRX == false)
	{
		echo $Filstar_conn->Errormsg()."::".__LINE__; exit();
	}
	$Filstar_conn->CompleteTrans();
	echo "<script>
				alert('Transaction $TRXNO has been successfully cancelled.');
				$('#btnreport').trigger('click',['YES']);
				$('#divtrxmto').dialog('close');
				resettrx();
		  </script>";
	exit();
}
if($action == "VIEWTRXDTLS")
{
	$TRX_NO	=	$_GET["TRXNO"];
	$COUNT	=	$_GET["COUNT"];
	$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_RTNDTL WHERE MTONO = '{$TRX_NO}' GROUP BY MPOSNO";
	$RSGETDTLS	=	$Filstar_conn->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		echo "<br><table border='1' class='tblresul-tbltdtls'>
					<tr class='tblresul-tbltdtls-hdr'>
				 		<td>No.</td>
				 		<td>MPOS No.</td>
				 		<td>Customer</td>
				 		<td>NO. of Boxes</td>
				 		<td>NO. of Pack</td>
				 		<td>Box Label</td>
				 	</tr>";
		$cnt = 1;
		while (!$RSGETDTLS->EOF) {
			$MPOSNO			= 	$RSGETDTLS->fields["MPOSNO"]; 
			$NO_OF_BOXES	= 	$RSGETDTLS->fields["NO_OF_BOXES"]; 
			$NO_OF_PACK		= 	$RSGETDTLS->fields["NO_OF_PACK"]; 
			$BOXLABEL		= 	$RSGETDTLS->fields["BOXLABEL"]; 
			$CUSTNO			=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","CUSTNO","MPOSNO= '{$MPOSNO}'");
			$CUSTDESC		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo= '{$CUSTNO}'");
			
			echo   "<tr class='trdtlsdtls tblresul-tbltdtls-dtls'  id='trdtlsdtls$cnt' title='Click to view items.'>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-mposno = '$MPOSNO' data-count='$cnt'>$cnt</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-mposno = '$MPOSNO' data-count='$cnt'>$MPOSNO</td>
				 		<td align='left'   class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-mposno = '$MPOSNO' data-count='$cnt'>$CUSTDESC-$CUSTNO</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-mposno = '$MPOSNO' data-count='$cnt'>$NO_OF_BOXES</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-mposno = '$MPOSNO' data-count='$cnt'>$NO_OF_PACK</td>
				 		<td align='center' class='tdtrxdtlsdtls'data-trxno='$TRX_NO' data-mposno = '$MPOSNO' data-count='$cnt'>$BOXLABEL</td>
				 	</tr> 
				 	<tr>
				 		<td id='tdtrxdtlsdtls$cnt' colspan='15' class='tdtrxdtlsdtlsClass td-dtls-dtls' align='center'></td>
					</tr>";
			$cnt++;
			$RSGETDTLS->MoveNext();
		}
		echo "</table><br>";
	}
	exit();
}
if($action == "VIEWTRXDTLSDTLS")
{
	$TRX_NO	=	$_GET["TRXNO"];
	$COUNT	=	$_GET["COUNT"];
	$MPOSNO	=	$_GET["MPOSNO"];
	
	$GETDTLS	=	"SELECT * FROM WMS_NEW.MTO_RTNDTL WHERE MTONO = '{$TRX_NO}' AND MPOSNO = '{$MPOSNO}'
					 ORDER BY SKUNO";
	$RSGETDTLS	=	$Filstar_conn->Execute($GETDTLS);
	if($RSGETDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		echo "<br><table class='tbl-dtls-dtls'>
					<tr class='dtls-dtls-hdr'>
				 		<td>No.</td>
				 		<td>SKU No.</td>
				 		<td>Description</td>
				 		<td>Item Status</td>
				 		<td>Qty</td>
				 		<td>Unit Price</td>
				 		<td>Gross Amount</td>
				 	</tr>";
		$cnt = 1;
		while (!$RSGETDTLS->EOF) {
			$SKUNO			= $RSGETDTLS->fields["SKUNO"]; 
			$DESCRIPTION	= $RSGETDTLS->fields["DESCRIPTION"]; 
			$ITEMSTATUS		= $RSGETDTLS->fields["ITEMSTATUS"]; 
			$QTY			= $RSGETDTLS->fields["QTY"]; 
			$DEFQTY			= $RSGETDTLS->fields["DEFQTY"]; 
			$UNITPRICE		= $RSGETDTLS->fields["UNITPRICE"]; 
			$GROSSAMT		= $RSGETDTLS->fields["GROSSAMT"]; 
			
			echo "<tr class='dtls-dtls-dtls'>
				 		<td align='center'>$cnt</td>
				 		<td align='center'>$SKUNO</td>
				 		<td>$DESCRIPTION</td>
				 		<td align='center'>$ITEMSTATUS</td>
				 		<td align='center'>$QTY</td>
				 		<td align='right'>$UNITPRICE</td>
				 		<td align='right'>".number_format($GROSSAMT,2)."</td>
			 	  </tr>";
			$cnt++;
			$RSGETDTLS->MoveNext();
		}
		echo "</table><br>";
	}
	exit();
}
function mtosave_to_10($Filstar_conn,$MTONUM)
{
	$Filstar_conn->StartTrans();
	$getmtohdr		=	"SELECT * FROM WMS_NEW.MTO_RTNHDR WHERE MTONO = '$MTONUM'";
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
			$MTONO 			= $RSgetmtohdr->fields["MTONO"]; 
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
				
				$getmtodtls		=	"SELECT MTONO,MPOSNO,SKUNO,DESCRIPTION,SUM(QTY) AS QTY,UNITPRICE,SUM(GROSSAMT) AS GROSSAMT 
									 FROM  WMS_NEW.MTO_RTNDTL 
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
						$MPOSNO 		= $RSgetmtodtls->fields["MPOSNO"]; 
						$SKUNO 			= $RSgetmtodtls->fields["SKUNO"]; 
						$DESCRIPTION 	= addslashes($RSgetmtodtls->fields["DESCRIPTION"]); 
						$QTY 			= $RSgetmtodtls->fields["QTY"]; 
						$UNITPRICE 		= $RSgetmtodtls->fields["UNITPRICE"]; 
						$GROSSAMT		= $RSgetmtodtls->fields["GROSSAMT"]; 
						
						$INSERTMTOdtls_10	=	"INSERT INTO  FDCRMSlive.mtodetail(`mdmtnum`, `mditmno`, `mditmds`, `mdwhscd`, `mduntpr`, `mdgramt`, `mdrcvqt`, `mdwhsqt`)
												 VALUES('$MTONO','$SKUNO','$DESCRIPTION','$DESTINATION','$UNITPRICE','$GROSSAMT','$QTY','$QTY')";
						$RSINSERTMTOdtls_10	=	$Filstar_conn->Execute($INSERTMTOdtls_10);
						if($RSINSERTMTOdtls_10 == false)
						{
							echo $errmsg	=	($Filstar_conn->ErrorMsg()."::".__LINE__);
							exit();
						}
					$RSgetmtodtls->MoveNext();
					}
				}
			}
		$RSgetmtohdr->MoveNext();
		}
	}
	$Filstar_conn->CompleteTrans();
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
function getTBLprev()
{
	return "<table border='1' class='tblresult'>
				<tr class='trheader'>
			 		<td >No.</td>
			 		<td >MTO No.</td>
			 		<td >Status</td>
			 		<td >Destination</td>
			 		<td >Pieceworker</td>
			 		<td >Date Created</td>
			 		<td >Created By</td>
			 		<td >Updated Date</td>
			 		<td >Updated By</td>
			 		<td >Posted Date</td>
			 		<td >Posted By</td>
			 		<td >Printed Date</td>
			 		<td >Printed By</td>
			 		<td >Actions</td>
			 	</tr>
		 		<tr class='trbody centered fnt-red'>
			 		<td colspan='14'>Nothing to display.</td>
			 	</tr>
			 </table>";
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/transactions/mto/mto.html");
?>