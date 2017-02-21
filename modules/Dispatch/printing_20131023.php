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
		$View	.=											"<td width='20%' align='center'>";
		$View	.=												"PLATENO";
		$View	.=											"</td>";
		$View	.=													"<td width='15%' align='center'>";
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
					$View	.=											"<td width='20%' align='center'>";
					$View	.=												$rssel_type->fields['PLATENO'];
					$View	.=											"</td>";
					$View	.=													"<td width='15%' align='center'>";
					$View	.=														"<input type='button' name='btnprint' id='btnprint' value='print' onclick=print_this('$type','$id','$transeq'); title='Print this Schedule'; class='small_button'>";
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
		
		function	print_this(val_type,val_id)
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
				window.open('province_pdf.php?schedule_id='+val_id);
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
</script>
</head>
<body >
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
		<table width="100%" border="0">
			<tr>
				<td width="100%" align="center">
					<div id="divloader" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
				</td>
			</tr>	
		</table>
		<div id="divdebug"></div>
	</form>
</body>
</html>