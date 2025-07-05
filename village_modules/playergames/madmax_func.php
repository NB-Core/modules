<?php


function getlifepoints($number) {
	$array=getdata($number);
	if (!is_array($array)) return array();
	$lifepoints=0;
	while (!$lifepoints) {
		$pop=array_pop($array);
		if ($pop==NULL) break;
		if (array_key_exists("lifepoints",$pop)) $lifepoints=$pop['lifepoints'];
	}
	return $lifepoints;
}

function enterdata($number,$datanow) {
	$array=getdata($number);
	if ($array==NULL || !is_array($array)) {
		$array=array();
	}
	array_push($array,$datanow);
	setdata($number,$array);
}

function showflow($number) {
	$alldata=getalldata($number);
	if ($session['user']['acctid']==$alldata['playerone']) 
		addnav("End this game forcefully","runmodule.php?module=madmax&op=end&number=$number");
	output("`@This is the gameflow of '%s`@' until now:",getgamename($number));
	output_notl("`n`n");
	$array=unserialize($alldata['gamedata']);
	if (!$array) $array=array();
	while (list($key,$val)=each($array)) {
		output_notl(stripslashes($val['sentence'])."`n"); //strip because of the serialize/insert...if " in a userpost, there is \"
	}
}

function rolldice() {
	$one=e_rand(1,6);
	$two=e_rand(1,6);
	if ($one>$two) {
		return $one."-".$two;
	} else {
		return $two."-".$one;
	}
}

function comparedice($roll,$passon,$pre) {
	$pass=explode("-",$passon);
	if ($pass[0]<$pass[1]) return false;
	$value=array("3-1"=>1,
				"3-2"=>2,
				"4-1"=>3,
				"4-2"=>4,
				"4-3"=>5,
				"5-1"=>6,
				"5-2"=>7,
				"5-3"=>8,
				"5-4"=>9,
				"6-1"=>10,
				"6-2"=>11,
				"6-3"=>12,
				"6-4"=>13,
				"6-5"=>14,
				"1-1"=>15,
				"2-2"=>16,
				"3-3"=>17,
				"4-4"=>18,
				"5-5"=>19,
				"6-6"=>20,
				"2-1"=>21,
				);
	//debug($value);debug($pre);
	if ($pre!=0) {
		if ($value[$pre]>=$value[$passon]) return false;
	}
	return true;
}

function validatedice($roll) {
	$numbers=explode("-",$roll);
	$two=substr($numbers[1],0,1);
	if (!is_numeric($numbers[0]) || substr($roll,1,1)!="-" || !is_numeric($two)) {
		return false;
	} elseif ($two<1 || $two>6 || $numbers[0]<1 || $numbers[0]>6) {
		return false;
	} elseif ($two>$numbers[0]) {
		return false;
	} else return true;
}

function pointspost($link,$length) {
	rawoutput("<form action='$link' method='POST'>");
	addnav("",$link);
	rawoutput("<input name='points' maxlength='$length'>");
	$submit = translate_inline("Submit");
	rawoutput("<input type='submit' class='button' value='$submit'></form>");
}

function userpost($link,$length) {
	rawoutput("<form action='$link' method='POST'>");
	addnav("",$link);
	output("`@`nEnter your passing-on roll like 'x-x' where x is a number from 1 to 6 following the rules`n");
	output("You may enter any comment after it. 'x-x yes I did it' is valid. 'yes, a x-x!' is not.`n");
	rawoutput("<input name='passon' maxlength='$length'>");
	$submit = translate_inline("Submit");
	rawoutput("<input type='submit' class='button' value='$submit'></form>");
}

function madmax_getvalidnext($number) {
	global $session;
	//don't use the normal function here, we have to consider kicked out players with 0 lifepoints and have to cycle through			
	$sql="SELECT playerone,nextturn,players FROM ".db_prefix("playergames")." WHERE number=".$number;
	$result=db_query($sql);
	$row=db_fetch_assoc($result);
	$players=explode(",",$row['players']); //explode all players (except the opener)
	array_push($players,$row['playerone']); //add the opener
	$players=array_merge($players,$players); //double the array - array_merge adds only with identical numerical keys
	$pos=array_search($session['user']['acctid'],$players); //look for the current player
	//debug("pos:".$pos);
	//debug($players);
	$players=array_slice($players,$pos+1); //cut off after him to get the turn order
	//debug($players);
	$lifearray=getlifepoints($number);	
	$num_players=count($players);
	//debug($players);
	//debug($lifearray);
	$id=false;
	while (list($key,$id_player)=each($players)) {
		if ($lifearray["s".$id_player]>0) {
			$id=$id_player;
			break;
		}
	}
	/*for ($i=0;$i<$num_players;$i++) { //search for the next guy with positive lifepoints
		if ($lifearray["s".$players[$i]]>0) {
			$id=$players[$i];
			break; //next player with positive lifepoints found
		}
	}*/
	if (!$id) {
		output("An error happened somewhere in the game engine, please report this:`n");
		output("Error: not found an alive player but also did not close game.`n");
		output("Selected current player.`n");
		output("L-Array:`n");
		while (list($key,$val)=each($lifearray)) {
			output_notl("Key: %s, Val: %s`n",$key,$val);
		}
		while (list($key,$val)=each($players)) {
			output_notl("KeyS: %s, ValS: %s`n",$key,$val);
		}
		$id=$session['user']['acctid'];
	}
	return $id;
}
?>
