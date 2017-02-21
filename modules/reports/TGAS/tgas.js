function fncLookup(type){
	if(type=='prodgroup'){
		$("#dialog_lookup_prodgroup").dialog('open');
	}else if(type=='genclass'){
		$("#dialog_genclass").dialog('open');
	}else if(type=='itemtype'){
		$("#dialog_lookup_itemtype").dialog('open');
	}else if(type=='catclass'){
		$("#dialog_lookup_catclass").dialog('open');
	}else if(type=='priceclass'){
		$("#dialog_lookup_priceclass").dialog('open');
	}else if(type=='caption'){
		$("#dialog_lookup_caption").dialog('open');
	}else if(type=='sacode'){
		$("#dialog_lookup_sacode").dialog('open');
	}else if(type=='itemstat'){
		$("#dialog_lookup_itemstat").dialog('open');
	}else if(type=='equi'){
		$("#dialog_lookup_equi").dialog('open');
	}
}

function fncLookupSearch(type){
	if(type=='prodgroup'){
		var txtSearchCode = $('#txtLookProdGroup').val();
		var txtSearchName = $('#txtLookProdGroupDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#dvSearchProdGroup').html(' S e a r c h i n g  .<blink> . . </blink>');
			$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_lookup&lookup=prodgroup&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerVendor').hide();
					$('#dvSearchProdGroup').html('');
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
			url: 'tgas.php?action=do_lookup&lookup=genclass&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerGenClass').hide();
					$('#dvSearchGenClass').html('');
				}		
			})	
		}
		
	}else if(type=='itemtype'){
		var txtSearchCode = $('#txtLookItemTypeCode').val();
		var txtSearchName = $('#txtLookItemTypeDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#dvSearchItemType').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_lookup&lookup=itemtype&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerItemType').hide();
					$('#dvSearchItemType').html('');
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
			url: 'tgas.php?action=do_lookup&lookup=catclass&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
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
			$('#dvSearchCaption').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_lookup&lookup=caption&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerCaption').hide();
					$('#dvSearchCaption').html('');
				}		
			})	
		}
	}else if(type=='equi'){
		var txtSearchCode = $('#txtLookEquiCode').val();
		var txtSearchName = $('#txtLookEquiDesc').val();
		if(txtSearchCode!="" || txtSearchName!=""){
			$('#dvSearchEqui').html(' S e a r c h i n g  .<blink> . . </blink>');
			
			$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_lookup&lookup=equi&txtSearchCode='+txtSearchCode+'&txtSearchName='+txtSearchName,
			success: function(html) {
					eval(html);	
					$('#spinnerEqui').hide();
					$('#dvSearchEqui').html('');
				}		
			})	
		}
	}
}

function fncSelectedLookup(type,code,desc){
	if(type=='prodgroup'){
		$('#txtProdGroup').val(code);
		$("#dialog_lookup_prodgroup").dialog('close');
		$('#divResultProdGroup').html('');
		$('#txtLookProdGroup').val('');
		$('#txtLookProdGroupDesc').val('');
		fncSearchProdGrp(code);
	}else if(type=='genclass'){
		$('#txtGenClass').val(code);
		$('#txtGenClassDesc').val(desc);
		$("#dialog_genclass").dialog('close');
		$('#divResultGenClass').html('');
		$('#txtLookGenClassCode').val('');
		$('#txtLookGenClassDesc').val('');
		fncSearchGenC(code);
	}else if(type=='itemtype'){
		$('#txtType').val(code);
		$("#dialog_lookup_itemtype").dialog('close');
		$('#divResultItemType').html('');
		$('#txtLookItemTypeCode').val('');
		$('#txtLookItemTypeDesc').val('');
		fncSearchItemType(code);
	}else if(type=='catclass'){
		$('#txtCatClass').val(code);
		$("#dialog_lookup_catclass").dialog('close');
		$('#divResultCatClass').html('');
		$('#txtLookCatClassCode').val('');
		$('#txtLookCatClassDesc').val('');
		fncSearchCatClass(code);
	}else if(type=='caption'){
		$('#txtCaption').val(code);
		$("#dialog_lookup_caption").dialog('close');
		$('#divResultCaption').html('');
		$('#txtLookCaptionCode').val('');
		$('#txtLookCaptionDesc').val('');
		fncSearchCaption(code);
	}else if(type=='equi'){
		$('#txtEqui').val(code);
		$("#dialog_lookup_equi").dialog('close');
		$('#divResultEqui').html('');
		$('#txtLookEquiCode').val('');
		$('#txtLookEquiDesc').val('');
		fncSearchEqui(code);
	}
}
function fncSearchGenC(val){
	if(val!=""){
		$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_searchlookup&lookup=genc&code='+val,
			success: function(html) {
				eval(html);	
				}		
			})	
	}else{
		$('#txtGenClassDesc').val('');
	}
}
function fncSearchProdGrp(val){
	if(val!=""){
		$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_searchlookup&lookup=prodgrp&code='+val,
			success: function(html) {
				eval(html);	
				}		
			})	
	}else{
		$('#txtProdGroupDesc').val('');
	}
}
function fncSearchItemType(val){
	if(val!=""){
		$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_searchlookup&lookup=itemtype&code='+val,
			success: function(html) {
				eval(html);	
				}		
			})	
	}else{
		$('#txtTypeDesc').val('');
	}
}
function fncSearchCatClass(val){
	if(val!=""){
		$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_searchlookup&lookup=catclass&code='+val,
			success: function(html) {
				eval(html);	
				}		
			})	
	}else{
		$('#txtCatClassDesc').val('');
	}
}
function fncSearchCaption(val){
	if(val!=""){
		$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_searchlookup&lookup=caption&code='+val,
			success: function(html) {
				eval(html);	
				}		
			})	
	}else{
		$('#txtCaptionDesc').val('');
	}
}
function fncSearchEqui(val){
	if(val!=""){
		$.ajax({
			type: "POST",
			url: 'tgas.php?action=do_searchlookup&lookup=equi&code='+val,
			success: function(html) {
				eval(html);	
				}		
			})	
	}else{
		$('#txtEquiDesc').val('');
	}
}

function fncDownload()
{
	var txtGenClass		= $('#txtGenClass').val();
	var txtProdGroup	= $('#txtProdGroup').val();
	var txtType			= $('#txtType').val();
	var txtCatClass		= $('#txtCatClass').val();
	var txtCaption		= $('#txtCaption').val();
	var txtEqui			= $('#txtEqui').val();
	var selStat			= $('#selStat').val();
	
	if(txtGenClass!='' || txtProdGroup!='' || txtType!='' || txtCatClass!='' || txtCaption!='' || txtEqui!='' || selStat!='')
	{
		window.open("tgas_csv.php?txtGenClass="+txtGenClass+"&txtProdGroup="+txtProdGroup+"&txtType="+txtType+"&txtCatClass="+txtCatClass+"&txtCaption="+txtCaption+"&txtEqui="+txtEqui+"&selStat="+selStat);
	}
	else
	{
		alert("Nothing to Download");
	}
//	alert("txtGenClass="+txtGenClass+"\ntxtProdGroup="+txtProdGroup+"\ntxtType="+txtType+"\ntxtCatClass="+txtCatClass+"\ntxtCaption="+txtCaption+"\ntxtEqui="+txtEqui+"\nselStat="+selStat);
}
