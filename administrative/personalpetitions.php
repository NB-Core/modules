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

        $result = db_query("SELECT COUNT(*) AS c FROM $table");
        $row = db_fetch_assoc($result);
        if ($row['c'] == 0) {
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
                db_query("INSERT INTO $table (name,active) VALUES ('".db_real_escape_string($core[0])."',1)");
                $zeroid = db_insert_id();
                db_query("UPDATE $table SET id=0 WHERE id=".(int)$zeroid);

                // Insert remaining core statuses with explicit ids
                foreach ($core as $id=>$name) {
                        if ($id == 0) continue;
                        db_query("INSERT INTO $table (id,name,active) VALUES (".(int)$id.",'".db_real_escape_string($name)."',1)");
                }

                // The previous version kept custom categories in this setting and
                // gave them the statuses 50, 51, ... in list order (empty entries
                // included). Keep those ids so existing petitions stay in their
                // category.
                $list = get_module_setting('categories');
                if ($list != '') {
                        $id = 50;
                        foreach (explode(',', $list) as $cat) {
                                $cat = trim($cat);
                                if ($cat != '') {
                                        db_query("INSERT INTO $table (id,name,active) VALUES (".$id.",'".db_real_escape_string($cat)."',1)");
                                }
                                $id++;
                        }
                }

                // Reset auto increment to next available id
                $max = db_fetch_assoc(db_query("SELECT MAX(id) AS maxid FROM $table"));
                $next = (int)$max['maxid'] + 1;
                db_query("ALTER TABLE $table AUTO_INCREMENT=".$next);

                db_query("DELETE FROM ".db_prefix('module_settings')." WHERE modulename='personalpetitions' AND setting='categories'");

                // Ensure seeded rows have default values for new columns
                db_query("UPDATE $table SET important = 0, sortOrder = NULL");
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
        invalidatedatacache('petition_counts');

       switch ($op) {
               case 'save':
                       $name = httppost('name');
                       $order = (int)httppost('order');
                       if ($name !== '') {
                               $sql = "UPDATE $table SET name='" . db_real_escape_string($name) . "', sortOrder=" . (int)$order . " WHERE id=$id";
                       } else {
                               $sql = "UPDATE $table SET sortOrder=" . (int)$order . " WHERE id=$id";
                       }
                       db_query($sql);
                       invalidatedatacache('petition_categories');
                       invalidatedatacache('petition_counts');
                       redirect("runmodule.php?module=personalpetitions");
                       break;
               case 'toggle':
                       $sql = "UPDATE $table SET active = 1 - active WHERE id=" . (int)$id;
                       db_query($sql);
                       invalidatedatacache('petition_categories');
                       invalidatedatacache('petition_counts');
                       redirect("runmodule.php?module=personalpetitions");
                       break;
               case 'toggleimp':
                       $sql = "UPDATE $table SET important = 1 - important WHERE id=" . (int)$id;
                       db_query($sql);
                       invalidatedatacache('petition_categories');
                       invalidatedatacache('petition_counts');
                       redirect("runmodule.php?module=personalpetitions");
                       break;
               case 'del':
                       // Check if the status is in use before deleting
                       $petitions_table = db_prefix('petitions');
                       $sql_check = "SELECT COUNT(*) AS cnt FROM $petitions_table WHERE status = $id";
                       $result = db_query($sql_check);
                       $row = db_fetch_assoc($result);
                       if ($row['cnt'] > 0) {
                               $msg = "`4Error:`0 This status cannot be deleted because it is in use by one or more petitions.";
                               break;
                       }
                       $sql = "DELETE FROM $table WHERE id=$id"; 
                       db_query($sql);
                       invalidatedatacache('petition_categories');
                       invalidatedatacache('petition_counts');
                       redirect("runmodule.php?module=personalpetitions");
                       break;
               case 'add':
                       $name = httppost('name');
                       $order = (int)httppost('order');
                       $important = httppost('important') ? 1 : 0;
                       if ($name !== '') {
                               $sql = "INSERT INTO $table (name,active,important,sortOrder) VALUES ('" . db_real_escape_string($name) . "',1," . db_real_escape_string($important) . "," . db_real_escape_string($order) . ")"; 
                               db_query($sql);
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
               $count_sql = "SELECT COUNT(*) AS cnt FROM " . db_prefix('petitions') . " WHERE status=" . (int)$cid;
               $count_res = db_query($count_sql);
               $count = db_fetch_assoc($count_res);
               $in_use = (int)$count['cnt'];

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
