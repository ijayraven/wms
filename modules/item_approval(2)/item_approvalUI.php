<div id="divitemappcreate">
	<table align="center" width="100%">
		<tr>
			<td>
				<form id="frmpodtls" onsubmit="return false;">
					<table align="center">
						<tr class="customhdr">
							<td>
								P.O. No. : 
								<input type="text" id="txtCpono" name="txtCpono" class="cntrd forpo">
							</td>
						</tr>
					</table>
					<div id="divpodtls"></div>
				</form>
			</td>
		</tr>
	</table>
</div>
<div id="divgatepass">
	<table align="center" width="100%">
		<tr class="customhdr">
			<td><input type="text" id="txtgatepassno" name="txtgatepassno" class="cntrd">Gatepass</td>
		</tr>
	</table>
</div>
<script>
$("#divitemappcreate").dialog({
	dialogClass:'no-close',
		closeOnEscape: false,
		bgiframe:true, 
		resizable:false, 
		title:'P.O. Details',
		height: "auto",
		width:700, 
		modal:true, 
		autoOpen: false,	
		draggable: true,
		overlay: { backgroundColor: '#000', opacity: 0.5 },
		buttons: 
		[
			{
			      text: "Cancel",
			      icons: {
			        primary: "ui-icon ui-icon-cancel"
			      },
			      click: function() {
			      	$(this).dialog("close");
			      }
		    },
		    {
				text: "Save",
		      	icons: {
		        	primary: "ui-icon ui-icon-disk"
		      	},
		      	click: function() {
		      		if(ItemApprovalFuncs.checkitems())
		      		{

		      			$("#divgatepass").dialog("open");
		      		}
		      		else
		      		{
		      			MessageType.infoMsg('Please select item/s to approve or disapprove.');
		      		}
		      	}
		    }
  	]
});
$("#divgatepass").dialog({
	dialogClass:'no-close',
		closeOnEscape: false,
		bgiframe:true, 
		resizable:false, 
		title:'Gatepass Validation',
		height: "auto",
		width:300, 
		modal:true, 
		autoOpen: false,	
		draggable: true,
		overlay: { backgroundColor: '#000', opacity: 0.5 },
		buttons: 
		[
			{
			      text: "Cancel",
			      icons: {
			        primary: "ui-icon ui-icon-cancel"
			      },
			      click: function() {
		      		$(this).dialog("close");
			      }
		    },
		    {
				text: "Save",
		      	icons: {
		        	primary: "ui-icon ui-icon-disk"
		      	},
		      	click: function() {
		      		if($("#txtgatepassno").val() != "")
		      		{
		      			MessageType.confirmmsg(ItemApprovalFuncs.SavePO,"Do you want to approve the selected item/s?","");
		      		}
		      		else
		      		{
		      			MessageType.infoMsg('Please input gatepass number.');
		      		}
		      	}
		    }
  	]
});
</script>