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
        $layout = httppost('layout');
        $layout = abandoncastleeditor_sanitize_layout($layout);
        $title = strip_tags(httppost('title'));
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
            // Load available tile images
            $imageDir = __DIR__ . '/abandoncastle/images';
            if (is_dir($imageDir) && is_readable($imageDir)) {
                $files = scandir($imageDir);
            } else {
                $files = false;
                output("Error: Unable to access the tile images directory.");
            }
            $tiles = [];
            if ($files !== false) {
                foreach ($files as $file) {
                    if (preg_match('/^([a-z])maze\.gif$/', $file, $matches)) {
                        $tiles[$matches[1]] = $file;
                    }
                }
            }
            require_once __DIR__ . '/abandoncastle/src/MazeRepository.php';
            $startTile = \Shinobi\Modules\AbandonCastle\MazeRepository::START_TILE;
            $letters = [];
            foreach (\Shinobi\Modules\AbandonCastle\MazeRepository::getMazes() as $maze) {
                $letters = array_merge($letters, $maze);
            }
            $letters = array_unique($letters);
            $letters[] = 'x';
            $tiles = array_intersect_key($tiles, array_flip($letters));
            ksort($tiles);

            $currentLink = 'runmodule.php?module=abandoncastleeditor&op=new';

            // Palette
            rawoutput("<div id='palette'>");
            foreach ($tiles as $letter => $file) {
                $src = "modules/abandoncastle/images/{$file}";
                rawoutput("<img src='{$src}' data-letter='{$letter}' data-src='{$src}' class='tile'>");
            }
            rawoutput('</div>');

            // Grid 11x13 with bottom row rendered last so index 0 maps to bottom-left
            $cols = 11;
            $rows = 13;
            $ENTRANCE_INDEX = 5; // Bottom row, 6th cell
            rawoutput("<table id='maze-grid'>");
            for ($row = 0; $row < $rows; $row++) {
                rawoutput('<tr>');
                for ($col = 0; $col < $cols; $col++) {
                    // Map visual grid coordinates to 1D index so that index 0 is bottom-left.
                    // This inverts the row order: row 0 is the bottom row, row $rows-1 is the top.
                    $index = ($rows - 1 - $row) * $cols + $col;
                    if ($index === $ENTRANCE_INDEX) {
                        $src = "modules/abandoncastle/images/{$startTile}maze.gif";
                        rawoutput("<td data-index='{$index}' class='cell'><img src='{$src}'></td>");
                    } else {
                        rawoutput("<td data-index='{$index}' class='cell'></td>");
                    }
                }
                rawoutput('</tr>');
            }
            rawoutput('</table>');

            // Controls
            $clearLink = $currentLink;
            $fillLink = $currentLink;
            $exportLink = $currentLink;
            rawoutput("<div id='controls'>");
            rawoutput("<a href='{$clearLink}' id='clear-btn' class='button'>Clear</a>");
            rawoutput("<a href='{$fillLink}' id='fill-btn' class='button'>Fill</a>");
            rawoutput("<a href='{$exportLink}' id='export-btn' class='button'>Export</a>");
            rawoutput('</div>');
            addnav('', $clearLink);
            addnav('', $fillLink);
            addnav('', $exportLink);

            // Styles
            $style = <<<'CSS'
<style>
#palette img {
    cursor: pointer;
    margin: 2px;
    border: 1px solid transparent;
}
#palette img.selected {
    border-color: #f00;
}
#maze-grid {
    border-collapse: collapse;
    margin-top: 10px;
}
#maze-grid td {
    width: 24px;
    height: 24px;
    text-align: center;
    border: 1px solid #000;
    cursor: pointer;
    font-weight: bold;
}
#controls {
    margin-top: 10px;
}
#controls .button {
    margin-right: 5px;
}
</style>
CSS;
            rawoutput($style);

            // Scripts
            require_once __DIR__ . '/abandoncastle/src/MazeRepository.php';
            $startTile = \Shinobi\Modules\AbandonCastle\MazeRepository::START_TILE;
            $exitTile = \Shinobi\Modules\AbandonCastle\MazeRepository::EXIT_TILE;
            rawoutput("<script>const START_TILE = '{$startTile}'; const EXIT_TILE = '{$exitTile}'; const START_SRC = 'modules/abandoncastle/images/{$startTile}maze.gif'; const EXIT_SRC = 'modules/abandoncastle/images/{$exitTile}maze.gif'; const ENTRANCE_INDEX = {$ENTRANCE_INDEX};</script>");
            rawoutput("<script src='../ext/js/jquery.min.js'></script>");
            $script = <<<'SCRIPT'
<script>
$(function(){
    const cols = 11;
    const rows = 13;
    let layout = new Array(cols * rows).fill('');
    let selected = '';
    let selectedSrc = '';
    let startIdx = ENTRANCE_INDEX;
    let exitIdx = null;
    layout[startIdx] = START_TILE;
    $('#maze-grid td[data-index="'+startIdx+'"]').html('<img src="'+START_SRC+'">');

    $('#palette img').on('click', function(){
        $('#palette img').removeClass('selected');
        $(this).addClass('selected');
        selected = $(this).data('letter');
        selectedSrc = $(this).data('src');
    });

    $('#maze-grid td').on('click', function(){
        if (!selected) return;
        const idx = $(this).data('index');
        if (idx === startIdx) {
            return;
        }
        if (selected === START_TILE) {
            alert('Start position is fixed and cannot be moved.');
            return;
        } else if (selected === EXIT_TILE) {
            if (exitIdx !== null && exitIdx !== idx) {
                alert('Exit already placed. Previous exit removed.');
                layout[exitIdx] = '';
                $('#maze-grid td[data-index="'+exitIdx+'"]').empty();
            }
            exitIdx = idx;
        } else {
            if (idx === exitIdx) {
                exitIdx = null;
            }
        }
        layout[idx] = selected;
        $(this).html('<img src="'+selectedSrc+'">');
    });

    $('#clear-btn').on('click', function(e){
        e.preventDefault();
        layout.fill('');
        $('#maze-grid td').empty();
        startIdx = ENTRANCE_INDEX;
        layout[startIdx] = START_TILE;
        $('#maze-grid td[data-index="'+startIdx+'"]').html('<img src="'+START_SRC+'">');
        exitIdx = null;
    });

    $('#fill-btn').on('click', function(e){
        e.preventDefault();
        if (!selected) return;
        if (selected === START_TILE || selected === EXIT_TILE) {
            alert('Fill operation not allowed for start and exit tiles.');
            return;
        }
        layout.fill(selected);
        $('#maze-grid td').html('<img src="'+selectedSrc+'">');
        if (startIdx !== null) {
            layout[startIdx] = START_TILE;
            $('#maze-grid td[data-index="'+startIdx+'"]').html('<img src="'+START_SRC+'">');
        }
        if (exitIdx !== null) {
            layout[exitIdx] = EXIT_TILE;
            $('#maze-grid td[data-index="'+exitIdx+'"]').html('<img src="'+EXIT_SRC+'">');
        }
    });

    $('#export-btn').on('click', function(e){
        e.preventDefault();
        const exported = 'array(' + layout.map(l => '\'' + l + '\'').join(',') + ');';
        $('#export-text').val(exported);
        $('#export-modal').show();
    });

    $('#close-export-modal').on('click', function(){
        $('#export-modal').hide();
    });

    $('#save-file-btn').on('click', function(){
        const exported = $('#export-text').val();
        const title = prompt('Enter maze title:');
        if (!title) {
            return;
        }
        $.post('runmodule.php?module=abandoncastleeditor&op=save', {layout: exported, title: title, csrf_token: CSRF_TOKEN}, function(resp){
            alert(resp);
        });
    });
});
</script>
SCRIPT;
            // Add modal HTML for export
            rawoutput('
<div id="export-modal" style="display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%); background:#fff; border:1px solid #333; padding:20px; z-index:1000;">
    <h3>Maze Layout Export</h3>
    <textarea id="export-text" style="width:100%;height:100px;"></textarea>
    <br>
    <button id="save-file-btn">Save to temp file</button>
    <button id="close-export-modal">Close</button>
</div>
');
            // The save request carries the module's token.
            $token = class_exists(Csrf::class) ? Csrf::token("module:abandoncastleeditor") : "";
            rawoutput(str_replace('CSRF_TOKEN', json_encode($token), $script));

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
    // allow only lowercase letters and array syntax characters
    $layout = strtolower($layout);
    $layout = preg_replace("/[^a-z,'()\s]/", '', $layout);
    $layout = trim($layout);

    preg_match_all("/'([a-z])'/", $layout, $matches);
    $letters = $matches[1] ?? [];

    if (empty($letters)) {
        return 'array();';
    }

    return "array('" . implode("','", $letters) . "');";
}


