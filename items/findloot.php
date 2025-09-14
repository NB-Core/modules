<?php

function findloot_getmoduleinfo(){
	$info = array(
		"name"=>"Find Loot",
		"author"=>"Christian Rutsch",
		"version"=>"1.01",
		"category"=>"Inventory",
		"download"=>"http://www.dragonprime.net/users/XChrisX/itemsystem.zip",
		"settings"=>array(
			"Find Loot - Settings,title",
			"category"=>"Category to find,text|loot",
			"wkchance"=>"Chance to find loot after a forest fight, range, 0, 100, 1|12",
			"tvchance"=>"Chance to find loot after a travel fight, range, 0, 100, 1|15",
		),
	);
	return $info;
}
function findloot_install(){
	module_addhook("battle-victory");
	debug("Please use the item-editor to create some items with the category 'Loot' in order to use this mod properly.");
	module_addhook("hw-preserve");
	return true;
	}
function findloot_uninstall(){
	return true;
}
function findloot_dohook($hookname,$args){
	global $session;
	require_once("modules/inventory/lib/itemhandler.php");

	switch ($hookname){
		case "battle-victory":
			$chance = e_rand(1,100);
			if (($args['type'] == 'forest' && $chance<=get_module_setting("wkchance")) || ($args['type'] == 'travel' && $chance<=get_module_setting("tvchance"))) {
				$category=get_module_setting('category');
				// Add a hook to modify this
				$category = array("categories"=>$category);
				$category = modulehook("findloot-categories",$category);
				$loot = get_random_item($category['categories']);
				// get_random_item() Will return false, when no matching item is found
				// and return a proper item-array, when one is found.
				if ($loot != false) {
					// Important: When handing over the itemid, make sure to hand it as real INT,
					// otherwise the function will search for an item named "1"
					if (add_item((int)$loot['itemid']))
						output("`^Searching the lifeless body of `4%s`^ you find a `7%s`^.`n", $args['creaturename'], $loot['name']);
				}
			}
			break;
	}
	return $args;
}

?>
