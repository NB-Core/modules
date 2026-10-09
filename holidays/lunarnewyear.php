<?php
use Lotgd\Battle;
/*
There are a few things I'd like to change about the Chinese New Year Module;
1, Please change the name to "Lunar New Year" as it is not only being celebrated by the Chinese. (done)
2, Players receive "Lucky Red Envelope" on a New Day page which gives them a random amount of gold. (done)
3, Add Kirin as a "Hunt" event while the module is active.
4, Add "Firecrackers" as a "Seasonal" item that can be bought from the merchant.
*/


function lunarnewyear_getmoduleinfo(){
	$info = array(
			"name"=>"Meet the Kirin (Lunar New Year) (fixed dates till 2043, when I retire)",
			"author"=>"`2Oliver Brendel",
			"version"=>"1.01",
			"category"=>"Holidays|Lunar New Year",
			"settings"=>array(
				"Kirin - Settings,title",
				"pre_days"=>"How many times before will this start,int|5",
				"post_days"=>"And how long will it last,int|14",
				),
			"prefs"=>array(
				"Lunar New Year - Prefs,title",
				"hadevent"=>"Has the user had this event,bool|0",
				),
		     );
	return $info;
}

function lunarnewyear_install(){
	// Fixed dates to alleviate server stress, but please if you change settings do a reinstall
	$pre_days = get_module_setting('pre_days','lunarnewyear');
	$post_days = get_module_setting('post_days','lunarnewyear');
	module_addeventhook("forest","require_once('modules/lunarnewyear.php'); return 
			(get_module_pref('hadevent','lunarnewyear')==0 && lunarnewyear_islunarnewyear($pre_days,$post_days)?100:0);");
	module_addhook("newday");
	return true;
}

function lunarnewyear_uninstall(){
	return true;
}

function lunarnewyear_islunarnewyear($pre_days, $post_days) {
		$cnyDates = [
		'2024' => '2024-02-10',
		'2025' => '2025-01-29',
		'2026' => '2026-02-17',
		'2027' => '2027-02-06',
		'2028' => '2028-01-26',
		'2029' => '2029-02-13',
		'2030' => '2030-02-03',
		'2031' => '2031-01-23',
		'2032' => '2032-02-11',
		'2033' => '2033-01-31',
		'2034' => '2034-02-19',
		'2035' => '2035-02-08',
		'2036' => '2036-01-28',
		'2037' => '2037-02-15',
		'2038' => '2038-02-04',
		'2039' => '2039-01-24',
		'2040' => '2040-02-12',
		'2041' => '2041-02-01',
		'2042' => '2042-01-22',
		'2043' => '2043-02-10',
		];

		// Looping is tedious, but technically $pre_days could fall into the previous year.
		$today = new DateTime(); // Today's date
		foreach ($cnyDates as $year => $date) {
			$cnyDate = new DateTime($date);
			$preDate = clone $cnyDate;
			$postDate = clone $cnyDate;

			// Adjusting for the pre and post days
			$preDate->modify("-{$pre_days} days");
			$postDate->modify("+{$post_days} days");

			// Check if today's date is within the range
			if ($today >= $preDate && $today <= $postDate) {
				return 1;
			}
		}
		// If none of the ranges match
		return 0;
}

function lunarnewyear_dohook($location,$args) {
	global $session;
	$pre_days = get_module_setting('pre_days','lunarnewyear');
	$post_days = get_module_setting('post_days','lunarnewyear');
	$had_event = get_module_pref('hadevent','lunarnewyear');
	switch($location) {
		case "newday":
			if (!lunarnewyear_islunarnewyear($pre_days,$post_days)) // reset the event if it's not the chinese new year
			{
				if ($had_event) {
					// No need to reset the event if it's ouside of lunar new year
					set_module_pref('hadevent',0,'lunarnewyear');
				}
			}
			else {
				output("`c`\$Lunar New Year!`0`c");
				// Now give out a red envelope (text) and award a random amount of gold based on the level and dragonkills
				$gold = e_rand(1,25); // baseline
				$gold *= max(100, $session['user']['level'] * log($session['user']['dragonkills']));
				$session['user']['gold'] += (int)$gold;
				output("`n`n`@You receive a `\$Lucky Red Envelope`@ containing `^%s gold`@!",$gold);
			}
			break;
	
	}
	return $args;
}

function lunarnewyear_runevent($type){
	global $session;
	$op = httpget('op');
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:lunarnewyear";
	output_notl("`n");
	switch ($op){
		case "":
			output("`@You see something `greflecting `@the `tsunlight `@along a less 
					travelled path... you also feel a bit tired right now... do you want 
					to...");
			addnav("Lie down for a bit and then investigate",$from."op=investigate");
			addnav("Leave",$from."op=continue");
			break;
		case "continue":
			output("`@It might just be nothing but a piece of `lbroken glass. `@You 
					shrug and continue on your way.");
			$session['user']['specialinc']='';
			break;
		case "investigate":
			output("`@Deciding that you wish to take a little rest, you stop off in a 
					clearing.");
			output("`@It is a `Lbeautiful day`@ with `gwind`@ rustling through the 
					`gleaves`@.");
			output("`@After a while, you decide to leave and make your way through an 
					overgrown path where you have spotted the `greflection`@. Your eyes widen 
					when you see the `2huge scale`@ almost the size of your palm, lying on the 
					ground.");
			output("`n`nWhat do you do?");
			addnav("Pick it up",$from."op=pickup");
			addnav("Leave",$from."op=leave");
			break;
		case "leave":
			output("`@You decide not to touch the `2unknown scale `@and make your way 
					back to the path that you are familiar with.");
			$session['user']['specialinc']='';
			break;
		case "pickup":
			output("`@As you were about to bend over to pick up the `2mysterious 
					scale`@, a creature with the head of a `\$dragon`@, the antlers of a 
					`)deer`@, the skin and scales of a `1fish`@, the hooves of an `Qox`@ and 
				tail of a `#lion`@ suddenly appears out of nowhere and attacks you! `QIt's 
				the legendary creature that only comes from it's lair during `\$Lunar New 
				Year`Q, the creature known as `gKi`tri`gn`Q!");
			addnav("Defend yourself!",$from."op=defend");
			break;
		case "hilfeichbineinadminholtmichhierraus":
			output("Due to your powers as a god you teleport yourself out of it.");
			$session['user']['specialinc'] = "";
			break;
		case "defend":
			$kirin = array(
					"creaturename"=>"`gKi`tri`gn`0",
					"creatureweapon"=>"`\$flaming `4h`%oo`)v`4es`0",
					"creaturelevel"=>$session['user']['level'],
					"creatureattack"=>($session['user']['attack']+$session['user']['dragonkills']/2),
					"creaturedefense"=>($session['user']['defense']),
					"creaturehealth"=>($session['user']['maxhitpoints']+e_rand($session['user']['dragonkills'],$session['user']['dragonkills']*9)),
					"schema"=>"module-lunarnewyear",
				      );
			$flames	 = array(
					"startmsg"=>"`n`^The `gKi`tri`gn`^ starts starts breathing fire!`n",
					"name"=>"`vKi`)r`vin `\$Flames",
					"rounds"=>-1,
					"wearoff"=>"The flames begin to disappear.",
					"minioncount"=>$session['user']['level'],
					"mingoodguydamage"=>1,
					"maxgoodguydamage"=>log($session['user']['dragonkills']+exp(1))^2+log($session['user']['level']),
					"effectmsg"=>"Flames surround you, dealing {damage} damage.",
					"effectnodmgmsg"=>"Jumping high, you are able to clear the fire.",
					"activate"=>"roundstart",
					"schema"=>"module-lunarnewyear",
					);
			$session['user']['badguy'] = createstring($kirin);
			apply_buff("kirin-flames",$flames);
			$op = "fight";
			httpset('op',$op);
		case "fight":
			if (file_exists("modules/lunarnewyear/Nian.jpg")) rawoutput("<center><img 
					src='modules/lunarnewyear/Nian.jpg'></center><br>");
			include("battle.php");
			if ($victory){
				strip_buff("kirin-flames");
				set_module_pref("hadevent",1);
				$session['user']['specialinc'] = "";
				output("`QThe `gKi`tri`gn`Q disappears into `\$flames`Q, leaving a 
						`\$red packet `Qon the spot where it once stood. You curiously pick up the 
						`\$red packet `Qand open it...`n`n");
				switch(e_rand(0,5)) {
					case 0:
						output("... you find `^one thousand gold pieces`g! `\$Happy Lunar 
								New Year!");
						$session['user']['gold']+=1000;
						break;
					case 1:
						$gems=e_rand(2,4);
						output("... lucky! You find `% %s`g gems! `\$Happy Lunar New Year!",$gems);
						$session['user']['gems']+=$gems;
						break;
					case 2:
						if (!is_module_active("inventory")) {
							output("... nothing!");
							break;
						}
						require_once("modules/inventory/lib/itemhandler.php");
						$number=e_rand(2,4);
						add_item_by_name("Health Elixir $number");
						output("... a health elixir %s! `\$Happy Lunar New Year!",$number);
						break;
					case 3:
						if (!is_module_active("inventory")) {
							output("... nothing!");
							break;
						}
						require_once("modules/inventory/lib/itemhandler.php");
						add_item_by_name("Talisman of Defense");
						output("...a Talisman of Defense! `\$Happy Lunar New Year!");
						break;
					case 4:
						if (!is_module_active("inventory")) {
							output("... nothing!");
							break;
						}
						require_once("modules/inventory/lib/itemhandler.php");
						add_item_by_name("Talisman of Attack");
						output("...a Talisman of Attack! `\$Happy Lunar New Year!");
						break;
					case 5:
						if (!is_module_active("inventory")) {
							output("... nothing!");
							break;
						}
						require_once("modules/inventory/lib/itemhandler.php");
						$number=e_rand(2,4);
						add_item_by_name("Specialty Elixir");
						output("... a Specialty Elixir! `\$Happy Lunar New Year!");
						break;
				}
				addnews("%s`^ survived an encounter with the legendary beast 
						`gKi`tri`gn`^.",$session['user']['name']);
			}elseif($defeat){
				strip_buff("kirin-flames");
				set_module_pref("hadevent",1);
				$session['user']['gold'] = 0;
				$session['user']['hitpoints']=1;
				output("`n`n`@Realizing that you wouldn't be able to defeat the beast, 
						you run for your life. Unfortunately, you lost your pouch during your 
						escape.");
				$session['user']['specialinc'] = "";
				addnav("Flee to the village","village.php");
				addnews("%s`^ barely escaped the wraith of the legendary beast 
						`gKi`tri`gn`^.",$session['user']['name']);
			}else{
				Battle::fightnav(true,false);
				if ($session['user']['superuser'] & SU_DEVELOPER) addnav("Escape to 
						Village",$from."op=hilfeichbineinadminholtmichhierraus");
			}

	}

}
?>
