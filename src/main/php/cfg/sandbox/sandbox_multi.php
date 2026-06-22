<?php

/*

    model/sandbox/sandbox_multi.php - the superclass for handling user-specific objects including the database saving
    -------------------------------

    This superclass is used by the classes values and results to enable user-specific changes
    similar to sandbox.php but for database objects that have custom prime id
    TODO should be merged once php allows aggregating extends e.g. sandbox extends db_object, db_user_object

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this multi table sandbox object
    - construct and map: including the mapping of the db row to this formula object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - set and get:       to capsule the vars from unexpected changes
    - sql write fields:  field list for writing to the database
    - sql helper:        support function for the sql creation
    - load:              database access object (DAO) functions
    - load types:        load related types
    - im- and export:    create an export object and set the vars from an import object
    - info:              functions to make code easier to read
    - delete:            manage to remove from the database
    - log:               write the changes to the log
    - internal:          internal info functions
    - internal check:    for testing during development


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

// TODO align the function return types with the source (ref) object
// TODO use the user sandbox also for the word object
// TODO check if handling of negative ids is correct
// TODO split into a link and a named user sandbox object to always use the smallest possible object

namespace Zukunft\ZukunftCom\main\php\cfg\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_multi_user.php';
//include_once paths::MODEL_COMPONENT . 'component.php';
//include_once paths::MODEL_COMPONENT . 'component_link.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_ELEMENT . 'element.php';
//include_once paths::EXPORT . 'export_type_list.php';
//include_once paths::MODEL_FORMULA . 'formula.php';
//include_once paths::MODEL_FORMULA . 'formula_db.php';
//include_once paths::MODEL_FORMULA . 'formula_link.php';
//include_once paths::MODEL_FORMULA . 'formula_link_type.php';
//include_once paths::MODEL_GROUP . 'group.php';
//include_once paths::MODEL_GROUP . 'group_db.php';
//include_once paths::MODEL_GROUP . 'group_id.php';
//include_once paths::MODEL_GROUP . 'result_id.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'db_object_multi.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
//include_once paths::MODEL_LOG . 'change_link.php';
include_once paths::MODEL_LOG . 'change_log.php';
include_once paths::MODEL_LOG . 'change_value.php';
include_once paths::MODEL_LOG . 'change_values_big.php';
include_once paths::MODEL_LOG . 'change_values_time_big.php';
include_once paths::MODEL_LOG . 'change_values_text_big.php';
include_once paths::MODEL_LOG . 'change_values_geo_big.php';
include_once paths::MODEL_LOG . 'change_values_norm.php';
include_once paths::MODEL_LOG . 'change_values_time_norm.php';
include_once paths::MODEL_LOG . 'change_values_text_norm.php';
include_once paths::MODEL_LOG . 'change_values_geo_norm.php';
include_once paths::MODEL_LOG . 'change_values_prime.php';
include_once paths::MODEL_LOG . 'change_values_time_prime.php';
include_once paths::MODEL_LOG . 'change_values_text_prime.php';
include_once paths::MODEL_LOG . 'change_values_geo_prime.php';
include_once paths::MODEL_LOG . 'changes_big.php';
include_once paths::MODEL_LOG . 'changes_norm.php';
//include_once paths::MODEL_PHRASE . 'phrase.php';
//include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_list.php';
include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_VALUE . 'value.php';
//include_once paths::MODEL_VALUE . 'value_base.php';
//include_once paths::MODEL_VALUE . 'value_db.php';
include_once paths::MODEL_VERB . 'verb.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_WORD . 'word.php';
//include_once paths::MODEL_WORD . 'triple.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'Message.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\group\group_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_multi;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_multi_user;
use Zukunft\ZukunftCom\main\php\cfg\element\element;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_type;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\group\group_id;
use Zukunft\ZukunftCom\main\php\cfg\group\result_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\log\change_value;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_geo_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_geo_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_geo_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_text_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_text_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_text_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_big;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_norm;
use Zukunft\ZukunftCom\main\php\cfg\log\change_values_time_prime;
use Zukunft\ZukunftCom\main\php\cfg\log\changes_big;
use Zukunft\ZukunftCom\main\php\cfg\log\changes_norm;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_db;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\value\value_base;
use Zukunft\ZukunftCom\main\php\cfg\value\value_db;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types as protect_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\share_types as share_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Exception;

class sandbox_multi extends db_object_multi_user
{

    /*
     * db const
     */

    // database and JSON object field names used in many user sandbox objects
    // the id field is not included here because it is used for the database relations and should be object specific
    // e.g. always "word_id" instead of simply "id"
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_EXCLUDED = 'excluded';    // field name used to delete the object only for one user
    const sql_field_type FLD_EXCLUDED_SQL_TYP = sql_field_type::BOOL;
    const string FLD_SHARE = "share_type_id";  // field name for the share permission
    const sql_field_type FLD_SHARE_SQL_TYP = sql_field_type::INT_SMALL;
    const string FLD_PROTECT = "protect_id";   // field name for the protection level
    const sql_field_type FLD_PROTECT_SQL_TYP = sql_field_type::INT_SMALL;
    // database fields used for user values and results
    const string FLD_VALUE = 'numeric_value';
    const string FLD_LAST_UPDATE = 'last_update';

    // dummy arrays that should be overwritten by the child object
    const array FLD_NAMES = array();
    const array FLD_NAMES_USR = array();
    // combine FLD_NAMES_NUM_USR_SBX and FLD_NAMES_NUM_USR_ONLY_SBX just for shorter code
    const array FLD_NAMES_NUM_USR = array(
        sql_db::FLD_EXCLUDED,
        self::FLD_SHARE,
        self::FLD_PROTECT
    );
    // all database sandbox field names used to identify if there are some user-specific changes so excluding the id fields
    const array ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );

    // list of all user sandbox database types with a standard ID
    // so exclude values and result TODO check missing owner for values and results
    const array DB_TYPES = array(
        word::class,
        triple::class,
        formula::class,
        formula_link::class,
        view::class,
        component::class,
        component_link::class
    );


    /*
     * object vars
     */

    // overwrite the id set method to keep the group id in sync
    public string|int $id {
        set {
            $this->id = $value;
            if ($this->grp()->id != $value) {
                $this->grp()->set_id($value);
            }
            $this->set_modified();
        }
    }
    // fields to define the object; should be set in the constructor of the child object
    public bool $rename_can_switch = True; // true if renaming an object can switch to another object with the new name

    // database fields that are used in all objects and that have a specific behavior
    public ?int $usr_cfg_id = null;    // the database id if there is already some user-specific configuration for this object
    public ?int $owner_id = null;      // the user id of the person who created the object, which is the default object
    private ?int $share_id = null;      // id for public, personal, group or private
    private ?int $protection_id = null; // id for no, user, admin or full protection
    public ?bool $excluded = null;       // the user sandbox for object is implemented, but can be switched off for the complete instance
    // but for calculation, use and display an excluded should not be used
    // when loading the word and saving the excluded field is handled as a normal user sandbox field,
    // but for calculation, use and display an excluded should not be used

    /*
     * dummy var because many child objects use a type_id and to enable to add the common code here
     *
     * could and should be moved to a user_sandbox_type_extension object
     * as soon as php allows something like 'extends _sandbox_named and user_sandbox_type_extension'
     *
     * database id of the type used for named user sandbox objects with predefined functionality
     * such as words, formulas, values, terms and view component links
     * because all types are preloaded with the database id the name and code id can fast be received
     * the id of the source type, view type, view component type or word type
     * e.g. to classify measure words
     * the id of the source type, view type, view component type or word type e.g. to classify measure words
     * or the formula type to link special behavior to special formulas like "this" or "next"
     */
    public ?int $type_id = null;


    /*
     * construct and map
     */

    /**
     * all user sandbox object are user-specific, that's why the user is always set
     * and most user sandbox objects are named object
     * but this is in many cases be overwritten by the child object
     * @param user $usr the user how has requested to see his view on the object
     */
    function __construct(user $usr)
    {
        parent::__construct($usr);
    }

    /**
     * reset all object vars of this object to the null or default value
     * used e.g. the cleanup the object before the import mapping
     * @param bool $keep_user set to true to keep the original user
     */
    function reset(bool $keep_user = false): void
    {
        $this->id = 0;
        $this->usr_cfg_id = null;
        $this->set_owner_id(null);
        $this->set_share_id(null);
        $this->set_protection_id(null);
        $this->excluded = null;
    }

    /**
     * map the database fields to the object fields
     * to be extended by the child object
     * the parent row_mapper function should be used for all db_objects
     * this row_mapper_sandbox function should be used for all user sandbox objects
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $ext the table type e.g. to indicate if the id is int
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as set in the child class
     * @param bool $one_id_fld false if the unique database id is based on more than one field and due to that the database id should not be used for the object id
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper_sandbox_multi(
        ?array $db_row,
        string $ext,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = '',
        bool   $one_id_fld = true
    ): bool
    {
        if ($id_fld == '') {
            $id_fld = $this->id_field();
        }
        $result = parent::row_mapper_multi($db_row, $ext, $id_fld, $one_id_fld);
        if ($result) {
            $this->set_owner_id($db_row[user_db::FLD_ID]);
            // e.g. the list of names does not include the field excluded
            // TODO instead the excluded rows are filtered out on SQL level
            if (array_key_exists(sandbox_multi::FLD_EXCLUDED, $db_row)) {
                $this->set_excluded($db_row[sql_db::FLD_EXCLUDED]);
            }
            if (!$load_std) {
                if (array_key_exists(sandbox::FLD_CHANGE_USER, $db_row)) {
                    $this->usr_cfg_id = $db_row[sandbox::FLD_CHANGE_USER];
                }
            }
            if ($allow_usr_protect) {
                $this->row_mapper_usr($db_row, $id_fld);
            } else {
                $this->row_mapper_std();
            }
        }
        return $result;
    }

    /**
     * map the standard user sandbox database fields to this user-specific object
     *
     * @param array $db_row with the data loaded from the database
     * @return void
     */
    function row_mapper_usr(array $db_row): void
    {
        if (array_key_exists(self::FLD_SHARE, $db_row)) {
            $this->share_id = $db_row[self::FLD_SHARE];
        }
        if (array_key_exists(self::FLD_PROTECT, $db_row)) {
            $this->protection_id = $db_row[self::FLD_PROTECT];
        }
    }

    /**
     * map the standard user sandbox database fields to this default object for all users
     *
     * @return void
     */
    function row_mapper_std(): void
    {
        global $sys;
        $this->share_id = $sys->typ_lst->shr_typ->id(share_type_shared::PUBLIC);
        $this->protection_id = $sys->typ_lst->ptc_typ->id(protect_type_shared::NO_PROTECT);
    }

    /**
     * fill the vars with this sandbox object based on the given api json array
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $msg if the mapping is incomplete the human-readable message what happened and how to solve it
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $msg): bool
    {
        // make sure that there are no unexpected leftovers
        $usr = $this->get_user();
        $this->reset();
        $this->set_user($usr);

        if (array_key_exists(json_fields::SHARE, $api_json)) {
            $this->share_id = $api_json[json_fields::SHARE];
        }
        if (array_key_exists(json_fields::PROTECTION, $api_json)) {
            $this->protection_id = $api_json[json_fields::PROTECTION];
        }

        return $msg->is_ok();
    }

    /**
     * function to import the core user sandbox object values from a json string
     * e.g. the share and protection settings
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

        parent::import_mapper($in_ex_json, $msg, $dto);

        if (key_exists(json_fields::SHARE, $in_ex_json)) {
            $this->share_id = $sys->typ_lst->shr_typ->id(
                $in_ex_json[json_fields::SHARE]);
            if ($this->share_id < 0) {
                $lib = new library();
                $msg->add(msg_id::SHARE_TYPE_NOT_EXPECTED, [
                    msg_id::VAR_NAME => $in_ex_json[json_fields::SHARE],
                    msg_id::VAR_JSON_TEXT => $lib->dsp_array($in_ex_json)
                ]);
            }
        }
        if (key_exists(json_fields::PROTECTION, $in_ex_json)) {
            $this->protection_id = $sys->typ_lst->ptc_typ->id(
                $in_ex_json[json_fields::PROTECTION]);
            if ($this->protection_id < 0) {
                $lib = new library();
                $msg->add(msg_id::PROTECTION_TYPE_NOT_EXPECTED, [
                    msg_id::VAR_NAME => $in_ex_json[json_fields::PROTECTION],
                    msg_id::VAR_JSON_TEXT => $lib->dsp_array($in_ex_json)
                ]);
            }
        }

        return $msg->is_ok();
    }


    /*
     * api
     */

    /**
     * create the array for the api message
     * which is on this level the same as the export json array
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        global $sys;

        $vars = [];

        // add the share type
        if ($this->share_id != null
            and $this->share_id > 0
            and $this->share_id <> $sys->typ_lst->shr_typ->id(share_type_shared::PUBLIC)) {
            $vars[json_fields::SHARE] = $this->share_id;
        }

        // add the protection type
        if ($this->protection_id != null
            and $this->protection_id > 0
            and $this->protection_id <> $sys->typ_lst->ptc_typ->id(protect_type_shared::NO_PROTECT)) {
            $vars[json_fields::PROTECTION] = $this->protection_id;
        }

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the vars of this object based on json string from the frontend object
     * @param string $api_json
     * @param user_message $usr_msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function set_from_api(string $api_json, user_message $usr_msg): bool
    {
        return $this->api_mapper(json_decode($api_json, true), $usr_msg);
    }

    /**
     * set the excluded field from a database value
     * with postgres and MySQL this is pretty strait forward so more to prevent future issues
     *
     * @param bool $db_val the value from the database row array
     * @return void
     */
    function set_excluded(?bool $db_val): void
    {
        if ($db_val == null) {
            $this->excluded = false;
        } else {
            $this->excluded = $db_val;
        }
    }

    /**
     * set excluded to 'true' to switch off the usage of this user sandbox object
     * @return void
     */
    function exclude(): void
    {
        $this->excluded = true;
    }

    /**
     * set excluded to 'false' to switch on the usage of this user sandbox object
     * @return void
     */
    function include(): void
    {
        $this->excluded = false;
    }

    /**
     * @return bool|null true if the user does not want to use this object at all
     */
    function is_excluded(): ?bool
    {
        return $this->excluded;
    }

    /**
     * @return group an empty group in this parent object, but overwritten by the child objects
     */
    function grp(): group
    {
        log_err('dummy grp() function called in sandbox_multi, which should never happen');
        return new group($this->get_user());
    }

    function set_owner_id(?int $id): void
    {
        $this->owner_id = $id;
    }

    function owner_id(): ?int
    {
        return $this->owner_id;
    }

    /**
     * TODO use a user list cache
     * @return user|null the person who has the permission to change the standard object
     */
    function owner(): ?user
    {
        $owner = null;
        if ($this->owner_id != null) {
            $owner = new user();
            $owner->load_by_id($this->owner_id);
        }
        return $owner;
    }

    function set_share_id(?int $id): void
    {
        $this->share_id = $id;
    }

    function share_id(): ?int
    {
        return $this->share_id;
    }

    function set_protection_id(?int $id): void
    {
        $this->protection_id = $id;
    }

    function set_protection_by_code_id(?string $code_id): void
    {
        global $sys;
        $this->set_protection_id($sys->typ_lst->ptc_typ->id($code_id));
    }

    function protection_id(): ?int
    {
        return $this->protection_id;
    }

    function set_source(source|null $src): void
    {
        log_warning('missing overwrite for set_source in ' . $this::class);
    }

    function source_id(): ?int
    {
        return null;
    }

    /**
     * @return bool true if the excluded field is set
     */
    function is_exclusion_set(): bool
    {
        if ($this->excluded == null) {
            return false;
        } else {
            return true;
        }
    }


    /*
     * load
     */

    /**
     * load one database row e.g. word, triple, formula, view or component from the database
     * for values and result the db key might be an 512-bit id or even a string
     * so for values and results the load_non_int_db_key function is used instead of this load function
     *
     * @param sql_par $qp the query parameters created by the calling function
     * @return int|string the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int|string
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper_sandbox_multi($db_row, $qp->ext);
        return $this->id;
    }

    /**
     * load the value parameters for all users
     * @param int|string $id the database row id to select the standard row
     * @param user_message $msg to collect the user messages
     * @return bool true if the standard object has been loaded
     */
    function load_standard(int|string $id, user_message $msg): bool
    {
        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_standard($id, $sc);

        $db_row = $db_con->get1($qp);
        if (!$this->row_mapper_sandbox_multi(
            $db_row, $qp->ext, true, false)) {
            $lib = new library();
            $msg->add(msg_id::LOAD_STANDARD_MAPPING_FAILED, [
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        return $msg->is_ok();
    }

    /**
     * create the SQL to load the single default value always by the id
     *
     * @param int|string $id the unique group id
     * @param sql_creator $sc with the target db_type set
     * @param array $fld_lst list of fields for the value, result or group
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_standard(
        int|string  $id,
        sql_creator $sc,
        array       $fld_lst = []
    ): sql_par
    {
        $sc_par_lst = new sql_type_list();
        $sc_par_lst->add($this->table_type());
        $sc_par_lst->add(sql_type::NORM);
        if ($this::class == group::class) {
            $id_ext = '';
        } else {
            $id_ext = $this->table_extension();
        }
        $qp = new sql_par($this::class, $sc_par_lst, '', $id_ext);
        $qp->name .= sql_db::FLD_ID;

        $sc->set_class($this::class, $sc_par_lst);
        $sc->set_name($qp->name);
        $sc->set_id_field($this->id_field());
        $sc->set_fields($fld_lst);
        $sc->set_usr($this->get_user()->id);
        $sc->add_where($this->id_field(), $this->id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        $qp->ext = $id_ext;

        return $qp;
    }

    /**
     * set the where condition and the final query parameters
     * for a value, result or group query
     *
     * @param sql_par $qp the query parameters fully set without the sql, par and ext
     * @param sql_creator $sc the sql creator with all parameters set
     * @param string $ext the table extension
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    protected function load_sql_set_where(sql_par $qp, sql_creator $sc, string $ext): sql_par
    {
        $this->load_sql_where_id($qp, $sc, true);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        $qp->ext = $ext;

        return $qp;
    }

    /**
     * set the where condition and the final query parameters
     * for a value, result or group query
     *
     * @param sql_par $qp the query parameters fully set without the sql, par and ext
     * @param sql_creator $sc the sql creator with all parameters set
     * @param bool $all true if all id fields should be used independent from the number of ids
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    protected function load_sql_where_id(sql_par $qp, sql_creator $sc, bool $all = false): sql_par
    {
        if ($this->is_prime() or $this->is_main()) {
            $fields = $this->id_names($all);
            $values = $this->id_lst();
            $pos = 0;
            foreach ($fields as $field) {
                $val_used = 0;
                if (array_key_exists($pos, $values)) {
                    $val_used = $values[$pos];
                }
                $sc->add_where($field, $val_used);
                $pos++;
            }
        } else {
            $sc->add_where(group_db::FLD_ID, $this->grp()->id);
        }
        return $qp;
    }

    /**
     * create the SQL to load the single default value always by something else than the main id
     * @param sql_creator $sc with the target db_type set
     * @param sql_par $qp the query parameters with the class and name already set
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_standard_by(sql_creator $sc, sql_par $qp): sql_par
    {
        $qp->name .= '_std';
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * function that can be overwritten or extended by the child object
     * @return array with all field names of the user sandbox object excluding the prime id field
     */
    protected function all_sandbox_fields(): array
    {
        return self::ALL_SANDBOX_FLD_NAMES;
    }

    /**
     * prepare the SQL parameter to load a single user-specific value
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param array $fields list of the fields from the child object
     * @param array $usr_fields list of the user specified fields from the child object
     * @param array $usr_num_fields list of the fields from the child object
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_fields(
        sql_creator $sc,
        string      $query_name,
        array       $fields,
        array       $usr_fields,
        array       $usr_num_fields,
    ): sql_par
    {
        $qp = parent::load_sql($sc, $query_name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields($fields);
        $sc->set_usr_fields($usr_fields);
        $sc->set_usr_num_fields($usr_num_fields);

        return $qp;
    }

    /**
     * create the SQL to load a sandbox object with numeric user-specific fields
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox_multi $sbx the name of the child class from where the call has been triggered
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_usr_num(sql_creator $sc, sandbox_multi $sbx, string $query_name): sql_par
    {
        $lib = new library();

        $qp = new sql_par($sbx::class);
        $qp->name .= $query_name;

        $sc->set_class($lib->class_to_name($sbx::class));
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields($sbx::FLD_NAMES);
        $sc->set_usr_fields($sbx::FLD_NAMES_USR);
        $sc->set_usr_num_fields($sbx::FLD_NAMES_NUM_USR);

        return $qp;
    }

    /**
     * create the SQL to load a single user-specific value
     * TODO replace by load_sql_usr or load_sql_usr_num
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_obj_vars(sql_creator $sc, string $class): sql_par
    {
        return new sql_par($class);
    }

    function load_owner(user_message $msg): bool
    {
        global $db_con;
        $result = false;

        if ($this->id() > 0) {

            // TODO: try to avoid using load_test_user
            if ($this->owner_id() > 0) {
                $usr = new user;
                if ($usr->load_by_id($this->owner_id())) {
                    $this->set_user($usr);
                    $result = true;
                }
            } else {
                // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
                $this->set_owner($this->get_user()->id, $msg);
            }
        }
        return $result;
    }

    /**
     * dummy function to get the missing objects from the database that is always overwritten by the child class
     * @returns bool  false if the loading has failed
     */
    function load_objects(): bool
    {
        $usr_msg = new user_message();
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'load_objects',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->is_ok();
    }


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param sandbox_multi|db_object_multi $std_obj the norm object as saved in the database
     * @param sandbox_multi|db_object_multi $result empty clone of the target user object
     * @return sandbox_multi|db_object_multi the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        sandbox_multi|db_object_multi $std_obj,
        sandbox_multi|db_object_multi $result
    ): sandbox_multi|db_object_multi
    {
        parent::delta($std_obj, $result);
        if ($std_obj->share_id !== $this->share_id) {
            $result->share_id = $this->share_id;
        }
        if ($std_obj->protection_id !== $this->protection_id) {
            $result->protection_id = $this->protection_id;
        }
        if ($std_obj->excluded !== $this->excluded) {
            $result->excluded = $this->excluded;
        }
        return $result;
    }

    function has_id(): bool
    {
        if (is_integer($this->id())) {
            if ($this->id() != 0) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($this->id() != '') {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @return bool true if a record is the standard for users that have not changed this object
     */
    function is_default(): bool
    {
        $result = false;
        if ($this->usr_cfg_id === null) {
            $result = true;
        }
        return $result;
    }



    /*
     * modify
     */

    /**
     * fill this sandbox object based on the given object
     * if the given type is not set (null) the type is not removed
     * if the given type is zero (not null) the type is removed
     *
     * @param sandbox_multi|db_object_multi $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(sandbox_multi|db_object_multi $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        // e.g. if the import contains the information that this object is excluded for one user this excluded setting should also be imported
        if ($this->excluded === null and $obj->excluded != null) {
            $this->set_excluded($obj->is_excluded());
        }
        if ($this->owner_id() === null and $obj->owner_id() != null) {
            $this->set_owner_id($obj->owner_id());
        }
        if ($this->share_id() === null and $obj->share_id() != null) {
            $this->set_share_id($obj->share_id());
        }
        if ($this->protection_id() === null and $obj->protection_id() != null) {
            $this->set_protection_id($obj->protection_id());
        }
        return $usr_msg;
    }


    /*
     *  check functions
     */

    /*
    // check if the owner is set for all records of a user sandbox object
    // e.g. if the owner of a new triple is set correctly at creation
    //      if not changes of another can overwrite the standard and by that influence the setup of the creator
    function chk_owner ($type, $correct) {
      zu_debug($this::class.'->chk_owner for '.$type);

      global $db_con;
      $msg = '';

      //$db_con = New mysql;
      $db_con->set_type($this::class);
      $db_con->set_usr($this->get_user()->id);

      if ($correct === True) {
        // set the default owner for all records with a missing owner
        $change_txt = $db_con->set_default_owner();
        if ($change_txt <> '') {
          $msg = 'Owner set for '.$change_txt.' '.$type.'s.';
        }
      } else {
        // get the list of records with a missing owner
        $id_lst = $db_con->missing_owner();
        $id_txt = implode(",",$id_lst);
        if ($id_txt <> '') {
          $msg = 'Owner not set for '.$type.' ID '.$id_txt.'.';
        }
      }

      return $id_lst;
    }
    */


    /*
     * load types
     */

    /**
     * @returns string the share type code id based on the database share type id
     */
    function share_type_code_id(): string
    {
        global $sys;
        return $sys->typ_lst->shr_typ->code_id($this->share_id);
    }

    /**
     * @returns string the share type name based on the database share type id
     */
    function share_type_name(): string
    {
        global $sys;

        // use the default share type if not set
        if ($this->share_id <= 0) {
            $this->share_id = $sys->typ_lst->shr_typ->id(share_type_shared::PUBLIC);
        }

        return $sys->typ_lst->shr_typ->name($this->share_id);
    }

    /**
     * @return string the protection type code id based on the database id
     */
    function protection_type_code_id(): string
    {
        global $sys;
        return $sys->typ_lst->ptc_typ->code_id($this->protection_id);
    }

    /**
     * @return string the protection type name based on the database id
     */
    function protection_type_name(): string
    {
        global $sys;

        // use the default share type if not set
        if ($this->protection_id <= 0) {
            $this->protection_id = $sys->typ_lst->ptc_typ->id(protect_type_shared::NO_PROTECT);
        }

        return $sys->typ_lst->ptc_typ->name($this->protection_id);
    }


    /*
     * info
     */

    /**
     * create human-readable messages of the differences between the sandbox objects
     * @param sandbox_multi|db_object_multi $obj which might be different to this sandbox object
     * @return user_message the human-readable messages of the differences between the sandbox objects
     */
    function diff_msg(sandbox_multi|db_object_multi $obj): user_message
    {
        $msg = parent::diff_msg($obj);
        $lib = new library();
        // TODO Prio 2 check owner is sometimes null on load?
        if ($this->owner_id() != $obj->owner_id()
            and $this->owner() != null
            and $obj->owner() != null) {
            $msg->add(msg_id::DIFF_OWNER, [
                msg_id::VAR_USER => $obj->owner()->dsp_id(),
                msg_id::VAR_USER_CHK => $this->owner()->dsp_id(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        if ($this->share_id() != $obj->share_id()) {
            $msg->add(msg_id::DIFF_SHARE, [
                msg_id::VAR_SHARE => $obj->share_type_name(),
                msg_id::VAR_SHARE_CHK => $this->share_type_name(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        if ($this->protection_id() != $obj->protection_id()) {
            $msg->add(msg_id::DIFF_PROTECTION, [
                msg_id::VAR_PROTECT => $obj->protection_type_name(),
                msg_id::VAR_PROTECT_CHK => $this->protection_type_name(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        if ($this->is_excluded() != $obj->is_excluded()) {
            $msg->add(msg_id::DIFF_EXCLUSION, [
                msg_id::VAR_EXCLUDE => $obj->is_excluded(),
                msg_id::VAR_EXCLUDE_CHK => $this->is_excluded(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        return $msg;
    }


    /*
     * im- and export
     */

    /**
     * function to import the core user sandbox object values from a json string
     * e.g. the share and protection settings
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
        global $sys;

        $msg = new user_message();

        $this->import_mapper($in_ex_json, $msg);


        // try to get the ownership if requested
        // TODO Prio 2 check where and in which cases this is needed probably if json_fields::PROTECTION != protect_type_shared::NO_PROTECT
        //if ($get_ownership) {
        //    $this->take_ownership();
        //}

        // TODO Prio 0 switch to a key_exists
        foreach ($in_ex_json as $key => $value) {
            if ($key == json_fields::SHARE) {
                $this->share_id = $sys->typ_lst->shr_typ->id($value);
                if ($this->share_id < 0) {
                    $lib = new library();
                    $msg->add(msg_id::SHARE_TYPE_NOT_EXPECTED, [
                        msg_id::VAR_NAME => $value,
                        msg_id::VAR_JSON_TEXT => $lib->dsp_array($in_ex_json)
                    ]);
                }
            }
            if ($key == json_fields::PROTECTION) {
                $this->protection_id = $sys->typ_lst->ptc_typ->id($value);
                if ($this->protection_id < 0) {
                    $lib = new library();
                    $msg->add(msg_id::PROTECTION_TYPE_NOT_EXPECTED, [
                        msg_id::VAR_NAME => $value,
                        msg_id::VAR_JSON_TEXT => $lib->dsp_array($in_ex_json)
                    ]);
                }
            }
        }
        return $msg->is_ok();
    }

    /**
     * create an array with the export json fields
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the export json
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        global $sys;

        $vars = [];

        // add the share type
        if ($this->share_id != null
            and $this->share_id > 0
            and $this->share_id <> $sys->typ_lst->shr_typ->id(share_type_shared::PUBLIC)) {
            $vars[json_fields::SHARE] = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id != null
            and $this->protection_id > 0
            and $this->protection_id <> $sys->typ_lst->ptc_typ->id(protect_type_shared::NO_PROTECT)) {
            $vars[json_fields::PROTECTION] = $this->protection_type_code_id();
        }

        return $vars;
    }

    private function common_json(): array
    {
        global $sys;

        $vars = [];

        // add the share type
        if ($this->share_id != null
            and $this->share_id > 0
            and $this->share_id <> $sys->typ_lst->shr_typ->id(share_type_shared::PUBLIC)) {
            $vars[json_fields::SHARE] = $this->share_type_code_id();
        }

        // add the protection type
        if ($this->protection_id != null
            and $this->protection_id > 0
            and $this->protection_id <> $sys->typ_lst->ptc_typ->id(protect_type_shared::NO_PROTECT)) {
            $vars[json_fields::PROTECTION] = $this->protection_type_code_id();
        }

        return $vars;
    }


    /*
     * info
     */

    /**
     * @param sql_creator $sc
     * @return sql_par sql parameter to get the user id of the most often used link (position) beside the standard (position)
     */
    function load_sql_median_user(sql_creator $sc): sql_par
    {
        $qp = new sql_par($this::class);
        $qp->name .= sql::NAME_EXT_MEDIAN_USER;
        if ($this->owner_id() > 0) {
            $qp->name .= sql::NAME_SEP . sql::NAME_EXT_EX_OWNER;
        }
        $sc->set_class($this::class);
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(array(user_db::FLD_ID));
        $qp->sql = $sc->select_by_id_not_owner($this->id());

        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * @returns int the user id of the most often used link (position) beside the standard (position)
     * TODO review, because the median is not taking into account the number of standard used values
     */
    function median_user(): int
    {
        log_debug($this->dsp_id() . ' beside the owner (' . $this->owner_id() . ')');

        global $db_con;
        $result = 0;

        $qp = $this->load_sql_median_user($db_con->sql_creator());
        $db_row = $db_con->get1($qp);
        if ($db_row[user_db::FLD_ID] > 0) {
            $result = $db_row[user_db::FLD_ID];
        } else {
            if ($this->owner_id() > 0) {
                $result = $this->owner_id();
            } else {
                if ($this->get_user()->id > 0) {
                    $result = $this->get_user()->id;
                }
            }
        }
        log_debug('for ' . $this->dsp_id() . ': ' . $result);
        return $result;
    }

    /**
     * TODO review (add ...)
     * @return bool true if no user has changed the value and no parameter beside the value is set
     */
    function is_standard(): bool
    {
        if ($this->usr_cfg_id == null
            and $this->owner_id() == null
            and !$this->excluded) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * save helper - ownership and access
     */

    /**
     * to be overwritten by the child object
     * @return bool true if the value has been at least once saved to the database
     */
    function is_saved(): bool
    {
        return true;
    }

    /**
     * if the user is an admin the user can force to be the owner of this object
     * TODO review
     */
    function take_ownership(user_message $usr_msg): bool
    {
        $result = false;
        log_debug($this->dsp_id());

        if ($this->get_user()->is_admin()) {
            // TODO Prio 3 activate $result .= $this->usr_cfg_create_all();
            $result = $this->set_owner($this->get_user()->id, $usr_msg); // TODO remove double getting of the user object
            // TODO Prio 3 activate $result .= $this->usr_cfg_cleanup();
        }

        log_debug($this->dsp_id() . ' done');
        return $result;
    }

    /**
     * change the owner of the object
     * any calling function should make sure that taking setting the owner is allowed
     * and that all user values
     * TODO review sql and object field compare of user and standard
     * @param bool $must_exist if false no error message is created if the value or result does not exist
     * @return bool true if the owner is succesful set
     */
    function set_owner(int $new_owner_id, user_message $usr_msg, bool $must_exist = true): bool
    {
        log_debug($this->dsp_id() . ' to ' . $new_owner_id);

        if ($this->has_id() > 0 and $new_owner_id > 0) {
            // load the standard db row
            $std = $this->clone_reset();
            $get_msg = clone $usr_msg;
            $std->load_standard($this->id(), $get_msg);

            if ($get_msg->is_ok() or $must_exist) {

                // set the owner and save
                $std->owner_id = $new_owner_id;
                $std->save($usr_msg);

                // update the current object
                if ($usr_msg->is_ok()) {
                    $this->owner_id = $new_owner_id;
                }
            }

        }
        return $usr_msg->is_ok();
    }

    // true if no other user has modified the object
    // assuming that in this case no confirmation from the other users for an object change is needed
    function not_changed(): bool
    {
        $result = true;
        log_debug($this->id() . ' by someone else than the owner ' . $this->owner_id());

        $lib = new library();
        $other_usr_id = $this->changer();
        if ($other_usr_id > 0) {
            $result = false;
        }

        log_debug($this->id() . ' is ' . $lib->dsp_bool($result));
        return $result;
    }

    /**
     * true if no one has used the object
     * TODO if this has been used for calculation, this is also used
     */
    function not_used(): bool
    {
        $result = true;
        log_debug($this->id());

        $lib = new library();
        $using_usr_id = $this->median_user();
        if ($using_usr_id > 0) {
            $result = false;
        }

        log_debug($lib->dsp_bool($result));
        return $result;
    }

    /**
     * create the sql statement to get the users that has changed to sandbox object
     *
     * @param sql_db $db_con the database connection with the active database type
     * @return sql_par the sql statement and the parameters to get the users that have changed the sandbox object
     */
    function changer_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par($this::class);
        $qp->name .= 'changer';
        if ($this->owner_id() > 0) {
            $qp->name .= sql::NAME_SEP . sql::NAME_EXT_EX_OWNER;
        }
        $db_con->set_class($this::class, true);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->get_user()->id);
        $db_con->set_fields(array(user_db::FLD_ID));
        $qp->sql = $db_con->select_by_id_not_owner($this->id(), $this->owner_id());

        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * if the object has been changed by someone else than the owner the user id is returned
     * but only return the user id if the user has not also excluded it
     * @returns int the user id of someone who has changed the object, but is not owner
     */
    function changer(): int
    {
        log_debug($this->dsp_id());

        global $db_con;

        $user_id = 0;
        $db_con->set_class($this::class);
        $db_con->set_usr($this->get_user()->id);
        $qp = $this->changer_sql($db_con);
        $db_row = $db_con->get1($qp);
        if ($db_row) {
            $user_id = $db_row[user_db::FLD_ID];
        }

        log_debug('is ' . $user_id);
        return $user_id;
    }

    /**
     * create an SQL statement to get a list of all user that have ever changed the object
     * @param sql_creator $sc with the target db_type set
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_of_users_that_changed(sql_creator $sc): sql_par
    {
        $lib = new library();

        $qp = new sql_par($this::class);
        $qp->name .= 'user_list';

        $class = $lib->class_to_name($this::class);
        $sc->set_class($class, new sql_type_list([sql_type::USER]));
        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_join_fields(
            array_merge(array(user_db::FLD_ID, user_db::FLD_NAME), user_db::FLD_NAMES_LIST),
            user::class,
            user_db::FLD_ID,
            user_db::FLD_ID);
        $sc->add_where($this->id_field(), $this->id());
        $sc->add_where(sandbox_multi::FLD_EXCLUDED, 1, sql_par_type::INT_NOT_OR_NULL);

        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * @return user_list a list of all user that have ever changed the object
     */
    function changed_by(): user_list
    {
        log_debug($this->dsp_id());

        global $db_con;

        $usr_id_lst = array();
        $result = new user_list($this->get_user());

        // add object owner
        $usr_id_lst[] = $this->owner_id();
        $qp = $this->load_sql_of_users_that_changed($db_con->sql_creator());
        $db_usr_lst = $db_con->get($qp);
        foreach ($db_usr_lst as $db_usr) {
            if ($db_usr[user_db::FLD_ID] > 0) {
                $usr_id_lst[] = $db_usr[user_db::FLD_ID];
            }
        }
        $result->load_by_ids($db_con, $usr_id_lst);

        return $result;
    }

    /**
     * true if no else one has used the object
     * TODO if this should be true if no one else has been used this object e.g. for calculation
     */
    function used_by_someone_else(): bool
    {
        $result = true;
        log_debug($this->id());

        $lib = new library();
        log_debug('owner is ' . $this->owner_id() . ' and the change is requested by ' . $this->get_user()->id);
        if ($this->owner_id() == $this->get_user()->id or $this->owner_id() <= 0) {
            $changer_id = $this->changer();
            // removed "OR $changer_id <= 0" because if no one has changed the object jet does not mean that it can be changed
            log_debug('changer is ' . $changer_id . ' and the change is requested by ' . $this->get_user()->id);
            if ($changer_id == $this->get_user()->id or $changer_id <= 0) {
                $result = false;
            }
        }

        log_debug(': ' . $lib->dsp_bool($result));
        return $result;
    }

    /**
     * @return bool true if the user is the owner and no one else has changed the object
     *              because if another user has changed the object and the original value is changed,
     *              maybe the user object also needs to be updated
     */
    function can_change(): bool
    {
        $result = false;

        $lib = new library();
        // if the user who wants to change it, is the owner, he can do it
        // or if the owner is not set, he can do it (and the owner should be set, because every object should have an owner)
        log_debug('owner is ' . $this->owner_id() . ' and the change is requested by ' . $this->get_user()->id);
        if ($this->owner_id() == $this->get_user()->id or $this->owner_id() <= 0) {
            $result = true;
        }

        log_debug($this::class . $lib->dsp_bool($result));
        return $result;
    }


    /*
     * save helper - user sandbox
     */

    function is_numeric(): bool
    {
        return false;
    }

    function is_time_value(): bool
    {
        return false;
    }

    function is_text_value(): bool
    {
        return false;
    }

    function is_geo_value(): bool
    {
        return false;
    }

    /**
     * @return bool true if a record for a user-specific configuration already exists in the database
     */
    function has_usr_cfg(): bool
    {
        $result = false;
        $lib = new library();
        if ($this->usr_cfg_id > 0) {
            $result = true;
        }

        log_debug($lib->dsp_bool($result));
        return $result;
    }

    /**
     * simply remove a user adjustment without check
     * log a system error if a technical error has occurred
     *
     * @return bool true if user sandbox row has successfully been deleted
     */
    function del_usr_cfg_exe($db_con): bool
    {
        log_debug($this->dsp_id() . ' und user ' . $this->get_user()->name);
        $lib = new library();
        $usr_msg = new user_message();
        $class_name = $lib->class_to_name($this::class);

        $result = false;
        $action = 'Deletion of user ' . $class_name . ' ';
        $msg_failed = $this->id() . ' failed for ' . $this->get_user()->name;

        $db_con->set_class($this::class, true);
        try {
            $qp = $this->sql_delete($db_con->sql_creator(), $usr_msg, new sql_type_list([sql_type::USER]));
            $db_con->delete($qp, $this::class . ' user exclusions', $usr_msg);
            $msg = $usr_msg->get_message();
            if ($msg == '') {
                $this->usr_cfg_id = null;
                $result = true;
            } else {
                log_err($action . $msg_failed . ' because ' . $msg);
            }
        } catch (Exception $e) {
            log_err($action . $msg_failed . ' because ' . $e);
        }
        return $result;
    }

    /**
     * remove user adjustment and log it (used by user.php to undo the user changes)
     */
    function del_usr_cfg(): bool
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        global $db_con;
        $result = true;

        if ($this->id() > 0 and $this->get_user()->id() > 0) {
            $log = $this->log_del();
            if ($log->id() > 0) {
                $db_con->usr_id = $this->get_user()->id();
                $result = $this->del_usr_cfg_exe($db_con);
            }

        } else {
            log_err('The database ID and the user must be set to remove a user-specific modification of ' . $class_name . '.', $this::class . '->del_usr_cfg');
        }

        return $result;
    }

    /**
     * create a database record to save user-specific settings for a user sandbox object
     * TODO combine the reread and the adding in a commit transaction; same for all db change transactions
     * @return bool false if the creation has failed and true if it was successful or not needed
     */
    protected function add_usr_cfg(user_message $usr_msg, string $class = self::class): bool
    {
        global $db_con;
        $result = true;
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);


        if (!$this->has_usr_cfg()) {
            $lib = new library();
            $class = $lib->class_to_name($class);

            if ($this->is_named_obj()) {
                log_debug('for "' . $this->dsp_id() . ' und user ' . $this->get_user()->name);
            } elseif ($this->is_link_obj()) {
                if (isset($this->fob) and isset($this->tob)) {
                    log_debug('for "' . $this->fob->name . '"/"' . $this->tob->name . '" by user "' . $this->get_user()->name . '"');
                } else {
                    log_debug('for "' . $this->id() . '" and user "' . $this->get_user()->name . '"');
                }
            } else {
                log_err('Unknown user sandbox type ' . $class_name . ' in ' . $class, $class . '->log_add');
            }

            // check again if there ist not yet a record
            $db_con->set_class($class, true);
            $qp = new sql_par($class);
            $qp->name = $class . '_add_usr_cfg';
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->get_user()->id());
            $db_con->set_where_std($this->id());
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();
            $db_row = $db_con->get1($qp);
            if ($db_row != null) {
                $this->usr_cfg_id = $db_row[$this->id_field()];
            }
            if (!$this->has_usr_cfg()) {
                // create an entry in the user sandbox
                $db_con->set_class(sql_db::TBL_USER_PREFIX . $class);
                $db_con->set_usr($this->get_user()->id);
                $qp = $this->sql_insert($db_con->sql_creator(), $usr_msg, new sql_type_list([sql_type::USER]));
                $db_con->insert($qp, 'add user-specific value', $usr_msg);
                if (!$usr_msg->is_ok()) {
                    log_err('Insert of ' . sql_db::USER_PREFIX . $class . ' failed.');
                    $result = false;
                } else {
                    $this->usr_cfg_id = $usr_msg->get_row_id();
                }
            }
        }
        return $result;
    }

    /**
     * check again if there is not yet a record
     * @return bool true if the user has done some personal changes on this object
     */
    protected function check_usr_cfg(): bool
    {
        global $db_con;
        $result = false;

        log_debug('for "' . $this->dsp_id() . ' und user ' . $this->get_user()->dsp_id());

        // check again if there ist not yet a record
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_user_changes($sc);
        $db_con->usr_id = $this->get_user()->id;
        $db_row = $db_con->get1($qp);
        if ($db_row != null) {
            $this->usr_cfg_id = $db_row[$this->id_field()];
            if ($this->has_usr_cfg()) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * check if the database record for the user-specific settings can be removed
     * TODO separate the query parameter creation and add a unit test
     * @return bool false if the deletion has failed and true if it was successful or not needed
     */
    protected function del_usr_cfg_if_not_needed(): bool
    {

        global $db_con;
        $result = true;

        // TODO check if next line is working
        //if ($this->has_usr_cfg) {

        // check again if there ist not yet a record
        $qp = $this->load_sql_user_changes($db_con->sql_creator());
        $db_con->usr_id = $this->get_user()->id;
        $usr_cfg_row = $db_con->get1($qp);
        if ($usr_cfg_row) {
            log_debug('check for "' . $this->dsp_id() . ' und user ' . $this->get_user()->name . ' with (' . $qp->sql . ')');
            $id = $this->id_field();
            if (is_array($id)) {
                $id_used = $id[0];
            } else {
                $id_used = $id;
            }
            if ($usr_cfg_row[$id_used] > 0) {
                if ($this->no_usr_fld_used($this->all_sandbox_fields(), $usr_cfg_row)) {
                    $result = $this->del_usr_cfg_exe($db_con);
                }
            }
        }
        //}

        // don't throw an error message if another account has removed the user sandbox row in the meantime
        if (!$this->has_usr_cfg()) {
            $result = true;
        }

        return $result;
    }

    /**
     * check if the database row with the user-specific data is still needed
     *
     * @param array $fld_lst all potential user-specific fields of the object
     * @param array $db_row the database record of the user table
     * @return bool true if no field contain any user overwrite
     */
    protected function no_usr_fld_used(array $fld_lst, array $db_row): bool
    {
        $result = true;
        foreach ($fld_lst as $field_name) {
            if ($db_row[$field_name] != '') {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * remove all user setting that are not needed any more based on the new standard object
     * TODO review
     */
    function usr_cfg_cleanup(sandbox_multi $std): string
    {
        $result = '';
        log_debug($this->dsp_id());

        // get a list of users that have a user cfg of this object
        $usr_lst = $this->changed_by();
        foreach ($usr_lst as $usr) {
            // remove the usr cfg if not needed any more
            $this->del_usr_cfg_if_not_needed($this->id_field(), $this->all_sandbox_fields());
        }

        log_debug('for ' . $this->dsp_id() . ': ' . $result);
        return $result;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new value or result to the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par|null the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par|null
    {
        // clone the parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        // create an empty sandbox object but of the same type and with the same user to detect the fields that should be written
        $sbx_empty = $this->cloned(null);
        // TODO Prio 1 activate next line for a new sandbox object the owner should be set, so remove the user id to force writing the user
        $sbx_empty->set_user($this->get_user()->clone_reset());
        // get a list of all fields that could potentially be updated
        $all_fields = $this->db_fields_all();
        return $this->sql_write($sc, $sbx_empty, $all_fields, $usr_msg, $sc_par_lst_used);
    }


    /*
     * log
     */

    /**
     * create and fill a change log entry for adding a multi table object
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     *
     * @return change|change_value|changes_norm|changes_big the change log object with the basic parameters set
     */
    function log_add(): change|change_value|changes_norm|changes_big
    {
        log_debug($this->dsp_id());
        $log = $this->log_object();
        return $this->log_add_common($log);
    }

    /**
     * set the common parameters to log an insert of a value, result or group object and execute it
     * @param change|change_value $log with the target table set
     * @return change|change_value with the log id set
     */
    protected function log_add_common(change|change_value $log): change|change_value
    {
        $lib = new library();
        $usr_msg = new user_message();
        $log->set_action(change_actions::ADD);
        // a value, result or group is always identified by the group name
        $log->set_field($lib->class_to_name(group::class) . '_name');
        $log->old_value = null;
        $log->new_value = $this->name();
        $log->row_id = 0;
        $log->add($usr_msg);
        return $log;
    }

    /**
     * set the log entry parameter for a new link object
     */
    function log_link_add(): change_link
    {
        log_err('The dummy parent method log_link_add has been called for ' . $this::class . ', which should never happen');
        return new change_link($this->get_user());
    }

    /**
     * create a log object for an update of an object field
     */
    function log_upd_field(): change
    {
        log_debug($this->dsp_id());
        $log = new change($this->get_user());
        return $this->log_upd_common($log);
    }

    /**
     * create a log object for an update of an object field
     * e.g. that the user can see >value name change from "inhabitants, Switzerland" to "Swiss inhabitants"<
     * @return change|change_value|changes_norm|changes_big with the settings to log the changes of this object
     */
    function log_upd(): change|change_value|changes_norm|changes_big
    {
        log_debug($this->dsp_id());
        $log = $this->log_object();
        return $this->log_upd_common($log);
    }

    function log_object(): change|change_value|changes_norm|changes_big
    {
        if ($this->is_prime()) {
            $log = $this->log_prime();
        } elseif ($this->is_big()) {
            $log = $this->log_big();
        } else {
            $log = $this->log_norm();
        }
        return $log;
    }

    function log_named_id_object(): change|change_value|changes_norm|changes_big
    {
        if ($this->is_prime()) {
            $log = new change($this->get_user());
        } elseif ($this->is_big()) {
            $log = new changes_big($this->get_user());
        } else {
            $log = new changes_norm($this->get_user());
        }
        return $log;
    }

    /**
     * set the log entry parameter for a group object with a bigint key
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     *
     * @return change|change_value the change log object with the basic parameters set
     */
    private function log_prime(): change|change_value
    {
        if ($this::class == group::class) {
            $log = new change($this->get_user());
        } else {
            if ($this->is_numeric()) {
                $log = new change_values_prime($this->get_user());
            } elseif ($this->is_time_value()) {
                $log = new change_values_time_prime($this->get_user());
            } elseif ($this->is_text_value()) {
                $log = new change_values_text_prime($this->get_user());
            } elseif ($this->is_geo_value()) {
                $log = new change_values_geo_prime($this->get_user());
            } else {
                $log = new change_values_prime($this->get_user());
            }
        }
        $class = (new library())->class_to_name($this::class);
        $log->set_table($class . sql_db::TABLE_EXTENSION);
        return $log;
    }

    /**
     * similar to log_prime but ...
     * ... set the log entry parameter for a group object with a 512bit key
     *
     * @return change|change_value the change log object with the basic parameters set
     */
    private function log_norm(): change|change_value
    {
        log_debug($this->dsp_id());

        if ($this::class == group::class) {
            $log = new changes_norm($this->get_user());
        } else {
            if ($this->is_numeric()) {
                $log = new change_values_norm($this->get_user());
            } elseif ($this->is_time_value()) {
                $log = new change_values_time_norm($this->get_user());
            } elseif ($this->is_text_value()) {
                $log = new change_values_text_norm($this->get_user());
            } elseif ($this->is_geo_value()) {
                $log = new change_values_geo_norm($this->get_user());
            } else {
                $log = new change_values_norm($this->get_user());
            }
        }
        $class = (new library())->class_to_name($this::class);
        $log->set_table($class . sql_db::TABLE_EXTENSION . sql_type::NORM->extension());
        return $log;
    }

    /**
     * similar to log_prime but ...
     * * ... set the log entry parameter for a group object with a text key
     *
     * @return changes_big|change_values_big|change_values_time_big|change_values_text_big|change_values_geo_big the change log object with the basic parameters set
     */
    private function log_big(): changes_big|change_values_big|change_values_time_big|change_values_text_big|change_values_geo_big
    {
        log_debug($this->dsp_id());

        if ($this::class == group::class) {
            $log = new changes_big($this->get_user());
        } else {
            if ($this->is_numeric()) {
                $log = new change_values_big($this->get_user());
            } elseif ($this->is_time_value()) {
                $log = new change_values_time_big($this->get_user());
            } elseif ($this->is_text_value()) {
                $log = new change_values_text_big($this->get_user());
            } elseif ($this->is_geo_value()) {
                $log = new change_values_geo_big($this->get_user());
            } else {
                $log = new change_values_big($this->get_user());
            }
        }
        $class = (new library())->class_to_name($this::class);
        $log->set_table($class . sql_db::TABLE_EXTENSION . sql_type::BIG->extension());
        return $log;
    }

    /**
     * set the main log entry parameters for updating one field
     */
    private function log_upd_common($log)
    {
        $lib = new library();
        $class = $lib->class_to_name($this::class);
        log_debug($this->dsp_id());
        $log->set_user($this->get_user());
        $log->set_action(change_actions::UPDATE);
        if ($this->can_change()) {
            // TODO add the table exceptions from sql_db
            $log->set_table($class . sql_db::TABLE_EXTENSION);
        } else {
            $log->set_table(sql_db::TBL_USER_PREFIX . $class . sql_db::TABLE_EXTENSION);
        }

        return $log;
    }

    /**
     * dummy function definition that will be overwritten by the child object
     * @return change_link
     */
    function log_del_link(): change_link
    {
        $usr_msg = new user_message();
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'log_del_link',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return new change_link($this->get_user());
    }

    /**
     * set the log entry parameter for a group object with a bigint key
     * for all not named objects like links, this function is overwritten
     * e.g. that the user can see "added formula 'scale millions' to word 'mio'"
     *
     * @return change the change log object with the basic parameters set
     */
    function log_del_prime(): change
    {
        log_debug($this->dsp_id());

        $log = new change($this->get_user());
        $class = (new library())->class_to_name($this::class);
        $log->set_table($class . sql_db::TABLE_EXTENSION);
        return $this->log_del_common($log);
    }

    /**
     * similar to log_del_prime but ...
     * ... set the log entry parameter for a group object with a 512bit key
     *
     * @return changes_norm the change log object with the basic parameters set
     */
    function log_del(): change
    {
        $log = new changes_norm($this->get_user());
        $class = (new library())->class_to_name($this::class);
        $log->set_table($class . sql_db::TABLE_EXTENSION . sql_type::NORM->extension());
        return $this->log_del_common($log);
    }

    /**
     * similar to log_del_prime but ...
     * * ... set the log entry parameter for a group object with a text key
     *
     * @return changes_big the change log object with the basic parameters set
     */
    function log_del_big(): changes_big
    {
        log_debug($this->dsp_id());

        $log = new changes_big($this->get_user());
        $class = (new library())->class_to_name($this::class);
        $log->set_table($class . sql_db::TABLE_EXTENSION . sql_type::BIG->extension());
        return $this->log_del_common($log);
    }

    /**
     * set the common parameters to log the delete or exclude of a value, result or group object and execute it
     * @param change|changes_norm|changes_big $log with the target table set
     * @return change|changes_norm|changes_big with the log id set
     */
    private function log_del_common(change|changes_norm|changes_big $log): change|changes_norm|changes_big
    {
        $lib = new library();
        $usr_msg = new user_message();
        // a value, result or group is always identified by the group name
        $log->set_field($lib->class_to_name(group::class) . '_name');
        $log->old_value = $this->name();
        $log->new_value = null;
        $log->row_id = 0;
        $log->set_action(change_actions::DELETE);
        $log->add($usr_msg);
        return $log;
    }

    /**
     * dummy function definition that will be overwritten by the child object
     * check if the user requested a preserved name and if yes return a message to the user
     * @param user_message $msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @return bool true if everything has been fine
     */
    protected function check_preserved(user_message $msg): bool
    {
        $msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'check_preserved',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $msg->is_ok();
    }

    /**
     * @return change_log the object that is used to log the user changes
     */
    function log_value_object(): change_log
    {
        if ($this->is_prime()) {
            return new change_values_prime($this->get_user());
        } elseif ($this->is_big()) {
            return new change_values_big($this->get_user());
        } else {
            return new change_values_norm($this->get_user());
        }
    }


    /*
     * save helper - save fields
     */

    /**
     * check if the sandbox can be added to the database
     * @param user_message|Message $msg including suggested solutions if something is missing e.g. the user
     * @return  bool true if the value or result can be added to the database
     */
    function db_ready(user_message|Message $msg): bool
    {
        if ($this->id == null) {
            $msg->add(msg_id::VALUE_ID_MISSING,
                [msg_id::VAR_VALUE => $this->dsp_id()]);
        }
        if ($this->get_user() == null) {
            $msg->add(msg_id::USER_MISSING,
                [msg_id::VAR_NAME => $this->dsp_id()]);
        }
        return $msg->is_ok();
    }

    /**
     * dummy function to save all updated word fields, which is always overwritten by the child class
     */
    function save_fields(sql_db $db_con, sandbox_multi $db_rec, sandbox_multi $std_rec): string
    {
        $usr_msg = new user_message();
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'save_fields',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->get_last_message();
    }

    /**
     * create the sql statement to update a value in the database
     * to be overwritten by a child object
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $fld_val_typ_lst list of field names, values and sql types additional to the standard id and name fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_update_multi(
        sql_creator   $sc,
        array         $fld_val_typ_lst = [],
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        $sc->set_class($this::class, $sc_par_lst);
        $qp = new sql_par($this::class, $sc_par_lst);
        $qp->name .= sql::NAME_SEP . sql_creator::FILE_UPDATE;
        $sc->set_name($qp->name);
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->set($fld_val_typ_lst);
        $qp->sql = $sc->create_sql_update($this->id_field(), $this->id(), $fvt_lst);
        $values = $sc->get_values($fld_val_typ_lst);
        $values[] = $this->id();
        $par_values = [];
        foreach (array_keys($values) as $i) {
            if ($values[$i] != sql::NOW) {
                $par_values[$i] = $values[$i];
            }
        }

        $qp->par = $par_values;
        return $qp;
    }

    /**
     * create the sql statement to delete a value in the database
     * similar to sandbox/sql_delete, but additional for prime or big tables
     * TODO check if user-specific overwrites can be deleted
     * TODO check if can be moved to sandbox_value object
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_delete(
        sql_creator   $sc,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // clone the parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::DELETE);
        // set the query name
        $qp = $this->sql_common($sc, $sc_par_lst_used);
        // delete the user overwrite
        // but if the excluded user overwrites should be deleted the overwrites for all users should be deleted
        if ($sc_par_lst_used->incl_log()) {
            // log functions must always use named parameters
            $sc_par_lst_used->add(sql_type::NAMED_PAR);
            $qp = $this->sql_delete_and_log($sc, $qp, $sc_par_lst_used);
        } else {
            $id_lst = $this->id_or_lst();
            if ($sc_par_lst_used->is_usr_tbl() and !$sc_par_lst_used->exclude_sql()) {
                if (is_array($id_lst)) {
                    $id_lst[] = $this->get_user_id();
                } else {
                    $id_lst = [$id_lst, $this->get_user_id()];
                }
                $qp->sql = $sc->create_sql_delete(
                    [$this->id_field(), user_db::FLD_ID], $id_lst, $sc_par_lst_used);
            } else {
                $qp->sql = $sc->create_sql_delete($this->id_field(), $id_lst, $sc_par_lst_used);
            }
            if (is_array($id_lst)) {
                $qp->par = $id_lst;
            } else {
                $qp->par = [$id_lst];
            }
        }
        return $qp;
    }

    /**
     * @param sql_creator $sc the sql creator object with the db type set
     * @param sql_par $qp the query parameter with the name already set
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @return sql_par
     */
    private function sql_delete_and_log(
        sql_creator   $sc,
        sql_par       $qp,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        global $sys;
        $table_id = $sc->table_id($this::class);

        // set some var names to shorten the code lines
        $ext = sql::NAME_SEP . sql_creator::FILE_DELETE;
        $id_fld = $sc->id_field_name();
        $id_val = '_' . $id_fld;
        $name_fld = $this->name_field();

        // list of parameters actually used in order of the function usage
        $fvt_lst_out = new sql_par_field_list();

        // init the function body
        $sql = $sc->sql_func_start('', $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = clone $sc_par_lst;
        $sc_par_lst_sub->add(sql_type::LIST);
        $sc_par_lst_sub->add(sql_type::NAMED_PAR);
        $sc_par_lst_sub->add(sql_type::DELETE_PART);
        $sc_par_lst_log = $sc_par_lst_sub->remove(sql_type::LOG);
        $sc_par_lst_log->add(sql_type::SELECT_FOR_INSERT);

        // use for prime the standard log table and for up to 16 phrases the norm table
        if ($sc_par_lst_log->is_prime()) {
            $sc_par_lst_log = $sc_par_lst_log->remove(sql_type::PRIME);
        } elseif (!$sc_par_lst_log->is_big()) {
            $sc_par_lst_log->add(sql_type::NORM_EXT);
        }

        // create the queries for the log entries
        $func_body_change = '';

        // create the insert log statement for the field of the loop
        $log = new change($this->get_user());
        $log->set_class($this::class);
        if ($this->is_named_obj()) {
            $log->set_field($name_fld);
            $log->old_value = $this->name();
            $log->new_value = null;
        }

        $sc_log = clone $sc;
        // TODO replace dummy value table with an enum value
        if ($this->is_named_obj()) {
            $qp_log = $log->sql_insert_log(
                $sc_log, $sc_par_lst_log, $ext . '_' . $name_fld, '', $name_fld, $id_val);
        } else {
            $qp_log = $log->sql_insert_log(
                $sc_log, $sc_par_lst_log, $ext, '', '', $id_val);
        }

        // TODO get the fields used in the change log sql from the sql
        $func_body_change .= ' ' . $qp_log->sql . ';';

        // add the user_id if needed
        $fvt_lst_out->add_field(
            user_db::FLD_ID,
            $this->get_user_id(),
            sql_par_type::INT);

        // add the change_action_id if needed
        $fvt_lst_out->add_field(
            change_action::FLD_ID,
            $sys->typ_lst->cng_act->id(change_actions::DELETE),
            sql_par_type::INT_SMALL);

        if ($this->is_named_obj()) {
            // add the field_id of the field actually changed if needed
            $fvt_lst_out->add_field(
                sql::FLD_LOG_FIELD_PREFIX . $name_fld,
                $sys->typ_lst->cng_fld->id($table_id . $name_fld),
                sql_par_type::INT_SMALL);

            // add the db field value of the field actually changed if needed
            $fvt_lst_out->add_field(
                $name_fld,
                $this->name(),
                sql_par_type::TEXT);
        }

        // add the row id of the standard table for user overwrites
        if ($sc_par_lst->is_big()) {
            $id_typ = sql_par_type::TEXT;
        } elseif ($sc_par_lst->is_prime()) {
            $id_typ = sql_par_type::INT;
        } else {
            $id_typ = sql_par_type::TEXT;
        }
        $fvt_lst_out->add_field(
            $this->id_field(),
            $this->id(),
            $id_typ);

        $sql .= ' ' . $func_body_change;

        // create the actual delete or exclude statement
        $sc_delete = clone $sc;
        $sc_par_lst_del = clone $sc_par_lst;
        $sc_par_lst_del->add(sql_type::DELETE);
        $sc_par_lst_del->add(sql_type::NAMED_PAR);
        $qp_delete = $this->sql_common($sc_delete, $sc_par_lst_sub);;
        $qp_delete->sql = $sc_delete->create_sql_delete(
            $id_fld, $id_val, $sc_par_lst_sub);
        // add the insert row to the function body
        $sql .= ' ' . $qp_delete->sql . ' ';

        $sql .= $sc->sql_func_end();

        // create the query parameters for the call
        $qp_func = clone $qp;
        $sc_par_lst_func = clone $sc_par_lst;
        $sc_par_lst_func->add(sql_type::FUNCTION);
        $sc_par_lst_func->add(sql_type::DELETE);
        $sc_par_lst_func->add(sql_type::NO_ID_RETURN);
        if ($sc_par_lst->exclude_sql()) {
            $sc_par_lst_func->add(sql_type::EXCLUDE);
        }
        $qp_func = $this->sql_common($sc_delete, $sc_par_lst_func);
        $qp_func->sql = $sc->create_sql_delete(
            $id_fld, $id_val, $sc_par_lst_func, $fvt_lst_out);
        $qp_func->par = $fvt_lst_out->values();

        // merge all together and create the function
        $qp->sql = $qp_func->sql . ' ' . $sql . ';';

        // create the function call
        $qp->call_sql = ' ' . sql::SELECT . ' ' . $qp_func->name . ' (';

        $call_val_str = $fvt_lst_out->par_sql($sc);

        $qp->call_sql .= $call_val_str . ');';

        return $qp;
    }

    /**
     * the common part of the sql_insert, sql_update and sql_delete functions
     * in most cases overwritten by the child object
     * TODO include the sql statements to log the changes
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par prepared sql parameter object with the name set
     */
    protected function sql_common(sql_creator $sc, sql_type_list $sc_par_lst): sql_par
    {
        $qp = new sql_par($this::class, $sc_par_lst);

        // update the sql creator settings
        $sc->set_class($this::class, $sc_par_lst);
        $sc->set_name($qp->name);

        return $qp;
    }

    /*
     * dummy sql related function that are overwritten by the child objects
     */

    /**
     * dummy function that should always be overwritten by the child object
     * @return string
     */
    function name_field(): string
    {
        $usr_msg = new user_message();
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'name_field',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->get_last_message();
    }

    /**
     * @param sandbox_multi $db_rec the object as saved in the database before the change
     * @return change_log the log object predefined for excluding
     */
    function save_field_excluded_log(sandbox_multi $db_rec): change_log
    {
        $log = new change_log($this->get_user());
        if ($db_rec->is_excluded() <> $this->is_excluded()) {
            if ($this->is_excluded()) {
                if ($this->is_link_obj()) {
                    $log = $this->log_del_link();
                } else {
                    $log = $this->log_del();
                }
            } else {
                if ($this->is_link_obj()) {
                    $log = $this->log_link_add();
                } else {
                    $log = $this->log_add();
                }
            }
        }
        $log->set_field(sql_db::FLD_EXCLUDED);
        return $log;
    }

    /**
     * set to row id for the log
     * @param change_value|change_log $log
     * @return void
     */
    function save_set_log_id(change_value|change_log $log): void
    {
        $id = $this->id();
        if (is_string($id)) {
            $log->group_id = $id;
        } else {
            $log->row_id = $id;
        }
    }


    /*
     * save helper - check id
     */

    /**
     * dummy function definition that will be overwritten by the child objects
     * check if the id parameters are supposed to be changed
     * @param sandbox_multi $db_rec the object data as it is now in the database
     * @return bool true if one of the object id fields have been changed
     */
    function is_id_updated(sandbox_multi $db_rec): bool
    {
        $usr_msg = new user_message();
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'is_id_updated',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return false;
    }

    /**
     * check if the target key value already exists
     * overwritten in the word class for formula link words
     *
     * @return sandbox_multi object with id zero if no object with the same id is found
     */
    function get_obj_with_same_id_fields(): sandbox_multi
    {
        log_debug('check if target already exists ' . $this->dsp_id());
        $db_chk = clone $this;
        $db_chk->load_standard($this->id(), new user_message()); // TODO should not ADDITIONAL the user-specific load be called
        return $db_chk;
    }

    /**
     * @return string text that requests the user to use another name
     * overwritten in the word class for formula link words
     */
    function msg_id_already_used(): string
    {
        $msg = 'ERROR: msg_id_already_used not overwritten by ' . $this::class;
        log_err($msg);
        return $msg;
    }


    /*
     * save helper - check similar
     */

    /**
     * dummy function that is supposed to be overwritten by the child classes for e.g. named or link objects
     *
     * check if the unique key (not the db id) of two user sandbox object is the same if the object type is the same, so the simple case
     * @param object $obj_to_check the object used for the comparison
     * @return bool true if the objects represent the same
     */
    function is_same_std(object $obj_to_check): bool
    {
        $usr_msg = new user_message();
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'is_same_std',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return false;
    }

    /**
     * check that the given object is by the unique keys the same as the actual object
     * handles the specials case that for each formula a corresponding word is created (which needs to be checked if this is really needed)
     * so if a formula word "millions" is not the same as the standard word "millions" because the formula word "millions" is representing a formula which should not be combined
     * in short: if two objects are the same by this definition, they are supposed to be merged
     */
    function is_same($obj_to_check): bool
    {
        global $sys;

        $result = false;

        /*
        if ($this::class == word::class and $obj_to_check::class == formula::class) {
            // special case if word should be created representing the formula it is a kind of same at least the creation of the word should be allowed
            if ($this->name == $obj_to_check->name) {
                $result = true;
            }
        } elseif ($this::class == word::class and $obj_to_check::class == word::class) {

        */
        if ($this::class == word::class) {
            // special case a word should not be combined with a word that is representing a formulas
            if ($this->name() == $obj_to_check->name()) {
                if (isset($this->type_id) and isset($obj_to_check->type_id)) {
                    if ($this->type_id == $obj_to_check->type_id) {
                        $result = true;
                    } else {
                        if ($obj_to_check::class == formula::class
                            and $this->type_id == $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK)) {
                            // if one is a formula and the other is a formula link word, the two objects are representing the same formula object (but the calling function should use the formula to update)
                            $result = true;
                        } elseif ($this->type_id == $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK)
                            or $obj_to_check->type_id == $sys->typ_lst->phr_typ->id(phrase_type_shared::FORMULA_LINK)) {
                            // if one of the two words is a formula link and not both, the user should ge no suggestion to combine them
                            $result = false;
                        } else {
                            // a measure word can be combined with a measure scale word
                            $result = true;
                        }
                    }
                } else {
                    log_debug('The type_id of the two objects to compare are not set');
                    $result = true;
                }
            }
        } elseif ($this::class == $obj_to_check::class) {
            $result = $this->is_same_std($obj_to_check);
        }
        return $result;
    }

    /**
     * just to double-check if the get similar function is working correctly
     * so if the formulas "millions" is compared with the word "millions" this function returns true
     * in short: if two objects are similar by this definition, they should not be both in the database
     * @param null|object $obj_to_check the object used for the comparison
     * @return bool true if the objects represent the same
     */
    function is_similar(?object $obj_to_check): bool
    {
        $result = false;
        if ($obj_to_check != null) {
            //
            if ($this::class == $obj_to_check::class) {
                $result = $this->is_same_std($obj_to_check);
            } else {
                // create a synthetic unique index over words, phrase, verbs and formulas
                if (in_array($this::class, def::TERM_CLASSES)) {
                    if ($this->name() == $obj_to_check->name()) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * dummy function that is supposed to be overwritten by the child classes for e.g. named or link objects
     *
     * check if an object with the unique key already exists
     * returns null if no similar object is found
     * or returns the object with the same unique key that is not the actual object;
     * any warning or error message needs to be created in the calling function
     * e.g. if the user tries to create a formula named "millions"
     *      but a word with the same name already exists, a term with the word "millions" is returned
     *      in this case the calling function should suggest the user to name the formula "scale millions"
     *      to prevent confusion when writing a formula where all words, phrases, verbs and formulas should be unique
     * @param user_message $msg the user who has requested the update and the object to collect the potential reject messages
     * @returns sandbox_multi|null a filled object that has the same name or links the same objects
     *                  or null if nothing similar has been found
     */
    function get_similar(user_message $msg): sandbox_multi|null
    {
        $msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'get_similar',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return new sandbox_multi($this->get_user());
    }


    /*
     * add
     */

    /**
     * dummy function that is supposed to be overwritten by the child classes for e.g. named or link objects
     *
     * @param user_message $usr_msg with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     * @return bool true if everything has been fine
     */
    function add(user_message $usr_msg): bool
    {
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'add',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->is_ok();
    }

    /*
     * save
     * TODO review and combine with value and result save functions
     *
     */

    function save(user_message $msg): bool
    {
        log_debug($this->dsp_id());

        global $db_con;

        // init
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        // check the preserved names (only used for group names)
        if ($this->check_preserved($msg)) {

            // load the objects if needed
            if ($this->is_link_obj()) {
                $this->load_objects();
            }

            // configure the global database connection object for the select, insert, update and delete queries
            $db_con->set_class($this::class);
            $db_con->set_usr($this->get_user()->id);

            // create an object to check possible duplicates
            $similar = null;

            // if a new object is supposed to be added check upfront for a similar object to prevent adding duplicates
            if ($this->id() == 0) {
                log_debug('check possible duplicates before adding ' . $this->dsp_id());
                $similar = $this->get_similar($msg);
                if ($similar !== null) {
                    if ($similar->id() <> 0) {
                        // check that the get_similar function has really found a similar object and report potential program errors
                        if (!$this->is_similar($similar)) {
                            $msg->add(msg_id::SANDBOX_NOT_SIMILAR, [
                                msg_id::VAR_ID => $this->dsp_id(),
                                msg_id::VAR_ID_CHK => $similar->dsp_id()
                            ]);
                        } else {
                            // if similar is found set the id to trigger the updating instead of adding
                            $similar->load_by_id($similar->id); // e.g. to get the type_id
                            // prevent that the id of a formula is used for the word with the type formula link
                            if (get_class($this) == get_class($similar)) {
                                $this->id = $similar->id();
                            } else {
                                if (!((get_class($this) == word::class and get_class($similar) == formula::class)
                                    or (get_class($this) == triple::class and get_class($similar) == formula::class))) {
                                    $msg->merge($similar->id_used_msg($this));
                                }
                            }
                        }
                    } else {
                        $similar = null;
                    }
                }
            }
        }

        // create a new object if nothing similar has been found
        if ($msg->is_ok()) {
            if (!$this->is_saved()) {
                log_debug('add');
                $this->add($msg);
            } else {
                // if the similar object is not the same as $this object, suggest renaming $this object
                if ($similar != null) {
                    log_debug('got similar and suggest renaming or merge');
                    // e.g. if a source already exists update the source
                    // but if a word with the same name of a formula already exists suggest a new formula name
                    if (!$this->is_same($similar)) {
                        $msg->merge($similar->id_used_msg($this));
                    }
                }

                // update the existing object
                if ($msg->is_ok()) {
                    log_debug('update');

                    // read the database values to be able to check if something has been changed;
                    // done first, because it needs to be done for user and general object values
                    $db_rec = clone $this;
                    $db_rec->reset();
                    $db_rec->set_user($this->get_user());
                    if ($db_rec->load_by_id($this->id()) != $this->id()) {
                        $msg->add(msg_id::FAILED_RELOAD_OBJECT, [
                            msg_id::VAR_CLASS_NAME => $class_name,
                            msg_id::VAR_VAL_ID => $this->id()
                        ]);
                    } else {
                        log_debug('reloaded from db');
                        if ($this->is_link_obj()) {
                            if (!$db_rec->load_objects()) {
                                $msg->add(msg_id::FAILED_RELOAD_OBJECT, [
                                    msg_id::VAR_VALUE => $class_name,
                                    msg_id::VAR_NAME => $this->name()
                                ]);
                            }
                            // configure the global database connection object again to overwrite any changes from load_objects
                            $db_con->set_class($this::class);
                            $db_con->set_usr($this->get_user()->id);
                        }
                        // relevant is if there is a user config in the database
                        // so use this information to prevent
                        // the need to forward the db_rec to all functions
                        if ($db_rec->has_usr_cfg() and !$this->has_usr_cfg()) {
                            $this->usr_cfg_id = $db_rec->usr_cfg_id;
                        }
                    }

                    // load the common object
                    $std_rec = $this->clone_reset(true); // user must also be set to allow to take the ownership
                    $std_rec->set_user($this->get_user());
                    if ($msg->is_ok()) {
                        if (!$std_rec->load_standard($this->id(), $msg)) {
                            $msg->add(msg_id::DEFAULT_VALUES_RELOADING_FAILED, [msg_id::VAR_VALUE => $class_name]);
                        }
                    }

                    // for a correct user setting detection (function can_change) set the owner even if the object has not been loaded before the save
                    if ($msg->is_ok()) {
                        log_debug('standard loaded');

                        if ($this->owner_id() <= 0) {
                            $this->set_owner_id($std_rec->owner_id());
                        }
                    }

                    // if a problem has appeared up to here, don't try to save the values
                    // the problem is shown to the user by the calling interactive script
                    // TODO add function based saving
                    if ($msg->is_ok()) {
                        $this->save_fields_func($db_con, $db_rec, $std_rec, $msg);
                    }
                }
            }
            if (!$msg->is_ok()) {
                log_err($msg->get_last_message(), 'user_sandbox_' . $class_name . '->save');
            }
        }

        return $msg->is_ok();
    }


    /*
     * delete
     */

    /**
     * delete the complete object (the calling function del must have checked that no one uses this object)
     * @returns string the message that should be shown to the user if something went wrong or an empty string if everything is fine
     */
    private function del_exe(user_message $usr_msg): bool
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        global $sys;
        global $db_con;

        $msg = '';

        // log the deletion request
        if ($this->is_link_obj()) {
            $log = $this->log_del_link();
        } else {
            $log = $this->log_del();
        }
        if ($log->id > 0) {
            $db_con->usr_id = $this->get_user()->id;

            // TODO Prio 1 activate
            // $msg = $this->del_links();
            // $usr_msg->merge($msg);

            // delete first all user configuration that have also been excluded
            if ($usr_msg->is_ok()) {
                // TODO always use the qp based setup
                if ($this::class == value::class) {
                    $qp = $this->sql_delete($db_con->sql_creator(), $usr_msg, new sql_type_list([sql_type::USER, sql_type::EXCLUDE]));
                    $db_con->delete($qp, $this::class . ' user exclusions', $usr_msg);
                } else {
                    log_err('Delete of user link for ' . $this::class . ' not yet defined');
                }
            }
            if ($usr_msg->is_ok()) {
                // finally, delete the object
                if ($this::class == value::class) {
                    $qp = $this->sql_delete($db_con->sql_creator(), $usr_msg);
                    $db_con->delete($qp, $this::class . ' user exclusions', $usr_msg);
                } else {
                    log_err('Delete of link for ' . $this::class . ' not yet defined');
                }
                log_debug('of ' . $this->dsp_id() . ' done');
            } else {
                log_err('Delete failed for ' . $this::class, $this::class . '->del_exe', 'Delete failed, because removing the user settings for ' . $class_name . ' ' . $this->dsp_id() . ' returns ' . $msg, (new Exception)->getTraceAsString(), $this->get_user());
            }
        }

        return $usr_msg->get_last_message();
    }

    /**
     * exclude or delete an object
     * similar to the sandbox del function but for more than one table
     *
     * @param user_message with status ok
     *                     or if something went wrong
     *                     the message that should be shown to the user
     *                     including suggested solutions
     * @return bool true if the value has been deleted without issues
     *
     * TODO if the owner deletes it, change the owner to the new median user
     * TODO check if all have deleted the object
     *      does not remove the user excluding if no one else is using it
     */
    function del(user_message $usr_msg, bool $must_exist = true): bool
    {
        log_debug($this->dsp_id());
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        global $db_con;
        $msg = '';

        // refresh the object with the database to include all updates utils now (TODO start of lock for commit here)
        // TODO it seems that the owner is not updated
        $reloaded = false;
        $reloaded_id = $this->load_by_id($this->id());
        if ($reloaded_id != 0) {
            $reloaded = true;
        }

        if (!$reloaded) {
            log_warning('Reload of for deletion has lead to unexpected', $this::class . '->del', 'Reload of ' . $class_name . ' ' . $this->dsp_id() . ' for deletion or exclude has unexpectedly lead to ' . $msg . '.', (new Exception)->getTraceAsString(), $this->get_user());
        } else {
            log_debug('reloaded ' . $this->dsp_id());
            // check if the object is still valid
            if ($this->id() <= 0) {
                log_warning('Delete failed', $this::class . '->del', 'Delete failed, because it seems that the ' . $class_name . ' ' . $this->dsp_id() . ' has been deleted in the meantime.', (new Exception)->getTraceAsString(), $this->get_user());
            } else {
                // reload the objects if needed
                if ($this->is_link_obj()) {
                    if (!$this->load_objects()) {
                        $msg .= 'Reloading of linked objects ' . $class_name . ' ' . $this->dsp_id() . ' failed.';
                    }
                }
                // check if the object simply can be deleted, because it has never been used
                if (!$this->used_by_someone_else()) {
                    $msg .= $this->del_exe($usr_msg);
                } else {
                    // if the owner deletes the object find a new owner or delete the object completely
                    if ($this->owner_id() == $this->get_user()->id) {
                        log_debug('owner has requested the deletion');
                        // get median user
                        $new_owner_id = $this->median_user();
                        if ($new_owner_id == 0) {
                            log_err('Delete failed', $this::class . '->del', 'Delete failed, because no median user found for ' . $class_name . ' ' . $this->dsp_id() . ' but change is nevertheless not allowed.', (new Exception)->getTraceAsString(), $this->get_user());
                        } else {
                            log_debug('set owner for ' . $this->dsp_id() . ' to user id "' . $new_owner_id . '"');

                            // TODO change the original object, so that it uses the configuration of the new owner

                            // set owner
                            if (!$this->set_owner($new_owner_id, $usr_msg, $must_exist)) {
                                $msg .= 'Setting of owner while deleting ' . $class_name . ' failed';
                                log_err($msg, $this::class . '->del');

                            }

                            // delete all user records of the new owner
                            // does not use del_usr_cfg because the deletion request has already been logged
                            if ($msg == '') {
                                if (!$this->del_usr_cfg_exe($db_con)) {
                                    $msg .= 'Deleting of ' . $class_name . ' failed';
                                }
                            }

                        }
                    }
                    // check again after the owner change if the object simply can be deleted, because it has never been used
                    // TODO check if "if ($this->can_change() AND $this->not_used()) {" would be correct
                    if (!$this->used_by_someone_else()) {
                        log_debug('can delete ' . $this->dsp_id() . ' after owner change');
                        $this->del_exe($usr_msg);
                    } else {
                        log_debug('exclude ' . $this->dsp_id());
                        $this->exclude();

                        // simple version TODO combine with save function

                        $db_rec = clone $this;
                        $db_rec->reset();
                        $db_rec->set_user($this->get_user());
                        if ($db_rec->load_by_id($this->id())) {
                            log_debug('reloaded ' . $db_rec->dsp_id() . ' from database');
                        }
                        $std_rec = $this->clone_reset();
                        $std_msg_txt = '';
                        if ($usr_msg->is_ok()) {
                            $std_msg = clone $usr_msg;
                            $std_rec->set_user($this->get_user()); // must also be set to allow to take the ownership
                            if (!$std_rec->load_standard($this->id(), $std_msg)) {
                                $std_msg_txt = 'Reloading of standard ' . $class_name . ' ' . $this->dsp_id() . ' failed.';
                            }
                            if ($must_exist) {
                                $usr_msg->merge($std_msg);
                                $msg .= $std_msg_txt;
                            }
                        }
                        if ($std_msg_txt == '') {
                            log_debug('loaded standard ' . $std_rec->dsp_id());
                            $this->save_fields_func($db_con, $db_rec, $std_rec, $usr_msg);
                        }
                    }
                }
            }
            // TODO end of db commit and unlock the records
            log_debug('done');
        }

        $usr_msg->add_message_text($msg);
        return $usr_msg->is_ok();
    }

    /**
     * save all updated fields with one sql function
     * similar to the sandbox save_fields_func function but for more than one table
     * *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param sandbox_multi $db_obj the database record before the saving
     * @param sandbox_multi $norm_obj the database record defined as standard because it is used by most users
     * @param user_message $usr_msg with the description of any problems for the user and the suggested solution
     * @return bool true if everything has been fine
     */
    function save_fields_func(
        sql_db        $db_con,
        sandbox_multi $db_obj,
        sandbox_multi $norm_obj,
        user_message  $usr_msg = new user_message()
    ): bool
    {
        // the sql creator is used more than once, so create it upfront
        $sc = $db_con->sql_creator();
        // the sql function should include the log of the changes
        $sc_par_lst = new sql_type_list([sql_type::LOG]);
        // get a list of all fields that could potentially be updated
        $all_fields = $this->db_fields_all();
        // get the object name for the log messages
        $lib = new library();
        $obj_name = $lib->class_to_name($this::class);

        // if the user is allowed to change the norm row e.g. because no other user has used it, change the norm row directly
        if ($this->can_change()) {
            // TODO check if the update of the standard db row will lead to unexpected changes for other users
            // TODO get a list of all user that have used the standard more than the threshold
            // TODO create a user db row for all these users that undo the expected changes of the standard db row
            // if there is no difference between the user row and the norm row remove all fields from the user row
            if ($this->no_diff($norm_obj, $usr_msg)) {
                if ($this->has_usr_cfg()) {
                    $qp = $this->sql_delete($sc, $usr_msg, new sql_type_list([sql_type::USER]));
                    $db_con->delete($qp, 'remove user overwrites of ' . $this->dsp_id(), $usr_msg);
                }
            } else {
                // apply the changes directly to the norm db record
                // TODO maybe check of other user have used the object and if yes keep or inform
                $fvt_lst = $this->db_fields_changed($db_obj, $usr_msg, $sc_par_lst);
                if (!$fvt_lst->is_empty_except_internal_fields()) {
                    $sc_par_lst->add(sql_type::UPDATE);
                    // call sql_write instead of sql_update_switch function to add the multi key fields based on the value type
                    $qp = $this->sql_write($sc, $db_obj, $all_fields, $usr_msg, $sc_par_lst);
                    $db_con->update($qp, 'update ' . $obj_name . $this->dsp_id(), $usr_msg);
                    if ($this->has_usr_cfg()) {
                        $sc_par_lst->add(sql_type::USER);
                        $qp = $this->sql_delete($sc, $usr_msg, $sc_par_lst);
                        $db_con->delete($qp, 'del user ' . $obj_name, $usr_msg);
                    }
                }
            }
            if ($usr_msg->is_ok()) {
                // check if some user overwrites can be removed
                $this->del_usr_cfg_if_not_needed(); // don't care what the result is, because in most cases it is fine to keep the user sandbox row
            }
        } else {
            $sc_par_lst->add(sql_type::USER);
            // TODO review if $this or $db_obj must be used here because in sandbox $this is used
            if ($db_obj->has_usr_cfg()) {
                if ($this->no_diff($norm_obj, $usr_msg)) {
                    $qp = $this->sql_delete($sc, $usr_msg, new sql_type_list([sql_type::USER]));
                    $db_con->delete($qp, 'remove user overwrites of ' . $this->dsp_id(), $usr_msg);
                } else {
                    $sc_par_lst->add(sql_type::UPDATE);
                    // call sql_write instead of sql_update_switch function to add the multi key fields based on the value type
                    // for a new user record compare with the norm db_row
                    // TODO compare sql_write with sql_update_switch
                    $qp = $this->sql_write($sc, $db_obj, $all_fields, $usr_msg, $sc_par_lst);
                    if ($qp != null) {
                        $db_con->update($qp, 'update user ' . $obj_name, $usr_msg);
                    }
                }
            } else {
                if (!$this->no_diff($norm_obj, $usr_msg)) {
                    $sc_par_lst->add(sql_type::INSERT);
                    $sc_par_lst->add(sql_type::NO_ID_RETURN);
                    // because one user can link a value to more than one source the source id is part of the user value prime key
                    // and due to that the source id in the user table cannot be null instead 0 is used
                    if ($this->source_id() == null) {
                        $src = new source($this->get_user());
                        $src->id = sources::TRUST_ME_BRO_ID;
                        $this->set_source($src);
                    }
                    // use the norm db_row to recreate the field list to include the id for the user table and to create the diff vs the norm db_row
                    $qp = $this->sql_write($sc, $db_obj, $all_fields, $usr_msg, $sc_par_lst);
                    // TODO compare sql_write with sql_insert_switch
                    $db_con->insert($qp, 'add user ' . $obj_name, $usr_msg, true);
                }
            }
        }

        log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        return $usr_msg->is_ok();
    }

    /**
     * create a sql statement to insert or update a sandbox object in the database
     * similar to sandbox_multi->sql_insert_switch and ->sql_update_switch but as a combined function not to repeat the id field creation
     * TODO move the code to an object used by sandbox and sandbox_value
     *
     * @param sql_creator $sc with the target db_type set
     * @param sandbox_multi|null $db_obj the user sandbox object with the database values before the update or the standard db_row
     * @param array $fld_lst_all list of field names of the given object
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par|null the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_write(
        sql_creator        $sc,
        sandbox_multi|null $db_obj,
        array              $fld_lst_all,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst = new sql_type_list()
    ): sql_par|null
    {
        // set the target sql table type for this value
        $sc_par_lst->add($this->table_type());
        // set the target sql table type for numeric, text or geo values
        $sc_par_lst->add($this->value_type());
        // get the name indicator how many id fields are user
        $id_ext = $this->table_extension();
        // get the prime db key list for this sandbox object
        $fvt_lst_id = $this->id_fvt_lst($sc_par_lst);
        // clone to keep the db key list unchanged
        $fvt_lst = clone $fvt_lst_id;
        // add the list of the changed fields to the id list
        $fvt_lst->add_list($this->db_fields_changed($db_obj, $usr_msg, $sc_par_lst));
        // get the list of all fields that can be changed by the user
        $fld_lst_ex_id = array_diff($fld_lst_all, $fvt_lst_id->names());
        // get the changed fields
        $chg_lst_ex_id = array_diff($fvt_lst->names(), $fvt_lst_id->names());

        if (count($chg_lst_ex_id) > 0) {
            // make the query name unique based on the changed fields
            $lib = new library();
            $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_ex_id, $usr_msg);

            // create the main query parameter object and set the query name
            $qp = $this->sql_common($sc, $sc_par_lst, $ext, $id_ext);

            // overwrite the standard auto increase id field name (added for multi tables)
            $sc->set_id_field($this->id_field($sc_par_lst));
            // use the query name for the sql creation (added for multi tables)
            $sc->set_name($qp->name);

            // actually create the sql statement
            if ($sc_par_lst->incl_log()) {
                // log functions must always use named parameters
                $sc_par_lst->add(sql_type::NAMED_PAR);
                $qp = $this->sql_write_with_log($sc, $qp, $fvt_lst_id, $fvt_lst, $fld_lst_all, $usr_msg, $sc_par_lst);
            } else {
                if ($sc_par_lst->is_insert()) {
                    $qp->sql = $sc->create_sql_insert($fvt_lst);
                    // set the parameters for the query execution
                    $qp->par = $fvt_lst->db_values();
                } else {
                    $qp->sql = $sc->create_sql_update_fvt($fvt_lst_id, $fvt_lst, $sc_par_lst);
                    // and remember the parameters used
                    $qp->par = $sc->par_values();
                }
            }
        } else {
            $qp = null;
        }
        return $qp;
    }

    /**
     * create the sql statement to add a new value and log the changes
     *
     * @param sql_creator $sc sql creator with the target db_type already set
     * @param sql_par_field_list $fvt_lst_id list of id field names, values and sql types additional to the standard id fields
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id fields
     * @param array $fld_lst_all list of all potential field names of the given object that can be changed by the user
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_write_with_log(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst_id,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // init the function body
        $sql = $sc->sql_func_start('', $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst->add(sql_type::NO_ID_RETURN);
        $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::SUB);
        $sc_par_lst_sub->add(sql_type::SELECT_FOR_INSERT);
        $sc_par_lst_log = $sc_par_lst_sub->remove(sql_type::STANDARD);

        // add the change action field to the field list for the log entries
        global $sys;
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $sys->typ_lst->cng_act->id(change_actions::ADD),
            type_object::FLD_ID_SQL_TYP
        );

        // get the fields for the value log entry
        // TODO review check why a different list for the log is needed; instead use the field names like in sandbox
        $fvt_lst_log = clone $fvt_lst;
        $fvt_lst_log->add_field(group_db::FLD_ID, $this->grp()->id());
        $fvt_lst_log->add_field(user_db::FLD_ID, $this->get_user_id(), sql_par_type::INT);

        // create the log entry for the value
        if ($fvt_lst_log->has_name($this::FLD_VALUE)) {
            $qp_log = $sc->sql_func_log_value($this, $this->get_user(), $fvt_lst_log, $sc_par_lst_log);
            $sql .= ' ' . $qp_log->sql;
        } else {
            // TODO review
            $qp_name = $sc->name();
            $qp_log = $sc->sql_par($this::class, $sc_par_lst_log);
            $qp_log->par_fld_lst = new sql_par_field_list();
            $qp_log->name = $qp_name;
            $qp_log->sql = ' ';
            $sc->set_name($qp_name);
        }

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();
        $par_lst_out->add_list($qp_log->par_fld_lst);

        // get the data fields and move the unique db key field to the first entry
        $fld_lst_ex_log = array_intersect($fvt_lst->names(), $fld_lst_all);

        // check if other vars than the value have been changed
        $fld_lst_ex_id = array_diff($fld_lst_ex_log, $fvt_lst_id->names());
        $fld_lst_ex_id_and_val = array_diff($fld_lst_ex_id, [
            change_action::FLD_ID,
            sandbox_multi::FLD_VALUE,
            value_db::FLD_VALUE_TIME,
            value_db::FLD_VALUE_TEXT,
            value_db::FLD_VALUE_GEO,
            sandbox_multi::FLD_LAST_UPDATE
        ]);

        // ... and log the value parameter changes if needed
        if (count($fld_lst_ex_id_and_val) > 0) {
            $qp_log = $sc->sql_func_log($this::class, $this->get_user(), $fld_lst_ex_id_and_val, $fvt_lst_log, $usr_msg, $sc_par_lst_log, $this);
            $sql .= ' ' . $qp_log->sql;
            $par_lst_out->add_list($qp_log->par_fld_lst);

            // add the group_id to the parameter list if it has not yet been added e.g. because the number has not been changed
            if ($this->is_prime()) {
                $par_lst_out->add_field(
                    group_db::FLD_ID,
                    $this->grp()->id(),
                    sql_par_type::INT);
            } else {
                $par_lst_out->add_field(
                    group_db::FLD_ID,
                    $this->grp()->id(),
                    sql_par_type::TEXT);
            }
        }

        // insert a new row
        $sc_write = clone $sc;
        $qp_write = $this->sql_common($sc_write, $sc_par_lst_sub);
        $sc_write->set_name($qp_write->name);

        // collect the fields that should be written to the database
        $fvt_lst_write = new sql_par_field_list();
        // add the id to the changes
        // TODO maybe net out with calling function and / or make list correct from beginning
        $fvt_lst_all = clone $fvt_lst;
        if ($sc_par_lst->is_update()) {
            $fvt_lst_all->add_list($fvt_lst_id);
        }
        if ($sc_par_lst->is_insert()) {
            foreach ($fvt_lst_id->names() as $fld) {
                $fvt_lst_write->add($fvt_lst_all->get($fld, $usr_msg));
            }
        }
        // add the changed group fields
        if ($this::class == group::class) {
            foreach ($fld_lst_ex_id as $fld) {
                $fvt_lst_write->add($fvt_lst_all->get($fld, $usr_msg));
            }
        }
        // add the user id only if a new user sandbox row is created
        if (!$sc_par_lst->is_standard()) {
            if ($fvt_lst_all->has_name(user_db::FLD_ID)) {
                if ($sc_par_lst->is_insert()) {
                    $fvt_lst_write->add($fvt_lst_all->get(user_db::FLD_ID, $usr_msg));
                } elseif ($sc_par_lst->is_update()
                    and $fvt_lst_all->get_old_id(user_db::FLD_ID) != $fvt_lst_all->get_id(user_db::FLD_ID))  {
                    // ... or if the owner is updated
                    $fvt_lst_write->add($fvt_lst_all->get(user_db::FLD_ID, $usr_msg));
                }
            }
        }
        if ($this->is_numeric()) {
            $val_fld = $fvt_lst_all->get(sandbox_multi::FLD_VALUE, $usr_msg, true);
        } elseif ($this->is_time_value()) {
            $val_fld = $fvt_lst_all->get(value_db::FLD_VALUE_TIME, $usr_msg, true);
        } elseif ($this->is_text_value()) {
            $val_fld = $fvt_lst_all->get(value_db::FLD_VALUE_TEXT, $usr_msg, true);
        } elseif ($this->is_geo_value()) {
            $val_fld = $fvt_lst_all->get(value_db::FLD_VALUE_GEO, $usr_msg, true);
        } else {
            $val_fld = $fvt_lst_all->get(sandbox_multi::FLD_VALUE, $usr_msg, true);
        }
        if ($val_fld != null) {
            $fvt_lst_write->add($val_fld);
        }

        // add the source field if it has changed, e.g. a source-only update of an existing value,
        // so that the update SET clause is not empty (the source is part of the changed field list,
        // but it is not one of the value/share/protect/last_update fields added above);
        // skip it for the user table where the source id is part of the unique key and therefore
        // used in the WHERE clause instead of the SET clause
        if (!in_array(source_db::FLD_ID, $fvt_lst_id->names())) {
            $src_fld = $fvt_lst_all->get(source_db::FLD_ID, $usr_msg, true);
            if ($src_fld != null) {
                $fvt_lst_write->add($src_fld);
            }
        }

        // sandbox fields
        $fvt_lst_write->add($fvt_lst_all->get(sandbox::FLD_SHARE, $usr_msg, true));
        $fvt_lst_write->add($fvt_lst_all->get(sandbox::FLD_PROTECT, $usr_msg, true));

        if (!$sc_par_lst->is_standard()) {
            $fvt_lst_write->add($fvt_lst_all->get(sandbox_multi::FLD_LAST_UPDATE, $usr_msg, true));
        }

        if ($sc_par_lst->is_insert()) {
            // create the sql to actually add the value to the database
            $qp_write->sql = $sc_write->create_sql_insert($fvt_lst_write, $sc_par_lst_sub);
        } else {
            // create the sql to actually update the value to the database
            $qp_write->sql = $sc_write->create_sql_update_fvt($fvt_lst_id, $fvt_lst_write, $sc_par_lst_sub);
        }
        // add the insert row to the function body
        $sql .= ' ' . $qp_write->sql . ' ';
        // add the fields used to the parameter list except the sql Now() function call
        $fvt_lst_write->del(sandbox_multi::FLD_LAST_UPDATE);
        $par_lst_out->add_list($fvt_lst_write);
        if ($sc_par_lst->is_update()) {
            $par_lst_out->add_list($fvt_lst_id);
        }

        // close the sql function statement
        $sql .= $sc->sql_func_end();

        // create the query parameters for the actual change
        $qp_chg = clone $qp;
        $qp_chg->sql = $sc->create_sql_insert($par_lst_out, $sc_par_lst);

        // merge all together and create the function
        $qp->sql = $qp_chg->sql . $sql . ';';
        $qp->par = $par_lst_out->values();

        // create the call sql statement
        return $sc->sql_call($qp, $qp_chg->name, $par_lst_out);
    }

    /**
     * dummy function to get the list of all id fields
     * to be overwritten by the child objects
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the id fields, values and types for this value or result object
     */
    function id_fvt_lst(sql_type_list $sc_par_lst = new sql_type_list()): sql_par_field_list
    {
        $usr_msg = new user_message();
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'id_fvt_lst',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return new sql_par_field_list;
    }

    /**
     * detects if this object has be changed compared to the given object
     *
     * @param sandbox_multi $db_obj the user database or standard record for compare
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @return bool true if any of the fields does not match
     */
    function no_diff(
        sandbox_multi $db_obj,
        user_message  $usr_msg
    ): bool
    {
        // for the check it is not relevant if only the user differs
        $chk_obj = clone $this;
        $chk_obj->set_user($db_obj->get_user());
        // if this object does not yet have a db key ignore this
        if ($chk_obj->id() == 0) {
            $chk_obj->set_id($db_obj->id());
        }
        $fvt_lst = $chk_obj->db_fields_changed($db_obj, $usr_msg);
        return $fvt_lst->is_empty_except_internal_fields();
    }

    /**
     * detects if this object has been changed compared to the given object,
     * excluding changes on internal fields like last_update
     *
     * @param sandbox_multi $db_obj the user database or standard record for compare
     * @return bool true if any of the fields does not match
     */
    function no_non_id_diff(
        sandbox_multi $db_obj,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): bool
    {
        $fvt_lst = $this->db_fields_changed($db_obj, $usr_msg, $sc_par_lst);
        return $fvt_lst->is_empty_except_id_and_internal_fields();
    }


    /*
     * sql write fields
     */

    /**
     * list of all fields that can be changed by the user in this object
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array with the field names of the object and any child object
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        $fields = [];

        $fields[] = user_db::FLD_ID;
        // the share and protection fields are added at the end

        return $fields;
    }

    /**
     * get a list of database field names, values and types that have been updated
     * dummy function overwritten by the child object
     *
     * @param sandbox_multi $sbx the same named sandbox as this to compare which fields have been changed
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox_multi $sbx,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $lst = new sql_par_field_list();
        $do_log = $sc_par_lst->incl_log();
        $sc = new sql_creator();
        $table_id = $sc->table_id($this::class);

        // to update the owner
        if ($this->is_default()) {
            if ($sbx->get_user()->id() !== $this->get_user()->id()) {
                if ($do_log) {
                    $lst->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_ID,
                        $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_ID),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                }
                $lst->add_field(
                    user_db::FLD_ID,
                    $this->get_user()->name(),
                    sql_field_type::NAME,
                    $sbx->get_user()->name(),
                    user_db::FLD_NAME,
                    $this->get_user()->id(),
                    $sbx->get_user()->id(),
                    sql_field_type::INT
                );
            }
        }

        return $lst;
    }

    /**
     * create the sql statement to add a new value or result to the database
     * TODO review
     * TODO check if it can be merged with the sandbox function
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param array $fld_lst_all list of field names of the given object
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_insert_switch(
        sql_creator        $sc,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst = new sql_type_list()): sql_par
    {
        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all, $usr_msg);

        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);

        if ($sc_par_lst->incl_log()) {
            // log functions must always use named parameters
            $sc_par_lst->add(sql_type::NAMED_PAR);
            $qp = $this->sql_insert_with_log($sc, $qp, $fvt_lst, $fld_lst_all, $usr_msg, $sc_par_lst);
        } else {
            // add the child object specific fields and values
            $qp->sql = $sc->create_sql_insert($fvt_lst);
            $qp->par = $fvt_lst->db_values();
        }

        return $qp;
    }

    /**
     * create the sql statement to change or exclude a sandbox object e.g. value to the database
     * either via a prepared SQL statement or via a function that includes the logging
     * similar to sandbox->sql_update_switch but for objects that are stored in several tables like values or results
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param array $fld_lst_all list of field names of the given object
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL update statement, the name of the SQL statement, and the parameter list
     */
    function sql_update_switch(
        sql_creator        $sc,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all, $usr_msg);

        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);

        if ($sc_par_lst->incl_log()) {
            // log functions must always use named parameters
            $sc_par_lst->add(sql_type::NAMED_PAR);
            $sc_par_lst->add(sql_type::NO_ID_RETURN);
            $qp = $this->sql_update_named_and_log($sc, $qp, $fvt_lst, $fld_lst_all, $usr_msg, $sc_par_lst);
        } else {
            if ($sc_par_lst->is_usr_tbl()) {
                $qp->sql = $sc->create_sql_update(
                    [$this->id_field(), user_db::FLD_ID], [$this->id(), $this->get_user_id()], $fvt_lst);
            } else {
                $qp->sql = $sc->create_sql_update($this->id_field(), $this->id(), $fvt_lst);
            }
            $qp->par = $sc->par_values();
        }

        return $qp;
    }

    /**
     * create the sql statement to add a new value or result to the database
     * TODO review
     * TODO add qp merge
     *
     * @param sql_creator $sc sql creator with the target db_type already set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id fields
     * @param array $fld_lst_all list of all potential field names of the given object that can be changed by the user
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    private function sql_insert_with_log(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();

        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $id_field = $sc->id_field_name();
        $var_name_row_id = $sc->var_name_row_id($sc_par_lst);

        // add the change action field to the field list for the log entries
        global $sys;
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $sys->typ_lst->cng_act->id(change_actions::ADD),
            type_object::FLD_ID_SQL_TYP
        );

        // init the function body
        $id_fld_new = $sc->var_name_new_id($sc_par_lst);
        $sql = $sc->sql_func_start($id_fld_new, $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::LIST);
        $sc_par_lst_log = clone $sc_par_lst_sub;
        $sc_par_lst_log->add(sql_type::INSERT_PART);

        // create sql to set the prime key upfront to get the sequence id
        $qp_id = clone $qp;
        if (!$usr_tbl) {
            $qp_id = $this->sql_insert_key_field($sc, $qp_id, $fvt_lst, $id_fld_new, $usr_msg, $sc_par_lst_sub);
            $par_lst_out->add($qp_id->par_fld);
            $sql .= $qp_id->sql;
        }

        // get the data fields and move the unique db key field to the first entry
        $fld_lst_ex_log = array_intersect($fvt_lst->names(), $fld_lst_all);
        if ($usr_tbl) {
            $key_fld_pos = array_search($this->id_field(), $fld_lst_ex_log);
            unset($fld_lst_ex_log[$key_fld_pos]);
            $key_fld_pos = array_search(user_db::FLD_ID, $fld_lst_ex_log);
            unset($fld_lst_ex_log[$key_fld_pos]);
            $fld_lst_ex_log_and_key = $fld_lst_ex_log;
        } else {
            $key_fld_pos = array_search($this->name_field(), $fld_lst_ex_log);
            unset($fld_lst_ex_log[$key_fld_pos]);
            $fld_lst_ex_log_and_key = array_merge([$qp_id->par_fld->name], $fld_lst_ex_log);
        }

        // remove the internal last update field from the list of field that should be logged
        $fld_lst_log = array_diff($fld_lst_ex_log_and_key, [
            formula_db::FLD_LAST_UPDATE
        ]);

        // create the query parameters for the log entries for the single fields
        $qp_log = $sc->sql_func_log($this::class, $this->get_user(), $fld_lst_log, $fvt_lst, $usr_msg, $sc_par_lst_log, $this);
        $sql .= ' ' . $qp_log->sql;
        $par_lst_out->add_list($qp_log->par_fld_lst);


        if (!$sc_par_lst->is_call_only()) {
            if ($usr_tbl) {
                // insert a new row in the user table
                $fld_lst_ex_log_and_key = array_merge([$this->id_field(), user_db::FLD_ID], $fld_lst_ex_log);
                $fvt_lst_ex_log_and_key = $fvt_lst->get_intersect($fld_lst_ex_log_and_key);
                $sc_insert = clone $sc;
                $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub);
                $sc_par_lst_sub->add(sql_type::NO_ID_RETURN);
                $qp_insert->sql = $sc_insert->create_sql_insert($fvt_lst_ex_log_and_key, $sc_par_lst_sub);
                // add the insert row to the function body and close the with statement with an ";"
                $sql .= ' ' . $qp_insert->sql . ';';
            } else {
                // update the fields excluding the unique id
                $update_fvt_lst = new sql_par_field_list();
                foreach ($fld_lst_ex_log as $fld) {
                    $update_fvt_lst->add($fvt_lst->get($fld, $usr_msg));
                }
                $sc_update = clone $sc;
                $sc_par_lst_upd = $sc_par_lst;
                $sc_par_lst_upd->add(sql_type::UPDATE);
                $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
                $sc_par_lst_upd_ex_log->add(sql_type::SUB);
                $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);

                $qp_update->sql = $sc_update->create_sql_update(
                    $id_field, $var_name_row_id, $update_fvt_lst, [], $sc_par_lst_upd_ex_log);
                // add the insert row to the function body
                $sql .= ' ' . $qp_update->sql . ' ';
            }
        }

        if ($sc->db_type == sql_db::POSTGRES) {
            if ($id_fld_new != '' and !$usr_tbl) {
                $sql .= sql::RETURN . ' ' . $id_fld_new . '; ';
            }
        }

        // create the query parameters for the actual change
        $qp_chg = clone $qp;

        if (!$sc_par_lst->is_call_only()) {
            $sql .= $sc->sql_func_end();

            $qp_chg->sql = $sc->create_sql_insert($par_lst_out, $sc_par_lst);

            // merge all together and create the function
            $qp->sql = $qp_chg->sql . $sql . ';';
        }
        $qp->par = $par_lst_out->values();

        // create the call sql statement
        return $sc->sql_call($qp, $qp_chg->name, $par_lst_out);
    }

    /**
     * create the sql statement to update a value or result in the database
     * similar to sandbox->sql_update_named_and_log but for objects that are stored in several tables like values or results
     *
     * TODO review
     * @param sql_creator $sc the sql creator object with the db type set
     * @param sql_par $qp the query parameter with the name already set
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param array $fld_lst_all
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst
     * @return sql_par
     */
    private function sql_update_named_and_log(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        array              $fld_lst_all,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        global $sys;

        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $ext = sql::NAME_SEP . sql_creator::FILE_INSERT;
        $id_fld = $sc->id_field_name();
        $id_val = '_' . $id_fld;

        // add the change action field to the list for the log entries
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $sys->typ_lst->cng_act->id(change_actions::UPDATE),
            type_object::FLD_ID_SQL_TYP
        );

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();

        // init the function body
        $sql = $sc->sql_func_start('', $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = $sc_par_lst->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::LIST);
        $sc_par_lst_log = clone $sc_par_lst_sub;
        $sc_par_lst_log->add(sql_type::INSERT_PART);
        if ($this->excluded) {
            $sc_par_lst_log->add(sql_type::EXCLUDE);
        }

        // get the fields actually changed
        $fld_lst = $fvt_lst->names();
        $fld_lst_chg = array_intersect($fld_lst, $fld_lst_all);

        // for the user sandbox table remove the primary key fields from the list
        if ($usr_tbl) {
            $key_fld_pos = array_search($id_fld, $fld_lst_chg);
            unset($fld_lst_chg[$key_fld_pos]);
            $key_fld_pos = array_search(user_db::FLD_ID, $fld_lst_chg);
            unset($fld_lst_chg[$key_fld_pos]);
        }

        // remove the internal last update field from the list of field that should be logged
        $fld_lst_log = array_diff($fld_lst_chg, [
            formula_db::FLD_LAST_UPDATE
        ]);

        // add the row id
        $fvt_lst->add_field(
            $sc->id_field_name(),
            $this->id(),
            $this->id_field_type());

        // create the query parameters for the log entries for the single fields
        $qp_log = $sc->sql_func_log_update(
            $this::class, $this->get_user(), $fld_lst_log, $fvt_lst, $sc_par_lst_log, $this->id(), $this);
        $sql .= ' ' . $qp_log->sql;
        $par_lst_out->add_list($qp_log->par_fld_lst);

        // add the name field if it is missing and the object should be excluded
        if ($this->excluded and $sc_par_lst->is_update()) {
            if ($this->is_named_obj()) {
                if (!$par_lst_out->has_name($this->name_field())) {
                    $table_id = $sc->table_id($this::class);
                    $par_lst_out->add_field(
                        sql::FLD_LOG_FIELD_PREFIX . $this->name_field(),
                        $sys->typ_lst->cng_fld->id($table_id . $this->name_field()),
                        change::FLD_FIELD_ID_SQL_TYP
                    );
                    $par_lst_out->add_field(
                        $this->name_field() . change::FLD_OLD_EXT,
                        $this->name(),
                        sandbox_named::FLD_NAME_SQL_TYP
                    );
                }
            }
        }

        // add additional log entry if the row has been excluded
        if ($this->excluded) {
            // TODO use a common function for this part with in sql_delete_and_log
            $sc_par_lst_log = clone $sc_par_lst_sub;
            $sc_par_lst_log->add(sql_type::EXCL_NAME_ONLY);
            $sc_par_lst_log->add(sql_type::SELECT_FOR_INSERT);
            $sc_par_lst_log->add(sql_type::UPDATE_PART);
            $sc_log = clone $sc;
            if ($this->is_named_obj()) {
                $log = new change($this->get_user());
                $log->set_class($this::class);
                $log->set_field($this->name_field());
                $log->old_value = $this->name();
                $log->new_value = null;
                $qp_log = $log->sql_insert_log(
                    $sc_log, $sc_par_lst_log, $ext . '_' . $this->name_field(), '', $this->name_field(), $id_val);
                $sql .= ' ' . $qp_log->sql . ';';
            } elseif ($this->is_link_obj()) {
                /*
                $qp_log = $sc->sql_func_log_link($this, $this, $this->get_user(), $par_lst_out, $sc_par_lst_log);
                $par_lst_out->add_list($qp_log->par_fld_lst);
                // TODO use these functions more often
                $par_lst_out->add_list($this->sql_key_fields_text_old($fvt_lst));
                $par_lst_out->add_list($this->sql_key_fields_id_old($fvt_lst));
                */
                $sql .= ' ' . $qp_log->sql;
            } else {
                log_err('Only named and link objects are supported in sandbox::sql_delete_and_log');
            }
        }

        // update the fields excluding the unique id
        $update_fvt_lst = new sql_par_field_list();
        foreach ($fld_lst_chg as $fld) {
            $update_fvt_lst->add($fvt_lst->get($fld, $usr_msg));
        }
        $sc_update = clone $sc;
        $sc_par_lst_upd = new sql_type_list([sql_type::NAMED_PAR, sql_type::UPDATE, sql_type::UPDATE_PART]);
        $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
        if ($usr_tbl) {
            $sc_par_lst_upd_ex_log->add(sql_type::USER);
        }
        $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);
        if ($usr_tbl) {
            $sc_par_lst_upd->add(sql_type::USER);
        }
        $qp_update->sql = $sc_update->create_sql_update(
            $id_fld, $id_val, $update_fvt_lst, [], $sc_par_lst_upd, true, '', $id_fld);
        // add the insert row to the function body
        $sql .= ' ' . $qp_update->sql . ' ';

        $sql .= $sc->sql_func_end();

        // create the query parameters for the actual change
        $qp_chg = clone $qp;
        $qp_chg->sql = $sc->create_sql_update(
            $id_fld, $id_val, $par_lst_out, [], $sc_par_lst);
        $qp_chg->par = $fvt_lst->values();

        // merge all together and create the function
        $qp->sql = $qp_chg->sql . $sql . ';';
        $qp->par = $par_lst_out->values();

        // create the call sql statement
        return $sc->sql_call($qp, $qp_chg->name, $par_lst_out);
    }

    /**
     * create the sql statement to add a new value or result to the database
     * TODO review
     * TODO add qp merge
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par $qp
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param string $id_fld_new
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst_sub the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_insert_key_field(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        string             $id_fld_new,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst_sub = new sql_type_list()
    ): sql_par
    {
        // set some var names to shorten the code lines
        $usr_tbl = $sc_par_lst_sub->is_usr_tbl();
        $ext = sql::NAME_SEP . sql_creator::FILE_INSERT;

        // list of parameters actually used in order of the function usage
        $sql = '';
        $fvt_insert = $fvt_lst->get($this->name_field(), $usr_msg);

        // create the sql to insert the row
        $fvt_insert_list = new sql_par_field_list();
        $fvt_insert_list->add($fvt_insert);
        $sc_insert = clone $sc;
        $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub, $ext);
        $sc_par_lst_sub->add(sql_type::SELECT_FOR_INSERT);
        if ($sc->db_type == sql_db::MYSQL) {
            $sc_par_lst_sub->add(sql_type::NO_ID_RETURN);
        }
        $qp_insert->sql = $sc_insert->create_sql_insert(
            $fvt_insert_list, $sc_par_lst_sub, true, '', '', '', $id_fld_new);
        $qp_insert->par = [$fvt_insert->value];

        // add the insert row to the function body
        $sql .= ' ' . $qp_insert->sql . '; ';

        // get the new row id for MySQL db
        if ($sc->db_type == sql_db::MYSQL and !$usr_tbl) {
            $sql .= ' ' . sql::LAST_ID_MYSQL . $sc->var_name_row_id($sc_par_lst_sub) . '; ';
        }

        $qp->sql = $sql;
        $qp->par_fld = $fvt_insert;

        return $qp;
    }

    /**
     * @return user_message a message to use a different name
     */
    function id_used_msg(sandbox_multi $obj_to_add): user_message
    {
        $lib = new library();
        $obj_to_add_name = $lib->class_to_name($obj_to_add::class);
        $msg = new user_message();
        $msg->add(msg_id::NAME_ALREADY_EXISTS, [
            msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
            msg_id::VAR_NAME => $obj_to_add->dsp_id(),
            msg_id::VAR_VALUE => $obj_to_add_name
        ]);
        return $msg;
    }

    /**
     * overwritten by the child objects
     * TODO to be overwritten by the sandbox value function
     * @return bool true if the db table for up to 4 phrases with a 8bit int key can be used
     */
    function is_prime(): bool
    {
        log_err('dummy is_prime() function called in sandbox_multi, which should never happen');
        return true;
    }

    /**
     * overwritten by the child objects
     * TODO to be overwritten by the sandbox value function
     * @return bool true if the db table for up to 4 phrases with a 8bit int key can be used
     */
    function is_main(): bool
    {
        log_err('dummy is_main() function called in sandbox_multi, which should never happen');
        return true;
    }

    /**
     * overwritten by the child objects
     * TODO to be overwritten by the sandbox value function
     * @return bool true if the db table key of 512bit is not enough
     */
    function is_big(): bool
    {
        log_err('dummy is_big() function called in sandbox_multi, which should never happen');
        return true;
    }

    /**
     * TODO create a function max_phrases that is overwritten by the result object
     * @param bool $all
     * @return array
     */
    function id_names(bool $all = false): array
    {
        if ($this::class == value::class) {
            return $this->grp()->id_names($all);
        } else {
            if ($this->is_main()) {
                if ($this->is_standard()) {
                    return $this->grp()->id_names($all, group_id::MAIN_PHRASES_STD);
                } else {
                    return $this->grp()->id_names($all, result_id::MAIN_PHRASES_ALL);
                }
            } else {
                return $this->grp()->id_names($all);
            }
        }
    }

    function id_lst(): array
    {
        return $this->grp()->id_lst();
    }

    /**
     * @return int|array|string the database id as used for the unique selection of one value
     *                          either the string with the id for the group id
     *                          or an 0 filled array with the phrase ids
     */
    function id_or_lst(): int|array|string
    {
        if ($this->is_prime() and $this::class != group::class) {
            $grp_id = new group_id();
            $id_lst = $grp_id->get_array($this->id());
            for ($i = count($id_lst); $i < group_id::PRIME_PHRASES_STD; $i++) {
                $id_lst[] = 0;
            }
            return $id_lst;
        } else {
            return $this->id();
        }
    }

    /**
     * dummy function to remove depending on objects, which needs to be overwritten by the child classes
     */
    function del_links(): user_message
    {
        $usr_msg = new user_message();
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'del_links',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of database field names, values and types that have been changed compared to a given object
     * to add to the list with the list of the child object e.g. word
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox_multi $sbx the same sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_changed_sandbox_list(sandbox_multi $sbx, sql_type_list $sc_par_lst): sql_par_field_list
    {
        global $sys;

        $lst = new sql_par_field_list();
        $sc = new sql_creator();
        $table_id = $sc->table_id($this::class);

        if ($sbx->excluded <> $this->excluded) {
            if ($sc_par_lst->incl_log()) {
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
                $sbx->excluded
            );
        }
        if ($sbx->share_id != $this->share_id) {
            if ($sc_par_lst->incl_log()) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_SHARE,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_SHARE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_SHARE,
                $this->share_id,
                self::FLD_SHARE_SQL_TYP,
                $sbx->share_id
            );
        }
        if ($sbx->protection_id <> $this->protection_id) {
            if ($sc_par_lst->incl_log()) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_PROTECT,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_PROTECT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_PROTECT,
                $this->protection_id,
                self::FLD_PROTECT_SQL_TYP,
                $sbx->protection_id
            );
        }
        return $lst;
    }


    /*
     * type field
     *
     * functions that are not used in this object, but in many child objects
     * and that is predefined in the main parent object to avoid code redundancy
     *
     * could and should be moved to a user_sandbox_type_extension object
     * as soon as php allows something like 'extends _sandbox_named and user_sandbox_type_extension'
     */

    /**
     * dummy function that should be overwritten by the child object
     * @return string the name of the object type
     */
    function type_name(): string
    {
        $usr_msg = new user_message();
        $usr_msg->add_err(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'type_name',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->get_last_message();
    }


    /*
     * sql write fields
     */

    /**
     * list of all fields that might be saved to the database
     * excluding internal object vars like the usr_cgf_id
     *
     * @return array with the field names of the object and any child object
     */
    function db_fields_all_sandbox(): array
    {
        return [sql_db::FLD_EXCLUDED, self::FLD_SHARE, self::FLD_PROTECT];
    }

    /**
     * list of fields that have been changed compared to a given object
     * the last_update field is excluded here because this is an internal only field
     *
     * @param sandbox_multi $sbx the same sandbox as this to compare which fields have been changed
     * @return array with the field names of the object and any child object
     */
    function db_fields_changed_sandbox(sandbox_multi $sbx): array
    {
        $lst = [];
        if ($sbx->excluded <> $this->excluded) {
            $lst[] = [
                sql_db::FLD_EXCLUDED,
                $this->excluded,
                sql_db::FLD_EXCLUDED_SQL_TYP
            ];
        }
        if ($sbx->share_id <> $this->share_id) {
            $lst[] = [
                self::FLD_SHARE,
                $this->share_id,
                self::FLD_SHARE_SQL_TYP
            ];
        }
        if ($sbx->protection_id <> $this->protection_id) {
            $lst[] = [
                self::FLD_PROTECT,
                $this->protection_id,
                self::FLD_PROTECT_SQL_TYP
            ];
        }
        return $lst;
    }


    /*
     * sql helper
     */

    function table_type(): sql_type
    {
        log_err('dummy table_type() function called in sandbox_multi, which should never happen');
        return sql_type::MAIN;
    }

    function value_type(): sql_type
    {
        log_err('dummy value_type() function called in sandbox_multi, which should never happen');
        return sql_type::NUMERIC;
    }

    public function sql_field_type(): sql_field_type
    {
        log_err('overwrite for sql_field_type() missing for ' . $this->dsp_id());
        return sql_field_type::NUMERIC_FLOAT;
    }

    function table_extension(): string
    {
        $msg = 'ERROR: dummy table_extension() function called in ' . $this::class . ', which should never happen';
        log_err($msg);
        return $msg;
    }

    /**
     * TODO review and add unit tests for all cases
     * @return sql_field_type with the type of the id
     */
    function id_field_type(): sql_field_type
    {
        if ($this->is_prime()) {
            return sql_field_type::INT;
        } elseif ($this->is_main()) {
            return sql_field_type::INT;
        } elseif ($this->is_big()) {
            return sql_field_type::TEXT;
        } else {
            return sql_field_type::KEY_512;
        }
    }

    /**
     * @param bool $usr_tbl true if also the user group id field should be returned
     * @param bool $usr_only true if only the user table field should be returned
     * @return string|array with the id field for a none prime value
     */
    function id_field_group(bool $usr_tbl = false, bool $usr_only = false): string|array
    {
        $lib = new library();
        $fld_name = $lib->class_to_name(group::class) . sql_db::FLD_EXT_ID;
        if (!$usr_tbl) {
            if ($usr_only) {
                return sql_db::TBL_USER_PREFIX . $fld_name;
            } else {
                return $fld_name;
            }
        } else {
            $id_fields = array();
            $id_fields[] = $fld_name;
            $id_fields[] = sql_db::TBL_USER_PREFIX . $fld_name;
            return $id_fields;
        }
    }


    /*
     * internal
     */

    /**
     * @return bool true if this sandbox object has a name as unique key
     * final function overwritten by the child object
     */
    function is_named_obj(): bool
    {
        return true;
    }

    /**
     * @return bool true if this sandbox object links two objects
     * final function overwritten by the child object
     */
    function is_link_obj(): bool
    {
        return false;
    }

    /**
     * @return bool true if this sandbox object is a value or result
     * final function overwritten by the child object
     */
    function is_value_obj(): bool
    {
        return true;
    }


    /*
     * internal check
     */

    /**
     * TODO deprecate because using the object const is actually faster in execution
     * return the expected database id field name of the object
     * should actually be static, but seems to be not yet possible
     * TODO check if it can be combined with id_field()
     */
    function fld_id(string $class = self::class): string
    {
        $lib = new library();
        return $lib->class_to_name($class) . sql_db::FLD_EXT_ID;
    }

    function fld_usr_id(string $class = self::class): string
    {
        $lib = new library();
        return sql_db::USER_PREFIX . $lib->class_to_name($class) . sql_db::FLD_EXT_ID;
    }

    function fld_name(string $class = self::class): string
    {
        $lib = new library();
        return $lib->class_to_name($class) . sql_db::FLD_EXT_NAME;
    }

    /**
     * @param object $api_obj frontend API object filled with the database id
     */
    function fill_api_obj(object $api_obj): void
    {
        $api_obj->set_id($this->id());
    }

    /**
     * TODO deprecate
     * fill a similar object that is extended with display interface functions
     *
     * @param object $dsp_obj the object that should be filled with all user sandbox values
     */
    function fill_ui_obj(object $dsp_obj): void
    {
        $dsp_obj->set_id($this->id());
        $dsp_obj->usr_cfg_id = $this->usr_cfg_id;
        $dsp_obj->usr = $this->get_user();
        $dsp_obj->set_owner_id($this->owner_id());
        $dsp_obj->excluded = $this->is_excluded();
    }

    /*
      these functions differ for each object, so they are always in the child class and not this in the superclass

      private function load_standard() {
      }

      function load() {
      }

    */

}


