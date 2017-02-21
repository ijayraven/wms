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
		$selcusttype=	$_GET["selcusttype"];
		if($selcusttype == "NBS")
		{
			$BRANCHCODE_Q	=	" AND CustomerBranchCode != ''";
		}
		if($selcusttype == "TRADE")
		{
			$BRANCHCODE_Q	=	" AND CustomerBranchCode = ''";
		}
		$sel	 =	"SELECT CustNo,CustName FROM  FDCRMSlive.custmast WHERE 1 $BRANCHCODE_Q";
		
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
			$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"STF/INV","Q_SEARCHCUST");
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
		var custtype	=	$('#selcusttype').val();
		var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
		if(txtcustno != '' || txtcustname != '')
		{
			if(evthandler != 40 && evthandler != 13 && evthandler != 27)
			{
				$.ajax({
						url			:	'stf_inv.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname+'&selcusttype='+custtype,
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
											$("#divselcust").css({ position:'absolute'});
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
//		if($("#txtcustno").val() == "" || $("#txtcustname").val() == "")
//		{
//			errmsg	=	"Please select customer.\n";
//		}
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
		if($("#tfrom").val() != "" || $("#tto").val() != "")
		{
			if($("#tfrom").val() > $("#tto").val())
			{
				errmsg	+=	"Invalid time range.\n";
			}
		}
//		if($('input[name=rdoordertype]:checked', '#dataform').val() == undefined)
//		{
//			errmsg	+=	"Please select Order Type.";
//		}
		
		if(errmsg == "")
		{
			window.open("stf_inv_PDF.php?"+dataform);
		}
		else
		{
			MessageType.infoMsg(errmsg);
		}
	});
	$("#selcusttype").change(function(){
		if($(this).val() != "NBS/TRADE")
		{
			$("#txtcustno").removeAttr("disabled"); 
			$("#txtcustname").removeAttr("disabled"); 
		}
		else
		{
			$("#txtcustno").attr("disabled","disabled");
			$("#txtcustname").attr("disabled","disabled");
			$("#txtcustno").val("");
			$("#txtcustname").val("");
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
								<option value="NBS/TRADE">All</option>
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
						<td>ALPHA SUFFIX</td>
						<td>:</td>
						<td>
							<?php
							$getalpha	=	"SELECT * FROM FDC_PMS.ALPHA_SUFFIX WHERE STATUS = 'ACTIVE' ORDER BY DESCRIPTION";
							$RSgetalpha	=	$conn_250_171->Execute($getalpha);
							if($RSgetalpha == false)
							{
								echo $conn_250_171->ErrorMsg()."::".__LINE__; exit();
							}
							?>
							<select id="selalpha" name="selalpha">
								<option value=""><-- Please Select --></option>
								<?php
								
								while (!$RSgetalpha->EOF) {
									
									$alpha	=	$RSgetalpha->fields["ALPHA_SUFFIX"];
									$desc	=	$RSgetalpha->fields["DESCRIPTION"];
									echo "<option value='$alpha'>$desc</option>";
									$RSgetalpha->MoveNext();
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td>DATE<span style="color:red;">*</span></td>
						<td>:</td>
						<td>
							<input type="text" name="dfrom" id="dfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;TO&nbsp;&nbsp;
						 	<input type="text" name="dto" 	id="dto" 	class="dates"	value="" size="10"  placeholder = "To"	>
						</td>
					</tr>
					<tr>
						<td>TIME</td>
						<td>:</td>
						<td>
							<input type="text" name="tfrom" id="tfrom" class="time" value="" size="10"  placeholder = "00:00:00">&nbsp;&nbsp;TO&nbsp;&nbsp;
						 	 <input type="text" name="tto" 	id="tto" 	class="time" value="" size="10"  placeholder = "00:00:00"	>
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
							<button type="button" id="btnreport" class="btnsubmit">Submit</button>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>