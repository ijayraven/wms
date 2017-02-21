<?php
/********************************************************************************************************************
* FILE NAME :	index_main.php																						*
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
* 2013/09/09	Raymond A. Galaroza																					*
* 																													*
* ALGORITHM(pseudocode)																								*
* 																													*
*********************************************************************************************************************/
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}

?>
<html>
<title>SEASON REPORT</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../../css/style.css);</style>
<style type="text/css">@import url(../../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../../calendar/calendar-setup.js"></script>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<script>
	function	CreateReport()
	{
		var issummary	=	$('#reporttype_summary').is(":checked");
		var isdetailed	=	$('#reporttype_detailed').is(":checked");
		var isall		=	$('#reporttype2_all').is(":checked");
		var isnbs		=	$('#reporttype2_nbs').is(":checked");
		var istrade		=	$('#reporttype2_trade').is(":checked");
		var islist		=	$('#reporttype3_list').is(":checked");
		var selsearch	=	$('#SelBatch').val();
		var dfrom		=	$('#dfrom').val();
		var dto			=	$('#dto').val();
		
		if(isall==true)
		{
			var	opt			=	'ALL';
		}
		else if(isnbs==true)
		{
			var opt			=	'NBS';
		}
		else if(istrade==true)
		{
			var opt			=	'TRADE';
		}
		
		if(dfrom != '' || dto != '')
		{
			if(dfrom > dto)
			{
				alert('Invalid date range!');
				return;
			}
			
			if(dfrom == '' || dto == '')
			{
				alert('Invalid date range!');
				return;
			}
		}
		
		if(islist==true)
		{
			var url_		= 'reports_christmas_list.php?action=GENERATE&OPT='+selsearch+'&OPT2='+opt+'&DFROM='+dfrom+'&DTO='+dto;
		}
		else if(issummary==true)
		{
			var url_		= 'reports_christmas_summary.php?action=GENERATE&OPT='+selsearch+'&OPT2='+opt+'&DFROM='+dfrom+'&DTO='+dto;
			
		}
		else if(isdetailed==true)
		{
			var url_		= 'reports_christmas.php?action=GENERATE&OPT='+selsearch+'&OPT2='+opt+'&DFROM='+dfrom+'&DTO='+dtos;
		}
		window.open(url_);
	}
	
	function disabled__()
	{
		$('#reporttype_summary').attr('disabled', true);
		$('#reporttype_detailed').attr('disabled', true);
	}
</script>
</head>
<body >
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0" class="Text_header">
			<tr>
				<td width="35%" align="right">
					SUMMARY
				</td>
				<td width="5%" align="center">
					<input type="radio" name="reporttype" id="reporttype_summary" value="SUMMARY" checked >
				</td>
				<td width="10%" align="center">
					DETAILED
				</td>
				<td width="40%" align="left" colspan="3">
					<input type="radio" name="reporttype" id="reporttype_detailed" value="DETAILED">
				</td>
			</tr>
			<tr>
				<td width="35%" align="right">
					ALL
				</td>
				<td width="5%" align="center">
					<input type="radio" name="reporttype2" id="reporttype2_all" value="ALL" checked>
				</td>
				<td width="10%" align="center">
					NBS
				</td>
				<td width="5%" align="left">
					<input type="radio" name="reporttype2" id="reporttype2_nbs" value="NBS">
				</td>
				<td width="5%" align="center">
					TRADE
				</td>
				<td width="30%">
					<input type="radio" name="reporttype2" id="reporttype2_trade" value="TRADE">
				</td>
			</tr>
			<tr>
				<td width="30%" align="right">
					SOF LISTING
				</td>
				<td width="5%" align="center">
					<input type="radio" name="reporttype3" id="reporttype3_list" value="LIST" onclick="disabled__();">
				</td>	
				<td width="65%" align="left" colspan="4">
					&nbsp;
				</td>	
			</tr>
			<tr>
				<td width="30%" align="right">
					SOF DATE RANGE
				</td>
				<td width="70%" align="left" colspan="5">
					<input type="text" name="dfrom" id="dfrom" value="" placeholder="FROM" maxlength="10" align="center" class="dates">
					<input type="text" name="dto" id="dto" value="" placeholder="TO"   maxlength="10" align="center" class="dates">
				</td>	
			</tr>
			<tr>
				<td colspan="6" width="100%" align="center">
					<select id="SelBatch">
							<option value="1">WAVE 1</option>
							<option value="2">WAVE 2</option>
							<!--<option value="2">WAVE 2</option>
							<option value="3">WAVE 3</option>-->
						</select>
				</td>	
			</tr>
			<tr>
				<td colspan="6" align="center">
					<input type="button" name="btnreport" id="btnreport" value="Submit" class="small_button" onclick="CreateReport();">
				</td>
			</tr>
		</table>
	</form>
</body>
</html>
<script>
$(".dates").datepicker({ 
	dateFormat: 'yy-mm-dd',
	changeMonth: true,
    changeYear: true 
});

</script>