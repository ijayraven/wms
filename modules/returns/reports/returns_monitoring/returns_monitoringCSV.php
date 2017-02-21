<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}

$output		.=	"FILSTAR DISTRIBUTORS CORPORATION"."\n";
$output		.=	"RETURNS MONITORING - CREATED MPOS"."\n";
if($_SESSION["mposdfrom"] != "")
{
	$output	.=	"MPOS DATE: ".$_SESSION['mposdfrom']." to ".$_SESSION['mposdto']."\n";;
}
if($_SESSION["scandfrom"] != "")
{
	$output	.=  "SCAN DATE: ".$_SESSION['scandfrom']." to ".$_SESSION['scandto']."\n";;
}
$output		.=	"REASON;".$_SESSION['REASON']."\n";
$output		.=	"CLASS;".$_SESSION['CLASS']."\n";
$output		.=	"BRAND;".$_SESSION['BRAND']."\n";
$output		.=	"\n\n";
$output		.=	"LINE;TRANSACTION;CUSTOMER;MPOS NO.;MPOS DATE;TABLET DATE;QUANTITY;GROSS AMOUNT;NET AMOUNT"."\n";

$arrMpos	=	$_SESSION["arrMPOS"];
$cnt		=	1;
$GRANDTOTAL	=	0;
$GRANDNET	=	0;
$GRANDQTY	=	0;
foreach ($arrMpos as $custno=>$val1)
{
	foreach ($val1 as $trxno=>$val2)
	{
		foreach ($val2 as $mpos=>$val3)
		{
			foreach ($val3 as $brand=>$val4)
			{
				foreach ($val4 as $class=>$val5)
				{
					$CustName	=	$val5["CustName"];
					$MPOSDATE	=	$val5["MPOSDATE"];
					$SCANDATE	=	$val5["SCANDATE"];
					$GROSSAMOUNT=	$val5["GROSSAMOUNT"];
					$NETAMOUNT	=	$val5["NETAMOUNT"];
					$BRANDNAME	=	$val5["BRANDNAME"];
					$CLASS_		=	$class;
					$STATUS		=	$val5["STATUS"];
					$REASON		=	$val5["REASON"];
					$TOTALQTY	=	$val5["TOTALQTY"];
					
					$tablet_date=	$global_func->Select_val($Filstar_conn,"WMS_NEW","MPOSHDR","SCANDATE","TRANSNO = '{$trxno}' and MPOSNO = '{$mpos}' ");
					
					$custname_	=	substr($CustName,0,45);
					$gross_amt	=	number_format($GROSSAMOUNT,2);
					$net_amt	=	number_format($NETAMOUNT,2);
					
					$output		.=	"$cnt;$trxno;$custname_;$mpos;$MPOSDATE;$tablet_date;$TOTALQTY;$gross_amt;$net_amt"."\n";
					
					$GRANDTOTAL	+=	$GROSSAMOUNT;
					$GRANDNET	+=	$NETAMOUNT;
					$GRANDQTY	+=	$TOTALQTY;
					$cnt++;
				}
			}
		}
	}
}

$output		.=	";;;;;TOTAL;$GRANDQTY;$GRANDTOTAL;$GRANDNET"."\n";

header("Content-Disposition: attachment; filename=mpos_return_monitoring_created_mpos.csv");
header("Content-Location: $_SERVER[REQUEST_URI]");
header("Content-Type: text/plain");
header("Expires: 0");
echo $output;
?>