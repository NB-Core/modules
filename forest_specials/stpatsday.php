<?php
// St Pat's special forest event
// A quest to find a Leprechaun's lost pot of Gold
// Original version by robert of maddnet for logd ver 097
// This version converted to .98 Prerelease 14 compatible by Talisman.
// March 16th, 2005

function stpatsday_getmoduleinfo(){
	$info = array(
		"name"=>"St. Patrick's Day Leprechaun",
		"version"=>"1.0",
		"author"=>"`2Robert - `4Talisman",
		"category"=>"Forest Specials",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1212",
		"settings"=>array(
		"St. Patrick's Day Leprechaun,title",
		"repeat"=>"Number of times to find,int|1",
		"minpot"=>"Minimum reward,int|950",
		"maxpot"=>"Maximum reward,int|2900",
		"mingems"=>"Minimum gem reward,int|2",
		"maxgems"=>"Maximum gem reward,int|5",
		"rungold"=>"Gold paid if Leprechaun runs,int|275",
		"rungems"=>"Gems paid if Leprechaun runs,int|2",
		),
		"prefs"=>array(
		"St. Patrick's Day Leprechaun User Prefs, title",
		"stage"=>"Stage of Quest,int|0",
		"done"=>"Number of times completed,int|0",
		)
	);
	return $info;
}

function stpatsday_install(){
	module_addeventhook("forest", "return 100;");
	return true;
}

function stpatsday_uninstall(){
	return true;
}

function stpatsday_runevent($type)
{
	global $session;
	$session['user']['specialinc'] = "module:stpatsday";
	$from = "forest.php?";
	$stage = get_module_pref("stage");
	
	switch(httpget('op')){

	case "":
		switch(e_rand(1,8)){
			case 1: case 2: case 3: case 5: case 6: case 7:
				if (($stage < 2) && (get_module_setting("repeat") > get_module_pref("done"))){
				addnav("the Pot o Gold");
				addnav("Return to forest","forest.php");
				}
				if ($stage == 0) {
					output("`n`n`2As you travel down a path, you stumble upon a `@young Leprechaun. ");
					output("`2He is feverishly looking upon the ground, seemingly searching for something.");
					output("`n`n`@The Leprechaun `2glances up at your approach, and says rather sadly,");
					output(" `6\"I've lost me `^Pot o' Gold! `6If the elders find out, my head they will have for sure!!\"");
					output("`n`n \"If you should happen across my `^Pot o' Gold`6 and return it, I'd be most grateful and grant ye a wish in return!\"");
					output("`n`n`n`& Your challenge is to find the Leprechaun's lost `^Pot o' Gold`& - if you find it, return it to the Leprechaun quickly! ");
					set_module_pref("stage",1);
					$session['user']['specialinc'] = "";

				}else if ($stage == 1) {
					output("`n`n`2 You come upon a Grassy Field. ");
			    	output("`n`n`2 While walking towards a shade tree to take a nap, you notice something under a shrub. ");
			    	output("`n`n Getting closer, you clearly see it is a `^Pot o' Gold `2, the one the young Leprechaun has lost. ");
			    	output("`n`n`& You must now return the `^Pot o' Gold &to the young Leprechaun in order to complete your quest! ");
					set_module_pref("stage",2);
					$session['user']['specialinc'] = "";
			
				}else if ($stage == 2) {
					output("`n`n`2 As you travel down a path, you encounter the young `@Leprechaun`2, who is quick to ask about his lost `^Pot o' Gold!`2");
					output("`n`n`2As you hand it to the `@Leprechaun, `2he says \"`6I promised to grant you a wish, so within me powers is the gift of gold or gem.  For what do you wish?\"");
					addnav("Gold", $from . "op=wish&choice=gold");
					addnav("Gems", $from . "op=wish&choice=gems");
					set_module_pref("stage",3);
					
				}else{
					output("`n`n`2 You take a well traveled path and hear some odd noises.  Thinking you might encounter the `@Leprechaun `2again, you look around.`n`n");
					output(" After a brief and fruitless search, you give up and return to the forest.");
					$session['user']['specialinc'] = "";
				}
				break;
				
			case 4: case 8:
				output("`n`n`2You enter a clearing in the forest, and catch sight of a little figure dressed in green just as he disappears through the undergrowth.");
				output("You would swear you just sighted a leprechaun, but remind yourself that they don't exist!");		
				$session['user']['specialinc'] = "";
				break;
	
			break;
		}
	break;
 		case "wish":
			$name=$session['user']['name'];
 			switch(e_rand(1,6)){
			 	case 1: case 6:

				 	$paygold = get_module_setting("rungold");
				 	$paygems = get_module_setting("rungems");
				 	output("`n`n`2The `@Leprechaun`2 bursts into laughter as he quickly runs out of your reach and into the nearby thickets.");
			 		output("He tauntingly shouts `6\"HA! So you thought you could get %s from a `@Leprechaun`6, you daft %s nin. Better luck next time!\"",httpget('choice'),$session['user']['race']);
			 		output("`n`n`2Once your anger calms, you notice the smug `@Leprechaun`2 was also somewhat clumsy, and dropped `^%s gold and %s gems.",$paygold,$paygems);
					addnews(" %s `2claimed %s gold and %s gems from a cheating `@Leprechaun.",$name,$paygold,$paygems);
		 	
					$session['user']['gold'] += $paygold;
					$session['user']['gems'] += $paygems;
					debuglog(" got $paygold gold and $paygems gems from St Patrick's Day Leprechaun`0");
			 		$session['user']['specialinc'] = "";
			 		break;

				case 2: case 3: case 4: case 5: 
					switch(httpget("choice")){
	
						case "gold":
							$mingold = get_module_setting("minpot");
							$maxgold = get_module_setting("maxpot");
							$pay = e_rand($mingold,$maxgold);
							$session['user']['gold'] += $pay;
							debuglog(" got $pay gold from St Patrick's Day Leprechaun`0");
							break;
						case "gems":
							$mingems = get_module_setting("mingems");
							$maxgems = get_module_setting("maxgems");
							$pay = e_rand($mingems,$maxgems);
							$session['user']['gems'] += $pay;
							debuglog(" got $pay gems from St Patrick's Day Leprechaun`0");
							break;
						}
					addnews(" %s `2 is greatly rewarded with %s %s for finding a lost `@Pot o Gold`2!",$name, $pay,httpget("choice"));
					output("`n`n`2The leprechaun smiles and says `6\"Well done my friend!  I am very happy to give you %s %s in reward for your assistance!\"",$pay,httpget("choice"));
					output("`n`2You put your newfound wealth away carefully as the `@Leprechaun`2 vanishes through the impenetrable underbrush.");
					break;
				}
			$done = get_module_pref("done");
			$done++;
			set_module_pref("done",$done);

			addnav("(R) Return to forest","forest.php");
			$session['user']['specialinc'] = "";
			break;
		}
}
?>
