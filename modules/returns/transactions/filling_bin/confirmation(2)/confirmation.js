function Fb_confirmation(){}
Fb_confirmation.prototype	=	{
	constructor:Fb_confirmation,
	
	getMTO:function()
	{
		var frmasearch	=	$("#frmasearch").serialize();
		$.ajax({
				data		:frmasearch,
				type		:"POST",	
				url			:"confirmation.php?action=GETMTO",
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
	getDetails:function(MTONO)
	{
		$.ajax({
				url			:"confirmation.php?action=GETMTODTLS&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divmtodtls").html(response);
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
					 				8: { sorter: false },
					 				9: { sorter: false }
					 			}
					});
					$("#divmtodtls").dialog("open");
					$("#divscanning").dialog("open");
					$("#divloader").dialog("close");
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
		}
		errmsg	=	errmsgR + errmsgS;
		return errmsg;
	},
	getNewTotals:function(curcnt, source,destination){
		var cnt	 			= + $("#trtotcnt").attr("data-cnt");
    	var addeditemscnt	= + $("#txtaddeditemscnt").val();
    	var currecqty		= + $("#"+source+curcnt).val();
    	var newcnt			=	addeditemscnt + cnt;
		var currgoodqty	= currdefqty 	= curribqty = currtotrecqty  	= 	currtotrecamt 	= 0, x = 1;
		var inputqty 	= recqty 		= recamt 	= OAinputqty		=	OAtotrecqty		= OAtotrecamt 	= 0;
		
		currgoodqty 	= + $("#txtgoodqty"+curcnt).val();
		currdefqty 		= + $("#txtdefqty"+curcnt).val();
		curribqty 		= + $("#txtibqty"+curcnt).val();
		currtotrecqty	= currgoodqty + currdefqty + curribqty;
		
		//compute current amount
		unitprice	=  + $("#hidsrp"+curcnt).val();
		currtotrecamt	=	parseFloat((unitprice * currtotrecqty)).toFixed(2);
		//end compute current amount
		
		$("#tdrecamt"+curcnt).text(inputAmount.getNumberWithCommas(currtotrecamt));
		$("#txtrecqty"+curcnt).val(currtotrecqty);
		
		$("#tdrecqty"+curcnt).text(currtotrecqty);
		
    	for(x; x < newcnt; x++)
    	{
    		if($("#"+source+x).val() != undefined && $("#txtrecqty"+x).val() != undefined && $("#tdrecamt"+x).val() != undefined)
    		{
	    		inputqty	=	+ ($("#"+source+x).val()).replace(/,/g, '');
				recqty		=	+ ($("#txtrecqty"+x).val()).replace(/,/g, '');
				recamt		=	+ ($("#tdrecamt"+x).text()).replace(/,/g, '');
				
				OAinputqty	=	OAinputqty	+ inputqty;
				OAtotrecqty	=	OAtotrecqty + recqty;
				OAtotrecamt	=	OAtotrecamt	+ recamt;
    		}
    	}
    	$("#"+destination).text(inputAmount.getNumberWithCommas(OAinputqty));
		$("#tdrecqty").text(inputAmount.getNumberWithCommas(OAtotrecqty));
		$("#tdrecamt").text(inputAmount.getNumberWithCommas(parseFloat(OAtotrecamt).toFixed(2)));
					
    	scanningFuns.saveItem(curcnt);
	}
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
				url			:"confirmation.php?action=UPDATEMTOHDR&MTONO="+MTONO,
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
								url			:"confirmation.php?action=UPDATEMTO&MTONO="+MTONO+"&recqty="+recqty+"&goodqty="+goodqty+"&defqty="+defqty+"&grossamt="+grossamt+"&skuno="+skuno,
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
				url			:"confirmation.php?action=POSTMTO&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
	},
	validateConfirmation:function()
	{
		var selected	=	false;
		$('.chkcons').each(function () 
		{
        	if($(this).is(":checked"))
        	{
        		selected	=	true;
        	}
		});
		if(!selected)
		{
			MessageType.infoMsg("No selected items to confirm.");
			return false;
		}
		else
		{
			return true;
		}
	},
	confirmItems:function(MTONO)
	{
		var frmconfirm	=	$("#frmconfirm").serialize();
		$.ajax({
				data		:frmconfirm,
				type		:"POST",
				url			:"confirmation.php?action=CONFIRMITEMS&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
	},
	getTotals:function(cnt)
	{
		
		var totcnt		=	$("#tdtotqty").attr("data-totcnt");
		var totdefqty	=	0;
		var totrecqty	=	0;
		var totgoodqty	=	0;
		var totgrossamt	=	0;
		
		totdefqty = inputAmount.sumupByLoop(totcnt-1,"tddefqty","text","-");
		$("#tdtotdefqty").text(inputAmount.getNumberWithCommas(totdefqty));
		
		totrecqty = inputAmount.sumupByLoop(totcnt-1,"tdrecqty","text","-");
		$("#tdtotrecqty").text(inputAmount.getNumberWithCommas(totrecqty));

		totgrossamt = inputAmount.sumupByLoop(totcnt-1,"tdgrossamt","text","");
		$("#tdtotgrossamt").text(inputAmount.getNumberWithCommas(totgrossamt.toFixed(2)));
		
		totgoodqty = inputAmount.sumupByLoop(totcnt-1,"tdgoodqty","text","-");
		$("#tdtotgoodqty").text(inputAmount.getNumberWithCommas(totgoodqty));
	}
}
var Fb_confirmation_funcs	=	new Fb_confirmation();
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
			Fb_confirmation_funcs.getMTO();
		}
	});
	$("#divMTO").on("click",".documentbtn",  function(){
		var MTONO	=	$(this).attr("data-trxno");
		$.ajax({
				url			:"confirmation.php?action=PRINTMTO&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
		});

	});
	$("#divMTO").on("click",".editbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		Fb_confirmation_funcs.getDetails(mtono);
	});
	$("#divMTO").on("click",".postbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		MessageType.confirmmsg(Fb_confirmation_funcs.postMTO,"Do you want to post this MTO?",mtono);
	});
	$("#divMTO").on("click",".confirmbtn",  function(){
		var MTONO	=	$(this).attr("data-trxno");
		$.ajax({
				url			:"confirmation.php?action=CONFIRM_GETMTODTLS&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divconfirm").html(response);
					$("#divloader").dialog("close");
					$("#divconfirm").dialog("open");
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
					 				8: { sorter: false },
					 				9: { sorter: false }
					 			}
					});
				}
			});
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
	$("#divMTO").on("click",".tddtls",function(){
		var cnt	=	$(this).attr("data-cnt");
		var mto	=	$(this).attr("data-mto");
		var row	=	"";
		if(! $("#trdtlscontent"+cnt).length)
		{
			$(".trdtlscontents").remove();
			$(".trdtls").removeClass("activetr");
			$("#trdtl"+cnt).addClass("activetr");
			
			row	=	"<tr class='trbody trdtlscontents' id='trdtlscontent"+cnt+"'>";
			row	+=		"<td colspan='7' id='tddtlscontent"+cnt+"' class='tddtlscontents tdtrxdtlsClass' style='padding:20px;' align='center'></td>";
			row	+=	"</tr>";
			$("#trdtl"+cnt).after(row);
			$.ajax({
				url			:"confirmation.php?action=GETDTLS&MTONO="+mto,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#tddtlscontent"+cnt).html(response);
					$("#divloader").dialog("close");
				}
			});
		}
		else
		{
			$("#trdtlscontent"+cnt).remove();
			$(".trdtls").removeClass("activetr");
		}
		
	});
	$("#divMTO").on("mousedown","th",function(){
		$(".trdtlscontents").remove();
	});
	$("#txtscan").change(function(){
		var scannedval	=	$(this).val();
		var MTONO		=	$("#tdmtono").text();
		var totcnt		=	$("#tdtotqty").attr("data-totcnt");
		var a;	
		$.ajax({
				url			:"confirmation.php?action=SEARCHITEM&SCANNEDVAL="+scannedval+"&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(itemno){
//					$("#divdebug").html(itemno);
					$("#divloader").dialog("close");
					$("#hdnitemno").val(itemno);
					if(itemno != "")
					{
						for(a = 1; a < totcnt; a++)
						{
							if($("#tdskuno"+a).text() == itemno)
							{
								$("#hdncurrcnt").val(a);
								$("#tr"+a).addClass('trfound');
								var element = document.getElementById("tr"+a);
								element.scrollIntoView();
								element.scrollIntoView(false);
								element.scrollIntoView({block: "end"});
								element.scrollIntoView({block: "end", behavior: "smooth"});
								$("#txtrecqty").focus();
								$("#errmsg").text("");
								$("#sucmsg").text("");
							}
						}
					}
					else
					{
						$("#errmsg").text("Item not found.");
						$("#sucmsg").text("");
						$("#txtscan").val("");
						$("#txtscan").focus();
						$("#txtrecqty").val("");
						$("#txtgoodqty").val("");
						$("#txtdefqty").val("");
					}
					
				}
			});
	});
	$(".txtqtys").keyup(function(){
		var txtgoodqty	=	+ $("#txtgoodqty").val();
		var txtdefqty	=	+ $("#txtdefqty").val();
		var recqty		=	0;
		recqty	=	txtgoodqty + txtdefqty;
		$("#txtrecqty").val(recqty);
		$("#tdSrecqty").text(recqty);
	});
	$("#divmtodtls").on("click",".chkcons",function(){
		if($("#chkAllCon").is(":checked"))
		{
			$("#chkAllCon").prop("checked",false);
		}
	});
	$("#divmtodtls").on("click","#chkAllCon",function(){
		if($(this).is(":checked"))
		{
			$(".chkcons").prop("checked",true);
		}
		else
		{
			$(".chkcons").prop("checked",false);
		}
	});
	Fb_confirmation_funcs.getMTO();
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