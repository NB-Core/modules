<?php

declare(strict_types=1);

namespace Shinobi\Modules\ClanTrees;

require_once __DIR__ . '/Texts.php';

use Lotgd\MySQL\Database;
use Lotgd\Security\Csrf;

/**
 * Clan Christmas trees: a clan buys a tree from the salesman outside the
 * Clan Halls, its members decorate it with turns, gold (tinsel) and gems
 * (baubles), and a tree past the bonus points lifts its members' spirits.
 *
 * Trees belong to one winter: a tree from an earlier winter is cleared on
 * the clan's next visit, and so is last winter's best tree.
 */
final class Trees
{
    private const MODULE = 'clantrees';
    private const URL = 'runmodule.php?module=clantrees';
    private const CSRF_SCOPE = 'module:clantrees';
    private const RANKING_SIZE = 10;

    /** @var array<string, array{0:int,1:int,2:int,3:string}> Size => [gold, gems, base points, text]. */
    private const SIZES = [
        'small' => [5000, 5, 10, Texts::BOUGHT_SMALL],
        'normal' => [10000, 10, 20, Texts::BOUGHT_MEDIUM],
        'grand' => [25000, 25, 50, Texts::BOUGHT_GRAND],
    ];

    /** @var array<string, array{0:string,1:string,2:string,3:string,4:string,5:string}> What => [user field, intro, form label, button, too little, done]. */
    private const SPEND = [
        'time' => ['turns', Texts::TIME, Texts::FORM_TIME, Texts::BUTTON_TIME, Texts::TOO_LITTLE_TIME, Texts::DONE_TIME],
        'gold' => ['gold', Texts::TINSEL, Texts::FORM_GOLD, Texts::BUTTON_GOLD, Texts::TOO_LITTLE_GOLD, Texts::DONE_GOLD],
        'gems' => ['gems', Texts::BAUBLES, Texts::FORM_GEMS, Texts::BUTTON_GEMS, Texts::TOO_LITTLE_GEMS, Texts::DONE_GEMS],
    ];

    // Hooks.

    public static function village(): void
    {
        if (!\get_module_setting('treebuy', self::MODULE)) {
            return;
        }
        \output(Texts::VILLAGE, \get_module_setting('salesman', self::MODULE));
        $best = self::bestTree();
        if ($best === 0) {
            return;
        }
        if (\get_module_setting('competitive', self::MODULE)) {
            $name = Database::getDoctrineConnection()->fetchOne(
                'SELECT clanname FROM ' . Database::prefix('clans') . ' WHERE clanid = :id',
                ['id' => $best]
            );
            if (\is_string($name)) {
                \output(Texts::VILLAGE_BEST, $name);
                return;
            }
        }
        \output(Texts::VILLAGE_TREES);
    }

    public static function clanHeader(): void
    {
        global $session;

        if (!\get_module_setting('treebuy', self::MODULE) || !$session['user']['alive']) {
            return;
        }
        $clanid = (int) $session['user']['clanid'];
        $detail = \httpget('detail');
        if (\httpget('op') !== '' || ($detail && (int) $detail !== $clanid) || !self::isMember()) {
            return;
        }
        $hasTree = self::hasTree($clanid);
        \addnav(Texts::NAV_HEADER);
        if (!$hasTree) {
            \addnav(Texts::NAV_BUY, self::URL . '&op=buytree');
            \addnav(Texts::NAV_RANKING, self::URL . '&op=ranking');
            \addnav(Texts::NAV_BACK_CLAN);
            \output(Texts::HALL_NO_TREE);
            if ($session['user']['clanrank'] >= \CLAN_LEADER) {
                \output(Texts::HALL_NO_TREE_LEADER, \get_module_setting('salesman', self::MODULE));
            }
            return;
        }
        \addnav(Texts::NAV_TIME, self::URL . '&op=treetime');
        \addnav(Texts::NAV_TINSEL, self::URL . '&op=treetinsel');
        \addnav(Texts::NAV_BAUBLES, self::URL . '&op=treebaubles');
        \addnav(Texts::NAV_RANKING, self::URL . '&op=ranking');
        \addnav(Texts::NAV_BACK_CLAN);

        $points = (int) self::pref($clanid, 'treepoints');
        $best = self::bestTree();
        \output(self::looks($points));
        \output(Texts::TREE_STATS, $points, (int) self::pref($clanid, 'time'), (int) self::pref($clanid, 'gold'), (int) self::pref($clanid, 'gems'));
        if ($clanid === $best) {
            \output(Texts::HALL_BEST);
        }
        if ($points >= (int) \get_module_setting('treebonus', self::MODULE)) {
            \output(Texts::HALL_SPIRIT);
            self::buff((int) \get_module_setting('treereward', self::MODULE) * ($best === $clanid ? 2 : 1));
            if (\get_module_setting('competitive', self::MODULE)) {
                \output(Texts::HALL_MORE);
            }
        }
    }

    // The run page.

    public function run(): void
    {
        global $session;

        $op = (string) \httpget('op');
        $clanid = (int) $session['user']['clanid'];
        if (!\get_module_setting('treebuy', self::MODULE) || !$session['user']['alive'] || !self::isMember()) {
            \redirect('clan.php');
        }
        $hasTree = self::hasTree($clanid);

        if ($op === 'tree') {
            $size = (string) \httpget('size');
            if ($hasTree || !isset(self::SIZES[$size]) || $session['user']['gold'] < self::SIZES[$size][0]
                || $session['user']['gems'] < self::SIZES[$size][1] || !self::validPost()) {
                $op = 'buytree';
            }
        }
        if ($hasTree && $op === 'buytree') {
            $op = '';
        }
        if (!$hasTree && \in_array($op, ['treetime', 'treetinsel', 'treebaubles', 'alter'], true)) {
            $op = 'buytree';
        }
        if ($op === 'alter' && (!isset(self::SPEND[(string) \httpget('what')]) || !self::validPost())) {
            $op = '';
        }
        if (!\in_array($op, ['buytree', 'tree', 'treetime', 'treetinsel', 'treebaubles', 'alter', 'ranking'], true)) {
            \redirect('clan.php');
        }

        \page_header('Christmas Trees');
        switch ($op) {
            case 'buytree':
                $this->shop();
                break;
            case 'tree':
                $this->buy($clanid, (string) \httpget('size'));
                break;
            case 'treetime':
                $this->form('time');
                break;
            case 'treetinsel':
                $this->form('gold');
                break;
            case 'treebaubles':
                $this->form('gems');
                break;
            case 'alter':
                $this->spend($clanid, (string) \httpget('what'));
                break;
            case 'ranking':
                $this->ranking();
                break;
        }
        \page_footer();
    }

    private function shop(): void
    {
        global $session;

        $salesman = \get_module_setting('salesman', self::MODULE);
        \output(Texts::SHOP, $salesman, $salesman);
        \output(Texts::SHOP_SMALL);
        \output(Texts::SHOP_MEDIUM);
        \output(Texts::SHOP_GRAND);
        \addnav(Texts::NAV_LEAVE, 'clan.php');

        $labels = ['small' => Texts::BUY_SMALL, 'normal' => Texts::BUY_MEDIUM, 'grand' => Texts::BUY_GRAND];
        $any = false;
        foreach (self::SIZES as $size => [$gold, $gems]) {
            if ($session['user']['gold'] >= $gold && $session['user']['gems'] >= $gems) {
                \rawoutput(self::postButton(self::URL . '&op=tree&size=' . $size, \translate_inline($labels[$size])) . ' ');
                $any = true;
            }
        }
        if (!$any) {
            \output(Texts::SHOP_TOO_POOR);
        }
    }

    private function buy(int $clanid, string $size): void
    {
        global $session;

        [$gold, $gems, $base, $text] = self::SIZES[$size];
        $session['user']['gold'] -= $gold;
        $session['user']['gems'] -= $gems;
        foreach (['time', 'gold', 'gems'] as $field) {
            self::setPref($clanid, $field, 0);
        }
        self::setPref($clanid, 'basetree', $base);
        self::setPref($clanid, 'treepoints', $base);
        self::setPref($clanid, 'season', self::season());
        self::setPref($clanid, 'havetree', 1);
        \output($text, \get_module_setting('salesman', self::MODULE));
        \debuglog("bought a $size Christmas tree for clan $clanid for $gold gold and $gems gems");
        self::updateBest($clanid, $base);
        \addnav(Texts::NAV_RETURN, 'clan.php');
    }

    private function form(string $what): void
    {
        global $session;

        [, $intro, $label, $button] = self::SPEND[$what];
        \output($intro);
        $field = $what === 'time'
            ? $label . ',range,0,' . (int) $session['user']['turns'] . ',1'
            : $label . ',int';
        $action = self::URL . '&op=alter&what=' . $what;
        require_once 'lib/showform.php';
        \rawoutput("<form action='" . htmlspecialchars($action, ENT_QUOTES) . "' method='POST'>" . self::tokenField());
        \showform(['replystuff' => $field], [], true);
        \addnav('', $action);
        \rawoutput("<input name='reply' id='reply' type='submit' class='button' value='" . htmlspecialchars(\translate_inline($button), ENT_QUOTES) . "'></form>");
        \addnav(Texts::NAV_RETURN, 'clan.php');
    }

    private function spend(int $clanid, string $what): void
    {
        global $session;

        [$field, , , , $tooLittle, $done] = self::SPEND[$what];
        // A whole, non-negative amount: a negative one would pay the player instead.
        $amount = max(0, (int) \httppost('replystuff'));
        \addnav(Texts::NAV_RETURN, 'clan.php');
        if ($amount === 0) {
            \output(Texts::NOTHING);
            return;
        }
        if ($session['user'][$field] < $amount) {
            \output($tooLittle);
            return;
        }
        $session['user'][$field] -= $amount;
        self::setPref($clanid, $what, (int) self::pref($clanid, $what) + $amount);
        \output($done);

        $points = (int) self::pref($clanid, 'basetree')
            + (int) self::pref($clanid, 'gems')
            + intdiv((int) self::pref($clanid, 'time'), 10)
            + (int) floor(sqrt((int) self::pref($clanid, 'gold') / 1000));
        self::setPref($clanid, 'treepoints', $points);
        if (self::updateBest($clanid, $points)) {
            \output(Texts::NEW_BEST);
        }
    }

    private function ranking(): void
    {
        \output(Texts::RANKING_TITLE);
        \output(Texts::RANKING_INTRO);
        \addnav(Texts::NAV_RETURN, 'clan.php');
        $prefs = Database::prefix('module_objprefs');
        $rows = Database::getDoctrineConnection()->fetchAllAssociative(
            'SELECT c.clanname, CAST(p.value AS UNSIGNED) AS points FROM ' . $prefs . ' p'
            . ' JOIN ' . $prefs . ' s ON s.modulename = p.modulename AND s.objtype = p.objtype AND s.objid = p.objid AND s.setting = :season_setting'
            . ' JOIN ' . $prefs . ' h ON h.modulename = p.modulename AND h.objtype = p.objtype AND h.objid = p.objid AND h.setting = :have_setting'
            . ' JOIN ' . Database::prefix('clans') . ' c ON c.clanid = p.objid'
            . ' WHERE p.modulename = :module AND p.objtype = :objtype AND p.setting = :points_setting'
            . ' AND s.value = :season AND h.value = :have'
            . ' ORDER BY points DESC LIMIT ' . self::RANKING_SIZE,
            [
                'module' => self::MODULE, 'objtype' => 'clans', 'points_setting' => 'treepoints',
                'season_setting' => 'season', 'season' => (string) self::season(),
                'have_setting' => 'havetree', 'have' => '1',
            ]
        );
        if ($rows === []) {
            \output(Texts::RANKING_EMPTY);
            return;
        }
        foreach ($rows as $rank => $row) {
            \output(Texts::RANKING_ROW, $rank + 1, $row['clanname'], (int) $row['points']);
        }
    }

    // State.

    /** Whether the clan has a tree this winter; a tree from an earlier winter is cleared. */
    private static function hasTree(int $clanid): bool
    {
        if (!self::pref($clanid, 'havetree')) {
            return false;
        }
        if ((int) self::pref($clanid, 'season') === self::season()) {
            return true;
        }
        foreach (['havetree', 'treepoints', 'basetree', 'time', 'gold', 'gems'] as $field) {
            self::setPref($clanid, $field, 0);
        }
        return false;
    }

    /** This winter's best tree, or 0; last winter's is forgotten. */
    private static function bestTree(): int
    {
        if ((int) \get_module_setting('bestseason', self::MODULE) !== self::season()) {
            return 0;
        }
        return (int) \get_module_setting('besttree', self::MODULE);
    }

    /** Make this clan's tree the best one if it beats the current best; returns whether it became the best. */
    private static function updateBest(int $clanid, int $points): bool
    {
        $best = self::bestTree();
        if ($best !== 0 && $best !== $clanid && $points <= (int) self::pref($best, 'treepoints')) {
            return false;
        }
        \set_module_setting('besttree', $clanid, self::MODULE);
        \set_module_setting('bestseason', self::season(), self::MODULE);
        return $best !== $clanid;
    }

    /** The winter a date belongs to: from July on it counts to the coming December. */
    public static function season(): int
    {
        $year = (int) date('Y');
        return (int) date('n') <= 6 ? $year - 1 : $year;
    }

    private static function isMember(): bool
    {
        global $session;
        return $session['user']['clanid'] && $session['user']['clanrank'] > \CLAN_APPLICANT;
    }

    private static function looks(int $points): string
    {
        foreach (Texts::LOOKS as [$min, $text]) {
            if ($points >= $min) {
                return $text;
            }
        }
        return Texts::LOOKS[\count(Texts::LOOKS) - 1][1];
    }

    private static function buff(int $rounds): void
    {
        if (\get_module_pref('gotbuff', self::MODULE)) {
            return;
        }
        \apply_buff('besttreespirit', [
            'name' => Texts::BUFF, 'rounds' => $rounds, 'defmod' => 1.15,
            'roundmsg' => Texts::BUFF_ROUND, 'wearoff' => Texts::BUFF_END, 'schema' => 'module-' . self::MODULE,
        ]);
        \set_module_pref('gotbuff', 1, self::MODULE);
    }

    private static function pref(int $clanid, string $name): mixed
    {
        return \get_module_objpref('clans', $clanid, $name, self::MODULE);
    }

    private static function setPref(int $clanid, string $name, int $value): void
    {
        \set_module_objpref('clans', $clanid, $name, $value, self::MODULE);
    }

    /**
     * Whether the request is a posted tree purchase or decoration with a valid token.
     * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
     */
    private static function validPost(): bool
    {
        if (!class_exists(Csrf::class)) {
            return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
        }
        return Csrf::validatePostRequest(self::CSRF_SCOPE);
    }

    private static function tokenField(): string
    {
        return class_exists(Csrf::class) ? Csrf::hiddenField(self::CSRF_SCOPE) : '';
    }

    private static function postButton(string $url, string $label): string
    {
        \addnav('', $url);
        return "<form action='" . htmlspecialchars($url, ENT_QUOTES) . "' method='post' style='display:inline'>"
            . self::tokenField()
            . "<input type='submit' class='button' value='" . htmlspecialchars($label, ENT_QUOTES) . "'></form>";
    }
}
