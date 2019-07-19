<?php
page_header("Circulum Vitae Editor");
require_once("lib/superusernav.php");
superusernav();
addnav("Circulum Vitae Editor");
addnav("Circulum Vitae Editor Main","runmodule.php?module=circulum&op=editor");
addnav("Operations");
require_once("./modules/circulum/func/circulum_nochange.php");
switch ($mode) {
	case "savefields":
		$sql="SELECT * from ".db_prefix("accounts")." LIMIT 1"; //get one row
		$result=db_query($sql);
		$fields=array();
		for ($i=0;$i<mysql_num_fields($result);$i++) {
			$t=array(mysql_field_name($result,$i)=>httppost(mysql_field_name($result,$i)));
			$fields=array_merge($fields,$t);
		}
		circulum_save_account_nochanges($fields);
		output("`%Saved!");
		break;
	case "editreset":
		$set=circulum_get_account_nochanges();
		require_once('lib/showform.php');
		$sql="SELECT * from ".db_prefix('accounts')." LIMIT 1"; //get one row
		$result=db_query($sql);
		$fields=array("justafield"=>"Name of the field - You want to keep it?,note");
		$numberoffields=mysql_num_fields($result);
		for ($i=0;$i<$numberoffields;$i++) {
			$t=array(mysql_field_name($result,$i)=>mysql_field_name($result,$i).",bool");
			$fields=array_merge($fields,$t);
		}
		output("Select the fields you want to keep in your accounts table. The rest will be set to a default value.");
		output_notl("`n`n");
		output("Yet be aware that you cannot reset: acctid,email,password,login... if you do, the account is thrash as the user cannot login - has no userprefs - has no password etc. It will be displayed an saved, but no action is taken.");
		//I can't really use showform else -_- bad thing. Or I don't know a way around.
		output_notl("`n`n");
		output("\"Yes\" means that you want to keep this field. \"No\" will reset the field.");
		rawoutput("<form action='runmodule.php?module=circulum&op=editor&mode=savefields' method='POST'>");
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
		if ($title=="save") {
			$titleid=httppost('titleid');
			$male = httppost('male');
			$female = httppost('female');
			if ((int)$id == 0) {
				if (circulum_get_title($titleid,"male")) {
					$note="`^Title already exists. Nothing saved, choose a number that is not occupied`0";
					} else {
					circulum_set_title($titleid,$male,$male);
					circulum_set_title($titleid,$male,$female);
					$note = "`^New title added.`0";
					}
				}else {
					circulum_set_title($titleid,$male,$male);
					circulum_set_title($titleid,$male,$female);
					$note = "`^Title modified.`0";
				}
				output($note);
				$title = "";
		} elseif ($title=="delete") {
			$sql = "DELETE FROM ".db_prefix("module_objprefs")." WHERE modulename='circulum' AND objtype='circulum_title' AND objid=$id";
			db_query($sql);
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
			rawoutput("<form action='runmodule.php?module=circulum&op=editor&mode=titleeditor&title=save&id=$id' method='POST'>");
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
			while (list($key,$i) = each ($titlearray)) {
				$k=!$k;
				rawoutput("<tr class='".($k?"trlight":"trdark")."'>");
				rawoutput("<td>[<a href='runmodule.php?module=circulum&op=editor&mode=titleeditor&title=edit&id=$i'>$edit</a>|<a href='runmodule.php?module=circulum&op=editor&mode=titleeditor&title=delete&id=$i' onClick='return confirm(\"$delconfirm\");'>$del</a>]</td>");
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
