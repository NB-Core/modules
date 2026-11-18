<?php

declare(strict_types=1);

namespace Shinobi\Modules\BijuuHunters;

use Lotgd\MySQL\Database;

require_once __DIR__ . '/Texts.php';

/**
 * Bijuu Hunters: Hidan and Kakuzu of the Akatsuki in the forest.
 *
 * They hunt Jinchuuriki (players whose mount is marked as a Bijuu) and
 * players with a bounty on their head. Everyone else just gets caught up
 * in their argument. A defeated Jinchuuriki has their tailed beast sealed
 * for some days; Ero-Sennin can remove the seal for favours.
 *
 * The fight itself runs in bijuuhunters_runevent(), because battle.php has
 * to be included there.
 */
final class Hunters
{
    public const MODULE = 'bijuuhunters';

    private string $link;

    public function __construct(string $link)
    {
        $this->link = $link;
    }

    // Who they are after.

    /** Whether the player's mount is a Bijuu. */
    public static function isJinchuuriki(): bool
    {
        global $playermount;
        $id = (\is_array($playermount) && isset($playermount['mountid'])) ? (int) $playermount['mountid'] : 0;
        return $id > 0 && (bool) \get_module_objpref('mounts', $id, 'bijuu', self::MODULE);
    }

    /** Open bounty on the player, in gold. */
    public static function bounty(int $acctid): int
    {
        return (int) Database::getDoctrineConnection()->fetchOne(
            'SELECT COALESCE(SUM(amount), 0) FROM ' . Database::prefix('bounty') . ' WHERE status = 0 AND target = :id',
            ['id' => $acctid]
        );
    }

    public static function hasBounty(int $acctid): bool
    {
        return self::bounty($acctid) >= (int) \get_module_setting('minbounty', self::MODULE);
    }

    public static function sealedDays(): int
    {
        return (int) \get_module_pref('sealeddays', self::MODULE);
    }

    /** Forest event chance: the targets meet them more often than bystanders. */
    public static function chance(): int
    {
        global $session;
        if (self::sealedDays() <= 0 && (self::isJinchuuriki() || self::hasBounty((int) $session['user']['acctid']))) {
            return \max(0, (int) \get_module_setting('targetchance', self::MODULE));
        }
        return \max(0, (int) \get_module_setting('chance', self::MODULE));
    }

    // The event, up to the fight.

    /**
     * Handles every op except the fight. Returns false when the fight should
     * run (the caller includes battle.php then).
     */
    public function handle(string $op): bool
    {
        global $session;
        $session['user']['specialinc'] = 'module:' . self::MODULE;
        if (self::sealedDays() > 0 && (string) $session['user']['badguy'] === '') {
            // With the beast sealed, there is nothing in you for them to find.
            $this->end(self::pick(Texts::SEALED));
            return true;
        }

        switch ($op) {
            case '':
                \set_module_pref('triedhide', 0, self::MODULE);
                \output(self::pick(Texts::ARRIVAL));
                \addnav(Texts::NAV_TALK, $this->link . 'op=talk');
                \addnav(Texts::NAV_CHALLENGE, $this->link . 'op=challenge');
                \addnav(Texts::NAV_RUN, $this->link . 'op=run');
                return true;
            case 'talk':
                $this->talk();
                return true;
            case 'run':
                $this->flee();
                return true;
            case 'pay':
                $this->pay();
                return true;
            case 'hide':
                $this->hide();
                return true;
            case 'challenge':
                $this->startFight();
                return false;
            default:
                // fight / combat: the battle goes on, if there is one.
                if ((string) $session['user']['badguy'] === '') {
                    $this->end(Texts::RUN_AWAY);
                    return true;
                }
                return false;
        }
    }

    private function talk(): void
    {
        global $session;
        if (self::isJinchuuriki()) {
            \output(Texts::TALK_JINCHURIKI);
            $this->targetNavs();
            return;
        }
        if (self::hasBounty((int) $session['user']['acctid'])) {
            \output(Texts::TALK_BOUNTY);
            $this->targetNavs();
            return;
        }
        \output(Texts::TALK_NEUTRAL);
        if (\e_rand(1, 4) === 1) {
            $gold = 10 * \max(1, (int) $session['user']['level']);
            $session['user']['gold'] += $gold;
            \output(Texts::TALK_PURSE, $gold);
            \debuglog("found $gold gold dropped by Kakuzu");
        }
        $session['user']['specialinc'] = '';
    }

    private function flee(): void
    {
        global $session;
        if (self::isJinchuuriki()) {
            \output(Texts::RUN_JINCHURIKI);
            $this->targetNavs();
            return;
        }
        if (self::hasBounty((int) $session['user']['acctid'])) {
            \output(Texts::RUN_BOUNTY);
            $this->targetNavs();
            return;
        }
        $this->end(Texts::RUN_AWAY);
    }

    /** Fight, and the way out that fits: hiding the beast, or paying Kakuzu. */
    private function targetNavs(): void
    {
        global $session;
        \addnav(Texts::NAV_DEFEND, $this->link . 'op=challenge');
        if (self::isJinchuuriki()) {
            if (!\get_module_pref('triedhide', self::MODULE)) {
                \addnav(Texts::NAV_HIDE, $this->link . 'op=hide');
            }
            return;
        }
        \addnav([Texts::NAV_PAY, self::price((int) $session['user']['acctid'])], $this->link . 'op=pay');
    }

    /** What Kakuzu wants to let a wanted player go. */
    private static function price(int $acctid): int
    {
        return (int) \ceil(self::bounty($acctid) * \max(100, (int) \get_module_setting('bribefactor', self::MODULE)) / 100);
    }

    private function pay(): void
    {
        global $session;
        $acctid = (int) $session['user']['acctid'];
        if (self::isJinchuuriki() || !self::hasBounty($acctid)) {
            // Only a wanted player can buy their way out; the beast is not for sale.
            $this->end(Texts::RUN_AWAY);
            return;
        }
        $price = self::price($acctid);
        if ($session['user']['gold'] < $price) {
            \output(Texts::PAY_POOR);
            \addnav(Texts::NAV_DEFEND, $this->link . 'op=challenge');
            return;
        }
        $session['user']['gold'] -= $price;
        \debuglog("paid Kakuzu $price gold to be let go");
        $this->end(\sprintf(\translate_inline(Texts::PAY), self::bounty($acctid), $price), false);
    }

    private function hide(): void
    {
        if (!self::isJinchuuriki() || \get_module_pref('triedhide', self::MODULE)) {
            \output(Texts::HIDE_LOSE);
            \addnav(Texts::NAV_DEFEND, $this->link . 'op=challenge');
            return;
        }
        \set_module_pref('triedhide', 1, self::MODULE);
        if (\e_rand(1, 3) === 1) {
            $this->end(Texts::HIDE_WIN);
            return;
        }
        \output(Texts::HIDE_LOSE);
        \addnav(Texts::NAV_DEFEND, $this->link . 'op=challenge');
    }

    // The fight.

    private function startFight(): void
    {
        global $session;
        \set_module_pref('triedhide', 0, self::MODULE);
        $level = (int) $session['user']['level'];
        $hp = $session['user']['maxhitpoints'] * 1.5;
        $att = $session['user']['attack'] * 1.5;
        $def = $session['user']['defense'] * 1.5;
        $badguyLevel = $level + 1;
        if ($level > 9) {
            $hp *= 1.05;
            $badguyLevel++;
        }
        if ($level < 4) {
            $hp *= 0.9;
            $att *= 0.9;
            $def *= 0.9;
            $badguyLevel--;
        }
        $kakuzu = [
            'creaturename' => Texts::KAKUZU,
            'creaturelevel' => $badguyLevel,
            'creatureweapon' => \translate_inline(Texts::WEAPON_KAKUZU),
            'creatureattack' => $att,
            'creaturedefense' => $def,
            'creaturehealth' => (int) \round($hp),
            'diddamage' => 0,
        ];
        // Hidan cannot die; he only falls apart once Kakuzu no longer sews him together.
        $hidan = [
            'creaturename' => Texts::HIDAN,
            'creaturelevel' => $badguyLevel,
            'creatureweapon' => \translate_inline(Texts::WEAPON_HIDAN),
            'creatureattack' => $att,
            'creaturedefense' => $def,
            'creaturehealth' => \defined('INT_MAX') ? (int) \constant('INT_MAX') : 4294967295,
            'diddamage' => 0,
            'hidehitpoints' => true,
            'fleesifalone' => \translate_inline(Texts::HIDAN_FALLS),
            'noadjust' => 1,
        ];
        $session['user']['badguy'] = \createstring([
            'enemies' => [$kakuzu, $hidan],
            'options' => ['type' => 'bijuuhunters'],
        ]);
        \set_module_pref('target', self::isJinchuuriki() ? 'beast' : (self::hasBounty((int) $session['user']['acctid']) ? 'bounty' : ''), self::MODULE);
        \httpset('op', 'combat');
    }

    public function afterBattle(bool $victory, bool $defeat): void
    {
        global $session;
        $target = (string) \get_module_pref('target', self::MODULE);
        if ($target === '') {
            // A fight started before 2.0 has no stored reason: decide now, as 1.0 did.
            $target = self::isJinchuuriki() ? 'beast' : (self::hasBounty((int) $session['user']['acctid']) ? 'bounty' : '');
        }

        if ($victory) {
            $exp = (int) \min((int) $session['user']['dragonkills'] * 1000, \round($session['user']['experience'] / 10));
            $session['user']['experience'] += $exp;
            \output(Texts::WIN, $exp);
            if ($target === 'bounty') {
                $gold = 50 * \max(1, (int) $session['user']['level']);
                $session['user']['gold'] += $gold;
                \output(Texts::WIN_PURSE, $gold);
            }
            \addnews(Texts::NEWS_WIN, $session['user']['name']);
            \debuglog("beat the Bijuu Hunters and gained $exp experience");
            $this->clearFight();
            return;
        }
        if (!$defeat) {
            require_once 'lib/fightnav.php';
            \fightnav(true, false);
            return;
        }

        if ($target === 'beast') {
            // They take the beast's power, not the player's life.
            $days = \max(1, (int) \get_module_setting('seallength', self::MODULE));
            \output(Texts::SEALED_NOW);
            \output(Texts::SEALED_DAYS, $days);
            \strip_buff('mount');
            \set_module_pref('sealeddays', $days, self::MODULE);
            $session['user']['hitpoints'] = 1;
            \debuglog("had their tailed beast sealed by the Bijuu Hunters for $days days");
            $this->clearFight();
            return;
        }

        if ($target === 'bounty') {
            \output(Texts::BOUNTY_TAKEN);
            \addnews(Texts::NEWS_BOUNTY, $session['user']['name']);
            $collected = self::collectBounties((int) $session['user']['acctid']);
            \debuglog("had $collected gold of bounties collected by the Bijuu Hunters");
        } else {
            \output(Texts::DEAD);
            \addnews(Texts::NEWS_DEAD, $session['user']['name']);
        }
        $session['user']['hitpoints'] = 0;
        $session['user']['alive'] = 0;
        $this->clearFight();
        \addnav(Texts::NAV_RETURN);
        \addnav(Texts::NAV_SHADES, 'shades.php');
    }

    /** Marks the player's open bounties as collected (by nobody: Akatsuki keeps the money). */
    private static function collectBounties(int $acctid): int
    {
        $conn = Database::getDoctrineConnection();
        $table = Database::prefix('bounty');
        $now = \date('Y-m-d H:i:s');
        $total = (int) $conn->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) FROM $table WHERE status = 0 AND target = :id AND setdate <= :now",
            ['id' => $acctid, 'now' => $now]
        );
        $conn->executeStatement(
            "UPDATE $table SET status = 1, winner = 0, windate = :now WHERE status = 0 AND target = :id AND setdate <= :now",
            ['id' => $acctid, 'now' => $now]
        );
        return $total;
    }

    private function clearFight(): void
    {
        global $session;
        $session['user']['badguy'] = '';
        $session['user']['specialinc'] = '';
        \set_module_pref('target', '', self::MODULE);
    }

    private function end(string $text, bool $translate = true): void
    {
        global $session;
        if ($translate) {
            \output($text);
        } else {
            \output_notl('%s', $text);
        }
        $session['user']['specialinc'] = '';
    }

    // The seal outside the event.

    /** New day: the sealed beast gives no buff and no extra turns, and the seal fades. */
    public static function newday(): void
    {
        global $session, $playermount;
        $days = self::sealedDays();
        if ($days <= 0 || !self::isJinchuuriki()) {
            return;
        }
        \strip_buff('mount');
        $name = (string) ($playermount['mountname'] ?? '');
        \output(Texts::NEWDAY_SEALED, $name, $days);
        $turns = \min((int) ($playermount['mountforestfights'] ?? 0), \max(0, (int) $session['user']['turns']));
        if ($turns > 0) {
            $session['user']['turns'] -= $turns;
            \output(Texts::NEWDAY_TURNS, $turns);
        }
        if ($days === 1) {
            \output(Texts::NEWDAY_LAST, $name);
        }
        \set_module_pref('sealeddays', $days - 1, self::MODULE);
    }

    /** Ero-Sennin's offer to remove the seal (shown at his bath house). */
    public static function eroNav(): void
    {
        if (self::canRemoveSeal()) {
            \addnav(Texts::NAV_ERO, 'runmodule.php?module=' . self::MODULE . '&op=ero');
        }
    }

    private static function canRemoveSeal(): bool
    {
        return self::sealedDays() > 0
            && (int) \get_module_pref('favour', 'erosennin') >= (int) \get_module_setting('erofavours', self::MODULE);
    }

    public static function run(): void
    {
        global $session;
        \page_header(Texts::ERO_TITLE);
        if ((string) \httpget('op') === 'ero' && self::canRemoveSeal()) {
            $favour = (int) \get_module_pref('favour', 'erosennin');
            \set_module_pref('favour', $favour - (int) \get_module_setting('erofavours', self::MODULE), 'erosennin');
            \set_module_pref('sealeddays', 0, self::MODULE);
            \output(Texts::ERO);
            \debuglog('had the Bijuu seal removed by Ero-Sennin');
        } else {
            \output(Texts::ERO_NO);
        }
        // Leaving his bath house ends that event, too.
        $session['user']['specialinc'] = '';
        \addnav(Texts::NAV_FOREST, 'forest.php');
        \page_footer();
    }

    /**
     * @param list<string> $variants
     */
    private static function pick(array $variants): string
    {
        return $variants[\e_rand(0, \count($variants) - 1)];
    }
}
