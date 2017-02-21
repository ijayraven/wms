<?php
/********************************************************************************************************************
* FILE NAME :	index_main.php																						*
* PURPOSE :																											*
* FILE REFERENCES :																									*
* NAME I/O DESCRIPTION 																								*
* ---------------------																								*
* EXTERNAL VARIABLES :																								*
* Source :																											*
* NAME I/O DESCRIPTION 																								*
* ---------------------																								*
* EXTERNAL REFERENCE :																								*
* NAME DESCRIPTION																									*
* ---------------------																								*
* ABNORMAL TERMINATION CONDITIONS, ERROR AND WARNING MESSAGES :														*
* ASSUMPTIONS, CONSTRAINTS, RESTRICTIONS :																			*
* NOTES :																											*
* REQUIRMENTS/FUNCTIONAL SPECIFICATION REFERENCES :																	*
* DATE 		AUTHOR	 			CHANGE ID	 	RELEASE 		DESCRIPTION OF CHANGE								*
* 2013/09/09	Raymond A. Galaroza																					*
* 																													*
* ALGORITHM(pseudocode)																								*
* 																													*
*********************************************************************************************************************/
session_start();
set_time_limit(0);
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}


if ($_GET['action']=='GETDATA_NBS_SUMMARY') 
{
	$year		=	$_GET['SEL_YEAR'];
	$wave		=	$_GET['SEL_WAVE'];
	$srcode		=	$_GET['SRCODE'];
	$custcode	=	$_GET['CUSTCODE'];
	$product	=	$_GET['SEL_PRODUCTTYPE'];
	
	$DB			=	"CHRISTMAS_".$year;
	if($wave != 'WAVE3')
	{
		$TABLE_HDR	=	"ORDERHEADER_".$wave;
		$TABLE_DTL	=	"ORDERDETAIL_".$wave;
	}
	else 
	{
		$TABLE_HDR	=	"ORDERHEADER";
		$TABLE_DTL	=	"ORDERDETAIL";
	}
	
	if ($product=='CARDS') 
	{
		$type	=	"CARDS";
	}
	elseif ($product=='NONCARDS')
	{
		$type	=	"NCARDS";
	}
	elseif ($product=='GIFTBAG')
	{
		$type	=	"NBSGIFTBAG";
	}
	elseif ($product=='ROLLEDWRAP')
	{
		$type	=	"NBSROLLWRAP";
	}
	elseif ($product=="FLATWRAP")
	{
		$type	=	"NBSFLATWRAP";
	}
	
	$aCustlist	=	array();
	$aSOFlist	=	array();
	$aData		=	array();
	
	if (!empty($srcode)) 
	{
		$check_sr	=	strlen($srcode);
		if ($check_sr==3) 
		{
			$sr	=	$srcode;
		}
		else 
		{
			$sr	=	substr($srcode,3,3);
		}
		$sel_customer	=	"SELECT CustNo from custmast where SalesRepCode = '{$sr}' and CustStatus = 'A' ";
		$rssel_customer	=	$Filstar_conn->Execute($sel_customer);
		if ($rssel_customer==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_customer->EOF) 
		{
			$aCustlist[]	=	$rssel_customer->fields['CustNo'];
			
			$rssel_customer->MoveNext();
		} 
		
		$custlist	=	implode("','",$aCustlist);
	}
	
	$sel_sof	 =	"SELECT ORDERNO FROM $DB.$TABLE_HDR WHERE ORDERSTATUS = 'For Picking' ";
	$sel_sof	.=	"AND TYPE = '{$type}' and PICKLISTNO != '' ";
	if (!empty($custcode)) 
	{
		$sel_sof	.=	"AND CUSTNO	= '{$custcode}' ";
	}
	else 
	{
		if (!empty($srcode)) 
		{
		$sel_sof	.=	"AND CUSTNO	IN ('$custlist') ";
		}
	}
	//echo $sel_sof;exit();
	$rssel_sof	=	$Filstar_172->Execute($sel_sof);
	if ($rssel_sof==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_sof->EOF) 
	{
		$ORDERNO	=	$rssel_sof->fields['ORDERNO'];
		
		$aSOFlist[]	=	$ORDERNO;
		
		$rssel_sof->MoveNext();
	}
	
	$asof		=	implode("','",$aSOFlist);
	
	
	$sel_dtl	=	"SELECT ITEM,SUM(ORDERQTY)AS QTY,UNITCOST,UNITPRICE,CUSTNO,ORDERNO,DESCRIPTION FROM $DB.$TABLE_DTL where ORDERNO IN('{$asof}') ";
	$sel_dtl	.=	"GROUP BY CUSTNO,ITEM ";
	$rssel_dtl	=	$Filstar_172->Execute($sel_dtl);
	if ($rssel_dtl==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_dtl->EOF)
	{
		$ITEM		=	$rssel_dtl->fields['ITEM'];
		$QTY		=	$rssel_dtl->fields['QTY'];
		$UNITCOST	=	$rssel_dtl->fields['UNITCOST'];
		$UNITPRICE	=	$rssel_dtl->fields['UNITPRICE'];
		$CUSTNO		=	$rssel_dtl->fields['CUSTNO'];
		$ORDERNO	=	$rssel_dtl->fields['ORDERNO'];
		$DESCRIPTION	=	$rssel_dtl->fields['DESCRIPTION'];
		
		$CustName	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
		$sr__		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CUSTNO}' ");
		$sr___name	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$sr__}' ");
		
		$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['ALLOC_QTY']	+=	$QTY;
		$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['UNITCOST']		=	$UNITCOST;
		$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['UNITPRICE']	=	$UNITPRICE;
		$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['ItemDesc']	=	$DESCRIPTION;
		
		$sel_dtl_2	=	"SELECT Item,SUM(ReleaseQty) AS qty,CustNo from orderdetail where Item = '{$ITEM}' and OrderNo = '{$ORDERNO}' and isDeleted != 'Y' ";
		$sel_dtl_2	.=	"GROUP BY Item ";
		$rssel_dtl_2=	$Filstar_conn->Execute($sel_dtl_2);
		if ($rssel_dtl_2==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		if ($rssel_dtl_2->RecordCount() > 0) 
		{
			
			$qty	=	$rssel_dtl_2->fields['qty'];
			$CustNo	=	$rssel_dtl_2->fields['CustNo'];
		
//			$CustName	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CustNo}' ");
//			$sr__		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CustNo}' ");
//			$sr___name	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$sr__}' ");
		
			$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['PL_QTY']	+=	$qty;
			$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['ItemDesc']	=	$DESCRIPTION;
		}
		else 
		{
			$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['PL_QTY']	+=	0;
			$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['ItemDesc']	=	$DESCRIPTION;
		}
		
		$rssel_dtl->MoveNext();
	}
	
	if ($wave=='WAVE1')
	{
		$range	=	date("Y-m-d",strtotime("2016-08-05")).' to '.date("Y-m-d",strtotime("2016-08-11"));
		//$range	=	date("Y-m-d",strtotime("2016-10-03")).' to '.date("Y-m-d",strtotime("2016-10-03"));
	}
	else if($wave=='WAVE2')
	{
		$range	=	date("Y-m-d",strtotime("2016-10-12")).' to '.date("Y-m-d",strtotime("2016-10-24"));
	}
	else 
	{
		$range	=	date("Y-m-d",strtotime("2016-10-26")).' to '.date("Y-m-d");
	}
	
	$sCsv	.=	"$wave;$range";
	$sCsv	.=	"\n";
	$sCsv	.=	"NBS";
	$sCsv	.=	"\n";
	$sCsv	.=	$type;
	$sCsv	.=	"\n";
	$sCsv	.=	"LINE#;SR;CUSTOMER;SKU;ITEM DESCRIPTION;ALLOC QTY;PL QTY;DISC PRICE;SRP;NET AMOUNT;GROSS AMOUNT;";
	$sCsv	.=	"\n";
	
	
	
	foreach ($aData as $key_sr=>$val_cust)
	{
		foreach ($val_cust as $key_cust=>$val_item)
		{
			$total_net		=	0;
			$total_gross	=	0;
			$total_alloc	=	0;
			$total_pl		=	0;
			
			$counter	=	1;
			
			foreach ($val_item as $key_item=>$val__)
			{
				$ALLOC_QTY	=	$val__['ALLOC_QTY'];
				$UNITCOST	=	$val__['UNITCOST'];
				$UNITPRICE	=	$val__['UNITPRICE'];
				$PL_QTY		=	$val__['PL_QTY'];
				$ITEMDESC	=	$val__['ItemDesc'];
				
				$net		=	$PL_QTY*$UNITCOST;
				$gross		=	$PL_QTY*$UNITPRICE;
				
				$total_net	+=	$net;
				$total_gross+=	$gross;
				
				$total_alloc+=	$ALLOC_QTY;
				$total_pl	+=	$PL_QTY;
				
				$sCsv		.=	"$counter;$key_sr;$key_cust;$key_item;$ITEMDESC;$ALLOC_QTY;$PL_QTY;$UNITCOST;$UNITPRICE;$net;$gross;";
				$sCsv		.=	"\n";
				
				$counter++;
			}
			//$sCsv		.=	";;TOTAL;;;$total_alloc;$total_pl;;;$total_net;$total_gross;";
			//$sCsv		.=	"\n\n";
		}
	}
	
	$time__	=	date("Y-m-d H:i:s");
	
	$filename	=	"NBS_SUMMARY_".$type."_$time__".".csv";
	
	header("Content-type: application/x-msdownload");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $sCsv;
	exit();
}


if ($_GET['action']=='GETDATA_TRADE_SUMMARY') 
{
	$year		=	$_GET['SEL_YEAR'];
	$wave		=	$_GET['SEL_WAVE'];
	$srcode		=	$_GET['SRCODE'];
	$custcode	=	$_GET['CUSTCODE'];
	$product	=	$_GET['SEL_PRODUCTTYPE'];
	
	$DB			=	"CHRISTMAS_".$year;
	if($wave != 'WAVE3')
	{
		$TABLE_HDR	=	"ORDERHEADER_".$wave;
		$TABLE_DTL	=	"ORDERDETAIL_".$wave;
	}
	else 
	{
		$TABLE_HDR	=	"ORDERHEADER";
		$TABLE_DTL	=	"ORDERDETAIL";
	}
	
	if ($product=='CARDS') 
	{
		$type	=	"TRADECARDS";
	}
	elseif ($product=='NONCARDS')
	{
		$type	=	"TRADENCARDS";
	}
	elseif ($product=='GIFTBAG')
	{
		$type	=	"TRADEGIFTBAG";
	}
	elseif ($product=='ROLLEDWRAP')
	{
		$type	=	"TRADEROLLWRAP";
	}
	elseif ($product=="FLATWRAP")
	{
		$type	=	"TRADEFLATWRAP";
	}
	
	$aCustlist	=	array();
	$aSOFlist	=	array();
	
	if (!empty($srcode)) 
	{
		$check_sr	=	strlen($srcode);
		if ($check_sr==3) 
		{
			$sr	=	$srcode;
		}
		else 
		{
			$sr	=	substr($srcode,3,3);
		}
		$sel_customer	=	"SELECT CustNo from custmast where SalesRepCode = '{$sr}' and CustStatus = 'A' ";
		$rssel_customer	=	$Filstar_conn->Execute($sel_customer);
		if ($rssel_customer==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_customer->EOF) 
		{
			$aCustlist[]	=	$rssel_customer->fields['CustNo'];
			
			$rssel_customer->MoveNext();
		}
		
		$custlist	=	implode("','",$aCustlist);
	}
	
	$sel_sof	 =	"SELECT ORDERNO FROM $DB.$TABLE_HDR WHERE ORDERSTATUS = 'For Picking' ";
	$sel_sof	.=	"AND TYPE = '{$type}' and PICKLISTNO != '' ";
	if (!empty($custcode)) 
	{
		$sel_sof	.=	"AND CUSTNO	= '{$custcode}' ";
	}
	else 
	{
		if (!empty($srcode)) 
		{
		$sel_sof	.=	"AND CUSTNO	IN ('$custlist') ";
		}
	}
	//echo $sel_sof;exit();
	$rssel_sof	=	$Filstar_172->Execute($sel_sof);
	if ($rssel_sof==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_sof->EOF) 
	{
		$ORDERNO	=	$rssel_sof->fields['ORDERNO'];
		
		$aSOFlist[]	=	$ORDERNO;
		
		$rssel_sof->MoveNext();
	}
	
	
	$asof		=	implode("','",$aSOFlist);
	
	
	$sel_dtl	=	"SELECT ITEM,SUM(ORDERQTY)AS QTY,UNITCOST,UNITPRICE,CUSTNO,ORDERNO,DESCRIPTION FROM $DB.$TABLE_DTL where ORDERNO IN('{$asof}') ";
	$sel_dtl	.=	"GROUP BY CUSTNO,ITEM ";
	$rssel_dtl	=	$Filstar_172->Execute($sel_dtl);
	if ($rssel_dtl==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_dtl->EOF)
	{
		$ITEM		=	$rssel_dtl->fields['ITEM'];
		$QTY		=	$rssel_dtl->fields['QTY'];
		$UNITCOST	=	$rssel_dtl->fields['UNITCOST'];
		$UNITPRICE	=	$rssel_dtl->fields['UNITPRICE'];
		$CUSTNO		=	$rssel_dtl->fields['CUSTNO'];
		$ORDERNO	=	$rssel_dtl->fields['ORDERNO'];
		$DESCRIPTION	=	$rssel_dtl->fields['DESCRIPTION'];
		
		$CustName	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
		$sr__		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CUSTNO}' ");
		$sr___name	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$sr__}' ");
		
		$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['ALLOC_QTY']	+=	$QTY;
		$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['UNITCOST']		=	$UNITCOST;
		$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['UNITPRICE']	=	$UNITPRICE;
		$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['ItemDesc']	=	$DESCRIPTION;
		
		
		$sel_dtl_2	=	"SELECT Item,SUM(ReleaseQty) AS qty,CustNo from orderdetail where Item = '{$ITEM}' and OrderNo = '{$ORDERNO}' and isDeleted != 'Y' ";
		$sel_dtl_2	.=	"GROUP BY Item ";
		$rssel_dtl_2=	$Filstar_conn->Execute($sel_dtl_2);
		if ($rssel_dtl_2==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		if ($rssel_dtl_2->RecordCount() > 0) 
		{
			
			$qty	=	$rssel_dtl_2->fields['qty'];
			$CustNo	=	$rssel_dtl_2->fields['CustNo'];
			
//			$CustName	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CustNo}' ");
//			$sr__		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CustNo}' ");
//			$sr___name	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$sr__}' ");
		
			$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['PL_QTY']	+=	$qty;
			$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['ItemDesc']	=	$DESCRIPTION;
		}
		else 
		{
			$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['PL_QTY']	+=	0;
			$aData[$sr__.'-'.$sr___name][$CUSTNO.'-'.$CustName][$ITEM]['ItemDesc']	=	$DESCRIPTION;
		}
		
		$rssel_dtl->MoveNext();
	}
	
	if ($wave=='WAVE1')
	{
		$range	=	date("Y-m-d",strtotime("2016-08-05")).' to '.date("Y-m-d",strtotime("2016-08-11"));
		//$range	=	date("Y-m-d",strtotime("2016-10-03")).' to '.date("Y-m-d",strtotime("2016-10-03"));
	}
	else if($wave=='WAVE2')
	{
		$range	=	date("Y-m-d",strtotime("2016-10-12")).' to '.date("Y-m-d",strtotime("2016-10-24"));
	}
	else 
	{
		$range	=	date("Y-m-d",strtotime("2016-10-26")).' to '.date("Y-m-d");
	}
	
	$sCsv	.=	"$wave;$range";
	$sCsv	.=	"\n";
	$sCsv	.=	"TRADE";
	$sCsv	.=	"\n";
	$sCsv	.=	$type;
	$sCsv	.=	"\n";
	$sCsv	.=	"LINE#;SR;CUSTOMER;SKU;ITEM DESCRIPTION;ALLOC QTY;PL QTY;DISC PRICE;SRP;NET AMOUNT;GROSS AMOUNT;";
	$sCsv	.=	"\n";
	
	foreach ($aData as $key_sr=>$val_cust)
	{
		foreach ($val_cust as $key_cust=>$val_item)
		{
			$total_net		=	0;
			$total_gross	=	0;
			$total_alloc	=	0;
			$total_pl		=	0;
			
			$counter	=	1; 
			
			foreach ($val_item as $key_item=>$val__)
			{
				$ALLOC_QTY	=	$val__['ALLOC_QTY'];
				$UNITCOST	=	$val__['UNITCOST'];
				$UNITPRICE	=	$val__['UNITPRICE'];
				$PL_QTY		=	$val__['PL_QTY'];
				$ItemDesc	=	$val__['ItemDesc'];
				
				$net		=	$PL_QTY*$UNITCOST;
				$gross		=	$PL_QTY*$UNITPRICE;
				
				$total_net	+=	$net;
				$total_gross+=	$gross;
				
				$total_alloc+=	$ALLOC_QTY;
				$total_pl	+=	$PL_QTY;
				
				$sCsv		.=	"$counter;$key_sr;$key_cust;$key_item;$ItemDesc;$ALLOC_QTY;$PL_QTY;$UNITCOST;$UNITPRICE;$net;$gross;";
				$sCsv	.=	"\n";
				
				$counter++;
			}
			//$sCsv		.=	";;TOTAL;;;$total_alloc;$total_pl;;;$total_net;$total_gross;";
			//$sCsv	.=	"\n\n";
		}
	}
	
	
	$time__	=	date("Y-m-d H:i:s");
	
	$filename	=	"TRADE_SUMMARY_".$type."_$time__".".csv";
	
	header("Content-type: application/x-msdownload");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $sCsv;
	exit();
}

if ($_GET['action']=='GETDATA_NBS_DETAILS') 
{
	$year		=	$_GET['SEL_YEAR'];
	$wave		=	$_GET['SEL_WAVE'];
	$srcode		=	$_GET['SRCODE'];
	$custcode	=	$_GET['CUSTCODE'];
	$product	=	$_GET['SEL_PRODUCTTYPE'];
	
	$DB			=	"CHRISTMAS_".$year;
	if($wave != 'WAVE3')
	{
		$TABLE_HDR	=	"ORDERHEADER_".$wave;
		$TABLE_DTL	=	"ORDERDETAIL_".$wave;
	}
	else 
	{
		$TABLE_HDR	=	"ORDERHEADER";
		$TABLE_DTL	=	"ORDERDETAIL";
	}
	
	if ($product=='CARDS') 
	{
		$type	=	"CARDS";
	}
	elseif ($product=='NONCARDS')
	{
		$type	=	"NCARDS";
	}
	elseif ($product=='GIFTBAG')
	{
		$type	=	"NBSGIFTBAG";
	}
	elseif ($product=='ROLLEDWRAP')
	{
		$type	=	"NBSROLLWRAP";
	}
	elseif ($product=="FLATWRAP")
	{
		$type	=	"NBSFLATWRAP";
	}
	
	$aCustlist	=	array();
	$aSOFlist	=	array();
	
	if (!empty($srcode)) 
	{
		$check_sr	=	strlen($srcode);
		if ($check_sr==3) 
		{
			$sr	=	$srcode;
		}
		else 
		{
			$sr	=	substr($srcode,3,3);
		}
		$sel_customer	=	"SELECT CustNo from custmast where SalesRepCode = '{$sr}' and CustStatus = 'A' ";
		$rssel_customer	=	$Filstar_conn->Execute($sel_customer);
		if ($rssel_customer==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_customer->EOF) 
		{
			$aCustlist[]	=	$rssel_customer->fields['CustNo'];
			
			$rssel_customer->MoveNext();
		}
		$custlist	=	implode("','",$aCustlist);
	}
	
	
	if ($wave=='WAVE1')
	{
		$range	=	date("Y-m-d",strtotime("2016-08-05")).' to '.date("Y-m-d",strtotime("2016-08-11"));
		//$range	=	date("Y-m-d",strtotime("2016-10-03")).' to '.date("Y-m-d",strtotime("2016-10-03"));
	}
	else if($wave=='WAVE2')
	{
		$range	=	date("Y-m-d",strtotime("2016-10-12")).' to '.date("Y-m-d",strtotime("2016-10-24"));
	}
	else 
	{
		$range	=	date("Y-m-d",strtotime("2016-10-26")).' to '.date("Y-m-d");
	}
	
	$sCsv	.=	"$wave;$range";
	$sCsv	.=	"\n";
	$sCsv	.=	"NBS";
	$sCsv	.=	"\n";
	$sCsv	.=	$type;
	$sCsv	.=	"\n";
	$sCsv	.=	"LINE#;SR;CUSTOMER;SOF;PL;INV;STF;NET AMOUNT;GROSS AMOUNT;STATUS;";
	$sCsv	.=	"\n";
	
	$counter	=	1;
	$total_net	=	0;
	$total_gross=	0;
	
	$sel_sof	 =	"SELECT ORDERNO,CUSTNO,PICKLISTNO FROM $DB.$TABLE_HDR WHERE ORDERSTATUS = 'For Picking' ";
	$sel_sof	.=	"AND TYPE = '{$type}' and PICKLISTNO != '' ";
	//$sel_sof	.=	"AND ORDERDATE between '2016-10-03' and '2016-10-03' ";
	if (!empty($custcode)) 
	{
		$sel_sof	.=	"AND CUSTNO	= '{$custcode}' ";
	}
	else 
	{
		if (!empty($srcode)) 
		{
		$sel_sof	.=	"AND CUSTNO	IN ('$custlist') ";
		}
	}
	$sel_sof	.=	"ORDER BY CUSTNO ";
	//echo $sel_sof;exit();
	$rssel_sof	=	$Filstar_172->Execute($sel_sof);
	if ($rssel_sof==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_sof->EOF) 
	{
		$ORDERNO	=	$rssel_sof->fields['ORDERNO'];
		$CUSTNO		=	$rssel_sof->fields['CUSTNO'];
		$PICKLISTNO	=	$rssel_sof->fields['PICKLISTNO'];
		
		$OrderStatus	=	'';
		
		$CustName		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
		$sr__			=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CUSTNO}' ");
		$sr___name		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$sr__}' ");
		$InvoiceNo		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","InvoiceNo","OrderNo = '{$ORDERNO}' ");
		$OrderCategory	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","OrderCategory","OrderNo = '{$ORDERNO}' ");
		$OrderStatus	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","OrderStatus","OrderNo = '{$ORDERNO}' ");
		
		if (!empty($OrderStatus)) 
		{
		if ($OrderStatus=='For Picking') 
		{
			$status	=	'SOF FOR PICKING';
		}
		else if ($OrderStatus=='Confirmed' || $OrderStatus=='Invoiced') 
		{
			$status	=	'PLCONF NOT YET DISPATCH';
			
			$is_manila	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_METROMANILA_DTL","TRACKINGNO","SOFNO = '{$ORDERNO}' and STATUS != 'CANCELLED' ");
			if (empty($is_manila)) 
			{
				$is_province	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_PROVINCE_DTL","TRACKINGNO","SOFNO = '{$ORDERNO}' and STATUS != 'CANCELLED' ");
				if (empty($is_province)) 
				{
					$status	=	'PLCONF NOT YET DISPATCH';
					$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$ORDERNO}' ");
					if (empty($is_confirm)) 
					{
						$status	=	'DISPATCH NOT YET CONF DELIVERY';
					}
					else 
					{
						$status	=	'CONF DELIVERY';
					}
				}
				else 
				{
					$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$ORDERNO}' ");
					if (empty($is_confirm)) 
					{
						$status	=	'DISPATCH NOT YET CONF DELIVERY';
					}
					else 
					{
						$status	=	'CONF DELIVERY';
					}
				}
			}
			else 
			{
				$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$ORDERNO}' ");
				if (empty($is_confirm)) 
				{
					$status	=	'DISPATCH NOT YET CONF DELIVERY';
				}
				else 
				{
					$status	=	'CONF DELIVERY';
				}
			}
		}
		
		$sr				=	$sr__."-".$sr___name;
		
		$sel_amount		=	"SELECT sum(GrossAmount)as gross ,sum(NetAmount)as net FROM orderdetail where OrderNo = '{$ORDERNO}' and isDeleted != 'Y' ";
		$rssel_amount	=	$Filstar_conn->Execute($sel_amount);
		if ($rssel_amount==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$gross	=	$rssel_amount->fields['gross'];
		$net	=	$rssel_amount->fields['net'];
		
		$total_net	+=	$net;
		$total_gross+=	$gross;
		
		if ($OrderCategory!='STF') 
		{
			$sCsv	.=	"$counter;$sr;$CUSTNO-$CustName;$ORDERNO;$PICKLISTNO;$InvoiceNo;;$net;$gross;$status";
			$sCsv	.=	"\n";
		}
		else 
		{
			$sCsv	.=	"$counter;$sr;$CUSTNO-$CustName;$ORDERNO;$PICKLISTNO;;$InvoiceNo;$net;$gross;$status";
			$sCsv	.=	"\n";
		}
		
		$counter++;
		}
		$rssel_sof->MoveNext();
	}
	$sCsv	.=		";;;;TOTAL;;;$total_net;$total_gross";
	
	$time__	=	date("Y-m-d H:i:s");
	
	$filename	=	"NBS_DETAILS_".$type."_$time__".".csv";
	
	header("Content-type: application/x-msdownload");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $sCsv;
	exit();
}

//if ($_GET['action']=='GETDATA_NBS_DETAILS') 
//{
//	$year		=	$_GET['SEL_YEAR'];
//	$wave		=	$_GET['SEL_WAVE'];
//	$srcode		=	$_GET['SRCODE'];
//	$custcode	=	$_GET['CUSTCODE'];
//	$product	=	$_GET['SEL_PRODUCTTYPE'];
//	
//	$DB			=	"CHRISTMAS_".$year;
//	if($wave != 'WAVE3')
//	{
//		$TABLE_HDR	=	"ORDERHEADER_".$wave;
//		$TABLE_DTL	=	"ORDERDETAIL_".$wave;
//	}
//	else 
//	{
//		$TABLE_HDR	=	"ORDERHEADER";
//		$TABLE_DTL	=	"ORDERDETAIL";
//	}
//	
//	if ($product=='CARDS') 
//	{
//		$type	=	"CARDS";
//	}
//	elseif ($product=='NONCARDS')
//	{
//		$type	=	"NCARDS";
//	}
//	elseif ($product=='GIFTBAG')
//	{
//		$type	=	"NBSGIFTBAG";
//	}
//	elseif ($product=='ROLLEDWRAP')
//	{
//		$type	=	"NBSROLLWRAP";
//	}
//	elseif ($product=="FLATWRAP")
//	{
//		$type	=	"NBSFLATWRAP";
//	}
//	
//	$aCustlist	=	array();
//	$aSOFlist	=	array();
//	$aData		=	array();
//	
//	if (!empty($srcode)) 
//	{
//		$check_sr	=	strlen($srcode);
//		if ($check_sr==3) 
//		{
//			$sr	=	$srcode;
//		}
//		else 
//		{
//			$sr	=	substr($srcode,3,3);
//		}
//		$sel_customer	=	"SELECT CustNo from custmast where SalesRepCode = '{$sr}' and CustStatus = 'A' ";
//		$rssel_customer	=	$Filstar_conn->Execute($sel_customer);
//		if ($rssel_customer==false) 
//		{
//			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
//		}
//		while (!$rssel_customer->EOF) 
//		{
//			$aCustlist[]	=	$rssel_customer->fields['CustNo'];
//			
//			$rssel_customer->MoveNext();
//		}
//		
//		$custlist	=	implode("','",$aCustlist);
//	}
//	
//	$sel_sof	 =	"SELECT ORDERNO,CUSTNO,PICKLISTNO FROM $DB.$TABLE_HDR WHERE ORDERSTATUS = 'For Picking' ";
//	$sel_sof	.=	"AND TYPE = '{$type}' and PICKLISTNO != '' ";
//	if (!empty($custcode)) 
//	{
//		$sel_sof	.=	"AND CUSTNO	= '{$custcode}' ";
//	}
//	else 
//	{
//		if (!empty($srcode)) 
//		{
//		$sel_sof	.=	"AND CUSTNO	IN ('$custlist') ";
//		}
//	}
//	$sel_sof	.=	"ORDER BY CUSTNO ";
//	$rssel_sof	=	$Filstar_172->Execute($sel_sof);
//	if ($rssel_sof==false) 
//	{
//		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
//	}
//	
//	
//	if ($wave=='WAVE1')
//	{
//		$range	=	date("Y-m-d",strtotime("2016-08-05")).' to '.date("Y-m-d",strtotime("2016-08-11"));
//	}
//	else if($wave=='WAVE2')
//	{
//		$range	=	date("Y-m-d",strtotime("2016-10-12")).' to '.date("Y-m-d",strtotime("2016-10-24"));
//	}
//	else 
//	{
//		$range	=	date("Y-m-d",strtotime("2016-10-26")).' to '.date("Y-m-d");
//	}
//	
//	$sCsv	.=	"$wave;$range";
//	$sCsv	.=	"\n";
//	$sCsv	.=	"NBS";
//	$sCsv	.=	"\n";
//	$sCsv	.=	$type;
//	$sCsv	.=	"\n";
//	$sCsv	.=	"LINE#;SR;CUSTOMER;SOF;PL;INV;STF;NET AMOUNT;GROSS AMOUNT;STATUS;";
//	$sCsv	.=	"\n";
//	
//	$counter	=	1;
//	$total_net	=	0;
//	$total_gross=	0;
//	
//	while (!$rssel_sof->EOF) 
//	{
//		$ORDERNO	=	$rssel_sof->fields['ORDERNO'];
//		$CUSTNO		=	$rssel_sof->fields['CUSTNO'];
//		$PICKLISTNO	=	$rssel_sof->fields['PICKLISTNO'];
//		
//		$CustName		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
//		$sr__			=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CUSTNO}' ");
//		$sr___name		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$sr__}' ");
//		$InvoiceNo		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","InvoiceNo","OrderNo = '{$ORDERNO}' ");
//		$OrderCategory	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","OrderCategory","OrderNo = '{$ORDERNO}' ");
//		$OrderStatus	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","OrderStatus","OrderNo = '{$ORDERNO}' ");
//		
//		if (!empty($OrderStatus)) 
//		{
//			if ($OrderStatus=='For Picking') 
//			{
//				$status	=	'SOF FOR PICKING';
//			}
//			else if ($OrderStatus=='Confirmed' || $OrderStatus=='Invoiced') 
//			{
//				$status	=	'PLCONF NOT YET DISPATCH';
//				
//				$is_manila	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_METROMANILA_DTL","TRACKINGNO","SOFNO = '{$ORDERNO}' and STATUS != 'CANCELLED' ");
//				if (empty($is_manila)) 
//				{
//					$is_province	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_PROVINCE_DTL","TRACKINGNO","SOFNO = '{$ORDERNO}' and STATUS != 'CANCELLED' ");
//					if (empty($is_province)) 
//					{
//						$status	=	'PLCONF NOT YET DISPATCH';
//						$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$ORDERNO}' ");
//						if (empty($is_confirm)) 
//						{
//							$status	=	'DISPATCH NOT YET CONF DELIVERY';
//						}
//						else 
//						{
//							$status	=	'CONF DELIVERY';
//						}
//					}
//					else 
//					{
//						$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$ORDERNO}' ");
//						if (empty($is_confirm)) 
//						{
//							$status	=	'DISPATCH NOT YET CONF DELIVERY';
//						}
//						else 
//						{
//							$status	=	'CONF DELIVERY';
//						}
//					}
//				}
//				else 
//				{
//					$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$ORDERNO}' ");
//					if (empty($is_confirm)) 
//					{
//						$status	=	'DISPATCH NOT YET CONF DELIVERY';
//					}
//					else 
//					{
//						$status	=	'CONF DELIVERY';
//					}
//				}
//			}
//		
//		
//		$sr				=	$sr__."-".$sr___name;
//		
//		$sel_amount		=	"SELECT sum(GrossAmount)as gross ,sum(NetAmount)as net FROM orderdetail where OrderNo = '{$ORDERNO}' and isDeleted != 'Y' ";
//		$rssel_amount	=	$Filstar_conn->Execute($sel_amount);
//		if ($rssel_amount==false) 
//		{
//			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
//		}
//		$gross	=	$rssel_amount->fields['gross'];
//		$net	=	$rssel_amount->fields['net'];
//		
//		$total_net	+=	$net;
//		$total_gross+=	$gross;
//		
//		if ($OrderCategory!='STF') 
//		{
//			$sCsv	.=	"$counter;$sr;$CUSTNO-$CustName;$ORDERNO;$PICKLISTNO;$InvoiceNo;;$net;$gross;$status";
//			$sCsv	.=	"\n";
//		}
//		else 
//		{
//			$sCsv	.=	"$counter;$sr;$CUSTNO-$CustName;$ORDERNO;$PICKLISTNO;;$InvoiceNo;$net;$gross;$status";
//			$sCsv	.=	"\n";
//		}
//		
//		$counter++;
//		}
//
//		$rssel_sof->MoveNext();
//	}
//	
//	$sCsv	.=		";;;;TOTAL;;;$total_net;$total_gross";
//	
//	$time__	=	date("Y-m-d H:i:s");
//	
//	$filename	=	"NBS_DETAILS_".$type."_$time__".".csv";
//	
//	header("Content-type: application/x-msdownload");
//	header("Content-Disposition: attachment; filename=$filename");
//	header("Pragma: no-cache");
//	header("Expires: 0");
//	echo $sCsv;
//	exit();
//}


if ($_GET['action']=='GETDATA_TRADE_DETAILS') 
{
	$year		=	$_GET['SEL_YEAR'];
	$wave		=	$_GET['SEL_WAVE'];
	$srcode		=	$_GET['SRCODE'];
	$custcode	=	$_GET['CUSTCODE'];
	$product	=	$_GET['SEL_PRODUCTTYPE'];
	
	$DB			=	"CHRISTMAS_".$year;
	if($wave != 'WAVE3')
	{
		$TABLE_HDR	=	"ORDERHEADER_".$wave;
		$TABLE_DTL	=	"ORDERDETAIL_".$wave;
	}
	else 
	{
		$TABLE_HDR	=	"ORDERHEADER";
		$TABLE_DTL	=	"ORDERDETAIL";
	}
	
	if ($product=='CARDS') 
	{
		$type	=	"TRADECARDS";
	}
	elseif ($product=='NONCARDS')
	{
		$type	=	"TRADENCARDS";
	}
	elseif ($product=='GIFTBAG')
	{
		$type	=	"TRADEGIFTBAG";
	}
	elseif ($product=='ROLLEDWRAP')
	{
		$type	=	"TRADEROLLWRAP";
	}
	elseif ($product=="FLATWRAP")
	{
		$type	=	"TRADEFLATWRAP";
	}
	
	$aCustlist	=	array();
	$aSOFlist	=	array();
	
	if (!empty($srcode)) 
	{
		$check_sr	=	strlen($srcode);
		if ($check_sr==3) 
		{
			$sr	=	$srcode;
		}
		else 
		{
			$sr	=	substr($srcode,3,3);
		}
		$sel_customer	=	"SELECT CustNo from custmast where SalesRepCode = '{$sr}' and CustStatus = 'A' ";
		$rssel_customer	=	$Filstar_conn->Execute($sel_customer);
		if ($rssel_customer==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_customer->EOF) 
		{
			$aCustlist[]	=	$rssel_customer->fields['CustNo'];
			
			$rssel_customer->MoveNext();
		}
		$custlist	=	implode("','",$aCustlist);
	}
	
	
	if ($wave=='WAVE1')
	{
		$range	=	date("Y-m-d",strtotime("2016-08-05")).' to '.date("Y-m-d",strtotime("2016-08-11"));
		//$range	=	date("Y-m-d",strtotime("2016-10-03")).' to '.date("Y-m-d",strtotime("2016-10-03"));
	}
	else if($wave=='WAVE2')
	{
		$range	=	date("Y-m-d",strtotime("2016-10-12")).' to '.date("Y-m-d",strtotime("2016-10-24"));
	}
	else 
	{
		$range	=	date("Y-m-d",strtotime("2016-10-26")).' to '.date("Y-m-d");
	}
	
	$sCsv	.=	"$wave;$range";
	$sCsv	.=	"\n";
	$sCsv	.=	"TRADES";
	$sCsv	.=	"\n";
	$sCsv	.=	$type;
	$sCsv	.=	"\n";
	$sCsv	.=	"LINE#;SR;CUSTOMER;SOF;PL;INV;STF;NET AMOUNT;GROSS AMOUNT;STATUS;";
	$sCsv	.=	"\n";
	
	$counter	=	1;
	$total_net	=	0;
	$total_gross=	0;
	
	$sel_sof	 =	"SELECT ORDERNO,CUSTNO,PICKLISTNO FROM $DB.$TABLE_HDR WHERE ORDERSTATUS = 'For Picking' ";
	$sel_sof	.=	"AND TYPE = '{$type}' and PICKLISTNO != '' ";
	//$sel_sof	.=	"AND ORDERDATE between '2016-10-03' and '2016-10-03' ";
	if (!empty($custcode)) 
	{
		$sel_sof	.=	"AND CUSTNO	= '{$custcode}' ";
	}
	else 
	{
		if (!empty($srcode)) 
		{
		$sel_sof	.=	"AND CUSTNO	IN ('$custlist') ";
		}
	}
	$sel_sof	.=	"ORDER BY CUSTNO ";
	//echo $sel_sof;exit();
	$rssel_sof	=	$Filstar_172->Execute($sel_sof);
	if ($rssel_sof==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_sof->EOF) 
	{
		$ORDERNO	=	$rssel_sof->fields['ORDERNO'];
		$CUSTNO		=	$rssel_sof->fields['CUSTNO'];
		$PICKLISTNO	=	$rssel_sof->fields['PICKLISTNO'];
		
		$OrderStatus	=	'';
		
		$CustName		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
		$sr__			=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","SalesRepCode","CustNo = '{$CUSTNO}' ");
		$sr___name		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","salesreps","SalesRepName","SalesRepCode = '{$sr__}' ");
		$InvoiceNo		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","InvoiceNo","OrderNo = '{$ORDERNO}' ");
		$OrderCategory	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","OrderCategory","OrderNo = '{$ORDERNO}' ");
		$OrderStatus	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","OrderStatus","OrderNo = '{$ORDERNO}' ");
		
		if (!empty($OrderStatus)) 
		{
		if ($OrderStatus=='For Picking') 
		{
			$status	=	'SOF FOR PICKING';
		}
		else if ($OrderStatus=='Confirmed' || $OrderStatus=='Invoiced') 
		{
			$status	=	'PLCONF NOT YET DISPATCH';
			
			$is_manila	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_METROMANILA_DTL","TRACKINGNO","SOFNO = '{$ORDERNO}' and STATUS != 'CANCELLED' ");
			if (empty($is_manila)) 
			{
				$is_province	=	$global_func->Select_val($Filstar_conn,"DISPATCH","DISPATCH_PROVINCE_DTL","TRACKINGNO","SOFNO = '{$ORDERNO}' and STATUS != 'CANCELLED' ");
				if (empty($is_province)) 
				{
					$status	=	'PLCONF NOT YET DISPATCH';
					$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$ORDERNO}' ");
					if (empty($is_confirm)) 
					{
						$status	=	'DISPATCH NOT YET CONF DELIVERY';
					}
					else 
					{
						$status	=	'CONF DELIVERY';
					}
				}
				else 
				{
					$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$ORDERNO}' ");
					if (empty($is_confirm)) 
					{
						$status	=	'DISPATCH NOT YET CONF DELIVERY';
					}
					else 
					{
						$status	=	'CONF DELIVERY';
					}
				}
			}
			else 
			{
				$is_confirm	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","CONFIRMDELIVERY_HDR","SOF","SOF = '{$ORDERNO}' ");
				if (empty($is_confirm)) 
				{
					$status	=	'DISPATCH NOT YET CONF DELIVERY';
				}
				else 
				{
					$status	=	'CONF DELIVERY';
				}
			}
		}
		
		$sr				=	$sr__."-".$sr___name;
		
		$sel_amount		=	"SELECT sum(GrossAmount)as gross ,sum(NetAmount)as net FROM orderdetail where OrderNo = '{$ORDERNO}' and isDeleted != 'Y' ";
		$rssel_amount	=	$Filstar_conn->Execute($sel_amount);
		if ($rssel_amount==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$gross	=	$rssel_amount->fields['gross'];
		$net	=	$rssel_amount->fields['net'];
		
		$total_net	+=	$net;
		$total_gross+=	$gross;
		
		if ($OrderCategory!='STF') 
		{
			$sCsv	.=	"$counter;$sr;$CUSTNO-$CustName;$ORDERNO;$PICKLISTNO;$InvoiceNo;;$net;$gross;$status";
			$sCsv	.=	"\n";
		}
		else 
		{
			$sCsv	.=	"$counter;$sr;$CUSTNO-$CustName;$ORDERNO;$PICKLISTNO;;$InvoiceNo;$net;$gross;$status";
			$sCsv	.=	"\n";
		}
		
		$counter++;
		}
		$rssel_sof->MoveNext();
	}
	$sCsv	.=		";;;;TOTAL;;;$total_net;$total_gross";
	
	$time__	=	date("Y-m-d H:i:s");
	
	$filename	=	"TRADE_DETAILS_".$type."_$time__".".csv";
	
	header("Content-type: application/x-msdownload");
	header("Content-Disposition: attachment; filename=$filename");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $sCsv;
	exit();
}


if ($_GET['action']=='FIND_SR') 
{
	
	$opt	=	$_GET['OPT__'];
	$val__	=	$_GET['OPT_VAL'];
	
	$sel_sr		=	"SELECT SalesRepCode,SalesRepName FROM salesreps where length(SalesRepCode) = '3' ";
	if ($opt=='srcode') 
	{
	$sel_sr	.=	"AND SalesRepCode LIKE '%{$val__}%' ";
	}
	else
	{
	$sel_sr	.=	"AND SalesRepName LIKE '%{$val__}%'	";
	}
	$sel_sr	.=	" limit 20 ";
	$rssel_sr	=	$Filstar_conn->Execute($sel_sr);
	$cnt	=	$rssel_sr->RecordCount();
	if ($cnt > 0)
	{
		echo "<select id=\"get_sr\" onkeypress=\"smartsel(event);\" multiple>";
		while (!$rssel_sr->EOF)
		{
			$RepCode	=	$rssel_sr->fields['SalesRepCode'];
			$RepName	=	$rssel_sr->fields['SalesRepName'];
			$cValue		=	$RepCode."|".$RepName;
			$show		=	$RepCode."-".$RepName;
			echo "<option value=\"$cValue\" onclick=\"smartsel('click');\">".$show."</option>";
			$rssel_sr->MoveNext();
		}
		echo "</select>";
	}
	else
	{
		echo "zero";
	}
	exit();
}


if ($_GET['action']=='FIND_CUST') 
{
	
	$opt		=	$_GET['OPT__'];
	$val__		=	$_GET['OPT_VAL'];
	$CUST_TYPE	=	$_GET['CUST_TYPE'];
	
	$sel_sr		=	"SELECT CustNo,CustName FROM custmast where length(CustNo) = '7' ";
	if ($opt=='custcode') 
	{
	$sel_sr	.=	"AND CustNo LIKE '%{$val__}%' ";
	}
	else
	{
	$sel_sr	.=	"AND CustName LIKE '%{$val__}%'	";
	}
	if ($CUST_TYPE=='NBS') 
	{
	$sel_sr	.=	"AND (CustomerBranchCode != '' && NBSnewBranchCode != '') ";
	}
	$sel_sr	.=	"AND CustStatus = 'A' limit 20 ";
	$rssel_sr	=	$Filstar_conn->Execute($sel_sr);
	$cnt	=	$rssel_sr->RecordCount();
	if ($cnt > 0)
	{
		echo "<select id=\"get_cust\" onkeypress=\"smartsel_2(event);\" multiple>";
		while (!$rssel_sr->EOF)
		{
			$RepCode	=	$rssel_sr->fields['CustNo'];
			$RepName	=	$rssel_sr->fields['CustName'];
			$cValue		=	$RepCode."|".$RepName;
			$show		=	$RepCode."-".$RepName;
			echo "<option value=\"$cValue\" onclick=\"smartsel_2('click');\">".$show."</option>";
			$rssel_sr->MoveNext();
		}
		echo "</select>";
	}
	else
	{
		echo "zero";
	}
	exit();
}

?>
<html>
<title>SEASON REPORT</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../../css/style.css);</style>
<style type="text/css">@import url(../../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../../calendar/calendar-setup.js"></script>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<style type="text/css">
body {
	font-size: 62.5%;
	font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
}

table {
	font-size: 1em;
}

.no-close .ui-dialog-titlebar-close {
    display: none;
}

.Text_header_value
{
	font:15px Dejavu Sans, arial, helvetica, sans-serif;
	font-weight:bold;
}
</style>
<script>
	function	report_type(val_id)
	{
		if(val_id == 'report_1')
		{
			$('#div_report_type').hide();
			$('#div_report_1').show();
		}
		else
		{
			$('#div_report_type').hide();
			$('#div_report_2').show();
		}
	}
	
	
	function	CreateReport_1()
	{
		var issummary	=	$('#reporttype_summary_1').is(":checked");
		var isdetailed	=	$('#reporttype_detailed_1').is(":checked");
		var isall		=	$('#reporttype2_all_1').is(":checked");
		var isnbs		=	$('#reporttype2_nbs_1').is(":checked");
		var istrade		=	$('#reporttype2_trade_1').is(":checked");
		var islist		=	$('#reporttype3_list_1').is(":checked");
		var selsearch	=	$('#SelBatch_1').val();
		var dfrom		=	$('#dfrom_1').val();
		var dto			=	$('#dto_1').val();
		
		if(isall==true)
		{
			var	opt			=	'ALL';
		}
		else if(isnbs==true)
		{
			var opt			=	'NBS';
		}
		else if(istrade==true)
		{
			var opt			=	'TRADE';
		}
		
		if(dfrom != '' || dto != '')
		{
			if(dfrom > dto)
			{
				alert('Invalid date range!');
				return;
			}
			
			if(dfrom == '' || dto == '')
			{
				alert('Invalid date range!');
				return;
			}
		}
		
		if(islist==true)
		{
			var url_		= 'reports_christmas_list.php?action=GENERATE&OPT='+selsearch+'&OPT2='+opt+'&DFROM='+dfrom+'&DTO='+dto;
		}
		else if(issummary==true)
		{
			var url_		= 'reports_christmas_summary.php?action=GENERATE&OPT='+selsearch+'&OPT2='+opt+'&DFROM='+dfrom+'&DTO='+dto;
			
		}
		else if(isdetailed==true)
		{
			var url_		= 'reports_christmas.php?action=GENERATE&OPT='+selsearch+'&OPT2='+opt+'&DFROM='+dfrom+'&DTO='+dtos;
		}
		window.open(url_);
	}
	
	function disabled__()
	{
		$('#reporttype_summary').attr('disabled', true);
		$('#reporttype_detailed').attr('disabled', true);
	}
	
	
	function	CreateReport(this_val)
	{
		var is_nbs	=	$('#reporttype2_nbs').is(":checked");
		if(is_nbs==true)
		{
			opt__	=	"GETDATA_NBS_"+this_val;
		}
		else
		{
			opt__	=	"GETDATA_TRADE_"+this_val;
		}
		
		var sel_year		=	$('#sel_year').val();
		var	sel_wave		=	$('#sel_wave').val();
		var	srcode			=	$('#srcode').val();
		var	custcode		=	$('#custcode').val();
		var	sel_producttype	=	$('#sel_producttype').val();
		
//		$.ajax({
//				url			:	'index_main.php?action='+opt__+'&SEL_YEAR='+sel_year+'&SEL_WAVE='+sel_wave+'&SRCODE='+srcode+'&SEL_PRODUCTTYPE='+sel_producttype,
//				beforeSend	:	function()
//							{
//								$('#divloader1').dialog('open');
//							},
//				success		:	function(response)
//							{
//								$('#divloader1').dialog('close');
//								$('#divresult').dialog('open');
//								$('#divresult').html(response);
////								$('#divresult_1').html(response);
////								$('#divresult_1').show();
//							}
//		});

		var	url_	=	'index_main.php?action='+opt__+'&SEL_YEAR='+sel_year+'&SEL_WAVE='+sel_wave+'&SRCODE='+srcode+'&SEL_PRODUCTTYPE='+sel_producttype+'&CUSTCODE='+custcode;
		window.open(url_);
	}
	
	function	selsr(evt,this_id)
	{
		if(this_id=='srcode')
		{
			var opt__	=	"srcode";
			var	opt_val	=	$('#srcode').val();
		}
		else
		{
			var opt__	=	"srname";
			var	opt_val	=	$('#srname').val();
		}
		
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(evthandler != 40 && evthandler != 13 && evthandler != 27)
		{
			if(opt_val != '')
			{
				$.ajax({
						url			:	'index_main.php?action=FIND_SR&OPT__='+opt__+'&OPT_VAL='+opt_val,
						beforeSend	:	function()
									{
										$('#loading').dialog('open');
									},
						success		:	function(response)
									{
										//alert(response);
										if(response=="zero")
										{
											alert('No record found...');
											$('#divsr').html('');
											$('#srcode').val('');
											$('#srname').val('');
										}
										else
										{
											$('#divsr').html(response);
											$('#divsr').show();
										}
									}
				});
			}
		}
		else if(evthandler == 40 && $('#divsr').html() != '')
		{
			$('#get_sr').focus();
		}
		else
		{
			$('#get_sr').html('');
		}
	}
	
	
	function	smartsel(evt)
	{
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		if(evt == 'click')
		{
			$('#hdnval').val($('#get_sr').val());
			var vx = $('#hdnval').val();
			var x = vx.split('|');
			$('#srcode').val(x[0]);
			$('#srname').val(x[1]);
			$('#divsr').html('');
		}
		else
		{
			if(evthandler == 13)
			{
				$('#hdnval').val($('#get_sr').val());
				var vx = $('#hdnval').val();
				var x = vx.split('|');
				$('#srcode').val(x[0]);
				$('#srname').val(x[1]);
				$('#divsr').html('');
			}
		}
	}
	
	function	selcust(evt,this_id)
	{
		
		var is_nbs	=	$('#reporttype2_nbs').is(":checked");
		if(is_nbs==true)
		{
			cust_type	=	"NBS";
		}
		else
		{
			cust_type	=	"TRADE";
		}
		
		if(this_id=='custcode')
		{
			var opt__	=	"custcode";
			var	opt_val	=	$('#custcode').val();
		}
		else
		{
			var opt__	=	"custname";
			var	opt_val	=	$('#custname').val();
		}
		
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(evthandler != 40 && evthandler != 13 && evthandler != 27)
		{
			if(opt_val != '')
			{
				$.ajax({
						url			:	'index_main.php?action=FIND_CUST&OPT__='+opt__+'&OPT_VAL='+opt_val+'&CUST_TYPE='+cust_type,
						beforeSend	:	function()
									{
										$('#loading').dialog('open');
									},
						success		:	function(response)
									{
										//alert(response);
										if(response=="zero")
										{
											alert('No record found...');
											$('#divcust').html('');
											$('#custcode').val('');
											$('#custname').val('');
										}
										else
										{
											$('#divcust').html(response);
											$('#divcust').show();
										}
									}
				});
			}
		}
		else if(evthandler == 40 && $('#divcust').html() != '')
		{
			$('#get_cust').focus();
		}
		else
		{
			$('#get_cust').html('');
		}
	}
	
	
	function	smartsel_2(evt)
	{
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		if(evt == 'click')
		{
			$('#hdnval_cust').val($('#get_cust').val());
			var vx = $('#hdnval_cust').val();
			var x = vx.split('|');
			$('#custcode').val(x[0]);
			$('#custname').val(x[1]);
			$('#divcust').html('');
		}
		else
		{
			if(evthandler == 13)
			{
				$('#hdnval_cust').val($('#get_cust').val());
				var vx = $('#hdnval_cust').val();
				var x = vx.split('|');
				$('#custcode').val(x[0]);
				$('#custname').val(x[1]);
				$('#divcust').html('');
			}
		}
	}
	
	function disabled__()
	{
		$('#reporttype_summary').attr('disabled', true);
		$('#reporttype_detailed').attr('disabled', true);
	}
</script>
</head>
<body>
	<div id="div_report_type">
		<form>
			<table width="60%" border="0" align="center">
				<tr>
					<td align="center">
						<input type="button" name="report_1" id="report_1" value="WAVE AMOUNT" onclick="report_type(this.id);" class="small_button" style="width:200px;height:50px;font-size:20px;">
						
					</td>
					<td align="center">
						<input type="button" name="report_2" id="report_2" value="PRODUCT TYPE" onclick="report_type(this.id);" class="small_button" style="width:200px;height:50px;font-size:20px;">
					</td>
				</tr>
			</table>
		</form>
	</div>
	<div id="div_report_1" style="display:none">
	<form name="dataform1" id="dataform1" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0" class="Text_header">
			<tr>
				<td width="35%" align="right">
					SUMMARY
				</td>
				<td width="5%" align="center">
					<input type="radio" name="reporttype_1" id="reporttype_summary_1" value="SUMMARY" checked >
				</td>
				<td width="10%" align="center">
					DETAILED
				</td>
				<td width="40%" align="left" colspan="3">
					<input type="radio" name="reporttype_1" id="reporttype_detailed_1" value="DETAILED">
				</td>
			</tr>
			<tr>
				<td width="35%" align="right">
					ALL
				</td>
				<td width="5%" align="center">
					<input type="radio" name="reporttype2_1" id="reporttype2_all_1" value="ALL" checked>
				</td>
				<td width="10%" align="center">
					NBS
				</td>
				<td width="5%" align="left">
					<input type="radio" name="reporttype2_1" id="reporttype2_nbs_1" value="NBS">
				</td>
				<td width="5%" align="center">
					TRADE
				</td>
				<td width="30%">
					<input type="radio" name="reporttype2_1" id="reporttype2_trade_1" value="TRADE">
				</td>
			</tr>
			<tr>
				<td width="30%" align="right">
					SOF LISTING
				</td>
				<td width="5%" align="center">
					<input type="radio" name="reporttype3_1" id="reporttype3_list_1" value="LIST" onclick="disabled__();">
				</td>	
				<td width="65%" align="left" colspan="4">
					&nbsp;
				</td>	
			</tr>
			<tr>
				<td width="30%" align="right">
					SOF DATE RANGE
				</td>
				<td width="70%" align="left" colspan="5">
					<input type="text" name="dfrom_1" id="dfrom_1" value="" placeholder="FROM" maxlength="10" align="center" class="dates">
					<input type="text" name="dto_1" id="dto_1" value="" placeholder="TO"   maxlength="10" align="center" class="dates">
				</td>	
			</tr>
			<tr>
				<td colspan="6" width="100%" align="center">
					<select id="SelBatch_1">
							<option value="1">WAVE 1</option>
							<option value="2">WAVE 2</option>
							<option value="3">WAVE 3</option>
						</select>
				</td>	
			</tr>
			<tr>
				<td colspan="6" align="center">
					<input type="button" name="btnreport_1" id="btnreport_1" value="Submit" class="small_button" onclick="CreateReport_1();">
				</td>
			</tr>
		</table>
	</form>
	</div>
	<div id="div_report_2" style="display:none;">
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0" class="Text_header">
			<tr>
				<td width="35%">
					&nbsp;
				</td>
				<td width="7%">
					&nbsp;
				</td>
				<td width="3%" align="center">
					<input type="radio" name="reporttype2" id="reporttype2_nbs" value="NBS" checked>
				</td>
				<td width="5%" align="left">
					NBS
				</td>
				<td width="3%" align="center">
					<input type="radio" name="reporttype2" id="reporttype2_trade" value="TRADE">
				</td>
				<td width="5%">
					TRADE
				</td>
				<td width="42%">
					&nbsp;
				</td>
			</tr>
			<tr>
				<td width="35%">
					&nbsp;
				</td>
				<td width="7%" align="left">
					YEAR
				</td>
				<td width="58%" colspan="5">
					&nbsp;:&nbsp;<select name="sel_year" id="sel_year">
						<option value="2016">2016</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="35%">
					&nbsp;
				</td>
				<td width="7%" align="left">
					WAVE
				</td>
				<td width="58%" colspan="5">
					&nbsp;:&nbsp;<select name="sel_wave" id="sel_wave">
						<option value="WAVE1">WAVE-1</option>
						<option value="WAVE2">WAVE-2</option>
						<option value="WAVE3">WAVE-3</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="35%">
					&nbsp;
				</td>
				<td width="7%" align="left">
					SR
				</td>
				<td width="58%" colspan="5">
					&nbsp;:&nbsp;<input type="text" name="srcode" id="srcode" value="" size="7" onkeyup="selsr(event,this.id);">
					<input type="text" name="srname" id="srname" value="" size="20" onkeyup="selsr(event,this.id);">
					<div id="divsr" style="position:absolute;"></div>
					<input type="hidden" id="hdnval" name="hdnval" value="">
					</select>
				</td>
			</tr>
			<tr>
				<td width="35%">
					&nbsp;
				</td>
				<td width="7%" align="left">
					CUSTOMER
				</td>
				<td width="58%" colspan="5">
					&nbsp;:&nbsp;<input type="text" name="custcode" id="custcode" value="" size="7" onkeyup="selcust(event,this.id);">
					<input type="text" name="custname" id="custname" value="" size="20" onkeyup="selcust(event,this.id);">
					<div id="divcust" style="position:absolute;"></div>
					<input type="hidden" id="hdnval_cust" name="hdnval_cust" value="">
					</select>
				</td>
			</tr>
			<tr>
				<td width="35%">
					&nbsp;
				</td>
				<td width="7%" align="left" nowrap>
					PRODUCT TYPE
				</td>
				<td width="58%" colspan="5">
					&nbsp;:&nbsp;<select name="sel_producttype" id="sel_producttype">
						<option value="CARDS">CARDS</option>
						<option value="NONCARDS">NONCARDS</option>
						<option value="GIFTBAG">GIFTBAG</option>
						<option value="ROLLEDWRAP">ROLLEDWRAP</option>
						<!--<option value="FLATWRAP">FLATWRAP</option>-->
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="7" align="center">
					<input type="button" name="btnreport" id="btnreport" value="SUMMARY" class="small_button" onclick="CreateReport(this.value);" style="width:100px;">
					<input type="button" name="btnreport" id="btnreport" value="DETAILS" class="small_button" onclick="CreateReport(this.value);" style="width:100px;" >
				</td>
			</tr>
		</table>
	</form>
	</div>
	<div id="divloader1" style="display:none;" align="center" title="LOADING">SEARCHING PLEASE WAIT<img src="../../../images/loading/ajax-loader_fast.gif"></div>
	<div id="divresult" style="display:none;" align="center" ></div>
	<div id="divresult_1" style="display:none;" align="center" ></div>
</body>
</html>
<script>
$(".dates").datepicker({ 
	dateFormat: 'yy-mm-dd',
	changeMonth: true,
    changeYear: true 
});

$("#divresult").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	bgiframe:true, resizable:false, height: 500, width: 1000, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		}
	}
});


$("#divloader1").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	bgiframe:true, resizable:false, height: 100, width: 300, modal:true, autoOpen: false,	draggable: false
});
</script>