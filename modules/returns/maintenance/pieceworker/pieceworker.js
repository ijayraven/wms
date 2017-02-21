function Pieceworker(){}
Pieceworker.prototype	=	{
	constructor:Pieceworker,
	
	validateFields:function(){
		var txtcode		=	$("#txtcode").val();
		var txtdesc		=	$("#txtdesc").val();
		var txtstreet	=	$("#txtstreet").val();
		var txtzipcode	=	$("#txtzipcode").val();
		var txtbrgy		=	$("#txtbrgy").val();
		var txtcity		=	$("#txtcity").val();
		var txtprovince	=	$("#txtprovince").val();
		var errmsg		=	"";
		if(txtcode == "")
		{
			errmsg		=	" - Please enter Pieceworker Code<br>";
			valid		=	false;
		}
		if(txtdesc == "")
		{
			errmsg		+=	" - Please enter Pieceworker Description<br>";
			valid		=	false;
		}
		if(txtstreet == "")
		{
			errmsg		+=	" - Please enter No. and Street.<br>";
			valid		=	false;
		}
		if(txtzipcode == "")
		{
			errmsg		+=	" - Please enter ZIP Code.<br>";
			valid		=	false;
		}
		if(txtbrgy == "")
		{
			errmsg		+=	" - Please enter Barangay.<br>";
			valid		=	false;
		}
		if(txtcity == "" && txtprovince == "")
		{
			errmsg		+=	" - Please enter City or Province.<br>";
			valid		=	false;
		}
		if(errmsg != "")
		{
			MessageType.infoMsg(errmsg);
			return false;
		}
		else
		{
			return true;
		}
	},
	saveData:function(mode){
		var frmpieceworker	=	$("#frmpieceworker").serialize();
		$.ajax({
			data		:frmpieceworker,
			type		:"POST",	
			url			:"pieceworker.php?action=SAVEDATA&MODE="+mode,
			beforeSend	:function(){
				$("#divloader").dialog("open");
			},
			success		:function(response){
				$("#divdebug").html(response);
				$("#divloader").dialog("close");
				$("#divpieceworker").dialog("close");
			}
		});
	},
	getList:function(){
		var	frmasearch	=	$("#frmasearch").serialize();
		$.ajax({
				data		:frmasearch,
				type		:"POST",	
				url			:"pieceworker.php?action=GETLIST",
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divPlist").html(response);
					$("#divloader").dialog("close");
					$(".tooltips").tooltip();
					$(".tablesorter").tablesorter({
						headers:{
							8:{sorter:false}
						}
					});
				}
			});
	},
	activatePCW:function(id)
	{
		$.ajax({
				url			:"pieceworker.php?action=ACTIVATEPCW&ID="+id,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
	},
	deactivatePCW:function(id)
	{
		$.ajax({
				url			:"pieceworker.php?action=DEACTIVATEPCW&ID="+id,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
				}
			});
	},
	clearFields:function(){
		$(".pwinput").val("");
	}
}
var P_functions	=	new Pieceworker();

$("document").ready(function(){
	$("#btncreate").click(function(){
		$(".dlg-pieceworker .ui-button-text:contains(Update)").text("Save");
		$('#divpieceworker').dialog('option', 'title', 'Create New Pieceworker');
		$("#divpieceworker").dialog("open");
	});
//	$("#txtcode").change(function(){
//		var txtcode	=	$(this).val();
//		$.ajax({
//			url			:"pieceworker.php?action=CHKCODE&CODE="+txtcode,
//			beforeSend	:function(){
//				$("#divloader").dialog("open");
//			},
//			success		:function(response){
//				$("#divdebug").html(response);
//				$("#divloader").dialog("close");
//			}
//		});
//	});
	$("#btnsearch").click(function(){
		P_functions.getList();
	});
	$("#divPlist").on("click",".editbtn",function(){
		var id	=	$(this).attr("data-id");
		$(".dlg-pieceworker .ui-button-text:contains(Save)").text("Update");
		$('#divpieceworker').dialog('option', 'title', 'Edit Pieceworker');
		$("#divpieceworker").data('ID', id).dialog("open");
		$.ajax({
			url			:"pieceworker.php?action=EDIT&ID="+id,
			beforeSend	:function(){
				$("#divloader").dialog("open");
			},
			success		:function(response){
				$("#divdebug").html(response);
				$("#divloader").dialog("close");
			}
		});
	});
	$("#divPlist").on("click",".activatebtn",function(){
		var id	=	$(this).attr("data-id");
		MessageType.confirmmsg(P_functions.activatePCW,"Do you want to activate this Pieceworker?",id);
	});
	$("#divPlist").on("click",".deactivatebtn",function(){
		var id	=	$(this).attr("data-id");
		MessageType.confirmmsg(P_functions.deactivatePCW,"Do you want to deactivate this Pieceworker?",id);
	});
	$("#txtdesc").change(function(){
		var id	=	$("#hdnid").val();
		$.ajax({
			url			:"pieceworker.php?action=CREATECODE&DESC="+$(this).val()+"&ID="+id,
			beforeSend	:function(){
				$("#divloader").dialog("open");
			},
			success		:function(response){
//				$("#divdebug").html(response);
				$("#txtcode").val(response);
				$("#divloader").dialog("close");
			}
		});
	});
	$(".pwinput").change(function(){
		var uppercase	=	$(this).val().toUpperCase();
		$(this).val(uppercase);
	});
	P_functions.getList();
});