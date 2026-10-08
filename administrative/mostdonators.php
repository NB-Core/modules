<?php
//just did this mod to let users have more text


function mostdonators_getmoduleinfo(){
	$info = array(
	    "name"=>"Most Donators Ranking",
		"description"=>"This module will show the people who donated most on your server (total sums)",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"lotgd-downloads.com",
		);
    return $info;
}

function mostdonators_install(){
	module_addhook_priority("gardens",50);
	module_addhook_priority("rock",50);
	return true;
}

function mostdonators_uninstall() {
	return true;
}


function mostdonators_dohook($hookname, $args){
	global $session;
	switch ($hookname)
	{
	case "gardens":
		output("You see a really huge Stone where many names seem to be carved in it... quite a few spectators have gathered there.`n`n");
		addnav("Stone of Legends");
		addnav("Investigate","runmodule.php?module=mostdonators&op=gardens");
		break;
	case "rock":
		output("You see a Great Fire where many heroes have gathered.`n`n");
		addnav("The Great Fire");
		addnav("Sit down","runmodule.php?module=mostdonators&op=rock");
		break;
	default:

	break;
	}
	return $args;
}

function mostdonators_run(){
	global $session;
	$op=httpget('op');
	$sql="SELECT a.name as name,a.acctid as acctid,sum(b.amount-b.txfee) as netamount from ".db_prefix("accounts")." as a INNER JOIN ".db_prefix("paylog")." as b ON a.acctid=b.acctid WHERE b.acctid!=0 GROUP BY a.name,a.acctid ORDER BY netamount DESC LIMIT 10";
	$result=db_query($sql);
	switch($op) {
		case "rock":
			$row=db_fetch_assoc($result);
			page_header("The Great Fire");
			modulehook("header-rockoflegends",array());
			output("`^You step closer the Great Fire where many heroes sit around...`n`n");
			output("You listen to them a few hours... and they tell you many tales");
			output(" of their adventures and their travels. As the time passes on, many grow quiet.");
			if ($row) {
				output("`n`nSuddenly, one of them stands upright and looks into every man's and woman's eyes...");
				output("`n`n\"`@But despite all of our adventures, we all know, who the Greatest Of All is... it is...`n`n`b`c`i`Q%s`@!`b`i`c`n`n",$row['name']);
				output("... and nobody else! This warrior supported the realm with most effort.`^\".`n`n");
				output(" You hear murmur arising... but after a few seconds apparently all agree with a short nod.");
			} else {
				output("`n`nYou leave after a few hours...");
			}
			addnav("Veterans Lounge");
			addnav("Back to the Lounge","rock.php");
			page_footer();
			break;
		case "gardens":
			page_header("Stone Of Legends");
			modulehook("header-stoneoflegends",array());
			output("`^You step closer... to a really huge stone. Others around you admire something written on it.");
			output("`n`nAfter a few moments you are able to take a look. Names seem to be written in large, friendly letters ... they glow magically...");
			output(" someone whispers: \"`2So these are the names of great heroes... who helped to create and maintain this entire realm... the higher, the more they achieved...`^\".`n`n");
			rawoutput("<table cellpadding='3' cellspacing='0' border='0' align='center'>");
			output("You read what's written on the stone:`n`n");
			rawoutput("<tr class='trhead'><td>");
			output("`\$`c`b`iThe Greatest Heroes`i`b`c`@");
			rawoutput("</td></tr>");
			$i=0;
			while ($row=db_fetch_assoc($result)) {
				rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
				output("`c`i`@%s`@`i`c",$row['name']);
				rawoutput("</td></tr>");
				$i++;
				$rows[]=$row['name']." --> ".$row['netamount'];
			}
			rawoutput("</table>");
			if ($i==0) output("`i`c`bNone`b`c`i");
			addnav("Gardens");
			addnav("Back to the Gardens","gardens.php");
			page_footer();
			break;
	}
}
?>
