<?php


function clanrequirements_getmoduleinfo(){
    $info = array(
        "name"=>"Clan Requirements",
        "version"=>"1.0",
        "author"=>"`2Oliver Brendel",
        "category"=>"Clan",
        "download"=>"",
        "settings"=>array(
            "Clan Reqs - Settings,title",
			"mindks"=>"How many dks do founders need, range,1,30,1|8",
			),        
    );
    return $info;
}

function clanrequirements_install(){
	module_addhook("footer-clan");
    return true;
}

function clanrequirements_uninstall(){
    return true;
}

function clanrequirements_dohook($hookname,$args){
    global $session;
    switch($hookname){
		case "footer-clan":
			$dks=(int)get_module_setting('mindks');
			if (httpget('op')!="" && httpget('op')!="withdraw") break;
			if ($session['user']['dragonkills']<$dks) {
				blocknav("clan.php?op=new");
				output("`7`n`n%s`7 notes, after an intense look at you, \"`%Sorry, but you are too young to make your own clan. Gain some experience, fight you-know-whom at Level 15 more often, until you have done so %s times in total. Thank you for your understanding.`7\"",getsetting('clanregistrar','`%Karissa'),$dks);
			}
			break;
		}
    return $args;
}

function clanrequirements_run () {

}
?>
