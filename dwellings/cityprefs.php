<?php
require_once("lib/commentary.php");
require_once("lib/showform.php");
require_once("modules/cityprefs/lib.php");
        
function cityprefs_getmoduleinfo() {
	$info = array(
		"name"=>"City Preferences Addon",
		"version"=>"20051213",
		"author"=>"Sixf00t4",
		"category"=>"General",
		"description"=>"Gives the ability to use prefs based on cities",
		"vertxtloc"=>"http://dragonprime.net/users/sixf00t4/",
		"download"=>"http://dragonprime.net/users/sixf00t4/cityprefs.zip",
    );                
	return $info;
}

function cityprefs_install(){
    global $session;
	require_once("lib/tabledescriptor.php");
	$cityprefs = array(
		'cityid'=>array('name'=>'cityid', 'type'=>'int unsigned',	'extra'=>'not null auto_increment'),
		'module'=>array('name'=>'module', 'type'=>'varchar(255)', 'extra'=>'not null'),
		'cityname'=>array('name'=>'cityname', 'type'=>'varchar(255)', 'extra'=>'not null'),
		'key-PRIMARY'=>array('name'=>'PRIMARY', 'type'=>'primary key',	'unique'=>'1', 'columns'=>'cityid'),
		'index-module'=>array('name'=>'module', 'type'=>'index', 'columns'=>'module'),
		'index-cityname'=>array('name'=>'cityname', 'type'=>'index', 'columns'=>'cityname'));
    synctable(db_prefix('cityprefs'), $cityprefs, true);
    if (!is_module_active('cityprefs')){
        if ($session['user']['superuser']&~SU_DOESNT_GIVE_GROTTO) output_notl("`4Installing cityprefs Module.`n");
		$conn = \Lotgd\MySQL\Database::getDoctrineConnection();
		$conn->executeStatement(
			"INSERT INTO ".\Lotgd\MySQL\Database::prefix("cityprefs")." (module,cityname) VALUES ('none', :cityname)",
			['cityname' => (string) getsetting("villagename",LOCATION_FIELDS)]
		);
		$vloc = array();
		$vloc = modulehook("validlocation", $vloc);
		ksort($vloc);
		foreach($vloc as $loc=>$val) {
			$modulename = $conn->fetchOne(
				"select modulename from ".\Lotgd\MySQL\Database::prefix("module_settings")." where value = :loc and setting='villagename'",
				['loc' => (string) $loc]
			);
			$conn->executeStatement(
				"INSERT INTO ".\Lotgd\MySQL\Database::prefix("cityprefs")." (module,cityname) VALUES (:module, :loc)",
				['module' => $modulename === false ? '' : (string) $modulename, 'loc' => (string) $loc]
			);
		}
    }else{
        if ($session['user']['superuser']&~SU_DOESNT_GIVE_GROTTO) output("`4Updating cityprefs Module.`n");
    }

    module_addhook("superuser");
    module_addhook("changesetting");
	return true;
}

function cityprefs_uninstall(){
    output("`4Un-Installing cityprefs Module.`n");
    $sql = "DROP TABLE ".db_prefix("cityprefs");
    db_query($sql);
    $sql = "delete from ".db_prefix("module_objprefs")." where objtype='city'";
    db_query($sql); // static
	return true;
}

function cityprefs_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
        case "changesetting":
   			if ($args['setting'] == "villagename") {
				// $args values come from settings (not slashed): bind raw.
				\Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement(
					"UPDATE ".\Lotgd\MySQL\Database::prefix("cityprefs")." SET cityname = :new WHERE cityname = :old",
					['new' => (string) $args['new'], 'old' => (string) $args['old']]
				);
            }
			break;
        case "superuser":
			if ($session['user']['superuser'] & SU_EDIT_USERS) {
				addnav("Editors");
				addnav("City Prefs","runmodule.php?module=cityprefs&op=su");
			}
		break; 
	}
	return $args;
}

function cityprefs_run() {
	global $session;
    page_header("City Prefs Editor");
    $op=httpget('op');
	$cityid = (int) httpget('cityid');
	if($cityid>0){
	$cityname=get_cityprefs_cityname("cityid",$cityid);
		page_header("%s Properties",$cityname);
		$modu=get_cityprefs_module("cityid",$cityid);
		if($modu!="none"){
			addnav("Operations");
			addnav("Module settings","configuration.php?op=modulesettings&module=$modu");
		}
		addnav("Navigation");
		addnav(array("Journey to %s",$cityname),"runmodule.php?module=cities&op=travel&city=".urlencode($cityname)."&su=1");
	}
	addnav("Navigation");	
   addnav("Back to the Grotto","superuser.php");   
   if(is_module_active("modloc")) addnav("Module locations","runmodule.php?module=modloc");
   if($op!="su") addnav("Back to city list","runmodule.php?module=cityprefs&op=su");
    switch ($op) {
        case "su":
			addnav("Operations");
            addnav("Auto-add new cities","runmodule.php?module=cityprefs&op=update");  
            $id=translate_inline("ID");
            $name=translate_inline("City Name");
            $module=translate_inline("Module");
            $edit=translate_inline("Edit");
            $cityrows = \Lotgd\MySQL\Database::getDoctrineConnection()->fetchAllAssociative(
                "select * from ".\Lotgd\MySQL\Database::prefix("cityprefs")
            );
            rawoutput("<table border='0' cellpadding='3' cellspacing='0' align='center'><tr class='trhead'><td style=\"width:50px\">$id</td><td style='width:150px' align=center>$name</td><td align=center>$module</td><td align=center>$edit</td></tr>"); 
            for ($i = 0; $i < count($cityrows); $i++){
                $row = $cityrows[$i];
                $vloc = array();
                $vname = getsetting("villagename", LOCATION_FIELDS);
                $vloc[$vname] = "village";
                $vloc = modulehook("validlocation", $vloc);
                ksort($vloc);
                foreach($vloc as $loc=>$val) {
                    if ($loc == $row['cityname']) $area=$val;
                }
                rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td align=center>".$row['cityid']."</td><td align=center>");
                output_notl("%s",$row['cityname']);
                rawoutput("</td><td align=center>");
                output_notl("%s",$row['module']);
                rawoutput("</td><td align=center>");
                rawoutput("<a href='runmodule.php?module=cityprefs&op=editmodule&area=".htmlentities($val)."&cityid=".$row['cityid']."'>$edit</a></td></tr>");
                addnav("","runmodule.php?module=cityprefs&op=editmodule&area=".htmlentities($val)."&cityid=".$row['cityid']."");  
            }
            rawoutput("</table>");
            break;
    
        case "update":
            $vloc = array();
            $vloc = modulehook("validlocation", $vloc);
            ksort($vloc);
            $out=0;
            $conn = \Lotgd\MySQL\Database::getDoctrineConnection();
            foreach($vloc as $loc=>$val) {
                $existing = $conn->fetchAllAssociative(
                    "select cityname from ".\Lotgd\MySQL\Database::prefix("cityprefs")." where cityname = :loc",
                    ['loc' => (string) $loc]
                );
                if(count($existing)==0){
                    $modulename = $conn->fetchOne(
                        "select modulename from ".\Lotgd\MySQL\Database::prefix("module_settings")." where value = :loc and setting='villagename'",
                        ['loc' => (string) $loc]
                    );
                    $conn->executeStatement(
                        "INSERT INTO ".\Lotgd\MySQL\Database::prefix("cityprefs")." (module,cityname) VALUES (:module, :loc)",
                        ['module' => $modulename === false ? '' : (string) $modulename, 'loc' => (string) $loc]
                    );
                    $out=1;
                    output("`n`@%s`0 was added.",$loc);
                }
            }
            if($out==0) output("There were no new locations found.");
        break; 
        case "editmodule": //code from clan editor by CortalUX
        case "editmodulesave":
			addnav("Operations");
			addnav("Edit city name and module","runmodule.php?module=cityprefs&op=editcity&cityid=$cityid");
			addnav("Delete this city","runmodule.php?module=cityprefs&op=delcity&cityid=$cityid");
			$mdule = httpget("mdule");
			if($mdule==""){
				output("Select a pref to edit.`n`n");
			//$area = httpget("area");
            //viewcommentary($area,"Speak into this city",25,"projects");
			}else{
				if ($op=="editmodulesave") {
					// Save module prefs
					$post = httpallpost();
					foreach($post as $key=>$val) {
						set_module_objpref("city", $cityid, $key, stripslashes($val), $mdule);
					}
					output("`^Saved!`0`n");
				}
				require_once("lib/showform.php");
				rawoutput("<form action='runmodule.php?module=cityprefs&op=editmodulesave&cityid=$cityid&mdule=$mdule' method='POST'>");
				module_objpref_edit("city", $mdule, $cityid);
				rawoutput("</form>");
				addnav("","runmodule.php?module=cityprefs&op=editmodulesave&cityid=$cityid&mdule=$mdule");
				//code from clan editor by CortalUX
			}
 			addnav("Module Prefs");
			module_editor_navs("prefs-city","runmodule.php?module=cityprefs&op=editmodule&cityid=$cityid&mdule=");
		break;
        
        case "editcity":
			output("Changing these values will not affect the city itself, just what city is associated with the preferences.  This is useful if you want to preserve prefs after removing a city.");
			addnav("Navigation");
			addnav("Back to city properties","runmodule.php?module=cityprefs&op=editmodule&cityid=$cityid");
            $row = \Lotgd\MySQL\Database::getDoctrineConnection()->fetchAssociative(
                "select * from ".\Lotgd\MySQL\Database::prefix("cityprefs")." where cityid = :cityid",
                ['cityid' => (int) $cityid]
            );
            $module=$row['module'] ?? null;
            $city=$row['cityname'] ?? null;
            $submit=translate_inline("Submit");
            rawoutput("<form action='runmodule.php?module=cityprefs&op=editcity2&cityid=$cityid' method='POST'>");
            addnav("","runmodule.php?module=cityprefs&op=editcity2&cityid=$cityid");
            rawoutput("<input name='cityname' id='cityname' value='".htmlspecialchars((string) $city, ENT_QUOTES | ENT_SUBSTITUTE, getsetting('charset', 'UTF-8'))."' size='40' maxlength='255'><br>");
            rawoutput("<input name='modulename' id='modulename' value='".htmlspecialchars((string) $module, ENT_QUOTES | ENT_SUBSTITUTE, getsetting('charset', 'UTF-8'))."' size='40' maxlength='255'><br>");
            rawoutput("<input type='submit' class='button' value='$submit'></form>");              
        break;
        
        case "editcity2":
			addnav("Navigation");
			addnav("Back to city properties","runmodule.php?module=cityprefs&op=editmodule&cityid=$cityid");
            // httppost() escapes with addslashes(); the values are bound below.
            $cityname = stripslashes((string) httppost('cityname'));
            $modulename = stripslashes((string) httppost('modulename'));
            \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement(
                "update ".db_prefix("cityprefs")." set cityname = :cityname, module = :module where cityid = :cityid",
                ['cityname' => (string) $cityname, 'module' => (string) $modulename, 'cityid' => $cityid],
                ['cityname' => \Doctrine\DBAL\ParameterType::STRING, 'module' => \Doctrine\DBAL\ParameterType::STRING, 'cityid' => \Doctrine\DBAL\ParameterType::INTEGER]
            );
            output("The city name is now %s and the module name is %s.",$cityname,$modulename);
        break;
        
        case "delcity":
			addnav("Navigation");
			addnav("Back to city properties","runmodule.php?module=cityprefs&op=editmodule&cityid=$cityid");
            $cityid = (int) httpget('cityid');
            output("Are you sure you want to delete this city?  All city prefs will be deleted.  If you would like to retain these settings for a future city, just rename it.");
        break;

        case "delcity2":
            \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement(
                "delete from ".\Lotgd\MySQL\Database::prefix("cityprefs")." where cityid = :cityid",
                ['cityid' => (int) $cityid]
            );
            output("The city has been deleted.");
        break;
        }
    page_footer();
    }    
?>
