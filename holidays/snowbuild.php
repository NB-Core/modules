<?php

/**
 * Snowman building contest on the village square, through the winter.
 *
 * Originally by Red Yates (as a pumpkin carving contest) and Shannon Brown.
 * Players build one of four kinds of snowman for a small fee; one in thirty
 * wins first prize, and the winner's snowman stands in the square.
 */

use Shinobi\Modules\Snowbuild\Contest;

require_once __DIR__ . '/snowbuild/src/Contest.php';

function snowbuild_getmoduleinfo(): array
{
    return [
        'name' => 'Snowman Building Contest',
        'version' => '2.0',
        'author' => '`$Red Yates`# & Shannon Brown`0, rewritten by `2Oliver Brendel',
        'download' => 'core_module',
        'category' => 'Holidays|Christmas',
        'settings' => [
            'Snowman Building Module Settings, title',
            'perday' => 'Tries at building per day, int|1',
            'cost' => 'How much to build a snowman, int|25',
            'snowbuildloc' => 'Village the snowman building is in, location|',
            'judge' => 'Name of the old man who judges the contest,text|`QGenzo',
            'start' => 'Open from (mm-dd; empty: always open)|12-1',
            'end' => 'Open until (mm-dd; empty: always open)|1-31',
            'winner' => 'Whose snowman is on display, int|0',
            'winnerstyle' => 'What kind of snowman is on display,viewonly|classic',
        ],
        'prefs' => [
            'Snowman Building User Prefs, title',
            'tries' => 'Times built today,int|0',
        ],
    ];
}

function snowbuild_install(): bool
{
    module_addhook('village');
    module_addhook('newday');
    module_addhook('changesetting');
    module_addhook('village-desc');
    return true;
}

function snowbuild_uninstall(): bool
{
    return true;
}

function snowbuild_dohook(string $hookname, array $args): array
{
    global $session;

    switch ($hookname) {
        case 'village':
            if ($session['user']['location'] == get_module_setting('snowbuildloc') && Contest::isOpen()) {
                tlschema($args['schemas']['marketnav']);
                addnav($args['marketnav']);
                tlschema();
                addnav('Snowman Building', 'runmodule.php?module=snowbuild');
            }
            break;
        case 'newday':
            set_module_pref('tries', 0);
            break;
        case 'changesetting':
            if ($args['setting'] == 'villagename' && $args['old'] == get_module_setting('snowbuildloc')) {
                set_module_setting('snowbuildloc', $args['new']);
            }
            break;
        case 'village-desc':
            if ($session['user']['location'] == get_module_setting('snowbuildloc') && Contest::isOpen()) {
                $line = Contest::winnerLine();
                if ($line !== '') {
                    output_notl('%s', $line);
                }
            }
            break;
    }
    return $args;
}

function snowbuild_run(): void
{
    (new Contest())->run();
}
