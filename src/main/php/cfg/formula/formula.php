<?php

/*

    model/formula/formula.php - the main formula object
    -----------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this formula object
    - construct and map: including the mapping of the db row to this formula object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - word:              manage the related word
    - info:              functions to make code easier to read
    - assign:            to define when a formula should be used
    - result:            manage the formula results
    - calc:              manage the formula calculation
    - im- and export:    create an export object and set the vars from an import object
    - expression:        handel to single parts of a formula
    - link:              add or remove a link to a word (this is user specific, so use the user sandbox)
    - save:              to update the formula in the database and for the user sandbox
    - del:               manage to remove from the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database


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

namespace cfg\formula;

include_once SHARED_TYPES_PATH . 'protection_type.php';
include_once SHARED_TYPES_PATH . 'share_type.php';
include_once SERVICE_EXPORT_PATH . 'formula_exp.php';
include_once MODEL_RESULT_PATH . 'result_list.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_creator.php';
include_once MODEL_ELEMENT_PATH . 'element.php';
include_once MODEL_ELEMENT_PATH . 'element_list.php';
include_once MODEL_HELPER_PATH . 'data_object.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_PHRASE_PATH . 'phr_ids.php';
include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_list.php';
include_once MODEL_PHRASE_PATH . 'phrase_type.php';
include_once MODEL_PHRASE_PATH . 'term.php';
include_once MODEL_PHRASE_PATH . 'term_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_typed.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
include_once MODEL_SANDBOX_PATH . 'protection_type.php';
include_once MODEL_SANDBOX_PATH . 'share_type.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once MODEL_VIEW_PATH . 'view.php';
include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_RESULT_PATH . 'result.php';
include_once MODEL_RESULT_PATH . 'result_list.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_base.php';
include_once MODEL_FORMULA_PATH . 'formula_type.php';
include_once MODEL_FORMULA_PATH . 'formula_link.php';
include_once MODEL_FORMULA_PATH . 'formula_link_type.php';
include_once MODEL_FORMULA_PATH . 'expression.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once WEB_FORMULA_PATH . 'formula.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_CALC_PATH . 'parameter_type.php';
include_once SHARED_CONST_PATH . 'chars.php';
include_once SHARED_CONST_PATH . 'formulas.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\element\element;
use cfg\element\element_list;
use cfg\helper\data_object;
use cfg\log\change;
use cfg\phrase\phr_ids;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\phrase\term;
use cfg\phrase\term_list;
use cfg\result\result;
use cfg\result\result_list;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\sandbox\sandbox_typed;
use cfg\user\user;
use cfg\user\user_message;
use cfg\value\value;
use cfg\view\view;
use cfg\word\triple;
use cfg\word\word;
use DateTime;
use Exception;
use math;
use shared\calc\parameter_type;
use shared\const\chars;
use shared\const\formulas;
use shared\enum\messages as msg_id;
use shared\json_fields;
use shared\library;
use shared\types\api_type_list;
use shared\types\phrase_type as phrase_type_shared;

class formula extends sandbox_typed
{

    /*
     * db const
     */

    // comments used for the database creation
    const TBL_COMMENT = 'the mathematical expression to calculate results based on values and results';

    // object specific database and JSON object field names
    // means: database fields only used for formulas
    // table fields where the change should be encoded before shown to the user
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'formula_id';
    const FLD_ID_SQL_TYP = sql_field_type::INT;
    const FLD_NAME_COM = 'the text used to search for formulas that must also be unique for all terms (words, triples, verbs and formulas)';
    const FLD_NAME = 'formula_name';
    const FLD_TYPE_COM = 'the id of the formula type';
    const FLD_TYPE = 'formula_type_id';
    const FLD_TYPE_SQL_TYP = sql_field_type::INT;
    const FLD_FORMULA_TEXT_COM = 'the internal formula expression with the database references e.g. {f1} for formula with id 1';
    const FLD_FORMULA_TEXT = 'formula_text';
    const FLD_FORMULA_TEXT_SQL_TYP = sql_field_type::TEXT;
    const FLD_FORMULA_USER_TEXT_COM = 'the formula expression in user readable format as shown to the user which can include formatting for better readability';
    const FLD_FORMULA_USER_TEXT = 'resolved_text';
    const FLD_FORMULA_USER_TEXT_SQL_TYP = sql_field_type::TEXT;
    //const FLD_REF_TEXT = "ref_text";             // the formula field "ref_txt" is a more internal field, which should not be shown to the user (only to an admin for debugging)
    const FLD_DESCRIPTION_COM = 'text to be shown to the user for mouse over; to be replaced by a language form entry';
    const FLD_ALL_NEEDED_COM = 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"';
    const FLD_ALL_NEEDED = 'all_values_needed';
    const FLD_ALL_NEEDED_SQL_TYP = sql_field_type::INT_SMALL;
    const FLD_LAST_UPDATE_COM = 'time of the last calculation relevant update';
    const FLD_LAST_UPDATE = 'last_update';
    const FLD_LAST_UPDATE_SQL_TYP = sql_field_type::TIME;
    const FLD_VIEW_COM = 'the default mask for this formula';
    const FLD_VIEW = 'view_id';
    const FLD_VIEW_SQL_TYP = sql_field_type::INT;
    const FLD_USAGE_COM = 'number of results linked to this formula';
    const FLD_USAGE = 'usage'; // TODO convert to a percent value of relative importance e.g. is 100% if all results, words and triples use this formula; should be possible to adjust the weight of e.g. values and views with the user specific system settings
    const FLD_USAGE_SQL_TYP = sql_field_type::INT;

    // the field names used for the im- and export in the json or yaml format
    const FLD_EXPRESSION = 'expression';
    const FLD_ASSIGN = 'assigned_word';

    // list of fields that MUST be set by one user
    // TODO add foreign key for share and protection type?
    const FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::UNIQUE, '', self::FLD_NAME_COM],
        [self::FLD_FORMULA_TEXT, self::FLD_FORMULA_TEXT_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_FORMULA_TEXT_COM],
        [self::FLD_FORMULA_USER_TEXT, self::FLD_FORMULA_USER_TEXT_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_FORMULA_USER_TEXT_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_NAME, self::FLD_NAME_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
        [self::FLD_FORMULA_TEXT, self::FLD_FORMULA_TEXT_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_FORMULA_TEXT_COM],
        [self::FLD_FORMULA_USER_TEXT, self::FLD_FORMULA_USER_TEXT_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_FORMULA_USER_TEXT_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_TYPE, self::FLD_TYPE_SQL_TYP, sql_field_default::NULL, sql::INDEX, formula_type::class, self::FLD_TYPE_COM],
        [self::FLD_ALL_NEEDED, self::FLD_ALL_NEEDED_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_ALL_NEEDED_COM],
        [self::FLD_LAST_UPDATE, self::FLD_LAST_UPDATE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_LAST_UPDATE_COM],
        [self::FLD_VIEW, self::FLD_VIEW_SQL_TYP, sql_field_default::NULL, sql::INDEX, view::class, self::FLD_VIEW_COM],
        [self::FLD_USAGE, self::FLD_USAGE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_USAGE_COM],
    );

    // all database field names excluding the id
    // actually empty because all formula fields are user specific
    // TODO check if last_update must be user specific
    const FLD_NAMES = array();
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_FORMULA_TEXT,
        self::FLD_FORMULA_USER_TEXT,
        sandbox_named::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_ALL_NEEDED,
        self::FLD_LAST_UPDATE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        self::FLD_FORMULA_TEXT,
        self::FLD_FORMULA_USER_TEXT,
        sandbox_named::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_ALL_NEEDED,
        self::FLD_LAST_UPDATE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields
    public ?string $ref_text = null;       // the formula expression with the names replaced by database references
    private bool $ref_text_dirty;          // true if the human-readable text has been updated and not yet converted
    public ?string $usr_text = null;       // the formula expression in the user format
    private bool $usr_text_dirty;          // true if the reference text has been updated and not yet converted
    public ?string $description = null;    // describes to the user what this formula is doing
    public ?bool $need_all_val = null;     // calculate and save the result only if all used values are not null
    public ?DateTime $last_update = null;  // the time of the last update of fields that may influence the calculated results
    private ?view $view;                   // name of the default view for this formula
    private ?int $usage = null;            // indicator of the popularity for sorting selection boxes

    // in memory only fields
    public ?string $type_cl = '';          // the code id of the formula type
    public ?word $name_wrd = null;         // the triple object for the formula name:
    //                                        because values can only be assigned to phrases, also for the formula name a triple must exist
    public bool $needs_res_upd = false;     // true if the formula results needs to be updated
    public ?string $ref_text_r = '';       // the part of the formula expression that is right of the equation sign (used as a work-in-progress field for calculation)


    /*
     * construct and map
     */

    /**
     * define the settings for this formula object
     * @param user $usr the user who requested to see this formula
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
        $this->reset();

        $this->rename_can_switch = UI_CAN_CHANGE_FORMULA_NAME;
    }

    /**
     * clear the view component object values
     * @return void
     */
    function reset(): void
    {
        parent::reset();

        $this->ref_text = null;
        $this->ref_text_dirty = false;
        $this->usr_text = null;
        $this->usr_text_dirty = false;
        $this->type_id = null;
        $this->need_all_val = null;
        $this->last_update = null;
        $this->usage = null;

        $this->type_cl = '';
        $this->name_wrd = null;

        $this->needs_res_upd = false;
        $this->ref_text_r = '';

        $this->view = null;
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @param string $type_fld the name of the type field as defined in this child class
     * @return bool true if the formula is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = self::FLD_ID,
        string $name_fld = self::FLD_NAME,
        string $type_fld = self::FLD_TYPE): bool
    {
        global $frm_typ_cac;
        $lib = new library();
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld);
        if ($result) {
            if (array_key_exists($type_fld, $db_row)) {
                $this->type_id = $db_row[$type_fld];
            }
            if (array_key_exists(self::FLD_FORMULA_TEXT, $db_row)) {
                $this->ref_text = $db_row[self::FLD_FORMULA_TEXT];
            }
            if (array_key_exists(self::FLD_FORMULA_USER_TEXT, $db_row)) {
                $this->usr_text = $db_row[self::FLD_FORMULA_USER_TEXT];
            }
            if (array_key_exists(self::FLD_ALL_NEEDED, $db_row)) {
                $this->need_all_val = $lib->get_bool($db_row[self::FLD_ALL_NEEDED]);
            }
            if (array_key_exists(self::FLD_LAST_UPDATE, $db_row)) {
                $this->last_update = $lib->get_datetime($db_row[self::FLD_LAST_UPDATE], $this->dsp_id());
            }
            if (array_key_exists(self::FLD_VIEW, $db_row)) {
                if ($db_row[self::FLD_VIEW] != null) {
                    $this->set_view_id($db_row[self::FLD_VIEW]);
                }
            }
            if (array_key_exists(self::FLD_USAGE, $db_row)) {
                $this->set_usage($db_row[self::FLD_USAGE]);
            }

            if ($this->type_id > 0) {
                $this->type_cl = $frm_typ_cac->code_id($this->type_id);
            }
            /*
            if ($this->id() > 0) {
                // TODO check the exclusion handling
                log_debug('->load ' . $this->dsp_id() . ' not excluded');

                // load the formula name word object
                // a word (TODO triple)
                // with the same name as the formula is needed,
                // because values can only be assigned to a word
                if (is_null($this->name_wrd)) {
                    $result = $this->load_wrd();
                } else {
                    $result = true;
                }
            }
            */
        }
        return $result;
    }

    /**
     * map a formula api json to this model formula object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the word values that should be mapped
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function api_mapper(array $api_json): user_message
    {
        $msg = parent::api_mapper($api_json);

        foreach ($api_json as $key => $value) {
            if ($key == json_fields::USR_TEXT) {
                if ($value <> '') {
                    $this->set_user_text($value);
                }
            }
        }

        return $msg;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        if ($this->is_excluded() and !$typ_lst->test_mode()) {
            $vars = [];
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
        } else {
            $vars = parent::api_json_array($typ_lst, $usr);
            $vars[json_fields::USR_TEXT] = $this->usr_text;
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the formula
     * @param string $name mainly for test creation the name of the formula
     * @param string $type_code_id the code id of the predefined formula type
     */
    function set(int $id = 0, string $name = '', string $type_code_id = ''): void
    {
        parent::set($id, $name);

        if ($type_code_id != '') {
            $this->set_type($type_code_id);
        }
    }

    /**
     * set the predefined type of this formula
     *
     * @param string $type_code_id the code id that should be added to this formula
     * @return void
     */
    function set_type(string $type_code_id): void
    {
        global $frm_typ_cac;
        $this->type_id = $frm_typ_cac->id($type_code_id);
    }

    /**
     * set the value to rank the formulas by usage
     *
     * @param int $usage a higher value moves the formula to the top of the selection list
     * @return void
     */
    function set_usage(?int $usage): void
    {
        $this->usage = $usage;
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

    /**
     * @return int|null a higher number indicates a higher usage
     */
    function usage(): ?int
    {
        return $this->usage;
    }

    /**
     * update the expression by setting the human-readable format and try to update the database reference format
     * @param string $usr_txt the formula expression in the human-readable format
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return void
     */
    function set_user_text(string $usr_txt, ?term_list $trm_lst = null): void
    {
        $this->usr_text = $usr_txt;
        $this->usr_text_dirty = false;
        $this->ref_text_dirty = true;
        $this->generate_ref_text($trm_lst);
    }

    function usr_text(): string
    {
        if ($this->usr_text_dirty) {
            $this->generate_usr_text();
        }
        return $this->usr_text;
    }

    function ref_text(): string
    {
        if ($this->ref_text_dirty) {
            $this->generate_ref_text();
        }
        return $this->ref_text;
    }


    /*
     * preloaded
     */

    /**
     * get the name of the formula type
     * @return string the name of the formula type
     */
    function type_name(): string
    {
        global $frm_typ_cac;
        return $frm_typ_cac->name($this->type_id);
    }


    /*
     * load
     */

    /**
     * load the formula parameters for all users
     * @param sql_par|null $qp placeholder to align the function parameters with the parent
     * @return bool true if the standard formula has been loaded
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
     * create the SQL to load the default formula always by the id
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
     * create the common part of an SQL statement to retrieve
     * the parameters of a formula from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param string $class the name of this class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        // maybe the formula name should be excluded from the user sandbox to avoid confusion
        return parent::load_sql_usr_num($sc, $this, $query_name);
    }

    /**
     * load the corresponding name word for the formula name
     * @param bool $with_automatic_error_fixing to add any missing words automatically
     * @return bool true if the word has been loaded
     */
    function load_wrd(bool $with_automatic_error_fixing = true): bool
    {
        $result = true;

        $do_load = true;
        if (isset($this->name_wrd)) {
            if ($this->name_wrd->name == $this->name()) {
                $do_load = false;
            }
        }
        if ($do_load) {
            log_debug('->load_wrd load ' . $this->dsp_id());
            $name_wrd = new word($this->user());
            $name_wrd->load_by_name($this->name());
            if ($name_wrd->id() > 0) {
                $this->name_wrd = $name_wrd;
            } else {
                // if the loading of the corresponding triple fails,
                // try to recreate it and report the internal error
                // because this should actually never happen
                if ($with_automatic_error_fixing) {
                    if (!$this->wrd_add_fix()) {
                        log_err('The formula word recreation for ' . $this->dsp_id() . ' failed');
                        $result = false;
                    }
                } else {
                    $result = false;
                }

            }
        }
        return $result;
    }

    function name_field(): string
    {
        return self::FLD_NAME;
    }

    function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * word
     */

    /**
     * create the corresponding name word for the formula name
     * @return bool true if adding the word has been successful
     */
    function wrd_add(): bool
    {
        global $phr_typ_cac;

        log_debug('formula wrd_add for ' . $this->dsp_id());
        $result = false;

        // if the formula word is missing, try a word creating as a kind of auto recovery
        $name_wrd = new word($this->user());
        $name_wrd->set_name($this->name());
        $name_wrd->type_id = $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK);
        $name_wrd->save()->get_last_message();
        if ($name_wrd->id() > 0) {
            $this->name_wrd = $name_wrd;
            $result = true;
        } else {
            log_err('Word with the formula name "' . $this->name() . '" missing for id ' . $this->id() . '.', 'formula->create_wrd');
        }
        return $result;
    }

    /**
     * rename the corresponding name word if the formula is renamed
     * @return bool true if adding the word has been successful
     */
    function wrd_rename(string $old_name): bool
    {
        log_debug('formula wrd_rename for ' . $this->dsp_id() . ' from ' . $old_name);
        $result = false;

        $wrd = new word($this->user());
        $wrd->load_by_name($old_name);
        if (!$wrd->is_loaded()) {
            log_err('reloading of the word related to formula ' . $this->dsp_id() . ' failed');
        } else {
            if ($wrd->type_code_id() != phrase_type_shared::FORMULA_LINK) {
                log_err('reloading formula word ' . $wrd->dsp_id() . ' ist not of type ' . phrase_type_shared::FORMULA_LINK);
            } else {
                $wrd->set_name($this->name());
                $wrd->save()->get_last_message();
                $result = true;
            }
        }
        return $result;
    }

    /**
     * remove the corresponding name word if the formula is deleted
     * @return user_message true if adding the word has been successful
     */
    function wrd_del(): user_message
    {
        log_debug('formula wrd_del for ' . $this->dsp_id());
        $usr_msg = new user_message();

        $wrd = new word($this->user());
        $wrd->load_by_name($this->name());
        if (!$wrd->is_loaded()) {
            log_warning('reloading of the word related to formula ' . $this->dsp_id() . ' failed');
        } else {
            if ($wrd->type_code_id() != phrase_type_shared::FORMULA_LINK) {
                log_err('reloading formula word ' . $wrd->dsp_id() . ' ist not of type ' . phrase_type_shared::FORMULA_LINK);
            } else {
                $usr_msg = $wrd->del();
            }
        }
        return $usr_msg;
    }

    /**
     * add the corresponding name word for the formula name to the database without similar check
     * this should only be used to fix internal errors
     */
    function wrd_add_fix(): bool
    {
        global $phr_typ_cac;

        log_err('The formula word for ' . $this->dsp_id() . ' needs to be recreated to fix an internal error');
        $result = false;

        // if the formula word is missing, try a word creating as a kind of auto recovery
        $name_wrd = new word($this->user());
        $name_wrd->name = $this->name();
        $name_wrd->type_id = $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK);
        $name_wrd->add();
        if ($name_wrd->id() > 0) {
            //zu_info('Word with the formula name "'.$this->name().'" has been missing for id '.$this->id.'.','formula->calc');
            $this->name_wrd = $name_wrd;
            $result = true;
        } else {
            log_err('Word with the formula name "' . $this->name() . '" missing for id ' . $this->id() . '.', 'formula->create_wrd');
        }
        return $result;
    }


    /*
     * info
     */

    /**
     * returns ok message if this formula can be added to the database
     * e.g. a formula without expression should not be added to the database
     * @return user_message the explanation why the link cannot yet be added to the database
     */
    function db_ready(): user_message
    {
        $usr_msg = parent::db_ready();

        if ($this->ref_text == null and $this->usr_text == null) {
            $usr_msg->add_id_with_vars(msg_id::FORMULA_EXPRESSION_MISSING,
                [msg_id::VAR_FORMULA => $this->dsp_id()]);
        }
        return $usr_msg;
    }

    /**
     * @return bool true if the formula object probably has already been added to the database
     *              false e.g. if some parameters are missing
     */
    function is_valid(): bool
    {
        $result = parent::is_valid();
        if ($this->ref_text == null and $this->usr_text == null) {
            $result = false;
        }
        return $result;
    }

    /**
     * return the true if the formula has a special type and the result is a kind of hardcoded
     * e.g. "this" or "next" where the value of this or the following time word is returned
     */
    function is_special(): bool
    {
        $result = false;
        if ($this->type_cl <> "") {
            $result = true;
        }
        return $result;
    }

    /**
     * return the result of a special formula
     * e.g. "this" or "next" where the value of this or the following time word is returned
     */
    function special_result(phrase_list $phr_lst, ?phrase $time_phr = null): value
    {
        log_debug("formula->special_result (" . $this->id() . ",t" . $phr_lst->dsp_id() . ",time" . $time_phr->name() . " and user " . $this->user()->name . ")");
        $val = null;

        if ($this->type_id > 0) {
            log_debug("type (" . $this->type_cl . ")");
            if ($this->type_cl == formula_type::THIS) {
                $val_phr_lst = clone $phr_lst;
                $val_phr_lst->add($time_phr); // the time word should be added at the end, because ...
                log_debug("this (" . $time_phr->name() . ")");
                $val = $val_phr_lst->value_scaled();
            }
            if ($this->type_cl == formula_type::NEXT) {
                $val_phr_lst = clone $phr_lst;
                $next_wrd = $time_phr->next();
                if ($next_wrd->id() > 0) {
                    $val_phr_lst->add($next_wrd); // the time word should be added at the end, because ...
                    log_debug("next (" . $next_wrd->name() . ")");
                    $val = $val_phr_lst->value_scaled();
                }
            }
            if ($this->type_cl == formula_type::PREV) {
                $val_phr_lst = clone $phr_lst;
                $prior_wrd = $time_phr->prior();
                if ($prior_wrd->id() > 0) {
                    $val_phr_lst->add($prior_wrd->phrase()); // the time word should be added at the end, because ...
                    log_debug("prior (" . $prior_wrd->name() . ")");
                    $val = $val_phr_lst->value_scaled();
                }
            }
        }

        log_debug('result: ' . $val->number());
        return $val;
    }

    /**
     * return the time word id used for the special formula results
     * e.g. "this" or "next" where the value of this or the following time word is returned
     */
    function special_time_phr(phrase $time_phr): phrase
    {
        log_debug($this->type_cl . ' for ' . $time_phr->dsp_id());
        $result = $time_phr;

        if ($this->type_id > 0) {
            if ($time_phr->id() <= 0) {
                log_err('No time defined for ' . $time_phr->dsp_id() . '.', 'formula->special_time_phr');
            } else {
                if ($this->type_cl == formula_type::THIS) {
                    $result = $time_phr;
                }
                if ($this->type_cl == formula_type::NEXT) {
                    $this_wrd = $time_phr->main_word();
                    $next_wrd = $this_wrd->next();
                    $result = $next_wrd->phrase();
                }
                if ($this->type_cl == formula_type::PREV) {
                    $this_wrd = $time_phr->main_word();
                    $prior_wrd = $this_wrd->prior();
                    $result = $prior_wrd->phrase();
                }
            }
        }

        log_debug('got ' . $result->dsp_id());
        return $result;
    }

    /**
     * get all phrases included by a special formula element for a list of phrases
     * e.g. if the list of phrases is "2016" and "2017" and the special formulas are "prior" and "next" the result should be "2015", "2016","2017" and "2018"
     */
    function special_phr_lst(phrase_list $phr_lst): phrase_list
    {
        log_debug('for ' . $phr_lst->dsp_id());
        $result = clone $phr_lst;

        foreach ($phr_lst->lst() as $phr) {
            // temp solution utils the real reason is found why the phrase list elements are missing the user settings
            if (!isset($phr->usr)) {
                $phr->set_user($this->user());
            }
            // get all special phrases
            $time_phr = $this->special_time_phr($phr);
            if (isset($time_phr)) {
                $result->add($time_phr);
                log_debug('added time ' . $time_phr->dsp_id() . ' to ' . $result->dsp_id());
            }
        }

        log_debug($result->dsp_id());
        return $result;
    }


    /*
     * assign
     */

    /**
     * lists of all words directly assigned to a formula and where the formula should be used
     */
    function assign_phr_glst_direct($sbx): ?phrase_list
    {
        $phr_lst = null;
        $lib = new library();

        if ($this->id() > 0 and $this->user() != null) {
            log_debug('for formula ' . $this->dsp_id() . ' and user "' . $this->user()->name . '"');
            $frm_lnk_lst = new formula_link_list($this->user());
            $frm_lnk_lst->load_by_frm_id($this->id());
            $phr_ids = $frm_lnk_lst->phrase_ids($sbx);

            if (count($phr_ids->lst) > 0) {
                $phr_lst = new phrase_list($this->user());
                $phr_lst->load_names_by_ids($phr_ids);
                log_debug("number of words " . $lib->dsp_count($phr_lst->lst()));
            }
        } else {
            log_err("The user id must be set to list the formula links.", "formula->assign_phr_glst_direct");
        }

        return $phr_lst;
    }

    /**
     * the complete list of a phrases assigned to a formula
     */
    function assign_phr_lst_direct(): ?phrase_list
    {
        return $this->assign_phr_glst_direct(false);
    }

    /**
     * the user specific list of a phrases assigned to a formula
     */
    function assign_phr_ulst_direct(): ?phrase_list
    {
        return $this->assign_phr_glst_direct(true);
    }

    /**
     * returns a list of all words that the formula is assigned to
     * e.g. if the formula is assigned to "Company" and "ABB is a Company" include ABB in the word list
     */
    function assign_phr_glst($sbx): phrase_list
    {
        $phr_lst = new phrase_list($this->user());
        $lib = new library();

        if ($this->id() > 0 and $this->user() != null) {
            $direct_phr_lst = $this->assign_phr_glst_direct($sbx);
            if ($direct_phr_lst != null) {
                if (!$direct_phr_lst->is_empty()) {
                    log_debug($this->dsp_id() . ' direct assigned words and triples ' . $direct_phr_lst->dsp_id());

                    //$indirect_phr_lst = $direct_phr_lst->is();
                    $indirect_phr_lst = $direct_phr_lst->are();
                    log_debug('indirect assigned words and triples ' . $indirect_phr_lst->dsp_id());

                    // merge direct and indirect assigns (maybe later using phrase_list->merge)
                    $phr_ids = array_merge($direct_phr_lst->id_lst(), $indirect_phr_lst->id_lst());
                    $phr_ids = array_unique($phr_ids);

                    $phr_lst->load_by_ids((new phr_ids($phr_ids)));
                    log_debug('number of words and triples ' . $lib->dsp_count($phr_lst->lst()));
                } else {
                    log_debug('no words are assigned to ' . $this->dsp_id());
                }
            }
        } else {
            log_err('The id and user id must be set to list the formula links.', 'formula->assign_phr_glst');
        }

        return $phr_lst;
    }

    /**
     * the complete list of a phrases assigned to a formula
     */
    function assign_phr_lst(): phrase_list
    {
        return $this->assign_phr_glst(false);
    }

    /**
     * the user specific list of a phrases assigned to a formula
     */
    function assign_phr_ulst(): phrase_list
    {
        return $this->assign_phr_glst(true);
    }

    // TODO review
    public static function cmp($a, $b): string
    {
        return strcmp($a->name, $b->name);
    }


    /*
     * result
     */

    /**
     * delete all results for this formula
     * @return string an empty string if the deletion has been successful
     *                or the error message that should be shown to the user
     *                which may include a link for error tracing
     */
    function res_del(): string
    {
        log_debug("formula->res_del (" . $this->id() . ")");

        global $db_con;

        $db_con->set_class(result::class);
        $db_con->set_usr($this->user()->id());
        return $db_con->delete_old(self::FLD_ID, $this->id());
    }

    /**
     * create a result object for this formula
     *
     * @param phrase_list $phr_lst list of the phrases that describes the result
     * @return result with the value from this formula
     */
    private function create_result(phrase_list $phr_lst): result
    {
        $rst = new result($this->user());
        $rst->frm = $this;
        $rst->src_grp = $phr_lst->get_grp_id();
        $rst->ref_text = $this->ref_text_r;
        $rst->num_text = $this->ref_text_r;
        $rst->src_grp->set_phrase_list(clone $phr_lst);
        $rst->grp()->set_phrase_list(clone $phr_lst);
        if ($rst->last_val_update < $this->last_update) {
            $rst->last_val_update = $this->last_update;
        }
        return $rst;
    }


    /*
     * calc
     */

    /**
     * fill the formula in the reference format with numbers
     * TODO review by splitting it up
     *
     * @param phrase_list $phr_lst list of phrase used to select the value for the calculation
     * @param phrase_list|null $pre_phr_lst list of preloaded / cached terms
     * TODO verbs
     * @return result_list all results of the formula for the given phrase list
     */
    function to_num(phrase_list $phr_lst, ?phrase_list $pre_phr_lst = null): result_list
    {
        log_debug('get numbers for ' . $this->dsp_id() . ' and ' . $phr_lst->dsp_id());
        $lib = new library();

        // check
        $pre_trm_lst = $pre_phr_lst?->term_list();
        if ($this->ref_text_r == '' and $this->ref_text <> '') {
            $exp = new expression($this->user());
            $exp->set_ref_text($this->ref_text, $pre_trm_lst);
            $this->ref_text_r = chars::CHAR_CALC . $exp->r_part();
        }

        // create the result list
        $res_lst = new result_list($this->user());

        // create a master result object to only need to fill it with the numbers in the code below
        $res_init = $this->create_result($phr_lst); // maybe move the constructor of result_list?

        // load the formula element groups; similar parts is used in the explain method in result
        // e.g. for "sales differentiator sector / Total sales" the element groups are
        //      "sales differentiator sector" and "Total sales" where
        //      the element group "sales differentiator sector" has the elements: "sales" (of type word), "differentiator" (verb), "sector" (word)
        $exp = $this->expression($pre_trm_lst);
        $elm_grp_lst = $exp->element_grp_lst($pre_trm_lst);
        log_debug('in ' . $exp->ref_text() . ' ' . $lib->dsp_count($elm_grp_lst->lst()) . ' element groups found');

        // to check if all needed value are given
        $all_elm_grp_filled = true;

        // loop over the element groups and replace the symbol with a number
        foreach ($elm_grp_lst->lst() as $elm_grp) {

            // get the figures based on the context e.g. the formula element "Share Price" for the context "ABB" can be 23.11
            // a figure is either the user edited value or a calculated formula result
            $elm_grp->phr_lst = clone $phr_lst;
            $elm_grp->build_symbol();
            $fig_lst = $elm_grp->figures($pre_trm_lst);
            log_debug('figures ');
            log_debug('figures ' . $fig_lst->dsp_id() . ' (' . $lib->dsp_count($fig_lst->lst()) . ') for ' . $elm_grp->dsp_id());

            // fill the figure into the formula text and create as much value and results as needed
            if ($fig_lst->lst() != null) {
                if (count($fig_lst->lst()) == 1) {
                    // if no figure is found use the master result as placeholder
                    if ($res_lst->lst() != null) {
                        if (count($res_lst->lst()) == 0) {
                            $res_lst->add_obj($res_init);
                        }
                    } else {
                        $res_lst->add_obj($res_init);
                    }
                    // fill each results created by any previous number filling
                    foreach ($res_lst->lst() as $res) {
                        // fill each results created by any previous number filling
                        if ($res->val_missing == False) {
                            if ($fig_lst->fig_missing and $this->need_all_val) {
                                log_debug('figure missing');
                                $res->val_missing = True;
                            } else {
                                $fig = $fig_lst->lst()[0];
                                $res->num_text = str_replace($fig->symbol(), $fig->number(), $res->num_text);
                                if ($res->last_val_update < $fig->last_update()) {
                                    $res->last_val_update = $fig->last_update();
                                }
                                log_debug('one figure "' . $fig->number() . '" for "' . $fig->symbol() . '" in "' . $res->num_text . '"');
                            }
                        }
                    }
                } elseif (count($fig_lst->lst()) > 1) {
                    // create the formula result object only if at least one figure if found
                    if (count($res_lst->lst()) == 0) {
                        $res_lst->add_obj($res_init);
                    }
                    // if there is more than one number to fill replicate each previous result, so in fact it multiplies the number of results
                    foreach ($res_lst->lst() as $res) {
                        $res_master = clone $res;
                        $fig_nbr = 1;
                        foreach ($fig_lst->lst() as $fig) {
                            if ($res->val_missing == False) {
                                if ($fig_lst->fig_missing and $this->need_all_val) {
                                    log_debug('figure missing');
                                    $res->val_missing = True;
                                } else {
                                    // for the first previous result, just fill in the first number
                                    if ($fig_nbr == 1) {

                                        // if the result has been the standard result utils now
                                        if ($res->is_std()) {
                                            // ... and the value is user specific
                                            if (!$fig->is_std()) {
                                                // split the result into a standard
                                                // get the standard value
                                                // $fig_std = ...;
                                                $res_std = clone $res;
                                                $res_std->num_text = str_replace($fig->symbol(), $fig->number(), $res_std->num_text);
                                                if ($res_std->last_val_update < $fig->last_update()) {
                                                    $res_std->last_val_update = $fig->last_update();
                                                }
                                                log_debug('one figure "' . $fig->number() . '" for "' . $fig->symbol() . '" in "' . $res->num_text . '"');
                                                $res_lst->add_obj($res_std);
                                                // ... and split into a user specific part
                                                $res->is_std = false;
                                            }
                                        }

                                        $res->num_text = str_replace($fig->symbol(), $fig->number(), $res->num_text);
                                        if ($res->last_val_update < $fig->last_update()) {
                                            $res->last_val_update = $fig->last_update();
                                        }
                                        log_debug('one figure "' . $fig->number() . '" for "' . $fig->symbol() . '" in "' . $res->num_text . '"');
                                    } else {
                                        // if the result has been the standard result utils now
                                        if ($res_master->is_std()) {
                                            // ... and the value is user specific
                                            if (!$fig->is_std()) {
                                                // split the result into a standard
                                                // get the standard value
                                                // $fig_std = ...;
                                                $res_std = clone $res_master;
                                                $res_std->num_text = str_replace($fig->symbol(), $fig->number(), $res_std->num_text);
                                                if ($res_std->last_val_update < $fig->last_update()) {
                                                    $res_std->last_val_update = $fig->last_update();
                                                }
                                                log_debug('one figure "' . $fig->number() . '" for "' . $fig->symbol() . '" in "' . $res->num_text . '"');
                                                $res_lst->add_obj($res_std);
                                                // ... and split into a user specific part
                                                $res_master->is_std = false;
                                            }
                                        }

                                        // for all following result reuse the first result and fill with the next number
                                        $res_new = clone $res_master;
                                        $res_new->num_text = str_replace($fig->symbol(), $fig->number(), $res_new->num_text);
                                        if ($res->last_val_update < $fig->last_update()) {
                                            $res->last_val_update = $fig->last_update();
                                        }
                                        log_debug('one figure "' . $fig->number() . '" for "' . $fig->symbol() . '" in "' . $res->num_text . '"');
                                        $res_lst->add_obj($res_new);
                                    }
                                    log_debug('figure "' . $fig->number() . '" for "' . $fig->symbol() . '" in "' . $res->num_text . '"');
                                    $fig_nbr++;
                                }
                            }
                        }
                    }
                } else {
                    // if not figure found remember to switch off the result if needed
                    log_debug('no figures found for ' . $elm_grp->dsp_id() . ' and ' . $phr_lst->dsp_id());
                    $all_elm_grp_filled = false;
                }
            }
        }

        // if some values are not filled and all are needed, switch off the incomplete formula results
        if ($this->need_all_val) {
            log_debug('for ' . $phr_lst->dsp_id() . ' all value are needed');
            if ($all_elm_grp_filled) {
                log_debug('for ' . $phr_lst->dsp_id() . ' all value are filled');
            } else {
                log_debug('some needed values missing for ' . $phr_lst->dsp_id());
                foreach ($res_lst->lst() as $res) {
                    log_debug('some needed values missing for ' . $res->dsp_id() . ' so switch off');
                    $res->val_missing = True;
                }
            }
        }

        // calculate the final numeric results
        $lib = new library();
        if ($res_lst->lst() != null) {
            foreach ($res_lst->lst() as $res) {
                // at least the formula update should be used
                if ($res->last_val_update < $this->last_update) {
                    $res->last_val_update = $this->last_update;
                }
                // calculate only if any parameter has been updated since last calculation
                if ($res->num_text == '') {
                    log_err('num text is empty nothing needs to be done, but actually this should never happen');
                } else {
                    if ($res->last_val_update > $res->last_update) {
                        // check if all needed value exist
                        $can_calc = false;
                        if ($this->need_all_val) {
                            log_debug('calculate ' . $this->dsp_id() . ' only if all numbers are given');
                            if ($res->val_missing) {
                                log_debug('got some numbers for ' . $this->dsp_id() . ' and ' . $lib->dsp_array($res->phr_ids()));
                            } else {
                                if ($res->is_std) {
                                    log_debug('got all numbers for ' . $this->dsp_id() . ' and ' . $res->name_linked() . ': ' . $res->num_text);
                                } else {
                                    log_debug('got all numbers for ' . $this->dsp_id() . ' and ' . $res->name_linked() . ': ' . $res->num_text . ' (user specific)');
                                }
                                $can_calc = true;
                            }
                        } else {
                            log_debug('always calculate ' . $this->dsp_id());
                            $can_calc = true;
                        }
                        if ($can_calc == true) {
                            log_debug('calculate ' . $res->num_text . ' for ' . $phr_lst->dsp_id());
                            $calc = new math;
                            $res->set_number($calc->parse($res->num_text));
                            $res->is_updated = true;
                            log_debug('the calculated ' . $this->dsp_id() . ' is ' . $res->number() . ' for ' . $res->grp()->phrase_list()->dsp_id());
                        }
                    }
                }
            }
        }

        return $res_lst;
    }

    // create the calculation request for one formula and one usr
    /*
    function calc_requests($phr_lst) {
    $result = array();

    $calc_request = New job;
    $calc_request->frm     = $this;
    $calc_request->usr     = $this->user();
    $calc_request->phr_lst = $phr_lst;
    $result[] = $calc_request;
    zu_debug('request "'.$frm->name().'" for "'.$phr_lst->name().'"');

    return $result;
    }
    */


    /**
     * calculate the result for one formula for one user
     * and save the result in the database
     * @param phrase_list $phr_lst is the context for the value retrieval and it also contains any time words
     * the time words are only separated right before saving to the database
     * always returns an array of results
     * TODO check if calculation is really needed
     *      if one of the result words is a scaling word, remove all value scaling words
     *      always create a default result (for the user 0)
     */
    function calc(phrase_list $phr_lst): ?array
    {
        $result = null;
        $lib = new library();

        // check the parameters
        if (!isset($phr_lst)) {
            log_warning('The calculation context for ' . $this->dsp_id() . ' is empty.', 'formula->calc');
        } else {
            log_debug('->calc ' . $this->dsp_id() . ' for ' . $phr_lst->dsp_id());

            // check if an update of the result is needed
            /*
      $needs_update = true;
      if ($this->has_verb ($this->ref_text, $this->user()->id)) {
        $needs_update = true; // this case will be checked later
      } else {
        $frm_wrd_ids = $this->wrd_ids($this->ref_text, $this->user()->id());
      } */

            // reload the formula if needed, but this should be done by the calling function, so create an info message
            if ($this->name() == '' or is_null($this->name_wrd)) {
                if ($this->id() > 0) {
                    $this->load_by_id($this->id());
                    log_info('formula ' . $this->dsp_id() . ' reloaded.', 'formula->calc');
                } else {
                    log_warning('formula ' . $this->dsp_id() . ' cannot be reloaded');
                }
            }

            // build the formula expression for calculating the result
            $exp = new expression($this->user());
            $exp->set_ref_text($this->ref_text);

            // the phrase left of the equation sign should be added to the result
            // e.g. percent for the increase formula
            $has_result_phrases = false;
            $res_add_phr_lst = $exp->result_phrases();
            if (isset($res_add_phr_lst)) {
                log_debug('use words ' . $res_add_phr_lst->dsp_id() . ' for the result');
                $has_result_phrases = true;
            }
            // use only the part right of the equation sign for the result calculation
            $this->ref_text_r = chars::CHAR_CALC . $exp->r_part();
            log_debug('->calc got result words of ' . $this->ref_text_r);

            // get the list of the numeric results
            // $res_lst is a list of all results saved in the database
            $res_lst = $this->to_num($phr_lst);
            if (isset($res_add_phr_lst)) {
                log_debug($lib->dsp_count($res_lst->lst()) . ' formula results to save');
            }

            // save the numeric results
            if ($res_lst->lst() != null) {
                foreach ($res_lst->lst() as $res) {
                    if ($res->val_missing) {
                        // check if res needs to be removed from the database
                        log_debug('some values missing for ' . $res->dsp_id());
                    } else {
                        if ($res->is_updated) {
                            log_debug('formula result ' . $res->dsp_id() . ' is updated');

                            // make common assumptions on the word list

                            // apply general rules to the result words
                            if (isset($res_add_phr_lst)) {

                                // add the phrases left of the equal sign to the result e.g. percent for the increase formula
                                log_debug('result words "' . $res_add_phr_lst->dsp_id() . '" defined for ' . $res->grp()->dsp_id());
                                $res_add_wrd_lst = $res_add_phr_lst->wrd_lst_all();

                                // if the result words contains "percent" remove any measure word from the list, because a relative value is expected without measure
                                if ($res_add_wrd_lst->has_percent()) {
                                    log_debug('has percent');
                                    $res->grp()->phrase_list()->ex_measure();
                                    log_debug('measure words removed from ' . $res->grp()->phrase_list()->dsp_id());
                                }

                                // if in the formula is defined, that the result is in percent
                                // and the values used are in millions, the result is only in percent, but not in millions
                                // TODO check that all value have the same scaling and adjust the scaling if needed
                                if ($res_add_wrd_lst->has_percent()) {
                                    $res->grp()->phrase_list()->ex_scaling();
                                    log_debug('scaling words removed from ' . $res->grp()->phrase_list()->dsp_id());
                                    // maybe add the scaling word to the result words to remember based on which words the result has been created,
                                    // but probably this is not needed, because the source words are also saved
                                    //$scale_wrd_lst = $res_add_wrd_lst->scaling_lst ();
                                    //$res->grp()->phrase_list()->merge($scale_wrd_lst->lst);
                                    //zu_debug(self::class . '->calc -> added the scaling word "'.implode(",",$scale_wrd_lst->names()).'" to the result words "'.implode(",",$res->grp()->phrase_list()->names()).'"');
                                }

                                // if the formula is a scaling formula, remove the obsolete scaling word from the source words
                                if ($res_add_wrd_lst->has_scaling()) {
                                    $res->grp()->phrase_list()->ex_scaling();
                                    log_debug('scaling words removed from ' . $res->grp()->phrase_list()->dsp_id());
                                }

                            }

                            // add the formula result word
                            // e.g. in the increase formula "percent" should be on the left side of the equation because the result is supposed to be in percent
                            if (isset($res_add_phr_lst)) {
                                log_debug('add words ' . $res_add_phr_lst->dsp_id() . ' to the result');
                                foreach ($res_add_phr_lst->lst() as $frm_result_wrd) {
                                    $res->grp()->phrase_list()->add($frm_result_wrd);
                                }
                                log_debug('added words ' . $res_add_phr_lst->dsp_id() . ' to the result ' . $res->grp()->phrase_list()->dsp_id());
                            }

                            // add the formula name also to the result phrase e.g. increase
                            if (is_null($this->name_wrd)) {
                                $this->load_wrd();
                            }
                            if (is_null($this->name_wrd)) {
                                log_warning('Cannot load word for formula ' . $this->dsp_id());
                            } else {
                                $res->grp()->phrase_list()->add($this->name_wrd->phrase());
                            }

                            $res = $res->save_if_updated($has_result_phrases);

                        }
                    }
                }
            }


            $result = $res_lst->lst();
        }

        log_debug('done');
        return $result;
    }

    /**
     * calculate the formula results based on a given figure list
     *
     * @param figure_list $fig_lst the value and results that should be used for the calculation
     * @return figure_list the received figure list with the additions formula results
     */
    function calc_with(figure_list $fig_lst): figure_list
    {
        return $fig_lst;
    }

    /**
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return expression the formula expression as an expression element
     */
    function expression(?term_list $trm_lst = null): expression
    {
        $exp = new expression($this->user());
        $exp->set_ref_text($this->ref_text, $trm_lst);
        $exp->set_user_text($this->usr_text, $trm_lst);
        log_debug('->expression ' . $exp->ref_text() . ' for user ' . $exp->usr->name);
        return $exp;
    }

    /**
     * @return result_list a list of all formula results linked to this formula
     */
    function get_res_lst(): result_list
    {
        $res_lst = new result_list($this->user());
        $res_lst->load_by_frm($this);
        return $res_lst;
    }


    /*
     * cast
     */

    /**
     * @returns term the formula object cast into a term object
     */
    function term(): term
    {
        $trm = new term($this->user());
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * im- and export
     */

    /**
     * set the vars of this formula object based on the given json without writing to the database
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message
     */
    function import_mapper(array $in_ex_json, data_object $dto = null, object $test_obj = null): user_message
    {
        global $frm_typ_cac;

        // reset the all parameters for the formula object but keep the user
        $usr = $this->user();
        $this->reset();
        $this->set_user($usr);
        $usr_msg = parent::import_mapper($in_ex_json, $dto, $test_obj);

        if (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            $this->type_id = $frm_typ_cac->id($in_ex_json[json_fields::TYPE_NAME]);
        }
        if (key_exists(json_fields::EXPRESSION, $in_ex_json)) {
            if ($in_ex_json[json_fields::EXPRESSION] <> '') {
                $this->usr_text = $in_ex_json[json_fields::EXPRESSION];
            }
        }

        // set the default type if no type is specified
        if ($this->type_id == 0) {
            $this->type_id = $frm_typ_cac->default_id();
        }

        return $usr_msg;
    }

    /**
     * import a formula from a JSON object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $in_ex_json, object $test_obj = null): user_message
    {
        log_debug();

        // set the object vars based on the json
        $usr_msg = $this->import_mapper($in_ex_json, null, $test_obj);

        // save the formula in the database
        if (!$test_obj) {
            if ($usr_msg->is_ok()) {
                $usr_msg->add($this->save());
            }
        }

        // assign the formula to the words and triple
        if ($usr_msg->is_ok()) {
            log_debug('saved ' . $this->dsp_id());
            foreach ($in_ex_json as $key => $value) {
                if ($usr_msg->is_ok()) {
                    if ($key == self::FLD_ASSIGN) {
                        if (is_array($value)) {
                            foreach ($value as $lnk_phr_name) {
                                $usr_msg->add_message_text($this->assign_name($lnk_phr_name, $test_obj));
                            }
                        } else {
                            $usr_msg->add_message_text($this->assign_name($value, $test_obj));
                        }
                    }
                }
            }
        }

        return $usr_msg;
    }

    private function assign_name(string $phr_name, object $test_obj = null): string
    {
        $result = '';
        $phr = new phrase($this->user());
        if (!$test_obj) {
            $phr->load_by_name($phr_name);
            $result .= $this->assign_phrase($phr);
        }
        return $result;
    }

    function assign_phrase(phrase $phr): string
    {
        $result = '';
        if ($this->id() > 0 and $phr->id() <> 0) {
            $frm_lnk = new formula_link($this->user());
            $frm_lnk->load_by_link($this, $phr);
            if ($frm_lnk->id() == 0) {
                $frm_lnk->set_formula($this);
                $frm_lnk->set_phrase($phr);
                $result .= $frm_lnk->save()->get_last_message();
            }
        }
        return $result;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load true if the result should be validated again before export
     * *                    use false for a faster export and unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $vars = parent::export_json($do_load);

        global $frm_typ_cac;

        // TODO avoid the var overwrite be overwriting the type_name() function
        if (isset($this->type_id)) {
            if ($this->type_id <> $frm_typ_cac->default_id()) {
                $vars[json_fields::TYPE_NAME] = $frm_typ_cac->code_id($this->type_id);
            }
        }
        if ($this->usr_text <> '') {
            $vars[json_fields::EXPRESSION] = $this->usr_text;
        }

        if ($do_load) {
            $exp_lst = [];
            $phr_lst = $this->assign_phr_lst_direct();
            if ($phr_lst != null) {
                foreach ($phr_lst->lst() as $phr) {
                    $exp_lst[] = $phr->export_json();
                }
                $vars[json_fields::ASSIGNED_WORD] = $exp_lst;
            }
        }

        return $vars;
    }


    /*
     * expression
     * TODO probably to be replaced with expression functions
     */

    /**
     * @param string $formula the formula expression in the reference format
     * @param string $start_maker
     * @param string $end_maker
     * @return int a positive term object (e.g. word, triple, verb or formula) id
     *             if the formula string in the database format contains a link
     */
    private function get_term_id(string $formula, string $start_maker, string $end_maker): int
    {
        $lib = new library();

        $result = 0;
        $pos_start = strpos($formula, $start_maker);
        if ($pos_start !== false) {
            $r_part = $lib->str_right_of($formula, $start_maker);
            $l_part = $lib->str_left_of($r_part, $end_maker);
            if (is_numeric($l_part)) {
                $result = $l_part;
                log_debug($result);
            }
        }

        return $result;
    }

    /**
     * get all terms used in this formula
     * including the phrases that should be added to the result
     * @param term_list $cache with the terms already loaded
     * @return term_list list of all terms used in the formula expression
     */
    function term_list(term_list $cache): term_list
    {
        $trm_lst = new term_list($this->user());
        $exp = $this->expression($cache);
        $elm_lst = $exp->element_list($cache);
        foreach ($elm_lst->lst() as $elm) {
            $trm_lst->add($elm->term());
        }
        $res_phr_lst = $exp->result_phrases($cache);
        return $trm_lst->merge($res_phr_lst->term_list());
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @param string $start_maker
     * @param string $end_maker
     * @return array with one type of term ids from a given formula text
     */
    private function trm_ids(string $frm_text, string $start_maker, string $end_maker): array
    {
        $result = array();

        $lib = new library();

        // add term id to selection
        $new_trm_id = $this->get_term_id($frm_text, $start_maker, $end_maker);
        while ($new_trm_id > 0) {
            if (!in_array($new_trm_id, $result)) {
                $result[] = $new_trm_id;
            }
            $frm_text = $lib->str_right_of($frm_text, $start_maker . $new_trm_id . $end_maker);
            $new_trm_id = $this->get_term_id($frm_text, $start_maker, $end_maker);
        }

        log_debug($lib->dsp_array($result));
        return $result;
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @return array with the word ids from a given formula text
     */
    function wrd_ids(string $frm_text): array
    {
        return $this->trm_ids($frm_text, chars::WORD_START, chars::WORD_END);
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @return array with the word ids from a given formula text
     */
    function trp_ids(string $frm_text): array
    {
        return $this->trm_ids($frm_text, chars::TRIPLE_START, chars::TRIPLE_END);
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @return array with the word ids from a given formula text
     */
    function vrb_ids(string $frm_text): array
    {
        return $this->trm_ids($frm_text, chars::VERB_START, chars::VERB_END);
    }

    /**
     * @param string $frm_text the formula expression in the reference format
     * @return array with the formula ids from a given formula text
     */
    function frm_ids(string $frm_text): array
    {
        return $this->trm_ids($frm_text, chars::FORMULA_START, chars::FORMULA_END);
    }

    /**
     * update formula links
     * part of element_refresh for one element type and one user
     * TODO move this to the formula element list object
     */
    function element_refresh_type(string $frm_text, $element_type, $frm_usr_id, $db_usr_id): bool
    {
        log_debug('->element_refresh_type (f' . $this->id() . $frm_text . ',' . $element_type . ',u' . $frm_usr_id . ')');

        global $db_con;
        $result = true;

        // read the elements from the formula text
        $elm_type_id = $element_type;
        switch ($element_type) {
            case parameter_type::TRIPLE_ID:
                $elm_ids = $this->trp_ids($frm_text);
                break;
            case parameter_type::VERB_ID:
                $elm_ids = $this->vrb_ids($frm_text);
                break;
            case parameter_type::FORMULA_ID:
                $elm_ids = $this->frm_ids($frm_text);
                break;
            default:
                $elm_ids = $this->wrd_ids($frm_text);
                break;
        }
        $lib = new library();
        log_debug('got (' . $lib->dsp_array($elm_ids) . ') of type ' . $element_type . ' from text');

        // read the existing elements from the database
        $frm_elm_lst = new element_list($this->user());
        $qp = $frm_elm_lst->load_sql_by_frm_and_type_id($db_con->sql_creator(), $this->id(), $elm_type_id);
        $db_lst = $db_con->get($qp);

        $elm_db_ids = array();
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                $elm_db_ids[] = $db_row['ref_id'];
            }
        }
        $lib = new library();
        log_debug('got (' . $lib->dsp_array($elm_db_ids) . ') of type ' . $element_type . ' from database');

        // add missing links
        $elm_add_ids = array_diff($elm_ids, $elm_db_ids);
        $elm_order_nbr = 1;
        $lib = new library();
        log_debug('add ' . $element_type . ' (' . $lib->dsp_array($elm_add_ids) . ')');
        // TODO use element list object
        foreach ($elm_add_ids as $elm_add_id) {
            $field_names = array();
            $field_values = array();
            $field_names[] = self::FLD_ID;
            $field_values[] = $this->id();
            $field_names[] = user::FLD_ID;
            if ($frm_usr_id > 0) {
                $field_values[] = $frm_usr_id;
            } else {
                $field_values[] = $this->user()->id();
            }
            $field_names[] = element::FLD_TYPE;
            $field_values[] = $elm_type_id;
            $field_names[] = element::FLD_REF_ID;
            $field_values[] = $elm_add_id;
            $field_names[] = element::FLD_ORDER;
            $field_values[] = $elm_order_nbr;
            $db_con->set_class(element::class);
            $add_result = $db_con->insert_old($field_names, $field_values);
            // in this case the row id is not needed, but for testing the number of action should be indicated by adding a '1' to the result string
            //if ($add_result > 0) {
            //    $result .= '1';
            //}
            $elm_order_nbr++;
        }

        // delete links not needed any more
        $elm_del_ids = array_diff($elm_db_ids, $elm_ids);
        $lib = new library();
        log_debug('del ' . $element_type . ' (' . $lib->dsp_array($elm_del_ids) . ')');
        foreach ($elm_del_ids as $elm_del_id) {
            $field_names = array();
            $field_values = array();
            $field_names[] = self::FLD_ID;
            $field_values[] = $this->id();
            if ($frm_usr_id > 0) {
                $field_names[] = user::FLD_ID;
                $field_values[] = $frm_usr_id;
            }
            $field_names[] = element::FLD_TYPE;
            $field_values[] = $elm_type_id;
            $field_names[] = element::FLD_REF_ID;
            $field_values[] = $elm_del_id;
            $db_con->set_class(element::class);
            $del_result = $db_con->delete_old($field_names, $field_values);
            if ($del_result != '') {
                $result = false;
            }
        }

        log_debug(zu_dsp_bool($result));
        return $result;
    }

    /**
     * update the database references to the formula elements
     * to be able to use the sql statements to find all formulas depending on a word. triple, verb or formula
     * TODO create one SQL statement for the update that is executed with one commit statement
     * @param string $frm_text the reference text that should be used for the update
     * @return bool true if the update has been fine
     */
    function element_refresh(string $frm_text): bool
    {
        log_debug('->element_refresh (f' . $this->id() . $frm_text . ',u' . $this->user()->id() . ')');

        global $db_con;
        $result = true;

        // refresh the links for the standard formula used if the user has not changed the formula
        $result = $this->element_refresh_type($frm_text, parameter_type::WORD_ID, 0, $this->user()->id());

        // update triple links of the standard formula
        if ($result) {
            $result = $this->element_refresh_type($frm_text, parameter_type::TRIPLE_ID, 0, $this->user()->id());
        }

        // update verb links of the standard formula
        if ($result) {
            $result = $this->element_refresh_type($frm_text, parameter_type::VERB_ID, 0, $this->user()->id());
        }

        // update formula links of the standard formula
        if ($result) {
            $result = $this->element_refresh_type($frm_text, parameter_type::FORMULA_ID, 0, $this->user()->id());
        }

        // refresh the links for the user specific formula
        $qp = $this->load_sql_user_changes_frm($db_con);
        $db_lst = $db_con->get($qp);
        if ($db_lst != null) {
            foreach ($db_lst as $db_row) {
                // update word links of the user formula
                if ($result) {
                    $result = $this->element_refresh_type($frm_text, parameter_type::WORD_ID, $db_row[user::FLD_ID], $this->user()->id());
                }
                // update triple links of the user formula
                if ($result) {
                    $result = $this->element_refresh_type($frm_text, parameter_type::TRIPLE_ID, $db_row[user::FLD_ID], $this->user()->id());
                }
                // update verb links of the user formula
                if ($result) {
                    $result = $this->element_refresh_type($frm_text, parameter_type::VERB_ID, $db_row[user::FLD_ID], $this->user()->id());
                }
                // update formula links of the standard formula
                if ($result) {
                    $result = $this->element_refresh_type($frm_text, parameter_type::FORMULA_ID, $db_row[user::FLD_ID], $this->user()->id());
                }
            }
        }

        log_debug('done' . $result);
        return $result;
    }


    /*
     * link
     */

    /**
     * link this formula to a word or triple
     */
    function link_phr(phrase $phr): string
    {
        $result = '';
        if ($this->user() != null) {
            log_debug($this->dsp_id() . ' to ' . $phr->dsp_id());
            $frm_lnk = new formula_link($this->user());
            $frm_lnk->set_formula($this);
            $frm_lnk->set_phrase($phr);
            $result = $frm_lnk->save()->get_last_message();
        }
        return $result;
    }

    /**
     * unlink this formula from a word or triple
     */
    function unlink_phr($phr): string
    {
        $result = '';
        if (isset($phr) and $this->user() != null) {
            log_debug($this->dsp_id() . ' from ' . $phr->dsp_id() . ' for user ' . $this->user()->dsp_id());
            $frm_lnk = new formula_link($this->user());
            $frm_lnk->load_by_link($this, $phr);
            $msg = $frm_lnk->del();
            $result = $msg->get_message();
        } else {
            $result .= log_err("Cannot unlink formula, phrase is not set.", "formula.php");
        }
        return $result;
    }


    /*
     * save
     */

    /**
     * update the database reference text based on the user text
     * TODO check in not the left AND the right part needs to be transformed as expression
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return string which is empty if the update of the reference text was successful and otherwise the error message that should be shown to the user
     */
    function generate_ref_text(?term_list $trm_lst = null): string
    {
        $result = '';
        if ($this->usr_text != null) {
            $exp = new expression($this->user());
            $exp->set_user_text($this->usr_text, $trm_lst);
            $this->ref_text = $exp->ref_text($trm_lst);
            $this->ref_text_dirty = false;
            $result .= $exp->err_text;
        }
        return $result;
    }

    /**
     * update the user text based on the database reference text
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return string which is empty if the update of the reference text was successful and otherwise the error message that should be shown to the user
     */
    function generate_usr_text(?term_list $trm_lst = null): string
    {
        $result = '';
        $exp = new expression($this->user());
        $exp->set_user_text($this->usr_text);
        $this->ref_text = $exp->ref_text($trm_lst);
        $this->ref_text_dirty = false;
        $result .= $exp->err_text;
        return $result;
    }

    /**
     * @return bool true if the formula or formula assignment has not been overwritten by the user
     */
    function is_std(): bool
    {
        if ($this->has_usr_cfg()) {
            return false;
        } else {
            // TODO check the formula assigment
            return true;
        }
    }

    function is_used(): bool
    {
        return !$this->not_used();
    }

    function not_used(): bool
    {
        /*    $change_user_id = 0;
        $sql = "SELECT user_id
                  FROM user_formulas
                 WHERE formula_id = ".$this->id."
                   AND user_id <> ".$this->owner_id."
                   AND (excluded <> 1 OR excluded is NULL)";
        //$db_con = new mysql;
        $db_con->usr_id = $this->user()->id();
        $change_user_id = $db_con->get1($sql);
        if ($change_user_id > 0) {
          $result = false;
        } */
        return $this->not_changed();
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     *                 to check if the formula has been changed
     */
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $lib = new library();
        $sc->set_class($lib->class_to_name(self::class));
        return $sc->load_sql_not_changed($this->id(), $this->owner_id());
    }

    /**
     * true if no other user has modified the formula
     * assuming that in this case not confirmation from the other users for a formula rename is needed
     */
    function not_changed(): bool
    {
        log_debug('->not_changed (' . $this->id() . ')');

        global $db_con;
        $result = true;

        if ($this->id() == 0) {
            log_err('The id must be set to check if the formula has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con->sql_creator());
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                if ($db_row[user::FLD_ID] > 0) {
                    $result = false;
                }
            }
        }
        log_debug('->not_changed for ' . $this->id() . ' is ' . zu_dsp_bool($result));
        return $result;
    }

    /**
     * create an SQL statement to retrieve all user specific changes of this formula
     * TODO combine with load_sql_user_changes ?
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes_frm(sql_db $db_con, string $class = self::class): sql_par
    {
        $lib = new library();
        $class = $lib->class_to_name($class);
        $db_con->set_class(formula::class, true);
        $qp = new sql_par($class);
        $qp->name = $class . '_user_sandbox';
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(array_merge(array(user::FLD_ID), self::FLD_NAMES_USR, self::FLD_NAMES_NUM_USR));
        $db_con->add_par(sql_par_type::INT, strval($this->id()));
        $qp->sql = $db_con->select_by_field(self::FLD_ID);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current formula
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_user_changes(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        $sc->set_fields(array_merge(
            self::FLD_NAMES_USR,
            self::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }

    /**
     * overwrite of the user sandbox function to remove also the related elements
     * simply remove a formula user adjustment without check including the formula elements
     * log a system error if a technical error has occurred
     *
     *
     * @return bool true if user sandbox row has successfully been deleted
     */
    function del_usr_cfg_exe($db_con): bool
    {
        log_debug('->del_usr_cfg_exe ' . $this->dsp_id());

        $result = false;
        $action = 'Deletion of user formula ';
        $msg_failed = $this->id() . ' failed for ' . $this->user()->name;
        $msg = '';

        $db_con->set_class(element::class);
        try {
            $msg = $db_con->delete_old(
                array(self::FLD_ID, user::FLD_ID),
                array($this->id(), $this->user()->id()));
        } catch (Exception $e) {
            log_err($action . ' elements ' . $msg_failed . ' because ' . $e);
        }
        if ($msg != '') {
            log_err($action . ' elements ' . $msg_failed . ' because ' . $msg);
        } else {
            $db_con->set_class(formula::class, true);
            try {
                $msg = $db_con->delete_old(
                    array(self::FLD_ID, user::FLD_ID),
                    array($this->id(), $this->user()->id()));
                if ($msg == '') {
                    $this->usr_cfg_id = null;
                    $result = true;
                } else {
                    log_err($action . $msg_failed . ' because ' . $msg);
                }
            } catch (Exception $e) {
                log_err($action . $msg_failed . ' because ' . $e);
            }
        }

        return $result;
    }

    /**
     * update the time stamp to trigger an update of the depending on results
     */
    function save_field_trigger_update(sql_db $db_con): user_message
    {
        $usr_msg = new user_message();
        $this->last_update = new DateTime();
        $db_con->set_class(formula::class);
        if (!$db_con->update_old($this->id(), self::FLD_LAST_UPDATE, sql::NOW)) {
            $usr_msg->add_id_with_vars(msg_id::FAILED_SAVE_FORMULA_TRIGGER, [msg_id::VAR_ID => $this->dsp_id()]);
        }

        log_debug('->save_field_trigger_update timestamp of ' .
            $this->id() . ' updated to "' . $this->last_update->format('Y-m-d H:i:s') .
            '" with ' . $usr_msg->get_last_message());

        // save the pending update to the database for the batch calculation
        return $usr_msg;
    }

    /**
     * set the update parameters for the formula text as written by the user if needed
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_usr_text(sql_db $db_con, formula $db_rec, formula $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->usr_text <> $this->usr_text) {
            $this->needs_res_upd = true;
            $log = $this->log_upd();
            $log->old_value = $db_rec->usr_text;
            $log->new_value = $this->usr_text;
            $log->std_value = $std_rec->usr_text;
            $log->row_id = $this->id();
            $log->set_field(self::FLD_FORMULA_USER_TEXT);
            $usr_msg->add($this->save_field_user($db_con, $log));
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the formula in the database reference format
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_ref_text(sql_db $db_con, formula $db_rec, formula $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->ref_text <> $this->ref_text) {
            $this->needs_res_upd = true;
            $log = $this->log_upd();
            $log->old_value = $db_rec->ref_text;
            $log->new_value = $this->ref_text;
            $log->std_value = $std_rec->ref_text;
            $log->row_id = $this->id();
            $log->set_field(self::FLD_FORMULA_TEXT);
            $usr_msg->add($this->save_field_user($db_con, $log));
            // updating the reference expression is probably relevant for calculation, so force to update the timestamp
            if ($usr_msg->is_ok()) {
                $usr_msg->add($this->save_field_trigger_update($db_con));
            }
        }
        return $usr_msg;
    }

    /**
     * set the update parameters that define if all results are needed to calculate a result
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_need_all(sql_db $db_con, formula $db_rec, formula $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->need_all_val <> $this->need_all_val) {
            $this->needs_res_upd = true;
            $log = $this->log_upd();
            if ($db_rec->need_all_val) {
                $log->old_value = '1';
            } else {
                $log->old_value = '0';
            }
            if ($this->need_all_val) {
                $log->new_value = '1';
            } else {
                $log->new_value = '0';
            }
            if ($std_rec->need_all_val) {
                $log->std_value = '1';
            } else {
                $log->std_value = '0';
            }
            $log->row_id = $this->id();
            $log->set_field(self::FLD_ALL_NEEDED);
            $usr_msg->add($this->save_field_user($db_con, $log));
            // switch on that all fields are needed for the calculation, probably some formula results can be removed
            if ($usr_msg->is_ok()) {
                $usr_msg->add($this->save_field_trigger_update($db_con));
            }
        }
        return $usr_msg;
    }

    /**
     * save all updated formula fields
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param formula|sandbox $db_obj the database record before the saving
     * @param formula|sandbox $norm_obj the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_all_fields(sql_db $db_con, formula|sandbox $db_obj, formula|sandbox $norm_obj): user_message
    {
        $usr_msg = parent::save_fields_typed($db_con, $db_obj, $norm_obj);
        $usr_msg->add($this->save_field_usr_text($db_con, $db_obj, $norm_obj));
        $usr_msg->add($this->save_field_ref_text($db_con, $db_obj, $norm_obj));
        $usr_msg->add($this->save_field_need_all($db_con, $db_obj, $norm_obj));
        if (!$usr_msg->is_ok()) {
            log_debug('not all fields for ' . $this->dsp_id() . ' have been saved because ' . $usr_msg->get_last_message());
        } else {
            log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        }
        return $usr_msg;
    }

    /**
     * set the update parameters for the formula text as written by the user if needed
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_field_name(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): user_message
    {
        $usr_msg = new user_message();
        if ($db_rec->name() <> $this->name()) {
            log_debug('->save_field_name to ' . $this->dsp_id() . ' from "' . $db_rec->name() . '"');
            $this->needs_res_upd = true;
            if ($this->can_change() and $this->not_changed()) {
                $log = $this->log_upd();
                $log->old_value = $db_rec->name();
                $log->new_value = $this->name();
                $log->std_value = $std_rec->name();
                $log->row_id = $this->id();
                $log->set_field(self::FLD_NAME);
                $usr_msg->add($this->save_field_user($db_con, $log));
                // in case a word link exist, change also the name of the word
                $wrd = new word($this->user());
                $wrd->load_by_name($db_rec->name());
                $wrd->set_name($this->name());
                $usr_msg->add($wrd->save());

            } else {
                // create a new formula
                // and request the deletion confirms for the old from all changers
                // ???? or update the user formula table
                log_warning('formula->save_field_name automatic creation of a new formula (' . $this->dsp_id()
                    . ') and deletion of the old  (' . $db_rec->dsp_id() . ') is not yet coded');
            }
        }
        return $usr_msg;
    }

    /**
     * updated the view component name (which is the id field)
     * should only be called if the user is the owner and nobody has used the display component link
     */
    function save_id_fields(sql_db $db_con, sandbox $db_rec, sandbox $std_rec): string
    {
        $result = '';
        if ($db_rec->name() <> $this->name()) {
            log_debug('->save_id_fields to ' . $this->dsp_id() . ' from ' . $db_rec->dsp_id() . ' (standard ' . $std_rec->dsp_id() . ')');
            // in case a word link exist, change also the name of the word
            if (!$this->wrd_rename($db_rec->name())) {
                $result .= 'formula ' . $db_rec->name() . ' cannot ba renamed to ' . $this->name() . ', because ...';
            }

            // change the formula name
            $log = $this->log_upd();
            $log->old_value = $db_rec->name();
            $log->new_value = $this->name();
            $log->std_value = $std_rec->name();
            $log->row_id = $this->id();
            $log->set_field(self::FLD_NAME);
            if ($log->add()) {
                $db_con->set_class(formula::class);
                if (!$db_con->update_old($this->id(),
                    array(self::FLD_NAME),
                    array($this->name()))) {
                    $result .= 'formula ' . $db_rec->name() . ' cannot be renamed to ' . $this->name();
                }
            }
        }
        log_debug('->save_id_fields for ' . $this->dsp_id() . ' has been done');
        return $result;
    }

    private
    function is_term_the_same(term $trm): bool
    {
        global $phr_typ_cac;

        $result = false;
        if ($trm->type() == formula::class) {
            //$result = $trm;
            $result = true;
        } elseif ($trm->type() == word::class) {
            if ($trm->obj() == null) {
                log_warning('The object of the term has been expected to be loaded');
            } else {
                if ($trm->obj()->type_id == $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK)) {
                    //$result = $trm;
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * check if the id parameters are supposed to be changed
     * and check if the name is already used
     * @param sql_db $db_con the active database connection
     * @param sandbox $db_rec the database record before the saving
     * @param sandbox $std_rec the database record defined as standard because it is used by most users
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @returns user_message a messages for the user what should be changed if something failed
     */
    function save_id_if_updated(sql_db $db_con, sandbox $db_rec, sandbox $std_rec, bool $use_func): user_message
    {
        log_debug('->save_id_if_updated has name changed from "' . $db_rec->name() . '" to ' . $this->dsp_id());
        $usr_msg = new user_message();

        // if the name has changed, check if word, verb or formula with the same name already exists
        // this should have been checked by the calling function, so display the error message directly if it happens
        if ($db_rec->name() <> $this->name()) {
            // check if a word, triple or verb with the same name is already in the database
            $trm = $this->get_term();
            if ($trm->id_obj() > 0 and !$this->is_term_the_same($trm)) {
                $usr_msg->add($trm->id_used_msg($this));
                log_debug('->save_id_if_updated name "' . $trm->name() . '" used already as "' . $trm->type() . '"');
            } else {

                // check if target formula name already exists
                log_debug('->save_id_if_updated check if target formula already exists ' . $this->dsp_id() . ' (has been ' . $db_rec->dsp_id() . ')');
                $db_chk = clone $this;
                $db_chk->set_id(0); // to force the load by the id fields
                $db_chk->load_standard();
                if ($db_chk->id() > 0) {
                    log_debug('->save_id_if_updated target formula name already exists ' . $db_chk->dsp_id());
                    if (UI_CAN_CHANGE_FORMULA_NAME) {
                        // ... if yes request to delete or exclude the record with the id parameters before the change
                        $to_del = clone $db_rec;
                        $msg = $to_del->del();
                        $usr_msg->add($msg);
                        // ... and use it for the update
                        $this->set_id($db_chk->id());
                        $this->set_owner_id($db_chk->owner_id());
                        // force including again
                        $this->include();
                        $db_rec->exclude();
                        $this->save_field_excluded($db_con, $db_rec, $std_rec);
                        log_debug('->save_id_if_updated found a display component link with target ids "' . $db_chk->dsp_id() . '", so del "' . $db_rec->dsp_id() . '" and add ' . $this->dsp_id());
                    } else {
                        $usr_msg->add_id_with_vars(msg_id::COMPONENT_ALREADY_EXISTS, [msg_id::VAR_COMPONENT_NAME => $this->name()]);
                    }
                } else {
                    // the formula can be renamed (either for this user or for all users)
                    log_debug('->save_id_if_updated target formula name does not yet exists ' . $db_chk->dsp_id());
                    if ($this->can_change() and $this->not_used()) {
                        // in this case change is allowed and done
                        log_debug('->save_id_if_updated change the existing display component link ' . $this->dsp_id() . ' (db "' . $db_rec->dsp_id() . '", standard "' . $std_rec->dsp_id() . '")');
                        //$this->load_objects();
                        $usr_msg->add_message_text($this->save_id_fields($db_con, $db_rec, $std_rec));
                    } else {
                        // if the target link has not yet been created
                        // ... request to delete the old
                        $to_del = clone $db_rec;
                        $msg = $to_del->del();
                        $usr_msg->add($msg);
                        // ... and create a deletion request for all users ???

                        // ... and create a new display component link
                        $this->set_id(0);
                        $this->set_owner_id($this->user()->id());
                        // TODO check the usr_msg values and if the id is needed
                        $usr_msg->add($this->add());
                        log_debug('->save_id_if_updated recreate the display component link del "' . $db_rec->dsp_id() . '" add ' . $this->dsp_id() . ' (standard "' . $std_rec->dsp_id() . '")');
                    }
                }
            }
        }

        log_debug('->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $usr_msg;
    }

    /**
     * create a new formula
     * the user sandbox function is overwritten because the formula text should never be null
     * and the corresponding formula word is created
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    function add(bool $use_func = false): user_message
    {
        log_debug($this->dsp_id());

        global $db_con;
        $usr_msg = new user_message();

        if ($use_func) {
            $sc = $db_con->sql_creator();
            $qp = $this->sql_insert($sc, new sql_type_list([sql_type::LOG]));
            $ins_msg = $db_con->insert($qp, 'add and log ' . $this->dsp_id());
            if ($ins_msg->is_ok()) {
                $this->set_id($ins_msg->get_row_id());
            }
            $usr_msg->add($ins_msg);
        } else {

            // log the insert attempt first
            $log = $this->log_add();
            if ($log->id() > 0) {
                // insert the new formula
                $db_con->set_class(formula::class);
                // include the formula_text and the resolved_text, because they should never be empty which is also forced by the db structure
                $this->set_id($db_con->insert_old(
                    array(self::FLD_NAME, user::FLD_ID, self::FLD_LAST_UPDATE, self::FLD_FORMULA_TEXT, self::FLD_FORMULA_USER_TEXT),
                    array($this->name(), $this->user()->id(), sql::NOW, $this->ref_text, $this->usr_text)));
                if ($this->id() > 0) {
                    log_debug('->add formula ' . $this->dsp_id() . ' has been added as ' . $this->id());
                    // update the id in the log for the correct reference
                    if (!$log->add_ref($this->id())) {
                        $usr_msg->add_id(msg_id::FAILED_UPDATE_REF);
                        $this->set_id(0);
                        // TODO do rollback or retry?
                    }
                } else {
                    $usr_msg->add_id_with_vars(msg_id::FAILED_ADD_FORMULA, [msg_id::VAR_NAME => $this->name]);
                }
            }
        }
        if ($this->id() > 0) {
            // create the related formula word
            // the creation of a formula word should not be needed if on creation a view of word, phrase, verb nad formula is used to check uniqueness
            // the creation of the formula word is switched off because the term loading should be fine now
            // TODO check and remove the create_wrd function and the phrase_type_shared::FORMULA_LINK
            if ($this->wrd_add()) {

                // create an empty db_frm element to force saving of all set fields
                $db_rec = new formula($this->user());
                $db_rec->set_name($this->name());
                $std_rec = clone $db_rec;
                // save the formula fields
                if ($use_func) {
                    $usr_msg->add($this->save_fields_func($db_con, $db_rec, $std_rec));
                } else {
                    $usr_msg->add($this->save_all_fields($db_con, $db_rec, $std_rec));
                }
            }
        } else {
            $usr_msg->add_id_with_vars(msg_id::FAILED_ADD_FORMULA, [msg_id::VAR_NAME => $this->name]);
        }

        return $usr_msg;
    }

    /**
     * add or update a formula in the database or create a user formula
     * overwrite the _sandbox function to create the formula ref text; maybe combine later
     *
     * @param bool $use_func if true a predefined function is used that also creates the log entries
     * @return user_message the message shown to the user why the action has failed or an empty string if everything is fine
     */
    function save(?bool $use_func = null): user_message
    {
        log_debug($this->dsp_id());

        global $db_con;
        global $mtr;
        global $phr_typ_cac;

        // decide which db write method should be used
        if ($use_func === null) {
            $use_func = $this->sql_default_script_usage();
        }

        // check the preserved names
        $usr_msg = $this->check_save();

        if ($usr_msg->is_ok()) {

            // build the database object because the is anyway needed
            $db_con->set_class(formula::class);
            $db_con->set_usr($this->user()->id());

            // check if a new formula is supposed to be added
            if ($this->id() <= 0) {
                // check if a verb, formula or word with the same name is already in the database
                log_debug('add ' . $this->dsp_id());
                $trm = $this->get_term();
                if ($trm->id_obj() > 0) {
                    if ($trm->type() <> formula::class) {
                        if ($trm->type() == word::class) {
                            if ($trm->obj()->type_id == $phr_typ_cac->id(phrase_type_shared::FORMULA_LINK)) {
                                log_debug('adding formula name ' . $this->dsp_id() . ' has just a matching formula word');
                            } else {
                                $usr_msg->add($trm->id_used_msg($this));
                            }
                        } else {
                            $usr_msg->add($trm->id_used_msg($this));
                        }
                    } else {
                        $this->set_id($trm->id_obj());
                        log_debug('->save adding formula name ' . $this->dsp_id() . ' is OK');
                    }
                }
            }
        }

        // create an object to check possible duplicates
        $similar = null;

        // if a new object is supposed to be added check upfront for a similar object to prevent adding duplicates
        if ($this->id() == 0) {
            log_debug('check possible duplicates before adding ' . $this->dsp_id());
            $similar = $this->get_similar();
            if ($similar->id() <> 0) {
                // check that the get_similar function has really found a similar object and report potential program errors
                if (!$this->is_similar($similar)) {
                    $msg_not = $mtr->txt(msg_id::NOT_SIMILAR);

                    $usr_msg->add_id_with_vars(msg_id::FORMULA_NOT_SIMILAR, [
                        msg_id::VAR_ID => $this->dsp_id(),
                        msg_id::VAR_VALUE => $msg_not,
                        msg_id::VAR_VAL_ID => $similar->dsp_id()
                    ]);
                } else {
                    // if similar is found set the id to trigger the updating instead of adding
                    $similar->load_by_id($similar->id()); // e.g. to get the type_id
                    // prevent that the id of a formula is used for the word with the type formula link
                    if (get_class($this) == get_class($similar)) {
                        $this->set_id($similar->id());
                    } else {
                        if (!((get_class($this) == word::class and get_class($similar) == formula::class)
                            or (get_class($this) == triple::class and get_class($similar) == formula::class))) {
                            $usr_msg->add($similar->id_used_msg($this));
                        }
                    }
                }
            } else {
                $similar = null;
            }

        }

        // create a new formula or update an existing
        if ($usr_msg->is_ok()) {
            if ($this->id() <= 0) {
                // convert the formula text to db format (any error messages should have been returned from the calling user script)
                $usr_msg->add_message_text($this->generate_ref_text());
                if ($usr_msg->is_ok()) {

                    log_debug('add');
                    $usr_msg->add($this->add($use_func));
                }
            } else {
                // if the similar object is not the same as $this object, suggest renaming $this object
                if ($similar != null) {
                    log_debug('got similar and suggest renaming or merge');
                    // e.g. if a source already exists update the source
                    // but if a word with the same name of a formula already exists suggest a new formula name
                    if (!$this->is_same($similar)) {
                        $usr_msg->add($similar->id_used_msg($this));
                    }
                }

                // update the existing object
                if ($usr_msg->is_ok()) {
                    log_debug('update ' . $this->id());
                    // read the database values to be able to check if something has been changed; done first,
                    // because it needs to be done for user and general formulas
                    $db_rec = new formula($this->user());
                    $db_rec->load_by_id($this->id());
                    log_debug('database formula "' . $db_rec->name() . '" (' . $db_rec->id() . ') loaded');

                    // relevant is if there is a user config in the database
                    // so use this information to prevent
                    // the need to forward the db_rec to all functions
                    if ($db_rec->has_usr_cfg() and !$this->has_usr_cfg()) {
                        $this->usr_cfg_id = $db_rec->usr_cfg_id;
                    }

                    $std_rec = new formula($this->user()); // must also be set to allow to take the ownership
                    $std_rec->set_id($this->id());
                    $std_rec->load_standard();
                    log_debug('standard formula "' . $std_rec->name() . '" (' . $std_rec->id() . ') loaded');

                    // for a correct user formula detection (function can_change) set the owner even if the formula has not been loaded before the save
                    if ($this->owner_id() <= 0) {
                        $this->set_owner_id($std_rec->owner_id());
                    }

                    // ... and convert the formula text to db format (any error messages should have been returned from the calling user script)
                    $usr_msg->add_message_text($this->generate_ref_text());
                    if ($usr_msg->is_ok()) {

                        // check if the id parameters are supposed to be changed
                        $usr_msg->add($this->save_id_if_updated($db_con, $db_rec, $std_rec, $use_func));

                        // if a problem has appeared up to here, don't try to save the values
                        // the problem is shown to the user by the calling interactive script
                        if ($usr_msg->is_ok()) {
                            if ($use_func) {
                                $usr_msg->add($this->save_fields_func($db_con, $db_rec, $std_rec));
                            } else {
                                $usr_msg->add($this->save_all_fields($db_con, $db_rec, $std_rec));
                            }
                        }
                    }
                }

            }

            // update the reference table for fast calculation
            // a '1' in the result only indicates that an update has been done for testing; '1' doesn't mean that there has been an error
            if ($usr_msg->is_ok()) {
                if (!$this->element_refresh($this->ref_text)) {
                    $usr_msg->add_id(msg_id::FAILED_REFRESH_FORMULA);
                }
            }
        }

        if (!$usr_msg->is_ok()) {
            log_info($usr_msg->get_last_message());
        }

        return $usr_msg;

    }


    /*
     * save helper
     */

    /**
     * @return array with the reserved formula names
     */
    protected function reserved_names(): array
    {
        return formulas::RESERVED_NAMES;
    }

    /**
     * @return array with the fixed formula names for db read testing
     */
    protected function fixed_names(): array
    {
        return formulas::FIXED_NAMES;
    }


    /*
     * del
     */

    /**
     * remove depending on objects
     * needs to be overwritten by the child class if needed
     * TODO make sure that only user specific data is deleted
     *
     * @return user_message the message for the user why the action has failed and a suggested solution
     */
    function del_links(): user_message
    {
        global $db_con;
        global $phr_typ_cac;
        $usr_msg = new user_message();

        $frm_lnk_lst = new formula_link_list($this->user());
        if ($frm_lnk_lst->load_by_frm_id($this->id())) {
            $msg = $frm_lnk_lst->del_without_log();
            $usr_msg->add_message_text($msg);
        }

        // and the corresponding formula elements
        if ($usr_msg->is_ok()) {
            $elm_lst = new element_list($this->user());
            $elm_lst->load_by_frm($this->id());
            // TODO add del function with test
            //$usr_msg->add($elm_lst->del_without_log());

            $db_con->set_class(element::class);
            $db_con->set_usr($this->user()->id());
            $msg = $db_con->delete_old($this->id_field(), $this->id());
            $usr_msg->add_message_text($msg);
        }

        // and the corresponding results
        if ($usr_msg->is_ok()) {
            $db_con->set_class(result::class);
            $db_con->set_usr($this->user()->id());
            $msg = $db_con->delete_old($this->id_field(), $this->id());
            $usr_msg->add_message_text($msg);
        }

        // and the corresponding word if possible
        if ($usr_msg->is_ok()) {
            $usr_msg->add($this->wrd_del());
        }

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
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                self::FLD_TYPE,
                self::FLD_FORMULA_TEXT,
                self::FLD_FORMULA_USER_TEXT,
                self::FLD_ALL_NEEDED,
                self::FLD_LAST_UPDATE,
                self::FLD_VIEW,
                self::FLD_USAGE
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox|formula $sbx the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        sandbox|formula $sbx,
        sql_type_list   $sc_par_lst = new sql_type_list()
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
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_TYPE,
                    $cng_fld_cac->id($table_id . self::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_TYPE,
                $this->type_id(),
                self::FLD_TYPE_SQL_TYP,
                $sbx->type_id()
            );
        }
        if ($sbx->ref_text <> $this->ref_text) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_FORMULA_TEXT,
                    $cng_fld_cac->id($table_id . self::FLD_FORMULA_TEXT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_FORMULA_TEXT,
                $this->ref_text,
                self::FLD_FORMULA_TEXT_SQL_TYP,
                $sbx->ref_text
            );
        }
        if ($sbx->usr_text <> $this->usr_text) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_FORMULA_USER_TEXT,
                    $cng_fld_cac->id($table_id . self::FLD_FORMULA_USER_TEXT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_FORMULA_USER_TEXT,
                $this->usr_text,
                self::FLD_FORMULA_USER_TEXT_SQL_TYP,
                $sbx->usr_text
            );
        }
        if ($sbx->need_all_val <> $this->need_all_val) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_ALL_NEEDED,
                    $cng_fld_cac->id($table_id . self::FLD_ALL_NEEDED),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_ALL_NEEDED,
                $this->need_all_val,
                self::FLD_ALL_NEEDED_SQL_TYP,
                $sbx->need_all_val
            );
        }
        if ($sbx->ref_text <> $this->ref_text
            or $sbx->type_id() <> $this->type_id()
            or $sbx->need_all_val <> $this->need_all_val
            or $this->last_update == null) {
            $lst->add_field(
                self::FLD_LAST_UPDATE,
                sql::NOW,
                sql_field_type::TIME
            );
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
        if ($sbx->usage() <> $this->usage()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_USAGE,
                    $cng_fld_cac->id($table_id . self::FLD_USAGE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_USAGE,
                $this->usage(),
                self::FLD_USAGE_SQL_TYP,
                $sbx->usage()
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($sbx, $sc_par_lst));
    }

}