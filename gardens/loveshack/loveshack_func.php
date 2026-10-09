<?php


function loveshack_flirtdec() {
	global $session;
	if (get_module_setting('flirtCharis')==1&&$session['user']['charm']>0) {
		if ($session['user']['charm']>0) $session['user']['charm']--;
		output("`n`n`^You LOSE a charm point!");
	}
}

function loveshack_hidedata($data="") {
	static $num;
	$code = "";
	if (!is_numeric($num)||empty($num)) $num = 0;
	if ($num==0) rawoutput("<script language=\"JavaScript\">\nfunction marShowAndHide(theId)\n{\n   var el = document.getElementById(theId)\n\n   if (el.style.display==\"none\")\n   {\n      el.style.display=\"block\"; //show element\n   }\n   else\n   {\n      el.style.display=\"none\"; //hide element\n   }\n}\n</script>");
	$num++;
	$text = translate_inline("Show/Hide Data");
	$code .= "<a href=\"#\" onClick = marShowAndHide('marData$num')>$text</a>";
	$code .= "<div id='marData$num' style=\"display:none\">";
	$code .= $data;
	$code .= "</div>";
	return $code;
}

function loveshack_flink($ac=1,$text="",$flir="") {
	global $session;
	$code = "";
	$sql = "SELECT login,sex,name,acctid FROM ".db_prefix("accounts")." WHERE acctid=$ac ORDER BY level,login";
	$result = db_query($sql);
	if (db_num_rows($result)!=0) {
		for ($i=0;$i<db_num_rows($result);$i++){
			$row = db_fetch_assoc($result);
			$code = "<a href='runmodule.php?module=loveshack&op=loveshack&op2=flirt&stage=1&gendertarget=".$row['sex']."&flirtitem=$flir&name=".urlencode($row['name'])."&target=".$row['acctid']."'>".translate_inline($text)."</a>";
			addnav("","runmodule.php?module=loveshack&op=loveshack&op2=flirt&stage=1&gendertarget=".$row['sex']."&flirtitem=$flir&name=".urlencode($row['name'])."&target=".$row['acctid']);
		}
	}
	return $code;
}


?>
