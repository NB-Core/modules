<?php

declare(strict_types=1);

namespace Shinobi\Modules\TownFountain;

/**
 * Everything the village fountain says.
 *
 * English source text for output(). Narration is in `2; %s in the
 * arrival texts is the village the fountain stands in.
 */
final class Texts
{
    /** %s: village. */
    public const TITLE = "%s Fountain";
    /** %s: link to the fountain. */
    public const VILLAGE_DESC = "`n`3In the middle of the square, a fountain splashes quietly. Would you like to <a href=\"%s\">[visit it]</a>?`n";
    public const VILLAGE_DESC_NAV = "Visit the fountain";
    public const MODERATE = "Village Fountain";

    /** @var list<string> %s: village. */
    public const ARRIVAL = [
        "`2You walk over to the big round fountain in the middle of %s`2. In its centre stands a stone shinobi with a forehead protector, its face worn smooth by many years of rain, water running from its outstretched hands into the basin below.`n`nAround the edge, villagers sit and talk, a chuunin eats his lunch out of a box, and two small Academy students dangle their feet in the chilly water, daring each other to go further in.`n`n",
        "`2The fountain in the square of %s`2 is busy, as always. Its water sparkles in the light, the bottom of the basin glitters with coins, and somebody has floated a paper boat with a genin's name on it across the surface.`n`nAn old woman is feeding the pigeons on the rim. A few shinobi lean against it, swapping stories from their last mission, and nobody seems in any hurry to leave.`n`n",
    ];
    public const NAV_HEADER = "The fountain";
    public const NAV_TOSS = "Toss a coin in";
    public const NAV_TALK = "Talk with the others";
    public const NAV_LISTEN = "Sit on the edge and listen";
    public const NAV_JUMP = "Jump in";
    public const NAV_BACK = "Back to the fountain";

    // A coin and a wish.

    public const TOSS = "`2You take a coin from your pouch, make a small wish, and toss it in.`n`n";
    public const TOSS_DONE = "`2You have already made your wish today. Wishing twice, the old people say, only confuses the fountain.";
    public const TOSS_POOR = "`2You search your pouch for a coin, but there is nothing in it. Not even a copper for a wish.";
    public const TOSS_JOURNEY = "`2The coin sinks to the bottom, and you wonder whether your wish for a safe journey will come true.";
    public const TOSS_ADVENTURE = "`2The coin sinks to the bottom and glimmers in the light. You hope your wish for a good mission will come true soon.";
    public const TOSS_THIEF_KID = "`2Before your coin can even reach the water, a small boy with a long scarf snatches it out of the air and runs away laughing. You could catch him easily, of course. You let him go.";
    public const TOSS_ENERGY = "`2As your coin touches the bottom, your mood lifts at the thought of your wish, and you feel full of new energy.`n`n`#You gain a forest fight!";
    public const TOSS_GEM = "`2As your coin drifts to the bottom, you catch a glimmer of something smaller in the water. You lean over a little, and there it is: a gem, lying among the coins!`n`n`2Casually, as if to take a sip of water, you bend down and snatch it up.";
    public const TOSS_THIEF_OLD = "`2Moments after your coin reaches the bottom, a bandy-legged old man rolls up his trousers, wades into the water and fishes it out again. He tips his hat to you as he goes.";
    /** %s: partner. */
    public const TOSS_LOVE = "`2The coin hits the water, and you wonder whether `5%s`2 will fall desperately in love with you, as you wished.";

    // Talk and listen.

    public const TALK = "`2You take a seat on the rim and join the people gathered around the fountain.`n`n";
    public const TALK_PROMPT = "Speak quietly";
    public const TALK_VERB = "says";
    /** @var list<string> */
    public const GOSSIP = [
        "`2Two chuunin are arguing about whose sensei is the strictest. The loser, it seems, has to run fifty laps around the village every morning. Neither of them wants to win.",
        "`2An old man is telling a group of children about the day he saw the Fourth Hokage, a long time ago. The story gets a little bigger every time he tells it, the woman next to you whispers.",
        "`2A merchant complains to anybody who will listen that the roads are full of bandits again, and that somebody should really send a team out to deal with them.",
        "`2A group of genin compare their bruises from the training grounds. The winner proudly shows off a bump the size of an egg.",
        "`2You hear somebody say that the ramen stand is giving a free egg with every bowl today. Half the people at the fountain get up and leave at once.",
        "`2Someone swears they saw a toad the size of a house in the forest last night. Nobody believes them. You are not so sure.",
    ];

    // A splash.

    public const JUMP = "`2Feeling particularly giddy, you kick off your sandals and climb into the fountain. For a few blissful moments you wade around in the cool water, kicking coins about and splashing the pigeons, until you decide it might be best to get out before anybody important notices.`n`n`2Seen or not, your spirits have been lifted by the little break.";
    public const JUMP_DONE = "`2You are still a little damp from your last splash. Once a day is enough, or people will start to talk.";
    public const JUMP_CLEAN = "`n`n`3You also feel slightly cleaner now.";
    /** %s: player. */
    public const JUMP_NEWS = "%s`2 was seen splashing around in the village fountain!";
}
