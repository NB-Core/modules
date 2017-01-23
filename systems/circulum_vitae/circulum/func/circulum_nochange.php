<?php
	function circulum_get_account_nochanges() {
		//copied from dragon.php
		$nochange=array("acctid"=>1
				   ,"name"=>1
				   ,"playername"=>1
				   ,"sex"=>1
				   ,"password"=>1
				   ,"marriedto"=>1
				   ,"title"=>0
				   ,"login"=>1
				   ,"dragonkills"=>0
				   ,"locked"=>1
				   ,"loggedin"=>1
				   ,"superuser"=>1
				   ,"gems"=>0
				   ,"hashorse"=>0
				   ,"gentime"=>1
				   ,"gentimecount"=>1
				   ,"lastip"=>1
				   ,"uniqueid"=>1
				   ,"dragonpoints"=>0
				   ,"laston"=>1
				   ,"prefs"=>1
				   ,"lastmotd"=>1
				   ,"emailaddress"=>1
				   ,"emailvalidation"=>1
				   ,"gensize"=>1
				   ,"bestdragonage"=>0
				   ,"dragonage"=>0
				   ,"donation"=>1
				   ,"donationspent"=>1
				   ,"donationconfig"=>1
				   ,"strength"=>1
				   ,"dexterity"=>1
				   ,"constitution"=>1
				   ,"intelligence"=>1
				   ,"wisdom"=>1
				   ,"bio"=>1
				   ,"charm"=>0
				   ,"banoverride"=>1 
				   ,"referer"=>1
				   ,"refererawarded"=>1
				   ,"ctitle"=>1
				   ,"beta"=>1
				   ,"clanid"=>1
				   ,"clanrank"=>1
				   ,"clanjoindate"=>1
				   ,"regdate"=>1
				   );
		$nochange_obj=get_module_objpref("circulum_nochangearray",0,"nochanges");
		if ($nochange_obj) 
			return unserialize($nochange_obj);
			else
			return $nochange;
	}
	
	function circulum_save_account_nochanges($fields) {
		set_module_objpref("circulum_nochangearray",0,"nochanges",serialize($fields));
	}
	
	function circulum_title_help() {
	output("`#You can have only one title per number of Circulum Vitae.");
	output("This is basically because of the storage in the database`n`n");
	output("You can have gaps in the title order.");
	output("If you have a gap, the title given will be for the CV rank less than or equal to the players current number of CVs.`n");
	}
	function circulum_set_title($titleid,$male,$female) {
		set_module_objpref("circulum_title",$titleid,"male",$male);
		set_module_objpref("circulum_title",$titleid,"female",$female);
	}
	function circulum_get_title($titleid,$gender) {
		return get_module_objpref("circulum_title",$titleid,$gender);
	}
	function circulum_get_maxtitle($maxid=9999) { //9999 just for the safety
		$sql = "SELECT max(objid) as titleid FROM ".db_prefix("module_objprefs")." WHERE modulename='circulum' AND objtype='circulum_title' AND objid<=$maxid;"; //(titleid,dk,ref,male,female) VALUES ($id,$dk,'$ref','$male','$female')";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		return $row['titleid'];
	}
	
	function circulum_get_maxgendertitle($maxid=9999,$gender="male") { //9999 just for the safety
		$sql = "SELECT value FROM ".db_prefix("module_objprefs")." WHERE modulename='circulum' AND objtype='circulum_title' AND objid<=$maxid AND setting='$gender' ORDER BY objid+0 DESC;"; //(titleid,dk,ref,male,female) VALUES ($id,$dk,'$ref','$male','$female')";
		$result=db_query($sql);
		$row=db_fetch_assoc($result);
		return $row['value'];
	}
	
	function circulum_get_arraytitle() {
		$sql = "SELECT objid as titleid FROM ".db_prefix("module_objprefs")." WHERE modulename='circulum' AND objtype='circulum_title' group by objid;"; //(titleid,dk,ref,male,female) VALUES ($id,$dk,'$ref','$male','$female')";
		$result=db_query($sql);
		$back=array();
		while ($row=db_fetch_assoc($result)) {
			array_push($back,$row['titleid']);
		}
		return $back;
	}	
?>
