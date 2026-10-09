<?php

function forest_special_drawitem_getmoduleinfo()
{
        return [
                "name" => "Forest Special - Draw Item",
                "author" => "OpenAI Assistant",
                "version" => "1.0",
                "category" => "Forest Specials",
                "download" => "",
                "settings" => [
                        "Forest Draw Item Settings,title",
                        "daily_chance" => "Chance (percentage) that the deck appears on a new day,int|25",
                        "forest_chance" => "Chance (percentage) per forest visit to discover the deck when it is available,int|20",
                        "max_draws" => "How many draws are allowed each day when the deck appears?,int|1",
                        "rarity_common_weight" => "Weight for the Common rarity bucket,int|60",
                        "rarity_uncommon_weight" => "Weight for the Uncommon rarity bucket,int|30",
                        "rarity_rare_weight" => "Weight for the Rare rarity bucket,int|10",
                        "rarity_common_categories" => "Common item categories (semicolon separated),text|",
                        "rarity_uncommon_categories" => "Uncommon item categories (semicolon separated),text|",
                        "rarity_rare_categories" => "Rare item categories (semicolon separated),text|",
                ],
                "prefs" => [
                        "Forest Draw Item User Preferences,title",
                        "available_today" => "Is the deck available to this player today?,bool|0",
                        "draws_used" => "How many draws have been used today?,int|0",
                        "offer_ready" => "Has the forest deck presented itself this turn?,bool|0",
                ],
        ];
}

function forest_special_drawitem_install()
{
        module_addhook("forest");
        module_addhook("newday");
        return true;
}

function forest_special_drawitem_uninstall()
{
        return true;
}

function forest_special_drawitem_dohook($hookname, $args)
{
        global $session;

        switch ($hookname) {
                case "newday":
                        $chance = max(0, min(100, (int) get_module_setting("daily_chance")));
                        $available = e_rand(1, 100) <= $chance ? 1 : 0;
                        set_module_pref("available_today", $available);
                        set_module_pref("draws_used", 0);
                        set_module_pref("offer_ready", 0);
                        break;
                case "forest":
                        if ($session['user']['specialinc'] !== "" && $session['user']['specialinc'] !== "module:forest_special_drawitem") {
                                break;
                        }
                        if (!get_module_pref("available_today")) {
                                break;
                        }
                        $drawsUsed = (int) get_module_pref("draws_used");
                        $maxDraws = max(0, (int) get_module_setting("max_draws"));
                        if ($maxDraws > 0 && $drawsUsed >= $maxDraws) {
                                set_module_pref("available_today", 0);
                                set_module_pref("offer_ready", 0);
                                break;
                        }

                        if (!get_module_pref("offer_ready")) {
                                $forestChance = max(0, min(100, (int) get_module_setting("forest_chance")));
                                if ($forestChance > 0 && e_rand(1, 100) <= $forestChance) {
                                        set_module_pref("offer_ready", 1);
                                }
                        }

                        if (get_module_pref("offer_ready")) {
                                addnav("Investigate the Deck", "runmodule.php?module=forest_special_drawitem");
                        }
                        break;
        }

        return $args;
}

function forest_special_drawitem_run()
{
        global $session;

        if (!forest_special_drawitem_is_ready()) {
                                redirect("forest.php");
        }

        $op = httpget('op');
        page_header("Mysterious Deck");

        switch ($op) {
                case "draw":
                        forest_special_drawitem_handle_draw();
                        break;
                case "leave":
                        output("`2You decide it is wiser to leave the mysterious deck alone for now. It fades back into the underbrush.`n`n");
                        set_module_pref("offer_ready", 0);
                        addnav("Return to the Forest", "forest.php");
                        break;
                default:
                        output("`2As you explore the forest, shimmering cards swirl into existence, forming a floating deck before you.`n");
                        output("`2A whispered voice invites you to draw a single card. Whatever is revealed becomes yours.`n`n");
                        addnav("Draw a Card", "runmodule.php?module=forest_special_drawitem&op=draw");
                        addnav("Back Away", "runmodule.php?module=forest_special_drawitem&op=leave");
                        break;
        }

        page_footer();
}

function forest_special_drawitem_is_ready()
{
        if (!get_module_pref("available_today")) {
                return false;
        }

        $drawsUsed = (int) get_module_pref("draws_used");
        $maxDraws = max(0, (int) get_module_setting("max_draws"));
        if ($maxDraws > 0 && $drawsUsed >= $maxDraws) {
                return false;
        }

        return (bool) get_module_pref("offer_ready");
}

function forest_special_drawitem_handle_draw()
{
        global $session;

        require_once("inventory/lib/itemhandler.php");

        $buckets = forest_special_drawitem_build_buckets();
        if (empty($buckets)) {
                output("`4The deck scatters before you can draw. It seems no items are configured for it yet.`n`n");
                set_module_pref("offer_ready", 0);
                addnav("Return to the Forest", "forest.php");
                return;
        }

        $bucket = forest_special_drawitem_select_bucket($buckets);
        if ($bucket === null) {
                output("`4The magic fizzles as the deck loses its form. No rewards are available right now.`n`n");
                set_module_pref("offer_ready", 0);
                addnav("Return to the Forest", "forest.php");
                return;
        }

        $loot = forest_special_drawitem_pull_item_from_bucket($bucket);
        if ($loot === null) {
                output("`4The chosen card crumbles into dust before revealing anything of value.`n`n");
                set_module_pref("offer_ready", 0);
                addnav("Return to the Forest", "forest.php");
                return;
        }

        $added = add_item_by_id((int) $loot['itemid']);
        if ($added) {
                $rarityLabel = $bucket['label'];
                $itemName = $loot['name'];
                output("`2You draw a card emblazoned with a %s sigil!`n", $rarityLabel);
                output("`^The deck grants you %s`^, which materializes in your hands before vanishing.`n`n", $itemName);
                debuglog(sprintf("drew %s (itemid %s) from the forest deck", $itemName, $loot['itemid']));
        } else {
                output("`4You draw a card, but the magic falters as your pack refuses another burden. The reward slips away.`n`n");
        }

        forest_special_drawitem_increment_draws();
        set_module_pref("offer_ready", 0);
        addnav("Return to the Forest", "forest.php");
}

function forest_special_drawitem_increment_draws()
{
        $drawsUsed = (int) get_module_pref("draws_used");
        $drawsUsed++;
        set_module_pref("draws_used", $drawsUsed);

        $maxDraws = max(0, (int) get_module_setting("max_draws"));
        if ($maxDraws > 0 && $drawsUsed >= $maxDraws) {
                set_module_pref("available_today", 0);
        }
}

function forest_special_drawitem_build_buckets()
{
        $definitions = [
                [
                        'label' => 'Common',
                        'weight_setting' => 'rarity_common_weight',
                        'categories_setting' => 'rarity_common_categories',
                ],
                [
                        'label' => 'Uncommon',
                        'weight_setting' => 'rarity_uncommon_weight',
                        'categories_setting' => 'rarity_uncommon_categories',
                ],
                [
                        'label' => 'Rare',
                        'weight_setting' => 'rarity_rare_weight',
                        'categories_setting' => 'rarity_rare_categories',
                ],
        ];

        $buckets = [];
        foreach ($definitions as $definition) {
                $weight = max(0, (int) get_module_setting($definition['weight_setting']));
                if ($weight === 0) {
                        continue;
                }

                $categories = forest_special_drawitem_parse_category_list(get_module_setting($definition['categories_setting']));
                if (empty($categories)) {
                        continue;
                }

                $categoriesString = forest_special_drawitem_implode_categories($categories);
                if ($categoriesString === '') {
                        continue;
                }

                $buckets[] = [
                        'label' => $definition['label'],
                        'weight' => $weight,
                        'categories' => $categoriesString,
                ];
        }

        return $buckets;
}

function forest_special_drawitem_parse_category_list($list)
{
        $entries = preg_split('/[;\r\n]+/', (string) $list);
        $parsed = [];
        foreach ($entries as $entry) {
                $entry = trim($entry);
                if ($entry === '') {
                        continue;
                }

                $parsed[$entry] = true;
        }

        return array_keys($parsed);
}

function forest_special_drawitem_implode_categories(array $categories)
{
        $categories = array_filter(array_map('trim', $categories), 'strlen');
        if (empty($categories)) {
                return '';
        }

        return implode(',', $categories);
}

function forest_special_drawitem_select_bucket(array $buckets)
{
        $totalWeight = 0;
        foreach ($buckets as $bucket) {
                                $totalWeight += (int) $bucket['weight'];
        }

        if ($totalWeight <= 0) {
                return null;
        }

        $roll = e_rand(1, $totalWeight);
        $cursor = 0;
        foreach ($buckets as $bucket) {
                $cursor += (int) $bucket['weight'];
                if ($roll <= $cursor) {
                        return $bucket;
                }
        }

        return end($buckets);
}

function forest_special_drawitem_pull_item_from_bucket(array $bucket)
{
        if (!isset($bucket['categories']) || $bucket['categories'] === '') {
                return null;
        }

        $category = [
                'categories' => $bucket['categories'],
        ];
        $category = modulehook('findloot-categories', $category);
        if (empty($category['categories'])) {
                return null;
        }

        $item = get_random_item($category['categories']);
        if ($item === false) {
                return null;
        }

        return $item;
}

?>
