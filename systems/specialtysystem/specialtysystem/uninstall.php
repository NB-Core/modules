<?php

declare(strict_types=1);

/**
 * Remove a specialty from the registry.
 */
function specialtysystem_uninstall(string $modulename): void
{
    $sql = 'DELETE FROM ' . db_prefix('specialtysystem') . " WHERE modulename='$modulename'";
    db_query($sql);
}
