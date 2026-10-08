<?php

function healer_buffremoval_getmoduleinfo(){
	$info = array(
			"name"=>"Healer removes bad buffs",
			"version"=>"1.0",
			"author"=>"`2Oliver Brendel",
			"category"=>"Forest",
			"download"=>"",
			"vertxtloc"=>"",
			"description"=>"Healer will remove certain buffs that are bad",
			"settings"=>array(
				"General Settings,title",
				"List buffs by unique name so they can be removed - separate by dash like \"buffname\"-\"Name\" and then by semicolon,note",
				"badbuffs"=>"Bad Buffs:,text|buffname-Poisonname;nextbuffname-Nextname",
				"price"=>"Price per player level for each buffremoval,int|100",
				),
		     );
	return $info;
}

function healer_buffremoval_install(){
	module_addhook("footer-healer");
	return true;
}

function healer_buffremoval_uninstall(){
	return true;
}

function healer_getbadbuffs() {
	$bufflist = array();
	$badbuffs = explode(";",get_module_setting('badbuffs'));
	foreach ($badbuffs as $item) {
		$item = explode("-",$item);
		$buffrealname = "an unknown poison";
		if (isset($item[1])) $buffrealname=$item[1];	
		if (isset($item[0])) {
			$buffname=$item[0];
			$bufflist[$buffname]=$buffrealname;
		}
	}
	return $bufflist;
}

/**
 * Return a safe internal destination for the healer's return navigation.
 *
 * The value must survive the intermediate cure request, or the core healer may
 * fall back to the Forest after removing a buff.
 *
 * @return string A validated relative PHP target, or an empty string.
 */
function healer_buffremoval_get_return_destination()
{
    global $session;

    $return = httpget('return');
    if (is_string($return) && preg_match(
        '/^[A-Za-z0-9_-]+\.php(?:\?[A-Za-z0-9_-]+=[A-Za-z0-9_.~-]*(?:&[A-Za-z0-9_-]+=[A-Za-z0-9_.~-]*)*)?$/D',
        $return
    )) {
        return $return;
    }

    $centralVillage = getsetting("villagename", LOCATION_FIELDS);
    $hokageVillage = sanitize(get_module_setting('villagename', 'hokagevillage'));
    $sessionLocation = isset($session['user']['location']) ? $session['user']['location'] : '';
    // Both Forest-restricted villages need a safe fallback even though their
    // normal hospital links include return=village.php.
    if (
        $sessionLocation === $centralVillage
        || ($sessionLocation === $hokageVillage && !get_module_setting('showforest', 'hokagevillage'))
    ) {
        return 'village.php';
    }

    return '';
}

function healer_buffremoval_dohook($hookname,$args){
	global $session;
	static $healer_buffremoval_did_i_run_yet = 0;
//	if ($session['user']['acctid']!=7) return $args; //me
	switch($hookname){
		case "footer-healer":
			$action = (int)httpget('removebuff');
			$returnDestination = healer_buffremoval_get_return_destination();
			$goldcost = get_module_setting('price')*$session['user']['level'];
			$badbuffs=healer_getbadbuffs();
			require_once("lib/buffs.php");
			if ($action!==0) {
				//remove buff before
				$i=1;
				//found, now remove + pay
				$session['user']['gold']-=min($goldcost,$session['user']['gold']);
				foreach($badbuffs as $buffname=>$buffrealname) {
					if ($i!=$action) {
						$i++;
						continue;
					}
					strip_buff($buffname);
					output("`n`n`4\"Alright, `\$%s`4! has been cured!\"\n",$buffrealname);
					return $args;
				}

			}
			if ($healer_buffremoval_did_i_run_yet==0) { 
				$healer_buffremoval_did_i_run_yet=1;
				$i=1;
				foreach($badbuffs as $buffname=>$buffrealname) {
					if (has_buff($buffname)) {
						output("`n`n`4\"Oh my, you have a case of `\$%s`4! Let me help you for a low price.\"\n",$buffrealname);
						addnav("Buff Removal");
						if ($session['user']['gold']<$goldcost) {
							addnav(array("Remove %s - %s gold",$buffrealname,$goldcost),"");
						} else {
							$cureLink = "healer.php?removebuff=$i";
							if ($returnDestination !== '') {
								$cureLink .= '&return=' . rawurlencode($returnDestination);
							}
							addnav(array("Remove %s - %s gold",$buffrealname,$goldcost),$cureLink);
						}
					}
					$i++;
				}	
			}
			break;

	}

	return $args;
}

function healer_buffremoval_run(){
}

?>
