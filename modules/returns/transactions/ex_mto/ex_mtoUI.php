<div id="divtrxmto"></div>
<div id="divsearchitems"><br>
	<table width="100%"class="label_text"border="0">
		<tr>
			<td>Posted Date</td>
			<td>:</td>
			<td>
				<input type="text" id="txtsfrom" name="txtsfrom" class="dates" placeholder='From'> to 
				<input type="text" id="txtsto" name="txtsto" class="dates" placeholder='To'>
			</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>
</div>
<div id="divpcw_ars" align="center">
	<table width="100%" class="tbl_labels">
		<tr>
			<td>Pieceworker</td>
			<td align="center">:</td>
			<td align="">
				<?php echo $DATASOURCE->getDropDown($conn_255_10,"WMS_LOOKUP","PIECEWORKER","","selpcw_add","RECID,CODE,DESCRIPTION","RECID","DESCRIPTION"," STATUS = 'ACTIVE'",""); ?>
			</td>
		</tr>
		<tr>
			<td>ARS No.</td>
			<td align="center">:</td>
			<td align="">
				<input type="text" id="txtars_add" name="txtars_add">
			</td>
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
					var TRXNO	=	$(this).data("TRXNO");
					var pcw_add	=	$("#selpcw_add").val();
					var ars_add	=	$("#txtars_add").val();
					if(confirm("You are about to transmit this MTO."))
			    	{
				    	 $.ajax({
								type	:	"GET",
								url		:	"ex_mto.php?action=TRANSMITTRX&TRXNO="+TRXNO+"&PCW="+pcw_add+"&ARS="+ars_add,
								beforeSend:	function()
								{
									$("#divloader").dialog("open");
								},
								success	:function(response)
								{
									$("#divdebug").html(response);
									$("#divloader").dialog("close");
									$("#selpcw_add").val("");
									$("#txtars_add").val("");
								}
						});
			    	}
				}
	      	}
	    }
  	]
});
$("#divtrxmto").dialog({
	dialogClass: "no-close diamto",
	closeOnEscape:false,	
	title:'EXCLUSIVE RETURN MTO CREATION',
	bgiframe:true, resizable:false, height: "auto", width: 800, modal:true, autoOpen: false,draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
	buttons:
	[
		{
			text: "Close",
			icons: {
				primary: "ui-icon ui-icon-cancel"
			},
			click: function() 
			{
				$(this).dialog("close");	
			}
		},
		
		{
			text: "Save",
			icons: {
				primary: "ui-icon ui-icon-disk"
			},
			click: function() 
			{
				var tdtrxno		=	 $('#tdtrxno').text();
				var mode		=	 "Save";
				var endmsg		=	"save";
				if(tdtrxno != "")
				{
					endmsg		=	"update";
					mode		=	"Update";
				}
				if(EX_MTO_funcs.validateItems())
				{
					MessageType.confirmmsg(EX_MTO_funcs.saveTrx,"Do you want to "+endmsg+" this transaction?",mode);
				}
			}
		}	
	]
});
$("#divsearchitems").dialog({
	dialogClass: "no-close",
	closeOnEscape:false,	
	title:'SEARCH EXCLUSIVE ITEMS',
	bgiframe:true, resizable:false, height: "auto", width:"auto", modal:true, autoOpen: false,draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
	buttons:
	[
		{
		      text: "Close",
		      icons: {
		        primary: "ui-icon ui-icon-cancel"
		      },
		      click: function() {
		      	$("#txtsfrom").val("");
				$("#txtsto").val("");
		        $( this ).dialog( "close" );
		      }
	    },
		{
		      text: "Search",
		      icons: {
		        primary: "ui-icon ui-icon-search"
		      },
		      click: function() {
		      	EX_MTO_funcs.searchItems();
		      }
	    }
	]
});
</script>