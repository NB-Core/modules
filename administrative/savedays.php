<?php
// addnews ready
// translator ready
// mail ready

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

function savedays_getmoduleinfo(){
	$info = array(
		"name"=>"Save Days",
		"author"=>"`JShinobiIceSlayer",
		"version"=>"1.00",
		"category"=>"General",
        "download"=>"",
		"settings"=>array(
			"Saved Days Module Settings,title",
			"turns"=>"What is the number of turns gain for each missed day?,range,1,25,1|10",
			"maxturns"=>"What is the Maximum amount of turns the user can gain?,int|50",
		),
		"prefs"=>array(
			"Saved Days User Preferences,title",
			"daysmissed"=>"How many days the user has missed,int|0",
			"user_reject"=>"Opt to not receive extra turns for missed days,bool|0",
		),
	);
	return $info;
}

function savedays_install(){
	module_addhook("newday-runonce");
	module_addhook("newday");
	return true;
}
function savedays_uninstall(){
	return true;
}

function savedays_dohook($hookname,$args){
	global $session;

	switch($hookname){
	case "newday-runonce":
		$connection = Database::getDoctrineConnection();
		$moduleUserPrefsTable = Database::prefix('module_userprefs');
		$accountsTable = Database::prefix('accounts');

		$connection->executeStatement(
			"UPDATE {$moduleUserPrefsTable}
				SET value = value + 1
				WHERE modulename = :module
				AND setting = :setting",
			[
				'module' => 'savedays',
				'setting' => 'daysmissed',
			],
			[
				'module' => ParameterType::STRING,
				'setting' => ParameterType::STRING,
			]
		);

		$result = $connection
			->createQueryBuilder()
			->select('a.acctid')
			->from($accountsTable, 'a')
			->where(
				"NOT EXISTS (
					SELECT 1
					FROM {$moduleUserPrefsTable} mup
					WHERE mup.userid = a.acctid
					AND mup.modulename = :module
					AND mup.setting = :setting
				)"
			)
			->setParameter('module', 'savedays', ParameterType::STRING)
			->setParameter('setting', 'daysmissed', ParameterType::STRING)
			->executeQuery();

		$statement = $connection->prepare(
			"INSERT INTO {$moduleUserPrefsTable} (modulename, setting, userid, value)
				VALUES (:module, :setting, :userid, :value)"
		);
		$statement->bindValue('module', 'savedays', ParameterType::STRING);
		$statement->bindValue('setting', 'daysmissed', ParameterType::STRING);
		$statement->bindValue('value', 1, ParameterType::INTEGER);

		while (($row = $result->fetchAssociative()) !== false) {
			$statement->bindValue('userid', (int) $row['acctid'], ParameterType::INTEGER);
			$statement->executeStatement();
		}
		break;
	case "newday":
		if(!get_module_pref("user_reject")){
			$misseddays=get_module_pref('daysmissed')-1;
			$multiplier=get_module_setting('turns');
			$maxgain=get_module_setting('maxturns');
			if ($misseddays>0) {
				$turnsgained= $misseddays * $multiplier;
			} else {
				$turnsgained = 0;
			}
			if ($turnsgained>$maxgain) $turnsgained=$maxgain;
			$session['user']['turns']+=$turnsgained;
			if ($turnsgained>0) {
				output("`n`^For having missed %s game days, you gain an extra of %s turns today.",$misseddays,$turnsgained);
				debuglog("Gained $turnsgained turns for $misseddays game newdays missed.");
			} else {
				debuglog("Gained no turns for game days missed.");
			}
			set_module_pref('daysmissed',0);
		} else {
			$misseddays=get_module_pref('daysmissed')-1;
			if ($misseddays>0) output("`nDue to your choice, you do not receive the extra days for each gameday you have missed.");
		}
		break;	
	}
	return $args;
}

function savedays_run(){
}
?>
