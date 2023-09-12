<?php

global $session,$badguy;
if ($session['user']['dragonkills']>=8) {
	if (!isset($badguy['initialsetup']) || $badguy['initialsetup']!=1) {
		$badguy['jutsupoints']=$session['user']['level']+3;
		$badguy['initialsetup']=1;
		$badguy['jutsurounds']=0;
		$badguy['jutsuactive']=0;
	}
	if (isset($badguy['jutsurounds']) && ((int)$badguy['jutsurounds'])>0 && isset($badguy['jutsuactive'])) {
		switch($badguy['jutsuactive']) {
			case 'Mizuchi':
				$msg=translate_inline('`$%s`3 gets sliced up by %s`3 for %s`3 points!`n');
				$ceil=round($session['user']['dragonkills']/2);
				$hits=e_rand(2,4);
				for ($i=0;$i<$hits;$i++) {
					$dmg=e_rand(1,e_rand(1,round($ceil)));
					output_notl($msg,$session['user']['name'],$badguy['creaturename'],$dmg);
					$session['user']['hitpoints']-=$dmg;
				}
				$badguy['diddamage']=1;
				$badguy['jutsurounds']-=1;
				break;
		}
	} else {
		$chosen=0;
		while ($chosen!=1) {
			switch (e_rand(0,3)) {
				case 1:
					if ($badguy['jutsupoints']<4) break;
					output("`\$%s: `\$\"`4M`jumyou `4J`jinpuuryu `4S`jatsujin `)Ken...`\$\"!`n",$badguy['creaturename']);
					output("`i`4M`\$izuchi`3! `i`n`q%s`q boosts out a gigantic airwave out of her sword!`n",$badguy['creaturename']);
					$badguy['jutsuactive']='Mizuchi';
					$badguy['jutsupoints']-=4;
					$badguy['jutsurounds']=3;
					$chosen=1;
					break;
				case 2:
					if ($badguy['jutsupoints']<3) break;
					output("`\$%s: `\$\"`4M`jumyou `4J`jinpuuryu `4S`jatsujin `)Ken...`\$\"!`n",$badguy['creaturename']);
					output("`i`4G`\$enbu`3! `i`n`q%s`q calls forth a black tortoise entwined with serpents... you have a harder time getting through while she is attacking you easier!!`n",$badguy['creaturename']);
					$badguy['jutsuactive']='Genbu';
					$badguy['jutsupoints']-=3;
					apply_buff('vampirelord_bride_genbu',
							array(
								"name"=>"`4M`jumyou `4J`jinpuuryu `4S`jatsujin Ken!",
								"rounds"=>4,
								"atkmod"=>0.7,
								"badguyatkmod"=>1.3,
								"minioncount"=>1,
								"expireafterfight"=>1,
								"effectmsg"=>"`)You are hindered by the serpents!",
								"schema"=>"module-vampirelord_bride",
							     ));
					$chosen=1;
					break;
				case 3:
					if ($badguy['jutsupoints']<2) break;
					output("`\$%s: `\$\"`4M`jumyou `4J`jinpuuryu `4S`jatsujin `)Ken...`\$\"!`n",$badguy['creaturename']);
					output("`i`4S`\$uzaku`3! `i`n`q%s`q calls forth a giant phoenix, invulnerable but dealing damage through his body heat!!`n",$badguy['creaturename']);
					$badguy['jutsuactive']='Suzaku';
					$badguy['jutsupoints']-=2;
					apply_buff('vampirelord_bride_suzaku',
							array(
								"name"=>"`4M`jumyou `4J`jinpuuryu `4S`jatsujin Ken!",
								"rounds"=>2,
								"mingoodguydamage"=>1,
								"maxgoodguydamage"=>5+ceil($session['user']['strength']/7),
								"minioncount"=>$session['user']['level'],
								"effectmsg"=>"`\$You are burnt for {damage} damage!",
								"schema"=>"module-vampirelord_bride",
							     ));
					$chosen=1;
					break;				
				default:
					$chosen=1;
			}
		}
	}	
}

?>

