<?php
function manualpayment_getmoduleinfo(){
	$info = array(
		"name"=>"Manual Payment Entry",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"http://lotgd-downloads.com",
	);
	return $info;
}

function manualpayment_install(){
	module_addhook("paylog");
	module_addhook("header-superuser");
	return true;
}

function manualpayment_uninstall(){
	return true;
}

function manualpayment_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
	case "paylog":
		if ((SU_EDIT_PAYLOG & $session['user']['superuser'])==SU_EDIT_PAYLOG) {
			addnav("Manual Entry");
			addnav("Enter Manual Payment","runmodule.php?module=manualpayment");
		}
		break;
	case "header-superuser":
		if (($session['user']['superuser'] & SU_EDIT_PAYLOG) != SU_EDIT_PAYLOG) break;
		$month = date("Y-m");
		$startdate = $month."-01 00:00:00";
		$enddate = date("Y-m-d H:i:s",strtotime("+1 month",strtotime($startdate)));
		$sql = "SELECT count(payid) as counter FROM ".db_prefix('paylog')." WHERE processdate>='$startdate' AND processdate < '$enddate' AND processed!=1";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		if ($row['counter']>0) output("`c`b`\$There are `^%s`\$ unprocessed payments!`b`c`n`n",$row['counter']);
		break;
	}
	return $args;
}

function manualpayment_run(){
	global $session;
	check_su_access(SU_EDIT_PAYLOG); //check again Superuser Access
	$op=httpget('op');
	page_header ('Manual Payment');
	addnav("Navigation");
	addnav("Return to the paylog","paylog.php");
	addnav("Actions");
	addnav("Refresh main form","runmodule.php?module=manualpayment");
	switch($op) {
		case "write":
			$amount=(double)httppost('amount');
			$fee=(double)httppost('fee');
			$acctid=(int)httppost('acctid');
			$name=httppost('name');
			$processed=(int)httppost('processed');
			if ($processed!=1) $processed=0;
			if ($processed==0) $acctid='0';
			$info=array(
				"txn_type"=>translate_inline("manual_entry"),
				"txn-id"=>translate_inline("Manual")." ".date("Y-m-d H:i:s"),
				"mc_gross"=>$amount,
				"mc_currency"=>httppost('currency'),
				"mc_fee"=>$fee,
				"memo"=>$name,
				"payment_date"=>date("Y-m-d H:i:s"),
				"item-number"=>translate_inline("Manual Payment"),
				"payment_status"=>"completed",
				);				
			$sendinfo=addslashes(serialize($info));	
			$sql="INSERT INTO ".db_prefix('paylog')." VALUES ";
			$sql.="(0,'$sendinfo','No response as this is a manual entry via module manualpayment.php','{$info['txn-id']}','$amount','$name','$acctid','$processed',0,'$fee','".date("Y-m-d H:i:s")."');";
			$result=db_query($sql);
			if ($result==1)
				output("`7The Entry has been generated.");
				else
				output("`\$There has been an error while processing the entry!");
				
			break;
		default:
			$paycurrency=getsetting('paypalcurrency','USD');
			output("`c`b`^Manual Payment`c`b");
			output_notl("`n`n");
			output("`7Enter here the payment you have manually received, make sure to calculate fees correctly!`n`n");
			rawoutput("<form action='runmodule.php?module=manualpayment&op=write' method='post'>");
			addnav("","runmodule.php?module=manualpayment&op=write");
			output("Player Acctid:`n");
			rawoutput("<input type='input' class='input' length=20 name='acctid'><br>");
			output("Player Name:`n");
			rawoutput("<input type='input' class='input' length=20 name='name'><br>");
			output("Currency:`n");
			rawoutput("<input type='input' class='input' length=20 name='currency' value='$paycurrency'><br>");
			output("Amount:`n");
			rawoutput("<input type='input' class='input' maxlength=12 length=20 name='amount'><br>");
			output("Fee/Tax:`n");
			rawoutput("<input type='input' class='input' length=20 name='fee'><br>");
			output("Processed (points credited):");
			rawoutput("<input type='checkbox' name='processed' value='1'><br><br>");
			$submit=translate_inline("Submit");
			rawoutput("<input type='submit' class='button' value='$submit'><br>");
			rawoutput("</form>");
			output("`n`iNotes:`n`\$Never leave the name empty!`i");
			output("`nIf you want to give a player points, leave out the acctid and use only the name. Else the paylog won't give out points, even if you set 'processed' to 'false'!`i");
			
			break;		
			
	}
	page_footer();
	
}


?>
