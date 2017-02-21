<table cellpadding="0" cellspacing="0" border="1" align="center" width="100%">
		<tr>
			<td height="28px" width="15%">&nbsp;</td>
			<td height="28px" width="30%" align="right"><div><img src="images/loader_icon/ajax-loader.gif" id="spinnerAdd" align="middle"></div>&nbsp;</td>
			<td height="28px" width="30%" align="left"><div id="dvSearchAdd" style="padding-left:10px; color: red; font-size: 12px;"></div>&nbsp;</td>
			<td height="28px" width="25%">&nbsp;</td>						
		</tr>
		<tr>
			<td colspan="4"><hr></td>
		</tr>
		<tr>
			<td colspan="4" align="left"><b>VENDOR INFORMATION</b></td>
		</tr>
		<tr>
			<td colspan="4"><hr></td>
		</tr>
		<!--<tr>
			<td height="25px" align="right">VENDOR NAME &nbsp;</td>
			<td colspan="2">: <input type="text" id="txtAddName" name="txtAddName" size="60" class="boxcolor"></td>
			<td>&nbsp;</td>
		</tr>-->
		<tr>
			<td height="25px" align="right">COMPANY/VENDOR NAME &nbsp;</td>
			<td colspan="2">: <input type="text" id="txtAddName" name="txtAddName" size="60" class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)"></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td height="25px" align="right">TRADE/BUSINESS NAME &nbsp;</td>
			<td colspan="2">: <input type="text" id="txtAddTradeName" name="txtAddTradeName" size="60" class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)"></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td height="25px" align="right">SHORT NAME &nbsp;</td>
			<td colspan="2">: <input type="text" id="txtAddShortName" name="txtAddShortName" size="25"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)"></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td height="25px" align="right">STREET NAME &nbsp;</td>
			<td colspan="2">: <input type="text" id="txtAddStreetName" name="txtAddStreetName" size="25"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)"></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td height="25px" align="right">BLDG./SUITE &nbsp;</td>
			<td colspan="2">: <input type="text" id="txtAddBldg" name="txtAddBldg" size="25"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)"></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td height="25px" align="right">CITY &nbsp;</td>
			<td colspan="2">: <input type="text" id="txtAddCity" name="txtAddCity" size="25"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)"></td>
			<td>&nbsp;</td>
		</tr>
		
		<tr>
			<td height="25px" align="right">ZIP CODE &nbsp;</td>			
			<td>: <input type="text" id="txtAddZip" name="txtAddZip" size="13"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									
					COUNTRY
			</td>
			<td>  : <!--<input type="text" id="txtAddCountryCode" name="txtAddCountryCode" size="13"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;COUNTRY -->
			
					<select id="selAddCountry" name="selAddCountry"  class="boxcolor">
						<option value=""> --Please Select-- </option>
						<?
							foreach ($country as $key => $lilVal)
							{										
						?>
								<option value="<?php echo $lilVal['COUNTRYDESC']; ?>"><?php echo trim($lilVal['COUNTRYDESC']); ?>
						<?																								
							}
						?>	
					</select>
			</td>	
			<td> &nbsp;
			</td>			
		</tr>
		<tr>
			<td height="25px" align="right">PHONE NO. &nbsp;</td>
			<td>: <input type="text" id="txtAddPhone" name="txtAddPhone" size="15"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,2)"> 
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;FAX N0. </td>
			<td colspan="2"> : <input type="text" id="txtAddFaxNo" name="txtAddFaxNo" size="15"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)">
			</td>			
		</tr>
		<tr>
			<td height="25px" align="right">VENDOR REP. &nbsp;</td>
			<td colspan="3">: <input type="text" id="txtAddVendorRep" name="txtAddVendorRep" size="25"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)">
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;OWNER'S NAME : <input type="text" id="txtAddOwner" name="txtAddOwner" size="25"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)" onblur="fncToUpper(this.id)">
			</td>			
		</tr>
<!--		<tr>
			<td height="25px" align="right">BUSINESS NAME &nbsp;</td>
			<td colspan="2">: <input type="text" id="txtAddBusName" name="txtAddBusName" size="60"  class="boxcolor" onKeyPress="return validateSpecialChar(this,event,1)"></td>
			<td>&nbsp;</td>
		</tr>-->
		<tr>
			<td height="25px" align="right">EMAIL ADD 1 &nbsp;</td>
			<td colspan="3">: <input type="text" id="txtAddEmail1" name="txtAddEmail1" size="25"  class="boxcolor">
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; EMAIL ADD 2 : <input type="text" id="txtAddEmail2" name="txtAddEmail2" size="25" style="background:#FDFACD;">
			</td>		
		</tr>
		
		<tr>
			<td colspan="4"><hr></td>
		</tr>
		<tr>
			<td colspan="4" align="left"><b>FINANCIAL INFORMATION</b></td>
		</tr>
		<tr>
			<td colspan="4"><hr></td>
		</tr>
		
		<tr>
			<td height="25px" align="right">TERM CODE &nbsp;</td>
			<td colspan="3">: <select id="selTermCode" name="selTermCode"  class="boxcolor">
								<option value=""> --Please Select-- </option>
								<?
									foreach ($terms as $key => $lilVal)
									{										
								?>
										<option value="<?php echo $lilVal['TermsID']; ?>"><?php echo trim(str_pad($lilVal['TermsID'],2,0,STR_PAD_LEFT)).' - '.trim($lilVal['Terms']); ?>
								<?																								
									}
								?>	
							</select>									
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							&nbsp;VENDOR TYPE &nbsp; : 
							<select id="selVendorType" name="selVendorType"  class="boxcolor">
									<option value=""> --Please Select-- </option>
									<option value="F">F - Foreign</option>
									<option value="L">L - Local</option>
									<option value="B">B - Bookkeeping</option>
							</select>
			</td>		
		</tr>
		<tr>
			<td height="25px" align="right">CURRENCY CODE &nbsp;</td>
			<td colspan="3">: <select id="selCurrCode" name="selCurrCode"  class="boxcolor">
								<option value=""> --Please Select-- </option>
								<?
									foreach ($currency as $key => $lilVal)
									{										
								?>
										<option value="<?php echo $lilVal['CURRENCYCODE']; ?>"><?php echo trim($lilVal['CURRENCYCODE']).' - '.trim($lilVal['DESCRIPTION']); ?>
								<?																								
									}
								?>	
							 </select>	
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
							VATABLE &nbsp; :
							<select id="selVatable" name="selVatable" class="boxcolor">
									<option value=""> --Please Select-- </option>
									<option value="Y">Yes</option>
									<option value="N">No</option>									
							</select>
			</td>		
		</tr>
		<tr>
			<td height="25px" align="right">VENDOR STATUS &nbsp;</td>
			<td colspan="3">: <input type="radio" id="radVendorStatActive" name="radVendorStat" value="Active" checked> <span class="label_text2">Active</span> &nbsp;
							  <input type="radio" id="radVendorStatInActive" name="radVendorStat" value="Inactive"> <span class="label_text2">Inactive</span>
			</td>		
		</tr>
	</table>