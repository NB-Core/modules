<?php

require_once 'modules/dwellings/run/table_helpers.php';

/**
 * Returns a normalized list of selected dwelling IDs from POST.
 *
 * @return array<int, int>
 */
function dwellingseditor_get_selected_abandoned_ids()
{
    $selected = httppost('demolish');

    if (!is_array($selected)) {
        return [];
    }

    $ids = [];
    foreach ($selected as $dwid) {
        $normalizedId = (int) $dwid;
        if ($normalizedId > 0) {
            $ids[$normalizedId] = $normalizedId;
        }
    }

    return array_values($ids);
}

/**
 * Deletes abandoned dwellings and their keys by dwelling ID.
 *
 * @param array<int, int> $dwids
 *
 * @return int Number of dwellings deleted.
 */
function dwellingseditor_delete_abandoned_dwellings(array $dwids)
{
    if (count($dwids) === 0) {
        return 0;
    }

    $idList = implode(',', $dwids);

    $deleteDwellingsSql = 'DELETE FROM ' . db_prefix('dwellings') . " WHERE status = 4 AND dwid IN ($idList)";
    db_query($deleteDwellingsSql);
    $deletedDwellings = (int) db_affected_rows();

    $deleteKeysSql = 'DELETE FROM ' . db_prefix('dwellingkeys') . " WHERE dwid IN ($idList)";
    db_query($deleteKeysSql);

    return $deletedDwellings;
}

$outputSortLabel = translate_inline('Sort by age proxy');
$oldestFirstLabel = translate_inline('Oldest first (lower ID)');
$newestFirstLabel = translate_inline('Newest first (higher ID)');
$sort = httpget('sort');
$sort = ($sort === 'newest') ? 'newest' : 'oldest';
$sortDirection = ($sort === 'newest') ? 'DESC' : 'ASC';

$confirmed = (int) httppost('confirmdemolish');
if ($confirmed === 1) {
    $selectedIds = dwellingseditor_get_selected_abandoned_ids();
    $deletedCount = dwellingseditor_delete_abandoned_dwellings($selectedIds);

    if ($deletedCount > 0) {
        output('`@Demolished %s abandoned dwellings.`0`n', $deletedCount);
    } elseif (count($selectedIds) > 0) {
        output('`$No selected dwellings were demolished. They may no longer be abandoned.`0`n');
    } else {
        output('`$No dwellings selected for demolition.`0`n');
    }
}

$abandonedSql = 'SELECT d.dwid, d.name, d.ownerid, d.type, d.location, a.name AS ownername '
    . 'FROM ' . db_prefix('dwellings') . ' AS d '
    . 'LEFT JOIN ' . db_prefix('accounts') . ' AS a ON a.acctid = d.ownerid '
    . 'WHERE d.status = 4 '
    . "ORDER BY d.dwid $sortDirection";
$abandonedResult = db_query($abandonedSql);

output('`bAbandoned Dwelling Demolition`b`n');
output('`7This overview lists all dwellings currently marked as abandoned (status 4).`0`n');
output('`7This module has no explicit building timestamp; dwelling ID is used as an age proxy for sorting.`0`n`n');

$sortUrlBase = 'runmodule.php?module=dwellingseditor&op=abandoned';
rawoutput('<div style="margin-bottom:0.75rem;">');
rawoutput('<strong>' . $outputSortLabel . ':</strong> ');
rawoutput('<a href="' . $sortUrlBase . '&sort=oldest">' . $oldestFirstLabel . '</a> | ');
rawoutput('<a href="' . $sortUrlBase . '&sort=newest">' . $newestFirstLabel . '</a>');
rawoutput('</div>');
addnav('', $sortUrlBase . '&sort=oldest');
addnav('', $sortUrlBase . '&sort=newest');

$headers = [
    translate_inline('Select'),
    translate_inline('ID'),
    translate_inline('Name'),
    translate_inline('Owner'),
    translate_inline('Type'),
    translate_inline('Location'),
];

// Reuse the same DataTables assets/helpers used in dwellings list views.
dwellings_require_datatable_assets();

rawoutput('<form action="' . $sortUrlBase . '&sort=' . $sort . '" method="post">');
addnav('', $sortUrlBase . '&sort=' . $sort);

dwellings_render_datatable_open('dwellings-editor-abandoned-table', $headers);

$hasRows = false;
while ($row = db_fetch_assoc($abandonedResult)) {
    $hasRows = true;

    $dwid = (int) $row['dwid'];
    $dwellingName = $row['name'];
    if ($dwellingName === '') {
        $dwellingName = translate_inline('Unnamed');
    }

    $ownerName = $row['ownername'];
    if ($ownerName === null || $ownerName === '') {
        $ownerName = translate_inline('No active owner');
    }

    $typeName = translate_inline(get_module_setting('dwname', $row['type']));

    rawoutput('<tr>');
    rawoutput("<td style='text-align:center;'><input type='checkbox' name='demolish[]' value='{$dwid}'></td>");
    rawoutput('<td>' . $dwid . '</td>');
    rawoutput('<td>');
    output_notl($dwellingName);
    rawoutput('</td><td>');
    output_notl($ownerName);
    rawoutput('</td><td>');
    output_notl($typeName);
    rawoutput('</td><td>');
    output_notl($row['location']);
    rawoutput('</td></tr>');
}

if (!$hasRows) {
    // Leave tbody empty so DataTables can show its empty-table message.
}

dwellings_render_datatable_close('dwellings-editor-abandoned-table', [
    'order' => [[1, ($sort === 'newest') ? 'desc' : 'asc']],
    'columnDefs' => [
        ['orderable' => false, 'targets' => [0]],
    ],
    'pageLength' => 50,
]);

if ($hasRows) {
    rawoutput('<div style="margin-top:0.75rem;">');
    rawoutput('<button class="button" type="button" id="dwellings-editor-select-visible" style="margin-right:0.5rem;">'
        . translate_inline('Select all visible')
        . '</button>');
    rawoutput('<button class="button" type="button" id="dwellings-editor-clear-visible" style="margin-right:0.5rem;">'
        . translate_inline('Clear visible')
        . '</button>');
    rawoutput('<button class="button" type="submit" name="confirmdemolish" value="1">'
        . translate_inline('Demolish selected abandoned dwellings')
        . '</button>');
    rawoutput('</div>');

    /**
     * DataTables keeps the currently visible rows in its own filtered/page state.
     * These controls target only that visible subset so admins can safely bulk-select
     * exactly what the search box currently shows.
     */
    rawoutput("<script>\n"
        . "jQuery(function ($) {\n"
        . "  var table = $('#dwellings-editor-abandoned-table').DataTable();\n"
        . "  $('#dwellings-editor-select-visible').on('click', function () {\n"
        . "    table.rows({ search: 'applied', page: 'current' }).nodes().to$()\n"
        . "      .find(\"input[name='demolish[]']\").prop('checked', true);\n"
        . "  });\n"
        . "  $('#dwellings-editor-clear-visible').on('click', function () {\n"
        . "    table.rows({ search: 'applied', page: 'current' }).nodes().to$()\n"
        . "      .find(\"input[name='demolish[]']\").prop('checked', false);\n"
        . "  });\n"
        . "});\n"
        . "</script>");
} else {
    output('`@No abandoned dwellings are currently available for demolition.`0');
}

rawoutput('</form>');
