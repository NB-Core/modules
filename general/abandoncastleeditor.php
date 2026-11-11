<?php
use Lotgd\Security\Csrf;

/**
 * Abandoned Castle Maze Editor
 *
 * Provides a browser based editor for creating maze layouts used by the
 * Abandoned Castle module. Superusers must explicitly grant access to users
 * through the "allowed" user preference in the module preference editor.
 *
 * For manual design or prototyping, see the spreadsheet `mazegrid.xls` located
 * in the repository root.
 */

function abandoncastleeditor_getmoduleinfo()
{
    return [
        'name' => 'Abandoned Castle Maze Editor',
        'version' => '1.0.0',
        'author' => 'Shinobi Legends',
        'category' => 'Administrative',
        // The editor saves through an AJAX POST, which forced navigation would refuse; the save
        // checks the access rights and the module's CSRF token itself.
        'override_forced_nav' => true,
        'prefs' => [
            'User Access,title',
            'allowed' => 'User may access Abandoned Castle Maze Editor,bool|0',
        ],
    ];
}

function abandoncastleeditor_install()
{
    module_addhook('superuser');
    return true;
}

function abandoncastleeditor_uninstall()
{
    return true;
}

function abandoncastleeditor_dohook($hookname, $args)
{
    global $session;

    switch ($hookname) {
        case 'superuser':
            if (($session['user']['superuser'] & SU_EDIT_USERS) && get_module_pref('allowed', 'abandoncastleeditor')) {
                addnav('Editors');
                $link = 'runmodule.php?module=abandoncastleeditor';
                addnav('Abandoned Castle Maze Editor', $link);
                addnav('', $link);
            }
            break;
    }

    return $args;
}

/**
 * Whether the request is a posted maze save with a valid token.
 * Lotgd\Security\Csrf came with the core of September 2026; an older core gets the POST check alone.
 */
function abandoncastleeditor_validpost(){
	if (!class_exists(Csrf::class)) {
		return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
	}
	return Csrf::validatePostRequest("module:abandoncastleeditor");
}

/**
 * The hidden token field for this module's forms; empty on a core without Lotgd\Security\Csrf.
 */
function abandoncastleeditor_tokenfield(){
	return class_exists(Csrf::class) ? Csrf::hiddenField("module:abandoncastleeditor") : "";
}

function abandoncastleeditor_run()
{
    global $session;

    if (!(($session['user']['superuser'] & SU_EDIT_USERS) && get_module_pref('allowed'))) {
        redirect('superuser.php');
    }

    $op = httpget('op');

    if ($op === 'save') {
        // Only the editor's own request, which carries the token, may write a file.
        if (!abandoncastleeditor_validpost()) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Not saved: the request did not come from the editor.';
            exit();
        }
        // httppost() hands values back addslashes()-escaped; the layout's quotes must be plain to be read.
        $layout = abandoncastleeditor_sanitize_layout(stripslashes((string) httppost('layout')));
        if (!abandoncastleeditor_entrance_is_passage($layout)) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Not saved: the entrance needs a passage tile.';
            exit();
        }
        $title = strip_tags(stripslashes((string) httppost('title')));
        $creator = $session['user']['login'] ?? 'anonymous';

        $dir = __DIR__ . '/abandoncastle/custom';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Sanitize title: allow only alphanumerics, dash, underscore, and limit length
        $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '', $title);
        $safeTitle = substr($safeTitle, 0, 32); // Limit length to 32 chars
        $safeTitle = $safeTitle ?: 'untitled';
        $safeTitle = basename($safeTitle); // Remove any directory components
        $filename = sprintf('%s/maze_%s_%d.txt', $dir, $safeTitle, time());
        // Ensure the file is saved only within the intended directory
        $realDir = realpath($dir);
        $realFile = $realDir . '/maze_' . $safeTitle . '_' . time() . '.txt';
        if (strpos(realpath(dirname($realFile)), $realDir) !== 0) {
            header('HTTP/1.1 400 Bad Request');
            echo 'Invalid filename.';
            exit();
        }
        $content = sprintf("//author: %s\n//title: %s\n%s\n", $creator, $title, $layout);
        if (file_put_contents($realFile, $content) === false) {
            // Graceful error message, do not expose system details
            header('HTTP/1.1 500 Internal Server Error');
            echo 'An error occurred while saving your maze. Please try again later.';
            exit();
        }

        echo 'Saved to ' . basename($realFile);
        exit();
    }

    page_header('Abandoned Castle Maze Editor');
    addnav('Navigation');
    addnav('Return to the Grotto', 'superuser.php');
    addnav('', 'superuser.php');
    addnav('Help');
    addnav('Maze Grid Template', 'modules/abandoncastle/mazegrid.xls');
    addnav('', 'modules/abandoncastle/mazegrid.xls');

    switch ($op) {
        case 'new':
            require_once __DIR__ . '/abandoncastle/src/Tiles.php';
            abandoncastleeditor_render();
            break;

        default:
            $newLink = 'runmodule.php?module=abandoncastleeditor&op=new';
            addnav('New Maze', $newLink);
            output("`nUse the navigation menu to create a new maze.`n");
            output("`nThis module provides administrative tools for the Abandoned Castle module.`n");
            output("`nFor manual maze design you can download the <a href=\"mazegrid.xls\">mazegrid.xls</a> template.`n", true);
            break;
    }

    page_footer();
}

/**
 * Sanitize a maze layout string.
 *
 * Strips disallowed characters, trims whitespace, and rebuilds the
 * layout to ensure it matches the expected "array('a','b',...)" format.
 */
function abandoncastleeditor_sanitize_layout(string $layout): string
{
    require_once __DIR__ . '/abandoncastle/src/Tiles.php';
    $allowed = \Shinobi\Modules\AbandonCastle\Tiles::letters();

    preg_match_all("/'([a-z]?)'/", strtolower($layout), $matches);
    $letters = [];
    foreach ($matches[1] ?? [] as $letter) {
        // An empty room is solid rock, not a gap that shifts every room after it.
        $letters[] = in_array($letter, $allowed, true) ? $letter : \Shinobi\Modules\AbandonCastle\Tiles::SOLID;
    }
    if ($letters === []) {
        return 'array();';
    }

    return "array('" . implode("','", $letters) . "');";
}

/**
 * Whether a sanitized layout has a passage on the entrance, the room every
 * player starts in.
 */
function abandoncastleeditor_entrance_is_passage(string $layout): bool
{
    require_once __DIR__ . '/abandoncastle/src/Tiles.php';
    preg_match_all("/'([a-z])'/", $layout, $matches);
    $letter = $matches[1][\Shinobi\Modules\AbandonCastle\Tiles::ENTRANCE_INDEX] ?? '';
    return \Shinobi\Modules\AbandonCastle\Tiles::isPassage($letter);
}

/**
 * The editor page: a palette of named tiles, the 11 by 13 grid, and the
 * controls. Every tile is a button with a spoken name, the grid is walked with
 * the arrow keys, and messages go to a status line instead of alert boxes.
 */
function abandoncastleeditor_render(): void
{
    $tiles = \Shinobi\Modules\AbandonCastle\Tiles::class;
    $t = static fn (string $text): string => (string) translate_inline($text, 'abandoncastleeditor');
    $esc = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $describe = static function (string $letter) use ($tiles, $t): string {
        $label = $t($tiles::kind($letter));
        $directions = $tiles::directionNames($tiles::exits($letter));
        if ($directions !== []) {
            $label .= ', ' . $t('passages') . ' ' . implode(', ', array_map($t, $directions));
        }
        return $label;
    };

    $groups = ['Passages' => [], 'Traps' => [], 'Special' => []];
    $catalog = [];
    foreach ($tiles::letters() as $letter) {
        $group = $tiles::isPassage($letter) ? 'Passages' : ($tiles::isTrap($letter) ? 'Traps' : 'Special');
        $groups[$group][] = $letter;
        $catalog[$letter] = ['label' => $describe($letter), 'svg' => $tiles::svg($letter), 'passage' => $tiles::isPassage($letter)];
    }

    rawoutput('<link href="modules/abandoncastle/abandoncastle.css" rel="stylesheet">');
    rawoutput('<p>' . $esc($t('Pick a tile, then click a room or move to it with the arrow keys and press Enter. Delete empties a room. The entrance is room F1 at the bottom; it needs a passage, and its south side is always closed.')) . '</p>');
    rawoutput('<div class="ac-editor">');

    // Palette
    rawoutput('<div class="ac-palette">');
    foreach ($groups as $group => $letters) {
        rawoutput('<fieldset><legend>' . $esc($t($group)) . '</legend><div class="ac-palette-tiles">');
        foreach ($letters as $letter) {
            rawoutput(sprintf(
                '<button type="button" class="ac-palette-tile" data-letter="%s" aria-pressed="false" aria-label="%s" title="%2$s">%s</button>',
                $letter,
                $esc($catalog[$letter]['label']),
                $catalog[$letter]['svg']
            ));
        }
        rawoutput('</div></fieldset>');
    }
    rawoutput('</div>');

    // Grid, north at the top
    $entrance = $tiles::ENTRANCE_INDEX;
    rawoutput('<div class="ac-grid"><table role="grid" aria-label="' . $esc($t('Maze grid, north at the top')) . '"><tbody>');
    for ($y = $tiles::HEIGHT - 1; $y >= 0; $y--) {
        rawoutput('<tr role="row">');
        for ($x = 0; $x < $tiles::WIDTH; $x++) {
            $index = $y * $tiles::WIDTH + $x;
            $class = 'ac-grid-cell' . ($index === $entrance ? ' ac-entrance' : '');
            rawoutput(sprintf(
                '<td role="gridcell"><button type="button" class="%s" data-index="%d" tabindex="%d"></button></td>',
                $class,
                $index,
                $index === $entrance ? 0 : -1
            ));
        }
        rawoutput('</tr>');
    }
    rawoutput('</tbody></table></div>');
    rawoutput('</div>');

    // Controls, status and export
    rawoutput('<div class="ac-editor-controls">'
        . '<button type="button" class="button" id="ac-fill">' . $esc($t('Fill empty rooms')) . '</button>'
        . '<button type="button" class="button" id="ac-clear">' . $esc($t('Clear')) . '</button>'
        . '<button type="button" class="button" id="ac-export">' . $esc($t('Export')) . '</button>'
        . '</div>');
    rawoutput('<p class="ac-editor-status" role="status" id="ac-status"></p>');
    rawoutput('<div id="ac-export-box" hidden>'
        . '<label for="ac-export-text">' . $esc($t('Maze layout')) . '</label>'
        . '<textarea id="ac-export-text" class="ac-editor-export" readonly></textarea>'
        . '<p><label for="ac-title">' . $esc($t('Title')) . '</label> <input type="text" id="ac-title" maxlength="32"> '
        . '<button type="button" class="button" id="ac-save">' . $esc($t('Save to file')) . '</button></p>'
        . '</div>');

    $token = class_exists(Csrf::class) ? Csrf::token('module:abandoncastleeditor') : '';
    $config = [
        'width' => $tiles::WIDTH,
        'height' => $tiles::HEIGHT,
        'entrance' => $entrance,
        'exit' => $tiles::EXIT,
        'solid' => $tiles::SOLID,
        'tiles' => $catalog,
        'exits' => array_combine($tiles::letters(), array_map([$tiles, 'exits'], $tiles::letters())),
        'token' => $token,
        'tokenField' => class_exists(Csrf::class) ? Csrf::FIELD : 'csrf_token',
        'saveUrl' => 'runmodule.php?module=abandoncastleeditor&op=save',
        'text' => [
            'empty' => $t('empty'),
            'entrance' => $t('entrance'),
            'pickFirst' => $t('Pick a tile first.'),
            'entranceNeedsPassage' => $t('The entrance needs a passage tile.'),
            'placed' => $t('%1$s placed in %2$s.'),
            'emptied' => $t('%s emptied.'),
            'filled' => $t('%1$s empty rooms filled with %2$s.'),
            'nothingToFill' => $t('There are no empty rooms to fill.'),
            'cleared' => $t('The grid is empty.'),
            'noExit' => $t('The maze has no exit yet.'),
            'unreachable' => $t('Warning: no exit can be reached from the entrance.'),
            'exported' => $t('Layout exported below.'),
            'needTitle' => $t('Enter a title first.'),
            'saving' => $t('Saving...'),
            'saveFailed' => $t('The maze could not be saved.'),
        ],
    ];
    $json = json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    rawoutput('<script>window.abandonCastleEditor = ' . $json . ';</script>');
    rawoutput('<script src="modules/abandoncastle/editor.js" defer></script>');
}
