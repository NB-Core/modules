<?php

declare(strict_types=1);

namespace Shinobi\Modules\NinjaMerchant;

use Lotgd\MySQL\Database;

/**
 * The Ninja Merchant's shop, shared by the forest merchant (ninjamerchant)
 * and his village store (ninjamerchantstore).
 *
 * Everything that changes gold, gems or the inventory is checked again on
 * the page that does it: the daily limit, the item's availability today,
 * the price (from the database, never from the link), funds, unique items,
 * and for selling, that the player really owns the item.
 */
final class Shop
{
    /** Share of the item price the merchant pays when buying from a player. */
    private const SELL_RATE = 0.6;

    private string $module;
    private string $link;
    private bool $store;

    /**
     * @param string $module ninjamerchant or ninjamerchantstore
     * @param string $link   base link for the shop's ops, ending in ? or &
     * @param bool   $store  the village store: Loot always on offer, one special item a day
     */
    public function __construct(string $module, string $link, bool $store)
    {
        $this->module = $module;
        $this->link = $link;
        $this->store = $store;
    }

    // Stock.

    /** Item ids not on offer today (comma list in the "forbidden" setting). */
    private function unavailable(): array
    {
        return \array_values(\array_filter(\array_map('intval', \explode(',', (string) \get_module_setting('forbidden', $this->module)))));
    }

    /** @return list<string> */
    private function forbiddenCategories(): array
    {
        return \array_values(\array_filter(\array_map('trim', \explode(',', (string) \get_module_setting('forbiddencategories', $this->module))), 'strlen'));
    }

    /** @return list<string> Categories the merchant sells. */
    public function categories(): array
    {
        $sql = 'SELECT class FROM ' . Database::prefix('item');
        $sql .= $this->store ? " WHERE class != 'Loot'" : '';
        $sql .= ' GROUP BY class ORDER BY class DESC';
        $out = $this->store ? ['Loot'] : [];
        foreach (Database::getDoctrineConnection()->fetchFirstColumn($sql) as $class) {
            if (!\in_array((string) $class, $this->forbiddenCategories(), true)) {
                $out[] = (string) $class;
            }
        }
        return $out;
    }

    /**
     * Buyable items on offer today, of one category or of all.
     *
     * @return list<array<string, mixed>>
     */
    public function items(?string $class = null): array
    {
        $sql = 'SELECT * FROM ' . Database::prefix('item') . ' WHERE buyable = 1';
        $params = [];
        if ($class !== null) {
            $sql .= ' AND class = :class';
            $params['class'] = $class;
        }
        $off = $this->unavailable();
        if ($off !== []) {
            // Ints only (cast above), so they can go into the list directly.
            $sql .= ' AND itemid NOT IN (' . \implode(',', $off) . ')';
        }
        $sql .= $class === null ? ($this->store ? ' ORDER BY RAND()' : ' ORDER BY class DESC') : ' ORDER BY name DESC';
        return Database::getDoctrineConnection()->fetchAllAssociative($sql, $params);
    }

    /** @return array<string, mixed>|null */
    public function item(int $itemid): ?array
    {
        $row = Database::getDoctrineConnection()->fetchAssociative(
            'SELECT * FROM ' . Database::prefix('item') . ' WHERE itemid = :id',
            ['id' => $itemid]
        );
        return $row === false ? null : $row;
    }

    /** An item the merchant sells today in the given category, or null. */
    public function offered(int $itemid, string $class): ?array
    {
        if (!\in_array($class, $this->categories(), true)) {
            return null;
        }
        foreach ($this->items($class) as $item) {
            if ((int) $item['itemid'] === $itemid) {
                return $item;
            }
        }
        return null;
    }

    /** New day for the whole server: roll what is on offer today. */
    public function rollStock(): void
    {
        \set_module_setting('forbidden', '', $this->module);
        $off = [];
        $special = false;
        foreach ($this->items() as $item) {
            $chance = match ((string) $item['class']) {
                'Loot' => (int) $item['findchance'] + 30,
                'Scroll' => 30,
                'Potion' => 20,
                'Secret Scroll' => 10,
                default => 0,
            };
            // The store has a single special (non-Loot) item a day; as before, once
            // it is chosen, nothing after it in the (random) order is on offer.
            if (\e_rand(1, 100) > $chance || ($this->store && $special)) {
                $off[] = (int) $item['itemid'];
            } elseif ($this->store && $item['class'] !== 'Loot') {
                $special = true;
            }
        }
        \set_module_setting('forbidden', \implode(',', $off), $this->module);
    }

    // Price and limits.

    public static function price(array $item): array
    {
        return [\max(1, (int) $item['gold']), \max(0, (int) $item['gems'])];
    }

    public function limit(): int
    {
        return \max(1, (int) \get_module_setting('maxshopping', $this->module));
    }

    public function doneToday(): bool
    {
        return (int) \get_module_pref('today', $this->module) >= $this->limit();
    }

    /** Whether this item is the store's special offer (not Loot). */
    public function isSpecial(array $item): bool
    {
        return $this->store && (string) $item['class'] !== 'Loot';
    }

    /** Why the special offer cannot be bought now, or null. */
    public function specialRefusal(): ?string
    {
        if ((int) \get_module_pref('boughtspecialtoday', $this->module) !== 0) {
            return 'had';
        }
        if ((int) \get_module_setting('specialoffer', $this->module) < 1) {
            return 'soldout';
        }
        return null;
    }

    // Buying and selling.

    /**
     * Buys one item. Returns null on success, or the reason it failed:
     * limit, unavailable, unique, funds, special-had, special-soldout, refused.
     */
    public function buy(int $itemid, string $class): ?string
    {
        global $session;
        if ($this->doneToday()) {
            return 'limit';
        }
        $item = $this->offered($itemid, $class);
        if ($item === null) {
            return 'unavailable';
        }
        if ($this->isSpecial($item) && ($why = $this->specialRefusal()) !== null) {
            return 'special-' . $why;
        }
        require_once 'modules/inventory/lib/itemhandler.php';
        if ((int) $item['uniqueforplayer'] === 1 && \check_qty_by_id((int) $item['itemid']) >= 1) {
            return 'unique';
        }
        [$gold, $gems] = self::price($item);
        if ($session['user']['gold'] < $gold || $session['user']['gems'] < $gems) {
            return 'funds';
        }
        // Hand over the item first: if the inventory refuses it (full, or an item
        // unique on the server), nothing is paid.
        if (!\add_item_by_id((int) $item['itemid'])) {
            return 'refused';
        }
        $session['user']['gold'] -= $gold;
        $session['user']['gems'] -= $gems;
        \increment_module_pref('today', 1, $this->module);
        if ($this->isSpecial($item)) {
            \set_module_pref('boughtspecialtoday', 1, $this->module);
            \increment_module_setting('specialoffer', -1, $this->module);
        }
        \debuglog("bought {$item['name']} from the ninja merchant for $gold gold and $gems gems");
        return null;
    }

    /**
     * Items the player can sell, with the merchant's price.
     *
     * @return list<array<string, mixed>>
     */
    public function sellable(): array
    {
        global $session;
        $item = Database::prefix('item');
        $inventory = Database::prefix('inventory');
        $rows = Database::getDoctrineConnection()->fetchAllAssociative(
            "SELECT i.name, i.itemid, i.gold, i.gems, inv.quantity FROM $item AS i INNER JOIN ("
            . " SELECT itemid, COUNT(itemid) AS quantity FROM $inventory WHERE userid = :user GROUP BY itemid"
            . ") AS inv ON i.itemid = inv.itemid WHERE i.sellable = 1 ORDER BY i.class DESC, i.name DESC",
            ['user' => (int) $session['user']['acctid']]
        );
        foreach ($rows as &$row) {
            $row['gold'] = (int) \round((int) $row['gold'] * self::SELL_RATE);
            $row['gems'] = (int) \round((int) $row['gems'] * self::SELL_RATE);
        }
        return $rows;
    }

    /**
     * Sells one item. Returns the row sold (with the price paid), or null
     * when the player does not own a sellable one of it, or the limit is reached.
     *
     * @return array<string, mixed>|null
     */
    public function sell(int $itemid): ?array
    {
        global $session;
        if ($this->doneToday()) {
            return null;
        }
        foreach ($this->sellable() as $row) {
            if ((int) $row['itemid'] !== $itemid) {
                continue;
            }
            require_once 'modules/inventory/lib/itemhandler.php';
            if ((int) \remove_item_by_id($itemid) < 1) {
                return null;
            }
            $session['user']['gold'] += (int) $row['gold'];
            $session['user']['gems'] += (int) $row['gems'];
            \increment_module_pref('today', 1, $this->module);
            \debuglog("sold {$row['name']} to the ninja merchant for {$row['gold']} gold and {$row['gems']} gems");
            return $row;
        }
        return null;
    }

    // Pages.

    /** The table with an item's details. */
    public static function showItem(array $item): void
    {
        [$gold, $gems] = self::price($item);
        \rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999' width=95% align=left>");
        \rawoutput("<tr class='trlight'><td>" . \htmlspecialchars(\translate_inline('Category')) . '</td><td>' . \htmlspecialchars((string) $item['class']) . '</td></tr>');
        \rawoutput("<tr class='trdark'><td>" . \htmlspecialchars(\translate_inline('Name of the item')) . '</td>');
        \output_notl('<td>%s</td></tr>', (string) $item['name'], true);
        \rawoutput("<tr class='trlight'><td>" . \htmlspecialchars(\translate_inline('Description')) . '</td>');
        \output_notl('<td>%s</td></tr>', (string) $item['description'], true);
        \rawoutput("<tr class='trdark'><td>" . \htmlspecialchars(\translate_inline('Gold Cost')) . "</td><td>$gold</td></tr>");
        \rawoutput("<tr class='trlight'><td>" . \htmlspecialchars(\translate_inline('Gem Cost')) . "</td><td>$gems</td></tr>");
        if ((int) $item['charges'] > 0) {
            \rawoutput("<tr class='trdark'><td>" . \htmlspecialchars(\translate_inline('Charges')) . '</td><td>' . (int) $item['charges'] . '</td></tr>');
        }
        \rawoutput('</table>');
    }

    /** The buy nav for an item the player is looking at. */
    public function buyNav(array $item, string $class): void
    {
        global $session;
        require_once 'modules/inventory/lib/itemhandler.php';
        [$gold, $gems] = self::price($item);
        if ((int) $item['uniqueforplayer'] === 1 && \check_qty_by_id((int) $item['itemid']) >= 1) {
            \addnav('Buy this item (you already have it!)', '');
        } elseif ($this->doneToday()) {
            \addnav('Buy this item (no more shopping today)', '');
        } elseif ($session['user']['gold'] >= $gold && $session['user']['gems'] >= $gems) {
            \addnav('Buy this item', $this->link . 'op=buy&category=' . \rawurlencode($class) . '&item=' . (int) $item['itemid']);
        } else {
            \addnav('Buy this item (not enough funds!)', '');
        }
    }

    /** The sell list as navs. */
    public function sellNavs(): void
    {
        \addnav('Sell');
        foreach ($this->sellable() as $row) {
            \addnav(
                ['`3Sell %s`3 (`^%s gold,`g %s gems`3) x%s', $row['name'], $row['gold'], $row['gems'], $row['quantity']],
                $this->link . 'op=sell&sellitem=' . (int) $row['itemid']
            );
        }
    }

    // Pages shared by the forest merchant and the village store.

    /** The items of one category */
    public function askPage(string $class): void
    {
        $items = \in_array($class, $this->categories(), true) ? $this->items($class) : [];
        if ($items === []) {
            \output("`^\"Ahh, sadly I have nothing today in the category %s.... okay, maybe tomorrow is a better day `4^^`^\"`2, says the strange dealer.", $class);
            return;
        }
        \output("`^\"Ahh, so you want to my %s.... okay, here we go, these are items I have today, tomorrow may be more... tomorrow may be less, you know, economy...\"`2, says the strange dealer.", $class);
        foreach ($items as $item) {
            \addnav(['`^Ask about %s', $item['name']], $this->link . 'op=look&category=' . \rawurlencode($class) . '&item=' . (int) $item['itemid']);
        }
    }

    /** What the merchant says after a purchase */
    public function boughtText(?string $why): void
    {
        switch ($why) {
            case null:
                \output("`^\"Gee, thanks for buying. I hope you will be another satisfied customer. Please bear in mind to use your bought item with care. Else they might sue me or something...\"`2, states the merchant.");
                if ($this->doneToday()) {
                    \output("`n`n`^\"Well, that's what I can do for you... See you later!\"");
                }
                break;
            case 'limit':
                \output("`^\"Gee... you already were here often enough today. Come back tomorrow, I have to hit the road again!\"`2");
                break;
            case 'funds':
                \output("`^\"Hey, hey, that's not enough money! I'm a merchant, not a charity.\"`2");
                break;
            case 'unique':
                \output("`^\"You already have one of these. One is all anybody needs, believe me.\"`2");
                break;
            case 'refused':
                \output("`^\"Hmm, I can't hand that one over to you right now. Maybe your pockets are full, maybe it's spoken for. Come back later.\"`2");
                break;
            case 'special-had':
                \output("`n`\$Sadly, you have had your special today. You know, others might want it too, you don't need to hog stuff... sorry.`^\"");
                break;
            case 'special-soldout':
                \output("`n`\$Sadly, we have sold out our entire special stock... sorry.`^\"");
                break;
            default:
                \output("`^\"Ahh, sadly I don't have that one any more today.... okay, maybe tomorrow is a better day `4^^`^\"`2, says the strange dealer.");
        }
    }

    /** The sell page */
    public function sellPage(bool $forest): void
    {
        $sellitem = (int) \httpget('sellitem');
        if ($forest) {
            \addnav('Navigation');
            \addnav('Leave', $this->link . 'op=leave');
        }
        if ($sellitem > 0) {
            $sold = $this->sell($sellitem);
            if ($sold === null) {
                \output("`^\"Hm? You don't have that, or I've bought enough for today.\"`2");
            } else {
                \output("`^\"Thanks, here is your money.\"`2");
            }
            if ($this->doneToday()) {
                \output("`n`n`^\"Well, that's what I can do for you... See you later!\"");
                return;
            }
            \addnav('Back to the Ninja Merchant', $this->link . 'op=investigate');
        } else {
            \addnav('Back to the Ninja Merchant', $this->link . 'op=investigate');
            \output("`^\"So you want to sell your stuff, eh? Ok, if you have something I want, then I pay you a good price. Remember, you can sell me or buy from me %s items a day.\"`2", $this->limit());
            if ($this->doneToday()) {
                \output("`n`n`^\"Gee... you already were here often enough today. Come back tomorrow, I have to hit the road again!\"`2");
                return;
            }
        }
        \output("`n`nYou can click on an item to `bsell it instantly`b to the merchant. The quantity is shown behind, i.e. x2 means you have 2 items to sell. `n`\$No extra confirmation!");
        if ($this->sellable() === []) {
            \output("`^`n`n\"Oh, you have nothing I am interested in...\"`2, says the merchant.");
        }
        $this->sellNavs();
    }
}
