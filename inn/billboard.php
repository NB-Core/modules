<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

function billboard_getmoduleinfo(){
	$info = array(
		"name"=>"Billboard",
		"author"=>"Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Inn",
		"download"=>"",
		"settings"=>array(
			"Billboard Settings,title",
			"expiry"=>"Days for posts to expire,|7",
		),
		"prefs"=>array(
			"Billboard User Prefs, title",
			"posted"=>"Has the player got gift,bool|0",
		)
	);
	return $info;
}

function billboard_install(){
	global $session;
	module_addhook("inn-desc");
	module_addhook("inn");
	module_addhook("moderate");
	module_addhook("newday");
	$sql=array(
		'id'=>array('name'=>'id', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'), 
		'acctid'=>array('name'=>'acctid', 'type'=>'int(11) unsigned'),
		'subject'=>array('name'=>'subject', 'type'=>'varchar(255)'),
		'body'=>array('name'=>'body', 'type'=>'text'),
		'sent'=>array('name'=>'sent', 'type'=>'datetime', 'default'=>DATETIME_DATEMIN),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'id'),
		'key-one'=> array('name'=>'acctid', 'type'=>'key', 'unique'=>'0', 'columns'=>'acctid'),
	);
	require_once("lib/tabledescriptor.php");
	synctable(db_prefix("billboard"), $sql, true);
	return true;
}

function billboard_uninstall(){
	return true;
}

function billboard_dohook($hookname, $args){

	global $session;
	$days = (int)get_module_setting('expiry');
	switch($hookname){
	case "newday":
		set_module_pref("posted",0);
		$conn = Database::getDoctrineConnection();
		$table = Database::prefix("billboard");
		$conn->executeStatement(
			"DELETE FROM {$table} WHERE sent < :cutoff",
			['cutoff' => date("Y-m-d H:i:s",strtotime("-".$days."days"))],
			['cutoff' => ParameterType::STRING]
		);
		break;
	case "moderate":
		$args['Billboard'] = translate_inline("Billboard");
		break;
	case "inn-desc":
		output("`n`xIn a `)shady`x corner, you see a big billboard, and some people snooping around for offers.`n");
		break;
	case "inn":
		addnav("Billboard");
		addnav("Walk over to the Billboard","runmodule.php?module=billboard");
		break;
	}
	return $args;
}

function billboard_run(){
	global $session;
	require_once("lib/commentary.php");
	addcommentary();
	page_header("The Town Billboard");
	addnav("Navigation");
	addnav("R?Return to Inn","inn.php");
	addnav("Actions");
	addnav("Check out the board","runmodule.php?module=billboard");
	output("`c`b`\$The Billboard`b`c`n`n`&");
	output(" `&You stroll over to the `7large black `&Billboard mounted in the `)shady`& corner.");
	output("`nThis is where most check out what people have to offer, are you looking to make an offer yourself?`n`n`0");

	$bodylimit = 250;
	$subjectlimit = 50;
	
	$bard = translate_inline(getsetting("bard", "`^Seth"));
	$barmaid = translate_inline(getsetting("barmaid", "`%Violet"));
	$ownpost = get_module_pref("posted");
	rawoutput("
			<style>
				#billboard_table {
					background-color: #111;
					float:none;
					display: table;
					width: 100%;
				}
				
				.billboard_row {
					border: 1px solid black;
					float:none;
					display: table-row;
					width: 100%;
				}
				
				.billboard_name {
					float: left;
					display: table-cell;
					text-align: center;
					padding: 5px;
				}
				
				.billboard_head {
					float:left;
					display: table-cell;
				}
				
				.billboard_subject {
					text-align: left;
					padding: 5px;
				}
				
				.billboard_body {
					text-align: left;
					padding: 5px;
				}
				</style>
				");
	if ($ownpost) {
		output("You notice your own post from today is still there.");
		output("`nIf you want to take it down, you may do so. You will not get a new free post if you do!");
		addnav("Post on the board","");
//		addnav("Take down post from today","runmodule.php?module=billboard&action=takedown");
		output("`n`n`&");
	} else {
		output("You have no note on the board, so you may post one.`n`n`&");
		addnav("Post on the board","runmodule.php?module=billboard&action=post");
	}
	
	$action = httpget('action');
	
	switch ($action) {
		case "post":
		$subject = sanitize_html(httppost('subject'));
		$body = sanitize_html(httppost('body'));
		if ($ownpost) {
			output("You already posted.`n`n");
			break;
		}
		if (strlen($body)>$bodylimit) {
			output("You realize the message is too long... please shorten it.");
		} 
		if (strlen($subject)>$subjectlimit) {
			output("You realize the subject too long... please shorten it.");
		}
		if (strlen($subject)<=$subjectlimit && strlen($body)<=$bodylimit && strlen($body)>0) {
			//all good, post
			$barkeep = translate_inline(getsetting("barkeep", "`%Don Johnson"));
			output("`&You ask `x%s`& to put a note on the board.`n`nHe puts the note with a lazy gesture next to the other ones.`n`n",$barkeep);			
			$conn = Database::getDoctrineConnection();
			$table = Database::prefix('billboard');
			// httppost() values carry legacy addslashes(); bind them unslashed.
			$conn->executeStatement(
				"INSERT INTO {$table} (id,acctid,sent,subject,body) VALUES (0, :acctid, :sent, :subject, :body)",
				[
					'acctid'  => (int) $session['user']['acctid'],
					'sent'    => date("Y-m-d H:i:s"),
					'subject' => stripslashes((string) $subject),
					'body'    => stripslashes((string) $body),
				],
				[
					'acctid'  => ParameterType::INTEGER,
					'sent'    => ParameterType::STRING,
					'subject' => ParameterType::STRING,
					'body'    => ParameterType::STRING,
				]
			);
			set_module_pref('posted',1);
			break;
		}
		rawoutput("<div id='billboard_table'>");
		rawoutput("<form action='runmodule.php?module=billboard&action=post' method='POST'>");
		addnav("","runmodule.php?module=billboard&action=post");
			rawoutput("<div class='billboard_row'>");
				rawoutput("<div class='billboard_name'>");
					//name
					output_notl($session['user']['name']);
				rawoutput("</div>");
				rawoutput("<div class='billboard_head'>");
					rawoutput("<div class='billboard_subject'>");
						rawoutput("<textarea name='subject' cols='50' rows='1' maxlength='$subjectlimit'>".addslashes($subject)."</textarea>"); 
					rawoutput("</div>");
					rawoutput("<div class='billboard_body'>");
						rawoutput("<textarea name='body' cols='50' rows='5' maxlength='$bodylimit'>".addslashes($body)."</textarea>"); 
					rawoutput("</div>");
				rawoutput("</div>");
			rawoutput("</div>");
				rawoutput("<input type='submit' name='button' class='button' value='".translate_inline("Submit")."' />");
		rawoutput("</form>");		
		rawoutput("</div>");
		break;
		case "takedown":
			$conn = Database::getDoctrineConnection();
			$billboardTable = Database::prefix('billboard');
			$accountsTable = Database::prefix('accounts');
			$row = $conn->executeQuery(
				"SELECT a.name as name, a.login, b.* from {$billboardTable} as b left join {$accountsTable} as a on a.acctid=b.acctid where id = :id",
				['id' => (int) httpget('id')],
				['id' => ParameterType::INTEGER]
			)->fetchAssociative();
			// Only the author or a comment moderator may take a post down (the same
			// rule that decides who sees the link).
			if (!$row || ($session['user']['acctid']!=$row['acctid'] && ($session['user']['superuser'] & SU_EDIT_COMMENTS)!=SU_EDIT_COMMENTS)) {
				break;
			}

			$conn->executeStatement(
				"DELETE FROM {$billboardTable} where id = :id",
				['id' => (int) httpget('id')],
				['id' => ParameterType::INTEGER]
			);
//			set_module_pref("posted",0);
			output("`\$You take down your post.");
			require_once("lib/gamelog.php");
debug($row);
			gamelog("Post taken down: ID ".$session['user']['acctid']." took down note by ID ".$row['acctid'],"billboard");
		break;
		default:
		output("You take a look at the current billboard:`n`n");
		
		$sql = "SELECT a.name as name, a.login, b.* from ".db_prefix('billboard')." as b left join ".db_prefix('accounts')." as a on a.acctid=b.acctid order by b.id desc";
		$result = db_query($sql);

  		$write = translate_inline("Write Mail");
		rawoutput("<div id='billboard_table'>");
		while ($row = db_fetch_assoc($result)) {
			rawoutput("<div class='billboard_row'>");
				rawoutput("<div class='billboard_name'>");
					//name
					output_notl(($row['name']?$row['name']:translate_inline("Unknown")));
					if ($row['login'])
		  				rawoutput("<a href=\"mail.php?op=write&to={$row['login']}\" target=\"_blank\" onClick=\"".popup("mail.php?op=write&to={$row['login']}").";return false;\"><img src='images/newscroll.GIF' width='16' height='16' alt='$write' border='0'></a>");
				rawoutput("</div>");
				rawoutput("<div class='billboard_head'>");
					rawoutput("<div class='billboard_subject'>");
						output_notl(($row['subject']?$row['subject']:translate_inline("No Subject")));
					rawoutput("</div>");
					rawoutput("<div class='billboard_body'>");
						output_notl(($row['body']?$row['body']:translate_inline("No Message Given")));
					rawoutput("</div>");
					rawoutput("<div class='billboard_subject'>");
						if ($session['user']['acctid']==$row['acctid'] || ($session['user']['superuser'] & SU_EDIT_COMMENTS)==SU_EDIT_COMMENTS) {
							rawoutput("<a href='runmodule.php?module=billboard&action=takedown&id=".$row['id']."'>".translate_inline('Take down post')."</a>");
							addnav("","runmodule.php?module=billboard&action=takedown&id=".$row['id']);	
						}
					rawoutput("</div>");
				rawoutput("</div>");
			rawoutput("</div>");
		}
		rawoutput("</div>");

	}
	modulehook("Billboard");
	commentdisplay("", "Billboard", "Chat about the offers", 25, "mentions");

	page_footer();
}

?>
