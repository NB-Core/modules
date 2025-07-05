<?php
/*needed:
Settings: fee, maxplayers,minplayers,gamename
operations(op): view,end,resume and a default with the starting screen
hooks: add to parlorgames and push the array with gameinfos there.
	structure and fields like this:
		$merge=array("fee"=>get_module_setting("fee"),"module"=>"madmax","name"=>translate_inline("Word Python - The friendly storytelling"));
		array_push($args,$merge);
fields:
record your game in the gamedatafield and use the functions in the playergames/func.php to access it. use this file as an example
	
*/

function madmax_getmoduleinfo(){
$info = array(
	"name"=>"Mad Max",
	"version"=>"1.0",
	"author"=>"`2Oliver Brendel",
	"category"=>"Games",
	"download"=>"http://lotgd-downloads.com",
	"settings"=> array (
		"fee"=>"Fee for this game?,int|50",
		"maxplayers"=>"How many players are allowed for this game? (Server performance),int|10",
		"minplayers"=>"How many players are necessary for this game?,int|2",
		"gamename"=>"How is this game called in the parlor?,text|Mad Max",
		"maxpoints"=>"Max lifepoints assignable,int|5",
		"maxlength"=>"How long can sentences be? (at least 3 to let them enter the result),int|200",
		),
	"requires"=>array(
		"playergames"=>"1.0|`2Oliver Brendel",
		), 
	);
	return $info;
}

function madmax_install(){
	module_addhook("parlorgames");
	return true;
}

function madmax_uninstall(){
	return true;
}

function madmax_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		case "parlorgames":
			$merge=array("module"=>"madmax","name"=>translate_inline("Mad Max - TwentyOne"));
			array_push($args,$merge);
			break;
	}
	return $args;
}

function madmax_run(){
	global $session;
	page_header("Mad Max - TwentyOne");
	villagenav();
	require_once("./modules/playergames/func.php");
	require_once("./modules/playergames/madmax_func.php");
	playernav();
	$number=httpget('number');
	$length=get_module_setting("maxpoints");
	$op=httpget('op');
	switch($op) {
		case "rules":
			output("`c`^These are the rules for for `%Mad Max - TwentyOne`^`c");
			output_notl("`n`n");
			output("This game has no real turns, but a start, gamepoints and an end.`n");
			output("The patron will start with his throw of two 6-sided dice.");
			output(" He will make this roll secretly and order the result by die descending.");
			output(" (Means he rolles 3 and 2, then it will be 3 and 2 for the game.");
			output(" If he rolled 2 and 3, this would have been also 3 and 2.)");
			output(" The 2-1 is special and the highest value. Also a special rule applies (we will come to that later on)");
			output("`n The value of the rolls is therefore ascending like this:`n");
			output_notl("3-1, 3-2,4-1,4-2,4-3,5-1,5-2,5-3,5-4,6-1,6-2,6-3,6-4,6-5,1-1,2-2,3-3,4-4,5-5,6-6,2-1");
			output_notl("`n`n");
			output("`bAim of the game:`n`b");
			output("After you have rolled the dice, you pass the dice with hidden results (in a cup i.e.) on saying what you have here.");
			output(" You CAN lie, which is the funny part. Because the other player now has 2 choices.");
			output(" Either he believes you, than he has to roll a higher result than you, or he disbelieves you, then he lifts the cup.");
			output("`nIf he believes, then he does the same procedure as you. Roll secretly and so on, yet he *must* have a higher result. Saying a 43 after your 32 is a must. If you said 65 and he says 43 he loses 1 point automatically.");
			output("`nIf he disbelieves you, lifts the cup and you lied, you will lose 1 point. In the case you were right, he will lose 1 point.");
			output("`nAs the roll must be higher as the previous, you will soon realize you have to lie :D");
			output("`nAn identical roll as 1-1, or 2-2 counts higher than one with differing die (2-1 is yet highest.");
			output("`nWhen you lose all points you have (the patron set the number for everybody) you will lose and the other players play on.");
			output(" It is a 'last man standing' game. (Usually, here in Germany, your life points are how many schnapps you can drink until you naturally drop out.)");
			output_notl("`n`n");
			output("Now to TwentyOne(=Mad Max): If you have rolled a 2-1 (or 1-2, counts like above mentioned), you have a MadMax.");
			output(" When passing on, you can say that it is a TwentyOne. If he believes you, he loses one point and a new round starts.");
			output(" If he disbelieves you, and it is a TwentyOne, he will lose two points. If (you might also lie about it) it `bnot`b a TwentyOne, you will lose two points.");
			output_notl("`n`n");
			output("There is one special rule about believing: You can pass the roll from the previous player to the next player.");
			output(" That means, you just grab his pass on and go to the next player. It is now up to him to believe or not.");
			output(" In two player games, it's not that interesting... but with multiple players... and a Mad Max passed over ... it can get nasty.");
			output_notl("`n`n");
			output("As you can see, this is a dangerous game... you might win by fooling everybody. Or lose by it.");
			output_notl("`n`n");
			output("`4Anyway, `2have `!`bFUN`b`^!");
			addnav("Return to where you came from","runmodule.php?module=madmax&op=".httpget('backop')."&number=$number");
			break;
			
		case "view":
			$data=getalldata($number);
			$name=getnames($data['players']);
			array_push($name,array('name'=>$data['playeronename']));
			$numi=explode(",",$data['players']);
			array_push($numi,$data['playerone']);
			$lifearray=getlifepoints($number);		
			output("`@Lifepoints:`n");
			$k=count($numi);
			for ($i=0;$i<$k;$i++) {
				output_notl("`Q".$name[$i]['name']."`Q: `^".$lifearray["s".$numi[$i]]."`n");
			}		
			output_notl("`n");
			showflow($number);
			break;			
		case "end":
			$array=getdata($number);
			if (!$array) $array=array();
			if (httpget('winner')) {
				$datanow="`@".$session['user']['name']."`& ".translate_inline("has won the game!!!");
			} elseif (httpget('loser')) {
				$next=nextplayer($number);
				$datanow="`@".translate_inline("Previous Player has won! ").$session['user']['name']."`& ".translate_inline("has lost the game!");
			} else {
				$datanow="`@".$session['user']['name']."`& ".translate_inline("has ended the game!");
			}
			enterdata($number,array('sentence'=>$datanow));
			endgame($number);
			output("Game has ended.");
			break;
		case "submit":
			if (!httppost('passedover')) {
				$passon=stripslashes(rawurldecode(httpget('passon')));
				$roll=httpget('roll');
				$passedover=0;
				$sentence=$session['user']['name']."`&: ";
				if (substr($passon,0,3)=="2-1") $sentence.=get_module_setting("gamename").substr($passon,3);
					else
					$sentence.=$passon;
				output("Roll submitted!");
			} else {
				$data=getdata($number);
				$array=array_pop($data);
				$passon=$array['passon'];
				$roll=$array['roll'];
				$passedover=1;
				$sentence=$session['user']['name']."`&".translate_inline("(passes over from previous player)").": ";
				if (substr($passon,0,3)=="2-1") $sentence.=get_module_setting("gamename").substr($passon,3);
					else
					$sentence.=$passon;
				output("You passed the roll over!");
			}
			$datanow=array("sentence"=>$sentence,"roll"=>$roll,"passon"=>substr($passon,0,3),"newround"=>$newround,"passedover"=>$passedover,"lifepoints"=>getlifepoints($number));
			enterdata($number,$datanow);
			$nextplayer=madmax_getvalidnext($number);
			nextturn($number,$nextplayer);
			playergames_setlastactive($number);
			break;
		case "resume":
			addnav("Rules","runmodule.php?module=madmax&op=rules&number=$number&backop=$op");
			output("`@Lifepoints:`n");
			$data=getalldata($number);
			if ($data['nextturn']!=$session['user']['acctid']) {
				output("You already did your move. Wait for the next player.");
				break;
			}
			$name=getnames($data['players']);
			array_push($name,array('name'=>$data['playeronename']));
			$numi=explode(",",$data['players']);
			array_push($numi,$data['playerone']);
			$lifearray=getlifepoints($number);			
			$k=count($numi);
			for ($i=0;$i<$k;$i++) {
				output_notl("`Q".$name[$i]['name']."`Q: `^".$lifearray["s".$numi[$i]]."`n");
			}		
			output_notl("`n");
			showflow($number);
			output_notl("`n");
			if (!httpget('startgame')) {
				$allrawdata=getdata($number);
				$last=array_pop($allrawdata);
				$prev=array_pop($allrawdata);
				if (!$prev) $prev=array("passon"=>0);
				$result=comparedice($last['roll'],$last['passon'],$prev['passon']);
				if ($last['newround']==1||$last['passedover']) $result=1; //new round, no check, or passed over = equal
				//debug($last);debug($prev);
				if (!$result) {
					output("`\$You have been passed on an invalid result. This player has lost one point.`^");
					$sentence=translate_inline("`\$Previous player has lost 1 lifepoints due to invalid passing.`@");
					$alldata=getalldata($number);
					$players=explode(",",$alldata['players']);
					array_push($players,$alldata['playerone']);
					$prevplayer=previousplayer($number);
					$lifearray["s".$prevplayer]--;
					//debug($lifearray);
					//checklife($lifearray,$alldata);
					$datanow=array("sentence"=>$sentence,"lifepoints"=>$lifearray,"newround"=>1);
					enterdata($number,$datanow);
					$counter=0;
					foreach($lifearray as $val) {
						if ($val>0) $counter++;
					}
					blockplayernav();					
					if ($lifearray["s".$prevplayer]<1) {
						$msg=array("`%You have passed an invalid result in game number %s. You passed %s over, but the player before you passed %s over.`nSorry, but you have 0 life points left and are out of the game!",$number,$last['passon'],$prev['passon']);
						$subject=array("You are out of the game!");
						require_once("./lib/systemmail.php");
						systemmail($prevplayer,$subject,$msg);
					}					
					if ($counter==1) {
						output("`\$`c`bCongratulations!!!`b`c`@");
						output_notl("`n`n");
						output("You are the winner of this game!");

						addnav("End the game","runmodule.php?module=madmax&op=end&number=$number&winner=1");
					} else {
						addnav("Start a new round","runmodule.php?module=madmax&op=resume&number=$number&startgame=1");
					}
					break;
				}
			}
			$check=unserialize($data['gamedata']);
			$now=array_pop($check);
			$link="runmodule.php?module=madmax&op=roll&number=$number";
			rawoutput("<form action='$link' method='POST'>");
			addnav("",$link);
			if ($now['passon']=="2-1") {
				output_notl("`n");
				$takeit = translate_inline("Believe!");
				rawoutput("<input type='submit' name='madmaxaccept' class='button' value='$takeit'>");
			} else {
				output("`n`n`@Roll two dice!");
				$submit = translate_inline("Roll!");
				output_notl("`n");
				rawoutput("<input type='submit' class='button' value='$submit'>");
			}
			$deny=translate_inline("Disbelieve!");
			$passover=translate_inline("Pass this roll over untouched");
			if (!$now['newround']) rawoutput("<input type='submit' name='deny' class='button' value='$deny'>");
			$link="runmodule.php?module=madmax&op=submit&number=$number";
			rawoutput("</form><form action='$link' method='POST'>");
			addnav("",$link);
			if (!$now['passedover']&!$now['newround']) rawoutput("<input type='submit' name='passedover' class='button' value='$passover'></form>");
			break;
		case "roll":
			showflow($number);
			if (httppost('madmaxaccept')) {
				$alldata=getalldata($number);
				$allrawdata=unserialize($alldata['gamedata']);
				$last=array_pop($allrawdata);
				$lifearray=getlifepoints($number);
				output("`^You think it is really a Mad Max... and take one point damage.`n");
				$damage=1;
				$points=translate_inline($damage==1?"lifepoint":"lifepoints");
				$sentence=array(translate_inline("`\$%s has lost %s %s due to accepting the unseen roll as a Mad Max.`@"),$session['user']['name'],$damage,$points);
				$sentence=call_user_func_array("sprintf",$sentence);
				$players=explode(",",$alldata['players']);
				array_push($players,$alldata['playerone']);
				$lifearray["s".$session['user']['acctid']]-=$damage;
				$datanow=array("sentence"=>$sentence,"lifepoints"=>$lifearray,"newround"=>1);
				enterdata($number,$datanow);
				$counter=0;
				foreach($lifearray as $val) {
					if ($val>0) $counter++;
				}
				blockplayernav();					
				if ($counter==1) {
					$msg=array("`%You have won game number %s!`n%s accepted your roll as Mad Max (You rolled %s)!",$number,$session['user']['acctid'],$last['roll']);
					$subject=array("You have won the game!");
					require_once("./lib/systemmail.php");
					systemmail($prevplayer,$subject,$msg);
					output("`\$`c`bOh my!!!`b`c`@");
					output_notl("`n`n");
					output("You have lost the game!");
					addnav("End the game","runmodule.php?module=madmax&op=end&number=$number&loser=1");
				} else {
					addnav("Start a new round","runmodule.php?module=madmax&op=resume&number=$number&startgame=1");
				}					
			}elseif (httppost('deny')) {
				$allrawdata=getdata($number);
				$last=array_pop($allrawdata);
				$lifearray=getlifepoints($number);
				output("`^You lift the cup... and discover...`n");
				if ($last['passon']==$last['roll']) {
					output("`4that your disbelief was wrong!`n");
					output("The roll was really %s!",$last['roll']);
					$damage=1;
					if ($last['roll']=='2-1') $damage=2;
					$points=translate_inline($damage==1?"lifepoint":"lifepoints");
					$sentence=array(translate_inline("`\$%s has lost %s %s due to disbelieving the roll but was wrong.`@"),$session['user']['name'],$damage,$points);
					$sentence=call_user_func_array("sprintf",$sentence);
					$alldata=getalldata($number);
					$players=explode(",",$alldata['players']);
					array_push($players,$alldata['playerone']);
					$lifearray["s".$session['user']['acctid']]-=$damage;
					$datanow=array("sentence"=>$sentence,"lifepoints"=>$lifearray,"newround"=>1);
					enterdata($number,$datanow);
					$counter=0;
					foreach($lifearray as $val) {
						if ($val>0) $counter++;
					}
					blockplayernav();					
					if ($counter==1) {
						$msg=array("`%You have won game number %s!`n%s did not believe you, but you were right(You rolled %s)!",$number,$session['user']['acctid'],$last['roll']);
						$subject=array("You have won the game!");
						require_once("./lib/systemmail.php");
						systemmail($prevplayer,$subject,$msg);
						output("`\$`c`bOh my!!!`b`c`@");
						output_notl("`n`n");
						output("You have lost the game!");
						addnav("End the game","runmodule.php?module=madmax&op=end&number=$number&loser=1");
					} else {
						addnav("Start a new round","runmodule.php?module=madmax&op=resume&number=$number&startgame=1");
					}					
				} else {
					output("`4that your disbelief was correct!`n");
					output("The roll was not a %s but a %s!",$last['passon'],$last['roll']);
					$damage=1;
					if ($last['passon']=='2-1') $damage=2;
					$points=translate_inline($damage==1?"lifepoint":"lifepoints");
					$sentence=array(translate_inline("`\$Previous Player has lost %s %s because %s did not believe in the roll and was correct!`@ The roll was in fact a %s!"),$damage,$points,$session['user']['name'],$last['roll']);
					$sentence=call_user_func_array("sprintf",$sentence);
					$alldata=getalldata($number);
					$players=explode(",",$alldata['players']);
					array_push($players,$alldata['playerone']);
					$prevplayer=previousplayer($number);
					$lifearray["s".$prevplayer]-=$damage;
					$datanow=array("sentence"=>$sentence,"lifepoints"=>$lifearray,"newround"=>1);
					enterdata($number,$datanow);
					$counter=0;
					blockplayernav();					
					foreach($lifearray as $val) {
						if ($val>0) $counter++;
					}
					if ($lifearray["s".$prevplayer]<1) {
						$msg=array("`%Your lie has been detected in game number %s.`nSorry, but you have 0 life points left and are out of the game!",$number);
						$subject=array("You are out of the game!");
						require_once("./lib/systemmail.php");
						systemmail($prevplayer,$subject,$msg);
					}
					if ($counter<=1) {
						output("`\$`c`bCongratulations!!!`b`c`@");
						output_notl("`n`n");
						output("You are the winner of this game!");
						addnav("End the game","runmodule.php?module=madmax&op=end&number=$number&winner=1");
					} else {
						addnav("Start a new round","runmodule.php?module=madmax&op=resume&number=$number&startgame=1");
					}
				}
			} else {
				if (httpget('roll')) {
					$rolled=httpget('roll');
				} else {
					$rolled=rolldice();
				}
				blockplayernav();
				addnav("","village.php");
				output("`@`n`nYou have rolled %s.",$rolled);
				output_notl("`n`n");
				output("Now enter what you want to pass on:`n");
				$link="runmodule.php?module=madmax&op=checkroll&number=$number&roll=$rolled";
				userpost($link,get_module_setting("maxlength"));
			}
			break;
		case "checkroll":
			$entered=httppost('passon');
			$rolled=httpget('roll');
			if (validatedice($entered)) {
				redirect("runmodule.php?module=madmax&op=submit&number=$number&roll=$rolled&passon=".rawurlencode($entered));
			} else {
				output("Erhm, this is not a valid pass-on, try again");
				addnav("Back to the roll","runmodule.php?module=madmax&op=roll&number=$number&roll=$rolled");	
				blockplayernav();
			}
			break;
		case "setpoints":
			$points=httppost('points');
			if ($points<1 || $points>$length) {
				output("That is not a valid number, try again");
				addnav("Back to setup","runmodule.php?module=madmax&number=$number");
				break;
			}
			output("`@Points set to %s!",$points);
			$sentence=translate_inline("%s has set the lifepoints to %s.");
			$sentence=array($sentence,$session['user']['name'],$points);
			$sentence=call_user_func_array("sprintf",$sentence);
			$alldata=getalldata($number);
			$players=explode(",",$alldata['players']);
			array_push($players,$alldata['playerone']);
			$lifearray=array();
			foreach($players as $val) {
				$lifearray=array_merge($lifearray,array("s".$val=>$points));
			}
			$datanow=array("sentence"=>$sentence,"lifepoints"=>$lifearray,"newround"=>1);
			enterdata($number,$datanow);
			playergames_setlastactive($number);
			addnav("Start the first round","runmodule.php?module=madmax&op=resume&number=$number&startgame=1");
			break;
		default:
			addnav("Rules","runmodule.php?module=madmax&op=rules&number=$number&backop=$op");
			output("`@Welcome to the new game, it is your turn as patron.");
			output("`n`nStart the game by setting the life points (%s points max):",$length);
			output_notl("`n`n");
			$link="runmodule.php?module=madmax&op=setpoints&number=$number";
			pointspost($link,$length);
			break;
	}
	
	page_footer();

}
?>
