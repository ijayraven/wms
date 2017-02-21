<?php
/********************************************************************************************************************
* FILE NAME :	Received.php																						*
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
* 2013/08/04	Raymond A. Galaroza																					*
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
	
	if ($action == 'TRANSACTION_LIST') 
	{
		$type		=	$_GET['TYPE'];
		$invoice 	=	$_GET['VAR_VAL'];
		$aData		=	array();
		$View	=	"<table border='0' width='100%'>";
		$View	.=		"<th>";
		$View	.=			"DISPATCH ".$type;
		$View	.=		"</th>";
		$View	.=	"</table>";
		$View	.=	"<table border='0' width='100%'>";
		$View	.=		"<tr class='Text_header'>";
		$View	.=			"<td width='30%' align='right'>";
		$View	.=				"&nbsp";
		$View	.=			"</td>";
		$View	.=			"<td width='10%' align='center'>";
		$View	.=				"INVOICE NO.";
		$View	.=			"</td>";
		$View	.=			"<td width='60%' align='left'>";
		$View	.=				"<input type='text' name='manila_invoice' id='manila_invoice' value='' onkeyup=receive_list('$type',event,this.value);>";
		$View	.=			"</td>";
		$View	.=		"</tr>";
		$View	.=	"</table>";
		$View	.=	"<table border='0' width='100%'>";
		$View	.=		"<tr class='Header_style'>";
		$View	.=			"<td width='15%' align='center'>";
		$View	.=				"TRACKING NO.";
		$View	.=			"</td>";
		$View	.=					"<td width='35%' align='center'>";
		$View	.=						"CUSTOMER";
		$View	.=					"</td>";
		$View	.=							"<td width='10%' align='center'>";
		$View	.=								"INVOICENO";
		$View	.=							"</td>";
		$View	.=									"<td width='10%' align='center'>";
		$View	.=										"INVOICEAMOUNT";
		$View	.=									"</td>";
		$View	.=											"<td width='10%' align='center'>";
		$View	.=												"SOFNO";
		$View	.=											"</td>";
		$View	.=													"<td width='10%' align='center' nowrap>";
		$View	.=														"DELIVER DATE";
		$View	.=													"</td>";
		$View	.=															"<td width='5%' align='center'>";
		$View	.=																"ACTION";
		$View	.=															"</td>";
		$View	.=		"</tr>";
		if ($type == 'MANILA')
		{
			$cnt 		=	" SELECT count(*) as CNT FROM ".DISPATCH_DB.".DISPATCH_METROMANILA_DTL D ";
			$cnt 		.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_ORDER AS O ON O.TRANSEQ = D.TRANSEQ ";
			$cnt 		.=	" WHERE D.STATUS = 'OPEN' AND O.STATUS = 'DELIVER' ";
			if (!empty($invoice)) 
			{
			$cnt 		.=	"and D.INVOICENO = '{$invoice}' ";
			}
			$rscnt		=	$Filstar_conn->Execute($cnt);
			$row_cnt	=	$rscnt->fields['CNT'];
			$rs_cnt		=	$row_cnt['CNT'];
			if ($rs_cnt > 0) 
			{		
				$sel		=	"SELECT D.ID_DTL,D.TRANSEQ,D.CUSTCODE,D.CUSTNAME,D.INVOICENO,D.INVOICEAMOUNT,D.SOFNO ";
				$sel 		.=	" FROM ".DISPATCH_DB.".DISPATCH_METROMANILA_DTL D ";
				$sel 		.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_ORDER AS O ON O.TRANSEQ = D.TRANSEQ ";
				$sel 		.=	" WHERE D.STATUS = 'OPEN' AND O.STATUS = 'DELIVER' ";
				if (!empty($invoice)) 
				{
				$sel 		.=	"and D.INVOICENO = '{$invoice}' ";
				}
				$sel 		.=	"ORDER BY D.TRANSEQ DESC";
				$rssel		=	$Filstar_conn->Execute($sel);
				$x=1;
				while (!$rssel->EOF)
				{
					$aData[$rssel->fields['TRANSEQ']][$rssel->fields['CUSTCODE'].'-'.$rssel->fields['CUSTNAME']][$rssel->fields['INVOICENO']]['ID_DTL']	=	$rssel->fields['ID_DTL'];
					$aData[$rssel->fields['TRANSEQ']][$rssel->fields['CUSTCODE'].'-'.$rssel->fields['CUSTNAME']][$rssel->fields['INVOICENO']]['INVOICEAMOUNT']	=	$rssel->fields['INVOICEAMOUNT'];
					$aData[$rssel->fields['TRANSEQ']][$rssel->fields['CUSTCODE'].'-'.$rssel->fields['CUSTNAME']][$rssel->fields['INVOICENO']]['SOFNO']	=	$rssel->fields['SOFNO'];
					$rssel->MoveNext();
				}
				foreach ($aData as $tracking_no=>$val_cust)
				{
					foreach ($val_cust as $customer=>$val_invoice)
					{
						foreach ($val_invoice as $invoice=>$val)
						{
							$id		=	$val['ID_DTL'];
							$concat	=	$type.'_'.$x;
							$View	.=		"<tr>";
							$View	.=			"<td width='15%' align='left' class='Text_header'>";
							$View	.=				$tracking_no;
							$View	.=			"</td>";
							$View	.=					"<td width='35%' align='left' class='Text_header'>";
							$View	.=						$customer;
							$View	.=					"</td>";
							$View	.=							"<td width='10%' align='right' class='Text_header'>";
							$View	.=								$invoice;
							$View	.=							"</td>";
							$View	.=									"<td width='10%' align='right' class='Text_header'>";
							$View	.=										number_format($val['INVOICEAMOUNT'],2);
							$View	.=									"</td>";
							$View	.=											"<td width='10%' align='right' class='Text_header'>";
							$View	.=												$val['SOFNO'];
							$View	.=											"</td>";
							$View	.=													"<td width='10%' align='left' class='Text_header'>";
							$View	.=														"<input type='text' name='deliverdate_$concat' id='deliverdate_$concat' value='' size='3' readonly>";
							$View	.=														"<img src='../../calendar/calendar.gif' width='20' height='14' id='datehere_$concat'>";
							$View	.=													"</td>";
							$View	.=															"<td width='5%' align='center' class='Text_header'>";
							$View	.=																"<input type='button' name='close_transaction_$x' id='close_transaction_$x' value='Close' size='5' onclick=Close_trans('$type',$x); class='small_button'>";
							$View	.=																"<input type='hidden' name='hdn_id_$concat' id='hdn_id_$concat' value='$id'>";
							$View	.=															"</td>";
							$View	.=		"</tr>";
							$x++;
						}
					}
				}
				$View	.=	"</table>";
			}
			else 
			{
				$zer0	=	1;
			}
		}
		elseif ($type == 'PANDAYAN')
		{
			$cnt 		=	"SELECT count(*) as CNT FROM ".DISPATCH_DB.".DISPATCH_PANDAYAN_DTL D ";
			$cnt 		.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_ORDER AS O ON O.TRANSEQ = D.TRANSEQ ";
			$cnt 		.=	" WHERE D.STATUS = 'OPEN' AND O.STATUS = 'DELIVER' ";
			if (!empty($invoice)) 
			{
			$cnt 		.=	"and D.INVOICENO = '{$invoice}' ";
			}
			$rscnt		=	$Filstar_conn->Execute($cnt);
			$rs_cnt		=	$rscnt->fields['CNT'];
			if ($rs_cnt > 0) 
			{
				$sel		=	"SELECT D.ID_DTL,D.TRANSEQ,D.CUSTCODE,D.CUSTNAME,D.INVOICENO,D.INVOICEAMOUNT,D.SOFNO ";
				$sel 		.=	" FROM ".DISPATCH_DB.".DISPATCH_PANDAYAN_DTL D  ";
				$sel 		.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_ORDER AS O ON O.TRANSEQ = D.TRANSEQ ";
				$sel 		.=	" WHERE D.STATUS = 'OPEN' AND O.STATUS = 'DELIVER' ";
				if (!empty($invoice)) 
				{
				$sel 		.=	"and D.INVOICENO = '{$invoice}' ";
				}
				$sel 		.=	"ORDER BY D.TRANSEQ DESC";
				$rssel		=	$Filstar_conn->Execute($sel);
				$x=1;
				while (!$rssel->EOF)
				{
					$aData[$rssel->fields['TRANSEQ']][$rssel->fields['CUSTCODE'].'-'.$rssel->fields['CUSTNAME']][$rssel->fields['INVOICENO']]['ID_DTL']	=	$rssel->fields['ID_DTL'];
					$aData[$rssel->fields['TRANSEQ']][$rssel->fields['CUSTCODE'].'-'.$rssel->fields['CUSTNAME']][$rssel->fields['INVOICENO']]['INVOICEAMOUNT']	=	$rssel->fields['INVOICEAMOUNT'];
					$aData[$rssel->fields['TRANSEQ']][$rssel->fields['CUSTCODE'].'-'.$rssel->fields['CUSTNAME']][$rssel->fields['INVOICENO']]['SOFNO']	=	$rssel->fields['SOFNO'];
					$rssel->MoveNext();
				}
				foreach ($aData as $tracking_no=>$val_cust)
				{
					foreach ($val_cust as $customer=>$val_invoice)
					{
						foreach ($val_invoice as $invoice=>$val)
						{
							$id		=	$val['ID_DTL'];
							$concat	=	$type.'_'.$x;
							$View	.=		"<tr>";
							$View	.=			"<td width='15%' align='left' class='Text_header'>";
							$View	.=				$tracking_no;
							$View	.=			"</td>";
							$View	.=					"<td width='35%' align='left' class='Text_header'>";
							$View	.=						$customer;
							$View	.=					"</td>";
							$View	.=							"<td width='10%' align='right' class='Text_header'>";
							$View	.=								$invoice;
							$View	.=							"</td>";
							$View	.=									"<td width='10%' align='right' class='Text_header'>";
							$View	.=										number_format($val['INVOICEAMOUNT'],2);
							$View	.=									"</td>";
							$View	.=											"<td width='10%' align='right' class='Text_header'>";
							$View	.=												$val['SOFNO'];
							$View	.=											"</td>";
							$View	.=													"<td width='10%' align='left' class='Text_header'>";
							$View	.=														"<input type='text' name='deliverdate_$concat' id='deliverdate_$concat' value='' size='3' readonly>";
							$View	.=														"<img src='../../calendar/calendar.gif' width='20' height='14' id='datehere_$concat'>";
							$View	.=													"</td>";
							$View	.=															"<td width='5%' align='center' class='Text_header'>";
							$View	.=																"<input type='button' name='close_transaction_$x' id='close_transaction_$x' value='Close' size='5' onclick=Close_trans('$type',$x); class='small_button'>";
							$View	.=																"<input type='hidden' name='hdn_id_$concat' id='hdn_id_$concat' value='$id'>";
							$View	.=															"</td>";
							$View	.=		"</tr>";
							$x++;
						}
					}
				}
				$View	.=	"</table>";
			}
			else 
			{
				$zer0	=	1;
			}
		}
		elseif ($type == 'PROVINCE')
		{
			$cnt 		=	"SELECT count(*) as CNT FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL D ";
			$cnt 		.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_ORDER AS O ON O.TRANSEQ = D.TRANSEQ ";
			$cnt 		.=	" WHERE D.STATUS = 'OPEN' AND O.STATUS = 'DELIVER' ";
			if (!empty($invoice)) 
			{
			$cnt 		.=	"and D.INVOICENO = '{$invoice}' ";
			}
			$rscnt		=	$Filstar_conn->Execute($cnt);
			$rs_cnt		=	$rscnt->fields['CNT'];
			if ($rs_cnt > 0) 
			{
				$sel		=	"SELECT D.ID_DTL,D.TRANSEQ,D.CUSTCODE,D.CUSTNAME,D.INVOICENO,D.INVOICEAMOUNT,D.SOFNO ";
				$sel 		.=	" FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL D  ";
				$sel 		.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_ORDER AS O ON O.TRANSEQ = D.TRANSEQ ";
				$sel 		.=	" WHERE D.STATUS = 'OPEN' AND O.STATUS = 'DELIVER' ";
				if (!empty($invoice)) 
				{
				$sel 		.=	"and D.INVOICENO = '{$invoice}' ";
				}
				$sel 		.=	"ORDER BY D.TRANSEQ DESC";
				$rssel		=	$Filstar_conn->Execute($sel);
				$x=1;
				while (!$rssel->EOF)
				{
					$aData[$rssel->fields['TRANSEQ']][$rssel->fields['CUSTCODE'].'-'.$rssel->fields['CUSTNAME']][$rssel->fields['INVOICENO']]['ID_DTL']	=	$rssel->fields['ID_DTL'];
					$aData[$rssel->fields['TRANSEQ']][$rssel->fields['CUSTCODE'].'-'.$rssel->fields['CUSTNAME']][$rssel->fields['INVOICENO']]['INVOICEAMOUNT']	=	$rssel->fields['INVOICEAMOUNT'];
					$aData[$rssel->fields['TRANSEQ']][$rssel->fields['CUSTCODE'].'-'.$rssel->fields['CUSTNAME']][$rssel->fields['INVOICENO']]['SOFNO']	=	$rssel->fields['SOFNO'];
					$rssel->MoveNext();
				}
				foreach ($aData as $tracking_no=>$val_cust)
				{
					foreach ($val_cust as $customer=>$val_invoice)
					{
						foreach ($val_invoice as $invoice=>$val)
						{
							$id		=	$val['ID_DTL'];
							$concat	=	$type.'_'.$x;
							$View	.=		"<tr>";
							$View	.=			"<td width='15%' align='left' class='Text_header'>";
							$View	.=				$tracking_no;
							$View	.=			"</td>";
							$View	.=					"<td width='35%' align='left' class='Text_header'>";
							$View	.=						$customer;
							$View	.=					"</td>";
							$View	.=							"<td width='10%' align='right' class='Text_header'>";
							$View	.=								$invoice;
							$View	.=							"</td>";
							$View	.=									"<td width='10%' align='right' class='Text_header'>";
							$View	.=										number_format($val['INVOICEAMOUNT'],2);
							$View	.=									"</td>";
							$View	.=											"<td width='10%' align='right' class='Text_header'>";
							$View	.=												$val['SOFNO'];
							$View	.=											"</td>";
							$View	.=													"<td width='10%' align='left' class='Text_header'>";
							$View	.=														"<input type='text' name='deliverdate_$concat' id='deliverdate_$concat' value='' size='3' readonly>";
							$View	.=														"<img src='../../calendar/calendar.gif' width='20' height='14' id='datehere_$concat'>";
							$View	.=													"</td>";
							$View	.=															"<td width='5%' align='center' class='Text_header'>";
							$View	.=																"<input type='button' name='close_transaction_$x' id='close_transaction_$x' value='Close' size='2' onclick=Close_trans('$type',$x); class='small_button'>";
							$View	.=																"<input type='hidden' name='hdn_id_$concat' id='hdn_id_$concat' value='$id'>";
							$View	.=															"</td>";
							$View	.=		"</tr>";
							$x++;
						}
					}
				}
				$View	.=	"</table>";
			}
			else 
			{
				$zer0	=	1;
			}
		}
		
		if ($zer0 == 1) 
		{
			$View	=	"<table border='0' width='100%'>";
			$View	.=		"<tr bgcolor='Silver' style='font-size: 11pt; font-weight: bold;'>";
			$View	.=			"<td width='100%' align='center'>";
			$View	.=				"No Record found...";
			$View	.=			"</td>";
			$View	.=		"</tr>";
			$View	.=	"</table>";
		}
		echo $View;
		exit();
	}
	
	if ($action	==	'CLOSE_NOW') 
	{
		$type		=	$_GET['TYPE'];
		$close_id	=	$_GET['CLOSE_ID'];
		
		try {
			$Filstar_conn->StartTrans();
			if ($type	==	'MANILA') 
			{
				$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_METROMANILA_DTL SET STATUS = 'CLOSE', CLOSE_BY = '{$_SESSION['username']}', DATE_CLOSE = sysdate() where ID_DTL = '{$close_id}'  ";
			}
			elseif ($type == 'PANDAYAN')
			{
				$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_PANDAYAN_DTL SET STATUS = 'CLOSE', CLOSE_BY = '{$_SESSION['username']}', DATE_CLOSE = sysdate() where ID_DTL = '{$close_id}'  ";
			}
			elseif ($type == 'PROVINCE')
			{
				$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL SET STATUS = 'CLOSE', CLOSE_BY = '{$_SESSION['username']}', DATE_CLOSE = sysdate() where ID_DTL = '{$close_id}'  ";
			}
			$rsupdate	=	$Filstar_conn->Execute($update);
			if ($rsupdate == false) 
			{
				throw new Exception(mysql_errno().":".mysql_error());
			}
			echo "1";
			$Filstar_conn->CompleteTrans();
		}
		catch (Exception $e)
		{
			echo $e->__toString();
			$Filstar_conn->CompleteTrans();
		}
		
		exit();
	}
	
	if ($action == 'COUNT_RECORD') 
	{
		$invoice	=	$_GET['VAR_VAL'];
		if ($_GET['TYPE'] == 'MANILA') 
		{
			$cnt 		=	" SELECT COUNT(DISTINCT(D.ID_DTL)) as CNT FROM ".DISPATCH_DB.".DISPATCH_METROMANILA_DTL D ";
			$cnt 		.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_ORDER AS O ON O.TRANSEQ = D.TRANSEQ ";
			$cnt 		.=	" WHERE D.STATUS = 'OPEN' AND O.STATUS = 'DELIVER' ";
			if (!empty($invoice)) 
			{
			$cnt 		.=	"AND D.INVOICENO = '{$invoice}' ";
			}
			$rscnt		=	$Filstar_conn->Execute($cnt);
			$retval		=	$rscnt->fields['CNT'];
			echo $retval;
		}
		elseif ($_GET['TYPE'] == 'PANDAYAN')
		{
			$cnt 		=	"SELECT COUNT(DISTINCT(D.ID_DTL)) as CNT FROM ".DISPATCH_DB.".DISPATCH_PANDAYAN_DTL D ";
			$cnt 		.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_ORDER AS O ON O.TRANSEQ = D.TRANSEQ ";
			$cnt 		.=	" WHERE D.STATUS = 'OPEN' AND O.STATUS = 'DELIVER' ";
			if (!empty($invoice)) 
			{
			$cnt 		.=	"AND D.INVOICENO = '{$invoice}' ";
			}
			$rscnt		=	$Filstar_conn->Execute($cnt);
			$retval		=	$rscnt->fields['CNT'];
			echo $retval;
		}
		else if ($_GET['TYPE'] == 'PROVINCE') 
		{
			$cnt 		=	" SELECT COUNT(DISTINCT(D.ID_DTL)) as CNT FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL D";
			$cnt 		.=	" LEFT JOIN ".DISPATCH_DB.".DISPATCH_ORDER AS O ON O.TRANSEQ = D.TRANSEQ ";
			$cnt 		.=	" WHERE D.STATUS = 'OPEN' AND O.STATUS = 'DELIVER' ";
			if (!empty($invoice)) 
			{
			$cnt 		.=	"AND D.INVOICENO = '{$invoice}' ";
			}
			$rscnt		=	$Filstar_conn->Execute($cnt);
			$retval		=	$rscnt->fields['CNT'];
			echo $retval;
		}
		exit();
	}
?>
<html>
<title>PRINTING</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../css/style.css);</style>
<style type="text/css">@import url(../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../calendar/calendar-setup.js"></script>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<script>
		function	receive_list(val_type,evt,var_val)
			{
				var process	=	'NO';
				if(evt == '')
				{
					process	=	'YES';
					
				}
				else
				{
					var evthandler	=	(evt.charCode)	?	evt.charCode	:	evt.keyCode;
					if(evthandler == 13)
					{
						process	=	'YES';
					}
				}
				if(process	==	'YES')
				{
					count_cal(val_type,var_val);
					$.ajax({
							url			:	'Received.php?action=TRANSACTION_LIST&TYPE='+val_type+'&VAR_VAL='+var_val,
							beforeSend	:	function()
										{
											$('#divloader').show();
										},
							success		:	function(response)
										{
											$('#divloader').hide();
											$('#divresponse').html(response);
											$('#divresponse').show();
											var	val_total	=	$('#hdntotal').val();
											if(val_total != '')
											{
												onloadcal(val_type,val_total);
											}
										}
					});
				}	
			}
			
			
			function count_cal(val_type,var_val)
			{
				$.ajax({
						url			:	'Received.php?action=COUNT_RECORD&TYPE='+val_type+'&VAR_VAL='+var_val,
						beforeSend	:	function()
									{
										$('#divloader').show();
									},
						success		:	function(response)
									{
										$('#hdntotal').val('');
										$('#hdntotal').val(response);
									}
				});
				
			}
			
			
			function onloadcal(val_type,val_limit)
			{
				var limit =	2;
				for(var x=1;x<=val_limit;+x++)
				{
					Calendar.setup
					(
					   {
					     inputField  : "deliverdate_"+val_type+"_"+x,    // ID of the input field
					     ifFormat    : "%Y-%m-%d", 						// The Date Format
					     button      : "datehere_"+val_type+"_"+x		// ID of the button
					   }
					);
				}
			}
			
			
			function	Close_trans(val_type,val_cnt)
			{
				var	close_id	=	$('#hdn_id_'+val_type+'_'+val_cnt).val();
				var	isDate		=	$('#deliverdate_'+val_type+'_'+val_cnt).val();
				if(isDate == '')
				{
					alert('Please insert delivered date!');
				}
				else
				{
					var isSubmit	=	confirm('Are you sure you want to Close this Invoice?');
					if(isSubmit == true)
					{
						$.ajax({
								url		:	'Received.php?action=CLOSE_NOW&TYPE='+val_type+'&CLOSE_ID='+close_id,
								success	:	function(response)
										{
											if(response == 1)
											{
												alert('Invoice was successfully closed.');
												receive_list(val_type,'','');
											}
											else
											{
												$('#divresponse').html(response);
												$('#divresponse').show();
											}
										}
						});
					}	
				}	
			}
			
			
			function	back()
			{
				location	=	'main.php';
			}
</script>
</head>
<body >
	<form name="dataform" id="dataform">
		<table width="100%" border="0">
			<tr>
				<td width="30%" align="center">
					<input type="button" name="btnprint_manila" id="btnprint_manila" value="MANILA" title="Printing list for MANILA" onclick="receive_list(this.value,'','');" class="small_button">
				</td>
				<td width="40%" align="center">
					<input type="button" name="btnprint_pandayan" id="btnprint_pandayan" value="PANDAYAN" title="Printing list for PANDAYAN" onclick="receive_list(this.value,'','');" class="small_button">
				</td>
				<td width="30%" align="center">
					<input type="button" name="btnprint_province" id="btnprint_province" value="PROVINCE" title="Printing list for PROVINCE" onclick="receive_list(this.value,'','');" class="small_button">
				</td>
			</tr>
		</table>
		<table width="100%" border="0">
			<tr>
				<td width="100%" align="center">
					<div id="divloader" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
					<input type="hidden" name="hdntotal" id="hdntotal" value="">
				</td>
			</tr>	
		</table>
		<div id="divresponse" class="Text_header" style="display:none;"></div>
	</form>
</body>
</html>