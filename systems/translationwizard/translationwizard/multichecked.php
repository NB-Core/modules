<?php
declare(strict_types=1);
$alrighty=true;
foreach($transintext as $key=>$trans) {
        if ($transouttext[$key] != '') {
                $intext = $trans; //this comes in from the textarea and mustn't be decoded
                $outtext = $transouttext[$key];
                if ($nametext[$key]) {
                        $namespace=$nametext[$key];
                }
                $login = $session['user']['login'];
                if ($translatedtid[$key]) {
                        $sql = "UPDATE " . db_prefix("translations") . " SET outtext='$outtext',author='$login',version='$logd_version' WHERE tid={$translatedtid[$key]};";
                        $result1 = db_query($sql);
                } else {
                        $result1 = WizardService::createTranslation(
                                $languageschema,
                                $namespace,
                                $intext,
                                $outtext,
                                $login,
                                $logd_version
                        );
                }
                $result2 = WizardService::deleteUntranslated($languageschema,$namespace,$intext);
                invalidatedatacache("translations-".$namespace."-".$languageschema);
                if (!$result1 || !$result2) {
                        $alrighty=false;
                }
        }
}
if (!$alrighty) {
        $error=4;
} else {
        $error=5;
}
if ($redirectonline) {
        redirect("runmodule.php?module=translationwizard&op=list&ns=".$namespace."&error=".$error);
}
?>
