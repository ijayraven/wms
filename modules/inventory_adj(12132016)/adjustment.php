<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../index.php'</script>";
}


$cTmpTable	= 	"tmp_IAM_".str_replace(".", "_", $_SESSION['username_id']);

$cQuery 	= "CREATE TABLE IF NOT EXISTS WMS_NEW.{$cTmpTable} ("
			." `ID` INT( 10 ) NOT NULL AUTO_INCREMENT ,"
			." `ITEM` VARCHAR( 20 ) NULL ,"
			." `STATUS` VARCHAR( 20 ) NULL ,"
			." `ITEMDESC` VARCHAR( 100 ) NULL ,"
			." `QTY` INT( 11) NULL, "
			." `OPERATION` VARCHAR( 20 ) NULL, "
			." `UNITCOST` DECIMAL( 12,2 ) NULL, "
			." `UNITPRICE` DECIMAL( 12,2 ) NULL, "
			." `GROSS` DECIMAL( 12,2 ) NULL, "
			." `NET` DECIMAL( 12,2 ) NULL, "
			." PRIMARY KEY ( `ID` ) "
			." ) ENGINE = MEMORY COMMENT = 'Used for adding record into Dispatch'";
$rsQuery	=	$Filstar_conn->Execute($cQuery);
if ($rsQuery == false) 
{
	echo $Filstar_conn->ErrorMsg()."::".__LINE__;
	die();
}

	$action	=	$_GET['action'];

	$action		=	$_GET['action'];
	if ($action=='DISPLAY_') 
	{
		$DFROM			=	$_GET['DFROM'];
		$DTO			=	$_GET['DTO'];
		
		$txttransno		=	$_POST['txttransno'];
		$txtrefno		=	$_POST['txtrefno'];
		$customercode	=	$_POST['customercode'];
		$selrefno		=	$_POST['selrefno'];
		$selstatus		=	$_POST['selstatus'];
		
		
		$sel_val	=	"SELECT * from WMS_NEW.INVENTORYADJUSTMENT_HDR WHERE ADDEDDATE between '{$DFROM}' AND '{$DTO}' ";
		if (!empty($txttransno)) 
		{
			$sel_val	.=	" AND IATRANSNO = '{$txttransno}' ";
		}
		if (!empty($customercode))
		{
			$sel_val	.=	" AND CUSTNO = '{$customercode}' ";
		}
		if (!empty($txtrefno))
		{
			$sel_val	.=	" AND REFN0 = '{$txtrefno}' ";
		}
		if (!empty($selrefno)) 
		{
			$sel_val	.=	" AND REFTYPE = '{$selrefno}' ";
		}
		if ($selstatus != 'ALL')
		{
			$sel_val	.=	" AND STATUS = '{$selstatus}' ";
		}
		$rssel_val		=	$Filstar_conn->Execute($sel_val);
		if ($rssel_val==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$cnt	=	$rssel_val->RecordCount();
		if ($cnt > 0) 
		{
			$get_val	=	"SELECT * from WMS_NEW.INVENTORYADJUSTMENT_HDR WHERE ADDEDDATE between '{$DFROM}' AND '{$DTO}' ";
			if (!empty($txttransno)) 
			{
				$get_val	.=	" AND IATRANSNO = '{$txttransno}' ";
			}
			if (!empty($customercode))
			{
				$get_val	.=	" AND CUSTNO = '{$customercode}' ";
			}
			if (!empty($txtrefno))
			{
				$get_val	.=	" AND REFN0 = '{$txtrefno}' ";
			}
			if (!empty($selrefno)) 
			{
				$get_val	.=	" AND REFTYPE = '{$selrefno}' ";
			}
			if ($selstatus != 'ALL')
			{
				$get_val	.=	" AND STATUS = '{$selstatus}' ";
			}
			$rsget_val		=	$Filstar_conn->Execute($get_val);
			if ($rsget_val==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			
			$show	.=	"<form id='form_detail' name='form_detail'>";
			$show	.=	"<table width='100%' border='0'>";
			$show	.=	"<tr class='Header_style' style='font-size:15px;'>";
			$show	.=		"<td width='5%' nowrap>";
			$show	.=			"LINENO";
			$show	.=		"</td>";
			$show	.=				"<td width='15%' align='center' nowrap>";
			$show	.=					"TRANSACTION";
			$show	.=				"</td>";
			$show	.=						"<td width='40%' align='center' nowrap>";
			$show	.=							"CUSTOMER";
			$show	.=						"</td>";
			$show	.=								"<td width='10%' align='center' nowrap>";
			$show	.=									"REF DOC";
			$show	.=								"</td>";
			$show	.=										"<td width='10%' align='center' nowrap>";
			$show	.=											"REF TYPE";
			$show	.=										"</td>";
			$show	.=												"<td width='10%' align='center' nowrap>";
			$show	.=													"STATUS";
			$show	.=												"</td>";
			$show	.=												"<td width='10%' align='center' nowrap>";
			$show	.=													"ACTION";
			$show	.=												"</td>";
			$show	.=	"</tr>";
		
			
			$counter	=	1;
			
			while (!$rsget_val->EOF) 
			{
				$IATRANSNO	=	$rsget_val->fields['IATRANSNO'];
				$ismanual	=	substr($IATRANSNO,0,3);
				
				$CUSTNO		=	$rsget_val->fields['CUSTNO'];
				$REFN0		=	$rsget_val->fields['REFN0'];
				$REFTYPE	=	$rsget_val->fields['REFTYPE'];
				$STATUS		=	$rsget_val->fields['STATUS'];
				$ADDEDDATE	=	$rsget_val->fields['ADDEDDATE'];
				
				$custname		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
				
				if ($STATUS == 'IN-PROCESS') 
				{
				$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' style='font-size:13px;' onclick=display_item('{$IATRANSNO}');>";
				}
				else 
				{
				$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover' style='font-size:13px;'>";
				}
				
				$show	.=	"<td align='center'  >";
				$show	.=	$counter;
				$show	.=	"</td>";
	
				$show	.=	"<td align='center'  >";
				$show	.=	$IATRANSNO;
				$show	.=	"</td>";
	
				$show	.=	"<td align='center'   nowrap >";
				$show	.=	$CUSTNO.'-'.$custname;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center'   nowrap>";
				$show	.=	$REFN0;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center'   nowrap>";
				$show	.=	$REFTYPE;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center'   nowrap>";
				$show	.=	$STATUS;
				$show	.=	"</td>";
				
				if ($STATUS=='POSTED' && $ismanual == 'IAM') 
				{
				$show	.=	"<td align='center'   nowrap>";
				$show	.=		"<img src='../../images/images/action_icon/print.png' onclick=PRINT_this('{$IATRANSNO}'); title='PRINT $IATRANSNO'; style='width:17px;'>";	
				$show	.=	"</td>";
				}
				else 
				{
				$show	.=	"<td align='center'   nowrap>";
				$show	.=		"&nbsp;";	
				$show	.=	"</td>";	
				}
				
				$show	.=	"</tr>";
				
				$counter++;
				
				$rsget_val->MoveNext();	
			}
				$show	.=	"</table>";
		}
		else 
		{
			$show	=	"<table width='100%' border='0'>";
			$show	.=	"<tr align='center' bgcolor='#FFFFF' class='Text_header_value'>";
			$show	.=		"<td width='100%' colspan='4' style='font-size:30px;color:red'>";
			$show	.=			"<blink>NO RECORD FOUND</blink>";
			$show	.=		"</td>";
			$show	.=	"</tr>";
			$show	.=	"</table>";
		}
		echo $show;
		exit();
	}
	
	if ($action=='ITEM_LIST') 
	{
		$IANO	=	$_GET['VAL_IANO'];
		
		$IANO	=	"SELECT IATRANSNO,SKUNO,IAQTY,REMARKS FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE  IATRANSNO = '{$IANO}' ";
		$rsIANO	=	$Filstar_conn->Execute($IANO);
		if ($rsIANO==false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		$show	.=	"<table width='100%' border='0'>";
		$show	.=	"<tr style='font-size:15px;'>";
		$show	.=		"<td width='5%' nowrap colspan='6'>";
		$show	.=			$_GET['VAL_IANO'];
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$show	.=	"<tr class='Header_style' style='font-size:15px;'>";
		$show	.=		"<td width='5%' nowrap>";
		$show	.=			"LINENO";
		$show	.=		"</td>";
		$show	.=				"<td width='15%' align='center' nowrap>";
		$show	.=					"SKUNO";
		$show	.=				"</td>";
		$show	.=						"<td width='50%' align='center' nowrap>";
		$show	.=							"DESCRIPTION";
		$show	.=						"</td>";
		$show	.=								"<td width='10%' align='center' nowrap>";
		$show	.=									"I.A. QTY";
		$show	.=								"</td>";
		$show	.=										"<td width='10%' align='center' nowrap>";
		$show	.=											"REMARKS";
		$show	.=										"</td>";
		$show	.=												"<td width='10%' align='center' nowrap>";
		$show	.=													"LOCATION";
		$show	.=												"</td>";
		if (substr($_GET['VAL_IANO'],0,3) == 'IAM')
		{
			$show	.=												"<td width='10%' align='center' nowrap>";
			$show	.=													"ACTION";
			$show	.=												"</td>";
		}
		$show	.=	"</tr>";
		
		$COUNTER	=	1;
		
		while (!$rsIANO->EOF) 
		{
			$IATRANSN0	=	$rsIANO->fields['IATRANSNO'];
			$SKUNO		=	$rsIANO->fields['SKUNO'];
			$IAQTY		=	$rsIANO->fields['IAQTY'];
			$REMARKS	=	$rsIANO->fields['REMARKS'];
			
			$ItemDesc	=	substr($global_func->Select_val($Filstar_conn,FDCRMS,"itemmaster","ItemDesc","ItemNo = '".$SKUNO."'"),0,40);
			$whsloc		=	$global_func->Select_val($Filstar_conn,FDCRMS,"itembal","whsloc","itmnbr = '".$SKUNO."' and house = 'FDC' ");
			$REMARKS	=	$global_func->Select_val($Filstar_conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE = '".$REMARKS."' ");
			
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#cccccc';\" bgcolor=\"#cccccc\" class='Text_header_hover' style='font-size:13px;' >";
			$show	.=	"<td align='center'  >";
			$show	.=	$COUNTER;
			$show	.=	"</td>";

			$show	.=	"<td align='center'  >";
			$show	.=	$SKUNO;
			$show	.=	"</td>";

			$show	.=	"<td align='center'   nowrap >";
			$show	.=	$ItemDesc;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'   nowrap>";
			$show	.=	$IAQTY;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'   nowrap>";
			$show	.=	$REMARKS;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'   nowrap>";
			$show	.=	$whsloc;
			$show	.=	"</td>";
			
			if (substr($_GET['VAL_IANO'],0,3) == 'IAM')
			{
				$show	.=	"<td width='10%' align='center' nowrap>";
				$show	.=		"<img src='../../images/action_icon/edit-icon.gif' onclick=EDIT_this('{$IATRANSN0}','{$SKUNO}','{$IAQTY}'); title='EDIT $SKUNO'; style='width:17px;'>";	
				$show	.=		"&nbsp;&nbsp;<img src='../../images/action_icon/delete-icon.gif' onclick=DELETE_this('{$IATRANSN0}','{$SKUNO}'); title='DELETE $SKUNO'; style='width:17px;'>";	
				$show	.=	"</td>";
			}
			$show	.=	"</tr>";
			$rsIANO->MoveNext();
		}
		
		$show	.=	"<input type='hidden' name='hdniano' id='hdniano' value='{$IATRANSN0}'>";
		$show	.=	"</table>";
		
		echo $show;
		exit();
	}
	
	
	if ($action=='POSTING') 
	{
		$THIS_IANO	=	$_GET['THIS_IANO'];
		
		$Filstar_conn->StartTrans();
		$sel_dtl	=	"SELECT  SKUNO,IAQTY,HOUSE,IA_TYPE,MOVEMENT FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSNO = '{$THIS_IANO}' ";
		$rssel_dtl	=	$Filstar_conn->Execute($sel_dtl);
		if ($rssel_dtl==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_dtl->EOF) 
		{
			$SKUNO	=	$rssel_dtl->fields['SKUNO'];
			$IAQTY	=	$rssel_dtl->fields['IAQTY'];
			$HOUSE	=	$rssel_dtl->fields['HOUSE'];
			$IA_TYPE=	$rssel_dtl->fields['IA_TYPE'];
			$MOVEMENT=	$rssel_dtl->fields['MOVEMENT'];
			
			
			
			if ($MOVEMENT == 'INCREASE')
			{
				$sel_itembal	=	"SELECT onhqty,whsloc,penqty FROM itembal where itmnbr = '{$SKUNO}' and house = 'FDC' ";
				$rssel_itembal	=	$Filstar_conn->Execute($sel_itembal);
				if ($rssel_itembal==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				$onhqty			=	$rssel_itembal->fields['onhqty'];
				$penqty			=	$rssel_itembal->fields['penqty'];
				$whsloc			=	$rssel_itembal->fields['whsloc'];
				
				
				if ($IA_TYPE=='ONHAND') 
				{
					$update_item	=	"UPDATE itembal set onhqty = (onhqty + $IAQTY) where itmnbr = '{$SKUNO}' and house = '{$HOUSE}' ";
					
					$newonhand		=	$onhqty+$IAQTY;
				}
				elseif ($IA_TYPE=='PENDING')
				{
					$update_item	=	"UPDATE itembal set onhqty = (penqty + $IAQTY) where itmnbr = '{$SKUNO}' and house = '{$HOUSE}' ";
					
					$newonhand		=	$penqty+$IAQTY;
				}
				$rsupdate_item	=	$Filstar_conn->Execute($update_item);
				if ($rsupdate_item==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
			}
			elseif ($MOVEMENT=='DECREASE')
			{
				$sel_itembal	=	"SELECT onhqty,whsloc,penqty FROM itembal where itmnbr = '{$SKUNO}' and house = 'FDC' ";
				$rssel_itembal	=	$Filstar_conn->Execute($sel_itembal);
				if ($rssel_itembal==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				$onhqty			=	$rssel_itembal->fields['onhqty'];
				$penqty			=	$rssel_itembal->fields['penqty'];
				$whsloc			=	$rssel_itembal->fields['whsloc'];
				
				
				if ($IA_TYPE=='ONHAND') 
				{
					$update_item	=	"UPDATE itembal set onhqty = (onhqty - $IAQTY) where itmnbr = '{$SKUNO}' and house = '{$HOUSE}' ";
					
					$newonhand		=	$onhqty-$IAQTY;
				}
				elseif ($IA_TYPE=='PENDING')
				{
					$update_item	=	"UPDATE itembal set onhqty = (penqty - $IAQTY) where itmnbr = '{$SKUNO}' and house = '{$HOUSE}' ";
					
					$newonhand		=	$penqty-$IAQTY;
				}
				$rsupdate_item	=	$Filstar_conn->Execute($update_item);
				if ($rsupdate_item==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				} 
			}
			
			if ($IA_TYPE=='ONHAND') 
			{
				$update_ia		=	"UPDATE WMS_NEW.INVENTORYADJUSTMENT_DTL set CURRONHANDQTY = '{$onhqty}' , NEWONHANDQTY =  '{$newonhand}', LOCATION = '{$whsloc}' 
									WHERE IATRANSNO = '{$THIS_IANO}' AND SKUNO = '{$SKUNO}' AND IAQTY = '{$IAQTY}' ";
			}
			else 
			{
				$update_ia		=	"UPDATE WMS_NEW.INVENTORYADJUSTMENT_DTL set CURRPENDINGQTY	= '{$onhqty}' , NEWPENDINGQTY =  '{$newonhand}', LOCATION = '{$whsloc}' 
									WHERE IATRANSNO = '{$THIS_IANO}' AND SKUNO = '{$SKUNO}' AND IAQTY = '{$IAQTY}' ";
			}
			$rsupdate_ia	=	$Filstar_conn->Execute($update_ia);
			if ($rsupdate_ia==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			$rssel_dtl->MoveNext();
		}
		
		$update_hdr			=	"UPDATE WMS_NEW.INVENTORYADJUSTMENT_HDR SET STATUS = 'POSTED', POSTEDBY= '{$_SESSION['username']}',POSTEDDATE=SYSDATE(),POSTEDTIME=SYSDATE()
								WHERE IATRANSNO = '{$THIS_IANO}' ";
		$rsupdate_hdr		=	$Filstar_conn->Execute($update_hdr);
		if ($rsupdate_hdr==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		echo "done";
		$Filstar_conn->CompleteTrans();
		exit();
	}
	
	
	if ($action=='SAVE_TMP')
	{
		$custcode	=	$_GET['CUSTOMERCODE'];
		$refno		=	$_GET['TEXTREFNO'];
		$remarks	=	$_GET['REMARKS'];
		$QTY		=	$_GET['QTY'];
		$skuno		=	$_GET['ITEMNO'];
		$opt		=	$_GET['opt__'];
		
		
		$location	=	$_GET['THIS_LOCATION'];
		$iatype		=	$_GET['THIS_IATYPE'];
		$operation	=	$_GET['THIS_OPERATION'];
		
		$total_qty	=	"";
		$total_gross=	"";
		$total_net	=	"";
		$msg		=	"";
		$check_item	=	"";
		
		
		$check_item	=	"YES";
		
		if ($opt=='IA') 
		{
			$sel_cnt	=	"SELECT SKUNO FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSNO = '{$refno}' AND SKUNO = '{$skuno}' ";
			$rssel_cnt	=	$Filstar_conn->Execute($sel_cnt);
			if ($rssel_cnt==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			if ($rssel_cnt->RecordCount() == 0) 
			{
				$check_item	=	"NO";
			}
		}
		elseif ($opt=='MRR')
		{
			$sel_cnt	=	"SELECT mditmno FROM mrrdetail WHERE mdmrnum = '{$refno}' AND mditmno = '{$skuno}' ";
			$rssel_cnt	=	$Filstar_conn->Execute($sel_cnt);
			if ($rssel_cnt==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			if ($rssel_cnt->RecordCount() == 0) 
			{
				$check_item	=	"NO";
			}
		}
		
		if ($check_item == "YES")
		{
			$sel_item 	=	"SELECT ItemNo,ItemDesc,UnitPrice from FDCRMSlive.itemmaster where ItemNo = '{$skuno}' ";
			$rssel_item	=	$Filstar_conn->Execute($sel_item);
			if ($rssel_item==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			$cnt 		=	$rssel_item->RecordCount();
		}
		else 
		{
			$cnt 	=	0;
		}
		if ($cnt	>	0) 
		{
			$cnt_data	=	"SELECT * FROM WMS_NEW.$cTmpTable WHERE ITEM = '{$skuno}' ";
			$rscnt_data	=	$Filstar_conn->Execute($cnt_data);
			if ($rscnt_data==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			
			$check_item	=	$rscnt_data->RecordCount();
			
			if ($check_item == 0)
			{
				$SKUNO		=	$rssel_item->fields['ItemNo'];
				$ItemDesc	=	$rssel_item->fields['ItemDesc'];
				$UnitPrice	=	$rssel_item->fields['UnitPrice'];
				
				$PriceClass		=	$global_func->Select_val($Filstar_conn,FDCRMS,"itemmaster","PriceClass","ItemNo = '".$SKUNO."'");
				
				$DeptNo			=	$global_func->Select_val($Filstar_conn,FDCRMS,"itemmaster","DeptNo","ItemNo = '".$SKUNO."'");
			
				$CustPriceBook	=	$global_func->Select_val($Filstar_conn,FDCRMS,"custmast","CustPriceBook","CustNo = '{$custcode}' and CustStatus = 'A' ");
				
				$DISC1			=	$global_func->Select_val($Filstar_conn,FDCRMS,"custdiscount","Discount","PriceBook = '{$CustPriceBook}' and PriceClass = '{$PriceClass}'");
				
				$aData['Disc']        = $global_func->AccntRound($DISC1 / 100, 2);
				$aData['DiscAmt']     = $global_func->AccntRound(($UnitPrice * $aData['Disc']), 2);
				$aData['GrossAmount'] = $global_func->AccntRound(($QTY * $UnitPrice), 2);
				$aData['DiscAmount']  = $global_func->AccntRound(($aData['DiscAmt'] * $QTY), 2);
				$aData['NetAmount']   = $global_func->AccntRound(($aData['GrossAmount'] - $aData['DiscAmount']), 2);
				$discount			  = $global_func->AccntRound($aData['GrossAmount']-$aData['NetAmount'],2);
				$UnitCost			  = $global_func->AccntRound($UnitPrice-$aData['DiscAmt'],2);
				
				$insert_data		=	"INSERT INTO WMS_NEW.$cTmpTable(`ITEM`,`ITEMDESC`,`QTY`,`STATUS`,`OPERATION`,`UNITCOST`,`UNITPRICE`,`GROSS`,`NET`)";
				$insert_data		.=	"VALUES";
				$insert_data		.=	"('{$SKUNO}','{$ItemDesc}','{$QTY}','{$DeptNo}','{$operation}','{$UnitCost}','{$UnitPrice}','{$aData['GrossAmount']}','{$aData['NetAmount']}')";
				$rsinsert_data		=	$Filstar_conn->Execute($insert_data);
				if ($rsinsert_data==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
			}
		}
			$show	.=	"<table width='100%' border='0'>";
			if ($check_item > 0) 
			{
			$show	.=	"<tr style='font-size:20px;color:red'>";
			$show	.=		"<td width='100%' colspan='8' align='center'>";
			$show	.=			"ITEM NO.$skuno ALREADY EXIST";
			$show	.=		"</td>";
			$show	.=	"</tr>";
			}
			if ($cnt==0) {
			$show	.=	"<tr style='font-size:20px;color:red'>";
			$show	.=		"<td width='100%' colspan='8' align='center'>";
			$show	.=			"ITEM NOT FOUND";
			$show	.=		"</td>";
			$show	.=	"</tr>";
			}
			
			$show	.=	"<tr class='Header_style' style='font-size:11px;'>";
			$show	.=		"<td width='5%' nowrap>";
			$show	.=			"ITEMNO";
			$show	.=		"</td>";
			$show	.=				"<td width='25%' align='center' nowrap>";
			$show	.=					"DESCRIPTION";
			$show	.=				"</td>";
			$show	.=						"<td width='5%' align='center' nowrap>";
			$show	.=							"QUANTITY";
			$show	.=						"</td>";
			$show	.=							"<td width='5%' align='center' nowrap>";
			$show	.=								$iatype;
			$show	.=							"</td>";
			$show	.=							"<td width='5%' align='center' style='color:#ff5c33' nowrap>";
			$show	.=								"NEW ".$iatype;
			$show	.=							"</td>";
			$show	.=							"<td width='5%' align='center' style='color:#ff5c33' nowrap>";
			$show	.=								"LOCATION";
			$show	.=							"</td>";
			$show	.=							"<td width='5%' align='center' style='color:#ff5c33' nowrap>";
			$show	.=								"OPERATION";
			$show	.=							"</td>";
			$show	.=								"<td width='5%' align='center' nowrap>";
			$show	.=									"UNITCOST";
			$show	.=								"</td>";
			$show	.=										"<td width='5%' align='center' nowrap>";
			$show	.=											"UNITPRICE";
			$show	.=										"</td>";
			$show	.=												"<td width='10%' align='center' nowrap>";
			$show	.=													"GROSS";
			$show	.=												"</td>";
			$show	.=														"<td width='10%' align='center' nowrap>";
			$show	.=															"NET";
			$show	.=														"</td>";
			$show	.=																"<td width='5%' align='center' nowrap>";
			$show	.=																	"ACTION";
			$show	.=																"</td>";
			$show	.=	"</tr>";
			
			$sel_data		=	"select * from WMS_NEW.$cTmpTable where 1";
			$rssel_data		=	$Filstar_conn->Execute($sel_data);
			if ($rssel_data==false) 
			{
				echo $rssel_data->ErrorMsg()."::".__LINE__;exit();
			}
			while (!$rssel_data->EOF) 
			{
				$ID			=	$rssel_data->fields['ID'];
				$ITEM		=	$rssel_data->fields['ITEM'];
				$ITEMDESC	=	$rssel_data->fields['ITEMDESC'];
				$QTY		=	$rssel_data->fields['QTY'];
				$OPERATION	=	$rssel_data->fields['OPERATION'];
				$UNITCOST	=	$rssel_data->fields['UNITCOST'];
				$UNITPRICE	=	$rssel_data->fields['UNITPRICE'];
				$GROSS		=	$rssel_data->fields['GROSS'];
				$NET		=	$rssel_data->fields['NET'];
				
				if ($iatype=='ONHAND') 
				{
					$val_qty	=	$global_func->Select_val($Filstar_conn,FDCRMS,"itembal","onhqty","itmnbr= '{$ITEM}' AND house = '{$location}' ");
				}
				else
				{
					$val_qty	=	$global_func->Select_val($Filstar_conn,FDCRMS,"itembal","ordqty","itmnbr= '{$ITEM}' AND house = '{$location}' ");
				}
				
				if ($location=='FDC') 
				{
					$whsloc		=	$global_func->Select_val($Filstar_conn,FDCRMS,"itembal","whsloc","itmnbr = '".$ITEM."' and house = 'FDC' ");
				}
				
				if ($operation=='INCREASE') 
				{
					$current	=	$val_qty+$QTY;
				}
				else 
				{
					$current	=	$val_qty-$QTY;
				}
				
				
				$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#cccccc';\" bgcolor=\"#cccccc\" class='Text_header_hover' style='font-size:13px;' >";
				$show	.=	"<td align='center'  >";
				$show	.=	$ITEM;
				$show	.=	"</td>";
	
				$show	.=	"<td align='center'  >";
				$show	.=	$ITEMDESC;
				$show	.=	"</td>";
	
				$show	.=	"<td align='center' >";
				$show	.=	$QTY;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center' >";
				$show	.=	$val_qty;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center' style='color:#ff5c33' >";
				$show	.=	$current;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center' style='color:#ff5c33' >";
				$show	.=	$whsloc;
				$show	.=	"</td>";
				
				$show	.=	"<td align='center' style='color:#ff5c33' >";
				$show	.=	$OPERATION;
				$show	.=	"</td>";
				
				$show	.=	"<td align='right' >";
				$show	.=	number_format($global_func->AccntRound($UNITCOST,2),2);
				$show	.=	"</td>";
				
				$show	.=	"<td align='right' >";
				$show	.=	number_format($global_func->AccntRound($UNITPRICE,2),2);
				$show	.=	"</td>";
				
				$show	.=	"<td align='right' >";
				$show	.=	number_format($global_func->AccntRound($GROSS,2),2);
				$show	.=	"</td>";
				
				$show	.=	"<td align='right' >";
				$show	.=	number_format($global_func->AccntRound($NET,2),2);
				$show	.=	"</td>";
				
				$show	.=	"<td align='center'>";
				$show	.=		"<img src='../../images/action_icon/delete-icon.gif' onclick=delete_this('{$ID}'); title='Delete $ITEM'; style='width:17px;'>";	
				$show	.=	"</td>";
				
				$total_qty	+=	$QTY;
				$total_gross+=	$GROSS;
				$total_net	+=	$NET;
				
				$rssel_data->MoveNext();
			}
			
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#cccccc';\" bgcolor=\"#cccccc\" class='Text_header_hover' style='font-size:13px;' >";
			$show	.=		"<td align='center'  >";
			$show	.=		"&nbsp;";
			$show	.=		"</td>";
			
			$show	.=		"<td align='center'  >";
			$show	.=		"TOTAL";
			$show	.=		"</td>";

			$show	.=		"<td align='center'  >";
			$show	.=		$total_qty;
			$show	.=		"</td>";

			$show	.=		"<td colspan='5' align='center' >";
			$show	.=		"&nbsp";
			$show	.=		"</td>";
			
			$show	.=		"<td align='right' >";
			$show	.=		number_format($total_gross,2);
			$show	.=		"</td>";
			
			$show	.=		"<td align='right' >";
			$show	.=		number_format($total_net,2);
			$show	.=		"</td>";
			
			$show	.=		"<td align='center' >";
			$show	.=		"&nbsp";
			$show	.=		"</td>";
			$show	.=	"</tr>";
			
			if ($rssel_data->RecordCount() > 0) 
			{
				$show	.=	"<tr>";
				$show	.=		"<td colspan='11' align='center' >";
				$show	.=		"<input type='button' name='btnsubmit_iam' id='btnsubmit_iam' value='Submit' onclick='submit_iam();' class='small_button' style='width:120px;height:35px;'>";
				$show	.=		"</td>";
				$show	.=	"</tr>";
			}
		
		$show	.=	"</table>";
		echo $show;
		
		exit();
	}
	
	
	if ($action=='DELETE_ITEM') 
	{
		$id	=	$_GET['ID_'];
		
		$location	=	$_GET['THIS_LOCATION'];
		$iatype		=	$_GET['THIS_IATYPE'];
		$operation	=	$_GET['THIS_OPERATION'];
	
		$cnt_data	=	"DELETE FROM WMS_NEW.$cTmpTable WHERE ID = '{$id}' ";
		$rscnt_data	=	$Filstar_conn->Execute($cnt_data);
		if ($rscnt_data==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$show	.=	"<table width='100%' border='0'>";
		$show	.=	"<tr class='Header_style' style='font-size:11px;'>";
		$show	.=		"<td width='5%' nowrap>";
		$show	.=			"ITEMNO";
		$show	.=		"</td>";
		$show	.=				"<td width='30%' align='center' nowrap>";
		$show	.=					"DESCRIPTION";
		$show	.=				"</td>";
		$show	.=						"<td width='5%' align='center' nowrap>";
		$show	.=							"QUANTITY";
		$show	.=						"</td>";
		$show	.=							"<td width='5%' align='center' nowrap>";
		$show	.=								$iatype;
		$show	.=							"</td>";
		$show	.=							"<td width='5%' align='center' style='color:#ff5c33' nowrap>";
		$show	.=								"NEW ".$iatype;
		$show	.=							"</td>";
		$show	.=							"<td width='5%' align='center' style='color:#ff5c33' nowrap>";
		$show	.=								"LOCATION";
		$show	.=							"</td>";
		$show	.=							"<td width='5%' align='center' style='color:#ff5c33' nowrap>";
		$show	.=								"OPERATION";
		$show	.=							"</td>";
		$show	.=								"<td width='5%' align='center' nowrap>";
		$show	.=									"UNITCOST";
		$show	.=								"</td>";
		$show	.=										"<td width='5%' align='center' nowrap>";
		$show	.=											"UNITPRICE";
		$show	.=										"</td>";
		$show	.=												"<td width='10%' align='center' nowrap>";
		$show	.=													"GROSS";
		$show	.=												"</td>";
		$show	.=														"<td width='10%' align='center' nowrap>";
		$show	.=															"NET";
		$show	.=														"</td>";
		$show	.=																"<td width='5%' align='center' nowrap>";
		$show	.=																	"ACTION";
		$show	.=																"</td>";
		$show	.=	"</tr>";
		
		$sel_data		=	"select * from WMS_NEW.$cTmpTable where 1";
		$rssel_data		=	$Filstar_conn->Execute($sel_data);
		if ($rssel_data==false) 
		{
			echo $rssel_data->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_data->EOF) 
		{
			$ID			=	$rssel_data->fields['ID'];
			$ITEM		=	$rssel_data->fields['ITEM'];
			$ITEMDESC	=	$rssel_data->fields['ITEMDESC'];
			$QTY		=	$rssel_data->fields['QTY'];
			$OPERATION	=	$rssel_data->fields['OPERATION'];
			$UNITCOST	=	$rssel_data->fields['UNITCOST'];
			$UNITPRICE	=	$rssel_data->fields['UNITPRICE'];
			$GROSS		=	$rssel_data->fields['GROSS'];
			$NET		=	$rssel_data->fields['NET'];
			
			if ($iatype=='ONHAND') 
			{
				$val_qty	=	$global_func->Select_val($Filstar_conn,FDCRMS,"itembal","onhqty","itmnbr= '{$ITEM}' AND house = '{$location}' ");
			}
			else
			{
				$val_qty	=	$global_func->Select_val($Filstar_conn,FDCRMS,"itembal","ordqty","itmnbr= '{$ITEM}' AND house = '{$location}' ");
			}
			
			if ($location=='FDC') 
			{
				$whsloc		=	$global_func->Select_val($Filstar_conn,FDCRMS,"itembal","whsloc","itmnbr = '".$ITEM."' and house = 'FDC' ");
			}
			
			if ($operation=='INCREASE') 
			{
				$current	=	$val_qty+$QTY;
			}
			else 
			{
				$current	=	$val_qty-$QTY;
			}
			
			
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#cccccc';\" bgcolor=\"#cccccc\" class='Text_header_hover' style='font-size:13px;' >";
			$show	.=	"<td align='center'  >";
			$show	.=	$ITEM;
			$show	.=	"</td>";

			$show	.=	"<td align='center'  >";
			$show	.=	$ITEMDESC;
			$show	.=	"</td>";

			$show	.=	"<td align='center' >";
			$show	.=	$QTY;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center' >";
			$show	.=	$val_qty;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center' style='color:#ff5c33' >";
			$show	.=	$current;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center' style='color:#ff5c33' >";
			$show	.=	$whsloc;
			$show	.=	"</td>";
			
			$show	.=	"<td align='center' style='color:#ff5c33' >";
			$show	.=	$OPERATION;
			$show	.=	"</td>";
			
			$show	.=	"<td align='right' >";
			$show	.=	number_format($global_func->AccntRound($UNITCOST,2),2);
			$show	.=	"</td>";
			
			$show	.=	"<td align='right' >";
			$show	.=	number_format($global_func->AccntRound($UNITPRICE,2),2);
			$show	.=	"</td>";
			
			$show	.=	"<td align='right' >";
			$show	.=	number_format($global_func->AccntRound($GROSS,2),2);
			$show	.=	"</td>";
			
			$show	.=	"<td align='right' >";
			$show	.=	number_format($global_func->AccntRound($NET,2),2);
			$show	.=	"</td>";
			
			$show	.=	"<td align='center'>";
			$show	.=		"<img src='../../images/action_icon/delete-icon.gif' onclick=delete_this('{$ID}'); title='Delete $ITEM'; style='width:17px;'>";	
			$show	.=	"</td>";
			
			$total_qty	+=	$QTY;
			$total_gross+=	$GROSS;
			$total_net	+=	$NET;
			
			$rssel_data->MoveNext();
		}
		
		$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#cccccc';\" bgcolor=\"#cccccc\" class='Text_header_hover' style='font-size:13px;' >";
		$show	.=		"<td align='center'  >";
		$show	.=		"&nbsp;";
		$show	.=		"</td>";
		
		$show	.=		"<td align='center'  >";
		$show	.=		"TOTAL";
		$show	.=		"</td>";

		$show	.=		"<td align='center'  >";
		$show	.=		$total_qty;
		$show	.=		"</td>";

		$show	.=		"<td colspan='4' align='center' >";
		$show	.=		"&nbsp";
		$show	.=		"</td>";
		
		$show	.=		"<td align='right' >";
		$show	.=		number_format($total_gross,2);
		$show	.=		"</td>";
		
		$show	.=		"<td align='right' >";
		$show	.=		number_format($total_net,2);
		$show	.=		"</td>";
		
		$show	.=		"<td align='center' >";
		$show	.=		"&nbsp";
		$show	.=		"</td>";
		$show	.=	"</tr>";
		
		if ($rssel_data->RecordCount() > 0) 
		{
			$show	.=	"<tr>";
			$show	.=		"<td colspan='10' align='center' >";
			$show	.=		"<input type='button' name='btnsubmit_iam' id='btnsubmit_iam' value='Submit' class='small_button' style='width:120px;height:35px;'>";
			$show	.=		"</td>";
			$show	.=	"</tr>";
		}
		
		echo $show;
		exit();
	}
	
	
	if ($action=='SUBMIT_IAM') 
	{
		$custcode	=	$_GET['CUSTOMERCODE'];
		$refno		=	$_GET['TEXTREFNO'];
		$remarks	=	$_GET['REMARKS'];
		
		$opt		=	$_GET['OPT__'];
		$location	=	$_GET['THIS_LOCATION'];
		$iatype		=	$_GET['THIS_IATYPE'];
		$operation	=	$_GET['THIS_OPERATION'];
		
		
		$Filstar_conn->StartTrans();
		
		$iam	=	$global_func->TRANSEQ_IAM($Filstar_conn);
		
		
		$sel_data	=	"SELECT * FROM WMS_NEW.{$cTmpTable} WHERE 1 ";
		$rssel_data	=	$Filstar_conn->Execute($sel_data);
		if ($rssel_data==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		while (!$rssel_data->EOF) 
		{
			
			$ITEMNO		=	$rssel_data->fields['ITEM'];
			$qty 		=	$rssel_data->fields['QTY'];
			$status		=	$rssel_data->fields['STATUS'];
			$OPERATION	=	$rssel_data->fields['OPERATION'];
		
			$insert_IA_dtl	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_DTL(`IATRANSNO`,`SKUNO`,`ITEMSTATUS`,`HOUSE`,`IA_TYPE`,`MOVEMENT`,`IAQTY`,`REMARKS`) 
								VALUES
								('{$iam}','{$ITEMNO}','{$status}','{$location}','{$iatype}','{$OPERATION}','{$qty}','{$remarks}')";
			$rsinsert_IA_dtl=	$Filstar_conn->Execute($insert_IA_dtl);
			if ($rsinsert_IA_dtl==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
		
			$rssel_data->MoveNext();
		}
		
		$insert_iam_hrd	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_HDR(`IATRANSNO`,`CUSTNO`,`REFN0`,`REFTYPE`,`STATUS`,`ADDEDDATE`,`ADDEDTIME`)
							VALUES('{$iam}','{$custcode}','{$refno}','{$opt}','IN-PROCESS',SYSDATE(),SYSDATE())";
		$rsinsert_ia_hdr=	$Filstar_conn->Execute($insert_iam_hrd);
		if ($rsinsert_ia_hdr==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		$Filstar_conn->CompleteTrans();
		echo "done|".$iam;
		exit();
	}
	
	
	
	if ($action=='MANUAL_IA') 
	{
		$CUSTOMERCODE		=	$_GET['CUSTOMERCODE'];
		$TEXTREFNO			=	$_GET['TEXTREFNO'];
		$REMARKS			=	$_GET['REMARKS'];
		$ITEMNO				=	$_GET['ITEMNO'];
		$OPT				=	$_GET['OPT'];
		
		$PENQTY_CHANGE_FDC	=	$_GET['PENQTY_CHANGE_FDC'];
		$PENQTY_CHANGE_RAW	=	$_GET['PENQTY_CHANGE_RAW'];
		$PENQTY_FDC			=	$_GET['PENQTY_FDC'];
		$PENQTY_RAW			=	$_GET['PENQTY_RAW'];
		
		
		$ONHQTY_CHANGE_RAW	=	$_GET['ONHQTY_CHANGE_RAW'];
		$ONHQTY_CHANGE_FDC	=	$_GET['ONHQTY_CHANGE_FDC'];
		$ONHQTY_RAW			=	$_GET['ONHQTY_RAW'];
		$ONHQTY_FDC			=	$_GET['ONHQTY_FDC'];
		
		
		$Filstar_conn->StartTrans();
		
		$iam	=	$global_func->TRANSEQ_IAM($Filstar_conn);
		
		if ($PENQTY_CHANGE_FDC == 'YES') 
		{
			$insert_IA_penqty_fdc	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_DTL(`IATRANSNO`,`SKUNO`,`HOUSE`,`IA_TYPE`,`IAQTY`,`REMARKS`) 
										VALUES
										('{$iam}','{$ITEMNO}','FDC','PENDING','{$PENQTY_FDC}','{$REMARKS}')";
			$rsinsert_IA_penqty_fdc	=	$Filstar_conn->Execute($insert_IA_penqty_fdc);
			if ($rsinsert_IA_penqty_fdc==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
		}
		if ($PENQTY_CHANGE_RAW == 'YES') 
		{
			$insert_IA_penqty_raw	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_DTL(`IATRANSNO`,`SKUNO`,`HOUSE`,`IA_TYPE`,`IAQTY`,`REMARKS`) 
										VALUES
										('{$iam}','{$ITEMNO}','RAW','PENDING','{$PENQTY_RAW}','{$REMARKS}')";
			if ($insert_IA_penqty_raw==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
		}
		
		if ($ONHQTY_CHANGE_RAW == 'YES') 
		{
			$insert_IA_onhqty_raw	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_DTL(`IATRANSNO`,`SKUNO`,`HOUSE`,`IA_TYPE`,`IAQTY`,`REMARKS`) 
										VALUES
										('{$iam}','{$ITEMNO}','RAW','ONHAND','{$ONHQTY_RAW}','{$REMARKS}')";
			if ($insert_IA_onhqty_raw==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
		}
		if ($ONHQTY_CHANGE_FDC == 'YES') 
		{
			$insert_IA_onhqty_fdc	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_DTL(`IATRANSNO`,`SKUNO`,`HOUSE`,`IA_TYPE`,`IAQTY`,`REMARKS`) 
										VALUES
										('{$iam}','{$ITEMNO}','FDC','ONHAND','{$ONHQTY_FDC}','{$REMARKS}')";
			if ($insert_IA_onhqty_raw==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
		}
		
		$insert_iam_hrd	=	"INSERT INTO WMS_NEW.INVENTORYADJUSTMENT_HDR(`IATRANSNO`,`CUSTNO`,`REFN0`,`REFTYPE`,`STATUS`,`ADDEDDATE`,`ADDEDTIME`)
							VALUES('{$iam}','{$CUSTOMERCODE}','{$TEXTREFNO}','{$OPT}','IN-PROCESS',SYSDATE(),SYSDATE())";
		$rsinsert_ia_hdr=	$Filstar_conn->Execute($insert_ia_hrd);
		if ($rsinsert_ia_hdr==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		$Filstar_conn->CompleteTrans();
		echo "done";
		exit();
	}
	
	
	if ($action=='UPDATE_ITEM') 
	{
		
		$sku	=	$_GET['SKUNO__'];
		$qty	=	$_GET['QTY__'];
		$iam	=	$_GET['IAM__'];
		
		
		$Filstar_conn->StartTrans();
		
		$update_	=	"UPDATE WMS_NEW.INVENTORYADJUSTMENT_DTL SET IAQTY = '{$qty}' WHERE IATRANSNO = '{$iam}' AND SKUNO = '{$sku}' ";
		$rspdate	=	$Filstar_conn->Execute($update_);
		if ($rspdate==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		echo "done";
		
		$Filstar_conn->CompleteTrans();
		exit();
	}
	
	if ($action=='DELETE_ITEM_EDIT') 
	{
		$iam	=	$_GET['VAL_TRX'];
		$sku	=	$_GET['VAL_SKU'];
		
		$Filstar_conn->StartTrans();
		
		$sel_data 	=	"SELECT * FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSNO = '{$iam}' ";
		$rssel_data	=	$Filstar_conn->Execute($sel_data);
		if ($rssel_data==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		if ($rssel_data->RecordCount() > 1) 
		{
			$del_item	=	"DELETE FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSNO = '{$iam}' AND SKUNO = '{$sku}' ";
			$rsdel_item	=	$Filstar_conn->Execute($del_item);
			if ($rsdel_item==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			echo "done";
		}
		elseif ($rssel_data->RecordCount() == 1)
		{
			echo "ONLY1";
		}
		
		$Filstar_conn->CompleteTrans();
		exit();
	}
	
	if ($action=='GET_IA') 
	{
		$VAL_OPT	=	$_GET['VAL_OPT'];
		$VAL__		=	strtoupper($_GET['VAL__']);
		
		$sel_ia		=	"SELECT IATRANSNO FROM WMS_NEW.CONFIRMDELIVERY_HDR WHERE DOCNO = '{$VAL__}' AND DOCTYPE = '{$VAL_OPT}' ";
		$rssel_ia	=	$Filstar_conn->Execute($sel_ia);
		if ($rssel_ia==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		if (!empty($rssel_ia->fields['IATRANSNO'])) 
		{
			echo $rssel_ia->fields['IATRANSNO'];
		}
		else 
		{
			echo "none";
		}
		exit();
	}
	
	if ($action=='SEARCHCUST')
	{
		$custcode	=	$_GET['CUSTCODE'];
		$custname	=	$_GET['CUSTNAME'];
		$sel	 =	"SELECT CustNo,CustName from custmast where 1 ";
		if (!empty($custcode)) 
		{
		$sel	.=	"AND CustNo like '%{$custcode}%' ";
		}
		if(!empty($custname))
		{
		$sel	.=	"AND CustName like '%{$custname}%' ";
		}
		$sel	.=	"AND CustStatus = 'A' AND CustomerBranchCode != '' ";
		$sel	.=	" limit 20 ";
		$rssel	=	$Filstar_conn->Execute($sel);
		if ($rssel == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;
			exit();
		}
		$cnt	=	$rssel->RecordCount();
		if ($cnt > 0)
		{
			echo "<select id=\"selcust\" onkeypress=\"smartsel(event);\" multiple>";
			while (!$rssel->EOF)
			{
				$custno		=	$rssel->fields['CustNo'];
				$custname	=	$rssel->fields['CustName'];
				$cValue		=	$custno."|".$custname;
				$show		=	$custno."-".$custname;
				echo "<option value=\"$cValue\" onclick=\"smartsel('click');\">".$show."</option>";
				$rssel->MoveNext();
			}
			echo "</select>";
		}
		else
		{
			echo "zero";
		}
		exit();
	}
	
	
	if ($action=='SEARCHCUST2')
	{
		$custcode	=	$_GET['CUSTCODE'];
		$custname	=	$_GET['CUSTNAME'];
		$OPT		=	$_GET['OPT'];
		$sel	 =	"SELECT CustNo,CustName from custmast where 1 ";
		if (!empty($custcode)) 
		{
		$sel	.=	"AND CustNo like '%{$custcode}%' ";
		}
		if(!empty($custname))
		{
		$sel	.=	"AND CustName like '%{$custname}%' ";
		}
		if ($OPT == 'DR')
		{
		$sel	.=	"AND CustStatus = 'A' and SUBSTRING(CustNo,7) = 'C' ";
		}
		else if ($OPT == 'INVOICE')
		{
		$sel	.=	"AND CustStatus = 'A' and SUBSTRING(CustNo,7) = 'O' ";	
		}
		else if ($OPT == 'STF')
		{
		$sel	.=	"AND CustStatus = 'A' and SUBSTRING(CustNo,7) = 'C' ";	
		}
		else 
		{
		$sel	.=	"AND CustStatus = 'A' and LENGTH(CustNo) = '7' ";
		}
		$sel	.=	" limit 20 ";
		$rssel	=	$Filstar_conn->Execute($sel);
		if ($rssel == false)
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;
			exit();
		}
		$cnt	=	$rssel->RecordCount();
		if ($cnt > 0)
		{
			echo "<select id=\"selcust2\" onkeypress=\"smartsel2(event);\" multiple>";
			while (!$rssel->EOF)
			{
				$custno		=	$rssel->fields['CustNo'];
				$custname	=	$rssel->fields['CustName'];
				$cValue		=	$custno."|".$custname;
				$show		=	$custno."-".$custname;
				echo "<option value=\"$cValue\" onclick=\"smartsel2('click');\">".$show."</option>";
				$rssel->MoveNext();
			}
			echo "</select>";
		}
		else
		{
			echo "zero";
		}
		exit();
	}
	
	if ($action=='CHECK_IA') 
	{
		$REFNO		=	$_GET['TEXTREFNO'];
		$CUSTCODE	=	$_GET['CUSTOMERCODE'];
		
		$sel_cnt	=	"SELECT IATRANSNO FROM WMS_NEW.INVENTORYADJUSTMENT_HDR WHERE IATRANSNO = '{$REFNO}' and	CUSTNO = '{$CUSTCODE}' AND STATUS = 'POSTED' ";
		$rssel_cnt	=	$Filstar_conn->Execute($sel_cnt);
		if ($rssel_cnt==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		if ($rssel_cnt->RecordCount() > 0) 
		{
			echo "FOUND";
		}
		else 
		{
			echo "NOTFOUND";
		}
		exit();
	}
	
	if ($action=='CHECK_MRR') 
	{
		$REFNO		=	$_GET['TEXTREFNO'];
		$CUSTCODE	=	$_GET['CUSTOMERCODE'];
		
		$sel_cnt	=	"SELECT mhmrnum FROM FDCRMSlive.mrrheader WHERE mhmrnum = '{$REFNO}' and mhsupcd = '{$CUSTCODE}' AND mhmrsts = 'C' ";
		$rssel_cnt	=	$Filstar_conn->Execute($sel_cnt);
		if ($rssel_cnt==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		if ($rssel_cnt->RecordCount() > 0) 
		{
			echo "FOUND";
		}
		else 
		{
			echo "NOTFOUND";
		}
		exit();
	}
	
	
	if ($action == 'TRUNCATE') 
	{
		$Truncate	=	"TRUNCATE WMS_NEW.$cTmpTable ";
		$rsTruncate	=	$Filstar_conn->Execute($Truncate);
		if ($rsTruncate == false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;
			die();
		}
	}
	
	
	function	 REMARKS_LOOKUP($conn)
	{
		$sel_	=	"SELECT  CODE,DESCRIPTION FROM WMS_NEW.DELIVERY_REMARKS WHERE 1 ";
		$rssel_	=	$conn->Execute($sel_);
		if ($rssel_==false) 
		{
			echo $conn->ErrorMsg()."::".__LINE__;exit();
		}
		
		$retval	 =	"<select id='remarks' name='remarks' style='font-size:12px;' >";
		$retval	.=	"<option value='none'>NONE</option>";
		while (!$rssel_->EOF) 
		{
			$code	=	$rssel_->fields['CODE'];
			$desc	=	$rssel_->fields['DESCRIPTION'];
			
			$retval	.=	"<option value='{$code}'>$desc</option>";
			
			$rssel_->MoveNext();
		}
		$retval	.=	"</option>";
		return $retval;
	}
?>
<html>
<title>SKU SUMMARY</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../css/style.css);</style>
<style type="text/css">@import url(../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../calendar/calendar-setup.js"></script>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<style type="text/css">
body {
	font-size: 62.5%;
	font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
}

table {
	font-size: 1em;
}

.no-close .ui-dialog-titlebar-close {
    display: none;
}

.Text_header_value
{
	font:15px Dejavu Sans, arial, helvetica, sans-serif;
	font-weight:bold;
}

</style>
</head>
<body onload="truncate();">
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0" class="Text_header" style="border:0px">
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					TRANSACTION NO. 
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<input type="text" name="txttransno" id="txttransno" value="">
				</td>
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					REFERENCE NO.
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<input type="text" name="txtrefno" id="txtrefno" value="" >
				</td>
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					CUSTOMER
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<input type="text" name="customercode" id="customercode" value="" onkeyup="searchcust(event);" autocomplete="off" size="9">
				 	<input type="text" name="customername" id="customername" value="" onkeyup="searchcust(event);" autocomplete="off" size="40">
				 	<div id="divcust" style="position:absolute;"></div>
					<input type="hidden" id="hdnval" name="hdnval" value="">
				</td>
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					REFERENCE TYPE
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<select name="selrefno" id="selrefno">
						<option value="INVOICE">INVOICE</option>
						<option value="STF">STF</option>
						<option value="DR">DR</option>
						<option value="PRR">PRR</option>
						<option value="IA">I.A. NO</option>
						<option value="MRR">MRR</option>
					</select>
				</td>
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					TRANSACTION DATE
				</td> 
				<td align="left" width="57%" style="font-size:15px">
					:<input type="text" name="dfrom" id="dfrom" 	class="dates" 	value="" size="10" placeholder="FROM" style="text-align:center;" >&nbsp;
				 	<input type="text" name="dto" 	id="dto" 	class="dates"	value="" size="10" placeholder="TO" style="text-align:center;" >
				</td>
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					STATUS
				</td>
				<td align="left" width="57%" colspan="3" style="font-size:15px">
					:<select name="selstatus" id="selstatus">
						<option value="ALL">--ALL--</option>
						<option value="IN-PROCESS">IN-PROCESS</option>
						<option value="POSTED">POSTED</option>
					</select>
				</td>
				
			</tr>
			<tr>
				<td width="100%" colspan="3" align="center">
					<input type="button" name="btnsearch" id="btnsearch" value="SEARCH" class="small_button" onclick="DISPLAY();"  style='width:120px;height:35px;'>
					<input type="button" name="btncreate" id="btncreate" value="CREATE" class="small_button" onclick="CREATE();"  style='width:120px;height:35px;'>
				</td>
			</tr>
		</table>
		</div>
		<div style="display:none;" id="divINVOICE">
		<table width="100%" border="0" class="Text_header" style="border:0px">
			<tr>
				<td align="right" width="45%" style="font-size:20px">
					INVOICE&nbsp;
				</td>
				<td align="left" width="55%" colspan="3" style="font-size:20px">
					:<input type="text" name="txtINVOICE" id="txtINVOICE" value="" onkeyup="isnumeric_(this.value,this.id);GET_DATA(event,this.id,this.value);" style="font-size:20px" size="10">
				</td>
			</tr>
		</table>
		</div>
		<div id="divdata"></div>
		<div id="divdata_detail" title="ITEM LIST"></div>
		<div id="divdata_add" title="INVENTORY ADJUSTMENT" style="display:none;">
		<div id="divdata_add_hdr" style="display:none;">
			<table width="100%" border="0" class="Text_header" style="border:0px;">
				<tr>
					<td align="left" width="20%" style="font-size:15px">
						I.A. DATE
					</td>
					<td align="left" width="80%" style="font-size:15px">
						:&nbsp;<?php echo date('Y-m-d'); ?><input type="hidden" name="hdniadate" id="hdniadate" value="<?php echo date('Y-m-d'); ?>" maxlength="10" size="10">
					</td>
				</tr>
				<tr>
					<td align="left" width="20%" style="font-size:15px">
						REF NO.
					</td>
					<td align="left" width="80%" style="font-size:15px">
						:&nbsp;<input type="text" name="textrefno" id="textrefno" value="" maxlength="10" size="10">
						<input type="radio" name="rdreftype" id="rdinvoice" value="Invoice" checked>INVOICE
						<input type="radio" name="rdreftype" id="rdstf" value="STF" >STF
						<input type="radio" name="rdreftype" id="rdprr" value="PRR" >PRR
						<input type="radio" name="rdreftype" id="rddr" value="DR" >DR
						<input type="radio" name="rdreftype" id="rdia" value="IA" >I.A NO.
						<input type="radio" name="rdreftype" id="rdmrr" value="MRR" >MRR
					</td>
				</tr>
				<tr>
					<td align="left" width="20%" style="font-size:15px">
						CUSTOMER
					</td>
					<td align="left" width="80%" style="font-size:15px">
						:&nbsp;<input type="text" name="customercode2" id="customercode2" value="" onkeyup="searchcust2(event);" autocomplete="off" size="10">
					 	<input type="text" name="customername2" id="customername2" value="" onkeyup="searchcust2(event);" autocomplete="off" size="40">
					 	<div id="divcust2" style="position:absolute;"></div>
						<input type="hidden" id="hdnval2" name="hdnval2" value="">
					</td>
				</tr>
				<tr>
					<td align="left" width="20%" style="font-size:15px">
						LOCATION
					</td>
					<td align="left" width="80%" style="font-size:15px">
						:&nbsp;
						<input type="radio" name="rdlocation" id="rdfdc" value="FDC" checked>FDC
						<input type="radio" name="rdlocation" id="rdraw" value="RAW" >RAW
					</td>
				</tr>
				<tr>
					<td align="left" width="20%" style="font-size:15px">
						IA TYPE
					</td>
					<td align="left" width="80%" style="font-size:15px">
						:&nbsp;
						<input type="radio" name="rdiatype" id="rdonhand" value="ONHAND" checked>ONHAND
						<input type="radio" name="rdiatype" id="rdpending" value="PENDING" >PENDING
					</td>
				</tr>
				<tr>
					<td align="left" width="20%" style="font-size:15px">
						REASON
					</td>
					<td align="left" width="80%" style="font-size:15px">
						:&nbsp;<?php echo REMARKS_LOOKUP($Filstar_conn);?>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<input type="button" name="btnsubmit_add" id="btnsubmit_add" value="SUBMIT" onclick="SUBMIT_HRD();">
					</td>
				</tr>
			</table>
		</div>	
		<div id="divdata_add_items" style="display:none;">
			<table width="100%" border="0" class="Text_header" style="border:0px;">
				<tr>
					<td align="left" width="20%" style="font-size:15px">
						ADJUSTMENT
					</td>
					<td align="left" width="80%" style="font-size:15px">
						:&nbsp;
						<input type="radio" name="rdoperation" id="rdincrease" value="INCREASE" checked>INCREASE
						<input type="radio" name="rdoperation" id="rddecrease" value="DECREASE" >DECREASE
						<input type="hidden" name="hdnresponse" id="hdnresponse" value="">
					</td>
				</tr>
				<tr>
					<td align="left" width="20%" style="font-size:15px">
						QUANTITY
					</td>
					<td align="left" width="80%" style="font-size:15px">
						:&nbsp;<input type="text" name="txtqty" id="txtqty" value="" onkeyup="isnumeric_(this.value,this.id);" maxlength="5">
					</td>
				</tr>
				<tr>
					<td align="left" width="20%" style="font-size:15px">
						SKUNO
					</td>
					<td align="left" width="80%" style="font-size:15px">
						:&nbsp;<input type="text" name="txtsku" id="txtsku" value="" onkeyup="save_item(event,this.value);">
					</td>
				</tr>
				
			</table>
		</div>
		<div id="div_item"></div>
		</div>
		<div id="divloader" style="display:none;" align="center" title="LOADING"><img src="../../images/loading/ajax-loader_fast.gif"></div>
		
		<div id="divedit_item" style="display:none;" align="center" title="UPDATE SKUNO">
			<table width="100%" border="0" class="Text_header">
				<tr>
					<td width="20%">
						SKUNO
					</td>
					<td width="80%">
						:&nbsp;<input type="text" name="txtedit_sku" id="txtedit_sku" value="" readonly size="20">
					</td>
				</tr>
				<tr>
					<td width="20%">
						QUANTITY
					</td>				
					<td width="80%">
						:&nbsp;<input type="text" name="txtedit_qty" id="txtedit_qty" value="" size="20" onkeyup="isnumeric_(this.value,this.id);">
					</td>
				</tr>
				<input type="hidden" name="txtedit_iam" id="txtedit_iam" value="" size="20" readonly>
			</table>
		</div>
		
		<div id="divmsg" style="display:none;" align="center" title="ALERT"></div>
	</form>
</body>
</html>
<script>
$(".dates").datepicker({ 
	dateFormat: 'yy-mm-dd',
	changeMonth: true,
    changeYear: true 
});

function	truncate()
{
	$.ajax({
			url		:	'adjustment.php?action=TRUNCATE'
	});
}


function DISPLAY()
{
	var	dfrom	=	$('#dfrom').val();
	var	dto		=	$('#dto').val();
	
	if(dfrom == '' || dto == '')
	{
		alert('Invalid transaction date!');
		return;
	}
	if(dfrom > dto)
	{
		alert('Invalid transaction date!');
		return;
	}
	
	var	dataform	=	$('#dataform').serialize();
	
	$.ajax({
			data		:	dataform,
			type		:	'POST',
			url			:	'adjustment.php?action=DISPLAY_&DFROM='+dfrom+'&DTO='+dto,
			beforeSend	:	function()
						{
							$('#divloader').dialog('open');
						},
			success		:	function(response)
						{
							$('#divloader').dialog('close');
							$('#divdata').dialog('open');
							$('#divdata').html(response);
						}
	});
}

function CREATE()
{
	var isconfirm	=	confirm("Create IA transaction");
	if(isconfirm==true)
	{
		$('#divdata_add').dialog("open");
		$('#divdata_add_hdr').show();
	}
}

function SUBMIT_HRD()
{
	var customer	=	$('#hdnval2').val();
	var customercode=	$('#customercode2').val();
	var textrefno	=	$('#textrefno').val();
	var remarks		=	$('#remarks').val();
	var Itemno		=	$('#textitemno').val();
	var	opt			=	"";
	
	var	invoiceno	=	$('#rdinvoice').is(':checked');
	var	rdstf		=	$('#rdstf').is(':checked');
	var	rdprr		=	$('#rdprr').is(':checked');
	var	rddr		=	$('#rddr').is(':checked');
	var	rdia		=	$('#rddia').is(':checked');
	var	rdmrr		=	$('#rdmrr').is(':checked');
	if(invoiceno==true)
	{
		var	opt		=	"INVOICE";
	}
	
	if(rdstf==true)
	{
		var opt		=	"STF";
	}
	
	if(rdprr==true)
	{
		var opt		=	"PRR";
	}
	
	if(rddr==true)
	{
		var opt		=	"DR";
	}
	
	if(rdia==true)
	{
		var opt		=	"IA";
	}
	
	if(rdmrr==true)
	{
		var opt		=	"MRR";
	}
	
	if(customer	== '')
	{
		alert('Invalid Customer!');
		return;
	}
	
	if(textrefno == '')
	{
		alert('Please insert reference no.');
		return;
	}
	
	
	if(rdia==true)
	{
		$a.ajax({
				url			:	'adjustment.php?action=CHECK_IA&TEXTREFNO='+textrefno+'&CUSTOMERCODE='+customercode,
				beforeSend	:	function()
							{
								$('#divloader').dialog('open');
							},
				success		:	function(response)
							{
								if(response=='NOTFOUND')
								{
									$('#divmsg').html('NO RECORD FOUND');
									$('#divmsg').dialog('open');
								}
								else
								{
									$('#divdata_add_hdr').hide();
									$('#divdata_add_items').show();
									$('#txtqty').focus();
								}
							}
		});
	}
	else if(rdmrr==true)
	{
		$a.ajax({
				url			:	'adjustment.php?action=CHECK_MRR&TEXTREFNO='+textrefno+'&CUSTOMERCODE='+customercode,
				beforeSend	:	function()
							{
								$('#divloader').dialog('open');
							},
				success		:	function(response)
							{
								if(response=='NOTFOUND')
								{
									$('#divmsg').html('NO RECORD FOUND');
									$('#divmsg').dialog('open');
								}
								else
								{
									$('#divdata_add_hdr').hide();
									$('#divdata_add_items').show();
									$('#txtqty').focus();
								}
							}
		});
	}
	else
	{
		$('#divdata_add_hdr').hide();
		$('#divdata_add_items').show();
		$('#txtqty').focus();
		
		
		if(opt == 'DR')
		{
			$('#rdincrease').attr('checked', false);
			$('#rdincrease').attr('disabled', true);
			$('#rddecrease').attr('checked', true);
		}
	}
}


function save_item(evt,this_val)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	var qty__		=	$('#txtqty').val();
	if(evthandler == 13)
	{
		if(this_val!='' && qty__ !='')
		{
			var customercode=	$('#customercode2').val();
			var textrefno	=	$('#textrefno').val();
			var remarks		=	$('#remarks').val();
			var qty			=	$('#txtqty').val();
			
			var	opt1		=	$('#rdinvoice').is(":checked");
			var	opt2		=	$('#rdstf').is(":checked");
			var	opt3		=	$('#rdprr').is(":checked");
			var	opt10		=	$('#rddr').is(":checked");
			var	opt11		=	$('#rdia').is(":checked");
			var	opt12		=	$('#rdmrr').is(":checked");
			
			var	opt4		=	$('#rdfdc').is(":checked");
			var	opt5		=	$('#rdraw').is(":checked");
			
			var opt6		=	$('#rdonhand').is(":checked");
			var opt7		=	$('#rdpending').is(":checked");
			
			var	opt8		=	$('#rdincrease').is(":checked");
			var	opt9		=	$('#rddecrease').is(":checked");
			
			
			
			if(opt1==true)
			{
				var	opt__	=	"INVOICE";
			}
			else if(opt2==true)
			{
				var	opt__	=	"STF";
			}
			else if(opt3==true)
			{
				var opt__	=	"PRR";
			}
			else if(opt10==true)
			{
				var opt__	=	"DR";
			}
			else if(opt11=true)
			{
				var opt__	=	"IA";
			}
			else if(opt12=true)
			{
				var opt__	=	"MRR";
			}
			
			
			
			if(opt4==true)
			{
				var this_location	=	"FDC";
			}
			else if(opt5==true)
			{
				var this_location	=	"RAW";
			}
			
			
			if(opt6==true)
			{
				var this_iatype	=	"ONHAND";
			}
			else if(opt7==true)
			{
				var this_iatype	=	"PENDING";
			}
			
			if(opt8==true)
			{
				var this_operation	=	"INCREASE";
			}
			else if(opt9==true)
			{
				var this_operation	=	"DECREASE";
			}
			
			$.ajax({
					url			:	'adjustment.php?action=SAVE_TMP&CUSTOMERCODE='+customercode+'&TEXTREFNO='+textrefno+'&REMARKS='+remarks+'&QTY='+qty+'&ITEMNO='+this_val+'&opt__='+opt__+'&THIS_LOCATION='+this_location+'&THIS_IATYPE='+this_iatype+'&THIS_OPERATION='+this_operation,
					beforeSend	:	function()
									{
										$('#divloader').dialog('open');
									},
					success		:	function(response)
									{
										$('#divloader').dialog('close');
										$('#div_item').html(response);
										$('#div_item').show();
										$('#txtqty').val('');
										$('#txtsku').val('');
										$('#txtqty').focus();
									}
			});
		}
		else
		{
			alert('Invalid Skuno!');
		}
	}
}

function delete_this(val_id)
{
	var isSubmit	=	confirm("Are sure you want to delete this item?");
	if(isSubmit==true)
	{
		
		var	opt1		=	$('#rdinvoice').is(":checked");
		var	opt2		=	$('#rdstf').is(":checked");
		var	opt3		=	$('#rdprr').is(":checked");
		
		var	opt4		=	$('#rdfdc').is(":checked");
		var	opt5		=	$('#rdraw').is(":checked");
		
		var opt6		=	$('#rdonhand').is(":checked");
		var opt7		=	$('#rdpending').is(":checked");
		
		var	opt8		=	$('#rdincrease').is(":checked");
		var	opt9		=	$('#rddecrease').is(":checked");
		
		if(opt1==true)
		{
			var	opt__	=	"INVOICE";
		}
		else if(opt2==true)
		{
			var	opt__	=	"STF";
		}
		else if(opt3==true)
		{
			var opt__	=	"PRR";
		}
		
		if(opt4==true)
		{
			var this_location	=	"FDC";
		}
		else if(opt5==true)
		{
			var this_location	=	"RAW";
		}
		
		
		if(opt6==true)
		{
			var this_iatype	=	"ONHAND";
		}
		else if(opt7==true)
		{
			var this_iatype	=	"PENDING";
		}
		
		if(opt8==true)
		{
			var this_operation	=	"INCREASE";
		}
		else if(opt9==true)
		{
			var this_operation	=	"DECREASE";
		}
		
		$.ajax({
				url			:	'adjustment.php?action=DELETE_ITEM&ID_='+val_id+'&THIS_LOCATION='+this_location+'&THIS_IATYPE'+this_iatype+'&THIS_OPERATION='+this_operation,
				beforeSend	:	function()
							{
								$('#divloader').dialog('open');
							},
				success		:	function(response)
							{
								$('#divloader').dialog('close');
								$('#div_item').html(response);
								$('#div_item').show();
								$('#txtqty').val('');
								$('#txtsku').val('');
								$('#txtqty').focus();
							}
		});
	}
}


function submit_iam()
{
	var isSubmit	=	confirm("Are you sure you want to create transaction?");
	if(isSubmit==true)
	{
		
		var customer	=	$('#hdnval2').val();
		var customercode=	$('#customercode2').val();
		var textrefno	=	$('#textrefno').val();
		var remarks		=	$('#remarks').val();
		var Itemno		=	$('#textitemno').val();
		var	opt			=	"";
		
		var	opt1		=	$('#rdinvoice').is(":checked");
		var	opt2		=	$('#rdstf').is(":checked");
		var	opt3		=	$('#rdprr').is(":checked");
		var	opt10		=	$('#rddr').is(":checked");
		var	opt11		=	$('#rdia').is(":checked");
		var	opt12		=	$('#rdmrr').is(":checked");
		
		var	opt4		=	$('#rdfdc').is(":checked");
		var	opt5		=	$('#rdraw').is(":checked");
		
		var opt6		=	$('#rdonhand').is(":checked");
		var opt7		=	$('#rdpending').is(":checked");
		
		var	opt8		=	$('#rdincrease').is(":checked");
		var	opt9		=	$('#rddecrease').is(":checked");
		
		if(opt1==true)
		{
			var	opt__	=	"INVOICE";
		}
		else if(opt2==true)
		{
			var	opt__	=	"STF";
		}
		else if(opt3==true)
		{
			var opt__	=	"PRR";
		}
		else if(opt10==true)
		{
			var opt__	=	"DR";
		}
		else if(opt11==true)
		{
			var opt__	=	"IA";
		}
		else if(opt12==true)
		{
			var opt__	=	"MRR";
		}
		
		
		if(opt4==true)
		{
			var this_location	=	"FDC";
		}
		else if(opt5==true)
		{
			var this_location	=	"RAW";
		}
		
		
		if(opt6==true)
		{
			var this_iatype	=	"ONHAND";
		}
		else if(opt7==true)
		{
			var this_iatype	=	"PENDING";
		}
		
//		if(opt8==true)
//		{
//			var this_operation	=	"INCREASE";
//		}
//		else if(opt9==true)
//		{
//			var this_operation	=	"DECREASE";
//		}
		
		
		$.ajax({
				//url			:	'adjustment.php?action=SUBMIT_IAM&CUSTOMERCODE='+customercode+'&TEXTREFNO='+textrefno+'&REMARKS='+remarks+'&OPT__='+opt__+'&THIS_LOCATION='+this_location+'&THIS_IATYPE='+this_iatype+'&THIS_OPERATION='+this_operation,
				url			:	'adjustment.php?action=SUBMIT_IAM&CUSTOMERCODE='+customercode+'&TEXTREFNO='+textrefno+'&REMARKS='+remarks+'&OPT__='+opt__+'&THIS_LOCATION='+this_location+'&THIS_IATYPE='+this_iatype,
				beforeSend	:	function()
							{
								$('#divloader').dialog('open');
							},
				success		:	function(response)
							{
								$('#divloader').dialog('close');
								$('#hdnresponse').val(response);
								var vx = $('#hdnresponse').val();
								var x = vx.split('|');
								if(x[0] == 'done')
								{
									alert('Manual I.A was successfully created confirmed your I.A No.'+x[1]);	
									location='index.php';
								}
								else
								{
									alert(response);
								}
								
							}
		});
	}
}


function Get_item()
{
	var customer	=	$('#hdnval2').val();
	var customercode=	$('#customercode2').val();
	var textrefno	=	$('#textrefno').val();
	var remarks		=	$('#remarks').val();
	var Itemno		=	$('#textitemno').val();
	var	opt			=	"";
	
	var	invoiceno	=	$('#rdinvoice').is(':checked');
	var	rdstf		=	$('#rdstf').is(':checked');
	var	rdprr		=	$('#rdprr').is(':checked');
	if(invoiceno==true)
	{
		var	opt		=	"INVOICE";
	}
	
	if(rdstf==true)
	{
		var opt		=	"STF";
	}
	
	if(rdprr==true)
	{
		var opt		=	"PRR";
	}
	
	if(customer	== '')
	{
		alert('Invalid Customer!');
		return;
	}
	
	if(textrefno == '')
	{
		alert('Please insert reference no.');
		return;
	}
	
	if(Itemno != '')
	{
		$.ajax({
				url			:	'adjustment.php?action=GETITEM&ITEMNO='+Itemno+'&CUSTOMERCODE='+customercode+'&TEXTREFNO='+textrefno+'&REMARKS='+remarks+'&OPT='+opt,
				beforeSend	:	function()
							{
								$('#divloader').dialog('open');
							},
				success		:	function(response)
							{
								$('#divloader').dialog('close');
								$('#div_item').html(response);
								$('#div_item').show();
							}
		})
	}
}


function	submit_ia()
{
	var customercode=	$('#customercode2').val();
	var textrefno	=	$('#textrefno').val();
	var remarks		=	$('#remarks').val();
	var Itemno		=	$('#textitemno').val();
	var	opt			=	"";
	var	invoiceno	=	$('#rdinvoice').is(':checked');
	var	rdstf		=	$('#rdstf').is(':checked');
	var	rdprr		=	$('#rdprr').is(':checked');
	if(invoiceno==true)
	{
		var	opt		=	"INVOICE";
	}
	
	if(rdstf==true)
	{
		var opt		=	"STF";
	}
	
	if(rdprr==true)
	{
		var opt		=	"PRR";
	}
	
	var	penqty_FDC			=	$('#txtpenqty_FDC').val();
	var	penqty_RAW			=	$('#txtpenqty_RAW').val();
	var	penqty_change_FDC	=	$('#hdnpenqty_change_FDC').val();
	var	penqty_change_RAW	=	$('#hdnpenqty_change_RAW').val();
	
	var	onhqty_RAW			=	$('#txtonhqty_RAW').val();
	var	onhqty_FDC			=	$('#txtonhqty_FDC').val();
	var	onhqty_change_RAW	=	$('#hdnonhqty_change_RAW').val();
	var	onhqty_change_FDC	=	$('#hdnonhqty_change_FDC').val();
	
	if(penqty_change_FDC == 'NO' && penqty_change_RAW == 'NO' && onhqty_change_RAW == 'NO' && onhqty_change_FDC == 'NO')
	{
		alert('NO Inventory adjustment found!');
	}
	else
	{
		
		$.ajax({
				url			:	'adjustment.php?action=MANUAL_IA&CUSTOMERCODE='+customercode+'&TEXTREFNO='+textrefno+'&REMARKS='+remarks+'&ITEMNO='+Itemno+'&OPT='+opt+'&PENQTY_CHANGE_FDC='+penqty_change_FDC+'&PENQTY_CHANGE_RAW='+penqty_change_RAW+'&PENQTY_FDC='+penqty_FDC+'&PENQTY_RAW='+penqty_RAW+'&ONHQTY_CHANGE_RAW='+onhqty_change_RAW+'&ONHQTY_CHANGE_FDC='+onhqty_change_FDC+'&ONHQTY_RAW='+onhqty_RAW+'&ONHQTY_FDC='+onhqty_FDC,
				beforeSend	:	function()
							{
								$('#divloader').dialog('open');
							},
				success		:	function(response)
							{
								$('#divloader').dialog('close');
								if(response=='done')
								{
									Get_IA(opt,textrefno);
								}
								else
								{
									alert(response);
								}
							}
		})
	}
}


function	EDIT_this(val_trx,val_sku,val_qty)
{
	var isSubmit	=	confirm('Are you sure you want to update item '+val_sku+'?');
	if(isSubmit==true)
	{
		$('#txtedit_sku').val(val_sku);
		$('#txtedit_qty').val(val_qty);
		$('#txtedit_iam').val(val_trx);
		$('#divedit_item').dialog('open');
	}
}

function	DELETE_this(val_trx,val_sku)
{
	var isSubmit	=	confirm('Are you sure you want to remove item '+val_sku+'?');
	if(isSubmit==true)
	{
		$.ajax({
				url			:	'adjustment.php?action=DELETE_ITEM_EDIT&VAL_TRX='+val_trx+'&VAL_SKU='+val_sku,
				beforeSend	:	function()
							{
								$('#divloader').dialog('open');
							},
				success		:	function(response)
							{
								$('#divloader').dialog('close');
								if(response=='done')
								{
									alert('Item was successfully deleted');
									display_item(val_trx);
								}
								else if(response=='ONLY1')
								{
									$('#divmsg').dialog('open');
									$('#divmsg').html('Unable to delete all items!');
								}
								else
								{
									alert(response);
								}
							}
				
		});
	}
}


function	PRINT_this(val_trx)
{
	window.open('adjustment_inventory_pdf.php?actio=PDF&VAL_TRX='+val_trx);
}


function	Get_IA(val_opt,val__)
{
	$.ajax({
			url		:	'adjustment.php?action=GET_IA&VAL_OPT='+val_opt+'&VAL__='+val__,
			success	:	function(response)
					{
						if(response != 'none')
						{
							alert('Manual I.A was successfully created confirmed your I.A No.'+response);	
							location='index.php';
						}
						else
						{
							alert(response);
						}
					}
	});
}

 
function searchcust(evt)
{
	var custcode	=	$('#customercode').val();
	var custname	=	$('#customername').val();
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;

	if(custcode != '' || custname != '')
	{
		if(evthandler != 40 && evthandler != 13 && evthandler != 27)
		{
			$.ajax({
				url			:	'adjustment.php?action=SEARCHCUST&CUSTCODE='+custcode+'&CUSTNAME='+custname,
				success		:	function(response)
				{
					if(response == 'zero')
					{
						alert('No record found...');
						$('#divcust').html('');
						$('#customercode').val('');
						$('#customername').val('');
					}
					else
					{
						$('#divcust').html(response);
						$('#divcust').show();
					}
				}
			});
		}
		else if(evthandler == 40 && $('#divcust').html() != '')
		{
			$('#selcust').focus();
		}
		else
		{
			$('#divcust').html('');
		}
	}
	else
	{
		$('#divcust').html('');
		$('#customercode').val('');
		$('#customername').val('');
	}

}

function	smartsel(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnval').val($('#selcust').val());
		var vx = $('#hdnval').val();
		var x = vx.split('|');
		$('#customercode').val(x[0]);
		$('#customername').val(x[1]);
		$('#divcust').html('');
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnval').val($('#selcust').val());
			var vx = $('#hdnval').val();
			var x = vx.split('|');
			$('#customercode').val(x[0]);
			$('#customername').val(x[1]);
			$('#divcust').html('');
		}
	}
}

function searchcust2(evt)
{
	var custcode	=	$('#customercode2').val();
	var custname	=	$('#customername2').val();
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	
	var	invoiceno	=	$('#rdinvoice').is(':checked');
	var	rdstf		=	$('#rdstf').is(':checked');
	var	rdprr		=	$('#rdprr').is(':checked');
	var	rddr		=	$('#rddr').is(':checked');
	if(invoiceno==true)
	{
		var	opt		=	"INVOICE";
	}
	
	if(rdstf==true)
	{
		var opt		=	"STF";
	}
	
	if(rdprr==true)
	{
		var opt		=	"PRR";
	}
	
	if(rddr==true)
	{
		var opt		=	"DR";
	}

	if(custcode != '' || custname != '')
	{
		if(evthandler != 40 && evthandler != 13 && evthandler != 27)
		{
			$.ajax({
				url			:	'adjustment.php?action=SEARCHCUST2&CUSTCODE='+custcode+'&CUSTNAME='+custname+'&OPT='+opt,
				success		:	function(response)
				{
					if(response == 'zero')
					{
						alert('No record found...');
						$('#divcust2').html('');
						$('#customercode2').val('');
						$('#customername2').val('');
					}
					else
					{
						$('#divcust2').html(response);
						$('#divcust2').show();
					}
				}
			});
		}
		else if(evthandler == 40 && $('#divcust2').html() != '')
		{
			$('#selcust2').focus();
		}
		else
		{
			$('#divcust2').html('');
		}
	}
	else
	{
		$('#divcust2').html('');
		$('#customercode2').val('');
		$('#customername2').val('');
	}

}


function	smartsel2(evt)
{
	var evthandler	=	(evt.charCode) ? evt.charCode : evt.keyCode;
	if(evt == 'click')
	{
		$('#hdnval2').val($('#selcust2').val());
		var vx = $('#hdnval2').val();
		var x = vx.split('|');
		$('#customercode2').val(x[0]);
		$('#customername2').val(x[1]);
		$('#divcust2').html('');
		//$('#textrefno').focus();
	}
	else
	{
		if(evthandler == 13)
		{
			$('#hdnval2').val($('#selcust2').val());
			var vx = $('#hdnval2').val();
			var x = vx.split('|');
			$('#customercode2').val(x[0]);
			$('#customername2').val(x[1]);
			$('#divcust2').html('');
			//$('#textrefno').focus();
		}
	}
}




function refno_enable(val__)
{
	var	enable_val	=	val__;
	if(enable_val != '')
	{
		$('#selrefno').attr('disabled', false)
	}
	else
	{
		$('#selrefno').attr('disabled', true)
	}
}


function	display_item(val_iano)
{
	$.ajax({
			url			:	'adjustment.php?action=ITEM_LIST&VAL_IANO='+val_iano,
			beforeSend	:	function()
						{
							$('#divloader').dialog('open');
						},
			success		:	function(response)
						{
							$('#divloader').dialog('close');
							$('#divdata_detail').dialog('open');
							$('#divdata_detail').html(response);
						}
	});
}

	
$("#divloader").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	bgiframe:true, resizable:false, height: 100, width: 170, modal:true, autoOpen: false,	draggable: false
});

$("#divmsg").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 100, width: 200, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		}
	}
});

$("#divedit_item").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	bgiframe:true, resizable:false, height: 200, width: 300, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		},
		'UPDATE': function()
		{
			var	skuno__	=	$('#txtedit_sku').val();
			var	qty__	=	$('#txtedit_qty').val();
			var	iam__	=	$('#txtedit_iam').val();
			
			if(qty__ != '')
			{
				$.ajax({
						url			:	'adjustment.php?action=UPDATE_ITEM&SKUNO__='+skuno__+'&QTY__='+qty__+'&IAM__='+iam__,
						beforeSend	:	function()
									{
										$('#divloader').dialog('open');
									},
						success		:	function(response)
									{
										$('#divloader').dialog('close');
										if(response=='done')
										{
											alert('Item was successfully updated!');
											$(this).dialog('close');
											display_item(iam__);
										}
										else
										{
											alert(response);
										}
										
									}
				});
			}
			
			$(this).dialog('close');
		}
	}
});


$("#divdata_detail").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 500, width: 900, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			$(this).dialog('close');
		},
		'SUBMIT': function()
		{
			var	this_iano	=	$('#hdniano').val();
			var isconfirm	=	confirm('Are you sure you want to post this transaction?');
			if(isconfirm==true)
			{
				$.ajax({
						url			:	'adjustment.php?action=POSTING&THIS_IANO='+this_iano,
						beforeSend	:	function()
									{
										$('#divloader').dialog('open');
									},
						success	:	function(response)
									{
										$('#divloader').dialog('close');
										if(response=='done')
										{
											$('#divmsg').dialog('open');
											$('#divmsg').html('Transaction was successfully posted');
											DISPLAY();
											$('#divdata_detail').dialog('close');
										}
										else
										{
											$('#divmsg').dialog('open');
											$('#divmsg').html(response);
										}
									}
				});
			}
		}
	}
});


$("#divdata_add").dialog({
	dialogClass: "no-close",
	closeOnEscape: false,
	bgiframe:true, resizable:false, height: 700, width: 1200, modal:true, autoOpen: false,	draggable: false,
	buttons: {
		'CLOSE': function()
		{
			//$(this).dialog('close');
			location='index.php'
		}
	}
});

function	check_val(val__,val_type,val_type2)
{
	var penqty	=	$('#hdnpenqty_'+val_type).val();
	var onhand	=	$('#hdnonhqty_'+val_type).val();
	
	var	qty		=	$.trim(val__);
	
	if(qty != '')
	{
		if(val_type2 == 'PENDING')
		{
			if(penqty != qty)
			{
				$('#div_btn_ia').show();
				$('#hdnpenqty_change_'+val_type).val('YES');
			}
			else
			{
				if(qty != '')
				{
					if(onhand == qty)
					{
						$('#div_btn_ia').hide();
						$('#hdnpenqty_change_'+val_type).val('NO');
					}
					if(penqty == qty)
					{
						$('#hdnpenqty_change_'+val_type).val('NO');
					}
				}
			}
		}
		else if(val_type2 == 'ONHAND')
		{
			if(onhand != qty)
			{
				$('#div_btn_ia').show();
				$('#hdnonhqty_change_'+val_type).val('YES');
			}
			else
			{
				if(qty != '')
				{
					if(penqty == qty)
					{
						$('#div_btn_ia').hide();
						$('#hdnonhqty_change_'+val_type).val('NO');
					}
					if(onhand == qty)
					{
						$('#hdnonhqty_change_'+val_type).val('NO');
					}
				}
			}
		}
	}
	else
	{
		
		if(val_type2 == 'PENDING')
		{
			$('#hdnpenqty_change_'+val_type).val('NO');
		}
		else
		{
			$('#hdnonhqty_change_'+val_type).val('NO');
		}
	}
	
}
						

function isnumeric(num,id)
{
	var ValidChars ="0123456789.";
	var IsNumber = "";
	var Char;
	
	for (var i=0; i < num.length; i++)
	{
		Char = num.charAt(i);
		if(ValidChars.indexOf(Char) != -1)
		{
			IsNumber = IsNumber + Char;
		}
	}
	document.getElementById(id).value = IsNumber;
}

function isnumeric_(num,id)
{
	var ValidChars ="0123456789";
	var IsNumber = "";
	var Char;
	
	for (var i=0; i < num.length; i++)
	{
		Char = num.charAt(i);
		if(ValidChars.indexOf(Char) != -1)
		{
			IsNumber = IsNumber + Char;
		}
	}
	document.getElementById(id).value = IsNumber;
}

function addCommas(nStr) {
    nStr += '';
    var x = nStr.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}
</script>