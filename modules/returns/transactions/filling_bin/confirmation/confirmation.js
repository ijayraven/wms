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
					$("#divmtodtls").dialog("open");
					$("#divscanning").dialog("open");
					$("#divloader").dialog("close");
				}
			});
	},
	saveItem:function(currcnt)
	{
		var MTONO		=	$("#tdmtono").text();
		var hiditemno	=	$("#tdskuno"+currcnt).text();
		var txtrecqty	=	$("#tdrecqty"+currcnt).text();
		var txtgoodqty	=	$("#txtgoodqty"+currcnt).val();
		var txtdefqty	=	$("#txtdefqty"+currcnt).val();
		$.ajax({
			type	:	"GET",
			url		:	"confirmation.php?action=SAVEQTY&MTONO="+MTONO+"&hiditemno="+hiditemno+"&txtrecqty="+txtrecqty+"&txtgoodqty="+txtgoodqty+"&txtdefqty="+txtdefqty,
			beforeSend:	function()
			{
			},
			success	:function(response)
			{
				$("#divdebug").html(response);
			}
		});
	},
	getNewTotals:function(curcnt, source,destination){
		var cnt	 			= + $("#tdtotqty").attr("data-totcnt");
    	var currecqty		= + $("#"+source+curcnt).val();
		var currgoodqty		= 	currdefqty 	= currtotrecqty  	= 	currtotrecamt 	= 	0, 	x = 1;
		var inputqty 		= 	recqty 		= recamt 			= 	OAinputqty		=	OAtotrecqty		= OAtotrecamt 	= 0;
		
		currgoodqty 	= + $("#txtgoodqty"+curcnt).val();
		currdefqty 		= + $("#txtdefqty"+curcnt).val();
		currtotrecqty	= currgoodqty + currdefqty;
		
		//compute current amount
		unitprice	=  + $("#tdunitprice"+curcnt).text();
		currtotrecamt	=	parseFloat((unitprice * currtotrecqty)).toFixed(2);
		//end compute current amount
		
		$("#tdgrossamt"+curcnt).text(inputAmount.getNumberWithCommas(currtotrecamt));
		$("#tdrecqty"+curcnt).text(currtotrecqty);
		
    	for(x; x < cnt; x++)
    	{
    		if($("#"+source+x).val() != undefined && $("#tdgrossamt"+x).val() != undefined)
    		{
	    		inputqty	=	+ ($("#"+source+x).val()).replace(/,/g, '');
				recqty		=	+ ($("#tdrecqty"+x).text()).replace(/,/g, '');
				recamt		=	+ ($("#tdgrossamt"+x).text()).replace(/,/g, '');
				
				OAinputqty	=	OAinputqty	+ inputqty;
				OAtotrecqty	=	OAtotrecqty + recqty;
				OAtotrecamt	=	OAtotrecamt	+ recamt;
    		}
    	}
    	$("#"+destination).text(inputAmount.getNumberWithCommas(OAinputqty));
		$("#tdtotrecqty").text(inputAmount.getNumberWithCommas(OAtotrecqty));
		$("#tdtotgrossamt").text(inputAmount.getNumberWithCommas(parseFloat(OAtotrecamt).toFixed(2)));
					
    	Fb_confirmation_funcs.saveItem(curcnt);
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
		var cnt		= + $("#tdtotqty").attr("data-totcnt");
		var a		=	1;
		var itemno, getresponse;
		$("#divloader").dialog("open");
		for(a; a < cnt; a++)
		{
			if($("#chkcon"+a).is(":checked"))
			{
				itemno	=	$("#chkcon"+a).val();
				$.ajax({
					url			:"confirmation.php?action=CONFIRMITEMS&MTONO="+MTONO+"&itemno="+itemno+"&a="+a,
					beforeSend	:function(){
					},
					success		:function(response){
						$("#divdebug").html(response);
					}
				});
			}
			if(a == cnt - 1)
			{
				$("#divloader").dialog("close");
				MessageType.successMsg('Selected item/s has/have been successfully confirmed.');
				$('#btnsearch').trigger('click');
				$('#divmtodtls').dialog('close');
				$('#divscanning').dialog('close');
				$("#divdebug").html("");
			}
		}
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
	$("#divMTO").on("click",".editbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		Fb_confirmation_funcs.getDetails(mtono);
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
						$("#txtscan").focus();
					}
					$("#txtscan").val("");
				}
			});
	});
	$("#divmtodtls").on("change",".txtgoodqties",function(){
    	var curcnt	= + $(this).attr("data-curcnt");
		Fb_confirmation_funcs.getNewTotals(curcnt, "txtgoodqty","tdtotgoodqty");
    });
	$("#divmtodtls").on("change",".txtdefqties",function(){
    	var curcnt	= + $(this).attr("data-curcnt");
		Fb_confirmation_funcs.getNewTotals(curcnt, "txtdefqty","tdtotdefqty");
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
	$("#divMTO").on("click",".postbtn",  function(){
		var mtono	=	$(this).attr("data-trxno");
		MessageType.confirmmsg(Fb_confirmation_funcs.postMTO,"Do you want to post this MTO?",mtono);
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
	Fb_confirmation_funcs.getMTO();
});