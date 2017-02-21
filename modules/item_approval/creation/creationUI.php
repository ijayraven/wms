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
			<td><input type="text" id="txtgatepassno" name="txtgatepassno" class="cntrd"><br>Gatepass</td>
		</tr>
		<tr class="customhdr">
			<td align="center">
				<?php echo $DATASOURCE->getDropDown($conn_255_10,"WMS_USERS","USERS","selusers selautowith cntrd","seluser","USERNAME,NAME","USERNAME","NAME","DEPT = 'Product Planning' ORDER BY NAME");?>
				<br>Approver
			</td>
		</tr>
	</table>
</div>
<script>
$("#divitemappcreate").dialog({
	dialogClass:'no-close',
		closeOnEscape: false,
		bgiframe:true, 
		resizable:false, 
		title:'Item Approval Creation',
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
			      	ItemApprovalFuncs.cancelPODTLS();
			      	$(this).dialog("close");
			      }
		    },
		    {
				text: "Save",
		      	icons: {
		        	primary: "ui-icon ui-icon-disk"
		      	},
		      	click: function() {
		      		ItemApprovalFuncs.checkitems();
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
		width:400, 
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
			      	ItemApprovalFuncs.cancelAP();
		      		$(this).dialog("close");
			      }
		    },
		    {
				text: "Save",
		      	icons: {
		        	primary: "ui-icon ui-icon-disk"
		      	},
		      	click: function() {
		      		var errmsg	=	"";
		      		if($("#txtgatepassno").val() == "")
		      		{
		      			errmsg	=	" - Please input gatepass number.<br>";
		      		}
		      		if($("#seluser").val() == "")
		      		{
		      			errmsg	+=	" - Please select approver.<br>";
		      		}
		      		if(errmsg == "")
		      		{
		      			MessageType.confirmmsg(ItemApprovalFuncs.SavePO,"Do you want to save the selected item/s subjected for approval?","");
		      		}
		      		else
		      		{
		      			MessageType.infoMsg(errmsg);
		      		}
		      	}
		    }
  	]
});
</script>