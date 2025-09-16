<?php
	if (is_int($args['loc'])){
		$args['handled'] = 1;
		if ($args['count'] == 1) {
			output("`&There is `^1`& person sleeping in their Dwelling whom you might find interesting.`0`n");
		}else{
		    output("`&There are `^%s`& people sleeping in their Dwellings whom you might find interesting.`0`n", $args['count']);
		}
	}
?>