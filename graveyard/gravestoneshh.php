<?php
//The idea for this came from a site I used to play on a long time ago but I can't remember which one it was.
//If anyone knows, please tell me, just PM me on Dragonprime.net :)
//The gravestone epitaphs I used, I found on http://www.funehumor.com and http://www.corsinet.com/braincandy
function gravestoneshh_getmoduleinfo(){
        $info = array(
        "name"=>"GraveStones - Haunted House",
        "version"=>"1.0",
        "author"=>"`)ShadowRaven - Edited by KainStrider",
        "category"=>"Shades",
        "download"=>"http://",

);
return $info;
}
function gravestoneshh_install(){
        if (!is_module_active('gravestoneshh')){
                output("`^Installing Gravestones`n`0");
        }else{
                output("`^Updating Gravestones`n`0");
}
        module_addhook("shades");
return true;
}
function gravestoneshh_uninstall(){
return true;
}
function gravestoneshh_dohook($hookname,$args){
        switch($hookname){
                case "shades":
                        addnav("Places");
                        addnav("Read Gravestones","runmodule.php?module=gravestoneshh");
break;
}
return $args;
}

function gravestoneshh_run(){
        global $session;
        $op = httpget('op');
        page_header("GraveStones");
        output("`n`)You look around the cemetary in the garden and notice gravestones all around, some of them look interesting.....`n`n`cYou walk over to one, and it is inscribed:`c`n`n`n ");
   switch(e_rand(1,64)){
        case 1: output("`c`%HERE LIES`nLESTER MORE`nFour slugs`nFrom a forty-four`nNo Les no more`c"); break;
        case 2: output("`c`%Here lies`nEZEKIEL AIKLE`nAge 102`nThe Good Die Young`c"); break;
        case 3: output("`c`&HERE LIES`nmy wife,`nI bid her goodbye.`nShe rests in peace`nand now so do I.`n`c"); break;
        case 4: output("`c`%Here lies the body`n of`nJOHN ROUND`nLost at sea`nand never found`c"); break;
        case 5: output("`c`%HERE LIES`na man named Zeke`nSecond fastest draw`nin Cripple Creek`c"); break;
        case 6: output("`c`%Here lies`nHENRY BLAKE`nHe stepped on the`ngas`nInstead of the brake`c"); break;
        case 7: output("`c`&Soon ripe`nSoon rotten`nSoon gone`nBut not forgotten`c"); break;
        case 8: output("`c`%HERE LIES`nJOHN YEAST`nPardon me`nfor not rising`c"); break;
        case 9: output("`c`%RIP`nTed N. Buried `c"); break;
        case 10: output("`c`%BILL BLAKE`nWas hanged by mistake`c"); break;
        case 11: output("`c`&RIP`nBarry M. Deep `c"); break;
        case 12: output("`c`%See`nI told you I was`n SICK!`c"); break;
        case 13: output("`c`%Here lies the body`n of`nEDWARD HYDE`nWe laid him here`nbecause he died`c"); break;
        case 14: output("`c`%Dear Departed`nBROTHER DAVE`nhe chased a bear`ninto a cave`c"); break;
        case 15: output("`c`&ARTHUR C. HOMAN`nOnce I wasn't`nThen I was`nNow I ain't again`c"); break;
        case 16: output("`c`%REST IN PEACE`nCOUSIN HUET`nwe all know`nyou didn't do it `c"); break;
        case 17: output("`c`%HERE LIES`nGOOD OLD FRED`na great big rock`nfell on his head`c"); break;
        case 18: output("`c`%HERE LIES`nthe Pillsbury Dough Boy`nHe will rise again "); break;
        case 19: output("`c`&RIP`nGood Friend Gordon`nnow you've crossed`nthe River Jordan `c"); break;
        case 20: output("`c`%Here Lies Joyce`nShe'd rather not`nBut no choice`c"); break;
        case 21: output("`c`%In memory of`nANNA HOPEWELL`nHere lies the body`n of our Hana`nDone to death`nby a banana`nIt wasn't the fruit`nthat laid her low`nBut the skin of the thing`nthat made her go`c"); break;
        case 22: output("`c`%EZEKIEL PEASE`nPease is not here,`nOnly his pod`nHe shelled out his Peas`nAnd went to his God `c"); break;
        case 23: output("`c`&Grim death took me`nwithout any warning`nI was well at night,`nand dead in the morning`c"); break;
        case 24: output("`c`%Here lies the body of`nMARGARET BENT`nShe kicked up her heels`nAnd away she went`c"); break;
        case 25: output("`c`%This life's a dream`nAnd all things show it`nI thought so once`n and`n Now I know it `c"); break;
        case 26: output("`c`%Here lies Clyde`nWhose life was full`nUntil he tried`nTo milk a bull `c"); break;
        case 27: output("`c`&In Memory of`nBEZA WOOD`n- Age 45 yrs.`nHere lies one Wood`nEnclosed in wood`nOne Wood Within another.`nThe outer wood`nIs very good:`nWe cannot praise`nThe other `c"); break;
        case 28: output("`c`%HERE LIES NED`nThere is nothing more`nto be said`nBecause we like to`nspeak well of the dead `c"); break;
        case 29: output("`c`%Pause, stranger, when you pass me by,`nFor as you are,`nso once was I.`nAs I am now,`nso will you be.`nSo Prepare for death`nand follow me.`n`n`7Scratched under this you read:`n`n`%To follow you I'm not content`nUntil I know which way you went!`c"); break;
        case 30: output("`c`%First a Cough`nCarried Me Off`nThen a Coffin`nThey Carried Me Off In `c"); break;
        case 31: output("`c`&OWEN MOORE`nGone away`nOwin' more`nThan he could pay`c"); break;
        case 32: output("`c`%Here lies ANN MANN`nWho lived an old maid`nBut died an old Mann `c"); break;
        case 33: output("`c`%Here lies the father`n of 29`nHe would have had`n more`nBut he didn't have`n time `c"); break;
        case 34: output("`c`%Here lies an Atheist,`n all dressd up,`n and no place to go.`c"); break;
        case 35: output("`c`%Here I lies,`n and no wonder I'm dead.`n For the wheel of a wagon`n went over my head.`c"); break;
        case 36: output("`c`%On the 22nd of June,`n Jonathan Fiddle`n went out of tune.`c"); break;
        case 37: output("`c`%I knew this`n was going to happen to me `c"); break;
        case 38: output("`c`%Here lies Slip Mcvey`nHe would be here today`nBut bad whiskey and a fast gun`nput him away`c"); break;
        case 39: output("`c`%Ma Loved Pa,`nPa Loved Women,`nMa caught Pa with one in swimmin..`nhere Lies Pa`c"); break;
        case 40: output("`c`&I Am Woman`nHear Me Roar`nAnd Boy Did She`c"); break;
        case 41: output("`c`%Here lies the body of Samuel Crane`nHe ran a race with a passenger train`nHe got to the crossing and almost across`nSam and his car was a total loss`nSams spirit now tolls his knell`nThat Sam is on his way to well`nIf he only took time to stop look and listen`nHe'd be living now instead of missing`c"); break;
        case 42: output("`c`%I was Carolina Born`nand Carolina bred`nand here I lay`nCarolina dead!`c"); break;
        case 43: output("`c`%She did what she could`c"); break;
        case 44: output("`c`%Here lies Kelly,`nWe buried him today.`nHe lived the life of Riley,`n....when Riley was away!`c"); break;
        case 45: output("`c`&Here Lies The Body Of A Man Who Died`nNobody Mourned - Nobody Cried`nHow He Lived - How He Fared`nNobody Knows - Nobody Cared`c"); break;
        case 46: output("`c`%Assuming my death`nhas occurred`nAnd five doctors`nhave concurred..`nPlease REVIVE me!`nIf you can get`nno breath`nTake the person who`ncaused my death`nand bury them`nright beside me.`c"); break;
        case 47: output("`c`%Ope'd my eyes`nTook a peep.`nDidn't like it`nWent back to sleep.`c"); break;
        case 48: output("`c`%We all have a debt`nTo nature due`nI've paid mine`nAnd so must you.`c"); break;
        case 49: output("`c`%-- Hana --`nThe Children of Israel wanted bread`nAnd the Lord sent them mana`nOld clerk Wallace wanted a wife`nAnd the Devil sent him Hana`c"); break;
        case 50: output("`c`%Sacred to the memory of Jared Bates,`nWho died Aug. the 6th.`nHis widow, aged 24, lives at 7 Elm Street,`nHas every qualification for a good wife,`nAnd longs to be comforted.`c"); break;
        case 51: output("`c`%He looked`nfor gold`nand died of`nlead poison`c"); break;
        case 52: output("`c`%Here's to Johnny quite a guy`nVery sad he had to die`nAll was well could not be better`nTill he wrote my girl a letter.`c"); break;
        case 53: output("`c`%Wherever you be,`nLet your wind go free.`nFor holding it in,`nWas the killing of me.`c"); break;
		case 54: output("`c`%Here lies the body of my sweet sister;`nShe was just fine 'til Dracula kissed her`c"); break;
		case 55: output("`c`%Here lies the father of 29`nThere would have been more`nBut he ran out of have time`c"); break;
		case 56: output("`c`%Grim death took me `nwithout any warning`nI was well at night, `nand dead in the morning`c"); break;
		case 57: output("`c`%Farewell my young companions all `nFrom death's arrest no age is free `nRemember this, a warning call`nPrepare to follow after me `nThe wise, the sober and the brave `nMust try the cold and silent grave`c"); break;
		case 58: output("`c`%Stop by here my friends `nAs you pass by; `nAs you are now `nSo once was I. `nAs I am now `nSo you must be. `nPrepare for death`nAnd follow me.`c"); break;
		case 59: output("`c`%Dapper Dan `nWas a lady's man `nAnd known for miles around `nBut he slept with Pearl, `nThe Gambler's girl, `nHe now lies six feet under ground.`c"); break;
		case 60: output("`c`%~ Count Dracula ~`nMay you always be in our hearts,`nAnd may`nthat stake always be in yours`c"); break;
		case 61: output("`c`%Mary, Mary, quite contrary`nHow does your garden grow?`nQuite well I bet`nSince it's well fed`nBy your body down below`c"); break;
		case 62: output("`c`%While living folks my tomb do view,`nRemember well - there's room for you!`c"); break;
		case 63: output("`c`%He stole our stuff - he had no class`nSo we got medieval on his ass`c"); break;
		case 64: output("`c`%It does my heart a world of good`nTo see you buried in a box of wood`nYou slept with them all when you were a-creepin'`nNow you sleep alone while worms start to seep in.`nIn loving memory from your grieving widow..`c"); break;
		}
addnav("Read another gravestone","runmodule.php?module=gravestoneshh&op=gravestonemodule");
if ($session['user']['hitpoints']>0) {
	addnav("Return to Haunted Gardens","runmodule.php?module=hauntedhouse&op=hauntedhousegarden");
} else {
	addnav("Return to the Shades","shades.php");
}
page_footer();
}
?>
