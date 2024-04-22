<?php

/*

    cfg/word/word.php - the main word object
    -----------------

    TODO move plural to a linked word?

    TODO check if all objects follow these rules
        - database fields are defined within the object wit a const staring with FLD_
        - the object is as small as possible, means there are no redundant fields
        - for each selection and database reading function a separate load function with the search field is defined e.g. load_by_name(string name)
        - for each load function a separate load_sql function exists, which is unit tested
        - the row_mapper function is always used map the database field to the object fields
        - a minimal object exists with for display only for one user only e.g. for a word object, just the id and the name
        - a ex- and import object exists, that does not include any internal database ids

    The main sections of this object are
    - db const:          const for the database link
    - preserved:         const word names of a words used by the system
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - cast:              create an api object and set the vars from an api json
    - convert:           convert this word e.g. phrase or term
    - load:              database access object (DAO) functions
    - sql fields:        field names for sql
    - retrieval:         get related objects assigned to this word
    - im- and export:    create an export object and set the vars from an import object
    - information:       functions to make code easier to read
    - foaf:              get related words and triples based on the friend of a friend (foaf) concept
    - ui sort:           user interface optimazation e.g. show the user to most relevant words
    - related:           functions that create and fill related objects
    - sandbox:           manage the user sandbox
    - log:               write the changes to the log
    - save:              manage to update the database
    - del:               manage to remove from the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - debug:             internal support functions for debugging


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

include_once SHARED_TYPES_PATH . 'protection_type.php';
include_once SHARED_TYPES_PATH . 'share_type.php';
include_once SERVICE_EXPORT_PATH . 'sandbox_exp_named.php';
include_once SERVICE_PATH . 'db_code_link.php';
include_once API_WORD_PATH . 'word.php';
include_once MODEL_REF_PATH . 'ref.php';
include_once SERVICE_EXPORT_PATH . 'word_exp.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_typed.php';

use shared\types\protection_type as protect_type_shared;
use shared\types\share_type as share_type_shared;
use api\api;
use api\word\word as word_api;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\export\sandbox_exp;
use cfg\export\sandbox_exp_named;
use cfg\export\word_exp;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_table_list;
use cfg\value\value_list;
use shared\library;

class word extends sandbox_typed
{

    /*
     * db const
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    const FLD_ID = 'word_id'; // TODO change the user_id field comment to 'the user who has changed the standard word'
    const FLD_NAME_COM = 'the text used for searching';
    const FLD_NAME = 'word_name';
    const FLD_DESCRIPTION_COM = 'to be replaced by a language form entry';
    const FLD_TYPE_COM = 'to link coded functionality to words e.g. to exclude measure words from a percent result';
    const FLD_CODE_ID_COM = 'to link coded functionality to a specific word e.g. to get the values of the system configuration';
    const FLD_PLURAL_COM = 'to be replaced by a language form entry; TODO to be move to language forms';
    const FLD_PLURAL = 'plural'; // TODO move to language types
    const FLD_VIEW_COM = 'the default mask for this word';
    const FLD_VIEW = 'view_id';
    const FLD_VALUES_COM = 'number of values linked to the word, which gives an indication of the importance';
    const FLD_VALUES = 'values'; // TODO convert to a percent value of relative importance e.g. is 100% if all values, results, triples, formulas and views use this word; should be possible to adjust the weight of e.g. values and views with the user specific system settings
    const FLD_INACTIVE_COM = 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';
    const FLD_INACTIVE = 'inactive';
    // the field names used for the im- and export in the json or yaml format
    const FLD_REFS = 'refs';

    // list of fields that MUST be set by one user
    const FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [language::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::ONE, sql::INDEX, language::class, self::FLD_NAME_COM],
        [self::FLD_NAME, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_PLURAL, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', self::FLD_PLURAL_COM],
        [self::FLD_DESCRIPTION, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [phrase::FLD_TYPE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, phrase_type::class, self::FLD_TYPE_COM],
        [self::FLD_VIEW, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, view::class, self::FLD_VIEW_COM],
        [self::FLD_VALUES, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_VALUES_COM],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [self::FLD_INACTIVE, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_INACTIVE_COM],
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );


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
     * preserved
     */

    // const names of a words used by the system for its own configuration
    // e.g. the number of decimal places related to the user specific words
    // system configuration that is not related to user sandbox data is using the flat cfg methods
    //included in the preserved word names
    const SYSTEM_CONFIG = 'system configuration';
    // for the configuration of a single job
    const JOB_CONFIG = 'job configuration';
    // TODO complete the concrete setup
    const IMPORT_TYPE = 'import type';
    const API_WORD = 'API';
    const URL = 'url';
    const USER_WORD = 'user';
    const PASSWORD = 'password';
    const OPEN_API = 'OpenAPI';
    const DEFINITION = 'definition';


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

        $lib = new library();
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
            if (array_key_exists($type_fld, $db_row)) {
                $this->type_id = $db_row[$type_fld];
            }
            if (array_key_exists(self::FLD_VIEW, $db_row)) {
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
     *r
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

    /**
     * get the database id of the word type
     * also to fix a problem if a phrase list contains a word
     * @return int|null the id of the word type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }


    /*
     * preloaded
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
     * TODO add a test case to check if an import of a pure name overwrites the existing type setting
     *      or if loading later adding a word with admin_protection and type does not overwrite the type and protection
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
            if ($key == share_type_shared::JSON_FLD) {
                $this->share_id = $share_types->id($value);
            }
            if ($key == protect_type_shared::JSON_FLD) {
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
     * load
     */

    /**
     * just set the class name for the user sandbox function
     * load a word object by name
     * @param string $name the name word
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        return parent::load_by_name($name);
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
     * load a word that represents a formula by the name
     * TODO exclude the formula words in all other queries
     *
     * @param string $name the name word
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_formula_name(string $name): int
    {
        global $db_con;

        $qp = $this->load_sql_by_formula_name($db_con->sql_creator(), $name);
        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox($db_row);
        return $this->id();
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
     * create the SQL to load the default word always by the id
     *
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql $sc, string $class = self::class): sql_par
    {
        $sc->set_class(word::class);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return parent::load_standard_sql($sc, $class);
    }

    /**
     * create an SQL statement to retrieve a word by id from the database
     * added to word just to assign the class for the user sandbox object
     *
     * @param sql $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql $sc, int $id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_id($sc, $id, $class);
    }

    /**
     * create an SQL statement to retrieve a word representing a formula by name
     *
     * @param sql $sc with the target db_type set
     * @param string $name the name of the formula
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_formula_name(sql $sc, string $name): sql_par
    {
        global $phrase_types;
        $qp = parent::load_sql_usr_num($sc, $this, formula::FLD_NAME);
        $sc->add_where($this->name_field(), $name, sql_par_type::TEXT_USR);
        $sc->add_where(phrase::FLD_TYPE, $phrase_types->id(phrase_type::FORMULA_LINK), sql_par_type::CONST);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a word from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc, string $query_name, string $class = self::class): sql_par
    {
        // TODO check if and where it is needed to exclude the formula words
        // global $phrase_types;
        // $qp = parent::load_sql_usr_num($sc, $this, $query_name);
        // $sc->add_where(phrase::FLD_TYPE, $phrase_types->id(phrase_type::FORMULA_LINK), sql_par_type::CONST_NOT);
        // return $qp;
        return parent::load_sql_usr_num($sc, $this, $query_name);
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return self::FLD_NAME;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * retrieval
     */

    /**
     * get a list of values related to this word
     * @param int $page the offset / page
     * @param int $size the number of values that should be returned
     * @return value_list a list object with the most relevant values related to this word
     */
    function value_list(int $page = 1, int $size = sql_db::ROW_LIMIT): value_list
    {
        $val_lst = new value_list($this->user());
        $val_lst->load_by_phr($this->phrase(), $size, $page);
        return $val_lst;
    }

    /**
     * get a list of all values related to this word
     */
    function val_lst(): value_list
    {
        $lib = new library();
        log_debug('for ' . $this->dsp_id() . ' and user "' . $this->user()->name . '"');
        $val_lst = new value_list($this->user());
        $val_lst->load_by_phr($this->phrase());
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

        $db_con->set_class(formula_link::class);
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

    function view(): ?view
    {
        return $this->load_view();
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

        log_debug();

        // set the object vars based on the json
        $result = $this->import_obj_fill($in_ex_json, $test_obj);

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
     * set the vars of this word object based on the given json without writing to the database
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message
     */
    function import_obj_fill(array $in_ex_json, object $test_obj = null): user_message
    {
        global $phrase_types;

        // reset all parameters for the word object but keep the user
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);

        // set the object vars based on the json
        $result = parent::import_obj($in_ex_json, $test_obj);
        foreach ($in_ex_json as $key => $value) {
            if ($key == sandbox_exp::FLD_TYPE) {
                $this->type_id = $phrase_types->id($value);
            }
            if ($key == self::FLD_PLURAL) {
                if ($value <> '') {
                    $this->plural = $value;
                }
            }
            // TODO change to view object like in triple
            if ($key == sandbox_exp::FLD_VIEW) {
                $wrd_view = new view($this->user());
                if (!$test_obj) {
                    $wrd_view->load_by_name($value);
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
        if ($this->share_id > 0 and $this->share_id <> $share_types->id(share_type_shared::PUBLIC)) {
            $result->share = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id > 0 and $this->protection_id <> $protection_types->id(protect_type_shared::NO_PROTECT)) {
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

    /*
     * TODO review
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


    /*
     * information
     */

    /**
     * @param string $type the ENUM string of the fixed type
     * @returns bool true if the word has the given type
     */
    function is_type(string $type): bool
    {
        global $phrase_types;

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
     * check if the word in the database needs to be updated
     * e.g. for import  if this word has only the name set, the protection should not be updated in the database
     *
     * @param word $db_wrd the word as saved in the database
     * @return bool true if this word has infos that should be saved in the datanase
     */
    function needs_db_update(word $db_wrd): bool
    {
        $result = parent::needs_db_update_typed($db_wrd);
        if ($this->plural != null) {
            if ($this->plural != $db_wrd->plural) {
                $result = true;
            }
        }
        if ($this->values != null) {
            if ($this->values != $db_wrd->values) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * foaf
     */

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
        $parent_phr_lst = $phr_lst->foaf_parents($verbs->get_verb(verb::IS));
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
            $wrd_lnk->verb = $verbs->get_verb(verb::IS);
            $wrd_lnk->tob = $this->phrase();
            if ($wrd_lnk->save() == '') {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * get all phrases that are linked to this word with the "is a" verb
     * e.g. for "Canton" it will return "Zurich (Canton)" and others, but not "Canton" itself
     *
     * @return phrase_list a list of words that are related to this word
     */
    function children(): phrase_list
    {
        global $verbs;
        log_debug('for ' . $this->dsp_id() . ' and user ' . $this->user()->id());
        $phr_lst = $this->lst();
        $child_phr_lst = $phr_lst->all_children($verbs->get_verb(verb::IS));
        log_debug('are ' . $child_phr_lst->name() . ' for ' . $this->dsp_id());
        return $child_phr_lst;
    }

    /**
     * get all phrases that are linked to this word with the "is a" verb including the parent word
     * e.g. for "Canton" it will return "Zurich (Canton)" and "Canton", but not "Zurich (City)"
     * used to collect e.g. all formulas used for Canton
     *
     * @return phrase_list a list of words that are related to the given word
     */
    function are(): phrase_list
    {
        $phr_lst = $this->children();
        $phr_lst->add($this->phrase());
        return $phr_lst;
    }

    /**
     * @return phrase_list a list of phrases that are 'part of'/'contain' this phrase
     * e.g. for "Switzerland" it will return "Zurich (Canton)" and "Zurich (City)" which is part of the Canton
     */
    function parts(): phrase_list
    {
        global $verbs;
        $phr_lst = $this->lst();
        return $phr_lst->foaf_children($verbs->get_verb(verb::IS_PART_OF));
    }

    /**
     * @return phrase_list a list of phrases that are 'part of'/'contain' this phrase
     * e.g. for "Switzerland" it will return "Zurich (Canton)" but not "Zurich (City)"
     */
    function direct_parts(): phrase_list
    {
        global $verbs;
        $phr_lst = $this->lst();
        return $phr_lst->foaf_children($verbs->get_verb(verb::IS_PART_OF), 1);
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
        $db_con->set_class(triple::class);
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
        $db_con->set_class(triple::class);
        $key_result = $db_con->get_value_2key('to_phrase_id', 'from_phrase_id', $this->id, verb::FLD_ID, $link_id);
        if (is_numeric($key_result)) {
            $id = intval($key_result);
            if ($id > 0) {
                $result->load_by_id($id);
            }
        }
        return $result;
    }


    /*
     * ui sort
     */

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
            if ($db_row[self::FLD_VIEW] != null) {
                $view_id = $db_row[self::FLD_VIEW];
            }
        }

        log_debug('for ' . $this->dsp_id() . ' got ' . $view_id);
        return $view_id;
    }

    /**
     * get the view object for this word
     */
    function load_view(): ?view
    {
        $result = null;

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
        $db_con->set_class(word::class);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(array(self::FLD_VIEW));
        $db_con->set_join_usr_count_fields(array(user::FLD_ID), word::class);
        $qp = new sql_par(self::class);
        $qp->name = 'word_view_most_used';
        $db_con->set_name($qp->name);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * calculates how many times a word is used, because this can be helpful for sorting
     */
    function calc_usage(): bool
    {
        global $db_con;

        $sql = 'UPDATE words t
             SET ' . $db_con->sf("values") . ' = ( 
          SELECT COUNT(group_id) 
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
        $is_phr_lst = $phr_lst->foaf_parents($verbs->get_verb(verb::IS_PART_OF));

        log_debug($this->dsp_id() . ' is a ' . $is_phr_lst->dsp_name());
        return $is_phr_lst;
    }


    /*
     * related
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
     * sandbox
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
    function not_changed_sql(sql $sc): sql_par
    {
        $sc->set_class(word::class);
        return $sc->load_sql_not_changed($this->id, $this->owner_id);
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
        log_debug('for ' . $this->id);
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
     * @param sql $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(sql $sc, string $class = self::class): sql_par
    {
        $sc->set_class(word::class, [sql_type::USER]);
        return parent::load_sql_user_changes($sc, $class);
    }


    /*
     * log
     */

    /**
     * set the log entry parameters for a value update
     */
    private
    function log_upd_view($view_id): change
    {
        log_debug($this->dsp_id() . ' for user ' . $this->user()->name);
        $msk_new = new view($this->user());
        $msk_new->load_by_id($view_id);

        $log = new change($this->user());
        $log->action = change_action::UPDATE;
        $log->set_table(change_table_list::WORD);
        $log->set_field(self::FLD_VIEW);
        if ($this->view_id() > 0) {
            $msk_old = new view($this->user());
            $msk_old->load_by_id($this->view_id());
            $log->old_value = $msk_old->name();
            $log->old_id = $msk_old->id;
        } else {
            $log->old_value = '';
            $log->old_id = 0;
        }
        $log->new_value = $msk_new->name();
        $log->new_id = $msk_new->id;
        $log->row_id = $this->id;
        $log->add();

        return $log;
    }


    /*
     * save
     */

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

            if ($key == sandbox_exp::FLD_NAME) {
                $this->name = $value;
            }
            if ($key == sandbox_exp::FLD_DESCRIPTION) {
                $this->description = $value;
            }
            if ($key == sandbox_exp::FLD_TYPE_ID) {
                $this->type_id = $value;
            }
        }

        if ($result->is_ok() and $do_save) {
            $result->add_message($this->save());
        }

        return $result;
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
            $this->set_view_id($view_id);
            if ($this->log_upd_view($view_id) > 0) {
                //$db_con = new mysql;
                $db_con->usr_id = $this->user()->id();
                if ($this->can_change()) {
                    $usr_msg = $this->update('view of word');
                    $result = $usr_msg->get_last_message();
                } else {
                    if (!$this->has_usr_cfg()) {
                        if (!$this->add_usr_cfg()) {
                            $result = 'adding of user configuration failed';
                        }
                    }
                    if ($result == '') {
                        $usr_msg = $this->update('user view of word');
                        $result = $usr_msg->get_last_message();
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


    /*
     * del
     */

    /**
     * delete the references to this word which includes the phrase groups, the triples and values
     * @return user_message of the link removal and if needed the error messages that should be shown to the user
     */
    function del_links(): user_message
    {
        $result = new user_message();

        // collect all phrase groups where this word is used
        // TODO activate
        //$grp_lst = new group_list($this->user());
        //$grp_lst->load_by_phr($this->phrase());

        // collect all triples where this word is used
        $trp_lst = new triple_list($this->user());
        $trp_lst->load_by_phr($this->phrase());

        // collect all values related to word triple
        $val_lst = new value_list($this->user());
        $val_lst->load_by_phr($this->phrase());

        // if there are still values, ask if they really should be deleted
        if ($val_lst->has_values()) {
            $result->add($val_lst->del());
        }

        // if there are still triples, ask if they really should be deleted
        if ($trp_lst->has_values()) {
            $result->add($trp_lst->del());
        }

        // delete the phrase groups
        // TODO activate
        //$result->add($grp_lst->del());

        return $result;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new word to the database
     * always all fields are included in the query to be able to remove overwrites with a null value
     *
     * @param sql $sc with the target db_type set
     * @param array $sc_par_lst the parameters for the sql statement creation* @param bool $and_log true if also the changes should be written
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(sql $sc, array $sc_par_lst = []): sql_par
    {
        // fields and values that the word has additional to the standard named user sandbox object
        $wrd_empty = $this->clone_reset();
        // for a new word the owner should be set, so remove the user id to force writing the user
        $wrd_empty->set_user($this->user()->clone_reset());
        $fields = $this->db_fields_changed($wrd_empty, $sc_par_lst);
        $values = $this->db_values_changed($wrd_empty, $sc_par_lst);
        $all_fields = $this->db_fields_all();
        // add the fields and values for logging
        if ($sc->and_log($sc_par_lst)) {
            global $change_action_list;
            $fields[] = change_action::FLD_ID;
            $values[] = $change_action_list->id(change_action::ADD);
        }
        return parent::sql_insert_named($sc, $fields, $values, $all_fields, $sc_par_lst);
    }

    /**
     * create the sql statement to update a word in the database
     *
     * @param sql $sc with the target db_type set
     * @param sandbox|word $db_row the word with the database values before the update
     * @param array $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(sql $sc, sandbox|word $db_row, array $sc_par_lst = []): sql_par
    {
        // get the fields and values that have been changed
        // and that needs to be updated in the database
        // the db_* child function call the corresponding parent function
        $fields = $this->db_fields_changed($db_row, $sc_par_lst);
        $values = $this->db_values_changed($db_row, $sc_par_lst);
        $all_fields = $this->db_fields_all();
        // unlike the db_* function the sql_update_* parent function is called directly
        return parent::sql_update_named($sc, $fields, $values, $all_fields, $sc_par_lst);
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(): array
    {
        return array_merge(
            parent::db_fields_all_named(),
            [phrase::FLD_TYPE,
                self::FLD_VIEW,
                self::FLD_PLURAL,
                self::FLD_VALUES],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database fields that have been updated
     * field list must be corresponding to the db_values_changed fields
     *
     * @param sandbox|word $sbx the compare value to detect the changed fields
     * @param array $sc_par_lst the parameters for the sql statement creation
     * @return array list of the database field names that have been updated
     */
    function db_fields_changed(sandbox|word $sbx, array $sc_par_lst = []): array
    {
        $sc = new sql();
        $do_log = $sc->and_log($sc_par_lst);
        $result = parent::db_fields_changed_named($sbx, $sc_par_lst);
        if ($sbx->type_id() <> $this->type_id()) {
            if ($do_log) {
                $result[] = sql::FLD_LOG_FIELD_PREFIX . phrase::FLD_TYPE;
            }
            $result[] = phrase::FLD_TYPE;
        }
        if ($sbx->view_id() <> $this->view_id()) {
            if ($do_log) {
                $result[] = sql::FLD_LOG_FIELD_PREFIX . self::FLD_VIEW;
            }
            $result[] = self::FLD_VIEW;
        }
        // TODO move to language forms
        if ($sbx->plural <> $this->plural) {
            if ($do_log) {
                $result[] = sql::FLD_LOG_FIELD_PREFIX . self::FLD_PLURAL;
            }
            $result[] = self::FLD_PLURAL;
        }
        // TODO rename to usage
        if ($sbx->values <> $this->values) {
            if ($do_log) {
                $result[] = sql::FLD_LOG_FIELD_PREFIX . self::FLD_VALUES;
            }
            $result[] = self::FLD_VALUES;
        }
        return array_merge($result, $this->db_fields_changed_sandbox($sbx, $sc_par_lst));
    }

    /**
     * get a list of database field values that have been updated
     *
     * @param sandbox|word $wrd the compare value to detect the changed fields
     * @param array $sc_par_lst the parameters for the sql statement creation
     * @return array list of the database field values that have been updated
     */
    function db_values_changed(sandbox|word $wrd, array $sc_par_lst = []): array
    {
        // get the preloaded ids for logging
        $sc = new sql();
        $do_log = $sc->and_log($sc_par_lst);
        $table_id = $sc->table_id($this::class);
        global $change_field_list;

        // create the value array
        $result = parent::db_values_changed_named($wrd, $sc_par_lst);
        if ($wrd->type_id() <> $this->type_id()) {
            if ($do_log) {
                $result[] = $change_field_list->id($table_id . phrase::FLD_TYPE);
            }
            $result[] = $this->type_id();
        }
        if ($wrd->view_id() <> $this->view_id()) {
            if ($do_log) {
                $result[] = $change_field_list->id($table_id . self::FLD_VIEW);
            }
            $result[] = $this->view_id();
        }
        // TODO move to language forms
        if ($wrd->plural <> $this->plural) {
            if ($do_log) {
                $result[] = $change_field_list->id($table_id . self::FLD_PLURAL);
            }
            $result[] = $this->plural;
        }
        // TODO rename to usage
        if ($wrd->values <> $this->values) {
            if ($do_log) {
                $result[] = $change_field_list->id($table_id . self::FLD_VALUES);
            }
            $result[] = $this->values;
        }
        return array_merge($result, $this->db_values_changed_sandbox($wrd, $sc_par_lst));
    }


    /*
     * debug
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

}
