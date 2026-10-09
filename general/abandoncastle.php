<?php

declare(strict_types=1);

use Shinobi\Modules\AbandonCastle\AbandonCastle;
use Shinobi\Modules\AbandonCastle\FightController;

require_once __DIR__ . '/abandoncastle/src/AbandonCastle.php';
require_once __DIR__ . '/abandoncastle/src/FightController.php';
require_once __DIR__ . '/abandoncastle/src/MazeRepository.php';

function abandoncastle_getmoduleinfo(): array
{
    return (new AbandonCastle())->getModuleInfo();
}

function abandoncastle_install(): bool
{
    return (new AbandonCastle())->install();
}

function abandoncastle_uninstall(): bool
{
    return (new AbandonCastle())->uninstall();
}

function abandoncastle_dohook(string $hookName, array $args): array
{
    return (new AbandonCastle())->doHook($hookName, $args);
}

function abandoncastle_runevent(string $type): void
{
    (new AbandonCastle())->runEvent($type);
}

function abandoncastle_run(): void
{
    (new AbandonCastle())->run();
}

/**
 * Handle combat operations within the Abandoned Castle.
 *
 * Delegates the operation to the FightController which expects the
 * current user session to contain combat related fields such as
 * attack, defense, level and hitpoints. Depending on the outcome the
 * session will be updated with gold, experience or death information.
 */
function abandoncastle_fight(string $op): void
{
    (new FightController())->dispatch($op);
}
