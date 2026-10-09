<?php
if ($args['setting'] == "villagename")
	if ($args['old'] == get_module_setting('mgloc')) set_module_setting('mgloc', $args['new']);
?>