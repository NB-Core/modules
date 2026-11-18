<?php

/**
 * The Snowy Banks: a snowball war in the gardens over the winter.
 *
 * Players throw at others who are online (a quick throw or a leaping
 * volley), hits are counted per winter for a champions board, targets get a
 * mail, and passers-by can be ambushed just for fun. Open between the start
 * and end settings plus a few days of leniency, through datemanager.
 */

use Shinobi\Modules\Snowball\Banks;

require_once __DIR__ . '/snowball/src/Banks.php';

function snowball_getmoduleinfo(): array
{
    return [
        'name' => 'Snowball in the gardens (timelocked)',
        'version' => '2.0',
        'author' => '`LShinobiIceSlayer`0, rewritten by `2Oliver Brendel',
        'category' => 'Holidays|Christmas',
        'download' => '',
        'settings' => [
            'Snowball - Settings,title',
            'snowballlimit' => 'How many snowballs a user can throw a day,int|3',
            'start' => 'Activation start date (mm-dd)|12-1',
            'end' => 'Activation end date (mm-dd)|12-31',
            'leniency' => 'How many days after the end date the event is active,int|5',
        ],
        'prefs' => [
            'Snowball - User Preferences,title',
            'throwstoday' => 'Snowballs thrown today,int|0',
        ],
        'requires' => [
            'datemanager' => '1.0|By Oliver Brendel',
        ],
    ];
}

function snowball_install(): bool
{
    module_addhook('footer-gardens');
    module_addhook('village-desc');
    module_addhook('gardens-desc');
    module_addhook('newday');
    return true;
}

function snowball_uninstall(): bool
{
    return true;
}

function snowball_dohook(string $hookname, array $args): array
{
    switch ($hookname) {
        case 'footer-gardens':
            if (Banks::dateState() > 0) {
                addnav('Winter');
                addnav('Snowy Banks', 'runmodule.php?module=snowball');
            }
            break;
        case 'village-desc':
        case 'gardens-desc':
            $state = Banks::dateState();
            if ($state > 0) {
                $start = (string) get_module_setting('start');
                $end = (string) get_module_setting('end');
                $leniency = (int) get_module_setting('leniency');
                $remaining = datemanager_get_time_remaining($start, $end, $leniency);
                if ($remaining !== null) {
                    $countdown = datemanager_format_countdown($remaining);
                    if ($state === 2) {
                        output("`n`&The snowball fight is winding down in the gardens.`7 There's `^%s`7 of frosty fun left before the thaw.`n", $countdown);
                    } else {
                        output("`n`&Snowball fights rage in the gardens!`7 There's still `^%s`7 of snowy fun remaining.`n", $countdown);
                    }
                }
            }
            break;
        case 'newday':
            set_module_pref('throwstoday', 0);
            break;
    }
    return $args;
}

function snowball_run(): void
{
    (new Banks())->run();
}
