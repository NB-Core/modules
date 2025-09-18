<?php

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

        global $session;
        require_once("lib/sanitize.php");
        define("OVERRIDE_FORCED_NAV", true);
        $item = db_prefix("item");
        $inventory = db_prefix("inventory");
        $conn = Database::getDoctrineConnection();
        $op2 = httpget('op2');
        $id = httpget('id');
        switch($op2) {
                case "show":
                        $sql = "SELECT {$item}.name, {$item}.description FROM {$item} WHERE itemid = :itemid";
                        $result = $conn->executeQuery(
                                $sql,
                                [
                                        'itemid' => (int) $id,
                                ],
                                [
                                        'itemid' => ParameterType::INTEGER,
                                ]
                        );
                        $row = $result->fetchAssociative();
                        if ($row) {
                                output("`vDescription of %s`v:`n",$row['name']);
                                output_notl("%s`0`n`n",$row['description']);
                        }
                        break;
                case "equip":
                        $thing = get_item((int)$id);
                        $sql = "SELECT {$inventory}.itemid FROM {$inventory} INNER JOIN {$item} ON {$inventory}.itemid = {$item}.itemid WHERE {$item}.equipwhere = :equipwhere AND {$inventory}.equipped = 1";
                        $result = $conn->executeQuery(
                                $sql,
                                [
                                        'equipwhere' => $thing['equipwhere'],
                                ],
                                [
                                        'equipwhere' => ParameterType::STRING,
                                ]
                        );
                        $wh = [];
                        while ($row = $result->fetchAssociative()) {
                                $wh[] = (int) $row['itemid'];
                        }
                        if ($wh) {
                                modulehook("unequip-item", array("ids"=>$wh));
                                $sql = "UPDATE {$inventory} SET equipped = 0 WHERE itemid IN (:itemids)";
                                $conn->executeStatement(
                                        $sql,
                                        [
                                                'itemids' => $wh,
                                        ],
                                        [
                                                'itemids' => ArrayParameterType::INTEGER,
                                        ]
                                );
                        }
                        modulehook("equip-item", array("id"=>(int)$id));
                        $sql = "UPDATE {$inventory} SET equipped = 1 WHERE itemid = :itemid AND userid = :userid LIMIT 1";
                        $conn->executeStatement(
                                $sql,
                                [
                                        'itemid' => (int) $id,
                                        'userid' => (int) $session['user']['acctid'],
                                ],
                                [
                                        'itemid' => ParameterType::INTEGER,
                                        'userid' => ParameterType::INTEGER,
                                ]
                        );
                        break;
                case "unequip":
                        modulehook("unequip-item", array("ids"=>array((int) $id)));
                        $sql = "UPDATE {$inventory} SET equipped = 0 WHERE itemid = :itemid AND userid = :userid";
                        $conn->executeStatement(
                                $sql,
                                [
                                        'itemid' => (int) $id,
                                        'userid' => (int) $session['user']['acctid'],
                                ],
                                [
                                        'itemid' => ParameterType::INTEGER,
                                        'userid' => ParameterType::INTEGER,
                                ]
                        );
                        break;
                case "activate":
                        $id = httpget('id');
                        $acitem = get_inventory_item((int)$id);
                        require_once("lib/buffs.php");
                        apply_buff($acitem['name'], get_buff($acitem['buffid']));
                        if ($acitem['charges'] > 1) uncharge_item((int)$id, 1);
                        else remove_item((int)$id);
                        if ($acitem['execvalue'] > "") {
                                if ($acitem['exectext'] > "") {
                                        output($acitem['exectext'], $acitem['name']);
                                } else {
                                        output("You activate %s!", $acitem['name']);
                                }
                                require_once("modules/inventory/lib/itemeffects.php");
                                output_notl("`n`n%s", get_effect($acitem));
                        }
                        break;
        }
	popup_header("Your Inventory ");
	output("You are currently wearing the following items:`n`n");
	$layout = array(
/*		"Weapons,title",
			"righthand",
			"lefthand",
		"Armor,title",
			"head",
			"body",
			"arms",
			"legs",
			"feet",
		"Miscellaneous,title",
			"ring1",
			"ring2",
			"ring3",
			"neck",
			"belt",
*/		"Inventory,title",
			"Unequippables",
	);
	$sql = "SELECT $item.*, MAX($inventory.equipped) AS equipped, COUNT($inventory.equipped) AS quantity FROM $item INNER JOIN $inventory ON $inventory.itemid = $item.itemid WHERE  $inventory.userid = {$session['user']['acctid']} GROUP BY $inventory.itemid ORDER BY $item.equipwhere";
	/*$item.equippable = 0 AND*/
	$result = db_query($sql);
	$inventory = array();
	while($row = db_fetch_assoc($result)) {
		if ($row['equippable'] == false)
			$inventory['Unequippables'][] = $row;
		else
			$inventory[$row['equipwhere']][] = $row;
	}
	inventory_showform($layout, $inventory);
	popup_footer();

function inventory_showform($layout,$row){
	global $session;
 	static $showform_id=0;
 	static $title_id=0;
 	$showform_id++;
 	$formSections = array();
	rawoutput("<table width='100%' cellpadding='0' cellspacing='0'><tr><td>");
	rawoutput("<div id='showFormSection$showform_id'></div>");
	rawoutput("</td></tr><tr><td>&nbsp;</td></tr><tr><td>");
	rawoutput("<table cellpadding='2' cellspacing='0'>");
	$i = 0;
	$wheres = translate(array("righthand"=>"Right Hand","lefthand"=>"Left Hand","head"=>"Your Head","body"=>"Upper Body","arms"=>"Yor Arms","legs"=>"Lower Body","feet"=>"Your Feet","ring1"=>"First Ring","ring2"=>"Second Ring","ring3"=>"Third Ring","neck"=>"Around your Neck","belt"=>"Around your Waist"));
	$equip = translate_inline("Equip");
	$unequip = translate_inline("Unequip");
	$activate = translate_inline("Activate");
	$show = translate_inline("Description");
	while(list($key,$val)=each($layout)){
		if (is_array($val)) {
			$v = $val[0];
			$info = explode(",", $v);
			$val[0] = $info[0];
			$info[0] = $val;
		} else {
			$info = explode(",",$val);
		}
		if (is_array($info[0])) {
			$info[0] = call_user_func_array("sprintf_translate", $info[0]);
		} else {
			$info[0] = translate($info[0]);
		}
		if (isset($info[1])) $info[1] = trim($info[1]);
		else $info[1] = "";

		if ($info[1]=="title"){
		 	$title_id++;
		 	rawoutput("</table>");
		 	$formSections[$title_id] = $info[0];
		 	rawoutput("<table id='showFormTable$title_id' cellpadding='2' cellspacing='0'>");
			rawoutput("<tr><td colspan='2' class='trhead'>",true);
			output_notl("`b%s`b", $info[0], true);
			rawoutput("</td></tr>",true);
			$i=0;
		} else {
			if (isset($row[$val])) {
				$item = $row[$val];
				rawoutput("<tr class='".($i%2?'trlight':'trdark')."'><td valign='top'>");
				output_notl("%s ->", $wheres[$info[0]],true);
				rawoutput("</td><td valign='top'>");
				while(list($itskey, $itsval) = each($item)) {
					output_notl("%s`7%s`7 (%s)", $itsval['equipped']?"`^*":"", $itsval['name'], $itsval['quantity']);
					if ($itsval['equipped'] && $itsval['equippable']) {
						rawoutput("[ <a href='runmodule.php?module=inventory&op=charstat&op2=unequip&id={$itsval['itemid']}'>$unequip</a> ]");
						addnav("", "runmodule.php?module=inventory&op=charstat&op2=unequip&id={$itsval['itemid']}");
					} else if ($itsval['equippable'] == 1) {
						rawoutput("[ <a href='runmodule.php?module=inventory&op=charstat&op2=equip&id={$itsval['itemid']}'>$equip</a> ]");
						addnav("", "runmodule.php?module=inventory&op=charstat&op2=equip&id={$itsval['itemid']}");
					} else if ($itsval['activationhook'] & 64) {
						rawoutput("[ <a href='runmodule.php?module=inventory&op=charstat&op2=activate&id={$itsval['itemid']}'>$activate</a> ]");
						addnav("", "runmodule.php?module=inventory&op=charstat&op2=activate&id={$itsval['itemid']}");
					} else {
						//output("(Gold value: %s, Gem Value: %s)", $itsval['gold'], $itsval['gems']);
					}
					rawoutput("[ <a href='runmodule.php?module=inventory&op=charstat&op2=show&id={$itsval['itemid']}'>$show</a> ]");
                        addnav("", "runmodule.php?module=inventory&op=charstat&op2=show&id={$itsval['itemid']}");
					output_notl("`n");
				}
				$i++;
			}
		}
		rawoutput("</td></tr>",true);
	}
	rawoutput("</table><br>",true);
	if ($showform_id==1){
		$startIndex = (int)httppost("showFormTabIndex");
		if ($startIndex == 0){
			$startIndex = 1;
		}
		if (isset($session['user']['prefs']['tabconfig']) && $session['user']['prefs']['tabconfig'] == 0) {
		} else {
		 	rawoutput("
		 	<script language='JavaScript'>
		 	function prepare_form(id){
		 		var theTable;
		 		var theDivs='';
		 		var x=0;
		 		var weight='';
		 		for (x in formSections[id]){
		 			theTable = document.getElementById('showFormTable'+x);
		 			if (x != $startIndex ){
			 			theTable.style.visibility='hidden';
			 			theTable.style.display='none';
			 			weight='';
			 		}else{
			 			theTable.style.visibility='visible';
			 			theTable.style.display='inline';
			 			weight='color: yellow;';
			 		}
			 		theDivs += \"<div id='showFormButton\"+x+\"' class='trhead' style='\"+weight+\"float: left; cursor: pointer; cursor: hand; padding: 5px; border: 1px solid #000000;' onClick='showFormTabClick(\"+id+\",\"+x+\");'>\"+formSections[id][x]+\"</div>\";
		 		}
		 		theDivs += \"<div style='display: block;'>&nbsp;</div>\";
				theDivs += \"<input type='hidden' name='showFormTabIndex' value='$startIndex' id='showFormTabIndex'>\";
		 		document.getElementById('showFormSection'+id).innerHTML = theDivs;
		 	}
		 	function showFormTabClick(formid,sectionid){
		 		var theTable;
		 		var theButton;
		 		for (x in formSections[formid]){
		 			theTable = document.getElementById('showFormTable'+x);
		 			theButton = document.getElementById('showFormButton'+x);
		 			if (x == sectionid){
		 				theTable.style.visibility='visible';
		 				theTable.style.display='inline';
		 				theButton.style.fontWeight='normal';
		 				theButton.style.color='yellow';
						document.getElementById('showFormTabIndex').value = sectionid;
		 			}else{
		 				theTable.style.visibility='hidden';
		 				theTable.style.display='none';
		 				theButton.style.fontWeight='normal';
		 				theButton.style.color='';
		 			}
		 		}
		 	}
		 	formSections = new Array();
			</script>");
		}
	}
	if (isset($session['user']['prefs']['tabconfig']) && $session['user']['prefs']['tabconfig'] == 0) {
	} else {
		rawoutput("<script language='JavaScript'>");
		rawoutput("formSections[$showform_id] = new Array();");
		reset($formSections);
		while (list($key,$val)=each($formSections)){
			rawoutput("formSections[$showform_id][$key] = '".addslashes($val)."';");
		}
		rawoutput("
		prepare_form($showform_id);
		</script>");
	}
	rawoutput("</td></tr></table>");
}
?>
