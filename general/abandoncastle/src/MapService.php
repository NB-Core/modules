<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

class MapService
{
    public const WIDTH = 11;
    public const HEIGHT = 13;
    public const MAXWIDTH = 500;

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

    /**
     * Render the explored map as a responsive flexbox of tile images.
     *
     * @param array<int, string> $maze Premade maze layout where each entry is a tile key
     */
    public function render(array $maze, int $currentLocale): string
    {
        $currentIndex = $currentLocale - 1;
        $tilePercent = 100 / self::WIDTH;
        $html = '<div class="ac-map" style="display:flex;flex-wrap:wrap;width:100%;max-width:' . self::MAXWIDTH . 'px">';

        for ($y = self::HEIGHT - 1; $y >= 0; $y--) {
            for ($x = 0; $x < self::WIDTH; $x++) {
                $index = $y * self::WIDTH + $x;
                $style = sprintf('flex:0 0 %.2f%%;max-width:%.2f%%;', $tilePercent, $tilePercent);
                if (isset($this->visited[$y][$x])) {
                    $tileKey = ltrim($maze[$index]);
                    $src = "./modules/abandoncastle/images/{$tileKey}maze.gif";
                    if ($index === $currentIndex) {
                        $html .= sprintf(
                            '<div style="%sposition:relative"><img src="%s" alt="" style="width:100%%;height:auto"><img src="./modules/abandoncastle/images/mcyan.gif" alt="" style="position:absolute;top:50%%;left:50%%;transform:translate(-50%%,-50%%);width:25%%;height:auto"></div>',
                            $style,
                            $src
                        );
                    } else {
                        $html .= sprintf(
                            '<div style="%s"><img src="%s" alt="" style="width:100%%;height:auto"></div>',
                            $style,
                            $src
                        );
                    }
                } else {
                    $html .= sprintf('<div style="%saspect-ratio:1/1"></div>', $style);
                }
            }
        }

        $html .= '</div>';
        return $html;
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
        $index = $locale - 1;
        $x = $index % self::WIDTH;
        $y = intdiv($index, self::WIDTH);
        return [$x, $y];
    }
}
