<?php
/*
Sells Potions in the Village

*/

function ninjamerchantstore_getmoduleinfo() {
	$info = array(
		"name"=>"Ninja Merchant Village Shop",
		"author"=>"`2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Village",
		"download"=>"",
		"settings"=>array(
			"The Ninja Merchant - Preferences, title",
			"maxshopping"=>"How many items can be bought per day?,floatrange,1,10,1|3",
			"forbiddencategories"=>"What categories are forbidden(seperate by comma),text",
			"specialoffer"=>"Quantity of the special offer,int|10",
			"forbidden"=>"Internal Array,viewonly"
		),
		"preferences"=>array(
			"The Ninja Merchant - User Prefences, title",
			"today"=>"Shopped today how often?,int",
			"todaysell"=>"Shopped today how often?,int",
			"boughtspecialtoday"=>"Bought a special offer today?,int"
		),

	);
 return $info;
}
function ninjamerchantstore_install() {
	module_addhook("newday-runonce");
	module_addhook("newday");
	module_addhook("village-Sunagakure");
	return true;
}
function ninjamerchantstore_uninstall() {
 return true;
}

function ninjamerchantstore_dohook($hookname,$args) {
	switch($hookname) {
		case "newday-runonce":
			//clear the setting first, else the items will not show up the following day either. Thanks to  Iori for pointing that out.
			set_module_setting("forbidden",'',"ninjamerchantstore");
			set_module_setting("specialoffer",10,"ninjamerchantstore");
			ninjamerchantstore_getavail();
			break;
		case "newday":
			set_module_pref("today",0);
			set_module_pref("boughtspecialtoday",0);
			set_module_pref("todaysell",0);
			break;
		case "village-Sunagakure":

			tlschema($args['schemas']['marketnav']);
			addnav($args['marketnav']);
			addnav("Ninja Merchant's Items","runmodule.php?module=ninjamerchantstore");
			break;
			
	}
return $args;
}

function ninjamerchantstore_run() {
	global $session;
	$from = "runmodule.php?module=ninjamerchantstore&";
	page_header("Ninja Item Shop");
	addnav("Navigation");
	addnav("Leave","village.php");
	$op = httpget('op');
	output_notl("`n");
	switch ($op) {
		case "sell":
			$sellitem=httpget('sellitem');
			$limit=get_module_setting('maxshopping');
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
				$items=ninjamerchantstore_sellitems();
				if ($items==array()) {
					output("`^`n`n\"Oh, you have nothing I am interested in...\"`2, says the merchant.");
				}
				addnav("Sell");
				foreach ($items as $key=>$val) {
					addnav(array("`3Sell %s`3 (`^%s gold,`g %s gems`3) x%s",$val['name'],$val['gold'],$val['gems'],$val['quantity']),$from."op=sell&sellitem={$val['itemid']}&sellgold={$val['gold']}&sellgems={$val['gems']}");
				}
			break;

		case "ask":
			addnav("Back to the Merchant","runmodule.php?module=ninjamerchantstore");
			addnav("Actions");
			$class=rawurldecode(httpget('category'));
			$item=ninjamerchantstore_getitems($class);
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
			$item=ninjamerchantstore_getitem($itemid);
			addnav("Actions");
			addnav(array("`^Ask again about %s",$class),$from."op=ask&category=".rawurlencode($class));
			

			
			output("`^\"Ahh, so you want to take a look at %s`^....",$item['name']);
			if ($item['class']!="Loot") {
				
				if (((int)get_module_pref('boughtspecialtoday'))!=0) {
					output("`n`\$Sadly, you have had your special today. You know, others might want it too, you don't need to hog stuff... sorry.`^\"");
					break;
				} elseif (get_module_setting('specialoffer')<1) {
					output("`n`\$Sadly, we have sold out our entire special stock... sorry.`^\"");
					break;					
				}
				output(" `4this is our special offer today and we have only %s left!`n`^",get_module_setting('specialoffer'));
			}
			output("Okay, here we go.\"`2`n`n");
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
			increment_module_pref("today",1);
			require_once("modules/inventory/lib/itemhandler.php");
			add_item_by_id($itemid);
			$item=ninjamerchantstore_getitem($itemid);
			if ($item['class']!="Loot") {
				set_module_pref("boughtspecialtoday",1);
				increment_module_setting('specialoffer',-1);
			}
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
		default:
			$limit=get_module_setting('maxshopping');
			$category=ninjamerchantstore_getcategories();
			output("`^\"Hello there, may I interest you in buying some potio...erm, well, you look like you might need more *hehe*. Want to see my ninja stuff I can offer you? I have only one business rule, every customer can buy no more than %s articles a day... I want to be able to fill in the basic supply of many people and also don't have the more sophisticated stuff on all days.\"`n`2There are some displays visible and you see quite a few stuff arranged in rows on the inside.`n`nDo you want to take a closer look?",$limit);
			if (get_module_pref('today')>=$limit) output("`n`n`^\"Gee... you already were here often enough, but ok, one more shopping for you and then I hit the road again. Don't want to let a customer go without a buy!\"`2");
			addnav("Buy");
			foreach ($category as $key=>$val) {
				addnav(array("`^Ask about %s",$val),$from."op=ask&category=".rawurlencode($val));
			}
			/* addnav("Sell");
			addnav("`!Sell some items",$from."op=sell"); */
			break;
	}
	page_footer();
}


function ninjamerchantstore_sellitems() {
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

function ninjamerchantstore_getitems($class=false) {
	$forbidden=get_module_setting('forbidden','ninjamerchantstore');
	if ($forbidden!='') $forbid="AND itemid NOT IN ($forbidden)";
	if (!$class)
		$sql="SELECT * FROM ".db_prefix("item")." WHERE buyable=1 $forbid ORDER BY RAND() DESC;";
		else
		$sql="SELECT * FROM ".db_prefix("item")." WHERE class='$class' AND buyable=1 $forbid ORDER BY name DESC;";
	$result=db_query($sql);
	$out=array(); 
	while ($row=db_fetch_assoc($result)) {
		array_push($out,$row);
	}
	return $out;
}

function ninjamerchantstore_getcategories() {
	$sql="SELECT class FROM ".db_prefix("item")." WHERE class!='Loot' GROUP BY class ORDER BY class DESC";
	$forbiddencategories=explode(",",get_module_setting('forbiddencategories','ninjamerchantstore'));
	if (!is_array($forbiddencategories)) $forbiddencategories=array();
	$result=db_query($sql);
	$out=array("Loot");
	while ($row=db_fetch_assoc($result)) {
		if (!in_array($row['class'],$forbiddencategories)) {
			array_push($out,$row['class']);
		}
	}
	debug($sql);debug($out);
	return $out;
}

function ninjamerchantstore_getitem($itemid) {
	$sql="SELECT * FROM ".db_prefix("item")." WHERE itemid=$itemid;";
	$result=db_query($sql);
	return db_fetch_assoc($result);
}

function ninjamerchantstore_getavail() {
	$items=ninjamerchantstore_getitems();
	$forbidden=array();
	$found=0;
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
		if (e_rand(1,100)>$chance || $found) {
			array_push($forbidden,$val['itemid']);
		} elseif ($val['class']!='Loot') {
			//we have our special item for today
			$found=1;
		}
	}
	set_module_setting("forbidden",implode(",",$forbidden),'ninjamerchantstore');
}
?>
