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
	
if($_GET['action']=='do_lookup'){
	$code = $_GET['txtSearchCode'];
	$desc = $_GET['txtSearchName'];
		
	if($_GET['lookup']=='vendor'){
		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= " AND SUPPLIERCODE='{$code}'";
		}
		if($desc!=""){
			$where .= " AND SUPPLIERNAME LIKE '%{$desc}%'";
		}
		
		$qrylookup		= "SELECT SUPPLIERCODE,SUPPLIERNAME FROM SUPPLIERS $where ";
		$rs_qrylookup	= $conn->Execute($qrylookup);
		$htm = "";
		$htm = "<table border=0 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_qrylookup)){
			foreach ($rs_qrylookup as $dataKey => $dataVal) {
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"vendor\",\"{$dataVal['SUPPLIERCODE']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=30% align=center height=22px>".$dataVal['SUPPLIERCODE']."&nbsp;</td>";
				$htm .= 	"<td width=45% align=center>".htmlentities(addslashes($dataVal['SUPPLIERNAME']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultVendor').html('$htm');";
		
	}else if($_GET['lookup']=='genclass'){
		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND GENCLASS='{$code}'";
		}
		if($desc!=""){
			$where .= "AND GENCLASSDESC LIKE '%{$desc}%'";
		}
		
		$qrylookup		= "SELECT GENCLASS,GENCLASSDESC FROM GENERALCLASSIFICATION $where ";
		$rs_qrylookup	= $conn->Execute($qrylookup);
		
		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_qrylookup)){
			foreach ($rs_qrylookup as $dataKey => $dataVal) {
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
		
	}else if($_GET['lookup']=='catclass'){
		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND CATEGORYCLASS='{$code}'";
		}
		if($desc!=""){
			$where .= "AND CATEGORYCLASSDESC LIKE '%{$desc}%'";
		}
		$qrylookup		= "SELECT CATEGORYCLASS,CATEGORYCLASSDESC FROM CATEGORYCLASS $where ";
		$rs_qrylookup	= $conn->Execute($qrylookup);

		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_qrylookup)){
			foreach ($rs_qrylookup as $dataKey => $dataVal) {
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
		$qrylookup		= "SELECT CAPTION,CAPTIONDESC FROM CAPTION $where ";
		$rs_qrylookup	= $conn->Execute($qrylookup);
		
		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_qrylookup)){
			foreach ($rs_qrylookup as $dataKey => $dataVal) {
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
		
	}else if($_GET['lookup']=='sacode'){		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND SACODE='{$code}'";
		}
		if($desc!=""){
			$where .= "AND SADESC LIKE '%{$desc}%'";
		}
		$qrylookup		= "SELECT SACODE,SADESC FROM SACODE $where ";
		$rs_qrylookup	= $conn->Execute($qrylookup);
		
		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_qrylookup)){
			foreach ($rs_qrylookup as $dataKey => $dataVal) {
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"sacode\",\"{$dataVal['SACODE']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=30% align=center height=22px>".$dataVal['SACODE']."&nbsp;</td>";
				$htm .= 	"<td width=45% align=center>".htmlentities(addslashes($dataVal['SADESC']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultSACode').html('$htm');";
		
	}else if($_GET['lookup']=='itemstat'){		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND CODE='{$code}'";
		}
		if($desc!=""){
			$where .= "AND DESCRIPTION LIKE '%{$desc}%'";
		}
		$qrylookup		= "SELECT CODE,DESCRIPTION FROM STATUS $where ";
		$rs_qrylookup	= $conn->Execute($qrylookup);
		
		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_qrylookup)){
			foreach ($rs_qrylookup as $dataKey => $dataVal) {
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"itemstat\",\"{$dataVal['CODE']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=30% align=center height=22px>".$dataVal['CODE']."&nbsp;</td>";
				$htm .= 	"<td width=45% align=center>".htmlentities(addslashes($dataVal['DESCRIPTION']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultItemStat').html('$htm');";
		
	}else if($_GET['lookup']=='priceclass'){		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND PRICECLASS='{$code}'";
		}
		if($desc!=""){
			$where .= "AND PRICECLASSDESC LIKE '%{$desc}%'";
		}
		$qrylookup		= "SELECT PRICECLASS,PRICECLASSDESC FROM PRICECLASS $where ";
		$rs_qrylookup	= $conn->Execute($qrylookup);
		
		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_qrylookup)){
			foreach ($rs_qrylookup as $dataKey => $dataVal) {
				$htm .= "<tr class=dtl bgcolor=#FFFFFF onclick=fncSelectedLookup(\"priceclass\",\"{$dataVal['PRICECLASS']}\") style=\"cursor:pointer;\">";
				$htm .= 	"<td width=30% align=center height=22px>".$dataVal['PRICECLASS']."&nbsp;</td>";
				$htm .= 	"<td width=45% align=center>".htmlentities(addslashes($dataVal['PRICECLASSDESC']))."&nbsp;</td>";			
				$htm .= "</tr>";
			}
		}else{
			$htm .= "<tr colspan=2 align=center><td>NO RECORDS FOUND</td></tr>";
		}
		$htm .= "</table>";
				
		echo "$('#divResultPriceClass').html('$htm');";
		
	}else if($_GET['lookup']=='itemtype'){		
		$where = "WHERE 1 ";
		
		if($code!=""){
			$where .= "AND ITEMTYPE='{$code}'";
		}
		if($desc!=""){
			$where .= "AND ITEMTYPEDESC LIKE '%{$desc}%'";
		}
		$qrylookup		= "SELECT ITEMTYPE,ITEMTYPEDESC FROM ITEMTYPE $where ";
		$rs_qrylookup	= $conn->Execute($qrylookup);		
		
		$htm = "";
		$htm = "<table border=1 cellpadding=0 cellspacing=0 width=75% align=center>";
		if(!empty($rs_qrylookup)){
			foreach ($rs_qrylookup as $dataKey => $dataVal) {
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
		
	}
	exit;
}	
	
if($_GET['action']=='do_search'){
	$txtSearchItemNo = trim($_GET['txtSearchItemNo']);
	$txtDesc = trim($_GET['txtDesc']);
	$txtVendorCode = trim($_GET['txtVendorCode']);
	$txGenClass = trim($_GET['txGenClass']);
	$txtCatClass = trim($_GET['txtCatClass']);
	$txCaption = trim($_GET['txCaption']);
	$txtSuppCode = trim($_GET['txtSuppCode']);
	$txSACode = trim($_GET['txSACode']);
	$txtItemStat = trim($_GET['txtItemStat']);
	$txPriceClass = trim($_GET['txPriceClass']);
	$txtItemType = trim($_GET['txtItemType']);
	$txtDateFrom = trim($_GET['txtDateFrom']);
	$txtDateTo = trim($_GET['txtDateTo']);
	$txtDateUpdateFrom = trim($_GET['txtDateUpdateFrom']);
	$txtDateUpdatedTo = trim($_GET['txtDateUpdatedTo']);
	$offset  = $_GET['offset'];
	$limit	 = 10;	
	$where   = "WHERE 1 ";
	

	
	if(empty($offset)){
		$offset 	= 	0;
		$disabled 	= 	"disabled";
		echo "$('#first').attr('disabled','disabled');";
		echo "$('#prev').attr('disabled','disabled');";
	}else{
		$offset 	= $_GET['offset'];
		echo "$('#first').removeAttr('disabled');";
		echo "$('#prev').removeAttr('disabled');";
	}		
	
	if($txtSearchItemNo!=''){
		$where .= "AND item.ITEMNO='{$txtSearchItemNo}' ";
	}
	if($txtDesc !=''){
		$where .= "AND item.ITEM_DESC LIKE '%{$txtDesc}%' ";
	}
	if($txtVendorCode!=''){
		$where .= "AND vendor.VENDOR='{$txtVendorCode}' ";
	}
	if($txGenClass!=''){
		$where .= "AND item.GENCLASS='{$txGenClass}' ";
	}
	if($txtCatClass!=''){
		$where .= "AND item.CATEGORYCLASS='{$txtCatClass}' ";
	}
	if($txCaption!=''){
		$where .= "AND item.ITEMCLASS='{$txCaption}' ";
	}
	if($txtSuppCode!=''){
		$where .= "AND item.SUPPLEMENTCODE='{$txtSuppCode}' ";
	}
	if($txSACode!=''){
		$where .= "AND item.SACODE='{$txSACode}' ";
	}
	if($txtItemStat!=''){
		$where .= "AND item.ITEMSTATUS='{$txtItemStat}' ";
	}
	if($txPriceClass!=''){
		$where .= "AND item.PRICECLASS='{$txPriceClass}' ";
	}
	if($txtItemType !=''){
		$where .= "AND item.ITEMSTATUS='{$txtItemType}' ";
	}
	if($txtDateFrom!='' and $txtDateTo!=''){
		$where .= "AND item.DATECREATED BETWEEN '{$txtDateFrom} 00:00:00' AND '{$txtDateTo} 23:59:59' ";
	}
	if($txtDateUpdateFrom!='' and $txtDateUpdatedTo!=''){
		$where .= "AND item.LASTUPDATED BETWEEN '{$txtDateUpdateFrom} 00:00:00' AND '{$txtDateUpdatedTo} 23:59:59' ";
	}
	
	$iteminq	="SELECT item.ITEMNO FROM ITEMMASTER item LEFT JOIN VENDORMASTER vendor ON vendor.ITEMNO=item.ITEMNO $where;";
	$re_iteminq	= $conn->Execute($iteminq);
	
	$recCount = $re_iteminq->RecordCount();
	
	$remain		=	$recCount % $limit;
    if ($remain != 0){
    	$lastpage = $recCount - $remain;
    }/*else if($remain==0){
    	$lastpage = 0;
    }*/else{
    	$lastpage = $recCount - $limit;
    }
    
    if ($offset == $lastpage){
		echo "$('#next').attr('disabled','disabled');";
		echo "$('#last').attr('disabled','disabled');";
	}else{
		echo "$('#next').removeAttr('disabled');";
		echo "$('#last').removeAttr('disabled');";
	}
	
	$page 		= ceil($offset / $limit) + 1;
	$maxpage 	= ceil($recCount / $limit);
	echo "$('#search').val('Page $page of $maxpage');";
	
	if($recCount > 0){
		$htm = rsDisplay($where,$offset,$limit);
	}else{
		$htm  = "<table border=0 cellpadding=0 cellspacing=0 width=90% align=center>";
		$htm .= "<tr class=dtl bgcolor=#FFFFFF>";
		$htm .= 	"<td colspan=7 align=center class-dtl>NO RECORD FOUND</td>";
		$htm .= "</tr>";	
		$htm .= "</table>";
	}
	$htm = addslashes($htm);
	echo "$('#hidOffset').val('$offset');";
	echo "$('#hidLimit').val('$limit');";
	echo "$('#hidRecCount').val('$recCount');";
	if ($recCount >0)
	{
		echo "$('#dvdownload').show();";
	}
	echo "$('#hidlastPage').val('$lastpage');";
	echo "$('#hidCheckCtr').val('$ctrCheck');";
	echo "$('#divResult').html('$htm');";
	exit;
}

if($_GET['action']=='do_navigate'){
	$offset 			= $_GET['offset'];
	$limit				= 10;
	$txtSearchItemNo 	= trim($_GET['txtSearchItemNo']);
	$txtDesc 			= trim($_GET['txtDesc']);
	$txtVendorCode 		= trim($_GET['txtVendorCode']);
	$txGenClass 		= trim($_GET['txGenClass']);
	$txtCatClass 		= trim($_GET['txtCatClass']);
	$txCaption 			= trim($_GET['txCaption']);
	$txtSuppCode 		= trim($_GET['txtSuppCode']);
	$txSACode 			= trim($_GET['txSACode']);
	$txtItemStat 		= trim($_GET['txtItemStat']);
	$txPriceClass 		= trim($_GET['txPriceClass']);
	$txtItemType 		= trim($_GET['txtItemType']);
	$txtDateFrom 		= trim($_GET['txtDateFrom']);
	$txtDateTo 			= trim($_GET['txtDateTo']);
	$txtDateUpdateFrom 	= trim($_GET['txtDateUpdateFrom']);
	$txtDateUpdatedTo 	= trim($_GET['txtDateUpdatedTo']);
	
	if(empty($offset)){
		$offset 	= 	0;
		$disabled 	= 	"disabled";
		echo "$('#first').attr('disabled','disabled');";
		echo "$('#prev').attr('disabled','disabled');";
	}else{
		$offset 	= $_GET['offset'];
		echo "$('#first').removeAttr('disabled');";
		echo "$('#prev').removeAttr('disabled');";
	}		
	
	$where = "WHERE 1 ";
	if($txtSearchItemNo!=''){
		$where .= "AND item.ITEMNO='{$txtSearchItemNo}' ";
	}
	if($txtDesc !=''){
		$where .= "AND item.ITEM_DESC LIKE '%{$txtDesc}%' ";
	}
	if($txtVendorCode!=''){
		$where .= "AND vendor.VENDOR='{$txtVendorCode}' ";
	}
	if($txGenClass!=''){
		$where .= "AND item.GENCLASS='{$txGenClass}' ";
	}
	if($txtCatClass!=''){
		$where .= "AND item.CATEGORYCLASS='{$txtCatClass}' ";
	}
	if($txCaption!=''){
		$where .= "AND item.ITEMCLASS='{$txCaption}' ";
	}
	if($txtSuppCode!=''){
		$where .= "AND item.SUPPLEMENTCODE='{$txtSuppCode}' ";
	}
	if($txSACode!=''){
		$where .= "AND item.SACODE='{$txSACode}' ";
	}
	if($txtItemStat!=''){
		$where .= "AND item.ITEMSTATUS='{$txtItemStat}' ";
	}
	if($txPriceClass!=''){
		$where .= "AND item.PRICECLASS='{$txPriceClass}' ";
	}
	if($txtItemType !=''){
		$where .= "AND item.ITEMSTATUS='{$txtItemType}' ";
	}
	if($txtDateFrom!='' and $txtDateTo!=''){
		$where .= "AND item.DATECREATED BETWEEN '{$txtDateFrom} 00:00:00' AND '{$txtDateTo} 23:59:59' ";
	}
	if($txtDateUpdateFrom!='' and $txtDateUpdatedTo!=''){
		$where .= "AND item.LASTUPDATED BETWEEN '{$txtDateUpdateFrom} 00:00:00' AND '{$txtDateUpdatedTo} 23:59:59' ";
	}
	
	$iteminq	="SELECT item.ITEMNO FROM ITEMMASTER item LEFT JOIN VENDORMASTER vendor ON vendor.ITEMNO=item.ITEMNO $where;";
	$re_iteminq	= $conn->Execute($iteminq);
	
	$recCount	= $re_iteminq->RecordCount();
	
	$remain		=	$recCount % $limit;
    if ($remain != 0){
    	$lastpage = $recCount - $remain;
    }else{
    	$lastpage = $recCount - $limit;
    }
    
    if ($offset == $lastpage){
		echo "$('#next').attr('disabled','disabled');";
		echo "$('#last').attr('disabled','disabled');";
	}else{
		echo "$('#next').removeAttr('disabled');";
		echo "$('#last').removeAttr('disabled');";
	}
	
	$page 		= ceil($offset / $limit) + 1;
	$maxpage 	= ceil($recCount / $limit);
	echo "$('#search').val('Page $page of $maxpage');";
	
	if($recCount>0){
		
		$qrydisplay		= "SELECT item.ITEMNO,item.ITEM_DESC,vendor.VENDOR,vendor.ADDEDDATE,item.DATECREATED,item.LASTUPDATED FROM ITEMMASTER item LEFT JOIN VENDORMASTER vendor ON vendor.ITEMNO=item.ITEMNO $where LIMIT {$offset},{$limit}";
		$rs_qrydisplay	= $conn->Execute($qrydisplay);
	
		$ctr = 0;
		$htm = "";
		$htm = "<table border=0 cellpadding=0 cellspacing=0 width=90% align=center>";
		foreach ($rs_qrydisplay as $dataKey => $dataVal) {
		$ctr++;		
		$htm .= "<tr class=dtl bgcolor=#FFFFFF >";
		$htm .= 	"<td width=10% align=center height=20px class=dtl>".$dataVal['ITEMNO']."&nbsp;</td>";
		$htm .= 	"<td width=30% align=center class=dtl>".substr(htmlentities(stripslashes($dataVal['ITEM_DESC'])),0,40)."&nbsp;</td>";
		$htm .= 	"<td width=10% align=center class=dtl>".$dataVal['VENDOR']."&nbsp;</td>";
		$htm .= 	"<td width=10% align=center class=dtl>".substr($dataVal['DATECREATED'],0,10)."</td>";	
//		if (substr($dataVal['ADDEDDATE'],0,10) != '0000-00-00') {
//			$htm .= 	"<td width=10% align=center>".substr($dataVal['ADDEDDATE'],0,10)."</td>";	
//		}
//		else 
//		{
			$htm .= 	"<td width=10% align=center class=dtl>".substr($dataVal['LASTUPDATED'],0,10)."</td>";	
//		}
		$htm .= 	"<td width=10% align=center class=dtl><img height=\"22px\"  title=\"VIEW\" src=\"../../../images/images/action_icon/report_blue.png\" class=\"action_butt\" onclick=\"fncView('".$dataVal['ITEMNO']."')\">&nbsp;</td>";
		$htm .= "</tr>";
	}	
	$htm .= "</table>";
	}else{
		$htm  = "<table border=0 cellpadding=0 cellspacing=0 width=80% align=center>";
		$htm .= "<tr class=dtl bgcolor=#FFFFFF>";
		$htm .= 	"<td colspan=7 align=center class=dtl>NO RECORD FOUND</td>";
		$htm .= "</tr>";	
		$htm .= "</table>";
	}
	$htm = addslashes($htm);
	echo "$('#hidOffset').val('$offset');";
	echo "$('#hidLimit').val('$limit');";
	echo "$('#hidRecCount').val('$recCount');";
	echo "$('#hidlastPage').val('$lastpage');";
	echo "$('#hidCheckCtr').val('$ctrCheck');";
	echo "$('#divResult').html('$htm');";
	exit;
}

if($_GET['action']=="do_view"){
	echo "$('#lblItemNo').html('');";
	echo "$('#lblItemDesc').html('');";
	echo "$('#lblBarcodeDesc').html('');";
	echo "$('#lblBarcodeType').html('');";
	echo "$('#lblNBSBarcode').html('');";
	echo "$('#lblTradeBarcode').html('');";
	echo "$('#lblFilBarcode').html('');";
	echo "$('#lblItemCat').html('');";
	echo "$('#lblSupCode').html('');";
	echo "$('#lblProdPlan').html('');";
	echo "$('#lblSecGrp').html('');";
	echo "$('#lblKCStockNo').html('');";
	echo "$('#lblStat').html('');";
	echo "$('#lblImpItemCode').html('');";
	echo "$('#lblPriceClass').html('');";
	echo "$('#lblVendor').html('');";
	echo "$('#lblSecGrp').html('');";
	echo "$('#lblMerchCode').html('');";
	echo "$('#lblCharCode').html('');";
	echo "$('#lblAlphCode').html('');";
	echo "$('#lblColor').html('');";
	echo "$('#lblSize').html('');";
	
	echo "$('#lblRetPrice').html('');";
	echo "$('#lblLithoHome').html('');";
	echo "$('#lblRoyaltyTPR').html('');";
	echo "$('#lblCostPrice').html('');";
	echo "$('#lblUOM').html('');";
	echo "$('#lblPackCode').html('');";
	echo "$('#lblPaperCost').html('');";
	echo "$('#lblPlasticCost').html('');";
	echo "$('#lblStickCost').html('');";
	echo "$('#lblPiecework').html('');";
	echo "$('#lblEnvelope').html('');";
	echo "$('#lblOther').html('');";
	echo "$('#lblVatType').html('');";
	echo "$('#lblAutoReOrder').html('');";
	echo "$('#lblMinOrder').html('');";
	echo "$('#lblMaxOrder').html('');";
	
	echo "$('#lblGenClass').html('');";
	echo "$('#lblReturnable').html('');";
	echo "$('#lblProdGrp').html('');";
	echo "$('#lblWarehouseItem').html('');";
	echo "$('#lblItemType').html('');";
	echo "$('#lblWebsiteItem').html('');";
	echo "$('#lblCatClass').html('');";
	echo "$('#lblCaption').html('');";
	echo "$('#lblSalesAnalCode').html('');";
	echo "$('#lblGrpCodeDesc').html('');";

	$isvat = "";
	$isAutoReOrder = "";
	$isReturnable = "";
	$isWarehouse = "";
	$isWebsite = "";
	
	$itemno = $_GET['code'];
	
	$iteminq	="SELECT * FROM FDC_PMS.ITEMMASTER WHERE ITEMNO='{$itemno}'";
	$rs_iteminq	= $conn->Execute($iteminq);
		
	foreach ($rs_iteminq as $dataKey => $dataVal) {	
		echo "$('#lblItemNo').html('{$dataVal['ITEMNO']}');";
		echo "$('#lblItemDesc').html('".addslashes($dataVal['ITEM_DESC'])."');";
		echo "$('#lblBarcodeDesc').html('".addslashes($dataVal['BAR_DESC'])."');";
		
		if($dataVal['BARCODE_TYPE']=='4809225'){
			$barcodetype = "FILSTAR";
		}else if($dataVal['BARCODE_TYPE']=='4800600'){
			$barcodetype = "BIC";
		}
		echo "$('#lblBarcodeType').html('{$dataVal['BARCODE_TYPE']} - {$barcodetype}');";
		echo "$('#lblNBSBarcode').html('{$dataVal['NBS_BARCODE']}');";
		echo "$('#lblTradeBarcode').html('{$dataVal['TRADE_BARCODE']}');";
		echo "$('#lblFilBarcode').html('{$dataVal['FILSTAR_BARCODE']}');";
		if($dataVal['ITEMCATEGORY']!=''){
			$itemcatdesc = sel_val($conn,"FDC_PMS","ITEMCATEGORY","DESCRIPTION","TYPE='{$dataVal['ITEMCATEGORY']}'");
			echo "$('#lblItemCat').html('{$dataVal['ITEMCATEGORY']} - {$itemcatdesc}');";		
		}		
		echo "$('#lblSupCode').html('{$dataVal['SUPPLEMENTCODE']}');";
		echo "$('#lblProdPlan').html('{$dataVal['PRODPLANCODE']}');";
		echo "$('#lblSecGrp').html('{$dataVal['SECTION']}');";
		echo "$('#lblKCStockNo').html('{$dataVal['IMP_ITEMCODE']}');";
		if($dataVal['ITEMSTATUS']!=''){
			$itemstatdesc = sel_val($conn,"FDC_PMS","STATUS","DESCRIPTION","CODE='{$dataVal['ITEMSTATUS']}'");
			echo "$('#lblStat').html('{$dataVal['ITEMSTATUS']} - {$itemstatdesc}');";	
		}	
		echo "$('#lblImpItemCode').html('{$dataVal['IMP_ITEMCODE']}');";
		echo "$('#lblPriceClass').html('{$dataVal['PRICECLASS']}');";
		$dataVEndor		= "SELECTvendor.VENDOR,supp.SUPPLIERNAME FROM VENDORMASTER vendor LEFT JOIN SUPPLIERS supp ON vendor.VENDOR=supp.SUPPLIERCODE WHERE vendor.ITEMNO='{$itemno}'";
		$rs_dataVEndor	= $conn->Execute($dataVEndor);
		foreach ($rs_dataVEndor as $dataKeyV => $dataValV) {
			$vendorname =  addslashes($dataValV['VENDOR'].' - '.$dataValV['SUPPLIERNAME']);
		}
		echo "$('#lblVendor').html('{$vendorname}');";
		echo "$('#lblSecGrp').html('{$dataVal['SECTION']}');";
		echo "$('#lblMerchCode').html('{$dataVal['MERCHCODE']}');";
		echo "$('#lblCharCode').html('{$dataVal['CHARACTERCODE']}');";
		echo "$('#lblAlphCode').html('{$dataVal['ALPHACODE']}');";
		echo "$('#lblColor').html('{$dataVal['ALPHACODE']}');";
		echo "$('#lblSize').html('{$dataVal['ITEMSIZE']}');";
		
		echo "$('#lblRetPrice').html('{$dataVal['SELLPRICE']}');";
		echo "$('#lblLithoHome').html('{$dataVal['HOMECOST']}');";
		echo "$('#lblRoyaltyTPR').html('{$dataVal['ROYALTYCODE']}');";
		echo "$('#lblCostPrice').html('{$dataVal['COSTPRICE']}');";
		if($dataVal['BUOM']!=''){
			$uomdesc = sel_val($conn,"FDC_PMS","UNITOFMEASURE","UNITDESC","UNIT='{$dataVal['BUOM']}'");
			echo "$('#lblUOM').html('{$dataVal['BUOM']} - {$uomdesc}');";		
		}	
		
		if($dataVal['SUOM']!=''){
			$suomdesc = sel_val($conn,"FDC_PMS","USERDEFINEDCODES","CODEDESC","CODETYPE='PACKCODE' AND CODEVALUE='{$dataVal['SUOM']}'");
			echo "$('#lblPackCode').html('{$dataVal['SUOM']} - {$suomdesc}');";		
		}	
		
		echo "$('#lblPaperCost').html('{$dataVal['PAPERCOST']}');";
		echo "$('#lblPlasticCost').html('{$dataVal['PLASTICCOST']}');";
		echo "$('#lblStickCost').html('{$dataVal['STICKERCOST']}');";
		echo "$('#lblPiecework').html('{$dataVal['PIECEWORKCOST']}');";
		echo "$('#lblEnvelope').html('{$dataVal['ENVELOPECOST']}');";
		echo "$('#lblOther').html('{$dataVal['OTHERCOST']}');";

		
		if($dataVal['GENCLASS']!=''){
			$genclassdesc = sel_val($conn,"FDC_PMS","GENERALCLASSIFICATION","GENCLASSDESC","GENCLASS='{$dataVal['GENCLASS']}'");
			echo "$('#lblGenClass').html('{$dataVal['GENCLASS']} - {$genclassdesc}');";		
		}	
		if($dataVal['PRODGROUP']!=''){
			$prodgrpdesc = sel_val($conn,"FDC_PMS","PRODUCTGROUP","PRODUCTGROUPDESC","PRODUCTGROUP='{$dataVal['PRODGROUP']}'");
			echo "$('#lblProdGrp').html('{$dataVal['PRODGROUP']} - {$prodgrpdesc}');";		
		}	
		
		if($dataVal['ITEMTYPE']!=''){
			$itemtypedesc = sel_val($conn,"FDC_PMS","ITEMTYPE","ITEMTYPEDESC","ITEMTYPE='{$dataVal['ITEMTYPE']}'");
			echo "$('#lblItemType').html('{$dataVal['ITEMTYPE']} - {$itemtypedesc}');";
		}	
		
		if($dataVal['CATEGORYCLASS']!=''){
			$catclassdesc = sel_val($conn,"FDC_PMS","CATEGORYCLASS","CATEGORYCLASSDESC","CATEGORYCLASS='{$dataVal['CATEGORYCLASS']}'");
			echo "$('#lblCatClass').html('{$dataVal['CATEGORYCLASS']} - {$catclassdesc}');";
		}	
		
		if($dataVal['ITEMCLASS']!=''){
			$captiondesc = sel_val($conn,"FDC_PMS","CAPTION","CAPTIONDESC","CAPTION='{$dataVal['ITEMCLASS']}'");
			echo "$('#lblCaption').html('{$dataVal['ITEMCLASS']} - {$captiondesc}');";
		}	
		
		if($dataVal['SACODE']!=''){
			$sadesc = sel_val($conn,"FDC_PMS","SACODE","SADESC","SACODE='{$dataVal['SACODE']}'");
			echo "$('#lblSalesAnalCode').html('{$dataVal['SACODE']} - {$sadesc}');";
		}
		echo "$('#lblGrpCodeDesc').html('".addslashes($dataVal['GROUPCODE'] ."-". $dataVal['GROUPCDESC'])."');";
	}	

	echo "$('#dialog_view_item').dialog('open');";
	exit;
}

function rsDisplay($where,$offset,$limit)
{
	$conn	=	ADONewConnection('mysqlt');
	$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
	if ($dbconn == false) 
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}
	
	$qrydisplay		= "SELECT item.ITEMNO,item.ITEM_DESC,vendor.VENDOR,vendor.ADDEDDATE,item.DATECREATED,item.LASTUPDATED FROM ITEMMASTER item LEFT JOIN VENDORMASTER vendor ON vendor.ITEMNO=item.ITEMNO $where LIMIT {$offset},{$limit}";
	$rs_qrydisplay	= $conn->Execute($qrydisplay);
	
	$ctr = 0;
	$htm = "";
	$htm = "<table border=0 cellpadding=0 cellspacing=0 width=90% align=center>";
	foreach ($rs_qrydisplay as $dataKey => $dataVal) {
		$ctr++;		
		$htm .= "<tr class=dtl bgcolor=#FFFFFF >";
		$htm .= 	"<td width=10% align=center height=20px class=dtl>".$dataVal['ITEMNO']."&nbsp;</td>";
		$htm .= 	"<td width=30% align=center class=dtl>".substr(htmlentities(stripslashes($dataVal['ITEM_DESC'])),0,40)."&nbsp;</td>";
		$htm .= 	"<td width=10% align=center class=dtl>".$dataVal['VENDOR']."&nbsp;</td>";
		$htm .= 	"<td width=10% align=center class=dtl>".substr($dataVal['DATECREATED'],0,10)."</td>";	
//		if (substr($dataVal['ADDEDDATE'],0,10) != '0000-00-00') {
//			$htm .= 	"<td width=10% align=center>".substr($dataVal['ADDEDDATE'],0,10)."</td>";	
//		}
//		else 
//		{
			$htm .= 	"<td width=10% align=center class=dtl>".substr($dataVal['LASTUPDATED'],0,10)."</td>";	
//		}
		$htm .= 	"<td width=10% align=center class=dtl><img height=\"22px\"  title=\"VIEW\" src=\"../../../images/images/action_icon/report_blue.png\" class=\"action_butt\" onclick=\"fncView('".$dataVal['ITEMNO']."')\">&nbsp;</td>";
		$htm .= "</tr>";
	}	
	$htm .= "</table>";
	return $htm;
}

function sel_val($conn,$database,$tbl,$fld,$condition)
{
	$sel	=	"SELECT $fld FROM ".$database.".$tbl WHERE $condition";
	$rssel	=	$conn->Execute($sel);
		if($rssel == false)
		{
			echo $conn->ErrorMsg()."::".__LINE__;
			exit();
		}
	$retval	=	$rssel->fields["$fld"];
	return $retval;
}
function Sel_val2($conn2,$database,$tbl,$fld,$condition)
{
	$sel	=	"SELECT $fld FROM ".$database.".$tbl WHERE $condition";
	$rssel	=	$conn2->Execute($sel);
		if($rssel == false)
		{
			echo $conn2->ErrorMsg()."::".__LINE__;
			exit();
		}
	$retval	=	$rssel->fields["$fld"];
	return $retval;
}



################################################
################################################
include('../iteminquiry/iteminquiry.htm');######
################################################
################################################
?>