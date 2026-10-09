<?php
if (get_module_pref('used') < get_module_setting('times')) 
{	
	if (!get_module_setting('move') && $session['user']['location'] == get_module_setting('mgloc'))
	{
		tlschema($args['schemas']['marketnav']);
		addnav($args['marketnav']);
		tlschema();
		addnav("Gem's Eternal Mysteries", "runmodule.php?module=mysterygems&op=enter");
	}
	elseif (get_module_setting('move') && get_module_setting('runoncemove') 
	 && get_module_setting('place') == $session['user']['location'])
	{
		tlschema($args['schemas']['marketnav']);
		addnav($args['marketnav']);
		tlschema();
		addnav("Gem's Eternal Mysteries", "runmodule.php?module=mysterygems&op=enter");
	}				
	elseif (get_module_setting('move') && !get_module_setting('runoncemove') 
	 && get_module_pref('userplace') == $session['user']['location'])
	{
		tlschema($args['schemas']['marketnav']);
		addnav($args['marketnav']);
		tlschema();
		addnav("Gem's Eternal Mysteries", "runmodule.php?module=mysterygems&op=enter");
	}
}
if (!get_module_setting('buymount') && get_module_pref('lostmount')) blocknav('stables.php');
?>