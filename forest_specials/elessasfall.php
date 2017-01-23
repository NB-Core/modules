<?php
/*
written for Elessa

*/
function elessasfall_getmoduleinfo() {
	$info = array(
		"name"=>"Elessas Waterfall",
		"author"=>"`@Elessa, technical assistant: `4Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Forest Specials",
		"download"=>"http://lotgd-downloads.com",
		"settings"=>array(
		"Elessas Fall - Preferences, title",
		"This module is gender-sensitive,note",
		"maxcharm"=>"PC charm value for the best result,int|100",
		),
	);
	return $info;
}

function elessasfall_install() {
	module_addeventhook("forest", "return 100;");
	if (is_module_active("elessasfall")) output("`c`bModule Elessas Fall updated`b`c`n`n");
	return true;
}

function elessasfall_uninstall() {
	return true;
}

function elessasfall_dohook($hookname,$args) {
	return $args;
}

function elessasfall_runevent($type,$link) {
 global $session;
 $from = "forest.php?";
 $session['user']['specialinc'] = "module:elessasfall";
 $op = httpget('op');
 $attracted_to=$session['user']['prefs']['sexuality'];
 $attracted   =($attracted_to==SEX_MALE?0:1);
 
 switch ($op) {
  case "":
	output("`@You come across a slightly overgrown path leading off the main trail.");
	if($session['user']['race']== 'Elf') {
		output("`@Your elvish senses tell you that something special and important closes in.");
	} elseif ($session['user']['race']== 'Troll') {
		output("`@Your trollish nose picks ups some sweet and slightly familiar scent.");
	} elseif ($session['user']['race']== 'Dwarf') {
		output("`@Your dwarven blood seems to boil... somehow you know that something will happen... and you won't like it..");
	}
	output("`n`nDo you want to follow the path?");
	addnav("Investigate",$link."op=investigate");
	addnav("Walk away",$link."op=walk");
	break;
	case "investigate":
		output("`n`n`@You follow it as it leads through some dense overgrowth of trees and bracken.");
		output("`n`nThere before you is a beautiful elven maiden.");
		output("`n");
		output("She has just dropped from her shoulders a veil of sheer green to reveal her fair bare skin.");
		output("You realize that she is about to step into the clear cascading waters of a waterfall.");
		if ($attracted) {
			output("`n`nThe beauty of the maiden is tempting your heart.");
		} else {
			output("`n`nThe beauty of the maiden is making you somehow jealous...");
		}
		output("`n`nWhat will you do?");
		addnav("Gaze",$link."op=gaze");
		if ($attracted) {
			addnav("Try to talk to her",$link."op=talk");
		} else {
			addnav("Try to provoke",$link."op=provoke");	
		} 
		addnav("Try to run away",$link."op=run");  
	break;
	
	
	case "gaze":
	output("`@You try to take a deeper look... well, anything might happen now.");
	 if (!$session['user']['sex']) {
		output("`n`nHehe... you can't wait to see her taking off her clothes.");
	} else {
		output("`n`nYou think she might have only little breasts... or really ugly orange skin underneath the clothes.");
		output("You just have to check that for your own satisfaction.");
	}  
	output("`n`nThe elven maiden drops her veil... and enters the waterfall.");
	output("`nYou are quite astonished... pale yet soft skin (unbelievable you can feel that from the distance)...");
	output(" as she begins to splash water upon her body, she begins to laugh.");
	output("The innocent laughter is resonating in your heart... while your eyes can't look away...");
	$randomchance=e_rand(1,5);
	 if($session['user']['race']== 'Elf') {
		$randomchance+=2;
	 } elseif ($session['user']['race']== 'Troll') {
		$randomchance-=2;
	} elseif ($session['user']['race']== 'Dwarf') {
		$randomchance--;
	}
	if ($randomchance<1) $randomchance=1;
	if ($randomchance>5) $randomchance=5;
	switch ($randomchance)
	{
	 case 1:
		output("`n`n`@She is aware of your presence... but feels no need to hide herself.`n");
		if ($attracted) {
			output("Instead, her frank look catches your eyes ... and she gives you a blow-kiss.`n`n");
			output("You feel good... well... no... you feel `%GREAT`@!");
			$session['user']['hitpoints']+=$session['user']['hitpoints']*0.2;
		} else {
			output("Instead, she offers you a friendly smile... like a childhoodfriend would.`n`n");
			output("Your jealousy is wiped away... the only thing left is a `%GREAT`@ warm feeling in your heart.");
		}
		output("`nYou `^gain`@ a `%charm`@ point!");
		$session['user']['charm']++;		
		break;
	case 2: case 3:
		output("`@She doesn't seem to be aware of your presence... you watch some time, but then decide that there is nothing to gain from just watching and leave.");
		break;
	case 4:
		output("`@Oh no! She has somehow detected your presence and blazes angrily at you!`n");
		output(" Your confidence is wiped away as she invokes a chant that gives you the creeps...");
		output("`n`nYou ran away as fast as you can, but feel her chant haunting you...");
		apply_buff('hauntingcharm',
			array(
				"name"=>"`)Elvish Haunting Charm",
				"rounds"=>30,
				"wearoff"=>"Your ego defeats the charm.",
				"atkmod"=>0.8,
				"defmod"=>0.8,
				"minioncount"=>1,
				"roundmsg"=>"You feel the elven charm haunting you!",
				"schema"=>"module-elessasfall",
			));
		break;
	case 5:
		output("`@Oh no! She has somehow detected your presence and blazes angrily at you!`n");
		output(" You can't get away... somehow the plants have entangled you as she shouted a charm!");
		output(" She puts on her clothes while you try to get away... but the plants are stronger`n`n");
		$sex=($session['user']['sex']?translate_inline("girl"):translate_inline("boy"));
		$sexgrownup=($session['user']['sex']?translate_inline("woman"):translate_inline("man"));
		$sexiness=($session['user']['sex']?translate_inline("womanliness"):translate_inline("manliness"));
		output("'`4Well, well... a little %s is here... hasn't your mommy told you not to peek?",$sex);
		output(" If you'd be a real %s... I might severly punish you... but you're still young and have to learn.`@'",$sexgrownup);
		if ($session['user']['gold']>0)	{
			output("'`4 I'll take your gold as a lesson, now get out of my sight, little %s.`@'",$sex);
			output("`n`nYou have `$ lost`@ all your gold and some %s.",$sexiness);
			$session['user']['gold']=0;
			$session['user']['charm']--;
			} else {
			output("'`4 You don't even have money to pay for my disgrace. Go... now...'`@");
			output("`n`nYou feel very depressed... you `$ lose`@ two charm points");
			$session['user']['charm']-=2;
			}
		break;
		}
		$session['user']['specialinc'] = "";
		break;
	case "provoke":
		output("You step out of your hideout and start to call her names.");
		output("She seems to be surprised to see you here out of the blue`n`n");
		$charmmax=get_module_setting("maxcharm");
		$charcurrent=round(($session['user']['charm']*10)/(2*$charmmax));
		if($session['user']['race']== 'Elf') {
			$charcurrent+=2;
		} elseif ($session['user']['race']== 'Troll') {
			$charcurrent-=2;
		} elseif ($session['user']['race']== 'Dwarf') {
			$charcurrent--;
		}
		if ($charcurrent<1) $charcurrent=1;
		if ($charcurrent>5) $charcurrent=5;		
		switch ($charcurrent)
		{
		case 5:
			output("`n`n`@Suddenly, she seems to be sad. You know that you're quite woman.`n");
			output("She hastily fetches her clothes and leaves the place... with tears in her eyes...");
			output("You feel very pleased...");
			output("`nYou `^gain`@ a `%charm`@ point!");
			$session['user']['charm']++;
			break;
		case 4: case 3:
			output("`@She doesn't seem to be impressed by your appearence. She fetches her clothes and leaves the fall.");
			break;
		case 2:
			output("`@Oh no! The look in her eyes let you grow quiet!");
			output(" Your confidence is wiped away as she invokes a chant that gives you the creeps...");
			output("`n`nYou ran away as fast as you can, but feel her chant haunting you...");
			apply_buff('hauntingcharm2',
				array(
					"name"=>"`)Elvish Haunting Charm",
					"rounds"=>30,
					"wearoff"=>"Your ego defeats the charm.",
					"atkmod"=>0.7,
					"defmod"=>0.7,
					"minioncount"=>1,
					"roundmsg"=>"You feel the elven charm haunting you!",
					"schema"=>"module-elessasfall",
				));
			break;
		case 1:
			output("`@You step out of your hideout, but she already seems to have detected you.");
			output(" As you utter the first nasty words, a word from her silences you completely.");
			output("`n`nYou can't get away... somehow the plants have entangled you as she shouted the charm!");
			output(" She puts on her clothes while you try to get away... but the plants are stronger`n`n");
			output("'`4Well, well... little girl, your mommy hasn't told you not to insult womans?");
			output("You will pay me dearly for this!`@'`n`n");
			if ($session['user']['gold']>0 && $session['user']['gems']>0) {
					output("'`4 I'll take your gold and one gem as a lesson, now get out of my sight, little girl.`@'");

					output("`n`nYou have `$ lost`@ all your gold and one gem.");
					$session['user']['gold']=0;			
					$session['user']['gems']--;
					addnews("%s`0 was stripped of all gold for insulting an elven maiden in the woods!",$session['user']['name']);			
				} elseif ($session['user']['gold']>0) {
					output("'`4 I'll take your gold as a lesson, now get out of my sight, little girl.`@'");
					output("`n`nYou have `$ lost`@ all your gold and some womanliness.");
					$session['user']['gold']=0;
					$session['user']['charm']--;
					addnews("%s`0 was stripped of all gold for insulting an elven maiden in the woods!",$session['user']['name']);
				} else {
					output("'`4You don't even have money nor gems to pay for my disgrace. Go... now...`@'");
					output("`n`nYou feel very depressed... you `$ lose`@ two charm points");
					$session['user']['charm']-=2;
				}
			break;
			}
		$session['user']['specialinc'] = "";			
		break;
	case "talk":
 		output("`@You try to walk casually towards the fall, trying to give the best impression of you.");
		output("She seems to be surprised to see you here out of the blue`n`n");
		$charmmax=get_module_setting("maxcharm");
		$charcurrent=round(($session['user']['charm']*10)/(2*$charmmax));
		if($session['user']['race']== 'Elf') {
			$charcurrent+=2;
		} elseif ($session['user']['race']== 'Troll') {
			$charcurrent-=2;
		} elseif ($session['user']['race']== 'Dwarf') {
			$charcurrent--;
		}
		if ($charcurrent<1) $charcurrent=1;
		if ($charcurrent>5) $charcurrent=5;
		switch ($charcurrent)
		{
		case 5:
			output("`n`n`@Suddenly, she starts to glow. She says, '`4You're quite something to look at...`@'`n");
			output("She doesn't make a move to hide herself... and you start to talk to her casually.");
			output("After a few hours - nobody except you knows what happened - you leave the place happier than before.`n`n");
			output("You feel very `^energized`@ and `$ very `%charming`@...");
			$session['user']['charm']+=3;
			apply_buff('elvishmaiden',
				array(
					"name"=>"`)Elven Maiden",
					"rounds"=>40,
					"wearoff"=>"The memories fade away...",
					"regen"=>$session['user']['level'],
					"defmod"=>1.2,
					"minioncount"=>1,
					"roundmsg"=>"You remember the elven maiden!",
					"schema"=>"module-elessasfall"
				));			
			addnews("%s`0 spent a few hours of... conversation... with an elven maiden in the woods!",$session['user']['name']);			
			break;
		case 4: case 3:
			output("`@She doesn't seem to be impressed by your appearence. She fetches her clothes and leaves the fall.");
			break;
		case 2:
			output("`@Oh no! Her look let you grow quiet!`n");
			output(" '`4 Uhh... look at you ... *yuck* how long have you been there... *eek*`@'`n`n");
			output(" Your confidence is wiped away as she invokes a chant that gives you the creeps...");
			output("`n`nYou ran away as fast as you can, but feel her chant haunting you...");
			apply_buff('disgustingcharm',
				array(
					"name"=>"`)Elvish Disgusting Charm",
					"rounds"=>20,
					"wearoff"=>"Your ego defeats the charm.",
					"atkmod"=>0.5,
					"defmod"=>0.5,
					"minioncount"=>1,
					"roundmsg"=>"You feel the elven charm haunting you!",
					"schema"=>"module-elessasfall",
				));
			break;
		case 1:
			output("`@You try to impress her... and let your flies wide open... as well as fix your hair with some spit.`n");
			output("As you approach her, she apparently is shocked by your appearence!`n");
			output(" \"`4 Uhh... look at you ... *yuck* you're `$ UGLY `4 unbelievable... how long have you been there... *eek*`@\"`n`n");
			output("She obviously tries not to puke... as she grabs her clothes and runs away.");
			output("`nYou don't care about that really... and turn around.");
			output("`n`nBehind you stands a large elf... larger than any elf you've ever seen.");
			output("`n\"`2 Well... you have some guts doing something like that... a lesson is not enough for you...");
			output(" you need some real punishment!\"`@");
			output("`n`nFaster than you can look... you are entangled... and see the elf checking your equipment!`n`n");
			$check=0;
			if (is_module_active('alignmentbasedweapon')) $check=(int)(get_module_pref('hasweapon','alignmentbasedweapon'));
			if (is_module_active('circulum')) {
				$get=(int)get_module_pref('circuli','circulum');
				if ($get>0) $check=1;
			}
			if ($session['user']['weapondmg']>0 && $session['user']['weapondmg']!=0 &&!$check)	{
				output("\"`2 I will render your weapon useless, a playboy like you doesn't have the need to fight!`@\"");
				$session['user']['attack']-=$session['user']['weapondmg'];
				$session['user']['weapon']=translate_inline("Metal parts");
				debuglog("turned user weapon into metal parts for peeping at an elven maiden");
				$session['user']['weapondmg']=0;
				$session['user']['weaponvalue']=0;
				} elseif ($session['user']['armordef']>0) {
					output("\"`2 Since your weapon is not existent, I will render your armor useless, a playboy should be used to pain!`@\"");
					$session['user']['defense']-=$session['user']['armordef'];
					$session['user']['armor']=translate_inline("Rags");
					debuglog("turned user armour into rags for peeping at an elven maiden");
					$session['user']['armordef']=0;
					$session['user']['armorvalue']=0;
				} else {
					output("\"`2 You call yourself a warrior? You're disgusting!`@\"");
					if ($session['user']['maxhitpoints']>1) {
						$session['user']['maxhitpoints']--;
						$losthp=1;
					}
				}
				output("`nWith these words, he beats you to a pulp...");
				if ($losthp=1) {
					output("`n`n You lose `$ one permanent`@ hitpoint!");
					debuglog("lost 1 hp permantent for peeping at an elven maiden");
				}
				$session['user']['hitpoints']=1;
				addnews("%s`0 was beaten to a pulp for peeking at an elven maiden in the woods!",$session['user']['name']);
			break;
		}
		$session['user']['specialinc'] = "";			
		break;	
	case "walk":
		output("You simply ignore the path and continue your search for monsters");
		$session['user']['specialinc'] = "";
		break;
	
	case "run":
		output("`@You decide that it is safer to run away.");
		$stumble=e_rand(1,2);
		if ($stumble==1) {
			output("`n`nOh my, you're really clumsy. You ran with full speed but you tripped... and suffered injury from the fall.`n");
			$hploss=e_rand(1,$session['user']['maxhitpoints']/5);
			$hptext=($hploss==1? translate_inline("point"):translate_inline("points"));
			output("You `$ lose`@ %s hit%s!",$hploss,$hptext);
			$session['user']['hitpoints']-=$hploss;
			if ($session['user']['hitpoints']<=0) {
				$session['user']['hitpoints']=1;
				output("`n`n`@You are at the brink of death... but somebody... the maiden?... has come to your aid.");
				output(" Your senses drift away... and you find yourself bandaged lying on a green meadow.");
				output(" You feel that she must be the one who saved you from death... ");
				output(" `n`n You feel `%charming`@ but `$ lose`@ a forest fight.");
				$session['user']['charm']++;
				$session['user']['turns']--;
				}
			}
	$session['user']['specialinc'] = "";
	break;
 
 }

}

function elessasfall_run(){
}

?>
