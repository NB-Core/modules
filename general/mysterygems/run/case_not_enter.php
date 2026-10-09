<?php
set_module_pref('used', get_module_pref('used') + 1);
$mgmod	= floor($ul / 5);
$mgchance	= e_rand(1,100) - $mgmod;
if ($mgchance<1) $mgchance =1;
if ($mgchance>0) $mgresult =1;
if ($mgchance>70) $mgresult =2;
if ($mgchance>96) $mgresult =3;
if (get_module_pref('used') < get_module_setting('times')) addnav("Buy more", "runmodule.php?module=mysterygems&op=enter");
villagenav();
?>