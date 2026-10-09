<?php
if (get_module_setting('runoncemove') && get_module_setting('move'))
{
	$vloc 		= array();
	$vname 		= getsetting("villagename", LOCATION_FIELDS);
	$vloc[$vname] 	= "village";
	$vloc 		= modulehook("validlocation", $vloc);
	$key 			= array_rand($vloc);
	set_module_setting("place", $key);
}
if (get_module_setting('runoncemount'))
{
	$sql = "update ".db_prefix("module_userprefs")." set value=value-1 where value>0 and setting='lostmountdays'
	 and modulename='mysterygems'";
	db_query($sql);
}
if (get_module_setting('runonceused'))
{
	$sql = "update ".db_prefix("module_userprefs")." set value=0 where value<>0 and setting='used'
	 and modulename='mysterygems'";
	db_query($sql);
}
?>