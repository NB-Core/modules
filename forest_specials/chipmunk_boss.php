<?php

function chipmunk_boss_getmoduleinfo() {
	$info = array(
		"name"=>"Chipmunk Boos",
		"author"=>"`LShinobiIceSlayer`~, based on work by, `2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Forest Specials",
		"download"=>"",
		"requires"=>array(
			"chipmunks"=>"1.0|Oliver Brendel",
		),
		"settings"=>array(
			"Chipmunk Boss - Preferences, title",
			"exp-lose"=>"Multipler of user's experience at loss,floatrange,0,1,.05|.1",
			"village-disp"=>"Display a list players with the largest amount stolen?,bool|1",
			"list-num"=>"How many players to display in the village if list is active?,int|3",
			),
	);
	return $info;
}

function chipmunk_boss_install() {
	module_addhook_priority("village-desc",75);
	module_addhook_priority("index",100);
	module_addeventhook("forest", "return 100;");
	return true;
}

function chipmunk_boss_uninstall() {
	return true;
}

function chipmunk_boss_dohook($hookname,$args) {
	global $session;
	switch ($hookname){
		case "village-desc":
		case "index":
			if(get_module_setting("village-disp") || $hookname=="index"){
				$size=get_module_setting("list-num");
				$sql = "SELECT a.name, m.value, a.acctid FROM " . db_prefix("accounts") . " AS a, " . db_prefix("module_userprefs") . " AS m WHERE m.modulename = 'chipmunks' AND m.setting = 'goldstolen' AND m.value > 0 AND m.userid = a.acctid ORDER by m.value+0 DESC LIMIT ".$size;
				$result=db_query($sql);
				if ($hookname!='index') output_notl("`c");
				output("`n`qThe following have lost the most gold to the dreaded `gC`lhipmunk`q army!`n");
				$i=0;
				while($row = db_fetch_assoc($result)) {
					$rows[]=$row;
					$rounding=-(strlen($row['value'])-2); //round two spaces after the smallest number
				}
				if ($rounding>0) $rounding=0; //do not round if there are too small amounts
				foreach ($rows as $row) {
					$i++;
					$amount=round($row['value'],$rounding);
					output("`q%s. %s`q (over`\$ %s`q gold) `n",$i,$row['name'],number_format($amount));
				}
				if ($hookname!='index') output_notl("`c");
					else output_notl("`n");
			}
			break;
	}
	return $args;
}

function chipmunk_boss_runevent($type,$link) {
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:chipmunk_boss";
	$op = httpget('op');
	
	$badguy = array(
		"creaturename"=>translate_inline("Giant Chipmunk Boss!"),
		"creaturelevel"=>$session['user']['level']+2,
		"creatureweapon"=>translate_inline("Sharp teeth and Claws!"),
		"creatureattack"=>$session['user']['level']+$session['user']['dragonkills']+10,
		"creaturedefense"=>$session['user']['level']+$session['user']['dragonkills']+15,
		"creaturehealth"=>max($session['user']['level']*100,$session['user']['dragonkills']*$session['user']['level']),
		"creatureaiscript"=>"require('modules/chipmunk_boss/AI.php');",
		"diddamage"=>0,
		);
	
	switch ($op) {
	case "":
		output("`qYou casually stroll through the forest, taking in the beautiful sights, the trees, flowers, a cute little `gC`lhipmunk`q, a pony.. WAIT! A `gC`lHIPMUNK`q!");
		output("You quickly dive divehind a tree, clutching your gold tightly, knowing of their reputation for stealing the hard earned gold of many a poor shinobi.");
		output("As you peek out from your hiding space, you let out a deep sigh of relief, as the `gC`lhipmunk`q seems not to have noticed you. You do however notice it carrying the meek treasure of some poor shinobi.");
		output("\"All that gold must go somewhere,\" you think. Maybe if you follow the wee creature, you can find their hoard and keep it for yourself, cause really, how hard could it be to take some gold from some tiny little `gC`lhipmunks`q?");
		addnav("Options");
		addnav("Continue",$link."op=follow");
		addnav("Sneak away",$link."op=leave");
		break;
	case "leave":
		output("`qYou decide not to risk it, so you carefully slip away through some bushes.`n`n");
		$caught=e_rand(1,3);
		if($caught=2&&$session['user']['gold']>0){
			$gold=number_format(min(e_rand(94,104),e_rand(1,$session['user']['gold'])),0);			
			output("`qYou suddenly feel a pull and before you realize another `gC`lhipmunk`q rallies out of your pocket with `^%s gold`q in its paws!`0`n`n", $gold);
			$session['user']['gold']-=$gold;
			increment_module_pref("goldstolen",$gold,"chipmunks");
		}
		$session['user']['specialinc'] = "";
	break;
	case "follow": 
		//Find their hoard... and their boss!
		output("`qYou follow the little `gC`lhipmunk`q, until your mouth drops at the huge pile of gold in a small clearing, however the gold isn't what caught your eye, but the giant `gC`lhipmunk`q standing upon it, staring you dead in the eye.");
		
	 	$session['user']['badguy'] = createstring($badguy);
		$op = "fight";
		httpset("op",$op);
		break;
	}
	if ($op == "fight"){
		$battle = true;
	}
	if (isset($battle) && $battle){
		include("battle.php");
			if ($victory){
				//You win! Steal some of the riches before they come after you again.
				output("`n`qAs you stare at the giant beast sprawled out in front of you, you suddenly notice the gold once more, and dive in to take your reward!.");
				addnews("`@%s `qhas slain the `gC`lhipmunk`q Boss, and earned a great reward.`0",$session['user']['name']);
				
				//Get some random players Gold.
				$sql = "SELECT a.name, m.value, a.acctid FROM " . db_prefix("accounts") . " AS a, " . db_prefix("module_userprefs") . " AS m WHERE m.modulename = 'chipmunks' AND m.setting = 'goldstolen' AND m.value > 0 AND m.userid = a.acctid ORDER by rand(".e_rand().") LIMIT 1";
				$result=db_query($sql);
				$row = db_fetch_assoc($result);
				$person = $row['name'];
				$gold = ceil($row['value']/100); // not the entire value, there is a lot of gold in there...
				$acctid = $row['acctid'];
				
				$session['user']['gold']+=$gold;
//				set_module_pref("goldstolen",$row['value']-$gold,"chipmunks",$acctid);				
				
				$gems=e_rand(-2,4);
				if ($gems>0){
					$session['user']['gems']+=$gems;
				}
				debuglog("attained $gold gold and $gems gems for slaying the Chipmunk Boss!");
				output("`n`n`^As you sort through your spoils, you find a Ninja Info card of `@%s `^suggesting these were once their belongings.",$person);
				output("`n`n`^Spoils:`n");				
				output("`^Gold: `@%s`n",$gold);
				if ($gems>0){
					output("`^Gems: `@%s`n",$gems);
				}
				output("`n`qAs you attempt to scoop up some more of the vast treasures, a deep rumbling from your fallen foe makes you run away screaming.");
			}elseif($defeat){
				$exp = round($session['user']['experience']*get_module_setting("exp-loss"));
				$gold=$session['user']['gold'];
				increment_module_pref('goldstolen',$gold,'chipmunks');
				$session['user']['alive']=false;
				$session['user']['gold']=0;
				$session['user']['hitpoints']=0;
				$session['user']['experience']-=$exp;
				debuglog("lost $exp experience to the Chipmunk Boss");
				//Chipmunk steal the gold from your dead body
				output("`n`n`qThe `gC`lhipmunk`q Boss orders his `gC`lhipmunks`q to strip your lifeless body of its belongs, and add them to their stash.");
				//News story about your mauled body
				addnews("%s `Qhas been mauled to death by gold hungry `gC`lhipmunks`q.",$session['user']['name']);
				addnav("Return to the Shades","shades.php");
				blocknav("forest.php");
				$session['user']['specialinc'] = "";
				$badguy=array();
				$session['user']['badguy']="";
			}else{
				$script = "runmodule.php?module=chipmunk_boss&op=fight";
				require_once("lib/fightnav.php");
				$allow = true;
				fightnav($allow,false);
				//blocknav("forest.php");
			}
		}

}

function chipmunk_boss_run(){
}

?>
