function Mto_issuance_pcw(){}
Mto_issuance_pcw.prototype	=	{
	constructor:Mto_issuance_pcw,
	
	getMTO:function()
	{
		var frmasearch	=	$("#frmasearch").serialize();
		$.ajax({
				data		:frmasearch,
				type		:"POST",	
				url			:"issuance.php?action=GETMTO",
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divMTO").html(response);
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter({
						sortList: [[0,0]],
					 	headers: { 8: { sorter: false } }
					});
					$(".tooltips").tooltip();
					$(".btntransmit").button({icons: {primary: "ui-icon ui-icon-check"}});
				}
			});
	},
	issueMTO:function(MTONO)
	{
		$("#divpcw_ars").data("MTONO",MTONO).dialog("open");
	},
	goIssueMTO:function(MTONO)
	{
		var pcw_add	=	$("#selpcw_add").val();
		var ars_add	=	$("#txtars_add").val();
		$.ajax({
				url			:"issuance.php?action=ISSUEMTO&MTONO="+MTONO+"&PCW="+pcw_add+"&ARS="+ars_add,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
					$("#selpcw_add").val("");
					$("#txtars_add").val("");
				}
			});
	}
}
var Mto_issuance_pcw_funcs	=	new Mto_issuance_pcw();
$("document").ready(function(){
	$("#btnsearch").click(function(){
		var txtmtono 	= $("#txtmtono").val();
		var selstatus 	= $("#selstatus").val();
		var selDtype 	= $("#selDtype").val();
		var txtfrom 	= $("#txtfrom").val();
		var txtto 		= $("#txtto").val();
		if((txtto != "" && txtfrom == "") || txtfrom > txtto)
		{
			MessageType.infoMsg("Invalid date range.");
		}
		else
		{
			Mto_issuance_pcw_funcs.getMTO();
		}
	});
	$(".searchpcw").keyup(function(evt){
		var txtpcwno	=	$('#txtpcwno').val();
		var txtpcwdesc	=	$('#txtpcwdesc').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(txtpcwno != '' || txtpcwdesc!= '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
					url			:	'issuance.php?action=Q_SEARCHPCW&PCWNO='+txtpcwno+'&PCWDESC='+txtpcwdesc,
					beforeSend	:	function()
								{
								},
					success		:	function(response)
								{
									if(response == '')
									{
										MessageType.infoMsg('Pieceworker not found.');
										$(".searchpcw").val("");
										$('#divselpcw').html('');
									}
									else
									{
										$('#divselpcw').html(response);
										var position =$("#txtpcwno").position();
										var selwidth	=	$("#txtpcwno").width() + $("#txtpcwdesc").width()+12;
										$("#divselpcw").css({ position:'absolute'});
										$('#divselpcw').show();
										$('#selpcw').css({width:selwidth});
									}
								}
				});
			}
			else if(evthandler == 40 && $('#divselpcw').html() != '')
			{
				$('#selpcw').focus();
			}
			else
			{
				$('#divselpcw').html('');
			}
		}
		else
		{
			$('#divselpcw').html('');
			$('#divpcw').html('');
		}
	});	
	$("#divMTO").on("click",".issuebtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		Mto_issuance_pcw_funcs.issueMTO(mtono);
	});
	$("#divMTO").on("click",".documentbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		window.open("issuance_PDF.php?MTONO="+mtono);
		$("#btnsearch").trigger("click");
	});
	Mto_issuance_pcw_funcs.getMTO();
});
function smartselpcw(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnvalpcw').val($('#selpcw').val());
		var vx = $('#hdnvalpcw').val();
		var x = vx.split('|'); 
		$('#txtpcwno').val(x[0]);
		$('#txtpcwdesc').val(x[1]);
		$('#divselpcw').html('');
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnvalpcw').val($('#selpcw').val());
			var vx = $('#hdnvalpcw').val();
			var x = vx.split('|'); 
			$('#txtpcwno').val(x[0]);
			$('#txtpcwdesc').val(x[1]);
			$('#divselpcw').html('');
		}
	}
}