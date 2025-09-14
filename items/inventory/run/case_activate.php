<?php
	$id = httpget('id');
	$item = get_item((int)$id);
	$return = httpget('return');
	$return = cmd_sanitize($return);
	$return = cmd_sanitize($return);
    if (strpos($return, "/") !== false) {
        $return = substr($return, strrpos($return, "/") + 1);
    }
	if (strpos($return, "forest.php") !== false) $return= "forest.php";
	if (strpos($return, "village.php") !== false) $return= "village.php";
	$item = get_inventory_item((int)$id);
	if (isset($item['charges']) && $item['charges'] > 1)
		uncharge_item((int)$id);
	else
		remove_item((int)$id);
	require_once("lib/buffs.php");
	if (isset($item['buffid']) && $item['buffid'] <> 0)
		apply_buff($item['name'], get_buff($item['buffid']));
	if (isset($item['execvalue']) && $item['execvalue'] > "") {
		page_header("Items");
		if ($item['exectext'] > "") {
			output($item['exectext'], $item['name']);
		} else {
			output("You activate %s!", $item['name']);
		}
		require_once("modules/inventory/lib/itemeffects.php");
		$text = get_effect($item, $item['noeffecttext']);
		output_notl("`n`n%s", $text);
		addnav("Return whence you came", $return);
		page_footer();
	} else {
		redirect($return);
	}
?>
