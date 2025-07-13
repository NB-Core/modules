<?php
declare(strict_types=1);

class TranslationWizard {
    /**
     * Parse a PHP file and return all translatable strings.
     *
     * @param string $filepath          Path to the file being scanned
     * @param bool   $debug             Output debug information when true
     * @param string|false $standard_tlschema Default tlschema if not auto-detected
     *
     * @return array Array of ['text' => string, 'schema' => string] entries
     */
    public static function scanFile(string $filepath, bool $debug=false, $standard_tlschema=false) {
        if(!is_file($filepath)) {
            throw new RuntimeException("Fatal Error: Could not find file at path: " . $filepath);
        }
        $str = join("", file($filepath));
        if($standard_tlschema === false) {
            $posi = strrpos($filepath, "/");
            $name = substr($filepath, $posi+1, strlen($filepath)-$posi-5);
            if(!$posi) {
                $name = substr($filepath, 0, strrpos($filepath, "."));
            }
            if(strstr($filepath, "modules")) {
                $name = "module-".$name;
            }
            debug("Used a parsed tlschema:".$name);
            $standard_tlschema = $name;
        }
        $start = explode(" ", microtime());
        $escape_flag = false;
        $escape_justset = false;
        $open_bracket_flag = false;
        $pretext_flag = false;
        $intext_flag = false;
        $translate_inline_inline_select_flag = false;
        $current_translate = "";
        $current_tlschema = $standard_tlschema;
        $unreadable_tlschema = "unreadable_tlschema";
        $current_outputtype = "";
        $temp_tlschema = "";
        $tlschema_stack = array();
        $return = array();
        $line = 1;
        $output_len = strlen("output");
        $output_notl_len = strlen("output_notl");
        $translate_inline_len = strlen("translate_inline");
        $addnav_len = strlen("addnav");
        $tlschema_len = strlen("tlschema");
        $addnews_len = strlen("addnews");
        $page_header_len = strlen("page_header");
        $sprintf_translate_len = strlen("sprintf_translate");
        $output_skip = $output_len - 1;
        $output_notl_skip = $output_notl_len - 1;
        $translate_inline_skip = $translate_inline_len - 1;
        $addnav_skip = $addnav_len - 1;
        $tlschema_skip = $tlschema_len - 1;
        $addnews_skip = $addnews_len - 1;
        $page_header_skip = $page_header_len - 1;
        $sprintf_translate_skip = $sprintf_translate_len - 1;
        for($i=0;$i<strlen($str);$i++) {
            if($str[$i] == "\n") {
                $line++;
            }
            if($intext_flag == false) {
                self::skipCommentary($str, $i, $line);
                if($pretext_flag == false) {
                    if(substr($str,$i,$output_len) == "output") {
                        if(substr($str,$i,$output_notl_len) != "output_notl") {
                            if(substr($str,$i-3,$output_len+3) != "rawoutput") {
                                if($debug) {
                                    debug("<br>Line $line: 'output' found");
                                }
                                $current_outputtype = "output";
                                $pretext_flag = true;
                            }
                            $i += $output_skip;
                        } else {
                            $i += $output_notl_skip;
                        }
                    }
                    if(substr($str,$i,$translate_inline_len) == "translate_inline") {
                        if($debug) {
                            debug("<br>Line $line: 'translate_inline' found");
                        }
                        $current_outputtype = "translate_inline";
                        $pretext_flag = true;
                        $i += $translate_inline_skip;
                    }
                    if(substr($str,$i,$output_len) == "addnav") {
                        if($debug) {
                            debug("<br>Line $line: 'addnav' found");
                        }
                        $current_outputtype = "addnav";
                        $pretext_flag = true;
                        $i += $addnav_skip;
                    }
                    if(substr($str,$i,$tlschema_len) == "tlschema") {
                        if($debug) {
                            debug("<br>Line $line: 'tlschema' found");
                        }
                        $current_outputtype = "tlschema";
                        $pretext_flag = true;
                        $i += $tlschema_skip;
                    }
                    if(substr($str,$i,$addnews_len) == "addnews") {
                        if($debug) {
                            debug("<br>Line $line: 'addnews' found");
                        }
                        $current_outputtype = "addnews";
                        $pretext_flag = true;
                        $i += $output_skip;
                    }
                    if(substr($str,$i,$page_header_len) == "page_header") {
                        if($debug) {
                            debug("<br>Line $line: 'page_header' found");
                        }
                        $current_outputtype = "page_header";
                        $pretext_flag = true;
                        $i += $page_header_skip;
                    }
                    if(substr($str,$i,$sprintf_translate_len) == "sprintf_translate") {
                        if($debug) {
                            debug("<br>Line $line: 'sprintf_translate' found");
                        }
                        $current_outputtype = "sprintf_translate";
                        $pretext_flag = true;
                        $i += $sprintf_translate_skip;
                    }
                } else {
                    if($str[$i] == "(") {
                        $open_bracket_flag = true;
                    } else if($str[$i] == "\"") {
                        if($open_bracket_flag == true) {
                            if($debug) {
                                debug("<br>Line $line: Reading string from '$current_outputtype' started");
                            }
                            $intext_flag = true;
                            $pretext_flag = false;
                            $open_bracket_flag = false;
                        } else {
                            if($current_outputtype == "sprintf_translate") {
                                if($debug) {
                                    debug("<br>Line $line: No '(' found before ' \" '. Assuming an 'call_user_func_array' call. Skipping till next';'");
                                }
                                while ($str[$i] != ";") {
                                    $i++;
                                    self::skipCommentary($str,$i,$line);
                                }
                                $intext_flag = false;
                                $pretext_flag = false;
                                $open_bracket_flag = false;
                            } else {
                                if($debug) {
                                    debug("<br><b>Line $line: <big>Important:</big></b> No '(' before ' \" ' was found in a '$current_outputtype'. Parse Error ? (Very confusing) Skipping till next';'");
                                }
                                $intext_flag = false;
                                $pretext_flag = false;
                                $open_bracket_flag = false;
                            }
                        }
                    } else if($str[$i] == "$") {
                        if($current_outputtype == "translate_inline") {
                            if($debug) {
                                debug("<br>Line $line: Assuming an translate_inline(\$var?\"First\":\"Second\");");
                            }
                            $translate_inline_inline_select_flag = true;
                        } else if($current_outputtype == "tlschema") {
                            if($debug) {
                                debug("<br>Line $line: Unreadable tlschema. Pushing '$unreadable_tlschema' on tl_stack. ");
                            }
                            $current_tlschema = $unreadable_tlschema;
                            array_push($tlschema_stack,$unreadable_tlschema);
                            $pretext_flag = false;
                            $open_bracket_flag = false;
                        } else {
                            if($debug) {
                                debug("<br><b>Line $line: <big>Important:</big></b> Found an '$' in a '$current_outputtype'. Not translation ready ! Skipping till next';'");
                            }
                            while ($str[$i] != ";") {
                                $i++;
                                self::skipCommentary($str,$i,$line);
                            }
                            $pretext_flag = false;
                            $open_bracket_flag = false;
                        }
                    } else if($str[$i] == ";") {
                        if($current_outputtype == "tlschema") {
                            array_pop($tlschema_stack);
                            $temp_tlschema = array_pop($tlschema_stack);
                            if($temp_tlschema != false) {
                                $current_tlschema = $temp_tlschema;
                                if($debug) {
                                    debug("<br>Line $line: tlschema set back. Pulled from tl_stack : '$temp_tlschema' . ");
                                }
                                array_push($tlschema_stack,$current_tlschema);
                            } else {
                                $current_tlschema = $standard_tlschema;
                                if($debug) {
                                    debug("<br>Line $line: tlschema set back. tl_stack is empty setting du standart : '$standard_tlschema' . ");
                                }
                            }
                        } else if($current_outputtype == "translate_inline") {
                            if($translate_inline_inline_select_flag == true) {
                                $translate_inline_inline_select_flag = false;
                                $assume = "$array";
                            } else {
                                $assume = "array(\"...\",\"...\")";
                            }
                            if($debug) {
                                debug("<br>Line $line: Reached ';' Now assuming an translate_inline($assume). Can't be handled. Skipping");
                            }
                        } else if($current_outputtype == "page_header") {
                            if($debug) {
                                debug("<br>Line $line: Empty page_header. No Problem. Skipping");
                            }
                        } else {
                            if($debug) {
                                debug("<br>Line $line: Unexpected ';' Skipping current Scan.");
                            }
                        }
                        $pretext_flag = false;
                        $open_bracket_flag = false;
                    }
                }
            } else {
                if($str[$i] == "\"" && $escape_flag == false) {
                    if($current_translate == false) {
                        if($current_outputtype == "addnav") {
                            if($debug) {
                                debug("<br>Line $line: Empty Addnav. Assuming it works together with an HTML 'form' Tag");
                            }
                        } else if($current_outputtype == "page_header") {
                            if($debug) {
                                debug("<br>Line $line: Empty Addnav. No Problem. Skipping");
                            }
                        } else {
                            if($debug) {
                                debug("<br><b>Line $line: Empty '$current_outputtype'. Makes no sense... parser is very sad ... :(</b>");
                            }
                        }
                    } else if($current_outputtype == "tlschema") {
                        if($debug) debug("<br>Line $line: tlschema changed to '$current_translate' and pushed onto tl_stack");
                        $current_tlschema = $current_translate;
                        array_push($tlschema_stack,$current_tlschema);
                    } else if(self::alreadyInArray($return,$current_translate,$current_tlschema) == false ) {
                        if ($current_outputtype == "addnews" && strstr($current_translate,"%s")) $current_translate=str_replace("`%","`%%",$current_translate);
                        $return[] = array("text" => $current_translate, "schema" => $current_tlschema);
                    } else {
                        if($debug) {
                            debug("<br><b>Line $line: Found: $current_translate</b> --- tlschema: $current_tlschema");
                        }
                    }
                    $intext_flag = false;
                    $current_translate = "";
                    if($translate_inline_inline_select_flag == true) {
                        if($debug) {
                            debug("<br>Line $line: 2nd translate inline initalised");
                        }
                        $translate_inline_inline_select_flag = false;
                        $pretext_flag = true;
                        $open_bracket_flag = true;
                    }
                } else if($str[$i] == "\\") {
                    $escape_flag = true;
                    $escape_justset = true;
                } else if($str[$i] == "$" && $escape_flag == false) {
                    if($debug) {
                        debug("<br><b>Line $line: <big>Important:</big></b> Found an '$' in an opend '$current_outputtype'. Not translation ready ! Skipping till next';'");
                    }
                    while ($str[$i] != ";") {
                        $i++;
                        self::skipCommentary($str,$i,$line);
                    }
                    $intext_flag = false;
                } else {
                    $current_translate .= $str[$i];
                }
            }
            if($escape_flag == true) {
                if($escape_justset == true) {
                    $escape_justset = false;
                } else {
                    $escape_flag = false;
                }
            }
        }
        $end = explode(" ", microtime());
        $used_micro = $end[0] - $start[0];
        $used_sec = $end[1] - $start[1] + $used_micro;
        debug("Time needed : $used_sec & Lines done: $line");
        return $return;
    }

    /**
     * Insert untranslated strings into the database.
     *
     * @param array|string $delrows       Strings or rows to insert
     * @param string       $languageschema Target language schema
     * @param bool         $serialized     Whether input rows are serialized
     *
     * @return void
     */
    public static function insertFile($delrows, $languageschema, $serialized=false) {
        if(is_array($delrows)) {
            $insertrows = $delrows;
        } else {
            if($delrows) $insertrows = array($delrows);
            else $insertrows = array();
        }
        foreach($insertrows as $key=>$val) {
            if($serialized) {
                $val = unserialize(rawurldecode($val));
            }
            $sql = "Insert IGNORE INTO ".db_prefix("untranslated")." Values ('".addslashes($val['text'])."','$languageschema','".addslashes($val['schema'])."');";
            db_query($sql);
        }
    }

    /**
     * Advance the pointer past PHP comments while scanning a file.
     *
     * @param string $str  Entire file contents
     * @param int    &$i   Current index in the string (modified)
     * @param int    &$line Current line counter (modified)
     *
     * @return void
     */
    protected static function skipCommentary($str, &$i, &$line) {
        while($str[$i] == "/" && $str[$i] != "") {
            if($str[($i+1)] == "/") {
                while($str[$i] != "\n") {
                    $i++;
                }
                $line++;
            } else if($str[($i+1)] == "*") {
                while(($str[$i] != "*" || $str[($i+1)] != "/") && $str[$i] != "") {
                    if($str[$i] == "\n") {
                        $line++;
                    }
                    $i++;
                }
            } else {
                return;
            }
        }
    }

    /**
     * Check if a translation entry already exists in the given array.
     *
     * @param array  $array  Current collection of entries
     * @param string $text   Text string to compare
     * @param string $schema Associated schema
     *
     * @return bool True if an identical entry exists
     */
    protected static function alreadyInArray($array, $text, $schema) {
        foreach($array as $entry) {
            if($entry['text'] == $text && $entry['schema'] == $schema) {
                return true;
            }
        }
        return false;
    }

    /**
     * Display or return a list of scannable files.
     *
     * @param bool $dosubmit       When true, change events submit the form
     * @param int  $onlymodules    0 = all, 1 = modules folder, 2 = only modules
     * @param bool $showselectbox  Whether to output a &lt;select&gt; element
     * @param bool $mainmodulecheck If true, onChange uses modulecheck()
     *
     * @return array Array of valid file paths
     */
    public static function showValidFiles($dosubmit=true, $onlymodules=0, $showselectbox=true, $mainmodulecheck=false) {
        global $coding;
        require_once("lib/errorhandling.php");
        $url="";
        $outputfiles=array();
        $dir = str_replace("\\","/",dirname($url)."/");
        $subdir = str_replace("\\","/",dirname($_SERVER['SCRIPT_NAME'])."/");
        if ($subdir == "//") $subdir = "/";
        switch ($onlymodules) {
            case 2:
                $legal_start_dirs = array(
                    $subdir."modules/*" => 1,
                );
                break;
            case 1:
                $legal_start_dirs = array(
                    $subdir."modules/" => 1,
                );
                break;
            default:
                $legal_start_dirs = array(
                    $subdir."" => 1,
                    $subdir."lib/*" => 1,
                    $subdir."modules/*" => 1,
                );
        }
        $illegal_files = array(
            ($subdir=="//"?"/":$subdir)."dbconnect.php"=>"Contains sensitive information specific to this installation.",
        );
        $legal_files=array();
        $legal_dirs = array();
        foreach($legal_start_dirs as $dir=>$value) {
            if(!$value) continue;
            $sdir = substr($dir,strlen($subdir));
            if ($sdir == dirname($_SERVER['SCRIPT_NAME'])) $sdir ="";
            $base = "./$sdir";
            if (!strstr($base, "/*")) {
                array_push($legal_dirs, $base);
                continue;
            }
            $base = substr($base, 0, -2);
            array_push($legal_dirs, $base . "/");
            $legal_dirs=array_merge($legal_dirs,self::tree($base));
        }
        sort($legal_dirs);
        if ($dosubmit) $sub="onChange='this.form.submit()'";
        if ($mainmodulecheck) $sub="onChange='modulecheck()'";
        if ($showselectbox) rawoutput("<select name='lookfor' $sub >");
        foreach ($legal_dirs as $key) {
            $key1 = substr($key, 2);
            $key2 = "/" . $key1;
            $d = dir("$key");
            $files = array();
            if ($onlymodules==2) {
                $check=$subdir.str_replace(array("./"),array(""),$key)."*";
                if (array_key_exists($check,$legal_start_dirs)) continue;
            }
            while ($entry = $d->read()) {
                if (substr($entry,strrpos($entry,"."))==".php"){
                    array_push($files, "$entry");
                }
            }
            $d->close();
            asort($files);
            foreach($files as $entry) {
                if (isset($illegal_files["$key2$entry"]) && $illegal_files["$key2$entry"]!=""){
                    if ($illegal_files["$key2$entry"]=="X"){
                    }else{
                        if ($showselectbox) rawoutput("<li>$key1$entry");
                        $reason = translate_inline($illegal_files[$key2 . $entry]);
                        if ($showselectbox) output("&#151; This file cannot be viewed: %s", $reason, true);
                        if ($showselectbox)rawoutput("</li>\n");
                    }
                }else{
                    $namesp=$key1.$entry;
                    array_push($outputfiles,$namesp);
                    $selected = '';
                    if ($showselectbox) rawoutput("<option value='".htmlentities($namesp,ENT_COMPAT,$coding)."' $selected>".htmlentities($namesp,ENT_COMPAT,$coding)."</option>");
                    $legal_files["$key2$entry"]=true;
                }
            }
        }
        if ($showselectbox) {
            rawoutput("</select>");
            rawoutput("<input type='submit' class='button' name='dummy' value='". translate_inline("Start Scan") ."'>");
        }
        return $outputfiles;
    }

    /**
     * Recursively gather subdirectories below a given base path.
     *
     * @param string $base Base directory
     *
     * @return array List of directory paths
     */
    protected static function tree($base) {
        $d = dir("$base");
        $back=array();
        while($entry = $d->read()) {
            if ($entry[0] == '.') continue;
            if (substr($entry,strrpos($entry, '.')) == ".php") continue;
            $ndir = $base . "/" . $entry;
            $test = preg_replace("!^\./!", "//", $ndir);
            if (is_dir($ndir)) {
                $back=array_merge($back,self::tree($ndir));
                array_push($back, $ndir . "/");
            }
        }
        return $back;
    }
}
?>
