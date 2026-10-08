<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

function adfuncbio_getmoduleinfo(){
        $info = array(
            "name"=>"Admin Functions from Bio",
            "author"=>"Chris Vorndran",
            "version"=>"0.14",
            "category"=>"Administrative",
			"download"=>"http://dragonprime.net/users/Sichae/adfuncbio.zip",
			"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
			"description"=>"Brings many of the Admin Functions (Newday, Navs and Killing) into a user's bio. Controlled by pref.",
			"settings"=>array(
				"Admin Functions from Bio Settings,title",
				"runfrom"=>"Navs appear when which condition is met,enum,0,Flag for Edit Users,1,Preference Set,2,Both|0",
				"hakil"=>"Is Kill Player enabled,bool|1",
					),
            "prefs"=>array(
                "Admin Functions From Bio Preferences,title",
                "ha"=>"Does this user have access to Give and Take functions,bool|0",
                "kp"=>"Has player been killed?,bool|0",
            ),
        );
    return $info;
}
function adfuncbio_install(){
	module_addhook("biostat");
	return true;
}
function adfuncbio_uninstall(){
    return true;
}
function adfuncbio_dohook($hookname,$args){
    global $session;
    $id = httpget('char');
//    $sql = "SELECT acctid FROM ".db_prefix("accounts")." WHERE login='$char'";
//    $res = db_query($sql);
//    $row = db_fetch_assoc($res);
//    $id = $row['acctid'];
    switch ($hookname){
        case "biostat":
            if ((get_module_setting("runfrom") == 0 && $session['user']['superuser'] & SU_EDIT_PETITIONS) || (get_module_setting("runfrom") == 1 && get_module_pref("ha") == 1) || (get_module_setting("runfrom") == 2 && $session['user']['superuser'] & SU_EDIT_USERS && get_module_pref("ha") == 1)){
                addnav("Admin Functions");
                //addnav("Give Newday","runmodule.php?module=adfuncbio&op=opt&act=nd&id=$id");
		addnav("Fix Navs","runmodule.php?module=adfuncbio&op=opt&act=fn&id=$id");
                if (get_module_setting("hakil") == 1) addnav("Kill Player","runmodule.php?module=adfuncbio&op=opt&act=kp&id=$id");
            }
            break;
            break;
    }
    return $args;
}
function adfuncbio_run(){
    global $session;
    $op = httpget('op');
    $act = httpget('act');
    $id = httpget('id');
        $conn     = Database::getDoctrineConnection();
        $accounts = Database::prefix('accounts');
        $result   = $conn->executeQuery(
            "SELECT name FROM {$accounts} WHERE acctid = :acctid",
            [
                'acctid' => (int) $id,
            ],
            [
                'acctid' => ParameterType::INTEGER,
            ]
        );

        $row  = $result->fetchAssociative();
        $name = $row['name'] ?? '';
    page_header("Give and Take");

    switch ($op){
        case "opt":
            switch ($act){
                case "nd":
                    $offset = "-".(24 / (int)getsetting("daysperday",4))." hours";
                    $newdate = date("Y-m-d H:i:s",strtotime($offset));
                    $conn->executeStatement(
                        "UPDATE {$accounts} SET lasthit = :lasthit WHERE acctid = :acctid",
                        [
                            'lasthit' => $newdate,
                            'acctid'  => (int) $id,
                        ],
                        [
                            'lasthit' => ParameterType::STRING,
                            'acctid'  => ParameterType::INTEGER,
                        ]
                    );
                    rawoutput("<big>");
                    output("NewDay successfully given to %s!",$name);
                    rawoutput("</big>");
					debuglog("has granted a newday to $name");
                    break;
                case "kp":
                    set_module_pref("kp",1,"adfuncbio",$id);
                    rawoutput("<big>");
                    output("%s has been successfully killed!",$name);
                    rawoutput("</big>");
					debuglog("has killed $name via Kill Player function");
                    break;
		case "fn":
                        $conn->executeStatement(
                            "UPDATE {$accounts} SET allowednavs = '', specialinc = '' WHERE acctid = :acctid",
                            [
                                'acctid' => (int) $id,
                            ],
                            [
                                'acctid' => ParameterType::INTEGER,
                            ]
                        );
                    rawoutput("<big>");
                    output("Navs have been fixed for %s!",$name);
                    rawoutput("</big>");
					debuglog("has fixed the navs for $name, by {$session['user']['login']}");
            }
            break;
        }
        villagenav();
page_footer();
}
?> 
