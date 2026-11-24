<?php

/**
 * Ninja Merchant: sells (and buys) items in the woods. By Oliver Brendel.
 *
 * The shop logic is shared with the village store (ninjamerchantstore) in
 * ninjamerchant/src/Shop.php.
 */

use Shinobi\Modules\NinjaMerchant\Shop;

require_once __DIR__ . '/ninjamerchant/src/Shop.php';

function ninjamerchant_getmoduleinfo(): array
{
    return [
        'name' => 'Ninja Merchant',
        'author' => '`2Oliver Brendel',
        'version' => '1.1',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'settings' => [
            'The Ninja Merchant - Preferences,title',
            'maxshopping' => 'How many items can be bought or sold per day?,floatrange,1,10,1|3',
            'forbiddencategories' => 'What categories are forbidden (separate by comma),text',
            'forbidden' => 'Internal Array,viewonly',
        ],
        'prefs' => [
            'The Ninja Merchant - User Preferences,title',
            'today' => 'Shopped today how often?,int|0',
            'todaysell' => 'Shopped today how often?,int|0',
        ],
    ];
}

function ninjamerchant_install(): bool
{
    module_addeventhook('forest', "require_once('modules/ninjamerchant/ninjamerchant_chances.php');return ninjamerchant_chances();");
    module_addhook('newday-runonce');
    module_addhook('newday');
    return true;
}

function ninjamerchant_uninstall(): bool
{
    return true;
}

function ninjamerchant_dohook(string $hookname, array $args): array
{
    switch ($hookname) {
        case 'newday-runonce':
            (new Shop('ninjamerchant', 'forest.php?', false))->rollStock();
            break;
        case 'newday':
            set_module_pref('today', 0);
            set_module_pref('todaysell', 0);
            break;
    }
    return $args;
}

/** "Navigation" header with the way back to him and out. */
function ninjamerchant_navs(string $from, bool $back): void
{
    addnav('Navigation');
    if ($back) {
        addnav('Back to the Ninja Merchant', $from . 'op=investigate');
    }
    addnav('Leave', $from . 'op=leave');
}

function ninjamerchant_runevent($type, $link): void
{
    global $session;
    $from = 'forest.php?';
    $session['user']['specialinc'] = 'module:ninjamerchant';
    $shop = new Shop('ninjamerchant', $from, false);
    $op = (string) httpget('op');
    output_notl('`n');

    switch ($op) {
        case '':
            output("`2As you were walking along the forest path, a man wearing a large coat and a pair of round sunglasses suddenly blocks your path and grins at you.");
            addnav('See what he wants from you', $from . 'op=investigate');
            addnav('Walk away', $from . 'op=walkaway');
            break;

        case 'investigate':
            ninjamerchant_navs($from, false);
            output("`^\"Hello there, may I interest you in buying some potio...erm, well, you look like you might need more *hehe*. Want to see my ninja stuff I can offer you? I have only one business rule, every customer can sell/buy no more than %s articles a day... I want to be able to fill in the basic supply of many people and also can't carry everything.\"`n`2He opens his coat and you see quite a few stuff arranged in rows on the inside.`n`nDo you want to take a closer look?", $shop->limit());
            if ($shop->doneToday()) {
                output("`n`n`^\"Gee... you already were here often enough today. Come back tomorrow, I have to hit the road again!\"`2");
                break;
            }
            addnav('Buy');
            foreach ($shop->categories() as $class) {
                addnav(['`^Ask about %s', $class], $from . 'op=ask&category=' . rawurlencode($class));
            }
            addnav('Sell');
            addnav('`!Sell some items', $from . 'op=sell');
            break;

        case 'sell':
            $shop->sellPage(true);
            break;

        case 'ask':
            ninjamerchant_navs($from, true);
            addnav('Actions');
            $shop->askPage((string) httpget('category'));
            break;

        case 'look':
            $class = (string) httpget('category');
            addnav('Navigation');
            addnav(['`^Ask again about %s', $class], $from . 'op=ask&category=' . rawurlencode($class));
            addnav('Back to the Ninja Merchant', $from . 'op=investigate');
            addnav('Leave', $from . 'op=leave');
            addnav('Actions');
            $item = $shop->offered((int) httpget('item'), $class);
            if ($item === null) {
                output("`^\"Ahh, sadly I have nothing today in the category %s.... okay, maybe tomorrow is a better day `4^^`^\"`2, says the strange dealer.", $class);
                break;
            }
            output("`^\"Ahh, so you want to take a look at %s`^.... okay, here we go.\"`2`n`n", $item['name']);
            Shop::showItem($item);
            $shop->buyNav($item, $class);
            break;

        case 'buy':
            $why = $shop->buy((int) httpget('item'), (string) httpget('category'));
            ninjamerchant_navs($from, $why !== null || !$shop->doneToday());
            $shop->boughtText($why);
            break;

        case 'leave':
            output("`2You continue on your trip after having had this kind of business...");
            $session['user']['specialinc'] = '';
            break;

        case 'walkaway':
        default:
            output("`2You don't like the stupid grin on his face. Pushing him aside, you continue on your way.");
            $session['user']['specialinc'] = '';
            break;
    }
}

function ninjamerchant_run(): void
{
}
