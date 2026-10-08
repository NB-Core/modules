<?php

declare(strict_types=1);

use Lotgd\MySQL\Database;
use Lotgd\Nav\SuperuserNav;

function showlastregistered_getmoduleinfo(): array
{
    return [
        'name'     => 'Show Last Regs for Grotto',
        'version'  => '1.1',
        'author'   => '`2Oliver Brendel',
        'category' => 'Administrative',
        'download' => '',
    ];
}

function showlastregistered_install(): bool
{
    module_addhook('superuser');

    return true;
}

function showlastregistered_uninstall(): bool
{
    return true;
}

function showlastregistered_dohook(string $hookname, array $args): array
{
    global $session;

    if ($hookname === 'superuser') {
        $canView =
            (($session['user']['superuser'] & SU_EDIT_COMMENTS) === SU_EDIT_COMMENTS)
            || (($session['user']['superuser'] & SU_EDIT_PETITIONS) === SU_EDIT_PETITIONS);

        if ($canView) {
            addnav('Mechanics');
            addnav('Show Last Registered Players', 'runmodule.php?module=showlastregistered');
        }
    }

    return $args;
}

function showlastregistered_run(): void
{
    global $session;

    $rangeParam = httpget('range');

    $ranges = [
        'all' => [
            'label'    => translate_inline('Latest 30 registrations'),
            'interval' => null,
        ],
        '24h' => [
            'label'    => translate_inline('Last 24 hours'),
            'interval' => new \DateInterval('P1D'),
        ],
        '3d' => [
            'label'    => translate_inline('Last 3 days'),
            'interval' => new \DateInterval('P3D'),
        ],
        '7d' => [
            'label'    => translate_inline('Last 7 days'),
            'interval' => new \DateInterval('P7D'),
        ],
        '30d' => [
            'label'    => translate_inline('Last 30 days'),
            'interval' => new \DateInterval('P30D'),
        ],
    ];

    $range = array_key_exists($rangeParam, $ranges) ? $rangeParam : 'all';

    page_header('Latest Registrations Overview');

    SuperuserNav::render();

    addnav('Actions');
    addnav('Refresh', 'runmodule.php?module=showlastregistered&range=' . rawurlencode($range));

    addnav('Time ranges');
    foreach ($ranges as $key => $definition) {
        addnav(
            $definition['label'],
            'runmodule.php?module=showlastregistered&range=' . rawurlencode($key)
        );
    }

    $startDate = null;
    $endDate   = new \DateTimeImmutable('now');

    if ($ranges[$range]['interval'] instanceof \DateInterval) {
        $startDate = $endDate->sub($ranges[$range]['interval']);
    }

    $limit    = 30;
    $conn     = Database::getDoctrineConnection();
    $accounts = Database::prefix('accounts');

    $builder = $conn->createQueryBuilder();
    $builder
        ->select('a.name', 'a.acctid', 'a.regdate', 'a.lastip', 'a.uniqueid')
        ->from($accounts, 'a')
        ->orderBy('a.regdate', 'DESC')
        ->setMaxResults($limit);

    if ($startDate !== null) {
        $builder
            ->where('a.regdate BETWEEN :start AND :end')
            ->setParameter('start', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('end', $endDate->format('Y-m-d H:i:s'));
    }

    $result = $builder->executeQuery();
    $rows   = $result->fetchAllAssociative();

    $countBuilder = $conn->createQueryBuilder();
    $countBuilder
        ->select('COUNT(*) AS registrations')
        ->from($accounts, 'a');

    if ($startDate !== null) {
        $countBuilder
            ->where('a.regdate BETWEEN :start AND :end')
            ->setParameter('start', $startDate->format('Y-m-d H:i:s'))
            ->setParameter('end', $endDate->format('Y-m-d H:i:s'));
    }

    $totalRegistrations = (int) $countBuilder->executeQuery()->fetchOne();

    $userHeader   = translate_inline('Username');
    $dateHeader   = translate_inline('Registerdate');
    $lastIpHeader = translate_inline('Last IP');
    $lastIdHeader = translate_inline('Last ID');
    $editHeader   = translate_inline('Edit');

    $rangeLabel = $ranges[$range]['label'];

    if ($startDate !== null) {
        output(
            'Showing the %s most recent registrations between `^%s`0 and `^%s`0. Total registrations in this window: `^%s`0.`n`n',
            $limit,
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s'),
            $totalRegistrations
        );
    } else {
        output(
            'Showing the %s most recent registrations overall. Total registered accounts: `^%s`0.`n`n',
            $limit,
            $totalRegistrations
        );
    }

    output('Selected range: `^%s`0.`n`n', $rangeLabel);

    if (empty($rows)) {
        output('`c`@No registrations found for the selected range.`0`c');
        page_footer();

        return;
    }

    rawoutput("<table border='0' cellpadding='2' cellspacing='1' bgcolor='#999999' align='center'>");
    rawoutput("<tr class='trhead' height='30px'><td><b>{$userHeader}</b></td><td><b>{$dateHeader}</b></td><td><b>{$lastIpHeader}</b></td><td><b>{$lastIdHeader}</b></td><td></td></tr>");

    foreach ($rows as $index => $row) {
        $class = $index % 2 === 0 ? 'trlight' : 'trdark';

        rawoutput("<tr height='30px' class='{$class}'>");
        rawoutput('<td>');
        output_notl($row['name']);
        rawoutput('</td><td>');
        output_notl($row['regdate']);
        rawoutput('</td><td>');
        output_notl($row['lastip']);
        rawoutput('</td><td>');
        output_notl($row['uniqueid']);
        rawoutput('</td><td>');

        if (($session['user']['superuser'] & SU_MEGAUSER) === SU_MEGAUSER) {
            $editLink = 'user.php?op=edit&userid=' . (int) $row['acctid'];
            rawoutput("<a href='{$editLink}'>{$editHeader}</a>");
            addnav('', $editLink);
        }

        rawoutput('</td></tr>');
    }

    rawoutput('</table>');

    page_footer();
}

