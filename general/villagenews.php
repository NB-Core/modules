<?php
use Lotgd\Translator;

function villagenews_getmoduleinfo(){
	$info = array(
		"name"=>"Village News",
		"version"=>"1.04",
		"author"=>"`#Lonny Luberts, `&modified by Oliver Brendel, usable 1.0.4+ (news schema)",
		"category"=>"PQcomp",
		"download"=>"",
		"prefs"=>array(
			"Village News Module User Preferences,title",
			"user_villnews"=>"Display Latest News in the Village,bool|1",
		),
		"settings"=>array(
			"Village News Module Settings,title",
			"showhome"=>"Show news on Home Page,enum,0,No,1,Above Login,2,Below Login",
			"newslines"=>"Number of news lines to display in the villages,int|4",
		),
	);
	return $info;
}

function villagenews_install(){
	module_addhook("village-desc");
	module_addhook("index");
	//module_addhook("footer-home");
	return true;
}

function villagenews_uninstall(){
	return true;
}

function villagenews_dohook($hookname,$args){
	switch($hookname){
		case "village-desc":
			if (get_module_pref('user_villnews')){
				output("`n`2`c`bLatest News`b`c");
				output_notl("`2`c-=-=-=-=-=-=-=-`c");
				output_notl("%s", villagenews_getnews());
				output_notl("`n");
			}
		break;
		case "index":
			if (get_module_setting('showhome') == 1){
			    output("`n`2`bLatest News`b`n");
				output_notl("`2-=-=-=-=-=-=-=-`c`n");
				output_notl("%s", villagenews_getnews());
				output_notl("`c`n");
			}
		break;
		case "footer-home":
			if (get_module_setting('showhome') == 2){
				output("`n`2`c`bLatest News`b`c");
				output_notl("`2`c-=-=-=-=-=-=-=-`c");
				output_notl("%s", villagenews_getnews());
				output_notl("`n");
			}

		break;
	}
	return $args;
}

function villagenews_getnews() {
	global $session;
	if (isset($session['user']['prefs']['language'])) {
		$lang=$session['user']['prefs']['language'];
	} else {
		$lang="en";
	}
	$datacache=datacache("villagenews-storage-$lang",120);
	$returnstring = "";
	if ($datacache==false) {
			$len=get_module_setting('newslines');
			$sql = "SELECT * FROM ".db_prefix("news")." ORDER BY newsid DESC LIMIT ".$len;
			$result = db_query($sql) or die(db_error(LINK));
			$rownum = db_num_rows($result);
			if (db_num_rows($result)==0) return translate_inline("`i`cNo news available`c`i");
			$len = min($len,$rownum); // max only what we can display

			for ($i=0;$i<$len;$i++){
				$row = db_fetch_assoc($result);
				tlschema($row['tlschema']);
				if ($row['arguments']>""){
					$arguments = array();
					$base_arguments = unserialize($row['arguments']);
					array_push($arguments,$row['newstext']);
					if (!is_array($base_arguments)) continue;
					foreach ($base_arguments as $val) {
						array_push($arguments,$val);
					}
					$newnews = Translator::sprintfTranslate(...$arguments);
				}else{
					$newnews = translate_inline((string)$row['newstext']);
				}
				tlschema();
				$returnstring.="`c $newnews `c";
				if ($i <> $len) $returnstring.="`2`c-=-=-=-=-=-=-=-`c";
			}
			updatedatacache("villagenews-storage-$lang",$returnstring);
	} else {
		$returnstring=$datacache;
	}
	return $returnstring;
}
?>
