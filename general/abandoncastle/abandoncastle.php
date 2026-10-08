<?php
use Shinobi\Modules\AbandonCastle\MapService;
use Shinobi\Modules\AbandonCastle\FightController;
use Shinobi\Modules\AbandonCastle\MazeRepository;

require_once 'modules/abandoncastle/src/MapService.php';
require_once 'modules/abandoncastle/src/FightController.php';
require_once 'modules/abandoncastle/src/MazeRepository.php';
        global $session;
        global $badguy;
         $op = (string) httpget('op');
         $locale = (string) httpget('loc');
         $skill = (string) httpget('skill');
        $modeparam = (string) httpget('mode');
        if ($modeparam !== '' && in_array($modeparam, ['hardcore', 'normal'], true)) {
                set_module_pref('mode', $modeparam);
        }
        $session['abandoncastle']['mode'] = get_module_pref('mode');
         if (is_module_active('potions')) {
                set_module_pref('restrict', true, 'potions');
         }
	if (is_module_active('usechow')) {
		set_module_pref('restrict', true, 'usechow');
	}

	$knownmonsters = ['ghost1', 'ghost2', 'bat', 'rat', 'minotaur'];
        if (in_array($op, $knownmonsters, true) || $op === 'fight' || $op === 'run') {
		(new FightController())->dispatch($op);
		die;
	}

        page_header("Abandoned Castle");
        rawoutput("<link href=\"modules/abandoncastle/abandoncastle.css\" rel=\"stylesheet\">");
        if ($session['user']['hitpoints'] <= 0) {
                set_module_pref('mapdata', '');
                redirect("shades.php");
        }

	$umaze = get_module_pref('maze');
	$umazeturn = get_module_pref('mazeturn');
	$upqtemp = get_module_pref('pqtemp');

        $directions = "";
        $navcount = "";
         $mapkey = "";

        $mapService = MapService::fromString(get_module_pref('mapdata'));

        // The entrance only offers these links to eligible players; enforce that here too.
        if ($op === 'enter' && ((get_module_pref('enteredtoday') && !get_module_pref('super')) || $session['user']['dragonkills'] < get_module_setting('dkenter'))) {
                $op = '';
        }
        if ($op === 'enter') {
                output("`2You enter the Abandoned Castle, as you do the door slams behind you.");
                output("Try as you may the door won't budge!  Looks like you are going to have to find ");
                output("another way out of this place!`n");
                output("You look around, and in the dim light you see that the castle's narrow passage is ");
                output("littered with junk and remains of past visitors.`n");
                if ($session['user']['hashorse']>0){
                        global $playermount;
                        output("Too bad your %s couldn't come in with you, now you are all alone.`n",$playermount['mountname']);
                }
                $skill="";
                require_once("lib/battle-skills.php");
                suspend_buffs('allowintrain',"This place feels strange... it's draining any buffs you have ... they are unusable.`n`n");
                $locale=6;
                $umazeturn = 0;
                set_module_pref("mazeturn", 0);
                set_module_pref('mapdata', '');
                $mapService = new MapService();
                if (!isset($maze) || $maze==""){
                        $mazes = MazeRepository::getMazes();
                        $maze = $mazes[array_rand($mazes)];
                        $umaze = implode(",",$maze);
                        set_module_pref("maze", $umaze);
                        if (!get_module_pref('super')){
                        set_module_pref("enteredtoday", true);
                        }
                }
                $op = '';
        }

        if ($op == "" && $locale == "") {
		addnav("Navigation");
                output("`c`b`&Abandoned Castle`0`b`c`n`n");
                if (get_module_pref('enteredtoday')) {
                        output("`2You tug on the door, however you cannot get it to open.`n`2(`3You can only enter the castle once per day`2)`0");
			addnav("Navigation");
                        if (get_module_setting("forestvil") == 1){
                                addnav("Continue","forest.php");
                        }else{
                                villagenav();
                        }
                } elseif ($session['user']['dragonkills'] >= get_module_setting('dkenter')) {
                        output("`2You stand before the Abandoned Castle. Choose your mode before entering.`n");
			output("`n`vHardcore will provide twice the rewards, but you will have no automap... so pick wisely. You cannot leave once you enter...");
			addnav("Navigation");
                        if (get_module_setting("forestvil") == 1){
                                addnav("Back to the Forest","forest.php");
                        }else{
                                villagenav();
                        }
			addnav("Be brave");
                        addnav("Enter the Castle (Hardcore)","runmodule.php?module=abandoncastle&op=enter&mode=hardcore");
                        addnav("Enter the Castle (Normal)","runmodule.php?module=abandoncastle&op=enter&mode=normal");
			
                } else {
			addnav("Navigation");
                        output("You tug on the door, however you cannot get it to open.`n");
                        output("Come back when you are a stronger and more experienced warrior.`n");
                        if (get_module_setting("forestvil") == 1){
                                addnav("Continue","forest.php");
                        }else{
                                villagenav();
                        }
                }
        }

	//now let's navigate the maze
        if ($op === 'checkmap') {
                $maze = explode(',', $umaze);
                if ($session['abandoncastle']['mode'] === 'normal') {
                        $mapService->markVisited((int) $locale);
                        set_module_pref('mapdata', $mapService->serialize());
                }
                output("`c`b`&Explored Map`0`b`c`n");
                output_notl($mapService->render($maze, (int) $locale), true);
                addnav("Return", "runmodule.php?module=abandoncastle&loc=$locale");
                page_footer();
                return;
        }

        if ($op <> ""){
                if ($op == "n") {
                        $locale+=11;
                        redirect("runmodule.php?module=abandoncastle&loc=$locale");
		}
		if ($op == "s"){
			$locale-=11;
			redirect("runmodule.php?module=abandoncastle&loc=$locale");
		}
		if ($op == "w"){
			$locale-=1;
			redirect("runmodule.php?module=abandoncastle&loc=$locale");
		}
		if ($op == "e"){
			$locale+=1;
			redirect("runmodule.php?module=abandoncastle&loc=$locale");
		}

        }else{
                if ($locale <> ""){
                        if ($session['abandoncastle']['mode'] === 'normal') {
                                $mapService->markVisited((int) $locale);
                                set_module_pref('mapdata', $mapService->serialize());
                        }
                        //now deal with random events good stuff first
                        //good stuff diminshes the longer player is in the maze
			//this is big... with lots of cases to help keep options open for future events
			//the lower cases should be good things the best at the lowest number
			//and the opposite for bad things
			$maze=explode(",", $umaze);
			if ($locale=="") $locale = $upqtemp;
			$upqtemp = $locale;
			set_module_pref("pqtemp", $upqtemp);
			for ($i=0;$i<$locale-1;$i++){
			}
			$navigate=ltrim($maze[$i]);
			output("`4");
			if ($navigate <> "z"){
				switch(e_rand($umazeturn,2500)){
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					case 10:
						output("Lucky Day!  You find a Gem!");
						$session['user']['gems']+=1;
						break;
					case 11:
					case 12:
					case 13:
					case 14:
					case 15:
					case 16:
					case 17:
					case 18:
					case 19:
					case 20:
						output("Lucky Day! You find %s gold!",500);
						$session['user']['gold']+=500;
						break;
					case 21:
					case 22:
					case 23:
					case 24:
					case 25:
					case 26:
					case 27:
					case 28:
					case 29:
					case 30:
						output("Lucky Day! You find %s gold!",450);
						$session['user']['gold']+=450;
						break;
					case 31:
					case 32:
					case 33:
					case 34:
					case 35:
					case 36:
					case 37:
					case 38:
					case 39:
					case 40:
						output("Lucky Day! You find %s gold!",400);
						$session['user']['gold']+=400;
						break;
					case 41:
					case 42:
					case 43:
					case 44:
					case 45:
					case 46:
					case 47:
					case 48:
					case 49:
					case 50:
						output("Lucky Day! You find %s gold!",350);
						$session['user']['gold']+=350;
						break;
					case 51:
					case 52:
					case 53:
					case 54:
					case 55:
					case 56:
					case 57:
					case 58:
					case 59:
					case 60:
						output("Lucky Day! You find %s gold!",300);
						$session['user']['gold']+=300;
						break;
					case 61:
					case 62:
					case 63:
					case 64:
					case 65:
					case 66:
					case 67:
					case 68:
					case 69:
					case 70:
						output("Lucky Day! You find %s gold!",250);
						$session['user']['gold']+=250;
						break;
					case 71:
					case 72:
					case 73:
					case 74:
					case 75:
					case 76:
					case 77:
					case 78:
					case 79:
					case 80:
						output("Lucky Day! You find %s gold!",200);
						$session['user']['gold']+ 0;
						break;
					case 81:
					case 82:
					case 83:
					case 84:
					case 85:
					case 86:
					case 87:
					case 88:
					case 89:
					case 90:
						output("Lucky Day! You find %s gold!",150);
						$session['user']['gold']+=150;
						break;
					case 91:
					case 92:
					case 93:
					case 94:
					case 95:
					case 96:
					case 97:
					case 98:
					case 99:
					case 100:
						output("Lucky Day! You find %s gold!",100);
						$session['user']['gold']+=100;
						break;
					case 101:
					case 102:
					case 103:
					case 104:
					case 105:
					case 106:
					case 107:
					case 108:
					case 109:
					case 110:
						output("Lucky Day! You find %s gold!",50);
						$session['user']['gold']+=50;
						break;
					case 111:
					case 112:
					case 113:
					case 114:
					case 115:
					case 116:
					case 117:
					case 118:
					case 119:
					case 120:
						output("Lucky Day! You find %s gold!",25);
						$session['user']['gold']+=25;
						break;
					case 121:
					case 122:
						if (is_module_active('potions')) {
							$upotion = get_module_pref('potion', 'potions');
							if ($upotion<5){
								output("Lucky Day! You find a Healing Potion!");
								set_module_pref('potion', ++$upotion, 'potions');
							}
						}
						break;
					case 123:
					case 124:
						if (is_module_active('usechow')) {
							$uchow  = get_module_pref("chow", "usechow");
							for ($i=0;$i<6;$i+=1){
								$chow[$i]=substr(strval($uchow),$i,1);
								if ($chow[$i] > 0) $userchow++;
							}
							if ($userchow<5){
								switch(e_rand(1,7)){
									case 1:
										output("`^Fortune smiles on you and you find a slice of bread!`0");
										for ($i=0;$i<6;$i+=1){
											$chow[$i]=substr(strval($uchow),$i,1);
											if ($chow[$i]=="0" and $done < 1){
												$chow[$i]="1";
												$done = 1;
											}
											$newchow.=$chow[$i];
										}
										break;
									case 2:
										output("`^Fortune smiles on you and you find a Pork Chop!`0");
										for ($i=0;$i<6;$i+=1){
											$chow[$i]=substr(strval($uchow),$i,1);
											if ($chow[$i]=="0" and $done < 1){
												$chow[$i]="2";
												$done = 1;
											}
											$newchow.=$chow[$i];
										}
										break;
									case 3:
										output("`^Fortune smiles on you and you find a Ham Steak!`0");
										for ($i=0;$i<6;$i+=1){
											$chow[$i]=substr(strval($uchow),$i,1);
											if ($chow[$i]=="0" and $done < 1){
												$chow[$i]="3";
												$done = 1;
											}
											$newchow.=$chow[$i];
										}
										break;
									case 4:
										output("`^Fortune smiles on you and you find a Steak!`0");
										for ($i=0;$i<6;$i+=1){
											$chow[$i]=substr(strval($uchow),$i,1);
											if ($chow[$i]=="0" and $done < 1){
												$chow[$i]="4";
												$done = 1;
											}
											$newchow.=$chow[$i];
										}
										break;
									case 5:
										output("`^Fortune smiles on you and you find a Whole Chicken!`0");
										for ($i=0;$i<6;$i+=1){
											$chow[$i]=substr(strval($uchow),$i,1);
											if ($chow[$i]=="0" and $done < 1){
												$chow[$i]="5";
												$done = 1;
											}
											$newchow.=$chow[$i];
										}
										break;
									case 6:
										output("`^Fortune smiles on you and you find a bottle of milk!`0");
										for ($i=0;$i<6;$i+=1){
											$chow[$i]=substr(strval($uchow),$i,1);
											if ($chow[$i]=="0" and $done < 1){
												$chow[$i]="6";
												$done = 1;
											}
											$newchow.=$chow[$i];
										}
										break;
									case 7:
										output("`^Fortune smiles on you and you find a bottle of Water!`0");
										for ($i=0;$i<6;$i+=1){
											$chow[$i]=substr(strval($uchow),$i,1);
											if ($chow[$i]=="0" and $done < 1){
												$chow[$i]="7";
												$done = 1;
											}
											$newchow.=$chow[$i];
										}
										break;
								}
								set_module_pref('chow', $newchow, 'usechow');
							}
						}
						break;
					case 125:
					case 126:
					case 127:
					case 128:
					case 129:
					case 130:
						output("Lucky Day! You find %s gold!",10);
						$session['user']['gold']+=10;
						break;
					case 131:
					case 132:
					case 133:
					case 134:
					case 135:
					case 136:
					case 137:
					case 138:
					case 139:
					case 140:
						if (is_module_active('lonnycastle')){
						output("You find ");
						set_module_pref('evil',(get_module_pref('evil','lonnycastle') - 1),'lonnycastle');
						find();
						}
						break;

				case 2321:
				case 2322:
				case 2323:
				case 2324:
				case 2325:
				case 2326:
				case 2327:
				case 2328:
				case 2329:
				case 2330:
					output("You hear a strange and eerie growling sound coming from somewhere.");
					break;
				case 2331:
				case 2332:
				case 2333:
				case 2334:
				case 2335:
				case 2336:
				case 2337:
				case 2338:
				case 2339:
				case 2340:
					output("You hear a blood curling scream coming from somewhere.");
					break;
				case 2341:
				case 2342:
				case 2343:
				case 2344:
				case 2345:
				case 2346:
				case 2347:
				case 2348:
				case 2349:
				case 2350:
					output("You encounter a putrid smell.  ");
					if (is_module_active('odor')){
					output("Some of that smell lingers with you.");
					set_module_pref('odor',(get_module_pref('odor','odor') - 1),'odor');
					}
					break;
				case 2351:
				case 2352:
				case 2353:
				case 2354:
				case 2355:
				case 2356:
				case 2357:
				case 2358:
				case 2359:
				case 2360:
					output("There is a skeleton laying in the corner.  Poor fellow didn't find his way out.");
					break;
				case 2361:
				case 2362:
				case 2363:
				case 2364:
				case 2365:
				case 2366:
				case 2367:
				case 2368:
				case 2369:
				case 2370:
					output("You see a rat chewing on what looks like a hand.");
					break;
				case 2371:
				case 2372:
				case 2373:
				case 2374:
				case 2375:
				case 2376:
				case 2377:
				case 2378:
				case 2379:
				case 2380:
					output("You hear a growl from somewhere very close.");
					break;
				case 2381:
				case 2382:
				case 2383:
				case 2384:
				case 2385:
				case 2386:
				case 2387:
				case 2388:
				case 2389:
				case 2390:
					output("A cold chill comes over you.");
					break;
				case 2391:
				case 2392:
				case 2393:
				case 2394:
				case 2395:
				case 2396:
				case 2397:
				case 2398:
				case 2399:
				case 2400:
					output("You hear screams for help coming from somewhere.");
					break;
				case 2401:
				case 2402:
				case 2403:
				case 2404:
				case 2405:
				case 2406:
				case 2407:
				case 2408:
				case 2409:
				case 2410:
					output("You hear screams for help coming from somewhere close.");
					break;
				case 2411:
				case 2412:
				case 2413:
				case 2414:
				case 2415:
				case 2416:
				case 2417:
				case 2418:
				case 2419:
				case 2420:
					output("You hear screams for help coming from somewhere.  Abruptly the Screaming Stops.");
					break;
				case 2421:
				case 2422:
				case 2423:
				case 2424:
				case 2425:
				case 2426:
				case 2427:
				case 2428:
				case 2429:
				case 2430:
					output("Ouch! You stepped on something sharp!");
					$session['user']['hitpoints']-=1;
					if ($session['user']['hitpoints']<1) $session['user']['hitpoints']=1;
					break;
				case 2431:
				case 2432:
				case 2433:
				case 2434:
				case 2435:
				case 2436:
				case 2437:
				case 2438:
				case 2439:
				case 2440:
					output("Ouch! You were bit by a spider");
					$session['user']['hitpoints']-=2;
					if ($session['user']['hitpoints']<1) $session['user']['hitpoints']=1;
					break;
				case 2441:
				case 2442:
				case 2443:
				case 2444:
				case 2445:
				case 2446:
				case 2447:
				case 2448:
				case 2449:
				case 2450:
					output("Ouch! You were bit by a rat.  The rat scurries away.");
					$session['user']['hitpoints']-=3;
					if ($session['user']['hitpoints']<1) $session['user']['hitpoints']=1;
					break;
				case 2451:
				case 2452:
				case 2453:
				case 2454:
				case 2455:
				case 2456:
				case 2457:
				case 2458:
				case 2459:
				case 2460:
					output("Ouch! You were bit by a big rat.  The rat scurries away.");
					$session['user']['hitpoints']-=4;
					if ($session['user']['hitpoints']<1) $session['user']['hitpoints']=1;
					break;
				case 2461:
				case 2462:
				case 2463:
                                        output("<span class='ac-text-large'>`4Wham!</span>`n", true);
					output("`3As the world goes dim... you see that large spikes have erupted from the floor and impaled you.`n");
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(" - Killed by spikes in the abandonded castle");
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews("`% %s `5 went into the Abandoned Castle and never came out.",$session['user']['name']);
					break;
				case 2464:
				case 2465:
				case 2466:
				case 2467:
				case 2468:
				case 2469:
				case 2470:
				case 2471:
					redirect("runmodule.php?module=abandoncastle&op=ghost1");
					break;
				case 2472:
				case 2473:
				case 2474:
				case 2475:
				case 2476:
				case 2477:
				case 2478:
				case 2479:
					redirect("runmodule.php?module=abandoncastle&op=ghost2");
					break;
				case 2480:
				case 2481:
				case 2482:
				case 2483:
				case 2484:
				case 2485:
				case 2486:
					redirect("runmodule.php?module=abandoncastle&op=bat");
					break;
				case 2487:
				case 2488:
				case 2489:
				case 2490:
				case 2491:
				case 2493:
				case 2494:
					redirect("runmodule.php?module=abandoncastle&op=rat");
					break;
				case 2495:
				case 2496:
					redirect("runmodule.php?module=abandoncastle&op=minotaur");
					break;
				case 2497:
				case 2498:
                                        output("<span class='ac-text-large'>`4Wham!</span>`n", true);
					output("`3As the world goes dim... you see that large spikes have erupted from the wall and impaled you.`n");
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(" - Killed by spikes in the abandonded castle");
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews("`% %s `5 went into the Abandoned Castle and never came out.",$session['user']['name']);
					break;
				case 2499:
				case 2500:
                                        output("<span class='ac-text-large'>`4Shoop!</span>`n", true);
					output("`3As the world goes dim... you see your body fall to the floor level where your head is laying.`n");
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(" - Beheaded in the abandonded castle");
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews("`% %s `5 went into the Abandoned Castle and never came out.",$session['user']['name']);
					break;
				}
			}
			output("`7");
			if ($navigate<>"z"){
				if ($navigate=="x"){
					output("You fell off of the end of the world!");
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(" - Died in the abandonded castle");
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews("`% %s `5 went into the Abandoned Castle and never came out.",$session['user']['name']);
				}
				if ($navigate=="p"){
					output("You fall into a pit filled with spikes, you see the dim light way above you fade, just as your life is fading.`n");
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(" - Killed by spikes in the abandonded castle");
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews("`% %s `5 went into the Abandoned Castle and never came out.",$session['user']['name']);
				}
				if ($navigate=="q"){
					output("You step on something on the floor you feel it shift and hear the rush of water.");
					output("The passage quickly fills with water, the world fades as your lungs burn for air.`n");
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(" - Drowned in the abandonded castle");
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews("`% %s `5 went into the Abandoned Castle and never came out.",$session['user']['name']);
				}
				if ($navigate=="r"){
					output("You hear a slam come from behind you, when you turn around you see that a door has blocked you into ");
					output("a small section of passageway.  The walls start to rumble and close in on you.  Soon you find out what ");
					output("it is like for a bug under your foot.");
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(" - Squished in the abandonded castle");
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews("`% %s `5 went into the Abandoned Castle and never came out.",$session['user']['name']);
				}
				if ($navigate=="s"){
					output("Out of nowhere a blade swings horizontally across your path.");
					output("The world goes dim as the top half of your body slides away from the bottom.`n");
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(" - Sliced in half in the abandonded castle");
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews("`% %s `5 went into the Abandoned Castle and never came out.",$session['user']['name']);
				}
				if ($session['user']['hitpoints'] > 0){
					if (get_module_pref('mode') == 'hardcore') {
						output("`b`\$Hardcore Mode`0`b`n");
					}
					addnav("Directions");
					if ($locale=="6"){
						output("`nYou are at the entrance with Passages to the");
					}else{
						output("`nYou are in a Dark Corridor with Passages to the");
					}
					$umazeturn++;
					set_module_pref('mazeturn', $umazeturn);
                                        if ($navigate=="a" or $navigate=="b" or $navigate=="e" or $navigate=="f" or $navigate=="g" or $navigate=="j" or $navigate=="k" or $navigate=="l"){
                                                addnav("North","runmodule.php?module=abandoncastle&op=n&loc=$locale");
                                                $directions.=" ".translate_inline("North");
                                                $navcount++;
                                        }
                                        if ($navigate=="a" or $navigate=="c" or $navigate=="e" or $navigate=="f" or $navigate=="g" or $navigate=="h" or $navigate=="i" or $navigate=="m"){
                                                if ($locale <> 6){
                                                        addnav("South","runmodule.php?module=abandoncastle&op=s&loc=$locale");
                                                        $navcount++;
                                                        if ($navcount > 1) $directions.=",";
                                                        $directions.=" ".translate_inline("South");
                                                }
                                        }
                                        if ($navigate=="a" or $navigate=="b" or $navigate=="c" or $navigate=="d" or $navigate=="e" or $navigate=="h" or $navigate=="k" or $navigate=="n"){
                                                addnav("West","runmodule.php?module=abandoncastle&op=w&loc=$locale");
                                                $navcount++;
                                                if ($navcount > 1) $directions.=",";
                                                $directions.=" ".translate_inline("West");
                                        }
                                        if ($navigate=="a" or $navigate=="b" or $navigate=="c" or $navigate=="d" or $navigate=="f" or $navigate=="i" or $navigate=="j" or $navigate=="o"){
                                                addnav("East","runmodule.php?module=abandoncastle&op=e&loc=$locale");
                                                $navcount++;
                                                if ($navcount > 1) $directions.=",";
                                                $directions.=" ".translate_inline("East");
                                        }
					if (get_module_pref('mode') == 'normal') {
						addnav("Other");
						addnav("Check Map","runmodule.php?module=abandoncastle&op=checkmap&loc=$locale");
					}
                                        output_notl(" %s.`n",$directions);
                                }else{
                                        set_module_pref('mapdata', '');
                                        addnav("Continue","shades.php");
                                }
                                if ($navigate === MazeRepository::START_TILE) {
                                        $mazemap = 'mgreen.gif';
                                } else {
                                        $mazemap = $navigate . 'maze.gif';
                                }
                                output_notl("<img src=\"./modules/abandoncastle/images/%s\" alt=\"Maze map\">\n", $mazemap, true);
                                output_notl("`n");
                                $legend = "`n";
                                $legend .= "<span class='ac-text-small'>`7" . translate_inline("You") . " = </span><img src=\"./modules/abandoncastle/images/mcyan.gif\" alt=\"" . translate_inline("You") . "\" style=\"width: 5px; height: 5px;\">`7, ";
                                $legend .= "<span class='ac-text-small'>" . translate_inline("Entrance") . " = </span><img src=\"./modules/abandoncastle/images/mgreen.gif\" alt=\"" . translate_inline("Entrance") . "\" style=\"width: 5px; height: 5px;\">`7, ";
                                $legend .= "<span class='ac-text-small'>" . translate_inline("Exit") . " = </span><img src=\"./modules/abandoncastle/images/mred.gif\" alt=\"" . translate_inline("Exit") . "\" style=\"width: 5px; height: 5px;\">";
                                output_notl($legend, true);
                                $mapkey2="<table style=\"height: 130px; width: 110px; text-align: left;\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody><tr><td style=\"vertical-align: top;\">";
                                for ($i=0;$i<143;$i++){
                                        if ($i == $locale - 1) {
                                                $mapkey .= "<img src=\"./modules/abandoncastle/images/mcyan.gif\" alt=\"" . translate_inline("You") . "\" style=\"width: 10px; height: 10px;\">";
                                        } else {
                                                if ($i == 5) {
                                                        $mapkey .= "<img src=\"./modules/abandoncastle/images/mgreen.gif\" alt=\"" . translate_inline("Entrance") . "\" style=\"width: 10px; height: 10px;\">";
                                                } else {
                                                        if (ltrim($maze[$i]) == "z") {
                                                                $exit = $i + 1;
                                                                $mapkey .= "<img src=\"./modules/abandoncastle/images/mred.gif\" alt=\"" . translate_inline("Exit") . "\" style=\"width: 10px; height: 10px;\">";
                                                        } else {
                                                                $mapkey .= "<img src=\"./modules/abandoncastle/images/mblack.gif\" alt=\"" . translate_inline("Path") . "\" style=\"width: 10px; height: 10px;\">";
                                                        }
                                                }
                                        }
					if ($i==10 or $i==21 or $i==32 or $i==43 or $i==54 or $i==65 or $i==76 or $i==87 or $i==98 or $i==109 or $i==120 or $i==131 or $i==142){
						$mapkey="`n".$mapkey;
						$mapkey2=$mapkey.$mapkey2;
						$mapkey="";
					}
				}
				$mapkey2.="</td></tr></tbody></table>";
				output_notl("%s",$mapkey2,true);
				if (get_module_pref('super')){
					output("Superuser Map`n");
					$mapkey2="";
					$mapkey="";
					for ($i=0;$i<143;$i++){
						$keymap=ltrim($maze[$i]);
						$mazemap=$keymap;
						$mazemap.="maze.gif";
                                                $mapkey .= "<img src=\"./modules/abandoncastle/images/$mazemap\" alt=\"" . translate_inline("Map tile") . "\" style=\"width: 20px; height: 20px;\">";
						if ($i==10 or $i==21 or $i==32 or $i==43 or $i==54 or $i==65 or $i==76 or $i==87 or $i==98 or $i==109 or $i==120 or $i==131 or $i==142){
							$mapkey="`n".$mapkey;
							$mapkey2=$mapkey.$mapkey2;
							$mapkey="";
						}
					}
					output_notl("%s",$mapkey2,true);
				}
				if (get_module_pref('super')) addnav("Superuser Exit","runmodule.php?module=abandoncastle&loc=$exit");
			}else{
				if ($session['user']['hashorse']>0){
					global $playermount;
					output("Your %s happily greets you at the exit.`n",$playermount['mountname']);
				}
				output("You have found your way out!`n");
				require_once("lib/battle-skills.php");
				unsuspend_buffs('allowintrain',"Leaving this place you feel your buffs coming back to you.`n`n");
				addnews("`% %s `5 made it out of the Abandoned Castle alive! In %s moves!",$session['user']['name'],$umazeturn);
				$reward = 1000 - ($umazeturn*10);
				if ($reward < 0) $reward = 0;
                                $gemreward = 0;
                                if ($umazeturn < 101) $gemreward = 1;
                                if ($umazeturn < 76) $gemreward = 2;
                                if ($umazeturn < 51) $gemreward = 3;
                                if ($umazeturn < 26) $gemreward = 4;
                                $mode = $session['abandoncastle']['mode'];
                                if ($mode === 'hardcore') {
                                        $reward *= 2;
                                        $gemreward *= 2;
                                }
                                output("`2You finished the maze in %s turns.`n",$umazeturn);
                                output("`2You find a treasure of %s gold and %s gems.`n`n",$reward,$gemreward);
                                if (get_module_setting("forestvil") == 1){
                                        addnav("Continue","forest.php");
                                }else{
					villagenav();
				}
				$session['user']['gold']+=$reward;
				$session['user']['gems']+=$gemreward;
                                set_module_pref('maze',"");
                                set_module_pref('mazeturn', 0);
                                set_module_pref('pqtemp',"");
                                set_module_pref('mapdata', '');
                        }
                }
        }
	//I cannot make you keep this line here but would appreciate it left in.
	// (NB) I will, but your pqcomp.com is expired / non-working, so I remove the direct link for sanity reasons
	rawoutput("<p style='font-size: 0.6em'>");
	output("`n`n`mAbandonded Castle originally by Lonny (pqcomp.com)");
	rawoutput("</p>");

	page_footer();
?>
