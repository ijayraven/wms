<?php 
include($_SERVER['DOCUMENT_ROOT']."/public_php/adodb/adodb.inc.php");
include($_SERVER['DOCUMENT_ROOT']."/public_php/class_db.php");
//include($_SERVER['DOCUMENT_ROOT']."/public_js/jsUI.php");
	$conn_255_10	=	newADOConnection("mysqlt");
	$RSconn_255_10	=	$conn_255_10->Connect("192.168.255.10","root","");
	if($RSconn_255_10 == false)
	{
		echo "Unable to connect to server."; exit();
	}
	else 
	{
		$GETEXITEMS		=	"SELECT `ITEMNO` FROM WMS_LOOKUP.MTO_EX_ITEMS_DTLS WHERE `CANCELLED` != 'Y'";
		$RSGETEXITEMS	=	$conn_255_10->Execute($GETEXITEMS);
		if($RSGETEXITEMS == false)
		{
			echo $conn_255_10->ErrprMsg()."::".__LINE__; exit();
		}
		else 
		{
			$output	=	"NO;ITEM NO;STATUS\r";
			while(!$RSGETEXITEMS->EOF)
			{
				$item_display = "";
				$ITEMNO			=	$RSGETEXITEMS->fields["ITEMNO"];
				$EXITEMS_WITH_REGMTO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTNDTL AS D LEFT JOIN WMS_NEW.MTO_RTNHDR AS H ON H.MTONO = D.MTONO","SUM(D.QTY)","SKUNO= '{$ITEMNO}' AND (H.STATUS = 'POSTED' OR H.STATUS = 'TRANSMITTED' OR H.STATUS = 'PRINTED')");
				if($EXITEMS_WITH_REGMTO != 0)
				{
					$EXITEMS_WITH_EXMTO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCDTL AS D LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO","SUM(D.QTY)","D.SKUNO= '{$ITEMNO}' AND (H.STATUS = 'POSTED' OR H.STATUS = 'TRANSMITTED' OR H.STATUS = 'PRINTED')");
					if($EXITEMS_WITH_EXMTO != 0)
					{
						$item_display	=	"Regular MTO created with Exclusive MTO created.";	
					}
					else 
					{
						$item_display	=	"Regular MTO created.";	
					}
				}
				else 
				{
					$EXITEMS_WITH_EXMTO	=	$DATASOURCE->selval($conn_255_10,"WMS_NEW","MTO_RTN_EXCDTL AS D LEFT JOIN WMS_NEW.MTO_RTN_EXCHDR AS H ON H.MTONO = D.MTONO","SUM(D.QTY)","D.SKUNO= '{$ITEMNO}' AND (H.STATUS = 'POSTED' OR H.STATUS = 'TRANSMITTED' OR H.STATUS = 'PRINTED')");
					if($EXITEMS_WITH_EXMTO != 0)
					{
						$item_display	=	"Exclusive MTO created.";
					}
				}
				if($item_display != "")
				{
					$cnt++;
					$output	.=	"$cnt;	$ITEMNO;$item_display \r";
				}
				$RSGETEXITEMS->MoveNext();
			}
			
		}
		header("Content-Disposition: attachment; filename=ExclusiveMTO.csv");
		header("Content-Location: $_SERVER[REQUEST_URI]");
		header("Content-Type: text/plain");
		header("Expires: 0");
		echo $output;
	}
?>