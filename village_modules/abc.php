<?php

function abc_getmoduleinfo(){
	$info = array(
		"name"=>"Aravis' Talismans",
		"author"=>"Chris Vorndran, modified by `2Oliver Brendel",
		"version"=>"1.0",
		"category"=>"Village",
		"description"=>"A shoppe that sells Talismans. A Talisman will revive a character upon death.",
		"download"=>"http://dragonprime.net/users/Sichae/abc.zip",
		"vertxtloc"=>"http://dragonprime.net/users/Sichae/",
		"settings"=>array(
			"Aravis' Talismans Settings,title",
				"name"=>"Name of the girl,text|`%Aravis",
				"favor"=>"How much favor does a Talisman cost?,int|100",
				"max"=>"Chance (%) that a Talisman will malfunction?,range,0,10,1|5",
				"tloss"=>"How many turns are lost upon Talisman Ressurection?,range,1,10,1|6",
				"abcloc"=>"Where is Aravis stationed?,location|".getsetting("villagename", LOCATION_FIELDS),
			),
		"prefs"=>array(
			"Aravis' Talismans Prefs,title",
				"has"=>"Does the user have a Talisman?,bool|0",
			),
		);
	return $info;
}
function abc_install(){
	module_addhook("changesetting");
	module_addhook("village");
	module_addhook("ramiusfavors");
	return true;
}
function abc_uninstall(){
	return true;
}
function abc_dohook($hookname,$args){
	global $session;
	switch ($hookname){
		case "changesetting":
			if ($args['setting'] == "villagename"){
				if ($args['old'] == get_module_setting("abcloc")){
					set_module_setting("abcloc",$args['new']);
				}
			}
			break;
		case "village":
			if ($session['user']['location'] == get_module_setting("abcloc")){
				tlschema($args['schemas']['fightnav']);
				addnav($args['fightnav']);
				tlschema();
				addnav(array("%s`)'s Talismans",get_module_setting("name")),"runmodule.php?module=abc&op=enter");
			}
			break;
		case "ramiusfavors":
			if (get_module_pref('has')) {
				addnav("Special");
				addnav("Show Talisman","runmodule.php?module=abc&op=res");
			}
			break;
		}
	return $args;
}
function abc_run(){
	global $session;
	$op = httpget('op');
	$favor = get_module_setting("favor");
	$mchance = get_module_setting("max");
	$g = translate_inline($session['user']['sex']==1?"miss":"mister");
	$aravis=get_module_setting('name');
	page_header("%s's Talismans",sanitize($aravis));
	switch ($op){
		case "enter":
			if (!get_module_pref("has")){
				output("`)You push open the door to a small shoppe.");
				output("Entering, you notice a vast array of ornaments amongst the walls.");
				output("Seeing a shining light behind the counter, you walk over.");
				output("`n`nA young girl peeks her head over the counter, \"`%Who is it!?`)\"");
				output("You reply, \"`@This is %s.`)\"",$session['user']['name']);
				output("`n`nAll of a sudden, wings appear on either side of her and %s`) floats a bit higher, gazing into your eyes.",$aravis);
				output("\"`%Oh... hello there %s`%.",$g);
				output("My name is %s`), pleased to meet you... I think...`)\", %s`) smiles.",$aravis,$aravis);
				output("You can see her brow come to a peek and you just continue smiling.");
				output("`n`n\"`%Around here, I sell Talismans.");
				output("In times of great peril, they are said to have powerful regenerative capabilities...`)\"");
				output("%s `)comes really close to you and whispers, \"`%Even the ability to conquer death...`)\"",$aravis);
				output("She flies back and stands on the counter, giggling, \"`%So... you want one?!`)\"");
				addnav(array("Buy a Talisman (%s Favor)",$favor),"runmodule.php?module=abc&op=buy");
			}else{
				output("%s `)smiles cherubically at you, \"`%I am sorry %s, but you have already purchased one of my fine wares...`)\"",$aravis,$g);
				output("She then flits away from you, smiling softly.");
			}
			break;
		case "buy":
			if ($session['user']['deathpower'] >= $favor){
				output("%s`) smiles and claspes her hands together.",$aravis);
				output("She flies up and then dives behind her counter, retrieving a dank piece of metal.");
				output("%s`) sets it around your neck, and it slowly swings left and right.",$aravis);
				output("`n`nShe`) says, \"`%This shall protect you... but be warned... there is a chance that the darkness is too great.");
				output("If that happens, the Talisman may fail... Do not fret, for these Talismans are strong and will not be bested easily...`)\"");
				$session['user']['deathpower']-=$favor;
				set_module_pref("has",1);
			}else{
				output("%s`) gazes upon your aura and shakes her head gently.",$aravis);
				output("\"`%I am terribly sorry... but you do not have the required spiritual strength to bear the Talisman.");
				output("Please return when you have `^%s `%Favor.`)\"",$favor);
				output("%s`) notes, \"`%That is `^%s `%more that you need...`)\"",$aravis,$favor-$session['user']['deathpower']);
			}
			break;
		case "res":
			$deathoverlord=getsetting('deathoverlord','`$Ramius');
			page_header("Pool of Darkness");
			output("`)You stand before %s`) and hold up your `&Talisman`).`n",$deathoverlord);
			output("You are surrounded in darkness, as the pit of your stomach falls out...");
			output("and then you hear %s`) say, '`\$Mhm, you have a `gVIP`\$ card, or so I should say... I will let you go to the living world if you hand me over that `&Talisman`\$...`)'.`n`n",$deathoverlord);
			output("Do you hand it over?");
			addnav("Hand over","runmodule.php?module=abc&op=res2");
			addnav("Leave to the mausoleum","graveyard.php?op=enter");
			break;
		case "res2":
			$deathoverlord=getsetting('deathoverlord','`$Ramius');
			page_header("Pool of Darkness");
			$i = e_rand(1,100);
			if ($mchance != 0 && $i <= $mchance){
				output("`)Yet, it begins to cough and sputter.");
				output("You frown, as your soul is slowly ripped away by %s`).",getsetting('deathoverlord','`$Ramius'));
				output("`n`nIt seems he cheated you... damn almighty powers...");
				addnav("Continue","graveyard.php");
				set_module_pref("has",0);
			}else{
				output("`)You grin, as you hand over the `&Talisman `).`n");
				output("Seeing that %s`) grabs it, you feel somehow very light... and almost shining... you slowly swim towards the surface of the blackness above you...`n`n",getsetting('deathoverlord','`$Ramius'));
				output("Finally you reach the top, and you peek your head above the black.");
				output("Taking in a fresh gulp of air, you feel ready for a newday.");
				addnav("Continue","newday.php?resurrection=true");
				set_module_pref("has",0);
				$session['user']['deathpower']+=100;
				$session['user']['specialinc']='';
				page_footer();
				return;
			}
			break;
		}
villagenav();
page_footer();
}
?>