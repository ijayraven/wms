<?php
include("../../fpdf/fpdf.php");
include("../../adodb/adodb.inc.php");
include("../../include/mailer/class.phpmailer.php");

	error_reporting(E_ERROR);
	set_time_limit(0);
	$conn	=	ADONewConnection('mysqlt');
	$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
	$conn2	=	ADONewConnection('mysqlt');
	$dbconn2	=	$conn2->Connect('192.168.250.10','root','','FDCRMSlive');
	if ($dbconn == false) 
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}

class ASSEMBLY extends FPDF 
{
	
	function Header()
	{
		$date = date("Y-m-d");

		$this->SetFont('Arial','B',15);
		$this->Cell(0,6,"FILSTAR DISTRIBUTORS CORPORATION",0,1,'C');
		$this->SetFont('Arial','B',13);
		$this->Cell(0,5,"ITEM ASSEMBLY",0,1,'C');
		
		$conn	=	ADONewConnection('mysqlt');
		$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
		
		$hidPrintCode	=	$_GET['hidPrintCode'];	
		$sel_items		= "SELECT * FROM FDC_PMS.ASSEMBLY WHERE TRANSNO='{$hidPrintCode}'";
		$rssel_items	= $conn->Execute($sel_items);
		foreach ($rssel_items as $key => $value)
		{
			$transno	=	$value['TRANSNO'];
			$itemno		=	$value['ITEMNO'];
			$image		=	$value['IMAGE'];
			$image2		=	substr($image,45,18);
			$member		=	$value['MEMBER'];
			$ins1		=	$value['INSTRUCTION1'];
			$ins2		=	$value['INSTRUCTION2'];
			$ins3		=	$value['INSTRUCTION3'];
			$addedby	=	$value['ADDED_BY'];
			$addeddate	=	$value['ADDED_DATE'];
			$prindate	=	$value['PRINTED_DATE'];
			$printby	=	$value['PRINTED_BY'];
		}
		$picH	=	40;
		$picW	=	35;
		$this->SetFont('Arial','B',10);
		$this->SetXY(20,30);$this->Cell(40,5,"TRANSACTION NO:",0,0,"R");
		$this->SetXY(62,30);$this->Cell(21,5,$transno,0,0,"L");
//		$this->Image('/var/www/html/wms/modules/itemassembly/PMS_IMAGE/'.$image2,126,190,$picW,$picH);
		

//		$this->Image($image,100,30,55,55);
		$this->SetXY(20,35);$this->Cell(40,5,"TRANSACTION DATE:",0,0,"R");
		$this->SetXY(62,35);$this->Cell(21,5,substr($addeddate,0,10),0,0,"L");
		$this->SetXY(20,40);$this->Cell(40,5,"CREATED BY:",0,0,"R");
		$this->SetXY(62,40);$this->Cell(21,5,$addedby,0,0,"L");
		$this->SetXY(20,45);$this->Cell(40,5,"MEMBER/S:",0,0,"R");
		$this->SetXY(62,45);$this->Cell(21,5,$member,0,0,"L");
		
		$this->SetFont('Arial','B',10);
		$this->SetXY(30,55);$this->Cell(15,5,"Line #",1,0,'C');	
		$this->SetXY(45,55);$this->Cell(20,5,"SKU No.",1,0,'C');		
		$this->SetXY(65,55);$this->Cell(90,5,"Description",1,0,'C');
		$this->SetXY(155,55);$this->Cell(20,5,"Quantity",1,0,'C');
											
	}
	function Body()
	{
		$conn	=	ADONewConnection('mysqlt');
		$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
		
		$hidPrintCode	=	$_GET['hidPrintCode'];	
		$sel_items		= "SELECT * FROM FDC_PMS.ASSEMBLY WHERE TRANSNO='{$hidPrintCode}'";
		$rssel_items	= $conn->Execute($sel_items);
		foreach ($rssel_items as $key => $value)
		{
			$transno	=	$value['TRANSNO'];
			$itemno		=	$value['ITEMNO'];
			$image		=	$value['IMAGE'];
			$image2		=	substr($image,45,18);
			$member		=	$value['MEMBER'];
			$ins1		=	$value['INSTRUCTION1'];
			$ins2		=	$value['INSTRUCTION2'];
			$ins3		=	$value['INSTRUCTION3'];
			$addedby	=	$value['ADDED_BY'];
			$addeddate	=	$value['ADDED_DATE'];
			$prindate	=	$value['PRINTED_DATE'];
			$printby	=	$value['PRINTED_BY'];
		}
		
		
				
		$sel_itemdtl	= "SELECT * FROM FDC_PMS.ASSEMBLY_DTL WHERE TRANSNO='{$transno}'";
		$rssel_itemdtl	= $conn->Execute($sel_itemdtl);
		$nYAxis	=55;
		$ctr = 0;
		$counter=1;
		foreach ($rssel_itemdtl as $key => $dataVal)
		{
			$itemno		=	$dataVal['ITEMNO'];
			$origitemno	=	$dataVal['ORIG_ITEMNO'];
			$desc		=	$dataVal['ORIG_DESC'];
			$srp		=	$dataVal['ORIG_SELLPRICE'];
			$qty		=	$dataVal['QUANTITY'];

			$this->SetFont('Arial','',10);
			$nYAxis += 5;
			$this->SetXY(30,$nYAxis);$this->Cell(15,5,"$counter",1,1,'C');
			$this->SetXY(45,$nYAxis);$this->Cell(20,5,"$origitemno",1,0,'C');	
			$this->SetFont('Arial','',8);	
			$this->SetXY(65,$nYAxis);$this->Cell(90,5,"$desc",1,0,'C');
			$this->SetFont('Arial','',10);
			$this->SetXY(155,$nYAxis);$this->Cell(20,5,"$qty",1,0,'C');
			$counter++;		
				if($nYAxis >= 250)
			{
				$this->AddPage();
				$this->Footer();
				$nYAxis = 55;
			}	
		}
		$ins1	=	str_concat($ins1,21);
		$ins2	=	str_concat($ins2,21);
		$ins3	=	str_concat($ins3,21);
		
		$this->SetFont('Arial','B',12);
		$this->SetXY(20,$nYAxis+=15);$this->Cell(35,5,"INSTRUCTIONS:",0,0,'C');
		$this->Image('/var/www/html/wms/modules/itemassembly/PMS_IMAGE/'.$image2,110,$nYAxis-5,60);
//		$this->SetFont('Arial','',10);
//		$this->SetXY(20,$nYAxis+=5);$this->Cell(50,5,"1.     $ins1",0,0,'L');
		$this->SetFont('Arial','B',12);
		$this->SetXY(20,$nYAxis+=5);$this->Cell(50,5,"1.",0,0,'L');
		$this->SetFont('Arial','',10);
		foreach ($ins1 as $key => $inss1)
		{
			$this->SetXY(25,$nYAxis+=4);$this->Cell(50,5,"$inss1",0,0,'L');
		}
		$this->SetFont('Arial','B',12);
		$this->SetXY(20,$nYAxis+=5);$this->Cell(50,5,"2.",0,0,'L');
		$this->SetFont('Arial','',10);
		foreach ($ins2 as $key => $inss2)
		{
			$this->SetXY(25,$nYAxis+=4);$this->Cell(50,5,"$inss2",0,0,'L');
		}
		$this->SetFont('Arial','B',12);
		$this->SetXY(20,$nYAxis+=5);$this->Cell(50,5,"3.",0,0,'L');
		$this->SetFont('Arial','',10);
		foreach ($ins3 as $key => $inss3)
		{
			$this->SetXY(25,$nYAxis+=4);$this->Cell(50,5,"$inss3",0,0,'L');
		}
//		$this->SetXY(20,$nYAxis+=5);$this->Cell(50,5,"4.",0,0,'L');
//		$this->SetXY(20,$nYAxis+=5);$this->Cell(50,5,"5.",0,0,'L');
			
	}
	
	function Footer()
	{
		$conn	=	ADONewConnection('mysqlt');
		$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
		
		$hidPrintCode	=	$_GET['hidPrintCode'];	
		$sel_itemsf		= "SELECT * FROM FDC_PMS.ASSEMBLY WHERE TRANSNO='{$hidPrintCode}'";
		$rssel_itemsf	= $conn->Execute($sel_itemsf);

		foreach ($rssel_itemsf as $key => $valuef)
		{
			$prindate	=	$valuef['PRINTED_DATE'];
			$printby	=	$valuef['PRINTED_BY'];
		}
		
		$this->SetFont('Times','I',9);
		$this->SetY(260);$this->Cell(0,5,"Printed Date:    ".substr($prindate,0,10)."",0,0,'L');
		$this->SetY(265);$this->Cell(0,5,"Printed By:       $printby",0,0,'L');
	}
	
}


function sel_item($conn,$database,$tbl,$fld,$condition)
	{
		$sel	=	"SELECT $fld FROM ".$database.".$tbl WHERE $condition";
		$rssel	=	$conn->Execute($sel);
		if ($rssel == false) 
		{
			die($conn->ErrorMsg());
		}
		$retval	=	$rssel->fields[$fld];
		return $retval;
	}

function str_concat($string,$length=25)
	{
		$count = 0;
		$newstring = array();
		if(strlen($string) <= $length){
			$newstring[] = $string;
		}
		else {
			$array_string = array();
			$array_string = explode(" ",$string);
			$nstring = "";
			foreach ($array_string as $value)
			{
				$count += strlen($value) + 1;

				if($count < $length)
				{
					$nstring .= $nstring == "" ? $value : " ".$value;
				}
				elseif ($count == $length)
				{
					$nstring .= $nstring == "" ? $value : " ".$value;
					$newstring[] = $nstring;
					$nstring = "";
					$count = 0;
				}
				elseif ($count > $length)
				{
					$newstring[] = $nstring;
					$nstring = $value;
					$count = strlen($value);
				}
			}
			if($nstring != "")
			{
				$newstring[] = $nstring;
			}
		}
		return $newstring;
	}
	

		

$pdf= new ASSEMBLY('P','mm','A4');
$pdf->Open();
$pdf->SetAutoPageBreak('auto',15);
$pdf->SetFont('Arial','B',12);
$pdf->AddPage();
$pdf->Body();
$pdf->Footer();

//EMAIL
$txtTransNo	=	$_GET['hidPrintCode'];	
$date = date("Y-m-d");
$assembly  = '/var/www/html/wms/modules/itemassembly/attachment/itemassembly'.$txtTransNo.'.pdf';
$pdf->Output($assembly,"I");
$printed 		= "SELECT TRANSNO,PRINTED_BY,PRINTED_DATE FROM ASSEMBLY WHERE TRANSNO ='{$txtTransNo}'";
$rs_printed		= $conn->Execute($printed);
if ($rs_printed == false) 
{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
}
else 
{
	foreach ($rs_printed as $key => $print)
	{
		$printdate	=	$print['PRINTED_DATE'];
		$printby	=	$print['PRINTED_BY'];
		
		$date = date("Y-m-d",strtotime("-1 day"));
		$now	= date("-Ymd");	
		$sDataRS="Item Assembly $txtTransNo has already been printed by ".ucwords($printby)." on ". substr($printdate,0,10)."  <br><br><br> This is a system generated message.<br>Please do not reply. DISCLAIMER";
		
		$mail = new PHPMailer();
		$mail-> IsSMTP();
		$mail->Host = "192.168.250.252";
		$mail->SMTPAuth = false;
		$mail->Username = "";
		$mail->Password = "";
		$mail->From = "filstartasksupport@filstar.com.ph";
		$mail->FromName = "FDC LMC DEPT";
		$mail->AddAddress("uahornachos@filstar.com.ph");	
		$mail->AddReplyTo("filstartasksupport@filstar.com.ph");
		$mail->IsHTML(true);
		$mail->AddAttachment($assembly);
		$mail->Subject = "Item Assembly $txtTransNo";
		$mail->Body = $sDataRS;
//		$mail->Send();
		if(!$mail->Send())
		{
			$errlogs = '/var/www/html/wms/modules/itemassembly/logs/err/err'.$now.'.log';
			$err  =	"=====================".chr(10);
			$err .=	date('Y-m-d H:i:s', time()).chr(10);
			$err .=	'SENDING EMAIL FAILED'.chr(10);
			write($errlogs,$err);
			return false;	
		}
		else
		{
			$succlogs = '/var/www/html/wms/modules/itemassembly/logs/succ/succ'.$now.'.log';
			$success  =	"=====================".chr(10);
			$success .=	date('Y-m-d H:i:s', time()).chr(10);
			$success .=	'SENDING EMAIL SUCCESSFUL'.chr(10);
			write($succlogs,$success);
			return true;
		}
	}
	
	
}

function write($filename='',$msg='')
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

?>
