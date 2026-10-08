<?php
function drinks_drunkenize($commentary,$level){
	if (get_module_pref("noslur")) return $commentary;
	$straight = $commentary;
	$replacements=0;
	while ($replacements/strlen($straight) < ($level)/500 ){
		$slurs = array("a"=>"aa","e"=>"ee","f"=>"ff","h"=>"hh","i"=>"iy","l"=>"ll","m"=>"mm","n"=>"nn","o"=>"oo","r"=>"rr","s"=>"sss","u"=>"oo","v"=>"vv","w"=>"ww","y"=>"yy","z"=>"zz");
		if (e_rand(0,9)) {
			$letter = array_rand($slurs);
			$x = mb_strpos(strtolower($commentary),$letter);
			if ($x!==false &&
				mb_substr($commentary,$x,5)!="*hic*" &&
				mb_substr($commentary,max($x-1,0),5)!="*hic*" &&
				mb_substr($commentary,max($x-2,0),5)!="*hic*" &&
				mb_substr($commentary,max($x-3,0),5)!="*hic*" &&
				mb_substr($commentary,max($x-4,0),5)!="*hic*") {
				if (mb_substr($commentary,$x,1)<>strtolower($letter))
					$slurs[$letter] = strtoupper($slurs[$letter]);
				else
					$slurs[$letter] = strtolower($slurs[$letter]);
				$commentary = mb_substr($commentary,0,$x).
					$slurs[$letter].mb_substr($commentary,$x+1);
				$replacements++;
			}
		}else{
			$x = e_rand(0,strlen($commentary));
			// Skip the ` followed by a letter
			if (mb_substr($commentary,$x-1,1)=="`") {$x += 1; }
			if (mb_substr($commentary,$x,5)=="*hic*") {$x+=5; }
			if (mb_substr($commentary,max($x-1,0),5)=="*hic*") {$x+=4; }
			if (mb_substr($commentary,max($x-2,0),5)=="*hic*") {$x+=3; }
			if (mb_substr($commentary,max($x-3,0),5)=="*hic*") {$x+=2; }
			if (mb_substr($commentary,max($x-4,0),5)=="*hic*") {$x+=1; }
			$commentary = mb_substr($commentary,0,$x).
				"*hic*".mb_substr($commentary,$x);
			$replacements++;
		}//end if
	}//end while
	//get rid of spare *'s in *hic**hic*
	while (mb_strpos($commentary,"*hic**hic*"))
		$commentary = str_replace("*hic**hic*","*hic*hic*",$commentary);
	return $commentary;
}
?>
