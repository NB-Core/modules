<?php
declare(strict_types=1);
$intext = httppost('intext');
$outtext = httppost('outtext');
$namespace = httppost('namespace');
$language = httppost('language');
if ($outtext !== '') {
        $login = $session['user']['login'];
        $success = WizardService::saveTranslation(
                $language,
                $namespace,
                $intext,
                $outtext,
                $login,
                $logd_version
        );
        $error = $success ? 5 : 4;
}
redirect("runmodule.php?module=translationwizard&error=".$error); //just redirecting so you go back to the previous page after the save
?>
