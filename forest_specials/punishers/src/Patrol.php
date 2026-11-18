<?php

declare(strict_types=1);

namespace Shinobi\Modules\Punishers;

require_once __DIR__ . '/Texts.php';

/**
 * The forest patrols: the ANBU and the Hunter-nin of the Bloody Mist.
 *
 * Which faction patrols depends on the village the player is in (the
 * "evilvillages" setting). Each faction checks the player's alignment:
 * enemies are stopped and given a choice (fight, bribe, talk, surrender),
 * the indifferent are waved on, and friends are healed.
 *
 * The fight itself runs in punishers_runevent(), because battle.php has to
 * be included there.
 */
final class Patrol
{
    public const MODULE = 'punishers';
    private const BUFF = 'punisher_kunais';

    private const HOSTILE = 0;
    private const NEUTRAL = 1;
    private const FRIENDLY = 2;

    private string $link;

    public function __construct(string $link)
    {
        $this->link = $link;
    }

    // Who patrols where.

    /** Forest event chance (the eval string in the event hook calls this). */
    public static function chance(): int
    {
        return \max(0, (int) \get_module_setting('chance', self::MODULE));
    }

    /** Whether the Hunter-nin, not the ANBU, patrol around the given village. */
    public static function isHunterVillage(string $village): bool
    {
        $list = \array_filter(\array_map('trim', \explode(',', (string) \get_module_setting('evilvillages', self::MODULE))), 'strlen');
        return \in_array($village, $list, true);
    }

    /**
     * Name and leader of a faction, for other modules (akatsuki).
     *
     * @return array{0:string,1:string}
     */
    public static function names(bool $hunters): array
    {
        return $hunters
            ? [(string) \get_module_setting('evilname', self::MODULE), (string) \get_module_setting('evilchief', self::MODULE)]
            : [(string) \get_module_setting('name', self::MODULE), (string) \get_module_setting('chiefname', self::MODULE)];
    }

    private function faction(): string
    {
        global $session;
        return self::isHunterVillage((string) $session['user']['location']) ? 'hunter' : 'anbu';
    }

    // The event, up to the fight.

    /**
     * Handles every op except the fight. Returns false when the fight should
     * run (the caller includes battle.php then).
     */
    public function handle(string $op): bool
    {
        global $session;
        $session['user']['specialinc'] = 'module:' . self::MODULE;
        $faction = $this->faction();
        [$name, $chief] = self::names($faction === 'hunter');

        switch ($op) {
            case '':
                \set_module_pref('chief', 0, self::MODULE);
                \output(self::pick(Texts::STOP));
                \addnav(Texts::NAV_STAND, $this->link . 'op=stand');
                \addnav(Texts::NAV_RUN, $this->link . 'op=run');
                return true;
            case 'stand':
                \output(Texts::SURROUNDED, $name);
                $this->encounter($faction, $name, $chief);
                return true;
            case 'run':
                $this->run($faction, $name, $chief);
                return true;
            case 'bribe':
                $this->bribe($name, $chief);
                return true;
            case 'talk':
                $this->talk();
                return true;
            case 'surrender':
                $this->surrender();
                return true;
            case 'escape':
            case 'hilfeichbineinadminholtmichhierraus': // the old staff escape
                if ($session['user']['superuser'] & SU_DEVELOPER) {
                    \output(Texts::ESCAPE);
                    $this->clearFight();
                    $session['user']['specialinc'] = '';
                    return true;
                }
                return $this->noFight($name);
            case 'leave':
                \output(Texts::LEAVE, $name);
                $session['user']['specialinc'] = '';
                return true;
            case 'combatready':
                // Encounters from before 2.0 carried the leader in the link.
                if ((string) \httpget('over') === '1') {
                    \set_module_pref('chief', 1, self::MODULE);
                }
                $this->startFight($faction, $name, $chief);
                return false;
            default:
                // fight / combat: the battle goes on, if there is one.
                return $this->noFight($name);
        }
    }

    /** Ends the event when there is no fight to continue; false otherwise. */
    private function noFight(string $name): bool
    {
        global $session;
        if ((string) $session['user']['badguy'] !== '') {
            return false;
        }
        \output(Texts::LEAVE, $name);
        $session['user']['specialinc'] = '';
        return true;
    }

    private function encounter(string $faction, string $name, string $chief): void
    {
        global $session;
        $who = \translate_inline((int) $session['user']['sex'] === SEX_MALE ? Texts::BOY : Texts::GIRL);
        $stance = self::stance($faction);

        if ($stance === self::HOSTILE) {
            $overwhelm = \max(1, (int) \get_module_setting('overwhelm', self::MODULE));
            $withChief = \e_rand(1, $overwhelm) === 1;
            \set_module_pref('chief', $withChief ? 1 : 0, self::MODULE);
            if ($withChief) {
                \output(Texts::CHIEF, $chief);
            }
            \output(Texts::HOSTILE[$faction], $who);
            $this->choiceNavs();
            return;
        }
        if ($stance === self::NEUTRAL) {
            \output(Texts::NEUTRAL[$faction], $name, $who);
        } else {
            \output(Texts::FRIENDLY[$faction]);
            if ($session['user']['hitpoints'] < $session['user']['maxhitpoints']) {
                \output(Texts::HEALED[$faction]);
                $session['user']['hitpoints'] = $session['user']['maxhitpoints'];
            }
        }
        \addnav(Texts::NAV_CONTINUE, $this->link . 'op=leave');
    }

    private function choiceNavs(): void
    {
        \addnav(Texts::NAV_FIGHT, $this->link . 'op=combatready');
        if (!\get_module_pref('chief', self::MODULE)) {
            if (self::bribePrice() > 0) {
                \addnav([Texts::NAV_BRIBE, self::bribePrice()], $this->link . 'op=bribe');
            }
            \addnav(Texts::NAV_TALK, $this->link . 'op=talk');
        }
        \addnav(Texts::NAV_SURRENDER, $this->link . 'op=surrender');
    }

    /** Flee chance in percent: grows with level and reaches 70% at the "level" setting. */
    public static function fleeChance(int $level): int
    {
        $full = \max(1, (int) \get_module_setting('level', self::MODULE));
        return \max(10, \min(85, (int) \round(70 * $level / $full)));
    }

    private function run(string $faction, string $name, string $chief): void
    {
        global $session;
        \output(Texts::RUN);
        if (\e_rand(1, 100) <= self::fleeChance((int) $session['user']['level'])) {
            \output(Texts::RUN_AWAY);
            if (\e_rand(0, 1) === 1 && $session['user']['turns'] > 0) {
                \output(Texts::RUN_TIME);
                $session['user']['turns']--;
            }
            $session['user']['specialinc'] = '';
            return;
        }
        \output(Texts::RUN_CAUGHT);
        \output(Texts::SURROUNDED, $name);
        $this->encounter($faction, $name, $chief);
    }

    private static function bribePrice(): int
    {
        global $session;
        return \max(0, (int) \get_module_setting('bribe', self::MODULE)) * \max(1, (int) $session['user']['level']);
    }

    private function bribe(string $name, string $chief): void
    {
        global $session;
        if (\get_module_pref('chief', self::MODULE)) {
            \output(Texts::BRIBE_CHIEF, $chief);
            \addnav(Texts::NAV_FIGHT, $this->link . 'op=combatready');
            \addnav(Texts::NAV_SURRENDER, $this->link . 'op=surrender');
            return;
        }
        $price = self::bribePrice();
        if ($price <= 0 || $session['user']['gold'] < $price) {
            \output(Texts::BRIBE_POOR);
            \addnav(Texts::NAV_FIGHT, $this->link . 'op=combatready');
            \addnav(Texts::NAV_SURRENDER, $this->link . 'op=surrender');
            return;
        }
        $session['user']['gold'] -= $price;
        \debuglog("bribed the forest patrol with $price gold");
        \output(Texts::BRIBE, $price, $name);
        $session['user']['specialinc'] = '';
    }

    private function talk(): void
    {
        global $session;
        // One try, one chance in three. (The leader cannot be talked round; the nav is not offered then.)
        if (!\get_module_pref('chief', self::MODULE) && \e_rand(1, 3) === 1) {
            \output(self::pick(Texts::TALK_WIN));
            $session['user']['specialinc'] = '';
            return;
        }
        \output(self::pick(Texts::TALK_LOSE));
        \addnav(Texts::NAV_FIGHT, $this->link . 'op=combatready');
    }

    private function surrender(): void
    {
        global $session;
        $fine = (int) \floor($session['user']['gold'] * self::goldLossPercent() / 100);
        $session['user']['gold'] -= $fine;
        \output($fine > 0 ? Texts::SURRENDER : Texts::SURRENDER_FREE, $fine);
        if ($session['user']['turns'] > 0) {
            $session['user']['turns']--;
            \output(Texts::SURRENDER_TURN);
        }
        \debuglog("surrendered to the forest patrol and paid $fine gold");
        $session['user']['specialinc'] = '';
    }

    // The fight.

    private function startFight(string $faction, string $name, string $chief): void
    {
        global $session;
        $level = (int) $session['user']['level'];
        $dks = (int) $session['user']['dragonkills'];
        $extra = \e_rand(1, \max(1, $level));
        $badguy = [
            'creaturename' => \sprintf(\translate_inline(Texts::CREATURE), $name),
            'creaturelevel' => $level + \e_rand(1, 3),
            'creatureweapon' => \translate_inline(Texts::WEAPON),
            'creatureattack' => $level + $dks + $extra,
            'creaturedefense' => $level + $dks + $extra,
            'creaturehealth' => $level * 10 + 50 + $extra * 20,
            'diddamage' => 0,
        ];
        if (\get_module_pref('chief', self::MODULE)) {
            // The leader makes the group noticeably, not overwhelmingly, stronger.
            $badguy['creaturehealth'] = (int) \round($badguy['creaturehealth'] * 1.5);
            $badguy['creatureattack'] += 2;
            $badguy['creaturedefense'] += 2;
            $badguy['creaturename'] = \sprintf(\translate_inline(Texts::CREATURE_CHIEF), $chief, \sprintf(\translate_inline(Texts::CREATURE), $name));
            $badguy['creatureweapon'] = \translate_inline(Texts::WEAPON_CHIEF[$faction]);
        }
        $session['user']['badguy'] = \createstring($badguy);
        \set_module_pref('faction', $faction, self::MODULE);
        // 'combat' (not 'fight'): the first page shows the encounter and rolls for surprise.
        \httpset('op', 'combat');
    }

    /** Before each round: now and then a volley of kunai from the trees. */
    public function kunai(): void
    {
        global $session;
        if (isset($session['bufflist'][self::BUFF]) || \e_rand(1, 3) !== 1) {
            return;
        }
        $faction = (string) \get_module_pref('faction', self::MODULE) === 'hunter' ? 'hunter' : 'anbu';
        \apply_buff(self::BUFF, [
            'name' => Texts::KUNAI_NAME,
            'rounds' => 2,
            'mingoodguydamage' => 1,
            'maxgoodguydamage' => 5,
            'minioncount' => 1,
            'effectmsg' => Texts::KUNAI[$faction],
            'schema' => 'module-' . self::MODULE,
        ]);
    }

    /** @param array<string, mixed> $badguy */
    public function afterBattle(bool $victory, bool $defeat, array $badguy): void
    {
        global $session;
        $faction = (string) \get_module_pref('faction', self::MODULE) === 'hunter' ? 'hunter' : 'anbu';
        [$name, $chief] = self::names($faction === 'hunter');
        $withChief = (bool) \get_module_pref('chief', self::MODULE);
        $opponent = (string) ($badguy['creaturename'] ?? $name);
        $place = (string) $session['user']['location'];

        if ($victory) {
            \output(Texts::WIN, $name);
            if ($withChief) {
                \output(Texts::WIN_CHIEF, $chief);
                \addnews(Texts::NEWS_WIN_CHIEF, $session['user']['name'], $opponent, $place);
            } else {
                \addnews(Texts::NEWS_WIN, $session['user']['name'], $name, $place);
            }
            $this->clearFight();
            $session['user']['specialinc'] = '';
            return;
        }
        if ($defeat) {
            $expPercent = \max(0, \min(100, (int) \get_module_setting('experienceloss', self::MODULE)));
            $goldPercent = self::goldLossPercent();
            $exp = (int) \round($session['user']['experience'] * $expPercent / 100);
            $gold = (int) \floor($session['user']['gold'] * $goldPercent / 100);
            $session['user']['experience'] -= $exp;
            $session['user']['gold'] -= $gold;
            $session['user']['hitpoints'] = 0;
            $session['user']['alive'] = 0;
            \output(Texts::DEAD, $name);
            \output(Texts::DEAD_LOSS, $expPercent, $goldPercent);
            \debuglog("lost $exp experience and $gold gold to the forest patrol $name");
            if ($withChief) {
                \addnews(Texts::NEWS_DEAD_CHIEF, $session['user']['name'], $opponent, $place);
            } else {
                \addnews(Texts::NEWS_DEAD, $session['user']['name'], $name, $place);
            }
            $this->clearFight();
            \addnav(Texts::NAV_RETURN);
            \addnav(Texts::NAV_SHADES, 'shades.php');
            $session['user']['specialinc'] = '';
            return;
        }
        require_once 'lib/fightnav.php';
        \fightnav(true, false);
        if ($session['user']['superuser'] & SU_DEVELOPER) {
            \addnav(Texts::NAV_ESCAPE, $this->link . 'op=escape');
        }
    }

    private function clearFight(): void
    {
        global $session;
        \strip_buff(self::BUFF);
        \strip_buff('evil_punisher_kunais'); // from before 2.0
        $session['user']['badguy'] = '';
        \set_module_pref('chief', 0, self::MODULE);
    }

    // Helpers.

    private static function goldLossPercent(): int
    {
        return \max(0, \min(100, (int) \get_module_setting('goldloss', self::MODULE)));
    }

    /** The faction's view of the player: hostile, neutral or friendly. */
    private static function stance(string $faction): int
    {
        if (!\is_module_active('alignment')) {
            return self::NEUTRAL;
        }
        $align = (int) \get_module_pref('alignment', 'alignment');
        $evil = $align <= (int) \get_module_setting('evilalign', 'alignment');
        $good = $align >= (int) \get_module_setting('goodalign', 'alignment');
        if (!$evil && !$good) {
            return self::NEUTRAL;
        }
        // The ANBU hunt the evil; the Hunter-nin hunt the good.
        return ($faction === 'anbu') === $evil ? self::HOSTILE : self::FRIENDLY;
    }

    /**
     * @param list<string> $variants
     */
    private static function pick(array $variants): string
    {
        return $variants[\e_rand(0, \count($variants) - 1)];
    }
}
