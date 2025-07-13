<?php
declare(strict_types=1);
$intext = httppost('intext');
$outtext = httppost('outtext');
if ($outtext !== '') {
        $login = $session['user']['login'];
        $success = WizardService::saveTranslation(
                $languageschema,
                $namespace,
                $intext,
                $outtext,
                $login,
                $logd_version
        );
        $error = $success ? 5 : 4;
}
redirect("runmodule.php?".$from."&error=".$error); //just redirecting so you go back to the previous page after the save
?>
