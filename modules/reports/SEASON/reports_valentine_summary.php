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
		$hdr		=	"ORDERHEADER";
		$dtl		=	"ORDERDETAIL";
		$Filename	=	"WAVE_1";
	}
	else if ($_GET['OPT']==2) 
	{
		$hdr		=	"ORDERHEADER_batch2";
		$dtl		=	"ORDERDETAIL_batch2";
		$Filename	=	"WAVE_2";
	}
	else 
	{
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
	//$sel_order_hdr_sof	=	"SELECT ORDERNO FROM VALENTINE_2016.$hdr WHERE ORDERSTATUS='For Picking' ";
	if ($_GET['OPT2'] != 'ALL')
	{
		$sel_order_hdr_sof	=	"SELECT ORDERNO FROM VALENTINE_2016.$hdr WHERE ORDERSTATUS='For Picking' AND CUSTNO in ('{$custlist}')";
	}
	else {
		$sel_order_hdr_sof	=	"SELECT ORDERNO FROM VALENTINE_2016.$hdr WHERE ORDERSTATUS='For Picking' ";
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
	
	$sel_order_dtl_sof	=	"SELECT SUM(ORDERQTY)as QTY,SUM(GROSSAMOUNT)as GROSS,SUM(NETAMOUNT)as NET FROM VALENTINE_2016.$dtl WHERE ORDERNO IN('{$SOFLIST}')";
	$rssel_order_dtl_sof=	$Filstar_172->Execute($sel_order_dtl_sof);
	if ($rssel_order_dtl_sof==false) 
	{
		echo $Filstar_172->ErrorMsg()."::".__LINE__;exit();
	}
	$QTY	=	$rssel_order_dtl_sof->fields['QTY'];
	$GROSS	=	$rssel_order_dtl_sof->fields['GROSS'];
	$NET	=	$rssel_order_dtl_sof->fields['NET'];
	
	$aAmount[$opt1]['QTY']	=	$QTY;
	$aAmount[$opt1]['GROSS']=	$GROSS;
	$aAmount[$opt1]['NET']	=	$NET;
	
	//print_r($aAmount);exit();
	/**
	 * FOR PICKING ORDERNO
	 */
	//$sel_order_172		=	"SELECT ORDERNO FROM VALENTINE_2016.$hdr WHERE ORDERSTATUS = 'For Picking' and PICKLISTNO != '' ";
	if ($_GET['OPT2'] != 'ALL') 
	{
		$sel_order_172		=	"SELECT ORDERNO FROM VALENTINE_2016.$hdr WHERE ORDERSTATUS = 'For Picking' and PICKLISTNO != '' AND CUSTNO in ('{$custlist}') ";
	}
	else 
	{
		$sel_order_172		=	"SELECT ORDERNO FROM VALENTINE_2016.$hdr WHERE ORDERSTATUS = 'For Picking' and PICKLISTNO != '' ";
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
	
	if ($rssel_order_hdr_10->RecordCount() > 0) 
	{
		$sel_order_dtl_10	=	"SELECT SUM(ReleaseQty)AS QTY,SUM(GrossAmount)AS GROSS,SUM(NetAmount)AS NET from $dtl10 WHERE isDeleted = 'N' AND OrderNo IN ('{$container2}')";
		$rssel_order_dtl_10	=	$Filstar_conn->Execute($sel_order_dtl_10);
		if ($rssel_order_dtl_10==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$QTY	=	$rssel_order_dtl_10->fields['QTY'];
		$GROSS	=	$rssel_order_dtl_10->fields['GROSS'];
		$NET	=	$rssel_order_dtl_10->fields['NET'];
		
		
		$aAmount[$opt2]['QTY']	=	$QTY;
		$aAmount[$opt2]['GROSS']=	$GROSS;
		$aAmount[$opt2]['NET']	=	$NET;
	}
	else 
	{
		$aAmount[$opt2]['QTY']	=	0;
		$aAmount[$opt2]['GROSS']=	0;
		$aAmount[$opt2]['NET']	=	0;
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
	
	if ($rssel_order_hdr_10->RecordCount() > 0) 
	{
		$sel_order_dtl_10	=	"SELECT SUM(ReleaseQty)AS QTY,SUM(GrossAmount)AS GROSS,SUM(NetAmount)AS NET from $dtl10 NET WHERE isDeleted = 'N' AND OrderNo IN ('{$container3}')";
		$rssel_order_dtl_10	=	$Filstar_conn->Execute($sel_order_dtl_10);
		if ($rssel_order_dtl_10==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$QTY	=	$rssel_order_dtl_10->fields['QTY'];
		$GROSS	=	$rssel_order_dtl_10->fields['GROSS'];
		$NET	=	$rssel_order_dtl_10->fields['NET'];
		
		
		$aAmount[$opt3]['QTY']	=	$QTY;
		$aAmount[$opt3]['GROSS']=	$GROSS;
		$aAmount[$opt3]['NET']	=	$NET;
	}
	else 
	{
		$aAmount[$opt3]['QTY']	=	0;
		$aAmount[$opt3]['GROSS']=	0;
		$aAmount[$opt3]['NET']	=	0;
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
	
	if ($rssel_order_hdr_10->RecordCount() > 0) 
	{
		$sel_order_dtl_10	=	"SELECT SUM(ReleaseQty)AS QTY,SUM(GrossAmount)AS GROSS,SUM(NetAmount)AS NET from $dtl10 NET WHERE isDeleted = 'N' AND OrderNo IN ('{$container4}')";
		$rssel_order_dtl_10	=	$Filstar_conn->Execute($sel_order_dtl_10);
		if ($rssel_order_dtl_10==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$QTY	=	$rssel_order_dtl_10->fields['QTY'];
		$GROSS	=	$rssel_order_dtl_10->fields['GROSS'];
		$NET	=	$rssel_order_dtl_10->fields['NET'];
		
		
		$aAmount[$opt4]['QTY']	=	$QTY;
		$aAmount[$opt4]['GROSS']=	$GROSS;
		$aAmount[$opt4]['NET']	=	$NET;
	}
	else 
	{
		$aAmount[$opt4]['QTY']	=	0;
		$aAmount[$opt4]['GROSS']=	0;
		$aAmount[$opt4]['NET']	=	0;
	}
	
	$cCSV	.=	"GENERATED DATE ".date('Y-m-d H:i:s');
	$cCSV	.=	"\n";
	$cCSV	.=	"\n";
	foreach ($aAmount as $opt=>$val__)
	{
		$cCSV	.=	$opt."\n";
		$QTY	=	0;
		$GROSS	=	0;
		$NET	=	0;
		
		$QTY	=	number_format($val__['QTY'],2);
		$GROSS	=	number_format($val__['GROSS'],2);
		$NET	=	number_format($val__['NET'],2);
		$cCSV	.=	"ORDERQTY;$QTY"."\n";
		$cCSV	.=	"GROSSAMOUNT;$GROSS"."\n";
		$cCSV	.=	"NETAMOUNT;$NET"."\n";
		$cCSV	.=	"\n";
	}
	
	header("Content-type: text/csv");
	header("Content-Transfer-Encoding: UTF-8");
	header("Content-Disposition: attachment; filename=SUMMARY_$Filename.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $cCSV;

?>