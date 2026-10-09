<?php

function rabidwerewolf_getmoduleinfo(){
	$info = array(
		"name"=>"Rabid Werewolf",
		"author"=>"Enhas, some code based a bit on Tattoo Monster",
		"version"=>"1.0",
		"category"=>"Forest Specials",
			"download"=>"http://dragonprime.net/users/Enhas/rabidwerewolf.txt",
	);
	return $info;
}

function rabidwerewolf_install(){
	module_addeventhook("forest", "return 1;");
	return true;
}

function rabidwerewolf_uninstall(){
	return true;
}

function rabidwerewolf_runevent($type,$link){
	global $session;


	$op = httpget('op');
	$session['user']['specialinc'] = "module:rabidwerewolf";
	$battle = false;
	  output("`n");

	switch ($op){
	case "":
	case "search":
		output("`2Making your way through a narrow forest path, you hear strange growling ahead.  ");
		output("Drawing your weapon and moving closer, you see a large beast feasting on the remains of a small animal.  ");
		output("The beast suddenly starts sniffing the air!  It has caught your scent, and is slowly moving towards you!`n`n");
			if ($session['user']['race'] == "Werewolf") {
			output("You notice that the beast is one of your kin, a `\$Werewolf`2!  It smells you for a moment, before leaving with the animal carcass in its mouth.`n`n");
			output("You are ready to go on your way as well when you notice something shining in the pool of blood where the animal was previously.  You find a `%gem`2!`0");
			$session['user']['gems']++;
			// The event is over; without this, returning to the forest replays it and pays another gem.
			$session['user']['specialinc'] = "";
			break;
			}
		output("You now notice that the beast is a large, `\$Rabid Werewolf`2!  Knowing that it would outrun you if you'd try to escape, you have no choice but to fight.`n`n");
		output("The `\$Rabid Werewolf`2's bloody mouth growls at you, and its equally `\$bloody fangs`2 lengthen!  It jumps at you, ready to strike!`0");
		addnav("Fight the Werewolf",$link."op=pre");
		break;
	case "pre":
		$op = "fight";
		httpset("op",$op);

		$badguy = array(
			"creaturename"=>translate_inline("`\$Rabid Werewolf`0"),
			"creatureweapon"=>translate_inline("`\$Bloody Fangs`0"),
			"creaturelevel"=>$session['user']['level'],
			"creaturehealth"=>round($session['user']['maxhitpoints']*0.9, 0),
			"creatureattack"=>round($session['user']['attack']*1.1, 0),
			"creaturedefense"=>round($session['user']['defense']*0.85, 0),
			"diddamage"=>0,
			"type"=>"rabidwerewolf"
		);

		$session['user']['badguy'] = createstring($badguy);
		break;
	}

	if ($op == "fight"){
		$battle = true;
	}

	if ($battle){
		include("battle.php");
		if ($victory) {
			output("`n`2You have gravely injured the `\$Rabid Werewolf`2, and not wanting to risk its life against you any longer, it limps away.`n`n");
			$expgained = min(50000,round($session['user']['experience'] * 0.05, 0));
			if ($expgained < 50){
				$expgained = 50;
			}
			output("`^You have gained `7%s`^ experience from this fight!`0`n`n", $expgained);
			$session['user']['experience'] += $expgained;
			if ($session['user']['hitpoints']<1) {
				$session['user']['hitpoints']=1;
			}
			$bitechance = e_rand(1,2);
			if (is_module_active("racewerewolf")) {
				$cancan = get_module_pref("cantransform","racewerewolf");
			}
			if ((is_module_active("racewerewolf")) && $bitechance==1 && $cancan==0 ) {
				output("`2You notice a small bite mark on your left arm.  It is hardly noticeable though, and nothing to worry about..`0`n`n");
				set_module_pref("cantransform",1,"racewerewolf");
			}
			output("`2Feeling a bit vigorous for having defeated the beast, you continue your trek..`0");
			$badguy=array();
			$session['user']['badguy'] = "";
			$session['user']['specialinc'] = "";
		}elseif($defeat){
			$badguy=array();
			$session['user']['badguy'] = "";
			$session['user']['specialinc'] = "";
			output("`n`2With one last bite, the `\$Rabid Werewolf`2 tears your throat out and feasts on your insides!`n`n");
			output("`b`4You have died!`n");
			output("You lose 10% of your experience, and all of your gold on hand!`n");
			output("You may continue playing again tomorrow.`0");
			debuglog("was slain by the Rabid Warewolf and lost ".
					$session['user']['gold']." gold.");
			$session['user']['gold']=0;
			$session['user']['experience']*=0.9;
			$session['user']['alive'] = false;
			addnews("`\$%s`7 was ripped apart and eaten by a `\$Rabid Werewolf`7!`0",
					$session['user']['name']);
			addnav("Daily News","news.php");
		}else{
			require_once("lib/fightnav.php");
			if ($type == "forest"){
				fightnav(true,false);
			}else{
				fightnav(true,false,$link);
			}
		}
	}
}
?>
