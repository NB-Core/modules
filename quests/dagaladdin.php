<?php
/************************************************************************************
Name => Aladdin's Lamp
Author => Lightbringer
Version => 1.0
Shout-outs => Big shout-out to Sneakabout as I am using his quest mod as the backbone
Future Additions - A few more features will be implemented
                   *Additional admin settable features (currently commented out)
                   *The possibility of a range of completely configurable quests
                   *Other features whenever I get a chance
************************************************************************************/

//Requires - necessary for ensuring functionality of the module

require_once("lib/http.php");
require_once("lib/villagenav.php");

//Module Information - this is the info about the module - eg Module name, author, version etc

function dagaladdin_getmoduleinfo(){
//Declaring the array and storing it as $info
	$info = array(
		"name"=>"Aladdin's Lamp",
		"version"=>"1.1",
		"author"=>"Lightbringer - based on Dag's Quests by Sneakabout, debugged by DaveS",
		"category"=>"Quest",
		"download"=>"http://dragonprime.net/users/Lightbringer/aladdin.zip",
		//Module Settings - listed in an array and parsed with the return $info at the end
		"settings"=>array(
			"Aladdin's Lamp Settings,title",
			//"lampname"=>"What is the name of the quest item?,|Aladdin's Lamp",
			//"lampmindk"=>"What is the minimum DK requirement for this quest?,int|1",
			//"lampmaxdk"=>"What is the maximum DK requirement for this quest?,int|5",
			"lampminlevel"=>"What is the minimum level for this quest?,range,1,15|8",
			"lampmaxlevel"=>"What is the maximum level for this quest?,range,1,15|14",
			"lampexp"=>"What is the quest experience multiplier for Lamp Quest?,floatrange,1.01,1.1,0.01|1.04",
			"rewardgold"=>"What is the gold reward for the quest?,int|1000",
			"rewardgems"=>"What is the gem reward for the quest?,int|2",
			"monster"=>"Monstername,text|Djinn",
			"monsterweapon"=>"Monsterweapon,text|The elements",
		),
		//Now here is where we set the prefs
		"prefs"=>array(
			"aladdinstatus"=>"How far in the Aladdin's Lamp quest has the player got?,int|0",
        ),
		"requires"=>array(
			"dagquests"=>"1.1|By Sneakabout",
		),
	);
//Now we need to return the string '$info'
	return $info;
}

//This is where the quest is installed - and the module hooks added
function dagaladdin_install(){
	module_addhook("village");
    module_addhook("dragonkilltext");
    module_addhook("newday");
	module_addhook("dagquests");
	return true;
}

//This is pretty self-explanatory
function dagaladdin_uninstall(){
	return true;
}

//The do hooks - all of the hooks that have been added in the install are dealt with here
function dagaladdin_dohook($hookname,$args){
	global $session;
	switch ($hookname) {

//module_addhook("village");
	case "village":
		if ($session['user']['location']==getsetting("villagename", LOCATION_FIELDS)) {
			tlschema($args['schemas']['gatenav']);
			addnav($args['gatenav']);
			tlschema();
			if (get_module_pref("aladdinstatus")==1 && $session['user']['turns'] >= 1) addnav("Search the ruins!","runmodule.php?module=dagaladdin&op=search");
		}
    break;
    case "dragonkilltext":
		set_module_pref("aladdinstatus",0);
    break;



//module_addhook("newday");
    case "newday":
//Check for player dragonkills cross-referencing admin settings...
        if (get_module_pref("aladdinstatus")==1 &&
			$session['user']['level']>get_module_setting("lampmaxlevel")) {
			set_module_pref("aladdinstatus",4);
			output("`n`6You hear that another adventurer has recovered the artifact.`0`n");

		}
		break;

//module_addhook("dagquests");
    case "dagquests":
		$op=httpget("op");
		$module=httpget("module");
//Here is where the user enquires about the quest/s
    case "enquire":
		if ($args['questoffer']) break;
		$lvlmin=get_module_setting("lampminlevel");
		$lvlmax=get_module_setting("lampmaxlevel");
//This is the user asking about the job - handy for sense of involvement
//Conditions checked for $lvlmin and in this case failing
		if ($lvlmin<=$session['user']['level'] && $session['user']['level']<=$lvlmax && !get_module_pref("aladdinstatus")) {
			output("He seems very busy, but when you ask him about the possibility of work, he studies you carefully and nods slightly.`n`n");
			output("\"Aye, there be something ye might be helpin' me wit'....Rumour 'as it that there be a wondrous artifact lyin' around in some old ruins....I would be much obliged if'n ye could fetch it for me...There would be a rather sizable reward...So will ye be takin' me up on the job? \"`n`n");
			output("You carefully consider his proposition. The reward clinches it...");
//Success! The user can progress on the quest!
			addnav("Accept the Quest","runmodule.php?module=dagaladdin&op=accept");
			$args['questoffer']=1;
//Just in case
			addnav("Refuse the Quest","inn.php");
		}
	break;


	break;
	}
//Parse the do hooks
        return $args;
}

//Use this if you need to...
function dagaladdin_runevent($type) {
}

//Here is the nitty gritty of the entire quest module - this is where it all goes down - first is the acknowledgement of the quest
function dagaladdin_run(){
//Lets make the session global
	global $session;
//Declare an operation
    $op = httpget('op');
//Declare that there are multiple ops which require further info to continue - hence the cases
	switch($op){
//Progress the user to the first stage of the quest
	case "accept":
//Once again, we need to retrieve the inn information
        $iname = getsetting("innname", LOCATION_INN);
		page_header($iname);
		rawoutput("<span style='color: #9900FF'>");
		output_notl("`c`b");
		output($iname);
		output_notl("`b`c");
//Dag tells the user a little about the quest
        output("`3Dag nods, and points you in the direction of where the priceless artifact was last seen.");
		output("You leave the table, ready to seek out the item.");
//Set the user's pref 'aladdinstatus' to 1
        set_module_pref("aladdinstatus",1);
//Let em out ;)
        addnav("I?Return to the Inn","inn.php");
		rawoutput("</span>");
		break;

//Searching for the lamp
        case "search":
//Title the page 'The Ruins'
        page_header("The Ruins");
//User needs to know what the hell is going on ;)
        output("`2You hike up to the ruins and scour them for any trace of the artifact.`n`n");
		$session['user']['turns']--;
//There is multiple events for this area - they need to be randomised
        $rand=e_rand(1,10);
		switch($rand){
//The first two cases are pretty much non-events
        case 1:
        case 2:
			output("You search through the ruins for a while, finding nothing but dust and rubble.");
			output("Dispirited after a few hours, you trudge back to the town and look for something else to do.");
			villagenav();
			break;
//Following two cases are an injured traveller
        case 3:
		case 4:
			output("You investigate the ruins for any sign of the artifact. In the process you espy something glittering in a dark recess...");
			output("You rush over, and find a few gold pieces lying on the ground.");
            $gold=e_rand(100,200);
            debuglog("gained $gold gold from ancient ruins");
            $session['user']['gold']+=$gold;
			villagenav();
			break;
//This one is a 'gain charm' event
        case 5:
			output("Scouring the ruins for the artifact, you find a small goblet sitting on a shelf.");
			output("You tentatively drink from it...");
			output("A feeling of pure, unadulterated passion pervades your very being as you feel your charm being raised by the goblet's contents!'");
			$charm=e_rand(1,3);
			debuglog("gained $charm charm from some ruins");
			$session['user']['charm']+=$charm;
			villagenav();
			break;
//A 'lose attack' event
            case 6:
			output("You wander into the ruins and notice a pile of rocks with something glinting from within a gap...");
			output("Curious, you approach and move a couple of rocks to investigate fully!");
			output("Your heart pounds in your chest as you realise the the glinting belongs to a decidedly poisonous looking asp. Before you can respond - it lunges at you and bites down hard - you feel the poison course through your veins!");
			debuglog("lost 1 attack from the asp in the ruins");
			$session['user']['attack']--;
			villagenav();
			break;
//Djinn fight (dealt with later)
        case 7:
		case 8:
		case 9:
		case 10:
			output("You investigate the ruins for any sign of the artifact.");
			output("Luckily for you you find it.....unluckily for you - it is not alone. A being made up of all the elements grins at you maliciously");
			output("The creature laughs and beckons you towards him, and you realise he won't give up the artifact without a fight - you draw your %s`2 and charge as the Djinn prepares some kind of spell, snarling all the while.",$session['user']['weapon']);
			addnav("Fight the Djinn!","runmodule.php?module=dagaladdin&fight=djinnfight");
			break;
		}
		break;
	}
//Now for setting up the aforementioned fight
    $fight=httpget("fight");
	switch($fight){
//Djinn fight
    case "djinnfight":
//Setting up the Djinn's abilities, fighting prowess, etc
         $badguy = array(
		 "creaturename"=>get_module_setting('monster'),
		 "creaturelevel"=>$session['user']['level']+2,
		 "creatureweapon"=>get_module_setting('monsterweapon'),
		 "creatureattack"=>round($session['user']['attack']*1.17, 0),
		 "creaturedefense"=>round($session['user']['defense']*1.1, 0),
		 "creaturehealth"=>round($session['user']['maxhitpoints']*1.2, 0),
		 "diddamage"=>0,
		 "type"=>"quest"
		);
//Djinn buffs follow
/*            apply_buff('elementblast',array(
			"name"=>"`\$Elemental Blast",
			"roundmsg"=>"The Djinn summons forth the four elements to rain down on you!",
			"effectmsg"=>"You are hit by one of the elemental rays for `4{damage}`) points!",
			"effectnodmgmsg"=>"You dodge one of the rays!",
			"rounds"=>20,
			"wearoff"=>"The Djinn's power is depleted'!",
			"minioncount"=>4,
			"maxgoodguydamage"=>$session['user']['level'],
			"schema"=>"dagaladdin"
		));
  */      $session['user']['badguy']=createstring($badguy);
		$battle=true;
// drop through
	case "djinnfighting":
//Djinn fight venue
        page_header("The Ruins");
//Let the game know there should be a fight here
        require_once("lib/fightnav.php");
		include("battle.php");
//Effects of victory
        if ($victory) {
		output("`2The Djinn explodes in a dazzling display of Earth, Wind, Fire and Water!");
		output("You have attained the artifact!");
//Rewards
		$expgain=round($session['user']['experience']*(get_module_setting("lampexp")-1), 0);
		$session['user']['experience']+=$expgain;
		output("`n`n`&You gain %s experience from locating the artifact!",$expgain);
        $goldgain=get_module_setting("rewardgold");
		$gemgain=get_module_setting("rewardgems");
		$goldgain=e_rand($goldgain/2,$goldgain);
		$gemgain=e_rand($gemgain/2,$gemgain);
		$session['user']['gold']+=$goldgain;
		$session['user']['gems']+=$gemgain;
		debuglog("got a reward of $goldgain gold and $gemgain gems for retrieving the artifact.");
		if ($goldgain && $gemgain) {
		output("`2You return to the Inn carrying the artifact, and Dag pays you the reward of `^%s gold`2 and a pouch of `%%s %s`2!",$goldgain,$gemgain,translate_inline(($gemgain==1)?"gems":"gem"));
		} elseif ($gemgain) {
		output("`2You return to the Inn carrying the artifact, and Dag pays you the reward of a pouch of `%%s %s`2!",$gemgain,translate_inline(($gemgain==1)?"gems":"gem"));
		} elseif ($goldgain) {
		output("`2You return to the Inn carrying the artifact, and Dag pays you the reward of `^%s gold`2!",$goldgain);
		} else {
		output("`2You return to the Inn carrying the artifact, but Dag cannot find the reward to pay you!");
		}
//Set the user's quest status to 2
        set_module_pref("aladdinstatus",2);
		addnews("%s defeated the Djinn in the Ruins! The legendary artifact has been recovered!",$session['user']['name']);
        villagenav();
//The Djinn loses its buffs
        strip_buff("elementblast");
//Effects of defeat
        } elseif ($defeat) {
		output("`2Your life ebbs away as the Djinn lands the killing blow.");
		output("You have failed your task to retrieve the artifact!");
		output("`n`n`%You have died! You lose 10% of your experience, and your gold is stolen by the Djinn!");
		output("Your soul drifts to the shades.");
		debuglog("was killed by a Djinn in the ruins ".$session['user']['gold']." gold.");
		$session['user']['gold']=0;
		$session['user']['experience']*=0.9;
		$session['user']['alive'] = false;
//Set the user's quest status to 3
        //set_module_pref("aladdinstatus",3);
		addnews("%s was slain in search of an ancient artifact!",
		$session['user']['name']);
		addnav("Return to the News","news.php");
//Djinn loses the buff
        strip_buff("elementblast");
        } else {
		fightnav(true,true,"runmodule.php?module=dagaladdin&fight=djinnfighting");
		}
		break;
	}
	page_footer();
}
?>
