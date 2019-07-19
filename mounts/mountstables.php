<?php

function mountstables_getmoduleinfo(){
	$info = array(
			"name"=>"Mount Stable Spots",
			"author"=>"Oliver Brendel",
			"version"=>"1.0",
			"category"=>"Mounts",
			"download"=>"https://github.com/NB-Core",
			"vertxtloc"=>"https://github.com/NB-Core",
			"description"=>"Stable places for mounts to swap in/out",
			"settings"=>array(
				"Mount Stables Settings,title",
				"stablecount"=>"How many spots does he grant,int|20",
				"givebuff"=>"Issue a fresh buff (!potential abuse!) on mount switch from stables?,bool|0",
				"Note: Switching grants a fresh mountbuff. So if you make the switch free it means free buff refresh,note",
				"dailylimit"=>"How many switches per day are fine?,int|5",
				),
			"prefs"=>array(
				"initial"=>"Has initial Mount Name been purchased,bool|0",
				"name"=>"User's Mount's Name,text|",
				"dailylimitused"=>"How often swapped today,int|",
				),
		     );
	return $info;
}
function mountstables_install(){
	module_addhook("newday");
	module_addhook("stables-desc");
	module_addhook("stables-nav");
	$table=array(
			'id'=>array('name'=>'id', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
			'acctid'=>array('name'=>'acctid', 'type'=>'int(11) unsigned'),
			'mountid'=>array('name'=>'mountid', 'type'=>'int(11) unsigned'),
			'mountname'=>array('name'=>'mountname', 'type'=>'varchar(256)'),
			'stabledate'=>array('name'=>'stabledate', 'type'=>'datetime', 'default'=>DATETIME_DATEMIN),
			'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'id'),
		    );
	require_once("lib/tabledescriptor.php");
	synctable(db_prefix("mountstables"), $table, true);	
	return true;
}
function mountstables_uninstall(){
	return true;
}

function mountstables_getMountList($id=0) {
	global $session;
	$u = $session['user']['acctid'];
	$add="";
	if ($id!=0) {
		$add = " AND id=$id ";
	}
	$sql = "SELECT * from ".db_prefix("mountstables")." WHERE acctid=$u".$add." ORDER BY stabledate DESC";
	$result = db_query($sql);
	if (db_num_rows($result)==0) return array();
	$list=array();
	while ($row=db_fetch_assoc($result)) {
		$list[]=$row;
	}
	return $list;
}

function mountstables_removeStabledMount($intid) {
	global $session;
	$u = $session['user']['acctid'];
	$sql = "delete from ".db_prefix("mountstables")." WHERE acctid=$u AND id=$intid;";
	$result = db_query($sql);
	$ret = (int) db_affected_rows($result);	
	debuglog("removed $mountid result $ret");
	return $ret;
}

function mountstables_makeCurrentMount($intid) {
	global $session,$playermount;
	$sql = "SELECT * from ".db_prefix("mountstables")." WHERE id=$intid";
	$result = db_query($sql);
	if (db_num_rows($result)==0) {
		debuglog("error, could not make current mount");
		return 0;
	}
	$row = db_fetch_assoc($result);
	$session['user']['hashorse'] = $row['mountid'];
	$playermount = getmount($row['mountid']); // yeah, some hotshot thought it to be cool in core to store the mount info in a global array...just for kicks...
	set_module_pref("name",$row['mountname'],"mountname");
	//give mount buff
	if (get_module_setting("givebuff")==1) {
		$sql = "SELECT * FROM " . db_prefix("mounts") . " WHERE mountid='".$row['mountid']."';";
		$result = db_query_cached($sql, "mountdata-".$row['mountid'], 3600);
		$mount = db_fetch_assoc($result);
		$buff = unserialize($mount['mountbuff']);
		$buff['schema'] = "mounts";
		apply_buff('mount',unserialize($mount['mountbuff']));
	}
	debuglog("made ".$row['mountid']." slot $intid current mount");
	return $ret;
}

function mountstables_removeCurrentMount() {
	global $session,$playermount;
	$session['user']['hashorse'] = 0;
	unset($playermount); //some hotshot thought it to be cool in core to store the mount info in a global array...just for kicks...
	if (is_module_active("mountname")) {
		set_module_pref("name","","mountname");
	}
	strip_buff('mount');
	debuglog("removed current mount from user");
	return 1;
}

function mountstables_currentMountName() {
	global $session;
	$mountid = $session['user']['hashorse'];
	if ($mountid==0) return "";
	$mountname = "Dummy";
	if (is_module_active("mountname")) {
		$mount = get_module_pref("name","mountname");
	}
	if ($mount == "") {
		//no user name given, take default
		$sql = "SELECT * FROM " . db_prefix("mounts") . " WHERE mountid='$mountid'";
		$result = db_query($sql);
		$row = db_fetch_assoc($result);
		$mount = $row['mountname'];
	}
//debug($mountid);
//debug($row);
	return $mount;
}

function mountstables_addCurrentMount($force=0) {
	global $session;
	$u = $session['user']['acctid'];
	$mountid = $session['user']['hashorse'];
	$mount = mountstables_currentMountName();
	$list = mountstables_getMountList();
	$size = count($list);
	if ($size >= get_module_setting("stablecount") && $force==0) {
		debuglog("tried to add mount $mountid to list, but $size spots were already taken");
		return 0;
	} elseif ($force==1){
		debuglog("tried to add mount $mountid to list, but $size spots were already taken. FORCED INSERT");
	}
	$sql = "insert into ".db_prefix("mountstables")." (acctid,mountid,mountname,stabledate) VALUES ($u,$mountid,'".db_real_escape_string($mount)."','".date("Y-m-d H:i:s")."');";
	$result = db_query($sql);
	$ret = (int) db_affected_rows($result);	
	debuglog("put $mountid into stable spot, result $ret");
	mountstables_removeCurrentMount();
	return $ret;	

}


function mountstables_dohook($hookname,$args){
	global $session;
	require_once("lib/debuglog.php");
	$op = httpget('op');
	$count = get_module_setting("stablecount");
	$mount = mountstables_currentMountName();
	switch ($hookname){
		case "newday":
			set_module_pref('dailylimitused',0);
			break;
		case "stables-nav":
			if (is_module_active('prizemount')) {
				$prizeid = get_module_setting("mountid","prizemount");
				if ($session['user']['hashorse']==$prizeid) {
					output("`2Sorry, we cannot stable your %s, it is too strong. Wait until it leaves...`n`n",$mount);
					break;
				}
			}
			$list = mountstables_getMountList();
			//debug($list);
			$size = count($list);
			addnav("Spots");
			if ($size<=$count) {
				//output("`2You can stable your current mount for a price of `4%s`2. You have `x%i`2 spots left to fill.`n`n",$cost,$count-$size);	
				output("`2You can stable your current mount here. You have `x%s`2 spots left to fill.`n`n",$count-$size);	
				if ($session['user']['hashorse']!=0 && $op!="confirmsell") {
					addnav(array("Stable your %s",$mount),"runmodule.php?module=mountstables&op=stable");
				} else {
					// no mount
				}
			} else {
				output("`2You have all stable spots taken. You have to remove a mount to stable again.`n`n");	
			}
			if (get_module_setting("givebuff")==0) {
				output("`yNote: `\$You will not get a rested mount from the stables. We make them rest, so they will come out dosy and need a new day to be able to support you in combat!`2`n");				
			}
			$switches = get_module_setting('dailylimit');
			$cur = get_module_pref('dailylimitused');
			$no_swap = ($switches<=$cur); // no swap possible
			addnav("Swap");
			foreach ($list as $row) {
				addnav(array("Swap to %s",$row['mountname']),($no_swap?"":"runmodule.php?module=mountstables&op=swap&intid=".$row['id']));
			}

			break;


	}
	return $args;
}
function mountstables_run(){
	global $session;
	require_once("lib/debuglog.php");
	$count = get_module_setting("stablecount");
	$op = httpget('op');
	page_header("Stable Spots");
	villagenav();
	addnav("Other");
	addnav("Back to the stables","stables.php");
	$mount = mountstables_currentMountName();
	switch ($op){
		case "stable":
			output("`2Do you want to stable your %s`2?",$mount);
			addnav("Actions");
			addnav("Yes","runmodule.php?module=mountstables&op=stable_yes");
			break;
		case "stable_yes":
			mountstables_addCurrentMount();
			$id = $session['user']['hashorse'];
			mountstables_removeCurrentMount();
			output("`2Your %s`2 was stabled.",$mount);
			break;
		case "swap":
			$intid = httpget('intid');
			$list=mountstables_getMountList();
			$row = array_shift($list);
			$switches = get_module_setting('dailylimit');
			$cur = get_module_pref('dailylimitused');
			output("`2Do you want to swap your %s`2 to %s`2?`n`n`xYou can so for another %s times today!",$mount,$row['mountname'],$switches-$cur);
			addnav("Actions");
			addnav("Yes","runmodule.php?module=mountstables&op=swap_yes&intid=$intid");
			break;
		case "swap_yes":
			$intid = httpget('intid');
			$list=mountstables_getMountList($intid);
//debug($list);
			$name_new = $list[0]['mountname'];
			$hasid = $session['user']['hashorse'];
			if ($hasid!=0) {
				//has mount, put it in
				mountstables_addCurrentMount(1); //force it
				mountstables_removeCurrentMount();
				$list=mountstables_getMountList($intid);
			}
			//add from stables
			mountstables_makeCurrentMount($intid);
			mountstables_removeStabledMount($intid);
			if ($hasid!=0) 
				output("`yYour %s`y is now safely stabled, and you have your %s`y now to ride out!`n`n",$mount,$name_new);
			else 
				output("`yYour %s`y is now ready to ride out!`n`n",$name_new);
			increment_module_pref('dailylimitused',1);
			break;
	}
	page_footer();
}
?>
