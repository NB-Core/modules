<?php

declare(strict_types=1);

namespace Shinobi\Modules\Stocking;

require_once __DIR__ . '/Texts.php';

/**
 * The inn's fireplace, through the year and at Christmas.
 *
 * About four weeks before Christmas the fireplace is decorated and a warm
 * drink a day comes with a small buff; in the last week the stockings go up;
 * Christmas Eve has its own evening; on Christmas morning, and for two days
 * after, every player finds one gift in their stocking, once a year.
 */
final class Fireplace
{
    private const MODULE = 'stocking';

    public function run(): void
    {
        global $session;

        require_once 'lib/commentary.php';
        \addcommentary();
        \page_header('The Fireplace');
        \addnav(Texts::NAV_LEAVE);
        \addnav(Texts::NAV_INN, 'inn.php');
        \output(Texts::TITLE);

        $barmaid = \translate_inline(\getsetting('barmaid', '`%Violet'));
        $bard = \translate_inline(\getsetting('bard', '`^Seth'));
        $days = self::daysToChristmas();

        if ($days > 8 && $days < 30) {
            \output(self::pick(Texts::ADVENT), $barmaid, $bard);
            \output(Texts::COUNTDOWN, $days);
            $this->drink($barmaid);
        } elseif ($days > 1 && $days <= 8) {
            \output(self::pick(Texts::STOCKINGS), $barmaid, $bard);
            \output(Texts::COUNTDOWN, $days);
            $this->drink($barmaid);
        } elseif ($days === 1) {
            \output(Texts::EVE, $bard);
            $this->drink($barmaid);
        } elseif ($days > -3 && $days < 1) {
            $this->stocking();
        } elseif ($days <= -3 && $days > -15) {
            \output(self::pick(Texts::AFTER), $barmaid, $bard);
        } else {
            \output(self::pick(Texts::ORDINARY), $barmaid, $bard);
        }

        \output_notl('`n`n');
        \modulehook('fireplace');
        \commentdisplay('', 'fireplace', Texts::TALK_PROMPT, 25, Texts::TALK_VERB);
        \page_footer();
    }

    /** One warm drink a day in the season, each with a small buff. */
    private function drink(string $barmaid): void
    {
        if (\get_module_pref('gotbuff', self::MODULE)) {
            \output(Texts::ALREADY_DRANK);
            return;
        }
        \set_module_pref('gotbuff', 1, self::MODULE);
        [$text, $name, $round, $end] = self::pick(Texts::DRINKS);
        \output($text, $barmaid);
        \apply_buff('stocking_drink', [
            'name' => $name,
            'rounds' => 15,
            'atkmod' => 1.1,
            'defmod' => 1.1,
            'roundmsg' => $round,
            'wearoff' => $end,
            'schema' => 'module-' . self::MODULE,
        ]);
    }

    /** Christmas morning: one gift per player per year. */
    private function stocking(): void
    {
        global $session;

        $year = (int) date('Y');
        if ((int) \get_module_pref('gotprez', self::MODULE) === $year) {
            \output(Texts::ALREADY_OPENED);
            \output(Texts::MERRY);
            return;
        }
        \set_module_pref('gotprez', $year, self::MODULE);
        \increment_module_setting('prizes', 1, self::MODULE);

        \output(Texts::FIND_STOCKING, $session['user']['name']);
        [$type, $amount, $text] = self::pick(Texts::GIFTS);
        switch ($type) {
            case 'turns':
                $session['user']['turns'] += $amount;
                break;
            case 'gems':
                $session['user']['gems'] += $amount;
                break;
            case 'gold':
                $session['user']['gold'] += $amount;
                break;
            case 'buff':
                \apply_buff('stocking_gift', [
                    'name' => Texts::CHEER_BUFF, 'rounds' => 35, 'atkmod' => 1.2, 'defmod' => 1.2,
                    'roundmsg' => Texts::CHEER_ROUND, 'wearoff' => Texts::CHEER_END, 'schema' => 'module-' . self::MODULE,
                ]);
                break;
            case 'coal':
                \apply_buff('stocking_gift', [
                    'name' => Texts::SPITE_BUFF, 'rounds' => 15, 'atkmod' => 1.05,
                    'roundmsg' => Texts::SPITE_ROUND, 'wearoff' => Texts::SPITE_END, 'schema' => 'module-' . self::MODULE,
                ]);
                break;
        }
        \output($text, $amount);
        \output(Texts::MERRY);
        \debuglog("received a Christmas stocking gift at the inn fireplace: $type $amount");
    }

    /** Days until Christmas (the date setting), counted from now; 0 on the day itself, negative after. */
    public static function daysToChristmas(): int
    {
        [$month, $day] = array_map('intval', explode('-', (string) \get_module_setting('date', self::MODULE)) + [1 => 25]);
        $holiday = mktime(0, 0, 0, $month ?: 12, $day ?: 25, (int) date('Y'));
        return (int) ceil(($holiday - time()) / 86400);
    }

    /**
     * @template T
     * @param list<T> $variants
     * @return T
     */
    private static function pick(array $variants)
    {
        return $variants[\e_rand(0, \count($variants) - 1)];
    }
}
