<?php

/**
 * The Grinch Miniboss. By ShinobiIceSlayer, modified by Neji.
 *
 * A Christmas challenge in the forest: twelve fights of rising difficulty
 * against the Grinch creatures (creature category "<categories>-<0..11>")
 * and his elves, a few times a day, in the Christmas season only. Winners
 * get loot from the inventory and a place in the hall of fame. Texts and
 * helpers live in thegrinch/src/.
 */

use Lotgd\Battle;
use Shinobi\Modules\TheGrinch\Grinch;
use Shinobi\Modules\TheGrinch\Texts;

require_once 'lib/forest.php';
require_once 'lib/taunt.php';
require_once 'lib/events.php';
require_once __DIR__ . '/thegrinch/src/Grinch.php';

function thegrinch_getmoduleinfo(): array
{
    return [
        'name' => 'The Grinch Miniboss',
        'author' => '`JShinobiIceSlayer, modified by Neji',
        'version' => '2.0',
        'category' => 'Holidays|Christmas',
        'download' => 'core_module',
        'settings' => [
            'Grinch Settings,title',
            'start' => 'The Grinch is around from (mm-dd; empty: all year),text|12-1',
            'end' => 'The Grinch is around until (mm-dd; may be after New Year),text|1-2',
            'specialchance' => 'Chance for special event in the forest Hook,int|10',
            'categories' => 'Creature category prefix (the challenges use prefix-0 to prefix-11),text|grinch',
            'grinch' => 'Name of the Grinch,text|`2T`@he `2G`@rinch',
            'elf' => 'Name of his elves,text|`QK`yyuubi `2E`@lf',
            'beast' => 'Name of the beast that fights when a challenge has no creature,text|`QK`yyuubi',
            'maxseen' => 'How many fights per day?,int|3',
            'villagehof' => 'Show the whole hall of fame in the village (instead of a link)?,bool|0',
        ],
        'prefs' => [
            'seen' => 'Grinch fights today,int|0',
            'lastwon' => 'Last fight (note for admins),viewonly',
            'enemy-0' => 'Times defeat enemy 0,viewonly',
            'enemy-1' => 'Times defeat enemy 1,viewonly',
            'enemy-2' => 'Times defeat enemy 2,viewonly',
            'enemy-3' => 'Times defeat enemy 3,viewonly',
            'enemy-4' => 'Times defeat enemy 4,viewonly',
            'enemy-5' => 'Times defeat enemy 5,viewonly',
            'enemy-6' => 'Times defeat enemy 6,viewonly',
            'enemy-7' => 'Times defeat enemy 7,viewonly',
            'enemy-8' => 'Times defeat enemy 8,viewonly',
            'enemy-9' => 'Times defeat enemy 9,viewonly',
            'enemy-10' => 'Times defeat enemy 10,viewonly',
            'enemy-11' => 'Times defeat enemy 11,viewonly',
        ],
    ];
}

function thegrinch_install(): bool
{
    module_addhook_priority('forest', 0);
    module_addhook('newday');
    module_addhook('village');
    return true;
}

function thegrinch_uninstall(): bool
{
    return true;
}

function thegrinch_dohook(string $hookname, array $args): array
{
    switch ($hookname) {
        case 'forest':
            if (Grinch::inSeason()) {
                addnav(Texts::NAV_HEADER);
                addnav(['%s', Grinch::name()], Grinch::URL . '&op=');
            }
            break;
        case 'newday':
            set_module_pref('seen', 0);
            break;
        case 'village':
            if (!Grinch::inSeason()) {
                break;
            }
            // The full list costs two queries per challenge; by default the village only links to it.
            if (get_module_setting('villagehof')) {
                Grinch::showHallOfFame();
            } else {
                output(Texts::VILLAGE, Grinch::name());
                addnav(Texts::NAV_HOF, Grinch::URL . '&op=hof');
            }
            break;
    }
    return $args;
}

/** The challenge menu. */
function thegrinch_nav(bool $noshowmessage = false): void
{
    tlschema('thegrinch');
    addnav('Navigation');
    villagenav();
    foreach (Texts::CHALLENGES as $section => $challenges) {
        addnav($section);
        foreach ($challenges as [$label, $type]) {
            addnav($label, Grinch::URL . '&op=search&enemytype=' . $type);
        }
    }
    addnav('Other');
    addnav(Texts::NAV_HOF, Grinch::URL . '&op=hof');
    if (!$noshowmessage) {
        output(Texts::TITLE);
        $arrival = Texts::ARRIVAL;
        output($arrival[e_rand(0, count($arrival) - 1)], Grinch::name());
        modulehook('thegrinch-desc');
    }
    modulehook('elite', []);
    module_display_events('thegrinch', Grinch::URL . '&op=');
    tlschema();
}

function thegrinch_run(): void
{
    global $session, $companions;

    tlschema('forest');
    page_header('The Forest');
    $op = (string) httpget('op');

    if ($op === 'hof') {
        Grinch::showHallOfFame();
        addnav('Navigation');
        villagenav();
        if (Grinch::inSeason()) {
            addnav(Texts::NAV_BACK_CHALLENGE, Grinch::URL . '&op=');
        }
        page_footer();
        return;
    }

    $dontdisplayforestmessage = handle_event('thegrinch');
    addnav('Forest');
    addnav('Back to the Forest', 'forest.php');
    addnav('Actions');

    // Out of season only a fight already under way may be finished.
    if (!Grinch::inSeason() && (string) $session['user']['badguy'] === '') {
        output(Texts::CLOSED);
        page_footer();
        return;
    }

    $battle = false;

    if ($op === 'run') {
        if (e_rand() % 3 === 0) {
            output(Texts::FLED);
            $op = '';
            httpset('op', '');
            unsuspend_buffs();
            foreach ($companions as $index => $companion) {
                if (!empty($companion['expireafterfight'])) {
                    unset($companions[$index]);
                }
            }
        } else {
            output(Texts::NOT_FLED);
        }
    }

    if ($op === 'search') {
        checkday();
        if (Grinch::doneForToday()) {
            output(Texts::ENOUGH_TODAY);
            addnav('Navigation');
            villagenav();
            page_footer();
            return;
        }
        if ($session['user']['turns'] <= 0) {
            output(Texts::TOO_TIRED);
            $op = '';
            httpset('op', '');
        } else {
            modulehook('thegrinchsearch', []);
            modulehook('soberup', [
                'soberval' => 0.9,
                'sobermsg' => "`&Faced with the prospect of death, you sober up a little.`n",
                'schema' => 'forest',
            ]);
            if (module_events('thegrinch', (int) get_module_setting('specialchance')) != 0) {
                if (!checknavs()) {
                    // If we're showing the forest, make sure to reset the special and the specialmisc.
                    $session['user']['specialinc'] = '';
                    $session['user']['specialmisc'] = '';
                    $dontdisplayforestmessage = true;
                    $op = '';
                    httpset('op', '');
                } else {
                    page_footer();
                    return;
                }
            } else {
                $session['user']['turns']--;
                $battle = true;
                restore_buff_fields();
                $stack = Grinch::enemies(Grinch::enemyType());
                calculate_buff_fields();
                $attackstack = ['enemies' => $stack, 'options' => ['type' => 'forest']];
                $attackstack = modulehook('forestfight-start', $attackstack);
                $attackstack = modulehook('thegrinchfight-start', $attackstack);
                $session['user']['badguy'] = createstring($attackstack);
                // A nav that already sets the number of rounds goes straight to the fight.
                if (httpget('auto') != '') {
                    httpset('op', 'fight');
                    $op = 'fight';
                }
            }
        }
    }

    if ($op === 'fight' || $op === 'run' || $op === 'newtarget') {
        $battle = true;
    }

    if ($battle) {
        if (is_module_active('specialtysystem')) {
            require_once 'modules/specialtysystem/datafunctions.php';
            specialtysystem_initialize_data();
        }
        $victory = false;
        $defeat = false;
        require_once 'battle.php';

        if ($victory) {
            require_once 'lib/forestoutcomes.php';
            $op = '';
            httpset('op', '');
            forestvictory($newenemies, isset($options['denyflawless']) ? $options['denyflawless'] : false);
            $dontdisplayforestmessage = true;
            $type = (int) ($newenemies[0]['creaturegrinch'] ?? 0);
            Grinch::record($type, true, is_array($badguy) ? $badguy : []);
            Grinch::loot($type, (string) ($newenemies[0]['creaturename'] ?? ''));
        } elseif ($defeat) {
            $type = (int) (is_array($badguy) ? ($badguy['creaturegrinch'] ?? 0) : 0);
            Grinch::record($type, false, is_array($badguy) ? $badguy : []);
            output(Texts::DEFEAT, Grinch::name());
            addnews(Texts::NEWS_DEFEAT, $session['user']['name'], Grinch::name());
            // The Grinch does not kill; he leaves you lying in the snow.
            $session['user']['hitpoints'] = 1;
            $session['user']['alive'] = true;
            $session['user']['badguy'] = '';
            $session['user']['specialinc'] = '';
        } else {
            Battle::fightnav(true, true, Grinch::URL . '&');
        }
    }

    if ($op === '') {
        thegrinch_nav($dontdisplayforestmessage);
    }
    page_footer();
}
