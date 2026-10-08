<?php
function bingobook_changecomment() {
        global $session;

        $acctId  = (int) httpget('ac');
        $comment = httppost('comment');

        if ($comment === '') {
                $comment = bingobook_getcomment($acctId);
        }

        $bingoid = (int) httppost('bingoid');
        $go      = httppost('go');

        // The button label is translated, so only check that the form was sent.
        if ($go=="") {
                $encodedComment = htmlentities($comment, ENT_COMPAT, getsetting('charset', 'ISO-8859-1'));
                output("`qIf you want, you may add a short text for your own discretion to the entry (changeable later on):`n`n");
                rawoutput("<form action='runmodule.php?module=bingobook&op=changecomment&ac={$acctId}' method='POST'>");
                addnav("","runmodule.php?module=bingobook&op=changecomment&ac={$acctId}");
                rawoutput("<input type='hidden' name='bingoid' value='{$acctId}'>");
                rawoutput("<textarea name='comment' cols='50' rows='10' wrap='virtual'>{$encodedComment}</textarea>");
                $submit=translate_inline("Submit");
                rawoutput("<input type='submit' class='button' name='go' value='$submit'>");
                rawoutput("</form>");
        } else {
                bingobook_change($bingoid,$session['user']['acctid'],stripslashes($comment));
                output("`gYou have altered the comment for that user in your bingo book... ");
                if (file_exists("modules/bingobook/devil.gif")) rawoutput("<img src='modules/bingobook/devil.gif'>");
        }
}
?>
