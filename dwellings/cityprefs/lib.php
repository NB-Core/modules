<?php
function get_cityprefs_module($lookup,$value,$player=false){
	$conn = \Lotgd\MySQL\Database::getDoctrineConnection();
	if($player>0){
		$row1 = $conn->fetchAssociative(
			"select location from ".\Lotgd\MySQL\Database::prefix("accounts")." where acctid = :acctid",
			['acctid' => (int) $player]
		);
		$lookup="cityname";
		$value=$row1['location'] ?? null;
	}

	if($lookup=='cityid'){
		$where="cityid = :value";
		$value=(int) $value;
	}else{
		$where="cityname = :value";
		$value=(string) $value;
	}

	$row = $conn->fetchAssociative(
		"select module from ".\Lotgd\MySQL\Database::prefix("cityprefs")." where $where",
		['value' => $value]
	);
	return $row['module'] ?? null;
}

function get_cityprefs_cityid($lookup,$value,$player=false){
	$conn = \Lotgd\MySQL\Database::getDoctrineConnection();
	if($player>0){
		$row1 = $conn->fetchAssociative(
			"select location from ".\Lotgd\MySQL\Database::prefix("accounts")." where acctid = :acctid",
			['acctid' => (int) $player]
		);
		$lookup="cityname";
		$value=$row1['location'] ?? null;
	}

	if($lookup=='module'){$where="module = :value";}
	else{$where="cityname = :value";}

	$row = $conn->fetchAssociative(
		"select cityid from ".\Lotgd\MySQL\Database::prefix("cityprefs")." where $where",
		['value' => (string) $value]
	);
	if ($row === false) {
		return false;
	} else {
		return $row['cityid'];
	}
}

function get_cityprefs_cityname($lookup,$value,$player=false){
	$conn = \Lotgd\MySQL\Database::getDoctrineConnection();
	if($player>0){
		$row1 = $conn->fetchAssociative(
			"select location from ".\Lotgd\MySQL\Database::prefix("accounts")." where acctid = :acctid",
			['acctid' => (int) $player]
		);
		return $row1['location'] ?? null;
	}

	if($lookup=='module'){
		$where="module = :value";
		$value=(string) $value;
	}else{
		$where="cityid = :value";
		$value=(int) $value;
	}

	$row = $conn->fetchAssociative(
		"select cityname from ".\Lotgd\MySQL\Database::prefix("cityprefs")." where $where",
		['value' => $value]
	);
	return $row['cityname'] ?? null;
}
?>
