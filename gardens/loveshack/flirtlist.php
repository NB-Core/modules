<?php
function loveshack_flist($items) {
	global $session;
	$list = unserialize(get_module_pref('flirtsreceived'));
	if ($list=="") $list=array();
	if (sizeof($list)>0) {
		output("`@The following people have contacted you... go ahead!");
		rawoutput("<table><tr class='trhead'><td>");
		output("Data");
		rawoutput("</td></tr>");
		$n=0;
		$stage=((get_module_setting('cost')>0&&get_module_pref('buyring')==1) || get_module_setting('cost')==0);
		//debug("Stage:".$stage);
		reset($list);
		while (list($name,$points)=each ($list)) {
			$sql = "SELECT name,acctid FROM ".db_prefix("accounts")." WHERE acctid='".substr($name,1)."' AND locked=0";
			$res = db_query($sql);
			if (db_num_rows($res)!=0) {
				$row = db_fetch_assoc($res);
				rawoutput("<tr ".($n%2?"trlight":"trdark")."><td>");
				output_notl("`@[`^".$row['name']."`@]");
				$links = translate_inline("Links: ");
				$links .= " [".loveshack_flink($row['acctid'],"Buy a Drink","drink")."]";
				$links .= " - [".loveshack_flink($row['acctid'],"Buy some Roses","roses")."]";
				$links .= " - [".loveshack_flink($row['acctid'],"Kiss","kiss")."]";
				foreach ($items['shortcut'] as $key=>$val){
					list($itemname,$navname)= each($val);
					//debug($val);
					//debug("Name:".$itemname." nav:".$navname);
					$links .= " - [".loveshack_flink($row['acctid'],$navname,$itemname)."]";
				}
				$links .= " - [".loveshack_flink($row['acctid'],"Slap","slap")."]";
//				$links .= " - [".loveshack_flink($row['acctid'],"Ignore","ignore")."]";
				rawoutput(loveshack_hidedata($links));
				rawoutput("</td></tr>");
			}
		}
		rawoutput("</table><br>");
	} else {
		rawoutput("<table><tr class='trhilight'><td>");
		output_notl("`^");
		output("Aww! No-one has interest in you.");
		rawoutput("</td></tr></table><br>");
	}


}
?>
