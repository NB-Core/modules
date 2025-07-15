<?php
declare(strict_types=1);

/**
 * Remove a specialty module from the registry.
 *
 * @param string $modulename
 */
function specialtysystem_uninstall(string $modulename): void { // the array itself will stay with the skillpoints... so they can't use that skill in battle, but still have the chakra points.
	$sql="DELETE FROM ".db_prefix('specialtysystem')." WHERE modulename='$modulename'";
	db_query($sql);
}
?>
