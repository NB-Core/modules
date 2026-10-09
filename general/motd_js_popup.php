<?php

function motd_js_popup_getmoduleinfo(){
	$info = array(
		"name"=>"MotD JS Popup",
		"version"=>"1.0",
		"author"=>"Oliver Brendel",
		"category"=>"General",
		"download"=>"",
		"settings"=>array(
			"MotD JS Popup Settings,title",
			
		),
	);
	return $info;
}

function motd_js_popup_install(){
	// check only on village
	module_addhook("village");
	return true;
}

function motd_js_popup_uninstall(){
	return true;
}

function motd_js_popup_dohook($hookname,$args){
	global $session;
	switch ($hookname) {
		case "village":
			// Check if the user has not seen the motd yet
			$sql = "SELECT motddate FROM " . db_prefix("motd") . " ORDER BY motditem DESC LIMIT 1";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			$headscript = "";
		if (db_num_rows($result)>0 && isset($session['user']['lastmotd']) && ($row['motddate']>$session['user']['lastmotd']) && $session['user']['loggedin']){
				// need to view the motd
				// Add the JS and CSS to the header
				rawoutput("
				<!-- jQuery Modal -->
<script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.js\"></script>
<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css\" />");
				
				rawoutput("<a id='popupMOTD' href=\"motd.php\" rel=\"modal:open\"></a>");
				rawoutput("<script type='text/javascript'>");
// Directly trigger the popup on page load
rawoutput("
$('#popupMOTD').click(function(event) {
	event.preventDefault();
	this.blur(); // Manually remove focus from clicked link.
	$.get(this.href, function(html) {
		$(html).appendTo('body').modal();
		});
});

$('#popupMOTD').modal({
	fadeDuration: 1000,
	clickClose: false,
});

$( document ).on( 'pageinit', function() {
	$( '#popupMOTD' ).click();
	});
");
rawoutput("</script>");
			}

		break;
	}
	return $args;
}
