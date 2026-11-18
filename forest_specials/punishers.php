<?php

/**
 * The Punishers: forest patrols. By Oliver Brendel.
 *
 * Around most villages the ANBU patrol the woods and stop evil shinobi;
 * around the villages in the "evilvillages" setting, the Hunter-nin of the
 * Bloody Mist hunt the good ones instead. Version 2.0 merges the former
 * evil_punishers module into this one. The logic and texts live in
 * punishers/src/.
 */

use Shinobi\Modules\Punishers\Patrol;

require_once __DIR__ . '/punishers/src/Patrol.php';

function punishers_getmoduleinfo(): array
{
    return [
        'name' => 'The Punishers - Swift and Sharp',
        'author' => '`2Oliver Brendel',
        'version' => '2.0',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'settings' => [
            'The Punishers - Settings,title',
            'Patrols stop players in the forest according to their alignment,note',
            'chance' => 'Forest event chance,int|50',
            'level' => 'Level at which fleeing succeeds 70% of the time (rises with level; 10-85%),range,2,15,1|10',
            'overwhelm' => '1 in x chance the leader comes along (much stronger; no bribes or talking),range,2,25,1|6',
            'experienceloss' => 'Percent of experience lost on death,range,0,100,1|5',
            'goldloss' => 'Percent of carried gold lost on death or when surrendering,range,0,100,1|50',
            'bribe' => 'Bribe: gold per player level,int|100',
            'ANBU (hunt evil shinobi),title',
            'name' => 'Name (coloured) of the organisation,text|`4A`$N`2B`4U',
            'chiefname' => 'Name of their leader,text|`QK`^a`tk`Ea`)s`@h`%i',
            'Hunter-nin (hunt good shinobi),title',
            'evilvillages' => 'Villages where the Hunter-nin patrol instead (comma separated),text|Kirigakure',
            'evilname' => 'Name (coloured) of the organisation,text|`1Hunter`!-nin',
            'evilchief' => 'Name of their leader,text|`QM`^i`ts`Et`)r`2e`@ss `QK`^a`ty`El`)a',
            'evilchief_migrated' => 'Leader name taken over from evil_punishers,viewonly|0',
        ],
        'prefs' => [
            'The Punishers - Preferences,title',
            'chief' => 'Leader present in the current encounter,bool|0',
            'faction' => 'Faction in the current fight,text|',
        ],
        'requires' => [
            'alignment' => '1.9|WebPixie<br> `#Lonny Luberts<br>`^and Chris Vorndran',
        ],
    ];
}

function punishers_install(): bool
{
    module_addeventhook('forest', 'require_once("modules/punishers.php");return Shinobi\\Modules\\Punishers\\Patrol::chance();');
    // 2.0 took over evil_punishers: keep a leader name that was customised there.
    $oldChief = (string) get_module_setting('chiefname', 'evil_punishers');
    if ($oldChief !== '' && get_module_setting('evilchief_migrated') != 1) {
        set_module_setting('evilchief', $oldChief);
        set_module_setting('evilchief_migrated', 1);
    }
    return true;
}

function punishers_uninstall(): bool
{
    return true;
}

function punishers_dohook(string $hookname, array $args): array
{
    return $args;
}

function punishers_runevent($type, $link): void
{
    global $session;
    $patrol = new Patrol((string) $link);
    if ($patrol->handle((string) httpget('op'))) {
        return;
    }
    // The fight: battle.php works on the variables of this scope.
    $patrol->kunai();
    $battle = true;
    $victory = false;
    $defeat = false;
    include 'battle.php';
    $patrol->afterBattle((bool) $victory, (bool) $defeat, is_array($badguy ?? null) ? $badguy : []);
}

function punishers_run(): void
{
}
