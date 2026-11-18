<?php

/**
 * The Evil Punishers: merged into the punishers module (2.0).
 *
 * The Hunter-nin now patrol from the punishers module, around the villages
 * in its "evilvillages" setting. This module only remains so that existing
 * installations upgrade cleanly: its forest event is removed, and a player
 * who is in the middle of one of its encounters is handed over.
 */

function evil_punishers_getmoduleinfo(): array
{
    return [
        'name' => 'The Evil Punishers (merged into The Punishers)',
        'author' => '`2Oliver Brendel',
        'version' => '2.0.1',
        'category' => 'Forest Specials',
        'download' => 'core_module',
        'settings' => [
            'The Evil Punishers,title',
            'Merged into the punishers module (its settings are there under Hunter-nin). This module can be uninstalled.,note',
        ],
    ];
}

function evil_punishers_install(): bool
{
    // The punishers module now handles this event; do not double its chance.
    module_dropeventhook('forest');
    return true;
}

function evil_punishers_uninstall(): bool
{
    return true;
}

function evil_punishers_dohook($hookname, $args)
{
    return $args;
}

function evil_punishers_runevent($type, $link): void
{
    global $session;
    if (!is_module_active('punishers')) {
        $session['user']['specialinc'] = '';
        return;
    }
    $session['user']['specialinc'] = 'module:punishers';
    require_once 'modules/punishers.php';
    punishers_runevent($type, $link);
}

function evil_punishers_run(): void
{
}
