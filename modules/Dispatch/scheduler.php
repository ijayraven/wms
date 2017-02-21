<?php
/********************************************************************************************************************
* FILE NAME :	scheduler.php																						*
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
* 2013/08/02	Raymond A. Galaroza																					*
* 																													*
* ALGORITHM(pseudocode)																								*
* 																													*
*********************************************************************************************************************/
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../index.php'</script>";
}

$action	=	$_GET['action'];
	
if ($action == 'INVOICE_DTL')
{
	$response	=	'';
	$nScan		=	$_GET['SCAN'];
	$cnt		=	" SELECT count(*) as cnt ";
	$cnt		.=	" FROM ".FDCRMS.".orderheader where InvoiceNo = $nScan and OrderCategory = 'Invoice'";
	$rscnt		=	$Filstar_conn->Execute($cnt);
	$selected	=	$rscnt->fields['cnt'];
	if ($selected > 0) 
	{
		$aData	=	array();
		$sel	=	" SELECT CustNo,InvoiceNo,InvoiceAmount,OrderNo,PickListNo ";
		$sel	.=	" FROM ".FDCRMS.".orderheader where InvoiceNo = $nScan and OrderCategory = 'Invoice'";
		$rssel	=	$Filstar_conn->Execute($sel);
		while (!$rssel->EOF) 
		{
			$CustNo			=	$rssel->fields['CustNo'];
			$InvoiceNo		=	$rssel->fields['InvoiceNo'];
			$InvoiceAmount	=	$rssel->fields['InvoiceAmount'];
			$OrderNo		=	$rssel->fields['OrderNo'];
			$PickListNo		=	$rssel->fields['PickListNo'];
			
			$aData[$CustNo][$InvoiceNo]['InvoiceAmount']	=	$InvoiceAmount;
			$aData[$CustNo][$InvoiceNo]['OrderNo']			=	$OrderNo;
			$aData[$CustNo][$InvoiceNo]['PickListNo']		=	$PickListNo;
			$rssel->MoveNext();
		}
		try {
			$Filstar_conn->StartTrans();
			
			foreach ($aData as $key1=>$val1)
			{
				foreach ($val1 as $key2=>$val2)
				{
					$sel		=	"SELECT count(*) as COUNTER FROM ".DISPATCH_DB.".DISPATCH_ORDER WHERE CUSTNO = '{$key1}' AND INVOICENO = '$key2' ";
					$rsel		=	$Filstar_conn->Execute($sel);
					$ncounter	=	$rsel->fields['COUNTER'];
					if ($ncounter > 0)
					{
						$response	.=	"Customer $key1 with invoice number $key2 already exist ";
						$response	.=	"\n";
					}
					else 
					{
						$dispatch	=	"INSERT INTO ".DISPATCH_DB.".DISPATCH_ORDER (`CUSTNO`,`INVOICENO`,`INVOICEAMOUNT`,`ORDERNO`,`PICKLISTNO`)
										values
										('{$key1}','{$key2}','{$val2['InvoiceAmount']}','{$val2['OrderNo']}','{$val2['PickListNo']}')";
						$rsdispatch	=	$Filstar_conn->Execute($dispatch);
						if ($rsdispatch == false) 
						{
							throw new Exception(mysql_errno()."::".mysql_error());
						}
					}
				}
			}
			if (empty($response)) 
			{
				echo "1";
			}
			else 
			{
				echo $response;
			}
			$Filstar_conn->CompleteTrans();
		}
		catch (Exception $e)
		{
			echo $e->__toString();
			$Filstar_conn->CompleteTrans();
		}
		unset($aData);
	}
	else 
	{
		echo "2";
	}
	exit();
}


if ($action	==	'INVOICE_LIST')
{
	$sel	=	" SELECT CUSTNO,INVOICENO,INVOICEAMOUNT,ORDERNO,PICKLISTNO ";
	$sel	.=	" FROM  ".DISPATCH_DB.".DISPATCH_ORDER WHERE STATUS = 'SAVE' ORDER BY ID DESC";
	$rssel	=	$Filstar_conn->Execute($sel);
	$aData	=	array();
	$cntr	=	1;
	$show	=	"<table width='100%' border='0'>";
	$show	.=	"<tr class='Header_style'>";
	$show	.=		"<td width='5%'>";
	$show	.=			"&nbsp";
	$show	.=		"</td>";
	$show	.=				"<td width='15%' align='center'>";
	$show	.=					"CUSTOMER CODE";
	$show	.=				"</td>";
	$show	.=						"<td width='55%' align='center'>";
	$show	.=							"CUSTOMER NAME";
	$show	.=						"</td>";
	$show	.=								"<td width='10%' align='center'>";
	$show	.=									"INVOICE NO";
	$show	.=								"</td>";
	$show	.=										"<td width='15%' align='center'>";
	$show	.=											"INVOICE AMOUNT";
	$show	.=										"</td>";
	$show	.=	"</tr>";
	while (!$rssel->EOF) 
	{
		$CustNo			=	$rssel->fields['CUSTNO'];
		$InvoiceNo		=	$rssel->fields['INVOICENO'];
		$InvoiceAmount	=	$rssel->fields['INVOICEAMOUNT'];
		$OrderNo		=	$rssel->fields['ORDERNO'];
		$PickListNo		=	$rssel->fields['PICKLISTNO'];
		
		$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
			$show	.=	"<td width='5%' align='center'>";
			$show	.=		"<input type='checkbox' name='cb_$cntr' id='cb_$cntr' value='$CustNo' onClick='toggle($cntr,this.id);'>";
			$show	.=		"<input type='hidden' name='cust_$cntr' id='cust_$cntr' value='$InvoiceNo' >";
			$show	.=	"</td>";
			
			$show	.=	"<td width='15%' align='center'>";
			$show	.=		$CustNo;
			$show	.=	"</td>";
			
			$show	.=	"<td width='55%' align='left'>";
			$show	.=	$global_func->CustName($Filstar_conn,$CustNo);
			$show	.=	"</td>";
			
			$show	.=	"<td width='10%' align='right'>";
			$show	.=		$InvoiceNo;
			$show	.=	"</td>";
			
			$show	.=	"<td width='15%' align='right'>";
			$show	.=		number_format($InvoiceAmount,2);
			$show	.=	"</td>";
		$show	.=	"</tr>";
		$cntr++;
		$rssel->MoveNext();
	}
	$show	.=	"<tr>";
	$show	.=		"<td colspan='1'align='center'>";
	$show	.=			"<input type='checkbox' name='selectall' id='selectall' value='selectall' onClick='toggle($cntr,this.id)'/>";
	$show	.=		"</td>";
	$show	.=		"<td colspan='1' align='left'>";
	$show	.=			"&nbsp;";
	$show	.=		"</td>";
	$show	.=			"<td colspan='1'>";
	$show	.=				"<table width='100%' border='0'>";
	$show	.=					"<tr>";
	$show	.=							"<td align='center'>";
	$show	.=								"<div id='divmanila'><input type='button' name='btnmanila' id='btnmanila' value='MANILA' onClick='Create_Dispatch($cntr,this.value);' class='small_button'></div>";
	$show	.=							"</td>";
	$show	.=								"<td align='center'>";
	$show	.=									"<div id='divpandayan'><input type='button' name='btnpandayan' id='btnpandayan' value='PANDAYAN' onClick='Create_Dispatch($cntr,this.value);' class='small_button'></div>";
	$show	.=								"</td>";
	$show	.=									"<td align='center'>";
	$show	.=										"<div id='divprovince'><input type='button' name='btnmprovince' id='btnmprovince' value='PROVINCE' onClick='Create_Dispatch($cntr,this.value);' class='small_button'></div>";
	$show	.=									"</td>";
	$show	.=					"</tr>";
	$show	.=				"</table>";
	$show	.=			"</td>";
	$show	.=					"<td colspan='2'>";
	$show	.=						"&nbsp";
	$show	.=					"</td>";
	$show	.=	"</tr>";
	$show	.=	"<tr>";
	$show	.=		"<td colspan='2' align='rifht'>";
	$show	.=			"&nbsp";
	$show	.=		"</td>";
	$show	.=			"<td colspan='1'>";
	$show	.=				"<table width='100%' border='0' class='d1'>";
	$show	.=					"<tr>";
	$show	.=							"<td align='center'>";
	$show	.=								"Total Record:";
	$show	.=							"</td>";
	$show	.=								"<td align='left'>";
	$show	.=									"<input type='text' name='txttotalcnt' id='txttotalcnt' value='".($cntr - 1)."' readonly style='border:1;'>";
	$show	.=								"</td>";
	$show	.=					"</tr>";
	$show	.=				"</table>";
	$show	.=			"</td>";
	
	$show	.=	"</tr>";
	$show	.=	"</table>";
	echo $show;
	exit();
}
	
if ($action	==	'DISPATCH_FORM') 
{
	$type		=	$_GET['VAL_TYPE'];
	$total_cnt	=	$_GET['VAL_CNT'];
	$x			=	0;
	$aData		=	array();
	for ($x;$x<=$total_cnt;$x++)
	{
		$chk_custno		=	$_GET['cb_'.$x];
		$chk_invoice	=	$_GET['cust_'.$x];
		if (!empty($chk_custno)) 
		{
			$aData[$chk_custno][]	=	$chk_invoice;
		}
	}
	if (count($aData) > 0) 
	{
		echo $global_func->Table_form(&$Filstar_conn,$aData,$type);
	}
	exit();
}

if ($action == 'SAVE_DISPATCH') 
{
	$error_cnt	=	0;
	$opt		=	$_GET['OPT'];
	$save_type	=	$_GET['SAVE_TYPE'];
	$total_cnt	=	$_GET['TOTAL_CNT'];
	$date		=	$_GET['txtdate_'.$save_type];
	$route		=	$_GET['txtroute_'.$save_type];
	$van		=	$_GET['txtvan_'.$save_type];
	$plate		=	$_GET['txtplate_'.$save_type];
	$driver		=	$_GET['txtdriver_'.$save_type];
	$helper		=	$_GET['txthelper_'.$save_type];
	$instruction=	$_GET['txtinstruction_'.$save_type];
	try {
		$Filstar_conn->StartTrans();
		if ($save_type == 'MANILA') 
		{
			$forward		=	$_GET['txtforwarded_'.$save_type];
			$tracking_no	=	$global_func->Generate_transeq($Filstar_conn,$save_type);
			$hdr_manila		 =	" INSERT INTO ".DISPATCH_DB.".DISPATCH_METROMANILA_HDR ";
			$hdr_manila		.=	" (`TRANSEQ`,`DATE`,`ROUTE`,`VANNO`,`PLATENO`,`DRIVER`,`HELPER`,`FORWARDER`,`SPECIAL_INSTRUCTION`,`PREPAREDBY`,`PREPAREDDATE`,`CHECKED_DISPATCHEDBY`,`APPROVEDBY`,`GUARDONDUTY`) ";
			$hdr_manila		.=	" VALUES ";
			$hdr_manila		.=	" ('{$tracking_no}','{$date}','{$route}','{$van}','{$plate}','{$driver}','{$helper}','{$forward}','{$instruction}','{$_SESSION['username']}',sysdate(),'','','')";
			$rshdr_manila	=	$Filstar_conn->Execute($hdr_manila);
			if ($rshdr_manila == false) 
			{
				throw new Exception(mysql_errno().":".mysql_error());
			}
			$this_id	=	$Filstar_conn->Insert_ID();
			for ($x=1;$x<=($total_cnt - 1);$x++)
			{
				$CustNo			=	$_GET['hdn_cust_MANILA_'.$x];
				$CustName		=	$global_func->CustName($Filstar_conn,$CustNo);
				$InvoiceNo		=	$_GET['hdn_invoice_MANILA_'.$x];
				$InvoiceDate	=	$global_func->Select_val($Filstar_conn,FDCRMS,"orderheader","InvoiceDate","CustNo= '$CustNo' AND InvoiceNo= '$InvoiceNo' and OrderCategory = 'Invoice'");
				$InvoiceAmount	=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_ORDER","INVOICEAMOUNT","CUSTNO = '$CustNo' AND INVOICENO = '$InvoiceNo'");
				$sofno			=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_ORDER","ORDERNO","CUSTNO = '$CustNo' AND INVOICENO = '$InvoiceNo'");
				$PickListNo		=	$global_func->Select_val($Filstar_conn,FDCRMS,"orderheader","PickListNo","CustNo= '$CustNo' AND InvoiceNo= '$InvoiceNo' and OrderCategory = 'Invoice'");
				$v	=	substr($sofno,-1);
				if (is_numeric($v))
				{
					$prodline	=	substr($sofno,1,1);
				}
				else 
				{
					$prodline	=	substr($sofno,-1);
				}
				$ctn			=	$_GET['txt_MANILA_ctn_'.$x];
				$pkg			=	$_GET['txt_MANILA_pkg_'.$x];
				$remark			=	$_GET['txt_MANILA_remark_'.$x];
				
				$dtl_manila	 =	" INSERT INTO ".DISPATCH_DB.".DISPATCH_METROMANILA_DTL ";
				$dtl_manila	.=	" (`TRANSEQ`,`ID`,`CUSTCODE`,`CUSTNAME`,`INVOICENO`,`INVOICEDATE`,`INVOICEAMOUNT`,`SOFNO`,`PLNO`,`PONO`,`PRODUCTLINE`,`CARTON`,`PACKAGE`,`REMARKS`) ";
				$dtl_manila	.=	" VALUES ";
				$dtl_manila	.=	" ('{$tracking_no}','{$this_id}','{$CustNo}','".mysql_escape_string($CustName)."','{$InvoiceNo}','{$InvoiceDate}','{$InvoiceAmount}','{$sofno}','{$PickListNo}','','{$prodline}','{$ctn}','{$pkg}','{$remark}')";
				$rsdtl_manila	=	$Filstar_conn->Execute($dtl_manila);
				if ($rsdtl_manila == false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
				if ($opt == 'SAVE')
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET TRANSEQ = '{$tracking_no}', STATUS = 'DELIVER' , DELIVERTO = '{$save_type}' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' ";
				}
				else 
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET TRANSEQ = '{$tracking_no}', STATUS = 'INPROCESS' , DELIVERTO = '{$save_type}' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' ";
				}
				$rsupdate	=	$Filstar_conn->Execute($update);
				if ($rsupdate== false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
			}
			echo $tracking_no;
		}
		elseif ($save_type == 'PANDAYAN')
		{
			$tracking_no		=	$global_func->Generate_transeq($Filstar_conn,$save_type);
			$hdr_pandayan		 =	" INSERT INTO ".DISPATCH_DB.".DISPATCH_PANDAYAN_HDR ";
			$hdr_pandayan		.=	" (`TRANSEQ`,`DATE`,`ROUTE`,`VANNO`,`PLATENO`,`DRIVER`,`HELPER`,`SPECIAL_INSTRUCTION`,`PREPAREDBY`,`PREPAREDDATE`,`CHECKED_DISPATCHEDBY`,`APPROVEDBY`,`GUARDONDUTY`) ";
			$hdr_pandayan		.=	" VALUES ";
			$hdr_pandayan		.=	" ('{$tracking_no}','{$date}','{$route}','{$van}','{$plate}','{$driver}','{$helper}','{$instruction}','{$_SESSION['username']}',sysdate(),'','','')";
			$rshdr_pandayan		 =	$Filstar_conn->Execute($hdr_pandayan);
			if ($rshdr_pandayan == false) 
			{
				throw new Exception(mysql_errno().":".mysql_error());
			}
			$this_id	=	$Filstar_conn->Insert_ID();
			for ($x=1;$x<=($total_cnt - 1);$x++)
			{
				$CustNo			=	$_GET['hdn_cust_PANDAYAN_'.$x];
				$CustName		=	$global_func->CustName($Filstar_conn,$CustNo);
				$InvoiceNo		=	$_GET['hdn_invoice_PANDAYAN_'.$x];
				$InvoiceDate	=	$global_func->Select_val($Filstar_conn,FDCRMS,"orderheader","InvoiceDate","CustNo= '$CustNo' AND InvoiceNo= '$InvoiceNo' and OrderCategory = 'Invoice'");
				$InvoiceAmount	=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_ORDER","INVOICEAMOUNT","CUSTNO = '$CustNo' AND INVOICENO = '$InvoiceNo'");
				$sofno			=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_ORDER","ORDERNO","CUSTNO = '$CustNo' AND INVOICENO = '$InvoiceNo'");
				$PickListNo		=	$global_func->Select_val($Filstar_conn,FDCRMS,"orderheader","PickListNo","CustNo= '$CustNo' AND InvoiceNo= '$InvoiceNo' and OrderCategory = 'Invoice'");
				$v	=	substr($sofno,-1);
				if (is_numeric($v))
				{
					$prodline	=	substr($sofno,1,1);
				}
				else 
				{
					$prodline	=	substr($sofno,-1);
				}
				$ctn			=	$_GET['txt_PANDAYAN_ctn_'.$x];
				$remark			=	$_GET['txt_PANDAYAN_remark_'.$x];
					
				$dtl_pandayan	 =	" INSERT INTO ".DISPATCH_DB.".DISPATCH_PANDAYAN_DTL ";
				$dtl_pandayan	.=	" (`TRANSEQ`,`ID`,`CUSTCODE`,`CUSTNAME`,`INVOICENO`,`INVOICEDATE`,`INVOICEAMOUNT`,`SOFNO`,`PLNO`,`PONO`,`PRODUCTLINE`,`CARTON`,`REMARKS`) ";
				$dtl_pandayan	.=	" VALUES ";
				$dtl_pandayan	.=	" ('{$tracking_no}','{$this_id}','{$CustNo}','".mysql_escape_string($CustName)."','{$InvoiceNo}','{$InvoiceDate}','{$InvoiceAmount}','{$sofno}','{$PickListNo}','','{$prodline}','{$ctn}','{$remark}')";
				$rsdtl_pandayan	 =	$Filstar_conn->Execute($dtl_pandayan);
				if ($rsdtl_pandayan == false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
				if ($opt == 'SAVE')
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET TRANSEQ = '{$tracking_no}', STATUS = 'DELIVER' , DELIVERTO = '{$save_type}' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' ";
				}
				else 
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET TRANSEQ = '{$tracking_no}', STATUS = 'INPROCESS' , DELIVERTO = '{$save_type}' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' ";
				}
				$rsupdate	=	$Filstar_conn->Execute($update);
				if ($rsupdate == false) 
				{
					echo mysql_errno().":".mysql_error();
				}
			}
			echo $tracking_no;
		}
		elseif ($save_type	==	'PROVINCE')
		{
			$forward		=	$_GET['txtforwarded_'.$save_type];
			$tracking_no	=	$global_func->Generate_transeq($Filstar_conn,$save_type);
			$hdr_province	 =	" INSERT INTO ".DISPATCH_DB.".DISPATCH_PROVINCE_HDR ";
			$hdr_province	.=	" (`TRANSEQ`,`DATE`,`ROUTE`,`VANNO`,`PLATENO`,`DRIVER`,`HELPER`,`FORWARDER`,`SPECIAL_INSTRUCTION`,`PREPAREDBY`,`PREPAREDDATE`,`CHECKED_DISPATCHEDBY`,`APPROVEDBY`,`GUARDONDUTY`) ";
			$hdr_province	.=	" VALUES ";
			$hdr_province	.=	" ('{$tracking_no}','{$date}','{$route}','{$van}','{$plate}','{$driver}','{$helper}','{$forward}','{$instruction}','{$_SESSION['username']}',sysdate(),'','','')";
			$rshdr_province	=	$Filstar_conn->Execute($hdr_province);
			if ($rshdr_province	== false) 
			{
				throw new Exception(mysql_errno().":".mysql_error());
			}
			$this_id	=	$Filstar_conn->Insert_ID();
			for ($x=1;$x<=($total_cnt - 1);$x++)
			{
				$CustNo			=	$_GET['hdn_cust_PROVINCE_'.$x];
				$CustName		=	$global_func->CustName($Filstar_conn,$CustNo);
				$InvoiceNo		=	$_GET['hdn_invoice_PROVINCE_'.$x];
				$InvoiceDate	=	$global_func->Select_val($Filstar_conn,FDCRMS,"orderheader","InvoiceDate","CustNo= '$CustNo' AND InvoiceNo= '$InvoiceNo' and OrderCategory = 'Invoice'");
				$InvoiceAmount	=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_ORDER","INVOICEAMOUNT","CUSTNO = '$CustNo' AND INVOICENO = '$InvoiceNo'");
				$sofno			=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,"DISPATCH_ORDER","ORDERNO","CUSTNO = '$CustNo' AND INVOICENO = '$InvoiceNo'");
				$PickListNo		=	$global_func->Select_val($Filstar_conn,FDCRMS,"orderheader","PickListNo","CustNo= '$CustNo' AND InvoiceNo= '$InvoiceNo' and OrderCategory = 'Invoice'");
				$v	=	substr($sofno,-1);
				if (is_numeric($v))
				{
					$prodline	=	substr($sofno,1,1);
				}
				else 
				{
					$prodline	=	substr($sofno,-1);
				}
				$size			=	$_GET['txt_PROVINCE_size_'.$x];
				$ctn			=	$_GET['txt_PROVINCE_ctn_'.$x];
				$dr				=	$_GET['txt_PROVINCE_dr_'.$x];
				$bill			=	$_GET['txt_PROVINCE_bill_'.$x];
				$kilo			=	$_GET['txt_PROVINCE_kilo_'.$x];
				
				$dtl_province	 =	" INSERT INTO ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL ";
				$dtl_province	.=	" (`TRANSEQ`,`ID`,`CUSTCODE`,`CUSTNAME`,`INVOICENO`,`INVOICEDATE`,`INVOICEAMOUNT`,`SOFNO`,`PRODUCT_LINE`,`SIZE_OF_CTN`,`QTY_CTN`,`DR_NO`,`WAYBILL_NUMBER`,`WEIGHT_BY_KILO`) ";
				$dtl_province	.=	" VALUES ";
				$dtl_province	.=	" ('{$tracking_no}','{$this_id}','{$CustNo}','".mysql_escape_string($CustName)."','{$InvoiceNo}','{$InvoiceDate}','{$InvoiceAmount}','{$sofno}','{$prodline}','{$size}','{$ctn}','{$dr}','{$bill}','{$kilo}')";
				$rsdtl_province	=	$Filstar_conn->Execute($dtl_province);
				if ($rsdtl_province== false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
				if ($opt == 'SAVE')
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET TRANSEQ = '{$tracking_no}', STATUS = 'DELIVER' , DELIVERTO = '{$save_type}' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' ";
				}
				else 
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET TRANSEQ = '{$tracking_no}', STATUS = 'INPROCESS' , DELIVERTO = '{$save_type}' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' ";
				}
				$rsupdate	=	$Filstar_conn->Execute($update);
				if ($rsupdate== false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
			}
			echo $tracking_no;
		}
		$Filstar_conn->CompleteTrans();
	}
	catch (Exception $e)
	{
		echo $e->__toString();
		$Filstar_conn->CompleteTrans();
	}
	exit();
}

if ($action == 'SEARCH_VAN') 
{
	$search	=	$_GET['VAL_VAL'];
	$type	=	$_GET['VAL_TYPE'];
	$cnt	=	"SELECT count(*) as CNT FROM ".WMS_LOOKUP.".VAN WHERE CODE LIKE '%$search%' ";
	$rscnt	=	$Filstar_conn->Execute($cnt);
	$cntr	=	$rscnt->fields['CNT'];
	if ($cntr > 0) 
	{
		$sel	=	"SELECT CODE,PLATE_NO FROM ".WMS_LOOKUP.".VAN WHERE CODE LIKE '%$search%' ";
		$rssel	=	$Filstar_conn->Execute($sel);
		$retval	=	"<select name='sel_van' id='sel_van' onkeypress=get_van(event,this.value,'$type'); multiple>";
		while (!$rssel->EOF) 
		{
			$code		=	$rssel->fields['CODE'];
			$plate_no	=	$rssel->fields['PLATE_NO'];
			
			$values	=	$code."|".$plate_no;
			$retval	.=	"<option value='$values' onClick=get_van('click',this.value,'$type');>$code-$plate_no</option>";
			//$retval	.=	"<option value='$code' >$plate_no</option>";
			$rssel->MoveNext();
		}
		$retval	.=	"</select>";
		echo $retval;
	}
	else 
	{
		echo "2";
	}
	
	exit();
}


if ($action == 'SEARCH_ROUTE') 
{
	$search	=	$_GET['VAL_VAL'];
	$type	=	$_GET['VAL_TYPE'];
	$cnt	=	"SELECT count(*) as CNT FROM ".WMS_LOOKUP.".ROUTE WHERE ROUTE LIKE '%$search%' ";
	$rscnt	=	$Filstar_conn->Execute($cnt);
	$cntr	=	$rscnt->fields['CNT'];
	if ($cntr > 0) 
	{
		$sel	=	"SELECT ROUTE FROM ".WMS_LOOKUP.".ROUTE WHERE ROUTE LIKE '%$search%' ";
		$rssel	=	$Filstar_conn->Execute($sel);
		$retval	=	"<select name='sel_van' id='sel_van' onkeypress=get_route(event,this.value,'$type'); multiple>";
		while (!$rssel->EOF) 
		{
			$code		=	$rssel->fields['ROUTE'];
			$retval	.=	"<option value='$code' onClick=get_route('click',this.value,'$type');>$code</option>";
			//$retval	.=	"<option value='$code' >$plate_no</option>";
			$rssel->MoveNext();
		}
		$retval	.=	"</select>";
		echo $retval;
	}
	else 
	{
		echo "2";
	}
	
	exit();
}
	
?>
<html>
<title>SCHEDULER</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../css/style.css);</style>
<style type="text/css">@import url(../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../calendar/calendar-setup.js"></script>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<script>
	function	Save_data(evt)
	{
		evtHandler	=	(evt.charCode)	?	evt.charCode	:	evt.keyCode;
		var Scan	=	$('#txtInvoice').val();
		if(isEmpty(Scan) == false)
		{
			if(evtHandler == 13 || evt == 'Click')
			{
				$.ajax({
						type		:	'POST',
						url			:	'scheduler.php?action=INVOICE_DTL&SCAN='+Scan,
						beforeSend	:	function()
									{
										$('#divloader').show();
									},
						success		:	function(response)
									{
										if(response == 1)
										{
											$('#divloader').hide();
											List_order();
										}
										else if(response == 2)
										{
											$('#divloader').hide();
											alert('No Record found...');
											List_order();
										}
										else
										{
											$('#divloader').hide();
											alert(response);
											List_order();
//											$('#divdebug').html(response);
//											$('#divdebug').show();
										}
										
									}
				});
			}
		}	
	}
		
	function	List_order()
	{
		$.ajax({
				type	:	'POST',
				url		:	'scheduler.php?action=INVOICE_LIST',
				success	:	function(response)
						{
							$('#txtInvoice').val('');
							$('#divlist').html(response);
							$('#divlist').show();
						}
		});
	}
		
	
	function	Create_Dispatch(val_cnt,val_type)
	{
		var x			=	1;
		var total_cnt 	=	val_cnt;
		var	deliver		=	val_type;
		var container	=	'';
		var	process		=	0;
		
		for(x;x<=total_cnt;x++)
		{
			var ischecked	=	$('#cb_'+x+'').is(":checked");
			if(ischecked == true)
			{
				process++;
			}
		}
		if(process == 0)
		{
			alert('Please Select atlease one transaction!');
		}
		else
		{
			$('#divsearch').hide();
			$('#divlist').hide();
			var formdata	=	$('#dataform').serialize();
			$.ajax({
					type		:	'POST',
					url			:	'scheduler.php?action=DISPATCH_FORM&'+formdata+'&VAL_TYPE='+val_type+'&VAL_CNT='+val_cnt,
					success		:	function(response)
								{
									$('#divresponse').html(response);
									$('#divresponse').show();
									Calendar.setup
									(
									   {
									     inputField  : "txtdate_"+val_type,    // ID of the input field
									     ifFormat    : "%Y-%m-%d", 			// The Date Format
									     button      : "img_date_"+val_type    // ID of the button
									   }
									);
								}
			});
		}
	}
		
	function	search_route(evt,val_val,val_id,val_type)
	{
		if(isEmpty(val_val) == false)
		{
			var evthandler = (evt.charCode) ? evt.charCode : evt.keyCode;
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url		:	'scheduler.php?action=SEARCH_ROUTE&VAL_VAL='+val_val+'&VAL_TYPE='+val_type,
						success	:	function(response)
								{
									if(response != 2)
									{
										$('#div_route_'+val_type).html(response);
										$('#div_route_'+val_type).show();
									}
									else
									{
										alert('route not found!');
										$('#'+val_id).val('');
										$('#div_route_'+val_type).html('');
										$('#div_route_'+val_type).hide();
									}
									
								}
				});
			}
			else if(evthandler == 40  && $('#div_van').html() != '')
			{
				$('#sel_route').focus();
			}
		}
		else
		{
			$('#div_route_'+val_type).html('');
		}
	}
	
	function	get_route(evt,val_val,val_type)
	{
		var evthandler = (evt.charCode) ? evt.charCode : evt.keyCode;
		if(evthandler == 13)
		{
			$('#txtroute_'+val_type).val(val_val);
			$('#div_route_'+val_type).html('');
			$('#div_route_'+val_type).hide();
		}
		else
		{
			$('#txtroute_'+val_type).val(val_val);
			$('#div_route_'+val_type).html('');
			$('#div_route_'+val_type).hide();
		}
	}

	function	search_van(evt,val_val,val_id,val_type)
	{
		if(isEmpty(val_val) == false)
		{
			var evthandler = (evt.charCode) ? evt.charCode : evt.keyCode;
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url		:	'scheduler.php?action=SEARCH_VAN&VAL_VAL='+val_val+'&VAL_TYPE='+val_type,
						success	:	function(response)
								{
									if(response != 2)
									{
										$('#div_van_'+val_type).html(response);
										$('#div_van_'+val_type).show();
									}
									else
									{
										alert('Van No. not found!');
										$('#'+val_id).val('');
										$('#div_van_'+val_type).html('');
										$('#div_van_'+val_type).hide();
									}
									
								}
				});
			}
			else if(evthandler == 40  && $('#div_van').html() != '')
			{
				$('#sel_van').focus();
			}
		}
		else
		{
			$('#div_van_'+val_type).html('');
			$('#txtplate_'+val_type).val('');
		}
	}
	
	
	function	get_van(evt,val_val,val_type)
	{
		var evthandler = (evt.charCode) ? evt.charCode : evt.keyCode;
		
		var vx = val_val;
		var x = vx.split('|'); 
		if(evthandler == 13)
		{
			$('#txtvan_'+val_type).val(x[0]);
			$('#txtplate_'+val_type).val(x[1]);
			$('#div_van_'+val_type).html('');
			$('#div_van_'+val_type).hide();
		}
		else
		{
			$('#txtvan_'+val_type).val(x[0]);
			$('#txtplate_'+val_type).val(x[1]);
			$('#div_van_'+val_type).html('');
			$('#div_van_'+val_type).hide();
		}
	}
	
	function	SaveDispatch(val_type,val_cnt)
	{
		var	limit			=	(val_cnt - 1);
		var	initial_ctn		=	1;
		var	initial_pkg		=	1;
		var do_process		=	true;
		var	val_date		=	$('#txtdate_'+val_type).val();
		var	val_route		=	$('#txtroute_'+val_type).val();
		var	val_van			=	$('#txtvan_'+val_type).val();
		var	val_plate		=	$('#txtplate_'+val_type).val();
		var	val_driver		=	$('#txtdriver_'+val_type).val();
		var	val_helper		=	$('#txthelper_'+val_type).val();
		var	val_instruction	=	$('#txtinstruction_'+val_type).val();
		
		if(isEmpty(val_date) == false)
		{
			if(isEmpty(val_route) == false)
			{
				if(isEmpty(val_van)	== false)
				{
					if(isEmpty(val_plate) == false)
					{
						if(isEmpty(val_driver) == false)
						{
							if(isEmpty(val_helper) == false)
							{
								/*SCHEDULE TYPE == ''*/
								if(val_type	==	'MANILA')
								{
									var formdata	=	$('#datamanila').serialize();
									var	val_forwarded	=	$('#txtforwarded_'+val_type).val();
									if(isEmpty(val_forwarded) == true)
									{
										alert('Please insert forwarded.');
										do_process	=	false;
									}
									else
									{
										for(initial_ctn;initial_ctn<=limit;initial_ctn++)
										{
											if(isEmpty($('#txt_'+val_type+'_ctn_'+initial_ctn).val()) == true && isEmpty($('#txt_'+val_type+'_pkg_'+initial_ctn).val()) == true)
											{
												do_process = false;
											}
											if(isEmpty($('#txt_'+val_type+'_remark_'+initial_ctn).val()) == true)
											{
												do_process = false;
											}
										}
									}
								}
								/*SCHEDULE TYPE == PANDAYAN*/
								else if(val_type == 'PANDAYAN')
								{
									var formdata	=	$('#datapandayan').serialize();
									for(initial_ctn;initial_ctn<=limit;initial_ctn++)
									{
										if(isEmpty($('#txt_'+val_type+'_ctn_'+initial_ctn).val()) == true)
										{
											do_process = false;
										}
										if(isEmpty($('#txt_'+val_type+'_remark_'+initial_ctn).val()) == true)
										{
											do_process = false;
										}
									}
								}
								/*SCHEDULE TYPE == PROVINCE*/
								else if(val_type == 'PROVINCE')
								{
									var formdata		=	$('#dataprovince').serialize();
									var	val_forwarded	=	$('#txtforwarded_'+val_type).val();
									if(isEmpty(val_forwarded) == true)
									{
										alert('Please insert forwarded.');
										do_process	=	false;
									}
									else
									{
										for(initial_ctn;initial_ctn<=limit;initial_ctn++)
										{
											var	var_size	=	$('#txt_'+val_type+'_size_'+initial_ctn).val();
											var	var_ctn		=	$('#txt_'+val_type+'_ctn_'+initial_ctn).val();
											var	var_dr		=	$('#txt_'+val_type+'_dr_'+initial_ctn).val();
											var	var_bill	=	$('#txt_'+val_type+'_bill_'+initial_ctn).val();
											var	var_kilo	=	$('#txt_'+val_type+'_kilo_'+initial_ctn).val();
											if(isEmpty(var_size) == true || isEmpty(var_ctn) == true || isEmpty(var_dr) == true || isEmpty(var_bill) == true || isEmpty(var_kilo) == true)
											{
												do_process	= false;
											}
										}
									}
								}
								
								if(do_process	==	true)
								{
									if(isEmpty(val_instruction) == false)
									{
										$.ajax({
												type		:	'POST',
												url			:	'scheduler.php?action=SAVE_DISPATCH&OPT=SAVE&SAVE_TYPE='+val_type+'&TOTAL_CNT='+val_cnt+'&'+formdata,
												beforeSend	:	function()
															{
																$('#divloader_response').show();
															},
												success		:	function(response)
															{
																if(response == 2)
																{
																	$('#divloader_response').hide();
																	$('#divdebug').html(response);
																 	$('#divdebug').show();
																}
																else
																{
																	alert('Transaction was successfully saved Your Tracking no. is '+response);
																	$('#divloader_response').hide();
																	$('#divresponse').html('');
																	$('#divresponse').hide();
																	$('#divsearch').show();
																	List_order();
																}
															}
										});
									}
									else
									{
										alert('Please insert Special Instruction.');
									}
								}
								else
								{
									alert('Please Complete all details inputs.');
								}
							}
							else
							{
								alert('Please insert helper.')
							}
						}
						else
						{
							alert('Please insert driver.');
						}
					}
					else
					{
						alert('Please insert plate no.');
					}
				}
				else
				{
					alert('Please insert van no.');
				}
			}
			else
			{
				alert('Please insert route.')
			}
		}
		else
		{
			alert('Please insert date.')
		}
	}
	
	
	function	SaveDispatch_temp(val_type,val_cnt)
	{
		var	limit			=	(val_cnt - 1);
		var	initial_ctn		=	1;
		var	initial_pkg		=	1;
		var do_process		=	true;
		var	val_date		=	$('#txtdate_'+val_type).val();
		var	val_instruction	=	$('#txtinstruction_'+val_type).val();
		
		if(isEmpty(val_date) == false)
		{
			/*SCHEDULE TYPE == ''*/
			if(val_type	==	'MANILA')
			{
				var formdata	=	$('#datamanila').serialize();
				for(initial_ctn;initial_ctn<=limit;initial_ctn++)
				{
					if(isEmpty($('#txt_'+val_type+'_ctn_'+initial_ctn).val()) == true && isEmpty($('#txt_'+val_type+'_pkg_'+initial_ctn).val()) == true)
					{
						do_process = false;
					}
					if(isEmpty($('#txt_'+val_type+'_remark_'+initial_ctn).val()) == true)
					{
						do_process = false;
					}
				}
			}
			/*SCHEDULE TYPE == PANDAYAN*/
			else if(val_type == 'PANDAYAN')
			{
				var formdata	=	$('#datapandayan').serialize();
				for(initial_ctn;initial_ctn<=limit;initial_ctn++)
				{
					if(isEmpty($('#txt_'+val_type+'_ctn_'+initial_ctn).val()) == true)
					{
						do_process = false;
					}
					if(isEmpty($('#txt_'+val_type+'_remark_'+initial_ctn).val()) == true)
					{
						do_process = false;
					}
				}
			}
			/*SCHEDULE TYPE == PROVINCE*/
			else if(val_type == 'PROVINCE')
			{
				var formdata		=	$('#dataprovince').serialize();
				for(initial_ctn;initial_ctn<=limit;initial_ctn++)
				{
					var	var_size	=	$('#txt_'+val_type+'_size_'+initial_ctn).val();
					var	var_ctn		=	$('#txt_'+val_type+'_ctn_'+initial_ctn).val();
					var	var_dr		=	$('#txt_'+val_type+'_dr_'+initial_ctn).val();
					var	var_bill	=	$('#txt_'+val_type+'_bill_'+initial_ctn).val();
					var	var_kilo	=	$('#txt_'+val_type+'_kilo_'+initial_ctn).val();
					if(isEmpty(var_size) == true || isEmpty(var_ctn) == true || isEmpty(var_dr) == true || isEmpty(var_bill) == true || isEmpty(var_kilo) == true)
					{
						do_process	= false;
					}
				}
			}
			if(do_process	==	true)
			{
				if(isEmpty(val_instruction) == false)
				{
					$.ajax({
							type		:	'POST',
							url			:	'scheduler.php?action=SAVE_DISPATCH&OPT=TEMP&SAVE_TYPE='+val_type+'&TOTAL_CNT='+val_cnt+'&'+formdata,
							beforeSend	:	function()
										{
											$('#divloader_response').show();
										},
							success		:	function(response)
										{
											if(response == 2)
											{
												$('#divloader_response').hide();
												$('#divdebug').html(response);
											 	$('#divdebug').show();
											}
											else
											{
												alert('Transaction was successfully saved Your Tracking no. is '+response);
												$('#divloader_response').hide();
												$('#divresponse').html('');
												$('#divresponse').hide();
												$('#divsearch').show();
												List_order();
											}
										}
					});
				}
				else
				{
					alert('Please insert Special Instruction.');
				}
			}
			else
			{
				alert('Please Complete all details inputs.');
			}
		}	
		else
		{
			alert('Please insert date.')
		}
	}
		
		
	function	toggle(val_cnt,val_id)
	{
		type			=	$('#'+val_id+'').val();
		var	ischecked	=	false;
		var	total_cnt	=	val_cnt;
		var x			= 	0;
		var y			= 	0;
		var	not_all		=	0;
		if(type == 'selectall')
		{
			var ischecked	=	$('#'+val_id+'').is(":checked");
			for(x;x<=total_cnt;x++)
			{
				if(ischecked == true)
				{
					$('#cb_'+x+'').attr('checked' , true);
				}
				else
				{
					$('#cb_'+x+'').attr('checked' , false);
				}
				
			}
		}	
		else
		{
			for(y;y<=total_cnt;y++)
			{
				var ischecked	=	$('#cb_'+y+'').is(":checked");
				if(ischecked == false)
				{
					not_all++;
				}
			}
			if(not_all > 0)
			{
				$('#selectall').attr('checked' , false);
			}
			else
			{
				$('#selectall').attr('checked' , true);
			}
		}
	}
	
	function	Go_print()
	{
		location='../Dispatch/reports/index.php';
	}
	
	function isnumeric(num,id)
	{
		var ValidChars ="0123456789";
		var Isnumber= "";
		var Char;
		
		for (var i=0; i < num.length; i++)
		{
			Char = num.charAt(i);
			if(ValidChars.indexOf(Char) != -1)
			{
				Isnumber = Isnumber + Char;
			}
		}
		document.getElementById(id).value = Isnumber;
	}
	
	function	focus_here(val_here)
	{
		var	focus_to	=	'#'+val_here;
		$(focus_to).focus();
	}
		
	function isEmpty(val)
	{
		var whiteSpace	=	0;
		var isEmpty		=	false;
		
		/* CHECK IF CONTAINS WHITESPACES ONLY */
		for (i = 0; i < val.length; i++)
		{
			if (val.charAt(i) == ' ')
			{
				whiteSpace++;
			}
		}
		
		if (val.length == whiteSpace)
		{
			isEmpty = true;
		}
		
		/* CHECK IF EMPTY */
		if (val == '')
		{
			isEmpty = true;
		}
		if (isNaN(val) == false && parseFloat(val) == 0)
		{
			isEmpty = true;
		}
		
		/* return boolean */
		return isEmpty;
	}    // end of isEmpty() function
	
	
	
	function	Cancel()
	{
		var isSubmit	=	confirm('Are you sure you want to cancel this transaction?');
		if(isSubmit == true)
		{
			location='index.php?targer=SCHEDULER';
		}
	}
	
	
	function	back	()
	{
		location	=	'main.php';
	}
</script>
</head>
<body onload="List_order();" >
		<form name="dataform" id="dataform">
			<div id="divsearch">
				<table width="100%" border="0" class="Title_style">
					<tr>
						<td width="20%" `>
							&nbsp;
						</td>
							<td width="20%" align="right" >
								INVOICE	#
							</td>
								<td width="20%" align="left">
								 	<input type="text" name="txtInvoice" id="txtInvoice" value="" onkeyup="Save_data(event);isnumeric(this.value,this.id);" autocomplete="off">
								</td>
									<td width="20%" align="left">
										&nbsp;
									</td>
										<td width="20%" align="right">
											&nbsp;
										</td>
					</tr>
					<tr>
						<td width="100%" colspan="5" align="center">
							<div id="divloader" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
						</td>
					</tr>
				</table>
			</div>
			<table width="100%" border="0">
				<tr>
					<td colspan="5">
						<div id="divlist"></div>
					</td>
				</tr>
			</table>
			<table width="100%" border="0">
				<tr>
					<td>
						<div id="divresponse" style="display:none;" class="d0"></div>
					</td>
				</tr>
				<tr>
					<td width="100%" align="center">
						<div id="divloader_response" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
					</td>
				</tr>
			</table>
		</form>
		
		<div id="divdebug"></div>
	</body>
</html>
