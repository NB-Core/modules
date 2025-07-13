<?php
declare(strict_types=1);
set_module_pref("view",!$viewsimple,"translationwizard");
redirect("runmodule.php?$from"); //just redirecting to the main to make your choice visible by now
?>