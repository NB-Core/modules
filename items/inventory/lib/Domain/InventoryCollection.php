<?php
declare(strict_types=1);

require_once __DIR__ . '/InventoryItem.php';

/**
 * Typed inventory item collection used by service and legacy bridge layers.
 *
 * Strict-typing note:
 * - Strict typing is enabled for the repository/service/domain library layer.
 * - Legacy procedural entrypoints (for example inventory/lib/itemhandler.php)
 *   intentionally remain non-strict for backward compatibility.
 *
 * Compatibility notes:
 * - Collection can be created from legacy associative rows.
 * - Legacy array output remains available so procedural callers keep existing
 *   return contracts.
 */
class InventoryCollection implements IteratorAggregate, Countable
{
    /**
     * @var InventoryItem[]
     */
    private $items;

    /**
     * @param InventoryItem[] $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Build a collection from legacy inventory rows.
     *
     * @param array<int, array<string, mixed>> $rows
     *
     * @return self
     */
    public static function fromLegacyRows(array $rows): self
    {
        $items = [];
        foreach ($rows as $row) {
            $items[] = new InventoryItem($row);
        }

        return new self($items);
    }

    /**
     * @return Traversable<int, InventoryItem>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Find an item by ID.
     */
    public function findByItemId(int $itemId): ?InventoryItem
    {
        foreach ($this->items as $item) {
            if ($item->getItemId() === $itemId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Sum all item quantities in this collection.
     *
     * @return int
     */
    public function getTotalQuantity(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getQuantity();
        }

        return $total;
    }

    /**
     * Convert back to legacy associative rows.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toLegacyArray(): array
    {
        $rows = [];
        foreach ($this->items as $item) {
            $rows[] = $item->toArray();
        }

        return $rows;
    }
}
