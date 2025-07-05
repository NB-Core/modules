<?php
// addnews ready
// translator ready
// mail ready

function savedays_getmoduleinfo(){
	$info = array(
		"name"=>"Save Days",
		"author"=>"`JShinobiIceSlayer",
		"version"=>"1.00",
		"category"=>"General",
        "download"=>"",
		"settings"=>array(
			"Saved Days Module Settings,title",
			"turns"=>"What is the number of turns gain for each missed day?,range,1,25,1|10",
			"maxturns"=>"What is the Maximum amount of turns the user can gain?,int|50",
		),
		"prefs"=>array(
			"Saved Days User Preferences,title",
			"daysmissed"=>"How many days the user has missed,int|0",
			"user_reject"=>"Opt to not receive extra turns for missed days,bool|0",
		),
	);
	return $info;
}

function savedays_install(){
	module_addhook("newday-runonce");
	module_addhook("newday");
	return true;
}
function savedays_uninstall(){
	return true;
}

function savedays_dohook($hookname,$args){
	global $session;

	switch($hookname){
	case "newday-runonce":
		$update="UPDATE ".db_prefix('module_userprefs')."
				SET value = value + 1 
				WHERE modulename = 'savedays' 
				AND setting = 'daysmissed';";
		db_query($update); 
		$select="SELECT acctid
				FROM ".db_prefix('accounts')."
				WHERE acctid not in (SELECT userid
									FROM module_userprefs
									WHERE modulename = 'savedays' 
									AND setting = 'daysmissed')";
		$result=db_query($select);
		$end=db_num_rows($result);
		if($end>0){
			$insert="INSERT 
					INTO ".db_prefix('module_userprefs')." (modulename, setting, userid, value)
					VALUES ";			
			while ($row=db_fetch_assoc($result)) {
				$userid=$row['acctid'];
				$insert.="('savedays','daysmissed',$userid,1),";
			}
			$insert = substr($insert,0,strlen($insert)-1);
			db_query($insert);
		}
		break;
	case "newday":
		if(!get_module_pref("user_reject")){
			$misseddays=get_module_pref('daysmissed')-1;
			$multiplier=get_module_setting('turns');
			$maxgain=get_module_setting('maxturns');
			if ($misseddays>0) {
				$turnsgained= $misseddays * $multiplier;
			} else {
				$turnsgained = 0;
			}
			if ($turnsgained>$maxgain) $turnsgained=$maxgain;
			$session['user']['turns']+=$turnsgained;
			if ($turnsgained>0) {
				output("`n`^For having missed %s game days, you gain an extra of %s turns today.",$misseddays,$turnsgained);
				debuglog("Gained $turnsgained turns for $misseddays game newdays missed.");
			} else {
				debuglog("Gained no turns for game days missed.");
			}
			set_module_pref('daysmissed',0);
		} else {
			$misseddays=get_module_pref('daysmissed')-1;
			if ($misseddays>0) output("`nDue to your choice, you do not receive the extra days for each gameday you have missed.");
		}
		break;	
	}
	return $args;
}

function savedays_run(){
}
?>
