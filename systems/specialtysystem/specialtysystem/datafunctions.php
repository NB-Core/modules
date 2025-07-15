<?php
declare(strict_types=1);
/**
 * Store specialty data for a user.
 *
 * @param array $array Key/value pairs of module data
 * @param int|null $user Optional user id, null for current user
 */
function specialtysystem_set(array $array, ?int $user = null): void {
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

/**
 * Reset specialty data on new day.
 */
function specialtysystem_newday(): void {
	global $session;
	set_module_pref("uses",0,"specialtysystem");
	$session['user']['specialmisc']='';
        return;
}

/**
 * Retrieve registered specialties.
 *
 * @param string|null $modulename Specific module name or null for all
 * @return array<string, mixed>
 */
function specialtysystem_getspecs(?string $modulename = null): array {
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

/**
 * Increase skill points for a specialty.
 *
 * @param string $modulename Module identifier
 * @param int $value Amount to increment
 * @return int New skill point total
 */
function specialtysystem_increment(string $modulename, int $value = 1): int {
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

/**
 * Get stored specialty data for a user.
 *
 * @param string|null $modulename Module name or null for all
 * @param int|null $user User id or null for current user
 * @return array|null
 */
function specialtysystem_get(?string $modulename = null, ?int $user = null): ?array {
	$get=unserialize(stripslashes(get_module_pref("data","specialtysystem",$user)));
	if ($modulename==false) return $get;
	if (isset($get[$modulename]) && $get[$modulename]!='')
                return $get[$modulename];
                else
                return null;
}
?>
