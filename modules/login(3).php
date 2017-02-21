<?php
/********************************************************************************************************************
* FILE NAME :	login.php																							*
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
* 2013/08/01	Raymond A. Galaroza																					*
* 																													*
* ALGORITHM(pseudocode)																								*
* 																													*
*********************************************************************************************************************/
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');

if(!empty($_POST['user']) && !empty($_POST['pass']))
{
	
	$user	=	$_POST['user'];
	$pass	=	$_POST['pass'];
	
	$cnt 	=	"SELECT COUNT(*) AS CNT FROM WMS_USER.USER WHERE USERNAME = '{$user}' AND PASSWORD = '{$pass}' AND STATUS = 'Y' ";
	
	$rscnt	=	$Filstar_conn->Execute($cnt);
	if ($rscnt->fields['CNT'] > 0) 
	{
		$sel	=	"SELECT * FROM WMS_USER.USER WHERE USERNAME = '{$user}' AND PASSWORD = '{$pass}'  AND STATUS = 'Y'";
		$rsel	=	$Filstar_conn->Execute($sel);
		while (!$rsel->EOF) 
		{
			$_SESSION['username']		=	$rsel->fields['USERNAME'];
			$_SESSION['username_id']	=	$rsel->fields['ID'];
			$TMP_MODULE					=	$rsel->fields['TMP_MODULE'];
			$_SESSION['SCANNING']		=	strstr($TMP_MODULE,"SCANNING");
			$_SESSION['NONMTO']			=	strstr($TMP_MODULE,"NONMTO");
			$rsel->MoveNext();
		}
		if (!empty($_SESSION)) 
		{
			echo "<script>location='wms.php';</script>";
		}
	}
	else 
	{
		echo "<script>alert('Invalid Account!');</script>";
		echo "<script>location='index.php';</script>";
	}
	exit();
}
?>