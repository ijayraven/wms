<div id="divmtodtls" align="center"></div>
<script>
$("#divmtodtls").dialog({
	title:"MTO Details Update",
	modal:true,
	closeOnEscape:false,
	dialogClass:"no-close",
	autoOpen: false,
	width:1000,
	show: { effect: "blind", duration: 800 },
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
			text: "Update",
	      	icons: {
	        	primary: "ui-icon ui-icon-disk"
	      	},
	      	click: function() {
				{
					var errmsg	=	Mto_issuance_fb_funcs.validate();
					if(errmsg == "")
					{
						MessageType.confirmmsg(Mto_issuance_fb_funcs.updateMTO,"Do you want to update this MTO?","");
					}
					else
					{
						MessageType.infoMsg(errmsg);
					}
				}
	      	}
	    }
  	]
});
</script>