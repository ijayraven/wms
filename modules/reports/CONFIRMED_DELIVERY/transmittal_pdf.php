<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'].'/wms/includes/warehouse.inc.php');
if (empty($_SESSION['username'])) 
{
	echo "<script>alert('You dont have a session!');</script>";
	echo "<script>parent.location='../../../index.php'</script>";
}


		$DFROM	=	$_GET['DFROM'];
		$DTO	=	$_GET['DTO'];
		
		$_SESSION['DFROM']	=	$_GET['DFROM'];
		$_SESSION['DTO']	=	$_GET['DTO'];
		
		$date_type	=	$_GET['SEL_DATA_TYPE'];
		$OPT_2		=	$_GET['OPT_2'];
		
		$opt	=	$_GET['OPT__'];
		$doc	=	$_GET['SEL_DOC'];
		
		if ($doc=='INVOICE') 
		{
			$_SESSION['DOC_TYPE']	=	"INV";
		}
		else 
		{
			$_SESSION['DOC_TYPE']	=	"STF";
		}
		
		
		$_SESSION['NAME']		=	$global_func->Select_val($Filstar_conn,"WMS_USER","USER","NAME","USERNAME = '{$_SESSION['username']}' ");
		
		$sof_list=	array();
		
		
		$sel_val_cust	 =	"SELECT * from WMS_NEW.CONFIRMDELIVERY_HDR WHERE TRANSMIT = 'N' ";
		if ($date_type == '1') 
		{
		 $sel_val_cust	 .=	" AND CONFIRMDELDATE between '{$DFROM}' AND '{$DTO}' ";
		}
		else 
		{
		$sel_val_cust	 .=	" AND ADDEDDATE between '{$DFROM}' AND '{$DTO}' ";	
		}
		if ($OPT_2=='N')
		{
		$sel_val_cust	 .=	" AND VARIANCE = 'N' ";
		}
		else 
		{
		$sel_val_cust	 .=	" AND VARIANCE = 'Y' ";	
		}
		if ($_SESSION['username'] != 'raymond') 
		{
		$sel_val_cust	.=	"AND DOCTYPE = '{$doc}' and ADDEDBY = '{$_SESSION['username']}' ";
		}
		$rssel_val_cust	 =	$Filstar_conn->Execute($sel_val_cust);
		if ($rssel_val_cust==false) 
		{
			echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
		}
		$cnt	=	$rssel_val_cust->RecordCount();
		if ($cnt > 0) 
		{
			while (!$rssel_val_cust->EOF) 
			{
				$CUSTNO			=	$rssel_val_cust->fields['CUSTNO'];
				$SOF			=	$rssel_val_cust->fields['SOF'];
				
				$branch_code	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustomerBranchCode","CustNo = '{$CUSTNO}' ");
				if ($opt=='NBS' && !empty($branch_code)) 
				{
					$sof_list[]	=	$SOF;
				}
				else if ($opt=='TRADE' && $branch_code == '') 
				{
					$sof_list[]	=	$SOF;
				}
				$rssel_val_cust->MoveNext();
			}
			$count_list	=	count($sof_list);
			if ($count_list > 0) 
			{
				$sof 		=	implode("','",$sof_list);

				$sel_sof	=	"SELECT * FROM WMS_NEW.CONFIRMDELIVERY_HDR WHERE SOF IN ('{$sof}') ";
				$rssel_sof	=	$Filstar_conn->Execute($sel_sof);
				if ($rssel_sof==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				
				/**
				 * insert into ordercycle
				 */
				
				$Filstar_conn->StartTrans();
				$sel_sof_2	=	"SELECT * FROM WMS_NEW.CONFIRMDELIVERY_HDR WHERE SOF IN ('{$sof}') ";
				$rssel_sof_2=	$Filstar_conn->Execute($sel_sof_2);
				if ($rssel_sof_2==false) 
				{
					echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
				}
				while (!$rssel_sof_2->EOF) 
				{
					$SOF		=	$rssel_sof_2->fields['SOF'];
					$CUSTNO		=	$rssel_sof_2->fields['CUSTNO'];
					$SRNO		=	$rssel_sof_2->fields['SRNO'];
					$ADDEDBY	=	$rssel_sof_2->fields['ADDEDBY'];
					$ADDEDDATE	=	$rssel_sof_2->fields['ADDEDDATE'];
					$ADDEDTIME	=	$rssel_sof_2->fields['ADDEDTIME'];
					$DOCNO		=	$rssel_sof_2->fields['DOCNO'];
					$NETAMOUNT	=	$rssel_sof_2->fields['NETAMOUNT'];
					$DEPTCODE	=	"MC";
					$DOCTYPE	=	$rssel_sof_2->fields['DOCTYPE'];
					
					$Season		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","Season","OrderNo = '{$SOF}' ");
				
					$insert_cycle	=	"INSERT INTO ordercycle
										(`SOFNumber`,`CustNumber`,`SRNumber`,`User_In`,`Date_C`,`Time_C`,`User_C`,`InvoiceNumber`,`InvAmount`,`DeptCode`,`DeliveryCode`,`TransType`,
										`DateEncoded`,`TimeEncoded`,`EncodedBy`,`DateChanged`,`TimeChanged`,`ChangedBy`,`Season`,`LIQUIDATED`)
										VALUES
										('{$SOF}','{$CUSTNO}','{$SRNO}','{$ADDEDBY}','{$ADDEDDATE}','{$ADDEDTIME}','{$ADDEDBY}','{$DOCNO}','{$NETAMOUNT}','MC','C','{$DOCTYPE}',
										'{$ADDEDDATE}','{$ADDEDTIME}','{$ADDEDBY}','{$ADDEDDATE}','{$ADDEDTIME}','{$ADDEDBY}','{$Season}','N') ";
					$rsinsert_cycle	=	$Filstar_conn->Execute($insert_cycle);
					if ($rsinsert_cycle==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					
					$update_transmit	=	"UPDATE WMS_NEW.CONFIRMDELIVERY_HDR SET TRANSMIT = 'Y', TRANSMITBY = '{$_SESSION['username']}', TRANSDATE = SYSDATE(), TRANSTIME = SYSDATE()
											WHERE SOF = '{$SOF}' ";
					$rsupdate_transmit	=	$Filstar_conn->Execute($update_transmit);
					if ($rsupdate_transmit==false) 
					{
						echo $Filstar_conn->ErrorMsg()."::".__LINE__;exit();
					}
					$rssel_sof_2->MoveNext();
				}
				$Filstar_conn->CompleteTrans();
			}
		}
	
		class PDF extends FPDF 
		{
			//function Header_ko($header_,$status)
			function Header($header_)
			{
				
				$this->Image("/var/www/html/wms/images/fdc101.jpg",10,5,38,18);
				
				$this->SetFont('Courier','B',15);
				$this->SetX(10);$this->Cell(0,5,'CONFIRMED DELIVERIES',0,1,'C');
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(0,5,$doc."TRANSMITTAL ".$opt,0,1,'C');
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(0,5,date("F d, Y",strtotime($_SESSION['DFROM']))." to ".date("F d, Y",strtotime($_SESSION['DTO'])),0,1,'C');
				
				$this->ln(5);
				$this->SetFont('Courier','B',12);
				$this->SetX(10);$this->Cell(15,5,'LINE#',0,0,'L');
				$this->SetX(25);$this->Cell(25,5,'SOF #',0,0,'C');
				$this->SetX(50);$this->Cell(100,5,'CUSTOMER',0,0,'C');
				$this->SetX(162);$this->Cell(30,5,$_SESSION['DOC_TYPE'].'#',0,0,'C');
				$this->SetX(188);$this->Cell(30,5,$_SESSION['DOC_TYPE'].' AMT',0,0,'C');
				$this->SetX(222);$this->Cell(25,5,$_SESSION['DOC_TYPE'].' DATE',0,0,'C');
				$this->SetX(247);$this->Cell(35,5,'GROSS AMT',0,0,'C');
				$this->SetX(275);$this->Cell(45,5,'CONF DEL DATE',0,0,'C');
				$this->SetX(318);$this->Cell(30,5,'ADDED DATE',0,1,'C');
			}
			
			function HEADER_TRXNO($TRXNO)
			{
				$this->SetFont('Courier','B',15);
				$this->SetXY(10,35);$this->Cell(10,5,$TRXNO,0,1,'L');
				$this->ln(5);
			}
			
			function Footer()
			{
				$this->SetFont('Courier','',9);
				$this->SetXY(15,185);$this->Cell(0,3,"TRANSMITTED BY : ",0,0,'L');
				$this->SetXY(45,185);$this->Cell(47,3,$_SESSION['NAME'],"B",0,'L');
				$this->SetXY(15,190);$this->Cell(0,3,"TRANSMITTED DATE/TIME	: ",0,0,'L');
				$this->SetXY(56,190);$this->Cell(37,3,date('Y-m-d H:i:s'),"B",0,'L');
				
				$this->SetXY(15,195);$this->Cell(0,3,"RECEIVED    BY : ",0,0,'L');
				$this->SetXY(45,195);$this->Cell(47,3,"","B",0,'L');
				$this->SetXY(15,200);$this->Cell(0,3,"RECEIVED    DATE/TIME:",0,0,'L');
				$this->SetXY(56,200);$this->Cell(37,3,"","B",0,'L');
				
				$this->SetX(10);$this->Cell(0,5,'Printed Date  : '.date('Y-m-d'),0,1,'C');
				$this->SetX(10);$this->Cell(0,5,'Printed Time : '.date('H:i A'),0,1,'C');
				$this->SetX(10);$this->Cell(0,5	,'Page '.$this->PageNo().'/{nb}',0,0,'C');
			}
			
			function Sel_val($conn,$database,$tbl,$fld,$condition)
			{
				$sel	=	"SELECT $fld FROM ".$database.".$tbl WHERE $condition";
				$rssel	=	$conn->Execute($sel);
				if ($rssel == false) 
				{
					die($conn->ErrorMsg());
				}
				$retval	=	$rssel->fields[$fld];
				return $retval;
			}
		}
		
		$pdf= new PDF('L','mm','legal');
		$pdf->Open();
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak('auto',30);
		$pdf->AddPage();
		
		if ($rssel_sof->RecordCount() > 0) 
		{
			$counter=1;
			$pdf->SetFont('Courier','',12);
			while (!$rssel_sof->EOF) 
			{
				
				$SOF			=	$rssel_sof->fields['SOF'];
				$CUSTNO			=	$rssel_sof->fields['CUSTNO'];
				$REFN0			=	$rssel_sof->fields['DOCNO'];
				$RCVDNETAMOUNT	=	$rssel_sof->fields['RCVDNETAMOUNT'];
				$RCVDGROSSAMOUNT=	$rssel_sof->fields['RCVDGROSSAMOUNT'];
				$CONFIRMDELDATE	=	$rssel_sof->fields['CONFIRMDELDATE'];
				$ADDEDDATE		=	$rssel_sof->fields['ADDEDDATE'];
				
				$custname		=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","custmast","CustName","CustNo = '{$CUSTNO}' ");
				$InvoiceDate	=	$global_func->Select_val($Filstar_conn,"FDCRMSlive","orderheader","InvoiceDate","OrderNo = '{$SOF}' ");
					
				
				$pdf->SetX(10);$pdf->Cell(15,5,$counter,0,0,'L');
				$pdf->SetX(25);$pdf->Cell(25,5,$SOF,0,0,'C');
				$pdf->SetX(53);$pdf->Cell(100,5,$CUSTNO.'-'.substr($custname,0,35),0,0,'L');
				$pdf->SetX(162);$pdf->Cell(30,5,$REFN0,0,0,'C');
				$pdf->SetX(185);$pdf->Cell(30,5,number_format($RCVDNETAMOUNT,2),0,0,'R');
				$pdf->SetX(222);$pdf->Cell(25,5,$InvoiceDate,0,0,'R');
				$pdf->SetX(243);$pdf->Cell(35,5,number_format($RCVDGROSSAMOUNT,2),0,0,'R');
				$pdf->SetX(270);$pdf->Cell(45,5,$CONFIRMDELDATE,0,0,'R');
				$pdf->SetX(318);$pdf->Cell(30,5,$ADDEDDATE,0,1,'R');
				
				
				$total_net	+=	$RCVDNETAMOUNT;
				$total_gross+=	$RCVDGROSSAMOUNT;
				
				$counter++;
				
				$rssel_sof->MoveNext();
			}
			$pdf->Ln(1);
			$pdf->SetFont('Courier','B',12);
			$pdf->SetX(185);$pdf->Cell(30,2,"","B",0,'R');
			$pdf->SetX(243);$pdf->Cell(35,2,"","B",1,'R');
			$pdf->SetX(53);$pdf->Cell(100,5,"TOTAL",0,0,'C');
			$pdf->SetX(185);$pdf->Cell(30,5,number_format($total_net,2),0,0,'R');
			$pdf->SetX(243);$pdf->Cell(35,5,number_format($total_gross,2),0,1,'R');
			$pdf->SetFont('Courier','IB',11);
			$pdf->SetX(10);$pdf->Cell(0,5,"* * * * * * * * * * * END OF RECORD * * * * * * * * * * *",0,0,'C');
		}
		else 
		{
			$pdf->SetFont('Courier','B',13);
			$pdf->SetX(10);$pdf->Cell(0,5," * * * NO RECORD FOUND * * * ",0,0,'C');
		}
		echo $pdf->Output();
?>