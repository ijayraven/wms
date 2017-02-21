<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
	exit();
}
$output	.=	";FILSTAR DISTRIBUTORS CORPORATION\r";			
$output	.=	";RECEIVED AND SCANNED REPORT\r\r";					
$output .= "No.; CUSTOMER; MPOS NO.; MPOS DATE; MPOS TYPE;  SCAN DATE; STATUS; QTY; GROSS AMOUNT;NET AMOUNT \r";	
$GETMPOS	=	$_SESSION["QUERY"];
$RSGETMPOS	=	$Filstar_conn->Execute($GETMPOS);
if($RSGETMPOS == false)
{
	echo $errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__);  exit();
}
else 
{
	$cnt		=	1;
	$GRANDTOTAL	=	0;
	$NETTOTAL	=	0;
	$GRANDQTY	=	0;
	while (!$RSGETMPOS->EOF) {
		$CUSTNO		=	$RSGETMPOS->fields["CUSTNO"];
		$CustName	=	$RSGETMPOS->fields["CustName"];
		$MPOSNO		=	$RSGETMPOS->fields["MPOSNO"];
		$MPOSDATE	=	$RSGETMPOS->fields["MPOSDATE"];
		$SCANDATE	=	$RSGETMPOS->fields["SCANDATE"];
		$TOTALQTY	=	$RSGETMPOS->fields["TOTALQTY"];
		$TYPE		=	$RSGETMPOS->fields["TYPE"];
		$STATUS		=	$RSGETMPOS->fields["STATUS"];
		$GROSSAMOUNT=	$RSGETMPOS->fields["GROSSAMOUNT"];
		$NETAMOUNT	=	$RSGETMPOS->fields["NETAMOUNT"];
		if($STATUS != "")
		{
			$STATUS	=	"Received and Scanned";
		}
		else 
		{
			$STATUS	=	"Received and not yet Scanned";
		}		
		$output .= "$cnt; $CUSTNO-$CustName;$MPOSNO;$MPOSDATE;$TYPE;$SCANDATE;$STATUS;$TOTALQTY;$GROSSAMOUNT;$NETAMOUNT \r";	
		$GRANDTOTAL	+=	$GROSSAMOUNT;
		$NETTOTAL	+=	$NETAMOUNT;
		$GRANDQTY	+=	$TOTALQTY;
		$cnt++;
	$RSGETMPOS->Movenext();
	}
	$output .= ";;;;;;TOTAL;$GRANDQTY;$GRANDTOTAL;$NETTOTAL \r";	
	
	header("Content-Disposition: attachment; filename=ReceivedAndScanned.csv");
	header("Content-Location: $_SERVER[REQUEST_URI]");
	header("Content-Type: text/plain");
	header("Expires: 0");
	echo $output;
}
?>