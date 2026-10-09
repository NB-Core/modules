<?php

require_once("lib/http.php");
require_once("lib/villagenav.php");

function dagkirin_getmoduleinfo(){
    $info = array(
        "name"=>"Kirin Hunt",
        "version"=>"1.0",
        "author"=>"`AI, based on work from Sneakabout Manticore",
        "category"=>"Quest",
        "download"=>"",
        "settings"=>array(
            "Kirin Hunt Settings,title",
            "rewardgold"=>"What is the gold reward for the Kirin Hunt?,int|2000",
            "rewardgems"=>"What is the gem reward for the Kirin Hunt?,int|3",
            "experience"=>"What is the quest experience multiplier for the Kirin Hunt?,floatrange,1.01,1.2,0.01|1.1",
            "minlevel"=>"What is the minimum level for this quest?,range,1,15|1",
            "maxlevel"=>"What is the maximum level for this quest?,range,1,15|15",
        ),
        "prefs"=>array(
            "Kirin Hunt Preferences,title",
            "status"=>"How far has the player gotten in the Kirin Hunt?,int|0",
        ),
        "requires"=>array(
            "dagquests"=>"1.1|By Sneakabout",
            "lunarnewyear"=>"1.01|By Oliver Brendel"
        ),
    );
    return $info;
}

function dagkirin_install(){
    module_addhook("village");
    module_addhook("dragonkilltext");
    module_addhook("newday");
    module_addhook("dagquests");
    return true;
}

function dagkirin_uninstall(){
    return true;
}

function dagkirin_dohook($hookname,$args){
    global $session;
    switch ($hookname) {
    case "village":
        if ($session['user']['location'] == getsetting("villagename", LOCATION_FIELDS)) {
            tlschema($args['schemas']['gatenav']);
            addnav($args['gatenav']);
            tlschema();
            if (get_module_pref("status") == 1) {
                addnav("Search the Forest (1 turn)", "runmodule.php?module=dagkirin&op=search");
            }
        }
        break;
    case "dragonkilltext":
        set_module_pref("status", 0);
        break;
    case "newday":
        if (get_module_pref("status") == 1 && $session['user']['level'] > (get_module_setting("maxlevel") + 1)) {
            set_module_pref("status", 4);
            output("`n`6You hear that another ninja has defeated the Kirin that appeared during the Lunar New Year.`0`n");
            require_once("modules/dagquests.php");
            dagquests_alterrep(-1);
        }
        break;
    case "dagquests":
        if ($args['questoffer']) break;
        // Check if it is the lunar new year by querying the lunarnewyear module
        $pre_days = get_module_setting('pre_days','lunarnewyear');
	    $post_days = get_module_setting('post_days','lunarnewyear');
        require_once("modules/lunarnewyear.php");
        $is_lunarnewyear =  lunarnewyear_islunarnewyear($pre_days, $post_days);
        if (!$is_lunarnewyear) break;
        if (get_module_setting("minlevel") <= $session['user']['level'] &&
            $session['user']['level'] <= get_module_setting("maxlevel") &&
            !get_module_pref("status")) {
            output("`n`nYou see a masked figure approach you, his presence commanding and his voice low.`n`n");
            output("\"I've heard about your skills. We have a situation. A mystical Kirin has appeared during the Lunar New Year, causing chaos in the surrounding forests. This creature is highly dangerous and elusive. If you're up for the challenge, there will be rewards. Will you accept this mission?\"");
            output("`n`n`4You hesitate for a moment, considering the danger, but the thrill of the hunt and the promise of rewards drive you to nod in agreement.");
	    addnav("Mission");
            addnav("Accept the Mission", "runmodule.php?module=dagkirin&op=take");
            addnav("Decline", "runmodule.php?module=dagkirin&op=nottake");
            $args['questoffer'] = 1;
        }
        break;
    }
    return $args;
}

function dagkirin_runevent($type) {
}

function dagkirin_run(){
    global $session;
    $op = httpget('op');
    
    switch($op){
    case "take":
        $iname = getsetting("innname", LOCATION_INN);
        page_header($iname);
        rawoutput("<span style='color: #9900FF'>");
        output_notl("`c`b");
        output($iname);
        output_notl("`b`c");
        output("`3The masked figure nods approvingly and gives you directions to the forest where the Kirin was last seen.");
        output("He advises you to prepare well before embarking on this dangerous mission.");
        set_module_pref("status", 1);
        addnav("I?Return to the Inn", "inn.php");
        break;
    case "nottake":
        $iname = getsetting("innname", LOCATION_INN);
        page_header($iname);
        rawoutput("<span style='color: #9900FF'>");
        output_notl("`c`b");
        output($iname);
        output_notl("`b`c");
        output("`3The masked figure shrugs slightly and turns away, disappearing into the shadows.");
        output("You feel a bit disappointed with yourself as you leave the inn.");
        set_module_pref("status", 4);
        addnav("I?Return to the Inn", "inn.php");
        require_once("modules/dagquests.php");
        dagquests_alterrep(-1);
        break;
    case "search":
        page_header("The Forest");
        if (!$session['user']['turns']) {
            output("`2You feel too exhausted to venture into the forest today. Maybe tomorrow.`n`n");
            villagenav();
            page_footer();
        }
        output("`2You follow the directions given to you and enter the forest, your senses alert for any sign of the Kirin.");
        $session['user']['turns']--;
        output("You begin your search, looking for any clues or signs of the mystical beast.");
        $rand = e_rand(1, 7);
        switch($rand){
        case 1:
        case 2:
            output("After hours of searching, you find yourself on a path that seems oddly familiar. Realizing you're back where you started, you head back to the village, your time wasted.");
            villagenav();
            break;
        case 3:
        case 4:
            output("As you move deeper into the forest, you hear a rustling sound. Suddenly, a rogue ninja appears, attacking you out of nowhere!");
            output("You ready your %s`2 to defend yourself!", $session['user']['weapon']);
            addnav("Fight the Rogue Ninja", "runmodule.php?module=dagkirin&fight=ninjafight");
            break;
        case 5:
        case 6:
        case 7:
            output("You finally spot a shimmering figure in the distance, the Kirin! You prepare yourself for a challenging battle as the majestic creature notices your presence and gets ready to defend itself.");
            addnav("Fight the Kirin", "runmodule.php?module=dagkirin&fight=kirinfight");
            break;
        }
        break;
    }
    $fight = httpget("fight");
    switch($fight){
    case "ninjafight":
        $badguy = array(
            "creaturename" => translate_inline("Rogue Ninja"),
            "creaturelevel" => $session['user']['level'] - 1,
            "creatureweapon" => translate_inline("Kunai"),
            "creatureattack" => $session['user']['attack'],
            "creaturedefense" => round($session['user']['defense'] * 0.75, 0),
            "creaturehealth" => round($session['user']['maxhitpoints'] * 1.1, 0), 
            "diddamage" => 0,
            "type" => "quest"
        );
        $session['user']['badguy'] = createstring($badguy);
        $battle = true;
        // drop through
    case "ninjafighting":
        page_header("The Forest");
        require_once("lib/fightnav.php");
        include("battle.php");
        if ($victory) {
            output("`2The rogue ninja falls to the ground, defeated.");
            if ($session['user']['hitpoints'] <= 0) {
                output("`n`n`^You manage to stabilize yourself with a healing jutsu before you bleed out.`n");
                $session['user']['hitpoints'] = 1;
            }
            output("`2You decide to retreat and recover before continuing your search for the Kirin.`n`n");
            $expgain = round($session['user']['experience'] * (e_rand(2, 4) * 0.002));
            $session['user']['experience'] += $expgain;
            output("`&You gain %s experience from this fight!", $expgain);
            output("`2You return to the village, planning your next move.");
            villagenav();
        } elseif ($defeat) {
            output("`6Your vision fades as the rogue ninja lands a fatal blow.`n`n");
            output("`%You have died!`n");
            output("You lose 10% of your experience, and your gold is taken by the rogue ninja!`n");
            output("Your soul drifts to the shades.");
            $session['user']['gold'] = 0;
            $session['user']['experience'] *= 0.9;
            $session['user']['alive'] = false;
            debuglog("was killed by a rogue ninja in the forest.");
            addnews("%s's body was found in the forest, killed by a rogue ninja!", $session['user']['name']);
            addnav("Return to the News", "news.php");
        } else {
            fightnav(true, true, "runmodule.php?module=dagkirin&fight=ninjafighting");
        }
        break;
    case "kirinfight":
        $badguy = array(
            "creaturename" => translate_inline("Kirin"),
            "creaturelevel" => $session['user']['level'] + 2,
            "creatureweapon" => translate_inline("Mystical Horn"),
            "creatureattack" => round($session['user']['attack'] * 1.15, 0),
            "creaturedefense" => round($session['user']['defense'] * 1.1, 0),
            "creaturehealth" => round($session['user']['maxhitpoints'] * 1.4, 0), 
            "diddamage" => 0,
            "type" => "quest"
        );
        apply_buff('kirinlightning', array(
            "name" => "`\$Kirin's Lightning",
            "roundmsg" => "The Kirin summons a lightning storm, striking you with intense energy!",
            "effectmsg" => "You are hit by a lightning bolt for `4{damage}`) points!",
            "effectnodmgmsg" => "You dodge a lightning bolt!",
            "rounds" => 20,
            "wearoff" => "The Kirin's storm dissipates.",
            "minioncount" => 3,
            "maxgoodguydamage" => $session['user']['level'] * sqrt($session['user']['dragonkills']),
            "schema" => "module-dagkirin"
        ));
        $session['user']['badguy'] = createstring($badguy);
        $battle = true;
        // drop through
    case "kirinfighting":
        page_header("The Forest");
        require_once("lib/fightnav.php");
        include("battle.php");
        if ($victory) {
            output("`2With a final blow, the Kirin collapses, its mystical energy fading away.");
            output("You have successfully defeated the Kirin and brought peace back to the forest.`n`n");
            $expgain = round($session['user']['experience'] * (get_module_setting("experience") - 1), 0);
            $session['user']['experience'] += $expgain;
            output("`&You gain %s experience from this fight!", $expgain);
            if ($session['user']['hitpoints'] < 1) {
                output("Barely alive, you find a hidden healing herb left by a grateful forest spirit.");
                output("Consuming it, your wounds start to heal, but you're still weak.");
                $session['user']['hitpoints'] = 1;
            }
            $goldgain = get_module_setting("rewardgold");
            $gemgain = get_module_setting("rewardgems");
            $session['user']['gold'] += $goldgain;
            $session['user']['gems'] += $gemgain;
            debuglog("found $goldgain gold and $gemgain gems after slaying the Kirin.");
            output("`n`n`2After searching the area, you find `^%s gold`2 and `%%s %s`2 hidden by the Kirin.", $goldgain, $gemgain, translate_inline(($gemgain == 1) ? "gem" : "gems"));
            output("You head back to the village, carrying news of your victory.");
            set_module_pref("status", 2);
            addnews("%s has defeated the Kirin during the Lunar New Year, restoring peace to the forest!", $session['user']['name']);
            villagenav();
            strip_buff("kirinlightning");
            require_once("modules/dagquests.php");
            dagquests_alterrep(3);
        } elseif ($defeat) {
            output("`2The Kirin's final lightning strike hits you, and you fall to the ground, defeated.");
            output("You have failed to defeat the Kirin!`n`n");
            output("`%You have died!`n");
            output("You lose 10% of your experience, and your gold is taken by forest scavengers!`n");
            output("Your soul drifts to the shades.");
            debuglog("was killed by the Kirin in the forest and lost " . $session['user']['gold'] . " gold.");
            $session['user']['gold'] = 0;
            $session['user']['experience'] *= 0.9;
            $session['user']['alive'] = false;
            set_module_pref("status", 3);
            addnews("%s was slain by the Kirin during the Lunar New Year!", $session['user']['name']);
            addnav("Return to the News", "news.php");
            strip_buff("kirinlightning");
            require_once("modules/dagquests.php");
            dagquests_alterrep(-1);
        } else {
            fightnav(true, true, "runmodule.php?module=dagkirin&fight=kirinfighting");
        }
        break;
    }
    page_footer();
}
?>

