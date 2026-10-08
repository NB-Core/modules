<?php
function hofcrimsonleaf_getmoduleinfo(){
	$info = array(
		"name"=>"Show the Crimson Leaf Clover in HOF",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Gypsy",
		"download"=>"",
		"requires"=> array(
			"crimsonleaf"=>"1.0|Crimson Leaf Clover by `2Oliver Brendel",
			),

	);
	return $info;
}

function hofcrimsonleaf_install(){
	module_addhook_priority("footer-hof", 100);
	return true;
}

function hofcrimsonleaf_uninstall(){
	return true;
}

function hofcrimsonleaf_dohook($hookname,$args){
	global $session;
	$name="`qCrimson `2Leaf `gClover";
	switch ($hookname)
	{
		case "footer-hof":
			addnav("Warrior Rankings");
			addnav(array("Show %s Winners",$name),"runmodule.php?module=hofcrimsonleaf");
		return $args;
	}
}

function hofcrimsonleaf_run(){
	global $session;
	$op = httpget("op");
	$name="`qCrimson `2Leaf `gClover";
	page_header("%s Winners",sanitize($name));
	output("`b`i`c`tWinners`c`i`b`n`n`5",$name);
	$winners=unserialize(stripslashes(get_module_setting("winners","crimsonleaf")));
	rawoutput("<center><table border='0' cellpadding='2' cellspacing='0'>");
	rawoutput("<tr class='trhead'><td>". translate_inline("Month") ."</td><td>". translate_inline("Winner") ."</td></tr>");
	$i=0;
	if (!$winners) $winners=array();
	foreach($winners as $key=>$val) {
		$i++;
		rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
		output_notl($key);
		rawoutput("</td><td>");
		$sql="SELECT name FROM ".db_prefix('accounts')." WHERE acctid=".$val;
		$row=db_fetch_assoc(db_query($sql));
		if (!$row['name']) $row['name']=translate_inline("Gone missing");
		output_notl($row['name']);
		rawoutput("</td></tr>");
	}
	rawoutput("</table></center>");
	addnav("Back to the HOF","hof.php");
	page_footer();
}
?>
