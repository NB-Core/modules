<?php
page_header("The Secret Cave");
output("`c`t~~~ `1T`lhe `1S`lecret `1C`lave `t~~~`c`n`n");
addnav("Leave...");
addnav("Leave the cave","forest.php");
addnav("Actions");
$subop=httpget('subop');
switch ($subop) {
	case "confirm":
		$module=httpget('item');
		modulehook("circulum-chosen",array("chosen"=>$module));
		//execute here the module and what it needs to do after it was selected, there is a hook alter in the reset also
		output("`%The old man starts to chant something you don't understand and forms quick hand symbols, too fast for your eye, even though you thought you already are almost godlike...`n`n");
		output("Your mind goes blank and you close your eyes... sleep, thicker and blacker than any you experienced conquers you... is forcing himself into your head.`n`n");
		output("`c`\$You are reborn`c`n`n`%");
		require_once("modules/circulum/func/circulum_reset.php");
		$name=$session['user']['name'];
		// make SURE no buffs are active that do any tempstat nasty stuff
		require_once("lib/buffs.php");
		restore_buff_fields();
		circulum_do_reset();
		//give a new day
		$sql = "UPDATE ".db_prefix('accounts')." SET lasthit='".DATETIME_DATEMIN."' WHERE acctid='{$session['user']['acctid']}'";
		db_query($sql);
		blocknav("forest.php");
		require_once("lib/debuglog.php");
		debuglog($name." has chosen the benefit of module $module");
		addnav("");
		addnav("It is a new day!","newday.php?continue=village");
		addnews("%s`\$ was reborn!`0",$name);
		break;
	case "preconfirm":
		output("`%The old man says: `n\"`3Are you really really sure to be reborn? Mind that you will lose:`n`n");
		output("`b`i`@`n*gems`n*gold`n*mount`n*companion`n*charm`n*stats`n*weapon`n*armor`n*skillpoints`n*curse seal`n*seven star tattoo `n*all tattoos`n*playerkills`n*rasengan`n*and more`i`b");
		output("`n`nYou will keep for sure:`n`b`i`2*alignment`n*spouse/fiancee`n*dwellings(though you might not have access to certain cities anymore)`n*all donation point related things like avatar, coloured name, title`n*and more`n`n");
		output("<h1>`jThere is no turning back! No \"undo\" to this! Only hit \"Yes\"</h1>`\$<h1>if you are absolutely sure!</h1>`j",true);
		addnav("`\$Yes","runmodule.php?module=circulum&op=hiddencave&subop=confirm&item=".httpget('item'));
		addnav("No","runmodule.php?module=circulum&op=hiddencave");
		break;
	case "view":
		output("`%You decide to let the old man go on and ask him to show what he has.`n`n");
		
		output("`i`@(Note: You will lose everything: gems, gold, mount, companion. You will keep selected things like your clan, your rank there, your marriage, title, donation points,any items you have.)`i`%`n`n");
		// translate it suing the ingame translator if you want to add something or leave out something in your game
		
		$args=modulehook('circulum-items',array());
		//if not set to active, you may put different prerequisites into the description, i.e. you might want to make some stackable and some not, it's up to you to determine in your module. view the example module delivered with this package to see how you work with this hook.
		//sorting
		$kekkei=array();
		$lockcat=array();
		foreach ($args as $kg) {
			if (isset($kg['category']) && $kg['category']!='') {
				if (((int)get_module_pref('stack',$kg['modulename']))>0) $lockcat[$kg['category']][]=$kg['modulename'];
				$kekkei[$kg['category']][$kg['modulename']]=$kg;
			} else {
				$kekkei['No Category'][$kg['modulename']]=$kg;
			}
		}
		
		foreach (array_keys($kekkei) as $category) {
			ksort($kekkei[$category]);
		}
		//
		addnav("Choose wisely...");
		if ($args===array()) {
			output("`%\"`3Sorry, I have nothing to offer right now. May the gods offer you something soon!`%\"");
		} else {
			rawoutput("<table style='border-spacing:0px'>");
			foreach ($kekkei as $category=>$cat) {
				$class='';
				addnav_notl($category);
				//output("`0`c`@~~~~~~~~~~~~%s~~~~~~~~~~~~`0`c`4",$category);
				output_notl("<tr style='background-color:#000000'><td style='font-size:large;text-align:center;font-family:fantasy,Times,serif;color:#FA00FA;'>~~~~~~~~~~~~%s~~~~~~~~~~~~</td></tr>",sanitize($category),true);
				foreach ($cat as $gift) {
					$class=($class=='trlight'?'trdark':'trlight');
					rawoutput("<tr class='$class'><td>");
					$locked=false;
					if (isset($gift['category']) && isset($gift['exclusive_in_category']) && $gift['exclusive_in_category']) {
						if (!isset($lockcat[$gift['category']])) $locked=false;
							elseif (count($lockcat[$gift['category']])>1 || $lockcat[$gift['category']][0]!=$gift['modulename']) $locked=true;
							else $locked=false;
					}
					output_notl($gift['description']);
					if (isset($gift['exclusive_in_category']) && $gift['exclusive_in_category']) output("`n`n`c`b`\$This ability is limited to those who have *no* other abilities from this category group!`b`c");
					if (get_module_setting('maxstack',$gift['modulename'])>get_module_pref('stack',$gift['modulename'])) $stacknotfull=true;
						else $stacknotfull=false;
					if (!$stacknotfull) output("`c`b`\$This ability is maxed out!`b`c");
					if ($gift['active'] && !$locked && $stacknotfull) {
						addnav_notl($gift['nav'],"runmodule.php?module=circulum&op=hiddencave&subop=preconfirm&item=".$gift['modulename']);
					} else {
						addnav_notl($gift['nav'],""); //display inactive nav
					}
					rawoutput("</td></tr>");
				}
			}
			rawoutput("</table>");
		}
		break;
	
	default:
		output("`%You stroll through the forest as you come up to a hidden cave. You are almost sure there was no cave before... but how... well... you decide to take a look.`n");
		output("As you enter, you can see sealing symbols written in ancient languages all over the place. This must be what hides this cave, but how were you now able to find it?`n");
		output("`n`nAs you ponder about that, suddenly a white-haired old man steps out of the dark: `n\"`3Hello there... I greet you, my name is nothing of your concern, let me just welcome you to the `1S`lecret `1C`lave`3 only few know about.`n`nWhat you are going to see here might be strange, and not really understandable.`n`n");
		output("You can here exchange, `\$`bat the cost of almost everything you know of`b`3, you are or you have, things that are truly unique. They cannot be bought with any wealth *coughcough*or donation points *coughcough*. Do you wish to see the benefits you might enjoy for giving up everything?`%\"`n`n");
		output("`n`n`i`b`xBluntly said, you will lose all your gold, gems, killpoints, charm, abilities, mount, everything. Things like your dwelling or other things stay.`b`i");
		addnav("`\$Let's see it","runmodule.php?module=circulum&op=hiddencave&op=hiddencave&subop=view");
		break;

}
page_footer();

?>
