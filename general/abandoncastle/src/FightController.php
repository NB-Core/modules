<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

/**
 * Handles combat interactions inside the Abandoned Castle.
 *
 * The controller operates on the global session and monster state.
 * It sets up encounters for the different monsters that can appear
 * and resolves the battle when the player chooses to fight or run.
 */
class FightController
{
    /**
     * Dispatch combat handling based on the operation value.
     *
     * @param string $op Operation identifier received from the request.
     */
    public function dispatch(string $op): void
    {
        \page_header('Maze Monster');

        switch ($op) {
            case 'ghost1':
                $this->ghost1();
                break;
            case 'ghost2':
                $this->ghost2();
                break;
            case 'bat':
                $this->bat();
                break;
            case 'rat':
                $this->rat();
                break;
            case 'minotaur':
                $this->minotaur();
                break;
            case 'fight':
                $this->fight();
                break;
            case 'run':
                $this->run();
                break;
        }

        \page_footer();
    }

    /**
     * Prepare an encounter with a Disembodied Spectre and start combat.
     *
     * Expects $session['user'] to contain attack, defense, level and hitpoints.
     * Populates $session['user']['badguy'] with the spectre's stats and triggers the fight.
     *
     * @global array $session
     * @global array $badguy
     */
    private function ghost1(): void
    {
        global $session, $badguy;

        $badguy = [
            'creaturename'   => \translate_inline('`@Disembodied Spectre`0'),
            'creaturelevel'  => 1,
            'creatureweapon' => \translate_inline('ghostly powers'),
            'creatureattack' => 1,
            'creaturedefense'=> 2,
            'creaturehealth' => 1000,
            'diddamage'      => 0,
        ];

        if (\e_rand(0, 1)) {
            $badguy['hidehitpoints'] = 1;
        }

        $userattack  = $session['user']['attack'] + \e_rand(1, 3);
        $userhealth  = (int) \round($session['user']['hitpoints'] / 2);
        $userdefense = $session['user']['defense'] + \e_rand(1, 3);
        $badguy['creaturelevel']   = $session['user']['level'];
        $badguy['creatureattack'] += ($userattack * 0.5);
        $badguy['creaturehealth'] += $userhealth;
        $badguy['creaturedefense']+= ($userdefense * 2);
        $session['user']['badguy'] = \createstring($badguy);

        $this->fight();
    }

    /**
     * Prepare an encounter with an Angry Spectre and start combat.
     *
     * @global array $session
     * @global array $badguy
     */
    private function ghost2(): void
    {
        global $session, $badguy;

        $badguy = [
            'creaturename'   => \translate_inline('`@Angry Spectre`0'),
            'creaturelevel'  => 1,
            'creatureweapon' => \translate_inline('ghostly powers'),
            'creatureattack' => 1,
            'creaturedefense'=> 2,
            'creaturehealth' => 400,
            'diddamage'      => 0,
        ];

        if (\e_rand(0, 1)) {
            $badguy['hidehitpoints'] = 1;
        }

        $userattack  = $session['user']['attack'] + \e_rand(1, 3);
        $userhealth  = (int) \round($session['user']['hitpoints'] / 2);
        $userdefense = $session['user']['defense'] + \e_rand(1, 3);
        $badguy['creaturelevel']   = $session['user']['level'];
        $badguy['creatureattack'] += ($userattack * 0.5);
        $badguy['creaturehealth'] += $userhealth;
        $badguy['creaturedefense']+= ($userdefense * 1.5);
        $session['user']['badguy'] = \createstring($badguy);

        $this->fight();
    }

    /**
     * Prepare an encounter with a Bat and start combat.
     *
     * @global array $session
     * @global array $badguy
     */
    private function bat(): void
    {
        global $session, $badguy;

        $badguy = [
            'creaturename'   => \translate_inline('`@Bat`0'),
            'creaturelevel'  => 1,
            'creatureweapon' => \translate_inline('Sharp Fangs'),
            'creatureattack' => 1,
            'creaturedefense'=> 2,
            'creaturehealth' => 1,
            'diddamage'      => 0,
        ];

        if (\e_rand(0, 1)) {
            $badguy['hidehitpoints'] = 1;
        }

        $userattack  = $session['user']['attack'] + \e_rand(1, 3);
        $userhealth  = (int) \round($session['user']['hitpoints'] / 2);
        $userdefense = $session['user']['defense'] + \e_rand(1, 3);
        $badguy['creaturelevel']   = $session['user']['level'];
        $badguy['creatureattack'] += ($userattack * 0.5);
        $badguy['creaturehealth'] += (int) \round($userhealth * 0.5);
        $badguy['creaturedefense']+= ($userdefense * 0.5);
        $session['user']['badguy'] = \createstring($badguy);

        $this->fight();
    }

    /**
     * Prepare an encounter with a Huge Rat and start combat.
     *
     * @global array $session
     * @global array $badguy
     */
    private function rat(): void
    {
        global $session, $badguy;

        $badguy = [
            'creaturename'   => \translate_inline('`@Huge Rat`0'),
            'creaturelevel'  => 1,
            'creatureweapon' => \translate_inline('Sharp Fangs'),
            'creatureattack' => 1,
            'creaturedefense'=> 2,
            'creaturehealth' => 1,
            'diddamage'      => 0,
        ];

        if (\e_rand(0, 1)) {
            $badguy['hidehitpoints'] = 1;
        }

        $userattack  = $session['user']['attack'] + \e_rand(1, 3);
        $userhealth  = (int) \round($session['user']['hitpoints'] / 2);
        $userdefense = $session['user']['defense'] + \e_rand(1, 3);
        $badguy['creaturelevel']   = $session['user']['level'];
        $badguy['creatureattack'] += \round($userattack * 0.75);
        $badguy['creaturehealth'] += \round($userhealth * 0.75);
        $badguy['creaturedefense']+= \round($userdefense * 0.75);
        $session['user']['badguy'] = \createstring($badguy);

        $this->fight();
    }

    /**
     * Prepare an encounter with the Minotaur and start combat.
     *
     * @global array $session
     * @global array $badguy
     */
    private function minotaur(): void
    {
        global $session, $badguy;

        $badguy = [
            'creaturename'   => \translate_inline('`@Minotaur`0'),
            'creaturelevel'  => 1,
            'creatureweapon' => \translate_inline('Sharp Fangs'),
            'creatureattack' => 1,
            'creaturedefense'=> 30,
            'creaturehealth' => 1000,
            'diddamage'      => 0,
        ];

        if (\e_rand(0, 1)) {
            $badguy['hidehitpoints'] = 1;
        }

        $userattack  = $session['user']['attack'] + \e_rand(1, 3);
        $userhealth  = (int) \round($session['user']['hitpoints'] / 2);
        $userdefense = $session['user']['defense'] + \e_rand(1, 3);
        $badguy['creaturelevel']   = $session['user']['level'];
        $badguy['creatureattack'] += ($userattack - 4);
        $badguy['creaturehealth'] += $userhealth;
        $badguy['creaturedefense']+= $userdefense;
        $session['user']['badguy'] = \createstring($badguy);

        $this->fight();
    }

    /**
     * Resolve the current battle.
     *
     * On victory the player gains gold and experience. On defeat the player dies
     * and is sent to the shades. Otherwise the fight navigation is displayed.
     *
     * Expects $session['user']['badguy'] to be populated.
     *
     * @global array $session
     * @global array $badguy
     * @global bool  $victory
     * @global bool  $defeat
     */
    private function fight(): void
    {
        global $session, $badguy, $victory, $defeat;

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

    /**
     * Attempt to flee from battle.
     *
     * The run option is processed like a normal fight in this module.
     */
    private function run(): void
    {
        $this->fight();
    }
}
