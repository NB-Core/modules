<?php
set_module_pref('inShack',1);
page_header("The Loveshack");
$link = "runmodule.php?module=loveshack&op=loveshack";
$operation = httpget('op2');
$target = httpget('target');
$bartender = get_module_setting("bartendername");
$genderbartender = (get_module_setting('genderbartender')?translate_inline("she"):translate_inline("he"));
$genterbartenderp = (get_module_setting('genderbartender')?translate_inline("herself"):translate_inline("himself"));
$title = (get_module_setting('genderbartender')?translate_inline("Lady"):translate_inline("Lord"));
addnav("Navigation");
addnav("Return to the Gardens","gardens.php");
addnav("Actions");
addnav(array("`^Talk to %s",$bartender),$link."&op2=talk");
addnav("The Bar",$link."&op2=bar");
addnav("Actions");
addnav("Buy someone a Drink",$link."&op2=flirt&flirtitem=drink");
addnav("Buy someone some Roses",$link."&op2=flirt&flirtitem=roses");
addnav("Kiss someone",$link."&op2=flirt&flirtitem=kiss");
$items=array(
	"roses"=>"`%{mname}`^ bought some expensive roses for you, at the loveshack!",
	"drink"=>"`%{mname}`^ bought you a drink at the loveshack!",
	"slap"=>"`^{mname}`\$ just `^slapped`\$ you, at the loveshack! You aren't going to stand for that, are you?",
	"kiss"=>"`%{mname}`^ planted a kiss on your lips!`n{mname}",
	"fail"=>"`^{mname}`@ attempted to flirt with you, but having heard `^{mname}`@ saying '`&{gen} is slightly substandard compared to my usual fare`@', you walk off in an understandable huff. `^{mname}`@'s flirt points have decreased by decreased by {pts} points with you.",
	'mailheader-roses'=>'`%Roses`^ from {mname}`^!',
	'mailheader-drink'=>'`^A Drink`% from {mname}`%!',
	'mailheader-slap'=>'`@A SLAP!!!',
	'mailheader-ignore'=>'`^BYE BYE!',
	'mailheader-kiss'=>'`@A KISS!',
	'mailheader-fail'=>'`@Failed Flirt!',
	'cost-roses'=>get_module_setting('prroses'),
	'cost-drink'=>get_module_setting('prdrink'),
	'points-roses'=>get_module_setting('poroses'),
	'points-drink'=>get_module_setting('podrink'),
	'points-slap'=>get_module_setting('poslap'),
	'points-kiss'=>get_module_setting('pokiss'),
	'shortcut'=>array(), //insert here the items you want to have a shortcut at the flirt selection
	);
$items = modulehook("loveshack-items", $items);
addnav("Slap someone",$link."&op2=flirt&flirtitem=slap");
switch ($operation) {
	default:
		output("`n`@As you stroll towards an imposing building, you notice a pumpkin-shaped door in the side...");
		output("`nWalking towards the garish portal, a strange feeling comes over you, and knowledge pours into your head.");
		output("`n`^Maybe a chance possibility, collapsed.. what would have happened?");
		output("`nWith this knowledge, a catchy little song sounds in your head... \"`&Bang, bang, bang on the door baby..`^\" ... \"`&The Love shack(helloween style), is a little old place where.. we can get together..`^\"");
		output("`n`@Shivering, you snap out of a semi-trance.. what on earth was that?");
		output("`nKnocking on the ornamented gateway, you wait for the door to open, and enter the Loveshack.");
		output("`n`nSomeone strolls up to you, and begins to speak...");
		output("`n`3\"`&Hello, I am %s`&, and I am a part-time Bartender, as well as owner of this establishment.`3\"",$bartender);
		output("`n`^%s `&enquires as to how %s may help you.",$bartender,$genderbartender);
	break;
	case "flirt":
		$stage = httpget('stage');
		$flirtitem = httpget('flirtitem');
		//ignore check
		$ignores=get_module_pref("iveignored","friendlist",$target);
		if ($target!='' && in_array($session['user']['acctid'],explode('|',$ignores))) {
			output("%s`Q has ignored you, so you cannot send `Q anything...",urldecode(httpget('name')));
			break;
		}
		//
		if ($stage=='') $stage = 0;
		if ($stage==0) {
			require_once("./modules/loveshack/flirtform.php");
			loveshack_fform($flirtitem);
		} else {
			$gendertarget = (httpget('gendertarget')?translate_inline("She"):translate_inline("He"));
			$gendertargetp = (httpget('gendertarget')?translate_inline("her"):translate_inline("his"));
			$name = urldecode(httpget('name'));
			set_module_pref('flirtsToday',get_module_pref('flirtsToday')+1);
			if (get_module_pref('flirtsToday')<=get_module_setting('maxDayFlirt')) {
				//if ($flirtitem!='ignore') $pts = get_module_setting('po'.$flirtitem);
				$haspaid = true;
				if (isset($items["cost-".$flirtitem])) {
					$pr = $items["cost-".$flirtitem];
				} else {
					$pr = 0; //free else
				} 
				if ($session['user']['gold']>=$pr&&$pr>0) {
					$session['user']['gold']-=$pr;
					output("`@You pay `^%s`@ Gold...`n`n",$pr);
					} elseif ($pr>0) {
					output("`@Cheapo! You don't have enough gold for that! You need `^%s`@ Gold!",$pr);
					$haspaid=false;
				}
				//debug("Paid:".$pr." Item:".$flirtitem);
				if ($haspaid) {
					$failchance = get_module_setting('fail');
					$random = e_rand(1,100);
					if ($random<=$failchance&&$flirtitem!='ignore'&&$flirtitem!='slap') $flirtitem = "fail";
					switch ($flirtitem) {
						case "one": //auto-fail if flirtpoints are too far away
							output("`%%s`% realizes your intentions and looks disgustedly at you.`nMaybe you should get more charming to impress `^%s`%.",$name,$name);
						break;
						case "two": //auto-fail if flirtpoints are too far away
							output("`%You take a good look at %s`%. You don't think that the charm of %s`% is attracting you and therefore walk away.",$name,$name);
						break;
						case "fail":
							//loveshack_modifyflirtpoints($target,-$items["points-".$flirtitem]);
							output("`%%s`% realizes your intentions and looks disgustedly at you.`n",$name,$name);
							loveshack_flirtdec();
						break;
						case "ignore":
							//loveshack_removeplayer($target,$session['user']['acctid']);
							//loveshack_removeplayer($session['user']['acctid'],$target);
							/*loveshack_modifyflirtpoints($target,-loveshack_getflirtpoints($target),$session['user']['acctid'],false);
							$user=$session['user']['acctid'];
							loveshack_modifyflirtpoints($user,-loveshack_getflirtpoints($user),$target,false);*/
							output("`@You ignore %s`@. %s walks off, forgetting your flirts.",$name,$gendertarget);
						break;
						case "drink":
							//loveshack_modifyflirtpoints($target,$items["points-".$flirtitem]);
							output("%s`@ emphatically thanks you for the drink.",$name,$name);
						break;
						case "roses":
							//loveshack_modifyflirtpoints($target,$items["points-".$flirtitem]);
							output("%s`@ gasps with delight!",$name,$name);
						break;
						case "slap":
							//loveshack_modifyflirtpoints($target,-$items["points-".$flirtitem],$session['user']['acctid'],false);
							output("`%%s`% stares at you in anger, as %s`% feels the slap.. ",$gendertarget,$name,$name);
						break;
						case "kiss":
							//loveshack_modifyflirtpoints($target,$items["points-".$flirtitem]);
							output("`@As `^%s`@ nods at you, you reach for `&%s`@ and kiss %s lucious lips!!",$bartender,$name,$name,$name);
						break;
						default:
							//loveshack_modifyflirtpoints($target,$items['points-'.$flirtitem]);
							require_once("./lib/substitute.php");
							$originalsubst=array('{name}','{gen}','{mname}','{pts}');
							$subst = array($name,$gendertarget,$session['user']['name'],$items["points-".$flirtitem]);
							output(substitute_array($items['output-'.$flirtitem],$originalsubst,$subst));
					}
					if (isset($items["mailheader-".$flirtitem])) {
						$title = $items["mailheader-".$flirtitem];
						$text = $items[$flirtitem];
						require_once("./lib/substitute.php");
						$originalsubst=array('{name}','{gen}','{mname}','{pts}');
						$subst = array($name,$gendertarget,$session['user']['name'],$items["points-".$flirtitem]);
						$title= substitute_array($title,$originalsubst,$subst);
						$text= substitute_array($text,$originalsubst,$subst);
						require_once("lib/systemmail.php");
						systemmail($target,$title,$text);
						//debug("ac:".$target." and title:".$title." and text: ".$text);
					}
				}
			} else {
				output("`@Erm.. you can't flirt any more today, pal!");
			}
		}
	break;
	case "talk":
		if (get_module_pref('flirtsToday')>get_module_setting('maxDayFlirt')) set_module_pref('flirtsToday',get_module_setting('maxDayFlirt'));
		output("`^%s`3 says, \"`&I am a %s of everything and the little bit more.. I can help you with problems.. here is some information for you.`n`n`3\"`n`&You've interacted `%%s`& times today, out of a possible `%%s`&.`n",$bartender,$title,get_module_pref('flirtsToday'),get_module_setting('maxDayFlirt'));
		require_once("./modules/loveshack/flirtlist.php");
		loveshack_flist($items);
	break;
	case "bar":
		addnav("Drinks");
		modulehook("ale", array());
		require_once("lib/commentary.php");
		addcommentary();
		output("`@As you sit down at the bar, %s`@ inquires as to if you would like a drink.`nLooking around, you nod and talk to other patrons.`n`n",$bartender);
		viewcommentary("loveshack","`#Discourse?`@",25,"discourses");
	break;
}
page_footer();
?>
