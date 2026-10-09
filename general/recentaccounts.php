<?php

function recentaccounts_getmoduleinfo(){
	$info = array(
		"name"=>"Most Recent Accounts",
		"author"=>"Chris Vorndran",
		"version"=>"0.1",
		"category"=>"General",
		"allowanonymous"=>"1",
		"download"=>"http://dragonprime.net/users/Sichae/recentaccounts.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"Generates a list of the most recent accounts made on a server.",
		"settings"=>array(
			"Most Recent Accounts Settings,title",
			"agetop"=>"What is the maximum age for people before they are off the list,|5",
			"howmany"=>"Show how many in the recent accounts listing,int|25",
		),
	);
	return $info;
}
function recentaccounts_install(){
	module_addhook("footer-list");
	return true;
}
function recentaccounts_uninstall(){
	return true;
}
function recentaccounts_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "footer-list":
			addnav("");
			addnav("Most Recent Accounts","runmodule.php?module=recentaccounts&op=start");
			break;
		}
	return $args;
}
function recentaccounts_run(){
	global $session;
	$op = httpget('op');
	$howmany = get_module_setting("howmany");
	$agetop = get_module_setting("agetop");
	switch ($op){
		case "start":
			$sql = "SELECT acctid,name,age,login,race,sex,level,dragonkills FROM " . db_prefix("accounts") . " WHERE age <= '$agetop' AND dragonkills = '0' ORDER BY acctid DESC LIMIT 0,$howmany";
		    $result = db_query($sql);
			$max = db_num_rows($result);

	        page_header("Most Recent Accounts");
			output("`c`bMost Recent Accounts `b`n`n");
		    rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999'>");

		$num = translate_inline("Num");
        $name = translate_inline("Name");
        $level = translate_inline("Level");
        $race = translate_inline("Race");
        $sex = translate_inline("Sex");

		rawoutput("<tr class='trhead'><td>$num</td><td>$name</td><td>$level</td><td>$race</td><td>$sex</td></tr>");
        for($i=0;$i<$max;$i++){
        $row = db_fetch_assoc($result);
            rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td>");

                output("%s",$i+1);
                rawoutput("</td><td>");

                if ($session['user']['loggedin']) {
                    $writemail = translate_inline("Write Mail");
                    rawoutput("<a href=\"mail.php?op=write&to=".rawurlencode($row['login'])."\" target=\"_blank\" onClick=\"".popup("mail.php?op=write&to=".rawurlencode($row['login'])."").";return false;\"><img src='images/newscroll.GIF' width='16' height='16' alt='$writemail' border='0'></a>");
					rawoutput("<a href='bio.php?char=".rawurlencode($row['login'])."'>");
		            addnav("","bio.php?char=".rawurlencode($row['login'])."");
                }

				output("`&{$row['name']}`0");
		        if ($session['user']['loggedin']) rawoutput("</a>");
			    rawoutput("</td><td>");
                output_notl("`^{$row['level']}`0");
		        rawoutput("</td><td>");
			if ($row['race']) $race=$row['race'];
					else
					$race=RACE_UNKNOWN;
			output_notl(translate_inline($race,"race"));
		        rawoutput("</td><td>");
		        output($row['sex']?"`%F`0":"`!M`0");

        rawoutput("</td></tr>");
        }

    rawoutput("</table>");
    output("`c");
	}
	addnav("Return to List Warriors","list.php");
	if ($session['user']['loggedin']){
		if ($session['user']['alive']){
			villagenav();
		}else{
			addnav("Return to the Shades","shades.php");
		}
	}
	page_footer();
	}
?>
