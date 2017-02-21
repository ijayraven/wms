<?php
/********************************************************************************************************************
* FILE NAME :	scheduler_main.php																					*
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
$limit	=	10;
if ($action == 'DISPLAY_ALL') 
{
	$page			=	$_GET['page'];
	$form_tracking	=	$_GET['txttracking'];
	$form_date		=	$_GET['txtdate'];
	$form_type		=	$_GET['seltype'];
	$cnt		=	" SELECT TRANSEQ,DELIVERTO ";
	$cnt		.=	" FROM  ".DISPATCH_DB.".DISPATCH_ORDER WHERE STATUS = 'INPROCESS' ";
	if (!empty($form_tracking)) 
	{
	$cnt		.=	" AND TRANSEQ = '{$form_tracking}' ";
	}
	if (!empty($form_type))
	{
		if (($form_type != 'ALL')) 
		{
			$cnt		.=	" AND 	DELIVERTO = '{$form_type}' ";
		}
	}
	$cnt		.=	"group by TRANSEQ ORDER BY DELIVERTO ASC ";
	$rscnt		=	$Filstar_conn->Execute($cnt);
	$cnt		=	$rscnt->RecordCount();
	$totalcount	=	ceil($cnt / $limit);
	
	$sel	=	" SELECT TRANSEQ,DELIVERTO ";
	$sel	.=	" FROM  ".DISPATCH_DB.".DISPATCH_ORDER WHERE STATUS = 'INPROCESS' ";
	if (!empty($form_tracking)) 
	{
	$sel		.=	" AND TRANSEQ = '{$form_tracking}' ";
	}
	if (!empty($form_type))
	{
		if (($form_type != 'ALL')) 
		{
			$sel		.=	" AND 	DELIVERTO = '{$form_type}' ";
		}
	}
	$sel		.=	"GROUP BY TRANSEQ ORDER BY DELIVERTO ASC LIMIT ".($page * $limit).",$limit ";
//echo $sel;
	$rssel	=	$Filstar_conn->Execute($sel);
	$aData	=	array();
	$aList	=	array();
	$show	=	"<table width='100%' border='0'>";
	$show	.=	"<tr class='Header_style'>";
	$show	.=		"<td width='20%'>";
	$show	.=			"TRACKING NO.";
	$show	.=		"</td>";
	$show	.=				"<td width='20%' align='center'>";
	$show	.=					"PREPARED BY";
	$show	.=				"</td>";
	$show	.=						"<td width='20%' align='center'>";
	$show	.=							"PREPARED DATE";
	$show	.=						"</td>";
	$show	.=								"<td width='20%' align='center'>";
	$show	.=									"DELIVERY TYPE";
	$show	.=								"</td>";
	$show	.=										"<td width='20%' align='center'>";
	$show	.=											"ACTION";
	$show	.=										"</td>";
	$show	.=	"</tr>";
	if ($cnt > 0) 
	{
		while (!$rssel->EOF) 
		{
			$tracking_no	=	$rssel->fields['TRANSEQ'];
			$deliver		=	$rssel->fields['DELIVERTO'];
			$aData[$deliver][]	=	$tracking_no;
			$rssel->MoveNext();
		}
		foreach ($aData as $key=>$val)
		{
			foreach ($val as $key2)
			{
				if ($key == 'MANILA') 
				{
					$tbl	= "DISPATCH_METROMANILA_HDR";
				}
				elseif ($key == 'PANDAYAN')
				{
					$tbl	= "DISPATCH_PANDAYAN_HDR";
				}
				elseif ($key == 'PROVINCE')
				{
					$tbl	= "DISPATCH_PROVINCE_HDR";
				}
				if ($_SESSION['username'] == 'raymond' || $_SESSION['username'] == '110357' || $_SESSION['username'] == '980286') 
				{
					$user			=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,$tbl,"PREPAREDBY","TRANSEQ = '{$key2}' ".(!empty($form_date) ? " and date(PREPAREDDATE) = '{$form_date}' " : "")."");
					$prepared		=	$global_func->Select_val($Filstar_conn,WMS_LOOKUP,"USER","NAME","USERNAME = '{$user}' ");
					$prepareddate	=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,$tbl,"PREPAREDDATE","TRANSEQ = '{$key2}' ".(!empty($form_date) ? " and date(PREPAREDDATE) = '{$form_date}' " : "")."");
				}
				else 
				{
					$user			=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,$tbl,"PREPAREDBY","TRANSEQ = '{$key2}' AND PREPAREDBY = '{$_SESSION['username']}' ".(!empty($form_date) ? " and date(PREPAREDDATE) = '{$form_date}' " : "")."");
					$prepared		=	$global_func->Select_val($Filstar_conn,WMS_LOOKUP,"USER","NAME","USERNAME = '{$user}' ");
					$prepareddate	=	$global_func->Select_val($Filstar_conn,DISPATCH_DB,$tbl,"PREPAREDDATE","TRANSEQ = '{$key2}' AND PREPAREDBY = '{$_SESSION['username']}' ".(!empty($form_date) ? " and date(PREPAREDDATE) = '{$form_date}' " : "")."");
				}
				
				$aList[$key][$key2]['prepared']		=	$prepared;
				$aList[$key][$key2]['prepareddate']	=	$prepareddate;
			}
		}
		if (count($aList) > 0 && !empty($user)) 
		{
			foreach ($aList as $deliver=>$tracking_no)
			{
				foreach ($tracking_no as $list_key=>$list_val)
				{
					$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
						$show	.=	"<td width='20%' align='center'>";
						$show	.=		$list_key;
						$show	.=	"</td>";
						
						$show	.=	"<td width='20%' align='center'>";
						$show	.=		$list_val['prepared'];
						$show	.=	"</td>";
						
						$show	.=	"<td width='20%' align='center'>";
						$show	.=		$list_val['prepareddate'];
						$show	.=	"</td>";
						
						$show	.=	"<td width='20%' align='center'>";
						$show	.=		$deliver;
						$show	.=	"</td>";
						
						$show	.=	"<td width='20%' align='center'>";
						$show	.=		"<input type='button' name='btnprint' id='btnprint' value='update' onclick=edit_this('$list_key','$deliver'); title='Edit this Schedule'; class='small_button'>";
						$show	.=		"<input type='button' name='btnclose' id='btnclose' value='delete' onclick=delete_this('$list_key','$deliver'); title='Delete this Schedule'; class='small_button'>";
						$show	.=	"</td>";
					$show	.=	"</tr>";
				}
			}
			$currentpg	=	$page	+	1;
			$show	.=	"<tr>";
				$show	.=	"<td align='center' colspan='5'>";
				$show	.=	"<input type='button' value='first' ".($page == 0 ? "disabled" : " onclick=\"Display_schedule(0);\" ").">";
				$show	.=	"<input type='button' value='prev'  ".($page == 0 ? "disabled" : "onclick=\"Display_schedule('".($page - 1)."');\" ").">";
				$show	.=	"<input type='text' name='txtpage' id='txtpage' value='$currentpg/$totalcount' size='7' style='text-align:center;' readonly>";
				//$show	.=	"<input type='text' name='txtpage' id='txtpage' value='$currentpg/$totalcount' size='7' style='text-align:center;' onfocus=\"remove_val(this.id);\" onblur=\"this_default(this.id,this.value);\" onkeyup=\"Navigate(this.value);\">";
				///$show	.=	"<input type='hidden' name='txtpage_default' id='txtpage_default' value='$currentpg/$totalcount' size='7' style='text-align:center;'>";
				$show	.=	"<input type='button' value='next' ".(($page + 1) == $totalcount ? "disabled" : " onclick=\"Display_schedule('".($page + 1)."');\" ").">";
				$show	.=	"<input type='button' value='last' ".(($page + 1) == $totalcount ? "disabled" : " onclick=\"Display_schedule('".($totalcount - 1)."');\" ").">";
				$show	.=	"</td>";
			$show	.=	"</tr>";
		}
		else 
		{
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
			$show	.=	"<td width='100%' colspan='5' align='center'>";
			$show	.=	"<blink>NOTHING TO DISPLAY</blink>";
			$show	.=	"</td>";
		$show	.=	"</tr>";
		}
	}
	else 
	{
		$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
			$show	.=	"<td width='100%' colspan='5' align='center'>";
			$show	.=	"<blink>NOTHING TO DISPLAY</blink>";
			$show	.=	"</td>";
		$show	.=	"</tr>";
	}
	$show	.=	"</table>";
	echo $show;
	exit();
}

if ($action == 'UPDATE') 
{
	$val_type		=	$_GET['VAL_TYPE'];
	$tracking_no	=	$_GET['VAL_TRANSEQ'];
	echo $global_func->Table_form_update($Filstar_conn,$val_type,$tracking_no);
	exit();
}

if ($action == 'SAVE_DISPATCH') 
{
	$error_cnt	=	0;
	$val_transeq=	$_GET['VAL_TRANSEQ'];
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
			$hdr_manila		 =	" UPDATE ".DISPATCH_DB.".DISPATCH_METROMANILA_HDR SET ";
			$hdr_manila		.=	" ROUTE = '{$route}',VANNO = '{$van}',PLATENO = '{$plate}',DRIVER = '{$driver}', ";
			$hdr_manila		.=	" HELPER = '{$helper}',FORWARDER = '{$forward}',SPECIAL_INSTRUCTION = '{$instruction}',DRIVER = '{$driver}', ";
			$hdr_manila		.=	" ISUPDATED = 'Y', UPDATEDBY = '{$_SESSION['username']}',UPDATEDATE = sysdate() ";
			$hdr_manila		.=	" WHERE TRANSEQ = '{$val_transeq}' ";
			$rshdr_manila	=	$Filstar_conn->Execute($hdr_manila);
			if ($rshdr_manila == false) 
			{
				throw new Exception(mysql_errno().":".mysql_error());
			}
			for ($x=1;$x<=($total_cnt - 1);$x++)
			{
				$CustNo			=	$_GET['hdn_cust_MANILA_'.$x];
				$InvoiceNo		=	$_GET['hdn_invoice_MANILA_'.$x];

				$ctn			=	$_GET['txt_MANILA_ctn_'.$x];
				$pkg			=	$_GET['txt_MANILA_pkg_'.$x];
				$remark			=	$_GET['txt_MANILA_remark_'.$x];
				
				$dtl_manila	 =	" UPDATE ".DISPATCH_DB.".DISPATCH_METROMANILA_DTL SET ";
				$dtl_manila	.=	" CARTON = '{$ctn}', PACKAGE = '{$pkg}',  REMARKS = '{$remark}' ";
				$dtl_manila	.=	" WHERE TRANSEQ = '{$val_transeq}' AND  CUSTCODE = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' ";
				$rsdtl_manila	=	$Filstar_conn->Execute($dtl_manila);
				if ($rsdtl_manila == false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
				if ($opt == 'SAVE')
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET STATUS = 'DELIVER' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' AND TRANSEQ = '{$val_transeq}'";
				}
				else 
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET STATUS = 'INPROCESS' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' AND TRANSEQ = '{$val_transeq}' ";
				}
				$rsupdate	=	$Filstar_conn->Execute($update);
				if ($rsupdate== false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
			}
			echo $val_transeq;
		}
		elseif ($save_type == 'PANDAYAN')
		{
			$hdr_pandayan		 =	" UPDATE ".DISPATCH_DB.".DISPATCH_PANDAYAN_HDR SET ";
			$hdr_pandayan		.=	" ROUTE = '{$route}',VANNO = '{$van}',PLATENO = '{$plate}',DRIVER = '{$driver}', ";
			$hdr_pandayan		.=	" HELPER = '{$helper}',SPECIAL_INSTRUCTION = '{$instruction}',DRIVER = '{$driver}', ";
			$hdr_pandayan		.=	" ISUPDATED = 'Y', UPDATEDBY = '{$_SESSION['username']}',UPDATEDATE = sysdate() ";
			$hdr_pandayan		.=	" WHERE TRANSEQ = '{$val_transeq}' ";
			$rshdr_pandayan		 =	$Filstar_conn->Execute($hdr_pandayan);
			if ($rshdr_pandayan == false) 
			{
				throw new Exception(mysql_errno().":".mysql_error());
			}
			for ($x=1;$x<=($total_cnt - 1);$x++)
			{
				$CustNo			=	$_GET['hdn_cust_PANDAYAN_'.$x];
				$InvoiceNo		=	$_GET['hdn_invoice_PANDAYAN_'.$x];
				$ctn			=	$_GET['txt_PANDAYAN_ctn_'.$x];
				$remark			=	$_GET['txt_PANDAYAN_remark_'.$x];
					
				$dtl_pandayan	 =	" UPDATE ".DISPATCH_DB.".DISPATCH_PANDAYAN_DTL SET ";
				$dtl_pandayan	.=	" CARTON = '{$ctn}',  REMARKS = '{$remark}' ";
				$dtl_pandayan	.=	" WHERE TRANSEQ = '{$val_transeq}' AND  CUSTCODE = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' ";
				$rsdtl_pandayan	 =	$Filstar_conn->Execute($dtl_pandayan);
				if ($rsdtl_pandayan == false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
				if ($opt == 'SAVE')
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET STATUS = 'DELIVER' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' AND TRANSEQ = '{$val_transeq}'";
				}
				else 
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET STATUS = 'INPROCESS' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' AND TRANSEQ = '{$val_transeq}' ";
				}
				$rsupdate	=	$Filstar_conn->Execute($update);
				if ($rsupdate == false) 
				{
					echo mysql_errno().":".mysql_error();
				}
			}
			echo $val_transeq;
		}
		elseif ($save_type	==	'PROVINCE')
		{
			$forward		=	$_GET['txtforwarded_'.$save_type];
			$hdr_province	 =	" UPDATE ".DISPATCH_DB.".DISPATCH_PROVINCE_HDR SET ";
			$hdr_province	.=	" ROUTE = '{$route}',VANNO = '{$van}',PLATENO = '{$plate}',DRIVER = '{$driver}', ";
			$hdr_province	.=	" HELPER = '{$helper}',SPECIAL_INSTRUCTION = '{$instruction}',DRIVER = '{$driver}', ";
			$hdr_province	.=	" ISUPDATED = 'Y', UPDATEDBY = '{$_SESSION['username']}',UPDATEDATE = sysdate() ";
			$hdr_province	.=	" WHERE TRANSEQ = '{$val_transeq}' ";
			$rshdr_province	=	$Filstar_conn->Execute($hdr_province);
			if ($rshdr_province	== false) 
			{
				throw new Exception(mysql_errno().":".mysql_error());
			}
			for ($x=1;$x<=($total_cnt - 1);$x++)
			{
				$CustNo			=	$_GET['hdn_cust_PROVINCE_'.$x];
				$InvoiceNo		=	$_GET['hdn_invoice_PROVINCE_'.$x];
				$size			=	$_GET['txt_PROVINCE_size_'.$x];
				$ctn			=	$_GET['txt_PROVINCE_ctn_'.$x];
				$dr				=	$_GET['txt_PROVINCE_dr_'.$x];
				$bill			=	$_GET['txt_PROVINCE_bill_'.$x];
				$kilo			=	$_GET['txt_PROVINCE_kilo_'.$x];
				
				$dtl_province	 =	" UPDATE ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL SET ";
				$dtl_province	.=	" `SIZE_OF_CTN` = '{$size}' ,`QTY_CTN` = '{$ctn}',`DR_NO`='{$dr}',`WAYBILL_NUMBER`='{$bill}',`WEIGHT_BY_KILO`='{$kilo}' ";
				$dtl_province	.=	" WHERE TRANSEQ = '{$val_transeq}' AND  CUSTCODE = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' ";
				$rsdtl_province	=	$Filstar_conn->Execute($dtl_province);
				if ($rsdtl_province== false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
				if ($opt == 'SAVE')
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET STATUS = 'DELIVER' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' AND TRANSEQ = '{$val_transeq}'";
				}
				else 
				{
					$update		=	"UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET STATUS = 'INPROCESS' WHERE CUSTNO = '{$CustNo}' AND INVOICENO = '{$InvoiceNo}' AND TRANSEQ = '{$val_transeq}' ";
				}
				$rsupdate	=	$Filstar_conn->Execute($update);
				if ($rsupdate== false) 
				{
					throw new Exception(mysql_errno().":".mysql_error());
				}
			}
			echo $val_transeq;
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

if ($action == 'DELETE') 
{
	$tracking_no	=	$_GET['VAL_TRANSEQ'];
	$type			=	$_GET['VAL_TYPE'];
	try {
		$Filstar_conn->StartTrans();
		$update		="UPDATE ".DISPATCH_DB.".DISPATCH_ORDER SET STATUS = 'DELETED', DELETEBY = '{$_SESSION['username']}',DELETEDATE = sysdate() WHERE TRANSEQ = '{$tracking_no}' ";
		$rsupdate	=$Filstar_conn->Execute();
		if ($rsupdate == false) 
		{
			throw new Exception(mysql_errno().":".mysql_error());
		}
		echo "1";
		$Filstar_conn->CompleteTrans();
	}
	catch (Exception $e)
	{
		$e->__toString();
		$Filstar_conn->CompleteTrans();
	}
	exit();
}
?>
<html>
<title>SCHEDULER - MAIN</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../css/style.css);</style>
<style type="text/css">@import url(../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../calendar/calendar-setup.js"></script>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<script>
		function	Display_schedule(val_page)
		{
			var datamain	=	$('#datamain').serialize();
			$.ajax({
					url			:	'scheduler_main.php?action=DISPLAY_ALL&page='+val_page+'&'+datamain,
					beforeSend	:	function()
								{
									$('#divloader').show();
								},
					success		:	function(response)
								{
									$('#divloader').hide();
									$('#divlist').html(response);
									$('#divlist').show();
								}
			});
		}
		
		function	edit_this(val_transeq,val_type)
		{
			var isSubmit	=	confirm('Are you sure you want to update this schedule?');
			if(isSubmit == true)
			{
				$('#div_body').hide();
				$.ajax({
						url		:	'scheduler_main.php?action=UPDATE&VAL_TRANSEQ='+val_transeq+'&VAL_TYPE='+val_type,
						success	:	function(response)
								{
									$('#divupdate').show();
									$('#divresponse').html(response);
									$('#divresponse').show();
								}
				});
			}	
		}
		
		function	delete_this(val_transeq,val_type)
		{
			var isSubmit	=	confirm('Are you sure you want to delete this schedule?');
			if(isSubmit == true)
			{
				$('#div_body').hide();
				$.ajax({
						url		:	'scheduler_main.php?action=DELETE&VAL_TRANSEQ='+val_transeq+'&VAL_TYPE='+val_type,
						success	:	function(response)
								{
									if(response == 1)
									{
										alert('Schedule was successfully deleted.');
										Display_schedule('0');
									}
									else
									{
										$('#divupdate').show();
										$('#divresponse').html(response);
										$('#divresponse').show();
									}
								}
				});
			}
		}
		
		
		function	SaveDispatch(val_transeq,val_type,val_cnt)
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
													url			:	'scheduler_main.php?action=SAVE_DISPATCH&OPT=SAVE&SAVE_TYPE='+val_type+'&TOTAL_CNT='+val_cnt+'&'+formdata+'&VAL_TRANSEQ='+val_transeq,
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
																		alert('Transaction was successfully updated');
																		$('#divloader_response').hide();
																		$('#divresponse').html('');
																		$('#divupdate').hide();
																		$('#div_body').show();
																		Display_schedule('0');
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
		
		
		function	SaveDispatch_temp(val_transeq,val_type,val_cnt)
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
								url			:	'scheduler_main.php?action=SAVE_DISPATCH&OPT=TEMP&SAVE_TYPE='+val_type+'&TOTAL_CNT='+val_cnt+'&'+formdata+'&VAL_TRANSEQ='+val_transeq,
								beforeSend	:	function()
											{
												$('#divloader_response').show();
											},
								success	:	function(response)
											{
												if(response == 2)
												{
													$('#divloader_response').hide();
													$('#divdebug').html(response);
												 	$('#divdebug').show();
												}
												else
												{
													
													alert('Transaction was successfully updated');
													$('#divloader_response').hide();
													$('#divresponse').html('');
													$('#divupdate').hide();
													$('#div_body').show();
													Display_schedule('0');
													
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
		
		
		function	Navigate(va_val)
		{
			if(va_val == 0)
			{
				alert('Invalid page.');
			}
		}
		
		function	remove_val(val_id)
		{
			$('#'+val_id).val('');
		}
		
		function	this_default(val_id,val_val)
		{
			var this_page	=	$('#txtpage_default').val();
			$('#'+val_id).val(this_page);
		}
		
		function	Create_schedule()
		{
			var isSubmit	=	confirm('Create new Dispatch Schedule?');
			if(isSubmit == true)
			{
				location='index.php?targer=SCHEDULER'
			}
		}
</script>
	<body onload="Display_schedule(0);">
		<div id="div_body">
		<form name='datamain' id="datamain">
			<table width="100%" border="0" class="Text_header">
				<tr>
					<td width="20%">
						&nbsp;
					</td>
						<td width="20%" align="left">
							Tracking No.
						</td>
							<td width="40%" align="left">
								:&nbsp;<input type="text" name="txttracking" id="txttracking" value="" size="25">
							</td>
								<td width="20%" align="center">
									&nbsp;
								</td>
				</tr>
				<tr>
					<td width="20%">
						&nbsp;
					</td>
						<td width="20%" align="left">
							Prepared date
						</td>
							<td width="40%" align="left">
								:&nbsp;<input type="text" name="txtdate" id="txtdate" value="" size="25" readonly>
								<img src="../../calendar/calendar.gif"  name="img_date" id="img_date">
							</td>
								<td width="20%" align="center">
									&nbsp;
								</td>
				</tr>
				<tr>
					<td width="20%">
						&nbsp;
					</td>
						<td width="20%" align="left">
							Delivery type
						</td>
							<td width="40%" align="left">
								:&nbsp;<select name="seltype" id="seltype">
											<option value="ALL">--ALL--</option>
											<option value="MANILA">MANILA</option>
											<option value="PANDAYAN">PANDAYAN</option>
											<option value="PROVINCE">PROVINCE</option>
									  </select>
							</td>
								<td width="20%" align="center">
									&nbsp;
								</td>
				</tr>
				<tr>
					<td width="20%">
						&nbsp;
					</td>
						<td width="60%" colspan="2" align="center">
							<input type="button" name="btndisplay" id="btndisplay" value="Display" class="small_button" onclick="Display_schedule(0);">
							<input type="button" name="btnCreate" id="btnCreate" value="Create" class="small_button" onclick="Create_schedule();">
						</td>
							<td width="20%">
								&nbsp;
							</td>
				</tr>
				<tr>
					<td width="100%" colspan="5" align="center">
						<div id="divloader" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
					</td>
				</tr>
				<tr>
					<td colspan="5">
						<div id="divlist"></div>
					</td>
				</tr>
			</table>
		</form>
		</div>
		<div id="divupdate" style="display:none;">
		<form name='dataupdate' id="dataupdate">
			<table width="100%">
				<tr>
					<td width="100%">
						<div id="divresponse"></div>
						<div id="divdebug"></div>
					</td>
				</tr>
				<tr>
					<td width="100%" align="center">
						<div id="divloader_response" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
					</td>
				</tr>
			</table>
		</form>
		</div>
	</body>
</html>
<script>
	Calendar.setup
	(
	   {
	     inputField  : "txtdate",    // ID of the input field
	     ifFormat    : "%Y-%m-%d", 	 // The Date Format
	     button		 : "img_date"	 // images
	   }
	);
</script>
