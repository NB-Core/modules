<?php
	page_header("Item Editor");
	require_once("lib/superusernav.php");
	superusernav();
	addnav("Options - Items");
	addnav("New Item", "runmodule.php?module=inventory&op=editor&op2=newitem");
	addnav("Show all items", "runmodule.php?module=inventory&op=editor&op2=showitems");
	addnav("Options - Buffs");
	addnav("New Buff", "runmodule.php?module=inventory&op=editor&op2=newbuff");
	addnav("Show all buffs", "runmodule.php?module=inventory&op=editor&op2=showbuffs");
	addnav("Other Options");
	require_once("lib/showform.php");
	switch(httpget('op2')) {
		case "newitem2":
			$item = httpallpost();
			$id = httpget('id');
			$value = 0;
			foreach ($item['activationhook'] as $k=>$v) {
				if ($v) $value += (int)$k; 
			}
			$item['activationhook'] = $value;
			unset($item['showFormTabIndex']);
			require_once("modules/inventory/lib/itemhandler.php");
			if (isset($item['itemid']) && $item['itemid'] == 0) unset($item['itemid']);
			inject_item($item);
			invalidatedatacache("item-activation-fightnav-specialties");
			invalidatedatacache("item-activation-forest");
			invalidatedatacache("item-activation-train");
			invalidatedatacache("item-activation-shades");
			invalidatedatacache("item-activation-village");
		case "newitem":
			$id=httpget("id");
			$subop=httpget("subop");
			// Default for brand-new items (no id yet) to avoid undefined-variable
			// warnings when the editor form is rendered.
			$item = array();
			require_once("modules/inventory/lib/itemhandler.php");
			if ($id != "") {
				$item = get_item((int)$id);
				if ($subop=="module") {
					// Save modules settings
					$module = httpget("submodule");
					$post = httpallpost();
					unset($post['showFormTabIndex']);
					reset($post);
					foreach ($post as $key=>$val) {
						set_module_objpref("items", $id, $key, $val, $module);
						output("`^Saved module objpref %s!`0`n", $key);
					}
					
				}
				addnav("Item properties", "runmodule.php?module=inventory&op=editor&op2=newitem&id=$id");
				module_editor_navs("prefs-items", "runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id=$id&submodule=");
			}
			if (!is_array($item)) $item = array();
			if ($subop=="module") {
				$module = httpget("submodule");
				rawoutput("<form action='runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id=$id&submodule=$module' method='POST'>");
				module_objpref_edit("items", $module, $id);
				rawoutput("</form>");
				addnav("", "runmodule.php?module=inventory&op=editor&op2=newitem&subop=module&id=$id&submodule=$module");
			} else {
				$sql = "SELECT buffid, buffname, buffshortname FROM ".db_prefix("itembuffs");
				$result = db_query($sql);
				while ($row = db_fetch_assoc($result)){
				  $row['buffname'] = str_replace(",", " ", $row['buffname']);
				  $row['buffshortname'] = str_replace(",", " ", $row['buffshortname']);
				  $buffs[] = $row['buffid'];
				  $buffs[] = "{$row['buffname']} ({$row['buffshortname']})";
				}
				if (is_array($buffs) && count($buffs)) 
					$buffsjoin = "0,none," . join(",",$buffs);
				else 
					$buffsjoin = "0,none,";
				$enum_equip=",No where,righthand,Right Hand,lefthand,Left Hand,head,On the Head,body,On Upper Body,arms,On the Arms,legs,On Lower Body,feet,As Shoes,ring,As Ring,neck,Around the Neck,belt,As Belt";
				rawoutput("<form action='runmodule.php?module=inventory&op=editor&op2=newitem2&id=$id' method='post'>");
				addnav("", "runmodule.php?module=inventory&op=editor&op2=newitem2&id=$id");
				$format = array(
					"Basic information,title",
						"itemid"=>"Item id,viewhiddenonly",
						"class"=>"Item category, string|Loot",
						"name"=>"Item name, string|",
						"description"=>"Description, textarea,60,5|Just a normal, useless item.",
				  	"Values,title",
						"gold"=>"Gold value,int|0",
						"gems"=>"Gem value,int|0",
						"weight"=>"Weight,int|1",
						"droppable"=>"Is this item droppable,bool",
						"level"=>"Minimum level needed,range,1,15,1|1",
						"dragonkills"=>"Dragonkills needed,int|0",
						"customvalue"=>"Custom value,textarea",
						"exectext"=>"Text to display upon activation of the item,string,70",
						"Use %s to insert the item's name!,note",
						"noeffecttext"=>"Text to display if item has no effect,string,70",
						"execvalue"=>"Exec value,textarea",
						"Please see the file 'modules/inventory/lib/itemeffects.php' for possible values,note",
						"hide"=>"Hide item from inventory?,bool",
					"Buffs and activation,title",
						"buffid"=>"Activate this buff on useage,enum,$buffsjoin",
						"charges"=>"Amount of charges the item has,int|0",
						"link"=>"Link that's called upon activation,|",
						"activationhook"=>"Hooks which show the item,bitfield,127,"
							.HOOK_NEWDAY.		",Newday,"
							.HOOK_FOREST.		",Forest,"
							.HOOK_VILLAGE.		",Village,"
							.HOOK_SHADES.		",Shades,"
							.HOOK_FIGHTNAV.	",Fightnav,"
							.HOOK_TRAIN.		",Train,"
							.HOOK_INVENTORY.	",Inventory",
					"Chances,title",
						"findchance"=>"Chance to get this item though 'get_random_item()',range,0,100,1|100",
						"loosechance"=>"Chance that this item gets damaged when dying in battle,range,0,100,1|100",
						"dkloosechance"=>"Chance to loose this item after killing the dragon,range,0,100,1|100",
					"Shop Options,title",
						"sellable"=>"Is this item sellable?,bool",
						"buyable"=>"Is this item buyable?,bool",
					"Special Settings,title",
						"uniqueforserver"=>"Is this item unique (server)?,bool",
						"uniqueforplayer"=>"Is this item unique for the player?,bool",
						"equippable"=>"Is this item equippable?,bool",
						"equipwhere"=>"Where can this item be equipped?,enum,$enum_equip",
			  );
			  showform($format, $item);
			  rawoutput("</form>");
			}
			break;
		case "takeitem":
			$id = (int)httpget('id');
			add_item($id);
			output("`\$Item no. %s added once, you now have %s pieces.`0", $id, check_qty($id));
		default:
		case "showitems":
			\Lotgd\Output::requireVendorAsset('jquery', 'js', \Lotgd\Output::VENDOR_BUCKET_MID);
			\Lotgd\Output::requireVendorAsset('datatables', 'js', \Lotgd\Output::VENDOR_BUCKET_MID);
			\Lotgd\Output::requireVendorAsset('datatables', 'css', \Lotgd\Output::VENDOR_BUCKET_MID);
			$sql = "SELECT itemid, class, name, description, gold, gems FROM " . db_prefix("item")
				. " ORDER BY class ASC, name ASC";
			$result       = db_query($sql);
			$edit         = translate_inline("Edit");
			$del          = translate_inline("Delete");
			$give         = translate_inline("Give");
			$take         = translate_inline("Take");
			$conf         = addslashes(translate_inline("Do you really want to delete this item?"));
			$labelUncateg = translate_inline("(Uncategorized)");
			// collect all rows + unique categories for the multiselect
			$rows       = [];
			$categories = [];
			while ($row = db_fetch_assoc($result)) {
				$hasClass = isset($row['class']) && $row['class'] !== '';
				$row['_catLabel'] = $hasClass ? htmlspecialchars($row['class']) : $labelUncateg;
				$row['_catOrder'] = $hasClass ? htmlspecialchars($row['class']) : 'zzz';
				if (!in_array($row['_catLabel'], $categories, true)) {
					$categories[] = $row['_catLabel'];
				}
				$rows[] = $row;
			}
			sort($categories);

			$catSelectLabel = translate_inline("Filter by category:");
			$catAllLabel    = translate_inline("(All categories)");
			$catJsonOptions = json_encode(array_values($categories), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
			rawoutput(
				'<div style="margin-bottom:0.8em;">'
				. '<label for="inv-cat-filter" style="font-weight:bold;margin-right:0.5em;">' . $catSelectLabel . '</label>'
				. '<select id="inv-cat-filter" multiple size="5" style="min-width:200px;vertical-align:top;">'
				. '<option value="">' . $catAllLabel . '</option>'
				. '</select>'
				. '<small style="display:block;margin-top:3px;opacity:.75;">'
				. translate_inline("Hold Ctrl / Cmd to select multiple. No selection = show all.")
				. '</small>'
				. '</div>'
			);
			rawoutput(
				'<table id="inv-items-dt" class="dataTable" style="width:100%;">'
				. '<thead><tr>'
				. '<th>' . translate_inline("Category")    . '</th>'
				. '<th>' . translate_inline("Name")        . '</th>'
				. '<th>' . translate_inline("Item Id")     . '</th>'
				. '<th>' . translate_inline("Description") . '</th>'
				. '<th>' . translate_inline("Gold")        . '</th>'
				. '<th>' . translate_inline("Gems")        . '</th>'
				. '<th>' . translate_inline("Actions")     . '</th>'
				. '</tr></thead><tbody>'
			);
			foreach ($rows as $row) {
				addnav("", "runmodule.php?module=inventory&op=editor&op2=newitem&id=" . $row['itemid']);
				addnav("", "runmodule.php?module=inventory&op=editor&op2=delitem&id=" . $row['itemid']);
				addnav("", "runmodule.php?module=inventory&op=editor&op2=takeitem&id=" . $row['itemid']);
				addnav("", "runmodule.php?module=inventory&op=editor&op2=giveitem&id=" . $row['itemid']);
				rawoutput(
					'<tr>'
					. '<td data-order="' . $row['_catOrder'] . '">' . $row['_catLabel'] . '</td>'
					. '<td>' . appoencode($row['name']) . '</td>'
					. '<td>' . (int)$row['itemid'] . '</td>'
					. '<td>' . htmlspecialchars(substr($row['description'], 0, 80)) . '</td>'
					. '<td>' . (int)$row['gold'] . '</td>'
					. '<td>' . (int)$row['gems'] . '</td>'
					. '<td style="white-space:nowrap;">'
					. '[<a href="runmodule.php?module=inventory&op=editor&op2=newitem&id=' . $row['itemid'] . '">'
					. $edit . '</a>'
					. ' - <a href="runmodule.php?module=inventory&op=editor&op2=delitem&id=' . $row['itemid'] . '"'
					. ' onclick="return confirm(\'' . $conf . '\');">'
					. $del . '</a>'
					. ' - <a href="runmodule.php?module=inventory&op=editor&op2=takeitem&id=' . $row['itemid'] . '">'
					. $take . '</a>'
					. ' - <a href="runmodule.php?module=inventory&op=editor&op2=giveitem&id=' . $row['itemid'] . '">'
					. $give . '</a>]'
					. '</td>'
					. '</tr>'
				);
			}
			rawoutput('</tbody></table>');
			rawoutput('<script>
(function(){
	var allCats = ' . $catJsonOptions . ';
	var sel = document.getElementById("inv-cat-filter");
	allCats.forEach(function(c){
		var o = document.createElement("option");
		o.value = c; o.textContent = c;
		sel.appendChild(o);
	});
	$.fn.dataTable.ext.search.push(function(settings, data){
		if (settings.nTable.id !== "inv-items-dt") return true;
		var chosen = Array.from(sel.selectedOptions).map(function(o){ return o.value; });
		if (!chosen.length || chosen.indexOf("") !== -1) return true;
		return chosen.indexOf(data[0]) !== -1;
	});
	$(function(){
		var dt = $("#inv-items-dt").DataTable({
			pageLength: 25,
			stripeClasses: ["trlight","trdark"],
			order: [[0,"asc"],[1,"asc"]],
			columnDefs: [
				{ type: "num", targets: [2,4,5] },
				{ orderable: false, searchable: false, targets: 6 }
			],
			createdRow: function(row, data){
				if (parseInt(data[4]) > 0) $("td:eq(4)",row).addClass("colLtBrown");
				if (parseInt(data[5]) > 0) $("td:eq(5)",row).addClass("colkhaki");
			}
		});
		sel.addEventListener("change", function(){ dt.draw(); });
	});
})();
</script>');
			break;
		case "giveitem":
			$id = (int)httpget('id');
			require_once("modules/inventory/lib/itemhandler.php");
			$item = get_item($id);
			if (!$item) {
				output("`4The selected item could not be found.`0`n");
				break;
			}

			output("`^Give item:`0 %s`n`n", $item['name']);
			if (!empty($item['uniqueforserver']) || !empty($item['uniqueforplayer'])) {
				output("`iThis item is unique, so the quantity is limited to 1.`i`n`n");
			}

			rawoutput("<form action='runmodule.php?module=inventory&op=editor&op2=giveitem2&id=$id' method='post'>");
			output("Target account ID:`n");
			rawoutput(" <input name='targetacctid' size='8'><br>");
			output("Quantity:`n");
			rawoutput(" <input name='quantity' value='1' size='5'><br><br>");
			rawoutput("<input type='submit' class='button' value='".translate_inline("Give Item")."'>");
			rawoutput("</form>");
			addnav("", "runmodule.php?module=inventory&op=editor&op2=giveitem2&id=$id");
			break;
		case "giveitem2":
			$id = (int)httpget('id');
			$targetAcctid = (int)httppost('targetacctid');
			$quantity = max(1, (int)httppost('quantity'));
			require_once("modules/inventory/lib/itemhandler.php");
			$item = get_item($id);
			if (!$item) {
				output("`4The selected item could not be found.`0`n");
				break;
			}

			/*
			 * Unique item rules are checked before add_item() so admins receive a
			 * specific validation message instead of a generic inventory add failure.
			 */
			if ((!empty($item['uniqueforserver']) || !empty($item['uniqueforplayer'])) && $quantity > 1) {
				output("`4This item is unique, so you may only give 1 copy at a time.`0`n");
				break;
			}

			if ($targetAcctid <= 0) {
				output("`4Please enter a valid target account ID.`0`n");
				break;
			}
			$sql = "SELECT acctid, name FROM ".db_prefix("accounts")." WHERE acctid = $targetAcctid LIMIT 1";
			$result = db_query($sql);
			$target = db_fetch_assoc($result);
			if (!$target) {
				output("`4The target account could not be found.`0`n");
				break;
			}

			if (add_item($id, $quantity, $targetAcctid)) {
				output("`@Successfully gave %s`@ %s`@ to %s.`0`n", $quantity, $item['name'], $target['name']);
			} else {
				output("`4Could not give %s`@ %s`@ to %s.`0`n", $quantity, $item['name'], $target['name']);
			}
			break;
		case "delitem":
			$id = (int) httpget('id');
			// Look the item up before deleting it, its name keys a read cache too.
			$deletedItem = get_item_by_id($id);
			$sql = "DELETE FROM ".db_prefix("item")." WHERE itemid = $id LIMIT 1";
			$result = db_query($sql);
			if (db_affected_rows($result)) output("Item successfully deleted.`n`n");
			else output("While deleting this item an error occurred. Probably someone has already deleted this item.`n`n");
			inventory_legacy_invalidate_item_read_caches($id, is_array($deletedItem) ? (string) $deletedItem['name'] : null);
			$removed = inventory_legacy_delete_item_from_all_inventories($id);
			if ($removed) output("This item has been removed %s times from players' inventories.`n`n", $removed);
			else output("No item has been deleted from players' inventories.`n`n");
			invalidatedatacache("item-activation-fightnav-specialties");
			invalidatedatacache("item-activation-forest");
			invalidatedatacache("item-activation-train");
			invalidatedatacache("item-activation-shades");
			invalidatedatacache("item-activation-village");
			break;
		case "newbuff":
			$id=httpget("id");
			$yes = translate_inline("Yes");
			$no = translate_inline("No");
			if ($id != "") {
				$sql = "SELECT * FROM ".db_prefix("itembuffs")." WHERE buffid = $id";
				$result = db_query($sql);
				$buff = db_fetch_assoc($result);
			}
			rawoutput("<form action='runmodule.php?module=inventory&op=editor&op2=newbuff2&id=$id' method='post'>");
			addnav("", "runmodule.php?module=inventory&op=editor&op2=newbuff2&id=$id");
			rawoutput("<table border=0 cellpadding=1 cellspacing=5 cols=2 width=100%>");
			rawoutput("<tr><td width=40%>");
				output("Buff name`n(shown in editor):");
				rawoutput("</td><td>");
				rawoutput("<input name='buffname' value='{$buff['buffname']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Buff name`n(shown in charstats):");
				rawoutput("</td><td>");
				rawoutput("<input name='buffshortname' value='{$buff['buffshortname']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Rounds:");
				rawoutput("</td><td>");
				rawoutput("<input name='rounds' value='{$buff['rounds']}'>");
			rawoutput("</td></tr><tr><td colspan=2><hr>");
			rawoutput("</td></tr><tr><td>");
				output("Damage modificator (Goodguy):");
				rawoutput("</td><td>");
				rawoutput("<input name='dmgmod' value='{$buff['dmgmod']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Attack modificator (Goodguy):");
				rawoutput("</td><td>");
				rawoutput("<input name='atkmod' value='{$buff['atkmod']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Defense modificator (Goodguy):");
				rawoutput("</td><td>");
				rawoutput("<input name='defmod' value='{$buff['defmod']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Damage modificator (Badguy):");
				rawoutput("</td><td>");
				rawoutput("<input name='badguydmgmod' value='{$buff['badguydmgmod']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Attack modificator (Badguy):");
				rawoutput("</td><td>");
				rawoutput("<input name='badguyatkmod' value='{$buff['badguyatkmod']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Defense modificator (Badguy):");
				rawoutput("</td><td>");
				rawoutput("<input name='badguydefmod' value='{$buff['badguydefmod']}'>");
			rawoutput("</td></tr><tr><td colspan=2><hr>");
			rawoutput("</td></tr><tr><td>");
				output("Damageshield:");
				rawoutput("</td><td>");
				rawoutput("<input name='dmgshield' value='{$buff['dmgshield']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Lifetap:");
				rawoutput("</td><td>");
				rawoutput("<input name='lifetap' value='{$buff['lifetap']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Regeneration:");
				rawoutput("</td><td>");
				rawoutput("<input name='regen' value='{$buff['regen']}'>");
			rawoutput("</td></tr><tr><td colspan=2><hr>");
			rawoutput("</td></tr><tr><td>");
			rawoutput("</td></tr><tr><td>");
				output("Minion count:");
				rawoutput("</td><td>");
				rawoutput("<input name='minioncount' value='{$buff['minioncount']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Max badguy damage:");
				rawoutput("</td><td>");
				rawoutput("<input name='maxbadguydamage' value='{$buff['maxbadguydamage']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Min badguy damage:");
				rawoutput("</td><td>");
				rawoutput("<input name='minbadguydamage' value='{$buff['minbadguydamage']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Max goodguy damage:");
				rawoutput("</td><td>");
				rawoutput("<input name='maxgoodguydamage' value='{$buff['maxgoodguydamage']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Min goodguy damage:");
				rawoutput("</td><td>");
				rawoutput("<input name='mingoodguydamage' value='{$buff['mingoodguydamage']}'>");
			rawoutput("</td></tr><tr><td colspan=2><hr>");
			rawoutput("</td></tr><tr><td>");
				output("Start message:");
				rawoutput("</td><td>");
				rawoutput("<input name='startmsg' value='{$buff['startmsg']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Round message:");
				rawoutput("</td><td>");
				rawoutput("<input name='roundmsg' value='{$buff['roundmsg']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Wearoff:");
				rawoutput("</td><td>");
				rawoutput("<input name='wearoff' value='{$buff['wearoff']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Effect fail message:");
				rawoutput("</td><td>");
				rawoutput("<input name='effectfailmsg' value='{$buff['effectfailmsg']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Effect no-effect message:");
				rawoutput("</td><td>");
				rawoutput("<input name='effectnodmgmsg' value='{$buff['effectnodmgmsg']}'>");
			rawoutput("</td></tr><tr><td>");
				output("Effect message:");
				rawoutput("</td><td>");
				rawoutput("<input name='effectmsg' value='{$buff['effectmsg']}'>");
			rawoutput("</td></tr><tr><td colspan=2><hr>");
			rawoutput("</td></tr><tr><td>");
				output("Makes invulnerable?:");
				rawoutput("</td><td>");
				rawoutput("<select name='invulnerable'>");
				rawoutput("<option value='1'");
				rawoutput($buff['invulnerable']?" selected":"");
				rawoutput(">$yes</option><option value='0'");
				rawoutput($buff['invulnerable']?"":" selected");
				rawoutput(">$no</option></select>");
			rawoutput("</td></tr><tr><td>");
				output("Allow in PvP (not the activation!)?:");
				rawoutput("</td><td>");
				rawoutput("<select name='allowinpvp'>");
				rawoutput("<option value='1'");
				rawoutput($buff['allowinpvp']?" selected":"");
				rawoutput(">$yes</option><option value='0'");
				rawoutput($buff['allowinpvp']?"":" selected");
				rawoutput(">$no</option></select>");
			rawoutput("</td></tr><tr><td>");
				output("Allow in train (not the activation!)?:");
				rawoutput("</td><td>");
				rawoutput("<select name='allowintrain'>");
				rawoutput("<option value='1'");
				rawoutput($buff['allowintrain']?" selected":"");
				rawoutput(">$yes</option><option value='0'");
				rawoutput($buff['allowintrain']?"":" selected");
				rawoutput(">$no</option></select>");
			rawoutput("</td></tr><tr><td>");
				output("Survives newday?:");
				rawoutput("</td><td>");
				rawoutput("<select name='survivenewday'>");
				rawoutput("<option value='1'");
				rawoutput($buff['survivenewday']?" selected":"");
				rawoutput(">$yes</option><option value='0'");
				rawoutput($buff['survivenewday']?"":" selected");
				rawoutput(">$no</option></select>");
			rawoutput("</td></tr><tr><td colspan=2><hr>");
			rawoutput("</td></tr><tr><td>");
				$create = translate_inline($id?"Update":"Create");
				rawoutput("<input type=submit class='button' value='$create'>");
				rawoutput("</td><td>");
				rawoutput("<input class='button' type=reset>");
			rawoutput("</td></tr></table>");
			rawoutput("</form>");
			break;
		case "newbuff2":
			$post = httpallpost();
			$id = httpget('id');
			if (!$id) {
				$sql = "INSERT INTO ".db_prefix("itembuffs")." (`lifetap`, `roundmsg`, `rounds`, `buffname`, `buffshortname`, `invulnerable`, `dmgmod`,	`badguydmgmod`,	`atkmod`, `badguyatkmod`, `defmod`,`badguydefmod`, `dmgshield`, `regen`, `minioncount`, `maxbadguydamage`, `minbadguydamage`, `maxgoodguydamage`, `mingoodguydamage`, `startmsg`, `wearoff`, `effectfailmsg`, `effectnodmgmsg`, `effectmsg`, `allowinpvp`, `allowintrain`, `survivenewday`) VALUES ('{$post['lifetap']}','{$post['roundmsg']}', '{$post['rounds']}', '{$post['buffname']}', '{$post['buffshortname']}', '{$post['invulnerable']}', '{$post['dmgmod']}', '{$post['badguydmgmod']}', '{$post['atkmod']}', '{$post['badguyatkmod']}', '{$post['defmod']}', '{$post['badguydefmod']}', '{$post['dmgshield']}', '{$post['regen']}', '{$post['minioncount']}', '{$post['maxbadguydamage']}', '{$post['minbadguydamage']}', '{$post['maxgoodguydamage']}', '{$post['mingoodguydamage']}','{$post['startmsg']}', '{$post['wearoff']}',  '{$post['effectfailmsg']}', '{$post['effectnodmgmsg']}', '{$post['effectmsg']}', '{$post['allowinpvp']}', '{$post['allowintrain']}', '{$post['survivenewday']}')";
				db_query($sql);
				output("'`^%s`0' inserted.", $post['buffname']);
			} else {
				$sql = "UPDATE ".db_prefix("itembuffs")." SET
							buffname = '{$post['buffname']}',
							rounds = '{$post['rounds']}',
							roundmsg = '{$post['roundmsg']}',
							lifetap = '{$post['lifetap']}',
							buffshortname = '{$post['buffshortname']}',
							invulnerable = '{$post['invulnerable']}',
							dmgmod = '{$post['dmgmod']}',
							badguydmgmod = '{$post['badguydmgmod']}',
							atkmod = '{$post['atkmod']}',
							badguyatkmod = '{$post['badguyatkmod']}',
							defmod = '{$post['defmod']}',
							badguydefmod = '{$post['badguydefmod']}',
							dmgshield = '{$post['dmgshield']}',
							regen = '{$post['regen']}',
							minioncount = '{$post['minioncount']}',
							maxbadguydamage = '{$post['maxbadguydamage']}',
							minbadguydamage = '{$post['minbadguydamage']}',
							maxgoodguydamage = '{$post['maxgoodguydamage']}',
							mingoodguydamage = '{$post['mingoodguydamage']}',
							startmsg = '{$post['startmsg']}',
							roundmsg = '{$post['roundmsg']}',
							wearoff = '{$post['wearoff']}',
							effectfailmsg = '{$post['effectfailmsg']}',
							effectnodmgmsg = '{$post['effectnodmgmsg']}',
							effectmsg = '{$post['effectmsg']}',
							allowinpvp = '{$post['allowinpvp']}',
							allowintrain = '{$post['allowintrain']}',
							survivenewday = '{$post['survivenewday']}'
						WHERE buffid = $id";
				db_query($sql);
				invalidatedatacache("inventory-buff-$id");
				output("'`^%s`0' updated.", $post['buffname']);
			}
			break;
		case "showbuffs":
			\Lotgd\Output::requireVendorAsset('jquery', 'js', \Lotgd\Output::VENDOR_BUCKET_MID);
			\Lotgd\Output::requireVendorAsset('datatables', 'js', \Lotgd\Output::VENDOR_BUCKET_MID);
			\Lotgd\Output::requireVendorAsset('datatables', 'css', \Lotgd\Output::VENDOR_BUCKET_MID);
			$sql = "SELECT buffid, buffname, buffshortname FROM " . db_prefix("itembuffs")
				. " ORDER BY buffname ASC";
			$result = db_query($sql);
			$edit   = translate_inline("Edit");
			$del    = translate_inline("Delete");
			$conf   = addslashes(translate_inline("Do you really want to delete this buff?"));
			rawoutput(
				'<table id="inv-buffs-dt" class="dataTable" style="width:100%;">'
				. '<thead><tr>'
				. '<th>' . translate_inline("Buff Id")    . '</th>'
				. '<th>' . translate_inline("Buff Name")  . '</th>'
				. '<th>' . translate_inline("Short Name") . '</th>'
				. '<th>' . translate_inline("Actions")    . '</th>'
				. '</tr></thead><tbody>'
			);
			while ($row = db_fetch_assoc($result)) {
				addnav("", "runmodule.php?module=inventory&op=editor&op2=newbuff&id=" . $row['buffid']);
				addnav("", "runmodule.php?module=inventory&op=editor&op2=delbuff&id=" . $row['buffid']);
				rawoutput(
					'<tr>'
					. '<td>' . (int)$row['buffid'] . '</td>'
					. '<td>' . htmlspecialchars($row['buffname']) . '</td>'
					. '<td>' . htmlspecialchars($row['buffshortname']) . '</td>'
					. '<td style="white-space:nowrap;">'
					. '[<a href="runmodule.php?module=inventory&op=editor&op2=newbuff&id=' . $row['buffid'] . '">'
					. $edit . '</a>'
					. ' - <a href="runmodule.php?module=inventory&op=editor&op2=delbuff&id=' . $row['buffid'] . '"'
					. ' onclick="return confirm(\'' . $conf . '\');">' . $del . '</a>]'
					. '</td>'
					. '</tr>'
				);
			}
			rawoutput('</tbody></table>');
			rawoutput('<script>$(function(){
	$("#inv-buffs-dt").DataTable({
		pageLength: 25,
		stripeClasses: ["trlight","trdark"],
		order: [[1,"asc"]],
		columnDefs: [
			{ type: "num", targets: 0 },
			{ orderable: false, searchable: false, targets: 3 }
		]
	});
});</script>');
			break;
		case "delbuff":
			$id = httpget('id');
			$sql = "DELETE FROM ".db_prefix("itembuffs")." WHERE buffid = $id LIMIT 1";
			$result = db_query($sql);
			if (db_affected_rows($result)) output("Buff successfully deleted.`n`n");
			else output("While deleting this buffs an error occurred. Probably someone else already deleted this buff.`n`n");
	}
	page_footer();
?>
