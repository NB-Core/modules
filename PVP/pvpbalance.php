<?php
// mod for quick fights in the wood
/*please visit dragonprime.net, I also share German translation, the name there is Nightborn*/
/*fight log copied from battle.php*/
/*
   v1.01 added a cap as a setting
   v1.02 moved it to pvpmodify targets with a text :) much better, no more changes to core pvp.php
   v1.03 removed the unnecessary sql queries and also a major bug ... the location "real one" is not grabbable from here... I can only block, not notify
   v1.04 summarized the notification who is sleeping here but not attackable...to make it easier and not have dozens of lines when many people are here.
   v1.05 optionally have a percentage as a minimum limit, not only a hard number.
 */

function pvpbalance_getmoduleinfo(){
	$info = array(
			"name"=>"PvP Balancing based on DKs",
			"version"=>"1.04",
			"author"=>"`2Oliver Brendel`0",
			"category"=>"PVP",
			"download"=>"http://lotgd-downloads.com",
			"settings"=>array(
				"PvP Balance - Preferences, title",
				"adjustment"=>"1. Attacked Player can be how many Dks lower than attacker,floatrange,1,100,1|5",
				"adjustmentup"=>"2. Attacked Player can be how many Dks higher than attacker,floatrange,1,100,1|10",
				"Now if you want to have it turned on or not,note",
				"attacklower"=>"Is option 1 active?,bool|1",
				"attackhigher"=>"Is option 2 active?,bool|0",
				"Note: the following settings add to the settings above. They are a minimum and below settings add if the range would extend due to them,note",
				"relativedown"=>"Do you want to have the player attack people x % lower in DKs than him?,bool|1",
				"relativitydown"=>"How many percent can he have less kills (rounded up),floatrange,1,100,1|15",
				"relativeup"=>"Do you want to have the player attack people x % higher in DKs than him?,bool|0",
				"relativityup"=>"How many percent can he have more kills (rounded up),floatrange,1,100,1|15",
				),

		     );
	return $info;
}

function pvpbalance_install(){
	module_addhook_priority("pvpmodifytargets", 100);
	if (!is_module_active('pvpbalance')){
		debug("`4Installing PvP Balancing Module.`n");
	}else{
		debug("`4Updating PvP Balancing Module.`n");
	}
	return true;
}

function pvpbalance_uninstall(){
	debug("Uninstalling this module.`n");
	return true;
}


function pvpbalance_dohook($hookname,$args){
	global $session;
	switch ($hookname)
	{
		case "pvpmodifytargets":
			$adjust=get_module_setting("adjustment");
			$adjusthigher=get_module_setting("adjustmentup");
			$lower=get_module_setting("attacklower");
			$higher=get_module_setting("attackhigher");
			$reldown=get_module_setting("relativedown");
			$relativitydown=get_module_setting("relativitydown");
			$relup=get_module_setting("relativeup");
			$relativityup=get_module_setting("relativityup");
			$lownames=array();
			$highnames=array();
			if ($reldown) {
				$relative=ceil($session['user']['dragonkills']*$relativitydown/100+0.5);
				if ($adjust<$relative) $adjust=$relative;
			}
			if ($relup) {
				$relative=ceil($session['user']['dragonkills']*$relativityup/100+0.5);
				if ($adjusthigher<$relative) $adjusthigher=$relative;
			}
			foreach ($args as $key=>$row) {
				if (($row['dragonkills']+$adjust)<$session['user']['dragonkills'] && $lower ) {
					$args[$key]['invalid'] = "too weak";
					//if ($session['user']['location']!=$row['location']) continue;
					//array_push($lownames,$row['name']);
				}
				if ($row['dragonkills']>($session['user']['dragonkills']+$adjusthigher) && $higher ) {
					$args[$key]['invalid'] = "too powerful";
					//if ($session['user']['location']!=$row['location']) continue;
					//array_push($highnames,$row['name']);

				}
			}/*
			    if (count($lownames)>0) {
			    $out=implode("`4,",$lownames);
			    if (count($lownames)==1) $count=translate_inline("a young foe");
			    else $count=translate_inline("young foes");
			    output("`4You see %s`4 sleeping here, yet you dare not to attack such %s.",$out,$count);
			    output_notl("`n`0");
			    }
			    if (count($highnames)>0) {
			    $out=implode("`4,",$highnames);
			    if (count($highnames)==1) $count=translate_inline("a frightening foe");
			    else $count=translate_inline("frightening foes");
			    output("`4You see %s`4 sleeping here, yet you dare not to attack such %s.",$out,$count);
			    output_notl("`n`0");
			    }	
			  */
			break;
	}
	return $args;
}

function pvpbalance_run(){
}


?>
