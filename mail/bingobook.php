<?php

function bingobook_getmoduleinfo(){
	$info = array(
		"name"=>"Bingo Book",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Mail", 
		"override_forced_nav"=>true,
		"settings"=>array(
			"Bingo Book - Settings,title",
		),
		"prefs"=>array(
			"Bingo Book,title",
			"bingo"=>"`^Bingo Book of this user,text|",
		),
	);
	return $info;
}

function bingobook_install(){
	module_addhook("mailfunctions");
	module_addhook("delete_character");
	$bingobook=array(
		'id'=>array('name'=>'id', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
		'userid'=>array('name'=>'userid', 'type'=>'int(11) unsigned'),
		'bingoid'=>array('name'=>'bingoid', 'type'=>'int(11) unsigned'),
		'entrydate'=>array('name'=>'entrydate', 'type'=>'datetime', 'default'=>DATETIME_DATEMIN),
		'comment'=>array('name'=>'comment', 'type'=>'text', 'null'=>'1'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'id'),
		'key-one'=> array('name'=>'user', 'type'=>'key', 'unique'=>'1', 'columns'=>'userid,bingoid'),
//		'key-two'=> array('name'=>'bingo', 'type'=>'key', 'unique'=>'0', 'columns'=>'bingoid'),
		);
	require_once("lib/tabledescriptor.php");
	synctable(db_prefix("bingobook"), $bingobook, true);
	return true;
}

function bingobook_uninstall(){
	return true;
}

function bingobook_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "mailfunctions":
			output_notl("`c`\$[`0");
			$t = appoencode(translate_inline("`vBingo Book"));
			rawoutput("<a href='runmodule.php?module=bingobook&op=list'>$t</a>");
			addnav('','runmodule.php?module=bingobook&op=list');
			output_notl("`\$]`c`0");
		break;
		case "delete_character":
			//clean up bingobook if a char gets deleted
			require_once("modules/bingobook/func.php");
			bingobook_clear($args['acctid']);
		break;
	}
	return $args;
}

function bingobook_run(){
	global $session;
	require_once("modules/bingobook/func.php");
	$op = httpget('op');
	popup_header("Bingo Book");
	output_notl("`c`\$[`0");
	$t = appoencode(translate_inline("`vBingo Book"));
	rawoutput("<a href='runmodule.php?module=bingobook&op=list'>$t</a>");
	addnav('','runmodule.php?module=bingobook&op=list');
	output_notl("`\$] - [`0");
	$t = appoencode(translate_inline("`vAdd Entry"));
	rawoutput("<a href='runmodule.php?module=bingobook&op=search'>$t</a>");
	addnav('','runmodule.php?module=bingobook&op=search');
	output_notl("`\$] - [`0");
	$t = appoencode(translate_inline("`gBack to the Shinobi Mailbox"));
	rawoutput("<a href='mail.php'>$t</a>");
	output_notl("`\$]`c`Q`n");
	
	require_once("modules/bingobook/bingobook_$op.php");
	if ($op=='remove') {
		bingobook_remove();
		$op="list";
		require_once("modules/bingobook/bingobook_list.php");
	}

	$fname="bingobook_".$op;
	$fname();
	popup_footer();
}
?>
