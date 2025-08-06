<?php
declare(strict_types=1);
output("`^Welcome to the Translation Wizard.`0");
output_notl("`n");
output("This tool stores untranslated lines in a separate table and keeps your finished translations in the main translations table. Use the links on the left to maintain these lists and fetch data from the central repository.");
output_notl("`n`n");
output("`bNavigation overview:`b");
output_notl("`n");
output("`bOverview:`b summary of your translation tables.");
output_notl("`n");
output("`bRestart Translator:`b work on random untranslated strings.");
output_notl("`n");
output("`bTranslate by Namespace:`b translate texts from a specific module.");
output_notl("`n");
output("`bFix already translated:`b remove lines already translated.");
output_notl("`n");
output("`bCheck for duplicate entries:`b find duplicate rows in your translations.");
output_notl("`n");
output("`bCheck for known translations:`b see if you already translated the text elsewhere.");
output_notl("`n`n");

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
                tw_table_open();
                tw_table_row([
                    sprintf("%s", sprintf(translate_inline('Target Language: %s'), $row['language'])),
                    ''
                ], true);
                tw_table_row([
                    sprintf("%s", sprintf(translate_inline('Namespace: %s'), $row['namespace'])),
                    ''
                ], false);
                tw_table_row([
                    "<textarea cols='35' rows='4' name='intext' readonly title=\"" . translate_inline('Original text') . "\">" . $row['intext'] . "</textarea>",
                    "<textarea cols='25' rows='4' name='outtext' title=\"" . translate_inline('Enter your translation') . "\"></textarea>"
                ], true);
                tw_table_close();
                tw_form_close($submit);
                output("<button type='button' onclick=\"window.location.href='runmodule.php?module=translationwizard';\">$skip</button>");
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

