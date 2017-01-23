<?php
function marriage_charsleft($name,$startdiv=false) {
	$youhave = translate_inline("You have ");
	$charsleft = translate_inline(" characters left.");
	if ($startdiv===false) 
		$startdiv='';
	rawoutput("<script language='JavaScript'>
				function previewtext$name(t,l){
					var out = \"<span class=\'colLtWhite\'>".addslashes(appoencode($startdiv))." \";
					var end = '</span>';
					var x=0;
					var y='';
					var z='';
					var max=document.getElementById('input$name');
					var charsleft='';
					if (l-t.length<0) charsleft +='<span class=\'colLtRed\'>';
					charsleft += '".$youhave."'+(l-t.length)+'".$charsleft."<br>';
					if (l-t.length<0) charsleft +='</span>';
					document.getElementById('charsleft$name').innerHTML=charsleft+'<br/>';
				}
				</script>
				");
	rawoutput("<span id='charsleft$name'></span>");
}
?>