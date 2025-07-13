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

    /**
     * Copy untranslated texts directly to the translation table.
     *
     * @param string $language  Target language
     * @param string $namespace Namespace of the texts
     * @param array  $texts     Array of untranslated texts (URL encoded)
     * @param string $author    Saving author
     * @param string $version   Game version
     *
     * @return bool True if every row was copied successfully
     */
    public static function copyCheckedTranslations(string $language, string $namespace, array $texts, string $author, string $version): bool {
        $success = true;
        foreach ($texts as $text) {
            $intext = addslashes(rawurldecode($text));
            $insert = self::createTranslation($language, $namespace, $intext, $intext, $author, $version);
            $delete = self::deleteUntranslated($language, $namespace, $intext);
            invalidatedatacache("translations-" . $namespace . "-" . $language);
            if (!$insert || !$delete) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Save multiple translations at once.
     *
     * @param string $language  Target language
     * @param string $namespace Default namespace if none is supplied per row
     * @param array  $inTexts   Texts to translate
     * @param array  $outTexts  Provided translations
     * @param array  $nameTexts Namespaces per row
     * @param array  $ids       Existing translation ids
     * @param string $author    Saving author
     * @param string $version   Game version
     *
     * @return bool True if all translations were processed successfully
     */
    public static function saveBatchTranslations(
        string $language,
        string $namespace,
        array $inTexts,
        array $outTexts,
        array $nameTexts,
        array $ids,
        string $author,
        string $version
    ): bool {
        $ok = true;
        foreach ($inTexts as $key => $text) {
            if ($outTexts[$key] !== '') {
                $ns = $nameTexts[$key] ?? $namespace;
                $out = $outTexts[$key];
                if (!empty($ids[$key])) {
                    $sql = "UPDATE " . db_prefix("translations") .
                           " SET outtext='$out',author='$author',version='$version' WHERE tid={$ids[$key]};";
                    $result1 = db_query($sql);
                } else {
                    $result1 = self::createTranslation($language, $ns, $text, $out, $author, $version);
                }
                $result2 = self::deleteUntranslated($language, $ns, $text);
                $cacheKeys["translations-" . $ns . "-" . $language] = true;
                if (!$result1 || !$result2) {
                    $ok = false;
                }
            }
        }
        foreach (array_keys($cacheKeys) as $cacheKey) {
            invalidatedatacache($cacheKey);
        }
        return $ok;
    }
}
?>
