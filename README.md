# NB-Core Modules

This repository provides optional modules for the [NB-Core/lotgd](https://github.com/NB-Core/lotgd) game engine. Most modules also work with DragonPrime LoTGD releases but are primarily maintained for NB-Core.

These modules extend nearly every aspect of the game ranging from administrative helpers to new forest events. Copy the desired PHP files (and folders if present) into your game's `modules/` directory and activate them from the game admin panel.

## Repository layout

Directories group modules by purpose:

- **pvp/** – player versus player extensions and events
- **administrative/** – tools for admins and moderation
- **forest/** – general forest related tweaks
- **forest_specials/** – additional forest encounters and bosses
- **lodge/** – lodge upgrades
- **mounts/** – mount related features
- **systems/** – large feature systems (races, specialties etc.)
- **village_modules/** – village shops and activities

## Module overview

Below is a short description for every module. Some modules rely on others; dependencies are noted where known.

### PVP
- **adminpvp** – grants PvP immunity to game staff.
- **halloween** – seasonal Halloween PvP event.
- **pumpkin** – Halloween pumpkin feature (requires `inventory`).
- **pvpavatars** – shows player avatars during PvP (requires `avatar`).
- **pvpbalance** – balance PvP targets based on dragon kills.
- **pvpquotes** – random quotes when entering PvP fights.
- **serverbalance** – helper for tracking server load.

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
- **recaptcha** – add Google reCAPTCHA to forms.
- **servercostlog** – log server expenses.
- **suspendannounce** – announce server suspensions.
- **welcomemail** – send welcome e‑mail to new users.

### Forest
- **forestmod_new** – quick fights for newer DP versions.
- **healer_buffremoval** – healer can remove negative buffs.

### Forest specials
- **akatsuki** – Akatsuki encounter.
- **chipmunks** – mischievous chipmunks that steal gold.
- **chipmunk_boss** – chipmunk boss (requires `chipmunks`).
- **elosassfall** – Elessa’s waterfall special.
- **erosennin** – meet Ero‑Sennin.
- **evil_punishers** – evil version of the punishers (requires `alignment`).
- **ladyerwin** – Lady Erwin encounter.
- **mrblack** – Mr. Black encounter (requires `ladyerwin`).
- **ninjamerchant** – wandering ninja merchant.
- **punishers** – the punishers encounter (requires `alignment`).
- **thegrinch** – Grinch mini‑boss.
- **vampirelord_bride** – bride of the vampire lord (requires `vampirelord`).
- **wedgieman** – the fearsome Wedgie Man.
- **zombie** – zombie outbreak event.

### Lodge
- **lodgedkpointreset** – reset spent lodge points for a dragon kill.
- **lodgenonexpiration** – purchase non‑expiration account upgrade.
- **namechange** – allow players to change character name.

### Mounts
- **mountstables** – extra mount stable slots.
- **xmasdiscount** – discounted mount prices around Christmas.

### Systems
- **circulum** – core reset system called “Circulum Vitae”.
- **circulum_hof** – Hall of Fame for circulum resets (requires `circulum`).
- **circulum_prefreset** – preference reset for circulum (requires `circulum`).
- **circulum_presave** – character restore helper.
- **circulum_uchiha** – Uchiha clan bonus (requires `circulum`).
- **marriage** – marriage system for players.
- **racesystem** – alternative race handling.
- **savedays** – track and save player day counters.
- **specialtysystem** – core specialty framework.
- **specialtysystem_basic** – basic ninja skills (requires `specialtysystem`).
- **specialtysystem_earth** – earth specialties (requires `specialtysystem`).
- **specialtysystem_fire** – fire specialties (requires `specialtysystem`).
- **specialtysystem_genjutsu** – genjutsu specialties (requires `specialtysystem`).
- **specialtysystem_ice** – ice specialties (requires `specialtysystem`).
- **specialtysystem_lightning** – lightning specialties (requires `specialtysystem`).
- **specialtysystem_medical** – medical specialties (requires `specialtysystem`).
- **translationwizard** – translation management wizard for text output.

### Village modules
- **beggarslane** – explore Beggars Lane in the village.
- **halleyscorner** – Halley’s Corner travel option.
- **invitationzones** – fighting zones accessible by invitation (requires `fightingzone`).
- **madmax** – multiplayer word game (requires `playergames`).
- **ninjamerchantstore** – ninja merchant village shop.
- **playergames** – framework for small player games.
- **sympathy** – allow players to purchase sympathy.

## Additional notes

Some modules require others to be installed first (as listed above). All modules assume a working NB-Core/lotgd installation. Enable or disable modules from the game’s Module Manager.
