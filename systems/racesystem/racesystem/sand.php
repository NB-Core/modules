<?php

function get_stable_sand() {
	$return= array(
	"title" => "Bertold's Bestiary",
	"desc"=>"`6Just outside the outskirts of the village, a training area and riding range has been set up.  Many people from all across the land mingle as Bertold, a strapping man with a wind-weathered face, extols the virtues of each of the creatures in his care.  After a certain war incident, Bertold, who lived all his life in Wind Country, was forced to re-open his business in Water Country. `n`nAs you approach, Bertold smiles broadly, \"`^Ahh! how can I help you today?`6\" he asks in a booming voice.",
	"lad"=>"friend",
	"lass"=>"friend",
	"nosuchbeast"=>"`6\"`^I'm sorry, I don't stock any such animal.`6\", Bertold say apologetically.",
	"finebeast"=>array(
		"`6\"`^Yes, yes, that's one of my finest beasts!`6\" says Bertold.`n`n",
		"`6\"`^Not even Orochimaru has a finer specimen than this!`6\" Bertold boasts.`n`n",
		"`6\"`^Doesn't this one have fine musculature?`6\" he asks.`n`n",
		"`6\"`^You'll not find a better trained creature in all the land!`6\" exclaims Bertold.`n`n",
		"`6\"`^And a bargain this one'd be at twice the price!`6\" booms Bertold.`n`n",
		),
	"toolittle"=>"`6Bertold looks over the gold and gems you offer and turns up his nose, \"`^Obviously you misheard my price.  This %s will cost you `&%s `^gold  and `%%s`^ gems and not a penny less.`6\"",
	"replacemount"=>"`6Patting %s`6 on the rump, you hand the reins as well as the money for your new creature, and Bertold hands you the reins of a `&%s`6.",
	"newmount"=>"`6You hand over the money for your new creature, and Bertold hands you the reins of a new `&%s`6.",
	"nofeed"=>"`6\"`^I'm terribly sorry %s, but I don't stock feed here.  I'm not a common stable after all!  Perhaps you should look elsewhere to feed your creature.`6\"",
	"nothungry"=>"`&%s`6 picks briefly at the food and then ignores it.  Bertold, being honest, shakes his head and hands you back your gold.",
	"halfhungry"=>"`&%s`6 dives into the provided food and gets through about half of it before stopping.  \"`^Well, %s wasn't as hungry as you thought.`6\" says Bertold as he hands you back all but %s gold.",
	"hungry"=>"`6%s`6 seems to inhale the food provided.  %s`6, the greedy creature that it is, then goes snuffling at Bertold's pockets for more food.`nBertold shakes his head in amusement and collects `&%s`6 gold from you.",
	"mountfull"=>"`n`6\"`^Well, %s, your %s`^ is full up now.  Come back tomorrow if it hungers again, and I'll be happy to sell you more.`6\" says Bertold with a genial smile.",
	"nofeedgold"=>"`6\"`^I'm sorry, but that is just not enough money to pay for food here.`6\"  Bertold turns his back on you, and you lead %s away to find other places for feeding.",
	"confirmsale"=>"`n`n`6Bertold eyes your mount up and down, checking it over carefully.  \"`^Are you quite sure you wish to part with this creature?`6\"",
	"mountsold"=>"`6With but a single tear, you hand over the reins to your %s`6 to Bertold's stableboy.  The tear dries quickly, and the %s in hand helps you quickly overcome your sorrow.",
	"offer"=>"`n`n`6Bertold strokes your creature's flank and offers you `&%s`6 gold and `%%s`6 gems for %s`6.",
	);
	return $return;
}

?>