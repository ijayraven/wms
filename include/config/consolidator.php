<?php
	session_start();
	include('config.php');			//system configuration
	include('adodb/adodb.inc.php');	//adodb libraries
	//include('connection.php');		//system database(s) connection
	include('function.inc.php');		//global function
	
	$Global_funcs	=	new __Global_functions();
?>
