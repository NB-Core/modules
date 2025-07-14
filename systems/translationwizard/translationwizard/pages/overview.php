<?php
declare(strict_types=1);
output("Overview:");
output_notl("`n`n");
$sql= "SELECT count(*) AS counter, uri,language FROM ".db_prefix("translations")." GROUP BY language,uri";
$result = db_query($sql);
tw_table_open([
    translate_inline('Language'),
    translate_inline('Namespace'),
    translate_inline('# of rows')
]);
output("`bYour translations table has the following structure:`b");
output_notl("`n`n");
	if (db_num_rows($result)>0) 
		{
		$i=0;
		while($row=db_fetch_assoc($result))
			{
			$i++;
                        tw_table_row([
                            htmlentities($row['language'], ENT_COMPAT, $coding),
                            htmlentities($row['uri'], ENT_COMPAT, $coding),
                            htmlentities($row['counter'], ENT_COMPAT, $coding)
                        ], $i%2==1);
			}
		}
                tw_table_close();
output_notl("`n`n");
$sql= "SELECT count(  tid  )  AS counter, language FROM ".db_prefix("translations")." GROUP BY language HAVING counter >1;";
$result = db_query($sql);
tw_table_open([
    translate_inline('Language'),
    translate_inline('# of rows')
]);
	if (db_num_rows($result)>0) 
		{
		while($row=db_fetch_assoc($result))
			{
			$i++;
                        tw_table_row([
                            htmlentities($row['language'], ENT_COMPAT, $coding),
                            htmlentities($row['counter'], ENT_COMPAT, $coding)
                        ], $i%2==1);
			}
		}
                tw_table_close();
output_notl("`n`n");
output("`bYour untranslated table has the following structure:`b");
output_notl("`n`n");
$sql= "SELECT count(  intext  )  AS counter, namespace,language FROM ".db_prefix("untranslated")." GROUP BY language,namespace;";
$result = db_query($sql);
tw_table_open([
    translate_inline('Language'),
    translate_inline('Namespace'),
    translate_inline('# of rows')
]);
	if (db_num_rows($result)>0) 
		{
		while($row=db_fetch_assoc($result))
			{
			$i++;
                    tw_table_row([
                        htmlentities($row['language'], ENT_COMPAT, $coding),
                        htmlentities($row['namespace'], ENT_COMPAT, $coding),
                        htmlentities($row['counter'], ENT_COMPAT, $coding)
                    ], $i%2==1);
			}
		}
            tw_table_close();
output_notl("`n`n");
$sql= "SELECT count(  intext  )  AS counter, language FROM ".db_prefix("untranslated")." GROUP BY language;";
$result = db_query($sql);
tw_table_open([
    translate_inline('Language'),
    translate_inline('# of rows')
]);
	if (db_num_rows($result)>0) 
		{
		while($row=db_fetch_assoc($result))
			{
			$i++;
                        tw_table_row([
                            htmlentities($row['language'], ENT_COMPAT, $coding),
                            htmlentities($row['counter'], ENT_COMPAT, $coding)
                        ], $i%2==1);
			}
		}
                tw_table_close();
output_notl("`n`n");
output("`bYour pulled translations table has the following structure:`b");
output_notl("`n`n");
$sql= "SELECT count(  *  )  AS counter, uri,language FROM ".db_prefix("temp_translations")." GROUP BY language,uri;";
$result = db_query($sql);
tw_table_open([
    translate_inline('Language'),
    translate_inline('Namespace'),
    translate_inline('# of rows')
]);
	if (db_num_rows($result)>0) 
		{
		while($row=db_fetch_assoc($result))
			{
			$i++;
                        tw_table_row([
                            htmlentities($row['language'], ENT_COMPAT, $coding),
                            htmlentities($row['uri'], ENT_COMPAT, $coding),
                            htmlentities($row['counter'], ENT_COMPAT, $coding)
                        ], $i%2==1);
			}
		}
                tw_table_close();
output_notl("`n`n");
$sql= "SELECT count(  intext  )  AS counter, language FROM ".db_prefix("temp_translations")." GROUP BY language;";
$result = db_query($sql);
tw_table_open([
    translate_inline('Language'),
    translate_inline('# of rows')
]);
	if (db_num_rows($result)>0) 
		{
		while($row=db_fetch_assoc($result))
			{
			$i++;
                        tw_table_row([
                            htmlentities($row['language'], ENT_COMPAT, $coding),
                            htmlentities($row['counter'], ENT_COMPAT, $coding)
                        ], $i%2==1);
			}
		}
                tw_table_close();

