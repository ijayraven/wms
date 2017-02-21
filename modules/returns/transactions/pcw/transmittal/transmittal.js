function Mto_Transmittal(){}
	Mto_Transmittal.prototype	=	{
		constructor:Mto_Transmittal,
		
		getMTO:function(){
			var frmasearch	=	$("#frmasearch").serialize();
			$.ajax({
					data		:frmasearch,
					type		:"POST",	
					url			:"transmittal.php?action=GETMTO",
					beforeSend	:function(){
						$("#divloader").dialog("open");
					},
					success		:function(response){
						$("#divMTO").html(response);
						$("#divloader").dialog("close");
						$(".tablesorter").tablesorter({
							sortList: [[0,0]],
						 	headers: { 15: { sorter: false } }
						});
						$(".buttonset").buttonset();
						$(".tooltips").tooltip();
						$(".btntransmit").button({icons: {primary: "ui-icon ui-icon-check"}});
						for(var x=10; x<=15;x++)
						{
							$('#tblmtolist tr').find('td:nth-child('+x+'),th:nth-child('+x+')').hide();
						}
					}
				});
		},
		TransmitMTO:function(){
			var frmmto	=	$("#frmmto").serialize();
			$.ajax({
				type		:"POST",
				data		:frmmto,
				url			:"transmittal.php?action=TRANSMITMTO",
				beforeSend	:function(){
					$("#divloader").dialog("open");
				},
				success		:function(html){
					$("#divdebug").html(html);
					$("#divloader").dialog("close");
				}
			});
		}
	}	
var transmittal_funcs	=	new Mto_Transmittal();
$("document").ready(function(){
	$("#divMTO").on("change",".chkcol",function(){
	    var index 	= $(this).val();
	    $('#tblmtolist tr').find('td:nth-child('+index+'),th:nth-child('+index+')').toggle();
	});
	$("#divMTO").on("click","#btntransmit",function(){
		var selected	=	false;
		$(".chkmtos").each(function(){
			if($(this).is(":checked"))
			{
				selected = true;
			}
		});
		if(selected)
		{
			MessageType.confirmmsg(transmittal_funcs.TransmitMTO,"Do you want to transmit the selected MTO?","");
		}
		else
		{
			MessageType.infoMsg("Please select MTO to transmit.");
		}
	});
	$("#btnsearch").click(function(){
		transmittal_funcs.getMTO();
	});
	transmittal_funcs.getMTO();
});