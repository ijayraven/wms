<?php
/********************************************************************************************************************
* FILE NAME :	printing.php																						*
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

if ($action == 'PRINTLIST') 
	{
		$type	=	$_GET['TYPE'];
		$cnt 	=	"SELECT COUNT(DISTINCT(TRANSEQ)) as CNT FROM ".DISPATCH_DB.".DISPATCH_ORDER WHERE STATUS = 'DELIVER' AND DELIVERTO = '{$type}' AND PRINT_STATUS = 'OPEN' ";
		$rscnt	=	$Filstar_conn->Execute($cnt);
		$record	=	$rscnt->fields['CNT'];
		$View	=	"<table border='0' width='100%'>";
		$View	.=		"<tr bgcolor='Silver' class='Header_style'>";
		$View	.=			"<td width='15%' align='center'>";
		$View	.=				"PREPAREDDATE";
		$View	.=			"</td>";
		$View	.=					"<td width='20%' align='center'>";
		$View	.=						"TRACKING NO.";
		$View	.=					"</td>";
		$View	.=							"<td width='20%' align='center'>";
		$View	.=								"ROUTE";
		$View	.=							"</td>";
		$View	.=									"<td width='10%' align='center'>";
		$View	.=										"VANNO";
		$View	.=									"</td>";
		$View	.=											"<td width='15%' align='center'>";
		$View	.=												"PLATENO";
		$View	.=											"</td>";
		$View	.=													"<td width='20%' align='center'>";
		$View	.=														"ACTION";
		$View	.=													"</td>";
		$View	.=		"</tr>";
		if ($record > 0) 
		{
			$sel	=	"SELECT DISTINCT(TRANSEQ) FROM ".DISPATCH_DB.".DISPATCH_ORDER WHERE STATUS = 'DELIVER' AND DELIVERTO = '{$type}'";
			$rssel	=	$Filstar_conn->Execute($sel);
			while (!$rssel->EOF) 
			{
				$tracking_no	=	$rssel->fields['TRANSEQ'];
				$sel_type		=	"SELECT ID,TRANSEQ,ROUTE,VANNO,PLATENO,PREPAREDDATE ";
				if ($type == 'MANILA') 
				{
					$sel_type		.=	" FROM ".DISPATCH_DB.".DISPATCH_METROMANILA_HDR WHERE TRANSEQ = '{$tracking_no}' AND PRINT_STATUS = 'OPEN' ORDER BY PREPAREDDATE DESC";
				}
				elseif ($type == 'PANDAYAN')
				{
					$sel_type		.=	" FROM ".DISPATCH_DB.".DISPATCH_PANDAYAN_HDR WHERE TRANSEQ = '{$tracking_no}' AND PRINT_STATUS = 'OPEN' ORDER BY PREPAREDDATE DESC";
				}
				elseif ($type == 'PROVINCE')
				{
					$sel_type		.=	" FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_HDR WHERE TRANSEQ = '{$tracking_no}' AND PRINT_STATUS = 'OPEN' ORDER BY PREPAREDDATE DESC";
				}
				$rssel_type		=	$Filstar_conn->Execute($sel_type);
				while (!$rssel_type->EOF) 
				{
					$id			=	$rssel_type->fields['ID'];
					$transeq	=	$rssel_type->fields['TRANSEQ'];
					$View	.=		"<tr  onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
					$View	.=			"<td width='15%' align='center'>";
					$View	.=				$rssel_type->fields['PREPAREDDATE'];
					$View	.=			"</td>";
					$View	.=					"<td width='20%' align='center'>";
					$View	.=						$transeq;
					$View	.=					"</td>";
					$View	.=							"<td width='20%' align='center'>";
					$View	.=								$rssel_type->fields['ROUTE'];
					$View	.=							"</td>";
					$View	.=									"<td width='10%' align='center'>";
					$View	.=										$rssel_type->fields['VANNO'];
					$View	.=									"</td>";
					$View	.=											"<td width='15%' align='center'>";
					$View	.=												$rssel_type->fields['PLATENO'];
					$View	.=											"</td>";
					$View	.=													"<td width='20%' align='center'>";
					$View	.=														"<input type='button' name='btnprint' id='btnprint' value='print' onclick=print_this('$type','$id','$transeq'); title='Print this Schedule'; class='small_button'>";
					if ($type == 'PROVINCE') 
					{
					$View	.=														"<input type='button' name='btnprint_dr' id='btnprint_dr' value='DR' onclick=print_this('$type','$id','DR'); title='Print this Schedule'; class='small_button'>";	
					}
					$View	.=														"<input type='button' name='btnclose' id='btnclose' value='close' onclick=close_this('$type','$id','$transeq'); title='Close this Schedule'; class='small_button'>";
					$View	.=													"</td>";
					$View	.=		"</tr>";
					$rssel_type->MoveNext();
				}
				$rssel->MoveNext();
			}
		}
		else 
		{
			$View	.=		"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
			$View	.=			"<td width='100%' align='center' colspan='6'>";
			$View	.=				"No Record found...";
			$View	.=			"</td>";
			$View	.=		"</tr>";
			$View	.=	"</table>";
		}
		echo $View;
		exit();
	}
	
	
	if ($action == 'CLOSE_NOW') 
	{
		$type			=	$_GET['VAL_TYPE'];
		$id				=	$_GET['VAL_ID'];
		$val_transeq	=	$_GET['VAL_TRANSEQ'];
		try {
			$Filstar_conn->StartTrans();
			if ($type == 'MANILA') 
			{
				$close		=	"UPDATE ".DISPATCH_DB.".DISPATCH_METROMANILA_HDR SET PRINT_STATUS = 'CLOSE' , CLOSE_PRINT = sysdate() WHERE ID = '{$id}' ";
			}
			elseif ($type == 'PANDAYAN')
			{
				$close		=	"UPDATE ".DISPATCH_DB.".DISPATCH_PANDAYAN_HDR SET PRINT_STATUS = 'CLOSE' , CLOSE_PRINT = sysdate() WHERE ID = '{$id}' ";
			}
			elseif ($type == 'PROVINCE')
			{
				$close		=	"UPDATE ".DISPATCH_DB.".DISPATCH_PROVINCE_HDR SET PRINT_STATUS = 'CLOSE' , CLOSE_PRINT = sysdate() WHERE ID = '{$id}' ";
			}
			$rsclose	=	$Filstar_conn->Execute($close);
			if ($rsclose == false) 
			{
				throw new Exception(mysql_errno().":".mysql_error());
			}
			$closeorder		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET PRINT_CLOSE = 'CLOSE' WHERE TRANSEQ = '{$val_transeq}' ";
			$rscloseorder	=	$Filstar_conn->Execute($closeorder);
			if ($rscloseorder == false) 
			{
				throw new Exception(mysql_errno().":".mysql_error());
			}
			echo 1;
			$Filstar_conn->CompleteTrans();
		}
		catch (Exception $e)
		{
			echo $e->__toString();
			$Filstar_conn->CompleteTrans();
		}
		exit();
	}
	
	if ($action == 'DRLIST') 
	{
		$trackingno	=	$_GET['TRACKINGNO'];
		$sel	 =	" SELECT ID_DTL,TRANSEQ,CUSTCODE,INVOICENO,INVOICEAMOUNT,SOFNO,PRODUCT_LINE,SIZE_OF_CTN,QTY_CTN,DR_NO,QTY_PKG,QTY_BDL,WEIGHT_BY_KILO ";
		$sel	.=	" FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL WHERE ID = '{$trackingno}' ";
		$rssel	 =	$Filstar_conn->Execute($sel);
		if ($rssel == false) 
		{
			echo $Filstar_conn->ErrorMsg();
			exit();
		}
		$aData	=	array();
		while (!$rssel->EOF) 
		{
			$id_dtl			=	$rssel->fields['ID_DTL'];
			$transeq		=	$rssel->fields['TRANSEQ'];
			$custcode		=	$rssel->fields['CUSTCODE'];
			$invoiceno		=	$rssel->fields['INVOICENO'];
			$invoiceamount	=	$rssel->fields['INVOICEAMOUNT'];
			$sofno			=	$rssel->fields['SOFNO'];
			$product_line	=	$rssel->fields['PRODUCT_LINE'];
			$size_of_ctn	=	$rssel->fields['SIZE_OF_CTN'];
			$qty_ctn		=	$rssel->fields['QTY_CTN'];
			$dr_no			=	$rssel->fields['DR_NO'];
			$qty_pkg		=	$rssel->fields['QTY_PKG'];
			$qty_bdl		=	$rssel->fields['QTY_BDL'];
			$weight_by_kilo	=	$rssel->fields['WEIGHT_BY_KILO'];
			
			$aData[$transeq][$custcode][$invoiceno]['ID']			=	$id_dtl;
			$aData[$transeq][$custcode][$invoiceno]['INVOICEAMOUNT']=	$invoiceamount;
			$aData[$transeq][$custcode][$invoiceno]['SOFNO']		=	$sofno;
			$aData[$transeq][$custcode][$invoiceno]['PRODUCT_LINE']	=	$product_line;
			$aData[$transeq][$custcode][$invoiceno]['SIZE_OF_CTN']	=	$size_of_ctn;
			$aData[$transeq][$custcode][$invoiceno]['QTY_CTN']		=	$qty_ctn;
			$aData[$transeq][$custcode][$invoiceno]['DR_NO']		=	$dr_no;
			$aData[$transeq][$custcode][$invoiceno]['QTY_PKG']		=	$qty_pkg;
			$aData[$transeq][$custcode][$invoiceno]['QTY_BDL']		=	$qty_bdl;
			$aData[$transeq][$custcode][$invoiceno]['WEIGHT_BY_KILO']=	$weight_by_kilo;
			$rssel->MoveNext();
		}
		if (is_array($aData))
		{
			$cnt=1;
			foreach ($aData as $tracking_no=>$val_cust)
			{
				foreach ($val_cust as $custcode=>$val_invoice)
				{
					$street	=	$global_func->Select_val($Filstar_conn,FDCRMS,"customer_address","StreetNumber","custno= '$custcode'");
					$town	=	$global_func->Select_val($Filstar_conn,FDCRMS,"customer_address","TownCity","custno= '$custcode'");
					$add	=	$street.','.$town;
					$show	.=	"<table width='100%' border='0' class='Text_header'>";
					$show	.=		"<tr>";
						$show	.=		"<td width='100%' align='center' class='Input_Style' colspan='6'>";
						$show	.=		FDC_HEADER;
						$show	.=		"</td>";
					$show	.=		"</tr>";
							$show	.=		"<tr>";
								$show	.=		"<td width='100%' align='center' colspan='6'>";
								$show	.=		FDC_ADDRESS;
								$show	.=		"</td>";
							$show	.=		"</tr>";
									$show	.=		"<tr>";
										$show	.=		"<td width='100%' align='center' colspan='6'>";
										$show	.=		FDC_TEL;
										$show	.=		"</td>";
									$show	.=		"</tr>";
											$show	.=		"<tr>";
												$show	.=		"<td width='100%' align='center' colspan='6'>";
												$show	.=		"<br>";
												$show	.=		"</td>";
											$show	.=		"</tr>";
													$show	.=		"<tr>";
														$show	.=		"<td width='100%' align='center' colspan='6'>";
														$show	.=		"DELIVERY RECEIPT";
														$show	.=		"</td>";
													$show	.=		"</tr>";
															$show	.=		"<tr>";
																$show	.=		"<td width='100%' align='center' colspan='6'>";
																$show	.=		"<br>";
																$show	.=		"</td>";
															$show	.=		"</tr>";
					$show	.=	"<tr>";
					$show	.=		"<td width='7%'>";
					$show	.=			"Del. To";
					$show	.=		"</td>";
							$show	.=		"<td width='1%'>";
							$show	.=			":";
							$show	.=		"</td>";
									$show	.=		"<td width='52%'>";
									$show	.=			$global_func->CustName($Filstar_conn,$custcode);
									$show	.=		"</td>";
											$show	.=		"<td width='9%' align='right'>";
											$show	.=			"DATE";
											$show	.=		"</td>";
													$show	.=		"<td width='1%' align='right'>";
													$show	.=			":";
													$show	.=		"</td>";
															$show	.=		"<td width='20%' align='left'>";
															$show	.=			date('F m, Y');
															$show	.=		"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
					$show	.=		"<td width='7%'>";
					$show	.=			"Address";
					$show	.=		"</td>";
							$show	.=		"<td width='1%'>";
							$show	.=			":";
							$show	.=		"</td>";
									$show	.=		"<td width='82%' colspan='4'>";
									$show	.=			$add;
									$show	.=		"</td>";
					$show	.=	"</tr>";
					$show	.=	"</table>";
					$show	.=	"<table width='100%' border='0' class='Text_header'>";
					$show	.=	"<tr>";
						$show	.=	"<td width='20%' align='center' class='Text_header' rowspan='2'>";
							$show	.=	"I T E M S";
						$show	.=	"</td>";
								$show	.=	"<td width='13%' align='center' class='Text_header'>";
									$show	.=	"SOF";
								$show	.=	"</td>";
										$show	.=	"<td width='12%' align='center' class='Text_header'>";
											$show	.=	"INV/SOF";
										$show	.=	"</td>";
											$show	.=	"<td width='25%' align='center' class='Text_header'>";
												$show	.=	"Q U A N T I T Y";
											$show	.=	"</td>";
													$show	.=	"<td width='15%' align='center' class='Text_header'>";
														$show	.=	"Declared";
													$show	.=	"</td>";
															$show	.=	"<td width='15%' align='center' class='Text_header' rowspan='2'>";
																$show	.=	"WEIGHT";
															$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='13%' align='center' class='Text_header'>";
							$show	.=	"No.";
						$show	.=	"</td>";
								$show	.=	"<td width='12%' align='center' class='Text_header'>";
									$show	.=	"DR No.";
								$show	.=	"</td>";
									$show	.=	"<td width='25%' align='center'>";
										$show	.=	"<table width='100%' border='1' class='Text_header'>";
										$show	.=		"<tr>";
											$show	.=		"<td width='34%' align='center' class='Text_header'>";
												$show	.=		"ctn";
											$show	.=		"</td>";
												$show	.=		"<td width='33%' align='center' class='Text_header'>";
													$show	.=		"pkg";
												$show	.=		"</td>";
														$show	.=		"<td width='34%' align='center' class='Text_header'>";
															$show	.=		"bdl";
														$show	.=		"</td>";
										$show	.=		"</tr>";
										$show	.=	"</table>";
									$show	.=	"</td>";
											$show	.=	"<td width='15%' align='center' class='Text_header'>";
												$show	.=	"Value";
											$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"</table>";
					foreach ($val_invoice as $invoice=>$val_)
					{
						$show	.=	"<table width='100%' border='0' class='Text_header'>";
						$id				=	$val_['ID'];
						$sofno			=	$val_['SOFNO'];
						$invoiceamount	=	$val_['INVOICEAMOUNT'];
						$drno			=	$val_['DR_NO'];
						$qty_ctn		=	$val_['QTY_CTN'];
						$qty_pkg		=	$val_['QTY_PKG'];
						$qty_bdl		=	$val_['QTY_BDL'];
						$weight			=	$val_['WEIGHT_BY_KILO'];
						$ordertype		=	$global_func->Remove_alpha($sofno);
						$categoty		=	$global_func->Select_val($Filstar_conn,FDCRMS,"orderheader","OrderCategory","OrderNo = '{$sofno}' ");
						if ($categoty == 'Invoice') 
						{
							$declared	=	$invoiceamount * INVOICE_RATE;
						}
						elseif ($categoty == 'STF')
						{
							$declared	=	$invoiceamount * STF_RATE;
						}
						else 
						{
							$declared	=	0;
						}
						
						$ndeclared	=	number_format($declared,2);
						
						$show	.=	"<tr>";
						$show	.=	"<td width='20%' align='center' class='Text_header' rowspan='2'>";
						$show	.=	$ordertype;
								$show	.=	"</td>";
									$show	.=	"<td width='13%' align='center' class='Text_header'>";
										$show	.=	$sofno;
									$show	.=	"</td>";
											$show	.=	"<td width='12%' align='center' class='Text_header'>";
												$show	.=	$invoice;
											$show	.=	"</td>";
												$show	.=	"<td width='25%' align='center'>";
													$show	.=	"<table width='100%' border='1' class='Text_header'>";
													$show	.=		"<tr>";
														$show	.=		"<td width='34%' align='center' class='Text_header'>";
															$show	.=		$qty_ctn;
														$show	.=		"</td>";
															$show	.=		"<td width='33%' align='center' class='Text_header'>";
																$show	.=		$qty_pkg;
															$show	.=		"</td>";
																	$show	.=		"<td width='34%' align='center' class='Text_header'>";
																		$show	.=		$qty_bdl;
																	$show	.=		"</td>";
													$show	.=		"</tr>";
													$show	.=	"</table>";
												$show	.=	"</td>";
														$show	.=	"<td width='15%' align='center' class='Text_header'>";
															$show	.=	"<input type='text' name='txtdeclared_$cnt' id='txtdeclared_$cnt' value='$ndeclared' size='8' style='text-align:center;' onkeyup='isnumeric(this.value,this.id);' onclick='Remove_val(this.id);' onselect='Remove_val(this.id);' onblur=Testing_val(this.id,this.value,'$cnt');>";
															$show	.=	"<input type='hidden' name='hdndeclared_$cnt' id='hdndeclared_$cnt' value='$ndeclared'>";
															$show	.=	"<input type='hidden' name='hdnid_$cnt' id='hdnid_$cnt' value='$id'>";
														$show	.=	"</td>";
																$show	.=	"<td width='15%' align='center' class='Text_header' rowspan='2'>";
																	$show	.=	$weight;
																$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"</table>";
					$cnt++;
					}
					$show	.=	"<tr><td>&nbsp";
					$show	.=	"<input type='hidden' name='hdncount' id='hdncount' value='$cnt'>";
					//$show	.=	"<input type='hidden' name='hndvoice' id='hndvoice' value='$invoice'>";
					$show	.=	"</td></tr>";
				}
			}
			$show	.=	"<table width='100%' >";
			$show	.=	"<tr>";
					$show	.=	"<td width='100%' align='center'>";
					$show	.=	"<div id='divloader_print' style='display:none;'><img src='../../images/loading/ajax-loader_fast.gif'></div>";
					$show	.=	"</td>";
				$show	.=	"</tr>";
				$show	.=	"<tr>";
					$show	.=	"<td width='100%' align='center'>";
					$show	.=	"<input type='button' name='btnsave' id='btnsave' value='SAVE & PRINT' onclick=Print_load('$tracking_no'); class='small_button'>";
					$show	.=	"</td>";
				$show	.=	"</tr>";
			$show	.=	"</table>";
			echo $show;
		}
		exit();
	}
	
	if ($action == 'SAVE_VALUE') 
	{
		$cnt	=	$_POST['hdncount']	-	1;
		try {
			$Filstar_conn->StartTrans();
			for ($x=1;$x<=$cnt;$x++)
			{
				$id			=	$_POST['hdnid_'.$x];
				$declared1	=	str_replace(',','',$_POST['txtdeclared_'.$x]);
				$declared2	=	str_replace(',','',$_POST['hdndeclared_'.$x]);
				$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL SET DECLARED_1 = '{$declared1}',DECLARED_2 = '{$declared2}' WHERE ID_DTL = '{$id}' ";
				$rsupdate	=	$Filstar_conn->Execute($update);
				if ($rsupdate == false) 
				{
					$global_func->AdminErrorLogs($update,$Filstar_conn->ErrorMsg(),__FILE__,__LINE__);
					throw new Exception($Filstar_conn->ErrorMsg());
				}
			}
			echo 1;
			$Filstar_conn->CompleteTrans();
		}
		catch (Exception $e)
		{
			echo $e->__toString();
			$Filstar_conn->CompleteTrans();
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
		function	print_list(val_type)
		{
			$.ajax({
					url			:	'printing.php?action=PRINTLIST&TYPE='+val_type,
					beforeSend	:	function()
								{
									$('#divloader').show();
								},
					success		:	function(response)
								{
									$('#divloader').hide();
									$('#divdebug').html(response);
									$('#divdebug').show();
								}
			});
		}
		
		function	print_this(val_type,val_id,is_DR)
		{
			if(val_type == 'MANILA')
			{
				window.open('manila_pdf.php?schedule_id='+val_id);
			}
			else if(val_type == 'PANDAYAN')
			{
				window.open('pandayan_pdf.php?schedule_id='+val_id);
			}
			else if(val_type == 'PROVINCE')
			{
				if(is_DR == 'DR')
				{
					$('#divprintlist').hide();
					$('#divprintdr').show();
					//window.open('province_dr_pdf.php?schedule_id='+val_id);
					$.ajax({
							url		:	'printing.php?action=DRLIST&TRACKINGNO='+val_id,
							success	:	function(response)
									{
										$('#divprintdr').show();
										$('#divresponse').html(response);
										$('#divresponse').show();
									}
					});
				}
				else
				{
					window.open('province_pdf.php?schedule_id='+val_id);
				}
			}
		}
		
		function	Print_load(val_tracking)
		{
			var isSubmit	=	confirm('Are you sure you wnat to Save & Print this Transaction?');
			if(isSubmit	== true)
			{
				var formdr	=	$('#datadr').serialize();
				/*$('#divdebug_dr').html(formdr);
				$('#divdebug_dr').show();*/
				$.ajax({
						type		:	'POST',
						data		:	formdr,
						url			:	'printing.php?action=SAVE_VALUE',
						beforeSend	:	function()
									{
										$('#divloader_print').show();
									},
						success		:	function(response)
									{
										if(response == 1)
										{
											$('#divloader_print').hide();
											window.open('province_dr_pdf.php?schedule_tracking='+val_tracking);
										}
										else
										{
											$('#divloader_print').hide();
											$('#divdebug_dr').html(response);
											$('#divdebug_dr').show();
										}	
									}
				});
			}
		}
		
		function	close_this(val_type,val_id,val_transeq)
		{
			var isSubmit	=	confirm('Are you sure you wnat to close this transaction?');
			if(isSubmit == true)
			{
				$.ajax({
						url			:	'printing.php?action=CLOSE_NOW&VAL_TYPE='+val_type+'&VAL_ID='+val_id+'&VAL_TRANSEQ='+val_transeq,
						beforeSend	:	function()
									{
										$('#divloader').show();
									},
						success		:	function(response)
									{
										if(response == 1)
										{
											print_list(val_type);
										}
										else
										{
											$('#divloader').hide();
											$('#divdebug').html(response);
											$('#divdebug').show();
										}
									}
				});
			}	
		}
		
		function	Remove_val(val_id)
		{
			$('#'+val_id).val('');
		}
		
		function	Testing_val(val_id,_val_value,val_cnt)
		{
			var Declared	=	$('#'+val_id).val();
			if(Declared == '')
			{
				var this_default	=	$('#hdndeclared_'+val_cnt).val();
				$('#'+val_id).val(this_default);
			}
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
</script>
</head>
<body >
	<div id="divprintlist">
	<form name="dataform" id="dataform">
			<table width="100%" border="0">
				<tr >
					<td width="30%" align="center">
						<input type="button" name="btnprint_manila" id="btnprint_manila" value="MANILA" title="Printing list for MANILA" onclick="print_list(this.value);" class="small_button">
					</td>
					<td width="40%" align="center">
						<input type="button" name="btnprint_pandayan" id="btnprint_pandayan" value="PANDAYAN" title="Printing list for PANDAYAN" onclick="print_list(this.value);" class="small_button">
					</td>
					<td width="30%" align="center">
						<input type="button" name="btnprint_province" id="btnprint_province" value="PROVINCE" title="Printing list for PROVINCE" onclick="print_list(this.value);" class="small_button">
					</td>
				</tr>
			</table>
			<div id="divdebug"></div>
		</form>
	</div>
	<form>
		<table width="100%" border="0">
			<tr>
				<td width="100%" align="center">
					<div id="divloader" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
				</td>
			</tr>	
		</table>
	</form>
	<div id="divprintdr" style="display:none;">
		<form name="datadr" id="datadr">
			<table width="100%" border="0">
				<tr>
					<td width="100%">
						<div id="divresponse"></div>
					</td>
				</tr>
				<tr>
					<td width="100%">
						<div id="divdebug_dr"></div>
					</td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>