<?php
	session_start();
	include($_SERVER['DOCUMENT_ROOT']."/public_php/config.php");
	
	$CREATE	=	"CREATE TABLE WMS_NEW.Employee(
				    ->     id            int,
				    ->     first_name    VARCHAR(15),
				    ->     last_name     VARCHAR(15),
				    ->     start_date    DATE,
				    ->     end_date      DATE,
				    ->     salary        FLOAT(8,2),
				    ->     city          VARCHAR(10),
				    ->     description   VARCHAR(15)
				    -> );";
	$RSCREATE	=	$conn_255_10->Execute($CREATE);
	if($RSCREATE == false)
	{
		$conn_255_10->ErrorMsg()."::".__LINE__; exit();
	}
	else 
	{
		echo "SCANDATA_DTL{$_SESSION['username_id']}";
	}
?>