<?php
function wintersnow_home_getmoduleinfo(){
	$info = array(
		"name" =>"Winter Snow on HP (timelocked)",
		"version" =>"0.1",
		"author" =>"Oliver Brendel",
		"category" =>"Holidays|Christmas",
		"download"=>"",
		"settings"=>array(
			"Winter Castle Settings,title",
			"start"=>"Activation start date (mm-dd)|12-1",
			"end"=>"Activation end date (mm-dd)|12-31",
		),
		"requires"=>array(
			"datemanager"=>"1.0|By Oliver Brendel",
		),
	);
	return $info;
}

function wintersnow_home_install(){
	module_addhook("index");
	return true;
}

function wintersnow_home_uninstall(){
	return true;
}

//dohook

function wintersnow_home_dohook($hook,$args){
	global $session;

	//check for datemanager
	require_once("modules/datemanager.php");

	//check for activation date
	$start = get_module_setting("start");
	$end = get_module_setting("end");
	switch($hook){
                case "index":
                        $mode = wintersnow_home_datecheck();
                        if ($mode === 0) {
                                break;
                        }

                        $placeholderAttributes = array(
                                'data-overlay-module="wintersnow_home"'
                        );

                        $assetBase = "modules/wintersnow_home/assets";
                        $assetBasePath = __DIR__ . '/wintersnow_home/assets';

                        $buildAssetUrl = function (string $filename) use ($assetBase, $assetBasePath): string {
                                $url = "{$assetBase}/{$filename}";
                                $mtime = @filemtime("{$assetBasePath}/{$filename}");

                                if ($mtime !== false) {
                                        $url .= '?v=' . $mtime;
                                }

                                return $url;
                        };

                        if ($mode === 1) {
                                $placeholderAttributes[] = 'data-overlay-mode="snow"';
                                $placeholderAttributes[] = 'data-snowflake-count="100"';
                                $placeholderAttributes[] = 'data-snowflake-color="white"';
                                $placeholderAttributes[] = 'data-z-index="9999"';
                                $snowOverlayUrl = $buildAssetUrl('snow-overlay.js');
                                rawoutput("<script src=\"{$snowOverlayUrl}\" defer></script>");
                        } else {
                                $placeholderAttributes[] = 'data-overlay-mode="fireworks"';
                                $placeholderAttributes[] = 'data-colors="rgb(255, 115, 0)|rgb(0, 200, 255)|rgb(255, 80, 200)|rgb(255, 0, 10)|rgb(255, 255, 255)"';
                                $placeholderAttributes[] = 'data-particle-count="1520"';
                                // Firework timing uses data-intervals (kebab-case) with a comma-separated list of milliseconds.
                                $placeholderAttributes[] = 'data-intervals="800,900,1000,1100,1200,1300,1400"';
                                $placeholderAttributes[] = 'data-speed-range="1,3"';
                                $placeholderAttributes[] = 'data-decay-range="0.005,0.02"';
                                $placeholderAttributes[] = 'data-z-index="9999"';
                                $fireworksOverlayUrl = $buildAssetUrl('fireworks-overlay.js');
                                rawoutput("<script src=\"{$fireworksOverlayUrl}\" defer></script>");
                        }

                        rawoutput('<div id="wintersnow-overlay-root" ' . implode(' ', $placeholderAttributes) . '></div>');
                        $overlayBootstrapUrl = $buildAssetUrl('overlay-bootstrap.js');
                        rawoutput("<script src=\"{$overlayBootstrapUrl}\" defer></script>");
                break;
        }
        return $args;
}

//run function

function wintersnow_home_datecheck(): int
{
        $start = get_module_setting('start');
        $end = get_module_setting('end');

        $window = datemanager_normalize_window($start, $end, new DateTimeImmutable('now'));

        if ($window === null) {
                return 0;
        }

        $currentDay = new DateTimeImmutable('today');
        $startDay = $window['start']->setTime(0, 0);
        $endDay = $window['end']->setTime(23, 59, 59);

        $checkWindow = function (DateTimeImmutable $currentDay, DateTimeImmutable $startDay, DateTimeImmutable $endDay): int {
                if ($currentDay >= $startDay && $currentDay <= $endDay) {
                        return 1;
                }

                $dayAfterEnd = $endDay->modify('+1 day')->setTime(0, 0);

                if ($currentDay == $dayAfterEnd) {
                        return 2;
                }

                return 0;
        };

        $result = $checkWindow($currentDay, $startDay, $endDay);

        if ($result === 0 && $currentDay < $startDay) {
                $result = $checkWindow(
                        $currentDay,
                        $startDay->modify('-1 year'),
                        $endDay->modify('-1 year')
                );
        }

        return $result;
}
