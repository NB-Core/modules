<?php
$session['user']['gold'] -= $moc;
output("Gem reaches for the moonstone and then hands it to you with a chuckle.");
if ($mgresult == 1)
{
	$gemsgained = e_rand(1,3);
	$session['user']['gems'] += $gemsgained;
	output
	(
		"The moonstone illuminates the room in a brilliant luminesence. The light allows you to spy several 
		gems within the room that others missed.`n`n`@You have gained %s gems!", $gemsgained
	);
	debuglog("gained $gemsgained hit points at G.E.M");
}
if ($mgresult == 2)
{
	$gemslost = e_rand(3,6);
	$session['user']['gems'] -= $gemslost;
	output
	(
		"The moonstone trembles in your hand. Before you know it, your entire body quivers, shaking free several 
		gems unto the ground, which seemingly fade.`n`n`@You have lost %s gems!", $gemslost
);
}
if ($mgresult == 3)
{
	$gemslost = e_rand(10,15);
	$session['user']['gems'] -= $gemslost;
	output
	(
		"The moonstone trembles in your hand. Before you know it, your entire body quivers, shaking free several 
		gems unto the ground, which seemingly fade.`n`n`@You have lost %s gems!", $gemslost
	);
}
?>