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
require_once("lib/nltoappon.php");

function creationaddon_indexdisplay_getmoduleinfo(){
	$info = array(
		"name"=>"Creation Addon Index Display",
        "version"=>"1.0",
        "author"=>"Oliver Brendel, based on Creation Addon by Billie Kennedy",
        "category"=>"Administrative",
        "download"=>"",
        "allowanonymous"=>true,
      	);
	return $info;
}

function creationaddon_indexdisplay_install(){
	module_addhook("index");
	return true;
}

function creationaddon_indexdisplay_uninstall(){
	return true;
}

function creationaddon_indexdisplay_dohook($hookname,$args){

	global $session;

	$terms=httppost('terms');
	$privacy=httppost('privacy');

	switch($hookname){

		case "index":

			if(get_module_setting("requireprivacy",'creationaddon')){

				addnav("Legal");
				addnav("Privacy Policy","runmodule.php?module=creationaddon_indexdisplay&op=privacy&index=1");
			}

			if(get_module_setting("requireterms",'creationaddon')){

				addnav("Legal");
				addnav("Terms and Agreements","runmodule.php?module=creationaddon_indexdisplay&op=terms&index=1");
			}

        break;

        }
        return $args;
}

function creationaddon_indexdisplay_run(){
	global $session;

	$op=httpget("op");
	
	$index=(int)httpget('index');
	
	if ($index) {
		switch ($op) {
			case "terms":
				page_header("Terms and Agreement");
				output_notl("`n%s`0`n`n",nltoappon(stripslashes(sanitize_html(get_module_setting("terms",'creationaddon')))),true);
			break;

			case "privacy":
				page_header("Privacy Policy");
				output_notl("`n%s`0`n`n",nltoappon(stripslashes((get_module_setting("privacy",'creationaddon')))),true);
			break;
		}
		addnav("Back to the index page","index.php");
		page_footer();
	} else {
		switch($op){
			case "terms":
				popup_header("Terms and Agreements");
				output_notl("`n%s`0`n`n",nltoappon(stripslashes(get_module_setting("terms",'creationaddon'))),true);
			break;

			case "privacy":
				popup_header("Privacy Policy");
				output_notl("`n%s`0`n`n",nltoappon(stripslashes(get_module_setting("privacy",'creationaddon'))),true);
			break;
		}

		popup_footer();
	}

}

?>
