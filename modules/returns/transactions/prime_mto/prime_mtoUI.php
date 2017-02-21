<div id="divtrxmto"></div>
<div id="divsearchitems"><br>
	<table width="100%"class="label_text"border="0">
		<tr>
			<td>Scanned Date</td>
			<td>:</td>
			<td>
				<input type="text" id="txtsfrom" name="txtsfrom" class="dates">
				<input type="text" id="txtsto" name="txtsto" class="dates">
			</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td>
				<input type="button" name="btnsearchitems" id="btnsearchitems" value="SEARCH PRIME ITEMS" class="btnsearch">
			</td>
		</tr>
	</table>
</div>
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
	dialogClass: "no-close diamto",
	closeOnEscape:false,	
	title:'PRIME RETURN MTO CREATION',
	bgiframe:true, resizable:false, height: "auto", width: 800, modal:true, autoOpen: false,draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
	buttons:{
		"Save":function()
		{
			var tdtrxno		=	 $('#tdtrxno').text();
			var updatemode	=	 "Save";
			var endmsg	=	"save";
			if(tdtrxno != "")
			{
				endmsg		=	"update";
				updatemode	=	"Update";
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
$("#divsearchitems").dialog({
	dialogClass: "",
	closeOnEscape:false,	
	title:'SEARCH PRIME ITEMS',
	bgiframe:true, resizable:false, height: "auto", width:300, modal:true, autoOpen: false,draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
});
</script>