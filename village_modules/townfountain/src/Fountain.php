<?php

declare(strict_types=1);

namespace Shinobi\Modules\TownFountain;

require_once __DIR__ . '/Texts.php';

/**
 * The village fountain: a coin and a wish once a day, a chat, a bit of
 * gossip, and a splash in the water.
 */
final class Fountain
{
    private const MODULE = 'townfountain';
    private const URL = 'runmodule.php?module=townfountain';

    public static function villageDesc(): void
    {
        \output(Texts::VILLAGE_DESC, self::URL, true);
        \addnav('', self::URL);
    }

    public function run(): void
    {
        global $session;
        $village = (string) \get_module_setting('fountainloc', self::MODULE);
        \page_header(Texts::TITLE, \full_sanitize($village));

        if ($session['user']['location'] !== $village) {
            \villagenav();
            \page_footer();
            return;
        }

        $what = (string) \httpget('what');
        if ((string) \httpget('op') === 'options' && $what === 'chat') {
            // The commentary needs addcommentary() before anything else is shown.
            require_once 'lib/commentary.php';
            \addcommentary();
        }

        switch ((string) \httpget('op') === 'options' ? $what : '') {
            case 'tosscoin':
                $this->toss();
                break;
            case 'chat':
                \output(Texts::TALK);
                \commentdisplay('', 'townfountain', Texts::TALK_PROMPT, 15, Texts::TALK_VERB);
                break;
            case 'listen':
                \output(self::pick(Texts::GOSSIP));
                break;
            case 'jumpin':
                $this->jump();
                break;
            default:
                \output(self::pick(Texts::ARRIVAL), $village);
                $this->navs();
                \villagenav();
                \page_footer();
                return;
        }
        \addnav(Texts::NAV_BACK, self::URL);
        \villagenav();
        \page_footer();
    }

    private function toss(): void
    {
        global $session;
        if (\get_module_pref('tossedcoin', self::MODULE)) {
            \output(Texts::TOSS_DONE);
            return;
        }
        if ($session['user']['gold'] < 1) {
            \output(Texts::TOSS_POOR);
            return;
        }
        $session['user']['gold']--;
        \set_module_pref('tossedcoin', 1, self::MODULE);
        \output(Texts::TOSS);
        switch (\e_rand(1, 10)) {
            case 1:
            case 2:
                \output(Texts::TOSS_JOURNEY);
                break;
            case 3:
            case 4:
                \output(Texts::TOSS_ADVENTURE);
                break;
            case 5:
                \output(Texts::TOSS_THIEF_KID);
                break;
            case 6:
                \output(Texts::TOSS_ENERGY);
                $session['user']['turns']++;
                \debuglog('gained a forest fight at the village fountain');
                break;
            case 7:
                \output(Texts::TOSS_GEM);
                $session['user']['gems']++;
                \debuglog('found a gem in the village fountain');
                break;
            case 8:
                \output(Texts::TOSS_THIEF_OLD);
                break;
            default:
                require_once 'lib/partner.php';
                \output(Texts::TOSS_LOVE, \get_partner(true));
        }
    }

    private function jump(): void
    {
        global $session;
        if (\get_module_pref('splashedaround', self::MODULE)) {
            \output(Texts::JUMP_DONE);
            return;
        }
        \set_module_pref('splashedaround', 1, self::MODULE);
        \output(Texts::JUMP);
        $session['user']['spirits'] = 2;
        if (\is_module_active('odor')) {
            $odor = (int) \get_module_pref('odor', 'odor');
            \set_module_pref('odor', \max(0, $odor - (int) \get_module_setting('cleanup', self::MODULE)), 'odor');
            \output(Texts::JUMP_CLEAN);
        }
        if (\e_rand(1, 5) === 1) {
            \addnews(Texts::JUMP_NEWS, $session['user']['name']);
        }
    }

    private function navs(): void
    {
        global $session;
        \addnav(Texts::NAV_HEADER);
        if ($session['user']['gold'] > 0 && !\get_module_pref('tossedcoin', self::MODULE)) {
            \addnav(Texts::NAV_TOSS, self::URL . '&op=options&what=tosscoin');
        }
        \addnav(Texts::NAV_TALK, self::URL . '&op=options&what=chat');
        \addnav(Texts::NAV_LISTEN, self::URL . '&op=options&what=listen');
        if (!\get_module_pref('splashedaround', self::MODULE)) {
            \addnav(Texts::NAV_JUMP, self::URL . '&op=options&what=jumpin');
        }
    }

    /**
     * @param list<string> $variants
     */
    private static function pick(array $variants): string
    {
        return $variants[\e_rand(0, \count($variants) - 1)];
    }
}
