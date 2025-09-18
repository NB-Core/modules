<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

function sectionedchats_getmoduleinfo(){
	$info = array(
		"name" => "Sectioned Chats",
		"author" => "`i`)Ae`7ol`&us`i`0, with additional code by `&`i`bXpert`b`i`0, added clan support (+nb)",
		"version" => "1.0",
		"category" => "Commentary",
		"download" => "http://dragonprime.net/index.php?topic=12424.0",
		"prefs" => array(
			"RP/OOC Chats Prefs,title",
			"chat_v" => "Which chat has user saved in village,enum,chat1,RP Chat,chat2,OOC Chat|chat1",
			"chat_s" => "Which chat has user saved in superuser,enum,chat1,Business Chat,chat2,OOC Chat|chat1",
			"chat_t_v"=>"Read times for chats for village,text|2005-01-01 01:00:00#2005-01-01 01:00:00",
			"chat_t_s"=>"Read times for chats for superuser,text|2005-01-01 01:00:00#2005-01-01 01:00:00",
			"chat_c" => "Which chat has user saved in clans,enum,chat1,RP Chat,chat2,OOC Chat|chat1",
			"chat_t_c"=>"Read times for chats for superuser,text|2005-01-01 01:00:00#2005-01-01 01:00:00",
		),
	);
	return $info;
}

function sectionedchats_install(){
	module_addhook_priority("moderate",100);
	module_addhook("superusertop");
	module_addhook("village");
	module_addhook("clan-commentary");
	return true;
}

function sectionedchats_uninstall(){
	return true;
}

function sectionedchats_dohook($hookname, $args){
	global $SCRIPT_NAME;
	switch ($hookname){
		case "moderate":
			// Checking for extra villages made by Generic City or Race modules
			foreach ($args as $key => $val){
				if (substr($key, 0, 7) == "village") $args[$key."-ooc"] = sprintf_translate($val." OOC");
			}
			$args["village-ooc"] = sprintf_translate(getsetting("villagename","Degolburg")." OOC");
			$args["superuser-ooc"] = sprintf_translate("Grotto OOC");
		break;
		case "village":
		case "superusertop":
		case "clan-commentary":
			$h = $hookname[0]; // v = village, s=superuser, c=clans might want to expand that
			$chat = httpget("chat");
			$date = date("Y-m-d H:i:s");
			
			if ($chat) set_module_pref("chat_$h",$chat);
			
			$chat0 = get_module_pref("chat_$h");
			list($time1, $time2) = explode("#",get_module_pref("chat_t_$h"));
			
			if ($chat0 == "chat1") set_module_pref("chat_t_$h",date("Y-m-d H:i:s")."#".$time2);
			if ($chat0 == "chat2") set_module_pref("chat_t_$h",$time1."#".date("Y-m-d H:i:s"));
			
			$link1 = $SCRIPT_NAME."?chat=chat1";
			$link2 = $SCRIPT_NAME."?chat=chat2";
			addnav("", $link1);
			addnav("", $link2);
                        $baseSection = $args['section'];
                        $connection = Database::getDoctrineConnection();
                        $commentaryTable = Database::prefix('commentary');
                        $query = "SELECT COUNT(commentid) FROM {$commentaryTable} WHERE section = :section AND postdate > :postdate";

                        if ($chat0 == "chat1"){
                                $result = $connection->executeQuery(
                                        $query,
                                        [
                                                'section' => $baseSection.'-ooc',
                                                'postdate' => $time2,
                                        ],
                                        [
                                                'section' => ParameterType::STRING,
                                                'postdate' => ParameterType::STRING,
                                        ]
                                );
                                $sql = (int) $result->fetchOne();
                                $style1 = "style='color:#ff0000;'";
                                if ($sql) $style2 = "style='color:blue;'"; else $style2 = "";
                        } else {
                                $result = $connection->executeQuery(
                                        $query,
                                        [
                                                'section' => $baseSection,
                                                'postdate' => $time1,
                                        ],
                                        [
                                                'section' => ParameterType::STRING,
                                                'postdate' => ParameterType::STRING,
                                        ]
                                );
                                $sql = (int) $result->fetchOne();
                                if ($sql) $style1 = "style='color:blue;'"; else $style1 = "";
                                $style2 = "style='color:#ff0000;'";
                        }

		 	invalidatedatacache("comments-".$args['section']);
 	                invalidatedatacache("comments-or11");

			
			$chatnames = array(
				"v" => array("chat1" => "Roleplay", "chat2" => "OOC"), 
				"c" => array("chat1" => "Clan Roleplay", "chat2" => "OOC"), 
				"s" => array("chat1" => "Business", "chat2" => "General")
			);
			$chatnames = translate_inline($chatnames);
			
			$active_chat1 = ($chat0=='chat1'?translate_inline("(Active)"):'');
			$active_chat2 = ($chat0=='chat2'?translate_inline("(Active)"):'');
				
			output_notl("`0");
			rawoutput("<center><big><b>[<a href='$link1' $style1>{$chatnames[$h]['chat1']}</a>$active_chat1] [<a href='$link2' $style2>{$chatnames[$h]['chat2']}</a>$active_chat2]</b></big></center><br />");
			
			if ($chat0 == "chat2") {
				$chatao = "-ooc"; 		
				//damn those long names...
				if ($args['section']=="village-cityamwayr") $args['section']="village-cityam";
			} else $chatao = "";

			$args['section'] .= $chatao;
		break;
	}
	return $args;
}
?>
