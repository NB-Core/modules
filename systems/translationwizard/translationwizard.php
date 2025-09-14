<?php
declare(strict_types=1);
/**
 * Directory layout notes
 * - `pages/` contains the runnable scripts for each wizard action. The main
 *   operations are `scan` (`pages/scanmodules.php`), `edit`
 *   (`pages/edit_single.php`), `pull` (`pages/central_pull.php`) and `push`
 *   (`pages/central_push.php`).
 * - `lib/` holds various helper functions used across the pages.
 * - `WizardService` defines the service layer that performs translation
 *   database operations and other utility logic for the wizard.
 */
//For versioninfos just take a look at /modules/translationwizard/versions.txt

// Okay, someone wants to use this outside of normal game flow.. no real harm
if(!defined('OVERRIDE_FORCED_NAV')) define("OVERRIDE_FORCED_NAV",true);
require_once __DIR__ . '/translationwizard/TranslationWizard.php';
require_once __DIR__ . '/translationwizard/lib/form_helpers.php';
require_once __DIR__ . '/translationwizard/WizardService.php';

/**
 * Return module metadata for the Translation Wizard.
 *
 * @return array Configuration for the module
 */
function translationwizard_getmoduleinfo(): array{
	//Slightly modified by JT Traub in the original untranslated.php
	$info = array(
	    "name"=>"Translation Wizard",
		"version"=>"1.47",
		"author"=>"`2Written by Oliver Brendel, `3based on the untranslated.php by Christian Rutsch`nFilescan by `qEdorian`n",
		"category"=>"Translations",
		"download"=>"http://lotgd-downloads.com",
		"settings"=>array(
			"General Preferences,title",
				"blocktrans"=>"Block the Untranslated Text in the grotto,bool|0",
				"query"=>"Use nested query (doesn't works with lower mysql servers),bool|0",
				"page"=>"How many results per page for fixing/checking,int|20",
			"Access Restrictions,title",
				"Restrictions are: search+edit the translations table + truncate untranslated,note",
				"restricted"=>"Has the wizard restrictions for some users?,bool|0",
			"Auto Scan + Cleanup,title",
				"This is only for skilled users! Its not finding everything yet,note",
				"and your untranslated gets filled quickly if you begin to use this at start,note",
				"but if you want to scan new modules automatically on install - here it is,note",
				"autoscan"=>"Scan automatically modules upon install and insert into untranslated,bool|0",
				"translationdelete"=>"Ask if translations should be deleted at uninstallation of a module,bool|0",
			"Central Translations,title",
				"blockcentral"=>"Block the Central Translations Section in the wizard,bool|0",
				"lookuppath"=>"URL to the central translations section,text|http://translations.nb-core.org",
				"Note: This is usually translation.nb-core.org,note",
			
		),
		"prefs"=>array(
		    "Translation Wizard - User prefs,title",
				"language"=>"Languages for the Wizard,enum,".getsetting("serverlanguages","en,English,fr,Français,dk,Danish,de,Deutsch,es,Español,it,Italian"),
				"Note: don't change this if you don't need to... it is set up in the Translation Wizard!,note",
				"allowed"=>"Does this user have unrestricted access to the wizard?,bool|0",
				"Note: This is only active if the restriction settings is 'true' in the module settings,note",
				"view"=>"Use advanced view (shows more),bool|0",
		),
		);
    return $info;
}

/**
 * Install the Translation Wizard module and create required tables.
 *
 * @return bool True on success
 */
function translationwizard_install(): bool{
	module_addhook("superuser");
	module_addhook("header-modules");
	if (is_module_active("translationwizard")) debug("Module Translationwizard updated");
	$wizard=array(
		'tid'=>array('name'=>'tid', 'type'=>'int(11) unsigned', 'extra'=>'auto_increment'),
		'language'=>array('name'=>'language', 'type'=>'varchar(10)'),
		'uri'=>array('name'=>'uri', 'type'=>'varchar(255)'),
		'intext'=>array('name'=>'intext', 'type'=>'text'),
		'outtext'=>array('name'=>'outtext', 'type'=>'text'),
		'author'=>array('name'=>'author', 'type'=>'varchar(50)'),
		'version'=>array('name'=>'version', 'type'=>'varchar(50)'),
		'key-PRIMARY' => array('name'=>'PRIMARY', 'type'=>'primary key', 'unique'=>'1', 'columns'=>'tid'),
		'key-one'=> array('name'=>'language', 'type'=>'key', 'unique'=>'0', 'columns'=>'language,uri'),
		'key-two'=> array('name'=>'uri', 'type'=>'key', 'unique'=>'0', 'columns'=>'uri'),
		);
	require_once("lib/tabledescriptor.php");
	synctable(db_prefix("temp_translations"), $wizard, true);
	return true;
}

/**
 * Remove temporary tables created by the Translation Wizard module.
 *
 * @return bool True on success
 */
function translationwizard_uninstall(): bool {
        debug ("Performing Uninstall on Translation Wizard. Thank you for using!`n`n");
        $result = true;
        if(db_table_exists(db_prefix("temp_translations"))){
                $query=db_query("DROP TABLE ".db_prefix("temp_translations"));
                $result = (bool)$query;
        }
        return $result;
}


/**
 * Hook dispatcher for the Translation Wizard.
 *
 * @param string $hookname Name of the hook
 * @param array  $args     Arguments passed by the hook system
 *
 * @return array Modified arguments
 */
function translationwizard_dohook(string $hookname, array $args): array{
        global $session;

        switch ($hookname) {
        case "superuser":
                if ($session['user']['superuser'] & SU_IS_TRANSLATOR) {
                        addnav("Actions");
                        addnav("Translation Wizard","runmodule.php?module=translationwizard&op=list");
                        if (get_module_setting("blocktrans")) blocknav("untranslated.php");
                }
                break;

        case "header-modules":

                if (get_module_setting("autoscan")) {
                        if (httpget('op')=="install") {
                                $languageschema=get_module_pref("language","translationwizard");
                                if (!$languageschema) break;
                                $content=TranslationWizard::scanFile("modules/".httpget('module').".php");
                                TranslationWizard::insertFile($content,$languageschema);
                        } elseif (httpget('op')=="mass" && httppost("install")) {
                                $languageschema=get_module_pref("language","translationwizard");
                                if (!$languageschema) break;

                                $modules = array_filter((array)httppost("module"));
                                foreach($modules as $module) {
                                        $content=TranslationWizard::scanFile("modules/$module.php");
                                        TranslationWizard::insertFile($content,$languageschema);
                                }
                        }
                }
                if (get_module_setting("translationdelete")) {
                        if (httpget('op')=="uninstall") {
                                $get=rawurlencode(serialize(httpallget()));
                                require_once __DIR__ . '/translationwizard/pages/deleteuninstalled.php';
                        } elseif (httpget('op')=="mass" && httppost("uninstall")) {
                                $get=rawurlencode(serialize(httpallget()));
                                $post=rawurlencode(serialize(httpallpost()));
                                require_once __DIR__ . '/translationwizard/pages/deleteuninstalled.php';
                        }
                }
                break;

                /*case "footer-modules":
                        output_notl("Get:");
                        debug(httpallget());
                break;
                */
        default:

                break;
        }

        return $args;
}

/**
 * Main execution dispatcher for the Translation Wizard module.
 * Handles request routing and page setup.
 *
 * @return void
 */
function translationwizard_run(): void{
	global $session,$logd_version,$coding;
	check_su_access(SU_IS_TRANSLATOR); //check again Superuser Access
        $op = basename(httpget('op'));
	page_header("Translation Wizard");
	//get some standards
	$languageschema=get_module_pref("language","translationwizard");
	//these lines grabbed the local scheme, in 1.1.0 there is a setting for it
	$coding=getsetting("charset", "ISO-8859-1");
	$viewsimple=get_module_pref("view","translationwizard");
	$mode = (string)httpget('mode');
	$namespace = (string)httppost('ns');
	$from = (string)httpget('from');
	$page = get_module_setting('page');
	if (httpget('ns')<>"" && $namespace=="") $namespace=httpget('ns'); //if there is no post then there is maybe something to get
	$trans = httppost("transtext");
	if (is_array($trans)) { //setting for any intexts you might receive
		$transintext = $trans;
	}else {
		if ($trans) $transintext = array($trans);
		else $transintext = array();
	}
	$trans = httppost("transtextout");
	if (is_array($trans)) { //setting for any outtexts you might receive
		$transouttext = $trans;
	}else {
		if ($trans) $transouttext = array($trans);
		else $transouttext = array();
	}
	//end of the header
        if ($op=="")  $op="default";
       if($op!='scanmodules') require(__DIR__ . '/translationwizard/lib/errorhandler.php');
        if ($op == 'randomsave') {
                $intext = httppost('intext');
                $outtext = httppost('outtext');
                $namespace = httppost('namespace');
                $language = httppost('language');
                if ($outtext !== '') {
                        $success = WizardService::saveTranslation(
                                $language,
                                $namespace,
                                $intext,
                                $outtext,
                                $session['user']['login'],
                                $logd_version
                        );
                        $error = $success ? 5 : 4;
                }
                redirect("runmodule.php?module=translationwizard&error=".$error);
        } elseif ($op == 'save_single') {
                $intext = httppost('intext');
                $outtext = httppost('outtext');
                $namespace = httppost('namespace');
                $language = httppost('language');
                if ($outtext !== '') {
                        $success = WizardService::saveTranslation(
                                $language,
                                $namespace,
                                $intext,
                                $outtext,
                                $session['user']['login'],
                                $logd_version
                        );
                        $error = $success ? 5 : 4;
                }
                redirect("runmodule.php?module=translationwizard&error=".$error);
        } elseif ($op == 'multichecked') {
                $namespace = httppost('namespace');
                $success = WizardService::saveBatchTranslations(
                        $languageschema,
                        $namespace,
                        WizardService::ensureArray($transintext),
                        WizardService::ensureArray($transouttext),
                        WizardService::ensureArray(httppost('nametext')),
                        WizardService::ensureArray(httppost('translatedtid')),
                        $session['user']['login'],
                        $logd_version
                );
                $error = $success ? 5 : 4;
                redirect("runmodule.php?module=translationwizard&op=list&ns=".$namespace."&error=".$error);
        } elseif ($op == 'copychecked') {
                $namespace = httppost('namespace');
                $success = WizardService::copyCheckedTranslations(
                        $languageschema,
                        $namespace,
                        WizardService::ensureArray($transintext),
                        $session['user']['login'],
                        $logd_version
                );
                $error = $success ? 5 : 4;
                redirect("runmodule.php?module=translationwizard&op=list&ns=".$namespace."&error=".$error);
        } elseif ($op == 'deleteempty') {
                WizardService::deleteEmpty($mode, (int)$page, $coding);
        } elseif ($op == 'switchview') {
                WizardService::toggleView((bool)$viewsimple, $from);
        } elseif ($op == 'insert_central') {
                WizardService::insertCentral($mode, $namespace, $languageschema);
       } else {
               $map = [
                       'list' => 'untranslated_list.php',
                       'fix'  => 'remove_translated.php',
                       'pull' => 'central_pull.php',
                       'push' => 'central_push.php',
               ];
               if (isset($map[$op])) {
                       $file = __DIR__ . '/translationwizard/pages/' . $map[$op];
               } else {
                       $file = __DIR__ . "/translationwizard/pages/{$op}.php";
               }
               if (file_exists($file)) {
                       require($file);
               } else {
                       output("Unknown operation: %s", $op);
               }
       }
	require_once("lib/superusernav.php");
	superusernav();
       require(__DIR__ . '/translationwizard/build_nav.php');
	page_footer();
}

?>
