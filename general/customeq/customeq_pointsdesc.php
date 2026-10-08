<?php
$args['count']++;
$format = $args['format'];
$wcost = get_module_setting("weaponcost");
$acost = get_module_setting("armorcost");
$xwcost = get_module_setting("extraweapon");
$xacost = get_module_setting("extraarmor");
if ($keep == 0){
	$basestr = translate("A custom %s costs %s points");
	$extrastr = translate(" for the first change and %s points for subsequent changes.");
}elseif ($keep == 1){
	$basestr = translate("Renaming your %s until you kill the dragon costs %s points.");
}else {
	$basestr = translate("Renaming your %s until you get a new one costs %s points.");
}
if ($xwcost && $keep == 0){
	$wstr = sprintf($basestr.$extrastr, translate_inline("weapon"), $wcost, $xwcost);
	}else{
	$wstr = sprintf($basestr.".", translate_inline("weapon"), $wcost);
}
if ($xwcost && $keep == 0){
	$astr = sprintf($basestr.$extrastr, translate_inline("armor"), $acost, $xacost);
}else{
	$astr = sprintf($basestr.".", translate_inline("armor"), $acost);
}
output($format, $wstr, true);
output($format, $astr, true);
?>
