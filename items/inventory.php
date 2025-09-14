<?php
// translator ready
// addnews ready
// mail ready

function inventory_getmoduleinfo(){
	$info = array(
		"name"=>"Inventory Basic System",
		"version"=>"2.0",
		"author"=>"Christian Rutsch",
		"category"=>"Items",
		"download"=>"http://www.dragonprime.net/users/XChrisX/itemsystem.zip",
		"override_forced_nav"=>true,
		"settings"=>array(
			"Inventory - Selling items, title",
				"sellgold"=>"how much gold does selling an item return? (percent), int|66",
				"sellgems"=>"how much gems does selling an item return? (percent), int|66",
			"Inventory - Carrying items, title",
				"limit"=>"How many items canbe carried by user?, range, 0,1000,1|0",
				"Note: Setting this to 0 will allow the user to carry a limitless amount of items, note",
				"weight"=>"Maximum weiht users can carry?, range,0,1000,1|0",
				"Note: Setting this to 0 will allow the user to carry a limitless weight of items, note",
				"droppable"=>"Items are droppable?, bool|1",
			"Inventory - Setup,title",
				"withcharstats"=>"Enable the charstat popup for the inventory,bool|0",
				"Please understand that this function is still in an early beta phase and not fully working!,note",
		),
		"prefs"=>array(
			"Inventory - Admin Prefs, title",
			"is_itemeditor"=>"User can edit Items,bool|0",
		)
	);
	return $info;
}
function inventory_install(){
	$item = db_prefix("item");
	$inventory = db_prefix("inventory");
	$itembuffs = db_prefix("itembuffs");

	// SQLs for creation of item-table

	$item_table = array(
		'itemid'=> array('name'=>'itemid', 'type'=>'int unsigned',	'extra'=>'auto_increment'),
		'class' => array('name'=>'class', 'type'=>'varchar(50)', 'null'=> '1',),
		'name' => array('name'=>'name', 'type'=>'varchar(50)', 'null'=>'0'),
		'description'  => array('name'=>'description', 'type'=>'text', 'null'=>'0'),
		'gold'=> array('name'=>'gold', 'type'=>'int unsigned', 'default'=>'0', 'null'=>'0'),
		'gems'=> array('name'=>'gems', 'type'=>'int unsigned', 'default'=>'0', 'null'=>'0'),
		'weight'=> array('name'=>'weight', 'type'=>'int unsigned', 'default'=>'0', 'null'=>'0'),
		'droppable'=> array('name'=>'droppable', 'type'=>'tinyint', 'default'=>'1', 'null'=>'0'),
		'level'=> array('name'=>'level', 'type'=>'tinyint unsigned', 'default'=>'1', 'null'=>'0'),
		'dragonkills'=> array('name'=>'dragonkills', 'type'=>'int unsigned', 'default'=>'0', 'null'=>'0'),
		'buffid'=> array('name'=>'buffid', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'charges'=> array('name'=>'charges', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'link'=> array('name'=>'link', 'type'=>'text', 'null'=>'0'),
		'hide'=> array('name'=>'hide', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'customvalue'=> array('name'=>'customvalue', 'type'=>'text', 'null'=>'0'),
/**/	'execvalue'=> array('name'=>'execvalue', 'type'=>'text', 'null'=>'0'),
/**/	'exectext'=> array('name'=>'exectext', 'type'=>'varchar(70)', 'null'=>'0'),
/**/	'noeffecttext'=> array('name'=>'noeffecttext', 'type'=>'varchar(70)', 'null'=>'0'),
		'activationhook'=> array('name'=>'activationhook', 'type'=>'varchar(50)', 'default'=>'0', 'null'=>'0'),
		'findchance'=> array('name'=>'findchance', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'loosechance'=> array('name'=>'loosechance', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'dkloosechance'=> array('name'=>'dkloosechance', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'),
		'sellable'=> array('name'=>'sellable', 'type'=>'tinyint(2)', 'default'=>'1', 'null'=>'0'),
		'buyable'=> array('name'=>'buyable', 'type'=>'tinyint(2)', 'default'=>'1', 'null'=>'0'),
/**/	'uniqueforserver'=> array('name'=>'uniqueforserver', 'type'=>'tinyint(2)', 'default'=>'0', 'null'=>'0'),
/**/	'uniqueforplayer'=> array('name'=>'uniqueforplayer', 'type'=>'tinyint(2)', 'default'=>'0', 'null'=>'0'),
/**/	'equippable'=> array('name'=>'equippable', 'type'=>'tinyint(2)', 'default'=>'0', 'null'=>'0'),
/**/	'equipwhere'=> array('name'=>'equipwhere', 'type'=>'varchar(15)', 'default'=>'', 'null'=>'0'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'itemid,name'));

	$inventory_table = array(
		'userid'=> array('name'=>'userid', 'type'=>'int unsigned'),
		'itemid' => array('name'=>'itemid', 'type'=>'int unsigned', 'null'=> '1',),
		'sellvaluegold' => array('name'=>'sellvaluegold', 'type'=>'int unsigned', 'null'=>'0'),
		'sellvaluegems' => array('name'=>'sellvaluegems', 'type'=>'int unsigned', 'null'=>'0'),
		'specialvalue' => array('name'=>'specialvalue', 'type'=>'text', 'null'=>'0'),
		'equipped' => array('name'=>'equipped', 'type'=>'tinyint(2)', 'default'=>0, 'null'=>'0'),
		'charges' => array('name'=>'charges', 'type'=>'tinyint', 'default'=>'0', 'null'=>'0'));

	$buff_table = array(
		'buffid'=> array('name'=>'buffid', 'type'=>'tinyint unsigned',	'null'=>'0', 'extra'=>'auto_increment'),
		'buffname'=> array('name'=>'buffname', 'type'=>'varchar(255)', 'null'=>'0'),
		'buffshortname'=> array('name'=>'buffshortname', 'type'=>'varchar(50)', 'null'=>'0'),
		'rounds'=> array('name'=>'rounds', 'type'=>'varchar(255)', 'null'=>'0'),
		'invulnerable'=> array('name'=>'invulnerable', 'type'=>'varchar(255)', 'null'=>'0'),
		'dmgmod'=> array('name'=>'dmgmod', 'type'=>'varchar(255)', 'null'=>'0'),
		'badguydmgmod'=> array('name'=>'badguydmgmod', 'type'=>'varchar(255)', 'null'=>'0'),
		'atkmod'=> array('name'=>'atkmod', 'type'=>'varchar(255)', 'null'=>'0'),
		'badguyatkmod'=> array('name'=>'badguyatkmod', 'type'=>'varchar(255)', 'null'=>'0'),
		'defmod'=> array('name'=>'defmod', 'type'=>'varchar(255)', 'null'=>'0'),
		'badguydefmod'=> array('name'=>'badguydefmod', 'type'=>'varchar(255)', 'null'=>'0'),
		'lifetap'=> array('name'=>'lifetap', 'type'=>'varchar(255)', 'null'=>'0'),
		'dmgshield'=> array('name'=>'dmgshield', 'type'=>'varchar(255)', 'null'=>'0'),
		'regen'=> array('name'=>'regen', 'type'=>'varchar(255)', 'null'=>'0'),
		'minioncount'=> array('name'=>'minioncount', 'type'=>'varchar(255)', 'null'=>'0'),
		'maxbadguydamage'=> array('name'=>'maxbadguydamage', 'type'=>'varchar(255)', 'null'=>'0'),
		'minbadguydamage'=> array('name'=>'minbadguydamage', 'type'=>'varchar(255)', 'null'=>'0'),
		'maxgoodguydamage'=> array('name'=>'maxgoodguydamage', 'type'=>'varchar(255)', 'null'=>'0'),
		'mingoodguydamage'=> array('name'=>'mingoodguydamage', 'type'=>'varchar(255)', 'null'=>'0'),
		'startmsg'=> array('name'=>'startmsg', 'type'=>'varchar(255)', 'null'=>'0'),
		'roundmsg'=> array('name'=>'roundmsg', 'type'=>'varchar(255)', 'null'=>'0'),
		'wearoff'=> array('name'=>'wearoff', 'type'=>'varchar(255)', 'null'=>'0'),
		'effectfailmsg'=> array('name'=>'effectfailmsg', 'type'=>'varchar(255)', 'null'=>'0'),
		'effectnodmgmsg'=> array('name'=>'effectnodmgmsg', 'type'=>'varchar(255)', 'null'=>'0'),
		'effectmsg'=> array('name'=>'effectmsg', 'type'=>'varchar(255)', 'null'=>'0'),
		'allowinpvp'=> array('name'=>'allowinpvp', 'type'=>'varchar(255)', 'null'=>'0'),
		'allowintrain'=> array('name'=>'allowintrain', 'type'=>'varchar(255)', 'null'=>'0'),
		'survivenewday'=> array('name'=>'survivenewday', 'type'=>'varchar(255)', 'null'=>'0'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'buffid'));


	require_once("lib/tabledescriptor.php");
	synctable($item, $item_table, true);
	synctable($inventory, $inventory_table, true);
	synctable($itembuffs, $buff_table, true);

	//module_addhook("bioinfo");
	module_addhook("footer-prefs");
	module_addhook("superuser");
	module_addhook("dragonkill");
	module_addhook("battle-defeat");
	module_addhook("delete_character");

	module_addhook("fightnav-specialties");
	module_addhook("apply-specialties");
	module_addhook("forest");
	module_addhook("village");
	module_addhook("shades");
	module_addhook("train");
	return true;
}

function inventory_uninstall(){
	require_once("modules/inventory/uninstall.php");
	return true;
}

function inventory_dohook($hookname,$args){
	require("modules/inventory/dohook/hook_$hookname.php");
	return $args;
}

function inventory_run(){
	require_once("modules/inventory/lib/itemhandler.php");

	$op=httpget('op');
	require_once("modules/inventory/run/case_$op.php");
}
?>
