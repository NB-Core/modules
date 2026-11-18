<?php

declare(strict_types=1);

namespace Shinobi\Modules\Snowball;

/**
 * Everything the Snowy Banks say.
 *
 * English source text for output(), which passes it through the translator.
 * Where an entry is a list, one variant is picked at random.
 */
final class Texts
{
    public const TITLE = "`&`c`bThe Snowy Banks`b`c`n";

    public const CLOSED = "`7You walk towards the snowy banks, but the snow has melted away, leaving nothing but cold, wet ground and a single, very sad carrot that was once somebody's snowman's nose.`n`nCome back when the snow returns.";
    public const LENIENCY = "`7Above the gardens, fireworks burst in red and gold. The snow is already turning grey and slushy at the edges: soon the great snowball war will be over for another year.`n`n";

    /** @var list<string> */
    public const ARRIVAL = [
        "`7Along the edge of the gardens, the snow has been shovelled into long white banks, chest high, and behind them a war is raging. Genin crouch in snow trenches, packing ammunition with deadly seriousness. A chuunin vaults over a bank, takes three snowballs to the face, and goes down laughing. Somewhere, a sensei is shouting \"`#No ninjutsu! Taijutsu only! I saw that fire jutsu, Hiroshi!`7\"",
        "`7The Snowy Banks are a battlefield. Forts of packed snow line the gardens, each with a little flag stuck on top: a leaf, a fan, a gourd, a squiggle that might be a frog. Snowballs fly in every direction, and the air rings with shrieks, laughter and the occasional dramatic \"`#I'm hit! Tell my mother I was brave!`7\"",
        "`7It is quieter at the banks than you expected. Then you notice them: a dozen shinobi lying perfectly still in the snow, camouflaged in white, each with a snowball in hand, each waiting for someone to walk into range. You have just walked into range.",
    ];
    /** %s: hits this season. */
    public const MY_HITS = "`n`n`7This winter you have hit `&%s`7 shinobi with snowballs";
    /** %s: times hit this season. */
    public const MY_TAKEN = "`7, and been hit `&%s`7 times yourself.";
    /** %s, %s: throws left, throws per day. */
    public const THROWS_LEFT = "`n`7Your hands can take `&%s`7 more of `&%s`7 throws today.";
    public const TOO_COLD = "`n`n`7Your fingers are so numb you can no longer feel them. No more snowballs for you today. You tuck your hands into your armpits and watch the others from behind a bank.";

    public const BOARD_TITLE = "`n`n`b`&Snowball champions of this winter`b`n";
    /** %s, %s, %s: rank, name, hits. */
    public const BOARD_ROW = "`7%s. %s`7 - `^%s`7 hits`n";
    public const BOARD_EMPTY = "`7Nobody has landed a snowball yet this winter. Be the first!`n";

    public const NAV_BACK = "Return to the Gardens";
    public const NAV_ACTIONS = "Snowball war";
    public const NAV_THROW = "Throw a snowball at someone";
    public const NAV_NPC = "Ambush a passer-by";
    public const TITLE_NAV = "Back to the Snowy Banks";
    public const NAV_TALK = "Shout across the banks";
    public const TALK_PROMPT = "Shout";
    public const TALK_VERB = "shouts";

    // Choosing a target.

    public const PICK = "`7You crouch behind the bank and pack a snowball, hard and round. Peering over the top, you look for a target among the shinobi in the gardens right now.`n`n";
    public const NOBODY = "`7You peer over the bank, snowball ready, but there is nobody else around. You throw it at a tree instead. The tree does not react. Trees never do.";
    public const LABEL_TARGET = "Target";
    public const LABEL_STYLE = "How do you throw?";
    public const STYLE_QUICK = "Quick throw (1 throw)";
    public const STYLE_LEAP = "Leaping volley: better aim, but costs 2 throws";
    public const BUTTON_THROW = "Throw!";
    public const EXPIRED = "`7Your snowball has melted in your hand while you were aiming. Pack a new one.";
    public const NOT_ENOUGH = "`7You do not have enough strength left in your hands for a leaping volley today.";
    public const GONE = "`7You look around for your target, but they have left the gardens.";

    // Throwing at a player. %s is always the target's name.

    /** @var list<string> */
    public const HIT = [
        "`7You rise from behind the bank, take aim, and let fly. The snowball arcs through the cold air and smacks %s`7 right on the back of the head. Snow goes down their collar. Their yelp can be heard across the gardens. `@Direct hit!",
        "`7You wait, perfectly still, until %s`7 turns to look the other way. Then: thwack. A perfect shot, square between the shoulder blades. They spin around, but you have already ducked out of sight. `@Hit!",
        "`7You throw. %s`7 sees it coming, tries to dodge, slips on the ice, and catches the snowball full in the face on the way down. Several genin applaud. `@Spectacular hit!",
    ];
    /** @var list<string> */
    public const LEAP_HIT = [
        "`7You burst out from behind the bank, leap high into the air, flip once, and release at the top of the arc like a true shinobi. The snowball comes down on %s`7 like a meteor. There is a small explosion of white. Somebody whistles. `@Devastating hit!",
        "`7You sprint along the top of the snowbank, kick off a tree trunk and throw in mid-air. %s`7 never had a chance. `@A textbook strike!",
    ];
    /** @var list<string> */
    public const MISS = [
        "`7You throw. %s`7 does not even turn around; they simply lean six inches to the left, and your snowball sails harmlessly past. Show-off.",
        "`7Your snowball flies true and straight and hits the tree right next to %s`7, which dumps its entire load of snow on a passing genin instead. The genin is not pleased. %s`7 is laughing.",
        "`7You wind up, throw, and the snowball disintegrates in mid-air, too loosely packed. %s`7 looks at the little cloud of powder drifting past and raises an eyebrow at you.",
    ];
    /** @var list<string> */
    public const COUNTER = [
        "`7You throw, but %s`7 catches your snowball out of the air with one hand, spins on the spot and sends it straight back. It hits you right in the face. You taste snow. `\$Countered!",
        "`7Before you can even throw, a snowball comes out of nowhere and hits you in the ear. %s`7 was waiting for you. You did not see them ambush you, which was rather the point. `\$Ambushed!",
    ];

    public const MAIL_SUBJECT = "`&Snowball attack!";
    /** @var list<string> %s: the thrower's name. */
    public const MAIL_BODY = [
        "`7Out of nowhere, a snowball hits you square on the back of the head, and snow goes down your collar. You spin around just in time to see %s`7 duck behind a snowbank in the gardens, grinning.`n`n`&Isn't it time to hit back?",
        "`7A snowball smacks into you so hard it knocks your forehead protector crooked. Across the gardens, %s`7 waves cheerfully.`n`n`&Revenge is a dish best served cold. Very cold.",
        "`7You are walking past the gardens, minding your own business, when %s`7 drops out of a tree and stuffs a handful of snow down your back.`n`n`&This means war.",
    ];
    /** %s: target name. */
    public const MAIL_SENT = "`n`n`7A post-hawk takes off from a nearby branch. %s`7 will know exactly who did this.";

    // Ambushing an NPC: no points, just fun.

    /** @var list<string> */
    public const NPC = [
        "`7A genin team marches past the banks in perfect formation, led by their very proud sensei. You aim for the sensei. You hit the sensei. The genin are delighted. The sensei, slowly wiping snow off his orange book, says, \"`#Well. That was a mistake.`7\" You run.",
        "`7The delivery boy from Ichiraku Ramen trots past with a stack of bowls on a tray. You would never, ever hit someone carrying ramen. You hit the snowman next to him instead. Its head falls off. The delivery boy screams. You run.",
        "`7An old lady with a cane is crossing the gardens. You decide not to throw at her. As you lower your arm, she spins around and hits you square in the chest with a snowball you never saw her make. \"`#Retired jounin,`7\" she says, and walks on.",
        "`7A pigeon lands on the snowbank right in front of you and stares at you. You stare back. Neither of you throws anything. It is a deeply strange moment that you will think about for days.",
        "`7You hit a kid. The kid bursts into tears. You feel terrible. You go over to apologise, and the kid stops crying instantly and hits you point-blank with a snowball hidden behind his back. Genin these days.",
    ];
}
