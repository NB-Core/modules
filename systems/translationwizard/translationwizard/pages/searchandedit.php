<?php
declare(strict_types=1);
//debug($session['user']['specialmisc']);
$p="";
$params=array(); //bound values for the search query (request values are stored slashed, so they are unslashed here)
switch ($mode)
{
	case "select":
		if (!$session['user']['specialmisc'])
		{
			$sql="Select * from ".db_prefix("translations")." WHERE";
			$sql2=$sql;
			if (!httppost('exactly')) $p="%";
			if (httppost('tid')) { $sql.=" tid LIKE :tid AND"; $params['tid']=$p.stripslashes(httppost('tid')).$p; }
			if (httppost('language')) { $sql.=" language LIKE :language AND"; $params['language']=$p.stripslashes(httppost('language')).$p; }
			if (httppost('uri')) { $sql.=" uri LIKE :uri AND"; $params['uri']=$p.stripslashes(httppost('uri')).$p; }
			if (httppost('intext')) { $sql.=" intext LIKE :intext AND"; $params['intext']=$p.stripslashes(httppost('intext')).$p; }
			if (httppost('outtext')) { $sql.=" outtext LIKE :outtext AND"; $params['outtext']=$p.stripslashes(httppost('outtext')).$p; }
			if (httppost('author')) { $sql.=" author LIKE :author AND"; $params['author']=$p.stripslashes(httppost('author')).$p; }
			if (httppost('version')) { $sql.=" version LIKE :version AND"; $params['version']=$p.stripslashes(httppost('version')).$p; }
			if ($sql==$sql2) redirect('runmodule.php?module=translationwizard&op=searchandedit&error=1'); //back to the roots if nothing was entered
			$session['user']['specialmisc']=serialize(array("number"=>httppost('number'),"orderbyascdesc"=>httppost('orderbyascdesc'),"orderby"=>httppost('orderby'),"exactly"=>httppost('exactly'),"tid"=>httppost('tid'),"language"=>httppost('language'),"uri"=>httppost('uri'),"intext"=>httppost('intext'),"outtext"=>httppost('outtext'),"author"=>httppost('author'),"version"=>httppost('version'))); //serialize to let the user return to his results and continue
			$orderbyascdesc=(!httppost('orderbyascdesc')?"ASC":"DESC");
			$forswitch=httppost('orderby');
			$numberof=(int)httppost('number');
		} else {
			$query=WizardService::normalizeSearchQuery(unserialize($session['user']['specialmisc']));
			$sql="Select * from ".db_prefix("translations")." WHERE";
			$sql2=$sql;
			if (!$query['exactly']) $p="%";
			if ($query['tid']) { $sql.=" tid LIKE :tid AND"; $params['tid']=$p.stripslashes($query['tid']).$p; }
			if ($query['language']) { $sql.=" language LIKE :language AND"; $params['language']=$p.stripslashes($query['language']).$p; }
			if ($query['uri']) { $sql.=" uri LIKE :uri AND"; $params['uri']=$p.stripslashes($query['uri']).$p; }
			if ($query['intext']) { $sql.=" intext LIKE :intext AND"; $params['intext']=$p.stripslashes($query['intext']).$p; }
			if ($query['outtext']) { $sql.=" outtext LIKE :outtext AND"; $params['outtext']=$p.stripslashes($query['outtext']).$p; }
			if ($query['author']) { $sql.=" author LIKE :author AND"; $params['author']=$p.stripslashes($query['author']).$p; }
			if ($query['version']) { $sql.=" version LIKE :version AND"; $params['version']=$p.stripslashes($query['version']).$p; }
			$orderbyascdesc=(!$query['orderbyascdesc']?"ASC":"DESC");
			$forswitch=$query['orderby'];
			$numberof=(int)$query['number'];
		}
		$sql=substr($sql,0,strlen($sql)-3);
		$presql=$sql;
		switch ($forswitch)
		{
			case 0:
				$sql.="ORDER BY tid $orderbyascdesc LIMIT ";
				break;
			case 1:
				$sql.="ORDER BY language $orderbyascdesc LIMIT ";
				break;		
			case 2:
				$sql.="ORDER BY uri $orderbyascdesc LIMIT ";
				break;
			case 3:
				$sql.="ORDER BY intext $orderbyascdesc LIMIT ";
				break;
			case 4:
				$sql.="ORDER BY outtext $orderbyascdesc LIMIT ";
				break;		
			case 5:
				$sql.="ORDER BY author $orderbyascdesc LIMIT ";
				break;
			case 6:
				$sql.="ORDER BY version $orderbyascdesc LIMIT ";
				break;
			default:
				$sql.="ORDER BY intext LIMIT ";
		}
		$start=httpget('pageop');
		if (!$start) $start=0;
		$conn=\Lotgd\MySQL\Database::getDoctrineConnection();
		// Only the number of matches is needed here.
		$numberofallrows=(int)$conn->fetchOne(preg_replace('/^Select \* from/i','SELECT COUNT(*) FROM',$presql).";",$params);
		$sql.=":startrow,:rowlimit;";
		$params['startrow']=(int)$start;
		$params['rowlimit']=(int)round((float)$numberof);
		//debug($sql);break;
		$result=$conn->fetchAllAssociative($sql,$params,array('startrow'=>\Doctrine\DBAL\ParameterType::INTEGER,'rowlimit'=>\Doctrine\DBAL\ParameterType::INTEGER));
		$rownumber=count($result);
		if ($rownumber==0) redirect('runmodule.php?module=translationwizard&op=searchandedit&error=2'); //back to the roots if nothing was found
                output("Select translations to delete:");
                tw_form_open('searchandedit&mode=delete');
                addnav("", "runmodule.php?module=translationwizard&op=searchandedit&mode=delete");
		output("%s rows have been found (Displaylimit was %s).",$numberofallrows,$numberof);
		output_notl("`n");
		output("Now viewing rows %s to %s",$start,$start+(min($numberof,$numberofallrows-$start)));
		output_notl("`n`n");
		rawoutput("<h4 align='left'>");
		if ($start>=$numberof) {
			rawoutput("<a href='runmodule.php?module=translationwizard&op=searchandedit&mode=select&pageop=".($start-$numberof)."'>". translate_inline("Previous Page")."</a>");
			addnav("", "runmodule.php?module=translationwizard&op=searchandedit&mode=select&pageop=".($start-$numberof)."");
		}
		if ($rownumber==$numberof) {
			rawoutput("<a href='runmodule.php?module=translationwizard&op=searchandedit&mode=select&pageop=".($numberof+$start)."'>". translate_inline("Next Page")."</a>");
			addnav("", "runmodule.php?module=translationwizard&op=searchandedit&mode=select&pageop=".($numberof+$start)."");
		}
		rawoutput("</h4>");
                tw_table_open([
                    '',
                    translate_inline('Tid'),
                    translate_inline('Language'),
                    translate_inline('Namespace'),
                    translate_inline('Intext'),
                    translate_inline('Outtext'),
                    translate_inline('Author'),
                    translate_inline('Version'),
                    translate_inline('Actions'),
                    ''
                ]);
                $i=0;
                foreach($result as $row)
                {
                        $i++;
                        $checkbox = "<input type='checkbox' name='deletetext[]' value='{$row['tid']}' >";
                        $edit = "<a href='runmodule.php?module=translationwizard&op=searchandedit&mode=edit&tid={$row['tid']}'>" . translate_inline('Edit') . "</a>";
                        addnav('', "runmodule.php?module=translationwizard&op=searchandedit&mode=edit&tid={$row['tid']}");
                        $del = "<a href='runmodule.php?module=translationwizard&op=searchandedit&mode=delete&tid={$row['tid']}'>" . translate_inline('Delete') . "</a>";
                        addnav('', "runmodule.php?module=translationwizard&op=searchandedit&mode=delete&tid={$row['tid']}");
                        tw_table_row([
                            $checkbox,
                            $row['tid'],
                            $row['language'],
                            $row['uri'],
                            htmlentities(stripslashes($row['intext']), ENT_COMPAT, $coding),
                            htmlentities(stripslashes($row['outtext']), ENT_COMPAT, $coding),
                            sanitize($row['author']),
                            $row['version'],
                            $edit,
                            $del
                        ], $i%2==1);
                }
                tw_table_close();
		//some check/uncheck all
		$all=translate_inline("Check all");
		$none=translate_inline("Uncheck all");
		rawoutput("<script type='text/javascript' language='JavaScript'>
				<!-- Begin
				var checkflag = 'false';
				cb = document.forms['editfeld'].elements['deletetext[]'];
				function check() {
				if (checkflag == 'false') {
				for (i = 0; i < cb.length; i++) {
				cb[i].checked=true;}
				checkflag = 'true';
				return ' $none '; }
				else {
				for (i = 0; i < cb.length; i++) {
				cb[i].checked=false; }
				checkflag = 'false';
				return ' $all '; }
				}
				//  End -->
				</script>");
				//end		
				if (count($result)>1) rawoutput("<input type='button' onClick='this.value=check()' name='allcheck' value='". $all ."' class='button'>");
				rawoutput("<input type='submit' name='deletechecked' value='". translate_inline("Delete selected") ."' class='button'>");
                                tw_form_close();
				break;

	case "edit":
				if (!httpget('tid')) redirect('runmodule.php?module=translationwizard&op=searchandedit&error=3'); //back to the roots 
				$result=\Lotgd\MySQL\Database::getDoctrineConnection()->fetchAllAssociative("SELECT * FROM ".\Lotgd\MySQL\Database::prefix("translations")." WHERE tid = ?;",array((int)httpget('tid')));
				if (count($result)>1) redirect('runmodule.php?module=translationwizard&op=searchandedit&error=3'); //back to the roots 
				$row=$result[0] ?? false;
				if ($row===false) redirect('runmodule.php?module=translationwizard&op=searchandedit&error=3'); //back to the roots, no such row
				output("Please edit the following row. If you hit save, all values will be saved.");
				output_notl(" ");
				output("If you want to abort, just click abort (or any other navigation except 'save'.");
				output_notl("`n`n");
                                output("Edit the selected translation:");
                                tw_form_open("searchandedit&mode=save");
				addnav("", "runmodule.php?module=translationwizard&op=searchandedit&mode=save");
				output("TID of the row:");
				rawoutput("<input id='input' name='tid' width=5 maxlength=5 value='".$row['tid']."'>");
				output_notl("`n`n");
				output("Language of the row:");
				rawoutput("<input id='input' name='language' width=2 maxlength=2 value='".$row['language']."'>");
				output_notl("`n`n");
				output("Namespace of the row:");
				rawoutput("<input id='input' name='uri' width=65 maxlength=255 value='".$row['uri']."'>");
				output_notl("`n`n");
				output("Intext of the row:");
				output_notl("`n");
                                rawoutput("<textarea name='intext' class='input' cols='60' rows='5' title=\"".translate_inline('Original text')."\">".htmlentities(stripslashes($row['intext']),ENT_COMPAT,$coding)."</textarea>");
				output_notl("`n`n");
				output("Outtext of the row:");
				output_notl("`n");
                                rawoutput("<textarea name='outtext' class='input' cols='60' rows='5' title=\"".translate_inline('Enter your translation')."\">".htmlentities(stripslashes($row['outtext']),ENT_COMPAT,$coding)."</textarea>");
				output_notl("`n`n");	
				output("Author of the row:");
				rawoutput("<input id='input' name='author' width=50 maxlength=50 value='".$row['author']."'>");
				output_notl("`n`n");
				output("Version of the row:");
				rawoutput("<input id='input' name='version' width=50 maxlength=50 value='".$row['version']."'>");
				output_notl("`n`n");
				rawoutput("<input type='submit' name='select' value='". translate_inline("Save")."' class='button'>");
				output("`b`$ ATTENTION`b`0");
				rawoutput("<input type='submit' name='abort' value='". translate_inline("Abort")."' class='button'>");
                                tw_form_close();
				break;

	case "save":
				if (httppost('abort')) redirect('runmodule.php?module=translationwizard&op=searchandedit'); //back to the roots
				$sql="UPDATE ".\Lotgd\MySQL\Database::prefix("translations")." set language=:language, uri=:uri, intext=:intext, outtext=:outtext, author=:author, version=:version WHERE tid=:tid;";
				try {
					\Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement($sql,array(
						'language'=>stripslashes(httppost('language')),
						'uri'=>stripslashes(httppost('uri')),
						'intext'=>stripslashes(httppost('intext')),
						'outtext'=>stripslashes(httppost('outtext')),
						'author'=>stripslashes(httppost('author')),
						'version'=>stripslashes(httppost('version')),
						'tid'=>(int)httppost('tid'),
					));
					$result=true;
				} catch (\Doctrine\DBAL\Exception $e) {
					$result=false;
				}
				debug($sql);
                                if (!$result) redirect('runmodule.php?module=translationwizard&op=searchandedit&error=4&mode=select'); //back to the roots
				redirect('runmodule.php?module=translationwizard&op=searchandedit&error=5&mode=select'); //back to the roots, no error but success

				break;

	case "delete":
				$delrows = httppost("deletetext");
				$earlyexit=false;
				if (is_array($delrows))  //setting for any intexts you might receive
				{
					$deleterows = $delrows;
				}else
				{
					if ($delrows) $deleterows = array($delrows);
					else 
					{
						$deleterows = array();
					}
				}
				foreach($deleterows as $val) {
					$earlyexit=true;		
					$sql = "DELETE FROM " . \Lotgd\MySQL\Database::prefix("translations") . " WHERE tid = ?;";
					try {
						\Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement($sql,array((int)$val));
					} catch (\Doctrine\DBAL\Exception $e) {
						// original db_query result was ignored here as well
					}
					//debug($sql);
				}
				//debug($sql);debug("Early:".$earlyexit);debug("tidget:".httpget('tid'));
				if ($earlyexit) redirect('runmodule.php?module=translationwizard&op=searchandedit&error=7&mode=select'); //back to the roots, no error but success
				if (!httpget('tid')) redirect('runmodule.php?module=translationwizard&op=searchandedit&error=6&mode=select'); //back to the roots 
				try {
					\Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement("DELETE FROM ".\Lotgd\MySQL\Database::prefix("translations")." WHERE tid = ?;",array((int)httpget('tid')));
					$result=true;
				} catch (\Doctrine\DBAL\Exception $e) {
					$result=false;
				}
				if (!$result) redirect('runmodule.php?module=translationwizard&op=searchandedit&error=6&mode=select'); //back to the roots 
				redirect('runmodule.php?module=translationwizard&op=searchandedit&error=7&mode=select'); //back to the roots, no error but success
				break;

	default:

				$query=WizardService::normalizeSearchQuery(unserialize($session['user']['specialmisc']));
				$session['user']['specialmisc']='';


				$orderby=array(translate_inline("Tid"),translate_inline("Language"),translate_inline("Namespace"),translate_inline("Intext"),translate_inline("Outtext"),translate_inline("Author"),translate_inline("Version"));
				output("This lets you search your translations table for a single row you want to edit or delete.");
				output_notl("`n");
				output("Usually you do this to correct a wrong translation your users encountered.");
				output_notl("`n");
				output("For those proficient with sql: The statement automatically has % at the end and the beginning of the word.");
				output_notl(" ");
				output("If you don't want that, just hit the checkbox below. You may use ?,% or the like in the text."); 
				output_notl("`n`n");
                                output("Enter part of the translation to search for:");
                                tw_form_open("searchandedit&mode=select");
				addnav("", "runmodule.php?module=translationwizard&op=searchandedit&mode=select");
				output("What do you want to search for (select enter one or more criteria):");
				output_notl("`n`n");
				output("Maximum number of results:");
				rawoutput("<input id='input' name='number' width=3 maxlength=3 value='".($query['number']?$query['number']:'30')."'>");
				output_notl("`n`n");
				output("Order results by:");
				rawoutput("<select name='orderby'>");
				foreach($orderby as $key=>$val) {
					rawoutput("<option value=\"".$key."\"".((int)$key === $query['orderby']?" selected" : "").">".$val."</option>");
				}
				rawoutput("</select>");
				rawoutput("<select name='orderbyascdesc'>");
				rawoutput("<option value=\"0\" ".(!$query['orderbyascdesc']?"selected":"").">".translate_inline("Ascending")."</option>");
				rawoutput("<option value=\"1\" ".($query['orderbyascdesc']?"selected":"").">".translate_inline("Descending")."</option>");
				rawoutput("</select>");
				output_notl("`n`n");
				output("Search exactly: ");
				rawoutput("<input type='checkbox' name='exactly' ".($query['exactly']?"checked":"").">");	
				output_notl("`n`n");
				output("TID of the row:");
				rawoutput("<input id='input' name='tid' width=5 maxlength=5 value='".$query['tid']."'>");
				output_notl("`n`n");
				output("Language of the row:");
				rawoutput("<input id='input' name='language' width=2 maxlength=2 value='".$query['language']."'>");
				output_notl("`n`n");
				output("Namespace of the row:");
				rawoutput("<input id='input' name='uri' width=65 maxlength=255 value='".$query['uri']."'>");
				output_notl("`n`n");
				output("Intext of the row:");
				output_notl("`n");
                                rawoutput("<textarea name='intext' class='input' cols='60' rows='5' title=\"".translate_inline('Original text')."\">".$query['intext']."</textarea>");
				output_notl("`n`n");
				output("Outtext of the row:");
				output_notl("`n");
                                rawoutput("<textarea name='outtext' class='input' cols='60' rows='5' title=\"".translate_inline('Enter your translation')."\">".$query['outtext']."</textarea>");
				output_notl("`n`n");	
				output("Author of the row:");
				rawoutput("<input id='input' name='author' width=50 maxlength=50 value='".$query['author']."'>");
				output_notl("`n`n");
				output("Version of the row:");
				rawoutput("<input id='input' name='version' width=50 maxlength=50 value='".$query['version']."'>");
				output_notl("`n`n");
                                rawoutput("<input type='submit' name='select' value='". translate_inline("Search")."' class='button'>");
                                tw_form_close();
}

