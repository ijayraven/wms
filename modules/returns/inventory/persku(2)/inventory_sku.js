$("document").ready(function(){
	$(".searchitem").keyup(function(evt){
		var txtitemno	=	$('#txtitemno').val();
		var txtitemdesc	=	$('#txtitemdesc').val();
		var selbrand	=	$('#selbrand').val();
		var selclass	=	$('#selclass').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(txtitemno != '' || txtitemdesc!= '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'inventory_sku.php?action=Q_SEARCHITEM&ITEMNO='+txtitemno+'&ITEMDESC='+txtitemdesc,
						beforeSend	:	function()
									{
									},
						success		:	function(response)
									{
										if(response == '')
										{
											$('#divselitem').html('');
										}
										else
										{
											$('#divselitem').html(response);
											var position =$("#txtitemno").position();
											var selwidth	=	$("#txtitemno").width() + $("#txtitemdesc").width()+12;
											$("#divselitem").css({ position:'absolute'});
											$('#divselitem').show();
											$('#selitem').css({width:selwidth});
										}
									}
				});
			}
			else if(evthandler == 40 && $('#divselitem').html() != '')
			{
				$('#selitem').focus();
			}
			else
			{
				$('#divselitem').html('');
			}
		}
		else
		{
			$('#divselitem').html('');
			$('#divitem').html('');
		}
	});
	$(".trscanning").click(function(){
		var itemno		=	$("#lblitemno1").text();
		var mposlist	=	$("#hdnscannedmpos").val();
		if(mposlist != "")
		{
			if($('#tdSdtls').text() != "")
			{
				$('#trSdtls').hide("blind");
				$('#tdSdtls').text("");
			}
			else
			{
				$.ajax({
						url			:	'inventory_sku.php?action=GETSCANNEDDTLS&ITEMNO='+itemno+"&MPOSLIST="+mposlist,
						beforeSend	:	function()
									{
										$("#divloader").dialog("open");
									},
						success		:	function(response)
									{
										$('#tdSdtls').html(response);
										$("#divloader").dialog("close");
									}
				});
				
				$('#trSdtls').show();
				$('#tdSdtls').show("blind");
			}
		}
	});
	$(".trmto").click(function(){
		var itemno		=	$("#lblitemno1").text();
		var mposlist	=	$("#hdnmtompos").val();
		if(mposlist != "")
		{
			if($('#tdMdtls').text() != "")
			{
				$('#trMdtls').hide("blind");
				$('#tdMdtls').text("");
			}
			else
			{
				$.ajax({
						url			:	'inventory_sku.php?action=GETSMTODTLS&ITEMNO='+itemno+"&MPOSLIST="+mposlist,
						beforeSend	:	function()
									{
										$("#divloader").dialog("open");
									},
						success		:	function(response)
									{
										$('#tdMdtls').html(response);
										$("#divloader").dialog("close");
									}
				});
				$('#trMdtls').show("blind");
			}
		}
	});
});
function smartselitem(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnvalitem').val($('#selitem').val());
		var vx = $('#hdnvalitem').val();
		var x = vx.split('|'); 
		$('#txtitemno').val(x[0]);
		$('#txtitemdesc').val(x[1]);
		$('#divselitem').html('');
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnvalitem').val($('#selitem').val());
			var vx = $('#hdnvalitem').val();
			var x = vx.split('|'); 
			$('#txtitemno').val(x[0]);
			$('#txtitemdesc').val(x[1]);
			$('#divselitem').html('');
		}
	}
	$(".lblitemno").text(x[0]);
	$(".lblitemdesc").text(x[1]);
	getItemdtls(x[0]);
}
function getItemdtls(itemno)
{
	$.ajax({
			url			:	'inventory_sku.php?action=GETITEMDTLS&ITEMNO='+itemno,
			beforeSend	:	function()
						{
							$("#divloader").dialog("open");
						},
			success		:	function(response)
						{
							$('#divdebug').html(response);
							$("#divloader").dialog("close");
						}
	});
}