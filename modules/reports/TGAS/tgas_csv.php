<?php
include("../../../adodb/adodb.inc.php");
$conn	=	ADONewConnection('mysqlt');
$dbconn	=	$conn->Connect('192.168.250.10','root','','FDCRMSlive');
	if ($dbconn == false) 
	{
		echo $conn->ErrorMsg()."::".__LINE__;
		exit();
	}

$txtGenClass	=	$_GET['txtGenClass'];
$txtProdGroup	=	$_GET['txtProdGroup'];
$txtType		=	$_GET['txtType'];
$txtCatClass	=	$_GET['txtCatClass'];
$txtCaption		=	$_GET['txtCaption'];
$txtEqui		=	$_GET['txtEqui'];
$selStat		=	$_GET['selStat'];	

//echo "$txtGenClass<br>$txtProdGroup<br>$txtType<br>$txtCatClass<br>$txtCaption<br>$txtEqui<br>$selStat<br>";

$STRQUERY	= "SELECT im.GenClass, im.ProdGroup, im.CategoryClass, im.DeptNo,
					 im.PackCode, im.ItemNo, im.SupplementCode, LEFT(im.ItemDesc,40) AS `ItemDesc`, im.UnitPrice,
					 ib.onhqty, ib.alcqty
					 FROM itemmaster im LEFT JOIN itembal ib ON im.ItemNo = ib.itmnbr
					 WHERE ib.house = 'FDC'";
		if(!empty($txtGenClass))
		{			
			$STRQUERY .= " AND im.GenClass = '".$txtGenClass."' ";
		}
		
		if(!empty($txtProdGroup))
		{
			$STRQUERY .= " AND im.ProdGroup = '".$txtProdGroup."' ";
		}
		
		if(!empty($txtType))
		{
			$STRQUERY .= " AND im.ItemType = '".$txtType."' ";
		}
		
		if(!empty($txtCatClass))
		{
			$STRQUERY .= " AND im.CategoryClass = '".$txtCatClass."' ";
		}
		
		if(!empty($txtCaption))
		{
			$STRQUERY .= " AND im.ItemClass = '".$Caption."' "; 
		}
		if(!empty($txtEqui))
		{
			$STRQUERY .= " AND im.Equi = '".$txtEqui."' ";
		}
		if(!empty($selStat))
		{
			$STRQUERY .= " AND im.DeptNo = '".$selStat."' ";
		}
		
		
		$STRQUERY .= " ORDER BY im.DeptNo, im.ItemNo, im.GenClass,  im.ProdGroup";
		$STRRESULT	= $conn->Execute($STRQUERY);
		$output		=	";;;;;;Filstar Distributor Corporation;;;;".date("n/j/y")."\r\n";
		$output		.=	";;;;;;Total Goods Available for Sales Report;;;;".date("H:i:s")."\r\n\n\n";
		$output		.=	"GenC;Product Level;MPTH;Pack;Item#;SCode;Stock Description;Base Price;Order Qty;T.G.A.S.;TGAS Amt\r\n";
		
		
		$TempAmount = 0;
		$TempSalesRepTotal = 0;
		$TotalperStatusTGAS = 0; 
		$TotalperStatusTGASAmt = 0;
		foreach ($STRRESULT as $key =>$ROW)
		{
			 $GenClass 			= $ROW["GenClass"];
			 $ProdGroup 		= $ROW["ProdGroup"];
			 $CategoryClass		= $ROW["CategoryClass"];
			 $DeptNo			= $ROW["DeptNo"];
			 $PackCode			= $ROW["PackCode"];
			 $ItemNo 			= $ROW["ItemNo"];
			 $SupplementCode 	= $ROW["SupplementCode"];
			 $ItemDesc 			= $ROW["ItemDesc"];
			 $UnitPrice 		= $ROW["UnitPrice"];
			 $onhqty 			= $ROW["onhqty"];
			 $alcqty 			= $ROW["alcqty"];
			 $TGAS 				= $onhqty - $alcqty;
			 $TGASAmt 			= $TGAS * $UnitPrice;
			 $ItmDesc 			= explode(",",$ItemDesc); 
			 $IDesc 			= implode(" ",$ItmDesc);
			 
			 if($TempDeptNo != $DeptNo)
			 {
			 	$output .= ";;;;;;;;Total per Status:;".$TotalperStatusTGAS.";".$TotalperStatusTGASAmt."\r\n"; 
//				fwrite($handle, $tmpx);	
			 	$TotalperStatusTGAS = 0; 
			 	$TotalperStatusTGASAmt = 0;
			 }	
			$TempDeptNo = $DeptNo;
				 
			$output  .= $GenClass.";".$ProdGroup.$CategoryClass.";".$DeptNo.";".$PackCode.";'".$ItemNo."';".$SupplementCode.";".$IDesc.";".$UnitPrice.";0;".$TGAS.";".$TGASAmt."\r\n"; 
			$TotalperStatusTGAS = $TotalperStatusTGAS + $TGAS; 
			$TotalperStatusTGASAmt = $TotalperStatusTGASAmt +  $TGASAmt;
			$TotalTGAS = $TotalTGAS + $TGAS; 
			$TotalTGASAmt = $TotalTGASAmt +  $TGASAmt;
		} 

	
		$output  .= ";;;;;;;;GRAND TOTAL: ;".$TotalTGAS.";".$TotalTGASAmt."\r\n"; 
		header("Content-Disposition: attachment; filename=TGAS.csv");
		header("Content-Location: $_SERVER[REQUEST_URI]");
		header("Content-Type: text/plain");
		header("Expires: 0");
		echo $output;
		

?>