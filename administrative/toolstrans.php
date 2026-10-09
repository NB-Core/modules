<?php
function toolstrans_getmoduleinfo(){
	$info = array(
		"name"=>"Tools Translators",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Administrative",
		"download"=>"",
	);
	return $info;
}

function toolstrans_install(){
	module_addhook("superuser");
	return true;
}

function toolstrans_uninstall(){
	return true;
}

function toolstrans_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
	case "superuser":
		if (($session['user']['superuser'] & SU_MEGAUSER)== SU_MEGAUSER) {
			addnav("Mechanics");
			addnav("Translatorguys Performance","runmodule.php?module=toolstrans");
		}

	break;
	}
	return $args;
}

function toolstrans_run(){
	global $session;
	check_su_access(SU_MEGAUSER);
	$op=httpget('op');
	require_once("lib/superusernav.php");
	superusernav();
	page_header("Translators Overview");
	switch ($op) {
		default:
			switch (httpget('order')) {
				case 'rows':
					$order='counter DESC';
					break;
				case 'language':
					$order='a.language DESC';
					break;
				default:
					$order='a.author DESC';
			}
			//only current translators and only those who are still on the server
			switch (httpget('subop')) {
				case "sum":
					$sql="SELECT a.author as author, a.language as language, count(tid) as counter FROM ".db_prefix('translations')." AS a INNER JOIN ".db_prefix('accounts')." AS b ON a.author=b.login WHERE (b.superuser&".SU_IS_TRANSLATOR.") GROUP BY a.author ORDER BY $order";
					break;
				default:
					$sql="SELECT a.author as author, a.language as language, count(tid) as counter FROM ".db_prefix('translations')." AS a INNER JOIN ".db_prefix('accounts')." AS b ON a.author=b.login WHERE (b.superuser&".SU_IS_TRANSLATOR.") GROUP BY a.author,a.language ORDER BY $order";
			}
			addnav("Actions");
			addnav("Refresh","runmodule.php?module=toolstrans&order=".httpget('order'));
			addnav("Order By Counter","runmodule.php?module=toolstrans&order=rows");
			addnav("Order By Language","runmodule.php?module=toolstrans&order=language");
			addnav("Order By Author","runmodule.php?module=toolstrans&order=author");
			addnav("Sums");
			addnav("Authors with total rows","runmodule.php?module=toolstrans&subop=sum&order=rows");
			$result = db_query ($sql);
			$translator=translate_inline("Translator");
			$language=translate_inline("Language");
			$rows=translate_inline("Rows");
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' align=center>");
			rawoutput("<tr class='trhead' height=30px><td><b>$translator</b></td><td><b>$language</b></td><td><b>$rows</b></td></tr>");
			$class="trlight";
			output("`4Stats:`n`n");
			while ($row=db_fetch_assoc($result)) {
				$class=($class=='trlight'?'trdark':'trlight');
				rawoutput("<tr height=30px class='$class'>");
				rawoutput("<td>");
				output_notl($row['author']);
				rawoutput("</td><td>");
				output_notl($row['language']);
				rawoutput("</td><td>");
				output_notl($row['counter']);
				rawoutput("</td></tr>");

			}
			rawoutput("</table>");
/*			require_once("lib/commentary.php");
			addcommentary();	
			commentdisplay("`n`n`@Translation Status Discussions`n","TransDiscussions","",10,"translates");
			break;
*/
	}
	page_footer();
}


?>
