<?php

declare(strict_types=1);

/**
 * Merge the given specialty data into the user's "data" preference.
 *
 * @param array $array Array of module names => data to store
 * @param int|null $user Optional user id, defaults to current user
 */
function specialtysystem_set(array $array, ?int $user = null): void
{
    $var = get_module_pref('data', 'specialtysystem', $user) ?? "";
    $get = unserialize(stripslashes($var));
    if (!is_array($get)) {
        $get = [];
    }

    foreach ($array as $moduleName => $data) {
        $get[$moduleName] = $data;
    }

    set_module_pref('data', addslashes(serialize($get)), 'specialtysystem', $user);
}

/**
 * Reset daily specialty data and ensure the data structure exists.
 */
function specialtysystem_newday(): void
{
    global $session;
    specialtysystem_initialize_data();
    set_module_pref('uses', 0, 'specialtysystem');
    $session['user']['specialmisc'] = '';
}

/**
 * Retrieve specialty definitions from the database.
 *
 * Results are cached and can be requested for a single module or all
 * of them. The returned array is keyed by module name.
 */
function specialtysystem_getspecs(?string $modulename = null): array
{
    if (($spec = datacache('specialtygetspecs', 3600)) != false) {
        if ($modulename === null) {
            return unserialize(stripslashes($spec));
        }
        $ret = unserialize(stripslashes($spec));
        return [$modulename => $ret[$modulename]];
    }
    $where = $modulename !== null ? " WHERE modulename='$modulename';" : '';
    $sql = 'SELECT * FROM ' . db_prefix('specialtysystem') . $where;
    $result = db_query($sql);
    $spec = [];
    while ($row = db_fetch_assoc($result)) {
        $spec[$row['modulename']] = $row;
    }
    if ($modulename === null) {
        updatedatacache('specialtygetspecs', addslashes(serialize($spec)));
    }
    return $spec;
}

/**
 * Ensure the "data" userpref contains all registered specialties.
 *
 * Missing entries are created with their basic uses. The current
 * active specialty is stored under the "active" key.
 */
function specialtysystem_initialize_data(): array
{
    $raw = get_module_pref('data', 'specialtysystem') ?? '';
    $data = unserialize(stripslashes($raw));
    if (!is_array($data)) {
        $data = [];
    }
    $specs = specialtysystem_getspecs();
    foreach ($specs as $name => $info) {
        if (!isset($data[$name])) {
            $uses = isset($info['basic_uses']) ? (int) $info['basic_uses'] : 0;
            $data[$name] = ['skillpoints' => $uses];
        }
    }
    if (!isset($data['active'])) {
        $data['active'] = '';
    }
    set_module_pref('data', addslashes(serialize($data)), 'specialtysystem');
    return $data;
}

/**
 * Increase the skill points for a given specialty module.
 */
function specialtysystem_increment(string $modulename, int $value = 1): int
{
    $raw = get_module_pref('data', 'specialtysystem') ?? '';
    $get = unserialize(stripslashes($raw));
    if (!is_array($get)) {
        $get = [];
    }
    if (array_key_exists($modulename, $get)) {
        $exist = $get[$modulename];
        $exist['skillpoints'] += $value;
        $get[$modulename] = array_unique($exist);
    } else {
        $exist = ['skillpoints' => $value];
        $get[$modulename] = $exist;
    }
    set_module_pref('data', addslashes(serialize($get)), 'specialtysystem');
    return $exist['skillpoints'];
}

/**
 * Retrieve specialty information from the user's "data" pref.
 *
 * @param string|null $modulename Specific module to fetch or null for all
 * @param int|null $user Optional user id to query
 *
 * @return mixed Array of data, module data, or false if not found
 */
function specialtysystem_get(?string $modulename = null, ?int $user = null)
{
    $raw = get_module_pref('data', 'specialtysystem', $user) ?? '';
    $get = unserialize(stripslashes($raw));
    if (!is_array($get)) {
        $get = [];
    }
    if ($modulename === null) {
        return $get;
    }
    if (isset($get[$modulename]) && $get[$modulename] != '') {
        return $get[$modulename];
    }
    return false;
}
