<?php


function halloween_getmoduleinfo(){
	$info = array(
		"name"=>"`qHallo`Qween `~Surprise`0",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Halloween",
		"download"=>"",

	);
	return $info;
}

function halloween_install(){
	module_addhook("pvpmodifytargets");
	module_addhook("pvpadjust");
	module_addhook("newday");
	return true;
}

function halloween_uninstall(){
	return true;
}

function halloween_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
	case "newday":
		if ($session['user']['playerfights']<30) {
			$session['user']['playerfights']=30;
		}
		break;
	case "pvpmodifytargets":
		$id=$session['user']['acctid'];
		$loc=$args[0]['location'];
		$last = date("Y-m-d H:i:s",
				strtotime("-".getsetting("LOGINTIMEOUT", 900)." sec"));
			
		$sql = "SELECT acctid, name, alive, location, sex, level, laston, " .
			"loggedin, login, pvpflag, clanshort, clanrank, dragonkills, " .
			db_prefix("accounts") . ".clanid FROM " .
			db_prefix("accounts") . " LEFT JOIN " .
			db_prefix("clans") . " ON " . db_prefix("clans") . ".clanid=" .
			db_prefix("accounts") . ".clanid WHERE (locked=0) " . 
			"AND (slaydragon=0) AND " .
			"(dragonkills>0 OR pk>0) " .
		// no regrets
		//	"AND (alive=1) " .
//			"AND (laston<'$last' OR loggedin=0) AND (acctid<>$id) " . 
			"ORDER BY location, level DESC, " .
			"experience DESC, dragonkills DESC";
		$result=db_query($sql);
		$pvp = array();
		while($row = db_fetch_assoc($result)) {
			$row['pvpflag']=0;
			$row['anylocation']=1;
			$pvp[] = $row;
		}
		$args=$pvp;
		break;
	case "pvpadjust":
		if (e_rand(0,9)==7) { //pumpkin massacre, check pvpsupport and pvplist for locationblockers as well as pvplist for "cannot reach"
			$row=$args;
			$sql = "UPDATE " . db_prefix("accounts") . " SET pvpflag='0' WHERE acctid={$row['acctid']}";
			db_query($sql);
			$row['creaturelevel']=$session['user']['level'];
			$row['acctid']=0; //let the original player live
			$row['creaturehealth']=round($session['user']['hitpoints']*e_rand(100,200)/100,0);
			$row['creatureattack']=round($session['user']['attack']*e_rand(75,200)/100,0);
			$row['creaturedefense']=$session['user']['defense'];
			$row['race']=translate_inline("Halloween Monster");
			output("`c`b`i`qH`Qa`qpp`Q`qy`Q `vHa`tll`vo`qween`0`b`c`i`n`n");
			output("`\$ OH NO! This is not %s`0! You are being deceived by an unbelievable monster lurking in the darkest nights!`n`n",$row['creaturename']);
			switch(e_rand(0,9)) {
				case 0:
					$row['creaturename']=translate_inline("`!Freddy Kruger");
					$row['creatureweapon']=translate_inline("Razorsharp Claws");
					break;
				case 1:
					$row['creaturename']=translate_inline("`qPump`Qkin `4Man");
					$row['creatureweapon']=translate_inline("Neither Sweets nor Sour Stuff");
					break;
				case 2:
					$row['creaturename']=translate_inline("`~Dead`4inside");
					$row['creatureweapon']=translate_inline("`~Instant`4kill");
					break;
				case 3:
					$row['creaturename']=translate_inline("`vHa`tll`vo`qween Whopper from Hell");
					$row['creatureweapon']=translate_inline("`3Calories");
					break;
				case 4:
					$row['creaturename']=translate_inline("`~Wicked `5Witch");
					$row['creatureweapon']=translate_inline("`lNasty `gBroom");
					break;
				case 5:
					$row['creaturename']=translate_inline("`vHa`tll`vo`qween `@B`2o`@r`2g");
					$row['creatureweapon']=translate_inline("`lSweets`2! `2Resistance`g is futile`2!");
					break;
				case 6:
					$row['creaturename']=translate_inline("`qLone `QPump`qkin");
					$row['creatureweapon']=translate_inline("`7Lone`6liness `5At Home");
					break;
				case 7:
					$row['creaturename']=translate_inline("`vSpirit of `vHa`tll`vo`qween");
					$row['creatureweapon']=translate_inline("`tUnbelievable `)H`~o`)rr`~o`)r");
					break;
				case 8:
					$row['creaturename']=translate_inline("`\$Naruto `tIn A Leotard");
					$row['creatureweapon']=translate_inline("`\$Kyuubi Power");
					break;
				case 9:
					$row['creaturename']=translate_inline("`%Sakura `tIn A Leotard");
					$row['creatureweapon']=translate_inline("`1Monstrous `!Punch `qIn `QThe `\$Gut");
					break;
				}
				$args=$row;
		}
	}
	return $args;
}

function halloween_run(){
}


?>
