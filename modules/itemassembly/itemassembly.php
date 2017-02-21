<?php
session_start();
include("../../adodb/adodb.inc.php");
include("../../fpdf/fpdf.php");

	$conn	=	ADONewConnection('mysqlt');
	$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
	if ($dbconn == false) 
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}
			                                                        	
	if($_GET['action'] == "do_mainsearch")
		{
			$conn	=	ADONewConnection('mysqlt');
			$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
			if ($dbconn == false) 
			{
				echo $conn->ErrorMsg()."::".__LINE__;
				exit();
			}
			$txtTransNo		= $_GET['txtTransNo'];
			$txtItemNo		= $_GET['txtItemNo'];
			$pageno			= $_GET['pageno'];
			
			$limit			= 10;
			$where			= "WHERE 1 AND STATUS!='Saved'";
				
				if (!empty($txtTransNo))
				{
					$where .= " AND TRANSNO='{$txtTransNo}'";
				}
				
				if (!empty($txtItemNo))
				{
					$where .= " AND ITEMNO='{$txtItemNo}'";
				}
				$where.= " ORDER BY ITEMNO ASC";

			$searchcnt		= "SELECT * FROM ASSEMBLY $where ";
			$rs_searchcnt	= $conn->Execute($searchcnt);
			
			if ($rs_searchcnt == false) 
			{
				echo $conn->ErrorMsg()."::".__LINE__;exit();
			}
			$searchcnt		=	$rs_searchcnt->RecordCount();
			$totalsearchcnt	=	ceil($searchcnt/$limit);
			$from			=	$limit * $pageno;
			$where2	= " WHERE 1 AND STATUS!='Saved'";
			if (!empty($txtItemNo)) 
			{
				$where2 .= " AND ITEMNO='{$txtItemNo}'";
			}
			if (!empty($txtTransNo))
			{
				$where2 .= " AND TRANSNO='{$txtTransNo}'";	
			}
				$where2 .= " ORDER BY TRANSNO DESC";

			$search		= "SELECT * FROM ASSEMBLY $where2 ";
			$rs_search	= $conn->Execute($search);	
			if ($rs_search == false) 
			{
				echo $conn->ErrorMsg()."::".__LINE__;exit();
			}
				if($rs_search->RecordCount() > 0)
				{
					$table	=	"<table border=1 cellpadding=0 cellspacing=0 width=92% align=center>";
					while (!$rs_search->EOF)
					{
						$transno	=	$rs_search->fields['TRANSNO'];
						$itemno		=	$rs_search->fields['ITEMNO'];
						$itemdesc	=	$rs_search->fields['DESCRIPTION'];
						$cost		=	$rs_search->fields['COST'];	
						$uom		=	$rs_search->fields['UOM'];
						$brand		=	$rs_search->fields['BRAND'];
						$status		=	$rs_search->fields['STATUS'];
						$printdate	=	$rs_search->fields['PRINTED_DATE'];
						$printby	=	$rs_search->fields['PRINTED_BY'];
						$table	.=	"<tr style='background-color:ffffff; font: 12px Verdana, Arial, Helvetica, sans-serif; color:#1d311b;' onmouseover=\"$(this).css('background-color', '#ced9f9');\" onmouseout=\"$(this).css('background-color', '#ffffff');\">";
						$table	.=		"<td width=10% align='center' style='border-color:#000000;' >$transno</td>
											<td width=10% align='center' style='border-color:#007ACC;'>$itemno</td>
											<td width=30% align='center' style='border-color:#007ACC;padding:2px;'>$itemdesc</td>
											<td width=15% align='center' style='border-color:#007ACC;padding:2px;'>$uom</td>
											<td width=10% align='center' style='border-color:#007ACC;padding:2px;'>$status</td>
											<td width=10% align='center' style='border-color:#007ACC;padding:2px;'>".substr($printdate,0,10)."</td>
											<td width=8% align='center' style='border-color:#007ACC;padding:2px;'>&nbsp;$printby</td>
											<td width=13% align='center' style='border-color:#007ACC;padding:2px;'>";
												if($rs_search->fields['STATUS'] == "For Posting")
												{
													$table .=	"<img title=\"VIEW {$rs_search->fields['ITEMNO']}\" src=\"../images/action_icon/new/clipboard.png\" style=\"height:19px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncView('".$rs_search->fields['ITEMNO']."')\">&nbsp;&nbsp;";	
													$table .=	"<img title=\"APPROVE {$rs_search->fields['ITEMNO']}\" src=\"../images/action_icon/approve.gif\" class=\"action_butt\" onclick=\"fncApprove('".$rs_search->fields['ITEMNO']."')\" style=\"height:18px;vertical-align: top;\">&nbsp;&nbsp;";
												}
												if($rs_search->fields['STATUS'] == "Posted" )
												{
													$table .=	"<img title=\"VIEW {$transno}\" src=\"../../images/images/action_icon/report_blue.png\" style=\"height:19px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncView('".$rs_search->fields['ITEMNO']."','".$transno."')\">&nbsp;&nbsp;<img title=\"PRINT {$transno}\" src=\"../../images/images/action_icon/print.png\" style=\"height:19px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncPrint('".$rs_search->fields['ITEMNO']."','".$transno."')\">";	
												}
												if($rs_search->fields['STATUS'] == "Saved")
												{
													$table .=	"<img title=\"VIEW {$rs_search->fields['ITEMNO']}\" src=\"../images/action_icon/new/clipboard.png\" style=\"height:19px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncView('".$rs_search->fields['ITEMNO']."')\">&nbsp;&nbsp;";	
													$table .=	"<img title=\"EDIT {$rs_search->fields['ITEMNO']}\" src=\"../images/action_icon/new/pencil.png\" style=\"height:19px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncEdit('".$rs_search->fields['ITEMNO']."')\" style=\"vertical-align: top;\">&nbsp;&nbsp;"; 
													$table .=	"<img title=\"CANCEL {$rs_search->fields['ITEMNO']}\" src=\"../images/action_icon/new/stop.png\" style=\"height:19px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncCancel('".$rs_search->fields['ITEMNO']."')\" style=\"height:18px;vertical-align: top;\">&nbsp;&nbsp;"; 
													$table .=	"<img title=\"APPROVE {$rs_search->fields['ITEMNO']}\" src=\"../images/action_icon/new/check.png\" style=\"height:18.5px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncApprove('".$rs_search->fields['ITEMNO']."')\" style=\"height:18px;vertical-align: top;\">&nbsp;&nbsp;";
												}
												if($rs_search->fields['STATUS'] == "Cancelled" )
												{
													$table .= "&nbsp;&nbsp;";
//													$table .=	"<img title=\"VIEW {$rs_search->fields['ITEMNO']}\" src=\"../images/action_icon/report_blue.png\" style=\"height:19px;vertical-align:top;\" class=\"action_butt\" onclick=\"fncView('".$rs_search->fields['ITEMNO']."')\">&nbsp;&nbsp;";	
												}
												$table .=		"</td>";
										 		$table	.="</tr>";
						$rs_search->MoveNext();	
					} 
					$currpage	=	$pageno + 1;
					$table	.=	"<tr style='background-color:ffffff; font: 12px Verdana, Arial, Helvetica, sans-serif; color:#007ACC;'>
								<td style='border-color:#007ACC;padding:2px;' align='center' colspan='10'>
									<input type='button' value='<<'".($currpage == "1" ? "disabled" : "onclick='fncSearch(0)'")." class='navbutton'>
									<input type='button' value='<'" .($currpage == "1" ? "disabled" : "onclick='fncSearch(".($pageno-1).")'")." class='navbutton'>
										<a><b>$currpage/$totalsearchcnt</a>
									<input type='button' value='>'".($currpage == $totalsearchcnt ? "disabled" : "onclick='fncSearch(".($currpage).")'")." class='navbutton'>
									<input type='button' value='>>'" .($currpage == $totalsearchcnt ? "disabled" : "onclick='fncSearch(".($totalsearchcnt-1).")'")." class='navbutton'>
								</td>
						</tr>
						
				</table>";
			echo $table;				
//					}

				}
				else 
				{
					$table	="<table border=1 cellpadding=0 cellspacing=0 width=92% align=center><tr style='background-color:ffffff; font: 12px Verdana, Arial, Helvetica, sans-serif; color:#007ACC;' onmouseover=\"$(this).css('background-color', '#47DAB5');\" onmouseout=\"$(this).css('background-color', '#ffffff');\">
											<td rowspan=7 width=10% align='center' style='border-color:#007ACC;'>NO RECORD FOUND</td></table>";
					echo $table;
				}
			
			
			exit;
		}
		if($_GET['action'] == "do_view")
		{
					
			$transno	= $_GET['txtTransNo'];
			
			$item	=	"SELECT * FROM ASSEMBLY WHERE TRANSNO='{$transno}'";
			$rsitem		=	$conn->Execute($item);
			if ($rsitem==false)
			{
				echo $conn->ErrorMsg()."::".__LINE__;exit();
			}		
			foreach ($rsitem as $key => $value) {
				$rsTransNo 		= $value['TRANSNO'];
				$rsItemNo 		= $value['ITEMNO'];
				$rsDesc 		= $value['DESCRIPTION'];
				$rsAddedDate 	= $value['ADDED_DATE'];				
				$rsMember	 	= $value['MEMBER'];				
				$rsAddedDate2 	= substr($rsAddedDate,0,10);		
				$rsImage		= $value['IMAGE'];
				$rsImage2		= substr($rsImage,45);
				$rsUOM 			= $value['UOM'];
							
				$insssss	=	"SELECT * FROM ASSEMBLY_INSTRUCT WHERE TRANSNO='{$rsTransNo}'";
				$rs_inssss	=	$conn->Execute($insssss);
				foreach ($rs_inssss as $key => $dataIns)
				{
					$_list 			= $dataIns['INSTRUCTIONS'];
//					echo "$('#ins').val('$_list');";
				}
			}
			
			echo "$('#lblTransNo').html('{$rsTransNo}');";
			
			echo "$('#lblItemNo').html('{$rsItemNo}');";
			echo "$('#lblAddedDate').html('{$rsAddedDate2}');";
			echo "$('#lblDesc').html('{$rsDesc}');";
			echo "$('#lblMember').html('{$rsMember}');";
			echo "$('#lblUOM').html('{$rsUOM}');";
			echo "$('#imgitem').attr('src','PMS_IMAGE/$rsImage2');";
						
					
			
			
				# display details
			$itemCnt		=	"SELECT COUNT(*) FROM ASSEMBLY WHERE TRANSNO='{$rsTransNo}'";
			$rsitemCnt		=	$conn->Execute($itemCnt);
			if ($rsitemCnt==false)
			{
				echo $conn->ErrorMsg()."::".__LINE__;exit();
			}		
				
			if($rsitemCnt>0){
				$htm = rsDisplay("WHERE TRANSNO='{$rsTransNo}'",$offset,$limit,$rsItemNo);		
			}else{
				$htm  = "<table border=1 cellpadding=0 cellspacing=0 width=90% align=center>";
				$htm .= "<tr class=dtl bgcolor=#FFFFFF>";
				$htm .= 	"<td colspan=7 align=center>NO RECORD FOUND</td>";
				$htm .= "</tr>";	
				$htm .= "</table>";
			}
			$htm = addslashes($htm);
			echo "$('#divResultview').html('$htm');";
			exit();		
		}
		
					
function rsDisplay($where,$offset,$limit,$itemnum){
	$conn	=	ADONewConnection('mysqlt');
	$dbconn	=	$conn->Connect('192.168.250.171','root','','FDC_PMS');
	if ($dbconn == false) 
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}
	
	$item		=	"SELECT * FROM ASSEMBLY_DTL $where";
	$rsitem		=	$conn->Execute($item);
	$ctr = 0;
	$htm = "";
	$htm = "<table border=1 cellpadding=0 cellspacing=0 width=90% align=center>";
		$counter=1;
	foreach ($rsitem as $dataKey => $dataVal) {	
		$img	=	substr($dataVal['IMAGE'],7);
		$htm .= "<tr class=dtl bgcolor=#FFFFFF>";
		$htm .= 	"<td width=5% align=center height=22px>".$counter."&nbsp;</td>";
		$htm .= 	"<td width=10% align=center height=22px>".$dataVal['ORIG_ITEMNO']."&nbsp;</td>";
		$htm .= 	"<td width=25% align=center height=22px>".$dataVal['ORIG_DESC']."&nbsp;</td>";
		$htm .= 	"<td width=10% align=center>".$dataVal['QUANTITY']."&nbsp;</td>";
		$htm .= 	"<td width=20% align=center>".$dataVal['REMARKS']."&nbsp;</td>";
		$htm .= 	"<td width=20% align=center><a id=tooltipimg$counter href=# title=><img src=PMS_IMAGE/{$img} id =img$counter title={$dataVal['ORIG_ITEMNO']} width=30px height=30px style=cursor:pointer; onmouseover=showTP($counter);></a></td>";
//		$htm .= 	"<td width=20% align=center><a id='tooltipimg$counter' href='#' title=''>
//						<img src='src/filemaintenance/item_pix/{$dataVal['IMAGE']}' id = 'imgitem$counter' title='{$dataVal['ORIG_ITEMNO']}' width='30px' height='30px' style='cursor:pointer;' $upload onmouseover='showTP($counter);'>
//					</a></td>";
//		$htm .= 	"<td width=10% align=center> <img title=\"EDIT".$dataVal['ORIG_ITEMNO']."\" src=\"images/action_icon/edit-icon.gif\" class=\"action_butt\" onclick=\"fncEdit('".$itemnum."','".$dataVal['ORIG_ITEMNO']."')\">&nbsp;&nbsp;&nbsp;<img title=\"DELETE".$dataVal['ORIG_ITEMNO']."\" src=\"images/action_icon/delete-icon.gif\" class=\"action_butt\" onclick=\"fncDelete('".$itemnum."','".$dataVal['ORIG_ITEMNO']."')\"></td>";
		$counter++;

		$htm .= "</tr>";
	}	
	$htm .= "</table>";
	return $htm;
	
}	

if($_GET['action'] == "do_print")
		{
			$txtTransNo = $_GET['txtTransNo'];
			
			if($txtTransNo!='')
			{
				$where = "WHERE TRANSNO='{$txtTransNo}'";
			}
				
			$sel_item	=	"SELECT * FROM FDC_PMS.ASSEMBLY $where";
			$rssel_item	=	$conn->Execute($sel_item);
			if ($rssel_item == false) 
			{
				echo $conn->ErrorMsg()."::".__LINE__;
				exit();
			}
			
			if($rssel_item!='')
			{
				$datenow 		= date("Y-m-d H:i:s");
				$values = "`IS_PRINTED` = 'Y',`PRINTED_DATE` = '{$datenow}', `PRINTED_BY` = '{$_SESSION["username"]}'";
				$where	="TRANSNO='{$txtTransNo}'";
				$update_print		= "UPDATE FDC_PMS.ASSEMBLY SET $values WHERE {$where}";
				$rsupdate_print		= $conn->Execute($update_print);
				if ($rsupdate_print == false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;
					exit();
				}
				
				$sel_item2	=	"SELECT * FROM FDC_PMS.ASSEMBLY WHERE $where";
				$rssel_item2	=	$conn->Execute($sel_item2);
				if ($rssel_item2 == false) 
				{
					echo $conn->ErrorMsg()."::".__LINE__;
					exit();
				}
				$transno	= $rssel_item2->fields['TRANSNO'];		
					
										
				echo "$('#hidPrintCode').val('{$transno}');";
				echo "$('#dialog_print_ok_cancel').dialog('open');";
				echo "$('#dialog_print_ok_cancel').html('Are you sure you want to print Transaction $transno?');";			
			}	
					exit;
		}
		
?>

<html>
<title>ITEM ASSEMBLY</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link 		href=	"/wms/includes/JQUERYUI/cupertino/jquery-ui.min.css"	rel	="stylesheet">
<script 	src	=	"/wms/includes/JQUERYUI/cupertino/external/jquery/jquery.js"></script>
<script 	src	=	"/wms/includes/JQUERYUI/cupertino/jquery-ui.min.js"></script>
<script type="text/javascript" src="common/.mouseevent.js"></script>	
<style type="text/css">
.ui-dialog .ui-dialog-title {
  text-align: center;
  width: 100%;
}
body {
	font-size: 62.5%;
	font-family: "Trebuchet MS", "Helvetica", "Arial",  "Verdana", "sans-serif";
}

table {
	font-size: 1em;
}

.action_butt {
	cursor:pointer;
}

.label_text {
	font-size: 12px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #000000;
}

.labeltext{
	color: black;
	font-weight: bold;
}

.errortext{
	color: red;
	font-weight: bold;
}
.error{
	background: #f8dbdb;
	border-color: #e77776;
}

.text_color{
	background:#FDFACD;
}

.select_color{
	background:#FDFACD;
	width:200px;
}

.text_white11 {
    font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #ffffff;
}

.text_white10 {
    font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	color: #ffffff;
}

tr.dtl {
	color: #336699;
	font-size: 11px;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
}

.no-close .ui-dialog-titlebar-close {
    display: none;
}

</style>
<style type="text/css">@import url(../../css/style.css);</style>
<style type="text/css">@import url(../../calendar/calendar-blue2.css);</style>
		

<script>
function fncSearch(page)
{
	var transno	=	$('#txtTransNo').val();
	var itemno	=	$('#txtItemNo').val();
	
		$.ajax
		({
			type: 'POST',
			url : 'itemassembly.php?action=do_mainsearch&txtTransNo='+transno+'&txtItemNo='+itemno+'&pageno='+page,
			beforeSend: function()
			{
				$('#divloader').dialog("open");
			},
			success: function (html)
			{			
//				eval(html)
//				alert(html);
				$('#divResult').html(html);
				$('#divloader').dialog("close");
				$('#txtItemNo').val('');				
				$('#txtTransNo').val('');				
			}
		})	
	
}
function fncView(txtItemNo,txtTransNo)
{
	if(txtTransNo != ''){
		$('#dialog_view').data('txtTransNo',txtTransNo);
		$("#dialog_view").dialog("open");
		
		$.ajax({
		type: "POST",
		url: 'itemassembly.php?action=do_view&txtTransNo='+txtTransNo,
		beforeSend: function()
		{
				$('#divloader').dialog("open");
		},
		success: function(html) {
				eval(html);	
//				alert(html);
				$('#divloader').dialog("close");
				$('#dvSearch').html('');
			}		
		})	
	}

}

function fncPrint(txtItemNo,txtTransNo)
{
//	alert("PRINT ITEM NUMBER="+txtItemNo+" with TRANSACTION NUMBER="+txtTransNo);
	$.ajax({
		type: "POST",
		url: 'itemassembly.php?action=do_print&txtTransNo='+txtTransNo,
		beforeSend: function()
		{
				$('#divloader').dialog("open");
		},
		success: function(html) {
				eval(html);	
				$('#divloader').dialog("close");
//				alert(html);
				$('#dvSearch').html('');
			}		
		})	
}
function showTP(cnt,IMGSRC)
	{
		var IMGSRC	=	$("#img"+cnt).attr('src');

		$( "#tooltipimg"+cnt ).tooltip({ 
			content: '<img src="'+IMGSRC+'" width="200px" height="200px" />',
			position: { my: "left+3 bottom-3", at: "left center" } ,
			show: {
				    effect: "slideDown",
				    delay: 250
				  }
		});
	}
</script>
<body onload="fncSearch();">
	<form name='assembly' id="assembly">
		<table width="100%" border="0" class="Text_header">
			<tr>
				<td width="50%" align="right">TRANSACTION NO</td>
				<td width="50%" >:&nbsp;<input type="text" id="txtTransNo" maxlength="10" name="txtTransNo" onkeypress="if(event.keyCode == 13){return fncSearch();}"></td>
			</tr>
			<tr>
				<td width="50%" align="right">ITEM NO</td>
				<td width="50%" >:&nbsp;<input type="text" id="txtItemNo" name="txtItemNo" onkeypress="if(event.keyCode == 13){return fncSearch();}"></td>
			</tr>
			<tr align="center">
				<td colspan="2"><img src="../../images/images/action_icon/Search.png" width="35px" height="35px" onclick="fncSearch();"></td>
			</tr>
			<tr>
				<td colspan="2">
					<table border="0" cellpadding="1" cellspacing="1" width="92%" align="center" height="23px" >
						<tr align="center" background="../../images/images/pmscellpic3.gif">
					 		<td width="10%" class="text_white10" height="30px"><p>Transaction Number</p></td>
					 		<td width="10%" class="text_white10" height="30px"><p>Item Number</p></td>
					 		<td width="30%" class="text_white10"><p>Description</p></td>
					 		<td width="15%" class="text_white10"><p>Quantity per Pack</p></td>
					 		<td width="10%" class="text_white10"><p>Status</p></td>	
					 		<td width="10%" class="text_white10"><p>Printed Date</p></td>	
					 		<td width="8%" class="text_white10"><p>Printed By</p></td>	
					 		<td width="13%" class="text_white10"><p>ACTION</p></td>
					 	</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2">			<!--235px-->		 		
					<div id="divResult" style="height:230px;"> <?php echo $table; ?></div>			 						 		
				</td>
			</tr>
			
		</table>
		<div id="dialog_ok" title="Alert"></div>
		<?php include("dialog_assembly.php"); ?>
		<div id="dialog_print_ok_cancel" title="ITEM ASSEMBLY"></div>
		<input type="hidden" id="hidPrintCode" name="hidPrintCode" title="print code" size="100">
		<div id="divloader" style="display:none;" align="center"><img src="../../images/loading/animated-loading.gif" width="100%"><p>Please wait...</p></div>



</form>
</body>
</html>
<script>
$("#divloader").dialog({
	dialogClass: "no-close",
	closeOnEscape:false,	
	title:'Processing',
	bgiframe:true, resizable:false, height: "auto", width: 250, modal:true, autoOpen: false,draggable: false,
	overlay: { backgroundColor: '#000', opacity: 0.5 }
	});
$("#dialog_ok").dialog({
	bgiframe:true,
	resizable:false,
	modal:true,
	autoOpen: false,
	dialogClass:'no-close',
	closeOnEscape:false,
	draggable:false,
	overlay: { 
		backgroundColor: '#000', opacity: 0.5
	},
	buttons: {
		'OK': function() {
			$('#txtItemNo').val('');				
			$('#txtTransNo').val('');	
						
			$(this).dialog('close');			
		}
	}
});
$("#dialog_view").dialog({
	bgiframe:true,
	width: 950,
	height:500,
	resizable:false,
	modal:true,
	autoOpen: false,
	dialogClass:'no-close',
	closeOnEscape:false,
	draggable:false,
	overlay: { 
		backgroundColor: '#000', opacity: 0.5
	},
	buttons: {
		'OK': function() {
			$('#txtItemNo').val('');				
			$('#txtTransNo').val('');	
						
			$(this).dialog('close');			
		}
	}
});

$("#dialog_print_ok_cancel").dialog({
	bgiframe:true, resizable:false,modal:true, autoOpen: false, dialogClass:'no-close',closeOnEscape:false,	
	
	overlay: {
		backgroundColor: '#000', opacity: 0.5
	},hide: "fadeOut",
	buttons: {
		'OK': function() {
			var hidPrintCode = $("#hidPrintCode").val();
			var txtItemNo = $("#txtItemNo").val();
			
			window.open('itemassem_print.php?&hidPrintCode='+hidPrintCode);
						
			$(this).dialog('close');	
					
		},
		'CANCEL': function() {
			$('#hidPrintCode').val('');
					
			$(this).dialog('close');
		}
	}
});

</script>

