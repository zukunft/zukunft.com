<?php

/*

    model/word/word.php - the main word object
    -------------------

    TODO move plural to a linked word?

    TODO check if all objects follow these rules
        - database fields are defined within the object wit a const staring with FLD_
        - the object is as small as possible, means there are no redundant fields
        - for each selection and database reading function a separate load function with the search field is defined e.g. load_by_name(string name)
        - for each load function a separate load_sql function exists, which is unit tested
        - the row_mapper function is always used map the database field to the object fields
        - a minimal object exists with for display only for one user only e.g. for a word object, just the id and the name
        - a ex- and import object exists, that does not include any internal database ids

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

include_once SERVICE_EXPORT_PATH . 'sandbox_exp_named.php';
include_once SERVICE_PATH . 'db_code_link.php';
include_once API_WORD_PATH . 'word.php';
include_once MODEL_REF_PATH . 'ref.php';
include_once SERVICE_EXPORT_PATH . 'word_exp.php';

use api\api;
use api\word_api;
use cfg\db\sql_creator;
use html\phrase\phrase_list as phrase_list_dsp;
use model\export\exp_obj;
use model\export\sandbox_exp_named;
use model\export\word_exp;
use controller\controller;
use html\button;
use html\html_base;
use html\html_selector;
use html\log\user_log_display;
use html\view\view_dsp_old;
use html\word\word as word_dsp;
use html\formula\formula as formula_dsp;

class word extends sandbox_typed
{

    /*
     * database link
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    const FLD_ID = 'word_id';
    const FLD_NAME = 'word_name';
    const FLD_PLURAL = 'plural'; // TODO move to language types
    const FLD_VIEW = 'view_id';
    const FLD_VALUES = 'values';
    // the field names used for the im- and export in the json or yaml format
    const FLD_REFS = 'refs';

    // all database field names excluding the id, standard name and user specific fields
    const FLD_NAMES = array(
        self::FLD_VALUES
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_PLURAL,
        sandbox_named::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        phrase::FLD_TYPE,
        self::FLD_VIEW,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        self::FLD_VALUES,
        self::FLD_PLURAL,
        sandbox_named::FLD_DESCRIPTION,
        phrase::FLD_TYPE,
        self::FLD_VIEW,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * const to use the system also for the own system configuration
     * e.g. the number of decimal places related to the user specific words
     * system configuration that is not related to user sandbox data is using the flat cfg methods
     * included in the preserved word names
     */

    const SYSTEM_CONFIG = 'system configuration';


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields
    public ?string $plural;    // the english plural name as a kind of shortcut; if plural is NULL the database value should not be updated
    public ?int $values;       // the total number of values linked to this word as an indication how common the word is and to sort the words

    // in memory only fields
    public ?int $link_type_id; // used in the word list to know based on which relation the word was added to the list

    // only used for the export object
    private ?view $view; // name of the default view for this word
    private ?array $ref_lst = [];


    /*
     * construct and map
     */

    /**
     * define the settings for this word object
     * @param user $usr the user who requested to see this word
     */
    function __construct(user $usr)
    {
        $this->reset();
        parent::__construct($usr);

        $this->obj_name = sql_db::TBL_WORD;
        $this->rename_can_switch = UI_CAN_CHANGE_WORD_NAME;
    }

    /**
     * clear the word object values
     * @return void
     */
    function reset(): void
    {
        parent::reset();
        $this->plural = null;
        $this->values = null;

        $this->link_type_id = null;

        $this->share_id = null;
        $this->protection_id = null;

        $this->view = null;
        $this->ref_lst = [];
    }

    /**
     * map the database fields to the object fields
     *
     * this is the pure mapping function which also maps the field 'exclude'
     * the 'exclude check' needs to be done in the calling function
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object ist loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @param string $type_fld the name of the type field as defined in this child class
     * @return bool true if the word is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID,
        string $name_fld = self::FLD_NAME,
        string $type_fld = phrase::FLD_TYPE): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld);
        if ($result) {
            if (array_key_exists(self::FLD_PLURAL, $db_row)) {
                $this->plural = $db_row[self::FLD_PLURAL];
            }
            $this->type_id = $db_row[$type_fld];
            if (array_key_exists(self::FLD_PLURAL, $db_row)) {
                if ($db_row[self::FLD_VIEW] != null) {
                    $this->set_view_id($db_row[self::FLD_VIEW]);
                }
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the word
     * @param string $name mainly for test creation the name of the word
     * @param string $type_code_id the code id of the predefined phrase type
     */
    function set(int $id = 0, string $name = '', string $type_code_id = ''): void
    {
        parent::set($id, $name);

        if ($type_code_id != '') {
            $this->set_type($type_code_id);
        }
    }

    /**
     * set the phrase type of this word
     *
     * @param string $type_code_id the code id that should be added to this word
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        global $phrase_types;
        $this->type_id = $phrase_types->id($type_code_id);
    }

    /**
     * set the value to rank the words by usage
     *
     * @param int|null $usage a higher value moves the word to the top of the selection list
     * @return void
     */
    function set_usage(?int $usage): void
    {
        $this->values = $usage;
    }

    /**
     * @return int|null a higher number indicates a higher usage
     */
    function usage(): ?int
    {
        return $this->values;
    }

    /**
     * @param int $id the id of the default view the should be remembered
     */
    function set_view_id(int $id): void
    {
        if ($this->view == null) {
            $this->view = new view($this->user());
        }
        $this->view->set_id($id);
    }

    /**
     * @return int the id of the default view for this word or null if no view is preferred
     */
    function view_id(): int
    {
        if ($this->view == null) {
            return 0;
        } else {
            return $this->view->id();
        }
    }

    function set_view(view $dsp): void
    {
        $this->view = $dsp;
    }


    /*
     * get preloaded information
     */

    /**
     * get the name of the word type
     * @return string the name of the word type
     */
    function type_name(): string
    {
        global $phrase_types;
        return $phrase_types->name($this->type_id);
    }

    /**
     * get the code_id of the word type
     * @return string the code_id of the word type
     */
    function type_code_id(): string
    {
        global $phrase_types;
        return $phrase_types->code_id($this->type_id);
    }


    /*
     * cast
     */

    /**
     * @return word_api the word frontend api object
     */
    function api_obj(): word_api
    {
        $api_obj = new word_api();
        if (!$this->is_excluded()) {
            parent::fill_api_obj($api_obj);
        }
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * map a word api json to this model word object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the word values that should be mapped
     */
    function set_by_api_json(array $api_json): user_message
    {
        global $phrase_types;

        $msg = new user_message();

        // make sure that there are no unexpected leftovers
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);

        foreach ($api_json as $key => $value) {

            if ($key == api::FLD_ID) {
                $this->set_id($value);
            }
            if ($key == api::FLD_NAME) {
                $this->set_name($value);
            }
            if ($key == api::FLD_DESCRIPTION) {
                if ($value <> '') {
                    $this->description = $value;
                }
            }
            if ($key == api::FLD_TYPE) {
                $this->type_id = $phrase_types->id($value);
            }

            /* TODO
            if ($key == self::FLD_PLURAL) {
                if ($value <> '') {
                    $this->plural = $value;
                }
            }
            if ($key == share_type::JSON_FLD) {
                $this->share_id = $share_types->id($value);
            }
            if ($key == protection_type::JSON_FLD) {
                $this->protection_id = $protection_types->id($value);
            }
            if ($key == exp_obj::FLD_VIEW) {
                $wrd_view = new view($this->user());
                if ($do_save) {
                    $wrd_view->load_by_name($value, view::class);
                    if ($wrd_view->id == 0) {
                        $result->add_message('Cannot find view "' . $value . '" when importing ' . $this->dsp_id());
                    } else {
                        $this->view_id = $wrd_view->id;
                    }
                } else {
                    $wrd_view->set_name($value);
                }
                $this->view = $wrd_view;
            }

            if ($key == api::FLD_PHRASES) {
                $phr_lst = new phrase_list($this->user());
                $msg->add($phr_lst->db_obj($value));
                if ($msg->is_ok()) {
                    $this->grp->phr_lst = $phr_lst;
                }
            }
            */

        }

        return $msg;
    }


    /*
     * loading / database access object (DAO) functions
     */

    /**
     * create the SQL to load the default word always by the id
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc, string $class = self::class): sql_par
    {
        $sc->set_type(sql_db::TBL_WORD);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return parent::load_standard_sql($sc, $class);
    }

    /**
     * load the word parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @param string $class the name of this class to be delivered to the parent function
     * @return bool true if the standard word has been loaded
     */
    function load_standard(?sql_par $qp = null, string $class = self::class): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        $result = parent::load_standard($qp, $class);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a word from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    protected function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_obj_vars($sc, $class);
        $qp->name .= $query_name;

        $sc->set_type(sql_db::TBL_WORD);
        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_usr_fields(self::FLD_NAMES_USR);
        $sc->set_usr_num_fields(self::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a word by id from the database
     * added to word just to assign the class for the user sandbox object
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_id($sc, $id, $class);
    }

    /**
     * just set the class name for the user sandbox function
     * load a word object by database id
     * @param int $id the id of the word
     * @param string $class the word class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        return parent::load_by_id($id, $class);
    }

    /**
     * just set the class name for the user sandbox function
     * load a word object by name
     * @param string $name the name word
     * @param string $class the word class name
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name, string $class = self::class): int
    {
        return parent::load_by_name($name, $class);
    }

    /**
     * @return string with the id field name of the word (not the related formula)
     */
    protected function id_field(): string
    {
        return self::FLD_ID;
    }

    function name_field(): string
    {
        return self::FLD_NAME;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }

    /**
     * return the main word object based on an id text e.g. used in view.php to get the word to display
     * TODO: check if needed and review
     */
    function main_wrd_from_txt($id_txt): void
    {
        if ($id_txt <> '') {
            log_debug('from "' . $id_txt . '"');
            $wrd_ids = explode(",", $id_txt);
            log_debug('check if "' . $wrd_ids[0] . '" is a number');
            if (is_numeric($wrd_ids[0])) {
                $this->load_by_id($wrd_ids[0]);
                log_debug('from "' . $id_txt . '" got id ' . $this->id);
            } else {
                $this->load_by_name($wrd_ids[0]);
                log_debug('from "' . $id_txt . '" got name ' . $this->name);
            }
        }
    }

    /*
     * data retrieval functions
     */

    /**
     * get a list of values related to this word
     * @param int $page the offset / page
     * @param int $size the number of values that should be returned
     * @return value_list a list object with the most relevant values related to this word
     */
    function value_list(int $page = 1, int $size = SQL_ROW_LIMIT): value_list
    {
        $val_lst = new value_list($this->user());
        $val_lst->load_old($page, $size);
        return $val_lst;
    }

    /**
     * get the view object for this word
     */
    function load_view(): ?view
    {
        $result = null;

        //$this->load_obj_vars();

        if ($this->view != null) {
            $result = $this->view;
        } else {
            if ($this->view_id() > 0) {
                $result = new view($this->user());
                if ($result->load_by_id($this->view_id())) {
                    $this->view = $result;
                    log_debug('for ' . $this->dsp_id() . ' is ' . $result->dsp_id());
                }
            }
        }

        return $result;
    }

    /**
     * calculate the suggested default view for this word
     * TODO review, because is it needed? get the view used by most users for this word
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function view_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_WORD);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(array(self::FLD_VIEW));
        $db_con->set_join_usr_count_fields(array(user::FLD_ID), sql_db::TBL_WORD);
        $qp = new sql_par(self::class);
        $qp->name = 'word_view_most_used';
        $db_con->set_name($qp->name);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * get the suggested view
     * @return int the view of the most often used view
     */
    function calc_view_id(): int
    {
        log_debug('for ' . $this->dsp_id());

        global $db_con;

        $view_id = 0;
        $qp = $this->view_sql($db_con);
        $db_row = $db_con->get1($qp);
        if (isset($db_row)) {
            $view_id = $db_row[self::FLD_VIEW];
        }

        log_debug('for ' . $this->dsp_id() . ' got ' . $view_id);
        return $view_id;
    }

    /**
     * get the view used by most other users
     * @return view the view of the most often used view
     */
    function suggested_view(): view
    {
        $dsp = new view($this->user());
        $dsp->load_by_phrase($this->phrase());
        return $dsp;
    }

    /**
     * get a list of all values related to this word
     */
    function val_lst(): value_list
    {
        $lib = new library();
        log_debug('for ' . $this->dsp_id() . ' and user "' . $this->user()->name . '"');
        $val_lst = new value_list($this->user());
        $val_lst->phr = $this->phrase();
        $val_lst->limit = SQL_ROW_MAX;
        $val_lst->load_old();
        log_debug('got ' . $lib->dsp_count($val_lst->lst()));
        return $val_lst;
    }

    /**
     * if there is just one formula linked to the word, get it
     * TODO separate the query parameter creation and add a unit test
     * TODO allow also to retrieve a list of formulas
     * TODO get the user specific list of formulas
     */
    function formula(): formula
    {
        log_debug('for ' . $this->dsp_id() . ' and user "' . $this->user()->name . '"');

        global $db_con;

        $db_con->set_type(sql_db::TBL_FORMULA_LINK);
        $qp = new sql_par(self::class);
        $qp->name = 'word_formula_by_id';
        $db_con->set_name($qp->name);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_where_link_no_fld(0, 0, $this->id);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();
        $db_row = $db_con->get1($qp);
        $frm = new formula($this->user());
        if ($db_row !== false) {
            if ($db_row[formula::FLD_ID] > 0) {
                $frm->load_by_id($db_row[formula::FLD_ID], formula::class);
            }
        }

        return $frm;
    }


    /*
     * im- and export
     */

    /**
     * import a word from a json data word object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        global $phrase_types;
        global $share_types;
        global $protection_types;

        log_debug();

        // reset all parameters for the word object but keep the user
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);
        $result = parent::import_obj($in_ex_json, $test_obj);
        foreach ($in_ex_json as $key => $value) {
            if ($key == exp_obj::FLD_TYPE) {
                $this->type_id = $phrase_types->id($value);
            }
            if ($key == self::FLD_PLURAL) {
                if ($value <> '') {
                    $this->plural = $value;
                }
            }
            // TODO change to view object like in triple
            if ($key == exp_obj::FLD_VIEW) {
                $wrd_view = new view($this->user());
                if (!$test_obj) {
                    $wrd_view->load_by_name($value, view::class);
                    if ($wrd_view->id == 0) {
                        $result->add_message('Cannot find view "' . $value . '" when importing ' . $this->dsp_id());
                    } else {
                        $this->set_view_id($wrd_view->id());
                    }
                } else {
                    $wrd_view->set_name($value);
                }
                $this->view = $wrd_view;
            }
        }

        // set the default type if no type is specified
        if ($this->type_id <= 0) {
            $this->type_id = $phrase_types->default_id();
        }
        // save the word in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                $result->add_message($this->save());
            }
        }

        // add related parameters to the word object
        if ($result->is_ok()) {
            log_debug('saved ' . $this->dsp_id());

            if ($this->id <= 0) {
                $result->add_message('Word ' . $this->dsp_id() . ' cannot be saved');
            } else {
                foreach ($in_ex_json as $key => $value) {
                    if ($result->is_ok()) {
                        if ($key == self::FLD_REFS) {
                            foreach ($value as $ref_data) {
                                $ref_obj = new ref($this->user());
                                $ref_obj->phr = $this->phrase();
                                $result->add($ref_obj->import_obj($ref_data, $test_obj));
                                $this->ref_lst[] = $ref_obj;
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * create a word object for the export
     * @param bool $do_load can be set to false for unit testing
     * @return sandbox_exp_named a reduced word object that can be used to create a JSON message
     */
    function export_obj(bool $do_load = true): sandbox_exp_named
    {
        global $phrase_types;
        global $share_types;
        global $protection_types;

        log_debug();
        $result = new word_exp();

        if ($this->name <> '') {
            $result->name = $this->name();
        }
        if ($this->plural <> '') {
            $result->plural = $this->plural;
        }
        if ($this->description <> '') {
            $result->description = $this->description;
        }
        if ($this->type_id > 0) {
            if ($this->type_id <> $phrase_types->default_id()) {
                $result->type = $this->type_code_id();
            }
        }

        // add the share type
        if ($this->share_id > 0 and $this->share_id <> $share_types->id(share_type::PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id > 0 and $this->protection_id <> $protection_types->id(protection_type::NO_PROTECT)) {
            $result->protection = $this->protection_type_code_id();
        }

        if ($this->view_id() > 0) {
            if ($do_load) {
                $this->view = $this->load_view();
            }
        }
        if (isset($this->view)) {
            $result->view = $this->view->name();
        }
        if (isset($this->ref_lst)) {
            foreach ($this->ref_lst as $ref) {
                $result->refs[] = $ref->export_obj();
            }
        }

        log_debug(json_encode($result));
        return $result;
    }

    /**
     * set the word object vars based on an api json array
     * similar to import_obj but using the database id instead of the names
     * the other side of the api_obj function
     *
     * @param array $api_json the api array
     * @return user_message false if a value could not be set
     */
    function save_from_api_msg(array $api_json, bool $do_save = true): user_message
    {
        log_debug();
        $result = new user_message();

        foreach ($api_json as $key => $value) {

            if ($key == exp_obj::FLD_NAME) {
                $this->name = $value;
            }
            if ($key == exp_obj::FLD_DESCRIPTION) {
                $this->description = $value;
            }
            if ($key == exp_obj::FLD_TYPE_ID) {
                $this->type_id = $value;
            }
        }

        if ($result->is_ok() and $do_save) {
            $result->add_message($this->save());
        }

        return $result;
    }


    /*
     * display functions
     */

    /**
     * return the name (just because all objects should have a name function)
     */
    function name_dsp(): string
    {
        if ($this->is_excluded()) {
            return '';
        } else {
            return $this->name;
        }
    }

    /*
     * display
     * TODO move to frontend
     */

    /**
     * returns the html code to select a word link type
     * database link must be open
     * TODO: similar to verb->dsp_selector maybe combine???
     */
    function selector_link($id, $form, $back): string
    {
        log_debug('verb id ' . $id);
        global $db_con;

        $result = '';

        $sql_name = "";
        if ($db_con->get_type() == sql_db::POSTGRES) {
            $sql_name = "CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name";
        } elseif ($db_con->get_type() == sql_db::MYSQL) {
            $sql_name = "IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name";
        } else {
            log_err('Unknown db type ' . $db_con->get_type());
        }
        $sql_avoid_code_check_prefix = "SELECT";
        $sql = $sql_avoid_code_check_prefix . " * FROM (
            SELECT verb_id AS id, 
                   " . $sql_name . ",
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
        $sel = new html_selector;
        $sel->form = $form;
        $sel->name = 'verb';
        $sel->sql = $sql;
        $sel->selected = $id;
        $sel->dummy_text = '';
        $result .= $sel->display_old();

        if ($this->user()->is_admin()) {
            // admin users should always have the possibility to create a new link type
            $result .= \html\btn_add('add new link type', '/http/verb_add.php?back=' . $back);
        }

        return $result;
    }

    // to select an existing word to be added
    private function selector_add($id, $form, $bs_class): string
    {
        log_debug('word_dsp->selector_add ... word id ' . $id);
        $result = '';
        $sel = new html_selector;
        $sel->form = $form;
        $sel->name = 'add';
        $sel->label = "Word:";
        $sel->bs_class = $bs_class;
        $sel->sql = sql_lst_usr("word", $this->user());
        $sel->selected = $id;
        $sel->dummy_text = '... or select an existing word to link it';
        $result .= $sel->display_old();

        return $result;
    }

    /**
     * @returns string the html code to select a word
     * database link must be open
     */
    function selector_word(int $id, int $pos, string $form_name): string
    {
        log_debug('word_dsp->selector_word ... word id ' . $id);
        $result = '';

        if ($pos > 0) {
            $field_id = "word" . $pos;
        } else {
            $field_id = "word";
        }
        $sel = new html_selector;
        $sel->form = $form_name;
        $sel->name = $field_id;
        $sel->sql = sql_lst_usr("word", $this->user());
        $sel->selected = $id;
        $sel->dummy_text = '';
        $result .= $sel->display_old();

        log_debug('word_dsp->selector_word ... done ' . $id);
        return $result;
    }

    /**
     * @param string $script
     * @param string $bs_class
     * @return string
     */
    private function type_selector(string $script, string $bs_class): string
    {
        $result = '';
        $sel = new html_selector;
        $sel->form = $script;
        $sel->name = 'type';
        $sel->label = "Word type:";
        $sel->bs_class = $bs_class;
        $sel->sql = sql_lst("phrase_type");
        $sel->selected = $this->type_id;
        $sel->dummy_text = '';
        $result .= $sel->display_old();
        return $result;
    }

    /**
     * @return string HTML code to edit all word fields
     */
    function dsp_add(int $wrd_id, int $wrd_to, int $vrb_id, $back): string
    {
        log_debug('word_dsp->dsp_add ' . $this->dsp_id() . ' or link the existing word with id ' . $wrd_id . ' to ' . $wrd_to . ' by verb ' . $vrb_id . ' for user ' . $this->user()->name . ' (called by ' . $back . ')');
        $result = '';
        $html = new html_base();

        $form = "word_add";
        $result .= $html->dsp_text_h2('Add a new word');
        $result .= $html->dsp_form_start($form);
        $result .= $html->dsp_form_hidden("back", $back);
        $result .= $html->dsp_form_hidden("confirm", '1');
        $result .= '<div class="form-row">';
        $result .= $html->dsp_form_text("word_name", $this->name, "Name:", "col-sm-4");
        $result .= $this->dsp_type_selector($form, "col-sm-4");
        $result .= $this->selector_add($wrd_id, $form, "form-row") . ' ';
        $result .= '</div>';
        $result .= 'which ';
        $result .= '<div class="form-row">';
        $result .= $this->selector_link($vrb_id, $form, $back);
        $result .= $this->selector_word($wrd_to, 0, $form);
        $result .= '</div>';
        $result .= $html->dsp_form_end('', $back);

        log_debug('word_dsp->dsp_add ... done');
        return $result;
    }

    function dsp_formula(string $back = ''): string
    {
        global $phrase_types;
        $html = new html_base();

        $result = '';
        if ($this->type_id == $phrase_types->id(phrase_type::FORMULA_LINK)) {
            $result .= $html->dsp_form_hidden("name", $this->name);
            $result .= '  to change the name of "' . $this->name . '" rename the ';
            $frm = $this->formula();
            $frm_html = new formula_dsp($frm->api_json());
            $result .= $frm_html->display_linked($back);
            $result .= '.<br> ';
        } else {
            $result .= $html->dsp_form_text("name", $this->name, "Name:", "col-sm-4");
        }
        return $result;
    }

    function dsp_type_selector(string $back = ''): string
    {
        global $phrase_types;
        $result = '';
        if ($this->type_id == $phrase_types->id(phrase_type::FORMULA_LINK)) {
            $result .= ' type: ' . $this->type_name();
        } else {
            $result .= $this->type_selector('word_edit', "col-sm-4");
        }
        return $result;
    }

    function dsp_graph(foaf_direction $direction, verb_list $link_types, string $back = ''): string
    {
        return $this->phrase()->dsp_graph($direction, $link_types, $back);
    }


    /**
     * HTML code to edit all word fields
     */
    function dsp_edit(string $back = ''): string
    {
        $html = new html_base();
        $phr_lst_up = $this->parents();
        $phr_lst_down = $this->children();
        $phr_lst_up_dsp = new phrase_list_dsp($phr_lst_up->api_json());
        $phr_lst_down_dsp = new phrase_list_dsp($phr_lst_down->api_json());
        $dsp_graph = $phr_lst_up_dsp->dsp_graph($this->phrase(), $back);
        $dsp_graph .= $phr_lst_down_dsp->dsp_graph($this->phrase(), $back);
        $wrd_dsp = new word_dsp($this->api_json());
        // collect the display code for the user changes
        $dsp_log = '';
        $changes = $this->dsp_hist(1, SQL_ROW_LIMIT, '', $back);
        if (trim($changes) <> "") {
            $dsp_log .= $html->dsp_text_h3("Latest changes related to this word", "change_hist");
            $dsp_log .= $changes;
        }
        $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back);
        if (trim($changes) <> "") {
            $dsp_log .= $html->dsp_text_h3("Latest link changes related to this word", "change_hist");
            $dsp_log .= $changes;
        }
        return $wrd_dsp->form_edit(
            $dsp_graph,
            $dsp_log,
            $this->dsp_formula($back),
            $this->dsp_type_selector(word_dsp::FORM_EDIT, $back),
            $back);
    }

    function view(): ?view
    {
        return $this->load_view();
    }

    /*
    // offer the user to export the word and the relations as a xml file
    function config_json_export(string $back = ''): string
    {
        return 'Export as <a href="/http/get_json.php?words=' . $this->name . '&back=' . $back . '">JSON</a>';
    }

    // offer the user to export the word and the relations as a xml file
    function config_xml_export($back)
    {
        $result = '';
        $result .= 'Export as <a href="/http/get_xml.php?words=' . $this->name . '&back=' . $back . '">XML</a>';
        return $result;
    }

    // offer the user to export the word and the relations as a xml file
    function config_csv_export($back)
    {
        $result = '<a href="/http/get_csv.php?words=' . $this->name . '&back=' . $back . '">CSV</a>';
        return $result;
    }
    */

    /**
     * to add a word linked to this word
     * e.g. if this word is "Company" to add another company
     */
    function btn_add(string $back = ''): string
    {
        global $verbs;
        global $phrase_types;
        $html = new html_base();
        $vrb_is = $verbs->id(verb::IS);
        $wrd_type = $phrase_types->default_id(); // maybe base it on the other linked words
        $wrd_add_title = "add a new " . $this->name();
        $url = $html->url(controller::DSP_WORD_ADD, 0, $back,
            "verb=" . $vrb_is . "&word=" . $this->id . "&type=" . $wrd_type);
        return (new button($url, $back))->add('', $wrd_add_title);
    }

    /**
     * get the database id of the word type
     * also to fix a problem if a phrase list contains a word
     * @return int the id of the word type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }

    /**
     * @param string $type the ENUM string of the fixed type
     * @returns bool true if the word has the given type
     */
    function is_type(string $type): bool
    {
        global $phrase_types;

        log_debug($this->dsp_id() . ' is ' . $type);

        $result = false;
        if ($this->type_id == $phrase_types->id($type)) {
            $result = true;
            log_debug($this->dsp_id() . ' is ' . $type);
        }
        return $result;
    }

    /**
     * @returns bool true if the word has the type "time"
     */
    function is_time(): bool
    {
        return $this->is_type(phrase_type::TIME);
    }

    /**
     * @return bool true if the word is just to define the default period
     */
    function is_time_jump(): bool
    {
        return $this->is_type(phrase_type::TIME_JUMP);
    }

    /**
     * @returns bool true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(phrase_type::MEASURE);
    }

    /**
     * @returns bool true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type::SCALING)
            or $this->is_type(phrase_type::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @returns bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type::PERCENT);
    }

    /**
     * tree building function
     * ----------------------
     *
     * Overview for words, triples and phrases and it's lists
     *
     * children and            parents return the direct parents and children   without the original phrase(s)
     * foaf_children and       foaf_parents return the    all parents and children   without the original phrase(s)
     * are and                 is return the    all parents and children including the original phrase(s) for the specific verb "is a"
     * contains                        return the    all             children including the original phrase(s) for the specific verb "contains"
     * is part of return the    all parents                without the original phrase(s) for the specific verb "contains"
     * next and              prior return the direct parents and children   without the original phrase(s) for the specific verb "follows"
     * followed_by and        follower_of return the    all parents and children   without the original phrase(s) for the specific verb "follows"
     * differentiated_by and differentiator_for return the    all parents and children   without the original phrase(s) for the specific verb "can_contain"
     *
     * Samples
     *
     * the        parents of  "ABB" can be "public limited company"
     * the   foaf_parents of  "ABB" can be "public limited company" and "company"
     * "is" of  "ABB" can be "public limited company" and "company" and "ABB" (used to get all related values)
     * the       children for "company" can include "public limited company"
     * the  foaf_children for "company" can include "public limited company" and "ABB"
     * "are" for "company" can include "public limited company" and "ABB" and "company" (used to get all related values)
     *
     * "contains" for "balance sheet" is "assets" and "liabilities" and "company" and "balance sheet" (used to get all related values)
     * "is part of" for "assets" is "balance sheet" but not "assets"
     *
     * "next" for "2016" is "2017"
     * "prior" for "2017" is "2016"
     * "is followed by" for "2016" is "2017" and "2018"
     * "is follower of" for "2016" is "2015" and "2014"
     *
     * "wind energy" and "energy" "can be differentiator for" "sector"
     * "sector" "can be differentiated_by"  "wind energy" and "energy"
     *
     * if "wind energy" "is part of" "energy"
     */

    /**
     * helper function that returns a phrase list object just with the word object
     * @return phrase_list a new phrase list just with this word as an entry
     */
    function lst(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        $phr_lst->add($this->phrase());
        return $phr_lst;
    }

    /**
     * returns a list of words (actually phrases) that are related to this word
     * e.g. for "Zurich" it will return "Canton", "City" and "Company", but not "Zurich" itself
     */
    function parents(): phrase_list
    {
        global $verbs;
        log_debug('for ' . $this->dsp_id() . ' and user ' . $this->user()->id());
        $phr_lst = $this->lst();
        $parent_phr_lst = $phr_lst->foaf_parents($verbs->get(verb::IS));
        log_debug('are ' . $parent_phr_lst->dsp_name() . ' for ' . $this->dsp_id());
        return $parent_phr_lst;
    }

    /**
     * TODO maybe collect the single words or this is a third case
     * returns a list of words that are related to this word
     * e.g. for "Zurich" it will return "Canton", "City" and "Company" and "Zurich" itself
     *      to be able to collect all relations to the given word e.g. Zurich
     */
    function is(): phrase_list
    {
        $phr_lst = $this->parents();
        $phr_lst->add($this->phrase());
        log_debug($this->dsp_id() . ' is a ' . $phr_lst->dsp_name());
        return $phr_lst;
    }

    /**
     * returns the best guess category for a word  e.g. for "ABB" it will return only "Company"
     */
    function is_mainly(): phrase
    {
        $result = null;
        $is_phr_lst = $this->is();
        if (!$is_phr_lst->is_empty()) {
            $result = $is_phr_lst->lst()[0];
        }
        log_debug($this->dsp_id() . ' is a ' . $result->name());
        return $result;
    }

    /**
     * add a child word to this word
     * e.g. Zurich (child) is a Canton (Parent)
     * @param word $child the word that should be added as a child
     * @return bool
     */
    function add_child(word $child): bool
    {
        global $verbs;

        $result = false;
        $wrd_lst = $this->children();
        if (!$wrd_lst->does_contain($child)) {
            $wrd_lnk = new triple($this->user());
            $wrd_lnk->fob = $child->phrase();
            $wrd_lnk->verb = $verbs->get(verb::IS);
            $wrd_lnk->tob = $this->phrase();
            if ($wrd_lnk->save() == '') {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return phrase_list a list of words that are related to this word
     * e.g. for "Canton" it will return "Zurich (Canton)" and others, but not "Canton" itself
     */
    function children(): phrase_list
    {
        global $verbs;
        log_debug('for ' . $this->dsp_id() . ' and user ' . $this->user()->id());
        $phr_lst = $this->lst();
        $child_phr_lst = $phr_lst->all_children($verbs->get(verb::IS));
        log_debug('are ' . $child_phr_lst->name() . ' for ' . $this->dsp_id());
        return $child_phr_lst;
    }

    /**
     * @return phrase_list a list of words that are related to the given word
     * e.g. for "Canton" it will return "Zurich (Canton)" and "Canton", but not "Zurich (City)"
     * used to collect e.g. all formulas used for Canton
     */
    function are(): phrase_list
    {
        $wrd_lst = $this->children();
        $wrd_lst->add($this->phrase());
        return $wrd_lst;
    }

    /**
     * @return phrase_list a list of phrases that are 'part of'/'contain' this phrase
     * e.g. for "Switzerland" it will return "Zurich (Canton)" and "Zurich (City)" which is part of the Canton
     */
    function parts(): phrase_list
    {
        global $verbs;
        $phr_lst = $this->lst();
        return $phr_lst->foaf_children($verbs->get(verb::IS_PART_OF));
    }

    /**
     * @return phrase_list a list of phrases that are 'part of'/'contain' this phrase
     * e.g. for "Switzerland" it will return "Zurich (Canton)" but not "Zurich (City)"
     */
    function direct_parts(): phrase_list
    {
        global $verbs;
        $phr_lst = $this->lst();
        return $phr_lst->foaf_children($verbs->get(verb::IS_PART_OF), 1);
    }

    /**
     * makes sure that all combinations of "are" and "contains" are included
     * @return phrase_list all phrases linked with are and contains
     */
    function are_and_contains(): phrase_list
    {
        log_debug('for ' . $this->dsp_id());

        // this first time get all related items
        $phr_lst = $this->lst();
        $phr_lst = $phr_lst->are();
        $added_lst = $phr_lst->contains();
        $added_lst->diff($this->lst());
        // ... and after that get only for the new
        if ($added_lst->count() > 0) {
            $loops = 0;
            log_debug('added ' . $added_lst->dsp_id() . ' to ' . $phr_lst->dsp_id());
            do {
                $next_lst = clone $added_lst;
                $next_lst = $next_lst->are();
                $added_lst = $next_lst->contains();
                $added_lst->diff($phr_lst);
                if (!$added_lst->is_empty()) {
                    log_debug('add ' . $added_lst->dsp_id() . ' to ' . $phr_lst->dsp_id());
                }
                $phr_lst->merge($added_lst);
                $loops++;
            } while (count($added_lst->lst()) > 0 and $loops < MAX_LOOP);
        }
        log_debug($this->dsp_id() . ' are_and_contains ' . $phr_lst->dsp_id());
        return $phr_lst;
    }

    /**
     * @return word the follow word id based on the predefined verb following
     * TODO create unit tests
     */
    function next(): word
    {
        log_debug($this->dsp_id());

        global $db_con;
        global $verbs;

        $result = new word($this->user());

        $link_id = $verbs->id(verb::FOLLOW);
        $db_con->usr_id = $this->user()->id();
        $db_con->set_type(sql_db::TBL_TRIPLE);
        $key_result = $db_con->get_value_2key('from_phrase_id', 'to_phrase_id', $this->id, verb::FLD_ID, $link_id);
        if (is_numeric($key_result)) {
            $id = intval($key_result);
            if ($id > 0) {
                $result->load_by_id($id);
            }
        }
        return $result;
    }

    /**
     * return the follow word id based on the predefined verb following
     * TODO create unit tests
     */
    function prior(): word
    {
        log_debug($this->dsp_id());

        global $db_con;
        global $verbs;

        $result = new word($this->user());

        $link_id = $verbs->id(verb::FOLLOW);
        $db_con->usr_id = $this->user()->id();
        $db_con->set_type(sql_db::TBL_TRIPLE);
        $key_result = $db_con->get_value_2key('to_phrase_id', 'from_phrase_id', $this->id, verb::FLD_ID, $link_id);
        if (is_numeric($key_result)) {
            $id = intval($key_result);
            if ($id > 0) {
                $result->load_by_id($id);
            }
        }
        return $result;
    }

    /**
     * calculates how many times a word is used, because this can be helpful for sorting
     */
    function calc_usage(): bool
    {
        global $db_con;

        $sql = 'UPDATE words t
             SET ' . $db_con->sf("values") . ' = ( 
          SELECT COUNT(value_id) 
            FROM value_phrase_links l
           WHERE l.phrase_id = t.word_id);';
        $db_con->exe_try('Calculate word usage', $sql);
        return true;
    }

    /**
     * returns the more general word as defined by "is part of"
     * e.g. for "Meilen (District)" it will return "Zürich (Canton)"
     * for the value selection this should be tested level by level
     * to use by default the most specific value
     */
    function is_part(): phrase_list
    {
        global $verbs;
        log_debug($this->dsp_id() . ', user ' . $this->user()->id());
        $phr_lst = $this->lst();
        $is_phr_lst = $phr_lst->foaf_parents($verbs->get(verb::IS_PART_OF));

        log_debug($this->dsp_id() . ' is a ' . $is_phr_lst->dsp_name());
        return $is_phr_lst;
    }


    /*
     * functions that create and fill related objects
     */

    /**
     * returns a list of the link types related to this word e.g. for "Company" the link "are" will be returned, because "ABB" "is a" "Company"
     */
    function link_types(foaf_direction $direction): verb_list
    {
        log_debug($this->dsp_id() . ' and user ' . $this->user()->id());

        global $db_con;

        $vrb_lst = new verb_list($this->user());
        $wrd = clone $this;
        $phr = $wrd->phrase();
        $vrb_lst->load_by_linked_phrases($db_con, $phr, $direction);
        return $vrb_lst;
    }

    /**
     * return a list of upward related verbs e.g. 'is a' for Zurich because Zurich is a City
     */
    private function verb_list_up(): verb_list
    {
        return $this->link_types(foaf_direction::UP);
    }

    /**
     * return a list of downward related verbs e.g. 'contains' for Mathematical constant because Mathematical constant contains Pi
     */
    private function verb_list_down(): verb_list
    {
        return $this->link_types(foaf_direction::DOWN);
    }

    private function phrase_list_up(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        return $phr_lst->parents();
    }

    private function phrase_list_down(): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        return $phr_lst->direct_children();
    }

    /*
     * display functions
     */

    /**
     * display the history of a word
     * maybe move this to a new object user_log_display
     * because this is very similar to a value linked function
     */
    function dsp_hist(int $page = 1, int $size = 20, string $call = '', string $back = ''): string
    {
        log_debug("word_dsp->dsp_hist for id " . $this->id . " page " . $size . ", size " . $size . ", call " . $call . ", back " . $back . ".");
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($this->user());
        $log_dsp->id = $this->id;
        $log_dsp->type = word::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist();

        log_debug('done');
        return $result;
    }

    /**
     * display the history of a word
     */
    function dsp_hist_links($page, $size, $call, $back): string
    {
        log_debug($this->id . ",size" . $size . ",b" . $size);
        $result = ''; // reset the html code var

        $log_dsp = new user_log_display($this->user());
        $log_dsp->id = $this->id;
        $log_dsp->type = word::class;
        $log_dsp->page = $page;
        $log_dsp->size = $size;
        $log_dsp->call = $call;
        $log_dsp->back = $back;
        $result .= $log_dsp->dsp_hist_links();

        log_debug('done');
        return $result;
    }


    /*
     * convert
     */

    /**
     * @returns phrase the word object cast into a phrase object
     */
    function phrase(): phrase
    {
        $phr = new phrase($this->user());
        $phr->set_obj($this);
        log_debug($this->dsp_id());
        return $phr;
    }

    /**
     * @returns term the word object cast into a term object
     */
    function term(): term
    {
        $trm = new term($this->user());
        $trm->set_id_from_obj($this->id, self::class);
        $trm->set_obj($this);
        log_debug($this->dsp_id());
        return $trm;
    }

    /*
     * save / database transfer object functions
     */

    /**
     * true if the word has any none default settings such as a special type
     */
    function has_cfg(): bool
    {
        global $phrase_types;

        $has_cfg = false;
        if (isset($this->plural)) {
            if ($this->plural <> '') {
                $has_cfg = true;
            }
        }
        if (isset($this->description)) {
            if ($this->description <> '') {
                $has_cfg = true;
            }
        }
        if (isset($this->type_id)) {
            if ($this->type_id <> $phrase_types->default_id()) {
                $has_cfg = true;
            }
        }
        if ($this->view_id() > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    function not_used(): bool
    {
        log_debug($this->id);

        if (parent::not_used()) {
            $result = true;
            // check if no value is related to the word
            // check if no phrase group is linked to the word
            // TODO if a value or formula is linked to the word the user should see a warning message, which he can confirm
            return $result;
        } else {
            return false;
        }

        /*    $change_user_id = 0;
            $sql = "SELECT user_id
                      FROM user_words
                     WHERE word_id = ".$this->id."
                       AND user_id <> ".$this->owner_id."
                       AND (excluded <> 1 OR excluded is NULL)";
            //$db_con = new mysql;
            $db_con->usr_id = $this->user()->id();
            $change_user_id = $db_con->get1($sql);
            if ($change_user_id > 0) {
              $result = false;
            } */
        //return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if the word has been changed
     */
    function not_changed_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_WORD);
        return $db_con->load_sql_not_changed($this->id, $this->owner_id);
    }

    /**
     * true if no other user has modified the word
     * assuming that in this case not confirmation from the other users for a word rename is needed
     */
    function not_changed(): bool
    {
        log_debug($this->id . ' by someone else than the owner (' . $this->owner_id);

        global $db_con;
        $result = true;

        if ($this->id == 0) {
            log_err('The id must be set to check if the triple has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con);
            $db_row = $db_con->get1($qp);
            if ($db_row[user::FLD_ID] > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->id . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * true if the user is the owner and no one else has changed the word
     * because if another user has changed the word and the original value is changed, maybe the user word also needs to be updated
     */
    function can_change(): bool
    {
        log_debug($this->id . ',u' . $this->user()->id());
        $can_change = false;
        if ($this->owner_id == $this->user()->id() or $this->owner_id <= 0) {
            $wrd_user = $this->changer();
            if ($wrd_user == $this->user()->id() or $wrd_user <= 0) {
                $can_change = true;
            }
        }

        log_debug(zu_dsp_bool($can_change));
        return $can_change;
    }

    /**
     * true if a record for a user specific configuration already exists in the database
     */
    function has_usr_cfg(): bool
    {
        $has_cfg = false;
        if ($this->usr_cfg_id > 0) {
            $has_cfg = true;
        }
        return $has_cfg;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current word
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql_db $db_con, string $class = self::class): sql_par
    {
        $db_con->set_type(sql_db::TBL_WORD);
        return parent::load_sql_user_changes($db_con, $class);
    }

    /**
     * set the log entry parameters for a value update
     */
    private
    function log_upd_view($view_id): change_log_named
    {
        log_debug($this->dsp_id() . ' for user ' . $this->user()->name);
        $dsp_new = new view_dsp_old($this->user());
        $dsp_new->load_by_id($view_id);

        $log = new change_log_named($this->user());
        $log->action = change_log_action::UPDATE;
        $log->set_table(change_log_table::WORD);
        $log->set_field(self::FLD_VIEW);
        if ($this->view_id() > 0) {
            $dsp_old = new view_dsp_old($this->user());
            $dsp_old->load_by_id($this->view_id());
            $log->old_value = $dsp_old->name();
            $log->old_id = $dsp_old->id;
        } else {
            $log->old_value = '';
            $log->old_id = 0;
        }
        $log->new_value = $dsp_new->name();
        $log->new_id = $dsp_new->id;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }

    /**
     * remember the word view, which means to save the view id for this word
     * each user can define set the view individually, so this is user specific
     */
    function save_view(int $view_id): string
    {

        global $db_con;
        $result = '';

        if ($this->id > 0 and $view_id > 0 and $view_id <> $this->view_id()) {
            log_debug($view_id . ' for ' . $this->dsp_id() . ' and user ' . $this->user()->id());
            if ($this->log_upd_view($view_id) > 0) {
                //$db_con = new mysql;
                $db_con->usr_id = $this->user()->id();
                if ($this->can_change()) {
                    $db_con->set_type(sql_db::TBL_WORD);
                    if (!$db_con->update($this->id, "view_id", $view_id)) {
                        $result = 'setting of view failed';
                    }
                } else {
                    if (!$this->has_usr_cfg()) {
                        if (!$this->add_usr_cfg()) {
                            $result = 'adding of user configuration failed';
                        }
                    }
                    if ($result == '') {
                        $db_con->set_type(sql_db::TBL_USER_PREFIX . sql_db::TBL_WORD);
                        if (!$db_con->update($this->id, "view_id", $view_id)) {
                            $result = 'setting of view for user failed';
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * set the update parameters for the word plural
     */
    private function save_field_plural(sql_db $db_con, word $db_rec, word $std_rec): string
    {
        $result = '';
        // if the plural is not set, don't overwrite any db entry
        if ($this->plural <> Null) {
            if ($this->plural <> $db_rec->plural) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->plural;
                $log->new_value = $this->plural;
                $log->std_value = $std_rec->plural;
                $log->row_id = $this->id;
                $log->set_field(self::FLD_PLURAL);
                $result = $this->save_field_user($db_con, $log);
            }
        }
        return $result;
    }

    /**
     * set the update parameters for the word view_id
     * @param word|sandbox $db_rec the database record before the saving
     * @return string an error message that should be shown to the user if something fails
     * TODO replace string by usr_msg to include more infos e.g. suggested solutions
     */
    private function save_field_view(word|sandbox $db_rec): string
    {
        $result = '';
        if ($db_rec->view_id() <> $this->view_id()) {
            $result = $this->save_view($this->view_id());
        }
        return $result;
    }

    /**
     * save all updated word fields
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param word|sandbox $db_rec the database record before the saving
     * @param word|sandbox $std_rec the database record defined as standard because it is used by most users
     * @return string if not empty the message that should be shown to the user
     */
    function save_fields(sql_db $db_con, word|sandbox $db_rec, word|sandbox $std_rec): string
    {
        $result = $this->save_field_plural($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_description($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_type($db_con, $db_rec, $std_rec);
        $result .= $this->save_field_view($db_rec);
        $result .= $this->save_field_excluded($db_con, $db_rec, $std_rec);
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }

    function get_obj_with_same_id_fields(): sandbox
    {
        $db_chk = parent::get_obj_with_same_id_fields();
        if ($db_chk->id > 0) {
            if ($this->obj_name == word::class or $this->obj_name == word_dsp::class) {
                // TODO check if this is always correct
                $db_chk->id = 0;
            }
        }

        return $db_chk;
    }

    /**
     * delete the references to this word which includes the phrase groups, the triples and values
     * @return user_message of the link removal and if needed the error messages that should be shown to the user
     */
    function del_links(): user_message
    {
        $result = new user_message();

        // collect all phrase groups where this word is used
        $grp_lst = new phrase_group_list($this->user());
        $grp_lst->phr = $this->phrase();
        $grp_lst->load();

        // collect all triples where this word is used
        $trp_lst = new triple_list($this->user());
        $trp_lst->load_by_phr($this->phrase());

        // collect all values related to word triple
        $val_lst = new value_list($this->user());
        $val_lst->phr = $this->phrase();
        $val_lst->load_old();

        // if there are still values, ask if they really should be deleted
        if ($val_lst->has_values()) {
            $result->add($val_lst->del());
        }

        // if there are still triples, ask if they really should be deleted
        if ($trp_lst->has_values()) {
            $result->add($trp_lst->del());
        }

        // delete the phrase groups
        $result->add($grp_lst->del());

        return $result;
    }

}
