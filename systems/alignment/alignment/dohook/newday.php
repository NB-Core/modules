<?php
			$id = $session['user']['hashorse'];
			if ($id){
				$al = get_module_objpref("mounts",$id,"al");
				if ($al != ""){
					require_once("modules/alignment/func.php");
					align($al);
				}
				$de = get_module_objpref("mounts",$id,"de");
				if ($de != ""){
					require_once("modules/alignment/func.php");
					demeanor($de);
				}
			}
			if (get_module_setting("reset")){
				$max_num = get_module_setting("max-num");
				$min_num = get_module_setting("min-num");
				require_once("modules/alignment/func.php");
				$align = get_align();
				$demeanor = get_demeanor();
				if ($align > $max_num){
					set_align($max_num);
				}elseif ($align < $min_num){
					set_align($min_num);
				}
				if ($demeanor > $max_num){
					set_demeanor($max_num);
				}elseif ($demeanor < $min_num){
					set_demeanor($min_num);
				}
				if ($demeanor == "none") set_demeanor(round(($max_num + $min_num)/2));
				
			}
?>