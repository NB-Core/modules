<?php
	require_once("modules/inventory/lib/itemhandler.php");
        $inventory = get_inventory();
        $count=0;
        $destroyed_list = array();
        foreach ($inventory as $item) {
                $destroyed = 0;
                // Roll per item unit so one failed stack roll cannot destroy an entire stack.
                for ($c = 0; $c < (int) $item['quantity']; $c++) {
                        if ((int) $item['loosechance'] >= e_rand(1, 100)) {
                                $destroyed++;
                        }
                }
                $removed = 0;
                if ($destroyed > 0) {
                        $removed = remove_item((int) $item['itemid'], $destroyed);
                        if ($removed > 0) {
                                if (!isset($destroyed_list[$item['name']])) {
                                        $destroyed_list[$item['name']] = 0;
                                }
                                $destroyed_list[$item['name']] += $removed;
                        }
                }
                $count += $removed;
        }
	if ($count == 1) {
		output("`n`\$One of your items got damaged during the fight. ");
	} else if ($count > 1) {
		output("`n`\$Overall `^%s`\$ of your items have been damaged during the fight.", $count);
	}
        if ($count > 0) {
                output("`n`\$You lost the following item%s:`n", ($count == 1 ? "" : "s"));
                rawoutput("<ul class='itemlist'>");
                foreach ($destroyed_list as $name => $destroyed) {
                        rawoutput("<li>");
                        output("%s x%d", $name, $destroyed);
                        rawoutput("</li>");
                }
                rawoutput("</ul>");
        }
?>
