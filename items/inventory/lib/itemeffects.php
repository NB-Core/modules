<?php
// Itemeffect by Edorian & Christian Rutsch (c) 2006
// currently contains modifications for
// - turns, attack, defense
// - hitpoints / maxhipoints
// - charm, gold, gems
// - experience (with check, if it should be modified or multiplied);
// - specialties (uses for all specialties can be set, skill levels for the chosen one)
// - deathpower, gravefights
// - travel, extraflirt
// - teleport
// - ability to apply or strip buffs

function get_effect($item = false, $noeffecttext = "", $giveoutput = true) {
	global $session;
	$out = array();
	if ($item === false) {
		if ($noeffecttext == "") {
			$args = modulehook("item-noeffect", array("msg"=>"`&Nothing happens.`n", "item"=>$item));
			$out[] = array("`&Nothing happens.`n");
		} else {
			$out[] = array($noeffecttext);
		}
	} else {
		debug($item['execvalue']);
		eval($item['execvalue']);

		if(isset($hitpoints) && $hitpoints <> 0) {
			if (!isset($override_maxhitpoints)) {
				$override_maxhitpoints = false;
			}
			if($hitpoints > 0) {
				if(($session['user']['hitpoints'] >= $session['user']['maxhitpoints']) && $override_maxhitpoints == false) {
				} else if(($session['user']['hitpoints'] + $hitpoints > $session['user']['maxhitpoints']) && $override_maxhitpoints == false) {
					$session['user']['hitpoints'] = $session['user']['maxhitpoints'];
					$out[] = "`^Your hitpoints have been `@fully`^ restored.`n";
				} else {
					if ($override_maxhitpoints == false) {
						$hitpoints = min($session['user']['maxhitpoints'] - $session['user']['hitpoints'], $hitpoints);
					}
					if ($hitpoints > 0) {
						$session['user']['hitpoints'] += $hitpoints;
						if ($hitpoints==1) {
							$out[] = array("`^You have been `@healed`^ for %s point.`n", $hitpoints);
						} else {
							$out[] = array("`^You have been `@healed`^ for %s points.`n", $hitpoints);
						}
					}
				}
			} else if ($hitpoints < 0) {
				if($session['user']['hitpoints'] + $hitpoints > 0) {
					output("`^You `4loose`^ %s hitpoints.", abs($hitpoints));
					$session['user']['hitpoints'] += $hitpoints;
				} else if (!$killable) {
					$session['user']['hitpoints'] = 1;
					$out[] = "`^You were `\$almost`^ killed.`n";
				} else {
					$experience = -$killable/100;
					$out[] = "`\$You die.`n";
				}
			}
		}

		if (isset($turns) && $turns <> 0) {
			$session['user']['turns'] += $turns;
			debuglog("'s turns were altered by $turns by item {$item['itemid']}.");
			if ($turns > 0) {
				if($turns==1){
					$out[] = array("`^You `@gain`^ one turn.`n");
				}else{
					$out[] = array("`^You `@gain`^ %s turns.`n", $turns);
				}
			} else {
				if ($session['user']['turns'] <= 0) {
					$out[] = array("`^You `\$lose`^ all your turns.`n");
					$session['user']['turns'] = 0;
				} else {
					if($turns==-1){
						$out[] = array("`^You `\$lose`^ one turn.`n");
					}else{
						$out[] = array("`^You `\$lose`^ %s turns.`n", abs($turns));
					}
				}
			}
		}

		if (isset($attack) && $attack <> 0) {
			$session['user']['attack'] += $attack;
			debuglog("'s attack was altered by $attack by item {$item['itemid']}.");
			if ($attack > 0) {
				$out[] = array("`^Your attack is `@increased`^ by %s.`n", $attack);
			} else {
				if ($session['user']['attack'] <= 1) {
					$out[] = array("`^You `\$lose`^ all your attack except the strength of your bare fists.`n");
					$session['user']['attack'] = 1;
				} else {
					$out[] = array("`^Your attack is `\$decreased`^ by %s.`n", abs($attack));
				}
			}
		}

		if (isset($defense) && $defense <> 0) {
			$session['user']['defense'] += $defense;
			debuglog("'s defense was altered by $defense by item {$item['itemid']}.");
			if ($defense > 0) {
				$out[] = array("`^Your defense is `@increased`^ by %s.`n", $defense);
			} else {
				if ($session['user']['defense'] <= 1) {
					$out[] = array("`^You `\$lose`^ all your defense except the durability of your everpresent T-Shirt.`n");
					$session['user']['defense'] = 1;
				} else {
					$out[] = array("`^Your defense is `\$decreased`^ by %s.`n", abs($defense));
				}
			}
		}

		if (isset($charm) && $charm <> 0) {
			$session['user']['charm'] += $charm;
			if ($charm > 0) {
				$out[] = array("`^Your charm is `@increased`^ by %s.`n", $charm);
			} else {
				if ($session['user']['charm'] <= 0) {
					$out[] = array("`^You `\$lose`^ all your charm.`n");
					$session['user']['charm'] = 0;
				} else {
					$out[] = array("`^Your charm is `\$decreased`^ by %s.`n", abs($charm));
				}
			}
		}


		if (isset($maxhitpoints) && $maxhitpoints <> 0) {
			if ($maxhitpoints > 0) {
				$session['user']['maxhitpoints'] += $maxhitpoints;
				$out[] = array("`^Your maximum hitpoints are permanently `@increased`^ by %s.`n", $maxhitpoints);
			} else {
				$minhp = $session['user']['level'] * 10;
				if (($session['user']['maxhitpoints'] + $maxhitpoints) < $minhp) $maxhitpoints = $session['user']['maxhitpoints'] - $minhp;
				if ($maxhitpoints < 0) {
					$out[] = array("`^Your maximum hitpoints are permanently `\$decreased`^ by %s.`n", abs($maxhitpoints));
					$session['user']['maxhitpoints'] += $maxhitpoints;
				}
			}
		}

		if (isset($uses)) {
			$modules = modulehook("specialtymodules");
			$names = modulehook("specialtynames");
			if (is_array($uses)){
				foreach ($uses as $key=>$val) {
					if ($val > 0) {
						if($val==1){
							$out[] = array("`^You `@gain`^ one point in %s.`n",$val, $names[$key]);
						} else {
							$out[] = array("`^You `@gain`^ %s points in %s.`n",$val, $names[$key]);
						}
					} else {
						$val = min(abs($val), get_module_pref("uses", $modules[$key]));
						if ($val==1){
							$out[] = array("`^You `\$lose`^ one point in %s.`n",$val, $names[$key]);
						}else{
							$out[] = array("`^You `\$lose`^ %s points in %s.`n",$val, $names[$key]);
						}
						$val *= -1;
					}
					increment_module_pref("uses", $val, $modules[$key]);
				}
			} else if ($uses == 'looseall') {
				foreach ($modules as $val) 
					set_module_pref("uses", 0, $val);
				$out[] = "`^You `\$lose all`^ uses in `\$all`^ specialties.`n";
			} else {
				if (is_numeric($uses) && $uses > 0 && $session['user']['specialty'] != "") {
					increment_module_pref("uses", $uses, $modules[$session['user']['specialty']]);
					$out[] = array("`^You `@gain`^ %s points in %s.`n",$uses, $names[$session['user']['specialty']]);
				} else if (is_numeric($uses) && $session['user']['specialty'] != "") {
					$out[] = array("`^You `\$lose`^ %s points in %s.`n",$uses, $names[$key]);
				}
			}
		}

		if (isset($increment_specialty) && $increment_specialty > 0) {
			require_once("lib/increment_specialty.php");
			while($increment_specialty) {
				increment_specialty("`^");
				$increment_specialty--;
			}
			$giveoutput = false;
		}

		if (isset($gems) && $gems != 0) {
			$session['user']['gems'] += $gems;
			debuglog("'s gems were altered by $gems by item {$item['itemid']}.");
			if ($gems > 0) {
				if($gems==1){
					$out[] = array("`^You `@gain`^ one gem.`n");
				}else{
					$out[] = array("`^You `@gain`^ %s gems.`n", $gems);
				}
			} else {
				$gems = min(abs($gems), $session['user']['gems']);
				if($gems==1){
					$out[] = array("`^You `\$lose`^ one gem.`n", $gems);
				}else{
					$out[] = array("`^You `\$lose`^ %s gems.`n", $gems);
				}
			}
		}

		if (isset($gold) && $gold != 0) {
			$session['user']['gold'] += $gold;
			debuglog("'s gold were altered by $gold by item {$item['itemid']}.");
			if ($gold > 0) {
				$out[] = array("`^You `@gain`^ %s gold.`n", $gold);
			} else {
				$gold = min(abs($gold), $session['user']['gold']);
				$out[] = array("`^You `\$lose`^ %s gold.`n", $gold);
			}
		}

		if (isset($experience) && $experience != 0)	{
			if(is_float($experience))
				$bonus = round($session['user']['experience'] * $experience, 0);
			else
				$bonus = $experience;
			$session['user']['experience'] += $experience;
			debuglog("'s experience was altered by $bonus by item {$item['itemid']}.");
			if ($bonus > 0) {
				$out[] = array("`^You `@gain`^ %s experience.`n", $gold);
			} else {
				$bonus = min(abs($bonus), $session['user']['experience']);
				$out[] = array("`^You `\$lose`^ %s experience.`n", $gold);
			}
		}

		if (isset($deathpower) && $deathpower != 0) {
			$session['user']['deathpower'] += $deathpower;
			if ($deathpower > 0) {
				$out[] = array("`^You `@gain`^ %s favor with `\$Ramius`0.`n", $deathpower);
			} else {
				$deathpower = min(abs($deathpower), $session['user']['deathpower']);
				$out[] = array("`^You `\$lose`^ %s favor with `\$Ramius`0.`n", $deathpower);
			}
		}

		if (isset($gravefights) && $gravefights != 0) {
			$session['user']['gravefights'] += $gravefights;
			if ($gravefights > 0) {
				$out[] = array("`^You `@gain`^ %s gravefights.`n", $gravefights);
			} else {
				$deathpower = min(abs($gravefights), $session['user']['gravefights']);
				$out[] = array("`^You `\$lose`^ %s gravefights.`n", $gravefights);
			}
		}

		if (isset($extraflirt) && is_module_active("lovers") && $extraflirt == true && get_module_pref("seenlover", "lovers")) {
			set_module_pref("seenlover", false, "lovers");
			$him = translate_inline("him");
			$her = translate_inline("her");
			require_once("lib/partner.php");
			$out[] = array("`^You miss %s`^ and want to see %s again.`n", get_partner(), $session['user']['sex']?$him:$her);
		}

		if (isset($extratravel) && is_module_active("cities") && $extratravel != 0) {
			increment_module_pref("traveltoday", -$extratravel, "cities");
			if ($extratravel > 0) {
				$out[] = array("`^You feel `@refreshed`^.");
				$out[] = array("`^You may travel %s times `@more`^ today.`n", $extratravel);
			} else {
				$out[] = array("`^You feel `\$tired`^.");
				$out[] = array("`^You may travel %s times `\$less`^ today.`n", $extratravel);
			}
		}

		if (isset($teleport) && is_module_active("cities")) {
			$session['user']['location']=$teleport;
		}

		if (isset($buff) && is_array($buff)) {
			require_once("lib/buffs.php");
			apply_buff("item-{$item['itemid']}", $buff);
			$out[] = array("`^Something feels strange within your body.`n");
		}

		if (isset($extrabuff) && is_array($extrabuff)) {
			require_once("lib/buffs.php");
			foreach ($extrabuff as $key=>$val) {
				if(has_buff($key)) {
					foreach ($val as $vkey=>$vval) {
						$session['bufflist'][$key][$vkey] += $vval;
						$things = true;
					}
				}
			}
			if ($things) $out[] = array("`^You feel something strange happening.`n");
		}

		if (isset($strip)) {
			require_once("lib/buffs.php");
			if (has_buff($strip)) {
				strip_buff($strip);
				$out[] = array("`^You have a weird feeling.`n");
			}
		}

	}

	$args = modulehook("itemeffect", array("out"=>$out, "item"=>$item));
	$out = $args['out'];
	$effect_text='';
	if (count($out) == 0) {
		if ($noeffecttext == "") {
			$args = modulehook("item-noeffect", array("msg"=>"`&Nothing happens.`n", "item"=>$item));
			$out[] = $args['msg'];
		} else if ($giveoutput) {
			$out[] = array($noeffecttext);
		}
	} else {
		tlschema("inventory");
		foreach ($out as $val) {
			if (is_array($val)) $effect_text.= sprintf_translate(...$val);
				else $effect_text .= sprintf_translate((string)$val);
		}
		tlschema();

	}
	return $effect_text;
}
?>
