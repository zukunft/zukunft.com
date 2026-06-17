<?php

/*

    model/word/triple.php - the object that links two words (an RDF triple)
    ---------------------

    $trp is the suggested var name

    A link can also be used in replacement for a word
    e.g. "Zurich (company)" where the link "Zurich is a company" is used

    The main sections of this object are
    - db const:          const for the database link
    - im/export const:   const for the im and export link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - set and get:       to capsule the vars from unexpected changes
    - modify:            change potentially all variables of this word object
    - preloaded:         select e.g. types from cache
    - fields:            the field names of this object as overwrite functions
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - im- and export:    create an export object and set the vars from an import object
    - info:              functions to make code easier to read
    - internal:          e.g. to generate the name based on the link
    - save:              manage to update the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - debug:             internal support functions for debugging
    - display:           to be moved to the frontend object


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

include_once paths::MODEL_SANDBOX . 'sandbox_link_named.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'combine_named.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LANGUAGE . 'language.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
//include_once paths::MODEL_LOG . 'change_link.php';
//include_once paths::MODEL_LOG . 'change_table_list.php';
//include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_type.php';
//include_once paths::MODEL_PHRASE . 'term.php';
//include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link.php';
include_once paths::MODEL_SANDBOX . 'sandbox_link_named.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_VERB . 'verb_db.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_VIEW . 'view_db.php';
//include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_db.php';
//include_once paths::MODEL_WORD . 'word_list.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\combine_named;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link_named;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_db;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_db;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;


class triple extends sandbox_link_named
{

    /*
     * db const
     */

    // comment used for the database creation
    const string TBL_COMMENT = 'to link one word or triple with a verb to another word or triple';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = triple_db::FLD_ID;
    const array FLD_LST_LINK = triple_db::FLD_LST_LINK;
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = triple_db::FLD_LST_MUST_BUT_USER_CAN_CHANGE;
    const array FLD_LST_USER_CAN_CHANGE = triple_db::FLD_LST_USER_CAN_CHANGE;
    const array FLD_LST_NON_CHANGEABLE = triple_db::FLD_LST_NON_CHANGEABLE;
    const array FLD_NAMES_LINK = triple_db::FLD_NAMES_LINK;
    const array FLD_NAMES = triple_db::FLD_NAMES;
    const array FLD_NAMES_USR = triple_db::FLD_NAMES_USR;
    const array FLD_NAMES_NUM_USR = triple_db::FLD_NAMES_NUM_USR;
    const array ALL_SANDBOX_FLD_NAMES = triple_db::ALL_SANDBOX_FLD_NAMES;

    /*
     * object vars
     */

    // triple vars additional to the name and link vars of the parent user sandbox object
    // the name manually set by the user, which can be empty
    public ?string $name_given {
        set {
            $this->name_given = $value;
        }
    }
    // the generated name based on the linked objects and saved in the database for faster searching
    private ?string $name_generated;

    // to select single triple used by the system without using the type that can potentially select more than one triple
    public ?string $code_id;

    // the weight of this triple compared to others where 1 represents 100% weight
    public ?float $weight {
        set {
            $this->weight = $value;
        }
    }

    // to cache the query results
    // the total number of values linked to this triple as an indication how common the triple is and to sort the triples
    public ?int $usage {
        /**
         * @return int|null a higher number indicates a higher usage
         */
        get {
            // TODO Prio 2 calculate usage from criteria if useful or requested
            return $this->usage;
        }
        /**
         * set the value to rank the triple by usage
         * @param int|null $usage the new value for the usage
         */
        set(int|null $usage) {
            // TODO Prio 2 remember refresh timestamp to avoid too many updates
            $this->usage = $usage;
        }
    }
    // the importance of the word based on the value defined for each word by the words "impact" and "criteria"
    public ?float $impact {
        get {
            // TODO Prio 2 calculate impact from criteria if useful or requested
            return $this->impact;
        }
        /**
         * set the cache value to sort this triple by relevance
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

    // only used for the export object
    // name of the default view for this word
    private ?view $view {
        set {
            $this->view = $value;
        }
    }
    private ?array $ref_lst = [];


    /*
     * construct and map
     */

    /**
     * define the settings for this triple object
     * @param user $usr the user who requested to see this triple
     */
    function __construct(user $usr)
    {
        $this->id = 0;

        parent::__construct($usr);

        $this->rename_can_switch = def::UI_CAN_CHANGE_triple_NAME;

        $this->reset(true);

        // also create the link objects because there is now case where they are supposed to be null
        $this->create_objects();
    }

    /**
     * reset the in memory fields used e.g. if some ids are updated
     * @param bool $keep_user set to true to keep the original user
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->set_name(null);
        $this->name_given = null;
        $this->name_generated = null;
        $this->weight = null;
        $this->code_id = null;
        $this->usage = null;
        $this->impact = null;

        $this->view = null;
        $this->ref_lst = [];

        $this->create_objects();
    }

    private function create_objects(
        string $from = '',
        string $verb = '',
        string $to = ''
    ): void
    {
        $this->set_fob(new phrase($this->get_user()));
        $this->fob()->set_name($from);
        $this->set_tob(new phrase($this->get_user()));
        $this->tob()->set_name($to);
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
     * @return bool true if the triple is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = triple_db::FLD_ID,
        string $name_fld = triple_db::FLD_NAME,
        string $type_fld = phrase::FLD_TYPE
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        if ($result) {
            if (array_key_exists(triple_db::FLD_FROM, $db_row)) {
                $phr_id = $db_row[triple_db::FLD_FROM];
                if ($phr_id != null) {
                    $this->get_from()->set_obj_from_id($phr_id);
                }
            }
            if (array_key_exists(triple_db::FLD_TO, $db_row)) {
                $phr_id = $db_row[triple_db::FLD_TO];
                if ($phr_id != null) {
                    $this->get_to()->set_obj_from_id($phr_id);
                }
            }
            if (array_key_exists(verb_db::FLD_ID, $db_row)) {
                if ($db_row[verb_db::FLD_ID] != null) {
                    $this->set_verb_id($db_row[verb_db::FLD_ID]);
                }
            }
            // TODO use json_fields object
            if (array_key_exists(triple_db::FLD_NAME_GIVEN, $db_row)) {
                $this->name_given = $db_row[triple_db::FLD_NAME_GIVEN];
            }
            if (array_key_exists(triple_db::FLD_NAME_AUTO, $db_row)) {
                $this->set_name_generated($db_row[triple_db::FLD_NAME_AUTO]);
            }
            if (array_key_exists(sql_db::FLD_CODE_ID, $db_row)) {
                $this->set_code_id($db_row[sql_db::FLD_CODE_ID], $this->get_user());
            }
            if (array_key_exists(triple_db::FLD_WIGHT, $db_row)) {
                $this->weight = $db_row[triple_db::FLD_WIGHT];
            }
            if (array_key_exists(sql_db::FLD_USAGE, $db_row)) {
                $this->usage = $db_row[sql_db::FLD_USAGE];
            }
            if (array_key_exists(sql_db::FLD_IMPACT, $db_row)) {
                $this->impact = $db_row[sql_db::FLD_IMPACT];
            }
        }
        return $result;
    }

    /**
     * map a triple api json to this model triple object
     * similar to the import_obj function but using the database id instead of names as the unique key
     * @param array $api_json the api array with the triple values that should be mapped
     * @param user_message $usr_msg the message for the user why the action has failed and a suggested solution
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        parent::api_mapper($api_json, $usr_msg);

        if (array_key_exists(json_fields::FROM, $api_json)) {
            $phr = $this->phrase_from_api_json($api_json[json_fields::FROM]);
            $this->set_from($phr);
        }
        if (array_key_exists(json_fields::VERB, $api_json)) {
            $vrb = $this->verb_from_api_json($api_json[json_fields::VERB]);
            $this->set_verb($vrb);
        }
        if (array_key_exists(json_fields::TO, $api_json)) {
            $phr = $this->phrase_from_api_json($api_json[json_fields::TO]);
            $this->set_to($phr);
        }
        if (array_key_exists(json_fields::WEIGHT, $api_json)) {
            $this->weight = $api_json[json_fields::WEIGHT];
        }
        if (array_key_exists(sql_db::FLD_IMPACT, $api_json)) {
            $this->impact = $api_json[sql_db::FLD_IMPACT];
        }

        // TODO move plural to language forms
        /*
        if (array_key_exists(json_fields::PLURAL, $api_json)) {
            if ($api_json[json_fields::PLURAL] <> '') {
                $this->set_plural($api_json[json_fields::PLURAL]);
            }
        }
        */
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
     * set the vars of this triple object based on the given json without writing to the database
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions and if the included user is a system user the import can also set the code_id
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
        global $sys;
        global $db_con;

        parent::import_mapper($in_ex_json, $msg, $dto);

        if (key_exists(json_fields::TYPE_CODE_ID, $in_ex_json)) {
            $this->set_type($in_ex_json[json_fields::TYPE_CODE_ID]);
        } elseif (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            $this->set_type($in_ex_json[json_fields::TYPE_NAME]);
        }
        if (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            $this->type_id = $sys->typ_lst->phr_typ->id($in_ex_json[json_fields::TYPE_NAME]);
        }
        if (key_exists(json_fields::EX_FROM, $in_ex_json)) {
            $value = $in_ex_json[json_fields::EX_FROM];
            if ($value == "") {
                $lib = new library();
                $msg->add(msg_id::FROM_NAME_NOT_EMPTY, [msg_id::VAR_JSON_TEXT => $lib->dsp_array($in_ex_json)]);
            } else {
                if (is_string($value)) {
                    if ($dto == null) {
                        $this->set_from($this->import_phrase($value, $msg));
                    } else {
                        $phr = $dto->get_phrase_by_name($value);
                        if ($phr == null) {
                            // create a phrase without saving to the database
                            $phr = new phrase($this->get_user());
                            $phr->set_name($value, word::class);
                            if ($phr->db_ready($msg)) {
                                $this->set_from($phr);
                            } else {
                                $msg->add_type_message($value, msg_id::PHRASE_MISSING->value);
                            }
                        } else {
                            $this->set_from($phr);
                        }
                    }
                } else {
                    log_err($value . ' is expected to be a string');
                }
            }
        }
        if (key_exists(json_fields::EX_TO, $in_ex_json)) {
            $value = $in_ex_json[json_fields::EX_TO];
            if ($value == "") {
                $lib = new library();
                $msg->add(msg_id::TO_NAME_NOT_EMPTY, [msg_id::VAR_JSON_TEXT => $lib->dsp_array($in_ex_json)]);
            } else {
                if ($dto == null) {
                    $this->set_to($this->import_phrase($value, $msg));
                } else {
                    $phr = $dto->get_phrase_by_name($value);
                    if ($phr == null) {
                        // create a phrase without saving to the database
                        $phr = new phrase($this->get_user());
                        $phr->set_name($value, word::class);
                        if ($phr->db_ready($msg)) {
                            $this->set_to($phr);
                        } else {
                            $msg->add_type_message($value, msg_id::PHRASE_MISSING->value);
                        }
                    } else {
                        $this->set_to($phr);
                    }
                }
            }
        }

        if (key_exists(json_fields::EX_VERB, $in_ex_json)) {
            $name = $in_ex_json[json_fields::EX_VERB];
            $vrb = $sys->typ_lst->vrb->get_by_name($name);
            if ($vrb == null) {
                if ($name <> '') {
                    $vrb = new verb();
                    $vrb->set_name($name);
                    // TODO Prio 0 move saving from the mapper to the import_obj to avoid db interaction during the mapping
                    if ($db_con->is_open()) {
                        $msg->add(msg_id::TRIPLE_VERB_CREATED, [msg_id::VAR_ID => $this->dsp_id(), msg_id::VAR_NAME => $name]);
                        $vrb->set_user($this->get_user());
                        // TODO remove this exception
                        $vrb->save($msg);
                    }
                    $dto?->add_verb($vrb);
                } else {
                    $vrb = $sys->typ_lst->vrb->get_verb(verbs::NOT_SET);
                    $msg->add(msg_id::TRIPLE_VERB_MISSING, [msg_id::VAR_ID => $this->dsp_id()]);
                }
            } else {
                if ($vrb->id <= 0) {
                    $msg->add(msg_id::TRIPLE_VERB_NOT_FOUND, [msg_id::VAR_NAME => $name]);
                    if ($this->name <> '') {
                        $msg->add(msg_id::FOR_TRIPLE, [msg_id::VAR_NAME => $this->name]);
                    }
                }
            }
            $this->set_verb($vrb);
        }

        if (key_exists(json_fields::WEIGHT, $in_ex_json)) {
            $this->weight = $in_ex_json[json_fields::WEIGHT];
        }
        if (key_exists(json_fields::IMPACT, $in_ex_json)) {
            $this->set_impact($in_ex_json[json_fields::IMPACT]);
        }
        if (key_exists(json_fields::CODE_ID, $in_ex_json)) {
            $this->set_code_id($in_ex_json[json_fields::CODE_ID], $msg->usr);
        }


        if (key_exists(json_fields::VIEW, $in_ex_json)) {
            $value = $in_ex_json[json_fields::VIEW];
            $trp_view = new view($this->get_user());
            if ($db_con->is_open()) {
                // TODO replace all load in the import mapper with get functions
                $trp_view->load_by_name($value);
                if ($trp_view->id() == 0) {
                    $msg->add(msg_id::IMPORT_NOT_FIND_VIEW, [msg_id::VAR_NAME => $value, msg_id::VAR_ID => $this->dsp_id()]);
                }
            } else {
                $trp_view->set_name($value);
            }
            $this->view = $trp_view;
        }

        // finally generate the name if needed
        if ($this->name() == null and $this->name_given() == null) {
            $this->name_generated = $this->generate_name();
            $this->name = $this->name_generated;
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
                // the from, verb and to names are included for a page request (incl_related)
                // or when the phrases are explicitly requested, so the frontend can show e.g.
                // the triple page title "<from> <verb> <to>" with a link to each part
                $with_names = ($typ_lst->include_phrases() or $typ_lst->incl_related());
                $from = $this->get_from()->obj();
                if ($from != null) {
                    if ($from->id() <> 0 or $from->name() != '') {
                        $vars[json_fields::FROM] = $this->from_id();
                        if ($with_names) {
                            // create the json based on the phrase not the object to include the class type
                            $vars[json_fields::FROM_PHRASE] = $this->get_from()->api_json_array($typ_lst);
                        }
                    }
                }
                if ($this->get_verb() != null) {
                    if ($with_names) {
                        // include the verb name so the frontend can link the verb
                        $vars[json_fields::VERB] = $this->get_verb()->api_json_array($typ_lst);
                    } else {
                        $vars[json_fields::VERB] = $this->get_verb()->id();
                    }
                }
                $to = $this->get_to()->obj();
                if ($to != null) {
                    if ($to->id() <> 0 or $to->name() != '') {
                        $vars[json_fields::TO] = $this->to_id();
                        if ($with_names) {
                            // create the json based on the phrase not the object to include the class type
                            $vars[json_fields::TO_PHRASE] = $this->get_to()->api_json_array($typ_lst);
                        }
                    }
                }
                // add the generated name if there is no given name
                if (!array_key_exists(json_fields::NAME, $vars)) {
                    $vars[json_fields::NAME] = $this->generate_name();
                } elseif ($vars[json_fields::NAME] == '') {
                    $vars[json_fields::NAME] = $this->generate_name();
                }
                $vars[json_fields::USAGE] = $this->usage;
                $vars[json_fields::IMPACT] = $this->impact;
            }
        } elseif ($this->is_excluded() and $typ_lst->with_excluded_id()) {
            $vars[json_fields::ID] = $this->id();
            $vars[json_fields::EXCLUDED] = true;
        }

        return $vars;
    }

    /**
     * select the id from a json array
     * TODO Prio 1 add user_message as parameter
     * @param int|array $value either the id itself or an array with the id
     * @return phrase
     */
    private function phrase_from_api_json(int|array $value): phrase
    {
        $usr_msg = new user_message();
        $phr = new phrase($this->get_user());
        if (is_array($value)) {
            $phr->api_mapper($value, $usr_msg);
        } elseif (is_int($value)) {
            if ($value != 0) {
                // TODO use phrase cache
                $phr->set_id($value);
            }
        } else {
            log_err('unexpected format of api message');
        }
        return $phr;
    }

    /**
     * select the id from a json array
     * @param int|array $value either the id itself or an array with the id
     * @return verb
     */
    private function verb_from_api_json(int|array $value): verb
    {
        global $sys;
        if (is_array($value)) {
            if (key_exists(json_fields::ID, $value)) {
                $id = $value[json_fields::ID];
                $vrb = $sys->typ_lst->vrb->get($id);
            } else {
                $vrb = new verb();
                log_err('id field missing in ' . implode(',', $value));
            }
        } elseif (is_int($value)) {
            if ($value != 0) {
                $vrb = $sys->typ_lst->vrb->get($value);
            } else {
                $vrb = new verb();
            }
        } else {
            $vrb = new verb();
            log_err('unexpected format of api message');
        }
        return $vrb;
    }


    /*
     * im- and export
     */

    /**
     * get a phrase based on the name (and save it if needed and requested)
     *
     * @param string $name the name of the phrase
     * @return phrase the created phrase object
     */
    private function import_phrase(string $name, user_message $usr_msg): phrase
    {
        global $db_con;

        $result = new phrase($this->get_user());
        if ($db_con->is_open()) {
            $result->load_by_name($name);
            if ($result->id() == 0) {
                // if there is no word or triple with the name yet, automatically create a word
                $wrd = new word($this->get_user());
                $wrd->set_name($name);
                if ($usr_msg->is_ok()) {
                    $wrd->save($usr_msg);
                    if ($wrd->id() == 0) {
                        log_err('Cannot add from word "' . $name . '" when importing ' . $this->dsp_id(), 'triple->import_obj');
                    } else {
                        $result = $wrd->phrase();
                    }
                }
            }
        } else {
            $result->set_name($name, word::class);
        }
        return $result;
    }

    /**
     * import a triple from a json object
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

        $this->import_mapper($in_ex_json, $msg, $dto);

        // add related parameters to the triple object
        if ($msg->is_ok()) {
            if (key_exists(json_fields::REFS, $in_ex_json)) {
                $ref_json = $in_ex_json[json_fields::REFS];
                foreach ($ref_json as $ref_data) {
                    $ref_obj = new ref($this->get_user());
                    $ref_obj->set_phrase($this->phrase());
                    if ($ref_obj->import_obj($ref_data, $msg, $dto)) {
                        $this->ref_lst[] = $ref_obj;
                    }
                }
            }
        }

        // save the triple in the database
        if ($db_con->is_open()) {
            if ($msg->is_ok()) {
                $this->save($msg);
            } else {
                $lib = new library();
                $msg->add(msg_id::IMPORT_NOT_SAVED, [
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                    msg_id::VAR_ID => $this->dsp_id()
                ]);
            }
        }

        return $msg->is_ok();
    }

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

        if ($this->name_ex_generated() <> '') {
            $vars[json_fields::NAME] = $this->name_ex_generated();
        }
        if ($this->description <> '') {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }
        if ($this->type_name() <> '') {
            if ($this->type_id != $sys->typ_lst->phr_typ->default_id()) {
                $vars[json_fields::TYPE_NAME] = $this->type_name();
            }
        }
        if ($this->get_from()->name() <> '') {
            $vars[json_fields::EX_FROM] = $this->get_from()->name();
        }
        if ($this->get_verb_name() <> '') {
            unset($vars[json_fields::PREDICATE]);
            $vars[json_fields::EX_VERB] = $this->get_verb_name();
        }
        if ($this->get_to()->name() <> '') {
            $vars[json_fields::EX_TO] = $this->get_to()->name();
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
        // the usage is only included in the export as an indication to validate the consistency
        if ($this->usage != null) {
            $vars[json_fields::USAGE] = $this->usage;
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
     * set the most used object vars with one set statement
     * @param int $id mainly for test creation the database id of the word
     * @param string $name mainly for test creation the name of the word
     */
    function set(
        int    $id = 0,
        string $name = '',
        string $from = '',
        string $verb = '',
        string $to = ''
    ): void
    {
        $this->id = $id;

        if ($name != '') {
            $this->set_name($name);
        }
        $this->create_objects($from, $verb, $to);
    }

    /**
     * set the "from" phrase of this triple
     * e.g. "Zurich" for "Zurich (city)" based on "Zurich" (from) "is a" (verb) "city" (to)
     *
     * @param phrase $from_phr the "from" phrase
     * @return void
     */
    function set_from(phrase $from_phr): void
    {
        $this->set_fob($from_phr);
    }

    function get_from(): phrase|sandbox_named|combine_named|null
    {
        return $this->fob();
    }

    /**
     * set the predicate of this triple
     * e.g. "Zurich" for "Zurich (city)" based on "Zurich" (from) "is a" (verb/predicate) "city" (to)
     *
     * @param verb $vrb the verb
     * @return void
     */
    function set_verb(verb $vrb): void
    {
        if ($vrb->id() != 0) {
            $this->set_predicate_id($vrb->id());
        } else {
            if ($vrb->name() != '') {
                global $sys;
                $vrb_selected = $sys->typ_lst->vrb->get_by_name($vrb->name());
                if ($vrb_selected == null) {
                    log_err('verb "' . $vrb->name() . '" not found');
                } else {
                    $this->set_predicate_id($vrb_selected->id());
                }
            }
        }
    }

    /**
     * set the id of the link predicate
     * in case of triple objects the id can be negative, which means that the object is used to test the reverse case
     * @param int $id if > 0 the id of the verb, if < 0 the reverse case and if 0 the verb is not yet set
     * @return void
     */
    function set_verb_id(int $id): void
    {
        $this->set_predicate_id($id);
    }

    function get_verb(): verb|null
    {
        global $sys;
        $id = $this->predicate_id();
        if ($id == 0) {
            return null;
        } else {
            return $sys->typ_lst->vrb->get($id);
        }
    }

    /**
     * @return int|null the id of predicate which is in case of the triple the verb id
     */
    function get_verb_id(): int|null
    {
        return $this->predicate_id();
    }

    /**
     * TODO Prio 1 use msg_id for messages
     * @return string the name of the verb
     */
    function get_verb_name(): string
    {
        global $sys;
        $id = $this->predicate_id();
        if ($id > 0) {
            $vrb = $sys->typ_lst->vrb->get($id);
            if ($vrb != null) {
                return $vrb->name();
            } else {
                $msg = 'name for verb id ' . $id . ' of a triple ' . $this->dsp_id() . ' is missing in system cache';
                log_err($msg);
                return $msg;
            }
        } elseif ($id < 0) {
            $vrb = $sys->typ_lst->vrb->get($id * -1);
            if ($vrb != null) {
                return $vrb->reverse();
            } else {
                $msg = 'name for reverse verb id ' . $id . ' of a triple ' . $this->dsp_id() . ' is missing in system cache';
                log_err($msg);
                return $msg;
            }
        } else {
            return '';
        }
    }

    /**
     * @return string the code_id of the verb
     */
    function get_verb_code_id(): string
    {
        global $sys;
        $id = $this->predicate_id();
        if ($id > 0) {
            $vrb = $sys->typ_lst->vrb->get($id);
            if ($vrb != null) {
                return $vrb->get_code_id();
            } else {
                $msg = 'code id for verb of triple ' . $this->dsp_id() . ' is missing in system cache';
                log_warning($msg);
                return $msg;
            }
        } elseif ($id < 0) {
            $vrb = $sys->typ_lst->vrb->get($id * -1);
            if ($vrb != null) {
                return $vrb->get_code_id();
            } else {
                $msg = 'code id for reverse verb of triple ' . $this->dsp_id() . ' is missing in system cache';
                log_err($msg);
                return $msg;
            }
        } else {
            $msg = 'verb with id is zero';
            log_info($msg);
            return $msg;
        }
    }

    /**
     * overwrite the link type function
     * @return string|null the name of the verb
     */
    function predicate_name(): ?string
    {
        return $this->get_verb_name();
    }

    /**
     * overwrite the link type function
     * @return string|null the code id of the verb
     */
    function get_predicate_code_id(): ?string
    {
        return $this->get_verb_code_id();
    }

    /**
     * set the "from" phrase of this triple
     * e.g. "city" for "Zurich (city)" based on "Zurich" (from) "is a" (verb) "city" (to)
     *
     * @param phrase $to_phr the code id that should be added to this triple
     * @return void
     */
    function set_to(phrase $to_phr): void
    {
        $this->set_tob($to_phr);
    }

    function get_to(): phrase|sandbox_named|combine_named|null
    {
        return $this->tob();
    }

    /**
     * set the phrase type of this triple by the given code id or name
     * if the type id is null or 0 the phrase type from the "to" phrase is returned
     *
     * @param string $code_id_or_name the code id that should be added to this triple
     * @param user $usr_req the user who wants to change the type
     * @return user_message a warning if the phrase type code id is not found
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

    /**
     * set the name used object
     * @param string|null $name
     * @return void
     */
    function set_name(?string $name): void
    {
        $this->name = $name;
    }

    /**
     *
     * @param string|null $name_generated the generated name as saved in the database
     * @return void
     */
    function set_name_generated(?string $name_generated): void
    {
        if ($name_generated != null) {
            // use the updated generated name or the generated name loaded from the database
            $this->name_generated = $name_generated;
        } else {
            // worst case use null
            $this->name_generated = null;
            log_warning('No name found for triple ' . $this->id());
        }
    }

    /**
     * set the used name, update the generated name if needed
     * @return void
     */
    function set_names(): void
    {
        // update the generated name if needed
        if ($this->name_given == null and $this->name == null) {
            if ($this->generate_name() != null and $this->generate_name() != '' and $this->generate_name() != ' ()') {
                $this->name_generated = $this->generate_name();
            }
        }

        // remove the given name if not needed
        if ($this->name_given == $this->name_generated) {
            $this->name_given = null;
        } else {
            // or set the given name if needed e.g. when called be json import
            if ($this->name != null and $this->name != $this->name_generated) {
                $this->name_given = $this->name;
            }
        }

        // use the generated name as fallback
        if ($this->name == null) {
            if ($this->name_given != null and $this->name_given != null) {
                $this->name = $this->name_given;
            } else {
                $this->name = $this->name_generated;
            }
        }
    }

    /**
     * set the value to rank the triple by usage
     *
     * @param int|null $usage a higher value moves the triple to the top of the selection list
     * @return void
     */
    function set_usage(?int $usage): void
    {
        $this->usage = $usage;
    }

    /**
     * @return int|null a higher number indicates a higher usage
     */
    function get_usage(): ?int
    {
        return $this->usage;
    }

    /**
     * set the cache value to sort this triple by relevance
     * the impact is calculated based on the formula assigned to the object
     * by the system triple "impact phrase"
     *
     * @param float|null $impact a higher value moves the sandbox object to the top of the selection list
     * @return void
     */
    function set_impact(?float $impact): void
    {
        $this->impact = $impact;
    }

    /**
     * the impact as a function to enable overwrite in the combine objects phrase and term
     * @return float|null a higher number indicates a higher relevance
     */
    function get_impact(): ?float
    {
        return $this->impact;
    }

    /**
     * @return string|null the name manually set by the user or null if the generated name should be used
     */
    function name_given(): ?string
    {
        return $this->name_given;
    }

    /**
     * TODO check where the function or the db value should be used
     */
    function name_generated(): ?string
    {
        if ($this->name_generated == null
            and $this->name_given == null
            and $this->name == null) {
            $this->name_generated = $this->generate_name();
            $this->name = $this->name_generated;
        }
        return $this->name_generated;
    }

    /**
     * @return string|null the description of the link which should be shown to the user as mouseover
     */
    function get_description(): ?string
    {
        return $this->description;
    }

    /**
     * get the database id of the phrase type
     * @return int|null the id of the word type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }

    /**
     * set the unique id to select a single triple by the program
     *r
     * @param string|null $code_id the unique key to select a word used by the system e.g. for the system configuration
     * @param user $usr the user who has requested the change
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_code_id(?string $code_id, user $usr): user_message
    {
        $msg = new user_message();
        if ($usr->can_set_code_id()) {
            $this->code_id = $code_id;
        } else {
            $lib = new library();
            $msg->add(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr->name(),
                msg_id::VAR_USER_PROFILE => $usr->profile_code_id(),
                msg_id::VAR_NAME => sql_db::FLD_CODE_ID,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $msg;
    }

    /**
     * @return string|null the unique key or null if the word is not used by the system
     */
    function get_code_id(): ?string
    {
        return $this->code_id;
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

    function get_view(): ?view
    {
        return $this->view;
    }

    /**
     * @return int the id of the default view for this triple or null if no view is preferred
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
     * create a clone and keep additional the verb as it is a unique db id for the triple
     *
     * @param string $name the target name
     * @return $this a clone with the name changed
     */
    function cloned_named(string $name): sandbox_link_named
    {

        $obj_cpy = parent::cloned_named($name);
        $obj_cpy->set_verb($this->get_verb());
        return $obj_cpy;
    }

    /**
     * copy the link objects from this object to the given triple
     * used to unset any changes in the link to detect only the changes fields that the user is allowed to change
     *
     * @param sandbox_link|triple $lnk
     * @return triple
     */
    function set_link_objects(sandbox_link|triple $lnk): triple
    {
        $lnk->set_fob($this->fob());
        $lnk->set_verb($this->get_verb());
        $lnk->set_tob($this->tob());
        return $lnk;
    }


    /*
     * info
     */

    /**
     * @return bool
     */
    function is_time(): bool
    {
        if ($this->type_code_id() == phrase_type_shared::TIME) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if the word has the type "time" e.g. "monthly"
     */
    function is_time_jump(): bool
    {
        return $this->is_type(phrase_type_shared::TIME_JUMP);
    }

    /**
     * @return bool true if the word has the type "measure" (e.g. "metre" or "CHF")
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
     * @return bool true if the triple has one of the scaling types (e.g. "a million" or "one"; "one" is a hidden scaling type)
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
     * @return bool true if the word has the type "scaling_percent" (e.g. "percent")
     */
    function is_percent(): bool
    {
        return $this->is_type(phrase_type_shared::PERCENT);
    }

    /**
     * @return bool true if the word is normally not shown to the user e.g. scaling of one is assumed
     */
    function is_hidden(): bool
    {
        $result = false;
        if ($this->is_type(phrase_type_shared::SCALING_HIDDEN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return bool true if the triple is of the given type
     */
    function is_type(string $typ): bool
    {
        global $sys;

        $result = false;
        if ($this->type_id == $sys->typ_lst->phr_typ->id($typ)) {
            $result = true;
        }
        return $result;
    }

    function needs_from(): bool
    {
        $needs_from = true;
        if (in_array($this->get_verb_code_id(), verbs::WITHOUT_FROM)) {
            $needs_from = false;
        }
        return $needs_from;
    }

    /**
     * @return bool true if the verb of the triple is set
     */
    function has_verb(): bool
    {
        if ($this->predicate_id() != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * avoid duplicates
     * if any of the unit keys of the object matches true is returned
     * @param triple|combine_named|type_object|sandbox|null $obj_to_check the object used for the comparison
     * @return bool true if the objects should not be in the database at the same time
     */
    function is_similar(triple|combine_named|type_object|sandbox|null $obj_to_check): bool
    {
        $result = parent::is_similar($obj_to_check);

        if ($this::class == $obj_to_check::class) {
            if ($this->name_given == $obj_to_check->name_given) {
                $result = true;
            }
            if ($this->name_generated == $obj_to_check->name_generated) {
                $result = true;
            }
            if ($this->code_id == $obj_to_check->code_id) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * can merge if all unique keys match
     * check that the given object is by all unique keys the same as the actual object
     * @param triple|sandbox_link|combine_named|type_object|sandbox|null $obj_to_check the object used for the comparison
     * @return bool true if the objects should not be in the database at the same time
     */
    function is_same(triple|sandbox_link|combine_named|type_object|sandbox|null $obj_to_check): bool
    {
        $result = parent::is_same($obj_to_check);

        if ($this::class == $obj_to_check::class) {
            if ($this->name_given != $obj_to_check->name_given) {
                $result = false;
            }
            if ($this->name_generated != $obj_to_check->name_generated) {
                $result = false;
            }
            if ($this->code_id != $obj_to_check->code_id) {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param triple|CombineObject|db_object_seq_id $std_obj the norm object as saved in the database
     * @param triple|CombineObject|db_object_seq_id $result empty clone of the target user object
     * @return triple|CombineObject|db_object_seq_id the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        triple|CombineObject|db_object_seq_id $std_obj,
        triple|CombineObject|db_object_seq_id $result
    ): triple|CombineObject|db_object_seq_id
    {
        parent::delta($std_obj, $result);
        if ($std_obj->from_id() !== $this->from_id()) {
            $result->set_from($this->get_from());
        }
        if ($std_obj->predicate_id !== $this->predicate_id) {
            $result->predicate_id = $this->predicate_id;
        }
        if ($std_obj->to_id() !== $this->to_id()) {
            $result->set_to($this->get_to());
        }

        if ($std_obj->code_id !== $this->code_id) {
            $result->code_id = $this->code_id;
        }

        if ($std_obj->name_given !== $this->name_given) {
            $result->name_given = $this->name_given;
        }
        if ($std_obj->name_generated !== $this->name_generated) {
            $result->name_generated = $this->name_generated;
        }

        if ($std_obj->weight !== $this->weight) {
            $result->weight = $this->weight;
        }
        if ($std_obj->view !== $this->view) {
            $result->view = $this->view;
        }

        if ($std_obj->usage !== $this->usage) {
            $result->usage = $this->usage;
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
     * fill this triple based on the given triple
     * if the id is set in the given word loaded from the database but this import word does not yet have the db id, set the id
     * if the given name is not set (null) the given name is not remove
     * if the given name is an empty string the given name is removed
     *
     * @param triple|CombineObject|db_object_seq_id $obj word with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(triple|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        // TODO use set and get function to enable phrase fill
        $trp = null;
        if ($obj::class == triple::class) {
            $trp = $obj;
        } elseif ($obj->obj()::class == triple::class) {
            $trp = $obj->obj();
        }
        if ($trp != null) {
            // fill to link objects
            if ($this->from_empty()) {
                if (!$trp->from_empty()) {
                    $this->set_from($trp->get_from());
                }
            } else {
                $this->get_from()->fill($trp->get_from(), $usr_req);
            }
            if ($this->verb_empty()) {
                if (!$trp->verb_empty()) {
                    $this->set_verb($trp->get_verb());
                }
            }
            if ($this->to_empty()) {
                if (!$trp->to_empty()) {
                    $this->set_to($trp->get_to());
                }
            } else {
                $this->get_to()->fill($trp->get_to(), $usr_req);
            }

            // fill the names
            if ($this->name_given === null and $trp->name_given != null) {
                $this->name_given = $trp->name_given;
            }
            if ($this->name_generated === null and $trp->name_generated != '') {
                $this->name_generated = $trp->name_generated;
            }

            // fill the parameters
            if ($this->weight === null and $trp->weight != null) {
                $this->weight = $trp->weight;
            }
            if ($this->view === null and $trp->view != null) {
                $this->view = $trp->view;
            }
        }
        if ($obj::class == phrase::class) {
            if ($this->get_usage() === null and $obj->get_usage() != null) {
                if ($this::class == phrase::class) {
                    $this->set_usage($obj->get_usage());
                } else {
                    $this->usage = $obj->get_usage();
                }
            }
        } else {
            if ($this->usage === null and $obj->usage != null) {
                if ($this::class == phrase::class) {
                    $this->set_usage($obj->usage);
                } else {
                    $this->usage = $obj->usage;
                }
            }
        }
        if ($obj::class == phrase::class) {
            if ($this->get_impact() === null and $obj->get_impact() != null) {
                if ($this::class == phrase::class) {
                    $this->set_impact($obj->get_impact());
                } else {
                    $this->impact = $obj->get_impact();
                }
            }
        } else {
            if ($this->impact === null and $obj->impact != null) {
                if ($this::class == phrase::class) {
                    $this->set_impact($obj->impact);
                } else {
                    $this->impact = $obj->impact;
                }
            }
        }
        return $usr_msg;
    }


    /*
     * preloaded
     */

    /**
     * get the name of the triple type
     * @return string the name of the triple type
     */
    function type_name(): string
    {
        global $sys;
        return $sys->typ_lst->phr_typ->name($this->type_id);
    }

    /**
     * get the code_id of the word type
     * @return string the code_id of the word type
     */
    function type_code_id(): string
    {
        global $sys;
        if ($this->type_id == null) {
            $msg = 'type for triple ' . $this->dsp_id() . ' is missing';
            log_err($msg);
            return $msg;
        } else {
            return $sys->typ_lst->phr_typ->code_id($this->type_id);
        }
    }

    // TODO add a function for each type and streamline the call


    /*
     * sql fields
     */

    function from_field(): string
    {
        return triple_db::FLD_FROM;
    }

    function type_field(): string
    {
        return verb_db::FLD_ID;
    }

    function type_name_field(): string
    {
        return verb_db::FLD_NAME;
    }

    function to_field(): string
    {
        return triple_db::FLD_TO;
    }


    /*
     * cast
     */

    /**
     * convert the word object into a phrase object
     */
    function phrase(): phrase
    {
        $phr = new phrase($this->get_user());
        // the triple has positive id, but the phrase uses a negative id
        $phr->set_name($this->name(), triple::class);
        $phr->set_obj($this);
        return $phr;
    }

    /**
     * @returns term the triple object cast into a term object
     * TODO remove lines not needed any more
     */
    function term(): term
    {
        $trm = new term($this->get_user());
        $trm->set_id_from_obj($this->id(), self::class);
        $trm->set_name($this->name(), triple::class);
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * load
     */

    /**
     * load a triple by name
     * @param string $name the name of the word, triple, formula, verb, view or view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name);
        return $this->load($qp);
    }

    /**
     * load a triple by the generated name (the name that the triple would have if the user has done not overwrite)
     * @param string|null $name the generated name of the triple
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name_generated(string|null $name): int
    {
        global $db_con;

        if ($name !== null) {
            log_debug($name);
            $qp = $this->load_sql_by_name_generated($db_con->sql_creator(), $name, $this::class);
            return $this->load($qp);
        } else {
            return 0;
        }
    }

    /**
     * load a triple by the ids of the linked objects
     * @param int $from the id of the phrase that is linked
     * @param int $predicate_id the type id of the link
     * @param int|string $to the id of the phrase to which is the link directed
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_link_id(int $from, int $predicate_id = 0, int|string $to = 0, string $class = self::class): int
    {
        global $db_con;

        log_debug($from . ' ' . $predicate_id . ' ' . $to);
        $qp = $this->load_sql_by_link($db_con->sql_creator(), $from, $predicate_id, $to, $class);
        return $this->load($qp);
    }

    /**
     * load a named user sandbox object e.g. word, triple, formula, verb or view from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox($db_row);
        // TODO Prio 1 add the original $msg as parameter to all functions that might create a message to the user
        $msg = new user_message();
        $this->reload_generated_name($msg);
        return $this->id();
    }


    /*
     * load sql
     */

    /**
     * create an SQL statement to retrieve a triple by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the triple and the related word, triple, formula or verb
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME, $this::class);
        $sc->add_where($this->name_field(), $name, sql_par_type::TEXT_USR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a triple by the generated name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the generated name of the triple and the related word, triple, formula or verb
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_name_generated(sql_creator $sc, string $name, string $class): sql_par
    {
        $qp = $this->load_sql($sc, 'name_generated', $class);
        $sc->add_where(triple_db::FLD_NAME_AUTO, $name, sql_par_type::TEXT_USR);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a triple by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $from the id of the phrase that is linked
     * @param int $predicate_id the type id of the link
     * @param int|string $to the id of the phrase to which is the link directed
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_link(sql_creator $sc, int $from, int $predicate_id, int|string $to, string $class): sql_par
    {
        $qp = $this->load_sql($sc, 'link_ids', $class);
        $sc->add_where(triple_db::FLD_FROM, $from);
        $sc->add_where(triple_db::FLD_TO, $to);
        $sc->add_where(verb_db::FLD_ID, $predicate_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the SQL to load the default triple always by the id
     *
     * @param int $name the database row id to select the standard row
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_standard_by_name(string $name, sql_creator $sc): sql_par
    {
        $sc->set_class($this::class);
        $qp = new sql_par($this::class, new sql_type_list([sql_type::NORM]));
        $qp->name .= $this->load_sql_name_ext();
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(array_merge(
            triple_db::FLD_NAMES_LINK,
            triple_db::FLD_NAMES,
            triple_db::FLD_NAMES_USR,
            triple_db::FLD_NAMES_NUM_USR,
            array(user_db::FLD_ID)
        ));

        return $this->load_sql_select_qp($sc, $qp);
    }

    /**
     * load the object parameters for all users by the standard formula link from the database
     * TODO Prio 0 add unit test
     *
     * @param int $from_id the id of the from link object
     * @param int $to_id the id of the to link object
     * @param user_message $msg to collect the error messages and suggested solutions for the calling user
     * @return bool true if the standard object has been loaded
     */
    function load_standard_by_link(
        int          $from_id,
        int          $to_id,
        user_message $msg
    ): bool
    {
        return parent::load_standard_by_link_parent(
            triple_db::FLD_FROM, $from_id,
            triple_db::FLD_TO, $to_id, $msg
        );
    }

    /**
     * load the object parameters for all users by the standard formula link from the database
     * TODO Prio 0 add unit test
     *
     * @param int $from_id the id of the from link object
     * @param int $typ_id the id of the verb object
     * @param int $to_id the id of the to link object
     * @param user_message $msg to collect the error messages and suggested solutions for the calling user
     * @return bool true if the standard object has been loaded
     */
    function load_standard_by_type_link(
        int          $from_id,
        int          $typ_id,
        int          $to_id,
        user_message $msg
    ): bool
    {
        return parent::load_standard_by_type_link_parent(
            triple_db::FLD_FROM, $from_id,
            triple_db::FLD_PREDICATE, $typ_id,
            triple_db::FLD_TO, $to_id, $msg
        );
    }

    /**
     * @return string the name of the SQL statement name extension based on the filled fields
     */
    private function load_sql_name_ext(): string
    {
        if ($this->id() != 0) {
            return sql_db::FLD_ID;
        } elseif ($this->name != '') {
            return sql_db::FLD_NAME;
        } elseif ($this->has_objects()) {
            // TODO Prio 2 check if an error message should be returned
            return '';
        } else {
            $msg = 'Either the database ID (' . $this->id() . ') or the ' .
                self::class . ' link objects (' . $this->dsp_id() . ') and the user (' . $this->get_user()->id() . ') must be set to load a ' .
                self::class;
            log_err($msg, self::class . '->load');
            return $msg;
        }
    }

    /**
     * add the select parameters to the query parameters
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par $qp the query parameters with the name already set
     * @return sql_par the query parameters with the select parameters added
     */
    private function load_sql_select_qp(sql_creator $sc, sql_par $qp): sql_par
    {
        if ($this->id() != 0) {
            $sc->add_where($this->id_field(), $this->id());
        } elseif ($this->name != '') {
            $sc->add_where($this->name_field(), $this->name());
        } elseif ($this->has_objects()) {
            $sc->add_where(triple_db::FLD_FROM, $this->from_id());
            $sc->add_where(triple_db::FLD_TO, $this->to_id());
            $sc->add_where(verb_db::FLD_ID, $this->get_verb_id());
        } elseif ($this->name_generated() != '') {
            $sc->add_where(triple_db::FLD_NAME_AUTO, $this->name_generated());
        } elseif ($this->name_given() != '') {
            $sc->add_where(triple_db::FLD_NAME_GIVEN, $this->name_given());
        } else {
            log_err('Cannot load default triple because no unique field is set');
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create an SQL statement to retrieve the user changes of the current triple
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
            triple_db::FLD_NAMES_USR,
            triple_db::FLD_NAMES_NUM_USR
        ));
        return parent::load_sql_user_changes($sc, $sc_par_lst);
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a triple from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $qp->name .= $query_name;

        $sc->set_class($class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(array_merge(triple_db::FLD_NAMES_LINK, triple_db::FLD_NAMES));
        $sc->set_usr_fields(triple_db::FLD_NAMES_USR);
        $sc->set_usr_num_fields(triple_db::FLD_NAMES_NUM_USR);

        return $qp;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return triple_db::FLD_NAME;
    }

    /**
     * @return array with all fields names of this view_relation object
     */
    protected function all_fields(): array
    {
        return array_merge(
            triple_db::FLD_NAMES_LINK,
            triple_db::FLD_NAMES,
            triple_db::FLD_NAMES_USR,
            triple_db::FLD_NAMES_NUM_USR,
            array(user_db::FLD_ID));
    }

    function all_sandbox_fields(): array
    {
        return triple_db::ALL_SANDBOX_FLD_NAMES;
    }


    /*
     * related
     */

    /**
     * set the generated triple name base on the view
     */
    private function reload_generated_name(user_message $msg): void
    {
        if ($this->id() > 0) {
            // automatically update the generic name
            $this->reload_objects($msg);
            $new_name = $this->name_generated();
            log_debug('triple->load check if name ' . $this->dsp_id() . ' needs to be updated to "' . $new_name . '"');
            if ($new_name <> $this->name_generated) {
                $this->name_generated = $new_name;
                $this->save($msg);
            }
        }
    }

    /**
     * load the triple without the linked objects, because in many cases the object are already loaded by the caller
     * similar to term->load, but with a different use of verbs
     * @param user_message $msg to collect the message due to missing links
     * @returns bool  false if the loading has failed
     */
    function reload_objects(user_message $msg): bool
    {
        log_debug($this->dsp_id());
        $result = true;

        // after every load call from outside the class the order should be checked and reversed if needed
        $this->check_order();

        // load the "from" phrase
        if ($this->get_from() == null) {
            log_err("The word (" . $this->from_id() . ") must be set before it can be loaded.", "triple->load_objects");
        } else {
            if ($this->from_id() <> 0 and !is_null($this->get_user()->id)) {
                if ($this->from_id() > 0) {
                    $wrd = new word($this->get_user());
                    $wrd->load_by_id($this->from_id());
                    if ($wrd->name() <> '') {
                        $this->set_from($wrd->phrase());
                        $this->get_from()->set_name($wrd->name());
                    } else {
                        $msg->add(msg_id::LOAD_WORD_BY_ID_FAILED, [
                            msg_id::VAR_SIDE => msg_id::SIDE_FROM->text(),
                            msg_id::VAR_WORD => $this->get_from()->dsp_id()
                        ]);
                    }
                } elseif ($this->from_id() < 0) {
                    $lnk = new triple($this->get_user());
                    $lnk->load_by_id($this->get_from()->obj_id());
                    if ($lnk->id() > 0) {
                        $this->set_from($lnk->phrase());
                        $this->get_from()->set_name($lnk->name());
                    } else {
                        $msg->add(msg_id::LOAD_TRIPLE_BY_ID_FAILED, [
                            msg_id::VAR_SIDE => msg_id::SIDE_FROM->text(),
                            msg_id::VAR_WORD => $this->get_from()->dsp_id()
                        ]);
                    }
                } else {
                    // if type is not (yet) set, create a dummy object to enable the selection
                    $phr = new phrase($this->get_user());
                    $this->set_from($phr);
                }
                log_debug('from ' . $this->get_from()->name());
            }
        }

        // test verb
        if (!$this->has_verb()) {
            log_err("The verb must be set before it can be loaded.", "triple->load_objects");
        }

        // load the "to" phrase
        if ($this->get_to() == null) {
            if ($this->to_id() == 0) {
                // set a dummy word
                $wrd_to = new word($this->get_user());
                $this->set_to($wrd_to->phrase());
            }
        } else {
            if ($this->to_id() <> 0 and !is_null($this->get_user()->id)) {
                if ($this->to_id() > 0) {
                    $wrd_to = new word($this->get_user());
                    $wrd_to->load_by_id($this->to_id());
                    if ($wrd_to->name() <> '') {
                        $this->set_to($wrd_to->phrase());
                        $this->get_to()->set_name($wrd_to->name());
                    } else {
                        $msg->add(msg_id::LOAD_WORD_BY_ID_FAILED, [
                            msg_id::VAR_SIDE => msg_id::SIDE_TO->text(),
                            msg_id::VAR_WORD => $this->get_from()->dsp_id()
                        ]);
                    }
                } elseif ($this->to_id() < 0) {
                    $lnk = new triple($this->get_user());
                    $lnk->load_by_id($this->get_to()->obj_id());
                    if ($lnk->id() > 0) {
                        $this->set_to($lnk->phrase());
                        $this->get_to()->set_name($lnk->name());
                    } else {
                        $msg->add(msg_id::LOAD_TRIPLE_BY_ID_FAILED, [
                            msg_id::VAR_SIDE => msg_id::SIDE_TO->text(),
                            msg_id::VAR_WORD => $this->get_from()->dsp_id()
                        ]);
                    }
                } else {
                    // if type is not (yet) set, create a dummy object to enable the selection
                    $phr_to = new phrase($this->get_user());
                    $this->set_to($phr_to);
                }
                log_debug('to ' . $this->get_to()->name());
            }
        }
        return $msg->is_ok();
    }

    /**
     * get the view object for this word
     */
    function reload_view(): ?view
    {
        $result = null;

        if ($this->view != null) {
            $result = $this->view;
        } else {
            if ($this->get_view_id() > 0) {
                $result = new view($this->get_user());
                if ($result->load_by_id($this->get_view_id())) {
                    $this->view = $result;
                    log_debug('for ' . $this->dsp_id() . ' is ' . $result->dsp_id());
                }
            }
        }

        return $result;
    }

    /**
     * @return true if no link objects is missing
     */
    private function has_objects(): bool
    {
        $result = true;
        if ($this->from_id() == 0) {
            $result = false;
        }
        if ($this->get_verb_id() == 0) {
            $result = false;
        }
        if ($this->to_id() == 0) {
            $result = false;
        }
        return $result;
    }

    /**
     * recursive function to include the foaf words for this triple
     */
    function wrd_lst(): word_list
    {
        log_debug('triple->wrd_lst ' . $this->dsp_id());
        $wrd_lst = new word_list($this->get_user());

        // add the "from" side
        if ($this->get_from() != null) {
            if ($this->from_id() > 0) {
                $wrd_lst->add($this->get_from()->obj());
            } elseif ($this->from_id() < 0) {
                $sub_wrd_lst = $this->get_from()->wrd_lst();
                foreach ($sub_wrd_lst->lst() as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The from phrase ' . $this->get_from()->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        // add the "to" side
        if ($this->get_to() != null) {
            if ($this->to_id() > 0) {
                $wrd_lst->add($this->get_to()->obj());
            } elseif ($this->to_id() < 0) {
                $sub_wrd_lst = $this->get_to()->wrd_lst();
                foreach ($sub_wrd_lst->lst() as $wrd) {
                    $wrd_lst->add($wrd);
                }
            } else {
                log_err('The to phrase ' . $this->get_to()->dsp_id() . ' should not have the id 0', 'triple->wrd_lst');
            }
        }

        log_debug($wrd_lst->name());
        return $wrd_lst;
    }


    /*
     * info
     */

    /**
     * check if the object can be added to the database
     * e.g. if from and to are valid
     * @param user_message|Message $msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @return bool true if everything has been fine
     */
    function check(user_message|Message $msg): bool
    {
        if ($this->needs_from()) {
            if ($this->get_from() == null) {
                $msg->add_id(msg_id::TRIPLE_FROM_PHRASE_MISSING);
            } else {
                if ($this->get_from()->id() == 0) {
                    if ($this->get_from()->name() == '') {
                        $msg->add_id(msg_id::TRIPLE_PHRASE_FROM_NAME_MISSING);
                    } else {
                        $msg->add_info_id(msg_id::TRIPLE_PHRASE_WITHOUT_DB_ID);
                    }
                }
            }
        }
        if ($this->get_to() == null) {
            $msg->add_id(msg_id::TRIPLE_TO_PHRASE_MISSING);
        } else {
            if ($this->get_to()->id() == 0) {
                if ($this->get_to()->name() == '') {
                    $msg->add_id(msg_id::TRIPLE_PHRASE_TO_NAME_MISSING);
                } else {
                    $msg->add_info_id(msg_id::TRIPLE_PHRASE_WITHOUT_DB_ID);
                }
            }
        }
        return $msg->is_ok();
    }

    /**
     * if needed, reverse the order if the user has entered it the other way round
     * e.g. "Cask Flow Statement" "contains" "Taxes" instead of "Taxes" "is part of" "Cask Flow Statement"
     */
    private function check_order(): void
    {
        if ($this->get_verb_id() < 0) {
            $to = $this->get_to();
            $this->set_to($this->get_from());
            $this->set_verb_id($this->get_verb_id() * -1);
            /*
             * TODO remove
            if ($this->has_verb()) {
                $this->verb->set_name($this->verb->reverse());
            }
            */
            $this->set_from($to);
            log_debug('reversed');
        }
    }

    /**
     * check if the triple might be added to the database
     * if all related objects have been added to the database
     * @param user_message|Message $msg including suggested solutions if something is missing e.g. a linked object
     * @return bool false if a mandatory var of the triple is not yet set that will not be added if the linked phrased are saved
     */
    function can_be_ready(user_message|Message $msg): bool
    {
        parent::can_be_ready($msg);
        $this->check($msg);
        return $msg->is_ok();
    }

    /**
     * check if the triple can be added to the database
     * @param user_message|Message $msg including suggested solutions if something is missing, e.g. a linked object
     * @return bool true if the triple can be added to the database
     */
    function db_ready(user_message|Message $msg): bool
    {
        parent::db_ready($msg);
        $this->check($msg);
        return $msg->is_ok();
    }

    /**
     * check if the word in the database needs to be updated
     * e.g. for import if this word has only the name set, the protection should not be updated in the database
     *
     * @param triple|CombineObject|IdObject $db_obj the word as saved in the database
     * @return bool true if this word has info that should be saved in the database
     */
    function needs_db_update(triple|CombineObject|IdObject $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        if ($this->get_verb_id() > 0) {
            if ($this->get_verb_id() != $db_obj->get_verb_id()) {
                $result = true;
            }
        }
        if ($this->name_given != null) {
            if ($this->name_given != $db_obj->name_given) {
                $result = true;
            }
        }
        if ($this->weight != null) {
            if ($this->weight != $db_obj->weight) {
                $result = true;
            }
        }
        if ($this->usage != null) {
            if ($this->usage != $db_obj->usage) {
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


    /*
     * internal
     */

    /**
     * @return string the generated name based on the linked phrases
     */
    function generate_name(): string
    {
        global $sys;
        if ($this->get_verb_id() == $sys->typ_lst->vrb->id(verbs::IS) and $this->get_from()->name() != '' and $this->get_to()->name() != '') {
            // use the user defined description
            return $this->get_from()->name() . ' (' . $this->get_to()->name() . ')';
        } elseif ($this->get_from()->name() != '' and $this->get_verb_name() != '' and $this->get_to()->name() != '') {
            // or use the standard generic description
            return $this->get_from()->name() . ' ' . $this->get_verb_name() . ' ' . $this->get_to()->name();
        } elseif ($this->get_from()->name() != '' and $this->get_to()->name() != '') {
            // or use the short generic description
            return $this->get_from()->name() . ' ' . $this->get_to()->name();
        } else {
            // or use the name as fallback
            if ($this->name_given() == null) {
                return '';
            } else {
                return $this->name_given();
            }
        }
    }


    /*
     * save helper
     */

    /**
     * preform the pre save checks which means
     * for these named objects check if the user has requested to use a preserved name
     * and if yes return a message and a suggested solution to the user
     *
     * @param user_message $usr_msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @return bool true if no preserved triple name is used and the triple can be saved to the database
     */
    protected function check_save(user_message $usr_msg): bool
    {
        return $this->check_preserved($usr_msg);
    }

    /**
     * check if the user has requested to use a preserved name for the sandbox object
     * and if yes return a message to the user
     *
     * @param user_message $msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * * @return bool true if everything has been fine
     */
    protected function check_preserved(user_message $msg): bool
    {
        global $sys;
        global $mtr;
        $usr = $sys?->usr_req;

        // init
        $msg_res = $mtr->txt(msg_id::IS_RESERVED);
        $msg_for = $mtr->txt(msg_id::RESERVED_NAME);
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        // system users are always allowed to add objects e.g. for the system views
        if (!$usr->is_system()) {
            if (in_array($this->name(), $this->reserved_names())) {
                // the admin user needs to add the read test objects during initial load
                if ($usr->is_admin() and !in_array($this->name(), $this->fixed_names())) {
                    $msg->add(msg_id::GROUP_IS_RESERVED, [
                        msg_id::VAR_NAME => $this->name(),
                        msg_id::VAR_JSON_TEXT => $msg_res . ' ' . $class_name . ' ' . $msg_for
                    ]);
                }
            }
        }
        return $msg->is_ok();
    }


    /*
     * sandbox
     */

    /**
     * true if no one has used this triple
     */
    function not_used(): bool
    {
        log_debug('triple->not_used (' . $this->id() . ')');

        // TODO review: maybe replace by a database foreign key check
        return $this->not_changed();
    }

    /**
     * @returns bool true if no other user has modified the triple
     */
    function not_changed(): bool
    {
        log_debug('triple->not_changed (' . $this->id() . ') by someone else than the owner (' . $this->owner_id() . ')');

        global $db_con;
        $result = true;

        $lib = new library();
        if ($this->id() == 0) {
            log_err('The id must be set to check if the triple has been changed');
        } else {
            $qp = $this->not_changed_sql($db_con->sql_creator());
            $db_row = $db_con->get1($qp);
            if (key_exists(user_db::FLD_ID, $db_row)) {
                if ($db_row[user_db::FLD_ID] > 0) {
                    $result = false;
                }
            }
        }
        log_debug('triple->not_changed for ' . $this->id() . ' is ' . $lib->dsp_bool($result));
        return $result;
    }

    /**
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     *                 to check if the triple has been changed
     */
    function not_changed_sql(sql_creator $sc): sql_par
    {
        $sc->set_class(triple::class);
        return $sc->load_sql_not_changed($this->id(), $this->owner_id());
    }


    /*
     * log
     */

    /**
     * set the log entry parameter for a new value
     * e.g. that the user can see "added ABB is a company"
     */
    function log_link_add(): change_link
    {
        log_debug('triple->log_link_add for ' . $this->dsp_id() . ' by user "' . $this->get_user()->name . '"');
        $usr_msg = new user_message();
        $log = new change_link($this->get_user());
        $log->set_action(change_actions::ADD);
        $log->set_table(change_tables::TRIPLE);
        $log->new_from = $this->get_from();
        $log->new_link = $this->get_verb();
        $log->new_to = $this->get_to();
        $log->row_id = 0;
        $log->add($usr_msg);

        return $log;
    }

    /**
     * set the main log entry parameters for updating the triple itself
     */
    function log_upd(): change_link
    {
        $log = new change_link($this->get_user());
        $log->set_action(change_actions::UPDATE);
        if ($this->can_change()) {
            $log->set_table(change_tables::TRIPLE);
        } else {
            $log->set_table(change_tables::TRIPLE_USR);
        }

        return $log;
    }

    /**
     * set the log entry parameter to delete a triple
     * e.g. that the user can see "ABB is a company not anymore"
     */
    function log_del_link(): change_link
    {
        log_debug('triple->log_link_del for ' . $this->dsp_id() . ' by user "' . $this->get_user()->name . '"');
        $usr_msg = new user_message();
        $log = new change_link($this->get_user());
        $log->set_action(change_actions::DELETE);
        $log->set_table(change_tables::TRIPLE);
        $log->old_from = $this->get_from();
        $log->old_link = $this->get_verb();
        $log->old_to = $this->get_to();
        $log->row_id = $this->id();
        $log->add($usr_msg);

        return $log;
    }

    /**
     * set the main log entry parameters for updating one display triple field
     */
    function log_upd_field(): change
    {
        $log = new change($this->get_user());
        $log->set_action(change_actions::UPDATE);
        if ($this->can_change()) {
            $log->set_table(change_tables::TRIPLE);
        } else {
            $log->set_table(change_tables::TRIPLE_USR);
        }

        return $log;
    }

    /**
     * check if a term with the unique name already exists
     * returns null if no similar object is found
     * or returns the term with the same unique key that is not the actual object
     * similar to sandbox named get_similar but
     *
     * @param string $name if given the specific name to check
     * @return term|null a filled object that has the same name
     *                or a sandbox object with id() = 0 if nothing similar has been found
     */
    function get_similar_named(string $name = ''): ?term
    {
        $trm = new term($this->get_user());
        if ($name != '') {
            $trm->load_by_name($name);
        } else {
            if ($this->name() != '') {
                $trm->load_by_name($this->name());
                if ($trm->id_obj() == 0) {
                    $similar_trp = new triple($this->get_user());
                    $similar_trp->load_by_name_generated($this->name());
                    if ($similar_trp->id() != 0) {
                        $trm = $similar_trp->term();
                    }
                }
            }
        }
        if ($trm->id_obj() == 0 or $trm->id_obj() == $this->id()) {
            $trm = null;
        }

        return $trm;
    }


    /*
     * save
     */

    /**
     * add a new triple to the database
     * @param user_message $msg with status ok
     *                              or if something went wrong
     *                              the message that should be shown to the user
     *                              including suggested solutions
     * @return bool true if everything has been fine
     */
    function add(user_message $msg): bool
    {
        log_debug('triple->add new triple for "' . $this->get_from()->name() . '" ' . $this->get_verb_name() . ' "' . $this->get_to()->name() . '"');

        global $db_con;

        // TODO review: do not set the generated name if it matches the name
        $this->set_names();
        $sc = $db_con->sql_creator();
        $qp = $this->sql_insert($sc, $msg, new sql_type_list([sql_type::LOG]));
        if ($msg->is_ok()) {
            $msg_txt = 'add and log ' . $this->dsp_id();
            if ($db_con->insert($qp, $msg_txt, $msg)) {
                $this->id = $msg->get_row_id();
            }
        }

        return $msg->is_ok();
    }

    /**
     * check additional if the opposite triple already exists and if yes, ask for confirmation
     *
     * @param user_message $msg the user who has requested the update and the object to collect the potential reject messages
     * @returns triple|type_object|sandbox|null a filled object that has the same name, links or reverse links
     */
    function get_similar(user_message $msg): triple|type_object|sandbox|null
    {
        $sim = parent::get_similar($msg);

        // check if the opposite triple already exists and if yes, ask for confirmation
        if ($this->id() == 0) {
            log_debug('check if a new triple for "' . $this->get_from()->name() . '" and "' . $this->get_to()->name() . '" needs to be created');
            // check if the reverse triple is already in the database
            $db_chk_rev = $this->clone_reset();
            $chk_msg = $msg->clone_reset();
            $db_chk_rev->load_standard_by_type_link($this->to_id(), $this->predicate_id(), $this->from_id(), $chk_msg);
            if ($db_chk_rev->id() > 0) {
                $sim = $db_chk_rev;

                $msg->add(msg_id::REVERSE_ALREADY_EXISTS, [
                    msg_id::VAR_SOURCE_NAME => $this->get_from()->name(),
                    msg_id::VAR_VERB_NAME => $this->get_verb_name(),
                    msg_id::VAR_NAME => $this->get_to()->name(),
                ]);
            }
        }

        return $sim;
    }

    /**
     * check if the id parameters are supposed to be changed
     * @param triple|sandbox_named|db_object_seq_id $db_rec the object data as it is now in the database
     * @return bool true if one of the object id fields has been changed
     */
    function is_key_updated(triple|sandbox_named|db_object_seq_id $db_rec): bool
    {
        $result = parent::is_key_updated($db_rec);

        if ($db_rec->name_given <> $this->name_given) {
            $result = True;
        }
        if ($db_rec->name_generated <> $this->name_generated) {
            $result = True;
        }

        return $result;
    }


    /*
     * save helper
     */

    /**
     * @return array with the reserved triple names
     */
    protected function reserved_names(): array
    {
        return triples::RESERVED_NAMES;
    }

    /**
     * @return array with the fixed triple names for db read testing
     */
    protected function fixed_names(): array
    {
        return triples::FIXED_NAMES;
    }

    /**
     * delete the phrase groups which where this triple is used
     *
     * @param user_message $usr_msg the message for the user why deleting the triple links has failed and a suggested solution
     * @return bool true if the triple links has been deleted
     */
    function del_links(user_message $usr_msg): bool
    {
        $usr_msg = new user_message();

        // collect all phrase groups where this triple is used
        // TODO Prio 2 activate
        //$grp_lst = new group_list($this->get_user());
        //$grp_lst->load_by_phr($this->phrase());

        // collect all values related to this triple
        $val_lst = new value_list($this->get_user());
        $val_lst->load_by_phr($this->phrase());

        // if there are still values, ask if they really should be deleted
        if ($val_lst->has_values()) {
            $val_lst->del($usr_msg);
        }

        // if the user confirms the deletion, the removal process is started with a retry of the triple deletion at the end
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
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all($sc_par_lst),
            [
                phrase::FLD_TYPE,
                verb_db::FLD_ID,
                triple_db::FLD_NAME_GIVEN,
                triple_db::FLD_NAME_AUTO,
                triple_db::FLD_WIGHT,
                sql_db::FLD_USAGE,
                sql_db::FLD_IMPACT,
                triple_db::FLD_VIEW
            ],
            parent::db_fields_all_sandbox()
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param triple|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        triple|db_object_seq_id $obj,
        user_message            $msg,
        sql_type_list           $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $is_insert = $sc_par_lst->is_insert();
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $table_id = $sc->table_id($this::class);

        // should be corresponding with the list of triple object vars
        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);

        // for triple the type is the phrase type
        // the type is object-specific that why it is not part of sandbox_link_types
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

        // the link type cannot be changed by the user, because this would be another link
        if (!$usr_tbl) {
            if ($obj->get_verb_id() !== $this->get_verb_id()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . verb_db::FLD_ID,
                        $sys->typ_lst->cng_fld->id($table_id . verb_db::FLD_ID),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                global $sys;
                if ($this->get_verb_id() < 0) {
                    $msg->add(msg_id::VERB_MISSING, [
                        msg_id::VAR_TYPE => $this->get_verb_name(),
                        msg_id::VAR_NAME => $this->dsp_id()
                    ]);
                }
                $lst->add_type_field(
                    verb_db::FLD_ID,
                    verb_db::FLD_NAME,
                    $this->get_verb_id(),
                    $obj->get_verb_id(),
                    $sys->typ_lst->vrb
                );
            }
        } else {
            // add the from and to fields even if the objects are the same in case of an insert exclude to identify the rows
            if ($is_insert) {
                // TODO check how to handle if the standard
                // $sbx can in this case be e.g. the standard object and $this is the object updated by the user
                if ($this->is_excluded() and !$obj->is_excluded()) {
                    // the verb field is added for triple exclude insert statements
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . verb_db::FLD_ID,
                            $sys->typ_lst->cng_fld->id($table_id . verb_db::FLD_ID),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    global $sys;
                    if ($obj->get_verb_id() < 0) {
                        $msg->add(msg_id::VERB_MISSING, [
                            msg_id::VAR_TYPE => $obj->get_verb_name(),
                            msg_id::VAR_NAME => $obj->dsp_id()
                        ]);
                    }
                    $lst->add_type_field(
                        verb_db::FLD_ID,
                        verb_db::FLD_NAME,
                        null,
                        $obj->get_verb_id(),
                        $sys->typ_lst->vrb
                    );
                    // TODO check if the excluded field is not already added by the sandbox function
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_EXCLUDED,
                            $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_EXCLUDED),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    $lst->add_field(
                        sql_db::FLD_EXCLUDED,
                        1,
                        sql_db::FLD_EXCLUDED_SQL_TYP
                    );
                } elseif (!$this->is_excluded() and $obj->is_excluded()) {
                    if ($do_log) {
                        $lst->add_field(
                            sql::FLD_LOG_FIELD_PREFIX . verb_db::FLD_ID,
                            $sys->typ_lst->cng_fld->id($table_id . verb_db::FLD_ID),
                            change::FLD_FIELD_ID_SQL_TYP
                        );
                    }
                    global $sys;
                    if ($this->get_verb_id() < 0) {
                        $msg->add(msg_id::VERB_MISSING, [
                            msg_id::VAR_TYPE => $this->get_verb_name(),
                            msg_id::VAR_NAME => $this->dsp_id()
                        ]);
                    }
                    $lst->add_type_field(
                        verb_db::FLD_ID,
                        verb_db::FLD_NAME,
                        $this->get_verb_id(),
                        null,
                        $sys->typ_lst->vrb
                    );
                }
            }
        }
        // TODO check if the excluded field is not already added by the sandbox function
        if ($obj->excluded !== $this->excluded) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_EXCLUDED,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_EXCLUDED),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_EXCLUDED,
                $this->excluded,
                sql_db::FLD_EXCLUDED_SQL_TYP,
                $obj->excluded,
            );
        }
        if ($obj->name_given() !== $this->name_given()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . triple_db::FLD_NAME_GIVEN,
                    $sys->typ_lst->cng_fld->id($table_id . triple_db::FLD_NAME_GIVEN),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                triple_db::FLD_NAME_GIVEN,
                $this->name_given(),
                triple_db::FLD_NAME_GIVEN_SQL_TYP,
                $obj->name_given()
            );
        }
        // TODO add test case
        // if the user has not changed the name or the give name the generated name does not need to be taken into account
        if (!$usr_tbl and ($obj->name != '' or $obj->name_given() != '')) {
            if ($obj->name_generated() !== $this->name_generated()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . triple_db::FLD_NAME_AUTO,
                        $sys->typ_lst->cng_fld->id($table_id . triple_db::FLD_NAME_AUTO),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                $lst->add_field(
                    triple_db::FLD_NAME_AUTO,
                    $this->name_generated(),
                    triple_db::FLD_NAME_AUTO_SQL_TYP,
                    $obj->name_generated()
                );
            }
        }
        if ($obj->weight !== $this->weight) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . triple_db::FLD_WIGHT,
                    $sys->typ_lst->cng_fld->id($table_id . triple_db::FLD_WIGHT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                triple_db::FLD_WIGHT,
                $this->weight,
                triple_db::FLD_WEIGHT_SQL_TYP,
                $obj->weight
            );
        }
        if ($obj->usage !== $this->usage) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_USAGE,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_USAGE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_USAGE,
                $this->usage,
                sql_db::FLD_USAGE_SQL_TYP,
                $obj->usage
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
        if ($obj->get_view_id() !== $this->get_view_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . triple_db::FLD_VIEW,
                    $sys->typ_lst->cng_fld->id($table_id . triple_db::FLD_VIEW),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                triple_db::FLD_VIEW,
                view_db::FLD_NAME,
                $this->view,
                $obj->view
            );
        }
        // TODO add ref list
        return $lst->merge($this->db_changed_sandbox_list($obj, $sc_par_lst));
    }



    /*
     * debug
     */

    /**
     * @return string with the unique id fields
     * TODO Prio 1 never use functions in dsp_id t avoid endless loops
     * TODO check if $this->load_objects(); needs to be called from the calling function upfront
     */
    function dsp_id(): string
    {
        global $sys;

        $result = '';

        $vrb_name = '';
        if ($this->predicate_id != null) {
            $vrb = $sys->typ_lst->vrb->get($this->predicate_id);
            if ($vrb != null) {
                $vrb_name = $vrb->name;
            }
        }

        $from = $this->fob;
        if ($from::class == phrase::class) {
            $from = $from->obj;
        }
        $to = $this->tob;
        if ($to::class == phrase::class) {
            $to = $to->obj;
        }

        if ($from?->name <> '' and $vrb_name <> '' and $to?->name <> '') {
            $result .= '"' . $from?->name . '" "'; // e.g. Australia
            $result .= $vrb_name . '" "'; // e.g. is a
            $result .= $to?->name . '"';       // e.g. country
        } elseif ($from?->name <> '' and $to?->name <> '') {
            $result .= '"' . $from?->name . '" "'; // e.g. Australia
            $result .= 'id ' . $this->predicate_id . '" "'; // e.g. is a
            $result .= $to?->name . '"';       // e.g. country
        } elseif ($this->name_given() != '') {
            $result .= $this->name_given(); // e.g. canton Zurich
        } elseif ($this->name() != '') {
            $result .= $this->name();
        }
        $result .= ' (' . $this->from_id() . ',' . $this->get_verb_id() . ',' . $this->to_id();
        if ($this->id() > 0) {
            $result .= ' -> triple_id ' . $this->id() . ')';
        }
        $result .= $this->dsp_id_user();
        return $result;
    }

    /**
     * either the user edited description
     * or the generic name e.g. Australia is a country
     * or for the verb is 'is' the category in brackets e.g. Zurich (canton) or Zurich (city)
     */
    function name(bool $ignore_excluded = false): string|null
    {
        $result = '';

        if (!$this->is_excluded() or $ignore_excluded) {
            if ($this->name != null) {
                // use the object
                $result = $this->name;
            } elseif ($this->name_given != null) {
                // use the user defined description
                $result = $this->name_given;
            } else {
                // or use the standard generic description
                // but do not generate a new generated name for user sandbox compare
                $result = $this->name_generated;
            }
        }

        return $result;
    }

    /**
     * either the user edited description
     * or the generic name e.g. Australia is a country
     * or for the verb is 'is' the category in brackets e.g. Zurich (canton) or Zurich (city)
     */
    function name_ex_generated(bool $ignore_excluded = false): string
    {
        $result = '';

        if (!$this->is_excluded() or $ignore_excluded) {
            if ($this->name <> '') {
                // use the object
                $result = $this->name;
            } elseif ($this->name_given() <> '') {
                // use the user defined description
                $result = $this->name_given();
            }
        }

        return $result;
    }

}
