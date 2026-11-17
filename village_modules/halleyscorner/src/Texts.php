<?php

declare(strict_types=1);

namespace Shinobi\Modules\HalleysCorner;

/**
 * Everything Halley's Corner says.
 *
 * English source text for output(), which passes it through the translator.
 * Where an entry is a list, one variant is picked at random. `%s` in a text
 * is Halley's name unless a comment says otherwise; output() is always called
 * with her name first.
 *
 * The world is the Hidden Leaf in the years after Naruto's return: Tsunade
 * is Hokage, the Akatsuki are on the move, and travellers carry news from
 * all five great nations through the Corner's door.
 */
final class Texts
{
    public const TITLE = "`b`i`c%s`7's `tCorner`c`i`b`n";

    // Arriving, by the time of day.

    /** @var array<string, list<string>> */
    public const ARRIVAL = [
        'morning' => [
            "`3The bell above the door gives a sleepy jingle as you step into %s`3's Corner. The shutters are only half open, and the morning sun lays long golden stripes across the floorboards. It smells of fresh rice, grilled fish and strong green tea. A handful of early travellers sit hunched over their bowls, packs already strapped and sandals already laced, and nobody talks much yet.",
            "`3Morning mist still clings to the village square when you push open the door of %s`3's Corner. Inside, a kettle sings on the stove, and someone has opened the back window so that the cool air carries in the sound of the market setting up: wooden stalls, shouted prices, a cart that needs oiling.",
        ],
        'day' => [
            "`3%1\$s`3's Corner is busy at this hour. Genin teams back from D-rank missions argue over who caught the cat, a merchant from the Land of Waves haggles over the price of a room, and the noon light falls warm through the paper screens. Over the hubbub you can hear the clink of glasses and %1\$s`3's laugh from behind the bar.",
            "`3The door swings shut behind you, and the heat and noise of the market fall away. %s`3's Corner is cool and dim at midday, with the windows shaded by bamboo blinds. A pair of shinobi from the Hidden Sand sit by the wall, still in their travelling cloaks, and the smell of sesame and ginger drifts out of the kitchen.",
        ],
        'evening' => [
            "`3Paper lanterns glow in red and gold along the eaves of %s`3's Corner as evening settles over the village. Inside, someone is plucking a shamisen in the corner, not very well, and nobody minds. The tables are full of travellers telling each other where they have been, each story a little taller than the last.",
            "`3By the time you step through the door, the lamps are lit and the Corner is warm with voices. Through the windows the Hokage Monument catches the last red light of the sun. %s`3 moves between the tables with a tray balanced on one hand, and somehow every glass is full before it is empty.",
        ],
        'night' => [
            "`3It is late. Most of the chairs are already upturned on the tables, and only one lamp still burns above the bar. %s`3 is polishing glasses that do not need polishing, humming something old and slow. Outside the village sleeps; in here, the night belongs to those who cannot.",
            "`3The streets are empty and the Corner is almost dark, but the door is never locked for travellers. %s`3 looks up from a ledger by candlelight, sees you, and simply reaches for a clean glass without a word. Somewhere above, the old house creaks in the wind.",
        ],
    ];

    /** The closing line of the arrival, every time. */
    public const ARRIVAL_SEAT = "`n`n`3Most people come here for the house drink, which is said to wake the wanderlust in anyone and quiet the urge to fight. Feeling welcome, you take a seat.";

    // Halley's greeting, by how well she knows you. The second %s is the player's name.

    /** @var array<string, list<string>> */
    public const GREETING = [
        'stranger' => [
            "`n`n%s`3 looks up as you come in and gives you a quick, measuring glance: the way your hands move, where your eyes go first, how you carry your weapon. Then she smiles. `#\"New face. Sit wherever you like, traveller. Nobody fights in my Corner.\"",
            "`n`nBehind the bar, %s`3 tilts her head. `#\"First time? Welcome. Shoes may stay on, kunai stay in their pouch, and the root beer is better than anything you will find in the Land of Fire. Those are the rules.\"",
        ],
        'known' => [
            "`n`n%s`3 recognises you and raises a hand in greeting. `#\"Back again? The road has been treating you well, I see. Or at least it has not finished you off yet.\"",
            "`n`n%s`3 gives you a nod from behind the bar, the kind she saves for faces she has seen before. `#\"Your usual table is free, if you want it.\"",
        ],
        'regular' => [
            "`n`n`#\"%2\$s!\"`3 %1\$s`3 calls across the room the moment you step in, loud enough that a few heads turn. `#\"I was just wondering when you would show up again. Sit, sit. You look like you have walked halfway to Suna.\"",
            "`n`n%s`3 is already pouring before you reach the bar. `#\"I know, I know, %s. Do not tell me where you have been until you have had something to drink.\"",
        ],
        'friend' => [
            "`n`n%s`3 waves you over to the little table by the window, the one with the view of the Hokage Monument and the scratch in the wood shaped like a leaf. Nobody else ever sits there. `#\"Your seat, %s. I kept it warm.\"",
            "`n`n%s`3 comes out from behind the bar to greet you, wiping her hands on her apron, and squeezes your shoulder. `#\"%s. Good. I always sleep better when I know you made it back.\"`3 She steers you to your window seat as if you were family.",
        ],
    ];

    /** A short line once per game day, after the greeting, on the first visit of the day. */
    public const FIRST_TODAY = "`n`3She sets a small cup of plain barley tea in front of you, on the house, as she does for every traveller's first visit of the day.";

    // Who else is here. One scene per visit.

    /** @var list<string> */
    public const PATRONS = [
        "`7At the long table, an old shinobi with a face like a walnut has spread a map of the five great nations across three plates. He is tracing a route through the Land of Rivers with a chopstick and muttering about the bridges that are not there any more.",
        "`7A travelling merchant from the Land of Waves is trying to sell a box of very ordinary seashells to anyone who will listen, swearing on his grandmother that they were picked up from beneath the Great Naruto Bridge itself.",
        "`7A fat ginger cat sleeps on the windowsill in a patch of light. According to the little wooden sign hanging from its collar, its name is `&Kunai`7, and it is the only one in the Corner allowed to bite.",
        "`7Two young chuunin sit by the wall, back from a long mission and too tired to talk. Their sandals are white with desert dust. One of them has fallen asleep sitting up, his chin on his chest, and his partner is carefully not waking him.",
        "`7A woman in the grey cloak of a messenger sits alone with a sealed scroll on the table in front of her. She drinks her tea slowly and keeps glancing at the door, as if she is waiting for someone who is late.",
        "`7A group of genin is crowded around a single bowl of dango, counting the pieces very carefully to make sure everybody gets the same number. Their sensei, reading a small orange book at the next table, pretends not to notice them.",
        "`7A broad-shouldered man with a Hidden Stone headband has his arm in a sling. He is telling the table next to him, at length, that the other fellow looks much worse.",
        "`7Near the stove, a toad the size of a small dog sits on a cushion, solemnly drinking sake from a saucer. Nobody seems to find this strange, so you decide not to either.",
    ];

    // The drinks.

    public const MENU_INTRO = "`n`n`3A slate board above the bar lists the drinks of the house in neat, round handwriting:`n";
    /** %s, %s, %s: owner, PvP cost, travels gained. */
    public const MENU_ROOTBEER = "`n`t- `^%s`^'s Root Beer`t: costs `\$%s`t PvP, gives `@%s`t travel(s).";
    /** %s, %s: PvP cost, travels gained. */
    public const MENU_BREW = "`n`t- `^Wanderer's Brew`t: costs `\$%s`t PvP, gives `@%s`t travels. Once a day.";
    /** %s: PvP cost. */
    public const MENU_TEA = "`n`t- `^Still Water Tea`t: costs `\$%s`t PvP, mends wounds and calms the mind. Once a day.";
    public const MENU_WATER = "`n`t- `^Water`t: free. %s`t's opinion of you: also free.";
    /** %s, %s: drinks had today, daily limit. */
    public const MENU_LIMIT = "`n`n`7(You have had `&%s`7 of `&%s`7 travel drinks today.)";
    public const NO_CITIES = "`n`n`7The travel drinks are off the menu for now: nobody is travelling anywhere at the moment.";

    public const NAV_ROOTBEER = "Order %s`0's Root Beer (%s PvP)";
    public const NAV_BREW = "Order the Wanderer's Brew (%s PvP)";
    public const NAV_TEA = "Order Still Water Tea (%s PvP)";
    public const NAV_WATER = "Ask for a glass of water";

    /** @var list<string> */
    public const ROOTBEER = [
        "`3You ask %s`3 for a glass of her root beer. She takes a dark bottle from beneath the bar, pulls the cork with her thumb and pours it slowly, so the foam rises right to the rim and not a hair further.`n`nIt tastes of vanilla, birch and something warm you cannot name. With every sip the tension drains out of your shoulders, and the roads beyond the village gates start to call: the dunes of Suna, the bridges of the Land of Waves, the misty coasts of the Hidden Mist.",
        "`3%s`3 slides a frosted glass across the bar before you have finished asking. `#\"Slowly,\"`3 she warns, `#\"it bites back.\"`3 It does. The first mouthful fizzes in your nose, and the second makes your feet itch. Suddenly the thought of picking a fight seems silly, and the thought of the open road seems like the best idea you have had all week.",
        "`3%s`3 pours you a root beer from the barrel in the back, the one with the faded Land of Tea stamp. You drink, and for a moment you are somewhere else entirely: a mountain path above the clouds, wind in your hair, and nowhere you have to be by nightfall.",
        "`3\"The usual,\" you say, and %s`3 laughs and pours. The root beer is cold and dark and sweet. By the bottom of the glass, your fists have unclenched all on their own, and you find yourself idly planning a route to the Hidden Waterfall.",
    ];
    /** %s, %s: travels gained, PvP lost. */
    public const ROOTBEER_RESULT = "`n`n`%You gain %s travel(s), and lose %s PvP fight(s) for today.";
    /** %s: PvP left. */
    public const PVP_LEFT = "`n`3You have `&%s`3 PvP fights left now.";

    /** @var list<string> */
    public const BREW = [
        "`3%s`3 raises an eyebrow when you order the Wanderer's Brew. She fetches a squat clay jug from a locked cupboard, pours a single thimbleful into a cup no bigger than an egg, and pushes it over. `#\"All at once,\"`3 she says. `#\"And then sit down for a moment.\"`n`nThe brew burns like a summer road at noon. Then it is gone, and your legs feel as if they could carry you from here to the Land of Lightning without stopping.",
        "`3The Wanderer's Brew comes in a little cup carved from cedar wood. %s`3 watches you drink it with a strange, fond look on her face. `#\"My grandmother made that for the couriers who ran the mountain passes,\"`3 she says. `#\"They used to say it made the road come to them.\"`n`nIt tastes of pine smoke and honey, and when you put the cup down, the walls of the Corner seem much too close.",
    ];
    /** %s, %s: travels gained, PvP lost. */
    public const BREW_RESULT = "`n`n`%You gain %s travels, and lose %s PvP fight(s) for today.";
    public const BREW_DONE = "`3%s`3 shakes her head and keeps the jug in its cupboard. `#\"One a day. Any more and you would run straight past the village gate and into the sea.\"";

    /** @var list<string> */
    public const TEA = [
        "`3%s`3 brews the Still Water Tea herself, slowly, in a black iron pot. She does not hurry, and somehow watching her makes you stop hurrying too. The tea is pale green and smells of rain on moss. With every sip your breathing slows, and the aches of the road ease out of your bones.",
        "`3The Still Water Tea arrives in a plain white cup, steaming gently. `#\"Do not think while you drink it,\"`3 %s`3 says. `#\"Just drink.\"`3 You try. The warmth spreads from your chest to your fingertips, and the noise of the Corner seems to come from very far away.",
    ];
    /** %s: hitpoints healed (output without Halley). */
    public const TEA_RESULT = "`n`n`%You recover %s hitpoints, and a deep calm settles over you.";
    public const TEA_DONE = "`3%s`3 smiles but does not reach for the pot. `#\"Calm is not something you can drink twice in one day. Come back tomorrow.\"";
    public const TEA_BUFF_NAME = "`2Still Water Calm";
    public const TEA_BUFF_ROUND = "`2Your mind is still, and you see the blow coming before it lands.";
    public const TEA_BUFF_END = "`2The calm of the tea slowly fades.";

    /** @var list<string> */
    public const WATER = [
        "`3%s`3 looks at you for a long moment. Then she fills a glass from the tap, sets it down very gently, and says, `#\"Water. In my Corner. Brave.\"`3 A few patrons snicker.",
        "`3You ask for water. %s`3 pours it with a perfectly straight face and then, as you drink, leans over and loudly tells the next table that you are `#\"saving yourself for the road\"`3. The table applauds.",
        "`3%s`3 hands you a glass of water with a slice of lemon in it. `#\"Since it is you,\"`3 she says, `#\"it comes with fruit.\"`3 It is, to be fair, very good water.",
    ];

    public const LIMIT_REACHED = "`3%s`3 covers your glass with her hand. `#\"That is enough for one day. If you leave the village any faster, the gate guards will think you are running from something.\"";
    public const NOT_ENOUGH_PVP = "`3%s`3 looks at you and shakes her head. `#\"You have no fight left in you to trade. Go and rest.\"";

    // The stamp card.

    /** %s, %s: stamps, stamps needed. */
    public const CARD = "`n`n`7Your stamp card from the Corner is tucked into your pouch. It shows `&%s`7 of `&%s`7 little leaf stamps.";
    /** %s, %s: Halley, bonus travels. */
    public const CARD_FULL = "`n`n`@%s`@ takes your card, sees the last stamp, and tears it in half with a flourish. `#\"Full card! This one is on the house.\"`@ She hands you a fresh card, and you feel the road calling just a little louder. You gain `^%s`@ extra travel(s).";

    // Talking to Halley.

    public const NAV_GOSSIP = "Ask %s`0 for news";
    public const NAV_PAST = "Ask %s`0 about herself";
    public const NAV_STORY = "Listen to the storyteller by the fire";
    public const NAV_TALK = "Converse with patrons";
    public const NAV_BOARD = "Read the travel board";
    public const NAV_POSTCARD = "Write a postcard";

    /** @var list<string> */
    public const GOSSIP = [
        "`#\"A courier came through from the Hidden Sand this morning,\"`3 %s`3 says, lowering her voice. `#\"He said the desert roads are quiet again. Quiet is good. Quiet means the bandits went somewhere else.\"",
        "`#\"Have you heard?\"`3 %s`3 leans on the bar. `#\"There were men in black cloaks with red clouds seen near the border of the Land of Rivers. Travellers are saying to keep to the main roads and travel in daylight. I would listen, if I were you.\"",
        "`#\"The Fifth Hokage lost at cards again last night,\"`3 %s`3 says with a perfectly straight face. `#\"Whole gambling hall heard about it. If you want a favour from the Hokage's office, this is not the week to ask.\"",
        "`#\"If you are heading to the Land of Waves,\"`3 %s`3 says, `#\"cross the Great Naruto Bridge in the morning. The fog comes up from the sea by afternoon and you will not see your own feet.\"",
        "`#\"Someone told me there are hot springs in the Land of Hot Water that cure everything from a twisted ankle to a broken heart,\"`3 %s`3 says. `#\"I have never been. Maybe one day. Tell me if it is true.\"",
        "`#\"The road to the Hidden Cloud is murder this time of year,\"`3 %s`3 says. `#\"Thunderstorms every afternoon. Leave early, find shelter before noon. Do not stand under the tallest tree, whatever your sensei taught you about high ground.\"",
        "`#\"A little advice from someone who has seen a lot of travellers come and go,\"`3 %s`3 says, refilling your glass. `#\"The ones who make it back are not the strongest. They are the ones who know when to rest.\"",
        "`#\"They are holding the Chuunin Exams again soon,\"`3 %s`3 says. `#\"Every inn in the village will be full of foreign genin. I already hid the good sake.\"",
        "`#\"Old Teuchi from the ramen stand swears a blond boy ate thirteen bowls in one sitting last week,\"`3 %s`3 says, shaking her head. `#\"Thirteen. I do not know whether to be impressed or worried.\"",
        "`#\"The Hidden Mist has a new Mizukage, they say, and the borders there are opening up again,\"`3 %s`3 says. `#\"Ten years ago I would not have sent my worst enemy down that road. Times change.\"",
    ];

    /**
     * Halley's own story, in chapters; each needs this many visits (counted once per day).
     *
     * @var list<array{0:int,1:string}>
     */
    public const PAST = [
        [1, "`3%s`3 laughs when you ask. `#\"Me? There is not much to tell. I pour drinks, I listen, I throw out anyone who draws a blade. That is all you need to know about me.\"`3 But something in the way she says it makes you think there is more."],
        [5, "`3%s`3 is quiet for a moment, turning a glass in her hands. `#\"I was a kunoichi once,\"`3 she says at last. `#\"Chuunin. Not a good one, not a bad one. I could throw a kunai through a keyhole at thirty paces. Does that surprise you?\"`3 She smiles. `#\"It surprises me too, these days.\""],
        [10, "`#\"I served in the Third Great Shinobi War,\"`3 %s`3 says, very softly so the other tables cannot hear. `#\"Three of us went out on a mission to the Land of Rain. One of us came back. After that, I could not hold a blade without my hands shaking. So I put it down, and I never picked it up again.\""],
        [20, "`#\"The root beer is my grandmother's recipe,\"`3 %s`3 tells you, nodding towards the old barrel with the Land of Tea stamp. `#\"She ran a teahouse on a mountain pass, where the couriers stopped. She used to say a person who wants to see the world does not want to destroy it. When I came home from the war, that was the only thing anybody said to me that made sense.\""],
        [35, "`#\"I opened the Corner the year after the Nine-Tails attacked the village,\"`3 %s`3 says. `#\"Half of Konoha was rubble. People needed somewhere to sit that was not a hospital or a funeral. I had a few tables, a barrel of root beer and a rule: nobody fights in here. It turns out that is a rule people are very grateful for.\""],
        [50, "`3%s`3 reaches under the bar and lays something in front of you: an old, scratched forehead protector with the Leaf symbol, the cloth faded almost white. `#\"Mine,\"`3 she says. `#\"I keep it to remember why I took it off.\"`3 She puts it away again, carefully, as if it might break."],
        [75, "`#\"You know why I keep this place?\"`3 %s`3 asks, looking around at the travellers, the tired genin, the old men with their maps. `#\"Because every one of them chose to come in here instead of looking for a fight. Every glass of root beer is one less grave on the hill. That is my war now.\"`3 She clinks her glass against yours. `#\"Thank you for being one of them.\""],
    ];
    public const PAST_LATER = "`3%s`3 smiles and shakes her head. `#\"I have told you enough for now. Keep coming back, and maybe I will tell you more.\"";
    public const PAST_ALL = "`3%s`3 laughs. `#\"You know all my secrets now. Well. Almost all. A woman has to keep one or two.\"";

    // The storyteller by the fire.

    public const STORY_INTRO = "`7By the fireplace sits an old man with a white beard down to his belt and a pipe that never seems to go out. When he sees you looking, he pats the bench beside him.`n`n";

    /** @var list<string> No placeholders. */
    public const STORIES = [
        "`&\"Long, long ago,\"`7 the old man begins, `&\"before there were villages, before there were headbands, there was a monk who carried a power so great it could have broken the world. They called him the Sage of the Six Paths. He did not use that power to rule. He used it to teach. He gave people the gift of chakra so they could understand one another.\"`7 He puffs on his pipe. `&\"Took them about five minutes to start using it to fight instead.\"",
        "`&\"You have seen the Valley of the End?\"`7 the old man asks. `&\"Two great stone statues, facing each other across a waterfall. Hashirama, our First Hokage, and Madara Uchiha, his oldest friend. They fought there, they say, a battle so terrible it carved the valley itself. And the river still runs between them, because neither of them could ever cross over to the other side.\"",
        "`&\"When the Nine-Tailed Fox came,\"`7 the old man says, and the table goes quiet, `&\"the night turned red. I was on the wall. I saw the Fourth Hokage standing on the great toad's head, his white coat burning in the firelight. He gave everything that night. Everything. And the village stood.\"`7 He looks into the fire for a long time.",
        "`&\"There is a sannin,\"`7 the old man says with a wink, `&\"who writes the most shameful books in all the five nations. Orange covers. You have seen them. But I tell you, that same man can summon a toad the size of the Hokage's tower and has faced down things that would make your hair turn white. Never judge a shinobi by his reading habits.\"",
        "`&\"In the Land of Waves,\"`7 the old man says, `&\"there was a bridge builder who would not stop building, even when a gang of thugs threatened to kill him. A little team of Leaf genin protected him. When the bridge was finished, the people named it after the loudest of those children. That is how you earn a bridge, young one. You refuse to give up.\"",
        "`&\"Tea ceremonies in the Land of Tea,\"`7 the old man says, `&\"used to take three days. Three days! The tea master would not even boil the water until everyone had stopped arguing. Some wars ended in those teahouses, because nobody could stay angry for three days without a single cup.\"",
        "`&\"They say the Hidden Mist was once called the Bloody Mist,\"`7 the old man says, lowering his voice. `&\"To graduate from their academy, the students had to fight each other. To the death. Then one year a boy who was not even a student walked in and killed every graduate. After that, they changed the rules.\"`7 He shrugs. `&\"Sometimes change comes from strange places.\"",
        "`&\"Do you know why we call it the Will of Fire?\"`7 the old man asks. `&\"Because a fire does not care who lit it. It warms everyone who sits near it. That is what the First Hokage wanted for the village. Not a fortress. A hearth.\"`7 He nods at the fireplace, then at the woman behind the bar. `&\"Some people understand that better than others.\"",
    ];

    // The travel board.

    public const BOARD_INTRO = "`7A corkboard hangs beside the door, covered in notes pinned with old kunai: travellers looking for company on the road, offering a spare bedroll, or warning about a washed-out bridge.`n";
    public const BOARD_PROMPT = "Pin a note to the travel board";
    public const BOARD_VERB = "pins a note";
    public const TALK_INTRO = "`n`n`@Converse with your fellow travellers.`n";
    public const TALK_PROMPT = "Join the conversation";
    public const TALK_VERB = "chats";

    // Postcards.

    /** %s, %s, %s: Halley, gold cost, postcards left today. */
    public const POSTCARD_INTRO = "`3By the window stands a little wooden rack of postcards: hand-painted scenes of the five great nations, each signed in the corner with a small leaf. %s`3 sells them for `^%s gold`3 each, and she will see that the post-hawk carries it. You can send `&%s`3 more today.`n`n";
    /** @var array<string, string> Motif key => description shown on the card. */
    public const MOTIFS = [
        'monument' => 'the Hokage Monument at sunset',
        'desert' => 'the red dunes around the Hidden Sand',
        'bridge' => 'the Great Naruto Bridge in morning fog',
        'mist' => 'the misty harbour of the Hidden Mist',
        'cloud' => 'the thunder peaks of the Land of Lightning',
        'corner' => 'the Corner itself, with its lanterns lit',
    ];
    public const POSTCARD_TO = "Who should receive it (character login name)?";
    public const POSTCARD_MOTIF = "Which picture?";
    public const POSTCARD_TEXT = "What do you write? (up to %s characters)";
    public const POSTCARD_SEND = "Send the postcard";
    public const POSTCARD_NOT_FOUND = "`3%s`3 looks through her list of addresses, then shakes her head. `#\"I do not know anyone by that name. Are you sure you spelled it right?\"";
    public const POSTCARD_SELF = "`3%s`3 raises an eyebrow. `#\"Sending a postcard to yourself? I know you are lonely on the road, but really.\"";
    public const POSTCARD_EMPTY = "`3%s`3 turns the card over. `#\"You have not written anything. A blank postcard just says 'I forgot about you' very politely.\"";
    public const POSTCARD_GOLD = "`3You count your coins and come up short. %s`3 pats your hand. `#\"Another day.\"";
    public const POSTCARD_LIMIT = "`3%s`3 laughs. `#\"The post-hawk has carried enough of your letters for one day. He has a union, you know.\"";
    /** %s, %s, %s: Halley, recipient, motif. */
    public const POSTCARD_SENT = "`3You write your message, and %s`3 seals the card with a drop of wax shaped like a leaf. A sleek brown hawk takes it from her hand at the window and is gone over the rooftops. Your postcard to %s`3, with a picture of %s, is on its way.";
    /** %s: owner. */
    public const POSTCARD_SUBJECT = "`3A postcard from %s`3's Corner";
    /** %s, %s, %s, %s, %s: sender, motif, message, owner, village. */
    public const POSTCARD_BODY = "`3A post-hawk drops a hand-painted postcard on your windowsill. On the front: %2\$s. On the back, in a hurried hand:`n`n`&%3\$s`n`n`3- %1\$s`3, sent from %4\$s`3's Corner in %5\$s.";

    public const NAV_BACK = "Back to %s`0";
    public const NAV_ORDERS = "Drinks";
    public const NAV_TALKS = "Talk";
    public const NAV_OTHER = "Other";
}
