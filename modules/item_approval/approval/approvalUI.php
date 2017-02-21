<div id="divtrxdtls"></div>
<script>
$("#divtrxdtls").dialog({
	dialogClass:'no-close',
	closeOnEscape: false,
	bgiframe:true, 
	resizable:false, 
	title:'Item Approval',
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
	      		ItemApprovalFuncs.checkitems();
	      	}
	    }
  	]
});
</script>