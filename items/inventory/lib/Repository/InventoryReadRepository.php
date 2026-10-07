<?php
declare(strict_types=1);

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

/**
 * Read-only data access repository for inventory module queries.
 *
 * Strict-typing note:
 * - Strict typing is enabled for the repository/service/domain library layer.
 * - Legacy procedural entrypoints (for example inventory/lib/itemhandler.php)
 *   intentionally remain non-strict for backward compatibility.
 *
 * Design goals for this phase:
 * - Preserve legacy output structures and gameplay behavior.
 * - Add two-level caching (request-local + shared datacache) for read paths.
 * - Keep SQL shape equivalent to existing legacy functions.
 */
class InventoryReadRepository
{
    /**
     * Shared datacache TTL in seconds for repository-managed read keys.
     *
     * Keep this centralized so all reads/index-tracking use the same horizon.
     */
    private const CACHE_TTL_SECONDS = 300;

    /**
     * Marker key used for cross-request negative-cache payloads.
     *
     * We cannot store literal `false` in datacache for item misses because
     * datacache() itself uses `false` for cache misses.
     */
    private const ITEM_NOT_FOUND_MARKER_KEY = '__inventory_read_repo_not_found__';

    /**
     * Request-local cache for expensive per-user inventory snapshots.
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    private static $snapshotCache = [];

    /**
     * Request-local cache for quantity maps keyed by user id.
     *
     * @var array<int, array<int, int>>
     */
    private static $qtyMapCache = [];

    /**
     * Request-local cache for legacy get_inventory_item()-shape lookups.
     *
     * Key format: "{userId}:{itemId}".
     *
     * @var array<string, array<string, mixed>|false>
     */
    private static $inventoryItemCache = [];

    /**
     * Request-local cache for display_item_nav() activatable item rows.
     *
     * Key format: "{userId}:{hookMask}".
     *
     * @var array<string, array<int, array<string, mixed>>>
     */
    private static $activatableItemsCache = [];

    /**
     * Request-local cache for single item lookups by item id.
     *
     * @var array<int, array<string, mixed>|false>
     */
    private static $itemByIdCache = [];

    /**
     * Request-local cache for single item lookups by item name.
     *
     * @var array<string, array<string, mixed>|false>
     */
    private static $itemByNameCache = [];

    /**
     * Return an aggregated inventory snapshot for one user.
     *
     * Compatibility contract:
     * - Returned row structure must remain identical to legacy get_inventory() rows.
     * - SQL ordering and filters remain equivalent to prior implementation.
     *
     * Caching:
     * - First level: request-local static cache (no SQL after first hit in same request).
     * - Second level: shared datacache key
     *   inventory:user:{userId}:snapshot:{showhide}:{class}.
     *
     * @param int        $userId   Account id.
     * @param int        $showhide Hide-flag filter used by legacy API.
     * @param int|string $class    Item class filter; 0 means all classes.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getInventorySnapshotForUser(int $userId, int $showhide = 0, int|string $class = 0): array
    {
        $cacheKey = $this->buildSnapshotCacheKey($userId, $showhide, $class);
        if (array_key_exists($cacheKey, self::$snapshotCache)) {
            return self::$snapshotCache[$cacheKey];
        }

        $cached = $this->cacheFetch($cacheKey);
        if (is_array($cached)) {
            self::$snapshotCache[$cacheKey] = $cached;

            return $cached;
        }

        $conn = Database::getDoctrineConnection();
        $inventory = Database::prefix('inventory');
        $item = Database::prefix('item');

        $sql = "SELECT {$item}.*, inv.quantity, inv.charges, inv.sellvaluegold, inv.sellvaluegems FROM {$item} INNER JOIN (
                    SELECT itemid, COUNT({$inventory}.itemid) AS quantity, SUM({$inventory}.charges) AS charges, {$inventory}.sellvaluegold AS sellvaluegold, {$inventory}.sellvaluegems AS sellvaluegems
                    FROM {$inventory}
                    WHERE {$inventory}.userid = :userid
                    GROUP BY {$inventory}.itemid, {$inventory}.sellvaluegold, {$inventory}.sellvaluegems ) AS inv
            ON {$item}.itemid = inv.itemid
            WHERE {$item}.hide = :showhide";

        $params = [
            'userid' => $userId,
            'showhide' => $showhide,
        ];

        $types = [
            'userid' => ParameterType::INTEGER,
            'showhide' => ParameterType::INTEGER,
        ];

        if ($class !== 0) {
            $sql .= " AND {$item}.class = :class";
            $params['class'] = $class;
            $types['class'] = ParameterType::STRING;
        }

        $sql .= "
            ORDER BY
            {$item}.class ASC,
            {$item}.name ASC";

        $rows = $conn->executeQuery($sql, $params, $types)->fetchAllAssociative();
        self::$snapshotCache[$cacheKey] = $rows;

        $this->cacheStore($cacheKey, $rows);
        $this->cacheRememberSnapshotKey($userId, $cacheKey);

        return $rows;
    }

    /**
     * Return quantity for a single item id in one user's inventory.
     *
     * @param int $userId Account id.
     * @param int $itemId Item id.
     *
     * @return int
     */
    public function getQuantityByItemId(int $userId, int $itemId): int
    {
        $map = $this->getQuantityMapForUser($userId);

        return (int) ($map[$itemId] ?? 0);
    }

    /**
     * Return a map of itemid => quantity for one user.
     *
     * Caching:
     * - First level: request-local static cache keyed by user id.
     * - Second level: shared datacache key inventory:user:{userId}:qtymap.
     *
     * @param int $userId Account id.
     *
     * @return array<int, int>
     */
    public function getQuantityMapForUser(int $userId): array
    {
        if (array_key_exists($userId, self::$qtyMapCache)) {
            return self::$qtyMapCache[$userId];
        }

        $cacheKey = $this->buildQuantityMapCacheKey($userId);
        $cached = $this->cacheFetch($cacheKey);
        if (is_array($cached)) {
            self::$qtyMapCache[$userId] = $cached;

            return $cached;
        }

        $conn = Database::getDoctrineConnection();
        $inventory = Database::prefix('inventory');
        $rows = $conn->executeQuery(
            "SELECT itemid, COUNT(itemid) AS qty FROM {$inventory} WHERE userid = :userid GROUP BY itemid",
            [
                'userid' => $userId,
            ],
            [
                'userid' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['itemid']] = (int) $row['qty'];
        }

        self::$qtyMapCache[$userId] = $map;
        $this->cacheStore($cacheKey, $map);

        return $map;
    }

    /**
     * Return one inventory row merged with item data for a user + item id.
     *
     * Compatibility contract:
     * - Row shape must match legacy get_inventory_item() output exactly
     *   (`item.*` columns plus quantity/charges/sell values from grouped inv).
     * - Returns false when the user does not own the item.
     *
     * Cache/invalidation contract:
     * - Uses request-local cache only, intentionally.
     * - Write paths must call InventoryReadRepository::invalidateUserLocalCaches()
     *   to prevent stale same-request reads after mutations.
     *
     * @return array<string, mixed>|false
     */
    public function getInventoryItemForUser(int $userId, int $itemId): array|false
    {
        $cacheKey = $userId . ':' . $itemId;
        if (array_key_exists($cacheKey, self::$inventoryItemCache)) {
            return self::$inventoryItemCache[$cacheKey];
        }

        $conn = Database::getDoctrineConnection();
        $inventory = Database::prefix('inventory');
        $item = Database::prefix('item');
        $row = $conn->executeQuery(
            "SELECT {$item}.*, inv.quantity, inv.charges, inv.sellvaluegold, inv.sellvaluegems FROM {$item} INNER JOIN (
                        SELECT itemid, COUNT({$inventory}.itemid) AS quantity, SUM({$inventory}.charges) AS charges, {$inventory}.sellvaluegold AS sellvaluegold, {$inventory}.sellvaluegems AS sellvaluegems
                        FROM {$inventory}
                        WHERE {$inventory}.userid = :userid
                        GROUP BY {$inventory}.itemid, {$inventory}.sellvaluegold, {$inventory}.sellvaluegems ) AS inv
                ON {$item}.itemid = inv.itemid
                WHERE {$item}.itemid = :itemid
                ORDER BY
                {$item}.class ASC,
                {$item}.name ASC",
            [
                'userid' => $userId,
                'itemid' => $itemId,
            ],
            [
                'userid' => ParameterType::INTEGER,
                'itemid' => ParameterType::INTEGER,
            ]
        )->fetchAssociative();

        $value = $row ?: false;
        self::$inventoryItemCache[$cacheKey] = $value;

        return $value;
    }

    /**
     * Return rows for item activation navigation in a specific hook context.
     *
     * Compatibility contract:
     * - SQL shape and row payload remain aligned with legacy display_item_nav().
     * - Returned rows include `item.*` and computed `quantity`.
     *
     * Cache/invalidation contract:
     * - Uses request-local cache only to avoid extra cross-request invalidation
     *   key management while still removing duplicate hot-path SQL within request.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActivatableItemsForHook(int $userId, int $hookMask): array
    {
        $cacheKey = $userId . ':' . $hookMask;
        if (array_key_exists($cacheKey, self::$activatableItemsCache)) {
            return self::$activatableItemsCache[$cacheKey];
        }

        $conn = Database::getDoctrineConnection();
        $itemTable = Database::prefix('item');
        $inventoryTable = Database::prefix('inventory');
        $rows = $conn->executeQuery(
            "SELECT {$itemTable}.*, inv.quantity
                FROM {$itemTable}
                INNER JOIN (
                        SELECT itemid,
                                SUM(IF({$inventoryTable}.charges > 1, {$inventoryTable}.charges, 1)) AS quantity
                        FROM {$inventoryTable}
                        WHERE {$inventoryTable}.userid = :userid
                        GROUP BY {$inventoryTable}.itemid
                ) AS inv ON {$itemTable}.itemid = inv.itemid
                WHERE ({$itemTable}.activationhook & :hook)",
            [
                'userid' => $userId,
                'hook' => $hookMask,
            ],
            [
                'userid' => ParameterType::INTEGER,
                'hook' => ParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        self::$activatableItemsCache[$cacheKey] = $rows;

        return $rows;
    }

    /**
     * Return capacity counters used by add-item pre-check logic.
     *
     * Compatibility contract:
     * - `totalcount` matches legacy summed COUNT(inv.itemid) semantics.
     * - `totalweight` matches legacy sum of (item weight * owned quantity).
     * - Empty inventory returns zero for both keys.
     *
     * Cache/invalidation expectations:
     * - This method intentionally performs a direct read (no persistent cache)
     *   because it guards write decisions and must reflect current DB state.
     *
     * @return array{totalcount:int,totalweight:int}
     */
    public function getCapacityStatsForUser(int $userId): array
    {
        $conn = Database::getDoctrineConnection();
        $inventory = Database::prefix('inventory');
        $item = Database::prefix('item');
        $row = $conn->executeQuery(
            "SELECT COUNT(inv.itemid) AS totalcount, COALESCE(SUM(it.weight), 0) AS totalweight
                FROM {$inventory} AS inv
                INNER JOIN {$item} AS it ON it.itemid = inv.itemid
                WHERE inv.userid = :userid",
            [
                'userid' => $userId,
            ],
            [
                'userid' => ParameterType::INTEGER,
            ]
        )->fetchAssociative();

        return [
            'totalcount' => (int) ($row['totalcount'] ?? 0),
            'totalweight' => (int) ($row['totalweight'] ?? 0),
        ];
    }

    /**
     * Return one item record by item id.
     *
     * @param int $itemId
     *
     * @return array<string, mixed>|false
     */
    public function getItemById(int $itemId): array|false
    {
        if (array_key_exists($itemId, self::$itemByIdCache)) {
            return self::$itemByIdCache[$itemId];
        }

        $cacheKey = $this->buildItemByIdCacheKey($itemId);
        $cached = $this->cacheFetch($cacheKey);
        if ($this->isItemNotFoundMarker($cached)) {
            self::$itemByIdCache[$itemId] = false;

            return false;
        }
        if (is_array($cached)) {
            self::$itemByIdCache[$itemId] = $cached;

            return $cached;
        }

        $conn = Database::getDoctrineConnection();
        $table = Database::prefix('item');
        $item = $conn->executeQuery(
            "SELECT * FROM {$table} WHERE itemid = :itemid LIMIT 1",
            [
                'itemid' => $itemId,
            ],
            [
                'itemid' => ParameterType::INTEGER,
            ]
        )->fetchAssociative();

        $item = $item ?: false;
        self::$itemByIdCache[$itemId] = $item;
        $this->cacheStore($cacheKey, $item === false ? $this->buildItemNotFoundMarker() : $item);

        return $item;
    }

    /**
     * Return one item record by exact name.
     *
     * @param string $name
     *
     * @return array<string, mixed>|false
     */
    public function getItemByName(string $name): array|false
    {
        if (array_key_exists($name, self::$itemByNameCache)) {
            return self::$itemByNameCache[$name];
        }

        $cacheKey = $this->buildItemByNameCacheKey($name);
        $cached = $this->cacheFetch($cacheKey);
        if ($this->isItemNotFoundMarker($cached)) {
            self::$itemByNameCache[$name] = false;

            return false;
        }
        if (is_array($cached)) {
            self::$itemByNameCache[$name] = $cached;

            return $cached;
        }

        $conn = Database::getDoctrineConnection();
        $table = Database::prefix('item');
        $item = $conn->executeQuery(
            "SELECT * FROM {$table} WHERE name = :name LIMIT 1",
            [
                'name' => $name,
            ],
            [
                'name' => ParameterType::STRING,
            ]
        )->fetchAssociative();

        $item = $item ?: false;
        self::$itemByNameCache[$name] = $item;
        $this->cacheStore($cacheKey, $item === false ? $this->buildItemNotFoundMarker() : $item);

        return $item;
    }

    /**
     * Invalidate request-local caches that are scoped by user id.
     *
     * This must be called by write paths to avoid stale reads later in the same
     * request after mutating inventory rows.
     */
    public static function invalidateUserLocalCaches(int $userId): void
    {
        unset(self::$qtyMapCache[$userId]);
        foreach (array_keys(self::$inventoryItemCache) as $inventoryItemKey) {
            if (strpos($inventoryItemKey, $userId . ':') === 0) {
                unset(self::$inventoryItemCache[$inventoryItemKey]);
            }
        }
        foreach (array_keys(self::$activatableItemsCache) as $activatableItemsKey) {
            if (strpos($activatableItemsKey, $userId . ':') === 0) {
                unset(self::$activatableItemsCache[$activatableItemsKey]);
            }
        }

        $prefix = sprintf('inventory:user:%d:snapshot:', $userId);
        foreach (array_keys(self::$snapshotCache) as $snapshotKey) {
            if (strpos($snapshotKey, $prefix) === 0) {
                unset(self::$snapshotCache[$snapshotKey]);
            }
        }
    }

    /**
     * Invalidate request-local item caches.
     */
    public static function invalidateItemLocalCaches($itemId = null, $itemName = null): void
    {
        // Keep parameters untyped for legacy bridge compatibility: older
        // callers may pass numeric-string item ids that are normalized here.
        if ($itemId !== null) {
            unset(self::$itemByIdCache[(int) $itemId]);
        }
        if ($itemName !== null && $itemName !== '') {
            unset(self::$itemByNameCache[(string) $itemName]);
        }
    }

    private function buildSnapshotCacheKey(int $userId, int $showhide, int|string $class): string
    {
        return sprintf('inventory:user:%d:snapshot:%d:%s', $userId, $showhide, (string) $class);
    }

    private function buildQuantityMapCacheKey(int $userId): string
    {
        return sprintf('inventory:user:%d:qtymap', $userId);
    }

    private function buildItemByIdCacheKey(int $itemId): string
    {
        return sprintf('inventory:item:id:%d', $itemId);
    }

    private function buildItemByNameCacheKey(string $name): string
    {
        return sprintf('inventory:item:name:%s', $name);
    }

    private function buildSnapshotIndexCacheKey(int $userId): string
    {
        return sprintf('inventory:user:%d:snapshot:index', $userId);
    }

    /**
     * Read from shared datacache when available.
     *
     * Fallback contract:
     * - If cache backend/functions are unavailable or return a miss, return null
     *   and allow SQL execution.
     *
     */
    private function cacheFetch(string $key): mixed
    {
        if (!function_exists('datacache')) {
            return null;
        }

        $value = datacache($key, self::CACHE_TTL_SECONDS);
        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * Write shared datacache when available.
     *
     * Fallback contract:
     * - If cache backend/functions are unavailable, repository remains fully
     *   functional via request-local cache + SQL.
     *
     * @param mixed $value
     */
    private function cacheStore(string $key, $value): void
    {
        if (!function_exists('updatedatacache')) {
            return;
        }

        updatedatacache($key, $value);
    }

    private function cacheRememberSnapshotKey(int $userId, string $snapshotKey): void
    {
        if (!function_exists('datacache') || !function_exists('updatedatacache')) {
            return;
        }

        $indexKey = $this->buildSnapshotIndexCacheKey($userId);
        $trackedKeys = datacache($indexKey, self::CACHE_TTL_SECONDS);
        if (!is_array($trackedKeys)) {
            $trackedKeys = [];
        }

        if (!in_array($snapshotKey, $trackedKeys, true)) {
            $trackedKeys[] = $snapshotKey;
            updatedatacache($indexKey, $trackedKeys);
        }
    }

    /**
     * @return array<string, bool>
     */
    private function buildItemNotFoundMarker(): array
    {
        return [self::ITEM_NOT_FOUND_MARKER_KEY => true];
    }

    /**
     */
    private function isItemNotFoundMarker(mixed $cached): bool
    {
        return is_array($cached)
            && count($cached) === 1
            && !empty($cached[self::ITEM_NOT_FOUND_MARKER_KEY]);
    }

    /**
     * Expose repository cache TTL to legacy adapter helpers so TTL does not
     * drift between repository reads and cache-index invalidation plumbing.
     */
    public static function getCacheTtlSeconds(): int
    {
        return self::CACHE_TTL_SECONDS;
    }
}
