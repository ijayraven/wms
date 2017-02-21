$("document").ready(function(){
	$(".searchcust").keyup(function(evt){
		var txtcustno	=	$('#txtcustno').val();
		var txtcustname	=	$('#txtcustname').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'scanning.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname,
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
	$("#btnreport").click(function(event,mainquery,pageno){
		var errmsg		=	"";
		var dataform	=	$("#dataform").serialize();
		if(mainquery == undefined)
		{
			if($("#txtmposno").val() == "" && $('#txtcustno').val() == "")
			{
				if($("#mposdfrom").val() == "" && $("#mposdto").val() == "" && $("#scandfrom").val() == "" &&  $("#scandto").val() == "" && $("#pickdfrom").val() == "" &&  $("#pickdto").val() == "")
				{
					errmsg	+=	"Please input at least one date range.\n";
				}
				if($("#mposdfrom").val() > $("#mposdto").val())
				{
					errmsg	+=	"Invalid MPOS date range.\n";
				}
				if($("#scandfrom").val() > $("#scandto").val())
				{
					errmsg	+=	"Invalid SCAN date range.\n";
				}
				if($("#pickfrom").val() > $("#pickdto").val())
				{
					errmsg	+=	"Invalid SCAN date range.\n";
				}
			}
		}
		
		if(errmsg == "" || mainquery != undefined || pageno != undefined)
		{
			$.ajax({
				type:	"POST",
				data:	dataform,
				url:	"scanning.php?action=GETMPOS&MAINQUERY="+mainquery+"&pageno="+pageno,
				beforeSend:function(){
					$("#divloader").dialog("open");
				},
				success:function(response){
					$("#divMPOS").html(response);
					$("#divloader").dialog("close");
					$(".tdmposdtlsClass").hide();
					for(var x=11; x<=12;x++)
					{
						$('#tblmtolist tr').find('td:nth-child('+x+'),th:nth-child('+x+')').hide();
					}
				}
			});
		}
		else
		{
			alert(errmsg);
		}
	});
	$(".scanitems").live("click",function(){
		var MPOSNO	=	$(this).attr("data-mposno");
		
		$.ajax({
			type	:	"GET",
			url		:	"scanning.php?action=SCANNING&MPOSNO="+MPOSNO,
			beforeSend:	function()
			{
				$("#divloader").dialog("open");
			},
			success	:function(response)
			{
				$("#divitems").html(response);
				$("#divloader").dialog("close");
				$("#divmposdtls").dialog("open");
				$("#divscanning").dialog("open");
				$('.ui-dialog-buttonpane button:contains(Update)').attr("id", "dia-btn-update");
				$('#dia-btn-update').html("Save");
			}
		});
		
	});
	$(".txtinputqty, .txtdefqty, #txtqtyS,.txtibqty").live("keydown",function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
             // Allow: Ctrl+C
            (e.keyCode == 67 && e.ctrlKey === true) ||
             // Allow: Ctrl+X
            (e.keyCode == 88 && e.ctrlKey === true) ||
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    $("#txtitemno2").click(function(){
    	$("#txtitemno").val("");
    });
    $("#txtitemno").click(function(){
    	$("#txtitemno2").val("");
    });
    $(".txtitemnos").keydown(function(e){
    	if(e.keyCode == 13)
    	{
	    	var BARCODE 	= $("#txtitemno").val();
	    	var txtitemno 	= $("#txtitemno2").val();
	    	var txtqtyS 	= + $("#txtqtyS").val();
	    	$.ajax({
					type	:	"GET",
					url		:	"scanning.php?action=GETITEM&BARCODE="+BARCODE+"&ITEMNO="+txtitemno,
					beforeSend:	function()
					{
						$("#divloader").dialog("open");
					},
					success	:function(response)
					{
	//					$("#divMPOSdebug").html(response);
						$("#divloader").dialog("close");
						var itemno  = response;
						var cnt	 	= $("#trtotcnt").attr("data-cnt");
						var x		=	1;
						var found	=	0;
						var preqty,postqty,unitprice,newtotamt,tditemno,totrecqty=0,totrecamt=0,recqty,recamt;
						for(x; x < cnt; x++)
						{
							tditemno	=	$("#tditemno"+x).text();
							if(tditemno == itemno)
							{
								
								found	=	1;
								$("#trfound"+x).addClass('trfound');
								if($("#tdcurrstatus"+x).text() == "P")
								{
									$("#tdcurrstatus"+x).addClass('primeitem');
								}
								else
								{
									$("#tdcurrstatus"+x).removeClass('primeitem');
								}
								preqty 		=  + $("#txtrecqty"+x).val();
								if(txtqtyS != "" && txtqtyS != 0)
								{
									postqty		= 	preqty + txtqtyS;
								}
								else
								{
									postqty		= 	preqty + 1;
								}
								unitprice	=  + $("#trfound"+x).attr("data-unitprice");
								newtotamt	=	parseFloat((unitprice * postqty)).toFixed(2);
								$("#tdrecamt"+x).text(numberWithCommas(newtotamt));
								$("#txtrecqty"+x).val(postqty);
								var element = document.getElementById("trfound"+x);
								element.scrollIntoView();
								element.scrollIntoView(false);
								element.scrollIntoView({block: "end"});
								element.scrollIntoView({block: "end", behavior: "smooth"});
							}
							else
							{
	//							$("#trfound"+x).removeClass('trfound');
							}
							recqty	=	+ ($("#txtrecqty"+x).val()).replace(/,/g, '');
							recamt	=	+ ($("#tdrecamt"+x).text()).replace(/,/g, '');
							totrecqty	=	recqty+totrecqty;
							totrecamt	=	recamt+totrecamt;
						}
						if(found == 0)
						{
							$("#errmsg").text("Item not found.");
						}
						else
						{
							$("#errmsg").text("");
						}
						$("#txtitemno").val("");
						$("#txtitemno2").val("");
						$("#txtitemno").focus();
						$("#tdrecqty").text(numberWithCommas(totrecqty));
						$("#tdrecamt").text(numberWithCommas(parseFloat(totrecamt).toFixed(2)));
					}
				});
    	}
    });
    $(".txtinputqty").live("change",function(){
    	var cnt	 	= + $("#trtotcnt").attr("data-cnt");
    	var curcnt	= + $(this).attr("data-curcnt");
    	var x		=	1;
    	var addeditemscnt	= + $("#txtaddeditemscnt").val();
    	var newcnt	= 	addeditemscnt + cnt;
    	var preqty,postqty,unitprice,newtotamt,tditemno,totrecqty=0,totrecamt=0,recqty,recamt;
    	if(addeditemscnt != 0)
    	{
    		if($("#hiditemno"+curcnt).val() == "")
    		{
    			alert("Please fill in item number.");
    			$(this).val("");
    			return;
    		}
    	}
    	for(x; x < newcnt; x++)
    	{
			preqty 		=  + $("#txtrecqty"+x).val();
			postqty		= 	preqty;
			if($("#hidsrp"+x).val() == undefined)
			{
				unitprice	=  + $("#trfound"+x).attr("data-unitprice");
			}
			else
			{
				unitprice	=  + $("#hidsrp"+x).val();
			}
				
			newtotamt	=	parseFloat((unitprice * postqty)).toFixed(2);
			$("#tdrecamt"+x).text(numberWithCommas(newtotamt));
			
    		recqty	=	+ ($("#txtrecqty"+x).val()).replace(/,/g, '');
    		recamt	=	+ ($("#tdrecamt"+x).text()).replace(/,/g, '');
    		totrecqty	=	recqty+totrecqty;
    		totrecamt	=	recamt+totrecamt;
    	}
    	$("#txtitemno").val("");
		$("#tdrecqty").text(numberWithCommas(totrecqty));
		$("#tdrecamt").text(numberWithCommas(parseFloat(totrecamt).toFixed(2)));
		$("#errmsg").text("");
		if(this.value != "" && this.value != 0)
		{
			$("#trfound"+curcnt).addClass('trfound');
		}
		else
		{
			$("#trfound"+curcnt).removeClass("trfound");
		}
//		$(this).val(numberWithCommas($(this).val()));
    });
    $(".txtdefqty").live("change",function(){
    	var cnt	 	= + $("#trtotcnt").attr("data-cnt");
    	var addeditemscnt	= + $("#txtaddeditemscnt").val();
    	var newcnt	=	addeditemscnt + cnt;
    	var curcnt	= + $(this).attr("data-curcnt");
    	var currecqty	= + $("#txtrecqty"+curcnt).val();
    	var x =	1,totdefqty	= 0,recqty = 0;
    	if($(this).val() > currecqty)
    	{
    		alert("Defective quantity is greater than received quantity.");
    		$(this).val("");
    		return;
    	}
    	for(x; x < newcnt; x++)
    	{
    		recqty = + $("#txtdefqty"+x).val();
    		totdefqty += recqty;
    	}
    	$("#tdtotdefqty").text(totdefqty);
    });
    $(".txtibqty").live("change",function(){
    	var cnt	 	= + $("#trtotcnt").attr("data-cnt");
    	var addeditemscnt	= + $("#txtaddeditemscnt").val();
    	var newcnt	=	addeditemscnt + cnt;
    	var curcnt	= + $(this).attr("data-curcnt");
    	var currecqty	= + $("#txtrecqty"+curcnt).val();
    	var x =	1,	totibqty	= 0,	recqty = 0;
    	if($(this).val() > currecqty)
    	{
    		alert("Internal barcode quantity is greater than received quantity.");
    		$(this).val("");
    		return;
    	}
    	for(x; x < newcnt; x++)
    	{
    		recqty = + $("#txtibqty"+x).val();
    		totibqty += recqty;
    	}
    	$("#tdtotibqty").text(totibqty);
    });
    $(".editdtls").live("click",function(){
    	var MPOSNO	=	$(this).attr("data-mposno");
	    $.ajax({
			type	:	"GET",
			url		:	"scanning.php?action=ESCANNING&MPOSNO="+MPOSNO,
			beforeSend:	function()
			{
				$("#divloader").dialog("open");
			},
			success	:function(response)
			{
				$("#divitems").html(response);
				$("#divmposdtls").dialog("open");
				$("#divscanning").dialog("open");
		    	$('#divmposdtls').dialog('option', 'title', 'EDIT SCANNED ITEMS');
			    $('.ui-dialog-buttonpane button:contains(Save)').attr("id", "dia-btn-save");
			    $('#dia-btn-save').html("Update");
				$("#divloader").dialog("close");
			}
		});
    });
    $(".deletempos").live("click",function(){
    	var MPOSNO	=	$(this).attr("data-mposno");
    	if(confirm("You are about to delete this scanned MPOS:"+MPOSNO+". Please confirm."))
    	{
		    $.ajax({
				type	:	"GET",
				url		:	"scanning.php?action=DELSCANNING&MPOSNO="+MPOSNO,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$("#divMPOSdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
    	}
    });
    $(".postmpos").live("click",function(){
    	var MPOSNO	=	$(this).attr("data-mposno");
    	if(confirm("You are about to post this scanned MPOS:"+MPOSNO+". Please confirm."))
    	{
		    $.ajax({
				type	:	"GET",
				url		:	"scanning.php?action=POSTSCANNING&MPOSNO="+MPOSNO,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$("#divMPOSdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
    	}
    });
    $("#btnadditem").live("click",function(){
    	var txtaddeditemscnt = + $("#txtaddeditemscnt").val();
    		txtaddeditemscnt++;
    	var traddeditems	=	"";
    	var aboverowcnt		=	+ $(this).attr("data-aboverowcnt");
    	var counter			=	(aboverowcnt-1)+ txtaddeditemscnt;
    	var disabled		=	"";
    	var isdisabled		=	$("#txtibqty1").is(':disabled');
    	if(isdisabled == true)
    	{
    		disabled = 'disabled';
    	}
    	traddeditems	=	"<tr id = 'trfound"+counter+"' class='trbody' style='font-size:12px;'>";
    	traddeditems	+=		"<td align='center'>"+counter+"</td>";
    	traddeditems	+=		"<td align='center'>"+
    								"<input type='text' id='hiditemno"+counter+"' name='hiditemno"+counter+"' size='8' class='addeditem centered' data-curcnt='"+counter+"'>"+
    								"<input type='hidden' id='hidsrp"+counter+"'  name='hidsrp"+counter+"'>"+
    							"</td>";
    	traddeditems	+=		"<td id='tditemdesc"+counter+"'></td>";
    	traddeditems	+=		"<td id='tditemstatus'></td>";
    	traddeditems	+=		"<td id='tditemqty'></td>";
    	traddeditems	+=		"<td id='tditemgross'></td>";
    	traddeditems	+=		"<td id='tdcurrstatus"+counter+"' align='center'></td>";
    	traddeditems	+=		"<td align='center'><input type='text' id='txtrecqty"+counter+"' name='txtrecqty"+counter+"' size='5' class='txtaddedinputqty txtinputqty' data-curcnt='"+counter+"'></td>";
    	traddeditems	+=		"<td id='tdrecamt"+counter+"' align='right'></td>";
    	traddeditems	+=		"<td align='center'><input type='text' id='txtdefqty"+counter+"' name='txtdefqty"+counter+"' size='5' class='txtdefqty' data-curcnt='"+counter+"'></td>";
    	traddeditems	+=		"<td align='center'><input type='text' id='txtibqty"+counter+"' name='txtibqty"+counter+"' size='5' class='txtibqty centered' data-curcnt='"+counter+"' "+disabled+"></td>";
    	traddeditems	+=	"</tr>";
    	
    	$("#tblscanning tbody").append(traddeditems);
    	$("#txtaddeditemscnt").val(txtaddeditemscnt);
    });
    $(".addeditem").live("change",function(){
    	var curcnt				=	$(this).attr("data-curcnt");
    	var itemno				=	$(this).val();
    	var totcnt				= + $("#trtotcnt").attr("data-cnt");
    	var txtaddeditemscnt 	= + $("#txtaddeditemscnt").val();
    	var dupErr				=	"";
    	var counter				=	txtaddeditemscnt+totcnt-1;
    	if(itemno != "")
		{	
			for(var cnt	= 1; cnt <= counter; cnt++)
			{
				if(cnt != curcnt)
				{
					if(itemno == $("#hiditemno"+cnt).val())
					{
						dupErr	=	"Duplicate item.";
					}
				}
			}
			if(dupErr == "")
			{
				$.ajax({
					type	:	"GET",
					url		:	"scanning.php?action=GETITEMDTLS&ITEMNO="+itemno+"&ITEMNO="+itemno+"&CURCNT="+curcnt,
					beforeSend:	function()
					{
//						$("#divloader").dialog("open");
					},
					success	:function(response)
					{
						$("#divitemsdebug").html(response);
//						$("#divloader").dialog("close");
					}
				});
			}
			else
			{
				$('#tditemdesc'+curcnt).text(dupErr);
				$('#tdcurrstatus'+curcnt).text('');
				$('#hiditemno'+curcnt).val('');
			}
		}
    });
    $(".tdmposdtls").live("click",function(){
    	var MPOSNO	=	$(this).attr("data-mposno");
    	var COUNT	=	$(this).attr("data-count");
    	var tdtext	=	$("#tdmposdtls"+COUNT).html();
			tdtext	=	tdtext.trim();
		if(tdtext == "")
		{	
		    $.ajax({
				type	:	"GET",
				url		:	"scanning.php?action=VIEWMPOSDTLS&MPOSNO="+MPOSNO+"&COUNT="+COUNT,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$(".tdmposdtlsClass").html("");
					$(".tdmposdtlsClass").hide();
					$("#tdmposdtls"+COUNT).show();
					$("#tdmposdtls"+COUNT).html(response);
					$(".trdtls").removeClass("activetr");
					$("#trdtls"+COUNT).addClass("activetr");
					$("#divloader").dialog("close");
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
    $("#btnremoveitem").live("click",function(){
    	var addeddcnt	=	+ $("#txtaddeditemscnt").val();
    	if(addeddcnt != 0)
    	{
	    	var prevcnt		=	+ $("#trtotcnt").attr("data-cnt");
	    	var newtrtotcnt	=	addeddcnt - 1 + prevcnt;
	    	
	    	$("#trfound"+newtrtotcnt).remove();
	    	$("#txtaddeditemscnt").val(addeddcnt - 1);
    	}
    	recompute();
    });
	$(".chkcol").live("change",function(){
	    var index 	= $(this).val();
	    $('#tblmtolist tr').find('td:nth-child('+index+'),th:nth-child('+index+')').toggle();
	});
//    $(".txtaddedinputqty").live("change",function(){
//    	var cnt	 		= 	$("#trtotcnt").attr("data-cnt");
//    	var addedcnt	=	+ $("#txtaddeditemscnt").val();
//		var newtrtot	=	cnt -1 + addedcnt;
//		var start		=	newtrtot - addedcnt + 1;
//		var tdrecqty	=	+ $("#tdrecqty").text();
//		var tdrecamt	=	+ $("#tdrecamt").text();
//		var tdtotdefqty	=	+ $("#tdtotdefqty").text();
//		var addedrecqty	=	addedrecamt	=	addeddefqty	=	0;
//		if(addedcnt != 0)
//		{ 
//			for(start; start <= newtrtot; start++)
//			{
//				addedrecamt +=	+ $("#hidsrp"+start).val() * $(this).val();;
//				addedrecqty	+=	+ $("#txtrecqty"+start).val();
//				$("#tdrecamt"+start).text(addedrecamt);
//			}
//		}
//		$("#tdrecqty").text(tdrecqty + addedrecqty);
//    });
});
window.setInterval(TriggerSave,1000);
var timer = 0;
function TriggerSave()
{
	if($("#divmposdtls").dialog( "isOpen" ) == true)
	{
		timer = timer + 1;
		if(timer > 600)
		{
			alert("Please save your work.");
			timer = 0;
		}
	}
	else
	{
		timer = 0;
	}
}
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
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
function validate()
{
	var cnt	 	= $("#trtotcnt").attr("data-cnt");
    	var x		=	1;
    	var valid	=	true;
//    	for(x; x < cnt; x++)
//    	{
//    		recqty	=	+ $("#txtrecqty"+x).val();
//    		if(recqty == "" || recqty == undefined || recqty == 0)
//    		{
//    			$("#trfound"+x).addClass("err-background");
//    			valid = false;
//    		}
//    		else
//    		{
//    			$("#trfound"+x).removeClass("err-background");
//    		}
//    	}
	var addeddcnt	=	+ $("#txtaddeditemscnt").val();
	var cnt	 		= 	+ $("#trtotcnt").attr("data-cnt");
	var newcnt		= 	addeddcnt + cnt;
	for(var a = 1; a < newcnt; a++)
	{
		if($("#hiditemno"+a).val() == "")
		{
			valid = false;
			$("#trfound"+a).addClass("err-background");
		}
		else
		{
			$("#trfound"+a).removeClass("err-background");
		}
	}
    return valid;
}
function getmpos(pageno)
{
	$("#btnreport").trigger("click",[undefined,pageno]);
}
function recompute()
{
	var addeddcnt	=	+ $("#txtaddeditemscnt").val();
	var cnt	 		= 	+ $("#trtotcnt").attr("data-cnt");
	var newcnt		= 	addeddcnt + cnt;
	var newrecqty	=	newrecamt	=	newdefqty	=	newibqty	=	0;
	var recqty		=		recamt	=		defqty	= 	ibqty		=	0;
	for(var a = 1; a < newcnt; a++)
	{
		recqty	=	+ $("#txtrecqty"+a).val();
		recamt	=	+ $("#tdrecamt"+a).text();
		defqty	=	+ $("#txtdefqty"+a).val();
		ibqty	=	+ $("#txtibqty"+a).val();
		
		newrecqty	+=	recqty;
		newrecamt	+=	recamt;
		newdefqty	+=	defqty;
		newibqty	+=	ibqty;
	}
	$("#tdrecqty").text(newrecqty);
	$("#tdrecamt").text(newrecamt);
	$("#tdtotdefqty").text(newdefqty);
	$("#tdtotibqty").text(newibqty);
}