<?php

function datemanager_getmoduleinfo(){
	$info = array(
			"name"=>"Library Module for Date mechanics (on/off) for use in modules",
			"version"=>"1.0",
			"author"=>"`2Oliver Brendel",
			"category"=>"Libraries",
			"download"=>"",
			"settings"=>array(
				),
			"prefs"=>array(
				)
		     );
	return $info;
}

function datemanager_install(){
//	module_addhook("newday");
	return true;
}

function datemanager_uninstall(){
	return true;
}

function datemanager_datevalid(string $date) : bool {
    // Check if the date is MM-dd .. and if MM is 1-12, dd is 1-31 (ignore february)
    $parts = explode('-', $date);
    if (count($parts) != 2) {
        return false;
    }
    list($month, $day) = $parts;
    if (!is_numeric($month) || !is_numeric($day)) {
        return false;
    }
    $month = (int)$month;
    $day = (int)$day;
    if ($month < 1 || $month > 12 || $day < 1 || $day > 31) {
        return false;
    }
    return true;
}
/** Return Values
-1 = error
0 = date not in range
1 = date in range
2 = date in leniency range
**/
function datemanager_datecheck(string $startdate, string $enddate, int $lenient_days_over): int {
    // Check the date format, if it is MM-dd for $startdate and $enddate
    if (!datemanager_datevalid($startdate) || !datemanager_datevalid($enddate)) {
        debug("Invalid date given to date manager: " . $startdate . " // " . $enddate);

        return -1;
    }

    $now = new DateTimeImmutable('now');
    $window = datemanager_normalize_window($startdate, $enddate, $now);

    if ($window === null) {
        debug("Unable to normalize dates for date manager: " . $startdate . " // " . $enddate);

        return -1;
    }

    $start = $window['start']->getTimestamp();
    $end = $window['end']->getTimestamp();
    $leniency_now = $window['end']->modify('+' . $lenient_days_over . ' days')->getTimestamp();
    $current = $now->getTimestamp();

    if ($start <= $current && $current <= $end) {
        return 1;
    }

    if ($start <= $current && $current <= $leniency_now) {
        return 2;
    }

    return 0;
}

/**
 * Normalise the start and end dates of a seasonal window against "now".
 *
 * @internal
 *
 * @return array{start: DateTimeImmutable, end: DateTimeImmutable}|null
 */
function datemanager_normalize_window(string $startdate, string $enddate, ?DateTimeImmutable $now = null): ?array
{
    if (!datemanager_datevalid($startdate) || !datemanager_datevalid($enddate)) {
        return null;
    }

    $now = $now ?? new DateTimeImmutable('now');
    $currentYear = (int) $now->format('Y');

    // '!' resets the time to midnight; without it the current time of day is
    // filled in, which made the end date expire during its own day. The window
    // runs from the start of the first day to the end of the last day.
    $start = DateTimeImmutable::createFromFormat('!Y-m-d', sprintf('%04d-%s', $currentYear, $startdate));
    $end = DateTimeImmutable::createFromFormat('!Y-m-d', sprintf('%04d-%s', $currentYear, $enddate));

    if (!$start || !$end) {
        return null;
    }

    $end = $end->setTime(23, 59, 59);

    if ($end < $start) {
        if ($now < $end) {
            $start = $start->modify('-1 year');
        } else {
            $end = $end->modify('+1 year');
        }
    }

    return [
        'start' => $start,
        'end' => $end,
    ];
}

/**
 * Return the remaining active time for a seasonal window.
 *
 * @param string $start       The start date in MM-dd format.
 * @param string $end         The end date in MM-dd format.
 * @param int    $lenientDays Additional days after the end date that still count as active.
 *
 * @return DateInterval|null Remaining time until the end of the active window, or null if inactive/invalid.
 */
function datemanager_get_time_remaining(string $start, string $end, int $lenientDays = 0): ?DateInterval
{
    $now = new DateTimeImmutable('now');
    $window = datemanager_normalize_window($start, $end, $now);

    if ($window === null) {
        return null;
    }

    $lenientEnd = $window['end']->modify('+' . $lenientDays . ' days');

    if ($now < $window['start'] || $now > $lenientEnd) {
        return null;
    }

    return $now->diff($lenientEnd, false);
}

/**
 * Format a DateInterval countdown as "X days, Y hours, Z minutes".
 *
 * @return string Human-readable representation of the interval.
 */
function datemanager_format_countdown(DateInterval $diff): string
{
    $days = $diff->days ?? $diff->d;

    return sprintf_translate('%d days, %d hours, %d minutes', $days, $diff->h, $diff->i);
}

function datemanager_dohook($hookname,$args){
    return $args;
}

function datemanager_run() {
}

?>
