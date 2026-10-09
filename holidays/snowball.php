<?php

require_once("lib/villagenav.php");
require_once("lib/http.php");
require_once("lib/systemmail.php");

function snowball_getmoduleinfo(){
	$info = array(
			"name"=>"Snowball in the gardens (timelocked)",
			"version"=>"1.0",
			"author"=>"`LShinobiIceSlayer",
			"category"=>"Holidays|Christmas",
			"download"=>"",
			"settings"=>array(
				"Snowball - Settings,title",
				"snowballlimit"=>"How many Snowballs a user can throw a day,int|3",
				"start"=>"Activation start date (mm-dd)|12-1",
				"end"=>"Activation end date (mm-dd)|12-31",
				"leniency"=>"How many days after the end date the event is active,int|5",
				),
			"prefs"=>array(
				"Snowball - User Preferences,title",
				"throwstoday"=>"Has the player visited today?,int|0",
				),
			"requires"=>array(
				"datemanager"=>"1.0|By Oliver Brendel",
				)
		     );
	return $info;
}

function snowball_install(){
        module_addhook("footer-gardens");
        module_addhook("village-desc");
        module_addhook("gardens-desc");
        module_addhook("newday");
        return true;
}

function snowball_uninstall(){
	return true;
}

function snowball_dohook($hookname,$args){
	global $session;

        switch($hookname){
                case "footer-gardens":
                        addnav("Winter");
                        addnav("Snowy Banks","runmodule.php?module=snowball");
                        break;
                case "village-desc":
                case "gardens-desc":
                        require_once("modules/datemanager.php");
                        $start = get_module_setting("start");
                        $end = get_module_setting("end");
                        $leniency = get_module_setting("leniency");
                        $date_check = datemanager_datecheck($start, $end, $leniency);

                        if ($date_check === 1 || $date_check === 2) {
                                $remaining = datemanager_get_time_remaining($start, $end, $leniency);

                                if ($remaining !== null) {
                                        $countdown = datemanager_format_countdown($remaining);

                                        if ($date_check === 2) {
                                                output("`n`&The snowball fight is winding down in the gardens.`7 There's `^%s`7 of frosty fun left before the thaw.`n", $countdown);
                                        } else {
                                                output("`n`&Snowball fights rage in the gardens!`7 There's still `^%s`7 of snowy fun remaining.`n", $countdown);
                                        }
                                }
                        }
                        break;
                case "newday":
                        set_module_pref("throwstoday",0);
                        break;
        }
	return $args;
}

function snowball_run() {
	global $session;
	require_once("modules/datemanager.php");
	$start = get_module_setting("start");
	$end = get_module_setting("end");
	$leniency = get_module_setting("leniency");
	$date_check = datemanager_datecheck($start, $end,$leniency);
	debug("Start: ".$start." End: ".$end);
	debug("Datecheck: ".$date_check);

	page_header("Snowy Banks");
	output("`&`c`bThe Snowy Banks!`b`c");

	if ($date_check==0) {
		// Not the right time of year, means the snowy banks are not snowy or closed
		output("`7`nYou walk towards the snowy banks, yet you find that the snow has melted away, leaving nothing but a cold, wet ground.`n`n");
		output("`7Maybe you should come back later when the snow has returned.`n`n");
		output("`nYou turn and return to the Gardens.`n`n");
		addnav("G?Return to the Gardens","gardens.php");
		page_footer();
	} elseif ($date_check==-1) {
		// Error in the date manager
		output("`7`nYou walk towards the snowy banks, yet you find that the snow has melted away, leaving nothing but a cold, wet ground.`n`n");
		output("`7Maybe you should come back later when the snow has returned.`n`n");
		output("`nYou turn and return to the Gardens.`n`n");
		addnav("G?Return to the Gardens","gardens.php");
		page_footer();
	} elseif ($date_check==2) {
		// The date is in the leniency range
		output("`7`nYou watch the spectacular fireworks, and know, soon the snowy fun will be over.`n`n");
	}

	$op=httpget('op');
	$throwstoday=get_module_pref("throwstoday");
	$limit=get_module_setting("snowballlimit");
	$name = stripslashes(rawurldecode(httppost('target')));


	if($throwstoday>=$limit){
		output("`7`nYou walk towards the snowy banks, but you are much too cold so you cannot even bear to place your hands in the frozen snow any longer.");
		output("`nYou turn and return to the Gardens.`n`n");
		addnav("G?Return to the Gardens","gardens.php");
	}elseif ($op==""){
		output("`7`nYou walk over to the side of the gardens, where the snow has been pushed to the side forming large banks. ");
		output("As you walk behind them you see a group of shinobi cupping snow in their hands to make small balls of ice. ");
		output("One of them nods towards the gathered snow, then he stands as flings his snowball at great speed towards a nearby friend, before rapidly ducking behind the snow back again. ");
		output("`n`nYou look to the snow in front of you, then back up towards the nearby group of shinobi in the gardens, deciding if you want to start a war or not.");
		addnav("G?Return to the Gardens","gardens.php");
		addnav("Actions");
		addnav("Throw a snowball","runmodule.php?module=snowball&op=throw");
	}elseif ($op=="throw"){
		addnav("G?Return to the Gardens","gardens.php");		
		addnav("Actions");
		if (httpget('found')==1){
			$sql = "SELECT acctid,name FROM " . db_prefix("accounts"). " WHERE name ='".addslashes($name)."'";
		} else{
			output("`n`7You bend down behind the banks, and gather up some snow in your hands. ");
			output("After you quickly crush it into a ball, you think about who you would like to toss it at. ");
			$sql = "SELECT acctid,name FROM " . db_prefix("accounts"). " WHERE loggedin = 1 AND acctid <> ".$session['user']['acctid'];
		}
		$result = db_query($sql);
		$count = db_num_rows($result);
		$row = db_fetch_assoc(db_query($sql));
		if ($count == 0) {
			output("`n`7Looking up you see no one else around, sadly you throw the snow back at the ground and walk off");
		} elseif ($count > 1){
			rawoutput("<form action='runmodule.php?module=snowball&op=throw&found=1' method='POST'>");
			addnav("", "runmodule.php?module=snowball&op=throw&found=1");
			output("`^Available: ");
			rawoutput("<select name='target'>");
			for ($i = 0; $i < $count; $i++) {
				$row = db_fetch_assoc($result);
				rawoutput("<option value='".rawurlencode(addslashes($row['name']))."'>".full_sanitize($row['name'])."</option>");
			}
			rawoutput("</select>");
			$sname = translate_inline("Throw at");
			rawoutput("<input type='submit' class='button' value='$sname'>");
			rawoutput("</form>");
		} else{
			output("`n`7You quickly leap to your feet to find where `\$%s`7 is hiding, and you even lob your snowball right them.`n`n",$row['name']);
			output("You quickly duck behind the snowbank again before sneaking off!");
			$acctid=$row['acctid'];
			$from=$session['user']['name'];
			$subj="Snowball attack!";
			$msg="$from has hit you with a snowball, don't ya think it is time to hit back?";
			systemmail($acctid,$subj,$msg);
		}
	}
	page_footer();
}

?>
