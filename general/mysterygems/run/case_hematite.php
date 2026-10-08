<?php
$session['user']['gold'] -= $hc;
output
(
	"Gem winks at you as you feel your body swirl, but not move at all. Your head spins and your eyes roll 
	to the back of your head."
);
if ($mgresult == 1)
{
	apply_buff('mysgems', array
	(
		"startmsg"		=> "`\$Your hematite health kicks in...",
		"name"		=> "`\$Hematite Health",
		"rounds"		=> 20,
		"wearoff"		=> "Your hematite health has faded...",
		"regen"		=> $umhp * $ul / 150,
		"effectmsg"		=> "Your hematite glows and you regenerate for {damage} health.",
		"effectnodmgmsg"	=> "Your hematite glows, but you have no wounds to regenerate.",
		"schema"		=> "module-mysterygems"
		)
	);
	output("`n`nWhen you come out of the trance, you feel invigorated!`n`n`@You have gained Hematite Health!");
}
if ($mgresult == 2)
{
	$turnslost = e_rand(3,7);
	$session['user']['turns'] -= $turnslost;
	output("`n`nWhen you come out of the trance, you feel dizzy and sick!`n`n`@You have lost %s turns!", $turnslost);
}
if ($mgresult == 3)
{
	$session['user']['turns'] = 0;
	output
	(
		"`n`nWhen you come out of the trance, you feel like you're going to puke!
		`n`n`@You have lost all of your turns for the day!"
	);
}
?>