<?php

declare(strict_types=1);

namespace Shinobi\Modules\TheGrinch;

/**
 * Everything the Grinch challenge says.
 *
 * English source text for output(). Where a text takes the Grinch's or the
 * elves' name, the name is a setting.
 */
final class Texts
{
    public const NAV_HEADER = "`2W`@inter `2C`@hallenge`0";
    public const NAV_HOF = "Grinch Hall of Fame";
    public const NAV_BACK_CHALLENGE = "Back to the challenge";

    /** @var array<string, list<array{0:string,1:int}>> The challenges by nav section: nav text and enemy type. */
    public const CHALLENGES = [
        'Fight' => [
            ["Tiny Challenge", 0],
            ["A Small Challenge", 1],
            ["`qA Challenge", 2],
            ["`\$A Big Challenge", 3],
            ["`4A Huge Challenge", 4],
            ["`\$S`4eriously?!?", 5],
            ["`\$S`4T`\$A`4H`\$P`4!!", 6],
        ],
        'More Fights' => [
            ["`2M`@onster `2M`@ode", 7],
            ["`\$M`4ega `2M`@ode", 8],
            ["`qU`tltimate `2M`@ode", 9],
        ],
        'Hardcore' => [
            ["`xY`lou`v wanted it", 10],
            ["`6P`tlease `6d`ton't `6c`tlick", 11],
        ],
    ];

    public const TITLE = "`c`7`bThe Snowbound Woods`b`0`c";
    /** @var list<string> %s: the Grinch. */
    public const ARRIVAL = [
        "`2Deep in the forest the snow lies untouched, and the trees are hung with ornaments that have been smashed, one by one. Somewhere ahead you hear a nasty little laugh. %s`2 is here, and so are the elves who serve him: small, furry, sharp of tooth, and every one of them in a very bad mood about Christmas.`n`nYou move as silently as you can through the drifts, wary of stepping on a candy cane or one of the many broken toys that litter the ground, lest you betray yourself to one of the creatures that wander these woods.`n",
        "`2The path into the snowbound woods is littered with torn wrapping paper and trampled tinsel. On a tree, someone has nailed a sign: \"`\$NO CHRISTMAS HERE`2\". Behind it, between the frozen trunks, small red eyes are watching you.`n`nThis is where %s`2 hides from the joy of the season, and from here he sends out his elves to steal it from everyone else. You tighten your grip on your weapon.`n",
    ];

    public const TOO_TIRED = "`\$`bYou are too tired to search the forest any longer today. Perhaps tomorrow you will have more energy.`b`0";
    public const ENOUGH_TODAY = "`2You think you had enough Christmas hate fighting for today....";
    public const CLOSED = "`2The snowbound woods are quiet. Whatever lived here has gone to sulk somewhere else until next winter.";

    public const FLED = "`c`b`&You have successfully fled your opponent!`0`b`c`n";
    public const NOT_FLED = "`c`b`\$You failed to flee your opponent!`0`b`c";

    /** %s: the Grinch. */
    public const DEFEAT = "%s`2 leaves your battered body on the forest floor, having other things to accomplish now...`n";
    /** %s, %s: player, the Grinch. */
    public const NEWS_DEFEAT = "`2%s`2 lost against %s`2 in the forest.";
    /** %s, %s: enemy, item. */
    public const LOOT = "`^Searching the lifeless body of `4%s`^ you find a `7%s`^.`n";

    // The elves (minions), by enemy type. %s: the elves' name from the settings.
    public const ELF = "%s";
    public const ELF_MEAN = "`\$Mean %s";
    public const ELF_VERY_MEAN = "`4V`)ery `\$Mean %s";
    public const ELF_HARMLESS = "`4H`)armless %s";
    public const ELF_BACKUP = "`tB`yackup %s `2for Demigods";
    public const WEAPON_ELF = "Sweet fangs and micro-claws";
    public const WEAPON_ELF_MEAN = "Tiny fangs and little claws";
    public const WEAPON_ELF_VERY_MEAN = "A little red flute with claws";
    public const WEAPON_ELF_HARMLESS = "Tiny tail";

    // When no Grinch creature exists for a challenge: the beast itself.
    /** %s: the beast. */
    public const BEAST = "%s";
    public const BEAST_EMPOWERED = "`\$Empowered %s";
    public const WEAPON_BEAST = "Piercing Fangs and Enormous Claws";
    public const WEAPON_BEAST_EMPOWERED = "Mauling Fangs and Enormous Claws";

    // Hall of fame.

    /** %s: the Grinch. */
    public const HOF_TITLE = "`c`@Top %s`4-Slayers:`0`c`n";
    /** %s, %s, %s, %s: enemy, best player, victories, own victories. */
    public const HOF_LINE = "`)%s`4: `)%s`2 (%s victories) (You: %s)`n";
    public const HOF_NOBODY = "Nobody yet";
    public const HOF_UNKNOWN = "`)???";
    /** %s: the Grinch. */
    public const VILLAGE = "`n`2Word in the streets is that %s`2 is loose in the snowbound woods, stealing Christmas from anyone foolish enough to go looking.`n";
}
