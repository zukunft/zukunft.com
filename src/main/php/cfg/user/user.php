<?php

/*

    model/user/user.php - a person who uses zukunft.com
    -------------------

    TODO make sure that no right gain is possible
    TODO move the non functional user parameters to hidden words to be able to reuse the standard view functionality
    TODO log the access attempts to objects with a restriked access
    TODO build a process so that a user can request access to an object with restrikted access

    if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
    if a user has added 3 values and at least one is accepted by another user, he can add words and formula and he must have a valid email
    if a user has added 2 formula and both are accepted by at least one other user and no one has complained, he can change formulas and words, including linking of words
    if a user has linked a 10 words and all got accepted by one other user and no one has complained, he can request new verbs and he must have an validated address

    if a user got 10 pending word or formula discussion, he can no longer add words or formula utils the open discussions are less than 10
    if a user got 5 pending word or formula discussion, he can no longer change words or formula utils the open discussions are less than 5
    if a user got 2 pending verb discussion, he can no longer add verbs utils the open discussions are less than 2

    the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range


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

namespace cfg;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once MODEL_HELPER_PATH . 'db_object.php';
include_once MODEL_SYSTEM_PATH . 'ip_range_list.php';
include_once MODEL_USER_PATH . 'user_profile.php';
include_once SERVICE_EXPORT_PATH . 'user_exp.php';

use api\user\user as user_api;
use cfg\db\db_check;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\export\user_exp;
use cfg\export\sandbox_exp;
use cfg\log\change;
use cfg\log\change_action;
use cfg\log\change_table_list;
use cfg\user\user_profile;
use cfg\user\user_type;
use Exception;
use html\user\user as user_dsp;

class user extends db_object_seq_id
{

    /*
     * database link
     */

    // database fields and comments only used for user
    const TBL_COMMENT = 'for users including system users; only users can add data';
    const FLD_ID = 'user_id'; // also the field name for foreign keys
    // fields for the main logon
    const FLD_NAME_COM = 'the user name unique for this pod';
    const FLD_NAME = 'user_name';
    const FLD_IP_ADDR_COM = 'all users a first identified with the ip address';
    const FLD_IP_ADDR = 'ip_address';
    const FLD_PASSWORD_COM = 'the hash value of the password';
    const FLD_PASSWORD = 'password';
    // description and type
    const FLD_DESCRIPTION_COM = 'for system users the description to expain the profile to human users';
    const FLD_DESCRIPTION = 'description';
    const FLD_CODE_ID_COM = 'to select e.g. the system batch user';
    const FLD_CODE_ID = 'code_id';
    const FLD_PROFILE_COM = 'to define the user roles and read and write rights';
    const FLD_PROFILE = 'user_profile_id';
    const FLD_TYPE_ID_COM = 'to set the confirmation level of a user';
    const FLD_TYPE_ID = 'user_type_id';
    const FLD_LEVEL_COM = 'the access right level to prevent unpermitted right gaining';
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
        [self::FLD_DESCRIPTION, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
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

    // list of the system users that have a coded functionality as defined in src/main/resources/users.json
    const SYSTEM_ID = 1; //
    const SYSTEM_NAME = "zukunft.com system";                    // the system user used to log system tasks and as a fallback owner
    const SYSTEM_CODE_ID = "system";                    // unique id of the system user used to log system tasks
    const SYSTEM_EMAIL = "admin@zukunft.com";

    // the user that performs the system tests
    const SYSTEM_TEST_ID = 2;
    const SYSTEM_TEST_NAME = "zukunft.com system test";
    const SYSTEM_TEST_EMAIL = "support@zukunft.com";
    const SYSTEM_TEST_IP = "localhost";
    const SYSTEM_TEST_NAME_FIRST = "first";
    const SYSTEM_TEST_NAME_LAST = "last";

    // the user that acts as a partner for the system tests
    // so that multi-user behaviour can be tested
    const SYSTEM_NAME_TEST_PARTNER = "zukunft.com system test partner"; // to test that the user sandbox is working e.g. that changes of the main test user has no impact of another user simulated by this test user
    const SYSTEM_TEST_PROFILE_CODE_ID = "test";
    const SYSTEM_LOCAL = 'localhost';
    const SYSTEM_TEST_PARTNER_EMAIL = "support.partner@zukunft.com";

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
    public ?user_profile $profile = null; //
    public ?user $viewer = null;          // the user who wants to access this user
    // e.g. only admin are allowed to see other user parameters

    // var used for the registration and logon process
    public ?string $activation_key = '';
    public ?string $activation_timeout = '';
    public ?string $db_now = '';


    /*
     * construct and map
     */

    function __construct()
    {
        parent::__construct();
        $this->reset();

        //global $user_profiles;
        //$this->profile = $user_profiles->get_by_code_id(user_profile::NORMAL);
        //$this->profile = cl(db_cl::USER_PROFILE, user_profile::NORMAL);

    }

    function reset(): void
    {
        $this->id = 0;
        $this->name = null;
        $this->description = null;
        $this->ip_addr = null;
        $this->email = null;
        $this->first_name = null;
        $this->last_name = null;
        $this->code_id = null;
        $this->dec_point = null;
        $this->thousand_sep = DEFAULT_THOUSAND_SEP;
        $this->percent_decimals = DEFAULT_PERCENT_DECIMALS;
        $this->profile_id = null;
        $this->source_id = null;

        $this->wrd_id = null;
        $this->vrb_id = null;

        $this->wrd = null;
        $this->profile = null;
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
     * @return string the unique username for this pod
     */
    function name(): string
    {
        return $this->name;
    }



    /*
     * cast
     */

    /**
     * @return user_api the user frontend api object with all fields filled
     */
    function api_obj(): user_api
    {
        $api_obj = new user_api();
        return $this->api_obj_fields($api_obj);
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }

    /**
     * @return user_dsp the user frontend display object with all fields filled
     */
    function dsp_obj(): user_dsp
    {
        $api_obj = new user_dsp();
        return $this->api_obj_fields($api_obj);
    }

    /**
     * @return user_api|user_dsp the user api or display object with all fields filled
     */
    private function api_obj_fields(user_api|user_dsp $api_obj): user_api|user_dsp
    {
        $api_obj->id = $this->id;
        if ($this->name != null) {
            $api_obj->name = $this->name;
        } else {
            $api_obj->name = '';
        }
        $api_obj->description = $this->description;
        $api_obj->profile = $this->profile;
        $api_obj->email = $this->email;
        $api_obj->first_name = $this->first_name;
        $api_obj->last_name = $this->last_name;
        return $api_obj;
    }


    /*
     * loading / database access object (DAO) functions
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of a user from the database
     *
     * @param sql $sc with the target db_type set
     * @param string $query_name the name of the query use to prepare and call the query
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql $sc, string $query_name, string $class = self::class): sql_par
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
        $sc->set_fields(self::FLD_NAMES);
        return $qp;
    }

    /**
     * create an SQL statement to retrieve a user by id from the database
     *
     * @param sql $sc with the target db_type set
     * @param int $id the id of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql $sc, int $id, string $class = self::class): sql_par
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
     * @param sql $sc with the target db_type set
     * @param string $name the name of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name(sql $sc, string $name, string $class = self::class): sql_par
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
     * @param sql $sc with the target db_type set
     * @param string $email the email of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_email(sql $sc, string $email, string $class = self::class): sql_par
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
     * @param sql $sc with the target db_type set
     * @param string $name the name of the user
     * @param string $email the email of the user
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_name_or_email(sql $sc, string $name, string $email, string $class = self::class): sql_par
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
     * @param sql $sc with the target db_type set
     * @param string $ip_addr the ip address with which the user has logged in
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_ip(sql $sc, string $ip_addr, string $class = self::class): sql_par
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
     * @param sql $sc with the target db_type set
     * @param int $profile_id the id of the profile of which the first matching user should be loaded
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_profile(sql $sc, int $profile_id, string $class = self::class): sql_par
    {
        $qp = $this->load_sql($sc, 'profile', $class);
        $sc->add_where(self::FLD_PROFILE, $profile_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
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

    /**
     * load a user by id from the database
     *
     * TODO make sure that it is always checked if the requesting user has the sufficient permissions
     *  param user|null $request_usr the user who has requested the loading of the user data to prevent right gains
     *
     * @param int $id
     * @param string $class the name of the user
     * @return int
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        global $db_con;

        log_debug($id);
        $this->reset();
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
        $this->reset();
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
        $this->reset();
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
        $this->reset();
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
        $this->reset();
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
        $this->reset();
        $qp = $this->load_sql_by_profile($db_con->sql_creator(), $profile_id);
        return $this->load($qp);
    }

    function load_by_profile_code(string $profile_code_id): bool
    {
        global $user_profiles;
        return $this->load_by_profile($user_profiles->id($profile_code_id));
    }

    /**
     * load the user specific data that is not supposed to be changed very rarely user
     * so if changed all data is reloaded once
     */
    function load_usr_data(): void
    {
        global $db_con;
        global $verbs;
        global $system_views;

        $verbs = new verb_list($this);
        $verbs->load($db_con);

        $system_views = new view_sys_list($this);
        $system_views->load($db_con);

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
            $this->id = 0; // switch off the permission
        }
        return $test_result->all_message_text();
    }

    /**
     * @return string the ip address of the active user
     */
    private function get_ip(): string
    {
        if (array_key_exists("REMOTE_ADDR", $_SERVER)) {
            $this->ip_addr = $_SERVER['REMOTE_ADDR'];
        }
        if ($this->ip_addr == null) {
            $this->ip_addr = self::SYSTEM_LOCAL;
        }
        return $this->ip_addr;
    }

    /**
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
                    log_debug('use (' . $this->id . ')');
                }
            } else {
                // else use the IP address (for testing don't overwrite any testing ip)
                global $user_profiles;
                global $db_con;

                $this->load_by_ip($this->get_ip());
                if ($this->id <= 0) {
                    // use the ip address as the username and add the user
                    $this->name = $this->get_ip();

                    // allow to fill the database only if a local user has logged in
                    if ($this->name == self::SYSTEM_LOCAL) {

                        // TODO move to functions used here to check class
                        if ($user_profiles->is_empty()) {
                            $db_con->db_fill_code_links();

                            // reopen the database to collect the cached lists
                            $db_con->close();
                            $db_con = prg_start("test_reset_db");
                        }

                        // create the system user before the local user and admin to get the desired database id
                        import_system_users();

                        // create the admin users
                        $check_usr = new user();
                        if (!$check_usr->has_any_user_this_profile(user_profile::ADMIN)) {
                            $this->set_profile(user_profile::ADMIN);
                        }

                        // add the local admin user to use it for the import
                        $upd_result = $this->save($db_con);

                        // use the system user for the database initial load
                        $sys_usr = new user;
                        $sys_usr->load_by_id(SYSTEM_USER_ID);

                        //
                        import_verbs($sys_usr);

                        // reload the base configuration
                        import_base_config($sys_usr);
                        import_config($sys_usr);

                    } else {
                        $upd_result = $this->save($db_con);
                    }


                    // TODO make sure that the result is always compatible and checked if needed
                    // adding a new user automatically is normal, so the result does not need to be shown to the user
                    if (str_replace('1', '', $upd_result) <> '') {
                        $result = $upd_result;
                    }
                }
            }
        }
        log_debug(' "' . $this->name . '" (' . $this->id . ')');
        return $result;
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
        global $user_profiles;

        log_debug();
        $result = parent::import_db_obj($this, $test_obj);

        // reset all parameters of this user object
        $this->reset();
        foreach ($json_obj as $key => $value) {
            if ($key == sandbox_exp::FLD_NAME) {
                $this->name = $value;
            }
            if ($key == sandbox_exp::FLD_DESCRIPTION) {
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
                $this->profile_id = $user_profiles->id($value);
            }
            if ($key == sandbox_exp::FLD_CODE_ID) {
                if ($profile_id == $user_profiles->id(user_profile::ADMIN)
                    or $profile_id == $user_profiles->id(user_profile::SYSTEM)) {
                    $this->code_id = $value;
                }
            }
        }

        // save the user in the database
        if (!$test_obj) {
            if ($result->is_ok()) {
                // check the importing profile and make sure that gaining additional privileges is impossible
                // the user profiles must always be in the order that the lower ID has same or less rights
                // TODO use the right level of the profile
                if ($profile_id >= $this->profile_id) {
                    global $db_con;
                    $result->add_message($this->save($db_con));
                }
            }
        }


        return $result;
    }

    /**
     * create a user object for the export
     * @param bool $do_load to switch off the database load for unit tests
     * @return sandbox_exp the filled object used to create the json
     */
    function export_obj(bool $do_load = true): sandbox_exp
    {
        log_debug();
        $result = new user_exp();

        // add the source parameters
        $result->name = $this->name;
        if ($this->description <> '') {
            $result->description = $this->description;
        }
        if ($this->email <> '') {
            $result->email = $this->email;
        }
        if ($this->first_name <> '') {
            $result->first_name = $this->first_name;
        }
        if ($this->last_name <> '') {
            $result->last_name = $this->last_name;
        }
        if ($this->code_id <> '') {
            $result->code_id = $this->code_id;
        }
        if ($this->profile <> '') {
            $result->profile = $this->profile;
        }

        log_debug(json_encode($result));
        return $result;
    }


    /*
     * information
     */

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
     * @returns bool true if the user has admin rights
     */
    function is_admin(): bool
    {
        global $user_profiles;
        log_debug();
        $result = false;

        if ($this->profile_id == $user_profiles->id(user_profile::ADMIN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @returns bool true if the user is a system user e.g. the reserved word names can be used
     */
    function is_system(): bool
    {
        global $user_profiles;
        log_debug();
        $result = false;

        if ($this->profile_id == $user_profiles->id(user_profile::TEST)
            or $this->profile_id == $user_profiles->id(user_profile::SYSTEM)) {
            $result = true;
        }
        return $result;
    }

    // true if the user has the right to import data
    function can_import(): bool
    {
        global $user_profiles;
        log_debug();
        $result = false;

        if ($this->profile_id == $user_profiles->id(user_profile::ADMIN)
            or $this->profile_id == $user_profiles->id(user_profile::TEST)
            or $this->profile_id == $user_profiles->id(user_profile::SYSTEM)) {
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
        $wrd->load_by_id($this->wrd_id, word::class);
        $this->wrd = $wrd;
        return $wrd;
    }

    /**
     * set the parameters for the virtual user that represents the standard view for all users
     */
    function dummy_all(): void
    {
        $this->id = 0;
        $this->code_id = 'all';
        $this->name = 'standard user view for all users';
    }

    //
    // create the display user object based on the object (not needed any more if always the display user object is used)
    function dsp_user(): user_dsp
    {
        global $db_con;
        $usr = new user;
        $usr->load_by_id($this->id);
        return new user_dsp($usr->api_json());
    }

    // create the HTML code to display the username with the HTML link
    function display(): string
    {
        return '<a href="/http/user.php?id=' . $this->id . '">' . $this->name . '</a>';
    }

    // remember the last source that the user has used
    function set_source($source_id): bool
    {
        log_debug('(' . $this->id . ',s' . $source_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $db_con->set_class(sql_db::TBL_USER);
        return $db_con->update_old($this->id, 'source_id', $source_id);
    }

    // remember the last source that the user has used
    // TODO add the database field
    function set_verb($vrb_id): bool
    {
        log_debug('(' . $this->id . ',s' . $vrb_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $result = $db_con->set_class(sql_db::TBL_USER);
        //$result = $db_con->update($this->id, verb::FLD_ID, $vrb_id);
        return $result;
    }

    function set_profile(string $profile_code_id): void
    {
        global $user_profiles;
        $this->profile_id = $user_profiles->id($profile_code_id);
        //$this->profile = $user_profiles->lst[$this->profile_id];
    }

    // set the main log entry parameters for updating one word field
    private function log_upd(): change
    {
        log_debug(' user ' . $this->name);
        $log = new change($this);
        $log->action = change_action::UPDATE;
        $log->set_table(change_table_list::USER);

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
            and $usr_par[$par_name] <> "") {
            $log = $this->log_upd();
            $log->old_value = $db_value;
            $log->new_value = $usr_par[$par_name];
            $log->row_id = $this->id;
            $log->set_field($fld_name);
            if ($log->add()) {
                $db_con->set_class(sql_db::TBL_USER);
                $result = $db_con->update_old($this->id, $log->field(), $log->new_value);
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
        $db_con->usr_id = $this->id;
        $db_con->set_class(sql_db::TBL_USER);

        $db_usr = new user;
        $db_id = $db_usr->load_by_id($this->id);
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
     * @return string an empty string if all user data are saved in the database otherwise the message that should be shown to the user
     */
    function save(sql_db $db_con): string
    {
        global $user_profiles;

        $result = '';

        // build the database object because the is anyway needed
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $db_con->set_class(sql_db::TBL_USER);

        if ($this->id <= 0) {
            log_debug(" add (" . $this->name . ")");

            $this->id = $db_con->insert_old("user_name", $this->name);
            // log the changes???
            if ($this->id > 0) {
                // add the description of the user
                if (!$db_con->update_old($this->id, sandbox_named::FLD_DESCRIPTION, $this->description)) {
                    $result = 'Saving of user description ' . $this->id . ' failed.';
                }
                // add the email of the user
                if (!$db_con->update_old($this->id, self::FLD_EMAIL, $this->email)) {
                    $result = 'Saving of user email ' . $this->id . ' failed.';
                }
                // add the first name of the user
                if (!$db_con->update_old($this->id, self::FLD_FIRST_NAME, $this->first_name)) {
                    $result = 'Saving of user first name ' . $this->id . ' failed.';
                }
                // add the last name of the user
                if (!$db_con->update_old($this->id, self::FLD_LAST_NAME, $this->last_name)) {
                    $result = 'Saving of user last name ' . $this->id . ' failed.';
                }
                // add the code of the user
                if ($this->code_id != '') {
                    if (!$db_con->update_old($this->id, self::FLD_CODE_ID, $this->code_id)) {
                        $result = 'Saving of user code id ' . $this->id . ' failed.';
                    }
                }
                // add the profile of the user
                if (!$db_con->update_old($this->id, self::FLD_PROFILE, $this->profile_id)) {
                    $result = 'Saving of user profile ' . $this->id . ' failed.';
                }
                // add the ip address to the user, but never for system users
                if ($this->profile_id != $user_profiles->id(user_profile::SYSTEM)
                    and $this->profile_id != $user_profiles->id(user_profile::TEST)) {
                    if (!$db_con->update_old($this->id, self::FLD_IP_ADDR, $this->get_ip())) {
                        $result = 'Saving of user ' . $this->id . ' failed.';
                    }
                }
                log_debug(" add ... done");
            } else {
                log_debug(" add ... failed");
            }
        } else {
            // update the ip address and log the changes????
            log_warning(' method for ip update missing', 'user->save', 'method for ip update missing', (new Exception)->getTraceAsString(), $this);
        }
        return $result;
    }


    /*
     * debug
     */

    function dsp_id(): string
    {
        return $this->name . ' (' . $this->id . ')';
    }

}
