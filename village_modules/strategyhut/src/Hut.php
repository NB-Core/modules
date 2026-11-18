<?php

declare(strict_types=1);

namespace Shinobi\Modules\StrategyHut;

require_once __DIR__ . '/Texts.php';

/**
 * The Strategy Hut: advice for young shinobi, and a daily game of shogi.
 *
 * Only players up to the "maxdk" setting (by default those who have not
 * yet faced the great beast) and staff see the hut.
 */
final class Hut
{
    private const MODULE = 'strategyhut';
    private const URL = 'runmodule.php?module=strategyhut';
    private const STRATEGIES = ['rush', 'castle', 'trap'];

    /** Whether the current player may visit the hut. */
    public static function mayVisit(): bool
    {
        global $session;
        return (int) $session['user']['dragonkills'] <= (int) \get_module_setting('maxdk', self::MODULE)
            || (int) $session['user']['superuser'] > 0;
    }

    /** Price of one piece of advice: the cost setting per level. */
    public static function price(): int
    {
        global $session;
        return \max(0, (int) \get_module_setting('cost', self::MODULE)) * \max(1, (int) $session['user']['level']);
    }

    public function run(): void
    {
        global $session;
        \page_header('The Strategy Hut');
        \output(Texts::TITLE);
        $mentor = (string) \get_module_setting('mentor', self::MODULE);

        if (!self::mayVisit() || $session['user']['location'] !== \get_module_setting('hutloc', self::MODULE)) {
            \villagenav();
            \page_footer();
            return;
        }

        switch ((string) \httpget('op')) {
            case 'ask':
                $this->advice($mentor);
                break;
            case 'shogi':
                $this->shogi($mentor);
                break;
            case 'move':
                $this->move($mentor, (string) \httpget('how'));
                break;
            default:
                \output(self::pick(Texts::ARRIVAL), $mentor);
                $this->navs(false);
        }
        \villagenav();
        \page_footer();
    }

    private function advice(string $mentor): void
    {
        global $session;
        $price = self::price();
        if ($session['user']['gold'] < $price) {
            \output(Texts::TOO_POOR, $mentor);
            $this->navs(false);
            return;
        }
        $session['user']['gold'] -= $price;
        \debuglog("spent $price gold at the strategy hut");
        \output(Texts::PAID, $mentor, $price);
        $advice = \translate_inline(self::pick(Texts::ADVICE));
        \output_notl('%s', \str_replace('{deathoverlord}', (string) \getsetting('deathoverlord', '`$Ramius'), $advice));
        \output(Texts::AFTER);
        $this->navs(true);
    }

    private function shogi(string $mentor): void
    {
        if (\get_module_pref('shogitoday', self::MODULE)) {
            \output(Texts::SHOGI_DONE, $mentor);
            $this->navs(false);
            return;
        }
        \output(Texts::SHOGI_START, $mentor);
        \addnav(Texts::NAV_HEADER);
        \addnav(Texts::NAV_SHOGI_RUSH, self::URL . '&op=move&how=rush');
        \addnav(Texts::NAV_SHOGI_CASTLE, self::URL . '&op=move&how=castle');
        \addnav(Texts::NAV_SHOGI_TRAP, self::URL . '&op=move&how=trap');
    }

    private function move(string $mentor, string $how): void
    {
        if (!\in_array($how, self::STRATEGIES, true) || \get_module_pref('shogitoday', self::MODULE)) {
            \output(Texts::SHOGI_DONE, $mentor);
            $this->navs(false);
            return;
        }
        \set_module_pref('shogitoday', 1, self::MODULE);
        \output(Texts::SHOGI_PLAY[$how], $mentor);
        // He is very good: one game in five, and only a patient or a tricky one, beats him.
        if ($how !== 'rush' && \e_rand(1, 5) === 1) {
            \output(Texts::SHOGI_WIN, $mentor);
            \apply_buff('strategyhut', [
                'name' => Texts::BUFF,
                'rounds' => 15,
                'defmod' => 1.05,
                'roundmsg' => Texts::BUFF_ROUND,
                'wearoff' => Texts::BUFF_END,
                'schema' => 'module-' . self::MODULE,
            ]);
        } else {
            \output(self::pick(Texts::SHOGI_LOSE), $mentor);
        }
        $this->navs(false);
    }

    private function navs(bool $again): void
    {
        \addnav(Texts::NAV_HEADER);
        \addnav([$again ? Texts::NAV_ASK_AGAIN : Texts::NAV_ASK, self::price()], self::URL . '&op=ask');
        if (!\get_module_pref('shogitoday', self::MODULE)) {
            \addnav(Texts::NAV_SHOGI, self::URL . '&op=shogi');
        }
    }

    /**
     * @param list<string> $variants
     */
    private static function pick(array $variants): string
    {
        return $variants[\e_rand(0, \count($variants) - 1)];
    }
}
