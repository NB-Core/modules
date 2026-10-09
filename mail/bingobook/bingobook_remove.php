<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

function bingobook_remove() {
        global $session;

        $acctId = (int) httpget('ac');

        $conn          = Database::getDoctrineConnection();
        $accountsTable = Database::prefix('accounts');

        $row = $conn->executeQuery(
                "SELECT name FROM {$accountsTable} WHERE acctid = :acctid AND locked = 0",
                [
                        'acctid' => $acctId,
                ],
                [
                        'acctid' => ParameterType::INTEGER,
                ]
        )->fetchAssociative();

        $removed = ($acctId > 0) ? bingobook_delete($acctId) : 0;

        if ($removed) {
                $info = translate_inline("That user has been removed.");
        } else {
                $info = translate_inline("No user found! Report this error.");
        }

        if ($row) {
                $info = sprintf_translate("%s has been removed.`n`n", $row['name']);
        }

        output_notl($info);
}
?>