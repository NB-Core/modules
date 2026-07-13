<?php

require_once('modules/dwellings/run/table_helpers.php');

/**
 * Renders a row for tables that list dwellings outside the current village.
 *
 * @param array<string, mixed> $row Dwelling row data.
 * @param string $owner Display name of the owner.
 * @param string $typeName Translated dwelling type.
 * @param string $locationLabel Location column output.
 * @param string $actionLabel Action label (enter/travel).
 * @param string $actionUrl Optional URL if the action is clickable.
 */
function dwellings_render_remote_access_row(array $row, $owner, $typeName, $locationLabel, $actionLabel, $actionUrl)
{
    // DataTables redraws rows after sort/search/page operations, so striping must be client-side.
    rawoutput('<tr><td>');
    if ($row['name'] == '') {
        $name = translate_inline('Unnamed');
    } else {
        $name = $row['name'];
    }
    output_notl($name);
    rawoutput('</td><td>');
    output_notl($owner);
    rawoutput('</td><td>');
    output_notl($locationLabel);
    rawoutput('</td><td>');
    rawoutput(appoencode($row['windowpeer']));
    rawoutput('</td><td>');
    output_notl($typeName);
    rawoutput("</td><td class='is-actions' nowrap>[");
    if ($actionUrl !== '') {
        rawoutput("<a href='$actionUrl'>");
        output_notl($actionLabel);
        rawoutput('</a>');
        addnav('', $actionUrl);
    } else {
        output_notl($actionLabel);
    }
    rawoutput(']</td></tr>');
}

$enter = translate_inline('Enter');
page_header('Dwellings');
if (get_module_pref('dwelling_saver') > 0) {
    $session['user']['location'] = get_module_pref('location_saver');
    set_module_pref('dwelling_saver', 0);
} else {
    set_module_pref('location_saver', $session['user']['location'], 'dwellings');
}
if ($cityid == '') {
    require_once('modules/cityprefs/lib.php');
    $cityid = get_cityprefs_cityid('location', $session['user']['location']);
}

if ($cityid === false) {
    // unknown city
    $cityid = 999;
}

output('You leave the village in search of a place to dwell.');
addnav('The Hamlet Registry', 'runmodule.php?module=dwellings&op=list&ref=hamlet');
addnav('Dwellings for Sale', 'runmodule.php?module=dwellings&op=forsale');

$sql = "SELECT COUNT(dwid) AS count FROM " . db_prefix('dwellings') . " WHERE location='" . $session['user']['location'] . "'";
$result = db_query($sql);
$row = db_fetch_assoc($result);
$allsumloc = $row['count'];
db_free_result($result);

$sql = 'SELECT * FROM ' . db_prefix('dwellings') . " WHERE location='" . $session['user']['location'] . "' AND ownerid=" . $session['user']['acctid'] . ' ORDER BY type DESC';
$result = db_query($sql);
$sumownedloc = db_num_rows($result);
$allowbuy = 1;
if (get_module_objpref('city', $cityid, 'allcitylimit') != 0
    && ($allsumloc >= get_module_objpref('city', $cityid, 'allcitylimit'))
) {
    $allowbuy = 0;
    output('`n`n`^If you\'re thinking about setting up a dwelling here, good luck finding available property.`0`n`n');
} elseif (get_module_objpref('city', $cityid, 'ownercitylimit') != 0
    && ($sumownedloc >= get_module_objpref('city', $cityid, 'ownercitylimit'))
) {
    $allowbuy = 0;
    output('`n`n`^Due to city restrictions on the number of dwellings you already own here, you cannot build another dwelling here.`0`n`n');
}
modulehook('dwellings', ['allowbuy' => $allowbuy, 'cityid' => $cityid]);

dwellings_require_datatable_assets();

output('`n`n`cYou own the following completed dwellings here:`c`n');
$tname = translate_inline('Name');
$towner = translate_inline('Owner');
$ttype = translate_inline('Type');
$tdesc = translate_inline('Description');

// Completed local dwellings table.
dwellings_render_datatable_open('dwellings-owned-local-table', [$tname, $tdesc, $ttype, '&nbsp;']);

if (!db_num_rows($result)) {
    // Keep tbody empty so DataTables can render a valid empty-table state.
} else {
    $outed = 0;
    while ($row = db_fetch_assoc($result)) {
        $rtype = $row['type'];
        $rdwid = $row['dwid'];
        if ($row['name'] == '') {
            $name = translate_inline('Unnamed');
        } else {
            $name = $row['name'];
        }
        $cname = translate_inline(get_module_setting('dwname', $rtype));
        if ($row['status'] == 2) {
            addnav('Make a Payment...');
            addnav(
                ['On your %s', $cname],
                "runmodule.php?module=dwellings&op=buy&type=$rtype&subop=payment&dwid=$rdwid"
            );
        } elseif ($row['status'] == 3) {
            addnav('Construction on...');
            addnav(
                ['Your %s', $cname],
                "runmodule.php?module=dwellings&op=build&type=$rtype&dwid=$rdwid"
            );
        } elseif ($row['status'] == 1) {
            $outed++;
            rawoutput('<tr><td>');
            rawoutput(appoencode($name));
            rawoutput('</td><td>');
            rawoutput(appoencode($row['windowpeer']));
            rawoutput('</td><td>');
            rawoutput(appoencode($cname));
            rawoutput("</td><td class='is-actions' nowrap>[<a href='runmodule.php?module=dwellings&op=enter&dwid=$rdwid'>");
            output_notl($enter);
            rawoutput('</a>]</td></tr>');
            addnav('', "runmodule.php?module=dwellings&op=enter&dwid=$rdwid");
        }
        if ($row['status'] != 1 && $session['user']['level'] >= get_module_setting('levelsell')) {
            addnav('Demolish...');
            addnav(
                ['Your %s', $cname],
                "runmodule.php?module=dwellings&op=demo&dwid=$rdwid"
            );
        }

        modulehook('dwellings-owned', ['type' => $rtype, 'dwid' => $rdwid, 'status' => $row['status']]);
    }
    if ($outed == 0) {
        // Keep tbody empty so DataTables can render a valid empty-table state.
    }
}

dwellings_render_datatable_close('dwellings-owned-local-table', [
    'order' => [[0, 'asc']],
    'columnDefs' => [
        ['targets' => [3], 'orderable' => false, 'searchable' => false],
    ],
]);

output('`n`n`cYou have keys to the following dwellings here:`c`n');
$dwellings = db_prefix('dwellings');
$dwellingkeys = db_prefix('dwellingkeys');
$sql = "SELECT $dwellings.name AS name,
        $dwellings.dwid AS dwid,
        $dwellings.ownerid AS ownerid,
        $dwellings.type AS type,
        $dwellings.status AS status,
        $dwellings.windowpeer AS windowpeer,
        $dwellingkeys.keyowner
        FROM $dwellingkeys
        INNER JOIN $dwellings ON $dwellings.dwid = $dwellingkeys.dwid
        WHERE $dwellingkeys.dwidowner != " . $session['user']['acctid'] . "
        AND $dwellingkeys.keyowner = " . $session['user']['acctid'] . "
        AND $dwellings.status = 1
        AND $dwellings.location = '" . $session['user']['location'] . "'
        ORDER BY $dwellingkeys.keyid DESC";
$result = db_query($sql);

dwellings_render_datatable_open('dwellings-keyed-local-table', [$tname, $towner, $tdesc, $ttype, '&nbsp;']);
if (!db_num_rows($result)) {
    // Keep tbody empty so DataTables can render a valid empty-table state.
} else {
    while ($row = db_fetch_assoc($result)) {
        $sql2 = 'SELECT name FROM ' . db_prefix('accounts') . ' WHERE acctid=' . $row['ownerid'];
        $result2 = db_query($sql2);
        $row2 = db_fetch_assoc($result2);
        $owner = $row2['name'];
        $dwid = $row['dwid'];
        $cname = translate_inline(get_module_setting('dwname', $row['type']));

        rawoutput('<tr><td>');
        if ($row['name'] == '') {
            $name = translate_inline('Unnamed');
        } else {
            $name = $row['name'];
        }
        output_notl($name);
        rawoutput('</td><td>');
        output_notl($owner);
        rawoutput('</td><td>');
        rawoutput(appoencode($row['windowpeer']));
        rawoutput('</td><td>');
        output_notl($cname);
        rawoutput("</td><td class='is-actions' nowrap>[<a href='runmodule.php?module=dwellings&op=enter&dwid=$dwid'>");
        output_notl($enter);
        rawoutput('</a>]</td></tr>');
        addnav('', "runmodule.php?module=dwellings&op=enter&dwid=$dwid");
    }
}
dwellings_render_datatable_close('dwellings-keyed-local-table', [
    'order' => [[0, 'asc']],
    'columnDefs' => [
        ['targets' => [4], 'orderable' => false, 'searchable' => false],
    ],
]);

$noEnter = translate_inline('You need to travel');
output('`n`n`cIn other villages, you have access to the following dwellings:`c`n');
$sql = "SELECT $dwellings.name AS name,
        $dwellings.dwid AS dwid,
        $dwellings.ownerid AS ownerid,
        $dwellings.type AS type,
        $dwellings.status AS status,
        $dwellings.windowpeer AS windowpeer,
        $dwellings.location AS location,
        $dwellingkeys.keyowner
        FROM $dwellingkeys
        INNER JOIN $dwellings ON $dwellings.dwid = $dwellingkeys.dwid
        WHERE $dwellingkeys.dwidowner != " . $session['user']['acctid'] . "
        AND $dwellingkeys.keyowner = " . $session['user']['acctid'] . "
        AND $dwellings.status = 1
        AND $dwellings.location != '" . $session['user']['location'] . "'
        ORDER BY $dwellingkeys.keyid DESC";
$result = db_query($sql);

$locationLabel = translate_inline('Location');
dwellings_render_datatable_open('dwellings-keyed-remote-table', [$tname, $towner, $locationLabel, $tdesc, $ttype, '&nbsp;']);
if (!db_num_rows($result)) {
    // Keep tbody empty so DataTables can render a valid empty-table state.
} else {
    while ($row = db_fetch_assoc($result)) {
        $sql2 = 'SELECT name FROM ' . db_prefix('accounts') . ' WHERE acctid=' . $row['ownerid'];
        $result2 = db_query($sql2);
        $row2 = db_fetch_assoc($result2);
        $owner = $row2['name'];
        $typeName = translate_inline(get_module_setting('dwname', $row['type']));

        dwellings_render_remote_access_row(
            $row,
            $owner,
            $typeName,
            $row['location'],
            $noEnter,
            ''
        );
    }
}
dwellings_render_datatable_close('dwellings-keyed-remote-table', [
    'order' => [[2, 'asc']],
    'columnDefs' => [
        ['targets' => [5], 'orderable' => false, 'searchable' => false],
    ],
]);

output('`n`n`cIn other villages, you own the following dwellings:`c`n');
$sql = "SELECT $dwellings.name AS name,
        $dwellings.dwid AS dwid,
        $dwellings.ownerid AS ownerid,
        $dwellings.type AS type,
        $dwellings.status AS status,
        $dwellings.windowpeer AS windowpeer,
        $dwellings.location AS location
        FROM $dwellings
        WHERE $dwellings.ownerid = " . $session['user']['acctid'] . "
        AND $dwellings.status = 1
        AND $dwellings.location != '" . $session['user']['location'] . "'
        ORDER BY $dwellings.location DESC";
$result = db_query($sql);

dwellings_render_datatable_open('dwellings-owned-remote-table', [$tname, $towner, $locationLabel, $tdesc, $ttype, '&nbsp;']);
if (!db_num_rows($result)) {
    // Keep tbody empty so DataTables can render a valid empty-table state.
} else {
    while ($row = db_fetch_assoc($result)) {
        $sql2 = 'SELECT name FROM ' . db_prefix('accounts') . ' WHERE acctid=' . $row['ownerid'];
        $result2 = db_query($sql2);
        $row2 = db_fetch_assoc($result2);
        $owner = $row2['name'];
        $typeName = translate_inline(get_module_setting('dwname', $row['type']));

        dwellings_render_remote_access_row(
            $row,
            $owner,
            $typeName,
            $row['location'],
            $noEnter,
            ''
        );
    }
}
dwellings_render_datatable_close('dwellings-owned-remote-table', [
    'order' => [[2, 'asc']],
    'columnDefs' => [
        ['targets' => [5], 'orderable' => false, 'searchable' => false],
    ],
]);
?>
