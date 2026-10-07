<?php

declare(strict_types=1);

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

function serverbalance_getmoduleinfo(){
$info = array(
	"name"=>"Serverbalance",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"category"=>"Administrative",
	"download"=>"http://dragonprime.net/dls/serverbalance.zip",
	);
	return $info;
}

function serverbalance_install(){
	module_addhook("superuser");
	module_addhook("dk-preserve");
	return true;
}

function serverbalance_uninstall(){
	return true;
}

function serverbalance_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		case "superuser":
			if ($session['user']['superuser'] & SU_MEGAUSER) {
				addnav("Mechanics");
				addnav("Serverbalance","runmodule.php?module=serverbalance");
			}
			break;
		case "dk-preserve":
			$dk=$session['user']['dragonkills'];
			$time=$session['user']['age'];
			$timestat=get_module_objpref("Stats",$dk,"Time");			
			if ($timestat) {
				$timestat=($timestat+$time)/2; //yes, sure, this is not the arithmetic average...but it costs less time to calculate.
			} else {
				$timestat=$time;
			}
			set_module_objpref("Stats",$dk,"Time",$timestat);
			$wealth=$session['user']['gold'];
			$wealthstat=get_module_objpref("Stats",$wealth,"Wealth");			
			if ($wealthstat) {
				$wealthstat=($wealthstat+$wealth)/2; //yes, sure, this is not the arithmetic average...but it costs less time to calculate.
			} else {
				$wealthstat=$wealth;
			}
			set_module_objpref("Stats",$dk,"Wealth",$wealthstat);
			$people=get_module_objpref("All",$dk,"Players")+1;
			set_module_objpref("All",$dk,"Players",$people);
			break;	
	}
	return $args;
}

function serverbalance_run(){
        global $session;
        $op = httpget('op');
        require_once("./lib/superusernav.php");
        superusernav();
        page_header("Serverbalance");
        addnav("Refresh","runmodule.php?module=serverbalance");
        addnav("Clear Stats","runmodule.php?module=serverbalance&op=clear");

        $connection = Database::getDoctrineConnection();
        $table      = Database::prefix('module_objprefs');

        switch ($op) {
                case "clear":
                        $deleted = $connection->executeStatement(
                                "DELETE FROM {$table} WHERE modulename = :module",
                                [
                                        'module' => 'serverbalance',
                                ],
                                [
                                        'module' => ParameterType::STRING,
                                ]
                        );

                        if ($deleted >= 0) {
                                output("Stats cleared.");
                        } else {
                                output("An error happened.");
                        }
                        break;
                default:
                        $i = 0;

                        $playersQuery = $connection->createQueryBuilder();
                        $playersQuery
                                ->select('players_pref.objid AS dk', 'players_pref.value AS players')
                                ->from($table, 'players_pref')
                                ->where('players_pref.modulename = :module')
                                ->andWhere('players_pref.objtype = :allType')
                                ->andWhere('players_pref.setting = :playersSetting')
                                ->setParameters(
                                        [
                                                'module'         => 'serverbalance',
                                                'allType'        => 'All',
                                                'playersSetting' => 'Players',
                                        ],
                                        [
                                                'module'         => ParameterType::STRING,
                                                'allType'        => ParameterType::STRING,
                                                'playersSetting' => ParameterType::STRING,
                                        ]
                                );

                        $playerRows = $playersQuery->executeQuery()->fetchAllAssociative();
                        $players    = [];

                        foreach ($playerRows as $playerRow) {
                                $players[(int) $playerRow['dk']] = (int) $playerRow['players'];
                        }

                        $statsQuery = $connection->createQueryBuilder();
                        $statsQuery
                                ->select('time_pref.objid AS dk', 'time_pref.value AS time', 'wealth_pref.value AS wealth')
                                ->from($table, 'time_pref')
                                ->innerJoin(
                                        'time_pref',
                                        $table,
                                        'wealth_pref',
                                        'time_pref.objid = wealth_pref.objid'
                                )
                                ->where('time_pref.modulename = :module')
                                ->andWhere('time_pref.objtype = :statsType')
                                ->andWhere('time_pref.setting = :timeSetting')
                                ->andWhere('wealth_pref.modulename = :module')
                                ->andWhere('wealth_pref.objtype = :statsType')
                                ->andWhere('wealth_pref.setting = :wealthSetting')
                                ->orderBy('time_pref.objid')
                                ->setParameters(
                                        [
                                                'module'        => 'serverbalance',
                                                'statsType'     => 'Stats',
                                                'timeSetting'   => 'Time',
                                                'wealthSetting' => 'Wealth',
                                        ],
                                        [
                                                'module'        => ParameterType::STRING,
                                                'statsType'     => ParameterType::STRING,
                                                'timeSetting'   => ParameterType::STRING,
                                                'wealthSetting' => ParameterType::STRING,
                                        ]
                                );

                        $rows = $statsQuery->executeQuery()->fetchAllAssociative();

                        rawoutput("<table border='0' cellpadding='2' cellspacing='0'>");
                        rawoutput("<tr class='trhead'><td>". translate_inline("Dk-#")."</td><td>".translate_inline("Dragonage avg")."</td><td>".translate_inline("Gold avg")."</td><td>". translate_inline("#Players")."</td></tr>");

                        foreach ($rows as $row) {
                                $dk           = (int) $row['dk'];
                                $playerNumber = $players[$dk] ?? 0;

                                rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
                                output_notl((string) $dk);
                                rawoutput("</td><td>");
                                output_notl($row['time']);
                                rawoutput("</td><td>");
                                output_notl($row['wealth']);
                                rawoutput("</td><td>");
                                output_notl((string) $playerNumber);
                                rawoutput("</td></tr>");
                                $i++;
                        }
                break;
        }
        rawoutput("</table>");
        page_footer();

}
?>
