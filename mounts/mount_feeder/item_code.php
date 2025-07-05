<?php

global $session, $playermount;

if (isset($playermount['mountbuff'])) {
	$mountbuff = unserialize($playermount['mountbuff']);

	$rand = e_rand(1,5);

	if($rand == 1) {
		$mountbuff['rounds'] = round($mountbuff['rounds']*1.25);
		output("The feed has an extra kick, giving, %s more energy than normal!",$playermount['mountname']);
	} elseif($rand == 5){
		if(e_rand(1,10) == 1) {
			output("Their was something really bad in that feed, making, %s crazy, and start attacking you.",$playermount['mountname']);
			$mountbuff = array("startmsg"=>"Your crazed mount starts attacking you!",
					"name"=>"`TCrazed Mount",
					"rounds"=>10,
					"wearoff"=>"The effects wear off you mount, and they pass out.",
					"mingoodguydamage"=>1,
					"maxgoodguydamage"=>5,
					"minioncount"=>2,
					"effectmsg"=>"You suffer {damage} damage from your own mount!",
					"schema"=>"mounts"
					);
		} else {
			$mountbuff['rounds'] = round($mountbuff['rounds']*0.75);
			output("The feed has something off, making %s feel a little off, and not willing to stick around as long.",$playermount['mountname']);
		}
	} else {
		output("%s eats the feed with pleasure, returning to it's normal condition.",$playermount['mountname']);
	}	


	if ($mountbuff['schema'] == "") $mountbuff['schema']="mounts";

	apply_buff('mount',$mountbuff);
} else {
	output("Sorry, but it seems your companion is too tired... next time, feed it before it is exhausted...");
}

?>
