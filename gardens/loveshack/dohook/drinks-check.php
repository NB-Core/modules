<?php
		if (get_module_pref('inShack')) {
			$args['allowdrink'] = get_module_objpref('drinks',$args['drinkid'],'drinkLove');
		} else {
			$args['allowdrink'] = get_module_objpref('drinks',$args['drinkid'],'loveOnly');
		}

?>