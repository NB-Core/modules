<?php

declare(strict_types=1);

namespace Shinobi\Modules\AbandonCastle;

/**
 * The prose that makes the castle a place rather than a grid: what a room
 * looks, smells and sounds like, the landmarks a player can find their way
 * by, the Minotaur's hunt, the choices the castle offers, and the bones of
 * those who did not make it out.
 *
 * Like Narration, every entry is English source text for output(), which
 * passes it through the translator. Room descriptions are put together from
 * small pieces, one per layer, so the same few lines make hundreds of rooms.
 * The castle gets older and darker the further north a room lies: each layer
 * has three tiers, for the outer halls, the middle and the deep castle.
 */
final class Scenery
{
    /**
     * What light there is, by tier.
     *
     * @var list<list<string>>
     */
    public const LIGHT = [
        [
            'Grey daylight leaks in through an arrow slit high in the wall.',
            'A shaft of pale light falls through a collapsed part of the ceiling.',
            'Dusty light seeps in through cracks between the stones.',
            'A narrow window, half choked with ivy, lets in a green, underwater kind of light.',
            'Somewhere above, a gap in the roof lets the wind and a little light in.',
        ],
        [
            'The only light comes from patches of pale fungus that glow faintly on the walls.',
            'It is almost dark here. You move by the faint glimmer of wet stone.',
            'A guttering candle stub burns in a niche, though no one should have lit it.',
            'A cold blue shimmer hangs in the air, with no source you can find.',
            'Light from somewhere behind you throws your own shadow far ahead, long and wrong.',
        ],
        [
            'The darkness here is thick, almost solid. You feel your way along the wall.',
            'No light reaches this deep. Your eyes play tricks on you: shapes that move when you look away.',
            'A dull red glow pulses from the cracks in the floor, slow as a heartbeat.',
            'Black soot covers every surface, as if a great fire burned here once and the dark never left.',
            'The air itself seems to swallow the light. You can barely see your own hands.',
        ],
    ];

    /**
     * What the air smells like, by tier.
     *
     * @var list<list<string>>
     */
    public const SMELL = [
        [
            'It smells of dust and old wood.',
            'The air smells of rain, wet moss and crumbling mortar.',
            'A faint smell of smoke hangs here, years old.',
            'Bird droppings and old straw: something nests up in the rafters.',
            'It smells of rust and mildew.',
        ],
        [
            'The air is damp and smells of rot and still water.',
            'A sour smell of mould clings to everything.',
            'You catch the smell of old candle wax and something burnt.',
            'It smells like a cellar that has not been opened in a hundred years.',
            'A sweetish smell of decay drifts in from somewhere close.',
        ],
        [
            'The air tastes of iron, like blood on your tongue.',
            'It smells of wet fur and dung. Something large sleeps down here sometimes.',
            'The stench of old death is so thick you can taste it.',
            'A cold, mineral smell rises from below, the breath of the deep earth.',
            'It smells of sulphur and ash.',
        ],
    ];

    /**
     * One thing that catches the eye, by tier.
     *
     * @var list<list<string>>
     */
    public const DETAIL = [
        [
            'A broken helmet lies in the corner, its visor rusted shut.',
            'Rotten tapestries hang from iron rods, their pictures long faded to grey.',
            'A toppled bench blocks half the floor; someone used it as a barricade once.',
            'Old boot prints cross the dust here and there. None of them lead back.',
            'A child\'s wooden horse lies on its side by the wall, painted red long ago.',
            'Swallows have built their nests along the beams overhead.',
        ],
        [
            'Names are scratched into the wall, dozens of them, some crossed out.',
            'A rusted cage hangs from the ceiling on a chain. It is empty. The door is open.',
            'A suit of armour stands against the wall. Its helmet turns ever so slightly as you pass. Surely not.',
            'Shattered pottery crunches under your boots, and among the shards lie small, gnawed bones.',
            'Someone has drawn a map on the wall in charcoal. It is wrong, or the castle has changed since.',
            'A heavy table, laid for a feast, stands under a blanket of dust. The plates are still full.',
        ],
        [
            'Deep gouges run along the floor, the marks of something dragged, or something dragging.',
            'Bones are stacked neatly against the wall, sorted by size. Someone did that on purpose.',
            'A great iron ring is set into the floor, and the chain on it is broken from the inside.',
            'Strange symbols are carved into every stone here, and the carvings are still sharp.',
            'The walls are wet, and when you touch them, your fingers come away dark red.',
            'Horns are nailed to the wall above an archway, big ones. Something here collects trophies.',
        ],
    ];

    /**
     * What you hear, by tier; picked fresh on each visit.
     *
     * @var list<list<string>>
     */
    public const SOUND = [
        [
            'Wind sighs through the cracks.',
            'Somewhere, a shutter bangs in the wind.',
            'Pigeons coo in the rafters, unbothered by you.',
            'Water drips steadily into a puddle nearby.',
        ],
        [
            'Somewhere far off, a chain rattles, then falls still.',
            'You hear scratching inside the walls, many small claws.',
            'A door creaks somewhere behind you, slowly, as if pushed by a careful hand.',
            'Your own heartbeat seems very loud in the silence.',
        ],
        [
            'Something breathes in the dark. It might be the wind.',
            'Far below, stone grinds against stone.',
            'A whisper, almost words, comes from nowhere in particular.',
            'Hooves on stone, far away. Then nothing.',
        ],
    ];

    /**
     * Landmarks: rooms a player can recognise and find their way by. A maze has
     * some of them in fixed places, so a room is the same room each time.
     * Each is [name for the map, description].
     *
     * @var list<array{0:string,1:string}>
     */
    public const LANDMARKS = [
        ['The Chapel', "`&The Chapel.`7 Rows of rotten pews face a cracked stone altar. Above it, a stained-glass window shows a saint whose face has been carefully scratched out."],
        ['The Library', "`&The Library.`7 Shelves climb into the darkness, sagging under books swollen with damp. One book lies open on a lectern, every page filled with the same word, over and over."],
        ['The Armoury', "`&The Armoury.`7 Weapon racks line the walls, almost all of them empty. A single spear remains, its tip snapped off and lying beside it, as if someone tried to take it and thought better of it."],
        ['The Great Hall', "`&The Great Hall.`7 A vast room with a minstrels' gallery high above. A long table runs its length, and at the head of it stands a throne of black wood, turned to face the wall."],
        ['The Well Room', "`&The Well Room.`7 A round well sits in the middle of the floor, its rim worn smooth by centuries of hands. Far, far below, something glints."],
        ['The Kitchen', "`&The Kitchen.`7 A hearth large enough to roast an ox. Copper pots hang from hooks, green with age, and a cleaver is still buried in the chopping block."],
        ['The Gallery of Portraits', "`&The Gallery of Portraits.`7 Generations of the castle's lords glare down from gilded frames. In every painting, the eyes have been cut out."],
        ['The Organ Loft', "`&The Organ Loft.`7 A great pipe organ fills one wall, its pipes dented and bent. When the wind blows through the castle, it plays a single, low note."],
        ['The Crypt Door', "`&The Crypt Door.`7 A heavy iron door is set into the floor, chained and padlocked. Fresh scratches surround the keyhole, made from the other side."],
        ['The Hall of Mirrors', "`&The Hall of Mirrors.`7 Tall mirrors line both walls, most of them cracked. In the ones still whole, your reflection is a heartbeat late."],
        ['The Bell Tower Stair', "`&The Bell Tower Stair.`7 A spiral stair winds up into the dark, but it ends after a dozen steps in empty air. The rope of a great bell hangs down from above, swaying gently."],
        ['The Garden Court', "`&The Garden Court.`7 An inner courtyard, open to the sky. What was once a garden has grown wild: black roses climb the walls, and their thorns are as long as daggers."],
    ];

    // The Minotaur's hunt.

    /** @var list<string> When it is in the very next room; %s is the direction. */
    public const HUNT_ADJACENT = [
        "`4A snort, so close you feel the hot breath of it, comes from the %s. Hooves scrape on stone right behind the corner. It knows exactly where you are.",
        "`4The stench of wet fur hits you like a wall. From the %s, just out of sight, something huge shifts its weight and breathes in your direction.",
        "`4The flagstones under your feet tremble. Heavy steps, very near, to the %s. Then they stop, and the silence is listening.",
    ];

    /** @var list<string> When it is two or three rooms away; %s is the direction. */
    public const HUNT_NEAR = [
        "`\$Hooves ring on stone somewhere to the %s, not far. Slow, heavy, patient steps.",
        "`\$A deep, rumbling growl rolls in from the %s. It is getting closer.",
        "`\$Something big is moving through the halls to the %s. You hear an axe-blade scrape along a wall.",
    ];

    /** @var list<string> When it is further, but on your trail. */
    public const HUNT_FAR = [
        "`7Far away, a bellow echoes through the castle, and the dust trickles from the ceiling.",
        "`7You hear distant hoofbeats. Somewhere in this maze, the Minotaur is walking, and it is not walking aimlessly.",
        "`7A low, mournful bellow drifts through the corridors from somewhere far off.",
    ];

    /** When a random event puts it on your scent. */
    public const HUNT_SCENT = "`4A bellow shakes the walls, and the sound of hooves breaks into a run. The Minotaur has caught your scent.";

    /** When it is dead and the halls are quieter for it. */
    public const HUNT_SLAIN = "`2With the Minotaur dead, the castle feels a little less like a trap and a little more like a ruin.`n";

    // Choices.

    public const CHEST = "`7Half hidden under a collapsed shelf stands an iron-bound chest. The lock is rusted, but the lid looks like it might give under a little force. Then again, people do not usually leave chests lying around for strangers.";
    public const CHEST_OPEN = "Force the chest open";
    public const CHEST_GOLD = "`^The lid gives way with a screech. Inside, wrapped in rotten velvet, lies a pouch of old coins: `6%s gold`^.";
    public const CHEST_GEM = "`^The lid gives way. The chest is empty but for a single `%gem`^, resting in the dust as if it had been waiting for you.";
    public const CHEST_TRAP = "`4There is a click as the lid lifts. A rusted needle springs out of the lock and drives deep into your hand. Your arm goes numb to the elbow. You lose `\$%s`4 hitpoints.";

    public const WELL = "`7A small stone basin is set into the wall, fed by a thin trickle of water from a crack above. The water is very clear, and very cold. Your throat is dry from the dust.";
    public const WELL_DRINK = "Drink from the basin";
    public const WELL_HEAL = "`@The water is icy and tastes of stone and snow. Strength flows back into your limbs, and your wounds close. You feel fully restored.";
    public const WELL_NOTHING = "`7The water is cold and clean and does absolutely nothing. Still, it was good to drink.";
    public const WELL_POISON = "`4The water burns like acid the moment it touches your tongue. You retch and stagger, and your vision swims. You lose `\$%s`4 hitpoints.";

    public const SHRINE = "`7In a niche in the wall stands a tiny shrine: a bowl of black stone and a candle that burns with a perfectly still flame. Scratched into the stone below: `#\"A jewel for the way.\"";
    public const SHRINE_OFFER = "Offer a gem to the shrine";
    public const SHRINE_ANSWER = "`#You lay the gem in the bowl. It sinks into the stone like into water. The candle flame bends, slowly, until it points %s. A voice like dry leaves breathes: `7\"%s rooms to the light.\"";
    public const SHRINE_NO_GEM = "`7You have no gem to give. The flame does not even flicker.";
    public const SHRINE_LOST = "`7The candle gutters and goes out. Wherever the way out is, the shrine will not say.";

    public const RIDDLE = "`7A skeleton sits propped against the wall, still in a rusted crown. As you pass, its jaw drops open and a dry voice rattles out of the empty skull:`n`n`#\"%s\"";
    public const RIDDLE_RIGHT = "`@The skeleton's jaw clacks shut, and it gives a dry, satisfied rattle. A `%gem`@ rolls out of its ribcage and stops at your feet.";
    public const RIDDLE_WRONG = "`4\"Wrong,\"`7 the skull hisses, and its bony hand snaps shut around your wrist, cold and hard as iron, before it lets go. You lose `\$%s`7 hitpoints. When you look again, it is just a skeleton.";

    /**
     * Riddles: the question, the possible answers, and which one is right.
     *
     * @var list<array{0:string,1:list<string>,2:int}>
     */
    public const RIDDLES = [
        ['The more of me you take, the more you leave behind. What am I?', ['Footsteps', 'Gold', 'Time'], 0],
        ['I have keys but open no locks, I have space but no room. What am I?', ['A jailer', 'A keyboard', 'A coffin'], 1],
        ['What has a head and a tail, but no body?', ['A coin', 'A snake', 'A ghost'], 0],
        ['I am always in front of you, but you can never see me. What am I?', ['The past', 'The future', 'Your nose'], 1],
        ['What can fill a room but takes up no space?', ['Smoke', 'Water', 'Light'], 2],
        ['The man who makes me sells me. The man who buys me never uses me. The man who uses me never knows. What am I?', ['A coffin', 'A sword', 'A crown'], 0],
    ];

    public const LEAVE_IT = "Leave it alone";
    public const WALK_ON = "Walk on without a word";

    // The bones of those who died here.

    /**
     * How each death reads on the bones; the keys match where the death is recorded.
     *
     * @var array<string, string>
     */
    public const BONES_HOW = [
        'spikes' => 'Rusted spikes still pin them to the floor.',
        'beheaded' => 'The skull lies a few steps away from the rest.',
        'monster' => 'Their bones are cracked and gnawed; whatever killed them was hungry.',
        'minotaur' => 'Their armour is split clean in two by a single, enormous blow.',
    ];

    /** %s: the name of whoever died here; then one of BONES_HOW. */
    public const BONES = "`7Against the wall lie the bones of `&%s`7, still in the rags of their gear. %s";
}
