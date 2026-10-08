<?php
// addnews ready
// mail ready

use Lotgd\Translator;

function holiday_xmas_getmoduleinfo(){
	$info = array(
		"name"=>"Holiday - Christmas",
		"version"=>"1.0",
		"author"=>"JT Traub",
		"category"=>"Holidays|Holiday Texts",
                "download"=>"core_module",
                "requires"=>array(
                        "datemanager"=>"1.0|By Oliver Brendel",
                ),
		"settings"=>array(
			"Christmas Holiday Settings,title",
			"start"=>"Activation start date (mm-dd)|12-15",
			"end"=>"Activation end date (mm-dd)|12-25",
		),
		"prefs"=>array(
			"Christmas Holiday User Preferences,title",
			"user_ignore"=>"Ignore Christmas Holiday text,bool|0",
		),
	);
	return $info;
}

function holiday_xmas_install(){
        module_addhook("holiday");
        module_addhook("newday");
        module_addhook("village-desc");
        return true;
}

function holiday_xmas_uninstall(){
	return true;
}

function holiday_xmas_munge($in) {
	$out = $in;
	$out = preg_replace("'([^[:alpha:]])ale([^[:alpha:]])'i","\\1egg nog\\2",$out);
	$out = preg_replace("'([^[:alpha:]])hi([^[:alpha:]])'i","\\1Ho Ho Ho\\2",$out);
	$out = preg_replace("'([^[:alpha:]])hello([^[:alpha:]])'i","\\1Ho Ho Ho\\2",$out);
	$out = preg_replace("'Forest'i","Winter Wonderland",$out);
	$out = preg_replace("'Country'i","Snowland",$out);
	$out = preg_replace("'Green Dragon'i","Abominable Snowman",$out);
	$out = preg_replace("'Dragon'i","Abominable Snowman",$out);
	$out = preg_replace("'Penguin'i","Christmas Elf",$out);
	$out = preg_replace("'Kabuto'i","Christmas Elf",$out);
	$out = str_replace("Hall o' Fame","Santa's List",$out);
	$out = str_replace("MightyE","FrostE",$out);
	$out = str_replace("Bluspring", "Rudolph", $out);
	//$out = preg_replace("' Bank'i", " Scrooge's House",$out);
	$out = preg_replace("'([^[:alpha:]])inn([^[:alpha:]])'i","\\1Igloo\\2",$out);
	$out = preg_replace("'garden'i","Ice Rink",$out);
	$out = str_replace("Merick","Santa",$out);
	$out = str_replace("Shinigami","Santa for Naughty Nin",$out);
	$out = str_replace("bounty","Naughty Points",$out);
	$out = str_replace("the dead", "the coal recipients", $out);
	$out = preg_replace("'([^[:alpha:]])dead([^[:alpha:]])'i",
			"\\1a coal recipient\\2",$out);
	$out = preg_replace("'Village'i","North Pole",$out);
	$vname = getsetting("villagename", LOCATION_FIELDS);
	$out = preg_replace("'$vname'i","North Pole",$out);
	$out = preg_replace("'Sunagakure'i","Village of Winter Sand",$out);
	$out = preg_replace("'Kirigakure'i","Village of Icy Mist",$out);
	$out = preg_replace("'Otogakure'i","Village of Audible Winter",$out);
	$out = preg_replace("'Academy Student'i","Snownin",$out);
	$out = preg_replace("'Shinobi'","Snownin",$out);
	$out = preg_replace("'shinobi'","snownin",$out);
	$out = preg_replace("'Farmgirl'i","Snowgirl",$out);
	$out = preg_replace("'Genin'i","Great Snownin",$out);
	$out = preg_replace("'Chuunin'i","Greater Snownin",$out);
	$out = preg_replace("'Jounin'i","Extraordinary Snownin",$out);
	$out = preg_replace("'Sannin'i","Yuukinin",$out);
	$out = preg_replace("'Hokage'i","Santa's Right Hand",$out);
	$out = preg_replace("'Pony'i", "Baby Reindeer", $out);
	$out = preg_replace("'Stallion'i", "Magic Reindeer", $out);
	$out = preg_replace("'Gelding'i", "Reindeer", $out);
	$out = preg_replace("'thick mold'i", "heavy snowfall", $out);
	$out = preg_replace("'Orochimaru'i", "Abominable Snowman", $out);
	$out = preg_replace("'Kyuubi'i", "Spirit of Christmas", $out);
	$out = preg_replace("'dwelling'i", "snowy home", $out);
	$out = preg_replace("'Dwelling'i", "Snowy Home", $out);
	$out = preg_replace("'house'i", "snowpalace", $out);
	$out = preg_replace("'Kusanagi'i", "Christmas", $out);
	$out = preg_replace("'fields'i", "snowfields", $out);
	$out = preg_replace("'Kunai'i", "Winterlove", $out);
	$out = preg_replace("'hot and sunny'i", "a smaller snowmelt", $out);	
	return $out;
}

function holiday_xmas_dohook($hookname,$args){
        require_once('modules/datemanager.php');

        $datecheck = datemanager_datecheck(get_module_setting('start'), get_module_setting('end'), 0);

        switch($hookname){
        case "newday":
                if(get_module_pref("user_ignore")) break;
                if ($datecheck > 0) {
                        output("`2It's Holiday Season! Here is a `\$small buff`2 for you...`n`n");
                        apply_buff('xmas_buff',array("name"=>"`\$C`2hristmas `\$S`2pirits","rounds"=>25,"atkmod"=>1.1, "schema"=>"module-holiday-xmas"));
                }
                break;
        case "holiday":
                if(get_module_pref("user_ignore")) break;
                if ($datecheck > 0) {
                        $args['text'] = holiday_xmas_munge($args['text']);
                }
                break;
        case 'village-desc':
                if (get_module_pref('user_ignore')) {
                        break;
                }

                if ($datecheck <= 0) {
                        break;
                }

                $remaining = datemanager_get_time_remaining(get_module_setting('start'), get_module_setting('end'));

                if ($remaining === null) {
                        break;
                }

                $countdown = datemanager_format_countdown($remaining);

                Translator::getInstance()->setSchema('module-holiday-xmas');
                output("`n`2Christmas cheer lasts %s`0`n", $countdown);
                Translator::getInstance()->setSchema();
                break;
        }
        return $args;
}

function holiday_xmas_run(){

}
?>
