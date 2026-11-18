<?php

/**
 * Bijuu Hunters. By ShinobiIceSlayer.
 *
 * Hidan and Kakuzu of the Akatsuki roam the forest, hunting Jinchuuriki
 * and players with a bounty on their head. The logic and texts live in
 * bijuuhunters/src/.
 */

use Shinobi\Modules\BijuuHunters\Hunters;

require_once __DIR__ . '/bijuuhunters/src/Hunters.php';

function bijuuhunters_getmoduleinfo(): array
{
    return [
        'name' => 'Bijuu Hunters',
        'version' => '2.0',
        'author' => '`JShinobiIceSlayer`0, rewritten by `2Oliver Brendel',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'settings' => [
            'Bijuu Hunters Settings,title',
            'chance' => 'Forest event chance for everyone else,int|100',
            'targetchance' => 'Forest event chance for Jinchuuriki and wanted players,int|100',
            'minbounty' => 'The minimum bounty needed for the Hunters to attack,int|500',
            'bribefactor' => 'Kakuzu lets a wanted player go for this percent of the bounty (at least 100),int|150',
            'seallength' => 'How many days does the Bijuu get sealed for?,int|10',
            'erofavours' => 'How many favours you need with Ero-Sennin to let him remove the seal,int|10',
        ],
        'prefs' => [
            'Bijuu Hunters User Preferences,title',
            'sealeddays' => 'How long does the user have left for his bijuu sealing,int|0',
            'target' => 'Why they fight the user in the current fight,text|',
            'triedhide' => 'Tried to hide the beast in the current encounter,bool|0',
        ],
        'prefs-mounts' => [
            'Bijuu Hunters Mount Preferences,title',
            'bijuu' => 'Is this mount a Bijuu?,bool|0',
        ],
    ];
}

/** Whether the player's mount is a Bijuu (kept for older callers). */
function bijuu_check(): bool
{
    return Hunters::isJinchuuriki();
}

/** Whether the player has at least the minimum bounty on them (kept for older callers). */
function bounty_check($id): bool
{
    return Hunters::hasBounty((int) $id);
}

function bijuuhunters_install(): bool
{
    module_addeventhook('forest', 'require_once("modules/bijuuhunters.php");return Shinobi\\Modules\\BijuuHunters\\Hunters::chance();');
    module_addhook('mountfeatures');
    module_addhook('soldmount');
    module_addhook('footer-stables');
    module_addhook('newday');
    module_addhook('erosennin_favours');
    return true;
}

function bijuuhunters_uninstall(): bool
{
    return true;
}

function bijuuhunters_dohook(string $hookname, array $args): array
{
    switch ($hookname) {
        case 'mountfeatures':
            $args['features']['Bijuu'] = get_module_objpref('mounts', $args['id'], 'bijuu');
            break;
        case 'soldmount':
            set_module_pref('sealeddays', 0);
            break;
        case 'footer-stables':
            // A sealed beast cannot be fed back to strength.
            if (Hunters::sealedDays() > 0 && Hunters::isJinchuuriki()) {
                blocknav('stables.php?op=feed', true);
            }
            break;
        case 'newday':
            Hunters::newday();
            break;
        case 'erosennin_favours':
            Hunters::eroNav();
            break;
    }
    return $args;
}

function bijuuhunters_runevent($type, $link): void
{
    global $session;
    $hunters = new Hunters((string) $link);
    if ($hunters->handle((string) httpget('op'))) {
        return;
    }
    // The fight: battle.php works on the variables of this scope.
    $battle = true;
    $victory = false;
    $defeat = false;
    include 'battle.php';
    $hunters->afterBattle((bool) $victory, (bool) $defeat);
}

function bijuuhunters_run(): void
{
    Hunters::run();
}
