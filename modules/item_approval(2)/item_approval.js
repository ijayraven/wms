function ItemApproval(){}
	ItemApproval.prototype = {
		constructor:ItemApproval,
		SavePO:function(){
			var frmpodtls		=	$("#frmpodtls").serialize();
			var txtgatepassno	=	$("#txtgatepassno").val()
			$.ajax({
				type		:"POST",
				data		:frmpodtls,
				url			:"item_approval.php?action=APPROVEPO&txtgatepassno="+txtgatepassno,
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
			$(".chkappitem").each(function(){
				if($(this).is(":checked") == true)
				{
					isselected = true;
				}
			});
			
			$(".chkdisitem").each(function(){
				if($(this).is(":checked") == true)
				{
					isselected = true;
				}
			});
			return isselected;
		}
	}
var ItemApprovalFuncs	=	new ItemApproval();
$("document").ready(function(){
	$("#btnsearch").click(function(){
		
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
				url			:"item_approval.php?action=GETPODTLS&PONO="+PONO,
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
});