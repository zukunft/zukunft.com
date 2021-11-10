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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class user
{

    // list of the system users that have a coded functionality
    const SYSTEM = "system";
    const SYSTEM_TEST = "test";

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
    public ?int $profile_id = null;       // id of the user profile
    public ?int $source_id = null;        // id of the last source used by the user

    // user setting parameters
    public ?int $wrd_id = null;           // id of the last word viewed by the user
    public ?int $vrb_id = null;           // id of the last verb used by the user

    // in memory only fields
    public ?word $wrd = null;             // the last word viewed by the user
    public ?user_profile $profile = null; //

    function __construct()
    {
        $this->reset();

        //global $user_profiles;
        //$this->profile = $user_profiles->get_by_code_id(user_profile::NORMAL);
        //$this->profile = cl(db_cl::USER_PROFILE, user_profile::NORMAL);

    }

    function reset()
    {
        $this->id = null;
        $this->name = null;
        $this->ip_addr = null;
        $this->email = null;
        $this->first_name = null;
        $this->last_name = null;
        $this->code_id = null;
        $this->dec_point = null;
        $this->thousand_sep = null;
        $this->profile_id = null;
        $this->source_id = null;

        $this->wrd_id = null;
        $this->vrb_id = null;

        $this->wrd = null;

    }

    /**
     * @return user_dsp the user object with the display interface functions
     */
    function dsp_obj(): user_dsp
    {
        $dsp_obj = new user_dsp();

        $dsp_obj->id = $this->id;
        $dsp_obj->name = $this->name;
        $dsp_obj->ip_addr = $this->ip_addr;
        $dsp_obj->email = $this->email;

        $dsp_obj->first_name = $this->first_name;
        $dsp_obj->last_name = $this->last_name;
        $dsp_obj->code_id = $this->code_id;
        $dsp_obj->dec_point = $this->dec_point;
        $dsp_obj->thousand_sep = $this->thousand_sep;

        $dsp_obj->profile_id = $this->profile_id;
        $dsp_obj->source_id = $this->source_id;

        $dsp_obj->wrd_id = $this->wrd_id;
        $dsp_obj->vrb_id = $this->vrb_id;

        $dsp_obj->wrd = $this->wrd;

        return $dsp_obj;
    }

    function row_mapper($db_usr): bool
    {
        $result = false;
        if ($db_usr == false) {
            $this->id = 0;
        } else {
            if ($db_usr['user_id'] <= 0) {
                $this->id = 0;
            } else {
                $this->id = $db_usr['user_id'];
                $this->code_id = $db_usr[sql_db::FLD_CODE_ID];
                $this->name = $db_usr['user_name'];
                $this->ip_addr = $db_usr['ip_address'];
                $this->email = $db_usr['email'];
                $this->first_name = $db_usr['first_name'];
                $this->last_name = $db_usr['last_name'];
                $this->wrd_id = $db_usr['last_word_id'];
                $this->source_id = $db_usr['source_id'];
                $this->profile_id = $db_usr['user_profile_id'];
                $this->dec_point = DEFAULT_DEC_POINT;
                $this->thousand_sep = DEFAULT_THOUSAND_SEP;
                $result = true;
                log_debug('user->row_mapper (' . $this->name . ')');
            }
        }
        return $result;
    }

    //
    private function load_db(sql_db $db_con)
    {

        $db_usr = null;
        // select the user either by id, code_id, name or ip
        $sql_where = '';
        if ($this->id > 0) {
            $sql_where = "u.user_id = " . $this->id;
            log_debug('user->load user id ' . $this->id);
        } elseif ($this->code_id > 0) {
            $sql_where = "u.code_id = " . $this->code_id;
        } elseif ($this->name <> '') {
            $sql_where = "u.user_name = " . $db_con->sf($this->name);
        } elseif ($this->ip_addr <> '') {
            $sql_where = "u.ip_address = " . $db_con->sf($this->ip_addr);
        }

        log_debug('user->load search by "' . $sql_where . '"');
        if ($sql_where == '') {
            log_err("Either the database ID, the user name, the ip address or the code_id must be set for loading a user.", "user->load", '', (new Exception)->getTraceAsString(), $this);
        } else {
            $sql = "SELECT u.user_id,
                     u.code_id,
                     u.user_name,
                     u.ip_address,
                     u.email,
                     u.first_name,
                     u.last_name,
                     u.last_word_id,
                     u.source_id,
                     u.user_profile_id
                FROM users u 
              WHERE " . $sql_where . ";";
            $db_con->usr_id = $this->id;
            $db_usr = $db_con->get1($sql);
        }
        return $db_usr;
    }

    /**
     * load the missing user parameters from the database
     * should be private because the loading should be done via the get method
     */
    function load($db_con): bool
    {
        $db_usr = $this->load_db($db_con);
        return $this->row_mapper($db_usr);
    }

    // special function to exposed the user loading for simulating test users for the automatic system test
    // TODO used also in the user sandbox: check if this is correct
    function load_test_user(): bool
    {
        global $db_con;
        return $this->load($db_con);
    }

    function load_user_by_profile(string $profile_code_id, sql_db $db_con): bool
    {
        $profile_id = cl(db_cl::USER_PROFILE, $profile_code_id);

        $sql = "SELECT * FROM users WHERE user_profile_id = " . $profile_id . ";";
        $db_usr = $db_con->get1($sql);
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
            log_debug('user->ip_in_range ip ' . $ip_addr . ' (' . ip2long(trim($ip_addr)) . ') is in range between ' . $min . ' (' . ip2long(trim($min)) . ') and  ' . $max . ' (' . ip2long(trim($max)) . ')');
            return true;
        }
        return $result;

    }

    // return the message, why the if is not permitted
    // exposed as public mainly for testing
    public function ip_check($ip_addr): string
    {
        log_debug('user->ip_check (' . $ip_addr . ')');

        global $db_con;

        $msg = '';
        $sql = "SELECT ip_from,
                   ip_to,
                   reason
              FROM user_blocked_ips 
             WHERE is_active = 1;";
        $db_con->usr_id = $this->id;
        $ip_lst = $db_con->get($sql);
        foreach ($ip_lst as $ip_range) {
            log_debug('user->ip_check range (' . $ip_range['ip_from'] . ' to ' . $ip_range['ip_to'] . ')');
            if ($this->ip_in_range($ip_addr, $ip_range['ip_from'], $ip_range['ip_to'])) {
                log_debug('user->ip_check ip ' . $ip_addr . ' blocked due to range from ' . $ip_range['ip_from'] . ' to ' . $ip_range['ip_to']);
                $msg = 'Your IP ' . $ip_addr . ' is blocked at the moment because ' . $ip_range['reason'] . '. If you think, this should not be the case, please request the unblocking with an email to admin@zukunft.com.';
                $this->id = 0; // switch off the permission
            }
        }
        return $msg;
    }

    // get the ip address of the active user
    private function get_ip()
    {
        if (array_key_exists("REMOTE_ADDR", $_SERVER)) {
            $this->ip_addr = $_SERVER['REMOTE_ADDR'];
        }
        if ($this->ip_addr == null) {
            $this->ip_addr = 'localhost';
        }
        return $this->ip_addr;
    }

    // get the active session user object
    function get()
    {
        $result = ''; // for the result message e.g. if the user is blocked

        // test first if the IP is blocked
        if ($this->ip_addr == '') {
            $this->get_ip();
        } else {
            log_debug('user->get (' . $this->ip_addr . ')');
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
                    log_debug('user->get -> use (' . $this->id . ')');
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
        log_debug('user->got "' . $this->name . '" (' . $this->id . ')');
        return $result;
    }

    /**
     * import a user from a json data user object
     *
     * @param array $json_obj an array with the data of the json object
     * @param user_profile $profile the profile of the user how has initiated the import mainly used to prevent any user to gain additional rights
     * @param bool $do_save can be set to false for unit testing
     * @return string an empty string if the import has been successfully saved to the database or an error message that should be shown to the user
     */
    function import_obj(array $json_obj, int $profile_id, bool $do_save = true): string
    {
        global $user_profiles;

        log_debug('user->import_obj');
        $result = '';

        // reset all parameters of this user object
        $this->reset();
        foreach ($json_obj as $key => $value) {
            if ($key == 'name') {
                $this->name = $value;
            }
            if ($key == 'description') {
                $this->description = $value;
            }
            if ($key == 'profile') {
                $this->profile_id = $user_profiles->id($value);
            }
        }

        // save the word in the database
        if ($do_save) {
            // check the importing profile and make sure that gaining additional privileges is impossible
            // the user profiles must always be in the order that the lower ID has same or less rights
            // TODO use the right level of the profile
            if ($profile_id >= $this->profile_id) {
                global $db_con;
                $result .= $this->save($db_con);
            }
        }


        return $result;
    }


    // true if the user has admin rights
    function is_admin(): bool
    {
        log_debug('user->is_admin (' . $this->id . ')');
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

    // true if the user is a system user e.g. the reserved word names can be used
    function is_system(): bool
    {
        log_debug('user->is_system (' . $this->id . ')');
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
        log_debug('user->can_import (' . $this->id . ')');
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
        $wrd = new word_dsp;
        $wrd->id = $this->wrd_id;
        $wrd->usr = $this;
        $wrd->load();
        $this->wrd = $wrd;
        return $wrd;
    }

    // set the parameters for the virtual user that represents the standard view for all users
    function dummy_all()
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

    // create the display user object based on the object (no needed any more if always the display user object is used)
    function dsp_user(): user_dsp
    {
        $dsp_user = new user_dsp;
        $dsp_user->id = $this->id;
        $dsp_user->load();
        return $dsp_user;
    }

    // create the HTML code to display the user name with the HTML link
    function display(): string
    {
        return '<a href="/http/user.php?id=' . $this->id . '">' . $this->name . '</a>';
    }

    // remember the last source that the user has used
    function set_source($source_id): bool
    {
        log_debug('user->set_source(' . $this->id . ',s' . $source_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $db_con->set_type(DB_TYPE_USER);
        return $db_con->update($this->id, 'source_id', $source_id);
    }

    // remember the last source that the user has used
    // todo add the database field
    function set_verb($vrb_id): bool
    {
        log_debug('user->set_verb(' . $this->id . ',s' . $vrb_id . ')');
        global $db_con;
        //$db_con = new mysql;
        $db_con->usr_id = $this->id;
        $result = $db_con->set_type(DB_TYPE_USER);
        //$result = $db_con->update($this->id, 'verb_id', $vrb_id);
        return $result;
    }

    function set_profile(string $profile_code_id)
    {
        global $user_profiles;
        $this->profile_id = $user_profiles->id($profile_code_id);
        //$this->profile = $user_profiles->lst[$this->profile_id];
    }

    // set the main log entry parameters for updating one word field
    private function log_upd(): user_log
    {
        log_debug('user->log_upd user ' . $this->name);
        $log = new user_log;
        $log->usr = $this;
        $log->action = 'update';
        $log->table = 'users';

        return $log;
    }

    // check and update a single user parameter
    private function upd_par($db_con, $usr_par, $db_row, $fld_name, $par_name)
    {
        $result = '';
        if ($usr_par[$par_name] <> $db_row[$fld_name]
            and $usr_par[$par_name] <> "") {
            $log = $this->log_upd();
            $log->old_value = $db_row[$fld_name];
            $log->new_value = $usr_par[$par_name];
            $log->row_id = $this->id;
            $log->field = $fld_name;
            if ($log->add()) {
                $db_con->set_type(DB_TYPE_USER);
                $result = $db_con->update($this->id, $log->field, $log->new_value);
            }
        }
        return $result;
    }

    // check and update all user parameters
    function upd_pars($usr_par)
    {
        log_debug('user->upd_pars');
        log_debug('user->upd_pars(u' . $this->id . ',p' . dsp_array($usr_par) . ')');

        global $db_con;

        $result = ''; // reset the html code var

        // build the database object because the is anyway needed
        $db_con->usr_id = $this->id;
        $db_con->set_type(DB_TYPE_USER);

        $db_usr = new user;
        $db_usr->id = $this->id;
        $db_row = $db_usr->load_db();
        log_debug('user->save -> database user loaded "' . $db_row['name'] . '"');

        $this->upd_par($db_con, $usr_par, $db_row, "user_name", 'name');
        $this->upd_par($db_con, $usr_par, $db_row, "email", 'email');
        $this->upd_par($db_con, $usr_par, $db_row, "first_name", 'fname');
        $this->upd_par($db_con, $usr_par, $db_row, "last_name", 'lname');

        log_debug('user->upd_pars -> done');
        return $result;
    }

    // if at least one other user has switched off all changes from this user
    // all changes of this user should be excluded from the standard values
    // e.g. a user has a
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
        $db_con->set_type(DB_TYPE_USER);

        if ($this->id <= 0) {
            log_debug("user->save add (" . $this->name . ")");

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
                    if (!$db_con->update($this->id, "ip_address", $this->get_ip())) {
                        $result = 'Saving of user ' . $this->id . ' failed.';
                    }
                }
                log_debug("user->save add ... done");
            } else {
                log_debug("user->save add ... failed");
            }
        } else {
            // update the ip address and log the changes????
            log_warning('user->save method for ip update missing', 'user->save', 'method for ip update missing', (new Exception)->getTraceAsString(), $this);
        }
        return $result;
    }
}
