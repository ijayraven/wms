function Mpos_receiving(){}
Mpos_receiving.prototype = {
	constructor:Mpos_receiving,
	
	searchcust:function(txtcustno,txtcustname,custcusttype){
		$.ajax({
					url			:	'receiving.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname+"&CUSTCUSTTYPE="+custcusttype,
					success		:	function(response)
								{
									if(response == '')
									{
										$('#divselcust').html('');
										$(".searchcust").val("");
										MessageType.infoMsg("Customer not found.");
									}
									else
									{
										$('#divselcust').html(response);
										var position 	=	$("#txtcustno").position();
										var selwidth	=	$("#txtcustno").width() + $("#txtcustname").width()+12;
										$("#divselcust").css({left: position.left, position:'absolute'});
										$('#divselcust').show();
										$('#selcust').css({width:selwidth});
									}
								}
			});
	},
	getSelCust:function(evt){
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		if(evt == 'click')
		{
			$('#hdnval').val($('#selcust').val());
			var vx = $('#hdnval').val();
			var x = vx.split('|'); 
			$('#txtcustno').val(x[0]);
			$('#txtcustname').val(x[1]);
			$('#divselcust').html('');
		}
		else
		{
			if(evthandler == 13)
			{
				$('#hdnval').val($('#selcust').val());
				var vx = $('#hdnval').val();
				var x = vx.split('|'); 
				$('#txtcustno').val(x[0]);
				$('#txtcustname').val(x[1]);
				$('#divselcust').html('');
			}
		}
	},
	getMposList:function()
	{
		var frmsearch	=	$("#frmsearch").serialize();
		$.ajax({
			data		:frmsearch,
			type		:"POST",
			url			:"receiving.php?action=GETMPOSLIST",
			beforeSend	:function()
			{
				$("#divloader").dialog("open");
			},
			success		:function(response)
			{
				$("#divmposlist").html(response);
				$("#divloader").dialog("close");
				$(".tablesorter").tablesorter({
					sortList: [[0,0]],
				 	headers: { 7: { sorter: false } }
				});
				$(".btntransmit").button({icons: {primary: "ui-icon ui-icon-check"}});
			}
		});
	},
	receiveMPOS:function()
	{
		var frmchk	=	$("#frmchk").serialize();
		$.ajax({
			data		:frmchk,
			type		:"POST",
			url			:"receiving.php?action=RECEIVEMPOS"	,
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
var Receiving_funcs	=	new Mpos_receiving;

$("document").ready(function(){
	$(".searchcust").keyup(function(evt){
		var txtcustno	=	$('#txtcustno').val();
		var txtcustname	=	$('#txtcustname').val();
		var custcusttype=	$('input[name=rdocusttype]:checked', '#frmsearch').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				Receiving_funcs.searchcust(txtcustno,txtcustname,custcusttype);
			}
			else if(evthandler == 40 && $('#divselcust').html() != '')
			{
				$('#selcust').focus();
			}
			else
			{
				$('#divselcust').html('');
			}
		}
		else
		{
			$('#divselcust').html('');
		}
	});
	$("#divselcust").on("keypress","#selcust",function(evt){
		Receiving_funcs.getSelCust(evt);		
	});
	$("#divselcust").on("click","#selcust option",function(){
		Receiving_funcs.getSelCust("click");		
	});
	$(".btnsearch").click(function(){
		var txtmposno	=	$("#txtmposno").val();
		var custcusttype=	$('input[name=rdocusttype]:checked', '#frmsearch').val();
		var txtcustno	=	$("#txtcustno").val();
		var selmpostype	=	$("#selmpostype").val();
		var selDtype	=	$("#selDtype").val();
		var txtdfrom	=	$("#txtdfrom").val();
		var txtdto		=	$("#txtdto").val();
		var valid		=	true;
		if(txtmposno == "" && custcusttype == undefined && txtcustno == "" && selmpostype == "" && selDtype == "" && txtdfrom == "" && txtdto == "")
		{
			MessageType.infoMsg("Please select at least one criterion to search.");
			valid = false;
		}
		else
		{
			if(txtmposno == "")
			{
				if(selDtype != "" && txtdfrom == "" && txtdto == "")
				{
					MessageType.infoMsg("Please specify date range.");
					valid = false;
				}
				else
				{
					if(txtdfrom > txtdto || txtdto != "" && txtdfrom == "")
					{
						MessageType.infoMsg("Please specify a valid date range.");
						valid = false;
					}
					else
					{
						if(selDtype == "")
						{
							MessageType.infoMsg("Please specify a date type.");
							valid = false;
						}
					}
				}
			}
		}
		if(valid)
		{
			Receiving_funcs.getMposList();
		}
		
	});
	$("#divmposlist").on("click",".btntransmit",function(){
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
			MessageType.infoMsg("No MPOS selected to receive.");
		}
		else
		{
			MessageType.confirmmsg(Receiving_funcs.receiveMPOS,"Do you want to receive the selected MPOS?","");
		}
	});
});