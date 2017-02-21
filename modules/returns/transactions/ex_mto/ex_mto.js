function EX_MTO(){}
	EX_MTO.prototype = {
		constructor:EX_MTO,
		searchItems:function(){
			var txtsfrom	=	$("#txtsfrom").val();
			var txtsto		=	$("#txtsto").val();
			if(txtsfrom > txtsto || (txtsfrom == "" && txtsto != "") || (txtsfrom == "" && txtsto == ""))
			{
				MessageType.infoMsg("Invalid date range.");
			}
			else
			{
				$.ajax({
					url			:	"ex_mto.php?action=CREATEEXMTO&txtsfrom="+txtsfrom+"&txtsto="+txtsto,
					beforeSend	:function(){
						$("#divloader").dialog("open");
					},
					success		:function(response){
						$("#divtrxmto").html(response);
						$('#divtrxmto').dialog('open'); 
						$('#divtrxmto').dialog('option', 'title', 'EXCLUSIVE RETURNS MTO CREATION');
						$('.diamto .ui-button-text:contains(Update)').text('Save');
						$("#divloader").dialog("close");
					}
				});
			}
		},
		validateItems:function(){
			var boxno		=	$("#txtboxes").val();	
			var packageno	=	$("#txtpackages").val();
			var cnt			=	$("#hidcnt").val();
			var valid		=	true;
			var noselected	=	true;
			var txtqty		=	0;
			
			if(boxno == "" && packageno == "")
			{
				MessageType.infoMsg("Box No. and Package No. are both empty.");
				return false;
			}
			else
			{
				for(var a = 1; a < cnt; a++)
				{
					if($("#txtitemno"+a).is(":checked"))
					{
						txtqty 			= + $("#txtqty"+a).val();
						if(txtqty == "" || txtqty == 0)
						{
							valid = false;
							$("#tr"+a).addClass("err-background");
						}
						else
						{
							$("#tr"+a).removeClass("err-background");
						}
						noselected = false;
					}
				}
				if(noselected)
				{
					MessageType.infoMsg("Please select item/s.");
					return;
				}
				if(valid)
				{
					return true;
				}
				else
				{
					MessageType.infoMsg("Some quantities are left with empty/zero value.");
					return false;
				}
			}
		},
		saveTrx:function(mode){
			$(".txtitemnos").removeAttr("disabled");
			var frmdata			=	$("#frmdata").serialize();
			var txtsfrom		=	$("#txtsfrom").val();
			var txtsto			=	$("#txtsto").val();
			var trxno			=	$("#tdtrxno").text();
			var hidcnt			=	$("#hidcnt").val();
			var txtboxes		=	$("#txtboxes").val();
			var txtpackages		=	$("#txtpackages").val();
			
			$.ajax({
				data		:frmdata,
				type		:"POST",
				url			:"ex_mto.php?action=SAVETRX&mode="+mode+"&trxno="+trxno,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(TRXNO)
				{
					$("#divdebug").html(TRXNO);
					var txtitemno = "";
					var txtqty = "";
					for(var a = 1; a < hidcnt; a++)
					{
						if($("#txtitemno"+a).is(":checked"))
						{
							txtitemno 	= $("#txtitemno"+a).val();
							txtqty 		= $("#txtqty"+a).val();
							$.ajax({
								type		:"POST",
								url			:"ex_mto.php?action=SAVETRXDTLS&txtitemno="+txtitemno+"&txtqty="+txtqty+"&mode="+mode+"&TRXNO="+TRXNO+"&a="+a+"&txtboxes="+
											  txtboxes+"&txtpackages="+txtpackages+"&txtsfrom="+txtsfrom+"&txtsto="+txtsto,
								success		:function(totcnt)
								{
									$("#divdebug").html(totcnt);
									$("#divdebug").html("");
								}
							});
						}
						if(a == hidcnt - 1)
						{
							$("#divloader").dialog("close");
							MessageType.successMsg('Transaction has been successfully ' + mode +"d.");
							$('#divtrxmto').dialog('close');
							$('#divsearchitems').dialog('close');
							if(mode == "Update")
							{
								$('#btnreport').trigger('click',['YES']);
							}
						}
					}
				}
			});
		},
		cancelTrx:function(TRXNO){
			 $.ajax({
					type	:	"GET",
					url		:	"ex_mto.php?action=CANCELTRX&TRXNO="+TRXNO,
					beforeSend:	function()
					{
						$("#divloader").dialog("open");
					},
					success	:function(response)
					{
						$("#divdebug").html(response);
						$("#divloader").dialog("close");
					}
				});
		},
		postTrx:function(TRXNO){
			$.ajax({
					type	:	"GET",
					url		:	"ex_mto.php?action=POSTTRX&TRXNO="+TRXNO,
					beforeSend:	function()
					{
						$("#divloader").dialog("open");
					},
					success	:function(response)
					{
						$("#divdebug").html(response);
						$("#divloader").dialog("close");
					}
				});
		},
		printTrx:function(TRXNO){
			$.ajax({
				type	:	"GET",
				url		:	"ex_mto.php?action=PRINTTRX&TRXNO="+TRXNO,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
		},
		transmitTrx:function(TRXNO){
			$.ajax({
					type	:	"GET",
					url		:	"ex_mto.php?action=TRANSMITTRX&TRXNO="+TRXNO,
					beforeSend:	function()
					{
						$("#divloader").dialog("open");
					},
					success	:function(response)
					{
						$("#divdebug").html(response);
						$("#divloader").dialog("close");
					}
			});
		}
	}
var EX_MTO_funcs	=	new EX_MTO();
	
$("document").ready(function(){
	$("#btncreate").click(function(){
		$("#trtrxno").hide();
		$("#divsearchitems").dialog("open");
	});
	$("#divtrxmto").on("keyup",".txtqty",function(){
		var curcnt	=	$(this).attr("data-curcnt");
		var onhand	=	+	$("#tddonhandqty"+curcnt).text();
		var thisval	=	+	$(this).val();
		if(thisval > onhand)
		{
			MessageType.infoMsg("Received quantity must not be greater than onhand quantity.");
			$(this).val("");
		}
	});
	$("#btnreport").click(function(usequery){
		var dataform	=	$("#dataform").serialize();
		var txtmtono	=	$("#txtmtono").val();
		var selstatus	=	$("#selstatus").val();
		var mtodfrom	=	$("#mtodfrom").val();
		var mtodto		=	$("#mtodto").val();
		var valid 		=	true;
		if(usequery != "YES")
		{
			if(txtmtono == "" && selstatus == "" && mtodfrom == "" && mtodto == "")
			{
				MessageType.infoMsg("Please select at least one criterion to search.");
				valid = false;
			}
			else
			{
				if((mtodfrom != "" && mtodto == "") || mtodfrom > mtodto)
				{
					MessageType.infoMsg("Invalid date range.");
					valid = false;
				}
			}
		}
		if(valid)
		{
			$.ajax({
				data		:dataform,
				type		:"POST",
				url			:"ex_mto.php?action=GETMTO&USESESSIONQUERY="+usequery,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divrtnmto").html(response);
					$("#divloader").dialog("close");
					$(".tdtrxdtlsClass").hide();
				}
			});
		}
	});
	$("#divrtnmto").on("click",".tdtrxdtls",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	var COUNT	=	$(this).attr("data-count");
    	var tdtext	=	$("#tdtrxdtls"+COUNT).html();
			tdtext	=	tdtext.trim();
		if(tdtext == "")
		{	
		    $.ajax({
				type	:	"GET",
				url		:	"ex_mto.php?action=VIEWTRXDTLS&TRXNO="+TRXNO+"&COUNT="+COUNT,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$(".tdtrxdtlsClass").html("");
					$("#tdtrxdtls"+COUNT).html(response);
					$("#tdtrxdtls"+COUNT).show();
					$(".trdtls").removeClass("activetr");
					$("#trdtls"+COUNT).addClass("activetr");
					$("#divloader").dialog("close");
				}
			});
		}
		else
		{
			$(".tdtrxdtlsClass").hide();
			$("#trdtls"+COUNT).removeClass("activetr");
			$("#tdtrxdtls"+COUNT).html("");
		}
    });
    $("#divrtnmto").on("click",".editbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	 $.ajax({
				type	:	"GET",
				url		:	"ex_mto.php?action=EDITTRX&TRXNO="+TRXNO,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$("#divtrxmto").html(response);
					$("#divtrxmto").dialog("open");
			    	$('#divtrxmto').dialog('option', 'title', 'EXCLUSIVE RETURNS MTO UPDATE');
			    	$('.diamto .ui-button-text:contains(Save)').text('Update');
					$("#divloader").dialog("close");
				}
			});
    });
    $("#divrtnmto").on("click",".cancelbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	MessageType.confirmmsg(EX_MTO_funcs.cancelTrx,"Do you want to cancel this transaction?",TRXNO);
    });
    $("#divrtnmto").on("click",".postbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	MessageType.confirmmsg(EX_MTO_funcs.postTrx,"Do you want to post this transaction?",TRXNO);
    });
    $("#divrtnmto").on("click",".printbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	MessageType.confirmmsg(EX_MTO_funcs.printTrx,"Do you want to print this transaction?",TRXNO);
    });
    $("#divrtnmto").on("click",".transmitbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	MessageType.confirmmsg(EX_MTO_funcs.transmitTrx,"Do you want to transmit this transaction?",TRXNO);
    });
});
