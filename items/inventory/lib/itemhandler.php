<?php
// Itemhandler by Christian Rutsch (c) 2005

mydefine("HOOK_NEWDAY", 1);
mydefine("HOOK_FOREST", 2);
mydefine("HOOK_VILLAGE", 4);
mydefine("HOOK_SHADES", 8);
mydefine("HOOK_FIGHTNAV", 16);
mydefine("HOOK_TRAIN", 32);
mydefine("HOOK_INVENTORY", 64);
//mydefine("HOOK_DRAGONKILL", 64);

function get_buff($buffid) {
	$sql = "SELECT * FROM ".db_prefix("itembuffs")." WHERE buffid = $buffid";
	$result = db_query_cached($sql, "inventory-buff-$buffid", 525600);
	$buff = db_fetch_assoc($result);
	if (db_num_rows($result)<1) return array();
	// Here we'll sanitize the buff a little, so there are no values in it
	// which will actually cause output but which don't have an effect
	$newbuff = array();
	if ($buff['atkmod'] != "0" && $buff['atkmod'] != "1" && $buff['atkmod'] != "") $new_buff['atkmod'] = $buff['atkmod'];
	if ($buff['defmod'] != "0" && $buff['defmod'] != "1" && $buff['defmod'] != "") $new_buff['defmod'] = $buff['defmod'];
	if ($buff['dmgmod'] != "0" && $buff['dmgmod'] != "1" && $buff['dmgmod'] != "") $new_buff['dmgmod'] = $buff['dmgmod'];
	if ($buff['badguyatkmod'] != "0" && $buff['badguyatkmod'] != "1" && $buff['badguyatkmod'] != "") $new_buff['badguyatkmod'] = $buff['badguyatkmod'];
	if ($buff['badguydefmod'] != "0" && $buff['badguydefmod'] != "1" && $buff['badguydefmod'] != "") $new_buff['badguydefmod'] = $buff['badguydefmod'];
	if ($buff['badguydmgmod'] != "0" && $buff['badguydmgmod'] != "1" && $buff['badguydmgmod'] != "") $new_buff['badguydmgmod'] = $buff['badguydmgmod'];
	if ($buff['invulnerable'] == "1") $new_buff['invulnerable'] = 1;
	if ($buff['dmgshield'] != "0" && $buff['dmgshield'] != "") $new_buff['dmgshield'] = $buff['dmgshield'];
	if ($buff['regen'] != "0" && $buff['regen'] != "") $new_buff['regen'] = $buff['regen'];
	if ($buff['lifetap'] != "0" && $buff['lifetap'] != "") $new_buff['lifetap'] = $buff['lifetap'];
	if ($buff['minioncount'] != "0" && $buff['minioncount'] != "") {
		$new_buff['minioncount'] = $buff['minioncount'];
		$new_buff['maxbadguydamage'] = $buff['maxbadguydamage'];
		$new_buff['minbadguydamage'] = $buff['minbadguydamage'];
		$new_buff['maxgoodguydamage'] = $buff['maxgoodguydamage'];
		$new_buff['mingoodguydamage'] = $buff['mingoodguydamage'];
	}
	$new_buff['rounds'] = $buff['rounds'];
	$new_buff['startmsg'] = $buff['startmsg'];
	$new_buff['roundmsg'] = $buff['roundmsg'];
	$new_buff['wearoff'] = $buff['wearoff'];
	$new_buff['effectfailmsg'] = $buff['effectfailmsg'];
	$new_buff['effectnodmgmsg'] = $buff['effectnodmgmsg'];
	$new_buff['effectmsg'] = $buff['effectmsg'];
	$new_buff['allowinpvp'] = $buff['allowinpvp'];
	$new_buff['allowintrain'] = $buff['allowintrain'];
	$new_buff['survivenewday'] = $buff['survivenewday'];
	$new_buff['startmsg'] = $buff['startmsg'];
	$new_buff['wearoff'] = $buff['wearoff'];

	if ($buff['buffshortname'] == "") $new_buff['name'] = $item['name'];
	else $new_buff['name'] = $buff['buffshortname'];

	foreach ($new_buff as $property=>$value)
		$new_buff[$property] = preg_replace("/\\n/", "", $value);

	return $new_buff;
}

function display_item_fightnav($result, $args) {
	$script= $args['script'];
	if (db_num_rows($result) > 0) {
		addnav("Items");
		for ($i=0;$i<db_num_rows($result);$i++){
			$item = db_fetch_assoc($result);
			$qty = check_qty((int)$item['itemid']);
			if ($qty == 0) continue;
			if ($item['link'] <> "")
				$linkentry = $item['link'];
			else
				$linkentry = "|";
			$link = list($lname, $llink) = explode("|", $linkentry, 2);
			if ($lname == "") $lname = $item['name'];
			if ($llink == "") $llink = $script."op=fight&skill=ITEM&l=".$item['itemid'];
			if ($item['charges']>0)
				addnav(array("%s `7(%s) %s charges left`0", $lname, $qty,$item['leftcharges']), $llink);
			else
				addnav(array("%s `7(%s)`0", $lname, $qty), $llink);
		}
	}
	return $args;
}

function display_item_nav($hookname, $return = false) {
	global $session;
	if ($hookname_override = httpget("hookname")) {
		$hookname = $hookname_override;
	}
	$constant = constant("HOOK_" . strtoupper($hookname));
	$item = db_prefix("item");
	$inventory = db_prefix("inventory");
	$acctid = $session['user']['acctid'];
	$sql = "SELECT $item.*,inv.quantity 
		FROM $item
		INNER JOIN (SELECT itemid,
				SUM(if ($inventory.charges > 1, $inventory.charges, 1)) AS quantity
				FROM $inventory 
				WHERE $inventory.userid = $acctid
				GROUP BY $inventory.itemid) AS inv ON
		item.itemid = inv.itemid
		WHERE ($item.activationhook & $constant)";
	$result = db_query($sql);

	if (db_num_rows($result) > 0) {
		addnav("Items");
		if ($return === false) {
			$return = URLencode($_SERVER['REQUEST_URI']);
		} else {
			$return = URLencode($return);
			$return .= "&returnhandle=1&hookname=$hookname";
		}
		for ($i=0;$i<db_num_rows($result);$i++){
			$item = db_fetch_assoc($result);
			if ((int)$item['itemid'] == 0 || $item['quantity'] == 0) continue;
			if ($item['link'] <> "")
				$linkentry = $item['link'];
			else
				$linkentry = "|";
			list($lname, $llink) = explode("|", $linkentry, 2);
			if ($lname == "") $lname = $item['name'];
			if ($llink == "") $llink = "runmodule.php?module=inventory&op=activate&id=".$item['itemid'];
			$llink .= "&return=".$return;
			if ($item['charges']>0)
				addnav(array("%s `7(%s) %s charges left`0", $lname, $item['quantity'],$item['charges']), $llink);
			else
				addnav(array("%s `7(%s)`0", $lname, $item['quantity']), $llink);
		}
	}
}

function run_newday_buffs($result) {
	require_once("lib/buffs.php");

	if (db_num_rows($result) > 0) {
		for ($i=0; $i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			if (check_qty($row['itemid']) == 0) continue;
			$buff = get_buff((int)$row['buffid']);
			apply_buff($row['name'], $buff);
			remove_item($row['itemid']);
		}
	}
}

function get_item_by_name($itemname) {
	$sql = "SELECT * FROM ".db_prefix("item")." WHERE name = '$itemname' LIMIT 1";
	$result = db_query_cached($sql, "item-name-$itemname");
	$item = db_fetch_assoc($result);
	if (db_num_rows($result) == 0)
		return false;
	else
		return $item;
}

function get_item_by_id($itemid) {
	$sql = "SELECT * FROM ".db_prefix("item")." WHERE itemid = $itemid LIMIT 1";
	$result = db_query_cached($sql, "item-id-$itemid");
	$item = db_fetch_assoc($result);
	if (db_num_rows($result) == 0)
		return false;
	else
		return $item;
}

function get_item($item){
	if(!is_numeric($item))
		return get_item_by_name($item);
	else
		return get_item_by_id($item);
}

function get_random_item($class = false) {
	$chance = e_rand(0,100);
	if ($class === false) {
		$sql = "SELECT * FROM ".db_prefix("item")." WHERE $chance <= findchance ORDER BY rand(".e_rand().") LIMIT 1";
	} else {
		$classes=explode(",",$class);
		if (is_array($classes) && count($classes)>1) {
			//more categories! =)
			$ins="";
			foreach ($classes as $class) {
				$ins.=" OR class = '$class' ";
			}

			$sql = "SELECT * FROM ".db_prefix("item")." WHERE (0 $ins) AND $chance <= findchance ORDER BY rand(".e_rand().") LIMIT 1";

		} else {
			$sql = "SELECT * FROM ".db_prefix("item")." WHERE class = '$class' AND $chance <= findchance ORDER BY rand(".e_rand().") LIMIT 1";
		}
	}

	$result = db_query($sql);
	if (db_num_rows($result) == 1) $item = db_fetch_assoc($result);
	else $item = false;
	return $item;
}

function add_item_by_name($itemname, $qty=1, $user=0, $specialvalue="", $sellvaluegold=false, $sellvaluegems=false) {
	$item = get_item_by_name($itemname);
	return add_item_by_id((int)$item['itemid'], $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems);
}

function add_item_by_id($itemid, $qty=1, $user=0, $specialvalue="", $sellvaluegold=false, $sellvaluegems=false, $charges=false) {
	global $session;
	if ($qty < 1) return false;
	if ($user === 0) $user = $session['user']['acctid'];
	$inventory = db_prefix("inventory");
	$item = db_prefix("item");
	$sql = "SELECT COUNT($inventory.itemid) AS totalcount, $item.weight AS totalweight
		FROM $inventory
		INNER JOIN $item ON $item.itemid = $inventory.itemid
		WHERE $inventory.userid = $user
		GROUP BY $inventory.itemid,$item.weight";
	$result = db_query($sql);
	$totalcount = 0;
	$totalweight = 0;
	while($row=db_fetch_assoc($result)){
		$totalcount += $row['totalcount'];
		$totalweight += $row['totalweight'] * $row['totalcount'];
	}
	$inv = db_fetch_assoc($result);
	$maxcount = get_module_setting("limit", "inventory");
	$maxweight = get_module_setting("weight", "inventory");
	if ($maxcount != 0 && $totalcount >= $maxcount) {
		debug("Too many items, will not add this one!");
		return false;
	} else if ($maxweight && $totalweight >= $maxweight) {
		debug("Items are too heavy. Item hasn't been added!");
		return false;
	} else {
		$sql = "SELECT gold, gems, charges, uniqueforserver, uniqueforplayer FROM $item WHERE itemid = $itemid";
		$result = db_query($sql);
		$item_raw = db_fetch_assoc($result);

		if ($sellvaluegold === false) $sellvaluegold = round($item_raw['gold'] * (get_module_setting("sellgold", "inventory")/100));
		if ($sellvaluegems === false) $sellvaluegems = round($item_raw['gems'] * (get_module_setting("sellgems", "inventory")/100));
		if ($charges === false) $charges = $item_raw['charges'];
		$charges = (int) $charges; //needs to be integer for the insert
		if (isset($item_raw['uniqueforserver']) && $item_raw['uniqueforserver']) {
			$sql = "SELECT * FROM $inventory WHERE itemid = $itemid LIMIT 2";
			$result = db_query($sql);
			if (db_num_rows($result) > 0) {
				debug("UNIQUE item has not been added because already someone else owns this!");
				return false;
			}
		}
		if (isset($item_raw['uniqueforplayer']) && $item_raw['uniqueforplayer']) {
			$sql = "SELECT * FROM $inventory WHERE itemid = $itemid AND userid = $user LIMIT 2";
			$result = db_query($sql);
			if (db_num_rows($result) > 0) {
				debug("UNIQUEFORPLAYER item has not been added because this player already owns this item!");
				return false;
			}
		}
		$sql = "INSERT INTO $inventory (`userid`, `itemid`, `sellvaluegold`, `sellvaluegems`, `specialvalue`, `charges`, `equipped` ) VALUES ";
		for ($i=0;$i<$qty;$i++) {
			if($i)
				$sql .= ",";
			$sql .= "($user, $itemid, $sellvaluegold, $sellvaluegems, '$specialvalue', $charges,0)";
		}
		db_query($sql);
		debuglog("has gained $qty items (ID: $itemid).");
		invalidatedatacache("inventory-user-$user");
		return true;
	}
}

function add_item($item, $qty=1, $user=0, $specialvalue="", $sellvaluegold=false, $sellvaluegems=false) {
	if(!is_int($item))
		return add_item_by_name($item, $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems);
	else
		return add_item_by_id($item, $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems);
}


function get_inventory($user=0, $showhide=false, $class=0) {
	global $session;

	if ($user === 0) $user = $session['user']['acctid'];
	$showhide = (int)$showhide;
	$inventory = db_prefix("inventory");
	$item = db_prefix("item");
	if ($class === 0)
		$classwhere = "";
	else
		$classwhere = "AND $item.class = '$class'";
	$sql = "SELECT $item.*, inv.quantity, inv.charges, inv.sellvaluegold, inv.sellvaluegems FROM $item INNER JOIN (
			SELECT itemid, COUNT($inventory.itemid) AS quantity, SUM($inventory.charges) AS charges, $inventory.sellvaluegold AS sellvaluegold, $inventory.sellvaluegems AS sellvaluegems
			FROM $inventory
			WHERE $inventory.userid = $user
			GROUP BY $inventory.itemid,$inventory.sellvaluegold,$inventory.sellvaluegems ) AS inv
		ON $item.itemid = inv.itemid
		WHERE $item.hide = $showhide $classwhere
		ORDER BY
		$item.class ASC,
		$item.name ASC";
	if ($class === 0)
		$result = db_query_cached($sql, "inventory-user-$user");
	else
		$result = db_query($sql);
	return $result;
}

function get_inventory_item($itemid, $user = 0) {
	global $session;
	if (!is_int($itemid)) {
		$itemid = get_item_by_name($itemid);
		$itemid = $itemid['itemid'];
	}
	$itemid = (int)$itemid;
	if ($user === 0) $user = $session['user']['acctid'];
	$inventory = db_prefix("inventory");
	$item = db_prefix("item");
	$sql = "SELECT $item.*, inv.quantity, inv.charges, inv.sellvaluegold, inv.sellvaluegems FROM $item INNER JOIN (
			SELECT itemid, COUNT($inventory.itemid) AS quantity, SUM($inventory.charges) AS charges, $inventory.sellvaluegold AS sellvaluegold, $inventory.sellvaluegems AS sellvaluegems
			FROM $inventory
			WHERE $inventory.userid = $user
			GROUP BY $inventory.itemid,$inventory.sellvaluegold,$inventory.sellvaluegems ) AS inv
		ON $item.itemid = inv.itemid
		WHERE $item.itemid = $itemid
		ORDER BY
		$item.class ASC,
		$item.name ASC";
	$result = db_query($sql);
	$item = db_fetch_assoc($result);
	return $item;
}

function uncharge_item($itemid, $user=0) {
	global $session;
	if (!is_int($itemid)) {
		$itemid = get_item_by_name($itemid);
		$itemid = $itemid['itemid'];
	}
	$itemid = (int)$itemid;
	if ($user === 0) $user = $session['user']['acctid'];
	$inventory = db_prefix("inventory");
	$sql = "UPDATE $inventory SET charges = charges - 1 WHERE itemid = $itemid AND userid = $user AND charges >= 1 LIMIT 1";
	$result = db_query($sql);
	if (db_affected_rows($result) == 0)
		debug("ERROR: Tried to uncharge item although no charges present!");
	else
		debuglog("uncharged ".db_affected_rows($result)." items (ID: $itemid)", $user);
	$sql = "DELETE FROM $inventory WHERE itemid = $itemid AND userid = $user AND charges = 0";
	$result = db_query($sql);
	$count = db_affected_rows($result);
	if ($count) debuglog("uncharged and deleted $count items (ID: $itemid)", $user);
	invalidatedatacache("inventory-user-$user");
}

function recharge_item($itemid, $user=0) {
	global $session;
	if (!is_int($itemid)) {
		$itemid = get_item_by_name($itemid);
		$itemid = $itemid['itemid'];
	}
	$itemid = (int)$itemid;
	if ($user === 0) $user = $session['user']['acctid'];
	$inventory = db_prefix("inventory");
	$sql = "UPDATE $inventory SET charges = charges 1 1 WHERE itemid = $itemid AND userid = $user LIMIT 1";
	$result = db_query($sql);
	if (db_affected_rows($result) == 0)
		debug("ERROR: Tried to recharge non-present item!");
	else
		debuglog("recharged ".db_affected_rows($result)." items (ID: $itemid)", $user);
	invalidatedatacache("inventory-user-$user");
}


function show_inventory($user = 0) {
	global $session;

	$login = httpget('login');
	if ($user === 0) $user = $session['user']['acctid'];
	$inventory = get_inventory($user);
	$count = db_num_rows($inventory);
	tlschema("inventory");
	$name = translate_inline("Name");
	$class = translate_inline("Category");
	$description = translate_inline("Description");
	$goldvalue = translate_inline("Goldvalue");
	$gemvalue = translate_inline("Gemvalue");
	$quantity = translate_inline("Quantity");
	$options = translate_inline("Options");
	$drop = translate_inline("Drop this once");
	$sql = "SELECT name FROM ".db_prefix("accounts")." WHERE acctid=$user";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);

	rawoutput("<table border=0 cellpadding=2 cellspacing=2 align=center>");
	rawoutput("<tr class='trhead'><td colspan=6>");
	output("`c`b`^%s`& is carrying these items:`b`c", $row['name']);
	$ret=URLEncode($_SERVER['REQUEST_URI']);
	rawoutput("</td></tr>");
	$countweight=0;
	$itemcounter=0;
	if ($count) {
		for ($i=0;$i<$count;$i++) {
			$item = db_fetch_assoc($inventory);
			$countweight += $item['weight'] * $item['quantity'];
			$itemcounter += $item['quantity'];
			rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td>");
			output("`&%s`0", translate_inline($item['name']));
			rawoutput("</td><td>");
			output("`&`i%s`i`0", translate_inline($item['class']));
			rawoutput("</td><td align='right'>");
			output("`&%s `2pcs`0", $item['quantity']);
			rawoutput("</td><td align='right'>");
			if ($user == $session['user']['acctid'])
				output("`&%s `^gold pieces`0", number_format($item['sellvaluegold']));
			else
				output_notl("&nbsp;",true);
			rawoutput("</td><td align='right'>");
			if ($user == $session['user']['acctid'])
				output("`&%s `%gems`0  ", number_format($item['sellvaluegems']));
			else
				output_notl("&nbsp;",true);
			rawoutput("</td><td align='center'>");
			if(($user == $session['user']['acctid'] && $item['droppable'] && get_module_setting("droppable", "inventory")) || ($session['user']['superuser'] & SU_EDIT_USERS)) {
				rawoutput("[&nbsp;<a href='runmodule.php?module=inventory&login=$login&user=$user&op=dropitem&id=".$item['itemid']."&return=$ret'>$drop</a>&nbsp;]");
				addnav("", "runmodule.php?module=inventory&login=$login&user=$user&op=dropitem&id=".$item['itemid']."&return=$ret");
			}
			rawoutput("</td></tr><tr class='".($i%2?"trlight":"trdark")."'><td colspan=6");
			output("`7`i%s`i`0", translate_inline($item['description']));
			rawoutput("</td></tr>");
		}
		$limit = get_module_setting("limit", "inventory");
		$weight = get_module_setting("weight", "inventory");
		if ($user == $session['user']['acctid']) {
			if ($limit) {
				rawoutput("<tr><td colspan=6>");
				output("`n`cYou are currently carrying `^%s`0 / `^%s`0 items.`c", $itemcounter, $limit);
			}
			if ($weight) {
				rawoutput("<tr><td colspan=6>");
				output("`n`cYour items have a total weight of `^%s`0. You must not carry more than `^%s`0.`c", $countweight, $weight);
			}
		}
	} else {
		output("<tr><td colspan=6>`n`c`iThis player does not have any items.`i`c</td></tr>", true);
	}
	rawoutput("</table>");
	tlschema();
}

function check_qty_by_id($itemid, $user = 0) {
	global $session;
	if ($user === 0) $user = $session['user']['acctid'];
	$inventory = db_prefix("inventory");
	$sql = "SELECT COUNT(itemid) AS qty FROM $inventory WHERE userid = $user AND itemid = $itemid";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);
	return $row['qty'];
}

function check_qty_by_name($itemname, $user = 0) {
	$item = get_item_by_name($itemname);
	return check_qty_by_id((int)$item['itemid'], $user);
}

function check_qty($item, $user=0) {
	if(!is_int($item))
		return check_qty_by_name($item, $user);
	else
		return check_qty_by_id($item, $user);
}

function remove_item_by_id($item, $qty=1, $user=0) {
	global $session;

	if ($user === 0) $user = $session['user']['acctid'];

	$inventory = db_prefix("inventory");

	$sql = "DELETE FROM $inventory WHERE userid = $user AND itemid = $item LIMIT $qty";
	debuglog("removed item $item from inventory", $user);
	$result = db_query($sql);
	invalidatedatacache("inventory-user-$user");
	invalidatedatacache("inventory-item-$item-$user");
	return db_affected_rows($result);
}

function remove_item_by_name($itemname, $qty=1, $user=0) {
	$row = get_item_by_name($itemname);
	return remove_item_by_id((int)$row['itemid'], $qty, $user);
}

function remove_item($item, $qty=1, $user=0) {
	if (!is_int($item))
		return remove_item_by_name($item, $qty, $user);
	else
		return remove_item_by_id($item, $qty, $user);
}

function shopnav($return, $class, $sell=false, $user=0, $sellall=false, $showdescription=true) {
	global $session;

	if (substr($return,strlen($return)-1,1) != "&") $return .= "&";

	$inventory = db_prefix("inventory");
	$item = db_prefix("item");
	if ($sell === false) {
		$sql = "SELECT *
			FROM $item
			WHERE $item.class = '$class'
			AND $item.buyable <> 0
			ORDER BY
			$item.class ASC,
			$item.name ASC";
	} else {
		if ($user === 0) $user = $session['user']['acctid'];
		$sql = "SELECT $item.*, inv.quantity, inv.charges, inv.sellvaluegold, inv.sellvaluegems FROM $item INNER JOIN (
				SELECT itemid, COUNT($inventory.itemid) AS quantity, SUM($inventory.charges) AS charges, $inventory.sellvaluegold AS sellvaluegold, $inventory.sellvaluegems AS sellvaluegems
				FROM $inventory
				WHERE $inventory.userid = $user
				AND $item.class = '$class'
				AND $item.sellable <> 0
				GROUP BY $inventory.itemid,$inventory.sellvaluegold,$inventory.sellvaluegems ) AS inv
			ON $item.itemid = inv.itemid
			WHERE $item.itemid = $itemid
			ORDER BY
			$item.class ASC,
			$item.name ASC";
	}
	$result = db_query($sql);
	if (db_num_rows($result) > 0) {
		tlschema('inventory');
		$class = translate_inline($class);
		if ($sellall == true && db_num_rows($result) > 0) {
			addnav(array("Sell all %s", $class));
			addnav("Sell all", $return."id=all");
		}
		$sell?addnav(array("Sell %s", $class)):addnav(array("Buy %s", $class));
		while($row = db_fetch_assoc($result)) {
			if ($sell === false) {
				if ($session['user']['gold'] >= $row['gold'] && $session['user']['gems'] >= $row['gems'])
					addnav(array("%s`n`^%s`0 Gold, `%%%s`0 Gems", translate_inline($row['name']), $row['gold'], $row['gems']), $return."id=".$row['itemid']);
				else
					addnav(array("%s`n`^%s`0 Gold, `%%%s`0 Gems", translate_inline($row['name']), $row['gold'], $row['gems']), "");
				if ($showdescription == true) {
					output("`#%s ", translate_inline($row['name']));
					$qty = check_qty((int)$row['itemid']);
					if ($qty > 0) output("`7- You own `^%s pieces", $qty);
					$description = translate_inline($row['description']);
					output("`n`7%s`n`n", $description);
				}
			} else {
				addnav(array("%s`n`^%s`0 Gold, `%%%s`0 Gems`n(`2%s Stück`0)", translate_inline($row['name']), $row['sellvaluegold'], $row['sellvaluegems'], $row['quantity']), $return."id=".$row['itemid']);
			}
		}
		tlschema();
	}
// $injection: An array containing information about the new item.
//					Unset values will be replaced by their defaults (via MySQL table definition.)
// true  - if the item was inserted
// false - if the item was updated
}
// This function updates an existing item or inserts a new item into
// the item table. Depending on the item name it will choose to either
// update all changed fields or insert this as a new item.
// Params:
// $injection: An array containing ininformation about the new item.
//					Unset values will be replaced by their defaults (via mysql
//					table definition.
// $exclude:	An array which contains the fields to be excluded from a
//					potential update procedure. Useful, if fields contain
//					semi-automatically generated values (e.g. links with ids)
// Return values:
// true			If the the item got inserted.
// false			If the the item got updated.

function inject_item($injection, $exclude=false) {
	// Borrowed basic idea from lotgd code. lib/http.php -> function: postparse();
	$sql = ""; $keys = ""; $vals = ""; $i = 0;

	$item = db_prefix("item");
	$test = get_item($injection['itemid']);
	debug($test);
	if (is_array($test)) {
		$update = array_diff_assoc($injection, $test);
		unset($update['itemid']);
		reset($update);
		if (is_array($exclude)) {
			foreach($exclude as $excl) {
				if (isset($update[$excl])) unset($update[$excl]);
			}
		}
		foreach($update as $key=>$val) {
			$sql .= (($i > 0) ? "," : "") . "$key='$val'";
			$keys .= (($i > 0) ? "," : "") . "$key";
			$vals .= (($i > 0) ? "," : "") . "'$val'";
			$i++;
		}
		if ($sql) {
			$sql = "UPDATE $item SET $sql WHERE itemid = {$test['itemid']}";
			db_query($sql);
			invalidatedatacache("item-name-".$injection['name']);
			invalidatedatacache("item-id-".$test['itemid']);
			debug("Updated Item '".$injection['name']."'. SQL = '$sql'");
		} else {
			debug("Nothing to update for '".$injection['name']."'");
		}
		return false;
	} else {
		reset($injection);
		foreach($injection as $key=>$val) {
			$keys .= (($i > 0) ? "," : "") . "$key";
			$vals .= (($i > 0) ? "," : "") . "'$val'";
			$i++;
		}
		$sql = "INSERT INTO $item ($keys) VALUES ($vals)";
		db_query($sql);
		debug("Inserted Item '".$injection['name']."'");
		return true;
	}
	return true;
}
?>
