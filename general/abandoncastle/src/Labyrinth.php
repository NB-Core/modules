<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

require_once __DIR__ . '/Tiles.php';
require_once __DIR__ . '/Narration.php';
require_once __DIR__ . '/Scenery.php';

/**
 * The maze as a place: how its rooms connect, what each room looks like,
 * where its landmarks stand, and how the Minotaur moves through it.
 *
 * A maze is the list of 143 letters (index 0 south-west). Everything that
 * should stay the same for a room in a given maze (its description, whether
 * it is a landmark) is derived from a hash of the maze and the room, not
 * from the random generator, so it reads the same each time the player comes
 * back, and the game's e_rand() sequence is left alone.
 */
final class Labyrinth
{
    private const STEPS = ['N' => Tiles::WIDTH, 'S' => -Tiles::WIDTH, 'W' => -1, 'E' => 1];

    /** About one passage room in this many becomes a landmark; a maze gets a handful. */
    private const LANDMARK_RARITY = 16;

    /**
     * A short, stable key for a maze, e.g. to tell apart the bones in different mazes.
     *
     * @param list<string> $maze
     */
    public static function key(array $maze): string
    {
        return sprintf('%08x', crc32(implode(',', array_map('ltrim', $maze))));
    }

    /**
     * The rooms one step away that a room really leads to, by direction.
     *
     * @param list<string> $maze
     * @return array<string, int>
     */
    public static function neighbours(array $maze, int $index): array
    {
        $letter = ltrim((string) ($maze[$index] ?? ''));
        $result = [];
        foreach (str_split(Tiles::exits($letter, $index)) as $direction) {
            if ($direction === '') {
                continue;
            }
            $x = $index % Tiles::WIDTH;
            if (($direction === 'W' && $x === 0) || ($direction === 'E' && $x === Tiles::WIDTH - 1)) {
                continue;
            }
            $next = $index + self::STEPS[$direction];
            if ($next < 0 || $next >= Tiles::WIDTH * Tiles::HEIGHT) {
                continue;
            }
            $result[$direction] = $next;
        }
        return $result;
    }

    /**
     * Walking distance from one room to every room it can reach.
     *
     * @param list<string> $maze
     * @param bool $passagesOnly Only walk through passage rooms (the Minotaur does not step into traps).
     * @return array<int, int>
     */
    public static function distances(array $maze, int $from, bool $passagesOnly = false): array
    {
        $distance = [$from => 0];
        $queue = [$from];
        while ($queue !== []) {
            $index = array_shift($queue);
            foreach (self::neighbours($maze, $index) as $next) {
                if (isset($distance[$next])) {
                    continue;
                }
                $letter = ltrim((string) ($maze[$next] ?? ''));
                if ($passagesOnly && !Tiles::isPassage($letter)) {
                    continue;
                }
                $distance[$next] = $distance[$index] + 1;
                if (Tiles::isPassage($letter)) {
                    $queue[] = $next;
                }
            }
        }
        return $distance;
    }

    /**
     * Landmarks of a maze, as room index => index into Scenery::LANDMARKS.
     *
     * @param list<string> $maze
     * @return array<int, int>
     */
    public static function landmarks(array $maze): array
    {
        $key = self::key($maze);
        $order = array_keys(Scenery::LANDMARKS);
        usort($order, static fn (int $a, int $b): int => crc32("$key|lm|$a") <=> crc32("$key|lm|$b"));
        $result = [];
        foreach ($maze as $index => $letter) {
            if ($order === []) {
                break;
            }
            if ($index === Tiles::ENTRANCE_INDEX || !Tiles::isPassage(ltrim((string) $letter))) {
                continue;
            }
            if (crc32("$key|room|$index") % self::LANDMARK_RARITY === 0) {
                $result[(int) $index] = array_shift($order);
            }
        }
        return $result;
    }

    /**
     * A layer's line for a room, the same each visit.
     *
     * @param list<list<string>> $layer
     */
    private static function stable(array $layer, string $key, int $index, string $name): string
    {
        $variants = $layer[self::tier($index)];
        return $variants[crc32("$key|$name|$index") % \count($variants)];
    }

    /** 0 for the outer halls, 1 for the middle, 2 for the deep castle in the north. */
    public static function tier(int $index): int
    {
        $row = intdiv($index, Tiles::WIDTH);
        return $row < 4 ? 0 : ($row < 9 ? 1 : 2);
    }

    /**
     * The room's kind sentence, the same each visit.
     */
    public static function kindSentence(array $maze, int $index, string $kind): string
    {
        if ($index === Tiles::ENTRANCE_INDEX) {
            return Narration::ENTRANCE_ROOM;
        }
        $variants = Narration::ROOMS[$kind] ?? ['A dark, silent room.'];
        return $variants[crc32(self::key($maze) . "|kind|$index") % \count($variants)];
    }

    /**
     * The untranslated pieces of a room's scenery, in reading order:
     * the landmark (if any), light, smell, a detail and a sound.
     *
     * @param list<string> $maze
     * @return list<string>
     */
    public static function scenery(array $maze, int $index, ?string $sound): array
    {
        $key = self::key($maze);
        $landmarks = self::landmarks($maze);
        $parts = [];
        if (isset($landmarks[$index])) {
            $parts[] = Scenery::LANDMARKS[$landmarks[$index]][1];
        }
        $parts[] = '`7' . self::stable(Scenery::LIGHT, $key, $index, 'light');
        $parts[] = self::stable(Scenery::SMELL, $key, $index, 'smell');
        if (!isset($landmarks[$index])) {
            $parts[] = self::stable(Scenery::DETAIL, $key, $index, 'detail');
        }
        if ($sound !== null) {
            $parts[] = $sound;
        }
        return $parts;
    }

    /**
     * A sound for this visit, or none; the sound changes from visit to visit.
     */
    public static function sound(int $index, int $roll): ?string
    {
        $variants = Scenery::SOUND[self::tier($index)];
        return $roll < \count($variants) ? $variants[$roll] : null;
    }

    // The Minotaur.

    /** No Minotaur in the maze, or it is dead. */
    public const NO_HUNTER = -1;

    /**
     * Where the Minotaur starts: a passage room far from the entrance.
     *
     * @param list<string> $maze
     */
    public static function hunterStart(array $maze, int $roll): int
    {
        $distance = self::distances($maze, Tiles::ENTRANCE_INDEX, true);
        unset($distance[Tiles::ENTRANCE_INDEX]);
        if ($distance === []) {
            return self::NO_HUNTER;
        }
        $far = array_keys(array_filter($distance, static fn (int $d): bool => $d >= 10));
        if ($far === []) {
            arsort($distance);
            $far = [array_key_first($distance)];
        }
        sort($far);
        return $far[$roll % \count($far)];
    }

    /**
     * One move of the Minotaur. It drifts at random until it is near, then it
     * closes in more often than not. It never walks into traps or the exit.
     *
     * @param list<string> $maze
     * @param int $chase 0..99; below the chase chance it steps towards the player.
     * @param int $wander used to pick a random way when it does not chase.
     */
    public static function hunterStep(array $maze, int $hunter, int $player, int $chase, int $wander): int
    {
        if ($hunter < 0) {
            return $hunter;
        }
        $ways = array_values(array_filter(
            self::neighbours($maze, $hunter),
            static fn (int $next): bool => Tiles::isPassage(ltrim((string) ($maze[$next] ?? '')))
        ));
        if ($ways === []) {
            return $hunter;
        }
        $fromPlayer = self::distances($maze, $player, true);
        $near = isset($fromPlayer[$hunter]) && $fromPlayer[$hunter] <= 6;
        if ($chase < ($near ? 60 : 15) && isset($fromPlayer[$hunter])) {
            usort($ways, static fn (int $a, int $b): int => ($fromPlayer[$a] ?? PHP_INT_MAX) <=> ($fromPlayer[$b] ?? PHP_INT_MAX));
            return $ways[0];
        }
        return $ways[$wander % \count($ways)];
    }

    /**
     * How far the Minotaur is from the player, walking, and from which way
     * the player would hear it: the direction of the first step towards it.
     *
     * @param list<string> $maze
     * @return array{0:int,1:string}|null null when it cannot reach the player.
     */
    public static function hunterBearing(array $maze, int $hunter, int $player): ?array
    {
        if ($hunter < 0) {
            return null;
        }
        $fromHunter = self::distances($maze, $hunter, true);
        if (!isset($fromHunter[$player])) {
            return null;
        }
        $distance = $fromHunter[$player];
        $direction = '';
        foreach (self::neighbours($maze, $player) as $way => $next) {
            if (isset($fromHunter[$next]) && $fromHunter[$next] === $distance - 1) {
                $direction = $way;
                break;
            }
        }
        return [$distance, $direction];
    }

    /**
     * The nearest exit that can be walked to, with its walking distance, or null if none can.
     *
     * @param list<string> $maze
     * @return array{0:int,1:int}|null [exit room index, distance]
     */
    public static function nearestExit(array $maze, int $from): ?array
    {
        $best = null;
        foreach (self::distances($maze, $from) as $index => $distance) {
            if (Tiles::isExit(ltrim((string) $maze[$index])) && ($best === null || $distance < $best[1])) {
                $best = [$index, $distance];
            }
        }
        return $best;
    }
}
