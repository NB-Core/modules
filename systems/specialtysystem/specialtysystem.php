<?php

declare(strict_types=1);


use Lotgd\Nav;

/**
 * Returns module information for the Specialty System.
 *
 * The module stores user information in the "data" userpref. This
 * preference contains a serialized array keyed by module name. Each
 * entry holds the skillpoints a user has earned for a specialty and an
 * "active" key denotes which specialty is currently selected.
 */
function specialtysystem_getmoduleinfo(): array
{
    return [
        'name' => 'Specialty Core System',
        'author' => '`2Oliver Brendel',
        'version' => '1.02',
        'download' => '',
        'category' => 'Specialty System',
        'settings' => [
            'Specialty System Settings,title',
            'nospecs' => "Disable Specialty Selection after DK and set to 'SS' for this system,bool|0",
        ],
        'prefs' => [
            'Specialtysystem User Prefs,title',
            'uses' => 'Use recordings,viewonly',
            'cache' => 'Fightnav Cache,viewonly',
            'data' => 'Internal Data for this Module (do not alter),viewonly',
        ],
    ];
}

/**
 * Install hooks and database table for the Specialty System.
 *
 * @return bool Returns true when the installation completed.
 */
function specialtysystem_install(): bool
{
    module_addhook('newday-intercept');

    module_addhook_priority('choose-specialty', 100);
    module_addhook_priority('set-specialty', 100);
    module_addhook_priority('fightnav-specialties', 10);
    module_addhook_priority('apply-specialties', 100);
    module_addhook('newday');
    module_addhook('incrementspecialty');
    module_addhook('specialtynames');
    module_addhook('specialtymodules');
    module_addhook('specialtycolor');
    module_addhook('dragonkill');
    module_addhook('superuser');

    $system = [
        'spec_name' => ['name' => 'spec_name', 'type' => 'varchar(35)'],
        'spec_colour' => ['name' => 'spec_colour', 'type' => 'varchar(2)'],
        'spec_shortdescription' => ['name' => 'spec_shortdescription', 'type' => 'varchar(150)'],
        'spec_longdescription' => ['name' => 'spec_longdescription', 'type' => 'mediumtext'],
        'modulename' => ['name' => 'modulename', 'type' => 'varchar(50)'],
        'fightnav_active' => ['name' => 'fightnav_active', 'type' => 'tinyint', 'default' => '0'],
        'fightnav_everyrefresh' => ['name' => 'fightnav_everyhitrefresh', 'type' => 'tinyint', 'default' => '0'],
        'newday_active' => ['name' => 'newday_active', 'type' => 'tinyint', 'default' => '0'],
        'dragonkill_active' => ['name' => 'dragonkill_active', 'type' => 'tinyint', 'default' => '0'],
        'dk_min' => ['name' => 'dragonkill_minimum_requirement', 'type' => 'smallint', 'default' => '0'],
        'stat_requirements' => ['name' => 'stat_requirements', 'type' => 'varchar(500)', 'default' => ''],
        'noaddskillpoints' => ['name' => 'noaddskillpoints', 'type' => 'tinyint unsigned', 'default' => '0'],
        'basic_uses' => ['name' => 'basic_uses', 'type' => 'tinyint unsigned', 'default' => '0'],
        'key-PRIMARY' => ['name' => 'modulename', 'type' => 'key', 'unique' => '1', 'columns' => 'modulename'],
        'key-one' => ['name' => 'spec_name', 'type' => 'key', 'unique' => '0', 'columns' => 'spec_name'],
    ];

    require_once 'lib/tabledescriptor.php';
    synctable(db_prefix('specialtysystem'), $system, true);

    return true;
}

/**
 * Remove the Specialty System table and reset player specialties.
 */
function specialtysystem_uninstall(): bool
{
    $sql = 'UPDATE ' . db_prefix('accounts') . " SET specialty='' WHERE specialty='SS'";
    db_query($sql);
    $sql = 'DROP TABLE ' . db_prefix('specialtysystem') . ';';
    db_query($sql);

    return true;
}

/**
 * Render fight navigation entries from registered specialties.
 *
 * The navigation uses a cached copy of the specialties stored in the
 * "cache" userpref to avoid expensive database calls.
 */
function specialtysystem_showfightnav(string $script, bool $force = false): void
{
    global $session;

    $raw = get_module_pref('cache', 'specialtysystem') ?? '';
    $check = unserialize(stripslashes($raw));
    if (isset($check['system']) && $check['system'] == 'specialtysystem' && ! $force) {
        $specs = $check['data'];
        $colours = $check['colours'];
    } else {
        $sql = 'SELECT * FROM ' . db_prefix('specialtysystem') . ' WHERE fightnav_active=1';
        $result = db_query($sql);
        $specs = [];
        $colours = [];
        while ($row = db_fetch_assoc($result)) {
            require_once "modules/{$row['modulename']}.php";
            $fname = $row['modulename'] . '_fightnav';
            $add = $fname();
            if ($add !== false) {
                $colours[$row['modulename']] = $row['spec_colour'];
                $specs[$row['modulename']] = $add;
            }
        }
        set_module_pref('cache', serialize(['system' => 'specialtysystem', 'data' => $specs, 'colours' => $colours]), 'specialtysystem');
    }

    require_once 'modules/specialtysystem/functions.php';
    Nav::add(['`bChakra (%s points)`b', specialtysystem_availableuses()]);
    ksort($specs);
    foreach ($specs as $key => $data) {
        $colour = $colours[$key];
	if (!is_array($data))
            continue;
        foreach ($data as $keyi => $dati) {
            if (!is_array($dati)) {
                if (strcmp((string)$keyi, 'headline') == 0) {
                    Nav::addColoredHeader($colour . $dati);
                } else {
                    $dativ = explode('|||', $dati);
                    addnav_notl($colour . $dativ[0] . '`0', $script . "op=fight&skill=SS&skillmodule=$key&skillname=" . $dativ[1], true);
                }
            } else {
                foreach ($dati as $keyv => $datv) {
                    if (strcmp($keyv, 'headline') == 0) {
                        Nav::addColoredSubHeader($colour . $datv);
                    } else {
                        $datvv = explode('|||', $datv);
                        addnav_notl($colour . $datvv[0] . '`0', $script . "op=fight&skill=SS&skillmodule=$key&skillname=" . $datvv[1], true);
                    }
                }
            }
        }
    }
}

/**
 * Handle module hooks for the Specialty System.
 *
 * Hooks access and modify the serialized "data" userpref which tracks
 * all specialties a player has unlocked and the currently active one
 * stored under the "active" key.
 */
function specialtysystem_dohook(string $hookname, array $args)
{
    global $session, $resline;

    switch ($hookname) {
        case 'superuser':
            tlschema('superuser');
            addnav('Mechanics');
            tlschema();
            if (($session['user']['superuser'] & SU_MEGAUSER) == SU_MEGAUSER) {
                addnav('Refresh Specialty System Add-Ons', 'runmodule.php?module=specialtysystem&op=refresh');
                addnav('Specialty System Repair Utility', 'runmodule.php?module=specialtysystem&op=repair');
            }
            break;
        case 'newdayintercept':
            require_once 'modules/specialtysystem/datafunctions.php';
            if (httpget('ssystem') != '') {
                specialtysystem_set(['active' => httpget('ssystem')]);
            } elseif (get_module_setting('nospecs') && $session['user']['specialty'] == '') {
                $session['user']['specialty'] = 'SS';
            }
            break;
        case 'dragonkill':
            set_module_pref('data', serialize([]), 'specialtysystem');
            set_module_pref('uses', 0, 'specialtysystem');
            set_module_pref('cache', '', 'specialtysystem');
            modulehook('specialtysystem_post_dragonkill',array());
            break;
        case 'choose-specialty':
            require_once 'modules/specialtysystem/datafunctions.php';
            if ($session['user']['specialty'] == '' || $session['user']['specialty'] == '0') {
                $choices = specialtysystem_getspecs();
                addnav('Chakra Specialties');
                $first = false;
                output_notl('`c');
                foreach ($choices as $key => $data) {
                    if ($data['dragonkill_minimum_requirement'] > $session['user']['dragonkills']) {
                        continue;
                    }
                    if ((int) $data['dragonkill_minimum_requirement'] == -1) {
                        continue;
                    }
                    if ($first) {
                        output_notl('`~~~~~~~~~~~~~`2`n`n');
                    }
                    $first = true;
                    $spec = $data['spec_colour'] . translate_inline($data['spec_name'], 'module-' . $data['modulename']);
                    output_notl('%s:`n`n', $spec);
                    $available = true;
                    if (isset($data['stat_requirements']) && $data['stat_requirements'] != '') {
                        output('`4Minimum Requirements:`n');
                        $unserialized = unserialize($data['stat_requirements']);
                        if (!is_array($unserialized)) {
                            output('None`n');
                        } else {
                            foreach ($unserialized as $stat => $value) {
                                $ok = ($session['user'][$stat] >= $value ? 1 : 0);
                                $k = $ok ? '`2' : '`$';
                                if (!$ok) {
                                    $available = false;
                                }
                                $stat_trans = translate_inline($stat, 'stats_specialtysystem');
                                output('%s%s (Minimum %s needed)`n', $k, $stat_trans, $value);
                            }
                        }
                        output_notl('`n`n');
                    }
                    if (!$available) {
                        addnav('Unavailable');
                        addnav_notl(sanitize($spec), '');
                        $t1 = appoencode(translate_inline($data['spec_shortdescription'], 'module-' . $data['modulename']));
                        rawoutput("$t1<br>");
                    } else {
                        addnav('Chakra Specialties');
                        addnav_notl(" ?$spec", "newday.php?setspecialty=SS&ssystem={$data['modulename']}$resline");
                        $t1 = appoencode(translate_inline($data['spec_shortdescription'], 'module-' . $data['modulename']));
                        rawoutput("<a href='newday.php?setspecialty=SS&ssystem={$data['modulename']}$resline'>$t1</a><br>");
                        addnav('', "newday.php?setspecialty=SS&ssystem={$data['modulename']}$resline");
                    }
                    output_notl('`n');
                }
                output_notl('`c');
                if ($session['user']['dragonkills'] < 1) {
                    output("`n`n`c`\$More `bsophisticated`b stuff will come along once you are more experienced!`c`0`n`n");
                }
            }
            break;
        case 'set-specialty':
            require_once 'modules/specialtysystem/datafunctions.php';
            if ($session['user']['specialty'] == 'SS') {
                $module = httpget('ssystem');
                $data = specialtysystem_getspecs($module);
                $data = array_shift($data);
                specialtysystem_set(['active' => $module, $module => ['skillpoints' => 1]]);
                page_header('A little story about yourself');
                output_notl('`c`b%s%s`b`c`n`n`&', $data['spec_colour'], $data['spec_name']);
                $desc = translate_inline($data['spec_longdescription'], 'module-' . $data['modulename']);
                output_notl($desc);
            }
            $basic = specialtysystem_getspecs();
            foreach ($basic as $modulename => $data) {
                if ($data['basic_uses'] > 0) {
                    specialtysystem_set([$modulename => ['skillpoints' => $data['basic_uses']]]);
                }
            }
            break;
        case 'specialtycolor':
            require_once 'modules/specialtysystem/datafunctions.php';
            $specs = specialtysystem_get('active');
            if ($specs == false) {
                break;
            }
            $spec = specialtysystem_getspecs($specs);
            $args['SS'] = $spec['spec_colour'] ?? 'SpecialtySystem';
            break;
        case 'specialtynames':
            global $SCRIPT_NAME;
            if ($SCRIPT_NAME == 'bio.php') {
                $login = httpget('char');
                $sql = 'SELECT acctid,login,specialty FROM ' . db_prefix('accounts') . " WHERE login='$login';";
                $result = db_query($sql);
                $user = db_fetch_assoc($result);
            } else {
                $user = $session['user'];
            }
            if (!isset($user['specialty']) || $user['specialty'] != 'SS') {
                break;
            }
            require_once 'modules/specialtysystem/datafunctions.php';
            require_once 'modules/specialtysystem/functions.php';
            $activeModule = specialtysystem_get('active', $user['acctid']);
            if ($activeModule === false) {
                $allData = specialtysystem_get(null, $user['acctid']);
                $availableSpecs = specialtysystem_getspecs();
                $bestModule = null;
                $bestPoints = -1;

                if (is_array($allData)) {
                    unset($allData['active']);
                    foreach ($availableSpecs as $moduleName => $specData) {
                        if (!isset($allData[$moduleName]) || !is_array($allData[$moduleName])) {
                            continue;
                        }
                        $points = (int) ($allData[$moduleName]['skillpoints'] ?? 0);
                        if ($points > $bestPoints) {
                            $bestModule = $moduleName;
                            $bestPoints = $points;
                        }
                    }
                }

                if ($bestModule !== null) {
                    specialtysystem_set(['active' => $bestModule], $user['acctid']);
                    $activeModule = $bestModule;
                } else {
                    $loginName = $user['login'] ?? ($user['acctid'] ?? 'unknown');
                    $message = sprintf(
                        'SpecialtySystem: Unable to infer active specialty for acctid %d (%s). Player should re-select a specialty on their next new day.',
                        (int) ($user['acctid'] ?? 0),
                        (string) $loginName
                    );
                    if (function_exists('debuglog')) {
                        debuglog($message);
                    } else {
                        trigger_error($message, E_USER_WARNING);
                    }
                    output('`$Your chakra path is unclear. Please choose a specialty again on the next new day.`0');
                    $args['SS'] = translate_inline('Specialty Placeholder');
                    break;
                }
            }
            $current = 0; // unused variable kept for compatibility
            $temp = specialtysystem_getspecs($activeModule);
            $data = array_shift($temp);
            if (!is_array($data)) {
                $args['SS'] = translate_inline('Specialty Placeholder');
                break;
            }
            $args['SS'] = translate_inline($data['spec_name']);
            break;
        case 'specialtymodules':
            $args['SS'] = 'specialtysystem';
            break;
        case 'incrementspecialty':
            $col = $args['color'];
            if ($session['user']['specialty'] != 'SS') {
                break;
            }
            require_once 'modules/specialtysystem/datafunctions.php';
            require_once 'modules/specialtysystem/functions.php';
            $name = '';
            $specs = specialtysystem_getspecs();
            foreach ($specs as $name_m => $spec) {
                if ($spec['spec_colour'] == $col) {
                    $name = $name_m;
                }
            }
            if ($col == '`^') {
                $name = specialtysystem_get('active');
            }
            if ($name == '') {
                break;
            }
            $data = $name;
            $current = 0;
            $current = specialtysystem_increment($data, 1);
            $specs = specialtysystem_getspecs($data);
            $data = array_shift($specs);
            $total = specialtysystem_getskillpoints();
            if (httpget('suppress') != 1) {
                output('`n`^You gain a level in `&%s%s`^. All in all, you have `&%s`^ skillpoints with this specialty and `&%s`^ all in all!`n`n', $data['spec_colour'], translate_inline($data['spec_name'], 'module-' . $data['modulename']), $current, $total);
                output_notl('`0');
            }
            set_module_pref('cache', '', 'specialtysystem');
            break;
        case 'apply-specialties':
            if (httpget('skill') != 'SS') {
                break;
            }
            $module = httpget('skillmodule');
            require_once "modules/$module.php";
            $fname = $module . '_apply';
            $value = $fname(httpget('skillname'));
            set_module_pref('cache', '', 'specialtysystem');
            break;
        case 'fightnav-specialties':
            $script = $args['script'];
            specialtysystem_showfightnav($script);
            break;
        case 'newday':
            if ($session['user']['specialty'] != 'SS') {
                break;
            }
            set_module_pref('uses', 0);
            $bonus = getsetting('specialtybonus', 1);
            $intel = (int) ($session['user']['intelligence'] / 10);
            $bonus += $intel;
            require_once 'modules/specialtysystem/datafunctions.php';
            specialtysystem_newday();
            $data = specialtysystem_get('active');
            if ($data == false) {
                output_notl('Error with your specialty! Report to admin!');
                break;
            }
            require_once 'modules/specialtysystem/functions.php';
            $current = specialtysystem_setuses(-$bonus);
            $temp = specialtysystem_getspecs($data);
            $data = array_shift($temp);
            $name = translate_inline($data['spec_name'], 'module-' . $data['modulename']);
            if ($bonus == 1) {
                output('`n`2Because of your inclination to %s%s`2, you receive `^1`2 extra chakra use for today.`n', $data['spec_colour'], $name);
            } else {
                output('`n`2Because of your inclination to %s%s`2, you receive `^%s`2 extra chakra uses (`@%s for high intelligence`2) for today.`n', $data['spec_colour'], $name, $bonus, $intel);
            }
            set_module_pref('cache', '', 'specialtysystem');
            break;
    }

    return $args;
}

/**
 * Entry point for runmodule operations.
 *
 * Currently only handles refreshing the module list used by the
 * Specialty System.
 */
function specialtysystem_run(): void
{
    $op = httpget('op');
    switch ($op) {
        case 'refresh':
            require_once 'modules/specialtysystem/register.php';
            specialtysystem_register();
            page_header('Specialtysystem');
            output('`2Successfully refreshed!');
            villagenav();
            page_footer();
            break;
        case 'repair':
            global $session;

            check_su_access(SU_MEGAUSER);

            require_once 'lib/superusernav.php';
            superusernav();

            require_once 'modules/specialtysystem/datafunctions.php';

            page_header('Specialtysystem Repair Utility');
            addnav('', 'runmodule.php?module=specialtysystem&op=repair');

            $acctId = (int) (httppost('acctid') ?: httpget('acctid'));
            $action = httppost('action');
            $module = httppost('module');

            $messages = [];
            $errors = [];

            $specs = specialtysystem_getspecs();
            $userInfo = null;
            $userPayload = [];

            if ($acctId > 0) {
                $sql = 'SELECT acctid, name, login FROM ' . db_prefix('accounts') . ' WHERE acctid = ' . $acctId;
                $result = db_query($sql);
                $userInfo = db_fetch_assoc($result) ?: null;

                if ($userInfo === null) {
                    $errors[] = translate_inline('The requested account could not be found.');
                } else {
                    $userPayload = specialtysystem_get(null, $acctId);
                }
            }

            if ($action !== '' && $acctId <= 0) {
                $errors[] = translate_inline('Select a valid account before performing maintenance.');
            }

            if ($userInfo !== null && $action === 'set-active') {
                if ($module === '') {
                    $errors[] = translate_inline('Choose a specialty to set as active.');
                } elseif (!isset($specs[$module])) {
                    $errors[] = translate_inline('The requested specialty is not registered.');
                } else {
                    specialtysystem_set(['active' => $module], $acctId);
                    set_module_pref('cache', '', 'specialtysystem', $acctId);
                    $messages[] = sprintf_translate('Active specialty changed to `%s`0 for %s (%s).', $module, $userInfo['name'], $userInfo['login']);
                    specialtysystem_record_moderation($acctId, sprintf('Set active specialty to %s for account %s (#%s).', $module, $userInfo['login'], $acctId));
                    $userPayload = specialtysystem_get(null, $acctId);
                }
            } elseif ($userInfo !== null && $action === 'reset') {
                if ($module === '') {
                    $errors[] = translate_inline('Choose a specialty to reset.');
                } elseif (!isset($specs[$module])) {
                    $errors[] = translate_inline('The requested specialty is not registered.');
                } else {
                    $basicUses = isset($specs[$module]['basic_uses']) ? (int) $specs[$module]['basic_uses'] : 0;
                    $currentBlock = specialtysystem_get($module, $acctId);
                    if (!is_array($currentBlock)) {
                        $currentBlock = [];
                    }
                    $currentBlock['skillpoints'] = $basicUses;
                    specialtysystem_set([$module => $currentBlock], $acctId);
                    set_module_pref('cache', '', 'specialtysystem', $acctId);
                    $messages[] = sprintf_translate('Reset `%s`0 skillpoints to %s for %s (%s).', $module, $basicUses, $userInfo['name'], $userInfo['login']);
                    specialtysystem_record_moderation($acctId, sprintf('Reset specialty %s skillpoints to %d for account %s (#%s).', $module, $basicUses, $userInfo['login'], $acctId));
                    $userPayload = specialtysystem_get(null, $acctId);
                }
            }

            output('`bSpecialty System Repair Utility`b`n');

            foreach ($messages as $message) {
                output('`@%s`0`n', $message);
            }

            foreach ($errors as $error) {
                output('`$%s`0`n', $error);
            }

            rawoutput('<form method="get" action="runmodule.php?module=specialtysystem&op=repair">');
            rawoutput('<label>' . translate_inline('Account ID') . ': <input name="acctid" value="' . ($acctId > 0 ? (int) $acctId : '') . '" /></label> ');
            rawoutput('<button type="submit">' . translate_inline('Load Account') . '</button>');
            rawoutput('</form>');

            if ($userInfo !== null) {
                output('`n`bAccount`b: %s (%s) `0[`#%s`0]`n', $userInfo['name'], $userInfo['login'], $userInfo['acctid']);

                if (!empty($userPayload)) {
                    $encoded = json_encode($userPayload, JSON_PRETTY_PRINT);
                    if ($encoded !== false) {
                        rawoutput('<div style="margin-top:10px"><details open><summary>' . translate_inline('Stored Specialty Payload') . '</summary><pre>' . htmlentities($encoded, ENT_COMPAT, 'UTF-8') . '</pre></details></div>');
                    }
                }

                rawoutput('<h3>' . translate_inline('Registered Specialties') . '</h3>');
                rawoutput('<table class="specialty-repair" style="width:100%; max-width:900px" border="0" cellspacing="1" cellpadding="3">');
                rawoutput('<tr class="trhead"><th>' . translate_inline('Module') . '</th><th>' . translate_inline('Name') . '</th><th>' . translate_inline('Skillpoints') . '</th><th>' . translate_inline('Basic Uses') . '</th><th>' . translate_inline('Actions') . '</th></tr>');

                $rowClass = 'trlight';
                $active = $userPayload['active'] ?? '';

                foreach ($specs as $key => $spec) {
                    $rowClass = $rowClass === 'trlight' ? 'trdark' : 'trlight';
                    $skillpoints = $userPayload[$key]['skillpoints'] ?? translate_inline('n/a');
                    $basicUses = isset($spec['basic_uses']) ? (int) $spec['basic_uses'] : 0;
                    $name = $spec['spec_name'] ?? $key;

                    rawoutput('<tr class="' . $rowClass . '">');
                    rawoutput('<td>' . htmlentities($key, ENT_COMPAT, 'UTF-8') . ($active === $key ? ' ' . translate_inline('(active)') : '') . '</td>');
                    rawoutput('<td>' . htmlentities($name, ENT_COMPAT, 'UTF-8') . '</td>');
                    rawoutput('<td>' . htmlentities((string) $skillpoints, ENT_COMPAT, 'UTF-8') . '</td>');
                    rawoutput('<td>' . htmlentities((string) $basicUses, ENT_COMPAT, 'UTF-8') . '</td>');
                    rawoutput('<td>');

                    rawoutput('<form method="post" action="runmodule.php?module=specialtysystem&op=repair" style="display:inline;margin-right:4px">');
                    rawoutput('<input type="hidden" name="acctid" value="' . (int) $acctId . '">');
                    rawoutput('<input type="hidden" name="module" value="' . htmlentities($key, ENT_COMPAT, 'UTF-8') . '">');
                    rawoutput('<input type="hidden" name="action" value="set-active">');
                    rawoutput('<button type="submit"' . ($active === $key ? ' disabled' : '') . '>' . translate_inline('Set Active') . '</button>');
                    rawoutput('</form>');

                    rawoutput('<form method="post" action="runmodule.php?module=specialtysystem&op=repair" style="display:inline">');
                    rawoutput('<input type="hidden" name="acctid" value="' . (int) $acctId . '">');
                    rawoutput('<input type="hidden" name="module" value="' . htmlentities($key, ENT_COMPAT, 'UTF-8') . '">');
                    rawoutput('<input type="hidden" name="action" value="reset">');
                    rawoutput('<button type="submit">' . translate_inline('Reset Skillpoints') . '</button>');
                    rawoutput('</form>');

                    rawoutput('</td>');
                    rawoutput('</tr>');
                }

                rawoutput('</table>');
            }

            page_footer();
            break;
    }
}

function specialtysystem_record_moderation(int $acctId, string $message): void
{
    global $session;

    if (function_exists('log_moderation')) {
        log_moderation($acctId, $message, 'specialtysystem');

        return;
    }

    if (class_exists('Lotgd\\Core\\Log')) {
        /** @psalm-suppress UndefinedClass */
        Lotgd\Core\Log::moderation($message, $acctId, $session['user']['acctid'] ?? 0, 'specialtysystem');

        return;
    }

    modulehook('moderationlog', [
        'module' => 'specialtysystem',
        'acctid' => $acctId,
        'moderator' => $session['user']['acctid'] ?? 0,
        'message' => $message,
    ]);

    if (function_exists('debuglog')) {
        debuglog($message, $acctId);
    }
}
