<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

//you can use this function if you want to search for a flirtpartner
function loveshack_fform($w,$whereto='runmodule.php?module=loveshack&op=loveshack&op2=flirt') {
        global $session;
        $whom = httppost("whom");
        $sameGenderAllowed = get_module_setting('sg','loveshack');
        rawoutput("<form action='$whereto&flirtitem=$w&stage=0' method='POST'>");
        addnav("","$whereto&flirtitem=$w&stage=0");
        if ($whom!="") {
                $string="%";
                for ($x=0;$x<strlen($whom);$x++){
                        $string .= substr($whom,$x,1)."%";
                }

                $conn = Database::getDoctrineConnection();
                $accountsTable = Database::prefix('accounts');
                $parameters = [
                        'acctid' => (int) $session['user']['acctid'],
                ];
                $types = [
                        'acctid' => ParameterType::INTEGER,
                ];

                if ($sameGenderAllowed==1) {
                        $sql = "SELECT login,sex,name,charm,acctid FROM {$accountsTable} WHERE login LIKE :loginPattern AND acctid <> :acctid ORDER BY level,login";
                        $parameters['loginPattern'] = '%'.$whom.'%';
                        $types['loginPattern'] = ParameterType::STRING;
                } else {
                        $sql = "SELECT login,sex,name,charm,acctid FROM {$accountsTable} WHERE name LIKE :namePattern AND acctid <> :acctid AND sex <> :sex ORDER BY level,login";
                        $parameters['namePattern'] = $string;
                        $parameters['sex'] = (int) $session['user']['sex'];
                        $types['namePattern'] = ParameterType::STRING;
                        $types['sex'] = ParameterType::INTEGER;
                }

                $result = $conn->executeQuery($sql, $parameters, $types);
                $rows = $result->fetchAllAssociative();
                $charmlevel=get_module_setting('charmleveldifference','loveshack');
                $charmlevelup=get_module_setting('charmleveldifferenceup','loveshack');
                if (!empty($rows)) {
                        output("`@These users were found `^(click on a name`@):`n");
                        rawoutput("<table cellpadding='3' cellspacing='0' border='0'>");
                        rawoutput("<tr class='trhead'><td>".translate_inline("Name")."</td></tr>");
                        foreach ($rows as $i => $row){
                                if ($row['charm']>($session['user']['charm']+$row['charm']*$charmlevel/100) && get_module_setting('charmlevelactivate','loveshack')) {
                                rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td><a href='$whereto&flirtitem=one&name=".urlencode($row['name'])."&stage=1&target=".$row['acctid']."'>");
                                addnav("","$whereto&flirtitem=one&name=".urlencode($row['name'])."&stage=1&target=".$row['acctid']);
                                } else if ($session['user']['charm']>($row['charm']+($session['user']['charm']*$charmlevelup/100)) && get_module_setting('charmlevelactivateup','loveshack')) {
                                rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td><a href='$whereto&flirtitem=two&name=".urlencode($row['name'])."&stage=1&target=".$row['acctid']."'>");
                                addnav("","$whereto&flirtitem=two&name=".urlencode($row['name'])."&stage=1&target=".$row['acctid']);
                                } else {
                                rawoutput("<tr class='".($i%2?"trlight":"trdark")."'><td><a href='$whereto&flirtitem=$w&name=".urlencode($row['name'])."&stage=1&target=".$row['acctid']."'>");
                                addnav("","$whereto&flirtitem=$w&name=".urlencode($row['name'])."&stage=1&target=".$row['acctid']);
                                }
                                output_notl($row['name']);
                                rawoutput("</td></tr>");
                        }
                        rawoutput("</table>");
                } else {
                        output("`c`@`bA user was not found with that name.`b`c");
                }
                output_notl("`n");
        }
        output("`^`b`c`\$Flirt`xing...`0`c`b");
        output("`nWho do you want to do that with?");
        if ($sameGenderAllowed==1) {
                output("`nSame gender flirting is allowed.");
        } else {
                output("`nSame gender flirting is not allowed.");
        }
        output("`n`nName of user: ");
        rawoutput("<input name='whom' maxlength='50' value=\"".htmlentities(stripslashes($whom))."\">");
        $apply = translate_inline("Flirt");
        rawoutput("<input type='submit' class='button' value='$apply'></form>");
}
?>
