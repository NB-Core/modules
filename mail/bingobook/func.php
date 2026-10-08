<?php

use Doctrine\DBAL\ParameterType;
use Lotgd\MySQL\Database;

function bingobook_clear($id=false) {
        global $session;

        $acctid = ($id === false) ? (int) $session['user']['acctid'] : (int) $id;

        $conn        = Database::getDoctrineConnection();
        $table       = Database::prefix('bingobook');

        return $conn->executeStatement(
                "DELETE FROM {$table} WHERE bingoid = :bingoid OR userid = :userid",
                [
                        'bingoid' => $acctid,
                        'userid'  => $acctid,
                ],
                [
                        'bingoid' => ParameterType::INTEGER,
                        'userid'  => ParameterType::INTEGER,
                ]
        );
}


function bingobook_getbingo($bingoid=false) {
        global $session;

        $bingoId = ($bingoid === false) ? (int) $session['user']['acctid'] : (int) $bingoid;

        $conn           = Database::getDoctrineConnection();
        $bingobookTable = Database::prefix('bingobook');
        $accountsTable  = Database::prefix('accounts');

        $result = $conn->executeQuery(
                "SELECT a.name AS username, b.userid AS acctid, b.entrydate AS entrydate, b.comment AS comment
                        FROM {$bingobookTable} AS b
                        LEFT JOIN {$accountsTable} AS a ON b.userid = a.acctid
                        WHERE b.bingoid = :bingoid
                        ORDER BY username ASC",
                [
                        'bingoid' => $bingoId,
                ],
                [
                        'bingoid' => ParameterType::INTEGER,
                ]
        );

        return $result->fetchAllAssociative();
}

function bingobook_massget($userid=false) {
        global $session;

        $userId = ($userid === false) ? (int) $session['user']['acctid'] : (int) $userid;

        $conn     = Database::getDoctrineConnection();
        $table    = Database::prefix('bingobook');

        $result = $conn->executeQuery(
                "SELECT * FROM {$table} WHERE userid = :userid",
                [
                        'userid' => $userId,
                ],
                [
                        'userid' => ParameterType::INTEGER,
                ]
        );

        return $result->fetchAllAssociative();
}

function bingobook_massgetid($userid=false) {
        global $session;

        $userId = ($userid === false) ? (int) $session['user']['acctid'] : (int) $userid;

        $conn     = Database::getDoctrineConnection();
        $table    = Database::prefix('bingobook');

        $result = $conn->executeQuery(
                "SELECT bingoid FROM {$table} WHERE userid = :userid",
                [
                        'userid' => $userId,
                ],
                [
                        'userid' => ParameterType::INTEGER,
                ]
        );

        return array_map('intval', array_column($result->fetchAllAssociative(), 'bingoid'));
}

function bingobook_massgetfull($userid=false) {
        global $session;

        $userId = ($userid === false) ? (int) $session['user']['acctid'] : (int) $userid;

        $conn           = Database::getDoctrineConnection();
        $bingobookTable = Database::prefix('bingobook');
        $accountsTable  = Database::prefix('accounts');

        $result = $conn->executeQuery(
                "SELECT b.*, a.name AS bingoname, a.login AS bingologin, a.alive AS bingoalive, a.loggedin AS bingologgedin, a.laston AS bingolaston, a.location AS bingolocation
                        FROM {$bingobookTable} AS b
                        LEFT JOIN {$accountsTable} AS a ON b.bingoid = a.acctid
                        WHERE b.userid = :userid
                        ORDER BY bingologin ASC",
                [
                        'userid' => $userId,
                ],
                [
                        'userid' => ParameterType::INTEGER,
                ]
        );

        return $result->fetchAllAssociative();
}

function bingobook_getcomment($bingoid,$userid=false) {
        global $session;

        $bingoId = (int) $bingoid;
        $userId  = ($userid === false) ? (int) $session['user']['acctid'] : (int) $userid;

        $conn  = Database::getDoctrineConnection();
        $table = Database::prefix('bingobook');

        $result = $conn->executeQuery(
                "SELECT comment FROM {$table} WHERE bingoid = :bingoid AND userid = :userid LIMIT 1",
                [
                        'bingoid' => $bingoId,
                        'userid'  => $userId,
                ],
                [
                        'bingoid' => ParameterType::INTEGER,
                        'userid'  => ParameterType::INTEGER,
                ]
        );

        $row = $result->fetchAssociative();

        return $row['comment'] ?? '';
}

function bingobook_get($bingoid,$userid=false) {
        global $session;

        $bingoId = (int) $bingoid;
        $userId  = ($userid === false) ? (int) $session['user']['acctid'] : (int) $userid;

        $conn  = Database::getDoctrineConnection();
        $table = Database::prefix('bingobook');

        return $conn->executeQuery(
                "SELECT * FROM {$table} WHERE bingoid = :bingoid AND userid = :userid LIMIT 1",
                [
                        'bingoid' => $bingoId,
                        'userid'  => $userId,
                ],
                [
                        'bingoid' => ParameterType::INTEGER,
                        'userid'  => ParameterType::INTEGER,
                ]
        )->fetchAssociative();
}

function bingobook_getfull($bingoid,$userid=false) {
        global $session;

        $bingoId = (int) $bingoid;
        $userId  = ($userid === false) ? (int) $session['user']['acctid'] : (int) $userid;

        $conn           = Database::getDoctrineConnection();
        $bingobookTable = Database::prefix('bingobook');
        $accountsTable  = Database::prefix('accounts');

        return $conn->executeQuery(
                "SELECT b.*, a.name AS bingoname, a.login AS bingologin, a.alive AS bingoalive, a.loggedin AS bingologgedin, a.laston AS bingolaston, a.location AS bingolocation
                        FROM {$bingobookTable} AS b
                        LEFT JOIN {$accountsTable} AS a ON b.bingoid = a.acctid
                        WHERE b.bingoid = :bingoid AND b.userid = :userid
                        ORDER BY bingologin ASC
                        LIMIT 1",
                [
                        'bingoid' => $bingoId,
                        'userid'  => $userId,
                ],
                [
                        'bingoid' => ParameterType::INTEGER,
                        'userid'  => ParameterType::INTEGER,
                ]
        )->fetchAssociative();
}

function bingobook_delete($bingoid,$userid=false) {
        global $session;

        $bingoId = (int) $bingoid;
        $userId  = ($userid === false) ? (int) $session['user']['acctid'] : (int) $userid;

        $conn  = Database::getDoctrineConnection();
        $table = Database::prefix('bingobook');

        $affected = $conn->executeStatement(
                "DELETE FROM {$table} WHERE bingoid = :bingoid AND userid = :userid LIMIT 1",
                [
                        'bingoid' => $bingoId,
                        'userid'  => $userId,
                ],
                [
                        'bingoid' => ParameterType::INTEGER,
                        'userid'  => ParameterType::INTEGER,
                ]
        );

        invalidatedatacache("bingobook-massget-$userId");

        return $affected;
}

function bingobook_change($bingoid,$userid,$comment) {
        $bingoId = (int) $bingoid;
        $userId  = (int) $userid;

        $conn  = Database::getDoctrineConnection();
        $table = Database::prefix('bingobook');

        $affected = $conn->executeStatement(
                "UPDATE {$table} SET comment = :comment WHERE bingoid = :bingoid AND userid = :userid",
                [
                        'comment' => $comment,
                        'bingoid' => $bingoId,
                        'userid'  => $userId,
                ],
                [
                        'comment' => ParameterType::STRING,
                        'bingoid' => ParameterType::INTEGER,
                        'userid'  => ParameterType::INTEGER,
                ]
        );

        invalidatedatacache("bingobook-massget-$userId");

        return $affected;
}

function bingobook_insert($bingoid,$userid=false,$comment=false,$date=false) {
        global $session;

        $bingoId = (int) $bingoid;
        $userId  = ($userid === false) ? (int) $session['user']['acctid'] : (int) $userid;
        $text    = ($comment === false) ? '' : $comment;
        $entry   = ($date === false) ? date("Y:m:d H:i:s",strtotime("now")) : $date;

        $conn  = Database::getDoctrineConnection();
        $table = Database::prefix('bingobook');

        $affected = $conn->executeStatement(
                "INSERT INTO {$table} (bingoid, userid, comment, entrydate) VALUES (:bingoid, :userid, :comment, :entrydate)",
                [
                        'bingoid'   => $bingoId,
                        'userid'    => $userId,
                        'comment'   => $text,
                        'entrydate' => $entry,
                ],
                [
                        'bingoid'   => ParameterType::INTEGER,
                        'userid'    => ParameterType::INTEGER,
                        'comment'   => ParameterType::STRING,
                        'entrydate' => ParameterType::STRING,
                ]
        );

        invalidatedatacache("bingobook-massget-$userId");

        return $affected;
}



?>