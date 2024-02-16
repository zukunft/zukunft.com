<?php

/*

  user_log_display.php - a combined object to display single value changes or changes of links by the user
  --------------------
  
  This file is part of zukunft.com - calc with words

  zukunft.com is free software: you can redistribute it and/or modify it
  under the terms of the GNU General Public License as
  published by the Free Software Foundation, either version 3 of
  the License, or (at your option) any later version.
  zukunft.com is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/


namespace html\log;

use cfg\db\sql;
use cfg\formula;
use html\formula\formula AS formula_dsp;
use cfg\log\change_log_table;
use cfg\component\component;
use cfg\library;
use cfg\db\sql_db;
use cfg\user;
use cfg\value\value;
use cfg\view;
use cfg\word;
use html\api;
use html\button;
use html\html_base;
use html\msg;

class user_log_display
{

    public int $id;                // the database id of the word, phrase, value or formula object
    public ?object $obj = null;    // the calling object
    public user $usr;              // the user of the person for whom the value is loaded, so to say the viewer
    public string $type;           // either "word", "phrase", "value" or "formula" to select the object to display
    public int $page;              // the page to display
    public bool $condensed = True; // display the changes in a few columns with reduced details
    public int $size;              // the page size
    public string $call = '';      // the html page which has call the hist display object
    public string $back = '';      //

    /**
     * define the settings for this log object
     * @param user $usr the user who requested to see this log
     */
    function __construct(user $usr)
    {
        $this->usr = $usr;
    }

    /**
     * display the history of a word, phrase, value or formula
     */
    function dsp_hist(): string
    {
        log_debug('user_log_display->dsp_hist ' . $this->type . ' id ' . $this->id . ' size ' . $this->size . ' page ' . $this->page . ' call from ' . $this->call . ' original call from ' . $this->back);

        global $db_con;
        global $change_log_tables;

        $result = ''; // reset the html code var

        $html = new html_base();

        // set default values
        if (!isset($this->size)) {
            $this->size = sql_db::ROW_LIMIT;
        } else {
            if ($this->size <= 0) {
                $this->size = sql_db::ROW_LIMIT;
            }
        }

        // select the change table to use
        $sql_where = '';
        $sql_row = '';
        $sql_user = '';
        // the setting for most cases
        $sql_row = 'AND c.row_id  = ' . $this->id . ' ';
        $sql_user = 'c.user_id = u.user_id';
        // the class specific settings
        if ($this->type == user::class) {
            $sql_where = " (f.table_id = " . $change_log_tables->id(change_log_table::WORD) . " 
                   OR f.table_id = " . $change_log_tables->id(change_log_table::WORD_USR) . ") AND ";
            $sql_row = '';
            $sql_user = 'c.user_id = u.user_id
                AND c.user_id = ' . $this->usr->id() . ' ';
        } elseif ($this->type == word::class) {
            $sql_where = " (f.table_id = " . $change_log_tables->id(change_log_table::WORD) . " 
                     OR f.table_id = " . $change_log_tables->id(change_log_table::WORD_USR) . ") AND ";
        } elseif ($this->type == value::class) {
            $sql_where = " (f.table_id = " . $change_log_tables->id(change_log_table::VALUE) . " 
                     OR f.table_id = " . $change_log_tables->id(change_log_table::VALUE_USR) . ") AND ";
        } elseif ($this->type == formula_dsp::class) {
            $sql_where = " (f.table_id = " . $change_log_tables->id(change_log_table::FORMULA) . " 
                     OR f.table_id = " . $change_log_tables->id(change_log_table::FORMULA_USR) . ") AND ";
        } elseif ($this->type == view::class) {
            $sql_where = " (f.table_id = " . $change_log_tables->id(change_log_table::VIEW) . " 
                     OR f.table_id = " . $change_log_tables->id(change_log_table::VIEW_USR) . ") AND ";
        } elseif ($this->type == component::class) {
            $sql_where = " (f.table_id = " . $change_log_tables->id(change_log_table::VIEW_COMPONENT) . " 
                     OR f.table_id = " . $change_log_tables->id(change_log_table::VIEW_COMPONENT_USR) . ") AND ";
        }

        if ($sql_where == '') {
            log_err("Internal error: object not defined for showing the changes.", "user_log_display->dsp_hist");
        } else {
            // get word changes by the user that are not standard
            $sql = "SELECT c.change_id, 
                     c.change_time AS time, 
                     u.user_name, 
                     a.change_action_name AS type, 
                     t.description AS type_table, 
                     f.description AS type_field, 
                     f.code_id, 
                     c.row_id, 
                     c.old_value AS old, 
                     c.new_value AS new
                FROM changes c,
                     change_actions a,
                     change_fields f,
                     change_tables t,
                     users u
               WHERE " . $sql_where . " 
                     f.change_field_id  = c.change_field_id 
                 AND f.table_id  = t.change_table_id
                 AND c.change_action_id = a.change_action_id 
                 AND " . $sql_user . " 
                     " . $sql_row . " 
            ORDER BY c.change_time DESC
               LIMIT " . $this->size . ";";
            log_debug('user_log_display->dsp_hist ' . $sql);
            $db_con->usr_id = $this->usr->id();
            $db_lst = $db_con->get_old($sql);

            // prepare to show where the user uses different word than a normal viewer
            $row_nbr = 0;
            $result .= $html->dsp_tbl_start_hist();
            foreach ($db_lst as $db_row) {
                // display the row only if the field is not an "admin only" field
                //if ($db_row["code_id"] <> formula::FLD_REF_TEXT) {
                $row_nbr++;
                $result .= '<tr>';
                if ($row_nbr == 1) {
                    if ($this->condensed) {
                        $result .= '<th>time</th>';
                        $result .= '<th>changed to</th>';
                    } else {
                        $result .= '<th>time</th>';
                        if ($this->type <> 'user') {
                            $result .= '<th>user</th>';
                        }
                        $result .= '<th>field</th>';
                        $result .= '<th>from</th>';
                        $result .= '<th>to</th>';
                        $result .= '<th></th>'; // extra column for the undo icon
                    }
                }
                $result .= '</tr><tr>';

                // pick the useful field name
                $txt_fld = '';
                if ($db_row[sql::FLD_CODE_ID] == "value") {
                    $txt_fld .= $db_row['type'] . ' value';
                    /* because changing the words creates a new value there is no need to display the words here
                    if ($db_row['row_id'] > 0) {
                      $val = New value;
                      $val->id = $db_row['row_id'];
                      $val->usr = $this;
                      $val->load();
                      $val->load_phrases();
                      $txt_fld .= '<td>';
                      if (isset($val->wrd_lst)) {
                        $txt_fld .= implode(",",$val->wrd_lst->names_linked());
                      }
                      $txt_fld .= '</td>';
                    } else {
                      $txt_fld .= '<td>'.$db_row['type'].' value</td>';
                    }
                    */
                } elseif ($this->type <> 'user') {
                    $txt_fld .= $db_row['type_field'];
                    // probably not needed to display the action, because this can be seen by the change itself
                    // $result .= $db_row['type'].' '.$db_row['type_field'];
                } else {
                    $txt_fld .= $db_row['type_table'] . ' ' . $db_row['type_field'];
                }

                // create the description for the old and new field value for the user
                $txt_old = $db_row["old"];
                $txt_new = $db_row["new"];
                // encode of text
                if ($db_row["code_id"] == formula::FLD_ALL_NEEDED) {
                    if ($txt_old == "1") {
                        $txt_old = "all values needed for calculation";
                    } else {
                        $txt_old = "calculate if one value is set";
                    }
                    if ($txt_new == "1") {
                        $txt_new = "all values needed for calculation";
                    } else {
                        $txt_new = "calculate if one value is set";
                    }
                }
                /* no encoding needed for this field at the moment
                if ($db_row["code_id"] == DBL_FLD_FORMULA_TYPE) {
                  if ($txt_old <> "") { $txt_old = 'type '.$txt_old; }
                  if ($txt_new <> "") { $txt_new = 'type '.$txt_new; }
                }
                */

                if ($this->condensed) {
                    $result .= '<td>' . $db_row["time"];
                    if ($this->type <> 'user') {
                        $result .= ' by ' . $db_row["user_name"];
                    }
                    $result .= '</td>';
                    $result .= '<td>' . $txt_fld . ': ' . $txt_new . '</td>';
                } else {
                    $result .= '<td>' . $db_row["time"] . '</td>';
                    if ($this->type <> 'user') {
                        $result .= '<td>' . $db_row["user"] . '</td>';
                    }


                    // display the change
                    $result .= '<td>' . $txt_fld . '</td>';
                    $result .= '<td>' . $txt_old . '</td>';
                    $result .= '<td>' . $txt_new . '</td>';
                    // switched of because "less seems to be more"
                    //if ($txt_old == "") { $result .= '<td>'.$db_row["type"].'</td>'; } else { $result .= '<td>'.$txt_old.'</td>'; }
                    //if ($txt_new == "") { $result .= '<td>'.$db_row["type"].'</td>'; } else { $result .= '<td>'.$txt_new.'</td>'; }
                }

                // encode the undo action
                $undo_text = '';
                $undo_call = '';
                $undo_btn = '';
                if ($this->type == 'word') {
                    if ($db_row['type'] == 'add') {
                        $undo_call = $html->url('value' . api::REMOVE, $this->id, $this->back);
                        $undo_btn = (new button($undo_call))->undo(msg::UNDO_ADD);
                    }
                } elseif ($this->type == 'value') {
                    if ($db_row['type'] == 'add') {
                        $undo_btn = $this->obj->btn_undo_add_value($this->back);
                    }
                } elseif ($this->type == 'formula') {
                    if ($db_row['type'] == 'update') {
                        $undo_call = $html->url(formula::class . api::UPDATE, $db_row["row_id"], $this->back . '&undo_change=' . $db_row["change_id"]);
                        $undo_btn = (new button($undo_call))->undo(msg::UNDO_ADD);
                    }
                }
                // display the undo button
                if ($this->condensed) {
                    if ($undo_call <> '') {
                        $result .= ' ' . $undo_btn;
                    } else {
                        $result .= '';
                    }
                } else {
                    if ($undo_call <> '') {
                        $result .= '<td>' . $undo_btn . '</td>';
                    } else {
                        $result .= '<td></td>';
                    }
                }

                $result .= '</tr>';
                //}
            }
            $result .= $html->dsp_tbl_end();
        }

        log_debug("done");
        return $result;
    }

    function dsp_hist_links_sql(sql_db $db_con, bool $get_name = false): string
    {
        global $change_log_tables;

        $lib = new library();
        $class = $lib->class_to_name($this->type);
        $sql_name = 'user_log_links_by_' . $class;

        // select the change table to use
        $sql_where = '';
        $sql_field = '';
        $sql_row = '';
        $sql_user = '';
        if ($class == 'user') {
            $sql_where = " ( c.change_table_id = " . $change_log_tables->id(change_log_table::USER) . " ) AND ";
            $sql_field = 'c.old_text_to AS old, 
                    c.new_text_to AS new';
            $sql_row = '';
            $sql_user = 'c.user_id = u.user_id
                AND c.user_id = ' . $this->usr->id() . ' ';
        } elseif ($class == 'word') {
            $sql_where = " ( c.change_table_id = " . $change_log_tables->id(change_log_table::WORD) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::WORD_USR) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::TRIPLE) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::TRIPLE_USR) . " ) AND ";
            $sql_field = 'c.old_text_to AS old, 
                    c.new_text_to AS new';
            $sql_row = ' (c.old_from_id = ' . $this->id . ' OR c.old_to_id = ' . $this->id . ' OR
                       c.new_from_id = ' . $this->id . ' OR c.new_to_id = ' . $this->id . ') AND ';
            $sql_user = 'c.user_id = u.user_id';
        } elseif ($class == 'value') {
            $sql_where = " ( c.change_table_id = " . $change_log_tables->id(change_log_table::VALUE) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::VALUE_USR) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::VALUE_LINK) . ") AND ";
            $sql_field = 'c.old_text_to AS old, 
                    c.new_text_to AS new';
            $sql_row = ' (c.old_from_id = ' . $this->id . ' OR c.new_from_id = ' . $this->id . ') AND ';
            $sql_user = 'c.user_id = u.user_id';
        } elseif ($class == 'formula') {
            $sql_where = " ( c.change_table_id = " . $change_log_tables->id(change_log_table::FORMULA) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::FORMULA_USR) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::FORMULA_LINK) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::FORMULA_LINK_USR) . " ) AND ";
            $sql_field = 'c.old_text_to AS old, 
                    c.new_text_to AS new';
            $sql_row = ' (c.old_from_id = ' . $this->id . ' OR c.new_from_id = ' . $this->id . ') AND ';
            $sql_user = 'c.user_id = u.user_id';
        } elseif ($class == 'view') {
            $sql_where = " ( c.change_table_id = " . $change_log_tables->id(change_log_table::VIEW) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::VIEW_USR) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::VIEW_LINK) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::VIEW_LINK_USR) . " ) AND ";
            $sql_field = 'c.old_text_to AS old, 
                    c.new_text_to AS new';
            $sql_row = ' (c.old_from_id = ' . $this->id . ' OR c.new_from_id = ' . $this->id . ') AND ';
            $sql_user = 'c.user_id = u.user_id';
        } elseif ($class == 'view_cmp') {
            $sql_where = " ( c.change_table_id = " . $change_log_tables->id(change_log_table::VIEW_COMPONENT) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::VIEW_COMPONENT_USR) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::VIEW_LINK) . " 
                    OR c.change_table_id = " . $change_log_tables->id(change_log_table::VIEW_LINK_USR) . " ) AND ";
            $sql_field = 'c.old_text_from AS old, 
                    c.new_text_from AS new';
            $sql_row = ' (c.old_to_id = ' . $this->id . ' OR c.new_to_id = ' . $this->id . ') AND ';
            $sql_user = 'c.user_id = u.user_id';
        }

        // get changed links related to one word
        $sql = "SELECT c.change_link_id, 
                   c.change_time AS time, 
                   u.user_name, 
                   a.change_action_name AS type, 
                   c.new_text_link AS link, 
                   c.row_id, 
                   " . $sql_field . "
              FROM change_links c,
                   change_actions a,
                   change_tables t,
                   users u
             WHERE " . $sql_where . "
                   " . $sql_row . "
                   c.change_table_id  = t.change_table_id
               AND c.change_action_id = a.change_action_id 
               AND " . $sql_user . " 
          ORDER BY c.change_time DESC
             LIMIT " . $this->size . ";";

        if ($get_name) {
            $result = $sql_name;
        } else {
            $result = $sql;
        }
        return $result;
    }

    // display change of links
    // e.g. if a formula is linked to another word
    //   or if a component is added to a display view
    function dsp_hist_links(): string
    {
        log_debug('user_log_display->dsp_hist_links ' . $this->type . ' id ' . $this->id . ' size ' . $this->size . ' page ' . $this->page . ' call from ' . $this->call . ' original call from ' . $this->back);

        global $db_con;
        $result = ''; // reset the html code var

        $html = new html_base();

        $sql = $this->dsp_hist_links_sql($db_con);
        $db_con->usr_id = $this->usr->id();
        $db_lst = $db_con->get_old($sql);

        // display the changes
        $row_nbr = 0;
        $result .= $html->dsp_tbl_start_hist();
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $row_nbr++;
                $result .= '<tr>';
                if ($row_nbr == 1) {
                    $result .= '<th>time</th>';
                    $result .= '<th>user</th>';
                    $result .= '<th>link</th>';
                    $result .= '<th></th>'; // extra column for the undo icon
                }
                $result .= '</tr><tr>';
                $result .= '<td>' . $db_row["time"] . '</td>';
                $result .= '<td>' . $db_row["user_name"] . '</td>';
                if ($db_row["old"] <> "" and $db_row["new"] <> "") {
                    $result .= '<td>change from ' . $db_row["old"] . ' to ' . $db_row["new"] . '</td>';
                } elseif ($db_row["old"] <> "") {
                    $result .= '<td>unlink from ' . $db_row["old"] . '</td>';
                } elseif ($db_row["new"] <> "") {
                    $result .= '<td>link to ' . $db_row["new"] . '</td>';
                } else {
                    // create an internal error???
                    $result .= '<td></td>';
                }
                // create the undo button if needed
                $undo_call = '';
                $undo_btn = '';
                if ($this->type == formula::class) {
                    $undo_call = $html->url(formula::class . api::UPDATE, $db_row["row_id"], $this->back . '&undo_change=' . $db_row["change_link_id"]);
                    $undo_btn = (new button($undo_call))->undo(msg::UNDO_EDIT);
                }
                // display the undo button
                if ($undo_call <> '') {
                    $result .= '<td>' . $undo_btn . '</td>';
                } else {
                    $result .= '<td></td>';
                }
                $result .= '</tr>';
            }
        }
        $result .= $html->dsp_tbl_end();

        log_debug("done");
        return $result;
    }

}