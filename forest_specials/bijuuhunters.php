<?php

function bijuuhunters_getmoduleinfo(){
	$info = array(
		"name"=>"Bijuu Hunters",
		"version"=>"1.0",
		"author"=>"`JShinobiIceSlayer",
		"category"=>"Forest Specials",
		"download"=>"",
		"settings"=>array(
			"Bijuu Hunters Settings,title",
			"minbounty"=>"The minimum bounty needed for the Hunters to attack,int|500",
			"seallength"=>"How many days does the Bijuu get sealed for?,int|10",
			"erofavours"=>"How many favours you need with Ero-Sennin to let him remove the seal,int|10",
		),
		"prefs"=>array(
			"Bijuu Hunters User Preferences,title",
			"sealeddays"=>"How long does the user have left for his bijuu sealing,int|0",
		),
		"prefs-mounts"=>array(
			"Bijuu Hunters Mount Preferences,title",
			"bijuu"=>"Is this mount a Bijuu?,bool|0",
		),
	);
	return $info;
}

function bijuu_check() {
	global $playermount;
	if (isset($playermount) && is_array($playermount) && array_key_exists("mountid",$playermount)){
		$id = $playermount['mountid'];
	}else{
		$id = 0;
	}
	$bijuu = get_module_objpref("mounts", $id, "bijuu", "bijuuhunters");
	return $bijuu;
}

function bounty_check($id) {
	$sql = "SELECT sum(amount) AS total FROM " . db_prefix("bounty") . " WHERE status=0 AND target=$id";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);
	$bounty = $row['total'];
	if ($bounty >= get_module_setting("minbounty")){
		return true;
	}else{
		return false;
	}
}

function bijuuhunters_install(){
	module_addeventhook("forest", "return 100;");	
	module_addhook("mountfeatures");
	module_addhook("soldmount");
	module_addhook("footer-stables");
	module_addhook("newday");
	module_addhook("erosennin_favours");
	return true;
}

function bijuuhunters_uninstall(){
	return true;
}

function bijuuhunters_dohook($hookname,$args){
	global $session;
	switch($hookname) {
	case "mountfeatures":
		$bijuu = get_module_objpref("mounts", $args['id'], "bijuu");
		$args['features']['Bijuu']=$bijuu;
		break;
	case "soldmount":
		set_module_pref('sealeddays',0);
		break;
	case "footer-stables":
		if (get_module_pref("sealeddays") > 0 && bijuu_check($session['user']['acctid'])) blocknav("stables.php?op=feed", true);			
		break;
	case "newday":
		global $playermount;
		$days = get_module_pref("sealeddays");
		if ($days > 0 && bijuu_check($session['user']['acctid'])){
			strip_buff('mount');
			output("`n`&Your %s has been sealed for %s days, blocking it's power, though the seal appears to be fading.`n",$playermount['mountname'], $days);
			$turns=(int)$playermount['mountforestfights'];
			$session['user']['turns']-=$turns;
			output("`nHaving your mount sealed, means you lose the extra %s turns you gained.`n",$turns);
			if ($days==1) output("`nThe seal appears to have almost completely faded, meaning that %s's power should soon return.`n",$playermount['mountname']);
			$days--;
			set_module_pref('sealeddays',$days);
		}		
		break;
	case "erosennin_favours":
		$favour = $args['favour'];
		if ($favour > get_module_setting("erofavours")) addnav("Remove Seal","runmodule.php?module=bijuuhunters&op=ero&favour=$favour");
		break;
	}
	return $args;
}

function bijuuhunters_runevent($type,$link){
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:bijuuhunters";
	
	$op = httpget('op');
	if (get_module_pref("sealeddays")>0) $op = 'other';
	switch ($op){
	case '':
		output("`&You dash through the forest, only once, not twice, neither did you turn around, retrace your steps. You rather firmly, almost briskly, on an almost straight path, went right through it, but are suddenly aware of two people yelling nearby. You stop and see what the problem is.");
		output("You stare at the two men, both in long black cloaks with small `\$red `&clouds dotted on them.");
		output("`n`q\"I say we stop for a bit, I've seen a few bounties around here, we could earn a bit extra.\" `&One of them exclaims, his head and face mostly covered, but his green eyes piercing into the other.");
		output("`n`4\"Who cares, can't we just do our mission and get out of here for once Damn it Kakuzu? One time?\" `&The other with a large triple bladed scythe on his back replies.");
		output("`nAt that point they both rapidly look straight at you, looking down you see you stood on a stick. They both approach you slowly, smiling between themselves.");
		output("`n`4\"So who do you think is right kid?\" `&The one with the scythe askes. You struggle to think of what to do next");
		addnav("State your opinion",$link."op=talk");
		addnav("Attack them",$link."op=challenge");
		addnav("Run",$link."op=run");
		break;
	case 'talk':
		if (bijuu_check()){
			output("`&As you start to sweat a little, you manage to state that they should go get some extra money on bounties, and the one with the Scythe on his back turns away and starts cursing loudly.");
			output("`n`q\"Is that because you know we're after you... Jinchuriki?\"");
			output("`&Wondering how he could tell you held a tailed beast you start to run, but before you know it, the one with the Scythe already has it out and his coming straight for you.");
			addnav("Defend yourself",$link."op=challenge");
		}elseif(bounty_check($session['user']['acctid'])){
			output("`&You agree with the one carrying the Scythe, saying that they should stick to there duty, and he seems rather pleased so he starts gloating to other about his 'victory' over him.");
			output("However the other just seems to be staring at a piece of paper, then he turns it around, showing a wanted poster with your face on it.");
			output("Your first thought is to run, but as you try to you are already surround by black threads.");
			addnav("Defend yourself",$link."op=challenge");
		}else{
			output("`&You start to share your views on their problem, but they start arguing again, then before long they are verbally and physical attacking each other.");
			output("Wanting to stay out of their way, you quietly slip away, praying they have forgotten about you.");
			addnav("Slip away",$link."op=run");
		}
		break;
	case 'run':
		if (bijuu_check()){
			output("`&You start to run when the two of them appear in front of you at great speed.");
			output("`q\"Going somewhere with that Bijuu?\"`&The green eyed one asks, as the other rushes in with his Scythe.");
			addnav("Defend yourself",$link."op=challenge");
		}elseif(bounty_check($session['user']['acctid'])){
			output("`&You just ignore them, and start to walk off when the green eyed one asks, `n`q\"Isn't this you?\"");
			output("`n`&He points to a wanted poster with your picture on it. Before you can react, black threads start winding around you.");
			addnav("Defend yourself",$link."op=challenge");
		}else{
			output("`&You quickly leave their sounds behind you, and let out a better sigh before returning to your normal activities.");
			$session['user']['specialinc'] = "";
		}
		break;	
	case 'other':
		output("`&You walk through the forest when you hear a strange noice from the bushes, you walk closer only to find... `na couple of old men playing chess.");
		$session['user']['specialinc'] = "";
		break;
	case 'challenge':
		require_once("lib/battle-skills.php");
		$badguylevel = $session['user']['level']+1;
		$badguyhp = round($session['user']['maxhitpoints']*1.5);
		$badguyatt = $session['user']['attack']*1.5;
		$badguydef = $session['user']['defense']*1.5;
		if ($session['user']['level'] > 9) {
			$badguyhp *= 1.05;
			$badguylevel++;
		}
		if ($session['user']['level'] < 4) {
			$badguyhp *= .9;
			$badguyatt *= .9;
			$badguydef *= .9;
			$badguylevel--;
		}
		$kakuzu = array(
			"creaturename"=>"`QKakuzu`0",
			"creaturelevel"=>$badguylevel,
			"creatureweapon"=>"Black Threads",
			"creatureattack"=>$badguyatt,
			"creaturedefense"=>$badguydef,
			"creaturehealth"=>round($badguyhp),
			//"creatureaiscript"=> <'kakuzuai.php'>,
			"diddamage"=>0,
			);
		$hidan = array(
			"creaturename"=>"`\$Hidan`0",
			"creaturelevel"=>$badguylevel,
			"creatureweapon"=>"Triple Bladed Scythe",
			"creatureattack"=>$badguyatt,
			"creaturedefense"=>$badguydef,
			"creaturehealth"=>INT_MAX,
			//"creatureaiscript"=> <'hidanai'>,
			"diddamage"=>0,
			"hidehitpoints"=>true, 
			"fleesifalone"=>"{badguy} no longer has Kakuzu to sew him together anymore, and he falls apart.",
			"noadjust"=>1, 
			);
		$stack = array();
		$stack[] = $kakuzu;
		$stack[] = $hidan;
		$attackstack = array(
			'enemies'=>$stack,
			'options'=>array('type'=>'bijuuhunters')
		);
		$session['user']['badguy']=createstring($attackstack);
		$op="combat";
		httpset('op', $op);
	case "combat": case "fight":
		include("battle.php");
		if ($victory){ 
			output("`&You stand over the bodies of the two, feeling much stronger than before you started.");
			addnews("`%%s`2 has beaten a couple of bounty hunters.`n",$session['user']['name']);
			$session['user']['specialinc'] = "";
			$expgain = min($session['user']['dragonkills']*1000,round($session['user']['experience']/10));		
			$session['user']['experience']+=$expgain;
			output("`n`nYou gain %s experience points.",$expgain);
			$badguy=array();
			$session['user']['badguy']="";
			$session['user']['specialinc'] = "";
		}elseif ($defeat){
			if (bijuu_check()){
				output("`&The last thing you remember is the two bending down over you, then a quick blur.");
				output("`nNext thing you know, you wake up alone in the forest, with a strange seal on your stomach. You just up and realise that your Bijuu's power appears to have been sealed.");
				strip_buff('mount');
				set_module_pref("sealeddays",get_module_setting("seallength"));
				$session['user']['hitpoints']++;
				$badguy=array();
				$session['user']['badguy']="";
				$session['user']['specialinc'] = "";
			}elseif(bounty_check($session['user']['acctid'])){
				output("`&You fall at the hands of Kakuzu and Hidan. They then take your body and collect the bounty on your head, before moving on.");
				addnews("`%%s`6 had the bounty on there head collected by a couple of bounty hunters.`n",$session['user']['name']);
				$sql = "SELECT bountyid,amount,setter FROM " . db_prefix("bounty") . " WHERE status=0 AND setdate<='".date("Y-m-d H:i:s")."' AND target=".$session['user']['acctid'];
				$result = db_query($sql);
				$amt = 0;
				$totgoodamt = 0;
				for($i=0;$i<db_num_rows($result);$i++){
					$row = db_fetch_assoc($result);
					$totgoodamt += $row['amount'];
					$windate = date("Y-m-d H:i:s");
					$sql = "UPDATE " . db_prefix("bounty") . " SET status=1,winner=0,windate=\"$windate\" WHERE bountyid=".$row['bountyid'];
					debug("Updating the bounties table with $sql.");
					db_query($sql);
				}
				$badguy=array();
				$session['user']['badguy']="";
				$session['user']['specialinc'] = "";
				addnav("Return");
				addnav("Return to the Shades","shades.php");
			}else{
				output("`&`\$Hidan`& and `QKakuzu `&process to 'play' with your body until they both get bored.");
				addnews("`%%s`6 had what was left of them found by some poor person in the forest.`n",$session['user']['name']);
				$badguy=array();
				$session['user']['badguy']="";
				$session['user']['specialinc'] = "";
				addnav("Return");
				addnav("Return to the Shades","shades.php");
			}
		}else{
			require_once("lib/fightnav.php");
			$allow = true;
			fightnav($allow,false);
		}
	}	
}

function bijuuhunters_run(){
	global $session;
	$op = httpget('op');
	$favour = httpget('favour');
	
	page_header("Ero-Sennin");
	
	if ($op == "ero") {
		output("`@The strange old man forms some handseals then touches your stomach, the seal instantly disappearing.");
		output("`n`2\"That should do it, this time tomorrow you'll be fighting fit, and your buddy in there will be back\"`@ He proudly declares before returning to his peek hole.");
		set_module_pref("favour",$favour-get_module_setting("erofavours"),"erosennin");
		set_module_pref("sealeddays",0);
		addnav("Back to Forest","forest.php");
		$session['user']['specialinc'] = "";
	}
	
	page_footer();
}

?>
