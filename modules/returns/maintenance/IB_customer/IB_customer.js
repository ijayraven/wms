function IB_barcode(){}
	IB_barcode.prototype = {
		constructor:IB_barcode,
		getSelCustS:function(evt){
			var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
			if(evt == 'click')
			{
				$('#hdnvalS').val($('#selcustS').val());
				var vx = $('#hdnvalS').val();
				var x = vx.split('|'); 
				$('#txtcustnoS').val(x[0]);
				$('#txtcustnameS').val(x[1]);
				$('#divselcustS').html('');
			}
			else
			{
				if(evthandler == 13)
				{
					$('#hdnvalS').val($('#selcustS').val());
					var vx = $('#hdnvalS').val();
					var x = vx.split('|'); 
					$('#txtcustnoS').val(x[0]);
					$('#txtcustnameS').val(x[1]);
					$('#divselcustS').html('');
				}
			}
		},
	getSelCust:function(evt){
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
		cancelCreate:function(){
			$(".searchcust").val("");
//	      	$("#selstatus").val("");
		},
		validateFields:function(){
			var txtcustno 	= $('#txtcustno').val();
			var selstatus 	= $('#selstatus').val();
			var errmsg		= "";
			if(txtcustno == "")
			{
				errmsg = " - Customer must not be empty. <br>";
			}
			if(selstatus == "")
			{
				errmsg += " - Status must not be empty. <br>";
			}
			if(errmsg == "")
			{
				return true;
			}
			else
			{
				MessageType.infoMsg(errmsg);
				return false;
			}
		},
		saveCust:function(mode){
			var frmcustomer = $("#frmcustomer").serialize();
			$.ajax({
					data		:	frmcustomer,
					type		:	"POST",
					url			:	"IB_customer.php?action=SAVECUSTS&MODE="+mode,
					beforeSend	:	function()
					{
						$('#divdialog').dialog("close");
					},
					success		:	function(response)
					{
						$('#divdebug').html(response);
						$('#divdialog').dialog("open");
					}
				});
		}
	}
var BIcustomer_funcs	=	new IB_barcode();
$("document").ready(function(){
	$(".searchcustS").keyup(function(evt){
		var txtcustno	=	$('#txtcustnoS').val();
		var txtcustname	=	$('#txtcustnameS').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
					url			:	'IB_customer.php?action=Q_SEARCHCUSTS&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname,
					success		:	function(response)
					{
						if(response == '')
						{
							$('#divselcustS').html('');
							$(".searchcustS").val("");
							MessageType.infoMsg("Customer not found.");
						}
						else
						{
							$('#divselcustS').html(response);
							var position 	=	$("#txtcustnoS").position();
							var selwidth	=	$("#txtcustnoS").width() + $("#txtcustnameS").width()+12;
							$("#divselcustS").css({left: position.left, position:'absolute',zIndex:1000000});
							$('#divselcustS').show();
							$('#selcustS').css({width:selwidth});
						}
					}
				});
			}
			else if(evthandler == 40 && $('#divselcustS').html() != '')
			{
				$('#selcustS').focus();
			}
			else
			{
				$('#divselcustS').html('');
			}
		}
		else
		{
			$('#divselcustS').html('');
		}
	});
	$("#btncreate").click(function(){
		$(".searchcust").prop("readonly",false);
		$("#divcustomer").dialog("open");
		$('#divcustomer').dialog('option', 'title', 'INTERNAL BARCODE CUSTOMER CREATION');
		$(".dlg-customer .ui-button-text:contains(Update)").text("Save");
		
//		$('.ui-dialog-buttonpane button:contains(Update)').attr("id", "dia-btn-update");
//		$('#dia-btn-update').html("Save");
	});
	$(".searchcust").keyup(function(evt){
		var txtcustno	=	$('#txtcustno').val();
		var txtcustname	=	$('#txtcustname').val();
		var evthandler	=	(evt.charCode) ? evt.charCbtnsearchode : evt.keyCode;
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
					url			:	'IB_customer.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname,
					success		:	function(response)
					{
						if(response == '')
						{
							$('#divselcust').html('');
							$(".searchcust").val("");
							MessageType.infoMsg("Customer not found.");
						}
						else
						{
							$('#divselcust').html(response);
							var position 	=	$("#txtcustno").position();
							var selwidth	=	$("#txtcustno").width() + $("#txtcustname").width()+12;
							$("#divselcust").css({left: position.left, position:'absolute',zIndex:1000000});
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
	$("#divselcustS").on("keypress","#selcustS",function(evt){
		BIcustomer_funcs.getSelCustS(evt);		
	});
	$("#divselcustS").on("click","#selcustS option",function(){
		BIcustomer_funcs.getSelCustS("click");		
	});
	$("#divselcust").on("keypress","#selcust",function(evt){
		BIcustomer_funcs.getSelCust(evt);		
	});
	$("#divselcust").on("click","#selcust option",function(){
		BIcustomer_funcs.getSelCust("click");		
	});
	$("#btnsearch").click(function(){
		var frmasearch	=	$("#frmasearch").serialize();
		$.ajax({
				data		:	frmasearch,
				type		:	"POST",
				url			:	"IB_customer.php?action=GETCUSTS",
				beforeSend	:	function()
				{
					$("#divloader").dialog("open");
				},
				success		:	function(response)
				{
					$('#divCustlist').html(response);
					$("#divloader").dialog("close");
					$(".tablesorter").tablesorter({
						sortList: [[0,0]],
					 	headers: { 7: { sorter: false } }
					});
					$(".smallbtns").tooltip();
				}
			});
	});
	$("#divCustlist").on("click",".editbtn",function(){
		var custno	=	$(this).attr("data-custno");
		$.ajax({
				url			:	"IB_customer.php?action=EDITCUST&CUSTNO="+custno,
				beforeSend	:	function()
				{
					$("#divloader").dialog("open");
				},
				success		:	function(response)
				{
					$('#divdebug').html(response);
					$("#divloader").dialog("close");
					$(".searchcust").prop("readonly",true);
					$("#divcustomer").dialog("open");
					$('#divcustomer').dialog('option', 'title', 'INTERNAL BARCODE CUSTOMER UPDATE');
					$(".dlg-customer .ui-button-text:contains(Save)").text("Update");
				}
			});
	});
	$("#btnsearch").trigger("click");
});