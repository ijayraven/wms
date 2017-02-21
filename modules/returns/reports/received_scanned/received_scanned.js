$("document").ready(function(){
	$(".searchcust").keyup(function(evt){
		var txtcustno	=	$('#txtcustno').val();
		var txtcustname	=	$('#txtcustname').val();
		var custtype	=	$('#selcusttype').val();
		var custcusttype=	$('input[name=rdcusttype]:checked', '#dataform').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'received_scanned.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname+'&selcusttype='+custtype+"&custcusttype="+custcusttype,
						beforeSend	:	function()
									{
									},
						success		:	function(response)
									{
										if(response == '')
										{
											MessageType.infoMsg('Customer not found.');
											$('#divselcust').html('');
											$(".searchcust").val("");
										}
										else
										{
											$('#divselcust').html(response);
											var position =$("#txtcustno").position();
											var selwidth	=	$("#txtcustno").width() + $("#txtcustname").width()+12;
											$("#divselcust").css({ position:'absolute'});
											$('#divselcust').show();
											$('#selcust').css({width:selwidth});
										}
									}
				});
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
	$("#btnsearch").click(function(){
		var errmsg		=	"";
		var dataform	=	$("#dataform").serialize();
		var errstatus	=	"";
		if($("#mposdfrom").val() == "" && $("#mposdto").val() == "" && $("#rcvddfrom").val() == "" &&  $("#rcvddto").val() == "" && $("#scandfrom").val() == "" &&  $("#scandto").val() == "")
		{
			errmsg	+=	" - Please input at least one date range.<br>";
		}
		if($("#mposdfrom").val() > $("#mposdto").val())
		{
			errmsg	+=	" - Invalid MPOS date range.<br>";
		}
		if($("#rcvddfrom").val() > $("#rcvddto").val())
		{
			errmsg	+=	" - Invalid Received date range.<br>";
		}
		if($("#scandfrom").val() > $("#scandto").val())
		{
			errmsg	+=	" - Invalid Scanned date range.<br>";
		}
		if($("#selmpostype").val() == "")
		{
			
		}
		if(errmsg == "" && errstatus == "")
		{
			$.ajax({
				type:	"POST",
				data:	dataform,
				url:	"received_scanned.php?action=GETMPOS",
				beforeSend:function(){
					$("#divloader").dialog("open");
				},
				success:function(response){
					$("#divMPOS").html(response);
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter();
					$(".btnprint").button({icons: {primary: "ui-icon ui-icon-print"}});
				}
			});
		}
		else
		{
			MessageType.infoMsg(errmsg+errstatus);
		}
	});
	$("#selcusttype").change(function(){
		var custcusttype=	$('input[name=rdcusttype]:checked', '#dataform').val();
		if($(this).val() != "ALL" || custcusttype != undefined)
		{
			$("#txtcustno").removeAttr("disabled"); 
			$("#txtcustname").removeAttr("disabled"); 
		}
		else
		{
			$("#txtcustno").attr("disabled","disabled");
			$("#txtcustname").attr("disabled","disabled");
			$("#txtcustno").val("");
			$("#txtcustname").val("");
		}
	});
	$("#rdonbs, #rdotrade").click(function(){
		$("#txtcustno").removeAttr("disabled"); 
		$("#txtcustname").removeAttr("disabled"); 
	});
	$("#chkscanned").click(function(){
		 if((this.checked))
		 {
		 	$("#scandfrom").removeAttr("disabled"); 
			$("#scandto").removeAttr("disabled"); 
		 }
		 else
		 {
		 	$("#scandfrom").attr("disabled","disabled");
			$("#scandto").attr("disabled","disabled");
		 	$("#scandfrom").val("");
			$("#scandto").val("");
		 }
	});
});
function	smartsel(evt)
{
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
}
function print()
{
	window.open("received_scanned_pdf.php");
}
function print_csv()
{
	window.open("received_scanned_csv.php");
}