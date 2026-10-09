<?php
require_once('modules/datemanager.php');
function trickortreathh_getmoduleinfo(){
	$info = array(
			"name" => "Trick or Treat - Haunted House (timelocked)",
			"author" => "`b`&Ka`6laza`&ar`b Incorporated, Edited, and Cloned by KainStrider ",
			"version" => "1.0",
                        "download" => "EORPG",
                        "requires" => array(
                                "datemanager" => "1.0|By Oliver Brendel",
                        ),
			"category" => "Haunted House",
			"description" => "Monsters play Trick or Treat in the haunted house",
			"settings"=>array(
				"Trick or Treat Settings,title",
				"start"=>"Start Date MM-DD,string|10-29",
				"end"=>"End Date MM-DD,string|11-02",
				"villagepercent"=>"percent chance of door appearing?,range,1,100,5|10",
				"staff1"=>"Name of first Staff member,int|Vampire",
				"weapon"=>"Attack of weapon,int|20",
				"staff2"=>"Name of second staff member,int|Zombie",
				"staff3"=>"Name of third Staff member,int|Werewolf",
				"staff4"=>"Name of fourth staff member,int|Ghost",
				"gold"=>"How much gold does this staff member take?,int|100",
				"staff5"=>"Name of fifth Staff member,int|Basilisk",
				"staff6"=>"Name of sixth staff member,int|Minotaur",
				"staff7"=>"Name of seventh Staff member,int|Witch",
				"staff8"=>"Name of eighth staff member,int|Jason",
				"exp"=>"Experience Gain per ff,int|20",
				"staff9"=>"Name of ninth Staff member,int|Freddy Kruegar",
				),
			"prefs"=>array(
					"Trick or Treat Prefs,title",
					"experience"=>"Experience Days left,int|",
				      ),
			);
	return $info;
}
function trickortreathh_install(){
	module_addhook("village-desc");
	module_addhook("village");
	module_addhook("battle-victory");
	return true;
}
function trickortreathh_uninstall(){
	return true;
}
function trickortreathh_dohook($hookname,$args){
	global $session;
	$op=httpget('op');
	$u=&$session['user'];
        $start = get_module_setting("start");
        $end = get_module_setting("end");
        $dateCheck = datemanager_datecheck($start, $end, 0);

        if ($dateCheck <= 0) {
                return $args;
        }

        $timeRemaining = datemanager_get_time_remaining($start, $end, 0);
	switch($hookname){
                case "village-desc":
                        static $countdownDisplayed = false;

                        if (!$countdownDisplayed && $timeRemaining instanceof DateInterval) {
                                $countdownDisplayed = true;
                                $countdown = datemanager_format_countdown($timeRemaining);

                                output_notl("`n");
                                output("`qThe Haunted House will close in %s.`0", $countdown);
                        }

                        if (e_rand(1, 100) <= get_module_setting("villagepercent")) {
                                output_notl("`n");
                                output("`b`c`QA strange door has appeared in the centre of the village.`b`c`n");
                                $args['dotrick'] = 1;
                        }
		case "village":
			if (!array_key_exists("dotrick",$args)) $args['dotrick'] = false;
			if ($args['dotrick']) {
				tlschema($args['schemas']['gatenav']);
				addnav($args["gatenav"]);
				tlschema();
				addnav("`b`4Hidden Door`b","runmodule.php?module=trickortreathh&op=knockdoor");
			}
			break;
		case "battle-victory":
			$exp=get_module_setting("exp");
			$staff8=get_module_setting("staff8");
			if(get_module_pref("experience")>0 && $args['type']=="forest"){
				output_notl("`n");
				output("`QYou receive a bonus of %s experience, compliments of %s",$exp,$staff8);
				output_notl("`n");
				$u['experience']+=$exp;
				$new=get_module_pref("experience")-1;
				set_module_pref("experience",$new);
			}
			break;
	}
	return $args;
}
function trickortreathh_run(){
	global $session;
	$u=&$session['user'];
	page_header("Trick or Treat!");
	$op=httpget('op');
	$staff1=get_module_setting("staff1");
	$staff2=get_module_setting("staff2");
	$staff3=get_module_setting("staff3");
	$staff4=get_module_setting("staff4");
	$staff5=get_module_setting("staff5");
	$staff6=get_module_setting("staff6");
	$staff7=get_module_setting("staff7");
	$staff8=get_module_setting("staff8");
	$staff9=get_module_setting("staff9");
	if ($op=="knockdoor") {
		addnews("%s `^trick or treated in the `5Haunted House!`0",$session['user']['name']);
		output("You push open the door to find yourself standing in front of ");
		switch(e_rand(1,9)){
			case 1:
				output("%s",$staff1);
				$a=1;
				break;
			case 2:
				output("%s",$staff2);
				$a=2;
				break;
			case 3:
				output("%s",$staff3);
				$a=3;
				break;
			case 4:
				output("%s",$staff4);
				$a=4;
				break;
			case 5:
				output("%s",$staff5);
				$a=5;
				break;
			case 6:
				output("%s",$staff6);
				$a=6;
				break;
			case 7:
				output("%s",$staff7);
				$a=7;
				break;
			case 8:
				output("%s",$staff8);
				$a=8;
				break;
			case 9:
				output("%s",$staff9);
				$a=9;
				break;
		}
		output_notl("`n`n`b");
		output("`4Trick or Treat`b`0 they say....");
		output_notl("`n`n");
		output("Which will it be?");
		addnav("Trick or Treat","runmodule.php?module=trickortreathh&op=trickortreat&staff=$a");
	}
	if ($op=="trickortreat"){
		$a=httpget("staff");
		$c=e_rand(1,2);
		if ($c==1){
			output("`c`b`^TRICK!!`b`c`n`n");
			switch ($a){
				case 1:
					output("%s`0 smiles at you before reaching over and tweaking something... In horror you realise you've just been turned into a `@Pinata`0 and as such you're `4DEAD!`0",$staff1);
					$u['alive']=0;
					$u['hitpoints']=0;
					addnav("Shades","shades.php");
					blocknav("village.php");
					page_footer();
					break;
				case 2:
					output("%s`0 burps and scratches before hitting you over the head and carting your body away.  When you awaken you realise that you smell of something from behind Cedriks bar.. and your pockets are empty",$staff2);
					$u['gold']=0;
					set_module_pref("drunkeness",50,"drinks");
					apply_buff('trick',array("name"=>"Trickery","rounds"=>25,"atkmod"=>0.5, "schema"=>"module-trick"));
					break;
				case 3:
					output("%s`0 cackles wildly and promptly hits you with a `^Fl`Qam`&i`Qng B`^roo`&m`^Sti`Qck`0.  Just before you pass out, you hear more wild cackling and the something that sounds like gold clinking",$staff3);
					$u['gold']=0;
					apply_buff('trick',array("name"=>"Wild Cackles","rounds"=>25,"atkmod"=>0.5, "schema"=>"module-trick"));
					break;
				case 4:
					output("%s pulls a bag of quicklime from behind his back... as you stumble backwards you feel a trapdoor open, falling through you realise you're now in his house.  A slightly ominous feeling comes over you, just before you feel a hand grab you by the scruff of the neck.",$staff4);
					output_notl("`n`n");
					output("The hand shoves you feet first into a Pirate Cannon, and you can hear %s chuckling in the background as with a boom, you suddenly find yourself flying through the air... coming down in...",$staff4);
					addnav("Ye Olde Bank","bank.php");
					$gold=get_module_setting("gold");
					if ($u['goldinbank']>$gold){
						$u['goldinbank']=$u['goldinbank']-=$gold;
					}else{
						$u['goldinbank']=0;
					}
					blocknav("village.php");
					page_footer();
					break;
				case 5:
					output("Sighing in relief you think to yourself, how bad can this be after all %s`0 is the nice one.. right?.. %s`0 waggles a finger at you before leading your mount off to keep hers company whilst she puts her feet up for a lil while",$staff5,$staff5);
					if ($session['user']['hashorse']){
						$buff = '';
						if (isset($playermount['mountbuff']))
							$buff = unserialize($playermount['mountbuff']);
						if ((!isset($buff['schema']) || $buff['schema'] == "") && is_array($buff)) {
							$buff['schema']="mounts";
						}
						strip_buff('mount',$buff);
					}
					break;
				case 6:
					output("%s`0 smiles at you revealing her `)Vampiric Teeth`0 swooping down she bites you.. Hard!!",$staff6);
					apply_buff('trick',array("name"=>"`)Vampire Bite","rounds"=>25,"atkmod"=>0.5, "schema"=>"module-trick"));
					break;
				case 7:
					output("%s`0 drags you back to the door you came in through, just as he tosses you bodily through it, he rips your pants down around your ankles.. To your dismay the entire village see.",$staff7);
					addnews("%s`^ Had their Pants pulled down by %s`^ in the Village",$u['name'],$staff7);
					output("  You feel somewhat less charming");
					$u['charm']-=5;
					break;
				case 8:
					output("%s reaches over and gives you the biggest HUGGLE of your life... That wasn't so bad you think.. until you notice how weak and trembly you feel",$staff8);
					apply_buff('trick',array("name"=>"`^Huggles of Death","rounds"=>25,"atkmod"=>0.5, "schema"=>"module-trick"));
					break;
				case 9:
					output("%s`0 puts you in a arm lock, just as he strips your armor from your body.  Turning quickly he gulps it down.... Looking back at you he says..\"Not bad, Needs Salt!\"",$staff9);
					$u['armor']="They ate It!!";
					//$b=$u['defense']-$u['armordef'];
					//$u['defense']=$b;
					$u['armorvalue']=0;
					break;
			}
		}else{
			output_notl("`b`c");
			output("`^TREAT!!");
			output_notl("`b`c`n`n");
			switch ($a){
				case 1:
					output("%s`0 reaches into their Bag 'O Tricks and passes you something.. wrapped in cloth.. You unwrap it slowly.. wondering what is inside.  As the last of the twine tieing it comes away.. you realise whats inside the cloth.. A nice new weapon`0",$staff1);
					$u['weapon']="The Gift";
					$b=$u['weapondmg'];
					$u['attack']=$u['attack']-=$b;
					$weapon=get_module_setting("weapon");
					$u['weapondmg']=$weapon;
					$u['attack']=$u['attack']+=$weapon;
					$u['weaponvalue']=0;
					break;
				case 2:
					output("%s`0 burps and scratches before saying \"Treat eh?\" and fumbling through his pockets.  He hands you a special Ale Glass",$staff2);
					apply_buff('treat',array("name"=>"`^Ale `QGlass","rounds"=>25,"atkmod"=>1.2, "schema"=>"module-trick"));
					break;
				case 3:
					output("`%s`0 cackles wildly before handing you your own `^Fl`Qam`&i`Qng B`^roo`&m`^Sti`Qck`0.",$staff3);
					$u['gold']=0;
					apply_buff('treat',array("name"=>"`^Fl`Qam`&i`Qng B`^roo`&m`^Sti`Qck","rounds"=>25,"atkmod"=>1.2, "schema"=>"module-trick"));
					break;
				case 4:
					output("%s`0 pulls a bag of quicklime from behind his back... and hands it to you... Looking slightly askance at him you wonder what to do with it...",$staff4);
					break;
				case 5:
					output("%s`0 passes you a Tim Tam and a nice cup of coffee to keep you warm",$staff5);
					apply_buff('treat',array("name"=>"`qTimTam High","rounds"=>25,"atkmod"=>1.3, "schema"=>"module-trick"));
					break;
				case 6:
					output("%s`0 allows you to gaze upon her beauty... You feel great",$staff6);
					apply_buff('treat',array("name"=>"Gaze of Beauty","rounds"=>25,"atkmod"=>1.5, "schema"=>"module-trick"));
					break;
				case 7:
					output("%s`0 reaches over to a large pile of posters he has had specially made for Halloween.. With a snap of his fingers he summons a goblin who runs off with the poster.  Smiling %s`0 shows you a giant poster of themself and explains that the goblin is at this moment hanging it in your house.  You certainly don't feel more charming, but %s`0 happens to think they're the most charming around, so they gives you some anyways...",$staff7,$staff7,$staff7);
					$u['charm']+=10;
					break;
				case 8:
					output("%s`0 Huggles you.. and gives you the value of his experience",$staff8);
					set_module_pref("experience",30);
					break;
				case 9:
					output("%s`0 Challenges you to an arm wrestle.. Seeing your stricken look, they laugh and gives you some of protection",$staff9);
					apply_buff('protect',array("name"=>"Protection","rounds"=>25,"invulnerabile"=>1, "schema"=>"module-trick"));
					break;
			}
		}
		addnav("Exit the Room","runmodule.php?module=hauntedhouse&op=hauntedfloor2");
	}
	page_footer();
}
?>
