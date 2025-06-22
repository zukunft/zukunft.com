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
    - save:              manage to update the database
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
include_once DB_PATH . 'sql_par_type.php';
//include_once MODEL_IMPORT_PATH . 'import_file.php';
include_once MODEL_SYSTEM_PATH . 'ip_range_list.php';
//include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
//include_once MODEL_LOG_PATH . 'change_table_list.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
//include_once MODEL_REF_PATH . 'source.php';
//include_once MODEL_WORD_PATH . 'triple.php';
include_once MODEL_USER_PATH . 'user_profile.php';
include_once MODEL_USER_PATH . 'user_type.php';
//include_once MODEL_VERB_PATH . 'verb_list.php';
//include_once MODEL_VIEW_PATH . 'view.php';
//include_once MODEL_VIEW_PATH . 'view_sys_list.php';
//include_once MODEL_WORD_PATH . 'word.php';
include_once SHARED_HELPER_PATH . 'Config.php';
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
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\helper\db_object_seq_id;
use cfg\system\ip_range_list;
use cfg\log\change;
use cfg\sandbox\sandbox_named;
use cfg\ref\source;
use cfg\word\triple;
use cfg\verb\verb_list;
use cfg\view\view;
use cfg\view\view_sys_list;
use cfg\word\word;
use shared\const\users;
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

    // TODO move to user_db class like word_db
    // database fields and comments only used for user
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const TBL_COMMENT = 'for users including system users; only users can add data';
    const FLD_ID = 'user_id'; // also the field name for foreign keys
    const FLD_ID_SQL_TYP = sql_field_type::INT;
    // fields for the main logon
    const FLD_NAME_COM = 'the user name unique for this pod';
    const FLD_NAME = 'user_name';
    const FLD_IP_ADDR_COM = 'all users a first identified with the ip address';
    const FLD_IP_ADDR = 'ip_address';
    const FLD_PASSWORD_COM = 'the hash value of the password';
    const FLD_PASSWORD = 'password';
    // description and type
    const FLD_DESCRIPTION_COM = 'for system users the description to explain the profile to human users';
    const FLD_DESCRIPTION = 'description';
    const FLD_DESCRIPTION_SQL_TYP = sql_field_type::TEXT;
    const FLD_CODE_ID_COM = 'to select e.g. the system batch user';
    const FLD_CODE_ID = 'code_id';
    const FLD_PROFILE_COM = 'to define the user roles and read and write rights';
    const FLD_PROFILE = 'user_profile_id';
    const FLD_TYPE_ID_COM = 'to set the confirmation level of a user';
    const FLD_TYPE_ID = 'user_type_id';
    const FLD_LEVEL_COM = 'the access right level to prevent not permitted right gaining';
    const FLD_LEVEL = 'right_level';
    // online verification
    const FLD_EMAIL_COM = 'the primary email for verification';
    const FLD_EMAIL = 'email';
    const FLD_EMAIL_STATUS_COM = 'if the email has been verified or if a password reset has been send';
    const FLD_EMAIL_STATUS = 'email_status';
    const FLD_EMAIL_ALT_COM = 'an alternative email for account recovery';
    const FLD_EMAIL_ALT = 'email_alternative';
    const FLD_TWO_FACTOR_ID = 'mobile_number';
    const FLD_TWO_FACTOR_STATUS = 'mobile_status';
    const FLD_ACTIVATION_KEY = 'activation_key';
    const FLD_ACTIVATION_TIMEOUT = 'activation_timeout';
    // offline verification
    const FLD_FIRST_NAME = 'first_name';
    const FLD_LAST_NAME = 'last_name';
    const FLD_NAME_TRIPLE_COM = 'triple that contains e.g. the given name, family name, selected name or title of the person';
    const FLD_NAME_TRIPLE_ID = 'name_triple_id';
    const FLD_GEO_TRIPLE_COM = 'the post address with street, city or any other form of geo location for physical transport';
    const FLD_GEO_TRIPLE_ID = 'geo_triple_id';
    const FLD_GEO_STATUS = 'geo_status_id';
    const FLD_OFFICIAL_ID_COM = 'e.g. the number of the passport';
    const FLD_OFFICIAL_ID = 'official_id';
    const FLD_OFFICIAL_TYPE_ID = 'official_id_type';
    const FLD_OFFICIAL_ID_STATUS = 'official_id_status';
    // settings
    const FLD_TERM_COM = 'the last term that the user had used';
    const FLD_TERM = 'term_id';
    const FLD_VIEW_COM = 'the last mask that the user has used';
    const FLD_VIEW = 'view_id';
    const FLD_SOURCE_COM = 'the last source used by this user to have a default for the next value';
    const FLD_SOURCE = 'source_id';
    const FLD_STATUS_COM = 'e.g. to exclude inactive users';
    const FLD_STATUS = 'user_status_id';
    const FLD_CREATED = 'created';
    const FLD_LAST_LOGIN = 'last_login';
    const FLD_LAST_LOGOUT = 'last_logoff';


    // database fields used for the user logon process
    const FLD_DB_NOW = 'NOW() AS db_now';

    // all database field names excluding the id
    const FLD_NAMES = array(
        sql::FLD_CODE_ID,
        self::FLD_IP_ADDR,
        self::FLD_EMAIL,
        self::FLD_FIRST_NAME,
        self::FLD_LAST_NAME,
        self::FLD_TERM,
        self::FLD_SOURCE,
        self::FLD_PROFILE,
        self::FLD_ACTIVATION_KEY,
        self::FLD_ACTIVATION_TIMEOUT,
        self::FLD_DB_NOW
    );
    // the database field names excluding the id and the fields for logon
    const FLD_NAMES_LIST = array(
        sql::FLD_CODE_ID,
        self::FLD_IP_ADDR,
        self::FLD_EMAIL,
        self::FLD_FIRST_NAME,
        self::FLD_LAST_NAME,
        self::FLD_TERM,
        self::FLD_SOURCE,
        self::FLD_PROFILE
    );

    // field lists for the table creation
    const FLD_LST_ALL = array(
        // main logon
        [self::FLD_NAME, sql_field_type::NAME, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
        [self::FLD_IP_ADDR, sql_field_type::CODE_ID, sql_field_default::NULL, sql::INDEX, '', self::FLD_IP_ADDR_COM],
        [self::FLD_PASSWORD, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_PASSWORD_COM],
        // description and type
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_CODE_ID, sql_field_type::CODE_ID, sql_field_default::NULL, sql::INDEX, '', self::FLD_CODE_ID_COM],
        [self::FLD_PROFILE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user_profile::class, self::FLD_PROFILE_COM],
        [self::FLD_TYPE_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user_type::class, self::FLD_TYPE_ID_COM],
        [self::FLD_LEVEL, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_LEVEL_COM],
        // online verification
        [self::FLD_EMAIL, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_EMAIL_COM],
        [self::FLD_EMAIL_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_EMAIL_STATUS_COM],
        [self::FLD_EMAIL_ALT, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_EMAIL_ALT_COM],
        [self::FLD_TWO_FACTOR_ID, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', ''],
        [self::FLD_TWO_FACTOR_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', ''],
        [self::FLD_ACTIVATION_KEY, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
        [self::FLD_ACTIVATION_TIMEOUT, sql_field_type::TIME, sql_field_default::NULL, '', '', ''],
        // offline verification
        [self::FLD_FIRST_NAME, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
        [self::FLD_LAST_NAME, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
        [self::FLD_NAME_TRIPLE_ID, sql_field_type::INT, sql_field_default::NULL, '', triple::class, self::FLD_NAME_TRIPLE_COM, triple::FLD_ID],
        [self::FLD_GEO_TRIPLE_ID, sql_field_type::INT, sql_field_default::NULL, '', triple::class, self::FLD_GEO_TRIPLE_COM, triple::FLD_ID],
        [self::FLD_GEO_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', ''],
        [self::FLD_OFFICIAL_ID, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_OFFICIAL_ID_COM],
        [self::FLD_OFFICIAL_TYPE_ID, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', ''],
        [self::FLD_OFFICIAL_ID_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', ''],
        // settings
        [self::FLD_TERM, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_TERM_COM],
        [self::FLD_VIEW, sql_field_type::INT, sql_field_default::NULL, '', view::class, self::FLD_VIEW_COM],
        [self::FLD_SOURCE, sql_field_type::INT, sql_field_default::NULL, '', source::class, self::FLD_SOURCE_COM],
        [self::FLD_STATUS, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_STATUS_COM],
        [self::FLD_CREATED, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, '', '', ''],
        [self::FLD_LAST_LOGIN, sql_field_type::TIME, sql_field_default::NULL, '', '', ''],
        [self::FLD_LAST_LOGOUT, sql_field_type::TIME, sql_field_default::NULL, '', '', ''],
    );


    /*
     * im- and export link
     */

    // the field names used for the im- and export in the json or yaml format
    const FLD_EX_PROFILE = 'profile';


    /*
     * predefined user linked to the program code
     */

    // TODO move to a separate shared class named users
    // list of the system users that have a coded functionality as defined in src/main/resources/users.json

    // the system user that should only be used for internal processes and to log system tasks
    const SYSTEM_ID = 1;
    const SYSTEM_NAME = 'zukunft.com system';
    const SYSTEM_CODE_ID = 'system'; // unique id to select the user
    const SYSTEM_EMAIL = 'system@zukunft.com';
    const SYSTEM_LOCAL_IP = 'localhost'; // as a second line of defence to prevent remote manipulation

    // the system admin user that should only be used in a break-glass event to recover other admin users
    const SYSTEM_ADMIN_ID = 2;
    const SYSTEM_ADMIN_NAME = 'zukunft.com local admin';
    const SYSTEM_ADMIN_CODE_ID = 'admin';
    const SYSTEM_ADMIN_EMAIL = 'admin@zukunft.com';

    // the user that performs the system tests
    const SYSTEM_TEST_ID = 3;
    const SYSTEM_TEST_NAME = 'zukunft.com system test';
    const SYSTEM_TEST_EMAIL = 'test@zukunft.com';
    const SYSTEM_TEST_CODE_ID = 'test';

    // the user that acts as a partner for the system tests
    // so that multi-user behaviour can be tested
    const SYSTEM_TEST_PARTNER_ID = 4;
    const SYSTEM_TEST_PARTNER_NAME = 'zukunft.com system test partner'; // to test that the user sandbox is working e.g. that changes of the main test user has no impact of another user simulated by this test user
    const SYSTEM_TEST_PARTNER_CODE_ID = 'test_partner';
    const SYSTEM_TEST_PARTNER_EMAIL = 'test.partner@zukunft.com';

    // an admin user to test the allow of functions only allowed for administrators
    const SYSTEM_TEST_ADMIN_ID = 5;
    const SYSTEM_TEST_ADMIN_NAME = 'zukunft.com system test admin';
    const SYSTEM_TEST_ADMIN_CODE_ID = 'admin';
    const SYSTEM_TEST_ADMIN_EMAIL = 'admin@zukunft.com';

    // a normal user to test the deny of functions only allowed for administrators
    // and as a fallback owner
    const SYSTEM_TEST_NORMAL_ID = 6;
    const SYSTEM_TEST_NORMAL_NAME = 'zukunft.com system test no admin';
    const SYSTEM_TEST_NORMAL_CODE_ID = 'test_normal';
    const SYSTEM_TEST_NORMAL_EMAIL = 'support.normal@zukunft.com';

    // change right levels to prevent access level gaining
    const RIGHT_LEVEL_USER = 10;
    const RIGHT_LEVEL_ADMIN = 60;
    const RIGHT_LEVEL_DEVELOPER = 80;
    const RIGHT_LEVEL_SYSTEM_TEST = 90;
    const RIGHT_LEVEL_SYSTEM = 99;


    /*
     * object vars
     */

    // database fields
    public ?string $name = null;          // simply the username, which cannot be empty
    public ?string $description = null;   // used for system users to describe the target; can be used by users for a short introduction
    public ?string $ip_addr = null;       // simply the ip address used if no username is given
    public ?string $email = null;         //
    public ?string $first_name = null;    //
    public ?string $last_name = null;     //
    public ?string $code_id = null;       // the main id to detect system users
    // TODO move to user config e.g. by using the key word "pod-user-config"
    public ?string $dec_point = null;     // the decimal point char for this user
    public ?string $thousand_sep = null;  // the thousand separator user for this user
    public ?int $percent_decimals = null; // the number of decimals for this user
    public ?int $profile_id = null;       // id of the user profile
    public ?int $source_id = null;        // id of the last source used by the user

    // user setting parameters
    public ?int $wrd_id = null;           // id of the last word viewed by the user
    public ?int $vrb_id = null;           // id of the last verb used by the user

    // in memory only fields
    public ?word $wrd = null;             // the last word viewed by the user
    public ?user_profile $profile = null; // to define the base rights of a user which can be further restricted but not expanded
    public ?user $viewer = null;          // the user who wants to access this user
    // e.g. only admin are allowed to see other user parameters

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
        $this->source_id = null;

        $this->wrd_id = null;
        $this->vrb_id = null;

        $this->wrd = null;
        $this->viewer = null;

        $this->activation_key = '';
        $this->activation_timeout = '';
        $this->db_now = '';

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
            $this->name = $db_row[self::FLD_NAME];
            $this->ip_addr = $db_row[self::FLD_IP_ADDR];
            $this->email = $db_row[self::FLD_EMAIL];
            $this->first_name = $db_row[self::FLD_FIRST_NAME];
            $this->last_name = $db_row[self::FLD_LAST_NAME];
            $this->wrd_id = $db_row[self::FLD_TERM];
            $this->source_id = $db_row[self::FLD_SOURCE];
            $this->profile_id = $db_row[self::FLD_PROFILE];
            $this->dec_point = DEFAULT_DEC_POINT;
            $this->thousand_sep = DEFAULT_THOUSAND_SEP;
            $this->percent_decimals = DEFAULT_PERCENT_DECIMALS;
            if (array_key_exists(self::FLD_ACTIVATION_KEY, $db_row)) {
                $this->activation_key = $db_row[self::FLD_ACTIVATION_KEY];
            }
            if (array_key_exists(self::FLD_ACTIVATION_TIMEOUT, $db_row)) {
                $this->activation_timeout = $db_row[self::FLD_ACTIVATION_TIMEOUT];
            }
            if (array_key_exists(self::FLD_DB_NOW, $db_row)) {
                $this->db_now = $db_row[self::FLD_DB_NOW];
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
        return $this->name;
    }

    /**
     * @return string the unique email for this user
     */
    function email(): ?string
    {
        return $this->email;
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
        $sc->set_fields(self::FLD_NAMES);
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
        $sc->add_where(self::FLD_NAME, $name);
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
        $sc->add_where(self::FLD_EMAIL, $email);
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
        $sc->add_where(self::FLD_NAME, $name, sql_par_type::TEXT_OR);
        $sc->add_where(self::FLD_EMAIL, $email, sql_par_type::TEXT_OR);
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
        $sc->add_where(self::FLD_IP_ADDR, $ip_addr);
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
        $sc->add_where(self::FLD_PROFILE, $profile_id);
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
            $this->ip_addr = self::SYSTEM_LOCAL_IP;
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
                    if ($this->name == self::SYSTEM_LOCAL_IP) {
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
            if ($key == self::FLD_EMAIL) {
                $this->email = $value;
            }
            if ($key == self::FLD_FIRST_NAME) {
                $this->first_name = $value;
            }
            if ($key == self::FLD_LAST_NAME) {
                $this->last_name = $value;
            }
            if ($key == self::FLD_CODE_ID) {
                $this->code_id = $value;
            }
            if ($key == self::FLD_EX_PROFILE) {
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
     * @return word load the last word used by the user
     */
    function last_wrd(): word
    {
        if ($this->wrd_id <= 0) {
            $this->wrd_id = DEFAULT_WORD_ID;
        }
        $wrd = new word($this);
        $wrd->load_by_id($this->wrd_id);
        $this->wrd = $wrd;
        return $wrd;
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

        $this->upd_par($db_con, $usr_par, $db_usr->name, self::FLD_NAME, 'name');
        $this->upd_par($db_con, $usr_par, $db_usr->email, self::FLD_EMAIL, 'email');
        $this->upd_par($db_con, $usr_par, $db_usr->first_name, self::FLD_FIRST_NAME, 'fname');
        $this->upd_par($db_con, $usr_par, $db_usr->last_name, self::FLD_LAST_NAME, 'lname');

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
            if (!$db_con->update_old($this->id(), self::FLD_EMAIL, $this->email)) {
                $result = 'Saving of user email ' . $this->id() . ' failed.';
            }
            // add the first name of the user
            if (!$db_con->update_old($this->id(), self::FLD_FIRST_NAME, $this->first_name)) {
                $result = 'Saving of user first name ' . $this->id() . ' failed.';
            }
            // add the last name of the user
            if (!$db_con->update_old($this->id(), self::FLD_LAST_NAME, $this->last_name)) {
                $result = 'Saving of user last name ' . $this->id() . ' failed.';
            }
            // add the code of the user
            if ($this->code_id != '') {
                if (!$db_con->update_old($this->id(), self::FLD_CODE_ID, $this->code_id)) {
                    $result = 'Saving of user code id ' . $this->id() . ' failed.';
                }
            }
            // add the profile of the user
            if (!$db_con->update_old($this->id(), self::FLD_PROFILE, $this->profile_id)) {
                $result = 'Saving of user profile ' . $this->id() . ' failed.';
            }
            // add the ip address to the user, but never for system users
            if ($this->profile_id != $usr_pro_cac->id(user_profiles::SYSTEM)
                and $this->profile_id != $usr_pro_cac->id(user_profiles::TEST)) {
                $ip = $this->get_ip();
                // write the localhost ip only for the local system admin user
                if ($ip == users::LOCALHOST_IP AND $this->name() != users::SYSTEM_ADMIN_NAME) {
                    $ip = '';
                }
                if ($ip != '') {
                    if (!$db_con->update_old($this->id(), self::FLD_IP_ADDR, $this->get_ip())) {
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
     * check if a preserver user name is trying to be added
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

        // use the already open database connection of the already started process
        global $db_con;
        // use the preloaded message translation object
        global $mtr;

        // check the preserved names
        $usr_msg = $this->check_preserved();

        return $usr_msg;
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


    /*
     * debug
     */

    function dsp_id(): string
    {
        return $this->name . ' (' . $this->id() . ')';
    }

}
