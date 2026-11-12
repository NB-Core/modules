<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

/**
 * What the castle says: the prose for everything that can happen inside.
 *
 * Kept apart from the game logic so the texts can be read, and rewritten, in
 * one place. Each entry is English source text for output(), which passes it
 * through the translator; where an entry is a list, one variant is picked at
 * random, so a walk through the castle does not read the same twice.
 * `%s` placeholders are filled by the caller.
 */
final class Narration
{
    public const ENTER = "`2The great oaken door groans on its hinges as you step across the threshold. "
        . "Before your eyes have adjusted to the gloom, it swings shut behind you with a boom that rolls "
        . "through the halls like distant thunder. You throw your weight against it, claw at the iron bands, "
        . "search for a latch... but there is none on this side.`n`n"
        . "`7The air is cold and still, and it tastes of dust, wet stone and something older. "
        . "In the thin grey light that seeps through cracks high above, you make out a narrow passage "
        . "littered with splintered furniture, rusted armour and the scattered remains of those who came "
        . "before you. Somewhere deep inside, water drips with the patience of centuries.`n`n"
        . "`2There is only one way out now: through.`n";

    public const MOUNT_LEFT = "`7Outside, your %s paws at the earth and calls after you, but the door does not open "
        . "again. Whatever waits in here, you will face it alone.`n";

    public const BUFFS_SUSPENDED = "`5A pressure settles on your chest as the castle's old magic seeps into you. "
        . "The blessings and wards you carried in fade like breath on cold glass; in here, they are of no use.`n`n";

    public const ENTERED_TODAY = "`2You set your shoulder to the door and push until your boots slide on the flagstones, "
        . "but it will not move an inch. Scratched into the wood, fresh enough to still show pale splinters, "
        . "is a single word: `\$Tomorrow`2.`n`2(`3You can only enter the castle once per day`2)`0";

    public const TOO_WEAK = "`2The door is studded with rusted iron and taller than three men. You push, you pull, "
        . "you kick it once out of sheer frustration... nothing. A hollow laugh drifts down from one of the "
        . "upper windows, and you get the feeling the castle is not yet interested in you.`n"
        . "Come back when you are a stronger and more experienced warrior.`n";

    public const BEFORE_THE_DOOR = "`2The Abandoned Castle looms over you, black against the sky. Ivy has swallowed "
        . "half its walls, and the windows stare down like empty eye sockets. The great door stands ajar, "
        . "as if it has been waiting for you. Once you step through, there is no turning back.`n";

    public const MODES = "`n`vChoose how you dare to enter:`n"
        . "`n`^Normal`v: you keep a map of every room you have explored, and you know where the exit lies. You also carry an escape rope: if you give up, you can climb out, but you leave without treasure and too tired to fight in the forest again today."
        . "`n`^Hardcore`v (twice the treasure): no map of the rooms, only a sense of where you stand and where the exit is."
        . "`n`^Extra Hardcore`v (three times the treasure): no map, no sense of direction. Only the room you stand in, and your wits."
        . "`n`nChoose wisely. Once the door closes, it does not open again...";

    /** @var list<string> */
    public const GEM = [
        "`^Something glitters between the bones of a long-dead adventurer. You pry it loose: a `%gem`^, cold and flawless. They will not be needing it anymore.",
        "`^A loose stone in the wall wobbles under your hand. Behind it, wrapped in a rotten scrap of velvet, lies a `%gem`^ that catches what little light there is.",
        "`^Your boot kicks something that skitters across the floor with a bright click. You crouch and find a `%gem`^ resting in a crack between the flagstones.",
    ];

    /** @var list<string> */
    public const GOLD = [
        "`^A torn purse lies among the rubble, its strings still knotted. Inside are `6%s gold`^ pieces, stamped with the face of a king nobody remembers.",
        "`^Under a collapsed bench you find a small strongbox, its lock rusted through. It holds `6%s gold`^.",
        "`^The skeleton slumped against the wall still clutches a pouch in its bony fingers. It gives up its `6%s gold`^ without a fight.",
        "`^Coins glint in a puddle of black water. You fish out `6%s gold`^, trying not to think about what else might be down there.",
        "`^Someone hid `6%s gold`^ in a hollowed-out book on a mouldering shelf. Whoever it was never came back for it.",
    ];

    public const POTION = "`^On a shelf thick with cobwebs stands a single stoppered vial, its contents glowing a faint, warm red. "
        . "A `@healing potion`^, still good. You tuck it away carefully.";

    /** @var string Filled with the name of the food found. */
    public const FOOD = "`^Fortune smiles on you: wrapped in waxed cloth in an alcove, as if someone meant to come back for it, you find %s!`0";

    public const GROWL_FAR = "`7From somewhere deep in the walls comes a low, wet growl. It stops. Then, much further away, it starts again, as though whatever made it is circling.";
    public const SCREAM = "`7A scream tears through the corridors, high and terrible, and ends far too suddenly. The silence afterwards is worse.";
    public const SMELL = "`7A stench rolls over you, thick and sweet with rot, the smell of meat left too long in the dark. You breathe through your mouth and press on.  ";
    public const SMELL_LINGERS = "`7Some of it clings to your clothes; you will be carrying this place with you for a while.";
    public const SKELETON = "`7A skeleton sits propped in the corner, still in the remains of its armour. One bony hand points down the corridor you came from. Poor soul never found the way out.";
    public const RAT_HAND = "`7In the shadows a rat crouches over something pale, gnawing with quick, wet sounds. It is a hand. The rat stares at you, utterly unafraid, and keeps eating.";
    public const GROWL_NEAR = "`4A growl rumbles right behind the wall beside you, close enough that you feel it through the stone. You hold your breath until it fades.";
    public const CHILL = "`3The temperature drops in an instant. Your breath mists, frost creeps along the walls, and for a heartbeat you are certain someone is standing right behind you. When you turn, there is no one.";
    public const HELP_FAR = "`7Faint and far away, someone is crying for help. The echoes make it impossible to tell which way the voice comes from.";
    public const HELP_NEAR = "`7\"Help! Please, someone, help me!\" The voice is close now, just around the next corner, or the one after that...";
    public const HELP_STOPS = "`7Somewhere a voice is screaming for help, desperate and hoarse. Then, abruptly, it stops. You do not hear it again.";

    public const HURT_SHARP = "`4Ouch!`7 Something sharp punches through the sole of your boot: a rusted caltrop, hidden in the dust for just this moment. You pull it out and limp on.";
    public const HURT_SPIDER = "`4Ouch!`7 A spider the size of your fist drops from the ceiling onto your neck. You tear it away, but not before it sinks its fangs in. The bite throbs and burns.";
    public const HURT_RAT = "`4Ouch!`7 A rat darts from a crack in the wall and bites your ankle, then vanishes back into the darkness before you can strike.";
    public const HURT_BIG_RAT = "`4Ouch!`7 A rat as big as a dog lunges out of a heap of rubble and sinks its teeth into your leg. It shakes its head once, hard, then lets go and scurries away into the dark.";

    public const WHAM = "<span class='ac-text-large'>`4Wham!</span>`n";
    public const SHOOP = "<span class='ac-text-large'>`4Shoop!</span>`n";
    public const SPIKES_FLOOR = "`3A click under your heel, too late to step back. The floor erupts in a forest of iron spikes, and the last thing you see is the dim light glinting on their tips...`n";
    public const SPIKES_WALL = "`3The wall beside you shudders, and before you can turn, a row of spikes bursts from the stone and pins you where you stand. The world goes grey, then black.`n";
    public const BEHEADED = "`3There is a whisper of steel in the dark. For a strange, weightless moment the world tilts... and you find yourself looking up at your own body as it slowly crumples to the floor.`n";

    public const TRAP_EDGE = "`3You step forward into nothing. There is no floor here, no walls, only an endless, howling dark below. You fall for a very long time.`n";
    public const TRAP_PIT = "`3The floor tilts away beneath you and you plunge into a pit lined with spikes. Far, far above, the dim light of the corridor narrows to a single star... and goes out, just as your life does.`n";
    public const TRAP_WATER = "`3A flagstone sinks under your foot with a heavy clunk. Somewhere above, sluices grind open, and water roars into the chamber. "
        . "It is at your knees, your chest, your chin... You claw at the walls for a handhold that is not there, and the world fades as your lungs burn for air.`n";
    public const TRAP_WALLS = "`3Behind you a portcullis slams down; ahead, another. Then, with a groan of ancient stone, the walls begin to move. "
        . "You push against them with all your strength, but they come on, slow and certain. Soon you know exactly how a beetle feels under a boot.`n";
    public const TRAP_BLADE = "`3You hear it before you see it: a deep, rising hum. A great curved blade swings out of the darkness across your path at waist height. "
        . "The world goes dim as the top half of your body slides away from the bottom.`n";

    public const NEWS_LOST = "`% %s `5went into the Abandoned Castle and was never seen again.";

    /** @var array<string, string> Morgue notes, by cause of death. */
    public const MORGUE = [
        'spikes' => ' - Impaled on spikes in the Abandoned Castle',
        'beheaded' => ' - Beheaded in the Abandoned Castle',
        'edge' => ' - Fell off the edge of the world in the Abandoned Castle',
        'drowned' => ' - Drowned in the Abandoned Castle',
        'crushed' => ' - Crushed between the walls of the Abandoned Castle',
        'sliced' => ' - Sliced in half in the Abandoned Castle',
    ];

    public const MOUNT_AT_EXIT = "`2Your %s is waiting for you outside, and greets you with a joyful whinny.`n";
    public const ESCAPED = "`@Light!`2 Real daylight, warm on your face. You stagger out of a narrow postern gate into fresh air, blinking like a mole. "
        . "Behind you the gate swings shut on its own, and you could swear you hear a disappointed sigh from somewhere inside the walls.`n";
    public const BUFFS_RETURN = "`5As you leave the castle's shadow, the weight lifts from your chest, and your blessings return to you one by one.`n`n";
    public const NEWS_ESCAPED = "`% %s `5made it out of the Abandoned Castle alive, in %s moves!";
    public const FINISHED = "`2You found your way through the maze in `^%s`2 moves.`n";
    public const TREASURE = "`2Just inside the gate lies the treasure of the castle's last lord: `6%s gold`2 and `%%s gems`2. It is yours now.`n`n";

    /** Normal mode only: giving up and climbing out costs the rest of the day's forest fights. */
    public const ROPE_NAV = "Climb out on the escape rope";
    public const ROPE_ESCAPE = "`2You have had enough. With shaking fingers you knot your escape rope around a rusted torch bracket and look for a way up. "
        . "High above, a crack in the vaulted ceiling lets in a thin grey thread of daylight.`n`n"
        . "The climb is long and miserable. The stones are slick with damp, the rope bites into your palms, and twice your boots slip and you hang there, "
        . "swinging over the dark, listening to something below that has noticed you leaving. At last you haul yourself through the crack and roll out "
        . "onto the wet grass outside the walls.`n`n"
        . "`2You are alive, and that is all you take with you. The castle's treasure stays behind, and the climb has left you so drained that "
        . "`\$you will not fight in the forest again today`2.`n`n";
    public const NEWS_ROPE = "`% %s `5climbed out of the Abandoned Castle on a rope, empty-handed but alive.";

    public const MODE_HARDCORE = "`b`\$Hardcore Mode`0`b`n";
    public const MODE_EXTREME = "`b`\$Extra Hardcore Mode`0`b`n";

    public const FOREST_FIND = "`n`2Pushing through a thicket you would normally have walked around, you stumble onto an overgrown path of cracked flagstones. "
        . "It leads up a hill to a black, half-ruined `bAbandoned Castle`b, older than any map you know.`n`n"
        . "At the foot of the path stands a weathered sign. Someone has carved into it, deeply and in haste:`n"
        . "\"`#Turn back. Only the bravest should enter this place, and even they rarely leave it.`2\"";

    /**
     * A few words on each kind of room, for the room's description.
     *
     * @var array<string, list<string>>
     */
    public const ROOMS = [
        'Crossroads' => [
            'Four passages meet here beneath a vaulted ceiling, and the wind moans softly through all of them at once.',
            'A wide crossroads. A cracked fountain stands dry in its centre, choked with dust and old bones.',
        ],
        'Junction' => [
            'The corridor forks here. Torch brackets line the walls, long cold and furred with rust.',
            'Three ways lead off from this small, damp chamber. Scratch marks on the walls count days nobody finished counting.',
        ],
        'Corridor' => [
            'A narrow corridor, its walls slick with damp. Your footsteps echo a little too long.',
            'A long passage of grey stone. Faded tapestries hang in rags along the walls.',
        ],
        'Bend' => [
            'The passage bends sharply here. You cannot see what waits beyond the corner.',
            'The corridor turns. Someone has scrawled an arrow on the wall in something dark, pointing back the way you came.',
        ],
        'Dead end' => [
            'A dead end. The stone is scored with deep claw marks, as if something tried very hard to get through.',
            'The passage ends in a blank wall. A cold draft whispers through the cracks, but there is no way on.',
        ],
    ];

    /** @var string Shown at the entrance instead of a room description. */
    public const ENTRANCE_ROOM = 'You stand just inside the entrance. The great door behind you will not open again.';

    /** @var array<string, string> Monster entrances, by the fight's op. */
    public const MONSTERS = [
        'ghost1' => "`7The cold comes first. Then a pale shape rises out of the floor, a face without eyes, a mouth that opens far too wide. "
            . "The `@Disembodied Spectre`7 drifts towards you, and the torch-light gutters and dies.`n`n",
        'ghost2' => "`7A shriek of pure fury fills the corridor. The `@Angry Spectre`7 comes at you from the wall, its outline flickering with rage, "
            . "screaming the names of people long dead.`n`n",
        'bat' => "`7Leathery wings explode from the dark above you. A `@Bat`7 the size of a hawk dives at your face, shrieking, fangs bared.`n`n",
        'rat' => "`7The rubble ahead shifts and heaves. Out of it climbs a `@Huge Rat`7, grey and scarred, its eyes glinting red. It does not run. It comes for you.`n`n",
        'minotaur' => "`7The floor trembles. Heavy, hooved footsteps, and a snort like a bellows. Out of the darkness steps the `@Minotaur`7, "
            . "head lowered, horns gleaming, a great axe dragging sparks from the stones behind it. It has smelled you.`n`n",
    ];

    public const VICTORY = "`b`4You have slain `^%s`4.`b`n`7Its last cry echoes away down the corridors, and the castle is silent again... for now.`n";
    public const DEFEAT = "`7You fall to the cold stones, and the darkness closes in. The last thing you see is `^%s`7 turning away, already losing interest in you.";

    /**
     * One of the variants, at random.
     *
     * @param list<string> $variants
     */
    public static function pick(array $variants): string
    {
        return $variants[\function_exists('e_rand') ? \e_rand(0, \count($variants) - 1) : array_rand($variants)];
    }

    /**
     * The atmospheric sentence for a room, by its kind.
     */
    public static function room(string $kind, bool $entrance): string
    {
        if ($entrance) {
            return self::ENTRANCE_ROOM;
        }
        return isset(self::ROOMS[$kind]) ? self::pick(self::ROOMS[$kind]) : 'A dark, silent room.';
    }
}
