<?php
declare(strict_types=1);
//functions for the specsystem

/**
 * Calculate usable points for a specialty.
 *
 * @param string|null $modulename Target module or null for overall points
 * @return int
 */
function specialtysystem_availableuses(?string $modulename = null): int {
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

/**
 * Fetch accumulated skill points.
 *
 * @param string|null $modulename Module name or null for all
 * @return int
 */
function specialtysystem_getskillpoints(?string $modulename = null): int {
	require_once("modules/specialtysystem/datafunctions.php");
	$ret=0;
	if ($modulename==false) {
		$data=specialtysystem_get();
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
		if ($data!==false) $ret=$data['skillpoints'];
	}
	return $ret;
}

/**
 * Get number of used points for current day.
 *
 * @return int
 */
function specialtysystem_getuses(): int {
	$data2=get_module_pref("uses","specialtysystem");
	return $data2;
}

/**
 * Persist used points value.
 *
 * @param int $value
 */
function specialtysystem_setuses(int $value): void {
	set_module_pref("uses",$value,"specialtysystem");
	return;
}

/**
 * Increase uses for a specialty.
 *
 * @param string $modulename
 * @param int $value
 */
function specialtysystem_incrementuses(string $modulename, int $value): void {
	require_once("modules/specialtysystem/datafunctions.php");
	$uses=get_module_pref("uses","specialtysystem");
	if ($uses!='') $uses+=(int)$value;
		else $uses=$value;
	set_module_pref("uses",$uses,"specialtysystem");
	return;
}

/**
 * Add a section headline to the fight navigation collector.
 *
 * @param string $name
 * @param int|null $uses
 * @param int|null $max
 */
function specialtysystem_addfightheadline(string $name, ?int $uses = null, ?int $max = null): void {
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

/**
 * Append an entry to the fight navigation collector.
 *
 * @param string $name
 * @param string|null $link
 * @param int|null $uses
 */
function specialtysystem_addfightnav(string $name, ?string $link = null, ?int $uses = null): void {
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

/**
 * Return and reset the fight navigation collector.
 *
 * @return array
 */
function specialtysystem_getfightnav(): array {
	global $specialtycollector;
	$return=$specialtycollector;
	$specialtycollector=false;
	return $return;
}
?>
