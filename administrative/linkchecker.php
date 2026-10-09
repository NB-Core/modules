<?php

/* get the original code at dragonprime.net, user name Nightborn

v1.0 basic functions
v1.01 added prefs so select the table layout


*/



function linkchecker_getmoduleinfo()
{
	$info = array
		(
		"name"=>"Linkchecker for your installed modules",
		"version"=>"1.01",
		"author"=>"Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"http://lotgd-downloads.com",
		"settings"=>array(
						"Linkchecker - Preferences, title",
						"showformal"=>"Column formal name shown,bool|false",
						"shortenformal"=>"Column formal name shortend,floatrange,0,100,5|25",
						"(only applicable if it is shown - 0 means no shortening),note",
						"showauthor"=>"Column module author shown,bool|false",
						"shortenauthor"=>"Column module author shortend,floatrange,0,100,5|25",
						"(only applicable if it is shown - 0 means no shortening),note",
						"showfilename"=>"Column filename shown,bool|true",
						"shortenfilename"=>"Column filename shortend,floatrange,0,100,5|25",
						"(only applicable if it is shown - 0 means no shortening),note",						
						"showdownload"=>"Column downloadpath shown, bool|true",
						"shortendownload"=>"Column downloadpath shortend,floatrange,0,100,5|0",
						"(only applicable if it is shown - 0 means no shortening),note",						
						),
		);
	return $info;
}

function linkchecker_install(){
	module_addhook("footer-modules");
	if (!is_module_active('linkchecker')){
		output("`4Installing Linkchecker Module.`n");
	}else{
		output("`4Updating Linkchecker Module.`n");
	}
	return true;
}

function linkchecker_uninstall(){
	output("Uninstalling linkchecker module.`n");
	return true;
}

function linkchecker_dohook($hookname,$args){
	global $session;
	switch ($hookname)
	{
	case "footer-modules":
		if ($session['user']['superuser'] & SU_MANAGE_MODULES) {;
			addnav("Navigation");
			addnav("Check Moduledownloadlinks",
					"runmodule.php?module=linkchecker");
		}
		break;
	}
	return $args;
}

function linkchecker_run()
{
	global $session;
	// The link is only offered to module managers; enforce the same here.
	check_su_access(SU_MANAGE_MODULES);
	page_header("Linkchecker");	
	require_once("lib/superusernav.php");
	require_once("lib/dbwrapper.php");
	superusernav();
	addnav("Back to Module Manager","modules.php");
	$i=0;
	$op = httpget('op');
	$showformal=get_module_setting("showformal");
	$shortenformal=get_module_setting("shortenformal");
	$showauthor=get_module_setting("showauthor");
	$shortenauthor=get_module_setting("shortenauthor");
	$showfilename=get_module_setting("showfilename");
	$shortenfilename=get_module_setting("shortenfilename");	
	$showdownload=get_module_setting("showdownload");
	$shortendownload=get_module_setting("shortendownload");	
	switch ($op)
	{
	case "execute":
	$sql= "SELECT formalname,moduleauthor,filename,download FROM ".db_prefix("modules")." ORDER BY formalname;"; 
	$result = db_query($sql);
	output("`bOperation finished, %s modules checked`b`n`n",db_num_rows($result));
	output("Legend:`n`n");
	output("`@Autodownloading`0: Module has the ability to provide itself for download`n");
	output("`)Core Module`0: Well, it's Core`n");
	output("`$ Not publically released`0: The download information is empty`n");
	output("No http link: This module has no valid link to check, i.e. if it's 'Hello' in it`n`n");
	output("Ok: This file link is valid and accessible`n");;
	output("`$`bNot ok!`0`b: This file link was tried to check, but seems invalid/no file -> you have to check that!`n");
	output("`n`nThe last case might happen when:`n");
	output("-The file is no longer available there`n");
	output("-The author misspelled the filename`n`n");
	rawoutput("<table border='0' cellpadding='2' cellspacing='0' >");  //build up the first line of the table
	rawoutput("<tr class='trhead'><td>");
	if ($showformal)
		{
		output ("Name");
		rawoutput("</td><td>");
		}
	if ($showauthor)
		{
		output ("Author");
		rawoutput("</td><td>");
		}
	if ($showfilename)
		{
		output ("Filename");
		rawoutput("</td><td>");
		}
	if ($showdownload)
		{
		output ("Download");
		rawoutput("</td><td>");
		}
	output("Link ok?");
	rawoutput("</td></tr>");
	if (db_num_rows($result)>0)  
		{
		$report=error_reporting(0);
		while ($row = db_fetch_assoc($result))   
			{
			$i++;
			rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");			
			if ($showformal)
				{
				$name=sanitize($row['formalname']);
				if ($shortenformal>0 && strlen($name)>$shortenformal+3) $name=substr($name,0,$shortenformal)."...";
				rawoutput($name);
				rawoutput("</td><td>");
				
				}
			if ($showauthor)
				{
				$author=sanitize($row['moduleauthor']);
				if ($shortenauthor>0 && strlen($author)>$shortenauthor+3) $author=substr($author,0,$shortenauthor)."...";	
				rawoutput(htmlentities($author));
				rawoutput("</td><td>");
				}
			if ($showfilename)
				{
				$filename=$row['filename'];
				if ($shortenfilename>0 && strlen($filename)>$shortenfilename+3) $filename=substr($filename,0,$shortenfilename)."...";	
				rawoutput($filename);
				rawoutput("</td><td>");
				}
			$download=$row['download'];
			$displaydownload=$download;
			if ($showdownload)
				{
				if ($shortendownload>0 && strlen($download)>$shortendownload+3)	$displaydownload=substr($download,0,$shortendownload)."...";	
				rawoutput($displaydownload);
				rawoutput("</td><td>");	
				}			
			if (substr($download,0,4)<>"core" && $download<>"" && substr($download,0,4)=="http" && (substr($download,-8)<>"download" && (substr($download,0,8)<>"modules/")))
				{
				$linkcheck=fopen($download,"r");  //try to open, and THIS is the time-eating part... the all-in all module needed 3 sec on my testserver, the rest was all due to the fopen
				//$linkcheck=true;  testing purposes
				if ($linkcheck)  //if it's open
					{
					output_notl("Ok");
					fclose($linkcheck);					
					}
					else //if it's not open
					{
					output("`$`bNot ok!`0`b");
					}
				}
				else
				{
				if (substr($download,-8)=="download" && (substr($download,0,8)=="modules/")) $download="auto";
				switch ($download)
					{
					case "core": case "core_module":
						output("`)Core Module`0");
						break;
					case "":
						output("`$ Not publically released`0");
						break;
					case "auto":
						output("`@Autodownloading`0");
						break;
					default:
						output("No Http Link");	
						break;
					}
				}
			rawoutput("</td></tr>");	
			/*if ($i>10000)
				{
				rawoutput("</table>");
				error_reporting($report);				
				page_footer();
				} //testing only;*/
			}
		rawoutput("</table>");
		error_reporting($report);
		}
		break;
	default:
		output("`b`$ Attention`b`0... this function queries all your modules (except core modules, auto-downloading[anpera], non-http links and with no links at all [not publically released ones])");
		output("`n`nIt takes some time to execute... please be patient. It may run from half a minute to several minutes...`n`n");
		output("This depends heavily on the connection speed and the speed of the server where the download source should be.`n`n");
		rawoutput("<form action='runmodule.php?module=linkchecker&op=execute' method='post'>");
		addnav("", "runmodule.php?module=linkchecker&op=execute");
		rawoutput("<input type='submit' value='". translate_inline("Execute") ."' class='button'>");
		output("`i`b`$ Attention`0`b`i");
		rawoutput("</form>");
	break;
	}	
	page_footer();
}


?>