<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

/**
 * The maze tiles: which way a room opens, what it is called, and how it is drawn.
 *
 * A maze is a list of 143 letters, one per room, 11 wide and 13 high, with
 * index 0 in the south-west corner and index 5 the entrance. Which way each
 * letter leads used to be spelled out again in every place that needed it;
 * the game, the map and the editor now read it from here.
 */
final class Tiles
{
    public const WIDTH = 11;
    public const HEIGHT = 13;

    /** The entrance is always the sixth room of the bottom row, whatever its letter. */
    public const ENTRANCE_INDEX = 5;

    public const EXIT = 'z';

    /** Solid rock: no room at all, only filler around a maze's shape. */
    public const SOLID = 'w';

    /**
     * Passage letters and the directions they lead, as N, S, W and E.
     *
     * @var array<string, string>
     */
    private const PASSAGES = [
        'a' => 'NSWE',
        'b' => 'NWE',
        'c' => 'SWE',
        'd' => 'WE',
        'e' => 'NSW',
        'f' => 'NSE',
        'g' => 'NS',
        'h' => 'SW',
        'i' => 'SE',
        'j' => 'NE',
        'k' => 'NW',
        'l' => 'N',
        'm' => 'S',
        'n' => 'W',
        'o' => 'E',
    ];

    /**
     * Rooms that kill whoever steps in, with what the player is told they are.
     *
     * @var array<string, string>
     */
    private const TRAPS = [
        'p' => 'Spike pit',
        'q' => 'Flooding room',
        'r' => 'Closing walls',
        's' => 'Swinging blade',
        'x' => 'Edge of the world',
    ];

    /**
     * A small mark per trap, so they can be told apart on the editor's palette.
     *
     * @var array<string, string>
     */
    private const TRAP_MARKS = [
        // Spikes
        'p' => '<path class="ac-mark-trap-fill" d="M3.2 6.6 3.9 3.6 4.6 6.6ZM4.5 6.6 5.2 3.6 5.9 6.6ZM5.8 6.6 6.5 3.6 7.2 6.6Z"/>',
        // Water
        'q' => '<path class="ac-mark-water" d="M3.2 4.3Q4 3.6 4.8 4.3T6.4 4.3M3.2 5.9Q4 5.2 4.8 5.9T6.4 5.9"/>',
        // Closing walls
        'r' => '<path class="ac-mark-trap" d="M3.6 3.3V6.7M6.4 3.3V6.7M4.4 5H5.6"/>',
        // Blade
        's' => '<path class="ac-mark-trap" d="M3.2 6.8 6.8 3.2"/>',
        // Edge of the world
        'x' => '<path class="ac-mark-trap" d="M3.4 3.4 6.6 6.6M6.6 3.4 3.4 6.6"/>',
    ];

    /** @var array<string, string> */
    private const DIRECTION_NAMES = ['N' => 'North', 'S' => 'South', 'W' => 'West', 'E' => 'East'];

    /**
     * The directions a room leads, as a subset of "NSWE" in that order.
     * The entrance never leads south: that is the door that slammed shut.
     */
    public static function exits(string $letter, ?int $index = null): string
    {
        $exits = self::PASSAGES[$letter] ?? '';
        if ($index === self::ENTRANCE_INDEX) {
            $exits = str_replace('S', '', $exits);
        }
        return $exits;
    }

    public static function isPassage(string $letter): bool
    {
        return isset(self::PASSAGES[$letter]);
    }

    public static function isTrap(string $letter): bool
    {
        return isset(self::TRAPS[$letter]);
    }

    public static function isExit(string $letter): bool
    {
        return $letter === self::EXIT;
    }

    public static function isSolid(string $letter): bool
    {
        return $letter === self::SOLID || $letter === '';
    }

    /**
     * Every letter a maze may hold, passages first.
     *
     * @return list<string>
     */
    public static function letters(): array
    {
        return array_merge(array_keys(self::PASSAGES), array_keys(self::TRAPS), [self::EXIT, self::SOLID]);
    }

    /**
     * The English kind of room, for the description and the map's labels;
     * callers translate it.
     */
    public static function kind(string $letter): string
    {
        if (self::isExit($letter)) {
            return 'Exit';
        }
        if (self::isSolid($letter)) {
            return 'Solid rock';
        }
        if (self::isTrap($letter)) {
            return self::TRAPS[$letter];
        }
        $exits = self::PASSAGES[$letter] ?? '';
        switch (strlen($exits)) {
            case 4:
                return 'Crossroads';
            case 3:
                return 'Junction';
            case 1:
                return 'Dead end';
            case 2:
                return ($exits === 'NS' || $exits === 'WE') ? 'Corridor' : 'Bend';
        }
        return 'Unknown room';
    }

    /**
     * English direction names of the exits, e.g. ['North', 'East'].
     *
     * @return list<string>
     */
    public static function directionNames(string $exits): array
    {
        $names = [];
        foreach (str_split($exits) as $direction) {
            if (isset(self::DIRECTION_NAMES[$direction])) {
                $names[] = self::DIRECTION_NAMES[$direction];
            }
        }
        return $names;
    }

    /**
     * The room drawn from above as an inline SVG: dark stone, lit passages
     * leading off the edges it opens to, and a mark for an exit or a trap.
     * It is decoration; the text next to it carries the meaning, so the SVG
     * is hidden from screen readers.
     */
    public static function svg(string $letter, ?int $index = null, string $class = 'ac-tile'): string
    {
        $exits = self::exits($letter, $index);
        $parts = ['<rect class="ac-stone" x="0" y="0" width="10" height="10"/>'];
        if ($exits !== '' || self::isExit($letter) || self::isTrap($letter)) {
            $parts[] = '<rect class="ac-floor" x="3" y="3" width="4" height="4"/>';
        }
        $arms = [
            'N' => '<rect class="ac-floor" x="3" y="0" width="4" height="4"/>',
            'S' => '<rect class="ac-floor" x="3" y="6" width="4" height="4"/>',
            'W' => '<rect class="ac-floor" x="0" y="3" width="4" height="4"/>',
            'E' => '<rect class="ac-floor" x="6" y="3" width="4" height="4"/>',
        ];
        foreach (str_split($exits) as $direction) {
            $parts[] = $arms[$direction] ?? '';
        }
        if (self::isExit($letter)) {
            $parts[] = '<rect class="ac-floor" x="1" y="1" width="8" height="8"/>';
            $parts[] = '<path class="ac-mark-exit" d="M5 1.8 8.2 5 5 8.2 1.8 5Z"/>';
        } elseif (self::isTrap($letter)) {
            $parts[] = self::TRAP_MARKS[$letter] ?? '';
        }
        if ($index === self::ENTRANCE_INDEX) {
            $parts[] = '<rect class="ac-mark-entrance" x="3" y="8.6" width="4" height="1.4"/>';
        }
        return '<svg class="' . htmlspecialchars($class, ENT_QUOTES) . '" viewBox="0 0 10 10" aria-hidden="true" focusable="false">'
            . implode('', $parts) . '</svg>';
    }

    /**
     * Grid coordinates of an index, x from the west and y from the south, both from 0.
     *
     * @return array{0:int,1:int}
     */
    public static function coordinates(int $index): array
    {
        return [$index % self::WIDTH, intdiv($index, self::WIDTH)];
    }
}
