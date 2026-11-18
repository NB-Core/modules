<?php

declare(strict_types=1);

namespace Shinobi\Modules\WinterCastle;

/**
 * Everything the Winter Castle says.
 *
 * English source text for output(), which passes it through the translator.
 * Where an entry is a list, one variant is picked at random.
 *
 * Every winter, the Frost Palace of the Land of Snow sends its envoys to the
 * villages of the shinobi world, and through a gate of ice beside the village
 * gate, guests can walk straight into its halls. Its keeper is Lady Yukiko,
 * who has run the palace since long before anyone can remember.
 */
final class Texts
{
    // The village and the gate.

    public const VILLAGE_DESC = "`n`&Beside the village gates, where yesterday there was only a snowdrift, an arch of blue ice now rises, glittering in the cold. Through it, faint and far away, you can hear bells.`n";
    public const NAV_VILLAGE = "`lW`Linter `)C`jastle";

    /** @var array<string, list<string>> */
    public const GATE = [
        'morning' => [
            "`&The arch of ice beside the village gates glows pink in the early light. Snow sculptures line the path towards it: a ninja frog in a scarf, a row of very serious snow-genin, a snowman with a forehead protector that someone has clearly stolen from a sleeping chuunin. Beyond the arch, the air shimmers like the surface of a lake, and through it you can see the white towers of a palace that should be a hundred leagues away.",
        ],
        'day' => [
            "`&Children are sliding down the drift beside the ice arch, screaming with joy, while their parents pretend to be too dignified to join them. The arch itself hums faintly when you come near, and through its shimmering heart you see a courtyard of snow, banners of white and silver, and the tall spires of the Frost Palace of the Land of Snow.",
        ],
        'evening' => [
            "`&Lanterns of carved ice line the path to the arch, each with a candle burning inside, so that the whole way glows blue and gold. Through the shimmering gateway you can hear music: flutes, a drum, laughter. Somewhere on the other side, the Frost Palace is holding its winter feast.",
        ],
        'night' => [
            "`&It is deep night, and the village is asleep, but the ice arch is not. It glows softly in the dark, and the snow sculptures around it cast long blue shadows. Through the gateway you see the Frost Palace under a sky full of northern lights, green and violet, rippling slowly over its towers.",
        ],
    ];
    public const GATE_ASK = "`n`n`&A small paper sign is frozen into the ice: `#\"Guests of the Frost Palace are welcome all winter. Please knock the snow off your sandals. No jutsu in the halls. - Y.\"";
    public const NAV_ENTER = "Step through the ice arch";
    public const NAV_BACK_VILLAGE = "Return to the village";

    public const CLOSED = "`&Where the ice arch stood, there is only a puddle and a ring of wet stones. The Frost Palace has closed its gates until next winter.";
    public const SU_OUT_OF_SEASON = "`n`n`\$(Out of season: only staff can enter right now.)`0`n";

    // The great hall and Lady Yukiko.

    public const HALL_TITLE = "`c`b`lT`Lhe `&Frost `lP`Lalace`b`c`n";
    public const HALL = "`&You step through the arch, and the cold of the village falls away behind you. Instead there is a different cold, clean and bright, like the first breath of air on a mountain. You stand in a great hall built entirely of ice. Its pillars are carved like frozen waterfalls, its ceiling is so high it disappears into a glittering mist, and through its walls you can see the shapes of snow-covered mountains outside, blurred and blue.`n`nIn the middle of the hall stands a fir tree taller than the Hokage Tower, hung with ice crystals that ring softly whenever someone walks past. Guests from every village wander between the pillars, wrapped in furs, their breath steaming, their faces red with cold and delight.";
    /** @var list<string> */
    public const YUKIKO = [
        "`n`nA tall woman in a kimono of white and silver glides across the hall towards you. Her hair is as pale as fresh snow, and when she smiles, the air around her grows a little warmer. `#\"Welcome to the Frost Palace,\"`& she says. `#\"I am Yukiko, keeper of these halls. The Land of Snow has not always been kind to strangers. This season, it would like to make up for that.\"",
        "`n`nLady Yukiko is standing by the great tree, straightening a crystal ornament that does not need straightening. She sees you and inclines her head. `#\"Back again? Good. The palace likes guests. It gets lonely the other eleven months of the year.\"",
        "`n`nLady Yukiko passes you with a tray of steaming cups, hands you one without asking, and moves on. It is ginger tea with honey, and it is exactly what you needed.",
        "`n`nYou spot Lady Yukiko by the window, talking quietly with an old man in the uniform of the Land of Snow's guard. She catches your eye and gives you a small wave, as if you were an old friend.",
    ];
    public const HALL_ROOMS = "`n`n`&Wide corridors lead off from the hall in every direction: to the `lIce Garden`&, where sculptors are at work, to the `lBanquet Hall`&, from which comes a smell that makes your stomach growl, up the spiral stair to the `lLantern Tower`&, out to the steaming `lHot Spring`& in the courtyard, and down a narrow stair into the `lFrost Cellar`&, where nobody seems to want to go.";

    public const NAV_ROOMS = "The Frost Palace";
    public const NAV_ADVENT = "Open today's door of the Advent calendar";
    public const NAV_ADVENT_LOOK = "Look at the Advent calendar";
    public const NAV_GARDEN = "Ice Garden";
    public const NAV_BANQUET = "Banquet Hall";
    public const NAV_TOWER = "Lantern Tower";
    public const NAV_SPRING = "Hot Spring";
    public const NAV_CELLAR = "Frost Cellar";
    public const NAV_HALL = "Back to the great hall";
    public const NAV_LEAVE = "Leave through the ice arch";
    public const HALL_TALK = "Talk with the other guests";

    // The Advent calendar.

    public const ADVENT_TITLE = "`c`b`\$A`@dvent `\$C`@alendar`b`c`n";
    public const ADVENT_INTRO = "`&Against the wall of the great hall stands a cabinet of red-lacquered wood, taller than three men, with twenty-four little doors set into it in no particular order. Each door is painted with a number and a tiny picture, and each picture was painted by a child of the Land of Snow, some of them very well and some of them with great enthusiasm. Lady Yukiko says a new door may be opened every day until the night of the twenty-fourth.`n`n";
    /** %s: the day of the month. */
    public const ADVENT_TODAY = "`&You find the door with the number `^%s`& on it and pull it open.`n`n";
    public const ADVENT_DONE = "`&Today's door is already open; you can see the empty little shelf behind it. Tomorrow there will be another.";
    public const ADVENT_NOT_YET = "`&The doors are all still sealed with a little dab of wax. `#\"Not until the first of the last month,\"`& says Lady Yukiko, `#\"no matter how nicely you ask.\"";
    public const ADVENT_OVER = "`&All twenty-four doors stand open, and every shelf behind them is empty. The calendar has given everything it had this year.";
    /** %s, %s: doors opened, days so far. */
    public const ADVENT_COUNT = "`n`n`7You have opened `&%s`7 of the doors so far this year.";
    public const ADVENT_MISSED = "`n`7(Doors from days you missed stay closed. The calendar is strict about that.)";

    /**
     * The twenty-four doors: [reward type, amount, text]. Types: gold, gems,
     * turns (forest fights), heal (percent of max hitpoints), buff, none.
     *
     * @var array<int, array{0:string,1:int,2:string}>
     */
    public const DOORS = [
        1 => ['gold', 100, "`&Behind the first door lies a little purse of red silk, embroidered with a snowflake. Inside are `^%s gold`& coins, each one stamped with the crest of the Land of Snow."],
        2 => ['none', 0, "`&Behind the second door is a tiny painted scene of a fox in the snow, curled up asleep with its tail over its nose. There is nothing else. Somehow, it is enough. You feel calm for the rest of the day."],
        3 => ['heal', 30, "`&Behind the third door sits a little clay pot of salve that smells of camphor and pine. You rub some into your knuckles, cracked from the cold, and feel the warmth spread through you. (You recover `@%s`& hitpoints.)"],
        4 => ['gems', 1, "`&Behind the fourth door is a single `%gem`&, frozen into a perfectly clear cube of ice. You hold it in your hand until the ice melts away."],
        5 => ['turns', 1, "`&Behind the fifth door you find a pair of fur-lined sandals, just your size, with a note: `#\"For the long road.\"`& Your feet have never felt this ready. (You gain `@%s`& forest fight.)"],
        6 => ['gold', 200, "`&The sixth door is bigger than the others, and painted with a pair of boots. In the Land of Snow, travellers say, a kind old monk fills the boots of good children on this day. Your boots, it seems, have been filled: with `^%s gold`& and a handful of walnuts."],
        7 => ['buff', 0, "`&Behind the seventh door is a tiny cup of plum wine, still warm. It tastes of summer, which is strange and wonderful in a palace made of ice. You feel your fighting spirit rise."],
        8 => ['none', 0, "`&Behind the eighth door is a slip of paper with a poem in a child's handwriting:`n`n`#  The snow is white,`n  the snow is cold,`n  my brother threw it,`n  so I told.`n`n`&You decide that this is the finest poem you have ever read."],
        9 => ['gold', 150, "`&Behind the ninth door, a small wooden ninja figure holds out a bag almost as big as itself. It contains `^%s gold`&. The ninja figure, as far as you can tell, does not want it back."],
        10 => ['heal', 50, "`&Behind the tenth door is a packet of the famous ginger tea of the Land of Snow. Lady Yukiko brews it for you on the spot, and you drink it beside the great tree. (You recover `@%s`& hitpoints.)"],
        11 => ['gems', 1, "`&Behind the eleventh door you find a tiny snow globe. When you shake it, the snowflakes inside settle into the shape of the Leaf symbol, and a `%gem`& rolls out of its wooden base."],
        12 => ['turns', 1, "`&Behind the twelfth door is a single dumpling, still steaming, wrapped in paper. You eat it in two bites and feel ready to take on the whole forest. (You gain `@%s`& forest fight.)"],
        13 => ['none', 0, "`&The thirteenth door is stuck. You pull, and pull, and finally it flies open, and a cascade of glitter pours out all over you. Lady Yukiko, nearby, does not laugh. Much. You will be finding glitter for days."],
        14 => ['gold', 250, "`&Behind the fourteenth door is a little model of the palace itself, carved from ice, and in its courtyard sits a pile of coins: `^%s gold`&."],
        15 => ['buff', 0, "`&Behind the fifteenth door lies a sprig of winter pine tied with a red thread. You tuck it behind your forehead protector. It smells of the forest and of courage."],
        16 => ['heal', 40, "`&Behind the sixteenth door you find a tiny hand-warmer, a pouch of hot stones, stitched with a smiling sun. You hold it between your hands until the warmth reaches your heart. (You recover `@%s`& hitpoints.)"],
        17 => ['gems', 1, "`&Behind the seventeenth door is a paper crane, folded from silver paper. When you unfold it to see how it was made, a `%gem`& drops out of its belly."],
        18 => ['none', 0, "`&Behind the eighteenth door is a drawing of the great tree in the hall, with a hundred tiny stick figures dancing around it. One of them, you notice, has your hair. You did not know you were famous in the Land of Snow."],
        19 => ['gold', 300, "`&Behind the nineteenth door, nestled in straw, is a lacquered box. Inside it lie `^%s gold`& and a note in Lady Yukiko's elegant hand: `#\"For the journey home, whenever that may be.\""],
        20 => ['turns', 2, "`&Behind the twentieth door is a small vial of something that glows faintly blue. It tastes of mint and lightning. You feel as if you could run up the side of a mountain. (You gain `@%s`& forest fights.)"],
        21 => ['buff', 0, "`&The twenty-first door is the longest night of the year, and behind it burns a little candle that will not go out, however hard you blow. Its light seems to follow you, keeping the darkness at bay."],
        22 => ['gems', 2, "`&Behind the twenty-second door, wrapped in white silk, lie `%%s gems`&, cut in the shape of snowflakes. The craftsmen of the Land of Snow are famous for them."],
        23 => ['heal', 100, "`&Behind the twenty-third door, there is only warmth: a gentle, golden warmth that flows out over you and fills you right to your fingertips. Every ache and bruise of the year melts away. (You recover `@%s`& hitpoints.)"],
        24 => ['gems', 3, "`&The twenty-fourth door is the largest of them all, painted with the great tree and every one of the stars above it. When you open it, the whole hall goes quiet, and the crystals on the tree ring all at once, softly, like a hundred tiny bells.`n`nBehind the door lies a box wrapped in red and gold. Inside are `%%s gems`&, a sprig of pine, and a card: `#\"Merry Christmas, from the Land of Snow, and from all of us at Shinobi Legends. Thank you for being with us this year.\"`n`n`&Lady Yukiko smiles at you from across the hall, and for a moment it is not cold at all."],
    ];
    public const ADVENT_BUFF = "`@Advent Spirit";
    public const ADVENT_BUFF_ROUND = "`@The warmth of the season strengthens your blows.";
    public const ADVENT_BUFF_END = "`@The Advent spirit settles quietly in your heart.";
    /** %s: gold. */
    public const ADVENT_DOOR24_GOLD = "`n`n`&Under the box, almost forgotten, is a purse with `^%s gold`&.";

    // The Ice Garden.

    public const GARDEN_TITLE = "`c`b`lI`Lce `&Garden`b`c`n";
    public const GARDEN = "`&The Ice Garden is a courtyard open to the sky, and the snow falls here slowly, in big, soft flakes. Blocks of clear ice the size of oxen stand in rows, and around them sculptors from every village work with chisels, kunai and, in one case, a very small and very careful fire jutsu. Finished sculptures stand along the walls: dragons, cranes, a perfect copy of the Hokage Monument the size of a cart, and something that might be a cat or might be a cloud.";
    /** %s, %s, %s: player name, motif, score. */
    public const GARDEN_BEST = "`n`n`&On a pedestal in the centre of the garden, lit by lanterns, stands the finest sculpture of this winter: %2\$s`&, carved by %1\$s`&. A small plaque says the judges gave it `^%3\$s`& points.";
    public const GARDEN_NO_BEST = "`n`n`&The pedestal in the centre of the garden is still empty. Nobody has carved anything worthy of it yet this winter.";
    /** %s: gold cost. */
    public const GARDEN_OFFER = "`n`n`&An old sculptor with a white beard and fingers stiff from the cold looks you up and down. `#\"You want to try? A block of ice and the loan of my tools, `^%s gold`#. What will you carve?\"";
    public const GARDEN_DONE = "`n`n`&Your hands are still numb from your last sculpture. Better leave the chisel for today.";
    public const GARDEN_GOLD = "`n`n`&You do not have enough gold for a block of ice. The old sculptor shrugs and goes back to his dragon.";
    public const NAV_CARVE = "Carve %s";
    public const GARDEN_TALK = "Talk with the sculptors";

    /** @var array<string, string> Motif key => name. */
    public const MOTIFS = [
        'dragon' => 'an ice dragon',
        'toad' => 'a great summoning toad',
        'crane' => 'a pair of dancing cranes',
        'hokage' => 'the face of a Hokage',
        'fox' => 'a nine-tailed fox',
        'snowman' => 'a snowman in a forehead protector',
    ];

    /** %s: motif. */
    public const CARVE = "`&You pay the old sculptor, pick up his chisel and set to work on %s`&. Chips of ice fly; your breath freezes on your scarf; the light changes twice. When you finally step back, your hands are blue and the sculpture is finished.`n`n";
    /** @var list<array{0:int,1:string}> Lowest score for a verdict, then the verdict (%s: score). */
    public const CARVE_VERDICT = [
        [95, "`&The garden goes quiet. Sculptors put down their tools and come to look. The old man walks around your work three times, then takes off his hat. `#\"In forty winters,\"`& he says hoarsely, `#\"I have not seen better.\"`& The judges give it `^%s`& points."],
        [75, "`&People stop to admire it. A little girl tugs her mother's sleeve and points. The old sculptor nods slowly. `#\"Good hands. Patient hands.\"`& The judges give it `^%s`& points."],
        [45, "`&It is... recognisable. Mostly. The old sculptor tilts his head one way, then the other. `#\"It has spirit,\"`& he says diplomatically. The judges give it `^%s`& points."],
        [0, "`&You step back and look at it, and it looks back at you, which it should not be able to do. The old sculptor pats you on the shoulder. `#\"The first one I made fell over,\"`& he says. `#\"At least yours is standing.\"`& The judges give it `^%s`& points, which is generous."],
    ];
    public const CARVE_RECORD = "`n`n`^Your sculpture is the best of the winter so far!`& The judges carry it to the pedestal in the centre of the garden, and a crowd gathers to look at it.";
    /** %s: gold. */
    public const CARVE_PRIZE_GOLD = "`n`&The sculptors' guild pays you a prize of `^%s gold`&.";
    public const CARVE_PRIZE_GEM = "`n`&The sculptors' guild awards you a `%gem`& for a masterpiece.";

    // The Banquet Hall.

    public const BANQUET_TITLE = "`c`b`lB`Lanquet `&Hall`b`c`n";
    public const BANQUET = "`&The Banquet Hall is the warmest room in the palace. Long tables of dark wood run its length, laden with steaming pots and plates, and a fire roars in a hearth at each end. Shinobi from every village sit shoulder to shoulder, passing dishes and arguing cheerfully about whose village makes the best dumplings. A troupe of musicians plays in the corner, and the cook, a round man with a ladle the size of a sword, shouts at anyone who looks hungry.";
    public const BANQUET_OFFER = "`n`n`&The cook spots you. `#\"You! Sit! Eat! One dish per guest per day, the palace's treat. What will it be?\"";
    public const BANQUET_DONE = "`n`n`&You have already eaten today, and the cook, kindly but firmly, pushes you back from the table. `#\"Tomorrow! I want to see you tomorrow!\"";
    public const BANQUET_TALK = "Join the conversation at the tables";

    /**
     * The dishes: [nav name, text, buff name, buff fields].
     *
     * @var array<string, array{0:string,1:string,2:string,3:array<string,mixed>}>
     */
    public const DISHES = [
        'nabe' => [
            "A bowl of hot pot",
            "`&The cook ladles you a bowl of hot pot, thick with mushrooms, tofu and slices of mountain boar, in a broth so rich it is almost a meal on its own. With every spoonful, the warmth sinks deeper into your bones, and you feel your strength return.",
            "`@Warm Belly",
            ['regen' => 3, 'rounds' => 20, 'roundmsg' => "`@The warmth of the hot pot mends your wounds."],
        ],
        'mochi' => [
            "Grilled rice cakes",
            "`&Two rice cakes come straight off the grill, puffed and golden, brushed with sweet soy sauce. They are chewy and crisp and far too hot, and you eat them anyway. A fierce energy fills you.",
            "`@Mochi Strength",
            ['atkmod' => 1.05, 'rounds' => 20, 'roundmsg' => "`@Rice-cake power lends weight to your blows."],
        ],
        'zenzai' => [
            "Sweet red bean soup",
            "`&The cook places a lacquered bowl of sweet red bean soup in front of you, with two soft rice dumplings floating in it. It tastes like every winter of your childhood at once. You feel calm, steady and very hard to knock over.",
            "`@Steady Heart",
            ['defmod' => 1.05, 'rounds' => 20, 'roundmsg' => "`@A warm, steady calm guards you."],
        ],
    ];
    public const DISH_WEAR_OFF = "`@The good meal wears off, and you are hungry again.";

    // The Lantern Tower.

    public const TOWER_TITLE = "`c`b`lL`Lantern `&Tower`b`c`n";
    public const TOWER = "`&The spiral stair winds up and up through the tallest tower of the palace, until your legs ache and the air grows thin. At the top is a round room with no glass in its windows, and the wind blows through it, cold and clean. Hundreds of paper lanterns hang from the beams, each one with a wish written on it, swaying gently. From the windows you can see the whole Land of Snow: white mountains, frozen lakes, and far, far away, the faint smoke of villages.";
    public const TOWER_OFFER = "`n`n`&A girl of about ten, in a coat three sizes too big for her, sits on a stool with a stack of blank lanterns and a pot of ink. `#\"Want to send a wish? You write it, I fold it, and the wind takes it to wherever wishes go. One per day. Those are the rules. I made them.\"";
    public const TOWER_DONE = "`n`n`&The lantern girl shakes her head. `#\"You already sent one today. The wind needs time to deliver them, you know.\"";
    public const NAV_LANTERN = "Send a wish lantern";
    /** @var list<string> */
    public const LANTERN = [
        "`&You write your wish on the thin paper, and the girl folds it into a lantern with quick, clever fingers. She lights the candle inside, and together you hold it out of the window. For a moment it hangs there, glowing; then the wind lifts it, and it floats away over the mountains, smaller and smaller, until it is just another star. You feel lighter, as if you had put down something heavy.",
        "`&The girl reads your wish upside down while she folds it, then looks at you very seriously. `#\"That is a good one,\"`& she says. `#\"Those come true more often.\"`& The lantern drifts out of the window and joins a hundred others, a river of little lights flowing towards the south.",
    ];
    public const LANTERN_BUFF = "`#Hope";
    public const LANTERN_BUFF_ROUND = "`#A small, stubborn hope keeps you on your feet.";
    public const LANTERN_BUFF_END = "`#Your lantern has reached wherever wishes go.";
    public const TOWER_BOARD = "Read and write on the wish wall";
    public const TOWER_BOARD_VERB = "wishes";

    // The Hot Spring.

    public const SPRING_TITLE = "`c`b`lH`Lot `&Spring`b`c`n";
    public const SPRING = "`&In a corner of the palace courtyard, ringed by black rocks and snow-laden pines, a hot spring steams in the cold air. Snow falls on the water and vanishes without a sound. A few guests sit up to their chins in it, eyes closed, faces red, looking as if they have forgotten that wars, missions and Hokage exist at all. A wooden sign reads: `#\"One soak per guest per day. No water jutsu. This means you, Mist ninja.\"";
    public const NAV_SOAK = "Take a soak";
    public const SOAK = "`&You lower yourself into the water, and the heat closes over you like an embrace. Every bruise, every strained muscle, every ache of the long year slowly lets go. Snowflakes land in your hair. Somewhere a bird sings. When you finally climb out, steaming like a dumpling, you feel brand new.";
    /** %s: hitpoints healed. */
    public const SOAK_RESULT = "`n`n`@You recover %s hitpoints.";
    public const SOAK_FULL = "`n`n`@You were not hurt, but it was lovely all the same.";
    public const SPRING_DONE = "`n`n`&You have already had your soak today. Your fingers are still wrinkled from it.";
    public const SPRING_TALK = "Chat with the bathers";

    // The Frost Cellar.

    public const CELLAR_TITLE = "`c`b`lF`Lrost `&Cellar`b`c`n";
    public const CELLAR = "`&The stair down to the cellar is narrow, steep and slippery, and the lamps along it burn with a cold blue flame that gives no warmth. At the bottom is a vault of black ice, so dark and so still that you can hear your own heartbeat. Frost creeps across the floor towards your feet.`n`nIn the middle of the vault, sitting on a chair of ice, is a woman in a white kimono with long black hair hanging over her face. She is so still that for a moment you think she is a statue. Then she lifts her head, and her eyes are the pale blue of a glacier.`n`n`#\"A guest,\"`& says the Yuki-onna, the snow woman, and her breath frosts the air. `#\"How nice. Guests are so warm. Answer my riddles, little shinobi, and I will reward you. Answer wrongly, and I will keep a little of your warmth for myself.\"";
    /** %s: riddles left today. */
    public const CELLAR_LEFT = "`n`n`7(She will ask you `&%s`7 more riddle(s) today.)";
    public const CELLAR_DONE = "`n`n`&The snow woman yawns, a little cloud of frost. `#\"Enough riddles for today. Come back tomorrow, when you are warm again.\"";
    public const NAV_RIDDLE = "Ask for a riddle";
    public const CELLAR_TALK = "Whisper with the other brave souls";
    /** %s: the riddle. */
    public const RIDDLE_ASK = "`&The Yuki-onna leans forward, and the cold rolls off her like mist.`n`n`#\"%s\"";
    public const NAV_WALK_AWAY = "Back away slowly";
    /** %s: gold. */
    public const RIDDLE_GOLD = "`&The snow woman smiles, and it is like sunlight on a glacier. `#\"Clever.\"`& She opens her hand, and `^%s gold`& coins, rimed with frost, fall into yours.";
    public const RIDDLE_GEM = "`&The snow woman laughs, a sound like icicles breaking. `#\"Very clever.\"`& She breathes on her palm, and a `%gem`& forms there out of nothing, cold and perfect. She gives it to you.";
    /** %s: hitpoints lost. */
    public const RIDDLE_WRONG = "`&The snow woman shakes her head slowly. `#\"No.\"`& She reaches out and touches your cheek with one finger, and the cold goes straight into your bones. Your teeth chatter, your fingers go white. You lose `\$%s`& hitpoints.";
    public const RIDDLE_GONE = "`&The snow woman blinks at you. `#\"I did not ask you anything. Do not be greedy.\"";

    /**
     * The snow woman's riddles: question, three answers, the right one.
     *
     * @var list<array{0:string,1:list<string>,2:int}>
     */
    public const RIDDLES = [
        ['I fall but never break. I can be packed into a ball but never bounce. The warmer you are, the faster I flee. What am I?', ['A tear', 'Snow', 'A kunai'], 1],
        ['I have no eyes, yet I see the whole village from above. Four faces, carved in stone. What am I?', ['The Hokage Monument', 'The Valley of the End', 'An owl'], 0],
        ['The more you take from me, the bigger I become. What am I?', ['A debt', 'A hole', 'A scroll'], 1],
        ['I am a shinobi\'s best friend in the dark, I am thrown but never caught, and I return to no one. What am I?', ['A shuriken', 'A crow', 'A promise'], 0],
        ['What has hands but cannot clap, and a face but cannot frown?', ['A clock', 'A puppet', 'A statue'], 0],
        ['I am born in the mountains, I run all my life, and I die in the sea. What am I?', ['A messenger', 'A river', 'A deer'], 1],
        ['I can be cracked, made, told and played. What am I?', ['A joke', 'An egg', 'A seal'], 0],
        ['Everyone in the five nations has one; it follows you in the sun and leaves you in the dark. What am I?', ['A shadow', 'A rival', 'A cloak'], 0],
        ['I have a neck but no head, and I wear a cap. What am I?', ['A bottle', 'A snowman', 'A tree'], 0],
        ['The man who made me did not want me. The man who bought me did not need me. The man who uses me does not know it. What am I?', ['A coffin', 'A mask', 'A summoning contract'], 0],
    ];
    public const LEAVE_CELLAR = "`&You back away from the snow woman, very slowly, without turning your back on her. She watches you go, smiling.";

    // Talk sections.

    public const TALK_INTRO = "`n`n`&The hall is full of guests talking, laughing, and comparing frostbite.`n";
    public const TALK_PROMPT = "Speak";
    public const TALK_VERB = "says";
}
