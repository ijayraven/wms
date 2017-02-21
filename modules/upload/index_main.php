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
* 2013/09/07	Raymond A. Galaroza																					*
* 																													*
* ALGORITHM(pseudocode)																								*
* 																													*
*********************************************************************************************************************/
session_start();
if (!empty($_GET['msg'])) 
{
	$color="Red";
}
?>
<html>
	<title>UPLOAD</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<style type="text/css">@import url(../../css/style.css);</style>
	<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
	<style type="text/css">
	.overlay 
	{
		background-color: #ffffff;
	  	position: fixed;
	  	top: 0; right: 0; bottom: 0; left: 0;
	  	opacity: 0.5; /* also -moz-opacity, etc. */
	  	z-index: 50;
	}
	.overlay_text
	{
		font-family: sans-serif;
		font-size: 15pt;
		font-weight: bold;
		color: #000000;
	}
	</style>
	<script>
		function	Show_overlay()
		{
			$('#divoverlay').show();
		}
		
		function	BTNCLEAR()
		{
			//$('#dataform').submit();
			location='index.php';
		}
	</script>
	<body>
	<form name="dataform" id="dataform" enctype="multipart/form-data"  method="POST" action="upload_charge.php">
		<table border="0" width="100%" >
			<tr class="Header_style">
				<td width="20%" align="center">
					<input type="button" name="btnclear" id="btnclear" value="Clear"  class="small_button" onclick="BTNCLEAR();">
				</td>
					<td width="20%" align="left">
						UPLOAD CSV
					</td>
						<td width="30%" align="left">
							<input type="file" name="uploadfile" id="uploadfile" value="" size="50" >
						</td>
							<td width="30%">
								<!--<input type="submit" name="btnsubmit" id="btnsubmit" value="Submit" class="small_button" onclick="Show_overlay();">-->
								<input type="submit" name="btnsubmit" id="btnsubmit" value="Submit" class="small_button">
								<!--<input type="button" name="btnsubmit" id="btnsubmit" value="Submit" class="small_button" onclick="Show_overlay();">-->
							</td>
			</tr>
		</table>
		<!--<div id="divoverlay" class="overlay" style="display:none">-->
		<table border="0" width="100%" border="0">
			<tr>
				<td width="100%" align="center" style="color:Red;">
					<?php echo $_GET['msg'];?>
				</td>
				<td width="100%" align="center">
					<div id="divloader" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
					<input type="hidden" name="hdntotal" id="hdntotal" value="">
				</td>
			</tr>	
		</table>
	</form>
	</body>
</html>