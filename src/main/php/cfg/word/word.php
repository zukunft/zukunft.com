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
    - modify:            change potentially all variables of this word object
    - preloaded:         select e.g. types from cache
    - cast:              create an api object and set the vars from an api json
    - convert:           convert this word e.g. phrase or term
    - load:              database access object (DAO) functions
    - sql fields:        field names for sql
    - retrieval:         get related objects assigned to this word
    - im- and export:    create an export object and set the vars from an import object
    - information:       functions to make code easier to read
    - foaf:              get related words and triples based on the friend of a friend (foaf) concept
    - ui sort:           user interface optimization e.g. show the user to most relevant words
    - related:           functions that create and fill related objects
    - sandbox:           manage the user sandbox
    - log:               write the changes to the log
    - save:              manage to update the database
    - save helper:       helpers for updating the database
    - del:               manage to remove from the database
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

namespace cfg\word;

include_once MODEL_SANDBOX_PATH . 'sandbox_typed.php';
include_once API_WORD_PATH . 'word.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_FORMULA_PATH . 'formula.php';
include_once MODEL_FORMULA_PATH . 'formula_link.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
include_once MODEL_LANGUAGE_PATH . 'language.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_PHRASE_PATH . 'phrase_type.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_REF_PATH . 'ref.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VALUE_PATH . 'value_list.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once MODEL_VERB_PATH . 'verb_list.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_WORD_PATH . 'triple_list.php';
include_once SHARED_ENUM_PATH . 'foaf_direction.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use api\word\word as word_api;
use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type_list;
use cfg\helper\db_object_seq_id;
use cfg\formula\formula;
use cfg\formula\formula_link;
use cfg\language\language;
use cfg\log\change;
use cfg\log\change_action;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\phrase\phrase_type;
use cfg\phrase\term;
use cfg\ref\ref;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\sandbox\sandbox_typed;
use cfg\word\triple_list;
use cfg\word\triple;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value_list;
use cfg\verb\verb;
use cfg\verb\verb_list;
use cfg\view\view;
use shared\enum\foaf_direction;
use shared\json_fields;
use shared\library;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\verbs;

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
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'word_id'; // TODO change the user_id field comment to 'the user who has changed the standard word'
    const FLD_NAME_COM = 'the text used for searching';
    const FLD_NAME = 'word_name';
    const FLD_DESCRIPTION_COM = 'to be replaced by a language form entry';
    const FLD_TYPE_COM = 'to link coded functionality to words e.g. to exclude measure words from a percent result';
    const FLD_CODE_ID_COM = 'to link coded functionality to a specific word e.g. to get the values of the system configuration';
    const FLD_PLURAL_COM = 'to be replaced by a language form entry; TODO to be move to language forms';
    const FLD_PLURAL = 'plural'; // TODO move to language types
    const FLD_PLURAL_SQL_TYP = sql_field_type::NAME;
    const FLD_VIEW_COM = 'the default mask for this word';
    const FLD_VIEW = 'view_id';
    const FLD_VIEW_SQL_TYP = sql_field_type::INT;
    const FLD_VALUES_COM = 'number of values linked to the word, which gives an indication of the importance';
    const FLD_VALUES = 'values'; // TODO convert to a percent value of relative importance e.g. is 100% if all values, results, triples, formulas and views use this word; should be possible to adjust the weight of e.g. values and views with the user specific system settings
    const FLD_VALUES_SQL_TYP = sql_field_type::INT;
    const FLD_INACTIVE_COM = 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';
    const FLD_INACTIVE = 'inactive';
    const FLD_INACTIVE_SQL_TYP = sql_field_type::INT_SMALL;
    // the field names used for the im- and export in the json or yaml format
    const FLD_REFS = 'refs';

    // list of fields that MUST be set by one user
    const FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [language::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::ONE, sql::INDEX, language::class, self::FLD_NAME_COM],
        [self::FLD_NAME, self::FLD_NAME_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_PLURAL, self::FLD_PLURAL_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_PLURAL_COM],
        [sandbox_named::FLD_DESCRIPTION, sandbox_named::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [phrase::FLD_TYPE, phrase::FLD_TYPE_SQL_TYP, sql_field_default::NULL, sql::INDEX, phrase_type::class, self::FLD_TYPE_COM],
        [self::FLD_VIEW, self::FLD_VIEW_SQL_TYP, sql_field_default::NULL, sql::INDEX, view::class, self::FLD_VIEW_COM],
        [self::FLD_VALUES, self::FLD_VALUES_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_VALUES_COM],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [self::FLD_INACTIVE, self::FLD_INACTIVE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_INACTIVE_COM],
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );


    // all database field names excluding the id, standard name and user specific fields
    const FLD_NAMES = array(
        self::FLD_VALUES,
        sql::FLD_CODE_ID
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

    // code_id and name of a words used by the system for its own configuration
    // e.g. the number of decimal places related to the user specific words
    // system configuration that is not related to user sandbox data is using the flat cfg methods
    //included in the preserved word names

    // words used to select parts of the system configuration where the normal name should not be changed
    // *_COM is the tooltip for the word; to have the comments on one place the yaml is the preferred place
    const TOOLTIP_COMMENT_COM = 'keyword to read the word or triple description from the config.yaml';
    const TOOLTIP_COMMENT = 'tooltip-comment';
    const SYS_CONF_VALUE_COM = 'keyword to read the numeric value from the config.yaml';
    const SYS_CONF_VALUE = 'sys-conf-value';
    const THIS_SYSTEM = 'zukunft.com';
    const CONFIGURATION = 'configuration';
    const SYSTEM = 'system';
    const JOB = 'job';
    const POD = 'pod';
    const USER = 'user';
    const FRONTEND = 'frontend';

    // general words used also for the system configuration that have a fixed tooltip
    const TIME = 'time';
    const TIME_COM = 'Time is the continued sequence of existence and events that occurs in an apparently irreversible succession from the past, through the present, into the future';
    const YEAR = 'year';
    const YEAR_COM = 'A year is the time taken for astronomical objects to complete one orbit. For example, a year on Earth is the time taken for Earth to revolve around the Sun.';
    const CALCULATION = 'calculation';
    const CALCULATION_COM = 'A calculation is a deliberate mathematical process that transforms one or more inputs into one or more outputs or results';
    const MIN = 'min';
    const MIN_COM = 'The minimal numeric value.';
    const MAX = 'max';
    const MAX_COM = 'The maximal numeric value.';
    const AVERAGE = 'average';
    const AVERAGE_COM = 'The arithmetic mean – the sum of the numbers divided by how many numbers are in the list.';
    const DEFAULT = 'default';
    const DEFAULT_COM = 'The setting used if nothing else is specified.';
    const DATABASE = 'database';
    const DATABASE_COM = 'An organized collection of data stored and accessed electronically.';

    // general words used also for the system configuration where the initial tooltip is in the config.yaml
    const VALUE = 'value';
    const VERSION = 'version';
    const RETRY = 'retry';
    const START = 'start';
    const DELAY = 'delay';
    const SEC = 'sec';
    const BLOCK = 'block';
    const SIZE = 'size';
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const TABLE = 'table';
    const NAME = 'name';
    const PHRASE = 'phrase';
    const MILLISECOND = 'millisecond';
    const SELECT = 'select';
    const INITIAL = 'initial';
    const ENTRY = 'entry';
    const PRESELECT = 'preselect';
    const PERCENT = 'percent';
    const FUTURE = 'future';
    const COLUMNS = 'columns';
    const AUTOMATIC = 'automatic';
    const CREATE = 'create';
    const VIEW = 'view';
    const FREEZE = 'freeze';
    const URL = 'url';

    const SYSTEM_CONFIG = 'system configuration';
    // for the configuration of a single job
    // TODO complete the concrete setup
    const IMPORT_TYPE = 'import type';
    const API_WORD = 'API';
    // to group the user data and configuration within the system configuration
    const PASSWORD = 'password';
    const OPEN_API = 'OpenAPI';
    const DEFINITION = 'definition';


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields
    private ?string $code_id;  // to select single words used by the system without using the type that can potentially select more than one word
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

        $this->rename_can_switch = UI_CAN_CHANGE_WORD_NAME;
    }

    /**
     * clear the word object values
     * @return void
     */
    function reset(): void
    {
        parent::reset();
        $this->code_id = null;
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
     * @param bool $load_std true if only the standard user sandbox object is loaded
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
            if (array_key_exists(sql::FLD_CODE_ID, $db_row)) {
                $this->set_code_id($db_row[sql::FLD_CODE_ID]);
            }
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
        global $phr_typ_cac;
        $this->type_id = $phr_typ_cac->id($type_code_id);
    }

    /**
     * set the unique id to select a single word by the program
     *r
     * @param string|null $code_id the unique key to select a word used by the system e.g. for the system configuration
     * @return void
     */
    function set_code_id(?string $code_id): void
    {
        $this->code_id = $code_id;
    }

    /**
     * @return string|null the unique key or null if the word is not used by the system
     */
    function code_id(): ?string
    {
        return $this->code_id;
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
     * @param int $id the id of the default view that should be remembered
     */
    function set_view_id(int $id): void
    {
        if ($this->view == null) {
            $this->view = new view($this->user());
        }
        $this->view->set_id($id);
    }

    /**
     * @return int the id of the default view for this word or zero if no view is preferred
     */
    function view_id(): int
    {
        if ($this->view == null) {
            return 0;
        } else {
            return $this->view->id();
        }
    }

    function set_view(view $msk): void
    {
        $this->view = $msk;
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

    function set_plural(?string $plural): void
    {
        $this->plural = $plural;
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
        global $phr_typ_cac;
        return $phr_typ_cac->name($this->type_id);
    }

    /**
     * get the name of the word type or null if no type is set
     * @return string|null the name of the word type
     */
    function type_name_or_null(): ?string
    {
        global $phr_typ_cac;
        return $phr_typ_cac->name_or_null($this->type_id);
    }

    /**
     * get the code_id of the word type
     * @return string the code_id of the word type
     */
    function type_code_id(): string
    {
        global $phr_typ_cac;
        return $phr_typ_cac->code_id($this->type_id);
    }


    /*
     * cast
     */

    /**
     * create word the api object and map the fields
     * @return word_api the word frontend api object with all vars set
     */
    function api_obj(): word_api
    {
        $api_obj = new word_api();
        if ($this->is_excluded()) {
            $api_obj->set_id($this->id());
            $api_obj->excluded = true;
        } else {
            parent::fill_api_obj($api_obj);
            $api_obj->set_plural($this->plural);
        }
        return $api_obj;
    }

    /**
     * map a word api json to this model word object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * TODO add a test case to check if an import of a pure name overwrites the existing type setting
     *      or if loading later adding a word with admin_protection and type does not overwrite the type and protection
     * @param array $api_json the api array with the word values that should be mapped
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function set_by_api_json(array $api_json): user_message
    {
        $msg = parent::set_by_api_json($api_json);

        foreach ($api_json as $key => $value) {

            // TODO move plural to language forms
            if ($key == json_fields::PLURAL) {
                if ($value <> '') {
                    $this->plural = $value;
                }
            }
            /*
            if ($key == exp_obj::FLD_VIEW) {
                $wrd_view = new view($this->user());
                if ($do_save) {
                    $wrd_view->load_by_name($value);
                    if ($wrd_view->id() == 0) {
                        $result->add_message('Cannot find view >' . $value . '< when importing ' . $this->dsp_id());
                    } else {
                        $this->view_id = $wrd_view->id();
                    }
                } else {
                    $wrd_view->set_name($value);
                }
                $this->view = $wrd_view;
            }

            if ($key == json_fields::PHRASES) {
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
                log_debug('from "' . $id_txt . '" got id ' . $this->id());
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
        $trm->set_id_from_obj($this->id(), self::class);
        $trm->set_obj($this);
        log_debug($this->dsp_id());
        return $trm;
    }


    /*
     * load
     */

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
     * @return bool true if the standard word has been loaded
     */
    function load_standard(?sql_par $qp = null): bool
    {
        global $db_con;
        $qp = $this->load_standard_sql($db_con->sql_creator());
        $result = parent::load_standard($qp);

        if ($result) {
            $result = $this->load_owner();
        }
        return $result;
    }

    /**
     * create the SQL to load the default word always by the id
     *
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_standard_sql(sql_creator $sc): sql_par
    {
        $sc->set_class($this::class);
        $sc->set_fields(array_merge(
            self::FLD_NAMES,
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR,
            array(user::FLD_ID)
        ));

        return parent::load_standard_sql($sc);
    }

    /**
     * create an SQL statement to retrieve a word by id from the database
     * added to word just to assign the class for the user sandbox object
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id): sql_par
    {
        return parent::load_sql_by_id($sc, $id);
    }

    /**
     * create an SQL statement to retrieve a word representing a formula by name
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the formula
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_formula_name(sql_creator $sc, string $name): sql_par
    {
        global $phr_typ_cac;
        $qp = parent::load_sql_usr_num($sc, $this, formula::FLD_NAME);
        $sc->add_where($this->name_field(), $name, sql_par_type::TEXT_USR);
        $sc->add_where(phrase::FLD_TYPE, $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK), sql_par_type::CONST);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a word from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        // TODO check if and where it is needed to exclude the formula words
        // global $phr_typ_cac;
        // $qp = parent::load_sql_usr_num($sc, $this, $query_name);
        // $sc->add_where(phrase::FLD_TYPE, $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK), sql_par_type::CONST_NOT);
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
        $db_con->set_where_link_no_fld(0, 0, $this->id());
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();
        $db_row = $db_con->get1($qp);
        $frm = new formula($this->user());
        if ($db_row !== false) {
            if ($db_row[formula::FLD_ID] > 0) {
                $frm->load_by_id($db_row[formula::FLD_ID]);
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
        if ($test_obj == null) {
            if ($result->is_ok()) {
                $result->add($this->save());
            }
        }

        // add related parameters to the word object
        if ($result->is_ok()) {
            log_debug('saved ' . $this->dsp_id());

            if ($this->id() <= 0) {
                $result->add_message('Word ' . $this->dsp_id() . ' cannot be saved');
            } else {
                foreach ($in_ex_json as $key => $value) {
                    if ($result->is_ok()) {
                        if ($key == self::FLD_REFS) {
                            foreach ($value as $ref_data) {
                                $ref_obj = new ref($this->user());
                                $ref_obj->set_phrase($this->phrase());
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
        global $phr_typ_cac;

        // reset all parameters for the word object but keep the user
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);

        // set the object vars based on the json
        $result = parent::import_obj($in_ex_json, $test_obj);
        foreach ($in_ex_json as $key => $value) {
            if ($key == json_fields::TYPE_NAME) {
                $this->type_id = $phr_typ_cac->id($value);
            }
            if ($key == sql::FLD_CODE_ID) {
                if ($this->user()->is_admin()) {
                    if ($value <> '') {
                        $this->set_code_id($value);
                    }
                }
            }
            if ($key == self::FLD_PLURAL) {
                if ($value <> '') {
                    $this->plural = $value;
                }
            }
            // TODO change to view object like in triple
            if ($key == json_fields::VIEW) {
                $wrd_view = new view($this->user());
                if (!$test_obj) {
                    $wrd_view->load_by_name($value);
                    if ($wrd_view->id() == 0) {
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
            $this->type_id = $phr_typ_cac->default_id();
        }

        return $result;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        global $phr_typ_cac;

        $vars = parent::export_json($do_load);

        if ($this->plural <> '') {
            $vars[json_fields::PLURAL] = $this->plural;
        }
        if ($this->type_id > 0) {
            if ($this->type_id == $phr_typ_cac->default_id()) {
                unset($vars[json_fields::TYPE_NAME]);
            }
        }

        if ($this->view != null) {
            if ($this->view_id() > 0 and $this->view->name() == '') {
                if ($do_load) {
                    $this->load_view();
                }
            }
            if ($this->view->name() != '') {
                $vars[json_fields::VIEW] = $this->view->name();
            }
        }
        if (count($this->ref_lst) > 0) {
            $ref_lst = [];
            foreach ($this->ref_lst as $ref) {
                $ref_lst[] = $ref->export_json();
            }
            $vars[json_fields::REFS] = $ref_lst;
        }

        return $vars;
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
        global $phr_typ_cac;

        $result = false;
        if ($this->type_id == $phr_typ_cac->id($type)) {
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
        return $this->is_type(phrase_type_shared::TIME);
    }

    /**
     * @return bool true if the word is just to define the default period
     */
    function is_time_jump(): bool
    {
        return $this->is_type(phrase_type_shared::TIME_JUMP);
    }

    /**
     * @returns bool true if the word has the type "measure" (e.g. "meter" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(phrase_type_shared::MEASURE);
    }

    /**
     * @returns bool true if the word has the type "scaling" (e.g. "million", "million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type_shared::SCALING)
            or $this->is_type(phrase_type_shared::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @returns bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type_shared::PERCENT);
    }

    /**
     * check if the word in the database needs to be updated
     * e.g. for import  if this word has only the name set, the protection should not be updated in the database
     *
     * @param word $db_wrd the word as saved in the database
     * @return bool true if this word has infos that should be saved in the database
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
     * modify
     */

    /**
     * fill this word based on the given word
     * if the id is set in the given word loaded from the database but this import word does not yet have the db id, set the id
     * if the given description is not set (null) the description is not remove
     * if the given description is an empty string the description is removed
     *
     * @param word|db_object_seq_id $sbx word with the values that should have been updated e.g. based on the import
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(word|db_object_seq_id $sbx): user_message
    {
        $usr_msg = parent::fill($sbx);
        if ($sbx->code_id() != null) {
            $this->set_code_id($sbx->code_id());
        }
        if ($sbx->plural != null) {
            $this->plural = $sbx->plural;
        }
        if ($sbx->values != null) {
            $this->values = $sbx->values;
        }
        return $usr_msg;
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
     * contains                   return the    all             children including the original phrase(s) for the specific verb "contains"
     * is part of return the                    all parents                without the original phrase(s) for the specific verb "contains"
     * next and                     prior return the direct parents and children   without the original phrase(s) for the specific verb "follows"
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
        global $vrb_cac;
        log_debug('for ' . $this->dsp_id() . ' and user ' . $this->user()->id());
        $phr_lst = $this->lst();
        $parent_phr_lst = $phr_lst->foaf_parents($vrb_cac->get_verb(verbs::IS));
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
        global $vrb_cac;

        $result = false;
        $wrd_lst = $this->children();
        if (!$wrd_lst->does_contain($child)) {
            $wrd_lnk = new triple($this->user());
            $wrd_lnk->set_from($child->phrase());
            $wrd_lnk->set_verb($vrb_cac->get_verb(verbs::IS));
            $wrd_lnk->set_to($this->phrase());
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
        global $vrb_cac;
        log_debug('for ' . $this->dsp_id() . ' and user ' . $this->user()->id());
        $phr_lst = $this->lst();
        $child_phr_lst = $phr_lst->all_children($vrb_cac->get_verb(verbs::IS));
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
        global $vrb_cac;
        $phr_lst = $this->lst();
        return $phr_lst->foaf_children($vrb_cac->get_verb(verbs::IS_PART_OF));
    }

    /**
     * @return phrase_list a list of phrases that are 'part of'/'contain' this phrase
     * e.g. for "Switzerland" it will return "Zurich (Canton)" but not "Zurich (City)"
     */
    function direct_parts(): phrase_list
    {
        global $vrb_cac;
        $phr_lst = $this->lst();
        return $phr_lst->foaf_children($vrb_cac->get_verb(verbs::IS_PART_OF), 1);
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
        global $vrb_cac;

        $result = new word($this->user());

        $link_id = $vrb_cac->id(verbs::FOLLOW);
        $db_con->usr_id = $this->user()->id();
        $db_con->set_class(triple::class);
        $key_result = $db_con->get_value_2key(triple::FLD_FROM, triple::FLD_TO, $this->id(), verb::FLD_ID, $link_id);
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
        global $vrb_cac;

        $result = new word($this->user());

        $link_id = $vrb_cac->id(verbs::FOLLOW);
        $db_con->usr_id = $this->user()->id();
        $db_con->set_class(triple::class);
        $key_result = $db_con->get_value_2key(triple::FLD_TO, triple::FLD_FROM, $this->id(), verb::FLD_ID, $link_id);
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
        $msk = new view($this->user());
        $msk->load_by_phrase($this->phrase());
        return $msk;
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

        // TODO recreate based on the group
        /*
        $sql = 'UPDATE words t
             SET ' . $db_con->sf("values") . ' = ( 
          SELECT COUNT(group_id) 
            FROM group g
           WHERE g.phrase_id = t.word_id);';
        $db_con->exe_try('Calculate word usage', $sql);
        */
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
        global $vrb_cac;
        log_debug($this->dsp_id() . ', user ' . $this->user()->id());
        $phr_lst = $this->lst();
        $is_phr_lst = $phr_lst->foaf_parents($vrb_cac->get_verb(verbs::IS_PART_OF));

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
        global $phr_typ_cac;

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
            if ($this->type_id <> $phr_typ_cac->default_id()) {
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
        log_debug($this->id());

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
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $sc->set_class(word::class);
        return $sc->load_sql_not_changed($this->id(), $this->owner_id);
    }

    /**
     * true if no other user has modified the word
     * assuming that in this case not confirmation from the other users for a word rename is needed
     */
    function not_changed(): bool
    {
        log_debug($this->id() . ' by someone else than the owner (' . $this->owner_id);

        global $db_con;
        $result = true;

        if ($this->id() == 0) {
            log_err('The id must be set to check if the triple has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con);
            $db_row = $db_con->get1($qp);
            if ($db_row[user::FLD_ID] > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->id());
        return $result;
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
        $log->set_action(change_action::UPDATE);
        $log->set_class(word::class);
        $log->set_field(self::FLD_VIEW);
        if ($this->view_id() > 0) {
            $msk_old = new view($this->user());
            $msk_old->load_by_id($this->view_id());
            $log->old_value = $msk_old->name();
            $log->old_id = $msk_old->id();
        } else {
            $log->old_value = null;
            $log->old_id = 0;
        }
        $log->new_value = $msk_new->name();
        $log->new_id = $msk_new->id();
        $log->row_id = $this->id();
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
        $usr_msg = new user_message();

        foreach ($api_json as $key => $value) {

            if ($key == json_fields::NAME) {
                $this->name = $value;
            }
            if ($key == json_fields::DESCRIPTION) {
                $this->description = $value;
            }
            if ($key == json_fields::TYPE) {
                $this->type_id = $value;
            }
        }

        if ($usr_msg->is_ok() and $do_save) {
            $usr_msg->add($this->save());
        }

        return $usr_msg;
    }

    /**
     * remember the word view, which means to save the view id for this word
     * each user can define set the view individually, so this is user specific
     */
    function save_view(int $view_id): user_message
    {

        global $db_con;
        $usr_msg = new user_message();

        if ($this->id() > 0 and $view_id > 0 and $view_id <> $this->view_id()) {
            $this->set_view_id($view_id);
            if ($this->log_upd_view($view_id) > 0) {
                //$db_con = new mysql;
                $db_con->usr_id = $this->user()->id();
                if ($this->can_change()) {
                    $usr_msg->add($this->update('view of word'));
                } else {
                    if (!$this->has_usr_cfg()) {
                        if (!$this->add_usr_cfg()) {
                            $usr_msg->add_message('adding of user configuration failed');
                        }
                    }
                    if ($usr_msg == '') {
                        $usr_msg->add($this->update('user view of word'));
                    }
                }
            }
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the word code_id
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    private function save_field_code_id(sql_db $db_con, word $db_rec, word $std_rec): user_message
    {
        $usr_msg = new user_message();
        // if the code_id is not set, don't overwrite any db entry
        if ($this->code_id() <> Null) {
            if ($this->code_id() <> $db_rec->code_id()) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->code_id();
                $log->new_value = $this->code_id();
                $log->std_value = $std_rec->code_id();
                $log->row_id = $this->id();
                $log->set_field(sql::FLD_CODE_ID);
                $usr_msg->add($this->save_field_user($db_con, $log));
            }
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the word plural
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    private function save_field_plural(sql_db $db_con, word $db_rec, word $std_rec): user_message
    {
        $usr_msg = new user_message();
        // if the plural is not set, don't overwrite any db entry
        if ($this->plural <> Null) {
            if ($this->plural <> $db_rec->plural) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->plural;
                $log->new_value = $this->plural;
                $log->std_value = $std_rec->plural;
                $log->row_id = $this->id();
                $log->set_field(self::FLD_PLURAL);
                $usr_msg->add($this->save_field_user($db_con, $log));
            }
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the word view_id
     * @param word|sandbox $db_rec the database record before the saving
     * @return user_message the message that should be shown to the user in case something went wrong
     * TODO replace string by usr_msg to include more infos e.g. suggested solutions
     */
    private function save_field_view(word|sandbox $db_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->view_id() <> $this->view_id()) {
            $usr_msg->add($this->save_view($this->view_id()));
        }
        return $usr_msg;
    }

    /**
     * save all updated word fields
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param word|sandbox $db_obj the database record before the saving
     * @param word|sandbox $norm_obj the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_all_fields(sql_db $db_con, word|sandbox $db_obj, word|sandbox $norm_obj): user_message
    {
        $result = $this->save_field_code_id($db_con, $db_obj, $norm_obj);
        $result->add($this->save_field_plural($db_con, $db_obj, $norm_obj));
        $result->add($this->save_field_description($db_con, $db_obj, $norm_obj));
        $result->add($this->save_field_type($db_con, $db_obj, $norm_obj));
        $result->add($this->save_field_view($db_obj));
        $result->add($this->save_field_excluded($db_con, $db_obj, $norm_obj));
        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $result;
    }


    /*
     * save helper
     */

    /**
     * @return array with the reserved word names
     */
    protected function reserved_names(): array
    {
        return word_api::RESERVED_NAMES;
    }

    /**
     * @return array with the fixed word names for db read testing
     */
    protected function fixed_names(): array
    {
        return word_api::FIXED_NAMES;
    }


    /*
     * del
     */

    /**
     * delete the references to this word
     * which includes the phrase groups, the triples and values
     *
     * @return user_message of the link removal and if needed the error messages that should be shown to the user
     */
    function del_links(): user_message
    {
        $usr_msg = new user_message();

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
            $usr_msg->add($val_lst->del());
        }

        // if there are still triples, ask if they really should be deleted
        if ($trp_lst->has_values()) {
            $usr_msg->add($trp_lst->del());
        }

        // delete the phrase groups
        // TODO activate
        //$usr_msg->add($grp_lst->del());

        return $usr_msg;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list([])): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                phrase::FLD_TYPE,
                self::FLD_VIEW,
                sql::FLD_CODE_ID,
                self::FLD_PLURAL,
                self::FLD_VALUES
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param sandbox|word $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|word  $sbx,
        sql_type_list $sc_par_lst = new sql_type_list([])
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        if ($sbx->type_id() <> $this->type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . phrase::FLD_TYPE,
                    $cng_fld_cac->id($table_id . phrase::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $phr_typ_cac;
            $lst->add_type_field(
                phrase::FLD_TYPE,
                phrase::FLD_TYPE_NAME,
                $this->type_id(),
                $sbx->type_id(),
                $phr_typ_cac);
        }
        if ($sbx->view_id() <> $this->view_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_VIEW,
                    $cng_fld_cac->id($table_id . self::FLD_VIEW),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                self::FLD_VIEW,
                view::FLD_NAME,
                $this->view,
                $sbx->view
            );
        }
        if (!$sc_par_lst->is_usr_tbl()) {
            if ($sbx->code_id() <> $this->code_id()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . sql::FLD_CODE_ID,
                        $cng_fld_cac->id($table_id . sql::FLD_CODE_ID),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                $lst->add_field(
                    sql::FLD_CODE_ID,
                    $this->code_id(),
                    sql::FLD_CODE_ID_SQL_TYP,
                    $sbx->code_id()
                );
            }
        }
        // TODO move to language forms
        if ($sbx->plural <> $this->plural) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_PLURAL,
                    $cng_fld_cac->id($table_id . self::FLD_PLURAL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_PLURAL,
                $this->plural,
                self::FLD_PLURAL_SQL_TYP,
                $sbx->plural
            );
        }
        // TODO rename to usage
        if ($sbx->values <> $this->values) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_VALUES,
                    $cng_fld_cac->id($table_id . self::FLD_VALUES),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_VALUES,
                $this->values,
                self::FLD_VALUES_SQL_TYP,
                $sbx->values
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
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
