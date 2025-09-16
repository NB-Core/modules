<?php
			$val = get_module_pref('alignment');
			$demeanor = get_module_pref('demeanor');
			$title = translate_inline('Alignment');
			$area = get_module_setting('shead');
			$good = translate_inline('`@Good`0');
			$evil = translate_inline('`$Evil`0');
			$neutral = translate_inline('`6Neutral`0');
			$chaos = translate_inline('`)Chaotic`0');
			$lawful = translate_inline('`&Lawful`0');
			$true = translate_inline('`^True`0');
			$evilalign = get_module_setting('evilalign');
			$goodalign = get_module_setting('goodalign');
			$chaosalign = get_module_setting('chaosalign');
			$lawalign = get_module_setting('lawfulalign');
			$extra = "";
			if ($demeanor >= $lawalign){
				$de = $lawful;
			} elseif ($demeanor <= $chaosalign){
				$de = $chaos;
			} else {
				$de = $neutral;
			}
			if (get_module_setting("display-num")) $extra = "(`b$val|$demeanor`b)";
			if ($val >= $goodalign){
				$al=$good;
			}elseif ($val <= $evilalign){
				$al=$evil;
			}else {
				$al=$neutral;
			}
			if ($de==$neutral && $al==$neutral) {
				$de=$true;
			}
			$color = sprintf("`b%s %s`b %s",$de,$al,$extra);
			setcharstat($area,$title,$color);
?>