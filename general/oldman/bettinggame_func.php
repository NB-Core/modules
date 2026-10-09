<?php
declare(strict_types=1);

function oldman_bettinggame(string $from): void
{
    global $session;
    $bet = abs((int) httpget('bet') + (int) httppost('bet'));
    if ($bet <= 0) {
        output("`@\"`#You have 6 tries to guess the number I am thinking of, from 1 to 100.  Each time I will tell you if you are too high or too low.`@\"`n`n");
        output("`@\"`#How much would you bet young %s?`@\"", translate_inline($session['user']['sex'] ? 'lady' : 'man'));
        rawoutput("<form action='" . $from . "op=game' method='POST'>");
        rawoutput("<input name='bet' id='bet'>");
        $b = translate_inline('Bet');
        rawoutput("<input type='submit' class='button' value='$b'>");
        rawoutput('</form>');
        rawoutput("<script language='JavaScript'>document.getElementById('bet').focus();</script>");
        addnav('', $from . 'op=game');
        $session['user']['specialmisc'] = (string) e_rand(1, 100);
    } elseif ($bet > $session['user']['gold']) {
        $session['user']['specialinc'] = '';
        $session['user']['specialmisc'] = '';
        output("`@The old man reaches out with his stick and pokes your coin purse.  \"`#I don't believe you have `^%s`# gold!`@\" he declares.`n`n", $bet);
        output("`@Desperate to really show him good, you open up your purse and spill out its contents: `^%s`@ gold.`n`n", (int) $session['user']['gold']);
        output("Embarrassed, you think you'll head back into the forest.");
        addnav("Return to the forest", $from);
    } else {
        $guess = (int) httppost('guess');
        $try = (int) httpget('try');
        if ($guess !== 0 || $try >= 1) {
            if ($guess == (int) $session['user']['specialmisc']) {
                if ($try == 1) {
                    output("`@\"`#INCREDIBLE!!!!`@\" the old man shouts, \"`#You guessed the number in only`^one try`#! Well, congratulations to you, and I am thoroughly impressed! It is almost as if you read my mind.`@\"");
                    output("He looks at you suspiciously and thinks about trying to make off with your winnings, but remembers your seemingly psychic abilities and hands over the `^%s`@ gold that he owes you.", $bet);
                } else {
                    output("`@\"`#AAAH!!!!`@\" the old man shouts, \"`#You guessed the number in only %s tries!  It was `^%s`#!!  Well, congratulations to you, I think I'll just be going now... `@\" he says as he heads for the underbrush.`n`n", $try, (int) $session['user']['specialmisc']);
                    output("A swift blow from your `^%s`@ knocks him unconscious.`n`n", $session['user']['weapon']);
                    output("You help yourself to his coinpurse, retrieving the `^%s`@ gold that he owes you.", $bet);
                }
                $session['user']['gold'] += $bet;
                debuglog("won $bet gold from the old man in the forest");
                $session['user']['specialinc'] = '';
                $session['user']['specialmisc'] = '';
                addnav("Return to the forest", $from);
            } else {
                if ($try >= 6 && ($guess >= 0 && $guess <= 100)) {
                    output("`@The old man chuckles.  \"`#The number was `^%s`#,`@\" he says.", (int) $session['user']['specialmisc']);
                    output("You, being the honorable citizen that you are, give the man the `^%s`@ gold that you owe him, ready to be away from here.", $bet);
                    $session['user']['specialinc'] = '';
                    $session['user']['specialmisc'] = '';
                    $session['user']['gold'] -= $bet;
                    debuglog("lost $bet gold to the old man in the forest");
                    addnav("Return to the forest", $from);
                } else {
                    if ($guess > 100 || $guess < 0 || ! $guess) {
                        $try--;
                        output("`@The old man chuckles, \"`#This will be like taking a sword from a baby if you think %s is between one and one hundred!`@\"`n", $guess);
                        output("`@\"`#You have `^%s`# %s left.`@\"`n", 6 - $try, ((6 - $try) == 1 ? 'try' : 'tries'));
                    } elseif ($guess > (int) $session['user']['specialmisc']) {
                        output("`@\"`#Nope, not `^%s`#, it's lower than that!  That was try `^%s`#.`@\"`n`n", $guess, $try);
                    } else {
                        output("`@\"`#Nope, not `^%s`#, it's higher than that!  That was try `^%s`#.`@\"`n`n", $guess, $try);
                    }
                    output("`@You have bet `^%s`@.  What is your guess?", $bet);
                    rawoutput("<form action='" . $from . "op=game&bet=$bet&try=" . (++$try) . "' method='POST'>");
                    rawoutput("<input name='guess' id='guess'>");
                    $g = translate_inline('Guess');
                    rawoutput("<input type='submit' class='button' value='$g'>");
                    rawoutput('</form>');
                    rawoutput("<script language='JavaScript'>document.getElementById('guess').focus();</script>", true);
                    addnav('', $from . "op=game&bet=$bet&try=$try");
                }
            }
        } else {
            output("`@You have bet `^%s`@.  What is your guess?", $bet);
            rawoutput("<form action='" . $from . "op=game&bet=$bet&try=1' method='POST'>");
            rawoutput("<input name='guess' id='guess'>");
            $g = translate_inline('Guess');
            rawoutput("<input type='submit' class='button' value='$g'>");
            rawoutput('</form>');
            rawoutput("<script language='JavaScript'>document.getElementById('guess').focus();</script>", true);
            addnav('', $from . "op=game&bet=$bet&try=1");
        }
    }
}
