<?php
		addnav("Funny Noises");
		if ($session['user']['location'] == get_module_setting("loveloc")&&get_module_setting("lall")==0) {
			addnav(array("%s Loveshack",$session['user']['location']),"runmodule.php?module=loveshack&op=loveshack");
		} elseif (get_module_setting("lall")==1) {
			addnav(array("The Loveshack"),"runmodule.php?module=loveshack&op=loveshack");
		}
		set_module_pref('inShack',0);
?>