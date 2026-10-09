<?php
	function title_help() {
	output("`#You can have only one title per number Clan Rank.");
	output("This is basically because of the storage in the database. You can also assign a total of 30 ranks... from the 1st to the 30th=Leader.`n`n");
	output("You can have gaps in the title order.");
	output("If you have a gap, the title given will be for the rank less than or equal to the players current Clan Rank Number (i.e. if you delete a rank with members assigned).`n");
	//output("`nAlso be aware that the `bbasic`b titles Applicant - Member - Officer - Leader at 0,10,20,30 can't be left empty. You can alter the name, but not leave it empty.");
	}
	function clanranks_set_title($titleid,$clan,$title) {
		set_module_objpref("clanranks_title",$titleid,$clan,stripslashes($title));
		//set_module_objpref("clanrank_title",$titleid,"female",$female);
	}
	function clanranks_get_title($titleid,$clan) {
		$ranks = array(CLAN_APPLICANT=>"`!Applicant`0",CLAN_MEMBER=>"`#Member`0",CLAN_OFFICER=>"`^Officer`0",CLAN_ADMINISTRATIVE=>"`\$Administrative`0",CLAN_LEADER=>"`&Leader`0");
		$new=get_module_objpref("clanranks_title",$titleid,$clan);
		if (isset($ranks[$titleid]) && $new=="") return $ranks[$titleid];
		return $new;
	}
	function clanranks_get_nexttitle($clan,$maxid=0) { //999 just for the safety
		$ranks=clanranks_getallranks($clan);
		$ranks=array_keys($ranks);
		while (count($ranks)>0) {
			$key=array_shift($ranks);
			if ($key>$maxid) return $key;
		}
		return 30;
	}

	function clanranks_get_prevtitle($clan,$maxid=30) { //999 just for the safety
		$ranks=clanranks_getallranks($clan);
		$ranks=array_keys($ranks);
		while (count($ranks)>0) {
			$key=array_pop($ranks);
			if ($key<$maxid) return $key;
		}
		return 0;
	}

	function clanranks_getallranks($clanid,$ranks=false) {
		$conn = \Lotgd\MySQL\Database::getDoctrineConnection();
		$objprefsTable = \Lotgd\MySQL\Database::prefix("module_objprefs");
		$rows = $conn->executeQuery(
			"SELECT objid,value AS title FROM {$objprefsTable} WHERE modulename='clanranks' AND objtype='clanranks_title' AND setting = :setting GROUP BY objid",
			['setting' => (string) $clanid],
			['setting' => \Doctrine\DBAL\ParameterType::STRING]
		)->fetchAllAssociative();
		if ($ranks===false) {
			$ranks = array(CLAN_APPLICANT=>"`!Applicant`0",CLAN_MEMBER=>"`#Member`0",CLAN_OFFICER=>"`^Officer`0",CLAN_ADMINISTRATIVE=>"`\$Administrative`0",CLAN_LEADER=>"`&Leader`0",CLAN_FOUNDER=>"`\$Founder");
		}
		foreach ($rows as $row) {
			if (isset($row['title'])) $ranks[$row['objid']]=$row['title'];
		}
		ksort($ranks);
		return $ranks;
	}
?>
