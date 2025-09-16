<?php
	invalidatedatacache("dwellings-sleepers-$dwid");
	redirect($session['user']['restorepage']);
	$session['user']['restorepage'] = "";
?>
