<?php

declare(strict_types=1);

namespace Shinobi\Modules\HalleysCorner;

require_once __DIR__ . '/Texts.php';

/**
 * Halley's Corner: a quiet tavern where travellers trade the urge to fight
 * for the urge to wander.
 *
 * Drinks turn PvP fights into travels (via the cities module's daily count),
 * the tea heals and calms, a stamp card rewards regulars, Halley tells her
 * story to those who keep coming back, and there is a storyteller, a travel
 * board and postcards for company.
 */
final class Corner
{
    private const MODULE = 'halleyscorner';
    private const URL = 'runmodule.php?module=halleyscorner';
    private const STAMPS_PER_CARD = 10;
    private const POSTCARD_LENGTH = 300;

    /** Visits needed before Halley treats you as known, a regular, a friend. */
    private const TIERS = ['friend' => 40, 'regular' => 15, 'known' => 5, 'stranger' => 0];

    private string $halley;

    public function __construct()
    {
        $this->halley = (string) \get_module_setting('owner', self::MODULE);
    }

    public function run(): void
    {
        $op = (string) \httpget('op');

        require_once 'lib/commentary.php';
        \addcommentary();
        \page_header("%s' Corner", \sanitize($this->halley));
        \output(Texts::TITLE, $this->halley);
        \villagenav();
        if ($op !== '') {
            \addnav([Texts::NAV_BACK, $this->halley], self::URL);
        }

        switch ($op) {
            case 'rootbeer':
                $this->rootBeer();
                break;
            case 'brew':
                $this->brew();
                break;
            case 'tea':
                $this->tea();
                break;
            case 'water':
                \output(self::pick(Texts::WATER), $this->halley);
                break;
            case 'gossip':
                \output(self::pick(Texts::GOSSIP), $this->halley);
                break;
            case 'past':
                $this->past();
                break;
            case 'story':
                \output(Texts::STORY_INTRO);
                \output(self::pick(Texts::STORIES));
                break;
            case 'board':
                \output(Texts::BOARD_INTRO);
                \commentdisplay('', 'halleyscorner-board', Texts::BOARD_PROMPT, 25, Texts::BOARD_VERB);
                break;
            case 'talk':
                \commentdisplay(Texts::TALK_INTRO, 'halleyscorner', Texts::TALK_PROMPT, 20, Texts::TALK_VERB);
                break;
            case 'postcard':
                $this->postcardForm();
                break;
            case 'postcard_send':
                $this->postcardSend();
                break;
            default:
                $this->arrive();
                break;
        }

        \page_footer();
    }

    // Arriving.

    private function arrive(): void
    {
        global $session;

        $firstToday = !\get_module_pref('visitedtoday', self::MODULE);
        if ($firstToday) {
            \set_module_pref('visitedtoday', 1, self::MODULE);
            \increment_module_pref('visits', 1, self::MODULE);
        }

        \output(self::pick(Texts::ARRIVAL[self::timeOfDay()]), $this->halley);
        \output(Texts::ARRIVAL_SEAT);
        \output(self::pick(Texts::GREETING[$this->tier()]), $this->halley, $session['user']['name']);
        if ($firstToday) {
            \output(Texts::FIRST_TODAY);
        }
        \output_notl('`n`n');
        \output(self::pick(Texts::PATRONS));

        $this->menu();
        \output(Texts::CARD, (int) \get_module_pref('stamps', self::MODULE), self::STAMPS_PER_CARD);
        $this->navs();
    }

    private function menu(): void
    {
        \output(Texts::MENU_INTRO);
        if ($this->travelling()) {
            \output(Texts::MENU_ROOTBEER, $this->setting('cost'), $this->setting('gain'));
            \output(Texts::MENU_BREW, $this->setting('brewcost'), $this->setting('brewgain'));
        }
        \output(Texts::MENU_TEA, $this->setting('teacost'));
        \output(Texts::MENU_WATER);
        if ($this->travelling()) {
            \output(Texts::MENU_LIMIT, (int) \get_module_pref('drinkstoday', self::MODULE), $this->setting('dailylimit'));
        } else {
            \output(Texts::NO_CITIES);
        }
    }

    private function navs(): void
    {
        global $session;
        $pvp = (int) $session['user']['playerfights'];

        \addnav(Texts::NAV_ORDERS);
        if ($this->travelling()) {
            if ($pvp >= $this->setting('cost')) {
                \addnav([Texts::NAV_ROOTBEER, $this->setting('cost')], self::URL . '&op=rootbeer');
            }
            if ($pvp >= $this->setting('brewcost') && !\get_module_pref('brewtoday', self::MODULE)) {
                \addnav([Texts::NAV_BREW, $this->setting('brewcost')], self::URL . '&op=brew');
            }
        }
        if ($pvp >= $this->setting('teacost') && !\get_module_pref('teatoday', self::MODULE)) {
            \addnav([Texts::NAV_TEA, $this->setting('teacost')], self::URL . '&op=tea');
        }
        \addnav(Texts::NAV_WATER, self::URL . '&op=water');

        \addnav(Texts::NAV_TALKS);
        \addnav(Texts::NAV_GOSSIP, self::URL . '&op=gossip');
        \addnav(Texts::NAV_PAST, self::URL . '&op=past');
        \addnav(Texts::NAV_STORY, self::URL . '&op=story');

        \addnav(Texts::NAV_OTHER);
        \addnav(Texts::NAV_TALK, self::URL . '&op=talk');
        \addnav(Texts::NAV_BOARD, self::URL . '&op=board');
        if ($this->setting('postcardlimit') > 0) {
            \addnav(Texts::NAV_POSTCARD, self::URL . '&op=postcard');
        }
    }

    // Drinks.

    private function rootBeer(): void
    {
        $cost = $this->setting('cost');
        $gain = $this->setting('gain');
        if (!$this->canTrade($cost)) {
            return;
        }
        \increment_module_pref('drinkstoday', 1, self::MODULE);
        \output(self::pick(Texts::ROOTBEER), $this->halley);
        $this->trade($cost, $gain);
        \output(Texts::ROOTBEER_RESULT, $gain, $cost);
        $this->stamp();
        $this->pvpLeft();
    }

    private function brew(): void
    {
        if (\get_module_pref('brewtoday', self::MODULE)) {
            \output(Texts::BREW_DONE, $this->halley);
            return;
        }
        $cost = $this->setting('brewcost');
        $gain = $this->setting('brewgain');
        if (!$this->canTrade($cost)) {
            return;
        }
        \set_module_pref('brewtoday', 1, self::MODULE);
        \increment_module_pref('drinkstoday', 1, self::MODULE);
        \output(self::pick(Texts::BREW), $this->halley);
        $this->trade($cost, $gain);
        \output(Texts::BREW_RESULT, $gain, $cost);
        $this->pvpLeft();
    }

    private function tea(): void
    {
        global $session;

        if (\get_module_pref('teatoday', self::MODULE)) {
            \output(Texts::TEA_DONE, $this->halley);
            return;
        }
        $cost = $this->setting('teacost');
        if ($session['user']['playerfights'] < $cost) {
            \output(Texts::NOT_ENOUGH_PVP, $this->halley);
            return;
        }
        \set_module_pref('teatoday', 1, self::MODULE);
        $session['user']['playerfights'] -= $cost;

        $max = (int) $session['user']['maxhitpoints'];
        $before = (int) $session['user']['hitpoints'];
        $healed = 0;
        if ($before < $max) {
            $healed = min($max - $before, (int) ceil($max * $this->setting('teaheal') / 100));
            $session['user']['hitpoints'] = $before + $healed;
        }
        \apply_buff('halleystea', [
            'name' => Texts::TEA_BUFF_NAME,
            'rounds' => 10,
            'defmod' => 1.05,
            'roundmsg' => Texts::TEA_BUFF_ROUND,
            'wearoff' => Texts::TEA_BUFF_END,
            'schema' => 'module-' . self::MODULE,
        ]);
        \output(self::pick(Texts::TEA), $this->halley);
        \output(Texts::TEA_RESULT, $healed);
        $this->pvpLeft();
        \debuglog("traded $cost pvp for Halley's tea, healed $healed");
    }

    /**
     * Whether a travel drink may be served: travel exists, the daily limit
     * is not reached, and the player has the fights to trade.
     */
    private function canTrade(int $cost): bool
    {
        global $session;

        if (!$this->travelling()) {
            \output(Texts::NO_CITIES);
            return false;
        }
        if ((int) \get_module_pref('drinkstoday', self::MODULE) >= $this->setting('dailylimit')) {
            \output(Texts::LIMIT_REACHED, $this->halley);
            return false;
        }
        if ($session['user']['playerfights'] < $cost) {
            \output(Texts::NOT_ENOUGH_PVP, $this->halley);
            return false;
        }
        return true;
    }

    private function trade(int $cost, int $gain): void
    {
        global $session;
        $session['user']['playerfights'] -= $cost;
        \increment_module_pref('traveltoday', -$gain, 'cities');
        \debuglog("traded $cost pvp for $gain travels at Halley's Corner");
    }

    private function stamp(): void
    {
        $stamps = (int) \get_module_pref('stamps', self::MODULE) + 1;
        if ($stamps >= self::STAMPS_PER_CARD) {
            $bonus = $this->setting('cardbonus');
            \increment_module_pref('traveltoday', -$bonus, 'cities');
            \output(Texts::CARD_FULL, $this->halley, $bonus);
            \debuglog("full stamp card at Halley's Corner, $bonus extra travels");
            $stamps = 0;
        }
        \set_module_pref('stamps', $stamps, self::MODULE);
        \output(Texts::CARD, $stamps, self::STAMPS_PER_CARD);
    }

    private function pvpLeft(): void
    {
        global $session;
        \output(Texts::PVP_LEFT, (int) $session['user']['playerfights']);
    }

    // Halley's story.

    private function past(): void
    {
        $visits = (int) \get_module_pref('visits', self::MODULE);
        $read = (int) \get_module_pref('chapter', self::MODULE);
        if ($read >= \count(Texts::PAST)) {
            \output(Texts::PAST_ALL, $this->halley);
            return;
        }
        [$needed, $text] = Texts::PAST[$read];
        if ($visits < $needed) {
            \output(Texts::PAST_LATER, $this->halley);
            return;
        }
        \output($text, $this->halley);
        \set_module_pref('chapter', $read + 1, self::MODULE);
    }

    // Postcards.

    private function postcardForm(): void
    {
        $left = $this->postcardsLeft();
        \output(Texts::POSTCARD_INTRO, $this->halley, $this->setting('postcardcost'), $left);
        if ($left < 1) {
            return;
        }
        $action = self::URL . '&op=postcard_send';
        \addnav('', $action);
        \rawoutput("<form action='" . htmlspecialchars($action, ENT_QUOTES) . "' method='POST'>");
        \rawoutput("<p><label for='hc-to'>" . self::esc(\translate_inline(Texts::POSTCARD_TO)) . "</label><br>"
            . "<input id='hc-to' name='to' maxlength='50' required></p>");
        \rawoutput("<fieldset><legend>" . self::esc(\translate_inline(Texts::POSTCARD_MOTIF)) . "</legend>");
        $first = true;
        foreach (Texts::MOTIFS as $key => $motif) {
            \rawoutput("<label><input type='radio' name='motif' value='" . $key . "'" . ($first ? ' checked' : '') . "> "
                . self::esc(\translate_inline($motif)) . "</label><br>");
            $first = false;
        }
        \rawoutput("</fieldset>");
        \rawoutput("<p><label for='hc-text'>" . self::esc(sprintf(\translate_inline(Texts::POSTCARD_TEXT), self::POSTCARD_LENGTH)) . "</label><br>"
            . "<textarea id='hc-text' name='text' rows='4' cols='50' maxlength='" . self::POSTCARD_LENGTH . "' required></textarea></p>");
        \rawoutput("<p><button type='submit' class='button'>" . self::esc(\translate_inline(Texts::POSTCARD_SEND)) . "</button></p></form>");
    }

    private function postcardSend(): void
    {
        global $session;

        if ($this->postcardsLeft() < 1) {
            \output(Texts::POSTCARD_LIMIT, $this->halley);
            return;
        }
        $cost = $this->setting('postcardcost');
        if ($session['user']['gold'] < $cost) {
            \output(Texts::POSTCARD_GOLD, $this->halley);
            return;
        }
        $text = trim(stripslashes((string) \httppost('text')));
        $text = mb_substr(str_replace(["\r\n", "\r", "\n"], ' ', $text), 0, self::POSTCARD_LENGTH);
        if ($text === '') {
            \output(Texts::POSTCARD_EMPTY, $this->halley);
            return;
        }
        $motifKey = (string) \httppost('motif');
        $motif = Texts::MOTIFS[$motifKey] ?? array_values(Texts::MOTIFS)[0];

        $login = trim(stripslashes((string) \httppost('to')));
        $sql = "SELECT acctid, name FROM " . \db_prefix('accounts')
            . " WHERE login = '" . \db_real_escape_string($login) . "' LIMIT 1";
        $row = $login === '' ? false : \db_fetch_assoc(\db_query($sql));
        if (!$row) {
            \output(Texts::POSTCARD_NOT_FOUND, $this->halley);
            return;
        }
        if ((int) $row['acctid'] === (int) $session['user']['acctid']) {
            \output(Texts::POSTCARD_SELF, $this->halley);
            return;
        }

        $session['user']['gold'] -= $cost;
        \increment_module_pref('postcardstoday', 1, self::MODULE);
        $motifText = \translate_inline($motif);
        \systemmail(
            (int) $row['acctid'],
            [Texts::POSTCARD_SUBJECT],
            [Texts::POSTCARD_BODY, $session['user']['name'], $motifText, $text],
            (int) $session['user']['acctid']
        );
        \output(Texts::POSTCARD_SENT, $this->halley, $row['name'], $motifText);
        \debuglog("sent a postcard from Halley's Corner to {$row['acctid']} for $cost gold");
    }

    private function postcardsLeft(): int
    {
        return max(0, $this->setting('postcardlimit') - (int) \get_module_pref('postcardstoday', self::MODULE));
    }

    // Helpers.

    private function tier(): string
    {
        $visits = (int) \get_module_pref('visits', self::MODULE);
        foreach (self::TIERS as $tier => $needed) {
            if ($visits >= $needed) {
                return $tier;
            }
        }
        return 'stranger';
    }

    /** Morning, day, evening or night, by the game clock. */
    private static function timeOfDay(): string
    {
        $hour = (int) gmdate('G', \function_exists('gametime') ? (int) \gametime() : time());
        if ($hour >= 5 && $hour < 11) {
            return 'morning';
        }
        if ($hour >= 11 && $hour < 17) {
            return 'day';
        }
        if ($hour >= 17 && $hour < 22) {
            return 'evening';
        }
        return 'night';
    }

    /** Travel drinks need the cities module, which keeps the daily travel count. */
    private function travelling(): bool
    {
        return \is_module_active('cities');
    }

    private function setting(string $name): int
    {
        return max(0, (int) \get_module_setting($name, self::MODULE));
    }

    /**
     * @param list<string> $variants
     */
    private static function pick(array $variants): string
    {
        return $variants[\e_rand(0, \count($variants) - 1)];
    }

    private static function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
