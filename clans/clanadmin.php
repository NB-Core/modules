<?php

function clanadmin_getmoduleinfo(){
	$info = array(
		"name"=>"Clan Admin Functions",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Clan",
		"download"=>"",
	);
	return $info;
}

function clanadmin_install(){
	module_addhook("header-clan");
	module_addhook("clanhall");	
	return true;
}

function clanadmin_uninstall(){
	return true;
}

function clanadmin_dohook($hookname,$args){
	global $session;
	$op=httpget('op');
	switch ($hookname) {
		case "header-clan":
			if ($op!='apply') break;
			$closed=(int)get_module_objpref('clanadmin',(int)httpget('to'),'clanclosed');
			if ($closed) {
				page_header("Clans");
				output("`\$Sorry, this clan does not accept applications.");
				addnav("Back to the clan pages","clan.php");
				page_footer();
			}
			break;		
		case "clanhall":
			if ($session['user']['clanrank']>=CLAN_LEADER && $session['user']['clanid']!=0) {
				addnav("Clan Administrative");
				if (get_module_objpref('clanadmin',$session['user']['clanid'],'clanclosed')==1)
					addnav("Open Clan for Applications","runmodule.php?module=clanadmin&op=openapps");
					else
					addnav("Close Clan for Applications","runmodule.php?module=clanadmin&op=closeapps");
			}
			break;
	}
	return $args;
}

function clanadmin_run(){
	global $session;
	// Same condition as the clan hall links.
	if ($session['user']['clanrank']<CLAN_LEADER || $session['user']['clanid']==0) {
		redirect("clan.php");
	}
	$op=httpget('op');
	page_header("Clan Administratives");
	addnav("Navigation");
	addnav("Back to the Clanhall","clan.php");
	switch ($op) {
		case "closeapps":
			output("`\$The Clan is now closed for applications. Applicants will not be allowed.");
			set_module_objpref('clanadmin',$session['user']['clanid'],'clanclosed',1);
			break;
			
		case "openapps":
			output("`\$The Clan is now open for applications.");
			set_module_objpref('clanadmin',$session['user']['clanid'],'clanclosed',0);
			break;
	
	}
	//set_module_objpref("clans",$session['user']['clanid'],"filename",$url);
	//not sure why dumbfuck me has this line above in the code for this - copy+paste error I guess
	page_footer();
}

?>
