<?php

declare(strict_types=1);

namespace Shinobi\Modules\TheGrinch;

use Lotgd\MySQL\Database;

require_once __DIR__ . '/Texts.php';

/**
 * The Grinch challenge: twelve fights of rising difficulty against the
 * Grinch creatures (creature category "<categories>-<type>") and his
 * elves, in the Christmas season only.
 *
 * The fight itself runs in thegrinch_run(), because battle.php has to be
 * included there.
 */
final class Grinch
{
    public const MODULE = 'thegrinch';
    public const URL = 'runmodule.php?module=thegrinch';
    public const TYPES = 12;

    /** The Grinch creatures are all of this level. */
    private const LEVEL = 19;

    // Season.

    /** Whether the Grinch is around today: between the start and end settings (mm-dd). */
    public static function inSeason(): bool
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
        $md = static function (string $date): string {
            $parts = \array_map('intval', \explode('-', $date));
            return \sprintf('%02d-%02d', $parts[0] ?? 1, $parts[1] ?? 1);
        };
        $today = \date('m-d');
        $from = $md($start);
        $to = $md($end);
        return $from <= $to ? ($today >= $from && $today <= $to) : ($today >= $from || $today <= $to);
    }

    public static function name(): string
    {
        return (string) \get_module_setting('grinch', self::MODULE);
    }

    /** The challenge (0..11) from the link; anything else is the first one. */
    public static function enemyType(): int
    {
        $type = (int) \httpget('enemytype');
        return ($type >= 0 && $type < self::TYPES) ? $type : 0;
    }

    /** Whether today's fights are used up. */
    public static function doneForToday(): bool
    {
        return (int) \get_module_pref('seen', self::MODULE) >= (int) \get_module_setting('maxseen', self::MODULE);
    }

    private static function category(int $type): ?string
    {
        $base = (string) \get_module_setting('categories', self::MODULE);
        return $base === '' ? null : $base . '-' . $type;
    }

    // The fight.

    /**
     * The enemies for a challenge: the Grinch creature with his elves, or,
     * if no such creature exists, the beast itself.
     *
     * @return list<array<string, mixed>>
     */
    public static function enemies(int $type): array
    {
        global $session;
        $category = self::category($type);
        $sql = 'SELECT * FROM ' . Database::prefix('creatures') . ' WHERE creaturelevel = :level';
        $params = ['level' => self::LEVEL];
        if ($category !== null) {
            $sql .= ' AND creaturecategory = :category';
            $params['category'] = $category;
        }
        $leader = Database::getDoctrineConnection()->fetchAssociative($sql . ' ORDER BY RAND() LIMIT 1', $params);
        if ($leader === false) {
            return [self::beast($type)];
        }

        $leader = self::aiScript($leader);
        $leader['playerstarthp'] = $session['user']['hitpoints'];
        $leader['creaturegrinch'] = $type;
        $leader['essentialleader'] = true;
        $leader['diddamage'] = $leader['diddamage'] ?? 0;
        $stack = [$leader];

        $elf = self::elf($type, $leader);
        if ($elf !== null) {
            $stack[] = $elf;
            $stack[] = $elf;
            if ((int) $session['user']['dragonkills'] > 1500) {
                $backup = $elf;
                $backup['creaturename'] = \sprintf(\translate_inline(Texts::ELF_BACKUP), self::elfName());
                $backup['creaturehealth'] = 8000;
                $backup['creatureattack'] = 1260;
                $backup['creaturedefense'] = 1730;
                $stack[] = $backup;
                $stack[] = $backup;
                $stack[] = $backup;
            }
        }
        return $stack;
    }

    /** Two elves come along from the third challenge on; tougher the harder the challenge. */
    private static function elf(int $type, array $leader): ?array
    {
        global $session;
        $level = (int) $session['user']['level'];
        $stats = match (true) {
            $type >= 10 => [Texts::ELF_HARMLESS, Texts::WEAPON_ELF_HARMLESS, $level + 5, 20000, 1300, 4000, 760, 730],
            $type >= 8 => [Texts::ELF_VERY_MEAN, Texts::WEAPON_ELF_VERY_MEAN, $level + 5, 8000, 900, 2000, 660, 530],
            $type >= 4 => [Texts::ELF_MEAN, Texts::WEAPON_ELF_MEAN, $level + 5, 8000, 900, 2000, 360, 330],
            $type >= 2 => [Texts::ELF, Texts::WEAPON_ELF, $level, 3000, 500, 400, 300, 280],
            default => null,
        };
        if ($stats === null) {
            return null;
        }
        [$name, $weapon, $elfLevel, $gold, $exp, $hp, $att, $def] = $stats;
        $elf = $leader;
        $elf['creaturename'] = \sprintf(\translate_inline($name), self::elfName());
        $elf['creatureweapon'] = \translate_inline($weapon);
        $elf['creaturelevel'] = $elfLevel;
        $elf['creaturegold'] = $gold;
        $elf['creatureexp'] = $exp;
        $elf['creaturehealth'] = $hp;
        $elf['creatureattack'] = $att;
        $elf['creaturedefense'] = $def;
        $elf['creatureaiscript'] = 'poison1_claws';
        $elf['creaturelose'] = '';
        $elf['creaturewin'] = '';
        $elf['essentialleader'] = false;
        return self::aiScript($elf);
    }

    /** No Grinch creature for this challenge: the beast stands in. */
    private static function beast(int $type): array
    {
        global $session;
        // As before: only the second challenge gets the empowered beast.
        $empowered = $type === 1;
        return [
            'creaturename' => \sprintf(\translate_inline($empowered ? Texts::BEAST_EMPOWERED : Texts::BEAST), (string) \get_module_setting('beast', self::MODULE)),
            'creatureweapon' => \translate_inline($empowered ? Texts::WEAPON_BEAST_EMPOWERED : Texts::WEAPON_BEAST),
            'creaturelevel' => (int) $session['user']['level'] + ($empowered ? 5 : 0),
            'creaturegold' => $empowered ? 8000 : 3000,
            'creatureexp' => $empowered ? 900 : 500,
            'creaturehealth' => $empowered ? 20000 : 10000,
            'creatureattack' => $empowered ? 360 : 300,
            'creaturedefense' => $empowered ? 330 : 280,
            'creaturegrinch' => $type,
            'playerstarthp' => $session['user']['hitpoints'],
            'diddamage' => 0,
        ];
    }

    /** Turns an AI script name into the core's eval form, if the script exists. */
    private static function aiScript(array $creature): array
    {
        $name = (string) ($creature['creatureaiscript'] ?? '');
        if ($name !== '' && \preg_match('/^[A-Za-z0-9_\-]+$/', $name) === 1) {
            $file = 'scripts/' . $name . '.php';
            if (\file_exists($file)) {
                $creature['creatureaiscript'] = "require('" . $file . "');";
            }
        }
        return $creature;
    }

    private static function elfName(): string
    {
        return (string) \get_module_setting('elf', self::MODULE);
    }

    // After the fight.

    /** Loot by challenge: potions for the hardest, scrolls, equipment, then plain loot. */
    public static function loot(int $type, string $enemyName): void
    {
        if (!\is_module_active('inventory')) {
            return;
        }
        $class = match (true) {
            $type >= 7 => 'Potion',
            $type >= 5 => 'Secret Scroll',
            $type >= 3 => 'Advanced Equipment',
            default => 'Loot',
        };
        require_once 'modules/inventory/lib/itemhandler.php';
        // The hardest two challenges give two items.
        for ($i = $type > 9 ? 2 : 1; $i > 0; $i--) {
            $item = Database::getDoctrineConnection()->fetchAssociative(
                'SELECT itemid, name FROM ' . Database::prefix('item') . ' WHERE class = :class ORDER BY RAND() LIMIT 1',
                ['class' => $class]
            );
            if ($item !== false && \add_item((int) $item['itemid'])) {
                \output(Texts::LOOT, $enemyName, $item['name']);
            }
        }
    }

    /** Counts the win for the hall of fame and keeps a note of the last fight. */
    public static function record(int $type, bool $won, array $enemy): void
    {
        global $session;
        require_once 'lib/playerfunctions.php';
        // Not a query: a plain note of the last fight for the admins (pref "lastwon").
        \set_module_pref('lastwon', \implode(' | ', [
            \date('Y-m-d H:i:s'),
            'dk ' . (int) $session['user']['dragonkills'],
            'hp ' . (int) $session['user']['hitpoints'] . '/' . (int) $session['user']['maxhitpoints'],
            'atk ' . \get_player_attack(),
            'def ' . \get_player_defense(),
            $won ? 'won' : 'lost',
            'enemy hp ' . (int) ($enemy['creaturehealth'] ?? 0),
            (string) ($enemy['creaturename'] ?? ''),
        ]), self::MODULE);
        \increment_module_pref('seen', 1, self::MODULE);
        if ($won) {
            \increment_module_pref('enemy-' . $type, 1, self::MODULE);
        }
    }

    // Hall of fame.

    /**
     * For each challenge: a Grinch creature's name, the best slayer and their victories.
     *
     * @return list<array{grinchname:string,name:string,kills:int}>
     */
    public static function hallOfFame(): array
    {
        $conn = Database::getDoctrineConnection();
        $creatures = Database::prefix('creatures');
        $prefs = Database::prefix('module_userprefs');
        $accounts = Database::prefix('accounts');
        $out = [];
        for ($type = 0; $type < self::TYPES; $type++) {
            $category = self::category($type);
            $creature = $category === null ? false : $conn->fetchOne(
                "SELECT creaturename FROM $creatures WHERE creaturecategory = :category ORDER BY RAND() LIMIT 1",
                ['category' => $category]
            );
            $best = $conn->fetchAssociative(
                "SELECT a.name, p.value FROM $prefs AS p INNER JOIN $accounts AS a ON a.acctid = p.userid"
                . " WHERE p.modulename = :module AND p.setting = :setting AND p.value + 0 > 0 ORDER BY p.value + 0 DESC LIMIT 1",
                ['module' => self::MODULE, 'setting' => 'enemy-' . $type]
            );
            $out[] = [
                'grinchname' => $creature === false ? \translate_inline(Texts::HOF_UNKNOWN) : (string) $creature,
                'name' => $best === false ? \translate_inline(Texts::HOF_NOBODY) : (string) $best['name'],
                'kills' => $best === false ? 0 : (int) $best['value'],
            ];
        }
        return $out;
    }

    public static function showHallOfFame(): void
    {
        \output(Texts::HOF_TITLE, self::name());
        \output_notl('`c');
        foreach (self::hallOfFame() as $type => $row) {
            \output(Texts::HOF_LINE, $row['grinchname'], $row['name'], $row['kills'], (int) \get_module_pref('enemy-' . $type, self::MODULE));
        }
        \output_notl('`c');
    }
}
