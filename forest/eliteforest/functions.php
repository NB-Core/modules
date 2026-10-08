<?php

require_once("lib/villagenav.php");

function eliteforest($noshowmessage=false) {
	global $session,$playermount;
	tlschema("eliteforest");
//	mass_module_prepare(array("forest", "validforestloc"));
	addnav("Navigation");
	villagenav();
	addnav("Fight");
//	addnav("L?Look for Something to Kill","runmodule.php?module=eliteforest&op=search");
//	addnav("T?Go Thrillseeking","runmodule.php?module=eliteforest&op=search&type=thrill");
	addnav("Face Kyuubi","runmodule.php?module=eliteforest&op=search&enemytype=0");
	addnav("Face Kyuubi `\$MKII","runmodule.php?module=eliteforest&op=search&enemytype=1");

	addnav("Other");

	if ($noshowmessage!=true){
		output("`c`7`bThe Forest`b`0`c");
		output("`2The Elite Forest Arena, home to evil creatures and evildoers of all sorts.`n`n");
		output("You enter a vast forest where you have no idea what evil might present itself to you... but you know one thing: it will be one of the most horrible things you've ever faced.");
		output("You move as silently as a soft breeze across the thick moss covering the ground, wary to avoid stepping on a twig or any of the numerous pieces of bleached bone that populate the forest floor, lest you betray your presence to one of the vile beasts that wander the forest.`n");
		modulehook("eliteforest-desc");
	}
	modulehook("elite", array());
	module_display_events("eliteforest","runmodule.php?module=eliteforest&op=");
	tlschema();
}

?>
