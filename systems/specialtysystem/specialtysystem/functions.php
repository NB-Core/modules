<?php
//functions for the specsystem

function specialtysystem_availableuses($modulename=false) {
	$upper=specialtysystem_getskillpoints();
	$lower=specialtysystem_getskillpoints($modulename);
	$uses=specialtysystem_getuses();
	if ($modulename==false) {
		$av=$upper-$uses;
	} else {
		$rest=$upper-$uses;
		$av=($rest>$lower?$lower:$rest);
	}
	return $av;
}

function specialtysystem_getskillpoints($modulename=false) {
	require_once("modules/specialtysystem/datafunctions.php");
	if ($modulename==false) {
		$data=specialtysystem_get();
		$ret=0;
		if (!is_array($data)) return 0;
		foreach ($data as $key=>$value) {
			//$value=unserialize($value);
			if (!is_array($value)) continue;
			if (array_key_exists('noaddskillpoints',$value)==false) $value['noaddskillpoints']=0;
			if ($value['noaddskillpoints']>0) $value['skillpoints']=max(0,$value['skillpoints']-$value['noaddskillpoints']);;
			// do not add points if  he is not supposed to get them from that spec
			$ret+=(int) $value['skillpoints'];
		}//debug($ret);
	} else {
		$data=specialtysystem_get($modulename);//debug("SKILL2");debug($data);
		$ret=$data['skillpoints'];
	}
	return $ret;
}

function specialtysystem_getuses() {
	$data2=get_module_pref("uses","specialtysystem");
	return $data2;
}

function specialtysystem_setuses($value) {
	set_module_pref("uses",$value,"specialtysystem");
	return;
}

function specialtysystem_incrementuses($modulename,$value) {
	require_once("modules/specialtysystem/datafunctions.php");
	$uses=get_module_pref("uses","specialtysystem");
	if ($uses!='') $uses+=(int)$value;
		else $uses=$value;
	set_module_pref("uses",$uses,"specialtysystem");
	return;
}

function specialtysystem_addfightheadline($name,$uses=false,$max=false) {
	global $specialtycollector;
	if (!is_array($specialtycollector)) $specialtycollector=array();
	if ($uses!=false) {
		$header=sprintf_translate("$name (%s/%s points)`0",$uses,$max);
	} else {
		$header=translate_inline($name)."`0";
	}
	$specialtycollector[]=array('headline'=>$header); //Each header makes a new array in Specialtycollector.
	return;
}

function specialtysystem_addfightnav($name,$link=false,$uses=false) {
	global $specialtycollector;
	if (is_array('name')) {
		$name=sprintf_translate($name);
	} elseif ($uses) {
		$name=sprintf_translate(" > %s`7 (%s)",$name,$uses);

	}else {
		$name=translate_inline($name);
	}
	if (!is_array($specialtycollector) && $link=false) {
		$specialtycollector[end($specialtycollector)][]=array($name);//Makes sure it adds it to the last array created.
	} else {
		$specialtycollector[]=implode("|||",array($name,$link));
	}
	return;
}

function specialtysystem_getfightnav() {
	global $specialtycollector;
	$return=$specialtycollector;
	$specialtycollector=false;
	return $return;
}
?>
