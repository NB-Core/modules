<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

require_once __DIR__ . '/Tiles.php';

/**
 * What the player sees of the castle: the room they stand in and, depending
 * on the mode, a map.
 *
 * - normal:   the rooms explored so far, with the exit marked from the start;
 * - hardcore: no rooms, only where the player, the entrance and the exit are;
 * - extreme:  no map at all.
 *
 * Everything that is drawn is also said in text, for screen readers: the map
 * is a table with a label per cell, and a summary in front of it says where
 * the exit lies from the player.
 */
class MapService
{
    public const WIDTH = Tiles::WIDTH;
    public const HEIGHT = Tiles::HEIGHT;

    public const MODE_NORMAL = 'normal';
    public const MODE_HARDCORE = 'hardcore';
    public const MODE_EXTREME = 'extreme';

    /**
     * @var array<int, array<int, bool>>
     */
    private array $visited;

    public function __construct(array $visited = [])
    {
        $this->visited = $visited;
    }

    public static function fromString(string $data): self
    {
        if ($data === '') {
            return new self();
        }
        $decoded = json_decode($data, true);
        if (!\is_array($decoded)) {
            return new self();
        }
        return new self($decoded);
    }

    public function markVisited(int $locale): void
    {
        [$x, $y] = self::localeToCoordinates($locale);
        $this->visited[$y][$x] = true;
    }

    public function isVisited(int $index): bool
    {
        [$x, $y] = Tiles::coordinates($index);
        return isset($this->visited[$y][$x]);
    }

    public function visitedCount(): int
    {
        $count = 0;
        foreach ($this->visited as $row) {
            $count += \count($row);
        }
        return $count;
    }

    /**
     * Whether a mode shows a map at all.
     */
    public static function hasMap(string $mode): bool
    {
        return $mode === self::MODE_NORMAL || $mode === self::MODE_HARDCORE;
    }

    /**
     * The room the player stands in, drawn large.
     */
    public static function renderRoom(string $letter, int $index): string
    {
        return '<div class="ac-room">' . Tiles::svg($letter, $index, 'ac-tile ac-tile-room') . '</div>';
    }

    /**
     * The map for a mode, with its summary and legend; empty in extreme mode.
     *
     * @param array<int, string> $maze
     * @param bool $revealAll Show every room, for the superuser map.
     */
    public function render(array $maze, int $currentLocale, string $mode = self::MODE_NORMAL, bool $revealAll = false): string
    {
        if (!$revealAll && !self::hasMap($mode)) {
            return '';
        }
        $current = $currentLocale - 1;
        $showRooms = $revealAll || $mode === self::MODE_NORMAL;
        $exits = self::exitIndexes($maze);

        $html = '<section class="ac-map-wrap" aria-label="' . self::attr(self::t($revealAll ? 'Full maze' : 'Map')) . '">';
        $html .= '<p class="ac-map-summary">' . self::esc($this->summary($current, $exits, $showRooms && !$revealAll)) . '</p>';
        $html .= '<table class="ac-map"><caption class="ac-sr">'
            . self::esc(self::t('Castle map, north at the top. Columns A to K from west to east, rows 1 to 13 from south to north.'))
            . '</caption><tbody>';
        for ($y = self::HEIGHT - 1; $y >= 0; $y--) {
            $html .= '<tr>';
            for ($x = 0; $x < self::WIDTH; $x++) {
                $index = $y * self::WIDTH + $x;
                $html .= $this->cell($maze, $index, $current, $exits, $showRooms, $revealAll);
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= self::legend($showRooms);
        $html .= '</section>';
        return $html;
    }

    /**
     * @param array<int, string> $maze
     * @param list<int> $exits
     */
    private function cell(array $maze, int $index, int $current, array $exits, bool $showRooms, bool $revealAll): string
    {
        $letter = ltrim((string) ($maze[$index] ?? ''));
        $isCurrent = $index === $current;
        $isEntrance = $index === Tiles::ENTRANCE_INDEX;
        $isExit = \in_array($index, $exits, true);
        $known = $revealAll || ($showRooms && $this->isVisited($index));

        $classes = ['ac-cell'];
        $labels = [self::coordinateName($index)];
        $content = '';
        if ($known) {
            $classes[] = 'ac-known';
            $content = Tiles::svg($letter, $index);
            $labels[] = self::t(Tiles::kind($letter));
            $directions = Tiles::directionNames(Tiles::exits($letter, $index));
            if ($directions !== []) {
                $labels[] = self::t('passages') . ' ' . implode(', ', array_map([self::class, 't'], $directions));
            }
        } elseif ($isExit) {
            $classes[] = 'ac-exit';
            $content = Tiles::svg(Tiles::EXIT);
            $labels[] = self::t('Exit');
        } else {
            $classes[] = 'ac-unknown';
            $labels[] = self::t('unexplored');
        }
        if ($isEntrance) {
            $classes[] = 'ac-entrance';
            $labels[] = self::t('entrance');
        }
        if ($isCurrent) {
            $classes[] = 'ac-current';
            $labels[] = self::t('you are here');
            $content .= '<span class="ac-you" aria-hidden="true"></span>';
        }

        return '<td class="' . implode(' ', $classes) . '"' . ($isCurrent ? ' aria-current="location"' : '') . '>'
            . '<div class="ac-box">' . $content . '</div>'
            . '<span class="ac-sr">' . self::esc(implode(', ', $labels)) . '</span></td>';
    }

    /**
     * Where the player is, how much is explored, and which way the exit lies.
     *
     * @param list<int> $exits
     */
    private function summary(int $current, array $exits, bool $countExplored): string
    {
        $parts = [sprintf(self::t('You are in room %s.'), self::coordinateName($current))];
        if ($countExplored) {
            $parts[] = sprintf(self::t('Explored: %s of %s rooms.'), $this->visitedCount(), self::WIDTH * self::HEIGHT);
        }
        $nearest = null;
        $best = PHP_INT_MAX;
        foreach ($exits as $exit) {
            [$ex, $ey] = Tiles::coordinates($exit);
            [$cx, $cy] = Tiles::coordinates($current);
            $distance = abs($ex - $cx) + abs($ey - $cy);
            if ($distance < $best) {
                $best = $distance;
                $nearest = $exit;
            }
        }
        if ($nearest !== null) {
            $parts[] = sprintf(
                self::t('The exit is in room %s, %s of you.'),
                self::coordinateName($nearest),
                self::t(self::bearing($current, $nearest))
            );
        }
        return implode(' ', $parts);
    }

    private static function legend(bool $showRooms): string
    {
        $items = [
            ['ac-swatch ac-swatch-you', 'You'],
            ['ac-swatch ac-swatch-entrance', 'Entrance'],
            ['ac-swatch ac-swatch-exit', 'Exit'],
        ];
        if ($showRooms) {
            $items[] = ['ac-swatch ac-swatch-known', 'Explored room'];
        }
        $items[] = ['ac-swatch ac-swatch-unknown', 'Unexplored'];
        $html = '<ul class="ac-legend" aria-label="' . self::attr(self::t('Legend')) . '">';
        foreach ($items as [$class, $label]) {
            $html .= '<li><span class="' . $class . '" aria-hidden="true"></span>' . self::esc(self::t($label)) . '</li>';
        }
        return $html . '</ul>';
    }

    /**
     * @param array<int, string> $maze
     * @return list<int>
     */
    private static function exitIndexes(array $maze): array
    {
        $exits = [];
        foreach ($maze as $index => $letter) {
            if (Tiles::isExit(ltrim((string) $letter))) {
                $exits[] = (int) $index;
            }
        }
        return $exits;
    }

    /**
     * A room's name on the map, e.g. "F1" for the entrance.
     */
    public static function coordinateName(int $index): string
    {
        [$x, $y] = Tiles::coordinates($index);
        return \chr(\ord('A') + $x) . ($y + 1);
    }

    /**
     * The compass direction from one room to another, in English.
     */
    public static function bearing(int $from, int $to): string
    {
        [$fx, $fy] = Tiles::coordinates($from);
        [$tx, $ty] = Tiles::coordinates($to);
        $dx = $tx - $fx;
        $dy = $ty - $fy;
        if ($dx === 0 && $dy === 0) {
            return 'right here';
        }
        // A component less than half the other is not worth naming.
        $ns = ($dy !== 0 && abs($dy) * 2 >= abs($dx)) ? ($dy > 0 ? 'north' : 'south') : '';
        $ew = ($dx !== 0 && abs($dx) * 2 >= abs($dy)) ? ($dx > 0 ? 'east' : 'west') : '';
        return $ns !== '' && $ew !== '' ? $ns . '-' . $ew : $ns . $ew;
    }

    public function serialize(): string
    {
        return json_encode($this->visited);
    }

    /**
     * @return array{0:int,1:int}
     */
    public static function localeToCoordinates(int $locale): array
    {
        return Tiles::coordinates($locale - 1);
    }

    private static function t(string $text): string
    {
        return \function_exists('translate_inline') ? (string) \translate_inline($text, 'abandoncastle') : $text;
    }

    private static function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function attr(string $text): string
    {
        return self::esc($text);
    }
}
