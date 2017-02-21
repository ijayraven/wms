$("document").ready(function(){
	$("#btncreate").click(function(){
		$("#trtrxno").hide();
		$.ajax({
			url			:	"ex_mto.php?action=CREATEEXMTO",
			beforeSend	:function(){
				$("#divloader").dialog("open");
			},
			success		:function(response){
				$("#divtrxmto").html(response);
				$('#divtrxmto').dialog('option', 'title', 'EXCLUSIVE RETURNS MTO CREATION');
				$('.ui-dialog-buttonpane button:contains(Update)').attr("id", "dia-btn-update");
				$('#dia-btn-update').html("Save");
				$("#divtrxmto").dialog("open");
				$("#divloader").dialog("close");
			}
		});
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
			    	$('.ui-dialog-buttonpane button:contains(Save)').attr("id", "dia-btn-save");
				    $('#dia-btn-save').html("Update");
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
    	}
    });
    $("#divrtnmto").on("click",".cancelbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	if(confirm("You are about to cancel this transaction."))
    	{
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
    	}
    });
    $("#divrtnmto").on("click",".printbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	window.open("ex_mto_pdf.php?TRXNO="+TRXNO);
    	window.open("ex_mto__summary_pdf.php?TRXNO="+TRXNO);
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
    $("#divrtnmto").on("click",".transmitbtn",function(){
    	var TRXNO	=	$(this).attr("data-trxno");
    	if(confirm("You are about to transmit this MTO."))
    	{
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
    });
});
function validateitems()
{
	var cnt			=	$("#hidcnt").val();
	var valid		=	true;
	var itemchecked	=	false;
	
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
		var txtitemno;
		var txtqty;
		var txtnoboxes;
		var txtnopackages;
		var txtboxlabel;
		$("#divloader").dialog("open");
		$.ajax({
				data		:frmdata,
				type		:"POST",
				url			:"ex_mto.php?action=SAVETRXHDR",
				beforeSend	:function(){
					
				},
				success		:function(response)
				{
					$("#divdebug").html(response);
					var response2;
					for(var a = 1; a < cnt; a++)
					{
						txtitemno		=	$("#txtitemno"+a).val();
						txtqty			=	$("#txtqty"+a).val();
//						txtnoboxes		=	$("#txtnoboxes"+a).val();
//						txtnopackages	=	$("#txtnopackages"+a).val();
//						txtboxlabel		=	$("#txtboxlabel"+a).val();
						if($("#txtitemno"+a).is(":checked"))
						{
							$.ajax({
										type		:"GET",
										url			:"ex_mto.php?action=SAVETRXDTLS&TRXNO="+response+"&txtitemno="+txtitemno+"&txtqty="+txtqty+"&txtnoboxes="+txtnoboxes+
													 "&txtnopackages="+txtnopackages,
										beforeSend	:function(){
											
										},
										success		:function(response2){
											$("#divdebug").html(response2);
										}
							});
						}
					}
					alert('Transaction has been successfully saved.');
					$("#divloader").dialog("close");
					$("#divtrxmto").dialog("close");
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
			url			:"ex_mto.php?action=UPDATETRX&TRXNO="+TRXNO,
			beforeSend	:function(){
				$("#divloader").dialog("open");
			},
			success		:function(response){
				$("#divdebug").html(response);
				$("#divloader").dialog("close");
			}
		});
}