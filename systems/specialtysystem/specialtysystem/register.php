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
    $params = [];
    foreach ($args as $i => $data) {
        $data['dragonkill_active'] = $data['dragonkill_active'] ?? 0;
        $data['dragonkill_minimum_requirement'] = $data['dragonkill_minimum_requirement'] ?? 0;
        $data['stat_requirements'] = $data['stat_requirements'] ?? [];
        $data['race_requirements'] = $data['race_requirements'] ?? [];
        $data['noaddskillpoints'] = $data['noaddskillpoints'] ?? 0;
        $data['basic_uses'] = $data['basic_uses'] ?? 0;

        // All values are bound raw: addslashes() inside the old quoted literals was undone by MySQL.
        // The values that were interpolated unescaped are bound as the strings they used to be.
        $n = 'r' . count($insert);
        $insert[] = "(:{$n}_name,:{$n}_colour,:{$n}_short,:{$n}_long,:{$n}_module,:{$n}_fight,:{$n}_newday,:{$n}_dk,:{$n}_dkmin,:{$n}_stat,:{$n}_race,:{$n}_noadd,:{$n}_uses)";
        $params["{$n}_name"] = (string) $data['spec_name'];
        $params["{$n}_colour"] = (string) $data['spec_colour'];
        $params["{$n}_short"] = (string) $data['spec_shortdescription'];
        $params["{$n}_long"] = (string) $data['spec_longdescription'];
        $params["{$n}_module"] = (string) $data['modulename'];
        $params["{$n}_fight"] = (string) $data['fightnav_active'];
        $params["{$n}_newday"] = (string) $data['newday_active'];
        $params["{$n}_dk"] = (string) $data['dragonkill_active'];
        $params["{$n}_dkmin"] = (string) $data['dragonkill_minimum_requirement'];
        $params["{$n}_stat"] = serialize($data['stat_requirements']);
        $params["{$n}_race"] = serialize($data['race_requirements']);
        $params["{$n}_noadd"] = (string) $data['noaddskillpoints'];
        $params["{$n}_uses"] = (string) $data['basic_uses'];
    }
    if ($insert == []) {
        return;
    }
    debug($insert);
    $sql .= implode(',', $insert);
    \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement($sql, $params);
}
