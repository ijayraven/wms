<?php
/************************************************************************************************************************************
* FILE NAME :		warehouse.inc.php																								*	
* PURPOSE 	:		compilation of include files																					*	
* FILE REFERENCES :																													*				
* NAME I/O DESCRIPTION 																												*		
* ---------------------																												*		
* EXTERNAL VARIABLES :																												*	
* Source :																															*	
* NAME I/O DESCRIPTION 																												*
* ---------------------																												*		
* EXTERNAL REFERENCE :																												*
* NAME DESCRIPTION																													*
* ---------------------																												*
* ABNORMAL TERMINATION CONDITIONS, ERROR AND WARNING MESSAGES :																		*	
* ASSUMPTIONS, CONSTRAINTS, RESTRICTIONS :																							*
* NOTES :																															*	
* REQUIRMENTS/FUNCTIONAL SPECIFICATION REFERENCES :																					*
* DATE 			AUTHOR	 			CHANGE ID	 	RELEASE 		DESCRIPTION OF CHANGE											*
* 2013-08-02	Raymond A. Galaroza																									*
* 																																	*
* ALGORITHM(pseudocode)																												*
* 																																	*
************************************************************************************************************************************/			

############################################### include files and folder ############################################################
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/config.php');			// configuration
	include($_SERVER['DOCUMENT_ROOT'].'/wms/adodb/adodb.inc.php');			// adodb
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/function.inc.php');	// global functions
	include($_SERVER['DOCUMENT_ROOT'].'/wms/fpdf/fpdf.php');				// fpdf
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/adgbarcodelib.php');	// Barcode Class
	include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/class_db.php');		// Another Class By Jay-R
######################################################################################################################################

	$Filstar_conn	=	ADONewConnection("mysqlt");
		
	$dbFilstar		=	$Filstar_conn->Connect(WMSSERVER,WMSUSER,WMSPASS,"FDCRMSlive");
	if ($dbFilstar == false) 
	{
		echo "<script>alert('Error Occurred no Database Connection to 255_10!');</script>";
		echo "<script>location = 'index.php'</script>";
	}
	
//	$Filstar_conn_2	=	ADONewConnection("mysql");
//		
//	$dbFilstar_2	=	$Filstar_conn_2->Connect(WMSSERVER_2,WMSUSER_2,WMSPASS_2, '');
//	if ($dbFilstar_2 == false) 
//	{
//		echo "<script>alert('Error Occurred no Database Connection!');</script>";
//		echo "<script>location = 'index.php'</script>";
//	}
	
	$Filstar_pms	=	ADONewConnection("mysql");
	$dbFilstar_pms	=	$Filstar_pms->Connect('192.168.250.171','root','');
	if ($dbFilstar_pms == false) 
	{
		echo "<script>alert('Error Occurred no Database Connection to database 171!');</script>";
		echo "<script>location = 'index.php'</script>";
	}
	$Filstar_172	=	ADONewConnection("mysql");
	$dbFilstar_172	=	$Filstar_172->Connect('192.168.250.172','root','','');
	if ($dbFilstar_172== false) 
	{
		echo "<script>alert('Error Occurred no Database Connection in Server 172!');</script>";
		echo "<script>location = 'index.php'</script>";
	}
	
	$global_func	=	new __Global_Func();
?>