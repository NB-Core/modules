<?php
// translator ready 
// addnews ready
// mail ready

function treasure_field_getmoduleinfo(){
	$info = array(
		"name"=>"Treasure Field",
		"version"=>"1.0",
		"author"=>"`LKurt Mills",
		"download"=>"",
		"category"=>"Village",
		"settings"=>array(
			"Treasure Field Module Settings, title",
			"perday"=>"Tries at digging per day, int|1",
			"cost"=>"How much to dig, int|100",
			"treasure_fieldloc"=>"Village the treasure field is in, location|Iwagakure", //Iwagakure
		),
		"prefs"=>array(
			"Treasure Field User Prefs, title",
			"tries"=>"Times dug today,int|0"
		),
	);
	return $info;
}

function treasure_field_install(){
	module_addhook("village");
	module_addhook("newday");
	module_addhook("changesetting");
	return true;
}

function treasure_field_uninstall(){
	return true;
}

function treasure_field_dohook($hookname, $args){
	global $session;
	switch ($hookname){
	case "village":
		if ($session['user']['location'] == get_module_setting("treasure_fieldloc")){
			tlschema($args['schemas']['marketnav']);
			addnav($args["marketnav"]);
			tlschema();
			addnav("Treasure Field","runmodule.php?module=treasure_field");
		}
		break;
	case "newday":
		set_module_pref("tries",0);
		break;
	case "changesetting":
		if ($args['setting'] == "villagename") {
			if ($args['old'] == get_module_setting("treasure_fieldloc")) {
				set_module_setting("treasure_fieldloc", $args['new']);
			}
		}
		break;
	}
	return $args;
}

function treasure_field_run(){
	page_header("The Treasure Field");
	require_once("lib/villagenav.php");
	global $session;
	$cost=get_module_setting("cost");
	$perday=get_module_setting("perday");
	$tries=get_module_pref("tries");
	$op=httpget('op');
	if ($op==""){
		addnav("Navigation");
		villagenav();
		addnav("Actions");
		if ($tries<$perday){
			output("`THidden in a quiet corner of the village, two suspicious guys are hanging around a patch of recently dug up earth.`n");
			output("`4\"Hey Deidara, you think this will work?\" `TOne says while hammering a sign into the ground, it states, 'Treasure Dig, %s Gold.'`n",$cost);
			output("`^\"Sure Gari, how could anyone not fall for...\" `TDeidara spies you approaching, `^\"Why hello! Care to try our fun game?\"`n");
			output("`TGari turns from his sign, and holds out a shovel in one hand, holding the other open for payment.`n`n");
			if ($session['user']['gold']<$cost){
				output("Once the two realise you don't have any money, they start yelling and chase after you!`n");
				output("You run back to the village as fast as you can, as the sound of explosions echo behind you.");
			}else{
				addnav(array("Dig for Treasure (`^%s`0 gold)",$cost),
						"runmodule.php?module=treasure_field&op=dig");
			}
		}else{
			output("`TThe two just glare at you, and you feel the need to keep walking past.");
		}
	}elseif ($op=="dig"){
		$tries++;
		set_module_pref("tries",$tries);
		$session['user']['gold']-=$cost;
		debuglog("spent $cost gold to dig.");
		addnav("Navigation");
		villagenav();
		addnav("Actions");
		output("`TGari quickly pockets your money, and hands over the shovel.");
		output("`4\"Have fun!\" `TYou notice the two backing away slightly, dirty smirks painted across their faces.");
		output("Shrugging them off, you thrust your shovel into soft earth, wondering what treasure it will yield.`n`n");
		$result=e_rand(1,10);
		switch($result){
			case 1: //??
				//gem
				output("After a few scoops, you hear a small 'ding' and something shiny surfaces. Gari and Deidara edge closer, trying to get a look, but you pocket it before they arrive.");
				output("You walk away with feigned disappointment, while clutching a shiny Gem in your pocket.`n`n");
				$session['user']['gems']+=1;
				output("`^You gain 1 gem!");
			break;
			case 2:
			case 3:
				//lots of gold
				output("You happily scoop up the dirty, and uncover a large pile of coins!`n");
				output("Gari turns to Deidara and yells, `4\"Why did you bury our stash there you idiot!!!\"`n");
				output("`^\"Me? It must have been you numbskull!!\" Deidara fires back.`n");
				output("`TYou swiftly gather up your prize and flee, just as the fireworks show starts behind you.`n`n");
				$reward=5*$cost;
				$session['user']['gold']+=$reward;
				output("`^You gain %s gold!",$reward);
			break;
			case 4:
				//Buff, Clay Bomb
				output("As you upturn the earth, you dig into something soft. You pull the shovel up, and find a strange lump of clay attached to it.`n");
				output("You twist and ask the crouching Garu and Deidara what it is, but for some reason they run screaming.`n");
				output("You are left standing there holding the shovel, a large lump of clay stuck to it.");
				apply_buff('clay_bomb',array(
					"startmsg"=>"`^You hurl the lump of clay at {badguy}.",
					"name"=>"`^Clay Bomb",
					"rounds"=>1,
					"areadamage"=>true,
					"minbadguydamage"=>5*$session['user']['level'],
					"maxbadguydamage"=>10*$session['user']['level'],
					"minioncount"=>1,
					"effectmsg"=>"`^The clay explodes causing {badguy} to suffer {damage} damage!",
					"schema"=>"module-treasure_field"
					));
			break;
			case 5:
			case 6:
				//nothing
				$junk=array('an old boot','a potato','a rock','a rusty kunai','some tree roots','an IOU');
				$rand=e_rand(0,5);
				output("You dig away, and eventually find %s.`n",$junk[$rand]);
				output("Before you can complain and demand your money back, the two swindlers are long gone.");
			break;
			case 7:
				//lose turn
				output("You dig, and you dig, and you just keep digging.`n");
				output("Eventually you realise there is nothing here, despite the fact you have dug down past your shoulders.`n");
				if ($session['user']['turns']>0){
					$session['user']['turns']--;
					output("You are so tired from wasting your time, you lose a turn.`n");
				} 
				output("Exhausted you treck back to the village.");
			break;
			case 8:
				//lose charm Manure
				output("You notice the soil is rather pungent as you dig.`n");
				output("You hit some clay, then suddenly there is a deafing *BOOM* and you are covered head to toe in Manure.`n");
				output("Caked in the foul smelling dirt, you slink away, trying to avoid anyone noticing you in this state.`n`n");
				$session['user']['charm']--;
				output("`^You feel less charming.");
			break;
			case 9:
				//Lose HP
				output("You dig down a bit, then earth is rent by explosive chakra, which engulfs you.`n");
				output("You wake up some distance from the hole, your body in searing pain.`n`n");
				$hploss=round($session['user']['hitpoints']/4);
				$session['user']['hitpoints']-=$hploss;
				output("`^You lose `\$%s`^ hitpoints.",$hploss);
			break;
			case 10:
				//Debuff, burns
				output("You start to dig, when *BOOM* the whole area is engulfed in a large explosion.`n");
				output("The heat and flames burn your skin, making it painful sensitive.`n`n");
				apply_buff('clay_burn',array(
					"startmsg"=>"`^Your burnt skin causes searing pain!",
					"name"=>"`^Burnt Skin",
					"rounds"=>10,
					"roundmsg"=>"Your burns make any new wounds feel worse!",
					"badguyatkmod"=>1.2,
					"minioncount"=>1,
					"schema"=>"module-treasure_field"
				));
			break;
		}
	}
	page_footer();
}
?>
