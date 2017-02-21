<?php
session_start();
//include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");
if (empty($_SESSION['username'])) 
{
	echo "<script>
				MessageType.sessexpMsg('wms');
		  </script>";
	exit();
}
$action	=	$_GET['action'];
$user	=	$_SESSION['username'];
$today	=	date("Y-m-d H:i:s A");
if($action == "CHKCODE")
{
	$txtcode	=	$_GET["CODE"];
	$CODE		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","CODE","CODE = '$txtcode'");
	if($CODE != "")
	{
		echo "<script>MessageType.infoMsg('Pieceworker code already exists.');$('#txtcode').val('');</script>";
	}
	exit();
}
if($action == "SAVEDATA")
{
	$MODE		=	$_GET["MODE"];
	$ID			=	$_POST["hdnid"];
	$txtcode	=	$_POST["txtcode"];
	$txtdesc	=	$_POST["txtdesc"];
	$txtzipcode	=	$_POST["txtzipcode"];
	$txtstreet	=	$_POST["txtstreet"];
	$txtbrgy	=	$_POST["txtbrgy"];
	$txtcity	=	$_POST["txtcity"];
	$txtprovince=	$_POST["txtprovince"];
	if($MODE == "Save")
	{
		$SAVEDATA	=	"INSERT INTO WMS_LOOKUP.PIECEWORKER(`CODE`, `DESCRIPTION`,`NOSTREET`, `ZIP`, `BRGY`, `CITY`, `PROVINCE`, `ADDDEDBY`, `ADDEDDATE`)
						 VALUES('$txtcode','$txtdesc','$txtstreet','$txtzipcode','$txtbrgy','$txtcity','$txtprovince','$user','$today')";
		$endmsg		=	"saved";
	}
	else 
	{
		$SAVEDATA	=	"UPDATE WMS_LOOKUP.PIECEWORKER SET `CODE`='$txtcode', `DESCRIPTION`='$txtdesc',`NOSTREET`='$txtstreet', `ZIP`='$txtzipcode', 
						`BRGY`='$txtbrgy', `CITY`='$txtcity', `PROVINCE`='$txtprovince', `EDITBY`='$user', `EDITDATE`='$today'
						 WHERE RECID = '$ID'";
		$endmsg		=	"updated";
	}
	$RSSAVEDATA	=	$conn_255_10->Execute($SAVEDATA);
	if($RSSAVEDATA ==  false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$SAVEDATA,$user,"PIECEWORKER MAINTENANCE","SAVEDATA");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<script>MessageType.successMsg('Pieceworker $txtcode-$txtdesc has been successfully $endmsg.');$('#btnsearch').trigger('click');P_functions.clearFields();</script>";
	}
	exit();
}
if($action == "GETLIST")
{
	$txtcodeS	=	$_POST["txtcodeS"];
	$txtdescS	=	$_POST["txtdescS"];
	$selstatusS	=	$_POST["selstatusS"];
	if($txtcodeS != "")
	{
		$txtcodeS_Q	=	" AND CODE LIKE '%$txtcodeS%'";
	}
	if($txtdescS != "")
	{
		$txtdescS_Q	=	" AND DESCRIPTION LIKE '%$txtdescS%'";
	}
	if($selstatusS != "")
	{
		$selstatusS_Q	=	" AND STATUS  = '$selstatusS'";
	}
	$GETLIST	=	"SELECT * FROM WMS_LOOKUP.PIECEWORKER
					 WHERE 1 $txtcodeS_Q $txtdescS_Q $selstatusS_Q";
	$RSGETLIST	=	$conn_255_10->Execute($GETLIST);
	if($RSGETLIST==  false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETLIST,$user,"PIECEWORKER MAINTENANCE","GETLIST");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<table class='tblresult tablesorter' border='1'>
					<thead>
						<tr class='trheader'>
							<th>No.</th>
							<th>Code</th>
							<th>Description</th>
							<th>Status</th>
							<th>Saved By</th>
							<th>Saved Date</th>
							<th>Updated By</th>
							<th>UpdatedDate</th>
							<th>Actions</th>
						</tr>
					<thead>
					<tbody>";
		if($RSGETLIST->RecordCount() > 0)
		{
			$cnt	=	1;
			while (!$RSGETLIST->EOF)
			{
				$RECID 			= $RSGETLIST->fields["RECID"]; 
				$CODE 			= $RSGETLIST->fields["CODE"]; 
				$DESCRIPTION 	= $RSGETLIST->fields["DESCRIPTION"]; 
				$STATUS 		= $RSGETLIST->fields["STATUS"]; 
				$ADDDEDBY 		= $RSGETLIST->fields["ADDDEDBY"]; 
				$ADDEDDATE 		= $RSGETLIST->fields["ADDEDDATE"]; 
				$EDITBY 		= $RSGETLIST->fields["EDITBY"]; 
				$EDITDATE 		= $RSGETLIST->fields["EDITDATE"]; 
				$btnedit	=	"<img src='/wms/images/images/action_icon/new/compose.png' class='smallbtns editbtn tooltips' title='Edit: $CODE-$DESCRIPTION' data-id='$RECID'>";
				$btnact		=	"<img src='/wms/images/images/action_icon/new/check.png' class='smallbtns activatebtn tooltips' title='Activate: $CODE-$DESCRIPTION' data-id='$RECID'>";
				$btndeact	=	"<img src='/wms/images/images/action_icon/new/stop.png' class='smallbtns deactivatebtn tooltips' title='Deactivate: $CODE-$DESCRIPTION' data-id='$RECID'>";
				echo "<tr class='trbody'>
		 				<td align='center'>$cnt</td>
		 				<td align='center'>$CODE</td>
		 				<td>$DESCRIPTION</td>
		 				<td align='center'>$STATUS</td>
		 				<td>$ADDDEDBY</td>
		 				<td align='center'>$ADDEDDATE</td>
		 				<td>$EDITBY</td>
		 				<td align='center'>$EDITDATE</td>
		 				<td align='center'>$btnedit $btnact $btndeact</td>
		 			  </tr>";
				$cnt++;
				$RSGETLIST->MoveNext();
			}
		}
		else 
		{
			echo "<tr class='trbody centered fnt-red'>
	 				<td colspan='11'>Nothing to display.</td>
	 			  </tr>";
		}
		echo "</tbody>
			</table>";
	}
	exit();
}
if($action == "EDIT")
{
	$ID			=	$_GET["ID"];
	$CODE		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","CODE","RECID = '$ID'");
	$DESC		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","DESCRIPTION","RECID = '$ID'");
	$STATUS		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","STATUS","RECID = '$ID'");
	$NOSTREET	=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","NOSTREET","RECID = '$ID'");
	$ZIP		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","ZIP","RECID = '$ID'");
	$BRGY		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","BRGY","RECID = '$ID'");
	$CITY		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","CITY","RECID = '$ID'");
	$PROVINCE	=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","PROVINCE","RECID = '$ID'");
	
	echo "<script>
			$('#hdnid').val('$ID');
			$('#txtcode').val('$CODE');
			$('#txtdesc').val('$DESC');
			$('#selstatus').val('$STATUS');
			$('#txtstreet').val('$NOSTREET');
			$('#txtzipcode').val('$ZIP');
			$('#txtbrgy').val('$BRGY');
			$('#txtcity').val('$CITY');
			$('#txtprovince').val('$PROVINCE');
		</script>";
	exit();
}
if($action == "CREATECODE")
{
	$ID			=	$_GET["ID"];
	$DESC		=	ucwords(strtolower($_GET["DESC"]));
	$words 		= 	explode(" ", $DESC);
	$acronym	= 	"";
	$year		=	date("Y");
	foreach ($words as $w) 
	{
	  	$acronym .= $w[0];
	}
	$cntr	=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","MAX(RECID)","1");
	if($ID == "")
	{
		$cntr++;
		$cntr	=	str_pad($cntr,2,"0",STR_PAD_LEFT);
		$CODE	=	"$year$cntr-$acronym";
	}
	else 
	{
		$CODE	=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","CODE","RECID = '$ID'");
		$CODE	=	substr($CODE,0,6);
		$CODE	=	"$CODE-$acronym";
	}
	$CODE_E		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","PIECEWORKER","CODE","CODE = '$CODE'");
	if($CODE_E != "")
	{
		echo "<script>MessageType.infoMsg('Pieceworker code already exists.');$('#txtcode').val('');</script>";
	}
	else 
	{
		echo $CODE;
	}
	exit();
}
if($action == "ACTIVATEPCW")
{
	$ID	=	$_GET["ID"];
	$ACTIVATE	=	"UPDATE WMS_LOOKUP.PIECEWORKER SET STATUS = 'ACTIVE',`EDITBY`='{$_SESSION['username']}', `EDITDATE`= NOW()
					 WHERE RECID = '$ID'";
	$RSACTIVATE	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$ACTIVATE,$_SESSION['username'],"PIECEWORKER MAINTENANCE","ACTIVATEPCW");
	if($RSACTIVATE)
	{
		echo "<script>
				MessageType.infoMsg('Pieceworker has been successfully activated.')
				$('#btnsearch').trigger('click');
			 </script>";
	}
	exit();
}
if($action == "DEACTIVATEPCW")
{
	$ID	=	$_GET["ID"];
	$DEACTIVATE	=		"UPDATE WMS_LOOKUP.PIECEWORKER SET STATUS = 'INACTIVE',`DELBY`='{$_SESSION['username']}', `DELDATE`=NOW()
						 WHERE RECID = '$ID'";
	$RSDEACTIVATE	=	$DATASOURCE->execQUERY("wms",$conn_255_10,$DEACTIVATE,$_SESSION['username'],"PIECEWORKER MAINTENANCE","DEACTIVATEPCW");
	if($RSDEACTIVATE)
	{
		echo "<script>
				MessageType.infoMsg('Pieceworker has been successfully deactivated.')
				$('#btnsearch').trigger('click');
			 </script>";
	}
	exit();
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/maintenance/pieceworker/pieceworker.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/maintenance/pieceworker/pieceworkerUI.php");
?>