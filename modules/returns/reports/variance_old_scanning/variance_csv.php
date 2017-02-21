<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}
$output	.=	";FILSTAR DISTRIBUTORS CORPORATION\r";			
$output	.=	";VARIANCE REPORT\r\r";			
			
$output	.=	"Line;Customer;MPOS No.;MPOS Date;Posted Date;Reason;MPOS Qty;Posted Qty;MPOS Amt;Posted Amt\r";

$arrMpos	=	$_SESSION["arrVAR"];
$cnt		=	1;
$GRANDMPOSTOTAL	=	0;
$GRANDPSTDTOTAL	=	0;
$GRANDMPOSQTY	=	0;
$GRANDPSTDQTY	=	0;
foreach($arrMpos as $MPOSNO=>$val1)
{
	$CUSTNO		= $val1["CUSTNO"];
	$MPOSDATE	= $val1["MPOSDATE"];
	$POSTEDDATE	= $val1["POSTEDDATE"];
	$SALESREPNO	= $val1["SALESREPNO"];
	$REASON		= $val1["REASON"];
	$TOTALQTY	= $val1["TOTALQTY"];
	$GROSSAMOUNT= $val1["GROSSAMOUNT"];
	$POSTEDQTY	= $val1["POSTEDQTY"];
	$POSTEDAMT	= $val1["POSTEDAMT"];

	$output	.=	"$cnt;$CUSTNO;$MPOSNO;$MPOSDATE;$POSTEDDATE;$REASON;$TOTALQTY;$POSTEDQTY;$GROSSAMOUNT;$POSTEDAMT\r\r";
		
	$GETMPOSDTLS	=	"SELECT * FROM WMS_NEW.MPOSDTL WHERE MPOSNO = '{$MPOSNO}'";
	$RSGETMPOSDTLS	=	$Filstar_conn->Execute($GETMPOSDTLS);
	if($RSGETMPOSDTLS == false)
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		$totqty	=	0;
		$totamt	=	0;
		$totsqty	=	0;
		$totsamt	=	0;
		$output .=	";;;;SKU No.;SKU Description;MPOS Qty;Posted Qty;Mpos Amount;Posted Amount\r";
		while (!$RSGETMPOSDTLS->EOF) {
			$SKUNO		=	$RSGETMPOSDTLS->fields["SKUNO"];
			$SKUNODESC	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}'");
			$QTY		=	$RSGETMPOSDTLS->fields["QTY"];
			$UNITPRICE	=	$RSGETMPOSDTLS->fields["UNITPRICE"];
			$GROSSAMOUNT=	$RSGETMPOSDTLS->fields["GROSSAMOUNT"];
			$F_QTY		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive ","SCANDATA","SUM(QTY)","SKUNO = '{$SKUNO}' AND REFNO = '{$MPOSNO}'");
			$RECAMT		=	$F_QTY * $UNITPRICE;
			
			$output .=	";;;;$SKUNO;$SKUNODESC;$QTY;$F_QTY;$GROSSAMOUNT;$RECAMT\r";
			$totqty	+=	$QTY;
			$totamt	+=	$GROSSAMOUNT;
			$totsqty	+=	$F_QTY;
			$totsamt	+=	$RECAMT;
			
			$RSGETMPOSDTLS->MoveNext();
		}
	}
	$GRANDMPOSTOTAL	+=	$GROSSAMOUNT;
	$GRANDPSTDTOTAL	+=	$POSTEDAMT;
	$GRANDMPOSQTY	+=	$TOTALQTY;
	$GRANDPSTDQTY	+=	$POSTEDQTY;
	$cnt++;
	$output .=	";;;;;GRAND TOTAL;$GRANDMPOSQTY;$GRANDPSTDQTY;$GRANDMPOSTOTAL;$GRANDPSTDTOTAL\r\r";
}
	
		
	header("Content-Disposition: attachment; filename=VarianceReport.csv");
	header("Content-Location: $_SERVER[REQUEST_URI]");
	header("Content-Type: text/plain");
	header("Expires: 0");
	echo $output;
?>