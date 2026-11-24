<?php

function personalpetitions_getmoduleinfo() {
        $info = array(
                "name"=>"Personal Petition Categories",
                "version"=>"1.0",
                "author"=>"`2Oliver Brendel",
                "category"=>"Administrative",
                "download"=>"",
        );
        return $info;
}

function personalpetitions_install() {
        module_addhook_priority("petition-status",50);
        module_addhook("petitioncount");
        module_addhook("superuser");

        $table = db_prefix('petition_categories');

        require_once("lib/tabledescriptor.php");
        $petition_desc = array(
                'id' => array('name' => 'id', 'type' => 'int(11) unsigned', 'extra' => 'auto_increment'),
                'name' => array('name' => 'name', 'type' => 'varchar(255)'),
                'active' => array('name' => 'active', 'type' => 'tinyint(4) unsigned', 'default' => '1'),
                'important' => array('name' => 'important', 'type' => 'tinyint(4) unsigned', 'default' => '0'),
                'sortOrder' => array('name' => 'sortOrder', 'type' => 'int(11)', 'null' => '1'),
                'key-PRIMARY' => array('name' => 'PRIMARY', 'type' => 'primary key', 'unique' => '1', 'columns' => 'id'),
        );
        synctable($table, $petition_desc, true);

        $conn = \Lotgd\MySQL\Database::getDoctrineConnection();
        $count = $conn->fetchOne("SELECT COUNT(*) AS c FROM $table");
        if ((int) $count == 0) {
                $core = array(
                        0=>"`bUnhandled`b",
                        1=>"In-Progress",
                        2=>"`iClosed`i",
                        3=>"`!Informational`0",
                        4=>"`^Escalated`0",
                        5=>"`\$Top Level`0",
                        6=>"`%Bug`0",
                        7=>"`#Awaiting Points`0",
                );

                // MySQL does not allow direct insertion of id=0 into an AUTO_INCREMENT column,
                // as it treats 0 as a trigger to auto-generate the next value. Therefore, we
                // first insert the row without specifying the id, then update it to set id=0.
                $conn->executeStatement(
                        "INSERT INTO $table (name,active) VALUES (:name,1)",
                        ['name' => $core[0]]
                );
                $zeroid = (int) $conn->lastInsertId();
                $conn->executeStatement("UPDATE $table SET id=0 WHERE id=".$zeroid);

                // Insert remaining core statuses with explicit ids
                foreach ($core as $id=>$name) {
                        if ($id == 0) continue;
                        $conn->executeStatement(
                                "INSERT INTO $table (id,name,active) VALUES (:id,:name,1)",
                                ['id' => (int) $id, 'name' => $name]
                        );
                }

                // The previous version kept custom categories in this setting and
                // gave them the statuses 50, 51, ... in list order (empty entries
                // included). Keep those ids so existing petitions stay in their
                // category.
                $customIds = array();
                $list = get_module_setting('categories');
                if ($list != '') {
                        $id = 50;
                        foreach (explode(',', $list) as $cat) {
                                $cat = trim($cat);
                                if ($cat != '') {
                                        $conn->executeStatement(
                                                "INSERT INTO $table (id,name,active) VALUES (:id,:name,1)",
                                                ['id' => (int) $id, 'name' => $cat]
                                        );
                                        $customIds[] = $id;
                                }
                                $id++;
                        }
                }

                // Reset auto increment to next available id
                $maxid = $conn->fetchOne("SELECT MAX(id) AS maxid FROM $table");
                $next = (int)$maxid + 1;
                db_query("ALTER TABLE $table AUTO_INCREMENT=".$next); // DDL, int-only

                $conn->executeStatement(
                        "DELETE FROM ".\Lotgd\MySQL\Database::prefix('module_settings')." WHERE modulename='personalpetitions' AND setting='categories'"
                );

                // Ensure seeded rows have default values for new columns. The
                // petition counter only lists categories with a sortOrder, so give
                // every seeded one a position, in the order the counter of the
                // previous version used (custom categories first).
                $conn->executeStatement("UPDATE $table SET important = 0, sortOrder = NULL");
                $order = array_merge($customIds, array(5, 4, 0, 1, 3, 7, 6, 2));
                foreach ($order as $pos => $statusId) {
                        $conn->executeStatement(
                                "UPDATE $table SET sortOrder = :pos WHERE id = :id",
                                ['pos' => (int) $pos, 'id' => (int) $statusId]
                        );
                }
        }
        return true;
}

function personalpetitions_uninstall() {
        $table = db_prefix('petition_categories');
        db_query("DROP TABLE IF EXISTS $table");
        return true;
}


function personalpetitions_dohook($hookname, $args) {
        global $session;
        switch ($hookname) {
               case "petitioncount":
                       $sql = "SELECT pc.id, pc.name, pc.important, pc.sortOrder, COUNT(p.petitionid) AS c FROM "
                               . db_prefix('petition_categories') . " pc LEFT JOIN " . db_prefix('petitions')
                               . " p ON pc.id = p.status WHERE pc.sortOrder IS NOT NULL GROUP BY pc.id, pc.name, pc.important, pc.sortOrder ORDER BY pc.sortOrder ASC";
                       $result = db_query($sql); //_cached($sql, "petition_counts",60);
                       $parts = array();
                       while ($row = db_fetch_assoc($result)) {
                               $count = (int)$row['c'];
                               $formatted = (string)$count;
                               if ($row['important']) {
                                       $formatted = "`b<span style='font-size:2.5em'>{$formatted}</span>`b";
                               }

                               $color = '';
                               $name = $row['name'];
                               $len = strlen($name);
                               for ($i = 0; $i < $len; $i++) {
                                       if ($name[$i] === '`' && ($i+1 < $len)) {
                                               $code = $name[$i+1];
                                               if (!in_array($code, array('b', 'B', 'i', 'I'))) {
                                                       $color = "`" . $code;
                                                       break;
                                               }
                                               // Always increment $i to skip the code character
                                               $i++;
                                       }
                               }
                               if ($color !== '') {
                                       $formatted = $color . $formatted . '`0';
                               }
                               $parts[] = $formatted;
                       }
                       $args['petitioncount'] = "`n " . implode('|', $parts);

                       break;
               case "petition-status":
                       $statuses = array();
                       $sql = "SELECT id,name FROM " . db_prefix('petition_categories') . " WHERE active=1 ORDER BY id";
                       $result = db_query_cached($sql, 'petition_categories');
                       while ($row = db_fetch_assoc($result)) {
                               $statuses[(int)$row['id']] = $row['name'];
                       }
                       return $statuses;
                       break;
                case "superuser":
                        if (($session['user']['superuser'] & SU_EDIT_USERS) == SU_EDIT_USERS) {
                                addnav("Editors");
                                addnav("Petition Categories", "runmodule.php?module=personalpetitions");
                        }
                        break;
                default:
                break;
        }
        return $args;
}

function personalpetitions_run(){
        global $session;

        if (($session['user']['superuser'] & SU_EDIT_USERS) != SU_EDIT_USERS) {
                require_once("lib/superusernav.php");
                page_header("Petition Categories");
                output("You are not authorized to access this page.");
                superusernav();
                page_footer();
                return;
        }

        $op = httpget('op');
        $id = (int)httpget('id');
        $table = db_prefix('petition_categories');
        $msg = '';
        $conn = \Lotgd\MySQL\Database::getDoctrineConnection();
        invalidatedatacache('petition_counts');

       switch ($op) {
               case 'save':
                       // httppost() value is already slashed; the old code escaped it again, so the
                       // stored name carries one level of slashes. Bound unchanged to keep stored bytes identical.
                       $name = httppost('name');
                       $order = (int)httppost('order');
                       if ($name !== '') {
                               $conn->executeStatement(
                                       "UPDATE $table SET name = :name, sortOrder = :order WHERE id = :id",
                                       ['name' => $name, 'order' => $order, 'id' => $id]
                               );
                       } else {
                               $conn->executeStatement(
                                       "UPDATE $table SET sortOrder = :order WHERE id = :id",
                                       ['order' => $order, 'id' => $id]
                               );
                       }
                       invalidatedatacache('petition_categories');
                       invalidatedatacache('petition_counts');
                       redirect("runmodule.php?module=personalpetitions");
                       break;
               case 'toggle':
                       $conn->executeStatement("UPDATE $table SET active = 1 - active WHERE id = :id", ['id' => (int) $id]);
                       invalidatedatacache('petition_categories');
                       invalidatedatacache('petition_counts');
                       redirect("runmodule.php?module=personalpetitions");
                       break;
               case 'toggleimp':
                       $conn->executeStatement("UPDATE $table SET important = 1 - important WHERE id = :id", ['id' => (int) $id]);
                       invalidatedatacache('petition_categories');
                       invalidatedatacache('petition_counts');
                       redirect("runmodule.php?module=personalpetitions");
                       break;
               case 'del':
                       // Check if the status is in use before deleting
                       $petitions_table = db_prefix('petitions');
                       $cnt = $conn->fetchOne("SELECT COUNT(*) AS cnt FROM $petitions_table WHERE status = :id", ['id' => $id]);
                       if ((int) $cnt > 0) {
                               $msg = "`4Error:`0 This status cannot be deleted because it is in use by one or more petitions.";
                               break;
                       }
                       $conn->executeStatement("DELETE FROM $table WHERE id = :id", ['id' => $id]);
                       invalidatedatacache('petition_categories');
                       invalidatedatacache('petition_counts');
                       redirect("runmodule.php?module=personalpetitions");
                       break;
               case 'add':
                       $name = httppost('name');
                       $order = (int)httppost('order');
                       $important = httppost('important') ? 1 : 0;
                       if ($name !== '') {
                               // Slashed httppost value bound unchanged (old code escaped it a second time).
                               $conn->executeStatement(
                                       "INSERT INTO $table (name,active,important,sortOrder) VALUES (:name,1,:important,:order)",
                                       ['name' => $name, 'important' => $important, 'order' => $order]
                               );
                               invalidatedatacache('petition_categories');
                               invalidatedatacache('petition_counts');
                       }
                       redirect("runmodule.php?module=personalpetitions");
                       break;
       }

        page_header("Petition Categories");
        if ($msg !== '') {
                output($msg);
        }
        addnav("Navigation");
        addnav("Back to the Grotto", "superuser.php");

       $sql = "SELECT id,name,active,important,sortOrder FROM $table ORDER BY sortOrder,id";
       $result = db_query($sql);
       rawoutput("<table border='0' cellpadding='2' cellspacing='1' class='trhead'>");
        rawoutput("<tr class='trhead'><th>ID</th><th>Name</th><th>Active</th><th>Important</th><th>Delete</th></tr>");
       while ($row = db_fetch_assoc($result)) {
               $cid = (int)$row['id'];
               $escname = htmlentities($row['name'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1'));
               $escorder = (int)$row['sortOrder'];
               // Determine if any petition currently uses this status ID
               $in_use = (int) $conn->fetchOne(
                       "SELECT COUNT(*) AS cnt FROM " . \Lotgd\MySQL\Database::prefix('petitions') . " WHERE status = :cid",
                       ['cid' => $cid]
               );

               rawoutput("<tr class='trlight'>");
               rawoutput("<td>$cid</td>");
               rawoutput("<td><form action='runmodule.php?module=personalpetitions&op=save&id=$cid' method='post'><input name='name' value=\"$escname\" /> <input name='order' type='number' min='0' max='9999' value=\"$escorder\" size='3' /> <input type='submit' value='Save' /></form></td>");
               addnav('',"runmodule.php?module=personalpetitions&op=save&id=$cid");
               $toggle = $row['active'] ? translate_inline('Deactivate') : translate_inline('Activate');
               rawoutput("<td><a href='runmodule.php?module=personalpetitions&op=toggle&id=$cid'>$toggle</a></td>");
               addnav('',"runmodule.php?module=personalpetitions&op=toggle&id=$cid");
               $toggleimp = $row['important'] ? translate_inline('Unmark') : translate_inline('Mark');
               rawoutput("<td><a href='runmodule.php?module=personalpetitions&op=toggleimp&id=$cid'>$toggleimp</a></td>");
               addnav('',"runmodule.php?module=personalpetitions&op=toggleimp&id=$cid");
               rawoutput("<td>");
               if ($in_use > 0) {
                       rawoutput("<input type='button' value='Delete' disabled='disabled' />");
               } else {
                       rawoutput("<form action='runmodule.php?module=personalpetitions&op=del&id=$cid' method='post' onsubmit=\"return confirm('Are you sure you wish to delete this category?');\"><input type='submit' value='Delete' /></form>");
                       addnav('',"runmodule.php?module=personalpetitions&op=del&id=$cid");
               }
               rawoutput("</td></tr>");
       }
       rawoutput("</table>");

       rawoutput("<h3>Add Category</h3>");
       rawoutput("<form action='runmodule.php?module=personalpetitions&op=add' method='post'><input name='name' required maxlength='50' /> Important: <input type='checkbox' name='important' value='1' /> Order: <input name='order' type='number' value='0' size='3' min='0' max='999' /> <input type='submit' value='Add' /></form>");

        page_footer();
}

?>
