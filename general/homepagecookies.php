<?php

function homepagecookies_getmoduleinfo(){
	return [
		"name"=>"Homepage Cookie Note",
		"description"=>"Modern DSGVO-compliant cookie bar using Klaro! with local assets",
		"version"=>"2.1",
		"author"=>"Oliver Brendel + ChatGPT",
		"override_forced_nav"=>true,
		"category"=>"Administrative",
		"download"=>"",
		"settings"=>[
			"usega"=>"Include Google Analytics consent option?,bool|0",
		],
	];
}

function homepagecookies_install(){
	module_addhook_priority("index-login",100);
	module_addhook_priority("index",10);
	module_addhook_priority("everyhit",10);
	return true;
}

function homepagecookies_uninstall(){
	debug("`n`c`b`QNotifier Module - Uninstalled`0`b`c");
	return true;
}

function homepagecookies_dohook($hookname, $args){
	global $session;
	switch ($hookname) {
		case "everyhit":
			if (!isset($_COOKIE['klaro'])) {
				unset($_COOKIE['lgi']);
				unset($_COOKIE['template']);
				unset($_COOKIE['language']);
			}
			break;

		case "index":
			$usega = get_module_setting("usega");

			// Klaro JS & CSS
			rawoutput("<link rel='stylesheet' href='modules/homepagecookies/assets/klaro.css'>");
			rawoutput("<link rel='stylesheet' href='modules/homepagecookies/assets/lotgd.css'>");
			rawoutput("<script defer src='modules/homepagecookies/assets/klaro.js'></script>");

			// Start Klaro Config
			rawoutput("<script>
				var klaroConfig = {
					elementID: 'klaro',
					storageMethod: 'cookie',
					cookieName: 'klaro',
					lang: 'en',
					translations: {
						en: {
							consentModal: {
								title: 'Cookie Consent',
								description: 'We use cookies to ensure the basic functionality of this website and to enhance your online experience.',
							},
							acceptAll: 'Accept all',
							acceptSelected: 'Accept selected',
							decline: 'Decline',
							privacyPolicy: {
								name: 'Privacy policy',
								text: 'To learn more, please read our {privacyPolicy}.',
							},
							googleAnalytics: {
								description: 'Analytics to improve website performance.',
							}
						}
					},
					mustConsent: true,
					acceptAll: true,
					hideDeclineAll: false,
					hideLearnMore: false,
					services: [
						{
							name: 'essential',
							title: 'Essential Cookies',
							purposes: ['security', 'functional'],
							required: true,
							default: true,
							cookies: ['lgi', 'template', 'language', 'PHPSESSID'],
						},
			");

			if ($usega) {
				rawoutput("
						{
							name: 'googleAnalytics',
							title: 'Google Analytics',
							purposes: ['analytics'],
							cookies: [/^_ga/, /^_gid/, /^_gat/],
							default: false
						}");
			}

			rawoutput("]
				};
			</script>");

			// Klaro Container
			rawoutput("<div id='klaro'></div>");
			break;

		case "index-login":
			rawoutput("<br/><center><a href='#' class=\"button\" onclick=\"klaro.show(); return false;\" style='color:#AA0000;'>Click here to change your cookie settings</a></center><br/>");
			break;
	}
	return $args;
}

function homepagecookies_run(){
}

?>
