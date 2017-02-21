function pageNavigator(id)
{
	var offset	 = $('#hidOffset').val();
	var totalrec = $('#hidRecCount').val();
	var limit	 = $('#hidLimit').val();
	
	var txtSearchItemNo = $('#txtSearchItemNo').val();
	var txtDesc = $('#txtDesc').val();
	var txtVendorCode = $('#txtVendorCode').val();
	var txGenClass = $('#txGenClass').val();
	var txtCatClass = $('#txtCatClass').val();
	var txCaption = $('#txCaption').val();
	var txtSuppCode = $('#txtSuppCode').val();
	var txSACode = $('#txSACode').val();
	var txtItemStat = $('#txtItemStat').val();
	var txPriceClass = $('#txPriceClass').val();
	var txtItemType = $('#txtItemType').val();
	var txtDateFrom = $('#txtDateFrom').val();
	var txtDateTo = $('#txtDateTo').val();
	var txtDateUpdateFrom = $('#txtDateUpdateFrom').val();
	var txtDateUpdatedTo = $('#txtDateUpdatedTo').val();

	if(id=='next'){ var link 	 = 	parseInt(offset) + parseInt(limit); }
	if(id=='prev'){ var link 	 = 	parseInt(offset) - (limit); }
	if(id=='first'){ var link 	 = 	parseInt(0); }
	if(id=='last'){ var link	 =	$('#hidlastPage').val(); }

	$('#spinner').show();
	$('#dvSearch').html(' S e a r c h i n g  .<blink> . . </blink>');
	
	$.ajax({
		type: "POST",
		url: 'iteminquiry.php?action=do_navigate&offset='+link+'&txtSearchItemNo='+txtSearchItemNo+'&txtDesc='+txtDesc+'&txtVendorCode='+txtVendorCode+'&txGenClass='+txGenClass+'&txtCatClass='+txtCatClass+'&txCaption='+txCaption+'&txtSuppCode='+txtSuppCode+'&txSACode='+txSACode+'&txtItemStat='+txtItemStat+'&txPriceClass='+txPriceClass+'&txtItemType='+txtItemType+'&txtDateFrom='+txtDateFrom+'&txtDateTo='+txtDateTo+'&txtDateUpdateFrom='+txtDateUpdateFrom+'&txtDateUpdatedTo='+txtDateUpdatedTo,
		success: function(html) {			
			if (link <= totalrec){						
				eval(html);			
				$('#spinner').hide();
				$('#dvSearch').html('');				
			}
			$('#txtSearchCode').select();
		}		
	})	
}

function fncSearch(page){
	var txtSearchItemNo = $('#txtSearchItemNo').val();
	var txtDesc = $('#txtDesc').val();
	var txtVendorCode = $('#txtVendorCode').val();
	var txGenClass = $('#txGenClass').val();
	var txtCatClass = $('#txtCatClass').val();
	var txCaption = $('#txCaption').val();
	var txtSuppCode = $('#txtSuppCode').val();
	var txSACode = $('#txSACode').val();
	var txtItemStat = $('#txtItemStat').val();
	var txPriceClass = $('#txPriceClass').val();
	var txtItemType = $('#txtItemType').val();
	var txtDateFrom = $('#txtDateFrom').val();
	var txtDateTo = $('#txtDateTo').val();
	var txtDateUpdateFrom = $('#txtDateUpdateFrom').val();
	var txtDateUpdatedTo = $('#txtDateUpdatedTo').val();
	
	if(txtSearchItemNo!='' || txtDesc!='' || txtVendorCode!='' || txGenClass!='' || txtCatClass!='' || txCaption!='' || txtSuppCode!='' || txSACode!='' || txtItemStat!='' || txPriceClass!='' || txtItemType!='' || (txtDateFrom!='' && txtDateTo!='') || (txtDateUpdateFrom!='' && txtDateUpdatedTo!='')){
		$('#spinner').show();
		$('#dvSearch').html(' S e a r c h i n g  .<blink> . . </blink>');
		
		$.ajax({
		type: "POST",
		url: 'iteminquiry.php?action=do_search&txtSearchItemNo='+txtSearchItemNo+'&txtDesc='+txtDesc+'&txtVendorCode='+txtVendorCode+'&txGenClass='+txGenClass+'&txtCatClass='+txtCatClass+'&txCaption='+txCaption+'&txtSuppCode='+txtSuppCode+'&txSACode='+txSACode+'&txtItemStat='+txtItemStat+'&txPriceClass='+txPriceClass+'&txtItemType='+txtItemType+'&txtDateFrom='+txtDateFrom+'&txtDateTo='+txtDateTo+'&txtDateUpdateFrom='+txtDateUpdateFrom+'&txtDateUpdatedTo='+txtDateUpdatedTo+'&pageno='+page,
		beforeSend: function()
		{
			$('#divloader').dialog("open");
		},
		success: function(html) {
				eval(html);	
				
//				alert(html);return;	
				$('#spinner').hide();
				$('#dvSearch').html('');
				$('#divloader').dialog("close");
				
			}		
		})	
		
	}else
	{		
		alert('Nothing to search!')
	}
	$('#txtSearchItemNo').select();
}

function fncLookup(type){
	if(type=='vendor'){
		$("#dialog_lookup_vendor").dialog('open');
	}else if(type=='genclass'){
		$("#dialog_lookup_genclass").dialog('open');
	}else if(type=='catclass'){
		$("#dialog_lookup_catclass").dialog('open');
	}else if(type=='caption'){
		$("#dialog_lookup_caption").dialog('open');
	}else if(type=='sacode'){
		$("#dialog_lookup_sacode").dialog('open');
	}else if(type=='itemstat'){
		$("#dialog_lookup_itemstat").dialog('open');
	}else if(type=='priceclass'){
		$("#dialog_lookup_priceclass").dialog('open');
	}else if(type=='itemtype'){
		$("#dialog_lookup_itemtype").dialog('open');
	}
}

function fncLookupSearch(type){
	if(type=='vendor'){
		var txtSearchCode = $('#txtLookVendorCode').val();
		var txtSearchName = $('#txtLookVendorDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#spinnerVendor').show();
			$('#dvSearchVendor').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'iteminquiry.php?action=do_lookup&lookup=vendor&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerVendor').hide();
					$('#dvSearchVendor').html('');
				}		
			})	
		}
		
	}else if(type=='genclass'){
		var txtSearchCode = $('#txtLookGenClassCode').val();
		var txtSearchName = $('#txtLookGenClassDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#spinnerGenClass').show();
			$('#dvSearchGenClass').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'iteminquiry.php?action=do_lookup&lookup=genclass&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerGenClass').hide();
					$('#dvSearchGenClass').html('');
				}		
			})	
		}
		
	}else if(type=='catclass'){
		var txtSearchCode = $('#txtLookCatClassCode').val();
		var txtSearchName = $('#txtLookCatClassDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#spinnerCatClass').show();
			$('#dvSearchCatClass').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'iteminquiry.php?action=do_lookup&lookup=catclass&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerCatClass').hide();
					$('#dvSearchCatClass').html('');
				}		
			})	
		}
	}else if(type=='caption'){
		var txtSearchCode = $('#txtLookCaptionCode').val();
		var txtSearchName = $('#txtLookCaptionDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#spinnerCaption').show();
			$('#dvSearchCaption').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'iteminquiry.php?action=do_lookup&lookup=caption&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerCaption').hide();
					$('#dvSearchCaption').html('');
				}		
			})	
		}
	}else if(type=='sacode'){
		var txtSearchCode = $('#txtLookSACodeCode').val();
		var txtSearchName = $('#txtLookSACodeDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#spinnerSACode').show();
			$('#dvSearchSACode').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'iteminquiry.php?action=do_lookup&lookup=sacode&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerSACode').hide();
					$('#dvSearchSACode').html('');
				}		
			})	
		}
	}else if(type=='itemstat'){
		var txtSearchCode = $('#txtLookItemStatCode').val();
		var txtSearchName = $('#txtLookItemStatDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#spinnerItemStat').show();
			$('#dvSearchItemStat').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'iteminquiry.php?action=do_lookup&lookup=itemstat&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerItemStat').hide();
					$('#dvSearchItemStat').html('');
				}		
			})	
		}
	}else if(type=='priceclass'){
		var txtSearchCode = $('#txtLookPriceClassCode').val();
		var txtSearchName = $('#txtLookPriceClassDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#spinnerPriceClass').show();
			$('#dvSearchPriceClass').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'iteminquiry.php?action=do_lookup&lookup=priceclass&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerPriceClass').hide();
					$('#dvSearchPriceClass').html('');
				}		
			})	
		}
	}else if(type=='itemtype'){
		var txtSearchCode = $('#txtLookItemTypeCode').val();
		var txtSearchName = $('#txtLookItemTypeDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#spinnerItemType').show();
			$('#dvSearchItemType').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'iteminquiry.php?action=do_lookup&lookup=itemtype&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerItemType').hide();
					$('#dvSearchItemType').html('');
				}		
			})	
		}
	}
}

function fncSelectedLookup(type,code){
	if(type=='vendor'){
		$('#txtVendorCode').val(code);
		$("#dialog_lookup_vendor").dialog('close');
		$('#divResultVendor').html('');
		$('#txtLookVendorCode').val('');
		$('#txtLookVendorDesc').val('');
	}else if(type=='genclass'){
		$('#txGenClass').val(code);
		$("#dialog_lookup_genclass").dialog('close');
		$('#divResultGenClass').html('');
		$('#txtLookGenClassCode').val('');
		$('#txtLookGenClassDesc').val('');
	}else if(type=='catclass'){
		$('#txtCatClass').val(code);
		$("#dialog_lookup_catclass").dialog('close');
		$('#divResultCatClass').html('');
		$('#txtLookCatClassCode').val('');
		$('#txtLookCatClassDesc').val('');
	}else if(type=='caption'){
		$('#txCaption').val(code);
		$("#dialog_lookup_caption").dialog('close');
		$('#divResultCaption').html('');
		$('#txtLookCaptionCode').val('');
		$('#txtLookCaptionDesc').val('');
	}else if(type=='sacode'){
		$('#txSACode').val(code);
		$("#dialog_lookup_sacode").dialog('close');
		$('#divResultSACode').html('');
		$('#txtLookSACodeCode').val('');
		$('#txtLookSACodeDesc').val('');
	}else if(type=='itemstat'){
		$('#txtItemStat').val(code);
		$("#dialog_lookup_itemstat").dialog('close');
		$('#divResultItemStat').html('');
		$('#txtLookItemStateCode').val('');
		$('#txtLookItemStatDesc').val('');
	}else if(type=='priceclass'){
		$('#txPriceClass').val(code);
		$("#dialog_lookup_priceclass").dialog('close');
		$('#divResultPriceClass').html('');
		$('#txtLookPriceClassCode').val('');
		$('#txtLookPriceClassDesc').val('');
	}else if(type=='itemtype'){
		$('#txtItemType').val(code);
		$("#dialog_lookup_itemtype").dialog('close');
		$('#divResultItemType').html('');
		$('#txtLookItemTypeCode').val('');
		$('#txtLookItemTypeDesc').val('');
	}
}

function fncView(code){
	if(code!=''){
		$.ajax({
				type: "POST",
				url: 'iteminquiry.php?action=do_view&code='+code,
				beforeSend: function()
				{
					$('#divloader').dialog("open");
				},
				success: function(html) {
					eval(html);
					$('#divloader').dialog("close");
				}		
			})	
	}
}

function fncDownload()
{
	var txtSearchItemNo = $('#txtSearchItemNo').val();
	var txtDesc = $('#txtDesc').val();
	var txtVendorCode = $('#txtVendorCode').val();
	var txGenClass = $('#txGenClass').val();
	var txtCatClass = $('#txtCatClass').val();
	var txCaption = $('#txCaption').val();
	var txtSuppCode = $('#txtSuppCode').val();
	var txSACode = $('#txSACode').val();
	var txtItemStat = $('#txtItemStat').val();
	var txPriceClass = $('#txPriceClass').val();
	var txtItemType = $('#txtItemType').val();
	var txtDateFrom = $('#txtDateFrom').val();
	var txtDateTo = $('#txtDateTo').val();
	var txtDateUpdateFrom = $('#txtDateUpdateFrom').val();
	var txtDateUpdatedTo = $('#txtDateUpdatedTo').val();

	if(txtSearchItemNo!='' || txtDesc!='' || txtVendorCode!='' || txGenClass!='' || txtCatClass!='' || txCaption!='' || txtSuppCode!='' || txSACode!='' || txtItemStat!='' || txPriceClass!='' || txtItemType!='' || (txtDateFrom!='' && txtDateTo!='') || (txtDateUpdateFrom!='' && txtDateUpdatedTo!='')){
//		$('#spinner').show();
//		$('#dvSearch').html(' S e a r c h i n g  .<blink> . . </blink>');
		
		window.open('iteminquiry_csv.php?action=do_download&txtSearchItemNo='+txtSearchItemNo+'&txtDesc='+txtDesc+'&txtVendorCode='+txtVendorCode+'&txGenClass='+txGenClass+'&txtCatClass='+txtCatClass+'&txCaption='+txCaption+'&txtSuppCode='+txtSuppCode+'&txSACode='+txSACode+'&txtItemStat='+txtItemStat+'&txPriceClass='+txPriceClass+'&txtItemType='+txtItemType+'&txtDateFrom='+txtDateFrom+'&txtDateTo='+txtDateTo+'&txtDateUpdateFrom='+txtDateUpdateFrom+'&txtDateUpdatedTo='+txtDateUpdatedTo);
				
	}else
	{		
		alert('Nothing to search!')
	}
	$('#txtSearchItemNo').select();
	
}

function TestChar(obj,e,type)
{
//	#type 1 = NUMERIC WITH PERIOD #type  2	=	NUMERIC ONLY #type  3	=	ALPHA NUMERIC
	AlphaBig 	= "65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89,90";
	AlphaSmall	=	"97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122";
	NumericCode = "48,49,50,51,52,53,54,55,56,57";
	Others		=	"8,9,13,46,32";
	OthersWithOutPeriod	=	"8,9,13";
	key = e.which;
	//alert(key)
	if(type==1){
		if (obj.value.indexOf(".")>=0){ //if period existing
			if (NumericCode.indexOf(key)<0 && OthersWithOutPeriod.indexOf(key)<0){
				return false;
			}
		}else if (obj.value.indexOf(".")<0 && NumericCode.indexOf(key)<0 && Others.indexOf(key)<0){ //if period not existing
			return false;
		}
	}else if (type==2){
		if (NumericCode.indexOf(key)<0){
			return false;
		}
	}else if(type==3){
		if (AlphaBig.indexOf(key)<0 && AlphaSmall.indexOf(key)<0 && NumericCode.indexOf(key)<0 && Others.indexOf(key)<0){
			return false;
		}
	}else if(type==4){ //ALPHACODE ONLY
		if (AlphaBig.indexOf(key)<0 && AlphaSmall.indexOf(key)<0){
			return false;
		}
	}else if(type==5){ //NUMERIC ONLY
		if(NumericCode.indexOf(key)<0){
			return false;
		}
	}
}

function decimalFormat(val){
	if(document.getElementById(val).value != ''){
		document.getElementById(val).value = stripCommas(document.getElementById(val).value);
		document.getElementById(val).value = parseFloat(document.getElementById(val).value).toFixed(2);
		numString = document.getElementById(val).value;
		
		var re = /(-?\d+)(\d{4})/;
	    while (re.test(numString)) {
	        numString = numString.replace(re, "$1,$2");
	    }
	    alert(numString);
	    document.getElementById(val).value = stripCommas(numString);
	}
}

function stripCommas(numString) {
    var re = /,/g;
    return numString.replace(re,"");
} 
