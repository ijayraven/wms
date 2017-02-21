$("document").ready(function(){
	$("#btnsearchitems").click(function(){
		var txtsfrom	=	$("#txtsfrom").val();
		var txtsto		=	$("#txtsto").val();
		if(txtsfrom > txtsto || (txtsfrom == "" && txtsto != "") || (txtsfrom == "" && txtsto == ""))
		{
			MessageType.infoMsg("Invalid date range.");
		}
		else
		{
			$.ajax({
				url			:	"def_mto.php?action=CREATEEXMTO&txtsfrom="+txtsfrom+"&txtsto="+txtsto,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divtrxmto").html(response);
					$('#divtrxmto').dialog('option', 'title', 'DEFECTIVE RETURNS MTO CREATION');
					$('.diamto .ui-button-text:contains(Update)').text('Save');
					$("#divloader").dialog("close");
				}
			});
		}
	});
	$("#btncreate").click(function(){
		$("#trtrxno").hide();
		$("#divsearchitems").dialog("open");
	});
	$("#divrtnmto").on("keyup",".txtnoboxes",function(){
		getnumbersOnly(this.value,this.id);
	});
	$("#divrtnmto").on("keyup",".txtqty",function(){
		getnumbersOnly(this.value,this.id);
		var curcnt	=	$(this).attr("data-curcnt");
		var onhand	=	+	$("#tddonhandqty"+curcnt).text();
		if(this.value > onhand)
		{
			alert("Entered quantity must not be greater than onhand quantity.");
			$(this).val("");
		}
	});
	
	$("#btnreport").click(function(usequery){
		var dataform	=	$("#dataform").serialize();
		var txtmtono	=	$("#txtmtono").val();
		var selstatus	=	$("#selstatus").val();
		var mtodfrom	=	$("#mtodfrom").val();
		var mtodto		=	$("#mtodto").val();
//		var destination	=	$('input[name=rdodestination]:checked', '#dataform').val();
		var valid 		=	true;
		if(usequery != "YES")
		{
			if(txtmtono == "" && selstatus == "" && mtodfrom == "" && mtodto == "")
			{
				alert("Please select at least one criterion to search.");
				valid = false;
			}
			else
			{
				if((mtodfrom != "" && mtodto == "") || mtodfrom > mtodto)
				{
					alert("Invalid date range.");
					valid = false;
				}
			}
		}
		if(valid)
		{
			$.ajax({
				data		:dataform,
				type		:"POST",
				url			:"def_mto.php?action=GETMTO&USESESSIONQUERY="+usequery,
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
   $("#divrtnmto").on("click",".editbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	 $.ajax({
				type	:	"GET",
				url		:	"def_mto.php?action=EDITTRX&TRXNO="+TRXNO,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$("#divtrxmto").html(response);
					$("#divtrxmto").dialog("open");
			    	$('#divtrxmto').dialog('option', 'title', 'DEFECTIVE RETURNS MTO UPDATE');
			    	$('.diamto .ui-button-text:contains(Save)').text('Update');
					$("#divloader").dialog("close");
				}
			});
    });
     $("#divrtnmto").on("click",".postbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	if(confirm("You are about to post this transaction."))
    	{
	    	 $.ajax({
					type	:	"GET",
					url		:	"def_mto.php?action=POSTTRX&TRXNO="+TRXNO,
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
    });
     $("#divrtnmto").on("click",".cancelbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	if(confirm("You are about to cancel this transaction."))
    	{
	    	 $.ajax({
					type	:	"GET",
					url		:	"def_mto.php?action=CANCELTRX&TRXNO="+TRXNO,
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
    });
    $("#divrtnmto").on("click",".printbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	window.open("def_mto_pdf.php?TRXNO="+TRXNO);
    	window.open("def_mto__summary_pdf.php?TRXNO="+TRXNO);
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
				url		:	"def_mto.php?action=VIEWTRXDTLS&TRXNO="+TRXNO+"&COUNT="+COUNT,
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
    $("#divrtnmto").on("keyup",".txtnotype", function(){
    	var curcnt	=	$(this).attr("data-curcnt");
    	$("#txtboxlabel"+curcnt).val("");
    });
    $("#divrtnmto").on("click",".transmitbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	if(confirm("You are about to transmit this MTO."))
    	{
	    	 $.ajax({
					type	:	"GET",
					url		:	"def_mto.php?action=TRANSMITTRX&TRXNO="+TRXNO,
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
    });
});
function checkdup(curcnt,txtitemno)
{
	var cnt		=	$("#hidcnt").val();
	var valid	=	true;
	for(var a = 1; a <= cnt; a++)
	{
		if(curcnt != a)
		{
			if($("#txtitemno"+a).val() == txtitemno)
			{
				valid = false;
			}
		}
	}
	return valid;
}
function getnumbersOnly(value,id)
{
	var ValidChars ="0123456789";
	var IsNumber = "";
	var Char;
	
	for (var i=0; i < value.length; i++)
	{
		Char = value.charAt(i);
		if(ValidChars.indexOf(Char) != -1)
		{
			IsNumber = IsNumber + Char;
		}
	}
	document.getElementById(id).value = IsNumber;
}
function resettrx()
{
	var cnt		=	$("#hidcnt").val();
	var valid	=	true;
	for(var a = 2; a <= cnt; a++)
	{
		$("#tr"+a).remove();
	}
	$("#tdcnt1").text("1");
	$("#txtitemno1").val("");
	$("#txtqty1").val("");
	$("#txtboxes").val("");
	$("#txtpackages").val("");
	$("#tddescription1").text("");
	$("#tddonhandqty1").text("");
	$("#hidcnt").val(1);
}
function validateitems()
{
	var cnt			=	$("#hidcnt").val();
	var valid		=	true;
	var itemchecked	=	true;
	if($("#txtboxes").val() == "" && $("#txtpackages").val() == "")
	{
		alert("No. of boxes and packages are empty.");
		valid = false;
	}
	else
	{
		if(itemchecked == false)
		{
			alert("Please select item/s.");
			return;
		}
		else
		{
			if(valid)
			{
				return valid;
			}
			else
			{
				alert("Some fields are empty.");
			}
		}
	}
}
function savetrxmto(Updatemode)
{
	if(Updatemode == "Update")
	{
		updatetrx();
	}
	else
	{
//		var destination	=	$('input[name=rdodestination_C]:checked', '#dataform').val();
		var cnt				=	$("#hidcnt").val();
		var frmdata			=	$("#frmdata").serialize();
		var txtnoboxes		=	$("#txtboxes").val();
		var txtnopackages	=	$("#txtpackages").val();
		var txtsfrom		=	$("#txtsfrom").val();
		var txtsto			=	$("#txtsto").val();
		
		$.ajax({
				data		:frmdata,
				type		:"POST",
				url			:"def_mto.php?action=SAVETRXHDR&txtnoboxes="+txtnoboxes+"&txtnopackages="+txtnopackages+"&txtsfrom="+txtsfrom+"&txtsto="+txtsto,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response)
				{
					$("#divdebug").html(response);
					alert('Transaction has been successfully saved.');
					$("#divloader").dialog("close");
					$("#divtrxmto").dialog("close");
					$("#divsearchitems").dialog("close");
					resettrx();
				}
		});
	}
	
}
function updatetrx()
{
	var frmdata		=	$("#frmdata").serialize();
	var TRXNO		=	$("#tdtrxno").text();
	$.ajax({
			data		:frmdata,
			type		:"POST",
			url			:"def_mto.php?action=UPDATETRX&TRXNO="+TRXNO,
			beforeSend	:function(){
				$("#divloader").dialog("open");
			},
			success		:function(response){
				$("#divdebug").html(response);
				$("#divloader").dialog("close");
			}
		});
}