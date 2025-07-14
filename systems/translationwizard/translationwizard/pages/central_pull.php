<?php
declare(strict_types=1);
require_once("lib/pullurl.php");
$masterpath=get_module_setting('lookuppath')."/";
//$masterpath="http://pull.strahd.de/homes/root/";
$selecteddir=httppost('pulldir');
if ($selecteddir=='') $selecteddir=(string)httpget('pulldir');
$selecteddir=trim($selecteddir);
$mymirror=httppost('mirror');
if ($mymirror=='') $mymirror=(string)httpget('mirror');
if (!$selecteddir) $selecteddir=$languageschema;
$datum = getdate(time());
$currentdate=$datum['mon']."-".$datum['mday']."-".$datum['year'];
$path=$masterpath;
$lookuppath=$masterpath;
//$path="http://pull.strahd.de/homes/root/";
if ($mymirror!='') $path=$mymirror."/";
$chosenpath=$path.$selecteddir."/";
//$lookuppath="http://pull.strahd.de";
if ($mymirror!='') $lookuppath=$mymirror;
if (httppost('pullchecked')||httppost('pulluntranslated')) $mode="pull";
switch($mode) {

	case "pull":
		if (!$selecteddir) {
			output("Sorry, please select a language!");
			break;
		}
		$pullmodules_temp = httppost("moduletext");
		if (is_array($pullmodules_temp)){ 
			//setting for any intexts you might receive
			$pullmodules = $pullmodules_temp;
		}else {
			if ($pullmodules_temp) $pullmodules = array($pullmodules_temp);
			else $pullmodules = array();
		}
		if ($namespace<>0 || $pullmodules[0]=="") $pullmodules[0]=$namespace;
		//debug ($pullmodules);
		output("URL: %s`n",$path);
		output("Pulled from folder: %s",$selecteddir);
		output_notl("`n`n");
		if (httppost("pulluntranslated")) {
			$pullmodules=array();
			$sql="SELECT namespace from ".db_prefix("untranslated")." group by namespace";
			$result=db_query($sql);
			while ($row=db_fetch_assoc($result))	{
				array_push($pullmodules,$row['namespace']);
			}
		}
		foreach ($pullmodules as $module) {
			$sql="";
			$file=pullurl($chosenpath.$module.".sql");
			if (!is_array($file) || $file==array()) continue;
			//debug($file);
			if (strstr($file[0],"Verified")) {
				$moduledate=substr($file[0],0,10);
				$file[0]="INSERT INTO ".db_prefix("temp_translations")." (`language`, `uri`, `intext`, `outtext`, `author`, `version`) VALUES ";
//				array_shift($file);
				$sql.=implode("",$file);
				$result=db_query($sql); //debug("Success:".$result);
			} else {
				output("The pull for namespace '%s' was `$ not`0 successful (error getting file / wrong file).",$module);
			}
			if ($result) {
				output("The pull for namespace '%s' was successful.",$module);
				set_module_objpref("namespaces", 0, $module, $currentdate ,"translationwizard");
				set_module_objpref("namespaces", 1, $module, $moduledate ,"translationwizard");
				set_module_objpref("namespaces", 2, $module, "" ,"translationwizard");
			} else {
				output("The pull for namespace '%s' was `$ not`0 successful (could not transfer into table).",$module);
			}
			output_notl("`n");
			$result=0;
		}

		break;
	default:
		//debug("Posted:".httppost('pulldir')." Selected:".$selecteddir);
		$linklist=pullurl($path."files.txt"); debug($path."files.txt");
		$mirrors=pullurl($masterpath."mirrors.txt");
		if (is_array($mirrors)) sort ($mirrors);
                output("Choose a mirror if you don't want to use the normal central DB:");
                output_notl("`n");debug($mymirror);
                output("Select a mirror and directory to pull from:");
                tw_form_open('pull');
		addnav("", "runmodule.php?module=translationwizard&op=pull");	
		rawoutput("<select name='mirror' onchange='this.form.submit()'>");
		rawoutput("<option value=''>---</option>");
		foreach ($mirrors as $mirror) {
			$mirror=explode("|",$mirror);
			rawoutput("<option value=\"".htmlentities($mirror[1],ENT_COMPAT,$coding)."\"".((htmlentities(str_replace(chr(10),"",$mirror[1]),ENT_COMPAT,$coding) == $mymirror) ? "selected" : "").">".htmlentities($mirror[0]."(".$mirror[1].")",ENT_COMPAT,$coding)."</option>");
			}
		rawoutput("</select>");
		output_notl("`n");
		if ($mymirror!='') {
			output("`nCurrently you are using the mirror `\$%s`0.`n`n",$mymirror);
		}
		if (is_array($linklist)) sort ($linklist); 
		output("Choose the directory (normally equals language) you want to pull from:");
		output_notl("`n");
		rawoutput("<select name='pulldir' onchange='this.form.submit()'>");
		rawoutput("<option value=''>---</option>");
		foreach ($linklist as $link) {
			rawoutput("<option value=\"".htmlentities($link,ENT_COMPAT,$coding)."\"".((sanitize(trim($link)) == trim($selecteddir)) ? "selected" : "").">".htmlentities($link,ENT_COMPAT,$coding)."</option>");
			}
		rawoutput("</select>");
		output_notl("`n");
		if (httpget('getdate')) {
			$file=pullurl($chosenpath.$namespace.".sql");
			if (strstr($file[0],"Verified")) {
				set_module_objpref("namespaces", 2, $namespace, substr($file[0],0,10) ,"translationwizard");
			} else{
				output_notl("`b");
				output("For namespace '%s' the pull was `$ not`0 successful (missing file)",$namespace);
				output_notl("`b`n`n");
			}
		}
		$sql="SELECT modulename FROM ".db_prefix("modules").";";
		$result=db_query($sql);
		$listing=array("about","armor","badnav","badword","bank","battle","bio","buffs","claneditor","clans","commentary","common","configuration","create","creatures","donation","dragon","events","faq","fightnav","forest","gardens","graveyard","gypsy","healer","hof","home","inn","installer","lib-commentary","lib-pageparts","list","lodge","logdnet","login","mail","masters","moderate","modulemanage","motd","mountname","mounts","nav","newday","paylog","petition","prefs","pvp","rawsql","referers","referral","retitle","rock","shades","showform","skill","skills","source","stables","stats","superuser","taunt","train","translatortool","untranslated","user","village","weapon");
		while($row=db_fetch_assoc($result)) {
			array_push($listing,"module-".$row['modulename']);
		}
		asort($listing);
		$missing=array();
		if (httppost('datecheck')) {
			$listing2=$listing;
			foreach ($listing2 as $val) {
				$file=pullurl($chosenpath.$val.".sql");
				if (strstr($file[0],"Verified")) {
					set_module_objpref("namespaces", 2, $val, substr($file[0],0,10) ,"translationwizard");
				} else {
					$missing[]=$val;
				}
			}
		}
		if ($missing!==array()) {
			output_notl("`b");
			output("The following namespace have had no match on the server (no file):`n`n`\$%s`0",implode("`n",$missing));
			output_notl("`b`n`n");
			
		}
		output("If you want to see what files we have currently on the server, visit `$ %s`0",$lookuppath);
		output_notl("`n");
		rawoutput("<input type='submit' name='datecheck' value='". translate_inline("Pull all dates") ."' class='button'>");

		output("If you pull only for those in the untranslated, you don't need to check anything.");
		output_notl("`n");
		output("Moduledate refers to the date in the file you pulled. That means when it was changed last as you pulled it.");
		output_notl(" ");
		output("`bDon't`b select too much. It may cause a `$ timeout`0...");
		output_notl("`n");
		output("Following namespace were found on your LotGD:`n");
		rawoutput("<input type='submit' name='pulluntranslated' value='". translate_inline("Pull only for those in the untranslated") ."' class='button'>");
		rawoutput("<input type='submit' name='pullchecked' value='". translate_inline("Pull checked") ."' class='button'>");
                tw_table_open([
                    '',
                    translate_inline("Namespace"),
                    translate_inline("Moduledate"),
                    translate_inline("Last Pulled"),
                    translate_inline("Server Date"),
                    translate_inline("Actions"),
                    '',
                    ''
                ]);
		$i=0;
		$rmymirror=rawurlencode($mymirror);
		$rselecteddir=rawurlencode($selecteddir);		
		foreach ($listing as $val) {
			$i=!$i;
			$rval=rawurlencode($val);
                        $checkbox = "<input type='checkbox' name='moduletext[]' value='$rval' >";
                        $pull = "<a href='runmodule.php?module=translationwizard&op=pull&mode=pull&pulldir=$rselecteddir&mirror=$rmymirror&ns=$rval'>" . translate_inline("Pull") . "</a>";
                        addnav('', "runmodule.php?module=translationwizard&op=pull&mode=pull&pulldir=".$rselecteddir."&mirror=".$rmymirror."&ns=".$rval);
                        $push = "<a href='runmodule.php?module=translationwizard&op=push&mode=push&ns=$rval'>" . translate_inline("Push") . "</a>";
                        addnav('', "runmodule.php?module=translationwizard&op=push&mode=push&ns=".$rval);
                        $date = "<a href='runmodule.php?module=translationwizard&op=pull&pulldir=$rselecteddir&mirror=$rmymirror&getdate=1&ns=$rval'>" . translate_inline("Pull Date") . "</a>";
                        addnav('', "runmodule.php?module=translationwizard&op=pull&pulldir=".$rselecteddir."&mirror=".$rmymirror."&getdate=1&ns=".$rval);
                        tw_table_row([
                            $checkbox,
                            $val,
                            get_module_objpref("namespaces", 1, $val, "translationwizard"),
                            get_module_objpref("namespaces", 0, $val, "translationwizard"),
                            get_module_objpref("namespaces", 2, $val, "translationwizard"),
                            $pull,
                            $push,
                            $date
                        ], $i%2==1);
                }
                tw_table_close();
		rawoutput("<input type='submit' name='pulluntranslated' value='". translate_inline("Pull only for those in the untranslated") ."' class='button'>");
		rawoutput("<input type='submit' name='pullchecked' value='". translate_inline("Pull checked") ."' class='button'>");
                tw_form_close();
}
?>
