<?php
/*
v 1.1 updated an auto-pull of the email addresses from my server
*/
function blocker_getmoduleinfo(){
$info = array(
	"name"=>"Blocker",
	"version"=>"1.1",
	"author"=>"`2Oliver Brendel",
	"override_forced_nav"=>true,
	"category"=>"Administrative",
	"download"=>"http://lotgd-downloads.com",
	"settings"=>array(
		"Blocker Settings,title",
		"notify"=>"Do you want an email to the gameadminaddress in case somebody tries to hop in while blocked?,bool|1",
		"The blocked mails can now be found in a table - editor in the grotto for megausers!,note",
		"blockedmails"=>"Mails to block (additionally to some guy's)?,text",
		"blockwarn"=>"Do you want to ONLY alert the admin via mail and NOT block the user from registering?,bool|0",
		"blockedproviders"=>"Which provider extensions (like @gmail.com; @yahoo.com) would you like to block with a refusal text?,text|",
		"Note: leave out the @ and just put in i.e. gmail.com and separate by comma,note",
		),
	);
	return $info;
}

function blocker_install(){
	module_addhook("check-create");
	module_addhook("superuser");
	require_once("lib/tabledescriptor.php");
	$blocktable=array(
		'emailaddress'=>array('name'=>'emailaddress', 'type'=>'varchar(255)', ),
		'reason'=>array('name'=>'reason','type'=>'text'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'emailaddress'),
		);
	synctable(db_prefix("blocker_emails"), $blocktable, true);
	return true;
}

function blocker_uninstall(){
	if(db_table_exists(db_prefix("blocker_emails"))){
		db_query("DROP TABLE ".db_prefix("blocker_emails"));
	}
	return true;
}

function blocker_legacy_filltable() {
	if (get_module_setting('blockedmails','blocker')=='') return array(); //nothing to do
	invalidatedatacache("blocker_blockedmails");
	$table_emails=db_prefix('blocker_emails');
	$merge=explode(",",get_module_setting('blockedmails','blocker'));				
	$sql="INSERT IGNORE INTO $table_emails (emailaddress,reason) VALUES ";
	$array=array();
	$processed=array();
	foreach ($merge as $email) {
		$array[]="('$email','legacy transfer - no reason given originally')";
		$processed[]=$email;
	}
	$sql.=implode(",",array_unique($array));
	$done=db_query($sql);
	if ($done) set_module_setting('blockedmails','');
		else return false;
	return $processed;
}

function blocker_dohook($hookname, $args){
	global $session;
	$table_emails=db_prefix('blocker_emails');
	switch ($hookname) {
		case "superuser":
			if ($session['user']['superuser']&SU_MEGAUSER) {
				addnav("Mechanics");
				addnav("Blocker","runmodule.php?module=blocker");
			}
			break;
		case "check-create":
			if (!isset($args['email'])) return $args; // nothing to do obviously...
			/* just do this if we have an old version previously who put stuff into the setting */
			if (get_module_setting('blockedmails','blocker')!='') {
				//legacy support, fill the table
				blocker_legacy_filltable();
			}
			$sql="SELECT emailaddress FROM $table_emails";
			$result=db_query_cached($sql,"blocker_blockedmails",60);
			while ($row=db_fetch_assoc($result)) {
				$merge[]=$row['emailaddress'];
			}
			$args['email']=strtolower(trim($args['email'])); //remove spaces and make lower case
			$args['email']=hash('sha512',$args['email'].get_module_setting('email_hash_salt','charrestore')); // hash it
			$accounts=array();
				
			//done
			
			/*array (
				"stonerdude42028@yahoo.com",
				"lestat666@rock.com",
				"scyther1@hotmail.co.uk",
				"matrix@mymatrix.info",
				"dumbledorehpo@hotmail.co.uk",
				"craig-fukin-emm@hotmail.co.uk",
				"lestat555@msn.com",
				);*/ //old stuff, now pulled in
			$accounts = array_merge ($accounts,$merge);
			//thank you explode function...
			$accounts = array_diff ($accounts, array(""));
			foreach ($accounts as $email) {
				$email=trim(strtolower($email)); //pullurl returns a chr(10) at the end of the line for some reason
				if (strpos($args['email'],$email)!==false) {
					//found one
					if (!get_module_setting("blockwarn")) { //don't block him if the -warningonly- is active
						$args['blockaccount']=1;
						$msg = array (
							"`@Sorry, %s, you cannot register on this server due to restrictions that apply to your person. You may petition if you think this is unjust, but normally we have our pretty good reasons to do so.`n`n",
							$args['name'],
							);
						$args['msg'] = sprintf_translate($msg);
					}
					if (get_module_setting('notify')) {
						$text = array (
							"Dear Admin, unfortunately somebody with a blocked email tried to register. Here are all the details you need: `nIP: %s`nName: '%s'`nemail: '%s'`nTime: %s", 
							$_SERVER['REMOTE_ADDR'], 
							$args['name'],
							$args['email'], 
							date("Y-m-d H:i:s"),
							);
						$text = sprintf_translate($text);
						$text = str_replace("`n","\n",$text);
						$subject = translate_inline("Blocked user tried to register");
						mail(getsetting("gameadminemail","postmaster@localhost.com"),$subject,$text);
					}
				}
			}
			
			$blockedproviders=get_module_setting('blockedproviders');
			if ($blockedproviders=="") $provs=array();
				else $provs=explode(",",$blockedproviders);
				
			$temp=explode("@",$args['email']);
			if (count($temp)==2)
				$extension=strtolower($temp[1]);
				else
				$extension="";
			foreach ($provs as $myextension) {
				if (strtolower($myextension)==$extension) {
					if (!get_module_setting("blockwarn")) { //don't block him if the -warningonly- is active
						$args['blockaccount']=true;
						$msg = array (
							"`@Sorry, %s, you cannot register on this server due to restrictions for email providers.`nYou cannot register using '`\$%s`@'. You may petition if you think this is unjust, but normally we have our pretty good reasons to do so.`n`n",
							$args['name'],
							$extension,
							);
						$args['msg'] = sprintf_translate($msg);
					}
					if (get_module_setting('notify')) {
						$text = array (
							"Dear Admin, unfortunately somebody with a blocked emailprovider (%s) tried to register. Here are all the details you need: `nIP: %s`nName: '%s'`nemail: '%s'`nTime: %s", 
							$extension,
							$_SERVER['REMOTE_ADDR'], 
							$args['name'],
							$args['email'], 
							date("Y-m-d H:i:s"),
							);
						$text = sprintf_translate($text);
						$text = str_replace("`n","\n",$text);
						$subject = translate_inline("Blocked user tried to register");
						mail(getsetting("gameadminemail","postmaster@localhost.com"),$subject,$text);
					}
				}
			}
			break;
	}
	return $args;
}

function blocker_run(){
	require("modules/blocker/run.php");
}

?>
