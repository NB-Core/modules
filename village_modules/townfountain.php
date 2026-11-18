<?php

/**
 * Town Fountain. Originally by Eth.
 *
 * A fountain in the village square: toss a coin and make a wish, chat with
 * the others, listen to the gossip, or have a splash. The logic and texts
 * live in townfountain/src/.
 */

use Shinobi\Modules\TownFountain\Fountain;
use Shinobi\Modules\TownFountain\Texts;

require_once __DIR__ . '/townfountain/src/Fountain.php';

function townfountain_getmoduleinfo(): array
{
    return [
        'name' => 'Town Fountain',
        'version' => '2.0',
        'author' => 'Eth`0, rewritten by `2Oliver Brendel',
        'category' => 'Village',
        'download' => 'core_module',
        'settings' => [
            'Fountain Settings,title',
            'fountainloc' => 'Where does the fountain appear?,location|' . getsetting('villagename', LOCATION_FIELDS),
            'Fountain Odor Settings,title',
            'You need to have the bathhouse/odor module installed and active for this to work.,note',
            'cleanup' => 'How many points removed from odor for jumping in fountain?,int|3',
        ],
        'prefs' => [
            'Fountain Preferences,title',
            'tossedcoin' => 'Tossed Coin Today?,bool|0',
            'splashedaround' => 'Played in Fountain Today?,bool|0',
        ],
    ];
}

function townfountain_install(): bool
{
    module_addhook('village-desc');
    module_addhook('newday');
    module_addhook('moderate');
    module_addhook('changesetting');
    return true;
}

function townfountain_uninstall(): bool
{
    return true;
}

function townfountain_dohook(string $hookname, array $args): array
{
    global $session;
    switch ($hookname) {
        case 'changesetting':
            // Follow the village when it is renamed.
            if ($args['setting'] == 'villagename' && $args['old'] == get_module_setting('fountainloc')) {
                set_module_setting('fountainloc', $args['new']);
            }
            break;
        case 'moderate':
            $args['townfountain'] = translate_inline(Texts::MODERATE);
            break;
        case 'newday':
            set_module_pref('tossedcoin', 0);
            set_module_pref('splashedaround', 0);
            break;
        case 'village-desc':
            if ($session['user']['location'] == get_module_setting('fountainloc')) {
                Fountain::villageDesc();
            }
            break;
    }
    return $args;
}

function townfountain_run(): void
{
    (new Fountain())->run();
}
