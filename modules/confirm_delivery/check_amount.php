<?php
session_start();
error_reporting(E_ERROR);
include('/var/www/html/wms/includes/config.php');			// configuration
include('/var/www/html/wms/adodb/adodb.inc.php');			// adodb
include('/var/www/html/wms/includes/function.inc.php');	// global functions
######################################################################################################################################

	$Filstar_conn	=	ADONewConnection("mysqlt");
		
	$dbFilstar		=	$Filstar_conn->Connect(WMSSERVER,WMSUSER,WMSPASS,'FDCRMSlive');
	if ($dbFilstar == false) 
	{
		echo "<script>alert('Error Occurred no Database Connection!');</script>";
		echo "<script>location = 'index.php'</script>";
	}
	
	$Filstar_pms	=	ADONewConnection("mysql");
	$dbFilstar_pms	=	$Filstar_pms->Connect('192.168.250.171','root','','');
	if ($dbFilstar_pms == false) 
	{
		echo "<script>alert('Error Occurred no Database Connection!');</script>";
		echo "<script>location = 'index.php'</script>";
	}
	
	$global_func	=	new __Global_Func();

$Filstar_conn->StartTrans();	
	
$amount		=	$argv[1];


echo AccntRound($amount,2);

function AccntRound($num,$round)
	{
		$num_parts   = explode(".",$num);
	    $dec         = $num_parts[1];
	    $zeroval     = substr($dec,0,1);
	    $plusvals    = "0.".str_pad(1,$round,0,STR_PAD_LEFT);
		$firstdrop   = substr($dec,$round,1);
	    $lastkept    = substr($dec,$round-1,1);
	    $succeedvals = substr($dec,$round+1);
	
	    if ($firstdrop > 5)
	    {
	        $fpart = $num_parts[0];
	        
	        if ($zeroval == 0)
	        {
	        	$spart = $zeroval.((substr($dec,0,$round)) + 1);
	        }
	        else 
	        {
	        	$spart = (substr($dec,0,$round)) + 1;
	        }
	    }
	    elseif ($firstdrop < 5)
	    {
	        $fpart = $num_parts[0];
	        $spart = substr($dec,0,$round);
	    }
	    elseif ($firstdrop == 5 && $succeedvals == "" || $succeedvals == 0)
	    {
	        if (in_array($lastkept,array(0,2,4,6,8)))
	        {
	            $fpart = $num_parts[0];
	            $spart = substr($dec,0,$round);
	        }
	        else 
	        {
	            $fpart = $num_parts[0];
	            if ($zeroval == 0)
	            {
	            	$spart = $zeroval.((substr($dec,0,$round)) + 1);
	            }
	            else 
	            {
	            	$spart = (substr($dec,0,$round)) + 1;
	            }
	        }
	    }
	    elseif ($firstdrop == 5 && $succeedvals != "" || $succeedvals > 0)
	    {
	        $fpart = $num_parts[0];
	        
	        if ($zeroval == 0)
	        {
	        	$spart = $zeroval.((substr($dec,0,$round)) + 1);
	        }
	        else 
	        {
	        	$spart = (substr($dec,0,$round)) + 1;
	        }
	    }
	
	    $newnum = str_pad($fpart,1,0,STR_PAD_LEFT).".".str_pad($spart,$round,0,STR_PAD_RIGHT);
	    
	    return $newnum;
	}

?>
