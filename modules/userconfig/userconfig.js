$("document").ready(function(){
	$("#btnsearch").click(function(){
		var txtname		=	$("#txtUname").val();
		var selstatus	=	$("#selUstatus").val();
		$.ajax({
			url			:"userconfig.php?action=GETUSERS&USER="+txtname+"&STATUS="+selstatus,
			beforeSend	:function()
			{
				$("#divloader").dialog("open");
			},
			success		:function(response)
			{
				$("#divuserlist").html(response);
				$("#divloader").dialog("close");
				$(".action_butt").tooltip();
				$(".tablesorter").tablesorter({
					sortList: [[1,0]],
				 	headers: { 5: { sorter: false } }
				}); 
				$('.tablesorter').paging({limit:15});
			}
		});
	});
	$("#btncreate").click(function(){
		$("span.ui-dialog-title").text('Create New User'); 
		$(".C_divusers .ui-button-text:contains(Update)").text("Save");
		$("#divusers").dialog("open");
	});
	$("tr td").on("click",".btnedit",function(){
		var userid	=	$(this).attr("data-userid");
		$("span.ui-dialog-title").text('Edit User'); 
		$(".C_divusers .ui-button-text:contains(Save)").text("Update");
		$("#divusers").dialog("open");
		
		$.ajax({
			url			:"userconfig.php?action=EDITUSERS&USERID="+userid,
			beforeSend	:function()
			{
				$("#divloader").dialog("open");
			},
			success		:function(response)
			{
				$("#divdebug").html(response);
				$("#divloader").dialog("close");
			}
		});
	});
	$("#txtusername").change(function(){
		if(this.value != "")
		{
			$.ajax({
				url			:"userconfig.php?action=CHKUSERNAME&USERNAME="+this.value,
				beforeSend	:function()
				{
					$("#divloader").dialog("open");
				},
				success		:function(response)
				{
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
		}
	});
	$("#txtcpassword").change(function(){
		if(this.value != $("#txtpassword").val())
		{
			$("#txtinfomsg").text("Passwords did not match.");
			$("#divinfomsg").dialog("open");
			$(this).val("");
		}
	});
	$("tr td").on("click",".btnmenu",function(){
	var userid	=	$(this).attr("data-userid");
	$.ajax({
		url			:	'userconfig.php?action=GETMODULES&USERID='+userid,
		beforeSend	:	function()
					{
						$('#divloader').dialog("open");
					},
		success		:	function(response)
					{
						$("#divEmodules").html(response);
						$("#divEmodules").dialog("open");
						$('#divloader').dialog("close");
						$("#menutab").tabs();
					}
		});
});
	$("#btnsearch").trigger("click");
});
function cancel()
{
	$('#frmuser *').filter(':input').each(function(){
		{
	    	$(this).val("");
	    	$(this).removeClass("errpurpose");
		}
	});
}
function validate(updatemode)
{
	var valid	=	true;
	if($("#txtname").val() == "")		{	$("#txtname").addClass("errpurpose"); valid = false;		}else{	$("#txtname").removeClass("errpurpose");}
	if($("#txtusername").val() == "")	{	$("#txtusername").addClass("errpurpose"); valid = false;	}else{	$("#txtusername").removeClass("errpurpose");}
	if($("#seldep").val() == "")		{	$("#seldep").addClass("errpurpose"); valid = false;			}else{	$("#seldep").removeClass("errpurpose");}
	if($("#sellevel").val() == "")		{	$("#sellevel").addClass("errpurpose"); valid = false;		}else{	$("#sellevel").removeClass("errpurpose");}
	if($("#selstatus").val() == "")		{	$("#selstatus").addClass("errpurpose"); valid = false;		}else{	$("#selstatus").removeClass("errpurpose");}
	
	if($("#txtuserid").val() == "")
	{
		if($("#txtpassword").val() == "")	{	$("#txtpassword").addClass("errpurpose"); valid = false;	}else{	$("#txtpassword").removeClass("errpurpose");}
		if($("#txtcpassword").val() == "")	{	$("#txtcpassword").addClass("errpurpose"); valid = false;	}else{	$("#txtcpassword").removeClass("errpurpose");}
	}
	return valid;
}
function toggleG(classG)
{
	var arrClasses	=	classG.split(" ");
	var arrLength	=	arrClasses.length;
	var x;
	var checkall	=	true;
	var classname;
	for(x=0; x<arrLength; x++)
	{
		classname	=	arrClasses[x];
		checkall	=	true;
		$( "."+classname ).each(function( index )
		{
			if(checkall)
			{
				$("#"+classname).prop("checked", true);
			}
			else
			{
				$("#"+classname).prop("checked", false);
			}
		});
	}
}
function toggleD(classG)
{
	if($("#"+classG).is(':checked'))
	{
		$("."+classG).prop("checked", true);
	}
	else
	{
		$("."+classG).prop("checked", false);
	}
}
function validateEmodules()
{
	var notempty	=	false;
	$('#frmmodules input[type="checkbox"]').each(function(){
   	 	if($(this).is(":checked"))
   	 	{
   	 		notempty	=	true;
   	 	}
	});
	if(notempty == false)
	{
		$("#txtinfomsg").text("Please select module/modules.");
		$("#divinfomsg").dialog("open");
		return false;
	}
	else
	{
		return true;
	}
}