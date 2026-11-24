<?php
declare(strict_types=1);

class WizardService {
    /**
     * Supply shared defaults for fresh or older saved wizard searches.
     */
    public static function normalizeSearchQuery(mixed $query): array
    {
        $query = is_array($query) ? $query : [];
        $defaults = [
            'number' => 30, 'orderby' => 3, 'orderbyascdesc' => 0, 'exactly' => '',
            'tid' => '', 'language' => '', 'uri' => '', 'intext' => '',
            'outtext' => '', 'author' => '', 'version' => '',
        ];
        foreach (['language', 'uri', 'intext', 'outtext', 'author', 'version'] as $field) {
            $defaults['r' . $field] = '';
            $defaults['r' . $field . '2'] = '';
        }
        foreach ($defaults as $field => $default) {
            $query[$field] ??= $default;
        }
        $order = filter_var($query['orderby'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 6],
        ]);
        $number = filter_var($query['number'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 999],
        ]);
        $query['orderby'] = $order === false ? 3 : $order;
        $query['number'] = $number === false ? 30 : $number;

        return $query;
    }

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
     * Values are bound as named parameters.
     *
     * @param string $language Target language code
     * @param string $namespace Namespace/URI of the text
     * @param string $intext Original text
     * @param string $outtext Translated text
     * @param string $author Saving author
     * @param string $version Game version
     *
     * @return bool True when the statement executed
     */
    public static function createTranslation(string $language, string $namespace, string $intext, string $outtext, string $author, string $version): bool {
        $sql = "INSERT INTO " . \Lotgd\MySQL\Database::prefix("translations") . " (language,uri,intext,outtext,author,version) VALUES" .
               " (:language,:uri,:intext,:outtext,:author,:version)";
        \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement($sql, [
            'language' => $language,
            'uri' => $namespace,
            'intext' => $intext,
            'outtext' => $outtext,
            'author' => $author,
            'version' => $version,
        ]);
        return true;
    }

    /**
     * Remove a row from the untranslated table.
     *
     * Values are bound as named parameters.
     *
     * @param string $language Target language code
     * @param string $namespace Namespace/URI of the text
     * @param string $intext Original untranslated text
     *
     * @return bool True when the statement executed
     */
    public static function deleteUntranslated(string $language, string $namespace, string $intext): bool {
        $sql = "DELETE FROM " . \Lotgd\MySQL\Database::prefix("untranslated") . " WHERE intext = :intext AND language = :language AND namespace = :namespace";
        \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement($sql, [
            'intext' => $intext,
            'language' => $language,
            'namespace' => $namespace,
        ]);
        return true;
    }

    /**
     * Convenience wrapper to insert a translation and remove the untranslated row.
     *
     * Values are bound as parameters inside the called methods.
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
     * Values are bound as parameters by the called methods.
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
            // Kept as before: the escaped value is what createTranslation()/deleteUntranslated() receive and bind
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
     * Values are bound as named parameters.
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
        $cacheKeys = [];
        foreach ($inTexts as $key => $text) {
            if ($outTexts[$key] !== '') {
                $ns = $nameTexts[$key] ?? $namespace;
                $out = $outTexts[$key];
                if (!empty($ids[$key])) {
                    $sql = "UPDATE " . \Lotgd\MySQL\Database::prefix("translations") .
                           " SET outtext=:outtext,author=:author,version=:version WHERE tid=:tid";
                    \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement($sql, [
                        'outtext' => $out,
                        'author' => $author,
                        'version' => $version,
                        'tid' => (int) $ids[$key],
                    ]);
                    $result1 = true;
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
                $sql = "DELETE FROM " . \Lotgd\MySQL\Database::prefix("untranslated") .
                       " WHERE BINARY intext = :intext AND language = :language AND namespace = :namespace";
                // $namespace is the request value (slashed) that the old SQL used unescaped
                \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement($sql, [
                    'intext' => rawurldecode($text),
                    'language' => $language,
                    'namespace' => stripslashes($namespace),
                ]);
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
     * @return array<int, array<string, mixed>> Result rows
     */
    private static function getEmptyNamespaceRows(): array {
        $sql = "SELECT BINARY intext AS intext, language FROM " . \Lotgd\MySQL\Database::prefix('untranslated') .
               " WHERE namespace='' GROUP BY BINARY intext, language";
        return \Lotgd\MySQL\Database::getDoctrineConnection()->fetchAllAssociative($sql);
    }

    /**
     * Delete all rows without a namespace from the untranslated table.
     *
     * @return int Number of deleted rows
     */
    private static function deleteEmptyNamespaceRows(): int {
        $sql = "DELETE FROM " . \Lotgd\MySQL\Database::prefix('untranslated') . " WHERE namespace=''";
        return \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement($sql);
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
                $sql = "DELETE FROM " . \Lotgd\MySQL\Database::prefix('untranslated') .
                       " WHERE intext = :intext AND namespace='' AND language = :language";
                if ($intext !== '') {
                    // Request values (slashed) that the old SQL used unescaped
                    \Lotgd\MySQL\Database::getDoctrineConnection()->executeStatement($sql, [
                        'intext' => stripslashes($intext),
                        'language' => stripslashes($language),
                    ]);
                }
                redirect("runmodule.php?module=translationwizard&op=deleteempty&mode=listing");
                break;

            case 'delete':
                $affected = self::deleteEmptyNamespaceRows();
                output("`bOperation commenced, %s rows found and deleted`b`n`n", $affected);
                break;

            case 'listing':
                $rows = self::getEmptyNamespaceRows();
                output("`n`n %s rows have been found with no namespace in your untranslated table.`n`n", count($rows));
                $i = 0;
                output("`n`nFollowing rows have no namespace:");
                tw_table_open([
                    translate_inline('Language'),
                    translate_inline('Namespace'),
                    translate_inline('Original'),
                    translate_inline('Actions')
                ]);
                foreach ($rows as $row) {
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
                $rows = self::getEmptyNamespaceRows();
                tw_form_open('deleteempty&mode=delete');
                addnav('', 'runmodule.php?module=translationwizard&op=deleteempty&mode=delete');
                rawoutput("<input type='hidden' name='op' value='check'>");
                output("`n`n %s rows have been found with no namespace in your untranslated table.`n`n", count($rows));
                if (count($rows) == 0) {
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
                $conn = \Lotgd\MySQL\Database::getDoctrineConnection();
                $tmpTable = \Lotgd\MySQL\Database::prefix('temp_translations');
                $transTable = \Lotgd\MySQL\Database::prefix('translations');
                $result = $conn->executeStatement(
                    "DELETE from " . $tmpTable .
                    " using " . $transTable .
                    " inner join " . $tmpTable .
                    " on  " . $transTable . ".intext=" . $tmpTable . ".intext AND " .
                    $transTable . ".language=" . $tmpTable . ".language AND " .
                    $transTable . ".uri=" . $tmpTable . ".uri;"
                );
                debug('Result for the delete:' . $result);
                $rows = $conn->fetchAllAssociative("Select language,uri,intext,outtext,author,version from " . $tmpTable . ";");
                if (count($rows) <> 0) {
                    $insertSql = "INSERT INTO " . $transTable . " (language, uri, intext, outtext, author, version)" .
                                 " VALUES (:language, :uri, :intext, :outtext, :author, :version)";
                    $conn->transactional(static function (\Doctrine\DBAL\Connection $c) use ($insertSql, $rows): void {
                        foreach ($rows as $row) {
                            $c->executeStatement($insertSql, [
                                'language' => $row['language'],
                                'uri' => $row['uri'],
                                'intext' => $row['intext'],
                                'outtext' => $row['outtext'],
                                'author' => $row['author'],
                                'version' => $row['version'],
                            ]);
                        }
                    });
                    $res = true;
                    invalidatedatacache('translations-' . $namespace . '-' . $language);
                    $conn->executeStatement('TRUNCATE ' . $tmpTable . ';');
                    output("%s rows have been inserted and the pulled translations table has been cleared.", count($rows));
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
                $sql = 'Select * from ' . \Lotgd\MySQL\Database::prefix('temp_translations') . ';';
                $rows = \Lotgd\MySQL\Database::getDoctrineConnection()->fetchAllAssociative($sql);
                if (count($rows)) {
                    output('You have %s entries in your pulled translations table.', count($rows));
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

