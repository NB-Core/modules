<?php

declare(strict_types=1);

namespace Shinobi\Modules\BijuuHunters;

/**
 * Everything the Bijuu Hunters event says.
 *
 * English source text for output(). Hidan's lines are in `4, Kakuzu's in
 * `q, narration in `&.
 */
final class Texts
{
    public const HIDAN = "`\$Hidan`0";
    public const KAKUZU = "`QKakuzu`0";

    // The meeting.

    /** @var list<string> */
    public const ARRIVAL = [
        "`&You dash through the forest when two voices ahead of you make you stop. Two men in long black cloaks with `\$red`& clouds stand in a clearing, arguing.`n`n`q\"I'm telling you, there are bounties around here. We could earn a little extra on the way.\"`& The first one's face is hidden behind a mask and a hood; only his green eyes are visible, and they are fixed on his partner.`n`n`4\"Who gives a damn about your money, Kakuzu? Can't we just finish one mission without a detour? One time?\"`& The other one has silver hair slicked back and a huge triple-bladed scythe on his back.`n`nA stick snaps under your foot. Both of them turn and look straight at you, and slowly, they begin to smile.`n`n`4\"Hey, kid. You decide. Who's right?\"",
        "`&The forest has gone very quiet. In a clearing ahead, a man with silver hair kneels in the middle of a circle drawn in blood, praying loudly to someone called Jashin, while his masked partner sits on a rock counting a thick stack of bills.`n`n`q\"Are you done yet? Time is money, Hidan.\"`n`4\"You don't interrupt a prayer, you heathen! Jashin-sama will...\"`n`n`&He stops in the middle of the sentence. Both of them are looking at you now.`n`n`4\"Well, well. A witness. Tell me, kid: who's right? Money, or faith?\"",
    ];
    public const NAV_TALK = "State your opinion";
    public const NAV_CHALLENGE = "Attack them";
    public const NAV_RUN = "Run";
    public const NAV_DEFEND = "Defend yourself";
    public const NAV_SLIP = "Slip away";
    /** %s: gold. */
    public const NAV_PAY = "Offer Kakuzu %s gold";
    public const NAV_HIDE = "Suppress your tailed beast's chakra";

    // Talking.

    public const TALK_JINCHURIKI = "`&Sweating a little, you say that extra money never hurt anyone. The one with the scythe turns away and swears loudly.`n`n`q\"Is that so? Interesting... because the money we're really after is sitting right inside you, Jinchuuriki.\"`n`n`&How could he know? Before you can move, the scythe is already off the silver-haired man's back and swinging towards you.";
    public const TALK_BOUNTY = "`&You agree with the one with the scythe: duty first. He seems very pleased with that and starts gloating at his partner.`n`nThe masked one is not listening. He is looking at a piece of paper. Slowly he turns it around: a wanted poster, with your face on it.`n`n`q\"Well, look at that. Duty and money, both at once.\"`n`n`&Black threads are already creeping across the ground towards you.";
    public const TALK_NEUTRAL = "`&You start to give your opinion, but they are arguing again before you have finished your first sentence. Within moments, the argument turns into a fight: the scythe flies, black threads lash out, and a tree falls over.`n`nYou decide to quietly step out of the way and hope they forget about you.";
    /** %s: gold. */
    public const TALK_PURSE = "`n`n`&As you sneak off, you notice a small pouch lying in the grass where Kakuzu was sitting. Nobody is looking. Inside are `^%s`& gold. You decide not to mention it to him.";

    // Running.

    public const RUN_JINCHURIKI = "`&You turn and run, but the two of them are suddenly in front of you, as if they had never moved.`n`n`q\"Going somewhere with that Bijuu?\"`& the masked one asks, while the other comes at you with his scythe.";
    public const RUN_BOUNTY = "`&You simply ignore them and start to walk off, when the masked one calls after you: `q\"Isn't this you?\"`n`n`&He holds up a wanted poster with your picture on it. Before you can react, black threads are winding around your ankles.";
    public const RUN_AWAY = "`&You leave their shouting behind you, and with a sigh of relief you return to the forest.";

    // Ways out.

    /** %s, %s: bounty, gold offered. */
    public const PAY = "`&You raise your hands. `&\"Wait! The bounty on my head is `^%s`& gold, right? I'll give you `^%s`&. Right now. No fight, no walk to the bounty office.\"`n`n`&The green eyes narrow. Then Kakuzu holds out his hand. He counts every coin, twice.`n`n`q\"A sensible one. Hidan, we're leaving.\"`n`4\"What?! You can't just let...\"`n`q\"I can. Money now beats money later.\"`n`n`&And just like that, they are gone. The bounty on your head is still there, of course.";
    public const PAY_POOR = "`&You reach for your purse, but there isn't nearly enough in it. Kakuzu looks at your coins, then at you. `q\"Don't waste my time.\"";
    public const HIDE_WIN = "`&You close your eyes and push the beast's chakra down, deeper and deeper, until even you can hardly feel it. When you open your eyes, Hidan is swinging his scythe at empty air, and Kakuzu is frowning.`n`n`q\"Strange. It was here a moment ago...\"`n`n`&You are already far away.";
    public const HIDE_LOSE = "`&You try to hide the beast's chakra, but it pushes back, angry and hot, and for a moment your eyes glow. Kakuzu laughs. `q\"There it is.\"";

    // When the tailed beast is sealed.

    /** @var list<string> */
    public const SEALED = [
        "`&You hear voices in the bushes and creep closer, your heart pounding... and find a couple of old men playing shogi. One of them is cheating.",
        "`&Two figures in black cloaks with red clouds walk past you on the path. One of them sniffs the air, frowns, and shrugs. With your tailed beast sealed, there is nothing in you for them to find. You let out a long breath.",
    ];

    // The fight.

    public const WEAPON_KAKUZU = "Black Threads";
    public const WEAPON_HIDAN = "Triple-Bladed Scythe";
    public const HIDAN_FALLS = "{badguy} no longer has Kakuzu to sew him back together, and falls apart.";

    /** %s: experience. */
    public const WIN = "`&You stand over the two of them, breathing hard. Kakuzu's hearts have stopped one after the other, and Hidan, in pieces, is still cursing you in the name of Jashin. You feel much stronger than before.`n`nYou gain `^%s`& experience points.";
    /** %s: gold. */
    public const WIN_PURSE = "`n`n`&Kakuzu's purse lies in the grass. There are `^%s`& gold in it. He won't be needing it any more.";
    /** %s: player. */
    public const NEWS_WIN = "`%%s`2 has defeated a couple of Akatsuki bounty hunters.";
    public const SEALED_NOW = "`&The last thing you remember is the two of them bending over you, Kakuzu's hand pressed against your stomach, and a blur of hand signs.`n`nWhen you wake up, you are alone in the forest, with a strange seal drawn on your skin. The beast inside you is silent. Its power has been sealed away.";
    /** %s: days. */
    public const SEALED_DAYS = "`n`n`&The seal will hold for `^%s`& days, unless someone who knows a thing or two about seals removes it.";
    public const BOUNTY_TAKEN = "`&You fall at the hands of Kakuzu and Hidan. Kakuzu carries your body to the nearest bounty office and collects the reward on your head, while Hidan complains the whole way.";
    /** %s: player. */
    public const NEWS_BOUNTY = "`%%s`6 had the bounty on their head collected by a couple of Akatsuki bounty hunters.";
    public const DEAD = "`\$Hidan`& and `QKakuzu`& 'play' with your body until they both get bored. Hidan calls it a sacrifice to Jashin. Kakuzu calls it a waste of time.";
    /** %s: player. */
    public const NEWS_DEAD = "`%%s`6 was found by some poor traveller in the forest, or what was left of them.";
    public const NAV_RETURN = "Return";
    public const NAV_SHADES = "Return to the Shades";

    // The seal.

    /** %s, %s: mount, days. */
    public const NEWDAY_SEALED = "`n`&Your %s`& has been sealed for %s more days, its power blocked, though the seal appears to be fading.`n";
    /** %s: turns. */
    public const NEWDAY_TURNS = "`nWith it sealed, you lose the %s extra turns it would give you.`n";
    /** %s: mount. */
    public const NEWDAY_LAST = "`nThe seal has almost completely faded. %s`&'s power should return soon.`n";

    public const ERO_TITLE = "Ero-Sennin";
    public const NAV_ERO = "Remove Seal";
    public const ERO = "`@The strange old man looks at the seal, grunts, and forms a few hand signs. Then he presses two fingers against your stomach, and the seal disappears.`n`n`2\"That should do it. By tomorrow you'll be fighting fit, and your buddy in there will be back.\"`@ He proudly announces this before returning to his peephole.";
    public const ERO_NO = "`@The old man shakes his head without looking up from his peephole. `2\"Not now, not for you. Come back when you've done something for me.\"";
    public const NAV_FOREST = "Back to the Forest";
}
