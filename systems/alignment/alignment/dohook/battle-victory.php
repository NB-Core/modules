<?php
			if ($args['type'] == "pvp" && get_module_setting("pvp")){
				$ual = get_module_pref("alignment");
				$al = get_module_pref("alignment","alignment",$badguy['creatureid']);
				if ($al > $goodalign && $ual < $evilalign){
					$new = round($session['user']['level']/2);
					output("`n`bYou have smote a good person, and as your are evil... it makes you more evil.`b`0`n");
					require_once("modules/alignment/func.php");
					align("-$new");
				}elseif($al < $evilalign && $ual > $goodalign){
					$new = round($session['user']['level']/2);
					output("`n`bYou have destroyed an evil person, and as you are good... it makes you more good.`b`0`n");
					require_once("modules/alignment/func.php");
					align("+$new");
				}else{
					switch (e_rand(1,2)){
						case 1:
							$new = round($session['user']['level']/3);
							output("`n`bYou have destroyed a person... strangely, it makes you more good.`b`0`n");
							require_once("modules/alignment/func.php");
							align("+$new");
							break;
						case 2:
							$new = round($session['user']['level']/3);
							output("`n`bYou have destroyed a person... strangely, it makes you more evil.`b`0`n");
							require_once("modules/alignment/func.php");
							align("-$new");
							break;
					}
				}
			}
			if ($args['type'] == "pvp" && get_module_setting("de-pvp")){
				$ual = get_module_pref("demeanor");
				$al = get_module_pref("demeanor","alignment",$badguy['creatureid']);
				if ($al > $lawalign && $ual < $chaosalign){
					$new = round($session['user']['level']/2);
					output("`n`bYou have smote a lawful person, and as your are chaotic... it makes you more chaotic.`b`0`n");
					require_once("modules/alignment/func.php");
					demeanor("-$new");
				}elseif($al < $chaosalign && $ual > $lawalign){
					$new = round($session['user']['level']/2);
					output("`n`bYou have destroyed a chaotic person, and as you are lawful... it makes you more lawful.`b`0`n");
					require_once("modules/alignment/func.php");
					demeanor("+$new");
				}else{
					switch (e_rand(1,2)){
						case 1:
							$new = round($session['user']['level']/3);
							output("`n`bYou have destroyed a person... strangely, it makes you more lawful.`b`0`n");
							require_once("modules/alignment/func.php");
							demeanor("+$new");
							break;
						case 2:
							$new = round($session['user']['level']/3);
							output("`n`bYou have destroyed a person... strangely, it makes you more chaotic.`b`0`n");
							require_once("modules/alignment/func.php");
							demeanor("-$new");
							break;
					}
				}
			}
			if ($args['type'] == 'forest' || $args['type'] == 'travel'){
				$id = $badguy['creatureid'];
				$al = get_module_objpref("creatures",$id,"al");
				if ($al != ""){
					require_once("modules/alignment/func.php");
					align($al);
				}
				$de = get_module_objpref("creatures",$id,"de");
				if ($de != ""){
					require_once("modules/alignment/func.php");
					demeanor($de);
				}
			}
?>