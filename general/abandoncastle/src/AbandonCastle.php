<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

class AbandonCastle
{
    public function getModuleInfo(): array
    {
        $info = [
            'name' => 'Abandoned Castle Maze',
            'version' => '2.13',
            'author' => '`#Lonny Luberts modified by `2Oliver Brendel',
            'category' => 'PQcomp',
            'download' => 'http://www.pqcomp.com/modules/mydownloads/visit.php?cid=3&lid=28',
            'vertxtloc' => 'http://www.pqcomp.com/',
            'prefs' => [
                'Abandoned Module User Preferences,title',
                'wasfound' => 'User found the Abandoned Castle?,bool|0',
                'maze' => 'Maze|',
                'mazeturn' => 'Maze Return,int|0',
                'pqtemp' => 'Temporary Information,viewonly',
                'enteredtoday' => 'User has entered the castle today,bool|0',
                'super' => 'Superuser Features,bool|0',
                'mode' => 'Game mode,enum,hardcore,normal|hardcore',
                'mapdata' => 'Serialized map data|',
            ],
            'settings' => [
                'Abandoned Castle Settings,title',
                'castleloc' => 'Where does the Abandonded Castle appear,location|' . \getsetting('villagename', \LOCATION_FIELDS),
                'dkenter' => 'Minimum Number of Dragon Kills to Enter Castle,range,0,100,1|9',
                'forestvil' => 'Yes = Forest Event No = Village Nav,bool|0',
            ],
        ];

        return $info;
    }

    public function install(): bool
    {
        \module_addhook('newday');
        \module_addhook('village');
        \module_addeventhook('forest', 'return 100;');

        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }

    public function doHook(string $hookName, array $args): array
    {
        global $session;

        switch ($hookName) {
            case 'newday':
                \set_module_pref('enteredtoday', 0);
                break;
            case 'village':
                if (\get_module_setting('forestvil') == 0) {
                    if ($session['user']['location'] == \get_module_setting('castleloc')) {
                        \tlschema($args['schemas']['tavernnav']);
                        \addnav($args['tavernnav']);
                        \tlschema();
                        \addnav('Abandoned Castle', 'runmodule.php?module=abandoncastle');
                    }
                }
                break;
        }

        return $args;
    }

    public function runEvent(string $type): void
    {
        global $session;
	addnav("Navigation");
        if (!\get_module_pref('wasfound') && \get_module_setting('forestvil') == 1) {
            \output("`n`2While walking in the Forest, you have found a path that leads to an `bAbandoned Castle`b, which is very old.`n`nA sign near the castle says:`n\"`#Beware, only the bravest warrior should enter the Castle. If you decide to enter,your chance to die is pretty high.");
            \addnews("%s has found a path to the Abandoned Castle.", $session['user']['name']);
            \debuglog("found the Abandoned Castle");
            \addnav("Back to the Forest", "forest.php");
	    \addnav("Options");
            \addnav("Enter the Castle", "runmodule.php?module=abandoncastle");
        } else {
            \redirect("forest.php?op=search");
        }
    }

    public function run(): void
    {
        require 'modules/abandoncastle/abandoncastle.php';
    }

    public function fight(string $op): void
    {
        \page_header('Maze Monster');
        global $session, $badguy;

        if ($op === 'ghost1') {
            $badguy = [
                'creaturename' => \translate_inline('`@Disembodied Spectre`0'),
                'creaturelevel' => 1,
                'creatureweapon' => \translate_inline('ghostly powers'),
                'creatureattack' => 1,
                'creaturedefense' => 2,
                'creaturehealth' => 1000,
                'diddamage' => 0,
            ];

            if (\e_rand(0, 1)) {
                $badguy['hidehitpoints'] = 1;
            }

            $userattack = $session['user']['attack'] + \e_rand(1, 3);
            $userhealth = (int) round($session['user']['hitpoints'] / 2);
            $userdefense = $session['user']['defense'] + \e_rand(1, 3);
            $badguy['creaturelevel'] = $session['user']['level'];
            $badguy['creatureattack'] += ($userattack * 0.5);
            $badguy['creaturehealth'] += $userhealth;
            $badguy['creaturedefense'] += ($userdefense * 2);
            $session['user']['badguy'] = \createstring($badguy);
            $op = 'fight';
        }
        if ($op === 'ghost2') {
            $badguy = [
                'creaturename' => \translate_inline('`@Angry Spectre`0'),
                'creaturelevel' => 1,
                'creatureweapon' => \translate_inline('ghostly powers'),
                'creatureattack' => 1,
                'creaturedefense' => 2,
                'creaturehealth' => 400,
                'diddamage' => 0,
            ];

            if (\e_rand(0, 1)) {
                $badguy['hidehitpoints'] = 1;
            }

            $userattack = $session['user']['attack'] + \e_rand(1, 3);
            $userhealth = (int) round($session['user']['hitpoints'] / 2);
            $userdefense = $session['user']['defense'] + \e_rand(1, 3);
            $badguy['creaturelevel'] = $session['user']['level'];
            $badguy['creatureattack'] += ($userattack * 0.5);
            $badguy['creaturehealth'] += $userhealth;
            $badguy['creaturedefense'] += ($userdefense * 1.5);
            $session['user']['badguy'] = \createstring($badguy);
            $op = 'fight';
        }
        if ($op === 'bat') {
            $badguy = [
                'creaturename' => \translate_inline('`@Bat`0'),
                'creaturelevel' => 1,
                'creatureweapon' => \translate_inline('Sharp Fangs'),
                'creatureattack' => 1,
                'creaturedefense' => 2,
                'creaturehealth' => 1,
                'diddamage' => 0,
            ];

            if (\e_rand(0, 1)) {
                $badguy['hidehitpoints'] = 1;
            }

            $userattack = $session['user']['attack'] + \e_rand(1, 3);
            $userhealth = (int) round($session['user']['hitpoints'] / 2);
            $userdefense = $session['user']['defense'] + \e_rand(1, 3);
            $badguy['creaturelevel'] = $session['user']['level'];
            $badguy['creatureattack'] += ($userattack * 0.5);
            $badguy['creaturehealth'] += (int) round($userhealth * 0.5);
            $badguy['creaturedefense'] += ($userdefense * 0.5);
            $session['user']['badguy'] = \createstring($badguy);
            $op = 'fight';
        }
        if ($op === 'rat') {
            $badguy = [
                'creaturename' => \translate_inline('`@Huge Rat`0'),
                'creaturelevel' => 1,
                'creatureweapon' => \translate_inline('Sharp Fangs'),
                'creatureattack' => 1,
                'creaturedefense' => 2,
                'creaturehealth' => 1,
                'diddamage' => 0,
            ];

            if (\e_rand(0, 1)) {
                $badguy['hidehitpoints'] = 1;
            }

            $userattack = $session['user']['attack'] + \e_rand(1, 3);
            $userhealth = (int) round($session['user']['hitpoints'] / 2);
            $userdefense = $session['user']['defense'] + \e_rand(1, 3);
            $badguy['creaturelevel'] = $session['user']['level'];
            $badguy['creatureattack'] += round($userattack * 0.75);
            $badguy['creaturehealth'] += round($userhealth * 0.75);
            $badguy['creaturedefense'] += round($userdefense * 0.75);
            $session['user']['badguy'] = \createstring($badguy);
            $op = 'fight';
        }
        if ($op === 'minotaur') {
            $badguy = [
                'creaturename' => \translate_inline('`@Minotaur`0'),
                'creaturelevel' => 1,
                'creatureweapon' => \translate_inline('Sharp Fangs'),
                'creatureattack' => 1,
                'creaturedefense' => 30,
                'creaturehealth' => 1000,
                'diddamage' => 0,
            ];

            if (\e_rand(0, 1)) {
                $badguy['hidehitpoints'] = 1;
            }

            $userattack = $session['user']['attack'] + \e_rand(1, 3);
            $userhealth = (int) round($session['user']['hitpoints'] / 2);
            $userdefense = $session['user']['defense'] + \e_rand(1, 3);
            $badguy['creaturelevel'] = $session['user']['level'];
            $badguy['creatureattack'] += ($userattack - 4);
            $badguy['creaturehealth'] += $userhealth;
            $badguy['creaturedefense'] += $userdefense;
            $session['user']['badguy'] = \createstring($badguy);
            $op = 'fight';
        }

        if ($op === 'fight' || $op === 'run') {
            $battle = true;
            $fight = true;
            if ($battle) {
                $session['user']['specialinc'] = 'module:abandoncastle';
                require_once 'battle.php';

                if ($victory) {
                    \output("`b`4You have slain `^%s`4.`b`n", $badguy['creaturename']);
                    $badguy = [];
                    $session['user']['badguy'] = '';
                    $gold = \e_rand(50, 250);
                    $experience = $session['user']['level'] * \e_rand(37, 99);
                    \output("`#You receive `6%s `#gold!`n", $gold);
                    $session['user']['gold'] += $gold;
                    \output("`#You receive `6%s `#experience!`n", $experience);
                    $session['user']['experience'] += $experience;
                    $session['user']['specialinc'] = '';
                    \addnav('Continue', 'runmodule.php?module=abandoncastle&loc=' . \get_module_pref('pqtemp'));
                } elseif ($defeat) {
                    \output('As you hit the ground `^%s runs away.', $badguy['creaturename']);
                    \addnews('`% %s`5 has been slain when %s encountered a %s in the Abandoned Castle.', $session['user']['name'], ($session['user']['sex'] ? \translate_inline('she') : \translate_inline('he')), $badguy['creaturename']);
                    $badguy = [];
                    $session['user']['badguy'] = '';
                    $session['user']['hitpoints'] = 0;
                    $session['user']['alive'] = false;
                    $session['user']['specialinc'] = '';
                    \addnav('Continue', 'shades.php');
                } else {
                    require_once 'lib/fightnav.php';
                    \fightnav(true, false, 'runmodule.php?module=abandoncastle');
                    if ($badguy['creaturehealth'] > 0) {
                        $hp = $badguy['creaturehealth'];
                    }
                }
            } else {
                \redirect('runmodule.php?module=abandoncastle&loc=' . \get_module_pref('pqtemp'));
            }
        }

        \page_footer();
    }
}
