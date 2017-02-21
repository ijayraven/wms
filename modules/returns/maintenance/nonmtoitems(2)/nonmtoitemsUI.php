<div id="divdebug"></div>
<div id="divtrxnonmtoitems">
	<form id="frmdata">
	<table border="1" class="label_text" width="100%" id="tbltrxnonmtoitems">
		<tr id="trtrxno"style="display:none;background-color:#6a90c8;color:#fff">
			<td id="tdtrxno" colspan="5" align="center"></td>
		</tr>
		<tr style="background-color:#3b64a0;color:#fff">
			<td width="10%" align="center">Line No.</td>
			<td width="10%" align="center">Item No.</td>
			<td width="60%" align="center">Description</td>
			<td width="10%" align="center">SRP</td>
			<td width="10%" align="center">Action</td>
		</tr>
		<tr id="tr1">
			<td id="tdcurcnt1" align="center">1</td>
			<td><input type="text" id="txtitemno1" name="txtitemno1" size="10" class="txtitemnos" data-curcnt = '1'></td>
			<td id="tditemdesc1">&nbsp;</td>
			<td id="tdsrp1" align="center">&nbsp;</td>
			<td align="center">
				<img src="/wms/images/images/action_icon/add_blue.png" class="smallbtns addbtn" title="Add Row">
				<input type="hidden" name="hidcnt" id="hidcnt" value="1">
			</td>
		</tr>
	</table>
	</form>
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
	$("#divtrxnonmtoitems").dialog({
		dialogClass:'no-close divtrxnonmtoitems_C',
		closeOnEscape: false,
		bgiframe:true, 
		resizable:false, 
		title:'EXCLUSIVE ITEMS CREATION',
		height: "auto",
		width:700, 
		modal:true, 
		autoOpen: false,	
		draggable: true,
		overlay: { backgroundColor: '#000', opacity: 0.5 },
		buttons: {
					'Save':function()
					{
						var updatemode	=	 $('#dia-btn-save').html();
						var endmsg	=	"save";
						if(updatemode == "Update")
						{
							endmsg	=	"update";
						}
						if(validateitems())
						{
							if(confirm("You are about to "+endmsg+" this record."))
							{
								savetrxnonmtoitems(updatemode);
							}
						}
						else
						{
							alert("Some fields are empty.");
						}
					},
					'Close':function()
					{			
						$(this).dialog("close");	
						resettrx();			
					}
				 }
	});
</script>