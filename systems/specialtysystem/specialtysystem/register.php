<?php

declare(strict_types=1);

/**
 * Rebuild the specialty registry table using hook data.
 */
function specialtysystem_register(): void
{
    $args = modulehook('specialtysystem-register', []);
    invalidatedatacache('specialtygetspecs');
    debug($args);
    $sql = 'DELETE FROM ' . db_prefix('specialtysystem') . ';';
    db_query($sql);
    if (!is_array($args) || $args == []) {
        return;
    }
    $sql = 'INSERT INTO ' . db_prefix('specialtysystem') . ' (spec_name,spec_colour,spec_shortdescription,spec_longdescription,modulename,fightnav_active,newday_active,dragonkill_active,dragonkill_minimum_requirement,stat_requirements,race_requirements,noaddskillpoints,basic_uses) VALUES ';
    $insert = [];
    foreach ($args as $data) {
        $data['dragonkill_active'] = $data['dragonkill_active'] ?? 0;
        $data['dragonkill_minimum_requirement'] = $data['dragonkill_minimum_requirement'] ?? 0;
        $data['stat_requirements'] = $data['stat_requirements'] ?? [];
        $data['race_requirements'] = $data['race_requirements'] ?? [];
        $data['noaddskillpoints'] = $data['noaddskillpoints'] ?? 0;

        $insert[] = "('" . addslashes($data['spec_name']) . "','{$data['spec_colour']}','" . addslashes($data['spec_shortdescription']) . "','" . addslashes($data['spec_longdescription']) . "','{$data['modulename']}','{$data['fightnav_active']}','{$data['newday_active']}','{$data['dragonkill_active']}','{$data['dragonkill_minimum_requirement']}','" . addslashes(serialize($data['stat_requirements'])) . "','" . addslashes(serialize($data['race_requirements'])) . "','{$data['noaddskillpoints']}','{$data['basic_uses']}')";
    }
    if ($insert == []) {
        return;
    }
    debug($insert);
    $sql .= implode(',', $insert);
    db_query($sql);
}
