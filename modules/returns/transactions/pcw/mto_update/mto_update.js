function Fb_mto_update(){}
Fb_mto_update.prototype	=	{
	constructor:Fb_mto_update,
	
	getMTO:function()
	{
		var frmasearch	=	$("#frmasearch").serialize();
		$.ajax({
				data		:frmasearch,
				type		:"POST",	
				url			:"mto_update.php?action=GETMTO",
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divMTO").html(response);
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter({
						sortList: [[0,0]],
					 	headers: { 15: { sorter: false } }
					});
					$(".buttonset").buttonset();
					$(".tooltips").tooltip();
					$(".btntransmit").button({icons: {primary: "ui-icon ui-icon-check"}});
					for(var x=10; x<=15;x++)
					{
						$('#tblmtolist tr').find('td:nth-child('+x+'),th:nth-child('+x+')').hide();
					}
				}
			});
	},
	getDetails:function(MTONO)
	{
		$.ajax({
				url			:"mto_update.php?action=GETMTODTLS&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divmtodtls").html(response);
					$("#divloader").dialog("close");
					$("#divmtodtls").dialog("open");
					$("#divscanning").dialog("open");
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
				url			:"mto_update.php?action=UPDATEMTOHDR&MTONO="+MTONO,
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
							if(recqty != 0)
							{
								$.ajax({
									url			:"mto_update.php?action=UPDATEMTO&MTONO="+MTONO+"&recqty="+recqty+"&goodqty="+goodqty+"&defqty="+defqty+"&grossamt="+grossamt+"&skuno="+skuno,
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
				url			:"mto_update.php?action=POSTMTO&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
	},
	confirmItems:function(MTONO)
	{
		var frmconfirm	=	$("#frmconfirm").serialize();
		$.ajax({
				data		:frmconfirm,
				type		:"POST",
				url			:"mto_update.php?action=CONFIRMITEMS&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
	},
	receiveMTO:function(MTONO)
	{
		var DRNO	=	$("#txtDR").val();
		$.ajax({
				data		:{MTONO:MTONO, DRNO:DRNO},
				type		:"POST",
				url			:"mto_update.php?action=RECEIVEMTO",
//				url			:"mto_update.php?action=RECEIVEMTO&MTONO="+MTONO+"&DRNO="+DRNO,
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
		
		var totcnt			=	$("#tdtotqty").attr("data-totcnt");
		var totdefqty		=	0;
		var totrecqty		=	0;
		var totgoodqty		=	0;
		var totgrossamt		=	0;
		var totnewgrossamt	=	0;
		
		totdefqty = inputAmount.sumupByLoop(totcnt-1,"tddefqty","text","-");
		$("#tdtotdefqty").text(inputAmount.getNumberWithCommas(totdefqty));
		
		totrecqty = inputAmount.sumupByLoop(totcnt-1,"tdrecqty","text","-");
		$("#tdtotrecqty").text(inputAmount.getNumberWithCommas(totrecqty));

		totgrossamt = inputAmount.sumupByLoop(totcnt-1,"tdgrossamt","text","");
		$("#tdtotgrossamt").text(inputAmount.getNumberWithCommas(totgrossamt.toFixed(2)));

		totnewgrossamt = inputAmount.sumupByLoop(totcnt-1,"tdnewgrossamt","text","");
		$("#tdtotnewgrossamt").text(inputAmount.getNumberWithCommas(totnewgrossamt.toFixed(2)));
		
		totgoodqty = inputAmount.sumupByLoop(totcnt-1,"tdgoodqty","text","-");
		$("#tdtotgoodqty").text(inputAmount.getNumberWithCommas(totgoodqty));
	}
}
var PCW_mto_update_funcs	=	new Fb_mto_update();
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
			PCW_mto_update_funcs.getMTO();
		}
	});
	$("#divMTO").on("click",".documentbtn",  function(){
		var MTONO	=	$(this).attr("data-trxno");
		$.ajax({
				url			:"mto_update.php?action=PRINTMTO&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
					$("#btnsearch").trigger("click");
				}
		});

	});
	$("#divMTO").on("click",".recbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		$("#divDR").data("MTONO",mtono).dialog("open");
	});
	$("#divMTO").on("click",".editbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		PCW_mto_update_funcs.getDetails(mtono);
	});
	$("#divMTO").on("click",".postbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		MessageType.confirmmsg(PCW_mto_update_funcs.postMTO,"Do you want to post this MTO?",mtono);
	});
	$("#divMTO").on("click",".confirmbtn",  function(){
		var MTONO	=	$(this).attr("data-trxno");
		$.ajax({
				url			:"mto_update.php?action=CONFIRM_GETMTODTLS&MTONO="+MTONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divconfirm").html(response);
					$("#divloader").dialog("close");
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
			row	+=		"<td colspan='16' id='tddtlscontent"+cnt+"' class='tddtlscontents tdtrxdtlsClass' style='padding:20px;' align='center'></td>";
			row	+=	"</tr>";
			$("#trdtl"+cnt).after(row);
			$.ajax({
				url			:"mto_update.php?action=GETDTLS&MTONO="+mto,
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
				url			:"mto_update.php?action=SEARCHITEM&SCANNEDVAL="+scannedval+"&MTONO="+MTONO,
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
								$("#tr"+a).removeClass('updated_qty');
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
	$("#divMTO").on("change",".chkcol",function(){
	    var index 	= $(this).val();
	    $('#tblmtolist tr').find('td:nth-child('+index+'),th:nth-child('+index+')').toggle();
	});
	$(".txtqtys").keyup(function(){
		var txtgoodqty	=	+ $("#txtgoodqty").val();
		var txtdefqty	=	+ $("#txtdefqty").val();
		var recqty		=	0;
		recqty	=	txtgoodqty + txtdefqty;
		$("#txtrecqty").val(recqty);
		$("#tdSrecqty").text(recqty);
	});
	PCW_mto_update_funcs.getMTO();
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