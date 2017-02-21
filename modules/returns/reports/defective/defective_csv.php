<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}
$output	.=	";FILSTAR DISTRIBUTORS CORPORATION\r";			
$output	.=	";MPOS DEFECTIVE SKU REPORT\r\r";			
			
$output	.=	"MPOS No.;	Reason;	Customer;	SKU No.;	SKU Description;	MPOS DATE;	SCANNED DATE;	Qty;	Gross Amt.\r";

$arrMpos	=	$_SESSION["arrVAR"];
$cnt		=	1;
$GRANDMPOSTOTAL	=	0;
$GRANDPSTDTOTAL	=	0;
$GRANDMPOSQTY	=	0;
$GRANDPSTDQTY	=	0;
foreach ($arrMpos as $CUSTCODE=>$val1)
{
	$totqty	=	0;
	$totgrs	=	0;
	foreach ($val1 as $MPOSNO=>$val2)
	{
		foreach ($val2 as $ITEMNO=>$val3)
		{
			$CUSTOMER		=	$val3["CUSTOMER"];
			$SKUDESC		=	$val3["SKUDESC"];
			$DEFECTIVEQTY	=	$val3["DEFECTIVEQTY"];
			$SCANDATE		=	$val3["SCANDATE"];
			$MPOSDATE		=	$val3["MPOSDATE"];
			$GROSSAMOUNT	=	$val3["GROSSAMOUNT"];
			$REASON			=	$val3["REASON"];
			$output	.=	"$MPOSNO;	$REASON;	$CUSTOMER;	$ITEMNO;	$SKUDESC;	$MPOSDATE;	$SCANDATE;	$DEFECTIVEQTY;	$GROSSAMOUNT\r";

			$totqty	+=	$DEFECTIVEQTY;
			$totgrs	+=	$GROSSAMOUNT;
		}
	}
	$output	.=	"TOTAL;;;;;;;	$totqty;	$totgrs\r\r\r";
}
		
	header("Content-Disposition: attachment; filename=DefectiveSKUQTY.csv");
	header("Content-Location: $_SERVER[REQUEST_URI]");
	header("Content-Type: text/plain");
	header("Expires: 0");
	echo $output;
?>