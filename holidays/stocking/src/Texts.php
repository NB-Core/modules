<?php

declare(strict_types=1);

namespace Shinobi\Modules\Stocking;

/**
 * Everything the inn's fireplace says through the year and at Christmas.
 *
 * English source text for output(). Where an entry is a list, one variant is
 * picked at random. In the scenes, the first %s is the barmaid's name and the
 * second the bard's, as set in the game settings.
 */
final class Texts
{
    public const TITLE = "`c`b`\$The Fireplace`b`c`n";
    public const INN_DESC = "`n`6You see an inviting fireplace, and some people gathered around.`n";
    public const INN_DESC_SEASON = "`n`6The fireplace has been decked with pine garlands and paper snowflakes for the season, and the whole inn smells of pine and cinnamon.`n";
    public const NAV_HEADER = "Fireplace";
    public const NAV_HEADER_SEASON = "`\$C`%hristmas `\$F`%ireplace`0";
    public const NAV_WALK = "Walk over to the Fireplace";
    public const NAV_LEAVE = "Leave Fireplace";
    public const NAV_INN = "R?Return to Inn";
    public const TALK_PROMPT = "Chat around the fire";
    public const TALK_VERB = "chats";

    /** @var list<string> Any time of the year. */
    public const ORDINARY = [
        "`&You stroll over to the large fireplace of grey river stone. A pot of tea hangs from a hook above the flames, and an old cat is asleep on the warm hearthstones, belly up, utterly without dignity. A few shinobi sit on the low benches, drying their sandals and swapping mission stories.",
        "`&The fire crackles and pops. %s`& comes over with an armful of logs, feeds the flames and wipes her hands on her apron. `#\"Sit, warm up,\"`& she says. `#\"Whatever is chasing you can wait until your toes have thawed.\"",
        "`&Someone has left a half-finished game of shogi on the little table by the fire. Two genin are studying it with great seriousness, arguing in whispers about whether the old man who left it was a genius or simply very bad at shogi.",
    ];

    /** @var list<string> From about four weeks before Christmas. */
    public const ADVENT = [
        "`&The fireplace has been decorated for the season. Garlands of pine and red berries hang along the mantle, and paper snowflakes, cut by the children of the village, are stuck to every beam. %s`& and %s`& are wrestling a fir tree into a pot in the corner. The tree is winning.",
        "`&Someone has hung little lanterns of red paper all around the fireplace, and the light dances on the walls. %s`& is teaching a group of genin to make paper cranes, and the floor is slowly disappearing under a drift of crooked, enthusiastic birds.",
        "`&By the fire, %2\$s`& is playing an old winter song on his shamisen, and half the inn is singing along, mostly in tune. Snow is falling past the windows. For a moment nobody is a shinobi or a merchant or a stranger; everybody is just warm.",
    ];
    /** %s: days until Christmas. */
    public const COUNTDOWN = "`n`n`b`2Only %s days until Christmas!`b";

    /** @var list<string> In the last week before Christmas Eve. */
    public const STOCKINGS = [
        "`&%s`& is hanging stockings on the mantle, red and white, one for every guest who has slept under this roof this year. Each has a name stitched on it in gold thread. You look along the row, and there it is: your name, a little crooked, but definitely yours.",
        "`&The stockings hang in a long row above the fire, still flat and empty, swaying gently in the warm air rising from the flames. Children keep sneaking up to touch them, as if to check that they are still there.",
    ];

    public const EVE = "`^It's Christmas Eve!`n`n`&The stockings hang from the mantle with care, still as empty as they were yesterday, but tonight something is different in the air. The inn is packed. %s`& sings the old songs by the fire, and the whole room joins in. Outside, the snow falls thick and silent on the rooftops of the village. Maybe, just maybe, there will be a little something in the stockings tomorrow.";

    /** @var list<array{0:string,1:string,2:string,3:string}> Drinks: [intro (%s barmaid), buff name, round message, wear-off]. */
    public const DRINKS = [
        ["`n`n%s`& hands you a steaming cup of egg nog, thick and sweet and dusted with nutmeg. You drink it far too fast.", "`2Egg Nog Dementia`0", "`2You become a whirling dervish with your egg nog dementia!`0", "`4You could use more egg nog, but there is none to be found!`0"],
        ["`n`n%s`& presses a bowl of warm amazake into your hands, sweet rice wine that tastes of winter mornings. Warmth spreads all the way to your toes.", "`2Amazake Glow`0", "`2The warm rice wine puts fire in your fists!`0", "`4The amazake glow fades.`0"],
        ["`n`n%s`& gives you a mug of hot cocoa with a small mountain of cream on top. You end up with most of it on your nose.", "`2Hot Cocoa Melee`0", "`2The heat from the cocoa adds fire to your fight!`0", "`4You come down from your hot cocoa high.`0"],
        ["`n`n%s`& serves you a cup of mulled wine with an orange slice and a stick of cinnamon. It smells like every good winter you have ever had.", "`2Mulled Courage`0", "`2Mulled wine and courage flow through your veins!`0", "`4The last of the mulled wine wears off.`0"],
    ];
    public const ALREADY_DRANK = "`n`n`&You stand for a while by the fire, enjoying the warmth and the singing, before wandering away.";

    // Christmas morning: the stockings.

    public const FIND_STOCKING = "`&You glance over the stockings hanging above the fireplace. Overnight, every single one of them has filled up, round and lumpy. You search the row for the one with %s`& stitched upon it. Ah! There it is!`n`n`^You reach inside and pull out your gift...`n`n";
    public const ALREADY_OPENED = "`&You check your stocking, but it is as empty as you left it, apart from a single, slightly squashed tangerine. You eat it anyway.";
    public const MERRY = "`n`n`\$M`@e`\$r`@r`\$y `\$C`@h`\$r`@i`\$s`@t`\$m`@a`\$s`@!`0";

    /**
     * The gifts: [type, amount, text]. Types: turns, gems, gold, buff, coal.
     *
     * @var list<array{0:string,1:int,2:string}>
     */
    public const GIFTS = [
        ['turns', 2, "`&A pair of brand-new running sandals, with a little card: `#\"For the road. Do not waste them on errands.\"`& You feel ready to take on the whole forest. (`@%s`& forest fights)"],
        ['gems', 2, "`&A small velvet pouch, tied with gold string. Inside, two `%gems`& glitter in the firelight like frozen stars. (`%%s gems`&)"],
        ['gold', 200, "`&A heavy little purse embroidered with a pine tree. It jingles beautifully. (`^%s gold`&)"],
        ['gems', 3, "`&A wooden box carved with the symbol of the Leaf, and inside, nestled in straw, three perfect `%gems`&. Whoever packed this cared a great deal. (`%%s gems`&)"],
        ['gold', 300, "`&A hand-knitted scarf, very long and very striped, with a purse of coins wrapped up inside it. (`^%s gold`&)"],
        ['buff', 0, "`&A whole basket of cheer: tangerines, honey cakes, a bottle of plum wine and a hand-written card that just says `#\"You did well this year.\"`& You feel unstoppable."],
        ['turns', 3, "`&A scroll with a summoning seal on it. When you unroll it, a very small toad pops out, salutes, says `#\"Merry Christmas, kid,\"`& and vanishes in a puff of smoke, leaving behind a feeling of enormous energy. (`@%s`& forest fights)"],
        ['gold', 500, "`&An envelope, sealed with red wax. Inside is a short note in very neat handwriting: `#\"For the one who always pays for everyone else's ramen.\"`& And money. Quite a lot of money. (`^%s gold`&)"],
        ['coal', 0, "`&A lump of coal. Someone thinks you have not been a deserving person this year...`n`nBut the coal is still warm, and as you hold it, the warmth spreads up your arms. Out of sheer spite, you decide to have a very good next year."],
    ];
    public const CHEER_BUFF = "`2Holiday Cheer`0";
    public const CHEER_ROUND = "`2Your joy increases your fighting abilities!`0";
    public const CHEER_END = "`4Your happiness fades.`0";
    public const SPITE_BUFF = "`2Spite`0";
    public const SPITE_ROUND = "`2You fight like someone with something to prove.`0";
    public const SPITE_END = "`4Your spite cools.`0";

    /** @var list<string> After Christmas. */
    public const AFTER = [
        "`&Christmas has come and gone. Bits of ribbon and wrapping paper are still tucked into corners of the inn, and most of the empty stockings have been taken down from the mantle. %s`& is sweeping up pine needles, humming the songs from the other night.",
        "`&The tree in the corner is starting to droop, and its needles fall with every footstep. By the fire, the old men are already arguing about whose present was best, and whether the toad was real.",
    ];
}
