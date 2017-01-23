<?php

function personalpetitions_getmoduleinfo() {
	$info = array(
		"name"=>"Personal Petition Categories",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"",
		"settings"=>array(
			"Petition Settings,title",
			"categories"=>"Comma Seperated Categories please here,text|",
			"Note: Do NEVER remove categories if you currently have petitions in one of latter ones here! If you do you screw up the display and the categories shift by one. If you drop a category rename it to 'reserve' or something like that.,note",
		),
	);
	return $info;
}

function personalpetitions_install() {
	module_addhook_priority("petition-status",50);
	return true;
}

function personalpetitions_uninstall() {
	return true;
}


function personalpetitions_dohook($hookname, $args) {
	global $session;
	switch ($hookname) {
		case "petition-status":
			$list=get_module_setting('categories');
			if ($list=='') break;
			$list=explode(",",$list);
			$statuses=array(
				5=>"`\$Top Level`0",
				4=>"`^Escalated`0",
				0=>"`bUnhandled`b",
				);

			$i=50; //safety limiter
			$new=array();
			foreach ($list as $category) {
				$new[$i]=$category;
				$i++;
			}
			$statuses=$statuses+$new+
				array(
					1=>"In-Progress",
					6=>"`%Bug`0",
					7=>"`#Awaiting Points`0",
					3=>"`!Informational`0",
					2=>"`iClosed`i",
					)
				;
			return $statuses;
			break;
		default:
		break;
	}
	return $args;
}

function personalpetitions_run(){
}

?>
