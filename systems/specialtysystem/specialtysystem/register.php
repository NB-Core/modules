<?php

function specialtysystem_register() {
	$args=modulehook("specialtysystem-register",array());
	invalidatedatacache("specialtygetspecs");
	debug($args);
	$sql="DELETE FROM ".db_prefix('specialtysystem').";";
	db_query($sql); //delete all entries
	if (!is_array($args)||$args==array()) return;
	$sql="INSERT INTO ".db_prefix('specialtysystem')." (spec_name,spec_colour,spec_shortdescription,spec_longdescription,modulename,fightnav_active,newday_active,dragonkill_active,dragonkill_minimum_requirement,stat_requirements,race_requirements,noaddskillpoints,basic_uses) VALUES ";
	$insert=array();
	foreach ($args as $key=>$data) {
		$insert[]=" ('".addslashes($data['spec_name'])."','{$data['spec_colour']}','".addslashes($data['spec_shortdescription'])."','".addslashes($data['spec_longdescription'])."','{$data['modulename']}','{$data['fightnav_active']}','{$data['newday_active']}','{$data['dragonkill_active']}','{$data['dragonkill_minimum_requirement']}','".addslashes(serialize($data['stat_requirements']))."','".addslashes(serialize($data['race_requirements']))."','{$data['noaddskillpoints']}','{$data['basic_uses']}')";
	}
	if ($insert==array()) return;
	debug($insert);
	$sql.=implode(",",$insert);
	return db_query($sql);
	/*usage:
	for first registering

	hook into specialtysystem-register
	case: specialtysystem-register
		$args[]=array(
			"spec_name"=>'Techniques',
			"spec_colour"=>'`!',
			"spec_shortdescription"=>'Healing';
			"spec_longdescription"=>'`5Growing up, you recall being ...',
			"modulename"=>'specialtysystem_nameofspec',
			"fightnav_active"=>'1',
			"newday_active"=>'0',
			"dragonkill-active"=>'0'
			);
	then call in the install:
	require_once("modules/specialtysystem/register.php");
	specialtysystem_register();
	*/
}

?>
