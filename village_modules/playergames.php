<?php
/*
v 1.01 now has a cleanup function for old games (no notification yet)
*/
function playergames_getmoduleinfo(){
$info = array(
	"name"=>"Player Games",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"category"=>"Games",
	"download"=>"http://lotgd-downloads.com",
	"settings"=> array (
		"showinall"=>"Show in all villages?,bool|1",
		"Note: If you activate the above the choice of the town below is not used,note",
		"location"=>"Where is the Parlor stationed?,location|".getsetting("villagename", LOCATION_FIELDS),
		"owner"=>"Name of the owner,text|Cheeky",
		"genderowner"=>"Is the user female?,bool|1",
		"generalcost"=>"How much is the general game fee?,int|50",
		"maxgames"=>"How many games are allowed (Server performance, 0 means unlimited),int|10",
		"Note: The level does not multiply as only the hoster pays for the table,note",
		"expiration"=>"After how many days are games automatically closed (regardless if active or not (0=infinite),int|10",
		),
	);
	return $info;
}

function playergames_install(){
	module_addhook("village");
	module_addhook("newday-runonce");
	$playergames=array(
		'number'=>array('name'=>'number', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
		'playerone'=>array('name'=>'playerone', 'type'=>'int(11) unsigned'),
		'playeronename'=>array('name'=>'playeronename', 'type'=>'text'),
		'players'=>array('name'=>'players', 'type'=>'text'),
		'nextturn'=>array('name'=>'nextturn', 'type'=>'int(11) unsigned'),
		'module'=>array('name'=>'module', 'type'=>'text'),
		'gamename'=>array('name'=>'gamename', 'type'=>'text'),
		'gamedata'=>array('name'=>'gamedata', 'type'=>'text'),
		'startdate'=>array('name'=>'startdate', 'type'=>'datetime', 'default'=>'0000-00-00 00:00:00'),
		'lastactive'=>array('name'=>'lastactive', 'type'=>'datetime', 'default'=>'0000-00-00 00:00:00'),
		'enddate'=>array('name'=>'enddate', 'type'=>'datetime', 'default'=>'0000-00-00 00:00:00'),
		'didexpire'=>array('name'=>'didexpire', 'type'=>'tinyint(4) unsigned', 'default'=>0),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'number'),
		);
	require_once("lib/tabledescriptor.php");
	synctable(db_prefix("playergames"), $playergames, true);
	/*switch support*/
	$sql="UPDATE ".db_prefix('playergames')." SET lastactive='".date("Y-m-d H:i:s", time())."' WHERE lastactive='0000-00-00 00:00:00';";
	db_query($sql);
	/*now all updated games won't be closed until they expire*/
	return true;
}

function playergames_uninstall(){
	output_notl("`n`c`b`QPlayergames Module - Uninstalled`0`b`c");
	return true;
}

function playergames_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		case "village":
			if ($session['user']['location']==get_module_setting("location") || get_module_setting("showinall")) {
			tlschema($args['schemas']['marketnav']);
			addnav($args['marketnav']);
			tlschema();
			addnav(array("%s`0's Gameparlor",get_module_setting("owner")),"runmodule.php?module=playergames");
			}
			break;
		case "newday-runonce":
			require_once("modules/playergames/func.php");
			playergames_clearup();
	}
	return $args;
}

function playergames_run(){
	global $session;
	$op = httpget('op');
	$mode=httpget('mode');
	$ownername=get_module_setting("owner");
	$ownergender=get_module_setting("ownergender");
	$cost=get_module_setting("generalcost");
	$link="runmodule.php?module=playergames";
	require_once("./modules/playergames/func.php");
	page_header("%s's Gameparlor",sanitize($ownername));
	villagenav();
	playernav();
	switch ($op) {
		case "list":
			$i=0;
			if ($mode=='current') {
				$sql="SELECT * FROM  ".db_prefix("playergames")." WHERE enddate='0000-00-00 00:00:00' order by number DESC limit 50";
				$nextorclosed=translate_inline("Next Player");
			} else {
				$sql="SELECT * FROM  ".db_prefix("playergames")." WHERE enddate!='0000-00-00 00:00:00' order by number DESC limit 50";
				$nextorclosed=translate_inline("Closing Player");
			}
			$result = db_query($sql);
			rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
			rawoutput("<tr class='trhead'><td>".translate_inline("#")."</td><td>".translate_inline("Gametype")."</td><td>".translate_inline("Gamename")."</td><td>".translate_inline("Patron")."</td><td>".translate_inline("Players")."</td><td>".translate_inline("Startdate")."</td><td>".translate_inline("Last Activity")."</td><td>".$nextorclosed."</td></tr>");
			while ($row=db_fetch_assoc($result)) {
				rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
				output_notl($row['number']);
				rawoutput("</td><td>");	
				rawoutput("<a href='runmodule.php?module={$row['module']}&op=view&number={$row['number']}'>");
				output(get_module_setting('gamename',$row['module']));
				rawoutput("</a>");
				addnav("","runmodule.php?module={$row['module']}&op=view&number={$row['number']}");		
				rawoutput("</td><td>");
				output_notl($row['gamename']);
				rawoutput("</td><td>");
				output_notl($row['playeronename']);
				rawoutput("</td><td>");
				$names=getnames($row['players']);
				while (list($key,$val)=each($names)) {
					output_notl($val['name']);
					output_notl("`n");
				}
				rawoutput("</td><td>");
				output_notl($row['startdate']);
				rawoutput("</td><td>");
				output_notl($row['lastactive']);
				rawoutput("</td><td>");				
				if ($session['user']['acctid']==$row['nextturn'] && $mode=='current') {
					rawoutput("<a href='runmodule.php?module={$row['module']}&op=resume&number={$row['number']}'>". translate_inline("Play your turn") ."</a>");
					addnav("","runmodule.php?module={$row['module']}&op=resume&number={$row['number']}");
				} elseif ($row['enddate']!='0000-00-00 00:00:00')  {
					if ($row['didexpire']==1) {
						output("`\$Expired!");
					}
				} else {					
					$name=getnames($row['nextturn']);
					output_notl($name[0]['name']);
					if ($session['user']['acctid']==$row['playerone'] && $mode=='current') {
						output_notl("`n");
						rawoutput("<a href='runmodule.php?module={$row['module']}&op=end&number={$row['number']}'>". translate_inline("End forcefully") ."</a>");
						addnav("","runmodule.php?module={$row['module']}&op=end&number={$row['number']}");
					}
				}
				rawoutput("</td></tr>");
				$i++;
				}
				rawoutput("</table>");
			break;
		case "opengame":
			require_once("./modules/playergames/opengame.php");
			break;
		default:
			$maxgames=get_module_setting("maxgames");
			$games=getgames();
			$gamecount=gamecount();
			$title=translate_inline(get_module_setting("ownergender")?"master":"mistress");
			output("`^You enter a vast hall teeming with people.");
			output_notl("`n`n");
			output("In the middle of the room stands a big statue of `%%s`^, the grand %s of this parlor.",$ownername,$title);
			output_notl("`n`n");
			output(" As you stand there amazed, a nicely dressed woman greets you.");
			output_notl("`n`n");
			output("`^\"`2Welcome to `%%s`2's Gameparlor.",$ownername);
			output(" If you want to play a game, be welcome!`n");
			output(" To start a new game, it will be %s gold general fee plus an individual game fee.",get_module_setting("generalcost"));
			output("`nTo get an overview, take a look around!`^\"");
			output_notl("`n`n");
			if (!$games) {
				output("Oh my, sorry, but currently we don't offer any games. Please come again later.");
				break;
			} elseif (get_module_setting("maxgames")<=$gamecount) {
				output("Oh my, sorry, but all the %s game tables are full. Please come again later.",$maxgames);
				break;
			}
			if ($maxgames>0) output("You can see %s tables here, ready for play.",$maxgames-$gamecount);
			output_notl("`n`n");
			addnav("Games");
			while (list($key,$row)=each($games)) {
				$fee=get_module_setting("fee",$row['module']);
				$min=get_module_setting("minplayers",$row['module']);
				$max=get_module_setting("maxplayers",$row['module']);
				if (($fee+$cost)>$session['user']['gold']) {
					addnav(array("%s (`^you need %s gold more`0)",$row['name'],$fee+$cost-$session['user']['gold']),$link);
				} else {
				addnav(array("%s (`^%s gold`0)",$row['name'],$fee+$cost),$link."&op=opengame&game=".$row['module']);
				output("For the Game '%s' you will need at least %s players and can have %s players maximum.`n",$row['name'],$min,$max);
				}
			}
			$expiration=(int)get_module_setting("expiration");
			if ($expiration!=0) output("`n`%Remember, any table will be cleared after %s (real) days of inactivity... keep that in mind!`n",$expiration);
			break;		
	}
	page_footer();

}


?>