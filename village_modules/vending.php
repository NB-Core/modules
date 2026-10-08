<?php
function vending_getmoduleinfo(){
	$info = array(
		"name"=>"Vending Machine",
		"author"=>"Chris Vorndran",
		"version"=>"0.2",
		"category"=>"Village Specials",
		"download"=>"http://dragonprime.net/users/Sichae/vending.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"description"=>"Player finds a vending machine, and can get certain boons from the machine.",
			"settings"=>array(
				"cost"=>"Cost of a Soda from the Vending Machine,int|50",
			),
			"prefs"=>array(
				"count"=>"Has user had a soda today?,bool|0",
			),
		);
	return $info;
}
function vending_install(){
	module_addeventhook("village","\$count=get_module_pref(\"count\", \"vending\");return (\$count?0:50);");
	module_addhook("newday");
	return true;
}
function vending_uninstall(){
	return true;
}
function vending_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "newday":
			set_module_pref("count",0);
			break;
	}
	return $args;
}
function vending_runevent($type){
	global $session;
	$op = httpget('op');
	$cost = get_module_setting("cost");
	$session['user']['specialinc'] = "";
	$from = "village.php?";
	$str1 = translate_inline("`@more than enough gold for a soda");
	$str2 = translate_inline("`@just enough gold for a soda");
	$str3 = translate_inline("`@not enough gold for a soda");
		if ($session['user']['gold'] > $cost){
			$str = $str1;
			$nav = 1;
		}elseif($session['user']['gold'] == $cost){
			$str = $str2;
			$nav = 1;
		}else{
			$str = $str3;
			$nav = 0;
		}
	switch ($type){
		case "village":
			switch ($op){
				case "":
					$session['user']['specialinc'] = "module:vending";
					output("`@In the center of %s Square, you see a rather large vending machine.",$session['user']['location']);
					output("You wander over, and then thumb through the cash in your pocket.");
					output("You see a small sign stating, \"`2Sodas for `^%s `2Gold\"`@",$cost);
					output("Rifling in your pocket, you notice that you have `^%s `@Gold.",$session['user']['gold']);
					output("Which is %s`@.",$str);
					if ($nav == 1) addnav("Aquire Soda",$from."op=vend");
					addnav("Walk Off",$from."op=quit");
					break;
				case "vend":
					$session['user']['specialinc'] = "";
					$session['user']['gold']-=$cost;
					set_module_pref("count",1);
					output("`@You insert `^%s `@Gold into the Vending Machine.",$cost);
					output("You hear the clanking around, and then a long buzzing noise.");
					// protection against no-turn-spammers
					$random_chance = e_rand(1,5);
					if ($session['user']['turns']==0 && $random_chance==5) $random_chance=4;
					switch ($random_chance){
						case 1:
						$val = round($session['user']['maxhitpoints']/$session['user']['level']);
							output("`@The can hits the bottom of the Vending Machine, and explodes.");
							output("A shard of metal comes out, and strikes you.");
							output("You lose `\$%s `@Hitpoints.",$val);
							$session['user']['hitpoints']-=$val;
							debuglog("lost $val hitpoints to exploding can.");
							break;
						case 2:
							output("`@The can hits the bottom of the Vending Machine, and you pick it up.");
							output("You pop it open, and see a tiny glimmer at the bottom of the can.");
							output("You quickly drain the soda, and see a `%GEM `@at the bottom.");
							$session['user']['gems']++;
							debuglog("got a gem from vending machine");
							break;
						case 3:
						$val = e_rand(1,3);
							output("`@The can hits the bottom of the Vending Machine, and you lift it out.");
							output("Drinking it quickly, the sugar and caffeine goes into your blood stream.");
							output("This makes you feel energetic, allowing for `#%s `@more Forest %s.",$val,translate_inline($val==1?"Fight":"Fights"));
							$session['user']['turns']+=$val;
							debuglog("got $val forest fights from vending machine");
							break;
						case 4:
						$val = $cost*$session['user']['level'];
							output("`@You look down, and see no can drop out.");
							output("You swiftly kick the machine, and all of a sudden - gold starts pouring into the streets.");
							output("Quickly counting, you find `^%s `@Gold.",$val);
							$session['user']['gold']+=$val;
							debuglog("got $val gold from vending machine");
							break;
						case 5:
							output("`@You look down, and see no can fall out.");
							output("So, you pull out your `&%s `@and push it through the Vending Machine.",$session['user']['weapon']);
							output("From there, millions of cans pour out, crushing you instantly.");
							output("You peek your hand out from under the pile, and grab onto a small tree root.");
							output("You pull yourself out, badly bruised, and bleeding heavily.");
							output("You had survived death, but lost all of your gold on hand.");
							addnews("%s was crushed underneath a mountain of Soda Cans.",$session['user']['name']);
							$session['user']['hitpoints']=1;
							$session['user']['gold']=0;
							break;
					}
					break;
				case "quit":
					output("`@You look around, and trife in your pockets.");
					output("Not wishing to part with your gold, you put your pocketbook away, and walk away from the Vending Machine.");
					$session['user']['specialinc'] = "";
					break;
			}
		break;
	}		
}
?>
