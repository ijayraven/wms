<?php
/********************************************************************************************************************
* FILE NAME :	create_reports.php																					*
* PURPOSE :																											*
* FILE REFERENCES :																									*
* NAME I/O DESCRIPTION 																								*
* ---------------------																								*
* EXTERNAL VARIABLES :																								*
* Source :																											*
* NAME I/O DESCRIPTION 																								*
* ---------------------																								*
* EXTERNAL REFERENCE :																								*
* NAME DESCRIPTION																									*
* ---------------------																								*
* ABNORMAL TERMINATION CONDITIONS, ERROR AND WARNING MESSAGES :														*
* ASSUMPTIONS, CONSTRAINTS, RESTRICTIONS :																			*
* NOTES :																											*
* REQUIRMENTS/FUNCTIONAL SPECIFICATION REFERENCES :																	*
* DATE 		AUTHOR	 			CHANGE ID	 	RELEASE 		DESCRIPTION OF CHANGE								*
* 2013/08/04	Raymond A. Galaroza																					*
* 																													*
* ALGORITHM(pseudocode)																								*
* 																													*
*********************************************************************************************************************/
session_start();
set_time_limit(0);
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../index.php'</script>";
}
if (isset($_POST['btnsubmit'])) 
{
	$aFile		=	$_FILES['uploadfile'];
	$type		=	$aFile['type'];
	$tmpname	=	$aFile['tmp_name'];
	$name		=	$aFile['name'];
	if ($type == 'text/csv') 
	{
		$aData	=	array();
		$n = '1'; //first n lines that are not wanted 
		$fn 			= 	fopen($tmpname, "r");
		$file 			= 	fread( $fn, filesize($tmpname));
		$aOutput		=	explode("\n",trim($file));
		/*Check CSV file authenticated*/
		for( $i=0; $i<$n  ; $i++) 
		{ 
			$HDR		=	trim($aOutput[$i]);
			$out_HDR	=	explode("|",trim($HDR));
			if (trim($out_HDR[$i]) == 'HD') 
			{
				$do_process	=	'YES';
			}
			else 
			{
				$do_process	=	'NO';
			}
		}
		if ($do_process == 'YES') 
		{
			foreach ($aOutput as $akey)
			{
				$explod_val	=	explode("|",$akey);
				$opt		=	trim($explod_val[0]);
				if ($opt == "HD") 
				{
					$Refno_HD	=	trim($explod_val[1]);	//REFNO
					$RTV		=	trim($explod_val[2]);	//RTV
					$Scandate	=	trim($explod_val[3]);	//SCANDATE
					$BranchCode	=	trim($explod_val[4]);	//BRANCH CODE
				}
				else if($opt == "DT") 
				{
					$Sku		=	trim($explod_val[2]);		//SKU
					$Qty		=	trim($explod_val[3]);		//QTY
					$aData[$BranchCode][$Refno_HD][$RTV][$Scandate][$Sku]['QTY']	+=	$Qty;
				}
			}
			/* ******************* S T A R T  TO  C R E A T E CSV F I L E ******************* */
			$enter			= chr(13).chr(10);
			foreach ($aData as $key_BranchCode=>$val_Refno)
			{
				foreach ($val_Refno as $key_Refno=>$val_RTV)
				{
					foreach ($val_RTV as $key_RTV=>$val_Scandate)
					{
						$custno		=	$global_func->Select_vaL($Filstar_conn_1,FDCRMS,"custmast","CustNo","CustomerBranchCode = '{$key_BranchCode}' ");
						$custname	=	$global_func->Select_vaL($Filstar_conn_1,FDCRMS,"custmast","CustName","CustomerBranchCode = '{$key_BranchCode}' ");
						$sCsv	.=	 "FILSTAR DISTRIBUTORS CORPORATION;RUN DATE:".date("m/d/Y");
						$sCsv	.=	 $enter;
						$sCsv	.=	 "RUN TIME:".date("H:i:s");
						$sCsv	.=	 $enter;
						$sCsv	.=	 "CUST. CODE:$custno-$custname";
						$sCsv	.=	 $enter;
						$sCsv	.=	 "RTV NO.:$key_RTV";
						$sCsv	.=	 $enter;
						$sCsv	.=	 $enter;
						
						$sCsv	.=	 ";;;;;;;;;;A;B;A x B;A;C;A x C";
						$sCsv	.=	 $enter;
						$sCsv	.=	 "$key_item;;;;;;;;;;;;TOTAL DISCOUNTED;;;TOTAL RETAIL/";
						$sCsv	.=	 $enter;
						$sCsv	.=	 "GENCLASS;STKNO;BARCODE;SCODE;DESCRIPTION;PROD. TYPE;ITEM TYPE;CATEGORY CLASS;ITEM CLASS;SACODE;QTY;COST PRICE;NET AMOUNT;QTY;RETAIL PRICE;GROSS AMOUNT";
						$sCsv	.=	 $enter;
						foreach ($val_Scandate as $key_Scandate=>$val_SKU)
						{
							foreach ($val_SKU as $key_SKU=>$val)
							{
								$UnitPrice	=	$global_func->Select_val($Filstar_conn_2,FDCRMS,"itemmaster","UnitPrice","ItemNo = '{$val_2}'");
								/* Computation to get the discount */
								$nPercent = 100; // 100%
								$nTotalDisc = ($UnitPrice * ($Disc / $nPercent));
								
								/* Total of UnitPrice */
								$nTotalCostPrice = ($UnitPrice - $nTotalDisc);
									
								/* Total of CostPrice */
								$nTotalCostPrice   = number_format($nTotalCostPrice, 2);
								/* Total of RetailPrice */
								$nTotalRetailPrice = number_format($UnitPrice, 2);
								
								$nTotalNet   = $val_11 * $nTotalCostPrice;
								$nTotalGross = $val_11 * $nTotalRetailPrice;
								
								$val_1	=	substr($key_SKU,0,2);
								$val_2	=	substr($key_SKU,2);
								$val_3	=	"	".$global_func->Select_val($Filstar_conn_1,FDCRMS,"itemmaster","BarCode","ItemNo = '{$val_2}'");
								$val_4	=	$global_func->Select_val($Filstar_conn_1,FDCRMS,"itemmaster","SupplementCode","ItemNo = '{$val_2}'");
								$val_5	=	$global_func->Select_val($Filstar_conn_1,FDCRMS,"itemmaster","ItemDesc","ItemNo = '{$val_2}'");
								$val_6	=	$global_func->Select_val($Filstar_conn_1,FDCRMS,"itemmaster","ProdGroup","ItemNo = '{$val_2}'");
								$val_7	=	$global_func->Select_val($Filstar_conn_1,FDCRMS,"itemmaster","ItemType","ItemNo = '{$val_2}'");
								$val_8	=	$global_func->Select_val($Filstar_conn_1,FDCRMS,"itemmaster","CategoryClass","ItemNo = '{$val_2}'");
								$val_9	=	$global_func->Select_val($Filstar_conn_1,FDCRMS,"itemmaster","ItemClass","ItemNo = '{$val_2}'");
								$val_10	=	$global_func->Select_val($Filstar_conn_1,FDCRMS,"itemmaster","SACode","ItemNo = '{$val_2}'");
								$val_11	=	$val['QTY'];
								$val_12	=	$nTotalCostPrice;			//COST PRICE
								$val_13	=	$nTotalNet;					//NET AMOUNT
								$val_14	=	$nTotalRetailPrice;			//RETAIL PRICE
								$val_15	=	$nTotalGross;				//GROSS AMOUNT
								$val_sku=	"	".substr($key_SKU,2);
								
								/*				1		 2			3			4		5					6				7					8				9				10		11			12				13			11			14					15*/										
								/*		"   GENCLASS;  STKNO   ;  BARCODE ;  SCODE	;DESCRIPTION	;	PROD. TYPE	;	ITEM TYPE	;	CATEGORY CLASS	;	ITEM CLASS	;	SACODE	;	QTY	;	COST PRICE	;	NET AMOUNT	;	QTY	;	RETAIL PRICE	;	GROSS AMOUNT";	*/
									
								//$sCsv	.=	$val_1.";".$val_2.";".$val_3.";".$val_4.";".$val_5.";".$val_6.";".$val_7.";".$val_8.";".$val_9.";".$val_10.";".$val_11.";".$val_12.";".$val_13.";".$val_11.";".$val_14.";".$val_15;
								$sCsv	.=	$val_1.";".$val_sku.";".$val_3.";".$val_4.";".$val_5.";".$val_6.";".$val_7.";".$val_8.";".$val_9.";".$val_10.";".$val_11.";".$val_12.";".$val_13.";".$val_11.";".$val_14.";".$val_15;
								$sCsv	.=	$enter;
								
								$nGrandQty			+=	$val_11;
								$nGrandTotalNet		+=	$val_13;
								$nGrandTotalGross	+=	$val_15;
								$nCount++;
							}
							
							$sCsv	.=	$enter;
							$sCsv	.=	";GRAND TOTAL------>;;;;;;;;;".$nGrandQty.";;".$nGrandTotalNet.";;;".$nGrandTotalGross;
							$sCsv	.=	$enter;
							$sCsv	.=	$enter;
							
							$sCsv	.=	";FILSTAR DISTRIBUTORS CORP.;".date("m/d/Y");	
							$sCsv	.=	$enter;
							$sCsv	.=	";UPLOADED SCANNED DATA CONTROL LISTING;".date("H:i:s");
							$sCsv	.=	$enter;
							$sCsv	.=	";SCANNED DATE:".$key_Scandate;
							$sCsv	.=	$enter;
							$sCsv	.=	$enter;
							
							$sCsv	.=	";TOTAL RECORDS====>;".$nCount;
							$sCsv	.=	$enter;
							$sCsv	.=	$enter;
							$sCsv	.=	$enter;
							
							$sCsv	.=	"*	*	*	*	*	*	*	*	*	*	end of reports	*	*	*	*	*	*	*	*	*	*";
							$sCsv	.=	$enter;
							$sCsv	.=	$enter;
							$sCsv	.=	$enter;
							$sCsv	.=	$enter;
							
							$nCount				=	"";
							$nGrandQty			=	"";
							$nGrandTotalNet		=	"";
							$nGrandTotalGross	=	"";
						}
					}
				}
			}
			header("Content-type: application/x-msdownload");
			header("Content-Disposition: attachment; filename=$BranchCode.csv");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo $sCsv;
		}
		else 
		{
			echo "<script>alert('Unable to upload unauthenticated file.');</script>";
			echo "<script>location='index.php'</script>";
		}
	}
	else 
	{
		echo "<script>alert('Invalid Upload file');</script>";
		echo "<script>location='index.php'</script>";
	}
	exit();
}
?>
