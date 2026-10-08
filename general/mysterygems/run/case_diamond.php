<?php
$session['user']['gold'] -= $dc;
output("Gem pulls out your diamond and hands it to you with a smile telling you to come back and visit another time.");
if ($mgresult == 1)
{
	$favorgained = e_rand(10,100);
	$session['user']['deathpower'] += $favorgained;
	output
	(
		"Gem dives behind the counter as the room rumbles in a dusty downfall of shakes. 
		You could have sworn you saw Death for a moment. The experience leaves you in favor with the Underworld.
		`n`n`@You have gained %s Favor!", $favorgained
	);
	debuglog("received $favorgained favor at G.E.M.");
}
if ($mgresult == 2)
{
	$favorlost = e_rand(100,150);
	$session['user']['deathpower'] -= $favorlost;
	output
	(
		"Gem dives behind the counter as the room rumbles in a dusty downfall of shakes. 
		The fear of death overwhelms you as you run screaming around the room.
		`n`n`@You have lost %s Favor!", $favorlost
	);
}
if ($mgresult == 3)
{
	$favorlost = e_rand(50,150);
	$session['user']['deathpower']	-= $favorlost;
	$session['user']['hitpoints']	= 0;
	$session['user']['alive']		= 0;
	$session['user']['gold']		= 0;
	//$session['user']['gems']		= 0;
	output
	(
		"Gem dives behind the counter as the room rumbles in a dusty downfall of shakes. 
		The Reaper looks ticked that you bothered him and raises his sickle. 
		With one fell swoop, he puts an end to your misery and you fade into the shades.
		`n`n`!You have DIED, lost %s Favor, and all gems and gold on hand!", $favorlost
	);
	debuglog("Died, lost all gold on hand, and $favorlost at G.E.M.");
}
?>
