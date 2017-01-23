<?php
/*
Sells Potions in the Woods

*/

function ninjamerchant_getmoduleinfo() {
	$info = array(
		"name"=>"Ninja Merchant",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Forest Specials",
		"download"=>"",
		"settings"=>array(
			"The Ninja Merchant - Preferences, title",
			"maxshopping"=>"How many items can be bought per day?,floatrange,1,10,1|3",
			"forbiddencategories"=>"What categories are forbidden(seperate by comma),text",
			"forbidden"=>"Internal Array,viewonly",
		),
		"preferences"=>array(
			"The Ninja Merchant - User Prefences, title",
			"today"=>"Shopped today how often?,int",
			"todaysell"=>"Shopped today how often?,int",
		),

	);
 return $info;
}
function ninjamerchant_install() {
	module_addeventhook("forest", "require_once('modules/ninjamerchant/ninjamerchant_chances.php');return ninjamerchant_chances();");
	module_addhook("newday-runonce");
	module_addhook("newday");
	return true;
}
function ninjamerchant_uninstall() {
 return true;
}

function ninjamerchant_dohook($hookname,$args) {
	switch($hookname) {
		case "newday-runonce":
			//clear the setting first, else the items will not show up the following day either. Thanks to  Iori for pointing that out.
			set_module_setting("forbidden",'',"ninjamerchant");
			ninjamerchant_getavail();
			break;
		case "newday":
			set_module_pref("today",0);
			set_module_pref("todaysell",0);
			break;
	}
return $args;
}

function ninjamerchant_runevent($type,$link) {
	global $session;
	$from = "forest.php?";
	$session['user']['specialinc'] = "module:ninjamerchant";
	$op = httpget('op');
	output_notl("`n");
	switch ($op) {
		case "":
			output("`2As you were walking along the forest path, a man wearing a large coat and a pair of round sunglasses suddenly blocks your path and grins at you.");
			addnav("See what he wants from you",$from."op=investigate");
			addnav("Walk away",$from."op=walkaway");
			break;
		case "investigate":
			$limit=get_module_setting('maxshopping');
			$category=ninjamerchant_getcategories();
			addnav("Navigation");
			addnav("Leave",$from."op=leave");
			output("`^\"Hello there, may I interest you in buying some potio...erm, well, you look like you might need more *hehe*. Want to see my ninja stuff I can offer you? I have only one business rule, every customer can sell/buy no more than %s articles a day... I want to be able to fill in the basic supply of many people and also can't carry everything.\"`n`2He opens his coat and you see quite a few stuff arranged in rows on the inside.`n`nDo you want to take a closer look?",$limit);
			if (get_module_pref('today')>=$limit) output("`n`n`^\"Gee... you already were here often enough, but ok, one more shopping for you and then I hit the road again. Don't want to let a customer go without a buy!\"`2");
			addnav("Buy");
			foreach ($category as $key=>$val) {
				addnav(array("`^Ask about %s",$val),$from."op=ask&category=".rawurlencode($val));
			}
			addnav("Sell");
			addnav("`!Sell some items",$from."op=sell");
			break;
		case "sell":
			$sellitem=httpget('sellitem');
			$limit=get_module_setting('maxshopping');
			addnav("Navigation");
			addnav("Leave",$from."op=leave");
			if ($sellitem) {
				require_once("modules/inventory/lib/itemhandler.php");
				remove_item_by_id($sellitem);
				output("`^\"Thanks, here is your money.\"`2");
				$session['user']['gold']+=(int) httpget('sellgold');
				$session['user']['gems']+=(int) httpget('sellgems');
				increment_module_pref("today",1);
				if (get_module_pref("today")>=get_module_setting("maxshopping")) {
					output("`n`n`^\"Well, that's what I can do for you... See you later!\"");
					break;
				} else {
					addnav("Back to the Ninja Merchant",$from."op=investigate");
				}
			} else {
				addnav("Back to the Ninja Merchant",$from."op=investigate");
				output("`^\"So you want to sell your stuff, eh? Ok, if you have something I want, then I pay you a good price. Remember, you can sell me or buy from me %s items a day.\"`2",$limit);
				if (get_module_pref("today")>=get_module_setting("maxshopping")) {
					output("`n`n`^\"So this is your last sell for today!\"`2");
				}
			}
				output("`n`nYou can click on an item to `bsell it instantly`b to the merchant. The quantity is shown behind, i.e. x2 means you have 2 items to sell. `n`\$No extra confirmation!");
				$items=ninjamerchant_sellitems();
				if ($items==array()) {
					output("`^`n`n\"Oh, you have nothing I am interested in...\"`2, says the merchant.");
				}
				addnav("Sell");
				foreach ($items as $key=>$val) {
					addnav(array("`3Sell %s`3 (`^%s gold,`g %s gems`3) x%s",$val['name'],$val['gold'],$val['gems'],$val['quantity']),$from."op=sell&sellitem={$val['itemid']}&sellgold={$val['gold']}&sellgems={$val['gems']}");
				}
			break;

		case "ask":
			addnav("Navigation");
			addnav("Back to the Ninja Merchant",$from."op=investigate");
			addnav("Leave",$from."op=leave");
			addnav("Actions");
			$class=rawurldecode(httpget('category'));
			$item=ninjamerchant_getitems($class);
			if ($item==array()) {
				output("`^\"Ahh, sadly I have nothing today in the category %s.... okay, maybe tomorrow is a better day `4^^`^\"`2, says the strange dealer.",$class);
				break;
			} else {
				output("`^\"Ahh, so you want to my %s.... okay, here we go, these are items I have today, tomorrow may be more... tomorrow may be less, you know, economy...\"`2, says the strange dealer.",$class);			
			}
			foreach ($item as $key=>$val) {
				addnav(array("`^Ask about %s",$val['name']),$from."op=look&category=".rawurlencode($class)."&item=".$val['itemid']);
			}

			break;
		case "look":
			$class=rawurldecode(httpget('category'));
			$itemid=httpget('item');
			$item=ninjamerchant_getitem($itemid);
			addnav("Navigation");
			addnav(array("`^Ask again about %s",$class),$from."op=ask&category=".rawurlencode($class));
			addnav("Back to the Ninja Merchant",$from."op=investigate");
			addnav("Leave",$from."op=leave");
			addnav("Actions");
			output("`^\"Ahh, so you want to take a look at %s`^.... okay, here we go.\"`2`n`n",$item['name']);
			rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' width=95% align=left>");
			$classname=translate_inline("Category");
			rawoutput("<tr class='trlight'><td>$classname</td>");
			rawoutput("<td>{$item['class']}</td></tr>");
			$name=translate_inline("Name of the item");
			rawoutput("<tr class='trdark'><td>$name</td>");
			output_notl("<td>{$item['name']}</td></tr>",true);
			$desc=translate_inline("Description");
			rawoutput("<tr class='trlight' ><td>$desc</td>");
			output_notl("<td>{$item['description']}</td></tr>",true);
			$gold=translate_inline("Gold Cost");
			rawoutput("<tr class='trdark'><td>$gold</td>");
			if ($item['gold']<1) $item['gold']=1;
			rawoutput("<td>{$item['gold']}</td></tr>");
			$gem=translate_inline("Gem Cost");
			rawoutput("<tr class='trlight'><td>$gem</td>");
			rawoutput("<td>{$item['gems']}</td></tr>");
			if ($item['charges']>0) {
				$charges=translate_inline("Charges");
				rawoutput("<tr class='trdark'><td>$charges</td>");
				rawoutput("<td>{$item['charges']}</td></tr>");
			}
			rawoutput("</table>");
			require_once("modules/inventory/lib/itemhandler.php");
			if ($item['uniqueforplayer']==1 && check_qty_by_id($item['itemid'])>=1) {
				addnav("Buy this item (you already have it!)","");
			} else {
				if ($session['user']['gold'] >=$item['gold'] && $session['user']['gems']>=$item['gems'])
					addnav("Buy this item",$from."op=buy&item=".$item['itemid']);
					else
					addnav("Buy this item (not enough funds!)","");
			}
			break;
		case "buy":
			$itemid=httpget('item');
			addnav("Navigation");
			addnav("Leave",$from."op=leave");
			increment_module_pref("today",1);
			require_once("modules/inventory/lib/itemhandler.php");
			add_item_by_id($itemid);
			$item=ninjamerchant_getitem($itemid);
			if ($item['gold']<1) $item['gold']=1;
			$session['user']['gold']-=$item['gold'];
			$session['user']['gems']-=$item['gems'];
			output("`^\"Gee, thanks for buying. I hope you will be another satisfied customer. Please bear in mind to use your bought item with care. Else they might sue me or something...\"`2, states the merchant.");
			if (get_module_pref("today")>=get_module_setting("maxshopping")) {
				output("`n`n`^\"Well, that's what I can do for you... See you later!");
			} else {
				addnav("Back to the Ninja Merchant",$from."op=investigate");
			}
			break;
		case "leave":
			output("`2You continue on your trip after having had this kind of business...");
			$session['user']['specialinc'] = "";
			break;
		case "walkaway":
			output("`2You don't like the stupid grin on his face. Pushing him aside, you continue on your way.");
			$session['user']['specialinc'] = "";
			break;
		break;
	}
}

function ninjamerchant_run(){
}

function ninjamerchant_sellitems() {
	global $session;
	$inventory=db_prefix('inventory');
	$item=db_prefix('item');
	$mod=0.6;
	$sql="SELECT $item.name as name, $item.itemid as itemid, round($item.gold*$mod) as gold, round($item.gems*$mod) as gems, count($item.itemid) as quantity FROM $item RIGHT JOIN $inventory ON $item.itemid=$inventory.itemid WHERE $item.sellable=1 AND $inventory.userid={$session['user']['acctid']} GROUP BY $item.itemid ORDER BY $item.class DESC, $item.name DESC;";
	$result=db_query($sql);
	$out=array();
	while ($row=db_fetch_assoc($result)) {
		array_push($out,$row);
	}
	return $out;
}

function ninjamerchant_getitems($class=false) {
	$forbidden=get_module_setting('forbidden','ninjamerchant');
	if ($forbidden!='') $forbid="AND itemid NOT IN ($forbidden)";
	if (!$class)
		$sql="SELECT * FROM ".db_prefix("item")." WHERE buyable=1 $forbid ORDER BY class DESC;";
		else
		$sql="SELECT * FROM ".db_prefix("item")." WHERE class='$class' AND buyable=1 $forbid ORDER BY name DESC;";
	$result=db_query($sql);
	$out=array(); 
	while ($row=db_fetch_assoc($result)) {
		array_push($out,$row);
	}
	return $out;
}

function ninjamerchant_getcategories() {
	$sql="SELECT class FROM ".db_prefix("item")." GROUP BY class ORDER BY class DESC";
	$forbiddencategories=explode(",",get_module_setting('forbiddencategories','ninjamerchant'));
	if (!is_array($forbiddencategories)) $forbiddencategories=array();
	$result=db_query($sql);
	$out=array();
	while ($row=db_fetch_assoc($result)) {
		if (!in_array($row['class'],$forbiddencategories))
			array_push($out,$row['class']);
	}
	return $out;
}

function ninjamerchant_getitem($itemid) {
	$sql="SELECT * FROM ".db_prefix("item")." WHERE itemid=$itemid;";
	$result=db_query($sql);
	return db_fetch_assoc($result);
}

function ninjamerchant_getavail() {
	$items=ninjamerchant_getitems();
	$forbidden=array();
	foreach ($items as $key=>$val) {
		switch ($val['class']) {
			case "Loot":
				$chance=$val['findchance']+30;
				break;
			case "Scroll":
				$chance=30;
				break;
			case "Potion":
				$chance=20;
				break;
			case "Secret Scroll":
				$chance=10;
				break;
			default:
				$chance=0;
		}
		if (e_rand(1,100)>$chance) {
			array_push($forbidden,$val['itemid']);
		}
	}
	set_module_setting("forbidden",implode(",",$forbidden),'ninjamerchant');
}
?>
