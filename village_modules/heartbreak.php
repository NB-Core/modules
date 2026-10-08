<?php

function heartbreak_getmoduleinfo(){
	$info = array(
		"name"=>"The Ghost of Valentine",
		"version"=>"1.0",
		"author"=>"Oliver Brendel",
		"category"=>"Village Specials",
		"download"=>"",
		"settings"=>array(
			"The Ghost -- Settings,title",
			"heartbreakloc"=>"Where does he/she appear,location|".getsetting("villagename", LOCATION_FIELDS)
		)
	);
	return $info;
}

function heartbreak_install(){
	module_addhook("changesetting");
	module_addeventhook("village",
			"require_once(\"modules/heartbreak.php\"); return heartbreak_test();");
	return true;
}

function heartbreak_uninstall(){
	return true;
}

function heartbreak_dohook($hookname,$args){
	global $session;
	switch($hookname){
   	case "changesetting":
		if ($args['setting'] == "villagename") {
			if ($args['old'] == get_module_setting("heartbreakloc")) {
				set_module_setting("heartbreakloc", $args['new']);
			}
		}
		break;
	}
	return $args;
}

function heartbreak_test(){
	global $session;
	if ($session['user']['location'] ==
			get_module_setting("heartbreakloc","heartbreak")) {
		$canappear = 1;
	}else{
		$canappear = 0;
	}
	$canappear=1;// for the time being
	$chance=($canappear?100:0);
	return $chance;
}

function heartbreak_runevent($type) {
	global $session;
   	$session['user']['specialinc'] = "";
	$from = "village.php?";
	$city = get_module_setting("heartbreakloc");
	$op = httpget('op');

	require_once("lib/partner.php");
	$partner = get_partner();

	if ($op == "") {
		$session['user']['specialinc'] = "module:heartbreak";
		output("`7While you're exploring %s`7, you see a small cute girl roaming the streets.`n`n",$city);
		output("\"`&If you feel dumbstruck`n
unable to control what you do`n
looking at yourself like a watcher from outside`n
thinking about the same things over and over again`n
chance is, your heart might have been stolen!`n`7\" a female voice sings.`n`n");
		output("You are not quite sure what to do...");
		addnav("Actions");
		if ($session['user']['gold']>0) {
			addnav("Treat (give her 1 gold)",$from."op=treat");
			output("You could give her some gold... `n`n");
		}elseif ($session['user']['gold']==0) {
			output("You don't have any gold to give her...`n`n");
		}
		output("You could also risk poking her for that possibly stupid song...");
		output("What will you do?");
		addnav("Whack her",$from."op=trick");
		addnav("Ignore her",$from."op=ignore");
	}elseif($op=="ignore"){
		output("`7You're really not in the mood to give in to some rant on the streets by some girl, so you turn your back on her and walk away.`n`n");
	}elseif($op=="trick"){
		output("`7You're in the right mood to show her what kind of stupid stuff she sings. You give her a really nice beating.`n`n");
		output("After all, how bad can some little kid be?`n`n");
		$bad=e_rand(1,5);
		switch ($bad) {

			case 5:
			output("You really feel good about that..`n");
			heartbreak_align(-2,-1);
			if ($session['user']['charm']>1) {
				$session['user']['charm']-=2;
				output("`7You `\$lose `7charm but hone your evil self!");
			}
			break;
			case 4:	
			output("The beaten kid seems strangely unharmed...`7\"`4Sad for you, but it got returned by mail yesterday. Now... oh, I feel really straaaange....`7\" ... are the last words before you get puked on A LOT.");
			// Aww heck, let's have the buff survive new day.
			heartbreak_align(-1,-1);
			apply_buff('heartbreak',
				array(
					"name"=>"`@Valentine Puke",
					"rounds"=>60,
					"wearoff"=>"The stench begins to fade.",
					"defmod"=>0.9,
					"survivenewday"=>1,
					"roundmsg"=>"The stench of foul puke makes it hard to concentrate on your defense.",
					)
			);
			break;
			case 3:
			output("Well, now you've done it. She cries and a lot of folks gather around while you take your leave rapidly, not wanting to get surrounded by crazy townfolk.");
			output("`n`nHowever, something hits you in the back....");
			output("`\$You're paralyzed!`n`n");
			output("`7You are frozen helplessly on the spot as hands rifle through your purse.");
			heartbreak_align(-1,1);
			$takegold=e_rand(200,$session['user']['level']*50);
			$takegems=ceil(($session['user']['level']+1)/3);
			if ($session['user']['gold']==0 && $session['user']['gems']==0){
				output("Somebody grunts in disgust at finding it empty.");
			}elseif ($session['user']['gold']>$takegold){
				output("Somebody helps him/herself to `^%s gold`7.`n",$takegold);
				$session['user']['gold']=$session['user']['gold']-$takegold;
			}elseif($session['user']['gold']>0){
				output("Somebody helps him/herself to `^all your gold`7.`n");
				$session['user']['gold']=0;
			}
			if($session['user']['gems']>$takegems){
				output("`7He/she also takes `5%s gems `7before wandering away.`n",$takegems);
				$session['user']['gems']-=$takegems;
			}elseif($session['user']['gems']>1){
				output("`7He/she also takes `5all your gems `7before wandering away.`n");
				$session['user']['gems']=0;
			}elseif($session['user']['gems']==1){
				output("`7He/she also takes `5your only gem `7before wandering away.`n");
				$session['user']['gems']=0;
			}
			debug("Lost $takegold gold and $takegems gems to the heartbreaker kid.");
			output("`nAfter a few minutes you are able to begin painfully shifting your aching muscles again.`n");
			break;

			default:
			output("Nobody sees you, and you walk away, chuckling very evilly.");
			output("If only %s `7could see you now.`n`n",$partner);
			output("`7You `\$lose `7charm!`n");
			heartbreak_align(-3,1);
			if ($session['user']['charm'] > 0)
				$session['user']['charm']--;
		}
	}elseif($op=="treat"){
		output("`7You're in the mood for being nice, so you hand over a piece of gold.`n`n");
		$session['user']['gold']--;
		$outcome=e_rand(0,5);
		heartbreak_align(3);
		switch($outcome) {
			case 5:
				output("The girl thanks you for being so nice and walks happily away, holding that precious gold piece in her hands.");	
				apply_buff('heartbreak',
					array(
						"name"=>"`#Girl's Gratefulness",
						"rounds"=>60,
						"wearoff"=>"You float back down to earth.",
						"atkmod"=>1.03,
						"survivenewday"=>1,
						"roundmsg"=>"Your good mood helps you hit harder.",
						)
				);
				break;
			case 4:
				output("Somehow, it seems there is no girl. Somehow, it seems you have accidentally awakened the evil ghost of Valentine!`n`n");
				output("\"`3Thank you for giving me that gold, now I'll help myself to something more... precious....`7\"... are the last words you hear before the world goes dark... `\$You died!");
				$session['user']['hitpoints']=0;
				$session['user']['alive']=0;
				addnews("%s`x had a broken heart and vanished into nothingness...",$session['user']['name']);
				addnav("Shades");
				addnav("To the shades","shades.php");
				break;
			case 3:
				output("You chuckle at yourself, as she tells you an extra verse:`n
\"`&it is like a river`n
strong currents, slow currents`n
washing away most obstacles`n
though mostly flowing in only one direction...`n
`7\"");
				output("`n`nSadly, you know that feeling and agree.");
				break;
			case 2:
				output("You chuckle at yourself, as she tells you an extra verse:`n
\"`&if you want reconcile`n
you have to steal one yourself`n
from the one that took yours`n
only then you will be even!`n
`7\"");
				output("`n`nRefreshingly, you know that feeling and agree.");
				apply_buff('heartbreak',
					array(
						"name"=>"`#Heart Steal",
						"rounds"=>30,
						"wearoff"=>"You float back down to earth.",
						"atkmod"=>1.09,
						"survivenewday"=>1,
						"roundmsg"=>"Your good mood helps you hit harder.",
						)
				);
				break;
			case 1:
				output("You chuckle at yourself, as she tells you a poem:`n");
				$poem = heartbreak_get_random_poem();
				output_notl("`x".$poem);
		/*		
output("
\"`&
Dusk in the north`n
splendid white trails`n
left behind on splendid white snow`n
`n
So pure and so cold`n
so bright and so distant`n
so shining and so untouchable.`n
`n
Wind engulfs you every step you take`n
you leave your own trail`n
in the pure bright and shining white.`n
`n
You're alone.`n
`n
The horizon calls you forth`n
out of your warm cave`n
away from those around you.`n
`n
Something tells you what awaits you`n
though to you, it feels unreal`n
though your steps continue.`n
`n
You don't want to be alone.`n
`n
Touching it,`n
feeling it,`n
embracing it.`n
`n
The white cold around feels warm`n
caressing your skin`n
cooling it down.`n
`n
You know you're in danger`n
but you cannot step back`n
you must go on.`n
`n
The stone on your chest feels heavy`n
separates your mind from your body`n
becoming more beast than man inside.`n
`n
How absurd your goal might be`n
How far away it may seem`n
How useless the struggle appears.`n
`n
You will walk that way.`n
`n
Driven by that instinct`n
forcing you to keep going`n
leaving your humanity behind.`n
`n
The more so`n
The closer you get`n
to that splendid pure bright white feeling.`n
`n
There is now a new trail in the snow.`n
`7\"");
*/
				output("`n`nRefreshingly, you can't make much sense out of it but agree.");
				apply_buff('heartbreak',
					array(
						"name"=>"`#Heart Steel",
						"rounds"=>30,
						"wearoff"=>"You float back down to earth.",
						"defmod"=>1.10,
						"survivenewday"=>1,
						"roundmsg"=>"Your good mood helps you defend yourself harder.",
						)
				);
				break;
			
			default:
				output("She thanks you very nicely and presses a small little stone in your hand before she runs off. It is a nice gesture the very least.");
				output("`n`nAs she walks away, she says, \"`%You always change, when somebody like that enters your life, regardless of the outcome, don't you?`7\"");
				break;
		}
				
	}
}

function heartbreak_get_random_poem() {
	$files = opendir("modules/heartbreak");
	$poems=array();
	while (false !== ($entry = readdir($files))) {
		if ($entry=="." || $entry=="..") continue;
		$poems[]=$entry;
	}
	if ($poems==array()) return "Lily is white`nRosy is red`nAnd this is my song!";
	$random = array_rand($poems);
	debug($random);
	debug($poems[$random]);
	$filename=($poems[$random]);
	$poem = file_get_contents("modules/heartbreak/".$filename);
	debug($poem);
	return str_replace(array("\n"),array("`n"),$poem);
}

function heartbreak_align($align=0,$dem=0) {
	if (is_module_active('alignment')) {
		require_once("./modules/alignment/func.php");
		align($align);
		demeanor($dem);
	}
}
