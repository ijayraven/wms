function Mto_receiving(){}
	Mto_receiving.prototype	=	{
		constructor:Mto_receiving,
		
		getMTO:function()
		{
			var frmasearch	=	$("#frmasearch").serialize();
			$.ajax({
					data		:frmasearch,
					type		:"POST",	
					url			:"receiving.php?action=GETMTO",
					beforeSend	:function(){
						$("#divloader").dialog("open");
					},
					success		:function(response){
						$("#divMTO").html(response);
						$("#divloader").dialog("close");
						$(".tablesorter").tablesorter({
							sortList: [[0,0]],
						 	headers: { 7: { sorter: false } }
						});
						$(".btntransmit").button({icons: {primary: "ui-icon ui-icon-check"}});
					}
				});
		},
		receiveMTO:function()
		{
			var frmchk	=	$("#frmchk").serialize();
			$.ajax({
				data		:frmchk,
				type		:"POST",
				url			:"receiving.php?action=RECEIVEMTO",
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
	}
var Mto_receiving_funcs	=	new Mto_receiving();
$("document").ready(function(){
	$("#btnsearch").click(function(){
		var txtmtono 	= $("#txtmtono").val();
		var selstatus 	= $("#selstatus").val();
		var selDtype 	= $("#selDtype").val();
		var txtfrom 	= $("#txtfrom").val();
		var txtto 		= $("#txtto").val();
//		if(txtmtono == "" && selstatus == "" && txtfrom == "" && txtto == "")
//		{
//			MessageType.infoMsg("Please add at least one criterion to search.");
//		}
//		else
//		{
			if((txtto != "" && txtfrom == "") || txtfrom > txtto)
			{
				MessageType.infoMsg("Invalid date range.");
			}
			else
			{
				Mto_receiving_funcs.getMTO();
			}
//		}
	});
	$("#divMTO").on("click",".btntransmit",function(){
		var frmchk	=	$('#frmchk').serialize();
		var selected	=	false;
		$('.chkmpos').each(function () 
		{
        	if($(this).is(":checked"))
        	{
        		selected	=	true;
        	}
		});
		if(!selected)
		{
			MessageType.infoMsg("No MTO selected to receive.");
		}
		else
		{
			MessageType.confirmmsg(Mto_receiving_funcs.receiveMTO,"Do you want to receive the selected MTO?","");
		}
	});
	Mto_receiving_funcs.getMTO();
});