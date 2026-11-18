<?php

declare(strict_types=1);

namespace Shinobi\Modules\WinterCastle;

require_once __DIR__ . '/Texts.php';

/**
 * The Winter Castle: the Frost Palace of the Land of Snow, open through an
 * arch of ice beside the village gates for the winter season.
 *
 * Rooms: the great hall with the Advent calendar and Lady Yukiko, the Ice
 * Garden (carve a sculpture, the best of the winter goes on the pedestal),
 * the Banquet Hall (one dish a day, each a small buff), the Lantern Tower
 * (a wish a day and the wish wall), the Hot Spring (a soak a day heals), and
 * the Frost Cellar, where the snow woman asks riddles.
 */
final class Castle
{
    private const MODULE = 'wintercastle';
    private const URL = 'runmodule.php?module=wintercastle';
    private const RIDDLES_PER_DAY = 3;
    private const ADVENT_DOOR24_GOLD = 500;

    public function run(): void
    {
        $op = (string) \httpget('op');

        require_once 'lib/commentary.php';
        \addcommentary();

        if (!self::isOpen() && !self::isStaff()) {
            \page_header('The Winter Castle');
            \output(Texts::CLOSED);
            \villagenav();
            \page_footer();
            return;
        }

        switch ($op) {
            case 'hall':
                $this->hall();
                break;
            case 'advent':
                $this->advent(false);
                break;
            case 'advent_open':
                $this->advent(true);
                break;
            case 'garden':
                $this->garden();
                break;
            case 'carve':
                $this->carve();
                break;
            case 'banquet':
                $this->banquet();
                break;
            case 'eat':
                $this->eat();
                break;
            case 'tower':
                $this->tower(false);
                break;
            case 'lantern':
                $this->tower(true);
                break;
            case 'spring':
                $this->spring(false);
                break;
            case 'soak':
                $this->spring(true);
                break;
            case 'cellar':
                $this->cellar();
                break;
            case 'riddle':
                $this->riddle();
                break;
            case 'answer':
                $this->answer();
                break;
            default:
                $this->gate();
                break;
        }
        \page_footer();
    }

    // The gate.

    private function gate(): void
    {
        \page_header('The Snowy Gate');
        \output(self::pick(Texts::GATE[self::timeOfDay()]));
        \output(Texts::GATE_ASK);
        if (!self::isOpen()) {
            \output(Texts::SU_OUT_OF_SEASON);
        }
        \addnav(Texts::NAV_ROOMS);
        \addnav(Texts::NAV_ENTER, self::URL . '&op=hall');
        \addnav(Texts::NAV_BACK_VILLAGE);
        \villagenav();
    }

    // The great hall.

    private function hall(): void
    {
        \page_header('The Frost Palace');
        \output(Texts::HALL_TITLE);
        \output(Texts::HALL);
        \output(self::pick(Texts::YUKIKO));
        \output(Texts::HALL_ROOMS);
        \output_notl('`n');
        \commentdisplay(Texts::TALK_INTRO, 'wintercastle', Texts::HALL_TALK, 25, Texts::TALK_VERB);

        \addnav(Texts::NAV_ROOMS);
        $day = self::adventDay();
        if ($day !== null && !$this->doorOpened($day)) {
            \addnav(Texts::NAV_ADVENT, self::URL . '&op=advent_open');
        } else {
            \addnav(Texts::NAV_ADVENT_LOOK, self::URL . '&op=advent');
        }
        \addnav(Texts::NAV_GARDEN, self::URL . '&op=garden');
        \addnav(Texts::NAV_BANQUET, self::URL . '&op=banquet');
        \addnav(Texts::NAV_TOWER, self::URL . '&op=tower');
        \addnav(Texts::NAV_SPRING, self::URL . '&op=spring');
        \addnav(Texts::NAV_CELLAR, self::URL . '&op=cellar');
        \addnav(Texts::NAV_BACK_VILLAGE);
        \addnav(Texts::NAV_LEAVE, self::URL);
        \villagenav();
    }

    private function backToHall(): void
    {
        \addnav(Texts::NAV_ROOMS);
        \addnav(Texts::NAV_HALL, self::URL . '&op=hall');
    }

    // The Advent calendar.

    private function advent(bool $open): void
    {
        \page_header('The Advent Calendar');
        \output(Texts::ADVENT_TITLE);
        \output(Texts::ADVENT_INTRO);
        $this->backToHall();

        $day = self::adventDay();
        if ($day === null) {
            \output((int) date('n') === 12 ? Texts::ADVENT_OVER : Texts::ADVENT_NOT_YET);
            return;
        }
        if ($this->doorOpened($day)) {
            \output(Texts::ADVENT_DONE);
        } elseif ($open) {
            $this->openDoor($day);
        } else {
            \addnav(Texts::NAV_ROOMS);
            \addnav(Texts::NAV_ADVENT, self::URL . '&op=advent_open');
        }
        \output(Texts::ADVENT_COUNT, \count($this->openedDoors()));
        \output(Texts::ADVENT_MISSED);
    }

    private function openDoor(int $day): void
    {
        global $session;

        $opened = $this->openedDoors();
        $opened[] = $day;
        \set_module_pref('advent', (string) json_encode(['y' => (int) date('Y'), 'd' => $opened]), self::MODULE);

        [$type, $amount, $text] = Texts::DOORS[$day];
        \output(Texts::ADVENT_TODAY, $day);
        switch ($type) {
            case 'gold':
                $session['user']['gold'] += $amount;
                \output($text, $amount);
                break;
            case 'gems':
                $session['user']['gems'] += $amount;
                \output($text, $amount);
                break;
            case 'turns':
                $session['user']['turns'] += $amount;
                \output($text, $amount);
                break;
            case 'heal':
                \output($text, $this->heal($amount));
                break;
            case 'buff':
                \apply_buff('wintercastle_advent', [
                    'name' => Texts::ADVENT_BUFF,
                    'rounds' => 15,
                    'atkmod' => 1.05,
                    'roundmsg' => Texts::ADVENT_BUFF_ROUND,
                    'wearoff' => Texts::ADVENT_BUFF_END,
                    'schema' => 'module-' . self::MODULE,
                ]);
                \output($text);
                break;
            default:
                \output($text);
                break;
        }
        if ($day === 24) {
            $session['user']['gold'] += self::ADVENT_DOOR24_GOLD;
            \output(Texts::ADVENT_DOOR24_GOLD, self::ADVENT_DOOR24_GOLD);
        }
        \debuglog("opened Advent door $day in the Winter Castle ($type $amount)");
    }

    /** @return list<int> Doors opened this year. */
    private function openedDoors(): array
    {
        $data = json_decode((string) \get_module_pref('advent', self::MODULE), true);
        if (!\is_array($data) || (int) ($data['y'] ?? 0) !== (int) date('Y') || !\is_array($data['d'] ?? null)) {
            return [];
        }
        return array_values(array_map('intval', $data['d']));
    }

    private function doorOpened(int $day): bool
    {
        return \in_array($day, $this->openedDoors(), true);
    }

    /** Today's door, 1 to 24, or null outside the first 24 days of December. */
    private static function adventDay(): ?int
    {
        $day = (int) date('j');
        return ((int) date('n') === 12 && $day <= 24) ? $day : null;
    }

    // The Ice Garden.

    private function garden(): void
    {
        global $session;

        \page_header('The Ice Garden');
        \output(Texts::GARDEN_TITLE);
        \output(Texts::GARDEN);
        $this->showBest();
        $this->backToHall();

        $cost = $this->setting('sculptcost');
        if (\get_module_pref('sculpttoday', self::MODULE)) {
            \output(Texts::GARDEN_DONE);
        } elseif ($session['user']['gold'] < $cost) {
            \output(Texts::GARDEN_GOLD);
        } else {
            \output(Texts::GARDEN_OFFER, $cost);
            \addnav(Texts::NAV_GARDEN);
            foreach (Texts::MOTIFS as $key => $motif) {
                \addnav([Texts::NAV_CARVE, $motif], self::URL . '&op=carve&motif=' . $key);
            }
        }
        \output_notl('`n');
        \commentdisplay('', 'wc_garden', Texts::GARDEN_TALK, 20, Texts::TALK_VERB);
    }

    private function showBest(): void
    {
        $best = json_decode((string) \get_module_setting('bestsculpture', self::MODULE), true);
        if (\is_array($best) && (int) ($best['y'] ?? 0) === self::season()) {
            \output(Texts::GARDEN_BEST, $best['n'], \translate_inline(Texts::MOTIFS[$best['m']] ?? array_values(Texts::MOTIFS)[0]), (int) $best['s']);
        } else {
            \output(Texts::GARDEN_NO_BEST);
        }
    }

    private function carve(): void
    {
        global $session;

        \page_header('The Ice Garden');
        \output(Texts::GARDEN_TITLE);
        $this->backToHall();
        \addnav(Texts::NAV_GARDEN, self::URL . '&op=garden');

        $key = (string) \httpget('motif');
        $cost = $this->setting('sculptcost');
        if (!isset(Texts::MOTIFS[$key]) || \get_module_pref('sculpttoday', self::MODULE)) {
            \output(Texts::GARDEN_DONE);
            return;
        }
        if ($session['user']['gold'] < $cost) {
            \output(Texts::GARDEN_GOLD);
            return;
        }
        $session['user']['gold'] -= $cost;
        \set_module_pref('sculpttoday', 1, self::MODULE);

        $motif = \translate_inline(Texts::MOTIFS[$key]);
        $score = \e_rand(1, 100);
        \output(Texts::CARVE, $motif);
        foreach (Texts::CARVE_VERDICT as [$min, $text]) {
            if ($score >= $min) {
                \output($text, $score);
                break;
            }
        }

        $best = json_decode((string) \get_module_setting('bestsculpture', self::MODULE), true);
        $bestScore = (\is_array($best) && (int) ($best['y'] ?? 0) === self::season()) ? (int) $best['s'] : 0;
        if ($score > $bestScore) {
            \set_module_setting('bestsculpture', (string) json_encode(['y' => self::season(), 'n' => $session['user']['name'], 'm' => $key, 's' => $score]), self::MODULE);
            \output(Texts::CARVE_RECORD);
        }
        if ($score >= 95) {
            $session['user']['gems']++;
            \output(Texts::CARVE_PRIZE_GEM);
        } elseif ($score >= 75) {
            $gold = $cost * 3;
            $session['user']['gold'] += $gold;
            \output(Texts::CARVE_PRIZE_GOLD, $gold);
        }
        \debuglog("carved an ice sculpture in the Winter Castle for $cost gold, score $score");
    }

    // The Banquet Hall.

    private function banquet(): void
    {
        \page_header('The Banquet Hall');
        \output(Texts::BANQUET_TITLE);
        \output(Texts::BANQUET);
        $this->backToHall();
        if (\get_module_pref('feasttoday', self::MODULE)) {
            \output(Texts::BANQUET_DONE);
        } else {
            \output(Texts::BANQUET_OFFER);
            \addnav(Texts::NAV_BANQUET);
            foreach (Texts::DISHES as $key => [$name]) {
                \addnav($name, self::URL . '&op=eat&dish=' . $key);
            }
        }
        \output_notl('`n');
        \commentdisplay('', 'wc_banquet', Texts::BANQUET_TALK, 20, Texts::TALK_VERB);
    }

    private function eat(): void
    {
        \page_header('The Banquet Hall');
        \output(Texts::BANQUET_TITLE);
        $this->backToHall();
        \addnav(Texts::NAV_BANQUET, self::URL . '&op=banquet');

        $key = (string) \httpget('dish');
        if (!isset(Texts::DISHES[$key]) || \get_module_pref('feasttoday', self::MODULE)) {
            \output(Texts::BANQUET_DONE);
            return;
        }
        \set_module_pref('feasttoday', 1, self::MODULE);
        [, $text, $buffName, $fields] = Texts::DISHES[$key];
        \output($text);
        \apply_buff('wintercastle_feast', $fields + [
            'name' => $buffName,
            'wearoff' => Texts::DISH_WEAR_OFF,
            'schema' => 'module-' . self::MODULE,
        ]);
    }

    // The Lantern Tower.

    private function tower(bool $send): void
    {
        \page_header('The Lantern Tower');
        \output(Texts::TOWER_TITLE);
        \output(Texts::TOWER);
        $this->backToHall();

        if (\get_module_pref('lanterntoday', self::MODULE)) {
            \output(Texts::TOWER_DONE);
        } elseif ($send) {
            \set_module_pref('lanterntoday', 1, self::MODULE);
            \output_notl('`n`n');
            \output(self::pick(Texts::LANTERN));
            \apply_buff('wintercastle_hope', [
                'name' => Texts::LANTERN_BUFF,
                'rounds' => 10,
                'regen' => 2,
                'roundmsg' => Texts::LANTERN_BUFF_ROUND,
                'wearoff' => Texts::LANTERN_BUFF_END,
                'schema' => 'module-' . self::MODULE,
            ]);
        } else {
            \output(Texts::TOWER_OFFER);
            \addnav(Texts::NAV_TOWER);
            \addnav(Texts::NAV_LANTERN, self::URL . '&op=lantern');
        }
        \output_notl('`n');
        \commentdisplay('', 'wc_wishes', Texts::TOWER_BOARD, 25, Texts::TOWER_BOARD_VERB);
    }

    // The Hot Spring.

    private function spring(bool $soak): void
    {
        global $session;

        \page_header('The Hot Spring');
        \output(Texts::SPRING_TITLE);
        \output(Texts::SPRING);
        $this->backToHall();

        if (\get_module_pref('soaktoday', self::MODULE)) {
            \output(Texts::SPRING_DONE);
        } elseif ($soak) {
            \set_module_pref('soaktoday', 1, self::MODULE);
            \output_notl('`n`n');
            \output(Texts::SOAK);
            $healed = $this->heal(100);
            \output($healed > 0 ? Texts::SOAK_RESULT : Texts::SOAK_FULL, $healed);
        } else {
            \addnav(Texts::NAV_SPRING);
            \addnav(Texts::NAV_SOAK, self::URL . '&op=soak');
        }
        \output_notl('`n');
        \commentdisplay('', 'wc_spring', Texts::SPRING_TALK, 20, Texts::TALK_VERB);
    }

    // The Frost Cellar.

    private function cellar(): void
    {
        \page_header('The Frost Cellar');
        \output(Texts::CELLAR_TITLE);
        \output(Texts::CELLAR);
        $this->backToHall();
        $left = self::RIDDLES_PER_DAY - (int) \get_module_pref('riddlestoday', self::MODULE);
        if ($left > 0) {
            \output(Texts::CELLAR_LEFT, $left);
            \addnav(Texts::NAV_CELLAR);
            \addnav(Texts::NAV_RIDDLE, self::URL . '&op=riddle');
        } else {
            \output(Texts::CELLAR_DONE);
        }
        \output_notl('`n');
        \commentdisplay('', 'wc_cellar', Texts::CELLAR_TALK, 20, Texts::TALK_VERB);
    }

    private function riddle(): void
    {
        \page_header('The Frost Cellar');
        \output(Texts::CELLAR_TITLE);
        if ((int) \get_module_pref('riddlestoday', self::MODULE) >= self::RIDDLES_PER_DAY) {
            \output(Texts::CELLAR_DONE);
            $this->backToHall();
            return;
        }
        $number = \e_rand(0, \count(Texts::RIDDLES) - 1);
        \set_module_pref('riddle', $number + 1, self::MODULE);
        [$question, $answers] = Texts::RIDDLES[$number];
        \output(Texts::RIDDLE_ASK, \translate_inline($question));
        \addnav(Texts::NAV_CELLAR);
        foreach ($answers as $index => $answer) {
            \addnav($answer, self::URL . '&op=answer&a=' . $index);
        }
        \addnav(Texts::NAV_WALK_AWAY, self::URL . '&op=cellar');
    }

    private function answer(): void
    {
        global $session;

        \page_header('The Frost Cellar');
        \output(Texts::CELLAR_TITLE);
        \addnav(Texts::NAV_CELLAR, self::URL . '&op=cellar');
        $this->backToHall();

        $pending = (int) \get_module_pref('riddle', self::MODULE) - 1;
        \set_module_pref('riddle', 0, self::MODULE);
        if (!isset(Texts::RIDDLES[$pending]) || (int) \get_module_pref('riddlestoday', self::MODULE) >= self::RIDDLES_PER_DAY) {
            \output(Texts::RIDDLE_GONE);
            return;
        }
        \increment_module_pref('riddlestoday', 1, self::MODULE);

        if ((int) \httpget('a') === Texts::RIDDLES[$pending][2] && \httpget('a') !== '') {
            if (\e_rand(1, 5) === 1) {
                $session['user']['gems']++;
                \output(Texts::RIDDLE_GEM);
            } else {
                $gold = \e_rand(50, 150);
                $session['user']['gold'] += $gold;
                \output(Texts::RIDDLE_GOLD, $gold);
            }
        } else {
            \output(Texts::RIDDLE_WRONG, $this->hurt((int) ceil($session['user']['hitpoints'] * 0.15)));
        }
    }

    // Helpers.

    /** Heal a percentage of max hitpoints, at most up to the maximum; returns what was healed. */
    private function heal(int $percent): int
    {
        global $session;
        $max = (int) $session['user']['maxhitpoints'];
        $now = (int) $session['user']['hitpoints'];
        if ($now >= $max) {
            return 0;
        }
        $healed = min($max - $now, (int) ceil($max * $percent / 100));
        $session['user']['hitpoints'] = $now + $healed;
        return $healed;
    }

    /** Take hitpoints, but never the last one. */
    private function hurt(int $amount): int
    {
        global $session;
        if ($session['user']['hitpoints'] <= 1) {
            return 0;
        }
        $amount = max(1, min($amount, $session['user']['hitpoints'] - 1));
        $session['user']['hitpoints'] -= $amount;
        return $amount;
    }

    /** Whether the castle is open today, by the start and end settings (mm-dd). */
    public static function isOpen(): bool
    {
        $start = (string) \get_module_setting('start', self::MODULE);
        $end = (string) \get_module_setting('end', self::MODULE);
        if (\is_file('modules/datemanager.php')) {
            require_once 'modules/datemanager.php';
            if (\function_exists('datemanager_datecheck')) {
                return \datemanager_datecheck($start, $end, 0) > 0;
            }
        }
        $today = date('m-d');
        $from = self::monthDay($start);
        $to = self::monthDay($end);
        return $from <= $to ? ($today >= $from && $today <= $to) : ($today >= $from || $today <= $to);
    }

    private static function monthDay(string $date): string
    {
        $parts = array_map('intval', explode('-', $date));
        return sprintf('%02d-%02d', $parts[0] ?? 1, $parts[1] ?? 1);
    }

    private static function isStaff(): bool
    {
        global $session;
        return \defined('SU_DEVELOPER') && ($session['user']['superuser'] & \SU_DEVELOPER);
    }

    /** The winter a date belongs to: January counts to the winter that began the December before. */
    private static function season(): int
    {
        return (int) date('n') <= 6 ? (int) date('Y') - 1 : (int) date('Y');
    }

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
}
