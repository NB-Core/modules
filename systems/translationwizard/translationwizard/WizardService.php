<?php
declare(strict_types=1);

class WizardService {
    /**
     * Normalize input into an array.
     *
     * @param mixed $value Scalar or array value to wrap
     *
     * @return array The provided value as an array
     */
    public static function ensureArray(mixed $value): array {
        if (is_array($value)) {
            return $value;
        }
        return ($value !== null && $value !== '') ? array($value) : array();
    }

    /**
     * Insert a translation row.
     *
     * Values are manually escaped because the db_query helper has no
     * parameterized query support.
     *
     * @param string $language Target language code
     * @param string $namespace Namespace/URI of the text
     * @param string $intext Original text
     * @param string $outtext Translated text
     * @param string $author Saving author
     * @param string $version Game version
     *
     * @return resource|bool Result of db_query()
     */
    public static function createTranslation(string $language, string $namespace, string $intext, string $outtext, string $author, string $version) {
        // db_query() has no support for parameterized queries, so manually escape values
        $language = addslashes($language);
        $namespace = addslashes($namespace);
        $intext = addslashes($intext);
        $outtext = addslashes($outtext);
        $author = addslashes($author);
        $version = addslashes($version);
        $sql = "INSERT INTO " . db_prefix("translations") . " (language,uri,intext,outtext,author,version) VALUES" .
               " ('$language','$namespace','$intext','$outtext','$author','$version')";
        return db_query($sql);
    }

    /**
     * Remove a row from the untranslated table.
     *
     * Values are manually escaped because parameterized queries are not
     * available.
     *
     * @param string $language Target language code
     * @param string $namespace Namespace/URI of the text
     * @param string $intext Original untranslated text
     *
     * @return resource|bool Result of db_query()
     */
    public static function deleteUntranslated(string $language, string $namespace, string $intext) {
        // Inputs may originate from user data; escape to prevent SQL injection
        // due to lack of parameterized query support
        $language = addslashes($language);
        $namespace = addslashes($namespace);
        $intext = addslashes($intext);
        $sql = "DELETE FROM " . db_prefix("untranslated") . " WHERE intext = '$intext' AND language = '$language' AND namespace = '$namespace'";
        return db_query($sql);
    }

    /**
     * Convenience wrapper to insert a translation and remove the untranslated row.
     *
     * Manual escaping occurs inside the called methods since no parameterized
     * queries are available.
     *
     * @param string $language Target language code
     * @param string $namespace Namespace/URI of the text
     * @param string $intext Original text
     * @param string $outtext Translated text
     * @param string $author Saving author
     * @param string $version Game version
     *
     * @return bool True on success
     */
    public static function saveTranslation(string $language, string $namespace, string $intext, string $outtext, string $author, string $version): bool {
        $insert = self::createTranslation($language, $namespace, $intext, $outtext, $author, $version);
        $delete = self::deleteUntranslated($language, $namespace, $intext);
        invalidatedatacache("translations-" . $namespace . "-" . $language);
        return (bool)$insert && (bool)$delete;
    }

    /**
     * Copy untranslated texts directly to the translation table.
     *
     * Manual escaping is performed before issuing SQL statements because no
     * parameterized query mechanism is available.
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
     * Manual escaping of values is required for the generated SQL queries
     * because the project lacks support for parameterized statements.
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
                    // Manually escape because there is no parameterized query API
                    $outEsc = addslashes($out);
                    $authorEsc = addslashes($author);
                    $versionEsc = addslashes($version);
                    $sql = "UPDATE " . db_prefix("translations") .
                           " SET outtext='$outEsc',author='$authorEsc',version='$versionEsc' WHERE tid={$ids[$key]};";
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

    /**
     * Delete selected untranslated rows.
     *
     * @param string $language  Target language
     * @param string $namespace Namespace of the texts
     * @param array  $texts     Array of rawurlencoded texts to delete
     *
     * @return void
     */
    public static function deleteCheckedRows(string $language, string $namespace, array $texts): void {
        foreach ($texts as $text) {
            if ($text !== '') {
                $intext = addslashes(rawurldecode($text));
                $sql = "DELETE FROM " . db_prefix("untranslated") .
                       " WHERE BINARY intext = '$intext' AND language = '$language' AND namespace = '$namespace'";
                db_query($sql);
            }
        }
    }

    /**
     * Toggle the view preference and redirect back to the caller.
     *
     * @param bool   $currentView Current view flag
     * @param string $from        Query string to redirect back to
     *
     * @return void
     */
    public static function toggleView(bool $currentView, string $from): void {
        set_module_pref('view', !$currentView, 'translationwizard');
        redirect("runmodule.php?$from");
    }

    /**
     * Retrieve rows without a namespace from the untranslated table.
     *
     * @return resource|bool Result of db_query()
     */
    private static function getEmptyNamespaceRows() {
        $sql = "SELECT intext, language FROM " . db_prefix('untranslated') .
               " WHERE namespace='' GROUP BY BINARY intext, language";
        return db_query($sql);
    }

    /**
     * Delete all rows without a namespace from the untranslated table.
     *
     * @return resource|bool Result of db_query()
     */
    private static function deleteEmptyNamespaceRows() {
        $sql = "DELETE FROM " . db_prefix('untranslated') . " WHERE namespace=''";
        return db_query($sql);
    }

    /**
     * Handle deletion of rows without a namespace.
     *
     * This replicates the legacy deleteempty.php behaviour.
     *
     * @param string $mode   Current mode
     * @param int    $page   Page size from module settings
     * @param string $coding Character set used for output
     *
     * @return void
     */
    public static function deleteEmpty(string $mode, int $page, string $coding): void {
        if (httppost('listing')) {
            $mode = 'listing';
        }
        if (httppost('deleteall')) {
            $mode = 'delete';
        }

        switch ($mode) {
            case 'del':
                $intext = rawurldecode(httpget('intext'));
                $language = httpget('deletelanguage');
                $sql = "DELETE FROM " . db_prefix('untranslated') .
                       " WHERE intext = '$intext' AND namespace='' AND language='$language'";
                if ($intext !== '') {
                    db_query($sql);
                }
                redirect("runmodule.php?module=translationwizard&op=deleteempty&mode=listing");
                break;

            case 'delete':
                $result = self::deleteEmptyNamespaceRows();
                output("`bOperation commenced, %s rows found and deleted`b`n`n", db_affected_rows($result));
                break;

            case 'listing':
                $result = self::getEmptyNamespaceRows();
                output("`n`n %s rows have been found with no namespace in your untranslated table.`n`n", db_num_rows($result));
                $i = 0;
                output("`n`nFollowing rows have no namespace:");
                tw_table_open([
                    translate_inline('Language'),
                    translate_inline('Namespace'),
                    translate_inline('Original'),
                    translate_inline('Actions')
                ]);
                while ($row = db_fetch_assoc($result)) {
                    $i++;
                    $delete = "<a href='runmodule.php?module=translationwizard&op=deleteempty&mode=del&intext=" . rawurlencode($row['intext']) . "&deletelanguage=" . $row['language'] . "'>" . translate_inline('Delete') . "</a>";
                    addnav('', "runmodule.php?module=translationwizard&op=deleteempty&mode=del&intext=" . rawurlencode($row['intext']) . "&deletelanguage=" . $row['language']);
                    tw_table_row([
                        htmlentities($row['language'], ENT_COMPAT, $coding),
                        htmlentities($row['namespace'], ENT_COMPAT, $coding),
                        htmlentities($row['intext'], ENT_COMPAT, $coding),
                        $delete
                    ], $i%2==1);
                    if ($i > $page) {
                        break;
                    }
                }
                tw_table_close();
                break;

            default:
                $result = self::getEmptyNamespaceRows();
                tw_form_open('deleteempty&mode=delete');
                addnav('', 'runmodule.php?module=translationwizard&op=deleteempty&mode=delete');
                rawoutput("<input type='hidden' name='op' value='check'>");
                output("`n`n %s rows have been found with no namespace in your untranslated table.`n`n", db_num_rows($result));
                if (db_num_rows($result) == 0) {
                    output("Congratulations! Your untranslated table does not have any rows with an empty namespace!");
                    tw_form_close();
                    break;
                }
                output("What do you want to do?`n`n`n`n");
                rawoutput("<input type='submit' name='deleteall' value='" . translate_inline('Delete multiple automatically') . "' class='button'>");
                rawoutput("<input type='submit' name='listing' value='" . translate_inline('Delete manually') . "' class='button'>");
                tw_form_close();
                output("`b`i`$ Attention, no additional confirmation`i`b`0");
                break;
        }
    }

    /**
     * Insert rows from the temporary pull table into translations.
     *
     * @param string $mode       Operation mode
     * @param string $namespace  Current namespace
     * @param string $language   Current language schema
     *
     * @return void
     */
    public static function insertCentral(string $mode, string $namespace, string $language): void {
        switch ($mode) {
            case 'continue':
                output('Commencing...');
                $sql = "DELETE from " . db_prefix('temp_translations') .
                       " using " . db_prefix('translations') .
                       " inner join " . db_prefix('temp_translations') .
                       " on  " . db_prefix('translations') . ".intext=" . db_prefix('temp_translations') . ".intext AND " .
                       db_prefix('translations') . ".language=" . db_prefix('temp_translations') . ".language AND " .
                       db_prefix('translations') . ".uri=" . db_prefix('temp_translations') . ".uri;";
                $result = db_query($sql);
                debug('Result for the delete:' . $result);
                $sql = "Select language,uri,intext,outtext,author,version from " . db_prefix('temp_translations') . ";";
                $result = db_query($sql);
                if (db_num_rows($result) <> 0) {
                    $copyrows = "INSERT INTO " . db_prefix('translations') . " (language, uri, intext, outtext, author, version) VALUES ";
                    while ($row = db_fetch_assoc($result)) {
                        $copyrows .= "('" . $row['language'] . "','" . $row['uri'] . "','" . addslashes($row['intext']) . "','" . addslashes($row['outtext']) . "','" . addslashes($row['author']) . "','" . $row['version'] . "'),";
                    }
                    $res = db_query(substr($copyrows, 0, -1) . ';');
                    invalidatedatacache('translations-' . $namespace . '-' . $language);
                    db_query('TRUNCATE ' . db_prefix('temp_translations') . ';');
                    output("%s rows have been inserted and the pulled translations table has been cleared.", db_num_rows($result));
                    output_notl("`n`n");
                    output("The insert has been %s.", ($res == 1 ? translate_inline('successful') : translate_inline('`$ not successful`0')));
                    output_notl("`n");
                    output("Please `%fix`0 your untranslated table now.");
                } else {
                    output('The pulled translations is now empty, all pulled rows are already in your translations table.');
                }
                break;

            default:
                output('You may hereby insert `b`$ ALL `0`b rows from the pulled translations table who are not in your current translations yet.');
                output_notl(' ');
                output('This would be wise if you just installed the game and pulled a few translations down.');
                output_notl('`n`n');
                $sql = 'Select * from ' . db_prefix('temp_translations') . ';';
                $result = db_query($sql);
                if (db_num_rows($result)) {
                    output('You have %s entries in your pulled translations table.', db_num_rows($result));
                    output_notl('`n');
                    output('This may take some time.');
                    output_notl('`n`n');
                    tw_form_open('insert_central&mode=continue');
                    addnav('', 'runmodule.php?module=translationwizard&op=insert_central&mode=continue');
                    rawoutput("<input type='submit' name='continue' value='" . translate_inline('Commence the process') . "' class='button'>");
                    tw_form_close();
                } else {
                    output('The pulled translations is empty, there is nothing to do!');
                }
                break;
        }
    }
}
?>
