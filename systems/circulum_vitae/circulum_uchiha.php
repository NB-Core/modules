<?php


function circulum_uchiha_getmoduleinfo(){
	$info = array(
	    "name"=>"Circulum Vitae - Uchiha Ichizoku",
		"description"=>"Uchiha Clan",
		"version"=>"1.0",
		"author"=>"`4Oliver Brendel`0",
		"category"=>"Circulum Vitae",
		"download"=>"",
		"settings"=>array(
			"Circulum Vitae  - Preferences,title",
				"maxstack"=>"How many times can this be stacked?,range,1,10,1|4",
			),		
		"prefs"=>array(
		    "Circulum Vitae - User prefs,title",
				"stack"=>"Number of CVs the player has chosen this benefit,int|0",
				"dkgot"=>"What DK did he last get the b00n,viewonly",
			),
		"requires"=>array(
			"circulum"=>"1.0|Circulum Vitae by `2Oliver Brendel",
			),
		);
    return $info;
}

function circulum_uchiha_install() {
	module_addhook("circulum-items");
	module_addhook("biostat");
	module_addhook("circulum-chosen");
	module_addhook("traininggrounds");
	module_addhook("newday");
	return true;
}

function circulum_uchiha_uninstall() {
  return true;
}


function circulum_uchiha_dohook($hookname, $args) {
	global $session;
	static $run;
	$stack=(int)get_module_pref('stack','circulum_uchiha');
	$isactive=($stack>0?true:false);
	$doujutsu=(int)get_module_pref('stack','circulum_hyuuga');
	if ($stack<get_module_setting('maxstack') && $doujutsu<1) {
		$canbechosen=true;
	} else $canbechosen=false;
	switch($hookname) {
		case "newday":
			if ($isactive) {
				$dk=get_module_pref('dkgot');
				if ($session['user']['dragonkills']!=$dk) {
					set_module_pref('dkgot',$session['user']['dragonkills']);
					require_once('lib/increment_specialty.php');
					for ($i=0;$i<5;$i++) {					
						increment_specialty('`$');
					}
				}
			}
			break;
			
		case "biostat":
			$stack=(int)get_module_pref('stack','circulum_uchiha',$args['acctid']);
			
			if ($stack==0) break;
			$ranks=array (
				'`$Initiate',
				'`$Honoured Member',
				'`$M`4aster',
				'`$G`4rand `$M`4aster',
				);
			if ($stack>count($ranks)) $stack=count($ranks);
			output("`xBorn as %s`x of the `\$Uchiha Ichizoku`0`n",$ranks[$stack-1]);
			break;
		case "circulum-items":
			$args[]=array(
				'modulename'=>'circulum_uchiha',
				'nav'=>translate_inline('Uchiha Ichizoku'),
				'category'=>'Doujutsu',
				'exclusive_in_category'=>true,
				'description'=>
					sprintf_translate("`lYou are born as `xUchiha`l, which means your body is carrying a unique Kekkei Genkai: The ability to perform the `\$S`4haringan`l, which enforces your vision to see through walls, bodies and barriers, and most importantly: other jutsus. Along with that comes the gift of seen the others chakra flow which enables you to do various things. The more often you choose this, the more powerful you will get.`n`nBenefits:`n`\$-None in normal play.`\$`n-Specialty \"Uchiha Techniques\" is available. This one is stronger than a normal jutsu and comparable kekkei genkai sets.`n`\$-Specialty \"Fire\" is available to you with 5 use points every day at least. `n`n`%This can be stacked up to %s times! You have chosen this already %s times. Every stacking makes the Jutsus stronger! Note that the 万華鏡写輪眼, Mangekyō Sharingan is first accessible having chosen this two times!",
						get_module_setting('maxstack'),
						get_module_pref('stack'),
						translate_inline(($doujutsu>0?"`n`n`c`b`\$You have already chosen another dōjutsu, you cannot choose this one!`b`c":"`n`n`c`b`\$You cannot choose this AND another dōjutsu!`b`c"))
						),
				'active'=>1,
				);
			break;
		case "circulum-chosen":
			if ($args['chosen']==='circulum_uchiha') {
				increment_module_pref('stack',1);
			}
			break;
		case "traininggrounds":
			if (!$isactive) break;
			addnav("Go to Sasuke","runmodule.php?module=circulum_uchiha");
			break;
	}
	return $args;
}

function circulum_uchiha_run()	{
	global $session;
	page_header("Training Grounds");
	addnav("Navigation");
	addnav("Back to the Academy","train.php");
	addnav("Actions");
	output("`#`b`c`n`\$Uchiha`x Training`0`c`b`n`n");
	$op = httpget('op');
	switch ($op) {
		case "chat":
			$heard=array(
				'I have heard there is a war against the Mist coming up... unsettling...',
				'Naruto should be headed for me right now, he will never get me...',
				'Sakura must have cut her hair again... Hinata should be her example as hers is long and healthy.',
				'Chouji has started to eat Chakra. He says it is good for the liver. Poor boy, that won\'t reduce his weight.',
				'Lately, there have been no news.',
				'Somebody was in search for the ink required for the Seven Star Tattoo... interesting thingie... but a kid could paint it better...',
				'Orochimaru has no idea of style... at all...',
				'Ino? Well, at least she knows how to wear her hair...',
				'If Rock Lee would learn how to use chakra... hilarious...',
				'The Sound Five? Well, Orochimaru simply had not enough people to form a \'Dirty Dozen\'.',
				'Mitarashi Anko... she is cute when she is asleep... ups...',
				'No, your underwear has no style at all.',
				'Jiraiya... beneath the surface, there lies a sensible and lonely man...',
				'Shino... well, we are somewhat alike. Except for the bugs and the silence...',
				);
			$heard=translate_inline($heard);
			$choice=array_rand($heard);
			output("`\$Sasuke`x thinks a bit and then says, \"`4%s`x\"",$heard[$choice]);
			break;
		default:
		output("`xYour master `\$U`4chiha `%Sasuke`x is here. What do you want to talk to him about?`n`n");
		addnav("Idle Chitchat","runmodule.php?module=circulum_uchiha&op=chat");
		break;
	}
	page_footer();
}

?>
