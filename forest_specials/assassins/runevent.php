<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\Battle;
use Lotgd\MySQL\Database;

	$ninja_creature = array(
		"creaturename"=>get_module_setting("badguy-name"),
		"creatureweapon"=>get_module_setting("badguy-weapon"),
		"creaturelevel"=>($session['user']['level']+1),
		"creatureexp"=> max((int)($session['user']['experience']*0.05),1),
		"creatureattack"=>($session['user']['attack']+e_rand(1,3)),
		"creaturedefense"=>($session['user']['defense']+e_rand(1,3)),
		"creaturehealth"=>($session['user']['maxhitpoints']+e_rand(10,100)),
		"schema"=>"module-assassins",
	);
	$dkb = round($session['user']['dragonkills']*1.1);
	$shurikens = array(
		"startmsg"=>"`n`^Shurikens are being hurled at you!`n",
		"name"=>"`)Shurikens",
		"rounds"=>1,
		"wearoff"=>"The Ninja ceases the throwing of shurikens.",
		"minioncount"=>$session['user']['level'],
		"mingoodguydamage"=>1,
		"maxgoodguydamage"=>1+round($dkb/2),
		"effectmsg"=>"A shuriken hits you for {damage} damage.",
		"effectnodmgmsg"=>"A shuriken MISSES your head.",
		"activate"=>"roundstart",
		"schema"=>"module-assassins",
	);
	$kunai = array(
		"startmsg"=>"`^The Ninja reaches to its hips and beings hurling many kunai at you!`n",
		"name"=>"Kunai Salvo",
		"rounds"=>1,
		"wearoff"=>"The kunai fall to the ground, lacking the energy to make it to you.",
		"minioncount"=>e_rand(1,5),
		"mingoodguydamage"=>1,
		"maxgoodguydamage"=>1+$dkb,
		"effectmsg"=>"A kunai hits you for {damage} damage.",
		"effectnodmgmsg"=>"Your reflexes get you out of the way quickly.",
		"activate"=>"roundstart",
		"schema"=>"module-assassins",
	);
	$lightning = array(
		"startmsg"=>"`n`^The Ninja begins pulling lightning down from the sky!`n",
		"name"=>"`^Lightning",
		"rounds"=>1,
		"wearoff"=>"The lightning stops coming down.",
		"minioncount"=>$session['user']['level'],
		"mingoodguydamage"=>1,
		"maxgoodguydamage"=>2+$dkb,
		"effectmsg"=>"A lightning bolt hits you for {damage} damage.",
		"effectnodmgmsg"=>"You skillfully dodge a lightning bolt.",
		"activate"=>"roundstart",
		"schema"=>"module-assassins",
	);
	$fireball = array(
		"startmsg"=>"`n`^The Ninja starts hurling massive fireballs in your direction!`n",
		"name"=>"`\$Fireball",
		"rounds"=>1,
		"wearoff"=>"The fireballs begin to disappear.",
		"minioncount"=>$session['user']['level'],
		"mingoodguydamage"=>1,
		"maxgoodguydamage"=>3+$dkb,
		"effectmsg"=>"A fireball crashes into you, dealing {damage} damage.",
		"effectnodmgmsg"=>"Jumping high, you are able to clear the fireball.",
		"activate"=>"roundstart",
		"schema"=>"module-assassins",
	);
	$session['user']['specialinc'] = "module:assassins";
	output_notl("`n");
	switch ($op){
		case "": case "search":
			output("`@Deciding that you wish to take a little rest, you stop off in a clearing.");
			output("It is a beautiful day with wind rustling through the leaves.");
			output("You blink your eyes and see a pair staring right back at you.");
			output("A figure cloaked in black appears and stares at you.");
			$note=stripslashes(get_module_pref("note"));
			if (get_module_pref("marked")){
				output("`n`n`@The Ninja pulls out a piece of paper and then his weapon.");
				if ($note!='') {
					output("Formally the Ninja addresses you: \"`2%s`2, you are hereby delivered the following message by someone who wants your death...`@\"...`n`n",$session['user']['name']);
					output_notl("`c%s`c`n`n",$note);
				}
				output("It picks up its pace, as it dashes towards you.");
				output("Having no time to run away, you are thrust into battle!`n`n");
				$op = "prefight";
				httpset('op',$op);
			}else{
				addnav("Address the Ninja",$from."op=address");
				addnav("Leave",$from."op=leave");
			}
			break;
		case "leave":
			output("`@Not wishing to mingle with such riff-raff, you head out of the clearing.");
			break;
		case "address":
			output("`@The Ninja steps towards you and pulls a cloth from its eyes.");
			output("\"`2I am a shinobi that wanders the land, carrying out tasks for those that are too weak.");
			output("If you wish, I will seek out and kill a person that you select,`@\" the Ninja says in a gruff voice.");
			addnav("Take Offer",$from."op=find");
			addnav("Kill the Ninja",$from."op=prefight");
			addnav("Leave",$from."op=leave");
			break;
		case "find":
			$name = httppost('name');
                        if ($name){
                                addnav("Search Again",$from."op=find&revenge=$revenge");
                                $conn = Database::getDoctrineConnection();
                                $accountsTable = Database::prefix('accounts');
                                $userPrefsTable = Database::prefix('module_userprefs');
                                $searchPattern = "%".$name."%"; //else too much results

                                $sql = <<<SQL
SELECT a.name, a.acctid, a.level, a.dragonkills, 0 AS marker
FROM {$accountsTable} AS a
WHERE (a.name LIKE :search_name OR a.login LIKE :search_login)
    AND a.acctid <> :acctid
    AND a.acctid NOT IN (
        SELECT a2.acctid
        FROM {$accountsTable} AS a2
        LEFT OUTER JOIN {$userPrefsTable} AS b ON a2.acctid = b.userid
        WHERE (a2.name LIKE :search_name_not_in OR a2.login LIKE :search_login_not_in)
            AND a2.acctid <> :acctid_not_in
            AND b.modulename = :module_marked
            AND b.setting = :setting_marked
            AND b.value = :marked_value
    )
UNION
SELECT a.name, a.acctid, a.level, a.dragonkills, b.value AS marker
FROM {$accountsTable} AS a
LEFT JOIN {$userPrefsTable} AS b ON a.acctid = b.userid
WHERE (a.name LIKE :search_name_union OR a.login LIKE :search_login_union)
    AND a.acctid <> :acctid_union
    AND b.modulename = :module
    AND b.setting = :setting
LIMIT 25
SQL;

                                $parameters = [
                                        'search_name' => $searchPattern,
                                        'search_login' => $searchPattern,
                                        'acctid' => (int) $session['user']['acctid'],
                                        'search_name_not_in' => $searchPattern,
                                        'search_login_not_in' => $searchPattern,
                                        'acctid_not_in' => (int) $session['user']['acctid'],
                                        'module_marked' => 'assassins',
                                        'setting_marked' => 'marked',
                                        'marked_value' => '1',
                                        'search_name_union' => $searchPattern,
                                        'search_login_union' => $searchPattern,
                                        'acctid_union' => (int) $session['user']['acctid'],
                                        'module' => 'assassins',
                                        'setting' => 'marked',
                                ];

                                $types = [
                                        'search_name' => ParameterType::STRING,
                                        'search_login' => ParameterType::STRING,
                                        'acctid' => ParameterType::INTEGER,
                                        'search_name_not_in' => ParameterType::STRING,
                                        'search_login_not_in' => ParameterType::STRING,
                                        'acctid_not_in' => ParameterType::INTEGER,
                                        'module_marked' => ParameterType::STRING,
                                        'setting_marked' => ParameterType::STRING,
                                        'marked_value' => ParameterType::STRING,
                                        'search_name_union' => ParameterType::STRING,
                                        'search_login_union' => ParameterType::STRING,
                                        'acctid_union' => ParameterType::INTEGER,
                                        'module' => ParameterType::STRING,
                                        'setting' => ParameterType::STRING,
                                ];

                                $result = $conn->executeQuery($sql, $parameters, $types);
                                $rows = $result->fetchAllAssociative();
                                $rowCount = count($rows);
                                $n = translate_inline("Name");
                                $cost = translate_inline("Costs");
                                $action= translate_inline("Target");
                                if (httpget('revenge') && is_module_active('alignment')){
                                        require_once("modules/alignment/func.php");
                                        demeanor("-".e_rand(1,5));
                                }
                                if ($rowCount < 1) {
                                        output("`@\"`2Sorry, there was nobody with that name.`@\"");
                                        break;
                                }
                                rawoutput("<form action='".$from."op=sic'  method='post'>");
                                addnav("", $from."op=sic");
                                rawoutput("<table border=0 align='center' width='50%' cellpadding=0><tr><td></td><td>");
                                output("`2Please enter a short message that we should deliver to the victim (i.e. who wants to kill him and why, or just nothing):`n`n");
                                rawoutput("</td></tr><td valign='top'>");
                                rawoutput("<table border=0 align='center' valign='top' width='50%' cellpadding=0><tr class='trhead'><td>$action</td><td>$n</td><td>$cost</td></tr>");
                                $i = 0;
                                foreach ($rows as $row){
                                        $i++;
                                        $ac = $row['acctid'];
                                        if ($row['dragonkills']>25) {
                                                $gold_cost = round($row['level']*$gold_var);
                                                $gem_cost = round(($row['dragonkills']-25)*$gem_var/(2*log($row['dragonkills'])))+round(25*$gem_var)+1;
					} else {
						$gold_cost = round($row['level']*$gold_var);
						$gem_cost = round($gem_var*$row['dragonkills'])+1;
					}
					if (httpget('revenge')){
						$gold_cost = round($gold_cost/2);
						$gem_cost = round($gem_cost/2);
					}
					rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
					if ($row['marker']) {
						rawoutput("</td><td>");
						output_notl("`i%s`i", $row['name']);
						rawoutput("</td><td>");
						output("We don't take contract");
					} else {
						rawoutput("<input type='radio' name='targetinfo' value='$ac|$gold_cost|$gem_cost'>");
						rawoutput("</td><td>");
						output_notl("%s", $row['name']);
						rawoutput("</td><td>");
						output("`@Gold: `^%s`0`n",$gold_cost);
						output("`@Gems: `%%s`0",$gem_cost);
					}
					rawoutput("</td></tr>");
				}
				rawoutput("</table>");
				rawoutput("<td>");
				rawoutput("<textarea name='note' class='input' cols='50' rows='10'></textarea>");
				rawoutput("</td></tr></table>");
				rawoutput("<input type='submit' class='button' value='".translate_inline("Make contract")."'></form>");
				rawoutput("</form>");
			}else{
				output("`@\"`2Please enter a name that you wish to have killed.`@\"`n`n");
				rawoutput("<form action='".$from."op=find&revenge=$revenge' method='POST'>");
				rawoutput("<input name='name' size='25'>");
				rawoutput("<input type='submit' class='button' value='".translate_inline("Preview List")."'></form>");
				addnav("",$from."op=find&revenge=$revenge");
			}
			addnav("Leave",$from."op=leave");
			break;
		case "sic":
			$targetinfo=explode("|",httppost('targetinfo'));
			$note=stripslashes(httppost('note'));
			if ($targetinfo[0]=='') {
				output("\"`2Hahaha, funny. Give me a name, bozo!`@\"");
				addnav("Search Again",$from."op=find&revenge=$revenge");
				addnav("Leave",$from."op=leave");
				break;
			}
			$session['user']['specialinc'] = "";

			if (is_module_active('alignment')) {
				require_once("modules/alignment/func.php");
				$demeanor = get_demeanor();
				$align = get_align();
				$fail = 0;
				if ($demeanor < get_module_setting("chaosalign","alignment")){
					output("`@The Ninja approaches you, but shirks away.");
					output("\"`2You are far too chaotic to execute this type of act. I cannot believe you will respect this agreement.`@\"`n`n");
					output("All of a sudden, the %s`2 slip into the shadows and are gone.",get_module_setting('badguy-name'));
					break;
				}else{
					if ($align >= get_module_setting("goodalign","alignment")){
						if (e_rand(1,100) < get_module_setting("chance_fail")) $fail = 1;
					}
				}
			}
			if (!$fail){
				if ($session['user']['gold'] > $targetinfo[1]
					&& $session['user']['gems'] > $targetinfo[2]){
					set_module_pref("marked",1,"assassins",$targetinfo[0]);
					set_module_pref("note",$note,"assassins",$targetinfo[0]);
					$session['user']['gold'] -= $targetinfo[1];
					$session['user']['gems'] -= $targetinfo[2];
					output("`@The Ninja nods, \"`2I shall hunt this whelp down and destroy.`@\"");
				}else{
					output("`@The Ninja shakes its head, \"`2You don't have enough to supplement my prices.");
					output("Leave now.`@\"");
				}
			}else{
				output("`@The Ninja arches a brow, \"`2I just noticed something about you.`n`n");
				output("I noticed that you are quite good and shouldn't be committing acts such as these. ");
				output("Get out of my sight.`@\"");
			}
			break;
		case "hilfeichbineinadminholtmichhierraus":
			output("Due to your powers as a god you teleport yourself out of it.");
			$session['user']['specialinc'] = "";
			break;
		case "done":
			$session['user']['specialinc'] = "";
			switch (httpget('mode')){
				case "kill":
					output("`@Not wishing to allow this creature to live, you pull out your %s `@and sever its head.",$session['user']['weapon']);
					output("You feel yourself growing more evil and chaotic.");
					require_once("modules/alignment/func.php");
					align("-".e_rand(1,10));
					demeanor("-".e_rand(1,10));
					break;
				case "heal":
					output("`@Since you are a good person, you stay behind and heal the major wounds.");
					output("As a result, you feel yourself growing more good.");
					require_once("modules/alignment/func.php");
					align(e_rand(1,10));
					break;
				case "arrest":
					output("`@Wishing to do the right thing, you turn the Ninja over to the proper authorities.");
					output("As a result, you feel more lawful.");
					require_once("modules/alignment/func.php");
					demeanor(e_rand(1,10));
					break;
			}
			break;
	}
	if ($op == "prefight"){
		$session['user']['badguy'] = createstring($ninja_creature);
		$op = "fight";
		httpset('op',$op);
	}
	if ($op == "fight"){
		$battle = true;
	}
	if (isset($battle) && $battle){
		switch (e_rand(1,10)){
			case 1: case 2: case 3: case 4: case 5: case 6:
				// nothing
				break;
			case 7:
				rawoutput("<big>");
				output("`c`gIs that all you got?`c`n");
				rawoutput("</big>");
				apply_buff("assassins-shuriken",$shurikens);
				break;
			case 8:
				rawoutput("<big>");
				output("`c`gYou are weak... why are you even trying?`c`n");
				rawoutput("</big>");
				apply_buff("assassins-lightning",$lightning);
				break;
			case 9:
				rawoutput("<big>");
				output("`c`gThat hurt... `\$not!`c`n");
				rawoutput("</big>");
				apply_buff("assassins-kunai",$kunai);
				break;
			case 10:
				rawoutput("<big>");
				output("`c`gPut some more muscle into it!`c`n");
				rawoutput("</big>");
				apply_buff("assassins-fireball",$fireball);
				break;
		}
		include("battle.php");
		if ($session['user']['hitpoints']<=0) {
			$victory=0;
			$defeat=1;		
		}
		if ($victory){
			strip_buff("assassins-fireball");
			strip_buff("assassins-shuriken");
			strip_buff("assassins-lightning");
			strip_buff("assassins-kunai");
			if (get_module_pref("marked")){
				output("`@Heavily injured, the ninja sent to kill you has been defeated.");
				output("What do you plan on doing?");
				addnav("Get Revenge",$from."op=find&revenge=1");
				addnav("Kill the Ninja",$from."op=done&mode=kill");
				addnav("Heal the Wounds",$from."op=done&mode=heal");
				addnav("Arrest the Ninja",$from."op=done&mode=arrest");
				addnav("Leave",$from."op=leave");
				set_module_pref("marked",0);
				set_module_pref("note",'');
			}else{
				$session['user']['specialinc'] = "";
				output("You wander out of the clearing, sheathing your %s.",$session['user']['weapon']);
				// Rewards are now handled by battle.php via creatureexp in $ninja_creature.
			}
		}elseif($defeat){
			strip_buff("assassins-fireball");
			strip_buff("assassins-shuriken");
			strip_buff("assassins-lightning");
			strip_buff("assassins-kunai");		
			$session['user']['experience'] *= .9;
			$session['user']['gold'] = 0;
			$session['user']['hitpoints']=0;
			output("`n`n`@The %s`@ member steals your gold and wanders off. You also lose 10%% of your experience.",get_module_setting("badguy-name"));
			if (get_module_pref("marked")){
				output("`@For a brief moment, you see the Ninja scratch a name off a list.");
				set_module_pref("marked",0);
				set_module_pref("note",'');
			}
			$session['user']['alive'] = FALSE;
			$session['user']['specialinc'] = "";
			addnav("Return to the Shades","shades.php");
			addnews("%s`^ was destroyed by %s`^ in the forest.",$session['user']['name'],get_module_setting("badguy-name"));
		}else{
			Battle::fightnav(true,false);
			if ($session['user']['superuser'] & SU_DEVELOPER) addnav("Escape to Village",$from."op=hilfeichbineinadminholtmichhierraus");
		}
	}
?>
