<?php

declare(strict_types=1);

// Functions for the specialty system helpers.

/**
 * Determine how many chakra points can be spent on a specialty.
 *
 * This accounts for overall available points and remaining points for the
 * specific module when requested.
 */
function specialtysystem_availableuses(?string $modulename = null): int
{
    // Total points across all specialties.
    $upper = specialtysystem_getskillpoints();
    // Points for the requested module, if any.
    $lower = specialtysystem_getskillpoints($modulename);
    // Points already spent this round.
    $uses = specialtysystem_getuses();
    if ($modulename === null) {
        // No module specified: remaining points are total minus used.
        $av = $upper - $uses;
    } else {
        // With a module: remaining is capped by that module's points.
        $rest = $upper - $uses;
        $av = ($rest > $lower ? $lower : $rest);
    }
    return $av;
}

/**
 * Get the total skill points a player has for a specialty or overall.
 *
 * When a module is specified, only that module's points are returned.
 * Otherwise, the total of all specialties is calculated.
 */
function specialtysystem_getskillpoints(?string $modulename = null): int
{
    // Load specialty data helpers.
    require_once 'modules/specialtysystem/datafunctions.php';
    $ret = 0;
    if ($modulename === null) {
        // Aggregate all specialties.
        $data = specialtysystem_get();
        if (!is_array($data)) {
            return 0;
        }
        foreach ($data as $value) {
            if (!is_array($value)) {
                continue;
            }
            // Default flag when unset.
            if (array_key_exists('noaddskillpoints', $value) === false) {
                $value['noaddskillpoints'] = 0;
            }
            // Subtract non-additive points if present.
            if ($value['noaddskillpoints'] > 0) {
                $value['skillpoints'] = max(0, $value['skillpoints'] - $value['noaddskillpoints']);
            }
            $ret += (int) $value['skillpoints'];
        }
    } else {
        // Return only the requested module's points.
        $data = specialtysystem_get($modulename);
        if ($data !== false) {
            $ret = (int) $data['skillpoints'];
        }
    }
    return $ret;
}

/**
 * Current total of chakra points spent this round.
 */
function specialtysystem_getuses(): int
{
    // Module pref is stored as a string; cast to int.
    return (int) get_module_pref('uses', 'specialtysystem');
}

/**
 * Overwrite the number of used chakra points for the round.
 */
function specialtysystem_setuses(int $value): void
{
    // Set the per-round usage counter explicitly.
    set_module_pref('uses', $value, 'specialtysystem');
}

/**
 * Increase the per-round chakra usage counter.
 */
function specialtysystem_incrementuses(string $modulename, int $value): void
{
    // Load specialty data helpers for consistency with the module system.
    require_once 'modules/specialtysystem/datafunctions.php';
    // Fetch current usage total.
    $uses = get_module_pref('uses', 'specialtysystem');
    if ($uses != '') {
        // Increment existing usage count.
        $uses += (int) $value;
    } else {
        // Initialize usage count if empty.
        $uses = (int) $value;
    }
    // Persist updated usage.
    set_module_pref('uses', $uses, 'specialtysystem');
}

/**
 * Start a new fight navigation block.
 *
 * Supports either numeric point headers or a translated label override.
 */
function specialtysystem_addfightheadline(
    string $name,
    ?int $uses = null,
    ?int $max = null,
    ?string $usesLabel = null
): void
{
    global $specialtycollector;
    if (!is_array($specialtycollector)) {
        // Initialize collector on first use.
        $specialtycollector = [];
    }
    if ($usesLabel !== null) {
        // Use translated label when provided (e.g., "once per day").
        $label = translate_inline($usesLabel);
        $header = sprintf_translate('%s (%s)`0', translate_inline($name), $label);
    } elseif ($uses !== null && $max !== null && $uses != 0 && $max != 0) {
        // Fallback to numeric points display for legacy callers.
        $header = sprintf_translate("$name (%s/%s points)`0", $uses, $max);
    } else {
        // Just show the name when no usage information exists.
        $header = translate_inline($name) . '`0';
    }
    // Store headline as the start of a new block.
    $specialtycollector[] = ['headline' => $header];
}

/**
 * Add an individual navigation link to the current fight block.
 *
 * Supports label overrides to display non-numeric usage text in the suffix.
 */
function specialtysystem_addfightnav(
    $name,
    ?string $link = null,
    ?int $uses = null,
    ?string $usesLabel = null
): void
{
    global $specialtycollector;
    if (is_array($name)) {
        // Format a translated nav label from a template array.
        $name = sprintf_translate(...$name);
    } elseif ($usesLabel !== null) {
        // Use translated label when provided (e.g., "once").
        $label = translate_inline($usesLabel);
        $name = sprintf_translate(' > %s`7 (%s)', $name, $label);
    } elseif ($uses !== null) {
        // Use numeric usage when available.
        $name = sprintf_translate(' > %s`7 (%s)', $name, $uses);
    } else {
        // Basic translation for simple labels.
        $name = translate_inline($name);
    }
    if (is_array($specialtycollector) && $link === null) {
        // Append to current block when only a label is provided.
        $specialtycollector[end($specialtycollector)][] = [$name];
    } else {
        // Otherwise, add a new entry with a link target.
        $specialtycollector[] = implode('|||', [$name, $link]);
    }
}

/**
 * Retrieve and clear the collected fight navigation block.
 */
function specialtysystem_getfightnav()
{
    global $specialtycollector;
    // Return current block and reset collector for next use.
    $return = $specialtycollector;
    $specialtycollector = false;
    return $return;
}
