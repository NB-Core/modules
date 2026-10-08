<?php
$session['user']['gold'] -= $mac;
output("`!Malachite, beautiful stone!`0, Gem spurts out in a rush as he hands you the malachite.");
if ($mgresult == 1)
{
	$hpgain = round($umhp * e_rand(7,17) / 100);
	$session['user']['hitpoints'] += $hpgain;
	output
	(
		"That has to feel good - the malachite melds into your palm and you feel healthy.
		`n`n`@You have gained %s hit points!", $hpgain
	);
	debuglog("gained $hpgain hit points at G.E.M");
}
if ($mgresult == 2)
{
	$loss = round($umhp * e_rand(24,34) / 100);
	$session['user']['hitpoints'] -= $loss;
	output
	(
		"That has to feel horrible - the malachite melds into your palm and you feel sick and feeble.
		`n`n`@You have lost %s hit points!", $loss
	);
}
if ($mgresult == 3)
{
	$loss = round($umhp * e_rand(35,75) / 100);
	$session['user']['hitpoints'] -= $loss;
	output
	(
		"That has to feel horrible - the malachite melds into your palm and you feel sick and feeble.
		`n`n`@You have lost %s hit points!", $loss
	);
}
?>