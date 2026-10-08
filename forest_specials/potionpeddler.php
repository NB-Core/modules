<?php
/*
Sells Potions in the Woods

*/

function potionpeddler_getmoduleinfo() {
	$info = array(
		"name"=>"Potion Peddler",
		"author"=>"`2Oliver Brendel, text by `4Gyururu",
		"version"=>"1.0",
		"category"=>"Forest Specials",
		"download"=>"",
		"settings"=>array(
			"The Potion Peddler - Preferences, title",
			"available_glowing"=>"Is the glowing potion here today?,bool|1",
			"available_rainbow"=>"Is the rainbox potion here today?,bool|1",
		),

	);
 return $info;
}
function potionpeddler_install() {
	module_addeventhook("forest", "return 100;");
	module_addhook("newday-runonce");
	return true;
}
function potionpeddler_uninstall() {
 return true;
}

function potionpeddler_dohook($hookname,$args) {
	switch($hookname) {
		case "newday-runonce":
			set_module_setting("available_glowing",(e_rand(0,9)<=2?1:0),"potionpeddler");
			set_module_setting("available_rainbow",(e_rand(0,9)<=2?1:0),"potionpeddler");
			break;
	}
return $args;
}

function potionpeddler_runevent($type,$link) {
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:potionpeddler";
	$op = httpget('op');
	$perm=potionpeddler_calc();
	// The same conditions as the "Take this potion" links: enough gems, and the glowing and
	// rainbow potions only while they are on offer. Otherwise he just shows his coat again.
	$prices = array("getgreen"=>1, "getred"=>3, "getblack"=>5, "getglowing"=>10, "getrainbow"=>3);
	if (isset($prices[$op]) && ($session['user']['gems'] < $prices[$op]
			|| ($op=="getglowing" && !get_module_setting('available_glowing'))
			|| ($op=="getrainbow" && ($perm<=30 || !get_module_setting('available_rainbow'))))) {
		$op = "investigate";
	}
	output_notl("`n");
	switch ($op) {
		case "":
			output("`2As you were walking along the forest path, a man wearing a large coat and a pair of round sunglasses suddenly blocks your path and grins at you.");
			addnav("See what he wants from you",$from."op=investigate");
			addnav("Walk away",$from."op=walkaway");
			break;
		case "investigate":
			output("`^\"Hello there, may I interest you in buying some potions?\"`n`2He opens his coat and you see potions of all colors neatly arranged in rows on the inside of his coat.`n`nDo you want to take a closer look?");
			addnav("`^Ask about the `@green `^potion.",$from."op=askgreen");
			addnav("`^Ask about the `\$red `^potion.",$from."op=askred");
			addnav("`^Ask about the `~`bblack`b `^potion.",$from."op=askblack");
			if (get_module_setting('available_glowing')) addnav("`^Ask about the `v`bglowing`b `^potion.",$from."op=askglowing");
			if ($perm>30 && get_module_setting('available_rainbow')) addnav("`^Ask about the `\$r`Qa`^i`@n`!b`%o`5w `^potion.",$from."op=askrainbow");
			addnav("Back off",$from."op=walkaway");
			break;
		case "askgreen":
			output("`2\"`^Wise choice there sir. Drinking this potion will surely make you healthier. It's going to cost you `7%s `^gem.`2\"",1);
			if ($session['user']['gems']>0) addnav("`2Take this potion",$from."op=getgreen");
				else output("`n`n`4Unfortunately you do not have enough funds...");
			addnav("Back to the peddler",$from."op=investigate");
			break;
		case "getgreen":
			output("`2You hand `7%s `2gem over to him and gulp down the strange `@greenish `2potion.`n`n",1);
			$session['user']['gems']-=1;
			if (potionpeddler_chance(50)) {
				output("`2Your body feels `@healthy `2after drinking the potion.`n`n");
				potionpeddler_hp(1);
			} else {
				output("`2Your body feels `t`bstrong`b `2after drinking the potion.`n`n");
				potionpeddler_hp(1);
				$session['user']['hitpoints']+=20;
			}
			addnav("Leave",$from."op=leave");
			break;
		case "askred":
			output("`2\"`^This is a strong potion there sir. The potion might have some negative effect on the body. I'll let you have it for `7%s `^gems.`2\"",3);
			if ($session['user']['gems']>=3) addnav("`2Take this potion",$from."op=getred");
				else output("`n`n`4Unfortunately you do not have enough funds...");
			addnav("Back to the peddler",$from."op=investigate");
			break;
		case "getred":
			if ($perm<4) {
				output("`2\"`^I'm very sorry but it doesn't seem that you body will be able to handle the side effects of this potion.`2\"");
				addnav("Back to the peddler",$from."op=investigate");
				break;
			}
			output("`2You hand `7%s `2gems over to him and gulp down the strange `\$redish `2potion.`n`n",3);
			$session['user']['gems']-=3;
			if (potionpeddler_chance(60)) {
				output("`2Your body feels `t`bstrong`b `2after drinking the potion.`n`n");
				potionpeddler_hp(5);
			} else {
				output("`2Your body feels `4weak `2after drinking the potion.");
				potionpeddler_hp(-5);
				if (e_rand(0,3)==0 && $session['user']['hitpoints']>20) {
					output("`2`n`nYour body feels `)sick `2after drinking the potion.`n`n");
					$session['user']['hitpoints']-=20;
				}
			}
			addnav("Leave",$from."op=leave");
			break;
		case "askblack":
			output("`2\"`^This is a very dangerous potion sir. A sip of this could kill you. I'll give it to you for `7%s `^gems.`2\"",5);
			if ($session['user']['gems']>4) addnav("`2Take this potion",$from."op=getblack");
				else output("`n`n`4Unfortunately you do not have enough funds...");
			addnav("Back to the peddler",$from."op=investigate");
			break;
		case "getblack":
			if ($perm<10) {
				output("`2\"`^I'm very sorry but it doesn't seem that you body will be able to handle the side effects of this potion.`2\"");
				addnav("Back to the peddler",$from."op=investigate");
				break;
			}
			output("`2You hand `7%s `2gems over to him and gulp down the strange `~`bpitch-black`b `2potion.`n`n",5);
			$session['user']['gems']-=5;
			if (potionpeddler_chance(50)) {
				output("`2Your body feels `t`bstrong`b `2after drinking the potion.`n`n");
				potionpeddler_hp(10);
			} else {
				if (e_rand(0,2)==0) {
					output("`n`n`\$Your body couldn't withstand the side effect of the potion and you drop dead on the spot!");
					addnews("%s`2 has taken a sip of a lethal potion and died!",$session['user']['name']);
					$session['user']['specialinc'] = "";
					$session['user']['hitpoints']=0;
					$session['user']['alive']=0;
					output("`^`n`nYou lose all your gold on hand. You can play again tomorrow.");
					$session['user']['gold']=0;
					output("`^`nYou lose %s percent of your experience.",10);
					$session['user']['experience']*=0.90;
					require_once("lib/villagenav.php");
					villagenav();
					break;
				} else {
					output("`2Your body feels `4weak `2after drinking the potion.`n`n");
					potionpeddler_hp(-10);
				}
			}
			addnav("Leave",$from."op=leave");
			break;
		case "askglowing":
			output("`2\"`^You sure have a sharp eye sir. This is a very rare potion created by the 5th Hokage herself! It's not usually for sale but for you, my friend, `7%s `^gems!`2\"",10);
			if ($session['user']['gems']>9) addnav("`2Take this potion",$from."op=getglowing");
				else output("`n`n`4Unfortunately you do not have enough funds...");
			addnav("Back to the peddler",$from."op=investigate");
			break;
		case "getglowing":
			output("`2You hand `7%s `2gems over to him and gulp down the strange `v`bglowing`b `2potion.",10);
			$session['user']['gems']-=10;
			output("`2Your body feels `t`bstrong`b `2after drinking the potion.`n`n");
			potionpeddler_hp(5);
			addnav("Leave",$from."op=leave");
			break;
		case "askrainbow":
			output("`2\"`^This potion is very unstable and dangerous sir. It's one of the many insane creations of `@Orochimaru `^himself! Well, if you are sure you are willing to take the chances I'll give it to you for a bargain price of `7%s `^gems.`2\"",3);
			if ($session['user']['gems']>2) addnav("`2Take this potion",$from."op=getrainbow");
				else output("`n`n`4Unfortunately you do not have enough funds...");
			addnav("Back to the peddler",$from."op=investigate");
			break;
		case "getrainbow":
			if ($perm<10) {
				output("`2\"`^I'm very sorry but it doesn't seem that you body will be able to handle the side effects of this potion.`2\"");
				addnav("Back to the peddler",$from."op=investigate");
				break;
			}
			output("`2You hand `7%s `2gems over to him and gulps down the strange `\$c`qo`!l`@o`5r`)f`6u`3l `2potion.`n`n",3);
			$session['user']['gems']-=3;
			$chance=e_rand(1,100);
			if ($chance<20) {
				output("`2Your body feels `t`bstrong`b `2after drinking the potion.`n`n");
				potionpeddler_hp(e_rand(1,10));
			} elseif ($chance<40) {
				output("`2Your body feels `4weak `2after drinking the potion.`n`n");
				potionpeddler_hp(-e_rand(1,10));
			} elseif ($chance<60) {
				output("`2Your body feels `)tired `2after drinking the potion.");
				if ($session['user']['turns']>0) {
					output(" You lost `7%s `2forest fight!",1);
					$session['user']['turns']--;
				}
			} elseif ($chance<70) {
				output("`2You feel that a small crunch of `@Orochimaru`2's power is flowing through your veins!`n`n");
				apply_buff('powerpeddler',array(
					"name"=>"`@Orochimaru`2's `\$Power",
					"rounds"=>40,
					"wearoff"=>"The power leaves you...",
					"atkmod"=>1.05,
					"defmod"=>1.05,
					"minioncount"=>1,
					"roundmsg"=>"You feel `@Orochimaru`2's power...",
					"schema"=>"module-potionpeddler"
					));
			} else {
				output("`n`n`\$Your body couldn't withstand the side effect of the potion and you drop dead on the spot!");
				addnews("%s`2 has taken a sip of a lethal potion and died!",$session['user']['name']);
				$session['user']['specialinc'] = "";
				$session['user']['hitpoints']=0;
				$session['user']['alive']=0;
				output("`^`n`nYou lose all your gold on hand. You can play again tomorrow.");
				$session['user']['gold']=0;
				output("`^`nYou lose %s percent of your experience.",10);
				$session['user']['experience']*=0.90;
				require_once("lib/villagenav.php");
				villagenav();
				break;
			}
			addnav("Leave",$from."op=leave");
			break;
		case "leave":
			output("`2You continue on your journey after having had this kind of... business...");
			$session['user']['specialinc'] = "";
			break;
		case "walkaway":
			output("`2You don't like the stupid grin on his face. Pushing him aside, you continue on your way.");
			$session['user']['specialinc'] = "";
			break;
		break;
	}
}

function potionpeddler_run(){
}

function potionpeddler_calc() {
	global $session;
	$hp=$session['user']['maxhitpoints']-$session['user']['level']*10; //leftover
	return $hp;
}

function potionpeddler_chance($random) {
	if ($random<0) return false;
	if ($random>100) return true;
	//else random...
	if (e_rand(0,100)<=$random) return true;
	return false;
}

function potionpeddler_hp($amount) {
	global $session;
	if (abs($amount)>1) $hp=translate_inline("hitpoints");
		else $hp=translate_inline("hitpoint");
	if ($amount>0) {
		output("`2You gain `%%s`2 `bpermanent`b %s!",$amount,$hp);
		$session['user']['maxhitpoints']+=$amount;
	} elseif ($amount<0) {
		output("`2You `\$lose `%%s`2 `bpermanent`b %s!",abs($amount),$hp);
		$session['user']['maxhitpoints']+=$amount;
		if ($session['user']['hitpoints']>$session['user']['maxhitpoints']) $session['user']['hitpoints']=$session['user']['maxhitpoints'];
	}
}

?>
