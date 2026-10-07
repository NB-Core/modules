<?php

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

// Itemhandler by Christian Rutsch (c) 2005

require_once __DIR__ . '/inventory_legacy_adapter.php';

mydefine("HOOK_NEWDAY", 1);
mydefine("HOOK_FOREST", 2);
mydefine("HOOK_VILLAGE", 4);
mydefine("HOOK_SHADES", 8);
mydefine("HOOK_FIGHTNAV", 16);
mydefine("HOOK_TRAIN", 32);
mydefine("HOOK_INVENTORY", 64);
//mydefine("HOOK_DRAGONKILL", 64);

/**
 * Build a sanitized buff array from an itembuffs row for apply_buff().
 *
 * Input: integer buff id from the module.
 * Output: associative array of buff properties filtered with existing inclusion
 * rules; name falls back to buffshortname, then buffname, then a neutral label.
 */
function get_buff($buffid) {
	$buffid = (int) $buffid;
	$sql = "SELECT * FROM ".db_prefix("itembuffs")." WHERE buffid = $buffid";
	$result = db_query_cached($sql, "inventory-buff-$buffid", 525600);
	$buff = db_fetch_assoc($result);
	if (db_num_rows($result)<1) return array();
	// Here we'll sanitize the buff a little, so there are no values in it
	// which will actually cause output but which don't have an effect
	$newbuff = array();
	if ($buff['atkmod'] != "0" && $buff['atkmod'] != "1" && $buff['atkmod'] != "") $newbuff['atkmod'] = $buff['atkmod'];
	if ($buff['defmod'] != "0" && $buff['defmod'] != "1" && $buff['defmod'] != "") $newbuff['defmod'] = $buff['defmod'];
	if ($buff['dmgmod'] != "0" && $buff['dmgmod'] != "1" && $buff['dmgmod'] != "") $newbuff['dmgmod'] = $buff['dmgmod'];
	if ($buff['badguyatkmod'] != "0" && $buff['badguyatkmod'] != "1" && $buff['badguyatkmod'] != "") $newbuff['badguyatkmod'] = $buff['badguyatkmod'];
	if ($buff['badguydefmod'] != "0" && $buff['badguydefmod'] != "1" && $buff['badguydefmod'] != "") $newbuff['badguydefmod'] = $buff['badguydefmod'];
	if ($buff['badguydmgmod'] != "0" && $buff['badguydmgmod'] != "1" && $buff['badguydmgmod'] != "") $newbuff['badguydmgmod'] = $buff['badguydmgmod'];
	if ($buff['invulnerable'] == "1") $newbuff['invulnerable'] = 1;
	if ($buff['dmgshield'] != "0" && $buff['dmgshield'] != "") $newbuff['dmgshield'] = $buff['dmgshield'];
	if ($buff['regen'] != "0" && $buff['regen'] != "") $newbuff['regen'] = $buff['regen'];
	if ($buff['lifetap'] != "0" && $buff['lifetap'] != "") $newbuff['lifetap'] = $buff['lifetap'];
	if ($buff['minioncount'] != "0" && $buff['minioncount'] != "") {
		$newbuff['minioncount'] = $buff['minioncount'];
		$newbuff['maxbadguydamage'] = $buff['maxbadguydamage'];
		$newbuff['minbadguydamage'] = $buff['minbadguydamage'];
		$newbuff['maxgoodguydamage'] = $buff['maxgoodguydamage'];
		$newbuff['mingoodguydamage'] = $buff['mingoodguydamage'];
	}
	$newbuff['rounds'] = $buff['rounds'];
	$newbuff['startmsg'] = $buff['startmsg'];
	$newbuff['roundmsg'] = $buff['roundmsg'];
	$newbuff['wearoff'] = $buff['wearoff'];
	$newbuff['effectfailmsg'] = $buff['effectfailmsg'];
	$newbuff['effectnodmgmsg'] = $buff['effectnodmgmsg'];
	$newbuff['effectmsg'] = $buff['effectmsg'];
	$newbuff['allowinpvp'] = $buff['allowinpvp'];
	$newbuff['allowintrain'] = $buff['allowintrain'];
	$newbuff['survivenewday'] = $buff['survivenewday'];

	// Avoid undefined-variable access: use stable buff name fields, then neutral fallback.
	if ($buff['buffshortname'] != "") {
		$newbuff['name'] = $buff['buffshortname'];
	} elseif ($buff['buffname'] != "") {
		$newbuff['name'] = $buff['buffname'];
	} else {
		// This label can be visible to players, so keep it localized.
		$newbuff['name'] = translate_inline("Unnamed Buff");
	}

	foreach ($newbuff as $property=>$value)
		$newbuff[$property] = preg_replace("/\\n/", "", $value);

	return $newbuff;
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
        $acctid = (int) $session['user']['acctid'];
        // Compatibility contract: keep navigation text/order identical while
        // routing high-frequency hook reads through repository abstractions.
        $items = inventory_legacy_get_read_repository()->getActivatableItemsForHook($acctid, (int) $constant);

        if ($items) {
                addnav("Items");
                if ($return === false) {
                        $return = URLencode($_SERVER['REQUEST_URI']);
                } else {
                        $return = URLencode($return);
                        $return .= "&returnhandle=1&hookname=$hookname";
                }
                foreach ($items as $row) {
                        if ((int)$row['itemid'] === 0 || (int)$row['quantity'] === 0) {
                                continue;
                        }
                        if ($row['link'] <> "") {
                                $linkentry = $row['link'];
                        } else {
                                $linkentry = "|";
                        }
                        [$lname, $llink] = explode("|", $linkentry, 2);
                        if ($lname == "") $lname = $row['name'];
                        if ($llink == "") $llink = "runmodule.php?module=inventory&op=activate&id=".$row['itemid'];
                        $llink .= "&return=".$return;
                        if ($row['charges']>0)
                                addnav(array("%s `7(%s) %s charges left`0", $lname, $row['quantity'],$row['charges']), $llink);
                        else
                                addnav(array("%s `7(%s)`0", $lname, $row['quantity']), $llink);
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
        return inventory_legacy_get_item_by_name((string) $itemname);
}

function get_item_by_id($itemid) {
        return inventory_legacy_get_item_by_id((int) $itemid);
}

function get_item($item){
	return inventory_legacy_get_item($item);
}

function get_random_item($class = false) {
        $chance = e_rand(0,100);
        $conn = Database::getDoctrineConnection();
        $table = Database::prefix("item");
        $qb = $conn->createQueryBuilder();
        $qb->select('*')
                ->from($table)
                ->where(':chance <= findchance')
                ->setParameter('chance', $chance, ParameterType::INTEGER)
                ->orderBy('RAND()')
                ->setMaxResults(1);
        if ($class !== false) {
                $classes = array_filter(array_map('trim', explode(',', $class)), 'strlen');
                if (count($classes) > 1) {
                        $qb->andWhere($qb->expr()->in('class', ':classes'));
                        $qb->setParameter('classes', $classes, ArrayParameterType::STRING);
                } elseif (count($classes) === 1) {
                        $singleClass = reset($classes);
                        $qb->andWhere('class = :class');
                        $qb->setParameter('class', $singleClass, ParameterType::STRING);
                }
        }

        $result = $qb->executeQuery();
        $item = $result->fetchAssociative();
        if ($item === false) {
                return false;
        }
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
        $conn = Database::getDoctrineConnection();
        $item = Database::prefix("item");
        $capacityStats = inventory_legacy_get_read_repository()->getCapacityStatsForUser((int) $user);
        $totalcount = (int) $capacityStats['totalcount'];
        $totalweight = (int) $capacityStats['totalweight'];
        $maxcount = get_module_setting("limit", "inventory");
        $maxweight = get_module_setting("weight", "inventory");
        if ($maxcount != 0 && $totalcount >= $maxcount) {
                debug("Too many items, will not add this one!");
                return false;
        } else if ($maxweight && $totalweight >= $maxweight) {
                debug("Items are too heavy. Item hasn't been added!");
                return false;
        } else {
                $sql = "SELECT gold, gems, charges, uniqueforserver, uniqueforplayer FROM {$item} WHERE itemid = :itemid";
                $result = $conn->executeQuery(
                        $sql,
                        [
                                'itemid' => (int) $itemid,
                        ],
                        [
                                'itemid' => ParameterType::INTEGER,
                        ]
                );
                $item_raw = $result->fetchAssociative();
                if (!$item_raw) {
                        return false;
                }

                if ($sellvaluegold === false) $sellvaluegold = round($item_raw['gold'] * (get_module_setting("sellgold", "inventory")/100));
                if ($sellvaluegems === false) $sellvaluegems = round($item_raw['gems'] * (get_module_setting("sellgems", "inventory")/100));
                if ($charges === false) $charges = $item_raw['charges'];
                $charges = (int) $charges; //needs to be integer for the insert
                if ((($item_raw['uniqueforserver'] ?? 0) || ($item_raw['uniqueforplayer'] ?? 0)) && (int)$qty > 1) {
                        debug("UNIQUE item request rejected because quantity > 1 would violate uniqueness semantics.");
                        return false;
                }
                $added = inventory_legacy_get_write_repository()->addItemByIdUsingKnownUniqueness(
                        (int) $user,
                        (int) $itemid,
                        (int) $qty,
                        (string) $specialvalue,
                        (int) $sellvaluegold,
                        (int) $sellvaluegems,
                        (int) $charges,
                        !empty($item_raw['uniqueforserver']),
                        !empty($item_raw['uniqueforplayer'])
                );
                if (!$added) {
                        if (isset($item_raw['uniqueforserver']) && $item_raw['uniqueforserver']) {
                                debug("UNIQUE item has not been added because already someone else owns this!");
                        }
                        if (isset($item_raw['uniqueforplayer']) && $item_raw['uniqueforplayer']) {
                                debug("UNIQUEFORPLAYER item has not been added because this player already owns this item!");
                        }
                        return false;
                }
                debuglog("has gained $qty items (ID: $itemid).");
                inventory_legacy_invalidate_user_read_caches((int) $user);
                return true;
        }
}

function add_item($item, $qty=1, $user=0, $specialvalue="", $sellvaluegold=false, $sellvaluegems=false) {
	return inventory_legacy_add_item($item, $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems);
}


function get_inventory($user=0, $showhide=false, $class=0) {
        return inventory_legacy_get_inventory($user, $showhide, $class);
}

function get_inventory_item($itemid, $user = 0) {
        global $session;
        if (!is_int($itemid)) {
                $itemid = get_item_by_name($itemid);
                $itemid = $itemid['itemid'];
        }
        $itemid = (int)$itemid;
        if ($user === 0) $user = $session['user']['acctid'];
        return inventory_legacy_get_read_repository()->getInventoryItemForUser((int) $user, $itemid);
}

function uncharge_item($itemid, $user=0) {
        global $session;
        if (!is_int($itemid)) {
                $itemid = get_item_by_name($itemid);
                $itemid = $itemid['itemid'];
        }
        $itemid = (int)$itemid;
        if ($user === 0) $user = $session['user']['acctid'];
        $conn = Database::getDoctrineConnection();
        $inventory = Database::prefix("inventory");
        $affected = inventory_legacy_get_write_repository()->changeCharges((int) $user, $itemid, -1);
        if ($affected == 0)
                debug("ERROR: Tried to uncharge item although no charges present!");
        else
                debuglog("uncharged $affected items (ID: $itemid)", $user);
        $sql = "DELETE FROM {$inventory} WHERE itemid = :itemid AND userid = :userid AND charges = 0";
        $count = $conn->executeStatement(
                $sql,
                [
                        'itemid' => $itemid,
                        'userid' => (int) $user,
                ],
                [
                        'itemid' => ParameterType::INTEGER,
                        'userid' => ParameterType::INTEGER,
                ]
        );
        if ($count) debuglog("uncharged and deleted $count items (ID: $itemid)", $user);
        inventory_legacy_invalidate_user_read_caches((int) $user);
}

function recharge_item($itemid, $user=0) {
        global $session;
        if (!is_int($itemid)) {
                $itemid = get_item_by_name($itemid);
                $itemid = $itemid['itemid'];
        }
        $itemid = (int)$itemid;
        if ($user === 0) $user = $session['user']['acctid'];
        $affected = inventory_legacy_get_write_repository()->changeCharges((int) $user, $itemid, 1);
        if ($affected == 0)
                debug("ERROR: Tried to recharge non-present item!");
        else
                debuglog("recharged $affected items (ID: $itemid)", $user);
        inventory_legacy_invalidate_user_read_caches((int) $user);
}


function show_inventory($user = 0) {
        global $session;

        $login = httpget('login');
        if ($user === 0) $user = $session['user']['acctid'];
        $inventory = get_inventory($user);
        $count = count($inventory);
        tlschema("inventory");
        $name = translate_inline("Name");
        $class = translate_inline("Category");
        $description = translate_inline("Description");
        $goldvalue = translate_inline("Goldvalue");
        $gemvalue = translate_inline("Gemvalue");
        $quantity = translate_inline("Quantity");
        $options = translate_inline("Options");
        $drop = translate_inline("Drop this once");
        $conn = Database::getDoctrineConnection();
        $accounts = Database::prefix("accounts");
        $result = $conn->executeQuery(
                "SELECT name FROM {$accounts} WHERE acctid = :acctid",
                [
                        'acctid' => (int) $user,
                ],
                [
                        'acctid' => ParameterType::INTEGER,
                ]
        );
        $row = $result->fetchAssociative();

        rawoutput("<table border=0 cellpadding=2 cellspacing=2 align=center>");
        rawoutput("<tr class='trhead'><td colspan=6>");
        output("`c`b`^%s`& is carrying these items:`b`c", $row['name']);
        $ret=URLEncode($_SERVER['REQUEST_URI']);
        rawoutput("</td></tr>");
        $countweight=0;
        $itemcounter=0;
        if ($count) {
                foreach ($inventory as $i => $item) {
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
        return inventory_legacy_check_qty_by_id((int) $itemid, (int) $user);
}

function check_qty_by_name($itemname, $user = 0) {
	$item = get_item_by_name($itemname);
	return check_qty_by_id((int)$item['itemid'], $user);
}

function check_qty($item, $user=0) {
	return inventory_legacy_check_qty($item, $user);
}

function remove_item_by_id($item, $qty=1, $user=0) {
        global $session;

        if ($user === 0) $user = $session['user']['acctid'];

        $removed = inventory_legacy_get_write_repository()->removeItemById(
                (int) $user,
                (int) $item,
                (int) $qty
        );
        if ($removed > 0) {
                debuglog("removed $removed item(s) with ID $item from inventory", $user);
        }
        inventory_legacy_invalidate_user_read_caches((int) $user);
        invalidatedatacache("inventory-item-$item-$user");
        return $removed;
}

function remove_item_by_name($itemname, $qty=1, $user=0) {
	$row = get_item_by_name($itemname);
	return remove_item_by_id((int)$row['itemid'], $qty, $user);
}

function remove_item($item, $qty=1, $user=0) {
	return inventory_legacy_remove_item($item, $qty, $user);
}

function shopnav($return, $class, $sell=false, $user=0, $sellall=false, $showdescription=true) {
        global $session;

        if (substr($return,strlen($return)-1,1) != "&") $return .= "&";

        $conn = Database::getDoctrineConnection();
        $inventoryTable = Database::prefix("inventory");
        $itemTable = Database::prefix("item");
        if ($sell === false) {
                $sql = "SELECT *
                        FROM {$itemTable}
                        WHERE {$itemTable}.class = :class
                        AND {$itemTable}.buyable <> 0
                        ORDER BY
                        {$itemTable}.class ASC,
                        {$itemTable}.name ASC";
                $result = $conn->executeQuery(
                        $sql,
                        [
                                'class' => $class,
                        ],
                        [
                                'class' => ParameterType::STRING,
                        ]
                );
                $rows = $result->fetchAllAssociative();
        } else {
                if ($user === 0) $user = $session['user']['acctid'];
                $items = get_inventory($user, false, $class);
                $rows = array_values(array_filter($items, function ($row) {
                        return isset($row['sellable']) && $row['sellable'] != 0;
                }));
        }
        if ($rows) {
                tlschema('inventory');
                $classTranslated = translate_inline($class);
                if ($sellall == true && $sell === true) {
                        addnav(array("Sell all %s", $classTranslated));
                        addnav("Sell all", $return."id=all");
                }
                $sell?addnav(array("Sell %s", $classTranslated)):addnav(array("Buy %s", $classTranslated));
                foreach ($rows as $row) {
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

/**
 * Insert or update an item row.
 *
 * NOTE:
 * This module runs against environments that may enforce strict SQL modes
 * (e.g. rejecting empty strings for integer columns). To keep backward
 * compatibility with legacy form submissions, numeric fields are normalized
 * before persistence.
 *
 * @param array $injection Item field values from editor/forms.
 * @param array|false $exclude Optional list of keys to skip during update.
 *
 * @return bool True when inserted, false when updated.
 */
function inject_item($injection, $exclude=false) {
	// Borrowed basic idea from lotgd code. lib/http.php -> function: postparse();
	$sql = ""; $keys = ""; $vals = ""; $i = 0;

	// Normalize known numeric columns so strict SQL mode does not fail on
	// legacy empty-string input values. Do not populate missing fields here;
	// leave omitted columns to the database/default application logic.
	$numericDefaults = [
		'gold' => 0,
		'gems' => 0,
		'weight' => 0,
		'charges' => 0,
		'dragonkills' => 0,
		'level' => 1,
		'findchance' => 0,
		'loosechance' => 0,
		'dkloosechance' => 0,
		'buffid' => 0,
		'activationhook' => 0,
		'droppable' => 1,
		'hide' => 0,
		'sellable' => 1,
		'buyable' => 1,
		'uniqueforserver' => 0,
		'uniqueforplayer' => 0,
		'equippable' => 0,
	];
	foreach ($numericDefaults as $field => $default) {
		if (array_key_exists($field, $injection) && ($injection[$field] === '' || $injection[$field] === null)) {
			$injection[$field] = $default;
		}
	}

	$item = db_prefix("item");
	// Keep itemid out of inserts when it is empty/zero-like.
	if (isset($injection['itemid']) && (int) $injection['itemid'] <= 0) {
		unset($injection['itemid']);
	}
	// Avoid undefined array-key notices when creating brand-new items.
	$itemId = isset($injection['itemid']) ? (int) $injection['itemid'] : 0;
	$test = $itemId > 0 ? get_item($itemId) : false;
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
			$oldName = isset($test['name']) ? (string) $test['name'] : '';
			$newName = isset($injection['name']) ? (string) $injection['name'] : '';
			// Rename-safe invalidation: clear caches for both old and new names to
			// avoid stale name lookups and stale negative-cache markers.
			inventory_legacy_invalidate_item_read_caches((int) $test['itemid'], [$oldName, $newName]);
			// Inventory snapshots carry the item's columns, so refresh every holder too.
			inventory_legacy_invalidate_item_holders((int) $test['itemid']);
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
		$insertedId = function_exists('db_insert_id') ? (int) db_insert_id() : null;
		// Insert-safe invalidation: clear caches by new name and inserted id (if
		// available) so stale negative-cache markers are removed immediately.
		inventory_legacy_invalidate_item_read_caches($insertedId, (string) $injection['name']);
		debug("Inserted Item '".$injection['name']."'");
		return true;
	}
	return true;
}
?>
