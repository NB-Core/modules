<?php

/**
 * The Strategy Hut. Originally by Atrus, cleaned up by Kendaer.
 *
 * A strategist gives young shinobi advice for a few coins per level, and
 * plays one game of shogi a day with them. The logic and texts live in
 * strategyhut/src/.
 */

use Shinobi\Modules\StrategyHut\Hut;
use Shinobi\Modules\StrategyHut\Texts;

require_once __DIR__ . '/strategyhut/src/Hut.php';

function strategyhut_getmoduleinfo(): array
{
    return [
        'name' => 'The Strategy Hut',
        'version' => '2.0',
        'author' => 'Atrus`0, rewritten by `2Oliver Brendel',
        'category' => 'Village',
        'download' => 'core_module',
        'settings' => [
            'Strategy Hut Settings,title',
            'hutloc' => 'Where does it appear,location|' . getsetting('villagename', LOCATION_FIELDS),
            'mentor' => 'Name of the strategist,text|`2Shikamaru Nara',
            'cost' => 'Gold per player level for one piece of advice,int|5',
            'maxdk' => 'Visible up to this many dragon kills (staff always),int|0',
        ],
        'prefs' => [
            'Strategy Hut Preferences,title',
            'shogitoday' => 'Played shogi today?,bool|0',
        ],
    ];
}

function strategyhut_install(): bool
{
    module_addhook('changesetting');
    module_addhook('village');
    module_addhook('newday');
    return true;
}

function strategyhut_uninstall(): bool
{
    return true;
}

function strategyhut_dohook(string $hookname, array $args): array
{
    global $session;
    switch ($hookname) {
        case 'village':
            if ($session['user']['location'] == get_module_setting('hutloc') && Hut::mayVisit()) {
                tlschema($args['schemas']['marketnav']);
                addnav($args['marketnav']);
                tlschema();
                addnav(Texts::NAV, 'runmodule.php?module=strategyhut');
            }
            break;
        case 'changesetting':
            // Follow the village when it is renamed.
            if ($args['setting'] == 'villagename' && $args['old'] == get_module_setting('hutloc')) {
                set_module_setting('hutloc', $args['new']);
            }
            break;
        case 'newday':
            set_module_pref('shogitoday', 0);
            break;
    }
    return $args;
}

function strategyhut_run(): void
{
    (new Hut())->run();
}
