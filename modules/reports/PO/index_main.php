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
<title>SCAN REPORT</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../../css/style.css);</style>
<style type="text/css">@import url(../../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../../calendar/calendar-setup.js"></script>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<script>
	function	CreatePOReport()
	{
		var selsearch	=	$('#selsearch').val();
		var txtPO		=	$('#txtPO').val();
		var dfrom		=	$('#txtfrom').val();
		var dto			=	$('#txtto').val();
		var	rstatus		=	'';
		if(selsearch == 'PO')
		{
			if($('#rdupload1').is(':checked') == true)
			{
				rstatus	=	'UPLOADED';
			}
			else if($('#rdupload2').is(':checked') == true)
			{
				rstatus	=	'NOTUPLOADED';
			}
			else
			{
				rstatus	=	'MULTIPLE';
			}
		}	
		if(dfrom != '' && dto != '')
		{
			if(rstatus == 'UPLOADED' || rstatus == '')
			{
				var url_	= 'reports_uploaded.php?action=PO&DFROM='+dfrom+'&DTO='+dto+'&TXTPO='+txtPO+'&SELSEARCH='+selsearch+'&RSTATUS='+rstatus;
			}
			else if(rstatus == 'NOTUPLOADED')
			{
				var url_	= 'reports_notuploaded.php?action=PO&DFROM='+dfrom+'&DTO='+dto+'&TXTPO='+txtPO;
			}
			else
			{
				var url_	= 'reports_multiple.php?action=PO&DFROM='+dfrom+'&DTO='+dto+'&TXTPO='+txtPO;
			}
			window.open(url_);
		}
		else
		{
			alert('Invalid Date');
		}
	}
	
	function	Selecttype(val_id)
	{
		if(val_id == 'PO')
		{
			$('#rdupload1').attr('disabled', false);
			$('#rdupload1').attr('checked', true);
			$('#rdupload2').attr('disabled', false);
			$('#rdupload3').attr('disabled', false);
		}
		else
		{
			$('#rdupload1').attr('disabled', true);
			$('#rdupload1').attr('checked', false);
			$('#rdupload2').attr('disabled', true);
			$('#rdupload3').attr('disabled', true);
		}
	}
</script>
</head>
<body >
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0" class="Text_header">
			<tr>
				<td width="10%">
					&nbsp;
				</td>
					<td width="10%">
						<select name="selsearch" id="selsearch" onchange="Selecttype(this.value);" onkeyup="Selecttype(this.value);">
							<option value="PO">P.O</option>
							<option value="SOF">SOF</option>
						</select>
					</td>
						<td width="75%" colspan="4">
							<input type="text" name="txtPO" id="txtPO" value="">
						</td>
			</tr>
			<tr>
				<td width="10%">
					&nbsp;
				</td>
					<td width="10%">
						STATUS
					</td>
						<td width="75%" colspan="4">
							<input type="radio" name="rdstatus" id="rdupload1" value="UPLOAD" checked>UPLOADED
							&nbsp;&nbsp;
							<input type="radio" name="rdstatus" id="rdupload2" value="NOTUPLOADED">NOT UPLOADED
							&nbsp;&nbsp;
							<input type="radio" name="rdstatus" id="rdupload3" value="MULTIPLE">w/ MULTIPLE SOF
						</td>
			</tr>
			<tr>
				<td width="20%">
					&nbsp;
				</td>
					<td width="10%">
						FROM
					</td>
						<td width="20%">
							<input type="text" name="txtfrom" id="txtfrom" value="" size="15" readonly>
							<img src="../../../calendar/calendar.gif"  name="img_date" id="img_date_from">
						</td>
							<td width="5%">
								TO
							</td>
								<td width="20%">
									<input type="text" name="txtto" id="txtto" value="" size="15" readonly>
									<img src="../../../calendar/calendar.gif"  name="img_date" id="img_date_to">
								</td>
									<td width="25%">
										&nbsp;
									</td>
			</tr>
			<tr>
				<td colspan="6" align="center">
					<input type="button" name="btnreport" id="btnreport" value="Submit" class="small_button" onclick="CreatePOReport();">
				</td>
			</tr>
		</table>
	</form>
</body>
</html>
<script>
	Calendar.setup
	(
	   {
	     inputField  : "txtfrom",    // ID of the input field
	     ifFormat    : "%Y-%m-%d", 	 // The Date Format
	     button		 : "img_date_from"	 // images
	   }
	);
	
	Calendar.setup
	(
	   {
	     inputField  : "txtto",    // ID of the input field
	     ifFormat    : "%Y-%m-%d", 	 // The Date Format
	     button		 : "img_date_to"	 // images
	   }
	);
</script>