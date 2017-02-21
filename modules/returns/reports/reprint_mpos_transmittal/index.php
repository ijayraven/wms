<?php
/**
* Module Name	:	REPRINT MPOS TRANSMITTAL
* Date Created	:	
* @author Jay-R A. Magdaluyo <ijayraven@gmail.com>
*/
session_start();
?>
<html>
	<head>
		<title>REPRINT MPOS TRANSMITTAL</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script src="/wms/script/disabled.js" language="JavaScript" type="text/javascript"></script>
		<script src="/public_js/jquery-3.0.0.min.js"></script>
		<?php include($_SERVER['DOCUMENT_ROOT']."/public_js/jsUI.php");?>
		<link rel="stylesheet"	href="/wms/css/j_styles.css">
		<link rel="stylesheet"	href="/wms/css/style.css">
	</head>
	<body>
		<table class="main-tbl">
			  <tr>
			    <td align="center">
			        <table class="main-tbl-2" cellspacing="0" cellpadding="0">
			            <tr>
			                <td class="top-left"></td>
			                <td class="top-top"></td>
			                <td class="top-right"></td>
			            </tr>
			            <tr>
			                <td class="upp-left"></td>
			                <td class="td-title">REPRINT MPOS TRANSMITTAL</td>
			                <td class="upp-right"></td>
			            </tr>
			            <tr>
			                <td class="mid-left"></td>
			                <td class="td-content">
								<?php
									include($_SERVER['DOCUMENT_ROOT'].'/wms/modules/returns/reports/reprint_mpos_transmittal/reprint_mpos_transmittal.php');
								?>
							</td>
			                <td class="mid-right"></td>
			            </tr>
			            <tr>
			                <td class="btm-left"></td>
			                <td class="btm-center"></td>
			                <td class="btm-right"></td>
			            </tr>
			        </table>
			    </td>
			  </tr>
		</table>
	</body>
</html>