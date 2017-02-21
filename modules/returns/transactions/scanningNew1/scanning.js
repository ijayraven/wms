function Scanning(){}
	Scanning.prototype = {
		constructor:Scanning,
		smartsel:function(evt){
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
		recompute:function()
		{
			var addeddcnt	=	+ $("#txtaddeditemscnt").val();
			var cnt	 		= 	+ $("#trtotcnt").attr("data-cnt");
			var newcnt		= 	addeddcnt + cnt;
			var newrecqty	=	newrecamt	=	newgoodqty	=	newdefqty	=	newibqty	=	0;
			var recqty		=		recamt	=		goodqty	=		defqty	= 	ibqty		=	0;
			for(var a = 1; a < newcnt; a++)
			{
				recqty	=	+ $("#txtrecqty"+a).val();
				recamt	=	+ $("#tdrecamt"+a).text();
				defqty	=	+ $("#txtdefqty"+a).val();
				goodqty	=	+ $("#txtgoodqty"+a).val();
				ibqty	=	+ $("#txtibqty"+a).val();
				if(isNaN(recqty) == false)
				{
					newrecqty	+=	recqty;
					newrecamt	+=	recamt;
					newgoodqty	+=	goodqty;
					newdefqty	+=	defqty;
					newibqty	+=	ibqty;
				}
			}
			$("#tdrecqty").text(newrecqty);
			$("#tdrecamt").text(newrecamt);
			$("#tdtotgoodqty").text(newgoodqty);
			$("#tdtotdefqty").text(newdefqty);
			$("#tdtotibqty").text(newibqty);		
		},
		save_scan:function(MPOSNO)
		{
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
					$(".buttons").button();
					$(".trmposdtlsadded").tooltip();
				}
			});
		},
		saveItem:function(currcnt)
		{
			var MPOSNO		=	$("#tdmposno").text();
			var hiditemno	=	$("#hiditemno"+currcnt).val();
			var txtrecqty	=	$("#txtrecqty"+currcnt).val();
			var txtgoodqty	=	$("#txtgoodqty"+currcnt).val();
			var txtdefqty	=	$("#txtdefqty"+currcnt).val();
			var txtibqty	=	$("#txtibqty"+currcnt).val();
			var adddtl		=	$("#hiditemno"+currcnt).attr("data-adddtl");
			$.ajax({
				type	:	"GET",
				url		:	"scanning.php?action=SAVEITEM_REPLACEQTY&MPOSNO="+MPOSNO+"&hiditemno="+hiditemno+"&txtrecqty="+txtrecqty+"&txtgoodqty="+txtgoodqty+"&txtdefqty="+txtdefqty+"&txtibqty="+txtibqty+"&adddtl="+adddtl,
				beforeSend:	function()
				{
//					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$("#divitemsdebug").html(response);
//					$("#divloader").dialog("close");
				}
			});
		},
		deleteItem:function(currcnt){
	    	var MPOSNO		=	$("#tdmposno").text();
			var hiditemno	=	$("#hiditemno"+currcnt).val();
			$.ajax({
				type	:	"GET",
				url		:	"scanning.php?action=DELETEITEM&MPOSNO="+MPOSNO+"&hiditemno="+hiditemno+"&currcnt="+currcnt,
				beforeSend:	function()
				{
//					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$("#divitemsdebug").html(response);
//					$("#divloader").dialog("close");
				}
			});
		},
		postMPOS:function(MPOSNO)
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
		},
		deleteMPOS:function(MPOSNO)
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
		},
		chkDup:function(itemno)
		{
			var txtaddeditemscnt 	= 	+ $("#txtaddeditemscnt").val();
	    	var aboverowcnt			=	+ $("#txtaboverowcnt").val();
			var curcnt				=	txtaddeditemscnt + (aboverowcnt-1);
	    	var counter				=	curcnt;
	    	var valid				=	true;
	    	if(itemno != "")
			{	
				for(var cnt	= 1; cnt <= counter; cnt++)
				{
					if(cnt != curcnt)
					{
						if(itemno == $("#hiditemno"+cnt).val())
						{
							valid	=	false;
						}
					}
				}
			}
			else
			{
				$('#tditemdesc'+curcnt).text('');
				$('#tdcurrstatus'+curcnt).text('');
				$('#hiditemno'+curcnt).val('');
				$('#hidsrp'+curcnt).val('');
			}
			return valid;
		}
	}
	
var scanningFuns	=	new Scanning();
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
	$("#btnsearch").click(function(event,mainquery,pageno){
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
		
		if(errmsg == "" || mainquery != undefined || pageno != undefined || $("#txtmposno").val() != "")
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
					$(".trdtls").tooltip();
					for(var x=11; x<=12;x++)
					{
						$('#tblmtolist tr').find('td:nth-child('+x+'),th:nth-child('+x+')').hide();
					}
				}
			});
		}
		else
		{
			MessageType.infoMsg(errmsg);
		}
	});
	$("#divMPOS").on("change",".chkcol",function(){
	    var index 	= $(this).val();
	    $('#tblmtolist tr').find('td:nth-child('+index+'),th:nth-child('+index+')').toggle();
	});
	$("#divMPOS").on("click",".tdmposdtls",function(){
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
    $("#divMPOS").on("click",".scanitems",function(){
		var MPOSNO	=	$(this).attr("data-mposno");
		MessageType.confirmmsg(scanningFuns.save_scan,"You are about to save the details of this MPOS and proceed to item/s scanning. Do you want to continue?",MPOSNO);
	});
    $("#divMPOS").on("click",".editdtls",function(){
		var MPOSNO	=	$(this).attr("data-mposno");
		scanningFuns.save_scan(MPOSNO);
	});
	$("#divMPOS").on("click",".postmpos",function(){
    	var MPOSNO	=	$(this).attr("data-mposno");
    	MessageType.confirmmsg(scanningFuns.postMPOS,"Do you want to post this scanned MPOS: "+MPOSNO+"? ",MPOSNO);
    });
	$("#divMPOS").on("click",".deletempos",function(){
    	var MPOSNO	=	$(this).attr("data-mposno");
    	MessageType.confirmmsg(scanningFuns.deleteMPOS,"Do you want to delete this MPOS: "+MPOSNO+"? ",MPOSNO);
    });
	$(".txtqtys").keydown(function(e){
		if(e.keyCode == 13 && $(this).val() != "")
    	{
    		var destination_trigger		= $(this).attr("data-destination_trigger");
    		var val 					= + $(this).val();
    		$("#"+destination_trigger).trigger("keydown",[ "enter", val ]);
    	}
	});
	$("#txtgood, #txtdef, #txtib").keydown(function(e,enter,qty){
    	if((e.keyCode == 13 || enter == "enter") && $(this).val() != "")
    	{
	    	var BARCODE_txtitemno 	= $(this).val();
	    	var destination_val		= $(this).attr("data-destination_val");
	    	
	    	if(qty == undefined)
	    	{
	    		qty	=	+ $("#"+this.id + "qty").val();
	    	}
	    	$.ajax({
				type	:	"GET",
				url		:	"scanning.php?action=GETITEM&BARCODE_TXTITEMNO="+BARCODE_txtitemno,
				success	:function(response)
				{
					var arr_itemdtls  = response;
					if(arr_itemdtls != "")
					{
						$("#errmsg").text("");
						var itemdetails	=	arr_itemdtls.split(",");
		    			var	itemno		=	itemdetails[0];
		    			var MPOSNO		=	$("#tdmposno").text();
						$.ajax({
							type	:	"GET",
							url		:	"scanning.php?action=SAVEITEM&itemno="+itemno+"&MPOSNO="+MPOSNO+"&qty="+qty+"&destination_val="+destination_val,
							success	:function(saveresponse)
							{
								$("#divitemsdebug").html(saveresponse);
								var arr_itemqtydetails	=	saveresponse.split(",");
								if(arr_itemqtydetails[0] == "successful")
								{
									$("#divitemsdebug").html("");
									var txtaddeditemscnt 	= 	+ $("#txtaddeditemscnt").val();
					   			 	var aboverowcnt			=	+ $("#txtaboverowcnt").val();
									var curcnt				=	txtaddeditemscnt + aboverowcnt;
							    	var addeditem			=	true;
									for(var a	= 1; a <= curcnt; a++)
									{
										if(itemno == $("#hiditemno"+a).val())
										{
											addeditem	=	false;
										}
									}
									if(addeditem)
						    		{
						    			var itemdesc			=	itemdetails[1];
							    		var itemstatus			=	itemdetails[2];
							    		var itemsrp				=	itemdetails[3];
							    		var counter				=	aboverowcnt + txtaddeditemscnt;
								    	traddeditems	=	"<tr id = 'trfound"+counter+"' class='trbody trmposdtlsadded' style='font-size:12px;' data-currcnt='"+counter+"' title='Double click to delete item from MPOS'>";
								    	traddeditems	+=		"<td align='center'>"+counter+
								    								"<input type='hidden' id='txtrecqty"+counter+"' name='txtrecqty"+counter+"' size='5' class='txtaddedinputqty txtinputqty' data-curcnt='"+counter+"'>"+
								    								"<input type='hidden' id='hiditemno"+counter+"' name='hiditemno"+counter+"' size='8' class='addeditem centered' data-curcnt='"+counter+"' data-adddtl='Y' value='"+itemno+"'>"+
								    								"<input type='hidden' id='hidsrp"+counter+"'  name='hidsrp"+counter+"' value='"+itemsrp+"'>"+
								    							"</td>";
								    	traddeditems	+=		"<td align='center'id='tditemno"+counter+"'>"+itemno+"</td>";
								    	traddeditems	+=		"<td id='tditemdesc"+counter+"'>"+itemdesc+"</td>";
								    	traddeditems	+=		"<td id='tditemstatus' class='centered'></td>";
								    	traddeditems	+=		"<td id='tditemqty'></td>";
								    	traddeditems	+=		"<td id='tditemgross'></td>";
								    	traddeditems	+=		"<td id='tdcurrstatus"+counter+"' align='center'>"+itemstatus+"</td>";
								    	traddeditems	+=		"<td align='center'id='tdrecqty"+counter+"'></td>";
								    	traddeditems	+=		"<td id='tdrecamt"+counter+"' align='right'></td>";
								    	traddeditems	+=		"<td align='center'><input type='text' id='txtgoodqty"+counter+"' name='txtgoodqty"+counter+"' size='5' class='txtgoodqty centered' data-curcnt='"+counter+"'></td>";
								    	traddeditems	+=		"<td align='center'><input type='text' id='txtdefqty"+counter+"' name='txtdefqty"+counter+"' size='5' class='txtdefqty centered' data-curcnt='"+counter+"'></td>";
								    	traddeditems	+=		"<td align='center'><input type='text' id='txtibqty"+counter+"' name='txtibqty"+counter+"' size='5' class='txtibqty centered' data-curcnt='"+counter+"'></td>";
								    	traddeditems	+=	"</tr>";
								    	txtaddeditemscnt++;
								    	$("#tblscanning tbody").append(traddeditems);
								    	$("#txtaddeditemscnt").val(txtaddeditemscnt);
								    	$(".trmposdtlsadded").tooltip();
						    		}
						    		var cnt	 			= + $("#trtotcnt").attr("data-cnt");
						    		txtaddeditemscnt 	= 	+ $("#txtaddeditemscnt").val();
					    			cnt					=	cnt + txtaddeditemscnt;
					    			
					    			var tditemno, goodqty, defqty,ibqty,scannedqty,unitprice,scannedamt,fortotscannedamt,recqty,recamt;
					    			var OAscannedqty = OAtotrecqty = OAtotrecamt = 0;
							        for(x=1; x < cnt; x++)
									{
										tditemno	=	$("#tditemno"+x).text();
										console.log(itemno +"-"+x);
										if(tditemno == itemno)
										{
											$("#trfound"+x).addClass('trfound');
											if($("#tdcurrstatus"+x).text() == "P")
											{
												$("#tdcurrstatus"+x).addClass('primeitem');
											}
											else
											{
												$("#tdcurrstatus"+x).removeClass('primeitem');
											}
											
											$("#txtgoodqty"+x).val(arr_itemqtydetails[1]);
											$("#txtdefqty"+x).val(arr_itemqtydetails[2]);
											$("#txtibqty"+x).val(arr_itemqtydetails[3]);
									
											unitprice	=  	+ $("#hidsrp"+x).val();
											scannedqty	=	+ arr_itemqtydetails[4];
											scannedamt	=	parseFloat((unitprice * scannedqty)).toFixed(2);
											
											$("#tdrecamt"+x).text(inputAmount.getNumberWithCommas(scannedamt));
											$("#txtrecqty"+x).val(scannedqty);
											$("#tdrecqty"+x).text(scannedqty);

											var element = document.getElementById("trfound"+x);
											element.scrollIntoView();
											element.scrollIntoView(false);
											element.scrollIntoView({block: "end"});
											element.scrollIntoView({block: "end", behavior: "smooth"});
										}
										if($("#"+destination_val+x).val() != undefined && $("#txtrecqty"+x).val() != undefined)
							    		{
								    		fortotscannedamt	=	+ ($("#"+destination_val+x).val()).replace(/,/g, '');
											recqty				=	+ ($("#txtrecqty"+x).val()).replace(/,/g, '');
											recamt				=	+ ($("#tdrecamt"+x).text()).replace(/,/g, '');
											
											OAscannedqty=	OAscannedqty + fortotscannedamt;
											OAtotrecqty	=	OAtotrecqty  + recqty;
											OAtotrecamt	=	OAtotrecamt	 + recamt;
							    		}
									}
									if(destination_val == "txtgoodqty")
									{
										$("#tdtotgoodqty").text(inputAmount.getNumberWithCommas(OAscannedqty));
									}
									if(destination_val == "txtdefqty")
									{
										$("#tdtotdefqty").text(inputAmount.getNumberWithCommas(OAscannedqty));
									}
									if(destination_val == "txtibqty")
									{
										$("#tdtotibqty").text(inputAmount.getNumberWithCommas(OAscannedqty));
									}
									$("#tdrecqty").text(inputAmount.getNumberWithCommas(OAtotrecqty));
									$("#tdrecamt").text(inputAmount.getNumberWithCommas(parseFloat(OAtotrecamt).toFixed(2)));
									$(".txtitemnos").val("");
									$(".txtqtys").val("1");
								}
							}
						});
					}
					else
					{
						$("#errmsg").text("Item not found.");
						$(".txtitemnos").val("");
						return;
					}
				}
			});
    	}
    });
	$("#divmposdtls").on("change",".txtgoodqty",function(){
    	var curcnt	= + $(this).attr("data-curcnt");
		scanningFuns.getNewTotals(curcnt, "txtgoodqty","tdtotgoodqty");
    });
	$("#divmposdtls").on("change",".txtdefqty",function(){
    	var curcnt	= + $(this).attr("data-curcnt");
		scanningFuns.getNewTotals(curcnt, "txtdefqty","tdtotdefqty");
    });
    $("#divmposdtls").on("change",".txtibqty",function(){
    	var curcnt	= + $(this).attr("data-curcnt");
		scanningFuns.getNewTotals(curcnt, "txtibqty","tdtotibqty");
    });
	$("#divmposdtls").on("dblclick",".trmposdtlsadded",function(){
    	var currcnt	=	+ $(this).attr("data-currcnt");
    	var itemno	=	$("#hiditemno"+currcnt).val();
	    	MessageType.confirmmsg(scanningFuns.deleteItem,"Do you want to delete the item "+itemno+"?",currcnt);
    });
    $("#divmposdtls").on("click","#btnadditem",function(){
    	$("#hdnscanmode").val("ADDITEM");
    });
//    $("#divmposdtls").on("click","#btnadditem",function(){
//    	var txtaddeditemscnt = + $("#txtaddeditemscnt").val();
//    		txtaddeditemscnt++;
//    	var traddeditems	=	"";
//    	var aboverowcnt		=	+ $(this).attr("data-aboverowcnt");
//    	var counter			=	(aboverowcnt-1)+ txtaddeditemscnt;
//    	traddeditems	=	"<tr id = 'trfound"+counter+"' class='trbody' style='font-size:12px;'>";
//    	traddeditems	+=		"<td align='center'>"+counter+"</td>";
//    	traddeditems	+=		"<td align='center'>"+
//    								"<input type='text' id='hiditemno"+counter+"' name='hiditemno"+counter+"' size='8' class='addeditem centered' data-curcnt='"+counter+"' data-adddtl='Y'>"+
//    								"<input type='hidden' id='hidsrp"+counter+"'  name='hidsrp"+counter+"'>"+
//    							"</td>";
//    	traddeditems	+=		"<td id='tditemdesc"+counter+"'></td>";
//    	traddeditems	+=		"<td id='tditemstatus'></td>";
//    	traddeditems	+=		"<td id='tditemqty'></td>";
//    	traddeditems	+=		"<td id='tditemgross'></td>";
//    	traddeditems	+=		"<td id='tdcurrstatus"+counter+"' align='center'></td>";
//    	traddeditems	+=		"<td align='center'><input type='text' id='txtrecqty"+counter+"' name='txtrecqty"+counter+"' size='5' class='txtaddedinputqty txtinputqty' data-curcnt='"+counter+"'></td>";
//    	traddeditems	+=		"<td id='tdrecamt"+counter+"' align='right'></td>";
//    	traddeditems	+=		"<td align='center'><input type='text' id='txtdefqty"+counter+"' name='txtdefqty"+counter+"' size='5' class='txtdefqty' data-curcnt='"+counter+"'></td>";
//    	traddeditems	+=		"<td align='center'><input type='text' id='txtibqty"+counter+"' name='txtibqty"+counter+"' size='5' class='txtibqty' data-curcnt='"+counter+"'></td>";
//    	traddeditems	+=	"</tr>";
//    	
//    	$("#tblscanning tbody").append(traddeditems);
//    	$("#txtaddeditemscnt").val(txtaddeditemscnt);
//    });
    $(".btnscans").click(function(){
    	var mode = $(this).attr("data-mode");
    	var bgid = $(this).attr("data-bgid");
    	$("#tdscanningmode").text(mode);
    	$("#errmsg").text("");
    });
});