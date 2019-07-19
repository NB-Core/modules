<?php
// mod for quick fights in the wood
//note that this is for Version Dragonprime 1.1.1
// you *cannot* use it for lower version.

function forestmod_new_getmoduleinfo(){
	$info = array(
		"name"=>"Forst Modification inspired by XChrisX for 1.1.1 DP Version",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Forest",
		"download"=>"http://lotgd-downloads.com",
		"settings"=>array(
			"suicide"=>"Let people sucicide till the end?,bool|0",
			"suicidedk"=>"Min DKs for that?,int|10",
			),

	);
	return $info;
}

function forestmod_new_install(){
//	module_addhook("forest-header");
	module_addhook("forest");
	return true;
}

function forestmod_new_uninstall(){
	return true;
}

function forestmod_new_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "forest":
		    //extracted from healer.php
		    $loglev = log($session['user']['level']);
			$cost = ($loglev * ($session['user']['maxhitpoints']-$session['user']['hitpoints'])) + ($loglev*10);
		    $cost = round($cost,0);
			$result=modulehook("healmultiply",array("alterpct"=>1.0));
            $cost*=$result['alterpct']+0.05;
            $cost=round($cost,0);
		    if (($session['user']['gold'] >= $cost && $session['user']['maxhitpoints']-$session['user']['hitpoints']>0) || $session['user']['turns']>0) {
			 	addnav("Quick Fight");
		     	if ($session['user']['gold'] >= $cost && $session['user']['maxhitpoints']-$session['user']['hitpoints']>0){
					addnav(array("Complete Healing (%s gold)",$cost),"runmodule.php?module=forestmod_new&op=heal");
				}
				if ($session['user']['turns']>0) {
					if ($session['user']['level']>1)
					addnav("Slumber (till the end)","forest.php?op=search&auto=full&type=slum");
					addnav("Seek out (till the end)","forest.php?op=search&auto=full");
					addnav("Thrillseeking (till the end)","forest.php?op=search&auto=full&type=thrill");
					//uncomment the next lines to let players seach suicidally till the end
					if (getsetting("suicide", 0)) {
						if (getsetting("suicidedk", 10) <= $session['user']['dragonkills']) {
							addnav("Suicide (till the end)","forest.php?op=search&auto=full&type=suicide");
						}
					}
								// add buttons 
					if ($_COOKIE['template']=='Mobile.htm') {
					rawoutput("<div class='col-xs-12 col-sm8 btn-group mobile hidden-md hidden-lg ' style='margin: 5px 0 5px 0';>
								  <button id='js_slum'   type='button' class='col-xs-4 btn btn-default'>Slumber</button>
								  <button id='js_seek'   type='button' class='col-xs-4 btn btn-default'>Seek out</button>
								  <button id='js_thrill' type='button' class='col-xs-4 btn btn-default'>Thrills</button>
								</div>");
					addnav("","forest.php?op=search&auto=full&type=slum");
					addnav("","forest.php?op=search&auto=full");
					addnav("","forest.php?op=search&auto=full&type=thrill");								
					rawoutput("<script type='text/javascript' charset='UTF-8'>
							$(function() {
								$('#js_slum').click(function() {
									window.location = 'forest.php?op=search&auto=full&type=slum'
								});
								$('#js_seek').click(function() {
									window.location = 'forest.php?op=search&auto=full'
								});
								$('#js_thrill').click(function() {
									window.location = 'forest.php?op=search&auto=full&type=thrill'
								});
							});
							</script>");
					}
						break;
				}
			 }
	}
	return $args;
}

function forestmod_new_run() {
 	global $session;
 	$opt=httpget('op');
 	switch($opt) {
	    case "heal":	    //autohealing
	    //tynan is interfering
	    $loglev = log($session['user']['level']);
		$cost = ($loglev * ($session['user']['maxhitpoints']-$session['user']['hitpoints'])) + ($loglev*10);
	    $cost = round($cost,0);
	    $maxhit=$session['user']['maxhitpoints'] + round(get_module_pref("hitpoints","tynan"),0);
	    $session['user']['hitpoints'] =$maxhit;
	    $result=modulehook("healmultiply",array("alterpct"=>1.0));
	    $cost*=$result['alterpct']+0.05;
        $cost=round($cost,0);	    
	    $session['user']['gold'] -=$cost;
	    page_header("Marco, the flying healer");
	    output("`^`c`bMarco, the flying healer`b`c");
		if ($session['user']['maxhitpoints']-$session['user']['hitpoints']==0) {
			// nothing to heal
			output("`n`\$Sorry, you are already healed... my services are not needed.");
			require_once("lib/forest.php");
			forest(true);
			break;
		}
		if (is_module_active("addimages")){
				if (get_module_pref("user_addimages","addimages") == 1) {
					$i=e_rand(1,2);
					output_notl("`c<img src=\"modules/forestmod/Healer$i.gif\" alt='healer'>`c<br>\n",true);
				}
		}	
	    output("`n`nNow you're fully healed, my %s.",translate_inline($session['user']['sex']?"Heroine":"Hero"));
	    require_once("lib/forest.php");
		forest(true);
	    break;
	}
	output_notl("`0");
	page_footer();
}

?>
