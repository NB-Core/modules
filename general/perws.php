<?php

function perws_getmoduleinfo(){
	$info = array(
		"name"=>"Bio: Personal Website",
		"author"=>"Chris Vorndran",
		"category"=>"General",
		"version"=>"1.01",
		"download"=>"http://dragonprime.net/users/Sichae/perws.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"This module will allow users to display a Personal Website in their bio. These URLs can be moderated and banned via the Grotto. Make sure to set the pref of the user that wishes to Moderate the URLs.",
		"settings"=>array(
			"limit"=>"Maximum chars on this,int|50",
			),
		"prefs"=>array(
			"Personal Website,title",
			"user_name"=>"What is the name of your website?,text|",
			"user_link"=>"What is the URL of your address?,text|",
			"user_note"=>"Make sure to append the `^http://`0 to the front. Thank you.,note",
			"ban"=>"Has user's URL been banned?,bool|0",
			"access"=>"Does this user have access to the Banning URL page?,bool|0",
		),
	);
	return $info;
}
function perws_install(){
	module_addhook("superuser");
	module_addhook("biostat");
	module_addhook("charrestore_nosavemodules");
	return true;
}
function perws_uninstall(){
	return true;
}
function perws_dohook($hookname,$args){
	global $session,$target;
	$length=get_module_setting('limit');
	switch ($hookname){
		case "charrestore_nosavemodules":
			$args['perws']=true; //don't let charrestore save this, it's personal info
			break;
		case "superuser":
			if (get_module_pref("access")){
				addnav("Editors");
				addnav("Moderate URLs","runmodule.php?module=perws&op=list");
			}
			break;
		case "biostat":
			if (get_module_pref("user_link","perws",$target['acctid']) <> "" && !get_module_pref("ban","perws",$target['acctid'])){
				$set=addslashes(get_module_pref("user_link","perws",$target['acctid']));
				if (strpos($set,"script")!==false) break;
				$name=stripslashes(get_module_pref("user_name","perws",$target['acctid']));
				$name=substr($name,0,$length);
				if (strpos($name,"script")!==false) break;
				if (filter_var($set, FILTER_VALIDATE_URL)===false) {
					// url invalid
					$set="#";
					$name="(invalid!)";
				}
				output("`^Personal Website: `@<a href='%s' target='_blank'>",$set,true);
				$name=str_replace("`c","",$name);
				$name=str_replace("`b","",$name);
				$name=str_replace("`i","",$name);
				$name=str_replace("`n","",$name);
				output_notl("%s",$name);
				output_notl("</a>`0`n",$name,true);
			}
			break;
		}
	return $args;
}
function perws_run(){
	global $sesion;
	$op = httpget('op');
	$id = httpget('id');
	$ban = httpget('ban');
	$sub = translate_inline("Your URL has been banned.");
	$body = translate_inline("We are sorry, but due to certain reasons, your personal URL has been banned. Please take this up with your local admin. There, you may discuss why your URL was banned, and see for a means of fixing this all up. Thank you.");
	page_header("Moderate URLs");
	switch ($op){
		case "list":
			if ($id <> ""){
				set_module_pref("ban",$ban,"perws",$id);
				output("`cUser's URL has been `^%s`0.`c",translate_inline($ban?"banned":"unbanned"));
				require_once("lib/systemmail.php");
				if ($ban) systemmail($id,$sub,$body);
			}
			$sql = "SELECT name, a.value AS link, b.value AS sitename, c.value AS ban, acctid FROM ".db_prefix("accounts")." INNER JOIN ".db_prefix("module_userprefs")." AS a, ".db_prefix("module_userprefs")." AS b, ".db_prefix("module_userprefs")." AS c ON acctid=a.userid ANd acctid=b.userid AND acctid=c.userid WHERE a.modulename='perws' AND b.modulename='perws' AND c.modulename='perws' AND a.setting='user_link' AND b.setting='user_name' AND c.setting='ban' AND a.value <> '' AND b.value <>''";
			$res = db_query($sql);
			$ops = translate_inline("Ops");
			$name = translate_inline("Name");
			$url = translate_inline("URL");
			$bsh = translate_inline("Banned");
			rawoutput("<br><table border='0' cellpadding='2' cellspacing='1' align='center' bgcolor='#999999'>");
			rawoutput("<tr class='trhead'><td>$ops</td><td>$name</td><td>$bsh</td><td>$url</td></tr>");
			for ($i = 0; $i < db_num_rows($res); $i++){
				$row = db_fetch_assoc($res);
				rawoutput("<tr class='".($i%2?"trdark":"trlight")."'><td>");
				if ($row['ban'] == 0){
					rawoutput("<a href='runmodule.php?module=perws&op=list&id=".rawurlencode($row['acctid'])."&ban=1'>");
					output("Ban");
					rawoutput("</a>");
					addnav("","runmodule.php?module=perws&op=list&id=".$row['acctid']."&ban=1");
				}else{
					rawoutput("<a href='runmodule.php?module=perws&op=list&id=".rawurlencode($row['acctid'])."&ban=0'>");
					output("Un-Ban");
					rawoutput("</a>");
					addnav("","runmodule.php?module=perws&op=list&id=".$row['acctid']."&ban=0");
				}					
				rawoutput("</td><td>");
				output_notl("`@%s",$row['name']);
				rawoutput("</td><td>");
				output("`c%s`c`0",translate_inline($row['ban']==1?"`@Yes":"`#No"));
				rawoutput("</td><td>");
				rawoutput("<a href='".$row['link']."' target='_blank'>".stripslashes($row['sitename'])."</a>");
				rawoutput("</td></tr>");
				}
			rawoutput("</table");
			addnav("Refresh List","runmodule.php?module=perws&op=list");
			addnav("Return to the Grotto","superuser.php");
			break;
		}
	page_footer();
}
?>	
