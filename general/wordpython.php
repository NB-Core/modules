<?php
/*needed:
Settings: fee, maxplayers,minplayers,gamename
operations(op): view,end,resume and a default with the starting screen
hooks: add to parlorgames and push the array with gameinfos there.
	structure and fields like this:
		$merge=array("module"=>"wordpython","name"=>translate_inline("Word Python - The friendly storytelling"));
		array_push($args,$merge);
fields:
record your game in the gamedatafield and use the functions in the playergames/func.php to access it. use this file as an example
	
*/

function wordpython_getmoduleinfo(){
$info = array(
	"name"=>"Word Python",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"category"=>"Games",
	"download"=>"http://lotgd-downloads.com",
	"settings"=> array (
		"fee"=>"Fee for this game?,int|50",
		"maxplayers"=>"How many players are allowed for this game? (Server performance),int|10",
		"minplayers"=>"How many players are necessary for this game?,int|2",
		"gamename"=>"How is this game called in the parlor?,text|Word Python",
		"length"=>"Max length of a sentence,int|200",
		),
	"requires"=>array(
		"playergames"=>"1.0|`2Oliver Brendel",
		), 
	);
	return $info;
}

function wordpython_install(){
	module_addhook("parlorgames");
	return true;
}

function wordpython_uninstall(){
	return true;
}

function wordpython_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		case "parlorgames":
			$merge=array("module"=>"wordpython","name"=>translate_inline("Word Python - The friendly storytelling"));
			array_push($args,$merge);
			break;
	}
	return $args;
}

function wordpython_run(){
	global $session;
	page_header("Word Python - The friendly storytelling");
	villagenav();
	require_once("./modules/playergames/func.php");
	playernav();
	$number=httpget('number');
	$length=get_module_setting("length");
	$namegame=getgamename($number);
	$op=httpget('op');
	switch($op) {
		case "view":
			output("`@This is the python '%s`@' until now:",$namegame);
			output_notl("`n`n");
			$array=getdata($number);
			if (!$array) $array=array();
			foreach($array as $val) {
				output_notl($val."`n");
			}
			break;			
		case "end":
			$array=getdata($number);
			if (!$array) $array=array();
			$datanow=$session['user']['name']."`& ".translate_inline("has ended the python");
			array_push($array,$datanow);
			setdata($number,$array);
			endgame($number);
			output("Game has ended");
			break;
		case "submit":
			$array=getdata($number);
			if (!$array) $array=array();
			$datanow=$session['user']['name']."`&: ".stripslashes(httppost('sentence'));
			array_push($array,$datanow);
			setdata($number,$array);
			output("Sentence submitted!");
			nextturn($number);
			playergames_setlastactive($number);
			break;
		case "resume":
			addnav("End this python","runmodule.php?module=wordpython&op=end&number=$number");
			output("`@This is the python '%s`@' until now:",$namegame);
			output_notl("`n`n");
			$array=getdata($number);
			if (!$array) $array=array();
			foreach($array as $val) {
				output_notl($val."`n");
			}
			output_notl("`n");
			output("`n`nContinue the Story with a sentence (%s chars max):",$length);
			output_notl("`n`n");
			$link="runmodule.php?module=wordpython&op=submit&number=$number";
			userpost($link,$length);
			break;
		default:
			
			output("`@Welcome to the new game, it is your turn as patron.");
			output("`n`nStart the Story with a sentence (%s chars max):",$length);
			output_notl("`n`n");
			$link="runmodule.php?module=wordpython&op=submit&number=$number";
			playergames_setlastactive($number);
			userpost($link,$length);
			break;
	}
	
	page_footer();

}

function userpost($link,$length) {
	rawoutput("<form action='$link' method='POST'>");
	addnav("",$link);
	rawoutput("<input name='sentence' maxlength='$length'>");
	$submit = translate_inline("Submit");
	rawoutput("<input type='submit' class='button' value='$submit'></form>");
}

?>
