<?php

//Kabuto
global $session,$badguy;debug($badguy);
if (!isset($badguy['initial']) || !$badguy['initial']) {
	$badguy['jutsupoints']=21;
	$badguy['maxhp']=$badguy['creaturehealth'];
	$badguy['initial']=1;
}
if (isset($badguy['healingrounds']) && $badguy['healingrounds']>0) {
	$points=min($badguy['healing'],$badguy['maxhp']-$badguy['creaturehealth']);
	if ($points>0) {
		output("%s`\$ heals himself for %s points!`n",$badguy['creaturename'],$points);
		$badguy['creaturehealth']+=$badguy['healing'];
		$badguy['healingrounds']--;
	} else {
		output("%s`\$ would regenerate but has no wound!`n",$badguy['creaturename']);
	}
} //healing "buff"
if ($badguy['creaturehealth']<=150 && $badguy['jutsupoints']>2) {
	output("%s`\$ uses a stronger healing jutsu on himself!`n",$badguy['creaturename']);
	$power=e_rand(0,min(3,max(round($badguy['jutsupoints']/3),1)));
	$badguy['creaturehealth']+=min($power*$session['user']['dragonkills'],$badguy['maxhp']-$badguy['creaturehealth']);
	$badguy['jutsupoints']-=$power;
} elseif ($badguy['jutsupoints']>0) {
	//enough health, now use offensive jutsus
	$chosen=0;
	while ($chosen!=1) {
		switch (e_rand(0,4)) {
			case 4:
				//lucky, nothing done
				$chosen=1;
				break;
			case 3:
				//cheap regen, but at least 1 point necessary 
				if (isset($badguy['healningrounds']) && $badguy['healingrounds']>1) break;
				$badguy['jutsupoints']-=1;
				$badguy['healing']=$session['user']['dragonkills'];
				$badguy['healingrounds']=5;
				output("%s`\$: `!`iChikatsu Saisei no Jutsu!`i`n`t %s`t starts to regenerate with his `iChi`i`n",$badguy['creaturename'],$badguy['creaturename']);
				$chosen=1;
				break;
			case 2: 
				//ouch
				if ($badguy['jutsupoints']<2) break;
				$damage=5+e_rand(5,min(5,$session['user']['dragonkills']*2,$session['user']['dragonkills']));
				output("%s`\$: `!`iDokugiri!`i`n`t%s`t blows a cloud of poison gas at you!`n",$badguy['creaturename'],$badguy['creaturename']);
				output("`\$You suffer `t%s`\$ damage from the gas!`n",$damage);
				$session['user']['hitpoints']-=$damage;
				$badguy['jutsupoints']-=2;
				$chosen=1;
				break;
			case 1:
				if ($badguy['jutsupoints']<18) break;
				require_once("lib/buffs.php");
				if (has_buff('kabuto7')) break;
				output("%s`\$: `!`iShousen Jutsu: attack organs!`i`n`t%s`t focusses chakra to his hands and forms an invisible scalpel ready to hit you at vital spots!`n",$badguy['creaturename'],$badguy['creaturename']);
				apply_buff('kabuto7',array(
					"startmsg"=>"",
					"name"=>array("`!Shousen Jutsu from %s: `\$attack organs",$badguy['creaturename']),
					"rounds"=>10,
					"wearoff"=>"{badguy}'s damaged organs have healed.",
					"defmod"=>0.25,
					"roundmsg"=>"{goodguy} can not defend well!",
					"schema"=>"module-specialtysystem_medical"
				));
				$badguy['jutsupoints']-=18;
				$chosen=1;
				break;
			default:
			if (is_module_active("specialtysystem") && $session['user']['specialty']=='SS') {
				require_once("modules/specialtysystem/functions.php");
				//if it is the specialtysystem present
				$uses=specialtysystem_availableuses();
				if ($uses==0) break;
				$loss=e_rand(1,ceil($uses/4));
				output("%s`\$: `!`iChakra Kyuuin no Jutsu!`i`n`t%s`t grabs you in an indecent way and begins to absorb `^%s chakra point(s)`t!`n",$badguy['creaturename'],$badguy['creaturename'],$loss);
				specialtysystem_incrementuses('',$loss);
				$badguy['jutsupoints']+=$loss-5;
				//sadistic, for high dk players, isn't it? =)
				$chosen=1;
			}
		}
	}
}

?>
