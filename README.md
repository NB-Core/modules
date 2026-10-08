# NB-Core Modules

This repository provides optional modules for the [NB-Core/lotgd](https://github.com/NB-Core/lotgd) game engine. Most modules also work with DragonPrime LoTGD releases but are primarily maintained for NB-Core.

These modules extend nearly every aspect of the game ranging from administrative helpers to new forest events. Copy the desired PHP files (and folders if present) into your game's `modules/` directory and activate them from the game admin panel.

# Old Dragonprime modules (for legacy display, old versions and may not work, unmaintained)

The modules are located in **_old_dragonprime_snapshot/**.

You will find there mostly undocumented modules that were taken from dragonprime as a safety before it went down, some time before.
This is history.

## Repository layout

Directories group modules by purpose:

- **PVP/** – player versus player extensions and events
- **administrative/** – tools for admins and moderation
- **clans/** – clan options, ranks, rooms and clan tools
- **commentary/** – enhancements for chat and roleplay areas
- **dwellings/** – the dwellings (housing) core, its dwelling types and add-ons
- **forest/** – general forest related tweaks
- **forest_specials/** – additional forest encounters and bosses
- **gardens/** – garden activities and romance
- **general/** – cross-area features and utilities, shared libraries
- **graveyard/** – graveyard and shades additions
- **hof_displays/** – additional Hall of Fame pages
- **holidays/** – seasonal and holiday events
- **inn/** – inn additions (drinks, billboard, games)
- **items/** – inventory and items
- **lodge/** – lodge upgrades
- **mail/** – player mail quality-of-life tools
- **mounts/** – mount related features
- **quests/** – the quest series handed out by Dag Durnick in the inn
- **systems/** – large feature systems (races, specialties etc.)
- **village_modules/** – village shops and activities

## Module overview

Below is a short description for every module. Some modules rely on others; dependencies are noted where known.

### Administrative
- **adfuncbio** – adds a Fix Navs link (clears a player's stuck navigation) to bios for staff with petition rights and/or the `ha` preference, per a setting; its Kill Player link only sets an unused flag.
- **advertisingtracker** – record advertising clicks displayed in game.
- **alldp** – megauser grotto tool that gives a chosen number of donation points, with a system mail, to every non-staff account (only works without a database table prefix).
- **bioalt** – adds links to a player's bio that list possible alt accounts sharing their IP range or unique ID, with hits, last login and referrer; only accounts with the `viewallowed` preference see them.
- **biocomment** – shows a comment thread on every player's bio where accounts with the `canaddcomments` preference can read and leave staff notes about that player.
- **blocker** – import e‑mail block lists.
- **claneditor** – grotto Clan Editor for staff with user-editing rights to list, create, edit and delete clans, change or remove members and edit clan module prefs (it deletes clans without members when listing).
- **creationaddon** – adds age, terms and privacy checkboxes (plus an optional birthday) to account creation, links the texts in villages and footers, and makes existing players accept the privacy policy (write the terms and privacy texts in the settings).
- **creationaddon_indexdisplay** – adds Privacy Policy and Terms and Agreements links to the login page, showing the texts set in `creationaddon` (requires `creationaddon`).
- **donation** – shows a monthly donation-goal bar below the PayPal links; once the goal is passed, all players get a healing discount, extra forest fights and a defense buff at new day (needs PayPal donations in the paylog; set the goal).
- **donationreceipt** – mails the donor an HTML thank-you receipt after a verified PayPal donation (game name, sender address, signature and subject are settings; empty values fall back to the server URL / game admin email).
- **faqmute** – players without a dragon kill cannot post comments until they have opened the FAQ; moderators can reset this from a newbie's bio to mute them again.
- **forgottenpasswordblocker** – limits password resets.
- **gmlog** – records every ban made in the ban editor against each affected account, and moderators can view an account's ban history from its bio.
- **homecounter** – shows a live countdown to a configurable date and time on the login page, with custom text and unit names (set the event date in the settings).
- **homepagenotifier** – message block that appears on the homepage.
- **inactivemods** – reports inactive moderators.
- **linkchecker** – adds a check to the module manager that tests whether each installed module's download link is reachable (superusers with module-manager rights only).
- **mailfrompetition** – forward petition text via e‑mail.
- **maillimiter** – limits number of mails players can send.
- **manualpayment** – lets staff with paylog rights enter manually received payments into the paylog and warns in the grotto when this month has unprocessed payments.
- **mostdonators** – a Stone of Legends in the gardens lists the ten players who donated the most, and the Great Fire in the Veterans' Lounge names the top donor (needs donations recorded in the paylog).
- **multichecker** – checks for multi‐accounts.
- **mutemod** – lets moderators temp-mute a player from their bio for game days that count down at each new day, extend or lift it, and lift permanent mutes; muted players cannot post comments.
- **newdaylog** – log information on player newday.
- **personalpetitions** – personal petition categories for staff.
- **petitionfixnavs** – adjusts petition navigation links.
- **polling** – staff poll in the superuser grotto with up to three choices, one vote per account, a results view and a megauser reset (switch it on and set the subject and choices in the settings).
- **recaptcha** – adds Google reCAPTCHA v3 to the login, account creation and petition forms. Enter your own site key and secret key in the module settings.
- **recentsql** – adds a history of recently run statements and named favorites to the superuser raw SQL/PHP page.
- **savedays** – grants extra turns for days a player missed (up to a configurable maximum); players can opt out.
- **servercostlog** – log server expenses.
- **serversuspend** – while active, shows everyone except module managers and megausers a maintenance page with an optional explanation text (activate it only during maintenance).
- **showmuted** – grotto overview for moderators of all temp-muted players, their remaining days and who muted them, with a staff discussion chat (requires `mutemod`).
- **staffdp** – megauser grotto tool that gives a chosen number of donation points, with a system mail, to every staff account that has a `stafflist` description (only works without a database table prefix).
- **stafflist** – public Staff List linked from villages and the About page, sorted by rank with descriptions and online status, plus separate translator and inactive lists (requires `userstatus`; give staff a rank above 0 and a description in their prefs).
- **statistics** – counts daily weapon, armor, drink and kitchen purchases and lets megausers browse the totals by year, month and day in the grotto (only works without a database table prefix).
- **suspendannounce** – announce server suspensions.
- **toolstrans** – megauser grotto overview of how many translation rows each current translator has written, per language or in total.
- **unclean** – logs comments that trip the bad-word filter: recent ones appear in the bad-word editor, and moderators see each player's filter-trip count and last filtered comment in the bio.
- **visalogin** – adds a staff entry login form to the home page that admits only superusers (optional) and accounts with the `hasvisa` preference; the core full-server check still applies at login.
- **welcomemail** – send welcome e‑mail to new users.

### Clans
- **clanadmin** – lets clan leaders close or reopen their clan to applications from the clan hall; players cannot apply to a closed clan.
- **clanlog** – records rank changes and removals made on the clan's membership page in a log that leaders can read and annotate from the clan hall.
- **clanmail** – lets clan leaders send one mail to all clan members from the clan hall for a gold fee per member.
- **clannews** – shows the latest news entries of the clan's members in the clan hall (number configurable).
- **clanoptions** – clan leaders (optionally officers) can set a minimum dragon-kill limit that removes members below it and auto-accept applicants, and get a private fountain chat with a poster (its `petra` clan-tattoo list is unused: the hook is never registered).
- **clanranks** – lets clans give their own titles to up to 30 clan ranks; members can view their clan's titles and leaders edit them in a Clan Rank Editor.
- **clanrequirements** – players need a set number of dragon kills (default 8) before they may found a clan.
- **clanrooms** – adds a secret conversation chat room to the clan hall for members of Administrative rank or higher.

### Commentary
- **callformod** – adds a Call for moderator link to chat areas that mails the last 15 chat lines and the player's reason to a random online moderator, or files them as a petition when none is online.
- **gm_rp_talk** – lets game masters participate in fzone roleplay chats without breaking character tools.
- **nicecomments** – chat filter that turns common shorthand into emotes or full words (hehe, lol, brb, u, ne1…) and lowercases shouting (each filter can be switched off in the settings).
- **rp_chatcommands** – typing `::start_rp` in a chat posts a random Naruto-themed roleplay prompt naming a recently active player and a village; the Dark Horse bartender explains the command.
- **rp_lastcomment** – lists below each village page who posted the latest comment in every village chat and how long ago (requires `cities`; refuses to install without it).
- **sectionedchats** – splits village, superuser, and clan chat areas into RP and OOC channels with unread indicators.

### Dwellings
- **dwcastles** - Castle type for dwellings core
- **dwcityhouses** - City House type for dwellings core
- **dwellings** - Dwellings core module
- **cityprefs** - Installs a `cityprefs` table, syncs known cities, and adds a superuser editor for managing city-specific module settings (`dwellings/cityprefs.php`).
- **dwshacks** - Shack type for dwellings core
- **dwellings_antiplunder** - Counter measures against plundering
- **dwellings_plunder** - Plundering dwellings for gold
- **dwellings_pvp** - PVP against dwelling sleepers
- **dwellingseditor** - Superuser Editor for dwellings (like an owner could)
- **privatedwellings** – blocks display of dwelling chats and coffer deposit logs (`dwellings-*` and `coffers-*` sections) wherever commentary is shown, including inside the dwellings themselves (only relevant with `dwellings`).

### Forest
- **banknin** – a forest link where Berta the bank ninja deposits all gold on hand into the bank for a fee (5% by default), with a configurable number of uses per day.
- **eliteforest** – Naruto-themed Elite Forest Arena (level 15 or 40 dragon kills) with a few daily fights against `elite`/`other` creatures or the Kyuubi (linked only where a village calls the custom `eliteforest` hook; needs an `eliteforestlog` table).
- **flawlessboost** – each flawless win in a row makes the next forest enemies 15% stronger in attack and defense (compounding) until the player takes damage again.
- **flawlesspause** – after 3 to 5 flawless forest wins in a row, the next fight gives no bonus turn for a flawless victory and the streak resets.
- **forest_ops** – a daily "Special Missions" forest link that starts an event for the day's mission type, e.g. `goldmine`, `distress` or `rabidwerewolf` (install the events it calls; `keykeeper`, `tsunade`, `riddles` and `fuujinraijin` are not in this repository).
- **forest_scaling** – meant to weaken forest enemies for players with more than 999 dragon kills, but it only registers `battle-victory`, so its `buffbadguy` code never runs and it currently has no effect.
- **forestmod_new** – quick fights for newer DP versions.
- **healer_buffremoval** – healer can remove negative buffs. After a cure the player is sent back to where they came from (e.g. the village) instead of always the forest.
- **outhouse** – the Gnomish Outhouse in the forest, once a day: a paid private or free public toilet; washing your hands may refund gold or give a gem, skipping it can cost gold and a news mention.
- **penguin** – from level 7 the Penguin Overlord can be fought once per dragon kill for experience, gold and gems, with kill counts in bios, a Hall of Fame list and a statue (copy `penguinoverlord/` for its high-DK AI).

### Forest specials
- **akatsuki** – Akatsuki encounter.
- **assassins** – a forest ninja who, for gold and gems, ambushes a player you name at their next encounter and delivers your note; victims who win can take revenge (their kill/heal/arrest choices need `alignment`).
- **bijuuhunters** – Naruto-themed fight with Kakuzu and Hidan, who hunt players with a Bijuu mount or a large bounty; defeat seals the Bijuu mount for days (needs `dag`'s bounty table; flag Bijuu mounts in the mount editor).
- **chipmunks** – mischievous chipmunks that steal gold.
- **chipmunk_boss** – chipmunk boss (requires `chipmunks`).
- **crazyaudrey** – Crazy Audrey's basket game, where matching animals win forest fights and no match costs one; some days she sits in the village, where petting her kittens (configurable) for a few gold gives a short defense buff.
- **distress** – rare forest event: answering a cry for help at one of three castles can reward gems, gold, experience, charm or a forest fight, or cost turns, charm, all gold or your life.
- **dragonbuffercizer** – tunes the green dragon fight with an optional fire-breathing damage buff (worded as Orochimaru's snake arms) and stats weighted between core values, a player doppelganger and a dragon-kill formula (default settings change nothing).
- **druid** – a druid offers a broth with random effects: forest fights, gems, charm, experience or a max hitpoint, or lost hitpoints, charm, max hitpoints or a turn; refusing has smaller random effects.
- **elessasfall** – Elessa’s waterfall special.
- **erosennin** – meet Ero‑Sennin.
- **evil_punishers** – evil version of the punishers (requires `alignment`).
- **ferryman** – pay (or dodge) a ferryman's fare to cross a river, with random outcomes from a defense buff, gold, gems or a forest fight to going overboard, a fight with him or death with favor.
- **findgem** – forest and travel event in which the player simply finds a gem.
- **findgold** – forest and travel event in which the player finds gold scaled by level (configurable minimum and maximum per level).
- **findring** – a pearl ring under the leaves (a travel event with `cities`, a forest event otherwise) gives a charm point or burns away some hitpoints, with charm rarer at high dragon kills.
- **forestturn** – a potent drink gives one extra forest fight, or (45% of the time by default) a sleepy flower scent costs one.
- **glowingstream** – forest and travel event: drinking from a glowing stream may fully heal, give a forest fight or a gem, or leave the player near death or dead (gold is kept).
- **goldmine** – an old mine where a turn of digging yields gold, gems or nothing and a cave-in can kill; per-mount settings decide whether mounts enter, die or save their rider.
- **jewelmonster** – fight against a Gorgon that gets weaker the more jewelry the player wears (grace pieces for low dragon kills); winning gives experience, losing costs gold, experience and maybe gems (requires `jeweler`).
- **ladyerwin** – Lady Erwin encounter.
- **ladylake** – the Lady of the Lake's pool, where reflecting shifts charm and tossing a coin (costing a forest fight) brings a gem, an attack buff, biting faeries or, with `thieves`, a gem-stealing elf.
- **mrblack** – Mr. Black encounter (requires `ladyerwin`).
- **ninjamerchant** – wandering ninja merchant.
- **ninjamerchantstore** – store of the wandering ninja merchant (also available as village shop, see below).
- **penguinlady** – after Darkmaster Pengu complains in the village about penguins stealing his threads, the player meets them in the forest that day and can fight them for a large gold reward or risk a bribe.
- **potionpeddler** – a forest peddler sells potions for gems that permanently raise or lower max hitpoints; stronger brews require surplus hitpoints and can kill, and the glowing and rainbow potions are only stocked on some days.
- **punishers** – the punishers encounter (requires `alignment`).
- **rabbithole** – the player either gets stuck in a rabbit hole and loses a forest fight or rests nearby and gains one (50/50 by default).
- **rabidwerewolf** – rare fight with a rabid werewolf that gives experience on victory and costs gold and experience on death; Werewolf characters get a gem instead, and with `racewerewolf` the winner may be bitten.
- **rspgnome** – rock-paper-scissors against a three-armed gnome, first to two points: winning pays gold by level, losing costs the same.
- **smith** – Smiythe the Smith (a travel event with `cities`, otherwise in the forest) tries to add +1 to the weapon or armor for a gem, with a configurable risk of a -1 that he later repairs.
- **solar** – a solar eclipse: watching it (with at least 3 forest fights left) may gain or cost forest fights or add a hitpoint, while walking on costs 5 hitpoints.
- **stonehenge** – entering the stone circle has random results: death, experience, gems, charm, a full heal, permanent max hitpoints or the loss of five forest fights.
- **stpatsday** – St. Patrick's Day quest over several forest visits: find a young leprechaun's lost Pot o' Gold and return it for a gold or gem wish, though he may run off and leave a smaller sum.
- **stumble** – the player trips chasing a bunny and loses a share of max hitpoints (25% by default), possibly dying, but finds a configurable number of gems.
- **tatmonster** – fight against Cerberus, which gets weaker the more tattoos the player has (grace tattoos for low dragon kills); fleeing costs charm, winning gives experience, losing costs gold, experience and maybe charm (requires `petra`).
- **thegrinch** – Grinch mini‑boss.
- **thieves** – Lonestrider's band ambushes gem-carrying players (likelier with more gems), who pay a share, try a distraction or fight; losing costs gems and maybe `jeweler` jewelry (copy the `thieves/` image folder).
- **treasure** – rare cliff climb to a chest that opens with a matching coloured key for charm, a defense buff, elixirs, talismans, gold nuggets or death (requires `inventory`; create the key and reward items).
- **vampirelord** – introduces the Vampire’s Lair event (`forest_specials/vampirelord.php`) where players can sacrifice permanent HP for buffs, gold, or gems. Event images are shown through the optional `addimages` module if it is active.
- **vampirelord_bride** – bride of the vampire lord (requires `vampirelord`; images via the optional `addimages` module).
- **villain** – a random staff member, as an evil overlord, kidnaps the player, who escapes, blows up the lair for experience, is rescued at a cost or dies (account IDs listed in the `excluded` setting, empty by default, never get it).
- **waterfall** – a trail to a waterfall: walking the ledge may find gems, cost hitpoints and gold, or kill; drinking may heal, give a permanent max hitpoint or cost a forest fight.
- **wedgieman** – the fearsome Wedgie Man.
- **zombie** – zombie outbreak event.

### Gardens
- **gardener** – gazebo in the gardens where the gardener asks one true-or-false question about garden and game rules per day; a correct answer pays level-scaled gold or occasionally a gem (requires `addimages`).
- **gardenparty** – yearly garden party (theme, date and length configurable; default June 30 for 24 hours) where players buy a limited amount of cake and drinks, posting an emote in the garden chat and getting a cosmetic forest buff.
- **gardensroster** – lists the online players currently in the gardens or in the Shades on those pages.
- **loveshack** – Loveshack in the gardens where players buy drinks or roses for other players, or kiss or slap them for free, each sent as a mail and capped per day, plus a bar chat (bar drinks need `drinks`).
- **temple** – forest event where players help or harm strangers, shifting alignment and earning points (capped per dragon kill) to spend at the Temple of Shadow and Light in the gardens on specialty, attack or defense (requires `alignment`).

### General
- **abandoncastle** – once-a-day Abandoned Castle maze (village nav or forest event, minimum dragon kills) where buffs are suspended and monsters lurk; the exit pays gold and gems by number of moves, doubled in hardcore mode without automap.
- **abandoncastleeditor** – grotto grid editor for Abandoned Castle mazes that exports them as PHP arrays or files in `abandoncastle/custom/`, to be added to the maze list by hand (needs user-edit rights plus the per-user `allowed` preference).
- **addimages** – image helper other modules use to show pictures as lightbox thumbnails with size limits; players can hide in-game images (resizing uses the Composer package intervention/image with the imagick extension; without them images are shown unscaled).
- **additionalbioinfos** – lets players add an extra text box plus age, height, eye and hair colour to their bio via preferences, with an admin character limit and switches for each part.
- **availability** – limited weapon and armor stock per level that drops with each purchase and partly restocks every game day; nearly sold-out items cost more for players past two dragon kills, and sold-out ones cannot be bought.
- **availabilityapi** – read-only JSON API with `mounts` and `merchant` endpoints listing mount prices, locations and remaining availability plus merchant shop items; players create, rotate or revoke a personal API token in their preferences (requires `mountrarity` and `ninjamerchantstore`).
- **bankmod** – lets players store a limited number of gems in the bank (default 10) and adds one-click deposit-all and withdraw-all links for gold and gems.
- **calendar** – in-game calendar with Japanese month and weekday names that advances every game day and shows at new day and in the village (only 12 months and 7 weekdays are named; lower the 13-month/8-day defaults).
- **current_events** – boxed announcement with an admin-written text and a days-left countdown shown in every village until a set end date (fill in the text and end date; nothing shows while the text is empty).
- **customeq** – Hunter's Lodge purchase of custom names for the player's weapon and armor with donation points; settings decide whether the names survive dragon kills and equipment upgrades, and equipment strength can be shown in the stats.
- **datemanager** – library module for date-limited (seasonal) modules: checks whether today lies in an `MM-DD` start/end window (also across the turn of the year, with optional leniency days) and provides countdown helpers. Required by `halloween`, `pumpkin` and `xmasdiscount`.
- **debt** – shifts alignment and demeanor based on choices in several events (fairy, distress, `abigail`, Crazy Audrey, Lady Erwin, Mr. Black, Ero-Sennin) and penalizes `slayerguild` members in bank debt at dragon kill (requires `alignment`).
- **discord_widget** – embeds the Discord server widget in a dark or light theme at the bottom of the home page (the Discord server ID is a setting; no widget is shown while it is empty).
- **donationday** – gives every player donation points each new day and a larger amount for each dragon kill (1 and 20 by default).
- **extlinks** – up to five external links, each with its own nav heading, shown on the home page, in the villages and/or in the Shades (link 1 defaults to the lotgd.net forum; change or clear it).
- **forest_special_drawitem** – on random days a card deck may appear in the forest; drawing a card gives an inventory item from rarity-weighted categories (requires `inventory`; fill in the item categories per rarity, or the deck gives nothing).
- **hitcount** – simple hit counter on the home page, shown above the login or below the skin selector, that counts every view of the page.
- **homepagecookies** – GDPR cookie-consent dialog on the home page using the bundled Klaro! library, with a link to change the choice and an optional Google Analytics entry; login, skin and language cookies are ignored until consent.
- **logslowmodules** – stub meant to print slow-module timings to debug output; it registers no hooks and does nothing on its own (the core already reports slow hooks in debug output).
- **mightyblogs** – public blogging system that lets authorised users post long-form updates accessible from the village, cemetery, and index. Older releases had serious issues; upgrade servers to at least version 1.1.
- **moons** – up to three moons with configurable names and cycle lengths whose phases advance every game day and are described at new day and in the village, forest and travel descriptions.
- **motd_js_popup** – opens the latest Message of the Day in a popup in the village while the player has not read it yet (loads the jquery-modal plugin from cdnjs and needs jQuery already on the page).
- **mysterygems** – Gem's Eternal Mysteries, a village shop selling magic stones for gold that randomly help or hurt mount, hitpoints, gems, turns, experience or favor (its stock is commented out in `run/case_enter.php`, so the shop stays locked).
- **namesextension** – stores each player's name without title in a module preference, filled for all accounts by a one-off conversion in the grotto (megauser only); no module in this collection reads the preference yet.
- **newdaybar** – adds a countdown to the next game day, as text and/or a small progress bar, to the Personal Info stats.
- **oldman** – forest event: an old man swings charm-changing sticks, asks for an escort to town, offers a number-guessing bet, or turns out a necromancer who kills you, drains max hitpoints or trades a gem for favor.
- **perws** – players can show a personal website link in their bio, and staff can ban or unban links from the grotto, which mails the owner (grant the moderation page per user via the `access` preference).
- **pktrack** – counts each player's PvP fights won and lost, shows them in the bio (players can hide them) and adds a Player Fights ranking to the Hall of Fame.
- **pqgiftshop** – village gift shop where players buy one of twelve configurable gifts or greeting cards for gold and mail it with a note to another player; players on the recipient's `friendlist` ignore list are refused.
- **recentaccounts** – adds a Most Recent Accounts list to the warrior list, showing the newest characters without dragon kills up to a set game-day age, linked to their bios.
- **rss** – RSS feeds for the daily news, who's online and the MoTD, also announced in page headers for browser autodiscovery (set your server's short name, long name and description; the defaults say LoGD).
- **rss_nav** – adds an RSS News Feeds link in the village to a page listing the available feeds (requires `rss`; shown only while its "show on about page" setting is on).
- **showfavors** – shows the player's current favor with the death overlord in the gypsy's tent.
- **showlastregistered** – grotto page for staff with comment or petition rights listing the latest registrations (last 30, or the last 24 hours, 3, 7 or 30 days) with last IP, last ID and an edit link.
- **stattracker** – records forest fights, flawless fights, PvP results, base stats and speed per dragon kill and shows them as a statistics table from the bio, to the player (if allowed) and to user-editing staff.
- **userstatus** – players pick a status (Visible, Appear Offline, Busy, Afk) and an optional message in preferences; Appear Offline hides them as online in lists and bios, and people writing them mail see the status.
- **villagenews** – shows the latest news lines in the village description (players can switch it off) and optionally above the login on the home page.
- **weaponnavs** – lists every weapon and armor in the shops as a buy link in the navigation (handy for translated games) and replaces the village return link with "Back to <town>".
- **weather** – picks a random daily weather from eight configurable conditions and shows it at new day, in villages, the gardens and on the home page, with separate lists for the Shades and one optional town.
- **weekend_events** – weekend bonuses by Friday of the month: extra PvP fights, extra forest fights, gold per level, an attack buff, or on fifth weekends a Weekend Warrior companion, granted at new day and announced in villages.
- **wordpython** – Word Python, a parlor game in which players take turns adding a sentence to a shared story (requires `playergames`).

### Graveyard
- **gravebless** – adds a "Seek a Blessing" option to the death overlord's favors (500 favor by default) that blesses armor or weapon until the next dragon kill with a decaying damage shield or lifetap, or rarely curses it.
- **graveofdragons** – graveyard event: a cavern of dragon bones where searching (costs a torment) finds gold, gems or a dark altar that trades soul points for favor, or a beast that drains soul points.
- **gravestoneshh** – a "Read Gravestones" link in the Shades that shows one of 64 humorous epitaphs at random.
- **scavenge** – graveyard event for looting a pile of bodies for gold, gems or favor, with zombie pits and a lost soul whose fate returns as a village wraith that blesses the player or brings terror debuffs.
- **shadestalk** – adds the Mourning Cemetery, a chat area for dead players, to the Shades.
- **soulgem** – graveyard event that grants one extra torment and 1–5 favor.

### Hall of Fame displays
- **hofalignment** – Hall of Fame page for the most good or evil characters (requires `alignment`).
- **hofbattlearena** – Battle Arena rankings in the Hall of Fame (requires `battlearena`, not part of this repository yet).
- **hofclandk** – dragon kills per clan in the Hall of Fame.
- **hofclanpk** – player kills per clan in the Hall of Fame (requires `pktrack`).
- **hofdemeanor** – Hall of Fame page for the most lawful or chaotic characters (requires `alignment`).

### Holidays
- **casperhh** – rock-paper-scissors against Casper in the Haunted House cellar, played just for fun and a news line, with no gold at stake (requires `hauntedhouse`).
- **clantrees** – clans buy a Christmas tree for their hall and decorate it with turns, gold and gems; a well-decorated tree gives members a daily defense buff, doubled for the best tree (turn on tree buying in the settings).
- **easteregg** – Easter forest event: find an egg hidden on a grid within three tries to win gold, a gem or a rare red egg for `eggbert` (active within 14 days of a hard-coded April 14; requires `inventory`; set the egg's item ID in the settings, otherwise a gem is given instead).
- **eggbert** – Eggbert's shop, open in one village for the four weeks before April 27, buys one rare red egg from `easteregg` per player and year for 200 donation points or 30 gems (requires `inventory`).
- **findfoolgold** – April Fools' forest and village event that shows the player a huge find of gold and gems that is never actually paid out (active only on April 1).
- **halloween_xp** – adds a configurable bonus (10% by default) to forest experience and a cosmetic "Happy Halloween" buff at each new day (active October 29 to November 2; requires `datemanager`).
- **halloweenghosts** – Halloween forest event with ghost siblings: meeting both peacefully reunites them for a combat buff, while attacking one starts a tough fight that costs experience and gold if lost (October 31 to November 2; requires `datemanager`).
- **hauntedhouse** – Halloween Haunted House at one village's gates with house and garden chat rooms plus rooms leading to `casperhh`, `trickortreathh`, `pumpkinhouse` and `gravestoneshh` (October 31 to November 2; requires `datemanager`; install those modules too).
- **holiday_fools** – rewrites game text April Fools' style, swapping character names for joke names and adding "bork bork" gibberish (active on April 1; players can opt out in their preferences).
- **holiday_naruto** – Naruto-themed "talk like Naruto" days that rewrite game text with character nicknames and "dattebayo" slang (active April 17 to 19; players can opt out in their preferences).
- **holiday_pirate** – Talk Like a Pirate Day: rewrites game text in pirate speak (active September 19 and 20; players can opt out in their preferences).
- **holiday_xmas** – Christmas text replacements (North Pole, Snownin, egg nog), a small attack buff at each new day and a countdown in the village description (active December 15 to 25; requires `datemanager`; players can opt out).
- **lunarnewyear** – Lunar New Year season (dates built in until 2043): a daily red-envelope gold gift and a once-per-season forest fight with the Kirin for gold, gems or items (items need `inventory`; reinstall after changing the day settings).
- **merry_xmas** – shows a rainbow "Merry Christmas" banner on the home page (active December 1 to 31; requires `datemanager`).
- **pumpkinhouse** – once-a-day pumpkin carving in the Haunted House gardens for a little gold, with prizes up to five times the fee plus a short buff for the best pumpkin (October 29 to November 2; requires `datemanager` and `hauntedhouse`).
- **pumpkinking** – Halloween forest event with the Pumpkin King: he scorns gold and gems, fleeing costs forest fights, and candy items earn random Halloween items (October 29 to November 2; requires `datemanager` and `inventory`; set the candy item ID).
- **snowball** – Snowy Banks in the gardens, where players throw snowballs at online players, who get a mail urging them to hit back (December 1 to 31 plus 5 grace days; requires `datemanager`).
- **snowbuild** – once-a-day snowman building contest for a little gold, with prizes up to five times the fee, a short buff and the winner shown in the village description (set its village first; it is empty by default).
- **stocking** – inn fireplace chat that, around December 25, hands out a daily combat buff in the last days before Christmas and one stocking present (gold, gems, forest fights or a buff) on and just after Christmas.
- **trickortreathh** – trick-or-treat at a Haunted House door or a hidden village door: one of nine monsters may kill, rob or debuff you, or grant buffs, a weapon or bonus forest XP (October 29 to November 2; requires `datemanager`).
- **valentine** – Valentine's Day forest and village event in which a hooded stranger asks "Will you be my valentine?"; saying yes brings 1–5 gems at the next new day (active on February 14 only).
- **wc_casper** – Winter Castle copy of `casperhh`: rock-paper-scissors with Casper in the castle cellar, played just for fun and a news line (requires `wintercastle`).
- **wc_trickortreat** – winter "Frosty Kiss or Frosty Bite" version of `trickortreathh` behind the Winter Castle's locked door, where snowmen, pixies and other winter characters hand out buffs, gifts or mishaps (requires `wintercastle`).
- **wintercastle** – Winter Castle at the village gates with chat rooms in the castle, garden and cellar and rooms leading to `wc_casper` and `wc_trickortreat` (active December 1 to 31; install those modules too).
- **wintersnow_home** – falling-snow overlay on the home page during the window, switching to fireworks on the day after it ends (default December 1 to 31; requires `datemanager`).

### Inn
- **billboard** – inn billboard where each player can pin one short note per day; notes expire after a set number of days (default 7), authors and moderators can take them down, and there is a chat.
- **breakin** – from the capital's stables, players can sneak into the inn to attack sleeping guests; getting caught costs PvP fights, charm and hitpoints, may land them in `stocks` and bars them from inn services for days.
- **cedrikspotions** – adds a Gems option at the barkeep for trading gems for charm, max hitpoints, healing, a specialty reset or a race reset with transmutation sickness; prices can be fixed or vary daily.
- **crying** – inn event in the capital for players holding the diamond ring found during a break-in: returning it to a crying lady shifts charm or grants a short attack buff, plus astuteness with `matthias` (requires `breakin`).
- **dag** – Dag Durnick in the inn, where players place gold bounties on others (level limits, fee and daily cap configurable) that PvP killers collect; includes a superuser bounty manager (shown only when PvP is enabled).
- **donationlottery** – donation-point version of the barkeep's lottery: buy a four-digit ticket for gold and share the donation-point jackpot if the daily draw matches (tickets do not grow the jackpot; set it in the settings, default 0).
- **drinks** – adds an editor-managed menu of drinks to the inn bar, priced per level and each with its own buff; drunkenness slurs chat, makes the barkeep refuse service and causes hangovers, and hard drinks are capped daily.
- **drunkard** – inn event in which a drunk either spills beer on the player (losing a charm point) or misses (gaining one), a limited number of times per day.
- **game_dice** – dice game against the old man in the Dark Horse Tavern: bet gold, reroll up to three times, keep a die and hope he rolls lower (requires `darkhorse`).
- **game_fivesix** – Five Sixes in the Dark Horse Tavern: pay a small fee, a limited number of times per day, to roll five dice for a growing jackpot, with smaller shares for four or three sixes (requires `darkhorse`).
- **game_stones** – stones game against the old man in the Dark Horse Tavern: bet gold on drawing like or unlike pairs from a bag of red and blue stones (requires `darkhorse`).
- **innchat** – adds extra topics to the inn's "people are talking about" line, such as a random player or master, dwarf tossing, the weather or the village.
- **lottery** – the barkeep's lottery: buy a four-digit ticket for gold, part of each ticket grows the jackpot, and players matching the daily draw split it, paid into the bank at their next new day.
- **salesman** – inn event in which a shady salesman offers a mystery item for gold per level once a day, usually good but sometimes bad (gems, turns, hitpoints, charm, weapon or armor changes); declining may get you robbed.
- **sethsong** – listen to the bard in the inn a set number of times per day for random effects such as forest fights, gold, gems, healing or charm, or a loss of hitpoints, gold or a turn.

### Items
- **backpack** – adds an Open Inventory link to the character stats that opens a popup listing the player's items with description, equip, unequip and use actions (requires `inventory` with its character-stats popup setting switched on).
- **inventory** - the entire updated item system by XChrisX for run with +nb core. Item and inventory data access runs through a Doctrine based service layer (`inventory/lib/Domain`, `Repository`, `Service`); the classic `itemhandler.php` functions remain available for other modules.
- **findloot** – hooks `battle-victory` to roll for loot items via the inventory system (`items/findloot.php`).

### Lodge
- **amuletru** – Hunter's Lodge Amulet of Ru, bought with donation points, that for a few game days grants 1–3 extra forest fights and a random attack/defense buff each new day before vanishing.
- **buyablog** – lets players buy permission to post in the common blog with donation points and change their blog signature for free (requires `mightyblogs`).
- **extrafights** – players spend donation points on one extra forest fight per game day for a set number of days (default 30), stackable up to a limit, with optional extension.
- **extratravels** – players spend donation points on one extra town travel per game day for a set number of days, stackable up to a limit like `extrafights` (requires `cities`).
- **fairydust** – players buy bottles of Fairy Dust for donation points plus a gem and use them in the forest for a defense buff and a random bonus: forest fights, two gems, max hitpoints or a specialty use.
- **healerdiscount** – players spend donation points on a token that gives a percentage discount at the healer for a few game days (default 5% for 5 days).
- **inncoupons** – players buy ten free inn stays with donation points and redeem them in the inn to log out safely without paying for a room.
- **lodge_colortable** – adds a Lodge navigation link displaying the LoTGD color table and a sample text input (`lodge/lodge_colortable.php`).
- **lodgedkpointreset** – reset spent lodge points for a dragon kill.
- **lodgenonexpiration** – purchase non‑expiration account upgrade.
- **namechange** – allow players to change character name.
- **prizemount** – gives donors a chosen prize mount for a number of game days per amount donated, swapped in at new day and unsellable, then restores their old mount (choose the mount and switch awarding on; needs donation processing).

### Mail
- **bingobook** – a private, Naruto-themed bingo book reached from the mailbox, where players list other players with a note and see their online status, location and whether they are alive.
- **bingobook_addon** – after bribing the inn bartender, players can ask who has them in a bingo book, what was written about them and when they were added (requires `bingobook`).
- **friendlist_faq** – adds a Friend Lists entry to the FAQ explaining friend requests, status display and ignoring players (meant for the separate `friendlist` module).
- **mailarchive** – archive incoming messages into user-defined categories for long-term storage.
- **mailnotepad** – provides personal notepad fields inside the mail interface.
- **mailrejecters** – shows an admin-written text on the account creation form, for example a warning about e-mail providers that reject game mail (fill in the text setting).
- **outbox** – review or optionally mirror sent messages, with support for a full separate outbox.
- **readmail** – adds Mark Checked As Seen and Delete All Read buttons to the mailbox.

### Mounts
- **mount_feeder** – forest event where a mysterious vendor sells mount feed (requires `inventory`).
- **mountname** – Hunter's Lodge service letting players give mounts custom names.
- **mountrarity** – rotates which mounts are in stock by giving each stable mount a rarity.
- **mountstables** – extra mount stable slots.
- **xmasdiscount** – discounted mount prices around Christmas.

### PVP
- **adminpvp** – grants PvP immunity to game staff (account IDs are set in the module settings). Other modules can switch the immunity off via the `adminpvp-allow-execution` hook.
- **halloween** – seasonal Halloween PvP event: more PvP fights, no PvP restrictions from `adminpvp`/`pvpbalance` while it runs, and a countdown in the village description (requires `datemanager`).
- **pumpkin** – Halloween pumpkin hunt with falling-pumpkin animation (requires `inventory` and `datemanager`). Copy the `pumpkin/` folder (script and images) along with the module.
- **pvpavatars** – shows player avatars during PvP (requires `avatar`, not part of this repository yet).
- **pvpbalance** – balance PvP targets based on dragon kills. Other modules can switch it off via the `pvpbalance-allow-execution` hook.
- **pvpquotes** – random quotes when entering PvP fights.
- **serverbalance** – collects balancing statistics on every dragon kill (average age and gold per dragon kill count) and shows them in the superuser grotto.

### Quests
- **dagaladdin** – Dag's quest for Aladdin's Lamp (levels 8–14): search ruins from the village gates for the artifact, possibly fighting a Djinn, and bring it back for gold and gems (requires `dagquests`).
- **dagbandits** – Dag's bandit bounty (levels 5–11, needs some reputation with Dag): spend turns fighting a bandit team and their animal pets at their base, then hand in their rings for gold and gems (requires `dagquests`).
- **dagkirin** – Lunar New Year mission offered at Dag's table: hunt a mystical Kirin in the forest, past a rogue ninja, for gold and gems (requires `dagquests` and `lunarnewyear`; offered only during the festival window).
- **dagmanticore** – Dag's quest (levels 10–14) to hunt the manticore that destroyed a wagon on the trails, rewarded with experience and the gold and gems left at the scene (requires `dagquests`).
- **dagminotaur** – Dag's bounty (levels 5–9) on a minotaur in the caves: track it down, possibly fighting a lion on the way, and bring its head back for gold and gems (requires `dagquests`).
- **dagquests** – framework that adds "Ask About Special Bounties" to Dag Durnick's table in the inn and tracks the player's reputation with Dag for the `dag*` quest modules (requires `dag`).

### Systems
- **alignment** – track and display player alignment and demeanor values that change via PvP, mounts, and creature settings.
- **alignmentbasedweapon** – alignment-driven shrine event that can grant powerful weapons to matching characters (requires `alignment`).
- **circulum** – core reset system called “Circulum Vitae”.
- **circulum_hof** – Hall of Fame for circulum resets (requires `circulum`).
- **circulum_prefreset** – preference reset for circulum (requires `circulum`).
- **circulum_presave** – character restore helper.
- **circulum_uchiha** – Uchiha clan bonus (requires `circulum`).
- **marriage** – marriage system for players.
- **racesystem** – alternative race handling. Races are defined in `racesystem/races.php`; drop in another file (e.g. the one from [bleach_modules](https://github.com/NB-Core/bleach_modules)) to change the setting.
- **specialtysystem** – core specialty framework. The setting `resourcename` names the resource spent on skills (default "Chakra"); specialties may restrict themselves to races via `race_requirements`.
- **specialtysystem_basic** – basic ninja skills (requires `specialtysystem`).
- **specialtysystem_earth** – earth specialties (requires `specialtysystem`).
- **specialtysystem_fire** – fire specialties (requires `specialtysystem`).
- **specialtysystem_genjutsu** – genjutsu specialties (requires `specialtysystem`).
- **specialtysystem_ice** – ice specialties (requires `specialtysystem`).
- **specialtysystem_lightning** – lightning specialties (requires `specialtysystem`).
- **specialtysystem_medical** – medical specialties (requires `specialtysystem`).
- **specialtysystem_sand** – registers sand elemental jutsu combat options for the specialty system (requires `specialtysystem`).
- **specialtysystem_water** – registers water elemental jutsu combat options for the specialty system (requires `specialtysystem`).
- **specialtysystem_wind** – registers wind elemental jutsu combat options for the specialty system (requires `specialtysystem`).
- **translationwizard** – translation management wizard for text output (scan modules for untranslated texts, edit, search and replace translations).

### Village modules
- **abc** – Aravis' Talismans shop where players buy a Talisman with favor and, once dead, hand it over in the graveyard for a resurrection that has a small configurable chance to fail.
- **abigail** – village event in which a street hawker sells a gift for the player's partner for a few gems; at the next new day the partner's reaction raises or lowers charm (requires `lovers`).
- **addgems** – one-off compensation that gives every player 1 gem and 2000 gold at their next new day together with a hard-coded outage apology (edit the message and amounts in the code before use).
- **additionaltattoos** – adds Phoenix (resurrections), Dragon (dragon kills) and Dragonfire (Dragon's Breath buff) designs to Petra's parlor, with Dragon and Dragonfire combining into a fire-breathing dragon set (requires `petra`).
- **aloysius** – Aloysius' Market, where once a day players gamble forest fights (more fights, better odds) for a chance at one extra PvP fight.
- **applebob** – Sichae's apple bobbing stand: pay a little gold a few times a day for a small heal, a rare blue-apple defense buff or a poisoned-apple debuff.
- **azrael** – Halloween trick-or-treat village event with a ghost-costumed child; treating him to a gold piece grants an attack buff, while ignoring him or asking for a trick risks charm, hitpoints, gold and gems.
- **beach** – Beach Resort location reached from the village gates, with its own chat, a picture, random beach events and a hook for add-ons such as `beach_mermaid` and `beach_sandcastle`.
- **beach_mermaid** – Mermaid's Rock at the beach, where players throw gems into the sea (limited per day) for random rewards such as forest fights, gold, gems, charm, max hitpoints or a defense buff (requires `beach`).
- **beach_sandcastle** – sandcastle contest at the beach: pay gold to build for a chance at prize money and a displayed winning castle, or trample castles for a gem, a mean title or a beating (requires `beach`).
- **beggar** – village event where an old beggar asks for alms; giving a coin, a gem or all your gold leads to charm, gem or news outcomes, and shifts alignment when `alignment` is active.
- **beggarslane** – explore Beggars Lane in the village.
- **calletrader** – Vernon's trade stall for buying and selling calle shells for gems and caravan tickets to the ghost and ice towns (offers nothing unless `highcity`, `caravan` or `icecaravan` is active).
- **cityamwayr** – adds the Amwayr city with configurable travel routes, minimum dragon kill access requirement, and city-specific PvP handling.
- **clanleadersecretroom** – two private chat rooms, reachable from the gypsy tent, where clan leaders and founders can talk among themselves.
- **costumeshop** – Haunter's Lodge (optionally Christmas-themed) that rents costumes replacing a player's custom title, weapon and armor names for a few game days (closed by default; switch it open in the settings).
- **crimsonleaf** – monthly PvP contest over a unique Crimson Leaf Clover handed out by the gypsy; in the final week its holder is always attackable, and whoever holds it at month end wins (requires `inventory`; must be re-activated each month).
- **drpap** – Dr. Paprika's office, where players with enough dragon kills pay gold and gems for a gender change a limited number of times, with a waiting-room chat.
- **exodus** – Exodus' pit, where players pay gold (per level) and gems for an extra skill point in their specialty, a few times per dragon kill.
- **falls_of_truth** – once a day players can face their true self at the Falls of Truth, which randomly shifts their alignment one point towards good or evil (has no effect without `alignment`).
- **fightingzone** – Naruto-themed roleplay chat arenas linked from every village: a main zone, eleven themed fighting grounds and extra grounds unlocked at 25 and 50 dragon kills, with an optional rules link (requires `addimages`).
- **fightingzone_shades** – undead roleplay chat arenas for dead players in the Shades, plus a link to the clan hall for clan members while dead, with an optional rules link.
- **frosty** – winter village event in which players can spend a turn helping a girl rebuild her snowman for extra forest fights, a gem or gold; ignoring her can cost hitpoints, a turn, charm or gold.
- **ghosttown** – Esoterra, a Halloween ghost-town city with most shops, the forest and the bank closed and a campsite for PvP, reachable via `caravan` or a travel setting (needs `cities`).
- **grave** – travel event: a roadside grave where praying costs a forest fight and earns favor with the death overlord (requires `cities`).
- **haberdasher** – Deimos' Haberdashery in the capital, a pure gold sink where players buy and upgrade luxury hats whose size shows in their bio and on a customer ranking list.
- **halleyscorner** – Halley’s Corner travel option.
- **heartbreak** – Valentine village event with a singing girl: tipping or beating her brings buffs, debuffs, robbery, alignment changes or even death (poems are optional text files in `modules/heartbreak/`; without them a short built-in verse is used).
- **heidi** – Heidi's Place, where players trade a PvP fight for forest fights, donate gold for a defense buff and optionally re-pick dragon points for gems; random anonymous gold gifts arrive at new day.
- **hepzibah** – Halloween village event with a creepy old woman who hands out a free-pizza voucher for the Marquee (the voucher needs `marquee`; otherwise flavor text only).
- **hitch** – lets players stuck in another town without travels and with few turns hitchhike to the capital, risking robbery or death and owing errands the next morning (needs `cities`).
- **hofcrimsonleaf** – Hall of Fame page listing the monthly Crimson Leaf Clover winners (requires `crimsonleaf`).
- **icetown** – Polareia Borealis, a winter-themed city with most shops, the forest and the bank closed and ski slopes for PvP, reachable via normal travel or `icecaravan` (needs `cities`).
- **invitationzones** – fighting zones accessible by invitation (requires `fightingzone`).
- **jeweler** – Oliver's Jewelry, a gem sink selling five pieces of jewelry that show in the player's bio and can be sold back at a reduced price.
- **kissingbooth** – Naruto-themed kissing booth in the gardens: once a day a kiss for gold per level gives charm, healing, a forest fight, a gem or a mishap (turn on the "gardens" setting, otherwise it appears nowhere).
- **kitchen** – Saucy's Kitchen, a once-a-day meal or snack priced by level with random effects on forest fights, hitpoints, charm and gems (tipping improves the odds); one dessert yields a feather that `petra` accepts as a discount.
- **luckydip** – Elias' Lucky Dip stand: a few paid dips a day into a small or large box for a gem, a little gold, a healing cookie, a worthless toy or a rare calle shell.
- **madmax** – multiplayer word game (requires `playergames`).
- **ninjamerchantstore** – ninja merchant village shop.
- **offering** – village event in which a strange woman asks for an amount of gold scaled by level and dragon kills; paying it earns favor with the death overlord.
- **oldchurch** – an abandoned church where the priest blesses generous donors with a combat buff and curses stingy ones, or offers a dark ritual that gives the buff at the cost of hitpoints and a turn.
- **oldhouse** – a haunted old house whose rooms players explore for gold, a gem, charm or a scare that costs hitpoints or charm and ends their visits for the day.
- **petra** – Petra's tattoo parlor, where players buy tattoos for gems that take days to heal (costing hitpoints meanwhile) and show in their bio; other modules can add designs and clan tattoos via hooks.
- **playergames** – framework for small player games.
- **scry** – scrying pool in the gypsy tent: pay gold per level to read and post in another village's chat for a few messages; game masters can also scry from the Shades (requires `cities`).
- **sichshop** – Sichae's Apple Shop, selling apples priced by level a few times a day, each with a random boon such as max hitpoints, gold, charm, gems, experience or forest fights.
- **skillshop** – Minzer's Skill Shop, where players pay gold per level once a day to refresh their specialty uses (also works with `specialtysystem`).
- **spa** – Booger's Trollish Spa offering a mudbath (attack buff) or massage (defense buff) for gold per level at the cost of forest fights (the town is a setting; a fresh install uses the capital).
- **sphinx** – travel event where a Sphinx must be fought: winners get gold or a gem and a full heal, losers lose gold, gems or experience, and Felynes pass with a gift (requires `cities`).
- **spookygold** – village event: a dark alley with a gem or gold piece lying around that may hide a larger cache or a Bonemarrow Beast fight, limited to a few visits per day.
- **statue** – shows a statue of the latest dragon slayer in the capital's description and, optionally, the most recent hero on the home page.
- **stocks** – a set of stocks in the capital's description; examining them frees the current occupant and traps the curious player instead.
- **strategyhut** – Atrus' Strategy Hut for players without dragon kills, selling random gameplay tips for a few gold.
- **sweets** – Mystie's Sweets Shoppe: buy treats a few times a day for random hitpoints, forest fights or gems, chat in the shop, or pour chocolate syrup on a random online player in the same village.
- **sympathy** – allow players to purchase sympathy.
- **townfountain** – a village fountain where players can toss a coin once a day for a small chance of a turn or gem, chat, or jump in to lift their spirits (and reduce odor with `odor`).
- **treasure_field** – Naruto-themed treasure dig run by Deidara and Gari: pay gold once a day to dig for a gem, gold, a clay-bomb buff or an explosive mishap (set its village; the default is `Iwagakure`).
- **vending** – village event with a soda vending machine; buying a can brings a gem, forest fights, gold, an exploding can or a crushing that leaves 1 hitpoint and no gold.
- **villhut** – a villager's hut with free bags of sweets: taking one or two at a time heals a little and may grant forest fights, while grabbing three or four costs hitpoints and charm.

## Additional notes

Some modules require others to be installed first (as listed above). Two of those dependencies (`avatar`, `battlearena`) are not published here yet. All modules assume a working NB-Core/lotgd installation. Enable or disable modules from the game’s Module Manager.

When a module comes with a folder of the same name (e.g. `pumpkin.php` and `pumpkin/`), copy both into your game's `modules/` directory.

## Additional modules

Some additional modules have been created by [Jeffrey Hoegee](https://github.com/Avanae/lotgd-modules/tree/main).
These work alongside the +nb framework (and the his edition), provided a proper installation is in place.

Many thanks to my pal [Jeffrey Hoegee](https://github.com/Avanae/lotgd-modules/tree/main) for his contributions and for helping to broaden the ecosystem.
