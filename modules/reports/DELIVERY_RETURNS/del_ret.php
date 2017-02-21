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
		$sel	 =	"SELECT CustNo,CustName FROM  FDCRMSlive.custmast WHERE 1 AND  LENGTH(CustNo) > 6";
		
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
			$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"DELIVERIES/RETURNS","Q_SEARCHCUST");
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
				echo "<option value=\"$cValue\" onclick=\"smartsel('click');\">"."$q_custno-$Q_custname"."</option>";
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
						url			:	'del_ret.php?action=Q_SEARCHCUST&CUSTNO='+txtcustno+'&CUSTNAME='+txtcustname,
						beforeSend	:	function()
									{
									},
						success		:	function(response)
									{
										if(response == '')
										{
											$("#txtinfomsg").text("No records found.");
											$("#divinfomsg").dialog("open");
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
		if($("#txtcustno").val() == "" || $("#txtcustname").val() == "")
		{
			errmsg	=	"Please select customer.\n";
		}
		if($("#deldfrom").val() == "" && $("#deldto").val() == "")
		{
			errmsg	+=	"Please input delivery date range.\n";
		}
		else
		{
			if($("#deldfrom").val() > $("#deldto").val())
			{
				errmsg	+=	"Invalid delivery date range.\n";
			}
		}
		
		if($("#retdfrom").val() == "" && $("#retdto").val() == "")
		{
			errmsg	+=	"Please input return date range.\n";
		}
		else
		{
			if($("#retdfrom").val() > $("#retdto").val())
			{
				errmsg	+=	"Invalid return date range.\n";
			}
		}
		if(errmsg == "")
		{
			window.open("del_ret_PDF.php?"+dataform);
		}
		else
		{
			alert(errmsg);
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
						<td>CUSTOMER<span style="color:red;">*</span></td>
						<td>:</td>
						<td>
							<input type="text" id="txtcustno" name="txtcustno" size="10" placeholder='CODE' class="searchcust centered">
							<input type="text" id="txtcustname" name="txtcustname" size="35" placeholder='NAME' class="searchcust centered">
							<div id="divselcust" class="divsel"></div>
							<input type="hidden" id="hdnval" name="hdnval" value="">
						</td>
					</tr>
					<tr>
						<td>ITEM NO</td>
						<td>:</td>
						<td>
							<input type="text" id="txtitemno" name="txtitemno" size="20" placeholder='ITEM NO.'class="centered">
						</td>
					</tr>
					<tr>
						<td>DELIVERY DATE<span style="color:red;">*</span></td>
						<td>:</td>
						<td>
							<input type="text" name="deldfrom" id="deldfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;TO&nbsp;&nbsp;
						 	<input type="text" name="deldto" 	id="deldto" 	class="dates"	value="" size="10"  placeholder = "To"	>
						</td>
					</tr>
					<tr>
						<td>RETURN DATE<span style="color:red;">*</span></td>
						<td>:</td>
						<td>
							<input type="text" name="retdfrom" id="retdfrom" 	class="dates" 	value="" size="10"  placeholder = "From">&nbsp;&nbsp;TO&nbsp;&nbsp;
						 	<input type="text" name="retdto" 	id="retdto" 	class="dates"	value="" size="10"  placeholder = "To"	>
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