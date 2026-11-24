<?php
declare(strict_types=1);
$datum = getdate(time());
$currentdate=$datum['mon']."-".$datum['mday']."-".$datum['year'];
$currenttime=$datum['hours'].":".$datum['minutes'].":".$datum['seconds'];
$selectedlanguage=httppost('pushlanguage');
if (!$selectedlanguage) $selectedlanguage=$languageschema;
$conn = \Lotgd\MySQL\Database::getDoctrineConnection();
if (httppost('pushall')||httppost('pushselected')) $mode="pushall"; //set in order to get the switch right... hard because no get reasonable
switch($mode)
{
case "push":
        if (httpget('pushlanguage')) $selectedlanguage=httpget('pushlanguage');
        $sql="SELECT intext,outtext,author,version FROM ".\Lotgd\MySQL\Database::prefix("translations")." where uri=:uri AND language=:language ORDER BY intext;";
	$result=$conn->fetchAllAssociative($sql, ['uri' => $namespace, 'language' => $selectedlanguage]);
	output("Please copy the following code to a file named `b`^%s.sql`0`b and give it to the admin for your language:",$namespace);
	output_notl("`n`n");
        $sql = "SELECT uri,count(*) AS c FROM " . \Lotgd\MySQL\Database::prefix("translations") . " WHERE language=:language GROUP BY uri ORDER BY uri ASC";
        $res=$conn->fetchAllAssociative($sql, ['language' => $selectedlanguage]);
        output("Select the namespace to push:");
        tw_form_open("push&mode=push");
	addnav("", "runmodule.php?module=translationwizard&op=push&mode=push");
	rawoutput("<input type='hidden' name='op' value='push'>");
	rawoutput("<input type='hidden' name='mode' value='push'>");
	rawoutput("<input type='hidden' name='pushlanguage' value='$selectedlanguage'>");	
	rawoutput("<select name='ns' onChange='this.form.submit()' >");
	foreach ($res as $row)
		{
		rawoutput("<option value=\"".htmlentities($row['uri'],ENT_COMPAT,$coding)."\"".((htmlentities($row['uri'],ENT_COMPAT,$coding) == $namespace) ? "selected" : "").">".htmlentities($row['uri'],ENT_COMPAT,$coding)." ({$row['c']})</option>");
		}
	rawoutput("</select>");
        tw_form_close();
	output_notl("`n`n");
	output_notl($currentdate." Verified "."Uploader:".$session['user']['login']." Time:".$currenttime);
	$start="('";
	$middle="','";
	$end="'),";
	$i=0;
	$numend=count($result);
	foreach($result as $row)
		{
		$i++;
		output_notl("`n");
		if ($i==$numend) $end="');";
		rawoutput($start.$selectedlanguage.$middle.$namespace.$middle.htmlentities(addslashes($row['intext']),ENT_COMPAT,$coding).$middle.htmlentities(addslashes($row['outtext']),ENT_COMPAT,$coding).$middle.$row['author'].$middle.$row['version'].$end);
		}
	break;

        case "pushall": //get a grip :D
                if (httppost('pushselected'))
                        {
                        $trans = httppost('pusharray');
			if (is_array($trans))  //setting for any intexts you might receive
				{
				$pusharray = $trans;
				}else
				{
					if ($trans) $pusharray = array($trans);
					else $pusharray = array();
                                }
                        }
                $sql = "SELECT uri,count(*) AS c FROM " . \Lotgd\MySQL\Database::prefix("translations") . " WHERE language=:language GROUP BY uri ORDER BY uri ASC";
                $res=$conn->fetchAllAssociative($sql, ['language' => $selectedlanguage]);
		$firstrow=$currentdate." Verified "."Uploader:".$session['user']['login']." Time:".$currenttime.chr(13).chr(10);
		$start="('";
		$middle="','";
		$dir=getsetting('datacachepath',0);
		output("Directory of your cache: %s",$dir);
		output_notl("`n");
		output("`bAll done... look at your datacache directoy :)`b");	
		if (!$dir) break;
		if (!$pusharray)
			{
			$pusharray=array();
			foreach ($res as $rows)
				{
				array_push($pusharray,$rows['uri']);
				}
			}
		foreach($pusharray as $key=>$rowspace)
			{
                        $end="'),".chr(13).chr(10);
                        // $rowspace: rawurlencoded POST value or DB value; bound raw
                        $sql="SELECT intext,outtext,author,version FROM ".\Lotgd\MySQL\Database::prefix("translations")." where uri=:uri AND language=:language ORDER BY intext;";
                        $result=$conn->fetchAllAssociative($sql, ['uri' => $rowspace, 'language' => $selectedlanguage]);
			$i=0;
			$numend=count($result);
			$filename=$dir."/".$rowspace.'.sql';
			$file=fopen($filename,'w-');
			fwrite($file,$firstrow);
			foreach($result as $row)
				{
				$i++;
				output_notl("`n");
				if ($i==$numend) $end="');";
				fwrite($file,$start.$selectedlanguage.$middle.$rowspace.$middle.addslashes($row['intext']).$middle.addslashes($row['outtext']).$middle.$row['author'].$middle.$row['version'].$end);
				}
				$end="";
				fclose($file);
			}
		break;
default:
        output("Choose the language you want to push:");
        output_notl("`n");
        output("Select language and namespaces to push:");
        tw_form_open('push');
	addnav("", "runmodule.php?module=translationwizard&op=push");	
	rawoutput("<select name='pushlanguage' onChange='this.form.submit()'>");
	$sql = "SELECT language,count(*) AS c FROM " . \Lotgd\MySQL\Database::prefix("translations") . " GROUP BY language ORDER BY language ASC";
	$result=$conn->fetchAllAssociative($sql);
	foreach ($result as $row)
		{
		rawoutput("<option value=\"".htmlentities($row['language'],ENT_COMPAT,$coding)."\"".((htmlentities($row['language'],ENT_COMPAT,$coding) == $selectedlanguage) ? "selected" : "").">".htmlentities($row['language'],ENT_COMPAT,$coding)." ({$row['c']}) </option>");
		}
	rawoutput("</select>");
	output_notl("`n");	
        $sql = "SELECT uri,count(*) AS c FROM " . \Lotgd\MySQL\Database::prefix("translations") . " WHERE language=:language GROUP BY uri ORDER BY uri ASC";
        $result=$conn->fetchAllAssociative($sql, ['language' => $selectedlanguage]);
	output("Following namespace were found on your LotGD:`n");
	output_notl("`n`n");
	rawoutput("<input type='submit' name='pushall' value='". translate_inline("Push entire translations") ."' class='button'>");
	rawoutput("<input type='submit' name='pushselected' value='". translate_inline("Push selected namespaces") ."' class='button'>");
	output_notl("`n");
	output("The buttons above dump your translations to sql files... only use this if you have a data caching dir specified!");
	output(" This takes some time...");
	output_notl("`n`n");
	output("Select the namespace from your translations you want to push.");
	output_notl("`n");
        tw_table_open([
            '',
            translate_inline("Namespace"),
            translate_inline("# of rows"),
            translate_inline("Actions"),
        ]);
        $i=0;
        foreach ($result as $row)
                {
		$i++;
                $checkbox = "<input type='checkbox' name='pusharray[]' value='".rawurlencode($row['uri'])."' >";
                $actions = "<a href='runmodule.php?module=translationwizard&op=push&mode=push&pushlanguage=$selectedlanguage&ns=". rawurlencode($row['uri'])."'>". translate_inline("Push") ."</a>";
                addnav("", "runmodule.php?module=translationwizard&op=push&mode=push&pushlanguage=$selectedlanguage&ns=". rawurlencode($row['uri']));
                tw_table_row([
                    $checkbox,
                    htmlspecialchars($row['uri'], ENT_QUOTES, 'UTF-8'),
                    $row['c'],
                    $actions,
                ], $i%2==1);
                }
        tw_table_close();
        tw_form_close();
}

