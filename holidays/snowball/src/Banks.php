<?php

declare(strict_types=1);

namespace Shinobi\Modules\Snowball;

require_once __DIR__ . '/Texts.php';

use Lotgd\MySQL\Database;
use Lotgd\Security\Csrf;

/**
 * The Snowy Banks: a snowball war in the gardens over the winter.
 *
 * Throw at a player who is online (quick throw, or a leaping volley that
 * aims better and costs two throws); hits are counted per winter and the
 * best throwers stand on the banks' board; the target gets a mail. Or
 * ambush a passer-by for fun, without points. Throws per day are limited.
 */
final class Banks
{
    private const MODULE = 'snowball';
    private const URL = 'runmodule.php?module=snowball';
    private const CSRF_SCOPE = 'module:snowball';
    private const LEAP_COST = 2;
    private const BOARD_SIZE = 10;

    public function run(): void
    {
        $op = (string) \httpget('op');
        \page_header('Snowy Banks');
        \output(Texts::TITLE);
        \addnav(Texts::NAV_BACK, 'gardens.php');

        $state = self::dateState();
        if ($state <= 0) {
            \output(Texts::CLOSED);
            \page_footer();
            return;
        }
        if ($state === 2) {
            \output(Texts::LENIENCY);
        }

        switch ($op) {
            case 'throw':
                $this->pickTarget();
                break;
            case 'hit':
                $this->throwAt();
                break;
            case 'npc':
                $this->ambush();
                break;
            default:
                $this->banks();
                break;
        }
        \page_footer();
    }

    private function banks(): void
    {
        require_once 'lib/commentary.php';
        \addcommentary();

        \output(self::pick(Texts::ARRIVAL));
        \output(Texts::MY_HITS, (int) \get_module_pref(self::seasonKey('hits'), self::MODULE));
        \output(Texts::MY_TAKEN, (int) \get_module_pref(self::seasonKey('taken'), self::MODULE));

        $left = $this->throwsLeft();
        if ($left > 0) {
            \output(Texts::THROWS_LEFT, $left, $this->limit());
            \addnav(Texts::NAV_ACTIONS);
            \addnav(Texts::NAV_THROW, self::URL . '&op=throw');
            \addnav(Texts::NAV_NPC, self::URL . '&op=npc');
        } else {
            \output(Texts::TOO_COLD);
        }
        $this->board();
        \output_notl('`n');
        \commentdisplay('', 'snowball', Texts::TALK_PROMPT, 20, Texts::TALK_VERB);
    }

    private function board(): void
    {
        \output(Texts::BOARD_TITLE);
        $prefs = Database::prefix('module_userprefs');
        $rows = Database::getDoctrineConnection()->fetchAllAssociative(
            'SELECT a.name, CAST(p.value AS UNSIGNED) AS hits FROM ' . $prefs . ' p'
            . ' JOIN ' . Database::prefix('accounts') . ' a ON a.acctid = p.userid'
            . ' WHERE p.modulename = :module AND p.setting = :setting AND CAST(p.value AS UNSIGNED) > 0'
            . ' ORDER BY hits DESC LIMIT ' . self::BOARD_SIZE,
            ['module' => self::MODULE, 'setting' => self::seasonKey('hits')]
        );
        if ($rows === []) {
            \output(Texts::BOARD_EMPTY);
            return;
        }
        foreach ($rows as $rank => $row) {
            \output(Texts::BOARD_ROW, $rank + 1, $row['name'], (int) $row['hits']);
        }
    }

    private function pickTarget(): void
    {
        global $session;

        if ($this->throwsLeft() < 1) {
            \output(Texts::TOO_COLD);
            return;
        }
        $rows = Database::getDoctrineConnection()->fetchAllAssociative(
            'SELECT acctid, name FROM ' . Database::prefix('accounts')
            . ' WHERE loggedin = 1 AND acctid <> :me ORDER BY login',
            ['me' => (int) $session['user']['acctid']]
        );
        if ($rows === []) {
            \output(Texts::NOBODY);
            $this->spendThrows(1);
            return;
        }
        \output(Texts::PICK);
        $action = self::URL . '&op=hit';
        \addnav('', $action);
        \rawoutput("<form action='" . htmlspecialchars($action, ENT_QUOTES) . "' method='POST'>"
            . (class_exists(Csrf::class) ? Csrf::hiddenField(self::CSRF_SCOPE) : ''));
        \rawoutput("<p><label for='sb-target'>" . self::esc(\translate_inline(Texts::LABEL_TARGET)) . "</label> <select id='sb-target' name='target'>");
        foreach ($rows as $row) {
            \rawoutput("<option value='" . (int) $row['acctid'] . "'>" . self::esc(\full_sanitize($row['name'])) . "</option>");
        }
        \rawoutput("</select></p><fieldset><legend>" . self::esc(\translate_inline(Texts::LABEL_STYLE)) . "</legend>");
        \rawoutput("<label><input type='radio' name='style' value='quick' checked> " . self::esc(\translate_inline(Texts::STYLE_QUICK)) . "</label><br>");
        if ($this->throwsLeft() >= self::LEAP_COST) {
            \rawoutput("<label><input type='radio' name='style' value='leap'> " . self::esc(\translate_inline(Texts::STYLE_LEAP)) . "</label>");
        }
        \rawoutput("</fieldset><p><button type='submit' class='button'>" . self::esc(\translate_inline(Texts::BUTTON_THROW)) . "</button></p></form>");
    }

    private function throwAt(): void
    {
        global $session;

        $valid = class_exists(Csrf::class)
            ? Csrf::validatePostRequest(self::CSRF_SCOPE)
            : ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
        if (!$valid) {
            \output(Texts::EXPIRED);
            return;
        }
        $leap = \httppost('style') === 'leap';
        $cost = $leap ? self::LEAP_COST : 1;
        if ($this->throwsLeft() < $cost) {
            \output($leap ? Texts::NOT_ENOUGH : Texts::TOO_COLD);
            return;
        }
        $target = Database::getDoctrineConnection()->fetchAssociative(
            'SELECT acctid, name, level FROM ' . Database::prefix('accounts')
            . ' WHERE acctid = :id AND loggedin = 1 AND acctid <> :me',
            ['id' => (int) \httppost('target'), 'me' => (int) $session['user']['acctid']]
        );
        if (!$target) {
            \output(Texts::GONE);
            return;
        }
        $this->spendThrows($cost);

        // Better aim for a leaping volley, a little for being stronger than the target.
        $chance = 55 + ($leap ? 20 : 0) + 2 * ((int) $session['user']['level'] - (int) $target['level']);
        $chance = max(25, min(90, $chance));
        $roll = \e_rand(1, 100);
        $name = $target['name'];

        if ($roll <= $chance) {
            \output(self::pick($leap ? Texts::LEAP_HIT : Texts::HIT), $name);
            \increment_module_pref(self::seasonKey('hits'), 1, self::MODULE);
            \increment_module_pref(self::seasonKey('taken'), 1, self::MODULE, (int) $target['acctid']);
            require_once 'lib/systemmail.php';
            \systemmail((int) $target['acctid'], [Texts::MAIL_SUBJECT], [self::pick(Texts::MAIL_BODY), $session['user']['name']]);
            \output(Texts::MAIL_SENT, $name);
        } elseif ($roll <= $chance + (int) ((100 - $chance) * 0.7)) {
            \output(self::pick(Texts::MISS), $name, $name);
        } else {
            \output(self::pick(Texts::COUNTER), $name);
            \increment_module_pref(self::seasonKey('taken'), 1, self::MODULE);
        }
        \addnav(Texts::NAV_ACTIONS);
        if ($this->throwsLeft() > 0) {
            \addnav(Texts::NAV_THROW, self::URL . '&op=throw');
        }
        \addnav(Texts::TITLE_NAV, self::URL);
    }

    private function ambush(): void
    {
        if ($this->throwsLeft() < 1) {
            \output(Texts::TOO_COLD);
            return;
        }
        $this->spendThrows(1);
        \output(self::pick(Texts::NPC));
        \addnav(Texts::NAV_ACTIONS);
        \addnav(Texts::TITLE_NAV, self::URL);
    }

    // Helpers.

    private function limit(): int
    {
        return max(0, (int) \get_module_setting('snowballlimit', self::MODULE));
    }

    private function throwsLeft(): int
    {
        return max(0, $this->limit() - (int) \get_module_pref('throwstoday', self::MODULE));
    }

    private function spendThrows(int $count): void
    {
        \increment_module_pref('throwstoday', $count, self::MODULE);
    }

    /** A pref name for this winter, e.g. "hits2026": December and the following months count to the same winter. */
    public static function seasonKey(string $name): string
    {
        $year = (int) date('Y');
        return $name . ((int) date('n') <= 6 ? $year - 1 : $year);
    }

    /** 1 open, 2 open in the leniency days after the end, 0 closed, -1 bad dates. */
    public static function dateState(): int
    {
        require_once 'modules/datemanager.php';
        return \datemanager_datecheck(
            (string) \get_module_setting('start', self::MODULE),
            (string) \get_module_setting('end', self::MODULE),
            (int) \get_module_setting('leniency', self::MODULE)
        );
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
