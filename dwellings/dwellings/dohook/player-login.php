<?php
        $page = $session['user']['restorepage'];
        if (substr($page, 0, 44) == "runmodule.php?module=dwellings&op=enter&dwid") {
                // BWAHAHAHAHACK!
                $grab = explode("=", $page);
                $dwid = $grab[count($grab) - 1];
		
                // Debug logging
                debuglog("player-login restorepage: {$session['user']['restorepage']}");
                debuglog("player-login dwid: {$dwid}");

                if ($dwid !== "" && ctype_digit($dwid)) {
                        invalidatedatacache("dwellings-sleepers-$dwid");
                } else {
                        $dwid = get_module_pref("dwelling_saver", "dwellings");
                        if ($dwid !== "" && ctype_digit((string) $dwid)) {
                                $session['user']['restorepage'] = "runmodule.php?module=dwellings&op=enter&dwid=$dwid";
				debuglog("player had good dwelling set, $dwid - dwid is not right, back to dwelling");
                        } else {
                                $session['user']['restorepage'] = "village.php";
				debuglog("player had bad dwelling set, $dwid - dwid is not set right, back to village");
                        }
                }
        }
?>
