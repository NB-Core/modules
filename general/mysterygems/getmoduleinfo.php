<?php
$info = array
(
	"name"	=> "Gem's Eternal Mysteries (1.1)",
	"author"	=> "RPGee.com",
	"version"	=> "1.1",
	"category"	=> "RPGee.com",
	"download"	=> "http://www.rpgee.com/lotgd/mysterygems.zip",
	"vertxtloc"	=> "http://www.rpgee.com/lotgd/",		
	"settings"	=> array
	(
		"G.E.M. General, title",
		"runoncemount"		=> "Reduce number of days until mount returns only on server generated 
		 game days?, bool|1",
		"runonceused"		=> "Reset daily buying allowance only on server generated game days?, bool|1",
		"times"			=> "Maximum times allowed per day, int|1",
		"buymount"			=> "Block stables when mount is lost?, bool|1",
		"G.E.M. Pricing in Gold, title",
		"turquoisecost"		=> "Turquoise, int|40",
		"malachitecost"		=> "Malachite, int|58",
		"moonstonecost"		=> "Moonstone, int|76",
		"hematitecost"		=> "Hematite, int|94",
		"starsapphirecost"	=> "Star Sapphire, int|112",
		"diamondcost"		=> "Diamond, int|130",
		"levelmultiply"		=> "Multiply cost by character level?, bool|1",
		"G.E.M. Location, title",
		"mgloc"			=> "Location if shop does not move, location|".getsetting("villagename", 
		 LOCATION_FIELDS),
		"move"			=> "Shop moves?, bool|0",
		"runoncemove"		=> "Move only on server generated day?, bool|1",
		"place"			=> "Shop location today if it moves on server days, location|".getsetting
			("villagename", LOCATION_FIELDS),
	),
	"prefs"	=> array
	(
		"G.E.M. Preferences,title",
		"used"			=> "Gems bought today, int|0",
		"lostmount"			=> "Lost mount?, bool|0",
		"lostmountdays"		=> "Days until mount returns, int|0",
		"mountid"			=> "ID of mount, int|0",
		"userplace"			=> "Shop location if set to move each new day, location|".getsetting
		 ("villagename", LOCATION_FIELDS),
	)
);
?>