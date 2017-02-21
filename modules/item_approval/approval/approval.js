function ItemApproval(){}
	ItemApproval.prototype = {
		constructor:ItemApproval,

		GETTRX:function(USEPREVQRY){
			var frmsearch	=	$("#frmsearch").serialize();
			$.ajax({
				type		:"POST",
				data		:frmsearch,
				url			:"approval.php?action=SEARCHPO&USEPREVQRY="+USEPREVQRY,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divTRXlist").html(response);
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter({
					 	headers: {7: { sorter: false } }
					});
					$(".tooltips").tooltip();
					$(".btncheck").button({icons: {primary: "ui-icon ui-icon-check"}});
				}
			});
		},
		receiveTRX:function(){
			var frmtrx	=	$('#frmtrx').serialize();
			$.ajax({
				type		:"POST",
				data		:frmtrx,
					url			:"approval.php?action=RECEIVETRX",
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
		},
		checkitems:function(){
			var isselecteddis	=	false;
			var isselectedapp	=	false;
			var qtyindex;
			
			$(".chkappitems").each(function(){
				if($(this).is(":checked") == true)
				{
					isselectedapp 	= true;
				}
			});
			$(".chkdisitems").each(function(){
				if($(this).is(":checked") == true)
				{
					isselecteddis 	= true;
				}
			});
			if(isselectedapp || isselecteddis)
			{
				MessageType.confirmmsg(ItemApprovalFuncs.SaveAppDis,"Do you want to save this transaction?","");
			}
			else
			{
				MessageType.infoMsg("Please select item/s to approve/disapprove.");
			}
		},
		SaveAppDis:function(){
			var frmappdis	=	$('#frmappdis').serialize();
			var trxnum		=	$("#tdtrxnum").text();
			$.ajax({
				type		:"POST",
				data		:frmappdis,
					url			:"approval.php?action=SAVEAPPDIS&TRXNUM="+trxnum,
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
var ItemApprovalFuncs	=	new ItemApproval();
$("document").ready(function(){
	$("#btnsearch").click(function(){
		var txtitemno	=	$("#txtitemno").val();
		var txtPONO		=	$("#txtPONO").val();
		var txtvendorno	=	$("#txtvendorno").val();
		var txttrxno	=	$("#txttrxno").val();
		var selstatus	=	$("#selstatus").val();
		var seldtype	=	$("#seldtype").val();
		var txtdfrom	=	$("#txtdfrom").val();
		var txtdto		=	$("#txtdto").val();
		if(txtitemno == "" && txtPONO == "" && txtvendorno == "" && txttrxno == "" && selstatus == "" && seldtype == "" && txtdfrom == "" && txtdto == "")
		{
			MessageType.infoMsg("Please select at least one criterion to search.");
		}
		else
		{
			if((txtdto != "" && txtdfrom == "") || txtdfrom > txtdto)
			{
				MessageType.infoMsg("Invalid date range.");
				return;
			}
			if(txtdfrom != "" && txtdto != "" && seldtype == "")
			{
				MessageType.infoMsg("Please select date type.");
				return;
			}
			if(txtdfrom == "" && txtdto == "" && seldtype != "")
			{
				MessageType.infoMsg("Please input date range.");
				return;
			}
			ItemApprovalFuncs.GETTRX();
		}
	});
	$(".searchitem").keyup(function(evt){
		var txtitemno	=	$('#txtitemno').val();
		var txtitemdesc	=	$('#txtitemdesc').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(txtitemno != '' || txtitemdesc!= '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'approval.php?action=Q_SEARCHITEM&ITEMNO='+txtitemno+'&ITEMDESC='+txtitemdesc,
						beforeSend	:	function()
									{
									},
						success		:	function(response)
									{
										if(response == '')
										{
											$('#divselitem').html('');
											MessageType.infoMsg("Item not found.");
											$(".searchitem").val("");
										}
										else
										{
											$('#divselitem').html(response);
											var position =$("#txtitemno").position();
											var selwidth	=	$("#txtitemno").width() + $("#txtitemdesc").width()+12;
											$("#divselitem").css({position:'absolute'});
											$('#divselitem').show();
											$('#selitem').css({width:selwidth});
										}
									}
				});
			}
			else if(evthandler == 40 && $('#divselitem').html() != '')
			{
				$('#selitem').focus();
			}
			else
			{
				$('#divselitem').html('');
			}
		}
		else
		{
			$('#divselitem').html('');
			$('#divitem').html('');
		}
	});
	$(".searchvend").keyup(function(evt){
		var txtvendno	=	$('#txtvendorno').val();
		var txtvendname	=	$('#txtvendorname').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		
		if(txtvendno != '' || txtvendname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'approval.php?action=Q_SEARCHVEND&VENDNO='+txtvendno+'&VENDNAME='+txtvendname,
						beforeSend	:	function()
									{
									},
						success		:	function(response)
									{
										if(response == '')
										{
											$('#divselvend').html('');
											MessageType.infoMsg("CusTomer not found.");
											$(".searchvend").val("");
										}
										else
										{
											$('#divselvend').html(response);
											var position =$("#txtvendno").position();
											var selwidth	=	$("#txtvendorno").width() + $("#txtvendorname").width()+12;
											$("#divselvend").css({position:'absolute'});
											$('#divselvend').show();
											$('#selvend').css({width:selwidth});
										}
									}
				});
			}
			else if(evthandler == 40 && $('#divselvend').html() != '')
			{
				$('#selvend').focus();
			}
			else
			{
				$('#divselvend').html('');
			}
		}
		else
		{
			$('#divselvend').html('');
		}
	});
	$("#divTRXlist").on("click","#btnreceivetrx",function(){
		var frmtrx	=	$('#frmtrx').serialize();
		var selected	=	false;
		$('.chktrxs').each(function () 
		{
        	if($(this).is(":checked"))
        	{
        		selected	=	true;
        	}
		});
		if(!selected)
		{
			MessageType.infoMsg("No transaction selected to receive.");
		}
		else
		{
			MessageType.confirmmsg(ItemApprovalFuncs.receiveTRX,"Do you want to receive the selected transaction/s?","");
		}
	});
	$("#divTRXlist").on("click",".approvebtn",function(){
		var transno	=	$(this).attr("data-trxno");
		$.ajax({
				url			:"approval.php?action=GETDTLSTOAPPR&TRANSNO="+transno,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divtrxdtls").html(response);
					$("#divtrxdtls").dialog("open");
					$("#divloader").dialog("close");
				}
			});
		
	});
	$("#divTRXlist").on("click",".postbtn",function(){
		var transno	=	$(this).attr("data-trxno");
		$.ajax({
				url			:"approval.php?action=CLOSETRX&TRANSNO="+transno,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
	});
	$("#divTRXlist").on("click",".tdtrxdtls",function(){
		var transno	=	$(this).attr("data-trxno");
		var status	=	$(this).attr("data-status");
		var count	=	$(this).attr("data-count");
		if(status != "FOR RECEIVING")
		{
			if(! $("#trtrxdtlsdtls"+count).length)
			{
				$(".trtrxdtlsdtls").remove();
				$(".trtrxdtls").removeClass("activetr");
				$("#trtrxdtls"+count).addClass("activetr");
				
				var trdtlsdtls = "";
				trdtlsdtls	=	"<tr id='trtrxdtlsdtls"+count+"' class='trtrxdtlsdtls trbody'>";
				trdtlsdtls	+=		"<td id='tdtrxdtlsdtls"+count+"' colspan='8' align='center'></td>";
				trdtlsdtls	+=	"</tr>";
				$("#trtrxdtls"+count).after(trdtlsdtls);
				
				$.ajax({
					url			:"approval.php?action=GETDTLS&TRANSNO="+transno,
					beforeSend	:function(){
						$("#divloader").dialog("open");
					},
					success		:function(response){
						$("#tdtrxdtlsdtls"+count).html(response);
						$("#divloader").dialog("close");
						$(".tablesorter").dragtable({
							excludeFooter:true,
							dragaccept:'.tdaccept',
							dragHandle:'.some-handle'
						});
						$(".tblresul-tbltdtls").tablesorter();
					}
				});
			}
			else
			{
				$("#trtrxdtlsdtls"+count).remove();
				$(".trtrxdtls").removeClass("activetr");
			}
		}
	});
	$("#divtrxdtls").on("click",".chkappitems",function(){
		var appchk	=	$(this).is(":checked");
		var cnt		=	$(this).attr("data-cnt");
		if(appchk)
		{
			$("#chkdis"+cnt).prop('checked', false);
		}
	});
	$("#divtrxdtls").on("click",".chkdisitems",function(){
		var appchk	=	$(this).is(":checked");
		var cnt		=	$(this).attr("data-cnt");
		if(appchk)
		{
			$("#chkapp"+cnt).prop('checked', false);
		}
	});
	ItemApprovalFuncs.GETTRX();
});
function	smartselitem(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnvalitem').val($('#selitem').val());
		var vx = $('#hdnvalitem').val();
		var x = vx.split('|'); 
		$('#txtitemno').val(x[0]);
		$('#txtitemdesc').val(x[1]);
		$('#divselitem').html('');
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnvalitem').val($('#selitem').val());
			var vx = $('#hdnvalitem').val();
			var x = vx.split('|'); 
			$('#txtitemno').val(x[0]);
			$('#txtitemdesc').val(x[1]);
			$('#divselitem').html('');
		}
	}
}
function	smartsel(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnval').val($('#selvend').val());
		var vx = $('#hdnval').val();
		var x = vx.split('|'); 
		$('#txtvendorno').val(x[0]);
		$('#txtvendorname').val(x[1]);
		$('#divselvend').html('');
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnval').val($('#selvend').val());
			var vx = $('#hdnval').val();
			var x = vx.split('|'); 
			$('#txtvendorno').val(x[0]);
			$('#txtvendorname').val(x[1]);
			$('#divselvend').html('');
		}
	}
}