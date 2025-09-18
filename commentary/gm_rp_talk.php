<?php

function gm_rp_talk_getmoduleinfo(){
	$info = array(
		"name"=>"GM Comments for Players",
		"version"=>"1.0",
		"author"=>"`2Oliver Brendel",
		"category"=>"Commentary",
		"download"=>"",

	);
	return $info;
}

function gm_rp_talk_install(){
	module_addhook("gmcommentarea");
	return true;
}

function gm_rp_talk_uninstall(){
	return true;
}

function gm_rp_talk_dohook($hookname,$args){
	global $session;
	switch($hookname){
		case "noclueyet":
			rawoutput("<div class='tooltip' id=\"COMMENT".$row['id']."\">");
			rawoutput(appoencode($row['username']));
			rawoutput("</div>");
			$name=appoencode("`0")."<span onMouseOver=\"showWMTT('MOD".$row['acctid']."')\" onMouseOut=\"hideWMTT()\">".("`\$USER`0")."</span>";
				rawoutput("<script>
					<!--
					wmtt = null;

					document.onmousemove = updateWMTT;

					function updateWMTT(e) {
						x = (document.all) ? window.event.x + document.body.scrollLeft : e.pageX;
						y = (document.all) ? window.event.y + document.body.scrollTop  : e.pageY;
						if (wmtt != null) {
							wmtt.style.left = (x + 20) + \"px\";
							wmtt.style.top 	= (y + 20) + \"px\";
						}
					}

					function showWMTT(id) {
						wmtt = document.getElementById(id);
						wmtt.style.display = \"block\";
					}

					function hideWMTT() {
						wmtt.style.display = \"none\";
					}
					//-->
					</script>
					<style type=\"text/css\">
					.tooltip {
						position: absolute;
						display: none;
						background-color: #FFFFFF;
					}
					</style>");
			break;
		case "gmcommentarea":
			/*$search = "fightingzone-";
			$substr = substr($args['section'],0,strlen($search));
			if ($search == $substr) {
				if (substr($args['commentary'],0,strlen('/game'))=='/game') 
					$args['commentary']=$args['commentary']."`$ (WORLD)`0";
				$args['allow_gm']=true;
			}*/
			$search = "fzone-";
			$substr = substr($args['section'],0,strlen($search));
			if ($search == $substr) {
				$args['allow_gm']=true;
			}
			break;
	}
	return $args;
}

function gm_rp_talk_run() {
}

?>
