function Mto_issuance_fb(){}
Mto_issuance_fb.prototype	=	{
	constructor:Mto_issuance_fb,
	
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
					 	headers: { 6: { sorter: false } }
					});
					$(".tooltips").tooltip();
					$(".btntransmit").button({icons: {primary: "ui-icon ui-icon-check"}});
				}
			});
	},
	issueMTO:function(MTONO)
	{
		$.ajax({
				url			:"issuance.php?action=ISSUEMTO&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
	},
	getDetails:function(MTONO)
	{
		$.ajax({
				url			:"issuance.php?action=GETMTODTLS&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divmtodtls").html(response);
					$("#divloader").dialog("close");
					$("#divmtodtls").dialog("open");
					$(".tooltips").tooltip();
					$(".tablesorter").dragtable({
						excludeFooter:true,
						dragaccept:'.tdaccept',
						dragHandle:'.some-handle'
					});
					$(".tablesorter").tablesorter({
						sortList: [[0,0]],
					 	headers: { 
					 				5: { sorter: false },
					 				6: { sorter: false },
					 				7: { sorter: false },
					 				8: { sorter: false }
					 			}
					});
				}
			});
	},
	validate:function(){
		var totcnt		=	$("#tdtotqty").attr("data-totcnt");
		var a			=	1;
		var recqty 		=	0;
		var goodqty		=	0;
		var defqty 		=	0;
		var errmsg 		=	"";
		var errmsgR		=	"";
		var errmsgS		=	"";
		for(a; a < totcnt; a++)
		{
			recqty	=	+ $("#txtrecqty"+a).val();
//			if(recqty != 0)
//			{
				goodqty	=	+ $("#txtgoodqty"+a).val();
				defqty	=	+ $("#txtdefqty"+a).val();
				if((goodqty+defqty) != recqty)
				{
					$("#txtgoodqty"+a).addClass("err-background");
					$("#txtdefqty"+a).addClass("err-background");
					errmsgS	=	" - The sum of good quantity and defective quantity must be equal to the received quantity.<br>";
				}
				else
				{
					$("#txtgoodqty"+a).removeClass("err-background");
					$("#txtdefqty"+a).removeClass("err-background");
				}
				$("#txtrecqty"+a).removeClass("err-background");
//			}
//			else
//			{
//				$("#txtrecqty"+a).addClass("err-background");
//				errmsgR		=	" - Received quantity must not be zero(0).<br>";
//			}	
		}
		errmsg	=	errmsgR + errmsgS
		return errmsg;
	},
	updateMTO:function()
	{
		var MTONO		=	$("#tdmtono").text();
		var totcnt		=	$("#tdtotqty").attr("data-totcnt");
		var a			=	1;
		var recqty 		=	0;
		var goodqty		=	0;
		var defqty 		=	0;
		var grossamt	=	0;
		var skuno;
		var output		=	"error";
		$("#divloader").dialog("open");
		$.ajax({
				url			:"issuance.php?action=UPDATEMTOHDR&MTONO="+MTONO,
//				async		:false,
				beforeSend	:function(){
					
				},
				success		:function(response){
					$("#divdebug").html(response);
					if(response == "")
					{
						for(a; a < totcnt; a++)
						{
							skuno	=	$("#tdskuno"+a).text();
							recqty	=	$("#txtrecqty"+a).val();
							goodqty	=	$("#txtgoodqty"+a).val();
							defqty	=	$("#txtdefqty"+a).val();
							grossamt=	$("#tdgrossamt"+a).text();
							$.ajax({
								url			:"issuance.php?action=UPDATEMTO&MTONO="+MTONO+"&recqty="+recqty+"&goodqty="+goodqty+"&defqty="+defqty+"&grossamt="+grossamt+"&skuno="+skuno,
								async		:false,
								beforeSend	:function(){
									
								},
								success		:function(response){
									$("#divdebug").html(response);
									output	=	response;
								}
							});	
						}
					}
					if(output == "")
					{
						MessageType.successMsg('MTO has been successfully updated.');
					}
					$("#divloader").dialog("close");
					$("#divmtodtls").dialog("close");
					$("#btnsearch").trigger("click");
				}
		});
	}, 
	postMTO:function(MTONO)
	{
		$.ajax({
				url			:"issuance.php?action=POSTMTO&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
	}
}
var Mto_issuance_fb_funcs	=	new Mto_issuance_fb();
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
			Mto_issuance_fb_funcs.getMTO();
		}
	});
	$("#divMTO").on("click",".documentbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		window.open("issuance_PDF.php?MTONO="+mtono);
		$("#btnsearch").trigger("click");
	});
	$("#divMTO").on("click",".editbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		Mto_issuance_fb_funcs.getDetails(mtono);
	});
	$("#divMTO").on("click",".postbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		MessageType.confirmmsg(Mto_issuance_fb_funcs.postMTO,"Do you want to post this MTO?",mtono);
	});
	$("#divMTO").on("click",".issuebtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		MessageType.confirmmsg(Mto_issuance_fb_funcs.issueMTO,"Do you want to issue this MTO to Filling Bin section?",mtono);
	});
	$("#divmtodtls").on("change",".txtdefqtys",  function(){
		var cnt			=	$(this).attr("data-cnt");
		var totcnt		=	$("#tdtotqty").attr("data-totcnt");
		var recqty			= +	$("#txtrecqty"+cnt).val();
		var goodqty		= +	$("#txtgoodqty"+cnt).val();
		var thisval		= +	$(this).val();
		var totqty		=	thisval + goodqty;
		var totdefqty	=	0;
//		if($(this).val() != "" && recqty != "")
//		{
//			if(totqty != recqty)
//			{
//				MessageType.infoMsg("The sum of good quantity and defective quantity must be equal to the received quantity.");
//				$(this).val("");
//				$("#tdgrossamt"+cnt).text("0.00");
//			}
//		}
		
		totdefqty = inputAmount.sumupByLoop(totcnt-1,"txtdefqty","val","-");
		$("#tdtotdefqty").text(inputAmount.getNumberWithCommas(totdefqty));
	});
	$("#divmtodtls").on("keyup",".txtrecqtys",  function(){
		var cnt			=	$(this).attr("data-cnt");
		var totrecqty	=	0;
		var totcnt		=	$("#tdtotqty").attr("data-totcnt");
		var qty			= +	$("#tdqty"+cnt).text();
		var thisval		= +	$(this).val();
//		if(thisval <= qty)
//		{
			totrecqty = inputAmount.sumupByLoop(totcnt-1,"txtrecqty","val","-");
			$("#tdtotrecqty").text(inputAmount.getNumberWithCommas(totrecqty));
//		}
//		else
//		{
//			MessageType.infoMsg("Received quantity must not be greater than quantity.");
//			$(this).val("");
//		}
	});
	$("#divmtodtls").on("change",".txtgoodqtys",  function(){
		var cnt			=	$(this).attr("data-cnt");
		var totcnt		=	$("#tdtotqty").attr("data-totcnt");
		var unitprice	= +	$("#tdunitprice"+cnt).text();
		var recqty		= +	$("#txtrecqty"+cnt).val();
		var defqty		= +	$("#txtdefqty"+cnt).val();
		var thisval		= +	$(this).val();
		var totqty		=	thisval + defqty;
		var totgrossamt	=	0;
		var totgoodamt	=	0;
//		if(($(this).val() != "" && defqty != ""))
//		{
//			if(totqty != recqty)
//			{
//				MessageType.infoMsg("The sum of good quantity and defective quantity must be equal to the received quantity.");
//				$(this).val("");
//				$("#tdgrossamt"+cnt).text("0.00");
//			}
//		}
		var newgrossamt	=	unitprice * $(this).val();;
		$("#tdgrossamt"+cnt).text(inputAmount.getNumberWithCommas(newgrossamt.toFixed(2)));
		totgrossamt = inputAmount.sumupByLoop(totcnt-1,"tdgrossamt","text","");
		$("#tdtotgrossamt").text(inputAmount.getNumberWithCommas(totgrossamt.toFixed(2)));
		
		totgoodqty = inputAmount.sumupByLoop(totcnt-1,"txtgoodqty","val","-");
		$("#tdtotgoodqty").text(inputAmount.getNumberWithCommas(totgoodqty));
	});
	Mto_issuance_fb_funcs.getMTO();
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