<?php
declare(strict_types=1);

/**
 * Module information for the core system.
 *
 * @return array<string, mixed>
 */
function specialtysystem_getmoduleinfo(): array {
	$info = array(
		"name" => "Specialty Core System",
		"author" => "`2Oliver Brendel",
		"version" => "1.0",
		"download" => "",
		"category" => "Specialty System",
		"settings"=> array(
			"Specialty System Settings,title",
			"nospecs"=>"Disable Specialty Selection after DK and set to 'SS' for this system,bool|0",
			),
		"prefs" => array(
			"Specialtysystem User Prefs,title",
			"uses"=>"Use recordings,viewonly",
			"cache"=>"Fightnav Cache,viewonly",
			"data"=>"Internal Data for this Module (do not alter),viewonly",
		),
	);
	return $info;
}

/**
 * Install the core module.
 *
 * @return bool
 */
function specialtysystem_install(): bool {
	module_addhook("newday-intercept");

	//legacy support
	module_addhook_priority("choose-specialty",100);
	module_addhook_priority("set-specialty",100);
	//woot
	module_addhook_priority("fightnav-specialties",10);
	module_addhook_priority("apply-specialties",100);
	module_addhook("newday");
	module_addhook("incrementspecialty");
	module_addhook("specialtynames");
	module_addhook("specialtymodules");
	module_addhook("specialtycolor");
	module_addhook("dragonkill");
	module_addhook("superuser");
	$system=array(
		//'id'=>array('name'=>'id', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
		'spec_name'=>array('name'=>'spec_name', 'type'=>'varchar(35)'),
		'spec_colour'=>array('name'=>'spec_colour', 'type'=>'varchar(2)'),
		'spec_shortdescription'=>array('name'=>'spec_shortdescription', 'type'=>'varchar(150)'),
		'spec_longdescription'=>array('name'=>'spec_longdescription','type'=>'mediumtext'),
		'modulename'=>array('name'=>'modulename', 'type'=>'varchar(50)'),
		'fightnav_active'=>array('name'=>'fightnav_active', 'type'=>'tinyint','default'=>'0'),
		'fightnav_everyrefresh'=>array('name'=>'fightnav_everyhitrefresh', 'type'=>'tinyint','default'=>'0'),
		'newday_active'=>array('name'=>'newday_active', 'type'=>'tinyint','default'=>'0'),
		'dragonkill_active'=>array('name'=>'dragonkill_active', 'type'=>'tinyint','default'=>'0'),
		'dk_min'=>array('name'=>'dragonkill_minimum_requirement', 'type'=>'smallint','default'=>'0'),
		'stat_requirements'=>array('name'=>'stat_requirements', 'type'=>'varchar(500)','default'=>''),
		'noaddskillpoints'=>array('name'=>'noaddskillpoints', 'type'=>'tinyint unsigned','default'=>'0'),
		'basic_uses'=>array('name'=>'basic_uses', 'type'=>'tinyint unsigned','default'=>'0'),
		'key-PRIMARY'=> array('name'=>'modulename', 'type'=>'key', 'unique'=>'1', 'columns'=>'modulename'),
		'key-one'=> array('name'=>'spec_name', 'type'=>'key', 'unique'=>'0', 'columns'=>'spec_name'),
		);
		require_once("lib/tabledescriptor.php");
		synctable(db_prefix('specialtysystem'), $system, true);
	return true;
}

/**
 * Uninstall the core module.
 *
 * @return bool
 */
function specialtysystem_uninstall(): bool {
	// Delete us and let the newday do what it wants to ... let them select a new specialty
	$sql="UPDATE ".db_prefix('accounts')." SET specialty='' WHERE specialty='SS'";
	db_query($sql);
	// clear up our table
	$sql="DROP TABLE ".db_prefix('specialtysystem').";";
	db_query($sql);
	return true;
}

/**
 * Output fight navigation for all specialties.
 *
 * @param string $script
 * @param bool $force
 * @return void
 */
function specialtysystem_showfightnav(string $script, bool $force = false): void {
	global $session;
	$check=unserialize(stripslashes(get_module_pref("cache","specialtysystem")));
	if (isset($check['system']) && $check['system']=='specialtysystem' && !$force) {
		$specs=$check['data'];
		$colours=$check['colours'];
	} else {
		debug("Refreshing the Fightnav Systemspecs");
		$sql="SELECT * FROM ".db_prefix('specialtysystem')." WHERE fightnav_active=1";
		$result=db_query($sql); //after testing add a cache here
		$specs=array();
		$colours=array();
		while ($row=db_fetch_assoc($result)) {
			require_once("modules/{$row['modulename']}.php");
			$fname=$row['modulename']."_fightnav";
			$add=$fname();//debug($fname);debug($add);
			if ($add!=false) {
				$colours[$row['modulename']]=$row['spec_colour'];
				$specs[$row['modulename']]=$add;
			}
		}
		set_module_pref("cache",serialize(array("system"=>"specialtysystem","data"=>$specs,"colours"=>$colours)),"specialtysystem");
	}//debug($specs);debug($colours);
	require_once("modules/specialtysystem/functions.php");
	addnav(array("`b--Chakra (%s points)--`b",specialtysystem_availableuses()));
	ksort($specs);
	foreach ($specs as $key=>$data) {
		$colour=$colours[$key];

		foreach ($data as $keyi=>$dati) {
			if (!is_array($dati)){ //Not sure if this is still needed, but I'll leave it here anyway.
				if (strcmp($keyi,'headline')==0) {
					addnav_notl(" ","");
					addnav_notl("`b`0-$colour$dati`0-`b","");
				} else {
					$dativ=explode("|||",$dati);
					//debug($colour.$dativ[0]."`0");
					addnav_notl($colour.$dativ[0]."`0", $script."op=fight&skill=SS&skillmodule=$key&skillname=".$dativ[1], true);
				}
			}else{
				foreach ($dati as $keyv=>$datv) { //Now deals with $specialtycollector being an Array of Arrays!
					if (strcmp($keyv,'headline')==0) {
						addnav_notl(" ","");
						addnav_notl("`b`0-$colour$datv`0-`b","");
					} else {
						$datvv=explode("|||",$datv);
						//debug($colour.$dativ[0]."`0");
						addnav_notl($colour.$datvv[0]."`0", $script."op=fight&skill=SS&skillmodule=$key&skillname=".$datvv[1], true);
					}
				}
			}
		}
	}
	return;
}

/**
 * Handle LoTGD hooks for the core system.
 *
 * @param string $hookname
 * @param array $args
 * @return array
 */
function specialtysystem_dohook(string $hookname, array $args): array {
	global $session,$resline;
	//resline is a hack to transfer the &ressurrection=0/1 ... I leave it in here ... for now.

	switch ($hookname) {
	case "superuser":
		tlschema("superuser");
		addnav("Mechanics");
		tlschema();
		if (($session['user']['superuser']&SU_MEGAUSER)==SU_MEGAUSER) addnav("Refresh Specialty System Add-Ons","runmodule.php?module=specialtysystem&op=refresh");
		break;
	case "newdayintercept":
		//if other specialties are disabled
		require_once("modules/specialtysystem/datafunctions.php");
		if (httpget('ssystem')!='') {
			specialtysystem_set(array("active"=>httpget('ssystem')));
		} else if (get_module_setting('nospecs')&&$session['user']['specialty']=='')
			$session['user']['specialty']='SS';
		break;
	case "dragonkill":
		set_module_pref("data",'',"specialtysystem");
		set_module_pref("uses",0,"specialtysystem");
		set_module_pref("cache",'',"specialtysystem");

		break;
	case "choose-specialty":
		require_once("modules/specialtysystem/datafunctions.php");
		if ($session['user']['specialty'] == "" ||
				$session['user']['specialty'] == '0') {
			$choices=specialtysystem_getspecs();
			addnav("Chakra Specialties");
			$first=false;
			output_notl("`c");
			foreach ($choices as $key=>$data) {
				if ($data['dragonkill_minimum_requirement']>$session['user']['dragonkills']) continue;
				if (((int)$data['dragonkill_minimum_requirement'])==-1) continue;
				if ($first) output_notl("`~~~~~~~~~~~~~`2`n`n");
				$first=true;
				$spec=$data['spec_colour'].translate_inline($data['spec_name'],"module-".$data['modulename']);
				output_notl("%s:`n`n",$spec);
				$available=true;
				if (isset($data['stat_requirements']) && $data['stat_requirements']!='') {
					//check if the stats are ok
					output("`4Minimum Requirements:`n");
					$unserialized=unserialize($data['stat_requirements']);
					if (!is_array($unserialized)) {
						output("None`n");
					} else {
						foreach ($unserialized as $stat=>$value) {
							$ok=($session['user'][$stat]>=$value?1:0);
							if ($ok) $k="`2";
								else $k="`\$";
							if (!$ok) $available=false;
							//deliberately translatable
							$stat_trans=translate_inline($stat,"stats_specialtysystem");
							output("%s%s (Minimum %s needed)`n",$k,$stat_trans,$value);
						}
					}
					output_notl("`n`n");				
				}
				if (!$available) {
					addnav("Unavailable");
					addnav_notl(sanitize($spec),"");
					$t1 = appoencode(translate_inline($data['spec_shortdescription'],"module-".$data['modulename']));
					//$t2 = appoencode("`7(".$data['spec_colour'].$spec."`7)`0");
					rawoutput("$t1<br>");
				} else {
					addnav("Chakra Specialties");
					addnav_notl(" ?$spec","newday.php?setspecialty=SS&ssystem={$data['modulename']}$resline");
					$t1 = appoencode(translate_inline($data['spec_shortdescription'],"module-".$data['modulename']));
					//$t2 = appoencode("`7(".$data['spec_colour'].$spec."`7)`0");
					//rawoutput("<a href='newday.php?setspecialty=SS&ssystem={$data['modulename']}$resline'>$t1 $t2</a><br>");
					rawoutput("<a href='newday.php?setspecialty=SS&ssystem={$data['modulename']}$resline'>$t1</a><br>");
					addnav("","newday.php?setspecialty=SS&ssystem={$data['modulename']}$resline");
				}
				output_notl("`n");
			}
			output_notl("`c");
			if ($session['user']['dragonkills']<1) output("`n`n`c`\$More `bsophisticated`b stuff will come along once you are more experienced!`c`0`n`n");
		}
		break;
	case "set-specialty":
		require_once("modules/specialtysystem/datafunctions.php");
		if($session['user']['specialty'] == "SS") {
			$module=httpget('ssystem');
			$data=specialtysystem_getspecs($module);
			$data=array_shift($data);
			specialtysystem_set(array("active"=>$module,$module=>array('skillpoints'=>1)));
			page_header("A little story about yourself");
			output_notl("`c`b%s%s`b`c`n`n`&",$data['spec_colour'],$data['spec_name']);
			$desc=translate_inline($data['spec_longdescription'],"module-".$data['modulename']);
			output_notl($desc);
		}
		//add here, in any case of his selection, the specs he gets minimum uses for in the system
		$basic=specialtysystem_getspecs();
		foreach ($basic as $modulename=>$data) {
			if ($data['basic_uses']>0) {
				specialtysystem_set(array($modulename=>array('skillpoints'=>$data['basic_uses'])));
			}
		}
		break;

	/* not used oftenly */
	case "specialtycolor": //the only module I can see which uses this is foilwench
		require_once("modules/specialtysystem/datafunctions.php");
		$specs=specialtysystem_get("active");
		if ($specs==false) {
			break;
		}
		$spec=specialtysystem_getspecs($specs);
		if (isset($spec['spec_colour'])) $args['SS'] = $spec['spec_colour'];
			else $args['SS']='SpecialtySystem';
		break;
	case "specialtynames": //same here, yet bio.php needs it too, so I have to make a nasty thing
		global $SCRIPT_NAME;
		if ($SCRIPT_NAME=='bio.php') { //just to let the bio work
			$login=httpget('char');
			$sql="SELECT acctid,specialty FROM ".db_prefix('accounts')." WHERE login='$login';";
			$result=db_query($sql);
			$user=db_fetch_assoc($result);
		} else {
			$user=$session['user'];
		}
		if (!isset($user['specialty']) || $user['specialty']!="SS") break;
		require_once("modules/specialtysystem/datafunctions.php");
		require_once("modules/specialtysystem/functions.php");
		$data=specialtysystem_get("active",$user['acctid']);
		if ($data==false) {
			output_notl("Error with your specialty! Report to admin!");
			break;
		}
		$current=0;
		$temp = specialtysystem_getspecs($data);
		$data=array_shift($temp);
		$args['SS'] =translate_inline($data['spec_name']);
		break;
	case "specialtymodules": //called in user.php
		$args['SS'] = "specialtysystem";
		break;
	/* end of */

	case "incrementspecialty":
		$col=$args['color'];
		if ($session['user']['specialty']!="SS") break;
		require_once("modules/specialtysystem/datafunctions.php");
		require_once("modules/specialtysystem/functions.php");
		$name='';
		$specs=specialtysystem_getspecs();
		foreach ($specs as $name_m=>$spec) {
			if ($spec['spec_colour']==$col) $name=$name_m;
		}
		debug($name);
		if ($col=="`^") $name=specialtysystem_get('active');
		if ($name=='') break;
		$data=$name;
		$current=0;
		$current=specialtysystem_increment($data,1);
		$data=array_shift(specialtysystem_getspecs($data));
		$total=specialtysystem_getskillpoints();
		if (httpget('suppress')!=1) {
			output("`n`^You gain a level in `&%s%s`^. All in all, you have `&%s`^ skillpoints with this specialty and `&%s`^ all in all!`n`n", $data['spec_colour'],translate_inline($data['spec_name'],"module-".$data['modulename']),$current,$total);
			output_notl("`0");
		}
		set_module_pref("cache",'',"specialtysystem");
		break;
	case "apply-specialties":
		if (httpget('skill')!="SS") break;
		$module=httpget('skillmodule');
		require_once("modules/$module.php");
		$fname=$module."_apply";
		$value=$fname(httpget('skillname'));
		set_module_pref("cache",'',"specialtysystem");
		break;
	case "fightnav-specialties":
		$script=$args['script'];
		specialtysystem_showfightnav($script);
		break;
	case "newday":
		if ($session['user']['specialty'] != "SS") break;
		set_module_pref("uses",0);
		$bonus = getsetting("specialtybonus", 1);
		$intel = (int)($session['user']['intelligence']/10);
		$bonus +=$intel;
		require_once("modules/specialtysystem/datafunctions.php");
		specialtysystem_newday();
		$data=specialtysystem_get("active");
		if ($data==false) {
			output_notl("Error with your specialty! Report to admin!");
			break;
		}
		require_once("modules/specialtysystem/functions.php");
		$current=specialtysystem_setuses(-$bonus);
		$temp = specialtysystem_getspecs($data);
		$data=array_shift($temp);
		$name = translate_inline($data['spec_name'],"module-".$data['modulename']);
		if ($bonus == 1) {
			output("`n`2Because of your inclination to %s%s`2, you receive `^1`2 extra chakra use for today.`n",$data['spec_colour'], $name);
		} else {
			output("`n`2Because of your inclination to %s%s`2, you receive `^%s`2 extra chakra uses (`@%s for high intelligence`2) for today.`n",$data['spec_colour'], $name,$bonus,$intel);
		}
		set_module_pref("cache",'',"specialtysystem");
		break;
	}
	return $args;
}

/**
 * Module runtime operations.
 */
function specialtysystem_run(): void {
	$op=httpget('op');
	switch ($op) {
		case "refresh":
		require_once("modules/specialtysystem/register.php");
		specialtysystem_register();
		page_header("Specialtysystem");
		output("`2Successfully refreshed!");
		villagenav();
		page_footer();
		//redirect("superuser.php");
		break;
	}

}
?>
