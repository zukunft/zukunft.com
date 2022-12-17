<?php

/*

    model/ref/source.php - the source object to define the source for the values
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

use api\source_api;
use html\source_dsp;
use export\source_exp;
use export\exp_obj;

class source extends user_sandbox_named_with_type
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    const FLD_ID = 'source_id';
    const FLD_NAME = 'source_name';
    const FLD_TYPE = 'source_type_id';
    const FLD_URL = 'url';
    const FLD_COMMENT = 'comment';

    const FLD_EX_URL = 'url';

    // all database field names excluding the id used to identify if there are some user specific changes
    const FLD_NAMES = array(
        self::FLD_NAME,
        sql_db::FLD_CODE_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_URL,
        self::FLD_COMMENT
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_EXCLUDED
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields
    public ?string $url = null;          // the internet link to the source
    public ?string $description = null;  // the source description that is shown as a mouseover explain to the user
    public ?string $code_id = null;      // to select internal predefined sources

    // in memory only fields
    public ?string $back = null; // the calling stack


    /*
     * construct and map
     */

    // define the settings for this source object
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->obj_name = sql_db::TBL_SOURCE;

        $this->rename_can_switch = UI_CAN_CHANGE_SOURCE_NAME;
    }

    function reset(): void
    {
        parent::reset();

        $this->name = '';

        $this->url = '';
        $this->description = '';
        $this->type_id = null;
        $this->code_id = '';

        $this->back = null;
    }

    /**
     * map the database object to this source class fields
     *
     * @param array $db_row with the data directly from the database
     * @param bool $map_usr_fields false for using the standard protection settings for the default source used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @return bool true if the source is loaded and valid
     */
    function row_mapper(array $db_row, bool $map_usr_fields = false, string $id_fld = self::FLD_ID): bool
    {
        $result = parent::row_mapper($db_row, $map_usr_fields, self::FLD_ID);
        if ($result) {
            $this->name = $db_row[self::FLD_NAME];
            $this->url = $db_row[self::FLD_URL];
            $this->description = $db_row[self::FLD_COMMENT];
            $this->type_id = $db_row[self::FLD_TYPE];
            $this->code_id = $db_row[sql_db::FLD_CODE_ID];
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the source
     * @param string $name mainly for test creation the name of the source
     * @param string $type_code_id the code id of the predefined source type
     */
    public function set(int $id = 0, string $name = '', string $type_code_id = ''): void
    {
        parent::set($id, $name);

        if ($type_code_id != '') {
            $this->set_type($type_code_id);
        }
    }

    /**
     * set the predefined type of this source
     *
     * @param string $type_code_id the code id that should be added to this source
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        $this->type_id = cl(db_cl::SOURCE_TYPE, $type_code_id);
    }


    /*
     * cast
     */

    /**
     * @return source_api the source frontend api object
     */
    function api_obj(): source_api
    {
        $api_obj = new source_api();
        if (!$this->excluded) {
            parent::fill_api_obj($api_obj);
        }
        return $api_obj;
    }

    /**
     * @return source_dsp the source object with the display interface functions
     */
    function dsp_obj(): source_dsp
    {
        $dsp_obj = new source_dsp();
        if (!$this->excluded) {
            parent::fill_dsp_obj($dsp_obj);
        }

        return $dsp_obj;
    }


    /*
     * load
     */

    /**
     * create the SQL to load the default source always by the id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_SOURCE);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(sql_db::FLD_USER_ID)
        ));

        return parent::load_standard_sql($db_con, $class);
    }

    /**
     * load the source parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the standard source has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con);
        $result = parent::load_standard($qp, $class);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a source from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_db $db_con, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con, $class);
        $qp->name .= $query_name;

        $db_con->set_type(sql_db::TBL_SOURCE);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve the parameters of a source from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_obj_vars(sql_db $db_con, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($db_con, $class);
        if ($this->id != 0) {
            $qp->name .= 'id';
        } elseif ($this->code_id != '') {
            $qp->name .= sql_db::FLD_CODE_ID;
        } elseif ($this->name != '') {
            $qp->name .= 'name';
        } else {
            log_err('Either the database ID (' . $this->id . ') or the ' .
                $class . ' name (' . $this->name . ') and the user (' . $this->user()->id . ') must be set to load a ' .
                $class, $class . '->load');
        }

        $db_con->set_type(sql_db::TBL_SOURCE);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_usr_fields(self::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(self::FLD_NAMES_NUM_USR);
        if ($this->id != 0) {
            $db_con->add_par(sql_db::PAR_INT, $this->id);
            $qp->sql = $db_con->select_by_set_id();
        } elseif ($this->code_id != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $this->code_id);
            $qp->sql = $db_con->select_by_code_id();
        } elseif ($this->name != '') {
            $db_con->add_par(sql_db::PAR_TEXT, $this->name);
            $qp->sql = $db_con->select_by_set_name();
        }
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the missing source parameters from the database
     */
    function load_obj_vars(): bool
    {
        global $db_con;
        $result = false;

        // check the all minimal input parameters
        if (!$this->user()->is_set()) {
            log_err("The user id must be set to load a source.", "source->load");
        } elseif ($this->id <= 0 and $this->code_id == '' and $this->name == '') {
            log_err("Either the database ID (" . $this->id . "), the name (" . $this->name . ") or the code_id (" . $this->code_id . ") and the user (" . $this->user()->id . ") must be set to load a source.", "source->load");
        } else {

            $qp = $this->load_sql_obj_vars($db_con);

            if ($db_con->get_where() <> '') {
                $db_row = $db_con->get1($qp);
                $this->row_mapper($db_row);
                if ($this->id > 0) {
                    log_debug('source->load (' . $this->dsp_id() . ')');
                    $result = true;
                }
            }
        }
        return $result;
    }

    function id_field(): string
    {
        return self::FLD_ID;
    }

    function name_field(): string
    {
        return self::FLD_NAME;
    }


    /**
     * @return string the source type name from the array preloaded from the database
     */
    public function type_name(): string
    {
        global $source_types;

        $type_name = '';
        if ($this->type_id > 0) {
            $type_name = $source_types->name($this->type_id);
        }
        return $type_name;
    }


    /*
     * im- and export
     */

    /**
     * import a source from an object
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): user_message
    {
        log_debug();
        $result = new user_message();

        foreach ($json_obj as $key => $value) {

            if ($key == exp_obj::FLD_NAME) {
                $this->name = $value;
            }
            if ($key == self::FLD_EX_URL) {
                $this->url = $value;
            }
            if ($key == exp_obj::FLD_DESCRIPTION) {
                $this->description = $value;
            }
            /* TODO
            if ($key == exp_obj::FLD_TYPE)    { $this->type_id = cl($value); }
            if ($key == sql_db::FLD_CODE_ID) {
            }
            */
        }

        if ($result->is_ok() and $do_save) {
            $result->add_message($this->save());
        }

        return $result;
    }

    /**
     * create an object for the export
     * @param bool $do_load to switch off the database load for unit tests
     * @return exp_obj the filled object used to create the json
     */
    function export_obj(bool $do_load = true): exp_obj
    {
        log_debug();
        $result = new source_exp();

        // add the source parameters
        $result->name = $this->name();
        if ($this->url <> '') {
            $result->url = $this->url;
        }
        if ($this->description <> '') {
            $result->comment = $this->description;
        }
        if ($this->type_name() <> '') {
            $result->type = $this->type_name();
        }
        if ($this->code_id <> '') {
            $result->code_id = $this->code_id;
        }

        log_debug(json_encode($result));
        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string the unique description of a source
     */
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
        if ($this->user()->is_set()) {
            $result .= ' for user ' . $this->user()->id . ' (' . $this->user()->name . ')';
        }
        return $result;
    }


    /*
     * save
     */

    /**
     * @return bool true if no one has used this source
     */
    public function not_used(): bool
    {
        log_debug('source->not_used (' . $this->id . ')');

        // to review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if the source has been changed
     */
    function not_changed_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_SOURCE);
        return $db_con->not_changed_sql($this->id, $this->owner_id);
    }

    /**
     * @return bool true if no other user has modified the source
     */
    function not_changed(): bool
    {
        log_debug($this->id . ' by someone else than the owner (' . $this->owner_id . ')');

        global $db_con;
        $result = true;

        if ($this->id == 0) {
            log_err('The id must be set to detect if the link has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con);
            $db_row = $db_con->get1($qp);
            $change_user_id = $db_row[self::FLD_USER];
            if ($change_user_id > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * @return bool true if the user is the owner and no one else has changed the source
     *              because if another user has changed the source and the original value is changed,
     *              maybe the user source also needs to be updated
     */
    function can_change(): bool
    {
        log_debug($this->id . ',u' . $this->user()->id);
        $can_change = false;
        if ($this->owner_id == $this->user()->id or $this->owner_id <= 0) {
            $can_change = true;
        }

        log_debug(zu_dsp_bool($can_change));
        return $can_change;
    }

    /**
     * create a database record to save user specific settings for this source
     * @return bool false if the adding has failed and true if it was successful or not needed
     */
    function add_usr_cfg(): bool
    {
        global $db_con;
        $result = true;

        if (!$this->has_usr_cfg()) {
            log_debug('for "' . $this->dsp_id() . ' und user ' . $this->user()->name);

            // check again if there ist not yet a record
            $db_con->set_type(sql_db::TBL_SOURCE, true);
            $qp = new sql_par(self::class);
            $qp->name = 'source_add_usr_cfg';
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id);
            $db_con->set_where_std($this->id);
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row['source_id'];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_SOURCE);
                $log_id = $db_con->insert(array('source_id', user_sandbox::FLD_USER), array($this->id, $this->user()->id));
                if ($log_id <= 0) {
                    log_err('Insert of user_source failed.');
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current source
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function usr_cfg_sql(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_SOURCE);
        $db_con->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::usr_cfg_sql($db_con, $class);
    }

    /**
     * check if the database record for the user specific settings can be removed
     * @return bool false if the deletion has failed and true if it was successful or not needed
     */
    function del_usr_cfg_if_not_needed(): bool
    {
        log_debug('pre check for "' . $this->dsp_id() . ' und user ' . $this->user()->name);

        global $db_con;
        $result = true;

        //if ($this->has_usr_cfg) {

        // check again if there ist not yet a record
        $qp = $this->usr_cfg_sql($db_con);
        $db_con->usr_id = $this->user()->id;
        $usr_src_cfg = $db_con->get1($qp);
        log_debug('check for "' . $this->dsp_id() . ' und user ' . $this->user()->name . ' with (' . $qp->sql . ')');
        if ($usr_src_cfg['source_id'] > 0) {
            // TODO check that this converts all fields for all types
            // TODO define for each user sandbox object a list with all user fields and loop here over this array
            if ($usr_src_cfg['source_name'] == ''
                and $usr_src_cfg['url'] == ''
                and $usr_src_cfg['comment'] == ''
                and $usr_src_cfg['source_type_id'] == Null
                and $usr_src_cfg[self::FLD_EXCLUDED] == Null) {
                // delete the entry in the user sandbox
                log_debug('any more for "' . $this->dsp_id() . ' und user ' . $this->user()->name);
                $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_SOURCE);
                $del_result = $db_con->delete(array('source_id', user_sandbox::FLD_USER), array($this->id, $this->user()->id));
                if ($del_result != '') {
                    $result = false;
                    log_err('Deletion of user_source failed.');
                }
            }
        }
        //}
        return $result;
    }

    /**
     * set the update parameters for the source url
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param user_sandbox $db_rec the database record before the saving
     * @param user_sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    private function save_field_url(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
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

    /**
     * set the update parameters for the source comment
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param user_sandbox $db_rec the database record before the saving
     * @param user_sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    private function save_field_comment(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = '';
        if ($db_rec->description <> $this->description) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->description;
            $log->new_value = $this->description;
            $log->std_value = $std_rec->description;
            $log->row_id = $this->id;
            $log->field = 'comment';
            $result = $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the word type
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param user_sandbox $db_rec the database record before the saving
     * @param user_sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_field_type(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
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
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param user_sandbox $db_rec the database record before the saving
     * @param user_sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_fields(sql_db $db_con, user_sandbox $db_rec, user_sandbox $std_rec): string
    {
        $result = $this->save_field_url($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_comment($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('source->save_fields all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

}
