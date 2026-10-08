<?php
/***************************************************************************/
/* Name: Creation Addon                                                    */
/* ver 2.2                                                                 */
/* Billie Kennedy => dannic06@gmail.com                                    */
/*                                                                         */
/* Uses some of the code from createfiltertitle by Dying                   */
/* ToDo:                                                                   */
/*   Graphic verification for bot killing.                                 */
/***************************************************************************/

/***************************************************************************/
/* Some help and code borrowed from the following people:                  */
/*   Chris                                                                 */
/*   dying                                                                 */
/***************************************************************************/

//define("ALLOW_ANONYMOUS",true);
//define("OVERRIDE_FORCED_NAV",true);

require_once("lib/http.php");
require_once("lib/nltoappon.php");

function creationaddon_getmoduleinfo(){
	$info = array(
			"name"=>"Creation Addon",
			"version"=>"2.22",
			"author"=>"Billie Kennedy",
			"category"=>"Administrative",
			"download"=>"http://www.orpgs.com/modules.php?name=Downloads&d_op=getit&lid=20",
			"vertxtloc"=>"http://www.orpgs.com/downloads",
			"allowanonymous"=>true,
			"settings"=>array(
				"Create Addon,title",
				"creationmsg"=>"This is the message to display to new users.,textarea|Some Message",
				"requireage"=> "Do you require the player to be a minimum age?,bool|1",
				"age"=>"What age to players need to be to play?,int|13",
				"requireterms"=>"Do you require the player to read the terms?,bool|1",
				"terms"=>"These are your Terms.,textarea|Some Message",
				"requireprivacy"=>"Show Privacy Policy?,bool|1",
				"privacy"=>"This is your Privacy Statement.,textarea|Some Message",
				"askbday"=>"Ask the player to input thier Birth Day?,bool|0",
				"requirebday"=>"Do you require the birthday?,bool|0",
				"bdaymsg"=>"What is the message for entering the birthday?,text|Please enter your Birthday:`n",
				"requireyear"=>"Do you require the year?,bool|0",
				"chkbday"=>"Do an Age check?,bool|0",
				"showfooter"=>"Show your terms/agreements and privacy statment in every footer?,bool|0",
				),
			"prefs"=>array(
					"Creation preferences,title",
					"ageverified"=>"Players age has been verified?,bool|0",
					"termsverified"=>"Player has read the terms.,bool|0",
					"privacyverified"=>"Player was shown the Privacy Statement.,bool|0",
					"privacyverifieddate"=>"Player was shown the Privacy Statement on...,date",
					"month"=>"Birth Month,int|0",
					"day"=>"Birth Day,int|0",
					"year"=>"Birth Year,int|0",
				      ),
			);
	return $info;
}

function creationaddon_install(){

	if (db_table_exists(db_prefix("badnames"))) {
		debug("Bad Names table already exists");
	}else{
		debug("Creating Bad Names table");
		$sqls = array("CREATE TABLE " . db_prefix("badnames") . " (
			bad_id TINYINT NOT NULL AUTO_INCREMENT ,
			       badname VARCHAR( 50 ) NOT NULL ,
			       PRIMARY KEY ( bad_id ))"
			     );
		foreach($sqls as $sql) {
			db_query($sql);
		}
	}
	module_addhook("create-form");
	module_addhook("check-create");
	module_addhook("process-create");
	module_addhook("village");
	module_addhook("shades");
	module_addhook("everyfooter");

	return true;
}

function creationaddon_uninstall(){

	return true;
}

function creationaddon_dohook($hookname,$args){

	global $session;

	$age=httppost('age');
	$month=httppost('month');
	$day=httppost('day');
	$year=httppost('year');
	$terms=httppost('terms');
	$privacy=httppost('privacy');
	$msg = '';

	switch($hookname){

		case "check-create":
			if (!isset($args['blockaccount'])) $args['blockaccount']=0;
			$msg = '';
			if (!isset($args['msg'])) $args['msg'] = '';
			$blockaccount = $args['blockaccount'];

			// Lets see if they meet the age requirements.

			if(get_module_setting("requireage")==1 && $age || get_module_setting('requireage')==0){

			}else{
				$msg=translate_inline("You must be at least ");
				$msg.=get_module_setting("age");
				$msg.=translate_inline(" years old to play.`n");
				$args['msg'] .=$msg;
				$blockaccount=true;
			}

			// Did they check the box for terms?
			if($terms || get_module_setting('requireterms')==0){

			}else{
				$msg=translate_inline("You must read the terms.`n");
				$args['msg'] .=$msg;
				$blockaccount=true;
			}

			// Did they check the box for the Privacy Policy?
			if($privacy || get_module_setting('requireprivacy')==0){

			}else{
				$msg=translate_inline("You must read the Privacy Policy.`n");
				$args['msg'] .=$msg;
				$blockaccount=true;
			}

			if(get_module_setting("chkbday")){

				// Lets do a small check to see if they are actually over the age according to their birthday.
				$thisday = date("j");
				$thismonth = date("n");
				$thisyear = date("Y");

				// ok.. lets check to see what month they were born.  if it was after this month then subtract a year.
				if( $thismonth-$month < 0) --$thisyear;

				// they were born the same month as this month.  Lets check the day to see if they have had it yet.
				if( $thisday < $day && $thismonth-$month ==0) --$thisyear;

				// Lets compare the math in the years.
				if(get_module_setting("requireage") && $thisyear-$year >= get_module_setting("age")){

				}else{
					$msg=translate_inline("Sorry but you do not meet the minimum age requirements.");
					$args['msg'] .=$msg;
					$blockaccount=true;
				}
			}

			$args['blockaccount']= $blockaccount;

			break;

		case "create-form":

			output("`n%s`0`n`n",nltoappon(stripslashes(get_module_setting("creationmsg"))));

			// Make them check a box requiring a minimum age.
			if(get_module_setting("requireage")){
				rawoutput("<input type=\"checkbox\" name=\"age\" />&nbsp&nbsp");
				output("I am at or over the age of %s.`n`n",get_module_setting("age"));
			}

			// Make them check a box for terms.  Give them a link.
			if(get_module_setting("requireterms")){
				rawoutput("<input type='checkbox' name='terms' />&nbsp&nbsp");
				$terms = translate_inline("Terms and Agreements");
				output("I have read the ");
				rawoutput("<a href='runmodule.php?module=creationaddon&op=terms' target='_blank' onClick=\"".popup("runmodule.php?module=creationaddon&op=terms","500x300")."; return false;\" 'class='motd'>$terms</a>.<br><br>");

			}

			// Make them check a box for Privacy Statement.  Give them a link.
			if(get_module_setting("requireprivacy")){
				rawoutput("<input type='checkbox' name='privacy' />&nbsp&nbsp");
				$privacy = translate_inline("Privacy Policy");
				output("I have read the ");
				rawoutput("<a href='runmodule.php?module=creationaddon&op=privacy' target='_blank' onClick=\"".popup("runmodule.php?module=creationaddon&op=privacy","500x300")."; return false;\" 'class='motd'>$privacy</a>.<br><br>");

			}

			// Don't require birthday.  Just do it.
			if(get_module_setting("askbday")){
				output("%s`n",get_module_setting("bdaymsg"));
				output("Month");
				rawoutput("<select name='month'>");

				for ($i=0;$i<13;$i++){
					rawoutput("<option value='$i'>$i</option>");
				}
				rawoutput("</select>");
				output("Day");
				rawoutput("<select name='day'>");
				for ($i=0;$i<32;$i++){
					rawoutput("<option value='$i'>$i</option>");
				}
				rawoutput("</select>");
				if(get_module_setting("requireyear")){
					output("Year");
					rawoutput("<select name='year'>");
					$thisyear=(int)date("Y");
					for ($x=$thisyear-100;$x<=$thisyear;$x++){
						rawoutput("<option value='$x'>$x</option>");
					}
					rawoutput("</select>");
					rawoutput("<br><br>");
				}else{
					rawoutput("<br><br>");
				}
			}
			break;
		case "village":
		case "shades":

			if(get_module_setting("requireprivacy")){
				$privacy = translate_inline("Privacy Policy");
				$privacyfooter= "<br><a href='runmodule.php?module=creationaddon&op=privacy' target='_blank' onClick=\"".popup("runmodule.php?module=creationaddon&op=privacy","500x300")."; return false;\" 'class='motd'>$privacy</a>";
				addnav("","runmodule.php?module=creationaddon&op=privacy");
				addnav("Info");
				addnav("Privacy Policy","runmodule.php?module=creationaddon&op=privacy&village=1");
				if (!isset($args['source'])) {

					$args['source'] = array();

				} elseif (!is_array($args['source'])) {

					$args['source'] = array($args['source']);
				}
				array_push($args['source'], $privacyfooter);
			}

			if(get_module_setting("requireterms")){

				addnav("Info");
				addnav("Terms and Agreements","runmodule.php?module=creationaddon&op=terms&village=1");
				$termsfooter="<br><a href='runmodule.php?module=creationaddon&op=terms' target='_blank' onClick=\"".popup("runmodule.php?module=creationaddon&op=terms","500x300")."; return false;\" 'class='motd'>$terms</a>";
				addnav("","runmodule.php?module=creationaddon&op=terms");
				if (!isset($args['source'])) {

					$args['source'] = array();

				} elseif (!is_array($args['source'])) {

					$args['source'] = array($args['source']);
				}
				array_push($args['source'], $termsfooter);
			}

			break;


		case "everyfooter":

			if(get_module_setting("requireprivacy")){
				$privacy = translate_inline("Privacy Policy");
				$privacyfooter= "<br><a href='runmodule.php?module=creationaddon&op=privacy' target='_blank' onClick=\"".popup("runmodule.php?module=creationaddon&op=privacy","500x300")."; return false;\" 'class='motd'>$privacy</a>";
				addnav("","runmodule.php?module=creationaddon&op=privacy");
				if (!isset($args['source'])) {

					$args['source'] = array();

				} elseif (!is_array($args['source'])) {

					$args['source'] = array($args['source']);
				}
				array_push($args['source'], $privacyfooter);
				if (!get_module_pref('privacyverified') && isset($session['user']['loggedin']) && $session['user']['loggedin']==true) {
					if (httpget('module')=='creationaddon' && (httpget('op')=='accept_privacy' || httpget('op')=='yes_accept_privacy')) {
						//we are on the page, don't redirect via everyfooter
					} else { 
						// we need to show it to them, they have not yet accepted it
						redirect("runmodule.php?module=creationaddon&op=accept_privacy&village=1");
					}
				}
			}

			if(get_module_setting("requireterms")){

				$terms = translate_inline("Terms and Agreements");
				$termsfooter="<br><a href='runmodule.php?module=creationaddon&op=terms' target='_blank' onClick=\"".popup("runmodule.php?module=creationaddon&op=terms","500x300")."; return false;\" 'class='motd'>$terms</a>";
				addnav("","runmodule.php?module=creationaddon&op=terms");
				if (!isset($args['source'])) {

					$args['source'] = array();

				} elseif (!is_array($args['source'])) {

					$args['source'] = array($args['source']);
				}
				array_push($args['source'], $termsfooter);
			}

			break;

		case "process-create":
			global $shortname;
			$sql = "SELECT acctid FROM " . db_prefix("accounts") . " WHERE login='$shortname'";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			$id=$row['acctid'];

			if(get_module_setting("requireterms")) set_module_pref('termsverified',1,'creationaddon',$id);
			if(get_module_setting("requireprivacy")) set_module_pref('privacyverified',1,'creationaddon',$id);
			if(get_module_setting("requireage")) set_module_pref('ageverified',1,'creationaddon',$id);
			if(get_module_setting("askbday")){
				set_module_pref('month',$month,'creationaddon',$id);
				set_module_pref('day',$day,'creationaddon',$id);
				if(get_module_setting("requireyear")) set_module_pref('year',$year,'creationaddon',$id);
			}

			break;

	}
	return $args;
}

function creationaddon_run(){
	global $session;

	$op=httpget("op");
	$terms = "Terms and Agreements";
	$privacy = "Privacy Policy";
	if (httpget('village')) {
		villagenav();
		switch ($op) {
			case "terms":
				page_header($terms);
				output("`n%s`0`n`n",nltoappon(stripslashes(sanitize_html(get_module_setting("terms")))),true);
				break;

			case "privacy":
				page_header($privacy);
				output("`n%s`0`n`n",nltoappon(stripslashes((get_module_setting("privacy")))),true);
				break;
			case "accept_privacy":
				page_header($privacy);
				output("`c`bPrivacy Rules updated - action required`b`c`n`n");
				output("`2Policies have changed and we need you to accept the general data privacy terms before you can play again.`n`n");
				output("`n%s`0`n`n",nltoappon(stripslashes((get_module_setting("privacy")))),true);
				addnav("Accept Agreement");
				addnav("Yes, I have read and accept the data privacy policies","runmodule.php?module=creationaddon&op=yes_accept_privacy&village=1");
				break;
			case "yes_accept_privacy":
				page_header($privacy);
				output("`!Thank you for acknowledging and accepting our privacy agreement.`n`nHave fun gaming!");
				set_module_pref('privacyverified',1);
				set_module_pref('privacyverifieddate',date("Y-m-d"));
				break;
		}
		page_footer();
	} else {
		define("NO_SAVE_USER",true);
		
		switch($op){
			case "terms":
				$title = translate_inline($terms);
				popup_header($title);
				output("`n%s`0`n`n",nltoappon(stripslashes(get_module_setting("terms"))),true);
				break;

			case "privacy":
				$title = translate_inline($privacy);
				popup_header($title);
				output("`n%s`0`n`n",nltoappon(stripslashes(get_module_setting("privacy"))),true);
				break;
		}

		popup_footer();
	}

}

?>
