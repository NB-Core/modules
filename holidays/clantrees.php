<?php

/**
 * Clan Christmas trees. Originally by Sneakabout.
 *
 * Clans buy a tree from the salesman outside the Clan Halls and decorate it
 * together with turns, gold and gems; a splendid tree gives its members a
 * small buff once a day, and the best tree of the winter is named in the
 * village. Trees belong to one winter and are cleared for the next.
 */

use Shinobi\Modules\ClanTrees\Trees;

require_once __DIR__ . '/clantrees/src/Trees.php';

function clantrees_getmoduleinfo(): array
{
    return [
        'name' => 'Clan Christmas Trees',
        'version' => '2.0',
        'author' => 'Sneakabout`0, rewritten by `2Oliver Brendel',
        'category' => 'Holidays|Christmas',
        'download' => 'core_module',
        'settings' => [
            'Clan Christmas Trees Settings,title',
            'treebuy' => 'Can you buy a tree?,bool|0',
            'treereward' => 'How many turns do they get the buff for?,int|15',
            'besttree' => 'Clan ID with the best tree,viewonly',
            'bestseason' => 'Winter of the best tree,viewonly|0',
            'competitive' => 'Is the tree decoration competitive?,bool|0',
            'treebonus' => 'How many tree points do they get a buff at?,int|100',
            'salesman' => 'Name of the Salesman?,|Sativ',
        ],
        'prefs' => [
            'gotbuff' => 'Has player gained the tree buff today?,bool|0',
        ],
        'prefs-clans' => [
            'Clan Christmas Trees Clan Preferences,title',
            'havetree' => 'Does this clan have a tree yet?,bool|0',
            'season' => 'Winter the tree belongs to,int|0',
            'treepoints' => 'How many points are in this tree?,int|0',
            'basetree' => 'What size tree at start?,enum,0,None,10,Small,20,Medium,50,Grand|0',
            'time' => 'How many turns have clan members put in?,int|0',
            'gold' => 'How much gold spent on tinsel?,int|0',
            'gems' => 'How many gems spent on baubles?,int|0',
        ],
    ];
}

function clantrees_install(): bool
{
    module_addhook('village');
    module_addhook('header-clan');
    module_addhook('newday');
    return true;
}

function clantrees_uninstall(): bool
{
    return true;
}

function clantrees_dohook(string $hookname, array $args): array
{
    switch ($hookname) {
        case 'village':
            Trees::village();
            break;
        case 'header-clan':
            Trees::clanHeader();
            break;
        case 'newday':
            set_module_pref('gotbuff', 0);
            break;
    }
    return $args;
}

function clantrees_runevent(): void
{
}

function clantrees_run(): void
{
    (new Trees())->run();
}
