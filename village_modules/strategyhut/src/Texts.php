<?php

declare(strict_types=1);

namespace Shinobi\Modules\StrategyHut;

/**
 * Everything the Strategy Hut says.
 *
 * English source text for output(). The strategist's name is a setting;
 * his lines are in `^, narration in `&. In the advice, {deathoverlord}
 * is replaced with the game's death overlord.
 */
final class Texts
{
    public const TITLE = "`5`c`bThe Strategy Hut`b`c`n";
    public const NAV = " ?The Strategy Hut";
    public const NAV_HEADER = "The Hut";
    /** %s: price. */
    public const NAV_ASK = "Ask for advice (`^%s gold`0)";
    public const NAV_ASK_AGAIN = "Ask for more advice (`^%s gold`0)";
    public const NAV_SHOGI = "Play a game of shogi";
    public const NAV_BACK = "Back into the hut";

    /** @var list<string> %s: strategist. */
    public const ARRIVAL = [
        "`&The Strategy Hut is a low wooden building at the edge of the training grounds, with a sliding door that sticks and a veranda that catches the afternoon sun. Inside, scrolls are piled on every shelf: maps of old battlefields, mission reports with half the names blacked out, and a dog-eared copy of the Academy's survival manual.`n`n%s`& is lying on his back on the veranda, watching the clouds. A shogi board with an unfinished game sits next to him. He opens one eye.`n`n`^\"Let me guess. New genin, first missions, and everything is trying to kill you. What a drag.\"`&`n`nHe sits up with a sigh. `^\"Fine. For a small fee I'll tell you something useful. Pay attention, I'm only saying it once.\"",
        "`&You slide open the door of the Strategy Hut. Somebody has pinned a map of the surrounding forest to the wall, covered with red circles and arrows and the words \"`\$DO NOT GO HERE AT NIGHT`&\" in large letters.`n`n%s`& is sitting at a low table, playing shogi against himself, and losing. He looks up without much enthusiasm.`n`n`^\"Another one who wants to survive the forest? Good. The ones who don't ask are the ones I read about in the mission reports. A few coins, and I'll share what I know.\"",
    ];

    /** %s, %s: strategist, gold. */
    public const PAID = "`&You put `^%2\$s gold`& on the table. %1\$s`& sweeps it into a drawer without counting, leans back and thinks for a moment.`n`n";
    /** %s: strategist. */
    public const TOO_POOR = "`&You go through your pockets, but you don't have enough. %s`& watches you search for a while, then shrugs. `^\"Here's some free advice, then: missions pay. Come back when you've done a few.\"";
    public const AFTER = "`n`n`&You turn the advice over in your mind for a moment, then thank him and step back out into the sun.";

    /** @var list<string> */
    public const ADVICE = [
        "`^\"Heal often. Bank often. Dead shinobi don't spend gold, and robbers love full pockets.\"",
        "`^\"Balance. Your weapon and your armour should be close in level. All attack and no defence, and the first hit you take is your last.\"",
        "`^\"Don't rush. At the start, speed doesn't matter. Get strong first. Later it's different.\"",
        "`^\"That health bar is your life. When it gets into the yellow, heal. When it goes red, pray.\"",
        "`^\"In PvP, pick your targets carefully. If you're not sure, don't. Or you'll be explaining to {deathoverlord}`^ what happened.\"",
        "`^\"You don't always have to come back from the dead straight away. Sometimes it's smarter to save the favour for an emergency.\"",
        "`^\"If it's a bug, petition it. If you think something's unfair, petition it. The staff can't fix what they don't know about.\"",
        "`^\"A good offence isn't always a good defence. Even the strongest shinobi die in the forest.\"",
        "`^\"Confidence is fine. Attacking someone ten times your level is suicide. Check their bio first.\"",
        "`^\"Think before you act. Only a fool fights blindly. Every move should have a reason.\"",
        "`^\"The great beast is waiting. When you're ready, it will be too. Patience.\"",
        "`^\"Travelling between the villages is dangerous. Heal before you go, and don't carry more gold than you need.\"",
        "`^\"Talk to everyone. Visit every shop and every stall, in every village. Explore. The best tricks aren't in any scroll.\"",
        "`^\"Everybody dies a lot at the beginning. Me too. It doesn't matter. Getting up again does.\"",
        "`^\"When you face the great beast, be ready and fully healed. Or it will have you for lunch.\"",
        "`^\"Your companions and summons are an asset. Learn what they can do, and know their limits. And yours.\"",
        "`^\"There's no shame in running. A bruised ego heals faster than a visit to {deathoverlord}`^.\"",
        "`^\"Don't hang around all day at the end of a game day. There's no safe place, and someone is always looking for an easy target.\"",
        "`^\"If you can't come back from the dead today, log off and wait for the new day. You're already dead. It can't get worse.\"",
        "`^\"Treat other shinobi with respect. A wise one knows that friends today are the team that saves you tomorrow.\"",
        "`^\"Look after your companions and your mount. A hungry partner is no partner at all.\"",
        "`^\"Train your jutsu. Chakra is a resource like any other: run out at the wrong moment, and you're just a person with a pointy stick.\"",
        "`^\"Choose a clan. Alone you're a target. Together you're a village.\"",
        "`^\"Every enemy has a pattern. Watch for a few rounds before you commit. The first move is rarely the winning one.\"",
        "`^\"The shadows are your friend. So is the hospital. Don't be too proud for either.\"",
        "`^\"If a deal sounds too good to be true, it is. That goes double for strangers in the forest.\"",
        "`^\"Write down what works. Memory lies, and a notebook doesn't.\"",
        "`^\"Asuma used to say that the king is the one you protect, not the one who does the fighting. Think about who your king is.\"",
    ];

    // Shogi.

    /** %s: strategist. */
    public const SHOGI_START = "`&%s`& clears the pieces from the board and sets them up again, without looking, in a few quick moves. `^\"One game. No money, no excuses. If you beat me, I'll tell you how I'd fight you.\"`&`n`nHe pushes a pawn forward. Your turn. How do you play?";
    public const NAV_SHOGI_RUSH = "Charge with your rook";
    public const NAV_SHOGI_CASTLE = "Build a fortress around your king";
    public const NAV_SHOGI_TRAP = "Sacrifice a piece to set a trap";
    /** %s: strategist. */
    public const SHOGI_DONE = "`&%s`& yawns. `^\"One game a day. Thinking is exhausting. Come back tomorrow.\"";

    /** @var array<string, string> %s: strategist. */
    public const SHOGI_PLAY = [
        'rush' => "`&You push your rook straight up the board and start taking pieces. For a few moves it goes wonderfully. %s`& watches, chin in his hand, and moves a silver general one square.`n`n",
        'castle' => "`&You move your pieces into a tight, careful fortress around your king and wait for him to come to you. %s`& raises an eyebrow and starts moving pieces very slowly, one square at a time.`n`n",
        'trap' => "`&You leave a bishop exposed, as if by accident, with three pieces waiting for whoever takes it. %s`& looks at the bishop for a long time. Then at you. Then at the bishop again.`n`n",
    ];
    /** @var list<string> %s: strategist. */
    public const SHOGI_LOSE = [
        "`&Twelve moves later your king is surrounded on every side. You never saw it coming. %s`& leans back. `^\"You were thinking about your next move. I was thinking about my twentieth. That's the whole difference.\"",
        "`&He takes your best piece, and then puts it back on the board on his side, right where it hurts. Captured pieces fight for whoever holds them. You forgot. `^\"In shogi and in the forest,\"`& %s`& says, `^\"never feed the enemy.\"",
        "`&It is a long game. For a while you think you might actually have him. Then he moves a single pawn, and you realise that it's over, and has been for ten moves. %s`& almost smiles. `^\"Not bad. Really. Come back tomorrow.\"",
    ];
    /** %s: strategist. */
    public const SHOGI_WIN = "`&You don't quite believe it, but there it is: his king has nowhere left to go. %s`& stares at the board for a long time. Then he laughs, a short, surprised laugh.`n`n`^\"Huh. You actually did it. Alright, I keep my word.\"`& He tells you, in a few quiet sentences, exactly where your guard is weakest and how to cover it.`n`n`@You feel sharper. Your defence improves for a while.";
    public const BUFF = "`5Strategic Mind";
    public const BUFF_ROUND = "`5You read your enemy's next move before they make it.";
    public const BUFF_END = "`5The lesson from the shogi board fades from your mind.";
}
