<?php

function valentine_getmoduleinfo(){
	$info = array(
		"name"=>"Valentine (timelocked 14th)",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Holidays|Valentine Specials",
		"prefs"=>
			array(
			"valentine"=>"Count,int",
			
			),
	);
	return $info;
}

function valentine_install(){
	module_addeventhook("forest", "require_once(\"modules/valentine.php\"); return valentine_chance('forest');");
	module_addeventhook("village", "require_once(\"modules/valentine.php\"); return valentine_chance('village');");
	module_addhook("newday");
	return true;
}
function valentine_chance($hook) {
	global $session;
	$test=(int)get_module_pref('valentine','valentine',$session['user']['acctid']);
	$test2 = (int)(date('m-d')=='02-14'?1:0);
	return ($test>0 || $test2!=1 ? 0:100);
}

function valentine_uninstall(){
	return true;
}

function valentine_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
		case "newday":
			if (get_module_pref('valentine')!=1) break;
			$gems = e_rand(1,5);
			output("You find a note on your pillow as you awake: `n`n\"`qThank you for the time we had ... it was a wonderful Valentine's Day.... here is something to make you remember me.`nLove,...`l\"`n`n`@You find %s gems in the envelope!`n`n",$gems);
			$session['user']['gems']+=$gems;
			set_module_pref('valentine',0); //gift received
			break;
		
	}
	return $args;
}

function valentine_runevent($type,$link){
	global $session;	
	$op=httpget('op');
	$from=$type.".php?";
	addnav("Actions");
	$attracted_to=$session['user']['prefs']['sexuality'];
	
	switch ($op) {
		case "no":
			output("`lYou walk away from that highly suspicious .... hood.");
			$session['user']['specialinc'] = "";
			break;
		case "yes":
			$hair=translate_inline(array('`^golden','`ybronze','`~black','`xbrown','`$cherry','`$red','`gashen'));
			$eyes=translate_inline(array('hazel','`~black','`#white','`$red','`qorange','`@green','`^emerald'));
			$gender=($attracted_to==SEX_MALE?"man":"woman");
			$hair_pick=e_rand(0,count($hair)-1);
			$eyes_pick=e_rand(0,count($eyes)-1);
			
			output("`lAs the figure lifts his/her/its hood, you swallow down a lump of anxiety and wait what happens...`n`n");
			
			output("`qBefore you stands the most beautiful %s you've ever seen!`nThe %s`q hair flows freely around a face made by the heavens ... %s`q eyes look on you, and penetrate your very soul...`n`n",translate_inline($gender),$hair[$hair_pick],$eyes[$eyes_pick]);
			
			
			set_module_pref('valentine',1); //gift coming in
			output("Hand in hand you two walk away and spend the rest of the day and evening together ...`n`n");
			set_module_pref("valentine",1);
			
			output("`c`b`\$H`qappy `\$V`qalentine's Day!`b`c`n`n");
			$session['user']['specialinc'] = "";
			break;
		default:
			$session['user']['specialinc'] = "module:valentine";
			output("`lYou are approached by a figure cloaked in red - you can't even see the face under a very long hood.`n`nAs you grow suspicious, you hear a voice like golden honey flowing down a waterfall of milk:`n`n\"`\$Will you be my valentine?\"`l`n`nPuzzled, you realize he/she/it wants an answer.");
			addnav("Yes, I want to",$from."op=yes");
			addnav("No.",$from."op=no");
			
	}
	
}

function valentine_run(){
}
?>
