<?php

/*

    user.php - a person who uses zukunft.com
    --------

    if a user has done 3 value edits he can add new values (adding a word to a value also creates a new value)
    if a user has added 3 values and at least one is accepted by another user, he can add words and formula and he must have a valid email
    if a user has added 2 formula and both are accepted by at least one other user and no one has complained, he can change formulas and words, including linking of words
    if a user has linked a 10 words and all got accepted by one other user and no one has complained, he can request new verbs and he must have an validated address

    if a user got 10 pending word or formula discussion, he can no longer add words or formula utils the open discussions are less than 10
    if a user got 5 pending word or formula discussion, he can no longer change words or formula utils the open discussions are less than 5
    if a user got 2 pending verb discussion, he can no longer add verbs utils the open discussions are less than 2

    the same ip can max 10 add 10 values and max 5 user a day, upon request the number of max user creation can be increased for an ip range

    TODO make sure that no right gain is possible
    TODO move the non functional user parameters to hidden words to be able to reuse the standard view functionality


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

use api\user_api;
use export\exp_obj;
use html\user_dsp;
use html\word_dsp;

class user
{

    /*
     * database link
     */

    // database fields only used for user
    const FLD_ID = 'user_id';
    const FLD_NAME= 'user_name';
    const FLD_IP_ADDRESS = 'ip_address';
    const FLD_EMAIL = 'email';
    const FLD_FIRST_NAME = 'first_name';
    const FLD_LAST_NAME = 'last_name';
    const FLD_LAST_WORD = 'last_word_id';
    const FLD_SOURCE = 'source_id';
    const FLD_USER_PROFILE = 'user_profile_id';

    // all database field names excluding the id
    const FLD_NAMES = array(
        sql_db::FLD_CODE_ID,
        self::FLD_IP_ADDRESS,
        self::FLD_EMAIL,
        self::FLD_FIRST_NAME,
        self::FLD_LAST_NAME,
        self::FLD_LAST_WORD,
        self::FLD_SOURCE,
        self::FLD_USER_PROFILE
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
    const SYSTEM = "zukunft.com system";                    // the system user used to log system tasks and as a fallback owner
    const NAME_SYSTEM_TEST = "zukunft.com system test";          // to perform the system tests
    const NAME_SYSTEM_TEST_PARTNER = "zukunft.com system test partner"; // to test that the user sandbox is working e.g. that changes of the main test user has no impact of another user simulated by this test user
    const SYSTEM_OLD = "system";
    const SYSTEM_TEST_OLD = "test";

    /*
     * object vars
     */

    // database fields
    public ?int $id = null;               // the database id of the word link type (verb)
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

    /*
     * construct and map
     */

    function __construct()
    {
        $this->reset();

        //global $user_profiles;
        //$this->profile = $user_profiles->get_by_code_id(user_profile::NORMAL);
        //$this->profile = cl(db_cl::USER_PROFILE, user_profile::NORMAL);

    }

    function reset(): void
    {
        $this->id = null;
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

    }

    function row_mapper(array $db_usr): bool
    {
        global $debug;

        $result = false;
        if (!$db_usr) {
            $this->id = 0;
        } else {
            if ($db_usr[user::FLD_ID] <= 0) {
                $this->id = 0;
            } else {
                $this->id = $db_usr[self::FLD_ID];
                $this->code_id = $db_usr[sql_db::FLD_CODE_ID];
                $this->name = $db_usr[self::FLD_NAME];
                $this->ip_addr = $db_usr[self::FLD_IP_ADDRESS];
                $this->email = $db_usr[self::FLD_EMAIL];
                $this->first_name = $db_usr[self::FLD_FIRST_NAME];
                $this->last_name = $db_usr[self::FLD_LAST_NAME];
                $this->wrd_id = $db_usr[self::FLD_LAST_WORD];
                $this->source_id = $db_usr[self::FLD_SOURCE];
                $this->profile_id = $db_usr[self::FLD_USER_PROFILE];
                $this->dec_point = DEFAULT_DEC_POINT;
                $this->thousand_sep = DEFAULT_THOUSAND_SEP;
                $this->percent_decimals = DEFAULT_PERCENT_DECIMALS;
                $result = true;
                log_debug($this->name, $debug - 25);
            }
        }
        return $result;
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
        $api_obj->name = $this->name;
        $api_obj->description = $this->description;
        $api_obj->profile = $this->profile;
        $api_obj->email = $this->email;
        $api_obj->first_name = $this->first_name;
        $api_obj->last_name = $this->last_name;
        return $api_obj;
    }

    /**
     * @return user_dsp_old the user object with the display interface functions
     */
    function dsp_obj_old(): user_dsp_old
    {
        $dsp_obj = new user_dsp_old();

        $dsp_obj->id = $this->id;
        $dsp_obj->name = $this->name;
        $dsp_obj->ip_addr = $this->ip_addr;
        $dsp_obj->email = $this->email;

        $dsp_obj->first_name = $this->first_name;
        $dsp_obj->last_name = $this->last_name;
        $dsp_obj->code_id = $this->code_id;
        $dsp_obj->dec_point = $this->dec_point;
        $dsp_obj->thousand_sep = $this->thousand_sep;
        $dsp_obj->percent_decimals = $this->percent_decimals;

        $dsp_obj->profile_id = $this->profile_id;
        $dsp_obj->source_id = $this->source_id;

        $dsp_obj->wrd_id = $this->wrd_id;
        $dsp_obj->vrb_id = $this->vrb_id;

        $dsp_obj->wrd = $this->wrd;

        return $dsp_obj;
    }

    /*
     * loading / database access object (DAO) functions
     */

    /**
     * create an SQL statement to retrieve the parameters of a user from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_obj_vars(sql_db $db_con, string $class = self::class): sql_par
    {
        $qp = new sql_par($class);
        $db_con->set_type(sql_db::TBL_USER);
        if ($this->viewer == null) {
            if ($this->id == null) {
                $db_con->set_usr(0);
            } else {
                $db_con->set_usr($this->id);
            }
        } else {
            $db_con->set_usr($this->viewer->id);
        }
        $db_con->set_fields(self::FLD_NAMES);
        if ($this->id > 0) {
            $qp->name .= 'id';
            $db_con->set_name($qp->name);
            $db_con->add_par(sql_db::PAR_INT, $this->id);
            $qp->sql = $db_con->select_by_set_id();
        } elseif ($this->code_id > 0) {
            $qp->name .= 'code_id';
            $db_con->set_name($qp->name);
            $db_con->add_par(sql_db::PAR_TEXT, $this->code_id);
            $qp->sql = $db_con->select_by_code_id();
        } elseif ($this->name <> '') {
            if ($this->email == '') {
                $qp->name .= 'name';
                $db_con->set_name($qp->name);
                $db_con->add_par(sql_db::PAR_TEXT, $this->name);
                $qp->sql = $db_con->select_by_set_name();
            } else {
                $qp->name .= 'name_or_email';
                $db_con->set_name($qp->name);
                $db_con->add_par(sql_db::PAR_TEXT, $this->name);
                $db_con->add_par(sql_db::PAR_TEXT_OR, $this->email);
                $qp->sql = $db_con->select_by_name_or(self::FLD_EMAIL);
            }
        } elseif ($this->ip_addr <> '') {
            $qp->name .= self::FLD_IP_ADDRESS;
            $db_con->set_name($qp->name);
            $db_con->add_par(sql_db::PAR_TEXT, $this->ip_addr);
            $qp->sql = $db_con->select_by_field(self::FLD_IP_ADDRESS);
        } elseif ($this->profile_id > 0) {
            $qp->name .= 'profile_id';
            $db_con->set_name($qp->name);
            $db_con->add_par(sql_db::PAR_INT, $this->profile_id);
            $qp->sql = $db_con->select_by_field(self::FLD_USER_PROFILE);
        } else {
            log_err('Either the id, code_id, name or ip address must be set to get a user');
        }

        $qp->par = $db_con->get_par();

        return $qp;
    }


    /**
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return array the database record for the db row to object mapper
     */
    private function load_db(sql_db $db_con): array
    {

        $db_usr = null;
        // select the user either by id, code_id, name or ip
        $qp = $this->load_sql_obj_vars($db_con);
        if (!$qp->has_par()) {
            log_err("Either the database ID, the user name, the ip address or the code_id must be set for loading a user.", "user->load", '', (new Exception)->getTraceAsString(), $this);
        } else {
            $db_usr = $db_con->get1($qp);
        }
        return $db_usr;
    }

    /**
     * load the missing user parameters from the database
     * should be private because the loading should be done via the get method
     */
    function load(sql_db $db_con): bool
    {
        $db_usr = $this->load_db($db_con);
        return $this->row_mapper($db_usr);
    }

    function load_by_id(int $id): bool
    {
        global $db_con;

        $this->reset();
        $this->id = $id;
        return $this->load($db_con);
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

        $this->reset();
        $this->name = $name;
        $this->email = $email;
        return $this->load($db_con);
    }

    /**
     * special function to exposed the user loading for simulating test users for the automatic system test
     * TODO used also in the user sandbox: check if this is correct
     */
    function load_test_user(): bool
    {
        global $db_con;
        return $this->load($db_con);
    }

    function load_user_by_profile(string $profile_code_id, sql_db $db_con): bool
    {
        $profile_id = cl(db_cl::USER_PROFILE, $profile_code_id);

        $this->reset();
        $this->profile_id = $profile_id;
        $qp = $this->load_sql_obj_vars($db_con);
        $db_usr = $db_con->get1($qp);
        return $this->row_mapper($db_usr);
    }

    function has_any_user_this_profile(string $profile_code_id, sql_db $db_con): bool
    {
        return $this->load_user_by_profile($profile_code_id, $db_con);
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
    public function ip_check(string $ip_addr): string
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
            $this->ip_addr = 'localhost';
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
            log_debug(' (' . $this->ip_addr . ')', $debug -1);
        }
        // even if the user has an open session, but the ip is blocked, drop the user
        $result .= $this->ip_check($this->ip_addr);

        if ($result == '') {
            // if the user has logged in use the logged in account
            if (isset($_SESSION['logged'])) {
                if ($_SESSION['logged']) {
                    $this->id = $_SESSION['usr_id'];
                    global $db_con;
                    $this->load($db_con);
                    log_debug('use (' . $this->id . ')');
                }
            } else {
                // else use the IP address (for testing don't overwrite any testing ip)
                global $user_profiles;
                global $db_con;

                $this->get_ip();
                $this->load($db_con);
                if ($this->id <= 0) {
                    // use the ip address as the username and add the user
                    $this->name = $this->ip_addr;

                    // allow to fill the database only if a local user has logged in
                    if ($this->name == 'localhost') {

                        // TODO move to functions used here to check class
                        if ($user_profiles->is_empty()) {
                            db_fill_code_links($db_con);

                            // reopen the database to collect the cached lists
                            $db_con->close();
                            $db_con = prg_start("test_reset_db");
                        }

                        // create the system user before the local user and admin to get the desired database id
                        import_system_users();

                        // create the admin users
                        $check_usr = new user();
                        if (!$check_usr->has_any_user_this_profile(user_profile::ADMIN, $db_con)) {
                            $this->set_profile(user_profile::ADMIN);
                        }

                        // add the local admin user to use it for the import
                        $upd_result = $this->save($db_con);

                        //
                        import_verbs($this);

                        // reload the base configuration
                        import_base_config();

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
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, int $profile_id, bool $do_save = true): user_message
    {
        global $user_profiles;

        log_debug();
        $result = new user_message();

        // reset all parameters of this user object
        $this->reset();
        foreach ($json_obj as $key => $value) {
            if ($key == exp_obj::FLD_NAME) {
                $this->name = $value;
            }
            if ($key == exp_obj::FLD_DESCRIPTION) {
                $this->description = $value;
            }
            if ($key == self::FLD_EX_PROFILE) {
                $this->profile_id = $user_profiles->id($value);
            }
        }

        // save the word in the database
        if ($result->is_ok() and $do_save) {
            // check the importing profile and make sure that gaining additional privileges is impossible
            // the user profiles must always be in the order that the lower ID has same or less rights
            // TODO use the right level of the profile
            if ($profile_id >= $this->profile_id) {
                global $db_con;
                $result->add_message($this->save($db_con));
            }
        }


        return $result;
    }


    /*
     * information functions
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
        log_debug(' (' . $this->id . ')');
        $result = false;

        if (!isset($this->profile_id)) {
            global $db_con;
            $this->load($db_con);
        }
        if ($this->profile_id == cl(db_cl::USER_PROFILE, user_profile::ADMIN)) {
            $result = true;
        }
        return $result;
    }

    /**
     * @returns bool true if the user is a system user e.g. the reserved word names can be used
     */
    function is_system(): bool
    {
        log_debug(' (' . $this->id . ')');
        $result = false;

        if (!isset($this->profile_id)) {
            global $db_con;
            $this->load($db_con);
        }
        if ($this->profile_id == cl(db_cl::USER_PROFILE, user_profile::TEST)
            or $this->profile_id == cl(db_cl::USER_PROFILE, user_profile::SYSTEM)) {
            $result = true;
        }
        return $result;
    }

    // true if the user has the right to import data
    function can_import(): bool
    {
        log_debug(' (' . $this->id . ')');
        $result = false;

        if (!isset($this->profile_id)) {
            global $db_con;
            $this->load($db_con);
        }
        if ($this->profile_id == cl(db_cl::USER_PROFILE, user_profile::ADMIN)
            or $this->profile_id == cl(db_cl::USER_PROFILE, user_profile::TEST)
            or $this->profile_id == cl(db_cl::USER_PROFILE, user_profile::SYSTEM)) {
            $result = true;
        }
        return $result;
    }

    // load the last word used by the user
    function last_wrd(): word_dsp
    {
        if ($this->wrd_id <= 0) {
            $this->wrd_id = DEFAULT_WORD_ID;
        }
        $wrd = new word($this);
        $wrd->load_by_id($this->wrd_id, word::class);
        $this->wrd = $wrd;
        return $wrd->dsp_obj();
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
    function dsp_id(): string
    {
        return $this->name . ' (' . $this->id . ')';
    }

    // create the display user object based on the object (not needed any more if always the display user object is used)
    function dsp_user(): user_dsp_old
    {
        global $db_con;
        $dsp_user = new user_dsp_old;
        $dsp_user->id = $this->id;
        $dsp_user->load($db_con);
        return $dsp_user;
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
        $db_con->set_type(sql_db::TBL_USER);
        return $db_con->update($this->id, 'source_id', $source_id);
    }

    // remember the last source that the user has used
    // TODO add the database field
    function set_verb($vrb_id): bool
    {
        log_debug('(' . $this->id . ',s' . $vrb_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $result = $db_con->set_type(sql_db::TBL_USER);
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
    private function log_upd(): user_log_named
    {
        log_debug(' user ' . $this->name);
        $log = new user_log_named;
        $log->usr = $this;
        $log->action = user_log::ACTION_UPDATE;
        $log->set_table(change_log_table::USR);

        return $log;
    }

    /**
     * check and update a single user parameter
     */
    private function upd_par(sql_db $db_con, $usr_par, $db_row, $fld_name, $par_name)
    {
        $result = '';
        if ($usr_par[$par_name] <> $db_row[$fld_name]
            and $usr_par[$par_name] <> "") {
            $log = $this->log_upd();
            $log->old_value = $db_row[$fld_name];
            $log->new_value = $usr_par[$par_name];
            $log->row_id = $this->id;
            $log->set_field($fld_name);
            if ($log->add()) {
                $db_con->set_type(sql_db::TBL_USER);
                $result = $db_con->update($this->id, $log->field(), $log->new_value);
            }
        }
        return $result;
    }

    /**
     * check and update all user parameters
     */
    function upd_pars($usr_par): string
    {
        log_debug();

        global $db_con;

        $result = ''; // reset the html code var

        // build the database object because the is anyway needed
        $db_con->usr_id = $this->id;
        $db_con->set_type(sql_db::TBL_USER);

        $db_usr = new user;
        $db_usr->id = $this->id;
        $db_row = $db_usr->load_db($db_con);
        log_debug('database user loaded "' . $db_row['name'] . '"');

        $this->upd_par($db_con, $usr_par, $db_row, self::FLD_NAME, 'name');
        $this->upd_par($db_con, $usr_par, $db_row, self::FLD_EMAIL, 'email');
        $this->upd_par($db_con, $usr_par, $db_row, self::FLD_FIRST_NAME, 'fname');
        $this->upd_par($db_con, $usr_par, $db_row, self::FLD_LAST_NAME, 'lname');

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
        $result = '';

        // build the database object because the is anyway needed
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $db_con->set_type(sql_db::TBL_USER);

        if ($this->id <= 0) {
            log_debug(" add (" . $this->name . ")");

            $this->id = $db_con->insert("user_name", $this->name);
            // log the changes???
            if ($this->id > 0) {
                // add the profile of the user
                if (!$db_con->update($this->id, "user_profile_id", $this->profile_id)) {
                    $result = 'Saving of user ' . $this->id . ' failed.';
                }
                // add the ip address to the user, but never for system users
                if ($this->profile_id != cl(db_cl::USER_PROFILE, user_profile::SYSTEM)
                    and $this->profile_id != cl(db_cl::USER_PROFILE, user_profile::TEST)) {
                    if (!$db_con->update($this->id, self::FLD_IP_ADDRESS, $this->get_ip())) {
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
}
