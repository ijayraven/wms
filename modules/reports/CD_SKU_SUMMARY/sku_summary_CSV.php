<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}
$selcusttype	=	$_GET["selcusttype"];
$sel_seasons	=	$_GET["sel_seasons"];
$sel_class		=	$_GET["sel_class"];
$txtcustno		=	$_GET["txtcustno"];
$dfrom			=	$_GET["dfrom"];
$dto			=	$_GET["dto"];
$rdoordertype	=	$_GET["rdoordertype"];
if($selcusttype == "NBS")
{
	$selcusttype_Q	=	" AND C.CustomerBranchCode !=''";
}
if($selcusttype == "TRADE")
{
	$selcusttype_Q	=	" AND C.CustomerBranchCode =''";
}
if($sel_class != "")
{
	if($sel_class == "EVERYDAY")
	{
		$SEASON_Q	=	" AND (O.SOF NOT REGEXP 'M' AND O.SOF NOT REGEXP 'ML' AND O.SOF NOT REGEXP 'F' AND O.SOF NOT REGEXP 'FL' AND O.SOF NOT REGEXP 'XN' AND O.SOF NOT REGEXP 'XL' AND O.SOF NOT REGEXP 'X' AND O.SOF NOT REGEXP 'H' AND O.SOF NOT REGEXP 'HL')";
	}
	else 
	{
		if($sel_seasons != "")
		{
			$SEASON_Q	=	" AND O.SOF REGEXP '$sel_seasons'";
		}
		else 
		{
			$SEASON_Q	=	" AND (O.SOF REGEXP 'M' OR O.SOF  REGEXP 'ML' OR O.SOF  REGEXP 'F' OR O.SOF  REGEXP 'FL' OR O.SOF  REGEXP 'XN' OR O.SOF  REGEXP 'XL' OR O.SOF  REGEXP 'X' OR O.SOF  REGEXP 'H' OR O.SOF  REGEXP 'HL')";
		}
	}
}
if ($txtcustno == "")
{
	$CUST_Q		=	"";
}
else 
{
	$CUST_Q		=	" AND O.CUSTNO = '{$txtcustno}'";
}
if($rdoordertype == "")
{
	$TYPE_Q		=	"";
}
else 
{
	$TYPE_Q		=	" AND O.DOCTYPE = '{$rdoordertype}'";
}
$getorder	=	"SELECT O.SOF,O.CUSTNO,O.DOCTYPE, O.GROSSAMOUNT,O.NETAMOUNT,SUM(D.RECEIVEDQTY) as RECEIVEDQTY,C.CustName
				 FROM WMS_NEW.CONFIRMDELIVERY_HDR AS O
				 LEFT JOIN WMS_NEW.CONFIRMDELIVERY_DTL AS D ON D.SOF = O.SOF
				 LEFT JOIN  FDCRMSlive.custmast AS C ON C.CustNo = O.CUSTNO
				 WHERE O.CONFIRMDELDATE BETWEEN '{$dfrom}' AND '{$dto}' $CUST_Q $TYPE_Q $selcusttype_Q $SEASON_Q
				 GROUP BY O.SOF
				 ORDER BY C.CustName";
//exit();
$rsgetorder	=	$Filstar_conn->Execute($getorder);
if($rsgetorder == false)
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__; exit();
}
else 
{
	if($rsgetorder->RecordCount() > 0)
	{
		$arrOrder	=	array();
		while (!$rsgetorder->EOF) {
			
			$SOF			=	$rsgetorder->fields["SOF"];
			$CUSTNO			=	$rsgetorder->fields["CUSTNO"];
			$GROSSAMOUNT	=	$rsgetorder->fields["GROSSAMOUNT"];
			$NETAMOUNT		=	$rsgetorder->fields["NETAMOUNT"];
			$RECEIVEDQTY	=	$rsgetorder->fields["RECEIVEDQTY"];
			$DOCTYPE		=	$rsgetorder->fields["DOCTYPE"];
			$CustName		=	$rsgetorder->fields["CustName"];
			if(strpos($SOF,"M"))
			{
				$SEASON = "MOTHER'S DAY";
			}
			else if(strpos($SOF,"F"))
			{
				$SEASON = "FATHER'S DAY";
			}
			else if(strpos($SOF,"X"))
			{
				$SEASON = "CHRISTMAS";
			}
			else if(strpos($SOF,"H"))
			{
				$SEASON = "VALENTINES";
			}
			else 
			{
				$SEASON = "EVERYDAY";
			}
			$arrOrder[$CUSTNO]["SOF"]			=	$SOF;
			$arrOrder[$CUSTNO]["GROSSAMOUNT"]	+=	$GROSSAMOUNT;
			$arrOrder[$CUSTNO]["NETAMOUNT"]		+=	$NETAMOUNT;
			$arrOrder[$CUSTNO]["RECEIVEDQTY"]	+=	$RECEIVEDQTY;
			$arrOrder[$CUSTNO]["DOCTYPE"]		=	$DOCTYPE;
			$arrOrder[$CUSTNO]["SEASON"]		=	$SEASON;
			$arrOrder[$CUSTNO]["CustName"]		=	$CustName;
			
			$rsgetorder->MoveNext();
		}
			$totsku			=	0;
			$totwholesale	=	0;
			$totgross		=	0;
			$output			.=	"FILSTAR DISTRIBUTORS CORPORATION\r";
			$output			.=	"CONFIRMED DELIVERIES\r";
			$output			.=	"SKU SUMMARY\r";
			$output			.=	"PERIOD: ".date("F d, Y",strtotime($dfrom))." to ".date("F d, Y",strtotime($dto))."\r\r";
			$output			.=	"CUSTOMER; ORDER TYPE; SEASON; TOTAL SKU QTY; TOTAL WHOLESALE AMOUNT; TOTAL GROSS AMOUNT \r";
			
			foreach ($arrOrder as $custnum=>$val1)
			{
				if($val1["DOCTYPE"] == "INVOICE")
				{
					$TYPE	=	"INV";
				}
				else 
				{
					$TYPE	=	$val1["DOCTYPE"];
				}
				$customer 	=	$custnum."-".($val1["CustName"]);
				$SEASON		=	$val1["SEASON"];
				$RECEIVEDQTY=	number_format($val1["RECEIVEDQTY"]);
				$NETAMOUNT	=	number_format($val1["NETAMOUNT"],2);
				$GROSSAMOUNT=	number_format($val1["GROSSAMOUNT"],2);
				
				$output			.=	"$customer;$TYPE;$SEASON;$RECEIVEDQTY;$NETAMOUNT;$GROSSAMOUNT\r";
				
				$totsku			+=	$val1["RECEIVEDQTY"];
				$totwholesale	+=	$val1["NETAMOUNT"];
				$totgross		+=	$val1["GROSSAMOUNT"];
			}
			$output			.=	";;TOTALS;$totsku;$totwholesale;$totgross\r";
			
	header("Content-Disposition: attachment; filename=Confirmed_Deliveries_SKU_Summary.csv");
	header("Content-Location: $_SERVER[REQUEST_URI]");
	header("Content-Type: text/plain");
	header("Expires: 0");
	echo $output;
	}
	else 
	{
		echo "No records found.";
	}
}
?>