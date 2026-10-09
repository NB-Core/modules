<?php


function treasure_getmoduleinfo()
{
 $info = array(
  "name"=>"Treasure Chest",
  "author"=>"`LShinobiIceSlayer",
  "version"=>"1.0",
  "category"=>"Forest Specials",
  "download"=>"",
  "settings"=>array(
   "Treasure Chest - Preferences, title",
   "A Treasure Chest with a few surprises.,note",
   "steps"=>"The number of steps needed to reach top of the Cliff,int|3",
  ),
  );
 return $info;
}

function treasure_install() {
	module_addeventhook("forest", "return 1;");
	return true;
}

function treasure_uninstall() {
	return true;
}

function treasure_dohook($hookname,$args) {
	return $args;
}
function treasure_runevent($type,$link) {
	global $session;
	require_once("modules/inventory/lib/itemhandler.php");
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:treasure";
	$op = httpget('op');
	$maxsteps=get_module_setting("steps");
	$keys=array(
		"red"=>"`\$Red Key",
		"black"=>"`~Black Key",
		"orange"=>"`qOrange Key",
		"blue"=>"`1Blue Key",
		"green"=>"`2Green Key",
		"gray"=>"`)Gray Key",
		"purple"=>"`5Purple Key",
		);
	switch ($op)
	{
		case "":
			output("`&You wander happily through the forest as you see a cliff up ahead.`n");
			output("`nAs you get closer, you see a Treasure Chest sitting on a ledge.");
			output("`nLooking upward, you think you could climb this rockface though it will be hard and you are prone to fall as you have no climbing gear with you.");
			addnav("Options");
			addnav("Climb the Cliff",$link."op=climb&steps=1");
			addnav("Keep Going",$link."op=leave");
		break;
		case "leave":
			output("`&You feel that you have no time for such childish things, and continue on your away.");
			$session['user']['specialinc'] = "";
		break;
		case "climb":
			$steps=httpget("steps");
			$randomchance=e_rand(1,5);
			if ($steps==$maxsteps) {
				output("`&You reach the top at last!");
				addnav("Options");
				addnav("Find the Chest",$link."op=chest"); 
			} elseif ($randomchance>1 or $steps==1) {
				output("`&You reach for branches, and grooves in the rocks to try and pull yourself up the cliff... one step taken, %s to go...!`n`n",$maxsteps-$steps);
				$steps++;
				addnav("Options");
				addnav("Keep Climbing",$link."op=climb&steps=$steps");
				addnav("Give up",$link."op=leave");
			} else {
				output("`&You grab a nearby tree branch, only to have it snap under your weight, causing you to fall.");
				$painchance=e_rand(1,3);
				if ($painchance==2) {
					$hploss=$session['user']['hitpoints']*0.4;
					$session['user']['hitpoints']-=$hploss;
					output("You lose %s from the fall.",$hploss);
				}
				$session['user']['specialinc'] = "";
			}
		break;
		case "chest":			
			$keynumber=e_rand(1,7);
			switch ($keynumber)
			{
				case "1":
					$key="red";
				break;
				case "2":
					$key="black";
				break;
				case "3":
					$key="orange";
				break;
				case "4":
					$key="blue";
				break;
				case "5":
					$key="green";
				break;
				case "6":
					$key="gray";
				break;
				case "7":
					$key="purple";
				break;
			}			
			$keyname=$keys[$key];
			output("`&You finally find the large Treasure Chest, but it is as has a large %s lock `&on it. What do you do?",$keyname);
			addnav("Open with...");
			$hasitem=check_qty_by_name($keyname,$session['user']['acctid']);
			if ($hasitem>0) addnav($keyname,$link."op=treasure&key=$key");
			addnav("Force Open",$link."op=treasure&key=none"); 
			addnav("Leave the Chest",$link."op=leave");
		break;
		case "treasure":
			$key=httpget("key");
			if($key!="none") output("`&You put your key in the lock, and slowly turn it.");
			switch ($key) 
			{
				case "red":
					output("`n`n`&You open the chest with your `\$red key `& when a beautiful woman appears!");
					if ($session['user']['sex']){
						output("`nShe attempts to kiss you, but seeing your a female, she slaps you instead.");
						$charmloss=e_rand(1,5);
						$session['user']['charm']-=$charmloss;
						output("`n`nYou lose %s Charm",$charmloss);
					}else{
						output("`nShe grabs you and tells you to pucker up!");
						$charmgain=e_rand(1,5);
						$session['user']['charm']+=$charmgain;
						output("`n`nYou gain %s Charm!",$charmgain);
					}
				break;
				case "black":
					output("`n`n`&You open the chest with your `~Black Key`&. A small old man rises from the chest, and offers you small potion.");
					output("`n`nYou Gain one Specialty Elixir!");
					$item="Specialty Elixir";
					$result=add_item_by_name($item,1);
				break;
				case "orange":
					output("`n`nYou open the chest with your `qOrange Key`&. You look inside and feel a warm glow which feels you with energy");
					apply_buff('chestglow1',
					array(
						"name"=>"`qInner Warmth",
						"rounds"=>50,
						"wearoff"=>"You lose that happy feeling.",
						"defmod"=>1.2,
						"roundmsg"=>"`qYou feel happy and warm",
						"schema"=>"module-treasure",
						));
				break;
				case "blue":
					output("`n`nYou open the chest, and a medic nin rises up, and hands you a small bottle.");
					$elixirnum=e_rand(1,10);
					switch($elixirnum)
					{
						case "1": case "2": case "3": case "4":
							$item="Health Elixir 1";
						break;
						case "5": case "6":
							$item="Health Elixir 2";
						break;
						case "7": 
							$item="Health Elixir 3";
						break;
						case "8": 
							$item="Health Elixir 4";
						break;
						case "9": 
							$item="Health Elixir 5";
						break;
						case "10": 
							$item="Health Elixir 6";
						break;
					}
					add_item_by_name($item,1);
					output("`n`nYou get one %s",$item);
				break;
				case "green":
					output("`n`nYou open the chest with your `2Green Key `&but nothing seems to happen, so you look in to see what was in there that you spent all this time looking for.");
					output(" As you peer in, there appears to be no bottom to the chest, but only a deep, endless void.");
					output("`nThen the chest starts to leak a thick `~Black Mist `&which seems to pull you in... never to be seen again.");
					$session['user']['alive']=false;
					$session['user']['hitpoints']=0;
					addnav("Daily News","news.php");
				break;
				case "grey":
					output("`n`nYou open the chest, and look deep inside, and you see something shinning at the bottom");
					$talismannum=e_rand(1,2);
					if ($talismannum==1){
						$talisman="Talisman of Attack";
					}else{
						$talisman="Talisman of Defense";
					}
					add_item_by_name($talisman,1);
					output("`n`nYou find one %s.",$talisman);
				break;
				case "purple":
					output("`n`nYou Open you chest, and a large royal figure steps out.");
					$nuggetsize=e_rand(1,5);
					switch($nuggetsize)
					{
						case "1": case "2": case "3":
							$nugget="Gold Nugget(Sm)";
						case "4":
							$nugget="Gold Nugget(Md)";
						case "5":
							$nugget="Gold Nugget(Lg)";
					}
					add_item_by_name($nugget,1);
					output("`n`nHe hands you a %s on a pillow.",$nugget);
				break;
				case "none":
					$weapon=$session['user']['weapon'];
					output("You beat the chest with your %s, but nothing happens.",$weapon);
				break;
			}
			if($key!="none") {
				$keyname=$keys[$key];
				remove_item_by_name($keyname,1, $session['user']['acctid']);
			}
			$session['user']['specialinc'] = "";
		break;
	}
}
function treasure_run(){
}

?>		
