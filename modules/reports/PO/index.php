<?php
session_start();
/********************************************************************************************************************
* FILE NAME :	index.php																							*
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
?>
<html>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!--Designed by Noli R. Gones//-->
<style>
body {	
	margin:0;
	background-color: #82bafc;
}
</style>
<script src="script/disabled.js" language="JavaScript" type="text/javascript"></script>
</head>
<body>
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
        <table width="1100" height="300" border="0" cellpadding="0" cellspacing="0" align="center"><!--optional width and height resize or none-->
            <tr>
                <td width="7" height="7" style="background-image:url(../../../images/fdc_01.gif); background-repeat:no-repeat;"></td>
                <td height="7" style="background-image:url(../../../images/fdc_02.gif); background-repeat:repeat-x;"></td>
                <td width="7" height="7" style="background-image:url(../../../images/fdc_03.gif); background-repeat:no-repeat;"></td>
            </tr>
            <tr>
                <td width="7" height="20" style="background-image:url(../../../images/fdc_04.gif); background-repeat:repeat-y;"></td>
                <td height="20" style="background-color:#3b64a0; font-family:Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold; color:#00ffff; text-align:center;">REPORT</td>
                <td width="7" height="20" style="background-image:url(../../../images/fdc_06.gif); background-repeat:repeat-y;"> </td>
            </tr>
            <tr>
                <td width="7" style="background-image:url(../../../images/fdc_07.gif); background-repeat:repeat-y;"></td>
                <td style="background-color:#e4e9f2; font-family:Arial, Helvetica, sans-serif; font-size:12px; font-weight:normal; text-align:center;">
					<!--start of contents//-->
					<?php
						include($_SERVER['DOCUMENT_ROOT'].'/wms/modules/reports/PO/index_main.php');
					?>
					<!--end of contents//-->                
                </td>
                <td width="7" style="background-image:url(../../../images/fdc_09.gif); background-repeat:repeat-y;"></td>
            </tr>
            <tr>
                <td width="7" height="7" style="background-image:url(../../images/fdc_10.gif); background-repeat:no-repeat;"></td>
                <td height="7" style="background-image:url(../../../images/fdc_11.gif); background-repeat:repeat-x;"></td>
                <td width="7" height="7" style="background-image:url(../../../images/fdc_12.gif); background-repeat:no-repeat;"></td>
            </tr>
        </table>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
</body>
</html>