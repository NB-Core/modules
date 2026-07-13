<?php

// translation readied "buy it" found by Gucky2000

require_once('modules/dwellings/run/table_helpers.php');

if ($cityid == '') {
    require_once('modules/cityprefs/lib.php');
    $cityid = get_cityprefs_cityid('location', $session['user']['location']);
}

if (httpget('subop') == 'buy') {
    $gemcost = httpget('gemcost');
    $goldcost = httpget('goldcost');
    $nosale = 0;
    modulehook('dwellings-forsale-check', ['type' => $type, 'dwid' => $dwid]);
    if ($session['user']['gems'] < $gemcost) {
        $nosale++;
        output('You do not have enough gems to buy this dwelling.`n');
    }
    if ($session['user']['gold'] < $goldcost) {
        $nosale++;
        output('You do not have enough gold to buy this dwelling.`n');
    }
    if ($session['user']['dragonkills'] < get_module_setting('dkreq', $type)) {
        $nosale++;
        output('You are not experienced enough to buy this type of dwelling.`n');
    }
    require_once('modules/cityprefs/lib.php');
    $cityid = get_cityprefs_cityid('location', $session['user']['location']);
    $sql = 'SELECT COUNT(dwid) AS count FROM ' . db_prefix('dwellings') . ' WHERE ownerid=' . $session['user']['acctid'] . '';
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
    $globsum = $row['count'];
    $sql = 'SELECT COUNT(dwid) AS count FROM ' . db_prefix('dwellings') . " WHERE location='" . $session['user']['location'] . "' and ownerid=" . $session['user']['acctid'] . " and type='$type'";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
    $usertypesumloc = $row['count'];

    $sql = 'SELECT COUNT(dwid) AS count FROM ' . db_prefix('dwellings') . " WHERE ownerid=" . $session['user']['acctid'] . " and type='$type'";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
    $usertypesumglobal = $row['count'];

    if (get_module_objpref('city', $cityid, "userloclimit$type") != 0
        && get_module_objpref('city', $cityid, "userloclimit$type") <= $usertypesumloc
    ) {
        $nosale++;
        output('`nLocal Land owning guidelines prevent you from owning any more %s in this location.`n', translate_inline(get_module_setting("dwnameplural,$type")));
    } elseif (get_module_setting('globallimit', $type) != 0
        && get_module_setting('globallimit', $type) <= $usertypesumglobal
    ) {
        $nosale++;
        output('`nRealm permit guidelines prevent you from owning any more %s.`n', translate_inline(get_module_setting('dwnameplural', $type)));
    } elseif (get_module_setting('ownergloballimit') != 0
        && get_module_setting('ownergloballimit') <= $globsum
    ) {
        $nosale++;
        output('`nYou must be some kind of dwelling addict!  You have enough dwellings as it is.`n');
    }

    if ($nosale == 0) {
        output('Congratulations on buying this dwelling!');
        $session['user']['gold'] -= $goldcost;
        $session['user']['gems'] -= $gemcost;
        modulehook('dwellings-forsale-buy', ['type' => $type, 'dwid' => $dwid]);
        $sql = 'UPDATE ' . db_prefix('dwellings') . ' SET ownerid=' . $session['user']['acctid'] . ",status=1 WHERE dwid=$dwid";
        db_query($sql);
        debuglog(sanitize($session['user']['name']) . " bought dwelling no. $dwid for $goldcost gold and $gemcost gems");
        addnav('Enter your Dwelling', "runmodule.php?module=dwellings&op=enter&dwid=$dwid");
    }
} else {
    output('`#Here you can see all the dwellings in %s`# that have either been abandoned or repossessed and are now available at a special rate for you to buy.', $session['user']['location']);
    $loc = $session['user']['location'];
    debug($loc);
    // No SELECT * FROM ... especially description and windowpeer can be quite large.
    $sql = 'SELECT dwid, name, goldvalue, gemvalue, gold, gems, type FROM ' . db_prefix('dwellings') . " WHERE location='$loc' AND (status=4 OR status=5) ORDER BY type DESC";
    $result = db_query($sql);

    // Shared DataTables styling/behavior keeps for-sale list aligned with other dwellings list UIs.
    dwellings_require_datatable_assets();

    $nameLabel = translate_inline('Name');
    $opsLabel = translate_inline('Options');
    $typeLabel = translate_inline('Type');
    $costLabel = translate_inline('Cost');

    dwellings_render_datatable_open('dwellings-forsale-table', [$nameLabel, $typeLabel, $costLabel, $opsLabel]);

    if (!db_num_rows($result)) {
        $none = translate_inline('None');
        rawoutput("<tr><td align='center' colspan='4'><i>$none</i></td></tr>");
    } else {
        while ($row = db_fetch_assoc($result)) {
            $rtype = $row['type'];
            if ((get_module_setting('lvlbuy'))
                || get_module_setting('dkreq', $rtype) <= $session['user']['dragonkills']
            ) {
                if ($row['name'] == '') {
                    $dwellingName = translate_inline('Unnamed');
                } else {
                    $dwellingName = $row['name'];
                }
                $cname = get_module_setting('dwname', $rtype);
                $dwid = $row['dwid'];

                $goldcost = $row['goldvalue'];
                $gemcost = $row['gemvalue'];
                if (get_module_setting('addcof')) {
                    $goldcost += $row['gold'];
                    $gemcost += $row['gems'];
                }
                $goldcost = round($goldcost * (get_module_setting('abnperc') * 0.01));
                $gemcost = round($gemcost * (get_module_setting('abnperc') * 0.01));

                $buyit = translate_inline('Buy it');
                $buyUrl = "runmodule.php?module=dwellings&op=forsale&subop=buy&goldcost=$goldcost&gemcost=$gemcost&dwid=$dwid";

                // DataTables redraw callback in table_helpers.php owns striping parity.
                rawoutput('<tr><td>');
                output_notl($dwellingName);
                rawoutput('</td><td>');
                output_notl($cname);
                rawoutput("</td><td data-order='$goldcost-$gemcost'>");
                output('`^Gold: %s`n`%Gems: %s`0', $goldcost, $gemcost);
                modulehook('dwellings-forsale-cost', ['type' => $row['type'], 'dwid' => $row['dwid']]);
                rawoutput("</td><td class='is-actions'><a href='$buyUrl'>");
                output_notl($buyit);
                rawoutput('</a></td></tr>');
                addnav('', $buyUrl);
            }
        }
    }

    // Keep action column unsortable because it only contains an interaction link.
    dwellings_render_datatable_close('dwellings-forsale-table', [
        'order' => [[1, 'asc']],
        'columnDefs' => [
            ['targets' => [3], 'orderable' => false, 'searchable' => false],
        ],
    ]);
}
?>
