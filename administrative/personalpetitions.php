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
			"categories"=>"Comma Separated Categories please here,text|",
			"Note: Do NEVER remove categories if you currently have petitions in one of latter ones here! If you do you screw up the display and the categories shift by one. If you drop a category rename it to 'reserve' or something like that.,note",
		),
	);
	return $info;
}

function personalpetitions_install() {
	module_addhook_priority("petition-status",50);
	module_addhook("petitioncount");
	return true;
}

function personalpetitions_uninstall() {
	return true;
}


function personalpetitions_dohook($hookname, $args) {
	global $session;
	switch ($hookname) {
		case "petitioncount":
			$sql = "SELECT count(petitionid) AS c,status FROM " . db_prefix("petitions") . " GROUP BY status";
			$result = db_query_cached($sql,"petition_counts");
			//fill a bigger array, most of this code is from core
			$petitions = array_fill(0,100,0);
			while ($row = db_fetch_assoc($result)) {
				$petitions[(int)$row['status']] = $row['c'];
			}
			//add critical 53 category to view, you may modify this for your server, I'm too lazy to make this generic
			$p = "`n `x`b<span style='font-size:2em'>{$petitions[53]}</span>`0|<span style='font-size:1.5em'>`v{$petitions[51]}`0</span>|`\${$petitions[5]}`0|`^{$petitions[4]}`0|`b{$petitions[0]}`b|{$petitions[1]}|`!{$petitions[3]}`0|`#{$petitions[7]}`0|`%{$petitions[6]}`0|`i{$petitions[2]}`i";
			$args['petitioncount'] = $p;
			
			break;
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
