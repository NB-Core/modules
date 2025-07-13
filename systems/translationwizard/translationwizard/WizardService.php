<?php
declare(strict_types=1);

class WizardService {
    public static function ensureArray($value): array {
        if (is_array($value)) {
            return $value;
        }
        return ($value !== null && $value !== '') ? array($value) : array();
    }

    public static function createTranslation(string $language, string $namespace, string $intext, string $outtext, string $author, string $version) {
        $sql = "INSERT INTO " . db_prefix("translations") . " (language,uri,intext,outtext,author,version) VALUES" .
               " ('$language','$namespace','$intext','$outtext','$author','$version')";
        return db_query($sql);
    }

    public static function deleteUntranslated(string $language, string $namespace, string $intext) {
        $sql = "DELETE FROM " . db_prefix("untranslated") . " WHERE intext = '$intext' AND language = '$language' AND namespace = '$namespace'";
        return db_query($sql);
    }

    public static function saveTranslation(string $language, string $namespace, string $intext, string $outtext, string $author, string $version): bool {
        $insert = self::createTranslation($language, $namespace, $intext, $outtext, $author, $version);
        $delete = self::deleteUntranslated($language, $namespace, $intext);
        invalidatedatacache("translations-" . $namespace . "-" . $language);
        return (bool)$insert && (bool)$delete;
    }
}
?>
