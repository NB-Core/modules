<?php

require_once("common.php");
require_once("lib/http.php");
//require_once("modules/inventory/lib/itemhandler.php");

function wedgieman_getmoduleinfo(){
 $info = array(
  "name"=>"The Wedgie Man!",
  "author"=>"`LShinobiIceSlayer",
  "version"=>"1.0",
  "category"=>"Forest Specials",
  "download"=>"",
  "settings"=>array(
   "Wedgie Man - Preferences, title",
   "A Strange man the stalks you in the forest.,note",
   "name"=>"The name(Coloured) of the Wedgie Man,text|`QW`qedgie `kM`lan",
   "dexterity"=>"The level Dexterity needed to avoid Punishment,int|35",
   "constitution"=>"The level Constitution needed to avoid Punishment,int|35",
   "intelligence"=>"The level Intelligence needed to avoid Punishment,int|20",
	"strength"=>"The difficulty of a strenght test to avoid punishment,int|40",
  ),
  );
 return $info;
}
function wedgieman_install(){

 module_addeventhook("forest", "return 100;");
 return true;
}
function wedgieman_uninstall(){

 return true;
}
function wedgieman_dohook($hookname,$args){

return $args;
}

function wedgieman_runevent($type,$link){

	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:wedgieman";
	$op=httpget('op');
	$name=get_module_setting("name");
	$dexterity=get_module_setting("dexterity");
	$constitution=get_module_setting("constitution");
	$intelligence=get_module_setting("intelligence");
	$strength = get_module_setting("strength");
	$myuser = new myuser();
	$user = &$session['user'];
	require_once("modules/inventory/lib/itemhandler.php");

	$bonus = (3-$user['turns']); //hehe, many turns, many kicks
	
	switch ($op) {
		case "":
			output("`&You walk through the forest and come to an empty clearing.`n`nYou hear noices coming from behind a tree.");
			addnav("Options");
			addnav("Check out the Noise",$link."op=wedgie");
			addnav("Continue on",$link."op=leave");
		break;
		case "leave":
			output("`&You leave this place, not sure you want to know what is on the other side of that tree.");
			$session['user']['specialinc'] = "";
		break;
		case "wedgie":
			output("`&You wander around the tree and find a big, bully like man. You stumble back gasping, it's the legendary %s`&!",$name);
			if ($user['sex']==SEX_MALE){//Spilts event results by gender
				$randomchance=e_rand(1,4);
				switch($randomchance) {
					case "1":
						output("`n`n%s `&comes running at you! `q\"It's Wedgie time!\"`&",$name);
						$dextest = $myuser->attributeTest("dexterity",$dexterity,$bonus);
						if ($dextest) {//Fast so you can beat him to the punch
							output("`n`n%s `&tries to wedgie you, but due to your awesome speed you race around and wedgie him first!",$name);
							$expgain=$user['experience']/10;
							$user['experience']+=$expgain;
							output("`n`nYou feel strong from defeating the %s `&and you gain %s experience points!",$name,$expgain);
						} else {//Oh dear....
							output("`n`nYou scream as %s `&comes from behind you, grabs you by your underwear and yanks it  up.",$name);
							$hploss=round($user['hitpoints']/3);
							$hploss=min($user['hitpoints']-1,$hploss); //non lethal
							$user['hitpoints']-=$hploss;
							output("`n`nYou slowly walk away, trying to re-adjust yourself. You lose %s hitpoints",$hploss);
						}
					break;
					case "2":
						output("`n`nThe %s `&creeps up on you, sucking on his finger as he inches closer.",$name);
						output("`n`nThe %s `&shoves his finger in your ear. `q\"Wet Willy!\" `&He yells.",$name);
						$contest = $myuser->attributeTest("constitution",$constitution,$bonus);
						if ($contest) {//Your tough, you can deal with a little spit 
							output("`n`nYou stand still and grin as he has his way with you, then leaves. Secretly you feel proud inside as you stuck a Kick Me sign on his back while he was preoccupied.");
							apply_buff('wetwilly',
							array(
								"name"=>"`qProudness",
								"rounds"=>20,
								"wearoff"=>"`qYou lose that proudness.",
								"atkmod"=>1.1,
								"defmod"=>1.1,
								"roundmsg"=>"`qYou feel Proud!",
								"schema"=>"module-wedgieman",
								));
						}else{
							output("`n`nYou scream, and try to fight off the %s `&but it is useless, his strength is too much for you, and you fall victim to his Wet Willy attack.",$name);
							output("`n`nHe finally lets you go and you head off, feeling dirty.");
							apply_buff('wetwilly',
							array(//Not so lucky
								"name"=>"`qWet Ear",
								"rounds"=>30,
								"wearoff"=>"`qYou finally get of the spit out.",
								"atkmod"=>0.8,
								"roundmsg"=>"`qYou feel gross, and try to clean spit out of your ear.",
								"schema"=>"module-wedgieman",
								));
						}
					break;
					case "3":
						output("`n`nThe %s `&walks up to you, he seems to be afraid of you. `q\"Hey, maybe you'd like to share some... things... I have obtained in my travels?\"`&",$name);
						$inttest = $myuser->attributeTest("intelligence",$intelligence,$bonus);
						if ($inttest) {//You out smart him, go nerds!
							output("`n`n%s `&rolls out a mat, and displays all his goods, after much debate, and some smart thinking on your part, you finally walk away with a nice new item.",$name);
							$randomitem=e_rand(1,4);
							switch ($randomitem) {
								case "1":
									$item="`2Scroll of Wind";
								break;
								case "2":
									$item="Health Elixir 4";
								break;
								case "3":
									$item="Specialty Elixir";
								break;
								case "4":
									$item="Talisman of Attack";
								break;
							}
							add_item_by_name($item,1);
							output("`n`nYou walk away with one %s`&.",$item);
						}else{//Brains beats strength
							output("`n`n%s`& shows you his goods, and asks if you would like to do some dealing with him. After a big argument over tiny things you finally walk away, though something doesn't quite feel right.",$name);
							$goldloss=round($user['gold']/e_rand(2,4),0);
							$goldloss=min($user['gold'],$goldloss); //cannot lose more than he has
							$user['gold']-=$goldloss;
							output("`n`nFor some reason you realize you lost %s gold then when you first saw the %s`&.",$goldloss,$name);
						}
					break;
					case "4":
						output("You go towards the %s`&, but he gets spooked and runs away.",$name);
						$strtest = $myuser->attributeTest("strength",$strength,$bonus);
						if ($strtest) {//Again, strength isn't everthing
							output("`n`nYou chase after the %s`&, thinking you'll be praised for finally putting an end to his evil reign over the forest.",$name);
							$user['turns']--;
							output("`n`nYou lose a turn after a wild goose chase through the forest.");
						}else{//Better to stop and sniff the roses
							output("`n`nYou watch the %s `&flee before you. You turn to the base of the tree and see a small sack lying there.",$name);
							$goldgain=$user['level']*30;
							$gemsgain=e_rand(1,2);
							$user['gold']+=$goldgain;
							$user['gems']+=$gemsgain;
							output("`n`nYou find %s gold, and %s gems in the sack.",$goldgain,$gemsgain);
						}
					break;
				}
			}else{
				$randomchance=e_rand(1,4);
				switch($randomchance) {
					case "1":
						output("`n`n%s sees you and he gets a glazed look in his eyes. `q\"I think your pretty...\" `&he tells you.",$name);
						$dextest = $myuser->attributeTest("dexterity",$dexterity,$bonus);
						if ($dextest) {//Once again, being fast is good
							output("`n`n%s runs after you, declearing his love for you! Due to your speed, you run as fast as you can, and feel you could keep running forever",$name);
							$user['turns']++;
							output("`n`nYou gain one forest fight",$name);
						} else {//Wouldn't be to bad having around right?
							output("`n`nYou scream as %s `&Comes from behind you, and tries to hug you. You try to run but you can't seem to get away.",$name);
							apply_buff('wedgie',
							array(
								"name"=>"`qWedgie Man",
								"rounds"=>30,
								"wearoff"=>"`qYou finally get away from him",
								"atkmod"=>0.8,
								"roundmsg"=>"`qYou try to get rid of the Wedgie Man.",
								"schema"=>"module-wedgieman",
								));
							output("`n`n%s keeps following you.",$name);
						}
					break;
					case "2":
						output("`n`nThe %s `&run up to you and puts you in a head lock, `q\"It's Noogie time!\"`&",$name);
						output("`n`n%s `&rapidly rubs his fist across your head.",$name);
						$contest = $myuser->attributeTest("constitution",$constitution,$bonus);
						if ($contest) {//Again, you can handle a little pain
							output("`n`nYou stand there and put up with the noogie, sneaking your hand into his pockets while he's busy.");
							$randomitem=e_rand(1,4);
							switch ($randomitem) {
								case "1":
									$item="`7Scroll of Demons";
								break;
								case "2":
									$item="Health Elixir 6";
								break;
								case "3":
									$item="Specialty Elixir";
								break;
								case "4":
									$item="Talisman of Defense";
								break;
							}
							add_item_by_name($item,1);
							output("`n`nYou walk away with one %s`&.",$item);
						}else{//Not the hair!
							output("`n`nYou scream, and try to fight off the %s `&but it is useless, his strength is too much for you.",$name);
							//output("`n`n%s `&rolls out a mat, and displays all his goods, after much debate, and some smart thinking on your part, you finally walk away with a nice new item.",$name);
							$charmloss=e_rand(1,5);
							if ($user['charm']>$charmloss)
								$user['charm']-=$charmloss;
							output("`n`nYou try and fix your hair, but it is to late and someone sees you, you lose %s Charm.",$charmloss);
						}
					break;
					case "3":
						output("`n`n%s backs away slowly and you think you must have frightened him somehow. Just then he pulls on a chakra string and a bucket of bugs hidden in the tree falls all over you.",$name);
						$inttest = $myuser->attributeTest("intelligence",$intelligence,$bonus);
						if ($inttest) {//Yummy!
							output("`n`nYou look at the bugs now covering your body, and remember them from books you have read.");
							$user['hitpoints']+=5;
							output("`n`nYou eat some of the bugs and gain 5 hitpoints (this might exceed your maximum amount for the time being.");
						}else{
							output("`n`nYou around screaming, and frantically try to fling them off. You get dirty in the process.");
						}
					break;
					case "4":
						output("You goes towards the %s`&, but he gets spooked and runs away.",$name);
						$strtest = $myuser->attributeTest("strength",$strength,$bonus);
						if ($strtest) {//same as above
							output("`n`nYou chase after the %s`&, thinking you'll be praised for finally putting an end to his evil reign over the forest.",$name);
							$user['turns']--;
							output("`n`nYou lose a turn after a wild goose chase through the forest.");
						}else{
							output("`n`nYou watch the %s `&flee before you. You turn to the base of the tree and see a small sack lying there.",$name);
							$goldgain=e_rand(20,$user['level']*30);
							$gemsgain=e_rand(1,2);
							$user['gold']+=$goldgain;
							$user['gems']+=$gemsgain;
							output("`n`nYou find %s gold, and %s gems in the sack.",$goldgain,$gemsgain);
						}
					break;
				}
			}
			$session['user']['specialinc'] = "";
		break;	
	}
}
class myuser {

	private $user;
	
	public function __construct() {//had to move this up here to make it run.
		global $session;
		$this->user=$session['user'];
	}
	
	public function attributeSkillCheck($attributename,$difficulty=10, $bonus=0) {
		
		if (!in_array($attributename,$this->user)) return false; //bad input
		$base = $this->user[$attributename] - 10 + $bonus; //errors appear here, and above line //missing ' ...could have been seen in any editor that supports syntax highlighting =) I wrote that at work in my webmailer - without anything except my head ^^ so I think it's forgivable
		$roll = e_rand(1,20);
		$result = $base + $roll - $difficulty;
		return $result;	
	}

	public function attributeTest($attributename,$difficulty,$bonus) {
		
		//short version for yes/no
		$result=$this->attributeSkillCheck($attributename,$difficulty,$bonus);
		if ($result>0){
			return true;
		}else{
			return false;
		}
	}
}
function wedgieman_run(){
}

?>		
