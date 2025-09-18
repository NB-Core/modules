<?php
// addnews ready
// mail ready

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;
function mightyblogs_getmoduleinfo(){
	$info = array(
		"name"=>"MightyE Blogs Public Release",
		"version"=>"1.1",
		"author"=>"Eric Stevens, changes by `2Oliver Brendel",
		"allowanonymous"=>true,
		"category"=>"General",
		"download"=>"",
		"settings"=>array(
			"Blog Settings,title",
			"blogtitle"=>"Title of the blog section.|MightyBlogs",
			"blogkey"=>"Hot key for blog nav.,string,1|#",
			"lastblog"=>"Last time any blog was posted|".DATETIME_DATEMIN,
			"words"=>"Path to words dictionary file|/usr/share/dict/words",
			"disablehtml"=>"Disable specific html to prevent external input,bool|1",
		),
		"prefs"=>array(
			"Blog User Preferences,title",
			"lastblog"=>"Last time user read blog|".DATETIME_DATEMIN,
			"canblog"=>"User can blog,bool|0",
			"blogsig"=>"Blog Signature|"
		)
	);
	return $info;
}

function mightyblogs_install(){

	require_once("lib/tabledescriptor.php");

	$blogdesc = array(
		'blogid'=>array('name'=>'blogid', 'type'=>'int(11) unsigned',
			'extra'=>'auto_increment'),
		'author'=>array('name'=>'author', 'type'=>'int(11) unsigned',
			'default'=>'1'),
		'date'=>array('name'=>'date', 'type'=>'datetime'),
		'subject'=>array('name'=>'subject', 'type'=>'varchar(255)'),
		'body'=>array('name'=>'body', 'type'=>'text'),
		'hits'=>array('name'=>'hits', 'type'=>'int(11) unsigned',
			'default'=>'0'),
		'key-PRIMARY'=>array('name'=>'PRIMARY', 'type'=>'primary key',
			'unique'=>'1', 'columns'=>'blogid'),
		'index-date'=>array('name'=>'date', 'type'=>'index', 'columns'=>'date'),
		'index-author'=>array('name'=>'author', 'type'=>'index',
			'columns'=>'author'));

	synctable(db_prefix('mod_mightyblogs'), $blogdesc, true);

	module_addhook("village");
	module_addhook("footer-shades");
	module_addhook("index");
	return true;
}

function mightyblogs_uninstall(){
        debug("Dropping mod_mightyblogs table. All blogs are lost.  Woe is them.");
        $sql = "DROP TABLE IF EXISTS " . db_prefix("mod_mightyblogs");
        $conn = Database::getDoctrineConnection();
        $conn->executeStatement($sql);
        return true;
}

function mightyblogs_dohook($hookname,$args){
	switch($hookname){
	case "village":
	case "footer-shades":
	case "index":
		// $args only has the othernav stuff from the village.
		if ($hookname == "village") {
			tlschema($args['schemas']['othernav']);
			addnav($args['othernav']);
			tlschema();
		} else {
			addnav("Other");
		}
		$title = get_module_setting("blogtitle");
		$blogkey = get_module_setting("blogkey");
		if (get_module_pref("lastblog") < get_module_setting("lastblog")){
			addnav(array("%s?`b%s`b", $blogkey, $title),
					"runmodule.php?module=mightyblogs&op=view");
		}else{
			addnav(array("%s?%s", $blogkey, $title),
					"runmodule.php?module=mightyblogs&op=view");
		}
		break;
	}
	return $args;
}

function mightyblogs_run(){
        global $session;
        require_once("lib/datetime.php");
        require_once("lib/villagenav.php");
        //if (!isset($session['user']['acctid'])||$session['user']['acctid']==0) return;
        $op = httpget("op");
        $id = (int) httpget("id");
        $author = httpget("author");
        $day = httpget("day");
        $daydate = null;
        if ($day !== '') {
                $daydate = DateTime::createFromFormat('Y-m-d', $day);
                if (!$daydate || $daydate->format('Y-m-d') !== $day) {
                        unset($day);
                        $daydate = null;
                }
        }
        $month = httpget("month");
        $conn = Database::getDoctrineConnection();
        $blogTable = Database::prefix('mod_mightyblogs');
        $accountsTable = Database::prefix('accounts');
        if ($op=="keepalive"){
                $conn->executeStatement(
                        "UPDATE {$accountsTable} SET laston = :laston WHERE acctid = :acctid",
                        [
                                'laston' => date("Y-m-d H:i:s"),
                                'acctid' => $session['user']['acctid'],
                        ],
                        [
                                'laston' => ParameterType::STRING,
                                'acctid' => ParameterType::INTEGER,
                        ]
                );
                global $REQUEST_URI;
                echo '<html><meta http-equiv="Refresh" content="30;url='.$REQUEST_URI.'"></html><body>'.date("Y-m-d H:i:s")."</body></html>";
                exit();
        }

	page_header(get_module_setting("blogtitle"));
	rawoutput("<script language='JavaScript'>
	<!--
	function showHide(id){
		var item = document.getElementById(id);
		if (item.style.display=='block'){
			item.style.display='none';
		}else{
			item.style.display='block';
		}
	}
	//-->
	</script>");
	rawoutput("<style type='text/css'>
		span.tangent {
			display: none;
			border: 1px dotted #0000FF;
		}
		table.calendar {
			border-left: 1px solid #000000;
			border-right: 0px solid #000000;
			border-bottom: 1px solid #000000;
			border-top: 0px solid #000000;
		}
		table.calendar tr {

		}
		table.calendar td {
			border-left: 0px solid #000000;
			border-right: 1px solid #000000;
			border-bottom: 0px solid #000000;
			border-top: 1px solid #000000;
			font-size: 10px;
			background-color: #003366;
			color: #FFFFFF;
			text-align: center;
		}
		table.calendar td.new {
			background-color: #006699;
		}
		table.calendar td.new a {
			color: #FFFF66;
			text-decoration: none;
		}
		table.calendar td.offmonth {
			background-color: #006633;
		}
	</style>");
        if ($op == "del") {
                $deleted = $conn->executeStatement(
                        "DELETE FROM {$blogTable} WHERE blogid = :blogid",
                        [
                                'blogid' => $id,
                        ],
                        [
                                'blogid' => ParameterType::INTEGER,
                        ]
                );
                output($deleted." blogs deleted.`n");
                $op = "view";
                $id = 0;
        }

        $limitClause = ' LIMIT 15';
        $conditions = [];
        $parameters = [];
        $types = [];

        switch ((int) httpget('timeframe')) {
                case 1:
                        $time = "7 days";
                break;
                case 2:
                        $time = "14 days";
                break;
                case 3:
                        $time = "1 month";
                break;
                case 4:
                        $time = "";
                break;
                default:
                        $time = "7 days";
                break;
        }

        if ($id>0) {
                $conditions[] = 'b.blogid = :blogid';
                $parameters['blogid'] = $id;
                $types['blogid'] = ParameterType::INTEGER;
                addnav("Calendar");
        }elseif ($author>""){
                $conditions[] = 'a.login = :author';
                $parameters['author'] = $author;
                $types['author'] = ParameterType::STRING;
                if ($daydate) {
                        $conditions[] = 'b.date >= :dayStart';
                        $conditions[] = 'b.date <= :dayEnd';
                        $parameters['dayStart'] = $daydate->format('Y-m-d 00:00:00');
                        $parameters['dayEnd'] = $daydate->format('Y-m-d 23:59:59');
                        $types['dayStart'] = ParameterType::STRING;
                        $types['dayEnd'] = ParameterType::STRING;
                } else {
                        if ($time!='') {
                                $parameters['startDate'] = date('Y-m-d H:i:s', strtotime('-'.$time));
                                $types['startDate'] = ParameterType::STRING;
                                $conditions[] = 'b.date > :startDate';
                        }
                        $limitClause = '';
                }
                addnav(array("%s's Calendar", htmlspecialchars(
                        $author,
                        ENT_QUOTES,
                        'UTF-8'
                )));
        }else{
                if ($daydate) {
                        $conditions[] = 'b.date >= :dayStart';
                        $conditions[] = 'b.date <= :dayEnd';
                        $parameters['dayStart'] = $daydate->format('Y-m-d 00:00:00');
                        $parameters['dayEnd'] = $daydate->format('Y-m-d 23:59:59');
                        $types['dayStart'] = ParameterType::STRING;
                        $types['dayEnd'] = ParameterType::STRING;
                } else {
                        if ($time!='') {
                                $parameters['startDate'] = date('Y-m-d H:i:s', strtotime('-'.$time));
                                $types['startDate'] = ParameterType::STRING;
                                $conditions[] = 'b.date > :startDate';
                        }

                        $limitClause = '';
                }
                addnav("Calendar");
        }
        $calendar = mightyblogs_calendar($month, isset($day) ? $day : '', $author);
	global $templatename;
	if ($templatename == "Classic.htm") {
		$calendar = "<tr><td>$calendar</td></tr>";
	}
	addnav("$calendar","!!!addraw!!!",true);

	$sql = "SELECT a.name, b.* FROM {$blogTable} AS b INNER JOIN {$accountsTable} AS a ON a.acctid = b.author";
        if ($conditions) {
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY b.date DESC';
        if ($limitClause !== '') {
                $sql .= $limitClause;
        }

	if ($op == "view") {
		$result = $conn->executeQuery($sql, $parameters, $types);
		output("Select by time:`n");
		rawoutput("<table cellpadding=2 noframe><tr>");
		$sevendays=translate_inline("7 days");
                rawoutput("<td><a href='runmodule.php?module=mightyblogs&op=$op&author="
                        . rawurlencode($author) .
                        "&timeframe=1'>$sevendays</a><td>");
                addnav(
                        "",
                        "runmodule.php?module=mightyblogs&op=$op&author=" .
                                rawurlencode($author) .
                                "&timeframe=1"
                );
                $fourteendays=translate_inline("14 days");
                rawoutput("<td><a href='runmodule.php?module=mightyblogs&op=$op&author="
                        . rawurlencode($author) .
                        "&timeframe=2'>$fourteendays</a><td>");
                addnav(
                        "",
                        "runmodule.php?module=mightyblogs&op=$op&author=" .
                                rawurlencode($author) .
                                "&timeframe=2"
                );
                $onemonth=translate_inline("1 month");
                rawoutput("<td><a href='runmodule.php?module=mightyblogs&op=$op&author="
                        . rawurlencode($author) .
                        "&timeframe=3'>$onemonth</a><td>");
                addnav(
                        "",
                        "runmodule.php?module=mightyblogs&op=$op&author=" .
                                rawurlencode($author) .
                                "&timeframe=3"
                );
                $all=translate_inline("No Limit");
                rawoutput("<td><a href='runmodule.php?module=mightyblogs&op=$op&author="
                        . rawurlencode($author) .
                        "&timeframe=4'>$all</a><td>");
                addnav(
                        "",
                        "runmodule.php?module=mightyblogs&op=$op&author=" .
                                rawurlencode($author) .
                                "&timeframe=4"
                );
		rawoutput("</tr></table><br>");
		foreach ($result->fetchAllAssociative() as $row){
			mightyblogs_show($row);
		}
	}elseif ($op == "edit"){
		$result = $conn->executeQuery($sql, $parameters, $types);
		$row = $result->fetchAssociative();
		if ($row === false){
			$row = array("name"=>$session['user']['name'],"blogid"=>"","author"=>"","date"=>date("Y-m-d H:i:s"),"subject"=>"","body"=>"","hits"=>0);
		}
		mightyblogs_form($row);
		if ($row['subject']>"" || $row['body']>"")
			mightyblogs_show($row);
        }elseif ($op == "save"){
                $post = httpallpost();
                $post['blogid'] = isset($post['blogid']) ? (int)$post['blogid'] : 0;
                $post['body'] = isset($post['body']) ? $post['body'] : '';
                $post['subject'] = isset($post['subject']) ? $post['subject'] : '';
                if (isset($post['save'])){
		if ($post['blogid']>0){
			$updated = $conn->executeStatement(
				"UPDATE {$blogTable} SET body = :body, subject = :subject WHERE blogid = :blogid",
				[
					'body' => $post['body'],
					'subject' => $post['subject'],
					'blogid' => $post['blogid'],
				],
				[
					'body' => ParameterType::STRING,
					'subject' => ParameterType::STRING,
					'blogid' => ParameterType::INTEGER,
				]
			);
			output($updated." rows updated.`n");
		}else{
			$blogsig = get_module_pref("blogsig");
			$post['body']=str_replace("`b","",$post['body']);
			$post['body']=str_replace("`c","",$post['body']);
			$post['body']=str_replace("`i","",$post['body']);
			if ($blogsig > "")
				$post['body'] .= "`0`n".get_module_pref("blogsig")."`0";
			$date = date("Y-m-d H:i:s");
			$inserted = $conn->executeStatement(
				"INSERT INTO {$blogTable} (body, subject, author, date) VALUES (:body, :subject, :author, :date)",
				[
					'body' => $post['body'],
					'subject' => $post['subject'],
					'author' => $session['user']['acctid'],
					'date' => $date,
				],
				[
					'body' => ParameterType::STRING,
					'subject' => ParameterType::STRING,
					'author' => ParameterType::INTEGER,
					'date' => ParameterType::STRING,
				]
			);
			output($inserted." rows inserted.`n");
			set_module_setting("lastblog", $date);
			$post['date'] = $date;
		}
		$post['author'] = $session['user']['acctid'];
        }else{
		//we're previewing the blog
		$post['body']=str_replace("`b","",$post['body']);
		$post['body']=str_replace("`c","",$post['body']);
		$post['body']=str_replace("`i","",$post['body']);
		mightyblogs_form($post);
        }
        //$post['body'] = mightyblogs_spell($post['body']);
                mightyblogs_show($post);
        }
	addnav("Options");
	addnav("Blog Homepage","runmodule.php?module=mightyblogs&op=view");
	if (!$session['user']['loggedin']){
		addnav("L?Return to Login","index.php");
	}elseif ($session['user']['alive']){
		villagenav();
	}else{
		addnav("S?Return to the Shades","shades.php");
	}if (get_module_pref("canblog")){
		addnav("Add a blog","runmodule.php?module=mightyblogs&op=edit&id=-1");
	}

	addnav("Browse by Author");
	$sql1 = "SELECT name,max(login) AS login, max(date) AS date FROM " . db_prefix("mod_mightyblogs") . " INNER JOIN " . db_prefix("accounts") . " ON acctid = author GROUP BY name";
	$result = $conn->executeQuery($sql1);
	foreach ($result->fetchAllAssociative() as $row){
		addnav_notl(sanitize($row['name']));
		addnav(array("%s (%s)", $row['name'], reltime(strtotime($row['date']))),"runmodule.php?module=mightyblogs&op=view&author=".rawurlencode($row['login']));
	}

	global $seenblogs;
	if (isset($seenblogs) && count($seenblogs)>0){
		$conn->executeStatement(
			"UPDATE {$blogTable} SET hits = hits + 1 WHERE blogid IN (:blogids)",
			[
				'blogids' => $seenblogs,
			],
			[
				'blogids' => ArrayParameterType::INTEGER,
			]
		);
	}
	page_footer();
}

$lastblogdate = "";
$seenblogs = array();
function mightyblogs_show($blog){
	require_once("lib/nltoappon.php");
	global $lastblogdate, $session, $seenblogs;
	if (!is_array($seenblogs)) $seenblogs = array();
	if ($blog['blogid']>"" && isset($session['user']['acctid']) && $session['user']['acctid']!=$blog['author']) array_push($seenblogs,$blog['blogid']);
	$d = strtotime($blog['date']);
	$thisblogdate = substr($blog['date'],0,10);
	if ($thisblogdate != $lastblogdate){
		$lastblogdate = $thisblogdate;
		output_notl("`^<font size=+1>".date("l, F d".(date("Y",$d)!=date("Y")?", Y":" "),$d)."</font>`0`n",true);
	}
	if ($blog['date'] > get_module_pref("lastblog")) set_module_pref("lastblog",$blog['date']);
	output_notl("`^".date("h:i a T",$d)."`0 - ");
	output_notl("`@%s`0", $blog['name']);
	if ($blog['subject']>"")
		output_notl(" - `%%s`0", $blog['subject']);
	if (!isset($blog['author'])) $blog['author'] = "Lost in the ages";
	if (!isset($blog['hits'])) $blog['hits']=0;
	output_notl("`n");
	if (isset($session['user']['acctid']) && ($session['user']['acctid']==$blog['author'] || ($session['user']['superuser']&SU_MEGAUSER) == SU_MEGAUSER)){
		$edit = translate_inline("Edit");
		$del = translate_inline("Delete");
		$delconf = translate_inline("Are you sure you want to delete this blog?");
		output_notl("[ <a href='runmodule.php?module=mightyblogs&op=edit&id={$blog['blogid']}'>$edit</a>",true);
		addnav("","runmodule.php?module=mightyblogs&op=edit&id={$blog['blogid']}");
		output_notl("| <a href='runmodule.php?module=mightyblogs&op=del&id={$blog['blogid']}' onClick=\"return(confirm('$delconf'));\">$del</a>",true);
		addnav("","runmodule.php?module=mightyblogs&op=del&id={$blog['blogid']}");
		output_notl(" ]");
	}
	output("Hits: %s`n", $blog['hits']);
	//add in raw links
	$urlcodes = "[!-;=?-~]"; //all keyboard chars sans space, < and >
	$bodyparts = preg_split("/([<>])/",$blog['body'],-1,PREG_SPLIT_DELIM_CAPTURE);
	$body = "";
	$intag = false;
	foreach($bodyparts as $key=>$val){
		//$body .= "`n--------------`n".htmlentities($val, ENT_COMPAT, getsetting("charset", "ISO-8859-1"));
		if ($val == "<") {
			$intag = true;
		}elseif ($val == ">") {
			$intag = false;
		}elseif (!$intag){
			//we're not within any HTML tags, we are safe to add links here.
			$val = htmlentities($val, ENT_COMPAT, getsetting("charset", "ISO-8859-1")); //get quotes and such encoded.
			$val = str_replace("`&amp;", "`&", $val);
			$val = preg_replace("/([[:alpha:]]+:\\/\\/)([!-~]+)/","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",$val);
		}elseif ($intag){
			$tag = explode("[ \t\n]",$val);
			if (strtolower($tag[0])=="a"){
				$targetfound = false;
				foreach($tag as $v){
					if (substr(strtolower($v),0,6)=="target") {
						$targetfound = true;
						break;
					}
				}
				if (!$targetfound) $val.=" target=\"_blank\"";
			}
		}
		$body .= $val;
	}
	$body = str_replace("<tangent>","<a href='#' onClick='showHide(\"tangent{$blog['blogid']}\");return false;'>Tangent here</a>.<br><span class='tangent' id='tangent{$blog['blogid']}'>",$body);
	$body = str_replace("</tangent>","</span>",$body);
	$body = preg_replace("/(>?)([[:alpha:]]+:\\/\\/)($urlcodes+)[[:punct:]]?/","\\1<a href=\"\\2\\3\" target=\"_blank\">\\2\\3</a>\\4",$blog['body']);
	$body = preg_replace("/([[:alpha:]]+:\\/\\/)([!-~]+)[[:punct:]]*/","<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>",$blog['body']);
	//yeah, we want to allow HTML, blogs are only being given to trusted users.
	output_notl("`@".nltoappon(stripslashes($body))."`0`n`n",true);
}

function mightyblogs_form($blog){
	rawoutput("<form action='runmodule.php?module=mightyblogs&op=save' method='POST'>");
	addnav("","runmodule.php?module=mightyblogs&op=save");
	output("`bAdd / Edit a Blog:`b`n");
	rawoutput("<input type='hidden' name='blogid' value=\"".htmlentities($blog['blogid'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\">");
	rawoutput("<input type='hidden' name='name' value=\"".htmlentities($blog['name'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\">");
	rawoutput("<input type='hidden' name='date' value=\"".htmlentities($blog['date'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\">");
	output("Subject: ");
	rawoutput("<input name='subject' value=\"".htmlentities($blog['subject'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."\" size='50' maxlength='255'><br/>");
	output("Body:`n");
	rawoutput("<textarea name='body' cols='70' rows='15' class='input'>".htmlentities($blog['body'], ENT_COMPAT, getsetting("charset", "ISO-8859-1"))."</textarea><br/>");
	$prev = translate_inline("Preview");
	$save = translate_inline("Save");
	rawoutput("<input type='submit' value='$prev' name='preview' class='button'>");
	rawoutput("<input type='submit' value='$save' name='save' class='button'>");
	rawoutput("</form>");
	output("All HTML is legitimate in the blogs. Though `+c or +i or +b for centered, bold, italic fonts are disabled by default - it will not be saved!-.`n");
	output("Things that look like links pasted right in will automatically link, and things that are manually linked (<a href>) will automatically open in a new window.");
	output("You can also use the <tangent> tag to insert tangents, and they'll be clickable & expandable.`n");
	rawoutput("<iframe src='runmodule.php?module=mightyblogs&op=keepalive' width='1' height='1' border='0'></iframe>");
	addnav("","runmodule.php?module=mightyblogs&op=keepalive");
}

function mightyblogs_spell($input,$prefix="<span style='border: 1px dotted #FF0000;'>",$postfix="</span>"){
	$words = get_module_setting("words");
	require_once("lib/spell.php");
	return spell($input,$words,$prefix,$postfix);
}

function mightyblogs_calendar($month,$day,$author){
        $conn = Database::getDoctrineConnection();
        $blogTable = Database::prefix('mod_mightyblogs');
        $accountsTable = Database::prefix('accounts');
        $monthDate = DateTime::createFromFormat('Y-m', $month);
        if (!$monthDate || $monthDate->format('Y-m') !== $month) {
                $monthDate = new DateTime('first day of this month');
        } else {
                $monthDate->setDate((int)$monthDate->format('Y'), (int)$monthDate->format('m'), 1);
        }
        $start = clone $monthDate;
        $start->modify('-'.$start->format('w').' days');
        $end = clone $start;
        $end->modify('+5 weeks');

        $queryBuilder = $conn->createQueryBuilder();
        $queryBuilder
                ->select("DISTINCT DATE(b.date) AS d")
                ->from($blogTable, 'b')
                ->innerJoin('b', $accountsTable, 'a', 'a.acctid = b.author')
                ->where('b.date >= :start')
                ->andWhere('b.date <= :end')
                ->orderBy('d');
        $queryBuilder->setParameter('start', $start->format('Y-m-d 00:00:00'), ParameterType::STRING);
        $queryBuilder->setParameter('end', $end->format('Y-m-d 23:59:59'), ParameterType::STRING);
        if ($author>""){
                $queryBuilder->andWhere('a.login IN (:authors)');
                $queryBuilder->setParameter('authors', array($author), Connection::PARAM_STR_ARRAY);
        }
        $result = $queryBuilder->executeQuery();
        $blogdays = array();
        foreach ($result->fetchAllAssociative() as $row){
                $blogdays[$row['d']] = true;
        }

        $calendar = "<table class='calendar' cellpadding='1' cellspacing='0'>";
        $calendar.= "<tr>";
        $prevMonth = clone $monthDate;
        $prevMonth->modify('-1 month');
        $link = "runmodule.php?module=mightyblogs&op=view&author=" .
                rawurlencode($author) .
                "&month=" . $prevMonth->format('Y-m') .
                "&day=$day";
        addnav("", $link);
        $calendar .= "<td class='new'><a href='$link'>&lt;</a></td>";
        $calendar.= "<td colspan='5' class='new'>".$monthDate->format('F y')."</td>";
        $nextMonth = clone $monthDate;
        $nextMonth->modify('+1 month');
        $link = "runmodule.php?module=mightyblogs&op=view&author=" .
                rawurlencode($author) .
                "&month=" . $nextMonth->format('Y-m') .
                "&day=$day";
        addnav("", $link);
        $calendar .= "<td class='new'><a href='$link'>&gt;</a></td></tr>";
        for ($d = clone $start; $d < $end; $d->modify('+1 day')){
                if ($d->format('w')==0) $calendar .= "<tr>";
                if (isset($blogdays[$d->format('Y-m-d')])){
                        $link = "runmodule.php?module=mightyblogs&op=view&author=" .
                                rawurlencode($author) .
                                "&month=" . $monthDate->format('Y-m') .
                                "&day=" . $d->format('Y-m-d');
                        addnav("", $link);
                        $calendar .= "<td class='new'><a href='$link'>" .
                                $d->format('d') .
                                "</a></td>";
                }else{
                        if ($d->format('m')==$monthDate->format('m')){
                                $calendar.="<td>".$d->format('d')."</td>";
                        }else{
                                $calendar.="<td class='offmonth'>".$d->format('d')."</td>";
                        }
                }
                if ($d->format('w')==6) $calendar .= "</tr>";
        }
        $calendar.="</table>";
        return $calendar;
}
?>
