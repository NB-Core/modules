<?php

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

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

                $connection = Database::getDoctrineConnection();
                $playerGamesTable = Database::prefix('playergames');

                $invitedPlayers = [];
                if ($players) {
                        $invitedPlayers = array_values(array_filter(array_map('intval', explode(',', $players))));
                }

                $playersList = implode(',', $invitedPlayers);

                $result = $connection->executeStatement(
                        "INSERT INTO {$playerGamesTable} (playerone, playeronename, players, nextturn, module, gamename, startdate)"
                        . " VALUES (:playerone, :playeronename, :players, :nextturn, :module, :gamename, :startdate)",
                        [
                                'playerone' => $session['user']['acctid'],
                                'playeronename' => $session['user']['name'],
                                'players' => $playersList,
                                'nextturn' => $session['user']['acctid'],
                                'module' => $game,
                                'gamename' => rawurldecode($gamename),
                                'startdate' => $time,
                        ],
                        [
                                'playerone' => ParameterType::INTEGER,
                                'playeronename' => ParameterType::STRING,
                                'players' => ParameterType::STRING,
                                'nextturn' => ParameterType::INTEGER,
                                'module' => ParameterType::STRING,
                                'gamename' => ParameterType::STRING,
                                'startdate' => ParameterType::STRING,
                        ]
                );

                if ($result) {
                        $statement = $connection->executeQuery(
                                "SELECT number FROM {$playerGamesTable} WHERE startdate = :startdate AND playerone = :playerone",
                                [
                                        'startdate' => $time,
                                        'playerone' => $session['user']['acctid'],
                                ],
                                [
                                        'startdate' => ParameterType::STRING,
                                        'playerone' => ParameterType::INTEGER,
                                ]
                        );
                        $row=$statement->fetchAssociative(); //should be unique... if he hasn't stopped time
                        $msgtext=array("`@Your friend %s`@ has invited you to play a game together!`n`nGo to the game parlor and look for the game '%s' with the number '%s'!",$session['user']['name'],get_module_setting('gamename',$game),$row['number']);
                        require_once("./lib/systemmail.php");
                        foreach ($invitedPlayers as $val) {
                                if ($val <= 0) {
                                        continue;
                                }
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
                $nameplayers = [];
                if ($players) {
                        $nameplayers = array_values(array_filter(array_map('intval', explode(',', $players))));
                }

                if ($nameplayers) {
                        $connection = Database::getDoctrineConnection();
                        $accountsTable = Database::prefix('accounts');

                        $result = $connection->executeQuery(
                                "SELECT acctid, name FROM {$accountsTable} WHERE acctid IN (:acctids)",
                                [
                                        'acctids' => $nameplayers,
                                ],
                                [
                                        'acctids' => ArrayParameterType::INTEGER,
                                ]
                        );

                        while (($row = $result->fetchAssociative()) !== false) {
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
