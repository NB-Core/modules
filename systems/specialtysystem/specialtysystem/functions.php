<?php

declare(strict_types=1);

// Functions for the specialty system helpers

/**
 * Determine how many chakra points can be spent on a specialty.
 */
function specialtysystem_availableuses(?string $modulename = null): int
{
    $upper = specialtysystem_getskillpoints();
    $lower = specialtysystem_getskillpoints($modulename);
    $uses = specialtysystem_getuses();
    if ($modulename === null) {
        $av = $upper - $uses;
    } else {
        $rest = $upper - $uses;
        $av = ($rest > $lower ? $lower : $rest);
    }
    return $av;
}

/**
 * Get the total skill points a player has for a specialty or overall.
 */
function specialtysystem_getskillpoints(?string $modulename = null): int
{
    require_once 'modules/specialtysystem/datafunctions.php';
    $ret = 0;
    if ($modulename === null) {
        $data = specialtysystem_get();
        if (!is_array($data)) {
            return 0;
        }
        foreach ($data as $value) {
            if (!is_array($value)) {
                continue;
            }
            if (array_key_exists('noaddskillpoints', $value) === false) {
                $value['noaddskillpoints'] = 0;
            }
            if ($value['noaddskillpoints'] > 0) {
                $value['skillpoints'] = max(0, $value['skillpoints'] - $value['noaddskillpoints']);
            }
            $ret += (int) $value['skillpoints'];
        }
    } else {
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
    return (int) get_module_pref('uses', 'specialtysystem');
}

/**
 * Overwrite the number of used chakra points for the round.
 */
function specialtysystem_setuses(int $value): void
{
    set_module_pref('uses', $value, 'specialtysystem');
}

/**
 * Increase the per-round chakra usage counter.
 */
function specialtysystem_incrementuses(string $modulename, int $value): void
{
    require_once 'modules/specialtysystem/datafunctions.php';
    $uses = get_module_pref('uses', 'specialtysystem');
    if ($uses != '') {
        $uses += (int) $value;
    } else {
        $uses = (int) $value;
    }
    set_module_pref('uses', $uses, 'specialtysystem');
}

/**
 * Start a new fight navigation block.
 */
function specialtysystem_addfightheadline(string $name, ?int $uses = null, ?int $max = null): void
{
    global $specialtycollector;
    if (!is_array($specialtycollector)) {
        $specialtycollector = [];
    }
    if ($uses !== null && $max !== null && $uses != 0 && $max != 0) {
        $header = sprintf_translate("$name (%s/%s points)`0", $uses, $max);
    } else {
        $header = translate_inline($name) . '`0';
    }
    $specialtycollector[] = ['headline' => $header];
}

/**
 * Add an individual navigation link to the current fight block.
 */
function specialtysystem_addfightnav($name, ?string $link = null, ?int $uses = null): void
{
    global $specialtycollector;
    if (is_array($name)) {
        $name = sprintf_translate(...$name);
    } elseif ($uses !== null) {
        $name = sprintf_translate(' > %s`7 (%s)', $name, $uses);
    } else {
        $name = translate_inline($name);
    }
    if (is_array($specialtycollector) && $link === null) {
        $specialtycollector[end($specialtycollector)][] = [$name];
    } else {
        $specialtycollector[] = implode('|||', [$name, $link]);
    }
}

/**
 * Retrieve and clear the collected fight navigation block.
 */
function specialtysystem_getfightnav()
{
    global $specialtycollector;
    $return = $specialtycollector;
    $specialtycollector = false;
    return $return;
}
