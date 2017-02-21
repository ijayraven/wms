<div id="divmposdtls">
	<table  class="label_text" width="100%" border="0">
		<tr>
			<td width="10%">MPOS NO</td>
			<td id="tdmposno"></td>
			<td width="15%">MPOS DATE</td>
			<td id="tdmposnodt"></td>
		</tr>
		<tr>
			<td>CUSTOMER</td>
			<td id="tdcustomer"></td>
			<td>TOTAL QTY</td>
			<td id="tdtotqty"></td>
		</tr>
		<tr>
			<td>SR</td>
			<td id="tdsr"></td>
			<td>TOTAL AMOUNT</td>
			<td id="tdtotamount"></td>
		</tr>
		
		<tr>
			<td colspan="10">
				<div id="divitems" align="center"></div>
				<form id="frmitems">
					<input type='hidden' id='txtitems' name='txtitems'>
					<input type='hidden' id='txtitemsqty' name='txtitemsqty'>
					<input type='hidden' id='txtitemsdefqty' name='txtitemsdefqty'>
				</form>
				<div id="divitemsdebug"></div>
			</td>
		</tr>
	</table>
</div>
<div id="divscanning">
	<table class="label_text" border="0" align="center">
		<tr class="trheader">
			<td colspan="4" align="center">
				SCAN ITEM
			</td>
		</tr>
		<!--<tr>
			<td colspan="4" align="center">
				&nbsp;
			</td>
		</tr>
		<tr>
			<td colspan="4" align="center"id="tdscanningmode"class="bld">
				NORMAL SCANNING
			</td>
		</tr>-->
		<tr>
			<td colspan="4" align="center">
				<br>
			</td>
		</tr>
		<tr>
			<td>GOOD</td>
			<td align="center">
				:<input type="text" id="txtgood" name="txtgood" data-destination_val="txtgoodqty" placeholder='ITEM No./BARCODE' class="centered txtitemnos">
			</td>
			<td></td>
			<td align="center">
				<input type="text" id="txtgoodqty" name="txtgoodqty" data-destination_trigger="txtgood" placeholder='Quantity' class="centered txtqtys numbersonly" size="10" value="1">
			</td>
		</tr>
		<tr>
			<td>DEFECTIVE</td>
			<td align="center">
				:<input type="text" id="txtdef" name="txtdef" data-destination_val="txtdefqty" placeholder='ITEM No./BARCODE' class="centered txtitemnos">
			</td>
			<td></td>
			<td align="center">
				<input type="text" id="txtdefqty" name="txtdefqty" data-destination_trigger="txtdef" placeholder='Quantity' class="centered txtqtys numbersonly" size="10" value="1">
			</td>
		</tr>
		<tr>
			<td>IB</td>
			<td align="center">
				:<input type="text" id="txtib" name="txtib" data-destination_val="txtibqty" placeholder='ITEM No./BARCODE' class="centered txtitemnos">
			</td>
			<td></td>
			<td align="center">
				<input type="text" id="txtibqty" name="txtibqty" data-destination_trigger="txtib" placeholder='Quantity' class="centered txtqtys numbersonly" size="10" value="1">
			</td>
		</tr>
		<!--<tr>
			<td></td>
			<td colspan="3" align=""><br>
				<div class="radioset">
					<input type="radio" id="btnnormalscan" name="btnscans" value="Normal Scan" class="buttons font20 btnscans" data-mode="NORMAL SCANNING" checked>
					<label for="btnnormalscan">NORMAL</label>
					<input type="radio" id="btnadditemscan" name="btnscans" value="Add Item Scan" class="buttons font20 btnscans" data-mode="ADD ITEM SCANNING">
					<label for="btnadditemscan">ADD ITEM</label>
				</div>
			</td>
		</tr>-->
		<tr>
			<td colspan="4" align="center">
				<h3 id="errmsg" style="color:red;"></h3>
			</td>
		</tr>
	</table>
</div>
<div>
	
</div>
<script>
$("#divscanning").dialog({
	dialogClass: "no-close fixed-dialog",
	closeOnEscape:false,	
	position: { my: "center", at: "center", of: window },
	title:'Scanning',
	bgiframe:true, resizable:false, height: "auto", width: 330, autoOpen: false,draggable: true
});

$("#divmposdtls").dialog({
	dialogClass: "no-close dialog-1 diaMPOS",
	closeOnEscape:false,	
	position: { my: "center", at: "top", of: window },
	title:'SCAN ITEMS',
	bgiframe:true, resizable:false, height: "auto", width: 1000, modal:true, autoOpen: false,draggable: false,
	buttons:{
				"Close":function(){
					$(this).dialog("close");
					$("#divscanning").dialog("close");
					$(".txtitemnos").val("");
					$("#errmsg").text("");
					$("#divitems").html("");
					$("#btnsearch").trigger("click");
					scanningFuns.truncateTmpTbl();
				},
				"Save":function(){
					 var mode	=	$(".diaMPOS .ui-button-text:contains(Save)").text();
					 var endmsg;
					if(mode == "Save")
					{
						endmsg	=	"save";
					}
					else
					{
						endmsg	=	"update";
					}
					MessageType.confirmmsg(scanningFuns.saveScannedItems,"Do you want to "+ endmsg + " this record?",mode);
				}
	}
});
</script>