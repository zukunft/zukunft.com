<?php

/*

    source.php - the source object to define the source for the values
    ----------

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class source extends user_sandbox
{

    // persevered source names for unit and integration tests
    const TN_READ = 'wikidata';
    const TN_ADD = 'System Test Source';
    const TN_RENAMED = 'System Test Source Renamed';

    // parameters used for unit and integration tests
    const TEST_URL = 'https://www.zukunft.com/';
    const TEST_URL_CHANGED = 'https://api.zukunft.com/';
    const TEST_DESCRIPTION = 'System Test Source Description';
    const TEST_DESCRIPTION_CHANGED = 'System Test Source Description Changed';

    // source group for creating the test sources and remove them after the test
    const RESERVED_SOURCES = array(
        self::TN_READ, // the source for all data imported from wikidata that does not yet have a source defined in wikidata
        self::TN_ADD,
        self::TN_RENAMED
    );

    // database fields additional to the user sandbox fields
    public ?string $url = null;      // the internet link to the source
    public ?string $comment = null;  // the source description that is shown as a mouseover explain to the user
    public ?string $code_id = null;  // to select internal predefined sources

    // in memory only fields
    public ?string $back = null; // the calling stack

    // define the settings for this source object
    function __construct()
    {
        parent::__construct();
        $this->obj_name = DB_TYPE_SOURCE;

        $this->rename_can_switch = UI_CAN_CHANGE_SOURCE_NAME;
    }

    function reset()
    {
        $this->id = null;
        $this->usr_cfg_id = null;
        $this->usr = null;
        $this->owner_id = null;
        $this->excluded = null;

        $this->name = '';

        $this->url = '';
        $this->comment = '';
        $this->type_id = null;
        $this->code_id = '';

        $this->type_name = '';
        $this->back = null;
    }

    // map the database object to this source class fields
    private function row_mapper($db_row, $map_usr_fields = false)
    {
        if ($db_row != null) {
            if ($db_row['source_id'] > 0) {
                $this->id = $db_row['source_id'];
                $this->name = $db_row['source_name'];
                $this->owner_id = $db_row[self::FLD_USER];
                $this->url = $db_row['url'];
                $this->comment = $db_row['comment'];
                $this->type_id = $db_row['source_type_id'];
                $this->code_id = $db_row[sql_db::FLD_CODE_ID];
                if ($map_usr_fields) {
                    $this->usr_cfg_id = $db_row['user_source_id'];
                }
            } else {
                $this->id = 0;
            }
        } else {
            $this->id = 0;
        }
    }

    // load the source parameters for all users
    // return false if load fails
    function load_standard(): bool
    {
        global $db_con;
        $result = false;

        $db_con->set_type(DB_TYPE_SOURCE);
        $db_con->set_fields(array(sql_db::FLD_USER_ID, 'url', 'comment', 'source_type_id', sql_db::FLD_CODE_ID));
        $db_con->set_where($this->id, $this->name);
        $sql = $db_con->select();

        if ($db_con->get_where() <> '') {
            $db_src = $db_con->get1($sql);
            $this->row_mapper($db_src);
            $result = $this->load_owner();
        }
        return $result;
    }

    // load the missing source parameters from the database
    function load(): bool
    {
        global $db_con;
        $result = false;

        // check the all minimal input parameters
        if (!isset($this->usr)) {
            log_err("The user id must be set to load a source.", "source->load");
        } elseif ($this->id <= 0 and $this->code_id == '' and $this->name == '') {
            log_err("Either the database ID (" . $this->id . "), the name (" . $this->name . ") or the code_id (" . $this->code_id . ") and the user (" . $this->usr->id . ") must be set to load a source.", "source->load");
        } else {

            $db_con->set_type(DB_TYPE_SOURCE);
            $db_con->set_usr($this->usr->id);
            $db_con->set_fields(array(sql_db::FLD_CODE_ID));
            $db_con->set_usr_fields(array('url', 'comment'));
            $db_con->set_usr_num_fields(array('source_type_id'));
            $db_con->set_where($this->id, $this->name, $this->code_id);
            $sql = $db_con->select();

            if ($db_con->get_where() <> '') {
                $db_row = $db_con->get1($sql);
                $this->row_mapper($db_row, true);
                if ($this->id > 0) {
                    log_debug('source->load (' . $this->dsp_id() . ')');
                    $result = true;
                }
            }
        }
        return $result;
    }


    // read the source type name from the database
    // TODO integrate this into the load
    private function type_name(): string
    {
        global $db_con;

        if ($this->type_id > 0) {
            $db_con->set_type(DB_TYPE_SOURCE_TYPE);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where($this->type_id);
            $sql = $db_con->select();
            $db_type = $db_con->get1($sql);
            $this->type_name = $db_type['source_type_name'];
        }
        return $this->type_name;
    }

    // create an object for the export
    function export_obj(): source
    {
        log_debug('source->export_obj');
        $result = new source();

        // add the source parameters
        $result->name = $this->name;
        if ($this->url <> '') {
            $result->url = $this->url;
        }
        if ($this->comment <> '') {
            $result->comment = $this->comment;
        }
        if ($this->type_name() <> '') {
            $result->obj_type = $this->type_name();
        }
        if ($this->code_id <> '') {
            $result->code_id = $this->code_id;
        }

        log_debug('source->export_obj -> ' . json_encode($result));
        return $result;
    }

    // import a source from an object
    function import_obj($json_obj): string
    {
        log_debug('source->import_obj');
        $result = '';

        foreach ($json_obj as $key => $value) {

            if ($key == 'name') {
                $this->name = $value;
            }
            if ($key == 'url') {
                $this->url = $value;
            }
            if ($key == 'comment') {
                $this->comment = $value;
            }
            /* TODO
            if ($key == 'type')    { $this->type_id = cl($value); }
            if ($key == sql_db::FLD_CODE_ID) {
            }
            */
        }

        $result .= $this->save();
        if ($result == '') {
            log_debug('source->import_obj -> ' . $this->dsp_id());
        } else {
            log_debug('source->import_obj -> save failed');
        }

        return $result;
    }

    /*

    display functions

    */

    // display the unique id fields
    function dsp_id(): string
    {
        $result = '';

        if ($this->name <> '') {
            $result .= $this->name . ' ';
            if ($this->id > 0) {
                $result .= '(' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    function name(): string
    {
        return $this->name;
    }

    // return the html code to display a source name with the link
    function name_linked($wrd, $back): string
    {
        return '<a href="/http/source_edit.php?id=' . $this->id . '&word=' . $wrd->id . '&back=' . $back . '">' . $this->name . '</a>';
    }

    /*
     * TODO check if this is still needed (at least use the idea)
     *
    // returns the html code for a source: this is the main function of this lib
    // source_id is used to force the display to a set form; e.g. display the sectors of a company instead of the balance sheet
    // source_type_id is used to .... remove???
    // word_id - id of the starting word to display; can be a single word, a comma separated list of word ids, a word group or a word triple
    function display($wrd): string
    {
        log_debug('source->display "' . $wrd->name . '" with the view ' . $this->dsp_id() . ' (type ' . $this->type_id . ')  for user "' . $this->usr->name . '"');
        $result = '';

        if ($this->id <= 0) {
            log_err("The source id must be loaded to display it.", "source->display");
        } else {
            // display always the source name in the top right corner and allow the user to edit the source
            $result .= $this->dsp_type_open();
            $result .= $this->dsp_navbar($wrd->id);
            $result .= $this->dsp_entries($wrd);
            $result .= $this->dsp_type_close();
        }
        log_debug('source->display ... done');

        return $result;
    }
    */

    // display a selector for the value source
    function dsp_select($form_name, $back): string
    {
        log_debug('source->dsp_select ' . $this->dsp_id());
        $result = ''; // reset the html code var

        // for new values assume the last source used, but not for existing values to enable only changing the value, but not setting the source
        if ($this->id <= 0 and $form_name == "value_add") {
            $this->id = $this->usr->source_id;
        }

        log_debug("source->dsp_select -> source id used (" . $this->id . ")");
        $sel = new selector;
        $sel->usr = $this->usr;
        $sel->form = $form_name;
        $sel->name = "source";
        $sel->sql = sql_lst_usr("source", $this->usr);
        $sel->selected = $this->id;
        $sel->dummy_text = 'please define the source';
        $result .= '      taken from ' . $sel->display() . ' ';
        $result .= '    <td>' . btn_edit("Rename " . $this->name, '/http/source_edit.php?id=' . $this->id . '&back=' . $back) . '</td>';
        $result .= '    <td>' . btn_add("Add new source", '/http/source_add.php?back=' . $back) . '</td>';
        return $result;
    }

    // display a selector for the source type
    private function dsp_select_type($form_name, $back): string
    {
        log_debug("source->dsp_select_type (" . $this->id . "," . $form_name . ",b" . $back . " and user " . $this->usr->name . ")");

        $result = ''; // reset the html code var

        $sel = new selector;
        $sel->usr = $this->usr;
        $sel->form = $form_name;
        $sel->name = "source_type";
        $sel->sql = sql_lst("source_type");
        $sel->selected = $this->type_id;
        $sel->dummy_text = 'please select the source type';
        $result .= $sel->display();
        return $result;
    }

    // display a html view to change the source name and url
    function dsp_edit(string $back = ''): string
    {
        log_debug('source->dsp_edit ' . $this->dsp_id() . ' by user ' . $this->usr->name);
        $result = '';

        if ($this->id <= 0) {
            $script = "source_add";
            $result .= dsp_text_h2("Add source");
        } else {
            $script = "source_edit";
            $result .= dsp_text_h2('Edit source "' . $this->name . '"');
        }
        $result .= dsp_form_start($script);
        //$result .= dsp_tbl_start();
        $result .= dsp_form_hidden("id", $this->id);
        $result .= dsp_form_hidden("back", $back);
        $result .= dsp_form_hidden("confirm", 1);
        $result .= dsp_form_fld("name", $this->name, "Source name:");
        $result .= '<tr><td>type   </td><td>' . $this->dsp_select_type($script, $back) . '</td></tr>';
        $result .= dsp_form_fld("url", $this->url, "URL:");
        $result .= dsp_form_fld("comment", $this->comment, "Comment:");
        //$result .= dsp_tbl_end ();
        $result .= dsp_form_end('', $back);

        log_debug('source->dsp_edit -> done');
        return $result;
    }

    /*

    save functions

    */

    // true if no one has used this source
    public function not_used(): bool
    {
        log_debug('source->not_used (' . $this->id . ')');

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    // true if no other user has modified the source
    function not_changed(): bool
    {
        log_debug('source->not_changed (' . $this->id . ') by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;

        $result = true;

        if ($this->owner_id > 0) {
            $sql = "SELECT user_id 
                FROM user_sources 
               WHERE source_id = " . $this->id . "
                 AND user_id <> " . $this->owner_id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        } else {
            $sql = "SELECT user_id 
                FROM user_sources 
               WHERE source_id = " . $this->id . "
                 AND (excluded <> 1 OR excluded is NULL)";
        }
        $db_con->usr_id = $this->usr->id;
        $db_row = $db_con->get1($sql);
        $change_user_id = $db_row[self::FLD_USER];
        if ($change_user_id > 0) {
            $result = false;
        }
        log_debug('source->not_changed for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    // true if the user is the owner and no one else has changed the source
    // because if another user has changed the source and the original value is changed, maybe the user source also needs to be updated
    function can_change(): bool
    {
        log_debug('source->can_change (' . $this->id . ',u' . $this->usr->id . ')');
        $can_change = false;
        if ($this->owner_id == $this->usr->id or $this->owner_id <= 0) {
            $can_change = true;
        }

        log_debug('source->can_change -> (' . zu_dsp_bool($can_change) . ')');
        return $can_change;
    }

    // create a database record to save user specific settings for this source
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            log_debug('source->add_usr_cfg for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

            // check again if there ist not yet a record
            $db_con->set_type(DB_TYPE_SOURCE, true);
            $db_con->set_usr($this->usr->id);
            $db_con->set_where($this->id);
            $sql = $db_con->select();
            $db_row = $db_con->get1($sql);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['source_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_SOURCE);
                $log_id = $db_con->insert(array('source_id', 'user_id'), array($this->id, $this->usr->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_source failed.');
                    $result = false;
                } else {
                    $result = true;
                }
            }
        }
        return $result;
    }

    // check if the database record for the user specific settings can be removed
    // returns false if the deletion has failed and true if it was successful or not needed
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('source->del_usr_cfg_if_not_needed pre check for "' . $this->dsp_id() . ' und user ' . $this->usr->name);

        global $db_con;
        $result = false;

        //if ($this->has_usr_cfg) {

        // check again if there ist not yet a record
        $sql = "SELECT source_id,
                     source_name,
                     url,
                     comment,
                     source_type_id, excluded
                FROM user_sources
               WHERE source_id = " . $this->id . " 
                 AND user_id = " . $this->usr->id . ";";
        $db_con->usr_id = $this->usr->id;
        $usr_wrd_cfg = $db_con->get1($sql);
        log_debug('source->del_usr_cfg_if_not_needed check for "' . $this->dsp_id() . ' und user ' . $this->usr->name . ' with (' . $sql . ')');
        if ($usr_wrd_cfg['source_id'] > 0) {
            // TODO check that this converts all fields for all types
            // TODO define for each user sandbox object a list with all user fields and loop here over this array
            if ($usr_wrd_cfg['source_name'] == ''
                and $usr_wrd_cfg['url'] == ''
                and $usr_wrd_cfg['comment'] == ''
                and $usr_wrd_cfg['source_type_id'] == Null
                and $usr_wrd_cfg['excluded'] == Null) {
                // delete the entry in the user sandbox
                log_debug('source->del_usr_cfg_if_not_needed any more for "' . $this->dsp_id() . ' und user ' . $this->usr->name);
                $db_con->set_type(DB_TYPE_USER_PREFIX . DB_TYPE_SOURCE);
                if ($db_con->delete(array('source_id', 'user_id'), array($this->id, $this->usr->id))) {
                    $result = true;
                } else {
                    log_err('Deletion of user_source failed.');
                }
            }
        }
        //}
        return $result;
    }

    // set the update parameters for the source url
    private function save_field_url($db_con, $db_rec, $std_rec): string
    {
        $result = '';
        if ($db_rec->url <> $this->url) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->url;
            $log->new_value = $this->url;
            $log->std_value = $std_rec->url;
            $log->row_id = $this->id;
            $log->field = 'url';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    // set the update parameters for the source comment
    private function save_field_comment($db_con, $db_rec, $std_rec): string
    {
        $result = '';
        if ($db_rec->comment <> $this->comment) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->comment;
            $log->new_value = $this->comment;
            $log->std_value = $std_rec->comment;
            $log->row_id = $this->id;
            $log->field = 'comment';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the word type
     */
    function save_field_type($db_con, $db_rec, $std_rec): string
    {
        $result = '';
        if ($db_rec->type_id <> $this->type_id) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->type_name();
            $log->old_id = $db_rec->type_id;
            $log->new_value = $this->type_name();
            $log->new_id = $this->type_id;
            $log->std_value = $std_rec->type_name();
            $log->std_id = $std_rec->type_id;
            $log->row_id = $this->id;
            $log->field = 'source_type_id';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * save all updated source fields excluding the name, because already done when adding a source
     */
    function save_fields($db_con, $db_rec, $std_rec): string
    {
        $result = $this->save_field_url($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_comment($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('source->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

}
