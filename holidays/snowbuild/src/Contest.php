<?php

declare(strict_types=1);

namespace Shinobi\Modules\Snowbuild;

require_once __DIR__ . '/Texts.php';

use Lotgd\MySQL\Database;

/**
 * The snowman building contest on the village square.
 *
 * A player builds one of four kinds of snowman a day (by default) for a
 * small fee: one in thirty wins first prize (five times the fee, a small
 * buff, and the snowman stands in the square), and the rest range from gold
 * back to a heap of slush. Open between the start and end settings.
 */
final class Contest
{
    private const MODULE = 'snowbuild';
    private const URL = 'runmodule.php?module=snowbuild';

    public function run(): void
    {
        global $session;

        \page_header('The Snowmen');
        \output(Texts::TITLE);
        \villagenav();
        if (!self::isOpen()) {
            \output(Texts::CLOSED);
            \page_footer();
            return;
        }

        $judge = (string) \get_module_setting('judge', self::MODULE);
        $cost = max(0, (int) \get_module_setting('cost', self::MODULE));
        $style = (string) \httpget('style');

        if (\httpget('op') === 'build' && isset(Texts::STYLES[$style]) && $this->triesLeft() > 0 && $session['user']['gold'] >= $cost) {
            $this->build($judge, $cost, $style);
        } else {
            \output(self::pick(Texts::ARRIVAL), $judge);
            if ($this->triesLeft() < 1) {
                \output(Texts::DONE);
            } elseif ($session['user']['gold'] < $cost) {
                \output(Texts::NO_GOLD);
            } else {
                \output(Texts::OFFER, $cost);
                \addnav(Texts::NAV);
                foreach (Texts::STYLES as $key => [$name]) {
                    \addnav([Texts::NAV_BUILD, $name, $cost], self::URL . '&op=build&style=' . $key);
                }
            }
        }
        \page_footer();
    }

    private function build(string $judge, int $cost, string $style): void
    {
        global $session;

        \increment_module_pref('tries', 1, self::MODULE);
        $session['user']['gold'] -= $cost;
        \debuglog("spent $cost gold to build a snowman.");
        [, $dressing] = Texts::STYLES[$style];
        \output(Texts::BUILD, $judge, $cost, \translate_inline($dressing));

        $roll = \e_rand(1, 30);
        if ($roll === 30) {
            \set_module_setting('winner', $session['user']['acctid'], self::MODULE);
            \set_module_setting('winnerstyle', $style, self::MODULE);
            \apply_buff('snowmanwin', [
                'name' => Texts::BUFF, 'rounds' => 10, 'atkmod' => 1.02, 'defmod' => 1.02,
                'roundmsg' => Texts::BUFF_ROUND, 'wearoff' => Texts::BUFF_END, 'schema' => 'module-' . self::MODULE,
            ]);
            $reward = 5 * $cost;
            $session['user']['gold'] += $reward;
            \debuglog("won $reward gold from building a snowman.");
            \output(Texts::RESULT_WINNER, $judge, $reward - $cost);
        } elseif ($roll > 20) {
            $reward = 3 * $cost;
            $session['user']['gold'] += $reward;
            \debuglog("won $reward gold from building a snowman.");
            \output(Texts::RESULT_GOOD, $judge, $reward - $cost);
        } elseif ($roll > 10) {
            $session['user']['gold'] += $cost;
            \debuglog("won $cost gold from building a snowman.");
            \output(Texts::RESULT_AVERAGE, $judge, $cost);
        } elseif ($roll > 5) {
            \output(Texts::RESULT_POOR, $judge);
        } else {
            \output(Texts::RESULT_SLUSH, $judge, $session['user']['weapon']);
        }
    }

    private function triesLeft(): int
    {
        return max(0, (int) \get_module_setting('perday', self::MODULE) - (int) \get_module_pref('tries', self::MODULE));
    }

    /** The winning snowman's line for the village description, or '' if there is none. */
    public static function winnerLine(): string
    {
        $winner = (int) \get_module_setting('winner', self::MODULE);
        if ($winner < 1) {
            return '';
        }
        $name = Database::getDoctrineConnection()->fetchOne(
            'SELECT name FROM ' . Database::prefix('accounts') . ' WHERE acctid = :id',
            ['id' => $winner]
        );
        if ($name === false || $name === null) {
            \set_module_setting('winner', 0, self::MODULE);
            return '';
        }
        $style = Texts::STYLES[(string) \get_module_setting('winnerstyle', self::MODULE)] ?? Texts::STYLES['classic'];
        return \sprintf(\translate_inline(Texts::VILLAGE_DESC), $name, \translate_inline($style[0]));
    }

    /** Whether the contest is open today, by the start and end settings (mm-dd). */
    public static function isOpen(): bool
    {
        $start = (string) \get_module_setting('start', self::MODULE);
        $end = (string) \get_module_setting('end', self::MODULE);
        if ($start === '' || $end === '') {
            return true;
        }
        if (\is_file('modules/datemanager.php')) {
            require_once 'modules/datemanager.php';
            if (\function_exists('datemanager_datecheck')) {
                return \datemanager_datecheck($start, $end, 0) > 0;
            }
        }
        $today = date('m-d');
        [$from, $to] = [self::monthDay($start), self::monthDay($end)];
        return $from <= $to ? ($today >= $from && $today <= $to) : ($today >= $from || $today <= $to);
    }

    private static function monthDay(string $date): string
    {
        $parts = array_map('intval', explode('-', $date));
        return sprintf('%02d-%02d', $parts[0] ?? 1, $parts[1] ?? 1);
    }

    /**
     * @param list<string> $variants
     */
    private static function pick(array $variants): string
    {
        return $variants[\e_rand(0, \count($variants) - 1)];
    }
}
