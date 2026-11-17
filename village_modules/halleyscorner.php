<?php

/**
 * Halley's Corner: a cosy tavern for travellers in the village square.
 *
 * Halley's drinks trade PvP fights for travels, her tea mends and calms, and
 * a stamp card rewards the regulars. Halley shares news and, chapter by
 * chapter, her own story with those who keep coming back. There is a
 * storyteller by the fire, a travel board, the patrons' chat, and postcards
 * to send to friends.
 */

use Shinobi\Modules\HalleysCorner\Corner;

require_once __DIR__ . '/halleyscorner/src/Corner.php';

function halleyscorner_getmoduleinfo(): array
{
    return [
        'name' => "Halleys Corner (PvP->Travel)",
        'author' => '`2Oliver Brendel',
        'version' => '2.0',
        'category' => 'Village|Shops',
        'download' => '',
        'settings' => [
            'Corner Settings,title',
            'owner' => 'Name of the Owner (female),text|`4Ha`1ll`gey',
            'location' => 'Where is the Corner,location|Konohagakure',
            'Drinks,title',
            'cost' => "Root Beer: costs X PvP fights,floatrange,0,20,1|1",
            'gain' => 'Root Beer: gives X travels,floatrange,0,20,1|1',
            'brewcost' => "Wanderer's Brew (once a day): costs X PvP fights,floatrange,0,20,1|2",
            'brewgain' => "Wanderer's Brew: gives X travels,floatrange,0,20,1|3",
            'dailylimit' => 'Travel drinks per day (Root Beer and Brew together),range,1,20,1|5',
            'teacost' => 'Still Water Tea (once a day): costs X PvP fights,floatrange,0,20,1|1',
            'teaheal' => 'Still Water Tea: heals X percent of max hitpoints,range,0,100,5|25',
            'cardbonus' => 'Extra travels for a full stamp card (10 Root Beers),range,0,5,1|1',
            'Postcards,title',
            'postcardcost' => 'Gold per postcard,int|50',
            'postcardlimit' => 'Postcards per day,range,0,10,1|3',
        ],
        'prefs' => [
            "Halley's Corner,title",
            'visits' => 'Days the player has visited,int|0',
            'visitedtoday' => 'Visited today,bool|0',
            'drinkstoday' => 'Travel drinks today,int|0',
            'brewtoday' => "Had the Wanderer's Brew today,bool|0",
            'teatoday' => 'Had the tea today,bool|0',
            'stamps' => 'Stamps on the current card,int|0',
            'chapter' => "Chapters of Halley's story heard,int|0",
            'postcardstoday' => 'Postcards sent today,int|0',
        ],
    ];
}

function halleyscorner_install(): bool
{
    // The Corner used to be fixed to Konoha; it now follows its location setting.
    module_drophook('village-Konohagakure');
    module_addhook('village');
    module_addhook('changesetting');
    module_addhook('newday');
    return true;
}

function halleyscorner_uninstall(): bool
{
    return true;
}

function halleyscorner_dohook(string $hookname, array $args): array
{
    global $session;

    switch ($hookname) {
        case 'changesetting':
            // Follow the village when it is renamed.
            if ($args['setting'] == 'villagename' && $args['old'] == get_module_setting('location')) {
                set_module_setting('location', $args['new']);
            }
            break;
        case 'newday':
            set_module_pref('visitedtoday', 0);
            set_module_pref('drinkstoday', 0);
            set_module_pref('brewtoday', 0);
            set_module_pref('teatoday', 0);
            set_module_pref('postcardstoday', 0);
            break;
        case 'village':
            if ($session['user']['dragonkills'] < 1 || $session['user']['location'] != get_module_setting('location')) {
                break;
            }
            tlschema($args['schemas']['marketnav']);
            addnav($args['marketnav']);
            tlschema();
            addnav(['%s`0\'s Corner', get_module_setting('owner')], 'runmodule.php?module=halleyscorner');
            break;
    }
    return $args;
}

function halleyscorner_run(): void
{
    (new Corner())->run();
}
