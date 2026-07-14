<?php
$storedinfo = array();
$dwell = httppost('dwell');
$dwid = (int) httpget('dwid');

if (!is_array($dwell)) {
    output("`\$Dwelling save failed: expected submitted dwelling data was not received.`0`n");
    return;
}

// postparse() receives the submitted dwell[...] array and returns the SQL
// assignment/key/value fragments expected by the legacy core dwelling editor.
list($sql, $keys, $vals) = postparse($dwell);

if ($dwid > 0) {
    $sql = "UPDATE " . db_prefix("dwellings") .
        " SET $sql WHERE dwid=$dwid";
} else {
    $sql = "INSERT INTO " . db_prefix("dwellings") .
        " ($keys) VALUES ($vals)";
}

db_query($sql);
if (db_affected_rows() > 0) {
    output("`^Dwelling saved!`0`n");
} else {
    output("`^Dwelling `\$not`^ saved: `\$%s`0`n", $sql);
}
?>
