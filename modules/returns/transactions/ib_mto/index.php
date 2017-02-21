<?php
/**
* Module Name	:	INTERNAL BARCODE MTO
* Date Created	:	
* @author Jay-R A. Magdaluyo <ijayraven@gmail.com>
*/
session_start();
?>
<html>
	<head>
		<title>INTERNAL BARCODE MTO</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<?php include($_SERVER['DOCUMENT_ROOT']."/public_js/jsUI.php");?>
		<script type="text/javascript" src="ib_mto.js"></script>
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
			                <td class="td-title">INTERNAL BARCODE MTO</td>
			                <td class="upp-right"></td>
			            </tr>
			            <tr>
			                <td class="mid-left"></td>
			                <td class="td-content">
								<?php
									include("ib_mto.php");
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