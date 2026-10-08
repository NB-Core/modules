<?php
		if (get_module_pref('inShack')) {
			$args['title']="The Loveshack";
			$args['barkeep']=get_module_setting("bartendername");
			$args['return']="Sit back at the Bar";
			$args['returnlink']="runmodule.php?module=loveshack&op=loveshack&op2=bar";
			$args['demand']="Giggling on the floor, you yell for another drink";
			$args['toodrunk']=" and so ".get_module_setting("bartendername")." places one on the bar.. however, you are too drunk to pick it up, so ".get_module_setting("bartendername")." leaves it to rot..";
			$args['toomany']=get_module_setting("bartendername")." `3apologizes, \"`&You've cleaned the place out.`3\"";
			$array = array("title","barkeep","return","demand","toodrunk","toomany","drinksubs");
			$schemas=array();
			foreach ($array as $val) {
				$schemas[$val]="module-loveshack";
			}
			$args['schemas']=$schemas;
			$args['drinksubs']=array(
				"/^he/"=>"^".(get_module_setting('genderbartender')?translate_inline("she"):translate_inline("he")),
				"/ he/"=>" ".(get_module_setting('genderbartender')?translate_inline("she"):translate_inline("he")),
				"/^his/"=>"^".(get_module_setting('genderbartender')?translate_inline("her"):translate_inline("his")),
				"/ his/"=>" ".(get_module_setting('genderbartender')?translate_inline("her"):translate_inline("his")),
				"/^him/"=>"^".(get_module_setting('genderbartender')?translate_inline("her"):translate_inline("him")),
				"/ him/"=>" ".(get_module_setting('genderbartender')?translate_inline("her"):translate_inline("him")),
				"/{barkeep}/"=>get_module_setting("bartendername"),
				"/Violet/"=>translate_inline("a stranger"),
				"/Seth/"=>translate_inline("a stranger"),
			);
		}
?>