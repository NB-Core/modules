<?php
	$sql = "SELECT dwid,name,ownerid,location,type,gold,gems FROM ".db_prefix("dwellings")." 
		INNER JOIN ".db_prefix("module_objprefs")." 
		ON dwid=objid 
		WHERE objtype='dwellings' 
		AND setting='bought' 
		AND value=1";
	$res = db_query($sql);
	while ($row = db_fetch_assoc($res)){
		$dwid = $row['dwid'];
		$gold_coffer = $row['gold'];
		$gems_coffer = $row['gems'];
		$typeid = get_module_setting("typeid",$row['type']);
		$gold_cost = get_module_objpref("dwellingtypes",$typeid,"cost-gold","dwellings_pvp");
		$gems_cost = get_module_objpref("dwellingtypes",$typeid,"cost-gems","dwellings_pvp");
		$daysleft = get_module_objpref("dwellings", $dwid, "run-out", "dwellings_pvp");
		$isauto = get_module_objpref("dwellings", $dwid, "isauto", "dwellings_pvp");
		set_module_objpref("dwellings",$dwid,"run-out", $daysleft-1, "dwellings_pvp");
		if ($daysleft <= 0){
			set_module_objpref("dwellings", $row['dwid'], "bought", 0, "dwellings_pvp");
			$subj = translate_inline("Concerning Dwellings Guard");
			if ($gold_coffer >= $gold_cost 
				&& $gems_coffer >= $gems_cost 
				&& (get_module_setting("whatif") && $isauto)){
				$body = sprintf("`@We are sorry to inform you, but your establishment, %s`@, in `^%s`@ has lost the usage of it's personal guard. This is due to the rental time running out. However, you had enough gold and gems inside of your coffers to purchase another Guard. So, we have gone ahead and hired your guard once again.`n`nDwellings Commission.",$row['name'],$row['location']);
				dwellings_modify_coffers($dwid,"gold","-".$gold_cost);
				dwellings_modify_coffers($dwid,"gems","-".$gems_cost);
				$days = get_module_objpref("dwellingtypes",$typeid,"guard-length","dwellings_pvp");
				set_module_objpref("dwellings",$dwid,"run-out",$days,"dwellings_pvp");
				set_module_objpref("dwellings",$dwid,"bought",1,"dwellings_pvp");
			}else{
				$body = sprintf("`@We are sorry to inform you, but your establishment, %s`@, in `^%s`@ has lost the usage of it's personal guard. This is due to the rental time running out. If you would like to purchase one again, contact our Public Relations Department.`n`nDwellings Commission.",$row['name'],$row['location']);
			}
			require_once("lib/systemmail.php");
			systemmail($row['ownerid'],$subj,$body);
		}
	}
?>
