<?php
declare(strict_types=1);
require_once("lib/pullurl.php");
$log=file("modules/translationwizard/versions.txt");
foreach($log as $val) {
	rawoutput($val);
	output_notl("`n");
	}
?>
