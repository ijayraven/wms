<div id="divtrxmto">
	<form id="frmdata">
	<table border="1" class="tblresult" id="tbltrxnonmto">
		<tr id="trtrxno" class="activetr">
			<td id="tdtrxno" colspan="10" align="center"style="padding:10px;"></td>
		</tr>
		<tr class="tblresul-tbltdtls-hdr">
			<td align="center" colspan="10" style="padding:10px;">
				DESTINATION:
					<label for="rdofillingbin_C"><input type="radio" id="rdofillingbin_C" name="rdodestination_C" value="FILLING BIN">FILLING BIN</label>
					<label for="rdoraw_C"><input type="radio" id="rdoraw_C" name="rdodestination_C" value="RAW">RAW</label>
			</td>
		</tr>
		<tr class="trheader">
			<td width="8%" align="center">Line No.</td>
			<td width="10%" align="center">MPOS No.</td>
			<td width="50%" align="center">Customer</td>
			<td width="10%" align="center"># of Boxes</td>
			<td width="12%" align="center"># of PackageS</td>
			<td width="12%" align="center">Box Label</td>
			<td width="10%" align="center">Action</td>
		</tr>
		<tr id="tr1" class="trbody">
			<td id="tdcurcnt1" align="center">1</td>
			<td>
				<input type="text" id="txtmposno1" name="txtmposno1" size="10" class="txtmposnos centered" data-curcnt = '1'>
			</td>
			<td id="tdcustomer1">&nbsp;</td>
			<td align="center">
				<input type="text" id="txtnoboxes1" name="txtnoboxes1" size="10" class="txtnoboxes txtnotype centered" data-curcnt = '1'>
			</td>
			<td align="center">
				<input type="text" id="txtnopackages1" name="txtnopackages1" size="10" class="txtnopackages txtnotype centered" data-curcnt = '1'>
			</td>
			<td align="center">
				<input type="text" id="txtboxlabel1" name="txtboxlabel1" size="10" class="txtboxlabel centered" data-curcnt = '1'>
			</td>
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
$("#divtrxmto").dialog({
	dialogClass: "no-close",
	closeOnEscape:false,	
	title:'RETURN MTO CREATION',
	bgiframe:true, resizable:false, height: "auto", width: 1000, modal:true, autoOpen: false,draggable: false,
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
				else
				{
					alert("Some fields are empty.");
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