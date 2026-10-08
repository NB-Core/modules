<?php
$session['user']['gold'] -= $sc;
output("`!Oh, aren't you fancy?! I like that!`0, proclaims Gem as he hands you the Star Sapphire");
if ($mgresult == 1)
{
	$expgained = round(e_rand(1,5) / 100 * $uexp);
	$session['user']['experience'] += $expgained;
	output
	(
		"Gem grins from ear to ear as you feel the star sapphire take it's hold.
		`n`n`#Just as you thought you would pass out, your mind clicks and you gain %s experience!", $expgained
	);
	debuglog("received $expgained experience at G.E.M.");
}
if ($mgresult == 2)
{
	$explost = round(e_rand(1,10) / 100 * $uexp);
	$session['user']['experience'] -= $explost;
	output
	(
		"Gem wrinkles his nose as you feel the star sapphire take it's hold.
		`n`n`#Just as you thought you would pass out, your mind crashes and you lose %s experience!", $explost
	);
}
if ($mgresult == 3)
{
	$explost = round(e_rand(10,34) / 100 * $uexp);
	$session['user']['experience'] -= $explost;
	output
	(
		"Gem wrinkles his nose as you feel the star sapphire take it's hold.
		`n`n`#Just as you thought you would pass out, your mind crashes and you lose %s experience!", $explost
	);
}
?>