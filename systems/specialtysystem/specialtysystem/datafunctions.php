<?php
function specialtysystem_set($array,$user=false) {
	$get=unserialize(stripslashes(get_module_pref("data","specialtysystem",$user)));
	if (!is_array($get))$get=array();
	foreach ($array as $modulename=>$data) {
		if (array_key_exists($modulename,$get)) {
			$get[$modulename]=$data;
		} else	{
			$exist=array($modulename=>$data);
			$get=array_merge($get,$exist);
		}
	}
	set_module_pref("data",addslashes(serialize($get)),"specialtysystem",$user);
	return;
}

function specialtysystem_newday() {
	global $session;
	set_module_pref("uses",0,"specialtysystem");
	$session['user']['specialmisc']='';
	return;
}

function specialtysystem_getspecs($modulename=false) {
	if (($spec=datacache("specialtygetspecs",3600))!=false) {
		if ($modulename==false) {
				return unserialize(stripslashes($spec));
			} else {
				$ret=unserialize(stripslashes($spec));
				return array($modulename=>$ret[$modulename]);
			}
	}
	if ($modulename!=false) $where=" WHERE modulename='$modulename';";
		else $where='';
	$sql="SELECT * FROM ".db_prefix('specialtysystem').$where;
	$result=db_query($sql);
	$spec=array();
	while ($row=db_fetch_assoc($result))
		$spec[$row['modulename']]=$row;
	if ($modulename==false) updatedatacache("specialtygetspecs",addslashes(serialize($spec)));
	return $spec;
}

function specialtysystem_increment($modulename,$value=1) {
	$get=unserialize(stripslashes(get_module_pref("data","specialtysystem")));
	if (array_key_exists($modulename,$get)) {
		$exist=$get[$modulename];
		$exist['skillpoints']+=$value;
		$get[$modulename]=array_unique($exist);
	} else	{
		$exist=array("skillpoints"=>$value);
		$get[$modulename]=$exist;
	}
	set_module_pref("data",addslashes(serialize($get)),"specialtysystem");
	return $exist['skillpoints'];
}

function specialtysystem_get($modulename=false,$user=false){
	$get=unserialize(stripslashes(get_module_pref("data","specialtysystem",$user)));
	if ($modulename==false) return $get;
	if ($get[$modulename]!='')
		return $get[$modulename];
		else
		return false;
}
?>