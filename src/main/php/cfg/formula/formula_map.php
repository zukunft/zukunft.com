<?php

/*

    model/formula/formula_map.php - the formula object for mapping including the database mapping
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
    - link:              add or remove a link to a word (this is user-specific, so use the user sandbox)
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

namespace Zukunft\ZukunftCom\main\php\cfg\formula;

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::MODEL_ELEMENT . 'element.php';
include_once paths::MODEL_ELEMENT . 'element_list.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_IMPORT . 'import.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_PHRASE . 'phrase_type.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_PHRASE . 'term_list.php';
include_once paths::MODEL_PHRASE . 'trm_ids.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_code_id.php';
include_once paths::MODEL_SANDBOX . 'protection_type.php';
include_once paths::MODEL_SANDBOX . 'share_type.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_db.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_FORMULA . 'formula_type.php';
include_once paths::MODEL_FORMULA . 'formula_link.php';
include_once paths::MODEL_FORMULA . 'formula_link_type.php';
include_once paths::MODEL_FORMULA . 'expression.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'formula_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\element\element;
use Zukunft\ZukunftCom\main\php\cfg\element\element_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\import\import;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\trm_ids;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_db;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\formula_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use DateTime;
use Exception;

class formula_map extends sandbox_code_id
{

    /*
     * db const
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'the mathematical expression to calculate results based on values and results';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = formula_db::FLD_ID;
    const array FLD_LST_MUST_BE_IN_STD = formula_db::FLD_LST_MUST_BE_IN_STD;
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = formula_db::FLD_LST_MUST_BUT_USER_CAN_CHANGE;
    const array FLD_LST_USER_CAN_CHANGE = formula_db::FLD_LST_USER_CAN_CHANGE;
    const array FLD_NAMES = formula_db::FLD_NAMES;
    const array FLD_NAMES_USR = formula_db::FLD_NAMES_USR;
    const array FLD_NAMES_NUM_USR = formula_db::FLD_NAMES_NUM_USR;
    const array ALL_SANDBOX_FLD_NAMES = formula_db::ALL_SANDBOX_FLD_NAMES;


    /*
     * object vars
     */

    // database fields additional to the user sandbox fields
    public ?string $ref_text = null;       // the formula expression with the names replaced by database references
    protected bool $ref_text_dirty;          // true if the human-readable text has been updated and not yet converted
    public ?string $usr_text = null;       // the formula expression in the user format
    private bool $usr_text_dirty;          // true if the reference text has been updated and not yet converted
    public ?string $description = null;    // describes to the user what this formula is doing
    public ?bool $need_all_val = null;     // calculate and save the result only if all used values are not null
    public ?DateTime $last_update = null;  // the time of the last update of fields that may influence the calculated results
    private ?view $view;                   // name of the default view for this formula
    // the importance of the word based on the value defined for each word by the words "impact" and "criteria"
    public ?float $impact = null {
        get {
            // TODO Prio 2 calculate impact from criteria if useful or requested
            return $this->impact;
        }
        /**
         * set the cache value to sort this formula by relevance
         * the impact is calculated based on the formula assigned to the object
         * by the system triple "impact phrase"
         *
         * @param float|null $impact a higher value moves the sandbox object to the top of the selection list
         */
        set(?float $impact) {
            // TODO Prio 2 remember refresh timestamp to avoid too many updates
            $this->impact = $impact;
        }
    }

    // in memory only fields
    // list of phrase that link to this formula
    private ?formula_link_list $lnk_lst = null;
    // old list of phrase that link to this formula
    private ?phrase_list $phr_lst = null;
    // TODO Prio 0 deprecate
    public ?string $type_cl = '';          // the code id of the formula type
    public ?word $name_wrd = null;         // the triple object for the formula name:
    //                                        because values can only be assigned to phrases, also for the formula name a triple must exist
    public bool $needs_res_upd = false;     // true if the formula results needs to be updated


    /*
     * construct and map
     */

    /**
     * define the settings for this formula object
     * @param user $usr the user who requested to see this formula
     */
    function __construct(user $usr)
    {
        $this->reset();
        parent::__construct($usr);

        $this->rename_can_switch = def::UI_CAN_CHANGE_FORMULA_NAME;
    }

    /**
     * clear the view component object values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);

        $this->ref_text = null;
        $this->ref_text_dirty = false;
        $this->usr_text = null;
        $this->usr_text_dirty = false;
        $this->type_id = null;
        $this->need_all_val = null;
        $this->last_update = null;
        $this->impact = null;

        $this->lnk_lst = null;
        $this->phr_lst = null;
        $this->type_cl = '';
        $this->name_wrd = null;

        $this->needs_res_upd = false;

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
        string $id_fld = formula_db::FLD_ID,
        string $name_fld = formula_db::FLD_NAME,
        string $type_fld = formula_db::FLD_TYPE): bool
    {
        global $sys;
        $lib = new library();
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        if ($result) {
            if (array_key_exists(formula_db::FLD_FORMULA_TEXT, $db_row)) {
                $this->ref_text = $db_row[formula_db::FLD_FORMULA_TEXT];
            }
            if (array_key_exists(formula_db::FLD_FORMULA_USER_TEXT, $db_row)) {
                $this->usr_text = $db_row[formula_db::FLD_FORMULA_USER_TEXT];
            }
            if (array_key_exists(formula_db::FLD_ALL_NEEDED, $db_row)) {
                $this->need_all_val = $lib->get_bool($db_row[formula_db::FLD_ALL_NEEDED]);
            }
            if (array_key_exists(formula_db::FLD_LAST_UPDATE, $db_row)) {
                $this->last_update = $lib->get_datetime($db_row[formula_db::FLD_LAST_UPDATE], $this->dsp_id());
            }
            if (array_key_exists(formula_db::FLD_VIEW, $db_row)) {
                if ($db_row[formula_db::FLD_VIEW] != null) {
                    $this->set_view_id($db_row[formula_db::FLD_VIEW]);
                }
            }
            if (array_key_exists(sql_db::FLD_IMPACT, $db_row)) {
                $this->impact = $db_row[sql_db::FLD_IMPACT];
            }

            if ($this->type_id > 0) {
                $this->type_cl = $sys->typ_lst->frm_typ->code_id($this->type_id);
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
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::USR_TEXT, $api_json)) {
            if ($api_json[json_fields::USR_TEXT] <> '') {
                $this->set_user_text($api_json[json_fields::USR_TEXT]);
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * set the vars of this formula object based on the given json without writing to the database
     * and its link phrases based on an import JSON object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $msg,
        ?data_object $dto = null
    ): bool
    {
        global $sys;

        // reset the all parameters for the formula object but keep the user
        $this->reset(true);
        parent::import_mapper($in_ex_json, $msg, $dto);

        if (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            $this->type_id = $sys->typ_lst->frm_typ->id($in_ex_json[json_fields::TYPE_NAME]);
        } else {
            $this->type_id = $sys->typ_lst->frm_typ->default_id();
        }

        if (key_exists(json_fields::USR_TEXT, $in_ex_json)) {
            if ($in_ex_json[json_fields::USR_TEXT] <> '') {
                $this->set_user_text($in_ex_json[json_fields::USR_TEXT]);
            }
        }
        // TODO Prio 2 decide if either it should be named expression or user text or if expression is used for im and export and user text for api
        if (key_exists(json_fields::EXPRESSION, $in_ex_json)) {
            if ($in_ex_json[json_fields::EXPRESSION] <> '') {
                $this->usr_text = $in_ex_json[json_fields::EXPRESSION];
            }
        }

        // TODO Prio 2 allow only one way to assign phrases on import
        // assign the phrases to the formula
        if (key_exists(json_fields::ASSIGNED, $in_ex_json)) {
            $phr_lst = new phrase_list($this->get_user());
            $phr_lst->import_map_names($in_ex_json[json_fields::ASSIGNED], $dto);
        }

        // assign the phrases to the formula
        if (key_exists(json_fields::ASSIGNED_WORD, $in_ex_json)) {
            $phr_names = explode(",", $in_ex_json[json_fields::ASSIGNED_WORD]);
            if ($dto != null) {
                $phr_lst = $dto->phrase_list();
                foreach ($phr_names as $name) {
                    $phr = $phr_lst->get_by_name($name);
                    if ($phr == null) {
                        $msg->add(msg_id::IMPORT_FORMULA_ASSIGN_PHRASE_MISSING, [
                            msg_id::VAR_FILE_NAME => json_encode($in_ex_json),
                            msg_id::VAR_NAME => $name,
                            msg_id::VAR_FORMULA => $this->name(),
                        ]);
                    } else {
                        $this->link_phrase($phr, $msg);
                    }
                }
            }
        }

        // set the default type if no type is specified
        if ($this->type_id == 0) {
            $this->type_id = $sys->typ_lst->frm_typ->default_id();
        }

        return $msg->is_ok();
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

        $vars = [];
        if (!$this->is_excluded() or $typ_lst->test_mode() or $typ_lst->with_excluded()) {
            $vars = parent::api_json_array($typ_lst, $usr);
            $vars[json_fields::USR_TEXT] = $this->usr_text;
            $vars[json_fields::REF_TEXT] = $this->ref_text;
            $vars[json_fields::IMPACT] = $this->impact;
        } elseif ($this->is_excluded() and $typ_lst->with_excluded_id()) {
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
            $vars[json_fields::IMPACT] = $this->impact;
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the predefined type of this formula by the given code id or name
     *
     * @param string $code_id_or_name the code id or name that should be added to this formula
     * @param user $usr_req the user who wants to change the type
     * @return user_message a warning if the view type code id is not found
     */
    function set_type(string $code_id_or_name, user $usr_req = new user()): user_message
    {
        global $sys;
        if ($sys->typ_lst->frm_typ->has_code_id($code_id_or_name)) {
            return parent::set_type_by_code_id(
                $code_id_or_name, $sys->typ_lst->frm_typ, msg_id::FORMULA_TYPE_NOT_FOUND, $usr_req);
        } else {
            return parent::set_type_by_name(
                $code_id_or_name, $sys->typ_lst->frm_typ, msg_id::FORMULA_TYPE_NOT_FOUND, $usr_req);
        }
    }

    /**
     * @param int $id the id of the default view that should be remembered
     */
    function set_view_id(int $id): void
    {
        if ($this->view == null) {
            $this->view = new view($this->get_user());
        }
        $this->view->id = $id;
    }

    /**
     * @return int the id of the default view for this word or null if no view is preferred
     */
    function get_view_id(): int
    {
        if ($this->view == null) {
            return 0;
        } else {
            return $this->view->id();
        }
    }

    /**
     * update the expression by setting the human-readable format and try to update the database reference format
     * @param string $usr_txt the formula expression in the human-readable format
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return bool true if also the reference text has been updated
     */
    function set_user_text(
        string       $usr_txt,
        ?term_list   $trm_lst = null,
        user_message $usr_msg = new user_message()
    ): bool
    {
        $this->usr_text = $usr_txt;
        $this->usr_text_dirty = false;
        $this->ref_text_dirty = true;
        return $this->generate_ref_text($trm_lst, $usr_msg);
    }

    function get_usr_text(
        ?term_list   $trm_lst = null,
        user_message $usr_msg = new user_message()
    ): string
    {
        if ($this->usr_text_dirty) {
            $this->generate_usr_text($trm_lst, $usr_msg);
        }
        return $this->usr_text;
    }

    function get_ref_text(
        ?term_list   $trm_lst = null,
        user_message $usr_msg = new user_message()
    ): ?string
    {
        if ($this->ref_text_dirty) {
            $this->generate_ref_text($trm_lst, $usr_msg);
        }
        return $this->ref_text;
    }


    /*
     * preloaded
     */

    /**
     * @return string|null the code_id of the formula type
     */
    function type_code_id(): string|null
    {
        global $sys;
        return $sys->typ_lst->frm_typ->code_id($this->type_id);
    }

    /**
     * @return string the name of the formula type
     */
    function type_name(): string
    {
        global $sys;
        return $sys->typ_lst->frm_typ->name($this->type_id);
    }



    /*
     * info
     */

    /**
     * if the formula has a fixed process for the result
     * e.g. "this" or "next" where the value of this or the following time word is returned
     * @return bool true if result calculation is a kind of hardcoded
     */
    function is_predefined(): bool
    {
        return in_array($this->type_code_id(), formula_type::PREDEFINED_CALCULATION);
    }

    /**
     * if the formula uses the verb / predicate following
     * to select an additional phrase for the value selection
     * e.g. "this" or "next" to add a year to narrow the value selection
     * @return bool true if another time phrase should be used for the value selection
     */
    function uses_following(): bool
    {
        return in_array($this->type_code_id(), formula_type::USES_FOLLOWING);
    }


    /*
     * related
     */

    /**
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return expression the formula expression as an expression element
     */
    function expression(?term_list $trm_lst = null): expression
    {
        $exp = new expression($this);
        // TODO Prio 0 use the ref text check function that includes the user message
        if ($this->ref_text != '' and $this->usr_text != '') {
            $exp->set_ref_and_user_text($this->ref_text, $this->usr_text);
        } else {
            $exp->set_ref_text($this->ref_text, $trm_lst);
            $exp->set_user_text($this->usr_text, $trm_lst);
        }
        return $exp;
    }


    /*
     * load sql
     */

    /**
     * create the common part of an SQL statement to retrieve
     * the parameters of a formula from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        // maybe the formula name should be excluded from the user sandbox to avoid confusion
        return parent::load_sql_usr_num($sc, $this, $query_name);
    }


    /*
     * related
     */

    /**
     * load the corresponding name word for the formula name
     * @param bool $with_automatic_error_fixing to add any missing words automatically
     * @return bool true if the word has been loaded
     */
    function reload_wrd(bool $with_automatic_error_fixing = true): bool
    {
        $result = true;
        $usr_msg = new user_message();

        $do_load = true;
        if (isset($this->name_wrd)) {
            if ($this->name_wrd->name == $this->name()) {
                $do_load = false;
            }
        }
        if ($do_load) {
            log_debug('->load_wrd load ' . $this->dsp_id());
            $name_wrd = new word($this->get_user());
            $name_wrd->load_by_name($this->name());
            if ($name_wrd->id() > 0) {
                $this->name_wrd = $name_wrd;
            } else {
                // if the loading of the corresponding triple fails,
                // try to recreate it and report the internal error
                // because this should actually never happen
                if ($with_automatic_error_fixing) {
                    if (!$this->wrd_add_fix($usr_msg)) {
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

    /**
     * load the terms from the database that are used in this formula expression
     * and that are not yet in the cache term list
     * and if terms are added, add the formula to the given list of formulas that should be updated
     *
     * @param user_message $usr_msg to collect messages which terms are missing
     * @param term_list|null $trm_lst with the terms that are already in the cache term list
     * @param formula_list|null $frm_lst to collect formulas that should be updated with the terms that have been loaded
     * @return term_list the additional terms that have been loaded
     */
    function load_missing_terms(
        user_message  $usr_msg,
        ?term_list    $trm_lst = null,
        ?formula_list $frm_lst = null
    ): term_list
    {
        $exp = $this->expression($trm_lst);
        // TODO Prio 2 try to avoid reloading of the terms
        $trm_lst = $this->load_terms($usr_msg, $trm_lst, $exp);
        if ($exp->is_valid() or $this->is_predefined()) {
            $frm_trm_lst = $exp->terms($usr_msg, $trm_lst);
            foreach ($frm_trm_lst->lst() as $trm) {
                $frm_trm = $trm_lst->get_by_name($trm->name());
                if ($frm_trm != null and $frm_lst != null) {
                    if ($frm_trm->is_formula()) {
                        $frm_lst->add_by_key($frm_trm->get_formula());
                    }
                }
            }
            // TODO Prio 1 remove ignoring predefined errors
            if (!$usr_msg->is_ok()) {
                if ($this->is_predefined()) {
                    $usr_msg->reset(true);
                }
            }
        }
        return $trm_lst;
    }

    /**
     * load all missing terms used in the expression,
     * including the phrases that should be added to the formula results
     *
     * @param user_message $usr_msg to collect messages which terms are missing
     * @param term_list|null $trm_lst_in list of terms already loaded
     * @param expression|null $exp if given the already created formula expression object
     * @return term_list
     */
    function load_terms(
        user_message    $usr_msg,
        term_list|null  $trm_lst_in = null,
        expression|null $exp = null
    ): term_list
    {
        if ($exp == null) {
            $exp = $this->expression($trm_lst_in);
        }
        $trm_lst = $this->load_exp_terms($usr_msg, $trm_lst_in, $exp);
        $trm_lst->merge($this->load_phrases($usr_msg, $trm_lst_in, $exp)->term_list());
        return $trm_lst;
    }

    /**
     * load all missing terms used in the expression
     *
     * @param user_message $usr_msg to collect messages which terms are missing
     * @param term_list|null $trm_lst_in list of terms already loaded
     * @param expression|null $exp if given the already created formula expression object
     * @return term_list
     */
    function load_exp_terms(
        user_message    $usr_msg,
        term_list|null  $trm_lst_in = null,
        expression|null $exp = null
    ): term_list
    {
        if ($exp == null) {
            $exp = $this->expression($trm_lst_in);
        }
        $trm_lst = $exp->term_id_list($usr_msg);
        $id_lst = $trm_lst->ids();
        if ($trm_lst_in != null) {
            if (!$trm_lst_in->is_empty()) {
                $ids_loaded = $trm_lst_in->ids();
                $id_lst = array_diff($id_lst, $ids_loaded);
            }
        }
        $trm_ids = new trm_ids($id_lst);
        $trm_lst->reset(true);
        $trm_lst->load_by_ids($trm_ids);
        if ($trm_lst_in != null) {
            $trm_lst->merge($trm_lst_in);
        }
        return $trm_lst;
    }

    /**
     * load all missing result phrases used in the expression
     *
     * @param user_message $usr_msg to collect messages which terms are missing
     * @param term_list|null $trm_lst_in list of terms already loaded
     * @param expression|null $exp if given the already created formula expression object
     * @return phrase_list with the phrases that should be added to the result of a formula
     */
    function load_phrases(
        user_message    $usr_msg,
        term_list|null  $trm_lst_in = null,
        expression|null $exp = null
    ): phrase_list
    {
        if ($exp == null) {
            $exp = $this->expression($trm_lst_in);
        }
        $phr_lst = $exp->phrase_id_list($usr_msg);
        $id_lst = $phr_lst->phrase_ids();
        $phr_lst->reset(true);
        $phr_lst->load_by_ids($id_lst);
        return $phr_lst;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return formula_db::FLD_NAME;
    }

    /**
     * @return array with all fields names of this formula object
     */
    protected function all_fields(): array
    {
        return array_merge(
            formula_db::FLD_NAMES,
            formula_db::FLD_NAMES_USR,
            formula_db::FLD_NAMES_NUM_USR,
            array(user_db::FLD_ID));
    }

    function all_sandbox_fields(): array
    {
        return formula_db::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param formula_map|CombineObject|db_object_seq_id $std_obj the norm object as saved in the database
     * @param formula_map|CombineObject|db_object_seq_id $result empty clone of the target user object
     * @return formula_map|CombineObject|db_object_seq_id the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        formula_map|CombineObject|db_object_seq_id $std_obj,
        formula_map|CombineObject|db_object_seq_id $result
    ): formula_map|CombineObject|db_object_seq_id
    {
        parent::delta($std_obj, $result);
        if ($std_obj->ref_text !== $this->ref_text) {
            $result->ref_text = $this->ref_text;
        }
        if ($std_obj->usr_text !== $this->usr_text) {
            $result->usr_text = $this->usr_text;
        }
        if ($std_obj->need_all_val !== $this->need_all_val) {
            $result->need_all_val = $this->need_all_val;
        }
        if ($std_obj->last_update !== $this->last_update) {
            $result->last_update = $this->last_update;
        }
        if ($std_obj->view !== $this->view) {
            $result->view = $this->view;
        }
        if ($std_obj->impact !== $this->impact) {
            $result->impact = $this->impact;
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * fill this formula based on the given formula
     *
     * @param formula|CombineObject|db_object_seq_id $obj word with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(formula|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);

        if ($obj::class == term::class) {
            $used_obj = $obj->obj();
        } else {
            $used_obj = $obj;
        }

        // do not fill all fields for the term ???
        if ($obj::class == formula::class) {
            if ($this->ref_text === null and $used_obj->ref_text != null) {
                $this->ref_text = $used_obj->ref_text;
            }
            if ($this->usr_text === null and $used_obj->usr_text != null) {
                $this->usr_text = $used_obj->usr_text;
            }
            if ($this->need_all_val === null and $used_obj->need_all_val != null) {
                $this->need_all_val = $used_obj->need_all_val;
            }
            if ($this->last_update === null and $used_obj->last_update != null) {
                $this->last_update = $used_obj->last_update;
            }
            if ($this->view === null and $obj->view != null) {
                $this->view = $obj->view;
            }
        }
        if ($this->impact === null and $used_obj->impact != null) {
            $this->impact = $used_obj->impact;
        }

        return $usr_msg;
    }


    /*
     * check
     */

    /**
     * returns an ok message if this formula can be added to the database if the related terms are added
     * e.g. a formula without any expression should not be added to the database
     * @param user_message|Message $msg the explanation why the link cannot yet be added to the database
     * @return true if the formula can be added to the database
     */
    function can_be_ready(user_message|Message $msg): bool
    {
        if ($this->ref_text == null or $this->ref_text == '') {
            if ($this->usr_text == null or $this->usr_text == '') {
                $msg->add(msg_id::FORMULA_EXPRESSION_MISSING, [
                    msg_id::VAR_FORMULA => $this->dsp_id()
                ]);
            }
        }
        return $msg->is_ok();
    }

    /**
     * returns an OK message if this formula can be added to the database
     * e.g. a formula without expression should not be added to the database
     * @param user_message|Message $msg the explanation why the link cannot yet be added to the database
     * @return true if the formula can be added to the database
     */
    function db_ready(user_message|Message $msg): bool
    {
        parent::db_ready($msg);

        if ($this->ref_text == null or $this->ref_text == '') {
            if ($this->usr_text == null or $this->usr_text == '') {
                $msg->add(msg_id::FORMULA_EXPRESSION_MISSING, [
                    msg_id::VAR_FORMULA => $this->dsp_id()
                ]);
            } else {
                if ($msg->is_ok() and !$this->no_ref_needed()) {
                    $msg->add(msg_id::FORMULA_REF_EXPRESSION_MISSING, [
                        msg_id::VAR_FORMULA => $this->dsp_id()
                    ]);
                }
            }
        }
        return $msg->is_ok();
    }

    /**
     * check if all mandatory formula vars are set
     * the difference between db_ready and is_valid is for named objects that
     * for db_ready either the id or the name must be set
     * for is_valid both the id and the name must be set
     *
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


    /*
     * info
     */

    /**
     * check if the formula in the database needs to be updated
     * e.g. for import  if this formula has only the name set, the protection should not be updated in the database
     *
     * @param formula|CombineObject|IdObject $db_obj the formula as saved in the database
     * @return bool true if this formula has infos that should be saved in the database
     */
    function needs_db_update(formula|CombineObject|IdObject $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        if ($this->get_ref_text() != null) {
            if ($this->get_ref_text() != $db_obj->get_ref_text()) {
                $result = true;
            }
        }
        if ($this->get_usr_text() != null) {
            if ($this->get_usr_text() != $db_obj->get_usr_text()) {
                $result = true;
            }
        }
        if ($this->type_id() != null) {
            if ($this->type_id() != $db_obj->type_id()) {
                $result = true;
            }
        }
        if ($this->need_all_val != $db_obj->need_all_val) {
            $result = true;
        }
        if ($this->impact != null) {
            if ($this->impact != $db_obj->impact) {
                $result = true;
            }
        }
        if ($this->get_view_id() != null) {
            if ($this->get_view_id() != $db_obj->get_view_id()) {
                $result = true;
            }
        }
        return $result;
    }

    function no_ref_needed(): bool
    {
        $result = false;
        if ($this->type_code_id() == formula_types::THIS
            or $this->type_code_id() == formula_types::PREV
            or $this->type_code_id() == formula_types::NEXT) {
            $result = true;
        }
        return $result;
    }


    /*
     * cast
     */

    /**
     * @returns term the formula object cast into a term object
     */
    function term(): term
    {
        $trm = new term($this->get_user());
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * im- and export
     */

    /**
     * import a formula from a JSON object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_obj(
        array        $in_ex_json,
        user_message $msg,
        ?data_object $dto = null
    ): bool
    {
        global $db_con;

        // map the json to the object
        $this->import_mapper($in_ex_json, $msg, $dto);

        // assign the formula to the words and triple
        // TODO check if it is done via mapper and save_related
        $this->assign_phrases($msg);

        // save the object and the related objects in the database
        if ($db_con->is_open()) {
            if ($msg->is_ok()) {
                $this->save($msg);
            }
        }

        return $msg->is_ok();
    }

    /**
     * create an array with the export json fields
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load true if the result should be validated again before export
     * *                    use false for a faster export and unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = parent::export_json($exp_typ, $do_load);

        global $sys;

        // TODO avoid the var overwrite be overwriting the type_name() function
        if (isset($this->type_id)) {
            if ($this->type_id <> $sys->typ_lst->frm_typ->default_id()) {
                $vars[json_fields::TYPE_NAME] = $sys->typ_lst->frm_typ->code_id($this->type_id);
            } else {
                // unset the type that might be set by the parent object
                unset($vars[json_fields::TYPE_NAME]);
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
                    $exp_lst[] = $phr->export_json([]);
                }
                $vars[json_fields::ASSIGNED_WORD] = $exp_lst;
            }
        }
        // the impact is only included in the export as an indication to validate the consistency
        $vars[json_fields::IMPACT] = $this->impact;

        return $vars;
    }


    /*
     * link
     */

    /**
     * add a phrase link to this formula object without updating the database
     * @param phrase $phr with at least the id of a phrase that exists already in the database
     * @param user_message $usr_msg to collect the problems to be able to present solutions to the user
     * @return bool true if the link has been added
     */
    function link_phrase(phrase $phr, user_message $usr_msg): bool
    {
        if ($this->get_user() != null) {
            $this->link_phrase_object($phr);
        } else {
            $usr_msg->add_message_text('user missing');
        }
        return $usr_msg->is_ok();
    }

    /**
     * add a phrase link to this formula object amd update the database
     * @param phrase $phr with at least the id of a phrase that exists already in the database
     * @param user_message $usr_msg to collect the problems to be able to present solutions to the user
     * @return bool true if the link has been added
     */
    function link_phrase_and_save(phrase $phr, user_message $usr_msg): bool
    {
        $frm_lnk = $this->link_phrase_object($phr);
        $frm_lnk->save($usr_msg);
        return $usr_msg->is_ok();
    }

    /**
     * interface function to have a nicer name for link_phrase_and_save
     * @param phrase $phr with at least the id of a phrase that exists already in the database
     * @param user_message $usr_msg to collect the problems to be able to present solutions to the user
     * @return bool true if the link has been added
     */
    function assign_phrase(phrase $phr, user_message $usr_msg): bool
    {
        return $this->link_phrase_and_save($phr, $usr_msg);
    }

    /**
     * create a phrase link to this formula object
     * @param phrase $phr with at least the id of a phrase that exists already in the database
     * @return formula_link with the vars set
     */
    private function link_phrase_object(phrase $phr): formula_link
    {
        if ($this->lnk_lst == null) {
            $this->lnk_lst = new formula_link_list($this->get_user());
        }
        $frm_lnk = new formula_link($this->get_user());
        $frm_lnk->set_formula($this);
        $frm_lnk->set_phrase($phr);
        $this->lnk_lst->add_link_by_key($frm_lnk);
        return $frm_lnk;
    }

    /**
     * link this formula to a phrase where only the name is given
     * @param string $phr_name the name of the phrase
     * @param user_message $usr_msg object to collect the user messages
     * @return bool true if the phrase has been assigned
     */

    private function link_phrase_by_name(string $phr_name, user_message $usr_msg): bool
    {
        global $db_con;

        $phr = new phrase($this->get_user());
        if ($db_con->is_open()) {
            if ($phr->load_by_name($phr_name)) {
                $this->link_phrase_and_save($phr, $usr_msg);
            }
        }
        return $usr_msg->is_ok();
    }

    /**
     * unlink this formula from a word or triple
     * @param phrase $phr with at least the id of a phrase that exists already in the database
     * @param user_message $usr_msg to collect the problems to be able to present solutions to the user
     * @return bool true if the link has been removed
     */
    function unlink_phrase(phrase $phr, user_message $usr_msg): bool
    {
        $usr_msg = new user_message();
        if ($this->get_user() != null) {
            log_debug($this->dsp_id() . ' from ' . $phr->dsp_id() . ' for user ' . $this->get_user()->dsp_id());
            $frm_lnk = new formula_link($this->get_user());
            if ($frm_lnk->load_by_link($this, $phr)) {
                $frm_lnk->del($usr_msg);
            } else {
                $msg = 'formula ' . $this->name() . ' is not linked to ' . $phr->name() . ' so link cannot be deactivated';
                $usr_msg->add_message_text($msg);
            }
        } else {
            $msg = 'Cannot unlink formula, phrase is not set.';
            $usr_msg->add_message_text($msg);
            log_err($msg, 'unlink_phrase');
        }
        return $usr_msg->is_ok();
    }

    function save_links(user_message $usr_msg): void
    {
        if ($this->lnk_lst != null) {
            foreach ($this->lnk_lst->lst() as $lnk) {
                if ($lnk->db_ready($usr_msg)) {
                    $lnk->save($usr_msg);
                }
            }
        }
    }

    /**
     * assign the formula to the words and triple
     *
     * @param user_message $usr_msg to enrich with messages
     * @return bool true if all phrases have been assigned
     */
    function assign_phrases(user_message $usr_msg = new user_message()): bool
    {
        $phr_lst = $this->phr_lst;
        if ($phr_lst != null) {
            if (!$phr_lst->is_empty()) {
                if ($phr_lst->save($usr_msg)) {
                    foreach ($phr_lst as $phr) {
                        $this->link_phrase($phr, $usr_msg);
                    }
                    if ($usr_msg->is_ok()) {
                        $this->save_links($usr_msg);
                    }
                }
            }
        }
        return $usr_msg->is_ok();
    }



    /*
     * save
     */

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
        $db_con->usr_id = $this->get_user()->id();
        $change_user_id = $db_con->get1($sql);
        if ($change_user_id > 0) {
          $result = false;
        } */
        return $this->not_changed();
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

        $lib = new library();
        if ($this->id() == 0) {
            log_err('The id must be set to check if the formula has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con->sql_creator());
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                if ($db_row[user_db::FLD_ID] > 0) {
                    $result = false;
                }
            }
        }
        log_debug('->not_changed for ' . $this->id() . ' is ' . $lib->dsp_bool($result));
        return $result;
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     *                 to check if the formula has been changed
     */
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $lib = new library();
        $sc->set_class($lib->class_to_name($this::class));
        return $sc->load_sql_not_changed($this->id(), $this->owner_id());
    }

    /**
     * create an SQL statement to retrieve all user-specific changes of this formula
     * TODO combine with load_sql_user_changes ?
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_user_changes_frm(sql_db $db_con): sql_par
    {
        $lib = new library();
        $class = $lib->class_to_name($this::class);
        $db_con->set_class(formula::class, true);
        $qp = new sql_par($class);
        $qp->name = $class . '_user_sandbox';
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->get_user()->id());
        $db_con->set_fields(array_merge(array(user_db::FLD_ID), formula_db::FLD_NAMES_USR, formula_db::FLD_NAMES_NUM_USR));
        $db_con->add_par(sql_par_type::INT, strval($this->id()));
        $qp->sql = $db_con->select_by_field(formula_db::FLD_ID);
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current formula
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation e.g. standard for values and results
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_user_changes(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, new sql_type_list([sql_type::USER]));
        $sc->set_fields(array_merge(
            formula_db::FLD_NAMES_USR,
            formula_db::FLD_NAMES_NUM_USR
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
        $msg_failed = $this->id() . ' failed for ' . $this->get_user()->name;
        $msg = '';

        $db_con->set_class(element::class);
        try {
            $msg = $db_con->delete_old(
                array(formula_db::FLD_ID, user_db::FLD_ID),
                array($this->id(), $this->get_user()->id()));
        } catch (Exception $e) {
            log_err($action . ' elements ' . $msg_failed . ' because ' . $e);
        }
        if ($msg != '') {
            log_err($action . ' elements ' . $msg_failed . ' because ' . $msg);
        } else {
            $db_con->set_class(formula::class, true);
            try {
                $msg = $db_con->delete_old(
                    array(formula_db::FLD_ID, user_db::FLD_ID),
                    array($this->id(), $this->get_user()->id()));
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

    private
    function is_term_the_same(term $trm): bool
    {
        global $sys;

        $result = false;
        if ($trm->type() == formula::class) {
            //$result = $trm;
            $result = true;
        } elseif ($trm->type() == word::class) {
            if ($trm->obj() == null) {
                log_warning('The object of the term has been expected to be loaded');
            } else {
                if ($trm->obj()->type_id == $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK)) {
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
     * @param sandbox $db_rec the database record before the saving
     * @param user_message $msg a message for the user what should be changed if something failed
     * @return bool true if everything has been fine
     */
    function delete_old_key_row(
        sandbox      $db_rec,
        user_message $msg
    ): bool
    {
        log_debug('->save_id_if_updated has name changed from "' . $db_rec->name() . '" to ' . $this->dsp_id());

        // if the name has changed, check if word, verb or formula with the same name already exists
        // this should have been checked by the calling function, so display the error message directly if it happens
        if ($db_rec->name() <> $this->name()) {
            // check if a word, triple or verb with the same name is already in the database
            $trm = $this->get_term();
            if ($trm->id_obj() > 0 and !$this->is_term_the_same($trm)) {
                $msg->merge($trm->id_used_msg($this));
                log_debug('->save_id_if_updated name "' . $trm->name() . '" used already as "' . $trm->type() . '"');
            } else {

                // check if target formula name already exists
                log_debug('->save_id_if_updated check if target formula already exists ' . $this->dsp_id() . ' (has been ' . $db_rec->dsp_id() . ')');
                $db_chk = $this->clone_reset();
                $db_chk->load_standard_by_name($this->name(), $msg);
                if ($db_chk->id() > 0) {
                    log_debug('->save_id_if_updated target formula name already exists ' . $db_chk->dsp_id());
                    if (def::UI_CAN_CHANGE_FORMULA_NAME) {
                        // ... if yes request to delete or exclude the record with the id parameters before the change
                        $to_del = clone $db_rec;
                        $to_del->del($msg);
                        // ... and use it for the update
                        $this->id = $db_chk->id();
                        $this->set_owner_id($db_chk->owner_id());
                        // force including again
                        $this->include();
                        $db_rec->exclude();
                    } else {
                        $msg->add(msg_id::COMPONENT_ALREADY_EXISTS, [msg_id::VAR_COMPONENT_NAME => $this->name()]);
                    }
                } else {
                    // the formula can be renamed (either for this user or for all users)
                    log_debug('->save_id_if_updated target formula name does not yet exists ' . $db_chk->dsp_id());
                    if (!$this->can_change() and $this->not_used()) {
                        $to_del = clone $db_rec;
                        if (!$this->not_used()) {
                            // if the target link has not yet been created
                            // ... request to delete the old
                            $to_del->del($msg);
                            // ... and create a deletion request for all users ???

                            // ... and create a new display component link
                            $this->id = 0;
                            $this->set_owner_id($this->get_user()->id);
                            // TODO check the usr_msg values and if the id is needed
                            $this->add($msg);
                            log_debug('->save_id_if_updated recreate the display component link del "'
                                . $db_rec->dsp_id() . '" add ' . $this->dsp_id());
                        } else {
                            $to_del->exclude();
                            if (!$to_del->save($msg)) {
                                $msg->add(msg_id::FAILED_TO_EXCLUDE_UNUSED, [
                                    msg_id::VAR_CLASS_NAME => $this::class
                                ]);
                            }
                        }
                    }
                }
            }
        }

        log_debug('->save_id_if_updated for ' . $this->dsp_id() . ' has been done');
        return $msg->is_ok();
    }

    /**
     * create a new formula
     * the user sandbox function is overwritten because the formula text should never be null
     * and the corresponding formula word is created
     * @param user_message $msg with status ok
     *                              or if something went wrong
     *                              the message that should be shown to the user
     *                              including suggested solutions
     * @return bool true if everything has been fine
     */
    function add(user_message $msg): bool
    {
        log_debug($this->dsp_id());

        global $db_con;

        // convert the formula text to db format (any error messages should have been returned from the calling user script)
        $this->generate_ref_text(null, $msg);

        $sc = $db_con->sql_creator();
        $qp = $this->sql_insert($sc, $msg, new sql_type_list([sql_type::LOG]));
        if ($msg->is_ok()) {
            if ($db_con->insert($qp, 'add and log ' . $this->dsp_id(), $msg)) {
                $this->id = $msg->get_row_id();
            }
        }

        if ($this->id() > 0) {
            // create the related formula word
            // the creation of a formula word should not be needed if on creation a view of word, phrase, verb nad formula is used to check uniqueness
            // the creation of the formula word is switched off because the term loading should be fine now
            // TODO check and remove the create_wrd function and the phrase_type_shared::FORMULA_LINK
            if ($this->wrd_add($msg)) {

                // create an empty db_frm element to force saving of all set fields
                $db_rec = new formula($this->get_user());
                $db_rec->set_name($this->name());
                $std_rec = clone $db_rec;
                // save the formula fields
                $this->save_fields_func($db_con, $db_rec, $std_rec, $msg);
            }
        } else {
            $msg->add(msg_id::FAILED_ADD_FORMULA, [msg_id::VAR_NAME => $this->name]);
        }

        return $msg->is_ok();
    }

    /**
     * dummy function that is supposed to be overwritten by the child class formula
     *
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @param user_message $msg to enrich with problems and suggested solution
     * @return bool true if the update of the reference text was successful and otherwise the error message is added to the user_message object
     */
    function generate_ref_text(
        ?term_list   $trm_lst = null,
        user_message $msg = new user_message()
    ): bool
    {
        $msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'generate_ref_text',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $msg->is_ok();
    }


    /*
     * save helper
     */

    /**
     * save all updated fields with one sql function
     * similar to the sandbox_multi save_fields_func function but for only one table
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param sandbox $db_obj the database record before saving the changes whereas $this is the record with the changes
     * @param sandbox $norm_obj the database record defined as standard because it is used by most users
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @return bool true if everything has been fine
     */
    function save_fields_func(
        sql_db         $db_con,
        sandbox        $db_obj,
        sandbox        $norm_obj,
        user_message   $usr_msg,
        ?sql_type_list $sc_par_lst = null
    ): bool
    {
        if ($db_obj->name() <> $this->name()) {
            log_debug('->save_id_fields to ' . $this->dsp_id() . ' from ' . $db_obj->dsp_id()
                . ' (standard ' . $norm_obj->dsp_id() . ')');
            // in case a word link exist, change also the name of the word
            if (!$this->wrd_rename($db_obj->name(), $usr_msg)) {
                $usr_msg->add(msg_id::FORMULA_WORD_RENAME_FAILED, [
                    msg_id::VAR_FORMULA => $db_obj->name(),
                    msg_id::VAR_NAME => $this->name()
                ]);
            }
        }

        if ($usr_msg->is_ok()) {
            return parent::save_fields_func($db_con, $db_obj, $norm_obj, $usr_msg, $sc_par_lst);
        } else {
            return false;
        }
    }

    /**
     * update the database references to the formula elements
     * to be able to use the sql statements to find all formulas depending on a word. triple, verb or formula
     *
     * @param user_message $usr_msg to collect problems and suggested solutions for the user
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return bool true if the update has been fine
     */
    function element_refresh(user_message $usr_msg, ?term_list $trm_lst = null): bool
    {
        $imp = new import();

        $frm_usr_msg = $usr_msg->clone_reset();
        $trm_lst = $this->load_missing_terms($frm_usr_msg, $trm_lst);

        // get the target list of elements that should be linked to the formula
        $elm_lst = $this->elements_incl_result_phrases($usr_msg, $trm_lst);

        // read the existing elements from the database
        $db_lst = $this->load_element_list();

        // add the missing links
        $add_lst = $elm_lst->diff($db_lst);
        $add_lst->db_insert_no_log($usr_msg, $imp, element::class);

        // delete links not needed any more
        $del_lst = $db_lst->diff($elm_lst);
        $del_lst->db_delete_no_log($usr_msg, $imp, element::class);

        return $usr_msg->is_ok();
    }

    /**
     * delete all elements related to this formula e.g. if the formula is supposed to be deleted
     * @param user_message $usr_msg to collect any error message for the requesting user
     * @return bool true is alle elements related to the formula have been deleted
     */
    function delete_elements(user_message $usr_msg): bool
    {
        $imp = new import();
        $lst = $this->load_element_list();
        $lst->db_delete_no_log($usr_msg, $imp, element::class);
        return $usr_msg->is_ok();
    }

    /**
     * @return element_list with the element linked to this formula according to the database
     */
    function load_element_list(): element_list
    {
        $db_lst = new element_list($this->get_user());
        $db_lst->load_by_frm($this->id());
        return $db_lst;
    }

    /**
     * get the list of elements used in this formula
     *
     * @param user_message $usr_msg to collect the error messages e.g. missing terms
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return element_list the list of elements used in this formula
     */
    function elements(user_message $usr_msg, ?term_list $trm_lst = null): element_list
    {
        $exp = $this->expression($trm_lst);
        return $exp->element_list($usr_msg, $trm_lst);
    }

    /**
     * get an element list with all formula elements
     * plus the phrases that should be added to the result as elements
     *
     * @param user_message $usr_msg to collect the error messages e.g. missing terms
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return element_list the list of elements used in this formula
     */
    function elements_incl_result_phrases(user_message $usr_msg, ?term_list $trm_lst = null): element_list
    {
        $exp = $this->expression($trm_lst);
        return $exp->elements_incl_result_phrases($usr_msg, $trm_lst);
    }

    /**
     * create the corresponding name word object for the formula name
     * @return word with the name of the formula
     */
    function formula_word(): word
    {
        global $sys;

        // if the formula word is missing, try a word creating as a kind of auto recovery
        $name_wrd = new word($this->get_user());
        $name_wrd->set_name($this->name());
        $name_wrd->type_id = $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK);
        return $name_wrd;
    }


    /**
     * add the corresponding name word for the formula name to the database
     * @return bool true if adding the word has been successful
     */
    function wrd_add(user_message $usr_msg): bool
    {
        log_debug('formula wrd_add for ' . $this->dsp_id());

        // if the formula word is missing, try a word creating as a kind of auto recovery
        $name_wrd = $this->formula_word();
        $name_wrd->save($usr_msg);
        if ($name_wrd->id > 0) {
            $this->name_wrd = $name_wrd;
        } else {
            log_err('Word with the formula name "' . $this->name() . '" missing for id ' . $this->id() . '.', 'formula->create_wrd');
        }
        return $usr_msg->is_ok();
    }

    /**
     * rename the corresponding name word if the formula is renamed
     * @return bool true if renaming the word has been successful
     */
    function wrd_rename(string $old_name, user_message $usr_msg): bool
    {
        log_debug('formula wrd_rename for ' . $this->dsp_id() . ' from ' . $old_name);

        $wrd = new word($this->get_user());
        $wrd->load_by_name($old_name);
        if (!$wrd->is_loaded()) {
            log_err('reloading of the word related to formula ' . $this->dsp_id() . ' failed');
        } else {
            if ($wrd->type_code_id() != phrase_type_shared::FORMULA_LINK) {
                log_err('reloading formula word ' . $wrd->dsp_id() . ' ist not of type ' . phrase_type_shared::FORMULA_LINK);
            } else {
                $wrd->set_name($this->name());
                $wrd->save($usr_msg);
            }
        }
        return $usr_msg->is_ok();
    }

    /**
     * remove the corresponding name word if the formula is deleted
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if deleting the word has been successful
     */
    function wrd_del(user_message $usr_msg): bool
    {
        log_debug('formula wrd_del for ' . $this->dsp_id());

        $wrd = new word($this->get_user());
        $wrd->load_by_name($this->name());
        if (!$wrd->is_loaded()) {
            log_warning('reloading of the word related to formula ' . $this->dsp_id() . ' failed');
        } else {
            if ($wrd->type_code_id() != phrase_type_shared::FORMULA_LINK) {
                log_err('reloading formula word ' . $wrd->dsp_id() . ' ist not of type ' . phrase_type_shared::FORMULA_LINK);
            } else {
                $wrd->del($usr_msg);
            }
        }
        return $usr_msg->is_ok();
    }

    /**
     * add the corresponding name word for the formula name to the database without similar check
     * this should only be used to fix internal errors
     */
    function wrd_add_fix(user_message $usr_msg): bool
    {
        global $sys;

        log_err('The formula word for ' . $this->dsp_id() . ' needs to be recreated to fix an internal error');

        // if the formula word is missing, try a word creating as a kind of auto recovery
        $name_wrd = new word($this->get_user());
        $name_wrd->name = $this->name();
        $name_wrd->type_id = $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK);
        $name_wrd->add($usr_msg);
        if ($name_wrd->id() > 0) {
            //zu_info('Word with the formula name "'.$this->name().'" has been missing for id '.$this->id.'.','formula->calc');
            $this->name_wrd = $name_wrd;
        } else {
            log_err('Word with the formula name "' . $this->name() . '" missing for id ' . $this->id() . '.', 'formula->create_wrd');
        }
        return $usr_msg->is_ok();
    }

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
     * TODO make sure that only user-specific data is deleted
     *
     * @param user_message $usr_msg the message for the user why deleting the formula links has failed and a suggested solution
     * @return bool true if the formula links has been deleted
     */
    function del_links(user_message $usr_msg): bool
    {
        global $db_con;

        $usr_msg_del = new user_message();

        $frm_lnk_lst = new formula_link_list($this->get_user());
        if ($frm_lnk_lst->load_by_frm_id($this->id())) {
            $msg = $frm_lnk_lst->del_without_log();
            $usr_msg_del->add_message_text($msg);
        }

        // and the corresponding formula elements
        if ($usr_msg_del->is_ok()) {
            $elm_lst = new element_list($this->get_user());
            $elm_lst->load_by_frm($this->id());
            // TODO add del function with test
            //$usr_msg->add($elm_lst->del_without_log());

            // TODO Prio 0 use element list delete function
            $db_con->set_class(element::class);
            $db_con->set_usr($this->get_user()->id);
            $msg = $db_con->delete_old($this->id_field(), $this->id());
            $usr_msg_del->add_message_text($msg);
        }

        // and the corresponding results
        if ($usr_msg_del->is_ok()) {
            $imp = new import();
            $res_lst = new result_list($this->get_user());
            $res_lst->load_by_frm($this);
            $res_lst->db_delete_no_log($usr_msg, $imp, result::class);
            $usr_msg_del->merge($usr_msg);
        }

        // and the corresponding word if possible
        if ($usr_msg_del->is_ok()) {
            $this->wrd_del($usr_msg_del);
        }

        $usr_msg->merge($usr_msg_del);

        return $usr_msg->is_ok();
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
                formula_db::FLD_TYPE,
                formula_db::FLD_FORMULA_TEXT,
                formula_db::FLD_FORMULA_USER_TEXT,
                formula_db::FLD_ALL_NEEDED,
                formula_db::FLD_LAST_UPDATE,
                formula_db::FLD_VIEW,
                sql_db::FLD_IMPACT
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     * the last_update field is excluded here because this is an internal only field
     *
     * @param formula|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        formula|db_object_seq_id $obj,
        user_message             $msg,
        sql_type_list            $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->type_id() !== $this->type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula_db::FLD_TYPE,
                    $sys->typ_lst->cng_fld->id($table_id . formula_db::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                formula_db::FLD_TYPE,
                $this->type_id(),
                formula_db::FLD_TYPE_SQL_TYP,
                $obj->type_id()
            );
        }
        // TODO Prio 2 check why reserving the formula name without expression is a useful feature
        if ($this->ref_text == null and !$sc_par_lst->is_delete()) {
            $msg->add(msg_id::MANDATORY_FIELD_NAME_MISSING, [
                msg_id::VAR_NAME => $this->dsp_id()
            ]);
        }
        if ($obj->ref_text !== $this->ref_text) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula_db::FLD_FORMULA_TEXT,
                    $sys->typ_lst->cng_fld->id($table_id . formula_db::FLD_FORMULA_TEXT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                formula_db::FLD_FORMULA_TEXT,
                $this->ref_text,
                formula_db::FLD_FORMULA_TEXT_SQL_TYP,
                $obj->ref_text
            );
        }
        if ($obj->usr_text !== $this->usr_text) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula_db::FLD_FORMULA_USER_TEXT,
                    $sys->typ_lst->cng_fld->id($table_id . formula_db::FLD_FORMULA_USER_TEXT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                formula_db::FLD_FORMULA_USER_TEXT,
                $this->usr_text,
                formula_db::FLD_FORMULA_USER_TEXT_SQL_TYP,
                $obj->usr_text
            );
        }
        if ($obj->need_all_val !== $this->need_all_val) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula_db::FLD_ALL_NEEDED,
                    $sys->typ_lst->cng_fld->id($table_id . formula_db::FLD_ALL_NEEDED),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                formula_db::FLD_ALL_NEEDED,
                $this->need_all_val,
                formula_db::FLD_ALL_NEEDED_SQL_TYP,
                $obj->need_all_val
            );
        }
        if ($obj->ref_text !== $this->ref_text
            or $obj->type_id() <> $this->type_id()
            or $obj->need_all_val <> $this->need_all_val
            or $this->last_update == null) {
            $lst->add_field(
                formula_db::FLD_LAST_UPDATE,
                sql::NOW,
                sql_field_type::TIME
            );
        }
        if ($obj->get_view_id() !== $this->get_view_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . formula_db::FLD_VIEW,
                    $sys->typ_lst->cng_fld->id($table_id . formula_db::FLD_VIEW),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                formula_db::FLD_VIEW,
                view_db::FLD_NAME,
                $this->view,
                $obj->view
            );
        }
        if ($obj->impact !== $this->impact) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_IMPACT,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_IMPACT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_IMPACT,
                $this->impact,
                sql_db::FLD_IMPACT_SQL_TYP,
                $obj->impact
            );
        }
        return $lst->merge($this->db_changed_sandbox_list($obj, $sc_par_lst));
    }

}