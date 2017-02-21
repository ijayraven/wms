<?php
include("adodb/adodb.inc.php");
	
	$conn	=	ADONewConnection('mysqlt');
	$dbconn	=	$conn->Connect('192.168.250.48','root','','FDC_PMS');
	if ($dbconn == false) 
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}
	
			                                                        	
	if($_GET['action'] == "do_print")
		{
			$txtTransNo = $_GET['txtTransNo'];
					$selType = $_GET['selType'];
					$txtDateFrom = $_GET['txtDateFrom'];
					$txtDateTo = $_GET['txtDateTo'];
					$where = "WHERE 1 ";
					$transnolist = "";
								
					if($txtTransNo!=''){
						$where .= "AND TRANSNO='{$txtTransNo}' ";
					}
					if($txtDateFrom!='' and $txtDateTo!=''){
						$where .= "AND START_DATE BETWEEN '{$txtDateFrom}' AND '{$txtDateTo}' ";
					}			
					
					$pa->qrySelectWhereArray("*","PRICEADJ_HDR","{$where}","","");
					$data = $pa->getArrResult();
					
					if($data!=''){
							$datenow 		= date("Y-m-d H:i:s");
							$values = "`PRINTED_DATE` = '{$datenow}', `PRINTED_BY` = '{$_SESSION["login_username"]}'";
							$where = "`START_DATE` BETWEEN '{$txtDateFrom}' and '{$txtDateTo}'";
							$pa->qryUpdate("{$values}","PRICEADJ_HDR",$where);
						
							
						foreach ($data as $dataKey => $dataVal) {		
							if($transnolist==""){
								$transnolist =	$dataVal['TRANSNO'];
							}else{
								$transolist = 	$transnolist.','.$dataVal['TRANSNO'];
							}							
						}
						echo "$('#hidPrintCode').val('{$transnolist}');";
						echo "$('#dialog_print_ok_cancel').dialog('open');";
						echo "$('#dialog_print_ok_cancel').html('Are you sure you want to print the result?');";			
					}	
					exit;
		}
?>