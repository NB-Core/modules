<?php
declare(strict_types=1);
$sql = "SELECT count(*) AS count FROM " . db_prefix("untranslated");
$count = db_fetch_assoc(db_query($sql));
if ($count['count'] > 0) {
	$sql = "SELECT * FROM " . db_prefix("untranslated") . " WHERE language = '" . $languageschema . "' ORDER BY rand(".e_rand().") LIMIT 1";
	$result = db_query($sql);
	if (db_num_rows($result) == 1) {
		$row = db_fetch_assoc($result);
		$row['intext'] = stripslashes($row['intext']);
                $submit = translate_inline("Save Translation");
                $skip = translate_inline("Skip Translation");
                output("Translate the text below:");
                tw_form_open('randomsave', [
                    'id'        => $row['id'],
                    'language'  => $row['language'],
                    'namespace' => $row['namespace'],
                ]);
		output("`^`cThere are `&%s`^ untranslated texts in the database.`c`n`n", $count['count']);
		rawoutput("<table width='80%'>");
		rawoutput("<tr><td width='30%'>");
		output("Target Language: %s", $row['language']);
		rawoutput("</td><td></td></tr>");
		rawoutput("<tr><td width='30%'>");
		output("Namespace: %s", $row['namespace']);
		rawoutput("</td><td></td></tr>");
                rawoutput("<tr><td width='30%'><textarea cols='35' rows='4' name='intext' readonly title=\"".translate_inline('Original text')."\">".$row['intext']."</textarea></td>");
                rawoutput("<td width='30%'><textarea cols='25' rows='4' name='outtext' title=\"".translate_inline('Enter your translation')."\"></textarea></td></tr></table>");
                tw_form_close($submit);
                tw_form_open('');
                tw_form_close($skip);
		addnav("", "runmodule.php?module=translationwizard&op=randomsave");
		addnav("", "runmodule.php?module=translationwizard");
	} else {
		output("There are `&%s`0 untranslated texts in the database, but none for your selected language.", $count['count']);
		output("Please change your language to translate these texts.");
	}
} else
	{
	output("There are no untranslated texts in the database!");
	output("Congratulations!!!");
	} // end if
?>
