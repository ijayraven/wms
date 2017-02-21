function ItemApproval(){}
	ItemApproval.prototype = {
		constructor:ItemApproval,
		SavePO:function(){
			var frmpodtls		=	$("#frmpodtls").serialize();
			var txtgatepassno	=	$("#txtgatepassno").val();
			var seluser			=	$("#seluser").val();
			$.ajax({
				type		:"POST",
				data		:frmpodtls,
				url			:"creation.php?action=SAVETRX&txtgatepassno="+txtgatepassno+"&seluser="+seluser,
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
			var isselected	=	false;
			var qtynotempty	=	true;
			var itemqty		=	0;
			var qtyindex;
			
			$(".chkappitem").each(function(){
				qtyindex = $(".chkappitem").index(this);
				if($(this).is(":checked") == true)
				{
					isselected 	= true;
					itemqty		= $(".txtqtys").eq(qtyindex).val();
					if(itemqty == 0)
					{
						qtynotempty	=	false;
						$("#tritems"+(qtyindex+1)).addClass("errpurpose");
					}
					else
					{
						$("#tritems"+(qtyindex+1)).removeClass("errpurpose");
					}
				}
			});
			if(isselected)
			{
				if(qtynotempty == false)
				{
					MessageType.infoMsg("Please input quantity.");
					return;
				}
				else
				{
					$("#divgatepass").dialog("open");
				}
			}
			else
			{
				MessageType.infoMsg('Please select item/s to be subjected for approval.');
			}
		},
		cancelPODTLS:function(){
			$("#divpodtls").html("");
			$("#txtCpono").val("");
		},
		cancelAP:function(){
			$("#txtgatepassno").val("");
			$("#seluser").val("");
		},
		GETTRX:function(USEPREVQRY){
			var frmsearch	=	$("#frmsearch").serialize();
			$.ajax({
				type		:"POST",
				data		:frmsearch,
				url			:"creation.php?action=SEARCHPO&USEPREVQRY="+USEPREVQRY,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divPOlist").html(response);
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter({
					 	headers: {7: { sorter: false } }
					});
					$(".tooltips").tooltip();
				}
			});
		},
		cancelTRX:function(transno){
			$.ajax({
				type		:"GET",
				url			:"creation.php?action=CANCELTRX&TRANSNO="+transno,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
		},
		postTRX:function(transno){
			$.ajax({
				type		:"GET",
				url			:"creation.php?action=POSTTRX&TRANSNO="+transno,
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
		if(txtitemno == "" && txtPONO == "" && txtvendorno == "" && txttrxno == "" && selstatus == "")
		{
			MessageType.infoMsg("Please select at least one criterion to search.");
		}
		else
		{
			ItemApprovalFuncs.GETTRX();
		}
	});
	$("#btncreate").click(function(){
		$("#divitemappcreate").dialog("open");
	});
	$("#txtCpono").change(function(){
		var PONO	=	$(this).val();
		if(PONO != "")
		{
			$.ajax({
				type		:"GET",
				url			:"creation.php?action=GETPODTLS&PONO="+PONO,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divpodtls").html(response);
					$("#divloader").dialog("close");
				}
			});
		}
	});
	$("#divPOlist").on("click",".cancelbtn",function(){
		var transno	=	$(this).attr("data-trxno");
		MessageType.confirmmsg(ItemApprovalFuncs.cancelTRX,"Do you want to cancel this transaction?",transno);
	});
	$("#divPOlist").on("click",".postbtn",function(){
		var transno	=	$(this).attr("data-trxno");
		MessageType.confirmmsg(ItemApprovalFuncs.postTRX,"Do you want to post this transaction?",transno);
	});
	$("#divPOlist").on("click",".tdtrxdtls",function(){
		var transno	=	$(this).attr("data-trxno");
		var count	=	$(this).attr("data-count");
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
				url			:"creation.php?action=GETDTLS&TRANSNO="+transno,
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
						url			:	'creation.php?action=Q_SEARCHITEM&ITEMNO='+txtitemno+'&ITEMDESC='+txtitemdesc,
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
						url			:	'creation.php?action=Q_SEARCHVEND&VENDNO='+txtvendno+'&VENDNAME='+txtvendname,
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
	$("#divitemappcreate #divpodtls").on("change",".txtqtys",function(){
		var qty		=	+ $(this).val();
		var cnt		=	+ $(this).attr("data-cnt");
		var O_qty	=	+ $("#tdO_qty"+cnt).text();
		if(qty > O_qty)
		{
			MessageType.infoMsg("Quantity must not be greater than the original quantity.");
			$(this).val("");
		}
	});
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