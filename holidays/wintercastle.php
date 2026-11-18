<?php

/**
 * The Winter Castle: the Frost Palace of the Land of Snow opens an arch of
 * ice beside the village gates every winter.
 *
 * Inside: Lady Yukiko's great hall with an Advent calendar of twenty-four
 * doors, an Ice Garden for sculptors, a Banquet Hall, a Lantern Tower for
 * wishes, a Hot Spring, and the Frost Cellar, where a snow woman asks
 * riddles. The castle is open between the start and end settings; staff can
 * enter outside of them to test.
 */

use Shinobi\Modules\WinterCastle\Castle;

require_once __DIR__ . '/wintercastle/src/Castle.php';

function wintercastle_getmoduleinfo(): array
{
    return [
        'name' => 'Winter Castle (Frost Palace of the Land of Snow)',
        'version' => '2.0',
        'author' => '`2Oliver Brendel`0, idea based on work by KainStrider',
        'category' => 'Holidays|Christmas',
        'download' => '',
        'settings' => [
            'Winter Castle Settings,title',
            'start' => 'Activation start date (mm-dd)|12-1',
            'end' => 'Activation end date (mm-dd)|12-31',
            'sculptcost' => 'Gold for a block of ice in the Ice Garden,int|100',
            'bestsculpture' => 'Best sculpture of the winter (JSON),viewonly|',
        ],
        'prefs' => [
            'Winter Castle,title',
            'advent' => 'Advent doors opened this year (JSON),viewonly|',
            'sculpttoday' => 'Carved a sculpture today,bool|0',
            'feasttoday' => 'Ate at the banquet today,bool|0',
            'lanterntoday' => 'Sent a wish lantern today,bool|0',
            'soaktoday' => 'Soaked in the hot spring today,bool|0',
            'riddlestoday' => "Snow woman's riddles answered today,int|0",
            'riddle' => 'Riddle waiting for an answer (number + 1),int|0',
        ],
    ];
}

function wintercastle_install(): bool
{
    module_addhook('village');
    module_addhook('village-desc');
    module_addhook('newday');
    return true;
}

function wintercastle_uninstall(): bool
{
    return true;
}

function wintercastle_dohook(string $hookname, array $args): array
{
    switch ($hookname) {
        case 'newday':
            foreach (['sculpttoday', 'feasttoday', 'lanterntoday', 'soaktoday', 'riddlestoday', 'riddle'] as $pref) {
                set_module_pref($pref, 0);
            }
            break;
        case 'village-desc':
            if (Castle::isOpen()) {
                output(Shinobi\Modules\WinterCastle\Texts::VILLAGE_DESC);
            }
            break;
        case 'village':
            if (Castle::isOpen()) {
                tlschema($args['schemas']['gatenav']);
                addnav($args['gatenav']);
                tlschema();
                addnav(Shinobi\Modules\WinterCastle\Texts::NAV_VILLAGE, 'runmodule.php?module=wintercastle');
            }
            break;
    }
    return $args;
}

function wintercastle_run(): void
{
    (new Castle())->run();
}
