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

if($action == "Q_SEARCHCUSTS")
{
	$CUSTNO			=	$_GET["CUSTNO"];
	$CUSTNAME		=	$_GET["CUSTNAME"];
	

	if($CUSTNO != "")
	{
		$CUSTNO_Q	=	" AND CODE like '%{$CUSTNO}%'";
	}
	if($CUSTNAME != "")
	{
		$CUSTNAME_Q	=	" AND DESCRIPTION like '%{$CUSTNAME}%'";
	}
	$sel	 =	"SELECT CODE,DESCRIPTION FROM WMS_LOOKUP.IB_CUSTOMERS WHERE 1 $CUSTNO_Q $CUSTNAME_Q
				 LIMIT 20";
	$rssel	=	$conn_255_10->Execute($sel);
	if ($rssel == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"INTERNAL BARCODE CUSTOMER MAINTENANCE","Q_SEARCHCUSTS");
		$DATASOURCE->displayError();
	}
	$cnt	=	$rssel->RecordCount();
	if ($cnt > 0) 
	{
		echo "<select id='selcustS' class = 'C_dropdown divselS' multiple>";
		while (!$rssel->EOF) 
		{
			$q_custno	=	$rssel->fields['CODE'];
			$Q_custname	=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['DESCRIPTION']);
			$cValue		=	$q_custno."|".$Q_custname;
			echo "<option value=\"$cValue\">$q_custno-$Q_custname</option>";
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
if($action == "Q_SEARCHCUST")
{
	$CUSTNO			=	$_GET["CUSTNO"];
	$CUSTNAME		=	$_GET["CUSTNAME"];
	
	if($CUSTNO != "")
	{
		$CUSTNO_Q	=	" AND CustNo like '%{$CUSTNO}%'";
	}
	if($CUSTNAME != "")
	{
		$CUSTNAME_Q	=	" AND CustName like '%{$CUSTNAME}%'";
	}
	$sel	 =	"SELECT CustNo,CustName FROM  FDCRMSlive.custmast WHERE 1 $CUSTNO_Q $CUSTNAME_Q
				 LIMIT 20";
	$rssel	=	$conn_255_10->Execute($sel);
	if ($rssel == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$sel,$_SESSION['username'],"INTERNAL BARCODE CUSTOMER MAINTENANCE","Q_SEARCHCUST");
		$DATASOURCE->displayError();
	}
	$cnt	=	$rssel->RecordCount();
	if ($cnt > 0) 
	{
		echo "<select id='selcust' class = 'C_dropdown' multiple>";
		while (!$rssel->EOF) 
		{
			$q_custno	=	$rssel->fields['CustNo'];
			$Q_custname	=	preg_replace('/[^A-Za-z0-9. \-]/', '', $rssel->fields['CustName']);
			$cValue		=	$q_custno."|".$Q_custname;
			echo "<option value=\"$cValue\">$q_custno-$Q_custname</option>";
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
if($action == "SAVECUSTS")
{
	$txtcustno		=	$_POST["txtcustno"];
	$txtcustname	=	addslashes($_POST["txtcustname"]);
	$selstatus		=	$_POST["selstatus"];
	$MODE			=	$_GET["MODE"];
	if($MODE == "Save")
	{
		$custfound		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","IB_CUSTOMERS","CODE","CODE = '$txtcustno'");
		if($custfound == "")
		{
			$SAVECUST	=	"INSERT INTO WMS_LOOKUP.IB_CUSTOMERS(`CODE`, `DESCRIPTION`, `STATUS`,`ADDEDBY`, `ADDEDDT`)
							 VALUES('$txtcustno','$txtcustname','$selstatus','$user','$today')";
			$endmsg		=	"Customer has been saved successfully.";
		}
		else 
		{
			echo "<script>
						MessageType.infoMsg('Customer already exists.');
			  	  </script>";
		}
	}
	else 
	{
		$SAVECUST	=	"UPDATE WMS_LOOKUP.IB_CUSTOMERS SET `STATUS` = '$selstatus',`EDITEDBY` = '$user', `EDITEDDT` = '$today'
						 WHERE `CODE` = '$txtcustno'";
		$endmsg		=	"Customer has been updated successfully.";
	}
	$RSSAVECUST	=	$conn_255_10->Execute($SAVECUST);
	if ($RSSAVECUST == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$SAVECUST,$_SESSION['username'],"INTERNAL BARCODE CUSTOMER MAINTENANCE","SAVECUSTS");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<script>
					MessageType.successMsg('$endmsg');
					$('#divcustomer').dialog('close');
					BIcustomer_funcs.cancelCreate();
					$('#btnsearch').trigger('click');
			  </script>";
	}
	
	exit();
}
if($action == "GETCUSTS")
{
	$txtcustno		=	$_POST["txtcustnoS"];
	$selstatus		=	$_POST["selstatusS"];
	if($txtcustno != "")
	{
		$cust_Q		=	" AND CODE  = '$txtcustno'";
	}
	if($selstatus != "")
	{
		$status_Q	=	" AND STATUS = '$selstatus'";
	}
	$GETCUST	=	"SELECT * FROM WMS_LOOKUP.IB_CUSTOMERS WHERE 1 $cust_Q $status_Q";
	$RSGETCUST	=	$conn_255_10->Execute($GETCUST);
	if ($RSGETCUST == false)
	{
		$errmsg	=	($conn_255_10->ErrorMsg()."::".__LINE__); 
		$DATASOURCE->logError("wms",$errmsg,$GETCUST,$_SESSION['username'],"INTERNAL BARCODE CUSTOMER MAINTENANCE","GETCUSTS");
		$DATASOURCE->displayError();
	}
	else 
	{
		echo "<table class='tblresult tablesorter' border='1'>
					<thead>
						<tr class='trheader'>
							<th>No.</th>
							<th>Customer</th>
							<th>Status</th>
							<th>Added By</th>
							<th>Added Date</th>
							<th>Edited By</th>
							<th>Edited Date</th>
							<th>Action</th>
						</tr>
					<thead>
					<tbody>";
		$cnt = 1;
		if($RSGETCUST->RecordCount() > 0)
		{
			while (!$RSGETCUST->EOF) {
				$ID 			= $RSGETCUST->fields["ID"]; 
				$CODE 			= $RSGETCUST->fields["CODE"]; 
				$DESCRIPTION 	= $RSGETCUST->fields["DESCRIPTION"]; 
				$STATUS 		= $RSGETCUST->fields["STATUS"]; 
				$ADDEDBY 		= $RSGETCUST->fields["ADDEDBY"]; 
				$ADDEDDT 		= $RSGETCUST->fields["ADDEDDT"]; 
				$EDITEDBY 		= $RSGETCUST->fields["EDITEDBY"]; 
				$EDITEDDT 		= $RSGETCUST->fields["EDITEDDT"]; 
				$btnedit	=	"<img src='/wms/images/images/action_icon/new/compose.png' class='smallbtns editbtn' title='Edit Customer: $DESCRIPTION' data-custno='$CODE'>";
				echo "<tr class='trbody'>
						<td align='center'>$cnt</td>
						<td>$CODE-$DESCRIPTION</td>
						<td align='center'>$STATUS</td>
						<td align='center'>$ADDEDBY</td>
						<td align='center'>$ADDEDDT</td>
						<td align='center'>$EDITEDBY</td>
						<td align='center'>$EDITEDDT</td>
						<td align='center'>
							$btnedit
						</td>
				   </tr>";
				$cnt++;
				$RSGETCUST->MoveNext();
			}
		}
		else 
		{
			echo "<tr class='trbody'>
						<td align='center' style='color:red;' colspan='9'>Nothing to display.</td>
				  </tr>";
		}
		echo " </tbody>
			</table>
			</form>";
	}
	exit();
}
if($action == "EDITCUST")
{
	$CUSTNO		=	$_GET["CUSTNO"];
	$custname	=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","IB_CUSTOMERS","DESCRIPTION","CODE = '$CUSTNO'");
	$status		=	$DATASOURCE->selval($conn_255_10,"WMS_LOOKUP","IB_CUSTOMERS","STATUS","CODE = '$CUSTNO'");
	echo "<script>
				$('#txtcustno').val('$CUSTNO');
				$('#txtcustname').val('$custname');
				$('#selstatus').val('$status');
		  </script>";
	exit();
}
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/maintenance/IB_customer/IB_customer.html");
include($_SERVER['DOCUMENT_ROOT']."/wms/modules/returns/maintenance/IB_customer/IB_customerUI.php");
?>