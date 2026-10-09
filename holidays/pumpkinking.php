<?php
function pumpkinking_getmoduleinfo(){
	$info = array(
		"name"=>"The Pumpkin King in the Forest (timelocked)",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel, Text by `4Gyururu",
		"category"=>"Holidays|Halloween",
		"download"=>"",
		"settings"=>array(
			"Settings,title",
			"start"=>"Start Date MM-DD,string|10-29",
			"end"=>"End Date MM-DD,string|11-02",
			"candy"=>"Candy ID,int|168",
			),
		"preferences"=>array(
			"King Preferences,title",
			"seen"=>"Has the user had this one?,bool|0",
			),
		"requires"=>array(
			"datemanager"=>"1.0|By Oliver Brendel",
		),
	);
	return $info;
}

function pumpkinking_install(){
	module_addeventhook("forest","require_once(\"modules/pumpkinking.php\"); return pumpkinking_chance();");

	return true;
}

function pumpkinking_chance() {
	// Check if the date is in the range
	require_once("modules/datemanager.php");
	$start = get_module_setting("start","pumpkinking");
	$end = get_module_setting("end","pumpkinking");
	$check=datemanager_datecheck($start,$end,0);

	//check for activation date
	if ($check!=1) return 0;

	$check=get_module_pref("seen","pumpkinking");
	return ($check==0?100:25);
}

function pumpkinking_uninstall(){
	return true;
}

function pumpkinking_dohook($hookname,$args){
	global $session;
	switch($hookname){
	default:

		break;
	}
	return $args;
}

function pumpkinking_runevent($type) {
	global $session;
	$session['user']['specialinc'] = "module:pumpkinking";
	$u=&$session['user'];
	$seen=get_module_setting('seen');
	$op = httpget('op');
	$from=$type.".php?op=";
	require_once("modules/inventory/lib/itemhandler.php");
	$id=get_module_setting('candy');
	$name="`QPumpkin `\$King";
	//output("`c`b`lSomething Special!!!`b`c`2`n`n");
	switch($op) {
	case "away": case "leave":
		$amount=e_rand(0,min(3,$u['turns']));
		output("`7With your quick reflexes, you manage to knock the pointed cane away while moving your neck out of harm's way. You ran out of the house without looking back, running back into the forest through the `Qpumpkin path`7.`n`nAs you ran along the path, you could hear a sinister laughter echoing thorugh the woods and the light from the pumpkin slowly fading away. After what seemed like an eternity of running through the dark, you finally made it out of the forest. `\$You lost %s forest fights from the terrifying ordeal!`n`n",$amount);
		$u['turns']-=$amount;
		$session['user']['specialinc'] = "";
		break;
	case "gold": case "gems":
		$what=$op;
		output("`3It took the %s and the pointed cane was withdrawn from your throat. You quickly turn around to see what it was, but you couldn't believe your own eyes. Right before your eyes stands a skinny figure in a `~worn out black suit `3with a `Qpumpkin `3as a head. 

`Q\"Yuck, I want `\$C`Qa`tn`@d`!y`Q!\"`3 `7it grumbled as it threw the %s out the window and started jumping up and down with its cane swinging wildly in the air. 

`^You quickly take this chance to escape from the %s`^!",$what,$what,$name);
		$session['user']['specialinc'] = "";
		set_module_pref("seen",1);
		break;
	case "candy":
		$what=$op;
		output("`3It took the %s and the pointed cane was withdrawn from your throat. You quickly turn around to see what it was, but you couldn't believe your own eyes. Right before your eyes stands a skinny figure in a `~worn out black suit `3with a `Qpumpkin `3as a head. 

`Q\"Yummy, I love `\$C`Qa`tn`@d`!y`Q!\" `7it mumbled while chewing the rock-hard candy to bits. 

`^The %s `^was pleased and left you with a small gift.",$what,$name);
		$sql="SELECT itemid,name FROM ".db_prefix('item')." WHERE class='Halloween' ORDER BY RAND();";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		if ($row) {
			output("`n`n`7You receive a/an %s`7!",$row['name']);
			add_item_by_id($row['itemid'],1);
		}
		remove_item_by_id($id,1);
		set_module_pref("seen",1);
		$session['user']['specialinc'] = "";
		break;
	case "depart":
		output("`7You turn around, forget that fella, and walk home...");
		$session['user']['specialinc'] = "";
		break;
	case "pcandy":
		$what="candy";
		$amount=(int)httpget('amount');
		output("`Q\"Yummy, I love `\$C`Qa`tn`@d`!y`Q!\" `7it mumbled while chewing the rock-hard candy pieces to bits. 

`^The %s`^ was pleased and left you with a small gift.",$name);
		$sql="SELECT itemid,name FROM ".db_prefix('item')." WHERE class='Halloween' ORDER BY RAND() limit $amount;";
		$result=db_query($sql);
		while  ($row=db_fetch_assoc($result)) {
			output("`n`n`7You receive a/an %s`7!`n",$row['name']);
			add_item_by_id($row['itemid'],1);
		}
		remove_item_by_id($id,$amount);
		set_module_pref("seen",1);
		$session['user']['specialinc'] = "";
		break;
	default:
		if (get_module_pref('seen')) {
			output("`7You enter a very well known clearing, where some skinny hand did something to you...`n`nWell, now there is a ... sick kind of throne made out of pumpkins (originally with lantern inside) and a... erm... %s`7 ... sitting on it.`n`n",$name);
			output("`Q\"I want `\$C`Qa`tn`@d`!y`Q!\" `7it grumbles as it jumps up and down with its cane swinging wildly in the air.");
			$sql="SELECT a.name,a.acctid FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON b.userid=a.acctid WHERE b.itemid='$id' AND b.userid='".$u['acctid']."';";
			$result=db_query($sql);
			$n=db_num_rows($result);
			if ($n>=1) {
				for ($i=1;$i<=$n;$i++) {
					addnav(array("Give %s Candy",$i),$from."pcandy&amount=$i");
				}
				addnav("Leave...",$from."depart");
			} else {
				output("`^You do not have any `\$C`Qa`tn`@d`!y `^to offer the %s`^. You run for your life as it swings its cane `\$angrily `^at you!",$name);
				$session['user']['specialinc'] = "";
			}
			return;
		}
		output("`3You `\$cautiously `3make your path to the `~darkest `3part of the forest, where legend has it that a terrifying creature called ´\"%s`3\" lived. You came to realize that the legend was true when you found a path `tlit up `3by hundreds of `Qcarved pumkins`3. The path leads you to a house, carved out of a `Qgigantic pumpkin`3.`n`n`7As you enter the house, a sinister laughter suddenly fills the room. `\$You immediately have your weapon drawn, but it wasn't fast enough as you realize there was something pointy pushing against your throat from behind.`n`n`Q\"Trick or treat!\" `7it uttered as its boney hand reach out to you from behind...",$name);
		addnav("Bribe with Gold",$from."gold");
		addnav("Bribe with Gems",$from."gems");
		$sql="SELECT a.name,a.acctid FROM ".db_prefix("accounts")." AS a INNER JOIN ".db_prefix("inventory")." AS b ON b.userid=a.acctid WHERE b.itemid='$id' AND b.userid='".$u['acctid']."';";
		$result=db_query($sql);
		$n=db_num_rows($result);
		if ($n>=1) {
			addnav("Give Candy",$from."candy");
		}
		addnav("Run away",$from."away");
		break;
	}
}

?>
