<?php
function wc_trickortreat_getmoduleinfo(){
	$info = array(
			"name" => "Trick or Treat for Winter Castle, based on Haunted House",
			"author" => "`b`&Ka`6laza`&ar`b Incorporated, Edited, and Cloned by KainStrider ",
			"version" => "1.0",
			"download" => "",
			"category" => "Winter",
			"description" => "Monsters play Trick or Treat in the haunted house",
			"settings"=>array(
				"Trick or Treat Settings,title",
				"villagepercent"=>"percent chance of door appearing?,range,1,100,5|10",
				"staff1"=>"Name of first Staff member,int|Snowman",
				"staff2"=>"Name of second staff member,int|Snowwoman",
				"staff3"=>"Name of third Staff member,int|Pixie",
				"staff4"=>"Name of fourth staff member,int|Winter Ghost",
				"staff5"=>"Name of fifth Staff member,int|Mist Maiden",
				"staff6"=>"Name of sixth staff member,int|Fearless Frog",
				"staff7"=>"Name of seventh Staff member,int|Winter Witch",
				"staff8"=>"Name of eighth staff member,int|Snowperson",
				"staff9"=>"Name of ninth Staff member,int|Snow Neji",
				"weapon"=>"Attack of weapon,int|21",
				"gold"=>"How much gold does this staff member take?,int|100",
				"exp"=>"Experience Gain per ff,int|25",

				),
			"prefs"=>array(
					"Trick or Treat Prefs,title",
					"experience"=>"Experience Days left,int|",
				      ),
			);
	return $info;
}
function wc_trickortreat_install(){
	module_addhook("village-desc");
	module_addhook("village");
	module_addhook("battle-victory");
	return true;
}
function wc_trickortreat_uninstall(){
	return true;
}
function wc_trickortreat_dohook($hookname,$args){
	global $session;
	$op=httpget('op');
	$u=&$session['user'];
	switch($hookname){
		case "village-desc":
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
				//addnav("`b`4Hidden Door`b","runmodule.php?module=wc_trickortreat&op=enter");
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
function wc_trickortreat_run(){
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
		addnews("%s `^trick or treated in the `lW`Linter `)C`jastle!`T",$session['user']['name']);
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
		output("`4Frosty Kiss or Frosty Bite`b`T they say....");
		output("`n`nYou have the general idea that they will either mean you good or not so good, depending on their mood. After all, it's cold in here and you brought no `2tea`4.");
		output_notl("`n`n");
		output("Which will it be?");
		addnav("Frosty Kiss or Frosty Bite","runmodule.php?module=wc_trickortreat&op=kissbite&staff=$a");
	}
	if ($op=="kissbite"){
		$a=httpget("staff");
		$c=e_rand(1,2);
		if ($c==1){
			output("`c`b`^BITE!!`b`c`n`n");
			switch ($a){
				case 1:
					output("%s`T smiles at you before reaching over and tweaking something... In horror you realise you've just been turned into a `@Frozen Pinata`T and as such you're `4INCORPOREAL`T",$staff1);
					$u['alive']=0;
					$u['hitpoints']=0;
					addnav("Shades","shades.php");
					blocknav("village.php");
					page_footer();
					break;
				case 2:
					output("%s`T burps and scratches before hitting you over the head and carting your body away.  When you awaken you realise that you smell of something from behind the bar.. and your pockets are empty.",$staff2);
					$u['gold']=0;
					set_module_pref("drunkeness",50,"drinks");
					apply_buff('trick',array("name"=>"`lF`Lrostbite","rounds"=>25,"atkmod"=>0.5, "schema"=>"module-wctrick"));
					break;
				case 3:
					output("%s`T cackles wildly and promptly hits you with a `^Fl`Qam`&i`Qng T`^ro`&r`^Qch`T.  Just before you pass out, you hear more wild cackling and the something that sounds like your purse being emptied.",$staff3);
					$u['gold']=0;
					apply_buff('trick',array("name"=>"Wild Cackles","rounds"=>25,"atkmod"=>0.75,"defmod"=>0.75, "schema"=>"module-wctrick"));
					break;
				case 4:
					output("%s`T pulls a bag of quicklime from behind his back... as you stumble backwards you feel a trapdoor open, falling through you realise you're now in his castle.  A slightly ominous feeling comes over you, just before you feel a hand grab you by the scruff of the neck.",$staff4);
					output_notl("`n`n");
					output("The hand shoves you feet first into a Winter Cannon, and you can hear %s chuckling in the background as with a boom, you suddenly find yourself flying through the air... coming down in...",$staff4);
					addnav("The Bank","bank.php");
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
					output("Sighing in relief you think to yourself, how bad can this be after all %s`T is the nice one.. right?.. %s`T waggles a finger at you before leading your mount off to keep hers company whilst she puts her feet up for a lil while",$staff5,$staff5);
					if ($session['user']['hashorse']){
						if (isset($playermount['mountbuff']))
							$buff = unserialize($playermount['mountbuff']);
						else
							$buff = array();
						if (!isset($buff['schema']) || $buff['schema'] == "")
							$buff['schema']="mounts";
						strip_buff('mount',$buff);
					}
					break;
				case 6:
					output("%s`T smiles at you revealing a nice pair of `)T`einy `)T`eeth`T, swooping down to bite you... hard!!",$staff6);
					apply_buff('trick',array("name"=>"`lF`Lrosty `)T`eeeth","rounds"=>25,"atkmod"=>0.5, "schema"=>"module-wctrick"));
					break;
				case 7:
					output("%s`T drags you back to the door you came in through, your pants rip and are now around your ankles.. To your dismay the entire village see.",$staff7);
					addnews("%s`^ had their pants pulled down by %s`^ in the `lW`Linter `)C`jastle",$u['name'],$staff7);
					output("You feel somewhat less charming.");
					$u['charm']-=5;
					break;
				case 8:
					output("%s`T reaches over and gives you the biggest HUGGLE of your life... That wasn't so bad you think.. until you notice how weak and trembly you feel.",$staff8);
					apply_buff('trick',array("name"=>"`^Huggles of `lF`Lrostbite","rounds"=>20,"defmod"=>0.67, "schema"=>"module-wctrick"));
					break;
				case 9:
					output("%s`T puts you in a arm lock, just as your armor ges ripped from your body. `n`nTurning quickly he gulps it down.... Looking back at you he says..\"Not bad, needs Salt!\"",$staff9);
					$u['armor']="Loincloth!!!";
					//$b=$u['defense']-$u['armordef'];
					//$u['defense']=$b;
					$u['armorvalue']=0;
					break;
			}
		}else{
			output_notl("`b`c");
			output("`^KISS!!");
			output_notl("`b`c`n`n");
			switch ($a){
				case 1:
					output("%s`T reaches into a rough bag and passes you something.. wrapped in cloth..`n`nYou unwrap it slowly.. wondering what is inside.`n`nAs the last of the twine tieing it comes away.. you realise whats inside the cloth..`n`n A nice new weapon!`T",$staff1);
					$u['weapon']="The Gift";
					$b=$u['weapondmg'];
					$u['attack']=$u['attack']-=$b;
					$weapon=get_module_setting("weapon");
					$u['weapondmg']=$weapon;
					$u['attack']=$u['attack']+=$weapon;
					$u['weaponvalue']=0;
					break;
				case 2:
					output("%s`T burps and scratches before saying \"Kiss eh?\" and fumbling through some pockets.`n`nYou get handed a special Ale Glass",$staff2);
					apply_buff('kiss',array("name"=>"`^Ale `QGlass","rounds"=>25,"atkmod"=>1.2, "schema"=>"module-wctrick"));
					break;
				case 3:
					output("`%s`T cackles wildly before handing you your own `^Fl`Qam`&i`Qng T`^ro`&r`^Qch`T.",$staff3);
					$u['gold']=0;
					apply_buff('kiss',array("name"=>"`^Fl`Qam`&i`Qng T`^ro`&r`^Qch","rounds"=>25,"atkmod"=>1.2, "schema"=>"module-wctrick"));
					break;
				case 4:
					output("%s`T pulls a bag of quicklime from behind his back... and hands it to you... Looking slightly askance at him you wonder what to do with it...",$staff4);
					break;
				case 5:
					output("%s`T passes you a Tim Tam and a nice cup of coffee to keep you warm",$staff5);
					apply_buff('kiss',array("name"=>"`qTimTam High","rounds"=>25,"atkmod"=>1.35, "schema"=>"module-wctrick"));
					break;
				case 6:
					output("%s`T allows you to gaze upon some beauty...`n`nYou feel great",$staff6);
					apply_buff('kiss',array("name"=>"`lF`Lrosty `)Gaze `xof `QBeauty","rounds"=>25,"atkmod"=>1.5, "schema"=>"module-wctrick"));
					break;
				case 7:
					output("%s`T reaches over to a large pile of posters he has had specially made for winter.. `n`nWith a snap of his fingers he summons a winter pixie who runs off with the poster.`n`nSmiling %s`T shows you a giant poster of themself and explains that the pixie is at this moment hanging it in your house.`n`nYou certainly don't feel more charming, but %s`T happens to think they're the most charming around, so they gives you some anyways...",$staff7,$staff7,$staff7);
					$u['charm']+=10;
					break;
				case 8:
					output("%s`T huggles you.. and gives you the value of his experience.",$staff8);
					set_module_pref("experience",30);
					break;
				case 9:
					output("%s`T challenges you to an arm wrestle.`n`nSeeing your stricken look, they laugh and this gives you some of frosty protection.",$staff9);
					apply_buff('protect',array("name"=>"`lF`Lrostbuff","rounds"=>10,"defmod"=>2, "schema"=>"module-wctrick"));
					break;
			}
		}
		addnav("Exit the Room","runmodule.php?module=wintercastle&op=hauntedfloor2");
	}
	page_footer();
}
?>
