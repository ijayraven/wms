$("document").ready(function(){
	$("#btncreate").click(function(){
		$("#trtrxno").hide();
		$('#divtrxnonmtoitems').dialog('option', 'title', 'EXCLUSIVE ITEMS CREATION');
		$('.ui-dialog-buttonpane button:contains(Update)').attr("id", "dia-btn-update");
		$('#dia-btn-update').html("Save");
		$("#divtrxnonmtoitems").dialog("open");
	});
	$("#btnsearch").click(function(){
		var dataform	=	$("#dataform").serialize();
		var trxno		=	$("#txttrxno").val();
		var itemno		=	$("#txtitemno").val();
		var Cdfrom		=	$("#Cdfrom").val();
		var Cdto		=	$("#Cdto").val();
		var Pdfrom		=	$("#Pdfrom").val();
		var Pdto		=	$("#Pdto").val();
		var selstatus	=	$("#selstatus").val();
		if(trxno == "" && itemno == "" && Cdfrom == "" && Cdto == "" && Pdfrom == "" && Pdto == "" && selstatus == "")
		{
			alert("PLease select at least one criterion to search.");
		}
		else if((Cdfrom == "" && Cdto!= "") || Cdfrom > Cdto)
		{
			alert("Invalid created date range.");
		}
		else if((Pdfrom == "" && Pdto!= "") || Pdfrom > Pdto)
		{
			alert("Invalid posted date range.");
		}
		else
		{
			$.ajax({
				data		:dataform,
				type		:"POST",
				url			:"nonmtoitems.php?action=SEARCHTRX",
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divTRX").html(response);
					$("#divloader").dialog("close");
					$(".tdtrxdtlsClass").hide();
				}
			});
		}
	});
	$(".txtitemnos").live("change",function(){
		var itemno	=	$(this).val();
		var curcnt	=	$(this).attr("data-curcnt");
		if(checkdup(curcnt,itemno))
		{
			$.ajax({
				url			:"nonmtoitems.php?action=GETITEMDESC&ITEMNO="+itemno+"&CURCNT="+curcnt,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
		}
		else
		{
			alert("Duplicate Item No.");
			$(this).val("");
		}
	});
	$(".addbtn").live("click",function(){
		var cnt	=	+ $("#hidcnt").val();
		var newcnt 	= cnt + 1;
		var newtr	=	"";
		newtr	=	"<tr id='tr"+newcnt+"' class='trbody'>";
		newtr	+=		"<td id='tdcurcnt"+newcnt+"' align='center'>"+newcnt+"</td>";
		newtr	+=		"<td><input type='text' id='txtitemno"+newcnt+"' name='txtitemno"+newcnt+"' size='10' class='txtitemnos centered' data-curcnt = '"+newcnt+"'></td>";
		newtr	+=		"<td id='tditemdesc"+newcnt+"'>&nbsp;</td>";
		newtr	+=		"<td id='tdsrp"+newcnt+"' align='center'>&nbsp;</td>";
		newtr	+=		"<td align='center'><img src='/wms/images/images/action_icon/new/stop.png' class='smallbtns rembtn' title='Remove Row' data-curcnt = '"+newcnt+"'></td>";
		newtr	+=	"</tr>";
		
		$("#tbltrxnonmtoitems tbody").append(newtr);
		$("#hidcnt").val(newcnt);
	});
	$(".rembtn").live("click",function(){
		var curcnt		=	$(this).attr("data-curcnt");
		$("#tr"+curcnt).remove()
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
				url		:	"nonmtoitems.php?action=VIEWTRXDTLS&TRXNO="+TRXNO+"&COUNT="+COUNT,
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
    $(".editbtn").live("click",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	$("#trtrxno").show();
    	$("#tdtrxno").text(TRXNO);
    	$('#divtrxnonmtoitems').dialog('option', 'title', 'EXCLUSIVE ITEMS UPDATE');
    	$('.ui-dialog-buttonpane button:contains(Save)').attr("id", "dia-btn-save");
	    $('#dia-btn-save').html("Update");
    	 $.ajax({
				type	:	"GET",
				url		:	"nonmtoitems.php?action=EDITTRX&TRXNO="+TRXNO,
				beforeSend:	function()
				{
					$("#divloader").dialog("open");
				},
				success	:function(response)
				{
					$("#divdebug").html(response);
					$("#divtrxnonmtoitems").dialog("open");
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
					url		:	"nonmtoitems.php?action=POSTTRX&TRXNO="+TRXNO,
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
    	var frmcancel	=	$("#frmcancel").serialize();
    	var cnt			=	$(this).attr("data-cnt");
    	if(confirm("You are about to cancel the selected item/s."))
    	{
	    	 $.ajax({
					type	:	"POST",
					data	:	frmcancel,
					url		:	"nonmtoitems.php?action=CANCELITEM&CNT="+cnt,
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
function checkdup(curcnt,itemno)
{
	var cnt		=	$("#hidcnt").val();
	var valid	=	true;
	for(var a = 1; a <= cnt; a++)
	{
		if(curcnt != a)
		{
			if($("#txtitemno"+a).val() == itemno)
			{
				valid = false;
			}
		}
	}
	return valid;
}
function validateitems()
{
	var cnt		=	$("#hidcnt").val();
	var valid	=	true;
	for(var a = 1; a <= cnt; a++)
	{
		if($("#txtitemno"+a).val() == "")
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
	$("#tditemdesc1").text("");
	$("#tdsrp1").text("");
	$("#hidcnt").val(1);
}
function savetrxnonmtoitems(Updatemode)
{
	if(Updatemode == "Update")
	{
		updatetrxnonmtoitems();
	}
	else
	{
		var frmdata		=	$("#frmdata").serialize();
		$.ajax({
				data		:frmdata,
				type		:"POST",
				url			:"nonmtoitems.php?action=SAVETRX",
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
function updatetrxnonmtoitems()
{
	var frmdata		=	$("#frmdata").serialize();
	var TRXNO		=	$("#tdtrxno").text();
	$.ajax({
			data		:frmdata,
			type		:"POST",
			url			:"nonmtoitems.php?action=UPDATETRX&TRXNO="+TRXNO,
			beforeSend	:function(){
				$("#divloader").dialog("open");
			},
			success		:function(response){
				$("#divdebug").html(response);
				$("#divloader").dialog("close");
			}
		});
}