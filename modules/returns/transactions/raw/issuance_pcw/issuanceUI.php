<div id="divpcw_ars" align="center">
	<table width="100%" class="tbl_labels">
		<tr>
			<td>Pieceworker</td>
			<td align="center">:</td>
			<td align="center">
				<?php echo $DATASOURCE->getDropDown($conn_255_10,"WMS_LOOKUP","PIECEWORKER","","selpcw_add","RECID,CODE,DESCRIPTION","RECID","DESCRIPTION"," STATUS = 'ACTIVE'",""); ?>
			</td>
		</tr>
		<tr>
			<td>ARS No.</td>
			<td align="center">:</td>
			<td align="center"><input type="text" id="txtars_add" name="txtars_add"></td>
		</tr>
	</table>
</div>
<script>
$("#divpcw_ars").dialog({
	title:"Additional Details",
	modal:true,
	closeOnEscape:false,
	dialogClass:"no-close",
	autoOpen: false,
	width:300,
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
		      	$("#selpcw_add").val("");
				$("#txtars_add").val("");
		        $( this ).dialog( "close" );
		      }
	    },
	    {
			text: "Submit",
	      	icons: {
	        	primary: "ui-icon ui-icon-disk"
	      	},
	      	click: function() {
				{
					var MTONO	=	$(this).data("MTONO");
					var pcw_add	=	$("#selpcw_add").val();
					var ars_add	=	$("#txtars_add").val();
					var errmsg	=	"";
					if(pcw_add == "")
					{
						errmsg	=	" - Please select Pieceworker<br>";
					}
					if(ars_add == "")
					{
						errmsg	+=	" - Please input ARS No.<br>";
					}
					if(errmsg == "")
					{
						MessageType.confirmmsg(Mto_issuance_pcw_funcs.goIssueMTO,"Do you want to issue this MTO to Piecewotk Section?",MTONO);
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