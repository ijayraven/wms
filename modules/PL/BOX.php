<?php
/********************************************************************************************************************
* FILE NAME :	scheduler.php																						*
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
* 2013/08/02	Raymond A. Galaroza																					*
* 																													*
* ALGORITHM(pseudocode)																								*
* 																													*
*********************************************************************************************************************/
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../index.php'</script>";
}

$action	=	$_GET['action'];
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

if ($action=='DISPLAY') 
{
	$page		=	$_GET['PAGE'];
	
	$PL			=	$_GET['PL'];
	$CUSTCODE	=	$_GET['CUSTCODE'];
	$cnt_h		=	"SELECT	OrderNo from orderheader where OrderStatus = 'Confirmed' and FILLED = 'N' and OrderDate >= '2014-09-01' ";
	if (!empty($PL))
	{
	$cnt_h		.=	"and PickListNo = '{$PL}' ";
	}
	if (!empty( $CUSTCODE)) 
	{
	$cnt_h		.=	"and CustNo = '{$CUSTCODE}' ";
	}
	else 
	{
	$cnt_h		.=	"and CustNo in
	('108041',	'107326',	'108829',	'108644',	'107801',	'108395',	'105049',	'105012',	'105014',	'105043',	'108158',	'108759',	'106037',	'105065',
	'107248',	'107034',	'108182',	'107002',	'108148',	'109148',	'108756',	'105026',	'105001',	'105003',	'105004',	'108580',	'109423',	'107918',
	'108172',	'108205',	'109700',	'108322',	'109287',	'108066',	'109585',	'105052',	'105013',	'105008',	'105025',	'109140',	'108272',	'109631',
	'105064',	'108091',	'107536',	'109121',	'109455',	'105060',	'105015',	'105021',	'105016',	'105023',	'105029',	'105020',	'105046',	'101085',
	'109275',	'108174',	'107425',	'107413',	'108074',	'108839',	'108600',	'107462',	'108100',	'105010',	'105044',	'109727',	'109820',	'109031',
	'109122',	'109075',	'107546',	'108344',	'108259',	'105005',	'105006',	'105051',	'105059',	'105062',	'105063',	'105027',	'105028',	'105061',
	'105018',	'107293',	'109602',	'107896',	'108211',	'107921',	'108170',	'108164',	'107150',	'109241',	'109479',	'108429',	'108581',	'107978',
	'109692',	'109795',	'107419',	'107508',	'107573',	'107596',	'107739',	'107920',	'108184',	'108257',	'108511',	'108680',	'108818',	'109069',
	'109107',	'109478',	'109514',	'109534',	'109551',	'109814',	'109819',	'109847',	'109993',	'107308',	'106039',	'107008',	'107437',	'107461',
	'107631',	'107691',	'107733',	'107766',	'108244',	'109015',	'109058',	'109059',	'109309',	'107147',	'107358',	'107381',	'107965',	'109337',
	'109841',	'108017',	'108222',	'108475',	'108706',	'108909',	'109616',	'109797',	'107015',	'108060',	'108129',	'108721',	'109839',	'109867',
	'109868',	'107038',	'107656',	'108535',	'109412',	'109635') ";
	}
	
	$rscnt	=	$Filstar_conn->Execute($cnt_h);
	if ($rscnt==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	$cnt		=	$rscnt->RecordCount();
	if ($cnt > 0) 
	{
		$totalcount	=	ceil($cnt / PAGE_LIMIT);
		
		$sel_h		=	"SELECT	OrderNo,CustNo,PickListNo,PickListDate from orderheader where OrderStatus = 'Confirmed' and FILLED = 'N' and OrderDate >= '2014-09-01'";
		if (!empty($PL))
		{
		$sel_h		.=	"and PickListNo = '{$PL}' ";
		}
		if (!empty( $CUSTCODE)) 
		{
		$sel_h		.=	"and CustNo = '{$CUSTCODE}' ";
		}
		else 
		{
		$sel_h		.=	"and CustNo in
		('108041',	'107326',	'108829',	'108644',	'107801',	'108395',	'105049',	'105012',	'105014',	'105043',	'108158',	'108759',	'106037',	'105065',
		'107248',	'107034',	'108182',	'107002',	'108148',	'109148',	'108756',	'105026',	'105001',	'105003',	'105004',	'108580',	'109423',	'107918',
		'108172',	'108205',	'109700',	'108322',	'109287',	'108066',	'109585',	'105052',	'105013',	'105008',	'105025',	'109140',	'108272',	'109631',
		'105064',	'108091',	'107536',	'109121',	'109455',	'105060',	'105015',	'105021',	'105016',	'105023',	'105029',	'105020',	'105046',	'101085',
		'109275',	'108174',	'107425',	'107413',	'108074',	'108839',	'108600',	'107462',	'108100',	'105010',	'105044',	'109727',	'109820',	'109031',
		'109122',	'109075',	'107546',	'108344',	'108259',	'105005',	'105006',	'105051',	'105059',	'105062',	'105063',	'105027',	'105028',	'105061',
		'105018',	'107293',	'109602',	'107896',	'108211',	'107921',	'108170',	'108164',	'107150',	'109241',	'109479',	'108429',	'108581',	'107978',
		'109692',	'109795',	'107419',	'107508',	'107573',	'107596',	'107739',	'107920',	'108184',	'108257',	'108511',	'108680',	'108818',	'109069',
		'109107',	'109478',	'109514',	'109534',	'109551',	'109814',	'109819',	'109847',	'109993',	'107308',	'106039',	'107008',	'107437',	'107461',
		'107631',	'107691',	'107733',	'107766',	'108244',	'109015',	'109058',	'109059',	'109309',	'107147',	'107358',	'107381',	'107965',	'109337',
		'109841',	'108017',	'108222',	'108475',	'108706',	'108909',	'109616',	'109797',	'107015',	'108060',	'108129',	'108721',	'109839',	'109867',
		'109868',	'107038',	'107656',	'108535',	'109412',	'109635') ";
		}
		$sel_h		.=	" ORDER BY PickListDate DESC LIMIT ".($page * PAGE_LIMIT).",".PAGE_LIMIT." ";
		$rssel_h	=	$Filstar_conn->Execute($sel_h);
		if ($rssel_h==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$show	=	"<table width='100%' border='0'>";
		$show	.=	"<tr class='Header_style'>";
		$show	.=		"<td width='10%' nowrap>";
		$show	.=			"PICKLIST NO";
		$show	.=		"</td>";
		$show	.=				"<td width='15%' align='center' nowrap>";
		$show	.=					"PL DATE";
		$show	.=				"</td>";
		$show	.=						"<td width='50%' align='center' nowrap>";
		$show	.=							"CUSTOMER NAME";
		$show	.=						"</td>";
		$show	.=								"<td width='10%' align='center' nowrap>";
		$show	.=									"TOTAL RECORD";
		$show	.=								"</td>";
		$show	.=										"<td width='15%' align='center' nowrap>";
		$show	.=											"ACTION";
		$show	.=										"</td>";
		$show	.=	"</tr>";
		while(!$rssel_h->EOF) 
		{
			$OrderNo		=	$rssel_h->fields['OrderNo'];
			$CustNo			=	$rssel_h->fields['CustNo'];
			$PickListNo		=	$rssel_h->fields['PickListNo'];
			$PickListDate	=	$rssel_h->fields['PickListDate'];
			
			$sel_qty		=	"SELECT sum(`ReleaseQty`)as TOTAL_QTY from orderdetail where OrderNo = '{$OrderNo}' and isDeleted = 'N' ";
			$rssel_qty		=	$Filstar_conn->Execute($sel_qty);
			if ($rssel_qty==false) 
			{
				echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
			}
			$TOTAL_QTY		=	$rssel_qty->fields['TOTAL_QTY'];
			
			$custname		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CustNo}' ");
			
			$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" title=\"Click to view SOF No.\" class='Text_header_hover' $do_script>";
			$show	.=	"<td align='center'>";
			$show	.=		$PickListNo;
			$show	.=	"</td>";

			$show	.=	"<td align='center'>";
			$show	.=		$PickListDate;
			$show	.=	"</td>";

			$show	.=	"<td align='center' nowrap>";
			$show	.=	$CustNo."-".$custname;
			$show	.=	"</td>";

			$show	.=	"<td align='center'>";
			$show	.=		$TOTAL_QTY;
			$show	.=	"</td>";

			$show	.=	"<td align='center'>";
			$show	.=		"<img src=\"../../images/action_icon/edit-icon.gif\" title=\"Edit $OrderNo\" onclick=\"__edit('$OrderNo');\">";
			$show	.=		"&nbsp;";
			$show	.=		"<img src=\"../../images/action_icon/approve.gif\" title=\"Confirm $OrderNo\" onclick=\"__process('$OrderNo');\" style=\"height:20px;\">";
			$rssel_h->MoveNext();
		}
		
		$currentpg	=	$page	+	1;
		//$totalcount	=	$totalcount	+	1;
		$show	.=	"<tr>";
		$show	.=	"<td align='center' colspan='6'>";
		$show	.=	"<input type='button' value='first' ".($page == 0 ? "disabled" : " onclick=\"DISPLAY(0);\" ").">";
		$show	.=	"<input type='button' value='prev'  ".($page == 0 ? "disabled" : "onclick=\"DISPLAY('".($page - 1)."');\" ").">";
		$show	.=	"<input type='text' name='txtpage' id='txtpage' value='$currentpg/$totalcount' size='7' style='text-align:center;' readonly>";
		$show	.=	"<input type='button' value='next' ".(($page + 1) == $totalcount ? "disabled" : " onclick=\"DISPLAY('".($page + 1)."');\" ").">";
		$show	.=	"<input type='button' value='last' ".(($page + 1) == $totalcount ? "disabled" : " onclick=\"DISPLAY('".($totalcount - 1)."');\" ").">";
		$show	.=	"</td>";
		$show	.=	"</tr>";
		$show	.=	"</table>";
	}
	else 
	{
		$show	=	"<table width='100%' border='0'>";
		$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
		$show	.=	"<td width='100%' colspan='6' align='center'>";
		$show	.=	"<blink>NOTHING TO DISPLAY</blink>";
		$show	.=	"</td>";
		$show	.=	"</tr>";
		$show	.=	"</table>";
	}
	echo $show;
	exit();
}


if ($action=='EDITBOX') 
{
	$SOFNO			=	$_GET['SOFNO'];

	$sel_detail		=	"SELECT im.ItemNo, OrderQty, ReleaseQty, ItemNo, ItemDesc, UnitMeasure, PackCode, im.UnitPrice, GenClass, ProdGroup, ItemType, SupplementCode, im.Location, UnitMeasure, PackCode,BOXNO,
		   				(SELECT whsloc  FROM itembal WHERE itmnbr = ItemNo GROUP BY itmnbr ) AS whsloc 
		   				FROM orderdetail AS od
		   				LEFT JOIN  itemmaster AS im  ON  od.Item = im.ItemNo 
		   				WHERE od.OrderNo = '{$SOFNO}' and isDeleted = 'N'
		   				GROUP BY  im.ItemNo ";
	$sel_detail		.= " ORDER BY  whsloc  ,ProdGroup, ItemType  ,  im.UnitPrice ";
	//$sel_detail		=	"SELECT Item,OrderQty,ReleaseQty,UnitPrice,BOXNO FROM orderdetail where OrderNo = '{$SOFNO}' and isDeleted = 'N' ";
	$rssel_detail	=	$Filstar_conn->Execute($sel_detail);
	if ($rssel_detail==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	$show	.=	"<form name='data_edit' id='data_edit'>";
	$show	.=	"<table width='100%' border='0' class='d0'>";
	$show	.=		"<tr>";
	$show	.=			"<td width='100%' colspan='7' align='center'  class='Header_style'>";
	$show	.=				"ITEM LIST";
	$show	.=			"</td>";
	$show	.=		"</tr>";
	$show	.=	"<tr>";
	$show	.=		"<td width='5%' align='center'  class='Text_header'>";
	$show	.=			"<input type='button' name='btnall' id='btnall' value='ALL' onclick='CHECKALL();'>";
	$show	.=		"</td>";
	$show	.=		"<td width='15%' align='center'  class='Text_header'>";
	$show	.=			"ITEMNO";
	$show	.=		"</td>";
	$show	.=		"<td width='40%' align='center'  class='Text_header'>";
	$show	.=			"DESC";
	$show	.=		"</td>";
	$show	.=		"<td width='10%' align='center'  class='Text_header'>";
	$show	.=			"ORDER QTY";
	$show	.=		"</td>";
	$show	.=		"<td width='10%' align='center'  class='Text_header'>";
	$show	.=			"RELEASE QTY";
	$show	.=		"</td>";
	$show	.=		"<td width='10%' align='center'  class='Text_header'>";
	$show	.=			"RETAIL PRICE";
	$show	.=		"</td>";
	$show	.=		"<td width='10%' align='center'  class='Text_header'>";
	$show	.=			"BOX";
	$show	.=		"</td>";
	$show	.=	"</tr>";
	$cnt		=	0;
	while (!$rssel_detail->EOF) 
	{
		$Item		=	$rssel_detail->fields['ItemNo'];
		//$ItemDesc	=	$global_func->Select_val($Filstar_conn,FDCRMS,"itemmaster","ItemDesc","ItemNo = '".$Item."'");
		$ItemDesc	=	$rssel_detail->fields['ItemDesc'];
		$OrderQty	=	$rssel_detail->fields['OrderQty'];
		$ReleaseQty	=	$rssel_detail->fields['ReleaseQty'];
		$UnitPrice	=	$rssel_detail->fields['UnitPrice'];
		$BOXNO		=	$rssel_detail->fields['BOXNO'];
		$opt		=	'';
		if (!empty($BOXNO)) 
		{
			$opt	=	'disabled';
		}
		$show	.=	"<tr onMouseOver=\"bgColor='#00FFFF';\" onmouseout=\"bgColor='#FFFFFF';\" bgcolor=\"#FFFFFF\" class='Text_header_hover'>";
		$show	.=		"<td align='center'>";
		$show	.=			"<input type='checkbox' name='checkbox_{$cnt}' id='checkbox_{$cnt}' value='{$Item}' $opt>";
		$show	.=		"</td>";
		$show	.=		"<td align='center'>";
		$show	.=			$Item;
		$show	.=		"</td>";
		$show	.=		"<td align='left'>";
		$show	.=			$ItemDesc;
		$show	.=		"</td>";
		$show	.=		"<td align='center'>";
		$show	.=			$OrderQty;
		$show	.=		"</td>";
		$show	.=		"<td align='center'>";
		$show	.=			$ReleaseQty;
		$show	.=		"</td>";
		$show	.=		"<td align='center'>";
		$show	.=			$UnitPrice;
		$show	.=		"</td>";
		$show	.=		"<td align='center'>";
		$show	.=			"<input type='text' name='txtbox_$cnt' id='txtbox_$cnt' value='{$BOXNO}' size='10' style='text-align:center;' $opt onkeyup=\"disablebox($cnt,this.value);\">";
		$show	.=			"<input type='hidden' name='itemno_$cnt' id='itemno_$cnt' value='{$Item}' size='10' style='text-align:center;'>";
		$show	.=		"</td>";
		$show	.=	"</tr>";
		$cnt++;
		$rssel_detail->MoveNext();
	}
	$show	.=		"<td width='5%' align='center' bgcolor=\"#FFFFFF\">";
	$show	.=			"<img src=\"../../images/action_icon/add_blue.png\" onclick=\"multibox('$SOFNO');\">";
	$show	.=			"<input type='hidden' name='hdncnt' id='hdncnt' value='$cnt'>";
	$show	.=			"<input type='hidden' name='hdnopt' id='hdnopt' value=''>";
	$show	.=		"</td>";
	$show	.=	"</table>";
	$show	.=	"<div id='divedit'></div>";
	$show	.=	"</form>";
	echo $show;
	exit();
}


if ($action=='SAVEDBOX') 
{
	$BOXNO	=	$_GET['BOXNO'];
	$SOF	=	$_GET['SOF'];
	
	$Filstar_conn->StartTrans();
	$update_dtl		=	"UPDATE orderdetail set BOXNO = '{$BOXNO}' where OrderNo = '{$SOF}' and isDeleted = 'N' ";
	$rsupdate_dtl	=	$Filstar_conn->Execute($update_dtl);
	if ($rsupdate_dtl==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	
	$update_hdr		=	"UPDATE orderheader set FILLED = 'Y' where OrderNo = '{$SOF}' ";
	$rsupdate_hdr	=	$Filstar_conn->Execute($update_hdr);
	if ($rsupdate_hdr==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	$Filstar_conn->CompleteTrans();
	echo "done";
	exit();
}

if ($action=='SAVEDBOX_EDIT') 
{
	$SOF	=	$_GET['SOF'];
	$cnt	=	$_POST['hdncnt']-1;
	$x		=	0;
	$Filstar_conn->StartTrans();
	for ($x;$x<=$cnt;$x++)
	{
		$BOXNO	=	$_POST['txtbox_'.$x];
		$ITEM	=	$_POST['itemno_'.$x];
		
		$update		=	"UPDATE orderdetail set BOXNO = '{$BOXNO}' WHERE Item = '{$ITEM}' and OrderNo = '{$SOF}' ";
		$rsupdate	=	$Filstar_conn->Execute($update);
		if ($rsupdate==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
	}
	$update_hdr		=	"UPDATE orderheader SET FILLED = 'Y' WHERE OrderNo = '{$SOF}' ";
	$rsupdate_hdr	=	$Filstar_conn->Execute($update_hdr);
	if ($rsupdate_hdr==false) 
	{
		echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
	}
	$Filstar_conn->CompleteTrans();
	echo "done";
	exit();
}
?>
<html>
<title>SCHEDULER</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<style type="text/css">@import url(../../css/style.css);</style>
<style type="text/css">@import url(../../calendar/calendar-blue2.css);</style>
<script type="text/javascript" src="../../calendar/calendar.js"></script>
<script type="text/javascript" src="../../calendar/calendar-en.js"></script>
<script type="text/javascript" src="../../calendar/calendar-setup.js"></script>
<?php include ($_SERVER['DOCUMENT_ROOT'].'/wms/includes/sub_header.inc.php') ?>
<script>
</script>
</head>
<body onload="DISPLAY(0);">
		<form name="dataform" id="dataform">
			<div id="divsearch">
				<table width="100%" border="0" class="Title_style">
					<tr>
						<td width="15%">
							&nbsp;
						</td>
							<td width="15%" align="left" >
								PICKING LIST
							</td>
								<td width="75%" align="left">
								 	:&nbsp;<input type="text" name="txtPL" id="txtPL" value="" autocomplete="off" size="15">
								</td>
					</tr>
					<tr>
						<td width="15%" `>
							&nbsp;
						</td>
							<td width="15%" align="left" >
								CUSTOMER
							</td>
								<td width="75%" align="left">
								 	:&nbsp;<input type="text" name="customercode" id="customercode" value="" onkeyup="searchcust(event);" autocomplete="off" size="15">
								 	<input type="text" name="customername" id="customername" value="" onkeyup="searchcust(event);" autocomplete="off" size="40">
								 	<div id="divcust" style="position:absolute;"></div>
									<input type="hidden" id="hdnval" name="hdnval" value="">
								</td>
					</tr>
					<tr>
						<td width="100%" colspan="3" align="center">
							<input type="button" name="btndisplay" id="btndisplay" value="SEARCH" class="small_button" onclick="DISPLAY(0);">
							<input type="hidden" name="txtcontainer" id="txtcontainer" value="">
						</td>
					</tr>
					<tr>
						<td width="100%" colspan="4" align="center">
							<div id="divloader" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
						</td>
					</tr>
				</table>
			</div>
			<table width="100%" border="0">
				<tr>
					<td colspan="5">
						<div id="divlist"></div>
					</td>
				</tr>
			</table>
			<table width="100%" border="0">
				<tr>
					<td>
						<div id="divresponse" style="display:none;" class="d0"></div>
					</td>
				</tr>
				<tr>
					<td width="100%" align="center">
						<div id="divloader_response" style="display:none;"><img src="../../images/loading/ajax-loader_fast.gif"></div>
					</td>
				</tr>
			</table>
		</form>
		<div id="divdebug"></div>
		<div id="dialog_show_pl" title="BOX NO." style="display:none;" align="center"><input type="text" name="txtboxno" id="txtboxno" value=""></div>
		<div id="dialog_show_pl_edit" title="PICKING LIST" style="display:none;" align="center"></div>
		<div id="dialog_show_pl_edit_box" title="BOX NO." style="display:none;" align="center"><input type="text" name="txtboxnoall" id="txtboxnoall" value=""></div>
		<div id="dialog_show_saving" title="SAVING..." style="display:none;" align="center"><img src="../../images/loading/ajax-loader_fast.gif"></div>
	</body>
</html>
<script>
function DISPLAY(val_page)
{
	if(val_page=='')
	{
		val_page	=	0;
	}
	var	PL			=	$('#txtPL').val();
	var custcode	=	$('#customercode').val();
	var custname	=	$('#customername').val();
	
	$.ajax({
		url		:	'BOX.php?action=DISPLAY&PL='+PL+'&CUSTCODE='+custcode+'&PAGE='+val_page,
		success	:	function(response)
		{
			$('#divlist').html(response);
			$('#divlist').show();
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
					url			:	'BOX.php?action=SEARCHCUST&CUSTCODE='+custcode+'&CUSTNAME='+custname,
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
	
	
	function	__edit(val_sof)
	{
		$('#txtcontainer').val(val_sof);
		
		$.ajax({
				url			:	'BOX.php?action=EDITBOX&SOFNO='+val_sof,
				boferoSend	:	function()
							{
								$('#divloader_response').show();
							},
				success		:	function(response)
							{
								$('#divloader_response').hide();
								$('#dialog_show_pl_edit').dialog('open');
								$('#dialog_show_pl_edit').html(response);
							}
		});
	}
	
	
	function	__process(val_sof)
	{
		$('#txtcontainer').val(val_sof);
		$('#txtboxno').val('');
		$('#dialog_show_pl').dialog('open');
	}
	
	function	CHECKALL()
	{
		var	checkopt	=	$('#hdnopt').val();
		var total_cnt	=	$('#hdncnt').val();
		var	start_cnt	=	0;
		if(checkopt=='')
		{
			for(start_cnt;start_cnt<=total_cnt;start_cnt++)
			{
				if($('#txtbox_'+start_cnt).val() == '')
				{
					$('#checkbox_'+start_cnt).attr("checked", true);
				}
			}
			$('#hdnopt').val('checked');
		}
		else
		{
			for(start_cnt;start_cnt<=total_cnt;start_cnt++)
			{
				if($('#txtbox_'+start_cnt).val() == '')
				{
					$('#checkbox_'+start_cnt).attr("checked", false);
				}
			}
			$('#hdnopt').val('');
		}
	}
	
	function	multibox(val_sof)
	{
		var total_cnt	=	$('#hdncnt').val();
		var	start_cnt	=	0;
		var doprocess	=	'no';
 		total_cnt	=	total_cnt-1;
		for(start_cnt;start_cnt<=total_cnt;start_cnt++)
		{
			var	isCheck	=	$('#checkbox_'+start_cnt).is(":checked");
			if(isCheck==true)
			{
				doprocess	=	'YES';
			}
		}
		
		if(doprocess=='YES')
		{
			$('#txtboxnoall').val('');
			$('#dialog_show_pl_edit_box').dialog('open');
		}
		else
		{
			alert('Please select atleast one!');
		}
	}
	
	
	function	disablebox(val_count,val__)
	{
		if(val__ != '')
		{
			$('#checkbox_'+val_count).attr("disabled", true);
		}
		else
		{
			$('#checkbox_'+val_count).attr("disabled", false);
		}
	}
	
	$("#dialog_show_pl").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 100, width: 200, modal:true, autoOpen: false,	draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
	buttons: {
		'CLOSE': function()
		{
			$('#txtcontainer').val('');
			$(this).dialog('close');
		},
		'SUBMIT': function()
		{
			var isSubmit	=	confirm('Are sure you want to submit?');
			if(isSubmit==true)
			{
				var boxno	=	$('#txtboxno').val();
				var sof		=	$('#txtcontainer').val();
				if(boxno!='')
				{
					$.ajax({
							url		:	'BOX.php?action=SAVEDBOX&BOXNO='+boxno+'&SOF='+sof,
							success	:	function(response)
									{
										if(response=='done')
										{
											alert('BOX NO. was successfully saved');
											$('#txtcontainer').val('');
											DISPLAY(0);
											$('#dialog_show_pl').dialog('close');
										}
										else
										{
											alert(response);
										}
									}
					});
				}
				else
				{
					alert('Plesae insert BOX NO.');
				}
			}
		}
	}
	});
	
	
	$("#dialog_show_pl_edit").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 700, width: 900, modal:true, autoOpen: false,	draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
	buttons: {
		'CLOSE': function()
		{
			$('#txtcontainer').val('');
			$(this).dialog('close');
		},
		'SUBMIT': function()
		{
			var isSubmit	=	confirm('Are sure you want to submit?');
			if(isSubmit==true)
			{
				var sof			=	$('#txtcontainer').val();
				var	dataform	=	$('#data_edit').serialize();
				var total_cnt	=	$('#hdncnt').val();
				var	start_cnt	=	0;
				var doprocess	=	'YES';
	 			total_cnt	=	total_cnt-1;
	 			for(start_cnt;start_cnt<=total_cnt;start_cnt++)
				{
					var	boxno	=	$('#txtbox_'+start_cnt).val();
					if(boxno=='')
					{
						doprocess	=	'NO';
					}
				}
				if(doprocess=='YES')
				{
					$.ajax({
							type		:	'POST',
							data		:	dataform,
							url			:	'BOX.php?action=SAVEDBOX_EDIT&SOF='+sof,
							beforeSend	:	function()
										{
											$('#dialog_show_saving').dialog('open');
										},
							success		:	function(response)
										{
											$('#dialog_show_saving').dialog('close');
											if(response=='done')
											{
												alert('BOX NO. was successfully saved');
												$('#txtcontainer').val('');
												DISPLAY(0);
												$('#dialog_show_pl_edit').dialog('close');
											}
											else
											{
												$('#divedit').html(response);
												$('#divedit').show(response);
											}
										}
					});
				}
				else
				{
					alert('Please complete all boxno!');
				}
			}
		}
	}
	});
	
	$("#dialog_show_pl_edit_box").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 100, width: 200, modal:true, autoOpen: false,	draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
	buttons: {
		'CLOSE': function()
		{
			$('#txtcontainer').val('');
			$(this).dialog('close');
		},
		'SUBMIT': function()
		{
			var total_cnt	=	$('#hdncnt').val();
			var	start_cnt	=	0;
			var doprocess	=	'no';
	 		total_cnt	=	total_cnt-1;
	 		var boxno	=	$('#txtboxnoall').val();
			for(start_cnt;start_cnt<=total_cnt;start_cnt++)
			{
				var	isCheck	=	$('#checkbox_'+start_cnt).is(":checked");
				if(isCheck==true)
				{
					$('#txtbox_'+start_cnt).val(boxno);
				}
			}
			
			var	start_cnt_2	=	0;
			for(start_cnt_2;start_cnt_2<=total_cnt;start_cnt_2++)
			{
				var	isCheck	=	$('#checkbox_'+start_cnt_2).is(":checked");
				if(isCheck==true)
				{
					$('#checkbox_'+start_cnt_2).attr("checked", false);
					$('#txtbox_'+start_cnt_2).val(boxno);
					$('#checkbox_'+start_cnt_2).attr("disabled", true);
				}
				
			}
			$(this).dialog('close');
		}
	}
	});
	
	$("#dialog_show_saving").dialog({
	dialogClass: "no-close",
	bgiframe:true, resizable:false, height: 100, width: 200, modal:true, autoOpen: false,	draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 },
	buttons: {
		'CLOSE': function()
		{
			$('#txtcontainer').val('');
			$(this).dialog('close');
		}
	}
	});
</script>