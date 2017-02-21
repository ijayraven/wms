<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}

	$action			=	$_GET['action'];
	$OPT_1			=	$_GET['OPT_1'];//NBS or TRADE
	$OPT_2			=	$_GET['OPT_2'];//ADDED or DELIVERY
	$OPT_3			=	$_GET['OPT_3'];//STF or INVOICE
	$CUSTOMERCODE	=	$_GET['CUSTOMERCODE'];
	$SEL_CLASS		=	$_GET['SEL_CLASS'];
	$DFROM			=	$_GET['DFROM'];
	$DTO			=	$_GET['DTO'];
	
	$sof_list		=	array();
	$cust_list		=	array();
	
	if ($OPT_1 == 'NBS') 
	{
		$sel_cust	=	"SELECT CustNo FROM custmast where (`CustomerBranchCode` != '' or `NBSnewBranchCode` != '' ) AND LENGTH(`CustNo`) = '7' ";
	}
	else 
	{
		$sel_cust	=	"SELECT CustNo FROM custmast where CustomerBranchCode = ''  AND LENGTH(`CustNo`) = '7' ";
	}
	$rssel_cust	=	$Filstar_conn->Execute($sel_cust);
	if ($rssel_cust==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	while (!$rssel_cust->EOF) 
	{
		$cust_list[]	=	$rssel_cust->fields['CustNo'];
		
		$rssel_cust->MoveNext();
	}
	$acust_list	=	implode("','",$cust_list);

	if($SEL_CLASS != "")
	{
		if($SEL_CLASS == "EVERYDAY")
		{
			$SEASON_Q	=	" AND (SOF NOT REGEXP 'M' AND SOF NOT REGEXP 'ML' AND SOF NOT REGEXP 'F' AND SOF NOT REGEXP 'FL' AND SOF NOT REGEXP 'XN' AND SOF NOT REGEXP 'XL' AND SOF NOT REGEXP 'X' AND SOF NOT REGEXP 'H' AND SOF NOT REGEXP 'HL')";
		}
		else 
		{
			if($sel_seasons != "")
			{
				$SEASON_Q	=	" AND SOF REGEXP '$sel_seasons'";
			}
			else 
			{
				$SEASON_Q	=	" AND (SOF REGEXP 'M' OR SOF  REGEXP 'ML' OR SOF  REGEXP 'F' OR SOF  REGEXP 'FL' OR SOF  REGEXP 'XN' OR SOF  REGEXP 'XL' OR SOF  REGEXP 'X' OR SOF  REGEXP 'H' OR SOF  REGEXP 'HL')";
			}
		}
	}
	
		
	$sel_hdr	 =	"SELECT SOF,CUSTNO,DOCNO,ADDEDDATE from WMS_NEW.CONFIRMDELIVERY_HDR WHERE 1 $SEASON_Q ";
	if (!empty($CUSTOMERCODE)) 
	{
	$sel_hdr	.=	"AND CUSTNO = '{$CUSTOMERCODE}' ";
	}
	$sel_hdr	.=	"AND DOCTYPE = '{$OPT_3}' ";
	$sel_hdr	.=	"AND CUSTNO IN ('$acust_list') ";
	if ($OPT_2 != 'ADDED') 
	{
	$sel_hdr	.=	" AND CONFIRMDELDATE between '{$DFROM}' AND '{$DTO}' order by CONFIRMDELDATE asc ";
	}
	else 
	{
	$sel_hdr	.=	" AND ADDEDDATE between '{$DFROM}' AND '{$DTO}' order by CUSTNO,ADDEDDATE asc ";	
	}
	$rssel_hdr	=	$Filstar_conn->Execute($sel_hdr);
	if ($rssel_hdr==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	$cnt	=	$rssel_hdr->RecordCount();
	if ($cnt > 0) 
	{
		$cCSV	.=	"FILSTAR DISTRIBUTORS CORP";
		$cCSV	.=	"\n";
		$cCSV	.=	"DELIVERY QUANTITY THRU ".$OPT_3;
		$cCSV	.=	"\n";
		$cCSV	.=	$OPT_2.":".$DFROM." to ".$DTO;
		$cCSV	.=	"\n";
		$cCSV	.=	"CUSTOMER;CUSTOMER NAME;SOFNO;DOCNO;DOC DATE;SKUNO;DESCRIPTION;CONFIRMED QTY;UNIT COST;UNIT PRICE;NET AMOUNT;GROSS AMOUNT;BRAND;CLASS;";
		$cCSV	.=	"\n";
		
		while (!$rssel_hdr->EOF)
		{
			$SOF		=	$rssel_hdr->fields['SOF'];
			$CUSTNO		=	$rssel_hdr->fields['CUSTNO'];
			$DOCNO		=	$rssel_hdr->fields['DOCNO'];
			$ADDEDDATE	=	$rssel_hdr->fields['ADDEDDATE'];
			
//			$net__		=	0;
//			$gross__	=	0;
			
			$CustName	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
			
			$sel_dtl	=	"SELECT * FROM WMS_NEW.CONFIRMDELIVERY_DTL WHERE SOF = '{$SOF}' ";
			$rssel_dtl	=	$Filstar_conn->Execute($sel_dtl);
			if ($rssel_dtl==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			while (!$rssel_dtl->EOF) 
			{
				$SKUNO	=	$rssel_dtl->fields['SKUNO'];
				$QTY	=	$rssel_dtl->fields['QTY'];
				$RQTY	=	$rssel_dtl->fields['RECEIVEDQTY'];
				
				$ItemDesc	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo = '{$SKUNO}' ");
				$UnitCost	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderdetail","UnitCost","OrderNo = '{$SOF}' and Item = '{$SKUNO}' ");
				$UnitPrice	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderdetail","UnitPrice","OrderNo = '{$SOF}' and Item = '{$SKUNO}' ");
				$BRAND		=	$global_func->Select_val($Filstar_pms,"FDC_PMS","ITEMMASTER","BRAND","ITEMNO = '{$SKUNO}' ");
				$BRAND_NAME	=	$global_func->Select_val($Filstar_pms,"FDC_PMS","BRAND_NEW","BRAND_NAME","BRAND_ID = '{$BRAND}' ");
				$CLASS		=	$global_func->Select_val($Filstar_pms,"FDC_PMS","ITEMMASTER","CLASS","ITEMNO = '{$SKUNO}' ");
				
				
				$net		=	$RQTY * $UnitCost;
				$gross		=	$RQTY * $UnitPrice;
				
				$cCSV	.=	"$CUSTNO;$CustName;$SOF;$DOCNO;$ADDEDDATE;$SKUNO;$ItemDesc;$RQTY;$UnitCost;$UnitPrice;$net;$gross;$BRAND_NAME;$CLASS;";
				$cCSV	.=	"\n";
				
//				$net__	+=	$net;
//				$gross__+=	$gross;
				
				$total_net	+=	$net;
				$total_gross+=	$gross;
				
				$rssel_dtl->MoveNext();
			}
			
			//$cCSV	.=	";;;;;;;;;SUB TOTAL;$total_net;$total_gross";
			
			$rssel_hdr->MoveNext();
		}
		$cCSV	.=	";;;;;;;;;GRAND TOTAL;$total_net;$total_gross";
	}
	else 
	{
	
		$cCSV	.=	"FILSTAR DISTRIBUTORS CORP";
		$cCSV	.=	"\n";
		$cCSV	.=	"DELIVERY QUANTITY THRU ".$OPT_3;
		$cCSV	.=	"\n";
		$cCSV	.=	$OPT_2.":".$DFROM." to ".$DTO;
		$cCSV	.=	"\n";
		$cCSV	.=	"NO RECORD FOUND";
		
	}
	
	header("Content-type: text/csv");
	header("Content-Transfer-Encoding: UTF-8");
	header("Content-Disposition: attachment; filename=CONFIRMED_PER_SKU_{$DFROM}_TO_{$DTO}.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $cCSV;