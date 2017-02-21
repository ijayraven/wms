<?php
session_start();
include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");
if (empty($_SESSION['username'])) 
{
	echo "<script>
				MessageType.sessexpMsg('wms');
		  </script>";
	exit();
}
$action	=	$_GET['action'];
	
	if ($action=='Q_SEARCHCUST') 
	{
		$custno		=	addslashes($_GET['CUSTNO']);
		$custname	=	addslashes($_GET['CUSTNAME']);
			
		$sel	 =	"SELECT CustNo,CustName FROM  FDCRMSlive.custmast WHERE 1";
		
		if (!empty($custno)) 
		{
		$sel	.=	" AND CustNo like '%{$custno}%' ";
		}
		if (!empty($custname)) 
		{
		$sel	.=	" AND CustName like '%{$custname}%' ";
		}
		$sel	.=	" limit 20 ";
		$rssel	=	$conn_255_10->Execute($sel);
		if ($rssel == false) 
		{
			$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
			$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"SKU SUMMARY","Q_SEARCHCUST");
			$DATASOURCE->displayError();
		}
		$cnt	=	$rssel->RecordCount();
		if ($cnt > 0) 
		{
			echo "<select id='selcust' class = 'C_dropdown divsel' style='width:532px;height:auto;' onkeypress='smartsel(event);' multiple>";
			while (!$rssel->EOF) 
			{
				$q_custno	=	$rssel->fields['CustNo'];
				$Q_custname	=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['CustName']);
				$cValue		=	$q_custno."|".$Q_custname;
				echo "<option value=\"$cValue\" onclick=\"smartsel('click');\">".$Q_custname."</option>";
				$rssel->MoveNext();
			}
			echo "</select>";
		}
		else
		{
			echo "";
		}
		exit();
	}

?>
<script>
$("document").ready(function(){
	$(".searchcust").keyup(function(evt){
		var txtcustno	=	$('#txtcustno').val();
		var txtcustname	=	$('#txtcustname').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'sku_summary.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname,
						beforeSend	:	function()
									{
									},
						success		:	function(response)
									{
										if(response == '')
										{
											MessageType.infoMsg("No records found.");
											$('#divselcust').html('');
										}
										else
										{
											$('#divselcust').html(response);
											var position =$("#txtcustno").position();
											var selwidth	=	$("#txtcustno").width() + $("#txtcustname").width()+12;
											$("#divselcust").css({ position:'absolute',zIndex:1000000});
											$('#divselcust').show();
											$('#selcust').css({width:selwidth});
										}
									}
				});
			}
			else if(evthandler == 40 && $('#divselcust').html() != '')
			{
				$('#selcust').focus();
			}
			else
			{
				$('#divselcust').html('');
			}
		}
		else
		{
			$('#divselcust').html('');
			$('#divcust').html('');
		}
	});
	$("#btnreport").click(function(){
		var errmsg		=	"";
		var dataform	=	$("#dataform").serialize();
		if($("#dfrom").val() == "" && $("#dto").val() == "")
		{
			errmsg	+=	"Please input date range.\n";
		}
		else
		{
			if($("#dfrom").val() > $("#dto").val())
			{
				errmsg	+=	"Invalid date range.\n";
			}
		}
		if(errmsg == "")
		{
			window.open("sku_summary_PDF.php?"+dataform);
		}
		else
		{
			MessageType.infoMsg(errmsg);
		}
	});
	$("#btnreportCSV").click(function(){
		var errmsg		=	"";
		var dataform	=	$("#dataform").serialize();
		if($("#dfrom").val() == "" && $("#dto").val() == "")
		{
			errmsg	+=	"Please input date range.\n";
		}
		else
		{
			if($("#dfrom").val() > $("#dto").val())
			{
				errmsg	+=	"Invalid date range.\n";
			}
		}
		if(errmsg == "")
		{
			window.open("sku_summary_CSV.php?"+dataform);
		}
		else
		{
			MessageType.infoMsg(errmsg);
		}
	});
	$("#sel_class").change(function(){
		if($(this).val() == "SEASONAL")
		{
			$("#trseasons").show();
		}
		if($(this).val() == "EVERYDAY")
		{
			$("#trseasons").hide();
			$("#sel_seasons").val("");
		}
	});
});
function	smartsel(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnval').val($('#selcust').val());
		var vx = $('#hdnval').val();
		var x = vx.split('|'); 
		$('#txtcustno').val(x[0]);
		$('#txtcustname').val(x[1]);
		$('#divselcust').html('');
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnval').val($('#selcust').val());
			var vx = $('#hdnval').val();
			var x = vx.split('|'); 
			$('#txtcustno').val(x[0]);
			$('#txtcustname').val(x[1]);
			$('#divselcust').html('');
		}
	}
}
</script>
<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
	<table width="100%" border="0"  class="Text_header">
		<tr>
			<td align="center" class="tdoptions">
				<table border="0"class="label_text">
					<tr>
						<td>CUSTOMER TYPE</td>
						<td>:</td>
						<td>
							<select id="selcusttype" name="selcusttype">
								<option value=""><-- Please Select --></option>
								<option value="NBS">NBS</option>
								<option value="TRADE">TRADE</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>CUSTOMER</td>
						<td>:</td>
						<td>
							<input type="text" id="txtcustno" name="txtcustno" size="10" placeholder='CODE' class="searchcust">
							<input type="text" id="txtcustname" name="txtcustname" size="35" placeholder='NAME' class="searchcust">
							<div id="divselcust" class="divsel"></div>
							<input type="hidden" id="hdnval" name="hdnval" value="">
						</td>
					</tr>
					<tr>
						<td>
							CLASS
						</td>
						<td>:</td>
						<td>
							<select id="sel_class" name="sel_class">
								<option value=""><-- Please Select --></option>
								<option value="EVERYDAY">EVERYDAY</option>
								<option value="SEASONAL">SEASONAL</option>
							</select>
						</td>
					</tr>
					<tr id="trseasons" style="display:none;">
						<td>SEASONS</td>
						<td>:</td>
						<td>
							<select id="sel_seasons" name="sel_seasons">
								<option value=""><-- Please Select --></option>
								<option value="M">MOTHER'S DAY</option>
								<option value="F">FATHER'S DAY</option>
								<option value="X">CHRISTMAS</option>
								<option value="H">VALENTINES DAY</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>DATE</td>
						<td>:</td>
						<td>
							<input type="text" name="dfrom" id="dfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;TO&nbsp;&nbsp;
						 	<input type="text" name="dto" 	id="dto" 	class="dates"	value="" size="10"  placeholder = "To"	>
						</td>
					</tr>
					<tr>
						<td>ORDER TYPE</td>
						<td>:</td>
						<td>
							<label for="rdoSTF"><input type="radio" name="rdoordertype" 	id="rdoSTF" 	value="STF">STF</label>
						 	<label for="rdoINV"><input type="radio" name="rdoordertype" 	id="rdoINV" 	value="Invoice">INVOICE</label>
						</td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td></td>
						<td>
							<button type="button" id="btnreport" class="btnsubmit">PDF</button>
							<button type="button" id="btnreportCSV" class="btnsubmit">CSV</button>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>