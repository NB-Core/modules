<?php
function mysterygems_getmoduleinfo()
{
	require("modules/mysterygems/getmoduleinfo.php");
	return $info;
}
function mysterygems_install()
{
	require_once("modules/mysterygems/install.php");
	return true;
}
function mysterygems_uninstall(){return true;}
function mysterygems_dohook($hookname, $args)
{
	global $session;
	require_once("modules/mysterygems/dohook/$hookname.php");
	return $args;
}
function mysterygems_run()
{
	global $playermount, $session;
	page_header("Gem's Eternal Mysteries");
	$op		= httpget('op');
	$greet	= 0;
	$times	= get_module_setting('times');
	$used		= get_module_pref('used');
	$ul		= $session['user']['level'];
	$umhp		= $session['user']['maxhitpoints'];
	$uexp		= $session['user']['experience'];
	$ugo		= $session['user']['gold'];
	$uge		= $session['user']['gems'];
	$udp		= $session['user']['deathpower'];
	$uhp		= $session['user']['hitpoints'];
	$utu		= $session['user']['turns'];
	if ($op <> 'enter') require_once("modules/mysterygems/run/case_not_enter.php");
	if (get_module_setting('levelmultiply')) require_once("modules/mysterygems/run/level_multiply.php");
	else require_once("modules/mysterygems/run/level_no_multiply.php");
	require_once("modules/mysterygems/run/case_$op.php");
	page_footer();
}
?>