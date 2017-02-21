<?php
include("../../../adodb/adodb.inc.php");

	$conn	=	ADONewConnection('mysqlt');
	$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
	$conn2	=	ADONewConnection('mysqlt');
	$dbconn2	=	$conn2->Connect('192.168.255.10','root','','FDCRMSlive');
	
if($_GET['action']=="do_download"){
	
//	$output		=	";;;;;;;;;;;ITEM DETAILS;;;;;;;;;;;;;;;;;;;;PRICE / COST / UM INFORMATION;;;;;;;;ADDITIONAL INFORMATION;;;;\r";
	$output		=	"Item Number;Item Description;Barcode Description; Barcode Type;NBS Barcode;Trade Barcode;Filstar Barcode; Item Category/Type;Supplement Code;Product Planner Code;Section Group;KC Stock No;Status;Brand;Imported Item Code;Price Class;Vendor Code;Section Group;Merchandise Code;Character Code;Alpha Code;Color;Size;Retail Price;Litho/Home Cost; Royalty/TPR Code;Cost Price ;Unit of Measure; Packing Code;Paper Cost ;Plastic Cost ;Sticker Cost ;Piecework Cost; Envelope Cost ;Other Cost ;General Class; Category Class; Product Group;Caption;Item Type; Sales Analysis Code;Group Code/Description;Created Date; Created By\r\r";
	
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
	if($recCount>0)
	{
		
		$qrydisplay		= "SELECT item.ITEMNO,item.ARTICLE,item.BARCODE_TYPE,item.NBS_BARCODE,item.FILSTAR_BARCODE,item.TRADE_BARCODE,item.ITEM_DESC,item.BAR_DESC,item.ITEMTYPE,item.GENCLASS,item.PRODGROUP,item.CATEGORYCLASS,item.ITEMCLASS,item.SACODE,item.GROUPCODE,item.GROUPCDESC,item.AUTO_REORDER,item.SUPPLEMENTCODE,item.PRODPLANCODE,item.IMP_ITEMCODE,item.MERCHCODE,item.CHARACTERCODE,item.ITEMSTATUS,item.ITEMCATEGORY,item.SECTION,item.PRICECLASS,item.ITEMCOLOR,item.ITEMSIZE,item.LENGTH,item.WIDTH,item.HEIGHT,item.ALPHACODE,item.MINORDERQTY,item.SUOM,item.COSTPRICE,item.SELLPRICE,item.HOMECOST,item.TPRCODE,item.PAPERCOST,item.PLASTICCOST,item.STICKERCOST,item.PIECEWORKCOST,item.ENVELOPECOST,item.OTHERCOST,item.PRODUCT_COST,item.VATTYPE,item.BUOM,item.UOMCONVF,item.DATECREATED,item.CREATEDBY,item.LASTUPDATED,item.EDITBY,item.ISWHS_ITEM,item.MAXORDERQTY,item.ISWS_ITEM,item.RETURNABLE,item.BRAND,item.CATEGORY,item.SUB_CATEGORY,item.CLASS,vendor.VENDOR,vendor.ADDEDDATE FROM ITEMMASTER item LEFT JOIN VENDORMASTER vendor ON vendor.ITEMNO=item.ITEMNO $where ";
		$rs_iteminq	= $conn->Execute($qrydisplay);
			
		while (!$rs_iteminq->EOF) 
		{
			$itemno			=	$rs_iteminq->fields['ITEMNO'];
			$desc			=	addslashes($rs_iteminq->fields['ITEM_DESC']);
			$bardesc		=	addslashes($rs_iteminq->fields['BAR_DESC']);
			$barcodetype	=	$rs_iteminq->fields['BARCODE_TYPE'];
			if($barcodetype =='4809225')
			{
				$barcodename = "FILSTAR";
			}
			else if($barcodetype =='4800600')
			{
				$barcodename = "BIC";
			}
			$nbsbarcode				=	$rs_iteminq->fields['NBS_BARCODE'];
			$tradebarcode			=	$rs_iteminq->fields['TRADE_BARCODE'];
			$filstarbarcode			=	$rs_iteminq->fields['FILSTAR_BARCODE'];
			$itemcategory			=	$rs_iteminq->fields['ITEMCATEGORY'];
			$itemcatdesc			= sel_val($conn,"FDC_PMS","ITEMCATEGORY","DESCRIPTION","TYPE='{$itemcategory}'");
			$supplementcode			=	$rs_iteminq->fields['SUPPLEMENTCODE'];
			$prodplancode			=	$rs_iteminq->fields['PRODPLANCODE'];
			$section				=	$rs_iteminq->fields['SECTION'];
			$imp_itemcode			=	$rs_iteminq->fields['IMP_ITEMCODE'];
			$itemstatus				=	$rs_iteminq->fields['ITEMSTATUS'];
			$itemstatdesc 			= sel_val($conn,"FDC_PMS","STATUS","DESCRIPTION","CODE='{$itemstatus}'");
			$priceclass				=	$rs_iteminq->fields['PRICECLASS'];
			$dataVEndor				= "SELECT vendor.VENDOR,supp.SUPPLIERNAME FROM VENDORMASTER vendor LEFT JOIN SUPPLIERS supp ON vendor.VENDOR=supp.SUPPLIERCODE WHERE vendor.ITEMNO='{$itemno}'";
			$rs_dataVEndor			= $conn->Execute($dataVEndor);
			foreach ($rs_dataVEndor as $dataKeyV => $dataValV)
			{
				$vendorname 	=	addslashes($dataValV['VENDOR']);
				$suppliername	=	addslashes($dataValV['SUPPLIERNAME']);
			}
			$section			=	$rs_iteminq->fields['SECTION'];
			$merchcode			=	$rs_iteminq->fields['MERCHCODE'];
			$charcode			=	$rs_iteminq->fields['CHARACTERCODE'];
			$alphacode			=	$rs_iteminq->fields['ALPHACODE'];
			$color				=	$rs_iteminq->fields['ALPHACODE'];
			$size				=	$rs_iteminq->fields['ITEMSIZE'];
			$sellprice			=	$rs_iteminq->fields['SELLPRICE'];
			$homecost			=	$rs_iteminq->fields['HOMECOST'];
			$royalty			=	$rs_iteminq->fields['ROYALTYCODE'];
			$costprice			=	$rs_iteminq->fields['COSTPRICE'];
			$buom				=	$rs_iteminq->fields['BUOM'];
			$buomdesc			= sel_val($conn,"FDC_PMS","UNITOFMEASURE","UNITDESC","UNIT='{$buom}'");
			$suom				=	$rs_iteminq->fields['SUOM'];
			$suomdesc			= sel_val($conn,"FDC_PMS","USERDEFINEDCODES","CODEDESC","CODETYPE='PACKCODE' AND CODEVALUE='{$suom}'");
			$papercost			=	$rs_iteminq->fields['PAPERCOST'];
			$plasticcost		=	$rs_iteminq->fields['PLASTICCOST'];
			$stickercost		=	$rs_iteminq->fields['STICKERCOST'];
			$pieceworkcost		=	$rs_iteminq->fields['PIECEWORKCOST'];
			$envelopecost		=	$rs_iteminq->fields['ENVELOPECOST'];
			$othercost			=	$rs_iteminq->fields['OTHERCOST'];
			$genclass			=	$rs_iteminq->fields['GENCLASS'];
			$genclassdesc 		= sel_val($conn,"FDC_PMS","GENERALCLASSIFICATION","GENCLASSDESC","GENCLASS='{$genclass}'");
			$prodgroup			=	$rs_iteminq->fields['PRODGROUP'];
			$prodgrpdesc		= sel_val($conn,"FDC_PMS","PRODUCTGROUP","PRODUCTGROUPDESC","PRODUCTGROUP='{$prodgroup}'");
			$itemtype			=	$rs_iteminq->fields['ITEMTYPE'];
			$itemtypedesc		= sel_val($conn,"FDC_PMS","ITEMTYPE","ITEMTYPEDESC","ITEMTYPE='{$itemtype}'");
			$catclass			=	$rs_iteminq->fields['CATEGORYCLASS'];
			$catclassdesc 		= sel_val($conn,"FDC_PMS","CATEGORYCLASS","CATEGORYCLASSDESC","CATEGORYCLASS='{$catclass}'");
			$itemclass			=	$rs_iteminq->fields['ITEMCLASS'];		
			$captiondesc		= sel_val($conn,"FDC_PMS","CAPTION","CAPTIONDESC","CAPTION='{$itemclass}'");
			$sacode				=	$rs_iteminq->fields['SACODE'];
			$sadesc				= sel_val($conn,"FDC_PMS","SACODE","SADESC","SACODE='{$sacode}'");
			$groupcode			=	addslashes($rs_iteminq->fields['GROUPCODE']);
			$groupdesc			=	$rs_iteminq->fields['GROUPCDESC'];
			$createddate		=	$rs_iteminq->fields['DATECREATED'];
			$createdby			=	$rs_iteminq->fields['CREATEDBY'];
			$brand				=	$rs_iteminq->fields['BRAND'];
			$brandname			= sel_val($conn,"FDC_PMS","BRAND_NEW","BRAND_NAME","BRAND_ID='{$brand}'");

		$output		.="	$itemno;$desc;$bardesc;$barcodetype - $barcodename;	$nbsbarcode;	$tradebarcode;	$filstarbarcode;$itemcategory - $itemcatdesc;$supplementcode;$prodplancode;$section;$imp_itemcode;$itemstatus - $itemstatdesc;$brand - $brandname;$imp_itemcode;$priceclass;$vendorname - $suppliername;$section;$merchcode;$charcode;$alphacode;$color;$size;$sellprice;$homecost;$royalty;$costprice;$buom - $buomdesc;$suom - $suomdesc;$papercost;$plasticcost;$stickercost;$pieceworkcost;$envelopecost;$othercost;$genclass - $genclassdesc;$prodgroup - $prodgrpdesc;$itemtype - $itemtypedesc;$catclass - $catclassdesc;$itemclass - $captiondesc; $sacode - $sadesc;$groupcode - $groupdesc;$createddate;$createdby;\r";

		$rs_iteminq->MoveNext();
		}
	$today = date("Y-m-d");
	header("Content-Disposition: attachment; filename=iteminquiry$today.csv");
	header("Content-Location: $_SERVER[REQUEST_URI]");
	header("Content-Type: text/plain");
	header("Expires: 0");
	echo $output;
	}
	
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
?>