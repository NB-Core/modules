<?php

/**
 * The inn's fireplace: a quiet corner the whole year, decorated in Advent
 * with a warm drink a day, hung with stockings in the last week, and on
 * Christmas morning every player finds one gift in their stocking.
 */

use Shinobi\Modules\Stocking\Fireplace;
use Shinobi\Modules\Stocking\Texts;

require_once __DIR__ . '/stocking/src/Fireplace.php';

function stocking_getmoduleinfo(): array
{
    return [
        'name' => 'Christmas Stocking',
        'author' => 'Talisman and Robert`0, rewritten by `2Oliver Brendel',
        'version' => '2.1',
        'category' => 'Holidays|Inn',
        'download' => 'core_module',
        'settings' => [
            'Christmas Stocking Settings,title',
            'date' => 'Date of holiday (mm-dd) [12-25 for Christmas],|12-25',
            'prizes' => 'Number of Stocking Presents taken,viewonly|0',
        ],
        'prefs' => [
            'Christmas Stocking User Prefs, title',
            'gotprez' => 'Year the player last got their stocking gift,int|0',
            'gotbuff' => 'Has the player had a drink by the fire today,bool|0',
        ],
    ];
}

function stocking_install(): bool
{
    module_addhook('inn-desc');
    module_addhook('inn');
    module_addhook('moderate');
    module_addhook('newday');
    return true;
}

function stocking_uninstall(): bool
{
    return true;
}

function stocking_dohook(string $hookname, array $args): array
{
    $days = Fireplace::daysToChristmas();
    $season = $days < 30 && $days >= -Fireplace::PICKUP_DAYS;
    switch ($hookname) {
        case 'newday':
            set_module_pref('gotbuff', 0);
            break;
        case 'moderate':
            $args['fireplace'] = translate_inline('Inn Fireplace');
            break;
        case 'inn-desc':
            output($season ? Texts::INN_DESC_SEASON : Texts::INN_DESC);
            break;
        case 'inn':
            addnav($days < 10 && $days >= -Fireplace::PICKUP_DAYS ? Texts::NAV_HEADER_SEASON : Texts::NAV_HEADER);
            addnav(Texts::NAV_WALK, 'runmodule.php?module=stocking');
            break;
    }
    return $args;
}

function stocking_run(): void
{
    (new Fireplace())->run();
}
