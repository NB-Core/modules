<?php
// addnews ready
// mail ready

function holiday_naruto_getmoduleinfo(){
	$info = array(
		"name"=>"Holiday - Talk like Naruto",
		"version"=>"1.0",
		"author"=>"Based on work by JT Traub, edited by `2Oliver Brendel",
		"category"=>"Holidays|Holiday Texts",
		"download"=>"core_module",
		"settings"=>array(
			"Talk Like A Naruto Day Settings,title",
			"start"=>"Activation date (mm-dd)|4-17",
			"end"=>"End date (mm-dd)|4-19",
		),
		"prefs"=>array(
			"Talk Like Naruto Day User Preferences,title",
			"user_ignore"=>"Ignore Talk Like Naruto text,bool|0",
		),
	);
	return $info;
}

function holiday_naruto_install(){
	module_addhook("holiday");
	return true;
}

function holiday_naruto_uninstall(){
	return true;
}

function holiday_naruto_munge($in) {
	$out = $in;
	$out = str_replace("Academy Student","Ultimate Warrior",$out);	
	$out = str_replace("Genin","Elite Warrior",$out);	
	$out = str_replace("Chuunin","Superior Warrior",$out);	
	$out = str_replace("Jounin","Uber Warrior",$out);	
	$out = str_replace("Shizune","Pig Lady",$out);	
	$out = str_replace("Baluski","Balu t' Bear",$out);	
	$out = str_replace("Tsunade","Tsunade Oobaa-san",$out);	
	$out = str_replace("Neji","Wobbly Tobbly",$out);	
	$out = str_replace("Lee","Fuzzy Eyebrows",$out);	
	$out = str_replace("Sakura","Sakura-chan",$out);	
	$out = str_replace("Naruto","The Main Boy",$out);	
	$out = str_replace("Forest","War Zone",$out);		
	$out = str_replace("forest","war zone",$out);	
	$out = str_replace("Bank","Gama Treasury",$out);
	$out = str_replace("Staff","Seadog",$out);	
	$out = str_replace("Weapon","Gut",$out);	
	$out = str_replace("Armor","Ninja Outfit",$out);
	$out = str_replace("weapon","gut",$out);	
	$out = str_replace("armor","ninja outfit",$out);		
	$out = str_replace("defeated","trashed",$out);
	$out = str_replace("lost","was trashed",$out);	
	$out = str_replace("It's", "It be",$out);
	$out = str_replace("it's", "it be",$out);
	if (e_rand(0,4) == 1) $out = str_replace("[^`]!",", dattebayo!",$out);
	$out = preg_replace("'([^ .,!?]+)ing '","\\1in' ",$out);
	$out = preg_replace("/ [s]{0,1}he /i"," that gutless moron ",$out);
	if (e_rand(0,4) == 1)
		$out = str_replace(". ",", 'tebayo. ",$out);
	if (e_rand(0,4) == 1)
		$out = str_replace(", ",", ne..ne, ",$out);
	if (e_rand(0,9) == 1)
		$out = str_replace(". ", ". Avast, ", $out);
	$out = str_replace("hello ", "dattebayo, ", $out);
	$out = str_replace("Hello ", "Dattebayo, ", $out);
	$out = preg_replace("'( |`.)(money|gold)( |`.)'", "\\1pieces o' gold\\3", $out);
	$out = preg_replace("'(Money|Gold) '", "Pieces o' gold ", $out);
	return $out;
}

function holiday_naruto_dohook($hookname,$args){
	switch($hookname){
	case "holiday":
		if(get_module_pref("user_ignore")) break;
		$mytime = get_module_setting("start");
		list($smonth,$sday) = explode("-",$mytime);
		$smonth=(int)$smonth;
		$sday=(int)$sday;
		$mytime = get_module_setting("end");
		list($emonth,$eday) = explode("-", $mytime);
		$emonth = (int)$emonth;
		$eday = (int)$eday;

		$month = (int)date("m");
		$day = (int)date("d");
		if ($month >= $smonth && $month <= $emonth &&
				$day >= $sday && $day <= $eday) {
			$args['text'] = holiday_naruto_munge($args['text']);
		}
		break;
	}
	return $args;
}

function holiday_naruto_run(){

}
?>
