<?php
$month=(int) date("n");
$day=(int) date("j");
if ($month==12 && ($day>21 && $day<27)) {
	$coldhot=translate_inline("cold");
	$degree=e_rand(15,30);
} else {
	$coldhot=translate_inline("hot");
	$degree=e_rand(110,150);
}
require_once("sand.php");
$leaf="Konohagakure";
$water="Kirigakure";
$sand="Sunagakure";
$rock="Iwagakure";
$lightning="Kumogakure";
$sound="Otogakure";

//mind colours in names! they get sanitized, don't make 2 names the same! you will never be able to select the second one properly.
$races = array(
	"Leaf"=>array (
		"id"=>1,
		"city"=>$leaf,
		"racedesc"=>"In %s</a>, where the Leaf shinobi are from there's a little town called Konoha.`n`n",
		"setracedesc"=>"`^As an a Leaf village shinobi, you inherited the 'Will of Fire'.`nYou gain extra defense and are stronger!`n`n",
		"raceevalnewday"=>"apply_buff(\"racialbenefit\",array(
			\"name\"=>\"`@Ninja Leetness`0\",
			\"defmod\"=>\"(<defense>?(1+((1+floor(<level>/5))/<defense>)):1.1)\",
			\"tempstat-strength\"=>2,
			\"allowinpvp\"=>1,
			\"allowintrain\"=>1,
			\"rounds\"=>-1,
			\"schema\"=>\"module-racesystem\",
			)
		);",
		"text"=>array("`^`c`b%s, Ancestral Home of the Leaf `b`c`n`6You stand on the forest floor.  %s rises about you, appearing to be one with the forest.  Ancient frail-looking buildings appear to grow from the forest floor, the tree limbs, and on the very treetops.  The faces of the Hokages are high above these homes of the leaf nin.  Bright motes of fire swirl around you as you move about.`n", $leaf, $leaf),
		"clock"=>"`n`6Capturing one of the tiny lights, you peer delicately into your hands.`nThe fire within tells you that it is `^%s`6 before disappearing in a tiny sparkle.`n",
		"calendar"=>"`n`6Another nin whispers in your ear, \"`^Today is `&%3\$s %2\$s`^, `&%4\$s`^.  It is `&%1\$s`^.`6\"`n",
		"younewest"=>"`n`6You stare around in wonder at the excessively tall buildings and feel just a bit queasy at the prospect of looking down from those heights.",
		"newest"=>"`n`6Looking at the buildings high above, and looking a little queasy at the prospect of such heights is `^%s`6.",
		
		"title"=> array("%s", $leaf),
		"pvpadjust"=>"\$args['creaturedefense']+=(1+floor(\$args['creaturelevel']/5));",
		"adjuststats"=>"\$args['defense'] += (1+floor(\$args['level']/5));",			
		"sayline"=>"converses",
		"talk"=>"`n`^Nearby some villagers converse:`n",
		"gatenav"=>"Village Gates",
		"fightnav"=>"Honor Avenue",
		"marketnav"=>"Mercantile",
		"tavernnav"=>"Towering Halls",
		"colour"=>"`@",
		"name"=>"Leaf",
		),
	"Mist"=>array (
		"id"=>2,
		"city"=>$water,
		"racedesc"=>"High above the ocean of %s</a>, in frail looking elaborate `^Ninja`0 structures that look as though they might collapse under the slightest strain, yet have existed for decades.`n`n",
		"setracedesc"=>"`^As an Mist ninja, you are keenly aware of your surroundings at all times; very little ever catches you by surprise and have lightning reflexes.`n",
		"raceevalnewday"=>"			apply_buff(\"racialbenefit\",array(
			\"name\"=>\"`QMist Awareness`0\",
			\"defmod\"=>\"(<defense>?(1+((1+floor(<level>/5))/<defense>)):1.1)\",
			\"tempstat-dexterity\"=>2,
			\"allowinpvp\"=>1,
			\"allowintrain\"=>1,
			\"rounds\"=>-1,
			\"schema\"=>\"module-racemist\",
			)
		);",
		"text"=>array("`^`bYou make your way to the edge of a snow covered cliff. Looking down, you see a huge city with snowy rooftops, the heart of Water Country. Its beauty hides the endless civil wars that it has experienced. Inside the dark alleys of the city...the fight for survival between the poverty-stricken goes unnoticed.`n`nKirigakure is located here, protected by a dense forest of tall snowy pine trees. The village earned the nickname `4\"The Village Hidden In The Bloody Mist\"`^ because of its gruesome graduation exam in the past. The well-known Seven Shinobi Swordsmen of the Mist were born from this very village...`n", $water, $water),
		"clock"=>"`n`6As you make your way down the misty trail to the city, a lumberjack mumbles that it's `^%s`6 as he passes you by.`n",
		"calendar"=>"`n`6Another person whispers in your ear, \"`^Today is `&%3\$s %2\$s`^, `&%4\$s`^.  It is `&%1\$s`^.`6\"`n",
		"title"=> array("%s", $water),
		"pvpadjust"=>"\$args['creaturedefense']+=(1+floor(\$args['creaturelevel']/5));",
		"adjuststats"=>"\$args['defense'] += (1+floor(\$args['level']/5));",				
		"sayline"=>"converses",
		"talk"=>"`n`^Nearby some villagers converse:`n",
		"younewest"=>"`n`6You stare around in wonder at the vast ocean in front of you and feel just a bit queasy at the prospect of being with water all the time.",
		"newest"=>"`n`6Looking at the ocean, a little queasy, is `^%s`6.",
		"gatenav"=>"Village Gates",
		"fightnav"=>"Honor Avenue",
		"stablename"=>"Beasts",
		"stable"=>get_stable_sand(),
		"mounts"=>1,
		"marketnav"=>"Mercantile",
		"tavernnav"=>"Towering Halls",
		"colour"=>"`1",
		"name"=>"Mist",			
		),			
	"Sand"=>array (
		"id"=>3,
		"city"=>$sand,
		"racedesc"=>"The Sand nin</a> are found within %s, from the Hidden Village of the Desert, quite obvious where they got the name by the looks of it.`n`n",
		"setracedesc"=>"`&As a Sand, being used to walking with the devilish sand in your face, and being no longer hindered by it at the moment, you tire much less quickly than usual.`n`^You gain an extra forest fight each day and have a higher constitution!",
		"raceevalnewday"=>"\$args['turnstoday'] .= \", Race (sand): 1\";
		\$session['user']['turns']++;
		apply_buff(\"racialbenefit\",array(
			\"name\"=>\"`QSand Sturdiness`0\",
			\"tempstat-constitution\"=>2,
			\"allowinpvp\"=>1,
			\"allowintrain\"=>1,
			\"rounds\"=>-1,
			\"schema\"=>\"module-racemist\",
			)
		);
		output(\"`n`&Because you are from the Sand Country, you gain `^an extra`& forest fight for today!`n`0\");
		",
		"text"=>array("`&`c`b%s`c`b... this stronghold of ninjas is little more than a fortified village.  The place is practically buried with sand, and man, it's %s... You take a look at the thermometer, indicating it is %s degrees Fahrenheit. No wonder.`n", $sand, $coldhot, $degree),
		"clock"=>"`n`7The great town clock at the heart of the city reads `&%s`7.`n",
		"calendar"=>"`n`7A smaller contraption next to it reads `&%s`7, `&%s %s %s`7.`n",
		"title"=> array("%s Land of the Desert", $sand),
		"sayline"=>"says",
		"talk"=>"`n`&Nearby some villagers talk:`n",
		"younewest"=>"`n`7As you wander your new home, you feel your jaw dropping at the wonders around you.",
		"newest"=>"`n`7Wandering the village, jaw agape, is `&%s`7.",
		"gatenav"=>"Village Gates",
		"fightnav"=>"Honor Avenue",
		"marketnav"=>"Mercantile",
		"tavernnav"=>"Towering Halls",
		"colour"=>"`g",
		"name"=>"Sand",			
		),
	"Rock"=>array (
		"id"=>4,
		"city"=>$rock,
		"racedesc"=>"%s</a>, where the $rock shinobi serve the sinister purposes of the Tsuchikage`n`n",
		"setracedesc"=>"`^As an $rock village shinobi, you were born to attack in your Tsuchikage's name and are skilled in the art of deception.`nYou gain extra attack and are wiser in your judgement than the common shinobi!`n",
		"raceevalnewday"=>"	apply_buff(\"racialbenefit\",array(
			\"name\"=>\"`)$rock Stubborness`0\",
			\"atkmod\"=>\"(<defense>?(1+((1+floor(<level>/7))/<defense>)):1.1)\",
			\"tempstat-wisdom\"=>2,			
			\"allowinpvp\"=>1,
			\"allowintrain\"=>1,
			\"rounds\"=>-1,
			\"schema\"=>\"module-racerock\",
			)
		);",
		"text"=>array("`^`c`b%s, Secret Home of the Rock `b`c`n`6You stand at the hidden entrance of this village.  %s rises about you, appearing to be one with the surroundings. You sneak in silently... as all visitors do so in this forbidden village...`n", $rock, $rock),
		"clock"=>"`n`6You hear someone screaming.`nThe screams seem to tell you that it is `^%s`6 before they disappear in nothingness.`n",
		"calendar"=>"`n`6A cloaked woman whispers in your ear, \"`^Today is `&%3\$s %2\$s`^, `&%4\$s`^.  It is `&%1\$s`^.`6\"`n",
		"title"=> array("%s", $rock),
		"sayline"=>"whispers",
		"talk"=>"`n`^Nearby some cloaked nin whisper:`n",
		"younewest"=>"`n`6You stare around in wonder at the secret buildings and feel just a bit queasy at the prospect of staying here.",
		"newest"=>"`n`6Looking at the hidden buildings around you, and looking a little queasy at the prospect of such secrecy is `^%s`6.",
		"gatenav"=>"Village Gates",
		"fightnav"=>"Orochimaru Avenue",
		"marketnav"=>"Mercantile",
		"tavernnav"=>"Towering Halls",
		"colour"=>"`L",
		"name"=>"Rock",			
		),			
	"Lightning"=>array (
		"id"=>5,
		"city"=>$lightning,
		"racedesc"=>"%s</a>, where the $lightning shinobi serve one of the five great Shinobi Nations to resist the evil Akatsuki. `n`n",
		"setracedesc"=>"`^As an a $lightning village shinobi, you were born to outsmart your enemies and are skilled in the art of warfare.`nYou gain extra attack and are smarter than the common shinobi!`n",
		"raceevalnewday"=>"	apply_buff(\"racialbenefit\",array(
			\"name\"=>\"`)$lightning Fierceness`0\",
			\"atkmod\"=>\"(<attack>?(1+((1+floor(<level>/7))/<attack>)):1.1)\",
			\"tempstat-intelligence\"=>2,			
			\"allowinpvp\"=>1,
			\"allowintrain\"=>1,
			\"rounds\"=>-1,
			\"schema\"=>\"module-racelightning\",
			)
		);",
		"text"=>array("`^`c`b%s, Home of the Lightning `b`c`n`6You stand on the hidden entrance of this village.  %s rises about you, appearing to be one with the surrounding. You enter silently and evade the many nin protecting the city... `n", $lightning, $sound),
		"clock"=>"`n`6You hear someone telling the time: It seems to be `^%s`6 at present.`n",
		"calendar"=>"`n`6A shadowy herold shouts, \"`^Today is `&%3\$s %2\$s`^, `&%4\$s`^.  It is `&%1\$s`^.`6\"`n",
		"title"=> array("%s", $lightning),
		"sayline"=>"says",
		"talk"=>"`n`^Nearby some nin parlay:`n",
		"younewest"=>"`n`6You stare around in wonder at the high buildings and feel just a bit queasy at the prospect of staying here.",
		"newest"=>"`n`6Looking at the great buildings around you, and looking a little queasy at the prospect of such secrecy is `^%s`6.",
		"gatenav"=>"Village Gates",
		"fightnav"=>"Raikage Avenue",
		"marketnav"=>"Wealthy Lane",
		"tavernnav"=>"Towering Halls",
		"colour"=>"`x",
		"name"=>"Lightning",			
		),
	"Sound"=>array (
		"id"=>6,
		"city"=>$sound,
		"racedesc"=>"%s</a>, where the sound shinobi serve as underlings of Orochimaru.`n`n",
		"setracedesc"=>"`^As an a sound village shinobi, you were born to attack in Orochimarus name and are skilled in the art of deceipt.`nYou gain extra attack and are smarter than the common shinobi!`n",
		"raceevalnewday"=>"	apply_buff(\"racialbenefit\",array(
			\"name\"=>\"`)Sound Fierceness`0\",
			\"atkmod\"=>\"(<attack>?(1+((1+floor(<level>/7))/<attack>)):1.1)\",
			\"tempstat-intelligence\"=>2,			
			\"allowinpvp\"=>1,
			\"allowintrain\"=>1,
			\"rounds\"=>-1,
			\"schema\"=>\"module-racesound\",
			)
		);",
		"text"=>array("`^`c`b%s, Secret Home of the Sound `b`c`n`6You stand on the hidden entrance of this village.  %s rises about you, appearing to be one with the surrounding. You sneak in silently... as all visitors do so in this forbidden Sound village...`n", $sound, $sound),
		"clock"=>"`n`6You hear someone screaming.`nThe screams seem to tell you that it is `^%s`6 before they disappear in nothingness.`n",
		"calendar"=>"`n`6A cloaked woman whispers in your ear, \"`^Today is `&%3\$s %2\$s`^, `&%4\$s`^.  It is `&%1\$s`^.`6\"`n",
		"title"=> array("%s", $sound),
		"sayline"=>"whispers",
		"talk"=>"`n`^Nearby some cloaked nin whisper:`n",
		"younewest"=>"`n`6You stare around in wonder at the secret buildings and feel just a bit queasy at the prospect of staying here.",
		"newest"=>"`n`6Looking at the hidden buildings around you, and looking a little queasy at the prospect of such secrecy is `^%s`6.",
		"gatenav"=>"Village Gates",
		"fightnav"=>"Orochimaru Avenue",
		"marketnav"=>"Mercantile",
		"tavernnav"=>"Towering Halls",
		"colour"=>"`L",
		"name"=>"Sound",
		),
	);	


	
?>
