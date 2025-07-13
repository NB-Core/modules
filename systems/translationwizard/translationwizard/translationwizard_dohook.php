<?php
declare(strict_types=1);
switch ($hookname)
{
case "superuser":
	if ($session['user']['superuser'] & SU_IS_TRANSLATOR) {
		if (in_array($session['user']['acctid'],array(53991,49152,11064,79959,80761,56609,16498,20127,62860,66990))) break; //safety
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
			
			$module = httppost("module");
			if (is_array($module)){
				$modules = $module;
				}else{
				if ($module) $modules = array($module);
					else $modules = array();
			}
			reset($modules);
			foreach($modules as $module) {
				$content=TranslationWizard::scanFile("modules/$module.php");
				TranslationWizard::insertFile($content,$languageschema);
			}
		}
	}
	if (get_module_setting("translationdelete")) {
		if (httpget('op')=="uninstall") {
			$get=rawurlencode(serialize(httpallget()));
			require_once ("./modules/translationwizard/deleteuninstalled.php");
		} elseif (httpget('op')=="mass" && httppost("uninstall")) {
			$get=rawurlencode(serialize(httpallget()));
			$post=rawurlencode(serialize(httpallpost()));
			require_once ("./modules/translationwizard/deleteuninstalled.php");
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

?>
