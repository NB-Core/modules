<?php
	global $session;
	if ($session['user']['superuser'] & SU_EDIT_USERS || get_module_pref("is_itemeditor")) {
		addnav("Editors");
		addnav("X?Item Editor", "runmodule.php?module=inventory&op=editor");
	}
?>
