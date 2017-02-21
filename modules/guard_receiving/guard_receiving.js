function POactivation(){}
	POactivation.prototype	=	{
		constructor:POactivation,
		
		activatePO:function(){
			var txtpono	=	$("#txtpono").val();
			$.ajax({
				url			:"guard_receiving.php?action=ACTIVATEPO&PONUM="+txtpono,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divdebug").html(response);
					$("#divloader").dialog("close");
					$("#txtpono").val("");
				}
			});
		}
	}
var POactivation_funcs	=	new POactivation();
$("document").ready(function(){
	$("#txtpono").change(function(event){
		if ( event.which == 13 ) 
		{
		   	event.preventDefault();
		}
		var txtpono	=	$(this).val();
		if(txtpono != "")
		{
			$.ajax({
				url			:"guard_receiving.php?action=GETPODTLS&PONUM="+txtpono,
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(response){
					$("#divPO").html(response);
					$("#divloader").dialog("close");
					$(".btncheck").button({icons: {primary: "ui-icon ui-icon-check"}});
				}
			});
		}
	});
	$("#divPO").on("click",".btnactivate",function(){
		MessageType.confirmmsg(POactivation_funcs.activatePO,"Do you want to activate this P.O.?","");
	});
	$("#txtpono").focus();
});