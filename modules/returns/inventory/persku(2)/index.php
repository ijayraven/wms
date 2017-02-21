<?php
session_start();
?>
<html>
<title>INVENTORY</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!--Designed by Noli R. Gones//-->
<style>
body {	
	margin:0;
	background-color: #82bafc;
}
.dates,.time
{
	text-align:center;
}
label,.small_button
{
	cursor:pointer;
}
.label_text
{
	font-weight:bold;
	font-size:12px;
}
input[type="text"],input[type="password"]
{
	text-align:center;
}
.text_white10 {
    font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #ffffff;
}
.trdtls:hover
{
	background-color:#99ffff;
	color:#000000;
}
.trdtls
{
	color:#132639;
}
.no-close .ui-dialog-titlebar-close
{
    display:none;
}
.no-close .ui-dialog-titlebar-close
{
    display:none;
}
.trfound
{
	background-color:#82bafc;
}
.err-background, .primeitem
{
	background-color:#ff9999;
}
.errnotfound
{
	color:red;
}
.tdtrxdtls
{
	cursor:pointer;
}
.activetr
{
	background-color:#2952a3;
	font-weight:bold;
	border: 1px solid black;
	color:#adc2eb;
}
.trdtls img
{
	background-color:#9fbfdf;
	border-radius:5px;
	box-shadow: 2px 2px 2px #888888;
}
#tbltrxnonmtoitems, #tbltrxnonmtoitems td
{
	border-collapse:collapse;
	border:1px solid #ffffff;
}
.padding5px
{
	padding:5px;
	text-align:center;
}
.colored
{
	color:#223a5d;
	cursor:pointer;
}
.coloredfff
{
	color:#fff;
}
.trscanning, .trmto, .trraw, .trpiecework, .trfillingbin
{
	background-color:dae3f1;
}
a
{
	text-decoration:none;
}
.moded
{
	font-size:14px;
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
                <td width="7" height="7" style="background-image:url(/wms/images/fdc_01.gif); background-repeat:no-repeat;"></td>
                <td height="7" style="background-image:url(/wms/images/fdc_02.gif); background-repeat:repeat-x;"></td>
                <td width="7" height="7" style="background-image:url(/wms/images/fdc_03.gif); background-repeat:no-repeat;"></td>
            </tr>
            <tr>
                <td width="7" height="20" style="background-image:url(/wms/images/fdc_04.gif); background-repeat:repeat-y;"></td>
                <td height="20" style="background-color:#3b64a0; font-family:Arial, Helvetica, sans-serif; font-size:12px; font-weight:bold; color:#00ffff; text-align:center;">INVENTORY</td>
                <td width="7" height="20" style="background-image:url(/wms/images/fdc_06.gif); background-repeat:repeat-y;"> </td>
            </tr>
            <tr>
                <td width="7" style="background-image:url(/wms/images/fdc_07.gif); background-repeat:repeat-y;"></td>
                <td style="background-color:#e4e9f2; font-family:Arial, Helvetica, sans-serif; font-size:12px; font-weight:normal; text-align:center;">
					<!--start of contents//-->
					<?php
						include($_SERVER['DOCUMENT_ROOT'].'/wms/modules/returns/inventory/persku/inventory_sku.php');
					?>
					<!--end of contents//-->                
                </td>
                <td width="7" style="background-image:url(/wms/images/fdc_09.gif); background-repeat:repeat-y;"></td>
            </tr>
            <tr>
                <td width="7" height="7" style="background-image:url(/wms/images/fdc_10.gif); background-repeat:no-repeat;"></td>
                <td height="7" style="background-image:url(/wms/images/fdc_11.gif); background-repeat:repeat-x;"></td>
                <td width="7" height="7" style="background-image:url(/wms/images/fdc_12.gif); background-repeat:no-repeat;"></td>
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