<?php
$session['user']['gold'] -= $tc;
output("Gem reaches into the cabinet and removes the polished piece of turquoise. He wryly smiles and hands it to you.");
if ($mgresult == 1)
{
	$buff = unserialize($playermount['mountbuff']);
	if ($buff['schema'] == "") $buff['schema'] = "mounts";
	apply_buff('mount', $buff);
	output
	(
		"The turquoise radiates with a warmth that engulfs your hand. You hear your mount outside shift about. 
		Turning toward Gem, you see him gleefully grinning. `!`n`nYour mount is recharged and ready to fight!"
	);
}
elseif ($mgresult == 2)
{	
	$lostmountdaysrand = e_rand(2,3);
	set_module_pref('lostmount', 1);
	set_module_pref('lostmountdays', $lostmountdaysrand);
	set_module_pref('mountid', $session['user']['hashorse']);
	$session['user']['hashorse'] = 0;
	$buff = unserialize($playermount['mountbuff']);
	if ($buff['schema'] == "") $buff['schema'] = "mounts";
	strip_buff('mount', $buff);
	output
	(
		"The turquoise resonates in your palm and then with a sudden flash of light, 
		leaps from your grasp, crashes through the window and scares the willies out of your mount! 
		`n`n`@You've lost your mount for %s game days!", $lostmountdaysrand
	);
}
else
{
	$session['user']['hashorse'] = 0;
	$buff = unserialize($playermount['mountbuff']);
	if ($buff['schema'] == "") $buff['schema'] = "mounts";
	strip_buff('mount', $buff);
	output
	(
		"Gem grimaces as a strange turquoise hue emanates from the stone and engulfs the room and then moves outside. 
		You here the thud of your mount collapse like a sack of potatoes. `@OH NO! Your mount has died from the magic 
		of the stone!"
	);
}
?>