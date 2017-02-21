<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../index.php'</script>";
}

	$action	=	$_GET['action'];

	$action		=	$_GET['action'];
	if ($action=='DISPLAY_') 
	{
		$sku			=	$_GET['sku'];
	
		if($sku!='')
		{
			$DFROM			=	$_GET['DFROM'];
			$DTO			=	$_GET['DTO'];
			$rdoType		=	$_GET['rdoType'];
			
			$selRemarks		=	$_POST['selRemarks'];
			$customercode	=	$_POST['customercode'];
			$selDateType	=	$_POST['selDateType'];
			
			$selstatus		=	$_POST['selstatus'];
			$results = array();
			$results2 = array();
			
			if ($rdoType == "NBS")
			{
				$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE (CustomerBranchCode!='' or NBSnewBranchCode!='')";
				$rs_cust	= $Filstar_conn->Execute($qry_cust);
			}
			else 
			{
				$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE CustomerBranchCode=''";
				$rs_cust	= $Filstar_conn->Execute($qry_cust);
			}
			
			while (!$rs_cust->EOF)
			{
				$cust_no	= $rs_cust->fields['CustNo'];
				$results[] = $cust_no;
				$rs_cust->MoveNext();	
			}
				$_cust	= implode("','",$results);
				$CUST_VALID	= " AND CUSTNO in ('{$_cust}')";
				
				
			$get_trans		= "SELECT IATRANSNO,SKUNO FROM  WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE SKUNO={$sku}";
			$rsget_trans	= $Filstar_conn->Execute($get_trans);
			
			while (!$rsget_trans->EOF)
			{
				$IATRANSNO_		= $rsget_trans->fields['IATRANSNO'];
				$results2[]		= $IATRANSNO_;
				
				$rsget_trans->MoveNext();	
			}
			
			$_IATRANSNO	= implode("','",$results2);

			$get_hdr		= "SELECT `IATRANSNO`, `CUSTNO`, `STATUS`, `ADDEDDATE`, `POSTEDDATE`
									FROM WMS_NEW.INVENTORYADJUSTMENT_HDR where IATRANSNO in ('{$_IATRANSNO}') AND `{$selDateType}` between '{$DFROM}' AND '{$DTO}' $CUST_VALID";
			if (!empty($customercode))
			{
				$get_hdr	.=	" AND `CUSTNO` = '{$customercode}' ";
			}
			if ($selstatus != 'ALL')
			{
				$get_hdr	.=	" AND `STATUS` = '{$selstatus}' ";
			}
			$rsget_hdr		=	$Filstar_conn->Execute($get_hdr);		

			if($rsget_hdr->RecordCount() > 0)
			{
				$show	.=	"<form id='form_detail' name='form_detail'>";
				$show	.=	"<table width='100%' border='0'>";
				$show	.=	"<tr class='Header_style' style='font-size:15px;'>";
				$show	.=		"<td width='5%' nowrap>";
				$show	.=			"LINENO";
				$show	.=		"</td>";
				$show	.=				"<td width='15%' align='center' nowrap>";
				$show	.=					"TRANSACTION";
				$show	.=				"</td>";
				$show	.=						"<td width='30%' align='center' nowrap>";
				$show	.=							"CUSTOMER";
				$show	.=						"</td>";
				$show	.=								"<td width='10%' align='center' nowrap>";
				$show	.=									"SKU";
				$show	.=								"</td>";
				$show	.=										"<td width='10%' align='center' nowrap>";
				$show	.=											"DESCRIPTION";
				$show	.=										"</td>";
				$show	.=												"<td width='10%' align='center' nowrap>";
				$show	.=													"STATUS";
				$show	.=												"</td>";
				$show	.=														"<td width='20%' align='center' nowrap>";
				$show	.=															"REMARKS";
				$show	.=														"</td>";
				$show	.=																"<td width='10%' align='center' nowrap>";
				$show	.=																	"$selDateType";
				$show	.=																"</td>";
				$show	.=	"</tr>";
			
				
				$counter	=	1;
				
				while (!$rsget_hdr->EOF) 
				{
					$IATRANSNO		=	$rsget_hdr->fields['IATRANSNO'];
					$CUSTNO			=	$rsget_hdr->fields['CUSTNO'];
					$REFN0			=	$rsget_hdr->fields['REFN0'];
					$REFTYPE		=	$rsget_hdr->fields['REFTYPE'];
					$STATUS			=	$rsget_hdr->fields['STATUS'];
					$aRemarkss		=	"";
					
					$get_remarks	= "SELECT REMARKS FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSNO='{$IATRANSNO}' GROUP BY REMARKS";
					$rs_remarks		= $Filstar_conn->Execute($get_remarks);
					if ($rs_remarks==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					while (!$rs_remarks->EOF) 
					{
						if ($aRemarkss=='') 
						{
							$aRemarkss_name	= Sel_val($Filstar_conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE='{$rs_remarks->fields['REMARKS']}'");
							$aRemarkss		=	$aRemarkss_name;
						}
						else 
						{
							$aRemarkss_name	= Sel_val($Filstar_conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE='{$rs_remarks->fields['REMARKS']}'");
							$aRemarkss	=	$aRemarkss.",".$aRemarkss_name;
						}
						$rs_remarks->MoveNext();
					}
						
					$desc		= Sel_val($Filstar_conn,"FDCRMSlive","itemmaster","ItemDesc","ItemNo='{$sku}'");
					if ($selDateType == "ADDEDDATE")
					{
						$ADDEDDATE	=	$rsget_hdr->fields['ADDEDDATE'];
					}
					else 
					{
						$ADDEDDATE	=	$rsget_hdr->fields['POSTEDDATE'];
					}
					
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
		
					$show	.=	"<td align='left'   nowrap >";
					$show	.=	$CUSTNO.'-'.$custname;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap >";
					$show	.=	$sku;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap >";
					$show	.=	$desc;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	$STATUS;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	"$aRemarkss";
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	$ADDEDDATE;
					$show	.=	"</td>";
					
					$show	.=	"</tr>";
					
					$counter++;
					
					$rsget_hdr->MoveNext();	
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
			
			echo$show;
					
			exit();
		}
		else 
		{
			$DFROM			=	$_GET['DFROM'];
			$DTO			=	$_GET['DTO'];
			$rdoType		=	$_GET['rdoType'];
			
			$selRemarks		=	$_POST['selRemarks'];
			$customercode	=	$_POST['customercode'];
			$selDateType_	=	$_POST['selDateType'];
			
			$selstatus		=	$_POST['selstatus'];
			$results = array();
			
			if ($rdoType == "NBS")
			{
				$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE (CustomerBranchCode!='' or NBSnewBranchCode!='')";
				$rs_cust	= $Filstar_conn->Execute($qry_cust);
			}
			else 
			{
				$qry_cust	= "SELECT CustNo FROM FDCRMSlive.custmast WHERE CustomerBranchCode=''";
				$rs_cust	= $Filstar_conn->Execute($qry_cust);
			}
			
			while (!$rs_cust->EOF)
			{
				$cust_no	= $rs_cust->fields['CustNo'];
				$results[] = $cust_no;
				$rs_cust->MoveNext();	
			}
				$_cust	= implode("','",$results);
				$CUST_VALID	= " AND CUSTNO in ('{$_cust}')";
				
			
			$sel_val	=	"SELECT `IATRANSNO`, `CUSTNO`, `STATUS`, `ADDEDDATE`, `POSTEDDATE`
							FROM WMS_NEW.INVENTORYADJUSTMENT_HDR where `{$selDateType_}` between '{$DFROM}' AND '{$DTO}' $CUST_VALID";
			if (!empty($customercode))
			{
				$sel_val	.=	" AND `CUSTNO` = '{$customercode}' ";
			}
			if ($selstatus != 'ALL')
			{
				$sel_val	.=	" AND `STATUS` = '{$selstatus}' ";
			}
	
			$rssel_val		=	$Filstar_conn->Execute($sel_val);
			$cnt	=	$rssel_val->RecordCount();
			if ($cnt > 0) 
			{
				$get_val	=	"SELECT `IATRANSNO`, `CUSTNO`, `STATUS`, `ADDEDDATE`, `POSTEDDATE`
								FROM WMS_NEW.INVENTORYADJUSTMENT_HDR where `{$selDateType_}` between '{$DFROM}' AND '{$DTO}' $CUST_VALID";
				if (!empty($customercode))
				{
					$get_val	.=	" AND `CUSTNO` = '{$customercode}' ";
				}
				if ($selstatus != 'ALL')
				{
					$get_val	.=	" AND `STATUS` = '{$selstatus}' ";
				}
				//echo $get_val;
				$rsget_val		=	$Filstar_conn->Execute($get_val);
				if ($rsget_val==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				if ($selDateType_ == "ADDEDDATE")
				{
					$selDateType = "I.A DATE";
				}
				else 
				{
					$selDateType = "POSTED DATE";
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
				$show	.=									"STATUS";
				$show	.=								"</td>";
				$show	.=										"<td width='20%' align='center' nowrap>";
				$show	.=											"REMARKS";
				$show	.=										"</td>";
				$show	.=												"<td width='10%' align='center' nowrap>";
				$show	.=													"$selDateType";
				$show	.=												"</td>";
				$show	.=	"</tr>";
			
				
				$counter	=	1;
				
				while (!$rsget_val->EOF) 
				{
					$IATRANSNO		=	$rsget_val->fields['IATRANSNO'];
					$CUSTNO			=	$rsget_val->fields['CUSTNO'];
					$REFN0			=	$rsget_val->fields['REFN0'];
					$REFTYPE		=	$rsget_val->fields['REFTYPE'];
					$STATUS			=	$rsget_val->fields['STATUS'];
					$aRemarkss		=	"";
					
					$get_remarks	= "SELECT REMARKS FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSNO='{$IATRANSNO}' GROUP BY REMARKS";
					$rs_remarks		= $Filstar_conn->Execute($get_remarks);
					if ($rs_remarks==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					while (!$rs_remarks->EOF) 
					{
						if ($aRemarkss=='') 
						{
							$aRemarkss_name	= Sel_val($Filstar_conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE='{$rs_remarks->fields['REMARKS']}'");
							$aRemarkss		=	$aRemarkss_name;
						}
						else 
						{
							$aRemarkss_name	= Sel_val($Filstar_conn,"WMS_NEW","DELIVERY_REMARKS","DESCRIPTION","CODE='{$rs_remarks->fields['REMARKS']}'");
							$aRemarkss	=	$aRemarkss.",".$aRemarkss_name;
						}
						$rs_remarks->MoveNext();
					}
						
					
					if ($selDateType_ == "ADDEDDATE")
					{
						$ADDEDDATE	=	$rsget_val->fields['ADDEDDATE'];
					}
					else 
					{
						$ADDEDDATE	=	$rsget_val->fields['POSTEDDATE'];
					}
					
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
		
					$show	.=	"<td align='left'   nowrap >";
					$show	.=	$CUSTNO.'-'.$custname;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	$STATUS;
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	"$aRemarkss";
					$show	.=	"</td>";
					
					$show	.=	"<td align='center'   nowrap>";
					$show	.=	$ADDEDDATE;
					$show	.=	"</td>";
					
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
			$show	.=	"</tr>";
			
			$COUNTER	=	1;
			
			while (!$rsIANO->EOF) 
			{
				$IATRANSNO	=	$rsIANO->fields['IATRANSNO'];
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
				
				$rsIANO->MoveNext();
			}
			
			$show	.=	"<input type='hidden' name='hdniano' id='hdniano' value='{$IATRANSNO}'>";
			$show	.=	"</table>";
			
			echo $show;
		}
		
		
		
		exit();
	}
	
	
	if ($action=='POSTING') 
	{
		$THIS_IANO	=	$_GET['THIS_IANO'];
		
		$Filstar_conn->StartTrans();
		$sel_dtl	=	"SELECT  SKUNO,IAQTY FROM WMS_NEW.INVENTORYADJUSTMENT_DTL WHERE IATRANSNO = '{$THIS_IANO}' ";
		$rssel_dtl	=	$Filstar_conn->Execute($sel_dtl);
		if ($rssel_dtl==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		while (!$rssel_dtl->EOF) 
		{
			$SKUNO	=	$rssel_dtl->fields['SKUNO'];
			$IAQTY	=	$rssel_dtl->fields['IAQTY'];
			
			$sel_itembal	=	"SELECT onhqty,whsloc FROM itembal where itmnbr = '{$SKUNO}' and house = 'FDC' ";
			$rssel_itembal	=	$Filstar_conn->Execute($sel_itembal);
			if ($rssel_itembal==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			$onhqty			=	$rssel_itembal->fields['onhqty'];
			$whsloc			=	$rssel_itembal->fields['whsloc'];
			
			$update_item	=	"UPDATE itembal set onhqty = (onhqty + $IAQTY) where itmnbr = '{$SKUNO}' and house = 'FDC' ";
			$rsupdate_item	=	$Filstar_conn->Execute($update_item);
			if ($rsupdate_item==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			
			$newonhand		=	$onhqty+$IAQTY;
			
			$update_ia		=	"UPDATE WMS_NEW.INVENTORYADJUSTMENT_DTL set CURRONHANDQTY = '{$onhqty}' , NEWONHANDQTY =  '{$newonhand}', LOCATION = '{$whsloc}' 
								WHERE IATRANSNO = '{$THIS_IANO}' AND SKUNO = '{$SKUNO}' AND IAQTY = '{$IAQTY}' ";
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
		$sel	.=	"AND CustStatus = 'A' AND (CustomerBranchCode!='' or NBSnewBranchCode!='') ";
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
	
	if($action == "get_sku")
	{
		$sku	=	$_GET['sku'];
		
		$qry_sku		= "SELECT ItemNo,ItemDesc from  FDCRMSlive.itemmaster WHERE ItemNo={$sku}";
		$rs_qry_sku		= $Filstar_conn->Execute($qry_sku);
		
		$counter =	$rs_qry_sku->RecordCount();
		
		if ($counter > 0)
		{
			$desc	= $rs_qry_sku->fields['ItemDesc'];
			echo $desc;
		}
		else 
		{
			$desc = "<font color=red>No Items</font>";
			echo $desc;
		}
		
		exit();
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
<body>
	<form name="dataform" id="dataform" enctype="multipart/form-data" method="POST">
		<table width="100%" border="0" class="Text_header" style="border:0px">
			<tr>
				<td align="center" width="18%" style="font-size:15px" colspan="3">
					<input type="radio" id="rdoNBS" name="rdoType" value="NBS">NBS&nbsp;&nbsp;<input type="radio" id="rdoTrade" name="rdoType" value="TRADE">TRADE
				</td>
				
			</tr>
			<tr>
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px" >
					SKU
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<input type="text" name="txtSKU" id="txtSKU" size="9" onchange="checkSKU();"> <label id="lblDescription"></label>
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
				<!--<td width="25%">
					&nbsp;
				</td>-->
				<!--<td align="left" width="18%" style="font-size:15px">
					REMARKS
				</td>-->
				<!--<td align="left" width="57%" style="font-size:15px">
				<?php
  						$sel_remarks	=	"SELECT * FROM WMS_NEW.DELIVERY_REMARKS WHERE 1";
  						$rssel_remarks	=	$Filstar_conn->Execute($sel_remarks);
  						if ($rssel_remarks == false) 
						{
							echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
						}
				?>
  				:<select id="selRemarks" name="selRemarks" >
  					<option value=""></option>
  				<?php
  					while (!$rssel_remarks->EOF) 
						{
							$remarksid	=	$rssel_remarks->fields['CODE'];
							$remarks	=	$rssel_remarks->fields['DESCRIPTION'];
				?>
					<option value="<?php echo $remarksid; ?>"><?php echo ucwords(strtoupper($remarks)); ?></option>
  				<?php $rssel_remarks->MoveNext();}?>
				</select>
				</td>-->
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
				<td width="25%">
					&nbsp;
				</td>
				<td align="left" width="18%" style="font-size:15px">
					DATE TYPE
				</td>
				<td align="left" width="57%" style="font-size:15px">
					:<select name="selDateType" id="selDateType" size="0">
						<option value=""></option>
						<option value="ADDEDDATE">I.A DATE</option>
						<option value="POSTEDDATE">POSTED DATE</option>
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
				<td width="100%" colspan="3" align="center">
					<input type="button" name="btnsearch" id="btnsearch" value="SEARCH" class="small_button" onclick="DISPLAY();"  style='width:120px;height:35px;'>
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
		<center><div id="btnPrint" style="display:none;">
			<input type="button" name="btnsearch" id="btnsearch" value="PRINT" class="small_button" onclick="fncPrint('');"  style='width:120px;height:35px;'>
		</div></center>
		<div id="divdata_detail" title="ITEM LIST"></div>
		<div id="divloader" style="display:none;" align="center" title="LOADING"><img src="../../images/loading/ajax-loader_fast.gif"></div>
		<div id="divmsg" style="display:none;" align="center" title="ALERT"></div>
		<div id="dialog_print_ok_cancel" title="ALERT"></div>
	</form>
</body>
</html>
<script>
$(".dates").datepicker({ 
	dateFormat: 'yy-mm-dd',
	changeMonth: true,
    changeYear: true 
});
$("#dialog_print_ok_cancel").dialog({
	bgiframe:true, resizable:false,modal:true, autoOpen: false, dialogClass:'no-close',closeOnEscape:false,	
	
	overlay: {
		backgroundColor: '#000', opacity: 0.5
	},
	buttons: {
		'OK': function() {
//			var PrintType		= $('#reportType').val();
						
			var txtSKU 			= $("#txtSKU").val();
			var txttransno 		= $("#txttransno").val();
			var customercode 	= $("#customercode").val();
			var selstatus 		= $("#selstatus").val();
			var reportType		= $('#reportType').val();
			var selType			= $('#selstatus').val();
			var txtDateFrom		= $('#dfrom').val();
			var txtDateTo		= $('#dto').val();
			var rdoType			= $('#rdoType').val();
	
	
//			if(PrintType == 'Detail')
//			{
				document.getElementById('dataform').action	=	'adjustment_summary_print.php'; //+'&selType='+selType+'&txtDateFrom='+txtDateFrom+'&txtDateTo='+txtDateTo
				document.getElementById('dataform').submit();
//			}
//			else
//			{
//				document.getElementById('dataform').action	=	'adjustment_printSum.php?printcode='+printcode+'&selType='+selType+'&txtDateFrom='+txtDateFrom+'&txtDateTo='+txtDateTo+'&reportType='+reportType+'&txttransno='+txttransno+'&txtrefno='+txtrefno+'&customercode='+customercode+'&selrefno='+selrefno+'&selstatus='+selstatus; //+'&selType='+selType+'&txtDateFrom='+txtDateFrom+'&txtDateTo='+txtDateTo
//				document.getElementById('dataform').submit();
//			}
			

			$('#hidPrintCode').val('');
			for(ctr=0;ctr<=10;ctr++)
			{
				$('#chk'+ctr).removeAttr('checked');
			}

		$(this).dialog('close');
					
		},
		'CANCEL': function() {
			$('#hidPrintCode').val('');
			for(ctr=0;ctr<=10;ctr++){
				$('#chk'+ctr).removeAttr('checked');
			}
			
			$(this).dialog('close');
		}
	}
});

function DISPLAY()
{
	var sku	= $('#txtSKU').val();
		
	var rdoType = "";
	if ($('#rdoTrade').is(":checked") == true)
	{
		rdoType	=	"Trade";
	}
	else if ($('#rdoNBS').is(":checked") == true)
	{
		rdoType	=	"NBS";
	}
	else
	{
		alert('Please choose type!');return;
	}
	
	
	var	selDateType	=	$('#selDateType').val();
	
	if (selDateType == '')
	{
		alert('Please Select Date Type!');
		return;
	}
	
	
	var	dfrom	=	$('#dfrom').val();
	var	dto		=	$('#dto').val();
	
	if(dfrom == '' || dto == '' )
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
			url			:	'adjustment_summary.php?action=DISPLAY_&DFROM='+dfrom+'&DTO='+dto+'&rdoType='+rdoType+'&sku='+sku,
			beforeSend	:	function()
						{
							$('#divloader').dialog('open');
						},
			success		:	function(response)
						{
							$('#divloader').dialog('close');
							$('#divdata').dialog('open');
							$('#divdata').html(response);
							$('#btnPrint').show();
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

function fncPrint()
{
	var rdoType	=	$('.rdoType').val();

	if(rdoType!='')
	{
		$('#dialog_print_ok_cancel').dialog('open');
		$('#dialog_print_ok_cancel').html('Do you want to print all results?');
	}	
}
function checkSKU()
{
	var sku		= $('#txtSKU').val();
	
	if(sku!='')
	{
		$.ajax({
		type: "POST",
		url: "adjustment_summary.php?action=get_sku&sku="+sku,
		beforeSend: function() {
					$('#divloader').dialog('open');
				},
		success: function(html) {
				$('#divloader').dialog('close');
				$('#lblDescription').html(html);
			}		
		});
	}
	else
	{
		$('#lblDescription').html('');
	}
}
</script>