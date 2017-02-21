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
?>
<html>
<title>SCAN REPORT</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../css/style.css);</style>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<script>
</script>
</head>
<body >
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST" action="create_report.php">
		<table width="100%" border="0">
			<tr class="Header_style">
				<td width="20%">
					Upload file
				</td>
					<td width="50%" align="left">
						<input type="file" name="uploadfile" id="uploadfile" value="">
					</td>
						<td width="30%" align="left">
							<input type="submit" name="btnsubmit" id="btnsubmit" value="Submit" class="small_button">
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
	</form>
</body>
</html>