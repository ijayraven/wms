<?php
	/**
	 * Author		:	Raymond A. Galaroza
	 * Date Created	:	2015-09-28
	 */
	session_start();
	set_time_limit(0);
	include($_SERVER['DOCUMENT_ROOT'].'/wms/include/config/consolidator.php');
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
	if (empty($_SESSION['username'])) 
	{
		echo "<script>alert('You dont have a session!');</script>";
		echo "<script>location='../../index.php'</script>";
	}
	
	$Filstar_172	=	ADONewConnection("mysqlt");
		
	$dbFilstar_172	=	$Filstar_172->Connect('192.168.250.172','root','');
	if ($dbFilstar_172 == false) 
	{
		echo "<script>alert('Error Occurred no Database Connection!');</script>";
		echo "<script>location = 'index.php'</script>";
	}
	
	
	if ($_GET['OPT']==1) 
	{
		$db__		=	"CHRISTMAS_2016";
		$hdr		=	"ORDERHEADER_WAVE1";
		$dtl		=	"ORDERDETAIL_WAVE1";
		$Filename	=	"WAVE_1";
	}
	else if ($_GET['OPT']==2) 
	{
		$db__		=	"CHRISTMAS_2016";
		$hdr		=	"ORDERHEADER";
		$dtl		=	"ORDERDETAIL";
		$Filename	=	"WAVE_2";
	}
	else 
	{
		$db__		=	"CHRISTMAS_2016";
		$hdr		=	"ORDERHEADER_batch3";
		$dtl		=	"ORDERDETAIL_batch3";
		$Filename	=	"WAVE_3";
	}
	
		$hdr10		=	"orderheader";
		$dtl10		=	"orderdetail";
	
	$aData			=	array();
	$aData2			=	array();
	$aData3			=	array();
	$aData4			=	array(); 
	$aAmount		=	array();
	$opt1			=	"SOF";
	$opt2			=	"PL";
	$opt3			=	"INVOICE";
	$opt4			=	"CONFIRMED";
	$container		=	"";
	$container2		=	"";
	$container3		=	"";
	$container4		=	"";
	
	$DFROM			=	$_GET['DFROM'];
	$DTO			=	$_GET['DTO'];
	
	if ($_GET['OPT2']=='NBS') 
	{
		$sel_nbs	=	"SELECT `CustNo` FROM `custmast` WHERE `CustomerBranchCode` != '' ";
		$rssel_nbs	=	$Filstar_conn->Execute($sel_nbs);
		if ($rssel_nbs==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_nbs->EOF) {
			$CustNo	=	$rssel_nbs->fields['CustNo'];
			$aCustomer[]	=	$CustNo;
			$rssel_nbs->MoveNext();
		}
		$custlist	=	implode("','",$aCustomer);
	}
	elseif ($_GET['OPT2']=='TRADE')
	{
		$sel_nbs	=	"SELECT `CustNo` FROM `custmast` WHERE `CustomerBranchCode` = '' ";
		$rssel_nbs	=	$Filstar_conn->Execute($sel_nbs);
		if ($rssel_nbs==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_nbs->EOF) {
			$CustNo	=	$rssel_nbs->fields['CustNo'];
			$aCustomer[]	=	$CustNo;
			$rssel_nbs->MoveNext();
		}
		$custlist	=	implode("','",$aCustomer);
	}
	
	
	/**
	 * FOR SOF
	 */
	//$sel_order_dtl_sof	=	"SELECT CUSTNO,SUM(ORDERQTY)as QTY,SUM(GROSSAMOUNT)as GROSS,SUM(NETAMOUNT)as NET FROM $db__.$dtl WHERE CUSTNO IN ('{$custlist}') GROUP BY CUSTNO ";
	if ($_GET['OPT2'] != 'ALL')
	{
		$sel_order_hdr_sof	=	"SELECT ORDERNO FROM $db__.$hdr WHERE ORDERSTATUS='For Picking' AND CUSTNO in ('{$custlist}')";
		if (!empty($DFROM) && !empty($DTO))
		{
			$sel_order_hdr_sof	.=	"AND ORDERDATE BETWEEN '{$DFROM}' AND '{$DTO}' ";
		}
	}
	else {
		$sel_order_hdr_sof	=	"SELECT ORDERNO FROM $db__.$hdr WHERE ORDERSTATUS='For Picking' ";
		if (!empty($DFROM) && !empty($DTO))
		{
			$sel_order_hdr_sof	.=	"AND ORDERDATE BETWEEN '{$DFROM}' AND '{$DTO}' ";
		}
	}
	
	$rssel_order_hdr_sof=	$Filstar_172->Execute($sel_order_hdr_sof);
	if ($rssel_order_hdr_sof==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_order_hdr_sof->EOF) 
	{
		$aSOF[]	=	$rssel_order_hdr_sof->fields['ORDERNO'];
		$rssel_order_hdr_sof->MoveNext();
	}
	$SOFLIST	=	implode("','",$aSOF);
	
	$sel_order_dtl_sof	=	"SELECT CUSTNO,SUM(ORDERQTY)as QTY,SUM(GROSSAMOUNT)as GROSS,SUM(NETAMOUNT)as NET FROM $db__.$dtl WHERE ORDERNO IN('{$SOFLIST}') GROUP BY CUSTNO ";
	$rssel_order_dtl_sof=	$Filstar_172->Execute($sel_order_dtl_sof);
	if ($rssel_order_dtl_sof==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_order_dtl_sof->EOF) 
	{
		$CUSTNO	=	$rssel_order_dtl_sof->fields['CUSTNO'];
		$QTY	=	$rssel_order_dtl_sof->fields['QTY'];
		$GROSS	=	$rssel_order_dtl_sof->fields['GROSS'];
		$NET	=	$rssel_order_dtl_sof->fields['NET'];
		
		$sel_salerp		=	"SELECT SalesRepCode from custmast where CustNo = '{$CUSTNO}' ";
		$rssel_salerp	=	$Filstar_conn->Execute($sel_salerp);
		if ($rssel_salerp==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$SalesRep		=	$rssel_salerp->fields['SalesRepCode'];
		if (!empty($SalesRep))
		{
			$aAmount[$SalesRep][$CUSTNO][$opt1]['QTY']	=	$QTY;
			$aAmount[$SalesRep][$CUSTNO][$opt1]['GROSS']=	$GROSS;
			$aAmount[$SalesRep][$CUSTNO][$opt1]['NET']	=	$NET;
			
			$aAmount[$SalesRep][$CUSTNO][$opt2]['QTY']	=	0;
			$aAmount[$SalesRep][$CUSTNO][$opt2]['GROSS']=	0;
			$aAmount[$SalesRep][$CUSTNO][$opt2]['NET']	=	0;
			
			$aAmount[$SalesRep][$CUSTNO][$opt3]['QTY']	=	0;
			$aAmount[$SalesRep][$CUSTNO][$opt3]['GROSS']=	0;
			$aAmount[$SalesRep][$CUSTNO][$opt3]['NET']	=	0;
			
			$aAmount[$SalesRep][$CUSTNO][$opt4]['QTY']	=	0;
			$aAmount[$SalesRep][$CUSTNO][$opt4]['GROSS']=	0;
			$aAmount[$SalesRep][$CUSTNO][$opt4]['NET']	=	0;
		}
		
		$rssel_order_dtl_sof->MoveNext();
	}
	
	
	//print_r($aAmount);exit();
	/**
	 * FOR PICKING ORDERNO
	 */
	if ($_GET['OPT2'] != 'ALL') 
	{
		$sel_order_172		=	"SELECT ORDERNO FROM $db__.$hdr WHERE ORDERSTATUS = 'For Picking' and PICKLISTNO != '' AND CUSTNO in ('{$custlist}') ";
		if (!empty($DFROM) && !empty($DTO))
		{
			$sel_order_172	.=	"AND ORDERDATE BETWEEN '{$DFROM}' AND '{$DTO}' ";
		}
	}
	else 
	{
		$sel_order_172		=	"SELECT ORDERNO FROM $db__.$hdr WHERE ORDERSTATUS = 'For Picking' and PICKLISTNO != '' ";
		if (!empty($DFROM) && !empty($DTO))
		{
			$sel_order_172	.=	"AND ORDERDATE BETWEEN '{$DFROM}' AND '{$DTO}' ";
		}
	}
	
	$rssel_order_172	=	$Filstar_172->Execute($sel_order_172);
	if ($rssel_order_172==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_order_172->EOF) 
	{
		$aData[]	=	$rssel_order_172->fields['ORDERNO'];
		$rssel_order_172->MoveNext();
	}
	$container		=	implode("','",$aData);
	
	$sel_order_hdr_10	=	"SELECT OrderNo FROM $hdr10 WHERE OrderStatus = 'For Picking' and OrderNo in ('{$container}') ";
	$rssel_order_hdr_10 =	$Filstar_conn->Execute($sel_order_hdr_10);
	if ($rssel_order_hdr_10==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_order_hdr_10->EOF)
	{
		$aData2[]	=	$rssel_order_hdr_10->fields['OrderNo'];
		$rssel_order_hdr_10->MoveNext();
	}
	$container2		=	implode("','",$aData2);
	
	$sel_order_dtl_10	=	"SELECT CustNo,SUM(ReleaseQty)AS QTY,SUM(GrossAmount)AS GROSS,SUM(NetAmount)AS NET from $dtl10 WHERE isDeleted = 'N' AND OrderNo IN ('{$container2}') group by CustNo";
	$rssel_order_dtl_10	=	$Filstar_conn->Execute($sel_order_dtl_10);
	if ($rssel_order_dtl_10==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_order_dtl_10->EOF) 
	{
		$CustNo		=	$rssel_order_dtl_10->fields['CustNo'];
		$QTY		=	$rssel_order_dtl_10->fields['QTY'];
		$GROSS		=	$rssel_order_dtl_10->fields['GROSS'];
		$NET		=	$rssel_order_dtl_10->fields['NET'];
		
		$sel_salerp		=	"SELECT SalesRepCode from custmast where CustNo = '{$CustNo}' ";
		$rssel_salerp	=	$Filstar_conn->Execute($sel_salerp);
		if ($rssel_salerp==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$SalesRep		=	$rssel_salerp->fields['SalesRepCode'];
		
		if (!empty($SalesRep))
		{
		$aAmount[$SalesRep][$CustNo][$opt2]['QTY']	=	$QTY;
		$aAmount[$SalesRep][$CustNo][$opt2]['GROSS']=	$GROSS;
		$aAmount[$SalesRep][$CustNo][$opt2]['NET']	=	$NET;
		}
		
		$rssel_order_dtl_10->MoveNext();
	}
	
	/**
	 * FOR INVOICE ORDER
	 */
	$sel_order_hdr_10	=	"SELECT OrderNo FROM $hdr10 WHERE OrderStatus = 'Invoiced' and OrderNo in ('{$container}') ";
	$rssel_order_hdr_10 =	$Filstar_conn->Execute($sel_order_hdr_10);
	if ($rssel_order_hdr_10==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_order_hdr_10->EOF)
	{
		$aData3[]	=	$rssel_order_hdr_10->fields['OrderNo'];
		$rssel_order_hdr_10->MoveNext();
	}
	$container3		=	implode("','",$aData3);
	
	$sel_order_dtl_10	=	"SELECT CustNo,SUM(ReleaseQty)AS QTY,SUM(GrossAmount)AS GROSS,SUM(NetAmount)AS NET from $dtl10 NET WHERE isDeleted = 'N' AND OrderNo IN ('{$container3}') group by CustNo";
	$rssel_order_dtl_10	=	$Filstar_conn->Execute($sel_order_dtl_10);
	if ($rssel_order_dtl_10==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_order_dtl_10->EOF) 
	{
		$CustNo		=	$rssel_order_dtl_10->fields['CustNo'];
		$QTY		=	$rssel_order_dtl_10->fields['QTY'];
		$GROSS		=	$rssel_order_dtl_10->fields['GROSS'];
		$NET		=	$rssel_order_dtl_10->fields['NET'];
		
		$sel_salerp		=	"SELECT SalesRepCode from custmast where CustNo = '{$CustNo}' ";
		$rssel_salerp	=	$Filstar_conn->Execute($sel_salerp);
		if ($rssel_salerp==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$SalesRep		=	$rssel_salerp->fields['SalesRepCode'];
		
		if (!empty($SalesRep))
		{
		$aAmount[$SalesRep][$CustNo][$opt3]['QTY']	=	$QTY;
		$aAmount[$SalesRep][$CustNo][$opt3]['GROSS']=	$GROSS;
		$aAmount[$SalesRep][$CustNo][$opt3]['NET']	=	$NET;
		}
		
		$rssel_order_dtl_10->MoveNext();
	}
	
	
	/**
	 * CONFIRMED
	 */
	$sel_order_hdr_10	=	"SELECT OrderNo FROM $hdr10 WHERE OrderStatus = 'Confirmed' and OrderNo in ('{$container}') ";
	$rssel_order_hdr_10 =	$Filstar_conn->Execute($sel_order_hdr_10);
	if ($rssel_order_hdr_10==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_order_hdr_10->EOF)
	{
		$aData4[]	=	$rssel_order_hdr_10->fields['OrderNo'];
		$rssel_order_hdr_10->MoveNext();
	}
	$container4		=	implode("','",$aData4);
	
	$sel_order_dtl_10	=	"SELECT CustNo,SUM(ReleaseQty)AS QTY,SUM(GrossAmount)AS GROSS,SUM(NetAmount)AS NET from $dtl10 NET WHERE isDeleted = 'N' AND OrderNo IN ('{$container4}') group by CustNo";
	$rssel_order_dtl_10	=	$Filstar_conn->Execute($sel_order_dtl_10);
	if ($rssel_order_dtl_10==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_order_dtl_10->EOF) 
	{
		$CustNo		=	$rssel_order_dtl_10->fields['CustNo'];
		$QTY		=	$rssel_order_dtl_10->fields['QTY'];
		$GROSS		=	$rssel_order_dtl_10->fields['GROSS'];
		$NET		=	$rssel_order_dtl_10->fields['NET'];
		
		$sel_salerp		=	"SELECT SalesRepCode from custmast where CustNo = '{$CustNo}' ";
		$rssel_salerp	=	$Filstar_conn->Execute($sel_salerp);
		if ($rssel_salerp==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$SalesRep		=	$rssel_salerp->fields['SalesRepCode'];
		
		if (!empty($SalesRep))
		{
		$aAmount[$SalesRep][$CustNo][$opt4]['QTY']	=	$QTY;
		$aAmount[$SalesRep][$CustNo][$opt4]['GROSS']=	$GROSS;
		$aAmount[$SalesRep][$CustNo][$opt4]['NET']	=	$NET;
		}
		
		$rssel_order_dtl_10->MoveNext();
	}
	//print_r($aAmount);exit();
	
	$cCSV	.=	"GENERATED DATE ".date('Y-m-d H:i:s');
	$cCSV	.=	"\n";
	$cCSV	.=	"\n";
	
	foreach ($aAmount as $sr_code=>$val_custno) 
	{
		$atotal	=	array();
		$sel_sr	=	"SELECT SalesRepName FROM `salesreps` where SalesRepCode = '{$sr_code}' ";
		$rssel_r=	$Filstar_conn->Execute($sel_sr);
		if ($rssel_r==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$srname	=	$rssel_r->fields['SalesRepName'];
		$cCSV	.=	$sr_code."-".$srname."\n";
		foreach ($val_custno as $key_custno=>$val_opt)
		{
			$sel_custname	=	"SELECT CustName FROM custmast where CustNo = '{$key_custno}' ";
			$rssel_custname	=	$Filstar_conn->Execute($sel_custname);
			if ($rssel_custname==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			$CustName		=	$rssel_custname->fields['CustName'];
			$cCSV	.=	";$key_custno-$CustName"."\n";
			foreach ($val_opt as $opt=>$val__)
			{
				$cCSV	.=	";;$opt"."\n";
				$QTY	=	number_format($val__['QTY'],2);
				$GROSS	=	number_format($val__['GROSS'],2);
				$NET	=	number_format($val__['NET'],2);
				$cCSV	.=	";;ORDERQTY;$QTY"."\n";
				$cCSV	.=	";;GROSSAMOUNT;$GROSS"."\n";
				$cCSV	.=	";;NETAMOUNT;$NET"."\n";
				$cCSV	.=	"\n";
				
				$atotal[$opt]['qty']	+=	$val__['QTY'];
				$atotal[$opt]['gross']	+=	$val__['GROSS'];
				$atotal[$opt]['net']	+=	$val__['NET'];
			}
		}
		foreach ($atotal as $key_opt=>$val__)
		{
			$cCSV	.=	";TOTAL;$key_opt"."\n";
			$cCSV	.=	";;QTY;".$val__['qty']."\n";
			$cCSV	.=	";;GROSS AMOUNT;".$val__['gross']."\n";
			$cCSV	.=	";;NET AMOUNT;".$val__['net']."\n";
			$cCSV	.=	"\n";
		}
		unset($atotal);
	}
	
	header("Content-type: text/csv");
	header("Content-Transfer-Encoding: UTF-8");
	header("Content-Disposition: attachment; filename=DETAILED_$Filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $cCSV;

?>