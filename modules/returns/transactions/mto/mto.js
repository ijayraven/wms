$("document").ready(function(){
	$("#btncreate").click(function(){
		$("#trtrxno").hide();
		$('#divtrxmto').dialog('option', 'title', 'RETURNS MTO CREATION');
		$('.ui-dialog-buttonpane button:contains(Update)').attr("id", "dia-btn-update");
		$('#dia-btn-update').html("Save");
		$("#divtrxmto").dialog("open");
	});
	$(".addbtn").live("click",function(){
		var cnt	=	+ $("#hidcnt").val();
		var newcnt 	= cnt + 1;
		var newtr	=	"";
		newtr	=	"<tr id='tr"+newcnt+"' class='trbody'>";
		newtr	+=		"<td id='tdcurcnt"+newcnt+"' align='center'>"+newcnt+"</td>";
		newtr	+=		"<td><input type='text' id='txtmposno"+newcnt+"' name='txtmposno"+newcnt+"' size='10' class='txtmposnos centered' data-curcnt = '"+newcnt+"'></td>";
		newtr	+=		"<td id='tdcustomer"+newcnt+"'>&nbsp;</td>";
		newtr	+=		"<td align='center'><input type='text' id='txtnoboxes"+newcnt+"' 	name='txtnoboxes"+newcnt+"' 	size='10' class='txtnoboxes txtnotype centered' 	data-curcnt = '"+newcnt+"'></td>";
		newtr	+=		"<td align='center'><input type='text' id='txtnopackages"+newcnt+"' name='txtnopackages"+newcnt+"' 	size='10' class='txtnopackages txtnotype centered' 	data-curcnt = '"+newcnt+"'></td>";
		newtr	+=		"<td align='center'><input type='text' id='txtboxlabel"+newcnt+"' 	name='txtboxlabel"+newcnt+"' 	size='10' class='txtboxlabel centered' 				data-curcnt = '"+newcnt+"'></td>";
		newtr	+=		"<td align='center'><img src='/wms/images/images/action_icon/new/stop.png' class='smallbtns rembtn' title='Remove Row' data-curcnt = '"+newcnt+"'></td>";
		newtr	+=	"</tr>";
		
		$("#tbltrxnonmto tbody").append(newtr);
		$("#hidcnt").val(newcnt);
	});
	$(".rembtn").live("click",function(){
		var curcnt		=	$(this).attr("data-curcnt");
		$("#tr"+curcnt).remove()
	});
	$(".txtmposnos").live("change",function(){
		var txtmposno	=	$(this).val();
		var curcnt		=	$(this).attr("data-curcnt");
		if(checkdup(curcnt,txtmposno))
		{
			$.ajax({
				url			:"mto.php?action=GETMPOS&MPOSNO="+txtmposno+"&CURCNT="+curcnt,
				beforeSend	:function(){
//					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
//					$("#divloader").dialog("close");
				}
			});
		}
		else
		{
			alert("Duplicate MPOS No.");
			$(this).val("");
			$('#tdcustomer'+curcnt).text('');
		}
	});
	$(".txtnoboxes").live("keyup",function(){
		getnumbersOnly(this.value,this.id);
	});
	$(".txtnopackages").live("keyup",function(){
		getnumbersOnly(this.value,this.id);
	});
	$("#btnreport").click(function(usequery){
		var dataform	=	$("#dataform").serialize();
		var txtmtono	=	$("#txtmtono").val();
		var selstatus	=	$("#selstatus").val();
		var mtodfrom	=	$("#mtodfrom").val();
		var mtodto		=	$("#mtodto").val();
		var destination	=	$('input[name=rdodestination]:checked', '#dataform').val();
		var valid 		=	true;
		if(usequery != "YES")
		{
			if(txtmtono == "" && selstatus == "" && mtodfrom == "" && mtodto == "" && destination == undefined)
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
				url			:"mto.php?action=GETMTO&USEQUERY="+usequery,
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
   $(".editbtn").live("click",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	$("#trtrxno").show();
    	$("#tdtrxno").text(TRXNO);
    	$('#divtrxmto').dialog('option', 'title', 'RETURNS MTO UPDATE');
    	$('.ui-dialog-buttonpane button:contains(Save)').attr("id", "dia-btn-save");
	    $('#dia-btn-save').html("Update");
    	 $.ajax({
				type	:	"GET",
				url		:	"mto.php?action=EDITTRX&TRXNO="+TRXNO,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$("#divdebug").html(response);
					$("#divtrxmto").dialog("open");
					$("#divloader").dialog("close");
				}
			});
    });
    $(".postbtn").live("click",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	if(confirm("You are about to post this transaction."))
    	{
	    	 $.ajax({
					type	:	"GET",
					url		:	"mto.php?action=POSTTRX&TRXNO="+TRXNO,
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
    $(".cancelbtn").live("click",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	if(confirm("You are about to cancel this transaction."))
    	{
	    	 $.ajax({
					type	:	"GET",
					url		:	"mto.php?action=CANCELTRX&TRXNO="+TRXNO,
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
    $(".transmitbtn").live("click",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	var destination	=	$(this).attr("data-destination");
    	
    	if(confirm("You are about to transmit this MTO."))
    	{
	    	 $.ajax({
					type	:	"GET",
					url		:	"mto.php?action=TRANSMITTRX&TRXNO="+TRXNO+"&DESTINATION="+destination,
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
    $(".printbtn").live("click",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	window.open("mto_pdf.php?TRXNO="+TRXNO);
    	window.open("mto__summary_pdf.php?TRXNO="+TRXNO);
    });
    $(".tdtrxdtls").live("click",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	var COUNT	=	$(this).attr("data-count");
    	var tdtext	=	$("#tdtrxdtls"+COUNT).html();
			tdtext	=	tdtext.trim();
		if(tdtext == "")
		{	
		    $.ajax({
				type	:	"GET",
				url		:	"mto.php?action=VIEWTRXDTLS&TRXNO="+TRXNO+"&COUNT="+COUNT,
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
					$(".tdtrxdtlsdtlsClass").hide();
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
    $(".tdtrxdtlsdtls").live("click",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	var COUNT	=	$(this).attr("data-count");
    	var MPOSNO	=	$(this).attr("data-mposno");
    	var tdtext	=	$("#tdtrxdtlsdtls"+COUNT).html();
			tdtext	=	tdtext.trim();
		if(tdtext == "")
		{	
		    $.ajax({
				type	:	"GET",
				url		:	"mto.php?action=VIEWTRXDTLSDTLS&TRXNO="+TRXNO+"&COUNT="+COUNT+"&MPOSNO="+MPOSNO,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$(".tdtrxdtlsdtlsClass").html("");
					$("#tdtrxdtlsdtls"+COUNT).html(response);
					$("#tdtrxdtlsdtls"+COUNT).show();
					$(".trdtlsdtls").removeClass("activetr-dtls");
					$("#trdtlsdtls"+COUNT).addClass("activetr-dtls");
					$("#divloader").dialog("close");
				}
			});
		}
		else
		{
			$(".tdtrxdtlsdtlsClass").hide();
			$("#trdtlsdtls"+COUNT).removeClass("activetr-dtls");
			$("#tdtrxdtlsdtls"+COUNT).html("");
		}
    });
    $(".txtboxlabel").live("keyup", function(e){
    	if(e.keyCode != 9)
    	{
	    	var curcnt	=	$(this).attr("data-curcnt");
	    	$("#txtnoboxes"+curcnt).val("");
	    	$("#txtnopackages"+curcnt).val("");
    	}
    });
    $(".txtnotype").live("keyup", function(){
    	var curcnt	=	$(this).attr("data-curcnt");
    	$("#txtboxlabel"+curcnt).val("");
    });
});
function checkdup(curcnt,txtmposno)
{
	var cnt		=	$("#hidcnt").val();
	var valid	=	true;
	for(var a = 1; a <= cnt; a++)
	{
		if(curcnt != a)
		{
			if($("#txtmposno"+a).val() == txtmposno)
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
	$("#txtmposno1").val("");
	$("#txtnoboxes1").val("");
	$("#txtnopackages1").val("");
	$("#tdcustomer1").text("");
	$("#txtboxlabel1").val("");
	$("#hidcnt").val(1)
}
function validateitems()
{
	var cnt		=	$("#hidcnt").val();
	var valid	=	true;
	
	for(var a = 1; a <= cnt; a++)
	{
		if($("#txtmposno"+a).val() == "" || ($("#txtnoboxes"+a).val() == "" && $("#txtnopackages"+a).val() == "" && $("#txtboxlabel"+a).val() == ""))
		{
			valid = false;
			$("#tr"+a).addClass("err-background");
		}
		else
		{
			$("#tr"+a).removeClass("err-background");
		}
	}
	return valid;
}
function savetrxmto(Updatemode)
{
	if(Updatemode == "Update")
	{
		updatetrx();
	}
	else
	{
		var frmdata		=	$("#frmdata").serialize();
		$.ajax({
				data		:frmdata,
				type		:"POST",
				url			:"mto.php?action=SAVETRX",
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
function updatetrx()
{
	var frmdata		=	$("#frmdata").serialize();
	var TRXNO		=	$("#tdtrxno").text();
	$.ajax({
			data		:frmdata,
			type		:"POST",
			url			:"mto.php?action=UPDATETRX&TRXNO="+TRXNO,
			beforeSend	:function(){
				$("#divloader").dialog("open");
			},
			success		:function(response){
				$("#divdebug").html(response);
				$("#divloader").dialog("close");
			}
		});
}