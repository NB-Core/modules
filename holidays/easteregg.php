<?php

function easteregg_getmoduleinfo(){
	$info = array(
		"name"=>"Easteregg",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Holidays|Easter Holiday",
		"settings"=>array(
			"Easteregg - Settings,title",
			"x"=>"How many columns?,int|6",
			"y"=>"How many rows?,int|4",
			"tries"=>"How many maxtries?,int|3",
		),
		"prefs"=>array(
			"easteregg - Prefs,title",
				"egg"=>"Where is the egg?,int|0",
				"usermap"=>"Map for the user,text|",
				"tries"=>"How many tries had this user?,int|0",
		),
	);
	return $info;
}
function easteregg_install(){
	//date hardcoded, because it will be set on install/reinstall of module
debug("days left: ".datedifference_events("04-14",true));
debug((datedifference_events('04-14',true)<14?100:0));
	module_addeventhook("forest","return (datedifference_events('04-14',true)<14?100:0);");
	return true;
}
function easteregg_uninstall(){
	return true;
}
function easteregg_runevent($type){
	global $session;
	$op = httpget('op');
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:easteregg";
	$amount = 0;
	//debug("Egg is at item:".get_module_pref("egg"));
	output_notl("`n");
	switch ($op){
		case "":
			output("`@Suddenly... as you wander through the forest on the lookout for enemies to defeat... you are stumbling and find yourself lying on green grass... directly in front of you is a somewhat strange guy... he is wearing `4B`%unny `4E`%ars`@!.`n`n");
			output("He speaks to you, \"`4Hey there! Want to try to find the precious egg I have hidden here? If you find it, I'll reward your skill!`@\".`n`n");
			output("What do you do...");
			addnav("Accept the offer",$from."op=grab");
			addnav("Walk away",$from."op=walk");
			set_module_pref("usermap",'');
			break;
		case "walk":
			output("`)You walk away... leaving that fancy `4B`%unny`@ behind.`n`n");
			$session['user']['specialinc']='';
			break;
		case "grab":
			output("`@\"`4I see, we have an agreement there... okay, try to find the egg! You have 3 tries!`@\".`n`n");
			output_notl("`l");
			$map=easteregg_generate();
			set_module_pref("usermap",$map);
			set_module_pref("tries",0);
			easteregg_display($map);
			$x=get_module_setting("x");
			$y=get_module_setting("y");
			set_module_pref("egg",e_rand(1,$x*$y));
			addnav("Leave",$from."op=leave");
			break;
		case "find":
			$tries=(int)get_module_pref('tries');
			$maxtries=(int)get_module_setting('tries');
			output("`@After a short while of searching....`n`n");
			addnav("Navigation");
			if (httpget('item')==get_module_pref("egg")) {
				output("`qYou find the fancy painted `vE`laster `vE`lgg`q!`n`n");
				output("`@The strange `4B`%unny`@ gasps as you show it to him... and he sighs as he says, \"`4Ahh... I never thought you'd search `bTHERE`b... well, here is your reward...NOTHING! HaHaHa... April's Fool... that must be `byou`b... HaHaHa...`@\"... and then he hops away.... gnarf... no honest people in the world!`n`n");
				$res = e_rand(0,2);
				switch ($res) {
					case 0:
						output("`qHmmm... but this egg looks nice... somebody has made quite an effort to it... after reaching the edge of the forest, you sell it and get quite some money!`n`n");
						$amount=e_rand(100,$session['user']['level']*100+100);
						output("`qYou receive `^%s gold`q!",$amount);
						$session['user']['gold']+=$amount;
						break;
					case 1:
						output("`qHmmm... but this egg looks nice... somebody has made quite an effort to it... he even placed `%A GEM `q on it!`n`n");
						output("`qYou receive `%one gem`q!",$amount);
						$session['user']['gems']+=1;
					case 2:
						output("`qHmmm... but this egg looks nice... somebody has made quite an effort to it... it is `\$RED`q and `)RARE`q ... you should take it to Eggbert if he is in town!`n`n");
						$itemid = 201;
						require_once("modules/inventory/lib/itemhandler.php");
						$items = add_item_by_id($itemid,1);
				}
				addnav("Continue",$from."op=continue");
			} else {
				$what=array("`2roots","`told `vsocks","`gearthworms","`~mud");
				$what=translate_inline($what);
				$random=e_rand(0,count($what)-1);
				output("`qNothing except for some %s`q.`n`n",$what[$random]);
				$tries++;
				set_module_pref('tries',$tries);
				if ($tries<$maxtries) {
					if ($maxtries-$tries>1) output("`@You have `^%s `@more tries!`n`n",$maxtries-$tries);
						else output("`@You have `^one `@more try!`n`n");
					easteregg_display(get_module_pref("usermap"));
				} else {
					output("`@You have had all tries :( sad, but you can't do a thing... that fancy `4B`%unny `4E`%ar`@ has prevailed... you can only return in shame now...");
				}
				addnav("Leave",$from."op=leave");
			}

			break;
		case "leave":
			output("`@You continue on your journey... leaving this ridiculous stuff behind`n`n");
			$session['user']['specialinc']='';
			break;
		case "continue":
			output("`@You continue gladly on your journey, having beaten that imprudent `4B`%unny `4E`%ar...");
			$session['user']['specialinc']='';
			break;

	}
}

function easteregg_generate() {
	$parts=array("Green Grass","Tree","Small Flowers","Bush");
	$count=count($parts)-1;
	// map be 6:4
	$x=get_module_setting("x");
	$y=get_module_setting("y");
	$map=array();
	for ($a=1;$a<=$y;$a++) {
		for ($b=1;$b<=$x;$b++) {
			$map[]=e_rand(0,$count);		
		}		
	}
	return implode(",",$map);
}

function easteregg_display($map) {
	$parts=array("Green Grass","Tree","Small Flowers","Bush");
	$parts=translate_inline($parts);
	rawoutput("<table border=0 cellspacing=0 cellpadding=2 width='100%' align='center'>");
	rawoutput("<tr>");
	$map=explode(",",$map);
	$x=get_module_setting("x");
	$y=get_module_setting("y");
	for ($a=1;$a<=$y;$a++) {
		rawoutput("<tr align='center'>");
		for ($b=1;$b<=$x;$b++) {
			rawoutput("<td align='center'><a href='forest.php?op=find&item=".(($a-1)*6+$b)."'>".$parts[$map[($a-1)*6+$b-1]]."</a></td>");
			addnav("","forest.php?op=find&item=".(($a-1)*6+$b));
		}	
		rawoutput("</tr>");
	}
	rawoutput("</table>");
}
	
?>
