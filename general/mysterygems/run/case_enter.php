<?php
/*
if ($used < $times)
{
	addnav("In the Glass Chest");
	if ($ugo >= $tc && get_module_setting('lostmount') < 1 && $session['user']['hashorse'] && !$session['bufflist']['mount']['suspended'])
	{
		addnav(array("`3Turquoise`0.........(`^%s gold`0)", $tc), "runmodule.php?module=mysterygems&op=turquoise");
		$greet = 1;
	}
	if ($ugo >= $mac && $uhp >= $umhp * .80)
	{
		addnav(array("`2Malachite`0..........(`^%s gold`0)", $mac), "runmodule.php?module=mysterygems&op=malachite");
		$greet = 1;
	}
	if ($ugo >= $moc && $uge > 14)
	{
		addnav(array("`7Moonstone`0.......(`^%s gold`0)", $moc), "runmodule.php?module=mysterygems&op=moonstone");
		$greet = 1;
	}
	if ($ugo >= $hc && $utu > 14)
	{
		addnav(array("`)Hematite`0..........(`^%s gold`0)", $hc), "runmodule.php?module=mysterygems&op=hematite");
		$greet = 1;
	}
	if ($ugo >= $sc)
	{
		addnav(array("`1Star Sapphire`0...(`^%s gold`0)", $sc), "runmodule.php?module=mysterygems&op=starsapphire");
		$greet = 1;
	}
	if ($ugo >= $dc && $udp > 149)
	{
		addnav(array("`&Diamond`0.........(`^%s gold`0)", $dc), "runmodule.php?module=mysterygems&op=diamond");
		$greet = 1;
	}
}*/
if ($greet == 1)
{
	output
	(
		"`^You enter the strange shop. It's best described as a high fashioned soothsayer's shop,
		but with a myriad of gems, jewels and baubles displayed within gold adorned cases and shelves.
		`n`nSeemingly springing out of nowhere with a flamboyant turn of toe and wisp of glittery fabric 
		comes Gem. He's as spry as ever with that silly looking grin on his face. What, is he wearing makeup 
		today?`n`n
		Somehow, you just now realize the many boxes around you and the other stuff who lies around. It seems he is up to something."
	);
	rawoutput
	(
		"<p id=\"mysterygems-closure\" data-rainbow-banner>Sorry, pal, we are closing!</p>"
	);
	output_notl('<script src="modules/merry_xmas/assets/rainbow-banner.js" defer></script>', true);
	output
	(
		"`^He exclaims in his undying giddiness. \"`vSorry, dear customer, but we are closing and moving to another location. I thank you for visiting.`n`n"
	);
}
else output("You try to enter the shop, but find that it's locked. Gem sure is a finnicky kind of guy.");
addnav("Prance Out");
villagenav();
?>
