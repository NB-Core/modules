<?php
    $dwid = httpget('dwid');
    $dwid = ctype_digit((string) $dwid) ? (int) $dwid : 0;
    page_header("Edit dwellings");
    output("Dwelling Editor:`n");
	require_once("modules/dwellingseditor/lib.php");
    dwellingform();
?>