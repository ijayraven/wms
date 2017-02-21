<div id="divcustomer" align="center">
	<form id="frmcustomer">
		<table border="0"class="label_text">
			<tr>
				<td>Customer</td>
				<td>:</td>
				<td>
					<input type="text" id="txtcustno" name="txtcustno" size="10" placeholder='CODE' class="searchcust">
				   	<input type="text" id="txtcustname" name="txtcustname" size="35" placeholder='NAME' class="searchcust">
					<div id="divselcust" class="divsel"></div>
					<input type="hidden" id="hdnval" name="hdnval" value="">
				</td>
			</tr>
			<tr>
				<td>Status</td>
				<td>:</td>
				<td>
					<select id="selstatus" name="selstatus">
						<option value=""><-- Please Select --></option>
						<option value="ACTIVE" selected>Active</option>
						<option value="INACTIVE">Inactive</option>
					</select>
				</td>
			</tr>
		</table>
	</form>
</div>
<script>
$("#divcustomer").dialog({
	modal:true,
	closeOnEscape:false,
	dialogClass:"no-close dlg-customer",
	autoOpen: false,
	width:500,
	height:200,
	show: { effect: "blind", duration: 800 },
	hide: { effect: "explode", duration: 1000 },
	buttons:
	[
		  {
		      text: "Cancel",
		      icons: {
		        primary: "ui-icon ui-icon-cancel"
		      },
		      click: function() {
		      	BIcustomer_funcs.cancelCreate();
		        $( this ).dialog( "close" );
		      }
	    },
	    {
			text: "Save",
	      	icons: {
	        	primary: "ui-icon ui-icon-disk"
	      	},
	      	click: function() {
		        var mode	=	$(".dlg-customer .ui-button-text:contains(Save)").text();
				if(mode == "Save")
				{
					endmsg	=	"save";
				}
				else
				{
					endmsg	=	"update";
				}
				if(BIcustomer_funcs.validateFields())
				{
					MessageType.confirmmsg(BIcustomer_funcs.saveCust,"Do you want to "+endmsg+" this customer?",mode);
				}
	      	}
	    }
  	]
});
</script>