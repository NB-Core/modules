<?php
    $dwid = httpget('dwid');
    $dwid = ctype_digit((string) $dwid) ? (int) $dwid : false;
    page_header("Edit dwellings");
    output("Dwelling Editor:`n");
	require_once("modules/dwellingseditor/lib.php");
    dwellingform();
?>