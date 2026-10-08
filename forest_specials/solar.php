<?php
function solar_getmoduleinfo(){
	$info = array(
		"name"=>"Solar Eclipse",
		"version"=>"1.3",
		"author"=>"Robert",
		"category"=>"Forest Specials",
		"download"=>"http://dragonprime.net/users/robert/solar098.zip",
	);
	return $info;
}

function solar_install(){
	module_addeventhook("forest", "return 100;");
	return true;
}

function solar_uninstall(){
	return true;
}

function solar_dohook($hookname,$args){
	return $args;
}

function solar_runevent($type){
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:solar";
	$op = httpget('op');
	if ($op=="" || $op=="search"){
    output("`n`n`2 You come to notice a darkness in the middle of the day. "); 
    output("`n`n`2 You look up towards the `^ SUN `2 and notice there is a Solar Eclipse occurring at this very moment. "); 
    output("`n`n`& What will you do? "); 
    addnav("Solar Eclipse"); 
    addnav("(W) Watch the event",$from."op=watch"); 
    addnav("(C) Continue On!",$from."op=dont"); 

}elseif ($op=="watch"){
	$session['user']['specialinc'] = "";
  if ($session['user']['turns']>=3){
      output("`n`n`2 You watch as the `& MOON `2 passes the `^ SUN `2. `n`n");
        switch(e_rand(1,10)){ 
        case 1: 
           output(" The event is beautiful and you will always remember this day. ");
           output("`n`n You feel a surge of energy pass through your veins. ");
           output("`n`n You gain 1 turn. ");
           $session['user']['turns']++;
           break; 
        case 2: case 3:
           output(" After you stare at this marvelous event, you find you are temporarily blinded. "); 
           output("`n`n You lose 2 turns. "); 
           $session['user']['turns']-=2;
           break;
        case 4: case 5: case 6: case 7: case 8: 
           output(" You find this event to be forever ingrained into your memory. ");
           output("`n`n You feel very tired and lose 1 turn. ");
           $session['user']['turns']--;
           break; 
        case 9: case 10:
           output(" After watching this marvelous event, you find you possess great inner strength. ");
           $session['user']['hitpoints']++;
           break; 
        } 
    }else{ 
      output("`n`n`2 You watch as the `& MOON `2 passes the `^ SUN `2.`n`n "); 
      output(" The event is beautiful and you will always remember this day. ");
    } 
}else{ 
  output("`n`n`2 Not wanting to waste your time watching some silly solar event, you continue on your way. ");
  output("`n`n As you walk away the forest path gets darker and darker, you stumble and fall ....maybe it was fate? ");
  $session['user']['hitpoints']-=5;
}
}
function solar_run(){
}
?>