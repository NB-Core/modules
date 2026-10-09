<?php


function friendlist_faq_getmoduleinfo(){
	$info = array(
		"name"=>"FAQ for Friendlist",
		"version"=>"1.0",
		"author"=>"`2 Oliver Brendel, based on FAQ Central Server",
		"category"=>"Mail",
		"download"=>"",
		"override_forced_nav"=>true,
	);
	return $info;
}

function friendlist_faq_install(){
	module_addhook("faq-toc");			//show in the FAQ
	return true;
}

function friendlist_faq_uninstall(){
	return true;
}

function friendlist_faq_dohook($hookname,$args){
	global $session;
	switch($hookname){
	case "faq-toc":
			$t = translate_inline("`@Frequently Asked Questions on Friend Lists`0");
			output_notl("&#149;<a href='runmodule.php?module=friendlist_faq&op=faq'>$t</a><br/>", true);
			addnav("","runmodule.php?module=friendlist_faq&op=faq");
		break;
	}
	return $args;
}

function friendlist_faq_run(){
	global $session;
	$op = httpget("op");
	if ($op == "faq") {
		friendlist_faq();
	} 
}

function friendlist_faq() {
	popup_header("Frequently Asked Questions on Friend Lists");
	$c = translate_inline("Return to the Contents");
	output_notl("`#<strong><center><a href='petition.php?op=faq'>$c</a></center></strong>`0",true);
	addnav("","petition.php?op=faq");
	rawoutput("<hr>");
	output("`c`&`bQuestions about Friend Lists`b`c`n");
	output("`^1. Where is it?`n");
	output("`@Just click the link at the top of the mail page.`n`n");
	output("`^2. What is it for?`n");
	output("`@You can send requests to add someone to both of your lists.`nIf the other user accepts, you will be able to see their status, location, and whether or not they are logged in.`n`n");
	output("`^3. Anything else?`n");
	output("`@You can ignore players that harass you, to prevent them from sending you mail.`nYou can ignore Admin, however their mails will still come through.`n`n");
	if (get_module_setting('allowStat')) {
		output("`^4. Sure that's it?`n");
		output("`@Oh, I forgot!;) You can turn on a preference to see how many of your friends are online.");
	}
	rawoutput("<hr>");
	output_notl("`#<strong><center><a href='petition.php?op=faq'>$c</a></center></strong>`0",true);
	popup_footer();
}
?>
