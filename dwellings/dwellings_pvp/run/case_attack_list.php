<?php
	page_header("PvP Roster");
	output("`3Before you stands a large roster of who is sleeping in the current house you are looking at.");
	output("Pick out your target and hunt them down!`n`n");
	$days = getsetting("pvpimmunity",5);
	$exp = getsetting("pvpminexp",1000);
	$id = $session['user']['acctid'];
	$loc = $session['user']['location'];
	$typeid = httpget('typeid');
	$top = $session['user']['level']+get_module_objpref("dwellingtypes",$typeid,"top-band","dwellings_pvp");
	$bottom = $session['user']['level']-get_module_objpref("dwellingtypes",$typeid,"bottom-band","dwellings_pvp");

	// build results via DBAL with bound parameters for both list modes
	$connection = \Lotgd\MySQL\Database::getDoctrineConnection();
	$params = [
		'dwid' => (string) $dwid,
		'days' => (int) $days,
		'exp' => (int) $exp,
		'bottom' => (int) $bottom,
		'top' => (int) $top,
		'last' => $last,
		'id' => (int) $id,
		'loc' => (string) $loc,
	];
	$types = [
		'dwid' => \Doctrine\DBAL\ParameterType::STRING,
		'days' => \Doctrine\DBAL\ParameterType::INTEGER,
		'exp' => \Doctrine\DBAL\ParameterType::INTEGER,
		'bottom' => \Doctrine\DBAL\ParameterType::INTEGER,
		'top' => \Doctrine\DBAL\ParameterType::INTEGER,
		'last' => \Doctrine\DBAL\ParameterType::STRING,
		'id' => \Doctrine\DBAL\ParameterType::INTEGER,
		'loc' => \Doctrine\DBAL\ParameterType::STRING,
	];

	if (!get_module_setting("altlist")) {
		$sql = "SELECT acctid, race, dragonkills, name, alive, a.value AS location, sex, level, laston, loggedin, login, pvpflag, clanshort, clanrank\n"
			. "FROM $ac\n"
			. "LEFT JOIN $cl ON $cl.clanid=$ac.clanid\n"
			. "INNER JOIN $mu AS a ON $ac.acctid=a.userid\n"
			. "INNER JOIN $mu AS b ON $ac.acctid=b.userid\n"
			. "WHERE (locked=0)\n"
			. "AND (a.setting = 'location_saver' AND a.modulename = 'dwellings')\n"
			. "AND (b.setting = 'dwelling_saver' AND b.modulename='dwellings' AND b.value = :dwid)\n"
			. "AND (slaydragon=0) AND\n"
			. "(age>:days OR dragonkills>0 OR pk>0 OR experience>:exp)\n"
			. "AND (level>=:bottom AND level<=:top) AND (alive=1)\n"
			. "AND (laston<:last OR loggedin=0) AND (acctid<>:id)\n"
			. "ORDER BY location=:loc DESC, location, level DESC, experience DESC, dragonkills DESC";

		$result = $connection->executeQuery($sql, $params, $types);
		$pvp = [];
		while ($row = \Lotgd\MySQL\Database::fetchAssoc($result)) {
			$pvp[] = $row;
		}
	} else {
		$sql = "SELECT acctid, race, dragonkills, name, title, alive, a.value AS location, sex, level, laston, loggedin, pvpflag\n"
			. "FROM $ac\n"
			. "INNER JOIN $mu AS a ON $ac.acctid=a.userid\n"
			. "INNER JOIN $mu AS b ON $ac.acctid=b.userid\n"
			. "WHERE (locked=0)\n"
			. "AND (a.setting = 'location_saver' AND a.modulename = 'dwellings')\n"
			. "AND (b.setting = 'dwelling_saver' AND b.modulename='dwellings' AND b.value = :dwid)\n"
			. "AND (slaydragon=0) AND\n"
			. "(age>:days OR dragonkills>0 OR pk>0 OR experience>:exp)\n"
			. "AND (level>=:bottom AND level<=:top) AND (alive=1)\n"
			. "AND (laston<:last OR loggedin=0) AND (acctid<>:id)\n"
			. "ORDER BY location=:loc DESC, location, level DESC, experience DESC, dragonkills DESC";

		$result = $connection->executeQuery($sql, $params, $types);
		$pvp = [];
		while ($row = \Lotgd\MySQL\Database::fetchAssoc($result)) {
			$pvp[] = $row;
		}
	}

	// Shared rendering for both branches
	$pvp = modulehook("pvpmodifytargets", $pvp);
	tlschema("pvp");
	$n = translate_inline("Title");
	$l = translate_inline("Level");
	$loca = translate_inline("Location");
	$ops = translate_inline("Ops");
	$att = translate_inline("Attack");
	$link = "runmodule.php?module=dwellings_pvp";
	$extra = "&op=fight1";
	rawoutput("<table align='center' border='0' cellpadding='3' cellspacing='0'>");
	rawoutput("<tr class='trhead'><td>$n</td><td>$l</td><td>$loca</td><td>$ops</td></tr>");
	$loc_counts = array(); $num = count($pvp); $j = 0;
	for ($i = 0; $i < $num; $i++){
		$row = $pvp[$i];
		if (isset($row['silentinvalid']) && $row['silentinvalid']) continue;
		if (!isset($loc_counts[$row['location']])) $loc_counts[$row['location']] = 0;
		$loc_counts[$row['location']]++;
		if (isset($row['invalid']) && $row['invalid']!="") {
			if ($row['invalid']==1) $row['invalid']="Unable to attack";
			output("`i`4(%s`4)`i",$row['invalid']);
		} elseif ($row['location'] != $loc) continue;
		$j++;
		rawoutput("<tr class='".($j%2?"trlight":"trdark")."'><td>");
		if (isset($row['title']) && $row['title']!='') output_notl("`@%s`0", $row['title']); else output_notl("`@%s`0", $row['name']);
		rawoutput("</td>");
		rawoutput("<td>");
		output_notl("%s", $row['level']);
		rawoutput("</td>");
		rawoutput("<td>");
		output_notl("%s", $row['location']);
		rawoutput("</td>");
		rawoutput("<td>[ ");
		if($row['pvpflag']>$pvptimeout){
			output("`i(Attacked too recently)`i");
		}elseif ($loc!=$row['location']){
			output("`i(Can't reach them from here)`i");
		}else{
			rawoutput("<a href='$link$extra&name=".rawurlencode($row['acctid'])."'>$att</a>");
			addnav("","$link$extra&name=".rawurlencode($row['acctid']));
		}
		rawoutput(" ]</td></tr>");
	}
	if (!isset($loc_counts[$loc]) || $loc_counts[$loc]==0){
		$noone = translate_inline("`iThere are no available targets.`i");
		output_notl("<tr><td align='center' colspan='4'>$noone</td></tr>", true);
	}
	rawoutput("</table>");
	tlschema();

	addnav("Actions");
	addnav("Refresh List","runmodule.php?module=dwellings_pvp&op=attack_list&dwid=$dwid&page=".((int)httpget('returnpage')+(int)httpget('page')));
	addnav("Leave");
	addnav("Hamlet Registry","runmodule.php?module=dwellings&op=list&ref=hamlet&page=".((int)httpget('returnpage')+(int)httpget('page')));
?>
