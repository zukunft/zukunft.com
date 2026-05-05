<?php

/*

    model/user/user.php - a person who uses zukunft.com
    -------------------

    TODO make sure that no right gain is possible
    TODO move the non functional user parameters to hidden words to be able to reuse the standard view functionality
    TODO log the access attempts to objects with a restricted access
    TODO build a process so that a user can request access to an object with restricted access

    if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
    if a user has added 3 values and at least one is accepted by another user, he can add words and formula and he must have a valid email
    if a user has added 2 formula and both are accepted by at least one other user and no one has complained, he can change formulas and words, including linking of words
    if a user has linked a 10 words and all got accepted by one other user and no one has complained, he can request new verbs and he must have an validated address

    if a user got 10 pending word or formula discussion, he can no longer add words or formula utils the open discussions are less than 10
    if a user got 5 pending word or formula discussion, he can no longer change words or formula utils the open discussions are less than 5
    if a user got 2 pending verb discussion, he can no longer add verbs utils the open discussions are less than 2

    the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range

    The main sections of this object are
    - db const:          const for the database link
    - preserved:         const user names used by the system
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - load:              database access object (DAO) functions
    - load sql:          create the sql statements for loading from the db
    - im- and export:    create an export object and set the vars from an import object
    - info:              functions to make code easier to read
    - save:              manage to insert or update the database
    - similar:           get similar objects or compare
    - add:               insert database wrapper
    - update:            update database wrapper
    - sql write:         create the sql insert, update of delete statements
    - sql write fields:  get a list of all or only the changed database fields
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\cfg\user;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_id_object_non_sandbox.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'db_object.php';
//include_once paths::DB . 'db_check.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::EXPORT . 'export_type_list.php';
//include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_HELPER . 'object_mapper.php';
//include_once paths::MODEL_IMPORT . 'import_file.php';
include_once paths::MODEL_SYSTEM . 'ip_range_list.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
//include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_LOG . 'change_log.php';
//include_once paths::MODEL_LOG . 'change_table_list.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
//include_once paths::MODEL_REF . 'source.php';
//include_once paths::MODEL_REF . 'source_db.php';
//include_once paths::MODEL_WORD . 'triple.php';
//include_once paths::MODEL_WORD . 'triple_list.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_profile.php';
include_once paths::MODEL_USER . 'user_type.php';
//include_once paths::MODEL_VERB . 'verb_list.php';
//include_once paths::MODEL_VIEW . 'view.php';
//include_once paths::MODEL_VIEW . 'view_db.php';
//include_once paths::MODEL_VIEW . 'view_sys_list.php';
//include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED_ENUM . 'user_types.php';
include_once paths::SHARED_ENUM . 'user_statuum.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_id_object_non_sandbox;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\object_mapper;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action;
use Zukunft\ZukunftCom\main\php\cfg\log\change_log;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_db;
use Zukunft\ZukunftCom\main\php\cfg\system\ip_range_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_db;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\cfg\view\view_sys_list;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\enum\user_statuum;
use Zukunft\ZukunftCom\main\php\shared\enum\user_types;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use DateTimeInterface;
use DateTime;
use Exception;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class user extends db_id_object_non_sandbox
{

    /*
     * db const
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'for users including system users; only users can add data';

    // forward the const string to enable usage of $this::CONST_NAME
    const string FLD_ID = user_db::FLD_ID;
    const array FLD_NAMES = user_db::FLD_NAMES;
    const array FLD_LST_ALL = user_db::FLD_LST_ALL;

    // the possible unique key fields of a user
    const string KEY_ID = user_db::FLD_ID;
    const string KEY_IP = user_db::FLD_IP_ADDR;
    const string KEY_NAME = user_db::FLD_NAME;
    const string KEY_EMAIL = user_db::FLD_EMAIL;


    /*
     * object vars
     */

    // database fields
    // more unique keys
    public ?string $name = null;           // simply the username, which is only empty if the user object is not yet saved to the database
    public ?string $ip_addr = null;        // simply the ip address used if no username is given
    public ?string $email = null;          // the email used for the signup process

    // log in and sighup
    // TODO Prio 0 check that all user vars are save and are included in the api message
    public ?string $password = null;       // only used for the login and password change process
    public ?string $activation_key = null; // var used for the registration and logon process
    public ?DateTime $activation_timeout = null;
    public ?DateTime $db_now = null;       // timestamp of the database server to have a reference with time zone e.g. for the activation timeout
    public ?DateTime $last_login = null;
    public ?DateTime $last_logoff = null;

    // for the permission settings
    public ?int $profile_id = null;        // id of the preloaded user profiles to define the base permissions of the user that should be used now
    public ?string $code_id = null;        // the main id to detect system users
    public ?int $type_id = null;           // the confirmation level / status of the user e.g. email checked or passport checked which might lead to a different profile id
    public ?int $right_level = null;       // can be used to reduce the right level of the profile
    public ?int $status_id = null;         // id of the actual status of the user profiles to reduce temporary the user writes of the profile
    public ?bool $excluded = null;         // to deactivate users that have already a log entry and cannot be deleted any more

    // additional info
    public ?DateTime $created = null;
    public ?string $description = null;    // used for system users to describe the target; can be used by users for a short introduction
    public ?string $first_name = null;
    public ?string $last_name = null;

    // volatile user parameters to improve guessing of next step
    public ?term $trm = null;              // the last term used by the user
    public ?view $msk = null;              // the last view used by the user
    public ?source $src = null;            // the last source used by the user

    // TODO move to user config e.g. by using the key word "pod-user-config"
    public ?string $dec_point = null;      // the decimal point char for this user
    public ?string $thousand_sep = null;   // the thousand separator user for this user
    public ?int $percent_decimals = null;  // the number of decimals for this user


    // TODO add set and get
    // e.g. only admin are allowed to see other user parameters
    public ?user $viewer = null;           // the user who wants to access this user


    /*
     * construct and map
     */

    function __construct(string $name = '', string $email = '')
    {
        parent::__construct();
        $this->reset();

        if ($name != '') {
            $this->name = $name;
        }
        if ($email != '') {
            $this->email = $email;
        }

        // set the default user profile
        $this->profile_id = user_profiles::NORMAL_ID;

    }

    /**
     * reset the vars of this user
     * used to search for a user with the same name, email or ip address
     * @param bool $keep_user not used here only for compatibility with the parent class
     */
    function reset(bool $keep_user = false): void
    {
        $this->id = 0;

        // more unique keys
        $this->name = null;
        $this->ip_addr = null;
        $this->email = null;

        // log in and sighup
        $this->password = null;
        $this->activation_key = null;
        $this->activation_timeout = null;
        $this->db_now = null;
        $this->last_login = null;
        $this->last_logoff = null;

        // for the permission settings
        $this->profile_id = null;
        $this->code_id = null;
        $this->type_id = null;
        $this->right_level = null;
        $this->status_id = null;
        $this->excluded = null;

        // additional info
        $this->created = null;
        $this->description = null;
        $this->first_name = null;
        $this->last_name = null;

        // volatile parameter to make the workflow more smooth
        $this->trm = null;
        $this->msk = null;
        $this->src = null;

        // TODO Prio 2 move it to user config base on a value list
        $this->dec_point = null;
        $this->thousand_sep = shared_config::DEFAULT_THOUSAND_SEP;
        $this->percent_decimals = shared_config::DEFAULT_PERCENT_DECIMALS;

        $this->viewer = null;

    }

    /**
     * create a clone and empty all fields
     *
     * @param bool $keep_user set to true to keep the original user
     * @return $this a clone with the name changed
     */
    function clone_reset(bool $keep_user = false): user
    {
        $obj_cpy = clone $this;
        $obj_cpy->reset();
        return $obj_cpy;
    }

    /**
     * create a clone and update the name (mainly used for unit testing)
     * but keep the id a unique db id
     *
     * @param string $name the target name
     * @return $this a clone with the name changed
     */
    function cloned(string $name): user
    {
        $obj_cpy = $this->clone_reset();
        $obj_cpy->id = $this->id;
        $obj_cpy->name = $name;
        return $obj_cpy;
    }

    /**
     * map the database fields to the user db row to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        global $debug;

        $lib = new library();
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $this->name = $db_row[user_db::FLD_NAME];
            $this->ip_addr = $db_row[user_db::FLD_IP_ADDR];
            $this->email = $db_row[user_db::FLD_EMAIL];

            if (array_key_exists(user_db::FLD_PASSWORD, $db_row)) {
                $this->password = $db_row[user_db::FLD_PASSWORD];
            }
            if (array_key_exists(user_db::FLD_ACTIVATION_KEY, $db_row)) {
                $this->activation_key = $db_row[user_db::FLD_ACTIVATION_KEY];
            }
            if (array_key_exists(user_db::FLD_ACTIVATION_TIMEOUT, $db_row)) {
                $this->activation_timeout = $lib->get_datetime($db_row[user_db::FLD_ACTIVATION_TIMEOUT], $this->dsp_id());
            }
            if (array_key_exists(user_db::FLD_DB_NOW, $db_row)) {
                $this->db_now = $lib->get_datetime($db_row[user_db::FLD_DB_NOW], $this->dsp_id());
            }
            if (array_key_exists(user_db::FLD_LAST_LOGIN, $db_row)) {
                $this->last_login = $lib->get_datetime($db_row[user_db::FLD_LAST_LOGIN], $this->dsp_id());
            }
            if (array_key_exists(user_db::FLD_LAST_LOGOUT, $db_row)) {
                $this->last_logoff = $lib->get_datetime($db_row[user_db::FLD_LAST_LOGOUT], $this->dsp_id());
            }

            if (array_key_exists(user_db::FLD_PROFILE, $db_row)) {
                $this->profile_id = $db_row[user_db::FLD_PROFILE];
            }
            if (array_key_exists(user_db::FLD_CODE_ID, $db_row)) {
                $this->code_id = $db_row[sql_db::FLD_CODE_ID];
            }
            if (array_key_exists(user_db::FLD_TYPE_ID, $db_row)) {
                $this->type_id = $db_row[user_db::FLD_TYPE_ID];
            }
            if (array_key_exists(user_db::FLD_LEVEL, $db_row)) {
                $this->right_level = $db_row[user_db::FLD_LEVEL];
            }
            if (array_key_exists(user_db::FLD_STATUS, $db_row)) {
                $this->status_id = $db_row[user_db::FLD_STATUS];
            }
            if (array_key_exists(sql_db::FLD_EXCLUDED, $db_row)) {
                $this->excluded = $db_row[sql_db::FLD_EXCLUDED];
            }

            if (array_key_exists(user_db::FLD_CREATED, $db_row)) {
                $this->created = $lib->get_datetime($db_row[user_db::FLD_CREATED], $this->dsp_id());
            }
            if (array_key_exists(sql_db::FLD_DESCRIPTION, $db_row)) {
                $this->description = $db_row[sql_db::FLD_DESCRIPTION];
            }
            if (array_key_exists(user_db::FLD_FIRST_NAME, $db_row)) {
                $this->first_name = $db_row[user_db::FLD_FIRST_NAME];
            }
            if (array_key_exists(user_db::FLD_LAST_NAME, $db_row)) {
                $this->last_name = $db_row[user_db::FLD_LAST_NAME];
            }

            if (array_key_exists(user_db::FLD_TERM, $db_row)) {
                if ($db_row[user_db::FLD_TERM] != null) {
                    // TODO Prio 1 get term from cache if it is in the cache already
                    $trm = new term($this);
                    $trm->set_id($db_row[user_db::FLD_TERM]);
                    $this->trm = $trm;
                }
            }
            if (array_key_exists(user_db::FLD_VIEW, $db_row)) {
                if ($db_row[user_db::FLD_VIEW] != null) {
                    $msk = new view($this);
                    $msk->id = $db_row[user_db::FLD_VIEW];
                    $this->msk = $msk;
                }
            }
            if (array_key_exists(user_db::FLD_SOURCE, $db_row)) {
                if ($db_row[user_db::FLD_SOURCE] != null) {
                    $src = new source($this);
                    $src->id = $db_row[user_db::FLD_SOURCE];
                    $this->src = $src;
                }
            }

            $this->dec_point = shared_config::DEFAULT_DEC_POINT;
            $this->thousand_sep = shared_config::DEFAULT_THOUSAND_SEP;
            $this->percent_decimals = shared_config::DEFAULT_PERCENT_DECIMALS;

            $result = true;
            log_debug($this->name, $debug - 25);
        }
        return $result;
    }

    /**
     * fill these db id object vars with the values from the given api json array
     * @param array $api_json the api array e.g. from the frontend with the word values that should be mapped
     * @param user_message $usr_msg if the mapping is incomplete, the human-readable message what happened and how to solve it
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        global $sys;

        parent::api_mapper($api_json, $usr_msg);

        // map the fields that are common for import and api json messages
        $this->json_mapper($api_json, $usr_msg);

        // map the api specific fields e.g. the json fields using the database id
        // TODO Prio 1 check that the api, url and import mapper just map the fields
        //             and the permission check of critical fields is done before the database save
        if (key_exists(json_fields::PROFILE_ID, $api_json)) {
            $this->profile_id = $api_json[json_fields::PROFILE_ID];
        } else {
            $this->profile_id = user_profiles::NORMAL_ID;
        }
        if (key_exists(json_fields::TYPE, $api_json)) {
            $this->type_id = $api_json[json_fields::TYPE];
        } else {
            $this->type_id = $sys->typ_lst->usr_typ->id(user_types::GUEST);
        }
        if (key_exists(json_fields::STATUS_ID, $api_json)) {
            $this->status_id = $api_json[json_fields::STATUS_ID];
        } else {
            $this->status_id = $sys->typ_lst->usr_sta->id(user_statuum::ACTIVE);
        }

        if (key_exists(json_fields::TERM_ID, $api_json)) {
            // TODO Prio 1 get term from cache if it is in the cache already
            $trm = new term($this);
            $trm->set_id($api_json[json_fields::TERM_ID]);
            $this->trm = $trm;
        }
        if (key_exists(json_fields::VIEW_ID, $api_json)) {
            // TODO Prio 1 get view from cache if it is in the cache already
            $msk = new view($this);
            $msk->id = $api_json[json_fields::VIEW_ID];
            $this->msk = $msk;
        }
        if (key_exists(json_fields::SOURCE_ID, $api_json)) {
            // TODO Prio 1 get source from cache if it is in the cache already
            $src = new source($this);
            $src->id = $api_json[json_fields::SOURCE_ID];
            $this->src = $src;
        }

        return $usr_msg->is_ok();
    }


    /**
     * set the vars of this user object based on the given json without writing to the database
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions including the user how has initiated the import mainly used to prevent any user to gain additional rights
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

        $map = new object_mapper();

        // map the fields that are common for import and api json messages
        $this->json_mapper($in_ex_json, $msg);

        // the code id should never be changed via api
        if (key_exists(json_fields::CODE_ID, $in_ex_json)) {
            // only system and admin users are allowed to change the code od
            if ($msg->usr->is_admin() or $msg->usr->is_system()) {
                $this->set_code_id($in_ex_json[json_fields::CODE_ID], $msg->usr);
            }
        }

        // map the api specific fields e.g. the json fields using the database id
        if (key_exists(json_fields::PROFILE, $in_ex_json)) {
            $this->set_profile($in_ex_json[json_fields::PROFILE], $msg);
        } else {
            $this->profile_id = user_profiles::NORMAL_ID;
        }
        if (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            $this->set_type($in_ex_json[json_fields::TYPE_NAME], $msg);
        } else {
            $this->type_id = $sys->typ_lst->usr_typ->id(user_types::GUEST);
        }
        if (key_exists(json_fields::STATUS, $in_ex_json)) {
            $this->status_id = $sys->typ_lst->usr_sta->id($in_ex_json[json_fields::STATUS]);
        } else {
            $this->status_id = $sys->typ_lst->usr_sta->id(user_statuum::ACTIVE);
        }

        $this->trm = $map->get_term($in_ex_json, $msg, $dto);
        $this->msk = $map->get_view($in_ex_json, $msg, $dto);
        $this->src = $map->get_source($in_ex_json, $msg, $dto);

        // TODO Prio 0 create and use an object mapper library (one for backend and one for the fromtend)
        // TODO Prio 2 create the view if the json is an array with more information than just the view name

        return $msg->is_ok();
    }

    /**
     * the common mapping part for the api and import mapper
     * TODO add api type to force to include the related objects in the json message
     *
     * @param array $json
     * @param user_message $msg with the requesting user to collect the message for the user
     * @return void return value is not needed because the messages are written to the given user_message object
     */
    private function json_mapper(
        array        $json,
        user_message $msg
    ): void
    {
        $lib = new library();

        if (key_exists(json_fields::NAME, $json)) {
            $this->name = $json[json_fields::NAME];
        }
        if (key_exists(json_fields::IP_ADDR, $json)) {
            $this->ip_addr = $json[json_fields::IP_ADDR];
        }
        if (key_exists(json_fields::EMAIL, $json)) {
            $this->email = $json[json_fields::EMAIL];
        }

        // the password is not to be expected to be imported or exported
        if (key_exists(json_fields::ACTIVATION_KEY, $json)) {
            $this->activation_key = $json[json_fields::ACTIVATION_KEY];
        }
        if (key_exists(json_fields::ACTIVATION_TIMEOUT, $json)) {
            $this->activation_timeout = $lib->get_datetime($json[json_fields::ACTIVATION_TIMEOUT], $this->dsp_id());
        }
        if (key_exists(json_fields::DB_NOW, $json)) {
            $this->db_now = $lib->get_datetime($json[json_fields::DB_NOW], $this->dsp_id());
        }
        if (key_exists(json_fields::LAST_LOGIN, $json)) {
            $this->last_login = $lib->get_datetime($json[json_fields::LAST_LOGIN], $this->dsp_id());
        }
        if (key_exists(json_fields::LAST_LOGOFF, $json)) {
            $this->last_logoff = $lib->get_datetime($json[json_fields::LAST_LOGOFF], $this->dsp_id());
        }

        // TODO Prio 1 restrict changes to admin users
        if (key_exists(json_fields::CODE_ID, $json)) {
            $this->code_id = $json[json_fields::CODE_ID];
        }
        if (key_exists(json_fields::RIGHT_LEVEL, $json)) {
            $this->right_level = $json[json_fields::RIGHT_LEVEL];
        }
        if (key_exists(json_fields::EXCLUDED, $json)) {
            $this->excluded = $json[json_fields::EXCLUDED];
        }

        if (key_exists(json_fields::CREATED, $json)) {
            $this->created = $lib->get_datetime($json[json_fields::CREATED], $this->dsp_id());
        }
        if (key_exists(json_fields::DESCRIPTION, $json)) {
            $this->description = $json[json_fields::DESCRIPTION];
        }
        if (key_exists(json_fields::FIRST_NAME, $json)) {
            $this->first_name = $json[json_fields::FIRST_NAME];
        }
        if (key_exists(json_fields::LAST_NAME, $json)) {
            $this->last_name = $json[json_fields::LAST_NAME];
        }

        // TODO Prio 2 report unexpected json field names

    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * TODO Prio 1 add the missing fields like ip_addr
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = $this->api_json_array_core($typ_lst, $usr);
        $vars[json_fields::IP_ADDR] = $this->ip_addr;
        $vars[json_fields::EMAIL] = $this->email;

        $vars[json_fields::ACTIVATION_KEY] = $this->activation_key;
        // TODO Prio 0 make sure that all DateTime to json use the DateTimeInterface::ATOM and the '?' for null
        $vars[json_fields::ACTIVATION_TIMEOUT] = $this->activation_timeout?->format(DateTimeInterface::ATOM);
        $vars[json_fields::DB_NOW] = $this->db_now?->format(DateTimeInterface::ATOM);
        $vars[json_fields::LAST_LOGIN] = $this->last_login?->format(DateTimeInterface::ATOM);
        $vars[json_fields::LAST_LOGOFF] = $this->last_logoff?->format(DateTimeInterface::ATOM);

        if ($this->profile_id > 0) {
            $vars[json_fields::PROFILE_ID] = $this->profile_id;
        }
        $vars[json_fields::CODE_ID] = $this->code_id;
        if ($this->type_id > 0) {
            $vars[json_fields::TYPE] = $this->type_id;
        }
        $vars[json_fields::RIGHT_LEVEL] = $this->right_level;
        if ($this->status_id > 0) {
            $vars[json_fields::STATUS] = $this->status_id;
        }
        $vars[json_fields::EXCLUDED] = $this->excluded;

        $vars[json_fields::CREATED] = $this->created?->format(DateTimeInterface::ATOM);
        $vars[json_fields::DESCRIPTION] = $this->description;
        $vars[json_fields::FIRST_NAME] = $this->first_name;
        $vars[json_fields::LAST_NAME] = $this->last_name;

        // the last objects used by the user to improve guessing of the next user actions
        // TODO Prio 2 add a api type to include only the objects that are expected to be missing in the frontend
        if ($this->trm != null) {
            if ($typ_lst->include_terms()) {
                $vars[json_fields::TERM] = $this->trm->api_json_array();
            } else {
                $vars[json_fields::TERM_ID] = $this->trm->id();
            }
        }
        // TODO Prio 3 make sure that the var name for the view is always msk
        if ($this->msk != null) {
            if ($typ_lst->include_views()) {
                $vars[json_fields::VIEW] = $this->msk->api_json_array();
            } else {
                $vars[json_fields::VIEW_ID] = $this->msk->id();
            }
        }
        if ($this->src != null) {
            if ($typ_lst->include_sources()) {
                $vars[json_fields::SOURCE] = $this->src->api_json_array($typ_lst);
            } else {
                $vars[json_fields::SOURCE_ID] = $this->src->id();
            }
        }

        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

    /**
     * create an array for the api json creation with only the core user fields
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array_core(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = parent::api_json_array($typ_lst, $usr);
        if ($this->name != null) {
            $vars[json_fields::NAME] = $this->name;
        } else {
            $vars[json_fields::NAME] = '';
        }

        return $vars;
    }


    /*
     * settings
     */

    /**
     * @return change_log the object that is used to log the user changes
     */
    function log_object(): change_log
    {
        return new change($this);
    }


    /*
     * set and get
     */

    /**
     * set the most often used user vars with one set statement
     * @param int $id mainly for test creation the database id of the user
     */
    function set(int $id = 0, string $name = '', string $email = ''): void
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * set the unique id to select a single user by the program
     *r
     * @param string|null $code_id the unique key to select a word used by the system e.g. for the system or configuration
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
     * @return string the unique username for the user on this pod
     */
    function name(): string|null
    {
        if ($this->name != null) {
            return $this->name;
        } elseif ($this->email != null) {
            return $this->email;
        } elseif ($this->ip_addr != null) {
            return $this->ip_addr;
        } else {
            return '';
        }
    }

    /**
     * @return string|null the unique username for the user on this pod or null if not set
     */
    function name_or_null(): ?string
    {
        if ($this->name == null) {
            return $this->email;
        } else {
            return $this->name;
        }
    }

    /**
     * @return string the username and the privileges of the user
     */
    function name_and_profile(): string
    {
        $result = $this->name();
        $result .= ' ' . $this->profile_code_id();
        return $result;
    }

    function profile_name(): ?string
    {
        global $sys;
        if ($this->profile_id > 0) {
            return $sys->typ_lst->usr_pro->name($this->profile_id);
        } else {
            return null;
        }
    }

    function type_name(): ?string
    {
        global $sys;
        if ($this->type_id > 0) {
            return $sys->typ_lst->usr_typ->name($this->type_id);
        } else {
            return null;
        }
    }

    function status_name(): ?string
    {
        global $sys;
        if ($this->status_id > 0) {
            return $sys->typ_lst->usr_sta->name($this->status_id);
        } else {
            return null;
        }
    }

    /**
     * @return int|null the id of the last term used by the user
     */
    function term_id(): ?int
    {
        return $this->trm?->id();
    }

    /**
     * @return string|null the name of the last term used by the user
     */
    function term_name(): ?string
    {
        return $this->trm?->name();
    }

    /**
     * @return int|null the id of the last source used by the user
     */
    function source_id(): ?int
    {
        return $this->src?->id();
    }

    /**
     * get the most relevant unique value of this user
     * e.g. the ip address if the username of an email is missing
     * must be corresponding with function key_field()
     *
     * @return string with the most relevant unique key
     */
    function unique_value(): string
    {
        if ($this->name_or_null() != null and $this->name() != '') {
            $key = $this->name();
        } elseif ($this->email != null and $this->email != '') {
            $key = $this->email;
        } elseif ($this->ip_addr != null and $this->ip_addr != '') {
            $key = $this->ip_addr;
        } else {
            $key = strval($this->id());
        }
        return $key;
    }

    /**
     * get the db field name of the most relevant unique value of this user
     * e.g. the ip_address if the username an email are missing
     * must be corresponding with function unique_value()
     *
     * @return string with the db field name of the most relevant unique key
     */
    function key_field(): string
    {
        if ($this->name_or_null() != null and $this->name() != '') {
            $key_fld = self::KEY_NAME;
        } elseif ($this->email != null and $this->email != '') {
            $key_fld = self::KEY_EMAIL;
        } elseif ($this->ip_addr != null and $this->ip_addr != '') {
            $key_fld = self::KEY_IP;
        } else {
            $key_fld = self::KEY_ID;
        }
        return $key_fld;
    }

    /**
     * set the excluded field for this user in the database
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
     * @return bool true if an admin user wanted to exclude the user from this pod without deleting the history
     */
    function is_excluded(): bool
    {
        if ($this->excluded == null) {
            // by default an object is not excluded
            return false;
        } else {
            return $this->excluded;
        }
    }


    /*
     * loading / database access object (DAO) functions
     */

    /**
     * load a user by id from the database
     *
     * TODO make sure that it is always checked if the requesting user has the sufficient permissions
     *  param user|null $request_usr the user who has requested the loading of the user data to prevent right gains
     *
     * @param int $id of the user that should be loaded
     * @return int an id > 0 if the loading has been successful
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id);
        return $this->load($qp);
    }

    /**
     * load one user by name
     * @param string $name the username of the user
     * @return int the id of the found user and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        global $db_con;

        log_debug($name);
        $qp = $this->load_sql_by_name($db_con->sql_creator(), $name);
        return $this->load($qp);
    }

    /**
     * load one user by the code id
     * @param string $code_id the code_id of the user
     * @return int the id of the found user and zero if nothing is found
     */
    function load_by_code_id(string $code_id): int
    {
        global $db_con;

        log_debug($code_id);
        $qp = $this->load_sql_by_code_id($db_con->sql_creator(), $code_id);
        return $this->load($qp);
    }

    /**
     * load one user by name
     * @param string $email the email of the user
     * @return bool true if a user has been found
     */
    function load_by_email(string $email): bool
    {
        global $db_con;

        log_debug();
        $qp = $this->load_sql_by_email($db_con->sql_creator(), $email);
        return $this->load($qp);
    }

    /**
     * load one user by name or email
     * @param ?string $name the username of the user
     * @param ?string $email the email of the user
     * @return bool true if a user has been found
     */
    function load_by_name_or_email(?string $name, ?string $email): bool
    {
        global $db_con;

        log_debug($this->dsp_id());
        $qp = $this->load_sql_by_name_or_email($db_con->sql_creator(), $name, $email);
        return $this->load($qp);
    }

    /**
     * load the first user with the given ip address
     * @param string $ip the ip address with which the user has logged in
     * @return bool true if a user has been found
     */
    function load_by_ip(string $ip): bool
    {
        global $db_con;

        log_debug($ip);
        $qp = $this->load_sql_by_ip($db_con->sql_creator(), $ip);
        return $this->load($qp);
    }

    /**
     * load the first user with the given ip address
     * @param int $profile_id the id of the profile of which the first matching user should be loaded
     * @return bool true if a user has been found
     */
    function load_by_profile(int $profile_id): bool
    {
        global $db_con;

        log_debug($profile_id);
        $qp = $this->load_sql_by_profile($db_con->sql_creator(), $profile_id);
        return $this->load($qp);
    }

    function load_by_profile_code(string $profile_code_id, bool $log_err = true): bool
    {
        global $sys;
        if ($sys->typ_lst->usr_pro != null) {
            return $this->load_by_profile($sys->typ_lst->usr_pro->id($profile_code_id, $log_err));
        } else {
            return false;
        }
    }

    /**
     * load a user from the database view
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected
    function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row);
        return $this->id;
    }


    /*
     * load sql
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of a user from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);

        $sc->set_class($class);
        $sc->set_name($qp->name);

        if ($this->viewer == null) {
            if ($this->id == null) {
                $sc->set_usr(0);
            } else {
                $sc->set_usr($this->id);
            }
        } else {
            $sc->set_usr($this->viewer->id);
        }
        $sc->set_fields(user_db::FLD_NAMES);
        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_ID, $class);
        $sc->add_where(self::FLD_ID, $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user by name from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $name the name of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_name(sql_creator $sc, string $name, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_NAME, $class);
        $sc->add_where(user_db::FLD_NAME, $name);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user by the code id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $code_id the code id of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_code_id(sql_creator $sc, string $code_id, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, user_db::FLD_CODE_ID, $class);
        $sc->add_where(user_db::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user by email from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $email the email of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_email(sql_creator $sc, string $email, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, 'email', $class);
        $sc->add_where(user_db::FLD_EMAIL, $email);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user by name or email from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param ?string $name the name of the user
     * @param ?string $email the email of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_name_or_email(sql_creator $sc, ?string $name, ?string $email, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, 'name_or_email', $class);
        if ($name != null) {
            $sc->add_where(user_db::FLD_NAME, $name, sql_par_type::TEXT_OR);
        }
        if ($email != null) {
            $sc->add_where(user_db::FLD_EMAIL, $email, sql_par_type::TEXT_OR);
        }
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user with the ip from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $ip_addr the ip address with which the user has logged in
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_ip(sql_creator $sc, string $ip_addr, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, 'ip', $class);
        $sc->add_where(user_db::FLD_IP_ADDR, $ip_addr);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user with the profile from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $profile_id the id of the profile of which the first matching user should be loaded
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_profile(sql_creator $sc, int $profile_id, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, 'profile', $class);
        $sc->add_where(user_db::FLD_PROFILE, $profile_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load the user-specific data not supposed to be changed very rarely user
     * so if changed all data is reloaded once
     */
    function load_usr_data(): void
    {
        global $sys;
        global $db_con;
        global $sys;
        global $sys_msk_cac;

        $sys->times->switch(system_time_type::LOAD_USER_DATA);
        $sys->typ_lst->vrb = new verb_list($this);
        $sys->typ_lst->vrb->load($db_con);

        $sys_msk_cac = new view_sys_list($this);
        $sys_msk_cac->load($db_con);

        $sys->times->switch(system_time_type::DEFAULT);
    }

    function has_any_user_this_profile(string $profile_code_id): bool
    {
        return $this->load_by_profile_code($profile_code_id, false);
    }

    private function ip_in_range($ip_addr, $min, $max): bool
    {
        $result = false;
        if (ip2long(trim($min)) <= ip2long(trim($ip_addr)) && ip2long(trim($ip_addr)) <= ip2long(trim($max))) {
            log_debug(' ip ' . $ip_addr . ' (' . ip2long(trim($ip_addr)) . ') is in range between ' . $min . ' (' . ip2long(trim($min)) . ') and  ' . $max . ' (' . ip2long(trim($max)) . ')');
            $result = true;
        }
        return $result;
    }

    /**
     *
     * exposed as public mainly for testing
     * @return string the message, why the if is not permitted
     */
    function ip_check(string $ip_addr): string
    {
        global $debug;
        log_debug(' (' . $ip_addr . ')', $debug - 12);

        $ip_lst = new ip_range_list();
        $ip_lst->load();
        $test_result = $ip_lst->includes($ip_addr);
        if (!$test_result->is_ok()) {
            $this->id = 0; // switch off the permission
            log_info('ip_check rejects due to ' . $test_result->text());
        }
        return $test_result->all_message_text();
    }

    /**
     * set the ip of the
     * @return string the ip address of the active user
     */
    private function get_ip(): void
    {
        if (array_key_exists(rest_ctrl::REMOTE_ADDR, $_SERVER)) {
            $this->ip_addr = $_SERVER[rest_ctrl::REMOTE_ADDR];
        }
        // TODO Prio 1 switch this off!!
        if ($this->ip_addr == null) {
            $this->ip_addr = users::SYSTEM_ADMIN_IP;
        }
    }

    /**
     * TODO return a translatable msg_id instead of a string
     * @returns string the active session user object
     */
    function get(): string
    {
        global $debug;

        $result = ''; // for the result message e.g. if the user is blocked
        $usr_msg = new user_message();

        // test first if the IP is blocked
        if ($this->ip_addr == '') {
            $this->get_ip();
        } else {
            log_debug('by given ip addr ' . $this->ip_addr, $debug - 1);
        }
        // even if the user has an open session, but the ip is blocked, drop the user
        $result .= $this->ip_check($this->ip_addr);

        if ($result == '') {
            // if the user has logged in use the logged in account
            if (isset($_SESSION[url_var::SESSION_LOGGED])) {
                log_debug('use session');
                if ($_SESSION[url_var::SESSION_LOGGED]) {
                    $this->load_by_id($_SESSION[url_var::SESSION_USER_ID]);
                    log_debug('use session id ' . $this->id);
                }
            } else {
                log_info('ip check result is ' . $result);
            }
        }
        if ($this->id <= 0 and $result == '') {
            // else use the IP address (for testing don't overwrite any testing ip)
            log_debug('load by ip addr ' . $this->ip_addr);
            $this->load_by_ip($this->ip_addr);
            if ($this->id <= 0) {
                // use the ip address as the username and add the user
                $this->name = $this->ip_addr;

                // allow to fill the database only if a local user has logged in
                if ($this->name == users::SYSTEM_ADMIN_IP) {

                    // create the main system user upfront direct from the code
                    // but only if needed and allowed which is only the case directly after the database structure creation
                    // TODO switch this fallback off because it should anyway never be called
                    $this->create_system_user($usr_msg);

                } else {
                    $this->save_user($usr_msg);
                }
                $result = $usr_msg->get_last_message();
            }
        }
        log_debug(' done with "' . $this->name . '" (' . $this->id . ')');
        return $result;
    }

    /**
     * create the core system users
     * BUT only if the user table is empty
     * fixed code to create the initial system user
     * TODO move to system_user
     * @param user_message $usr_msg OK if the system users have been created
     * @return bool true if the system users have been created
     */
    function create_system_user(user_message $usr_msg): bool
    {
        global $db_con;

        if ($db_con->count(user::class) <= 0) {
            // reload user profiles if needed
            global $sys;
            if ($sys->typ_lst->usr_pro == null) {
                log_warning('unexpected reload of user profiles');
                $sys->typ_lst->usr_pro = new user_profile_list();
                if (!$sys->typ_lst->usr_pro->load($db_con)) {
                    $sys->typ_lst->usr_pro->load_dummy();
                };
                $sys->typ_lst->usr_typ = new user_type_list();
                if (!$sys->typ_lst->usr_typ->load($db_con)) {
                    $sys->typ_lst->usr_typ->load_dummy();
                };
                $sys->typ_lst->usr_sta = new user_status_list();
                if (!$sys->typ_lst->usr_sta->load($db_con)) {
                    $sys->typ_lst->usr_sta->load_dummy();
                };
            }

            // add the system user to use it for the import
            $sys_usr = new user();
            $sys_usr->name = users::SYSTEM_NAME;
            $sys_usr->email = users::SYSTEM_EMAIL;
            $sys_usr->description = users::SYSTEM_COM;
            $sys_usr->set_profile_id(user_profiles::SYSTEM_ID);
            $sys_usr->code_id = users::SYSTEM_CODE_ID;
            $sys_usr->excluded = false;
            $usr_msg->merge($sys_usr->save_direct());
            if (!$usr_msg->is_ok()) {
                log_fatal('system user cannot be created', 'sql_db->create_system_user');
            } elseif ($sys_usr->id != users::SYSTEM_ID) {
                log_fatal('system user has not the expected database id of ' . users::SYSTEM_ID, 'sql_db->create_system_user');
            } else {
                // use a temp user message with the system as requesting user to set the admin profile
                $msg_sys = new user_message();
                $msg_sys->usr = $sys_usr;
                // add the local admin user to use it for the import
                $local_usr = new user();
                $local_usr->name = users::SYSTEM_ADMIN_NAME;
                $local_usr->ip_addr = users::SYSTEM_ADMIN_IP;
                $local_usr->email = users::SYSTEM_ADMIN_EMAIL;
                $local_usr->description = users::SYSTEM_ADMIN_COM;
                $local_usr->set_profile(user_profiles::ADMIN, $msg_sys);
                $local_usr->code_id = users::SYSTEM_ADMIN_CODE_ID;
                $local_usr->excluded = false;
                $usr_msg->merge($local_usr->save_direct());
                if (!$usr_msg->is_ok()) {
                    log_fatal('local admin user cannot be created', 'sql_db->create_system_user');
                } elseif ($local_usr->id != users::SYSTEM_ADMIN_ID) {
                    log_fatal('local admin user has not the expected database id of ' . users::SYSTEM_ADMIN_ID, 'sql_db->create_system_user');
                } else {
                    $usr_msg->add_info_id(msg_id::DONE);
                }
            }
        }
        return $usr_msg->is_ok();
    }


    /*
     * compare
     */

    /**
     * detects if this object has been changed compared to the given object,
     * excluding changes on internal fields like last_update
     *
     * @param user $db_usr the user database or standard record for compare
     * @return bool true if any of the fields does not match
     */
    function no_diff(
        user          $db_usr,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): bool
    {
        // for the check it is not relevant if only the user differs
        $chk_obj = clone $this;
        // if this object does not yet have a db key ignore this
        if ($chk_obj->id() == 0) {
            $chk_obj->id = $db_usr->id();
        }
        $fvt_lst = $chk_obj->db_fields_changed($db_usr, $usr_msg, $sc_par_lst);
        return $fvt_lst->is_empty_except_internal_fields();
    }

    /**
     * detects if this object has been changed compared to the given object,
     * excluding changes on internal fields like last_update
     *
     * @param user $db_usr the user database or standard record for compare
     * @return bool true if any of the fields does not match
     */
    function no_non_id_diff(
        user          $db_usr,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): bool
    {
        $fvt_lst = $this->db_fields_changed($db_usr, $usr_msg, $sc_par_lst);
        return $fvt_lst->is_empty_except_id_and_internal_fields();
    }


    /*
     * owner and access
     */

    /**
     * true if the login user is in general allowed to insert anything in this user
     * TODO Prio 2 review and add mor profiles
     *
     * @param user $usr_req the user who has request the user adding
     * @return bool true if the logged-in user is the user itself or an admin
     */
    function can_add(user $usr_req): bool
    {
        $can_add = false;

        // if the user who wants to change it, is the owner, he can do it
        // or if the owner is not set, he can do it (and the owner should be set, because every object should have an owner)
        if ($usr_req->is_admin() or $usr_req->is_system()) {
            $can_add = true;
            log_info('user ' . $this->dsp_id() . ' is change by admin user ' . $usr_req->dsp_id());
        } elseif ($usr_req->is_normal()) {
            $can_add = true;
            log_info('user ' . $this->dsp_id() . ' is added by user ' . $usr_req->dsp_id());
        } elseif ($this->is_normal()) {
            $can_add = true;
            log_info('normal user ' . $this->dsp_id() . ' is added by user ' . $usr_req->dsp_id());
        } else {
            log_warning('privileged user ' . $usr_req->dsp_id() . ' has requested to added by non admin user ' . $this->dsp_id() . ' without permission');
        }

        return $can_add;
    }

    /**
     * true if the login user is in general allowed to change anything in this user
     *
     * @param user_message $usr_msg the user who has requested the update and the object to collect the potential reject messages
     * @param user|db_object_seq_id $db_rec the user as it is in the database before the change
     * @return bool true if the logged-in user is the user itself or an admin
     */
    function can_be_changed_by(user_message $usr_msg, user|db_object_seq_id $db_rec): bool
    {
        $can_change = false;

        // if the user who wants to change it, is the owner, he can do it
        // or if the owner is not set, he can do it (and the owner should be set, because every object should have an owner)
        if ($this->id == $usr_msg->usr->id) {
            $can_change = true;
        } elseif ($usr_msg->usr->is_admin() or $usr_msg->usr->is_system()) {
            $can_change = true;
            log_info('user ' . $this->dsp_id() . ' is change by admin user ' . $usr_msg->usr->dsp_id());
        } else {
            log_warning('user ' . $usr_msg->usr->dsp_id() . ' has requested to change by user ' . $this->dsp_id() . ' without permission');
        }

        return $can_change;
    }

    /**
     * check if the requesting user is permitted to do any changes on the user
     * this function is part of the profile list because the profile list is already a cached object
     *
     * @param user $usr the user that is expected to be changed
     * @return bool true if the user can be changes
     */
    function can_change(user $usr): bool
    {
        $result = false;
        // the user can change in general its own parameters
        if ($usr->id != 0 and $usr->id == $this->id) {
            $result = true;
        }
        // the system users can always change other users
        if ($this->is_system()) {
            $result = true;
        }
        // the admin users can change other users ...
        if ($this->is_admin()) {
            // ... but not system users
            if (!$usr->is_system()) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * check if the user is allowed to permit other user to have the given profile
     * this function is part of the profile list because the profile list is already a cached object
     * TODO add the missing profiles and use the profile level as a second line of defence
     *
     * @param int $profile_id the profile that should be set
     * @return bool
     */
    function can_set_profile(int $profile_id): bool
    {
        $result = false;

        global $sys;

        $profile = $sys->typ_lst->usr_pro->get($profile_id);
        if ($profile != null) {
            // the system users can assign all profiles
            if ($this->is_system()) {
                $result = true;
            }
            // the admin users can change other users ...
            if ($this->is_admin()) {
                // ... but not system users
                // if (!$profile->is_system()) {
                if (!$profile->is_type(user_profiles::SYSTEM)) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * check if the user is allowed to set the type
     *
     * @return bool true if the code id of the object can be changes
     */
    function can_set_type_id(): bool
    {
        $result = false;

        // the system users can always change the message id
        if ($this->is_system()) {
            $result = true;
        }
        // ... and developers
        if ($this->is_developer()) {
            $result = true;
        }
        // ... and administrators
        if ($this->is_admin()) {
            $result = true;
        }
        // ... and even normal unique users are allowed to change the type because a unique log is possible
        if ($this->is_unique()) {
            $result = true;
        }
        return $result;
    }

    /**
     * check if the user is allowed to set the code id of an object
     * the system upgrade user is allowed the change the object code id by importing a json message
     * if the code id of the object is changed the change should be written to the database
     * so the main check is on setting the code id by the import mapper
     * developers can change the code id via json import in the development environment
     * without a deployment for faster testing
     *
     * @return bool true if the code id of the object can be changes
     */
    function can_set_code_id(): bool
    {
        $result = false;

        // the system users can always change the code id
        if ($this->is_system()) {
            $result = true;
        }
        // the development users can change the code id ...
        if ($this->is_developer()) {
            // TODO review
            // ... but only in the deployed development branch
            // if (!$env->is_development()) {
            $result = true;
            // }
        }
        return $result;
    }

    /**
     * check if the user is allowed to set the user interface message code id of an object
     * the system users, developer and administrators are allowed to change
     * because this might be used for online corrections that are not critical
     *
     * @return bool true if the code id of the object can be changes
     */
    function can_set_ui_msg_id(): bool
    {
        $result = false;

        // the system users can always change the message id
        if ($this->is_system()) {
            $result = true;
        }
        // ... and developers
        if ($this->is_developer()) {
            $result = true;
        }
        // ... and administrators
        if ($this->is_admin()) {
            $result = true;
        }
        return $result;
    }


    /*
     * im- and export
     */

    /**
     * import a user from a json data user object
     * TODO Prio 2 add missing fields and user configuration
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @param user|null $usr_req the user how has initiated the import mainly used to prevent any user to gain additional rights
     * @return bool true if everything was fine
     */
    function import_obj(
        array        $in_ex_json,
        user_message $msg,
        ?data_object $dto = null,
        ?object      $test_obj = null,
        ?user        $usr_req = null
    ): bool
    {
        global $sys;
        global $usr;

        if ($usr_req == null) {
            $usr_req = $usr;
        }
        $profile_id = $usr_req->profile_id;

        // reset all parameters of this user object
        $this->reset();

        // map the non critical fields
        $this->import_mapper($in_ex_json, $msg, $dto);

        // set the critical parameter
        // TODO Prio 0 review
        /*
        if ($usr_req->is_admin() or $usr_req->is_system()) {
            $trm = new term($this);
            $trm->import_mapper($in_ex_json[json_fields::TERM], $msg, $dto);
            $this->trm = $trm;
            $msk = new view($this);
            $msk->import_mapper($in_ex_json[json_fields::VIEW], $msg, $dto);
            $this->msk = $msk;
            $src = new source($this);
            $src->import_mapper($in_ex_json[json_fields::SOURCE], $msg, $dto);
            $this->src = $src;
        } else {
            // or remove the setting if the requesting user has no permission
            $this->profile_id = user_profiles::NORMAL_ID;
            $this->code_id = null;
            $this->status_id = $sys->typ_lst->usr_sta->id(user_statuum::ACTIVE);
            $this->trm = null;
            $this->msk = null;
            $this->src = null;
        }
        */


        // the last used objects are not included in the im- and export because this is mot like not needed

        // save the user in the database
        if (!$test_obj) {
            if ($msg->is_ok()) {
                // check the importing profile and make sure that gaining additional privileges is impossible
                // the user profiles must always be in the order that the lower ID has same or less rights
                // TODO use the right level of the profile
                if ($profile_id >= $this->profile_id) {
                    $this->save_user($msg, $usr_req);
                }
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

        $vars = [];

        $vars[json_fields::NAME] = $this->name;
        $vars[json_fields::IP_ADDR] = $this->ip_addr;
        $vars[json_fields::EMAIL] = $this->email;

        $vars[json_fields::ACTIVATION_KEY] = $this->activation_key;
        // TODO Prio 0 make sure that all DateTime to json use the DateTimeInterface::ATOM and the '?' for null
        $vars[json_fields::ACTIVATION_TIMEOUT] = $this->activation_timeout?->format(DateTimeInterface::ATOM);
        $vars[json_fields::DB_NOW] = $this->db_now?->format(DateTimeInterface::ATOM);
        $vars[json_fields::LAST_LOGIN] = $this->last_login?->format(DateTimeInterface::ATOM);
        $vars[json_fields::LAST_LOGOFF] = $this->last_logoff?->format(DateTimeInterface::ATOM);

        $vars[json_fields::PROFILE] = $this->profile_name();
        $vars[json_fields::CODE_ID] = $this->code_id;
        $vars[json_fields::TYPE] = $this->type_name();
        $vars[json_fields::RIGHT_LEVEL] = $this->right_level;
        $vars[json_fields::STATUS] = $this->status_name();
        $vars[json_fields::EXCLUDED] = $this->excluded;

        $vars[json_fields::CREATED] = $this->created?->format(DateTimeInterface::ATOM);
        $vars[json_fields::DESCRIPTION] = $this->description;
        $vars[json_fields::FIRST_NAME] = $this->first_name;
        $vars[json_fields::LAST_NAME] = $this->last_name;

        // the last objects used by the user to improve guessing of the next user actions
        if ($this->trm != null) {
            $vars[json_fields::TERM] = $this->trm->export_json($exp_typ);
            //$vars[json_fields::TERM] = $this->trm->name();
        }
        if ($this->msk != null) {
            $vars[json_fields::VIEW] = $this->msk->export_json($exp_typ);
            //$vars[json_fields::VIEW] = $this->msk->name();
        }
        if ($this->src != null) {
            $vars[json_fields::SOURCE] = $this->src->export_json($exp_typ);
            //$vars[json_fields::SOURCE] = $this->src->name();
        }

        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * check
     */

    /**
     * returns an OK message if this formula can be added to the database
     * e.g. a formula without expression should not be added to the database
     * @param user_message $msg the explanation why the link cannot yet be added to the database
     * @return true if the formula can be added to the database
     */
    function db_ready(user_message $msg): bool
    {
        if (($this->ip_addr == null or $this->ip_addr == '')) {
            $msg->add(msg_id::USER_IP_ADDR_MISSING,
                [msg_id::VAR_USER_NAME => $this->dsp_id()]);
        }
        return $msg->is_ok();
    }


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     *
     * @param user|CombineObject|db_object_seq_id $std_obj the norm object as saved in the database
     * @param user|CombineObject|db_object_seq_id $result empty clone of the target user object
     * @return user|CombineObject|db_object_seq_id the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        user|CombineObject|db_object_seq_id $std_obj,
        user|CombineObject|db_object_seq_id $result
    ): user|CombineObject|db_object_seq_id
    {
        parent::delta($std_obj, $result);
        if ($std_obj->name !== $this->name) {
            $result->name = $this->name;
        }
        if ($std_obj->ip_addr !== $this->ip_addr) {
            $result->ip_addr = $this->ip_addr;
        }
        if ($std_obj->email !== $this->email) {
            $result->email = $this->email;
        }

        if ($std_obj->password !== $this->password) {
            $result->password = $this->password;
        }
        if ($std_obj->activation_key !== $this->activation_key) {
            $result->activation_key = $this->activation_key;
        }
        if ($std_obj->activation_timeout !== $this->activation_timeout) {
            $result->activation_timeout = $this->activation_timeout;
        }
        if ($std_obj->db_now !== $this->db_now) {
            $result->db_now = $this->db_now;
        }
        if ($std_obj->last_login !== $this->last_login) {
            $result->last_login = $this->last_login;
        }
        if ($std_obj->last_logoff !== $this->last_logoff) {
            $result->last_logoff = $this->last_logoff;
        }

        if ($std_obj->profile_id !== $this->profile_id) {
            $result->profile_id = $this->profile_id;
        }
        if ($std_obj->code_id !== $this->code_id) {
            $result->code_id = $this->code_id;
        }
        if ($std_obj->type_id !== $this->type_id) {
            $result->type_id = $this->type_id;
        }
        if ($std_obj->right_level !== $this->right_level) {
            $result->right_level = $this->right_level;
        }
        if ($std_obj->status_id !== $this->status_id) {
            $result->status_id = $this->status_id;
        }
        if ($std_obj->excluded !== $this->excluded) {
            $result->excluded = $this->excluded;
        }

        if ($std_obj->created !== $this->created) {
            $result->created = $this->created;
        }
        if ($std_obj->description !== $this->description) {
            $result->description = $this->description;
        }
        if ($std_obj->first_name !== $this->first_name) {
            $result->first_name = $this->first_name;
        }
        if ($std_obj->last_name !== $this->last_name) {
            $result->last_name = $this->last_name;
        }

        if ($std_obj->trm !== $this->trm) {
            $result->trm = $this->trm;
        }
        if ($std_obj->msk !== $this->msk) {
            $result->msk = $this->msk;
        }
        if ($std_obj->src !== $this->src) {
            $result->src = $this->src;
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * fill this seq id object based on the given object
     * if the given id is zero the id is never overwritten
     * if the given id is not zero the id is set if not yet done
     *
     * @param user|CombineObject|db_object_seq_id $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(user|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($this->name === null and $obj->name != null) {
            $this->name = $obj->name;
        }
        if ($this->ip_addr === null and $obj->ip_addr != null) {
            $this->ip_addr = $obj->ip_addr;
        }
        if ($this->email === null and $obj->email != null) {
            $this->email = $obj->email;
        }

        if ($this->password === null and $obj->password != null) {
            $this->password = $obj->password;
        }
        if ($this->activation_key === null and $obj->activation_key != null) {
            $this->activation_key = $obj->activation_key;
        }
        if ($this->activation_timeout === null and $obj->activation_timeout != null) {
            $this->activation_timeout = $obj->activation_timeout;
        }
        if ($this->db_now === null and $obj->db_now != null) {
            $this->db_now = $obj->db_now;
        }
        if ($this->last_login === null and $obj->last_login != null) {
            $this->last_login = $obj->last_login;
        }
        if ($this->last_logoff === null and $obj->last_logoff != null) {
            $this->last_logoff = $obj->last_logoff;
        }

        if ($this->profile_id === null and $obj->profile_id != null) {
            $this->profile_id = $obj->profile_id;
        }
        if ($this->code_id === null and $obj->code_id != null) {
            $this->code_id = $obj->code_id;
        }
        if ($this->type_id === null and $obj->type_id != null) {
            $this->type_id = $obj->type_id;
        }
        if ($this->right_level === null and $obj->right_level != null) {
            $this->right_level = $obj->right_level;
        }
        if ($this->status_id === null and $obj->status_id != null) {
            $this->status_id = $obj->status_id;
        }
        if ($this->excluded === null and $obj->excluded != null) {
            $this->excluded = $obj->excluded;
        }

        if ($this->created === null and $obj->created != null) {
            $this->created = $obj->created;
        }
        if ($this->description === null and $obj->description != null) {
            $this->description = $obj->description;
        }
        if ($this->first_name === null and $obj->first_name != null) {
            $this->first_name = $obj->first_name;
        }
        if ($this->last_name === null and $obj->last_name != null) {
            $this->last_name = $obj->last_name;
        }

        if ($this->trm === null and $obj->trm != null) {
            $this->trm = $obj->trm;
        }
        if ($this->msk === null and $obj->msk != null) {
            $this->msk = $obj->msk;
        }
        if ($this->src === null and $obj->src != null) {
            $this->src = $obj->src;
        }

        return $usr_msg;
    }


    /*
     * info
     */

    /**
     * @return bool true if the user has never been used
     */
    function never_used(): bool
    {
        // TODO just read the change log
        //      and because the change log is expected to be complete
        //      if the change log for this user is empty
        //      it can be assumed that the user has never done any relevant change
        return true;
    }

    /**
     * @returns bool true if the user is valid
     */
    function is_set(): bool
    {
        if ($this->id > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string the profile code id
     */
    function profile_code_id(): string
    {
        global $sys;

        $result = '';
        if ($this->is_profile_valid()) {
            $result = $sys->typ_lst->usr_pro->code_id($this->profile_id);
        }
        return $result;
    }

    /**
     * @return int|null the profile id or null if the profile is not yet set
     */
    function profile_id(): int|null
    {
        return $this->profile_id;
    }

    /**
     * @returns bool true if the user has a unique name for log entries
     */
    function is_unique(): bool
    {
        global $sys;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::EMAIL)
                or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::HUMAN)
                or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::SYS_LINK)
                or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::ADMIN)
                or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::DEV)
                or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::TEST)
                or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::SYSTEM)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @returns bool true if the user is not allowed to do any changes
     */
    function is_blocked(): bool
    {
        $result = false;
        return false;
    }

    /**
     * @returns bool true if the user has developer rights
     */
    function is_developer(): bool
    {
        global $sys;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::DEV)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @returns bool true if the user has admin rights
     */
    function is_admin(): bool
    {
        global $sys;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::ADMIN)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @returns bool true if the user is a system user e.g. the reserved word names can be used
     */
    function is_system(): bool
    {
        global $sys;
        log_debug();
        $result = false;

        // TODO Prio 1 should never happen, so create a log_err instead of a log_warning
        if ($sys->typ_lst->usr_pro == null) {
            log_warning('unexpected creation of a user profile list because it has been empty');
            $sys->typ_lst->usr_pro = new user_profile_list();
            $sys->typ_lst->usr_pro->load_dummy();
        }

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::TEST)
                or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::LOG)
                or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::SYSTEM)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @returns bool true if the user is a normal user without any privileges
     */
    function is_normal(): bool
    {
        global $sys;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::NORMAL)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool false if the profile is not set or is not found
     */
    private
    function is_profile_valid(): bool
    {
        if ($this->profile_id > 0) {
            return true;
        } else {
            return false;
        }
    }

    // true if the user has the right to import data
    function can_import(): bool
    {
        global $sys;
        log_debug();
        $result = false;

        if ($this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::ADMIN)
            or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::TEST)
            or $this->profile_id == $sys->typ_lst->usr_pro->id(user_profiles::SYSTEM)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @return term load the last term used by the user
     */
    function last_term(): term
    {

        if ($this->trm == null) {
            $trm = new term($this);
            $trm->load_by_id(words::DEFAULT_WORD_ID);
            $this->trm = $trm;
        } elseif ($this->term_id() != 0 and ($this->term_name() == null or $this->term_name() == '')) {
            $this->trm->load_by_id($this->term_id());
        }
        return $this->trm;
    }

    /**
     * set the parameters for the virtual user that represents the standard view for all users
     */
    function dummy_all(): void
    {
        $this->id = 0;
        // TODO Prio 1 use a const
        $this->code_id = 'all';
        $this->name = 'standard user view for all users';
    }

    // create the HTML code to display the username with the HTML link
    function display(): string
    {
        return '<a href="/http/user.php?id=' . $this->id . '">' . $this->name . '</a>';
    }

    // remember the last source that the user has used
    // TODO add the database field
    function set_verb($vrb_id): bool
    {
        log_debug('(' . $this->id . ',s' . $vrb_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $result = $db_con->set_class(user::class);
        //$result = $db_con->update($this->id, verb_db::FLD_ID, $vrb_id);
        return $result;
    }

    function set_profile(
        string       $code_id,
        user_message $msg
    ): void
    {
        global $sys;
        $usr = $msg->usr;
        if ($usr != null) {
            $profile_id = $sys->typ_lst->usr_pro->id($code_id);
            if ($profile_id <= 0) {
                $profile_id = $sys->typ_lst->usr_pro->id_by_name($code_id);
            }
            if ($profile_id <= 0) {
                $msg->add_info_text('default profile used because no profile found for ' . $code_id);
                $this->profile_id = $sys->typ_lst->usr_pro->id_by_name(user_profiles::NORMAL);
            } else {
                if ($usr->can_set_profile($profile_id)) {
                    $this->profile_id = $profile_id;
                } else {
                    $msg->add(msg_id::USER_NO_IMPORT_PRIVILEGES, [
                        msg_id::VAR_USER_NAME => $this->name(),
                        msg_id::VAR_USER_PROFILE => $usr->name_and_profile()
                    ]);
                }
            }
        } else {
            $this->profile_id = user_profiles::NORMAL_ID;
        }
    }

    function set_type(
        string       $code_id,
        user_message $msg
    ): void
    {
        global $sys;
        $usr = $msg->usr;
        if ($usr != null) {
            $type_id = $sys->typ_lst->usr_typ->id($code_id);
            if ($usr->can_set_type_id()) {
                $this->type_id = $type_id;
            } else {
                $msg->add(msg_id::USER_NO_IMPORT_PRIVILEGES, [
                    msg_id::VAR_USER_NAME => $this->name(),
                    msg_id::VAR_USER_PROFILE => $usr->name_and_profile()
                ]);
            }
        } else {
            $this->type_id = $sys->typ_lst->usr_typ->id(user_types::GUEST);
        }
    }

    /**
     * set the user profile id directly, which is hopefully only used once to set the profile of the system user
     * @param int $profile_id the id of the user profile
     * @return void
     */
    function set_profile_id(int $profile_id): void
    {
        $this->profile_id = $profile_id;
    }

    // set the main log entry parameters for updating one word field
    private
    function log_upd(): change
    {
        log_debug(' user ' . $this->name);
        $log = new change($this);
        $log->set_action(change_actions::UPDATE);
        $log->set_table(change_tables::USER);

        return $log;
    }

    /**
     * if at least one other user has switched off all changes from this user
     * all changes of this user should be excluded from the standard values
     * e.g. a user has a
     */
    function exclude_from_standard()
    {
    }

    /**
     * check if a preserver username is trying to be added
     * and if return a message to the user to suggest another name
     *
     * @param user $usr the user who has request the user adding or update
     * @param user_message $msg
     * @return bool true if ...
     */
    protected
    function check_preserved(user_message $msg, user $usr): bool
    {
        // system users are always allowed to add users e.g. to add the system users
        if (!$usr->is_system()) {
            if (in_array($this->name(), users::RESERVED_NAMES)) {
                // the admin user needs to add the read test objects during initial load
                if ($usr->is_admin() and !in_array($this->name(), users::FIXED_NAMES)) {
                    $msg->add(msg_id::USER_IS_RESERVED, [
                        msg_id::VAR_USER_NAME => $this->name(),
                        msg_id::VAR_NAME_LIST => implode(',', users::RESERVED_NAMES)
                    ]);
                }
            }
        }
        return $msg->is_ok();
    }

    function is_same(user $usr, user_message $usr_msg): bool
    {
        $result = false;
        $fvt_lst = $this->db_fields_changed($usr, $usr_msg);
        if ($fvt_lst->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /*
     * save
     */

    /**
     * add or update a user in the database
     *
     * @param user|null $usr_req the user who has request the user adding or update
     * @param user_message $msg the message that should be shown to the user in case something went wrong
     *                              or the database id of the user just added
     */
    function save_user(user_message $msg, ?user $usr_req = null): void
    {
        // all potential time intensive function should start with a log message to detect time improvement potential
        log_debug($this->dsp_id());

        // use the already open database connection of the already started process
        global $db_con;
        // get the user that is logged in and is requesting the changes
        global $usr;

        if ($usr_req == null) {
            $usr_req = clone $usr;
        }

        // configure the global database connection object for the select, insert, update and delete queries
        $db_con->set_class($this::class);
        $db_con->set_usr($usr_req->id);

        // check the preserved names
        if ($this->check_preserved($msg, $usr_req)) {
            // check if a user with the same name or email already exists
            if ($this->id == 0) {
                // if a new user is supposed to be added check upfront for a similar object to prevent adding duplicates
                log_debug('check possible duplicates before adding ' . $this->dsp_id());
                $similar = $this->get_similar($msg);
                if ($similar != null) {
                    if ($similar->id <> 0) {
                        log_debug('got similar ' . $similar->dsp_id());
                        // check that the get_similar function has really found a similar object and report potential program errors
                        if (!$this->is_similar($similar)) {
                            $msg->add(msg_id::NOT_SIMILAR_OBJECTS, [
                                msg_id::VAR_NAME => $this->dsp_id(),
                                msg_id::VAR_NAME_CHK => $similar->dsp_id()
                            ]);
                        } else {
                            // if similar is found set the id to trigger the updating instead of adding
                            $similar->load_by_id($similar->id); // e.g. to get the type_id
                            $this->id = $similar->id;
                        }
                    } else {
                        log_debug('no similar to ' . $this->dsp_id() . ' found');
                        $similar = null;
                    }
                }
            }
        }

        // create or update
        if ($msg->is_ok()) {
            if ($this->id == 0) {

                // create a user if no similar user has been found
                $msg->merge($this->db_insert($db_con, $usr_req));

            } else {

                // update the user

                // read the database parameter of the user as of now
                $db_rec = clone $this;
                $db_rec->reset();
                if ($db_rec->load_by_id($this->id) != $this->id) {
                    $lib = new library();
                    $msg->add(msg_id::FAILED_RELOAD_CLASS, [
                        msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
                    ]);
                } else {
                    if (!$this->is_same($db_rec, $msg)) {
                        $msg->merge($this->db_update_user($db_con, $db_rec, $usr_req));
                    }
                }
            }
        }
    }

    /**
     * fixed sql to add a system user without log
     * @return user_message
     */
    private
    function save_direct(): user_message
    {
        // use the already open database connection of the already started process
        global $db_con;

        $usr_msg = new user_message();

        // configure the global database connection object for the select, insert, update and delete queries
        $db_con->set_class($this::class);
        $db_con->set_usr($this->id);
        $sc_par_lst = new sql_type_list();

        // fields and values that the word has additional to the standard named user sandbox object
        $usr_empty = $this->clone_reset();
        // get the list of the changed fields
        $fvt_lst = $this->db_fields_changed($usr_empty, $usr_msg, $sc_par_lst);
        // get the list of all fields that can be changed by the user
        $fld_lst_all = $this->db_fields_all();

        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all, $usr_msg);

        // update the sql creator settings
        $sc = $db_con->sql_creator();
        $sc->set_class($this::class, $sc_par_lst);
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);
        $sc->set_name($qp->name);
        $qp->sql = $sc->create_sql_insert($fvt_lst);
        $qp->par = $fvt_lst->db_values();

        $msg = 'add and log ' . $this->dsp_id();
        if ($db_con->insert($qp, $msg, $usr_msg)) {
            $this->id = $usr_msg->get_row_id();
        }

        return $usr_msg;
    }


    /*
     * similar
     */

    /**
     * check if a user with the name or email already exists
     * @param user_message $msg the user who has requested the update and the object to collect the potential reject messages
     * @return user|null a filled object that has the same name
     *                 or null if nothing similar has been found
     */
    function get_similar(user_message $msg): user|null
    {
        $sim = new user();
        if ($this->name != '' and $this->name != null and $this->email != '' and $this->email != null) {
            $sim->load_by_name_or_email($this->name, $this->email);
        } elseif ($this->name != '' and $this->name != null) {
            $sim->load_by_name($this->name);
        } elseif ($this->email != '' and $this->email != null) {
            $sim->load_by_email($this->email);
        }
        if ($sim->id() == 0) {
            return null;
        }
        return $sim;
    }

    /**
     * check if all unique key of the given user matches with this
     * and there it can be assumed that the users are the same
     * @return bool true if all unique keys match
     */
    function is_similar(user $similar): bool
    {
        $result = true;
        if ($this->name != null and $similar->name != null) {
            if ($this->name != $similar->name) {
                $result = false;
            }
        }
        if ($this->email != null and $similar->email != null) {
            if ($this->email != $similar->email) {
                $result = false;
            }
        }
        return $result;
    }


    /*
     * add
     */

    /**
     * create a new user in the database
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param user $usr_req the user who has request the user adding or update
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    private
    function db_insert(sql_db $db_con, user $usr_req): user_message
    {
        log_debug($this->dsp_id());

        // always return a user message and if everything is fine, it is just empty
        $msg = new user_message();
        $msg->usr = $usr_req;

        // use the signup system user for standard accounts if no requesting user is given
        if ($usr_req->id == 0) {
            $usr_req->load_by_code_id(users::SYSTEM_SIGNUP_CODE_ID);
        }

        if ($this->can_add($usr_req)) {
            // the sql creator is used more than once, so create it upfront
            $sc = $db_con->sql_creator();
            $qp = $this->sql_insert($sc, $msg, new sql_type_list([sql_type::LOG]));
            $msg_txt = 'add and log ' . $this->dsp_id();
            if ($db_con->is_open()) {
                if ($db_con->insert($qp, $msg_txt, $msg)) {
                    $this->id = $msg->get_row_id();
                }
            }
        } else {
            log_debug('no permission to add user ' . $this->dsp_id());
            $msg->add(msg_id::USER_NO_ADD_PRIVILEGES, [
                msg_id::VAR_USER_NAME => $this->name(),
                msg_id::VAR_USER_PROFILE => $usr_req->name_and_profile()
            ]);
        }

        return $msg;
    }


    /*
     * update
     */

    /**
     * save all updated fields with one sql function
     * similar to the sandbox save_fields_func function but for non-user sandbox objects like the user
     *
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param user $db_usr the database record before saving the changes whereas $this is the record with the changes
     * @param user $usr_req the user who has request the user adding or update
     * @return user_message with the description of any problems for the user and the suggested solution
     */
    private function db_update_user(sql_db $db_con, user $db_usr, user $usr_req): user_message
    {
        log_debug($this->dsp_id());

        // always return a user message and if everything is fine, it is just empty
        $msg = new user_message();
        $msg->usr = $usr_req;

        if ($this->can_be_changed_by($msg, $db_usr)) {
            // the sql creator is used more than once, so create it upfront
            $sc = $db_con->sql_creator();

            if (in_array($this->name(), users::TEST_NO_LOG)) {
                $qp = $this->sql_update($sc, $db_usr, $msg, new sql_type_list([]));
                $db_con->update($qp, 'update ' . $this->dsp_id(), $msg);
            } else {
                $qp = $this->sql_update($sc, $db_usr, $msg, new sql_type_list([sql_type::LOG]));
                $db_con->update($qp, 'update and log ' . $this->dsp_id(), $msg);
            }

            log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        } else {
            $msg->add(msg_id::USER_NO_UPDATE_PRIVILEGES, [
                msg_id::VAR_USER_NAME => $this->name(),
                msg_id::VAR_USER_PROFILE => $usr_req->name_and_profile()
            ]);
        }

        return $msg;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to insert a user in the database
     * always all fields are included in the query to be able to remove overwrites with a null value
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // set some var names to shorten the code lines
        $var_name_row_id = $sc->var_name_row_id($sc_par_lst);

        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        // fields and values that the word has additional to the standard named user sandbox object
        $sbx_empty = $this->clone_reset();
        // get the list of the changed fields
        $fvt_lst = $this->db_fields_changed($sbx_empty, $usr_msg, $sc_par_lst_used);
        // get the list of all fields that can be changed by the user
        $fld_lst_all = $this->db_fields_all();

        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all, $usr_msg);

        // TODO check if the prepared function already exists and if yes, skip the query recreation

        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst_used, $ext);

        // log functions must always use named parameters
        $sc_par_lst_used->add(sql_type::NAMED_PAR);

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();

        // add the change action field to the field list for the log entries
        global $sys;
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $sys->typ_lst->cng_act->id(change_actions::ADD),
            type_object::FLD_ID_SQL_TYP
        );

        // init the function body
        $id_fld_new = $sc->var_name_new_id($sc_par_lst_used);
        $sql = $sc->sql_func_start($id_fld_new, $sc_par_lst_used);

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = $sc_par_lst_used->remove(sql_type::LOG);
        $sc_par_lst_sub->add(sql_type::LIST);
        $sc_par_lst_log = clone $sc_par_lst_sub;
        $sc_par_lst_log->add(sql_type::INSERT_PART);

        // create sql to set the prime key upfront to get the sequence id
        $qp_id = clone $qp;
        $qp_id = $this->sql_insert_key_field($sc, $qp_id, $fvt_lst, $id_fld_new, $usr_msg, $sc_par_lst_sub);
        $par_lst_out->add($qp_id->par_fld);
        $sql .= $qp_id->sql;

        // get the data fields and move the unique db key field to the first entry
        $fld_lst_ex_log = array_intersect($fvt_lst->names(), $fld_lst_all);

        $key_fld_pos = array_search(user_db::FLD_NAME, $fld_lst_ex_log);
        unset($fld_lst_ex_log[$key_fld_pos]);
        $fld_lst_log = array_merge([$qp_id->par_fld->name], $fld_lst_ex_log);

        // add the requesting user id for logging
        $fvt_lst_log = clone $fvt_lst;
        $fvt_lst_log->add_field(
            user_db::FLD_ID,
            $usr_msg->usr->id,
            sql_par_type::INT
        );

        // create the query parameters for the log entries for the single fields
        $qp_log = $sc->sql_func_log($this::class, $usr_msg->usr, $fld_lst_log, $fvt_lst_log, $usr_msg, $sc_par_lst_log);
        $sql .= ' ' . $qp_log->sql;
        $par_lst_out->add_list($qp_log->par_fld_lst);

        if (!$sc_par_lst_used->is_call_only()) {

            // update the fields excluding the unique id
            $update_fvt_lst = new sql_par_field_list();
            foreach ($fld_lst_ex_log as $fld) {
                $update_fvt_lst->add($fvt_lst->get($fld, $usr_msg));
            }
            $sc_update = clone $sc;
            $sc_par_lst_upd = $sc_par_lst_used;
            $sc_par_lst_upd->add(sql_type::UPDATE);
            $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
            $sc_par_lst_upd_ex_log->add(sql_type::SUB);
            $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);

            $qp_update->sql = $sc_update->create_sql_update(
                user_db::FLD_ID, $var_name_row_id . user_db::FLD_ID, $update_fvt_lst, [], $sc_par_lst_upd_ex_log);
            // add the insert row to the function body
            $sql .= ' ' . $qp_update->sql . ' ';
        }

        if ($sc->db_type == sql_db::POSTGRES) {
            if ($id_fld_new != '') {
                $sql .= sql::RETURN . ' ' . $id_fld_new . '; ';
            }
        }

        // create the query parameters for the actual change
        $qp_chg = clone $qp;

        if (!$sc_par_lst_used->is_call_only()) {
            $sql .= $sc->sql_func_end();

            $qp_chg->sql = $sc->create_sql_insert($par_lst_out, $sc_par_lst_used);

            // merge all together and create the function
            $qp->sql = $qp_chg->sql . $sql . ';';
        }
        $qp->par = $par_lst_out->values();

        // create the call sql statement
        return $sc->sql_call($qp, $qp_chg->name, $par_lst_out);
    }

    /**
     * create the sql statement to update a user in the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param user|db_object_seq_id $db_row the sandbox object with the database values before the update
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par|null the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_update(
        sql_creator           $sc,
        user|db_object_seq_id $db_row,
        user_message          $usr_msg,
        sql_type_list         $sc_par_lst = new sql_type_list()
    ): sql_par|null
    {
        if ($this->can_update($usr_msg)) {
            global $sys;
            // clone the parameter list to avoid changing the given list
            $sc_par_lst_used = clone $sc_par_lst;
            // set the sql query type
            $sc_par_lst_used->add(sql_type::UPDATE);
            // update does not need to return an id
            $sc_par_lst_used->add(sql_type::NO_ID_RETURN);
            // log for users is always on and log functions must always use named parameters
            if ($sc_par_lst->incl_log()) {
                $sc_par_lst_used->add(sql_type::NAMED_PAR);
            }
            // get the field names, values and parameter types that have been changed
            // and that needs to be updated in the database
            // the db_* child function call the corresponding parent function
            // including the sql parameters for logging
            $fvt_lst = $this->db_fields_changed($db_row, $usr_msg, $sc_par_lst_used);
            // get the list of all fields that can be changed by the user
            $fld_lst_all = $this->db_fields_all();

            // create either the prepared sql query or a sql function that includes the logging of the changes
            // unlike the db_* function the sql_update_* parent function is called directly
            //return $this::sql_update_switch($sc, $fld_lst, $all_fields, $sc_par_lst_used);

            // make the query name unique based on the changed fields
            $lib = new library();
            $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all, $usr_msg);

            // TODO check if the prepared function already exists and if yes, skip the query recreation

            // create the main query parameter object and set the query name
            $qp = $this->sql_common($sc, $sc_par_lst_used, $ext);

            if (!$fvt_lst->is_empty_except_internal_fields()) {

                // set some var names to shorten the code lines
                $id_fld = user_db::FLD_ID;
                $id_val = '_' . $id_fld;

                // add the change action field to the field list for the log entries
                $fvt_lst->add_field(
                    change_action::FLD_ID,
                    $sys->typ_lst->cng_act->id(change_actions::UPDATE),
                    type_object::FLD_ID_SQL_TYP
                );

                // list of parameters actually used in order of the function usage
                $par_lst_out = new sql_par_field_list();

                // get the fields actually changed
                $fld_lst = $fvt_lst->names();
                $fld_lst_chg = array_intersect($fld_lst, $fld_lst_all);

                // init the function body
                if ($sc_par_lst->incl_log()) {
                    $sql = $sc->sql_func_start('', $sc_par_lst_used);

                    // don't use the log parameter for the sub queries
                    $sc_par_lst_sub = $sc_par_lst_used->remove(sql_type::LOG);
                    $sc_par_lst_sub->add(sql_type::LIST);
                    $sc_par_lst_log = clone $sc_par_lst_sub;
                    $sc_par_lst_log->add(sql_type::UPDATE_PART);

                    // add the row id
                    $fvt_lst->add_field(
                        $sc->id_field_name(),
                        $this->id,
                        db_object_seq_id::FLD_ID_SQL_TYP);

                    // create the query parameters for the log entries for the single fields
                    $qp_log = $sc->sql_func_log_update($this::class, $usr_msg->usr, $fld_lst_chg, $fvt_lst, $sc_par_lst_log, $this->id);
                    $sql .= ' ' . $qp_log->sql;
                    $par_lst_out->add_list($qp_log->par_fld_lst);
                } else {
                    $sql = '';

                    // add the parameters with type
                    $par_lst_out = new sql_par_field_list();
                    foreach ($fld_lst_chg as $fld) {
                        $par_lst_out->add_field(
                            $fld,
                            $fvt_lst->get_value($fld),
                            $fvt_lst->get_type($fld));
                    }

                    // add the row id
                    $fvt_lst->add_field(
                        $sc->id_field_name(),
                        $this->id,
                        db_object_seq_id::FLD_ID_SQL_TYP);

                    // add the row id of the standard table for user overwrites
                    $log_id = $fvt_lst->get_value($id_fld);
                    $id_type = $fvt_lst->get_type($id_fld);
                    $par_lst_out->add_field(
                        $id_fld,
                        $log_id,
                        $id_type);
                }

                // update the fields excluding the unique id
                $update_fvt_lst = new sql_par_field_list();
                foreach ($fld_lst_chg as $fld) {
                    $update_fvt_lst->add($fvt_lst->get($fld, $usr_msg));
                }
                $sc_update = clone $sc;
                if ($sc_par_lst->incl_log()) {
                    $sc_par_lst_upd = new sql_type_list([sql_type::NAMED_PAR, sql_type::UPDATE, sql_type::UPDATE_PART]);
                } else {
                    $sc_par_lst_upd = new sql_type_list([sql_type::UPDATE, sql_type::UPDATE_PART]);
                }
                $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
                $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);
                $qp_update->sql = $sc_update->create_sql_update(
                    $id_fld, $id_val, $update_fvt_lst, [], $sc_par_lst_upd, true, '', $id_fld);
                // add the insert row to the function body
                $sql .= ' ' . $qp_update->sql . ' ';

                if ($sc_par_lst->incl_log()) {
                    $sql .= $sc->sql_func_end();
                }

                // create the query parameters for the actual change
                $qp_chg = clone $qp;
                if ($sc_par_lst->incl_log()) {
                    $qp_chg->sql = $sc->create_sql_update(
                        $id_fld, $id_val, $par_lst_out, [], $sc_par_lst_used);
                    $qp_chg->par = $fvt_lst->values();
                }

                // merge all together and create the function
                if ($sc_par_lst->incl_log()) {
                    $qp->sql = $qp_chg->sql . $sql . ';';
                } else {
                    $sc->set_par_list($par_lst_out->sql_field_list());
                    $qp->sql = $sc->prepare_sql($sql, $qp_chg->name, $par_lst_out->types());
                }
                $qp->par = $par_lst_out->values();

                // create the call sql statement
                if ($sc_par_lst->incl_log()) {
                    return $sc->sql_call($qp, $qp_chg->name, $par_lst_out);
                } else {
                    return $qp;
                }
            } else {
                return $qp;
            }
        } else {
            return null;
        }
    }

    /**
     * create the sql statement to add a new user to the database
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
        $ext = sql::NAME_SEP . sql_creator::FILE_INSERT;

        // list of parameters actually used in order of the function usage
        $sql = '';
        $fvt_insert = $fvt_lst->get(user_db::FLD_NAME, $usr_msg);

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
        if ($sc->db_type == sql_db::MYSQL) {
            $sql .= ' ' . sql::LAST_ID_MYSQL . $sc->var_name_row_id($sc_par_lst_sub) . '; ';
        }

        $qp->sql = $sql;
        $qp->par_fld = $fvt_insert;

        return $qp;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     *
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return user_db::FLD_NAMES;
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param user|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        user|db_object_seq_id $obj,
        user_message          $msg,
        sql_type_list         $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $lst = new sql_par_field_list();
        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        if ($do_log) {
            $table_id = $sc->table_id($this::class);
        }

        // the user database fields in order of user_db::FLD_NAMES and user_db::FLD_LST_ALL

        // the username must be unique
        if ($obj->name_or_null() !== $this->name()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_NAME,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_NAME),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_NAME,
                $this->name(),
                sandbox_named::FLD_NAME_SQL_TYP,
                $obj->name_or_null()
            );
        }
        // the ip address should always be included
        if ($obj->ip_addr !== $this->ip_addr) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_IP_ADDR,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_IP_ADDR),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_IP_ADDR,
                $this->ip_addr,
                sql_field_type::CODE_ID,
                $obj->ip_addr
            );
        }
        // the is used as the name if no name is given
        if ($obj->email !== $this->email) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_EMAIL,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_EMAIL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_EMAIL,
                $this->email,
                sql_field_type::CODE_ID,
                $obj->email
            );
        }

        // password should not be part of the change log
        if ($obj->password <> $this->password) {
            // TODO Prio 3 log the password hash change in a admin only log for security reasons
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_PASSWORD,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_PASSWORD),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_PASSWORD,
                $this->password,
                sql_field_type::NAME,
                $obj->password
            );
        }
        // the activation_key is used during the signup process and is not logged
        if ($obj->activation_key <> $this->activation_key) {
            // the change of the activation_key if logged e.g. to be able to limit the number of login attempts
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_ACTIVATION_KEY,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_ACTIVATION_KEY),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_ACTIVATION_KEY,
                $this->activation_key,
                sql_field_type::NAME,
                $obj->activation_key
            );
        }
        // the activation_timeout is used during the signup process and is not logged
        if ($obj->activation_timeout <> $this->activation_timeout) {
            // the change of the activation_timeout if logged e.g. to be able to limit the number of login attempts
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_ACTIVATION_TIMEOUT,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_ACTIVATION_TIMEOUT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_ACTIVATION_TIMEOUT,
                $this->activation_timeout?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->activation_timeout?->format(sql_db::DATE_FORMAT)
            );
        }
        // the db_now field is read only and never written
        // the last_login is used mainly to log the login and logoff events
        if ($obj->last_login <> $this->last_login) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_LAST_LOGIN,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_LAST_LOGIN),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_LAST_LOGIN,
                $this->last_login?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->last_login?->format(sql_db::DATE_FORMAT)
            );
        }
        // the last_logoff is used mainly to log the login and logoff events
        if ($obj->last_logoff <> $this->last_logoff) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_LAST_LOGOUT,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_LAST_LOGOUT),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_LAST_LOGOUT,
                $this->last_logoff?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->last_logoff?->format(sql_db::DATE_FORMAT)
            );
        }

        // TODO a profile with more permissions can only be set by a user that has the permission to do so
        if ($obj->profile_id() !== $this->profile_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_PROFILE,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_PROFILE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($sys->typ_lst->usr_pro == null) {
                log_fatal('no user profile found', 'user->db_fields_changed');
            } else {
                if ($this->profile_id() < 0) {
                    $msg->add(msg_id::USER_PROFILE_MISSING, [
                        msg_id::VAR_TYPE => $this->profile_id(),
                        msg_id::VAR_NAME => $this->dsp_id()
                    ]);
                }
                $lst->add_type_field(
                    user_db::FLD_PROFILE,
                    user_profile::FLD_NAME,
                    $this->profile_id(),
                    $obj->profile_id(),
                    $sys->typ_lst->usr_pro);
            }
        }
        // the code_id to select users with predefined assigned functionality that can change their username
        if ($obj->code_id !== $this->code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_CODE_ID,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_CODE_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_CODE_ID,
                $this->code_id,
                sql_field_type::CODE_ID,
                $obj->code_id
            );
        }
        // TODO the confirmation levels should created and be added
        // the confirmation type should only be changed by the system based on the confirmation process
        if ($obj->type_id !== $this->type_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_TYPE_ID,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_TYPE_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_type_field(
                user_db::FLD_TYPE_ID,
                user_type::FLD_NAME,
                $this->type_id,
                $obj->type_id,
                $sys->typ_lst->usr_typ);
        }
        if ($obj->right_level !== $this->right_level) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_LEVEL,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_LEVEL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_LEVEL,
                $this->right_level,
                sql_field_type::INT_SMALL,
                $obj->right_level
            );
        }
        if ($obj->status_id !== $this->status_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_STATUS,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_STATUS),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($sys->typ_lst->usr_sta == null) {
                log_fatal('no user status found', 'user->db_fields_changed');
            } else {
                if ($this->status_id < 0) {
                    $msg->add(msg_id::USER_STATUS_MISSING, [
                        msg_id::VAR_TYPE => $this->status_id,
                        msg_id::VAR_NAME => $this->dsp_id()
                    ]);
                }
                $lst->add_type_field(
                    user_db::FLD_STATUS,
                    user_status::FLD_NAME,
                    $this->status_id,
                    $obj->status_id,
                    $sys->typ_lst->usr_sta);
            }
        }
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
                $obj->excluded
            );
        }

        // created ist the time when the user has been save the first time so actually a change log entry is never expeted
        if ($this->created === null) {
            $this->created = new DateTime();
        }
        if ($obj->created <> $this->created) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_CREATED,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_CREATED),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_CREATED,
                $this->created?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->created?->format(sql_db::DATE_FORMAT)
            );
        }
        // the description is mainly used for system users
        if ($obj->description !== $this->description) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_DESCRIPTION,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_DESCRIPTION,
                $this->description,
                sql_db::FLD_DESCRIPTION_SQL_TYP,
                $obj->description
            );
        }
        // in may be useful to move the name and other non-critical user parameters to a value_list
        if ($obj->first_name !== $this->first_name) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_FIRST_NAME,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_FIRST_NAME),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_FIRST_NAME,
                $this->first_name,
                sql_field_type::NAME,
                $obj->first_name
            );
        }
        // in may be useful to move the last name and other non-critical user parameters to a value_list
        if ($obj->last_name !== $this->last_name) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_LAST_NAME,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_LAST_NAME),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_LAST_NAME,
                $this->last_name,
                sql_field_type::NAME,
                $obj->last_name
            );
        }

        // for the last used term additional the name is written to the log
        if ($obj->term_id() !== $this->term_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_TERM,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_TERM),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                user_db::FLD_TERM,
                term::FLD_NAME,
                $this->trm,
                $obj->trm
            );
        }
        // for the view id additional the view name is written to the log
        if ($obj->msk?->id() !== $this->msk?->id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_VIEW,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_VIEW),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            // TODO Prio 3 check that always the add_link_field function is used to add a link field
            $lst->add_link_field(
                user_db::FLD_VIEW,
                view_db::FLD_NAME,
                $this->src,
                $obj->src
            );
        }
        // for the source id additional the source name is written to the log
        if ($obj->source_id() !== $this->source_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_SOURCE,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_SOURCE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                user_db::FLD_SOURCE,
                source_db::FLD_NAME,
                $this->src,
                $obj->src
            );
        }


        return $lst;
    }


    /*
     * del
     */

    /**
     * exclude, archive or delete this user
     *
     * @param user|null $usr_req the user who has request the user adding or update
     * @param user_message $msg with status ok
     *                              or if something went wrong
     *                              the message that should be shown to the user
     *                              including suggested solutions
     * @return bool true if everything has been fine
     */
    function del(user_message $msg, user|null $usr_req = null): bool
    {
        if ($this->never_used()) {
            $lib = new library();
            $class_name = $lib->class_to_name($this::class);
            if ($this->id == 0) {
                $msg->add(msg_id::ID_MISSING_FOR_DEL, [
                    msg_id::VAR_CLASS_NAME => $class_name,
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            } else {
                // refresh the object with the database to include all updates utils now
                $reloaded = false;
                $reloaded_id = $this->load_by_id($this->id);
                if ($reloaded_id != 0) {
                    $reloaded = true;
                }
                if (!$reloaded) {
                    log_warning('Reload of for deletion has failed',
                        $class_name . '->del',
                        'Reload of ' . $class_name . ' ' . $this->dsp_id()
                        . ' for deletion has failed.',
                        (new Exception)->getTraceAsString());
                } else {
                    log_debug('reloaded ' . $this->dsp_id());
                    // check if the object is still valid
                    if ($this->id <= 0) {
                        log_warning('Delete failed',
                            $class_name . '->del',
                            'Delete failed, because it seems that the ' . $class_name . ' ' . $this->dsp_id()
                            . ' has been deleted in the meantime.', (new Exception)->getTraceAsString());
                    } else {
                        if ($usr_req == null) {
                            global $usr;
                            $usr_req = $usr;
                        }
                        // TODO check if there are related log entries and if yes exclude it instead of delete
                        $msg->merge(parent::del_exe($usr_req));
                    }
                }
            }
            return $msg->is_ok();
        } else {
            $msg->add(msg_id::USER_CANNOT_DEL, [
                msg_id::VAR_USER_NAME => $this->name(),
            ]);
        }
        return $msg->is_ok();
    }


    /*
     * info
     */

    /**
     * the name of the log entry used shown to the user which entry has been deleted
     * e.g. for the user it can be the name, the ip-address, or as fallback the database id
     * @return string the field name(s) of the prime database index of the object
     */
    function log_name_field(): string
    {
        $fld_name = $this->id_field();
        if ($this->name != '') {
            $fld_name = user_db::FLD_NAME;
        } elseif ($this->email != '') {
            $fld_name = user_db::FLD_EMAIL;
        } elseif ($this->ip_addr != '') {
            $fld_name = user_db::FLD_IP_ADDR;
        }
        return $fld_name;
    }


    /*
     * debug
     */

    function dsp_id(): string
    {
        $result = $this->name();
        if ($this->email != '' and $this->email != null and $this->email != $this->name()) {
            $result .= ' - ' . $this->email;
        }
        if ($this->ip_addr != '' and $this->ip_addr != null and $this->ip_addr != $this->name()) {
            $result .= ' - ip ' . $this->ip_addr;
        }
        if ($this->id != 0) {
            $result .= ' (' . $this->id . ')';
        }
        return $result;
    }

}
