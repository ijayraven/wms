function search(page)
{
	var selField	= $('#selField').val();
	
	var txtSearch	= $('#txtSearch').val();
	
//	if(txtSearch!='')
//	{
		$('#searcherror').hide('slow');
		$.ajax({
			type: "POST",
			url: 'userconfig.php?action=do_search&txtSearch='+txtSearch+'&selField='+selField+'&pageno='+page,
			success: function(html) {
//						alert(html);return;	
//						eval(html);	
					$('#divResult').html(html);
	
				}		
			})	
//	}
//	else
//	{
//		$('#txtSearch').val('');
//		$('#searcherror').show('fast');
//		$('#txtSearch').focus();
//	}
}
function addUser()
{
	$('#dialog_add').dialog('open');
	$.ajax({
	type: "POST",
	url: "userconfig.php?action=do_gen_transno",
	success: function(html) {
//			alert(html);return;			
			eval(html);			
//			$('#dialog_liqui').dialog('open');
//			$('#dialog_liqui').data('stfno',stfno);
//			$('#dialog_liqui').data('hidcust',hidcust);
		}		
	});
}
function disable()
{
	$('#txtPass').attr('disabled','disabled');
	$('#txtPass2').focus();
}
function retypepass()
{
	var pass1	= $('#txtPass').val();
	var pass2	= $('#txtPass2').val();
	
	if(pass1!='' && pass2!='')
	{
		if(pass1 != pass2)
		{
			$('#txtPass2').val('');
			$('#passerror').show();
			$('#txtPass2').focus();
		}
		else
		{
			$('#passerror').hide();
			$('#txtPass').removeAttr("disabled")
		}
	}
	else
	{
		alert("Check Password");
	}
}
function fncEdit(id)
{
			
	$('#dialog_edit').dialog('open');
	$.ajax({
			type: "POST",
			url: 'userconfig.php?action=do_edit&id='+id,
			success: function(html) {
//						alert(html);return;	
						eval(html);
//					$('#divResult').html(html);
					search();
	
				}		
			})	
}
function accessmodule()
{
	$('#dialog_module').dialog('open');
}
function accessmodule2()
{
	$('#dialog_moduleEdit').dialog('open');
}
function showDtls(id)
{
//	$('#dialog_view').dialog('open');
//	$('#dialog_view').html(id);
}
function fncCancel(id)
{
	if(id!='')
	{
		$('#dialog_ok_cancel').dialog('open');
		$('#dialog_ok_cancel').html("Are you sure you want to Cancel User "+id+"?");
		$('#dialog_ok_cancel').data('id',id);
	}
}