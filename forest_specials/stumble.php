<?php
/*********************************************/
/* Stumble                                   */
/* -------                                   */
/* Version 1.4                               */
/* Written by Jake Taft (Zanzaras)           */
/* Based on a mod by an unknown programmer   */
/*********************************************/

/************************************************************************************/
/* Version History                                                                  */
/* ---------------                                                                  */
/* 1.0 - Original Release - Zanzaras                                                */
/* 1.1 - Modified for use with LotGD .98 - Zanzaras                                 */
/* 1.11 - Added the missing stumble_run() function - Zanzaras                       */
/* 1.2 - Made translator ready & made user variables conform to standard - Zanzaras */
/* 1.21 - Added support for Lonny's Module Update checker.                          */
/* 1.22 - Fixed a tiny goof. - Zanzaras                                             */
/* 1.3 - Added the ability for admins to select how many gems are found and how     */
/*       much damage is done (module can handle a zero reward field if the admin    */
/*       simply wants to make life miserable for the players); Found and fixed a    */
/*       non-translated output line; Fixed a bug that could possibly execute the    */
/*       module in town after a ressurection. - Zanzaras                            */
/* 1.4 - Added a description field to the module. - Zanzaras                        */
/************************************************************************************/

/*********************************************************************************/
/* Setup instructions                                                            */
/* ------------------                                                            */
/* Copy this file to the "Module" directory inside the main lotgd directory then */
/* in the game go to the Manage modules in the Grotto and Install/activate it.   */
/*********************************************************************************/

function stumble_getmoduleinfo()
         {$info = array(
                  "name"=>"Stumble",
                  "author"=>"Jake Taft (Zanzaras)",
                  "version"=>"1.4",
                  "category"=>"Forest Specials",
                  "download"=>"http://www.dragonprime.net/users/Zanzaras/Stumble%20Module.zip",
                  "description"=>"The players trip and, if they survive, find a gem(s).",
                  "vertxtloc"=>"http://www.dragonprime.net/users/Zanzaras/",
                  "settings"=>array("Stumble - Settings,title",
                                   "stumblereward"=>"Number of gems a player can find.,int|1",
                                   "stumbledamage"=>"Percentage of damage player takes on falling.,int|25"));
          return $info;
         }

function stumble_install()
         {module_addeventhook("forest", "return 100;");
          return true;
         }

function stumble_uninstall(){return true;}

function stumble_dohook($hookname,$args){return $args;}

function stumble_runevent($type)
{global $session;
 $from = "forest.php?";
 $session['user']['specialinc'] = "module:stumble";
 $op = httpget('op');
 $reward = get_module_setting("stumblereward");
 $damage = get_module_setting("stumbledamage");
 $damage = round(($damage * .01)*$session['user']['maxhitpoints']);

 output("`#As you sneak up on an unsuspecting, evil-looking bunny, You trip on something and slam your head hard on a rock!");
 $session['user']['hitpoints']-=$damage;
 if ($session['user']['hitpoints']<=0)
    {output("`n`n`4You lose %s hitpoints!`n",$damage);
     output("You're `bDEAD`b!`n");
     output("All gold has been lost!`n");
     output("You may continue playing tomorrow.`#`n");
     $session['user']['turns'] = 0;
     $session['user']['hitpoints'] = 0;
     $session['user']['gold'] = 0;
     $session['user']['alive'] = false;
     addnav("Daily News","news.php");
     addnews("%s was killed after tripping over a rock and hitting %s noggin'!",$session['user']['name'],translate_inline($session['user']['sex'] ? "her" : "his"));
    }
   else
    {output("As you lay there holding your throbbing head and thinking about how stupid you were for not looking where you were going...`n`n");
     if ($reward > 1) output("...you spot `^%s gems`# laying in the grass beside you, but lose `4%s `#hitpoints from slamming your head on that rock.`#`n",$reward,$damage);
     if ($reward == 1) output("...you spot `^a gem`# laying in the grass beside you, but lose `4%s `#hitpoints from slamming your head on that rock.`#`n",$damage);
     if ($reward <=0)
        {$reward = 0;
         output("...you spot that evil-looking bunny laughing at you so hard that it can barely breath.`n`n");
         output("You lose `4%s `#hitpoints from slamming your head on that rock.`#`n",$damage);
        }
     $session['user']['gems']+=$reward;
     debuglog("found $reward gem(s) in the forest after hitting his/her head on a rock.");
    }
 $session['user']['specialinc']="";
}

function stumble_run(){}
?>
