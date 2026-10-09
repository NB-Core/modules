<?php
function sweets_generator($internal){
	global $session;
	$stopq = get_module_setting("stopq");
	$stoptoday = e_rand(2,4);
	if (isset($internal['stopped']) && $internal['stopped']==0){
		switch (e_rand(1,18)){
			case 1:
				output("You gobble up your sweets and gain `^5 hitpoints.");
				$session['user']['hitpoints']+=5;
			break;
			case 2:
				output("You gobble up your sweets and gain `^10 hitpoints.");
				$session['user']['hitpoints']+=10;
			break;
			case 3:
				output("You gobble up your sweets and gain `^15 hitpoints.");
				$session['user']['hitpoints']+=15;
			break;
			case 4:
				output("You gobble up your sweets and gain `^20 hitpoints.");
				$session['user']['hitpoints']+=20;
			break;
			case 5:
				output("You gobble up your sweets and gain `^25 hitpoints.");
				$session['user']['hitpoints']+=25;
			break;
			case 6:
				output("You can feel the sugar blasting through your veins.");
				output(" You gain `^1 turn.");
				$session['user']['turns']+=1;
			break;
			case 7:
				output("You can feel the sugar blasting through your veins.");
				output(" You gain `^2 turns.");
				$session['user']['turns']+=2;
			break;
			case 8:
				output("You can feel the sugar blasting through your veins.");
				output(" You gain `^3 turns.");
				$session['user']['turns']+=3;
				if ($stopq==1){
					$internal['stopped']=1;
					$internal['stop']=$stoptoday;
				}
			break;
			case 9:
				output("You can feel the sugar blasting through your veins.");
				output(" You gain `^4 turns.");
				$session['user']['turns']+=4;
				if ($stopq==1){
					$internal['stopped']=1;
					$internal['stop']=$stoptoday;
				}
			break;/*
			case 10:
				output("You can feel the sugar blasting through your veins.");
				output(" You gain `^5 turns.");
				$session['user']['turns']+=5;
				if ($stopq==1){
				set_module_pref("stopped",1);
				set_module_pref("stop",$stopday);
				}
			break;*/
			case 11:
				output("You gobble up your treat and see something sparkling at the bottom.");
				output(" You gain `^1 Gem.");
				$session['user']['gems']+=1;
				if ($stopq==1){
					$internal['stopped']=1;
					$internal['stop']=$stoptoday;
				}				
			break;
			case 12:
				output("You gobble up your treat and see something sparkling at the bottom.");
				output(" You gain `^2 Gems.");
				$session['user']['gems']+=2;
				if ($stopq==1){
					$internal['stopped']=1;
					$internal['stop']=$stoptoday;
				}				
			break;/*
			case 13:
				output("You gobble up your treat and see something sparkling at the bottom.");
				output(" You gain `^3 Gems.");
				$session['user']['gems']+=3;
				if ($stopq==1){
					$internal['stopped']=1;
					$internal['stop']=$stoptoday;
				}
			break;
			case 14:
				output("You gobble up your treat and see something sparkling at the bottom.");
				output(" You gain `^4 Gems.");
				$session['user']['gems']+=4;
					if ($stopq==1){
					$internal['stopped']=1;
					$internal['stop']=$stoptoday;
				}
			break;
			case 15:
				output("You gobble up your treat and see something sparkling at the bottom.");
				output(" You gain `^5 gems.");
				$session['user']['gems']+=5;
				if ($stopq==1){
					$internal['stopped']=1;
					$internal['stop']=$stoptoday;
				}
				break;*/
			default:
				output("You gobble up your treat and ... nothing happens. You just feel... erm... fat in a way.");
				output(" `qNothing`0 happens.");
			break;
		}
	} else {
		switch (e_rand(1,14)){
			case 1:
				output("You gobble up your sweets and gain `^5 hitpoints.");
				$session['user']['hitpoints']+=5;
			break;
			case 2:
				output("You gobble up your sweets and gain `^10 hitpoints.");
				$session['user']['hitpoints']+=10;
			break;
			case 3:
				output("You gobble up your sweets and gain `^15 hitpoints.");
				$session['user']['hitpoints']+=15;
			break;
			case 4:
				output("You gobble up your sweets and gain `^20 hitpoints.");
				$session['user']['hitpoints']+=20;
			break;
			case 5:
				output("You gobble up your sweets and gain `^25 hitpoints.");
				$session['user']['hitpoints']+=25;
			break;
			case 6:
				output("You can feel the sugar blasting through your veins.");
				output(" You gain `^1 turn.");
				$session['user']['turns']+=1;
			break;
			case 7:
				output("You can feel the sugar blasting through your veins.");
				output(" You gain `^2 turns.");
				$session['user']['turns']+=2;
			break;
			default:
				output("You gobble up your treat and ... nothing happens. You just feel... erm... fat in a way.");
				output(" `qNothing`0 happens.");
			break;
		}
	}
	return $internal;
}
?>
