<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

function userstatus_getmoduleinfo(){
    $info = array(
        "name" => "User Status - Appear Offline and more",
        "version" => "1.0",
        "author" => "Oliver Brendel",
        "category" => "User",
        "settings" => array(
            "User Status - Settings,title",
        ),
    );
    return $info;
}

function userstatus_install(){
    module_addhook("footer-prefs");
    module_addhook("biotarget");
    module_addhook("warriorlist");
    module_addhook("is-player-online");
    module_addhook("mail-write-notify");
    module_addhook("friendlist-friends");
    module_addhook("loggedin");
    module_addhook("delete_character");

    // Table for user status information
    $userstatus = array(
        'acctid' => array('name' => 'acctid', 'type' => 'int(11) unsigned'),
        'statusid' => array('name' => 'statusid', 'type' => 'int(11) unsigned'),
        'statusmessage' => array('name' => 'statusmessage', 'type' => 'text', 'null' => '1'),
        'key-PRIMARY' => array('name' => 'PRIMARY', 'type' => 'primary key', 'unique' => '1', 'columns' => 'acctid'),
    );

    // Table for status names
    $statusnames = array(
        'statusid' => array('name' => 'statusid', 'type' => 'int(11) unsigned', 'extra' => 'auto_increment'),
        'statusname' => array('name' => 'statusname', 'type' => 'varchar(50)'),
        'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'statusid'),
    );

    require_once("lib/tabledescriptor.php");
    synctable(db_prefix("userstatus"), $userstatus, true);
    synctable(db_prefix("statusnames"), $statusnames, true);

    // Insert default status names if they don't exist
    $conn       = Database::getDoctrineConnection();
    $statusTable = Database::prefix('statusnames');
    $count      = (int) $conn->fetchOne("SELECT COUNT(*) AS count FROM {$statusTable}");
    if ($count === 0) {
        $statuses = array(
            array('id' => 0, 'name' => 'Appear Offline'),
            array('id' => 1, 'name' => 'Visible'),
            array('id' => 2, 'name' => 'Busy'),
            array('id' => 3, 'name' => 'Afk'),
        );
        foreach ($statuses as $status) {
            $conn->executeStatement(
                "INSERT INTO {$statusTable} (statusid, statusname) VALUES (:statusid, :statusname)",
                array('statusid' => $status['id'], 'statusname' => $status['name']),
                array('statusid' => ParameterType::INTEGER, 'statusname' => ParameterType::STRING)
            );
        }
    }

    return true;
}

function userstatus_uninstall(){
    // Optionally drop the tables if you want to clean up
    // db_query("DROP TABLE IF EXISTS " . db_prefix("userstatus"));
    // db_query("DROP TABLE IF EXISTS " . db_prefix("statusnames"));
    return true;
}

function userstatus_dohook($hookname, $args){
    global $session;
    switch($hookname){
        case "mail-write-notify":
            $acctid = $args['acctid_to'];
            if ($acctid) {
                $status = userstatus_getstatus($acctid);
                if ($status['statusid'] > 1) {
                    output("`2Recipient Status:`4 %s`0`n", $status['statusname']);
                }
                if (!empty($status['statusmessage'])) {
                    output("`2User Status Message:`y %s`n`n", $status['statusmessage']);
                }
            }
            break;
        case "footer-prefs":
            // Add a navigation link in the user preferences
	        addnav("User Status");
            addnav("Set User Status", "runmodule.php?module=userstatus");
            break;
        case "loggedin":
            // Array of online users
            $online_users = $args;

            // Array of offline users
            $offline_users = userstatus_getofflineusers();

            // Filter out 'Appear Offline' users from the online list
            foreach ($online_users as $key => $user) {
                if (!is_array($user) || !array_key_exists('acctid', $user)) {
                    continue;
                }
                if (in_array($user['acctid'], $offline_users)) {
                    unset($online_users[$key]);
                }
            }

            $args = $online_users;
        case "friendlist-friends":
        case "warriorlist":
            // Array of offline users
            $offline_users = userstatus_getofflineusers();

            // Add status information to the warrior list, i.e. set the loggedin status in the args-array to 0 if the user has set status to 'Appear Offline'
            foreach ($args as $key => $user) {
                if (!is_array($user) || !array_key_exists('acctid', $user)) {
                    continue;
                }
                if (in_array($user['acctid'], $offline_users)) {
                    $args[$key]['loggedin'] = 0;
                }
            }
            break;
        case "biotarget":
        case "is-player-online":
            // Check if the target wants to be offline, and set loggedin to 0 if so
            $acctid = $args['acctid'];
            $status = userstatus_getstatus($acctid);
            if ($status['statusid'] == 0) {
                $args['loggedin'] = 0;
            }
            break;
        case "delete_character":
            // Clean up user status when a character is deleted
            $acctid = (int) $args['acctid'];
            if ($acctid > 0) {
                $conn  = Database::getDoctrineConnection();
                $table = Database::prefix('userstatus');
                $conn->executeStatement(
                    "DELETE FROM {$table} WHERE acctid = :acctid",
                    array('acctid' => $acctid),
                    array('acctid' => ParameterType::INTEGER)
                );
            }
            break;
    }
    return $args;
}

function userstatus_run(){
    global $session;
    page_header("Change User Status");

    $op = httpget('op');
    $conn  = Database::getDoctrineConnection();
    $table = Database::prefix('userstatus');

    if ($op == "save") {
        // Process form submission
        $statusid = (int)httppost('statusid');
        $statusmessage = (string) httppost('statusmessage');
        $acctid = $session['user']['acctid'];

        // Check if an entry already exists for the user
        $exists = (int) $conn->fetchOne(
            "SELECT COUNT(*) FROM {$table} WHERE acctid = :acctid",
            array('acctid' => $acctid),
            array('acctid' => ParameterType::INTEGER)
        );
        if ($exists > 0) {
            // Update existing record
            $conn->executeStatement(
                "UPDATE {$table} SET statusid = :statusid, statusmessage = :statusmessage WHERE acctid = :acctid",
                array(
                    'statusid' => $statusid,
                    'statusmessage' => $statusmessage,
                    'acctid' => $acctid,
                ),
                array(
                    'statusid' => ParameterType::INTEGER,
                    'statusmessage' => ParameterType::STRING,
                    'acctid' => ParameterType::INTEGER,
                )
            );
        } else {
            // Insert new record
            $conn->executeStatement(
                "INSERT INTO {$table} (acctid, statusid, statusmessage) VALUES (:acctid, :statusid, :statusmessage)",
                array(
                    'acctid' => $acctid,
                    'statusid' => $statusid,
                    'statusmessage' => $statusmessage,
                ),
                array(
                    'acctid' => ParameterType::INTEGER,
                    'statusid' => ParameterType::INTEGER,
                    'statusmessage' => ParameterType::STRING,
                )
            );
        }
        // Invalidate the cache for offline users
        invalidatedatacache("offlineusers");
        output("`^Status successfully updated.`0`n`n`n");
    }

    // Retrieve current status
    $acctid = $session['user']['acctid'];
    $row = $conn->fetchAssociative(
        "SELECT statusid, statusmessage FROM {$table} WHERE acctid = :acctid",
        array('acctid' => $acctid),
        array('acctid' => ParameterType::INTEGER)
    );
    if ($row) {
        $statusid = (int) $row['statusid'];
        $statusmessage = $row['statusmessage'] ?? '';
    } else {
        // Default values
        $statusid = 1; // Visible
        $statusmessage = '';
    }

    rawoutput("<form action='runmodule.php?module=userstatus&op=save' method='POST'>");
    addnav("", "runmodule.php?module=userstatus&op=save");
    output("`^Select your status:`0`n");
    rawoutput("<select name='statusid'>");
    $statusRows = $conn->fetchAllAssociative(
        "SELECT statusid, statusname FROM " . Database::prefix('statusnames')
    );
    foreach ($statusRows as $row) {
        $selected = ((int) $row['statusid'] === $statusid) ? "selected" : "";
        rawoutput("<option value='{$row['statusid']}' $selected>{$row['statusname']}</option>");
    }
    output_notl("</select>`n`n",true);

    output("`^Enter your status message:`0`n");
    $statusmessage = htmlentities($statusmessage, ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
    output_notl("<input name='statusmessage' value='$statusmessage' size='40'>`n`n",true);

    rawoutput("<input type='submit' class='button' value='Save'>");
    rawoutput("</form>");

    addnav("Return to Preferences", "prefs.php");
    page_footer();
}

/**
 * Get the status of a user.
 *
 * @param int $acctid The account ID of the user.
 * @return array|false Returns an associative array with 'statusid', 'statusname', and 'statusmessage' or false if not found.
 */
function userstatus_getstatus($acctid) {
    $acctid = (int)$acctid;
    $conn = Database::getDoctrineConnection();
    $statusTable = Database::prefix('userstatus');
    $namesTable  = Database::prefix('statusnames');
    $row = $conn->fetchAssociative(
        "SELECT u.statusid, n.statusname, u.statusmessage
            FROM {$statusTable} AS u
            LEFT JOIN {$namesTable} AS n ON u.statusid = n.statusid
            WHERE u.acctid = :acctid",
        array('acctid' => $acctid),
        array('acctid' => ParameterType::INTEGER)
    );
    if ($row) {
        return $row;
    } else {
        // Default values if user hasn't set status
        return array(
            'statusid' => 1, // Visible
            'statusname' => 'Visible',
            'statusmessage' => '',
        );
    }
}

function userstatus_getofflineusers() : array {
    // Do one query for all and return the acctids that appear Offline
    // Retrieve users who have set status to 'Appear Offline' (statusid = 0)
    $cacheKey = 'offlineusers';
    $cached   = datacache($cacheKey, 60);
    if ($cached !== false && is_array($cached)) {
        return $cached;
    }

    $conn  = Database::getDoctrineConnection();
    $table = Database::prefix('userstatus');
    $rows  = $conn->fetchFirstColumn(
        "SELECT acctid FROM {$table} WHERE statusid = :statusid",
        array('statusid' => 0),
        array('statusid' => ParameterType::INTEGER)
    );
    $offline_users = array_map('intval', $rows ?: array());
    updatedatacache($cacheKey, $offline_users);

    return $offline_users;
}

?>
