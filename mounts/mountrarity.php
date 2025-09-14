<?php

/*
   Mount Rarity
   File: mountrarity.php
   Author:  Red Yates aka Deimos
   Date:    1/10/2005
   Version: 1.2 (1/16/2023)

   Attaches a setting to each mount for rarity percentage.
   Each game day the module roles for each mount to be available or not.
   Done by request of the jcp.

   v1.02
   Fixed stupid error wherein nothing actually happened.

   v1.1
   Made changes so that it blocks the navs on every stables page, not just the
   main.
   Flipped the available/unavailable pref for more sensible boolean operating.

   v1.2
   Added mountdays setting and mountdays_remaining objpref to make mounts available for a set duration.
 */

// translator ready
// addnews ready
// mail ready

require_once("lib/gamelog.php");

function mountrarity_getmoduleinfo(){
	$info=array(
			"name"=>"Mount Rarity",
			"version"=>"1.2",
			"author"=>"`\$Red Yates",
			"category"=>"Mounts",
			"download"=>"core_module",
			"settings"=>array(
				"Mount Rarity settings,title",
				"showout"=>"Show missing mounts list,bool|0",
				"mountdays"=>"Number of days a mount remains available once it becomes available,int|3",
				),
			"prefs-mounts"=>array(
				"Mount Rarity Mount Preferences,title",
				"rarity"=>"Percentage chance of mount being available each day,floatrange,1,100,0.5|100",
				"always_available_date"=>"Date on which mount is always available (mm-dd),|00-00",
				"unavailable"=>"Is mount unavailable today,bool|0", 
				"mountdays_remaining"=>"Days remaining for mount to be available,int|0", 
				),
			"user_prefs"=>array(
				"Mount Rarity User Preferences,title",
				"user_showmountlist"=>"Show mount list on new day,bool|1",
				),
			"prefs"=>array(
				"Mount Rarity User Preferences,title",
				"user_showmountlist"=>"Show mount list on new day,bool|1",
				),
     );
	return $info;
}

function mountrarity_install(){
	module_addhook("newday-runonce");
	module_addhook("mountfeatures");
	module_addhook("stables-desc");
	module_addhook("stables-nav");
	module_addhook("newday");
	return true;
}

function mountrarity_uninstall(){
	return true;
}

function mountrarity_dohook($hookname, $args){
	switch($hookname){
		case "newday-runonce":
			$sql="SELECT mountid FROM ".db_prefix("mounts")." WHERE mountactive=1";
			$result=db_query($sql);
			$gamelog="";
			$current_date = date("m-d");
			while($row=db_fetch_assoc($result)) {
				$id=$row['mountid'];
				$rarity=get_module_objpref("mounts",$id,"rarity");
				$always_available_date = get_module_objpref("mounts", $id, "always_available_date");
				$mountdays = get_module_setting("mountdays");
				// Get the remaining days for the mount, if any
				$mountdays_remaining = (int) get_module_objpref("mounts", $id, "mountdays_remaining");
				// Get if the mount is available today
				$unavailable = get_module_objpref("mounts", $id, "unavailable");
				// If the mount is available today, reduce the number of days remaining, else check if it should be available today
				if ($mountdays_remaining > 0) {
					$mountdays_remaining--;
					set_module_objpref("mounts", $id, "mountdays_remaining", $mountdays_remaining);
				} else {
					if ($always_available_date == $current_date) {
						set_module_objpref("mounts", $id, "unavailable", 0);
						set_module_objpref("mounts", $id, "mountdays_remaining", $mountdays);
						$gamelog.="$id ";
					} else {
						if (e_rand(1,1000)>($rarity*10)) { // set to promille
							set_module_objpref("mounts", $id, "unavailable", 1);
							set_module_objpref("mounts", $id, "mountdays_remaining", 0);
						} else {
							set_module_objpref("mounts", $id, "unavailable", 0);
							set_module_objpref("mounts", $id, "mountdays_remaining", $mountdays);
							$gamelog.="$id ";
						}
					}
				}
			}
			gamelog("Recalculated Mounts available to IDs: ".$gamelog);
			break;
		case "mountfeatures":
			$rarity=get_module_objpref("mounts",$args['id'],"rarity");
			$args['features']['Rarity']=$rarity;
			break;
		case "stables-desc":
			if (get_module_setting("showout")){
				$sql="SELECT mountid, mountname FROM ".db_prefix("mounts")." WHERE mountactive=1";
				$result=db_query($sql);
				output("`nA sign by the door proclaims that the following mounts are out of stock for today:");
				while ($row=db_fetch_assoc($result)) {
					$out=get_module_objpref("mounts",$row['mountid'],"unavailable");
					if ($out){
						output("`n%s",$row['mountname']);
					}
				}
			}else{
				output("`nIf you don't see something you like today, perhaps you should check again tomorrow.");
			}
			break;
		case "stables-nav":
			$sql="SELECT mountid FROM ".db_prefix("mounts")." WHERE mountactive=1";
			$result=db_query($sql);
			while($row=db_fetch_assoc($result)) {
				$id=$row['mountid'];
				$out=get_module_objpref("mounts",$id,"unavailable");
				$days_remaining=get_module_objpref("mounts",$id,"mountdays_remaining");
				if ($out || $days_remaining <= 0) blocknav("stables.php?op=examine&id=$id");
			}
			break;
		case "newday":
            if (get_module_pref("user_showmountlist")) {
            output("`n`c`bAvailable Mounts for Today:`b`n");
            $sql="SELECT mountid, mountname, mountcostgems, mountcostgold, mountlocation FROM ".db_prefix("mounts")." WHERE mountactive=1 ORDER BY mountname ASC";
            $result=db_query($sql);
            $counter = 0;
            rawoutput("<table>");
            $gold = translate_inline("Gold");
            $gems = translate_inline("Gems");
            $name = translate_inline("Name");
            $location = translate_inline("Location");
            rawoutput("<tr class='trhead'><td>$name</td><td>$gold</td><td>$gems</td><td>$location</td></tr>");
            while($row=db_fetch_assoc($result)) {
            $id=$row['mountid'];
            $out=get_module_objpref("mounts",$id,"unavailable");
            $days_remaining=get_module_objpref("mounts",$id,"mountdays_remaining");
            $mountdays = get_module_setting("mountdays");
            if (!$out && $days_remaining > 0)
            {
            $days = ($days_remaining < $mountdays) ? sprintf_translate("%s gameday%s remaining", $days_remaining, $days_remaining == 1 ? "" : "s") : translate_inline("available");
            $class = ($counter % 2 == 0) ? "trlight" : "trdark";
            $costGold = number_format($row['mountcostgold'], 0, '.', ',');
            $costGems = number_format($row['mountcostgems'], 0, '.', ',');
            rawoutput("<tr class='$class'><td>".$row['mountname']."</td><td>".$costGold."</td><td>".$costGems."</td><td>".$row['mountlocation']." (".$days.")</td></tr>");
            $counter++;
            }
            }
            rawoutput("</table>");
            output_notl("`c");
            }
            break;
	}
	return $args;
}

function mountrarity_run(){
}

?>
