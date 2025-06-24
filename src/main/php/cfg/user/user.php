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
    - information:       functions to make code easier to read
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

namespace cfg\user;

include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
include_once MODEL_HELPER_PATH . 'db_object.php';
//include_once DB_PATH . 'db_check.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
//include_once MODEL_IMPORT_PATH . 'import_file.php';
include_once MODEL_SYSTEM_PATH . 'ip_range_list.php';
//include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
//include_once MODEL_LOG_PATH . 'change_table_list.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
//include_once MODEL_REF_PATH . 'source.php';
//include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_USER_PATH . 'user_db.php';
include_once MODEL_USER_PATH . 'user_profile.php';
include_once MODEL_USER_PATH . 'user_type.php';
//include_once MODEL_VERB_PATH . 'verb_list.php';
//include_once MODEL_VIEW_PATH . 'view.php';
//include_once MODEL_VIEW_PATH . 'view_sys_list.php';
//include_once MODEL_PHRASE_PATH . 'term.php';
include_once SHARED_HELPER_PATH . 'Config.php';
include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_CONST_PATH . 'users.php';
include_once SHARED_ENUM_PATH . 'change_actions.php';
include_once SHARED_ENUM_PATH . 'change_tables.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_ENUM_PATH . 'user_profiles.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'library.php';
include_once SHARED_PATH . 'json_fields.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\db_object_seq_id;
use cfg\helper\type_object;
use cfg\log\change_action;
use cfg\phrase\term;
use cfg\system\ip_range_list;
use cfg\log\change;
use cfg\sandbox\sandbox_named;
use cfg\ref\source;
use cfg\verb\verb_list;
use cfg\view\view_sys_list;
use shared\const\users;
use shared\const\words;
use shared\enum\change_actions;
use shared\enum\change_tables;
use shared\enum\messages as msg_id;
use shared\enum\user_profiles;
use shared\helper\Config as shared_config;
use shared\json_fields;
use Exception;
use shared\library;
use shared\types\api_type_list;

class user extends db_object_seq_id
{

    /*
     * db const
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for users including system users; only users can add data';

    // forward the const to enable usage of $this::CONST_NAME
    const FLD_ID = user_db::FLD_ID;
    const FLD_NAMES = user_db::FLD_NAMES;
    const FLD_LST_ALL = user_db::FLD_LST_ALL;


    /*
     * object vars
     */

    // database fields
    public ?string $name = null;          // simply the username, which cannot be empty
    public ?string $ip_addr = null;       // simply the ip address used if no username is given
    public ?string $password = null;      // only used for the login and password change process
    public ?string $description = null;   // used for system users to describe the target; can be used by users for a short introduction
    public ?string $code_id = null;       // the main id to detect system users
    public ?int $profile_id = null;       // id of the user profile
    public ?int $type_id = null;          // the confirmation level of the user e.g. email checked or passport checked
    public ?string $email = null;         //
    public ?string $first_name = null;    //
    public ?string $last_name = null;     //
    // TODO move to user config e.g. by using the key word "pod-user-config"
    public ?string $dec_point = null;     // the decimal point char for this user
    public ?string $thousand_sep = null;  // the thousand separator user for this user
    public ?int $percent_decimals = null; // the number of decimals for this user

    public ?user_profile $profile = null; // to define the base rights of a user which can be further restricted but not expanded

    // user setting parameters
    // in memory only fields
    // the last term used by the user
    private ?term $trm = null;
    // the last source used by the user
    private ?source $source = null;

    // TODO add set and get
    // e.g. only admin are allowed to see other user parameters
    public ?user $viewer = null;          // the user who wants to access this user


    // var used for the registration and logon process
    public ?string $activation_key = '';
    public ?string $activation_timeout = '';
    public ?string $db_now = '';


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

        //global $usr_pro_cac;
        //$this->profile = $usr_pro_cac->get_by_code_id(user_profiles::NORMAL);
        //$this->profile = cl(db_cl::USER_PROFILE, user_profiles::NORMAL);

    }

    function reset(): void
    {
        $this->set_id(0);
        $this->name = null;
        $this->description = null;
        $this->ip_addr = null;
        $this->email = null;
        $this->first_name = null;
        $this->last_name = null;
        $this->code_id = null;
        $this->dec_point = null;
        $this->thousand_sep = shared_config::DEFAULT_THOUSAND_SEP;
        $this->percent_decimals = shared_config::DEFAULT_PERCENT_DECIMALS;
        $this->profile_id = null;

        $this->trm = null;
        $this->source = null;

        $this->viewer = null;

        $this->activation_key = '';
        $this->activation_timeout = '';
        $this->db_now = '';

    }

    /**
     * create a clone and empty all fields
     *
     * @return $this a clone with the name changed
     */
    function clone_reset(): user
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
        $obj_cpy->set_id($this->id());
        $obj_cpy->set_name($name);
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

        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $this->code_id = $db_row[sql::FLD_CODE_ID];
            $this->name = $db_row[user_db::FLD_NAME];
            $this->ip_addr = $db_row[user_db::FLD_IP_ADDR];
            $this->email = $db_row[user_db::FLD_EMAIL];
            if (array_key_exists(user_db::FLD_FIRST_NAME, $db_row)) {
                $this->first_name = $db_row[user_db::FLD_FIRST_NAME];
            }
            if (array_key_exists(user_db::FLD_LAST_NAME, $db_row)) {
                $this->last_name = $db_row[user_db::FLD_LAST_NAME];
            }
            if (array_key_exists(user_db::FLD_TERM, $db_row)) {
                if ($db_row[user_db::FLD_TERM] != null) {
                    $trm = new term($this);
                    $trm->set_id($db_row[user_db::FLD_TERM]);
                    $this->trm = $trm;
                }
            }
            if (array_key_exists(user_db::FLD_SOURCE, $db_row)) {
                if ($db_row[user_db::FLD_SOURCE] != null) {
                    $src = new source($this);
                    $src->set_id($db_row[user_db::FLD_SOURCE]);
                    $this->source = $src;
                }
            }
            $this->profile_id = $db_row[user_db::FLD_PROFILE];
            $this->dec_point = DEFAULT_DEC_POINT;
            $this->thousand_sep = DEFAULT_THOUSAND_SEP;
            $this->percent_decimals = DEFAULT_PERCENT_DECIMALS;
            if (array_key_exists(user_db::FLD_ACTIVATION_KEY, $db_row)) {
                $this->activation_key = $db_row[user_db::FLD_ACTIVATION_KEY];
            }
            if (array_key_exists(user_db::FLD_ACTIVATION_TIMEOUT, $db_row)) {
                $this->activation_timeout = $db_row[user_db::FLD_ACTIVATION_TIMEOUT];
            }
            if (array_key_exists(user_db::FLD_DB_NOW, $db_row)) {
                $this->db_now = $db_row[user_db::FLD_DB_NOW];
            }
            $result = true;
            log_debug($this->name, $debug - 25);
        }
        return $result;
    }

    // TODO test api_mapper

    // TODO add the import mapper

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
        $vars = $this->api_json_array_core($typ_lst, $usr);
        if ($this->description != null) {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }
        if ($this->profile_id > 0) {
            $vars[json_fields::PROFILE_ID] = $this->profile_id;
        }
        if ($this->email != null) {
            $vars[json_fields::EMAIL] = $this->email;
        }
        if ($this->first_name != null) {
            $vars[json_fields::FIRST_NAME] = $this->first_name;
        }
        if ($this->first_name != null) {
            $vars[json_fields::LAST_NAME] = $this->last_name;
        }

        return $vars;
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
     * set and get
     */

    /**
     * set the most often used reference vars with one set statement
     * @param int $id mainly for test creation the database id of the reference
     */
    function set(int $id = 0, string $name = '', string $email = '', string $code_id = ''): void
    {
        $this->set_id($id);
        $this->set_name($name);
        $this->set_email($email);
    }

    /**
     * @param string|null $name the unique username for this pod
     */
    function set_name(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string|null $email the unique email for the user
     */
    function set_email(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string the unique username for the user on this pod
     */
    function name(): string
    {
        if ($this->name == null) {
            return $this->email;
        } else {
            return $this->name;
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

    /**
     * @return string|null the unique email for this user
     */
    function email(): ?string
    {
        return $this->email;
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
        return $this->source?->id();
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
     * load one user by name
     * @param string $email the email of the user
     * @return bool true if a user has been found
     */
    function load_by_email(string $email): bool
    {
        global $db_con;

        log_debug($email);
        $qp = $this->load_sql_by_email($db_con->sql_creator(), $email);
        return $this->load($qp);
    }

    /**
     * load one user by name or email
     * @param string $name the username of the user
     * @param string $email the email of the user
     * @return bool true if a user has been found
     */
    function load_by_name_or_email(string $name, string $email): bool
    {
        global $db_con;

        log_debug($email);
        $qp = $this->load_sql_by_name_or_email($db_con, $name, $email);
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

    function load_by_profile_code(string $profile_code_id): bool
    {
        global $usr_pro_cac;
        if ($usr_pro_cac != null) {
            return $this->load_by_profile($usr_pro_cac->id($profile_code_id));
        } else {
            return false;
        }
    }

    /**
     * load a user from the database view
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row);
        return $this->id();
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
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);

        $sc->set_class($class);
        $sc->set_name($qp->name);

        if ($this->viewer == null) {
            if ($this->id() == null) {
                $sc->set_usr(0);
            } else {
                $sc->set_usr($this->id());
            }
        } else {
            $sc->set_usr($this->viewer->id());
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
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
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
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
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
     * create an SQL statement to retrieve a user by email from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $email the email of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
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
     * @param string $name the name of the user
     * @param string $email the email of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name_or_email(sql_creator $sc, string $name, string $email, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, 'name_or_email', $class);
        $sc->add_where(user_db::FLD_NAME, $name, sql_par_type::TEXT_OR);
        $sc->add_where(user_db::FLD_EMAIL, $email, sql_par_type::TEXT_OR);
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
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
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
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
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
     * load the user specific data that is not supposed to be changed very rarely user
     * so if changed all data is reloaded once
     */
    function load_usr_data(): void
    {
        global $db_con;
        global $vrb_cac;
        global $sys_msk_cac;

        $vrb_cac = new verb_list($this);
        $vrb_cac->load($db_con);

        $sys_msk_cac = new view_sys_list($this);
        $sys_msk_cac->load($db_con);

    }

    function has_any_user_this_profile(string $profile_code_id): bool
    {
        return $this->load_by_profile_code($profile_code_id);
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
            $this->set_id(0); // switch off the permission
        }
        return $test_result->all_message_text();
    }

    /**
     * @return string the ip address of the active user
     */
    private function get_ip(): string
    {
        if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $this->ip_addr = $_SERVER['REMOTE_ADDR'];
        }
        if ($this->ip_addr == null) {
            $this->ip_addr = users::SYSTEM_LOCAL_IP;
        }
        return $this->ip_addr;
    }

    /**
     * TODO return a translatable msg_id instead of a string
     * @returns string the active session user object
     */
    function get(): string
    {
        global $debug;

        $result = ''; // for the result message e.g. if the user is blocked

        // test first if the IP is blocked
        if ($this->ip_addr == '') {
            $this->get_ip();
        } else {
            log_debug(' (' . $this->ip_addr . ')', $debug - 1);
        }
        // even if the user has an open session, but the ip is blocked, drop the user
        $result .= $this->ip_check($this->ip_addr);

        if ($result == '') {
            // if the user has logged in use the logged in account
            if (isset($_SESSION['logged'])) {
                if ($_SESSION['logged']) {
                    $this->load_by_id($_SESSION['usr_id']);
                    log_debug('use (' . $this->id() . ')');
                }
            } else {
                // else use the IP address (for testing don't overwrite any testing ip)
                global $usr_pro_cac;
                global $db_con;

                $this->load_by_ip($this->get_ip());
                if ($this->id() <= 0) {
                    // use the ip address as the username and add the user
                    $this->name = $this->get_ip();

                    // allow to fill the database only if a local user has logged in
                    if ($this->name == users::SYSTEM_LOCAL_IP) {
                        // add the local admin user to use it for the import
                        $upd_result = $this->create_local_admin($db_con);
                    } else {
                        $upd_result = $this->save_old($db_con);
                    }

                    // TODO make sure that the result is always compatible and checked if needed
                    // adding a new user automatically is normal, so the result does not need to be shown to the user
                    if (str_replace('1', '', $upd_result) <> '') {
                        $result = $upd_result;
                    }
                }
            }
        }
        log_debug(' "' . $this->name . '" (' . $this->id() . ')');
        return $result;
    }

    function create_local_admin(sql_db $db_con): string
    {
        // create the local admin users but only if there are no other admins
        $check_usr = new user();
        if (!$check_usr->has_any_user_this_profile(user_profiles::ADMIN)) {
            $this->set_name(users::LOCALHOST_NAME);
            $this->ip_addr = users::LOCALHOST_IP;
            $this->set_profile(user_profiles::ADMIN);
        }

        // add the local admin user to use it for the import
        return $this->save_old($db_con);

    }


    /*
     * owner and access
     */

    /**
     * true if the login user is in general allowed to insert anything in this user
     *
     * @return bool true if the logged-in user is the user itself or an admin
     */
    function can_add(): bool
    {
        global $usr;

        $can_add = false;

        // if the user who wants to change it, is the owner, he can do it
        // or if the owner is not set, he can do it (and the owner should be set, because every object should have an owner)
        if ($usr->is_admin() or $usr->is_system()) {
            $can_add = true;
            log_info('user ' . $this->dsp_id() . ' is change by admin user ' . $usr->dsp_id());
        } elseif ($usr->is_normal()) {
            $can_add = true;
            log_info('user ' . $this->dsp_id() . ' is added by user ' . $usr->dsp_id());
        } else {
            log_warning('privileged user ' . $usr->dsp_id() . ' has requested to added by non admin user ' . $this->dsp_id() . ' without permission');
        }

        return $can_add;
    }

    /**
     * true if the login user is in general allowed to change anything in this user
     *
     * @return bool true if the logged-in user is the user itself or an admin
     */
    function can_change(): bool
    {
        global $usr;

        $can_change = false;

        // if the user who wants to change it, is the owner, he can do it
        // or if the owner is not set, he can do it (and the owner should be set, because every object should have an owner)
        if ($this->id() == $usr->id()) {
            $can_change = true;
        } elseif ($usr->is_admin() or $usr->is_system()) {
            $can_change = true;
            log_info('user ' . $this->dsp_id() . ' is change by admin user ' . $usr->dsp_id());
        } else {
            log_warning('user ' . $usr->dsp_id() . ' has requested to change by user ' . $this->dsp_id() . ' without permission');
        }

        return $can_change;
    }


    /*
     * im- and export
     */

    /**
     * import a user from a json data user object
     *
     * @param array $json_obj an array with the data of the json object
     * @param int $profile_id the profile of the user how has initiated the import mainly used to prevent any user to gain additional rights
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, int $profile_id, object $test_obj = null): user_message
    {
        global $usr_pro_cac;

        log_debug();
        $usr_msg = parent::import_db_obj($this, $test_obj);

        // reset all parameters of this user object
        $this->reset();
        foreach ($json_obj as $key => $value) {
            if ($key == json_fields::NAME) {
                $this->name = $value;
            }
            if ($key == json_fields::DESCRIPTION) {
                $this->description = $value;
            }
            if ($key == user_db::FLD_EMAIL) {
                $this->email = $value;
            }
            if ($key == user_db::FLD_FIRST_NAME) {
                $this->first_name = $value;
            }
            if ($key == user_db::FLD_LAST_NAME) {
                $this->last_name = $value;
            }
            if ($key == user_db::FLD_CODE_ID) {
                $this->code_id = $value;
            }
            if ($key == json_fields::PROFILE) {
                $this->profile_id = $usr_pro_cac->id($value);
            }
            if ($key == json_fields::CODE_ID) {
                if ($profile_id == $usr_pro_cac->id(user_profiles::ADMIN)
                    or $profile_id == $usr_pro_cac->id(user_profiles::SYSTEM)) {
                    $this->code_id = $value;
                }
            }
        }

        // save the user in the database
        if (!$test_obj) {
            if ($usr_msg->is_ok()) {
                // check the importing profile and make sure that gaining additional privileges is impossible
                // the user profiles must always be in the order that the lower ID has same or less rights
                // TODO use the right level of the profile
                if ($profile_id <= $this->profile_id) {
                    global $db_con;
                    $usr_msg->add_message_text($this->save_old($db_con));
                }
            }
        }


        return $usr_msg;
    }

    /**
     * create an array with the export json fields
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(bool $do_load = true): array
    {
        $vars = [];

        $vars[json_fields::NAME] = $this->name;
        if ($this->description <> '') {
            $vars[json_fields::DESCRIPTION] = $this->description;
        }
        if ($this->email <> '') {
            $vars[json_fields::EMAIL] = $this->email;
        }
        if ($this->first_name <> '') {
            $vars[json_fields::FIRST_NAME] = $this->first_name;
        }
        if ($this->last_name <> '') {
            $vars[json_fields::LAST_NAME] = $this->last_name;
        }
        if ($this->code_id <> '') {
            $vars[json_fields::CODE_ID] = $this->code_id;
        }
        if ($this->is_profile_valid()) {
            $vars[json_fields::PROFILE] = $this->profile_code_id();
        }

        return $vars;
    }


    /*
     * information
     */

    /**
     * @returns bool true if the user is valid
     */
    function is_set(): bool
    {
        if ($this->id() > 0) {
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
        global $usr_pro_cac;

        $result = '';
        if ($this->is_profile_valid()) {
            $result = $usr_pro_cac->code_id($this->profile_id);
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
     * @returns bool true if the user has admin rights
     */
    function is_admin(): bool
    {
        global $usr_pro_cac;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $usr_pro_cac->id(user_profiles::ADMIN)) {
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
        global $usr_pro_cac;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $usr_pro_cac->id(user_profiles::TEST)
                or $this->profile_id == $usr_pro_cac->id(user_profiles::SYSTEM)) {
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
        global $usr_pro_cac;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $usr_pro_cac->id(user_profiles::NORMAL)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool false if the profile is not set or is not found
     */
    private function is_profile_valid(): bool
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
        global $usr_pro_cac;
        log_debug();
        $result = false;

        if ($this->profile_id == $usr_pro_cac->id(user_profiles::ADMIN)
            or $this->profile_id == $usr_pro_cac->id(user_profiles::TEST)
            or $this->profile_id == $usr_pro_cac->id(user_profiles::SYSTEM)) {
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
        $this->set_id(0);
        $this->code_id = 'all';
        $this->name = 'standard user view for all users';
    }

    // create the HTML code to display the username with the HTML link
    function display(): string
    {
        return '<a href="/http/user.php?id=' . $this->id() . '">' . $this->name . '</a>';
    }

    // remember the last source that the user has used
    function set_source($source_id): bool
    {
        log_debug('(' . $this->id() . ',s' . $source_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id();
        $db_con->set_class(user::class);
        return $db_con->update_old($this->id(), 'source_id', $source_id);
    }

    // remember the last source that the user has used
    // TODO add the database field
    function set_verb($vrb_id): bool
    {
        log_debug('(' . $this->id() . ',s' . $vrb_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id();
        $result = $db_con->set_class(user::class);
        //$result = $db_con->update($this->id(), verb::FLD_ID, $vrb_id);
        return $result;
    }

    function set_profile(string $profile_code_id): void
    {
        global $usr_pro_cac;
        $this->profile_id = $usr_pro_cac->id($profile_code_id);
        //$this->profile = $usr_pro_cac->lst[$this->profile_id];
    }

    /**
     * set the user profile id directly which is hopefully only used once to set the profile of the system user
     * @param int $profile_id the id of the user profile
     * @return void
     */
    function set_profile_id(int $profile_id): void
    {
        $this->profile_id = $profile_id;
    }

    // set the main log entry parameters for updating one word field
    private function log_upd(): change
    {
        log_debug(' user ' . $this->name);
        $log = new change($this);
        $log->set_action(change_actions::UPDATE);
        $log->set_table(change_tables::USER);

        return $log;
    }

    /**
     * check and update a single user parameter
     *
     * TODO check if name and email are unique and do the check within one transaction closed by a commit
     */
    private function upd_par(sql_db $db_con, array $usr_par, string $db_value, string $fld_name, string $par_name): void
    {
        $result = '';
        if ($usr_par[$par_name] <> $db_value
            and $usr_par[$par_name] <> '') {
            $log = $this->log_upd();
            $log->old_value = $db_value;
            $log->new_value = $usr_par[$par_name];
            $log->row_id = $this->id();
            $log->set_field($fld_name);
            if ($log->add()) {
                $db_con->set_class(user::class);
                $result = $db_con->update_old($this->id(), $log->field(), $log->new_value);
            }
        }
    }

    /**
     * check and update all user parameters
     *
     * @param array $usr_par the array of parameters as received with the URL
     */
    function upd_pars(array $usr_par): string
    {
        log_debug();

        global $db_con;

        $result = ''; // reset the html code var

        // build the database object because the is anyway needed
        $db_con->usr_id = $this->id();
        $db_con->set_class(user::class);

        $db_usr = new user;
        $db_id = $db_usr->load_by_id($this->id());
        log_debug('database user loaded "' . $db_id . '"');

        $this->upd_par($db_con, $usr_par, $db_usr->name, user_db::FLD_NAME, 'name');
        $this->upd_par($db_con, $usr_par, $db_usr->email, user_db::FLD_EMAIL, 'email');
        $this->upd_par($db_con, $usr_par, $db_usr->first_name, user_db::FLD_FIRST_NAME, 'fname');
        $this->upd_par($db_con, $usr_par, $db_usr->last_name, user_db::FLD_LAST_NAME, 'lname');

        log_debug('done');
        return $result;
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
     * create a new user or update the existing
     * TODO use prepare SQL statements
     * TODO return a user_message not a string
     * TODO check if the user name or email exist before adding a new user
     * @return string an empty string if all user data are saved in the database otherwise the message that should be shown to the user
     */
    function save_old(sql_db $db_con): string
    {
        global $usr_pro_cac;

        $result = '';


        // build the database object because the is anyway needed
        // TODO review
        //$db_con = new mysql;
        $db_con->usr_id = $this->id();
        $db_con->set_class(user::class);

        if ($this->name() != '' and $this->name != null) {
            $this->load_by_name($this->name());
        }
        if ($this->id() <= 0) {
            if ($this->email() != '' and $this->email() != null) {
                $this->load_by_email($this->email());
            }
        }

        if ($this->id() <= 0) {
            log_debug(' add (' . $this->name . ')');

            if ($this->name != '' and $this->name != null) {
                $this->set_id($db_con->insert_old('user_name', $this->name));
            }
            // TODO log the changes???
        } else {
            // update the ip address and log the changes????
            log_warning(' method for ip update missing', 'user->save', 'method for ip update missing', (new Exception)->getTraceAsString(), $this);
        }

        // update the user
        if ($this->id() > 0) {
            // add the description of the user
            if (!$db_con->update_old($this->id(), sandbox_named::FLD_DESCRIPTION, $this->description)) {
                $result = 'Saving of user description ' . $this->id() . ' failed.';
            }
            // add the email of the user
            if (!$db_con->update_old($this->id(), user_db::FLD_EMAIL, $this->email)) {
                $result = 'Saving of user email ' . $this->id() . ' failed.';
            }
            // add the first name of the user
            if (!$db_con->update_old($this->id(), user_db::FLD_FIRST_NAME, $this->first_name)) {
                $result = 'Saving of user first name ' . $this->id() . ' failed.';
            }
            // add the last name of the user
            if (!$db_con->update_old($this->id(), user_db::FLD_LAST_NAME, $this->last_name)) {
                $result = 'Saving of user last name ' . $this->id() . ' failed.';
            }
            // add the code of the user
            if ($this->code_id != '') {
                if (!$db_con->update_old($this->id(), user_db::FLD_CODE_ID, $this->code_id)) {
                    $result = 'Saving of user code id ' . $this->id() . ' failed.';
                }
            }
            // add the profile of the user
            if (!$db_con->update_old($this->id(), user_db::FLD_PROFILE, $this->profile_id)) {
                $result = 'Saving of user profile ' . $this->id() . ' failed.';
            }
            // add the ip address to the user, but never for system users
            if ($this->profile_id != $usr_pro_cac->id(user_profiles::SYSTEM)
                and $this->profile_id != $usr_pro_cac->id(user_profiles::TEST)) {
                $ip = $this->get_ip();
                // write the localhost ip only for the local system admin user
                if ($ip == users::LOCALHOST_IP and $this->name() != users::SYSTEM_ADMIN_NAME) {
                    $ip = '';
                }
                if ($ip != '') {
                    if (!$db_con->update_old($this->id(), user_db::FLD_IP_ADDR, $this->get_ip())) {
                        $result = 'Saving of user ' . $this->id() . ' failed.';
                    }
                }
            }
            log_debug(' add ... done');
        } else {
            log_debug(' add ... failed');
        }

        return $result;
    }

    /**
     * check if a preserver username is trying to be added
     * and if return a message to the user to suggest another name
     *
     * @return user_message
     */
    protected function check_preserved(): user_message
    {
        global $usr;
        global $mtr;

        // init
        $usr_msg = new user_message();
        $msg_res = $mtr->txt(msg_id::IS_RESERVED);
        $msg_for = $mtr->txt(msg_id::RESERVED_NAME);
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);

        // system users are always allowed to add users e.g. to add the system users
        if (!$usr->is_system()) {
            if (in_array($this->name(), users::RESERVED_NAMES)) {
                // the admin user needs to add the read test objects during initial load
                if ($usr->is_admin() and !in_array($this->name(), users::FIXED_NAMES)) {
                    $usr_msg->add_id_with_vars(msg_id::USER_IS_RESERVED, [
                        msg_id::VAR_USER_NAME => $this->name(),
                        msg_id::VAR_NAME_LIST => implode(',', users::RESERVED_NAMES)
                    ]);
                }
            }
        }
        return $usr_msg;
    }


    /*
     * save
     */

    /**
     * add or update a user in the database
     *
     * @return user_message the message that should be shown to the user in case something went wrong
     *                      or the database id of the user just added
     */
    function save(): user_message
    {
        // all potential time intensive function should start with a log message to detect time improvement potential
        log_debug($this->dsp_id());

        // get the user that is logged in and is requesting the changes
        global $usr;
        // use the already open database connection of the already started process
        global $db_con;

        // configure the global database connection object for the select, insert, update and delete queries
        $db_con->set_class($this::class);
        $db_con->set_usr($usr->id());

        // check the preserved names
        $usr_msg = $this->check_preserved();

        // check if a user with the same name or email already exists
        if ($usr_msg->is_ok()) {
            // if a new user is supposed to be added check upfront for a similar object to prevent adding duplicates
            if ($this->id() == 0) {
                log_debug('check possible duplicates before adding ' . $this->dsp_id());
                $similar = $this->get_similar();
                if ($similar->id() <> 0) {
                    // check that the get_similar function has really found a similar object and report potential program errors
                    if (!$this->is_similar($similar)) {
                        $usr_msg->add_id_with_vars(msg_id::NOT_SIMILAR_OBJECTS, [
                            msg_id::VAR_NAME => $this->dsp_id(),
                            msg_id::VAR_NAME_CHK => $similar->dsp_id()
                        ]);
                    } else {
                        // if similar is found set the id to trigger the updating instead of adding
                        $similar->load_by_id($similar->id()); // e.g. to get the type_id
                    }
                } else {
                    $similar = null;
                }
            }
        }

        // create or update
        if ($usr_msg->is_ok()) {
            if ($this->id() == 0) {

                // create a user if no similar user has been found
                log_debug('add ' . $this->dsp_id());
                $usr_msg->add($this->db_insert($db_con));

            } else {

                // update the user
                log_debug('update');

                // read the database parameter of the user as of now
                $db_rec = clone $this;
                $db_rec->reset();
                if ($db_rec->load_by_id($this->id()) != $this->id()) {
                    $lib = new library();
                    $usr_msg->add_id_with_vars(msg_id::FAILED_RELOAD_CLASS, [
                        msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
                    ]);
                } else {
                    $usr_msg->add($this->db_update($db_con, $db_rec));
                }
            }
        }

        return $usr_msg;
    }


    /*
     * similar
     */

    /**
     * check if a user with the name or email already exists
     * @return user a filled object that has the same name
     *                 or a sandbox object with id() = 0 if nothing similar has been found
     */
    function get_similar(): user
    {
        $result = new user();
        $result->load_by_name_or_email($this->name(), $this->email());
        return $result;
    }

    /**
     * check if all unique key of the given user matches with this
     * and there it can be assumed that the users are the same
     * @return bool true if all unique keys match
     */
    function is_similar(user $similar): bool
    {
        $result = true;
        if ($this->name() != null and $similar->name() != null) {
            if ($this->name() != $similar->name()) {
                $result = false;
            }
        }
        if ($this->email() != null and $similar->email() != null) {
            if ($this->email() != $similar->email()) {
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
     * @return user_message with status ok
     *                      or if something went wrong
     *                      the message that should be shown to the user
     *                      including suggested solutions
     */
    private function db_insert(sql_db $db_con): user_message
    {
        log_debug($this->dsp_id());

        global $usr;

        // always return a user message and if everything is fine, it is just empty
        $usr_msg = new user_message();

        if ($this->can_add()) {
            // the sql creator is used more than once, so create it upfront
            $sc = $db_con->sql_creator();
            $qp = $this->sql_insert($sc, new sql_type_list([sql_type::LOG]));
            $ins_msg = $db_con->insert($qp, 'add and log ' . $this->dsp_id());
            if ($ins_msg->is_ok()) {
                $this->set_id($ins_msg->get_row_id());
            }
            $usr_msg->add($ins_msg);
        } else {
            $usr_msg->add_id_with_vars(msg_id::USER_NO_ADD_PRIVILEGES, [
                msg_id::VAR_USER_NAME => $this->name(),
                msg_id::VAR_USER_PROFILE => $usr->name_and_profile()
            ]);
        }

        return $usr_msg;
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
     * @return user_message with the description of any problems for the user and the suggested solution
     */
    private function db_update(sql_db $db_con, user $db_usr): user_message
    {
        log_debug($this->dsp_id());

        global $usr;

        // always return a user message and if everything is fine, it is just empty
        $usr_msg = new user_message();

        if ($this->can_change()) {
            // the sql creator is used more than once, so create it upfront
            $sc = $db_con->sql_creator();

            $qp = $this->sql_update($sc, $db_usr, new sql_type_list([sql_type::LOG]));
            $upd_msg = $db_con->update($qp, 'update and log ' . $this->dsp_id());
            $usr_msg->add($upd_msg);

            log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        } else {
            $usr_msg->add_id_with_vars(msg_id::USER_NO_UPDATE_PRIVILEGES, [
                msg_id::VAR_USER_NAME => $this->name(),
                msg_id::VAR_USER_PROFILE => $usr->name_and_profile()
            ]);
        }

        return $usr_msg;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to insert a user in the database
     * always all fields are included in the query to be able to remove overwrites with a null value
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        global $usr;

        // set some var names to shorten the code lines
        $var_name_row_id = $sc->var_name_row_id($sc_par_lst);

        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        // fields and values that the word has additional to the standard named user sandbox object
        $sbx_empty = $this->clone_reset();
        // get the list of the changed fields
        $fvt_lst = $this->db_fields_changed($sbx_empty, $sc_par_lst_used);
        // get the list of all fields that can be changed by the user
        $fld_lst_all = $this->db_fields_all();

        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all);

        // TODO check if the prepared function already exists and if yes, skip the query recreation

        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst_used, $ext);

        // log functions must always use named parameters
        $sc_par_lst_used->add(sql_type::NAMED_PAR);

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();

        // add the change action field to the field list for the log entries
        global $cng_act_cac;
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $cng_act_cac->id(change_actions::ADD),
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
        $qp_id = $this->sql_insert_key_field($sc, $qp_id, $fvt_lst, $id_fld_new, $sc_par_lst_sub);
        $par_lst_out->add($qp_id->par_fld);
        $sql .= $qp_id->sql;

        // get the data fields and move the unique db key field to the first entry
        $fld_lst_ex_log = array_intersect($fvt_lst->names(), $fld_lst_all);

        $key_fld_pos = array_search(user_db::FLD_NAME, $fld_lst_ex_log);
        unset($fld_lst_ex_log[$key_fld_pos]);
        $fld_lst_log = array_merge([$qp_id->par_fld->name], $fld_lst_ex_log);

        // create the query parameters for the log entries for the single fields
        $qp_log = $sc->sql_func_log($this::class, $usr, $fld_lst_log, $fvt_lst, $sc_par_lst_log);
        $sql .= ' ' . $qp_log->sql;
        $par_lst_out->add_list($qp_log->par_fld_lst);

        if (!$sc_par_lst_used->is_call_only()) {

            // update the fields excluding the unique id
            $update_fvt_lst = new sql_par_field_list();
            foreach ($fld_lst_ex_log as $fld) {
                $update_fvt_lst->add($fvt_lst->get($fld));
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
     * @param user $db_row the sandbox object with the database values before the update
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_update(
        sql_creator   $sc,
        user          $db_row,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        global $usr;

        // clone the parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::UPDATE);
        // update does not need to return an id
        $sc_par_lst_used->add(sql_type::NO_ID_RETURN);
        // log for users is always on and log functions must always use named parameters
        $sc_par_lst_used->add(sql_type::NAMED_PAR);
        // get the field names, values and parameter types that have been changed
        // and that needs to be updated in the database
        // the db_* child function call the corresponding parent function
        // including the sql parameters for logging
        $fvt_lst = $this->db_fields_changed($db_row, $sc_par_lst_used);
        // get the list of all fields that can be changed by the user
        $fld_lst_all = $this->db_fields_all();

        // create either the prepared sql query or a sql function that includes the logging of the changes
        // unlike the db_* function the sql_update_* parent function is called directly
        //return $this::sql_update_switch($sc, $fld_lst, $all_fields, $sc_par_lst_used);

        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all);

        // TODO check if the prepared function already exists and if yes, skip the query recreation

        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst_used, $ext);

        if (!$fvt_lst->is_empty_except_internal_fields()) {

            // set some var names to shorten the code lines
            $id_fld = user_db::FLD_ID;
            $id_val = '_' . $id_fld;

            // add the change action field to the field list for the log entries
            global $cng_act_cac;
            $fvt_lst->add_field(
                change_action::FLD_ID,
                $cng_act_cac->id(change_actions::UPDATE),
                type_object::FLD_ID_SQL_TYP
            );

            // list of parameters actually used in order of the function usage
            $par_lst_out = new sql_par_field_list();

            // init the function body
            $sql = $sc->sql_func_start('', $sc_par_lst_used);

            // don't use the log parameter for the sub queries
            $sc_par_lst_sub = $sc_par_lst_used->remove(sql_type::LOG);
            $sc_par_lst_sub->add(sql_type::LIST);
            $sc_par_lst_log = clone $sc_par_lst_sub;
            $sc_par_lst_log->add(sql_type::UPDATE_PART);

            // get the fields actually changed
            $fld_lst = $fvt_lst->names();
            $fld_lst_chg = array_intersect($fld_lst, $fld_lst_all);

            // add the row id
            $fvt_lst->add_field(
                $sc->id_field_name(),
                $this->id(),
                db_object_seq_id::FLD_ID_SQL_TYP);

            // create the query parameters for the log entries for the single fields
            $qp_log = $sc->sql_func_log_update($this::class, $usr, $fld_lst_chg, $fvt_lst, $sc_par_lst_log, $this->id());
            $sql .= ' ' . $qp_log->sql;
            $par_lst_out->add_list($qp_log->par_fld_lst);

            // update the fields excluding the unique id
            $update_fvt_lst = new sql_par_field_list();
            foreach ($fld_lst_chg as $fld) {
                $update_fvt_lst->add($fvt_lst->get($fld));
            }
            $sc_update = clone $sc;
            $sc_par_lst_upd = new sql_type_list([sql_type::NAMED_PAR, sql_type::UPDATE, sql_type::UPDATE_PART]);
            $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
            $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);
            $qp_update->sql = $sc_update->create_sql_update(
                $id_fld, $id_val, $update_fvt_lst, [], $sc_par_lst_upd, true, '', $id_fld);
            // add the insert row to the function body
            $sql .= ' ' . $qp_update->sql . ' ';

            $sql .= $sc->sql_func_end();

            // create the query parameters for the actual change
            $qp_chg = clone $qp;
            $qp_chg->sql = $sc->create_sql_update(
                $id_fld, $id_val, $par_lst_out, [], $sc_par_lst_used);
            $qp_chg->par = $fvt_lst->values();

            // merge all together and create the function
            $qp->sql = $qp_chg->sql . $sql . ';';
            $qp->par = $par_lst_out->values();

            // create the call sql statement
            return $sc->sql_call($qp, $qp_chg->name, $par_lst_out);
        } else {
            return $qp;
        }
    }

    /**
     * the common part of the sql_insert and sql_update functions
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the query name extension to differ the queries based on the fields changed
     * @return sql_par prepared sql parameter object with the name set
     */
    protected function sql_common(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list(),
        string        $ext = ''): sql_par
    {
        $qp = new sql_par($this::class, $sc_par_lst, $ext);

        // update the sql creator settings
        $sc->set_class($this::class, $sc_par_lst);
        $sc->set_name($qp->name);

        return $qp;
    }

    /**
     * create the sql statement to add a new user to the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par $qp
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param string $id_fld_new
     * @param sql_type_list $sc_par_lst_sub the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert_key_field(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        string             $id_fld_new,
        sql_type_list      $sc_par_lst_sub = new sql_type_list()
    ): sql_par
    {
        // set some var names to shorten the code lines
        $ext = sql::NAME_SEP . sql_creator::FILE_INSERT;

        // list of parameters actually used in order of the function usage
        $sql = '';
        $fvt_insert = $fvt_lst->get(user_db::FLD_NAME);

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
    function db_fields_all(): array
    {
        return user_db::FLD_NAMES;
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param user $db_usr the compare value to detect the changed fields
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        user          $db_usr,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $lst = new sql_par_field_list();
        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        // the user database fields in order of user_db::FLD_NAMES and user_db::FLD_LST_ALL

        // the username must be unique
        if ($db_usr->name_or_null() <> $this->name()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_NAME,
                    $cng_fld_cac->id($table_id . user_db::FLD_NAME),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_NAME,
                $this->name(),
                sandbox_named::FLD_NAME_SQL_TYP,
                $db_usr->name_or_null()
            );
        }

        // the ip address should always be included
        if ($db_usr->ip_addr <> $this->ip_addr) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_IP_ADDR,
                    $cng_fld_cac->id($table_id . user_db::FLD_IP_ADDR),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_IP_ADDR,
                $this->ip_addr,
                sql_field_type::CODE_ID,
                $db_usr->ip_addr
            );
        }

        // the password is not part of the standard update process

        // the description is mainly used for system users
        if ($db_usr->description <> $this->description) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sandbox_named::FLD_DESCRIPTION,
                    $cng_fld_cac->id($table_id . sandbox_named::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sandbox_named::FLD_DESCRIPTION,
                $this->description,
                sandbox_named::FLD_DESCRIPTION_SQL_TYP,
                $db_usr->description
            );
        }

        // the code_id to select users with predefined assigned functionality that can change their username
        if ($db_usr->code_id <> $this->code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_CODE_ID,
                    $cng_fld_cac->id($table_id . user_db::FLD_CODE_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_CODE_ID,
                $this->code_id,
                sql_field_type::CODE_ID,
                $db_usr->code_id
            );
        }

        // TODO a profile with more permissions can only be set by a user that has the permission to do so
        if ($db_usr->profile_id() <> $this->profile_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_PROFILE,
                    $cng_fld_cac->id($table_id . user_db::FLD_PROFILE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $usr_pro_cac;
            $lst->add_type_field(
                user_db::FLD_PROFILE,
                type_object::FLD_NAME,
                $this->profile_id(),
                $db_usr->profile_id(),
                $usr_pro_cac);
        }

        /* TODO the confirmation levels should created and be added
        // the confirmation type should only be changed by the system based on the confirmation process
        if ($db_usr->type_id  <> $this->type_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_TYPE_ID,
                    $cng_fld_cac->id($table_id . user_db::FLD_TYPE_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $usr_cfm_cac;
            $lst->add_type_field(
                user_db::FLD_TYPE_ID,
                type_object::FLD_NAME,
                $this->profile_code_id(),
                $db_usr->profile_code_id(),
                $usr_cfm_cac);
        }
        */

        // TODO add user_db::FLD_LEVEL

        // the is used as the name if no name is given
        if ($db_usr->email <> $this->email) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_EMAIL,
                    $cng_fld_cac->id($table_id . user_db::FLD_EMAIL),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_EMAIL,
                $this->email,
                sql_field_type::CODE_ID,
                $db_usr->email
            );
        }

        // in may be useful to move the name and other non-critical user parameters to a value_list
        if ($db_usr->first_name <> $this->first_name) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_FIRST_NAME,
                    $cng_fld_cac->id($table_id . user_db::FLD_FIRST_NAME),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_FIRST_NAME,
                $this->first_name,
                sql_field_type::NAME,
                $db_usr->first_name
            );
        }

        // in may be useful to move the last name and other non-critical user parameters to a value_list
        if ($db_usr->last_name <> $this->last_name) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_LAST_NAME,
                    $cng_fld_cac->id($table_id . user_db::FLD_LAST_NAME),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                user_db::FLD_LAST_NAME,
                $this->last_name,
                sql_field_type::NAME,
                $db_usr->last_name
            );
        }

        // for the last used term additional the name is written to the log
        if ($db_usr->term_id() <> $this->term_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_TERM,
                    $cng_fld_cac->id($table_id . user_db::FLD_TERM),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                user_db::FLD_TERM,
                term::FLD_NAME,
                $this->trm,
                $db_usr->trm
            );
        }

        // for the source id additional the source name is written to the log
        if ($db_usr->source_id() <> $this->source_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_SOURCE,
                    $cng_fld_cac->id($table_id . user_db::FLD_SOURCE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                user_db::FLD_SOURCE,
                source::FLD_NAME,
                $this->source,
                $db_usr->source
            );
        }

        // the activation_key is used during the signup process and is not logged
        if ($db_usr->activation_key <> $this->activation_key) {
            $lst->add_field(
                user_db::FLD_ACTIVATION_KEY,
                $this->activation_key,
                sql_field_type::NAME,
                $db_usr->activation_key
            );
        }

        // the activation_timeout is used during the signup process and is not logged
        if ($db_usr->activation_timeout <> $this->activation_timeout) {
            $lst->add_field(
                user_db::FLD_ACTIVATION_TIMEOUT,
                $this->activation_timeout,
                sql_field_type::TIME,
                $db_usr->activation_timeout
            );
        }

        return $lst;
    }


    /*
     * debug
     */

    function dsp_id(): string
    {
        return $this->name . ' (' . $this->id() . ')';
    }

}
