<?php

declare(strict_types=1);

/**
 * Add-ons require this file and call specialtysystem_uninstall('<their name>').
 *
 * The helper used to be defined here under the same name as the engine's own
 * uninstall function, so uninstalling an add-on and the engine in one request
 * died with "Cannot redeclare". The engine function now takes the module name
 * itself; this file only makes sure it is loaded.
 */
require_once __DIR__ . '/../specialtysystem.php';
