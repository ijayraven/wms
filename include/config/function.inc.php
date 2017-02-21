<?php
	/**
	 * All Function here for upload_charge program
	 * Created by	:	Raymond A. Galaroza
	 * Created date	:	2013-09-07
	 */
	class __Global_functions
	{
		/**
		 * Select Value from database
		 *
		 * @param Connection	$conn
		 * @param Database 		$db
		 * @param Table			$tbl
		 * @param Fields		$fld
		 * @param Condition		$conditions
		 * @return String
		 */
		public function	Sel_val($conn,$db,$tbl,$fld,$conditions)
		{
			try {
				$sel	=	"SELECT $fld FROM $db.$tbl WHERE $conditions ";
				$rssel	=	$conn->Execute($sel);
				if ($rssel == false) 
				{
					throw new Exception($db->ErrorNo().'::'.$db->ErrorMsg());
				}
				$retval =	$rssel->fields[$fld];
				return $retval;
			}
			catch (Exception $e)
			{
				echo $e->__toString();
			}
		}
		
		
		/**
		 * NBS ACCOUNTING WAY OF ROUNDING NUMBERS 08/27/12
		 * last update 10/05/12
		 *
		 * @param  int $num
		 * @param  int $round
		 * @return int $newnum
		 */
		public function AccntRound($num,$round)
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
	
	    /**
	     * fetch all amounts 07/19/12
	     *
	     * @param  int   $nDiscount
	     * @param  int   $nUnitPrice
	     * @param  int   $nQty
	     * @return array $aData
	     */
	    public function FetchAmount($nDiscount, $nUnitPrice, $nQty)
	    {
	        $nDisc                = self::AccntRound($nDiscount / 100, 2);
	        $nDiscAmt             = self::AccntRound(($nUnitPrice * $nDisc), 2);
	        $aData['GrossAmount'] = self::AccntRound(($nQty * $nUnitPrice), 2);
	        $aData['DiscAmount']  = self::AccntRound(($nDiscAmt * $nQty), 2);
	        $aData['NetAmount']   = self::AccntRound(($aData['GrossAmount'] - $aData['DiscAmount']), 2);
	
	        return $aData;
	    }
	    
	    
	    public function Create_sofno($conn,$alpha)
	    {
	    	try {
	    		$conn->StartTrans();
				$insert 	=	"INSERT INTO FDC_WMS_LOOKUP.ALPHA_$alpha (`CREATEDDATE`)VALUES(sysdate())";
		    	$rsinsert	=	$conn->Execute($insert);
		    	if ($rsinsert == false) 
		    	{
		    		throw new Exception($conn->ErrorMsg());
		    	}
		    	$id				=	$conn->Insert_ID();
		    	$alpha_count	=	strlen($alpha);
		    	if ($alpha_count == 1) 
		    	{
		    		$max = 7;
		    	}
		    	else if ($alpha_count == 2) 
		    	{
		    		$max = 6;
		    	}
		    	$retval	=	"R$alpha".str_pad($id,$max,0,STR_PAD_LEFT);
		    	return $retval;
		    	$conn->CompleteTrans();
	    	}
	    	catch (Exception $e)
	    	{
	    		echo $e->__toString();
	    		$conn->CompleteTrans();
	    	}
	    }
	}
	
?>