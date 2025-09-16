<?php
			require_once("modules/alignment/func.php");
			$title = translate_inline("Alignment");
			$area = get_module_setting("shead");
			$good = translate_inline("`@Good`0");
			$evil = translate_inline("`\$Evil`0");
			$neutral = translate_inline("`6Neutral`0");
			$chaos = translate_inline("`)Chaotic`0");
			$lawful = translate_inline("`&Lawful`0");
			$true = translate_inline("`^True`0");
			$evilalign = get_module_setting("evilalign");
			$goodalign = get_module_setting("goodalign");
			$chaosalign = get_module_setting("chaosalign");
			$lawalign = get_module_setting("lawfulalign");
			$useralign = get_align($args['acctid']);
			$userdemeanor = get_demeanor($args['acctid']);
			if ($userdemeanor >= $lawalign){
				$de = $lawful;
			} else if ($userdemeanor <= $chaosalign){
				$de = $chaos;
			} else {
				$de = $neutral;
			}
			if ($useralign >= $goodalign) {
				$al=$good;
			} elseif ($useralign <= $evilalign) {
				$al=$evil;
			} else {
				$al=$neutral;
			}
			if ($de==$neutral && $al==$neutral) {
				$de=$true;
			}
			output("`^Alignment: %s %s`n",$de,$al);

?>