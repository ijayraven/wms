<?php
//include("../common/session.php");
include('../../../adodb/adodb.inc.php');
$conn	=	ADONewConnection('mysqlt');
$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
	if ($dbconn == false) 
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}
$action	= $_GET['action'];
if($action=='do_lookup'){
	$code = $_GET['txtSearchCode'];
	$desc = $_GET['txtSearchName'];
		
	if($_GET['lookup']=='prodgroup'){
		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND PRODUCTGROUP='{$code}'";
		}
		if($desc!=""){
			$where .= "AND PRODUCTGROUPDESC LIKE '%{$desc}%'";
		}
		$getprodgroup		= "SELECT PRODUCTGROUP,PRODUCTGROUPDESC FROM FDC_PMS.PRODUCTGROUP $where";
		$rs_getprodgroup	= $conn->Execute($getprodgroup);

		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_getprodgroup)){
			foreach ($rs_getprodgroup as $dataKey => $dataVal) {
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"prodgroup\",\"{$dataVal['PRODUCTGROUP']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=30% align=center height=22px>".$dataVal['PRODUCTGROUP']."&nbsp;</td>";
				$htm .= 	"<td width=45% align=center>".htmlentities(addslashes($dataVal['PRODUCTGROUPDESC']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultProdGroup').html('$htm');";
		
	}else if($_GET['lookup']=='genclass'){
		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND GENCLASS='{$code}'";
		}
		if($desc!=""){
			$where .= "AND GENCLASSDESC LIKE '%{$desc}%'";
		}
		
		$getgenc	= "SELECT GENCLASS,GENCLASSDESC FROM FDC_PMS.GENERALCLASSIFICATION $where";
		$rs_getgenc	= $conn->Execute($getgenc);

		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_getgenc)){
			foreach ($rs_getgenc as $dataKey => $dataVal) {
				$gencdesc	= htmlentities(addslashes($dataVal['GENCLASSDESC']));
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"genclass\",\"{$dataVal['GENCLASS']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=30% align=center height=22px>".$dataVal['GENCLASS']."&nbsp;</td>";
				$htm .= 	"<td width=45% align=center>".htmlentities(addslashes($dataVal['GENCLASSDESC']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultGenClass').html('$htm');";
		
	}else if($_GET['lookup']=='itemtype'){		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND ITEMTYPE='{$code}'";
		}
		if($desc!=""){
			$where .= "AND ITEMTYPEDESC LIKE '%{$desc}%'";
		}
		$getitemtype	= "SELECT ITEMTYPE,ITEMTYPEDESC FROM FDC_PMS.ITEMTYPE $where";
		$rs_getitemtype	= $conn->Execute($getitemtype);
		
		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_getitemtype)){
			foreach ($rs_getitemtype as $dataKey => $dataVal) {
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"itemtype\",\"{$dataVal['ITEMTYPE']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=30% align=center height=22px>".$dataVal['ITEMTYPE']."&nbsp;</td>";
				$htm .= 	"<td width=45% align=center>".htmlentities(addslashes($dataVal['ITEMTYPEDESC']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultItemType').html('$htm');";
		
	}else if($_GET['lookup']=='catclass'){
		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND CATEGORYCLASS='{$code}'";
		}
		if($desc!=""){
			$where .= "AND CATEGORYCLASSDESC LIKE '%{$desc}%'";
		}
		
		$getcatclass	= "SELECT CATEGORYCLASS,CATEGORYCLASSDESC FROM FDC_PMS.CATEGORYCLASS $where";
		$rs_getcatclass	= $conn->Execute($getcatclass);

		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_getcatclass)){
			foreach ($rs_getcatclass as $dataKey => $dataVal) {
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"catclass\",\"{$dataVal['CATEGORYCLASS']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=30% align=center height=22px>".$dataVal['CATEGORYCLASS']."&nbsp;</td>";
				$htm .= 	"<td width=45% align=center>".htmlentities(addslashes($dataVal['CATEGORYCLASSDESC']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultCatClass').html('$htm');";
		
	}else if($_GET['lookup']=='caption'){		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND CAPTION='{$code}'";
		}
		if($desc!=""){
			$where .= "AND CAPTIONDESC LIKE '%{$desc}%'";
		}
		$getcaption		= "SELECT CAPTION,CAPTIONDESC FROM FDC_PMS.CAPTION $where";
		$rs_getcaption	= $conn->Execute($getcaption);
		
		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_getcaption)){
			foreach ($rs_getcaption as $dataKey => $dataVal) {
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"caption\",\"{$dataVal['CAPTION']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=30% align=center height=22px>".$dataVal['CAPTION']."&nbsp;</td>";
				$htm .= 	"<td width=45% align=center>".htmlentities(addslashes($dataVal['CAPTIONDESC']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultCaption').html('$htm');";
		
	}else if($_GET['lookup']=='equi'){		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= " AND EQUI='{$code}'";
		}
		
		$getcaption		= "SELECT * FROM FDC_PMS.EQUI $where";
		$rs_getcaption	= $conn->Execute($getcaption);
		
		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=100% align=center>";
		if(!empty($rs_getcaption)){
			foreach ($rs_getcaption as $dataKey => $dataVal) {
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"equi\",\"{$dataVal['EQUI']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=11% align=center>".htmlentities(addslashes($dataVal['EQUI']))."&nbsp;</td>";			
				$htm .= 	"<td width=11% align=center>".htmlentities(addslashes($dataVal['GENCLASS']))."&nbsp;</td>";			
				$htm .= 	"<td width=11% align=center>".htmlentities(addslashes($dataVal['PRODGROUP']))."&nbsp;</td>";			
				$htm .= 	"<td width=11% align=center>".htmlentities(addslashes($dataVal['ITEMTYPE']))."&nbsp;</td>";			
				$htm .= 	"<td width=11% align=center>".htmlentities(addslashes($dataVal['CATEGORYCLASS']))."&nbsp;</td>";			
				$htm .= 	"<td width=12% align=center>".htmlentities(addslashes($dataVal['SACODE']))."&nbsp;</td>";			
				$htm .= 	"<td width=11% align=center>".htmlentities(addslashes($dataVal['PRICECLASS']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultEqui').html('$htm');";
		
	}
	exit;
}

if ($action=="do_searchlookup")
{
	$code = $_GET['code'];
	
	if($_GET['lookup']=="genc"){
		$where .= "WHERE GENCLASS='{$code}' ";
		
		$getdesc	= "SELECT GENCLASS,GENCLASSDESC FROM FDC_PMS.GENERALCLASSIFICATION $where";
		$rs_getdesc	= $conn->Execute($getdesc);
			
		foreach ($rs_getdesc as $dataKey => $dataVal) 
		{	
			echo "$('#txtGenClassDesc').val('{$dataVal['GENCLASSDESC']}');";
		}	
	}
	elseif ($_GET['lookup']=="prodgrp")
	{
		$where .= "WHERE PRODUCTGROUP='{$code}' ";
		
		$getdesc	= "SELECT PRODUCTGROUP,PRODUCTGROUPDESC FROM FDC_PMS.PRODUCTGROUP $where";
		$rs_getdesc	= $conn->Execute($getdesc);
			
		foreach ($rs_getdesc as $dataKey => $dataVal) 
		{	
			echo "$('#txtProdGroupDesc').val('{$dataVal['PRODUCTGROUPDESC']}');";
		}	
	}
	elseif ($_GET['lookup']=="itemtype")
	{
		$where .= "WHERE ITEMTYPE='{$code}' ";
		$getdesc	= "SELECT ITEMTYPE,ITEMTYPEDESC FROM FDC_PMS.ITEMTYPE $where";
		$rs_getdesc	= $conn->Execute($getdesc);
		foreach ($rs_getdesc as $dataKey => $dataVal) 
		{	
			echo "$('#txtTypeDesc').val('{$dataVal['ITEMTYPEDESC']}');";
		}	
	}
	elseif ($_GET['lookup']=="catclass")
	{
		$where .= "WHERE CATEGORYCLASS='{$code}' ";
		
		$getdesc	= "SELECT CATEGORYCLASS,CATEGORYCLASSDESC FROM FDC_PMS.CATEGORYCLASS $where";
		$rs_getdesc	= $conn->Execute($getdesc);
		
		foreach ($rs_getdesc as $dataKey => $dataVal) 
		{	
			echo "$('#txtCatClassDesc').val('{$dataVal['CATEGORYCLASSDESC']}');";
		}	
	}
	elseif ($_GET['lookup']=="caption")
	{
		$where .= "WHERE CAPTION='{$code}' ";

		$getdesc	= "SELECT CAPTION,CAPTIONDESC FROM FDC_PMS.CAPTION $where";
		$rs_getdesc	= $conn->Execute($getdesc);
		
		foreach ($rs_getdesc as $dataKey => $dataVal) 
		{	
			echo "$('#txtCaptionDesc').val('{$dataVal['CAPTIONDESC']}');";
		}	
	}
exit();
}

###################################
###################################
include('../TGAS/tgas.htm');######
###################################
###################################
?>