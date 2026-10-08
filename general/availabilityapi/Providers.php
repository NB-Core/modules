<?php

declare(strict_types=1);

use Doctrine\DBAL\ArrayParameterType;
use Lotgd\MySQL\Database;

/**
 * A predictable provider failure suitable for conversion to a public API error.
 */
final class AvailabilityApiServiceException extends RuntimeException
{
}

/**
 * Convert LoTGD-formatted content to plain text for JSON consumers.
 */
function availabilityapi_plain_text(string $value): string
{
    if (!function_exists('color_sanitize')) {
        throw new AvailabilityApiServiceException('Plain-text conversion is unavailable.');
    }

    // Decode entities before removing tags so encoded markup cannot reappear as
    // raw angle brackets after the markup-removal boundary has already passed.
    $decoded = html_entity_decode(
        (string) color_sanitize($value),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );

    return trim(strip_tags($decoded));
}

/**
 * Read the completed mount-rarity state without running daily calculations.
 *
 * @return array<int, array<string, int|string>>
 */
function availabilityapi_mounts_dataset(): array
{
    if (!function_exists('is_module_active') || !is_module_active('mountrarity')) {
        throw new AvailabilityApiServiceException('Mount availability is unavailable.');
    }
    if (!function_exists('get_module_objpref')) {
        throw new AvailabilityApiServiceException('Mount availability preferences are unavailable.');
    }

    $sql = 'SELECT mountid, mountname, mountcostgold, mountcostgems, mountlocation'
        . ' FROM ' . db_prefix('mounts') . ' WHERE mountactive = 1';
    $result = db_query($sql);
    if (!$result) {
        throw new AvailabilityApiServiceException('Mount availability could not be read.');
    }

    $mounts = [];
    while ($row = db_fetch_assoc($result)) {
        $id = (int) $row['mountid'];
        $unavailable = (bool) get_module_objpref('mounts', $id, 'unavailable', 'mountrarity');
        $daysRemaining = (int) get_module_objpref('mounts', $id, 'mountdays_remaining', 'mountrarity');

        // This is the same completed-day state interpreted by stables-nav. It
        // deliberately does not invoke mountrarity's random new-day hook.
        if ($unavailable || $daysRemaining <= 0) {
            continue;
        }

        // Sensitive mount buffs and all non-public columns are excluded here.
        $mounts[] = [
            'id' => $id,
            'name' => availabilityapi_plain_text((string) $row['mountname']),
            'gold' => (int) $row['mountcostgold'],
            'gems' => (int) $row['mountcostgems'],
            'location' => availabilityapi_plain_text((string) $row['mountlocation']),
            'availability_days_remaining' => $daysRemaining,
        ];
    }

    usort($mounts, static function (array $left, array $right): int {
        return strcasecmp($left['name'], $right['name']) ?: $left['id'] <=> $right['id'];
    });

    return $mounts;
}

/**
 * Parse merchant forbidden stock into unique positive item IDs.
 *
 * @return array<int, true>
 */
function availabilityapi_forbidden_item_ids(string $setting): array
{
    $ids = [];
    foreach (explode(',', $setting) as $value) {
        $value = trim($value);
        if (preg_match('/^[1-9][0-9]*$/D', $value)) {
            $ids[(int) $value] = true;
        }
    }

    return $ids;
}

/**
 * Read the merchant's already-calculated stock without rerolling availability.
 *
 * @return array<int, array<string, bool|int|string>>
 */
function availabilityapi_merchant_dataset(): array
{
    if (!function_exists('is_module_active') || !is_module_active('ninjamerchantstore')) {
        throw new AvailabilityApiServiceException('Merchant availability is unavailable.');
    }
    if (!function_exists('get_module_setting')) {
        throw new AvailabilityApiServiceException('Merchant availability settings are unavailable.');
    }

    $forbiddenSetting = get_module_setting('forbidden', 'ninjamerchantstore');
    if ($forbiddenSetting === false || $forbiddenSetting === null) {
        throw new AvailabilityApiServiceException('Merchant availability has not been calculated.');
    }
    $forbidden = availabilityapi_forbidden_item_ids((string) $forbiddenSetting);

    $itemsTable = Database::prefix('item');
    // This explicit field list is the security boundary: executable behavior,
    // serialized effects, scripts, restrictions, and metadata never leave SQL.
    $sql = "SELECT itemid, name, class, description, gold, gems, charges, uniqueforplayer
              FROM {$itemsTable}
             WHERE buyable = 1";
    $parameters = [];
    $types = [];
    if ($forbidden !== []) {
        // DBAL expands and binds the verified positive integers; no ID is ever
        // interpolated into SQL, and excluded rows are not hydrated into PHP.
        $sql .= ' AND itemid NOT IN (:forbidden_ids)';
        $parameters['forbidden_ids'] = array_keys($forbidden);
        $types['forbidden_ids'] = ArrayParameterType::INTEGER;
    }
    try {
        $connection = Database::getDoctrineConnection();
        $rows = $connection->executeQuery($sql, $parameters, $types)->fetchAllAssociative();
    } catch (Throwable $exception) {
        // Database/schema availability is an expected provider dependency
        // failure, not an unclassified endpoint failure. Preserve the cause for
        // operators while the endpoint exposes only its generic 503 response.
        throw new AvailabilityApiServiceException(
            'Merchant availability could not be read.',
            0,
            $exception
        );
    }

    $items = [];
    foreach ($rows as $row) {
        $id = (int) $row['itemid'];
        $category = availabilityapi_plain_text((string) $row['class']);
        $items[] = [
            'id' => $id,
            'name' => availabilityapi_plain_text((string) $row['name']),
            'category' => $category,
            'description' => availabilityapi_plain_text((string) $row['description']),
            'gold' => (int) $row['gold'],
            'gems' => (int) $row['gems'],
            'charges' => (int) $row['charges'],
            'unique_for_player' => (bool) $row['uniqueforplayer'],
            // The shop limits every available non-Loot item as its daily special.
            'special_offer' => strcasecmp($category, 'Loot') !== 0,
        ];
    }

    usort($items, static function (array $left, array $right): int {
        return strcasecmp($left['category'], $right['category'])
            ?: (strcasecmp($left['name'], $right['name']) ?: $left['id'] <=> $right['id']);
    });

    return $items;
}
