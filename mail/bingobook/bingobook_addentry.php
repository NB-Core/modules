<?php
function bingobook_addentry() {
        global $session;

        $acctId  = (int) httpget('ac');
        $comment = httppost('comment');
        $bingoid = (int) httppost('bingoid');
        $go      = httppost('go');

        // The button label is translated, so only check that the form was sent.
        if ($go=="") {
                $encodedComment = htmlentities($comment, ENT_COMPAT, getsetting('charset', 'ISO-8859-1'));
                output("`qIf you want, you may add a short text for your own discretion to the entry (changeable later on):`n`n");
                rawoutput("<form action='runmodule.php?module=bingobook&op=addentry&ac={$acctId}' method='POST'>");
                addnav("","runmodule.php?module=bingobook&op=addentry&ac={$acctId}");
                rawoutput("<input type='hidden' name='bingoid' value='{$acctId}'>");
                rawoutput("<textarea name='comment' cols='50' rows='10' wrap='virtual'>{$encodedComment}</textarea>");
                $submit=translate_inline("Submit");
                rawoutput("<input type='submit' class='button' name='go' value='$submit'>");
                rawoutput("</form>");
        } else {
                // httppost() adds slashes; the comment is bound as a parameter.
                bingobook_insert($bingoid,$session['user']['acctid'],stripslashes((string)$comment));
                output("`qYou put that user in your bingo book... ");
                if (file_exists("modules/bingobook/devil.gif")) rawoutput("<img src='modules/bingobook/devil.gif'>");
        }
}
?>
