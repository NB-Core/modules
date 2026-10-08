<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

/*
Details:
 * This is a module for Clans to set up a few options
*/
require_once("lib/commentary.php");
require_once("lib/http.php");
require_once("lib/systemmail.php");
require_once("lib/villagenav.php");

function clanoptions_getmoduleinfo(){
	$info = array(
		"name"=>"Clan Options",
		"vertxtloc"=>"http://dragonprime.net/users/CortalUX/",
		"download"=>"http://dragonprime.net/users/CortalUX/clanoptions.zip",
		"author"=>"`@CortalUX",
                "version"=>"1.6",
		"category"=>"Clan",
		"download"=>"http://dragonprime.net/users/CortalUX/clanoptions.zip",
		"prefs-clans"=>array(
			"Clan Options - General,title",
			"officersUse"=>"Can officers set stuff up?,bool|0",
			"autoAc"=>"Will users be automatically added?,bool|0",
			"minDKs"=>"Min DKs?,int|0",
			"fountPoster"=>"Fountain poster?,text|..It is too smudged to make out...",
                        "authID"=>"Author ID?,int|0",
                        "petra_tattoos"=>"Petra: Clan tattoo definitions,text|a:0:{}",
		),
	);
	return $info;
}

function clanoptions_install(){
	module_addhook("moderate");
	module_addhook("newday");
	module_addhook("footer-clan");
	return true;
}
function clanoptions_uninstall(){
	return true;
}
function clanoptions_dohook($hookname, $args){
	global $session;
	$op = httpget('op');
	$clan = $session['user']['clanid'];
        switch($hookname){
                case "moderate":
			$sql = "SELECT *FROM " . db_prefix("clans");
			$result = db_query($sql);
			if (db_num_rows($result)>0){
				for ($i=0;$i<db_num_rows($result);$i++){
					$row = db_fetch_assoc($result);
					$x = "clanoptions-fount-".$row['clanid'];
					$y = "<".$row['clanshort']."> ".htmlentities(full_sanitize($row['clanname']))." - "."Clan Fountain";
					$args[$x] = $y;
				}
			}
		break;
		case "footer-clan":
			$title = translate_inline("`\$Auto Acceptance");
			$message = translate_inline("`@The Clan you have just joined automatically accepts Applicants. You are now a member.");
			$dks = $session['user']['clanid']!=0 ? (int)get_module_objpref("clans", $session['user']['clanid'], "minDKs") : 0;
			// Leaders, and officers when they may manage the options, are never removed by the minimum.
			$dkexempt = $session['user']['clanrank']>=CLAN_LEADER
				|| ($session['user']['clanrank']==CLAN_OFFICER && get_module_objpref("clans", $clan, "officersUse")==1);
			if ($dks>0 && !$dkexempt && $session['user']['dragonkills']<$dks) {
				$sql = "SELECT * FROM " . db_prefix("clans") . " WHERE clanid=".$session['user']['clanid'];
				$result = db_query($sql);
				$row = db_fetch_assoc($result);
				$n = full_sanitize($row['clanname']);
				$title = translate_inline("`\$Kicked Out");
				$message = array(translate_inline("`@The Clan you were a member of (%s), has a minimum `&Dragonkill`@ limit of `^%s`@, however you don't pass it."),$n,$dks);
				systemmail($session['user']['acctid'],$title,$message);
				$session['user']['clanrank'] = CLAN_APPLICANT;
				$session['user']['clanid'] = 0;
				$session['user']['clanjoindate'] = DATETIME_DATEMIN;
				$apply_short = "`@Clan App: `&%s`0";
				$subj = serialize(array($apply_short, $session['user']['name']));
				$conn = Database::getDoctrineConnection();
				$mailTable = Database::prefix("mail");
				$conn->executeStatement(
					"DELETE FROM {$mailTable} WHERE msgfrom=0 AND seen=0 AND subject = :subject",
					['subject' => $subj],
					['subject' => ParameterType::STRING]
				);
			} elseif ($session['user']['clanrank'] == CLAN_APPLICANT&&
						get_module_objpref("clans", $session['user']['clanid'],"autoAc")==1&&
						$session['user']['clanid']!=0){

				$session['user']['clanrank'] = CLAN_MEMBER;
				systemmail($session['user']['acctid'],$title,$message);
			}
			if (httpget('op')=='list'||httpget('op')=='apply') {
				rawoutput("<div style='clear:left;'>");
				$to = httpget('to');
				if ($to!=''&&$to>0) {
					$dks = get_module_objpref("clans", $to, "minDKs");
					$conn = Database::getDoctrineConnection();
					$clansTable = Database::prefix("clans");
					$row = $conn->executeQuery(
						"SELECT * FROM {$clansTable} WHERE clanid = :clanid",
						['clanid' => (int) $to],
						['clanid' => ParameterType::INTEGER]
					)->fetchAssociative();
					$n = full_sanitize($row['clanname']);
					if ($dks>=$session['user']['dragonkills']&&$dks!=0) {
						output("`^%s`@ has a minimum `&Dragonkill`@ limit of `^%s`@, but you pass it.",$n,$dks);
					} elseif ($dks>0) {
						output("`^%s`@ has a minimum `&Dragonkill`@ limit of `^%s`@, and you don't pass it.",$n,$dks);
					}
				} else {
					$sql = "SELECT * FROM " . db_prefix("clans");
					$result = db_query($sql);
					if (db_num_rows($result)>0){
						output_notl('`n');
						for ($i=0;$i<db_num_rows($result);$i++){
							$row = db_fetch_assoc($result);
							$n = full_sanitize($row['clanname']);
							$dks = get_module_objpref("clans", $row['clanid'], "minDKs");
							if ($dks<=$session['user']['dragonkills']&&$dks!=0) {
								output("`^%s`@ has a minimum `&Dragonkill`@ limit of `^%s`@, but you pass it.`n",$n,$dks);
							} elseif ($dks>0) {
								output("`^%s`@ has a minimum `&Dragonkill`@ limit of `^%s`@, and you don't pass it.`n",$n,$dks);
							}
						}
					}
				}
				rawoutput("</div>");
			}
			if ($op=='enter'&&$clan!=0||$op==''&&$clan!=0) {
				addnav("~");
				if ($session['user']['clanrank'] >= CLAN_LEADER){
					addnav("Clan Fountain","runmodule.php?module=clanoptions&op=fountain");
					addnav("Clan Options","runmodule.php?module=clanoptions&op=admin");
				} elseif ($session['user']['clanrank'] >= CLAN_OFFICER){
					addnav("Clan Fountain","runmodule.php?module=clanoptions&op=fountain");
					if (get_module_objpref("clans", $clan, "officersUse")==1){
						addnav("Clan Options","runmodule.php?module=clanoptions&op=admin");
					}
				}
			}
                break;
                case "petra_clantattoo":
                        $clanid = isset($args['clanid']) ? (int)$args['clanid'] : 0;
                        if ($clanid > 0) {
                                $tattoos = clanoptions_get_clan_tattoos($clanid);
                                foreach ($tattoos as $definition) {
                                        $args['tattoos'][] = $definition;
                                }
                        }
                break;
        }
        return $args;
}

function clanoptions_run() {
        global $session;
        $op = httpget('op');
        switch ($op) {
                case "fountain":
			page_header("High Fountain");
			clanoptions_fountain();
		break;
		case "admin":
			page_header("Clan Setup");
			clanoptions_setup();
		break;
	}
        page_footer();
}

function clanoptions_user_can_manage_settings() {
        global $session;

        $clan = (int)$session['user']['clanid'];
        if ($clan <= 0) {
                return false;
        }

        if ($session['user']['clanrank'] >= CLAN_LEADER) {
                return true;
        }

        if ($session['user']['clanrank'] >= CLAN_OFFICER && get_module_objpref("clans", $clan, "officersUse")) {
                return true;
        }

        return false;
}

function clanoptions_sanitize_tattoo_field($value) {
        $value = trim((string)$value);
        return str_replace('|', '/', $value);
}

function clanoptions_clean_tattoo_identifier($rawId) {
        $rawId = trim((string)$rawId);
        $clean = strtolower(preg_replace('/[^a-z0-9_-]/i', '', $rawId));
        return $clean;
}

function clanoptions_parse_tattoo_lines($text, &$messages = array()) {
        $tattoos = array();
        $lines = preg_split("/(\r\n|\r|\n)/", (string)$text);
        $lineNumber = 0;
        foreach ($lines as $line) {
                $lineNumber++;
                $line = trim($line);
                if ($line === '') {
                        continue;
                }

                $parts = explode('|', $line, 4);
                $parts = array_pad($parts, 4, '');
                list($rawId, $rawName, $rawColor, $rawDescription) = $parts;

                $rawIdTrimmed = trim((string)$rawId);
                $identifier = clanoptions_clean_tattoo_identifier($rawIdTrimmed);
                if ($identifier === '') {
                        $messages[] = sprintf("Line %d was skipped because the identifier is missing.", $lineNumber);
                        continue;
                }

                if (isset($tattoos[$identifier])) {
                        $messages[] = sprintf("Line %d was skipped because the identifier '%s' is already used.", $lineNumber, $identifier);
                        continue;
                }

                $displayName = clanoptions_sanitize_tattoo_field($rawName);
                if ($displayName === '') {
                        $displayName = ($rawId !== '' ? trim((string)$rawId) : $identifier);
                }

                $color = clanoptions_sanitize_tattoo_field($rawColor);
                $description = clanoptions_sanitize_tattoo_field($rawDescription);

                if ($identifier !== strtolower($rawIdTrimmed)) {
                        $messages[] = sprintf("Line %d identifier adjusted to '%s'.", $lineNumber, $identifier);
                }

                $tattoos[$identifier] = array(
                        'id' => $identifier,
                        'name' => $displayName,
                        'color' => $color,
                        'description' => $description,
                        'name_translated' => false,
                        'description_translated' => false,
                );
        }

        return $tattoos;
}

function clanoptions_normalize_stored_tattoo($clanid, $id, $definition) {
        $identifier = clanoptions_clean_tattoo_identifier(is_array($definition) && isset($definition['id']) ? $definition['id'] : $id);
        if ($identifier === '') {
                return null;
        }

        $name = '';
        $color = '';
        $description = '';
        $nameTranslated = false;
        $descriptionTranslated = false;

        if (is_array($definition)) {
                if (isset($definition['name'])) {
                        $name = clanoptions_sanitize_tattoo_field($definition['name']);
                }
                if (isset($definition['color'])) {
                        $color = clanoptions_sanitize_tattoo_field($definition['color']);
                }
                if (isset($definition['description'])) {
                        $description = clanoptions_sanitize_tattoo_field($definition['description']);
                }
                $nameTranslated = !empty($definition['name_translated']);
                $descriptionTranslated = !empty($definition['description_translated']);
        } elseif (is_string($definition)) {
                $name = clanoptions_sanitize_tattoo_field($definition);
        }

        if ($name === '') {
                $name = $identifier;
        }

        return array(
                'id' => $identifier,
                'name' => $name,
                'color' => $color,
                'description' => $description,
                'clanid' => (int)$clanid,
                'name_translated' => $nameTranslated,
                'description_translated' => $descriptionTranslated,
        );
}

function clanoptions_get_clan_tattoos($clanid) {
        $tattoos = array();
        $raw = get_module_objpref("clans", $clanid, "petra_tattoos");
        if ($raw === false) {
                $raw = '';
        }

        $stored = @unserialize($raw);
        if (is_array($stored)) {
                foreach ($stored as $id => $definition) {
                        $normalized = clanoptions_normalize_stored_tattoo($clanid, $id, $definition);
                        if ($normalized === null) {
                                continue;
                        }
                        $tattoos[$normalized['id']] = $normalized;
                }
        }

        if (!empty($tattoos)) {
                return $tattoos;
        }

        $messages = array();
        $tattoos = clanoptions_parse_tattoo_lines($raw, $messages);
        foreach ($tattoos as $id => $definition) {
                $tattoos[$id]['clanid'] = (int)$clanid;
        }

        return $tattoos;
}

function clanoptions_format_tattoo_lines($tattoos) {
        if (!is_array($tattoos) || empty($tattoos)) {
                return '';
        }

        $lines = array();
        foreach ($tattoos as $definition) {
                if (!is_array($definition)) {
                        continue;
                }
                $id = isset($definition['id']) ? $definition['id'] : '';
                $name = isset($definition['name']) ? $definition['name'] : '';
                $color = isset($definition['color']) ? $definition['color'] : '';
                $description = isset($definition['description']) ? $definition['description'] : '';

                $segments = array($id, $name, $color, $description);
                for ($i = count($segments) - 1; $i > 0; $i--) {
                        if ($segments[$i] === '') {
                                array_pop($segments);
                                continue;
                        }
                        break;
                }

                $lines[] = implode(' | ', $segments);
        }

        return implode("\n", $lines);
}

function clanoptions_fountain() {
	global $session;
	addnav("Navigation");
	addnav("Return to your Clan","clan.php");
	$clan = $session['user']['clanid'];
	$type = httpget('type');
	$link = "runmodule.php?module=clanoptions&op=fountain&";
	addnav("Actions");
	switch ($type) {
		default:
		case "talk":
			output("`@You step under a curtain to hide from the rest of the clan, and sit in the High Fountain, bathing in the cool water. You wave to your fellow Leaders and Officers. Members are not allowed here.`n");
			$time = gametime();
			$tomorrow = strtotime(date("Y-m-d H:i:s",$time)." + 1 day");
			$tomorrow = strtotime(date("Y-m-d 00:00:00",$tomorrow));
			$secstotomorrow = $tomorrow-$time;
			$realsecstotomorrow = $secstotomorrow / getsetting("daysperday",4);
			$authid = get_module_objpref("clans", $clan, "authID");
			if ($authID!=0) {
				$sql = "SELECT name FROM " . db_prefix("accounts") . " WHERE acctid='".$authid."'";
				$result = db_query_cached($sql, "clanoptions-fount-".$clan);
				$row = db_fetch_assoc($result);
				$author = $row['name'];
				unset($row,$result,$sql,$time,$secstotomorrow,$tomorrow,$time);
			} else {
				$author = translate_inline('Anonymous');
			}
			output("`@You read the poster that is attached to wall in front of the fountain.`nIt says `^%s`0`@... and you see the name `&%s`@ etched in a corner.`nYou then stare at the waterclock- `^%s`0",str_replace("\'","'",str_replace("\\\"","\"",get_module_objpref("clans", $clan, "fountPoster"))),$author,getgametime());
			addcommentary();
			output("`n`n`#You listen in..`n");
			viewcommentary("clanoptions-fount-".$session['user']['clanid'],"`#Talk?`@",25,"talks");
		break;
		case "poster":
			if (httpget('s')=='yes') {
				output("`@You decide the old poster is boring, and change it.");
				$poster = substr(str_replace("'","\'",httppost('poster')),0,255);
				set_module_objpref("clans", $clan, "fountPoster",$poster);
				set_module_objpref("clans", $clan, "authID",$session['user']['acctid']);
				$time = gametime();
				$tomorrow = strtotime(date("Y-m-d H:i:s",$time)." + 1 day");
				$tomorrow = strtotime(date("Y-m-d 00:00:00",$tomorrow));
				$secstotomorrow = $tomorrow-$time;
				$realsecstotomorrow = $secstotomorrow / getsetting("daysperday",4);
				$authID = $session['user']['name'];
				output("`@You read the poster that is attached to the wall opposite the fountain.`nIt says `^%s`0`@... with your name etched in a corner.`nYou then stare at the waterclock- `^%s`@.`nYou figure a new day in `^%s hours %s minutes and %s seconds`@.`0",str_replace("\'","'",str_replace("\\\"","\"",get_module_objpref("clans", $clan, "fountPoster"))),$author,getgametime(),date("G",strtotime("1970-01-01 00:00:00 + $realsecstotomorrow seconds")),date("i",strtotime("1970-01-01 00:00:00 + $realsecstotomorrow seconds")),date("s",strtotime("1970-01-01 00:00:00 + $realsecstotomorrow seconds")));
			} else {
				rawoutput("<form action='".$link."type=poster&s=yes' method='POST'>");
				addnav("",$link."type=poster&s=yes");
				output("`&`bNew Poster:`b `7(255 chars max)`n");
				rawoutput("<input name='poster' value='' maxlength='255' size='50'>");
				$submit = translate_inline('Replace');
				rawoutput("<input type='submit' name='submit' value=\"$submit\" class='button'>");
				rawoutput("</form>");
			}
		break;
	}
	addnav("Change the Poster",$link."type=poster");
	addnav("Talk to the Others",$link."type=talk");
	addnav("Navigation");
	villagenav();
	if ($session['user']['clanrank'] >= CLAN_LEADER){
		addnav("Clan Options","runmodule.php?module=clanoptions&op=admin");
	} elseif ($session['user']['clanrank'] >= CLAN_OFFICER&&get_module_objpref("clans", $clan, "officersUse")==1){
		addnav("Clan Options","runmodule.php?module=clanoptions&op=admin");
	}
	addnav("Actions");
	modulehook("clanoptions-fountain");
}

function clanoptions_setup() {
        global $session;
        $clan = $session['user']['clanid'];
        $type = httpget('type');
        $link = "runmodule.php?module=clanoptions&op=admin";
        $canManage = clanoptions_user_can_manage_settings();
        addnav("Navigation");
        addnav("Actions");
        switch ($type) {
                case "save":
                        $minDKs=httppost('minDKs');
			if (is_numeric($minDKs)) {
				set_module_objpref("clans", $clan, "minDKs",$minDKs);
				output("`@`bThat minimum DK limit has been saved.`b`n`n");
			} else {
                                output("`@`bThat minimum DK limit is not valid.`b`n`n");
                        }
                break;
                case "toggle":
                        $status = (get_module_objpref("clans", $session['user']['clanid'], "officersUse")?"0":"1");
			set_module_objpref("clans", $session['user']['clanid'], "officersUse",$status);
			$status = translate_inline(($status?"On":"Off"));
			output_notl("`n`n`b`Q%s `^%s `Q%s`b",translate_inline("You have just toggled Officers System Editing to"),$status,translate_inline("."));
		break;
		case "atoggle":
			$status = (get_module_objpref("clans", $session['user']['clanid'], "autoAc")?"0":"1");
                        set_module_objpref("clans", $session['user']['clanid'], "autoAc",$status);
                        $status = translate_inline(($status?"On":"Off"));
                        output_notl("`n`n`b`Q%s `^%s `Q%s`b",translate_inline("You have just toggled Auto Accept to"),$status,translate_inline("."));
                break;
                case "tattoosave":
                        if (!$canManage) {
                                output("`4You are not permitted to change the clan tattoo offerings.`0`n`n");
                                break;
                        }

                        $rawList = httppost('tattoo_list');
                        $messages = array();
                        $tattoos = clanoptions_parse_tattoo_lines($rawList, $messages);
                        set_module_objpref("clans", $clan, "petra_tattoos", serialize($tattoos));
                        output("`@`bThe clan tattoo list has been saved.`b`n`n");
                        foreach ($messages as $message) {
                                output_notl("`7%s`0`n", $message);
                        }
                break;
        }
        modulehook("clanoptions-admin-savetext");
        rawoutput("<form action='".$link."&type=save' method='POST'>");
        $b = translate_inline("Minimum Dragon Kills limit:");
        $s = translate_inline('Submit');
	output("`n`&");
        rawoutput("<b>$b"."</b> <input type='text' name='minDKs'> <input type='submit' value='$s'>");
        rawoutput("</form>");
        addnav("",$link."&type=save");
        modulehook("clanoptions-admin-form");
        $tattooLines = clanoptions_format_tattoo_lines(clanoptions_get_clan_tattoos($clan));
        $charset = getsetting("charset", "UTF-8");
        output("`n`b`@Clan Tattoo Offerings`b`n");
        output("`@List each tattoo on a new line using the format `^identifier`@|`^Display Name`@|`^Color`@|`^Description`@.`n`@Only the identifier and display name are required. The color uses standard LoGD color codes, and the description is shown during Petra's purchase flow.`n");
        $saveLink = $link."&type=tattoosave";
        rawoutput("<form action='".$saveLink."' method='POST'>");
        $readOnly = $canManage ? '' : " readonly='readonly'";
        rawoutput("<textarea name='tattoo_list' rows='6' cols='70'".$readOnly.">".
                htmlentities($tattooLines, ENT_COMPAT, $charset)."</textarea><br>");
        if ($canManage) {
                $saveLabel = translate_inline('Save clan tattoos');
                rawoutput("<input type='submit' value='".htmlentities($saveLabel, ENT_COMPAT, $charset)."'>");
        } else {
                output("`7Only clan leaders or authorized officers can update this list.`n");
        }
        rawoutput("</form>");
        addnav("", $saveLink);
        output_notl("`n");
        addnav("Navigation");
        addnav("Return to your clan","clan.php?op=enter");
        villagenav();
        addnav("Clan Fountain","runmodule.php?module=clanoptions&op=fountain");
	addnav("Actions");
	if ($session['user']['clanrank'] == CLAN_LEADER){
		$status = translate_inline((get_module_objpref("clans", $clan, "officersUse")?"On":"Off"));
		output_notl("`n`@%s `^%s `@%s",translate_inline("You currently have Officers Setup Editing"),$status,translate_inline(". If this is on, Officers and all higher ranks can change the settings, otherwise, only Leaders can."));
		addnav(array("`^%s`@%s`^%s",translate_inline("Toggle Clan Officers (Currently: "),$status,translate_inline(")")),$link."&type=toggle");
	}
	output("`n`@The minimum DK limit for your clan is `&%s`@.",get_module_objpref("clans", $session['user']['clanid'], "minDKs"));
	output("`n`@Anyone who cannot change settings here will be kicked out, if they do not meet the requirement.");
	output_notl("`n`@%s `^%s `@%s",translate_inline("You currently have Auto Accept"),$status,translate_inline(". If this is on, users will automatically be added to the clan upon application."));
	$status = translate_inline((get_module_objpref("clans", $clan, "autoAc")?"On":"Off"));
	addnav(array("`^%s`@%s`^%s",translate_inline("Toggle Auto Accept (Currently: "),$status,translate_inline(")")),$link."&type=atoggle");
	modulehook("clanoptions-admin-text");
}
?>
