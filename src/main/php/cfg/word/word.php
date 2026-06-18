<?php

/*

    model/word/word.php - the main word object
    -----------------

    $wrd is the suggested var name

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
    - construct and map: set the vars of this word object to the initial value or based on a db row, api or import object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export array or write the imported object to the database
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - load:              database access object (DAO) functions
    - cast:              create an api object and set the vars from an api json
    - convert:           convert this word e.g. phrase or term
    - sql fields:        field names for sql
    - related:           load related objects assigned to this word from the database
    - modify:            change potentially all variables of this word object
    - info:              functions to make code easier to read
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

namespace Zukunft\ZukunftCom\main\php\cfg\word;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::SHARED_CONST . 'def.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_FORMULA . 'formula_link.php';
include_once paths::MODEL_FORMULA . 'formula_list.php';
include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_LOG . 'change_log_list.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'ref_list.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_code_id.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::MODEL_VERB . 'verb_db.php';
include_once paths::MODEL_VERB . 'verb_list.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_db.php';
include_once paths::MODEL_VIEW . 'view_list.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\combine_named;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_code_id;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_db;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_db;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\def as def_shared;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;

class word extends sandbox_code_id
{

    /*
     * db const
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'for a short text, that can be used to search for values or results with a 64 bit database key because humans will never be able to use more than a few million words';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = word_db::FLD_ID;
    const array FLD_LST_MUST_BE_IN_STD = word_db::FLD_LST_MUST_BE_IN_STD;
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = word_db::FLD_LST_MUST_BUT_USER_CAN_CHANGE;
    const array FLD_LST_USER_CAN_CHANGE = word_db::FLD_LST_USER_CAN_CHANGE;
    const array FLD_LST_NON_CHANGEABLE = word_db::FLD_LST_NON_CHANGEABLE;
    const array FLD_NAMES = word_db::FLD_NAMES;
    const array FLD_NAMES_USR = word_db::FLD_NAMES_USR;
    const array FLD_NAMES_NUM_USR = word_db::FLD_NAMES_NUM_USR;
    const array ALL_SANDBOX_FLD_NAMES = word_db::ALL_SANDBOX_FLD_NAMES;

    /*
     * object vars
     */

    // database fields additional to the user sandbox fields
    // the english plural name as a kind of shortcut; if plural is NULL the database value should not be updated
    public ?string $plural {
        set {
            $this->plural = $value;
            $this->set_modified();
        }
    }

    // the importance of the word; a higher number indicates a higher relevance
    // based on the value defined for each word by the words "impact" and "criteria".
    // set the cache value to sort this word by relevance
    public ?float $impact {
        get {
            // TODO Prio 2 calculate impact from criteria if useful or requested
            return $this->impact;
        }
        /**
         * set the cache value to sort this word by relevance
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

    // the phrases connected to this word by a triple (this word is the from or the to
    // of each triple — direction is "both"); populated lazily by load_phrases_related()
    // and only emitted via api_json_array() when the api_types::INCL_RELATED flag is
    // set on the caller's api_type_list; each entry is a phrase wrapping the connecting
    // triple, so the frontend renderer can display the triple's "other end" word as the
    // link label and the triple itself as the link target — e.g. for the word "Zurich"
    // the entries are the triples "city of Zurich", "canton of Zurich", "Zurich Insurance";
    // the per-verb count is bounded by the related-per-verb config so the list stays compact
    public ?phrase_list $phrases_related = null;

    // the values related to this word (this word is one of the phrases of the value's group);
    // populated lazily by load_values_related() and only emitted via api_json_array() when the
    // api_types::INCL_RELATED flag is set, so the default word view can show e.g. for "Zurich"
    // the inhabitant numbers; the frontend caps and sorts the list by impact when rendering
    public ?value_list $values_related = null;

    // the formulas related to this word (this word is one of the formula's terms);
    // populated lazily by load_formulas_related() and only emitted via api_json_array()
    // when the api_types::INCL_RELATED flag is set, so the default word view can show the
    // formulas using the word; the frontend caps and sorts the list by impact when rendering
    public ?formula_list $formulas_related = null;

    // the external references of this word (e.g. its wikidata or wikipedia link);
    // populated lazily by load_references_related() and only emitted via api_json_array()
    // when the api_types::INCL_RELATED flag is set, so the default word view can show the
    // references; refs have no impact, so the frontend keeps the database (id) order
    public ?ref_list $references_related = null;

    // the most recent change log entries of this word;
    // populated lazily by load_changes_related() and only emitted via api_json_array()
    // when the api_types::INCL_RELATED flag is set, so the default word view can show the
    // recent changes; the change log is ordered by time, latest first
    public ?change_log_list $changes_related = null;

    // the views that can show this word: its own default view plus the default views of
    // its parent words; populated lazily by load_views_related() and only emitted via
    // api_json_array() when the api_types::INCL_RELATED flag is set
    public ?view_list $views_related = null;

    // in memory only fields
    public ?int $link_type_id; // used in the word list to know based on which relation the word was added to the list

    // only used for the export object
    public ?view $view {
        set(?view $value) {
            $this->view = $value;
        }
    } // name of the default view for this word
    public ?array $ref_lst = [];


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

        $this->rename_can_switch = def::UI_CAN_CHANGE_WORD_NAME;
    }

    /**
     * clear the word object values
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->plural = null;
        $this->impact = null;

        $this->link_type_id = null;

        $this->view = null;
        $this->ref_lst = [];
    }

    /**
     * map the database fields to this word object fields
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
        string $id_fld = word_db::FLD_ID,
        string $name_fld = word_db::FLD_NAME,
        string $type_fld = phrase::FLD_TYPE): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        if ($result) {
            if (array_key_exists(word_db::FLD_PLURAL, $db_row)) {
                $this->plural = $db_row[word_db::FLD_PLURAL];
            }
            if (array_key_exists(word_db::FLD_VIEW, $db_row)) {
                if ($db_row[word_db::FLD_VIEW] != null) {
                    $this->set_view_id($db_row[word_db::FLD_VIEW]);
                }
            }
            if (array_key_exists(sql_db::FLD_IMPACT, $db_row)) {
                $this->impact = $db_row[sql_db::FLD_IMPACT];
            }
        }
        return $result;
    }

    /**
     * map a word api json to this model word object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * TODO add a test case to check if an import of a pure name overwrites the existing type setting
     *      or if loading later adding a word with admin_protection and type does not overwrite the type and protection
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        // it is expected that the code id is set via import by an admin not via api

        // TODO move plural to language forms
        if (array_key_exists(json_fields::PLURAL, $api_json)) {
            if ($api_json[json_fields::PLURAL] <> '') {
                $this->plural = $api_json[json_fields::PLURAL];
            }
        }

        if (array_key_exists(sql_db::FLD_IMPACT, $api_json)) {
            $this->impact = $api_json[sql_db::FLD_IMPACT];
        }

        if (array_key_exists(json_fields::VIEW, $api_json)) {
            $msk = new view($this->get_user());
            $id = $api_json[json_fields::VIEW];
            if ($id != 0) {
                $msk->id = $id;
                $this->view = $msk;
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * set the vars of this word object based on the given json without writing to the database
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions including the user who has initiated the import mainly used to add tge code id to the database
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
        global $db_con;

        // reset all parameters for the word object but keep the user
        $this->reset(true);

        // set the object vars based on the json
        parent::import_mapper($in_ex_json, $msg, $dto);

        if (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            $this->type_id = $sys->typ_lst->phr_typ->id($in_ex_json[json_fields::TYPE_NAME]);
        }
        if (key_exists(json_fields::PLURAL, $in_ex_json)) {
            if ($in_ex_json[json_fields::PLURAL] <> '') {
                $this->plural = $in_ex_json[json_fields::PLURAL];
            }
        }
        if (key_exists(json_fields::IMPACT, $in_ex_json)) {
            $this->impact = $in_ex_json[json_fields::IMPACT];
        }

        // remember the references
        if (key_exists(json_fields::REFS, $in_ex_json)) {
            if ($in_ex_json[json_fields::REFS] <> '') {
                $ref_json = $in_ex_json[json_fields::REFS];
                foreach ($ref_json as $ref_data) {
                    $ref_obj = new ref($this->get_user());
                    $ref_obj->set_phrase($this->phrase());
                    $ref_obj->import_mapper($ref_data, $msg, $dto);
                    // TODO $dto should never be null if no direct import is used
                    $dto?->add_reference($ref_obj);
                    if ($msg->is_ok()) {
                        $this->ref_lst[] = $ref_obj;
                    }
                }
            }
        }

        // TODO change to view object like in triple
        if (key_exists(json_fields::VIEW, $in_ex_json)) {
            $msk_name = $in_ex_json[json_fields::VIEW];
            $wrd_view = new view($this->get_user());
            if ($db_con->is_open()) {
                $wrd_view->load_by_name($msk_name);
                if ($wrd_view->id() == 0) {
                    $msg->add(msg_id::IMPORT_NOT_FIND_VIEW, [msg_id::VAR_ID => $this->dsp_id(), msg_id::VAR_NAME => $msk_name]);
                } else {
                    $this->set_view_id($wrd_view->id());
                }
            } else {
                $wrd_view->set_name($msk_name);
            }
            $this->view = $wrd_view;
        }

        // set the default type if no type is specified
        if ($this->type_id <= 0) {
            $this->type_id = $sys->typ_lst->phr_typ->default_id();
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
            if ($typ_lst->phrase_names()) {
                $vars[json_fields::ID] = $this->id();
                $vars[json_fields::NAME] = $this->name();
            } else {
                $vars = parent::api_json_array($typ_lst, $usr);
                $vars[json_fields::PLURAL] = $this->plural;
                $vars[json_fields::IMPACT] = $this->impact;
                // related data is keyed by the word's phrase id, so a fresh
                // word (id 0, e.g. the add form) has none to load
                if ($typ_lst->incl_related() and $this->id() != 0) {
                    if ($this->phrases_related == null and !$typ_lst->test_mode()) {
                        $this->load_phrases_related();
                    }
                    if ($this->phrases_related != null and !$this->phrases_related->is_empty()) {
                        // INCL_PHRASES so each related triple emits its from/verb/to phrases,
                        // not just id+name — the page-title renderer needs the link names
                        $vars[json_fields::PHRASES_RELATED] = $this->phrases_related->api_json_array(
                            new api_type_list([api_types::INCL_PHRASES]), $usr);
                    }
                    if ($this->values_related == null and !$typ_lst->test_mode()) {
                        $this->load_values_related();
                    }
                    if ($this->values_related != null and !$this->values_related->is_empty()) {
                        // INCL_PHRASES so each value carries its group phrases, which the
                        // frontend needs for the value name and to sort the list by impact
                        $vars[json_fields::VALUES] = $this->values_related->api_json_array(
                            new api_type_list([api_types::INCL_PHRASES]), $usr);
                    }
                    if ($this->formulas_related == null and !$typ_lst->test_mode()) {
                        $this->load_formulas_related();
                    }
                    if ($this->formulas_related != null and !$this->formulas_related->is_empty()) {
                        // a fresh api_type_list (no INCL_RELATED) so the formulas emit only
                        // their own name, id and impact, which the frontend needs to render
                        // and sort the list by impact, without recursing back into relations
                        $vars[json_fields::FORMULAS] = $this->formulas_related->api_json_array(
                            new api_type_list(), $usr);
                    }
                    if ($this->references_related == null and !$typ_lst->test_mode()) {
                        $this->load_references_related();
                    }
                    if ($this->references_related != null and !$this->references_related->is_empty()) {
                        $vars[json_fields::REFERENCES] = $this->references_related->api_json_array(
                            new api_type_list(), $usr);
                    }
                    if ($this->changes_related == null and !$typ_lst->test_mode()) {
                        $this->load_changes_related();
                    }
                    if ($this->changes_related != null and !$this->changes_related->is_empty()) {
                        $vars[json_fields::CHANGES] = $this->changes_related->api_json_array(
                            new api_type_list(), $usr);
                    }
                    if ($this->views_related == null and !$typ_lst->test_mode()) {
                        $this->load_views_related();
                    }
                    if ($this->views_related != null and !$this->views_related->is_empty()) {
                        $vars[json_fields::VIEWS] = $this->views_related->api_json_array(
                            new api_type_list(), $usr);
                    }
                }
            }
        } elseif ($this->is_excluded() and $typ_lst->with_excluded_id()) {
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
            $vars[json_fields::IMPACT] = $this->impact;
        }

        return $vars;
    }

    /**
     * load the values related to this word into the in-memory values_related list
     * so that api_json_array() can emit them under the INCL_RELATED flag
     */
    function load_values_related(): void
    {
        $this->values_related = $this->reload_value_list();
    }

    /**
     * load the formulas related to this word into the in-memory formulas_related list
     * so that api_json_array() can emit them under the INCL_RELATED flag
     */
    function load_formulas_related(): void
    {
        $frm_lst = new formula_list($this->get_user());
        $frm_lst->load_by_phr($this->phrase());
        $this->formulas_related = $frm_lst;
    }

    /**
     * load the external references of this word into the in-memory references_related list
     * so that api_json_array() can emit them under the INCL_RELATED flag
     */
    function load_references_related(): void
    {
        $ref_lst = new ref_list($this->get_user());
        $ref_lst->load_by_phr_id($this->phrase()->id());
        $this->references_related = $ref_lst;
    }

    /**
     * load the most recent change log entries of this word into the in-memory
     * changes_related list so that api_json_array() can emit them under the INCL_RELATED flag
     */
    function load_changes_related(): void
    {
        $chg_lst = new change_log_list();
        $chg_lst->load_obj_last($this, $this->get_user());
        $this->changes_related = $chg_lst;
    }

    /**
     * load the views related to this word into the in-memory views_related list so that
     * api_json_array() can emit them under the INCL_RELATED flag; the list is the word's own
     * default view plus the default views of its parent words (the phrases it "is a")
     * each view is loaded by id because set_view_id() only stores the id, not the name that
     * the api export and the frontend name_link need
     * TODO the parent loop loads each parent word and its view one by one; replace with a
     *      single list load once a view_list->load_by_word_list() exists
     */
    function load_views_related(): void
    {
        $msk_lst = new view_list($this->get_user());
        $this->add_default_view_to($msk_lst);
        foreach ($this->parents()->lst() as $phr) {
            if ($phr->is_word()) {
                $par_wrd = new word($this->get_user());
                $par_wrd->load_by_id($phr->id());
                $par_wrd->add_default_view_to($msk_lst);
            }
        }
        $this->views_related = $msk_lst;
    }

    /**
     * add the fully loaded default view of this word to the given list (skipping duplicates and
     * words without a default view); the view is loaded by id so that it carries its name
     * @param view_list $msk_lst the list the default view is added to
     */
    private function add_default_view_to(view_list $msk_lst): void
    {
        if ($this->view != null and $this->view->id() > 0) {
            $msk = new view($this->get_user());
            $msk->load_by_id($this->view->id());
            $msk_lst->add($msk);
        }
    }

    /**
     * load the phrases related to this word via a triple
     *
     * @param int $per_verb_limit upper bound on triples kept per verb; the loader keeps one
     *                            extra row so the caller can detect overflow without a count
     */
    function load_phrases_related(int $per_verb_limit = def_shared::LIMIT_RELATED_PER_VERB): void
    {
        $trp_lst = new triple_list($this->get_user());
        $trp_lst->load_by_phr($this->phrase(), null, foaf_direction::BOTH);
        $this->phrases_related = $this->select_phrases_related($trp_lst, $per_verb_limit);
    }

    /**
     * select the most relevant phrases related to this word from the given triples
     * sorted with the highest impact first so that e.g. the stocks with the highest
     * market capitalisation are kept within the per verb limit and always shown in the same order
     *
     * @param triple_list $trp_lst the triples connected to this word
     * @param int $per_verb_limit upper bound on triples kept per verb; one extra row is kept
     *                            so the caller can detect overflow without a count
     * @return phrase_list the kept triples as phrases with the highest impact first
     */
    function select_phrases_related(triple_list $trp_lst, int $per_verb_limit): phrase_list
    {
        $trp_lst->sort_by_impact();
        $kept = new phrase_list($this->get_user());
        $per_verb_count = [];
        foreach ($trp_lst->lst() as $trp) {
            $vrb_id = $trp->get_verb()?->id() ?? 0;
            $seen = $per_verb_count[$vrb_id] ?? 0;
            // keep one extra row beyond the limit so the renderer can show a "more" indicator
            if ($seen <= $per_verb_limit) {
                $kept->add($trp->phrase());
                $per_verb_count[$vrb_id] = $seen + 1;
            }
        }
        return $kept;
    }

    /**
     * load a word by id and, in the same call, populate the related phrases and the related
     * values that the default word view expects (the page-title renderer's city, canton, ...
     * inline list, the "is symbol for <X>" symbol line and the related values list). Used by the default-word-view path —
     * test snapshot generation via test_base::assert_view and any other caller that wants
     * the rendered HTML to reflect a word's connecting triples without going through the
     * INCL_RELATED-gated api_json round-trip
     *
     * @param int $id the word id to load
     * @return int the id of the loaded word, or 0 if not found
     */
    function load_by_id_with_related(int $id): int
    {
        $loaded_id = parent::load_by_id($id);
        if ($loaded_id > 0) {
            $this->load_phrases_related();
            $this->load_values_related();
        }
        return $loaded_id;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        global $sys;

        $vars = parent::export_json($exp_typ, $do_load);

        if ($this->plural <> '') {
            $vars[json_fields::PLURAL] = $this->plural;
        }
        if ($this->type_id > 0) {
            if ($this->type_id == $sys->typ_lst->phr_typ->default_id()) {
                unset($vars[json_fields::TYPE_NAME]);
            }
        }

        if ($this->view != null) {
            if ($this->get_view_id() > 0 and $this->view->name() == '') {
                if ($do_load) {
                    $this->reload_view();
                }
            }
            if ($this->view->name() != '') {
                $vars[json_fields::VIEW] = $this->view->name();
            }
        }
        if (count($this->ref_lst) > 0) {
            $ref_lst = [];
            foreach ($this->ref_lst as $ref) {
                $ref_lst[] = $ref->export_json([]);
            }
            $vars[json_fields::REFS] = $ref_lst;
        }
        // the impact is part of the im- and export so that it round-trips
        if ($this->impact != null) {
            $vars[json_fields::IMPACT] = $this->impact;
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the phrase type of this word by the given code id or name
     *
     * @param string $code_id_or_name the code id or name that should be added to this word
     * @param user $usr_req the user who wants to change the type
     * @return user_message a warning if the view type code id is not found
     */
    function set_type(string $code_id_or_name, user $usr_req = new user()): user_message
    {
        global $sys;
        if ($sys->typ_lst->phr_typ->has_code_id($code_id_or_name)) {
            return parent::set_type_by_code_id(
                $code_id_or_name, $sys->typ_lst->phr_typ, msg_id::PHRASE_TYPE_NOT_FOUND, $usr_req);
        } else {
            return parent::set_type_by_name(
                $code_id_or_name, $sys->typ_lst->phr_typ, msg_id::PHRASE_TYPE_NOT_FOUND, $usr_req);
        }
    }

    function get_view(): ?view
    {
        return $this->reload_view();
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
     * @return int the id of the default view for this word or zero if no view is preferred
     */
    function get_view_id(): int
    {
        if ($this->view == null) {
            return 0;
        } else {
            return $this->view->id();
        }
    }


    /*
     * preloaded
     */

    /**
     * get the code_id of the phrase type
     * @return string|null the code_id of the phrase type of this word
     */
    function type_code_id(): string|null
    {
        global $sys;
        return $sys->typ_lst->phr_typ->code_id($this->type_id);
    }

    /**
     * get the name of the phrase type
     * @return string the name of the phrase type of this word
     */
    function type_name(): string
    {
        global $sys;
        return $sys->typ_lst->phr_typ->name($this->type_id);
    }

    /**
     * get the name of the word type or null if no type is set
     * @return string|null the name of the word type
     */
    function type_name_or_null(): ?string
    {
        global $sys;
        return $sys->typ_lst->phr_typ->name_or_null($this->type_id);
    }


    /*
     * cast
     */

    /**
     * @returns phrase the word object cast into a phrase object
     */
    function phrase(): phrase
    {
        $phr = new phrase($this->get_user());
        $phr->set_obj($this);
        log_debug($this->dsp_id());
        return $phr;
    }

    /**
     * helper function that returns a phrase list object just with the word object
     * @return phrase_list a new phrase list just with this word as an entry
     */
    function phrase_list(): phrase_list
    {
        $phr_lst = new phrase_list($this->get_user());
        $phr_lst->add($this->phrase());
        return $phr_lst;
    }

    /**
     * @returns term the word object cast into a term object
     */
    function term(): term
    {
        $trm = new term($this->get_user());
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


    /*
     * load sql
     */

    /**
     * create an SQL statement to retrieve a word by id from the database
     * added to word just to assign the class for the user sandbox object
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_formula_name(sql_creator $sc, string $name): sql_par
    {
        global $sys;
        $qp = parent::load_sql_usr_num($sc, $this, formula_db::FLD_NAME);
        $sc->add_where($this->name_field(), $name, sql_par_type::TEXT_USR);
        $sc->add_where(phrase::FLD_TYPE, $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK), sql_par_type::CONST);
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        // TODO check if and where it is needed to exclude the formula words
        // global $sys;
        // $qp = parent::load_sql_usr_num($sc, $this, $query_name);
        // $sc->add_where(phrase::FLD_TYPE, $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK), sql_par_type::CONST_NOT);
        // return $qp;
        return parent::load_sql_usr_num($sc, $this, $query_name);
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return word_db::FLD_NAME;
    }

    /**
     * @return array with all fields names of this word object
     */
    protected function all_fields(): array
    {
        return array_merge(
            word_db::FLD_NAMES,
            word_db::FLD_NAMES_USR,
            word_db::FLD_NAMES_NUM_USR,
            array(user_db::FLD_ID));
    }

    function all_sandbox_fields(): array
    {
        return word_db::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * related
     */

    /**
     * get a list of values related to this word
     * @param int $page the offset / page
     * @param int $size the number of values that should be returned
     * @return value_list a list object with the most relevant values related to this word
     */
    function reload_value_list(int $page = 1, int $size = sql_db::ROW_LIMIT): value_list
    {
        $val_lst = new value_list($this->get_user());
        $val_lst->load_by_phr($this->phrase(), $size, $page);
        // load the phrase names of each value group so that the related value list
        // shows the phrase names (and not only the links) in the api and frontend
        $val_lst->load_phrases();
        return $val_lst;
    }

    /**
     * if there is just one formula linked to the word, get it
     * TODO separate the query parameter creation and add a unit test
     * TODO allow also to retrieve a list of formulas
     * TODO get the user-specific list of formulas
     */
    function reload_formula(): formula
    {
        log_debug('for ' . $this->dsp_id() . ' and user "' . $this->get_user()->name . '"');

        global $db_con;

        $db_con->set_class(formula_link::class);
        $qp = new sql_par(self::class);
        $qp->name = 'word_formula_by_id';
        $db_con->set_name($qp->name);
        $db_con->set_link_fields(formula_db::FLD_ID, phrase::FLD_ID);
        $db_con->set_where_link_no_fld(0, 0, $this->id());
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();
        $db_row = $db_con->get1($qp);
        $frm = new formula($this->get_user());
        if ($db_row !== false) {
            if ($db_row[formula_db::FLD_ID] > 0) {
                $frm->load_by_id($db_row[formula_db::FLD_ID]);
            }
        }

        return $frm;
    }


    /*
     * info
     */

    /**
     * can merge id all unique keys match
     * check that the given object is by all unique keys the same as the actual object
     * handles the special case that for each formula a corresponding word is created (which needs to be checked if this is really needed)
     * so if a formula word "millions" is different from the standard word "millions" because the formula word "millions" is representing a formula which should not be combined
     * in short: if two objects are the same by this definition, they are supposed to be merged
     * @param word|combine_named|type_object|sandbox $obj_to_check the filled object that might be the same as this object
     * @return bool true if the given object is exactly the same as this object and the two objects can be merged
     */
    function is_same(word|combine_named|type_object|sandbox $obj_to_check): bool
    {
        global $sys;
        $result = parent::is_same($obj_to_check);
        // check the exception case that formula link words are similar to the formula, but are not the same
        if ($this->name() == $obj_to_check->name()) {
            if ($this->type_id !== $obj_to_check->type_id) {
                if ($this->type_id == $sys->typ_lst->phr_typ->id(phrase_types::FORMULA_LINK)
                    or $obj_to_check->type_id == $sys->typ_lst->phr_typ->id(phrase_types::FORMULA_LINK)) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * create human-readable messages of the differences between the word objects
     * TODO Prio 2 move to db_object_seq_id ?
     * @param word|CombineObject|db_object_seq_id $obj which might be different to this word
     * @return user_message the human-readable messages of the differences between the word objects
     */
    function diff_msg(word|CombineObject|db_object_seq_id $obj): user_message
    {
        $msg = parent::diff_msg($obj);
        $lib = new library();
        if ($this->id() != $obj->id()) {
            $msg->add(msg_id::DIFF_ID, [
                msg_id::VAR_ID => $obj->dsp_id(),
                msg_id::VAR_ID_CHK => $this->dsp_id(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_WORD_NAME => $this->dsp_id(),
            ]);
        }
        return $msg;
    }

    /**
     * check if the word in the database needs to be updated
     * * e.g. for import  if this word has only the name set, the protection should not be updated in the database
     * is expected to be similar to the diff_msg function
     *
     * @param word|CombineObject|IdObject $db_obj which might be different to this sandbox object
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update(word|CombineObject|IdObject $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        if ($this->plural != null) {
            if ($this->plural != $db_obj->plural) {
                $result = true;
            }
        }
        if ($this->impact != null) {
            if ($this->impact != $db_obj->impact) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @param string $type the ENUM string of the fixed type
     * @returns bool true if the word has the given type
     */
    function is_type(string $type): bool
    {
        global $sys;

        $result = false;
        if ($this->type_id == $sys->typ_lst->phr_typ->id($type)) {
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
     * @returns bool true if the word has the type "measure" (e.g. "metre" or "CHF")
     * in case of a division, these words are excluded from the result
     * in case of add, it is checked that the added value does not have a different measure
     */
    function is_measure(): bool
    {
        return $this->is_type(phrase_type_shared::MEASURE);
    }

    /**
     * @return bool true if the word has the type "information" (e.g. "1967 (year of definition)")
     * if used for a value these phrases are shown only as a tooltip
     */
    function is_info(): bool
    {
        return $this->is_type(phrase_types::INFO);
    }

    /**
     * @returns bool true if the word has one of the scaling types (e.g. "million" or "one"; "one" is a hidden scaling type)
     */
    function is_scaling(): bool
    {
        $result = false;
        foreach (phrase_type_shared::SCALING_TYPES as $scale_type) {
            if ($this->is_type($scale_type)) {
                $result = true;
            }
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


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param word|CombineObject|db_object_seq_id $std_obj the norm object as saved in the database
     * @param word|CombineObject|db_object_seq_id $result empty clone of the target user object
     * @return word|CombineObject|db_object_seq_id the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        word|CombineObject|db_object_seq_id $std_obj,
        word|CombineObject|db_object_seq_id $result
    ): word|CombineObject|db_object_seq_id
    {
        parent::delta($std_obj, $result);

        if ($std_obj->view !== $this->view) {
            $result->view = $this->view;
        }
        if ($std_obj->plural !== $this->plural) {
            $result->plural = $this->plural;
        }

        if ($std_obj->impact !== $this->impact) {
            $result->impact = $this->impact;
        }
        if ($std_obj->view !== $this->view) {
            $result->view = $this->view;
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * fill this word based on the given word
     * if the id is set in the given word loaded from the database, but this import word does not yet have the db id, set the id.
     * if the given description is not set (null). the description is not removed.
     * if the given description is an empty string. the description is removed.
     *
     * @param word|CombineObject|db_object_seq_id $obj word with the values that should have been updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(word|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($this->plural === null and $obj->plural != null) {
            $this->plural = $obj->plural;
        }
        if ($this->impact === null and $obj->impact != null) {
            $this->impact = $obj->impact;
        }
        if ($this->view === null and $obj->view != null) {
            $this->view = $obj->view;
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
     * returns a list of words (actually phrases) that are related to this word
     * e.g. for "Zurich" it will return "canton", "city" and "company", but not "Zurich" itself
     */
    function parents(): phrase_list
    {
        global $sys;
        log_debug('for ' . $this->dsp_id() . ' and user ' . $this->get_user()->id);
        $phr_lst = $this->phrase_list();
        $parent_phr_lst = $phr_lst->foaf_parents($sys->typ_lst->vrb->get_verb(verbs::IS));
        log_debug('are ' . $parent_phr_lst->dsp_name() . ' for ' . $this->dsp_id());
        return $parent_phr_lst;
    }

    /**
     * TODO maybe collect the single words or this is a third case
     * returns a list of words that are related to this word
     * e.g. for "Zurich" it will return "canton", "city" and "company" and "Zurich" itself
     *      to be able to collect all relations to the given word e.g. Zurich
     */
    function is_phrases(): phrase_list
    {
        $phr_lst = $this->parents();
        $phr_lst->add($this->phrase());
        log_debug($this->dsp_id() . ' is a ' . $phr_lst->dsp_name());
        return $phr_lst;
    }

    /**
     * returns the best guess category for a word  e.g. for "ABB" it will return only "company"
     */
    function is_mainly(): phrase
    {
        $result = null;
        $is_phr_lst = $this->is_phrases();
        if (!$is_phr_lst->is_empty()) {
            $result = $is_phr_lst->lst()[0];
        }
        log_debug($this->dsp_id() . ' is a ' . $result->name());
        return $result;
    }

    /**
     * add a child word to this word
     * e.g. Zurich (child) is a canton (Parent)
     * @param word $child the word that should be added as a child
     * @param user_message $usr_msg
     * @return bool
     */
    function add_child(word $child, user_message $usr_msg): bool
    {
        global $sys;

        $wrd_lst = $this->children();
        if (!$wrd_lst->does_contain($child)) {
            $wrd_lnk = new triple($this->get_user());
            $wrd_lnk->set_from($child->phrase());
            $wrd_lnk->set_verb($sys->typ_lst->vrb->get_verb(verbs::IS));
            $wrd_lnk->set_to($this->phrase());
            $wrd_lnk->save($usr_msg);
        }
        return $usr_msg->is_ok();
    }

    /**
     * get all phrases that are linked to this word with the "is a" verb
     * e.g. for "canton" it will return "Zurich (canton)" and others, but not "canton" itself
     *
     * @return phrase_list a list of words that are related to this word
     */
    function children(): phrase_list
    {
        global $sys;
        log_debug('for ' . $this->dsp_id() . ' and user ' . $this->get_user()->id);
        $phr_lst = $this->phrase_list();
        $child_phr_lst = $phr_lst->all_children($sys->typ_lst->vrb->get_verb(verbs::IS));
        log_debug('are ' . $child_phr_lst->name() . ' for ' . $this->dsp_id());
        return $child_phr_lst;
    }

    /**
     * return a list of upward related verbs e.g. 'is a' for Zurich because Zurich is a city
     */
    private
    function verb_list_up(): verb_list
    {
        return $this->link_types(foaf_direction::UP);
    }

    /**
     * return a list of downward related verbs e.g. 'contains' for mathematical constant because mathematical constant contains Pi
     */
    private
    function verb_list_down(): verb_list
    {
        return $this->link_types(foaf_direction::DOWN);
    }

    private
    function phrase_list_up(): phrase_list
    {
        $phr_lst = new phrase_list($this->get_user());
        return $phr_lst->parents();
    }

    private
    function phrase_list_down(): phrase_list
    {
        $phr_lst = new phrase_list($this->get_user());
        return $phr_lst->direct_children();
    }

    /**
     * get all phrases that are linked to this word with the "is a" verb including the parent word
     * e.g. for "canton" it will return "Zurich (canton)" and "canton", but not "Zurich (city)"
     * used to collect e.g. all formulas used for canton
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
     * e.g. for "Switzerland" it will return "Zurich (canton)" and "Zurich (city)" which is part of the canton
     */
    function parts(): phrase_list
    {
        global $sys;
        $phr_lst = $this->phrase_list();
        return $phr_lst->foaf_children($sys->typ_lst->vrb->get_verb(verbs::PART_NAME));
    }

    /**
     * returns the more general word as defined by "is part of"
     * e.g. for "Meilen (District)" it will return "Zürich (canton)"
     * for the value selection this should be tested level by level
     * to use by default the most specific value
     */
    function is_part(): phrase_list
    {
        global $sys;
        log_debug($this->dsp_id() . ', user ' . $this->get_user()->id);
        $phr_lst = $this->phrase_list();
        $is_phr_lst = $phr_lst->foaf_parents($sys->typ_lst->vrb->get_verb(verbs::PART_NAME));

        log_debug($this->dsp_id() . ' is a ' . $is_phr_lst->dsp_name());
        return $is_phr_lst;
    }

    /**
     * @return phrase_list a list of phrases that are 'part of'/'contain' this phrase
     * e.g. for "Switzerland" it will return "Zurich (canton)" but not "Zurich (city)"
     */
    function direct_parts(): phrase_list
    {
        global $sys;
        $phr_lst = $this->phrase_list();
        return $phr_lst->foaf_children($sys->typ_lst->vrb->get_verb(verbs::PART_NAME), 1);
    }

    /**
     * makes sure that all combinations of "are" and "contains" are included
     * @return phrase_list all phrases linked with are and contains
     */
    function are_and_contains(): phrase_list
    {
        log_debug('for ' . $this->dsp_id());

        // this first time get all related items
        $phr_lst = $this->phrase_list();
        $phr_lst = $phr_lst->are();
        $added_lst = $phr_lst->contains();
        $added_lst->remove($this->phrase_list());
        // ... and after that get only for the new
        if ($added_lst->count() > 0) {
            $loops = 0;
            log_debug('added ' . $added_lst->dsp_id() . ' to ' . $phr_lst->dsp_id());
            do {
                $next_lst = clone $added_lst;
                $next_lst = $next_lst->are();
                $added_lst = $next_lst->contains();
                $added_lst->remove($phr_lst);
                if (!$added_lst->is_empty()) {
                    log_debug('add ' . $added_lst->dsp_id() . ' to ' . $phr_lst->dsp_id());
                }
                $phr_lst->merge($added_lst);
                $loops++;
            } while (count($added_lst->lst()) > 0 and $loops < def::MAX_LOOP);
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
        global $sys;

        $result = new word($this->get_user());

        $link_id = $sys->typ_lst->vrb->id(verbs::FOLLOW);
        $db_con->usr_id = $this->get_user()->id;
        $db_con->set_class(triple::class);
        $key_result = $db_con->get_value_2key(triple_db::FLD_FROM, triple_db::FLD_TO, $this->id(), verb_db::FLD_ID, $link_id);
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
        global $sys;

        $result = new word($this->get_user());

        $link_id = $sys->typ_lst->vrb->id(verbs::FOLLOW);
        $db_con->usr_id = $this->get_user()->id;
        $db_con->set_class(triple::class);
        $key_result = $db_con->get_value_2key(triple_db::FLD_TO, triple_db::FLD_FROM, $this->id(), verb_db::FLD_ID, $link_id);
        if (is_numeric($key_result)) {
            $id = intval($key_result);
            if ($id > 0) {
                $result->load_by_id($id);
            }
        }
        return $result;
    }


    /*
     * ui support
     */

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
            if ($db_row[word_db::FLD_VIEW] != null) {
                $view_id = $db_row[word_db::FLD_VIEW];
            }
        }

        log_debug('for ' . $this->dsp_id() . ' got ' . $view_id);
        return $view_id;
    }

    /**
     * calculate the suggested default view for this word
     * TODO review, because is it needed? get the view used by most users for this word
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function view_sql(sql_db $db_con): sql_par
    {
        $db_con->set_class(word::class);
        $db_con->set_usr($this->get_user()->id);
        $db_con->set_fields(array(word_db::FLD_VIEW));
        $db_con->set_join_usr_count_fields(array(user_db::FLD_ID), word::class);
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


    /*
     * related
     */

    /**
     * returns a list of the link types related to this word e.g. for "company" the link "are" will be returned, because "ABB" "is a" "company"
     */
    function link_types(foaf_direction $direction): verb_list
    {
        log_debug($this->dsp_id() . ' and user ' . $this->get_user()->id);

        global $db_con;

        $vrb_lst = new verb_list($this->get_user());
        $wrd = clone $this;
        $phr = $wrd->phrase();
        $vrb_lst->load_by_linked_phrases($db_con, $phr, $direction);
        return $vrb_lst;
    }

    /**
     * get the view object for this word
     */
    function reload_view(): ?view
    {
        $msk = null;

        if ($this->view != null) {
            $msk = $this->view;
        } else {
            if ($this->get_view_id() > 0) {
                $msk = new view($this->get_user());
                if ($msk->load_by_id($this->get_view_id())) {
                    $this->view = $msk;
                    log_debug('for ' . $this->dsp_id() . ' is ' . $msk->dsp_id());
                }
            }
        }

        return $msk;
    }


    /*
     * sandbox
     */

    /**
     * TODO review
     * true if the word has any none default settings such as a special type
     */
    function has_cfg(): bool
    {
        global $sys;

        $has_cfg = false;
        if ($this->plural != null) {
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
            if ($this->type_id <> $sys->typ_lst->phr_typ->default_id()) {
                $has_cfg = true;
            }
        }
        if ($this->get_view_id() > 0) {
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
    }

    /**
     * true if no other user has modified the word
     * assuming that in this case not confirmation from the other users for a word rename is needed
     */
    function not_changed(): bool
    {
        log_debug($this->id() . ' by someone else than the owner (' . $this->owner_id());

        global $db_con;
        $result = true;

        if ($this->id() == 0) {
            log_err('The id must be set to check if the triple has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con);
            $db_row = $db_con->get1($qp);
            if ($db_row[user_db::FLD_ID] > 0) {
                $result = false;
            }
        }
        log_debug('for ' . $this->id());
        return $result;
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     *                 to check if the word has been changed
     */
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $sc->set_class(word::class);
        return $sc->load_sql_not_changed($this->id(), $this->owner_id());
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
        log_debug($this->dsp_id() . ' for user ' . $this->get_user()->name);
        $usr_msg = new user_message();
        $msk_new = new view($this->get_user());
        $msk_new->load_by_id($view_id);

        $log = new change($this->get_user());
        $log->set_action(change_actions::UPDATE);
        $log->set_class(word::class);
        $log->set_field(word_db::FLD_VIEW);
        if ($this->get_view_id() > 0) {
            $msk_old = new view($this->get_user());
            $msk_old->load_by_id($this->get_view_id());
            $log->old_value = $msk_old->name();
            $log->old_id = $msk_old->id();
        } else {
            $log->old_value = null;
            $log->old_id = 0;
        }
        $log->new_value = $msk_new->name();
        $log->new_id = $msk_new->id();
        $log->row_id = $this->id();
        $log->add($usr_msg);

        return $log;
    }


    /*
     * save
     */

    /**
     * remember the word view, which means to save the view id for this word
     * each user can define set the view individually, so this is user-specific
     */
    function save_view(int $view_id): user_message
    {

        global $db_con;
        $usr_msg = new user_message();

        if ($this->id() > 0 and $view_id > 0 and $view_id <> $this->get_view_id()) {
            $this->set_view_id($view_id);
            if ($this->log_upd_view($view_id) > 0) {
                //$db_con = new mysql;
                $db_con->usr_id = $this->get_user()->id;
                if ($this->can_change()) {
                    $this->update('view of word', $usr_msg);
                } else {
                    if (!$this->has_usr_cfg()) {
                        if (!$this->add_usr_cfg()) {
                            $usr_msg->add_id(msg_id::ADD_USER_CONFIG_FAILED);
                        }
                    }
                    if ($usr_msg == '') {
                        $this->update('user view of word', $usr_msg);
                    }
                }
            }
        }
        return $usr_msg;
    }


    /*
     * save helper
     */

    /**
     * @return array with the reserved word names
     */
    protected
    function reserved_names(): array
    {
        return words::RESERVED_NAMES;
    }

    /**
     * @return array with the fixed word names for db read testing
     */
    protected
    function fixed_names(): array
    {
        return words::FIXED_NAMES;
    }


    /*
     * del
     */

    /**
     * delete the references to this word
     * which includes the phrase groups, the triples and values
     *
     * @param user_message $usr_msg the message for the user why deleting the word links has failed and a suggested solution
     * @return bool true if the word links has been deleted
     */
    function del_links(user_message $usr_msg): bool
    {
        $usr_msg = new user_message();

        // collect all phrase groups where this word is used
        // TODO Prio 2 activate
        //$grp_lst = new group_list($this->get_user());
        //$grp_lst->load_by_phr($this->phrase());

        // collect all triples where this word is used
        $trp_lst = new triple_list($this->get_user());
        $trp_lst->load_by_phr($this->phrase());

        // collect all values related to word triple
        $val_lst = new value_list($this->get_user());
        $val_lst->load_by_phr($this->phrase());

        // if there are still values, ask if they really should be deleted
        if ($val_lst->has_values()) {
            $val_lst->del($usr_msg);
        }

        // if there are still triples, ask if they really should be deleted
        if ($trp_lst->has_values()) {
            $trp_lst->del($usr_msg);
        }

        // delete the phrase groups
        // TODO Prio 2 activate
        //$grp_lst->del($usr_msg);

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
                phrase::FLD_TYPE,
                word_db::FLD_VIEW,
                word_db::FLD_PLURAL,
                sql_db::FLD_IMPACT
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param word|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        word|db_object_seq_id $obj,
        user_message          $msg,
        sql_type_list         $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->type_id() !== $this->type_id()) {
            $change_typ = true;
        } else {
            $change_typ = false;
        }
        // TODO Prio 2 review
        // do not overwrite a type with the default value
        // because this is set also if not specified by the import
        if ($this->type_id() == $sys->typ_lst->phr_typ->default_id() and $obj->type_id() !== null) {
            // if not the user table
            if (!$sc_par_lst->is_usr_tbl()) {
                $change_typ = false;
            }
        }
        if ($change_typ) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . phrase::FLD_TYPE,
                    $sys->typ_lst->cng_fld->id($table_id . phrase::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $sys;
            if ($this->type_id() < 0) {
                $msg->add(msg_id::PHRASE_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->type_id(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                phrase::FLD_TYPE,
                phrase::FLD_TYPE_NAME,
                $this->type_id(),
                $obj->type_id(),
                $sys->typ_lst->phr_typ);
        }
        if ($obj->get_view_id() !== $this->get_view_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . word_db::FLD_VIEW,
                    $sys->typ_lst->cng_fld->id($table_id . word_db::FLD_VIEW),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                word_db::FLD_VIEW,
                view_db::FLD_NAME,
                $this->view,
                $obj->view
            );
        }
        // TODO move to language forms
        if ($obj->plural !== $this->plural) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . word_db::FLD_PLURAL,
                    $sys->typ_lst->cng_fld->id($table_id . word_db::FLD_PLURAL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                word_db::FLD_PLURAL,
                $this->plural,
                word_db::FLD_PLURAL_SQL_TYP,
                $obj->plural
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


    /*
     * debug
     */

    /**
     * return the name (just because all objects should have a name function)
     */
    function name_dsp(): ?string
    {
        if ($this->is_excluded()) {
            return '';
        } else {
            return $this->name;
        }
    }

}
