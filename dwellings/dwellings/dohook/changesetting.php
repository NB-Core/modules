<?php
	if ($args['setting'] == "villagename") {
		\Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement(
			"UPDATE ".\Lotgd\MySQL\Database::prefix("dwellings")." SET location = :new WHERE location = :old",
			['new' => $args['new'], 'old' => $args['old']]
		);
	}
// Added this because of a new setting...
	if ($args['setting'] == "logoutlocation") {
		\Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement(
			"UPDATE ".\Lotgd\MySQL\Database::prefix("accounts")." SET location = :new WHERE location = :old",
			['new' => $args['new'], 'old' => $args['old']]
		);
	}
?>