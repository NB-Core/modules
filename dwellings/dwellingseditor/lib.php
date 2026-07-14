<?php
/**
 * Render the dwelling editor form.
 *
 * When a positive dwid is present in the request, the form is populated from
 * that existing dwelling record. Otherwise, it uses a complete default
 * new-dwelling payload so every rendered field has an initialized value before
 * submission to the save handler.
 */
function dwellingform()
{
	$dwid = httpget("dwid");
	$dwell = array();

	if ($dwid > 0) {
		$sql = "SELECT * FROM " . db_prefix("dwellings") . " WHERE dwid=$dwid";
		$res = db_query($sql);
		$dwell = db_fetch_assoc($res);
	} else {
		$dwell = array(
			"ownerid" => 0,
			"name" => "Bonkhouse",
			"type" => "",
			"location" => getsetting('villagename', LOCATION_FIELDS),
			"gold" => 0,
			"gems" => 0,
			"goldvalue" => 0,
			"gemvalue" => 0,
			"status" => 1,
			"description" => "",
			"windowpeer" => "",
		);
	}
	page_header("Create a dwelling");
	require_once("lib/showform.php");        
	rawoutput("<form action='runmodule.php?module=dwellingseditor&op=dwsave&dwid=$dwid' method='POST'>");
	addnav("","runmodule.php?module=dwellingseditor&op=dwsave&dwid=$dwid");
	rawoutput("<table align=left>");
	rawoutput("<tr><td nowrap align=left>");
	output("ID:");
	rawoutput("</td><td align=left>");
	output("`^$dwid`0");
	rawoutput("</td></tr>");
	rawoutput("<tr><td nowrap align=left>");
	output("Owner: ");
	rawoutput("<a href='runmodule.php?module=dwellingseditor&op=lookup' target='_blank' onClick=\"".popup("runmodule.php?module=dwellingseditor&op=lookup").";return false;\">");
	output("(Look up User ID)");
	rawoutput("</a>");        
	addnav("","runmodule.php?module=dwellingseditor&op=lookup");
	rawoutput("</td><td align=left><input name='dwell[ownerid]' value=\"".htmlentities($dwell['ownerid'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\"></td></tr>");
	rawoutput("<tr><td nowrap align=left>");
	output("Name:");
	rawoutput("</td><td align=left><input name='dwell[name]' value=\"".htmlentities($dwell['name'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\"></td></tr>");
	rawoutput("<tr><td nowrap align=left>");
	output("Type:");
	rawoutput("</td><td nowra align=left>");
	$types = array();
	$sql3 = "SELECT module FROM ".db_prefix("dwellingtypes")."";
	$res3 = db_query($sql3);
	while($row3 = db_fetch_assoc($res3)){
		$vname = $row3['module'];
		$types[$vname] = sprintf_translate("%s", $vname);
	}
	ksort($types);
	reset($types);
	rawoutput("<select name='dwell[type]'>");
	foreach($types as $typ=>$name) {
		$name = translate_inline(sanitize(get_module_setting("dwname",$name)));
		rawoutput("<option value='$typ'".($dwell['type']==$typ?" selected":"").">$name</option>");
	}
	rawoutput("</td></tr>");
	rawoutput("<tr><td nowrap align=left>");
	output("Location:");
	rawoutput("</td><td nowrap align=left>");
	$vname = getsetting('villagename', LOCATION_FIELDS);
	$locs = array($vname => sprintf_translate("%s", $vname));
	$sql2 = "SELECT cityname FROM ".db_prefix("cityprefs")."";
	$res2 = db_query($sql2);
	while($row2 = db_fetch_assoc($res2)){
		$vname = $row2['cityname'];
		$locs[$vname] = sprintf_translate("%s", $vname);
	}
	ksort($locs);
	reset($locs);
	rawoutput("<select name='dwell[location]'>");
	foreach($locs as $loc=>$name) {
		rawoutput("<option value='$loc'".($dwell['location']==$loc?" selected":"").">$name</option>");
	}
	rawoutput("</td></tr>");
	require_once("lib/nltoappon.php");
	rawoutput("<tr><td nowrap align=left>");
	output("Gold in coffers:");
	rawoutput("</td><td align=left><input name='dwell[gold]' value=\"".htmlentities($dwell['gold'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\"></td></tr>");
	rawoutput("<tr><td nowrap align=left>");
	output("Gems in coffers:");
	rawoutput("</td><td align=left><input name='dwell[gems]' value=\"".htmlentities($dwell['gems'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\"></td></tr>");
	rawoutput("<tr><td nowrap align=left>");
	output("Gold value:");
	rawoutput("</td><td align=left><input name='dwell[goldvalue]' value=\"".htmlentities($dwell['goldvalue'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\"></td></tr>");
	rawoutput("<tr><td nowrap align=left>");
	output("Gems value:");
	rawoutput("</td><td align=left><input name='dwell[gemvalue]' value=\"".htmlentities($dwell['gemvalue'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\"></td></tr>");
	rawoutput("<tr><td nowrap align=left>");
	output("Status:");
	rawoutput("</td><td nowrap align=left>");
	$stats = array();
	$stats[1] = translate_inline("`#Occupied");
	$stats[2] = translate_inline("`@Financing");
	$stats[3] = translate_inline("`QIn Construction");
	$stats[4] = translate_inline("`!Abandoned");
	$stats[5] = translate_inline("`%For Sale");
	$stats = modulehook("dwellings-status", $stats);
	ksort($stats);
	reset($stats);
	rawoutput("<select name='dwell[status]'>");
	foreach($stats as $stat=>$name) {
		rawoutput("<option value='$stat'".($dwell['status']==$stat?" selected":"").">$name</option>");
	}
	rawoutput("</td></tr><tr><td>");
	//'storedinfo
	output("Internal Description:");
	rawoutput("</td><td><textarea name='dwell[description]' rows='10' cols='60' class='input'>".stripslashes(htmlentities($dwell['description'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."</textarea></td></tr>");
	rawoutput("<tr><td nowrap align=left>");
	output("Public Description:");
	rawoutput("</td><td><textarea name='dwell[windowpeer]' rows='10' cols='60' class='input'>".stripslashes(htmlentities($dwell['windowpeer'], ENT_COMPAT, getsetting("charset", "ISO-8859-1")))."</textarea></td></tr>");
	$button = translate_inline("Save");
	if($dwid == "")$button = translate_inline("Create");
	rawoutput("<tr><td><input type='submit' class='button' value='$button'></td></tr></form>");
	rawoutput("</table>");
}
?>
