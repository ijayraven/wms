<div id="divmtodtls" align="center"></div>
<div id="divconfirm" align="center"></div>
<div id="divscanning" align="center">
	<table class="tblresult" width="100%" border="0">
		<tr>
			<td colspan="3" class="tblresul-tbltdtls-hdr">
				<br>
				<input type="text" id="txtscan" name="txtscan" class="cntrd" size="40" placeholder="Item No./Barcode">
				<input type="hidden" id="hdnitemno" name="hdnitemno">
				<input type="hidden" id="hdncurrcnt" name="hdncurrcnt">
				<br>
				<br>
			</td>
		</tr>
	</table>
	<h1 id="errmsg" style="color:red;"></h1>
	<h1 id="sucmsg" style="color:green;"></h1>
</div>
<script>
$("#divmtodtls").dialog({
	title:"MTO Details Update",
	modal:true,
	closeOnEscape:false,
	dialogClass:"no-close dialog-1",
	autoOpen: false,
	width:1000,
	hide: { effect: "clip", duration: 1000 }
});
$("#divconfirm").dialog({
	title:"Confirm MTO Items",
	modal:true,
	closeOnEscape:false,
	dialogClass:"no-close dialog-1",
	autoOpen: false,
	width:1000,
	hide: { effect: "clip", duration: 1000 },
	buttons:
	[
		{
		    text: "Cancel",
		    icons: {
		    	primary: "ui-icon ui-icon-cancel"
		    },
		    click: function() {
		      	$( this ).dialog( "close" );
		    }
	    },
	    {
			text: "Confirm",
	      	icons: {
	        	primary: "ui-icon ui-icon-circle-check"
	      	},
	      	click: function() {
				{
					var MTONO		=	$("#tdmtono").text();
					if(Fb_confirmation_funcs.validateConfirmation())
					{
						MessageType.confirmmsg(Fb_confirmation_funcs.confirmItems,"Do you want to confirm the selected items?",MTONO);
					}
				}
	      	}
	    }
  	]
});
$("#divscanning").dialog({
	dialogClass: "no-close fixed-dialog",
	closeOnEscape:false,
	title:'Scanning',
	width:400,resizable:false, height: "auto", modal:false, autoOpen: false,draggable: true,
	buttons:
	[
		{
		      text: "Close",
		      icons: {
		        primary: "ui-icon ui-icon-cancel"
		      },
		      click: function() {
		      	$("#errmsg").text("");
		      	$("#txtscan").val("");
				$(this).dialog("close");
				$("#divmtodtls").dialog("close");
		      }
	    }
  	]
});
</script>