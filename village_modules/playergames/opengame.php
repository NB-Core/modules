<?php
$players=httpget('players');
$mode=httpget('mode');
$game=httpget('game');
$gamename=httpget('gamename');
if (!$gamename) $gamename=httppost('gamename');
$locallink=$link."&op=opengame&game=$game&players=$players&gamename=".rawurlencode($gamename);
addnav("Back to game",$locallink);
switch ($mode) {
	case "gamename":
		rawoutput("<form action='$locallink' method='POST'>");
		addnav("","$locallink");
		rawoutput("<input name='gamename' maxlength='50' value=\"".htmlentities(stripslashes($gamename))."\">");
		$save = translate_inline("Save");
		rawoutput("<input type='submit' class='button' value='$save'></form>");
		break;
	case "invite":
		if (!httpget('target')) {
			require_once("./modules/playergames/searchplayer.php");
			searchplayer($locallink."&mode=invite");
		} else {
			if ($players) $players=explode(",",$players);
				else
				$players=array();
			if (!in_array(httpget('target'),$players)) array_push($players,httpget('target'));
			$players=implode(",",$players);
			redirect($link."&op=opengame&game=$game&players=$players&gamename=".rawurlencode($gamename));
		}
		break;
	case "kick":
		$players=explode(",",$players);
		$players=array_diff($players,array(httpget('who')));
		$players=implode(",",$players);
		redirect($link."&op=opengame&game=$game&players=$players&gamename=".rawurlencode($gamename));
		break; //well, not necessary
	case "startgame":
		$gold=get_module_setting('fee',$game);
		$session['user']['gold']-=$gold;
		$time=gmdate("Y-m-d H:i:s", time());
		$sql="INSERT INTO ".db_prefix("playergames")." (playerone,playeronename,players,nextturn,module,gamename,startdate) VALUES (";
		$sql.=$session['user']['acctid'].",";
		$sql.="'".addslashes($session['user']['name'])."',";
		$sql.="'$players',";
		$sql.=$session['user']['acctid'].",";
		$sql.="'$game',";
		$sql.="'".addslashes(rawurldecode($gamename))."',";
		$sql.="'".$time."'";
		$sql.=");";
		$result=db_query($sql);
		if ($result) {
			$sql="SELECT number from ".db_prefix("playergames")." WHERE startdate='".$time."';";
			$result=db_query($sql);
			$row=db_fetch_assoc($result); //should be unique... if he hasn't stopped time
			$players=explode(",",$players);
			$msgtext=array("`@Your friend %s`@ has invited you to play a game together!`n`nGo to the game parlor and look for the game '%s' with the number '%s'!",$session['user']['name'],get_module_setting('gamename',$game),$row['number']);
			require_once("./lib/systemmail.php");
			while (list($key,$val)=each($players)) {
				systemmail($val,array("You have been invited to a game!"),$msgtext);
			}
			redirect("runmodule.php?module=$game&number=".$row['number']);
		} else {
			output("Error while creating the game! Let your admin know about this!");
		}
		
		break;	
		
	default:
		$games=getgames();
		output("Name of the Game: %s",$gamename);
		output_notl("`n`n");
		output("You decided to make a new game. Please invite now players to you game.");
		output_notl("`n`n");
		output("Currently invited (Click on someone to kick him from the list):");
		output_notl("`n`n");
		if ($players) {
			$nameplayers=explode(",",$players);
			$sql="SELECT acctid, name from ".db_prefix("accounts")." WHERE ";
			while (list($key,$val)=each($nameplayers)) {
				$sql.=" acctid=".$val." OR";
			} 
			$sql=substr($sql,0,strlen($sql)-3);
			$result=db_query($sql);
			while ($row=db_fetch_assoc($result)) {
				rawoutput("<a href='$locallink&mode=kick&who={$row['acctid']}'>");
				output_notl("`@".$row['name']."`@");
				rawoutput("</a>");
				addnav("","$locallink&mode=kick&who={$row['acctid']}");
				output_notl("`n");
			}
		} else output("`^None!");
		addnav("Edit Gamename",$locallink."&mode=gamename");
		if (get_module_setting("maxplayers",$game)>count($nameplayers)) {
			addnav("Invite Player",$locallink."&mode=invite");
		}
		if (count($nameplayers)>0) {
			if (count($nameplayers)+1>=get_module_setting("minplayers",$game)) addnav("Start the game",$locallink."&mode=startgame");
		}
		

}



?>
