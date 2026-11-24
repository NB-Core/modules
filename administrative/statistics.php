<?php

declare(strict_types=1);


function statistics_getmoduleinfo(): array
{
    $info = [
        'name'     => 'Usage Statistics',
        'version'  => '1.0',
        'author'   => 'Christian Rutsch, refactored by NB-Core',
        'category' => 'Administrative',
    ];

    return $info;
}

function statistics_install(): bool
{
    module_addhook('footer-weapons');
    module_addhook('footer-armor');
    module_addhook('footer-drinks');
    module_addhook('footer-kitchen');
    module_addhook('footer-wayofthehero');

    module_addhook('superuser');

    $statistics = [
        'type'        => ['name' => 'type', 'type' => 'varchar(100)'],
        'value'       => ['name' => 'value', 'type' => 'varchar(100)'],
        'date'        => ['name' => 'date', 'type' => 'varchar(10)'],
        'count'       => ['name' => 'count', 'type' => 'varchar(55)'],
        'key-PRIMARY' => [
            'name'    => 'PRIMARY',
            'type'    => 'primary key',
            'unique'  => '1',
            'columns' => 'type,value,date',
        ],
    ];

    require_once 'lib/tabledescriptor.php';

    synctable(db_prefix('statistics'), $statistics, true);

    return true;
}

function statistics_uninstall(): bool
{
    return true;
}

/**
 * Increment the usage counter for a statistics entry.
 */
function statistics_record(string $type, string $value, string $date): void
{
    \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement(
        "INSERT INTO " . \Lotgd\MySQL\Database::prefix('statistics') . " (type, value, date, count) VALUES (:type, :value, :date, '1') ON DUPLICATE KEY UPDATE count = count+1",
        ['type' => $type, 'value' => $value, 'date' => $date]
    );
}

function statistics_dohook(string $hookname, array $args): array
{
    if ($hookname === 'footer-wayofthehero') {
        if ($blubb = httpget('op3')) {
            if (is_numeric($blubb)) {
                httpset('id', $blubb, true);
                $op = httpget('op2');
                if ($op === 'forgeweapon') {
                    $hookname = 'footer-weapons';
                } else {
                    $hookname = 'footer-armor';
                }
            }
        }
    }

    switch ($hookname) {
        case 'footer-weapons':
            $id = httpget('id');
            if ($id !== false && ctype_digit($id)) {
                $id   = (int) $id % 15;
                if ($id === 0) {
                    $id = 15;
                }
                $date = date('Ymd');
                statistics_record('weapon', (string) $id, $date);
            }

            break;

        case 'footer-armor':
            $id = httpget('id');
            if ($id !== false && ctype_digit($id)) {
                $id   = (int) $id % 15;
                if ($id === 0) {
                    $id = 15;
                }
                $date = date('Ymd');
                statistics_record('armor', (string) $id, $date);
            }

            break;

        case 'footer-drinks':
            $act = httpget('act');
            if ($act === 'buy') {
                $id = httpget('id');
                if ($id !== false && ctype_digit($id)) {
                    $date = date('Ymd');
                    statistics_record('drinks', (string) $id, $date);
                }
            }

            break;

        case 'footer-kitchen':
            $op = httpget('op');
            if ($op !== false && $op !== '' && $op !== 'food') {
                $date = date('Ymd');
                // httpget() values carry legacy addslashes() escaping, which SafeEscape::escape()
                // left untouched; the stored value was the original text, so bind the unslashed value.
                statistics_record('kitchen', stripslashes((string) $op), $date);
            }

            break;

        case 'superuser':
            global $session;
            if ($session['user']['superuser'] & SU_MEGAUSER) {
                addnav('Admin Tools');
                addnav('Statistics', 'runmodule.php?module=statistics');
            }

            break;
    }

    return $args;
}

function statistics_run(): void
{
    global $session;
    // The Grotto link is only offered to megausers; enforce the same here.
    check_su_access(SU_MEGAUSER);
    page_header('Statistics');

    rawoutput('<style>
      .stats-table { float: left; margin: 0 1em 1em 0; }
      .stats-clear { clear: both; }
    </style>');

    // Navigation
    addnav('Navigation');
    require_once 'lib/superusernav.php';
    superusernav();
    addnav("Actions");

    $yearParam  = httpget('year');
    $monthParam = httpget('month');
    $dayParam   = httpget('day');
    $typeParam  = httpget('type');
    $valueParam = httpget('value');

    $year  = ($yearParam !== null && ctype_digit($yearParam) && strlen($yearParam) === 4) ? (int) $yearParam : (int) date('Y');
    $month = ($monthParam !== null && ctype_digit($monthParam) && strlen($monthParam) === 2) ? (int) $monthParam : null;
    $day   = ($dayParam !== null && ctype_digit($dayParam) && strlen($dayParam) === 2) ? (int) $dayParam : null;

    // httpget() values carry legacy addslashes() escaping (SafeEscape::escape() left them as is, so the
    // query compared the original text); bind the unslashed values.
    $typeKey  = is_string($typeParam) && $typeParam !== '' ? stripslashes($typeParam) : null;
    $valueKey = is_string($valueParam) && $valueParam !== '' ? stripslashes($valueParam) : null;
    $conn     = \Lotgd\MySQL\Database::getDoctrineConnection();
    $statsTable = \Lotgd\MySQL\Database::prefix('statistics');

    $safeEncode = static function ($value, ?int $length = null): string {
        if ($value === null || $value === false) {
            return '';
        }

        $str = (string) $value;
        if ($str === '') {
            return '';
        }

        if ($length !== null) {
            if (!ctype_digit($str)) {
                return '';
            }
            $str = str_pad($str, $length, '0', STR_PAD_LEFT);
        }

        return rawurlencode($str);
    };

    $years = [];
    $sql    = "SELECT DISTINCT SUBSTRING(date,1,4) AS year FROM {$statsTable} ORDER BY year DESC";
    foreach ($conn->fetchAllAssociative($sql) as $row) {
        $years[] = (int) $row['year'];
    }
    $selectedYear = in_array($year, $years, true) ? $year : ($years[0] ?? null);
    $yearKey  = $selectedYear !== null ? sprintf('%04d', $selectedYear) : null;

    addnav('Choose a year...');
    foreach ($years as $yearOption) {
        $link     = 'runmodule.php?module=statistics';
        $yearPart = $safeEncode($yearOption, 4);
        if ($yearPart !== '') {
            $link .= '&year=' . $yearPart;
        }
        addnav([
            '%s',
            sprintf('%04d', $yearOption),
        ], $link);
    }

    $months = [];
    if ($selectedYear !== null) {
        $sql    = "SELECT DISTINCT SUBSTRING(date,5,2) AS month FROM {$statsTable} WHERE SUBSTRING(date,1,4) = :year ORDER BY month DESC";
        foreach ($conn->fetchAllAssociative($sql, ['year' => $yearKey]) as $row) {
            $months[] = (int) $row['month'];
        }
        $selectedMonth = ($month !== null && in_array($month, $months, true)) ? $month : null;
        $monthKey  = $selectedMonth !== null ? sprintf('%02d', $selectedMonth) : null;

        addnav('Choose a month...');
        foreach ($months as $monthOption) {
            $link      = 'runmodule.php?module=statistics';
            $yearPart  = $safeEncode($selectedYear, 4);
            $monthPart = $safeEncode($monthOption, 2);
            if ($yearPart !== '') {
                $link .= '&year=' . $yearPart;
            }
            if ($monthPart !== '') {
                $link .= '&month=' . $monthPart;
            }
            $typePart  = $safeEncode(is_string($typeParam) ? $typeParam : '');
            $valuePart = $safeEncode(is_string($valueParam) ? $valueParam : '');
            if ($typePart !== '' && $valuePart !== '') {
                $link .= '&type=' . $typePart . '&value=' . $valuePart;
            }
            addnav([
                '%s',
                sprintf('%02d', $monthOption),
            ], $link);
        }
    } else {
        $selectedMonth = null;
        $monthKey  = null;
    }

    $days = [];
    if ($selectedYear !== null && $selectedMonth !== null) {
        $sql    = "SELECT DISTINCT SUBSTRING(date,7,2) AS day FROM {$statsTable} WHERE SUBSTRING(date,1,4) = :year AND SUBSTRING(date,5,2) = :month ORDER BY day DESC";
        foreach ($conn->fetchAllAssociative($sql, ['year' => $yearKey, 'month' => $monthKey]) as $row) {
            $days[] = (int) $row['day'];
        }
        $selectedDay = ($day !== null && in_array($day, $days, true)) ? $day : null;
        $dayKey  = $selectedDay !== null ? sprintf('%02d', $selectedDay) : null;

        addnav('Choose a day...');
        foreach ($days as $dayOption) {
            $link      = 'runmodule.php?module=statistics';
            $yearPart  = $safeEncode($selectedYear, 4);
            $monthPart = $safeEncode($selectedMonth, 2);
            $dayPart   = $safeEncode($dayOption, 2);
            if ($yearPart !== '') {
                $link .= '&year=' . $yearPart;
            }
            if ($monthPart !== '') {
                $link .= '&month=' . $monthPart;
            }
            if ($dayPart !== '') {
                $link .= '&day=' . $dayPart;
            }
            $typePart  = $safeEncode(is_string($typeParam) ? $typeParam : '');
            $valuePart = $safeEncode(is_string($valueParam) ? $valueParam : '');
            if ($typePart !== '' && $valuePart !== '') {
                $link .= '&type=' . $typePart . '&value=' . $valuePart;
            }
            addnav([
                '%s',
                sprintf('%02d', $dayOption),
            ], $link);
        }
    } else {
        $selectedDay = null;
        $dayKey  = null;
    }

    // Content
    if ($selectedYear !== null && $selectedMonth !== null && $selectedDay !== null) {
        $timestamp = mktime(0, 0, 0, $selectedMonth, $selectedDay, $selectedYear);
        $prevTs    = $timestamp - 86400;
        $nextTs    = $timestamp + 86400;

        addnav('Actions');
        addnav(
            'Previous Day',
            sprintf(
                'runmodule.php?module=statistics&year=%04d&month=%02d&day=%02d',
                (int) date('Y', $prevTs),
                (int) date('m', $prevTs),
                (int) date('d', $prevTs)
            )
        );
        addnav(
            'Next Day',
            sprintf(
                'runmodule.php?module=statistics&year=%04d&month=%02d&day=%02d',
                (int) date('Y', $nextTs),
                (int) date('m', $nextTs),
                (int) date('d', $nextTs)
            )
        );

        $date        = sprintf('%04d%02d%02d', $selectedYear, $selectedMonth, $selectedDay);
        $sql         = "SELECT * FROM {$statsTable} WHERE date = :date";
        $params      = ['date' => $date];
        if ($typeKey !== null && $valueKey !== null) {
            $sql .= " AND type = :type AND value = :value";
            $params['type']  = $typeKey;
            $params['value'] = $valueKey;
        }
        $sql    .= ' ORDER BY type ASC, value+0 ASC';
        $dayRows = $conn->fetchAllAssociative($sql, $params);
        $currentType = null;
        $typeTotal   = 0;
        $i           = 0;
        rawoutput('<div class="stats-table"><table style="margin-bottom:1em;">');
        rawoutput("<tr class='trhead'><td>Type</td><td>Value</td><td>Count</td></tr>");
        foreach ($dayRows as $row) {
            if ($currentType !== null && $row['type'] !== $currentType) {
                rawoutput("<tr class='trhead'><td>");
                output('%s', $currentType);
                rawoutput("</td><td>Total</td><td>");
                output('%s', $typeTotal);
                rawoutput('</td></tr>');
                $typeTotal = 0;
            }
            $currentType = $row['type'];
            $typeTotal  += (int) $row['count'];

            rawoutput("<tr class='" . ($i % 2 ? 'trlight' : 'trdark') . "'><td>");
            output('%s', $row['type']);
            rawoutput('</td><td>');
            output('%s', $row['value']);
            rawoutput('</td><td>');
            output('%s', $row['count']);
            rawoutput('</td></tr>');
            ++$i;
        }
        if ($currentType !== null) {
            rawoutput("<tr class='trhead'><td>");
            output('%s', $currentType);
            rawoutput("</td><td>Total</td><td>");
            output('%s', $typeTotal);
            rawoutput('</td></tr>');
        }
        rawoutput('</table></div>');
    } elseif ($selectedYear !== null && $selectedMonth !== null) {
        if ($typeKey !== null && $valueKey !== null) {
            $sql = "SELECT SUBSTRING(date,7,2) AS day, SUM(CAST(count AS UNSIGNED)) AS total FROM {$statsTable} WHERE SUBSTRING(date,1,4) = :year AND SUBSTRING(date,5,2) = :month";
            $sql .= " AND type = :type AND value = :value";
            $sql .= ' GROUP BY day ORDER BY day';
            $monthRows = $conn->fetchAllAssociative($sql, ['year' => $yearKey, 'month' => $monthKey, 'type' => $typeKey, 'value' => $valueKey]);
            rawoutput('<div class="stats-table"><table style="margin-bottom:1em;">');
            rawoutput("<tr class='trhead'><td>Value</td><td>Count</td></tr>");
            $i          = 0;
            $monthTotal = 0;
            foreach ($monthRows as $row) {
                $link      = 'runmodule.php?module=statistics';
                $yearPart  = $safeEncode($selectedYear, 4);
                $monthPart = $safeEncode($selectedMonth, 2);
                $dayPart   = $safeEncode($row['day'], 2);
                if ($yearPart !== '') {
                    $link .= '&year=' . $yearPart;
                }
                if ($monthPart !== '') {
                    $link .= '&month=' . $monthPart;
                }
                if ($dayPart !== '') {
                    $link .= '&day=' . $dayPart;
                }
                $typePart  = $safeEncode(is_string($typeParam) ? $typeParam : '');
                $valuePart = $safeEncode(is_string($valueParam) ? $valueParam : '');
                if ($typePart !== '' && $valuePart !== '') {
                    $link .= '&type=' . $typePart . '&value=' . $valuePart;
                }
                addnav('', $link);
                rawoutput("<tr class='" . ($i % 2 ? 'trlight' : 'trdark') . "'><td><a href='$link'>");
                output('%s', $row['day']);
                rawoutput("</a></td><td>");
                output('%s', $row['total']);
                rawoutput('</td></tr>');
                $monthTotal += (int) $row['total'];
                ++$i;
            }
            rawoutput("<tr class='trhead'><td>Total</td><td>");
            output('%s', $monthTotal);
            rawoutput('</td></tr>');
            rawoutput('</table></div>');
        } else {
            $sql    = "SELECT type, value, SUM(CAST(count AS UNSIGNED)) AS total FROM {$statsTable} WHERE SUBSTRING(date,1,4) = :year AND SUBSTRING(date,5,2) = :month GROUP BY type,value ORDER BY type ASC, value+0 ASC";
            $monthTypeRows = $conn->fetchAllAssociative($sql, ['year' => $yearKey, 'month' => $monthKey]);
            rawoutput('<div class="stats-table"><table style="margin-bottom:1em;">');
            rawoutput("<tr class='trhead'><td>Type</td><td>Value</td><td>Count</td></tr>");
            $currentType = null;
            $typeTotal   = 0;
            $i           = 0;
            foreach ($monthTypeRows as $row) {
                if ($currentType !== null && $row['type'] !== $currentType) {
                    rawoutput("<tr class='trhead'><td>");
                    output('%s', $currentType);
                    rawoutput("</td><td>Total</td><td>");
                    output('%s', $typeTotal);
                    rawoutput('</td></tr>');
                    $typeTotal = 0;
                }
                $currentType = $row['type'];
                $typeTotal  += (int) $row['total'];

                $link      = 'runmodule.php?module=statistics';
                $yearPart  = $safeEncode($selectedYear, 4);
                $monthPart = $safeEncode($selectedMonth, 2);
                if ($yearPart !== '') {
                    $link .= '&year=' . $yearPart;
                }
                if ($monthPart !== '') {
                    $link .= '&month=' . $monthPart;
                }
                $typePart  = $safeEncode($row['type']);
                $valuePart = $safeEncode($row['value']);
                if ($typePart !== '' && $valuePart !== '') {
                    $link .= '&type=' . $typePart . '&value=' . $valuePart;
                }
                addnav('', $link);
                rawoutput("<tr class='" . ($i % 2 ? 'trlight' : 'trdark') . "'><td><a href='$link'>");
                output('%s', $row['type']);
                addnav('', $link);
                rawoutput('</a></td><td><a href="' . $link . '">');
                output('%s', $row['value']);
                rawoutput("</a></td><td>");
                output('%s', $row['total']);
                rawoutput('</td></tr>');
                ++$i;
            }
            if ($currentType !== null) {
                rawoutput("<tr class='trhead'><td>");
                output('%s', $currentType);
                rawoutput("</td><td>Total</td><td>");
                output('%s', $typeTotal);
                rawoutput('</td></tr>');
            }
            rawoutput('</table></div>');
        }
    } elseif ($selectedYear !== null) {
        if ($typeKey !== null && $valueKey !== null) {
            $sql    = "SELECT SUBSTRING(date,5,2) AS month, SUM(CAST(count AS UNSIGNED)) AS total FROM {$statsTable} WHERE SUBSTRING(date,1,4) = :year AND type = :type AND value = :value GROUP BY month ORDER BY month ASC";
            $yearMonthRows = $conn->fetchAllAssociative($sql, ['year' => $yearKey, 'type' => $typeKey, 'value' => $valueKey]);

            $months = [];
            foreach ($yearMonthRows as $row) {
                $month            = sprintf('%02d', (int) $row['month']);
                $months[$month] = (int) $row['total'];
            }

            $yearTotal = array_sum($months);

            rawoutput('<div class="stats-table"><table border="0" cellpadding="2" cellspacing="1" align="center" bgcolor="#999999" style="margin-bottom:1em;">');
            rawoutput("<tr class='trhead'><td>Type</td><td>Value</td>");
            for ($m = 1; $m <= 12; $m++) {
                $link      = 'runmodule.php?module=statistics';
                $yearPart  = $safeEncode($selectedYear, 4);
                $monthPart = $safeEncode($m, 2);
                if ($yearPart !== '') {
                    $link .= '&year=' . $yearPart;
                }
                if ($monthPart !== '') {
                    $link .= '&month=' . $monthPart;
                }
                $typePart  = $safeEncode(is_string($typeParam) ? $typeParam : '');
                $valuePart = $safeEncode(is_string($valueParam) ? $valueParam : '');
                if ($typePart !== '' && $valuePart !== '') {
                    $link .= '&type=' . $typePart . '&value=' . $valuePart;
                }
                addnav('', $link);
                rawoutput("<td><a href='$link'>" . sprintf('%02d', $m) . "</a></td>");
            }
            rawoutput("<td>Total</td></tr>");

            $class = 'trlight';
            rawoutput("<tr class='$class'><td>");
            output('%s', is_string($typeParam) ? $typeParam : '');
            rawoutput('</td><td>');
            output('%s', is_string($valueParam) ? $valueParam : '');
            rawoutput('</td>');
            for ($m = 1; $m <= 12; $m++) {
                $month = sprintf('%02d', $m);
                $total = $months[$month] ?? 0;
                rawoutput('<td>');
                output('%s', $total);
                rawoutput('</td>');
            }
            rawoutput('<td>');
            output('%s', $yearTotal);
            rawoutput('</td>');
            rawoutput('</tr>');
            rawoutput('</table></div>');
        } else {
            $sql    = "SELECT type, value, SUM(CAST(count AS UNSIGNED)) AS total FROM {$statsTable} WHERE SUBSTRING(date,1,4) = :year GROUP BY type, value ORDER BY type ASC, value+0 ASC";
            $yearTypeRows = $conn->fetchAllAssociative($sql, ['year' => $yearKey]);

            rawoutput('<div class="stats-table"><table style="margin-bottom:1em;">');
            rawoutput("<tr class='trhead'><td>Type</td><td>Value</td><td>Total</td></tr>");
            $currentType = null;
            $typeTotal   = 0;
            $i           = 0;
            foreach ($yearTypeRows as $row) {
                if ($currentType !== null && $row['type'] !== $currentType) {
                    rawoutput("<tr class='trhead'><td>");
                    output('%s', $currentType);
                    rawoutput("</td><td>Total</td><td>");
                    output('%s', $typeTotal);
                    rawoutput('</td></tr>');
                    $typeTotal = 0;
                }
                $currentType = $row['type'];
                $typeTotal  += (int) $row['total'];

                $link     = 'runmodule.php?module=statistics';
                $yearPart = $safeEncode($selectedYear, 4);
                if ($yearPart !== '') {
                    $link .= '&year=' . $yearPart;
                }
                $typePart  = $safeEncode($row['type']);
                $valuePart = $safeEncode($row['value']);
                if ($typePart !== '' && $valuePart !== '') {
                    $link .= '&type=' . $typePart . '&value=' . $valuePart;
                }
                rawoutput("<tr class='" . ($i % 2 ? 'trlight' : 'trdark') . "'><td>");
                output('%s', $row['type']);
                rawoutput('</td><td>');
                output('%s', $row['value']);
                addnav('', $link);
                rawoutput("</td><td><a href='$link'>");
                output('%s', $row['total']);
                rawoutput('</a></td></tr>');
                ++$i;
            }
            if ($currentType !== null) {
                rawoutput("<tr class='trhead'><td>");
                output('%s', $currentType);
                rawoutput("</td><td>Total</td><td>");
                output('%s', $typeTotal);
                rawoutput('</td></tr>');
            }
            rawoutput('</table></div>');
        }
    } else {
        $sql    = "SELECT * FROM {$statsTable} ORDER BY type ASC, value+0 ASC";
        foreach ($conn->fetchAllAssociative($sql) as $row) {
            $type  = $row['type'];
            $value = $row['value'];
            $stats[$type][$value]['count'] = ($stats[$type][$value]['count'] ?? 0) + (int) $row['count'];
            $stats[$type][$value]['qty']   = ($stats[$type][$value]['qty'] ?? 0) + 1;
        }
        $type = '';
        $i    = 0;
        rawoutput('<div class="stats-table"><table style="margin-bottom:1em;">');
        rawoutput("<tr class='trhead'><td>Value</td><td>Count</td></tr>");
        foreach ($stats as $newtype => $content) {
            if ($newtype !== $type) {
                rawoutput("<tr class='trhead'><td colspan='2'>");
                output('`^%s', $newtype);
                rawoutput('</td></tr>');
                $type = $newtype;
            }
            foreach ($content as $value => $array) {
                rawoutput("<tr class='" . ($i % 2 ? 'trlight' : 'trdark') . "'><td>");
                output('%s', $value);
                rawoutput('</td><td>');
                output('%s items were sold over %s days. (~%f per day)', $array['count'], $array['qty'], round($array['count'] / $array['qty'], 6));
                rawoutput('</td></tr>');
            }
            ++$i;
        }
        rawoutput('</table></div>');
    }

    rawoutput('<div class="stats-clear"></div>');
    page_footer();
}

?>
