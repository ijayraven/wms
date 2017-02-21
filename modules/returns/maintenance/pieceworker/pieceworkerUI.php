<div id="divpieceworker" align="center" class="mrgn10">
	<form id="frmpieceworker">
		<table border="0"class="label_text">
			<tr>
				<td>Code</td>
				<td>:</td>
				<td>
					<input type="text" id="txtcode" name="txtcode" placeholder="Pieceworker Code" class="pwinput" readonly>
					<input type="hidden" id="hdnid" name="hdnid"class="pwinput">
				</td>
				<td></td>
			</tr>
			<tr>
				<td>Descrition</td>
				<td>:</td>
				<td><input type="text" id="txtdesc" name="txtdesc" size="40"placeholder="Pieceworker Description" class="pwinput"></td>
				<td></td>
			</tr>
			<tr>
				<td>Address</td>
				<td>:</td>
				<td><input type="text" id="txtstreet" name="txtstreet" size="40" placeholder="No. & Street" class="pwinput"></td>
				<td>No. & Street</td>
			</tr>
			<tr>
				<td></td>
				<td>&nbsp;</td>
				<td><input type="text" id="txtbrgy" name="txtbrgy" size="40" placeholder="Barangay" class="pwinput"></td>
				<td>Barangay</td>
			</tr>
			<tr>
				<td></td>
				<td>&nbsp;</td>
				<td><input type="text" id="txtcity" name="txtcity" size="40" placeholder="City" class="pwinput"></td>
				<td>City</td>
			</tr>
			<tr>
				<td></td>
				<td>&nbsp;</td>
				<td><input type="text" id="txtprovince" name="txtprovince" size="40" placeholder="Province" class="pwinput"></td>
				<td>Town, Province</td>
			</tr>
			<tr>
				<td></td>
				<td>&nbsp;</td>
				<td><input type="text" id="txtzipcode" name="txtzipcode" size="40" placeholder="ZIP Code" class="pwinput"></td>
				<td>ZIP Code</td>
			</tr>
		</table>
	</form>
</div>
<script>
$("#divpieceworker").dialog({
	modal:true,
	closeOnEscape:false,
	dialogClass:"no-close dlg-pieceworker",
	autoOpen: false,
	width:500,
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
		      	P_functions.clearFields();
		        $( this ).dialog( "close" );
		      }
	    },
	    {
			text: "Save",
	      	icons: {
	        	primary: "ui-icon ui-icon-disk"
	      	},
	      	click: function() {
		        var mode	=	$(".dlg-pieceworker .ui-button-text:contains(Save)").text();
				var endmsg	=	"";
				var id = $(this).data('ID');
				$("#hdnid").val(id);
				if(mode == "Save")
				{
					endmsg	=	"save";
				}
				else
				{
					endmsg	=	"update";
				}
				if(P_functions.validateFields())
				{
					MessageType.confirmmsg(P_functions.saveData,"Do you want to "+endmsg+" this pieceworker data?",mode);
				}
	      	}
	    }
  	]
});
</script>