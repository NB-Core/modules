<?php
use Shinobi\Modules\AbandonCastle\MapService;
use Shinobi\Modules\AbandonCastle\FightController;
use Shinobi\Modules\AbandonCastle\MazeRepository;
use Shinobi\Modules\AbandonCastle\Tiles;
use Shinobi\Modules\AbandonCastle\Narration;
use Shinobi\Modules\AbandonCastle\Scenery;
use Shinobi\Modules\AbandonCastle\Labyrinth;
use Shinobi\Modules\AbandonCastle\Bones;
use Shinobi\Modules\AbandonCastle\Choices;

require_once 'modules/abandoncastle/src/MapService.php';
require_once 'modules/abandoncastle/src/FightController.php';
require_once 'modules/abandoncastle/src/MazeRepository.php';
require_once 'modules/abandoncastle/src/Tiles.php';
require_once 'modules/abandoncastle/src/Narration.php';
require_once 'modules/abandoncastle/src/Scenery.php';
require_once 'modules/abandoncastle/src/Labyrinth.php';
require_once 'modules/abandoncastle/src/Bones.php';
require_once 'modules/abandoncastle/src/Choices.php';
        global $session;
        global $badguy;
         $op = (string) httpget('op');
         $locale = (string) httpget('loc');
         $skill = (string) httpget('skill');
        $modeparam = (string) httpget('mode');
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
                // The mode is chosen at the door and fixed for the whole run; a mode in any other link is ignored.
                if (in_array($modeparam, ['hardcore', 'normal', 'extreme'], true)) {
                        set_module_pref('mode', $modeparam);
                        $session['abandoncastle']['mode'] = $modeparam;
                }
                output(Narration::ENTER);
                if ($session['user']['hashorse']>0){
                        global $playermount;
                        output(Narration::MOUNT_LEFT, $playermount['mountname']);
                }
                $skill="";
                require_once("lib/battle-skills.php");
                suspend_buffs('allowintrain', Narration::BUFFS_SUSPENDED);
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
                        set_module_pref('minotaur', Labyrinth::hunterStart(array_values($maze), e_rand(0, 9999)));
                        set_module_pref('choice', '');
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
                        output(Narration::ENTERED_TODAY);
			addnav("Navigation");
                        if (get_module_setting("forestvil") == 1){
                                addnav("Continue","forest.php");
                        }else{
                                villagenav();
                        }
                } elseif ($session['user']['dragonkills'] >= get_module_setting('dkenter')) {
                        output(Narration::BEFORE_THE_DOOR);
			output(Narration::MODES);
			addnav("Navigation");
                        if (get_module_setting("forestvil") == 1){
                                addnav("Back to the Forest","forest.php");
                        }else{
                                villagenav();
                        }
			addnav("Be brave");
                        addnav("Enter the Castle (Hardcore)","runmodule.php?module=abandoncastle&op=enter&mode=hardcore");
                        addnav("Enter the Castle (Normal)","runmodule.php?module=abandoncastle&op=enter&mode=normal");
                        addnav("Enter the Castle (Extra Hardcore)","runmodule.php?module=abandoncastle&op=enter&mode=extreme");
			
                } else {
			addnav("Navigation");
                        output(Narration::TOO_WEAK);
                        if (get_module_setting("forestvil") == 1){
                                addnav("Continue","forest.php");
                        }else{
                                villagenav();
                        }
                }
        }

	//now let's navigate the maze
        // The map is part of every room now; an old Check Map link leads back to the room.
        if ($op === 'checkmap') {
                redirect("runmodule.php?module=abandoncastle&loc=" . (int) $locale);
        }

        // Normal mode only: give up and climb out, without a reward and without the rest of today's forest fights.
        // First a second screen that asks whether the player really wants to give up.
        if ($op === 'rope' && httpget('confirm') !== '1'
                && $session['abandoncastle']['mode'] === MapService::MODE_NORMAL && $umaze !== '') {
                // Back to the room the player stands in; the saved room covers a link without one.
                $back = (int) ($locale !== '' ? $locale : $upqtemp);
                if ($back < 1 || $back > Tiles::WIDTH * Tiles::HEIGHT) {
                        $back = Tiles::ENTRANCE_INDEX + 1;
                }
                output(Narration::ROPE_CONFIRM);
                addnav("Give up?");
                addnav(Narration::ROPE_CONFIRM_YES, "runmodule.php?module=abandoncastle&op=rope&confirm=1&loc=$back");
                addnav(Narration::ROPE_CONFIRM_NO, "runmodule.php?module=abandoncastle&loc=$back");
                page_footer();
                return;
        }
        if ($op === 'rope') {
                if ($session['abandoncastle']['mode'] === MapService::MODE_NORMAL && $umaze !== '') {
                        if ($session['user']['hashorse'] > 0) {
                                global $playermount;
                                output(Narration::MOUNT_AT_EXIT, $playermount['mountname']);
                        }
                        output(Narration::ROPE_ESCAPE);
                        require_once("lib/battle-skills.php");
                        unsuspend_buffs('allowintrain', Narration::BUFFS_RETURN);
                        addnews(Narration::NEWS_ROPE, $session['user']['name']);
                        $session['user']['turns'] = 0;
                        set_module_pref('maze', "");
                        set_module_pref('minotaur', Labyrinth::NO_HUNTER);
                        set_module_pref('choice', '');
                        set_module_pref('mazeturn', 0);
                        set_module_pref('pqtemp', "");
                        set_module_pref('mapdata', '');
                        if (get_module_setting("forestvil") == 1) {
                                addnav("Continue", "forest.php");
                        } else {
                                villagenav();
                        }
                        page_footer();
                        return;
                }
                $op = '';
        }

        // The answer to a choice the castle offered in this room.
        $quietVisit = false;
        if ($op === 'choice') {
                $quietVisit = Choices::resolve((string) httpget('pick'), (int) $locale, explode(",", $umaze));
                $op = '';
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
			$index = (int) $locale - 1;
			$mazeKey = Labyrinth::key($maze);
			$hunter = get_module_pref('minotaur');
			$hunter = $hunter === '' ? Labyrinth::NO_HUNTER : (int) $hunter;
			if (Tiles::isPassage($navigate)) {
				// What the room looks like: a landmark, light, smell, a detail, and what you hear right now.
				$scenery = array_map('translate_inline', Labyrinth::scenery($maze, $index, Labyrinth::sound($index, e_rand(0, 7))));
				output_notl("%s`n`n", implode(' ', $scenery));
				foreach (Bones::in($mazeKey, $index) as $bone) {
					output(Scenery::BONES, $bone['n'], translate_inline(Scenery::BONES_HOW[$bone['h']] ?? Scenery::BONES_HOW['monster']));
					output_notl("`n`n");
				}
				// The Minotaur hunts: walking into it, or it into you, means a fight.
				if (!$quietVisit && $hunter >= 0) {
					// It is slower than you: about one move in three it stands and listens instead.
					if ($hunter !== $index && e_rand(0, 2) > 0) {
						$hunter = Labyrinth::hunterStep($maze, $hunter, $index, e_rand(0, 99), e_rand(0, 99));
						set_module_pref('minotaur', $hunter);
					}
					if ($hunter === $index) {
						$umazeturn++;
						set_module_pref('mazeturn', $umazeturn);
						redirect("runmodule.php?module=abandoncastle&op=minotaur");
					}
				}
			}
			$roll = $quietVisit ? 0 : e_rand($umazeturn, 2500);
			if ($navigate <> "z" && $roll >= 1001 && $roll <= 1100 && Tiles::isPassage($navigate)) {
				// A choice instead of something simply happening.
				Choices::offer(Choices::KINDS[intdiv($roll - 1001, 25)], (int) $locale);
			} elseif ($navigate <> "z" && !$quietVisit){
				switch($roll){
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
						output(Narration::pick(Narration::GEM));
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
						output(Narration::pick(Narration::GOLD), 500);
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
						output(Narration::pick(Narration::GOLD), 450);
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
						output(Narration::pick(Narration::GOLD), 400);
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
						output(Narration::pick(Narration::GOLD), 350);
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
						output(Narration::pick(Narration::GOLD), 300);
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
						output(Narration::pick(Narration::GOLD), 250);
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
						output(Narration::pick(Narration::GOLD), 200);
						$session['user']['gold']+=200;
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
						output(Narration::pick(Narration::GOLD), 150);
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
						output(Narration::pick(Narration::GOLD), 100);
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
						output(Narration::pick(Narration::GOLD), 50);
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
						output(Narration::pick(Narration::GOLD), 25);
						$session['user']['gold']+=25;
						break;
					case 121:
					case 122:
						if (is_module_active('potions')) {
							$upotion = get_module_pref('potion', 'potions');
							if ($upotion<5){
								output(Narration::POTION);
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
										output(Narration::FOOD, translate_inline("a slice of bread"));
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
										output(Narration::FOOD, translate_inline("a Pork Chop"));
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
										output(Narration::FOOD, translate_inline("a Ham Steak"));
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
										output(Narration::FOOD, translate_inline("a Steak"));
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
										output(Narration::FOOD, translate_inline("a Whole Chicken"));
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
										output(Narration::FOOD, translate_inline("a bottle of milk"));
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
										output(Narration::FOOD, translate_inline("a bottle of Water"));
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
						output(Narration::pick(Narration::GOLD), 10);
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
					output(Narration::GROWL_FAR);
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
					output(Narration::SCREAM);
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
					output(Narration::SMELL);
					if (is_module_active('odor')){
					output(Narration::SMELL_LINGERS);
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
					output(Narration::SKELETON);
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
					output(Narration::RAT_HAND);
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
					output(Narration::GROWL_NEAR);
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
					output(Narration::CHILL);
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
					output(Narration::HELP_FAR);
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
					output(Narration::HELP_NEAR);
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
					output(Narration::HELP_STOPS);
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
					output(Narration::HURT_SHARP);
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
					output(Narration::HURT_SPIDER);
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
					output(Narration::HURT_RAT);
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
					output(Narration::HURT_BIG_RAT);
					$session['user']['hitpoints']-=4;
					if ($session['user']['hitpoints']<1) $session['user']['hitpoints']=1;
					break;
				case 2461:
				case 2462:
				case 2463:
                                        output(Narration::WHAM, true);
					output(Narration::SPIKES_FLOOR);
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					Bones::record($mazeKey, $index, $session['user']['name'], 'spikes');
					if (is_module_active("morgue")){
						$diedhow = translate_inline(Narration::MORGUE['spikes']);
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews(Narration::NEWS_LOST, $session['user']['name']);
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
					// The hunter catches your scent and comes running; if it is dead, only an echo remains.
					$adjacent = array_values(array_filter(Labyrinth::neighbours($maze, $index), static fn (int $next): bool => Tiles::isPassage(ltrim($maze[$next]))));
					if ($hunter >= 0 && $adjacent !== []) {
						$hunter = $adjacent[e_rand(0, count($adjacent) - 1)];
						set_module_pref('minotaur', $hunter);
						output(Scenery::HUNT_SCENT);
					} else {
						output(Narration::GROWL_FAR);
					}
					break;
				case 2497:
				case 2498:
                                        output(Narration::WHAM, true);
					output(Narration::SPIKES_WALL);
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					Bones::record($mazeKey, $index, $session['user']['name'], 'spikes');
					if (is_module_active("morgue")){
						$diedhow = translate_inline(Narration::MORGUE['spikes']);
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews(Narration::NEWS_LOST, $session['user']['name']);
					break;
				case 2499:
				case 2500:
                                        output(Narration::SHOOP, true);
					output(Narration::BEHEADED);
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					Bones::record($mazeKey, $index, $session['user']['name'], 'beheaded');
					if (is_module_active("morgue")){
						$diedhow = translate_inline(Narration::MORGUE['beheaded']);
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews(Narration::NEWS_LOST, $session['user']['name']);
					break;
				}
			}
			if (!$quietVisit && $hunter >= 0 && Tiles::isPassage($navigate) && $session['user']['hitpoints'] > 0) {
				$bearing = Labyrinth::hunterBearing($maze, $hunter, $index);
				if ($bearing !== null) {
					[$distance, $way] = $bearing;
					$wayName = translate_inline(['N' => 'north', 'S' => 'south', 'W' => 'west', 'E' => 'east'][$way] ?? 'dark');
					output_notl("`n");
					if ($distance === 1) {
						output(Narration::pick(Scenery::HUNT_ADJACENT), $wayName);
					} elseif ($distance <= 3) {
						output(Narration::pick(Scenery::HUNT_NEAR), $wayName);
					} elseif ($distance <= 6 && e_rand(0, 1)) {
						output(Narration::pick(Scenery::HUNT_FAR));
					}
				}
			}
			output("`7");
			if ($navigate<>"z"){
				if ($navigate=="x"){
					output(Narration::TRAP_EDGE);
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(Narration::MORGUE['edge']);
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews(Narration::NEWS_LOST, $session['user']['name']);
				}
				if ($navigate=="p"){
					output(Narration::TRAP_PIT);
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(Narration::MORGUE['spikes']);
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews(Narration::NEWS_LOST, $session['user']['name']);
				}
				if ($navigate=="q"){
					output(Narration::TRAP_WATER);
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(Narration::MORGUE['drowned']);
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews(Narration::NEWS_LOST, $session['user']['name']);
				}
				if ($navigate=="r"){
					output(Narration::TRAP_WALLS);
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(Narration::MORGUE['crushed']);
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews(Narration::NEWS_LOST, $session['user']['name']);
				}
				if ($navigate=="s"){
					output(Narration::TRAP_BLADE);
					$session['user']['hitpoints']=0;
					$session['user']['alive']=false;
					if (is_module_active("morgue")){
						$diedhow = translate_inline(Narration::MORGUE['sliced']);
						set_module_pref("died",$diedhow,"morgue");
						set_module_pref("killdate",date("Y-m-D h:m:s"),"morgue");
					}
					$session['user']['gold']=0;
					addnews(Narration::NEWS_LOST, $session['user']['name']);
				}
				$mode = (string) $session['abandoncastle']['mode'];
				$index = (int) $locale - 1;
				if ($session['user']['hitpoints'] > 0){
					if ($mode === MapService::MODE_HARDCORE) {
						output(Narration::MODE_HARDCORE);
					} elseif ($mode === MapService::MODE_EXTREME) {
						output(Narration::MODE_EXTREME);
					}
					$umazeturn++;
					set_module_pref('mazeturn', $umazeturn);
					addnav("Directions");
					$names = ['N' => 'North', 'S' => 'South', 'W' => 'West', 'E' => 'East'];
					$steps = ['N' => 'n', 'S' => 's', 'W' => 'w', 'E' => 'e'];
					$exits = Tiles::exits($navigate, $index);
					$directions = [];
					foreach (str_split($exits) as $direction) {
						addnav($names[$direction], "runmodule.php?module=abandoncastle&op={$steps[$direction]}&loc=$locale");
						$directions[] = translate_inline($names[$direction]);
					}
					if ($mode === MapService::MODE_NORMAL) {
						addnav("Give up");
						addnav(Narration::ROPE_NAV, "runmodule.php?module=abandoncastle&op=rope&loc=$locale");
					}
					$roomText = translate_inline(Labyrinth::kindSentence($maze, $index, Tiles::kind($navigate))) . ' '
						. ($directions === []
							? translate_inline("There is no way on.")
							: sprintf(
								translate_inline("You can go %s."),
								count($directions) > 1
									? implode(', ', array_slice($directions, 0, -1)) . ' ' . translate_inline('or') . ' ' . end($directions)
									: $directions[0]
							));
					rawoutput("<div class='ac-view'>" . MapService::renderRoom($navigate, $index)
						. "<p class='ac-room-text'>" . htmlspecialchars($roomText, ENT_QUOTES, 'UTF-8') . "</p>"
						. $mapService->render($maze, (int) $locale, $mode, false, array_map(static fn (int $l): string => Scenery::LANDMARKS[$l][0], Labyrinth::landmarks($maze)))
						. "</div>");
				}else{
					set_module_pref('mapdata', '');
					addnav("Continue","shades.php");
					rawoutput("<div class='ac-view'>" . MapService::renderRoom($navigate, $index) . "</div>");
				}
				if (get_module_pref('super')){
					output("`nSuperuser Map`n");
					output_notl("Minotaur: %s`n", $hunter >= 0 ? MapService::coordinateName($hunter) : '-');
					rawoutput($mapService->render($maze, (int) $locale, MapService::MODE_NORMAL, true));
					$exit = array_search(Tiles::EXIT, array_map('ltrim', $maze), true);
					if ($exit !== false) {
						addnav("Superuser Exit", "runmodule.php?module=abandoncastle&loc=" . ($exit + 1));
					}
				}
			}else{
				if ($session['user']['hashorse']>0){
					global $playermount;
					output(Narration::MOUNT_AT_EXIT, $playermount['mountname']);
				}
				output(Narration::ESCAPED);
				require_once("lib/battle-skills.php");
				unsuspend_buffs('allowintrain', Narration::BUFFS_RETURN);
				addnews(Narration::NEWS_ESCAPED, $session['user']['name'], $umazeturn);
				$reward = 1000 - ($umazeturn*10);
				if ($reward < 0) $reward = 0;
                                $gemreward = 0;
                                if ($umazeturn < 101) $gemreward = 1;
                                if ($umazeturn < 76) $gemreward = 2;
                                if ($umazeturn < 51) $gemreward = 3;
                                if ($umazeturn < 26) $gemreward = 4;
                                $mode = $session['abandoncastle']['mode'];
                                $multiplier = ['hardcore' => 2, 'extreme' => 3][$mode] ?? 1;
                                $reward *= $multiplier;
                                $gemreward *= $multiplier;
                                output(Narration::FINISHED, $umazeturn);
                                output(Narration::TREASURE, $reward, $gemreward);
                                if (get_module_setting("forestvil") == 1){
                                        addnav("Continue","forest.php");
                                }else{
					villagenav();
				}
				$session['user']['gold']+=$reward;
				$session['user']['gems']+=$gemreward;
                                set_module_pref('maze',"");
                                set_module_pref('minotaur', Labyrinth::NO_HUNTER);
                                set_module_pref('choice', '');
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
