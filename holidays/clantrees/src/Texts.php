<?php

declare(strict_types=1);

namespace Shinobi\Modules\ClanTrees;

/**
 * Everything the clan Christmas trees say.
 *
 * English source text for output(). %s is usually the salesman's name, from
 * the settings; his grumpy, money-loving assistant is Kakuzu.
 */
final class Texts
{
    public const VILLAGE = "`n`&Outside the Clan Halls, %s`& is selling fir trees to the clans, while his assistant guards the cash box with a look that could freeze a river.`n";
    /** %s: clan name. */
    public const VILLAGE_BEST = "`nThe biggest, sparkliest, twinkliest tree of the winter stands in the hall of the %s`& clan!`n";
    public const VILLAGE_TREES = "`nMany of the clan hall windows glow with beautifully decorated trees.`n";

    // In the clan hall.

    public const HALL_NO_TREE = "`@Your clan hall seems somewhat bare this winter. A tree would cheer things up no end!`0`n`n";
    public const HALL_NO_TREE_LEADER = "`@Just outside you can see %s`@ offering trees. It could be good for morale to get one for your members.`0`n`n";
    public const HALL_BEST = "`@Your tree is the finest in all the Clan Halls this winter!`0`n";
    public const HALL_SPIRIT = "`@Looking at it, your spirit lifts.`0`n`n";
    public const HALL_MORE = "`@It could still be brighter, though. A little time, gold or gems would liven it up.`0`n`n";

    /**
     * How the tree looks, by its points: [lowest points, description].
     *
     * @var list<array{0:int,1:string}>
     */
    public const LOOKS = [
        [200, "`@In the middle of the hall towers your clan's tree, a blaze of light from root to crown. Ten thousand crystal baubles catch every lamp, tinsel hangs in silver waterfalls, and somebody has managed to put a glowing star on top that is almost too bright to look at. Visitors from other clans come just to stand in front of it with their mouths open.`0`n`n"],
        [100, "`@Your clan's tree stands proudly by the entrance, thick with tinsel and heavy with baubles. Little paper lanterns glow between the branches, and the whole hall smells of pine. Members stop on their way past to straighten an ornament or add one of their own.`0`n`n"],
        [50, "`@Your clan's tree is coming along nicely. There is tinsel on most of the branches now, a handful of baubles, and a paper star on top that a genin folded with great care, if not great skill.`0`n`n"],
        [20, "`@Your clan's tree stands by the entrance, green and fresh, with a few strands of tinsel and a lonely bauble or two. It is a start.`0`n`n"],
        [0, "`@Your clan's tree stands by the entrance, still almost bare. It is waiting for someone to love it.`0`n`n"],
    ];
    /** %s, %s, %s, %s: points, turns, gold, gems. */
    public const TREE_STATS = "`7(Tree splendour: `&%s`7 points. The clan has put in `&%s`7 turns, `^%s gold`7 of tinsel and `%%s gems`7 of baubles.)`0`n`n";

    public const NAV_HEADER = "Christmas Tree";
    public const NAV_BUY = "Buy a Tree";
    public const NAV_TIME = "Work on the Tree";
    public const NAV_TINSEL = "Buy Tinsel";
    public const NAV_BAUBLES = "Buy Baubles";
    public const NAV_RANKING = "Admire the other halls' trees";
    public const NAV_BACK_CLAN = "Clan Options";
    public const NAV_RETURN = "Return to the Hall";
    public const NAV_LEAVE = "Leave the Enclosure";

    // Buying a tree.

    public const SHOP = "`@You decide that your clan does indeed need cheering up this winter, and go outside to talk to %s`@ about a tree.`n`nHe is busy with another customer, so you look over his stock. Near the entrance stands a row of small, scraggly trees, cheap but sad. Close to %s`@ himself are some nicely sized, nicely priced trees that would look lovely by the door of your hall. And at the back of the enclosure, guarded by his assistant `\$Kakuzu`@, who is counting coins and does not look up, stand two enormous trees, so tall their tips are lost in the falling snow.`n`nThe salesman finishes with his customer and turns to you, rubbing his hands. Which will it be?`n`n";
    public const SHOP_SMALL = "`&Small, Scraggly Tree - `^5,000 Gold `&and `%5 Gems`&.`n";
    public const SHOP_MEDIUM = "`@Medium, Pretty Tree - `^10,000 Gold `@and `%10 Gems`@.`n";
    public const SHOP_GRAND = "`2Large, Grand Tree - `^25,000 Gold `2and `%25 Gems`2.`n`n";
    public const SHOP_TOO_POOR = "`@You realise you do not have enough on you for even the smallest tree, and vow to return with more money. `\$Kakuzu`@ watches you go with deep contempt.";
    public const BUY_SMALL = "Buy a Scraggly Tree";
    public const BUY_MEDIUM = "Buy a Pretty Tree";
    public const BUY_GRAND = "Buy a Grand Tree";

    public const BOUGHT_SMALL = "`@%s`@ nods and waves to `\$Kakuzu`@, who glares at him dangerously, then picks up the little tree one-handed and stomps off to your clan hall. He drops it into a pot by the entrance. It looks very plain, and even the youngest genin of your clan are taller than it, but a sense of holiday spirit spreads through the hall all the same.`n`nNow it needs decorating: tinsel and baubles, and a good deal of time spent arranging them.";
    public const BOUGHT_MEDIUM = "`@%s`@ nods and waves to `\$Kakuzu`@, who heaves the tree onto his shoulder with a long-suffering sigh. He struggles a little at the door, says something you choose not to hear, and finally sets it in a big pot beside the entrance. It is a fine tree, and a sense of holiday spirit spreads through the hall.`n`nNow it needs decorating: tinsel and baubles, and a good deal of time spent arranging them.";
    public const BOUGHT_GRAND = "`@%s`@ nods and waves to `\$Kakuzu`@, who takes one look at the enormous tree and goes to fetch help. Five minutes later, a dozen sweating genin have given up: it is far too heavy. Just then a jounin strolls by with her students, lecturing them on chakra control, and within minutes the whole squad is walking the tree up the walls and across the roof and lowering it through the skylight of your hall, for practice. Its topmost branch scrapes the ceiling, and the holiday spirit fills every corner.`n`nNow it needs decorating: tinsel and baubles, and a good deal of time spent arranging them.";

    // Decorating.

    public const TIME = "`&Your tree looks nice, but you reckon a while spent rearranging the ornaments could make it look a lot better. Then again, you only have so much time in a day...`n`nHow many turns do you want to spend on the tree?";
    public const TINSEL = "`^Your tree could do with more tinsel. Tinsel is expensive, though, and `7Abigail`^ charges through the nose for it, what with the snow on the trade roads.`n`nHow much gold can you spare?";
    public const BAUBLES = "`%Your tree could do with more baubles. `7Abigail`% has some lovely ones, hand-blown glass from the Land of Snow, or so she says. She only takes gems for them.`n`nHow many gems can you spare?";
    public const FORM_TIME = "Turns to spend";
    public const FORM_GOLD = "Gold to spend";
    public const FORM_GEMS = "Gems to invest";
    public const BUTTON_TIME = "Spend Turns";
    public const BUTTON_GOLD = "Spend Gold";
    public const BUTTON_GEMS = "Invest Gems";

    public const NOTHING = "`&You decide not to spend anything on the tree right now.";
    public const TOO_LITTLE_TIME = "`&You realise you do not have that much time to spend, and curse your lack of arithmetic.";
    public const TOO_LITTLE_GOLD = "`7Abigail`^ looks at the small pile of gold in your hand. How are you going to buy that much tinsel without enough money?";
    public const TOO_LITTLE_GEMS = "`7Abigail`% looks at the few gems in your hand. How are you going to buy that many baubles without enough gems?";
    public const DONE_TIME = "`&You spend a good while among the branches, untangling tinsel, moving baubles to where they catch the light, and arguing with a clan mate about where the star should go. When you step back, the tree looks clearly better.`n`n`@You return to the main hall, your spirits lifted.";
    public const DONE_GOLD = "`7Abigail`^ takes your gold, and within the hour a crate of tinsel arrives at your clan hall, gold and silver and red. You hope someone has the time to arrange it properly.";
    public const DONE_GEMS = "`7Abigail`% takes your gems and hands you a box of baubles, each one wrapped in straw. You hang them on the branches, where they glow like little moons.";
    public const NEW_BEST = "`n`n`@Your contribution has made your tree the finest in all the Clan Halls! The twinkliness is nearly blinding!";

    // The ranking.

    public const RANKING_TITLE = "`c`b`@The Trees of the Clan Halls`b`c`n";
    public const RANKING_INTRO = "`7You take a walk along the Clan Halls, peering through windows and open doors at the other clans' trees.`n`n";
    /** %s, %s, %s: rank, clan name, points. */
    public const RANKING_ROW = "`7%s. %s`7 - `&%s`7 points`n";
    public const RANKING_EMPTY = "`7No clan has a tree yet this winter.`n";

    public const BUFF = "`@Christmas Spirit";
    public const BUFF_ROUND = "You feel the blessing of the season!";
    public const BUFF_END = "The cold around saps your joy.";
}
