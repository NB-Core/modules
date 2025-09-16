<?php
function align($val,$user=false){
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	increment_module_pref('alignment',$val,'alignment',$user);
}
function get_align($user=false){
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	$val = get_module_pref('alignment','alignment',$user);
	return $val;
}
function set_align($val,$user=false){
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	set_module_pref('alignment',$val,'alignment',$user);
}
function demeanor($val,$user=false){
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	increment_module_pref('demeanor',$val,'alignment',$user);
}
function get_demeanor($user=false){
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	$val = get_module_pref('demeanor','alignment',$user);
	return $val;
}
function set_demeanor($val,$user=false){
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	set_module_pref('demeanor',$val,'alignment',$user);
}

function is_evil($user=false) {
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	$evilalign = get_module_setting('evilalign','alignment');
	$alignment = get_module_pref('alignment','alignment',$user);
	return ($alignment<=$evilalign?true:false);
}

function is_good($user=false) {
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	$goodalign = get_module_setting('goodalign','alignment');
	$alignment = get_module_pref('alignment','alignment',$user);
	return ($alignment>=$goodalign?true:false);	
}

function is_chaotic($user=false) {
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	$chaosalign = get_module_setting('chaosalign','alignment');
	$demeanor = get_module_pref('demeanor','alignment',$user);
	return ($demeanor<=$chaosalign?true:false);
}

function is_lawful($user=false){
	global $session;
	if ($user === false) $user = $session['user']['acctid'];
	$lawfulalign = get_module_setting('lawfulalign','alignment');
	$demeanor = get_module_pref('demeanor','alignment',$user);
	return ($demeanor>=$lawfulalign?true:false);
}

function is_trueneutral($user=false) {
	return (is_demneutral($user) && is_alneutral($user));
}

function is_demneutral($user=false) {
	return (!is_lawful($user) && !is_chaotic($user));
}

function is_alneutral($user=false) {
	return (!is_good($user) && !is_evil($user));
}


?>