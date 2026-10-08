<?php
declare(strict_types=1);

require_once __DIR__ . '/../Domain/InventoryCollection.php';

/**
 * Application service for inventory operations shared by OOP and legacy flows.
 *
 * Strict-typing note:
 * - Strict typing is enabled for the repository/service/domain library layer.
 * - Legacy procedural entrypoints (for example inventory/lib/itemhandler.php)
 *   intentionally remain non-strict for backward compatibility.
 *
 * Compatibility notes:
 * - Public methods intentionally mirror legacy semantics (mixed IDs/names,
 *   false/null return paths, and integer removal counts).
 * - Uses injected callbacks so phase 1 can delegate to existing procedural
 *   implementations without changing database schema or hook behavior.
 */
class InventoryService
{
    /** @var callable(int): array<string, mixed>|false */
    private $getItemById;

    /** @var callable(string): array<string, mixed>|false */
    private $getItemByName;

    /** @var callable(int, int, int|string): array<int, array<string, mixed>> */
    private $getInventoryByUser;

    /** @var callable(int, mixed): int */
    private $checkQtyById;

    /** @var callable(string, mixed): int */
    private $checkQtyByName;

    /** @var callable(int, int, mixed, string, int|false, int|false): bool */
    private $addItemById;

    /** @var callable(string, int, mixed, string, int|false, int|false): bool */
    private $addItemByName;

    /** @var callable(int, int, mixed): int */
    private $removeItemById;

    /** @var callable(string, int, mixed): int */
    private $removeItemByName;

    /**
     * @param array<string, callable> $operations Callback map to legacy data handlers.
     */
    public function __construct(array $operations)
    {
        $this->getItemById = $this->validateOperation($operations, 'getItemById');
        $this->getItemByName = $this->validateOperation($operations, 'getItemByName');
        $this->getInventoryByUser = $this->validateOperation($operations, 'getInventoryByUser');
        $this->checkQtyById = $this->validateOperation($operations, 'checkQtyById');
        $this->checkQtyByName = $this->validateOperation($operations, 'checkQtyByName');
        $this->addItemById = $this->validateOperation($operations, 'addItemById');
        $this->addItemByName = $this->validateOperation($operations, 'addItemByName');
        $this->removeItemById = $this->validateOperation($operations, 'removeItemById');
        $this->removeItemByName = $this->validateOperation($operations, 'removeItemByName');
    }

    /**
     * Load one item by numeric ID or item name.
     *
     * Compatibility: mirrors legacy get_item(); numeric strings are treated as IDs.
     *
     * @return array<string, mixed>|false
     */
    public function getItem($item): array|false
    {
        // Keep $item untyped for legacy bridge compatibility with mixed caller input.
        return $this->invokeItemByNameOrId($item);
    }

    /**
     * Load inventory rows for one player and return legacy row format.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getInventory($user, $showhide, $class): array
    {
        // Keep these parameters untyped for legacy bridge compatibility with
        // request-derived scalars and bool/int sentinel values.
        $normalizedClass = $this->normalizeInventoryClassFilter($class);

        $callback = $this->getInventoryByUser;

        return $callback((int) $user, (int) $showhide, $normalizedClass);
    }

    /**
     * Load inventory rows and expose them as an OOP collection for new callers.
     */
    public function getInventoryCollection($user, $showhide, $class): InventoryCollection
    {
        // Keep these parameters untyped for legacy bridge compatibility with
        // request-derived scalars and bool/int sentinel values.
        $rows = $this->getInventory($user, $showhide, $class);

        return InventoryCollection::fromLegacyRows($rows);
    }

    /**
     * Get quantity by item name or numeric ID.
     *
     * Compatibility: mirrors legacy check_qty(); only native ints are treated as IDs.
     */
    public function checkQty($item, $user): int
    {
        // Keep $item/$user untyped for legacy bridge compatibility.
        // Keep legacy user-default semantics: downstream adapters/helpers often
        // default only on strict `$user === 0`, so we intentionally do not cast
        // string sentinel inputs here.
        return $this->invokeQtyByNameOrId($item, $user);
    }

    /**
     * Add item(s) by item name or numeric ID.
     *
     * Compatibility: mirrors legacy add_item(); only native ints are treated as IDs.
     */
    public function addItem($item, $qty, $user, $specialvalue, $sellvaluegold, $sellvaluegems): bool
    {
        // Keep parameters untyped for legacy bridge compatibility with mixed
        // procedural caller inputs and false sentinel sell-value behavior.
        $normalizedQty = (int) $qty;
        $normalizedSpecialValue = (string) $specialvalue;
        $normalizedSellValueGold = $this->normalizeSellValue($sellvaluegold);
        $normalizedSellValueGems = $this->normalizeSellValue($sellvaluegems);

        return $this->invokeAddByNameOrId(
            $item,
            $normalizedQty,
            $user,
            $normalizedSpecialValue,
            $normalizedSellValueGold,
            $normalizedSellValueGems
        );
    }

    /**
     * Remove item(s) by item name or numeric ID.
     *
     * Compatibility: mirrors legacy remove_item(); only native ints are treated as IDs.
     */
    public function removeItem($item, $qty, $user): int
    {
        // Keep $item/$qty/$user untyped for legacy bridge compatibility.
        $normalizedQty = (int) $qty;

        // Keep legacy user-default semantics: downstream adapters/helpers often
        // default only on strict `$user === 0`, so we intentionally do not cast
        // string sentinel inputs here.
        return $this->invokeRemoveByNameOrId($item, $normalizedQty, $user);
    }

    /**
     * Validate required operation callback and return it for assignment.
     *
     * @param array<string, callable> $operations
     */
    private function validateOperation(array $operations, string $key): callable
    {
        if (!array_key_exists($key, $operations)) {
            throw new InvalidArgumentException("Missing required inventory operation: {$key}");
        }

        if (!is_callable($operations[$key])) {
            throw new InvalidArgumentException("Inventory operation is not callable: {$key}");
        }

        return $operations[$key];
    }

    /**
     * Normalize legacy class filter inputs before reaching typed repository paths.
     *
     * Compatibility note:
     * - Legacy callers often pass HTTP values like "0" for "all classes".
     * - Empty/whitespace-only values should also preserve the same no-filter behavior.
     * - Non-scalar values (e.g. `class[]` request payloads) are normalized to
     *   no-filter to avoid array-to-string warnings and accidental SQL filters.
     * - Repository logic treats int 0 as the no-filter sentinel.
     *
     */
    private function normalizeInventoryClassFilter(mixed $class): int|string
    {
        if ($class === 0 || $class === null || $class === false) {
            return 0;
        }
        if (is_int($class)) {
            return $class;
        }
        if (is_string($class)) {
            $normalizedClass = trim($class);
            if ($normalizedClass === '' || $normalizedClass === '0') {
                return 0;
            }

            return $normalizedClass;
        }
        if (!is_scalar($class)) {
            return 0;
        }

        return (string) $class;
    }

    /**
     * Keep legacy sell-value sentinel behavior for add_item flows.
     *
     * Compatibility note:
     * - `false` means "auto-calculate sell value" downstream.
     * - Any non-false value is normalized to int for strict call boundaries.
     *
     */
    private function normalizeSellValue(mixed $sellValue): int|false
    {
        if ($sellValue === false) {
            return false;
        }

        return (int) $sellValue;
    }

    /**
     * Helper contract: centralizes legacy item lookup dispatch and callable extraction.
     *
     * - Uses is_numeric() semantics so numeric strings route to ID callbacks.
     * - Non-numeric values always route to name callbacks.
     *
     * @return array<string, mixed>|false
     */
    private function invokeItemByNameOrId(mixed $item): array|false
    {
        if (!is_numeric($item)) {
            $callback = $this->getItemByName;

            return $callback((string) $item);
        }

        $callback = $this->getItemById;

        return $callback((int) $item);
    }

    /**
     * Helper contract: centralizes legacy quantity dispatch and callable extraction.
     *
     * - Uses is_int() semantics so numeric strings route to name callbacks.
     */
    private function invokeQtyByNameOrId(mixed $item, mixed $user): int
    {
        if (!is_int($item)) {
            $callback = $this->checkQtyByName;

            return (int) $callback((string) $item, $user);
        }

        $callback = $this->checkQtyById;

        return (int) $callback($item, $user);
    }

    /**
     * Helper contract: centralizes legacy add-item dispatch and callable extraction.
     *
     * - Uses is_int() semantics so numeric strings route to name callbacks.
     */
    private function invokeAddByNameOrId(
        mixed $item,
        int $qty,
        mixed $user,
        string $specialValue,
        int|false $sellValueGold,
        int|false $sellValueGems
    ): bool
    {
        if (!is_int($item)) {
            $callback = $this->addItemByName;

            return (bool) $callback(
                (string) $item,
                $qty,
                $user,
                $specialValue,
                $sellValueGold,
                $sellValueGems
            );
        }

        $callback = $this->addItemById;

        return (bool) $callback(
            $item,
            $qty,
            $user,
            $specialValue,
            $sellValueGold,
            $sellValueGems
        );
    }

    /**
     * Helper contract: centralizes legacy remove-item dispatch and callable extraction.
     *
     * - Uses is_int() semantics so numeric strings route to name callbacks.
     */
    private function invokeRemoveByNameOrId(mixed $item, int $qty, mixed $user): int
    {
        if (!is_int($item)) {
            $callback = $this->removeItemByName;

            return (int) $callback((string) $item, $qty, $user);
        }

        $callback = $this->removeItemById;

        return (int) $callback($item, $qty, $user);
    }
}
