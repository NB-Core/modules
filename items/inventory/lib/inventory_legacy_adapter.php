<?php

require_once __DIR__ . '/Repository/InventoryReadRepository.php';
require_once __DIR__ . '/Repository/InventoryWriteRepository.php';
require_once __DIR__ . '/Service/InventoryService.php';

/**
 * Build (or return cached) inventory service instance for procedural callers.
 *
 * @return InventoryService
 */
function inventory_legacy_get_service()
{
    static $service = null;

    if ($service instanceof InventoryService) {
        return $service;
    }

    $service = new InventoryService(
        [
            'getItemById' => 'get_item_by_id',
            'getItemByName' => 'get_item_by_name',
            'getInventoryByUser' => 'inventory_legacy_get_inventory_rows',
            'checkQtyById' => 'check_qty_by_id',
            'checkQtyByName' => 'check_qty_by_name',
            'addItemById' => 'add_item_by_id',
            'addItemByName' => 'add_item_by_name',
            'removeItemById' => 'remove_item_by_id',
            'removeItemByName' => 'remove_item_by_name',
        ]
    );

    return $service;
}

/**
 * Build (or return cached) read repository instance for legacy wrappers.
 *
 * @return InventoryReadRepository
 */
function inventory_legacy_get_read_repository()
{
    static $repository = null;

    if ($repository instanceof InventoryReadRepository) {
        return $repository;
    }

    $repository = new InventoryReadRepository();

    return $repository;
}

/**
 * Build (or return cached) write repository instance for legacy wrappers.
 *
 * @return InventoryWriteRepository
 */
function inventory_legacy_get_write_repository()
{
    static $repository = null;

    if ($repository instanceof InventoryWriteRepository) {
        return $repository;
    }

    $repository = new InventoryWriteRepository();

    return $repository;
}

/**
 * Adapter wrapper for get_item() legacy signature.
 *
 * @param int|string $item
 *
 * @return array<string, mixed>|false
 */
function inventory_legacy_get_item($item)
{
    return inventory_legacy_get_service()->getItem($item);
}

/**
 * Adapter wrapper for get_inventory() legacy signature.
 *
 * @param int        $user
 * @param int|bool   $showhide
 * @param int|string $class
 *
 * @return array<int, array<string, mixed>>
 */
function inventory_legacy_get_inventory($user, $showhide, $class)
{
    return inventory_legacy_get_service()->getInventory($user, $showhide, $class);
}

/**
 * Adapter wrapper for check_qty() legacy signature.
 *
 * @param int|string $item
 * @param int        $user
 *
 * @return int
 */
function inventory_legacy_check_qty($item, $user)
{
    return inventory_legacy_get_service()->checkQty($item, $user);
}

/**
 * Adapter wrapper for add_item() legacy signature.
 *
 * @param int|string $item
 * @param int        $qty
 * @param int        $user
 * @param string     $specialvalue
 * @param int|bool   $sellvaluegold
 * @param int|bool   $sellvaluegems
 *
 * @return bool
 */
function inventory_legacy_add_item($item, $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems)
{
    return inventory_legacy_get_service()->addItem(
        $item,
        $qty,
        $user,
        $specialvalue,
        $sellvaluegold,
        $sellvaluegems
    );
}

/**
 * Adapter wrapper for remove_item() legacy signature.
 *
 * @param int|string $item
 * @param int        $qty
 * @param int        $user
 *
 * @return int
 */
function inventory_legacy_remove_item($item, $qty, $user)
{
    return inventory_legacy_get_service()->removeItem($item, $qty, $user);
}

/**
 * Read wrapper used by get_item_by_id() procedural function.
 *
 * @param int $itemid
 *
 * @return array<string, mixed>|false
 */
function inventory_legacy_get_item_by_id($itemid)
{
    return inventory_legacy_get_read_repository()->getItemById((int) $itemid);
}

/**
 * Read wrapper used by get_item_by_name() procedural function.
 *
 * @param string $itemname
 *
 * @return array<string, mixed>|false
 */
function inventory_legacy_get_item_by_name($itemname)
{
    return inventory_legacy_get_read_repository()->getItemByName((string) $itemname);
}

/**
 * Read wrapper used by check_qty_by_id() procedural function.
 *
 * @param int $itemid
 * @param int $user
 *
 * @return int
 */
function inventory_legacy_check_qty_by_id($itemid, $user)
{
    return inventory_legacy_get_read_repository()->getQuantityByItemId((int) $user, (int) $itemid);
}

/**
 * Helper callable used by InventoryService to retrieve raw legacy rows.
 *
 * Kept separate from get_inventory() to avoid adapter recursion when get_inventory()
 * delegates back into the service layer.
 *
 * @param int        $user
 * @param int|bool   $showhide
 * @param int|string $class
 *
 * @return array<int, array<string, mixed>>
 */
function inventory_legacy_get_inventory_rows($user, $showhide, $class)
{
    global $session;

    if ($user === 0) {
        $user = $session['user']['acctid'];
    }

    return inventory_legacy_get_read_repository()->getInventorySnapshotForUser(
        (int) $user,
        (int) $showhide,
        $class
    );
}

/**
 * Build all user-scope inventory read cache keys used by the new repository.
 *
 * Invalidation contract:
 * - Writes MUST invalidate both legacy cache keys and the normalized repository
 *   keys so read paths never return stale post-write data.
 * - Snapshot keys are tracked in a user-specific index and invalidated in bulk.
 *
 * @param int $userId
 *
 * @return array<int, string>
 */
function inventory_legacy_get_user_cache_keys_for_invalidation($userId)
{
    $userId = (int) $userId;
    $keys = [
        "inventory-user-{$userId}",
        "inventory:user:{$userId}:qtymap",
    ];

    $snapshotIndexKey = "inventory:user:{$userId}:snapshot:index";
    $keys[] = $snapshotIndexKey;

    // If datacache is unavailable, callers still invalidate deterministic keys.
    if (function_exists('datacache')) {
        $snapshotKeys = datacache($snapshotIndexKey, InventoryReadRepository::getCacheTtlSeconds());
        if (is_array($snapshotKeys)) {
            foreach ($snapshotKeys as $snapshotKey) {
                if (is_string($snapshotKey) && $snapshotKey !== '') {
                    $keys[] = $snapshotKey;
                }
            }
        }
    }

    return array_values(array_unique($keys));
}

/**
 * Invalidate user-scope inventory read caches.
 *
 * Fallback behavior:
 * - If invalidatedatacache() is unavailable, this function is a no-op and
 *   repository reads gracefully fall back to direct SQL for correctness.
 *
 * @param int $userId
 *
 * @return void
 */
function inventory_legacy_invalidate_user_read_caches($userId)
{
    // Always clear request-local static caches first to prevent stale reads
    // later in the same request after a successful write operation.
    InventoryReadRepository::invalidateUserLocalCaches((int) $userId);

    if (!function_exists('invalidatedatacache')) {
        return;
    }

    foreach (inventory_legacy_get_user_cache_keys_for_invalidation((int) $userId) as $cacheKey) {
        invalidatedatacache($cacheKey);
    }
}

/**
 * Return the ids of all accounts that hold the given item.
 *
 * @param int $itemId
 *
 * @return array<int, int>
 */
function inventory_legacy_item_holders($itemId)
{
    $itemId = (int) $itemId;
    $inventory = db_prefix('inventory');
    $holders = [];
    $result = db_query("SELECT DISTINCT userid FROM {$inventory} WHERE itemid = {$itemId}");
    while ($row = db_fetch_assoc($result)) {
        $holders[] = (int) $row['userid'];
    }

    return $holders;
}

/**
 * Invalidate the read caches of every account that holds the given item.
 *
 * Inventory snapshots embed the item's own columns (name, class, sell
 * values, ...), so changing an item definition must refresh every holder.
 *
 * @param int $itemId
 *
 * @return void
 */
function inventory_legacy_invalidate_item_holders($itemId)
{
    foreach (inventory_legacy_item_holders($itemId) as $userId) {
        inventory_legacy_invalidate_user_read_caches($userId);
    }
}

/**
 * Remove an item from every inventory and invalidate the read caches of the
 * accounts that held it.
 *
 * Writes that bypass the repository must invalidate the caches of every
 * affected account, or reads keep returning the removed rows until the shared
 * cache expires. A delete by item id cannot name those accounts up front, so
 * they are collected first.
 *
 * @param int $itemId
 *
 * @return int Number of inventory rows removed.
 */
function inventory_legacy_delete_item_from_all_inventories($itemId)
{
    $itemId = (int) $itemId;
    $inventory = db_prefix('inventory');
    $holders = inventory_legacy_item_holders($itemId);
    db_query("DELETE FROM {$inventory} WHERE itemid = {$itemId}");
    $removed = (int) db_affected_rows();
    foreach ($holders as $userId) {
        inventory_legacy_invalidate_user_read_caches($userId);
    }

    return $removed;
}

/**
 * Invalidate item-level read cache keys used by repository and legacy paths.
 *
 * @param int|null                          $itemId
 * @param string|array<int, string>|null    $itemNames
 *
 * @return void
 */
function inventory_legacy_invalidate_item_read_caches($itemId = null, $itemNames = null)
{
    // Always clear request-local static caches first. Shared-cache invalidation
    // alone is not enough to avoid same-request stale reads.
    InventoryReadRepository::invalidateItemLocalCaches($itemId === null ? null : (int) $itemId);

    $normalizedNames = [];
    if (is_array($itemNames)) {
        foreach ($itemNames as $name) {
            if (is_string($name) && $name !== '') {
                $normalizedNames[] = $name;
            }
        }
    } elseif (is_string($itemNames) && $itemNames !== '') {
        $normalizedNames[] = $itemNames;
    }
    $normalizedNames = array_values(array_unique($normalizedNames));
    foreach ($normalizedNames as $normalizedName) {
        InventoryReadRepository::invalidateItemLocalCaches(null, $normalizedName);
    }

    if (!function_exists('invalidatedatacache')) {
        return;
    }

    $keys = [];
    if ($itemId !== null) {
        $keys[] = 'item-id-' . (int) $itemId;
        $keys[] = 'inventory:item:id:' . (int) $itemId;
    }

    foreach ($normalizedNames as $normalizedName) {
        $keys[] = 'item-name-' . $normalizedName;
        $keys[] = 'inventory:item:name:' . $normalizedName;
    }

    foreach (array_unique($keys) as $cacheKey) {
        invalidatedatacache($cacheKey);
    }
}
