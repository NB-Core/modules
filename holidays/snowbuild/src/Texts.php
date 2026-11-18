<?php

declare(strict_types=1);

namespace Shinobi\Modules\Snowbuild;

/**
 * Everything the snowman building contest says.
 *
 * English source text for output(). In most texts the first %s is the
 * judge's name from the settings.
 */
final class Texts
{
    public const TITLE = "`c`b`&The Snowman Contest`b`c`n";
    public const NAV = "Snowman Contest";
    public const CLOSED = "`7At the edge of the square, where the snowman contest is held in winter, there is only a muddy patch of grass and a forgotten carrot. Come back when the snow returns.";

    /** %s, %s: winner name, snowman kind. */
    public const VILLAGE_DESC = "`n`QIn the middle of the square stands an almost lifelike snowman: %2\$s`Q. A shiny placard at its feet reads \"`&First prize: %1\$s`Q\".`n";

    /** @var list<string> %s: judge. */
    public const ARRIVAL = [
        "`7At the edge of the village square, the snow has been swept into a huge pile, and all around it villagers and shinobi are building snowmen. Some are lumpy, some are leaning, and one is very clearly a self-portrait of a genin's sensei, complete with a mask made of a scarf. Somebody kicks over a lopsided snowman to start a new one with its snow.`n`nA white-bearded old man in a padded coat sees your shocked face and laughs. `#\"Eh, don't worry, that wasn't a winner! We all have a go, and if we don't win, the snow goes back in the pile. That's how it works! I'm %s, and I judge this little contest every winter.\"",
        "`7The snowman contest is in full swing. Children roll balls of snow bigger than themselves across the square, a pair of chuunin are arguing about whether chakra-packed snow counts as cheating, and an old man with a clipboard, %s`7, walks between the snowmen making very serious notes.`n`nHe spots you and beams. `#\"A new contestant! Wonderful. The snow is free, the hat and buttons cost a little - for the orphanage, you understand.\"",
    ];
    /** %s: gold cost. */
    public const OFFER = "`n`n`#\"I'll lend you the accessories for `^%s gold`#. What kind of snowman will it be?\"";
    public const NO_GOLD = "`n`n`7If only you had enough gold. You shove your hands into your pockets and watch the others build.";
    public const DONE = "`n`n`7You have already built today, and your fingers are still numb. Better leave the snow to the others until tomorrow.";
    public const NAV_BUILD = "Build %s (%s gold)";

    /** @var array<string, array{0:string,1:string}> Style key => [name, what you put on it]. */
    public const STYLES = [
        'classic' => ['a classic snowman', 'a carrot nose, coal eyes, a row of buttons and an old red scarf'],
        'ninja' => ['a ninja snowman', 'a forehead protector, a black mask pulled over its face, and two kunai stuck into its sides for arms'],
        'hokage' => ['a Hokage snowman', 'a wide straw hat with a red veil and a white cloak made from a bedsheet'],
        'toad' => ['a giant toad of snow', 'two big round eyes of coal, a pipe made from a stick, and a very long tongue made from a red ribbon'],
    ];

    /** %s, %s, %s: judge, gold, what you put on it. */
    public const BUILD = "`7You pay %s`7 the `^%s`7 gold for the orphanage, and he hands you a sack of accessories: %s. You roll the snow, stack it, pack it and shape it, until your fingers burn with cold and your breath hangs around you in clouds. At last you step back to look.`n`n";

    /** %s, %s: judge, extra gold. */
    public const RESULT_WINNER = "`7The crowd goes quiet, then bursts into applause. %s`7 drops his clipboard. `#\"Wonderful! Just wonderful! I can't remember the last time I saw a snowman like that. First prize! This one stays on display - I'm having it roped off right now!\"`n`n`7He presses a heavy bag of gold into your hands. You head back to the others `^%s`7 gold richer, glowing with pride.";
    /** %s, %s: judge, extra gold. */
    public const RESULT_GOOD = "`7Your snowman looks almost alive, as if it might wave at you. %s`7 walks around it twice and nods. `#\"Yes, very nice. High quality work. Well done!\"`7 He tosses you a small bag of gold. You are `^%s`7 gold richer than when you came.";
    /** %s, %s: judge, gold back. */
    public const RESULT_AVERAGE = "`7Your snowman is perfectly respectable, standard, solid, a snowman's snowman. %s`7 looks it over. `#\"Not bad at all. With a little more detail, it could win first prize.\"`7 He gives you back your `^%s`7 gold.";
    /** %s: judge. */
    public const RESULT_POOR = "`7Your snowman crudely resembles what might, on a dark night and from a distance, be called a person. %s`7 looks at it for a long time. `#\"I can tell you put a lot of... effort into this. Well, you did your best, right?\"`7 Disappointed, you head back to the others.";
    /** %s, %s: judge, weapon. */
    public const RESULT_SLUSH = "`7Somewhere along the way, you got carried away and tried to sharpen the details with your %2\$s. Your snowman is now a large heap of pulpy slush. %1\$s`7 laughs softly and shakes his head. `#\"Perhaps next time, eh?\"";

    public const BUFF = "`QFirst Prize";
    public const BUFF_ROUND = "`QThe pride of a champion snowman builder steadies your hand.";
    public const BUFF_END = "`QThe glow of your victory fades.";
}
