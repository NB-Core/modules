<?php
function dwellings_deleteforowner($acctid = false, $dwid = false){
	if ($acctid === false) return false;
	$sql = "DELETE FROM ".db_prefix("dwellings")." WHERE ownerid = $acctid";
	db_query($sql);
	if ($dwid){
		$sql = "DELETE FROM ".db_prefix("commentary")." WHERE section = 'dwellings-$dwid' OR section='coffers-$dwid'";
		db_query($sql);
	}
}

function dwellings_abandonforowner($acctid = false, $dwid = false){
	if ($acctid === false) return false;
	$ab = "";
	$extra = "";
	if(get_module_setting("zerocof")){
		$ab = ",gold=0,gems=0";
		$extra = "OR section='coffers-$dwid'";
	}
	$sql = "UPDATE ".db_prefix("dwellings")." SET ownerid = 0,status=4$ab WHERE ownerid = $acctid";
	db_query($sql);
	if ($dwid){
		$sql = "DELETE FROM ".db_prefix("commentary")." WHERE section = 'dwellings-$dwid' $extra";
		db_query($sql);
	}
}	

function dwellings_wipekeysforowner($acctid = false) {
	if ($acctid === false) return false;
	$sql = "UPDATE ".db_prefix("dwellingkeys")."  SET keyowner = 0 WHERE keyowner = $acctid";
	db_query($sql);
}

/**
 * Determine whether a string contains visible content beyond color codes.
 *
 * @param string $z The string to inspect.
 *
 * @return int 1 when visible content is present, otherwise 0.
 */
function dwellings_teststring($z)
{
    /* Thanks to Edorian for the brainstorm. */
    $farbflag = 0;
    $colorlist = array_keys(\Lotgd\Output::getInstance()->getColors());
    if ($z == "") {
        return 0;
    }
    $length = strlen($z);

    // Use the known string length so the loop never reads beyond its last byte.
    for ($x = 0; $x < $length; $x++) {
        if ($farbflag) {
            $farbflag = 0;
            continue;
        }

        if ($z[$x] == '`') {
            if (isset($z[$x + 1]) && in_array($z[$x + 1], $colorlist)) {
                $farbflag = 1;
                continue;
            }

            return 0;
        }

        if ($z[$x] != ' ') {
            return 1;
        }

        // Preserve the legacy treatment of a final space as submitted content.
        if (!isset($z[$x + 1])) {
            return 1;
        }
    }

    return 0;
}
function getlogin($id){
	$sql = "SELECT login FROM ".db_prefix("accounts")." WHERE acctid=$id";
	$res = db_query($sql);
	$row = db_fetch_assoc($res);
	$login = $row['login'];
	return $login;
}
function dwellings_get_coffers($dwid,$type){
	$sql = "SELECT $type FROM ".db_prefix("dwellings")." WHERE dwid='$dwid'";
	$res = db_query($sql);
	$row = db_fetch_assoc($res);
	$amnt = $row[$type];
	return $amnt;
}
function dwellings_modify_coffers($dwid,$type,$amnt){
	$sql = "UPDATE ".db_prefix("dwellings")." SET $type=$type+$amnt WHERE dwid='$dwid'";
	db_query($sql);
}	
?>
