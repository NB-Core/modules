<?php
function playernav() {
	addnav("Navigation");
	addnav("Main Game Hall","runmodule.php?module=playergames");
	addnav("List current games","runmodule.php?module=playergames&op=list&mode=current");
	addnav("List last closed games","runmodule.php?module=playergames&op=list&mode=previous");
}

function blockplayernav() {
	blocknav("village.php");
	blocknav("runmodule.php?module=playergames");
	blocknav("runmodule.php?module=playergames&op=list&mode=current");
	blocknav("runmodule.php?module=playergames&op=list&mode=previous");

}
function getgames() {
	$play=array();
	$play=modulehook("parlorgames",$play);
	return $play;
}

function getnames($nameplayers) {
	$nameplayers=explode(",",$nameplayers);
	$sql="SELECT acctid, name from ".db_prefix("accounts")." WHERE ";
	while (list($key,$val)=each($nameplayers)) {
		$sql.=" acctid=".$val." OR";
	}
	$sql=substr($sql,0,strlen($sql)-3);
	$result=db_query($sql);
	$return=array();
	while ($row=db_fetch_assoc($result)) {
		array_push($return,$row);
	}
	return $return;
}

function endgame($number) {
	$sql="UPDATE ".db_prefix("playergames")." set enddate='".date("Y-m-d H:i:s", time())."' WHERE number=".$number;
	$result=db_query($sql);
}

function getdata($number) {
	$sql="SELECT gamedata FROM ".db_prefix("playergames")." WHERE number=".$number;
	$result=db_query($sql);
	if (db_num_rows($result)!=1) {
		output("An error happened while getting the gamedata.");
		return false;
	} else {
		$row=db_fetch_assoc($result);
		return unserialize($row['gamedata']);
	}
}

function getalldata($number,$back=false) {
	$sql="SELECT * FROM ".db_prefix("playergames")." WHERE number=".$number;
	$result=db_query($sql);
	if (db_num_rows($result)!=1) {
		output("An error happened while getting the gamedata.");
		return false;
	} else {
		$row=db_fetch_assoc($result);
		return $row;
	}
}

function getgamename($number) {
	$sql="SELECT gamename FROM ".db_prefix("playergames")." WHERE number=".$number;
	$result=db_query($sql);
	if (db_num_rows($result)!=1) {
		output("An error happened while getting the gamedata.");
		return false;
	} else {
		$row=db_fetch_assoc($result);
		return $row['gamename'];
	}
}

function nextplayer($number) {
	$sql="SELECT playerone,nextturn,players FROM ".db_prefix("playergames")." WHERE number=".$number;
	$result=db_query($sql);
	$row=db_fetch_assoc($result);
	$players=explode(",",$row['players']);
	while ($checkid=array_shift($players)) {
		if ($checkid==$row['nextturn']) {
			$id=array_shift($players);
			if (!$id) $id=$row['playerone'];
		}
	}
	if (!$id) $id=array_shift(explode(",",$row['players']));
	return $id;
}

function previousplayer($number) {
	$sql="SELECT playerone,nextturn,players FROM ".db_prefix("playergames")." WHERE number=".$number;
	$result=db_query($sql);
	$row=db_fetch_assoc($result);
	$players=explode(",",$row['players']);
	while ($checkid=array_pop($players)) {
		if ($checkid==$row['nextturn']) {
			$id=array_pop($players);
			if (!$id) $id=$row['playerone'];
		}
	}
	if (!$id) $id=array_pop(explode(",",$row['players']));
	return $id;
}

function nextturn($number,$id=false) {
	if (!$id) $id=nextplayer($number);
	$sql="UPDATE ".db_prefix("playergames")." SET nextturn=".$id." WHERE number=".$number;
	$result=db_query($sql);
	$msgtext=array("The game %s with the number %s awaits your move.",getgamename($number),$number);
	require_once("./lib/systemmail.php");
	systemmail($id,array("It's your turn!"),$msgtext);
}

function setdata($number,$gamedata) {
	$sql="UPDATE ".db_prefix("playergames")." SET gamedata='".addslashes(serialize($gamedata))."' WHERE number=".$number;
	$result=db_query($sql);
	if ($result!=1) {
		output("An error happened while getting the gamedata.");
		return false;
	}
}

function gamecount($gamename=false) {
	$sql="SELECT count(number) as counter FROM ".db_prefix("playergames")." WHERE enddate='".DATETIME_DATEMAX."'";
	if ($gamename) {
		$sql.=" WHERE modulename=".$gamename;
	}
	$result=db_query($sql);
	$row=db_fetch_assoc($result);
	return $row['counter'];
}

function playergames_setlastactive($number) {
	$sql="UPDATE ".db_prefix('playergames')." SET lastactive='".date("Y-m-d H:i:s", time())."'";
	$result=db_query($sql);
	return $result;
}

function playergames_clearup() {
	$expiration=get_module_setting("expiration","playergames");
	if ($expiration==0) return;
	$sql="SELECT * FROM ".db_prefix("playergames"). " WHERE lastactive<'".date("Y-m-d H:i:s",strtotime("-$expiration days"))."' AND enddate='".DATETIME_DATEMAX."'";
	$result=db_query($sql);
	require_once("lib/systemmail.php");
	$subject=array("Expired game at the parlor");debug($expiration);debug($sql);
	while ($row=db_fetch_assoc($result)) {
		$body=array("`vSorry, but your game number %s has expired. Please open a new one.",$row['number']);
		systemmail($row['playerone'],$subject,$body);
	}
	$sql="UPDATE ".db_prefix("playergames"). " SET enddate='".date("Y-m-d H:i:s", time())."',didexpire=1 WHERE lastactive<'".date("Y-m-d H:i:s",strtotime("-$expiration days"))."' AND enddate='".DATETIME_DATEMAX."'";
	$result=db_query($sql);
	
	//now cleared up old ones
}
?>
