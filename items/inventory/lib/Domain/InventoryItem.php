<?php
declare(strict_types=1);

/**
 * Value/read object representing one inventory row with item metadata.
 *
 * Strict-typing note:
 * - Strict typing is enabled for the repository/service/domain library layer.
 * - Legacy procedural entrypoints (for example inventory/lib/itemhandler.php)
 *   intentionally remain non-strict for backward compatibility.
 *
 * Compatibility notes:
 * - Stores the original associative array payload so legacy keys and values are
 *   preserved exactly for procedural consumers.
 * - This class is intentionally read-only in phase 1 and does not mutate
 *   database state.
 */
class InventoryItem
{
    /**
     * Original item + inventory state payload from legacy queries.
     *
     * @var array<string, mixed>
     */
    private $data;

    /**
     * @param array<string, mixed> $data Legacy item/inventory row data.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the numeric item ID from the payload.
     *
     * @return int
     */
    public function getItemId(): int
    {
        return (int) ($this->data['itemid'] ?? 0);
    }

    /**
     * Get the current quantity value from the payload.
     *
     * @return int
     */
    public function getQuantity(): int
    {
        return (int) ($this->data['quantity'] ?? 0);
    }

    /**
     * Export the preserved legacy row payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
