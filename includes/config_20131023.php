<?php
################## Warehouse Management System Configuration #########################
	//CONSTANT VARIABLES
	session_cache_limiter('nocache');
	########################
	define("DEBUGGING"				,	true);
	
	define("WMSSERVER",				"192.168.255.14");//WMS database
	define("WMSUSER",				"root");//WMS database user
	define("WMSPASS",				"");//password WMS server
	
	/*define("WMSSERVER_2",			"192.168.204.40");//WMS database
	define("WMSUSER_2",				"root");//WMS	 database user
	define("WMSPASS_2",				"");//password WMS server*/
	
	define('FDC_HEADER'				,	'FILSTAR DISTRIBUTORS CORPORATION');
	define('DISPATCH_HEADER'		,	'DISPATCH SCHEDULE');
	
	define("FDCRMS"					,	'FDCRMSlive');
	define('DISPATCH_DB'			,	'DISPATCH');
	define('WMS_LOOKUP'				,	'WMS_LOOKUP');
?>