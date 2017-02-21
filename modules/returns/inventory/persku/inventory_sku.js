$("document").ready(function(){
	$(".searchitem").keyup(function(evt){
		var txtitemno	=	$('#txtitemno').val();
		var txtitemdesc	=	$('#txtitemdesc').val();
		var selbrand	=	$('#selbrand').val();
		var selclass	=	$('#selclass').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
//		$(".tddtls").text("");
//		$(".tddtls").css("display","none");
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
											MessageType.infoMsg('Item not found.');
											$(".searchitem").val("");
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
	$("#divitemdtls").on("click",".trscanning",function(){
		var itemno		=	$("#lblitemno1").text();
		var mposlist	=	$("#hdnscannedmpos").val();
		var is_exclusive=	$("#hdnis_exclusive").val();
		if(is_exclusive == "NO")
		{
			if(mposlist != "")
			{
				if($('#tdSdtls').text() != "")
				{
					$('#trSdtls').hide();
					$('#tdSdtls').text("");
					$(this).removeClass("activetr");
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
											$(".trscanning").addClass("activetr");
										}
					});
					
					$('#trSdtls').show();
					$(this).addClass("dtlsclicked");
				}
			}
		}
	});
	$("#divitemdtls").on("click",".tripm",function(){
		var itemno		=	$("#lblitemno1").text();
	
		var tdIqty	=	$("#tdIqty").text();
		if(tdIqty != "0")
		{
			if($('#tdIdtls').text() != "")
			{
				$('#trIdtls').hide();
				$('#tdIdtls').text("");
				$(this).removeClass("activetr");
			}
			else
			{
				$.ajax({
						url			:	'inventory_sku.php?action=GETIMTODTLS&ITEMNO='+itemno,
						beforeSend	:	function()
									{
										$("#divloader").dialog("open");
									},
						success		:	function(response)
									{
										$('#tdIdtls').html(response);
										$("#divloader").dialog("close");
										$(".tripm").addClass("activetr");
									}
				});
				$(this).addClass("dtlsclicked");
				$('#trIdtls').show();
			}
		}
	});
	$("#divitemdtls").on("click",".trmto",function(){
		var itemno		=	$("#lblitemno1").text();
		var mposlist	=	$("#hdnmtompos").val();
		var is_exclusive=	$("#hdnis_exclusive").val();
		if(is_exclusive == "NO")
		{
			if(mposlist != "")
			{
				if($('#tdMdtls').text() != "")
				{
					$('#trMdtls').hide();
					$('#tdMdtls').text("");
					$(this).removeClass("activetr");
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
											$(".trmto").addClass("activetr");
										}
					});
					$(this).addClass("dtlsclicked");
					$('#trMdtls').show();
				}
			}
		}
		else
		{
			var tdMqty	=	$("#tdMqty").text();
			if(tdMqty != "0")
			{
				if($('#tdMdtls').text() != "")
				{
					$('#trMdtls').hide();
					$('#tdMdtls').text("");
					$(this).removeClass("activetr");
				}
				else
				{
					$.ajax({
							url			:	'inventory_sku.php?action=GETSMTODTLS&ITEMNO='+itemno+"&IS_EXCLUSIVE=YES",
							beforeSend	:	function()
										{
											$("#divloader").dialog("open");
										},
							success		:	function(response)
										{
											$('#tdMdtls').html(response);
											$("#divloader").dialog("close");
											$(".trmto").addClass("activetr");
										}
					});
					$(this).addClass("dtlsclicked");
					$('#trMdtls').show();
				}
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
							$('#divitemdtls').html(response);
							$("#divloader").dialog("close");
						}
	});
}