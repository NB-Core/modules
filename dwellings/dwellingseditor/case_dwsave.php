<?php
/**
 * Escape a dwelling editor string value for direct use in legacy SQL fragments.
 *
 * @param string $value Submitted string value.
 * @return string Escaped value without surrounding quotes, or null when no host escape API exists.
 */
function dwellingseditor_escape_sql_value($value)
{
    if (class_exists('\\Lotgd\\SafeEscape')) {
        return \Lotgd\SafeEscape::escape($value);
    }

    if (function_exists('safeescape')) {
        return safeescape($value);
    }

    // Core verification needed: legacy LotGD runtimes should provide safeescape()
    // or the modern \Lotgd\SafeEscape class before this editor writes strings.
    return null;
}

/**
 * Provide safe recovery navigation after a save attempt.
 *
 * The dwsave operation is reached by POST; explicit navs ensure page_footer()
 * persists usable links even when validation fails or no dwelling id is available.
 *
 * @param int $dwid Current dwelling id, or zero for a new dwelling.
 */
function dwellingseditor_add_save_recovery_navs($dwid)
{
    addnav('Navigation');
    addnav('Dwelling List', 'runmodule.php?module=dwellingseditor');
    addnav('Create a New Dwelling', 'runmodule.php?module=dwellingseditor&op=new');

    if ($dwid > 0) {
        addnav('Edit Saved Dwelling', "runmodule.php?module=dwellingseditor&op=edit&dwid=$dwid");
        addnav('View Saved Dwelling', "runmodule.php?module=dwellingseditor&op=dwsu&dwid=$dwid");
    }
}

$dwell = httppost('dwell');
$dwid = (int) httpget('dwid');

if (!is_array($dwell)) {
    output('`$Dwelling save failed: expected submitted dwelling data was not received.`0`n');
    dwellingseditor_add_save_recovery_navs($dwid);
    return;
}

$numericFields = [
    'ownerid',
    'gold',
    'gems',
    'goldvalue',
    'gemvalue',
    'status',
];
$stringFields = [
    'name',
    'type',
    'location',
    'description',
    'windowpeer',
];

$values = [];
foreach ($numericFields as $field) {
    $values[$field] = isset($dwell[$field]) ? (int) $dwell[$field] : 0;
}

foreach ($stringFields as $field) {
    $rawValue = isset($dwell[$field]) ? (string) $dwell[$field] : '';
    $escapedValue = dwellingseditor_escape_sql_value($rawValue);

    if ($escapedValue === null) {
        output('`$Dwelling save failed: no compatible SQL escaping helper is available in this runtime.`0`n');
        dwellingseditor_add_save_recovery_navs($dwid);
        return;
    }

    $values[$field] = $escapedValue;
}

/*
 * postparse() expects a form-definition array in this core lineage, not the
 * nested submitted dwell[...] payload. Build known dwelling columns explicitly
 * so SQL never receives stringified arrays such as "Array" during creation.
 */
$assignments = [];
foreach ($values as $field => $value) {
    if (in_array($field, $numericFields, true)) {
        $assignments[] = "$field=$value";
    } else {
        $assignments[] = "$field='$value'";
    }
}

if ($dwid > 0) {
    $sql = 'UPDATE ' . db_prefix('dwellings') . ' SET ' . implode(',', $assignments) . " WHERE dwid=$dwid";
} else {
    /*
     * storedinfo is maintained internally by dwelling modules and is not
     * rendered by the editor form. New rows still need an explicit empty
     * TEXT value for strict MySQL installations where the column may be
     * declared NOT NULL without a default. Keep this insert-only so routine
     * editor saves do not erase existing module data.
     */
    $insertValuesByColumn = $values;
    if (!array_key_exists('storedinfo', $insertValuesByColumn)) {
        $escapedValue = dwellingseditor_escape_sql_value('');

        if ($escapedValue === null) {
            output('`$Dwelling save failed: no compatible SQL escaping helper is available in this runtime.`0`n');
            dwellingseditor_add_save_recovery_navs($dwid);
            return;
        }

        $insertValuesByColumn['storedinfo'] = $escapedValue;
    }

    $columns = array_keys($insertValuesByColumn);
    $insertValues = [];

    foreach ($columns as $field) {
        if (in_array($field, $numericFields, true)) {
            $insertValues[] = $insertValuesByColumn[$field];
        } else {
            $insertValues[] = "'" . $insertValuesByColumn[$field] . "'";
        }
    }

    $sql = 'INSERT INTO ' . db_prefix('dwellings') . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $insertValues) . ')';
}

db_query($sql);
if (db_affected_rows() > 0) {
    if ($dwid <= 0 && function_exists('db_insert_id')) {
        $dwid = (int) db_insert_id();
    }

    output('`^Dwelling saved!`0`n');
} else {
    output('`^Dwelling `$not`^ saved. Please review the submitted values and try again.`0`n');
}

// Save attempts finish on this page; provide explicit navs to avoid badnav traps.
dwellingseditor_add_save_recovery_navs($dwid);
?>
