<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\Forms;
use Lotgd\MySQL\Database;

function gmlog_getmoduleinfo() {
	$info = array(
		"name"=>"GM Log (ban-based)",
		"version"=>"1.1",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"",
		"settings"=>array(
			"GM Log Settings,title",
		),
	);
	return $info;
}

function gmlog_install() {
	module_addhook_priority("header-bans",50);
	module_addhook("biotop");
        $archive=array(
                'id'=>array('name'=>'id', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
                'gm_name'=>array('name'=>'gm_name', 'type'=>'varchar(255)'),
                'ipfilter'=>array('name'=>'ipfilter', 'type'=>'varchar(15)', 'default'=>''),
                'uniqueid'=>array('name'=>'uniqueid', 'type'=>'varchar(32)', 'default'=>''),
		'reason'=>array('name'=>'reason', 'type'=>'text'),
		'date'=>array('name'=>'date', 'type'=>'datetime', 'default'=>DATETIME_DATEMIN),
		'expiration'=>array('name'=>'expiration', 'type'=>'datetime', 'default'=>DATETIME_DATEMIN),
		'acctid'=>array('name'=>'acctid', 'type'=>'int(11) unsigned'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'id'),
	);
	require_once("lib/tabledescriptor.php");
	gmlog_normalize_archive_dates(db_prefix("gm_log"), $archive);
	synctable(db_prefix("gm_log"), $archive, true);
	return true;
}

/**
 * Upgrade legacy log dates before a strict-mode ALTER TABLE validates them.
 * DATETIME_DATEMIN also preserves the sentinel used for permanent bans.
 */
function gmlog_normalize_archive_dates(string $tablename, array $descriptor): void
{
    if (!db_table_exists($tablename)) {
        return;
    }

    $connection = Database::getDoctrineConnection();
    $table = $connection->quoteIdentifier($tablename);
    $columns = array_flip($connection->fetchFirstColumn("SHOW COLUMNS FROM $table"));
    $updates = array();
    $conditions = array();
    foreach ($descriptor as $name => $definition) {
        if (($definition['type'] ?? '') !== 'datetime' || !isset($columns[$name])) {
            continue;
        }
        $column = $connection->quoteIdentifier($name);
        // A datetime comparison against a zero-date literal can itself fail.
        $condition = "LEFT(CAST($column AS CHAR), 10) = :zeroDate";
        $updates[] = "$column = CASE WHEN $condition THEN :minimumDate ELSE $column END";
        $conditions[] = $condition;
    }

    if ($updates) {
        $connection->executeStatement(
            "UPDATE $table SET " . implode(', ', $updates) . " WHERE " . implode(' OR ', $conditions),
            ['zeroDate' => '0000-00-00', 'minimumDate' => DATETIME_DATEMIN]
        );
    }
}

function gmlog_uninstall() {
	return true;
}


function gmlog_dohook($hookname, $args) {
	global $session;
	switch ($hookname) {
		case "header-bans":
			$op=httpget('op');
			// bans.php refuses a ban without its form token but leaves op in the URL; log only verified bans.
			// A core without Forms::validateCsrf() (before September 2026) has no form token to check.
			if ($op=='saveban' && method_exists(Forms::class, 'validateCsrf') && !Forms::validateCsrf()) $op='';
			if ($op=='saveban') {
                                // ban is setup, record it
                                $type = httppost("type");
                                $ip = ($type == "ip") ? httppost("ip") : "";
                                $unique = ($type == "ip") ? "" : httppost("id");
                                if ($type == "ip") {
                                        $key = "lastip";
                                        $key_value = $ip;
                                } else {
                                        $key = "uniqueid";
                                        $key_value = $unique;
                                }
                                $conn = Database::getDoctrineConnection();
                                $gmLogTable = Database::prefix("gm_log");
                                $accountsTable = Database::prefix('accounts');
                                $date = date('Y-m-d', strtotime("now"));
                                $duration = (int)httppost("duration");
                                if ($duration == 0) {
                                        $duration = DATETIME_DATEMIN;
                                } else {
                                        $duration = date("Y-m-d", strtotime("+$duration days"));
                                }
                                // httppost() values carry legacy addslashes(); bind them unslashed.
                                // $key is one of the two fixed column names above.
                                $reason = httppost("reason");

                                /* one entry for every dude found (acctid) at that time - other values can and will change! This is at bantime*/
                                $accounts = $conn->executeQuery(
                                        "SELECT acctid FROM {$accountsTable} WHERE $key = :keyvalue",
                                        ['keyvalue' => stripslashes((string) $key_value)],
                                        ['keyvalue' => ParameterType::STRING]
                                )->fetchAllAssociative();
                                foreach ($accounts as $row) {
                                        $conn->executeStatement(
                                                "INSERT INTO {$gmLogTable} (gm_name,acctid,date,ipfilter,uniqueid,expiration,reason) VALUES (:gm_name, :acctid, :date, :ipfilter, :uniqueid, :expiration, :reason)",
                                                [
                                                        'gm_name'    => (string) $session['user']['login'],
                                                        'acctid'     => (int) $row['acctid'],
                                                        'date'       => $date,
                                                        'ipfilter'   => stripslashes((string) $ip),
                                                        'uniqueid'   => stripslashes((string) $unique),
                                                        'expiration' => $duration,
                                                        'reason'     => stripslashes((string) $reason),
                                                ],
                                                [
                                                        'gm_name'    => ParameterType::STRING,
                                                        'acctid'     => ParameterType::INTEGER,
                                                        'date'       => ParameterType::STRING,
                                                        'ipfilter'   => ParameterType::STRING,
                                                        'uniqueid'   => ParameterType::STRING,
                                                        'expiration' => ParameterType::STRING,
                                                        'reason'     => ParameterType::STRING,
                                                ]
                                        );
                                }
                        }
                        break;
                case "biotop":
                        if ($session['user']['superuser'] & SU_EDIT_COMMENTS) {
                                $user=httpget('char');
                                addnav("See GM Log","runmodule.php?module=gmlog&op=show&userid=$user");
                        }
                        break;
                default:
                        break;
	}
	return $args;
}

function gmlog_run(){
	global $session;
	check_su_access(SU_EDIT_COMMENTS);
	$user=httpget('userid');
	addnav("Back to Bio","bio.php?char=$user");
	$conn = Database::getDoctrineConnection();
	$table = Database::prefix('gm_log');
	$rows = $conn->executeQuery(
		"SELECT * FROM {$table} WHERE acctid = :acctid order by date desc",
		['acctid' => (int) $user],
		['acctid' => ParameterType::INTEGER]
	)->fetchAllAssociative();
	page_header("GM Log");
	output("Here you see a list of log entires that were made for this account. It may or may not be from the person currently owning it.`n");
	output("`nBans that affected this char might have been 'sitted' by an offender i.e.`n`n");
	rawoutput("<table>");
	$class='trdark';
	foreach ($rows as $row) {
		$class=($class=='trdark'?'trlight':'trdark');
		rawoutput("<tr class='$class'>");
		rawoutput(sprintf("<td>%s</td><td>%s</td><td>%s</td>",$row['date'],$row['reason'],$row['gm_name']));
		rawoutput("</tr>");
	}
	rawoutput("</table>");	
	if (count($rows)==0) 
		output("`\$Well... nothing found. Very nice - quiet char :)");
	page_footer();
}

?>
