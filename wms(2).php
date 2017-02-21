<?php
/********************************************************************************************************************
* FILE NAME :	wms.php																								*
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
	echo "<script>location='index.php'</script>";
}
// if($global_func->GetAccess($Filstar_conn,$_SESSION['username'],"DISPATCH","SCHEDULER"))
//{
////	echo "yes";
//}	
?>
<html>
<head>
<title>FDC WMS</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- Designed by Noli R. Gones //-->
<!--menu script //-->
<script language="JavaScript" src="script/menu.js"></script>
<script language="JavaScript" src="script/menu_items.js"></script>
<script language="JavaScript" src="script/menu_tpl.js"></script>
<link rel="stylesheet" href="style/menu.css">
<style type="text/css">
#page-container {
margin:0 0;
width: 160px;
text-align: left;
}

.close {
	display:none;	
}

.open {
	display:compact;
}
</style>
<link rel="stylesheet" type="text/css" media="all" href="style/nav_v.css" />
<script src="script/disabled.js" language="JavaScript" type="text/javascript"></script>
<!--[if gte IE 5.5]>
<script language="JavaScript" src="script/nav_v.js" type="text/JavaScript"></script>
<![endif]-->
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="background-color: #82bafc;">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" style="background-image:url(images/mainbg.gif); background-repeat:repeat-y; background-position:left;">
	<tr>
		<td height="62" valign="top">
			<table width="100%" height="62" border="0" cellpadding="0" cellspacing="0" style="background-image: url(images/topbanbg.gif); background-repeat:repeat-x;">
				<tr>
					<td style="background-image: url(images/toplogo.gif); background-repeat:no-repeat; background-position:left top;"></td>
				</tr>
	  		</table>
	    </td>
	</tr>
	<tr>
		<td valign="top">
			<table width="100%" height="24" border="0" cellpadding="0" cellspacing="0" style="background-image:url(images/menubg.gif); background-repeat: repeat-x;">
				<tr>
					<!-- main menu //-->
					<td align="right" style="font-family: Arial, Helvetica, sans-serif; font-size:18px; font-weight:bold; color:#6CD9C3; text-align:right;">
						<?php echo $global_func->Login_user($Filstar_conn,$_SESSION['username']); ?>
						<!--<marquee behavior="scroll" direction="right">Hi <?php echo $global_func->Login_user($Filstar_conn,$_SESSION['username']); ?>!</marquee>-->
					</td>
				</tr>
			</table>
    	</td>
	</tr>
	<tr>
		<td height="100%">
		<!--mid table start-->
			<table height="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td height="100%" id="menu">
						<!-- ########################### menu ################################# -->
						<table id="menu_table" height="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<td bgcolor="#1c54a0" valign="top">
									<div id="page-container">
									  <ul id="navmenu-v">
                                    	<li>
                                    		<a href="modules/PL/index.php" target="mainFrame"><strong>NBS-PL</strong></a>
                                    	</li>
                                    	<?php if ($_SESSION['username'] == 'raymond' || $_SESSION['username'] == 'badz' || $_SESSION['username'] == '14-0872' || $_SESSION['username'] == '130840' || $_SESSION['username'] == 'mpcpjrm' || $_SESSION['username'] == '987322' || $_SESSION['username'] == '950129') { ?>
                                		<li><a target="mainFrame"><strong>REPORTS</strong></a>
                                			<ul>
												<li><a>SEASON</a>
													<ul>
														<li><a href="modules/reports/SEASON/index.php" target="mainFrame">CHRISTMAS ALLOC</a></li>
														<li><a href="modules/reports/SEASON/index_valentine.php" target="mainFrame">VALENTINE ALLOC</a></li>
														<li><a href="modules/reports/SEASON/index_bts.php" target="mainFrame">BTS ALLOC</a></li>
														<li><a href="modules/reports/SEASON/index_mothers.php" target="mainFrame">MOTHER'S DAY ALLOC</a></li>
													</ul>
												</li>
												<?php if($_SESSION['username'] == 'raymond' ||$_SESSION['username'] == 'pplac' || $_SESSION['username'] == 'badz'){ ?>
												<li><a href="modules/reports/iteminquiry/index.php" target="mainFrame">ITEM INQUIRY</a></li>
												<?php }?>
												<li><a href="modules/reports/TGAS/index.php" target="mainFrame">T.G.A.S DOWNLOAD</a></li>
												<li><a>CONFIRMED DELIVERIES</a>
													<ul>
														<li><a href="modules/reports/SKU_SUMMARY" target="mainFrame">SKU Summary</a></li>
														<li><a href="modules/reports/STF_INV" target="mainFrame">STF/Invoice</a></li>
													</ul>
												</li>
												<li><a href="modules/reports/DELIVERY_RETURNS" target="mainFrame">DELIVERIES/RETURNS</a></li>
												<?php if($_SESSION['username'] == 'raymond' || $_SESSION['username'] == '14-0872') { ?>
												<li><a href="modules/reports/ORDER_STATUS" target="mainFrame">ORDER STATUS</a></li>
												<?php } ?>
											</ul>
                                    	</li>
                                    	<?php } ?>
                                    	<li><a target="mainFrame"><strong>TIANGGE</strong></a>
                                    		<ul>
                                    			<li><a href="modules/reports/tiangge/index.php" target="mainFrame">REPORT</a></li>
                                    			<li><a href="modules/tiangge/index.php" target="mainFrame">TRANSACTION</a></li>
                                    		</ul>
                                    	</li>
                                    	<?php if ($_SESSION['username'] == 'raymond' or $_SESSION['username'] == 'badz' ) { ?>
                                    	<li><a href="#"><strong>ITEM ASSEMBLY</strong></a>
											<ul>
												<li><a href="modules/itemassembly/index_itemassembly.php" target="mainFrame">Assembly</a></li>
											</ul>
										</li>
								       	<?php } ?>
								       	<?php if ($_SESSION['username'] == 'raymond' || $_SESSION['username'] == 'whronie' || $_SESSION['username'] == 'whmark' || $_SESSION['username'] == '950129' || $_SESSION['username'] == '14-0872') { ?>
								       	<li><a href="#"><strong>CONFIRM DELIVERY</strong></a>
											<ul>
												<li><a href="#">TRANSACTION</a>
													<ul>
														<li><a href="modules/confirm_delivery/index.php" target="mainFrame">STF/INVOICE</a></li>
														<?php if ($_SESSION['username'] == 'raymond') { ?>
														<li><a href="modules/confirm_delivery_ref_doc/index.php" target="mainFrame">REF DOC</a></li>	
														<?php } ?>
														
														<!--<a href="modules/confirm_delivery_ref_doc/index.php" target="mainFrame"><strong>REF DOC</strong></a>-->
													</ul>
												</li>
												<li><a href="#">REPORTS</a>
													<ul>
														<li><a href="modules/reports/CONFIRMED_DELIVERY/index.php" target="mainFrame">TRANSMITTAL</a></li>
														<li><a href="modules/reports/CONFIRMED_DELIVERY/index_summary.php" target="mainFrame">SUMMARY</a></li>
													</ul>
												</li>
											</ul>
										</li>		
										<?php } ?>
										<?php if ($_SESSION['username'] == 'raymond' || $_SESSION['username'] == 'cgherrera'|| $_SESSION['username'] == '940091' ||$_SESSION['username'] == 'badz' || $_SESSION['username'] == 'whpdb' || $_SESSION['username'] == '950129' || $_SESSION['username'] == '14-0872') { ?>
										<li><a href="#"><strong>INVENTORY ADJ</strong></a>
											<ul>
												<li>
													<a href="modules/inventory_adj/index.php" target="mainFrame">TRANSACTIONS</a>
												</li>
												<li>
													<?php if ($_SESSION['username'] == 'raymond' || $_SESSION['username'] == 'badz' || $_SESSION['username'] == 'cgherrera' || $_SESSION['username'] == '940091') {?>
													<a href="#" >REPORT</a>
													<ul>
														<li><a href="modules/inventory_adj/index_detail.php" target="mainFrame">Detail</a></li>
														<li><a href="modules/inventory_adj/index_summary.php" target="mainFrame">Summary</a></li>
													</ul>
													<?php } ?>
												</li>
											</ul>
										</li>
										<?php } if ($_SESSION['username'] == 'raymond') { ?>
										<li><a href="modules/nbs_document/index.php" target="mainFrame"><strong>RECEIVING FORM</strong></a></li>
										<li><a href="#"><strong>INVOICE GENERATION</strong></a>
											<ul>
												<li><a href="modules/invoice_generation/index.php" target="mainFrame">NBS</a></li>
											</ul>
										</li>
										<?php } ?>
										<li><a href="#"><strong>RETURNS</strong></a>
											<ul>
												<li><a href="#">TRANSACTIONS</a>
													<ul>
														<?php if($_SESSION['SCANNING']){ ?>
														<li><a href="modules/returns/transactions/scanning" target="mainFrame">MPOS SCANNING</a></li>
														<?php }?>
														<li><a href="modules/returns/transactions/mto" target="mainFrame">RETURNS MTO</a></li>
													</ul>
												</li>
												<li><a href="#">REPORTS</a>
													<ul>
														<li><a href="modules/returns/reports/returns_monitoring" target="mainFrame">MPOS MONITORING</a></li>
														<li><a href="modules/returns/reports/returns_monitoring_peritem" target="mainFrame">MPOS MONITORING/ITEM</a></li>
														<li><a href="modules/returns/reports/returns_monitoring_persr" target="mainFrame">MPOS MONITORING/SR</a></li>
														<li><a href="modules/returns/reports/variance" target="mainFrame">VARIANCE</a></li>
														<li><a href="modules/returns/reports/variance_old_scanning" target="mainFrame">VARIANCE OLD SCANNING</a></li>
														<li><a href="modules/returns/reports/mpos_transmittal" target="mainFrame">MPOS TRANSMITTAL</a></li>
														<li><a href="modules/returns/reports/reprint_mpos_transmittal" target="mainFrame">REPRINT MPOS TRANSMITTAL</a></li>
													</ul>
												</li>
												
												
											</ul>
										</li>
										<li><a href="#"><strong>MAINTENANCE</strong></a>
											<ul>
												<?php if($_SESSION['NONMTO']){ ?>
												<li><a href="modules/returns/maintenance/nonmtoitems" target="mainFrame">NON-MTO ITEMS</a></li>
												<?php }?>
											</ul>
										</li>
										<?php  if ($_SESSION['username'] == 'raymond' || $_SESSION['username'] == 'badz') { ?>
										<li><a href="#"><strong>USER MAINTENANCE</strong></a>
											<ul>
											
														<li><a href="modules/userconfig/userconfig.php" target="mainFrame">CONFIG</a></li>
														
											</ul>
										</li>
										<?php } ?>
										<li>
										<?php // if($global_func->GetAccess($Filstar_conn,$_SESSION['username'],"GENERAL","LOGOUT")){?>
											<a href="logout.php" target="_self"><strong>Logout</strong></a>
										<?php // }?>
										</li>
									  </ul>
									</div>
								</td>
							</tr>
						</table>
						<!-- ###########################  end of menu ########################## -->
					</td>
					<td align="left" bgcolor="#82bafc"><img src="images/close_vert.gif" style="cursor:pointer" onClick="Close(this);" title="Menu On/Off"></td>
					<td width="1600" id="blank" align="left">
						<iframe height="100%" width="100%" name="mainFrame" frameborder="0" scrolling="auto" allowtransparency="yes" src="table.html"></iframe>
					</td>
				</tr>
			</table>
			<!--mid table start-->
    	</td>
	</tr>
	<tr>
		<td valign="bottom">
			<table width="100%" height="16" border="0" cellpadding="0" cellspacing="0" style="background-image:url(images/botbg.gif); background-color:#0a3e89; background-repeat:repeat-y; background-position:left;">
				<tr>
					<td style="font-family:Arial, Helvetica, sans-serif; font-size:9px; color:#81bafd; text-align:right;">Copyright &copy; 2013 Data Edge Corporation. All rights reserved.&nbsp;&nbsp;&nbsp;</td>
				</tr>
			</table>
    	</td>
	</tr>
</table>
</body>
</html>
<!--start script for folding menu//-->					
<script>

	function Close(obj)
	{
		//alert(obj.src.indexOf("images/close_vert.gif"));
		if (obj.src.indexOf("images/close_vert.gif") != -1) 
		{
				obj.src = obj.src.replace("images/close_vert.gif","images/open_vert.gif");
				parent.document.getElementById("menu_table").className='close';
				x = parent.document.getElementById("menu").width +10;
				parent.document.getElementById("menu").width=10;
				parent.document.getElementById("blank").width=parseInt(parent.document.getElementById("blank").width+x);
		}
		else
		{
				obj.src = obj.src.replace("images/open_vert.gif","images/close_vert.gif");
				parent.document.getElementById("menu_table").className='open';
				parent.document.getElementById("menu").width=0;
				parent.document.getElementById("blank").width=1600;
		}
	}
	
</script>
<!--end script for folding menu//-->
