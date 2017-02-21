<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
include($_SERVER['DOCUMENT_ROOT'].'/wms/include/mailer/class.phpmailer.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}

$Filstar_loc	=	ADONewConnection("mysqlt");
	
$dbFilstar_loc	=	$Filstar_loc->Connect('192.168.255.10','root','');
if ($dbFilstar_loc == false) 
{
	echo "<script>alert('Error Occurred no Database Connection!');</script>";
	echo "<script>location = 'index.php'</script>";
}


$Filstar_171	=	ADONewConnection("mysqlt");
$dbFilstar_171	=	$Filstar_171->Connect('192.168.250.171','root','');
if ($dbFilstar_171 == false) 
{
	echo "<script>alert('Error Occurred no Database Connection!');</script>";
	echo "<script>location = 'index.php'</script>";
}
//$_SESSION['THIS_TRXNO']		=	$_GET['THIS_TRXNO'];
$_SESSION['THIS_TRXNO']		="T_INV0220160223064";

$sel_transno	 =	"SELECT TRANSNO,TYPE,POSTEDDATE,REFN0 FROM WMS_NEW.TIANGGE_HDR WHERE TRANSNO ='{$_SESSION['THIS_TRXNO']}' ";
$rssel_transno	=	$Filstar_loc->Execute($sel_transno);
if ($rssel_transno == false)
{
	echo $Filstar_loc->ErrorMsg()."::".__LINE__;
	exit();
}

while (!$rssel_transno->EOF)
{
	$transno	= $rssel_transno->fields['TRANSNO'];
	$posteddate	= $rssel_transno->fields['POSTEDDATE'];
	$refno		= $rssel_transno->fields['REFN0'];
	$sel_dtl	=	"SELECT  * FROM WMS_NEW.TIANGGE_DTL WHERE TRANSNO = '{$transno}' ";
	$rssel_dtl	=	$Filstar_loc->Execute($sel_dtl);
//	echo "$posteddate -- $refno";exit();
	if ($rssel_dtl == false) 
	{
		echo $Filstar_171->ErrorMsg()."::".__LINE__;exit();
	}
$output		=	"PRIMESTOCK; AND; DEFECTIVE ITEMS;;;;;\r";
$output		.=	"TRANSACTION:;".$_SESSION['THIS_TRXNO'].";;;;;DISPOSAL REFNO:;$refno\r";
$output		.=	"TYPE: ; CHOPPING;;;;;POSTED DATE:;$posteddate\r\r";
$output		.=	"LINE NO;ITEM #;DESCRIPTION;TOTAL QTY;SRP;SRP AMOUNT;PRODUCT COST;PROD COST AMOUNT\r\r";
	while (!$rssel_dtl->EOF) 
	{
		$TRANSNO		=	$rssel_dtl->fields['TRANSNO'];
		$SKUNO			=	$rssel_dtl->fields['SKUNO'];
		$ITEMSTATUS		=	$rssel_dtl->fields['ITEMSTATUS'];
		$QTY			=	$rssel_dtl->fields['QTY'];
		$RETAILPRICE	=	$rssel_dtl->fields['RETAILPRICE'];
		$PRODCOST		=	$rssel_dtl->fields['PRODCOST'];
		$GROSSAMOUNT	=	$rssel_dtl->fields['GROSSAMOUNT'];
		$COSTAMOUNT		=	$rssel_dtl->fields['COSTAMOUNT'];
		$BRAND			=	$rssel_dtl->fields['BRAND'];
		$CATEGORY		=	$rssel_dtl->fields['CATEGORY'];
		$SUBCATEGORY	=	$rssel_dtl->fields['SUBCATEGORY'];
		$CLASS			=	$rssel_dtl->fields['CLASS'];
		$desc 			= Sel_val($Filstar_171,"FDC_PMS","ITEMMASTER","ITEM_DESC","ITEMNO='{$SKUNO}'");

		$aData[$SKUNO]['QTY']			+=	$QTY;
		$aData[$SKUNO]['STATUS']		=	$ITEMSTATUS;
		$aData[$SKUNO]['RETAILPRICE']	=	$RETAILPRICE;
		$aData[$SKUNO]['PRODCOST']		+=	$PRODCOST;
		$aData[$SKUNO]['GROSSAMOUNT']	+=	$GROSSAMOUNT;
		$aData[$SKUNO]['COSTAMOUNT']	+=	$COSTAMOUNT;
		
		$rssel_dtl->MoveNext();
	}
	$rssel_transno->MoveNext();	
}
$COUNT		=	1;
$total_qty	=	0;
$total_gross=	0;
$total_cost	= 	0;
foreach ($aData as $key_item=>$val__)
{

	$QTY		=	$val__['QTY'];
	$RETAILPRICE=	$val__['RETAILPRICE'];
	$PRODCOST	=	$val__['PRODCOST'];
	$GROSSAMOUNT=	$val__['GROSSAMOUNT'];
	$COSTAMOUNT	=	$val__['COSTAMOUNT'];
	
	$total_qty	+=	$QTY;
	$total_gross+=	$GROSSAMOUNT;
	$total_cost +=	$COSTAMOUNT;
	
//	echo "$COUNT  --  $SKUNO   --  $desc --  $QTY  --  $RETAILPRICE  --  $total_gross  --  $PRODCOST   --   $total_cost<br>";
	$output	.= "$COUNT;$SKUNO;$desc;$QTY;$RETAILPRICE;$total_gross;$PRODCOST;$total_cost\r";
	$COUNT++;
}
//	header("Content-Disposition: attachment; filename=PRIMESTOCKDEFECTIVEITEMS'{$_SESSION['THIS_TRXNO']}'.csv");
//	header("Content-Location: $_SERVER[REQUEST_URI]");
//	header("Content-Type: text/plain");
//	header("Expires: 0");
	$filename	= "PRIMESTOCKDEFECTIVEITEMS-{$_SESSION['THIS_TRXNO']}.csv";
	awrite("attachment/$filename",$output);
//	echo $output;
	mailer("attachment/$filename");
	
function mailer($attach)
{
	$date 		= date("Y-m-d");
	$now		= date("-Ymd");	
	$sDataRS	="PRIMESTOCK AND DEFECTIVE ITEMS <br><br><br> This is a system generated message.<br>Please do not reply. DISCLAIMER";
	$mail = new PHPMailer();
	$mail-> IsSMTP();
	$mail->Host = "192.168.250.252";
	$mail->SMTPAuth = false;
	$mail->Username = "";
	$mail->Password = "";
	$mail->From = "filstartasksupport@filstar.com.ph";
	$mail->FromName = "FDC PURCHASING DEPT";
	$mail->AddAddress("uahornachos@filstar.com.ph");
//	$mail->AddAddress("rpmilitante@filstar.com.ph");
	$mail->AddReplyTo("filstartasksupport@filstar.com.ph");
	$mail->IsHTML(true);
	$mail->AddAttachment($attach);
	$mail->Subject = "PRIMESTOCK AND DEFECTIVE ITEMS '{$_SESSION['THIS_TRXNO']}'";
	$mail->Body = $sDataRS;
//	$mail->Send();
	if(!$mail->Send())
	{
		$errlogs = '/var/www/html/wms/modules/tiangge/logs/err/err'.$now.'.log';
		$err  =	"=====================".chr(10);
		$err .=	date('Y-m-d H:i:s', time()).chr(10);
		$err .=	'SENDING EMAIL FAILED'.chr(10);
		write($errlogs,$err);
		return false;	
	}
	else{
		$succlogs = '/var/www/html/wms/modules/tiangge/logs/succ/succ'.$now.'.log';
		$success  =	"=====================".chr(10);
		$success .=	date('Y-m-d H:i:s', time()).chr(10);
		$success .=	'SENDING EMAIL SUCCESSFUL'.chr(10);
		write($succlogs,$success);
		return true;
	}
}	
	
function awrite($filename='',$msg='')
{		
	
	$fp = fopen($filename, "a+");
	fwrite($fp, $msg);
	fclose($fp);
}

function this_error($val)
{
	$err	=	LOGSLINER;
	$err	.=	LOGSDATE;
	$err	.=	$val.chr(10);
	$err	.=	LOGSLINER;
	return $err;
}	
	
function Sel_val($conn,$database,$tbl,$fld,$condition)
{
	$sel	=	"SELECT $fld FROM ".$database.".$tbl WHERE $condition";
	$rssel	=	$conn->Execute($sel);
		if($rssel == false)
		{
			echo $conn->ErrorMsg()."::".__LINE__;
			exit();
		}
	$retval	=	$rssel->fields["$fld"];
	return $retval;
}
?>