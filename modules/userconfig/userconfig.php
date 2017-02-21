<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='/wms/index.php'</script>";
}
$action	=	$_GET['action'];
if($action == "GETUSERS")
{
	$USER	=	$_GET["USER"];
	$STATUS	=	$_GET["STATUS"];
	if($USER != "")
	{
		$USER_Q		=	" AND USERNAME LIKE'%$USER%' OR NAME LIKE '%$USER%'";
	}
	if($STATUS != "")
	{
		$STATUS_Q	=	" AND STATUS = '$STATUS'";
	}
	$GETUSERS	=	"SELECT * FROM WMS_USERS.USERS WHERE 1 $USER_Q $STATUS_Q";
	$RSGETUSERS	=	$Filstar_conn->Execute($GETUSERS);
	if($RSGETUSERS == false)
	{
		$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		$table	=	"<table border='1'class='tblresult tablesorter'>
						<thead>
							<tr class='trheader' bgcolor='Teal'>
								<th>USERNAME</th>
								<th>NAME</th>
								<th>DEPARTMENT</th>
								<th>USER LEVEL</th>
								<th>STATUS</th>
								<th>ACTION</th>
							</tr>
						<thead>
						<tbody>";
		if($RSGETUSERS->RecordCount() > 0)
		{
			while (!$RSGETUSERS->EOF) {
				$ID 		= $RSGETUSERS->fields["ID"]; 
				$USERNAME 	= $RSGETUSERS->fields["USERNAME"]; 
				$NAME		= $RSGETUSERS->fields["NAME"]; 
				$DEPT 		= $RSGETUSERS->fields["DEPT"]; 
				$USERLEVEL 	= $RSGETUSERS->fields["USERLEVEL"]; 
				$STATUS		= $RSGETUSERS->fields["STATUS"]; 
				$btnedit	=	"<img class='action_butt btnedit'src='/wms/images/images/action_icon/new/pencil.png' title='Edit $NAME' data-userid='$ID'></img>";
				$btnmenu	=	"<img class='action_butt btnmenu'src='/wms/images/images/action_icon/new/bookshelf.png' title='Edit Module Access for $NAME' data-userid='$ID'></img>";
				$table	.=	"<tr class='trbody'>
								<td align='center'>$USERNAME</td>
								<td>$NAME</td>
								<td align='center'>$DEPT</td>
								<td align='center'>$USERLEVEL</td>
								<td align='center'>$STATUS</td>
								<td align='center'>$btnedit $btnmenu</td>
							 </tr>";
				$RSGETUSERS->MoveNext();
			}
		}
		else 
		{
			$table .= "<tr class='trbody'>
						<td align='center' style='color:red;' colspan='6'>No records found.</td>
					   </tr>";
		}
		$table	.=	"</tbody>
					</table>";
		echo $table;
	}
	exit();
}
if($action == "SAVEUSER")
{
	$name				= strtoupper($_POST['txtname']);
	$username 			= $_POST['txtusername'];
	$password 			= $_POST['txtpassword'];
	$position			= $_POST['txtposition'];
	$department			= $_POST['seldep'];
	$status				= $_POST['selstatus'];
	$level				= $_POST['sellevel'];
	$TODAY				= date("Y-m-d H:i:s");
	$BY					= $_SESSION['username'];
	$Filstar_conn->StartTrans();
	$SAVEUSER	=	"INSERT INTO WMS_USERS.USERS(`USERNAME`, `PASSWORD`, `NAME`, `DEPT`, `USERLEVEL`, `STATUS`, `ADDEDDATE`, `ADDEDBY`)
					 VALUES('$username','$password','$name','$department','$level','$status','$TODAY','$BY')";
	$RSSAVEUSER	=	$Filstar_conn->Execute($SAVEUSER);
	if($RSSAVEUSER == false)
	{
		$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		echo "<script>
				$('#txtinfomsg').text('$name\'s record has been successfully saved.');
				$('#divinfomsg').dialog('open');
				$('#btnsearch').trigger('click');
				$('#divusers').dialog('close');
			  </script>";
	}
	$Filstar_conn->CompleteTrans();
	exit();
}
if($action == "UPDATEUSER")
{
	$name				= strtoupper($_POST['txtname']);
	$userid 			= $_POST['txtuserid'];
	$username 			= $_POST['txtusername'];
	$password 			= $_POST['txtpassword'];
	$position			= $_POST['txtposition'];
	$department			= $_POST['seldep'];
	$status				= $_POST['selstatus'];
	$level				= $_POST['sellevel'];
	$TODAY				= date("Y-m-d H:i:s");
	$BY					= $_SESSION['username'];
	$Filstar_conn->StartTrans();
	$UPDATEUSER	=	"UPDATE WMS_USERS.USERS SET `USERNAME`='$username', `PASSWORD`='$password', `NAME`='$name', `DEPT`='$department', `USERLEVEL`='$level',
					`STATUS`='$status', `EDITDATE`='$TODAY', `EDITBY`='$BY'
					 WHERE ID='$userid'";
	$RSUPDATEUSER	=	$Filstar_conn->Execute($UPDATEUSER);
	if($RSUPDATEUSER == false)
	{
		$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		echo "<script>
				$('#txtinfomsg').text('$name\'s record has been successfully updated.');
				$('#divinfomsg').dialog('open');
				$('#btnsearch').trigger('click');
				$('#divusers').dialog('close');
			  </script>";
	}
	$Filstar_conn->CompleteTrans();
	exit();
}
if($action == "EDITUSERS")
{
	$USERID = $_GET["USERID"];
	$GETUSER	=	"SELECT * FROM WMS_USERS.USERS WHERE ID = '$USERID'";
	$RSGETUSER	=	$Filstar_conn->Execute($GETUSER);
	if($RSGETUSER == false)
	{
		$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		$ID 		= $RSGETUSER->fields["ID"]; 
		$USERNAME 	= $RSGETUSER->fields["USERNAME"]; 
		$PASSWORD 	= $RSGETUSER->fields["PASSWORD"]; 
		$NAME		= $RSGETUSER->fields["NAME"]; 
		$DEPT 		= $RSGETUSER->fields["DEPT"]; 
		$USERLEVEL 	= $RSGETUSER->fields["USERLEVEL"]; 
		$STATUS		= $RSGETUSER->fields["STATUS"]; 
		
		echo "<script>
				$('#txtuserid').val('$ID');
				$('#txtname').val('$NAME');
				$('#txtusername').val('$USERNAME');
				$('#txtpassword').val('$PASSWORD');
				$('#seldep').val('$DEPT');
				$('#sellevel').val('$USERLEVEL');
				$('#selstatus').val('$STATUS');
			  </script>";
	}
	exit();
}
if($action == "CHKUSERNAME")
{
	$USERNAME	=	$_GET["USERNAME"];
	$CNTUSER	=	$global_func->Select_val($Filstar_conn,"WMS_USERS","USERS","USERNAME","USERNAME='{$USERNAME}'");
	if ($CNTUSER != "")
	{
		echo "<script>
				$('#txtinfomsg').text('Username already in use.');
				$('#divinfomsg').dialog('open');
				$('#txtusername').val('');
			  </script>";
	}
	exit();
}
if($action == "GETMODULES")
{
	$USERID		=	$_GET["USERID"];
	$name		=	$global_func->Select_val($Filstar_conn,"WMS_USERS","USERS","NAME","ID='{$USERID}'");
	
	$getModules		=	"SELECT * FROM WMS_USERS.MODULES WHERE MODULE_GROUP = '' ORDER BY ORDERING";
	$RSgetModules	=	$Filstar_conn->Execute($getModules);
	if($RSgetModules == false)
	{
		$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		$aMainMenu	=	array();
		echo "<input type='hidden' id='txtMuserid' name='txtMuserid' value='$USERID'>
			  <table width='100%' border='0'bgcolor='#ffffe9'>
				<tr class='trheader'>
					<td id='tdEMuser'>$name</td>
				</tr>
				<tr>
					<td>";
						echo 	 "<form id='frmmodules'>";
						echo 	 "<div id='menutab' class='shadowed'>";
						echo 		"<ul>";	
						while (!$RSgetModules->EOF) 
						{
							$LINK_NAME	=	$RSgetModules->fields["LINK_NAME"];
							$ID			=	$RSgetModules->fields["ID"];
							$IS_GROUP	=	$RSgetModules->fields["IS_GROUP"];
							echo 		"<li> <a href='#div$ID'>$LINK_NAME</a></li>";	
							$aMainMenu[$ID]["LINK_NAME"]	=	$LINK_NAME;
							$aMainMenu[$ID]["IS_GROUP"]		=	$IS_GROUP;
							$RSgetModules->MoveNext();
						}
						echo 		"</ul>";
						foreach ($aMainMenu as $id=>$val)
						{
							$LINKNAME	=	$val["LINK_NAME"];
							$IS_GROUP	=	$val["IS_GROUP"];
							echo 	 "<div id='div$id'>";	
							echo 		"<ul>";
							echo 			"<li>
												<label for='$id' onclick='toggleD(\"$id\");'>
													<input type='checkbox' id='$id' name='links[]' value='$id'>";
							echo 						"$LINKNAME";
														
							echo 				"</label>";
												if ($IS_GROUP == "Y")
												{
													echo getLinks($id,$LINKNAME,$DATASOURCE,$Filstar_conn);
												}
							echo 			"</li>";
							echo 		"</ul>";
							echo 	 "</div>";	
						}
						echo 	 "</div>";	
						echo 	 "</form>";	
						
						$GETUSERACCESS		=	"SELECT * FROM WMS_USERS.USER_ACCESS WHERE USERID = '{$USERID}'";
						$RSGETUSERACCESS	=	$Filstar_conn->Execute($GETUSERACCESS);
						if($RSGETUSERACCESS == false)
						{
							$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
						}
						else 
						{
							while (!$RSGETUSERACCESS->EOF) 
							{
								$LINK_ID	=	$RSGETUSERACCESS->fields["LINK_ID"];
								echo "<script>
											$('#$LINK_ID').prop('checked',true);
									  </script>";
								$RSGETUSERACCESS->MoveNext();
							}
						}
		echo 		"</td>
				</td>
			 </table>";
		
	}
	exit();
}
if($action == "SAVEMODULES")
{
	$USERID	=	$_GET["USERID"];
	$Filstar_conn->StartTrans();
		$delUserAccess		=	"DELETE FROM WMS_USERS.USER_ACCESS WHERE USERID = '{$USERID}'";
		$RSdelUserAccess	=	$Filstar_conn->Execute($delUserAccess);
		if($RSdelUserAccess == false)
		{
			$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
		}
		if(!empty($_POST['links'])) 
		{
	    	foreach($_POST['links'] as $link)
		    {
		       	$DATENOW	=	date("Y-m-d h:i:s");
		       	$INSERTUSERACCESS	=	"INSERT INTO WMS_USERS.USER_ACCESS(`USERID`, `LINK_ID`, `MODIFIED_BY`, `MODIFIED_DATE`)
		       							 VALUES('{$USERID}','{$link}','{$_SESSION['username']}','{$DATENOW}')"; 
		       	$RSINSERTUSERACCESS	=	$Filstar_conn->Execute($INSERTUSERACCESS);
				if($RSINSERTUSERACCESS == false)
				{
					$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
				}
	   		}
		}
	$Filstar_conn->CompleteTrans();
	echo "<script>
				$('#txtinfomsg').text('Modules access has ben successfuly updated.');
				$('#divinfomsg').dialog('open');
				$('#divEmodules').dialog('close');
		  </script>";
	exit();
}
function getLinks($id,$LINKNAME,$DATASOURCE,$Filstar_conn)
{
	$aID		=	explode(" ",$id);
	$aLength	=	count($aID);
	$newid	=	$aID[$aLength-1];
	$getlink	=	"SELECT * FROM WMS_USERS.MODULES WHERE MODULE_GROUP = '{$newid}'  ORDER BY ORDERING";
	$RSgetlink	=	$Filstar_conn->Execute($getlink);
	if($RSgetlink == false)
	{
		$Filstar_conn->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		$linklist	=	"<ul>";
		while (!$RSgetlink->EOF) 
		{
			$ID				=	$RSgetlink->fields["ID"];
			$LINK_NAME		=	$RSgetlink->fields["LINK_NAME"];
			$MODULE_GROUP	=	$RSgetlink->fields["MODULE_GROUP"];
			$IS_GROUP		=	$RSgetlink->fields["IS_GROUP"];
			if ($IS_GROUP == "Y")
			{
				$toggleD	=	"toggleD(\"$ID\");";
			}
			else
			{
				$toggleD	=	"";
			}
			$linklist		.=	"<li>
									<label for='$ID' onclick='$toggleD toggleG(\"$id\");'>
										<input type='checkbox' id='$ID' name='links[]' value='$ID' class='$id'>";
			$linklist		.=				"$LINK_NAME";
			$linklist		.=		"</label>";
											if ($IS_GROUP == "Y")
											{
			$linklist		.= 					getLinks($id." ".$ID,$LINK_NAME,$DATASOURCE,$Filstar_conn);
											}
			$linklist		.=	"</li>";
			$RSgetlink->MoveNext();
		}
		$linklist	.=	"</ul>";
		return $linklist;
	}
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/userconfig/userconfig.html");
?>