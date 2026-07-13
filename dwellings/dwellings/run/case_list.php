<?php

require_once('modules/dwellings/run/table_helpers.php');

page_header('Dwellings Registry');

$ref = httpget('ref');
$showonly = trim((string) httpget('showonly'));

$allowedRefs = ['', 'hamlet', 'hof'];
if (!in_array($ref, $allowedRefs, true)) {
    $ref = '';
}

// Keep type filtering but strictly validate the token before using it in SQL.
if ($showonly !== '' && !preg_match('/^[a-z0-9_]+$/i', $showonly)) {
    $showonly = '';
}

$dw = db_prefix('dwellings');
$ac = db_prefix('accounts');
$page = max(1, (int) httpget('page'));
$hamletPerPage = 100;

if ($showonly !== '') {
    /*
     * Validate the requested filter token against the dwelling type registry.
     * The dwellingtypes table stores module identifiers in the `module` column
     * (not `type`), so checking `type` triggers SQLSTATE[42S22].
     */
    $typeCheckSql = "SELECT module FROM " . db_prefix('dwellingtypes') . " WHERE module='$showonly' LIMIT 1";
    $typeCheckResult = db_query($typeCheckSql);
    if (!db_num_rows($typeCheckResult)) {
        $showonly = '';
    }
}

if ($ref === 'hof') {
    addnav('Navigation');
    addnav('Return to HoF', 'hof.php');
} else {
    addnav('Navigation');
    addnav('Back to the Hamlet', 'runmodule.php?module=dwellings');
}

$whereParts = [];
if ($showonly !== '') {
    $whereParts[] = "$dw.type='$showonly'";
    addnav('Show Only Types');
    addnav('Show All', "runmodule.php?module=dwellings&op=list&ref=$ref&showonly=");
}

if ($ref === 'hamlet') {
    $location = $session['user']['location'];
    $whereParts[] = "$dw.location='$location'";
}

$whereSql = '';
if (!empty($whereParts)) {
    $whereSql = ' WHERE ' . implode(' AND ', $whereParts);
}

// Preserve compatibility hooks while deprecated URL sort/order params are intentionally ignored.
modulehook('dwellings-list-type', [
    'ref' => $ref,
    'order' => '',
    'showonly' => $showonly,
    'sortby' => '',
]);

$sql = "SELECT $dw.*, $ac.name AS ownername
        FROM $dw
        LEFT JOIN $ac ON $dw.ownerid = $ac.acctid
        $whereSql
        ORDER BY $dw.name ASC, $dw.dwid ASC";

if ($ref === 'hamlet') {
    /*
     * Hamlet can contain thousands of rows on long-running servers.
     * Keep legacy SQL pagination to avoid rendering every row up front.
     */
    $countSql = "SELECT COUNT($dw.dwid) AS count
            FROM $dw
            LEFT JOIN $ac ON $dw.ownerid = $ac.acctid
            $whereSql";
    $countResult = db_query_cached($countSql, 'dwellings-list-count-' . md5($whereSql), 60);
    $countRow = db_fetch_assoc($countResult);
    $totalRows = (int) ($countRow['count'] ?? 0);
    $totalPages = max(1, (int) ceil($totalRows / $hamletPerPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $hamletPerPage;

    $sql .= " LIMIT $offset,$hamletPerPage";
    $result = db_query_cached($sql, 'dwellings-list-' . md5($whereSql . '|page=' . $page), 60);
} else {
    $result = db_query_cached($sql, 'dwellings-list-' . md5($whereSql), 60);
}

// Load DataTables so sorting/filter/paging is handled client-side.
dwellings_require_datatable_assets();

$nameLabel = translate_inline('Name');
$ownerLabel = translate_inline('Owner');
$typeLabel = translate_inline('Type');
$descLabel = translate_inline('Description');
$statusLabel = translate_inline('Status');
$locLabel = translate_inline('Location');
$interactLabel = translate_inline('Interact');

$headers = [$nameLabel, $ownerLabel, $descLabel];
if ($showonly === '') {
    $headers[] = $typeLabel;
}
if ($ref !== 'hamlet') {
    $headers[] = $locLabel;
}
$headers[] = $statusLabel;
if ($ref !== 'hof') {
    $headers[] = $interactLabel;
}

dwellings_render_datatable_open('dwellings-list-table', $headers);

$status1 = translate_inline('`#Occupied');
$status2 = translate_inline('`@Financing');
$status3 = translate_inline('`QIn Construction');
$status4 = translate_inline('`!Abandoned');
$status5 = translate_inline('`%For Sale');

while ($row = db_fetch_assoc($result)) {
    // Row striping is assigned by DataTables via stripeClasses during redraws.
    rawoutput('<tr><td>');

    $ctype = translate_inline(ucwords(get_module_setting('dwname', $row['type'])));
    $statusName = 'status' . $row['status'];
    $status = $$statusName;

    // Keep status customization hook intact for module compatibility.
    $stat = modulehook('dwellings-status', [
        'rowstatus' => $row['status'],
        'dwid' => $row['dwid'],
        'type' => $row['type'],
        'status' => $status,
    ]);
    $status = $stat['status'];

    $dwellingName = $row['name'];
    if ($dwellingName === '') {
        $dwellingName = translate_inline('Unnamed');
    }
    output_notl($dwellingName);

    rawoutput('</td><td>');
    output_notl($row['ownername'] ?? translate_inline('Abandoned'));

    $windowpeer = $row['windowpeer'];
    if ($windowpeer === '') {
        $windowpeer = translate_inline('This dwelling has no public description yet.');
        if ((int) $row['status'] === 2) {
            $windowpeer = translate_inline('This dwelling is still being built.');
        }
    }

    rawoutput('</td><td>');
    output_notl('%s', $windowpeer);

    if ($showonly === '') {
        rawoutput('</td><td>');
        output_notl($ctype);
    }

    if ($ref !== 'hamlet') {
        rawoutput('</td><td>');
        output_notl('%s', $row['location']);
    }

    rawoutput('</td><td>');
    output_notl('%s', $status);

    if ($ref !== 'hof') {
        rawoutput("</td><td class='is-actions'>");
        // Keep interaction hook call unchanged so custom links/actions continue to render.
        modulehook('dwellings-list-interact', [
            'type' => $row['type'],
            'dwid' => $row['dwid'],
            'owner' => $row['ownerid'],
            'status' => $row['status'],
            'location' => $row['location'],
        ]);
    }

    rawoutput('</td></tr>');
}

$tableOptions = [
    'order' => [[0, 'asc']],
];
if ($ref === 'hamlet') {
    /*
     * Enhancement mode: DataTables styles/search/order only the currently
     * loaded server page. Global search/sort requires async/server endpoints
     * and is intentionally out of scope for this legacy flow.
     */
    $tableOptions['paging'] = false;
}
if ($ref !== 'hof') {
    $tableOptions['columnDefs'] = [
        ['targets' => [count($headers) - 1], 'orderable' => false, 'searchable' => false],
    ];
}

dwellings_render_datatable_close('dwellings-list-table', $tableOptions);

if ($ref === 'hamlet' && $totalRows > 0) {
    addnav('Pages');
    for ($p = 1; $p <= $totalPages; $p++) {
        $from = (($p - 1) * $hamletPerPage) + 1;
        $to = min($p * $hamletPerPage, $totalRows);
        $pageUrl = "runmodule.php?module=dwellings&op=list&ref=hamlet&showonly=$showonly&page=$p";
        if ($p === $page) {
            addnav(["`b`#Page %s`0 (%s-%s)`b", $p, $from, $to], $pageUrl);
        } else {
            addnav(["Page %s (%s-%s)", $p, $from, $to], $pageUrl);
        }
    }
}
