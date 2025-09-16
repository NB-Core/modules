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
	if (!get_module_setting("altlist")){
		$sql = "SELECT acctid, race, dragonkills, name, alive, a.value AS location, sex, level, laston, loggedin, login, pvpflag, clanshort, clanrank		FROM $ac		LEFT JOIN $cl ON $cl.clanid=$ac.clanid		INNER JOIN $mu AS a ON $ac.acctid=a.userid		INNER JOIN $mu AS b ON $ac.acctid=b.userid		WHERE (locked=0)		AND (a.setting = 'location_saver' AND a.modulename = 'dwellings')		AND (b.setting = 'dwelling_saver' AND b.modulename='dwellings' AND b.value = '$dwid')		AND (slaydragon=0) AND		(age>$days OR dragonkills>0 OR pk>0 OR experience>$exp)		AND (level>=$bottom AND level<=$top) AND (alive=1)		AND (laston<'$last' OR loggedin=0) AND (acctid<>$id)		ORDER BY location='$loc' DESC, location, level DESC,		experience DESC, dragonkills DESC";		output_notl("`c");
		$link = "runmodule.php?module=dwellings_pvp";
		$extra = "&op=fight1";
		require_once("lib/pvplist.php");
		pvplist($loc, $link, $extra, $sql);
		output_notl("`c`0");
	}else{
		$sql = "SELECT acctid, race, dragonkills, name, title, alive, a.value AS location, sex, level, laston, loggedin, pvpflag		FROM $ac		INNER JOIN $mu AS a ON $ac.acctid=a.userid		INNER JOIN $mu AS b ON $ac.acctid=b.userid		WHERE (locked=0)		AND (a.setting = 'location_saver' AND a.modulename = 'dwellings')		AND (b.setting = 'dwelling_saver' AND b.modulename='dwellings' AND b.value = '$dwid')		AND (slaydragon=0) AND		(age>$days OR dragonkills>0 OR pk>0 OR experience>$exp)		AND (level>=$bottom AND level<=$top) AND (alive=1)		AND (laston<'$last' OR loggedin=0) AND (acctid<>$id)		ORDER BY location='$loc' DESC, location, level DESC,		experience DESC, dragonkills DESC";	
		// Following code is liberated from lib/pvplist.php
		$pvp = array();
		while ($row = db_fetch_assoc($res)) {
			$pvp[] = $row;
		}
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
		$loc_counts = array();		$num = count($pvp);		$j = 0;	
		for ($i = 0; $i < $num; $i++){			$row = $pvp[$i];
		if (isset($row['silentinvalid']) && $row['silentinvalid']) continue;
		if (!isset($loc_counts[$row['location']]))	$loc_counts[$row['location']] = 0;
		$loc_counts[$row['location']]++;
		if (isset($row['invalid']) && $row['invalid']!="") {
			if ($row['invalid']==1) $row['invalid']="Unable to attack";
			output("`i`4(%s`4)`i",$row['invalid']);
		} elseif ($row['location'] != $loc) continue;
		$j++;
		rawoutput("<tr class='".($j%2?"trlight":"trdark")."'><td>");
		output_notl("`@%s`0", $row['title']);
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
	}
		addnav("Actions");
		addnav("Refresh List","runmodule.php?module=dwellings_pvp&op=attack_list&dwid=$dwid&page=".((int)httpget('returnpage')+(int)httpget('page')));
		addnav("Leave");
		addnav("Hamlet Registry","runmodule.php?module=dwellings&op=list&ref=hamlet&page=".((int)httpget('returnpage')+(int)httpget('page')));
?>
