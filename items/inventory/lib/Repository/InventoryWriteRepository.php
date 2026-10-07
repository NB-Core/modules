<?php
declare(strict_types=1);

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

/**
 * Write-focused repository for inventory mutation operations.
 *
 * Strict-typing note:
 * - Strict typing is enabled for the repository/service/domain library layer.
 * - Legacy procedural entrypoints (for example inventory/lib/itemhandler.php)
 *   intentionally remain non-strict for backward compatibility.
 *
 * Compatibility contract:
 * - Keep legacy uniqueness checks for `uniqueforserver` and `uniqueforplayer`.
 * - Preserve row-oriented inventory semantics (one row per acquired item instance).
 * - Return values mirror legacy callers: boolean success for adds, exact affected
 *   row count for removals/charge updates.
 * - Uniqueness checks are intentionally best-effort without explicit atomic
 *   lock orchestration, which keeps behavior aligned with module-wide
 *   simplicity and maintainability standards.
 */
class InventoryWriteRepository
{
    /**
     * Add item rows for one user and item id.
     *
     * Explicit contract:
     * - Delegates to addItemByIdUsingKnownUniqueness().
     * - Unique writes use best-effort pre-insert existence checks.
     * - Non-unique writes use the optimized batched insert path.
     * - Inserts exactly `$qty` rows when successful.
     * - Returns false when uniqueness constraints block insert or when item is
     *   missing.
     */
    public function addItemById(
        int $userId,
        int $itemId,
        int $qty,
        string $specialValue,
        int $sellGold,
        int $sellGems,
        int $charges
    ): bool {
        return $this->addItemByIdUsingKnownUniqueness(
            $userId,
            $itemId,
            $qty,
            $specialValue,
            $sellGold,
            $sellGems,
            $charges
        );
    }

    /**
     * Add item rows while reusing already-fetched uniqueness flags.
     *
     * Explicit contract:
     * - If uniqueness flags are omitted, the method first performs an unlocked
     *   read to derive uniqueness from the database.
     * - If uniqueness flags are provided, the method still verifies the item
     *   exists before writing inventory rows.
     * - Non-unique writes use a plain batched insert path.
     * - Unique writes use best-effort pre-insert existence checks.
     * - If uniqueness is enabled, quantity must be exactly 1; otherwise the
     *   method rejects the write to avoid creating internally inconsistent
     *   duplicate rows for unique items.
     * - This is intentionally consistent with other modules: no explicit
     *   atomic lock orchestration is performed in this repository layer.
     * - Return semantics match addItemById().
     */
    public function addItemByIdUsingKnownUniqueness(
        int $userId,
        int $itemId,
        int $qty,
        string $specialValue,
        int $sellGold,
        int $sellGems,
        int $charges,
        ?bool $uniqueForServer = null,
        ?bool $uniqueForPlayer = null
    ): bool {
        if ($qty < 1) {
            return false;
        }

        $conn = Database::getDoctrineConnection();
        $inventory = Database::prefix('inventory');
        $item = Database::prefix('item');

        if ($uniqueForServer === null || $uniqueForPlayer === null) {
            $itemRow = $conn->executeQuery(
                "SELECT uniqueforserver, uniqueforplayer FROM {$item} WHERE itemid = :itemid LIMIT 1",
                [
                    'itemid' => $itemId,
                ],
                [
                    'itemid' => ParameterType::INTEGER,
                ]
            )->fetchAssociative();
            if (!$itemRow) {
                return false;
            }
            if ($uniqueForServer === null) {
                $uniqueForServer = !empty($itemRow['uniqueforserver']);
            }
            if ($uniqueForPlayer === null) {
                $uniqueForPlayer = !empty($itemRow['uniqueforplayer']);
            }
        } else {
            // Keep add semantics aligned with legacy helpers: inserts for
            // unknown item ids are rejected even when uniqueness flags were
            // precomputed by the caller.
            $itemExists = $conn->executeQuery(
                "SELECT 1 FROM {$item} WHERE itemid = :itemid LIMIT 1",
                [
                    'itemid' => $itemId,
                ],
                [
                    'itemid' => ParameterType::INTEGER,
                ]
            )->fetchOne();
            if (!$itemExists) {
                return false;
            }
        }

        $isUniqueWrite = (bool) $uniqueForServer || (bool) $uniqueForPlayer;
        if ($isUniqueWrite && $qty > 1) {
            return false;
        }
        if (!$isUniqueWrite) {
            $this->insertInventoryRows($conn, $inventory, $userId, $itemId, $qty, $specialValue, $sellGold, $sellGems, $charges);

            return true;
        }

        // Best-effort uniqueness checks are intentionally done as simple
        // pre-insert reads without explicit lock orchestration.
        if ($uniqueForServer) {
            $alreadyOwned = $conn->executeQuery(
                "SELECT 1 FROM {$inventory} WHERE itemid = :itemid LIMIT 1",
                [
                    'itemid' => $itemId,
                ],
                [
                    'itemid' => ParameterType::INTEGER,
                ]
            )->fetchOne();

            if ($alreadyOwned) {
                return false;
            }
        }

        if ($uniqueForPlayer) {
            $alreadyOwnedByUser = $conn->executeQuery(
                "SELECT 1 FROM {$inventory} WHERE itemid = :itemid AND userid = :userid LIMIT 1",
                [
                    'itemid' => $itemId,
                    'userid' => $userId,
                ],
                [
                    'itemid' => ParameterType::INTEGER,
                    'userid' => ParameterType::INTEGER,
                ]
            )->fetchOne();

            if ($alreadyOwnedByUser) {
                return false;
            }
        }

        $this->insertInventoryRows($conn, $inventory, $userId, $itemId, $qty, $specialValue, $sellGold, $sellGems, $charges);

        return true;
    }

    /**
     * Remove up to `$qty` rows for one user's item id.
     *
     * Explicit contract:
     * - Deletes at most `$qty` rows.
     * - Returns exact removed-row count for legacy callers.
     */
    public function removeItemById(int $userId, int $itemId, int $qty): int
    {
        if ($qty < 1) {
            return 0;
        }

        $conn = Database::getDoctrineConnection();
        $inventory = Database::prefix('inventory');

        // LIMIT cannot be parameterized portably across all host drivers, so we
        // safely inject an int-cast quantity to preserve legacy behavior.
        $sql = sprintf(
            'DELETE FROM %s WHERE userid = :userid AND itemid = :itemid LIMIT %d',
            $inventory,
            (int) $qty
        );

        return (int) $conn->executeStatement(
            $sql,
            [
                'userid' => $userId,
                'itemid' => $itemId,
            ],
            [
                'userid' => ParameterType::INTEGER,
                'itemid' => ParameterType::INTEGER,
            ]
        );
    }

    /**
     * Increment/decrement charges on a single matching row.
     *
     * Explicit contract:
     * - Positive delta recharges one row (`LIMIT 1`).
     * - Negative delta uncharges one row only when charges remain >= 1.
     * - Delta values other than -1 or 1 are rejected and return 0 to preserve
     *   legacy single-step charge semantics.
     * - Returns affected-row count exactly as legacy logic expects.
     */
    public function changeCharges(int $userId, int $itemId, int $delta): int
    {
        if ($delta !== 1 && $delta !== -1) {
            return 0;
        }

        $conn = Database::getDoctrineConnection();
        $inventory = Database::prefix('inventory');

        if ($delta > 0) {
            return (int) $conn->executeStatement(
                "UPDATE {$inventory} SET charges = charges + :delta WHERE itemid = :itemid AND userid = :userid LIMIT 1",
                [
                    'delta' => $delta,
                    'itemid' => $itemId,
                    'userid' => $userId,
                ],
                [
                    'delta' => ParameterType::INTEGER,
                    'itemid' => ParameterType::INTEGER,
                    'userid' => ParameterType::INTEGER,
                ]
            );
        }

        return (int) $conn->executeStatement(
            "UPDATE {$inventory} SET charges = charges + :delta WHERE itemid = :itemid AND userid = :userid AND charges >= :minimumCharges LIMIT 1",
            [
                'delta' => $delta,
                'itemid' => $itemId,
                'userid' => $userId,
                'minimumCharges' => abs($delta),
            ],
            [
                'delta' => ParameterType::INTEGER,
                'itemid' => ParameterType::INTEGER,
                'userid' => ParameterType::INTEGER,
                'minimumCharges' => ParameterType::INTEGER,
            ]
        );
    }

    private function insertInventoryRows(
        $conn,
        string $inventoryTable,
        int $userId,
        int $itemId,
        int $qty,
        string $specialValue,
        int $sellGold,
        int $sellGems,
        int $charges
    ): void {
        $chunkSize = 100;
        for ($offset = 0; $offset < $qty; $offset += $chunkSize) {
            $currentChunk = min($chunkSize, $qty - $offset);
            $values = [];
            $params = [];
            $types = [];

            for ($i = 0; $i < $currentChunk; $i++) {
                $suffix = (string) $i;
                $values[] = "(:userid{$suffix}, :itemid{$suffix}, :sellvaluegold{$suffix}, :sellvaluegems{$suffix}, :specialvalue{$suffix}, :charges{$suffix}, 0)";
                $params["userid{$suffix}"] = $userId;
                $params["itemid{$suffix}"] = $itemId;
                $params["sellvaluegold{$suffix}"] = $sellGold;
                $params["sellvaluegems{$suffix}"] = $sellGems;
                $params["specialvalue{$suffix}"] = $specialValue;
                $params["charges{$suffix}"] = $charges;

                $types["userid{$suffix}"] = ParameterType::INTEGER;
                $types["itemid{$suffix}"] = ParameterType::INTEGER;
                $types["sellvaluegold{$suffix}"] = ParameterType::INTEGER;
                $types["sellvaluegems{$suffix}"] = ParameterType::INTEGER;
                $types["specialvalue{$suffix}"] = ParameterType::STRING;
                $types["charges{$suffix}"] = ParameterType::INTEGER;
            }

            $conn->executeStatement(
                "INSERT INTO {$inventoryTable} (userid, itemid, sellvaluegold, sellvaluegems, specialvalue, charges, equipped) VALUES " . implode(', ', $values),
                $params,
                $types
            );
        }
    }

}
