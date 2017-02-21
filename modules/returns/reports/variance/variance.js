$("document").ready(function(){
	$("#btnreport").click(function(event,mainquery){
		var errmsg		=	"";
		var dataform	=	$("#dataform").serialize();
		if($("#txtmposno").val() == "" && $("#txtcustno").val() == "")
		{
			
			if($("#dfrom").val() > $("#dto").val() || ($("#dfrom").val() == "" && $("#dto").val() != "") || ($("#dfrom").val() == "" && $("#dto").val() == ""))
			{
				errmsg	+=	"Invalid date range.\n";
			}
			else
			{
				if($("#seldtype").val() == "")
				{
					errmsg	+=	"Please select date type.\n";
				}
			}
			
		}
		if(errmsg == "")
		{
			$.ajax({
				type:	"POST",
				data:	dataform,
				url:	"variance.php?action=GETMPOS",
				beforeSend:function(){
					$("#divloader").dialog("open");
				},
				success:function(response){
					$("#divMPOS").html(response);
					$("#divloader").dialog("close");
					$(".tdmposdtlsClass").hide();
					$(".btncsv").button({icons: {primary: "ui-icon ui-icon-note"}});
					$(".btnpdf").button({icons: {primary: "ui-icon ui-icon-document"}});
				}
			});
		}
		else
		{
			MessageType.infoMsg(errmsg);
		}
	});
	$("#divMPOS").on("click",".trdtls",function(){
		var MPOSNO	=	$(this).attr("data-mposno");
		var COUNT	=	$(this).attr("data-cnt");
		var tdtext	=	$("#tdmposdtls"+COUNT).html();
			tdtext	=	tdtext.trim();
		if(tdtext == "")
		{	
		    $.ajax({
				type	:	"GET",
				url		:	"variance.php?action=VIEWMPOSDTLS&MPOSNO="+MPOSNO+"&COUNT="+COUNT,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					
					$(".tdmposdtlsClass").html("");
					$("#tdmposdtls"+COUNT).html(response);
					$(".tdmposdtlsClass").hide();
					$("#tdmposdtls"+COUNT).show();
					$(".trdtls").removeClass("activetr");
					$("#trdtls"+COUNT).addClass("activetr");
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter();
				}
			});
		}
		else
		{
			$(".tdmposdtlsClass").hide();
			$("#trdtls"+COUNT).removeClass("activetr");
			$("#tdmposdtls"+COUNT).html("");
		}
	});
	$("#divMPOS").on("click","#btncsv",function(){
		window.open("variance_csv.php");
	});
	$("#divMPOS").on("click","#btnpdf",function(){
		window.open("variance_pdf.php");
	});
	$(".searchcust").keyup(function(evt){
		var txtcustno	=	$('#txtcustno').val();
		var txtcustname	=	$('#txtcustname').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'variance.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname,
						beforeSend	:	function()
									{
									},
						success		:	function(response)
									{
										if(response == '')
										{
											$('#divselcust').html('');
										}
										else
										{
											$('#divselcust').html(response);
											var position =$("#txtcustno").position();
											var selwidth	=	$("#txtcustno").width() + $("#txtcustname").width()+12;
											$("#divselcust").css({position:'absolute'});
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
});
function smartsel(evt)
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