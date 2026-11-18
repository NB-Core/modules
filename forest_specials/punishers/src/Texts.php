<?php

declare(strict_types=1);

namespace Shinobi\Modules\Punishers;

/**
 * Everything the forest patrols say.
 *
 * English source text for output(). Two factions patrol the woods: the
 * village's ANBU, who hunt evil shinobi, and the Hunter-nin of the Bloody
 * Mist, who hunt the good ones. Faction texts are arrays keyed 'anbu' and
 * 'hunter'. Narration is `3, patrol lines `&.
 */
final class Texts
{
    public const BOY = "buddy";
    public const GIRL = "lassie";

    // Stop!

    /** @var list<string> */
    public const STOP = [
        "`3You are on your way through the forest when a voice rings out from the trees: \"`\$STOP`&, don't move!`3\"`n`nIt is not loud, but there is a weight behind it, the kind of voice that is used to being obeyed. Somewhere above you, a branch creaks. Then another, on the other side.`n`nWhat do you do?",
        "`3The birds have gone quiet. You notice it a moment too late: the forest around you holds its breath, and then a masked figure is crouching on the path in front of you, as if it had always been there. \"`\$Stop right there`&.\"`3`n`nYou count three more shapes in the branches. Maybe four.`n`nWhat do you do?",
    ];
    public const NAV_STAND = "Stand and wait";
    public const NAV_RUN = "Run away";

    /** %s: faction. */
    public const SURROUNDED = "`3Within moments, you are surrounded by members of the `^%s`3.`n`n";
    /** %s: chief. */
    public const CHIEF = "`3Oh no. You seem to have attracted the very best of them. They are led by the famous %s`3, who is looking at you with no warmth at all.`n`n";

    /** @var array<string, string> %s: buddy/lassie. */
    public const HOSTILE = [
        'anbu' => "`&\"Well, well. What do we have here? An evil %s, sneaking through our woods. The village knows your name, and it doesn't like what it hears. You'll come with us, one way or another.\"`3",
        'hunter' => "`&\"Look at this. A good little %s, walking through `bour`b woods as if they belonged to you. The Mist has a word for people like you: prey.\"`3",
    ];
    /** @var array<string, string> %s, %s: faction, buddy/lassie. */
    public const NEUTRAL = [
        'anbu' => "`&\"Greetings. You are not known to us. We are the local `^%s`&, on patrol. Keep your nose clean, %s.\"`3`n`nAnd just as they came, they are gone: fast, and almost without a sound.",
        'hunter' => "`&\"We `^%s`& have no business with you. Yet. Don't make trouble in our village, %s.\"`3`n`nThe masks turn away, and the mist swallows them.",
    ];
    /** @var array<string, string> */
    public const FRIENDLY = [
        'anbu' => "`&\"Greetings! Word of your good deeds has reached us. It's good to have someone like you out here. Bad people are always lurking in the deep woods.\"`3",
        'hunter' => "`&\"Well, well. Someone just as wicked as we are! We've heard about your work, all that lovely mayhem. Good people are always lurking in these woods, you know. Be careful.\"`3",
    ];
    /** @var array<string, string> */
    public const HEALED = [
        'anbu' => "`n`n`&\"You're hurt. Hold still, our medic will see to it.\"`3 A masked woman lays her glowing hands on your wounds. `2Your health has been fully restored!`3",
        'hunter' => "`n`n`&\"You're hurt. Our medic will treat you. He enjoys it.\"`3 It hurts a great deal more than it should. `2Still, your health has been fully restored!`3",
    ];
    public const NAV_CONTINUE = "Continue your journey";

    // Choices.

    public const NAV_FIGHT = "Get combat-ready";
    /** %s: gold. */
    public const NAV_BRIBE = "Offer them %s gold";
    public const NAV_TALK = "Try to talk your way out";
    public const NAV_SURRENDER = "Surrender";

    /** %s, %s: gold, faction. */
    public const BRIBE = "`3You hold out a purse with `^%s`3 gold. The masks look at each other. Then one of them takes the purse, weighs it in a gloved hand, and makes it disappear.`n`n`&\"We never saw you.\"`3`n`nThe `^%s`3 vanish into the trees.";
    /** %s: chief. */
    public const BRIBE_CHIEF = "`3You hold out a purse of gold. %s`3 looks at it, then at you, and you have the distinct feeling that you have just made things worse.`n`n`&\"No.\"";
    public const BRIBE_POOR = "`3You reach for your purse and realise that it isn't nearly heavy enough. The masks watch you count. Nobody laughs. That is somehow worse.";
    /** @var list<string> */
    public const TALK_WIN = [
        "`3You talk. You talk fast, about a sick grandmother, a very urgent mission, a terrible misunderstanding and a cousin in the Hokage's office. After a while, one of the masks raises a hand. `&\"Enough. Just go.\"`3`n`nYou go.",
        "`3You keep your voice calm, explain where you are going and why, and look the leader straight in the mask while you do it. There is a long silence. `&\"Fine. But we'll be watching you.\"`3 And then they are gone.",
    ];
    /** @var list<string> */
    public const TALK_LOSE = [
        "`3You start explaining. About halfway through your second sentence, the leader draws a kunai. `&\"We've heard that one before.\"",
        "`3You open your mouth, and a tag of paper sticks to it. A silence seal. Well, that settles that. Weapons are drawn all around you.",
    ];
    /** %s: gold lost. */
    public const SURRENDER = "`3You raise your hands slowly and kneel down in the damp leaves. They search you thoroughly, take `^%s`3 gold as a \"fine\", bind your hands, and march you a long way through the forest before they cut you loose again.`n`n`&\"Next time we won't be so kind.\"`3`n`nYou have lost a lot of time.";
    public const SURRENDER_FREE = "`3You raise your hands slowly and kneel down in the damp leaves. They search you thoroughly, find nothing worth taking, bind your hands, and march you a long way through the forest before they cut you loose again.`n`n`&\"Next time we won't be so kind.\"`3`n`nYou have lost a lot of time.";
    public const SURRENDER_TURN = " `\$You lose one forest fight.`3";

    // Running.

    public const RUN = "`3\"`&Hey, you! Stay where you are!`3\" The voice is already fading behind you as you run.`n`n";
    public const RUN_AWAY = "`3You run as fast as you can, trusting your legs and every trick you ever learned at the Academy. Leaves, branches, a river, another river... and at last you are alone in the woods again.";
    public const RUN_TIME = " Shaking them off has cost you enough time for one forest fight.";
    public const RUN_CAUGHT = "`3You are fast. They are faster. A shadow drops in front of you, another lands behind you, and that is the end of your escape.`n`n";

    // The fight.

    /** %s: faction. */
    public const CREATURE = "%s`0 members";
    /** %s, %s: chief, faction members. */
    public const CREATURE_CHIEF = "%s`0 and the %s";
    public const WEAPON = "many deadly weapons";
    /** @var array<string, string> */
    public const WEAPON_CHIEF = [
        'anbu' => "a Mangekyou Sharingan and many deadly weapons",
        'hunter' => "two vicious whips and many deadly weapons",
    ];
    /** @var array<string, string> */
    public const KUNAI = [
        'anbu' => "`)A kunai hits you for {damage} damage!",
        'hunter' => "`)A poisoned kunai hits you for {damage} damage!",
    ];
    public const KUNAI_NAME = "`qKunai!";
    public const NAV_ESCAPE = "Escape to the village (staff)";
    public const ESCAPE = "With the power of the staff, you simply step out of the fight.";

    /** %s: faction. */
    public const WIN = "`n`n`@The `^%s`@ lie defeated around you. You survived... somehow.";
    /** %s: chief. */
    public const WIN_CHIEF = "`n`^%s`^ is nowhere to be seen. Gone to see to their wounds, no doubt. You hope they don't remember your face.";
    /** %s, %s, %s: player, faction, place. */
    public const NEWS_WIN = "%s`^ survived an encounter with the `^%s`^ in the woods near %s.";
    /** %s, %s, %s: player, opponent, place. */
    public const NEWS_WIN_CHIEF = "%s`^ survived an encounter with %s`^ in the woods near %s.";
    /** %s: faction. */
    public const DEAD = "`n`n`@You are dead, struck down by the `^%s`@.`n";
    /** %s, %s: experience percent, gold percent. */
    public const DEAD_LOSS = "`@You lose `^%s%%`@ of your experience and `^%s%%`@ of the gold you were carrying.";
    /** %s, %s, %s: player, faction, place. */
    public const NEWS_DEAD = "%s`^ was killed by the `^%s`^ in the woods near %s.";
    /** %s, %s, %s: player, opponent, place. */
    public const NEWS_DEAD_CHIEF = "%s`^ was killed by %s`^ in the woods near %s.";
    public const NAV_RETURN = "Return";
    public const NAV_SHADES = "Return to the Shades";

    /** %s: faction. */
    public const LEAVE = "`3You continue on your way, and soon you have almost forgotten the `^%s`3.`n`n";
}
