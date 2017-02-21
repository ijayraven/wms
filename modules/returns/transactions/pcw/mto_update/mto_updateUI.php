<div id="divmtodtls" align="center"></div>
<div id="divconfirm" align="center"></div>
<div id="divDR" align="center">
	<table width="100%" class="tbl_labels">
		<tr>
			<td>DR No.</td>
			<td align="center">:</td>
			<td align="center"><input type="text" id="txtDR" name="txtDR" size="30"></td>
		</tr>
	</table>
</div>
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
		<tr class="trheader">
			<td>Received Qty.</td>
			<td>Good Qty.</td>
			<td>Defective Qty.</td>
		</tr>
		<tr class="trbody">
			<td align="center" id="tdSrecqty"></td>
			<td align="center">
				<input type="hidden" id="txtrecqty" name="txtrecqty" size="10" class="cntrd">
				<input type="text" id="txtgoodqty" name="txtgoodqty" size="10" class="cntrd txtqtys numbersonly">
			</td>
			<td align="center">
				<input type="text" id="txtdefqty" name="txtdefqty" size="10" class="cntrd txtqtys numbersonly">
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
	dialogClass:"no-close",
	autoOpen: false,
	width:1000
});
$("#divDR").dialog({
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
				$("#txtDR").val("");
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
					var DRNO	=	$("#txtDR").val();
					var errmsg	=	"";
					if(DRNO == "")
					{
						MessageType.infoMsg("Please input DR no.");
					}
					else
					{
						MessageType.confirmmsg(PCW_mto_update_funcs.receiveMTO,"Do you want to receive this MTO?",MTONO);
					}
				}
	      	}
	    }
  	]
});
$("#divscanning").dialog({
	dialogClass: "no-close fixed-dlg",
	closeOnEscape:false,
	title:'Scanning',
	width:400,resizable:false, height: "auto", modal:true, autoOpen: false,draggable: true,
	buttons:
	[
		  {
		      text: "Close",
		      icons: {
		        primary: "ui-icon ui-icon-cancel"
		      },
		      click: function() {
		      	$("#errmsg").text("");
		      	$("#sucmsg").text("");
		      	$("#txtscan").val("");
		      	$("#txtrecqty").val("");
		      	$("#tdSrecqty").text("");
		      	$("#txtgoodqty").val("");
		      	$("#txtdefqty").val("");
				$(this).dialog("close");
				$("#divmtodtls").dialog("close");
		      }
	    },
	    {
			text: "Save",
	      	icons: {
	        	primary: "ui-icon ui-icon-disk"
	      	},
	      	click: function() 
				{
					var errmsgS		=	"";
					var scannedval	=	  $("#txtscan").val();
					var recqty		=	+ $("#txtrecqty").val();
					var goodqty		=	+ $("#txtgoodqty").val();
					var defqty		=	+ $("#txtdefqty").val();
					var itemno		=	  $("#hdnitemno").val();
					var mtono		=	  $("#tdmtono").text();
					if(scannedval == "")
					{
						$("#errmsg").text("Please scan an item.");
						$("#txtscan").focus();
						return;
					}
					
					if(errmsgS == "")
					{
						var totcnt		=	$("#tdtotqty").attr("data-totcnt");
						var a			=	$("#hdncurrcnt").val();
						var unitprice	=	+ $("#tdunitprice"+a).text();
						var grossamt	=	unitprice * recqty;
						$("#tdrecqty"+a).text(recqty);
						$("#tdgoodqty"+a).text(goodqty);
						$("#tddefqty"+a).text(defqty);
						$("#tdgrossamt"+a).text(inputAmount.getNumberWithCommas(grossamt.toFixed(2)));
						PCW_mto_update_funcs.getTotals(a);
						$.ajax({
							data		:{ITEMNO:itemno, MTONO:mtono, RECQTY:recqty, GOODQTY:goodqty, DEFQTY:defqty,GROSSAMT:grossamt},
							type		:"POST",
							url			:"mto_update.php?action=UPDATEITEM",
							beforeSend	:function(){
								
							},
							success		:function(response){
								$("#errmsg").html(response);
								$("#txtrecqty").val("");
								$("#tdSrecqty").text("");
								$("#txtgoodqty").val("");
								$("#txtdefqty").val("");
								$("#txtscan").val("");
								$("#txtscan").focus();
							}
						});
					}
				}
	      	}
	    
  	]
});
$("#divscanning").dialog().parent().css({'position':'fixed','top':'50px','left':'40.5%','zIndex':'10000 !important'});
</script>