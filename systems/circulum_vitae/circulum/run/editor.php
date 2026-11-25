<?php
if (!function_exists('circulum_editor_validpost')) {
	/**
	 * Whether the request is a posted editor form with a valid token.
	 * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
	 */
	function circulum_editor_validpost(): bool
	{
		if (!class_exists(\Lotgd\Security\Csrf::class)) {
			return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
		}
		return \Lotgd\Security\Csrf::validatePostRequest('module:circulum');
	}
	/** The hidden token field for the editor forms; empty on a core without Lotgd\Security\Csrf. */
	function circulum_editor_tokenfield(): string
	{
		return class_exists(\Lotgd\Security\Csrf::class) ? \Lotgd\Security\Csrf::hiddenField('module:circulum') : '';
	}
}
if (!function_exists('circulum_editor_account_columns')) {
	/** Column names of the accounts table, in table order. */
	function circulum_editor_account_columns(): array
	{
		return array_column(
			\Lotgd\MySQL\Database::getDoctrineConnection()->fetchAllAssociative("DESCRIBE " . \Lotgd\MySQL\Database::prefix("accounts")),
			'Field'
		);
	}
}
page_header("Circulum Vitae Editor");
require_once("lib/superusernav.php");
superusernav();
addnav("Circulum Vitae Editor");
addnav("Circulum Vitae Editor Main","runmodule.php?module=circulum&op=editor");
addnav("Operations");
require_once("./modules/circulum/func/circulum_nochange.php");
switch ($mode) {
	case "savefields":
		if (!circulum_editor_validpost()) {
			output("`\$The form has expired. Nothing was saved.`0");
			break;
		}
		// The column names of the accounts table (the mysqli field functions do not work on the core's DBAL results).
		$fields=array();
		foreach (circulum_editor_account_columns() as $column) {
			$fields[$column]=httppost($column);
		}
		circulum_save_account_nochanges($fields);
		output("`%Saved!");
		break;
	case "editreset":
		$set=circulum_get_account_nochanges();
		require_once('lib/showform.php');
		$fields=array("justafield"=>"Name of the field - You want to keep it?,note");
		foreach (circulum_editor_account_columns() as $column) {
			$fields[$column]=$column.",bool";
		}
		output("Select the fields you want to keep in your accounts table. The rest will be set to a default value.");
		output_notl("`n`n");
		output("Yet be aware that you cannot reset: acctid,email,password,login... if you do, the account is thrash as the user cannot login - has no userprefs - has no password etc. It will be displayed an saved, but no action is taken.");
		//I can't really use showform else -_- bad thing. Or I don't know a way around.
		output_notl("`n`n");
		output("\"Yes\" means that you want to keep this field. \"No\" will reset the field.");
		rawoutput("<form action='runmodule.php?module=circulum&op=editor&mode=savefields' method='POST'>".circulum_editor_tokenfield());
		$info = showform($fields,$set);
		rawoutput("</form>");
		addnav("","runmodule.php?module=circulum&op=editor&mode=savefields");	
		break;
	case "titleeditor": 
		//mainly copy+paste from titleedit.php
		$id = httpget('id');
		$editarray=array(
			"Titles,title",
			"titleid"=>"# of Circuli Vitae,viewonly",
			"male"=>"Male Title,text|",
			"female"=>"Female Title,text|",
			);
		$title=httpget('title');
		if (($title=="save" || $title=="delete") && !circulum_editor_validpost()) {
			output("`\$The form has expired. Nothing was changed.`0");
			$title = "";
		} elseif ($title=="save") {
			$titleid=httppost('titleid');
			$male = httppost('male');
			$female = httppost('female');
			if ((int)$id == 0) {
				if (circulum_get_title($titleid,"male")) {
					$note="`^Title already exists. Nothing saved, choose a number that is not occupied`0";
					} else {
					circulum_set_title($titleid,$male,$female);
					$note = "`^New title added.`0";
					}
				}else {
					circulum_set_title($titleid,$male,$female);
					$note = "`^Title modified.`0";
				}
				output($note);
				$title = "";
		} elseif ($title=="delete") {
			\Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement(
				"DELETE FROM ".\Lotgd\MySQL\Database::prefix("module_objprefs")." WHERE modulename='circulum' AND objtype='circulum_title' AND objid = :id",
				['id' => (int) $id]
			);
			output("`^Title deleted.`0");
			$title = "";
		}
		switch ($title) {
			case"add":case "edit":
			require_once("lib/showform.php");
			if ($title=="edit"){
				$male=circulum_get_title($id,"male");
				$female=circulum_get_title($id,"female");
				$row = array('titleid'=>$id, 'male'=>$male, 'female'=>$female);
			} elseif ($title=="add") {
				$row = array('titleid'=>1, 'male'=>'', 'female'=>'');
				$editarray['titleid']="# of Circuli Vitae,int|1";
				$id = 0;
			}
			rawoutput("<form action='runmodule.php?module=circulum&op=editor&mode=titleeditor&title=save&id=$id' method='POST'>".circulum_editor_tokenfield());
			addnav("","runmodule.php?module=circulum&op=editor&mode=titleeditor&title=save&id=$id");
			if ($title=='edit') rawoutput("<input type='hidden' name='titleid' value='$id'>");
			showform($editarray,$row);
			rawoutput("</form>");
			circulum_title_help();
			break;	
		default:
			output("`@`c`b-=Title Editor=-`b`c");
			$ops = translate_inline("Ops");
			$dks = translate_inline("# of Circuli Vitae");
			$mtit = translate_inline("Male Title");
			$ftit = translate_inline("Female Title");
			$edit = translate_inline("Edit");
			$del = translate_inline("Delete");
			$delconfirm = translate_inline("Are you sure you wish to delete this title?");
			rawoutput("<table border=0 cellspacing=0 cellpadding=2 width='100%' align='center'>");
			rawoutput("<tr class='trhead'><td>$ops</td><td>$dks</td><td>$mtit</td><td>$ftit</td></tr>");
			$titlearray=circulum_get_arraytitle();
			$k=0;
			foreach ($titlearray as $key=>$i) {
				$k=!$k;
				rawoutput("<tr class='".($k?"trlight":"trdark")."'>");
				rawoutput("<td>[<a href='runmodule.php?module=circulum&op=editor&mode=titleeditor&title=edit&id=$i'>$edit</a>|<form action='runmodule.php?module=circulum&op=editor&mode=titleeditor&title=delete&id=$i' method='POST' style='display:inline' onSubmit='return confirm(\"$delconfirm\");'>".circulum_editor_tokenfield()."<input type='submit' class='button' value='$del'></form>]</td>");
				addnav("","runmodule.php?module=circulum&op=editor&mode=titleeditor&title=edit&id=$i");
				addnav("","runmodule.php?module=circulum&op=editor&mode=titleeditor&title=delete&id=$i");
				rawoutput("<td>");
				output_notl("`&%s`0",$i);
				rawoutput("</td><td>");
				output_notl("`2%s`0",circulum_get_title($i,"male"));
				rawoutput("</td><td>");
				output_notl("`6%s`0",circulum_get_title($i,"female"));
				rawoutput("</td></tr>");
			}
			rawoutput("</table>");
			addnav("Functions");
			addnav("Add a Title", "runmodule.php?module=circulum&op=editor&mode=titleeditor&title=add");
			circulum_title_help();
		break;
		}	
			addnav("Operations");
			addnav("CV Titleeditor","runmodule.php?module=circulum&op=editor&mode=titleeditor");
			break;
	default:
		output("`b`cWelcome to the CV Editor.`b`c");
		output_notl("`n`n");
		addnav("CV Reseteditor","runmodule.php?module=circulum&op=editor&mode=editreset");
		addnav("CV Titleeditor","runmodule.php?module=circulum&op=editor&mode=titleeditor");
		break;
}
page_footer();
?>
