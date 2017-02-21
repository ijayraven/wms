<div id="divusers">
	<table width="100%" bgcolor="#ffffe9">
		<tr class="trheader">
			<td>USER INFORMATION</td>
		</tr>
		<tr class="">
			<td align="center">
				<form id="frmuser">
				<input type="hidden" id="txtuserid" name="txtuserid">
				<table class="label_text">
						<tr>
							<td>Name</td>
							<td>:</td>
							<td>
								<input type="text" name="txtname" id="txtname" size="30">
							</td>
						</tr>
						<tr>
							<td>Username</td>
							<td>:</td>
							<td><input type="text" name="txtusername" id="txtusername" size="20"></td>
						
						</tr>
						<tr>
							<td>Password</td>
							<td>:</td>
							<td><input type="password" name="txtpassword" id="txtpassword" size="20"></td>
						</tr>
						<tr>
							<td>Confirm Password</td>
							<td>:</td>
							<td><input type="password" name="txtcpassword" id="txtcpassword" size="20" ></td>
						</tr>
						<tr>
							<td>Department</td>
							<td>:</td>
							<td>
								<select id="seldep" name="seldep">
										<option value="">--Please Select--</option>
										<option value="Accounting">Accounting</option>
										<option value="HRGA">HRGA</option>
										<option value="IT">IT</option>
										<option value="LMC">LMC</option>
										<option value="Marketing">Marketing</option>
										<option value="MPC">MPC</option>
										<option value="Product Planner">Product Planning</option>
										<option value="Sales">Sales</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>User Level</td>
							<td>:</td>
							<td>
									<select name="sellevel" id="sellevel">
										<option value="">--Please Select--</option>
										<option value="Admin">Admin</option>
										<option value="User">User</option>
									</select>
							</td>
						</tr>
						<tr>
							<td>Status</td>
							<td>:</td>
							<td>
								<select name="selstatus" id="selstatus">
									<option value="">--Please Select--</option>
									<option value="Active">Active</option>
									<option value="Inactive">Inactive</option>
								</select>
							</td>	
						</tr>
				</table>
			</form>
			</td>
		</tr>
	</table>
</div>
<div id="divEmodules"></div>
<div id="divinfomsg" style="display:none;" class="divdialogs">
	<div class="ui-widget shadowed" >
		<div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;">
			<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
			<strong>Alert:</strong> <a id="txtinfomsg"></a></p>
		</div>
	</div>
</div>
<div id="divloader" style="display:none;" align="center"><img src="/wms/images/loading/animated-loading.gif" width="100%"><p>Please wait...</p></div>

<script>
$( "button:first" ).button({
	icons: {
		primary: "ui-icon ui-icon-search"
	},
	}).next().button({
	icons: {
		primary: "ui-icon ui-icon-person"
	}
});
$("#divloader").dialog({
	dialogClass: "no-close",
	closeOnEscape:false,	
	title:'Processing',
	bgiframe:true, resizable:false, height: "auto", width: 250, modal:true, autoOpen: false,draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 }
});
$("#divusers").dialog({
	bgiframe:true, resizable:false,width:600, modal:true, autoOpen: false,closeOnEscape:false,
	overlay: {backgroundColor: '#000', opacity: 0.5},
	dialogClass:"C_divusers no-close",
	buttons: {
		'Close': function()
		{
			cancel();
			$(this).dialog('close');
		},
		'Save': function()
		{
			if(validate())
			{
				var frmuser = $("#frmuser").serialize();
				if($("#txtuserid").val() == "")
				{
					if(confirm("You are about to save this record."))
					{
						$.ajax({
								type		:	'POST',
								data		:	frmuser,
								url			:	'userconfig.php?action=SAVEUSER',
								beforeSend	:	function()
											{
												$('#divloader').dialog("open");
											},
								success		:	function(response)
											{
												$('#divdebug').html(response);
												$('#divloader').dialog("close");
												cancel();
											}
							 });
					}
				}
				else
				{
					if(confirm("You are about to update this record."))
					{
						$.ajax({
								type		:	'POST',
								data		:	frmuser,
								url			:	'userconfig.php?action=UPDATEUSER',
								beforeSend	:	function()
											{
												$('#divloader').dialog("open");
											},
								success		:	function(response)
											{
												$('#divdebug').html(response);
												$('#divloader').dialog("close");
												cancel();
											}
							 });
					}
				}
			}
			else
			{
				$("#txtinfomsg").text("Please fill in the missing fields.");
				$("#divinfomsg").dialog("open");
			}
		}
	}
});
$("#divEmodules").dialog({
		modal:true,
		title:"Edit User Modules Access",
		closeOnEscape:false,
		dialogClass:"no-close",
		autoOpen: false,
		width:900,
		buttons: [
			{
				text: "Cancel",
				click: function()
				{
					$(this).dialog("close");
				}
			},
			{
				text: "Update",
				click: function() 
				{
					if(validateEmodules())
					{
						if(confirm("You are about to update the modules access of this user."))
						{
							var frmmodules		=	$("#frmmodules").serialize();
							var userid			=	$("#txtMuserid").val();
							$.ajax({
									data		:	frmmodules,
									type		:	"POST",
									url			:	'userconfig.php?action=SAVEMODULES&USERID='+userid,
									beforeSend	:	function()
												{
													$('#divloader').dialog("open");
												},
									success		:	function(response)
												{
													$("#divdebug").html(response);
													$('#divloader').dialog("close");
												}
									});
						}
					}
				}
			}
		]
	});
$("#divinfomsg").dialog({
	title:"Message!",
	modal:true,
	autoOpen: false,
	width: 500,
	buttons: [
		{
			text: "Ok",
			click: function() {
				$( this ).dialog( "close" );
			}
		}
	]
});
</script>