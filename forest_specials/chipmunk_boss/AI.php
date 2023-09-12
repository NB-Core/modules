<?php

//Kabuto
global $session,$badguy;debug($badguy);

if (!isset($badguy['turns']) || !$badguy['turns']) {
	$badguy['turns']=1;
	$badguy['maxhp']=$badguy['creaturehealth'];
	$badguy['prevhp']=$badguy['creaturehealth'];
	$badguy['rage']=1;
}else{
	$badguy['turns']+=1;
	$badguy['rage']=ceil(min(max($badguy['prevhp']-$badguy['creaturehealth'],1)/$badguy['prevhp'],1)*10);
	$badguy['prevhp']=$badguy['creaturehealth'];
}

//Rage Message!
$rage_msg=array(
			1=>"`qThe `gC`lhipmunk`q Boss is bored.`n",
			2=>"`qThe `gC`lhipmunk`q Boss is mildly annoyed.`n",
			3=>"`qThe `gC`lhipmunk`q Boss is bothered by your persistence.`n",
			4=>"`qThe `gC`lhipmunk`q Boss's eye starts twitching with anger.`n",
			5=>"`qThe `gC`lhipmunk`q Boss's mouth is foaming in rage!`n",
			6=>"`qThe `gC`lhipmunk`q Boss begins thrashing around in hatred!`n",
			7=>"`qThe `gC`lhipmunk`q Boss's bloodshot eyes narrow in on you.`n",
			8=>"`qThe `gC`lhipmunk`q Boss howls a deep and tremble sound that peices the soul.`n",
			9=>"`qThe `gC`lhipmunk`q Boss causes the ground to rend apart with his fury!`n",
			10=>"`qThe `gC`lhipmunk`q Boss unleashes his full wrath upon you!`n",
			);
output($rage_msg[$badguy['rage']]);

$chosen=$badguy['turns'];

while ($chosen>0) {
	debug("Looping ".$chosen);
	$rand=e_rand(0,5);
	debug($rand);
	switch ($rand) {
		case 5:
			//lucky, nothing done
			output("`qThe `gC`lhipmunk`q Boss just stares you down.");
			$chosen-=1;
			break;
		case 4:
			//Recover health			
			if ($badguy['creaturehealth']<$badguy['maxhp']){
				$heal=ceil(e_rand(50,100)*(1+round($badguy['rage']/8,1)));
				if (($badguy['creaturehealth']+$heal)>$badguy['maxhp']) $badguy['creaturehealth']=$badguy['maxhp'];
				else $badguy['creaturehealth']+=$heal;				
				output("`qThe `gC`lhipmunk`q Boss eats a `!Blue `qacorn, which recovers his health!`n");
			} else output("`qThe `gC`lhipmunk`q Boss mock your inability to hurt him.");
			$chosen-=1;
			break;
		case 3:
			//RAGE!!!
			$badguy['creatureattack']+=1+(round($badguy['rage']/2,1));
			$badguy['creaturedefense']+=1+(round($badguy['rage']/2,1));
			output("`qThe `gC`lhipmunk`q Boss howls in rage, has his strength increases drastically!!`n");
			$chosen-=1;
			break;
		case 2: 
			//Followers pelt with acorns
			output("`qThe `gC`lhipmunk`q Boss calls on his many minions, to attack you with acorns!`n");
			$pelts=e_rand(2,5)+(round($badguy['rage']/2));
			while ($pelts>0) {
				$damage=e_rand(round($session['user']['level']/2),$session['user']['level']);
				output("`qA `gC`lhipmunk`q plet you for `t%s`q points of damage!`n",$damage);
				$session['user']['hitpoints']-=$damage;
				$pelts-=1;
			}
			$chosen-=1;
			break;
		case 1:
			//Tickling Debuff!
			output("`qA large number of small `gC`lhipmunks`q scramble up your pants, and in shirt, tickling you with their bushy tails.");
			apply_buff('chipboss',array(
				"name"=>"`gC`lhipmuck`q Tickle!",
				"rounds"=>10,
				"wearoff"=>"`qThe `gC`lhipmunks`q flee.",
				"defmod"=>round(1/$badguy['rage'],1),
				"roundmsg"=>"`qThe `gC`lhipmunks`q tickle with their tails, making it hard to attack.",
				"schema"=>"module-chipmuck_boss"
			));
			$chosen-=1;
			break;
		default:
			//Claws and Teeth!
			$damage=e_rand(1,round($session['user']['hitpoints']/8))*$badguy['rage'];
			output("`qThe `gC`lhipmunk`q Boss mauls you with his large teeth and claws causing `t%s`q points of damage!`n",$damage);
			$session['user']['hitpoints']-=$damage;
			$chosen-=1;
			break;
	}
}

?>
