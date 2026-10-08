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
- **advertisingtracker** – record advertising clicks displayed in game.
- **blocker** – import e‑mail block lists.
- **forgottenpasswordblocker** – limits password resets.
- **homepagenotifier** – message block that appears on the homepage.
- **inactivemods** – reports inactive moderators.
- **mailfrompetition** – forward petition text via e‑mail.
- **maillimiter** – limits number of mails players can send.
- **multichecker** – checks for multi‐accounts.
- **newdaylog** – log information on player newday.
- **personalpetitions** – personal petition categories for staff.
- **petitionfixnavs** – adjusts petition navigation links.
- **recaptcha** – adds Google reCAPTCHA v3 to the login, account creation and petition forms. Enter your own site key and secret key in the module settings.
- **savedays** – grants extra turns for days a player missed (up to a configurable maximum); players can opt out.
- **servercostlog** – log server expenses.
- **suspendannounce** – announce server suspensions.
- **welcomemail** – send welcome e‑mail to new users.

### Commentary
- **gm_rp_talk** – lets game masters participate in fzone roleplay chats without breaking character tools.
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

### Forest
- **forestmod_new** – quick fights for newer DP versions.
- **healer_buffremoval** – healer can remove negative buffs. After a cure the player is sent back to where they came from (e.g. the village) instead of always the forest.

### Forest specials
- **akatsuki** – Akatsuki encounter.
- **chipmunks** – mischievous chipmunks that steal gold.
- **chipmunk_boss** – chipmunk boss (requires `chipmunks`).
- **elessasfall** – Elessa’s waterfall special.
- **erosennin** – meet Ero‑Sennin.
- **evil_punishers** – evil version of the punishers (requires `alignment`).
- **ladyerwin** – Lady Erwin encounter.
- **mrblack** – Mr. Black encounter (requires `ladyerwin`).
- **ninjamerchant** – wandering ninja merchant.
- **ninjamerchantstore** – store of the wandering ninja merchant (also available as village shop, see below).
- **punishers** – the punishers encounter (requires `alignment`).
- **thegrinch** – Grinch mini‑boss.
- **vampirelord** – introduces the Vampire’s Lair event (`forest_specials/vampirelord.php`) where players can sacrifice permanent HP for buffs, gold, or gems. Event images are shown through the optional `addimages` module if it is active.
- **vampirelord_bride** – bride of the vampire lord (requires `vampirelord`; images via the optional `addimages` module).
- **wedgieman** – the fearsome Wedgie Man.
- **zombie** – zombie outbreak event.

### General
- **datemanager** – library module for date-limited (seasonal) modules: checks whether today lies in an `MM-DD` start/end window (also across the turn of the year, with optional leniency days) and provides countdown helpers. Required by `halloween`, `pumpkin` and `xmasdiscount`.
- **mightyblogs** – public blogging system that lets authorised users post long-form updates accessible from the village, cemetery, and index. Older releases had serious issues; upgrade servers to at least version 1.1.

### Hall of Fame displays
- **hofalignment** – Hall of Fame page for the most good or evil characters (requires `alignment`).
- **hofbattlearena** – Battle Arena rankings in the Hall of Fame (requires `battlearena`, not part of this repository yet).
- **hofclandk** – dragon kills per clan in the Hall of Fame.
- **hofclanpk** – player kills per clan in the Hall of Fame (requires `pktrack`).
- **hofdemeanor** – Hall of Fame page for the most lawful or chaotic characters (requires `alignment`).

### Items
- **inventory** - the entire updated item system by XChrisX for run with +nb core. Item and inventory data access runs through a Doctrine based service layer (`inventory/lib/Domain`, `Repository`, `Service`); the classic `itemhandler.php` functions remain available for other modules.
- **findloot** – hooks `battle-victory` to roll for loot items via the inventory system (`items/findloot.php`).

### Lodge
- **lodge_colortable** – adds a Lodge navigation link displaying the LoTGD color table and a sample text input (`lodge/lodge_colortable.php`).
- **lodgedkpointreset** – reset spent lodge points for a dragon kill.
- **lodgenonexpiration** – purchase non‑expiration account upgrade.
- **namechange** – allow players to change character name.

### Mail
- **mailarchive** – archive incoming messages into user-defined categories for long-term storage.
- **mailnotepad** – provides personal notepad fields inside the mail interface.
- **outbox** – review or optionally mirror sent messages, with support for a full separate outbox.

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
- **beggarslane** – explore Beggars Lane in the village.
- **cityamwayr** – adds the Amwayr city with configurable travel routes, minimum dragon kill access requirement, and city-specific PvP handling.
- **halleyscorner** – Halley’s Corner travel option.
- **invitationzones** – fighting zones accessible by invitation (requires `fightingzone`).
- **madmax** – multiplayer word game (requires `playergames`).
- **ninjamerchantstore** – ninja merchant village shop.
- **playergames** – framework for small player games.
- **sympathy** – allow players to purchase sympathy.

## Additional notes

Some modules require others to be installed first (as listed above). Two of those dependencies (`avatar`, `battlearena`) are not published here yet. All modules assume a working NB-Core/lotgd installation. Enable or disable modules from the game’s Module Manager.

When a module comes with a folder of the same name (e.g. `pumpkin.php` and `pumpkin/`), copy both into your game's `modules/` directory.

## Additional modules

Some additional modules have been created by [Jeffrey Hoegee](https://github.com/Avanae/lotgd-modules/tree/main).
These work alongside the +nb framework (and the his edition), provided a proper installation is in place.

Many thanks to my pal [Jeffrey Hoegee](https://github.com/Avanae/lotgd-modules/tree/main) for his contributions and for helping to broaden the ecosystem.
