<?php

declare(strict_types=1);

/** Circulum Vitae snapshot integration backed by Character Restorer 2.0. */

/** Return module metadata and shared-library dependency. */
function circulum_presave_getmoduleinfo(): array
{
    return array('name' => 'Reset Saver - Character Restorer', 'category' => 'Circulum Vitae', 'version' => '2.0',
        'author' => 'Eric Stevens, Oliver Brendel', 'download' => '',
        'requires' => array('charrestore' => '2.0|Character Restorer'),
        'settings' => array('auto_snapshot' => 'Create character snapshots upon each circulum?,bool|1',
            'snapshot_dir' => 'Location to store snapshots|../circulum_snapshots'));
}

/** Install Circulum integration hooks. */
function circulum_presave_install(): bool
{
    module_addhook('circulum-prereset');
    module_addhook('superuser');
    return true;
}

/** Uninstall the wrapper without changing historical archives. */
function circulum_presave_uninstall(): bool
{
    return true;
}

/** Build explicit shared-library options for Circulum. */
function circulum_presave_context(): array
{
    return array('owner' => 'circulum_presave', 'snapshot_dir' => (string) get_module_setting('snapshot_dir'),
        'filename_strategy' => 'reset', 'log_category' => 'circulum_presave', 'privacy_filter' => false,
        'deletion_notification' => false, 'excluded_modules_hook' => false);
}

/** Load the Character Restorer shared API from the stable deployed module path. */
function circulum_presave_load_library(): bool
{
    $snapshotLibrary = __DIR__ . '/charrestore/lib/snapshot.php';
    $restoreLibrary = __DIR__ . '/charrestore/lib/restore.php';
    if (!is_file($snapshotLibrary) || !is_file($restoreLibrary)) {
        return false;
    }
    require_once $snapshotLibrary;
    require_once $restoreLibrary;
    return function_exists('charrestore_snapshot_create') && function_exists('charrestore_restore_admin_flow');
}

/** Handle reset snapshot creation and superuser navigation. */
function circulum_presave_dohook(string $hookname, array $args): array
{
    if ($hookname === 'superuser') {
        global $session;
        if ($session['user']['superuser'] & SU_EDIT_USERS) {
            addnav('Character Restore');
            addnav('Restore a circulum-reset char', 'runmodule.php?module=circulum_presave&op=list&admin=true');
        }
    } elseif ($hookname === 'circulum-prereset') {
        // Core verification needed: circulum_do_reset() currently discards the
        // modulehook() return value, so this wrapper can log failure but cannot
        // reliably cancel the reset until the owning module adds a supported abort contract.
        if (!circulum_presave_load_library()) {
            gamelog('[circulum_presave] Character Restorer shared library is unavailable.');
            return $args;
        }
        if (!charrestore_snapshot_create((int) ($args['acctid'] ?? 0), circulum_presave_context())) {
            gamelog('[circulum_presave] Reset snapshot creation failed before the Circulum reset.');
        }
    }
    return $args;
}

/** Render the authorized shared archive administration flow. */
function circulum_presave_run(): void
{
    check_su_access(SU_EDIT_USERS);
    require_once 'lib/superusernav.php';
    page_header('Character Restore');
    superusernav();
    addnav('Functions');
    addnav('Search', 'runmodule.php?module=circulum_presave&op=list');
    if (!circulum_presave_load_library()) {
        gamelog('[circulum_presave] Character Restorer shared library is unavailable.');
        output('`$Character Restorer is unavailable; snapshots cannot be administered.`0');
    } else {
        charrestore_restore_admin_flow(circulum_presave_context(), (string) httpget('op'), 'runmodule.php?module=circulum_presave');
    }
    page_footer();
}
