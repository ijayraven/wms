
<style type="text/css">
body {
	font-size: 62.5%;
	font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
}

table {
	font-size: 1em;
}

.action_butt {
	cursor:pointer;
}

.label_text {
	font-size: 10px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #005870;
}

.label_text10 {
	font-size: 10px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #444444;
}

.error{
	background: #f8dbdb;
	border-color: #e77776;
}

.errortext{
	color: red;
	font-weight: bold;
}

.text_color{
	background:#FDFACD;
}
.select_color{
	background:#FDFACD;
	width:200px;
}

.text_white11 {
    font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #ffffff;
}

.text_white10 {
    font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #ffffff;
}

tr.dtl {
	color: #336699;
	font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
}

tr.dtl:hover td { 
	background: #dddddd; 
}

.no-close
.ui-dialog-titlebar-close{ display:none; }
.div_text_shadow 
{
color: rgb(181, 223, 255);
font-size: 24px;
text-shadow: rgb(54, 54, 54) 5px 3px 4px;
}
</style>
<div id="dialog_view" title="ITEM ASSEMBLY"><!--start of contents//-->
	<table border="1" cellpadding="0" cellspacing="0" width="100%">
		<tr>
	 		<td width="15%" height="20px" class="label_text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Transaction No</td>
	 		<td width="15%" class="label_text10">:&nbsp;&nbsp;<label id="lblTransNo" name="lblTransNo"></td>
	 		<td width="15%" height="20px" class="label_text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Item Number</td>
	 		<td width="15%" class="label_text10">:&nbsp;&nbsp;<label id="lblItemNo" name="lblItemNo"></td>
	 		<td rowspan="7" colspan="2" class="label_text10">
				<img id="imgitem" src="<?php echo $rsImage; ?>" width="200px" height="200px" style="border:solid 2px #0F1F00;">
			</td>
		</tr>
	 	<tr>
	 		<td width="15%"  height="20px"class="label_text" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Member</td>
	 		<td width="15%" class="label_text10">:&nbsp;&nbsp;<label id="lblMember" name="lblMember"></td>
	 		<td width="15%"  height="20px"class="label_text" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Added Date</td>
	 		<td width="15%" class="label_text10">:&nbsp;&nbsp;<label id="lblAddedDate" name="lblAddedDate"></td>
	 	</tr>
	 	<tr>
		 	<td colspan="" width="10%" height="20px" class="label_text" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Description</td>
	 		<td colspan="3" class="label_text10">:&nbsp;&nbsp;<label id="lblDesc" name="lblDesc"></td>
	 	</tr>
	 	<tr>
	 		<td class="label_text" height="20px" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;UOM</td>
	 		<td class="label_text10">:&nbsp;&nbsp;<label id="lblUOM" name="lblUOM"></td>
	 	</tr>
	 	<tr>
	 		<td height="20px" class="label_text" ><!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Instruction--></td>
	  		<td colspan="2" class="label_text10"><!--:&nbsp;&nbsp;<label id="lblIns1" name="lblIns1">--></td>
	  	</tr>
	  	<tr>
	 		<td height="20px" class="label_text" ></td>
	 		<td colspan="2" class="label_text10"><!--:&nbsp;&nbsp;<label id="lblIns2" name="lblIns2>"--></td>
	 	</tr>
	 	<tr>
	 		<td height="20px" class="label_text" ></td>
	 		<td colspan="2" class="label_text10"><!--:&nbsp;&nbsp;<label id="lblIns3" name="lblIns3>"--></td>
	 	</tr>
							 	
	 	<tr>
	 		<td height="1px">&nbsp;</td>
	 	</tr>
	 	<tr>
	 		<td align="center" colspan="5">
	 			<table width="90%" cellpadding="0" cellspacing="0" border="1">
	 				<tr align="center" background="../../images/images/pmscellpic3.gif">
	 					<td class="text_white10" width="5%" height="20px">LN</td>
	 					<td class="text_white10" width="10%">ITEM NUMBER</td>
	 					<td class="text_white10" width="25%">DESCRIPTION</td>
	 					<td class="text_white10" width="10%">QUANTITY</td>
	 					<td class="text_white10" width="20%">REMARKS</td>
	 					<td class="text_white10" width="20%">IMAGE</td>
<!--					 					<td class="text_white10" width="10%">ACTION</td>-->
	 				</tr>
	 			</table>
	 		</td>
	 	</tr>
	 	<tr>
	 		<td align="center" colspan="5">
		 		<div id="divResultview"> <?echo $htm;?> </div>	
	 		</td>
	 	</tr>
	 	<tr>
	 		<td>&nbsp;</td>
	 	</tr>
	 	<tr>
	 		<td height="20px" class="label_text" align="right"><label>INSTRUCTIONS</label>&nbsp;&nbsp;:&nbsp;&nbsp;&nbsp;</td>
	 		<td>&nbsp;
	 			<input type="text" id="ins" name="ins" value="<?php echo $_InsList ;?>">
	 		</td>
	 	</tr>
	 </table>
</div>