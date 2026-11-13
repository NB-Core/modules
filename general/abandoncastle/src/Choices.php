<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

require_once __DIR__ . '/Scenery.php';
require_once __DIR__ . '/Labyrinth.php';
require_once __DIR__ . '/MapService.php';

/**
 * Small decisions the castle puts in a player's way: a chest to force, a
 * basin to drink from, a shrine that wants a gem, a skeleton with a riddle.
 *
 * Offering one shows it, adds its navs and remembers it in the 'choice'
 * pref; the answer (op=choice) is only taken for the choice that is pending,
 * in the room it was offered in, and only once. Walking away just drops it.
 */
final class Choices
{
    public const KINDS = ['chest', 'well', 'shrine', 'riddle'];

    /**
     * Put a choice in front of the player in this room.
     */
    public static function offer(string $kind, int $locale): void
    {
        $riddle = $kind === 'riddle' ? \e_rand(0, \count(Scenery::RIDDLES) - 1) : 0;
        \set_module_pref('choice', (string) json_encode(['k' => $kind, 'l' => $locale, 'r' => $riddle]));
        $link = static fn (string $pick): string => "runmodule.php?module=abandoncastle&op=choice&pick=$pick&loc=$locale";

        \addnav('What do you do?');
        switch ($kind) {
            case 'chest':
                \output(Scenery::CHEST);
                \addnav(Scenery::CHEST_OPEN, $link('open'));
                \addnav(Scenery::LEAVE_IT, $link('leave'));
                break;
            case 'well':
                \output(Scenery::WELL);
                \addnav(Scenery::WELL_DRINK, $link('drink'));
                \addnav(Scenery::LEAVE_IT, $link('leave'));
                break;
            case 'shrine':
                \output(Scenery::SHRINE);
                \addnav(Scenery::SHRINE_OFFER, $link('offer'));
                \addnav(Scenery::LEAVE_IT, $link('leave'));
                break;
            case 'riddle':
                [$question, $answers] = Scenery::RIDDLES[$riddle];
                \output(Scenery::RIDDLE, \translate_inline($question));
                foreach ($answers as $number => $answer) {
                    \addnav($answer, $link('answer' . $number));
                }
                \addnav(Scenery::WALK_ON, $link('leave'));
                break;
        }
        \output('`n');
    }

    /**
     * Settle the pending choice, if the answer belongs to it. Returns whether it did.
     *
     * @param list<string> $maze
     */
    public static function resolve(string $pick, int $locale, array $maze): bool
    {
        global $session;

        $pending = json_decode((string) \get_module_pref('choice'), true);
        \set_module_pref('choice', '');
        if (!\is_array($pending) || (int) ($pending['l'] ?? 0) !== $locale || !\in_array($pending['k'] ?? '', self::KINDS, true)) {
            return false;
        }
        if ($pick === 'leave') {
            return true;
        }
        $user = &$session['user'];
        switch ($pending['k']) {
            case 'chest':
                if ($pick !== 'open') {
                    return false;
                }
                $roll = \e_rand(1, 10);
                if ($roll <= 5) {
                    $gold = \e_rand(100, 300);
                    \output(Scenery::CHEST_GOLD, $gold);
                    $user['gold'] += $gold;
                } elseif ($roll <= 7) {
                    \output(Scenery::CHEST_GEM);
                    $user['gems']++;
                } else {
                    \output(Scenery::CHEST_TRAP, self::hurt((int) ceil($user['maxhitpoints'] * 0.15)));
                }
                break;
            case 'well':
                if ($pick !== 'drink') {
                    return false;
                }
                $roll = \e_rand(1, 10);
                if ($roll <= 5) {
                    \output(Scenery::WELL_HEAL);
                    $user['hitpoints'] = max($user['hitpoints'], $user['maxhitpoints']);
                } elseif ($roll <= 7) {
                    \output(Scenery::WELL_NOTHING);
                } else {
                    \output(Scenery::WELL_POISON, self::hurt((int) ceil($user['hitpoints'] * 0.2)));
                }
                break;
            case 'shrine':
                if ($pick !== 'offer') {
                    return false;
                }
                if ($user['gems'] < 1) {
                    \output(Scenery::SHRINE_NO_GEM);
                    break;
                }
                $user['gems']--;
                $index = $locale - 1;
                $distance = Labyrinth::exitDistance($maze, $index);
                $exit = array_search(Tiles::EXIT, array_map('ltrim', $maze), true);
                if ($distance === null || $exit === false) {
                    \output(Scenery::SHRINE_LOST);
                    break;
                }
                \output(Scenery::SHRINE_ANSWER, \translate_inline(MapService::bearing($index, (int) $exit)), $distance);
                break;
            case 'riddle':
                if (!preg_match('/^answer([0-9])$/', $pick, $match)) {
                    return false;
                }
                $riddle = Scenery::RIDDLES[(int) ($pending['r'] ?? 0)] ?? Scenery::RIDDLES[0];
                if ((int) $match[1] === $riddle[2]) {
                    \output(Scenery::RIDDLE_RIGHT);
                    $user['gems']++;
                } else {
                    \output(Scenery::RIDDLE_WRONG, self::hurt((int) ceil($user['hitpoints'] * 0.2)));
                }
                break;
        }
        \output('`n`n');
        return true;
    }

    /**
     * Take hitpoints, but never the last one: the castle's choices wound, they do not kill.
     */
    private static function hurt(int $amount): int
    {
        global $session;
        $amount = max(1, min($amount, $session['user']['hitpoints'] - 1));
        if ($session['user']['hitpoints'] <= 1) {
            return 0;
        }
        $session['user']['hitpoints'] -= $amount;
        return $amount;
    }
}
