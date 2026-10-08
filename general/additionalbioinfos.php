<?php
//just did this mod to let users have more text
//yes,I like British English, but for users I will comply


function additionalbioinfos_getmoduleinfo(){
	$info = array(
	    "name"=>"Additional Bioinfos",
		"description"=>"This module offers more text to add to your bioinfos... in an extra box of course",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"General",
		"download"=>"http://lotgd-downloads.com",
		"settings"=>array(
		"Additional Bioinformations - Settings,title",
		"Note: This restrictions can be up to 65000 Chars,note",
		"charlimit"=>"How many chars may the user enter?,int|400",
		"physicalstats"=>"Let them enter physical stats?,bool|1",
		"additional"=>"Enable additional text,bool|1",
		"killc"=>"Disable Center Tags,bool|1",
		),
		"prefs"=>array(
		    "Additional Bioinfos,title",
			"user_showbioinfo"=>"Do you want to display this info in your bio?,bool|0",
			"user_additionalbioinfo"=>"Enter your additional Bioinformation here,textarea",
			"0 equals not-set,note",
			"user_age"=>"Your Age:,floatrange,0,120,1|0",
			"80 equals not-set,note",
			"user_height"=>"Your height (in cm):,floatrange,80,215,1|120",
			"0 equals not-set,note",
			"user_eyecolour"=>"Your eyecolor (color codes allowed):,text",
			"user_haircolour"=>"Your haircolor (color codes allowed):,text",
		),
		);
    return $info;
}

function additionalbioinfos_install(){
	output_notl ("Performing Install on Additional Bioinfo Module.`n`n");
	module_addhook_priority("bioinfo",75);
	module_addhook_priority("footer-prefs",75);
	return true;
}

function additionalbioinfos_uninstall()
{
	output_notl ("Performing Uninstall on Additional Bioinfo Module. Thank you for using!`n`n");
	return true;
}

function additional_bioinfos_sanitize($string) {
	$string=str_replace("`c","",$string);
	$string=str_replace("`b","",$string);
	$string=str_replace("`i","",$string);
	$string=sanitize_mb(soap($string));
	return $string;

}


function additionalbioinfos_dohook($hookname, $args){
	global $session;
	switch ($hookname)
	{
	case "bioinfo":
		if (get_module_pref("user_showbioinfo","additionalbioinfos",$args['acctid'])) {
			if (get_module_setting("physicalstats")) {

				//stringlength

				$fetch = array(
					"user_age"=>"age",
					"user_height"=>"height",
					"user_eyecolour"=>"eyes",
					"user_haircolour"=>"hair",
					);
				foreach ($fetch as $key=>$var) {
					$$var=stripslashes(get_module_pref($key,"additionalbioinfos",$args['acctid']));
					$$var=additional_bioinfos_sanitize($$var);
				}


#				$age=stripslashes(get_module_pref("user_age","additionalbioinfos",$args['acctid']));
#				$height=(int)stripslashes(get_module_pref("user_height","additionalbioinfos",$args['acctid']));
#				$eyes=stripslashes(get_module_pref("user_eyecolour","additionalbioinfos",$args['acctid']));
#				$hairs=stripslashes(get_module_pref("user_haircolour","additionalbioinfos",$args['acctid']));

				
				$age=(int) $age;
				$height=(int) $height;
				$eyes=additional_bioinfos_sanitize($eyes);
				$eyes=mb_substr($eyes,0,15,getsetting("charset", "ISO-8859-1"));
				$hair=mb_substr($hair,0,15,getsetting("charset", "ISO-8859-1"));
				$hair=additional_bioinfos_sanitize($hair);




				if ($age>0) output_notl("`^Age: `@%s`@ Years`n",$age);

				if ($height>80) output_notl("`^Height: `@%s`@ cm`n",$height);
				if ($eyes!='') output_notl("`^Eyecolor: `@%s`@`n",$eyes);
				if ($hair!='') output_notl("`^Haircolor: `@%s`@`n",$hair);
			}
			if (get_module_setting("additional")) {
				$bio=get_module_pref("user_additionalbioinfo","additionalbioinfos",$args['acctid']);
				$bio=str_replace(chr(13),"`n",$bio);
				if (get_module_setting('killc')) $bio=additional_bioinfos_sanitize($bio);
				output_notl("`^Bio: `@`n%s`@`n",stripslashes(stripslashes($bio)));
			}
		}
		break;
	case "footer-prefs":
		$bio=get_module_pref("user_additionalbioinfo");
		$eyes=get_module_pref("user_eyecolour");
		$hairs=get_module_pref("user_haircolour");
		$maxlength=get_module_setting("charlimit");
		$maxlength_eyes=25;
		output("Note: `^Charlimit for additional bioinfos is %s chars.`n",$maxlength);
		output(" This additional text is turned `\$%s`^ by your admin.`n",(get_module_setting("additional")?"on":"off"));
		output(" Note that only numbers are used for Age and Height. Any other input will not be displayed.`n");
		output(" This additional physical informations are turned `\$%s`^ by your admin.",(get_module_setting("physicalstats")?"on":"off"));
		output_notl("`n");
		if (mb_strlen($bio,getsetting("charset", "ISO-8859-1"))>$maxlength) {
			output("Sorry, your description was too long, it has been cut to the proper size!");
			output_notl("`n");
			output("You entered %s more chars than allowed!",mb_strlen($bio,getsetting("charset", "ISO-8859-1"))-$maxlength);
			output_notl("`n");
			output("Please edit your entered text if you don't want to have it cut (the original text is still displayed).");
			$bio=mb_substr($bio,0,$maxlength,getsetting("charset", "ISO-8859-1"));
		}
		set_module_pref("user_additionalbioinfo",$bio);
		if (mb_strlen($eyes,getsetting("charset", "ISO-8859-1"))>$maxlength_eyes) {
			output("Sorry, your eye description was too long, it has been cut to the proper size!");
			output_notl("`n");
			output("You entered %s more chars than allowed!",mb_strlen($eyes,getsetting("charset", "ISO-8859-1"))-$maxlength_eyes);
			output_notl("`n");
			output("Please edit your entered text if you don't want to have it cut (the original text is still displayed).");
			$eyes=mb_substr($eyes,0,$maxlength_eyes,getsetting("charset", "ISO-8859-1"));
		}
		//set_module_pref("user_eyecolour",$eyes);
		if (mb_strlen($hairs,getsetting("charset", "ISO-8859-1"))>$maxlength_eyes) {
			output("Sorry, your eye description was too long, it has been cut to the proper size!");
			output_notl("`n");
			output("You entered %s more chars than allowed!",mb_strlen($eyes,getsetting("charset", "ISO-8859-1"))-$maxlength_eyes);
			output_notl("`n");
			output("Please edit your entered text if you don't want to have it cut (the original text is still displayed).");
			$hairs=mb_substr($hairs,0,$maxlength_eyes,getsetting("charset", "ISO-8859-1"));
		}
		//set_module_pref("user_haircolour",$hairs);
		break;
	default:

	break;
	}
	return $args;
}

function additionalbioinfos_run(){
}

?>
