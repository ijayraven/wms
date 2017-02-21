<div id="divtrxmto"></div>
<script>
$(".dates").datepicker({ 
	dateFormat: 'yy-mm-dd',
	changeMonth: true,
    changeYear: true 
});
$("#divloader").dialog({
	dialogClass: "no-close",
	closeOnEscape:false,	
	title:'Processing',
	bgiframe:true, resizable:false, height: "auto", width: 250, modal:true, autoOpen: false,draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 }
	});
$("#divtrxmto").dialog({
	dialogClass: "",
	closeOnEscape:false,	
	title:'EXCLUSIVE RETURN MTO CREATION',
	bgiframe:true, resizable:false, height: "auto", width: 800, modal:true, autoOpen: false,draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
	buttons:{
		"Save":function()
		{
			var updatemode	=	 $('#dia-btn-save').html();
				var endmsg	=	"save";
				if(updatemode == "Update")
				{
					endmsg	=	"update";
				}
				if($('input[name=rdodestination_C]:checked', '#frmdata').val() == undefined)
				{
					alert("Please choose destination.");
					return;
				}
				if(validateitems())
				{
					if(confirm("You are about to "+endmsg+" this record."))
					{
						savetrxmto(updatemode);
					}
				}
		},
		"Close":function()
		{
			$(this).dialog("close");	
			resettrx();
		}
	}	
});
</script>