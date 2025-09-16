<?php
	$last = date("Y-m-d H:i:s", strtotime("-".getsetting("LOGINTIMEOUT", 900)." sec"));
	if ((is_module_active("pvpimmunity") && get_module_pref("check_willing","pvpimmunity"))|| !is_module_active("pvpimmunity")){
		$typeid = get_module_setting("typeid",$args['type']);
		if ($session['user']['location'] == $args['location'] && get_module_objpref("dwellingtypes", $typeid, "pvp", "dwellings_pvp")
			&& !get_module_objpref("dwellings", $args['dwid'], "bought", "dwellings_pvp") && $session['user']['playerfights'] > 0 && $args['status'] == 1){
				$top = $session['user']['level']+get_module_objpref("dwellingtypes",$typeid,"top-band","dwellings_pvp");
				$bottom = $session['user']['level']-get_module_objpref("dwellingtypes",$typeid,"bottom-band","dwellings_pvp");
				$sql = "SELECT count(acctid) AS count FROM ".db_prefix("accounts")." INNER JOIN ".db_prefix("module_userprefs")." ON acctid=userid WHERE (level>=$bottom && level<=$top)
						AND (laston < '$last' OR loggedin=0) AND modulename='dwellings'	AND setting='dwelling_saver' AND value='{$args['dwid']}'";
						$res = db_query_cached($sql,"pvp-".$args['dwid'],60);
						$row = db_fetch_assoc($res);
						$dwid = $args['dwid'];
						if ($row['count'] > 0){
							$temp = sprintf_translate("Slay (%s)",$row['count']);
							rawoutput("<a href='runmodule.php?module=dwellings_pvp&op=attack_list&dwid=$dwid&typeid=$typeid&returnpage=".httpget('page')."'>$temp</a><br>");  				
							addnav("","runmodule.php?module=dwellings_pvp&op=attack_list&dwid=$dwid&typeid=$typeid&returnpage=".httpget('page'));
						}
				}
	}
?>
