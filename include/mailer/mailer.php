<?
	
	include("class.phpmailer.php");
	$sDataRS="<table border='1'>
		<tr>
		<td>111111111</td>
		<td>2222222222222</td>
		</tr>
		<tr>
		<td>11111111111111111</td>
		<td>22222222</td>
		</tr>		
		</table>";
	$mail = new PHPMailer();
	$mail-> IsSMTP();
	$mail->Host = "mail.nationalbookstore.com.ph";
	//$mail->Host = "192.168.254.12";
	$mail->SMTPAuth = false;
	$mail->Username = "";
	$mail->Password = "";
	$mail->From = "cordovaa@nationalbookstore.com.ph";
	$mail->FromName = "mailer";
	$mail->AddAddress("cordovaaa@nationalbookstore.com.ph");	
	//$mail->AddAddress("alfren.cordova@yahoo.com");
	$mail->AddReplyTo("cordovaaa@nationalbookstore.com.ph");
	$mail->IsHTML(true);
	$mail->AddAttachment('/home/alfren/Pictures/dec.gif');
	$mail->Subject = "This is trial e-mail message";
	$mail->Body = $sDataRS;
	if(!$mail->Send())
	{
		$Confirm = "Error in sending! Message has not been sent";
	}
	else
	{
		$Confirm = "Message has been sent";
	}
		
?>
