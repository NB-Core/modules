<?php

require_once("lib/http.php");

function sphinx_getmoduleinfo(){
	$info = array(
		"name"=>"The Sphinx",
		"version"=>"1.03",
		"author"=>"Enhas",
		"category"=>"Travel Specials",
		"download"=>"http://www.dragonprime.net/users/Enhas/sphinx.txt",
		"requires"=>array(
			"cities"=>"1.0|By Eric Stevens",
		),
		"settings"=>array(
			"The Sphinx Settings,title",
			"favorfelyne"=>"Does the Sphinx let Felynes pass immediately,bool|1",
                  "takegold"=>"How much gold (in percent) can the Sphinx take on battle loss,range,0,75,5|50",
                  "takegems"=>"How much gems (in percent) can the Sphinx take on battle loss,floatrange,0,10,2.5|5",
                  "takeexp"=>"Will the Sphinx take exp on battle loss,bool|1",
                  "takeexpamount"=>"Amount of exp (in percent) that can be won or lost in battle,floatrange,0,15,2.5|5",
		),
	);
	return $info;
}

function sphinx_install(){
	module_addeventhook("travel",
			"return (is_module_active('cities')?80:0);");
	return true;
}

function sphinx_uninstall(){
	return true;
}

function sphinx_dohook($hookname,$args){
	return $args;
}

function sphinx_runevent($type,$link)
{
	global $session;
	$goldchance = e_rand(1,5);
      $gemchance = e_rand(1,15);
      $goldoffering = $session['user']['level'] * (e_rand(75,150));
      $goldbattle = $session['user']['level'] * (e_rand(25,75));
      $takegold = get_module_setting("takegold");
      $takegems = get_module_setting("takegems");
      $takeexpamount = get_module_setting("takeexpamount");
	$op = httpget('op');
      $fromvillage = $session['user']['location'];
	$session['user']['specialinc'] = "module:sphinx";
      $battle = false;
      output("`n");
      switch($op){
	case "":
	case "search":
		output("`3Trudging your way through a narrow trail to the nearest city, you see something blocking the way in the distance.");
            output("Moving closer, you notice it is a huge creature, with the body of a lion and the head of a man.`n`n");
            output("You have found the Legendary `6Sphinx`3!  He bats around a huge boulder with one of his equally huge paws in boredom, as if it were nothing.  His large eyes examine you for a moment, before he speaks:`n`n");
            if ($session['user']['race']=="Felyne" && get_module_setting("favorfelyne")) {
            output("`6'Greetings, my friend. I can feel the strength that flows through our veins, that connects us! I will allow you to pass, and please take this offering!'`n`n");
            if ($goldchance >= $gemchance) {
            output("`3One of the Sphinx's huge paws opens, and within the palm is two `%gems`3!`n`n");
            $session['user']['gems']+=2;
            debuglog("got two gems from the Sphinx.");
            } elseif ($gemchance >= $goldchance) {
            output("`3One of the Sphinx's huge paws opens, and within the palm is a small leather bag!`n");
            output("You find `^%s`3 gold within!`n`n", $goldoffering);
            $session['user']['gold'] += $goldoffering;
            debuglog("got $goldoffering gold from the Sphinx.");
            }
            if ($session['user']['hitpoints'] < $session['user']['maxhitpoints']) {
            output("`6'Please take this as well, to ease your wounds.'`n");
            output("`3You notice a small flask on the ground.  Taking it, you drink it, and you are completely `@healed`3!`n`n");
		$session['user']['hitpoints']=$session['user']['maxhitpoints'];
	      }
            output("`3You take the Sphinx's offering, and wish him well before you leave on your travels.`0");
            $session['user']['specialinc'] = "";
            } else {
            output("`6'Greetings, fair warrior.  I am the great Sphinx, and you must challenge me if you wish to pass!'`n`n");
            output("`@'Challenge you?'`3  you ask.  `@'Do I not get a chance to answer a riddle first?'`n`n");
            output("`3The Sphinx chuckles.  `6'No, you do not. That tale is a load of rubbish, written and spread by the hands of men.  You have two choices, either fight me if you wish to pass, or run back whence you came!`0");
            addnav("The Sphinx");
            addnav("Challenge the Sphinx", $link."op=prefight");
            addnav("Run away", $link."op=runaway");
            }
            break;
            case "runaway":
            $session['user']['specialinc'] = "";
            require_once("lib/villagenav.php");
            villagenav();
            output("`3Not wanting to be on the receiving end of those huge paws, you cowardly make your way back to %s.`0", $fromvillage);
            break;
	      case "prefight":
            $op = "fight";
		httpset("op",$op);
		$badguy = array(
			"creaturename"=>"`6Sphinx`0",
			"creaturelevel"=>$session['user']['level']+2,
			"creatureweapon"=>"Huge Paws",
			"creatureattack"=>round($session['user']['attack']*1.25, 0),
			"creaturedefense"=>round($session['user']['defense']*1.1, 0),
			"creaturehealth"=>round($session['user']['maxhitpoints']*1.15, 0),
			"diddamage"=>0,
			"type"=>"sphinx"
		);
		$session['user']['badguy']=createstring($badguy);
            break;
            }
            if ($op == "fight"){
		$battle = true;
	      }
            if ($battle){
		include("battle.php");
		if ($victory){
                  output("`n");
			output("`3The Sphinx is worn out from the battle, and calls for the fighting to end.`n`n");
                  $expgained = round($session['user']['experience'] * ($takeexpamount / 100), 0);
                  if ($expgained < 1) {
                  output("");
                  }else{
                  output("`3You gain `^%s`3 experience from this fight!`n`n", $expgained);
                  $session['user']['experience'] += $expgained;
                  }
                  output("`6'Well done, warrior!  I haven't had a battle like that for a very long time!  Please take this as a memento of our battle!'`n`n");
                  if ($goldchance > $gemchance) {
                  output("`3One of the Sphinx's huge paws opens, and within the palm is a `%gem`3!`n`n");
                  $session['user']['gems']++;
                  debuglog("got a gem from the Sphinx.");
                  } else {
                  output("`3One of the Sphinx's huge paws opens, and within the palm is a small leather bag!`n");
                  output("You find `^%s`3 gold within!`n`n", $goldbattle);
                  $session['user']['gold'] += $goldbattle;
                  debuglog("got $goldbattle gold from the Sphinx.");
                  }
                  if ($session['user']['hitpoints'] < $session['user']['maxhitpoints']) {
                  output("`6'Please take this as well, to ease your wounds.'`n");
                  output("`3You notice a small flask on the ground.  Taking it, you drink it, and you are completely `@healed`3!`n`n");
		      $session['user']['hitpoints']=$session['user']['maxhitpoints'];
			}
                  output("`6'Farewell warrior!  May our paths cross again!'`3 the Sphinx says, as he moves out of the way. You continue along the trail...`0");
			$session['user']['specialinc'] = "";
		} elseif ($defeat) {
                  $session['user']['specialinc'] = "";
                  require_once("lib/villagenav.php");
                  villagenav();
                  $session['user']['hitpoints']=1;
                  output("`n");
			output("`3The Sphinx, knowing you are near death, calls for the fighting to end.`n`n");
					addnews("%s`^ has been found badly wounded on a road murmering something about a Sphinx...",$session['user']['name']);
                  if ($goldchance >= $gemchance && $session['user']['gems']>0) {
                  $gemsstolen = round($session['user']['gems'] * ($takegems / 100), 0);
                  if ($gemsstolen < 1) {
                  output("");
                  }else{
                  output("`3Being helpless on the ground, the Sphinx helps itself to `\$%s`3 of your `%gems`3!`n`n", $gemsstolen);
                  $session['user']['gems'] -= $gemsstolen;
                  debuglog("lost $gemsstolen gems to the Sphinx.");
                  }
                  }elseif ($gemchance >= $goldchance && $session['user']['gold']>0) {
                  $goldstolen = round($session['user']['gold'] * ($takegold / 100), 0);
                  if ($goldstolen < 1) {
                  output("");
                  }else{
                  output("`3Being helpless on the ground, the Sphinx helps itself to `\$%s`3 of your gold!`n`n", $goldstolen);
                  $session['user']['gold'] -= $goldstolen;
                  debuglog("lost $goldstolen gold to the Sphinx.");
                  }
                  }
                  if (get_module_setting("takeexp")) {
			$exploss = round($session['user']['experience'] * ($takeexpamount / 100), 0);
                  if ($exploss < 1) {
                  output("");
                  }else{
                  output("`3The Sphinx chants a spell in a long-forgotten language.  It takes `\$%s`3 of your experience away!`n`n", $exploss);
                  $session['user']['experience'] -= $exploss;
                  }
			}
			output("`6'I am victorious!  Ha ha ha!  You shall not pass!  Now leave!'`n`n");
			output("`3Not wanting to stay here any longer, you use what little strength you have left to limp your way back to %s.`0", $fromvillage);
		} else {
                  require_once("lib/fightnav.php");
			fightnav(true,false,$link);
		}


	}
}
function sphinx_run(){
}
?>
