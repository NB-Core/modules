<?php

/**
 * Ninja Merchant Village Shop: the merchant's store in a village. By Oliver Brendel.
 *
 * Loot is always on the shelves; one special (non-Loot) item a day, of
 * which there is a limited stock and one per player. The shop logic is
 * shared with the forest merchant in ninjamerchant/src/Shop.php.
 */

use Shinobi\Modules\NinjaMerchant\Shop;

require_once __DIR__ . '/ninjamerchant/src/Shop.php';

function ninjamerchantstore_getmoduleinfo(): array
{
    return [
        'name' => 'Ninja Merchant Village Shop',
        'author' => '`2Oliver Brendel',
        'version' => '1.1',
        'category' => 'Village',
        'download' => 'core_module',
        'settings' => [
            'The Ninja Merchant - Preferences,title',
            'storeloc' => 'In which village is the shop?,location|Sunagakure',
            'maxshopping' => 'How many items can be bought per day?,floatrange,1,10,1|3',
            'forbiddencategories' => 'What categories are forbidden (separate by comma),text',
            'specialstock' => 'Special offers in stock at the start of each day,int|10',
            'specialoffer' => 'Special offers left today,int|10',
            'forbidden' => 'Internal Array,viewonly',
        ],
        'prefs' => [
            'The Ninja Merchant - User Preferences,title',
            'today' => 'Shopped today how often?,int|0',
            'todaysell' => 'Shopped today how often?,int|0',
            'boughtspecialtoday' => 'Bought a special offer today?,int|0',
        ],
    ];
}

function ninjamerchantstore_install(): bool
{
    // The shop used to be fixed to Sunagakure; it now follows its location setting.
    module_drophook('village-Sunagakure');
    module_addhook('village');
    module_addhook('changesetting');
    module_addhook('newday-runonce');
    module_addhook('newday');
    return true;
}

function ninjamerchantstore_uninstall(): bool
{
    return true;
}

function ninjamerchantstore_dohook(string $hookname, array $args): array
{
    global $session;
    switch ($hookname) {
        case 'newday-runonce':
            set_module_setting('specialoffer', max(0, (int) get_module_setting('specialstock')), 'ninjamerchantstore');
            (new Shop('ninjamerchantstore', 'runmodule.php?module=ninjamerchantstore&', true))->rollStock();
            break;
        case 'newday':
            set_module_pref('today', 0);
            set_module_pref('boughtspecialtoday', 0);
            set_module_pref('todaysell', 0);
            break;
        case 'changesetting':
            // Follow the village when it is renamed.
            if (($args['module'] ?? '') == 'core' && $args['setting'] == 'villagename' && $args['old'] == get_module_setting('storeloc')) {
                set_module_setting('storeloc', $args['new']);
            }
            break;
        case 'village':
            if ($session['user']['location'] == get_module_setting('storeloc')) {
                tlschema($args['schemas']['marketnav']);
                addnav($args['marketnav']);
                tlschema();
                addnav("Ninja Merchant's Items", 'runmodule.php?module=ninjamerchantstore');
            }
            break;
    }
    return $args;
}

function ninjamerchantstore_run(): void
{
    global $session;
    $from = 'runmodule.php?module=ninjamerchantstore&';
    $shop = new Shop('ninjamerchantstore', $from, true);
    page_header('Ninja Item Shop');
    addnav('Navigation');
    addnav('Leave', 'village.php');
    $op = (string) httpget('op');
    output_notl('`n');

    if ($session['user']['location'] != get_module_setting('storeloc')) {
        output("`2The shop's door is locked. A sign says the merchant has moved on to another village.");
        page_footer();
        return;
    }

    switch ($op) {
        case 'sell':
            // Selling is not offered in the store (only in the forest), but the page still works.
            $shop->sellPage(false);
            break;

        case 'ask':
            addnav('Back to the Merchant', 'runmodule.php?module=ninjamerchantstore');
            addnav('Actions');
            $shop->askPage((string) httpget('category'));
            break;

        case 'look':
            $class = (string) httpget('category');
            addnav('Actions');
            addnav(['`^Ask again about %s', $class], $from . 'op=ask&category=' . rawurlencode($class));
            $item = $shop->offered((int) httpget('item'), $class);
            if ($item === null) {
                output("`^\"Ahh, sadly I have nothing today in the category %s.... okay, maybe tomorrow is a better day `4^^`^\"`2, says the strange dealer.", $class);
                break;
            }
            output("`^\"Ahh, so you want to take a look at %s`^....", $item['name']);
            if ($shop->isSpecial($item)) {
                $why = $shop->specialRefusal();
                if ($why === 'had') {
                    output("`n`\$Sadly, you have had your special today. You know, others might want it too, you don't need to hog stuff... sorry.`^\"");
                    break;
                }
                if ($why === 'soldout') {
                    output("`n`\$Sadly, we have sold out our entire special stock... sorry.`^\"");
                    break;
                }
                output(" `4this is our special offer today and we have only %s left!`n`^", (int) get_module_setting('specialoffer'));
            }
            output("Okay, here we go.\"`2`n`n");
            Shop::showItem($item);
            $shop->buyNav($item, $class);
            break;

        case 'buy':
            $why = $shop->buy((int) httpget('item'), (string) httpget('category'));
            $shop->boughtText($why);
            if ($why !== null || !$shop->doneToday()) {
                addnav('Back to the Ninja Merchant', $from . 'op=investigate');
            }
            break;

        default:
            output("`^\"Hello there, may I interest you in buying some potio...erm, well, you look like you might need more *hehe*. Want to see my ninja stuff I can offer you? I have only one business rule, every customer can buy no more than %s articles a day... I want to be able to fill in the basic supply of many people and also don't have the more sophisticated stuff on all days.\"`n`2There are some displays visible and you see quite a few stuff arranged in rows on the inside.`n`nDo you want to take a closer look?", $shop->limit());
            if ($shop->doneToday()) {
                output("`n`n`^\"Gee... you already were here often enough today. Come back tomorrow, I have to hit the road again!\"`2");
                break;
            }
            addnav('Buy');
            foreach ($shop->categories() as $class) {
                addnav(['`^Ask about %s', $class], $from . 'op=ask&category=' . rawurlencode($class));
            }
            break;
    }
    page_footer();
}
