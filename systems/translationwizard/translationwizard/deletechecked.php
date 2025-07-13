<?php
declare(strict_types=1);
foreach($transintext as $key=>$trans) {
	if ($transintext[$key]<>"")
		{
		$intext = addslashes(rawurldecode($transintext[$key]));
		//debug($intext);
		$sql = "DELETE FROM " . db_prefix("untranslated") . " WHERE BINARY intext = '$intext' AND language = '$languageschema' AND namespace = '$namespace'";
		db_query($sql);
		}
}
?>
