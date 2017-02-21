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
	if ($_GET['OPT2'] != 'ALL')
	{
		$sel_order_hdr_sof	=	"SELECT ORDERNO FROM VALENTINE_2015.$hdr WHERE ORDERSTATUS='For Picking' AND CUSTNO in ('{$custlist}')";
	}
	else {
		$sel_order_hdr_sof	=	"SELECT ORDERNO FROM VALENTINE_2015.$hdr WHERE ORDERSTATUS='For Picking' ";
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
	
	//print_r($aSOF);exit();

	$TOTAL1	=	0;
	$TOTAL2	=	0;
	$TOTAL3	=	0;
	$TOTAL4	=	0;
	$TOTAL5	=	0;
	$cCSV	.=	"INVOICE FORMAT;CUSTOMER;SOFNO;ORDERDATE;PLNO;PL DATE;INVNO;TOTAL RELEASE QTY; REC;GROSS;NET;STATUS"."\n";
	foreach ($aSOF as $key_sof)
	{
		$sel_hdr 	=	"SELECT OrderCategory,OrderStatus,CustNo,PickListNo,InvoiceNo,OrderDate,PickListDate from orderheader where OrderNo = '{$key_sof}' ";
		$rssel_hdr	=	$Filstar_conn->Execute($sel_hdr);
		if ($rssel_hdr==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		if($rssel_hdr->RecordCount() > 0)
		{
			while (!$rssel_hdr->EOF) 
			{
				//$OrderCategory	=	$rssel_hdr->fields['OrderCategory'];
				$OrderStatus	=	$rssel_hdr->fields['OrderStatus'];
				$CustNo			=	$rssel_hdr->fields['CustNo'];
				$PickListNo		=	$rssel_hdr->fields['PickListNo'];
				$InvoiceNo		=	$rssel_hdr->fields['InvoiceNo'];
				$OrderDate		=	$rssel_hdr->fields['OrderDate'];
				$PickListDate	=	$rssel_hdr->fields['PickListDate'];
				
				$type			=	substr($CustNo,-1,1);
				if ($type == 'C') 
				{
					$OrderCategory	=	'STF';
				}
				else 
				{
					$OrderCategory	=	'INVOICE';
				}
				
				$sel_dtl		=	"SELECT COUNT(Item)AS ITEM,sum(OrderQty)as QTY,sum(ReleaseQty)as QTY2,sum(GrossAmount)as GROSS,sum(NetAmount)as NET
									 FROM orderdetail where OrderNo = '{$key_sof}' and isDeleted = 'N'";
				$rssel_dtl		=	$Filstar_conn->Execute($sel_dtl);
				if ($rssel_dtl==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				$count	=	$rssel_dtl->fields['ITEM'];
				$QTY	=	$rssel_dtl->fields['QTY'];
				$QTY2	=	$rssel_dtl->fields['QTY2'];
				$GROSS	=	$rssel_dtl->fields['GROSS'];
				$NET	=	$rssel_dtl->fields['NET'];
				
				$sel_custname	=	"SELECT CustName FROM custmast where CustNo = '{$CustNo}' ";
				$rssel_custname	=	$Filstar_conn->Execute($sel_custname);
				if ($rssel_custname==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				$CustName		=	$rssel_custname->fields['CustName'];
				
				$cCSV	.=	"$OrderCategory;$CustNo-$CustName;$key_sof;$OrderDate;$PickListNo;$PickListDate;$InvoiceNo;$QTY2;$count;$GROSS;$NET;$OrderStatus";
				$cCSV	.=	"\n";
				
				$TOTAL1	+=	$QTY2;
				$TOTAL2	+=	$count;
				$TOTAL3	+=	$GROSS;
				$TOTAL4	+=	$NET;
				$rssel_hdr->MoveNext();
			}
		}
	}
	$cCSV	.=	";;;;;;;$TOTAL1;$TOTAL2;$TOTAL3;$TOTAL4";
	
	$FILE	=	$Filename."_".$_GET['OPT2'];
	
	header("Content-type: text/csv");
	header("Content-Transfer-Encoding: UTF-8");
	header("Content-Disposition: attachment; filename=$FILE.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $cCSV;
?>