<?php
class	__Global_Func
{
	function	Login_user(&$conn,$val_user)
	{
		$sel	=	" SELECT NAME FROM ".WMS_LOOKUP.".USER WHERE USERNAME = '{$val_user}' ";
		$rssel	=	$conn->Execute($sel);
		$retval	=	$rssel->fields['NAME'];
		return $retval;
	}
	
	function	Login_user2(&$conn,$val_user)
	{
		$sel	=	" SELECT NAME FROM WMS_USER.USER WHERE USERNAME = '{$val_user}' ";
		$rssel	=	$conn->Execute($sel);
		$retval	=	$rssel->fields['NAME'];
		return $retval;
	}
	
	/**
	 * Grant modul access of Users
	 *
	 * @param connection
	 * @param User
	 * @param Parent Module
	 * @param Module name
	 * @return bool
	 */
	function	GetAccess(&$conn,$val_user,$val_parent,$val_type)
	{
		$user		= 	"SELECT ID  FROM ".WMS_LOOKUP.".USER ";
		$user		.=	"WHERE USERNAME ='{$val_user}' AND STATUS = 'Y'";
		$rsuser		=	$conn->Execute($user);
		$userid		=	$rsuser->fields['ID'];
		$sel 		=	"SELECT MODULEID FROM ".WMS_LOOKUP.".MODULES ";
		$sel 		.=	"WHERE MODULENAME = '{$val_type}' and MODULETYPE = '{$val_parent}' AND ACTIVE = 'Y' ";
		$rssel		=	$conn->Execute($sel);
		$id	 		=	$rssel->fields['MODULEID'];
		$access		=	"SELECT COUNT(*) AS CNT FROM ".WMS_LOOKUP.".ACCESSLEVEL WHERE MODULEID = '{$id}' AND USERID = '{$userid}' ";
		$rsaccess	=	$conn->Execute($access);
		if ($rsaccess->fields['CNT'] > 0) 
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
	function	GetAccess2(&$conn,$val_user,$val_parent,$val_type)
	{
		$user		= 	"SELECT ID  FROM WMS_USER.USER ";
		$user		.=	"WHERE USERNAME ='{$val_user}' AND STATUS = 'Y'";
		$rsuser		=	$conn->Execute($user);
		$userid		=	$rsuser->fields['ID'];
		$sel 		=	"SELECT MODULEID FROM WMS_USER.MODULES ";
		$sel 		.=	"WHERE MODULENAME = '{$val_type}' and MODULETYPE = '{$val_parent}' AND ACTIVE = 'Y' ";
		$rssel		=	$conn->Execute($sel);
		$id	 		=	$rssel->fields['MODULEID'];
		$access		=	"SELECT COUNT(*) AS CNT FROM WMS_USER.ACCESS_MODULE WHERE MODULEID = '{$id}' AND ID_NUMBER = '{$userid}' ";
		$rsaccess	=	$conn->Execute($access);

		if ($rsaccess->fields['CNT'] > 0) 
		{
			return true;
		}
		else 
		{
			return false;
		}
	}
	
	/**
	 * Get Customer Name
	 *
	 * @param Connection
	 * @param Customer number
	 * @return String
	 */
	function	CustName($conn,$val_no)
	{
		$sel_dept	=	"SELECT CustName FROM ".FDCRMS.".custmast WHERE CustNo = '{$val_no}' ";
		$rsdept		=	$conn->Execute($sel_dept);
		$retval		=	$rsdept->fields['CustName'];
		return $retval;
	}
	
	/**
	 * Generate Transaction number
	 *
	 * @param Connection
	 * @param Dispatch Type
	 * @return String
	 */
	function	Generate_transeq($conn,$type)
	{
		$date	=	date('Ymd');
		//try {
			//$conn->StartTrans();
			if ($type == 'MANILA') 
			{
				$alpha		=	'M';
				$transeq	=	"INSERT INTO ".DISPATCH_DB.".TRANSEQ_MANILA(`CREATED_DATE`)VALUE(sysdate())";
				$rstranseq	=	$conn->Execute($transeq);
				if ($rstranseq == false) 
				{
					throw new Exception(mysql_errno()."::".mysql_error());
				}
				$id	=	$conn->Insert_ID();
				$tracking	=	$alpha.$date.str_pad($id,10,0,STR_PAD_LEFT);
			}
			elseif ($type == 'PANDAYAN')
			{
				$alpha		=	'P';
				$transeq	=	"INSERT INTO ".DISPATCH_DB.".TRANSEQ_PANDAYAN(`CREATED_DATE`)VALUE(sysdate())";
				$rstranseq	=	$conn->Execute($transeq);
				if ($rstranseq == false) 
				{
					throw new Exception(mysql_errno()."::".mysql_error());
				}
				$id	=	$conn->Insert_ID();
				$tracking	=	$alpha.$date.str_pad($id,10,0,STR_PAD_LEFT);
			}
			else 
			{
				$alpha		=	'V';
				$transeq	=	"INSERT INTO ".DISPATCH_DB.".TRANSEQ_PROVINCE(`CREATED_DATE`)VALUE(sysdate())";
				$rstranseq	=	$conn->Execute($transeq);
				if ($rstranseq == false) 
				{
					throw new Exception(mysql_errno()."::".mysql_error());
				}
				$id	=	$conn->Insert_ID();
				$tracking	=	$alpha.$date.str_pad($id,10,0,STR_PAD_LEFT);
			}
			//$conn->CompleteTrans();
			return $tracking;
		//}
		//catch (Exception $e)
		//{
			//echo $e->__toString();
			//$conn->CompleteTrans();
		//}
	}
	
	/**
	 * Select value
	 *
	 * @param connection
	 * @param database
	 * @param table
	 * @param field
	 * @param condition
	 * @return unknown
	 */
	function	Select_val($conn,$db,$tbl,$fld,$condition)
	{
		$sel	=	" SELECT $fld FROM $db.$tbl WHERE $condition ";
		$rssel	=	$conn->Execute($sel);
		if ($rssel == false)
		{
			die(mysql_errno()."::".mysql_error());
		}
		$retval	=	$rssel->fields[$fld];
		return $retval;
	}
	
	/**
	 * Show table for Dispatch type
	 *
	 * @param unknown_type $conn1
	 * @param unknown_type $data
	 * @param unknown_type $type
	 */
	function	Table_form($conn1,$data,$type)
	{
		$cnt	=	1;
		if ($type	==	'MANILA') 
		{
			$show	=	"<form name='datamanila' id='datamanila'>";
			
			/******************************************************************/
			/*TABLE FOR MAIN HEADER*/
			$show	.=	"<table width='100%' border='0' class='d0'>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center'  class='Text_header'>";
						$show	.=	FDC_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center'  class='Text_header'>";
								$show	.=	'DATE';
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center'  class='Text_header'>";
										$show	.=	'ROUTE';
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center'  class='Text_header'>";
												$show	.=	'VAN NO.';
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center'  class='Text_header'>";
														$show	.=	'PLATE NO.';
														$show	.=	"</td>";
																$show	.=	"<td width='8%' align='left' class='Text_header'>";
																$show	.=	"DRIVER";
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='left' colspan='3'  class='Text_header'>";
																		$show	.=	"<input type='text' name='txtdriver_MANILA' id='txtdriver_MANILA' value='' size='20' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center' style='font-weight: bold;'  class='Text_header'>";
						$show	.=	DISPATCH_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center'  class='Text_header'>";
								$show	.=	"<input type='text' name='txtdate_MANILA' id='txtdate_MANILA' value='' size='5' maxlength='10' style='text-align:center;' autocomplete='off' readonly>";
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center' class='Text_header'>";
										$show	.=	"<input type='text' name='txtroute_MANILA' id='txtroute_MANILA' size='5' onkeyup=search_route(event,this.value,this.id,'$type'); style='text-align:center;' >";
										$show	.=	"<div id='div_route_MANILA' style='position:absolute;'></div>";
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center' class='Text_header'>";
												$show	.=	"<input type='text' name='txtvan_MANILA' id='txtvan_MANILA' size='5' onkeyup=search_van(event,this.value,this.id,'$type'); style='text-align:center;' >";
												$show	.=	"<div id='div_van_MANILA' style='position:absolute;'></div>";
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center'  class='Text_header'>";
														$show	.=	"<input type='text' name='txtplate_MANILA' id='txtplate_MANILA' size='5' readonly style='text-align:center;' >";
														$show	.=	"</td>";
																$show	.=	"<td width='8%' align='left'  class='Text_header' >";
																$show	.=	"HELPER";
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='left' colspan='3'  class='Text_header'>";
																		$show	.=	"<input type='text' name='txthelper_MANILA' id='txthelper_MANILA' value='' size='20' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='71%' colspan='5' align='left'  class='Text_header'>";
						$show	.=	"&nbsp";
						$show	.=	"</td>";
							$show	.=	"<td width='12%' align='left'  class='Text_header'>";
							$show	.=	"FORWARDER";
							$show	.=	"</td>";
									$show	.=	"<td width='12%' align='left' colspan='3' class='Text_header'>";
									$show	.=	"<input type='text' name='txtforwarded_MANILA' id='txtforwarded_MANILA' value='' size='20' style='border:1;' >";
									$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center' style='font-weight: bold;' rowspan='2' class='Text_header'>";
						$show	.=	"CUSTOMER NAME & ADDRESS";
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center' colspan='2' class='Text_header'>";
								$show	.=	'INVOICE';
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center' class='Text_header'>";
										$show	.=	'SOF';
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center' class='Text_header'>";
												$show	.=	'P.O.';
												$show	.=	"</td>";
														$show	.=	"<td width='8%' align='center' class='Text_header'>";
														$show	.=	'PROD.';
														$show	.=	"</td>";
																$show	.=	"<td width='14%' align='center' class='Text_header' colspan='2'>";
																$show	.=	'QUANTITY';
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='center' style='font-weight: bold;' rowspan='2' class='Text_header'>";
																		$show	.=	'REMARKS';
																	$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
							$show	.=	"<td width='5%' align='center' class='Text_header'>";
							$show	.=	'NUMBER';
							$show	.=	"</td>";
							$show	.=	"<td width='5%' align='center' class='Text_header'>";
							$show	.=	'AMOUNT';
							$show	.=	"</td>";
									$show	.=	"<td width='10%' align='center' class='Text_header'>";
									$show	.=	'NUMBER';
									$show	.=	"</td>";
											$show	.=	"<td width='10%' align='center' class='Text_header'>";
											$show	.=	'NUMBER';
											$show	.=	"</td>";
													$show	.=	"<td width='8%' align='center' class='Text_header'>";
													$show	.=	'LINE';
													$show	.=	"</td>";
															$show	.=	"<td width='7%' align='center' class='Text_header'>";
															$show	.=	'CTN';
															$show	.=	"</td>";
																$show	.=	"<td width='7%' align='center' class='Text_header'>";
																$show	.=	'PKG';
																$show	.=	"</td>";
					$show	.=	"</tr>";
					//$show	.=	"</table>";
				/*END OF TABLE FOR MAIN HEADER*/
				/******************************************************************/
				
				
				/******************************************************************/
				/*DATA SELECTED DISPLAY*/
				//$show	.=	"<table width='100%' border='1' class='d0'>";
				foreach ($data as $key=>$val)
				{
					$val_cnt	=	count($val);
					$x			=	0;
					for ($x;$x<=($val_cnt - 1);$x++)
					{
						$val_orno	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","ORDERNO","CUSTNO = '$key' AND INVOICENO = '".$val[$x]."' ");
						$v	=	substr($val_orno,-1);
						if (is_numeric($v))
						{
							$prodline	=	substr($val_orno,1,1);
						}
						else 
						{
							$prodline	=	substr($val_orno,-1);
						}
						
						$Street			=	self::Select_val($conn1,FDCRMS,"customer_address","StreetNumber","CUSTNO= '$key'");
						$Town			=	self::Select_val($conn1,FDCRMS,"customer_address","TownCity","CUSTNO= '$key'");
						$Custname_addr	=	self::CustName($conn1,$key)."<br>".$Street.' '.$Town;
						$invoiceno		=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","INVOICENO","CUSTNO= '$key' AND INVOICENO= '".$val[$x]."'");
						$invoiceamt		=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","INVOICEAMOUNT","CUSTNO= '$key' AND INVOICENO= '".$val[$x]."'");
						$orderno		=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","ORDERNO","CUSTNO= '$key' AND INVOICENO= '".$val[$x]."'");
						$show	.=	"<tr>";
							$show	.=	"<td width='35%' align='left' class='Text_header'>";
							$show	.=	$Custname_addr;
							$show	.=	"<input type='hidden' name='hdn_cust_MANILA_$cnt' id='hdn_cust_MANILA_$cnt' value='$key'";
							$show	.=	"</td>";
									$show	.=	"<td width='10%' align='right' class='Text_header'>";
									$show	.=	$invoiceno;
									$show	.=	"<input type='hidden' name='hdn_invoice_MANILA_$cnt' id='hdn_invoice_MANILA_$cnt' value='".$val[$x]."'";
									$show	.=	"</td>";
										$show	.=	"<td width='10%' align='right' class='Text_header'>";
										$show	.=	number_format($invoiceamt,2);
										$show	.=	"</td>";
											$show	.=	"<td width='10%' align='right' class='Text_header'>";
											$show	.=	$orderno;
											$show	.=	"</td>";
													$show	.=	"<td width='10%' align='center' class='Text_header'>";
													$show	.=	'&nbsp';
													$show	.=	"</td>";
															$show	.=	"<td width='10%' align='center' class='Text_header'>";
															$show	.=	$prodline;
															$show	.=	"</td>";
																	$show	.=	"<td width='5%' align='center' class='Text_header' onclick=focus_here('txt_MANILA_ctn_$cnt');>";
																	$show	.=	"<input type='text' name='txt_MANILA_ctn_$cnt' id='txt_MANILA_ctn_$cnt' value='' size='1' style='text-align:center;border:1;' maxlength='4' onkeyup='isnumeric(this.value,this.id);' >";
																	$show	.=	"</td>";
																		$show	.=	"<td width='5%' align='center' class='Text_header' onclick=focus_here('txt_MANILA_pkg_$cnt');>";
																		$show	.=	"<input type='text' name='txt_MANILA_pkg_$cnt' id='txt_MANILA_pkg_$cnt' value='' size='1' style='text-align:center;border:1;' maxlength='4'>";
																		$show	.=	"</td>";
																			$show	.=	"<td width='5%' align='center' class='Text_header' onclick=focus_here('txt_MANILA_remark_$cnt');>";
																			$show	.=	"<input type='text' name='txt_MANILA_remark_$cnt' id='txt_MANILA_remark_$cnt' value='' size='6' style='text-align:center;border:1;' >";
																			$show	.=	"</td>";
						$show	.=	"</tr>";
						$cnt++;
					}
				}
				/*END OF DATA SELECTED DISPLAY*/
				/******************************************************************/
			$show	.=		"<tr>";
			$show	.=			"<td width='30%' colspan='1' align='left'  class='Text_header'>";
			$show	.=				"SPECIAL INSTRUCTION";
			$show	.=			"</td>";
			$show	.=			"<td width='70%' colspan='8' align='left'  class='Text_header'>";
			$show	.=				"<input type = 'text' name='txtinstruction_MANILA' id='txtinstruction_MANILA' value='' size='50'>";
			$show	.=			"<td>";
			$show	.=		"</tr>";
			$show	.=	"</table>";
			$show	.=	"</form>";
		}
		elseif ($type == 'PANDAYAN')
		{
			$show	=	"<form name='datapandayan' id='datapandayan'>";
//			/******************************************************************/
			$show	.=	"<table width='100%' border='0' class='d0'>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center'  class='Text_header'>";
						$show	.=	FDC_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center'  class='Text_header'>";
								$show	.=	'DATE';
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center'  class='Text_header'>";
										$show	.=	'ROUTE';
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center'  class='Text_header'>";
												$show	.=	'VAN NO.';
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center'  class='Text_header'>";
														$show	.=	'PLATE NO.';
														$show	.=	"</td>";
																$show	.=	"<td width='8%' align='left' class='Text_header'>";
																$show	.=	"DRIVER";
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='left' colspan='2'  class='Text_header'>";
																		$show	.=	"<input type='text' name='txtdriver_PANDAYAN' id='txtdriver_PANDAYAN' value='' size='25' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center' style='font-weight: bold;'  class='Text_header'>";
						$show	.=	DISPATCH_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center'  class='Text_header'>";
								$show	.=	"<input type='text' name='txtdate_PANDAYAN' id='txtdate_PANDAYAN' value='' size='5' maxlength='10' style='text-align:center;' autocomplete='off' readonly>";
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center' class='Text_header'>";
										$show	.=	"<input type='text' name='txtroute_PANDAYAN' id='txtroute_PANDAYAN' size='5' onkeyup=search_route(event,this.value,this.id,'$type'); style='text-align:center;' >";
										$show	.=	"<div id='div_route_PANDAYAN' style='position:absolute;'></div>";
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center' class='Text_header'>";
												$show	.=	"<input type='text' name='txtvan_PANDAYAN' id='txtvan_PANDAYAN' size='5' onkeyup=search_van(event,this.value,this.id,'$type'); style='text-align:center;' >";
												$show	.=	"<div id='div_van_PANDAYAN' style='position:absolute;'></div>";
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center'  class='Text_header'>";
														$show	.=	"<input type='text' name='txtplate_PANDAYAN' id='txtplate_PANDAYAN' size='5' readonly style='text-align:center;' >";
														$show	.=	"</td>";
																$show	.=	"<td width='8%' align='left'  class='Text_header' >";
																$show	.=	"HELPER";
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='left' colspan='2'  class='Text_header'>";
																		$show	.=	"<input type='text' name='txthelper_PANDAYAN' id='txthelper_PANDAYAN' value='' size='25' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='100%' colspan='8' align='left'  class='Text_header'>";
						$show	.=	"&nbsp";
						$show	.=	"</td>";
					$show	.=	"</tr>";
					
					$show	.=	"<tr>";
					$show	.=	"<td width='30%' align='center' style='font-weight: bold;' rowspan='2' class='Text_header'>";
					$show	.=	"CUSTOMER NAME & ADDRESS";
					$show	.=	"</td>";
							$show	.=	"<td width='15%' align='center' colspan='2' class='Text_header'>";
							$show	.=	'INVOICE';
							$show	.=	"</td>";
									$show	.=	"<td width='8%' align='center' class='Text_header'>";
									$show	.=	'SOF';
									$show	.=	"</td>";
											$show	.=	"<td width='8%' align='center' class='Text_header'>";
											$show	.=	'PICKING';
											$show	.=	"</td>";
														$show	.=	"<td width='12%' align='center' class='Text_header'>";
														$show	.=	'PRODUCT';
														$show	.=	"</td>";
															$show	.=	"<td width='12%' align='center' class='Text_header'>";
															$show	.=	'NUMBER OF';
															$show	.=	"</td>";
																$show	.=	"<td width='15%' align='center' style='font-weight: bold;' rowspan='2' class='Text_header'>";
																$show	.=	'REMARKS';
																$show	.=	"</td>";
				$show	.=	"</tr>";
				$show	.=	"<tr>";
							$show	.=	"<td width='7%' align='center' class='Text_header'>";
							$show	.=	'NUMBER';
							$show	.=	"</td>";
							$show	.=	"<td width='8%' align='center' class='Text_header'>";
							$show	.=	'AMOUNT';
							$show	.=	"</td>";
									$show	.=	"<td width='8%' align='center' class='Text_header'>";
									$show	.=	'NUMBER';
									$show	.=	"</td>";
											$show	.=	"<td width='8%' align='center' class='Text_header'>";
											$show	.=	'LIST NO.';
											$show	.=	"</td>";
													$show	.=	"<td width='8%' align='center' class='Text_header'>";
													$show	.=	'LINE';
													$show	.=	"</td>";
															$show	.=	"<td width='8%' align='center' class='Text_header'>";
															$show	.=	'CARTON';
															$show	.=	"</td>";
				$show	.=	"</tr>";
				
				/*DATA SELECTED DISPLAY*/
				foreach ($data as $key=>$val)
				{
					$val_cnt	=	count($val);
					$x			=	0;
					for ($x;$x<=($val_cnt - 1);$x++)
					{
						$val_orno	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","ORDERNO","CUSTNO = '$key' AND INVOICENO = '".$val[$x]."' ");
						$v	=	substr($val_orno,-1);
						if (is_numeric($v))
						{
							$prodline	=	substr($val_orno,1,1);
						}
						else 
						{
							$prodline	=	substr($val_orno,-1);
						}
						
						$Street			=	self::Select_val($conn1,FDCRMS,"customer_address","StreetNumber","CUSTNO= '$key'");
						$Town			=	self::Select_val($conn1,FDCRMS,"customer_address","TownCity","CUSTNO= '$key'");
						$Custname_addr	=	self::CustName($conn1,$key)."<br>".$Street.' '.$Town;
						$invoiceno		=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","INVOICENO","CUSTNO= '$key' AND INVOICENO= '".$val[$x]."'");
						$invoiceamt		=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","INVOICEAMOUNT","CUSTNO= '$key' AND INVOICENO= '".$val[$x]."'");
						$orderno		=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","ORDERNO","CUSTNO= '$key' AND INVOICENO= '".$val[$x]."'");
						$picklist		=	self::Select_val($conn1,FDCRMS,"orderheader","PickListNo","CustNo= '$key' AND InvoiceNo= '".$val[$x]."'");
						
						$show	.=	"<tr>";
							$show	.=	"<td width='30%' align='left'  class='Text_header'>";
							$show	.=	$Custname_addr;
							$show	.=	"<input type='hidden' name='hdn_cust_PANDAYAN_$cnt' id='hdn_cust_PANDAYAN_$cnt' value='$key' >";
							$show	.=	"</td>";
									$show	.=	"<td width='7%' align='right' class='Text_header'>";
									$show	.=	$invoiceno;
									$show	.=	"<input type='hidden' name='hdn_invoice_PANDAYAN_$cnt' id='hdn_invoice_PANDAYAN_$cnt' value='".$val[$x]."'>";
									$show	.=	"</td>";
									$show	.=	"<td width='8%' align='right' class='Text_header'>";
									$show	.=	number_format($invoiceamt,2);
									$show	.=	"</td>";
											$show	.=	"<td width='8%' align='right' class='Text_header'>";
											$show	.=	$orderno;
											$show	.=	"</td>";
													$show	.=	"<td width='8%' align='right' class='Text_header'>";
													$show	.=	$picklist;
													$show	.=	"</td>";
																$show	.=	"<td width='8%' align='center' class='Text_header'>";
																$show	.=	$prodline;
																$show	.=	"</td>";
																	$show	.=	"<td width='8%' align='center' onclick=focus_here('txt_PANDAYAN_ctn_$cnt'); class='Text_header'>";
																	$show	.=	"<input type='text' name='txt_PANDAYAN_ctn_$cnt' id='txt_PANDAYAN_ctn_$cnt' value='' size='5' style='text-align:center;border:1;' maxlength='4' onkeyup='isnumeric(this.value,this.id);'>";
																	$show	.=	"</td>";
																			$show	.=	"<td width='15%' align='center' onclick=focus_here('txt_PANDAYAN_remark_$cnt'); class='Text_header'>";
																			$show	.=	"<input type='text' name='txt_PANDAYAN_remark_$cnt' id='txt_PANDAYAN_remark_$cnt' value='' size='15' style='text-align:center;border:1;' >";
																			$show	.=	"</td>";
						$show	.=	"</tr>";
						$cnt++;
						}
				}
			/*END OF DATA SELECTED DISPLAY*/
			/******************************************************************/
			$show	.=		"<tr>";
			$show	.=			"<td width='30%' colspan='1' align='left' class='Text_header'>";
			$show	.=				"SPECIAL INSTRUCTION";
			$show	.=			"</td>";
			$show	.=			"<td width='70%' colspan='7' align='left' class='Text_header'>";
			$show	.=				"<input type = 'text' name='txtinstruction_PANDAYAN' id='txtinstruction_PANDAYAN' value='' size='50'>";
			$show	.=			"<td>";
			$show	.=		"</tr>";
			$show	.=	"</table>";
			$show	.=	"</form>";
		}
		elseif ($type == 'PROVINCE')
		{
			$show	=	"<form name='dataprovince' id='dataprovince'>";
			/******************************************************************/
			/*TABLE FOR MAIN HEADER*/
			$show	.=	"<table width='100%' border='0' class='d0'>";
					$show	.=	"<tr>";
						$show	.=	"<td width='30%' align='center' class='Text_header'>";
						$show	.=	FDC_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='8%' align='center' class='Text_header' >";
								$show	.=	'DATE';
								$show	.=	"</td>";
										$show	.=	"<td width='8%' align='center' class='Text_header'>";
										$show	.=	'ROUTE';
										$show	.=	"</td>";
												$show	.=	"<td width='8%' align='center' class='Text_header'>";
												$show	.=	'VAN NO.';
												$show	.=	"</td>";
														$show	.=	"<td width='8%' align='center' class='Text_header'>";
														$show	.=	'PLATE NO.';
														$show	.=	"</td>";
																$show	.=	"<td width='10%' align='left' class='Text_header'>";
																$show	.=	"DRIVER";
																$show	.=	"</td>";
																		$show	.=	"<td width='33%' align='left' class='Text_header' colspan='3'>";
																		$show	.=	"<input type='text' name='txtdriver_PROVINCE' id='txtdriver_PROVINCE' value='' size='30' style='border:1;'>";
																		$show	.=	"</td>";
																		
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='30%' align='center' style='font-weight: bold;' class='Text_header'>";
						$show	.=	DISPATCH_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='8%' align='center' class='Text_header'>";
								$show	.=	"<input type='text' name='txtdate_PROVINCE' id='txtdate_PROVINCE' value='' size='5' maxlength='10' style='text-align:center;' autocomplete='off' readonly>";
								$show	.=	"</td>";
										$show	.=	"<td width='8%' align='center' class='Text_header'>";
										$show	.=	"<input tye='text' name='txtroute_PROVINCE' id='txtroute_PROVINCE' value='' size='5'  onkeyup=search_route(event,this.value,this.id,'$type'); style='text-align:center;'>";
										$show	.=	"<div id='div_route_PROVINCE' style='position:absolute;'></div>";
										$show	.=	"</td>";
												$show	.=	"<td width='8%' align='center' class='Text_header'>";
												$show	.=	"<input type='text' name='txtvan_PROVINCE' id='txtvan_PROVINCE' value='' size='5'  onkeyup=search_van(event,this.value,this.id,'$type'); style='text-align:center;'>";
												$show	.=	"<div id='div_van_PROVINCE' style='position:absolute;'></div>";
												$show	.=	"</td>";
														$show	.=	"<td width='8%' align='center' class='Text_header'>";
														$show	.=	"<input type='text' name='txtplate_PROVINCE' id='txtplate_PROVINCE' value='' size='5' style='text-align:center;' readonly>";
														$show	.=	"</td>";
																$show	.=	"<td width='10%' align='left' class='Text_header'>";
																$show	.=	"HELPER";
																$show	.=	"</td>";
																		$show	.=	"<td width='33%' align='left' class='Text_header' colspan='3'>";
																		$show	.=	"<input type='text' name='txthelper_PROVINCE' id='txthelper_PROVINCE' value='' size='30' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='67%' align='left' colspan='5' class='Text_header'>";
						$show	.=	"&nbsp";
						$show	.=	"</td>";
							$show	.=	"<td width='10%' align='left' class='Text_header'>";
							$show	.=	"FORWARDER";
							$show	.=	"</td>";
									$show	.=	"<td width='33%' align='left' class='Text_header' colspan='3'>";
									$show	.=	"<input type='text' name='txtforwarded_PROVINCE' id='txtforwarded_PROVINCE' value='' size='30' style='border:1;'>";
									$show	.=	"</td>";
					$show	.=	"</tr>";
					
					$show	.=	"<tr>";
					$show	.=	"<td width='25%' align='center' rowspan='2' class='Text_header'>";
					$show	.=	"CUSTOMER NAME & ADDRESS ";
					$show	.=	"</td>";
							$show	.=	"<td width='8%' align='center' class='Text_header'>";
							$show	.=	"INVOICE";
							$show	.=	"</td>";
									$show	.=	"<td width='8%' align='center' class='Text_header'>";
									$show	.=	"SOF";
									$show	.=	"</td>";
											$show	.=	"<td width='8%' align='center' class='Text_header'>";
											$show	.=	"PRODUCT";
											$show	.=	"</td>";
													$show	.=	"<td width='8%' align='center' class='Text_header'>";
													$show	.=	"SIZE";
													$show	.=	"</td>";
															$show	.=	"<td width='10%' align='center' class='Text_header'>";
															$show	.=	"QTY";
															$show	.=	"</td>";
																	$show	.=	"<td width='10%'  align='center' rowspan='2' class='Text_header'>";
																	$show	.=	"DR NO.";
																	$show	.=	"</td>";
																		$show	.=	"<td width='13%'  align='center' class='Text_header'>";
																		$show	.=	"WAYBILL";
																		$show	.=	"</td>";
																				$show	.=	"<td width='10%'  align='center' class='Text_header'>";
																				$show	.=	"WEIGHT";
																				$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='8%' align='center' class='Text_header'>";
						$show	.=	"NUMBER";
						$show	.=	"</td>";
								$show	.=	"<td width='8%' align='center' class='Text_header'>";
								$show	.=	"NUMBER";
								$show	.=	"</td>";
										$show	.=	"<td width='8%' align='center' class='Text_header'>";
										$show	.=	"LINE";
										$show	.=	"</td>";
												$show	.=	"<td width='8%' align='center' class='Text_header'>";
												$show	.=	"OF CTN";
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center' class='Text_header'>";
														$show	.=	"CTN";
														$show	.=	"</td>";
																$show	.=	"<td width='13%'  align='center' class='Text_header'>";
																$show	.=	"NUMBER";
																$show	.=	"</td>";
																			$show	.=	"<td width='10%'  align='center' nowrap class='Text_header'>";
																			$show	.=	"BY KILO";
																			$show	.=	"</td>";
														
					$show	.=	"</tr>";
					/*DATA SELECTED DISPLAY*/
					foreach ($data as $key=>$val)
					{
						$val_cnt	=	count($val);
						$x			=	0;
						for ($x;$x<=($val_cnt - 1);$x++)
						{
							$val_orno	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","ORDERNO","CUSTNO = '$key' AND INVOICENO = '".$val[$x]."' ");
							$v	=	substr($val_orno,-1);
							if (is_numeric($v))
							{
								$prodline	=	substr($val_orno,1,1);
							}
							else 
							{
								$prodline	=	substr($val_orno,-1);
							}
							$Street			=	self::Select_val($conn1,FDCRMS,"customer_address","StreetNumber","CUSTNO= '$key'");
							$Town			=	self::Select_val($conn1,FDCRMS,"customer_address","TownCity","CUSTNO= '$key'");
							$Custname_addr	=	self::CustName($conn1,$key)."<br>".$Street.' '.$Town;
							$invoiceno		=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","INVOICENO","CUSTNO= '$key' AND INVOICENO= '".$val[$x]."'");
							$invoiceamt		=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","INVOICEAMOUNT","CUSTNO= '$key' AND INVOICENO= '".$val[$x]."'");
							$orderno		=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_ORDER","ORDERNO","CUSTNO= '$key' AND INVOICENO= '".$val[$x]."'");
							$picklist		=	self::Select_val($conn1,FDCRMS,"orderheader","PickListNo","CustNo= '$key' AND InvoiceNo= '".$val[$x]."'");
							
						$show	.=	"<tr>";
							$show	.=	"<td width='25%' align='left' class='Text_header'>";
							$show	.=	$Custname_addr;
							$show	.=	"<input type='hidden' name='hdn_cust_PROVINCE_$cnt' id='hdn_cust_PROVINCE_$cnt' value='$key'";
							$show	.=	"</td>";
								$show	.=	"<td width='8%' align='right' class='Text_header'>";
								$show	.=	$invoiceno;
								$show	.=	"<input type='hidden' name='hdn_invoice_PROVINCE_$cnt' id='hdn_invoice_PROVINCE_$cnt' value='$invoiceno'";
								$show	.=	"</td>";
										$show	.=	"<td width='8%' align='right' class='Text_header'>";
										$show	.=	$orderno;
										$show	.=	"</td>";
												$show	.=	"<td width='8%' align='center' class='Text_header'>";
												$show	.=	$prodline;
												$show	.=	"</td>";
														$show	.=	"<td width='8%' align='center' onclick=focus_here('txt_PROVINCE_size_$cnt'); class='Text_header'>";
														$show	.=	"<input type='text' name='txt_PROVINCE_size_$cnt' id='txt_PROVINCE_size_$cnt' value='' size='5' style='text-align:center;border:1;' maxlength='4' onkeyup='isnumeric(this.value,this.id);'>";
														$show	.=	"</td>";
																$show	.=	"<td width='10%' align='center' onclick=focus_here('txt_PROVINCE_ctn_$cnt'); class='Text_header'>";
																$show	.=	"<input type='text' name='txt_PROVINCE_ctn_$cnt' id='txt_PROVINCE_ctn_$cnt' value='' size='5' style='text-align:center;border:1;' maxlength='4' onkeyup='isnumeric(this.value,this.id);'>";
																$show	.=	"</td>";
																		$show	.=	"<td width='10%'  align='center'  onclick=focus_here('txt_PROVINCE_dr_$cnt'); class='Text_header'>";
																		$show	.=	"<input type='text' name='txt_PROVINCE_dr_$cnt' id='txt_PROVINCE_dr_$cnt' value='' size='5' style='text-align:center;border:1;' maxlength='7'>";
																		$show	.=	"</td>";
																			$show	.=	"<td width='13%'  align='center' onclick=focus_here('txt_PROVINCE_bill_$cnt');  class='Text_header'>";
																			$show	.=	"<input type='text' name='txt_PROVINCE_bill_$cnt' id='txt_PROVINCE_bill_$cnt' value='' size='5' style='text-align:center;border:1;' maxlength='10'>";
																			$show	.=	"</td>";
																					$show	.=	"<td width='10%'  align='center' onclick=focus_here('txt_PROVINCE_kilo_$cnt'); class='Text_header'>";
																					$show	.=	"<input type='text' name='txt_PROVINCE_kilo_$cnt' id='txt_PROVINCE_kilo_$cnt' value='' size='5' style='text-align:center;border:1;' maxlength='4'>";
																					$show	.=	"</td>";
						$show	.=	"</tr>";
						$cnt++;
						}
					}
			/*END OF DATA SELECTED DISPLAY*/
			/******************************************************************/
			$show	.=		"<tr>";
			$show	.=			"<td width='25%' align='left' class='Text_header'>";
			$show	.=				"SPECIAL INSTRUCTION";
			$show	.=			"</td>";
			$show	.=			"<td width='75%' align='left' class='Text_header' colspan='8'>";
			$show	.=				"<input type = 'text' name='txtinstruction_PROVINCE' id='txtinstruction_PROVINCE' value='' size='50'>";
			$show	.=			"<td>";
			$show	.=		"</tr>";
			$show	.=	"</table>";;
			$show	.=	"</form>";
		}
//		/*SUBMIT BUTTONS*/
		$show	.=	"<table width='100%' border='0' class='d0'>";
		$show	.=		"<tr>";
		$show	.=			"<td width='100%' align='center'>";
		$show	.=				"<input type='button' name='btnsave' id='btnsave' value='Save' onClick=SaveDispatch_temp('$type',$cnt) class='small_button'>";
		$show	.=				"<input type='button' name='btnconfirm' id='btnconfirm' value='Confirm' onClick=SaveDispatch('$type',$cnt) class='small_button'>";
		$show	.=				"<input type='button' name='btnCancel' id='btnCancel' value='Cancel' onClick=Cancel(); class='small_button'>";
		$show	.=			"</td>";
		$show	.=		"</tr>";
		$show	.=	"</table>";
		/*END OF SUBMIT BUTTONS*/
//		unset($data);
		return $show;
		}
		
		
		
		/**
	 * Show table for Dispatch type(update)
	 *
	 * @param connect 1 $conn1
	 * @param dispatch type $type
	 * @param tracking no. $transeq
	 */
	function	Table_form_update($conn1,$type,$transeq)
	{
		$cnt	=	1;
		$aData	=	array();
		if ($type	==	'MANILA') 
		{
			$date	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_METROMANILA_HDR","DATE","TRANSEQ = '{$transeq}' ");
			$route	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_METROMANILA_HDR","ROUTE","TRANSEQ = '{$transeq}' ");
			$vanno	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_METROMANILA_HDR","VANNO","TRANSEQ = '{$transeq}' ");
			$plate	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_METROMANILA_HDR","PLATENO","TRANSEQ = '{$transeq}' ");
			$driver	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_METROMANILA_HDR","DRIVER","TRANSEQ = '{$transeq}' ");
			$helper	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_METROMANILA_HDR","HELPER","TRANSEQ = '{$transeq}' ");
			$forward=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_METROMANILA_HDR","FORWARDER","TRANSEQ = '{$transeq}' ");
			$special_=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_METROMANILA_HDR","SPECIAL_INSTRUCTION","TRANSEQ = '{$transeq}' ");
			
			$sel	=" SELECT ID_DTL,TRANSEQ,CUSTCODE,INVOICENO,INVOICEDATE,INVOICEAMOUNT,SOFNO,PLNO,PONO,PRODUCTLINE,CARTON,PACKAGE,REMARKS ";
			$sel   .=" FROM ".DISPATCH_DB.".DISPATCH_METROMANILA_DTL WHERE TRANSEQ = '{$transeq}' ";
			$rssel	=	$conn1->Execute($sel);
			while (!$rssel->EOF) 
			{
				$id				=	$rssel->fields['ID_DTL'];
				$transeq		=	$rssel->fields['TRANSEQ'];
				$custcode		=	$rssel->fields['CUSTCODE'];
				$invoiceno		=	$rssel->fields['INVOICENO'];
				$invoicedate	=	$rssel->fields['INVOICEDATE'];
				$invoiceamount	=	$rssel->fields['INVOICEAMOUNT'];
				$sofno			=	$rssel->fields['SOFNO'];
				$plno			=	$rssel->fields['PLNO'];
				$pono			=	$rssel->fields['PONO'];
				$productline	=	$rssel->fields['PRODUCTLINE'];
				$carton			=	$rssel->fields['CARTON'];
				$package		=	$rssel->fields['PACKAGE'];
				$remarks		=	$rssel->fields['REMARKS'];
				
				$aData[$transeq][$custcode][$invoiceno]['ID']			=	$id;
				$aData[$transeq][$custcode][$invoiceno]['INVOICEDATE']	=	$invoicedate;
				$aData[$transeq][$custcode][$invoiceno]['INVOICEAMOUNT']=	$invoiceamount;
				$aData[$transeq][$custcode][$invoiceno]['SOFNO']		=	$sofno;
				$aData[$transeq][$custcode][$invoiceno]['PLNO']			=	$plno;
				$aData[$transeq][$custcode][$invoiceno]['PONO']			=	$pono;
				$aData[$transeq][$custcode][$invoiceno]['PRODUCTLINE']	=	$productline;
				$aData[$transeq][$custcode][$invoiceno]['CARTON']		=	$carton;
				$aData[$transeq][$custcode][$invoiceno]['PACKAGE']		=	$package;
				$aData[$transeq][$custcode][$invoiceno]['REMARKS']		=	$remarks;
				
				$rssel->MoveNext();
			}
			
			$show	=	"<form name='datamanila' id='datamanila'>";
			
			/******************************************************************/
			/*TABLE FOR MAIN HEADER*/
			$show	.=	"<table width='100%' border='0' class='d0'>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center'  class='Text_header'>";
						$show	.=	FDC_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center'  class='Text_header'>";
								$show	.=	'DATE';
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center'  class='Text_header'>";
										$show	.=	'ROUTE';
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center'  class='Text_header'>";
												$show	.=	'VAN NO.';
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center'  class='Text_header'>";
														$show	.=	'PLATE NO.';
														$show	.=	"</td>";
																$show	.=	"<td width='8%' align='left' class='Text_header'>";
																$show	.=	"DRIVER";
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='left' colspan='3'  class='Text_header'>";
																		$show	.=	"<input type='text' name='txtdriver_MANILA' id='txtdriver_MANILA' value='$driver' size='20' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center' style='font-weight: bold;'  class='Text_header'>";
						$show	.=	DISPATCH_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center'  class='Text_header'>";
								$show	.=	"<input type='text' name='txtdate_MANILA' id='txtdate_MANILA' value='$date' size='5' maxlength='10' style='text-align:center;' autocomplete='off' readonly>";
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center' class='Text_header'>";
										$show	.=	"<input type='text' name='txtroute_MANILA' id='txtroute_MANILA' value='$route' size='5' onkeyup=search_route(event,this.value,this.id,'$type'); style='text-align:center;' >";
										$show	.=	"<div id='div_route_MANILA' style='position:absolute;'></div>";
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center' class='Text_header'>";
												$show	.=	"<input type='text' name='txtvan_MANILA' id='txtvan_MANILA' value='$vanno' size='5' onkeyup=search_van(event,this.value,this.id,'$type'); style='text-align:center;' >";
												$show	.=	"<div id='div_van_MANILA' style='position:absolute;'></div>";
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center'  class='Text_header'>";
														$show	.=	"<input type='text' name='txtplate_MANILA' id='txtplate_MANILA' value='$plate' size='5' readonly style='text-align:center;' >";
														$show	.=	"</td>";
																$show	.=	"<td width='8%' align='left'  class='Text_header' >";
																$show	.=	"HELPER";
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='left' colspan='3'  class='Text_header'>";
																		$show	.=	"<input type='text' name='txthelper_MANILA' id='txthelper_MANILA' value='$helper' size='20' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='71%' colspan='5' align='left'  class='Text_header'>";
						$show	.=	"&nbsp";
						$show	.=	"</td>";
							$show	.=	"<td width='12%' align='left'  class='Text_header'>";
							$show	.=	"FORWARDER";
							$show	.=	"</td>";
									$show	.=	"<td width='12%' align='left' colspan='3' class='Text_header'>";
									$show	.=	"<input type='text' name='txtforwarded_MANILA' id='txtforwarded_MANILA' value='$forward' size='20' style='border:1;' >";
									$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center' style='font-weight: bold;' rowspan='2' class='Text_header'>";
						$show	.=	"CUSTOMER NAME & ADDRESS";
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center' colspan='2' class='Text_header'>";
								$show	.=	'INVOICE';
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center' class='Text_header'>";
										$show	.=	'SOF';
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center' class='Text_header'>";
												$show	.=	'P.O.';
												$show	.=	"</td>";
														$show	.=	"<td width='8%' align='center' class='Text_header'>";
														$show	.=	'PROD.';
														$show	.=	"</td>";
																$show	.=	"<td width='14%' align='center' class='Text_header' colspan='2'>";
																$show	.=	'QUANTITY';
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='center' style='font-weight: bold;' rowspan='2' class='Text_header'>";
																		$show	.=	'REMARKS';
																	$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
							$show	.=	"<td width='5%' align='center' class='Text_header'>";
							$show	.=	'NUMBER';
							$show	.=	"</td>";
							$show	.=	"<td width='5%' align='center' class='Text_header'>";
							$show	.=	'AMOUNT';
							$show	.=	"</td>";
									$show	.=	"<td width='10%' align='center' class='Text_header'>";
									$show	.=	'NUMBER';
									$show	.=	"</td>";
											$show	.=	"<td width='10%' align='center' class='Text_header'>";
											$show	.=	'NUMBER';
											$show	.=	"</td>";
													$show	.=	"<td width='8%' align='center' class='Text_header'>";
													$show	.=	'LINE';
													$show	.=	"</td>";
															$show	.=	"<td width='7%' align='center' class='Text_header'>";
															$show	.=	'CTN';
															$show	.=	"</td>";
																$show	.=	"<td width='7%' align='center' class='Text_header'>";
																$show	.=	'PKG';
																$show	.=	"</td>";
					$show	.=	"</tr>";
					//$show	.=	"</table>";
				/*END OF TABLE FOR MAIN HEADER*/
				/******************************************************************/
				/******************************************************************/
				/*DATA SELECTED DISPLAY*/
				foreach ($aData as $transeq=>$val_custcode)
				{
					foreach ($val_custcode as $custcode=>$val_invoice) 
					{
						foreach ($val_invoice as $invoice=>$val)
						{
								$Street			=	self::Select_val($conn1,FDCRMS,"customer_address","StreetNumber","CUSTNO= '$custcode'");
								$Town			=	self::Select_val($conn1,FDCRMS,"customer_address","TownCity","CUSTNO= '$custcode'");
								$Custname_addr	=	self::CustName($conn1,$custcode)."<br>".$Street.' '.$Town;
							$show	.=	"<tr>";
								$show	.=	"<td width='35%' align='left' class='Text_header'>";
								$show	.=	$Custname_addr;
								$show	.=	"<input type='hidden' name='hdn_cust_MANILA_$cnt' id='hdn_cust_MANILA_$cnt' value='$custcode'";
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='right' class='Text_header'>";
										$show	.=	$invoiceno;
										$show	.=	"<input type='hidden' name='hdn_invoice_MANILA_$cnt' id='hdn_invoice_MANILA_$cnt' value='".$invoice."'";
										$show	.=	"</td>";
											$show	.=	"<td width='10%' align='right' class='Text_header'>";
											$show	.=	number_format($val['INVOICEAMOUNT'],2);
											$show	.=	"</td>";
												$show	.=	"<td width='10%' align='right' class='Text_header'>";
												$show	.=	$val['SOFNO'];
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center' class='Text_header'>";
														$show	.=	$val['PONO'];
														$show	.=	"</td>";
																$show	.=	"<td width='10%' align='center' class='Text_header'>";
																$show	.=	$val['PRODUCTLINE'];
																$show	.=	"</td>";
																		$show	.=	"<td width='5%' align='center' class='Text_header' onclick=focus_here('txt_MANILA_ctn_$cnt');>";
																		$show	.=	"<input type='text' name='txt_MANILA_ctn_$cnt' id='txt_MANILA_ctn_$cnt' value='".$val['CARTON']."' size='1' style='text-align:center;border:1;' maxlength='4' onkeyup='isnumeric(this.value,this.id);' >";
																		$show	.=	"</td>";
																			$show	.=	"<td width='5%' align='center' class='Text_header' onclick=focus_here('txt_MANILA_pkg_$cnt');>";
																			$show	.=	"<input type='text' name='txt_MANILA_pkg_$cnt' id='txt_MANILA_pkg_$cnt' value='".$val['PACKAGE']."' size='1' style='text-align:center;border:1;' maxlength='4'>";
																			$show	.=	"</td>";
																				$show	.=	"<td width='5%' align='center' class='Text_header' onclick=focus_here('txt_MANILA_remark_$cnt');>";
																				$show	.=	"<input type='text' name='txt_MANILA_remark_$cnt' id='txt_MANILA_remark_$cnt' value='".$val['REMARKS']."' size='6' style='text-align:center;border:1;' >";
																				$show	.=	"</td>";
							$show	.=	"</tr>";
							$cnt++;
						}
					}
				}
				/*END OF DATA SELECTED DISPLAY*/
				/******************************************************************/
			$show	.=		"<tr>";
			$show	.=			"<td width='30%' colspan='1' align='left'  class='Text_header'>";
			$show	.=				"SPECIAL INSTRUCTION";
			$show	.=			"</td>";
			$show	.=			"<td width='70%' colspan='8' align='left'  class='Text_header'>";
			$show	.=				"<input type = 'text' name='txtinstruction_MANILA' id='txtinstruction_MANILA' value='".$special_."' size='50'>";
			$show	.=			"<td>";
			$show	.=		"</tr>";
			$show	.=	"</table>";
			//$show	.=	"</form>";
		}
		elseif ($type == 'PANDAYAN')
		{
			
			$date	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","DATE","TRANSEQ = '{$transeq}' ");
			$route	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","ROUTE","TRANSEQ = '{$transeq}' ");
			$vanno	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","VANNO","TRANSEQ = '{$transeq}' ");
			$plate	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","PLATENO","TRANSEQ = '{$transeq}' ");
			$driver	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","DRIVER","TRANSEQ = '{$transeq}' ");
			$helper	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","HELPER","TRANSEQ = '{$transeq}' ");
			$special_=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PANDAYAN_HDR","SPECIAL_INSTRUCTION","TRANSEQ = '{$transeq}' ");
			
			
			$sel	=" SELECT ID_DTL,TRANSEQ,CUSTCODE,INVOICENO,INVOICEDATE,INVOICEAMOUNT,SOFNO,PLNO,PONO,PRODUCTLINE,CARTON,PACKAGE,REMARKS ";
			$sel   .=" FROM ".DISPATCH_DB.".DISPATCH_PANDAYAN_DTL WHERE TRANSEQ = '{$transeq}' ";
			$rssel	=	$conn1->Execute($sel);
			while (!$rssel->EOF) 
			{
				$id				=	$rssel->fields['ID_DTL'];
				$transeq		=	$rssel->fields['TRANSEQ'];
				$custcode		=	$rssel->fields['CUSTCODE'];
				$invoiceno		=	$rssel->fields['INVOICENO'];
				$invoicedate	=	$rssel->fields['INVOICEDATE'];
				$invoiceamount	=	$rssel->fields['INVOICEAMOUNT'];
				$sofno			=	$rssel->fields['SOFNO'];
				$plno			=	$rssel->fields['PLNO'];
				$pono			=	$rssel->fields['PONO'];
				$productline	=	$rssel->fields['PRODUCTLINE'];
				$carton			=	$rssel->fields['CARTON'];
				$package		=	$rssel->fields['PACKAGE'];
				$remarks		=	$rssel->fields['REMARKS'];
				
				$aData[$transeq][$custcode][$invoiceno]['ID']			=	$id;
				$aData[$transeq][$custcode][$invoiceno]['INVOICEDATE']	=	$invoicedate;
				$aData[$transeq][$custcode][$invoiceno]['INVOICEAMOUNT']=	$invoiceamount;
				$aData[$transeq][$custcode][$invoiceno]['SOFNO']		=	$sofno;
				$aData[$transeq][$custcode][$invoiceno]['PLNO']			=	$plno;
				$aData[$transeq][$custcode][$invoiceno]['PONO']			=	$pono;
				$aData[$transeq][$custcode][$invoiceno]['PRODUCTLINE']	=	$productline;
				$aData[$transeq][$custcode][$invoiceno]['CARTON']		=	$carton;
				$aData[$transeq][$custcode][$invoiceno]['PACKAGE']		=	$package;
				$aData[$transeq][$custcode][$invoiceno]['REMARKS']		=	$remarks;
				
				$rssel->MoveNext();
			}
			
			$show	=	"<form name='datapandayan' id='datapandayan'>";
//			/******************************************************************/
			$show	.=	"<table width='100%' border='0' class='d0'>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center'  class='Text_header'>";
						$show	.=	FDC_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center'  class='Text_header'>";
								$show	.=	'DATE';
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center'  class='Text_header'>";
										$show	.=	'ROUTE';
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center'  class='Text_header'>";
												$show	.=	'VAN NO.';
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center'  class='Text_header'>";
														$show	.=	'PLATE NO.';
														$show	.=	"</td>";
																$show	.=	"<td width='8%' align='left' class='Text_header'>";
																$show	.=	"DRIVER";
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='left' colspan='2'  class='Text_header'>";
																		$show	.=	"<input type='text' name='txtdriver_PANDAYAN' id='txtdriver_PANDAYAN' value='$driver' size='25' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='35%' align='center' style='font-weight: bold;'  class='Text_header'>";
						$show	.=	DISPATCH_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='10%' align='center'  class='Text_header'>";
								$show	.=	"<input type='text' name='txtdate_PANDAYAN' id='txtdate_PANDAYAN' value='$date' size='5' maxlength='10' style='text-align:center;' autocomplete='off' readonly>";
								$show	.=	"</td>";
										$show	.=	"<td width='10%' align='center' class='Text_header'>";
										$show	.=	"<input type='text' name='txtroute_PANDAYAN' id='txtroute_PANDAYAN' value='$route' size='5' onkeyup=search_route(event,this.value,this.id,'$type'); style='text-align:center;' >";
										$show	.=	"<div id='div_route_PANDAYAN' style='position:absolute;'></div>";
										$show	.=	"</td>";
												$show	.=	"<td width='10%' align='center' class='Text_header'>";
												$show	.=	"<input type='text' name='txtvan_PANDAYAN' id='txtvan_PANDAYAN' value='$vanno' size='5' onkeyup=search_van(event,this.value,this.id,'$type'); style='text-align:center;' >";
												$show	.=	"<div id='div_van_PANDAYAN' style='position:absolute;'></div>";
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center'  class='Text_header'>";
														$show	.=	"<input type='text' name='txtplate_PANDAYAN' id='txtplate_PANDAYAN' value='$plate' size='5' readonly style='text-align:center;' >";
														$show	.=	"</td>";
																$show	.=	"<td width='8%' align='left'  class='Text_header' >";
																$show	.=	"HELPER";
																$show	.=	"</td>";
																		$show	.=	"<td width='12%' align='left' colspan='2'  class='Text_header'>";
																		$show	.=	"<input type='text' name='txthelper_PANDAYAN' id='txthelper_PANDAYAN' value='$helper' size='25' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='100%' colspan='8' align='left'  class='Text_header'>";
						$show	.=	"&nbsp";
						$show	.=	"</td>";
					$show	.=	"</tr>";
					
					$show	.=	"<tr>";
					$show	.=	"<td width='30%' align='center' style='font-weight: bold;' rowspan='2' class='Text_header'>";
					$show	.=	"CUSTOMER NAME & ADDRESS";
					$show	.=	"</td>";
							$show	.=	"<td width='15%' align='center' colspan='2' class='Text_header'>";
							$show	.=	'INVOICE';
							$show	.=	"</td>";
									$show	.=	"<td width='8%' align='center' class='Text_header'>";
									$show	.=	'SOF';
									$show	.=	"</td>";
											$show	.=	"<td width='8%' align='center' class='Text_header'>";
											$show	.=	'PICKING';
											$show	.=	"</td>";
														$show	.=	"<td width='12%' align='center' class='Text_header'>";
														$show	.=	'PRODUCT';
														$show	.=	"</td>";
															$show	.=	"<td width='12%' align='center' class='Text_header'>";
															$show	.=	'NUMBER OF';
															$show	.=	"</td>";
																$show	.=	"<td width='15%' align='center' style='font-weight: bold;' rowspan='2' class='Text_header'>";
																$show	.=	'REMARKS';
																$show	.=	"</td>";
				$show	.=	"</tr>";
				$show	.=	"<tr>";
							$show	.=	"<td width='7%' align='center' class='Text_header'>";
							$show	.=	'NUMBER';
							$show	.=	"</td>";
							$show	.=	"<td width='8%' align='center' class='Text_header'>";
							$show	.=	'AMOUNT';
							$show	.=	"</td>";
									$show	.=	"<td width='8%' align='center' class='Text_header'>";
									$show	.=	'NUMBER';
									$show	.=	"</td>";
											$show	.=	"<td width='8%' align='center' class='Text_header'>";
											$show	.=	'LIST NO.';
											$show	.=	"</td>";
													$show	.=	"<td width='8%' align='center' class='Text_header'>";
													$show	.=	'LINE';
													$show	.=	"</td>";
															$show	.=	"<td width='8%' align='center' class='Text_header'>";
															$show	.=	'CARTON';
															$show	.=	"</td>";
				$show	.=	"</tr>";
				
				foreach ($aData as $transeq=>$val_custcode)
				{
					foreach ($val_custcode as $custcode=>$val_invoice) 
					{
						foreach ($val_invoice as $invoice=>$val)
						{
							$Street			=	self::Select_val($conn1,FDCRMS,"customer_address","StreetNumber","CUSTNO= '$custcode'");
							$Town			=	self::Select_val($conn1,FDCRMS,"customer_address","TownCity","CUSTNO= '$custcode'");
							$Custname_addr	=	self::CustName($conn1,$custcode)."<br>".$Street.' '.$Town;
							
							$show	.=	"<tr>";
								$show	.=	"<td width='30%' align='left'  class='Text_header'>";
								$show	.=	$Custname_addr;
								$show	.=	"<input type='hidden' name='hdn_cust_PANDAYAN_$cnt' id='hdn_cust_PANDAYAN_$cnt' value='$custcode' >";
								$show	.=	"</td>";
										$show	.=	"<td width='7%' align='right' class='Text_header'>";
										$show	.=	$invoice;
										$show	.=	"<input type='hidden' name='hdn_invoice_PANDAYAN_$cnt' id='hdn_invoice_PANDAYAN_$cnt' value='".$invoice."'>";
										$show	.=	"</td>";
										$show	.=	"<td width='8%' align='right' class='Text_header'>";
										$show	.=	number_format($val['INVOICEAMOUNT'],2);
										$show	.=	"</td>";
												$show	.=	"<td width='8%' align='right' class='Text_header'>";
												$show	.=	$val['SOFNO'];
												$show	.=	"</td>";
														$show	.=	"<td width='8%' align='right' class='Text_header'>";
														$show	.=	$val['PLNO'];
														$show	.=	"</td>";
																	$show	.=	"<td width='8%' align='center' class='Text_header'>";
																	$show	.=	$val['PRODUCTLINE'];
																	$show	.=	"</td>";
																		$show	.=	"<td width='8%' align='center' onclick=focus_here('txt_PANDAYAN_ctn_$cnt'); class='Text_header'>";
																		$show	.=	"<input type='text' name='txt_PANDAYAN_ctn_$cnt' id='txt_PANDAYAN_ctn_$cnt' value='".$val['CARTON']."' size='5' style='text-align:center;border:1;' maxlength='4' onkeyup='isnumeric(this.value,this.id);'>";
																		$show	.=	"</td>";
																				$show	.=	"<td width='15%' align='center' onclick=focus_here('txt_PANDAYAN_remark_$cnt'); class='Text_header'>";
																				$show	.=	"<input type='text' name='txt_PANDAYAN_remark_$cnt' id='txt_PANDAYAN_remark_$cnt' value='".$val['REMARKS']."' size='15' style='text-align:center;border:1;' >";
																				$show	.=	"</td>";
							$show	.=	"</tr>";
							$cnt++;
						}
					}
				}
			/*END OF DATA SELECTED DISPLAY*/
			/******************************************************************/
			$show	.=		"<tr>";
			$show	.=			"<td width='25%' align='left' class='Text_header'>";
			$show	.=				"SPECIAL INSTRUCTION";
			$show	.=			"</td>";
			$show	.=			"<td width='75%' align='left' class='Text_header' colspan='8'>";
			$show	.=				"<input type = 'text' name='txtinstruction_PANDAYAN' id='txtinstruction_PANDAYAN' value='$special_' size='50'>";
			$show	.=			"<td>";
			$show	.=		"</tr>";
			$show	.=	"</table>";
			//$show	.=	"</form>";	
		}
		elseif ($type == 'PROVINCE')
		{
			$date	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","DATE","TRANSEQ = '{$transeq}' ");
			$route	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","ROUTE","TRANSEQ = '{$transeq}' ");
			$vanno	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","VANNO","TRANSEQ = '{$transeq}' ");
			$plate	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","PLATENO","TRANSEQ = '{$transeq}' ");
			$driver	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","DRIVER","TRANSEQ = '{$transeq}' ");
			$helper	=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","HELPER","TRANSEQ = '{$transeq}' ");
			$forward=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","FORWARDER","TRANSEQ = '{$transeq}' ");
			$special_=	self::Select_val($conn1,DISPATCH_DB,"DISPATCH_PROVINCE_HDR","SPECIAL_INSTRUCTION","TRANSEQ = '{$transeq}' ");
			
			$sel	=" SELECT `ID_DTL`,`TRANSEQ`,`CUSTCODE`, `INVOICENO`, `INVOICEAMOUNT`, `SOFNO`, `PRODUCT_LINE`, `SIZE_OF_CTN`, `QTY_CTN`, `DR_NO`, `WAYBILL_NUMBER`, `WEIGHT_BY_KILO` ";
			$sel   .=" FROM ".DISPATCH_DB.".DISPATCH_PROVINCE_DTL WHERE TRANSEQ = '{$transeq}' ";
			$rssel	=	$conn1->Execute($sel);
			while (!$rssel->EOF) 
			{
				$id				=	$rssel->fields['ID_DTL'];
				$transeq		=	$rssel->fields['TRANSEQ'];
				$custcode		=	$rssel->fields['CUSTCODE'];
				$invoiceno		=	$rssel->fields['INVOICENO'];
				$invoiceamount	=	$rssel->fields['INVOICEAMOUNT'];
				$sofno			=	$rssel->fields['SOFNO'];
				$product_line	=	$rssel->fields['PRODUCT_LINE'];
				$size_of_ctn	=	$rssel->fields['SIZE_OF_CTN'];
				$qty_ctn		=	$rssel->fields['QTY_CTN'];
				$dr_no			=	$rssel->fields['DR_NO'];
				$waybill_number	=	$rssel->fields['WAYBILL_NUMBER'];
				$weight_by_kilo	=	$rssel->fields['WEIGHT_BY_KILO'];
				
				$aData[$transeq][$custcode][$invoiceno]['ID']			=	$id;
				$aData[$transeq][$custcode][$invoiceno]['INVOICEAMOUNT']=	$invoiceamount;
				$aData[$transeq][$custcode][$invoiceno]['SOFNO']		=	$sofno;
				$aData[$transeq][$custcode][$invoiceno]['PRODUCT_LINE']	=	$product_line;
				$aData[$transeq][$custcode][$invoiceno]['SIZE_OF_CTN']	=	$size_of_ctn;
				$aData[$transeq][$custcode][$invoiceno]['QTY_CTN']		=	$qty_ctn;
				$aData[$transeq][$custcode][$invoiceno]['DR_NO']		=	$dr_no;
				$aData[$transeq][$custcode][$invoiceno]['WAYBILL_NUMBER']=	$waybill_number;
				$aData[$transeq][$custcode][$invoiceno]['WEIGHT_BY_KILO']=	$weight_by_kilo;
				$rssel->MoveNext();
			}
			$show	=	"<form name='dataprovince' id='dataprovince'>";
			/******************************************************************/
			/*TABLE FOR MAIN HEADER*/
			$show	.=	"<table width='100%' border='0' class='d0'>";
					$show	.=	"<tr>";
						$show	.=	"<td width='30%' align='center' class='Text_header'>";
						$show	.=	FDC_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='8%' align='center' class='Text_header' >";
								$show	.=	'DATE';
								$show	.=	"</td>";
										$show	.=	"<td width='8%' align='center' class='Text_header'>";
										$show	.=	'ROUTE';
										$show	.=	"</td>";
												$show	.=	"<td width='8%' align='center' class='Text_header'>";
												$show	.=	'VAN NO.';
												$show	.=	"</td>";
														$show	.=	"<td width='8%' align='center' class='Text_header'>";
														$show	.=	'PLATE NO.';
														$show	.=	"</td>";
																$show	.=	"<td width='10%' align='left' class='Text_header'>";
																$show	.=	"DRIVER";
																$show	.=	"</td>";
																		$show	.=	"<td width='33%' align='left' class='Text_header' colspan='3'>";
																		$show	.=	"<input type='text' name='txtdriver_PROVINCE' id='txtdriver_PROVINCE' value='$driver' size='30' style='border:1;'>";
																		$show	.=	"</td>";
																		
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='30%' align='center' style='font-weight: bold;' class='Text_header'>";
						$show	.=	DISPATCH_HEADER;
						$show	.=	"</td>";
								$show	.=	"<td width='8%' align='center' class='Text_header'>";
								$show	.=	"<input type='text' name='txtdate_PROVINCE' id='txtdate_PROVINCE' value='$date' size='5' maxlength='10' style='text-align:center;' autocomplete='off' readonly>";
								$show	.=	"</td>";
										$show	.=	"<td width='8%' align='center' class='Text_header'>";
										$show	.=	"<input tye='text' name='txtroute_PROVINCE' id='txtroute_PROVINCE' value='$route' size='5'  onkeyup=search_route(event,this.value,this.id,'$type'); style='text-align:center;'>";
										$show	.=	"<div id='div_route_PROVINCE' style='position:absolute;'></div>";
										$show	.=	"</td>";
												$show	.=	"<td width='8%' align='center' class='Text_header'>";
												$show	.=	"<input type='text' name='txtvan_PROVINCE' id='txtvan_PROVINCE' value='$vanno' size='5'  onkeyup=search_van(event,this.value,this.id,'$type'); style='text-align:center;'>";
												$show	.=	"<div id='div_van_PROVINCE' style='position:absolute;'></div>";
												$show	.=	"</td>";
														$show	.=	"<td width='8%' align='center' class='Text_header'>";
														$show	.=	"<input type='text' name='txtplate_PROVINCE' id='txtplate_PROVINCE' value='$plate' size='5' style='text-align:center;' readonly>";
														$show	.=	"</td>";
																$show	.=	"<td width='10%' align='left' class='Text_header'>";
																$show	.=	"HELPER";
																$show	.=	"</td>";
																		$show	.=	"<td width='33%' align='left' class='Text_header' colspan='3'>";
																		$show	.=	"<input type='text' name='txthelper_PROVINCE' id='txthelper_PROVINCE' value='$helper' size='30' style='border:1;'>";
																		$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='67%' align='left' colspan='5' class='Text_header'>";
						$show	.=	"&nbsp";
						$show	.=	"</td>";
							$show	.=	"<td width='10%' align='left' class='Text_header'>";
							$show	.=	"FORWARDER";
							$show	.=	"</td>";
									$show	.=	"<td width='33%' align='left' class='Text_header' colspan='3'>";
									$show	.=	"<input type='text' name='txtforwarded_PROVINCE' id='txtforwarded_PROVINCE' value='$forward' size='30' style='border:1;'>";
									$show	.=	"</td>";
					$show	.=	"</tr>";
					
					$show	.=	"<tr>";
					$show	.=	"<td width='25%' align='center' rowspan='2' class='Text_header'>";
					$show	.=	"CUSTOMER NAME & ADDRESS ";
					$show	.=	"</td>";
							$show	.=	"<td width='8%' align='center' class='Text_header'>";
							$show	.=	"INVOICE";
							$show	.=	"</td>";
									$show	.=	"<td width='8%' align='center' class='Text_header'>";
									$show	.=	"SOF";
									$show	.=	"</td>";
											$show	.=	"<td width='8%' align='center' class='Text_header'>";
											$show	.=	"PRODUCT";
											$show	.=	"</td>";
													$show	.=	"<td width='8%' align='center' class='Text_header'>";
													$show	.=	"SIZE";
													$show	.=	"</td>";
															$show	.=	"<td width='10%' align='center' class='Text_header'>";
															$show	.=	"QTY";
															$show	.=	"</td>";
																	$show	.=	"<td width='10%'  align='center' rowspan='2' class='Text_header'>";
																	$show	.=	"DR NO.";
																	$show	.=	"</td>";
																		$show	.=	"<td width='13%'  align='center' class='Text_header'>";
																		$show	.=	"WAYBILL";
																		$show	.=	"</td>";
																				$show	.=	"<td width='10%'  align='center' class='Text_header'>";
																				$show	.=	"WEIGHT";
																				$show	.=	"</td>";
					$show	.=	"</tr>";
					$show	.=	"<tr>";
						$show	.=	"<td width='8%' align='center' class='Text_header'>";
						$show	.=	"NUMBER";
						$show	.=	"</td>";
								$show	.=	"<td width='8%' align='center' class='Text_header'>";
								$show	.=	"NUMBER";
								$show	.=	"</td>";
										$show	.=	"<td width='8%' align='center' class='Text_header'>";
										$show	.=	"LINE";
										$show	.=	"</td>";
												$show	.=	"<td width='8%' align='center' class='Text_header'>";
												$show	.=	"OF CTN";
												$show	.=	"</td>";
														$show	.=	"<td width='10%' align='center' class='Text_header'>";
														$show	.=	"CTN";
														$show	.=	"</td>";
																$show	.=	"<td width='13%'  align='center' class='Text_header'>";
																$show	.=	"NUMBER";
																$show	.=	"</td>";
																			$show	.=	"<td width='10%'  align='center' nowrap class='Text_header'>";
																			$show	.=	"BY KILO";
																			$show	.=	"</td>";
														
					$show	.=	"</tr>";
					foreach ($aData as $transeq=>$val_custcode)
					{
						foreach ($val_custcode as $custcode=>$val_invoice) 
						{
							foreach ($val_invoice as $invoice=>$val)
							{
								$Street			=	self::Select_val($conn1,FDCRMS,"customer_address","StreetNumber","CUSTNO= '$custcode'");
								$Town			=	self::Select_val($conn1,FDCRMS,"customer_address","TownCity","CUSTNO= '$custcode'");
								$Custname_addr	=	self::CustName($conn1,$custcode)."<br>".$Street.' '.$Town;
								$show	.=	"<tr>";
									$show	.=	"<td width='25%' align='left' class='Text_header'>";
									$show	.=	$Custname_addr;
									$show	.=	"<input type='hidden' name='hdn_cust_PROVINCE_$cnt' id='hdn_cust_PROVINCE_$cnt' value='$custcode'";
									$show	.=	"</td>";
										$show	.=	"<td width='8%' align='right' class='Text_header'>";
										$show	.=	$invoice;
										$show	.=	"<input type='hidden' name='hdn_invoice_PROVINCE_$cnt' id='hdn_invoice_PROVINCE_$cnt' value='$invoice'";
										$show	.=	"</td>";
												$show	.=	"<td width='8%' align='right' class='Text_header'>";
												$show	.=	$val['SOFNO'];
												$show	.=	"</td>";
														$show	.=	"<td width='8%' align='center' class='Text_header'>";
														$show	.=	$val['PRODUCT_LINE'];
														$show	.=	"</td>";
																$show	.=	"<td width='8%' align='center' onclick=focus_here('txt_PROVINCE_size_$cnt'); class='Text_header'>";
																$show	.=	"<input type='text' name='txt_PROVINCE_size_$cnt' id='txt_PROVINCE_size_$cnt' value='".$val['SIZE_OF_CTN']."' size='5' style='text-align:center;border:1;' maxlength='4' onkeyup='isnumeric(this.value,this.id);'>";
																$show	.=	"</td>";
																		$show	.=	"<td width='10%' align='center' onclick=focus_here('txt_PROVINCE_ctn_$cnt'); class='Text_header'>";
																		$show	.=	"<input type='text' name='txt_PROVINCE_ctn_$cnt' id='txt_PROVINCE_ctn_$cnt' value='".$val['QTY_CTN']."' size='5' style='text-align:center;border:1;' maxlength='4' onkeyup='isnumeric(this.value,this.id);'>";
																		$show	.=	"</td>";
																				$show	.=	"<td width='10%'  align='center'  onclick=focus_here('txt_PROVINCE_dr_$cnt'); class='Text_header'>";
																				$show	.=	"<input type='text' name='txt_PROVINCE_dr_$cnt' id='txt_PROVINCE_dr_$cnt' value='".$val['DR_NO']."' size='5' style='text-align:center;border:1;' maxlength='7'>";
																				$show	.=	"</td>";
																					$show	.=	"<td width='13%'  align='center' onclick=focus_here('txt_PROVINCE_bill_$cnt');  class='Text_header'>";
																					$show	.=	"<input type='text' name='txt_PROVINCE_bill_$cnt' id='txt_PROVINCE_bill_$cnt' value='".$val['WAYBILL_NUMBER']."' size='5' style='text-align:center;border:1;' maxlength='10'>";
																					$show	.=	"</td>";
																							$show	.=	"<td width='10%'  align='center' onclick=focus_here('txt_PROVINCE_kilo_$cnt'); class='Text_header'>";
																							$show	.=	"<input type='text' name='txt_PROVINCE_kilo_$cnt' id='txt_PROVINCE_kilo_$cnt' value='".$val['WEIGHT_BY_KILO']."' size='5' style='text-align:center;border:1;' maxlength='4'>";
																							$show	.=	"</td>";
								$show	.=	"</tr>";
								$cnt++;
							}
						}
					}			
			/*END OF DATA SELECTED DISPLAY*/
			/******************************************************************/
			$show	.=		"<tr>";
			$show	.=			"<td width='25%' align='left' class='Text_header'>";
			$show	.=				"SPECIAL INSTRUCTION";
			$show	.=			"</td>";
			$show	.=			"<td width='75%' align='left' class='Text_header' colspan='8'>";
			$show	.=				"<input type = 'text' name='txtinstruction_PROVINCE' id='txtinstruction_PROVINCE' value='$special_' size='50'>";
			$show	.=			"<td>";
			$show	.=		"</tr>";
			$show	.=	"</table>";
			//$show	.=	"</form>";
		}
		/*SUBMIT BUTTONS*/
		$show	.=	"<table width='100%' border='0' class='d0'>";
		$show	.=		"<tr>";
		$show	.=			"<td width='100%' align='center'>";
		$show	.=				"<input type='button' name='btnsave' id='btnsave' value='Save' onClick=SaveDispatch_temp('$transeq','$type',$cnt) class='small_button'>";
		$show	.=				"<input type='button' name='btnconfirm' id='btnconfirm' value='Confirm' onClick=SaveDispatch('$transeq','$type',$cnt) class='small_button'>";
		$show	.=				"<input type='button' name='btnCancel' id='btnCancel' value='Cancel' onClick=Cancel(); class='small_button'>";
		$show	.=			"</td>";
		$show	.=		"</tr>";
		$show	.=	"</table>";
		$show	.=	"</form>";
		
		/*END OF SUBMIT BUTTONS*/
//		unset($data);
		return $show;
		}
		
		
		/**
		 * Concat string
		 *
		 * @param $string
		 * @param $length
		 * @return string
		 */
		function str_concat($string,$length=25)
		{
			$count = 0;
			$newstring = array();
			if(strlen($string) <= $length)
			{
				$newstring['compress'][] = $string;
			}
			else 
			{
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
						$newstring['compress'][] = $nstring;
						$nstring = "";
						$count = 0;
					}
					elseif ($count > $length)
					{
						$newstring['compress'][] = $nstring;
						$nstring = $value;
						$count = strlen($value);
					}
				}
				if($nstring != "")
				{
					$newstring['compress'][] = $nstring;
				}
			}
			return $newstring;
		}
		
		public function TRANSEQ_IA($conn)
		{
			$today		=	date('Y-m-d');
			$sel_today	=	"select * from WMS_NEW.TRANSEQ_IA WHERE SUBSTRING(DATE,1,10) = '{$today}' ORDER BY COUNTER DESC LIMIT 1";
			$rssel_today=	$conn->Execute($sel_today);
			if ($rssel_today==false) 
			{
				echo $conn->ErrorMsg()."::".__LINE__;exit();
			}
			$record		=	$rssel_today->RecordCount();
			if ($record>0) 
			{
				$transeq		=	$rssel_today->fields['COUNTER']+1;
				$insert_data	=	"INSERT INTO WMS_NEW.TRANSEQ_IA(`COUNTER`,`DATE`)VALUES('{$transeq}',SYSDATE()) ";
				$rsinsert_data	=	$conn->Execute($insert_data);
				if ($rsinsert_data==false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;exit();
				}
			}
			else 
			{
				$insert_data	=	"INSERT INTO WMS_NEW.TRANSEQ_IA(`COUNTER`,`DATE`)VALUES('1',SYSDATE()) ";
				$rsinsert_data	=	$conn->Execute($insert_data);
				if ($rsinsert_data==false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;exit();
				}
				$transeq		=	1;
			}
			
			$retval	=	"IA".date('Ymd').str_pad($transeq,3,"0",STR_PAD_LEFT);
			
			return $retval;
		}
		
		public function TRANSEQ_IAM($conn)
		{
			$today		=	date('Y-m-d');
			$sel_today	=	"select * from WMS_NEW.TRANSEQ_IAM WHERE SUBSTRING(DATE,1,10) = '{$today}' ORDER BY COUNTER DESC LIMIT 1";
			$rssel_today=	$conn->Execute($sel_today);
			if ($rssel_today==false) 
			{
				echo $conn->ErrorMsg()."::".__LINE__;exit();
			}
			$record		=	$rssel_today->RecordCount();
			if ($record>0) 
			{
				$transeq		=	$rssel_today->fields['COUNTER']+1;
				$insert_data	=	"INSERT INTO WMS_NEW.TRANSEQ_IAM(`COUNTER`,`DATE`)VALUES('{$transeq}',SYSDATE()) ";
				$rsinsert_data	=	$conn->Execute($insert_data);
				if ($rsinsert_data==false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;exit();
				}
			}
			else 
			{
				$insert_data	=	"INSERT INTO WMS_NEW.TRANSEQ_IAM(`COUNTER`,`DATE`)VALUES('1',SYSDATE()) ";
				$rsinsert_data	=	$conn->Execute($insert_data);
				if ($rsinsert_data==false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;exit();
				}
				$transeq		=	1;
			}
			
			$retval	=	"IAM".date('Ymd').str_pad($transeq,3,"0",STR_PAD_LEFT);
			
			return $retval;
		}
		
		public function TRANSEQ_RF($conn)
		{
			$today		=	date('Y-m-d');
			$sel_today	=	"select * from WMS_NEW.TRANSEQ_RF WHERE SUBSTRING(DATETIME,1,10) = '{$today}' ORDER BY COUNTER DESC LIMIT 1";
			$rssel_today=	$conn->Execute($sel_today);
			if ($rssel_today==false) 
			{
				echo $conn->ErrorMsg()."::".__LINE__;exit();
			}
			$record		=	$rssel_today->RecordCount();
			if ($record>0) 
			{
				$transeq		=	$rssel_today->fields['COUNTER']+1;
				$insert_data	=	"INSERT INTO WMS_NEW.TRANSEQ_RF(`COUNTER`,`DATETIME`)VALUES('{$transeq}',SYSDATE()) ";
				$rsinsert_data	=	$conn->Execute($insert_data);
				if ($rsinsert_data==false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;exit();
				}
			}
			else 
			{
				$insert_data	=	"INSERT INTO WMS_NEW.TRANSEQ_RF(`COUNTER`,`DATETIME`)VALUES('1',SYSDATE()) ";
				$rsinsert_data	=	$conn->Execute($insert_data);
				if ($rsinsert_data==false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;exit();
				}
				$transeq		=	1;
			}
			
			$retval	=	date('Ymd').str_pad($transeq,3,"0",STR_PAD_LEFT);
			
			return $retval;
		}
		
		
		public function TRANSEQ_IA_DR($conn)
		{
			$today		=	date('Y-m-d');
			$sel_today	=	"select * from WMS_NEW.TRANSEQ_IA WHERE SUBSTRING(DATE,1,10) = '{$today}' ORDER BY COUNTER DESC LIMIT 1";
			$rssel_today=	$conn->Execute($sel_today);
			if ($rssel_today==false) 
			{
				echo $conn->ErrorMsg()."::".__LINE__;exit();
			}
			$record		=	$rssel_today->RecordCount();
			if ($record>0) 
			{
				$transeq		=	$rssel_today->fields['COUNTER']+1;
				$insert_data	=	"INSERT INTO WMS_NEW.TRANSEQ_IA(`COUNTER`,`DATE`)VALUES('{$transeq}',SYSDATE()) ";
				$rsinsert_data	=	$conn->Execute($insert_data);
				if ($rsinsert_data==false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;exit();
				}
			}
			else 
			{
				$insert_data	=	"INSERT INTO WMS_NEW.TRANSEQ_IA(`COUNTER`,`DATE`)VALUES('1',SYSDATE()) ";
				$rsinsert_data	=	$conn->Execute($insert_data);
				if ($rsinsert_data==false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;exit();
				}
				$transeq		=	1;
			}
			
			$retval	=	"IAD".date('Ymd').str_pad($transeq,3,"0",STR_PAD_LEFT);
			
			return $retval;
		}
		
		
		/**
		 * ADDED DATE : 2016-07-13
		 */
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
	}
?>