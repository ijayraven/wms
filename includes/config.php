<?php
	################## Warehouse Management System Configuration #########################
	//CONSTANT VARIABLES
	session_cache_limiter('nocache');
	########################
	define("DEBUGGING"			,	true);
	
//	define("WMSSERVER"			,	"192.168.255.10");//WMS database
	define("WMSSERVER"			,	"localhost");//WMS database
	define("WMSUSER"			,	"root");//WMS database user
	define("WMSPASS"			,	"");//password WMS server
	
	define("SYSTEM_NAME"		,	"wms");
//	define("WMSSERVER_2"		,	"192.168.255.10");//WMS database
	define("WMSSERVER_2"		,	"localhost");//WMS database
	define("WMSUSER_2"			,	"root");//WMS	 database user
	define("WMSPASS_2"			,	"");//password WMS server
	
	define("HOME"				,	$_SERVER['DOCUMENT_ROOT']."/".SYSTEM_NAME."/");//folder of branch utility system
	define('FDC_HEADER'			,	'FILSTAR DISTRIBUTORS CORPORATION');
	define('FDC_ADDRESS'		,	'# 11 Brixton Street, Brgy. Kapitolyo, Pasig City 1603');
	define('FDC_TEL'			,	'Tel. Nos.: +63 2 636.5051 to 60, Web: www.filstar.com.ph');
	define('DISPATCH_HEADER'	,	'DISPATCH SCHEDULE');
	
	define("FDCRMS"				,	'FDCRMSlive');
	define('DISPATCH_DB'		,	'DISPATCH');
	define('WMS_LOOKUP'			,	'WMS_LOOKUP');
	
	
	define('INVOICE_RATE'		,	.2);
	define('STF_RATE'			,	.1);
	
	
	#************ ERROR HANDLING CONFIGURATIONS ************

	define("CUR_DATE"			,	date("Y-m"));
	define('ERRLOG_PATH'		,	HOME."ADMIN_ERROR_LOGS/".CUR_DATE."/");
	define('ERRFILENAME'		,	"error.log");
	
	#************ QUERY LOGS CONFIGURATIONS ************

	define('QUERY_PATH'			,	HOME."ADMIN_QUERY_LOGS/".CUR_DATE."/");
	define('QFILENAME'			,	"query.log");
	
	define('PAGE_LIMIT'			,	'10');
		
?>