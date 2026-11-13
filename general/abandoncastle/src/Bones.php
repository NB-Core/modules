<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

/**
 * The bones of players who died in the castle, left where they fell.
 *
 * Kept in one module setting as a small JSON list, newest last, so whoever
 * walks the same maze later finds them. Only the most recent deaths are kept,
 * and none older than a month.
 */
final class Bones
{
    private const SETTING = 'bones';
    private const KEEP = 60;
    private const MAX_AGE = 30 * 86400;

    /**
     * Leave someone's bones in a room.
     *
     * @param string $how a key of Scenery::BONES_HOW
     */
    public static function record(string $mazeKey, int $index, string $name, string $how): void
    {
        $bones = self::all();
        $bones[] = ['m' => $mazeKey, 'i' => $index, 'n' => $name, 'h' => $how, 't' => time()];
        if (\count($bones) > self::KEEP) {
            $bones = \array_slice($bones, -self::KEEP);
        }
        \set_module_setting(self::SETTING, (string) json_encode($bones), 'abandoncastle');
    }

    /**
     * The bones lying in a room, newest first, at most $limit of them.
     *
     * @return list<array{n:string,h:string}>
     */
    public static function in(string $mazeKey, int $index, int $limit = 2): array
    {
        $found = [];
        foreach (array_reverse(self::all()) as $bone) {
            if ($bone['m'] === $mazeKey && $bone['i'] === $index) {
                $found[] = ['n' => $bone['n'], 'h' => $bone['h']];
                if (\count($found) >= $limit) {
                    break;
                }
            }
        }
        return $found;
    }

    /**
     * @return list<array{m:string,i:int,n:string,h:string,t:int}>
     */
    private static function all(): array
    {
        $decoded = json_decode((string) \get_module_setting(self::SETTING, 'abandoncastle'), true);
        if (!\is_array($decoded)) {
            return [];
        }
        $cutoff = time() - self::MAX_AGE;
        $bones = [];
        foreach ($decoded as $bone) {
            if (\is_array($bone) && isset($bone['m'], $bone['i'], $bone['n'], $bone['h'], $bone['t'])
                && (int) $bone['t'] >= $cutoff) {
                $bones[] = ['m' => (string) $bone['m'], 'i' => (int) $bone['i'], 'n' => (string) $bone['n'], 'h' => (string) $bone['h'], 't' => (int) $bone['t']];
            }
        }
        return $bones;
    }
}
