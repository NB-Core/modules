<?php
function akatsuki_getchance() {
	if (!is_module_active("slayerguild")) return 0;
	if (get_module_pref("slayercheater","akatsuki")!=1) return 0;
	return 100;
}
?>